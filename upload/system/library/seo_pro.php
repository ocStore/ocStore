<?php
/**
 * @package   SeoPro
 * @author    Oclabs
 * @copyright Copyright (c) 2017, Oclabs (https://www.oclabs.pro/)
 * @copyright Copyright (c) 2021, ocStore (https://ocstore.com/)
 * @license   https://opensource.org/licenses/GPL-3.0
 */
namespace Opencart\System\Library;

class SeoPro {
	private object $registry;
	private object $config;
	private $request;
	private $response;
	private $session;
	private $db;
	private $cache;
	private $url;
	private bool $ajax = false;
	private array $topic_tree = [];
	private array $category_tree = [];
	private array $keywords = [];
	private array $queries = [];
	private array $product_categories = [];
	private array $allowed_params = [];

	private const ENTITIES = ['path', 'product_id', 'manufacturer_id', 'information_id', 'article_id', 'topic_id', 'route'];

	public function __construct(\Opencart\System\Engine\Registry $registry) {
		$this->registry = $registry;
		$this->config = $registry->get('config');

		$this->detectAjax();

		if (!$this->config->get('config_seo_pro')) {
			return;
		}

		$this->request = $registry->get('request');
		$this->session = $registry->get('session');
		$this->response = $registry->get('response');
		$this->url = $registry->get('url');
		$this->db = $registry->get('db');
		$this->cache = $registry->get('cache');

		$this->detectPostfix();
		$this->detectLanguage();
		$this->initHelpers();

		if ($this->config->get('config_seopro_param_status')) {
			$params = preg_split('/\R/', (string)$this->config->get('config_seopro_params'), -1, PREG_SPLIT_NO_EMPTY);

			$this->allowed_params = array_map('trim', $params);
		}
	}

	public function prepareRoute(array $parts): array {
		$segments = array_values($parts);
		$total = count($segments);
		$leftover = [];
		$found = false;

		for ($i = 0; $i < $total; ) {
			$query = '';
			$length = 0;

			for ($j = $total; $j > $i; $j--) {
				$candidate = implode('/', array_slice($segments, $i, $j - $i));

				if ($this->config->get('config_seopro_lowercase')) {
					$candidate = oc_strtolower($candidate);
				}

				$query = $this->getQueryByKeyword($candidate);

				if ($query) {
					$length = $j - $i;

					break;
				}
			}

			if (!$query) {
				$leftover[] = $segments[$i];

				$i++;

				continue;
			}

			[$key, $value] = array_pad(explode('=', $query, 2), 2, '');

			if (!in_array($key, self::ENTITIES)) {
				return $parts;
			}

			$found = true;

			if ($key == 'path' && isset($this->request->get['path'])) {
				$this->request->get['path'] .= '_' . $value;
			} else {
				$this->request->get[$key] = $value;
			}

			$i += $length;
		}

		if (!$found && $leftover) {
			$this->request->get['route'] = $this->config->get('action_error');

			return [];
		}

		if (isset($this->request->get['product_id'])) {
			unset($this->request->get['path']);

			$path = $this->getCategoryByProduct((int)$this->request->get['product_id']);

			if ($path) {
				$this->request->get['path'] = $path;
			}

			$this->request->get['route'] = 'product/product';
		} elseif (isset($this->request->get['route'])) {
			return $leftover;
		} elseif (isset($this->request->get['path'])) {
			$this->request->get['route'] = 'product/category';
		} elseif (isset($this->request->get['manufacturer_id'])) {
			$this->request->get['route'] = 'product/manufacturer.info';
		} elseif (isset($this->request->get['information_id'])) {
			$this->request->get['route'] = 'information/information';
		} elseif (isset($this->request->get['article_id'])) {
			$this->request->get['route'] = 'cms/blog.info';
		} elseif (isset($this->request->get['topic_id'])) {
			$this->request->get['route'] = 'cms/blog';
		}

		return $leftover;
	}

