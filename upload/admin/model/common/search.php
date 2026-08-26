<?php
namespace Opencart\Admin\Model\Common;
/**
 * Class Search
 *
 * Can be loaded using $this->load->model('common/search');
 *
 * @package Opencart\Admin\Model\Common
 */
class Search extends \Opencart\System\Engine\Model {
	/**
	 * Get Products
	 *
	 * @param string $filter_name
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getProducts(string $filter_name): array {
		$query = $this->db->query("SELECT `p`.`product_id`, `pd`.`name`, `p`.`model`, `p`.`image` FROM `" . DB_PREFIX . "product` `p` LEFT JOIN `" . DB_PREFIX . "product_description` `pd` ON (`p`.`product_id` = `pd`.`product_id`) LEFT JOIN `" . DB_PREFIX . "product_code` `pc` ON (`p`.`product_id` = `pc`.`product_id`) WHERE `pd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND (`pd`.`name` LIKE '" . $this->db->escape($filter_name) . "%' OR `p`.`model` LIKE '" . $this->db->escape($filter_name) . "%' OR `pc`.`value` LIKE '" . $this->db->escape($filter_name) . "%') GROUP BY `p`.`product_id` ORDER BY `pd`.`name` ASC LIMIT 5");

		return $query->rows;
	}

	/**
	 * Get Categories
	 *
	 * @param string $filter_name
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getCategories(string $filter_name): array {
		$query = $this->db->query("SELECT `cp`.`category_id`, GROUP_CONCAT(`cd1`.`name` ORDER BY `cp`.`level` SEPARATOR '&nbsp;&gt;&nbsp;') AS `name`, `c`.`image` FROM `" . DB_PREFIX . "category_path` `cp` LEFT JOIN `" . DB_PREFIX . "category` `c` ON (`cp`.`category_id` = `c`.`category_id`) LEFT JOIN `" . DB_PREFIX . "category_description` `cd1` ON (`cp`.`path_id` = `cd1`.`category_id`) LEFT JOIN `" . DB_PREFIX . "category_description` `cd2` ON (`cp`.`category_id` = `cd2`.`category_id`) WHERE `cd1`.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND `cd2`.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND `cd2`.`name` LIKE '%" . $this->db->escape($filter_name) . "%' GROUP BY `cp`.`category_id` ORDER BY `name` ASC LIMIT 5");

		return $query->rows;
	}

	/**
	 * Get Manufacturers
	 *
	 * @param string $filter_name
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getManufacturers(string $filter_name): array {
		$query = $this->db->query("SELECT `manufacturer_id`, `name`, `image` FROM `" . DB_PREFIX . "manufacturer` WHERE `name` LIKE '" . $this->db->escape($filter_name) . "%' ORDER BY `name` ASC LIMIT 5");

		return $query->rows;
	}

	/**
	 * Get Customers
	 *
	 * @param string $filter_name
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getCustomers(string $filter_name): array {
		$query = $this->db->query("SELECT `customer_id`, `email`, CONCAT(`firstname`, ' ', `lastname`) AS `name` FROM `" . DB_PREFIX . "customer` WHERE `firstname` LIKE '" . $this->db->escape($filter_name) . "%' OR `lastname` LIKE '" . $this->db->escape($filter_name) . "%' OR `email` LIKE '" . $this->db->escape($filter_name) . "%' ORDER BY `name` ASC LIMIT 5");

		return $query->rows;
	}

	/**
	 * Get Orders
	 *
	 * @param string $filter_name
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getOrders(string $filter_name): array {
		$query = $this->db->query("SELECT `order_id`, CONCAT(`firstname`, ' ', `lastname`) AS `customer`, `total`, `currency_code`, `currency_value`, `date_added`, `email` FROM `" . DB_PREFIX . "order` WHERE `order_status_id` > '0' AND (`order_id` = '" . (int)$filter_name . "' OR `firstname` LIKE '" . $this->db->escape($filter_name) . "%' OR `lastname` LIKE '" . $this->db->escape($filter_name) . "%' OR `email` LIKE '" . $this->db->escape($filter_name) . "%' OR CONCAT(`invoice_prefix`, `invoice_no`) LIKE '" . $this->db->escape($filter_name) . "%') ORDER BY `order_id` DESC LIMIT 5");

		return $query->rows;
	}
}
