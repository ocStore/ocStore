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

	public function getBlogCategories(array $data = []): array {}
	public function getBlogArticles(array $data = []): array {}
	public function getCategories(array $data = []): array {}
	public function getInformations(array $data = []): array {}
	public function getManufacturers(array $data = []): array {}
	public function getProducts(array $data = []): array {}
}