	public function baseRewrite(array $data): array {
		$url = null;
		$postfix = null;

		unset($data['language']);

		$route = $data['route'] ?? '';

		switch ($route) {
			case 'product/product':
				if (isset($data['product_id'])) {
					$product_id = $data['product_id'];
					$path = '';

					if (isset($data['path']) || $this->config->get('config_seo_url_include_path')) {
						$path = $this->getCategoryByProduct((int)$product_id);
					}

					$kept = $this->keepAllowed($data);

					$data = ['route' => $route];

					if ($path && $this->config->get('config_seo_url_include_path')) {
						$data['path'] = $path;
					}

					$data['product_id'] = $product_id;
					$data += $kept;
				}
				break;
			case 'cms/blog.info':
				if (isset($data['article_id'])) {
					$article_id = $data['article_id'];
					$topic_id = '';

					if (isset($data['topic_id']) || $this->config->get('config_seo_url_include_path')) {
						$topic_id = $this->getTopicByArticle((int)$article_id);
					}

					$kept = $this->keepAllowed($data);

					$data = ['route' => $route];

					if ($topic_id && $this->config->get('config_seo_url_include_path')) {
						$data['topic_id'] = $topic_id;
					}

					$data['article_id'] = $article_id;
					$data += $kept;
				}
				break;
			case 'product/category':
				if (isset($data['path'])) {
					$categories = explode('_', (string)$data['path']);

					$data['path'] = $this->getPathByCategory((int)end($categories));
				}
				break;
			default:
				break;
		}

		if (isset($data['route'])) {
			unset($data['route']);
		}

		$queries = [];

		foreach ($data as $key => $value) {
			switch ($key) {
				case 'product_id':
				case 'manufacturer_id':
				case 'information_id':
				case 'article_id':
					$queries[] = $key . '=' . (int)$value;
					$postfix = true;
					unset($data[$key]);
					break;
				case 'topic_id':
					foreach (explode('_', (string)$value) as $topic_id) {
						$queries[] = 'topic_id=' . (int)$topic_id;
					}
					unset($data[$key]);
					break;
				case 'path':
					$queries[] = 'path=' . (string)$value;
					unset($data[$key]);
					break;
				default:
					break;
			}
		}

		if (!$queries && $route) {
			if ($route == $this->config->get('action_default')) {
				$url = '';
			} else {
				$keyword = $this->getKeywordByQuery('route=' . $route);

				if ($keyword !== null && $keyword !== '') {
					$url = '/' . str_replace('%2F', '/', rawurlencode($keyword));
				} else {
					$data['route'] = $route;
				}
			}
		} else {
			$rows = [];

			foreach ($queries as $query) {
				$keyword = $this->getKeywordByQuery($query);

				if ($keyword) {
					$rows[] = $keyword;
				}
			}

			if ($rows && count($rows) == count($queries)) {
				foreach ($rows as $row) {
					$url .= '/' . str_replace('%2F', '/', rawurlencode($row));
				}
			} else {
				$data['route'] = $route;
			}
		}

		return [$url, $data, $postfix];
	}

	public function validate(): void {
		if (php_sapi_name() == 'cli') {
			return;
		}

		if (!empty($this->request->post)) {
			return;
		}

		if ($this->ajax) {
			$this->response->addHeader('X-Robots-Tag: noindex');

			return;
		}

		$route = $this->request->get['route'] ?? $this->config->get('action_default');

		$skip = [
			$this->config->get('action_error'),
			'extension/opencart/feed/google_sitemap',
			'extension/opencart/feed/google_base'
		];

		if (in_array($route, $skip)) {
			return;
		}

		$this->request->get['route'] = $route;

		if (isset($this->request->get['page']) && (float)$this->request->get['page'] < 1) {
			unset($this->request->get['page']);
		}

		$uri = (string)($this->request->server['REQUEST_URI'] ?? '');

		if ($this->isSecure()) {
			$host = rtrim((string)$this->config->get('config_url'), '/');
		} else {
			$host = rtrim((string)$this->config->get('config_url'), '/');
		}

		$current = str_replace('&amp;', '&', $host . $uri);
		$expected = str_replace('&amp;', '&', $this->url->link($route, $this->getQueryString(['_route_', 'route'])));

		if (rawurldecode($current) != rawurldecode($expected)) {
			$this->response->redirect($expected, 301);
		}
	}

