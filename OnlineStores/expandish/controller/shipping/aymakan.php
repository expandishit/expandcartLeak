<?php

class ControllerShippingAyMakan extends Controller{

    /**
	 * [POST] Webhook Method which will be called by AyMakan system whenever a shipment status is changed.
	 *
	 * @param string tracking: The Tracking number of Shipment.
	 * @param string reference: Shipment reference which is provided by customer while creating a shipment.
	 * @param string status: Status code of the shipment. Please request sales team to share all of our statuses with code.
	 * @param string carrier: It is carrier name. In Aymakan case, it will always have aymakan.
	 *
	 * @return void
	*/
	public function callback(){
		if( $this->request->server['REQUEST_METHOD'] == 'POST' 
			&& apache_request_headers()['Content-Type'] === 'application/json') {

			header('Content-type: application/json');
			
			//Parse JSON request parameters to php Array
			$aymakan_data = json_decode(file_get_contents('php://input'), true);

			//Validate date ..
			if( !isset($aymakan_data['reference']) || !isset($aymakan_data['status']) ){
				http_response_code(422);
				return $this->response->setOutput(json_encode([
					'error' => '1',
					'messege'=> 'tracking or status is missing.'
				]));
			}

			/** Change order status **/
			//1.Get order to be changed.
			$this->load->model('checkout/order');
			$order = $this->model_checkout_order->getOrder($aymakan_data['reference']);

			if(!$order){
				http_response_code(404);

				return $this->response->setOutput(json_encode([
					'error' => '1',
					'messege'=> 'No such delivery order with this Id.'
				]));
			}

			//2.Get status to be changed to.
			$aymakan_statuses = $this->config->get('aymakan_statuses');
			$new_status = $aymakan_data['status'];

			$new_status_key = array_search($new_status, array_column($aymakan_statuses, 'code'));
			$status_id = $aymakan_statuses[$new_status_key]['expandcartid'];

			if(!$status_id){
				$status_id = $order['order_status_id'];
			}

			
			//3.Format Comment of status changing.
			$comment = 'AyMakan.co callback - status updated to: ' . $aymakan_data['status'];
			$comment .= ', <br/> Tracking is ' . $aymakan_data['tracking'];
			$comment .= ', <br/> carrier is ' . $aymakan_data['carrier'];
			$comment .= ', <br/> reference is ' . $aymakan_data['reference'];


			//4.update status & add history record
			$this->load->model('shipping/aymakan');			
	        $this->model_shipping_aymakan->updateOrderStatus(
	        	$order['order_id'], 
	        	$status_id, 
	        	$comment);

			$this->response->setOutput(json_encode('Changed Successfully, Thank you..'));
		}
		else{
			$this->response->setOutput(json_encode('invalid request'));
		}
	}

}
