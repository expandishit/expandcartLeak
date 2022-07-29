<?php

use \Stripe\Stripe;
use \Stripe\Checkout\Session;

class ControllerPaymentStripe2 extends Controller
{

    /**
     * @var string $stringpaymentName
     */
    private $paymentName = 'stripe2';

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
        $this->language->load_json('payment/' . $this->paymentName);
        if (isset($this->session->data["error_{$this->paymentName}"])) {
            $this->data["error_{$this->paymentName}"] = $this->session->data["error_{$this->paymentName}"];
        }

        // set stripe api key
        Stripe::setApiKey($this->config->get('stripe2_secret_key'));

        // create payment order and obtain iframe token
        // the ontained iframe token is appneded to $this->data array
        $this->data['session_id'] = $this->createSession();

        $this->data['script_link'] = 'https://js.stripe.com/v3/';
        $this->data['published_key'] = $this->config->get('stripe2_published_key');

        $this->template = 'default/template/payment/' . $this->paymentName . '.expand';

        $this->render_ecwig();
    }


    /**
     * create stripe payment session id
     * 
     * @return String session_id
     */
    public function createSession() {
        $this->initializer([
            'checkout/order'
        ]);
        unset($this->session->data["error_{$this->paymentName}"]);
        $this->language->load_json('payment/' . $this->paymentName);
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $payment_products_data = $this->getPreparedProductsData($order_info);

        $checkout_session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $payment_products_data,
            'mode' => 'payment',
            'success_url' => $this->url->link('payment/stripe2/handlePaymentSuccessCallback', '', true),
            'cancel_url' => $this->url->link('checkout/error'),
          ]);
        
        return $checkout_session->id;
    }

    /**
     * form the products data array 
     * 
     * @param array order_info
     * 
     * @return array payment_products_data
     */
    public function getPreparedProductsData($order_info)
    {
        $this->initializer([
            'checkout/order'
        ]);

        $order_currency = strtolower($order_info['currency_code']);
        $pay_fixed_crny = strtolower($this->config->get('stripe2_completed_order_currency'));
        $should_transform_crncy = false;
        if ($pay_fixed_crny && $pay_fixed_crny != $order_currency) {
            $should_transform_crncy = true;
            $order_currency = $pay_fixed_crny;
        }

        // payment params
        $payment_products_data = [];
        $quantity = 0;
        $payment_products_data = array_map(function($order_product) use ($payment_products_data, $order_info, $quantity, $order_currency, $should_transform_crncy) {
            if ($order_product) {
                if ($should_transform_crncy) {

                    $order_product['price'] = round($this->currency->convert(
                        $order_product['price'], 
                        strtoupper($order_info['currency_code']), 
                        strtoupper($order_currency)
                    ));
                }
                return [
                    'price_data' => [
                        'currency' => $order_currency, 
                        'unit_amount' => $order_product['price'] * 100,
                        'product_data' => [
                            'name' => $order_product['name']
                        ]
                    ],
                    'quantity' => $order_product['quantity']
                ];
            }
        }, $this->model_checkout_order->getOrderProducts($order_info['order_id']));
        
        return $payment_products_data;
    }


    /**
     * confirm order
     */
    public function confirm()
    {
        $this->load->model('checkout/order');
        $this->model_checkout_order->confirm(
            $this->session->data['order_id'], 
            $this->config->get($this->paymentName . '_completed_order_status_id')
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
        $this->redirect($this->url->link('checkout/success'));
    }
  
}