<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

namespace Opencart\Catalog\Model\Extension\ocStore\Feed;

if (!defined('VERSION')) {
	header('Refresh: 1; URL=/');
	exit('ЗАПРЫШЧАЮ!');
}

class Sitemap extends \Opencart\System\Engine\Model {
	public function getLanguage(array $data = []): array {
		return $this->db->query("SELECT language_id, code FROM `" . DB_PREFIX . "language` WHERE `language_id` = '" . (int)$data['language_id'] . "'" . (!empty($data['language']) ? " OR `code` = '" . $this->db->escape($data['language']) . "'" : false))->row;
	}

	public function getBlogCategories(array $data = []): array {
		$sql = "SELECT bc1.`blog_category_id` AS `blog_category_id`, bc1.`image` AS `image`, bc1.`date_modified` AS date_modified, bcd.`name` AS `name`, GROUP_CONCAT(bc2.`blog_category_id` ORDER BY bcp.`level` SEPARATOR '_') AS `path` FROM `" . DB_PREFIX . "blog_category_path` bcp LEFT JOIN `" . DB_PREFIX . "blog_category` bc1 ON (bcp.`blog_category_id` = bc1.`blog_category_id`) LEFT JOIN `" . DB_PREFIX . "blog_category` bc2 ON (bcp.`path_id` = bc2.`blog_category_id`) LEFT JOIN `" . DB_PREFIX . "blog_category_description` bcd ON (bc1.`blog_category_id` = bcd.`blog_category_id`) WHERE bcd.`language_id` = '" . (int)$this->config->get('config_language_id') . "'";

		$sql .= " GROUP BY bcp.`blog_category_id`";

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

	public function getBlogArticles(array $data = []): array {
		$sql = "SELECT a.`article_id` AS `article_id`, a.`image` AS `image`, a.`date_modified` AS date_modified, ad.`name` AS `name` FROM `" . DB_PREFIX . "article` a";

		$sql .= " LEFT JOIN `" . DB_PREFIX . "article_description` ad ON (a.`article_id` = ad.`article_id`) WHERE ad.`language_id` = '" . (int)$this->config->get('config_language_id') . "'";

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

	public function getCategories(array $data = []): array {
		$sql = "SELECT c1.`category_id` AS `category_id`, c1.`image` AS `image`, c1.`date_modified` AS date_modified, cd.`name` AS `name`, GROUP_CONCAT(c2.`category_id` ORDER BY cp.`level` SEPARATOR '_') AS `path` FROM `" . DB_PREFIX . "category_path` cp LEFT JOIN `" . DB_PREFIX . "category` c1 ON (cp.`category_id` = c1.`category_id`) LEFT JOIN `" . DB_PREFIX . "category` c2 ON (cp.`path_id` = c2.`category_id`)";

		$sql .= " LEFT JOIN `" . DB_PREFIX . "category_description` cd ON (c1.`category_id` = cd.`category_id`) WHERE cd.`language_id` = '" . (int)$this->config->get('config_language_id') . "'";

		$sql .= " GROUP BY cp.`category_id`";

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

	public function getInformations(array $data = []): array {
		$sql = "SELECT i.`information_id` AS `information_id`, '' AS `image`, '' AS date_modified, id.`title` AS `name` FROM `" . DB_PREFIX . "information` i";

		$sql .= " LEFT JOIN `" . DB_PREFIX . "information_description` id ON (i.`information_id` = id.`information_id`) WHERE id.`language_id` = '" . (int)$this->config->get('config_language_id') . "'";

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

	public function getManufacturers(array $data = []): array {
		$sql = "SELECT m.`manufacturer_id` AS `manufacturer_id`, m.`image` AS `image`, '' AS date_modified, m.`name` AS `name` FROM `" . DB_PREFIX . "manufacturer` m";

		//$sql .= " LEFT JOIN `" . DB_PREFIX . "manufacturer_description` ON (m.`manufacturer_id` = md.`manufacturer_id`) WHERE md.`language_id` = '" . (int)$this->config->get('config_language_id') . "'";

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

	public function getProducts(array $data = []): array {
		$sql = "SELECT p.`product_id` AS `product_id`, p.`image` AS `image`, p.`date_modified` AS date_modified, pd.`name` AS `name` FROM `" . DB_PREFIX . "product` p";

		$sql .= " LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.`product_id` = pd.`product_id`) WHERE pd.`language_id` = '" . (int)$this->config->get('config_language_id') . "'";

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
}