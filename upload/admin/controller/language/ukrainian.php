<?php
namespace Opencart\Admin\Controller\Extension\LanguageUkrainian\Language;
class Ukrainian extends \Opencart\System\Engine\Controller {

	private string $extensionPath              = 'extension/language_ukrainian/language/ukrainian';
	private string $extensionDescription       = 'Українська локалізація';
	private bool $extensionCopy                = true;
	private bool $extensionUninstallComplete   = false;
	private bool $extensionMaintenance         = true;

	public function index(): void {
		$this->load->language($this->extensionPath);

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=language')
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->extensionPath, 'user_token=' . $this->session->data['user_token'])
		];

		$data['save'] = $this->url->link($this->extensionPath . '.save', 'user_token=' . $this->session->data['user_token']);
		$data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=language');

		$data['language_ukrainian_status'] = $this->config->get('language_ukrainian_status');

		$this->load->model('localisation/language');

		$language_info = $this->model_localisation_language->getLanguageByCode('uk-ua');

		if ($language_info) {
			$data['entry_language_name'] = $language_info['name'];
			$data['language_ukrainian_ua_status'] = $language_info['status'];
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->extensionPath, $data));
	}

	public function save(): void {
		$this->load->language($this->extensionPath);

		if (!$this->user->hasPermission('modify', $this->extensionPath)) {

			$json['error'] = $this->language->get('error_permission');
				
			if ($this->extensionMaintenance) {
				$this->log->write($json['error']);				
			}

			return;
		}

		$json = [];

		if (!$json) {
			$this->load->model('setting/setting');

			$this->model_setting_setting->editSetting('language_ukrainian', $this->request->post);

			$this->load->model('localisation/language');

			$language_info = $this->model_localisation_language-> getLanguageByCode('uk-ua');

			$language_info['status'] = (empty($this->request->post['language_ukrainian_ua_status']) ? '0' : '1');

			$this->model_localisation_language->editLanguage($language_info['language_id'], $language_info);

			$json['success'] = $this->language->get('text_success');
		}

		if ($this->extensionMaintenance) {
			$this->log->write('Налаштування доповнення ' . $this->extensionDescription . ' збережені');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function install(): void {
		$this->load->language($this->extensionPath);

		if (!$this->user->hasPermission('modify', 'extension/language')) {

			$json['error'] = $this->language->get('error_permission');
				
			if ($this->extensionMaintenance) {
				$this->log->write('Користувач не має прав для змінення налаштувань доповнень типу Переклади');				
			}

			return;
		}

		$this->load->model('localisation/language');

		$language_info = $this->model_localisation_language->getLanguageByCode('uk-ua');

		if (!$language_info) {
			$language_data = [
				'name'       => 'Українська',
				'code'       => 'uk-ua',
				'locale'     => 'uk_UA.UTF-8,uk_UA,ukrainian',
				'extension'  => 'language_ukrainian',
				'status'     => 1,
				'sort_order' => 1
			];

			$this->load->model('localisation/language');

			$this->model_localisation_language->addLanguage($language_data);

			if ($this->extensionMaintenance) {
				$this->log->write('Додано дані мовної локалізації для української');
			}
		} else {
			$this->load->model('localisation/language');

			$this->model_localisation_language->editLanguage($language_info['language_id'], $language_info + ['extension' => 'oc_language_ukrainian']);

			if ($this->extensionMaintenance) {
				$this->log->write('Дані мовної локалізації для української вже присутні');		
			}
		}
		
		if ($this->extensionCopy) {
			$extension_folder = implode('', glob(DIR_EXTENSION));

			$source = $extension_folder . 'language_ukrainian/extension/opencart/';
			$destination = $extension_folder . 'opencart/';

			if ($this->extensionMaintenance) {
				$this->log->write('Папка джерела локалізації extension/opencart: (' . $source . ')');
				$this->log->write('Папка призначення локалізації extension/opencart: (' . $destination . ')');
			}

			if ((is_dir($source)) && (is_dir($destination))) {
				$this->custom_copy($source, $destination);
			} else {
				$this->log->write('Увага! Відсутня папка джерела локалізації або папка її призначення!');
			}
		}
		
		if ($this->extensionMaintenance) {	
			$this->log->write($this->extensionDescription . ' успішно встановлена');
		}


// Translations

        $query = $this->db->query("SELECT `language_id` FROM `" . DB_PREFIX . "language` WHERE `code` = 'uk-ua'");

        if ($query->row) {
            $language_id = $query->row['language_id'];


            // Перевіряємо чи є валюта Гривня
            $query = $this->db->query("SELECT `currency_id` FROM `" . DB_PREFIX . "currency` WHERE `code` = 'UAH'");

            if (!$query->row) {
                // Додаємо UAH ящо немає
                $this->db->query("INSERT INTO `" . DB_PREFIX . "currency` (`title`, `code`, `symbol_left`, `symbol_right`, `decimal_place`, `value`, `status`, `date_modified`) VALUES ('Гривня', 'UAH', '', ' грн.', '2', 1.00000000, 1, NOW());");

                $currency_id = $this->db->getLastId();
            } else {
                $currency_id = $query->row['currency_id'];
            }

            // Переклад групи покупців за замовчуванням
            $query = $this->db->query("SELECT `customer_group_id`, `name` FROM `" . DB_PREFIX . "customer_group_description` WHERE `language_id` = '1'");

            if ($query->rows) {
                foreach ($query->rows as $row) {
                    if ('Default' == $row['name']) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "customer_group_description` SET `name` = '" . $this->db->escape('Основна') . "' WHERE `customer_group_id` = '" . (int)$row['customer_group_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }
                }
            }

            // Переклад одиниць виміру
            $query = $this->db->query("SELECT `length_class_id`, `title` FROM `" . DB_PREFIX . "length_class_description` WHERE `language_id` = '1'");

            if ($query->rows) {
                foreach ($query->rows as $row) {
                    if ('Centimeter' == trim($row['title'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "length_class_description` SET `title` = '" . $this->db->escape('Сантиметр') . "', `unit` = '" . $this->db->escape('см') . "' WHERE `length_class_id` = '" . (int)$row['length_class_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }

                    if ('Millimeter' == trim($row['title'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "length_class_description` SET `title` = '" . $this->db->escape('Міліметр') . "', `unit` = '" . $this->db->escape('мм') . "' WHERE `length_class_id` = '" . (int)$row['length_class_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }

                    if ('Inch' == trim($row['title'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "length_class_description` SET `title` = '" . $this->db->escape('Дюйм') . "', `unit` = '" . $this->db->escape('in') . "' WHERE `length_class_id` = '" . (int)$row['length_class_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }
                }
            }

            $this->cache->delete('length_class');

            // Переклад назв статей
            $query = $this->db->query("SELECT `information_id`, `title` FROM `" . DB_PREFIX . "information_description` WHERE `language_id` = '1'");

            if ($query->rows) {
                foreach ($query->rows as $row) {
                    if ('About Us' == trim($row['title'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "information_description` SET `title` = '" . $this->db->escape('Про магазин') . "' WHERE `information_id` = '" . (int)$row['information_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }

                    if ('Terms &amp; Conditions' == trim($row['title'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "information_description` SET `title` = '" . $this->db->escape('Умови оформлення замовлення') . "' WHERE `information_id` = '" . (int)$row['information_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }

                    if ('Privacy Policy' == trim($row['title'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "information_description` SET `title` = '" . $this->db->escape('Умови використання сайту') . "' WHERE `information_id` = '" . (int)$row['information_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }

                    if ('Delivery Information' == trim($row['title'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "information_description` SET `title` = '" . $this->db->escape('Інформація про доставку') . "' WHERE `information_id` = '" . (int)$row['information_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }
                }
            }

            $this->cache->delete('information');


            // Переклад опцій
            $query = $this->db->query("SELECT `option_id`, `name` FROM `" . DB_PREFIX . "option_description` WHERE `language_id` = '1'");

            if ($query->rows) {
                foreach ($query->rows as $row) {
                    if ('Radio' == trim($row['name'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "option_description` SET `name` = '" . $this->db->escape('Перемикач') . "' WHERE `option_id` = '" . (int)$row['option_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }

                    if ('Checkbox' == trim($row['name'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "option_description` SET `name` = '" . $this->db->escape('Прапорець') . "' WHERE `option_id` = '" . (int)$row['option_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }

                    if ('Text' == trim($row['name'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "option_description` SET `name` = '" . $this->db->escape('Текст') . "' WHERE `option_id` = '" . (int)$row['option_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }

                    if ('Textarea' == trim($row['name'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "option_description` SET `name` = '" . $this->db->escape('Текстова область') . "' WHERE `option_id` = '" . (int)$row['option_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }

                    if ('Date' == trim($row['name'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "option_description` SET `name` = '" . $this->db->escape('Дата') . "' WHERE `option_id` = '" . (int)$row['option_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }

                    if ('File' == trim($row['name'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "option_description` SET `name` = '" . $this->db->escape('Файл') . "' WHERE `option_id` = '" . (int)$row['option_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }

                    if ('Select' == trim($row['name'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "option_description` SET `name` = '" . $this->db->escape('Список') . "' WHERE `option_id` = '" . (int)$row['option_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }

                    if ('Time' == trim($row['name'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "option_description` SET `name` = '" . $this->db->escape('Час') . "' WHERE `option_id` = '" . (int)$row['option_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }

                    if ('Date &amp; Time' == trim($row['name'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "option_description` SET `name` = '" . $this->db->escape('Дата та час') . "' WHERE `option_id` = '" . (int)$row['option_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }

                    if ('Delivery Date' == trim($row['name'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "option_description` SET `name` = '" . $this->db->escape('Дата доставки') . "' WHERE `option_id` = '" . (int)$row['option_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }

                    if ('Size' == trim($row['name'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "option_description` SET `name` = '" . $this->db->escape('Розмір') . "' WHERE `option_id` = '" . (int)$row['option_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }
                }
            }

            // Переклад статусів замовлень
            $query = $this->db->query("SELECT `order_status_id`, `name` FROM `" . DB_PREFIX . "order_status` WHERE `language_id` = '1'");

            if ($query->rows) {
                foreach ($query->rows as $row) {
                    if ('Pending' == trim($row['name'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "order_status` SET `name` = '" . $this->db->escape('Очікування') . "' WHERE `order_status_id` = '" . (int)$row['order_status_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }

                    if ('Processing' == trim($row['name'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "order_status` SET `name` = '" . $this->db->escape('В обробці') . "' WHERE `order_status_id` = '" . (int)$row['order_status_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }

                    if ('Shipped' == trim($row['name'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "order_status` SET `name` = '" . $this->db->escape('Доставлено') . "' WHERE `order_status_id` = '" . (int)$row['order_status_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }

                    if ('Complete' == trim($row['name'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "order_status` SET `name` = '" . $this->db->escape('Завершено') . "' WHERE `order_status_id` = '" . (int)$row['order_status_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }

                    if ('Canceled' == trim($row['name'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "order_status` SET `name` = '" . $this->db->escape('Скасовано') . "' WHERE `order_status_id` = '" . (int)$row['order_status_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }

                    if ('Denied' == trim($row['name'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "order_status` SET `name` = '" . $this->db->escape('Помилкове') . "' WHERE `order_status_id` = '" . (int)$row['order_status_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }

                    if ('Canceled Reversal' == trim($row['name'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "order_status` SET `name` = '" . $this->db->escape('Повністю скасовано') . "' WHERE `order_status_id` = '" . (int)$row['order_status_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }

                    if ('Failed' == trim($row['name'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "order_status` SET `name` = '" . $this->db->escape('Невдале') . "' WHERE `order_status_id` = '" . (int)$row['order_status_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }

                    if ('Refunded' == trim($row['name'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "order_status` SET `name` = '" . $this->db->escape('Відшкодоване') . "' WHERE `order_status_id` = '" . (int)$row['order_status_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }

                    if ('Reversed' == trim($row['name'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "order_status` SET `name` = '" . $this->db->escape('Повністю змінене') . "' WHERE `order_status_id` = '" . (int)$row['order_status_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }

                    if ('Chargeback' == trim($row['name'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "order_status` SET `name` = '" . $this->db->escape('Повне повернення') . "' WHERE `order_status_id` = '" . (int)$row['order_status_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }

                    if ('Expired' == trim($row['name'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "order_status` SET `name` = '" . $this->db->escape('Прострочене') . "' WHERE `order_status_id` = '" . (int)$row['order_status_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }

                    if ('Processed' == trim($row['name'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "order_status` SET `name` = '" . $this->db->escape('Оброблено') . "' WHERE `order_status_id` = '" . (int)$row['order_status_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }

                    if ('Voided' == trim($row['name'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "order_status` SET `name` = '" . $this->db->escape('Анульовано') . "' WHERE `order_status_id` = '" . (int)$row['order_status_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }
                }
            }

            $this->cache->delete('order_status');

            // Переклад статусів повернення
            $query = $this->db->query("SELECT `return_status_id`, `name` FROM `" . DB_PREFIX . "return_status` WHERE `language_id` = '1'");

            if ($query->rows) {
                foreach ($query->rows as $row) {
                    if ('Pending' == trim($row['name'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "return_status` SET `name` = '" . $this->db->escape('В очікуванні') . "' WHERE `return_status_id` = '" . (int)$row['return_status_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }

                    if ('Complete' == trim($row['name'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "return_status` SET `name` = '" . $this->db->escape('Виконано') . "' WHERE `return_status_id` = '" . (int)$row['return_status_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }

                    if ('Awaiting Products' == trim($row['name'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "return_status` SET `name` = '" . $this->db->escape('Очікування товару') . "' WHERE `return_status_id` = '" . (int)$row['return_status_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }
                }
            }

            // Переклад операцій повернення
            $query = $this->db->query("SELECT `return_action_id`, `name` FROM `" . DB_PREFIX . "return_action` WHERE `language_id` = '1'");

            if ($query->rows) {
                foreach ($query->rows as $row) {
                    if ('Refunded' == trim($row['name'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "return_action` SET `name` = '" . $this->db->escape('Відшкодовано') . "' WHERE `return_action_id` = '" . (int)$row['return_action_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }

                    if ('Credit Issued' == trim($row['name'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "return_action` SET `name` = '" . $this->db->escape('Повернення коштів') . "' WHERE `return_action_id` = '" . (int)$row['return_action_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }

                    if ('Replacement Sent' == trim($row['name'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "return_action` SET `name` = '" . $this->db->escape('Відправлено заміну') . "' WHERE `return_action_id` = '" . (int)$row['return_action_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }
                }
            }

            // Переклад причин повернення
            $query = $this->db->query("SELECT `return_reason_id`, `name` FROM `" . DB_PREFIX . "return_reason` WHERE `language_id` = '1'");

            if ($query->rows) {
                foreach ($query->rows as $row) {
                    if ('Dead On Arrival' == trim($row['name'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "return_reason` SET `name` = '" . $this->db->escape('Отримано/доставлено несправним (зламаним)') . "' WHERE `return_reason_id` = '" . (int)$row['return_reason_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }

                    if ('Received Wrong Item' == trim($row['name'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "return_reason` SET `name` = '" . $this->db->escape('Отримано не той (помилковий) товар') . "' WHERE `return_reason_id` = '" . (int)$row['return_reason_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }

                    if ('Order Error' == trim($row['name'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "return_reason` SET `name` = '" . $this->db->escape('Помилкове замовлення') . "' WHERE `return_reason_id` = '" . (int)$row['return_reason_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }

                    if ('Faulty, please supply details' == trim($row['name'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "return_reason` SET `name` = '" . $this->db->escape('Несправний, будь ласка, вкажіть подробиці') . "' WHERE `return_reason_id` = '" . (int)$row['return_reason_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }

                    if ('Other, please supply details' == trim($row['name'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "return_reason` SET `name` = '" . $this->db->escape('Інше (інша причина), будь ласка, вкажіть/докладіть подробиці') . "' WHERE `return_reason_id` = '" . (int)$row['return_reason_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }
                }
            }

            // Переклад статусів наявності
            $query = $this->db->query("SELECT `stock_status_id`, `name` FROM `" . DB_PREFIX . "stock_status` WHERE `language_id` = '1'");

            if ($query->rows) {
                foreach ($query->rows as $row) {
                    if ('In Stock' == trim($row['name'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "stock_status` SET `name` = '" . $this->db->escape('В наявності') . "' WHERE `stock_status_id` = '" . (int)$row['stock_status_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }

                    if ('Pre-Order' == trim($row['name'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "stock_status` SET `name` = '" . $this->db->escape('Попереднє замовлення') . "' WHERE `stock_status_id` = '" . (int)$row['stock_status_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }

                    if ('Out Of Stock' == trim($row['name'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "stock_status` SET `name` = '" . $this->db->escape('Немає в наявності') . "' WHERE `stock_status_id` = '" . (int)$row['stock_status_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }

                    if ('2-3 Days' == trim($row['name'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "stock_status` SET `name` = '" . $this->db->escape('Очікування 2-3 дні') . "' WHERE `stock_status_id` = '" . (int)$row['stock_status_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }
                }
            }

            $this->cache->delete('stock_status');

            // Переклад тем подарункових сертифікатів
            $query = $this->db->query("SELECT `voucher_theme_id`, `name` FROM `" . DB_PREFIX . "voucher_theme_description` WHERE `language_id` = '1'");

            if ($query->rows) {
                foreach ($query->rows as $row) {
                    if ('Christmas' == trim($row['name'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "voucher_theme_description` SET `name` = '" . $this->db->escape('Новий Рік') . "' WHERE `voucher_theme_id` = '" . (int)$row['voucher_theme_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }

                    if ('Birthday' == trim($row['name'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "voucher_theme_description` SET `name` = '" . $this->db->escape('День Народження') . "' WHERE `voucher_theme_id` = '" . (int)$row['voucher_theme_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }

                    if ('General' == trim($row['name'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "voucher_theme_description` SET `name` = '" . $this->db->escape('Подарунок') . "' WHERE `voucher_theme_id` = '" . (int)$row['voucher_theme_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }
                }
            }

            $this->cache->delete('voucher_theme');

            // Переклад одиниць ваги
            $query = $this->db->query("SELECT `weight_class_id`, `title` FROM `" . DB_PREFIX . "weight_class_description` WHERE `language_id` = '1'");

            if ($query->rows) {
                foreach ($query->rows as $row) {
                    if ('Kilogram' == trim($row['title'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "weight_class_description` SET `title` = '" . $this->db->escape('Кілограм') . "' WHERE `weight_class_id` = '" . (int)$row['weight_class_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }

                    if ('Gram' == trim($row['title'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "weight_class_description` SET `title` = '" . $this->db->escape('Грам') . "' WHERE `weight_class_id` = '" . (int)$row['weight_class_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }

                    if ('Pound' == trim($row['title'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "weight_class_description` SET `title` = '" . $this->db->escape('Фунт') . "' WHERE `weight_class_id` = '" . (int)$row['weight_class_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }

                    if ('Ounce' == trim($row['title'])) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "weight_class_description` SET `title` = '" . $this->db->escape('Унція') . "' WHERE `weight_class_id` = '" . (int)$row['weight_class_id'] . "' AND `language_id` = '" . (int)$language_id . "'");
                    }
                }
            }

            $this->cache->delete('weight_class');

/*

            // Change default settings
            $this->model_setting_setting->editValue('config', 'config_country_id', (int)$country_id);
            $this->model_setting_setting->editValue('config', 'config_zone_id', (int)$zone_id);
            $this->model_setting_setting->editValue('config', 'config_language', 'uk-ua');
            $this->model_setting_setting->editValue('config', 'config_language_admin', 'uk-ua');
            $this->model_setting_setting->editValue('config', 'config_currency', 'UAH');

            // add all translations to the database
            $this->load->model('design/translation');

            $files = $this->glob_recursive(DIR_EXTENSION . $this->extension_id . '/catalog/language/uk-ua', '*.php');

            foreach ($files as $file) {
                if (is_file($file)) {
                    $_ = [];

                    require($file);

                    preg_match('/(.*)' . $this->extension_id . '\/catalog\/language\/uk-ua\/(.*)\.php/i', $file, $matches);

                    foreach ($_ as $key => $value) {
                        $data = array(
                            'store_id' => 0,
                            'language_id' => (int) $language_id,
                            'route' => 'extension/' . $this->extension_id . '/' . $matches[2],
                            'key' => $key,
                            'value' => $value
                        );

                        $query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "translation` WHERE `route` = '" . $this->db->escape((string)$data['route']) . "' AND `key` = '" . $this->db->escape((string)$data['key']) ."'");

                        if (!$query->rows) {
                            $this->model_design_translation->addTranslation($data);
                        }
                    }
                }
            }*/

		}
// END Translations

	}

	public function uninstall(): void {
		$this->load->language($this->extensionPath);

		if (!$this->user->hasPermission('modify', 'extension/language')) {

			$json['error'] = $this->language->get('error_permission');
				
			if ($this->extensionMaintenance) {
				$this->log->write('Користувач не має прав для змінення налаштувань доповнень типу Переклади');				
			}

			return;
		}

		$language_info = $this->model_localisation_language->getLanguageByCode('uk-ua');

		if (($language_info) && ($this->extensionUninstallComplete)) {
			$this->load->model('localisation/language');

			$this->model_localisation_language->deleteLanguage($language_info['language_id']);

			$this->log->write('Українська локалізація видалена');		

		} elseif ($language_info) {
			$this->load->model('localisation/language');

			$language_info = $this->model_localisation_language-> getLanguageByCode('uk-ua');

			$language_info['status'] = 0;

			$this->model_localisation_language->editLanguage($language_info['language_id'], $language_info);

			$this->log->write('Українська локалізація деактивована');		
		}

	}

	private function custom_copy(string $src, string $dst) : void {
		$dir = opendir($src); 
	  
		if(!is_dir($dst))
		{
			mkdir($dst, 0755);
		}

		while( $file = readdir($dir) ) { 

			if (( $file != '.' ) && ( $file != '..' )) {
				if ( is_dir($src . '/' . $file) ) {
					$this->custom_copy($src . '/' . $file, $dst . '/' . $file); 
				} else { 
					copy($src . '/' . $file, $dst . '/' . $file); 
				}
			}
		}

		closedir($dir);
	}
}
