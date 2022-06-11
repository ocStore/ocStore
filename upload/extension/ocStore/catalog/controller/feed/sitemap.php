<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

namespace Opencart\Catalog\Controller\Extension\ocStore\Feed;

if (!defined('VERSION')) {
	header('Refresh: 1; URL=/');
	exit('ЗАПРЫШЧАЮ!');
}

class Sitemap extends \Opencart\System\Engine\Controller {
	private $setting_default = array(
		'status' => false,
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
		'cache_status'           => true,
		'start'                  => 0,
		'limit'                  => 10000,
	);
	private $setting = array();

	public function index() {
		if ($this->config->get('feed_sitemap_status')) {
			$this->setting = $this->setting_default;
			$setting = $this->config->get('feed_sitemap');

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

			$language_info = $this->db->query("SELECT language_id, code FROM `" . DB_PREFIX . "language` WHERE `language_id` = '" . (int)$this->setting['language_id'] . "'" . (!empty($this->request->get['language']) ? " OR `code` = '" . $this->db->escape($this->setting['language']) . "'" : false))->row;

			if (!empty($language_info['code']) && !empty($language_info['language_id'])) {
				$this->setting['language'] = $language_info['code'];
				$this->setting['language_id'] = $language_info['language_id'];
			} else {
				$this->setting['language'] = $this->setting_default['language'];
				$this->setting['language_id'] = $this->setting_default['language_id'];
			}

			$this->setting['cache_status'] = false;

			if ($this->setting['cache_status']) {
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

			if ($this->setting['blog_category_status'] && is_file(DIR_EXTENSION . 'ocStore/catalog/model/blog/category.php')) {
				if ($this->setting['blog_category_priority'] > 1) {
					$this->setting['blog_category_priority'] = 1;
				}

				$this->load->model('extension/ocStore/blog/category');

				$output .= $this->getBlogCategories(0);
			}

			if ($this->setting['blog_article_status'] && is_file(DIR_EXTENSION . 'ocStore/catalog/model/blog/article.php')) {
				if ($this->setting['blog_article_priority'] > 1) {
					$this->setting['blog_article_priority'] = 1;
				}

				$this->load->model('extension/ocStore/blog/article');

				$articles = $this->model_extension_ocStore_blog_article->getArticles();

				foreach ($articles as $article) {
					if (!$this->setting['blog_article_image'] || $this->setting['blog_article_image'] && $article['image']) {
						$output .= '<url>';
						$output .= '  <loc>' . $this->url->link('extension/ocStor/blog/article', 'article_id=' . $article['article_id'] . '&language=' . $this->setting['language']) . '</loc>';
						$output .= '  <changefreq>weekly</changefreq>';
						if (isset($article['date_modified'])) {
							$output .= '  <lastmod>' . date('Y-m-d\TH:i:sP', strtotime($article['date_modified'])) . '</lastmod>';
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

			if ($this->setting['category_status'] && is_file(DIR_APPLICATION . 'model/catalog/category.php')) {
				if ($this->setting['category_priority'] > 1) {
					$this->setting['category_priority'] = 1;
				}

				$this->load->model('catalog/category');

				$output .= $this->getCategories(0);
			}

			if ($this->setting['information_status'] && is_file(DIR_APPLICATION . 'model/catalog/information.php')) {
				if ($this->setting['information_priority'] > 1) {
					$this->setting['information_priority'] = 1;
				}

				$this->load->model('catalog/information');

				$informations = $this->model_catalog_information->getInformations();

				foreach ($informations as $information) {
					if (!$this->setting['information_image'] || $this->setting['information_image'] && $information['image']) {
						$output .= '<url>';
						$output .= '  <loc>' . $this->url->link('information/information', 'information_id=' . $information['information_id'] . '&language=' . $this->setting['language']) . '</loc>';
						$output .= '  <changefreq>weekly</changefreq>';
						if (isset($information['date_modified'])) {
							$output .= '  <lastmod>' . date('Y-m-d\TH:i:sP', strtotime($information['date_modified'])) . '</lastmod>';
						}
						$output .= '  <priority>' . $this->setting['information_priority'] . '</priority>';

						if ($this->setting['information_image']) {
							$output .= '  <image:image>';
							$output .= '  <image:loc>' . $this->model_tool_image->resize($information['image'], $this->config->get('config_image_popup_width'), $this->config->get('config_image_popup_height')) . '</image:loc>';
							if (!isset($information['name'])) {
								$information['name'] = $information['title'];
							}
							$output .= '  <image:caption>' . $information['name'] . '</image:caption>';
							$output .= '  <image:title>' . $information['name'] . '</image:title>';
							$output .= '  </image:image>';
						}

						$output .= '</url>';
					}
				}
			}

			if ($this->setting['manufacturer_status'] && is_file(DIR_APPLICATION . 'model/catalog/manufacturer.php')) {
				if ($this->setting['manufacturer_priority'] > 1) {
					$this->setting['manufacturer_priority'] = 1;
				}
				
				$this->load->model('catalog/manufacturer');

				$manufacturers = $this->model_catalog_manufacturer->getManufacturers();

				foreach ($manufacturers as $manufacturer) {
					if (!$this->setting['manufacturer_image'] || $this->setting['manufacturer_image'] && $manufacturer['image']) {
						$output .= '<url>';
						$output .= '  <loc>' . $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $manufacturer['manufacturer_id'] . '&language=' . $this->setting['language']) . '</loc>';
						$output .= '  <changefreq>weekly</changefreq>';
						if (isset($manufacturer['date_modified'])) {
							$output .= '  <lastmod>' . date('Y-m-d\TH:i:sP', strtotime($manufacturer['date_modified'])) . '</lastmod>';
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

			if ($this->setting['product_status'] && is_file(DIR_APPLICATION . 'model/catalog/product.php')) {
				if ($this->setting['product_priority'] > 1) {
					$this->setting['product_priority'] = 1;
				}

				$this->load->model('catalog/product');

				$products = $this->model_catalog_product->getProducts();

				foreach ($products as $product) {
					if (!$this->setting['product_image'] || $this->setting['product_image'] && $product['image']) {
						$output .= '<url>';
						$output .= '  <loc>' . $this->url->link('product/product', 'product_id=' . $product['product_id'] . '&language=' . $this->setting['language']) . '</loc>';
						$output .= '  <changefreq>weekly</changefreq>';
						if (isset($product['date_modified'])) {
							$output .= '  <lastmod>' . date('Y-m-d\TH:i:sP', strtotime($product['date_modified'])) . '</lastmod>';
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
				if ($this->setting['cache_status']) {
					$cache->set($cache_name, $output);
				}
				$this->response->addHeader('Content-Type: application/xml');
				$this->response->setOutput($output);
			}
		}
	}

	protected function getCategories($parent_id, $current_path = '') {
		$output = '';

		$results = $this->model_catalog_category->getCategories($parent_id);

		foreach ($results as $result) {
			if (!$current_path) {
				$new_path = $result['category_id'];
			} else {
				$new_path = $current_path . '_' . $result['category_id'];
			}

			if (!$this->setting['category_image'] || $this->setting['category_image'] && $result['image']) {
				$output .= '<url>';
				$output .= '  <loc>' . $this->url->link('product/category', 'path=' . $new_path . '&language=' . $this->setting['language']) . '</loc>';
				$output .= '  <changefreq>weekly</changefreq>';
				if (isset($result['date_modified'])) {
					$output .= '  <lastmod>' . date('Y-m-d\TH:i:sP', strtotime($result['date_modified'])) . '</lastmod>';
				}
				$output .= '  <priority>' . $this->setting['category_priority'] . '</priority>';

				if ($this->setting['category_image']) {
					$output .= '  <image:image>';
					$output .= '  <image:loc>' . $this->model_tool_image->resize($result['image'], $this->config->get('config_image_popup_width'), $this->config->get('config_image_popup_height')) . '</image:loc>';
					$output .= '  <image:caption>' . $result['name'] . '</image:caption>';
					$output .= '  <image:title>' . $result['name'] . '</image:title>';
					$output .= '  </image:image>';
				}

				$output .= '</url>';
			}

			$output .= $this->getCategories($result['category_id'], $new_path);
		}

		return $output;
	}

	protected function getBlogCategories($parent_id, $current_path = '') {
		$output = '';

		$results = $this->model_extension_ocStore_blog_category->getCategories($parent_id);

		foreach ($results as $result) {
			if (!$current_path) {
				$new_path = $result['blog_category_id'];
			} else {
				$new_path = $current_path . '_' . $result['blog_category_id'];
			}

			if (!$this->setting['blog_category_image'] || $this->setting['blog_category_image'] && $result['image']) {
				$output .= '<url>';
				$output .= '  <loc>' . $this->url->link('extension/ocStor/blog/category', 'blog_category_id=' . $new_path . '&language=' . $this->setting['language']) . '</loc>';
				$output .= '  <changefreq>weekly</changefreq>';
				if (isset($result['date_modified'])) {
					$output .= '  <lastmod>' . date('Y-m-d\TH:i:sP', strtotime($result['date_modified'])) . '</lastmod>';
				}
				$output .= '  <priority>' . $this->setting['blog_category_priority'] . '</priority>';

				if ($this->setting['blog_category_image']) {
					$output .= '  <image:image>';
					$output .= '  <image:loc>' . $this->model_tool_image->resize($result['image'], $this->config->get('config_image_popup_width'), $this->config->get('config_image_popup_height')) . '</image:loc>';
					$output .= '  <image:caption>' . $result['name'] . '</image:caption>';
					$output .= '  <image:title>' . $result['name'] . '</image:title>';
					$output .= '  </image:image>';
				}

				$output .= '</url>';
			}

			$output .= $this->getCategories($result['blog_category_id'], $new_path);
		}

		return $output;
	}
}