<?php
namespace Opencart\Admin\Controller\Marketplace;
/**
 * Class OpenCartForum
 *
 * @package Opencart\Admin\Controller\OpenCartForum
 */
class OpenCartForum extends \Opencart\System\Engine\Controller {
	/**
	 * @return void
	 */
	public function index(): void {
		$this->load->language('marketplace/opencartforum');

		$this->document->setTitle($this->language->get('heading_title'));

		if (isset($this->request->get['filter_search'])) {
			$filter_search = (string)$this->request->get['filter_search'];
		} else {
			$filter_search = '';
		}

		if (isset($this->request->get['filter_category'])) {
			$filter_category = (string)$this->request->get['filter_category'];
		} else {
			$filter_category = '';
		}

		if (isset($this->request->get['filter_license'])) {
			$filter_license = (string)$this->request->get['filter_license'];
		} else {
			$filter_license = '';
		}

		if (isset($this->request->get['filter_rating'])) {
			$filter_rating = (int)$this->request->get['filter_rating'];
		} else {
			$filter_rating = '';
		}

		if (isset($this->request->get['filter_member_type'])) {
			$filter_member_type = (string)$this->request->get['filter_member_type'];
		} else {
			$filter_member_type = '';
		}

		if (isset($this->request->get['filter_member'])) {
			$filter_member = (string)$this->request->get['filter_member'];
		} else {
			$filter_member = '';
		}

		if (isset($this->request->get['sort'])) {
			$sort = (string)$this->request->get['sort'];
		} else {
			$sort = 'date_added';
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

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('marketplace/opencartforum', 'user_token=' . $this->session->data['user_token'] . $url)
		];

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
			$url .= '&filter_member=' . urlencode($this->request->get['filter_member']);
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$curl = curl_init(OPENCARTFORUM_SERVER . 'marketplace/api?' . $url);

		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_FORBID_REUSE, true);
		curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($curl, CURLOPT_POST, true);

		$response = curl_exec($curl);

		$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		curl_close($curl);

		$response_info = json_decode($response, true);

		$extension_total = strip_tags($response_info['extension_total']);

