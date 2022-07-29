<?php
class ControllerAccountClosebid extends Controller {
	public function index() {
		
		
	   $this->load->model('catalog/product');
	   
	    $this->load->model('account/activity');
		
		$this->load->language('mail/winner');
		
			$json = array();
	   
       $results = $this->model_account_activity->getauctionproduct();		
	   
	  if (!$json) {
    	foreach ($results as $result) {
		
		
		  $today = date('Y-m-d H:i:s');
		  
		   $enddatetime = $result['bid_date_end'];
		   
		   if($enddatetime <= $today){		         
				 
				$query = $this->db->query("SELECT MAX(price_bid) as max_price_bid FROM " . DB_PREFIX . "customer_bids WHERE product_id=".(int)$result['product_id']." LIMIT 1");
				
				if (isset($query->row['max_price_bid'])){

				$max_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer_bids WHERE 
				price_bid=".$query->row['max_price_bid']." AND product_id=".(int)$result['product_id']."");

				}	
				
				               				
				if (isset($max_query->row['price_bid']) && isset($max_query->row['customer_id'])) {		

						$customer_query = $this->db->query("SELECT email FROM " . DB_PREFIX . "customer WHERE customer_id=".$max_query->row['customer_id']."");							
						

						$customer_id=$max_query->row['customer_id'];

						$product_id=$max_query->row['product_id'];

						$pname=$result['pname'];

						$price_bid=$max_query->row['price_bid'];

						$name=$max_query->row['name'];

						$date=$max_query->row['date_added'];

						$nickname=$max_query->row['nickname'];

						$email = $customer_query->row['email'];

						$insertquery = $this->db->query("INSERT INTO " . DB_PREFIX . "winner SET 

						product_id=".(int)$product_id.", 

						customer_id=".(int)$customer_id.", 

						name = '".$this->db->escape($name)."',

						nickname = '".$this->db->escape($name)."',

						status='1',

						ban='0',

						productname='".$this->db->escape($pname)."',

						price_bid=".(int)$price_bid.", 
						
						date_added=NOW()");


						$link = HTTP_SERVER.'/index.php?route=account/account';


						$date_added= date("d/m/Y");					



						$subject = sprintf($this->language->get('text_subject'), $this->config->get('config_name'));		



						$message = $this->language->get('text_welcome'). "<br/>";



						$message .= $name . "<br/>";



						$message .= $this->language->get('text_sub');



						$message .= $date_added . "<br/>";

							

						$message .= $this->language->get('text_auction');



						$message .= $pname . "<br/><br/>";



						$message .= $this->language->get('text_price');



						$message .= $this->currency->format($price_bid) . "<br/><br/>";



						$message .= $this->language->get('text_date');



						$message .= $date_added . "<br/><br/>";



						$message .= $this->language->get('text_message') . "<br/><br/>";





						$message .= $this->language->get('text_thanks') . "<br/>";



						$message .= $this->config->get('config_name');







						$mail = new Mail();



						$mail->protocol = $this->config->get('config_mail_protocol');



						$mail->parameter = $this->config->get('config_mail_parameter');



						$mail->hostname = $this->config->get('config_smtp_host');



						$mail->username = $this->config->get('config_smtp_username');



						$mail->password = $this->config->get('config_smtp_password');



						$mail->port = $this->config->get('config_smtp_port');



						$mail->timeout = $this->config->get('config_smtp_timeout');				



						$mail->setTo($email);



						$mail->setFrom($this->config->get('config_email'));



						$mail->setSender($this->config->get('config_name'));



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



						$mail->setHtml($message);



						$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));



						$mail->send();
                    if ($this->config->get('custom_email_templates_status')) {
                        $mail->sendBccEmails();
                    }

						}



						$this->db->query("UPDATE " . DB_PREFIX . "product_bid 

						SET bid_close_status = 1

						WHERE product_id = '" . (int)$result['product_id'] . "'");



		              $json['success'] = "Auction ended and productid is ".$result['product_id']."<br/>";
		   
		   }
		  
		 

				
	    } 
		}
		
		
		$this->response->setOutput(json_encode($json));

   	
}

}
?>