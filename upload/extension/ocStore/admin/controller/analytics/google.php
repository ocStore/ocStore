<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

namespace Opencart\Admin\Controller\Extension\ocStore\Analytics;

if (!defined('VERSION')) {
	header('Refresh: 1; URL=/');
	exit('ЗАПРЫШЧАЮ!');
}

class Google extends \Opencart\System\Engine\Controller {
	private array $error = [];

	public function index(): void {
		$this->load->language('extension/ocStore/analytics/google');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('analytics_google', $this->request->post, $this->request->get['store_id']);

			//$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=analytics', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['code'])) {
			$data['error_code'] = $this->error['code'];
		} else {
			$data['error_code'] = '';
		}

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=analytics', true)
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/ocStore/analytics/google', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $this->request->get['store_id'], true)
		];

		$data['action'] = $this->url->link('extension/ocStore/analytics/google', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $this->request->get['store_id'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=analytics', true);

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->post['analytics_google_code'])) {
			$data['analytics_google_code'] = $this->request->post['analytics_google_code'];
		} else {
			$data['analytics_google_code'] = $this->model_setting_setting->getValue('analytics_google_code', $this->request->get['store_id']);
		}

		if (isset($this->request->post['analytics_google_status'])) {
			$data['analytics_google_status'] = $this->request->post['analytics_google_status'];
		} else {
			$data['analytics_google_status'] = $this->model_setting_setting->getValue('analytics_google_status', $this->request->get['store_id']);
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/ocStore/analytics/google', $data));
	}

	protected function validate(): bool {
		if (!$this->user->hasPermission('modify', 'extension/ocStore/analytics/google')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!empty($this->request->post['analytics_google_status']) && empty($this->request->post['analytics_google_code'])) {
			$this->error['code'] = $this->language->get('error_code');
		}

		if (!isset($this->request->get['store_id'])) {
			$this->request->get['store_id'] = 0;
		}

		return !$this->error;
	}

	public function save(): void {
		$this->load->language('extension/ocStore/analytics/google');

		$json = [];

		if ($this->request->server['REQUEST_METHOD'] != 'POST') {
			$json['error']['warning'] = $this->language->get('error_permission');
		}

		if (!$this->validate()) {
			foreach ($this->error as $key => $result) {
				$json['error'][$key] = $result;
			}
		}

		if (!$json) {
			$this->load->model('setting/setting');

			$this->model_setting_setting->editSetting('analytics_google', $this->request->post, $this->request->get['store_id']);

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}