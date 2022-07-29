<?php
class ModelPaymentTap2 extends Model {

    public function install(){
	   $this->db->query("
          CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "tap2_customer` (
            `expandcart_customer_id` INT(11) NOT NULL,
            `tap2_customer_id` varchar(50) NOT NULL,
            PRIMARY KEY (`expandcart_customer_id`),
            FOREIGN KEY (expandcart_customer_id) REFERENCES customer(customer_id)
          ) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");
    }

    public function uninstall(){
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "tap2_customer`;");
    }

}
