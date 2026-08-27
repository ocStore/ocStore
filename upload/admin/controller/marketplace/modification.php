<?php
namespace Opencart\Admin\Controller\Marketplace;
/**
 * Class Modification
 *
 * @package Opencart\Admin\Controller\Marketplace
 */
class Modification extends \Opencart\System\Engine\Controller {
	/**
	 * Index
	 *
	 * @return void
	 */
	public function index(): void {
		$this->load->language('marketplace/modification');

		$this->document->setTitle($this->language->get('heading_title'));

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('marketplace/modification', 'user_token=' . $this->session->data['user_token'] . $url)
		];

		$data['delete'] = $this->url->link('marketplace/modification.delete', 'user_token=' . $this->session->data['user_token']);
		$data['download'] = $this->url->link('tool/log.download', 'user_token=' . $this->session->data['user_token'] . '&filename=ocmod.log');
		$data['upload'] = $this->url->link('tool/installer.upload', 'user_token=' . $this->session->data['user_token']);

		$data['list'] = $this->getList();

		// Log
		$data['log'] = $this->getLog();

		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('marketplace/modification', $data));
	}

	/**
	 * List
	 *
	 * @return void
	 */
	public function list(): void {
		$this->load->language('marketplace/modification');

		$this->response->setOutput($this->getList());
	}

	/**
	 * Get List
	 *
	 * @return string
	 */
	public function getList(): string {
		if (isset($this->request->get['sort'])) {
			$sort = (string)$this->request->get['sort'];
		} else {
			$sort = 'name';
		}

		if (isset($this->request->get['order'])) {
			$order = (string)$this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['action'] = $this->url->link('marketplace/modification.list', 'user_token=' . $this->session->data['user_token'] . $url);

		// Modification
		$data['modifications'] = [];

		$filter_data = [
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_pagination_admin'),
			'limit' => $this->config->get('config_pagination_admin')
		];

		$this->load->model('setting/modification');

		$results = $this->model_setting_modification->getModifications($filter_data);

		foreach ($results as $result) {
			$data['modifications'][] = [
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'filename'   => $result['code'] . '.ocmod.xml',
				'edit'       => $this->url->link('marketplace/modification.form', 'user_token=' . $this->session->data['user_token'] . '&modification_id=' . $result['modification_id']),
				'download'   => $this->url->link('marketplace/modification.download', 'user_token=' . $this->session->data['user_token'] . '&modification_id=' . $result['modification_id']),
				'enable'     => $this->url->link('marketplace/modification.enable', 'user_token=' . $this->session->data['user_token'] . '&modification_id=' . $result['modification_id']),
				'disable'    => $this->url->link('marketplace/modification.disable', 'user_token=' . $this->session->data['user_token'] . '&modification_id=' . $result['modification_id'])
			] + $result;
		}

		$url = '';

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		$data['sort_name'] = $this->url->link('marketplace/modification', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url, true);
		$data['sort_author'] = $this->url->link('marketplace/modification', 'user_token=' . $this->session->data['user_token'] . '&sort=author' . $url, true);
		$data['sort_version'] = $this->url->link('marketplace/modification', 'user_token=' . $this->session->data['user_token'] . '&sort=version' . $url, true);
		$data['sort_date_added'] = $this->url->link('marketplace/modification', 'user_token=' . $this->session->data['user_token'] . '&sort=date_added' . $url, true);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$modification_total = $this->model_setting_modification->getTotalModifications();

		$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $modification_total,
			'page'  => $page,
			'limit' => $this->config->get('config_pagination_admin'),
			'url'   => $this->url->link('marketplace/modification.list', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}')
		]);

		$data['results'] = sprintf($this->language->get('text_pagination'), ($modification_total) ? (($page - 1) * $this->config->get('config_pagination_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_pagination_admin')) > ($modification_total - $this->config->get('config_pagination_admin'))) ? $modification_total : ((($page - 1) * $this->config->get('config_pagination_admin')) + $this->config->get('config_pagination_admin')), $modification_total, ceil($modification_total / $this->config->get('config_pagination_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;

		return $this->load->view('marketplace/modification_list', $data);
	}

	/**
	 * Refresh
	 *
	 * @throws \Exception
	 *
	 * @return void
	 */
	public function refresh(): void {
		$this->load->language('marketplace/modification');

		$json = [];

		if (!$this->user->hasPermission('modify', 'marketplace/modification')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			// Just before files are deleted, if config settings say maintenance mode is off then turn it on
			$maintenance = $this->config->get('config_maintenance');

			// Setting
			$this->load->model('setting/setting');

			$this->model_setting_setting->editValue('config', 'config_maintenance', '1');

			// Clear all modification files
			$files = [];

			// Make path into an array
			$path = [DIR_EXTENSION . 'ocmod/*'];

			// While the path array is still populated keep looping through
			while (count($path) != 0) {
				$next = array_shift($path);

				foreach (glob($next) as $file) {
					// If directory add to path array
					if (is_dir($file)) {
						$path[] = $file . '/*';
					}

					// Add the file to the files to be deleted array
					$files[] = $file;
				}
			}

			// Reverse sort the file array
			rsort($files);

			// Clear all modification files
			foreach ($files as $file) {
				if ($file != DIR_EXTENSION . 'ocmod/index.html') {
					// If file just delete
					if (is_file($file)) {
						unlink($file);

						// If directory use the remove directory function
					} elseif (is_dir($file)) {
						rmdir($file);
					}
				}
			}

			// Begin
			$xml = [];

			// This is purely so developers they can run mods directly and have them run without upload after each change.
			$files = glob(DIR_SYSTEM . '*.ocmod.xml');

			if ($files) {
				foreach ($files as $file) {
					$xml[] = file_get_contents($file);
				}
			}

			$this->load->model('setting/modification');

			$results = $this->model_setting_modification->getModifications();

			foreach ($results as $result) {
				if ($result['status']) {
					$xml[] = $result['xml'];
				}
			}

			// Log
			$log = [];

			$original = [];
			$modification = [];

			foreach ($xml as $xml) {
				if (empty($xml)) {
					continue;
				}

				$dom = new \DOMDocument('1.0', 'UTF-8');
				$dom->preserveWhiteSpace = false;
				$dom->loadXml($xml);

				// Log
				$log[] = 'MOD: ' . $dom->getElementsByTagName('name')->item(0)->textContent;

				// Store a backup recovery of the modification code in case we need to use it if an abort attribute is used.
				$recovery = $modification;

				$files = $dom->getElementsByTagName('modification')->item(0)->getElementsByTagName('file');

				foreach ($files as $file) {
					$operations = $file->getElementsByTagName('operation');

					$files = explode('|', str_replace("\\", '/', $file->getAttribute('path')));

					foreach ($files as $file) {
						$path = '';

						// Get the full path of the files that are going to be used for modification
						if ((substr($file, 0, 7) == 'catalog')) {
							$path = DIR_CATALOG . substr($file, 8);
						}

						if ((substr($file, 0, 5) == 'admin')) {
							$path = DIR_APPLICATION . substr($file, 6);
						}

						if ((substr($file, 0, 9) == 'extension')) {
							$path = DIR_EXTENSION . substr($file, 10);
						}

						if ((substr($file, 0, 6) == 'system')) {
							$path = DIR_SYSTEM . substr($file, 7);
						}

						if ($path) {
							$files = oc_glob($path);

							if ($files) {
								foreach ($files as $file) {
									if (substr($file, 0, strlen(DIR_APPLICATION)) == DIR_APPLICATION) {
										$key = 'admin/' . substr($file, strlen(DIR_APPLICATION));
									}

									// Get the key to be used for the modification cache filename.
									if (substr($file, 0, strlen(DIR_CATALOG)) == DIR_CATALOG) {
										$key = 'catalog/' . substr($file, strlen(DIR_CATALOG));
									}

									if (substr($file, 0, strlen(DIR_EXTENSION)) == DIR_EXTENSION) {
										$key = 'extension/' . substr($file, strlen(DIR_EXTENSION));
									}

									if (substr($file, 0, strlen(DIR_SYSTEM)) == DIR_SYSTEM) {
										$key = 'system/' . substr($file, strlen(DIR_SYSTEM));
									}

									// If file contents is not already in the modification array we need to load it.
									if (!isset($modification[$key])) {
										$content = file_get_contents($file);

										$modification[$key] = preg_replace('~\r?\n~', "\n", $content);
										$original[$key] = preg_replace('~\r?\n~', "\n", $content);

										// Log
										$log[] = PHP_EOL . 'FILE: ' . $key;
									} else {
										// Log
										$log[] = PHP_EOL . 'FILE: (sub modification) ' . $key;
									}

									foreach ($operations as $operation) {
										$error = $operation->getAttribute('error');

										// Ignoreif
										$ignoreif = $operation->getElementsByTagName('ignoreif')->item(0);

										if ($ignoreif) {
											if ($ignoreif->getAttribute('regex') != 'true') {
												if (strpos($modification[$key], $ignoreif->textContent) !== false) {
													continue;
												}
											} else {
												if (preg_match($ignoreif->textContent, $modification[$key])) {
													continue;
												}
											}
										}

										$status = false;

										// Search and replace
										if ($operation->getElementsByTagName('search')->item(0)->getAttribute('regex') != 'true') {
											// Search
											$search = $operation->getElementsByTagName('search')->item(0)->textContent;
											$trim = $operation->getElementsByTagName('search')->item(0)->getAttribute('trim');
											$index = $operation->getElementsByTagName('search')->item(0)->getAttribute('index');

											// Trim line if no trim attribute is set or is set to true.
											if (!$trim || $trim == 'true') {
												$search = trim($search);
											}

											// Add
											$add = $operation->getElementsByTagName('add')->item(0)->textContent;
											$trim = $operation->getElementsByTagName('add')->item(0)->getAttribute('trim');
											$position = $operation->getElementsByTagName('add')->item(0)->getAttribute('position');
											$offset = (int)$operation->getElementsByTagName('add')->item(0)->getAttribute('offset');

											// Trim line if is set to true.
											if ($trim == 'true') {
												$add = trim($add);
											}

											// Log
											$log[] = 'CODE: ' . $search;

											// Check if using indexes
											if ($index !== '') {
												$indexes = explode(',', $index);
											} else {
												$indexes = '';
											}

											// Get all the matches
											$i = 0;

											$lines = explode("\n", $modification[$key]);

											for ($line_id = 0; $line_id < count($lines); $line_id++) {
												$line = $lines[$line_id];

												// Status
												$match = false;

												// Check to see if the line matches the search code.
												if (stripos($line, $search) !== false) {
													// If indexes are not used then just set the found status to true.
													if (!$indexes) {
														$match = true;
													} elseif (in_array($i, $indexes)) {
														$match = true;
													}

													$i++;
												}

												// Now for replacing or adding to the matched elements
												if ($match) {
													switch ($position) {
														default:
														case 'replace':
															$new_lines = explode("\n", $add);

															if ($offset < 0) {
																array_splice($lines, $line_id + $offset, abs($offset) + 1, [str_replace($search, $add, $line)]);

																$line_id -= $offset;
															} else {
																array_splice($lines, $line_id, $offset + 1, [str_replace($search, $add, $line)]);
															}
															break;
														case 'before':
															$new_lines = explode("\n", $add);

															array_splice($lines, $line_id - $offset, 0, $new_lines);

															$line_id += count($new_lines);
															break;
														case 'after':
															$new_lines = explode("\n", $add);

															array_splice($lines, ($line_id + 1) + $offset, 0, $new_lines);

															$line_id += count($new_lines);
															break;
													}

													// Log
													$log[] = 'LINE: ' . $line_id;

													$status = true;
												}
											}

											$modification[$key] = implode("\n", $lines);
										} else {
											$search = trim($operation->getElementsByTagName('search')->item(0)->textContent);
											$limit = (int)$operation->getElementsByTagName('search')->item(0)->getAttribute('limit');
											$replace = trim($operation->getElementsByTagName('add')->item(0)->textContent);

											// Limit
											if (!$limit) {
												$limit = -1;
											}

											// Log
											$match = [];

											preg_match_all($search, $modification[$key], $match, PREG_OFFSET_CAPTURE);

											// Remove part of the result if a limit is set.
											if ($limit > 0) {
												$match[0] = array_slice($match[0], 0, $limit);
											}

											if ($match[0]) {
												$log[] = 'REGEX: ' . $search;

												for ($i = 0; $i < count($match[0]); $i++) {
													$log[] = 'LINE: ' . (substr_count(substr($modification[$key], 0, $match[0][$i][1]), "\n") + 1);
												}

												$status = true;
											}

											// Make the modification
											$modification[$key] = preg_replace($search, $replace, $modification[$key], $limit);
										}

										if (!$status) {
											// Abort applying this modification completely.
											if ($error == 'abort') {
												$modification = $recovery;
												// Log
												$log[] = 'NOT FOUND - ABORTING!';
												break 4;
											}
											// Skip current operation or break
											elseif ($error == 'skip') {
												// Log
												$log[] = 'NOT FOUND - OPERATION SKIPPED!';
												continue;
											}
											// Break current operations
											else {
												// Log
												$log[] = 'NOT FOUND - OPERATIONS ABORTED!';
												break;
											}
										}
									}
								}
							}
						}
					}
				}

				// Log
				$log[] = '----------------------------------------------------------------';
			}

			// Log
			$ocmod = new \Opencart\System\Library\Log('ocmod.log');
			$ocmod->write(implode("\n", $log));

			// Write all modification files
			foreach ($modification as $key => $value) {
				// Only create a file if there are changes
				if ($original[$key] != $value) {
					$path = '';

					$directories = explode('/', dirname($key));

					foreach ($directories as $directory) {
						$path = $path . '/' . $directory;

						if (!is_dir(DIR_EXTENSION . 'ocmod/' . $path)) {
							@mkdir(DIR_EXTENSION . 'ocmod/' . $path, 0777);
						}
					}

					$handle = fopen(DIR_EXTENSION . 'ocmod/' . $key, 'w');

					fwrite($handle, $value);

					fclose($handle);
				}
			}

			// Maintance mode back to original settings
			$this->model_setting_setting->editValue('config', 'config_maintenance', $maintenance);

			// Do not return success message if refresh() was called with $data
			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * Log
	 *
	 * @return void
	 */
	public function log(): void {
		$this->response->setOutput($this->getLog());
	}

	/**
	 * getLog
	 *
	 * @return string
	 */
	public function getLog(): string {
		$file = DIR_LOGS . 'ocmod.log';

		if (is_file($file)) {
			return htmlentities(file_get_contents($file, true, null));
		} else {
			return '';
		}
	}

	/**
	 * Clear
	 *
	 * @return void
	 */
	public function clear(): void {
		$this->load->language('marketplace/modification');

		$json = [];

		if (!$this->user->hasPermission('modify', 'marketplace/modification')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$files = [];

			// Make path into an array
			$path = [DIR_EXTENSION . 'ocmod/*'];

			// While the path array is still populated keep looping through
			while (count($path) != 0) {
				$next = array_shift($path);

				foreach (glob($next) as $file) {
					// If directory add to path array
					if (is_dir($file)) {
						$path[] = $file . '/*';
					}

					// Add the file to the files to be deleted array
					$files[] = $file;
				}
			}

			// Reverse sort the file array
			rsort($files);

			// Clear all modification files
			foreach ($files as $file) {
				if ($file != DIR_EXTENSION . 'ocmod/index.html') {
					// If file just delete
					if (is_file($file)) {
						unlink($file);

						// If directory use the remove directory function
					} elseif (is_dir($file)) {
						rmdir($file);
					}
				}
			}

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * Enable
	 *
	 * @return void
	 */
	public function enable(): void {
		$this->load->language('marketplace/modification');

		$json = [];

		if (isset($this->request->get['modification_id'])) {
			$modification_id = (int)$this->request->get['modification_id'];
		} else {
			$modification_id = 0;
		}

		if (!$this->user->hasPermission('modify', 'marketplace/modification')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->load->model('setting/modification');

			$this->model_setting_modification->editStatus($modification_id, true);

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * Disable
	 *
	 * @return void
	 */
	public function disable(): void {
		$this->load->language('marketplace/modification');

		$json = [];

		if (isset($this->request->get['modification_id'])) {
			$modification_id = (int)$this->request->get['modification_id'];
		} else {
			$modification_id = 0;
		}

		if (!$this->user->hasPermission('modify', 'marketplace/modification')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->load->model('setting/modification');

			$this->model_setting_modification->editStatus($modification_id, false);

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * Delete
	 *
	 * @return void
	 */
	public function delete(): void {
		$this->load->language('marketplace/modification');

		$json = [];

		if (isset($this->request->post['selected'])) {
			$selected = (array)$this->request->post['selected'];
		} else {
			$selected = [];
		}

		if (!$this->user->hasPermission('modify', 'marketplace/modification')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->load->model('setting/modification');

			foreach ($selected as $modification_id) {
				$this->model_setting_modification->deleteModification($modification_id);
			}

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * Form
	 *
	 * @return void
	 */
	public function form(): void {
		$this->load->language('marketplace/modification');

		$this->document->setTitle($this->language->get('heading_title'));

		if (isset($this->request->get['modification_id'])) {
			$modification_id = (int)$this->request->get['modification_id'];
		} else {
			$modification_id = 0;
		}

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('marketplace/modification', 'user_token=' . $this->session->data['user_token'])
		];

		$data['save'] = $this->url->link('marketplace/modification.save', 'user_token=' . $this->session->data['user_token'] . '&modification_id=' . $modification_id);
		$data['refresh'] = $this->url->link('marketplace/modification.refresh', 'user_token=' . $this->session->data['user_token']);
		$data['history'] = $this->url->link('marketplace/modification.clearHistory', 'user_token=' . $this->session->data['user_token'] . '&modification_id=' . $modification_id);
		$data['back'] = $this->url->link('marketplace/modification', 'user_token=' . $this->session->data['user_token']);

		$this->load->model('setting/modification');

		$modification_info = $this->model_setting_modification->getModification($modification_id);

		if ($modification_info) {
			$data['name'] = $modification_info['name'];
			$data['xml'] = ltrim((string)$modification_info['xml'], "\xEF\xBB\xBF");
		} else {
			$data['name'] = '';
			$data['xml'] = '';
		}

		// Backup
		$data['backups'] = [];

		$results = $this->model_setting_modification->getBackups($modification_id);

		foreach ($results as $result) {
			$data['backups'][] = [
				'backup_id'  => $result['backup_id'],
				'code'       => $result['code'],
				'date_added' => date($this->language->get('datetime_format'), strtotime($result['date_added'])),
				'restore'    => $this->url->link('marketplace/modification.restore', 'user_token=' . $this->session->data['user_token'] . '&modification_id=' . $modification_id . '&backup_id=' . $result['backup_id'])
			];
		}

		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('marketplace/modification_form', $data));
	}

	/**
	 * Save
	 *
	 * @return void
	 */
	public function save(): void {
		$this->load->language('marketplace/modification');

		$json = [];

		if (!$this->user->hasPermission('modify', 'marketplace/modification')) {
			$json['error']['warning'] = $this->language->get('error_permission');
		}

		$required = [
			'name' => '',
			'xml'  => ''
		];

		$post_info = $this->request->post + $required;

		$modification_id = (int)($this->request->get['modification_id'] ?? 0);

		if (!oc_validate_length($post_info['name'], 2, 64)) {
			$json['error']['name'] = $this->language->get('error_name');
		}

		if (!$json) {
			$this->load->model('setting/modification');

			$modification_info = $this->model_setting_modification->getModification($modification_id);

			if ($modification_info) {
				$this->model_setting_modification->addBackup($modification_id, $modification_info);
			}

			$this->model_setting_modification->editModification($modification_id, $post_info);

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * Restore
	 *
	 * @return void
	 */
	public function restore(): void {
		$this->load->language('marketplace/modification');

		$json = [];

		if (!$this->user->hasPermission('modify', 'marketplace/modification')) {
			$json['error'] = $this->language->get('error_permission');
		}

		$modification_id = (int)($this->request->get['modification_id'] ?? 0);

		if (!$json) {
			$this->load->model('setting/modification');

			$backup_info = $this->model_setting_modification->getBackup((int)($this->request->get['backup_id'] ?? 0));

			if (!$backup_info || (int)$backup_info['modification_id'] != $modification_id) {
				$json['error'] = $this->language->get('error_file');
			}
		}

		if (!$json) {
			$this->model_setting_modification->restore($modification_id, $backup_info['xml']);

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * Clear History
	 *
	 * @return void
	 */
	public function clearHistory(): void {
		$this->load->language('marketplace/modification');

		$json = [];

		if (!$this->user->hasPermission('modify', 'marketplace/modification')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->load->model('setting/modification');

			$this->model_setting_modification->deleteBackups((int)($this->request->get['modification_id'] ?? 0));

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * Download
	 *
	 * @return void
	 */
	public function download(): void {
		if (!$this->user->hasPermission('access', 'marketplace/modification')) {
			return;
		}

		$this->load->model('setting/modification');

		$modification_info = $this->model_setting_modification->getModification((int)($this->request->get['modification_id'] ?? 0));

		if ($modification_info) {
			$xml = (string)$modification_info['xml'];
		} else {
			$xml = '';
		}

		$this->response->addHeader('Content-Type: application/xml');
		$this->response->setOutput($xml);
	}

	/**
	 * Upload
	 *
	 * @return void
	 */
	public function upload(): void {
		$this->load->language('marketplace/modification');

		$json = [];

		if (!$this->user->hasPermission('modify', 'marketplace/modification')) {
			$json['error'] = $this->language->get('error_permission');
		}

		$this->load->model('setting/modification');

		$modification_id = (int)($this->request->get['modification_id'] ?? 0);

		$modification_info = $this->model_setting_modification->getModification($modification_id);

		if (!$modification_info) {
			$json['error'] = $this->language->get('error_file');
		}

		if (!$json) {
			if (!empty($this->request->files['file']['name'])) {
				if ($this->request->files['file']['name'] != $modification_info['code'] . '.ocmod.xml') {
					$json['error'] = $this->language->get('error_filetype');
				}

				if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
					$json['error'] = $this->language->get('error_upload');
				}
			} else {
				$json['error'] = $this->language->get('error_upload');
			}
		}

		if (!$json) {
			// If no temp directory exists create it
			$path = 'temp-' . oc_token(32);

			if (!is_dir(DIR_UPLOAD . $path)) {
				mkdir(DIR_UPLOAD . $path, 0777);
			}

			$file = DIR_UPLOAD . $path . '/install.xml';

			// If xml file copy it to the temporary directory
			move_uploaded_file($this->request->files['file']['tmp_name'], $file);

			if (is_file($file)) {
				// Set the steps required for installation
				$json['step'] = [];

				$json['step'][] = [
					'text' => $this->language->get('text_xml'),
					'url'  => $this->url->link('marketplace/modification.xml', 'user_token=' . $this->session->data['user_token'] . '&modification_id=' . $modification_id, true),
					'path' => $path
				];

				// Clear temporary files
				$json['step'][] = [
					'text' => $this->language->get('text_remove'),
					'url'  => $this->url->link('marketplace/modification.remove', 'user_token=' . $this->session->data['user_token'] . '&modification_id=' . $modification_id, true),
					'path' => $path
				];
			} else {
				$json['error'] = $this->language->get('error_file');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * Xml
	 *
	 * @return void
	 */
	public function xml(): void {
		$this->load->language('marketplace/modification');

		$json = [];

		if (!$this->user->hasPermission('modify', 'marketplace/modification')) {
			$json['error'] = $this->language->get('error_permission');
		}

		$this->load->model('setting/modification');

		$modification_id = (int)($this->request->get['modification_id'] ?? 0);

		$modification_info = $this->model_setting_modification->getModification($modification_id);

		if (!$modification_info) {
			$json['error'] = $this->language->get('error_file');
		}

		$file = DIR_UPLOAD . ($this->request->post['path'] ?? '') . '/install.xml';

		if (!$json && (!is_file($file) || substr(str_replace('\\', '/', (string)realpath($file)), 0, strlen(DIR_UPLOAD)) != DIR_UPLOAD)) {
			$json['error'] = $this->language->get('error_file');
		}

		if (!$json) {
			$xml = file_get_contents($file);

			$dom = new \DOMDocument('1.0', 'UTF-8');

			libxml_use_internal_errors(true);

			if ($dom->loadXml($xml)) {
				$code = $dom->getElementsByTagName('code')->item(0);

				if (!$code) {
					$json['error'] = $this->language->get('error_code');
				}

				$name = $dom->getElementsByTagName('name')->item(0);

				if (!$json) {
					$this->model_setting_modification->editModification($modification_id, [
						'name' => $name ? $name->nodeValue : $modification_info['name'],
						'xml'  => htmlentities($xml, ENT_QUOTES, 'UTF-8')
					]);
				}
			} else {
				$error = libxml_get_last_error();

				$json['error'] = $error ? trim($error->message) : $this->language->get('error_file');
			}

			libxml_clear_errors();
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * Remove
	 *
	 * @return void
	 */
	public function remove(): void {
		$this->load->language('marketplace/modification');

		$json = [];

		if (!$this->user->hasPermission('modify', 'marketplace/modification')) {
			$json['error'] = $this->language->get('error_permission');
		}

		$directory = DIR_UPLOAD . ($this->request->post['path'] ?? '');

		if (!$json && (!is_dir($directory) || substr(str_replace('\\', '/', (string)realpath($directory)), 0, strlen(DIR_UPLOAD)) != DIR_UPLOAD)) {
			$json['error'] = $this->language->get('error_directory');
		}

		if (!$json) {
			// Get a list of files ready to be deleted
			$files = [];

			$path = [$directory];

			while (count($path) != 0) {
				$next = array_shift($path);

				// We have to use scandir function because glob will not pick up dot files.
				foreach (array_diff((array)scandir($next), ['.', '..']) as $file) {
					$file = $next . '/' . $file;

					if (is_dir($file)) {
						$path[] = $file;
					}

					$files[] = $file;
				}
			}

			rsort($files);

			foreach ($files as $file) {
				if (is_file($file)) {
					unlink($file);
				} elseif (is_dir($file)) {
					rmdir($file);
				}
			}

			if (is_dir($directory)) {
				rmdir($directory);
			}

			$json['success'] = $this->language->get('text_clear');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
