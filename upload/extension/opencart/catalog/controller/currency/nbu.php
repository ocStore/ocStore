<?php
namespace Opencart\Catalog\Controller\Extension\Opencart\Currency;
/**
 * Class NBU
 *
 * @package Opencart\Catalog\Controller\Extension\Opencart\Currency
 */
class NBU extends \Opencart\System\Engine\Controller {
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

				$this->cache->delete('currency');
			}
		}
	}
}
