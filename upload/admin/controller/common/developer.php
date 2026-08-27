<?php
namespace Opencart\Admin\Controller\Common;
/**
 * Class Developer
 *
 * Can be loaded using $this->load->controller('common/developer');
 *
 * @package Opencart\Admin\Controller\Common
 */
class Developer extends \Opencart\System\Engine\Controller {
	/**
	 * Index
	 *
	 * @return void
	 */
	public function index(): void {
		$this->load->language('common/developer');

		$data['developer_sass'] = $this->config->get('developer_sass');

		$data['user_token'] = $this->session->data['user_token'];

		$this->response->setOutput($this->load->view('common/developer', $data));
	}

	/**
	 * Edit
	 *
	 * @return void
	 */
	public function edit(): void {
		$this->load->language('common/developer');

		$json = [];

		if (!$this->user->hasPermission('modify', 'common/developer')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			// Setting
			$this->load->model('setting/setting');

			$this->model_setting_setting->editSetting('developer', $this->request->post, 0);

			$json['success'] = $this->language->get('text_developer_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * Cache
	 *
	 * @return void
	 */
	public function cache(): void {
		$this->load->language('common/developer');

		$json = [];

		if (!$this->user->hasPermission('modify', 'common/developer')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->cache->clear();

			$json['success'] = $this->language->get('text_cache_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * Theme
	 *
	 * @return void
	 */
	public function theme(): void {
		$this->load->language('common/developer');

		$json = [];

		if (!$this->user->hasPermission('modify', 'common/developer')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->clearTheme();

			$json['success'] = $this->language->get('text_theme_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * Sass
	 *
	 * @return void
	 */
	public function sass(): void {
		$this->load->language('common/developer');

		$json = [];

		if (!$this->user->hasPermission('modify', 'common/developer')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->clearSass();

			$json['success'] = $this->language->get('text_sass_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * Image
	 *
	 * @return void
	 */
	public function image(): void {
		$this->load->language('common/developer');

		$json = [];

		if (!$this->user->hasPermission('modify', 'common/developer')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->clearImage();

			$json['success'] = $this->language->get('text_image_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * All
	 *
	 * Clear every cache at once, image resizes included.
	 *
	 * @return void
	 */
	public function all(): void {
		$this->load->language('common/developer');

		$json = [];

		if (!$this->user->hasPermission('modify', 'common/developer')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->cache->clear();

			$this->clearTheme();

			$this->clearSass();

			$this->clearImage();

			$json['success'] = $this->language->get('text_all_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * Clear Theme
	 *
	 * @return void
	 */
	private function clearTheme(): void {
		$directories = glob(DIR_CACHE . 'template/*', GLOB_ONLYDIR);

		if ($directories) {
			foreach ($directories as $directory) {
				$files = glob($directory . '/*');

				foreach ($files as $file) {
					if (is_file($file)) {
						unlink($file);
					}
				}

				if (is_dir($directory)) {
					rmdir($directory);
				}
			}
		}
	}

	/**
	 * Clear Sass
	 *
	 * @return void
	 */
	private function clearSass(): void {
		// Before we delete we need to make sure there is a sass file to regenerate the css
		$file = DIR_APPLICATION . 'view/stylesheet/bootstrap.css';

		if (is_file($file) && is_file(DIR_APPLICATION . 'view/stylesheet/scss/bootstrap.scss')) {
			unlink($file);
		}

		$files = glob(DIR_CATALOG . 'view/stylesheet/scss/bootstrap.scss');

		foreach ($files as $file) {
			$file = substr($file, 0, -20) . '/bootstrap.css';

			if (is_file($file)) {
				unlink($file);
			}
		}

		$files = glob(DIR_CATALOG . 'view/stylesheet/stylesheet.scss');

		foreach ($files as $file) {
			$file = substr($file, 0, -16) . '/stylesheet.css';

			if (is_file($file)) {
				unlink($file);
			}
		}
	}

	/**
	 * Clear Image
	 *
	 * @return void
	 */
	private function clearImage(): void {
		$files = glob(DIR_IMAGE . 'cache/*');

		if ($files) {
			foreach ($files as $file) {
				if ($file == DIR_IMAGE . 'cache/index.html') {
					continue;
				}

				if (is_file($file)) {
					unlink($file);
				} elseif (is_dir($file)) {
					$this->deleteDirectory($file);
				}
			}
		}
	}

	/**
	 * Delete Directory
	 *
	 * @param string $directory
	 *
	 * @return void
	 */
	private function deleteDirectory(string $directory): void {
		$files = glob($directory . '/*');

		if ($files) {
			foreach ($files as $file) {
				if (is_file($file)) {
					unlink($file);
				} elseif (is_dir($file)) {
					$this->deleteDirectory($file);
				}
			}
		}

		if (is_dir($directory)) {
			rmdir($directory);
		}
	}
}
