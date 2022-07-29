<?php

class ControllerPriceRulesAmazonImportMap extends Controller {

	public function __construct($registory) {
		parent::__construct($registory);
    $this->load->model('amazon/price_rule_amazon/import_map');
		$this->_amazonRuleMap = $this->model_amazon/price_rule_amazon_import_map;
  }

  public function index($params) {
    $price_change = 0 ;
		$newprice = 0;
    $rule_ranges = $this->_amazonRuleMap->getPriceRules('price');
    foreach ($rule_ranges as $key => $rule_range) {
      if(!$this->_amazonRuleMap->getMapProduct($params['product_id'],'price')){
        if($this->_validateRuleRange($params['price'], $rule_range['price_from'], $rule_range['price_to'])){
				  if($rule_range['price_opration']) { // take the precentage of the price of product
            $price_change += ($params['price'] * $rule_range['price_value']) / 100 ;
				  } else{
					  $price_change += $rule_range['price_value'];
				  }

					if($rule_range['price_type']) { // take the precentage of the price of product
            $newprice = $params['price'] + $price_change ;
				  } else{
					  $newprice = $params['price'] - $price_change ;
				  }
					$updateEntry = array(
						'product_id'     => $params['product_id'],
						'price'      => $newprice,
						'change_type'     => $rule_range['price_type'],
						'price_change'   => $price_change,
						'source'         => 'amazon',
						'rule_id'        => $rule_range['id'],
						'rule_type'      =>'price',
					);
          $this->_amazonRuleMap->updateRuleMapProduct($updateEntry);
				}
      }
    }

	}
	public function edit($params){

		if($this->_amazonRuleMap->getMapProduct($params['product_id'],'price')) {

         $price_rule = $this->_amazonRuleMap->getPriceRule($params['product_id'],'price');

				 if($price_rule['change_type']){
					 $orgin_price = $params['price'] + $price_rule['change_type'];
				 } else {
					 $orgin_price = $params['price'] - $price_rule['change_type'];
				 }

				 $current_price = $this->_amazonRuleMap->getPrice($params['product_id']);

				 if($orgin_price != $current_price){
            $this->index($params);
				 }
		}
	}

  public function realtime_update($params){

      if($this->_amazonRuleMap->deleteEntry($params['product_id'])){
				$this->index($params);
			}
	}
	public function realtime_update_quantity($params){

			if($this->_amazonRuleMap->deleteEntry($params['product_id'])){
				$this->quantity_rule($params);
			}
	}
	public function _validateRuleRange($price, $min, $max){

		if($price >= $min && $price <= $max) {
			return 1;
		} else {
			return 0;
		}
	 }
	  public function quantity_rule($params) {
			$price_change = 0 ;
			$newquantity = 0;
			$rule_ranges = $this->_amazonRuleMap->getPriceRules('quantity');

			foreach ($rule_ranges as $key => $rule_range) {
				if(!$this->_amazonRuleMap->getMapProduct($params['product_id'],'quantity')){
					if($this->_validateRuleRange($params['quantity'], $rule_range['price_from'], $rule_range['price_to'])){


						if($rule_range['price_type']) { // take the precentage of the price of product
							$newquantity = $params['quantity'] + $rule_range['price_value'];

						} else{
							$newquantity =abs($params['quantity'] - $rule_range['price_value']);
								
						}

						$updateEntry = array(
							'product_id'     => $params['product_id'],
							'price'           => $newquantity,
							'change_type'     => $rule_range['price_type'],
							'price_change'   => $price_change,
							'source'         => 'amazon',
							'rule_id'        => $rule_range['id'],
							'rule_type'      =>'quantity',
						);
						$this->_amazonRuleMap->updateQuantityRuleMapProduct($updateEntry);
					}
				}
			}

		}
		public function quantity_rule_edit($params){

			if($this->_amazonRuleMap->getMapProduct($params['product_id'],'quantity')) {

	         $price_rule = $this->_amazonRuleMap->getPriceRule($params['product_id'],'quantity');

					 if($price_rule['change_type']){
						 $orginal_quantity = $params['quantity'] + $price_rule['change_type'];
					 } else {
						 $orginal_quantity = $params['quantity'] - $price_rule['change_type'];
					 }

					 $current_quantity = $this->_amazonRuleMap->getQuantity($params['product_id']);

					 if($orginal_quantity != $current_quantity){
	            $this->quantity_rule($params);
					 }
			}
		}


}
