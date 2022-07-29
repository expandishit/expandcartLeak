<?php
class ControllerTrackingInfo extends Controller {

	private $errors = [];

	public function index() {

		$this->language->load_json('tracking/info', true);

		$tracking_info = [];

		if( !empty($this->request->get['order_id']) && !empty($this->request->get['phone_number'])){
			$order_id = $this->request->get['order_id'];
			$phone_number = $this->request->get['phone_number'];
			$tracking_info = $this->_getTrackingInfo($order_id, $phone_number);
			$this->data['tracking_info'] = $tracking_info;
		}

		if( $this->request->server['REQUEST_METHOD'] == 'POST'){
			if( $this->_validate() ){

				$order_id = $this->request->post['order_id'];
				$phone_number = $this->request->post['phone_number'];

				$tracking_info = $this->_getTrackingInfo($order_id, $phone_number);
				$graph_tracking_status = $this->config->get('config_order_tracking_status');

				$this->load->model('localisation/order_status');
				foreach ($graph_tracking_status as &$track_status) {
					$order_status=$this->model_localisation_order_status->getOrderStatus($track_status['id']);
					if(!empty($order_status))
					{
						$track_status['name']=$order_status['name'];
					}
				}

	        	$result_json['success_msg'] = $this->language->get('text_success');
				$result_json['success']  = '1';
				$result_json['tracking_info']  = $tracking_info;
				$result_json['tracking_info'][]  = ['order_tracking_status' => $graph_tracking_status];
				$result_json['tracking_info'][]  = ['show_graph' => empty($graph_tracking_status) ? false:true];
			}
			else{
	        	// $result_json['success_msg'] = $this->language->get('text_error_order_id_not_found');
        		$result_json['errors'] = $this->errors;
				$result_json['success']  = '0';
			}

      		$this->response->setOutput(json_encode($result_json));
      		return;
		}


		//Send Data to View Template
		$this->data['tracking_info'] = $tracking_info;
		// var_dump($this->data['tracking_info'] );
		// echo '------';
		$graph_tracking_status = $this->config->get('config_order_tracking_status');
		$this->load->model('localisation/order_status');
		foreach ($graph_tracking_status as &$track_status) {
			$order_status=$this->model_localisation_order_status->getOrderStatus($track_status['id']);
			if(!empty($order_status))
			{
				$track_status['name']=$order_status['name'];
			}
		}
		$this->data['order_tracking_status'] = $graph_tracking_status;
		// var_dump($this->data['order_tracking_status']);die();
		$this->data['show_graph'] = empty($graph_tracking_status) ? false:true ;
		//Rendering Template
		$this->_renderViewTemplate();
	}


	private function _renderViewTemplate(){

		$lang = $this->config->get('config_language');

		if($lang === "ar"){
        	$this->document->addStyle('expandish/view/theme/default/css/RTL/tracking.css');
		}
		else{
        	$this->document->addStyle('expandish/view/theme/default/css/LTR/tracking.css');
		}

		if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/tracking/info.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/tracking/info.expand';
        }
        else {
            // $this->template = $this->config->get('config_template') . '/template/tracking/info.expand';
            $this->template = 'default/template/tracking/info.expand';
        }

		$this->children = array(
			'common/footer',
			'common/header'
		);

		$this->response->setOutput($this->render_ecwig());
	}


	private function _isAjax() {

		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
	}


	private function _getTrackingInfo($order_id, $phone_number){

		$this->load->model('tracking/info');
		$result = $this->model_tracking_info->getOrder($order_id, $phone_number);

		// return 'Order ID: ' . $order_id;
		return $result;
	}

	private function _validate(){
		if( empty($this->request->post['order_id']) || empty($this->request->post['phone_number'])){
			$this->errors['empty_parameters'] = $this->language->get('text_error_order_id_not_found');
		}


		if(!ctype_digit($this->request->post['order_id']) ){
			$this->errors['order_id'] = $this->language->get('text_error_order_id_must_be_integer');
		}


		if(!preg_match("/^\+?\d+$/", $this->request->post['phone_number'])){
			$this->errors['phone'] = $this->language->get('text_error_invalid_phone_number');
		}


		return !$this->errors;
	}
}
