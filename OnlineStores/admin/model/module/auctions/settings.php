<?php

class ModelModuleAuctionsSettings extends Model {

	public function install(){
		//Add new tables
        //You cannot add multiple auctions for one product.
        //FOR ADDED BY ADMIN AUCTIONS
        //enabled, disabled
        //Pending, Started, closed, canceled
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "auction` (
			  `auction_id` int(11) NOT NULL AUTO_INCREMENT,
			  `product_id` int(11) NOT NULL UNIQUE,                          
			  `seller_id` int(11) NOT NULL DEFAULT 0,                        
			  `auction_status` TINYINT(1) NOT NULL DEFAULT 0,                
			  `bidding_status` varchar(100) NOT NULL DEFAULT 'Pending',      
			  `starting_bid_price`  decimal(15,4) NOT NULL DEFAULT '1.0000',
			  `start_datetime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  `close_datetime` DATETIME NOT NULL,
			  
			  `min_deposit` decimal(15,4) NOT NULL DEFAULT '0.0000',
			  
			  `winning_bidder_id` int(11) NULL,			  
			  `current_bid`  decimal(15,4) NULL,
			  
			  `increment`  decimal(15,4) DEFAULT '0.25',  			  
  			  `min_quantity` int(11) NOT NULL DEFAULT 1,
  			  `max_quantity` int(11) NOT NULL DEFAULT 1,
  			  `purchase_valid_days` int(2) NOT NULL DEFAULT 2,   

			  
			  PRIMARY KEY (`auction_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "auction_bid` (
			  `bid_id` int(11) NOT NULL AUTO_INCREMENT,
			  `amount` decimal(15,4) NOT NULL,
			  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  `bidder_id` int(11) NOT NULL,
			  `auction_id` int(11) NOT NULL,
			  PRIMARY KEY (`bid_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "auction_deposit_payments_log` (
			  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
			  `customer_id` int(11) NOT NULL,
			  `auction_id` int(11) NOT NULL,
			  `amount` DECIMAL(15,4) NOT NULL,
			  `currency_code` VARCHAR(3) NOT NULL,
			  `paid_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  `order_id`  int(11) NULL,
			  PRIMARY KEY (`payment_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;");


		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "auction_orders_history` (
			  `auction_id`  int(11) NOT NULL,
			  `order_id`  int(11) NOT NULL,
			  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

			  PRIMARY KEY (`auction_id`, `order_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
	}

	public function uninstall(){
		$this->db->query("
			DROP TABLE IF EXISTS `auction`,
			`auction_bid`,
			`auction_deposit_payments_log` , 
			`auction_orders_history`;
			");
	}
}
