<?php
class ModelCatalogCustomEmailTemplates extends Model {
	public function getOtherTemplates($store_id) {
		$other_template_data = array();

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "cet_template` WHERE store_id = '" . (int)$store_id . "' AND (`code` NOT LIKE 'order.status_%' AND `code` NOT LIKE 'mail_%')");

		foreach ($query->rows as $result) {
			$other_template_data[$result['code']] = array(
				'template_id' => $result['template_id'],
				'code'        => $result['code'],
				'status'      => $result['status'],
			);
		}

		return $other_template_data;
	}

	public function getOrderStatusTemplates($store_id) {
		$order_status_template_data = array();

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "cet_template` WHERE store_id = '" . (int)$store_id . "' AND `code` LIKE 'order.status_%'");

		foreach ($query->rows as $result) {
			$order_status_template_data[$result['code']] = array(
				'template_id' => $result['template_id'],
				'code'        => $result['code'],
				'status'      => $result['status'],
			);
		}

		return $order_status_template_data;
	}

	public function getReturnStatusTemplates($store_id) {
		$return_status_template_data = array();

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "cet_template` WHERE store_id = '" . (int)$store_id . "' AND `code` LIKE 'return.status_%'");

		foreach ($query->rows as $result) {
			$return_status_template_data[$result['code']] = array(
				'template_id' => $result['template_id'],
				'code'        => $result['code'],
				'status'      => $result['status'],
			);
		}

		return $return_status_template_data;
	}

	public function getMailTemplates($store_id = null) {
		$sql = "SELECT cett.*, cetd.subject FROM `" . DB_PREFIX . "cet_template` cett LEFT JOIN `" . DB_PREFIX . "cet_description` cetd ON (cett.template_id = cetd.template_id) WHERE cetd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (isset($store_id) && !is_null($store_id)) {
			$sql .= " AND cett.store_id = '" . (int)$store_id . "'";
		}

		$sql .= " AND cett.code LIKE 'mail_%' ORDER BY cett.template_id DESC";

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getEmailTemplate($code, $store_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "cet_template` WHERE store_id = '" . (int)$store_id . "' AND `code` = '" . $this->db->escape($code) . "'");

		return $query->row;
	}

	public function getEmailTemplateDescriptions($template_id) {
		$template_description_data = array();

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "cet_description` WHERE template_id = '" . (int)$template_id . "'");

		foreach ($query->rows as $result) {
			$template_description_data[$result['language_id']] = array(
				'subject'     => $result['subject'],
				'description' => $result['description']
			);
		}

		return $template_description_data;
	}

	public function addEmailTemplate($code, $data = array()) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "cet_template` SET `code` = '" . $this->db->escape($code) . "', bcc = '" . $this->db->escape($data['bcc']) . "', product = '" . $this->db->escape($data['product']) . "', product_limit = '" . (int)$data['product_limit'] . "', store_id = '" . (int)$data['store_id'] . "', status = '" . (int)$data['status'] . "'");

		$template_id = $this->db->getLastId();

		foreach ($data['template_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "cet_description` SET template_id = '" . (int)$template_id . "', language_id = '" . (int)$language_id . "', subject = '" . $this->db->escape($value['subject']) . "', description = '" . $this->db->escape($value['description']) . "'");
		}
	}

	public function editEmailTemplate($template_id, $data = array()) {
		$this->db->query("UPDATE `" . DB_PREFIX . "cet_template` SET bcc = '" . $this->db->escape($data['bcc']) . "', product = '" . (int)$data['product'] . "', product_limit = '" . (int)$data['product_limit'] . "', status = '" . (int)$data['status'] . "' WHERE template_id = '" . (int)$template_id . "'");

		$this->db->query("DELETE FROM `" . DB_PREFIX . "cet_description` WHERE template_id = '" . (int)$template_id . "'");

		foreach ($data['template_description'] as $language_id => $value) {
			
			$params = [
				(int)$template_id,
				(int)$language_id,
				$value['subject'],
				html_entity_decode($value['description'], ENT_QUOTES, 'UTF-8') 
			];

			$this->db->execute("INSERT INTO `" . DB_PREFIX . "cet_description` SET template_id = ?, language_id = ?, subject = ?, description = ?", $params);
		}
	}

	public function getHistory($history_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "cet_history` WHERE history_id = '" . (int)$history_id . "'");

		return $query->row;
	}

	public function getHistories($data = array()) {
		$sql = "SELECT ceth.*, cett.code AS template FROM `" . DB_PREFIX . "cet_history` ceth LEFT JOIN `" . DB_PREFIX . "cet_template` cett ON (ceth.template_id = cett.template_id) WHERE cett.store_id = '" . (int)$data['filter_store_id'] . "'";

		if (!empty($data['filter_subject'])) {
			$sql .= " AND ceth.subject LIKE '%" . $this->db->escape($data['filter_subject']) . "%'";
		}

		if (isset($data['filter_template_code']) && !is_null($data['filter_template_code'])) {
			$sql .= " AND cett.code = '" . $this->db->escape($data['filter_template_code']) . "'";
		}

		if (!empty($data['filter_email'])) {
			$sql .= " AND ceth.email LIKE '" . $this->db->escape($data['filter_email']) . "%'";
		}

		if (isset($data['filter_attachment']) && !is_null($data['filter_attachment'])) {
			if ($data['filter_attachment'] == 2) {
				$sql .= " AND (SELECT COUNT(1) FROM `" . DB_PREFIX . "cet_attachment` ceta WHERE ceth.history_id = ceta.history_id) > 0";
			} elseif ($data['filter_attachment'] == 1) {
				$sql .= " AND (SELECT COUNT(1) FROM `" . DB_PREFIX . "cet_attachment` ceta WHERE ceth.history_id = ceta.history_id) = 0";
			}
		}

		if (!empty($data['filter_date_added'])) {
			$sql .= " AND DATE(ceth.date_added) = '" . $this->db->escape($data['filter_date_added']) . "'";
		}

		$sql .= " ORDER BY ceth.date_added DESC";

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function dtHistoryHandler($start, $length, $data, $orderColumn, $orderType){

        $query = "SELECT ceth.*, cett.code AS template FROM `" . DB_PREFIX . "cet_history` ceth LEFT JOIN `" . DB_PREFIX . "cet_template` cett ON (ceth.template_id = cett.template_id) WHERE cett.store_id = '" . (int)$data['filter_store_id'] . "'";
        //$query = ;

        $total = $this->db->query($query)->num_rows;
        $where = "";
        if (!empty($data['filter_subject'])) {
            $query .= " or ceth.subject LIKE '%" . $this->db->escape($data['filter_subject']) . "%'";
        }

        if (isset($data['filter_template_code']) && !is_null($data['filter_template_code'])) {
            $query .= " or cett.code = '" . $this->db->escape($data['filter_template_code']) . "'";
        }

        if (!empty($data['filter_email'])) {
            $query .= " or ceth.email LIKE '" . $this->db->escape($data['filter_email']) . "%'";
        }

        if (isset($data['filter_attachment']) && !is_null($data['filter_attachment'])) {
            if ($data['filter_attachment'] == 2) {
                $query .= " or (SELECT COUNT(1) FROM `" . DB_PREFIX . "cet_attachment` ceta WHERE ceth.history_id = ceta.history_id) > 0";
            } elseif ($data['filter_attachment'] == 1) {
                $query .= " or (SELECT COUNT(1) FROM `" . DB_PREFIX . "cet_attachment` ceta WHERE ceth.history_id = ceta.history_id) = 0";
            }
        }

        if (!empty($data['filter_date_added'])) {
            $query .= " or DATE(ceth.date_added) = '" . $this->db->escape($data['filter_date_added']) . "'";
        }

        $totalFiltered = $this->db->query($query)->num_rows;
        $query .= " ORDER by {$orderColumn} {$orderType}";

        if($length != -1) {
            $query .= " LIMIT " . $start . ", " . $length;
        }

        $results = $this->db->query($query)->rows;

        foreach ($results as $key => $result) {
            $attachment = array();

            $attachments = $this->getAttachments($result['history_id']);

            if ($attachments) {
                foreach ($attachments as $file) {
                    $attachment[] = array(
                        'file' => $file['file'],
                        'href' => $this->url->link('module/custom_email_templates/download', 'token=' . $this->session->data['token'] . '&attachment_id=' . $file['attachment_id'], 'SSL')
                    );
                }
            }
            $results[$key]['attachment']=$attachment;
            $results[$key]['track']= ($result['track']) ? $this->language->get('text_yes') : $this->language->get('text_no');
            $results[$key]['date_added']= date($this->language->get('date_format_short'), strtotime($result['date_added']));
            //$results[$key]['date_opened'] = ($result['date_opened'] != '0000-00-00 00:00:00') ? date($this->language->get('date_format_short'), strtotime($result['date_opened'])) . '<br />IP: ' . $result['ip'] : 'N/A';

            $date_opened = $result['date_opened'];
            $results[$key]['date_opened'] = 'N/A';
            if (($date_opened != '0000-00-00 00:00:00' && $date_opened != null)) {
                $results[$key]['date_opened'] = date(
                    $this->language->get('date_format_short'), strtotime($result['date_opened'])
                ) . '<br />IP: ' . $result['ip'];
            }
        }

        $data = array (
            'data' => $results,
            'total' => $total,
            'totalFiltered' => $totalFiltered
        );
        return $data;

    }
	public function getTotalHistories($data = array()) {
		$sql = "SELECT COUNT(ceth.history_id) AS total FROM `" . DB_PREFIX . "cet_history` ceth LEFT JOIN `" . DB_PREFIX . "cet_template` cett ON (ceth.template_id = cett.template_id) WHERE cett.store_id = '" . (int)$data['filter_store_id'] . "'";

		if (!empty($data['filter_subject'])) {
			$sql .= " AND ceth.subject LIKE '%" . $this->db->escape($data['filter_subject']) . "%'";
		}

		if (isset($data['filter_template_code']) && !is_null($data['filter_template_code'])) {
			$sql .= " AND cett.code = '" . $this->db->escape($data['filter_template_code']) . "'";
		}

		if (!empty($data['filter_email'])) {
			$sql .= " AND ceth.email LIKE '" . $this->db->escape($data['filter_email']) . "%'";
		}

		if (isset($data['filter_attachment']) && !is_null($data['filter_attachment'])) {
			if ($data['filter_attachment'] == 2) {
				$sql .= " AND (SELECT COUNT(1) FROM `" . DB_PREFIX . "cet_attachment` ceta WHERE ceth.history_id = ceta.history_id) > 0";
			} elseif ($data['filter_attachment'] == 1) {
				$sql .= " AND (SELECT COUNT(1) FROM `" . DB_PREFIX . "cet_attachment` ceta WHERE ceth.history_id = ceta.history_id) = 0";
			}
		}

		if (!empty($data['filter_date_added'])) {
			$sql .= " AND DATE(ceth.date_added) = '" . $this->db->escape($data['filter_date_added']) . "'";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function deleteHistory($history_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "cet_history` WHERE history_id = '" . (int)$history_id . "'");
	}

	public function deleteHistories ($store_id) {
		$this->db->query("DELETE ceth FROM `" . DB_PREFIX . "cet_history` ceth LEFT JOIN `" . DB_PREFIX . "cet_template` cett ON (ceth.template_id = cett.template_id) WHERE cett.store_id = '" . (int)$store_id . "'");
	}

	public function getAttachments($history_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "cet_attachment` WHERE history_id = '" . (int)$history_id . "'");

		return $query->rows;
	}

	public function getAttachment($attachment_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "cet_attachment` WHERE attachment_id = '" . (int)$attachment_id . "'");

		return $query->row;
	}

	public function getMaxIdMailTemplates() {
		$id = 0;

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "cet_template` WHERE `code` LIKE 'mail_%' ORDER BY template_id DESC");

		if ($query->row) {
			$id = str_replace('mail_', '', $query->row['code']);
		}

		return ((int)$id + 1);
	}

	public function deleteHistoryOrderById($history_id) {
	  	$this->db->query("DELETE FROM `" . DB_PREFIX . "order_history` WHERE order_history_id = '" . (int)$history_id . "'");
	}
}
