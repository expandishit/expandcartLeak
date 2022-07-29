<?php
class ModelMultisellerInstall extends Model {
	public function __construct($registry) {
		parent::__construct($registry);
		$this->load->model('localisation/language');
	}
	
	public function createSchema() {
		$this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ms_db_schema` (
		`schema_change_id` int(11) NOT NULL AUTO_INCREMENT,
		`major` TINYINT NOT NULL,
		`minor` TINYINT NOT NULL,
		`build` TINYINT NOT NULL,
		`revision` SMALLINT NOT NULL,
		`date_applied` DATETIME NOT NULL,
		PRIMARY KEY (`schema_change_id`)) default CHARSET=utf8");
	
		$this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ms_commission` (
		`commission_id` int(11) NOT NULL AUTO_INCREMENT,
		PRIMARY KEY (`commission_id`)) default CHARSET=utf8");
	
		$this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ms_commission_rate` (
		`rate_id` int(11) NOT NULL AUTO_INCREMENT,
		`rate_type` int(11) NOT NULL,
		`commission_id` int(11) NOT NULL,
		`commission_type` ENUM('general' , 'price_list') DEFAULT 'general', 
		`flat` DECIMAL(15,4),
		`percent` DECIMAL(15,2),
		`payment_method` TINYINT DEFAULT NULL,
		`price_list_id` int(11) NOT NULL,
		PRIMARY KEY (`rate_id`)) default CHARSET=utf8");
	

		//EC-22299 customization (sale commission per category)
        $this->db->query("set foreign_key_checks=0");
		$this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ms_commission_price_list_items` (
		`item_id` int(11) NOT NULL AUTO_INCREMENT,
		`category_id` int(11) NOT NULL,
		`value_type` TINYINT DEFAULT 1,     
		`value` DECIMAL(15,4),
		`price_list_id` int(11) NOT NULL,
		FOREIGN KEY (price_list_id) REFERENCES ms_commission_price_list (price_list_id) ON DELETE CASCADE,
		PRIMARY KEY (`item_id`)) default CHARSET=utf8");
	
		$this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ms_commission_price_list` (
		`price_list_id` int(11) NOT NULL AUTO_INCREMENT,
		PRIMARY KEY (`price_list_id`)) default CHARSET=utf8");


		$this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ms_seller_group` (
		`seller_group_id` int(11) NOT NULL AUTO_INCREMENT,
		`commission_id` int(11) DEFAULT NULL,
		`product_period` int(5) DEFAULT 0,
		`product_quantity` int(5) DEFAULT 0,
		PRIMARY KEY (`seller_group_id`)) default CHARSET=utf8");
	
		$this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ms_seller_group_description` (
		`seller_group_description_id` int(11) NOT NULL AUTO_INCREMENT,
		`seller_group_id` int(11) NOT NULL,
		`name` VARCHAR(32) NOT NULL DEFAULT '',
		`description` TEXT NOT NULL DEFAULT '',
		`language_id` int(11) DEFAULT NULL,
		PRIMARY KEY (`seller_group_description_id`)) default CHARSET=utf8");
	
		$this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ms_product` (
		`product_id` int(11) NOT NULL,
		`seller_id` int(11) DEFAULT NULL,
		`number_sold` int(11) NOT NULL DEFAULT '0',
		`product_status` TINYINT NOT NULL,
		`product_approved` TINYINT NOT NULL,
		`list_until` DATE DEFAULT NULL,
		`product_file` VARCHAR(200) NOT NULL, 
		PRIMARY KEY (`product_id`)) default CHARSET=utf8");
	
		$this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ms_seller` (
		`seller_id` int(11) NOT NULL AUTO_INCREMENT,
		`nickname` VARCHAR(32) NOT NULL DEFAULT '',
		`slug` VARCHAR(32) NOT NULL DEFAULT '',
		`mobile` VARCHAR(32) NOT NULL DEFAULT '',
		`company` VARCHAR(32) NOT NULL DEFAULT '',
		`website` VARCHAR(2083) NOT NULL DEFAULT '',
		`tax_card` VARCHAR(255) NOT NULL DEFAULT '',
		`commercial_reg` VARCHAR(255) NOT NULL DEFAULT '',
		`rec_exp_date` DATE NULL DEFAULT NULL,
		`license_num` VARCHAR(255) NOT NULL DEFAULT '',
		`lcn_exp_date` DATE NULL DEFAULT NULL,
		`personal_id` VARCHAR(255) NOT NULL DEFAULT '',
		`description` TEXT NOT NULL DEFAULT '',
		`country_id` INT(11) NOT NULL DEFAULT '0',
		`zone_id` INT(11) NOT NULL DEFAULT '0',
		`avatar` VARCHAR(255) DEFAULT NULL,
		`image_id` VARCHAR(255) DEFAULT NULL,
		`commercial_image` VARCHAR(255) DEFAULT NULL,
		`license_image` VARCHAR(255) DEFAULT NULL,
		`tax_image` VARCHAR(255) DEFAULT NULL,
		`paypal` VARCHAR(255) DEFAULT NULL,
		`bank_name` VARCHAR(255) DEFAULT NULL,
		`bank_iban` VARCHAR(255) DEFAULT NULL,
		`bank_transfer` TEXT DEFAULT NULL,
		`date_created` DATETIME NOT NULL,
		`seller_status` TINYINT NOT NULL,
		`seller_approved` TINYINT NOT NULL,
		`subscription_plan` INT(11) NOT NULL,
		`is_recurring` SMALLINT(1) NOT NULL DEFAULT 0,
		`minimum_order` FLOAT(10,2) NULL,
		`view_minimum_alert` TINYINT(1) NULL DEFAULT '1',
		`payment_methods` VARCHAR(255) NOT NULL DEFAULT '',
		`product_validation` tinyint(4) NOT NULL DEFAULT '1',
		`products_state` VARCHAR(1) NOT NULL DEFAULT '1',
		`seller_group` int(11) NOT NULL DEFAULT '1',
		`commission_id` int(11) DEFAULT NULL,
		`custom_fields` TEXT NULL,
		`sellerAffiliateReferral` varchar(64) DEFAULT NULL,
		`seller_location` text DEFAULT NULL,
		PRIMARY KEY (`seller_id`)) default CHARSET=utf8");
	
		$this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ms_balance` (
		`balance_id` int(11) NOT NULL AUTO_INCREMENT,
		`seller_id` int(11) NOT NULL,
		`order_id` int(11) DEFAULT NULL,
		`product_id` int(11) DEFAULT NULL,
		`withdrawal_id` int(11) DEFAULT NULL,
		`balance_type` int(11) DEFAULT NULL,
		`amount` DECIMAL(15,4) NOT NULL,
		`balance` DECIMAL(15,4) NOT NULL,
		`description` TEXT NOT NULL DEFAULT '',
		`date_created` DATETIME NOT NULL,
		`date_modified` DATETIME DEFAULT NULL,
		PRIMARY KEY (`balance_id`)) default CHARSET=utf8");
	
		$this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ms_order_product_data` (
		`order_product_data_id` int(11) NOT NULL AUTO_INCREMENT,
		`order_id` int(11) NOT NULL,
		`product_id` int(11) NOT NULL,
		`seller_id` int(11) DEFAULT NULL,
		`store_commission_flat` DECIMAL(15,4) NOT NULL,
		`store_commission_pct` DECIMAL(15,4) NOT NULL,
		`seller_net_amt` DECIMAL(15,4) NOT NULL,
		PRIMARY KEY (`order_product_data_id`)) default CHARSET=utf8");
	
		// ms_criteria - criterias table
		$this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ms_criteria` (
		`criteria_id` int(11) NOT NULL AUTO_INCREMENT,
		`criteria_type` TINYINT NOT NULL,
		`range_id` int(11) NOT NULL,
		PRIMARY KEY (`criteria_id`)) default CHARSET=utf8");
	
		// ms_range_int - int criteria range table
		$this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ms_range_int` (
		`range_id` int(11) NOT NULL AUTO_INCREMENT,
		`from` int(11) NOT NULL,
		`to` int(11) NOT NULL,
		PRIMARY KEY (`range_id`)) default CHARSET=utf8");
	
		// ms_range_decimal - decimal criteria range table
		$this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ms_range_decimal` (
		`range_id` int(11) NOT NULL AUTO_INCREMENT,
		`from` DECIMAL(15,4) NOT NULL,
		`to` DECIMAL(15,4) NOT NULL,
		PRIMARY KEY (`range_id`)) default CHARSET=utf8");
	
		// ms_range_periodic - periodic criteria range table
		$this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ms_range_date` (
		`range_id` int(11) NOT NULL AUTO_INCREMENT,
		`from` DATETIME,
		`to` DATETIME NOT NULL,
		PRIMARY KEY (`range_id`)) default CHARSET=utf8");
	
		// ms_seller_group_criteria - table, which connects concrete commissions for criterias in the seller groups
		$this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ms_seller_group_criteria` (
		`seller_group_criteria_id` int(11) NOT NULL AUTO_INCREMENT,
		`commission_id` int(11) NOT NULL,
		`criteria_id` int(11) NOT NULL,
		PRIMARY KEY (`seller_group_criteria_id`)) default CHARSET=utf8");
	
		// new attributes
		$this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ms_attribute` (
		`attribute_id` int(11) NOT NULL AUTO_INCREMENT,
		`attribute_type` int(11) NOT NULL,
		`number` TINYINT NOT NULL DEFAULT 0,
		`multilang` TINYINT NOT NULL DEFAULT 0,
		`tab_display` TINYINT NOT NULL DEFAULT 0,
		`required` TINYINT NOT NULL DEFAULT 0,
		`enabled` TINYINT NOT NULL DEFAULT 1,
		`sort_order` int(3) NOT NULL,
		PRIMARY KEY (`attribute_id`)
		) DEFAULT CHARSET=utf8");
	
		$this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ms_attribute_description` (
		`attribute_id` int(11) NOT NULL,
		`language_id` int(11) NOT NULL,
		`name` varchar(128) NOT NULL,
		`description` TEXT NOT NULL DEFAULT '',
		PRIMARY KEY (`attribute_id`,`language_id`)
		) DEFAULT CHARSET=utf8");
	
		$this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ms_attribute_value` (
		`attribute_value_id` int(11) NOT NULL AUTO_INCREMENT,
		`attribute_id` int(11) NOT NULL,
		`image` varchar(255) NOT NULL,
		`sort_order` int(3) NOT NULL,
		PRIMARY KEY (`attribute_value_id`)
		) DEFAULT CHARSET=utf8");
	
		$this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ms_attribute_value_description` (
		`attribute_value_id` int(11) NOT NULL,
		`language_id` int(11) NOT NULL,
		`attribute_id` int(11) NOT NULL,
		`name` TEXT NOT NULL,
		PRIMARY KEY (`attribute_value_id`,`language_id`)
		) DEFAULT CHARSET=utf8");
	
		$this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ms_attribute_attribute` (
		`ms_attribute_id` int(11) DEFAULT 0,
		`oc_attribute_id` int(11) DEFAULT 0,
		PRIMARY KEY (`ms_attribute_id`, `oc_attribute_id`)
		) DEFAULT CHARSET=utf8");
	
		$this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ms_product_attribute` (
		`product_id` int(11) NOT NULL,
		`attribute_id` int(11) NOT NULL,
		`attribute_value_id` int(11) NOT NULL,
		PRIMARY KEY (`product_id`,`attribute_id`,`attribute_value_id`)) default CHARSET=utf8");
	
		// new payments
		$this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ms_payment` (
		`payment_id` int(11) NOT NULL AUTO_INCREMENT,
		`seller_id` int(11) NOT NULL,
		`product_id` int(11) DEFAULT NULL,
		`order_id` int(11) DEFAULT NULL,
		`payment_type` int(11) NOT NULL,
		`payment_status` int(11) NOT NULL,
		`payment_method` int(11) NOT NULL,
		`payment_data` TEXT NOT NULL DEFAULT '',
		`amount` DECIMAL(15,4) NOT NULL,
		`currency_id` int(11) NOT NULL,
		`currency_code` VARCHAR(3) NOT NULL,
		`description` TEXT NOT NULL DEFAULT '',
		`date_created` DATETIME NOT NULL,
		`date_paid` DATETIME DEFAULT NULL,
		PRIMARY KEY (`payment_id`)) default CHARSET=utf8");
	
		$this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ms_order_comment` (
		`order_comment_id` int(11) NOT NULL AUTO_INCREMENT,
		`order_id` int(11) NOT NULL,
		`product_id` int(11) NOT NULL,
		`seller_id` int(11) NOT NULL,
		`comment` text NOT NULL,
		PRIMARY KEY (`order_comment_id`)
		) DEFAULT CHARSET=utf8");

        $this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ms_subscriptions` (
		`plan_id` int(11) NOT NULL AUTO_INCREMENT,
		`plan_status` int(2) NOT NULL DEFAULT '0',
		`price` decimal(15,4) NOT NULL DEFAULT '0.0000',
		`maximum_products` int(10) NOT NULL DEFAULT '0',
		`format` INT(11) NOT NULL DEFAULT '3',
		`period` INT(11) NOT NULL DEFAULT '1',
		`discount` decimal(15,4) NOT NULL DEFAULT '0.0000',
		PRIMARY KEY (`plan_id`)
		) DEFAULT CHARSET=utf8");

        $this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ms_subscriptions_description` (
		`plan_id` int(11) NOT NULL,
		`language_id` int(11) NOT NULL,
		`title` VARCHAR(255) NOT NULL,
		`description` TEXT NULL,
		PRIMARY KEY (`plan_id`, `language_id`)
		) DEFAULT CHARSET=utf8");
	
        $this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ms_seller_payments` (
		`payment_id` int(11) NOT NULL AUTO_INCREMENT,
		`plan_id` int(11) NOT NULL,
		`seller_id` int(11) NOT NULL,
		`amount` int(11) NOT NULL DEFAULT '1',
		`payment_method` int(11) NOT NULL DEFAULT '1',
		`payment_status` int(1) NOT NULL DEFAULT '0',
		`created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (`payment_id`, `plan_id`, `seller_id`)
		) DEFAULT CHARSET=utf8");

