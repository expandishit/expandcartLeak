<?php
class ModelModulestockzonesSettings extends Model {

    /**
     * Install stockzones Application
     */
    public function install(){
        //Add stockzones new tables
        //variant_id
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "stockzones_product` (
            `stockzones_product_id` VARCHAR(30) NOT NULL,      
            `expandcart_product_id` int(11) NOT NULL,
            
            `parent_id` VARCHAR(255) NULL,
            `orignal_variation_id` VARCHAR(255) NULL,
            `name` VARCHAR(255) NULL,
            `slug` VARCHAR(255) NULL,            
            `wholesaler_id` VARCHAR(255) NULL,
            
            PRIMARY KEY (`stockzones_product_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "stockzones_category` (
            `stockzones_category_id` VARCHAR(30) NOT NULL,
            `expandcart_category_id` int(11) NOT NULL,
            `name` VARCHAR(255),
            `slug` VARCHAR(255),
            `parent_id` int(11),
            `created` DATETIME,
            PRIMARY KEY (`stockzones_category_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "stockzones_order` (
            `stockzones_order_id` VARCHAR(30) NOT NULL,
            `expandcart_order_id` int(11) NOT NULL,
            `order_number` VARCHAR(100) NOT NULL,            
            `received_at` DATETIME NOT NULL DEFAULT current_timestamp(),
            `status` VARCHAR(255) DEFAULT 'In Process',
            PRIMARY KEY (`stockzones_order_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    }

    /**
     * Uninstall stockzones Application
     */
    public function uninstall(){
        $this->db->query("
            DROP TABLE IF EXISTS `buyer_subscription`,
            `stockzones_product`,
            `stockzones_category`,
            `stockzones_order` 
            ");
    }
 
}
