<?php
class ControllerMarketplaceOpencartforum extends Controller {
	public function index() {
		$this->load->language('marketplace/opencartforum');

		$this->document->setTitle($this->language->get('heading_title'));

		if (isset($this->request->get['filter_search'])) {
			$filter_search = $this->request->get['filter_search'];
		} else {
			$filter_search = '';
		}

		if (isset($this->request->get['filter_category'])) {
			$filter_category = $this->request->get['filter_category'];
		} else {
			$filter_category = '';
		}

		if (isset($this->request->get['filter_license'])) {
			$filter_license = $this->request->get['filter_license'];
		} else {
			$filter_license = '';
		}

		if (isset($this->request->get['filter_rating'])) {
			$filter_rating = $this->request->get['filter_rating'];
		} else {
			$filter_rating = '';
		}

		if (isset($this->request->get['filter_member_type'])) {
			$filter_member_type = $this->request->get['filter_member_type'];
		} else {
			$filter_member_type = '';
		}

		if (isset($this->request->get['filter_member'])) {
			$filter_member = $this->request->get['filter_member'];
		} else {
			$filter_member = '';
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'date_modified';
		}

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['filter_search'])) {
			$url .= '&filter_search=' . $this->request->get['filter_search'];
		}

		if (isset($this->request->get['filter_category'])) {
			$url .= '&filter_category=' . $this->request->get['filter_category'];
		}

		if (isset($this->request->get['filter_license'])) {
			$url .= '&filter_license=' . $this->request->get['filter_license'];
		}

		if (isset($this->request->get['filter_rating'])) {
			$url .= '&filter_rating=' . $this->request->get['filter_rating'];
		}

		if (isset($this->request->get['filter_member_type'])) {
			$url .= '&filter_member_type=' . $this->request->get['filter_member_type'];
		}

		if (isset($this->request->get['filter_member'])) {
			$url .= '&filter_member=' . $this->request->get['filter_member'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('marketplace/opencartforum', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$time = time();

		// We create a hash from the data in a similar method to how amazon does things.

		$url .= '&domain=' . $this->request->server['HTTP_HOST'];
		$url .= '&version=' . urlencode(VERSION);
		$url .= '&time=' . $time;
        $url .= '&language=' . $this->language->get('code');



		if (isset($this->request->get['filter_search'])) {
			$url .= '&filter_search=' . urlencode($this->request->get['filter_search']);
		}

		if (isset($this->request->get['filter_category'])) {
			$url .= '&filter_category=' . $this->request->get['filter_category'];
		}

		if (isset($this->request->get['filter_license'])) {
			$url .= '&filter_license=' . $this->request->get['filter_license'];
		}

		if (isset($this->request->get['filter_rating'])) {
			$url .= '&filter_rating=' . $this->request->get['filter_rating'];
		}

		if (isset($this->request->get['filter_member_type'])) {
			$url .= '&filter_member_type=' . $this->request->get['filter_member_type'];
		}

		if (isset($this->request->get['filter_member'])) {
			$url .= '&filter_member=' . $this->request->get['filter_member'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$curl = curl_init(OPENCARTFORUM_SERVER . 'marketplace/api?' . $url);

		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);

		$response = curl_exec($curl);

		$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		curl_close($curl);

		$response_info = json_decode($response, true);

        $extension_total = strip_tags($response_info['extension_total'] ?? '0');

		// Categories
        $curl = curl_init(OPENCARTFORUM_SERVER . 'marketplace/api/categories?' . $url);

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);

        $response = curl_exec($curl);

        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        $categories_info = json_decode($response, true);

		$url  = '';

		if (isset($this->request->get['filter_search'])) {
			$url .= '&filter_search=' . $this->request->get['filter_search'];
		}

		if (isset($this->request->get['filter_category'])) {
			$url .= '&filter_category=' . $this->request->get['filter_category'];
		}

		if (isset($this->request->get['filter_license'])) {
			$url .= '&filter_license=' . $this->request->get['filter_license'];
		}

		if (isset($this->request->get['filter_rating'])) {
			$url .= '&filter_rating=' . $this->request->get['filter_rating'];
		}

		if (isset($this->request->get['filter_member_type'])) {
			$url .= '&filter_member_type=' . $this->request->get['filter_member_type'];
		}

		if (isset($this->request->get['filter_member'])) {
			$url .= '&filter_member=' . $this->request->get['filter_member'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['promotions'] = array();

        $this->load->helper('HTMLPurifier/Bootstrap');

        HTMLPurifier_Bootstrap::registerAutoload();

        $config = HTMLPurifier_Config::createDefault();

        $response_info = $this->strip($response_info, $config);

        $promotions = isset($response_info['promotions']) ? $this->strip($response_info['promotions'], $config) : [];

        if ($promotions && $page == 1) {
            foreach ($promotions as $result) {
				$data['promotions'][] = array(
                    'name' => $result['name'],
                    'description' => $result['description'],
                    'image' => $result['image'],
                    'license' => $result['license'],
                    'price' => $result['price'],
                    'rating' => $result['rating'],
					'rating_total' => $result['rating_total'],
					'href'         => $this->url->link('marketplace/opencartforum/info', 'user_token=' . $this->session->data['user_token'] . '&extension_id=' . $result['extension_id'] . $url, true)
				);
			}
		}

		$data['extensions'] = array();

        $extensions = $this->strip($response_info['extensions'] ?? [], $config);

		if ($extensions) {
			foreach ($extensions as $result) {
				$data['extensions'][] = array(
					'name'         => $result['name'],
					'description'  => $result['description'],
					'image'        => $result['image'],
					'license'      => $result['license'],
					'price'        => $result['price'],
					'rating'       => $result['rating'],
					'rating_total' => $result['rating_total'],
					'href'         => $this->url->link('marketplace/opencartforum/info', 'user_token=' . $this->session->data['user_token'] . '&extension_id=' . $result['extension_id'] . $url, true)
				);
			}
		}

		$data['user_token'] = $this->session->data['user_token'];

		// Categories
		$url = '';

		if (isset($this->request->get['filter_search'])) {
			$url .= '&filter_search=' . $this->request->get['filter_search'];
		}

		if (isset($this->request->get['filter_license'])) {
			$url .= '&filter_license=' . $this->request->get['filter_license'];
		}

		if (isset($this->request->get['filter_rating'])) {
			$url .= '&filter_rating=' . $this->request->get['filter_rating'];
		}

		if (isset($this->request->get['filter_member_type'])) {
			$url .= '&filter_member_type=' . $this->request->get['filter_member_type'];
		}

		if (isset($this->request->get['filter_member'])) {
			$url .= '&filter_member=' . $this->request->get['filter_member'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

        $categories = $this->strip($categories_info['categories'] ?? [], $config);

		$data['categories'] = array();

		$data['categories'][] = array(
			'text'  => $this->language->get('text_all'),
			'value' => '',
			'href'  => $this->url->link('marketplace/opencartforum', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		foreach ($categories as $category) {
            $data['categories'][] = array(
                'text'  => $category['text'],
                'value' => $category['value'],
                'href'  => $this->url->link('marketplace/opencartforum', 'user_token=' . $this->session->data['user_token'] . '&filter_category='.$category['value']. $url, true)
            );
		}

		// Licenses
		$url = '';

		if (isset($this->request->get['filter_search'])) {
			$url .= '&filter_search=' . $this->request->get['filter_search'];
		}

		if (isset($this->request->get['filter_category'])) {
			$url .= '&filter_category=' . $this->request->get['filter_category'];
		}

		if (isset($this->request->get['filter_rating'])) {
			$url .= '&filter_rating=' . $this->request->get['filter_rating'];
		}

		if (isset($this->request->get['filter_member_type'])) {
			$url .= '&filter_member_type=' . $this->request->get['filter_member_type'];
		}

		if (isset($this->request->get['filter_member'])) {
			$url .= '&filter_member=' . $this->request->get['filter_member'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['licenses'] = array();

		$data['licenses'][] = array(
			'text'  => $this->language->get('text_all'),
			'value' => '',
			'href'  => $this->url->link('marketplace/opencartforum', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['licenses'][] = array(
			'text'  => $this->language->get('text_free'),
			'value' => 'free',
			'href'  => $this->url->link('marketplace/opencartforum', 'user_token=' . $this->session->data['user_token'] . '&filter_license=free' . $url, true)
		);

		$data['licenses'][] = array(
			'text'  => $this->language->get('text_paid'),
			'value' => 'paid',
			'href'  => $this->url->link('marketplace/opencartforum', 'user_token=' . $this->session->data['user_token'] . '&filter_license=paid' . $url, true)
		);

        $data['licenses'][] = array(
            'text'  => $this->language->get('text_purchased'),
            'value' => 'purchased',
            'href'  => $this->url->link('marketplace/opencartforum', 'user_token=' . $this->session->data['user_token'] . '&filter_license=purchased' . $url, true)
        );

		// Sort
		$url = '';

		if (isset($this->request->get['filter_search'])) {
			$url .= '&filter_search=' . $this->request->get['filter_search'];
		}

		if (isset($this->request->get['filter_category'])) {
			$url .= '&filter_category=' . $this->request->get['filter_category'];
		}

		if (isset($this->request->get['filter_license'])) {
			$url .= '&filter_license=' . $this->request->get['filter_license'];
		}

		if (isset($this->request->get['filter_rating'])) {
			$url .= '&filter_rating=' . $this->request->get['filter_rating'];
		}

		if (isset($this->request->get['filter_member_type'])) {
			$url .= '&filter_member_type=' . $this->request->get['filter_member_type'];
		}

		if (isset($this->request->get['filter_member'])) {
			$url .= '&filter_member=' . $this->request->get['filter_member'];
		}

		$data['sorts'] = array();

		$data['sorts'][] = array(
			'text'  => $this->language->get('text_date_modified'),
			'value' => 'date_modified',
			'href'  => $this->url->link('marketplace/opencartforum', 'user_token=' . $this->session->data['user_token'] . $url . '&sort=date_modified')
		);

		$data['sorts'][] = array(
			'text'  => $this->language->get('text_date_added'),
			'value' => 'date_added',
			'href'  => $this->url->link('marketplace/opencartforum', 'user_token=' . $this->session->data['user_token'] . $url . '&sort=date_added')
		);

		/*$data['sorts'][] = array(
			'text'  => $this->language->get('text_rating'),
			'value' => 'rating',
			'href'  => $this->url->link('marketplace/opencartforum', 'user_token=' . $this->session->data['user_token'] . $url . '&sort=rating')
		);*/


		$data['sorts'][] = array(
			'text'  => $this->language->get('text_name'),
			'value' => 'name',
			'href'  => $this->url->link('marketplace/opencartforum', 'user_token=' . $this->session->data['user_token'] . $url . '&sort=name')
		);

		/*$data['sorts'][] = array(
			'text'  => $this->language->get('text_price'),
			'value' => 'price',
			'href'  => $this->url->link('marketplace/opencartforum', 'user_token=' . $this->session->data['user_token'] . $url . '&sort=price')
		);*/

		// Pagination
		$url = '';

		if (isset($this->request->get['filter_search'])) {
			$url .= '&filter_search=' . $this->request->get['filter_search'];
		}

		if (isset($this->request->get['filter_category'])) {
			$url .= '&filter_category=' . $this->request->get['filter_category'];
		}

		if (isset($this->request->get['filter_license'])) {
			$url .= '&filter_license=' . $this->request->get['filter_license'];
		}

		if (isset($this->request->get['filter_rating'])) {
			$url .= '&filter_rating=' . $this->request->get['filter_rating'];
		}

		if (isset($this->request->get['filter_member_type'])) {
			$url .= '&filter_member_type=' . $this->request->get['filter_member_type'];
		}

		if (isset($this->request->get['filter_member'])) {
			$url .= '&filter_member=' . $this->request->get['filter_member'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		$pagination = new Pagination();
		$pagination->total = (int)$extension_total;
		$pagination->page = $page;
		$pagination->limit = 12;
		$pagination->url = $this->url->link('marketplace/opencartforum', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['filter_search'] = $filter_search;
		$data['filter_category'] = $filter_category;
		$data['filter_license'] = $filter_license;
		$data['filter_member_type'] = $filter_member_type;
		$data['filter_rating'] = $filter_rating;
		$data['sort'] = $sort;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('marketplace/opencartforum_list', $data));
	}

	public function info() {
		if (isset($this->request->get['extension_id'])) {
			$extension_id = $this->request->get['extension_id'];
		} else {
			$extension_id = 0;
		}

        $this->document->setTitle($this->language->get('heading_title'));
		$time = time();
		$url = '&domain=' . $this->request->server['HTTP_HOST'];
		$url .= '&version=' . urlencode(VERSION);
		$url .= '&extension_id=' . $extension_id;
		$url .= '&time=' . $time;
		$url .= '&language=' . $this->language->get('code');

		$curl = curl_init(OPENCARTFORUM_SERVER . 'marketplace/api/info?' . $url);

		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);

		$response = curl_exec($curl);

		$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		curl_close($curl);

		$response_info = json_decode($response, true);

		if ($response_info) {
			$this->load->language('marketplace/opencartforum');

			$this->document->setTitle($this->language->get('heading_title'));


			$data['user_token'] = $this->session->data['user_token'];

			$url = '';

			if (isset($this->request->get['filter_search'])) {
				$url .= '&filter_search=' . $this->request->get['filter_search'];
			}

			if (isset($this->request->get['filter_category'])) {
				$url .= '&filter_category=' . $this->request->get['filter_category'];
			}

			if (isset($this->request->get['filter_license'])) {
				$url .= '&filter_license=' . $this->request->get['filter_license'];
			}

			if (isset($this->request->get['filter_username'])) {
				$url .= '&filter_username=' . $this->request->get['filter_username'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$data['cancel'] = $this->url->link('marketplace/opencartforum', 'user_token=' . $this->session->data['user_token'] . $url, true);

			$data['breadcrumbs'] = array();

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('marketplace/opencartforum', 'user_token=' . $this->session->data['user_token'] . $url, true)
			);



            $this->document->setTitle($this->language->get('heading_title'));

            $this->load->helper('HTMLPurifier/Bootstrap');

            HTMLPurifier_Bootstrap::registerAutoload();

            $config = HTMLPurifier_Config::createDefault();
            $config->set('AutoFormat.RemoveEmpty',true);
            $config->set('HTML.Allowed', 'p,ul[style],ol,li,a,img');
            $config->set('HTML.AllowedAttributes', 'src, *.style, alt, href, target');
            $config->set('CSS.AllowedProperties', 'font-size, text-align, color');
            $config->set('HTML.Nofollow', true);
            $config->set('HTML.TargetBlank', true);


            $response_info = $this->strip($response_info, $config);


            foreach ($response_info as $key => $value) {
                $data[$key] = $value;
            }

			$data['filter_member'] = $this->url->link('marketplace/opencartforum', 'user_token=' . $this->session->data['user_token'] . '&filter_member=' . strip_tags($response_info['member_username']));





			$this->document->addStyle('view/javascript/jquery/magnific/magnific-popup.css');
			$this->document->addScript('view/javascript/jquery/magnific/jquery.magnific-popup.min.js');

			$data['header'] = $this->load->controller('common/header');
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['footer'] = $this->load->controller('common/footer');

			$this->response->setOutput($this->load->view('marketplace/opencartforum_info', $data));
		} else {
			return new Action('error/not_found');
		}
	}

    private function strip($string, $config) {
        if (empty($string)) {
            return is_array($string) ? [] : '';
        }

        $purifier = new HTMLPurifier($config);

        if (is_array($string)) {
            foreach ($string as $k => $v) {
                $string[$k] = $this->strip($v, $config);
            }
            return $string;
        }

        if (is_string($string)) {
            return $purifier->purify($string);
        }

        return $string;
    }

}
