<?php 
require_once(DIR_SYSTEM . 'library/gcloud_task.php');

class ControllerModuleAbandonedCart extends Controller {

	private $error = [];

	public function saveCustomerEmailToOrder(){
		$order_id = $this->session->data['order_id'];
		$email    = $this->request->request['email'];
		$phone    = $this->request->request['phone'];

		if(!$this->_validateFields($email, $phone)) {
			$json['success'] = 0;
			$json['message'] = $this->error;
        	$this->response->setOutput(json_encode($json));
        	return;
		}

		$this->load->model('checkout/order');
        $this->model_checkout_order->updateOrderEmail($order_id, $email);
		$this->session->data['payment_address']['email']     = $email;
		$this->session->data['payment_address']['telephone'] = $phone;
		
		$json['success'] = 1;
		$json['message'] = 'success';
    	$this->response->setOutput(json_encode($json));
	}

	public function addRetentionMailCronJob(){
		$order_id = $this->session->data['order_id'];
		$this->load->model('checkout/order');
        $orderInfo = $this->model_checkout_order->getOrder($order_id);

		$this->load->model('module/abandoned_cart');
		$order_has_crobjob = $this->model_module_abandoned_cart->orderHasCronJob($order_id);

		if( ! $order_has_crobjob && !empty($orderInfo['email']) ){
			
			$after_hours = $this->config->get('abandoned_cart')['schedule_time'];			
			$url = HTTPS_SERVER."index.php?route=module/abandoned_cart/sendRetaintionMail&order_id=" .(int)$order_id;			
			$task = new GCloudTask();
			
			try{
				//Must be uncomment it before publishing
            	$response = $task->create($url,['order'=>$order_id, 'name'=>'order-'.(string)$order_id], $after_hours * 60 * 60);

				if($response){
					$this->model_module_abandoned_cart->addOrderCronJob($order_id);
					echo 'Task created successfully.';
				}
            }
            catch(Exception $ex){
            	$log = new Log('abandoned_cart_gcloudtask_error.log');
				$log->write(json_encode($ex->getMessage()));
            }
		}
	}

