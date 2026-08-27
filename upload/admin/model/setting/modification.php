<?php
namespace Opencart\Admin\Model\Setting;
/**
 * Class Modification
 *
 * Can be loaded using $this->load->model('setting/modification');
 *
 * @package Opencart\Admin\Model\Setting
 */
class Modification extends \Opencart\System\Engine\Model {
	/**
	 * Add Modification
	 *
	 * Create a new modification record in the database.
	 *
	 * @param array<string, mixed> $data array of data
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $modification_data = [
	 *     'extension_install_id' => 1,
	 *     'name'                 => 'Modification Name',
	 *     'description'          => 'Modification Description',
	 *     'code'                 => 'Modification Code',
	 *     'author'               => 'Author Name',
	 *     'version'              => '1.00',
	 *     'link'                 => '',
	 *     'xml'                  => '',
	 *     'status'               => 0
	 * ];
	 *
	 * $this->load->model('setting/modification');
	 *
	 * $this->model_setting_modification->addModification($$modification_data);
	 */
	public function addModification(array $data): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "modification` SET `extension_install_id` = '" . (int)$data['extension_install_id'] . "', `name` = '" . $this->db->escape($data['name']) . "', `description` = '" . $this->db->escape($data['description']) . "', `code` = '" . $this->db->escape($data['code']) . "', `author` = '" . $this->db->escape($data['author']) . "', `version` = '" . $this->db->escape($data['version']) . "', `link` = '" . $this->db->escape($data['link']) . "', `xml` = '" . $this->db->escape($data['xml']) . "', `status` = '" . (int)$data['status'] . "', `date_added` = NOW()");
	}

	/**
	 * Edit Modification
	 *
	 * Edit modification record in the database.
	 *
	 * @param int                  $modification_id primary key of the modification record
	 * @param array<string, mixed> $data            array of data
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->load->model('setting/modification');
	 *
	 * $this->model_setting_modification->editModification($modification_id, $modification_data);
	 */
	public function editModification(int $modification_id, array $data): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "modification` SET `name` = '" . $this->db->escape(html_entity_decode((string)$data['name'], ENT_QUOTES, 'UTF-8')) . "', `xml` = '" . $this->db->escape(html_entity_decode((string)$data['xml'], ENT_QUOTES, 'UTF-8')) . "' WHERE `modification_id` = '" . (int)$modification_id . "'");
	}

	/**
	 * Add Backup
	 *
	 * Keep the current version of the modification code before it is overwritten.
	 *
	 * @param int                  $modification_id primary key of the modification record
	 * @param array<string, mixed> $data            array of data
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->load->model('setting/modification');
	 *
	 * $this->model_setting_modification->addBackup($modification_id, $modification_info);
	 */
	public function addBackup(int $modification_id, array $data): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "modification_backup` SET `modification_id` = '" . (int)$modification_id . "', `code` = '" . $this->db->escape((string)$data['code']) . "', `xml` = '" . $this->db->escape(html_entity_decode((string)$data['xml'], ENT_QUOTES, 'UTF-8')) . "', `date_added` = NOW()");
	}

	/**
	 * Restore
	 *
	 * Put the code of a backup back into the modification record.
	 *
	 * @param int    $modification_id primary key of the modification record
	 * @param string $xml             modification code
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->load->model('setting/modification');
	 *
	 * $this->model_setting_modification->restore($modification_id, $xml);
	 */
	public function restore(int $modification_id, string $xml): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "modification` SET `xml` = '" . $this->db->escape($xml) . "' WHERE `modification_id` = '" . (int)$modification_id . "'");
	}

	/**
	 * Delete Backups
	 *
	 * @param int $modification_id primary key of the modification record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->load->model('setting/modification');
	 *
	 * $this->model_setting_modification->deleteBackups($modification_id);
	 */
	public function deleteBackups(int $modification_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "modification_backup` WHERE `modification_id` = '" . (int)$modification_id . "'");
	}

	/**
	 * Get Backup
	 *
	 * @param int $backup_id primary key of the backup record
	 *
	 * @return array<string, mixed> backup record that has backup ID
	 *
	 * @example
	 *
	 * $this->load->model('setting/modification');
	 *
	 * $backup_info = $this->model_setting_modification->getBackup($backup_id);
	 */
	public function getBackup(int $backup_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "modification_backup` WHERE `backup_id` = '" . (int)$backup_id . "'");

		return $query->row;
	}

	/**
	 * Get Backups
	 *
	 * @param int $modification_id primary key of the modification record
	 *
	 * @return array<int, array<string, mixed>> backup records that have modification ID
	 *
	 * @example
	 *
	 * $this->load->model('setting/modification');
	 *
	 * $backups = $this->model_setting_modification->getBackups($modification_id);
	 */
	public function getBackups(int $modification_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "modification_backup` WHERE `modification_id` = '" . (int)$modification_id . "' ORDER BY `date_added` DESC");

		return $query->rows;
	}

	/**
	 * Delete Modification
	 *
	 * Delete modification record in the database.
	 *
	 * @param int $modification_id primary key of the modification record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->load->model('setting/modification');
	 *
	 * $this->model_setting_modification->deleteModification($modification_id);
	 */
	public function deleteModification(int $modification_id): void {
		$this->model_setting_modification->deleteBackups($modification_id);

		$this->db->query("DELETE FROM `" . DB_PREFIX . "modification` WHERE `modification_id` = '" . (int)$modification_id . "'");
	}

	/**
	 * Delete Modifications By Extension Install ID
	 *
	 * Delete modifications by extension install records in the database.
	 *
	 * @param int $extension_install_id primary key of the extension install record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->load->model('setting/modification');
	 *
	 * $this->model_setting_modification->deleteModificationsByExtensionInstallId($extension_install_id);
	 */
	public function deleteModificationsByExtensionInstallId(int $extension_install_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "modification` WHERE `extension_install_id` = '" . (int)$extension_install_id . "'");
	}

	/**
	 * Edit Status
	 *
	 * Edit modification status record in the database.
	 *
	 * @param int  $modification_id primary key of the modification record
	 * @param bool $status
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->load->model('setting/modification');
	 *
	 * $this->model_setting_modification->editStatus($modification_id, $status);
	 */
	public function editStatus(int $modification_id, bool $status): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "modification` SET `status` = '" . (bool)$status . "' WHERE `modification_id` = '" . (int)$modification_id . "'");
	}

	/**
	 * Get Modification
	 *
	 * Get the record of the modification record in the database.
	 *
	 * @param int $modification_id primary key of the modification record
	 *
	 * @return array<string, mixed> modification record that has modification ID
	 *
	 * @example
	 *
	 * $this->load->model('setting/modification');
	 *
	 * $modification_info = $this->model_setting_modification->getModification($modification_id);
	 */
	public function getModification(int $modification_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "modification` WHERE `modification_id` = '" . (int)$modification_id . "'");

		return $query->row;
	}

	/**
	 * Get Modifications
	 *
	 * Get the record of the modification records in the database.
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return array<int, array<string, mixed>> modification records
	 *
	 * @example
	 *
	 * $filter_data = [
	 *     'sort'  => 'name',
	 *     'order' => 'DESC',
	 *     'start' => 0,
	 *     'limit' => 10
	 * ];
	 *
	 * $this->load->model('setting/modification');
	 *
	 * $results = $this->model_setting_modification->getModifications($filter_data);
	 */
	public function getModifications(array $data = []): array {
		$sql = "SELECT * FROM `" . DB_PREFIX . "modification`";

		$sort_data = [
			'name',
			'description',
			'author',
			'version',
			'status',
			'date_added'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY `name`";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	/**
	 * Get Total Modifications
	 *
	 * Get the total number of total modification records in the database.
	 *
	 * @return int total number of modification records
	 *
	 * @example
	 *
	 * $this->load->model('setting/modification');
	 *
	 * $modification_total = $this->model_setting_modification->getTotalModifications();
	 */
	public function getTotalModifications(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "modification`");

		return (int)$query->row['total'];
	}

	/**
	 * Get Modification By Code
	 *
	 * @param string $code
	 *
	 * @return array<string, mixed>
	 *
	 * @example
	 *
	 * $this->load->model('setting/modification');
	 *
	 * $modification_info = $this->model_setting_modification->getModificationByCode($code);
	 */
	public function getModificationByCode(string $code): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "modification` WHERE `code` = '" . $this->db->escape($code) . "'");

		return $query->row;
	}
}
