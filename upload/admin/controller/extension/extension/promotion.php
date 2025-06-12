<?php
class ControllerExtensionExtensionPromotion extends Controller {
	public function index() {
		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, OPENCARTFORUM_SERVER . 'marketplace/api/promotion?type=' . substr($this->request->get['route'], strrpos($this->request->get['route'], '/') + 1));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);

		$response = curl_exec($curl);

		curl_close($curl);

        $this->load->helper('HTMLPurifier/Bootstrap');

        HTMLPurifier_Bootstrap::registerAutoload();

        $config = HTMLPurifier_Config::createDefault();

        $response = $this->strip($response, $config);

		if ($response) {
			return $response;
		} else {
			return '';
		}
	}

    private function strip($string, $config) {
        $purifier = new HTMLPurifier($config);
        if (is_array($string))  {
            foreach ($string as $k => $v) {
                $string[$k] = $this->strip($v, $config); } return $string;
        }
        return $purifier->purify($string);
    }
}