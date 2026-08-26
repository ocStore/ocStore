<?php
namespace Opencart\Catalog\Controller\Cms;
/**
 * Class Menu
 *
 * Can be called from $this->load->controller('cms/menu');
 *
 * @package Opencart\Catalog\Controller\Cms
 */
class Menu extends \Opencart\System\Engine\Controller {
	/**
	 * Index
	 *
	 * @return string
	 */
	public function index(): string {
		$this->load->language('cms/menu');

		$data['text_blog'] = $this->language->get('text_blog');
		$data['text_all'] = $this->language->get('text_all');

		$data['blog'] = $this->url->link('cms/blog', 'language=' . $this->config->get('config_language'));

		$this->load->model('cms/topic');

		$this->load->model('cms/article');

		$data['topics'] = [];

		foreach ($this->model_cms_topic->getTopicsByParentId(0) as $topic) {
			$children_data = [];

			foreach ($this->model_cms_topic->getTopicsByParentId($topic['topic_id']) as $child) {
				$children_data[] = [
					'name' => $child['name'] . ' (' . $this->model_cms_article->getTotalArticles(['filter_topic_id' => $child['topic_id'], 'filter_sub_topic' => true]) . ')',
					'href' => $this->url->link('cms/blog', 'language=' . $this->config->get('config_language') . '&topic_id=' . $child['topic_id'])
				];
			}

			$data['topics'][] = [
				'name'     => $topic['name'] . ' (' . $this->model_cms_article->getTotalArticles(['filter_topic_id' => $topic['topic_id'], 'filter_sub_topic' => true]) . ')',
				'children' => $children_data,
				'href'     => $this->url->link('cms/blog', 'language=' . $this->config->get('config_language') . '&topic_id=' . $topic['topic_id'])
			];
		}

		return $this->load->view('cms/menu', $data);
	}
}
