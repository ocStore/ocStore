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

		$cron = (array)$this->config->get('dashboard_domovyi_cron');

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

		$cron = (array)$this->config->get('dashboard_domovyi_cron');

		$data['folders'] = [];

		foreach (array_keys($this->getFolders()) as $key) {
			$folder = [
				'key'  => $key,
				'name' => $this->language->get('text_dir_' . $key)
			];

			$limit = (float)($cron[$key]['size'] ?? 0) * pow(1024, 2);

			$cache = (array)$this->config->get('domovyi_folders_' . $key);

			// Recalculate the folder once the period set by the user has passed
			if (!empty($cron[$key]['status']) && $this->dateDiff(date('Y-m-d H:i:s'), (string)($cache['date'] ?? '')) > (float)($cron[$key]['time'] ?? 0) * 60) {
				$cache = $this->calcFolder($key);
			}

			if ($cache) {
				$folder['size'] = sprintf($this->language->get('text_folder_size'), $cache['unit']['size'] . ' ' . $cache['unit']['unit']);
				$folder['files'] = sprintf($this->language->get('text_folder_files'), $cache['files']) . ' | ' . $cache['date'];

				if ($limit && $cache['size'] > $limit) {
					$folder['warning'] = sprintf($this->language->get('text_warning_size'), $cron[$key]['size']);
				} else {
					$folder['warning'] = '';
				}
			} else {
				$folder['size'] = $this->language->get('text_check');
				$folder['files'] = '';
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

			$cron = (array)$this->config->get('dashboard_domovyi_cron');

			$limit = (float)($cron[$key]['size'] ?? 0) * pow(1024, 2);

			if ($limit && $folder['size'] > $limit) {
				$warning = sprintf($this->language->get('text_warning_size'), $cron[$key]['size']);
			} else {
				$warning = $this->language->get('text_normal');
			}

			$json['success'] = sprintf($this->language->get('text_folder_size'), $folder['unit']['size'] . ' ' . $folder['unit']['unit']) . ' ' . sprintf($this->language->get('text_folder_files'), $folder['files']) . ' | ' . $folder['date'] . ' ' . $warning;
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
