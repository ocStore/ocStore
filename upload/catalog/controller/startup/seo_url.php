<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

class ControllerStartupSeoUrl extends Controller {
	
	//seopro start
		private $seo_pro;
		public function __construct($registry) {
			parent::__construct($registry);	
			$this->seo_pro = new SeoPro($registry);
		}
	//seopro end
	
	public function index() {
		// Add rewrite to url class
		if ($this->config->get('config_seo_url')) {
			$this->url->addRewrite($this);
		}

		// Load all regexes in the var so we are not accessing the db so much.
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_regex ORDER BY sort_order ASC");

		$this->regex = $query->rows;

		// Decode URL
		if (isset($this->request->get['_route_'])) {
			$parts = explode('/', $this->request->get['_route_']);
			
		//seopro prepare route
		if($this->config->get('config_seo_pro')){		
			$parts = $this->seo_pro->prepareRoute($parts);
		}
		//seopro prepare route end

			// remove any empty arrays from trailing
			if (utf8_strlen(end($parts)) == 0) {
				array_pop($parts);
			}

			foreach ($parts as $part) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE keyword = '" . $this->db->escape($part) . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");

				if ($query->num_rows) {
					foreach ($query->rows as $result) {
						parse_str($result['push'], $data);

						foreach ($data as $key => $value) {
							$this->request->get[$key] = $value;
						}
					}
				} else {
					if(!$this->config->get('config_seo_pro')){		
					$this->request->get['route'] = 'error/not_found';
					}

					break;
				}
			}
		}
		
		//seopro validate
		if($this->config->get('config_seo_pro')){		
		$this->seo_pro->validate();
		}
	//seopro validate
		
	}

	public function rewrite($link) {
		$url_info = parse_url(str_replace('&amp;', '&', $link));

		if($this->config->get('config_seo_pro')){		
		$url = null;
			} else {	
		$url = '';
		}

		$url_info = parse_url(str_replace('&amp;', '&', $link));

		parse_str($url_info['query'], $data);
		
		//seo_pro baseRewrite
		if($this->config->get('config_seo_pro')){		
		list($url, $data, $postfix) =  $this->seo_pro->baseRewrite($data, (int)$this->config->get('config_language_id'));	
		}
		//seo_pro baseRewrite

		foreach ($this->regex as $result) {
			if (preg_match('/' . $result['regex'] . '/', $url_info['query'], $matches)) {
				array_shift($matches);

				foreach ($matches as $match) {
					$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE `query` = '" . $this->db->escape($match) . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");

					if ($query->num_rows) {
						foreach ($query->rows as $seo) {
							if ($seo['keyword']) {
								$url .= '/' . $seo['keyword'];
							}
						}

						parse_str($match, $remove);

						// Remove all the matched url elements
						foreach (array_keys($remove) as $key) {
							if (isset($data[$key])) {
								unset($data[$key]);
							}
						}
					}
				}
			}
		}

		//seo_pro add blank url
		if($this->config->get('config_seo_pro')){		
			$condition = ($url !== null);
		} else {
			$condition = ($url);
		}
			
		if ($condition) {
		
		if($this->config->get('config_seo_pro')){		
			if($this->config->get('config_page_postfix') && $postfix) {
				$url .= $this->config->get('config_page_postfix');
			}
		}
		
		//seo_pro add blank url
			unset($data['route']);

			$query = '';

			if ($data) {
				foreach ($data as $key => $value) {
					$query .= '&' . rawurlencode((string)$key) . '=' . rawurlencode(is_array($value) ? http_build_query($value) : (string)$value);
				}

				if ($query) {
					$query = '?' . str_replace('&', '&amp;', trim($query, '&'));
				}
			}

			return $url_info['scheme'] . '://' . $url_info['host'] . (isset($url_info['port']) ? ':' . $url_info['port'] : '') . str_replace('/index.php', '', $url_info['path']) . $url . $query;
		} else {
			return $link;
		}
	}
}