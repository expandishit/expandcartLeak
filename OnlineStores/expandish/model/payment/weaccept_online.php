<?php

/**
 * WeAccept Payments Model Class
 * @author Mohamed Hassan
 */
class ModelPaymentWeAcceptOnline extends Model
{
	/**
	 * @var lastError last error has been initialized
	 */
	private $lastError;



	public function getMethod($address, $total)
	{
		$method_data = array();

		$this->language->load_json('payment/weaccept_online');

		$geo_zone_id = $this->config->get('weaccept_online_geo_zone_id');

		if ($geo_zone_id != 0 && $this->getGeoZone($geo_zone_id, $address) == false) {
            $method_data = [];
		} else {
            $method_data = array(
                'code'       => 'weaccept_online',
                'title'      => $this->language->get('text_title'),
            );
		}


		return $method_data;
	}

	/**
	 * Step 1: get the authentication token
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
     * @param $auth_token Authentication token from step 1
     * @param $mercant_id
     * @param $amount_cents Total amount to be collected
     * @param $currency Currency to be used
     * @param $order_id Mercant order id
     * @param $shipping_data Customer's shipping address data
     * @return bool|string
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
     *
     * @param $auth_token
     * @param $amount_cents
     * @param $weaccept_order_id
     * @param $currency
     * @param $integration_id
     * @param $billing_data
     * @return false|mixed
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

	public function insertWeAcceptOrder($customerId,$order,$weaccept_order,$merchant_order_id,$order_registration_request)
	{
		$sql = "
		INSERT INTO `" . DB_PREFIX . "weaccept_online_orders`
		(customer_id,expand_order_id,weaccept_order_id,merchant_order_id,response)
		VALUES
		({$customerId},{$order['order_id']},{$weaccept_order['id']},'{$merchant_order_id}','{$order_registration_request}')";

		$this->db->query($sql);
	}


    /**
     * @param $weaccept_order_id
     * @return |null
     */
    public function get_weaccept_order_by_id($weaccept_order_id)
    {
        $weaccept_order_id = $this->db->escape($weaccept_order_id);
        $order = $this->db->query("SELECT * FROM `" . DB_PREFIX . "weaccept_online_orders` WHERE weaccept_order_id='" . $weaccept_order_id . "' LIMIT 1");

        if ($order->num_rows > 0) {
            return $order->row;
        } else {
            return null;
        }
    }
}
