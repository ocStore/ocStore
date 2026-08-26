<?php
namespace Opencart\Catalog\Controller\Extension\Opencart\Module;
/**
 * Class FeaturedProduct
 *
 * @package Opencart\Catalog\Controller\Extension\Opencart\Module
 */
class FeaturedProduct extends \Opencart\System\Engine\Controller {
	/**
	 * Index
	 *
	 * @param array<string, mixed> $setting array of data
	 *
	 * @return string
	 */
	public function index(array $setting): string {
		$this->load->language('extension/opencart/module/featured_product');

		// In a side column the products go one under another, otherwise they go in a grid
		$position = (string)($setting['position'] ?? '');

		$data['axis'] = substr($position, 0, 6) == 'column' ? 'vertical' : 'horizontal';

		if (empty($setting['limit'])) {
			$setting['limit'] = 4;
		}

		// Product
		$this->load->model('catalog/product');

		if (isset($this->request->get['manufacturer_id'])) {
			$results = $this->model_catalog_product->getRelatedByManufacturer((int)$this->request->get['manufacturer_id'], (int)$setting['limit']);
		} elseif (isset($this->request->get['path'])) {
			$parts = explode('_', (string)$this->request->get['path']);

			$results = $this->model_catalog_product->getRelatedByCategory((int)end($parts), (int)$setting['limit']);
		} else {
			$results = [];
		}

		$data['products'] = [];

		// Image
		$this->load->model('tool/image');

		foreach ($results as $product) {
			if ($product['image']) {
				$image = $this->model_tool_image->resize(html_entity_decode($product['image'], ENT_QUOTES, 'UTF-8'), $setting['width'], $setting['height']);
			} else {
				$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
			}

			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				$price = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
			} else {
				$price = false;
			}

			if ((float)$product['special']) {
				$special = $this->currency->format($this->tax->calculate($product['special'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
			} else {
				$special = false;
			}

			if ($this->config->get('config_tax')) {
				$tax = $this->currency->format((float)$product['special'] ? $product['special'] : $product['price'], $this->session->data['currency']);
			} else {
				$tax = false;
			}

			$product_data = [
				'product_id'  => $product['product_id'],
				'thumb'       => $image,
				'name'        => $product['name'],
				'description' => oc_substr(trim(strip_tags(html_entity_decode($product['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('config_product_description_length')) . '..',
				'price'       => $price,
				'special'     => $special,
				'tax'         => $tax,
				'minimum'     => $product['minimum'] > 0 ? $product['minimum'] : 1,
				'rating'      => $product['rating'],
				'href'        => $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $product['product_id'])
			];

			$data['products'][] = $this->load->controller('product/thumb', $product_data);
		}

		if ($data['products']) {
			return $this->load->view('extension/opencart/module/featured_product', $data);
		} else {
			return '';
		}
	}
}
