<?php

class ModelModuleElModaqeqSettings extends Model
{

	public function install()
	{
		//Add elmodaqeq new tables
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "elmodaqeq_product` (
            `elmodaqeq_product_id` int(11) NOT NULL,     
            `expandcart_product_id` int(11) NOT NULL,
            `barcode` varchar(20) NOT NULL,
            PRIMARY KEY (`elmodaqeq_product_id`,`expandcart_product_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "elmodaqeq_category` (
            `elmodaqeq_category_id` int(11) NOT NULL,
            `expandcart_category_id` int(11) NOT NULL,
            PRIMARY KEY (`elmodaqeq_category_id`, `expandcart_category_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
	}

	public function uninstall()
	{
		$this->db->query("
            DROP TABLE IF EXISTS 
            `elmodaqeq_product`, 
            `elmodaqeq_category`
        ");
	}	
}

