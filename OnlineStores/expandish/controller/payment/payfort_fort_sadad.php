<?php

require_once DIR_SYSTEM . '/library/payfortFort/init.php';

class ControllerPaymentPayfortFortSadad extends Controller
{

    public $paymentMethod;
    public $integrationType;
    public $pfConfig;
    public $pfPayment;

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->pfConfig        = Payfort_Fort_Config::getInstance();
        $this->pfPayment       = Payfort_Fort_Payment::getInstance();
        $this->integrationType = PAYFORT_FORT_INTEGRATION_TYPE_REDIRECTION;
        $this->paymentMethod   = PAYFORT_FORT_PAYMENT_METHOD_SADAD;
    }

    public function index()
    {
        $this->language->load_json('payment/payfort_fort');

        // if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/payment/payfort_fort_sadad.expand')) {
        //     $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/payment/payfort_fort_sadad.expand';
        // }
        // else {
        //     $this->template = $this->config->get('config_template') . '/template/payment/payfort_fort_sadad.expand';
        // }
        
        $this->template = 'default/template/payment/payfort_fort_sadad.expand';
        
        $this->render_ecwig();
    }

    public function send()
    {
        $form = $this->pfPayment->getPaymentRequestForm($this->paymentMethod);

        $json = array('form' => $form);
        $this->response->setOutput(json_encode($json));
    }

    public function response()
    {
        $this->_handleResponse('offline');
    }

    public function responseOnline()
    {

        $this->_handleResponse('online');
    }

    public function merchantPageResponse()
    {
        $this->_handleResponse('online', $this->integrationType);
    }

    private function _handleResponse($response_mode = 'online', $integration_type = PAYFORT_FORT_INTEGRATION_TYPE_REDIRECTION)
    {
        $response_params = array_merge($this->request->get, $this->request->post); //never use $_REQUEST, it might include PUT .. etc

        $success = $this->pfPayment->handleFortResponse($response_params, $response_mode, $integration_type);
        if ($success) {
            $redirectUrl = 'payment/payfort_fort/success';
        }
        else {
            $redirectUrl = 'checkout/checkout';
        }
        header('location:' . $this->url->link($redirectUrl));
    }

}
