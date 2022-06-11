<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

namespace Opencart\Catalog\Model\Extension\ocStore\Blog;

if (!defined('VERSION')) {
	header('Refresh: 1; URL=/');
	exit('ЗАПРЫШЧАЮ!');
}

class Category extends \Opencart\System\Engine\Model {
	public function getCategory(int $blog_category_id): array {
		return $this->getCategories((int)$blog_category_id, 'by_id');
	}

	public function getCategories(int $id = 0, string $type = 'by_parent'): array {
		static $data = null;

		if ($data === null) {
			$data = [];

			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "blog_category c LEFT JOIN " . DB_PREFIX . "blog_category_description cd ON (c.blog_category_id = cd.blog_category_id) LEFT JOIN " . DB_PREFIX . "blog_category_to_store c2s ON (c.blog_category_id = c2s.blog_category_id) WHERE cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND c.status = '1' ORDER BY c.parent_id, c.sort_order, cd.name");

			foreach ($query->rows as $row) {
				$data['by_id'][$row['blog_category_id']] = $row;
				$data['by_parent'][$row['parent_id']][] = $row;
			}
		}

		return ((isset($data[$type]) && isset($data[$type][$id])) ? $data[$type][$id] : []);
	}

	public function getCategoriesByParentId(int $blog_category_id): array {
		$category_data = [];

		$categories = $this->getCategories((int)$blog_category_id);

		foreach ($categories as $category) {
			$category_data[] = $category['blog_category_id'];

			$children = $this->getCategoriesByParentId($category['blog_category_id']);

			if ($children) {
				$category_data = array_merge($children, $category_data);
			}
		}

		return $category_data;
	}

	public function getCategoryLayoutId(int $blog_category_id): int {
		$query = $this->db->query("SELECT layout_id FROM " . DB_PREFIX . "blog_category_to_layout WHERE blog_category_id = '" . (int)$blog_category_id . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "'");

		if ($query->num_rows) {
			return (int)$query->row['layout_id'];
		} else {
			return 0;
		}
	}

	public function getTotalCategoriesByCategoryId(int $parent_id = 0): int {
		return count($this->getCategories((int)$parent_id));
	}
}