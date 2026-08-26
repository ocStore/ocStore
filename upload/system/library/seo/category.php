<?php
/**
 * @package   SeoPro
 * @copyright Copyright (c) 2026, ocStore (https://ocstore.com/)
 * @license   https://opensource.org/licenses/GPL-3.0
 */
namespace Opencart\System\Library\Seo;

class Category extends Handler {
	public function getKeys(): array {
		return ['path'];
	}

	public function getRoute(): string {
		return 'product/category';
	}

	public function encode(array $data): ?array {
		if (!isset($data['path'])) {
			return null;
		}

		$categories = explode('_', (string)$data['path']);

		$path = $this->seo->getPathByCategory((int)end($categories));

		unset($data['path']);

		return ['queries' => ['path=' . $path], 'data' => $data, 'postfix' => false];
	}
}
