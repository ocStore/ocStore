<?php
namespace Opencart\Catalog\Controller\Extension\Opencart\Module;
/**
 * Class Blog
 *
 * @package Opencart\Catalog\Controller\Extension\Opencart\Module
 */
class Blog extends \Opencart\System\Engine\Controller {
	/**
	 * Index
	 *
	 * @param array<string, mixed> $setting array of filters
	 *
	 * @return string
	 */
	public function index(array $setting): string {
		$this->load->language('extension/opencart/module/blog');

		$position = (string)($setting['position'] ?? '');

		if (substr($position, 0, 6) == 'column') {
			$data['axis'] = 'vertical';
		} else {
			$data['axis'] = $setting['axis'] ?? 'horizontal';
		}

		$data['text_viewed'] = $this->language->get('text_viewed');
		$data['button_more'] = $this->language->get('button_more');

		$data['blogs'] = [];

		// Blog
		$this->load->model('extension/opencart/module/blog');

		// Image
		$this->load->model('tool/image');

		$filter_data = [
			'sort'  => $setting['sort'],
			'order' => $setting['order'],
			'start' => 0,
			'limit' => $setting['limit']
		];

		$results = $this->model_extension_opencart_module_blog->getArticles($filter_data);

		if ($results) {
			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize(html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'), $setting['width'], $setting['height']);
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
				}

				$data['blogs'][] = [
					'article_id'  => $result['article_id'],
					'thumb'       => $image,
					'name'        => $result['name'],
					'description' => oc_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, (int)($setting['description_length'] ?? 100)) . '..',
					'viewed'      => $result['viewed'],
					'date_added'  => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
					'href'        => $this->url->link('cms/blog.info', 'language=' . $this->config->get('config_language') . '&article_id=' . $result['article_id'])
				];
			}

			return $this->load->view('extension/opencart/module/blog', $data);
		} else {
			return '';
		}
	}
}
