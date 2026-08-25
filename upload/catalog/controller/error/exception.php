<?php
namespace Opencart\Catalog\Controller\Error;
/**
 * Class Exception
 *
 * @package Opencart\Catalog\Controller\Error
 */
class Exception extends \Opencart\System\Engine\Controller {
	/**
	 * Index
	 *
	 * @param string $message
	 * @param string $code
	 * @param string $file
	 * @param string $line
	 *
	 * @return void
	 */
	public function index(string $message, string $code, string $file, string $line): void {
		$this->load->language('error/exception');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home', 'language=' . $this->config->get('config_language'))
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('error/exception', 'language=' . $this->config->get('config_language'))
		];

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('error/exception', $data));
	}
}
