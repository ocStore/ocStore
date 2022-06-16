<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

namespace Opencart\Admin\Controller\Extension\ocStore\Blog;

if (!defined('VERSION')) {
	header('Refresh: 1; URL=/');
	exit('ЗАПРЫШЧАЮ!');
}

class Setting extends \Opencart\System\Engine\Controller {
	private array $error = [];

	public function index(): void {
		$this->load->language('extension/ocStore/blog/setting');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('configblog', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('extension/ocStore/blog/setting', 'user_token=' . $this->session->data['user_token'], true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['image_category'])) {
			$data['error_image_category'] = $this->error['image_category'];
		} else {
			$data['error_image_category'] = '';
		}

		if (isset($this->error['image_article'])) {
			$data['error_image_article'] = $this->error['image_article'];
		} else {
			$data['error_image_article'] = '';
		}

		if (isset($this->error['image_related'])) {
			$data['error_image_related'] = $this->error['image_related'];
		} else {
			$data['error_image_related'] = '';
		}

		if (isset($this->error['article_limit'])) {
			$data['error_article_limit'] = $this->error['article_limit'];
		} else {
			$data['error_article_limit'] = '';
		}

		if (isset($this->error['article_description_length'])) {
			$data['error_article_description_length'] = $this->error['article_description_length'];
		} else {
			$data['error_article_description_length'] = '';
		}

		if (isset($this->error['limit_admin'])) {
			$data['error_limit_admin'] = $this->error['limit_admin'];
		} else {
			$data['error_limit_admin'] = '';
		}

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/ocStore/blog/setting', 'user_token=' . $this->session->data['user_token'], true)
		];

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['action'] = $this->url->link('extension/ocStore/blog/setting', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('setting/store', 'user_token=' . $this->session->data['user_token'], true);

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->post['configblog_article_limit'])) {
			$data['configblog_article_limit'] = $this->request->post['configblog_article_limit'];
		} else {
			$data['configblog_article_limit'] = $this->config->get('configblog_article_limit');
		}

		if (isset($this->request->post['configblog_article_description_length'])) {
			$data['configblog_article_description_length'] = $this->request->post['configblog_article_description_length'];
		} else {
			$data['configblog_article_description_length'] = $this->config->get('configblog_article_description_length');
		}

		if (isset($this->request->post['configblog_limit_admin'])) {
			$data['configblog_limit_admin'] = $this->request->post['configblog_limit_admin'];
		} else {
			$data['configblog_limit_admin'] = $this->config->get('configblog_limit_admin');
		}

		if (isset($this->request->post['configblog_article_count'])) {
			$data['configblog_article_count'] = $this->request->post['configblog_article_count'];
		} else {
			$data['configblog_article_count'] = $this->config->get('configblog_article_count');
		}

		if (isset($this->request->post['configblog_blog_menu'])) {
			$data['configblog_blog_menu'] = $this->request->post['configblog_blog_menu'];
		} else {
			$data['configblog_blog_menu'] = $this->config->get('configblog_blog_menu');
		}

		if (isset($this->request->post['configblog_blog_search'])) {
			$data['configblog_blog_search'] = $this->request->post['configblog_blog_search'];
		} else {
			$data['configblog_blog_search'] = $this->config->get('configblog_blog_search');
		}

		if (isset($this->request->post['configblog_customer_blog_search'])) {
			$data['configblog_customer_blog_search'] = $this->request->post['configblog_customer_blog_search'];
		} else {
			$data['configblog_customer_blog_search'] = $this->config->get('configblog_customer_blog_search');
		}

		if (isset($this->request->post['configblog_sitemap'])) {
			$data['configblog_sitemap'] = $this->request->post['configblog_sitemap'];
		} else {
			$data['configblog_sitemap'] = $this->config->get('configblog_sitemap');
		}

		if (isset($this->request->post['configblog_cache_status'])) {
			$data['configblog_cache_status'] = $this->request->post['configblog_cache_status'];
		} else {
			$data['configblog_cache_status'] = $this->config->get('configblog_cache_status');
		}

		if (isset($this->request->post['configblogarticle_download'])) {
			$data['configblog_article_download'] = $this->request->post['configblog_article_download'];
		} else {
			$data['configblog_article_download'] = $this->config->get('configblog_article_download');
		}

		if (isset($this->request->post['configblog_review_status'])) {
			$data['configblog_review_status'] = $this->request->post['configblog_review_status'];
		} else {
			$data['configblog_review_status'] = $this->config->get('configblog_review_status');
		}

		if (isset($this->request->post['configblog_review_guest'])) {
			$data['configblog_review_guest'] = $this->request->post['configblog_review_guest'];
		} else {
			$data['configblog_review_guest'] = $this->config->get('configblog_review_guest');
		}

		if (isset($this->request->post['configblog_review_mail'])) {
			$data['configblog_review_mail'] = $this->request->post['configblog_review_mail'];
		} else {
			$data['configblog_review_mail'] = $this->config->get('configblog_review_mail');
		}

		if (isset($this->request->post['configblog_image_category_width'])) {
			$data['configblog_image_category_width'] = $this->request->post['configblog_image_category_width'];
		} else {
			$data['configblog_image_category_width'] = $this->config->get('configblog_image_category_width');
		}

		if (isset($this->request->post['configblog_image_category_height'])) {
			$data['configblog_image_category_height'] = $this->request->post['configblog_image_category_height'];
		} else {
			$data['configblog_image_category_height'] = $this->config->get('configblog_image_category_height');
		}

		if (isset($this->request->post['configblog_image_article_width'])) {
			$data['configblog_image_article_width'] = $this->request->post['configblog_image_article_width'];
		} else {
			$data['configblog_image_article_width'] = $this->config->get('configblog_image_article_width');
		}

		if (isset($this->request->post['configblog_image_article_height'])) {
			$data['configblog_image_article_height'] = $this->request->post['configblog_image_article_height'];
		} else {
			$data['configblog_image_article_height'] = $this->config->get('configblog_image_article_height');
		}

		if (isset($this->request->post['configblog_image_related_width'])) {
			$data['configblog_image_related_width'] = $this->request->post['configblog_image_related_width'];
		} else {
			$data['configblog_image_related_width'] = $this->config->get('configblog_image_related_width');
		}

		if (isset($this->request->post['configblog_image_related_height'])) {
			$data['configblog_image_related_height'] = $this->request->post['configblog_image_related_height'];
		} else {
			$data['configblog_image_related_height'] = $this->config->get('configblog_image_related_height');
		}

		if (isset($this->request->post['configblog_name'])) {
			$data['configblog_name'] = $this->request->post['configblog_name'];
		} else {
			$data['configblog_name'] = $this->config->get('configblog_name');
		}

		if (isset($this->request->post['configblog_html_h1'])) {
			$data['configblog_html_h1'] = $this->request->post['configblog_html_h1'];
		} else {
			$data['configblog_html_h1'] = $this->config->get('configblog_html_h1');
		}

		if (isset($this->request->post['configblog_meta_title'])) {
			$data['configblog_meta_title'] = $this->request->post['configblog_meta_title'];
		} else {
			$data['configblog_meta_title'] = $this->config->get('configblog_meta_title');
		}

		if (isset($this->request->post['configblog_meta_description'])) {
			$data['configblog_meta_description'] = $this->request->post['configblog_meta_description'];
		} else {
			$data['configblog_meta_description'] = $this->config->get('configblog_meta_description');
		}

		if (isset($this->request->post['configblog_meta_keyword'])) {
			$data['configblog_meta_keyword'] = $this->request->post['configblog_meta_keyword'];
		} else {
			$data['configblog_meta_keyword'] = $this->config->get('configblog_meta_keyword');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/ocStore/blog/setting', $data));
	}

	protected function validate(): bool {
		if (!$this->user->hasPermission('modify', 'extension/ocStore/blog/setting')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((!isset($this->request->post['configblog_image_category_width']) || !(int)$this->request->post['configblog_image_category_width']) || (!isset($this->request->post['configblog_image_category_height']) || !(int)$this->request->post['configblog_image_category_height'])) {
			$this->error['image_category'] = $this->language->get('error_image_category');
		}

		if ((!isset($this->request->post['configblog_image_article_width']) || !(int)$this->request->post['configblog_image_article_width']) || (!isset($this->request->post['configblog_image_article_height']) || !(int)$this->request->post['configblog_image_article_height'])) {
			$this->error['image_article'] = $this->language->get('error_image_article');
		}

		if ((!isset($this->request->post['configblog_image_related_width']) || !(int)$this->request->post['configblog_image_related_width']) || (!isset($this->request->post['configblog_image_related_height']) || !(int)$this->request->post['configblog_image_related_height'])) {
			$this->error['image_related'] = $this->language->get('error_image_related');
		}

		if (!isset($this->request->post['configblog_article_limit']) || !(int)$this->request->post['configblog_article_limit']) {
			$this->error['article_limit'] = $this->language->get('error_limit');
		}

		if (!isset($this->request->post['configblog_article_description_length']) || !(int)$this->request->post['configblog_article_description_length']) {
			$this->error['article_description_length'] = $this->language->get('error_limit');
		}

		if (!isset($this->request->post['configblog_limit_admin']) || !(int)$this->request->post['configblog_limit_admin']) {
			$this->error['limit_admin'] = $this->language->get('error_limit');
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}
}