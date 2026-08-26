<?php
/**
 * @package   SeoPro
 * @copyright Copyright (c) 2026, ocStore (https://ocstore.com/)
 * @license   https://opensource.org/licenses/GPL-3.0
 */
namespace Opencart\System\Library\Seo;

class Manufacturer extends Handler {
	public function getKeys(): array {
		return ['manufacturer_id'];
	}

	public function getRoute(): string {
		return 'product/manufacturer.info';
	}
}
