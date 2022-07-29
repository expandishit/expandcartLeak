<?php
class ModelPaymentJumiaPay extends Model {

    const BASE_API_TESTING_URL    = 'https://api-sandbox-pay.jumia.com.eg';
    const BASE_API_PRODUCTION_URL = 'https://api-pay.jumia.com.eg';

    public function refundPayment($order_id){
        $settings      = $this->config->get('jumiapay');
        $shop_config   = $settings['shop_config'];
        $api_key       = $settings['api_key'];
       
        $this->load->model('extension/payment_transaction');
        $payment_transaction = $this->model_extension_payment_transaction->selectByOrderId($order_id);
        $details = json_decode($payment_transaction['details'], true);

        $data = [
            'shopConfig'          => $shop_config,
            'refundAmount'        => (float)$payment_transaction['amount'],
            'refundCurrency'      => $payment_transaction['currency'],
            'description'         => 'Refund order #'.$order_id,
            'purchaseId'          => $details['jumiapay_purchase_id'],
            'purchaseReferenceId' => $details['jumiapay_purchase_id'],
            'referenceId'         => $details['jumiapay_reference_id']
        ];

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $this->_getBaseUrl() . '/merchant/refund',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => json_encode($data),
          CURLOPT_HTTPHEADER => array(
            'apikey: ' . $api_key,
            'Content-Type: application/json'
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        //Add log
        $log = new Log('jumia-refund.'.time().'.json');
        $log->write(json_encode($data));
        $log->write(json_encode($response));

        return $response;
    }

    private function _getBaseUrl(){
        //Check current API Mode..
        $debugging_mode = $this->config->get('jumiapay')['debugging_mode'];
        return ( isset($debugging_mode) && $debugging_mode == 1 ) ? self::BASE_API_TESTING_URL : self::BASE_API_PRODUCTION_URL;
    }
}
