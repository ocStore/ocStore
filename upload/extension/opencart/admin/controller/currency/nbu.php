<?php
namespace Opencart\Admin\Controller\Extension\Opencart\Currency;
/**
 * Class NBU
 *
 * @package Opencart\Admin\Controller\Extension\Opencart\Currency
 */
class NBU extends \Opencart\System\Engine\Controller {
	/**
	 * Index
	 *
	 * @return void
	 */
	public function index(): void {
		$this->load->language('extension/opencart/currency/nbu');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=currency')
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/opencart/currency/nbu', 'user_token=' . $this->session->data['user_token'])
		];

		$data['save'] = $this->url->link('extension/opencart/currency/nbu.save', 'user_token=' . $this->session->data['user_token']);
		$data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=currency');

		$data['currency_nbu_status'] = $this->config->get('currency_nbu_status');

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/opencart/currency/nbu', $data));
	}

	/**
	 * Save
	 *
	 * @return void
	 */
	public function save(): void {
		$this->load->language('extension/opencart/currency/nbu');

		$json = [];

		if (!$this->user->hasPermission('modify', 'extension/opencart/currency/nbu')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			// Setting
			$this->load->model('setting/setting');

			$this->model_setting_setting->editSetting('currency_nbu', $this->request->post);

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * Currency
	 *
	 * @param string $default
	 *
	 * @return void
	 */
	public function currency(string $default = ''): void {
		if ($this->config->get('currency_nbu_status')) {
			$curl = curl_init();

			curl_setopt($curl, CURLOPT_URL, 'https://bank.gov.ua/NBUStatService/v1/statdirectory/exchange');
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
			curl_setopt($curl, CURLOPT_TIMEOUT, 30);

			$response = curl_exec($curl);

			$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

			unset($curl);

			if ($status == 200) {
				$dom = new \DOMDocument('1.0', 'UTF-8');
				$dom->loadXml($response);

				// Compile all the rates into an array. The NBU quotes how many hryvnias one unit of a currency costs.
				$currencies = [];

				$currencies['UAH'] = 1.0000;

				foreach ($dom->getElementsByTagName('currency') as $currency) {
					$code = $currency->getElementsByTagName('cc')->item(0);
					$rate = $currency->getElementsByTagName('rate')->item(0);

					if ($code && $rate) {
						$currencies[$code->nodeValue] = (float)str_replace(',', '.', (string)$rate->nodeValue);
					}
				}

				if (!$default) {
					$default = 'UAH';
				}

				if (isset($currencies[$default])) {
					$value = $currencies[$default];
				} else {
					$value = $currencies['UAH'];
				}

				if (count($currencies) > 1) {
					$this->load->model('localisation/currency');

					$results = $this->model_localisation_currency->getCurrencies();

					foreach ($results as $result) {
						if (isset($currencies[$result['code']])) {
							$from = $currencies['UAH'];
							$to = $currencies[$result['code']];

							$this->model_localisation_currency->editValueByCode($result['code'], $value * ($from / $to));
						}
					}

					$this->model_localisation_currency->editValueByCode($default, 1.00000);
				}
			}
		}

		$this->cache->delete('currency');
	}
}
