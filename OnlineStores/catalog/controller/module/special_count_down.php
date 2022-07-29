<?php
class ControllerModuleSpecialCountDown extends Controller {
	protected function index($setting) {
		$this->language->load('module/special_count_down');
 
      	$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['button_cart'] = $this->language->get('text_buy');
		$this->data['text_expiry'] = $this->language->get('text_expiry');
		
		
		$this->document->addScript('catalog/view/javascript/jquery/jquery.countdown.js');
		
		$this->load->model('module/special_count_down');
		
		
		$this->load->model('catalog/product');
		
		
		$this->load->model('tool/image');
		$this->data['text_auction'] = $this->language->get('text_auction');
        $this->data['text_auctionended'] = $this->language->get('text_auctionended');
		$this->data['products'] = array();
		
		$data = array(
			'sort'  => 'pd.name',
			'order' => 'ASC',
			'start' => 0,
			'limit' => $setting['limit']
		);
		

		$results = $this->model_module_special_count_down->getProductSpecialssuh($data);

		foreach ($results as $result) {
		
		
			if ($result['image']) {
				$image = $this->model_tool_image->resize($result['image'], $setting['image_width'], $setting['image_height']);
			} else {
				$image = false;
			}

			if ($this->customer->isCustomerAllowedToViewPrice()) {
				$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')));
			} else {
				$price = false;
			}
					
			$specialper = false;
			if ((float)$result['special']) {
				$spl = $result['price'] - $result['special'];
				$specialper = ($spl*100)/$result['price'];
				$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')));
				
			}else{
				$special = false;
			}
			
			if ((float)$result['startbidprice']) {
				$startbidprice = $this->currency->format($result['startbidprice']);
			$clearstartbidprice = $result['startbidprice'];
			} else {
				$startbidprice = false;
				$clearstartbidprice = 0;
			}

			
			
			if ((float)$result['endbidtime']) {
				$endbidtime = $result['endbidtime'];
			} else {
				$endbidtime = "0000-00-00 0:0:0";
			}
			
						
			if ($this->config->get('config_review_status')) {
				$rating = $result['rating'];
			} else {
				$rating = false;
			}
			
			$this->data['checkclosedate'] = $this->model_catalog_product->checkclosedatetime($result['product_id']);

			$currentdate= date("Y-m-d h:i:s");

	


			if($this->data['checkclosedate'] <= $currentdate){
				
			$checkstatebid = $this->model_catalog_product->closeBidstate($result['product_id']);

			}else{

			$checkstatebid = $this->model_catalog_product->checkBidstate($result['product_id']);


			}
			
		$countCustomerBids = $this->model_catalog_product->countCustomerBids($result['product_id']);

		 $maxcustomerbid = $this->model_catalog_product->forMaxProductBids($result['product_id']);
		 
		 $minofferstep = $this->model_catalog_product->minofferstep($result['product_id']);

		$this->data['minoffersteps'] = $this->currency->format($minofferstep);
		
		 $maxcustomerbid = $this->model_catalog_product->forMaxProductBids($result['product_id']);
		 
		if($countCustomerBids > 0){
						
				$mincustomerbid = $maxcustomerbid +  $minofferstep;
				
				$maxcustomerbidamt = $this->currency->format($maxcustomerbid);
				
				}else {
				
				$mincustomerbid = $clearstartbidprice+ $minofferstep;
				
				$maxcustomerbidamt = $this->currency->format($clearstartbidprice);
			
			
			}
		
			
			
						$date_stop = getdate(strtotime($result['endbidtime']));
						$seconds=$date_stop['seconds'];
						$minutes=$date_stop['minutes'];
						$hours=$date_stop['hours'];
						$mday=$date_stop['mday'];
						$wday=$date_stop['wday'];
						$mon=$date_stop['mon'];
						$year=$date_stop['year'];
			 
			$this->data['products'][] = array(
				'product_id' => $result['product_id'],
				'thumb'   	 => $image,
				'name'    	 => $result['name'],
				'price'   	 => $price,
				'special' 	 => $special,
				'specialper' 	 => $specialper,
				'seconds' 	 => $seconds,
				'minutes' 	 => $minutes,
				'hours' 	 => $hours,
				'day' 	 => $mday,
				'wday' 	 => $wday,
				'mon' 	 => $mon,
				'year' 	 => $year,
				'startbidprice' => $startbidprice,
				'checkstatebid' => $checkstatebid,
				'clearstartbidprice' => $clearstartbidprice,
				'maxcustomerbidamt' => $maxcustomerbidamt,
				'date_end'   => $result['endbidtime'],
				'rating'     => $rating,
				'reviews'    => sprintf($this->language->get('text_reviews'), (int)$result['reviews']),
				'href'    	 => $this->url->link('product/product', 'product_id=' . $result['product_id']),
			);
		}

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/special_count_down.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/module/special_count_down.tpl';
		} else {
			$this->template = 'default/template/module/special_count_down.tpl';
		}

		$this->render();
	}
	
	public function updateprice() {
	
		$json = array();
		
		$this->load->model('module/special_count_down');
		
		$maxpricebid = $this->model_module_special_count_down->getlatestprice($this->request->get['productid']);
		
	
		
		
		if(!empty($maxpricebid[1])) {
			$json['maxbid'] = $this->currency->format($maxpricebid[1]);
			$json['biddername'] = $maxpricebid[2];
		 }else {
			$json['maxbid'] = '';
			$json['biddername'] = '';
		 }
		
		
		$this->response->setOutput(json_encode($json));
	}
}
?>