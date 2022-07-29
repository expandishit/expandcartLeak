<?php

class ControllerPaymentDigitalWorld extends Controller
{

    /**
     * @var string $stringpaymentName
     */
    private $paymentName = "digital_world";


    /**
     * @var array $errors
     */
    private $errors = array();


    /**
     * index function that appends needed template data then renders it
     *
     * @return template
     */
    public function index()
    {

        $this->template = "default/template/payment/" . $this->paymentName . ".expand";

        $this->render_ecwig();
    }

    /**
     * prepares payment data and generating payment iframe token
     *
     * @return void
     */
    public function createPaymentPage()
    {
        $this->initializer([
            "checkout/order"
        ]);
        unset($this->session->data["error_{$this->paymentName}"]);

        $this->language->load_json("payment/" . $this->paymentName);

        // Get Order Info
        $order_info = $this->model_checkout_order->getOrder($this->session->data["order_id"]);

        // prepare payment data
        $payment_data = $this->preparePaymentData($order_info);


        $response = $this->invokeCurlRequest(
            'https://merchants.digitalpay.net/apidigitalpay/generate_payment_page',
            $payment_data
        );

        if (!$response || $response['response_code'] != 4012) {
            $result['success'] = false;
            $result['message'] = $response['result'];
            $this->response->setOutput(json_encode($result));
            return;
        }

        $result['success'] = true;
        $result['payment_url'] = $response['payment_url'];
        $this->response->setOutput(json_encode($result));
        return;
    }


    /**
     * form the payment data array
     *
     * @param array order_info
     *
     * @return array payment_data
     */
    public function preparePaymentData($order_info)
    {
        $this->initializer([
            "checkout/order"
        ]);

        $payment_data = [];

        // gateway credentials
        $payment_data["merchant_email"] = $this->config->get("{$this->paymentName}_merchant_email");
        $payment_data["secret_key"] = $this->config->get("{$this->paymentName}_secret_key");
        $payment_data["site_url"] = $this->config->get("{$this->paymentName}_site_url");
        $payment_data["return_url"] = $this->url->link("payment/{$this->paymentName}/handlePaymentSuccessCallback", "", true);
        $payment_data["title"] = "{$order_info["firstname"]} {$order_info["lastname"]}";
        $payment_data["email"] = $order_info["email"];
        $payment_data["phone_number"] = $order_info["telephone"];
        $payment_data["m_phone_number"] = $order_info["telephone"];
        $payment_data["m_first_name"] = $order_info["firstname"];
        $payment_data["m_last_name"] = $order_info["lastname"];

        $order_products = $this->model_checkout_order->getOrderProducts($order_info["order_id"]);
        $payment_data["products_per_title"] = "";
        $products_total_price = 0;
        foreach ($order_products as $order_product) {
            $payment_data["products_per_title"] .= $order_product["name"] . "||";
            $payment_data["unit_price"] .= round($this->currency->convert($order_product["price"], $order_info['currency_code'], 'SAR')) . "||";
            $payment_data["quantity"] .= $order_product["quantity"] . "||";

            $products_total_price += $payment_data["quantity"] * $payment_data["unit_price"];
        }

        $payment_data["products_per_title"] = rtrim($payment_data["products_per_title"], '||');
        $payment_data["unit_price"] = rtrim($payment_data["unit_price"], '||');
        $payment_data["quantity"] = rtrim($payment_data["quantity"], '||');
        
        $shipping = $this->session->data['shipping_method']['cost'];

        $taxes = $this->cart->getTaxes();
        $taxes_total = 0;
        foreach ($taxes as $tax){
            $taxes_total += $tax;
        }

        $coupon_discount = 0;
        $reward_point_discount = 0;
        if (isset($this->session->data['coupon_discount'])) {
            $coupon_discount = $this->session->data['coupon_discount'];
        }

        if (isset($this->session->data['reward_point_discount'])) {
            $reward_point_discount = $this->session->data['reward_point_discount'];
        }

        if (isset($this->session->data['voucher'])) {
            $this->language->load_json('total/voucher');
            $this->load->model('checkout/voucher');
            $voucher_info = $this->model_checkout_voucher->getVoucher($this->session->data['voucher']);
            if ($voucher_info) {
                $amount_voucher = $voucher_info['amount'];
            }
        }
        
        $payment_data["other_charges"]	= round($this->currency->convert(($shipping + $taxes_total), $order_info['currency_code'], 'SAR'));
        $payment_data["amount"]	= $products_total_price + $payment_data["other_charges"];
        $payment_data["discount"]	= round($this->currency->convert(($coupon_discount + $reward_point_discount), $order_info['currency_code'], 'SAR'));
        $payment_data["ref_no"]	= 'ORDER-REF-' . $order_info["order_id"];
        $payment_data["refno"]	= 'ORDER-REF-' . $order_info["order_id"];
        $payment_data["currency"] = 'SAR';
        $payment_data["ip_customer"] = $this->getCurrentUserIP();
        $payment_data["ip_merchant"] = $this->getCurrentUserIP();
        $payment_data["billing_address"] = $this->determineUseAddress($order_info, 'payment'); 
        $payment_data["state"] = $order_info["payment_zone"];
        $payment_data["city"] = $order_info["payment_city"];
        $payment_data["postal_code"] = $order_info["payment_postcode"];
        $payment_data["country"] = $order_info["payment_iso_code_3"];
        $payment_data["shipping_first_name"] = $order_info["shipping_firstname"];
        $payment_data["shipping_last_name"] = $order_info["shipping_lastname"];
        $payment_data["address_shipping"] = $this->determineUseAddress($order_info, 'shipping');
        $payment_data["city_shipping"] = $order_info["shipping_city"];
        $payment_data["state_shipping"] = $order_info["shipping_zone"];
        $payment_data["postal_code_shipping"] = $order_info["shipping_postcode"];
        $payment_data["country_shipping"] = $order_info["shipping_iso_code_3"];

        return $payment_data;
    }

