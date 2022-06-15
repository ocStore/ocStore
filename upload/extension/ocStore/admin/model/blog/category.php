<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

namespace Opencart\Admin\Model\Extension\ocStore\Blog;

if (!defined('VERSION')) {
	header('Refresh: 1; URL=/');
	exit('ЗАПРЫШЧАЮ!');
}

class Category extends \Opencart\System\Engine\Model {
	public function addCategory($data): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_category` SET `parent_id` = '" . (int)$data['parent_id'] . "', `top` = '" . (isset($data['top']) ? (int)$data['top'] : 0) . "', `column` = '" . (int)$data['column'] . "', `sort_order` = '" . (int)$data['sort_order'] . "', `status` = '" . (int)$data['status'] . "', `noindex` = '" . (int)$data['noindex'] . "', `date_modified` = NOW(), `date_added` = NOW()");

		$blog_category_id = $this->db->getLastId();

		if (isset($data['image'])) {
			$this->db->query("UPDATE `" . DB_PREFIX . "blog_category` SET `image` = '" . $this->db->escape($data['image']) . "' WHERE `blog_category_id` = '" . (int)$blog_category_id . "'");
		}

		foreach ($data['category_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_category_description` SET `blog_category_id` = '" . (int)$blog_category_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "', `description` = '" . $this->db->escape($value['description']) . "', `meta_title` = '" . $this->db->escape($value['meta_title']) . "', `meta_h1` = '" . $this->db->escape($value['meta_h1']) . "', `meta_description` = '" . $this->db->escape($value['meta_description']) . "', `meta_keyword` = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		// MySQL Hierarchical Data Closure Table Pattern
		$level = 0;

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_category_path` WHERE `blog_category_id` = '" . (int)$data['parent_id'] . "' ORDER BY `level` ASC");

		foreach ($query->rows as $result) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_category_path` SET `blog_category_id` = '" . (int)$blog_category_id . "', `path_id` = '" . (int)$result['path_id'] . "', `level` = '" . (int)$level . "'");

