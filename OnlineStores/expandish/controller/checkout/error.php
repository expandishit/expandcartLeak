<?php

class ControllerCheckoutError extends Controller
{
    public function index()
    {
        $this->language->load_json('checkout/error');
        $this->language->load_json('checkout/success');
        //$this->template = 'clearion/template/common/error.tpl';

        //WideBot App check...
        //Call WideBot After-Checkout-Api
        if( \Extension::isInstalled('widebot') &&
         $this->config->get('widebot')['status'] == 1 && 
         !empty($this->session->data['x-bot-token']) ){

            $failure_follow_name = $this->config->get('widebot')['failure_follow_name'];
            $this->load->model('module/widebot');
            $this->model_module_widebot->callAfterCheckoutAPI($failure_follow_name , $this->session->data['x-bot-token']);
            unset($this->session->data['x-bot-token']);
        }
        
        if (isset($this->session->data['order_id'])) {

            if(!isset($this->session->data["paypal_order_value"])) {

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
                unset($this->session->data["coupon_discount"]);
                unset($this->session->data['voucher']);
                unset($this->session->data["voucher_discount"]);
                unset($this->session->data["store_credit_discount"]);
                unset($this->session->data['vouchers']);
                unset($this->session->data['totals']);

            } else {

                $product_id = $this->session->data["paypal_product_id"];

                $option = $this->session->data["product_option_array"];

                $key = null;

                if ( ! $option || !is_array( $option ) )
                {
                    $key = (int)$product_id;
                }
                else
                {
                    $key = (int) $product_id . ':' . base64_encode( serialize( $option ) );
                }

                if($key != null) {
                    $this->cart->remove($key);
                }


                unset($this->session->data['coupon']);
                unset($this->session->data["coupon_discount"]);
                unset($this->session->data['reward']);
                unset($this->session->data['voucher']);
                unset($this->session->data["voucher_discount"]);
                unset($this->session->data["store_credit_discount"]);
                unset($this->session->data['vouchers']);
                unset($this->session->data['subscription']);
                unset($this->session->data['subscription_discount']);
                unset($this->session->data['order_attributes']);
                unset($this->session->data["paypal_order_total"]);
                unset($this->session->data["paypal_order_value"]);
                unset($this->session->data["paypal_product_id"]);
                unset($this->session->data["product_option_array"]);
                unset($this->session->data["previouslyAddedProducts"]);


            }
			
			unset($this->session->data["pending_order_id"]);
        }

        $this->language->load_json('checkout/error');

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
            'href' => $this->url->link('checkout/checkout'),
            'text' => $this->language->get('text_checkout'),
            'separator' => $this->language->get('text_separator')
        );

        $this->data['breadcrumbs'][] = array(
            'href' => $this->url->link('checkout/error'),
            'text' => $this->language->get('text_success'),
            'separator' => $this->language->get('text_separator')
        );

        // This session is saved in case of failed payment method in my_fatoorh payment method controller  
        // and can be applicable to any payment method
        if(isset($this->session->data['error_payment_response'])){
            $this->data['text_message'] = $this->session->data['error_payment_response']['resp_code'].": ".
            $this->session->data['error_payment_response']['resp_msg'];               
            $this->data['continue'] =  $this->session->data['error_payment_response']['continue'];
        }
        else{
            if ($this->customer->isLogged()) {
                $this->data['text_message'] = sprintf($this->language->get('text_customer'), $this->url->link('account/account', '', 'SSL'), $this->url->link('account/order', '', 'SSL'), $this->url->link('account/download', '', 'SSL'), $this->url->link('information/contact'));
            } else {
                $this->data['text_message'] = sprintf($this->language->get('text_guest'), $this->url->link('information/contact'));
            }    
               
           $this->data['continue'] = $this->url->link('common/home');

        }
        
        $this->data['text_message'] =  (!empty($this->session->data['customPaymentDetails']['pg_fail_msg'])) ? $this->session->data['customPaymentDetails']['pg_fail_msg'] : $this->data['text_message'];

        $this->template = $this->checkTemplate('common/error.expand');

        $this->children = array(
            'common/footer',
            'common/header'
        );

        $this->response->setOutput($this->render_ecwig());
    }

    public function show()
    {
        $this->language->load_json('checkout/error');
        $this->language->load_json('checkout/success');

        $this->language->load_json('checkout/error');

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
            'href' => $this->url->link('checkout/checkout'),
            'text' => $this->language->get('text_checkout'),
            'separator' => $this->language->get('text_separator')
        );

        $this->data['breadcrumbs'][] = array(
            'href' => $this->url->link('checkout/error'),
            'text' => $this->language->get('text_success'),
            'separator' => $this->language->get('text_separator')
        );

        // This session is saved in case of failed payment method in my_fatoorh payment method controller  
        // and can be applicable to any payment method
        if (isset($this->session->data['error_payment_response'])) {
            $this->data['text_message'] = $this->session->data['error_payment_response']['resp_code'].": ".
            $this->session->data['error_payment_response']['resp_msg'];               
            $this->data['continue'] =  $this->session->data['error_payment_response']['continue'];
        } else {
            if ($this->customer->isLogged()) {
                $this->data['text_message'] = sprintf($this->language->get('text_customer'), $this->url->link('account/account', '', 'SSL'), $this->url->link('account/order', '', 'SSL'), $this->url->link('account/download', '', 'SSL'), $this->url->link('information/contact'));
            } else {
                $this->data['text_message'] = sprintf($this->language->get('text_guest'), $this->url->link('information/contact'));
            }    
               
           $this->data['continue'] = $this->url->link('common/home');
        }

        if (file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/common/error.expand')) {
        } else {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/common/error.expand';
            $this->template = $this->config->get('config_template') . '/template/common/error.expand';
        }

        $this->children = array(
            'common/footer',
            'common/header'
        );

        $this->response->setOutput($this->render_ecwig());
    }
}
