<?php

class FaturahUtility{
    public static function convertCurrency($from_Currency, $to_Currency='SAR') {
        $amount=1;
        $amount = urlencode($amount);
        $from_Currency = urlencode($from_Currency);
        $to_Currency = urlencode($to_Currency);
        $url = "http://www.google.com/finance/converter?a=$amount&from=$from_Currency&to=$to_Currency";
        $ch = curl_init();
        $timeout = 0;
        curl_setopt ($ch, CURLOPT_URL, $url);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $rawdata = curl_exec($ch);
        curl_close($ch);
        $data = explode('bld>', $rawdata);
        $data = explode($to_Currency, $data[1]);
        return round($data[0], 2);
    }
	   
	public static function alert($msg) {
	    echo '<script type="text/javascript">alert("' . $msg . '");</script>';
    }
}

class ControllerPaymentFaturah extends Controller {

    public $faturahActionPostUrl = 'https://gateway.faturah.com/TransactionRequestHandler_Post.aspx';
    public $faturahActionGetUrl  = 'https://gateway.faturah.com/TransactionRequestHandler.aspx';

    public function alert($msg) {
        echo '<script type="text/javascript">alert("'.$msg.'");</script>';
    }

    public function index() {
        $faturahMerchantCode = $this->config->get('faturah_merchant_code');
        $faturahMerchantKey = $this->config->get('faturah_secure_key');

		$this->load->model('checkout/order');
        $this->load->model('payment/faturah');

        $this->language->load('payment/faturah');

        $merchantToken = $this->model_payment_faturah->generateFaturahMerchantToken($faturahMerchantCode);

        $orderDate =  date('m/d/Y h:i:s A');

        $orderId = $this->session->data['order_id'];
        $orderInfo = $this->model_checkout_order->getOrder($orderId);

        if (! $orderInfo) {
            return;
        }

        $this->db->query(
            "INSERT INTO " . DB_PREFIX . "order_history SET order_id = '" . (int)$orderId . "', order_status_id = '7', notify = '0', comment = '" . $this->db->escape($merchantToken) . "', date_added = NOW()"
        );

        $rate = 1;
        if ($orderInfo['currency_code'] != 'SAR') {
            $rate = $this->model_payment_faturah->convertCurrency($orderInfo['currency_code']);
        }

        $data['total'] = $this->currency->format($orderInfo['total'], $orderInfo['currency_code'], false, false);

        $data['products'] = $this->cart->getProducts();

        $customerInfo = array(
            'fullName' => $orderInfo['payment_firstname'] . ' ' . $orderInfo['payment_lastname'],
            'PhoneNumber' => $orderInfo['telephone'],
            'email' => $orderInfo['email']
        );

        $paymentURL = $this->model_payment_faturah->generatePaymentURL(
            $faturahMerchantCode,
            $merchantToken,
            $orderDate,
            $orderInfo['total'],
            $data['products'],
            $customerInfo,
            0,
            $rate,
            $orderInfo['currency_code']
        );

        $isSecureMerchant = $this->model_payment_faturah->isSecureMerchant($faturahMerchantCode);

        if ($isSecureMerchant) {
            $hash = $this->model_payment_faturah->generateSecureHashMessage(
                $faturahMerchantKey,
                $faturahMerchantCode,
                $merchantToken,
                $orderInfo['total'],
                $rate,
                $orderInfo['currency_code']
            );
            $paymentURL .= "&vpc_SecureHash=".$hash;
        }

        $data['first_name'] = html_entity_decode($orderInfo['payment_firstname'], ENT_QUOTES, 'UTF-8');
        $data['last_name'] = html_entity_decode($orderInfo['payment_lastname'], ENT_QUOTES, 'UTF-8');
        $data['address1'] = html_entity_decode($orderInfo['payment_address_1'], ENT_QUOTES, 'UTF-8');
        $data['address2'] = html_entity_decode($orderInfo['payment_address_2'], ENT_QUOTES, 'UTF-8');
        $data['city'] = html_entity_decode($orderInfo['payment_city'], ENT_QUOTES, 'UTF-8');
        $data['zip'] = html_entity_decode($orderInfo['payment_postcode'], ENT_QUOTES, 'UTF-8');
        $data['country'] = $orderInfo['payment_iso_code_2'];
        $data['email'] = $orderInfo['email'];
        $data['invoice'] = $this->session->data['order_id'] . ' - ' . html_entity_decode($orderInfo['payment_firstname'], ENT_QUOTES, 'UTF-8') . ' ' . html_entity_decode($orderInfo['payment_lastname'], ENT_QUOTES, 'UTF-8');
        $data['lc'] = $this->session->data['language'];
        $data['return'] = $this->url->link('checkout/success');
        $currentOrderTotal=$this->currency->format($orderInfo['total'], $orderInfo['currency_code'], $orderInfo['currency_value'], false);

        $data['sendurl'] = $paymentURL;
        $data['custom'] = $this->session->data['order_id'];
        $data['button_confirm'] = $this->language->get('button_confirm');


        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/faturah.tpl')) {
		    $this->template = $this->config->get('config_template') . '/template/payment/faturah.tpl';
		} else {
            $this->template = 'default/template/payment/faturah.tpl';
        }

