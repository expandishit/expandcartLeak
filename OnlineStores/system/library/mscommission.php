<?php
class MsCommission extends Model {
	const RATE_SALE = 1;
	const RATE_LISTING = 2;
	const RATE_SIGNUP = 3;
	
	/*
	const PAYMENT_TYPE_BALANCE = 1;
	const PAYMENT_TYPE_GATEWAY = 2;
	const PAYMENT_TYPE_COMBINED = 3;
	*/
	
	const TYPE_SALES_QUANTITY = 1;
	const TYPE_SALES_AMOUNT = 2;
	const TYPE_PERIODIC = 3;
	const TYPE_DATE_UNTIL = 4;
	
	public function createCommission($rates) {
		foreach ($rates as $type => $rate) {
			if ( (!isset($rate['flat']) || $rate['flat'] === '') &&
			 (!isset($rate['percent']) || $rate['percent'] === '') &&
			  empty($rate['commission_price_list_items'])
			) {
				unset($rates[$type]);
			}
		}

		if (!empty($rates)) {
			$sql = "INSERT INTO " . DB_PREFIX . "ms_commission () VALUES ()";
			$this->db->query($sql);
			$commission_id = $this->db->getLastId();
			
			foreach ($rates as $type => $rate) {
				//Commission type : price_list
				if( isset($rate['commission_type']) &&
				 $rate['commission_type'] === 'price_list' &&
				 !empty($rate['commission_price_list_items'])){

					//Add new list
					$this->db->query('INSERT INTO `'.DB_PREFIX.'ms_commission_price_list` () VALUES ()');
					$list_id = $this->db->getLastId();

					$items = $rate['commission_price_list_items'];
					foreach ($items as $item) {					
						$this->db->query("
							INSERT INTO `" . DB_PREFIX . "ms_commission_price_list_items`
							SET category_id = " . (int)$item['category_id'] . ",
								value_type  = " . (int)$item['value_type'] . ",
								value       = " . (float)$item['value'] . ",
								price_list_id=" . (int)$list_id
							);
					}

					$sql = "INSERT INTO " . DB_PREFIX . "ms_commission_rate
						SET commission_id = " . (int)$commission_id . ",
							rate_type = " . (int)$type . ",
							flat = " . (isset($rate['flat']) && $rate['flat'] !== '' ? (float)$rate['flat'] : 'NULL') . ",
							percent = " . (isset($rate['percent']) && $rate['percent'] !== '' ? (float)$rate['percent'] : 'NULL') . ",
							commission_type = 'price_list' ,
							price_list_id = " . (int)$list_id . " ,
							payment_method = " . (isset($rate['payment_method']) && (int)$rate['payment_method'] > 0 ? (int)$rate['payment_method'] : 'NULL');
					$this->db->query($sql);	
				}
				//Commission type : General
				else{
					$sql = "INSERT INTO " . DB_PREFIX . "ms_commission_rate
						SET commission_id = " . (int)$commission_id . ",
							rate_type = " . (int)$type . ",
							flat = " . (isset($rate['flat']) && $rate['flat'] !== '' ? (float)$rate['flat'] : 'NULL') . ",
							percent = " . (isset($rate['percent']) && $rate['percent'] !== '' ? (float)$rate['percent'] : 'NULL') . ",
							commission_type = 'general' ,
							payment_method = " . (isset($rate['payment_method']) && (int)$rate['payment_method'] > 0 ? (int)$rate['payment_method'] : 'NULL');
					$this->db->query($sql);		
				}
			}
		} else {
			$commission_id = NULL;
		}
		
		return $commission_id;
	}
	