		// Categories
        $curl = curl_init(OPENCARTFORUM_SERVER . 'marketplace/api/categories?' . $url);

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FORBID_REUSE, true);
        curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);

        $response = curl_exec($curl);

        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        $categories_info = json_decode($response, true);

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

		$data['promotions'] = array();

        $this->load->helper('HTMLPurifier/Bootstrap');

        \HTMLPurifier_Bootstrap::registerAutoload();

        $config = \HTMLPurifier_Config::createDefault();

        $response_info = $this->strip($response_info, $config);

        $promotions = $this->strip($response_info['promotions'], $config);

		if (isset($response_info['promotions']) && $page == 1) {
			foreach ($response_info['promotions'] as $result) {
				$data['promotions'][] = [
					'name'         => $result['name'],
					'description'  => $result['description'],
					'image'        => $result['image'],
					'license'      => $result['license'],
					'price'        => $result['price'],
					'old_price'    => $result['old_price'] ?? '',
					'discount'     => $result['discount'] ?? '',
					'ukraine'      => $result['ukraine'] ?? '',
					'sales'        => $result['sales'] ?? '',
					'downloaded'   => $result['downloaded'] ?? '',
					'author'       => $result['member_username'] ?? '',
					'author_url'   => $result['member_url'] ?? '',
					'rating'       => $result['rating'],
					'rating_total' => $result['rating_total'],
					'href'         => $this->url->link('marketplace/opencartforum.info', 'user_token=' . $this->session->data['user_token'] . '&extension_id=' . $result['extension_id'] . $url)
				];
			}
		}

		$data['extensions'] = [];

        $extensions = $this->strip($response_info['extensions'], $config);

        if ($extensions) {
            foreach ($extensions as $result) {
				$data['extensions'][] = [
					'name'         => $result['name'],
					'description'  => $result['description'],
					'image'        => $result['image'],
					'license'      => $result['license'],
					'price'        => $result['price'],
					'old_price'    => $result['old_price'] ?? '',
					'discount'     => $result['discount'] ?? '',
					'ukraine'      => $result['ukraine'] ?? '',
					'sales'        => $result['sales'] ?? '',
					'downloaded'   => $result['downloaded'] ?? '',
					'author'       => $result['member_username'] ?? '',
					'author_url'   => $result['member_url'] ?? '',
					'rating'       => $result['rating'],
					'rating_total' => $result['rating_total'],
					'href'         => $this->url->link('marketplace/opencartforum.info', 'user_token=' . $this->session->data['user_token'] . '&extension_id=' . $result['extension_id'] . $url, true)
				];
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

        $categories = $this->strip($categories_info['categories'], $config);

		$data['categories'] = [];

		$data['categories'][] = [
			'text'  => $this->language->get('text_all'),
			'value' => '',
			'href'  => $this->url->link('marketplace/opencartforum', 'user_token=' . $this->session->data['user_token'] . $url, true)
		];

        foreach ($categories as $category) {
            $data['categories'][] = [
                'text'  => $category['text'],
                'value' => $category['value'],
                'href'  => $this->url->link('marketplace/opencartforum', 'user_token=' . $this->session->data['user_token'] . '&filter_category='.$category['value']. $url, true)
            ];
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

		$data['licenses'] = [];

		$data['licenses'][] = [
			'text'  => $this->language->get('text_all'),
			'value' => '',
			'href'  => $this->url->link('marketplace/opencartforum', 'user_token=' . $this->session->data['user_token'] . $url)
		];

		$data['licenses'][] = [
			'text'  => $this->language->get('text_recommended'),
			'value' => 'recommended',
			'href'  => $this->url->link('marketplace/opencartforum', 'user_token=' . $this->session->data['user_token'] . '&filter_license=recommended' . $url)
		];

		$data['licenses'][] = [
			'text'  => $this->language->get('text_free'),
			'value' => 'free',
			'href'  => $this->url->link('marketplace/opencartforum', 'user_token=' . $this->session->data['user_token'] . '&filter_license=free' . $url)
		];

		$data['licenses'][] = [
			'text'  => $this->language->get('text_paid'),
			'value' => 'paid',
			'href'  => $this->url->link('marketplace/opencartforum', 'user_token=' . $this->session->data['user_token'] . '&filter_license=paid' . $url)
		];

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

		$data['sorts'] = [];

		$data['sorts'][] = [
			'text'  => $this->language->get('text_date_added'),
			'value' => 'date_added',
			'href'  => $this->url->link('marketplace/opencartforum', 'user_token=' . $this->session->data['user_token'] . $url . '&sort=date_added')
		];

		$data['sorts'][] = [
			'text'  => $this->language->get('text_date_modified'),
			'value' => 'date_modified',
			'href'  => $this->url->link('marketplace/opencartforum', 'user_token=' . $this->session->data['user_token'] . $url . '&sort=date_modified')
		];

		$data['sorts'][] = [
			'text'  => $this->language->get('text_rating'),
			'value' => 'rating',
			'href'  => $this->url->link('marketplace/opencartforum', 'user_token=' . $this->session->data['user_token'] . $url . '&sort=rating')
		];

		$data['sorts'][] = [
			'text'  => $this->language->get('text_name'),
			'value' => 'name',
			'href'  => $this->url->link('marketplace/opencartforum', 'user_token=' . $this->session->data['user_token'] . $url . '&sort=name')
		];

		$data['sorts'][] = [
			'text'  => $this->language->get('text_price'),
			'value' => 'price',
			'href'  => $this->url->link('marketplace/opencartforum', 'user_token=' . $this->session->data['user_token'] . $url . '&sort=price')
		];

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

		$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $extension_total,
			'page'  => $page,
			'limit' => 12,
			'url'   => $this->url->link('marketplace/opencartforum', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}')
		]);

		$data['filter_search'] = $filter_search;
		$data['filter_category'] = $filter_category;
		$data['filter_license'] = $filter_license;
		$data['filter_member_type'] = $filter_member_type;
		$data['filter_rating'] = $filter_rating;
		$data['sort'] = $sort;

		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('marketplace/opencartforum_list', $data));
	}

	/**
	 * @return object|\Opencart\System\Engine\Action|null
	 */
	public function info(): object|null {
		if (isset($this->request->get['extension_id'])) {
			$extension_id = (int)$this->request->get['extension_id'];
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

		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_FORBID_REUSE, true);
		curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($curl, CURLOPT_POST, true);

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

			$data['back'] = $this->url->link('marketplace/opencartforum', 'user_token=' . $this->session->data['user_token'] . $url);

			$data['breadcrumbs'] = [];

			$data['breadcrumbs'][] = [
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
			];

			$data['breadcrumbs'][] = [
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('marketplace/opencartforum', 'user_token=' . $this->session->data['user_token'] . $url)
			];

			$data['banner'] = $response_info['banner'];

			$data['extension_id'] = (int)$this->request->get['extension_id'];
			$data['name'] = $response_info['name'];
			$data['description'] = $response_info['description'];
			$data['documentation'] = $response_info['documentation'];
			$data['price'] = $response_info['price'];
			$data['license'] = $response_info['license'];
			$data['license_period'] = $response_info['license_period'];
			$data['purchased'] = $response_info['purchased'];

			$data['rating'] = $response_info['rating'];
			$data['rating_total'] = $response_info['rating_total'];

			$data['downloaded'] = $response_info['downloaded'];
			$data['sales'] = $response_info['sales'];

			$data['member_username'] = $response_info['member_username'];
			$data['member_image'] = $response_info['member_image'];
			$data['filter_member'] = $this->url->link('marketplace/opencartforum', 'user_token=' . $this->session->data['user_token'] . '&filter_member=' . $response_info['member_username']);

            $this->load->helper('HTMLPurifier/Bootstrap');

            \HTMLPurifier_Bootstrap::registerAutoload();

            $config = \HTMLPurifier_Config::createDefault();
            $config->set('AutoFormat.RemoveEmpty', true);
            $config->set('HTML.Allowed', 'div,span,p,br,hr,h1,h2,h3,h4,h5,h6,strong,b,em,i,u,s,del,ins,sub,sup,small,mark,code,kbd,samp,var,abbr,pre,blockquote,ul,ol,li,dl,dt,dd,a,img,table,thead,tbody,tfoot,tr,th,td,caption,figure,figcaption');
            $config->set('HTML.AllowedAttributes', '*.style, *.title, abbr.title, a.href, a.target, a.rel, img.src, img.alt, img.width, img.height, td.colspan, td.rowspan, th.colspan, th.rowspan, th.scope, ol.start, li.value');
            $config->set('CSS.AllowedProperties', 'font-size, font-weight, font-style, font-family, text-align, text-decoration, text-transform, line-height, letter-spacing, color, background-color, border, border-top, border-right, border-bottom, border-left, border-color, border-style, border-width, padding, padding-top, padding-right, padding-bottom, padding-left, margin, margin-top, margin-right, margin-bottom, margin-left, width, max-width, height, max-height, list-style-type, vertical-align, white-space');
            $config->set('CSS.MaxImgLength', '1200px');
            $config->set('HTML.MaxImgLength', 1200);
            $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true]);
            $config->set('Attr.AllowedClasses', []);
            $config->set('HTML.Nofollow', true);
            $config->set('HTML.TargetBlank', true);


            $response_info = $this->strip($response_info, $config);

            foreach ($response_info as $key => $value) {
                $data[$key] = $value;
            }

            $data['date_added'] = date($this->language->get('date_format_short'), strtotime($response_info['date_added']));
            $data['date_modified'] = date($this->language->get('date_format_short'), strtotime($response_info['date_modified']));
            $member_date_added = trim((string)$response_info['member_date_added']);

            $member_date = \DateTime::createFromFormat('d.m.y H:i', $member_date_added);

            if (!$member_date) {
                $member_timestamp = strtotime($member_date_added);

                $member_date = $member_timestamp ? (new \DateTime())->setTimestamp($member_timestamp) : null;
            }

            $data['member_date_added'] = $member_date ? $member_date->format($this->language->get('date_format_short')) : '';

            if (isset($response_info['comment_total'])) {
				$data['comment_total'] = $response_info['comment_total'];
			} else {
				$data['comment_total'] = 0;
			}

			$data['images'] = [];

			foreach ($response_info['images'] as $result) {
				$data['images'][] = [
					'thumb' => $result['thumb'],
					'popup' => $result['popup']
				];
			}

			$this->document->addStyle('view/javascript/jquery/magnific/magnific-popup.css');
			$this->document->addScript('view/javascript/jquery/magnific/jquery.magnific-popup.min.js');

			$data['user_token'] = $this->session->data['user_token'];

			$data['header'] = $this->load->controller('common/header');
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['footer'] = $this->load->controller('common/footer');

			$this->response->setOutput($this->load->view('marketplace/opencartforum_info', $data));

			return null;
		} else {
			return new \Opencart\System\Engine\Action('error/not_found');
		}
	}

    /**
     * @return string|array
     */

    /**
     * @param array<mixed>|string  $string
     * @param \HTMLPurifier_Config $config
     *
     * @return array<mixed>|string
     */
    private function strip($string, $config): string|array {
        $purifier = new \HTMLPurifier($config);
        if (is_array($string))  {
            foreach ($string as $k => $v) {
                $string[$k] = $this->strip($v, $config); } return $string;
        }

        return $purifier->purify($string);
    }
}
