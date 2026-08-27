<?php
/**
 * @package   SeoPro
 *
 * @copyright Copyright (c) 2026, ocStore (https://ocstore.com/)
 * @license   https://opensource.org/licenses/GPL-3.0
 */
namespace Opencart\System\Library\Seo;

class Topic extends Handler {
	public function getKeys(): array {
		return ['topic_id'];
	}

	public function getRoute(): string {
		return 'cms/blog';
	}

	public function encode(array $data): ?array {
		if (!isset($data['topic_id'])) {
			return null;
		}

		$queries = [];

		foreach (explode('_', (string)$data['topic_id']) as $id) {
			$queries[] = 'topic_id=' . (int)$id;
		}

		unset($data['topic_id']);

		return ['queries' => $queries, 'data' => $data, 'postfix' => false];
	}
}
