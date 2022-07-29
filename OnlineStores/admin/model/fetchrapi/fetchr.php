<?php
/*
  @Model: Fetchr Api Model.
  @Author: Moath Mobarak.
  @Version: 1.1.0
*/
class ModelFetchrapiFetchr extends Model
{
	public function getOrders()
	{
		$query = $this->db->query("SELECT order_id, store_id, firstname, lastname, email, telephone, payment_address_1, payment_country,payment_city, FORMAT((select `value` from order_total as o_t where code like 'total' and o_t.order_id = order.order_id),2) as total, total as total_org, payment_method, payment_code, comment, customer_id, shipping_zone as zone_name FROM `" . DB_PREFIX . "order` 
        	LEFT JOIN order_status ON order.order_status_id = order_status.order_status_id
			WHERE order_status.`order_status_id` = '" . $this->config->get('fetchr_ready_shipping_status') . "' AND order_status.`language_id` = '" . $this->config->get('config_language_id') . "'");

        return $query->rows;
    }

    public function getOrderProducts($order_id) 
    {
        $query = $this->db->query("SELECT op.order_id, op.name, op.quantity, p.sku, p.price FROM " . DB_PREFIX . "order_product op INNER JOIN " . DB_PREFIX . "product p on op.product_id=p.product_id  WHERE order_id = '" . (int)$order_id . "'");

        return $query->rows;
	}

	/* Old version
	public function saveTrackingStatus($orderId, $tracking_number)
	{
		//Get status.
		$Fetchr_Shipping = $this->config->get('fetchr_being_shipped_status');
		$Ready_Pick_up = $this->config->get('fetchr_ready_shipping_status');

		//Save tracking and change status.
		$query = $this->db->query("UPDATE `" . DB_PREFIX . "order` SET tracking = '" . $this->db->escape($tracking_number) . "', order_status_id = '" . (int)$Fetchr_Shipping . "' WHERE order_id='" . (int)$orderId . "'");

		if ($this->db->escape($tracking_number)) {
			$href = "http://track.menavip.com/track.php?tracking_number=". $this->db->escape($tracking_number);

			$tracking_url = '<strong>Tracking URL:</strong> <a href="'. $href .'" target="_blank">' . $href . '</a>';
        	
			$histroy_data = $this->db->query("SELECT order_status_id, order_history_id, `comment` FROM " . DB_PREFIX . "order_history WHERE order_id='" . (int)$orderId . "' AND order_status_id= '" . (int)$Ready_Pick_up . "' AND comment NOT LIKE '%track.menavip.com%'");

            if (isset($histroy_data->row['order_history_id'])) {
                $order_history_id = $histroy_data->row['order_history_id'];
                $comment = $histroy_data->row['comment'];

                $history = $this->db->query("UPDATE `" . DB_PREFIX . "order_history` SET comment = '" . $this->db->escape($tracking_url) . '</br>' . $comment . "' WHERE order_id='" . (int)$orderId . "' AND order_status_id='" . (int)$Ready_Pick_up . "' AND order_history_id='" . $order_history_id . "'");
            }
        }

		return $query;
	}
	*/

	public function saveTrackingStatus($orderId, $tracking_number,$awb_link)
	{	
		//Get status.
		$Fetchr_Shipping = $this->config->get('fetchr_being_shipped_status');
		$Ready_Pick_up = $this->config->get('fetchr_ready_shipping_status');
		//Save tracking and change status.
		$query = $this->db->query("UPDATE `" . DB_PREFIX . "order` SET tracking = '" . $this->db->escape($tracking_number) . "', order_status_id = '" . (int)$Fetchr_Shipping . "' WHERE order_id='" . (int)$orderId . "'");
		
		if ($tracking_number) {
			$href = "https://track.fetchr.us/track/". $tracking_number;
			$tracking_url = '<strong>Tracking URL:</strong> <a href="'. $href .'" target="_blank">' . $href . '</a>';

			$histroy_data = $this->db->query("SELECT order_status_id, order_history_id, `comment` FROM " . DB_PREFIX . "order_history WHERE order_id='" . (int)$orderId . "' AND order_status_id= '" . (int)$Ready_Pick_up . "' AND comment NOT LIKE '%track.fetchr.us/track/%'");
			
			$comment = $order_history_id = '';
			if (isset($histroy_data->row['comment']))
				$comment =  $histroy_data->row['comment'];

			if (isset($histroy_data->row['order_history_id']))
				$order_history_id = $histroy_data->row['order_history_id'];


			if (isset($histroy_data->row['order_history_id'])) {
				if(!empty($awb_link)) {
					$awb_url = '<strong>AWB LINK:</strong> <a href="'. $awb_link .'" target="_blank">' . $awb_link . '</a>';
				}
				$order_history_id = $histroy_data->row['order_history_id'];
				$history = $this->db->query("UPDATE `" . DB_PREFIX . "order_history` SET comment = '" . $this->db->escape($tracking_url) . '</br>' . $this->db->escape($awb_url) . '</br>' . $comment . "' WHERE order_id='" . (int)$orderId . "' AND order_status_id='" . (int)$Ready_Pick_up . "' AND order_history_id='" . $order_history_id . "'");
			}
		}

		return $query;
	}


}
