<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

namespace Opencart\Catalog\Model\Extension\ocStore\Blog;

if (!defined('VERSION')) {
	header('Refresh: 1; URL=/');
	exit('ЗАПРЫШЧАЮ!');
}

class Review extends \Opencart\System\Engine\Model {
	public function addReview(int $article_id, array $data): int {
		$this->db->query("INSERT INTO " . DB_PREFIX . "review_article SET author = '" . $this->db->escape($data['name']) . "', customer_id = '" . (int)$this->customer->getId() . "', article_id = '" . (int)$article_id . "', text = '" . $this->db->escape($data['text']) . "', rating = '" . (int)$data['rating'] . "', date_added = NOW()");

		$review_id = $this->db->getLastId();

		if ($this->config->get('configblog_review_mail')) {
			$this->load->model('blog/article');
			
			$article_info = $this->model_blog_article->getArticle($article_id);

			if ($article_info) {
				$store_name = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');

				$subject = sprintf($this->language->get('text_subject'), $store_name);

				$message = $this->load->language('blog/mail/review');
				$message['text_product'] = $this->language->get('text_article');
				$message['product'] = html_entity_decode($article_info['name'], ENT_QUOTES, 'UTF-8');
				$message['reviewer'] = html_entity_decode($data['name'], ENT_QUOTES, 'UTF-8');
				$message['rating'] = (int)$data['rating'];
				$message['text'] = nl2br($data['text']);
				$message['store'] = $store_name;
				$message['store_url'] = $this->config->get('config_url');

				$mail = new \Opencart\System\Library\Mail($this->config->get('config_mail_engine'));
				$mail->parameter = $this->config->get('config_mail_parameter');
				$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
				$mail->smtp_username = $this->config->get('config_mail_smtp_username');
				$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
				$mail->smtp_port = $this->config->get('config_mail_smtp_port');
				$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

				$mail->setTo($this->config->get('config_email'));
				$mail->setFrom($this->config->get('config_email'));
				$mail->setSender($store_name);
				$mail->setSubject($subject);
				$mail->setHtml($this->load->view('mail/review', $message));
				$mail->send();

				// Send to additional alert emails
				$emails = explode(',', (string)$this->config->get('config_mail_alert_email'));

				foreach ($emails as $email) {
					if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
						$mail->setTo(trim($email));
						$mail->send();
					}
				}
			}
		}

		return $review_id;
	}

	public function getReviewsByArticleId(int $article_id, int $start = 0, int $limit = 20): array {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 20;
		}

		$query = $this->db->query("SELECT r.review_article_id, r.author, r.rating, r.text, p.article_id, pd.name, p.image, r.date_added FROM " . DB_PREFIX . "review_article r LEFT JOIN " . DB_PREFIX . "article p ON (r.article_id = p.article_id) LEFT JOIN " . DB_PREFIX . "article_description pd ON (p.article_id = pd.article_id) WHERE p.article_id = '" . (int)$article_id . "' AND p.date_available <= NOW() AND p.status = '1' AND r.status = '1' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY r.date_added DESC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}

	public function getTotalReviewsByArticleId(int $article_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review_article r LEFT JOIN " . DB_PREFIX . "article p ON (r.article_id = p.article_id) LEFT JOIN " . DB_PREFIX . "article_description pd ON (p.article_id = pd.article_id) WHERE p.article_id = '" . (int)$article_id . "' AND p.date_available <= NOW() AND p.status = '1' AND r.status = '1' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row['total'];
	}
}