	public function editCommission($commission_id, $rates) {
		foreach ($rates as $type => $rate) {
			if (!isset($rate['rate_id']) || $rate['rate_id'] === '') {
				//create new rate
				if ((isset($rate['flat']) && $rate['flat'] !== '') || (isset($rate['percent']) && $rate['percent'] !== '') ) {
					$sql = "INSERT INTO " . DB_PREFIX . "ms_commission_rate
							SET commission_id = " . (int)$commission_id . ",
								rate_type = " . (int)$type . ",
								flat = " . (isset($rate['flat']) && $rate['flat'] !== '' ? (float)$rate['flat'] : 'NULL') . ",
								percent = " . (isset($rate['percent']) && $rate['percent'] !== '' ? (float)$rate['percent'] : 'NULL') . ",
								commission_type= '" . (isset($rate['commission_type']) && $rate['commission_type'] !== '' ? $rate['commission_type'] : 'general') . "',
								price_list_id = " . (isset($rate['price_list_id']) && $rate['price_list_id'] !== '' ? (int)$rate['price_list_id'] : 0) . " ,
								payment_method = " . (isset($rate['payment_method']) && (int)$rate['payment_method'] > 0 ? (int)$rate['payment_method'] : 'NULL');

					$this->db->query($sql);
				}
			} else {
				// update rate
				if ( (!isset($rate['flat']) || $rate['flat'] === '') &&
				 (!isset($rate['percent']) || $rate['percent'] === '') &&
				 empty($rate['commission_price_list_items'])
				) {
					$sql = "DELETE FROM " . DB_PREFIX . "ms_commission_rate WHERE rate_id = " . (int)$rate['rate_id'];
					$this->db->query($sql);
					unset($rates[$type]);
				} else {
					//if commission_type == 'price_list', then save the price list
					if( isset($rate['commission_type']) &&
					 $rate['commission_type'] === 'price_list' &&
					 !empty($rate['commission_price_list_items'])){
						//if list exist update items, else add new list and items
						$is_list_exist = $this->db->query('SELECT price_list_id FROM `'.DB_PREFIX.'ms_commission_price_list` WHERE price_list_id = ' . (int)$rate['price_list_id'])->row['price_list_id'];
						
						if($is_list_exist){
							//update list items
							//Delete exist items
							$this->db->query('DELETE FROM `'.DB_PREFIX.'ms_commission_price_list_items` WHERE price_list_id = '.(int)$rate['price_list_id'] );

							//Add list items
							$items = $rate['commission_price_list_items'];
							foreach ($items as $item) {					
								$this->db->query("
									INSERT INTO `" . DB_PREFIX . "ms_commission_price_list_items`
									SET category_id = " . (int)$item['category_id'] . ",
										value_type  = " . (int)$item['value_type'] . ",
										value       = " . (float)$item['value'] . ",
										price_list_id=" . (int)$rate['price_list_id']
									);
							}

							//Update commission data
							$sql = "UPDATE " . DB_PREFIX . "ms_commission_rate
							SET flat = " . (isset($rate['flat']) && $rate['flat'] !== '' ? (float)$rate['flat'] : 'NULL') . ",
								percent = " . (isset($rate['percent']) && $rate['percent'] !== '' ? (float)$rate['percent'] : 'NULL') . ",
								commission_type= '" . (isset($rate['commission_type']) && $rate['commission_type'] !== '' ? $rate['commission_type'] : 'general') . "',
								price_list_id = " .(int)$rate['price_list_id'] . ",
								payment_method = " . (isset($rate['payment_method']) && (int)$rate['payment_method'] > 0 ? (int)$rate['payment_method'] : 'NULL') . "
							WHERE rate_id = " . (int)$rate['rate_id'];							
							$this->db->query($sql);

						}else{
							//Add new list
							$this->db->query('INSERT INTO `'.DB_PREFIX.'ms_commission_price_list` () VALUES ()');
							$list_id = $this->db->getLastId();


							//Add list items
							$items = $rate['commission_price_list_items'];					
							foreach ($items as $item) {					
								$this->db->query("
									INSERT INTO `" . DB_PREFIX . "ms_commission_price_list_items`
									SET category_id = " . (int)$item['category_id'] . ",
										value_type  = " . (int)$item['value_type'] . ",
										value       = " . (float)$item['value'] . ",
										price_list_id=" . (int)$list_id
									);
							}

							//Update commission data
							$sql = "UPDATE " . DB_PREFIX . "ms_commission_rate
							SET flat = " . (isset($rate['flat']) && $rate['flat'] !== '' ? (float)$rate['flat'] : 'NULL') . ",
								percent = " . (isset($rate['percent']) && $rate['percent'] !== '' ? (float)$rate['percent'] : 'NULL') . ",
								commission_type= '" . (isset($rate['commission_type']) && $rate['commission_type'] !== '' ? $rate['commission_type'] : 'general') . "',
								price_list_id = " .(int)$list_id . ",
								payment_method = " . (isset($rate['payment_method']) && (int)$rate['payment_method'] > 0 ? (int)$rate['payment_method'] : 'NULL') . "
							WHERE rate_id = " . (int)$rate['rate_id'];							
							$this->db->query($sql);
						}						
					}
					else{
						//Update commission data
						$sql = "UPDATE " . DB_PREFIX . "ms_commission_rate
						SET flat = " . (isset($rate['flat']) && $rate['flat'] !== '' ? (float)$rate['flat'] : 'NULL') . ",
							percent = " . (isset($rate['percent']) && $rate['percent'] !== '' ? (float)$rate['percent'] : 'NULL') . ",
							commission_type= '" . (isset($rate['commission_type']) && $rate['commission_type'] !== '' ? $rate['commission_type'] : 'general') . "',
							price_list_id = NULL,
							payment_method = " . (isset($rate['payment_method']) && (int)$rate['payment_method'] > 0 ? (int)$rate['payment_method'] : 'NULL') . "
						WHERE rate_id = " . (int)$rate['rate_id'];						
						$this->db->query($sql);

						//remove price list if commission_type is not pricelist and pricelistid is set.
						if( $rate['price_list_id'] ){
							$this->db->query('DELETE FROM `'.DB_PREFIX.'ms_commission_price_list` WHERE price_list_id = '.(int)$rate['price_list_id'] );
						}

					}	
				}
			}
		}
		
		if (empty($rates)) {
			$commission_id = NULL;
		}
		
		return $commission_id;
	}
		
