<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

namespace Opencart\Admin\Controller\Extension\ocStore\Feed;

if (!defined('VERSION')) {
	header('Refresh: 1; URL=/');
	exit('ЗАПРЫШЧАЮ!');
}

class YandexTurbo extends \Opencart\System\Engine\Controller {
	private array $allowed = ['RUR', 'RUB', 'USD', 'BYN', 'BYR', 'KZT', 'EUR', 'UAH'];
	private array $error = [];

	public function index(): void {
		$this->load->language('extension/ocStore/feed/yandex_turbo');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('feed_yandex_turbo', $this->request->post);

			//$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=feed', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=feed', true)
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/ocStore/feed/yandex_turbo', 'user_token=' . $this->session->data['user_token'], true)
		];

		$data['action'] = $this->url->link('extension/ocStore/feed/yandex_turbo', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=feed', true);

		$this->load->model('localisation/currency');
		$currencies = $this->model_localisation_currency->getCurrencies();
		$allowed_currencies = array_flip($this->allowed);
		$data['currencies'] = array_intersect_key($currencies, $allowed_currencies);

		if (isset($this->request->post['feed_yandex_turbo_status'])) {
			$data['feed_yandex_turbo_status'] = $this->request->post['feed_yandex_turbo_status'];
		} else {
			$data['feed_yandex_turbo_status'] = $this->config->get('feed_yandex_turbo_status');
		}

		if (isset($this->request->post['feed_yandex_turbo_currency'])) {
			$data['feed_yandex_turbo_currency'] = $this->request->post['feed_yandex_turbo_currency'];
		} else {
			$data['feed_yandex_turbo_currency'] = $this->config->get('feed_yandex_turbo_currency');
		}

		$data['entry_currency'] = $this->language->get('entry_currency');
		$data['entry_made'] = $this->language->get('entry_made');

		$data['data_feed'] = HTTP_CATALOG . 'index.php?route=extension/ocStore/feed/yandex_turbo';

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/ocStore/feed/yandex_turbo', $data));
	}

	protected function validate(): bool {
		if (!$this->user->hasPermission('modify', 'extension/ocStore/feed/yandex_turbo')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function save(): void {
		$this->load->language('extension/ocStore/feed/yandex_turbo');

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

			$this->model_setting_setting->editSetting('feed_yandex_turbo', $this->request->post);

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}