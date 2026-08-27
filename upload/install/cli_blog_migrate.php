<?php
namespace Opencart\Install;
if (PHP_SAPI != 'cli') {
	exit('Command line only');
}

ini_set('display_errors', '1');
error_reporting(E_ALL);

define('DIR_OPENCART', str_replace('\\', '/', realpath(__DIR__ . '/../')) . '/');
define('DIR_APPLICATION', DIR_OPENCART . 'install/');
define('DIR_SYSTEM', DIR_OPENCART . 'system/');
define('DIR_STORAGE', DIR_SYSTEM . 'storage/');

require_once(DIR_SYSTEM . 'library/db.php');
require_once(DIR_SYSTEM . 'library/db/mysqli.php');

class CliBlogMigrate {
	private object $db;
	private string $source;
	private string $prefix;
	private string $source_prefix;
	/**
	 * @var array<int, string>
	 */
	private array $report = [];

	/**
	 * @param array<string, string> $option
	 */
	public function __construct(array $option) {
		$this->db = new \Opencart\System\Library\DB('mysqli', $option['db_hostname'], $option['db_username'], $option['db_password'], $option['db_database'], (string)$option['db_port']);

		$this->source = $option['source_database'];
		$this->prefix = $option['db_prefix'];
		$this->source_prefix = $option['source_prefix'];
	}

	private function target(string $table): string {
		return '`' . $this->prefix . $table . '`';
	}

	private function origin(string $table): string {
		return '`' . $this->source . '`.`' . $this->source_prefix . $table . '`';
	}

	private function exists(string $table): bool {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `information_schema`.`tables` WHERE `table_schema` = '" . $this->db->escape($this->source) . "' AND `table_name` = '" . $this->db->escape($this->source_prefix . $table) . "'");

		return (bool)$query->row['total'];
	}

	private function count(string $table): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM " . $this->target($table));

