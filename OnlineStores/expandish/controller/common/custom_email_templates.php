<?php
class ControllerCommonCustomEmailTemplates extends Controller {
	public function cron() {
		if ($this->config->get('custom_email_templates_status') && $this->config->get('custom_email_templates_cron_invoice_status')) {
			$max_loop = 6;
			$i = 0;
			$sql = "";

			include_once(DIR_SYSTEM . 'library/custom_email_templates.php');

			$cet = new CustomEmailTemplates($this->registry);

			if ($this->config->get('custom_email_templates_generate_invoice_number') && is_array($this->config->get('custom_email_templates_generate_invoice_number'))) {
				foreach ((array)$this->config->get('custom_email_templates_generate_invoice_number') as $order_status_id) {
					$sql .= "order_status_id = '" . (int)$order_status_id . "' OR ";
				}

				$sql = " AND (" . rtrim($sql, ' OR ') . ")";

				$orders = $this->db->query("SELECT order_id, invoice_no, invoice_prefix FROM `" . DB_PREFIX . "order` WHERE invoice_sent = '0' AND (invoice_no = '0' OR invoice_no = '')" . $sql);

				foreach ($orders->rows as $order_info) {
					if (!$order_info['invoice_no']) {
						$query = $this->db->query("SELECT MAX(invoice_no) AS invoice_no FROM `" . DB_PREFIX . "order` WHERE invoice_prefix = '" . $$this->db->escape($order_info['invoice_prefix']) . "'");

						if ($query->row['invoice_no']) {
							$invoice_no = (int)$query->row['invoice_no'] + 1;
						} else {
							$invoice_no = 1;
						}

						$this->db->query("UPDATE `" . DB_PREFIX . "order` SET invoice_no = '" . (int)$invoice_no . "', invoice_prefix = '" . $this->db->escape($order_info['invoice_prefix']) . "' WHERE order_id = '" . (int)$order_info['order_id'] . "'");
					}
				}
			}

			$orders = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order` WHERE invoice_sent = '0' AND invoice_no > '0' AND order_status_id > 0");

			foreach ($orders->rows as $order_info) {
				$cet_result = $cet->getEmailTemplate(array('type' => 'catalog', 'class' => __CLASS__, 'function' => __FUNCTION__, 'vars' => get_defined_vars()));

				if ($cet_result) {
					$mail2 = new Mail();
					$mail2->protocol = $this->config->get('config_mail_protocol');
					$mail2->parameter = $this->config->get('config_mail_parameter');

					if (version_compare(VERSION, '2.0.2') < 0) {
						$mail2->hostname = $this->config->get('config_smtp_host');
						$mail2->username = $this->config->get('config_smtp_username');
						$mail2->password = $this->config->get('config_smtp_password');
						$mail2->port = $this->config->get('config_smtp_port');
						$mail2->timeout = $this->config->get('config_smtp_timeout');
					} else {
						$mail2->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
						$mail2->smtp_username = $this->config->get('config_mail_smtp_username');
						$mail2->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
						$mail2->smtp_port = $this->config->get('config_mail_smtp_port');
						$mail2->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
					}

					$mail2->setTo($order_info['email']);
					$mail2->setFrom((!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email')));
					$mail2->setSender(html_entity_decode($order_info['store_name'], ENT_QUOTES, 'UTF-8'));
					$mail2->setSubject(html_entity_decode($cet_result['subject'], ENT_QUOTES, 'UTF-8'));
					$mail2->setHtml($cet_result['message']);
					$mail2->setBccEmails($cet_result['bcc_emails']);
					$mail2->addAttachment($cet->invoice($order_info));
					$mail2->send();
					$mail2->sendBccEmails();

					sleep(2);

					++$i;

					if ($i >= $max_loop)
						return;
				}
			}

			echo"Success! Sent invoices: " . $i;
			exit();
		}

		echo'There is no invoices to send!';
		exit();
	}

	public function tracking() {
		if (function_exists("date_default_timezone_set") && function_exists("date_default_timezone_get")) {
			date_default_timezone_set(date_default_timezone_get());
		}

		if (!isset($this->request->get['_h']) || $this->request->get['_h'] == '') {
			return;
		}

		if (!isset($this->request->get['_c']) || strlen($this->request->get['_c']) < 32) {
			return;
		}

		$history_info = $this->db->query("SELECT * FROM `" . DB_PREFIX . "cet_history` WHERE track = '1' AND history_id = '" . (int)$this->request->get['_h'] . "'");

		if ($history_info->num_rows) {
			$template_info = $this->db->query("SELECT * FROM `" . DB_PREFIX . "cet_template` WHERE template_id = '" . (int)$history_info->row['template_id'] . "'");

			if (md5($this->request->get['_h'] . $history_info->row['email'] . $template_info->row['code'] . $history_info->row['template_id']) == $this->request->get['_c']) {
				$ip = '';

				if (isset($_SERVER)) {
					if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
					elseif (isset($_SERVER['HTTP_CLIENT_IP']))   $ip = $_SERVER['HTTP_CLIENT_IP'];
					else                                         $ip = $_SERVER['REMOTE_ADDR'];
				} else {
					if (getenv('HTTP_X_FORWARDED_FOR')) $ip = getenv('HTTP_X_FORWARDED_FOR');
					elseif (getenv('HTTP_CLIENT_IP'))   $ip = getenv('HTTP_CLIENT_IP');
					else                                $ip = getenv('REMOTE_ADDR');
				}

				$this->db->query("UPDATE `" . DB_PREFIX . "cet_history` SET date_opened = NOW(), ip = '" . $this->db->escape($ip) . "' WHERE history_id = '" . (int)$this->request->get['_h'] . "'");
			}
		}

		$filename = DIR_IMAGE . $this->config->get('config_logo');

		header('Content-Type: image/' . substr(strrchr($filename, '.'), 1));
		header('Pragma: public');
    	header('Expires: 0');
    	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    	header('Cache-Control: private', false);
    	header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
    	header('Content-Transfer-Encoding: binary');
    	header('Content-Length: ' . filesize($filename));

		readfile($filename);

		exit;
	}
}