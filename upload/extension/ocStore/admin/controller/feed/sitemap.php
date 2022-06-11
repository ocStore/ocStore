<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

namespace Opencart\Admin\Controller\Extension\ocStore\Feed;
class Sitemap extends \Opencart\System\Engine\Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/ocStore/feed/sitemap');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('feed_sitemap', [
				'feed_sitemap_status' => !empty($this->request->post['status']),
				'feed_sitemap' => $this->request->post
			]);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=feed', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=feed', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/ocStore/feed/sitemap', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/ocStore/feed/sitemap', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=feed', true);

		$module_info = $this->config->get('feed_sitemap');

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($module_info['status'])) {
			$data['status'] = $module_info['status'];
		} else {
			$data['status'] = true;
		}

		$data['results'] = [];
		$data['results'][] = ['status' => false, 'image' => true, 'priority' => '0.9', 'table' => 'blog_category'];
		$data['results'][] = ['status' => false, 'image' => true, 'priority' => '1.0', 'table' => 'blog_article'];
		$data['results'][] = ['status' => false, 'image' => true, 'priority' => '0.9', 'table' => 'category'];
		$data['results'][] = ['status' => false, 'image' => false, 'priority' => '0.5', 'table' => 'information'];
		$data['results'][] = ['status' => false, 'image' => true, 'priority' => '0.7', 'table' => 'manufacturer'];
		$data['results'][] = ['status' => false, 'image' => true, 'priority' => '1.0', 'table' => 'product'];

		foreach ($data['results'] as $result) {
			if (isset($this->request->post[$result['table'] . '_status'])) {
				$data[$result['table'] . '_status'] = $this->request->post[$result['table'] . '_status'];
			} elseif (!empty($module_info[$result['table'] . '_status'])) {
				$data[$result['table'] . '_status'] = $module_info[$result['table'] . '_status'];
			} else {
				$data[$result['table'] . '_status'] = $result['status'];
			}

			if (isset($this->request->post[$result['table'] . '_image'])) {
				$data[$result['table'] . '_image'] = $this->request->post[$result['table'] . '_image'];
			} elseif (!empty($module_info[$result['table'] . '_image'])) {
				$data[$result['table'] . '_image'] = $module_info[$result['table'] . '_image'];
			} else {
				$data[$result['table'] . '_image'] = $result['image'];
			}

			if (isset($this->request->post[$result['table'] . '_priority'])) {
				$data[$result['table'] . '_priority'] = $this->request->post[$result['table'] . '_priority'];
			} elseif (!empty($module_info[$result['table'] . '_priority'])) {
				$data[$result['table'] . '_priority'] = $module_info[$result['table'] . '_priority'];
			} else {
				$data[$result['table'] . '_priority'] = $result['priority'];
			}
		}

		$this->load->model('setting/store');

		$data['stores_list'] = [];

		$data['stores_list'][] = [
			'store_id' => 0,
			'name'     => $this->language->get('text_default')
		];

		$stores = $this->model_setting_store->getStores();

		foreach ($stores as $store) {
			$data['stores_list'][] = [
				'store_id' => $store['store_id'],
				'name'     => $store['name']
			];
		}

		if (isset($this->request->post['stores'])) {
			$data['stores'] = $this->request->post['stores'];
		} elseif (!empty($module_info['stores'])) {
			$data['stores'] = $module_info['stores'];
		} else {
			$data['stores'] = [0];
		}

		if (isset($this->request->post['store_id'])) {
			$data['store_id'] = $this->request->post['store_id'];
		} elseif (!empty($module_info['store_id'])) {
			$data['store_id'] = $module_info['store_id'];
		} else {
			$data['store_id'] = 0;
		}

		$this->load->model('localisation/language');

		$data['languages_list'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['languages'])) {
			$data['languages'] = $this->request->post['languages'];
		} elseif (!empty($module_info['languages'])) {
			$data['languages'] = $module_info['languages'];
		} else {
			$data['languages'] = [2];
		}

		if (isset($this->request->post['language_id'])) {
			$data['language_id'] = $this->request->post['language_id'];
		} elseif (!empty($module_info['language_id'])) {
			$data['language_id'] = $module_info['language_id'];
		} else {
			$data['language_id'] = 2;
		}

		$data['data_feed'] = HTTP_CATALOG . 'index.php?route=extension/ocStore/feed/sitemap';

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/ocStore/feed/sitemap', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/ocStore/feed/sitemap')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}