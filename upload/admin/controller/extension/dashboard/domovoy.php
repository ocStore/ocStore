<?php
/**
 * @package        Domovoy
 * @author        Dinox
 * @copyright    Copyright (c) 2009 - 2021, Dinox. (https://opencartforum.com/)
 * @license        GPL3
 * @link        https://opencartforum.com/
 */

class ControllerExtensionDashboardDomovoy extends Controller
{
    private $error = array();

    public function index()
    {
        $this->load->language('extension/dashboard/domovoy');

        $this->document->setTitle($this->language->get('heading_h1'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

            $this->model_setting_setting->editSetting('dashboard_domovoy', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=dashboard', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/dashboard/domovoy', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/dashboard/domovoy', 'user_token=' . $this->session->data['user_token'], true);

        $data['cancel'] = $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'] . '&type=dashboard', true);

        $folders = array('logs' => array('dir' => DIR_STORAGE . 'logs'), 'cache' => array('dir' => DIR_CACHE), 'imagescache' => array('dir' => DIR_IMAGE . 'cache'));

        foreach ($folders as $key => $folder) {

            $data['folders'][$key]['name'] = $this->language->get('text_dir_' . $key);
            $data['folders'][$key]['key'] = $key;

            if (isset($this->request->post["dashboard_domovoy_cron"])) {
                $cron = $this->request->post["dashboard_domovoy_cron"];
            } elseif ($this->config->get("dashboard_domovoy_cron")) {
                $cron = $this->config->get("dashboard_domovoy_cron");
            } else {
                $data['folders'][$key]['cron']['time'] = 30;
                $data['folders'][$key]['cron']['size'] = 100;
            }

            if (isset($cron)) {
                $data['folders'][$key]['cron']['status'] = $cron[$key]['status'];
                $data['folders'][$key]['cron']['size'] = $cron[$key]['size'];
                $data['folders'][$key]['cron']['time'] = $cron[$key]['time'];
            }
        }

        if (isset($this->request->post['dashboard_domovoy_danger_funtions'])) {
            $data['dashboard_domovoy_danger_funtions'] = $this->request->post['dashboard_domovoy_danger_funtions'];
        } elseif ($this->config->get('dashboard_domovoy_danger_funtions')) {
            $data['dashboard_domovoy_danger_funtions'] = $this->config->get('dashboard_domovoy_danger_funtions');
        } else {
            $data['dashboard_domovoy_danger_funtions'] = "exec\r\npassthru\r\nini_get\r\nini_get_all\r\nparse_ini_file\r\nphp_uname\r\nsystem\r\nshell_exec\r\nshow_source\r\npcntl_exec\r\npcntl_exec\r\nexpect_popen\r\nproc_open\r\npopen";
        }

        if (isset($this->request->post['dashboard_domovoy_warning_funtions'])) {
            $data['dashboard_domovoy_warning_funtions'] = $this->request->post['dashboard_domovoy_warning_funtions'];
        } elseif ($this->config->get('dashboard_domovoy_warning_funtions')) {
            $data['dashboard_domovoy_warning_funtions'] = $this->config->get('dashboard_domovoy_warning_funtions');
        } else {
            $data['dashboard_domovoy_warning_funtions'] = "diskfreespace\r\ndisk_total_space\r\ndisk_total_space\r\nfileperms\r\nfopen\r\nphpversion\r\nopendir\r\nposix_getpwuid\r\nposix_uname";
        }

        if (isset($this->request->post['dashboard_domovoy_width'])) {
            $data['dashboard_domovoy_width'] = $this->request->post['dashboard_domovoy_width'];
        } else {
            $data['dashboard_domovoy_width'] = $this->config->get('dashboard_domovoy_width');
        }

        if (isset($this->request->post['dashboard_domovoy_disk_free_space'])) {
            $data['dashboard_domovoy_disk_free_space'] = $this->request->post['dashboard_domovoy_disk_free_space'];
        } elseif($this->config->get('dashboard_domovoy_disk_free_space')) {
            $data['dashboard_domovoy_disk_free_space'] = $this->config->get('dashboard_domovoy_disk_free_space');
        } else {
            $data['dashboard_domovoy_disk_free_space'] = 500;
        }

        if (isset($this->request->post['dashboard_domovoy_free_space_status'])) {
            $data['dashboard_domovoy_free_space_status'] = $this->request->post['dashboard_domovoy_free_space_status'];
        } else {
            $data['dashboard_domovoy_free_space_status'] = $this->config->get('dashboard_domovoy_free_space_status');
        }


        $data['columns'] = array();

        for ($i = 3; $i <= 12; $i++) {
            $data['columns'][] = $i;
        }

        if (isset($this->request->post['dashboard_domovoy_status'])) {
            $data['dashboard_domovoy_status'] = $this->request->post['dashboard_domovoy_status'];
        } else {
            $data['dashboard_domovoy_status'] = $this->config->get('dashboard_domovoy_status');
        }


        if (isset($this->request->post['dashboard_domovoy_sort_order'])) {
            $data['dashboard_domovoy_sort_order'] = $this->request->post['dashboard_domovoy_sort_order'];
        } else {
            $data['dashboard_domovoy_sort_order'] = $this->config->get('dashboard_domovoy_sort_order');
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/dashboard/domovoy_form', $data));
    }

    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/dashboard/domovoy')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    public function dashboard()
    {
        $this->load->language('common/developer');
        $this->load->language('extension/dashboard/domovoy');

        $this->document->addStyle('view/stylesheet/fork-awesome.css');

        $data['user_token'] = $this->session->data['user_token'];

        $data['developer_theme'] = $this->config->get('developer_theme');
        $data['developer_sass'] = $this->config->get('developer_sass');

        $eval = false;

        $eval = '$eval = true;';

        eval($eval);

        if ($eval === true) {
            $data['eval'] = true;
        } else {
            $this->load->model('setting/setting');

            $this->model_setting_setting->editSetting('developer', array('developer_theme' => 1), 0);

            $data['eval'] = false;
        }

        $data['activities'] = array();

        $folders = array('logs' => array('dir' => DIR_STORAGE . 'logs'), 'cache' => array('dir' => DIR_CACHE), 'imagescache' => array('dir' => DIR_IMAGE . 'cache'));

        $data['setting'] = $this->url->link('extension/dashboard/domovoy', 'user_token=' . $this->session->data['user_token'], true);

        $data['folders'] = array();

        foreach ($folders as $key => $folder) {
            $data['folders'][$key]['name'] = $this->language->get('text_dir_' . $key);
            $data['folders'][$key]['key'] = $key;

            if ($this->config->get("dashboard_domovoy_cron")) {
                $cron = $this->config->get("dashboard_domovoy_cron");
                $folder_size = $cron[$key]['size'] * pow(1024, 2);
            } else {
                $folder_size = 0;
            }

            // Calc Cron

            $cron_time = $cron[$key]['time'] * 60;

            $cache = $this->config->get('domovoy_folders_' . $key);

            $time = $this->date_diff(date('Y-m-d H:i'), (isset($cache['date']) ? $cache['date'] : ''));
            if ($cron[$key]['status'] && $time > $cron_time) {
                $this->calc($key);
            }

            if ($cache) {
                $data['folders'][$key]['size'] = sprintf($this->language->get('text_folder_size'), $cache['unit']['size'] . " " . $cache['unit']['unit']);
                if ($cache['size'] > $folder_size && $folder_size != 0) {
                    $data['folders'][$key]['warning_size'] = sprintf($this->language->get('text_warning_size'), $cron[$key]['size']);
                } else {
                    $data['folders'][$key]['warning_size'] = false;
                }
                $data['folders'][$key]['files'] = sprintf($this->language->get('text_folder_files'), $cache['files']) . " | " . $cache['date'];
            } else {
                $data['folders'][$key]['size'] = $this->language->get('text_check');
            }
        }

        $data['phpversion'] = phpversion();

        $reader = function & ($object, $property) {
            $value = &Closure::bind(function & () use ($property) {
                return $this->$property;
            }, $object, $object)->__invoke();
            return $value;
        };

        $result = &$reader($this->db, 'adaptor');
        $result = &$reader($result, 'connection');

        $data['database_version'] = $result->server_info;


        if (function_exists('ioncube_loader_version')) {
            $data['ioncube_version'] = ioncube_loader_version();
        }

        if (function_exists('disk_free_space') && $this->config->get('dashboard_domovoy_free_space_status')) {
            $disk_space = disk_free_space("/");
            $data['disk_free_space'] = $this->format_size($disk_space);
                $space_limit = $this->config->get("dashboard_domovoy_disk_free_space");
                $folder_size = $space_limit * pow(1024, 2);
            if ($disk_space < $folder_size) {
                $data['disk_free_space_warning'] = sprintf($this->language->get('text_warning_free_space'), $space_limit);
            }
        }

        $danger_funtions = explode("\r\n", $this->config->get('dashboard_domovoy_danger_funtions'));
        if (!empty($danger_funtions)) {
            $data['danger_funtions'] = $this->checkFunc($danger_funtions);
        } else {
            $data['danger_funtions'] = array();
        }

        $warning_funtions = explode("\r\n", $this->config->get('dashboard_domovoy_warning_funtions'));
        if (!empty($warning_funtions)) {
            $data['warning_funtions'] = $this->checkFunc($warning_funtions);
        } else {
            $data['warning_funtions'] = array();
        }

        return $this->load->view('extension/dashboard/domovoy_info', $data);
    }

    public function clear($dir = false)
    {
        $this->load->language('extension/dashboard/domovoy');

        $json = array();

        if (!$this->user->hasPermission('modify', 'extension/dashboard/domovoy')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            if (isset($this->request->get['dir']) or $dir) {
                if ($dir) {
                    $key = $dir;
                } else {
                    $key = $this->request->get['dir'];
                }

                $folders = array('logs' => array('dir' => DIR_STORAGE . 'logs/*'), 'cache' => array('dir' => DIR_CACHE . '*.*'), 'imagescache' => array('dir' => DIR_IMAGE . 'cache/*'));

                $files = glob($folders[$key]['dir']);
                if (!empty($files)) {
                    foreach ($files as $file) {
                        $this->deldir($file);
                    }
                }
                $json['success'] = sprintf($this->language->get('text_cache'), $this->language->get('text_clearfolder'));
            } else {
                $json['error'] = $this->language->get('error_folder');
            }
        }

        if (!$dir) {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        }
    }

    public function calc($dir = false)
    {
        $this->load->language('extension/dashboard/domovoy');
        $this->load->model('setting/setting');

        if (isset($this->request->get['dir']) or $dir) {

            $folders = array('logs' => array('dir' => DIR_STORAGE . 'logs'), 'cache' => array('dir' => DIR_CACHE), 'imagescache' => array('dir' => DIR_IMAGE . 'cache'));

            if ($dir) {
                $key = $dir;
            } else {
                $key = $this->request->get['dir'];
            }


            $folder = array();

            $json = array();

            if (!$this->user->hasPermission('modify', 'extension/dashboard/domovoy')) {
                $json['error'] = $this->language->get('error_permission');
            } else {
                $folder['size'] = $this->getFilesSize($folders[$key]['dir']);
                $folder['unit'] = $this->format_size($folder['size']);
                $folder['files'] = count(scandir($folders[$key]['dir'])) - 2;
                $folder['date'] = date("Y-m-d H:i:s");

                $value = $this->model_setting_setting->getSettingValue('domovoy_folders_' . $key);
                if ($value) {
                    $this->model_setting_setting->editSettingValue('domovoy', 'domovoy_folders_' . $key, $folder);
                } else {
                    $settings = $this->model_setting_setting->getSetting('domovoy');
                    $settings['domovoy_folders_' . $key] = $folder;
                    $this->model_setting_setting->editSetting('domovoy', $settings);
                }

                if ($this->config->get("dashboard_domovoy_cron")) {
                    $cron = $this->config->get("dashboard_domovoy_cron");
                    $folder_size = $cron[$key]['size'] * pow(1024, 2);
                } else {
                    $folder_size = 0;
                }

                if ($folder['size'] > $folder_size && $folder_size != 0) {
                    $warning_size = sprintf($this->language->get('text_warning_size'), $cron[$key]['size']);
                } else {
                    $warning_size = $this->language->get('text_normal');
                }

                $json['success'] = sprintf($this->language->get('text_folder_size'), $folder['unit']['size'] . " " . $folder['unit']['unit']) . sprintf($this->language->get('text_folder_files'), $folder['files']) . " | " . $warning_size;
            }
        } else {
            $json['error'] = $this->language->get('error_folder');
        }

        if (!$dir) {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        }
    }

    public function phpinfo()
    {

        ob_start();
        phpinfo();
        $data['phpinfo'] = ob_get_contents();
        ob_end_clean();

        $data['phpinfo'] = preg_replace('@<style[^>]*?>.*?</style>@si', '', $data['phpinfo']);

        $this->response->setOutput($this->load->view('extension/dashboard/phpinfo', $data));
    }

    private function getFilesSize($path)
    {
        $fileSize = 0;
        $dir = scandir($path);

        foreach ($dir as $file) {
            if (($file != '.') && ($file != '..'))
                if (is_dir($path . '/' . $file))
                    $fileSize += $this->getFilesSize($path . '/' . $file);
                else
                    $fileSize += filesize($path . '/' . $file);
        }

        return $fileSize;
    }

    private function format_size($size)
    {
        $this->load->language('extension/dashboard/domovoy');
        $metrics[0] = $this->language->get('text_metrics_bit');
        $metrics[1] = $this->language->get('text_metrics_kbit');
        $metrics[2] = $this->language->get('text_metrics_mbit');
        $metrics[3] = $this->language->get('text_metrics_gbit');
        $metrics[4] = $this->language->get('text_metrics_tbit');
        $metric = 0;

        $ret = array();

        while (floor($size / 1024) > 0) {
            ++$metric;
            $size /= 1024;
        }
        $ret['size'] = round($size, 1);
        $ret['unit'] = isset($metrics[$metric]) ? $metrics[$metric] : '??';
        return $ret;
    }

    private function checkFunc($functions)
    {
        $result = array();
        foreach ($functions as $function) {
            if (function_exists($function)) {
                $result[] = $function;
            }
        }

        $result = implode(", ", $result);
        return $result;
    }

    private function deldir($dirname)
    {
        if (file_exists($dirname)) {
            if (is_dir($dirname)) {
                $dir = opendir($dirname);
                while (($filename = readdir($dir)) !== false) {
                    if ($filename != "." && $filename != "..") {
                        $file = $dirname . "/" . $filename;
                        $this->deldir($file);
                    }
                }
                closedir($dir);
                rmdir($dirname);
            } else {
                @unlink($dirname);
            }
        }
    }

    private function date_diff($date1, $date2)
    {
        $diff = strtotime($date2) - strtotime($date1);
        return abs($diff);
    }
}
