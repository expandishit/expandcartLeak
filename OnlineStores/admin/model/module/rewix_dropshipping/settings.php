<?php

class ModelModuleRewixDropshippingSettings extends Model
{
	    /**
     * Install rewix Application
     */
    public function install(){
        //Add rewix new tables
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "rewix_product` (
            `rewix_product_id` VARCHAR(30) NOT NULL,
            `expandcart_product_id` int(11) NOT NULL,
            PRIMARY KEY (`rewix_product_id`, `expandcart_product_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");       
        
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "rewix_category` (
            `rewix_category_id` int(11) NOT NULL,
            `expandcart_category_id` int(11) NOT NULL,
            PRIMARY KEY (`rewix_category_id`, `expandcart_category_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    }

    /**
     * Uninstall rewix Application
     */
    public function uninstall(){
        $this->db->query("
            DROP TABLE IF EXISTS `buyer_subscription`,
            `rewix_product`,
            `rewix_category`,
            `rewix_order` 
            ");
    }
}