			$level++;
		}

		$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_category_path` SET `blog_category_id` = '" . (int)$blog_category_id . "', `path_id` = '" . (int)$blog_category_id . "', `level` = '" . (int)$level . "'");

		if (isset($data['category_store'])) {
			foreach ($data['category_store'] as $store_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_category_to_store` SET `blog_category_id` = '" . (int)$blog_category_id . "', `store_id` = '" . (int)$store_id . "'");
			}
		}

		// Seo urls on categories need to be done differently to they include the full keyword path
		$parent_path = $this->getPath($data['parent_id']);

		if (!$parent_path) {
			$path = $blog_category_id;
		} else {
			$path = $parent_path . '_' . $blog_category_id;
		}

		$this->load->model('design/seo_url');

		foreach ($data['category_seo_url'] as $store_id => $language) {
			foreach ($language as $language_id => $keyword) {
				$seo_url_info = $this->model_design_seo_url->getSeoUrlByKeyValue('blog_category_id', $parent_path, $store_id, $language_id);

				if ($seo_url_info) {
					$keyword = $seo_url_info['keyword'] . '/' . $keyword;
				}

				$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET `store_id` = '" . (int)$store_id . "', `language_id` = '" . (int)$language_id . "', `key` = 'blog_category_id', `value`= '" . $this->db->escape($path) . "', `keyword` = '" . $this->db->escape($keyword) . "'");
			}
		}

		// Set which layout to use with this category
		if (isset($data['category_layout'])) {
			foreach ($data['category_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_category_to_layout` SET `blog_category_id` = '" . (int)$blog_category_id . "', `store_id` = '" . (int)$store_id . "', `layout_id` = '" . (int)$layout_id . "'");
			}
		}

		$this->cache->delete('blog_category');
		$this->cache->delete('seo_pro');
		$this->cache->delete('seo_url');

		return $blog_category_id;
	}

	public function editCategory(int $blog_category_id, array $data): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "blog_category` SET `parent_id` = '" . (int)$data['parent_id'] . "', `top` = '" . (isset($data['top']) ? (int)$data['top'] : 0) . "', `column` = '" . (int)$data['column'] . "', `sort_order` = '" . (int)$data['sort_order'] . "', `status` = '" . (int)$data['status'] . "', `noindex` = '" . (int)$data['noindex'] . "', `date_modified` = NOW() WHERE `blog_category_id` = '" . (int)$blog_category_id . "'");

		if (isset($data['image'])) {
			$this->db->query("UPDATE `" . DB_PREFIX . "blog_category` SET `image` = '" . $this->db->escape($data['image']) . "' WHERE `blog_category_id` = '" . (int)$blog_category_id . "'");
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_category_description` WHERE `blog_category_id` = '" . (int)$blog_category_id . "'");

		foreach ($data['category_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_category_description` SET `blog_category_id` = '" . (int)$blog_category_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "', `description` = '" . $this->db->escape($value['description']) . "', `meta_title` = '" . $this->db->escape($value['meta_title']) . "', `meta_h1` = '" . $this->db->escape($value['meta_h1']) . "', `meta_description` = '" . $this->db->escape($value['meta_description']) . "', `meta_keyword` = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		// MySQL Hierarchical Data Closure Table Pattern
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_category_path` WHERE `path_id` = '" . (int)$blog_category_id . "' ORDER BY `level` ASC");

		if ($query->rows) {
			foreach ($query->rows as $category_path) {
				// Delete the path below the current one
				$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_category_path` WHERE `blog_category_id` = '" . (int)$category_path['blog_category_id'] . "' AND `level` < '" . (int)$category_path['level'] . "'");

				$path = [];

				// Get the nodes new parents
				$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_category_path` WHERE `blog_category_id` = '" . (int)$data['parent_id'] . "' ORDER BY `level` ASC");

				foreach ($query->rows as $result) {
					$path[] = $result['path_id'];
				}

				// Get whats left of the nodes current path
				$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_category_path` WHERE `blog_category_id` = '" . (int)$category_path['blog_category_id'] . "' ORDER BY `level` ASC");

				foreach ($query->rows as $result) {
					$path[] = $result['path_id'];
				}

				// Combine the paths with a new level
				$level = 0;

				foreach ($path as $path_id) {
					$this->db->query("REPLACE INTO `" . DB_PREFIX . "blog_category_path` SET `blog_category_id` = '" . (int)$category_path['blog_category_id'] . "', `path_id` = '" . (int)$path_id . "', `level` = '" . (int)$level . "'");

					$level++;
				}
			}
		} else {
			// Delete the path below the current one
			$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_category_path` WHERE `blog_category_id` = '" . (int)$blog_category_id . "'");

			// Fix for records with no paths
			$level = 0;

			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_category_path` WHERE `blog_category_id` = '" . (int)$data['parent_id'] . "' ORDER BY `level` ASC");

			foreach ($query->rows as $result) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_category_path` SET `blog_category_id` = '" . (int)$blog_category_id . "', `path_id` = '" . (int)$result['path_id'] . "', `level` = '" . (int)$level . "'");

				$level++;
			}

			$this->db->query("REPLACE INTO `" . DB_PREFIX . "blog_category_path` SET `blog_category_id` = '" . (int)$blog_category_id . "', `path_id` = '" . (int)$blog_category_id . "', `level` = '" . (int)$level . "'");
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_category_to_store` WHERE `blog_category_id` = '" . (int)$blog_category_id . "'");

		if (isset($data['category_store'])) {
			foreach ($data['category_store'] as $store_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_category_to_store` SET `blog_category_id` = '" . (int)$blog_category_id . "', `store_id` = '" . (int)$store_id . "'");
			}
		}

		// Seo urls on categories need to be done differently to they include the full keyword path
		$path_parent = $this->getPath($data['parent_id']);

		if (!$path_parent) {
			$path_new = $blog_category_id;
		} else {
			$path_new = $path_parent . '_' . $blog_category_id;
		}

		// Get old data to so we know what to replace
		$seo_url_data = $this->getSeoUrls($blog_category_id);

		// Old path
		$path_old = $this->getPath($blog_category_id);

		// Delete the old path
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'blog_category_id' AND `value` = '" . $this->db->escape($path_old) . "'");

		$this->load->model('design/seo_url');

		foreach ($data['category_seo_url'] as $store_id => $language) {
			foreach ($language as $language_id => $keyword) {
				$parent_info = $this->model_design_seo_url->getSeoUrlByKeyValue('blog_category_id', $path_parent, $store_id, $language_id);

				if ($parent_info) {
					$keyword = $parent_info['keyword'] . '/' . $keyword;
				}

				$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET `store_id` = '" . (int)$store_id . "', `language_id` = '" . (int)$language_id . "', `key` = 'blog_category_id', `value` = '" . $this->db->escape($path_new) . "', `keyword` = '" . $this->db->escape($keyword) . "'");

				// Update sub category seo urls
				if (isset($seo_url_data[$store_id][$language_id])) {
					$this->db->query("UPDATE `" . DB_PREFIX . "seo_url` SET `value` = CONCAT('" . $this->db->escape($path_new . '_') . "', SUBSTRING(`value`, " . (strlen($path_old . '_') + 1) . ")), `keyword` = CONCAT('" . $this->db->escape($keyword) . "', SUBSTRING(`keyword`, " . (utf8_strlen($seo_url_data[$store_id][$language_id]) + 1) . ")) WHERE `store_id` = '" . (int)$store_id . "' AND `language_id` = '" . (int)$language_id . "' AND `key` = 'blog_category_id' AND `value` LIKE '" . $this->db->escape($path_old . '_%') . "'");
				}
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_category_to_layout` WHERE `blog_category_id` = '" . (int)$blog_category_id . "'");

		if (isset($data['category_layout'])) {
			foreach ($data['category_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_category_to_layout` SET `blog_category_id` = '" . (int)$blog_category_id . "', `store_id` = '" . (int)$store_id . "', `layout_id` = '" . (int)$layout_id . "'");
			}
		}

		$this->cache->delete('blog_category');
		$this->cache->delete('seo_pro');
		$this->cache->delete('seo_url');
	}

	public function editCategoryStatus(int $blog_category_id, int|bool $status): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "blog_category` SET `status` = '" . (int)$status . "', `date_modified` = NOW() WHERE `blog_category_id` = '" . (int)$blog_category_id . "'");

		$this->cache->delete('category');
	}

	public function deleteCategory(int $blog_category_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "article_to_blog_category` WHERE `blog_category_id` = '" . (int)$blog_category_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_category` WHERE `blog_category_id` = '" . (int)$blog_category_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_category_description` WHERE `blog_category_id` = '" . (int)$blog_category_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_category_to_store` WHERE `blog_category_id` = '" . (int)$blog_category_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_category_to_layout` WHERE `blog_category_id` = '" . (int)$blog_category_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'blog_category_id' AND `value` = '" . $this->db->escape($this->getPath($blog_category_id)) . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_category_path` WHERE `blog_category_id` = '" . (int)$blog_category_id . "'");

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_category_path` WHERE `path_id` = '" . (int)$blog_category_id . "'");

		foreach ($query->rows as $result) {
			$this->deleteCategory($result['blog_category_id']);
		}

		$this->cache->delete('blog_category');
	}

	public function repairCategories(int $parent_id = 0): void {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_category` WHERE `parent_id` = '" . (int)$parent_id . "'");

		foreach ($query->rows as $category) {
			// Delete the path below the current one
			$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_category_path` WHERE `blog_category_id` = '" . (int)$category['blog_category_id'] . "'");

			// Fix for records with no paths
			$level = 0;

			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_category_path` WHERE `blog_category_id` = '" . (int)$parent_id . "' ORDER BY `level` ASC");

			foreach ($query->rows as $result) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_category_path` SET `blog_category_id` = '" . (int)$category['blog_category_id'] . "', `path_id` = '" . (int)$result['path_id'] . "', `level` = '" . (int)$level . "'");

				$level++;
			}

			$this->db->query("REPLACE INTO `" . DB_PREFIX . "blog_category_path` SET `blog_category_id` = '" . (int)$category['blog_category_id'] . "', `path_id` = '" . (int)$category['blog_category_id'] . "', `level` = '" . (int)$level . "'");

			$this->repairCategories($category['blog_category_id']);
		}
	}

	public function getCategory(int $blog_category_id): array {
		$query = $this->db->query("SELECT DISTINCT *, (SELECT GROUP_CONCAT(cd1.`name` ORDER BY `level` SEPARATOR '&nbsp;&nbsp;&gt;&nbsp;&nbsp;') FROM `" . DB_PREFIX . "blog_category_path` cp LEFT JOIN `" . DB_PREFIX . "blog_category_description` cd1 ON (cp.`path_id` = cd1.`blog_category_id` AND cp.`blog_category_id` != cp.`path_id`) WHERE cp.`blog_category_id` = c.`blog_category_id` AND cd1.`language_id` = '" . (int)$this->config->get('config_language_id') . "' GROUP BY cp.`blog_category_id`) AS `path` FROM `" . DB_PREFIX . "blog_category` c LEFT JOIN `" . DB_PREFIX . "blog_category_description` cd2 ON (c.`blog_category_id` = cd2.`blog_category_id`) WHERE c.`blog_category_id` = '" . (int)$blog_category_id . "' AND cd2.`language_id` = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function getPath(int $blog_category_id): string {
		return implode('_', array_column($this->getPaths($blog_category_id), 'path_id'));
	}

	public function getPaths(int $blog_category_id): array {
		$query = $this->db->query("SELECT `blog_category_id`, `path_id`, `level` FROM `" . DB_PREFIX . "blog_category_path` WHERE `blog_category_id` = '" . (int)$blog_category_id . "' ORDER BY `level` ASC");

		return $query->rows;
	}

	public function getCategories(array $data = []): array {
		$sql = "SELECT cp.`blog_category_id` AS `blog_category_id`, GROUP_CONCAT(cd1.`name` ORDER BY cp.`level` SEPARATOR '&nbsp;&nbsp;&gt;&nbsp;&nbsp;') AS `name`, c1.`parent_id`, c1.`sort_order`, c1.`noindex`, c1.`status` FROM `" . DB_PREFIX . "blog_category_path` cp LEFT JOIN `" . DB_PREFIX . "blog_category` c1 ON (cp.`blog_category_id` = c1.`blog_category_id`) LEFT JOIN `" . DB_PREFIX . "blog_category` c2 ON (cp.`path_id` = c2.`blog_category_id`) LEFT JOIN `" . DB_PREFIX . "blog_category_description` cd1 ON (cp.`path_id` = cd1.`blog_category_id`) LEFT JOIN `" . DB_PREFIX . "blog_category_description` cd2 ON (cp.`blog_category_id` = cd2.`blog_category_id`) WHERE cd1.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND cd2.`language_id` = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND cd2.`name` LIKE '" . $this->db->escape('%' . (string)$data['filter_name'] . '%') . "'";
		}

		$sql .= " GROUP BY cp.`blog_category_id`";

		$sort_data = [
			'name',
			'sort_order',
			'noindex',
			'status'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY `" . $data['sort'] . "`";
		} else {
			$sql .= " ORDER BY `sort_order`";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if (!isset($data['start']) || $data['start'] < 0) {
				$data['start'] = 0;
			}

			if (!isset($data['limit']) || $data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getDescriptions(int $blog_category_id): array {
		$category_description_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_category_description` WHERE `blog_category_id` = '" . (int)$blog_category_id . "'");

		foreach ($query->rows as $result) {
			$category_description_data[$result['language_id']] = [
				'name'             => $result['name'],
				'meta_h1'          => $result['meta_h1'],
				'meta_title'       => $result['meta_title'],
				'meta_description' => $result['meta_description'],
				'meta_keyword'     => $result['meta_keyword'],
				'description'      => $result['description']
			];
		}

		return $category_description_data;
	}

	public function getSeoUrls(int $blog_category_id): array {
		$category_seo_url_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'blog_category_id' AND `value` = '" . $this->db->escape($this->getPath($blog_category_id)) . "'");

		foreach ($query->rows as $result) {
			$category_seo_url_data[$result['store_id']][$result['language_id']] = $result['keyword'];
		}

		return $category_seo_url_data;
	}

	public function getStores(int $blog_category_id): array {
		$category_store_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_category_to_store` WHERE `blog_category_id` = '" . (int)$blog_category_id . "'");

		foreach ($query->rows as $result) {
			$category_store_data[] = $result['store_id'];
		}

		return $category_store_data;
	}

	public function getLayouts(int $blog_category_id): array {
		$category_layout_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_category_to_layout` WHERE `blog_category_id` = '" . (int)$blog_category_id . "'");

		foreach ($query->rows as $result) {
			$category_layout_data[$result['store_id']] = $result['layout_id'];
		}

		return $category_layout_data;
	}

	public function getTotalCategories(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "blog_category`");

		return (int)$query->row['total'];
	}
	
	public function getTotalCategoriesByLayoutId(int $layout_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "blog_category_to_layout` WHERE `layout_id` = '" . (int)$layout_id . "'");

		return (int)$query->row['total'];
	}

	public function getCategoriesByParentId(int $parent_id = 0): array {
		$query = $this->db->query("SELECT *, (SELECT COUNT(parent_id) FROM `" . DB_PREFIX . "blog_category` WHERE `parent_id` = c.`blog_category_id`) AS `children` FROM `" . DB_PREFIX . "blog_category` c LEFT JOIN `" . DB_PREFIX . "blog_category_description` cd ON (c.`blog_category_id` = cd.`blog_category_id`) WHERE c.`parent_id` = '" . (int)$parent_id . "' AND cd.`language_id` = '" . (int)$this->config->get('config_language_id') . "' ORDER BY c.`sort_order`, cd.`name`");

		return $query->rows;
	}

	public function getCategoriesChildren(int $path_id = 0): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_category_path` WHERE `path_id` = '" . (int)$path_id . "'");

		return $query->rows;
	}
}