<?php
class ModelWkposWkpos extends Model {
	/**
	 * Check POS installation
	 * @return bool
	 */
	public function is_installed() {
		if(POS_FLAG != 0)
			return true;

		return false;
	}

	/**
	 * Get POS Orders totals
	 * @return int
	 */
	public function getTotalOrders() {
		$query = "SELECT COUNT(*) AS total_orders, SUM(o.total) as total_rev FROM `" . DB_PREFIX . "order` o RIGHT JOIN  `" . DB_PREFIX . "wkpos_user_orders` wo ON (wo.order_id = o.order_id) WHERE o.order_status_id > '0'";

        $result = $this->db->query($query);
        return $result->row;
	}
	/**
	 * Get POS total orders
	 * @return int
	 */
	public function getTotalReturns() {
		$query = "SELECT COUNT(*) AS total_return FROM `" . DB_PREFIX . "wkpos_return`";

        $result = $this->db->query($query);
        return $result->row['total_return'];
	}


	public function fixQuantities($outlet_id){

	    // this query will get results only if the quantity assigned to one product in one outlet greater than the
        // the total quantity for this product


        $query = "SELECT count(outlet_id) as outlet_count from " .DB_PREFIX ."wkpos_outlet " ;
        $result = $this->db->query($query);
        if($result->row['outlet_count'] == 1) {
            $query = "SELECT wkp.wkpos_products_id,p.quantity FROM `" .
                DB_PREFIX . "wkpos_products` wkp join " .DB_PREFIX .
                "product p on wkp.product_id=p.product_id where wkp.quantity > p.quantity ";

            $query .= " AND outlet_id=".$outlet_id;

            $result = $this->db->query($query);

            foreach ($result->rows as $row){

                $this->db->query('UPDATE ' . DB_PREFIX . 'wkpos_products SET quantity=' .
                    $row['quantity'] .
                    ' WHERE wkpos_products_id="' . (int)$row['wkpos_products_id'] . '"'
                    ." AND outlet_id=".$outlet_id
                );
            }
        }
    }

