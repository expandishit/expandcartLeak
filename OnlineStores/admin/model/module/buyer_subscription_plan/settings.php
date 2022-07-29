<?php

class ModelModuleBuyerSubscriptionPlanSettings extends Model {

	public function install(){
		//Add new columns
		if( !$this->db->check(['customer' => ['buyer_subscription_id']], 'column') ){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "customer` ADD buyer_subscription_id INT(11) DEFAULT NULL;");
		}

		//Add new 4 tables
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "buyer_subscription` (
			  `subscription_id` int(11) NOT NULL AUTO_INCREMENT,
			  `status` TINYINT(1) NOT NULL,
			  `price` decimal(15,4) NOT NULL DEFAULT '0.0000',
			  `validity_period` int(11) NOT NULL DEFAULT 1,
			  `validity_period_unit` ENUM('day', 'month', 'year') NOT NULL DEFAULT 'year',
			  PRIMARY KEY (`subscription_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "buyer_subscription_translations` (
			  `subscription_id` int(11) NOT NULL,
			  `language_id` int(11) NOT NULL,
			  `title` VARCHAR(255) NOT NULL,
			  `description` TEXT NULL,
			  PRIMARY KEY (`subscription_id`, `language_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "buyer_subscription_payments_log` (
			  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
			  `buyer_id` int(11) NOT NULL,
			  `subscription_id` int(11) NOT NULL,

			  `amount` DECIMAL(15,4) NOT NULL,
			  `payment_datetime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

			  `payment_data` TEXT NOT NULL DEFAULT '',
			  `currency_id` int(11) NOT NULL,
			  `currency_code` VARCHAR(3) NOT NULL,
			  `order_id`  int(11) NOT NULL,
			  PRIMARY KEY (`payment_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;");


		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "buyer_subscription_coupon` (
			  `subscription_id` int(11) NOT NULL,
			  `coupon_id` int(11) NOT NULL ,			 
			  PRIMARY KEY (`subscription_id`, `coupon_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;");


		$this->load->model('setting/extension');		
		$this->model_setting_extension->install('total', 'buyer_subscription_plan');
	}

	public function uninstall(){
		if( $this->db->check(['customer' => ['buyer_subscription_id']], 'column') ){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "customer` DROP COLUMN buyer_subscription_id;");
		}

		$this->db->query("
			DROP TABLE IF EXISTS `buyer_subscription`,
			`buyer_subscription_translations`,
			`buyer_subscription_payments_log` , 
			`buyer_subscription_coupon`;
			");

	    $this->load->model('setting/setting');
	    $this->model_setting_setting->deleteSetting('buyer_subscription_plan');
		$this->model_setting_extension->uninstall('total', 'buyer_subscription_plan');
	}
}
