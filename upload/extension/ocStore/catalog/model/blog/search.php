<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

namespace Opencart\Catalog\Model\Extension\ocStore\Blog;

if (!defined('VERSION')) {
	header('Refresh: 1; URL=/');
	exit('ЗАПРЫШЧАЮ!');
}

class Search extends \Opencart\System\Engine\Model {
	public function addSearch(array $data) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "customer_blog_search` SET `store_id` = '" . (int)$this->config->get('config_store_id') . "', `language_id` = '" . (int)$this->config->get('config_language_id') . "', `customer_id` = '" . (int)$data['customer_id'] . "', `keyword` = '" . $this->db->escape($data['keyword']) . "', `blog_category_id` = '" . (int)$data['blog_category_id'] . "', `sub_category` = '" . (int)$data['sub_category'] . "', `description` = '" . (int)$data['description'] . "', `articles` = '" . (int)$data['articles'] . "', `ip` = '" . $this->db->escape($data['ip']) . "', `date_added` = NOW()");
	}
}