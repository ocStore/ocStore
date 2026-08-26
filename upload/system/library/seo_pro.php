<?php
/**
 * @package   SeoPro
 * @author    Oclabs
 * @copyright Copyright (c) 2017, Oclabs (https://www.oclabs.pro/)
 * @copyright Copyright (c) 2026, ocStore (https://ocstore.com/)
 * @license   https://opensource.org/licenses/GPL-3.0
 */
namespace Opencart\System\Library;

class SeoPro {
	private object $registry;
	private object $config;
	private ?object $request = null;
	private ?object $response = null;
	private ?object $session = null;
	private ?object $db = null;
	private ?object $cache = null;
	private ?object $url = null;
	private bool $ajax = false;
	/**
	 * @var array<int, string>
	 */
	private array $topic_tree = [];
	/**
	 * @var array<int, string>
	 */
	private array $category_tree = [];
	/**
	 * @var array<string, array<int, array<int, string>>>
	 */
	private array $keywords = [];
	/**
	 * @var array<string, int>
	 */
	private array $language_ids = [];
	/**
	 * @var array<string, array<string, mixed>>
	 */
	private array $language_prefixes = [];
	private int $link_language_id = 0;
	private bool $link_language_distinct = true;
	/**
	 * @var array<string, array<int, array<int, string>>>
	 */
	private array $queries = [];
	/**
	 * @var array<int, string>
	 */
	private array $product_categories = [];
	/**
	 * @var array<int, string>
	 */
	private array $allowed_params = [];
	/**
	 * @var array<int, array<string, mixed>>
	 */
	private array $handlers = [];
	/**
	 * @var array<int, string>
	 */
	private array $handler_keys = [];

	/**
	 * Єдиний екземпляр на запит. Зберігається в реєстрі, тож доступний
	 * як $this->seo_pro з будь-якого контролера, моделі чи обробника.
	 */
	public static function getInstance(\Opencart\System\Engine\Registry $registry): self {
		if (!$registry->has('seo_pro')) {
			$registry->set('seo_pro', new self($registry));
		}

		return $registry->get('seo_pro');
	}

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

		$this->registerHandlers();

		$this->detectPostfix();
		$this->detectLanguage();
		$this->initHelpers();

