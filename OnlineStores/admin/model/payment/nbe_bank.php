<?php

class ModelPaymentNbeBank extends Model
{
    public function __install()
    {
        //Check if Pending Capture status exists
        $isSetPendingCapture = $this->db->query("
			SELECT COUNT(*) as total FROM " . DB_PREFIX . "order_status WHERE name = 'Pending Capture';
		");

        if (!$isSetPendingCapture->rows[0]['total']) {
            //get max id of order status table
            $max_order_status_id = $this->db->query("
                SELECT MAX(`order_status_id`) AS max_order_status_id FROM  `" . DB_PREFIX . "order_status`
            ");

            foreach ($max_order_status_id->rows as $id) {
                $new_order_status_id = $id['max_order_status_id'] + 1;
            }

            $lang_ids = $this->db->query("
                SELECT DISTINCT `language_id` FROM  `" . DB_PREFIX . "order_status`
            ");

            foreach ($lang_ids->rows as $id) {
                $this->db->query("INSERT INTO `" . DB_PREFIX . "order_status` SET `order_status_id` = '" . (int)$new_order_status_id . "', `language_id` = '" . (int)$id['language_id'] . "', `name` = 'Pending Capture'");
            }
        }

        $this->db->query(" 
               CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "smeonline_order_transaction` (
			  `smeonline_order_transaction_id` int(11) NOT NULL AUTO_INCREMENT,
			  `smeonline_order_id` int(11) NOT NULL,
			  `transaction_id` CHAR(20) NOT NULL,
			  `parent_transaction_id` CHAR(20) NOT NULL,
			  `created` DATETIME NOT NULL,
			  `note` VARCHAR(255) NOT NULL,
			  `payment_type` ENUM('none','payment','preauth', 'capture', 'refund') DEFAULT NULL,
			  `payment_status` CHAR(20) NOT NULL,
			  `currency_code` CHAR(3) NOT NULL,
			  `amount` DECIMAL( 15, 3 ) NOT NULL,
			  PRIMARY KEY (`smeonline_order_transaction_id`)
			) ENGINE=InnoDB DEFAULT COLLATE=utf8_general_ci;");

        $this->cache->delete('order_status');
    }

    public function __uninstall()
    {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `group` = 'smeonline_browser' ");
    }

    public function addTransaction($transaction_data, $txnNumber, $amount, $currency, $request_data = array())
    {
        if ($transaction_data->TxnResp->ResponseCode == '0') {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "smeonline_order_transaction` SET `smeonline_order_id` = '" . (int)($transaction_data->TxnResp->Crn1) . "', `transaction_id` = '" . $this->db->escape($transaction_data->TxnResp->TxnNumber) . "', `parent_transaction_id` = '" . $this->db->escape($txnNumber) . "', `created` = NOW(), `note` = '" . $this->db->escape($transaction_data->TxnResp->ResponseText) . "', `payment_type` = '" . $this->db->escape($transaction_data->TxnResp->Action) . "', `payment_status` = '" . $this->db->escape('success') . "', `currency_code` = '" . $this->db->escape($currency) . "', `amount` = '" . (double)($amount) . "'");
        } else {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "smeonline_order_transaction` SET `smeonline_order_id` = '" . (int)($transaction_data->TxnResp->Crn1) . "', `transaction_id` = '" . $this->db->escape($transaction_data->TxnResp->TxnNumber) . "', `parent_transaction_id` = '" . $this->db->escape($txnNumber) . "', `created` = NOW(), `note` = '" . $this->db->escape($transaction_data->TxnResp->ResponseText) . "', `payment_type` = '" . $this->db->escape($transaction_data->TxnResp->Action) . "', `payment_status` = '" . $this->db->escape('failure') . "', `currency_code` = '" . $this->db->escape($currency) . "', `amount` = '" . (double)($amount) . "'");
        }

        $smeonline_order_transaction_id = $this->db->getLastId();

        if ($request_data) {
            $serialized_data = serialize($request_data);

            $this->db->query("
				UPDATE " . DB_PREFIX . "smeonline_order_transaction
				SET call_data = '" . $this->db->escape($serialized_data) . "'
				WHERE smeonline_order_transaction_id = " . (int)$smeonline_order_transaction_id . "
				LIMIT 1
			");
        }

        return $smeonline_order_transaction_id;
    }

    public function getTransaction($smeonline_order_id, $payment_type, $status = 'success')
    {
        $qry = $this->db->query("SELECT `ot`.*, (SELECT count(`ot2`.`smeonline_order_id`) FROM `" . DB_PREFIX . "smeonline_order_transaction` `ot2` WHERE `ot2`.`parent_transaction_id` = `ot`.`transaction_id` ) AS `children` FROM `" . DB_PREFIX . "smeonline_order_transaction` `ot` WHERE `smeonline_order_id` = '" . (int)$smeonline_order_id . "' AND `payment_type` = '" . $this->db->escape($payment_type) . "' AND `payment_status` = '" . $this->db->escape($status) . "'");

        if ($qry->num_rows) {
            return $qry->rows[0];
        } else {
            return false;
        }
    }

    public function getRefundTotal($smeonline_order_id)
    {
        $qry = $this->db->query("SELECT SUM(`amount`) AS `total` FROM `" . DB_PREFIX . "smeonline_order_transaction` WHERE `payment_type`='refund' AND `smeonline_order_id` = " . (int)$smeonline_order_id . " AND `payment_status` = 'success'");
        if ($qry->num_rows) {
            if ($qry->rows[0]['total']) {
                return $qry->rows[0]['total'];
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    public function getPaymentCurrenCode($smeonline_order_id)
    {
        $qry = $this->db->query("SELECT `currency_code` FROM `" . DB_PREFIX . "smeonline_order_transaction` WHERE `smeonline_order_id` = " . (int)$smeonline_order_id);
        if ($qry->num_rows) {
            if ($qry->rows[0]) {
                return $qry->rows[0]['currency_code'];
            }
        } else {
            return 0;
        }
    }

    public function getMaxOrderStatusId()
    {
        $new_order_status_id = '';

        $max_order_status_id = $this->db->query("
            SELECT MAX(`order_status_id`) AS max_order_status_id FROM  `" . DB_PREFIX . "order_status`
        ");

        foreach ($max_order_status_id->rows as $id) {
            $new_order_status_id = $id['max_order_status_id'];
        }

        return $new_order_status_id;
    }
}