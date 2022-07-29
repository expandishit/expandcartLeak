<?php

class ModelModuleStockForecasting extends Model {
	public function getProductStockForecasting($product_id){
		return $this->db->query("SELECT `available_quantity` , `day`
			FROM `" . DB_PREFIX . "product_stock_forecasting_quantities`
			WHERE product_id = " . (int)$product_id)->rows;
	}

	public function getProductStockForecastingByDate($product_id, $date){
		if( ($d = DateTime::createFromFormat('Y-m-d', $date)) ){
			$day = $d->format('l');
		}else{
			return 0;
		}

		return $this->db->query("SELECT `available_quantity`
			FROM `" . DB_PREFIX . "product_stock_forecasting_quantities`
			WHERE product_id = " . (int)$product_id . " AND day = '" . $this->db->escape($day) ."'")->row['available_quantity'] - $this->getOrderedQuantityOfProductInDate($product_id, $date);
	}

	public function getOrderedQuantityOfProductInDate($product_id, $date){
		return $this->db->query("
			SELECT SUM(quantity) AS total_quantity 
			FROM `order_product` 
			WHERE product_delivery_date = '" . $this->db->escape($date) . "' 
			AND product_id = " .(int)$product_id . "
			AND order_id != " . (int)($this->session->data['order_id'] ?: 0) . ";")->row['total_quantity'];
	}

}

