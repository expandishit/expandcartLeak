<?php

class ModelModuleAuctionsAuction extends Model {

	public function getAll(){
		return $this->db->query("
			SELECT * FROM `" . DB_PREFIX . "auction`
			WHERE auction_status = 1")->rows;
	}


	/**
	* Get All auctions currently can bid on
	*
	*/
	public function getAllCurrent(){
		//Get current datetime with the app configured timezone
		$now_datetime  = $this->getNowTimeWithConfiguredTimeZone();

		return $this->db->query("
			SELECT a.*, pd.name as product_name, p.image as product_image 
			FROM `" . DB_PREFIX . "auction` a
			JOIN `" . DB_PREFIX . "product` p
				ON p.product_id = a.product_id
			JOIN `" . DB_PREFIX . "product_description` pd
				ON pd.product_id = a.product_id
			WHERE 
			pd.language_id = " . (int)$this->config->get('config_language_id') . "
			AND auction_status = 1 
			AND start_datetime <= '" . $now_datetime . "'
			AND close_datetime >= '" . $now_datetime . "'
			")->rows;
	}


	public function getOne($auction_id, $with_bidders_list = true, $with_product_images = true){
		$decimalPlace = $this->currency->getDecimalPlace();
		
		$auction = $this->db->query("
			SELECT a.*, pd.name as product_name, p.image as product_image,
			FORMAT(a.starting_bid_price, {$decimalPlace}) AS starting_bid_price,
			FORMAT(a.min_deposit, {$decimalPlace}) AS min_deposit,
			FORMAT(a.current_bid, {$decimalPlace}) AS current_bid,
			FORMAT(a.increment, {$decimalPlace}) AS increment
			FROM `" . DB_PREFIX . "auction` a
			JOIN `" . DB_PREFIX . "product` p
				ON p.product_id = a.product_id
			JOIN `" . DB_PREFIX . "product_description` pd
				ON pd.product_id = a.product_id
			WHERE 
			pd.language_id = " . (int)$this->config->get('config_language_id') . "
			AND auction_id = " . (int)$auction_id
		)->row;
		if($with_product_images)
			$auction['product_images'] = array_column($this->db->query("SELECT image FROM `" . DB_PREFIX . "product_image` WHERE product_id = " . (int)$auction['product_id'])->rows, 'image');
		
		if($with_bidders_list)
			$auction['bids'] = $this->getBiddersList($auction_id);

		return $auction;
	}

	public function getBiddersList($auction_id){
		$bidders = $this->db->query("
			SELECT ab.*, CONCAT(c.firstname , ' ', c.lastname) AS bidder_name,
			FORMAT(ab.amount, {$this->currency->getDecimalPlace()}) AS amount
			FROM `" . DB_PREFIX . "auction_bid` ab
			JOIN `" . DB_PREFIX . "customer` c
				ON ab.bidder_id = c.customer_id
			WHERE auction_id = " . (int)$auction_id. "
			ORDER BY created_at DESC")->rows;

		//Convert normal datetime format to "time ago" format.. ex: 2 seconds ago
		$bidders = array_map(function($element){
			$element['created_at'] = $this->time_elapsed_string($element['created_at'], false);
			return $element;
		},$bidders );

		// var_dump($bidders);die();


		return $bidders;
	}

	public function getOneByProductId($product_id){
		//Get current datetime with the app configured timezone
		$now_datetime  = $this->getNowTimeWithConfiguredTimeZone();

		return $this->db->query("
			SELECT *
			FROM `" . DB_PREFIX . "auction`			
			WHERE start_datetime <= '" . $now_datetime . "'
			AND close_datetime >= '" . $now_datetime . "' AND product_id = " . (int)$product_id
		)->row;
	}


	public function isRunning($auction_id){
		$now_datetime  = $this->getNowTimeWithConfiguredTimeZone();

		return $this->db->query("SELECT * FROM `" . DB_PREFIX . "auction` 
			WHERE 
			auction_id = " . (int)$auction_id  . " 
			AND start_datetime <= '" . $now_datetime . "' 
			AND close_datetime >= '" . $now_datetime . "'")->row ? TRUE : FALSE;
	}

	public function getNextMinAllowedBid($auction_id){
		
		return $this->db->query("SELECT (IFNULL( (current_bid + increment), starting_bid_price) ) AS next_minimum_allowed_bid FROM `" . DB_PREFIX . "auction` WHERE auction_id = " . (int)$auction_id)->row['next_minimum_allowed_bid'];
	}


	public function placeNewBid($auction_id, $amount, $bidder_id){
		$now_datetime  = $this->getNowTimeWithConfiguredTimeZone();

		//insert new row in auction_bid
		$this->db->query("INSERT INTO `" . DB_PREFIX . "auction_bid` SET
			amount = " . (float)$amount . ",
			bidder_id = " . (int)$bidder_id . ",
			auction_id = " . (int)$auction_id .",
			created_at = '" . $now_datetime . "';");

		$this->db->query("UPDATE `" . DB_PREFIX . "auction` SET current_bid = " . (float)$amount . " WHERE auction_id = " . (int)$auction_id);

	}

	public function getBidsUpdates($auction_id){
		$auction = $this->db->query("SELECT * FROM `" . DB_PREFIX . "auction` WHERE auction_id = " . (int)$auction_id)->row;
		$result['current_bid'] = $auction['current_bid'] ? number_format($auction['current_bid'], 2, '.', ',') : null;  //number_format($auction['starting_bid_price'], 2, '.', ',');
		$result['next_minimum_allowed_bid'] = $this->getNextMinAllowedBid($auction_id);
		$result['bidders'] = $this->getBiddersList($auction_id);
	
		return $result;
	}


	public function isCustomerSubscribedInAuction($auction_id, $customer_id){
		//if auction doesn't have a min-deposit value, all customers are subscribed by defualt
		$auction_min_deposit = $this->db->query("SELECT min_deposit FROM `" . DB_PREFIX . "auction` WHERE auction_id = " . (int)$auction_id)->row['min_deposit'];
		if($auction_min_deposit == 0) return true;


		//if not, check if customer has been paid the min-deposit already.
		return $this->db->query("SELECT * 
			FROM `" . DB_PREFIX . "auction_deposit_payments_log` 
			WHERE customer_id = " . (int)$customer_id . " AND auction_id = " . (int)$auction_id)->row ? TRUE : FALSE;
	}


	private function time_elapsed_string($datetime, $full = false) {
		$app_config_timezone = $this->config->get('auctions_timezone')?:$this->config->get('config_timezone');
		$now = new DateTime('NOW', new DateTimeZone($app_config_timezone));//)->format('Y-m-d H:i:s');
	
		$now_datetime  = $this->getNowTimeWithConfiguredTimeZone();

	    // $now = new DateTime;
	    $ago = new DateTime($datetime, new DateTimeZone($app_config_timezone));
	    $diff = $now->diff($ago);

	    $diff->w = floor($diff->d / 7);
	    $diff->d -= $diff->w * 7;

	    $string = array(
	        'y' => 'year',
	        'm' => 'month',
	        'w' => 'week',
	        'd' => 'day',
	        'h' => 'hour',
	        'i' => 'minute',
	        's' => 'second',
	    );
	    foreach ($string as $k => &$v) {
	        if ($diff->$k) {
	            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
	        } else {
	            unset($string[$k]);
	        }
	    }

	    if (!$full) $string = array_slice($string, 0, 1);
	    // var_dump($string ? implode(', ', $string) . ' ago' : 'just now');die();
	    return $string ? implode(', ', $string) . ' ago' : 'just now';
	}

	public function payMinimumDeposit($auction_id, $customer_id, $amount){
		$now_datetime  = $this->getNowTimeWithConfiguredTimeZone();

		$this->db->query("INSERT INTO " . DB_PREFIX . "customer_transaction SET customer_id = " . (int)$customer_id . ", order_id = 0 , description = 'pay minimum deposit for auction #" . (int)$auction_id  . "', amount = " . (float)-$amount . ", date_added ='" . $now_datetime . "';");				
		$this->db->query("INSERT INTO `" . DB_PREFIX . "auction_deposit_payments_log` SET customer_id = " . (int)$customer_id . ", auction_id = " . (int)$auction_id . ", amount = " . (float)$amount . " , currency_code = '" . $this->config->get('config_currency') . "' , paid_at = '" . $now_datetime . "' , order_id = 0;");
	}

	public function getCustomerSubscribedAuctions($customer_id){
		$auctions_ids = array_column(
			$this->db->query("
				SELECT DISTINCT auction_id 
				FROM `" . DB_PREFIX . "auction_bid` 
				WHERE bidder_id = " . (int)$customer_id)->rows
			,'auction_id');

		return $this->db->query("SELECT a.*, pd.name as product_name 
			FROM `" . DB_PREFIX . "auction` a
			JOIN `" . DB_PREFIX . "product_description` pd
				ON pd.product_id = a.product_id
			WHERE pd.language_id = " . (int)$this->config->get('config_language_id') . " 
			AND auction_id IN (" . implode(',', $auctions_ids) . ");")->rows;
	}

	/**
	*  Get Auctions that: 1- closed, 2- purchase_valid_days are not passed, 3- Customer didn't purchase this auction product before.
	*
	*/
	public function getCustomerWinningAuctions($customer_id){
		// $now_datetime  = $this->getNowTimeWithConfiguredTimeZone();
		$app_config_timezone = $this->config->get('auctions_timezone')?:$this->config->get('config_timezone');
		$now_datetime = new DateTime('NOW', new DateTimeZone($app_config_timezone));//)->format('Y-m-d H:i:s');

		//GET Expired/Closed Auctions ids
		$auctions_ids = array_column(
			$this->db->query("SELECT auction_id 
				FROM `" . DB_PREFIX . "auction`
				WHERE close_datetime < '" . $now_datetime->format('Y-m-d H:i:s') . "'
				AND DATE_ADD(close_datetime, INTERVAL purchase_valid_days DAY) > '" . $now_datetime->format('Y-m-d') . "'
				AND auction_id NOT IN (SELECT auction_id FROM `" . DB_PREFIX . "auction_deposit_payments_log` WHERE order_id != 0);")->rows
			, 'auction_id');

		$auctions = $auctions_ids ? $this->db->query("
			SELECT ab.auction_id, ab.amount AS highest_bid, ab.bidder_id as winner_id, ab.created_at, DATE_ADD(date(a.close_datetime), INTERVAL a.purchase_valid_days DAY) AS purchase_valid_until, a.product_id, pd.name AS product_name
			FROM auction_bid ab 
			INNER JOIN
			(
			  SELECT MAX(amount) AS highest_bid, auction_id
			  FROM auction_bid
			  GROUP BY auction_id
			) t
			ON ab.amount = t.highest_bid
			AND ab.auction_id = t.auction_id

			INNER JOIN `" . DB_PREFIX . "auction` a
			ON a.auction_id = ab.auction_id

			INNER JOIN `" . DB_PREFIX . "product_description` pd
			ON pd.product_id = a.product_id

			WHERE ab.auction_id IN (" . implode(',', $auctions_ids) . ")
			AND ab.bidder_id = " . (int)$customer_id . "
			AND pd.language_id = " . (int)$this->config->get('config_language_id'))->rows : [];


		

		return $auctions;
	}

	public function getNowTimeWithConfiguredTimeZone(){
		$app_config_timezone = $this->config->get('auctions_timezone')?:$this->config->get('config_timezone');
		return (new DateTime('NOW', new DateTimeZone($app_config_timezone)))->format('Y-m-d H:i:s');
	}

	public function addAuctionOrder($customer_id, $auction_id, $amount, $currency_code, $order_id){
		$now_datetime  = $this->getNowTimeWithConfiguredTimeZone();

		$this->db->query("INSERT INTO `" . DB_PREFIX . "auction_deposit_payments_log` 
			SET customer_id = " . (int)$customer_id . ", 
			auction_id = " . (int)$auction_id . ", 
			amount = " . (float)$amount . " , 
			currency_code = '" . $this->db->escape($currency_code) . "' , 
			paid_at = '" . $now_datetime . "' , 
			order_id = " . (int)$order_id . ";");
	}
}
