<?php

require_once DIR_SYSTEM . 'library/vendor/webtopay/libwebtopay/WebToPay.php';

class ControllerPaymentPaysera extends Controller {

    public function index() {

        $data['action']         = $this->url->link('payment/paysera/confirm', '', 'SSL');
        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['button_back']    = $this->language->get('button_back');

        $this->language->load('payment/paysera');
        $data['text_chosen']     = $this->language->get('text_chosen');
        $data['text_paycountry'] = $this->language->get('text_paycountry');

        $data['default_country']  = $this->config->get('default_payment_country');
        $data['paysera_display_payments'] = $this->config->get('paysera_display_payments_list');


        if ($this->request->get['route'] != 'checkout/guest/confirm') {
            $data['back'] = HTTPS_SERVER . 'index.php?route=checkout/payment';
        } else {
            $data['back'] = HTTPS_SERVER . 'index.php?route=checkout/guest';
        }



        $data['projectId'] = $this->config->get('paysera_project');
        $this->id = 'payment';
//countries
        $this->load->model('checkout/order');
        $order  = $this->model_checkout_order->getOrder($this->session->data['order_id']);
	$data['payment_country'] = $order['payment_iso_code_2']; 
        $amount = ceil($order['total'] * $this->currency->getvalue($order['currency_code']) * 100);
        $amount = $amount<1?100:$amount;
        $language  = $order['language_code'];
        $projectId = $this->config->get('paysera_project');

        $methods = WebToPay::getPaymentMethodList($projectId, $order['currency_code'])
            ->filterForAmount($amount, $order['currency_code'])
            ->setDefaultLanguage($language);
        $data['evp_countries'] = $methods->getCountries();
//end countries


        $this->data = $data;
        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/paysera.tpl')) {
            $this->template = $this->config->get('config_template') . '/template/payment/paysera.tpl';
        } else {
            $this->template = 'default/template/payment/paysera.tpl';
        }
        $this->render();

    }

    public function confirm() {

        $this->load->model('checkout/order');
        $order = $this->model_checkout_order->getOrder($this->session->data['order_id']);
      
        $language = $this->config->get('paysera_lang');

        if (!isset($_SERVER['HTTPS'])) {
            $_SERVER['HTTPS'] = false;
        }

        $info = array(
            'projectid'     => $this->config->get('paysera_project'),
            'sign_password' => $this->config->get('paysera_sign'),

            'orderid'       => $order['order_id'],
            'amount'        => intval(number_format($order['total'] * $this->currency->getvalue($order['currency_code']), 2, '', '')), //ceil($order['total'] * $this->currency->getvalue($order['currency_code']) * 100)
            'currency'      => $order['currency_code'],
            'lang'          => $language,

            'accepturl'     => HTTPS_SERVER . 'index.php?route=payment/paysera/accept',
            'cancelurl'     => HTTPS_SERVER . 'index.php?route=payment/paysera/cancel',
            'callbackurl'   => HTTPS_SERVER . 'index.php?route=payment/paysera/callback',
            'payment'       => (isset($_REQUEST['payment'])) ? $_REQUEST['payment'] : '',
            'country'       => $order['payment_iso_code_2'],

            'logo'          => '',
            'p_firstname'   => $order['payment_firstname'],
            'p_lastname'    => $order['payment_lastname'],
            'p_email'       => $order['email'],
            'p_street'      => $order['payment_address_1'] . ' ' . $order['payment_address_2'],
            'p_city'        => $order['payment_city'],
            'p_state'       => '',
            'p_zip'         => $order['payment_postcode'],
            'p_countrycode' => $order['payment_iso_code_2'],
            'test'          => ($this->config->get('paysera_test') != 0 ? 1 : 0),
        );

        try {
            $request = WebToPay::redirectToPayment($info);
        } catch (WebToPayException $e) {
            exit($e->getMessage());
        }
        $this->load->model('checkout/order');

	    $this->model_checkout_order->addOrderHistory($order['order_id'], $this->config->get('paysera_new_order_status_id'));
        //$this->model_checkout_order->confirm($order['order_id'], 1);





      $data['request']    = $request;
      //  $data['countries']    = $countries;
        $data['requestUrl'] = WebToPay::PAY_URL;

        $this->data = $data;
        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/paysera_redirect.tpl')) {
            $this->template = $this->config->get('config_template') . '/template/payment/paysera_redirect.tpl';
        } else {
            $this->template = 'default/template/payment/paysera_redirect.tpl';
        }
        $this->render();


     //   print_r($request);
        //$this->response->setOutput($this->render(TRUE));
    }

    private function getPaymentList() {

        $this->load->model('checkout/order');
        $order  = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $amount = ceil($order['total'] * $this->currency->getvalue($order['currency_code']) * 100);

        $language  = $this->language->get('code');
        $projectId = $this->config->get('paysera_project');

        $methods = WebToPay::getPaymentMethodList($projectId, $order['currency_code'])
            ->filterForAmount($amount, $order['currency_code'])
            ->setDefaultLanguage($language);

        return $methods->getCountries();   }

    public function accept() {
        if (isset($this->session->data['token'])) {
            $this->redirect(HTTPS_SERVER . 'index.php?route=checkout/success&token=' . $this->session->data['token']);
        } else {
            $this->redirect(HTTPS_SERVER . 'index.php?route=checkout/success');
        }
    }

    public function cancel() {
        $this->language->load('payment/paysera');

        $data['title'] = sprintf($this->language->get('heading_title'), $this->config->get('config_store'));

        if(isset($this->request->server['HTTPS']) and $this->request->server['HTTPS'] == 'on') {
            $data['base'] = HTTPS_SERVER;
        } else {
            $data['base'] = HTTP_SERVER;
        }

        $data['charset']   = $this->language->get('charset');
        $data['language']  = $this->language->get('code');
        $data['direction'] = $this->language->get('direction');

        $data['heading_title'] = sprintf($this->language->get('heading_title'), $this->config->get('config_store'));

        $data['text_response']     = $this->language->get('text_response');
        $data['text_success']      = $this->language->get('text_success');
        $data['text_success_wait'] = sprintf($this->language->get('text_success_wait'), $this->data['base'] . 'index.php?route=checkout/success');
        $data['text_failure']      = $this->language->get('text_failure');
        $data['text_failure_wait'] = sprintf($this->language->get('text_failure_wait'), $this->data['base'] . 'index.php?route=checkout/cart');

        $data['button_continue'] = $this->language->get('button_continue');

        $data['continue'] = $data['base'] . 'index.php?route=checkout/cart';

        $this->data = $data;
        //$this->template = $this->config->get('config_template') . '/template/payment/paysera_failure.tpl';
        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/paysera_failure.tpl')) {
            $this->template = $this->config->get('config_template') . '/template/payment/paysera_failure.tpl';
        } else {
            $this->template = 'default/template/payment/paysera_failure.tpl';
        }

        $this->render();
	    //$this->response->setOutput($this->load->view('default/template/payment/paysera_failure.tpl', $data));
    }

    public function callback() {

        $project_id    = $this->config->get('paysera_project');
        $sign_password = $this->config->get('paysera_sign');
        $this->load->model('checkout/order');

        try {
            $response = WebToPay::validateAndParseData($_REQUEST, $project_id, $sign_password);

            if ($response['status'] == 1) {
                $orderId = isset($response['orderid']) ? $response['orderid'] : null;

                $order = $this->model_checkout_order->getOrder($orderId);

                $amount = intval(number_format($order['total'] * $this->currency->getvalue($order['currency_code']), 2, '', ''));

                if (empty($order)) {
                    throw new Exception('Order with this ID not found');
                }

                if ($response['amount'] < $amount) {
                    throw new Exception('Bad amount: ' . $response['amount'] . ', expected: ' . ceil($order['total'] * 100));
                }

                if ($response['currency'] != $order['currency_code']) {
                    throw new Exception('Bad currency: ' . $response['currency'] . ', expected: ' . $order['currency_code']);
                }


                $message = '';


                $this->model_checkout_order->addOrderHistory($orderId, $this->config->get('paysera_order_status_id'));
                //$this->model_checkout_order->update($orderId, $this->config->get('paysera_order_status_id'), $message, FALSE);

                exit('OK');
            }
            exit('OK');
        } catch (Exception $e) {
            exit(get_class($e) . ': ' . $e->getMessage());
        }
    }
}
