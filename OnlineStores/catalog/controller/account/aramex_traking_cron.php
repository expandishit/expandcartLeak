<?php
class ControllerAccountAramexTrakingCron extends Controller {
	
	private $error = array();

	public function index() {
		ini_set('max_execution_time', 0); 
		$this->document->setTitle('Aramex Traking');
		$this->load->model('checkout/order');

		/*
		 1:Pending , 2:Processing , 3:Shipped , 15:Processed 
		 AND order_status_id IN (1,2,3,15)
		*/
		$query = $this->db->query("SELECT order_id
					FROM " . DB_PREFIX . "order_history
					WHERE COMMENT LIKE '%AWB No.%' AND order_status_id IN (1,2,3,15)
					GROUP BY order_id");
		$ord_ids = $query->rows;
		
		
		if(isset($ord_ids) && !empty($ord_ids))
		{
				foreach($ord_ids as $key=>$ord)
				{
					$order_id = ($ord['order_id'])?$ord['order_id']:0;
					if($order_id != 0)
					{
						$this->getHistory($order_id);
					}
				}
		}
	}
	
	public function getHistory($order_id) {
		
		
		$this->load->model('checkout/order');
		$this->load->model('aramex/aramex');
		
		$order_info = $this->model_checkout_order->getOrder($order_id);
		$awb_no = $this->model_aramex_aramex->getAWB($order_id);
		
		
		if ($order_info && $awb_no!=0) {

		################## track shipment ###########
		$response=array();
		$clientInfo = $this->model_aramex_aramex->getClientInfo();	
		
			
		
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
						//$this->data['eRRORS'][] = 'Aramex: ' . $notify_error->Code .' - '. $notify_error->Message."<br>";				
					}
				}else{
						//$this->data['eRRORS'][] = 'Aramex: ' . $results->Notifications->Notification->Code . ' - '. $results->Notifications->Notification->Message;
				}
				
				}else{

					$HAWBHistory = $results->TrackingResults->KeyValueOfstringArrayOfTrackingResultmFAkxlpY->Value->TrackingResult;
					//echo $order_info['email']."<br>";
					$subject = sprintf('%s - Order Tracking Update for order no %s', $order_info['store_name'], $order_id);

					$message  = 'Order ID:' . $order_id . "\n". "<br>";
					$message .= 'Date Ordered:'. date("Y-m-d", strtotime($order_info['date_added'])) . "\n\n". "<br>";
					$message .= 'Please find the order tracking information below ' . "\n". "<br>";;
					$html = $this->getTrackingInfoTable($HAWBHistory);
                 					
					$html = $message.$html;
					############### email ###########
					$mail = new Mail();
					$mail->protocol = $this->config->get('config_mail_protocol');
					$mail->parameter = $this->config->get('config_mail_parameter');
					$mail->hostname = $this->config->get('config_smtp_host');
					$mail->username = $this->config->get('config_smtp_username');
					$mail->password = $this->config->get('config_smtp_password');
					$mail->port = $this->config->get('config_smtp_port');
					$mail->timeout = $this->config->get('config_smtp_timeout');
					$mail->setTo($order_info['email']);
					$mail->setFrom($this->config->get('config_email'));
					$mail->setSender($order_info['store_name']);
					$mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
                    if ($this->config->get('custom_email_templates_status') && !isset($cet)) {
                        include_once(DIR_SYSTEM . 'library/custom_email_templates.php');

                        $cet = new CustomEmailTemplates($this->registry);

                        $cet_result = $cet->getEmailTemplate(array('type' => 'admin', 'class' => __CLASS__, 'function' => __FUNCTION__, 'vars' => get_defined_vars()));

                        if ($cet_result) {
                            if ($cet_result['subject']) {
                                $mail->setSubject(html_entity_decode($cet_result['subject'], ENT_QUOTES, 'UTF-8'));
                            }

                            if ($cet_result['html']) {
                                $mail->setNewHtml($cet_result['html']);
                            }

                            if ($cet_result['text']) {
                                $mail->setNewText($cet_result['text']);
                            }

                            if ($cet_result['bcc_html']) {
                                $mail->setBccHtml($cet_result['bcc_html']);
                            }

                            if ((isset($this->request->post['attach_invoicepdf']) && $this->request->post['attach_invoicepdf'] == 1) || $cet_result['invoice'] == 1) {
                                $path_to_invoice_pdf = $cet->invoice($order_info, $cet_result['history_id']);

                                $mail->addAttachment($path_to_invoice_pdf);
                            }

                            if (isset($this->request->post['fattachments'])) {
                                if ($this->request->post['fattachments']) {
                                    foreach ($this->request->post['fattachments'] as $attachment) {
                                        foreach ($attachment as $file) {
                                            $mail->addAttachment($file);
                                        }
                                    }
                                }
                            }

                            $mail->setBccEmails($cet_result['bcc_emails']);
                        }
                    }
					$mail->setHtml($html);
					
					$mail->send();
                    if ($this->config->get('custom_email_templates_status')) {
                        $mail->sendBccEmails();
                    }
					
					############### email end #########
					
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
			
		}
	}
	
	
	function getTrackingInfoTable($HAWBHistory) {

        $_resultTable = '<table summary="Item Tracking"  class="list" border="1">';
        $_resultTable .= '<thead><tr>
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