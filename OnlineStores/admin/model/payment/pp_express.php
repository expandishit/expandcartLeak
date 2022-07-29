<?php

class ModelPaymentPPExpress extends Model {

    public function install() {
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "paypal_order` (
			  `paypal_order_id` int(11) NOT NULL AUTO_INCREMENT,
			  `order_id` int(11) NOT NULL,
			  `date_added` DATETIME NOT NULL,
			  `date_modified` DATETIME NOT NULL,
			  `capture_status` ENUM('Complete','NotComplete') DEFAULT NULL,
			  `currency_code` CHAR(3) NOT NULL,
			  `authorization_id` VARCHAR(30) NOT NULL,
			  `total` DECIMAL( 10, 2 ) NOT NULL,
			  PRIMARY KEY (`paypal_order_id`)
			) ENGINE=InnoDB DEFAULT COLLATE=utf8_general_ci
		");

		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "paypal_order_transaction` (
			  `paypal_order_transaction_id` int(11) NOT NULL AUTO_INCREMENT,
			  `paypal_order_id` int(11) NOT NULL,
			  `transaction_id` CHAR(20) NOT NULL,
			  `parent_id` CHAR(20) NOT NULL,
			  `date_added` DATETIME NOT NULL,
			  `note` VARCHAR(255) NOT NULL,
			  `msgsubid` CHAR(38) NOT NULL,
			  `receipt_id` CHAR(20) NOT NULL,
			  `payment_type` ENUM('none','echeck','instant', 'refund', 'void') DEFAULT NULL,
			  `payment_status` CHAR(20) NOT NULL,
			  `pending_reason` CHAR(50) NOT NULL,
			  `transaction_entity` CHAR(50) NOT NULL,
			  `amount` DECIMAL( 10, 2 ) NOT NULL,
			  `debug_data` TEXT NOT NULL,
			  `call_data` TEXT NOT NULL,
			  PRIMARY KEY (`paypal_order_transaction_id`)
			) ENGINE=InnoDB DEFAULT COLLATE=utf8_general_ci
		");
	}
	
    public function uninstall() {
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "paypal_order_transaction`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "paypal_order`");
		$this->load->model('setting/event');
		$this->model_setting_event->deleteEventByCode('extension_pp_express_checkout_js');
    }
}