	public function sendRetaintionMail(){
		$order_id = $this->request->get['order_id'];

		$this->load->model('checkout/order');
        $orderInfo = $this->model_checkout_order->getOrder($order_id);

		$this->load->model('module/abandoned_cart');
		$order_already_emailed = $this->model_module_abandoned_cart->orderHasBeenEmailed($order_id);

        if($orderInfo['order_status_id'] == 0 && !$order_already_emailed){
            // mark this order as not sent
            $this->model_module_abandoned_cart->setOrderAsEmailed($order_id, false);
			$mail = new Mail();
			$mail->protocol = $this->config->get('config_mail_protocol');
			$mail->parameter= $this->config->get('config_mail_parameter');
			$mail->hostname = $this->config->get('config_smtp_host');
			$mail->username = $this->config->get('config_smtp_username');
			$mail->password = $this->config->get('config_smtp_password');
			$mail->port     = $this->config->get('config_smtp_port');
			$mail->timeout  = $this->config->get('config_smtp_timeout');
			$mail->setReplyTo(
	            $this->config->get('config_mail_reply_to'),
	            $this->config->get('config_name') ? $this->config->get('config_name')[0] : 'ExpandCart',
	            $this->config->get('config_email')
	        );

			$mail->setTo($orderInfo['email']);
			$mail->setFrom((!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email')));
			$mail->setSender($this->config->get('config_name'));
			$mail->setSubject($this->config->get('abandoned_cart')['mail_subject']);
			$mail->setHtml(htmlspecialchars_decode($this->renderMessageTemplate($this->config->get('abandoned_cart')['mail_message'], $orderInfo)));

			if( $mail->send() ){
				//send as emailed
				$this->model_module_abandoned_cart->setOrderAsEmailed($order_id);
				echo 'mail sent';
			}
		}

		//remove crobjob from list
		// $crontab = new Ssh2CrontabManager(DOMAINNAME, SSH_PORT, SSH_USER, SSH_PASSWORD);
		// $path = preg_quote(BASE_STORE_DIR . "cronjobs/", '/');	
		// $crontab->remove_cronjob('/'.$path."job-order-{$order_id}\.sh/");
		//remove from DB
		$this->model_module_abandoned_cart->removeOrderCronJob($order_id);
		// //remove shell file
		// unlink(BASE_STORE_DIR . "cronjobs/job-order-{$order_id}.sh");
	}

    /**
     * Render the email message.
     *
     * @param string $message
     * @param array $orderInfo
     *
     * @return string
     */
    private function renderMessageTemplate($message, $orderInfo){

        $message = preg_replace('#\{firstname\}#', $orderInfo['firstname'], $message);
        $message = preg_replace('#\{lastname\}#', $orderInfo['lastname'], $message);
        $message = preg_replace('#\{orderid\}#', $orderInfo['order_id'], $message);
        $message = preg_replace('#\{invoice\}#', $orderInfo['invoice_prefix'] . $orderInfo['invoice_no'], $message);
        $message = preg_replace('#\{telephone\}#', $orderInfo['telephone'], $message);
        $message = preg_replace('#\{email\}#', $orderInfo['email'], $message);

        if (strstr($message, '{products}') !== false) {

            $orderProducts = $this->model_checkout_order->getOrderProducts($orderInfo['order_id']);

            $message = preg_replace_callback('#\{products\}#', function ($matches) use ($orderProducts) {

                $template = [];

                $template[] = '<table>';
                $template[] = '<thead>';
                $template[] = '<tr>';
                $template[] = '<th>Product Name</th>';
                $template[] = '<th>Total</th>';
                $template[] = '</tr>';
                $template[] = '</thead>';

                foreach ($orderProducts as $orderProduct) {
                    $template[] = '<tr>';
                    $template[] = '<td>' . $orderProduct['name'] . '</td>';
                    $template[] = '<td>' . $orderProduct['price'] . '</td>';
                    $template[] = '</tr>';
                }

                $template[] = '</table>';

                return implode(' ', $template);
            }, $message);
        }
        if (strstr($message, '{productsImages}') !== false){
            $this->load->model('tool/image');
            $this->language->load_json('module/abandoned_cart');

            $orderProducts = $this->model_checkout_order->getOrderProducts($orderInfo['order_id']);            

            foreach($orderProducts as $key => $product ){ 
                $image = $this->model_tool_image->resize($product['image'], 70, 70); 
                $orderProducts[$key]['image'] = $image;
            }
            $this->data['orderProducts'] = $orderProducts;
            $this->data['more_products'] = count($orderProducts) > 5 ?  count($orderProducts) - 5 : '';
            $logo_img = $this->model_tool_image->resize($this->config->get('config_logo'), 70, 70); 
            if(!strstr($logo_img, 'no_image')){
                $this->data['logo'] = $logo_img; 
            }
            $this->data['store_name'] = $this->config->get('config_name')[$this->config->get('config_§uage')];
            $this->data['cart_redirect'] =$this->url->link('checkout/cart','src=mailMsg', 'SSL');
            $this->template = 'default/template/module/abandoned_cart/product_images.expand';
            $productImagesTemplate = $this->render_ecwig();
            $message = preg_replace('#\{productsImages\}#', $productImagesTemplate, $message);
        }
        return $message;
    }

	private function _validateFields($email, $phone){
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
			$this->error['email_error'] = 'A valid email is required';
		}
		//8 digits or more...
		if (!preg_match('/^[0-9]{8,}$/', $phone)){
			$this->error['phone_error'] = 'A valid phone number is required';			
		}
		return !$this->error;
	}

	private function _createNewJobFile($order_id){
		$crobjob_dir = BASE_STORE_DIR . "cronjobs";

		try{
			if( !is_dir($crobjob_dir) ){
			    mkdir($crobjob_dir , 0775);
		    }

		    $cronjob_file = $crobjob_dir . "/job-order-{$order_id}.sh";
		    if(!is_file($cronjob_file)){
				file_put_contents($cronjob_file, "#!/bin/bash\ncurl --location --request GET '".HTTP_SERVER."index.php?route=module/abandoned_cart/sendRetaintionMail&order_id=" .(int)$order_id . "'");
				chmod($cronjob_file, 0755);
			}
			return $cronjob_file;
		}
		catch(Exception $ex){
			$log = new Log('ex.'.time().'.abandoned_cart_cronjob_error_create_file.json');
			$log->write(json_encode($ex->getMessage()));
			return false;
		}
	}
}
