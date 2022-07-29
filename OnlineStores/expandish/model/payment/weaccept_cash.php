<?php

/**
 * WeAccept Cash Payments Model Class
 * 
 * @author MohamedHassanWD <hassan.mohamed.sf@gmail.com>
 * @copyright 2020 ExpandCart
 */
class ModelPaymentWeAcceptCash extends Model
{
	/**
	 * @var lastError last error has been initialized
	 */
	private $lastError;



	public function getMethod($address, $total)
	{
		$method_data = array();

		$this->language->load_json('payment/weaccept_cash');

		$geo_zone_id = $this->config->get('weaccept_online_geo_zone_id');

		if ($geo_zone_id != 0 && $this->getGeoZone($geo_zone_id, $address) == false) {
			$method_data = [];
		} else {
			$method_data = array(
				'code'       => 'weaccept_cash',
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
	 * Step 4: Start pay request
	 *
	 * @param string $payment_key
	 * @return array|null response object
	 */
	public function start_pay_request($payment_key = '')
	{
		$request_data = [
			"source" => [
				"identifier" => "cash",
				"subtype" => "CASH",
			],
			"payment_token" => $payment_key
		];

		$url = 'https://accept.paymobsolutions.com/api/acceptance/payments/pay';

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
	 * Prepare cities according to Accept codex
	 *
	 * @return array Associative array of states and cities
	 */
	public function getSupportedCties()
	{
		$supported_cities = json_decode('
		[{
			"name": "Al Beheira",
			"name_ar": "Al Beheira",
			"cities": [{
					"name": "Al Beheira",
					"name_ar": "Al Beheira"
				},
				{
					"name": "Etay Al Barud",
					"name_ar": "Etay Al Barud"
				},
				{
					"name": "Wadi Al Natroun",
					"name_ar": "Wadi Al Natroun"
				},
				{
					"name": "Hosh Issa",
					"name_ar": "Hosh Issa"
				},
				{
					"name": "Kom Hamadah",
					"name_ar": "Kom Hamadah"
				},
				{
					"name": "Damanhour",
					"name_ar": "Damanhour"
				},
				{
					"name": "Kafr El Dawwar",
					"name_ar": "Kafr El Dawwar"
				},
				{
					"name": "Abou Al Matamer",
					"name_ar": "Abou Al Matamer"
				},
				{
					"name": "Edko",
					"name_ar": "Edko"
				},
				{
					"name": "Shubrakhit",
					"name_ar": "Shubrakhit"
				},
				{
					"name": "Rashid",
					"name_ar": "Rashid"
				},
				{
					"name": "Edfina",
					"name_ar": "Edfina"
				},
				{
					"name": "Al Delengat",
					"name_ar": "Al Delengat"
				},
				{
					"name": "Al Rahmaniyah",
					"name_ar": "Al Rahmaniyah"
				},
				{
					"name": "Al Mahmoudiyah",
					"name_ar": "Al Mahmoudiyah"
				},
				{
					"name": "El Nubariyah",
					"name_ar": "El Nubariyah"
				},
				{
					"name": "Abu Hummus",
					"name_ar": "Abu Hummus"
				}
			]
		},
		{
			"name": "Al Daqahliya",
			"name_ar": "Al Daqahliya",
			"cities": [{
					"name": "Al Mansoura",
					"name_ar": "Al Mansoura"
				},
				{
					"name": "Manzala",
					"name_ar": "Manzala"
				},
				{
					"name": "Aga",
					"name_ar": "Aga"
				},
				{
					"name": "Met Salsel",
					"name_ar": "Met Salsel"
				},
				{
					"name": "Tamai El Amadid",
					"name_ar": "Tamai El Amadid"
				},
				{
					"name": "Bani Ebed",
					"name_ar": "Bani Ebed"
				},
				{
					"name": "Nabroo",
					"name_ar": "Nabroo"
				},
				{
					"name": "Belqas",
					"name_ar": "Belqas"
				},
				{
					"name": "Meet Ghamr",
					"name_ar": "Meet Ghamr"
				},
				{
					"name": "Al Daqahliya",
					"name_ar": "Al Daqahliya"
				},
				{
					"name": "Dekernes",
					"name_ar": "Dekernes"
				},
				{
					"name": "Shrbeen",
					"name_ar": "Shrbeen"
				},
				{
					"name": "Mahalet Demna",
					"name_ar": "Mahalet Demna"
				},
				{
					"name": "El Gamalia",
					"name_ar": "El Gamalia"
				},
				{
					"name": "El Sinblaween",
					"name_ar": "El Sinblaween"
				},
				{
					"name": "El Mataria",
					"name_ar": "El Mataria"
				},
				{
					"name": "Talkha",
					"name_ar": "Talkha"
				},
				{
					"name": "Menit El Nasr",
					"name_ar": "Menit El Nasr"
				}
			]
		},
		{
			"name": "Al Fayoum",
			"name_ar": "Al Fayoum",
			"cities": [{
					"name": "Manshaa Abdalla",
					"name_ar": "Manshaa Abdalla"
				},
				{
					"name": "Tameaa",
					"name_ar": "Tameaa"
				},
				{
					"name": "Youssef Sadek",
					"name_ar": "Youssef Sadek"
				},
				{
					"name": "El Aagamen",
					"name_ar": "El Aagamen"
				},
				{
					"name": "Al Fayoum",
					"name_ar": "Al Fayoum"
				},
				{
					"name": "Sonores",
					"name_ar": "Sonores"
				},
				{
					"name": "Sersenaa",
					"name_ar": "Sersenaa"
				},
				{
					"name": "New Fayoum",
					"name_ar": "New Fayoum"
				},
				{
					"name": "Ebshoy",
					"name_ar": "Ebshoy"
				},
				{
					"name": "Sanhoor",
					"name_ar": "Sanhoor"
				},
				{
					"name": "Atsa",
					"name_ar": "Atsa"
				},
				{
					"name": "Kofooer Elniel",
					"name_ar": "Kofooer Elniel"
				},
				{
					"name": "Manshaa Elgamal",
					"name_ar": "Manshaa Elgamal"
				}
			]
		},
		{
			"name": "Al Gharbia",
			"name_ar": "Al Gharbia",
			"cities": [{
					"name": "Basyoon",
					"name_ar": "Basyoon"
				},
				{
					"name": "Qotoor",
					"name_ar": "Qotoor"
				},
				{
					"name": "Al Gharbia",
					"name_ar": "Al Gharbia"
				},
				{
					"name": "Alsanta",
					"name_ar": "Alsanta"
				},
				{
					"name": "Kafr Alziat",
					"name_ar": "Kafr Alziat"
				},
				{
					"name": "Zefta",
					"name_ar": "Zefta"
				},
				{
					"name": "Tanta",
					"name_ar": "Tanta"
				},
				{
					"name": "Samanood",
					"name_ar": "Samanood"
				},
				{
					"name": "Al Mahala Al Kobra",
					"name_ar": "Al Mahala Al Kobra"
				}
			]
		},
		{
			"name": "Al Meniya",
			"name_ar": "Al Meniya",
			"cities": [{
					"name": "Minya",
					"name_ar": "Minya"
				},
				{
					"name": "Mghagha",
					"name_ar": "Mghagha"
				},
				{
					"name": "Bani Mazar",
					"name_ar": "Bani Mazar"
				},
				{
					"name": "Malawi",
					"name_ar": "Malawi"
				},
				{
					"name": "Dermwas",
					"name_ar": "Dermwas"
				},
				{
					"name": "Abo Korkas",
					"name_ar": "Abo Korkas"
				},
				{
					"name": "Eladwa",
					"name_ar": "Eladwa"
				},
				{
					"name": "Matai",
					"name_ar": "Matai"
				},
				{
					"name": "Samaloot",
					"name_ar": "Samaloot"
				}
			]
		},
		{
			"name": "Al Monufia",
			"name_ar": "Al Monufia",
			"cities": [{
					"name": "Shebin El Koom",
					"name_ar": "Shebin El Koom"
				},
				{
					"name": "Al Monufia",
					"name_ar": "Al Monufia"
				},
				{
					"name": "Quesna",
					"name_ar": "Quesna"
				},
				{
					"name": "Berket Al Sabei",
					"name_ar": "Berket Al Sabei"
				},
				{
					"name": "Shohada",
					"name_ar": "Shohada"
				},
				{
					"name": "Ashmoon",
					"name_ar": "Ashmoon"
				},
				{
					"name": "Menoof",
					"name_ar": "Menoof"
				},
				{
					"name": "Sadat City",
					"name_ar": "Sadat City"
				},
				{
					"name": "Tala",
					"name_ar": "Tala"
				}
			]
		},
		{
			"name": "Al Sharqia",
			"name_ar": "Al Sharqia",
			"cities": [{
					"name": "Al Qareen",
					"name_ar": "Al Qareen"
				},
				{
					"name": "Belbes",
					"name_ar": "Belbes"
				},
				{
					"name": "Al Hasiniya",
					"name_ar": "Al Hasiniya"
				},
				{
					"name": "Al Sharqia",
					"name_ar": "Al Sharqia"
				},
				{
					"name": "Qanayiat",
					"name_ar": "Qanayiat"
				},
				{
					"name": "Hehya",
					"name_ar": "Hehya"
				},
				{
					"name": "Al Ibrahimiya",
					"name_ar": "Al Ibrahimiya"
				},
				{
					"name": "10th of Ramdan City",
					"name_ar": "10th of Ramdan City"
				},
				{
					"name": "Abu Hammad",
					"name_ar": "Abu Hammad"
				},
				{
					"name": "Zakazik",
					"name_ar": "Zakazik"
				},
				{
					"name": "Darb Negm",
					"name_ar": "Darb Negm"
				},
				{
					"name": "Awlad Saqr",
					"name_ar": "Awlad Saqr"
				},
				{
					"name": "Mashtool Al Sooq",
					"name_ar": "Mashtool Al Sooq"
				},
				{
					"name": "Meniya Alqamh",
					"name_ar": "Meniya Alqamh"
				},
				{
					"name": "Abu Kbeer",
					"name_ar": "Abu Kbeer"
				},
				{
					"name": "Inshas",
					"name_ar": "Inshas"
				},
				{
					"name": "Kafr Saqr",
					"name_ar": "Kafr Saqr"
				},
				{
					"name": "San Al Hagar",
					"name_ar": "San Al Hagar"
				},
				{
					"name": "Faqous",
					"name_ar": "Faqous"
				},
				{
					"name": "Al Salhiya Al Gedida",
					"name_ar": "Al Salhiya Al Gedida"
				}
			]
		},
		{
			"name": "Alexandria",
			"name_ar": "Alexandria",
			"cities": [{
					"name": "Maamora",
					"name_ar": "Maamora"
				},
				{
					"name": "Smouha",
					"name_ar": "Smouha"
				},
				{
					"name": "City Center",
					"name_ar": "City Center"
				},
				{
					"name": "Roshdy",
					"name_ar": "Roshdy"
				},
				{
					"name": "Luran",
					"name_ar": "Luran"
				},
				{
					"name": "Kafer Abdou",
					"name_ar": "Kafer Abdou"
				},
				{
					"name": "Miami",
					"name_ar": "Miami"
				},
				{
					"name": "Sporting",
					"name_ar": "Sporting"
				},
				{
					"name": "Al Bitash",
					"name_ar": "Al Bitash"
				},
				{
					"name": "Manshia",
					"name_ar": "Manshia"
				},
				{
					"name": "Borg El Arab",
					"name_ar": "Borg El Arab"
				},
				{
					"name": "Zezenya",
					"name_ar": "Zezenya"
				},
				{
					"name": "Abees",
					"name_ar": "Abees"
				},
				{
					"name": "San Stefano",
					"name_ar": "San Stefano"
				},
				{
					"name": "Al A\'mriah",
					"name_ar": "Al A\'mriah"
				},
				{
					"name": "Al Soyof",
					"name_ar": "Al Soyof"
				},
				{
					"name": "Azarita",
					"name_ar": "Azarita"
				},
				{
					"name": "Glem",
					"name_ar": "Glem"
				},
				{
					"name": "Muntazah",
					"name_ar": "Muntazah"
				},
				{
					"name": "Awaied-Ras Souda",
					"name_ar": "Awaied-Ras Souda"
				},
				{
					"name": "Khorshid",
					"name_ar": "Khorshid"
				},
				{
					"name": "Abu Keer",
					"name_ar": "Abu Keer"
				},
				{
					"name": "Sedi Bisher",
					"name_ar": "Sedi Bisher"
				},
				{
					"name": "Asafra",
					"name_ar": "Asafra"
				},
				{
					"name": "Alexandria",
					"name_ar": "Alexandria"
				},
				{
					"name": "Sedi Gaber",
					"name_ar": "Sedi Gaber"
				},
				{
					"name": "El-Agamy",
					"name_ar": "El-Agamy"
				},
				{
					"name": "Bangar EL Sokar",
					"name_ar": "Bangar EL Sokar"
				},
				{
					"name": "Mandara",
					"name_ar": "Mandara"
				},
				{
					"name": "Stanly",
					"name_ar": "Stanly"
				},
				{
					"name": "Al Nahda Al Amria",
					"name_ar": "Al Nahda Al Amria"
				},
				{
					"name": "Mahtet El-Raml",
					"name_ar": "Mahtet El-Raml"
				},
				{
					"name": "Sedi Kreir",
					"name_ar": "Sedi Kreir"
				},
				{
					"name": "El Borg El Kadem",
					"name_ar": "El Borg El Kadem"
				}
			]
		},
		{
			"name": "Aswan",
			"name_ar": "Aswan",
			"cities": [{
					"name": "Draw",
					"name_ar": "Draw"
				},
				{
					"name": "Nasr Elnoba",
					"name_ar": "Nasr Elnoba"
				},
				{
					"name": "El Klabsha",
					"name_ar": "El Klabsha"
				},
				{
					"name": "Aswan",
					"name_ar": "Aswan"
				},
				{
					"name": "Edfo",
					"name_ar": "Edfo"
				},
				{
					"name": "Kom Ombo",
					"name_ar": "Kom Ombo"
				},
				{
					"name": "Al Sad Al Aali",
					"name_ar": "Al Sad Al Aali"
				},
				{
					"name": "Markaz Naser",
					"name_ar": "Markaz Naser"
				},
				{
					"name": "El Redisia",
					"name_ar": "El Redisia"
				},
				{
					"name": "Abu Simbel",
					"name_ar": "Abu Simbel"
				},
				{
					"name": "El Sbaaia",
					"name_ar": "El Sbaaia"
				}
			]
		},
		{
			"name": "Asyut",
			"name_ar": "Asyut",
			"cities": [{
					"name": "Elfath",
					"name_ar": "Elfath"
				},
				{
					"name": "Manqbad",
					"name_ar": "Manqbad"
				},
				{
					"name": "El Badari",
					"name_ar": "El Badari"
				},
				{
					"name": "El Ghnayem",
					"name_ar": "El Ghnayem"
				},
				{
					"name": "Manflout",
					"name_ar": "Manflout"
				},
				{
					"name": "Abnoub",
					"name_ar": "Abnoub"
				},
				{
					"name": "Dayrout",
					"name_ar": "Dayrout"
				},
				{
					"name": "Sahel Selim",
					"name_ar": "Sahel Selim"
				},
				{
					"name": "Dronka",
					"name_ar": "Dronka"
				},
				{
					"name": "Serfa",
					"name_ar": "Serfa"
				},
				{
					"name": "Assuit Elgdeda",
					"name_ar": "Assuit Elgdeda"
				},
				{
					"name": "Asyut",
					"name_ar": "Asyut"
				},
				{
					"name": "Abou Teag",
					"name_ar": "Abou Teag"
				},
				{
					"name": "El Qusya",
					"name_ar": "El Qusya"
				},
				{
					"name": "Beny Hossien",
					"name_ar": "Beny Hossien"
				}
			]
		},
		{
			"name": "Bani Souaif",
			"name_ar": "Bani Souaif",
			"cities": [{
					"name": "Bani Souaif",
					"name_ar": "Bani Souaif"
				},
				{
					"name": "El Wastaa",
					"name_ar": "El Wastaa"
				},
				{
					"name": "Smostaa",
					"name_ar": "Smostaa"
				},
				{
					"name": "El Fashn",
					"name_ar": "El Fashn"
				},
				{
					"name": "Bebaa",
					"name_ar": "Bebaa"
				},
				{
					"name": "New Bani Souaif",
					"name_ar": "New Bani Souaif"
				},
				{
					"name": "Naser",
					"name_ar": "Naser"
				},
				{
					"name": "El Korimat",
					"name_ar": "El Korimat"
				},
				{
					"name": "Ahnaseaa",
					"name_ar": "Ahnaseaa"
				}
			]
		},
		{
			"name": "Cairo",
			"name_ar": "Cairo",
			"cities": [{
					"name": "Al Kasr Al Einy",
					"name_ar": "Al Kasr Al Einy"
				},
				{
					"name": "Manial Al Rodah",
					"name_ar": "Manial Al Rodah"
				},
				{
					"name": "1st Settlement",
					"name_ar": "1st Settlement"
				},
				{
					"name": "Down Town",
					"name_ar": "Down Town"
				},
				{
					"name": "Ramsis",
					"name_ar": "Ramsis"
				},
				{
					"name": "Al Rehab",
					"name_ar": "Al Rehab"
				},
				{
					"name": "Ezbt Elhagana",
					"name_ar": "Ezbt Elhagana"
				},
				{
					"name": "Mansheyet Naser",
					"name_ar": "Mansheyet Naser"
				},
				{
					"name": "15th of May City",
					"name_ar": "15th of May City"
				},
				{
					"name": "Gesr Al Suez",
					"name_ar": "Gesr Al Suez"
				},
				{
					"name": "3rd Settlement",
					"name_ar": "3rd Settlement"
				},
				{
					"name": "Al Moski",
					"name_ar": "Al Moski"
				},
				{
					"name": "New Maadi",
					"name_ar": "New Maadi"
				},
				{
					"name": "Al Salam City",
					"name_ar": "Al Salam City"
				},
				{
					"name": "Fustat",
					"name_ar": "Fustat"
				},
				{
					"name": "Al Matareya",
					"name_ar": "Al Matareya"
				},
				{
					"name": "Ain Shams",
					"name_ar": "Ain Shams"
				},
				{
					"name": "Cairo",
					"name_ar": "Cairo"
				},
				{
					"name": "Abdeen",
					"name_ar": "Abdeen"
				},
				{
					"name": "Abdo Basha",
					"name_ar": "Abdo Basha"
				},
				{
					"name": "Basateen",
					"name_ar": "Basateen"
				},
				{
					"name": "Ezbet El Nakhl",
					"name_ar": "Ezbet El Nakhl"
				},
				{
					"name": "5th Settlement",
					"name_ar": "5th Settlement"
				},
				{
					"name": "Nasr City",
					"name_ar": "Nasr City"
				},
				{
					"name": "Amiria",
					"name_ar": "Amiria"
				},
				{
					"name": "Helwan",
					"name_ar": "Helwan"
				},
				{
					"name": "New Cairo",
					"name_ar": "New Cairo"
				},
				{
					"name": "Al Zeitoun",
					"name_ar": "Al Zeitoun"
				},
				{
					"name": "Madinty",
					"name_ar": "Madinty"
				},
				{
					"name": "El Shorouk",
					"name_ar": "El Shorouk"
				},
				{
					"name": "Katamiah",
					"name_ar": "Katamiah"
				},
				{
					"name": "El Tahrir",
					"name_ar": "El Tahrir"
				},
				{
					"name": "Rod El Farag",
					"name_ar": "Rod El Farag"
				},
				{
					"name": "Sayeda Zeinab",
					"name_ar": "Sayeda Zeinab"
				},
				{
					"name": "Almaza",
					"name_ar": "Almaza"
				},
				{
					"name": "Hadayek Helwan",
					"name_ar": "Hadayek Helwan"
				},
				{
					"name": "El Meraag",
					"name_ar": "El Meraag"
				},
				{
					"name": "Helmeya",
					"name_ar": "Helmeya"
				},
				{
					"name": "Heliopolis",
					"name_ar": "Heliopolis"
				},
				{
					"name": "El Herafieen",
					"name_ar": "El Herafieen"
				},
				{
					"name": "Al Azhar",
					"name_ar": "Al Azhar"
				},
				{
					"name": "Hadayek Maadi",
					"name_ar": "Hadayek Maadi"
				},
				{
					"name": "Masaken Sheraton",
					"name_ar": "Masaken Sheraton"
				},
				{
					"name": "Helmiet Elzaitoun",
					"name_ar": "Helmiet Elzaitoun"
				},
				{
					"name": "Mokattam",
					"name_ar": "Mokattam"
				},
				{
					"name": "Garden City",
					"name_ar": "Garden City"
				},
				{
					"name": "Maadi",
					"name_ar": "Maadi"
				},
				{
					"name": "Dar Al Salam",
					"name_ar": "Dar Al Salam"
				},
				{
					"name": "Abaseya",
					"name_ar": "Abaseya"
				},
				{
					"name": "Mirage City",
					"name_ar": "Mirage City"
				},
				{
					"name": "Ghamrah",
					"name_ar": "Ghamrah"
				},
				{
					"name": "New El Marg",
					"name_ar": "New El Marg"
				},
				{
					"name": "Badr City",
					"name_ar": "Badr City"
				},
				{
					"name": "Zamalek",
					"name_ar": "Zamalek"
				},
				{
					"name": "Hadayek Al Qobah",
					"name_ar": "Hadayek Al Qobah"
				},
				{
					"name": "Shubra",
					"name_ar": "Shubra"
				},
				{
					"name": "Cornish Al Nile",
					"name_ar": "Cornish Al Nile"
				},
				{
					"name": "EL Marg",
					"name_ar": "EL Marg"
				},
				{
					"name": "New Nozha",
					"name_ar": "New Nozha"
				},
				{
					"name": "Maadi Degla",
					"name_ar": "Maadi Degla"
				},
				{
					"name": "Al Daher",
					"name_ar": "Al Daher"
				},
				{
					"name": "Misr El Kadima",
					"name_ar": "Misr El Kadima"
				},
				{
					"name": "Al Kalaa",
					"name_ar": "Al Kalaa"
				}
			]
		},
		{
			"name": "Damietta",
			"name_ar": "Damietta",
			"cities": [{
					"name": "Fareskor",
					"name_ar": "Fareskor"
				},
				{
					"name": "Kafr Saad",
					"name_ar": "Kafr Saad"
				},
				{
					"name": "Meet Abughaleb",
					"name_ar": "Meet Abughaleb"
				},
				{
					"name": "Kafr Bateekh",
					"name_ar": "Kafr Bateekh"
				},
				{
					"name": "Al Zarkah",
					"name_ar": "Al Zarkah"
				},
				{
					"name": "Al Sarw",
					"name_ar": "Al Sarw"
				},
				{
					"name": "Ezbet El Borg",
					"name_ar": "Ezbet El Borg"
				},
				{
					"name": "Al Rodah",
					"name_ar": "Al Rodah"
				},
				{
					"name": "Damietta",
					"name_ar": "Damietta"
				},
				{
					"name": "New Damietta",
					"name_ar": "New Damietta"
				},
				{
					"name": "Ras El Bar",
					"name_ar": "Ras El Bar"
				}
			]
		},
		{
			"name": "Giza",
			"name_ar": "Giza",
			"cities": [{
					"name": "Abu Al Nomros",
					"name_ar": "Abu Al Nomros"
				},
				{
					"name": "Qism el Giza",
					"name_ar": "Qism el Giza"
				},
				{
					"name": "Kit Kat",
					"name_ar": "Kit Kat"
				},
				{
					"name": "Al Moatamadia",
					"name_ar": "Al Moatamadia"
				},
				{
					"name": "Mansoureya",
					"name_ar": "Mansoureya"
				},
				{
					"name": "Agouza",
					"name_ar": "Agouza"
				},
				{
					"name": "Saft El Laban",
					"name_ar": "Saft El Laban"
				},
				{
					"name": "Al Monib",
					"name_ar": "Al Monib"
				},
				{
					"name": "Shabramant",
					"name_ar": "Shabramant"
				},
				{
					"name": "Dokki",
					"name_ar": "Dokki"
				},
				{
					"name": "Nahai Elbalad",
					"name_ar": "Nahai Elbalad"
				},
				{
					"name": "Al Wahat",
					"name_ar": "Al Wahat"
				},
				{
					"name": "Abou Rawash",
					"name_ar": "Abou Rawash"
				},
				{
					"name": "Al Saf",
					"name_ar": "Al Saf"
				},
				{
					"name": "Faisal",
					"name_ar": "Faisal"
				},
				{
					"name": "Beherms",
					"name_ar": "Beherms"
				},
				{
					"name": "Imbaba",
					"name_ar": "Imbaba"
				},
				{
					"name": "Bolak Al Dakrour",
					"name_ar": "Bolak Al Dakrour"
				},
				{
					"name": "Saqara",
					"name_ar": "Saqara"
				},
				{
					"name": "Al Nobariah",
					"name_ar": "Al Nobariah"
				},
				{
					"name": "Harania",
					"name_ar": "Harania"
				},
				{
					"name": "Smart Village",
					"name_ar": "Smart Village"
				},
				{
					"name": "Berak Alkiaam",
					"name_ar": "Berak Alkiaam"
				},
				{
					"name": "Saqeel",
					"name_ar": "Saqeel"
				},
				{
					"name": "Hawamdya",
					"name_ar": "Hawamdya"
				},
				{
					"name": "Al Manashi",
					"name_ar": "Al Manashi"
				},
				{
					"name": "Aossim",
					"name_ar": "Aossim"
				},
				{
					"name": "Haram",
					"name_ar": "Haram"
				},
				{
					"name": "Warraq",
					"name_ar": "Warraq"
				},
				{
					"name": "Tirsa",
					"name_ar": "Tirsa"
				},
				{
					"name": "Kerdasa",
					"name_ar": "Kerdasa"
				},
				{
					"name": "Manial",
					"name_ar": "Manial"
				},
				{
					"name": "Sheikh Zayed",
					"name_ar": "Sheikh Zayed"
				},
				{
					"name": "6th of October",
					"name_ar": "6th of October"
				},
				{
					"name": "Hadayeq El Ahram",
					"name_ar": "Hadayeq El Ahram"
				},
				{
					"name": "Manial Shiha",
					"name_ar": "Manial Shiha"
				},
				{
					"name": "Sakiat Mekki",
					"name_ar": "Sakiat Mekki"
				},
				{
					"name": "Omraneya",
					"name_ar": "Omraneya"
				},
				{
					"name": "Mohandessin",
					"name_ar": "Mohandessin"
				},
				{
					"name": "Al Barageel",
					"name_ar": "Al Barageel"
				},
				{
					"name": "Al Kom Al Ahmer",
					"name_ar": "Al Kom Al Ahmer"
				},
				{
					"name": "Giza",
					"name_ar": "Giza"
				},
				{
					"name": "Badrashin",
					"name_ar": "Badrashin"
				}
			]
		},
		{
			"name": "Ismailia",
			"name_ar": "Ismailia",
			"cities": [{
					"name": "Abo Sultan",
					"name_ar": "Abo Sultan"
				},
				{
					"name": "Fayed",
					"name_ar": "Fayed"
				},
				{
					"name": "Abu Swer",
					"name_ar": "Abu Swer"
				},
				{
					"name": "Qantara Sharq",
					"name_ar": "Qantara Sharq"
				},
				{
					"name": "Ismailia",
					"name_ar": "Ismailia"
				},
				{
					"name": "Al Kasaseen",
					"name_ar": "Al Kasaseen"
				},
				{
					"name": "Elsalhia Elgdida",
					"name_ar": "Elsalhia Elgdida"
				},
				{
					"name": "Qantara Gharb",
					"name_ar": "Qantara Gharb"
				},
				{
					"name": "Nfeesha",
					"name_ar": "Nfeesha"
				},
				{
					"name": "El Tal El Kebir",
					"name_ar": "El Tal El Kebir"
				},
				{
					"name": "Srabioom",
					"name_ar": "Srabioom"
				}
			]
		},
		{
			"name": "Kafr El Sheikh",
			"name_ar": "Kafr El Sheikh",
			"cities": [{
					"name": "Borollos",
					"name_ar": "Borollos"
				},
				{
					"name": "Metobas",
					"name_ar": "Metobas"
				},
				{
					"name": "Balteem",
					"name_ar": "Balteem"
				},
				{
					"name": "Qeleen",
					"name_ar": "Qeleen"
				},
				{
					"name": "Fooh",
					"name_ar": "Fooh"
				},
				{
					"name": "Seedy Salem",
					"name_ar": "Seedy Salem"
				},
				{
					"name": "Hamool",
					"name_ar": "Hamool"
				},
				{
					"name": "Kafr El Sheikh",
					"name_ar": "Kafr El Sheikh"
				},
				{
					"name": "Desouq",
					"name_ar": "Desouq"
				},
				{
					"name": "Bela",
					"name_ar": "Bela"
				},
				{
					"name": "Al Riadh",
					"name_ar": "Al Riadh"
				}
			]
		},
		{
			"name": "Luxor",
			"name_ar": "Luxor",
			"cities": [{
					"name": "El Tood",
					"name_ar": "El Tood"
				},
				{
					"name": "El Korna Elgdida",
					"name_ar": "El Korna Elgdida"
				},
				{
					"name": "Armant Gharb",
					"name_ar": "Armant Gharb"
				},
				{
					"name": "El Baiadiaa",
					"name_ar": "El Baiadiaa"
				},
				{
					"name": "El Korna",
					"name_ar": "El Korna"
				},
				{
					"name": "El Karnak",
					"name_ar": "El Karnak"
				},
				{
					"name": "Esnaa",
					"name_ar": "Esnaa"
				},
				{
					"name": "Armant Sharq",
					"name_ar": "Armant Sharq"
				},
				{
					"name": "El Boghdady",
					"name_ar": "El Boghdady"
				},
				{
					"name": "Luxor",
					"name_ar": "Luxor"
				}
			]
		},
		{
			"name": "Matrooh",
			"name_ar": "Matrooh",
			"cities": [{
					"name": "Matrooh",
					"name_ar": "Matrooh"
				},
				{
					"name": "Sidi Abdel Rahman",
					"name_ar": "Sidi Abdel Rahman"
				},
				{
					"name": "El Dabaa",
					"name_ar": "El Dabaa"
				},
				{
					"name": "El Alamein",
					"name_ar": "El Alamein"
				},
				{
					"name": "Marsa Matrooh",
					"name_ar": "Marsa Matrooh"
				}
			]
		},
		{
			"name": "New Valley",
			"name_ar": "New Valley",
			"cities": [{
					"name": "El Kharga",
					"name_ar": "El Kharga"
				},
				{
					"name": "New Valley",
					"name_ar": "New Valley"
				}
			]
		},
		{
			"name": "North Sinai",
			"name_ar": "North Sinai",
			"cities": [{
				"name": "Al Arish",
				"name_ar": "Al Arish"
			}]
		},
		{
			"name": "Port Said",
			"name_ar": "Port Said",
			"cities": [{
					"name": "Zohoor District",
					"name_ar": "Zohoor District"
				},
				{
					"name": "South District",
					"name_ar": "South District"
				},
				{
					"name": "Port Said",
					"name_ar": "Port Said"
				},
				{
					"name": "Port Fouad",
					"name_ar": "Port Fouad"
				}
			]
		},
		{
			"name": "Qalyubia",
			"name_ar": "Qalyubia",
			"cities": [{
					"name": "Sheben Alkanater",
					"name_ar": "Sheben Alkanater"
				},
				{
					"name": "Al Shareaa Al Gadid",
					"name_ar": "Al Shareaa Al Gadid"
				},
				{
					"name": "El Oboor",
					"name_ar": "El Oboor"
				},
				{
					"name": "Tookh",
					"name_ar": "Tookh"
				},
				{
					"name": "Kafr Shokr",
					"name_ar": "Kafr Shokr"
				},
				{
					"name": "Izbet Al Nakhl",
					"name_ar": "Izbet Al Nakhl"
				},
				{
					"name": "Om Bayoumi",
					"name_ar": "Om Bayoumi"
				},
				{
					"name": "Banha",
					"name_ar": "Banha"
				},
				{
					"name": "Bahteem",
					"name_ar": "Bahteem"
				},
				{
					"name": "Meet Nama",
					"name_ar": "Meet Nama"
				},
				{
					"name": "Abu Zaabal",
					"name_ar": "Abu Zaabal"
				},
				{
					"name": "El Khsos",
					"name_ar": "El Khsos"
				},
				{
					"name": "Shoubra Alkhema",
					"name_ar": "Shoubra Alkhema"
				},
				{
					"name": "Al Khanka",
					"name_ar": "Al Khanka"
				},
				{
					"name": "El Kanater EL Khayrya",
					"name_ar": "El Kanater EL Khayrya"
				},
				{
					"name": "Orabi",
					"name_ar": "Orabi"
				},
				{
					"name": "Mostorod",
					"name_ar": "Mostorod"
				},
				{
					"name": "Qaha",
					"name_ar": "Qaha"
				},
				{
					"name": "Qalyubia",
					"name_ar": "Qalyubia"
				},
				{
					"name": "Qalyoob",
					"name_ar": "Qalyoob"
				},
				{
					"name": "El Qalag",
					"name_ar": "El Qalag"
				}
			]
		},
		{
			"name": "Qena",
			"name_ar": "Qena",
			"cities": [{
					"name": "Abu Tesht",
					"name_ar": "Abu Tesht"
				},
				{
					"name": "Qeft",
					"name_ar": "Qeft"
				},
				{
					"name": "Naqada",
					"name_ar": "Naqada"
				},
				{
					"name": "Qena",
					"name_ar": "Qena"
				},
				{
					"name": "Al Waqf",
					"name_ar": "Al Waqf"
				},
				{
					"name": "Deshna",
					"name_ar": "Deshna"
				},
				{
					"name": "Qoos",
					"name_ar": "Qoos"
				},
				{
					"name": "Naga Hamadi",
					"name_ar": "Naga Hamadi"
				},
				{
					"name": "Farshoot",
					"name_ar": "Farshoot"
				}
			]
		},
		{
			"name": "Red Sea",
			"name_ar": "Red Sea",
			"cities": [{
					"name": "Safaga",
					"name_ar": "Safaga"
				},
				{
					"name": "Ras Ghareb",
					"name_ar": "Ras Ghareb"
				},
				{
					"name": "Gouna",
					"name_ar": "Gouna"
				},
				{
					"name": "Marsa Alam",
					"name_ar": "Marsa Alam"
				},
				{
					"name": "Qouseir",
					"name_ar": "Qouseir"
				},
				{
					"name": "Hurghada",
					"name_ar": "Hurghada"
				}
			]
		},
		{
			"name": "Sohag",
			"name_ar": "Sohag",
			"cities": [{
					"name": "Tahta",
					"name_ar": "Tahta"
				},
				{
					"name": "El Kawthar",
					"name_ar": "El Kawthar"
				},
				{
					"name": "Bardes",
					"name_ar": "Bardes"
				},
				{
					"name": "Tema",
					"name_ar": "Tema"
				},
				{
					"name": "Gerga",
					"name_ar": "Gerga"
				},
				{
					"name": "Saqatlah",
					"name_ar": "Saqatlah"
				},
				{
					"name": "Eljazeera",
					"name_ar": "Eljazeera"
				},
				{
					"name": "Dar Elsalam",
					"name_ar": "Dar Elsalam"
				},
				{
					"name": "Maragha",
					"name_ar": "Maragha"
				},
				{
					"name": "El Monshah",
					"name_ar": "El Monshah"
				},
				{
					"name": "Sohag",
					"name_ar": "Sohag"
				},
				{
					"name": "Elbalyna",
					"name_ar": "Elbalyna"
				},
				{
					"name": "Akhmem",
					"name_ar": "Akhmem"
				},
				{
					"name": "Ghena",
					"name_ar": "Ghena"
				}
			]
		},
		{
			"name": "South Sinai",
			"name_ar": "South Sinai",
			"cities": [{
					"name": "Saint Catherine",
					"name_ar": "Saint Catherine"
				},
				{
					"name": "Taba",
					"name_ar": "Taba"
				},
				{
					"name": "Dahab",
					"name_ar": "Dahab"
				},
				{
					"name": "Neweibaa",
					"name_ar": "Neweibaa"
				},
				{
					"name": "Sharm Al Sheikh",
					"name_ar": "Sharm Al Sheikh"
				},
				{
					"name": "Toor Sinai",
					"name_ar": "Toor Sinai"
				}
			]
		},
		{
			"name": "Suez",
			"name_ar": "Suez",
			"cities": [{
					"name": "Ataka District",
					"name_ar": "Ataka District"
				},
				{
					"name": "Suez",
					"name_ar": "Suez"
				},
				{
					"name": "Al Adabya",
					"name_ar": "Al Adabya"
				},
				{
					"name": "Elganaien District",
					"name_ar": "Elganaien District"
				},
				{
					"name": "El Arbeen District",
					"name_ar": "El Arbeen District"
				},
				{
					"name": "Al Suez",
					"name_ar": "Al Suez"
				},
				{
					"name": "Ain Al Sukhna",
					"name_ar": "Ain Al Sukhna"
				}
			]
		}
	]', 1);

		return $supported_cities;
	}

	public function findStateCity($city = '')
	{
		$selected_state_city = [];

		$cs = $this->getSupportedCties();

		foreach ($cs as $state) {
			foreach ($state['cities'] as $c) {
				if ($c['name'] === $city) {
					$selected_state_city['state'] = $state['name'];
					$selected_state_city['city'] = $c['name'];
					break;
				}
			}
		}

		return $selected_state_city;
	}

	public function insertWeAcceptOrder($customerId, $order, $weaccept_order, $merchant_order_id, $order_registration_request)
	{
		$sql = "
		INSERT INTO `" . DB_PREFIX . "weaccept_online_orders`
		(customer_id,expand_order_id,weaccept_order_id,merchant_order_id,response)
		VALUES
		({$customerId},{$order['order_id']},{$weaccept_order['id']},'{$merchant_order_id}','{$order_registration_request}')";

		$this->db->query($sql);
	}
}
