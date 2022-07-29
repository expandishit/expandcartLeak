<?php

class ControllerShippingBosta extends Controller{

    /**
	* [POST] Method to be called from Bosta Server 
	* whenever status of any of our orders changed To 
	* change shipment order status in our system.
	*
	* @param string _id : shipmend order id that its state has changed.
	* @param integer state : The new updated shipment order state.
	* @param string starName : Star name who is handling this delivery. (in ​Delivered​ status only)
	* @param float cod : Cash on delivery that is collected by bosta if exists.  (in ​Delivered​ status only)
	* @param string exceptionReason : The reason of the exception in the delivery. (in failed status only)
	* @param float price : The final fees you will pay after including, 
	*         packaging, extra weight, cod ...etc fees. (in ​Delivered​ status only)
	* @param float weight : Your package weight. (in ​Delivered​ status only)
	*
	* @return void
	*/
	public function callback(){
		if( $this->request->server['REQUEST_METHOD'] == 'POST' 
			&& apache_request_headers()['Content-Type'] === 'application/json') {

			header('Content-type: application/json');

			
			//Parse JSON request parameters to php Array
			$bosta_data = json_decode(file_get_contents('php://input'), true);

			//Validate date ..
			if( !isset($bosta_data['_id']) || !isset($bosta_data['state']) ){
				return $this->response->setOutput(json_encode([
					'status' => 401,
					'messege'=> '_id or state is missing.'
				]));
			}

			/** Change order status **/
			//1.Get order to be changed.
			$this->load->model('shipping/bosta');
			$order = $this->model_shipping_bosta->getOrderByTrackingNumber($bosta_data['_id']);

			if(!$order){
				return $this->response->setOutput(json_encode([
					'status' => 402,
					'messege'=> 'No such delivery order with this Id.'
				]));
			}

			//2.Get status to be changed to.
			$bosta_statuses = $this->config->get('bosta_statuses');
			$status_id = $bosta_statuses[$bosta_data['state']]['expandcartid'];

			if(!$status_id){
				$status_id = $order['order_status_id'];
			}

			
			//3.Format Comment of status changing.
			$comment = 'Bosta.co callback - status updated to: ' . $bosta_data['state'];
			if($bosta_data['state'] === 45){ //​Delivered​ 
				$comment .= ', <br/> Star name is ' . $bosta_data['starName'];
				$comment .= ', <br/> Cash on delivery = ' . $bosta_data['cod'];
				$comment .= ', <br/> The final fees = ' . $bosta_data['price'];
				$comment .= ', <br/> Your package weight = ' . $bosta_data['weight'];
			}
			else if($bosta_data['state'] === 55){ //Failed
				$comment .= '<br/> Exception Reason : ' . $bosta_data['exceptionReason']; 
			}

			//4.update status & add history record
	        $this->model_shipping_bosta->updateOrderStatus(
	        	$order['order_id'], 
	        	$status_id, 
	        	$comment);

			$this->response->setOutput(json_encode('Changed Successfully, Thank you..'));
		}
		else{
			// $this->response->redirect($this->url->link('error/not_found', '', 'SSL'));

			$this->response->setOutput(json_encode('invalid request'));

		}
	}

}
