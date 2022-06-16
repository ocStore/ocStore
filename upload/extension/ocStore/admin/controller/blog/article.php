<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

namespace Opencart\Admin\Controller\Extension\ocStore\Blog;

if (!defined('VERSION')) {
	header('Refresh: 1; URL=/');
	exit('ЗАПРЫШЧАЮ!');
}

class Article extends \Opencart\System\Engine\Controller {
	private array $error = [];

	public function index(): void {
		$this->load->language('extension/ocStore/blog/article');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/ocStore/blog/article');

		$this->getList();
	}

	public function add(): void {
		$this->load->language('extension/ocStore/blog/article');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/ocStore/blog/article');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_extension_ocStore_blog_article->addArticle($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_category'])) {
				$url .= '&filter_category=' . $this->request->get['filter_category'];
				if (isset($this->request->get['filter_sub_category'])) {
					$url .= '&filter_sub_category';
				}
			}

			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}

			if (isset($this->request->get['filter_noindex'])) {
				$url .= '&filter_noindex=' . $this->request->get['filter_noindex'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('extension/ocStore/blog/article', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function edit(): void {
		$this->load->language('extension/ocStore/blog/article');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/ocStore/blog/article');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_extension_ocStore_blog_article->editArticle($this->request->get['article_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_category'])) {
				$url .= '&filter_category=' . $this->request->get['filter_category'];
				if (isset($this->request->get['filter_sub_category'])) {
					$url .= '&filter_sub_category';
				}
			}

			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}

			if (isset($this->request->get['filter_noindex'])) {
				$url .= '&filter_noindex=' . $this->request->get['filter_noindex'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('extension/ocStore/blog/article', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function delete(): void {
		$this->load->language('extension/ocStore/blog/article');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/ocStore/blog/article');

		if (isset($this->request->post['selected']) && $this->validate()) {
			foreach ($this->request->post['selected'] as $article_id) {
				$this->model_extension_ocStore_blog_article->deleteArticle($article_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_category'])) {
				$url .= '&filter_category=' . $this->request->get['filter_category'];
				if (isset($this->request->get['filter_sub_category'])) {
					$url .= '&filter_sub_category';
				}
			}

			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}

			if (isset($this->request->get['filter_noindex'])) {
				$url .= '&filter_noindex=' . $this->request->get['filter_noindex'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('extension/ocStore/blog/article', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	public function copy(): void {
		$this->load->language('extension/ocStore/blog/article');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/ocStore/blog/article');

		if (isset($this->request->post['selected']) && $this->validate()) {
			foreach ($this->request->post['selected'] as $article_id) {
				$this->model_extension_ocStore_blog_article->copyArticle($article_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_category'])) {
				$url .= '&filter_category=' . $this->request->get['filter_category'];
				if (isset($this->request->get['filter_sub_category'])) {
					$url .= '&filter_sub_category';
				}
			}

			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}

			if (isset($this->request->get['filter_noindex'])) {
				$url .= '&filter_noindex=' . $this->request->get['filter_noindex'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('extension/ocStore/blog/article', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	public function enable(): void {
		$this->load->language('extension/ocStore/blog/article');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/ocStore/blog/article');

		if (isset($this->request->post['selected']) && $this->validate()) {
			foreach ($this->request->post['selected'] as $article_id) {
				$this->model_extension_ocStore_blog_article->editArticleStatus($article_id, 1);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			$this->response->redirect($this->url->link('extension/ocStore/blog/article', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	public function disable(): void {
		$this->load->language('extension/ocStore/blog/article');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/ocStore/blog/article');

		if (isset($this->request->post['selected']) && $this->validate()) {
			foreach ($this->request->post['selected'] as $article_id) {
				$this->model_extension_ocStore_blog_article->editArticleStatus($article_id, 0);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			$this->response->redirect($this->url->link('extension/ocStore/blog/article', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	protected function getList(): void {
		$this->config->set('configblog_limit_admin', 10);

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = null;
		}

		$filter_sub_category = null;

		if (isset($this->request->get['filter_category'])) {
			$filter_category = $this->request->get['filter_category'];

			if (!empty($filter_category) && isset($this->request->get['filter_sub_category'])) {
				$filter_sub_category = true;
			} elseif (isset($this->request->get['filter_sub_category'])) {
				unset($this->request->get['filter_sub_category']);
			}
		} else {
			$filter_category = null;

			if (isset($this->request->get['filter_sub_category'])) {
				unset($this->request->get['filter_sub_category']);
			}
		}

		$filter_category_name = null;

		if (isset($filter_category)) {
			if ($filter_category > 0) {
				$this->load->model('extension/ocStore/blog/category');

				$category = $this->model_extension_ocStore_blog_category->getCategory($filter_category);

				if ($category) {
					$filter_category_name = ($category['path']) ? $category['path'] . ' &gt; ' . $category['name'] : $category['name'];
				} else {
					$filter_category = null;
					$filter_sub_category = null;

					unset($this->request->get['filter_category']);

					if (isset($this->request->get['filter_sub_category'])) {
						unset($this->request->get['filter_sub_category']);
					}
				}
			} else {
				$filter_category_name = $this->language->get('text_none_category');
			}
		}

		if (isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		} else {
			$filter_status = null;
		}

		if (isset($this->request->get['filter_noindex'])) {
			$filter_noindex = $this->request->get['filter_noindex'];
		} else {
			$filter_noindex = null;
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'pd.name';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		if ($this->request->server['HTTPS']) {
			$server = HTTPS_CATALOG;
		} else {
			$server = HTTP_CATALOG;
		}

		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_category'])) {
			$url .= '&filter_category=' . $this->request->get['filter_category'];
			if (isset($this->request->get['filter_sub_category'])) {
				$url .= '&filter_sub_category';
			}
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['filter_noindex'])) {
			$url .= '&filter_noindex=' . $this->request->get['filter_noindex'];
		}

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
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/ocStore/blog/article', 'user_token=' . $this->session->data['user_token'] . $url, true)
		];

		$data['add'] = $this->url->link('extension/ocStore/blog/article|add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['copy'] = $this->url->link('extension/ocStore/blog/article|copy', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('extension/ocStore/blog/article|delete', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['enabled'] = $this->url->link('extension/ocStore/blog/article|enable', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['disabled'] = $this->url->link('extension/ocStore/blog/article|disable', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['articles'] = [];

		$filter_data = [
			'filter_name'         => $filter_name,
			'filter_category'     => $filter_category,
			'filter_sub_category' => $filter_sub_category,
			'filter_status'       => $filter_status,
			'filter_noindex'      => $filter_noindex,
			'sort'                => $sort,
			'order'               => $order,
			'start'               => ($page - 1) * $this->config->get('configblog_limit_admin'),
			'limit'               => $this->config->get('configblog_limit_admin')
		];

		$this->load->model('tool/image');

		$article_total = $this->model_extension_ocStore_blog_article->getTotalArticles($filter_data);

		$results = $this->model_extension_ocStore_blog_article->getArticles($filter_data);

		foreach ($results as $result) {
			if (is_file(DIR_IMAGE . $result['image'])) {
				$image = $this->model_tool_image->resize($result['image'], 40, 40);
			} else {
				$image = $this->model_tool_image->resize('no_image.png', 40, 40);
			}

			$data['articles'][] = [
				'article_id' => $result['article_id'],
				'image'      => $image,
				'name'       => $result['name'],
				'status'     => ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
				'noindex'    => ($result['noindex'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
				'href_shop'  => $server . 'index.php?route=extension/ocStore/blog/article&article_id=' . $result['article_id'],
				'edit'       => $this->url->link('extension/ocStore/blog/article|edit', 'user_token=' . $this->session->data['user_token'] . '&article_id=' . $result['article_id'] . $url, true)
			];
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = [];
		}

		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_category'])) {
			$url .= '&filter_category=' . $this->request->get['filter_category'];
			if (isset($this->request->get['filter_sub_category'])) {
				$url .= '&filter_sub_category';
			}
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['filter_noindex'])) {
			$url .= '&filter_noindex=' . $this->request->get['filter_noindex'];
		}

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_name'] = $this->url->link('extension/ocStore/blog/article', 'user_token=' . $this->session->data['user_token'] . '&sort=pd.name' . $url, true);
		$data['sort_status'] = $this->url->link('extension/ocStore/blog/article', 'user_token=' . $this->session->data['user_token'] . '&sort=p.status' . $url, true);
		$data['sort_noindex'] = $this->url->link('extension/ocStore/blog/article', 'user_token=' . $this->session->data['user_token'] . '&sort=p.noindex' . $url, true);
		$data['sort_order'] = $this->url->link('extension/ocStore/blog/article', 'user_token=' . $this->session->data['user_token'] . '&sort=p.sort_order' . $url, true);

		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_category'])) {
			$url .= '&filter_category=' . $this->request->get['filter_category'];
			if (isset($this->request->get['filter_sub_category'])) {
				$url .= '&filter_sub_category';
			}
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['filter_noindex'])) {
			$url .= '&filter_noindex=' . $this->request->get['filter_noindex'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $article_total,
			'page'  => $page,
			'limit' => $this->config->get('configblog_limit_admin'),
			'url'   => $this->url->link('extension/ocStore/blog/article', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true)
		]);

		$data['results'] = sprintf($this->language->get('text_pagination'), ($article_total) ? (($page - 1) * $this->config->get('configblog_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('configblog_limit_admin')) > ($article_total - $this->config->get('configblog_limit_admin'))) ? $article_total : ((($page - 1) * $this->config->get('configblog_limit_admin')) + $this->config->get('configblog_limit_admin')), $article_total, ceil($article_total / $this->config->get('configblog_limit_admin')));

		$data['filter_name'] = $filter_name;
		$data['filter_category_name'] = $filter_category_name;
		$data['filter_category'] = $filter_category;
		$data['filter_sub_category'] = $filter_sub_category;
		$data['filter_status'] = $filter_status;
		$data['filter_noindex'] = $filter_noindex;

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/ocStore/blog/article_list', $data));
	}

	protected function getForm(): void {
		$data['text_form'] = !isset($this->request->get['article_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = [];
		}

		if (isset($this->error['meta_title'])) {
			$data['error_meta_title'] = $this->error['meta_title'];
		} else {
			$data['error_meta_title'] = [];
		}

		if (isset($this->error['meta_h1'])) {
			$data['error_meta_h1'] = $this->error['meta_h1'];
		} else {
			$data['error_meta_h1'] = [];
		}

		if (isset($this->error['keyword'])) {
			$data['error_keyword'] = $this->error['keyword'];
		} else {
			$data['error_keyword'] = '';
		}

		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_category'])) {
			$url .= '&filter_category=' . $this->request->get['filter_category'];
			if (isset($this->request->get['filter_sub_category'])) {
				$url .= '&filter_sub_category';
			}
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['filter_noindex'])) {
			$url .= '&filter_noindex=' . $this->request->get['filter_noindex'];
		}

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
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/ocStore/blog/article', 'user_token=' . $this->session->data['user_token'] . $url, true)
		];

		if (!isset($this->request->get['article_id'])) {
			$data['action'] = $this->url->link('extension/ocStore/blog/article|add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('extension/ocStore/blog/article|edit', 'user_token=' . $this->session->data['user_token'] . '&article_id=' . $this->request->get['article_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link('extension/ocStore/blog/article', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['article_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$article_info = $this->model_extension_ocStore_blog_article->getArticle($this->request->get['article_id']);
		}

		$data['user_token'] = $this->session->data['user_token'];

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['article_description'])) {
			$data['article_description'] = $this->request->post['article_description'];
		} elseif (isset($this->request->get['article_id'])) {
			$data['article_description'] = $this->model_extension_ocStore_blog_article->getDescriptions($this->request->get['article_id']);
		} else {
			$data['article_description'] = [];
		}

		$language_id = $this->config->get('config_language_id');
		if (isset($data['article_description'][$language_id]['name'])) {
			$data['heading_title'] = $data['article_description'][$language_id]['name'];
		}

		if (isset($this->request->post['image'])) {
			$data['image'] = $this->request->post['image'];
		} elseif (isset($article_info['image'])) {
			$data['image'] = $article_info['image'];
		} else {
			$data['image'] = '';
		}

		$this->load->model('tool/image');

		if (is_file(DIR_IMAGE . html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8'))) {
			$data['thumb'] = $this->model_tool_image->resize(html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8'), 100, 100);
		} else {
			$data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		$this->load->model('setting/store');

		$data['stores'] = [];

		$data['stores'][] = [
			'store_id' => 0,
			'name'     => $this->language->get('text_default')
		];

		$stores = $this->model_setting_store->getStores();

		foreach ($stores as $store) {
			$data['stores'][] = [
				'store_id' => $store['store_id'],
				'name'     => $store['name']
			];
		}

		if (isset($this->request->post['article_store'])) {
			$data['article_store'] = $this->request->post['article_store'];
		} elseif (isset($this->request->get['article_id'])) {
			$data['article_store'] = $this->model_extension_ocStore_blog_article->getStores($this->request->get['article_id']);
		} else {
			$data['article_store'] = [0];
		}

		if (isset($this->request->post['sort_order'])) {
			$data['sort_order'] = $this->request->post['sort_order'];
		} elseif (isset($article_info['sort_order'])) {
			$data['sort_order'] = $article_info['sort_order'];
		} else {
			$data['sort_order'] = 1;
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (isset($article_info['status'])) {
			$data['status'] = $article_info['status'];
		} else {
			$data['status'] = true;
		}

		if (isset($this->request->post['noindex'])) {
			$data['noindex'] = $this->request->post['noindex'];
		} elseif (isset($article_info['noindex'])) {
			$data['noindex'] = $article_info['noindex'];
		} else {
			$data['noindex'] = 1;
		}

		// Categories
		$this->load->model('extension/ocStore/blog/category');

		$data['categories'] = $this->model_extension_ocStore_blog_category->getCategories();

		if (isset($this->request->post['main_blog_category_id'])) {
			$data['main_blog_category_id'] = $this->request->post['main_blog_category_id'];
		} elseif (isset($this->request->get['article_id'])) {
			$data['main_blog_category_id'] = $this->model_extension_ocStore_blog_article->getMainCategoryId($this->request->get['article_id']);
		} else {
			$data['main_blog_category_id'] = 0;
		}

		if (isset($this->request->post['article_blog_category'])) {
			$categories = $this->request->post['article_blog_category'];
		} elseif (isset($this->request->get['article_id'])) {
			$categories = $this->model_extension_ocStore_blog_article->getCategories($this->request->get['article_id']);
		} else {
			$categories = [];
		}

		$data['article_categories'] = [];

		foreach ($categories as $blog_category_id) {
			$category_info = $this->model_extension_ocStore_blog_category->getCategory($blog_category_id);

			if ($category_info) {
				$data['article_categories'][] = [
					'blog_category_id' => $category_info['blog_category_id'],
					'name' => ($category_info['path']) ? $category_info['path'] . ' &gt; ' . $category_info['name'] : $category_info['name']
				];
			}
		}

		// Images
		if (isset($this->request->post['article_image'])) {
			$article_images = $this->request->post['article_image'];
		} elseif (isset($this->request->get['article_id'])) {
			$article_images = $this->model_extension_ocStore_blog_article->getImages($this->request->get['article_id']);
		} else {
			$article_images = [];
		}

		$data['article_images'] = [];

		foreach ($article_images as $article_image) {
			if (is_file(DIR_IMAGE . $article_image['image'])) {
				$image = $article_image['image'];
				$thumb = $article_image['image'];
			} else {
				$image = '';
				$thumb = 'no_image.png';
			}

			$data['article_images'][] = [
				'image'      => $image,
				'thumb'      => $this->model_tool_image->resize($thumb, 100, 100),
				'sort_order' => $article_image['sort_order']
			];
		}

		// Downloads
		$this->load->model('catalog/download');

		if (isset($this->request->post['article_download'])) {
			$article_downloads = $this->request->post['article_download'];
		} elseif (isset($this->request->get['article_id'])) {
			$article_downloads = $this->model_extension_ocStore_blog_article->getDownloads($this->request->get['article_id']);
		} else {
			$article_downloads = [];
		}

		$data['article_downloads'] = [];

		foreach ($article_downloads as $download_id) {
			$download_info = $this->model_catalog_download->getDownload($download_id);

			if ($download_info) {
				$data['article_downloads'][] = [
					'download_id' => $download_info['download_id'],
					'name'        => $download_info['name']
				];
			}
		}

		if (isset($this->request->post['article_related'])) {
			$articles = $this->request->post['article_related'];
		} elseif (isset($this->request->get['article_id'])) {
			$articles = $this->model_extension_ocStore_blog_article->getRelated($this->request->get['article_id']);
		} else {
			$articles = [];
		}

		$data['article_relateds'] = [];

		foreach ($articles as $article_id) {
			$related_info = $this->model_extension_ocStore_blog_article->getArticle($article_id);

			if ($related_info) {
				$data['article_relateds'][] = [
					'article_id' => $related_info['article_id'],
					'name'       => $related_info['name']
				];
			}
		}

		if (isset($this->request->post['article_related_product'])) {
			$products = $this->request->post['article_related_product'];
		} elseif (isset($this->request->get['article_id'])) {
			$products = $this->model_extension_ocStore_blog_article->getProductRelated($this->request->get['article_id']);
		} else {
			$products = [];
		}

		$data['article_related_product'] = [];

		$this->load->model('catalog/product');

		foreach ($products as $product_id) {
			$product_info = $this->model_catalog_product->getProduct($product_id);

			if ($product_info) {
				$data['article_related_product'][] = [
					'product_id' => $product_info['product_id'],
					'name'       => $product_info['name']
				];
			}
		}

		if (isset($this->request->get['article_id'])) {
			$data['product_seo_url'] = $this->model_catalog_product->getSeoUrls($this->request->get['article_id']);
		} else {
			$data['product_seo_url'] = [];
		}

		$this->load->model('design/layout');

		$data['layouts'] = $this->model_design_layout->getLayouts();

		if (isset($this->request->post['article_layout'])) {
			$data['article_layout'] = $this->request->post['article_layout'];
		} elseif (isset($this->request->get['article_id'])) {
			$data['article_layout'] = $this->model_extension_ocStore_blog_article->getLayouts($this->request->get['article_id']);
		} else {
			$data['article_layout'] = [];
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/ocStore/blog/article_form', $data));
	}

	protected function validate(): bool {
		if (!$this->user->hasPermission('modify', 'extension/ocStore/blog/article')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (isset($this->request->post['article_description']) && is_array($this->request->post['article_description'])) {
			foreach ($this->request->post['article_description'] as $language_id => $value) {
				if (!isset($value['name']) || (utf8_strlen($value['name']) < 2) || (utf8_strlen($value['name']) > 255)) {
					$this->error['name'][$language_id] = $this->language->get('error_name');
				}

				if (!empty($value['meta_h1']) || utf8_strlen($value['meta_h1']) > 255) {
					$this->error['meta_h1'][$language_id] = $this->language->get('error_meta_h1');
				}

				if (!empty($value['meta_title']) || (utf8_strlen($value['meta_title']) > 255)) {
					$this->error['meta_title'][$language_id] = $this->language->get('error_meta_title');
				}
			}
		}

		if (!empty($this->request->post['article_seo_url'])) {
			$this->load->model('design/seo_url');

			foreach ($this->request->post['article_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if ($keyword) {
						$seo_url_info = $this->model_design_seo_url->getSeoUrlByKeyword($keyword, $store_id, $language_id);

						if ($seo_url_info && (!isset($this->request->get['article_id']) || $seo_url_info['key'] != 'article_id' || $seo_url_info['value'] != (int)$this->request->get['article_id'])) {
							$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_keyword');
						}
					} else {
						$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_seo');
					}
				}
			}
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}

	public function autocomplete(): void {
		$json = [];

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('extension/ocStore/blog/article');

			if (isset($this->request->get['filter_name'])) {
				$filter_name = $this->request->get['filter_name'];
			} else {
				$filter_name = '';
			}

			if (isset($this->request->get['limit'])) {
				$limit = $this->request->get['limit'];
			} else {
				$limit = $this->config->get('configblog_limit_admin');
			}

			$filter_data = [
				'filter_name'  => $filter_name,
				'start'        => 0,
				'limit'        => $limit
			];

			$results = $this->model_extension_ocStore_blog_article->getArticles($filter_data);

			foreach ($results as $result) {
				$json[] = [
					'article_id' => $result['article_id'],
					'name'       => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
				];
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}