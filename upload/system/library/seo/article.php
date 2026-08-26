<?php
/**
 * @package   SeoPro
 * @copyright Copyright (c) 2026, ocStore (https://ocstore.com/)
 * @license   https://opensource.org/licenses/GPL-3.0
 */
namespace Opencart\System\Library\Seo;

class Article extends Handler {
	public function getKeys(): array {
		return ['article_id'];
	}

	public function getRoute(): string {
		return 'cms/blog.info';
	}

	public function encode(array $data): ?array {
		if (!isset($data['article_id'])) {
			return null;
		}

		$article_id = (int)$data['article_id'];
		$topic_id = '';

		if (isset($data['topic_id']) || $this->config->get('config_seo_url_include_path')) {
			$topic_id = $this->seo->getTopicByArticle($article_id);
		}

		$kept = $this->seo->keepAllowed($data);

		$queries = [];

		if ($topic_id && $this->config->get('config_seo_url_include_path')) {
			foreach (explode('_', (string)$topic_id) as $id) {
				$queries[] = 'topic_id=' . (int)$id;
			}
		}

		$queries[] = 'article_id=' . $article_id;

		return ['queries' => $queries, 'data' => $kept, 'postfix' => true];
	}
}
