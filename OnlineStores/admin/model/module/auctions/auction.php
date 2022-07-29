<?php

class ModelModuleAuctionsAuction extends Model {


	public function getAll(){
		return $this->db->query("SELECT a.* , pd.name as product_name
			FROM `" . DB_PREFIX . "auction` a
			JOIN `" . DB_PREFIX . "product_description` pd
				ON pd.product_id = a.product_id
			WHERE pd.language_id = " . (int)$this->config->get('config_language_id') )->rows;
	}

	public function getAuctionsOrders(){
		return $this->db->query("SELECT * , CONCAT(c.firstname , ' ' , c.lastname ) as customer_name
			FROM `".DB_PREFIX."auction_deposit_payments_log` adpl
			JOIN `".DB_PREFIX."customer` c ON c.customer_id = adpl.customer_id
			WHERE order_id != 0; ")->rows;
	}
	

	public function getAuctionsMinDepositLog(){
		return $this->db->query("SELECT * , CONCAT(c.firstname , ' ' , c.lastname ) as customer_name, a.product_id, pd.name as product_name
			FROM `".DB_PREFIX."auction_deposit_payments_log` adpl
			JOIN `".DB_PREFIX."customer` c ON c.customer_id = adpl.customer_id
			JOIN `".DB_PREFIX."auction` a ON a.auction_id = adpl.auction_id
			JOIN `" . DB_PREFIX . "product_description` pd ON pd.product_id = a.product_id

			WHERE 
			pd.language_id = " . (int)$this->config->get('config_language_id') . "
			AND order_id = 0; ")->rows;
	}

	private function _convertTimezone($auction){
		$client_timezone     = $auction['client_timezone'];
		$app_config_timezone = $this->config->get('auctions_timezone')?:$this->config->get('config_timezone');

		//get a datetime object with the current client timezone.
		$start_datetime = new DateTime($auction['start_datetime'], new DateTimeZone($client_timezone));
		$close_datetime = new DateTime($auction['close_datetime'], new DateTimeZone($client_timezone));
		
		//Convert to app settings timezone.
		$start_datetime->setTimezone(new DateTimeZone($app_config_timezone));
		$close_datetime->setTimezone(new DateTimeZone($app_config_timezone));

		$auction['start_datetime'] = $start_datetime->format('Y-m-d H:i:s');
		$auction['close_datetime'] = $close_datetime->format('Y-m-d H:i:s');

		return $auction;
	}

	public function add($auction){

		//Convert datatime fields from client timezone to cofigured timezone in the auctions app.		
		$auction = $this->_convertTimezone($auction);

		$result = $this->db->query("INSERT INTO `" . DB_PREFIX . "auction`
			SET product_id = " . (int) $auction['product_id'] . ",
			seller_id      = 0,
			auction_status = " . (int) $auction['auction_status'] . ",
			starting_bid_price = " . (float) $auction['starting_bid_price'] . ",
			start_datetime = '" . $this->db->escape($auction['start_datetime']) . "',
			close_datetime = '" . $this->db->escape($auction['close_datetime']) . "'," .

			($auction['min_deposit'] ? ("min_deposit    = " . (float) $auction['min_deposit'] . ",") : "") .

			"increment      = " . (float) $auction['increment'] . ",
			min_quantity   = " . (int) $auction['min_quantity'] . ",
			max_quantity   = " . (int) $auction['max_quantity'] . ",
			purchase_valid_days = " . (int) $auction['purchase_valid_days']);
		
		return $result ? $this->db->getLastId() : $result;
	}

	public function get($auction_id){
		$auction = $this->db->query("SELECT * FROM `" . DB_PREFIX . "auction` WHERE auction_id = " . (int)$auction_id)->row;
		$auction['bids'] = $this->db->query("
			SELECT ab.*, CONCAT(c.firstname , ' ', c.lastname) AS bidder_name
			FROM `" . DB_PREFIX . "auction_bid` ab
			JOIN `" . DB_PREFIX . "customer` c
				ON ab.bidder_id = c.customer_id
			WHERE auction_id = " . (int)$auction_id . " ORDER BY created_at DESC")->rows;

		return $auction;
	}


	public function update($auction){
		return $this->db->query("UPDATE `" . DB_PREFIX . "auction`
			SET product_id = " . (int) $auction['product_id'] . ",
			seller_id      = 0,
			auction_status = " . (int) $auction['auction_status'] . ",
			starting_bid_price = " . (float) $auction['starting_bid_price'] . ",
			start_datetime = '" . $this->db->escape($auction['start_datetime']) . "',
			close_datetime = '" . $this->db->escape($auction['close_datetime']) . "'," .

			($auction['min_deposit'] ? ("min_deposit    = " . (float) $auction['min_deposit'] . ",") : "") .

			"increment      = " . (float) $auction['increment'] . ",
			min_quantity   = " . (int) $auction['min_quantity'] . ",
			max_quantity   = " . (int) $auction['max_quantity'] . ",
			purchase_valid_days = " . (int) $auction['purchase_valid_days'] . " 

			WHERE auction_id = " . (int)$auction['auction_id']);

	}

	public function delete($ids){
		// return $this->db->query( "DELETE FROM `".DB_PREFIX."auction` WHERE auction_id IN (" . implode(',', $ids) . ")" );

		return false;
	}
	public function deleteBid($bid_ids){
		return $this->db->query( "DELETE FROM `".DB_PREFIX."auction_bid` WHERE bid_id IN (" . implode(',', $bid_ids) . ")" );

		// return false;
	}
	public function isStarted($auction_id){

		return false;
	}

	/**
	* Get all illegible product for auctions. An illegible product is:
	* 1- status enabled, 2- quantity > 0
	*/
	public function getAuctionIllegibleProducts(){
		return $this->db->query("SELECT p.product_id, pd.name, p.quantity, p.price 
        FROM " . DB_PREFIX . "product p 
        LEFT JOIN " . DB_PREFIX . "product_description pd 
        	ON (p.product_id = pd.product_id)
        WHERE pd.language_id = " . (int)$this->config->get('config_language_id') . " 
        AND p.status = 1 AND p.quantity > 0  ")->rows;
	}
}