    /**
     * get current user IP
     *
     * @return string ip
     */
    private function getCurrentUserIP()
    {
        if(isset($_SERVER["HTTP_CLIENT_IP"])) {
            return $_SERVER["HTTP_CLIENT_IP"];
        }

        if(isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            return $_SERVER["HTTP_X_FORWARDED_FOR"];
        }

        return $_SERVER["REMOTE_ADDR"];
    }


    /**
     * handle CURL request
     *
     * @return array curl response
     */
    private function invokeCurlRequest($url, $data)
    {
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true) ;
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        $result = @curl_exec($ch);

        if(curl_errno($ch)){
            die("connection error. err:".curl_error($ch));
        }

        curl_close($ch);
        return json_decode($result, true);
    }


    /**
     * get user"s address to order info
     *
     * @param array $order_into
     * @param string $address_type
     *
     * @return string address
     */
    private function determineUseAddress(array $order_info, string $address_type):string
    {

        if (isset($order_info["{$address_type}_address_1"]) && $order_info["{$address_type}_address_1"]) {
            return $order_info["{$address_type}_address_1"];
        }

        if (isset($order_info["{$address_type}_address_2"]) && $order_info["{$address_type}_address_2"]) {
            return $order_info["{$address_type}_address_2"];
        }

        return $order_info["{$address_type}_country"];
    }


    /**
     * confirm order
     */
    public function confirm()
    {
        $this->load->model("checkout/order");
        $this->model_checkout_order->confirm(
            $this->session->data["order_id"],
            $this->config->get($this->paymentName . "_completed_order_status_id")
        );
    }


    /**
     * handel the payment success callback
     * confirm order
     * redirect to checkout/success to complete order
     */
    public function handlePaymentSuccessCallback()
    {
        $this->confirm();
        $this->redirect($this->url->link("checkout/success"));
    }

}