	private function keepAllowed(array $data): array {
		$kept = [];

		foreach ($this->allowed_params as $param) {
			if (isset($data[$param])) {
				$kept[$param] = $data[$param];

				$this->response->addHeader('X-Robots-Tag: noindex');
			}
		}

		return $kept;
	}

	private function isSecure(): bool {
		if (!empty($this->request->server['HTTPS']) && strtolower((string)$this->request->server['HTTPS']) != 'off') {
			return true;
		}

		if (!empty($this->request->server['HTTP_X_FORWARDED_PROTO']) && strtolower((string)$this->request->server['HTTP_X_FORWARDED_PROTO']) == 'https') {
			return true;
		}

		return false;
	}

	private function detectAjax(): void {
		$request = $this->registry->get('request');

		if (isset($request->server['HTTP_X_REQUESTED_WITH']) && strtolower((string)$request->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			$this->ajax = true;
		}
	}

	private function detectPostfix(): void {
		$postfix = (string)$this->config->get('config_seopro_postfix');

		if ($postfix && isset($this->request->get['_route_'])) {
			$this->request->get['_route_'] = preg_replace('/' . preg_quote($postfix, '/') . '$/', '', (string)$this->request->get['_route_']);
		}
	}

	private function detectLanguage(): void {
		if (!$this->config->get('config_language_id')) {
			$code = (string)$this->config->get('config_language_catalog');

			if (isset($this->session->data['language'])) {
				$code = (string)$this->session->data['language'];
			}

			$query = $this->db->query("SELECT `language_id`, `code` FROM `" . DB_PREFIX . "language` WHERE `code` = '" . $this->db->escape($code) . "' AND `status` = '1' LIMIT 1");

			if ($query->num_rows) {
				$this->config->set('config_language_id', (int)$query->row['language_id']);
				$this->config->set('config_language', $query->row['code']);
			}
		}

		if ($this->ajax) {
			return;
		}

		$keyword = '';

		if (isset($this->request->get['_route_'])) {
			$parts = explode('/', (string)$this->request->get['_route_']);

			$keyword = (string)end($parts);
		}

		if (!$keyword) {
			return;
		}

		$query = $this->db->query("SELECT `language_id` FROM `" . DB_PREFIX . "seo_url` WHERE `keyword` = '" . $this->db->escape(trim($keyword)) . "' AND `store_id` = '" . (int)$this->config->get('config_store_id') . "' LIMIT 1");

		if (!$query->num_rows) {
			return;
		}

		$language_id = (int)$query->row['language_id'];

		if ($language_id == (int)$this->config->get('config_language_id')) {
			return;
		}

		$query = $this->db->query("SELECT `code` FROM `" . DB_PREFIX . "language` WHERE `language_id` = '" . $language_id . "' AND `status` = '1' LIMIT 1");

		if (!$query->num_rows) {
			return;
		}

		$this->session->data['language'] = $query->row['code'];

		$this->config->set('config_language_id', $language_id);
		$this->config->set('config_language', $query->row['code']);
	}

	private function initHelpers(): void {
		$cached = $this->config->get('config_seo_url_cache');

		if ($cached) {
			$this->keywords = (array)$this->cache->get('seopro.keywords');
			$this->queries = (array)$this->cache->get('seopro.queries');
			$this->category_tree = (array)$this->cache->get('seopro.category_tree');
			$this->topic_tree = (array)$this->cache->get('seopro.topic_tree');
			$this->product_categories = (array)$this->cache->get('seopro.product_categories');
		}

		if (!$this->keywords || !$this->queries) {
			$this->keywords = [];
			$this->queries = [];

			$query = $this->db->query("SELECT `key`, `value`, `keyword`, `store_id`, `language_id` FROM `" . DB_PREFIX . "seo_url`");

			foreach ($query->rows as $row) {
				$keyword = $this->config->get('config_seopro_lowercase') ? oc_strtolower($row['keyword']) : $row['keyword'];

				$this->keywords[$row['key'] . '=' . $row['value']][$row['store_id']][$row['language_id']] = $keyword;
				$this->queries[$keyword][$row['store_id']][$row['language_id']] = $row['key'] . '=' . $row['value'];
			}
		}

		if (!$this->category_tree) {
			$query = $this->db->query("SELECT `category_id`, GROUP_CONCAT(`path_id` ORDER BY `level` ASC SEPARATOR '_') AS `path` FROM `" . DB_PREFIX . "category_path` GROUP BY `category_id`");

			foreach ($query->rows as $row) {
				$this->category_tree[$row['category_id']] = $row['path'];
			}
		}

		if (!$this->topic_tree) {
			$query = $this->db->query("SELECT `topic_id`, GROUP_CONCAT(`path_id` ORDER BY `level` ASC SEPARATOR '_') AS `path` FROM `" . DB_PREFIX . "topic_path` GROUP BY `topic_id`");

			foreach ($query->rows as $row) {
				$this->topic_tree[$row['topic_id']] = $row['path'];
			}
		}
	}

	private function getQueryByKeyword(string $keyword): string {
		$store_id = (int)$this->config->get('config_store_id');
		$language_id = (int)$this->config->get('config_language_id');

		if (isset($this->queries[$keyword][$store_id][$language_id])) {
			return $this->queries[$keyword][$store_id][$language_id];
		}

		return '';
	}

	private function getKeywordByQuery(string $query): ?string {
		$store_id = (int)$this->config->get('config_store_id');
		$language_id = (int)$this->config->get('config_language_id');

		if (isset($this->keywords[$query][$store_id][$language_id])) {
			return $this->keywords[$query][$store_id][$language_id];
		}

		return null;
	}

	private function getCategoryByProduct(int $product_id): string {
		if ($product_id < 1) {
			return '';
		}

		if (isset($this->product_categories[$product_id])) {
			return $this->product_categories[$product_id];
		}

		$query = $this->db->query("SELECT `category_id` FROM `" . DB_PREFIX . "product_to_category` WHERE `product_id` = '" . $product_id . "' ORDER BY `main_category` DESC LIMIT 1");

		$path = $query->num_rows ? $this->getPathByCategory((int)$query->row['category_id']) : '';

		$this->product_categories[$product_id] = $path;

		return $path;
	}

	private function getPathByCategory(int $category_id): string {
		return $this->category_tree[$category_id] ?? '';
	}

	private function getTopicByArticle(int $article_id): string {
		if ($article_id < 1) {
			return '';
		}

		$query = $this->db->query("SELECT `topic_id` FROM `" . DB_PREFIX . "article_to_topic` WHERE `article_id` = '" . $article_id . "' ORDER BY `main_topic` DESC LIMIT 1");

		if (!$query->num_rows) {
			return '';
		}

		return $this->topic_tree[$query->row['topic_id']] ?? (string)$query->row['topic_id'];
	}

	private function getQueryString(array $exclude = []): string {
		return urldecode(http_build_query(array_diff_key($this->request->get, array_flip($exclude))));
	}

	public function __destruct() {
		if (!$this->config->get('config_seo_pro') || !$this->config->get('config_seo_url_cache')) {
			return;
		}

		$this->cache->set('seopro.keywords', $this->keywords);
		$this->cache->set('seopro.queries', $this->queries);
		$this->cache->set('seopro.category_tree', $this->category_tree);
		$this->cache->set('seopro.topic_tree', $this->topic_tree);
		$this->cache->set('seopro.product_categories', $this->product_categories);
	}
}
