<?php
/**
 * @package   Domovyi
 * @author    Dinox
 * @copyright Copyright (c) 2009 - 2026, Dinox (https://opencartforum.com/)
 * @license   https://opensource.org/licenses/GPL-3.0
 * @link      https://opencartforum.com/files/file/8732-domoviy-vidzhet-dlya-monitoringu-stanu-magazinu
 */
namespace Opencart\Admin\Controller\Extension\Opencart\Dashboard;
/**
 * Class Domovyi
 *
 * @package Opencart\Admin\Controller\Extension\Opencart\Dashboard
 */
class Domovyi extends \Opencart\System\Engine\Controller {
	/**
	 * Index
	 *
	 * @return void
	 */
	public function index(): void {
		$this->load->language('extension/opencart/dashboard/domovyi');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=dashboard')
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/opencart/dashboard/domovyi', 'user_token=' . $this->session->data['user_token'])
		];

		$data['save'] = $this->url->link('extension/opencart/dashboard/domovyi.save', 'user_token=' . $this->session->data['user_token']);
		$data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=dashboard');
		$data['dashboard'] = $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token']);

		$cron = $this->getCron();

		$data['folders'] = [];

		foreach (array_keys($this->getFolders()) as $key) {
			$data['folders'][] = [
				'key'    => $key,
				'name'   => $this->language->get('text_dir_' . $key),
				'status' => $cron[$key]['status'] ?? 0,
				'size'   => $cron[$key]['size'] ?? 100,
				'time'   => $cron[$key]['time'] ?? 30
			];
		}

		$data['dashboard_domovyi_danger_funtions'] = $this->getFunctions('danger');
		$data['dashboard_domovyi_warning_funtions'] = $this->getFunctions('warning');

		$data['dashboard_domovyi_disk_free_space'] = $this->config->get('dashboard_domovyi_disk_free_space') ?: 500;
		$data['dashboard_domovyi_free_space_status'] = $this->config->get('dashboard_domovyi_free_space_status');

		$data['columns'] = [];

		for ($i = 3; $i <= 12; $i++) {
			$data['columns'][] = $i;
		}