	// Get commissions
	public function getCommissionRates($commission_id) {
		$sql = "SELECT 	mcr.rate_id as 'mcr.rate_id',
						mcr.rate_type as 'mcr.rate_type',
						mcr.flat as 'mcr.flat',
						mcr.percent as 'mcr.percent',
						mcr.payment_method as 'mcr.payment_method',
						mcr.commission_type as 'mcr.commission_type',
						mcr.price_list_id as 'mcr.price_list_id'
				FROM `" . DB_PREFIX . "ms_commission_rate` mcr
				WHERE mcr.commission_id = " . (int)$commission_id . "
				ORDER BY mcr.rate_type";
				
		$res = $this->db->query($sql);
		$rates = array();

		foreach ($res->rows as $row) {

			//if commission has a price list, get it...
			$price_list = NULL;

			if( $row['mcr.commission_type'] == 'price_list' ){
				$price_list = $this->db->query("SELECT cpi.* , cd.name as category_name 
					FROM `" . DB_PREFIX . "ms_commission_price_list_items` cpi
					JOIN `" . DB_PREFIX . "category_description` cd ON cd.category_id = cpi.category_id
				 WHERE language_id = " . (int)$this->config->get('config_language_id') . " AND price_list_id = " . (int)$row['mcr.price_list_id'] )->rows;
			}

			$rates[$row['mcr.rate_type']] = array(
				'rate_id' => $row['mcr.rate_id'],			
				'rate_type' => $row['mcr.rate_type'],
				'flat' => $row['mcr.flat'],
				'percent' => $row['mcr.percent'],
				'payment_method' => $row['mcr.payment_method'],
				'commission_type' => $row['mcr.commission_type'],
				'price_list_id'   => $row['mcr.price_list_id'],
				'price_list'      => $price_list,
			);
		}
		return $rates;
	}
	
	public function calculateCommission($data) {
		$default_seller_group = $this->MsLoader->MsSellerGroup->getSellerGroup($this->config->get('msconf_default_seller_group_id'));
		$default_commission_id = $default_seller_group['msg.commission_id'];



		if (isset($data['seller_id'])) {
			$sql = "SELECT seller_group as `seller_group`,
							commission_id as `commission_id`
					FROM `" . DB_PREFIX . "ms_seller`
					WHERE seller_id = " . (int)$data['seller_id'];
			$res = $this->db->query($sql);
			
			//!
			$seller_group_id = $res->row['seller_group'];
			$seller_commission_id = $res->row['commission_id'];
		} else if (isset($data['seller_group_id'])) {
			$seller_group_id = $data['seller_group_id'];
			$seller_commission_id = NULL;
		} else {
			 return FALSE;
		}

		$sql = "SELECT commission_id as `commission_id`
				FROM `" . DB_PREFIX . "ms_seller_group`
				WHERE seller_group_id = " . (int)$seller_group_id;
		$res = $this->db->query($sql);
		
		$group_commission_id = $res->row['commission_id'];
		
		// Get default commissions
		$commissions = $this->getCommissionRates($default_commission_id);

		// Apply group commissions
		if ($group_commission_id != $default_commission_id) {
			$group_commissions = $this->getCommissionRates($group_commission_id);
			foreach ($group_commissions as $rate_type => $rate_val) {
					if (!is_null($rate_val['flat'])) $commissions[$rate_type]['flat'] = $rate_val['flat'];
					if (!is_null($rate_val['percent'])) $commissions[$rate_type]['percent'] = $rate_val['percent'];
					if (!is_null($rate_val['payment_method'])) $commissions[$rate_type]['payment_method'] = $rate_val['payment_method'];
			}
		}
		
		// Apply individual seller commissions
		if (!is_null($seller_commission_id)) {
			$seller_commissions = $this->getCommissionRates($seller_commission_id);
			foreach ($seller_commissions as $rate_type => $rate_val) {
					if (!is_null($rate_val['flat'])) $commissions[$rate_type]['flat'] = $rate_val['flat'];
					if (!is_null($rate_val['percent'])) $commissions[$rate_type]['percent'] = $rate_val['percent'];
					if (!is_null($rate_val['payment_method'])) $commissions[$rate_type]['payment_method'] = $rate_val['payment_method'];
			}
		}
		
		///Apply Category SALE commissions
		if (isset($data['product_id'])) {

			//Get Product Categories
			$cats = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$data['product_id'] . "'");
				
			if($cats->num_rows > 0){
				
				$cat_ids = [];
				foreach ($cats->rows as $cat) {
					$cat_ids[] = $cat['category_id'];
				}
				$cat_ids = implode(',', $cat_ids);
				
				///Get Categories Rates
				$catRates = $this->db->query("SELECT ms_fee FROM " . DB_PREFIX . "category WHERE category_id IN (" . $cat_ids . ")");

				if($catRates->num_rows > 0){

					$selectedRate = [];
					$tempTotal = 0;
					
					foreach ($catRates->rows as $rate) {
						$rates = unserialize($rate['ms_fee']);
						
						$flat = $rates['commission'][self::RATE_SALE]['flat'];
						$prct = $rates['commission'][self::RATE_SALE]['percent'];

						if($flat || $prct){
							if( ($flat + $prct) < $tempTotal || $tempTotal == 0 ){
								$tempTotal = $flat + $prct;
								$selectedRate = array('flat' => $flat, 'percent' => $prct);
							}
						}
					}

					if(count($selectedRate) > 0){
						$commissions[self::RATE_SALE] = array_merge(array(
							'rate_id' => 0,			
							'rate_type' => self::RATE_SALE,
							'payment_method' => 1,
						), $selectedRate);
					}
				}
			}	
			////END Get Product Categories
		}
		//////////////////////////////

		return $commissions;
	}

