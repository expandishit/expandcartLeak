<?php
class ControllerSaleAramexTraking extends Controller {
	
	private $error = array();

	public function index() {

		$this->language->load('sale/aramex');
		$this->document->setTitle($this->language->get('heading_tracking'));
		$this->load->model('sale/order');
		
		$this->getForm();
	}
	
	public function getForm() {
		
		$this->load->model('sale/order');
		$this->load->model('sale/aramex');
		$this->load->model('shipping/aramex');
		$this->document->addScript('view/javascript/jquery.chained.js');
		
		if (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
		} else {
			$order_id = 0;
		}
		
		$order_info = $this->model_sale_order->getOrder($order_id);
		//echo "<pre>";
		//print_r($order_info);
		//echo "</pre>";
		if ($order_info) {
			
			
			$this->document->setTitle($this->language->get('heading_tracking'));
			
			############### label #############
			$this->data['text_back_to_order'] = $this->language->get('text_back_to_order');
			$this->data['text_create_sipment'] = $this->language->get('text_create_sipment');
			$this->data['text_rate_calculator'] = $this->language->get('text_rate_calculator');
			$this->data['text_schedule_pickup'] = $this->language->get('text_schedule_pickup');
			$this->data['text_print_label'] = $this->language->get('text_print_label');
			$this->data['text_track'] = $this->language->get('text_track');
			$this->data['text_track_history'] = $this->language->get('text_track_history');
            $this->data['text_return_shipment'] = $this->language->get('text_return_shipment');
			
			$this->data['heading_title'] = $this->language->get('heading_tracking');
			
			
			$this->data['breadcrumbs'] = array();

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home', '', 'SSL'),
				'separator' => false
			);

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('heading_title_rate'),
				'href'      => $this->url->link('sale/order', '', 'SSL'),
				'separator' => ' :: '
			);

			$this->data['order_id'] = $this->request->get['order_id'];

	############ button ########## 
	$this->data['back_to_order'] = $this->url->link('sale/order/info', 'order_id=' . $order_id , 'SSL');
	$this->data['aramex_create_sipment'] = $this->url->link('sale/aramex_create_shipment', 'order_id=' . $order_id , 'SSL');
	$this->data['aramex_rate_calculator'] = $this->url->link('sale/aramex_rate_calculator', 'order_id=' . $order_id , 'SSL');
	$this->data['aramex_schedule_pickup'] = $this->url->link('sale/aramex_schedule_pickup', 'order_id=' . $order_id , 'SSL');
	$this->data['aramex_print_label'] = $this->url->link('sale/aramex_create_shipment/lable', 'order_id=' . $order_id , 'SSL');
	$this->data['aramex_traking'] = $this->url->link('sale/aramex_traking', 'order_id=' . $order_id , 'SSL');
						
	
	################## track shipment ###########
	
		$account=($this->config->get('aramex_account_number'))?$this->config->get('aramex_account_number'):'';	
		$response=array();
		$clientInfo = $this->model_sale_aramex->getClientInfo();	
		if(isset($this->request->post['track_awb']))
		{
			$awb_no = $this->request->post['track_awb'];
		}else{
			$awb_no = $this->model_sale_aramex->getAWB($order_id);
		}
		
		$this->data['awb_no'] = $awb_no;
		try {
				
		$params = array(
		'ClientInfo'  			=> $clientInfo,
								
		'Transaction' 			=> array(
									'Reference1'			=> '001' 
								),
		'Shipments'				=> array(
									$awb_no
								)
	);


	$baseUrl = $this->model_sale_aramex->getWsdlPath();
	$soapClient = new SoapClient($baseUrl.'/Tracking.wsdl', array('trace' => 1));
	
	try{
	$results = $soapClient->TrackShipments($params);	

	if($results->HasErrors){
		if(count($results->Notifications->Notification) > 1){
			$error="";
			foreach($results->Notifications->Notification as $notify_error){
				$this->data['eRRORS'][] = 'Aramex: ' . $notify_error->Code .' - '. $notify_error->Message."<br>";				
			}
		}else{
				$this->data['eRRORS'][] = 'Aramex: ' . $results->Notifications->Notification->Code . ' - '. $results->Notifications->Notification->Message;
		}
		
	}else{

		$HAWBHistory = $results->TrackingResults->KeyValueOfstringArrayOfTrackingResultmFAkxlpY->Value->TrackingResult;
		$this->data['html'] = $this->getTrackingInfoTable($HAWBHistory);	
		
	}
	} catch (Exception $e) {
			$response['type']='error';
			$response['error']=$e->getMessage();			
	}
	}
	catch (Exception $e) {
			$response['type']='error';
			$response['error']=$e->getMessage();			
	}
				

		
		
		
		################## create shipment end ###########
		
		
		$this->data['is_shipment'] = $this->model_sale_aramex->checkAWB($this->request->get['order_id']);
		//echo '<pre>';
		//print_r($this->data['order_products']);
		
			$this->template = 'sale/aramex_tracking.expand';
			$this->children = array(
				'common/header',
				'common/footer'
			);

			$this->response->setOutput($this->render());
		} else {
			$this->language->load('error/not_found');

			$this->document->setTitle($this->language->get('heading_title'));

			$this->data['heading_title'] = $this->language->get('heading_title');

			$this->data['text_not_found'] = $this->language->get('text_not_found');

			$this->data['breadcrumbs'] = array();

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home', '', 'SSL'),
				'separator' => false
			);

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('error/not_found', '', 'SSL'),
				'separator' => ' :: '
			);

			$this->template = 'error/not_found.expand';
            $this->base = "common/base";

			$this->response->setOutput($this->render_ecwig());
		}
	}
	
	function getTrackingInfoTable($HAWBHistory) {
		$this->load->language('sale/aramex');
        $_resultTable = '<table summary="Item Tracking"  class="table table-hover">';
        $_resultTable .= '<thead><tr>
                          <td>'.$this->language->get('entry_location').'</td>
                          <td>'.$this->language->get('entry_action_date_time').'</td>
                          <td class="a-right">'.$this->language->get('entry_description').'</td>
                          <td class="a-center">'.$this->language->get('entry_comments').'</td>
                          </tr>
                          </thead>';
		if(is_array($HAWBHistory)){
			foreach ($HAWBHistory as $HAWBUpdate) {
				$_resultTable .= '<tbody><tr>
					<td>' . $HAWBUpdate->UpdateLocation . '</td>
					<td>' . $HAWBUpdate->UpdateDateTime . '</td>
					<td>' . $HAWBUpdate->UpdateDescription . '</td>
					<td>' . $HAWBUpdate->Comments . '</td>
					</tr></tbody>';
			}
		}
		else{
			$_resultTable .= '<tbody><tr>
			<td>' . $HAWBHistory->UpdateLocation . '</td>
			<td>' . $HAWBHistory->UpdateDateTime . '</td>
			<td>' . $HAWBHistory->UpdateDescription . '</td>
			<td>' . $HAWBHistory->Comments . '</td>
			</tr></tbody>';
		}
        $_resultTable .= '</table>';

        return $_resultTable;
    }
	
	
}
?>