		$data['dashboard_domovyi_width'] = $this->config->get('dashboard_domovyi_width');
		$data['dashboard_domovyi_status'] = $this->config->get('dashboard_domovyi_status');
		$data['dashboard_domovyi_sort_order'] = $this->config->get('dashboard_domovyi_sort_order');

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/opencart/dashboard/domovyi_form', $data));
	}

	/**
	 * Save
	 *
	 * @return void
	 */
	public function save(): void {
		$this->load->language('extension/opencart/dashboard/domovyi');

		$json = [];

		if (!$this->user->hasPermission('modify', 'extension/opencart/dashboard/domovyi')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			// Setting
			$this->load->model('setting/setting');

			$this->model_setting_setting->editSetting('dashboard_domovyi', $this->request->post);

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * Dashboard
	 *
	 * @return string
	 */
	public function dashboard(): string {
		$this->load->language('common/developer');
		$this->load->language('extension/opencart/dashboard/domovyi');

		$data['user_token'] = $this->session->data['user_token'];

		$data['developer_sass'] = $this->config->get('developer_sass');

		$data['setting'] = $this->url->link('extension/opencart/dashboard/domovyi', 'user_token=' . $this->session->data['user_token']);

		$cron = $this->getCron();

		$data['folders'] = [];

		foreach (array_keys($this->getFolders()) as $key) {
			$folder = [
				'key'  => $key,
				'name' => $this->language->get('text_dir_' . $key)
			];

			$limit = (float)($cron[$key]['size'] ?? 0) * pow(1024, 2);

			$cache = $this->getCache($key);

			// Recalculate the folder once the period set by the user has passed
			if (!empty($cron[$key]['status']) && $this->dateDiff(date('Y-m-d H:i:s'), (string)($cache['date'] ?? '')) > (float)($cron[$key]['time'] ?? 0) * 60) {
				$cache = $this->calcFolder($key);
			}

			if ($cache) {
				$folder['size'] = $cache['unit']['size'] . ' ' . $cache['unit']['unit'];
				$folder['files'] = sprintf($this->language->get('text_folder_files'), $cache['files']);
				$folder['date'] = date($this->language->get('datetime_format'), (int)strtotime($cache['date']));
				$folder['limit'] = $limit ? $this->formatSize($limit) : [];
				$folder['percent'] = $limit ? min(100, round($cache['size'] / $limit * 100)) : 0;

				if ($limit && $cache['size'] > $limit) {
					$folder['warning'] = sprintf($this->language->get('text_warning_size'), $cron[$key]['size']);
				} else {
					$folder['warning'] = '';
				}
			} else {
				$folder['size'] = '';
				$folder['files'] = $this->language->get('text_never');
				$folder['date'] = '';
				$folder['limit'] = [];
				$folder['percent'] = 0;
				$folder['warning'] = '';
			}

			$data['folders'][] = $folder;
		}

		$data['phpversion'] = phpversion();

		$query = $this->db->query("SELECT VERSION() AS `version`");

		$data['database_version'] = $query->row['version'] ?? '';

		if (function_exists('ioncube_loader_version')) {
			$data['ioncube_version'] = ioncube_loader_version();
		} else {
			$data['ioncube_version'] = '';
		}

		$data['disk_free_space'] = [];
		$data['disk_free_space_warning'] = '';

		if (function_exists('disk_free_space') && $this->config->get('dashboard_domovyi_free_space_status')) {
			$disk_space = disk_free_space('/');

			if ($disk_space !== false) {
				$data['disk_free_space'] = $this->formatSize($disk_space);

				$limit = (float)$this->config->get('dashboard_domovyi_disk_free_space');

				if ($limit && $disk_space < $limit * pow(1024, 2)) {
					$data['disk_free_space_warning'] = sprintf($this->language->get('text_warning_free_space'), $limit);
				}
			}
		}

		$data['danger_funtions'] = $this->checkFunc((array)preg_split('/\R/', $this->getFunctions('danger')));
		$data['warning_funtions'] = $this->checkFunc((array)preg_split('/\R/', $this->getFunctions('warning')));

		$data['extensions'] = $this->getExtensions();
		$data['limits'] = $this->getLimits();
		$data['permissions'] = $this->getPermissions();
		$data['database'] = $this->getDatabase();
		$data['errors'] = $this->getErrors();

		$data['log'] = $this->url->link('tool/log', 'user_token=' . $this->session->data['user_token'] . '&filename=error.log');

		return $this->load->view('extension/opencart/dashboard/domovyi_info', $data);
	}

	/**
	 * Clear
	 *
	 * @return void
	 */
	public function clear(): void {
		$this->load->language('extension/opencart/dashboard/domovyi');

		$json = [];

		if (!$this->user->hasPermission('modify', 'extension/opencart/dashboard/domovyi')) {
			$json['error'] = $this->language->get('error_permission');
		}

		$key = (string)($this->request->get['dir'] ?? '');

		if (!$json && !isset($this->getFolders()[$key])) {
			$json['error'] = $this->language->get('error_folder');
		}

		if (!$json) {
			$files = glob($this->getFolders()[$key] . ($key == 'cache' ? '/cache.*' : '/*'));

			if ($files) {
				foreach ($files as $file) {
					if ($file == $this->getFolders()[$key] . '/index.html') {
						continue;
					}

					$this->deleteDirectory($file);
				}
			}

			$json['success'] = $this->language->get('text_cache');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * Calc
	 *
	 * @return void
	 */
	public function calc(): void {
		$this->load->language('extension/opencart/dashboard/domovyi');

		$json = [];

		if (!$this->user->hasPermission('modify', 'extension/opencart/dashboard/domovyi')) {
			$json['error'] = $this->language->get('error_permission');
		}

		$key = (string)($this->request->get['dir'] ?? '');

		if (!$json && !isset($this->getFolders()[$key])) {
			$json['error'] = $this->language->get('error_folder');
		}

		if (!$json) {
			$folder = $this->calcFolder($key);

			$cron = $this->getCron();

			$limit = (float)($cron[$key]['size'] ?? 0) * pow(1024, 2);

			$json['size'] = $folder['unit']['size'] . ' ' . $folder['unit']['unit'];
			$json['percent'] = $limit ? min(100, round($folder['size'] / $limit * 100)) : 0;
			$json['warning'] = ($limit && $folder['size'] > $limit) ? sprintf($this->language->get('text_warning_size'), $cron[$key]['size']) : '';

			$text = sprintf($this->language->get('text_folder_files'), $folder['files']) . ' · ' . date($this->language->get('datetime_format'), (int)strtotime($folder['date']));

			if ($limit) {
				$unit = $this->formatSize($limit);

				$text .= ' · ' . sprintf($this->language->get('text_limit'), $unit['size'] . ' ' . $unit['unit']);
			}

			$json['text'] = $text;
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * Phpinfo
	 *
	 * @return void
	 */
	public function phpinfo(): void {
		$this->load->language('extension/opencart/dashboard/domovyi');

		if (!$this->user->hasPermission('access', 'extension/opencart/dashboard/domovyi')) {
			$this->response->setOutput($this->language->get('error_permission'));

			return;
		}

		ob_start();

		phpinfo();

		$data['phpinfo'] = (string)ob_get_clean();

		$data['phpinfo'] = preg_replace('@<style[^>]*?>.*?</style>@si', '', $data['phpinfo']);

		$this->response->setOutput($this->load->view('extension/opencart/dashboard/phpinfo', $data));
	}

	/**
	 * Get Extensions
	 *
	 * The PHP extensions the store cannot run without.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function getExtensions(): array {
		$required = [
			'db'       => (bool)array_filter(['mysqli', 'pdo', 'pgsql'], 'extension_loaded'),
			'gd'       => extension_loaded('gd'),
			'curl'     => extension_loaded('curl'),
			'openssl'  => function_exists('openssl_encrypt'),
			'zlib'     => extension_loaded('zlib'),
			'zip'      => extension_loaded('zip'),
			'mbstring' => extension_loaded('mbstring'),
			'iconv'    => function_exists('iconv')
		];

		$extensions = [];

		foreach ($required as $name => $status) {
			$extensions[] = [
				'name'   => $name == 'db' ? $this->language->get('text_db_driver') : $name,
				'status' => $status
			];
		}

		return $extensions;
	}

	/**
	 * Get Limits
	 *
	 * The php.ini values worth keeping an eye on, compared with what a store needs.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function getLimits(): array {
		$required = [
			'memory_limit'        => [256 * 1024 * 1024, true],
			'upload_max_filesize' => [8 * 1024 * 1024, true],
			'post_max_size'       => [8 * 1024 * 1024, true],
			'max_execution_time'  => [30, false],
			'max_input_vars'      => [5000, false]
		];

		$limits = [];

		foreach ($required as $name => $required_data) {
			list($recommended, $is_size) = $required_data;

			$value = (string)ini_get($name);

			if ($is_size) {
				$current = $this->toBytes($value);
				$unit = $this->formatSize((float)$recommended);
				$expected = $unit['size'] . ' ' . $unit['unit'];
			} else {
				$current = (float)$value;
				$expected = (string)$recommended;
			}

			$limits[] = [
				'name'        => $name,
				'value'       => $value !== '' ? $value : '—',
				'recommended' => $expected,
				'status'      => $current <= 0 || $current >= $recommended
			];
		}

		return $limits;
	}

	/**
	 * Get Permissions
	 *
	 * The paths the store has to be able to write into.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function getPermissions(): array {
		$paths = [
			'system/storage/cache'     => DIR_CACHE,
			'system/storage/logs'      => DIR_LOGS,
			'system/storage/session'   => DIR_SESSION,
			'system/storage/upload'    => DIR_UPLOAD,
			'system/storage/download'  => DIR_DOWNLOAD,
			'system/storage/backup'    => DIR_STORAGE . 'backup/',
			'image/cache'              => DIR_IMAGE . 'cache/',
			'extension/ocmod'          => DIR_EXTENSION . 'ocmod/',
			'config.php'               => DIR_OPENCART . 'config.php',
			'admin/config.php'         => DIR_APPLICATION . 'config.php'
		];

		$permissions = [];

		foreach ($paths as $name => $path) {
			$permissions[] = [
				'name'   => $name,
				'status' => file_exists($path) && is_writable($path)
			];
		}

		return $permissions;
	}

	/**
	 * Get Database
	 *
	 * The total size of the database and the tables that weigh the most.
	 *
	 * @return array<string, mixed>
	 */
	private function getDatabase(): array {
		$query = $this->db->query("SELECT `TABLE_NAME` AS `name`, (`DATA_LENGTH` + `INDEX_LENGTH`) AS `size`, `TABLE_ROWS` AS `rows` FROM `information_schema`.`TABLES` WHERE `TABLE_SCHEMA` = DATABASE() ORDER BY `size` DESC");

		$total = 0;
		$tables = [];

		foreach ($query->rows as $row) {
			$total += (float)$row['size'];

			if (count($tables) < 5) {
				$unit = $this->formatSize((float)$row['size']);

				$tables[] = [
					'name' => $row['name'],
					'rows' => (int)$row['rows'],
					'size' => $unit['size'] . ' ' . $unit['unit']
				];
			}
		}

		$unit = $this->formatSize($total);

		return [
			'total'  => $unit['size'] . ' ' . $unit['unit'],
			'tables' => $tables
		];
	}

	/**
	 * Get Errors
	 *
	 * The tail of the error log, so a broken store is visible without leaving the dashboard.
	 *
	 * @return array<string, mixed>
	 */
	private function getErrors(): array {
		$file = DIR_LOGS . 'error.log';

		if (!is_file($file) || !filesize($file)) {
			return ['total' => 0, 'lines' => []];
		}

		$handle = fopen($file, 'r');

		if (!$handle) {
			return ['total' => 0, 'lines' => []];
		}

		// Only the tail is worth reading, the log can grow to megabytes
		$size = (int)filesize($file);
		$offset = max(0, $size - 65536);

		fseek($handle, $offset);

		$content = (string)fread($handle, $size - $offset);

		fclose($handle);

		$lines = array_values(array_filter(array_map('trim', explode("\n", $content))));

		if ($offset) {
			array_shift($lines);
		}

		return [
			'total'    => count($lines),
			'trimmed'  => (bool)$offset,
			'lines'    => array_slice($lines, -5)
		];
	}

	/**
	 * To Bytes
	 *
	 * @param string $value php.ini shorthand such as 256M
	 *
	 * @return float
	 */
	private function toBytes(string $value): float {
		$value = trim($value);

		if ($value === '') {
			return 0;
		}

		$number = (float)$value;

		switch (strtolower(substr($value, -1))) {
			case 'g':
				return $number * 1024 * 1024 * 1024;
			case 'm':
				return $number * 1024 * 1024;
			case 'k':
				return $number * 1024;
			default:
				return $number;
		}
	}

	/**
	 * Get Cron
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private function getCron(): array {
		$cron = $this->config->get('dashboard_domovyi_cron');

		return is_array($cron) ? $cron : [];
	}

	/**
	 * Get Cache
	 *
	 * The last measurement of the folder, empty while it has never been measured.
	 *
	 * @param string $key
	 *
	 * @return array<string, mixed>
	 */
	private function getCache(string $key): array {
		$cache = $this->config->get('domovyi_folders_' . $key);

		if (!is_array($cache) || !isset($cache['size'], $cache['files'], $cache['date'], $cache['unit']['size'], $cache['unit']['unit'])) {
			return [];
		}

		return $cache;
	}

	/**
	 * Get Functions
	 *
	 * The list of functions to look for, falling back to the defaults while the store owner has not set their own.
	 *
	 * @param string $type
	 *
	 * @return string
	 */
	private function getFunctions(string $type): string {
		$default = [
			'danger'  => "exec\r\npassthru\r\nini_get\r\nini_get_all\r\nparse_ini_file\r\nphp_uname\r\nsystem\r\nshell_exec\r\nshow_source\r\npcntl_exec\r\nexpect_popen\r\nproc_open\r\npopen",
			'warning' => "diskfreespace\r\ndisk_total_space\r\nfileperms\r\nfopen\r\nphpversion\r\nopendir\r\nposix_getpwuid\r\nposix_uname"
		];

		return (string)($this->config->get('dashboard_domovyi_' . $type . '_funtions') ?: $default[$type]);
	}

	/**
	 * Get Folders
	 *
	 * @return array<string, string>
	 */
	private function getFolders(): array {
		return [
			'logs'        => rtrim(DIR_LOGS, '/'),
			'cache'       => rtrim(DIR_CACHE, '/'),
			'imagescache' => DIR_IMAGE . 'cache'
		];
	}

	/**
	 * Calc Folder
	 *
	 * Measure a folder and remember the result in the settings.
	 *
	 * @param string $key
	 *
	 * @return array<string, mixed>
	 */
	private function calcFolder(string $key): array {
		$directory = $this->getFolders()[$key];

		$folder = [];

		$folder['size'] = $this->getFilesSize($directory);
		$folder['unit'] = $this->formatSize($folder['size']);
		$folder['files'] = count((array)scandir($directory)) - 2;
		$folder['date'] = date('Y-m-d H:i:s');

		// Setting
		$this->load->model('setting/setting');

		$setting_data = $this->model_setting_setting->getSetting('domovyi');

		$setting_data['domovyi_folders_' . $key] = $folder;

		$this->model_setting_setting->editSetting('domovyi', $setting_data);

		return $folder;
	}

	/**
	 * Get Files Size
	 *
	 * @param string $path
	 *
	 * @return int
	 */
	private function getFilesSize(string $path): int {
		$size = 0;

		foreach (array_diff((array)scandir($path), ['.', '..']) as $file) {
			$file = $path . '/' . $file;

			if (is_dir($file)) {
				$size += $this->getFilesSize($file);
			} elseif (is_file($file)) {
				$size += (int)filesize($file);
			}
		}

		return $size;
	}

	/**
	 * Format Size
	 *
	 * @param float $size
	 *
	 * @return array<string, mixed>
	 */
	private function formatSize(float $size): array {
		$metrics = [
			$this->language->get('text_metrics_bit'),
			$this->language->get('text_metrics_kbit'),
			$this->language->get('text_metrics_mbit'),
			$this->language->get('text_metrics_gbit'),
			$this->language->get('text_metrics_tbit')
		];

		$metric = 0;

		while (floor($size / 1024) > 0 && isset($metrics[$metric + 1])) {
			$metric++;

			$size /= 1024;
		}

		return [
			'size' => round($size, 1),
			'unit' => $metrics[$metric]
		];
	}

	/**
	 * Check Func
	 *
	 * @param array<int, string> $functions
	 *
	 * @return string
	 */
	private function checkFunc(array $functions): string {
		$result = [];

		foreach ($functions as $function) {
			$function = trim($function);

			if ($function && function_exists($function)) {
				$result[] = $function;
			}
		}

		return implode(', ', array_unique($result));
	}

	/**
	 * Delete Directory
	 *
	 * @param string $directory
	 *
	 * @return void
	 */
	private function deleteDirectory(string $directory): void {
		if (is_dir($directory)) {
			$files = glob($directory . '/*');

			if ($files) {
				foreach ($files as $file) {
					$this->deleteDirectory($file);
				}
			}

			rmdir($directory);
		} elseif (is_file($directory)) {
			unlink($directory);
		}
	}

	/**
	 * Date Diff
	 *
	 * @param string $date_1
	 * @param string $date_2
	 *
	 * @return int
	 */
	private function dateDiff(string $date_1, string $date_2): int {
		return abs((int)strtotime($date_2) - (int)strtotime($date_1));
	}
}
