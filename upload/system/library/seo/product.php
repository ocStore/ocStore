<?php
/**
 * @package   SeoPro
 *
 * @copyright Copyright (c) 2026, ocStore (https://ocstore.com/)
 * @license   https://opensource.org/licenses/GPL-3.0
 */
namespace Opencart\System\Library\Seo;

class Product extends Handler {
	public function getKeys(): array {
		return ['product_id'];
	}

	public function getRoute(): string {
		return 'product/product';
	}

	public function decode(array $get): ?array {
		if (!isset($get['product_id'])) {
			return null;
		}

		unset($get['path']);

		$path = $this->seo->getCategoryByProduct((int)$get['product_id']);

		if ($path) {
			$get['path'] = $path;
		}

		$get['route'] = $this->getRoute();

		return $get;
	}

	public function encode(array $data): ?array {
		if (!isset($data['product_id'])) {
			return null;
		}

		$product_id = (int)$data['product_id'];
		$path = '';

		if (isset($data['path']) || $this->config->get('config_seo_url_include_path')) {
			$path = $this->seo->getCategoryByProduct($product_id);
		}

		$kept = $this->seo->keepAllowed($data);

		$queries = [];

		if ($path && $this->config->get('config_seo_url_include_path')) {
			$queries[] = 'path=' . $path;
		}

		$queries[] = 'product_id=' . $product_id;

		return ['queries' => $queries, 'data' => $kept, 'postfix' => true];
	}
}