		return (int)$query->row['total'];
	}

	public function run(): void {
		foreach (['article', 'article_description', 'blog_category', 'blog_category_description', 'article_to_blog_category'] as $table) {
			if (!$this->exists($table)) {
				exit('ERROR: у базі ' . $this->source . ' немає таблиці ' . $this->source_prefix . $table . "\n");
			}
		}

		$this->migrateTopics();
		$this->migrateTopicPaths();
		$this->migrateArticles();
		$this->migrateArticleTopics();
		$this->migrateComments();
		$this->migrateRelated();
		$this->migrateProducts();
		$this->migrateDownloads();
		$this->migrateSeoUrls();

		foreach ($this->report as $line) {
			echo $line . "\n";
		}
	}

	private function migrateTopics(): void {
		$this->db->query("DELETE FROM " . $this->target('topic'));
		$this->db->query("DELETE FROM " . $this->target('topic_description'));
		$this->db->query("DELETE FROM " . $this->target('topic_to_store'));

		$this->db->query("INSERT INTO " . $this->target('topic') . " (`topic_id`, `parent_id`, `sort_order`, `noindex`, `status`) SELECT `blog_category_id`, `parent_id`, `sort_order`, `noindex`, `status` FROM " . $this->origin('blog_category'));

		$this->db->query("INSERT INTO " . $this->target('topic_description') . " (`topic_id`, `language_id`, `name`, `description`, `image`, `meta_title`, `meta_description`, `meta_keyword`, `meta_h1`) SELECT `cd`.`blog_category_id`, `cd`.`language_id`, `cd`.`name`, `cd`.`description`, IFNULL(`c`.`image`, ''), `cd`.`meta_title`, `cd`.`meta_description`, `cd`.`meta_keyword`, `cd`.`meta_h1` FROM " . $this->origin('blog_category_description') . " `cd` LEFT JOIN " . $this->origin('blog_category') . " `c` ON (`cd`.`blog_category_id` = `c`.`blog_category_id`)");

		if ($this->exists('blog_category_to_store')) {
			$this->db->query("INSERT INTO " . $this->target('topic_to_store') . " (`topic_id`, `store_id`) SELECT `blog_category_id`, `store_id` FROM " . $this->origin('blog_category_to_store'));
		} else {
			$this->db->query("INSERT INTO " . $this->target('topic_to_store') . " (`topic_id`, `store_id`) SELECT `topic_id`, 0 FROM " . $this->target('topic'));
		}

		$this->report[] = 'теми: ' . $this->count('topic');
	}

	private function migrateTopicPaths(): void {
		$this->db->query("DELETE FROM " . $this->target('topic_path'));

		if ($this->exists('blog_category_path')) {
			$this->db->query("INSERT INTO " . $this->target('topic_path') . " (`topic_id`, `path_id`, `level`) SELECT `blog_category_id`, `path_id`, `level` FROM " . $this->origin('blog_category_path'));
		} else {
			$this->rebuildPaths(0, []);
		}

		$this->report[] = 'шляхи тем: ' . $this->count('topic_path');
	}

	/**
	 * @param int             $parent_id
	 * @param array<int, int> $path
	 */
	private function rebuildPaths(int $parent_id, array $path): void {
		$query = $this->db->query("SELECT `topic_id` FROM " . $this->target('topic') . " WHERE `parent_id` = '" . (int)$parent_id . "'");

		foreach ($query->rows as $row) {
			$current = array_merge($path, [(int)$row['topic_id']]);

			foreach ($current as $level => $path_id) {
				$this->db->query("INSERT INTO " . $this->target('topic_path') . " SET `topic_id` = '" . (int)$row['topic_id'] . "', `path_id` = '" . (int)$path_id . "', `level` = '" . (int)$level . "'");
			}

			$this->rebuildPaths((int)$row['topic_id'], $current);
		}
	}

	private function migrateArticles(): void {
		$this->db->query("DELETE FROM " . $this->target('article'));
		$this->db->query("DELETE FROM " . $this->target('article_description'));
		$this->db->query("DELETE FROM " . $this->target('article_to_store'));

		$this->db->query("INSERT INTO " . $this->target('article') . " (`article_id`, `topic_id`, `author`, `rating`, `sort_order`, `date_available`, `viewed`, `noindex`, `status`, `date_added`, `date_modified`) SELECT `a`.`article_id`, IFNULL((SELECT `a2c`.`blog_category_id` FROM " . $this->origin('article_to_blog_category') . " `a2c` WHERE `a2c`.`article_id` = `a`.`article_id` ORDER BY `a2c`.`main_blog_category` DESC LIMIT 1), 0), '', 0, `a`.`sort_order`, IF(`a`.`date_available` = '0000-00-00', DATE(`a`.`date_added`), `a`.`date_available`), `a`.`viewed`, `a`.`noindex`, `a`.`status`, `a`.`date_added`, `a`.`date_modified` FROM " . $this->origin('article') . " `a`");

		$this->db->query("INSERT INTO " . $this->target('article_description') . " (`article_id`, `language_id`, `name`, `description`, `image`, `tag`, `meta_title`, `meta_description`, `meta_keyword`, `meta_h1`) SELECT `ad`.`article_id`, `ad`.`language_id`, `ad`.`name`, `ad`.`description`, IFNULL(`a`.`image`, ''), `ad`.`tag`, `ad`.`meta_title`, `ad`.`meta_description`, `ad`.`meta_keyword`, `ad`.`meta_h1` FROM " . $this->origin('article_description') . " `ad` LEFT JOIN " . $this->origin('article') . " `a` ON (`ad`.`article_id` = `a`.`article_id`)");

		if ($this->exists('article_to_store')) {
			$this->db->query("INSERT INTO " . $this->target('article_to_store') . " (`article_id`, `store_id`) SELECT `article_id`, `store_id` FROM " . $this->origin('article_to_store'));
		} else {
			$this->db->query("INSERT INTO " . $this->target('article_to_store') . " (`article_id`, `store_id`) SELECT `article_id`, 0 FROM " . $this->target('article'));
		}

		$this->report[] = 'статті: ' . $this->count('article');
	}

	private function migrateArticleTopics(): void {
		$this->db->query("DELETE FROM " . $this->target('article_to_topic'));

		$this->db->query("INSERT INTO " . $this->target('article_to_topic') . " (`article_id`, `topic_id`, `main_topic`) SELECT `article_id`, `blog_category_id`, `main_blog_category` FROM " . $this->origin('article_to_blog_category') . " WHERE `blog_category_id` > 0");

		$this->report[] = 'зв’язки стаття-тема: ' . $this->count('article_to_topic');
	}

	private function migrateComments(): void {
		if (!$this->exists('review_article')) {
			$this->report[] = 'відгуки: таблиці немає, пропущено';

			return;
		}

		$this->db->query("DELETE FROM " . $this->target('article_comment'));

		$this->db->query("INSERT INTO " . $this->target('article_comment') . " (`article_id`, `parent_id`, `customer_id`, `author`, `comment`, `rating`, `ip`, `status`, `date_added`) SELECT `article_id`, 0, `customer_id`, `author`, `text`, `rating`, '', `status`, `date_added` FROM " . $this->origin('review_article'));

		$this->report[] = 'коментарі з відгуків: ' . $this->count('article_comment');
	}

	private function migrateRelated(): void {
		$this->db->query("DELETE FROM " . $this->target('article_related'));

		if ($this->exists('article_related')) {
			$this->db->query("INSERT IGNORE INTO " . $this->target('article_related') . " (`article_id`, `related_id`) SELECT `article_id`, `related_id` FROM " . $this->origin('article_related') . " WHERE `article_id` <> `related_id`");
		}

		$this->report[] = 'пов’язані статті: ' . $this->count('article_related');

		$this->db->query("DELETE FROM " . $this->target('article_to_manufacturer'));

		if ($this->exists('article_related_mn')) {
			$this->db->query("INSERT IGNORE INTO " . $this->target('article_to_manufacturer') . " (`article_id`, `manufacturer_id`) SELECT `article_id`, `manufacturer_id` FROM " . $this->origin('article_related_mn'));
		}

		$this->report[] = 'зв’язки стаття-виробник: ' . $this->count('article_to_manufacturer');

		$this->db->query("DELETE FROM " . $this->target('article_to_category'));

		if ($this->exists('article_related_wb')) {
			$this->db->query("INSERT IGNORE INTO " . $this->target('article_to_category') . " (`article_id`, `category_id`) SELECT `article_id`, `category_id` FROM " . $this->origin('article_related_wb'));
		}

		$this->report[] = 'зв’язки стаття-категорія: ' . $this->count('article_to_category');
	}

	private function migrateProducts(): void {
		$this->db->query("DELETE FROM " . $this->target('article_to_product'));

		foreach (['article_related_product', 'product_related_article'] as $table) {
			if (!$this->exists($table)) {
				continue;
			}

			$this->db->query("INSERT IGNORE INTO " . $this->target('article_to_product') . " (`article_id`, `product_id`) SELECT `article_id`, `product_id` FROM " . $this->origin($table));
		}

		$this->report[] = 'зв’язки стаття-товар: ' . $this->count('article_to_product');
	}

	private function migrateDownloads(): void {
		$this->db->query("DELETE FROM " . $this->target('article_to_download'));

		if ($this->exists('article_to_download')) {
			$this->db->query("INSERT IGNORE INTO " . $this->target('article_to_download') . " (`article_id`, `download_id`) SELECT `article_id`, `download_id` FROM " . $this->origin('article_to_download'));
		}

		$this->report[] = 'файли статей: ' . $this->count('article_to_download');
	}

	private function migrateSeoUrls(): void {
		$this->db->query("DELETE FROM " . $this->target('seo_url') . " WHERE `key` IN ('article_id', 'topic_id')");

		if (!$this->exists('seo_url')) {
			$this->report[] = 'SEO URL: таблиці немає, пропущено';

			return;
		}

		$this->db->query("INSERT INTO " . $this->target('seo_url') . " (`store_id`, `language_id`, `key`, `value`, `keyword`, `sort_order`) SELECT `store_id`, `language_id`, 'article_id', SUBSTRING(`query`, 12), `keyword`, 0 FROM " . $this->origin('seo_url') . " WHERE `query` LIKE 'article_id=%'");

		$this->db->query("INSERT INTO " . $this->target('seo_url') . " (`store_id`, `language_id`, `key`, `value`, `keyword`, `sort_order`) SELECT `store_id`, `language_id`, 'topic_id', SUBSTRING(`query`, 18), `keyword`, 0 FROM " . $this->origin('seo_url') . " WHERE `query` LIKE 'blog_category_id=%'");

		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM " . $this->target('seo_url') . " WHERE `key` IN ('article_id', 'topic_id')");

		$this->report[] = 'SEO URL статей і тем: ' . (int)$query->row['total'];
	}
}

$argv = $_SERVER['argv'];

array_shift($argv);

$option = [
	'db_hostname'     => 'localhost',
	'db_username'     => 'root',
	'db_password'     => '',
	'db_database'     => '',
	'db_port'         => '3306',
	'db_prefix'       => 'oc_',
	'source_database' => '',
	'source_prefix'   => 'oc_'
];

$total = count($argv);

for ($i = 0; $i < $total; $i += 2) {
	if (substr($argv[$i], 0, 2) == '--') {
		$option[substr($argv[$i], 2)] = $argv[$i + 1] ?? '';
	}
}

if (!$option['db_database'] || !$option['source_database']) {
	echo "Використання:\n";
	echo "  php cli_blog_migrate.php --db_hostname mysql --db_username root --db_password pass --db_database new4x --source_database old3x [--db_port 3306] [--db_prefix oc_] [--source_prefix oc_]\n";

	exit(1);
}

$migrate = new CliBlogMigrate($option);

$migrate->run();