	public function getPriceList($seller_id = 0){
		//get commission id
		$commission_id = $this->_getSellerCommissionId($seller_id);
		
		//get price_list_id 
		$price_list_id = $this->db->query("
			SELECT price_list_id 
			FROM `" . DB_PREFIX . "ms_commission_rate` WHERE rate_type = 1 AND commission_id = " . (int)($commission_id?:0)
		)->row['price_list_id'];

		return $this->db->query("
			SELECT pli.* , cd.name as category_name
			FROM `" . DB_PREFIX . "ms_commission_price_list_items` pli
			JOIN `" . DB_PREFIX . "category_description` cd
				ON cd.category_id = pli.category_id
			WHERE cd.language_id = ". $this->config->get('config_language_id') ." AND price_list_id = " . (int)$price_list_id )->rows;
	}

	public function getSaleCommissionType($seller_id = 0){
		//get commission id
		$commission_id = $this->_getSellerCommissionId($seller_id);

		//get commission type for sale
		$commission_type = $this->db->query("
			SELECT commission_type 
			FROM `" . DB_PREFIX . "ms_commission_rate` WHERE rate_type = 1 AND commission_id = " . (int)($commission_id?:0)
		)->row['commission_type'];

		return $commission_type ?: 'general';
	}

	private function _getSellerCommissionId($seller_id = 0){
		//get seller group id
		$this->load->model('seller/seller');
		$group_id = $this->model_seller_seller->getSellerGroupId($seller_id);

		//get commission if for this group
		return $this->MsLoader->MsSellerGroup->getSellerGroup($group_id?:0)['commission_id'];
	}
}
?>
