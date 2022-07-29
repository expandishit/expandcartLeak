<?php

require_once DIR_SYSTEM . '/library/payfortFort/init.php';

class ModelPaymentPayfortFort extends Model
{

    public $pfConfig;
    public $pfHelper;

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->pfConfig = Payfort_Fort_Config::getInstance();
        $this->pfHelper = Payfort_Fort_Helper::getInstance();
    }

    public function getMethod($address, $total)
    {
        $this->language->load_json('payment/payfort_fort');
        $enabled = $this->pfConfig->isCcActive();

        $status = true;

        if (!$enabled) {
            $status = false;
        }

        $method_data = array();

        if ($status) {
            $method_data = array(
                'code'       => PAYFORT_FORT_PAYMENT_METHOD_CC,
                'title'      => $this->language->get('text_title'),
                'sort_order' => $this->config->get('payfort_fort_sort_order')
            );

             

        }
        // print_r($method_data);die();
        return $method_data;
    }
}

?>