	/**
	 * Creates the tables for POS purpose
	 * @return null none
	 */
	public function createTables() {
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "wkpos_user` (
		  `user_id` int(11) NOT NULL AUTO_INCREMENT,
		  `outlet_id` int(11) NOT NULL,
		  `username` varchar(20) NOT NULL,
		  `password` varchar(40) NOT NULL,
		  `salt` varchar(9) NOT NULL,
		  `firstname` varchar(32) NOT NULL,
		  `lastname` varchar(32) NOT NULL,
		  `email` varchar(96) NOT NULL,
		  `image` varchar(255) NOT NULL,
		  `code` varchar(40) NOT NULL,
		  `ip` varchar(40) NOT NULL,
		  `status` tinyint(1) NOT NULL,
		  `date_added` datetime NOT NULL,
		  PRIMARY KEY (`user_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1");

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "wkpos_outlet` (
		  `outlet_id` int(11) NOT NULL AUTO_INCREMENT,
		  `name` varchar(64) NOT NULL,
		  `address` text NOT NULL,
		  `country_id` int(11) NOT NULL,
		  `zone_id` int(11) NOT NULL,
		  `status` tinyint(1) NOT NULL,
		  PRIMARY KEY (`outlet_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1");

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "wkpos_supplier` (
		  `supplier_id` int(11) NOT NULL AUTO_INCREMENT,
		  `supplier_group_id` int(11) NOT NULL,
		  `firstname` varchar(32) NOT NULL,
		  `lastname` varchar(32) NOT NULL,
		  `company` varchar(40) NOT NULL,
		  `email` varchar(96) NOT NULL,
		  `telephone` varchar(32) NOT NULL,
		  `website` varchar(255) NOT NULL,
		  `outlets` text NOT NULL,
		  `extra_info` text NOT NULL,
		  `status` tinyint(1) NOT NULL,
		  `address` varchar(128) NOT NULL,
		  `city` varchar(128) NOT NULL,
		  `postcode` varchar(10) NOT NULL,
		  `country_id` int(11) NOT NULL,
		  `zone_id` int(11) NOT NULL,
		  `date_added` datetime NOT NULL,
		  PRIMARY KEY (`supplier_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1");

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "wkpos_supplier_product` (
		  `supplier_product_id` int(11) NOT NULL AUTO_INCREMENT,
		  `supplier_id` int(11) NOT NULL,
		  `product_id` int(11) NOT NULL,
		  `min_quantity` int(11) NOT NULL,
		  `max_quantity` int(11) NOT NULL,
		  `status` tinyint(1) NOT NULL,
		  PRIMARY KEY (`supplier_product_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1");

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "wkpos_barcode` (
		  `barcode_id` int(11) NOT NULL AUTO_INCREMENT,
		  `product_id` int(11) NOT NULL,
		  `barcode` varchar(15) NOT NULL,
		  PRIMARY KEY (`barcode_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1");

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "wkpos_supplier_request` (
		  `request_id` int(11) NOT NULL AUTO_INCREMENT,
		  `user_id` int(11) NOT NULL,
		  `user_name` varchar(255) NOT NULL,
		  `comment` text NOT NULL,
		  `status` tinyint(1) NOT NULL,
		  `cancel` tinyint(1) NOT NULL,
		  `date_added` datetime NOT NULL,
		  PRIMARY KEY (`request_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1");

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "wkpos_request_info` (
		  `request_info_id` int(11) NOT NULL AUTO_INCREMENT,
		  `request_id` int(11) NOT NULL,
		  `supplier_id` int(11) NOT NULL,
		  `supplier` varchar(255) NOT NULL,
		  `product_id` int(11) NOT NULL,
		  `quantity` int(11) NOT NULL,
		  `comment` text NOT NULL,
		  `status` tinyint(1) NOT NULL,
		  `cancel` tinyint(1) NOT NULL,
		  `date_added` datetime NOT NULL,
		  PRIMARY KEY (`request_info_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1");

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "wkpos_user_orders` (
		  `user_order_id` int(11) NOT NULL AUTO_INCREMENT,
		  `order_id` int(11) NOT NULL,
		  `user_id` int(11) NOT NULL,
		  `user_name` varchar(255) NOT NULL,
		  `txn_id` int(11) NOT NULL,
		  `order_note` text NOT NULL,
		  PRIMARY KEY (`user_order_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1");

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "wkpos_products` (
		  `wkpos_products_id` int(11) NOT NULL AUTO_INCREMENT,
		  `product_id` int(11) NOT NULL,
		  `quantity` int(11) NOT NULL,
		  `status` tinyint(1) NOT NULL,
		  `outlet_id` int(11) NOT NULL,
		  PRIMARY KEY (`wkpos_products_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1");

		// POS Update code
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "wkpos_return` (
		  `wkpos_return_id` int(11) NOT NULL AUTO_INCREMENT,
		  `return_id` int(11) NOT NULL,
			`product_id` int(11) NOT NULL,
		  PRIMARY KEY (`wkpos_return_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1");

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "wkpos_credit` (
			`wkpos_credit_id` int(11) NOT NULL AUTO_INCREMENT,
			`description` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			`customer_id` int(11) NOT NULL,
			`order_id` int(11) NOT NULL,
			`amount` decimal(15,4) NOT NULL,
			`date_added` DATETIME NOT NULL,
			PRIMARY KEY (`wkpos_credit_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1");
		// Update code ended
		$barcode_path = str_replace('system/', 'wkpos/barcode/', DIR_SYSTEM);
		require_once($barcode_path . 'barcode.php');

		$products = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "product")->rows;

		foreach ($products as $product) {
			$image_name = 'wkpos' . $product['product_id'];
			$filepath = $barcode_path . 'img/' . $image_name . '.png';

			$size = 25;

			barcode( $filepath, $image_name, $size );

			$this->db->query("INSERT INTO " . DB_PREFIX . "wkpos_barcode SET product_id = '" . $product['product_id'] . "', barcode = '" . $image_name . "'");
		}
	}

	/**
	 * Deletes the existing table on uninstallation of POS
	 * @return null none
	 */
	public function deleteTables() {
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "wkpos_user`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "wkpos_outlet`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "wkpos_barcode`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "wkpos_user_orders`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "wkpos_products`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "wkpos_request_info`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "wkpos_supplier`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "wkpos_supplier_product`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "wkpos_supplier_request`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "wkpos_return`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "wkpos_credit`");
	}
}
