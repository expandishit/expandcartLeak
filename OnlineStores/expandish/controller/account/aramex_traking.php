<?php
class ControllerAccountAramexTraking extends Controller {
	
	private $error = array();

	public function index() {

		$this->document->setTitle('Aramex Traking');
		$this->load->model('checkout/order');
		
		$this->getForm();
	}
	
	public function getForm() {
		$this->language->load_json('account/order');
		$this->load->model('checkout/order');
		$this->load->model('aramex/aramex');

		if (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
		} else {
			$order_id = 0;
		}
		
		$order_info = $this->model_checkout_order->getOrder($order_id);
		//echo "<pre>";
		//print_r($order_info);
		//echo "</pre>";
		if ($order_info) {
			
			
			$this->document->setTitle($this->language->get('heading_tracking'));
			
			############### label #############
			$this->data['breadcrumbs'] = array();

		$this->document->setTitle($this->language->get('text_order'));

			$this->data['breadcrumbs'] = array();

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home'),        	
				'separator' => false
			); 

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_account'),
				'href'      => $this->url->link('account/account', '', 'SSL'),        	
				'separator' => $this->language->get('text_separator')
			);

			$url = '';

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('account/order', $url, 'SSL'),      	
				'separator' => $this->language->get('text_separator')
			);

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_order'),
				'href'      => $this->url->link('account/order/info', 'order_id=' . $this->request->get['order_id'] . $url, 'SSL'),
				'separator' => $this->language->get('text_separator')
			);

			$this->data['order_id'] = $this->request->get['order_id'];

	############ button ########## 
	$this->data['back_to_order'] = $this->url->link('sale/order/info', 'token=' . $this->session->data['token'] . '&order_id=' . $order_id , 'SSL');
	$this->data['aramex_create_sipment'] = $this->url->link('sale/aramex_create_shipment', 'token=' . $this->session->data['token'] . '&order_id=' . $order_id , 'SSL');
	$this->data['aramex_rate_calculator'] = $this->url->link('sale/aramex_rate_calculator', 'token=' . $this->session->data['token'] . '&order_id=' . $order_id , 'SSL');
	$this->data['aramex_schedule_pickup'] = $this->url->link('sale/aramex_schedule_pickup', 'token=' . $this->session->data['token'] . '&order_id=' . $order_id , 'SSL');
	$this->data['aramex_print_label'] = $this->url->link('sale/aramex_create_shipment/lable', 'token=' . $this->session->data['token'] . '&order_id=' . $order_id , 'SSL');
	$this->data['aramex_traking'] = $this->url->link('sale/aramex_traking', 'token=' . $this->session->data['token'] . '&order_id=' . $order_id , 'SSL');
						
	
	################## track shipment ###########
	
		$account=($this->config->get('aramex_account_number'))?$this->config->get('aramex_account_number'):'';	
		$response=array();
		$clientInfo = $this->model_aramex_aramex->getClientInfo();	
		
		if(isset($this->request->post['track_awb']))
		{
			$awb_no = $this->request->post['track_awb'];
		}else{
			$awb_no = $this->model_aramex_aramex->getAWB($order_id);
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


	$baseUrl = $this->model_aramex_aramex->getWsdlPath();
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
		

            if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/account/aramex_tracking.expand')) {
                $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/account/aramex_tracking.expand';
            }
            else {
                $this->template = $this->config->get('config_template') . '/template/account/aramex_tracking.expand';
            }

			$this->children = array(
			'common/footer',
			'common/header'
		);

			$this->response->setOutput($this->render_ecwig());
		} else {
			$this->document->setTitle($this->language->get('text_order'));


			$this->data['breadcrumbs'] = array();

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home'),
				'separator' => false
			);

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_account'),
				'href'      => $this->url->link('account/account', '', 'SSL'),
				'separator' => $this->language->get('text_separator')
			);

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('account/order', '', 'SSL'),
				'separator' => $this->language->get('text_separator')
			);

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_order'),
				'href'      => $this->url->link('account/order/info', 'order_id=' . $order_id, 'SSL'),
				'separator' => $this->language->get('text_separator')
			);

			$this->data['continue'] = $this->url->link('account/order', '', 'SSL');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . '/1.1 404 Not Found');

            if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/error/not_found.expand')) {
                $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/error/not_found.expand';
            }
            else {
                $this->template = $this->config->get('config_template') . '/template/error/not_found.expand';
            }

			$this->children = array(
			'common/footer',
			'common/header'
		);

			$this->response->setOutput($this->render_ecwig());
		}
	}
	
	function getTrackingInfoTable($HAWBHistory) {

        $_resultTable = '<table summary="Item Tracking"  class="table table-striped table-hover">';
        $_resultTable .= '<thead style="font-weight: bold"><tr>
                          <td>Location</td>
                          <td>Action Date/Time</td>
                          <td class="a-right">Tracking Description</td>
                          <td class="a-center">Comments</td>
                          </tr>
                          </thead>';

        foreach ($HAWBHistory as $HAWBUpdate) {

            $_resultTable .= '<tbody><tr>
                <td>' . $HAWBUpdate->UpdateLocation . '</td>
                <td>' . $HAWBUpdate->UpdateDateTime . '</td>
                <td>' . $HAWBUpdate->UpdateDescription . '</td>
                <td>' . $HAWBUpdate->Comments . '</td>
                </tr></tbody>';
        }
        $_resultTable .= '</table>';

        return $_resultTable;
    }
	
	
}
?>