        $this->data = $data;

        $this->render();
    }

    public function callback()
    {
        $faturahMerchantCode = $this->config->get('faturah_merchant_code');
        $faturahMerchantKey = $this->config->get('faturah_secure_key');

        $this->load->model('checkout/order');
        $this->load->model('payment/faturah');  //var_dump($this->model_payment_faturah);die;
        $this->language->load('payment/faturah');

        if ($this->request->get['Response'] != '1') {
            $this->alert('Error Occured');
            return ;
        }

        $isSecuredMerchant = $this->model_payment_faturah->isSecureMerchant($faturahMerchantCode);

        if ($isSecuredMerchant) {
            $hash = $this->model_payment_faturah->generateResponseHash(
                $faturahMerchantKey,
                $this->request->get['Response'],
                $this->request->get['status'],
                $this->request->get['code'],
                $this->request->get['token'],
                $this->request->get['lang'],
                $this->request->get['ignore']
            );

            if ($this->request->get['vpc_SecureHash'] != $hash) {
                $this->alert('Hash is not valid');
                return ;
            }

        }

        $order_id = -1;

        $token = '';

        if (isset($this->request->get['token']) && ! empty($this->request->get['token'])) {
            $token = $this->request->get['token'];
            $order_query = $this->db->query(
                "SELECT order_id FROM `" . DB_PREFIX .
                "order_history` oh WHERE oh.comment = '" .  $this->db->escape($token) . "'"
            );

            if ($order_query->num_rows) {
                $order_id = (int)$order_query->row['order_id'];
            }
        }

        $this->language->load('payment/faturah');
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($order_id);

        if ( $order_info
            && isset($this->request->get['Response'])
            && $this->request->get['Response'] == "1"
            && isset($this->request->get['status'])
        ) {

            $parsed_url = parse_url($_SERVER["REQUEST_URI"]);
            $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
            $this->log->write('Faturah :: callback ' .$query);

            if ($this->request->get['ignore'] == "0") {

                if (in_array($this->request->get["status"], array(15, 30, 1, 18, 22, 6))) {

                    $order_status_id = $this->config->get('faturah_status_' . $this->request->get["status"]);

                    if (! $order_info['order_status_id']) {
                        $this->model_checkout_order->confirm($order_id, $order_status_id, $token . ' Result from Faturah');
                        $this->response->redirect($this->url->link('checkout/success', '', true));
                    } else {
                        $this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = " . (int)$order_status_id . " WHERE order_id = "  . (int)$order_id);
                        $this->model_checkout_order->update($order_id, $order_status_id, $token);
                    }
                }
//                die();

            } else if ($this->request->get['ignore'] == "1") {

                if (in_array($this->request->get["status"], array(15, 30, 1, 18))) {
                    $data['text_msg'] = $this->language->get('text_success');
                    $data['text_wait'] = sprintf($this->language->get('text_success_wait'), $this->url->link('checkout/success'));
                    $data['continue'] = $this->url->link('checkout/success');
                } else {
                    $data['text_msg'] = $this->language->get('text_failure');
                    $data['text_wait'] = sprintf($this->language->get('text_failure_wait'), $this->url->link('checkout/checkout', '', 'SSL'));
                    $data['continue'] = $this->url->link('checkout/cart');
                }

                $data['heading_title'] = sprintf($this->language->get('heading_title'), $this->config->get('config_name'));
                $data['text_response'] = $this->language->get('entry_' . $this->request->get["status"]);

                $this->data = $data;

                if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/faturah_callback.tpl')) {
                    $this->template = $this->config->get('config_template') . '/template/payment/faturah_callback.tpl';
                } else {
                    $this->template = 'default/template/payment/faturah_callback.tpl';
                }

                $this->response->setOutput($this->render());
            }

        } else {
            $data['continue'] = $this->url->link('checkout/cart');
            $data['heading_title'] = $this->request->get["ResponseText"];
            $data['text_response'] = $this->request->get["ResponseText"];

            $this->data = $data;

            if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/faturah_callback.tpl')) {
                $this->template = $this->config->get('config_template') . '/template/payment/faturah_callback.tpl';
            } else {
                $this->template = 'default/template/payment/faturah_callback.tpl';
            }

            $this->response->setOutput($this->render());
        }


    }
}
?>
