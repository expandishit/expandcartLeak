<?php

class ControllerCheckoutPending extends Controller
{
    public function index()
    {
        $this->language->load_json('checkout/error');
        $this->language->load_json('checkout/success');
        $this->language->load_json('checkout/pending');
        $this->load->model('checkout/order');
       

        if (isset($this->session->data['order_id'])) {
			$this->session->data['pending_order_id'] = $this->session->data['order_id'];
            $this->cart->clear();

            unset($this->session->data['stock_forecasting_cart']);
            unset($this->session->data['shipping_method']);
            unset($this->session->data['shipping_methods']);
            unset($this->session->data['payment_method']);
            unset($this->session->data['payment_methods']);
            unset($this->session->data['guest']);
            unset($this->session->data['comment']);
            unset($this->session->data['order_id']);
            unset($this->session->data['coupon']);
            unset($this->session->data['reward']);
            unset($this->session->data['voucher']);
            unset($this->session->data['vouchers']);
            unset($this->session->data['totals']);
        }
        
        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'href' => $this->url->link('common/home'),
            'text' => $this->language->get('text_home'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'href' => $this->url->link('checkout/cart'),
            'text' => $this->language->get('text_basket'),
            'separator' => $this->language->get('text_separator')
        );

        $this->data['breadcrumbs'][] = array(
            'href' => $this->url->link('checkout/pending'),
            'text' => $this->language->get('text_checkout'),
            'separator' => $this->language->get('text_separator')
        );
        

        // This session is saved in case of failed payment method in my_fatoorh payment method controller  
        // and can be applicable to any payment method
        if(isset($this->session->data['pending_payment_response'])){
            $this->data['text_message'] = $this->session->data['pending_payment_response']['resp_code'].": ".
                $this->session->data['pending_payment_response']['resp_msg'];
            $this->data['continue'] =  $this->session->data['pending_payment_response']['continue'];
        }
        else{
            if ($this->customer->isLogged()) {
                $this->data['text_message'] = sprintf($this->language->get('text_customer'), $this->url->link('account/account', '', 'SSL'), $this->url->link('account/order', '', 'SSL'), $this->url->link('account/download', '', 'SSL'), $this->url->link('information/contact'));
            } else {
                $this->data['text_message'] = sprintf($this->language->get('text_guest'), $this->url->link('information/contact'));
            }

            $this->data['continue'] = $this->url->link('common/home');

        }

        $this->data['text_message'] = (!empty($this->session->data['customPaymentDetails']['pg_fail_msg'])) ? $this->session->data['customPaymentDetails']['pg_fail_msg'] : $this->data['text_message'];
		$this->data['success_url']  = $this->url->link('checkout/success', '', 'SSL');
        $this->data['error_url']    = $this->url->link('checkout/error', '', 'SSL');
		
		
		$handled_pending_methods = ['paypal','expandpay'];
		
		
		//by enabling this condition we track the order status change to redirect client from pending 
		//to success or failed after reciving payment webhook 
		//TO:DO | remove this condition after handling all payments that have custom statuses config names 
		//and implementing payment webhooks of each payment method if it missing 
		$orderInfo = $this->model_checkout_order->getOrder((int)$this->session->data['pending_order_id']??0);
		
		if(in_array($orderInfo['payment_code']??"",$handled_pending_methods)){
			$url_parameters = 'payment_code='.$orderInfo['payment_code'];
			$this->data['check_url']	= $this->url->link('checkout/pending/checkOrderStatusXHR',$url_parameters, 'SSL');
		}
		
		
		/*
		//depreciated here , handled at common/pending.expand
       if($orderInfo['payment_code'] == 'expandpay'){
            $this->data['check_url']   = $this->url->link('payment/expandpay/checkStatus', '', 'SSL');
            $this->data['success_url'] = $this->url->link('checkout/success', '', 'SSL');
            $this->data['error_url']   = $this->url->link('checkout/error', '', 'SSL');
            $this->template =  'default/template/module/expandpay/pending.expand';
            $this->session->data['order_id'] = $orderId;
        }*/

		
        $this->children = array(
            'common/footer',
            'common/header'
        );
		$this->template = $this->checkTemplate('common/pending.expand');
        $this->response->setOutput($this->render_ecwig());
    }

	public function checkOrderStatusXHR()
	{
		$this->load->model('checkout/order');
        
		$order_id 	 = (int)$this->session->data['pending_order_id'];
		$method_code = (string)$this->request->get['method_code'];
		$method_code = strtolower($method_code);
		
        if($order_id){
   
			//default statuses names that all payment methods supponse to use  
			$success_status = $this->config->get($method_code.'_order_status_id');
			$failed_status	= $this->config->get($method_code.'_failed_order_status_id');
				
			/*
			 * TO:DO | if any payment integration use custom configs name
			 * for success and/or failed should mentioned here
			 */
			if($method_code == 'paypal'){
				$failed_status = $this->config->get('paypal_failed_order_status');
			}
			else if($method_code == 'expandpay'){
				$failed_status = $this->config->get('expandpay_denied_order_status_id');
            }
			
			
			$order_info = $this->model_checkout_order->getOrder($order_id);
			
			if($order_info['order_status_id'] == $success_status){
				 unset($this->session->data['pending_order_id']);
                $this->response->setOutput(json_encode([
                    'status' => "success",
                ]));
            }else if($order_info['order_status_id'] == $failed_status){
				 unset($this->session->data['pending_order_id']);
                $this->response->setOutput(json_encode([
                    'status' => "failed",
                ]));
            }else{
                $this->response->setOutput(json_encode([
                    'status' => "pending",
                ]));
            }
        }
		
		
	}
}
