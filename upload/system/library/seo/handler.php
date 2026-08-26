<?php
/**
 * @package   SeoPro
 * @copyright Copyright (c) 2026, ocStore (https://ocstore.com/)
 * @license   https://opensource.org/licenses/GPL-3.0
 */
namespace Opencart\System\Library\Seo;

abstract class Handler implements HandlerInterface {
	protected \Opencart\System\Engine\Registry $registry;
	protected \Opencart\System\Library\SeoPro $seo;
	protected ?object $config = null;
	protected ?object $db = null;

	public function __construct(\Opencart\System\Engine\Registry $registry, \Opencart\System\Library\SeoPro $seo) {
		$this->registry = $registry;
		$this->seo = $seo;
		$this->config = $registry->get('config');
		$this->db = $registry->get('db');
	}

	public function getKeys(): array {
		return [];
	}

	public function getRoute(): string {
		return '';
	}

	public function decode(array $get): ?array {
		if (isset($get['route'])) {
			return null;
		}

		foreach ($this->getKeys() as $key) {
			if (isset($get[$key])) {
				$get['route'] = $this->getRoute();

				return $get;
			}
		}

		return null;
	}

	public function encode(array $data): ?array {
		$queries = [];

		foreach ($this->getKeys() as $key) {
			if (!isset($data[$key])) {
				return null;
			}

			$queries[] = $key . '=' . (int)$data[$key];

			unset($data[$key]);
		}

		return ['queries' => $queries, 'data' => $data, 'postfix' => true];
	}
}
