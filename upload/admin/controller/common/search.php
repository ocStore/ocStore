<?php
namespace Opencart\Admin\Controller\Common;
/**
 * Class Search
 *
 * Can be loaded using $this->load->controller('common/search');
 *
 * @package Opencart\Admin\Controller\Common
 */
class Search extends \Opencart\System\Engine\Controller {
	/**
	 * Index
	 *
	 * @return string
	 */
	public function index(): string {
		if (empty($this->session->data['user_token']) || !$this->user->hasPermission('access', 'common/search')) {
			return '';
		}

		$this->load->language('common/search');

		$data['options'] = [
			[
				'code'        => 'catalog',
				'icon'        => 'fa-solid fa-book',
				'text'        => $this->language->get('text_catalog'),
				'placeholder' => $this->language->get('text_catalog_placeholder')
			],
			[
				'code'        => 'customer',
				'icon'        => 'fa-solid fa-users',
				'text'        => $this->language->get('text_customers'),
				'placeholder' => $this->language->get('text_customers_placeholder')
			],
			[
				'code'        => 'order',
				'icon'        => 'fa-solid fa-credit-card',
				'text'        => $this->language->get('text_orders'),
				'placeholder' => $this->language->get('text_orders_placeholder')
			]
		];

		$data['search'] = $this->url->link('common/search.search', 'user_token=' . $this->session->data['user_token'], true);

		return $this->load->view('common/search', $data);
	}

	/**
	 * Search
	 *
	 * @return void
	 */
	public function search(): void {
		$this->load->language('common/search');

		$json = [];

		if (isset($this->request->get['filter_name'])) {
			$filter_name = (string)$this->request->get['filter_name'];
		} else {
			$filter_name = '';
		}

		if (isset($this->request->get['filter_option'])) {
			$filter_option = (string)$this->request->get['filter_option'];
		} else {
			$filter_option = 'catalog';
		}

		if (!oc_validate_length($filter_name, 1, 128)) {
			$json['error'] = $this->language->get('error_name');
		}

		if (!$json) {
			$this->load->model('common/search');
			$this->load->model('tool/image');

			$data['groups'] = [];

			switch ($filter_option) {
				case 'customer':
					$results = $this->model_common_search->getCustomers($filter_name);

					$customers = [];

					foreach ($results as $result) {
						$customers[] = [
							'name'  => $result['name'],
							'note'  => $result['email'],
							'thumb' => '',
							'icon'  => 'fa-solid fa-user',
							'href'  => $this->url->link('customer/customer.form', 'user_token=' . $this->session->data['user_token'] . '&customer_id=' . $result['customer_id'])
						];
					}

					$data['groups'][] = [
						'name'    => $this->language->get('text_customers'),
						'results' => $customers
					];
					break;
				case 'order':
					$results = $this->model_common_search->getOrders($filter_name);

					$orders = [];

					foreach ($results as $result) {
						$orders[] = [
							'name'  => sprintf($this->language->get('text_order_id'), $result['order_id']) . ($result['customer'] != ' ' ? ' — ' . $result['customer'] : ''),
							'note'  => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
							'thumb' => '',
							'icon'  => 'fa-solid fa-credit-card',
							'href'  => $this->url->link('sale/order.info', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $result['order_id'])
						];
					}

					$data['groups'][] = [
						'name'    => $this->language->get('text_orders'),
						'results' => $orders
					];
					break;
				default:
					$results = $this->model_common_search->getProducts($filter_name);

					$products = [];

					foreach ($results as $result) {
						$products[] = [
							'name'  => $result['name'],
							'note'  => $result['model'],
							'thumb' => $this->thumb($result['image']),
							'icon'  => '',
							'href'  => $this->url->link('catalog/product.form', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $result['product_id'])
						];
					}

					$data['groups'][] = [
						'name'    => $this->language->get('text_products'),
						'results' => $products
					];

					$results = $this->model_common_search->getCategories($filter_name);

					$categories = [];

					foreach ($results as $result) {
						$categories[] = [
							'name'  => $result['name'],
							'note'  => '',
							'thumb' => $this->thumb($result['image']),
							'icon'  => '',
							'href'  => $this->url->link('catalog/category.form', 'user_token=' . $this->session->data['user_token'] . '&category_id=' . $result['category_id'])
						];
					}

					$data['groups'][] = [
						'name'    => $this->language->get('text_categories'),
						'results' => $categories
					];

					$results = $this->model_common_search->getManufacturers($filter_name);

					$manufacturers = [];

					foreach ($results as $result) {
						$manufacturers[] = [
							'name'  => $result['name'],
							'note'  => '',
							'thumb' => $this->thumb($result['image']),
							'icon'  => '',
							'href'  => $this->url->link('catalog/manufacturer.form', 'user_token=' . $this->session->data['user_token'] . '&manufacturer_id=' . $result['manufacturer_id'])
						];
					}

					$data['groups'][] = [
						'name'    => $this->language->get('text_manufacturers'),
						'results' => $manufacturers
					];
					break;
			}

			$data['text_no_results'] = $this->language->get('text_no_results');

			$json['results'] = $this->load->view('common/search_result', $data);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * Thumb
	 *
	 * @param string $image
	 *
	 * @return string
	 */
	private function thumb(string $image): string {
		if ($image && is_file(DIR_IMAGE . html_entity_decode($image, ENT_QUOTES, 'UTF-8'))) {
			return $this->model_tool_image->resize($image, 30, 30);
		}

		return $this->model_tool_image->resize('no_image.png', 30, 30);
	}
}
