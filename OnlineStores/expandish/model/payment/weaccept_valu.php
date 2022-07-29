<?php

/**
 * WeAccept Value Model Class
 * 
 * @author MohamedHassanWD <hassan.mohamed.sf@gmail.com>
 */
class ModelPaymentWeAcceptValu extends Model
{
	/**
	 * @var lastError last error has been initialized
	 */
	private $lastError;



	public function getMethod($address, $total)
	{
		$method_data = array();

		$this->language->load_json('payment/weaccept_valu');

		$geo_zone_id = $this->config->get('weaccept_valu_geo_zone_id');

		if ($geo_zone_id != 0 && $this->getGeoZone($geo_zone_id, $address) == false) {
            $method_data = [];
		} else {
            $method_data = array(
                'code'       => 'weaccept_valu',
                'title'      => $this->language->get('text_title'),
            );
		}


		return $method_data;
	}

	/**
	 * Step 1: get the authentication token
	 * 
	 * @param api_key API Key
	 * @return auth_token Authentication Token
	 */
	public function get_auth_token($api_key)
	{
		$url = 'https://accept.paymobsolutions.com/api/auth/tokens';

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3600);
		curl_setopt($ch, CURLOPT_TIMEOUT, 3600);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
		curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['api_key' => $api_key]));
		$response = curl_exec($ch);
		$http_info = curl_getinfo($ch);
		$this->lastError = curl_error($ch);
		curl_close($ch);

		$result = false;
		if (($http_info['http_code'] == 200 || $http_info['http_code'] == 201) && $response) {
			$result = json_decode($response);
			$this->lastError = false;
		} elseif ($http_info['http_code'] != 0) {
			if ($response) {
				$response = json_decode($response, true);
				$error = $this->errorParser($response);
				$this->lastError = $error;
			} else {
				$this->lastError = 'ErrorCode: ' . $http_info['http_code'];
			}
		}
		return $result;
	}


	/**
	 * Step 2: Order registration request
	 * @param auth_token Authentication token from step 1
	 * @param amount_cents Total amount to be collected
	 * @param currency Currency to be used
	 * @param order_id Mercant order id
	 * @param shipping_data Customer's shipping address data
	 * @return order_data Order data from payment gateway
	 */
	public function get_order_registeration_request($auth_token, $mercant_id, $order_id, $amount_cents, $currency, $shipping_data)
	{
		$request_data = [
			"auth_token" 				=> $auth_token,
			"delivery_needed" 			=> "false",
			"merchant_id" 				=> $mercant_id,
			"amount_cents" 				=> $amount_cents,
			"currency" 					=> $currency,
			"merchant_order_id" 		=> $order_id,
			"items" 					=> [],
			"shipping_data" 			=> $shipping_data,
		];

		$url = 'https://accept.paymobsolutions.com/api/ecommerce/orders';

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3600);
		curl_setopt($ch, CURLOPT_TIMEOUT, 3600);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
		curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_data));
		$response = curl_exec($ch);
		$http_info = curl_getinfo($ch);
		$this->lastError = curl_error($ch);
		curl_close($ch);

		$result = false;

		if (($http_info['http_code'] == 200 || $http_info['http_code'] == 201) && $response) {
			$result = $response;
			$this->lastError = false;
		} elseif ($http_info['http_code'] != 0) {
			if ($response) {
				$response = json_decode($response, true);
				$error = $this->errorParser($response);
				$this->lastError = $error;

				if ($error == 'message: duplicate') {
					$url = 'https://accept.paymobsolutions.com/api/ecommerce/orders/3294760';
					$ch = curl_init($url);
					curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3600);
					curl_setopt($ch, CURLOPT_TIMEOUT, 3600);
					curl_setopt($ch, CURLOPT_HEADER, false);
					curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
					curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
					curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_data));
					$response = curl_exec($ch);
					$http_info = curl_getinfo($ch);
					curl_close($ch);

					return $response;
				}
			} else {
				$this->lastError = 'ErrorCode: ' . $http_info['http_code'];
			}
		}
		return $result;
	}


	/**
	 * Step 3: Payment key request
	 * @param 
	 * @return token Payment key
	 */
	public function get_payment_key_request($auth_token, $amount_cents, $weaccept_order_id, $currency, $integration_id, $billing_data)
	{
		$request_data = [
			"auth_token"	 			=> $auth_token,
			"amount_cents"				=> $amount_cents,
			"delivery_needed" 			=> "false",
			"expiration" 				=> 3600,
			"order_id" 					=> $weaccept_order_id,
			"billing_data" 				=> $billing_data,
			"currency" 					=> $currency,
			"integration_id" 			=> $integration_id,
			"lock_order_when_paid" 		=> "false"
		];

		$url = 'https://accept.paymobsolutions.com/api/acceptance/payment_keys';

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3600);
		curl_setopt($ch, CURLOPT_TIMEOUT, 3600);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
		curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_data));
		curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
		$response = curl_exec($ch);
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$this->lastError = curl_error($ch);
		curl_close($ch);

		$result = false;

		if (($code == 200 || $code == 201) && $response) {
			$result = json_decode($response);
			$this->lastError = false;
		} elseif ($code != 0) {
			if ($response) {
				$response = json_decode($response, true);
				$error = $this->errorParser($response);
				$this->lastError = $error;
			} else {
				$this->lastError = 'ErrorCode: ' . $code;
			}
		}
		return $result;
	}


	/**
	 * Get the order from DB by order_id
	 * @param order_id Order id from Expand DB
	 */
	public function get_weaccept_order($order_id)
	{
		$order = $this->db->query("SELECT * FROM `" . DB_PREFIX . "weaccept_online_orders` WHERE expand_order_id='" . $order_id . "' LIMIT 1");

		if ($order->num_rows > 0) {
			return $order->rows;
		} else {
			return null;
		}
	}

	public function getLastError()
	{
		return $this->lastError;
	}

	private function errorParser($array)
	{
		$result = '';
		if (is_array($array)) {
			foreach ($array as $key => $value) {
				if ($key !== 0) {
					$result .= $key . ': ';
				}
				if (is_array($value)) {
					$result .= $this->errorParser($value) . "\n";
				} else {
					$result .= $value;
				}
			}
		} else {
			$result = $array;
		}
		return $result;
	}

	/**
	 * Get all geo zones by array of zones ids.
	 *
	 * @param array $zones
	 * @param array $address
	 *
	 * return array|bool
	 */
	public function getGeoZone($zones, $address)
	{
		if (!is_array($zones)) {
			$zones = [$zones];
		}

		$query = [];
		$query[] = 'SELECT * FROM zone_to_geo_zone';
		$query[] = 'WHERE geo_zone_id IN (' . implode(',', $zones) . ')';
		$query[] = 'AND country_id = "' . (int) $address['country_id'] . '"';
		$query[] = 'AND (zone_id = "' . (int) $address['zone_id'] . '" OR zone_id = "0")';

		$data = $this->db->query(implode(' ', $query));

		if ($data->num_rows) {
			return $data->rows;
		}

		return false;
	}

	/**
	 * Calculate the Hash
	 *
	 * @param string $key
	 * @param array $data
	 * @param string $type
	 * @return string Hash
	 */
	public static function calculateHash($key, $data, $type)
	{
		$str = '';
		switch ($type) {
			case 'TRANSACTION':
				$str =
					$data['amount_cents'] .
					$data['created_at'] .
					$data['currency'] .
					$data['error_occured'] .
					$data['has_parent_transaction'] .
					$data['id'] .
					$data['integration_id'] .
					$data['is_3d_secure'] .
					$data['is_auth'] .
					$data['is_capture'] .
					$data['is_refunded'] .
					$data['is_standalone_payment'] .
					$data['is_voided'] .
					$data['order'] .
					$data['owner'] .
					$data['pending'] .
					$data['source_data_pan'] .
					$data['source_data_sub_type'] .
					$data['source_data_type'] .
					$data['success'];
				break;
			case 'TOKEN':
				$str =
					$data['card_subtype'] .
					$data['created_at'] .
					$data['email'] .
					$data['id'] .
					$data['masked_pan'] .
					$data['merchant_id'] .
					$data['order_id'] .
					$data['token'];
				break;
			case 'DELIVERY_STATUS':
				$str =
					$data['created_at'] .
					$data['extra_description'] .
					$data['gps_lat'] .
					$data['gps_long'] .
					$data['id'] .
					$data['merchant'] .
					$data['order'] .
					$data['status'];
				break;
		}
		$hash = hash_hmac('sha512', $str, $key);
		return $hash;
	}

	public function insertWeAcceptOrder($customerId,$order,$weaccept_order,$merchant_order_id,$order_registration_request)
	{
		$sql = "
		INSERT INTO `" . DB_PREFIX . "weaccept_online_orders`
		(customer_id,expand_order_id,weaccept_order_id,merchant_order_id,response)
		VALUES
		({$customerId},{$order['order_id']},{$weaccept_order['id']},'{$merchant_order_id}','{$this->db->escape($order_registration_request)}')";

		$this->db->query($sql);
	}
}