		if ($this->config->get('config_seopro_param_status')) {
			$params = preg_split('/\R/', (string)$this->config->get('config_seopro_params'), -1, PREG_SPLIT_NO_EMPTY);

			$this->allowed_params = array_map('trim', $params);
		}
	}

	/**
	 * Додає обробник SEO URL. Менший sort_order перевіряється раніше.
	 */
	public function addHandler(\Opencart\System\Library\Seo\HandlerInterface $handler, int $sort_order = 100): void {
		$this->handlers[] = ['handler' => $handler, 'sort_order' => $sort_order];
		$this->handler_keys = [];

		usort($this->handlers, function (array $a, array $b): int {
			return $a['sort_order'] <=> $b['sort_order'];
		});
	}

	/**
	 * @return array<int, \Opencart\System\Library\Seo\HandlerInterface>
	 */
	public function getHandlers(): array {
		return array_column($this->handlers, 'handler');
	}

	/**
	 * Усі ключі запиту, за які відповідають обробники.
	 *
	 * @return array<int, string>
	 */
	public function getHandlerKeys(): array {
		if (!$this->handler_keys) {
			$keys = ['route'];

			foreach ($this->getHandlers() as $handler) {
				$keys = array_merge($keys, $handler->getKeys());
			}

			$this->handler_keys = array_values(array_unique($keys));
		}

		return $this->handler_keys;
	}

	private function registerHandlers(): void {
		$this->addHandler(new \Opencart\System\Library\Seo\Product($this->registry, $this), 10);
		$this->addHandler(new \Opencart\System\Library\Seo\Article($this->registry, $this), 20);
		$this->addHandler(new \Opencart\System\Library\Seo\Category($this->registry, $this), 30);
		$this->addHandler(new \Opencart\System\Library\Seo\Manufacturer($this->registry, $this), 40);
		$this->addHandler(new \Opencart\System\Library\Seo\Information($this->registry, $this), 50);
		$this->addHandler(new \Opencart\System\Library\Seo\Topic($this->registry, $this), 60);

		// Сторонні обробники. Подієва шина на цьому кроці ще порожня —
		// startup/event виконується пізніше, тому читаємо реєстрації напряму.
		$query = $this->db->query("SELECT `action`, `sort_order` FROM `" . DB_PREFIX . "event` WHERE `trigger` = 'seo/handler/register' AND `status` = '1' ORDER BY `sort_order` ASC");

		foreach ($query->rows as $row) {
			$args = [$this];

			$action = new \Opencart\System\Engine\Action($row['action']);

			$action->execute($this->registry, $args);
		}
	}

	/**
	 * @param array<int, string> $parts
	 *
	 * @return array<int, string>
	 */
	public function prepareRoute(array $parts): array {
		$parts = $this->applyLanguagePrefix($parts);

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

			if (!in_array($key, $this->getHandlerKeys())) {
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

		foreach ($this->getHandlers() as $handler) {
			$decoded = $handler->decode($this->request->get);

			if ($decoded !== null) {
				$this->request->get = $decoded;

				return $leftover;
			}
		}

		return $leftover;
	}

	/**
	 * @param array<int, string> $parts
	 *
	 * @return array<int, string>
	 */
	private function applyLanguagePrefix(array $parts): array {
		if (!$this->config->get('config_seopro_language') || !$parts) {
			return $parts;
		}

		$prefix = (string)reset($parts);

		$languages = $this->getLanguagePrefixes();

		if (!isset($languages[$prefix])) {
			return $parts;
		}

		$this->request->get['language'] = $languages[$prefix]['code'];

		$this->config->set('config_language_id', $languages[$prefix]['language_id']);
		$this->config->set('config_language', $languages[$prefix]['code']);

		array_shift($parts);

		return array_values($parts);
	}

	/**
	 * @param array<string, mixed> $data
	 *
	 * @return array<int, mixed>
	 */
	public function baseRewrite(array $data): array {
		$url = null;
		$postfix = null;
		$encoded = false;

		$language_code = isset($data['language']) ? (string)$data['language'] : '';

		$this->link_language_id = $language_code ? $this->getLanguageIdByCode($language_code) : 0;
		$this->link_language_distinct = true;

		unset($data['language']);

		$route = $data['route'] ?? '';

		$queries = [];

		foreach ($this->getHandlers() as $handler) {
			if ($route && $handler->getRoute() && $handler->getRoute() != $route) {
				continue;
			}

			$encode = $handler->encode($data);

			if ($encode !== null) {
				$queries = $encode['queries'];
				$data = $encode['data'];

				if (!empty($encode['postfix'])) {
					$postfix = true;
				}

				break;
			}
		}

		unset($data['route']);

		if (!$queries && $route) {
			if ($route == $this->config->get('action_default')) {
				$url = '';
			} else {
				$keyword = $this->getKeywordByQuery('route=' . $route);

				if ($keyword !== null && $keyword !== '') {
					$url = '/' . str_replace('%2F', '/', rawurlencode($keyword));

					$encoded = true;
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

				$encoded = true;
			} else {
				$data['route'] = $route;
			}
		}

		if ($language_code && $language_code != $this->config->get('config_language_catalog')) {
			$mode = (string)$this->config->get('config_seopro_language');

			$unique = $encoded && $this->link_language_distinct;

			if ($mode == 'keyword' && $unique) {
				$prefix = '';
			} elseif ($mode == 'keyword' || $mode == 'prefix') {
				$prefix = $this->getPrefixByCode($language_code);
			} else {
				$prefix = '';

				if (!$unique) {
					$data['language'] = $language_code;
				}
			}

			if ($prefix) {
				$url = '/' . $prefix . (string)$url;

				if ($url == '/' . $prefix) {
					$url .= '/';
				}
			}
		}

		$this->link_language_id = 0;

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

		if (isset($this->request->get['page']) && (float)$this->request->get['page'] < 1) { // @phpstan-ignore isset.offset
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

	/**
	 * @param array<string, mixed> $data
	 *
	 * @return array<string, mixed>
	 */
	public function keepAllowed(array $data): array {
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

			if ($this->session && isset($this->session->data['language'])) {
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

		if ($this->session) {
			$this->session->data['language'] = $query->row['code'];
		}

		$this->request->get['language'] = $query->row['code'];

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

	/**
	 * @return array<string, array<string, mixed>>
	 */
	private function getLanguagePrefixes(): array {
		if ($this->language_prefixes) {
			return $this->language_prefixes;
		}

		$query = $this->db->query("SELECT `language_id`, `code` FROM `" . DB_PREFIX . "language` WHERE `status` = '1'");

		$short = [];

		foreach ($query->rows as $row) {
			$prefix = (string)strtok($row['code'], '-');

			$short[$prefix][] = $row;
		}

		foreach ($short as $prefix => $rows) {
			foreach ($rows as $row) {
				$key = count($rows) > 1 ? $row['code'] : $prefix;

				$this->language_prefixes[$key] = ['language_id' => (int)$row['language_id'], 'code' => $row['code']];
			}
		}

		return $this->language_prefixes;
	}

	private function getPrefixByCode(string $code): string {
		foreach ($this->getLanguagePrefixes() as $prefix => $language) {
			if ($language['code'] == $code) {
				return $prefix;
			}
		}

		return '';
	}

	private function getLanguageIdByCode(string $code): int {
		if (!isset($this->language_ids[$code])) {
			$query = $this->db->query("SELECT `language_id` FROM `" . DB_PREFIX . "language` WHERE `code` = '" . $this->db->escape($code) . "' AND `status` = '1' LIMIT 1");

			$this->language_ids[$code] = $query->num_rows ? (int)$query->row['language_id'] : 0;
		}

		return $this->language_ids[$code];
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
		$language_id = $this->link_language_id ?: (int)$this->config->get('config_language_id');

		if (!isset($this->keywords[$query][$store_id][$language_id])) {
			return null;
		}

		$keyword = $this->keywords[$query][$store_id][$language_id];

		if ($this->link_language_id) {
			$default_id = $this->getLanguageIdByCode((string)$this->config->get('config_language_catalog'));

			if (($this->keywords[$query][$store_id][$default_id] ?? $keyword) === $keyword) {
				$this->link_language_distinct = false;
			}
		}

		return $keyword;
	}

	public function getCategoryByProduct(int $product_id): string {
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

	public function getPathByCategory(int $category_id): string {
		return $this->category_tree[$category_id] ?? '';
	}

	public function getTopicByArticle(int $article_id): string {
		if ($article_id < 1) {
			return '';
		}

		$query = $this->db->query("SELECT `topic_id` FROM `" . DB_PREFIX . "article_to_topic` WHERE `article_id` = '" . $article_id . "' ORDER BY `main_topic` DESC LIMIT 1");

		if (!$query->num_rows) {
			return '';
		}

		return $this->topic_tree[$query->row['topic_id']] ?? (string)$query->row['topic_id'];
	}

	/**
	 * @param array<int, string> $exclude
	 */
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
