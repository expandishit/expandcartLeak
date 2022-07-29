<?php

class ModelModuleStockForecasting extends Model {

	public function install(){
		//Add new table
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "product_stock_forecasting_quantities` (
			  `product_id` int(11) NOT NULL,
  			  `day` ENUM('SUNDAY', 'MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY'),
			  `available_quantity` int(4) NOT NULL DEFAULT 0,
			  PRIMARY KEY (`product_id`, `day`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

		//Add Tracking column in Order table if it doesn't exist
		if( !$this->db->check(['order_product' => ['product_delivery_date']], 'column') ){
			$this->db->query("ALTER TABLE `order_product` ADD COLUMN `product_delivery_date` date NULL");
		}
	}

	public function uninstall(){
		$this->db->query("DROP TABLE IF EXISTS `product_stock_forecasting_quantities`;");
		$this->db->query("ALTER TABLE `order_product` DROP COLUMN `product_delivery_date`");
		unset($this->session->data['stock_forecasting_cart']);
	}

	public function getProductStockForecasting($product_id){
		return $this->db->query("SELECT `available_quantity` , `day`
			FROM `" . DB_PREFIX . "product_stock_forecasting_quantities`
			WHERE product_id = " . (int)$product_id)->rows;
	}
}

