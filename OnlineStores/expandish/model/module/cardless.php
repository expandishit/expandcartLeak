<?php

class ModelModuleCardless extends Model  {	
	

	public function isCardlessProduct($product_id)
    {
        $query = "SELECT `sku` cardless_sku FROM `" . DB_PREFIX . "cardless_products` ";
		$query .= "WHERE `product_id`='{$product_id}'";
        return $this->db->query($query)->row;
	}
	

	public function addNewCardlessPurchased($cardelss_order_confirmation, $order_id, $prepared_products)
	{

		$gift_products = $cardelss_order_confirmation->result[0]->itemdet;

		$query_values = "";

		foreach ($gift_products as $key => $gift_product) {

			$transaction_id = $cardelss_order_confirmation->result[0]->transactionid;
			$order_id = $order_id;
			$product_id = $prepared_products[$key]['product_id'];
			$skuname = $gift_product->skuname;
			$sku = $gift_product->sku;
			$price = $gift_product->price;
			$cost = $gift_product->cost;
			$code = $gift_product->code;
			$serial = $gift_product->serial;

			$query_values .= "('{$transaction_id}', '{$order_id}', '{$product_id}', '{$skuname}', '{$sku}', '{$price}', '{$cost}', '{$code}', '{$serial}'), ";			
		}

		$query_values = rtrim($query_values, ', ') . ';';
		$query = "INSERT INTO `" . DB_PREFIX . "cardless_purchases` ";
		$query .= "(transaction_id, order_id, product_id, skuname, sku, price, cost, code, serial) VALUES ";
		$query .= $query_values;

		return $this->db->query($query);
	}



	public function getOrderCardlessPurchases($order_id) 
	{
		return $this->db->query("SELECT * FROM `" . DB_PREFIX . "cardless_purchases` WHERE order_id='{$order_id}'")->rows;
	}

}
