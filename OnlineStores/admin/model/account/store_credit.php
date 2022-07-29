<?php

class ModelAccountStoreCredit extends Model
{
	public function getAvailableOffers(array $filter = []){

		$this->load->model('account/invoice');

		if(!isset(WHITELIST_STORES[STORECODE]) || !WHITELIST_STORES[STORECODE]){
			return [];
		}

		$clientProducts = $this->model_account_invoice->getClientsProducts();
        foreach ($clientProducts->products->product as $product) {
            if ($product->pid == PRODUCTID) {
                $billingcycle=$product->billingcycle;
            }
        }

        // ============================================ static experimental =========================================

        $expiration_date = '2021-12-12 00:00:00';
        $store_registeration_date = date("Y-m-d H:i:s");

        if(PRODUCTID == '3' && STORE_CREATED_AT < date("Y-m-d H:i:s")) {
        	$store_credits = [
	        	[
	        		'id' => 1,
	        		'amount' => [
	        			'USD' => 15,
	        			'EGP' => 230,
	        			'SAR' => 50
	        		],
	        		'plan_ids' => ['53'],
	        		'cycle' => 1,
	        		'expiration_date' => $expiration_date
	        	]
	        ];

        	if(isset($filter['plan_ids'])){
        		if(in_array('53', $filter['plan_ids']) && $filter['cycle'] == 'monthly') {
        			return $store_credits;
        		}
        		return [];
        	}
        	else{
	        	return $store_credits;
        	}

        } elseif(in_array(PRODUCTID, ['53', '6']) && $billingcycle == 'monthly' && STORE_CREATED_AT < date("Y-m-d H:i:s")) {
        	$store_credits = [
	        	[
	        		'id' => 2,
	        		'amount' => [
	        			'USD' => 150,
	        			'EGP' => 2305,
	        			'SAR' => 510
	        		],
	        		'plan_ids' => ['6'],
	        		'cycle' => 2,
	        		'expiration_date' => $expiration_date
	        	]
	        ];

        	if(isset($filter['plan_ids'])){
        		if(in_array('6', $filter['plan_ids']) && $filter['cycle'] == 'annually') {
        			return $store_credits;
        		}
        		return [];
        	}
        	else{
	        	return $store_credits;
        	}

        }

        return [];

        // ============================================ end static experimental =========================================

        $applied_credits_ids = array_column($this->db->query("SELECT store_credit_id FROM `". DB_PREFIX ."ec_store_credit`")->rows, 'store_credit_id');

		$conditions = [
			"`expiration_date` >= NOW()",
			"(`store_registeration_date` IS NULL OR (`store_registeration_date` > '" . STORE_CREATED_AT . "' AND `store_registeration_type` = 0) OR (`store_registeration_date` < '" . STORE_CREATED_AT . "' AND `store_registeration_type` = 1))",
			"(`store_plan_ids` IS NULL OR JSON_CONTAINS(`store_plan_ids`, '\"" . PRODUCTID . "\"', '$'))",
			"(`store_cycle` = 0 OR " . PRODUCTID ." = 3 OR (`store_cycle` = 1 AND '". $billingcycle ."' = 'monthly') OR (`store_cycle` = 2 AND '". $billingcycle ."' = 'annually'))",
			"(`use_limit` IS NULL OR `num_uses` < `use_limit`)"
		];

		if(count($applied_credits_ids)){
			$conditions[] = "(`one_time_use` = 0 OR `id` NOT IN (". implode(',', $applied_credits_ids) ."))";
		}

		if(isset($filter['banner_display']) && $filter['banner_display']){
			$conditions[] = "`banner_display` = 1";
		}

		if(isset($filter['product_type'])){
			if($filter['product_type'] == 'appservice') {
				$conditions[] = "`product_type` IN (0,2)";
			} elseif($filter['product_type'] == 'plan') {
				$conditions[] = "`product_type` IN (0,1)";
			}
		}

		if(isset($filter['plan_ids'])){
			$condition = implode(" OR ", array_map(function($id) {
			    return "JSON_CONTAINS(`plan_ids`, '\"" . $id . "\"')";
			}, $filter['plan_ids']));
			$conditions[] = "`product_type` IN (0,1) AND (". $condition .")";
		}

		if(isset($filter['appservice_ids'])){
			$condition = implode(" OR ", array_map(function($id) {
			    return "JSON_CONTAINS(`appservice_ids`, '\"" . $id . "\"')";
			}, $filter['appservice_ids']));
			$conditions[] = "`product_type` IN (0,2) AND (". $condition .")";
		}

		if(isset($filter['cycle'])){
			$conditions[] = "(`cycle` = 0 OR (`cycle` = 1 AND '". $filter['cycle'] ."' = 'monthly') OR (`cycle` = 2 AND '". $filter['cycle'] ."' = 'annually') OR (`cycle` = 3 AND '". $filter['cycle'] ."' = 'one_time'))";
		}

		$query = "SELECT * FROM store_credit WHERE " . implode(' AND ', $conditions);
		$store_credits = $this->ecusersdb->query($query)->rows;

		foreach ($store_credits as &$store_credit) {
			$store_credit['store_plan_ids'] = json_decode($store_credit['store_plan_ids']);
			$store_credit['appservice_ids'] = json_decode($store_credit['appservice_ids']);
			$store_credit['plan_ids'] = json_decode($store_credit['plan_ids']);
			$store_credit['amount'] = json_decode($store_credit['amount'], true);
		}

		return $store_credits;
	}

	public function use_credit($id, $product_id, $cycle) {

        // ============================================ static experimental =========================================
        return true;
        // ============================================ end static experimental =========================================

		$this->ecusersdb->query("UPDATE `store_credit` SET num_uses = num_uses + 1 WHERE id = $id");
		$this->db->query("INSERT INTO `". DB_PREFIX ."ec_store_credit` SET store_credit_id = $id, product_id = $product_id, cycle = '$cycle'");
	}
}