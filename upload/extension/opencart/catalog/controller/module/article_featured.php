<?php
namespace Opencart\Catalog\Controller\Extension\Opencart\Module;
/**
 * Class ArticleFeatured
 *
 * @package Opencart\Catalog\Controller\Extension\Opencart\Module
 */
class ArticleFeatured extends \Opencart\System\Engine\Controller {
	/**
	 * Index
	 *
	 * @param array<string, mixed> $setting array of data
	 *
	 * @return string
	 */
	public function index(array $setting): string {
		$this->load->language('extension/opencart/module/article_featured');

		$position = (string)($setting['position'] ?? '');

		if (substr($position, 0, 6) == 'column') {
			$data['axis'] = 'vertical';
		} else {
			$data['axis'] = $setting['axis'] ?? 'horizontal';
		}

		$data['text_viewed'] = $this->language->get('text_viewed');
		$data['button_more'] = $this->language->get('button_more');

		$data['articles'] = [];

		$this->load->model('cms/article');

		$this->load->model('tool/image');

		if (!empty($setting['article'])) {
			$articles = array_slice($setting['article'], 0, (int)($setting['limit'] ?? 4));

			foreach ($articles as $article_id) {
				$article_info = $this->model_cms_article->getArticle((int)$article_id);

				if (!$article_info) {
					continue;
				}

				if ($article_info['image']) {
					$image = $this->model_tool_image->resize(html_entity_decode($article_info['image'], ENT_QUOTES, 'UTF-8'), (int)$setting['width'], (int)$setting['height']);
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', (int)$setting['width'], (int)$setting['height']);
				}

				$data['articles'][] = [
					'article_id'  => $article_info['article_id'],
					'thumb'       => $image,
					'name'        => $article_info['name'],
					'description' => oc_substr(trim(strip_tags(html_entity_decode($article_info['description'], ENT_QUOTES, 'UTF-8'))), 0, (int)($setting['description_length'] ?? 100)) . '..',
					'rating'      => $article_info['rating'],
					'viewed'      => $article_info['viewed'],
					'date_added'  => date($this->language->get('date_format_short'), strtotime($article_info['date_added'])),
					'href'        => $this->url->link('cms/blog.info', 'language=' . $this->config->get('config_language') . '&article_id=' . $article_info['article_id'])
				];
			}
		}

		if ($data['articles']) {
			return $this->load->view('extension/opencart/module/article_featured', $data);
		} else {
			return '';
		}
	}
}
