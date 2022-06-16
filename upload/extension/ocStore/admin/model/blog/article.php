<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

namespace Opencart\Admin\Model\Extension\ocStore\Blog;

if (!defined('VERSION')) {
	header('Refresh: 1; URL=/');
	exit('ЗАПРЫШЧАЮ!');
}

class Article extends \Opencart\System\Engine\Model {
	public function addArticle(array $data): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "article` SET `status` = '" . (int)$data['status'] . "', `noindex` = '" . (int)$data['noindex'] . "', `sort_order` = '" . (int)$data['sort_order'] . "', `date_added` = NOW()");

		$article_id = $this->db->getLastId();

		if (isset($data['image'])) {
			$this->db->query("UPDATE `" . DB_PREFIX . "article` SET `image` = '" . $this->db->escape($data['image']) . "' WHERE `article_id` = '" . (int)$article_id . "'");
		}

		foreach ($data['article_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "article_description` SET `article_id` = '" . (int)$article_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "', `description` = '" . $this->db->escape($value['description']) . "', `tag` = '" . $this->db->escape($value['tag']) . "', `meta_title` = '" . $this->db->escape($value['meta_title']) . "', `meta_h1` = '" . $this->db->escape($value['meta_h1']) . "', `meta_description` = '" . $this->db->escape($value['meta_description']) . "', `meta_keyword` = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		if (isset($data['article_store'])) {
			foreach ($data['article_store'] as $store_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "article_to_store` SET `article_id` = '" . (int)$article_id . "', `store_id` = '" . (int)$store_id . "'");
			}
		}

		if (isset($data['article_image'])) {
			foreach ($data['article_image'] as $article_image) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "article_image` SET `article_id` = '" . (int)$article_id . "', `image` = '" . $this->db->escape($article_image['image']) . "', `sort_order` = '" . (int)$article_image['sort_order'] . "'");
			}
		}

		if (isset($data['article_download'])) {
			foreach ($data['article_download'] as $download_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "article_to_download` SET `article_id` = '" . (int)$article_id . "', `download_id` = '" . (int)$download_id . "'");
			}
		}

		if (isset($data['article_category'])) {
			foreach ($data['article_category'] as $blog_category_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "article_to_blog_category` SET `article_id` = '" . (int)$article_id . "', `blog_category_id` = '" . (int)$blog_category_id . "'");
			}
		}

		if (isset($data['main_blog_category_id']) && $data['main_blog_category_id'] > 0) {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "article_to_blog_category` WHERE `article_id` = '" . (int)$article_id . "' AND `blog_category_id` = '" . (int)$data['main_blog_category_id'] . "'");
			$this->db->query("INSERT INTO `" . DB_PREFIX . "article_to_blog_category` SET `article_id` = '" . (int)$article_id . "', `blog_category_id` = '" . (int)$data['main_blog_category_id'] . "', `main_blog_category` = 1");
		} elseif (isset($data['article_category'][0])) {
			$this->db->query("UPDATE `" . DB_PREFIX . "article_to_blog_category` SET `main_blog_category` = 1 WHERE `article_id` = '" . (int)$article_id . "' AND `blog_category_id` = '" . (int)$data['article_category'][0] . "'");
		}

		if (isset($data['article_related'])) {
			foreach ($data['article_related'] as $related_id) {
				$this->db->query("DELETE FROM `" . DB_PREFIX . "article_related` WHERE `article_id` = '" . (int)$article_id . "' AND `related_id` = '" . (int)$related_id . "'");
				$this->db->query("INSERT INTO `" . DB_PREFIX . "article_related` SET `article_id` = '" . (int)$article_id . "', `related_id` = '" . (int)$related_id . "'");
				$this->db->query("DELETE FROM `" . DB_PREFIX . "article_related` WHERE `article_id` = '" . (int)$related_id . "' AND `related_id` = '" . (int)$article_id . "'");
				$this->db->query("INSERT INTO `" . DB_PREFIX . "article_related` SET `article_id` = '" . (int)$related_id . "', `related_id` = '" . (int)$article_id . "'");
			}
		}

		if (isset($data['article_related_product'])) {
			foreach ($data['article_related_product'] as $related_id) {
				$this->db->query("DELETE FROM `" . DB_PREFIX . "article_related_product` WHERE `article_id` = '" . (int)$article_id . "' AND `product_id` = '" . (int)$related_id . "'");
				$this->db->query("INSERT INTO `" . DB_PREFIX . "article_related_product` SET `article_id` = '" . (int)$article_id . "', `product_id` = '" . (int)$related_id . "'");
			}
		}

		if (isset($data['article_seo_url'])) {
			foreach ($data['article_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET `store_id` = '" . (int)$store_id . "', `language_id` = '" . (int)$language_id . "', `key` = 'article_id', `value` = '" . (int)$article_id . "', `keyword` = '" . $this->db->escape($keyword) . "'");
				}
			}
		}

		if (isset($data['article_layout'])) {
			foreach ($data['article_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "article_to_layout` SET `article_id` = '" . (int)$article_id . "', `store_id` = '" . (int)$store_id . "', `layout_id` = '" . (int)$layout_id . "'");
			}
		}

		$this->cache->delete('article');
		$this->cache->delete('seo_pro');
		$this->cache->delete('seo_url');

		return $article_id;
	}

	public function editArticle(int $article_id, array $data): array {
		$this->db->query("UPDATE `" . DB_PREFIX . "article` SET `status` = '" . (int)$data['status'] . "', `noindex` = '" . (int)$data['noindex'] . "', `sort_order` = '" . (int)$data['sort_order'] . "', `date_modified` = NOW() WHERE `article_id` = '" . (int)$article_id . "'");

		if (isset($data['image'])) {
			$this->db->query("UPDATE `" . DB_PREFIX . "article` SET `image` = '" . $this->db->escape($data['image']) . "' WHERE `article_id` = '" . (int)$article_id . "'");
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "article_description` WHERE `article_id` = '" . (int)$article_id . "'");

		foreach ($data['article_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "article_description` SET `article_id` = '" . (int)$article_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "', `description` = '" . $this->db->escape($value['description']) . "', `tag` = '" . $this->db->escape($value['tag']) . "', `meta_title` = '" . $this->db->escape($value['meta_title']) . "', `meta_h1` = '" . $this->db->escape($value['meta_h1']) . "', meta_description` = '" . $this->db->escape($value['meta_description']) . "', `meta_keyword` = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "article_to_store` WHERE `article_id` = '" . (int)$article_id . "'");

		if (isset($data['article_store'])) {
			foreach ($data['article_store'] as $store_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "article_to_store` SET `article_id` = '" . (int)$article_id . "', `store_id` = '" . (int)$store_id . "'");
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "article_image` WHERE `article_id` = '" . (int)$article_id . "'");

		if (isset($data['article_image'])) {
			foreach ($data['article_image'] as $article_image) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "article_image` SET `article_id` = '" . (int)$article_id . "', `image` = '" . $this->db->escape($article_image['image']) . "', `sort_order` = '" . (int)$article_image['sort_order'] . "'");
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "article_to_download` WHERE `article_id` = '" . (int)$article_id . "'");

		if (isset($data['article_download'])) {
			foreach ($data['article_download'] as $download_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "article_to_download` SET `article_id` = '" . (int)$article_id . "', `download_id` = '" . (int)$download_id . "'");
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "article_to_blog_category` WHERE `article_id` = '" . (int)$article_id . "'");

		if (isset($data['article_category'])) {
			foreach ($data['article_category'] as $blog_category_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "article_to_blog_category` SET `article_id` = '" . (int)$article_id . "', `blog_category_id` = '" . (int)$blog_category_id . "'");
			}
		}

		if (isset($data['main_blog_category_id']) && $data['main_blog_category_id'] > 0) {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "article_to_blog_category` WHERE `article_id` = '" . (int)$article_id . "' AND `blog_category_id` = '" . (int)$data['main_blog_category_id'] . "'");
			$this->db->query("INSERT INTO `" . DB_PREFIX . "article_to_blog_category` SET `article_id` = '" . (int)$article_id . "', `blog_category_id` = '" . (int)$data['main_blog_category_id'] . "', `main_blog_category` = 1");
		} elseif (isset($data['article_category'][0])) {
			$this->db->query("UPDATE `" . DB_PREFIX . "article_to_blog_category` SET `main_blog_category` = 1 WHERE `article_id` = '" . (int)$article_id . "' AND `blog_category_id` = '" . (int)$data['article_category'][0] . "'");
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "article_related` WHERE `article_id` = '" . (int)$article_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "article_related` WHERE `related_id` = '" . (int)$article_id . "'");

		if (isset($data['article_related'])) {
			foreach ($data['article_related'] as $related_id) {
				$this->db->query("DELETE FROM `" . DB_PREFIX . "article_related` WHERE `article_id` = '" . (int)$article_id . "' AND `related_id` = '" . (int)$related_id . "'");
				$this->db->query("INSERT INTO `" . DB_PREFIX . "article_related` SET `article_id` = '" . (int)$article_id . "', `related_id` = '" . (int)$related_id . "'");
				$this->db->query("DELETE FROM `" . DB_PREFIX . "article_related` WHERE `article_id` = '" . (int)$related_id . "' AND `related_id` = '" . (int)$article_id . "'");
				$this->db->query("INSERT INTO `" . DB_PREFIX . "article_related` SET `article_id` = '" . (int)$related_id . "', `related_id` = '" . (int)$article_id . "'");
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "article_related_product` WHERE `article_id` = '" . (int)$article_id . "'");

		if (isset($data['article_related_product'])) {
			foreach ($data['article_related_product'] as $related_id) {
				$this->db->query("DELETE FROM `" . DB_PREFIX . "article_related_product` WHERE `article_id` = '" . (int)$article_id . "' AND `product_id` = '" . (int)$related_id . "'");
				$this->db->query("INSERT INTO `" . DB_PREFIX . "article_related_product` SET `article_id` = '" . (int)$article_id . "', `product_id` = '" . (int)$related_id . "'");
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'article_id' AND `value` = '" . (int)$article_id . "'");

		if (isset($data['article_seo_url'])) {
			foreach ($data['article_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET `store_id` = '" . (int)$store_id . "', `language_id` = '" . (int)$language_id . "', `key` = 'article_id', `value` = '" . (int)$article_id . "', `keyword` = '" . $this->db->escape($keyword) . "'");
				}
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "article_to_layout` WHERE `article_id` = '" . (int)$article_id . "'");

		if (isset($data['article_layout'])) {
			foreach ($data['article_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "article_to_layout` SET `article_id` = '" . (int)$article_id . "', `store_id` = '" . (int)$store_id . "', `layout_id` = '" . (int)$layout_id . "'");
			}
		}

		$this->cache->delete('article');
		$this->cache->delete('seo_pro');
		$this->cache->delete('seo_url');
	}

	public function editArticleStatus(int $article_id, int|bool $status): int {
		$this->db->query("UPDATE `" . DB_PREFIX . "article` SET `status` = '" . (int)$status . "', `date_modified` = NOW() WHERE `article_id` = '" . (int)$article_id . "'");

		$this->cache->delete('article');

		return $article_id;
	}

	public function copyArticle(int $article_id): void {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "article` p LEFT JOIN `" . DB_PREFIX . "article_description` pd ON (p.`article_id` = pd.`article_id`) WHERE p.`article_id` = '" . (int)$article_id . "' AND pd.`language_id` = '" . (int)$this->config->get('config_language_id') . "'");

		if ($query->num_rows) {
			$data = $query->row;

			$data['viewed'] = '0';
			$data['status'] = '0';
			$data['noindex'] = '0';

			$data['article_description'] = $this->getDescriptions($article_id);
			$data['article_image'] = $this->getImages($article_id);
			$data['article_related'] = $this->getRelated($article_id);
			$data['article_related_product'] = $this->getProductRelated($article_id);
			$data['article_category'] = $this->getCategories($article_id);
			$data['article_download'] = $this->getDownloads($article_id);
			$data['article_layout'] = $this->getLayouts($article_id);
			$data['article_store'] = $this->getStores($article_id);

			$this->addArticle($data);
		}
	}

	public function deleteArticle(int $article_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "article` WHERE `article_id` = '" . (int)$article_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "article_description` WHERE `article_id` = '" . (int)$article_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "article_image` WHERE `article_id` = '" . (int)$article_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "article_related` WHERE `article_id` = '" . (int)$article_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "article_related` WHERE `related_id` = '" . (int)$article_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "article_related_product` WHERE `article_id` = '" . (int)$article_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "article_to_blog_category` WHERE `article_id` = '" . (int)$article_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "article_to_download` WHERE `article_id` = '" . (int)$article_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "article_to_layout` WHERE `article_id` = '" . (int)$article_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "article_to_store` WHERE `article_id` = '" . (int)$article_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "review_article` WHERE `article_id` = '" . (int)$article_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'article_id' AND `value` = '" . (int)$article_id . "'");

		$this->cache->delete('article');
	}

	public function getArticle(int $article_id): array {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "article` p LEFT JOIN `" . DB_PREFIX . "article_description` pd ON (p.`article_id` = pd.`article_id`) WHERE p.`article_id` = '" . (int)$article_id . "' AND pd.`language_id` = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function getArticles(array $data = []): array {
		$sql = "SELECT p.*, pd.* FROM `" . DB_PREFIX . "article` p LEFT JOIN `" . DB_PREFIX . "article_description` pd ON (p.`article_id` = pd.`article_id`)";

		if (isset($data['filter_category'])) {
			$sql .= " LEFT JOIN `" . DB_PREFIX . "article_to_blog_category` a2c ON (p.`article_id` = a2c.`article_id`)";
		}

		$sql .= " WHERE pd.`language_id` = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND pd.`name` LIKE '" . $this->db->escape("%" . (string)$data['filter_name'] . "%") . "'";
		}

		if (isset($data['filter_category']) && !is_null($data['filter_category'])) {
			if (!empty($data['filter_category']) && !empty($data['filter_sub_category'])) {
				$implode_data = [];

				$this->load->model('extension/ocStore/blog/category');

				$categories = $this->model_extension_ocStore_blog_category->getCategoriesChildren($data['filter_category']);

				foreach ($categories as $category) {
					$implode_data[] = "a2c.blog_category_id = '" . (int)$category['blog_category_id'] . "'";
				}

				if ($implode_data) {
					$sql .= " AND (" . implode(' OR ', $implode_data) . ")";
				}
			} else {
				if ((int)$data['filter_category'] > 0) {
					$sql .= " AND a2c.`blog_category_id` = '" . (int)$data['filter_category'] . "'";
				} else {
					$sql .= " AND a2c.`blog_category_id` IS NULL";
				}
			}
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$sql .= " AND p.`status` = '" . (int)$data['filter_status'] . "'";
		}

		if (isset($data['filter_noindex']) && !is_null($data['filter_noindex'])) {
			$sql .= " AND p.`noindex` = '" . (int)$data['filter_noindex'] . "'";
		}

		$sql .= " GROUP BY p.`article_id`";

		$sort_data = [
			'name',
			'sort_order',
			'noindex',
			'status'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY `" . $data['sort'] . "`";
		} else {
			$sql .= " ORDER BY pd.`name`";
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

	public function getDescriptions(int $article_id): array {
		$article_description_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "article_description` WHERE `article_id` = '" . (int)$article_id . "'");

		foreach ($query->rows as $result) {
			$article_description_data[$result['language_id']] = [
				'name'             => $result['name'],
				'description'      => $result['description'],
				'meta_title'       => $result['meta_title'],
				'meta_h1'          => $result['meta_h1'],
				'meta_description' => $result['meta_description'],
				'meta_keyword'     => $result['meta_keyword'],
				'tag'              => $result['tag']
			];
		}

		return $article_description_data;
	}

	public function getCategories(int $article_id): array {
		$article_category_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "article_to_blog_category` WHERE `article_id` = '" . (int)$article_id . "'");

		foreach ($query->rows as $result) {
			$article_category_data[] = $result['blog_category_id'];
		}

		return $article_category_data;
	}

	public function getMainCategoryId(int $article_id): int {
		$query = $this->db->query("SELECT `blog_category_id` FROM `" . DB_PREFIX . "article_to_blog_category` WHERE `article_id` = '" . (int)$article_id . "' AND main_blog_category = '1' LIMIT 1");

		return ($query->num_rows ? (int)$query->row['blog_category_id'] : 0);
	}

	public function getImages(int $article_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "article_image` WHERE `article_id` = '" . (int)$article_id . "' ORDER BY `sort_order` ASC");

		return $query->rows;
	}

	public function getDownloads(int $article_id): array {
		$article_download_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "article_to_download` WHERE `article_id` = '" . (int)$article_id . "'");

		foreach ($query->rows as $result) {
			$article_download_data[] = $result['download_id'];
		}

		return $article_download_data;
	}

	public function getStores(int $article_id): array {
		$article_store_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "article_to_store` WHERE `article_id` = '" . (int)$article_id . "'");

		foreach ($query->rows as $result) {
			$article_store_data[] = $result['store_id'];
		}

		return $article_store_data;
	}

	public function getLayouts(int $article_id): array {
		$article_layout_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "article_to_layout` WHERE `article_id` = '" . (int)$article_id . "'");

		foreach ($query->rows as $result) {
			$article_layout_data[$result['store_id']] = $result['layout_id'];
		}

		return $article_layout_data;
	}

	public function getRelated(int $article_id): array {
		$article_related_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "article_related` WHERE `article_id` = '" . (int)$article_id . "'");

		foreach ($query->rows as $result) {
			if ($result['related_id'] != $article_id) {
				$article_related_data[] = $result['related_id'];
			}
		}

		return $article_related_data;
	}

	public function getProductRelated(int $article_id): array {
		$article_related_product = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "article_related_product` WHERE `article_id` = '" . (int)$article_id . "'");

		foreach ($query->rows as $result) {
			$article_related_product[] = $result['product_id'];
		}

		return $article_related_product;
	}

	public function getTotalArticles(array $data = []): int {
		$sql = "SELECT COUNT(DISTINCT p.article_id) AS `total` FROM `" . DB_PREFIX . "article` p LEFT JOIN `" . DB_PREFIX . "article_description` pd ON (p.`article_id` = pd.`article_id`)";

		if (isset($data['filter_category']) && !is_null($data['filter_category'])) {
			$sql .= " LEFT JOIN `" . DB_PREFIX . "article_to_blog_category` a2c ON (p.`article_id` = a2c.`article_id`)";
		}

		$sql .= " WHERE pd.`language_id` = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND pd.`name` LIKE '" . $this->db->escape("%" . $data['filter_name'] . "%") . "'";
		}

		if (isset($data['filter_category']) && !is_null($data['filter_category'])) {
			if (!empty($data['filter_category']) && !empty($data['filter_sub_category'])) {
				$implode_data = [];

				$this->load->model('extension/ocStore/blog/category');

				$categories = $this->model_extension_ocStore_blog_category->getCategoriesChildren($data['filter_category']);

				foreach ($categories as $category) {
					$implode_data[] = "a2c.`blog_category_id` = '" . (int)$category['blog_category_id'] . "'";
				}

				if ($implode_data) {
					$sql .= " AND (" . implode(' OR ', $implode_data) . ")";
				}
			} else {
				if ((int)$data['filter_category'] > 0) {
					$sql .= " AND a2c.`blog_category_id` = '" . (int)$data['filter_category'] . "'";
				} else {
					$sql .= " AND a2c.`blog_category_id` IS NULL";
				}
			}
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$sql .= " AND p.`status` = '" . (int)$data['filter_status'] . "'";
		}

		if (isset($data['filter_noindex']) && $data['filter_noindex'] !== null) {
			$sql .= " AND p.`noindex` = '" . (int)$data['filter_noindex'] . "'";
		}

		$query = $this->db->query($sql);

		return (int)$query->row['total'];
	}

	public function getTotalArticlesByDownloadId(int $download_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "article_to_download` WHERE `download_id` = '" . (int)$download_id . "'");

		return (int)$query->row['total'];
	}

	public function getTotalArticlesByLayoutId(int $layout_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "article_to_layout` WHERE `layout_id` = '" . (int)$layout_id . "'");

		return (int)$query->row['total'];
	}
}