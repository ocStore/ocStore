<?php
/**
 * @package   SeoPro
 * @copyright Copyright (c) 2026, ocStore (https://ocstore.com/)
 * @license   https://opensource.org/licenses/GPL-3.0
 */
namespace Opencart\System\Library\Seo;

class Information extends Handler {
	public function getKeys(): array {
		return ['information_id'];
	}

	public function getRoute(): string {
		return 'information/information';
	}
}
