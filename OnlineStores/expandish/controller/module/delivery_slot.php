<?php  
class ControllerModuleDeliverySlot extends Controller {



	public function getSlotsData() {
		$json = array();

		$this->load->model('module/delivery_slot/slots');

		$day_id = $this->request->get['dayOfWeek'];
		$day_value = $this->request->get['dateValue'];

		if($day_id >= 0 && $day_id < 5){
			$day_id = $day_id + 3;
		}else{
			$day_id = $day_id - 4;
		}

		$data = [
			"day_id"   => $day_id,
			"dayValue" => $day_value
		];

		if ( $this->MsLoader->isInstalled() && \Extension::isInstalled('delivery_slot') && $this->config->get('delivery_slot')['status'] == 1
		     && $this->config->get('msconf_delivery_slots_to_sellers') )
        {   
			$cart_products=$this->cart->getProducts();
			foreach($cart_products as $cartproduct)
			{ 
				$cart_product_seller_id[]=$this->MsLoader->MsProduct->getSellerId($cartproduct['product_id']);
			}
			$sellerArray = array_unique($cart_product_seller_id);
			$seller_id=$sellerArray[0];
            $data += ['seller_id' => $seller_id, ];
        }
        $slots = $this->model_module_delivery_slot_slots->getSlots($data);

        if (count($slots) > 0) {
			foreach ($slots as $key=> $slot){
				$json[]= array(
					'slot_id'        => $slot['ds_delivery_slot_id'],
					'ds_day_id'      => $slot['ds_day_id'],
					'delivery_slot_data'  => $slot['delivery_slot'],
				);
			}

        }

		$this->response->setOutput(json_encode($json));
	}
	

}
?>
