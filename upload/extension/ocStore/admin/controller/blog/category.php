<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

namespace Opencart\Admin\Controller\Extension\ocStore\Blog;

if (!defined('VERSION')) {
	header('Refresh: 1; URL=/');
	exit('ЗАПРЫШЧАЮ!');
}

class Category extends \Opencart\System\Engine\Controller {
	private array $error = [];
	private array $path = [];

	public function index(): void {
		$this->load->language('extension/ocStore/blog/category');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/ocStore/blog/category');

		$this->getList();
	}

	public function add(): void {
		$this->load->language('extension/ocStore/blog/category');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/ocStore/blog/category');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_extension_ocStore_blog_category->addCategory($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('extension/ocStore/blog/category', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function edit(): void {
		$this->load->language('extension/ocStore/blog/category');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/ocStore/blog/category');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_extension_ocStore_blog_category->editCategory($this->request->get['blog_category_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('extension/ocStore/blog/category', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function delete(): void {
		$this->load->language('extension/ocStore/blog/category');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/ocStore/blog/category');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $blog_category_id) {
				$this->model_extension_ocStore_blog_category->deleteCategory($blog_category_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('extension/ocStore/blog/category', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	public function repair(): void {
		$url = '';
		
		$this->load->language('extension/ocStore/blog/category');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/ocStore/blog/category');

		if ($this->validateRepair()) {
			$this->model_extension_ocStore_blog_category->repairCategories();

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('extension/ocStore/blog/category', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	public function enable(): void {
		$this->load->language('extension/ocStore/blog/category');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/ocStore/blog/category');

		if (isset($this->request->post['selected']) && $this->validateProStatus()) {
			foreach ($this->request->post['selected'] as $article_id) {
				$this->model_extension_ocStore_blog_category->editCategoryStatus($article_id, 1);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			$this->response->redirect($this->url->link('extension/ocStore/blog/category', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	public function disable(): void {
		$this->load->language('extension/ocStore/blog/category');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/ocStore/blog/category');

		if (isset($this->request->post['selected']) && $this->validateProStatus()) {
			foreach ($this->request->post['selected'] as $article_id) {
				$this->model_extension_ocStore_blog_category->editCategoryStatus($article_id, 0);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			$this->response->redirect($this->url->link('extension/ocStore/blog/category', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	protected function getList(): void {
		$url = '';

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/ocStore/blog/category', 'user_token=' . $this->session->data['user_token'] . '&path=' . $url, true)
		];

		$data['add'] = $this->url->link('extension/ocStore/blog/category|add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('extension/ocStore/blog/category|delete', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['repair'] = $this->url->link('extension/ocStore/blog/category|repair', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['enabled'] = $this->url->link('extension/ocStore/blog/category|enable', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['disabled'] = $this->url->link('extension/ocStore/blog/category|disable', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['path'])) {
			if ($this->request->get['path'] != '') {
				$this->path = explode('_', $this->request->get['path']);
				$this->session->data['path'] = $this->request->get['path'];
			} else {
				unset($this->session->data['path']);
			}
		} elseif (isset($this->session->data['path'])) {
			$this->path = explode('_', $this->session->data['path']);
 		}

		$data['categories'] = $this->getCategories(0);

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_list'] = $this->language->get('text_list');
		$data['text_no_results'] = $this->language->get('text_no_results');
		$data['text_confirm'] = $this->language->get('text_confirm');

		$data['column_name'] = $this->language->get('column_name');
		$data['column_sort_order'] = $this->language->get('column_sort_order');
		$data['column_noindex'] = $this->language->get('column_noindex');
		$data['column_action'] = $this->language->get('column_action');

		$data['button_add'] = $this->language->get('button_add');
		$data['button_edit'] = $this->language->get('button_edit');
		$data['button_shop'] = $this->language->get('button_shop');
		$data['button_delete'] = $this->language->get('button_delete');
		$data['button_rebuild'] = $this->language->get('button_rebuild');
		$data['button_enable'] = $this->language->get('button_enable');
		$data['button_disable'] = $this->language->get('button_disable');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = [];
		}

		$url = '';

		$category_total = $this->model_extension_ocStore_blog_category->getTotalCategories();

		$data['results'] = $this->language->get('text_category_total') . $category_total;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/ocStore/blog/category_list', $data));
	}

	protected function getForm(): void {
		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_form'] = !isset($this->request->get['blog_category_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
		$data['text_none'] = $this->language->get('text_none');
		$data['text_default'] = $this->language->get('text_default');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');

		$data['entry_name'] = $this->language->get('entry_name');
		$data['entry_description'] = $this->language->get('entry_description');
		$data['entry_meta_title'] = $this->language->get('entry_meta_title');
		$data['entry_meta_h1'] = $this->language->get('entry_meta_h1');
		$data['entry_meta_description'] = $this->language->get('entry_meta_description');
		$data['entry_meta_keyword'] = $this->language->get('entry_meta_keyword');
		$data['entry_keyword'] = $this->language->get('entry_keyword');
		$data['entry_parent'] = $this->language->get('entry_parent');
		$data['entry_store'] = $this->language->get('entry_store');
		$data['entry_image'] = $this->language->get('entry_image');
		$data['entry_top'] = $this->language->get('entry_top');
		$data['entry_column'] = $this->language->get('entry_column');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_noindex'] = $this->language->get('entry_noindex');
		$data['entry_layout'] = $this->language->get('entry_layout');

		$data['help_keyword'] = $this->language->get('help_keyword');
		$data['help_top'] = $this->language->get('help_top');
		$data['help_column'] = $this->language->get('help_column');
		$data['help_noindex'] = $this->language->get('help_noindex');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		$data['tab_general'] = $this->language->get('tab_general');
		$data['tab_data'] = $this->language->get('tab_data');
		$data['tab_design'] = $this->language->get('tab_design');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = [];
		}

		if (isset($this->error['meta_title'])) {
			$data['error_meta_title'] = $this->error['meta_title'];
		} else {
			$data['error_meta_title'] = [];
		}

		if (isset($this->error['meta_h1'])) {
			$data['error_meta_h1'] = $this->error['meta_h1'];
		} else {
			$data['error_meta_h1'] = [];
		}

		if (isset($this->error['keyword'])) {
			$data['error_keyword'] = $this->error['keyword'];
		} else {
			$data['error_keyword'] = '';
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/ocStore/blog/category', 'user_token=' . $this->session->data['user_token'] . $url, true)
		];

		if (!isset($this->request->get['blog_category_id'])) {
			$data['action'] = $this->url->link('extension/ocStore/blog/category|add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('extension/ocStore/blog/category|edit', 'user_token=' . $this->session->data['user_token'] . '&blog_category_id=' . $this->request->get['blog_category_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link('extension/ocStore/blog/category', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['blog_category_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$category_info = $this->model_extension_ocStore_blog_category->getCategory($this->request->get['blog_category_id']);
		}

		$data['token'] = $this->session->data['user_token'];

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['category_description'])) {
			$data['category_description'] = $this->request->post['category_description'];
		} elseif (isset($this->request->get['blog_category_id'])) {
			$data['category_description'] = $this->model_extension_ocStore_blog_category->getCategoryDescriptions($this->request->get['blog_category_id']);
		} else {
			$data['category_description'] = [];
		}

		$language_id = $this->config->get('config_language_id');
		if (isset($data['category_description'][$language_id]['name'])) {
			$data['heading_title'] = $data['category_description'][$language_id]['name'];
		}

		if (isset($this->request->post['path'])) {
			$data['path'] = $this->request->post['path'];
		} elseif (!empty($category_info)) {
			$data['path'] = $category_info['path'];
		} else {
			$data['path'] = '';
		}

		if (isset($this->request->post['parent_id'])) {
			$data['parent_id'] = $this->request->post['parent_id'];
		} elseif (!empty($category_info)) {
			$data['parent_id'] = $category_info['parent_id'];
		} else {
			$data['parent_id'] = 0;
		}

		$this->load->model('setting/store');

		$data['stores'] = $this->model_setting_store->getStores();

		if (isset($this->request->post['category_store'])) {
			$data['category_store'] = $this->request->post['category_store'];
		} elseif (isset($this->request->get['blog_category_id'])) {
			$data['category_store'] = $this->model_extension_ocStore_blog_category->getStores($this->request->get['blog_category_id']);
		} else {
			$data['category_store'] = array(0);
		}

		if (isset($this->request->post['keyword'])) {
			$data['keyword'] = $this->request->post['keyword'];
		} elseif (!empty($category_info)) {
			$data['keyword'] = $category_info['keyword'];
		} else {
			$data['keyword'] = '';
		}

		if (isset($this->request->post['image'])) {
			$data['image'] = $this->request->post['image'];
		} elseif (!empty($category_info)) {
			$data['image'] = $category_info['image'];
		} else {
			$data['image'] = '';
		}

		$this->load->model('tool/image');

		if (isset($this->request->post['image']) && is_file(DIR_IMAGE . $this->request->post['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($this->request->post['image'], 100, 100);
		} elseif (!empty($category_info) && is_file(DIR_IMAGE . $category_info['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($category_info['image'], 100, 100);
		} else {
			$data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		if (isset($this->request->post['top'])) {
			$data['top'] = $this->request->post['top'];
		} elseif (!empty($category_info)) {
			$data['top'] = $category_info['top'];
		} else {
			$data['top'] = 0;
		}

		if (isset($this->request->post['column'])) {
			$data['column'] = $this->request->post['column'];
		} elseif (!empty($category_info)) {
			$data['column'] = $category_info['column'];
		} else {
			$data['column'] = 1;
		}

		if (isset($this->request->post['sort_order'])) {
			$data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($category_info)) {
			$data['sort_order'] = $category_info['sort_order'];
		} else {
			$data['sort_order'] = 0;
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($category_info)) {
			$data['status'] = $category_info['status'];
		} else {
			$data['status'] = true;
		}

		if (isset($this->request->post['noindex'])) {
			$data['noindex'] = $this->request->post['noindex'];
		} elseif (!empty($category_info)) {
			$data['noindex'] = $category_info['noindex'];
		} else {
			$data['noindex'] = 1;
		}

		if (isset($this->request->post['category_layout'])) {
			$data['category_layout'] = $this->request->post['category_layout'];
		} elseif (isset($this->request->get['blog_category_id'])) {
			$data['category_layout'] = $this->model_extension_ocStore_blog_category->getLayouts($this->request->get['blog_category_id']);
		} else {
			$data['category_layout'] = [];
		}

		$this->load->model('design/layout');

		$data['layouts'] = $this->model_design_layout->getLayouts();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/ocStore/blog/category_form', $data));
	}

	protected function validateForm(): bool {
		if (!$this->user->hasPermission('modify', 'extension/ocStore/blog/category')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['category_description'] as $language_id => $value) {
			if ((utf8_strlen($value['name']) < 2) || (utf8_strlen($value['name']) > 255)) {
				$this->error['name'][$language_id] = $this->language->get('error_name');
			}

			if ((utf8_strlen($value['meta_title']) < 0) || (utf8_strlen($value['meta_title']) > 255)) {
				$this->error['meta_title'][$language_id] = $this->language->get('error_meta_title');
			}
			
			if ((utf8_strlen($value['meta_h1']) < 0) || (utf8_strlen($value['meta_h1']) > 255)) {
				$this->error['meta_h1'][$language_id] = $this->language->get('error_meta_h1');
			}
		}

		if (utf8_strlen($this->request->post['keyword']) > 0) {
			$this->load->model('catalog/url_alias');

			$url_alias_info = $this->model_catalog_url_alias->getUrlAlias($this->request->post['keyword']);

			if ($url_alias_info && isset($this->request->get['blog_category_id']) && $url_alias_info['query'] != 'blog_category_id=' . $this->request->get['blog_category_id']) {
				$this->error['keyword'] = sprintf($this->language->get('error_keyword')) . ' <a href="' . $this->url->link('tool/seomanager', 'user_token=' . $this->session->data['user_token'] . '&filter_query=' . $url_alias_info['query'], true) . '" target="_blank">' . $url_alias_info['query'] . '</a>';
			}

			if ($url_alias_info && !isset($this->request->get['blog_category_id'])) {
				$this->error['keyword'] = sprintf($this->language->get('error_keyword')) . ' <a href="' . $this->url->link('tool/seomanager', 'user_token=' . $this->session->data['user_token'] . '&filter_query=' . $url_alias_info['query'], true) . '" target="_blank">' . $url_alias_info['query'] . '</a>';
			}

			if ($this->error && !isset($this->error['warning'])) {
				$this->error['warning'] = $this->language->get('error_warning');
			}
		}

		return !$this->error;
	}

	protected function validateDelete(): bool {
		if (!$this->user->hasPermission('modify', 'extension/ocStore/blog/category')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	protected function validateRepair(): bool {
		if (!$this->user->hasPermission('modify', 'extension/ocStore/blog/category')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	protected function validateProStatus(): bool {
		if (!$this->user->hasPermission('modify', 'extension/ocStore/blog/category')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function autocomplete(): void {
		$json = [];

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('extension/ocStore/blog/category');

			$filter_data = array(
				'filter_name' => $this->request->get['filter_name'],
				'sort'        => 'name',
				'order'       => 'ASC',
				'start'       => 0,
				'limit'       => $this->config->get('configblog_limit_admin')
			);

			$results = $this->model_extension_ocStore_blog_category->getCategories($filter_data);

			foreach ($results as $result) {
				$json[] = array(
					'blog_category_id' => $result['blog_category_id'],
					'name'             => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
				);
			}
		}

		$sort_order = [];

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	private function getCategories($parent_id, $parent_path = '', $indent = ''): array {
		$blog_category_id = array_shift($this->path);

		$output = [];

		$results = $this->model_extension_ocStore_blog_category->getCategoriesByParentId($parent_id);

		foreach ($results as $result) {
			if ($blog_category_id == $result['blog_category_id']) {
				$name = '<b>' . $result['name'] . '</b>';
				$href = '';
			} else {
				$name = $result['name'];
				if ($result['children']) {
					$href = $this->url->link('extension/ocStore/blog/category', 'user_token=' . $this->session->data['user_token'] . '&path=' . $parent_path . $result['blog_category_id'], true);
				} else {
					$href = '';
				}
			}

			$selected = isset($this->request->post['selected']) && in_array($result['blog_category_id'], $this->request->post['selected']);

			$output[$result['blog_category_id']] = array(
				'blog_category_id' => $result['blog_category_id'],
				'name'             => $name,
				'sort_order'       => $result['sort_order'],
				'noindex'          => ($result['noindex'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
				'edit'             => $this->url->link('extension/ocStore/blog/category|edit', 'user_token=' . $this->session->data['user_token'] . '&blog_category_id=' . $result['blog_category_id'], true),
				'selected'         => $selected,
				'href'             => $href,
				'href_shop'        => HTTP_CATALOG . 'index.php?route=extension/ocStore/blog/category&blog_category_id=' . $result['blog_category_id'],
				'indent'           => $indent
			);

			if ($blog_category_id == $result['blog_category_id']) {
				$output += $this->getCategories($result['blog_category_id'], $parent_path . $result['blog_category_id'] . '_', $indent . str_repeat('&nbsp;', 8));
			}
		}

		return $output;
	}
}