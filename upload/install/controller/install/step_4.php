<?php
namespace Opencart\Install\Controller\Install;
/**
 * Class Step4
 *
 * @package Opencart\Install\Controller\Install
 */
class Step4 extends \Opencart\System\Engine\Controller {
	/**
	 * Index
	 *
	 * @return void
	 */
	public function index(): void {
		$this->load->language('install/step_4');
		$this->load->model('install/install');

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {

			$this->model_install_install->enableCountries($this->request->post['country']);

			$this->response->redirect($this->url->link('install/step_5'));
		}

		$this->document->setTitle($this->language->get('heading_title'));

		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_step_4'] = $this->language->get('text_step_4');
		$data['text_yes'] = $this->language->get('text_yes');
		$data['text_no'] = $this->language->get('text_no');
		$data['text_select'] = $this->language->get('text_select');
		$data['text_delete'] = $this->language->get('text_delete');
		$data['text_select_all'] = $this->language->get('text_select_all');
		$data['text_unselect_all'] = $this->language->get('text_unselect_all');

		$data['button_back'] = $this->language->get('button_back');
		$data['button_continue'] = $this->language->get('button_continue');

		$data['entry_country'] = $this->language->get('entry_country');
		$data['help_country'] = $this->language->get('help_country');

		$data['countries'] = $this->model_install_install->getCountries();

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			$data['country'] = !empty($this->request->post['country']) ? $this->request->post['country'] : [];
		} else {
			$data['country'] = [];

			foreach ($data['countries'] as $country) {
				if ($country['status']) {
					$data['country'][] = $country['country_id'];
				}
			}
		}

		$data['back'] = $this->url->link('install/step_3');

		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');

		$this->response->setOutput($this->load->view('install/step_4', $data));
	}
}