        $this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ms_seller_review` (
		`review_id` int(11) NOT NULL AUTO_INCREMENT,
	    `seller_id` int(11) NOT NULL,
	    `customer_id` int(11) NOT NULL,
	    `author` varchar(64) NOT NULL,
	    `text` text NOT NULL,
	    `rating` int(1) NOT NULL,
	    `status` tinyint(1) NOT NULL DEFAULT '0',
	    `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	    `date_modified` datetime DEFAULT NULL,
	    PRIMARY KEY (`review_id`),
	    KEY `seller_id` (`seller_id`)
		) DEFAULT CHARSET=utf8");

		$this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ms_seller_gallery` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
	    `seller_id` int(11) NOT NULL,
	    `image` varchar(191) NOT NULL,
	    PRIMARY KEY (`id`),
	    KEY `seller_id` (`seller_id`)
		) DEFAULT CHARSET=utf8");

		$this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ms_allowed_payment_methods` (
		`seller_id` int(11) NOT NULL,
		`data` TEXT NULL
		) DEFAULT CHARSET=utf8");


        $this->db->query("CREATE INDEX idx_seller_id ON ms_product(seller_id)");

        $ms_fee_column = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "category` LIKE 'ms_fee'");
		if(!$ms_fee_column->num_rows)
			$this->db->query("ALTER TABLE `".DB_PREFIX."category` ADD COLUMN `ms_fee` text NULL AFTER `status`");

		if( !$this->db->check(['product_description' => ['seller_notes']], 'column') ){
			$this->db->query('ALTER TABLE `product_description` ADD COLUMN seller_notes TEXT NULL;');
		}
	}
	
	public function createData() {
        $testDataExists = $this->db->query("SELECT * FROM " . DB_PREFIX . "ms_db_schema");

        if ($testDataExists->num_rows)
        {

        }
        else {
            $schema = explode(".", $this->MsLoader->dbVer);
            $this->db->query("INSERT INTO " . DB_PREFIX . "ms_db_schema (major, minor, build, revision, date_applied) VALUES({$schema[0]},{$schema[1]},{$schema[2]},{$schema[3]}, NOW())");

            // create default fees
            $this->db->query("INSERT INTO " . DB_PREFIX . "ms_commission () VALUES()");
            $commission_id = $this->db->getLastId();

            // default fee rates
            foreach (array(MsCommission::RATE_SALE, MsCommission::RATE_SIGNUP, MsCommission::RATE_LISTING) as $type) {
                $this->db->query("INSERT INTO `" . DB_PREFIX . "ms_commission_rate` (rate_type, commission_id, flat, percent, payment_method) VALUES(" . $type . ", $commission_id, 0,0," . MsPayment::METHOD_BALANCE . ")");
            }

            // default seller group fees
            $this->db->query("INSERT INTO " . DB_PREFIX . "ms_seller_group (commission_id) VALUES($commission_id)");
            $seller_group_id = $this->db->getLastId();

            // default seller group description
            $languages = $this->model_localisation_language->getLanguages();
            foreach ($languages as $code => $language) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "ms_seller_group_description SET seller_group_id = '" . (int)$seller_group_id . "', language_id = '" . (int)$language['language_id'] . "', name = 'Default', description = 'Default seller group'");
            }

            // multimerch routes
            $this->db->query("INSERT INTO " . DB_PREFIX . "layout SET name = 'MultiMerch Seller Account'");
            $layout_id = $this->db->getLastId();
            $this->db->query("INSERT INTO " . DB_PREFIX . "layout_route SET layout_id = '" . (int)$layout_id . "', route = 'seller/account'");
            $this->db->query("INSERT INTO " . DB_PREFIX . "layout SET name = 'MultiMerch Seller List'");
            $layout_id = $this->db->getLastId();
            $this->db->query("INSERT INTO " . DB_PREFIX . "layout_route SET layout_id = '" . (int)$layout_id . "', route = 'seller/catalog-seller'");
            $this->db->query("INSERT INTO " . DB_PREFIX . "layout SET name = 'MultiMerch Seller Profile'");
            $layout_id = $this->db->getLastId();
            $this->db->query("INSERT INTO " . DB_PREFIX . "layout_route SET layout_id = '" . (int)$layout_id . "', route = 'seller/catalog-seller/profile'");
            $this->db->query("INSERT INTO " . DB_PREFIX . "layout SET name = 'MultiMerch Seller Products'");
            $layout_id = $this->db->getLastId();
            $this->db->query("INSERT INTO " . DB_PREFIX . "layout_route SET layout_id = '" . (int)$layout_id . "', route = 'seller/catalog-seller/products'");
        }
    }
	public function deleteSchema() {
		$this->db->query("DROP TABLE IF EXISTS
		`" . DB_PREFIX . "ms_product`,
		`" . DB_PREFIX . "ms_seller`,
		`" . DB_PREFIX . "ms_order_product_data`,
		`" . DB_PREFIX . "ms_balance`,
		`" . DB_PREFIX . "ms_seller_group`,
		`" . DB_PREFIX . "ms_seller_group_description`,
		`" . DB_PREFIX . "ms_seller_group_criteria`,
		`" . DB_PREFIX . "ms_commission_rate`,
		`" . DB_PREFIX . "ms_commission`,
		`" . DB_PREFIX . "ms_criteria`,
		`" . DB_PREFIX . "ms_range_int`,
		`" . DB_PREFIX . "ms_range_decimal`,
		`" . DB_PREFIX . "ms_range_date`,
		`" . DB_PREFIX . "ms_attribute`,
		`" . DB_PREFIX . "ms_attribute_description`,
		`" . DB_PREFIX . "ms_attribute_value`,
		`" . DB_PREFIX . "ms_attribute_value_description`,
		`" . DB_PREFIX . "ms_attribute_attribute`,
		`" . DB_PREFIX . "ms_product_attribute`,
		`" . DB_PREFIX . "ms_payment`,
		`" . DB_PREFIX . "ms_db_schema`,
		`" . DB_PREFIX . "ms_version`,
		`" . DB_PREFIX . "ms_subscriptions`,
		`" . DB_PREFIX . "ms_subscriptions_description`,
		`" . DB_PREFIX . "ms_seller_payments`,
		`" . DB_PREFIX . "ms_seller_review`,
		`" . DB_PREFIX . "ms_seller_gallery`,
		`" . DB_PREFIX . "ms_allowed_payment_methods`");

		$this->db->query("ALTER TABLE `".DB_PREFIX."category` DROP COLUMN IF EXISTS `ms_fee`");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "extension` WHERE code = 'seller_based'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `group`='seller_based' AND `key`='seller_based_status'");
	}
	
	public function deleteData() {
		//@todo
		
		// remove MultiMerch routes
		$this->db->query("DELETE FROM " . DB_PREFIX . "layout WHERE name = 'MultiMerch Seller Account'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "layout_route WHERE route = 'seller/account'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "layout WHERE name = 'MultiMerch Seller List'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "layout_route WHERE route = 'seller/catalog-seller'");
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "layout WHERE name = 'MultiMerch Seller Profile'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "layout_route WHERE route = 'seller/catalog-seller/profile'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "layout WHERE name = 'MultiMerch Seller Products'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "layout_route WHERE route = 'seller/catalog-seller/products'");
	}
}
