<?php
namespace Opencart\Catalog\Model\Cms;
/**
 * Class Article
 *
 * Can be called using $this->load->model('cms/article');
 *
 * @package Opencart\Catalog\Model\Cms
 */
class Article extends \Opencart\System\Engine\Model {
	/**
	 * Get Article
	 *
	 * Get the record of the article record in the database.
	 *
	 * @param int $article_id primary key of the article record
	 *
	 * @return array<string, mixed> article record that has article ID
	 *
	 * @example
	 *
	 * $this->load->model('cms/article');
	 *
	 * $article_info = $this->model_cms_article->getArticle($article_id);
	 */
	public function getArticle(int $article_id): array {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "article` `a` LEFT JOIN `" . DB_PREFIX . "article_description` `ad` ON (`a`.`article_id` = `ad`.`article_id`) LEFT JOIN `" . DB_PREFIX . "article_to_store` `a2s` ON (`a`.`article_id` = `a2s`.`article_id`) WHERE `a`.`article_id` = '" . (int)$article_id . "' AND `ad`.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND `a2s`.`store_id` = '" . (int)$this->config->get('config_store_id') . "' AND `a`.`status` = '1'");

		return $query->row;
	}

	/**
	 * Get Articles
	 *
	 * Get the record of the article records in the database.
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return array<int, array<string, mixed>> article records
	 *
	 * @example
	 *
	 * $this->load->model('cms/article');
	 *
	 * $results = $this->model_cms_article->getArticles();
	 */
	public function getArticles(array $data = []): array {
		$sql = "SELECT * FROM `" . DB_PREFIX . "article` `a` LEFT JOIN `" . DB_PREFIX . "article_description` `ad` ON (`a`.`article_id` = `ad`.`article_id`) LEFT JOIN `" . DB_PREFIX . "article_to_store` `a2s` ON (`a`.`article_id` = `a2s`.`article_id`) WHERE `ad`.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND `a2s`.`store_id` = '" . (int)$this->config->get('config_store_id') . "' AND `a`.`status` = '1'";

		if (!empty($data['filter_search'])) {
			$sql .= " AND (";

			$implode = [];

			$words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_search'])));
			$words = array_filter($words);

			foreach ($words as $word) {
				$implode[] = "`ad`.`name` LIKE '" . $this->db->escape('%' . $word . '%') . "'";
			}

			if ($implode) {
				$sql .= " (" . implode(" OR ", $implode) . ")";
			}

			$sql .= " OR `ad`.`description` LIKE '" . $this->db->escape('%' . (string)$data['filter_search'] . '%') . "'";

			$implode = [];

			foreach ($words as $word) {
				$implode[] = "`ad`.`tag` LIKE '" . $this->db->escape('%' . $word . '%') . "'";
			}

			if ($implode) {
				$sql .= " OR (" . implode(" OR ", $implode) . ")";
			}

			$sql .= ")";
		}

		if (!empty($data['filter_topic_id'])) {
			if (!empty($data['filter_sub_topic'])) {
				$sql .= " AND `a`.`article_id` IN (SELECT `a2t`.`article_id` FROM `" . DB_PREFIX . "article_to_topic` `a2t` LEFT JOIN `" . DB_PREFIX . "topic_path` `tp` ON (`a2t`.`topic_id` = `tp`.`topic_id`) WHERE `tp`.`path_id` = '" . (int)$data['filter_topic_id'] . "')";
			} else {
				$sql .= " AND `a`.`article_id` IN (SELECT `a2t`.`article_id` FROM `" . DB_PREFIX . "article_to_topic` `a2t` WHERE `a2t`.`topic_id` = '" . (int)$data['filter_topic_id'] . "')";
			}
		}

		if (!empty($data['filter_author'])) {
			$sql .= " AND `a`.`author` = '" . $this->db->escape($data['filter_author']) . "'";
		}

		$sort_data = [
			'rating',
			'date_added'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY `a`.`" . $data['sort'] . "`";
		} else {
			$sql .= " ORDER BY `a`.`date_added`";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			$start = isset($data['start']) ? (int)$data['start'] : 0;
			$limit = isset($data['limit']) ? (int)$data['limit'] : 20;

			if ($start < 0) {
				$start = 0;
			}

			if ($limit < 1) {
				$limit = 20;
			}

			$sql .= " LIMIT " . $start . "," . $limit;
		}

		$key = md5($sql);

		$article_data = $this->cache->get('article.' . $key);

		if (!$article_data) {
			$query = $this->db->query($sql);

			$article_data = $query->rows;

			$this->cache->set('article.' . $key, $article_data);
		}

		return $article_data;
	}

	/**
	 * Edit Rating
	 *
	 * Edit article rating record in the database.
	 *
	 * @param int $article_id primary key of the article record
	 * @param int $rating
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->load->model('cms/article');
	 *
	 * $this->model_cms_article->editRating($article_id, $rating);
	 */
	public function editRating(int $article_id, int $rating): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "article` SET `rating` = '" . (int)$rating . "' WHERE `article_id` = '" . (int)$article_id . "'");
	}

	/**
	 * Get Total Articles
	 *
	 * Get the total number of total article records in the database.
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return int total number of article records
	 *
	 * @example
	 *
	 * $this->load->model('cms/article');
	 *
	 * $article_total = $this->model_cms_article->getTotalArticles();
	 */
	public function getTotalArticles(array $data = []): int {
		$sql = "SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "article` `a` LEFT JOIN `" . DB_PREFIX . "article_description` `ad` ON (`a`.`article_id` = `ad`.`article_id`) LEFT JOIN `" . DB_PREFIX . "article_to_store` `a2s` ON (`a`.`article_id` = `a2s`.`article_id`) WHERE `ad`.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND `a2s`.`store_id` = '" . (int)$this->config->get('config_store_id') . "' AND `a`.`status` = '1'";

		if (!empty($data['filter_search'])) {
			$sql .= " AND (";

			$implode = [];

			$words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_search'])));
			$words = array_filter($words);

			foreach ($words as $word) {
				$implode[] = "`ad`.`name` LIKE '" . $this->db->escape('%' . $word . '%') . "'";
			}

			if ($implode) {
				$sql .= " (" . implode(" OR ", $implode) . ")";
			}

			$sql .= " OR `ad`.`description` LIKE '" . $this->db->escape('%' . (string)$data['filter_search'] . '%') . "'";

			$implode = [];

			foreach ($words as $word) {
				$implode[] = "`ad`.`tag` LIKE '" . $this->db->escape('%' . $word . '%') . "'";
			}

			if ($implode) {
				$sql .= " OR (" . implode(" OR ", $implode) . ")";
			}

			$sql .= ")";
		}

		if (!empty($data['filter_topic_id'])) {
			if (!empty($data['filter_sub_topic'])) {
				$sql .= " AND `a`.`article_id` IN (SELECT `a2t`.`article_id` FROM `" . DB_PREFIX . "article_to_topic` `a2t` LEFT JOIN `" . DB_PREFIX . "topic_path` `tp` ON (`a2t`.`topic_id` = `tp`.`topic_id`) WHERE `tp`.`path_id` = '" . (int)$data['filter_topic_id'] . "')";
			} else {
				$sql .= " AND `a`.`article_id` IN (SELECT `a2t`.`article_id` FROM `" . DB_PREFIX . "article_to_topic` `a2t` WHERE `a2t`.`topic_id` = '" . (int)$data['filter_topic_id'] . "')";
			}
		}

		if (!empty($data['filter_author'])) {
			$sql .= " AND `a`.`author` = '" . $this->db->escape($data['filter_author']) . "'";
		}

		$query = $this->db->query($sql);

		return (int)$query->row['total'];
	}

	/**
	 * Get Layout ID
	 *
	 * Get the record of the article layout record in the database.
	 *
	 * @param int $article_id primary key of the article record
	 *
	 * @return int total number of layout records that have article ID
	 *
	 * @example
	 *
	 * $this->load->model('cms/article');
	 *
	 * $layout_id = $this->model_cms_article->getLayoutId($article_id);
	 */
	public function getLayoutId(int $article_id): int {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "article_to_layout` WHERE `article_id` = '" . (int)$article_id . "' AND `store_id` = '" . (int)$this->config->get('config_store_id') . "'");

		if ($query->num_rows) {
			return (int)$query->row['layout_id'];
		} else {
			return 0;
		}
	}

	/**
	 * Add Comment
	 *
	 * Create a new article comment record in the database.
	 *
	 * @param int                  $article_id primary key of the article record
	 * @param array<string, mixed> $data       array of data
	 *
	 * @return int
	 *
	 * @example
	 *
	 * $article_data = [
	 *     'parent_id' => 0,
	 *     'author'    => 'Author Name',
	 *     'comment'   => '',
	 *     'ip'        => '',
	 *     'status'    => 0
	 * ];
	 *
	 * $this->load->model('cms/article');
	 *
	 * $this->model_cms_article->addComment($article_id, $article_data);
	 */
	public function addComment(int $article_id, array $data): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "article_comment` SET `article_id` = '" . (int)$article_id . "', `parent_id` = '" . (int)$data['parent_id'] . "', `customer_id` = '" . (int)$this->customer->getId() . "', `author` = '" . $this->db->escape($data['author']) . "', `comment` = '" . $this->db->escape($data['comment']) . "', `ip` = '" . $this->db->escape(oc_get_ip()) . "', `status` = '" . (bool)!empty($data['status']) . "', `date_added` = NOW()");

		$this->cache->delete('comment');

		return $this->db->getLastId();
	}

	/**
	 * Edit Comment Rating
	 *
	 * Edit article comment rating record in the database.
	 *
	 * @param int $article_id         primary key of the article record
	 * @param int $article_comment_id primary key of the article comment record
	 * @param int $rating
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->load->model('cms/article');
	 *
	 * $this->model_cms_article->editCommentRating($article_id, $article_comment_id, $rating);
	 */
	public function editCommentRating(int $article_id, int $article_comment_id, int $rating): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "article_comment` SET `rating` = '" . (int)$rating . "' WHERE `article_comment_id` = '" . (int)$article_comment_id . "' AND `article_id` = '" . (int)$article_id . "'");
	}

	/**
	 * Get Comment
	 *
	 * Get the record of the article comment record in the database.
	 *
	 * @param int $article_comment_id primary key of the article comment record
	 *
	 * @return array<string, mixed> comment record that has article comment ID
	 *
	 * @example
	 *
	 * $this->load->model('cms/article');
	 *
	 * $comment_info = $this->model_cms_article->getComment($article_comment_id);
	 */
	public function getComment(int $article_comment_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "article_comment` WHERE `article_comment_id` = '" . (int)$article_comment_id . "' AND `status` = '1'");

		return $query->row;
	}

	/**
	 * Get Comments
	 *
	 * Get the record of the article comment records in the database.
	 *
	 * @param int                  $article_id primary key of the article record
	 * @param array<string, mixed> $data       array of filters
	 *
	 * @return array<int, array<string, mixed>> comment records that have article ID
	 *
	 * @example
	 *
	 * $filter_data = [
	 *     'parent_id' => 0,
	 *     'sort'      => 'date_added',
	 *     'order'     => 'DESC',
	 *     'start'     => 0,
	 *     'limit'     => 10
	 * ];
	 *
	 * $this->load->model('cms/article');
	 *
	 * $results = $this->model_cms_article->getComments($article_id, $filter_data);
	 */
	public function getComments(int $article_id, array $data = []): array {
		$sql = "SELECT * FROM `" . DB_PREFIX . "article_comment` WHERE `article_id` = '" . (int)$article_id . "'";

		if (!empty($data['customer_id'])) {
			$sql .= " AND `customer_id` = '" . (int)$data['customer_id'] . "'";
		}

		if (isset($data['parent_id'])) {
			$sql .= " AND `parent_id` = '" . (int)$data['parent_id'] . "'";
		}

		$sql .= " AND `status` = '1'";

		$sort_data = [
			'rating',
			'date_added'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY `" . $data['sort'] . "`";
		} else {
			$sql .= " ORDER BY `date_added`";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			$start = isset($data['start']) ? (int)$data['start'] : 0;
			$limit = isset($data['limit']) ? (int)$data['limit'] : 20;

			if ($start < 0) {
				$start = 0;
			}

			if ($limit < 1) {
				$limit = 20;
			}

			$sql .= " LIMIT " . $start . "," . $limit;
		}

		$key = md5($sql);

		$comment_data = $this->cache->get('comment.' . $key);

		if (!$comment_data) {
			$query = $this->db->query($sql);

			$comment_data = $query->rows;

			$this->cache->set('comment.' . $key, $comment_data);
		}

		return $comment_data;
	}

	/**
	 * Get Total Comments
	 *
	 * Get the total number of total article comment records in the database.
	 *
	 * @param int                  $article_id primary key of the article record
	 * @param array<string, mixed> $data       array of filters
	 *
	 * @return int total number of comment records that have article ID
	 *
	 * @example
	 *
	 * $filter_data = [
	 *     'parent_id' => 0,
	 *     'sort'      => 'date_added',
	 *     'order'     => 'DESC',
	 *     'start'     => 0,
	 *     'limit'     => 10
	 * ];
	 *
	 * $this->load->model('cms/article');
	 *
	 * $comment_total = $this->model_cms_article->getTotalComments($article_id, $filter_data);
	 */
	public function getTotalComments(int $article_id, array $data = []): int {
		$sql = "SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "article_comment` WHERE `article_id` = '" . (int)$article_id . "'";

		if (!empty($data['customer_id'])) {
			$sql .= " AND `customer_id` = '" . (int)$data['customer_id'] . "'";
		}

		if (isset($data['parent_id'])) {
			$sql .= " AND `parent_id` = '" . (int)$data['parent_id'] . "'";
		}

		$sql .= " AND `status` = '1'";

		$query = $this->db->query($sql);

		return (int)$query->row['total'];
	}

	/**
	 * Add Rating
	 *
	 * Create a new article rating record in the database.
	 *
	 * @param int  $article_id         primary key of the article record
	 * @param int  $article_comment_id primary key of the article comment record
	 * @param bool $rating
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->load->model('cms/article');
	 *
	 * $this->model_cms_article->addRating($article_id, $article_comment_id, $rating);
	 */
	public function addRating(int $article_id, int $article_comment_id, bool $rating): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "article_rating` SET `article_comment_id` = '" . (int)$article_comment_id . "', `article_id` = '" . (int)$article_id . "', `store_id` = '" . (int)$this->config->get('config_store_id') . "', `customer_id` = '" . (int)$this->customer->getId() . "', `rating` = '" . (bool)$rating . "', `ip` = '" . $this->db->escape(oc_get_ip()) . "', `date_added` = NOW()");
	}

	/**
	 * Delete Rating
	 *
	 * Delete article rating record in the database.
	 *
	 * @param int $article_id         primary key of the article record
	 * @param int $article_comment_id primary key of the article comment record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->load->model('cms/article');
	 *
	 * $this->model_cms_article->deleteRating($article_id, $article_comment_id);
	 */
	public function deleteRating(int $article_id, int $article_comment_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "article_rating` WHERE `article_comment_id` = '" . (int)$article_comment_id . "' AND `article_id` = '" . (int)$article_id . "' AND `customer_id` = '" . (int)$this->customer->getId() . "'");
	}

	/**
	 * Get Ratings
	 *
	 * Get the record of the article rating records in the database.
	 *
	 * @param int $article_id         primary key of the article record
	 * @param int $article_comment_id primary key of the article comment record
	 *
	 * @return array<int, array<string, mixed>> rating records that have article ID
	 *
	 * @example
	 *
	 * $this->load->model('cms/article');
	 *
	 * $results = $this->model_cms_article->getRatings($article_id, $article_comment_id);
	 */
	public function getRatings(int $article_id, int $article_comment_id = 0): array {
		$sql = "SELECT `rating`, COUNT(*) AS `total` FROM `" . DB_PREFIX . "article_rating` WHERE `article_id` = '" . (int)$article_id . "'";

		if ($article_comment_id) {
			$sql .= " AND `article_comment_id` = '" . (int)$article_comment_id . "'";
		}

		$sql .= " GROUP BY `rating`";

		$query = $this->db->query($sql);

		return $query->rows;
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function getRelated(int $article_id): array {
		$article_data = [];

		$query = $this->db->query("SELECT `ar`.`related_id` FROM `" . DB_PREFIX . "article_related` `ar` LEFT JOIN `" . DB_PREFIX . "article` `a` ON (`ar`.`related_id` = `a`.`article_id`) LEFT JOIN `" . DB_PREFIX . "article_to_store` `a2s` ON (`a`.`article_id` = `a2s`.`article_id`) WHERE `ar`.`article_id` = '" . (int)$article_id . "' AND `a`.`status` = '1' AND `a2s`.`store_id` = '" . (int)$this->config->get('config_store_id') . "'");

		foreach ($query->rows as $result) {
			$article_info = $this->model_cms_article->getArticle($result['related_id']);

			if ($article_info) {
				$article_data[$result['related_id']] = $article_info;
			}
		}

		return $article_data;
	}

	/**
	 * @return array<int, int>
	 */
	public function getProducts(int $article_id): array {
		$query = $this->db->query("SELECT `product_id` FROM `" . DB_PREFIX . "article_to_product` WHERE `article_id` = '" . (int)$article_id . "'");

		return array_column($query->rows, 'product_id');
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function getArticlesByProductId(int $product_id): array {
		$article_data = [];

		$query = $this->db->query("SELECT `a2p`.`article_id` FROM `" . DB_PREFIX . "article_to_product` `a2p` LEFT JOIN `" . DB_PREFIX . "article` `a` ON (`a2p`.`article_id` = `a`.`article_id`) LEFT JOIN `" . DB_PREFIX . "article_to_store` `a2s` ON (`a`.`article_id` = `a2s`.`article_id`) WHERE `a2p`.`product_id` = '" . (int)$product_id . "' AND `a`.`status` = '1' AND `a2s`.`store_id` = '" . (int)$this->config->get('config_store_id') . "'");

		foreach ($query->rows as $result) {
			$article_info = $this->model_cms_article->getArticle($result['article_id']);

			if ($article_info) {
				$article_data[$result['article_id']] = $article_info;
			}
		}

		return $article_data;
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function getDownloads(int $article_id): array {
		$query = $this->db->query("SELECT `d`.* FROM `" . DB_PREFIX . "article_to_download` `a2d` LEFT JOIN `" . DB_PREFIX . "download` `d` ON (`a2d`.`download_id` = `d`.`download_id`) LEFT JOIN `" . DB_PREFIX . "download_description` `dd` ON (`d`.`download_id` = `dd`.`download_id`) WHERE `a2d`.`article_id` = '" . (int)$article_id . "' AND `dd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->rows;
	}

	public function updateViewed(int $article_id): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "article` SET `viewed` = `viewed` + 1 WHERE `article_id` = '" . (int)$article_id . "'");
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function getArticlesByManufacturerId(int $manufacturer_id): array {
		$article_data = [];

		$query = $this->db->query("SELECT `a2m`.`article_id` FROM `" . DB_PREFIX . "article_to_manufacturer` `a2m` LEFT JOIN `" . DB_PREFIX . "article` `a` ON (`a2m`.`article_id` = `a`.`article_id`) LEFT JOIN `" . DB_PREFIX . "article_to_store` `a2s` ON (`a`.`article_id` = `a2s`.`article_id`) WHERE `a2m`.`manufacturer_id` = '" . (int)$manufacturer_id . "' AND `a`.`status` = '1' AND `a2s`.`store_id` = '" . (int)$this->config->get('config_store_id') . "'");

		foreach ($query->rows as $result) {
			$article_info = $this->model_cms_article->getArticle($result['article_id']);

			if ($article_info) {
				$article_data[$result['article_id']] = $article_info;
			}
		}

		return $article_data;
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function getArticlesByCategoryId(int $category_id): array {
		$article_data = [];

		$query = $this->db->query("SELECT `a2c`.`article_id` FROM `" . DB_PREFIX . "article_to_category` `a2c` LEFT JOIN `" . DB_PREFIX . "article` `a` ON (`a2c`.`article_id` = `a`.`article_id`) LEFT JOIN `" . DB_PREFIX . "article_to_store` `a2s` ON (`a`.`article_id` = `a2s`.`article_id`) WHERE `a2c`.`category_id` = '" . (int)$category_id . "' AND `a`.`status` = '1' AND `a2s`.`store_id` = '" . (int)$this->config->get('config_store_id') . "'");

		foreach ($query->rows as $result) {
			$article_info = $this->model_cms_article->getArticle($result['article_id']);

			if ($article_info) {
				$article_data[$result['article_id']] = $article_info;
			}
		}

		return $article_data;
	}
}
