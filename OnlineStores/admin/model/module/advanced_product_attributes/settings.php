<?php

class ModelModuleAdvancedProductAttributesSettings extends Model {

	public function isInstalled(){
        $isInstalled =  \Extension::isInstalled('advanced_product_attributes');

		if($isInstalled && $this->config->get('advanced_product_attribute_status')) return true;

        return false;
	}

	public function install(){
		if( !$this->db->check(['product_attribute' => ['advanced_attribute_id']], 'column') ){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "product_attribute` ADD advanced_attribute_id INT(11) DEFAULT 0;");
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "product_attribute` DROP PRIMARY KEY, ADD PRIMARY KEY (`product_id`,`attribute_id`,`language_id`, `advanced_attribute_id`);");
		}
		//Add the App-Essential 6 tables

		$this->db->query("
			CREATE TABLE IF NOT EXISTS  `advanced_attribute` (
			  `advanced_attribute_id` int(11) NOT NULL AUTO_INCREMENT,
			  `attribute_group_id` int(11) NOT NULL,
			  `sort_order` int(3) NOT NULL,
			  `type` varchar(100) NOT NULL DEFAULT 'text',
			  `glyphicon` varchar(100) NOT NULL DEFAULT 'fa fa-check-circle',
			  PRIMARY KEY (`advanced_attribute_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
			
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `advanced_attribute_description` (
			  `advanced_attribute_id` int(11) NOT NULL,
			  `language_id` int(11) NOT NULL,
			  `name` varchar(64) NOT NULL,
			  PRIMARY KEY (`advanced_attribute_id`,`language_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;");


		$this->db->query("
			CREATE TABLE IF NOT EXISTS  `advanced_attribute_value` (
			  `advanced_attribute_value_id` int(11) NOT NULL AUTO_INCREMENT,
			  `advanced_attribute_id` int(11) NOT NULL,
			  PRIMARY KEY (`advanced_attribute_value_id`)
			) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;");


		$this->db->query("	
			CREATE TABLE IF NOT EXISTS  `advanced_attribute_value_description` (
			  `advanced_attribute_value_id` int(11) NOT NULL,
			  `language_id` int(11) NOT NULL,
			  `advanced_attribute_id` int(11) NOT NULL,
			  `name` varchar(255) NOT NULL,
			  PRIMARY KEY (`advanced_attribute_value_id`,`language_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;");


		// -- Special type One (Text types)
		$this->db->query("
			CREATE TABLE IF NOT EXISTS  `advanced_product_attribute_text` (
			  `product_id` int(11) NOT NULL,
			  `advanced_attribute_id` int(11) NOT NULL,
			  `language_id` int(11) NOT NULL,
			  `text` text NOT NULL,
			  PRIMARY KEY (`product_id`,`advanced_attribute_id`,`language_id`),
			  FOREIGN KEY (product_id) REFERENCES product_attribute (product_id) ON DELETE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;");


		// -- Special type two (choose types)
		$this->db->query("	
			CREATE TABLE IF NOT EXISTS  `advanced_product_attribute_choose` (
			 `product_id` int(11) NOT NULL,
			 `advanced_attribute_id` int(11) NOT NULL,
			 `advanced_attribute_value_id` int(11) NOT NULL,
			  PRIMARY KEY (`product_id`, `advanced_attribute_id`, `advanced_attribute_value_id`),
			  FOREIGN KEY (product_id) REFERENCES product_attribute (product_id) ON DELETE CASCADE
			)ENGINE=InnoDB DEFAULT CHARSET=utf8;");

	}

	public function uninstall(){
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_attribute` 
			WHERE advanced_attribute_id <> 0 AND language_id=0 AND attribute_id=0;");
		if( $this->db->check(['product_attribute' => ['advanced_attribute_id']], 'column') ){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "product_attribute` DROP PRIMARY KEY, ADD PRIMARY KEY (`product_id`,`attribute_id`,`language_id`);");
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "product_attribute` DROP COLUMN advanced_attribute_id;");
		}

		$this->db->query("
			DROP TABLE IF EXISTS `advanced_attribute`,
			`advanced_attribute_description`,
			`advanced_attribute_value` , 
			`advanced_attribute_value_description` ,
			`advanced_product_attribute_text`,
			`advanced_product_attribute_choose`;
			");

	    $this->load->model('setting/setting');
	    $this->model_setting_setting->deleteSetting('advanced_product_attributes');
	}

}
