<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

namespace Opencart\Catalog\Controller\Extension\ocStore\Feed;

if (!defined('VERSION')) {
	header('Refresh: 1; URL=/');
	exit('ЗАПРЫШЧАЮ!');
}

class Sitemap extends \Opencart\System\Engine\Controller {
	private $setting_default = [
		'status'                 => false,
		'blog_category_status'   => false,
		'blog_category_image'    => true,
		'blog_category_priority' => '0.9',
		'blog_article_status'    => false,
		'blog_article_image'     => true,
		'blog_article_priority'  => '1.0',
		'category_status'        => true,
		'category_image'         => true,
		'category_priority'      => '0.9',
		'information_status'     => false,
		'information_image'      => false,
		'information_priority'   => '0.5',
		'manufacturer_status'    => false,
		'manufacturer_image'     => true,
		'manufacturer_priority'  => '0.7',
		'product_status'         => true,
		'product_image'          => true,
		'product_priority'       => '1.0',
		'store_id'               => 0,
		'language'               => 'uk-ua',
		'language_id'            => 2,
		'cache'                  => false,
		'start'                  => 0,
		'limit'                  => 10000,
	];
	private $setting = [];

	public function index() {
		if ($this->config->get('feed_sitemap_status')) {
			// Защитный ключ
			$secret_key = $this->config->get('feed_sitemap_secret_key');
			$this->setting = $this->setting_default;
			$setting = $this->config->get('feed_sitemap');

			if ($secret_key) {
				if (!isset($this->request->get['secret_key']) || isset($this->request->get['secret_key']) && $this->request->get['secret_key'] != $secret_key) {
					exit();
				}
			}

			if ($setting && is_array($setting)) {
				foreach ($setting as $key => $result) {
					$this->setting[$key] = $result;
				}
			}

			foreach ($this->setting as $key => $result) {
				if (isset($this->request->get[$key])) {
					$this->setting[$key] = $this->request->get[$key];
				}
			}

			$this->setting['cache'] = $setting['cache'];

			$language_info = $this->db->query("SELECT language_id, code FROM `" . DB_PREFIX . "language` WHERE `language_id` = '" . (int)$this->setting['language_id'] . "'" . (!empty($this->request->get['language']) ? " OR `code` = '" . $this->db->escape($this->setting['language']) . "'" : false))->row;

			if (!empty($language_info['code']) && !empty($language_info['language_id'])) {
				$this->setting['language'] = $language_info['code'];
				$this->setting['language_id'] = $language_info['language_id'];
			} else {
				$this->setting['language'] = $this->setting_default['language'];
				$this->setting['language_id'] = $this->setting_default['language_id'];
			}

			$this->config->set('config_store_id', $this->setting['store_id']);
			$this->config->set('config_language', $this->setting['language']);
			$this->config->set('config_language_id', $this->setting['language_id']);

			if ($this->setting['cache']) {
				$cache_name = 'ocStore.sitemap.' . md5(http_build_query($this->setting));
				$cache = new \Opencart\System\Library\Cache\File(36000);
				$output = $cache->get($cache_name);

				if ($output) {
					$this->response->addHeader('Content-Type: application/xml');
					$this->response->setOutput($output);

					return '';
				}
			}

			$this->load->model('extension/ocStore/feed/sitemap');
			$this->load->model('tool/image');

			$output  = '<?xml version="1.0" encoding="UTF-8"?>';
			$output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';

			if ($this->setting['blog_category_status']) {
				if ($this->setting['blog_category_priority'] > 1) {
					$this->setting['blog_category_priority'] = '1.0';
				} else {
					$this->setting['blog_category_priority'] = abs($this->setting['blog_category_priority']);
				}

				$this->setting['blog_category_priority'] = bcdiv($this->setting['blog_category_priority'], 1, 1);

				$filter_data = [
					'start' => $this->setting['start'],
					'limit' => $this->setting['limit']
				];

				$blog_categories = $this->model_extension_ocStore_feed_sitemap->getBlogCategories($filter_data);

				foreach ($blog_categories as $blog_category) {
					if (!$this->setting['blog_category_image'] || $this->setting['blog_category_image'] && $blog_category['image']) {
						$output .= '<url>';
						$output .= '  <loc>' . $this->url->link('extension/ocStor/blog/category', 'blog_category_id=' . $blog_category['path'] . '&language=' . $this->setting['language']) . '</loc>';
						$output .= '  <changefreq>weekly</changefreq>';
						if (!empty($blog_category['date_modified'])) {
							$output .= '  <lastmod>' . date('Y-m-d\TH:i:sP', strtotime($blog_category['date_modified'])) . '</lastmod>';
						} else {
							$output .= '  <lastmod>' . date('Y-m-d\TH:i:sP') . '</lastmod>';
						}
						$output .= '  <priority>' . $this->setting['blog_category_priority'] . '</priority>';

						if ($this->setting['blog_category_image']) {
							$output .= '  <image:image>';
							$output .= '  <image:loc>' . $this->model_tool_image->resize($blog_category['image'], $this->config->get('config_image_popup_width'), $this->config->get('config_image_popup_height')) . '</image:loc>';
							$output .= '  <image:caption>' . $blog_category['name'] . '</image:caption>';
							$output .= '  <image:title>' . $blog_category['name'] . '</image:title>';
							$output .= '  </image:image>';
						}

						$output .= '</url>';
					}
				}
			}

			if ($this->setting['blog_article_status']) {
				if ($this->setting['blog_article_priority'] > 1) {
					$this->setting['blog_article_priority'] = 1;
				} else {
					$this->setting['blog_article_priority'] = abs($this->setting['blog_article_priority']);
				}

				$this->setting['blog_article_priority'] = bcdiv($this->setting['blog_article_priority'], 1, 1);

				$filter_data = [
					'start' => $this->setting['start'],
					'limit' => $this->setting['limit']
				];

				$articles = $this->model_extension_ocStore_feed_sitemap->getBlogArticles($filter_data);

				foreach ($articles as $article) {
					if (!$this->setting['blog_article_image'] || $this->setting['blog_article_image'] && $article['image']) {
						$output .= '<url>';
						$output .= '  <loc>' . $this->url->link('extension/ocStor/blog/article', 'article_id=' . $article['article_id'] . '&language=' . $this->setting['language']) . '</loc>';
						$output .= '  <changefreq>weekly</changefreq>';
						if (!empty($article['date_modified'])) {
							$output .= '  <lastmod>' . date('Y-m-d\TH:i:sP', strtotime($article['date_modified'])) . '</lastmod>';
						} else {
							$output .= '  <lastmod>' . date('Y-m-d\TH:i:sP') . '</lastmod>';
						}
						$output .= '  <priority>' . $this->setting['blog_article_priority'] . '</priority>';

						if ($this->setting['blog_article_image']) {
							$output .= '  <image:image>';
							$output .= '  <image:loc>' . $this->model_tool_image->resize($article['image'], $this->config->get('config_image_popup_width'), $this->config->get('config_image_popup_height')) . '</image:loc>';
							$output .= '  <image:caption>' . $article['name'] . '</image:caption>';
							$output .= '  <image:title>' . $article['name'] . '</image:title>';
							$output .= '  </image:image>';
						}

						$output .= '</url>';
					}
				}
			}

			if ($this->setting['category_status']) {
				if ($this->setting['category_priority'] > 1) {
					$this->setting['category_priority'] = 1;
				} else {
					$this->setting['category_priority'] = abs($this->setting['category_priority']);
				}

				$this->setting['category_priority'] = bcdiv($this->setting['category_priority'], 1, 1);

				$filter_data = [
					'start' => $this->setting['start'],
					'limit' => $this->setting['limit']
				];

				$categories = $this->model_extension_ocStore_feed_sitemap->getCategories($filter_data);

				foreach ($categories as $category) {
					if (!$this->setting['category_image'] || $this->setting['category_image'] && $category['image']) {
						$output .= '<url>';
						$output .= '  <loc>' . $this->url->link('product/category', 'path=' . $category['path'] . '&language=' . $this->setting['language']) . '</loc>';
						$output .= '  <changefreq>weekly</changefreq>';
						if (!empty($category['date_modified'])) {
							$output .= '  <lastmod>' . date('Y-m-d\TH:i:sP', strtotime($category['date_modified'])) . '</lastmod>';
						} else {
							$output .= '  <lastmod>' . date('Y-m-d\TH:i:sP') . '</lastmod>';
						}
						$output .= '  <priority>' . $this->setting['category_priority'] . '</priority>';

						if ($this->setting['category_image']) {
							$output .= '  <image:image>';
							$output .= '  <image:loc>' . $this->model_tool_image->resize($category['image'], $this->config->get('config_image_popup_width'), $this->config->get('config_image_popup_height')) . '</image:loc>';
							$output .= '  <image:caption>' . $category['name'] . '</image:caption>';
							$output .= '  <image:title>' . $category['name'] . '</image:title>';
							$output .= '  </image:image>';
						}

						$output .= '</url>';
					}
				}
			}

			if ($this->setting['information_status']) {
				if ($this->setting['information_priority'] > 1) {
					$this->setting['information_priority'] = 1;
				} else {
					$this->setting['information_priority'] = abs($this->setting['information_priority']);
				}

				$this->setting['information_priority'] = bcdiv($this->setting['information_priority'], 1, 1);

				$filter_data = [
					'start' => $this->setting['start'],
					'limit' => $this->setting['limit']
				];

				$informations = $this->model_extension_ocStore_feed_sitemap->getInformations($filter_data);

				foreach ($informations as $information) {
					if (!$this->setting['information_image'] || $this->setting['information_image'] && $information['image']) {
						$output .= '<url>';
						$output .= '  <loc>' . $this->url->link('information/information', 'information_id=' . $information['information_id'] . '&language=' . $this->setting['language']) . '</loc>';
						$output .= '  <changefreq>weekly</changefreq>';
						if (!empty($information['date_modified'])) {
							$output .= '  <lastmod>' . date('Y-m-d\TH:i:sP', strtotime($information['date_modified'])) . '</lastmod>';
						} else {
							$output .= '  <lastmod>' . date('Y-m-d\TH:i:sP') . '</lastmod>';
						}
						$output .= '  <priority>' . $this->setting['information_priority'] . '</priority>';

						if ($this->setting['information_image']) {
							$output .= '  <image:image>';
							$output .= '  <image:loc>' . $this->model_tool_image->resize($information['image'], $this->config->get('config_image_popup_width'), $this->config->get('config_image_popup_height')) . '</image:loc>';
							$output .= '  <image:caption>' . $information['name'] . '</image:caption>';
							$output .= '  <image:title>' . $information['name'] . '</image:title>';
							$output .= '  </image:image>';
						}

						$output .= '</url>';
					}
				}
			}

			if ($this->setting['manufacturer_status']) {
				if ($this->setting['manufacturer_priority'] > 1) {
					$this->setting['manufacturer_priority'] = 1;
				} else {
					$this->setting['manufacturer_priority'] = abs($this->setting['manufacturer_priority']);
				}

				$this->setting['manufacturer_priority'] = bcdiv($this->setting['manufacturer_priority'], 1, 1);

				$filter_data = [
					'start' => $this->setting['start'],
					'limit' => $this->setting['limit']
				];

				$manufacturers = $this->model_extension_ocStore_feed_sitemap->getManufacturers($filter_data);

				foreach ($manufacturers as $manufacturer) {
					if (!$this->setting['manufacturer_image'] || $this->setting['manufacturer_image'] && $manufacturer['image']) {
						$output .= '<url>';
						$output .= '  <loc>' . $this->url->link('product/manufacturer|info', 'manufacturer_id=' . $manufacturer['manufacturer_id'] . '&language=' . $this->setting['language']) . '</loc>';
						$output .= '  <changefreq>weekly</changefreq>';
						if (!empty($manufacturer['date_modified'])) {
							$output .= '  <lastmod>' . date('Y-m-d\TH:i:sP', strtotime($manufacturer['date_modified'])) . '</lastmod>';
						} else {
							$output .= '  <lastmod>' . date('Y-m-d\TH:i:sP') . '</lastmod>';
						}
						$output .= '  <priority>' . $this->setting['manufacturer_priority'] . '</priority>';

						if ($this->setting['manufacturer_image']) {
							$output .= '  <image:image>';
							$output .= '  <image:loc>' . $this->model_tool_image->resize($manufacturer['image'], $this->config->get('config_image_popup_width'), $this->config->get('config_image_popup_height')) . '</image:loc>';
							$output .= '  <image:caption>' . $manufacturer['name'] . '</image:caption>';
							$output .= '  <image:title>' . $manufacturer['name'] . '</image:title>';
							$output .= '  </image:image>';
						}

						$output .= '</url>';
					}
				}
			}

			if ($this->setting['product_status']) {
				if ($this->setting['product_priority'] > 1) {
					$this->setting['product_priority'] = 1;
				} else {
					$this->setting['product_priority'] = abs($this->setting['product_priority']);
				}

				$this->setting['product_priority'] = bcdiv($this->setting['product_priority'], 1, 1);

				$filter_data = [
					'start' => $this->setting['start'],
					'limit' => $this->setting['limit']
				];

				$products = $this->model_extension_ocStore_feed_sitemap->getProducts($filter_data);

				foreach ($products as $product) {
					if (!$this->setting['product_image'] || $this->setting['product_image'] && $product['image']) {
						$output .= '<url>';
						$output .= '  <loc>' . $this->url->link('product/product', 'product_id=' . $product['product_id'] . '&language=' . $this->setting['language']) . '</loc>';
						$output .= '  <changefreq>weekly</changefreq>';
						if (!empty($product['date_modified'])) {
							$output .= '  <lastmod>' . date('Y-m-d\TH:i:sP', strtotime($product['date_modified'])) . '</lastmod>';
						} else {
							$output .= '  <lastmod>' . date('Y-m-d\TH:i:sP') . '</lastmod>';
						}
						$output .= '  <priority>' . $this->setting['product_priority'] . '</priority>';

						if ($this->setting['product_image']) {
							$output .= '  <image:image>';
							$output .= '  <image:loc>' . $this->model_tool_image->resize($product['image'], $this->config->get('config_image_popup_width'), $this->config->get('config_image_popup_height')) . '</image:loc>';
							$output .= '  <image:caption>' . $product['name'] . '</image:caption>';
							$output .= '  <image:title>' . $product['name'] . '</image:title>';
							$output .= '  </image:image>';
						}

						$output .= '</url>';
					}
				}
			}

			$output .= '</urlset>';

			if ($output) {
				if ($this->setting['cache']) {
					$cache->set($cache_name, $output);
				}
				$this->response->addHeader('Content-Type: application/xml');
				$this->response->setOutput($output);
			}
		}
	}
}
