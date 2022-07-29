<?php

require_once DIR_SYSTEM . '/library/payfortFort/init.php';

class ControllerPaymentPayfortFortValu extends Controller
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
        $this->paymentMethod   = PAYFORT_FORT_PAYMENT_METHOD_VALU;
        $this->pfHelper        = Payfort_Fort_Helper::getInstance();
    }

    public function index()
    {
        $this->language->load_json('payment/payfort_fort');

        // if (file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/default/template/payment/payfort_fort_valu.expand')) {
        //     $this->template = 'customtemplates/' . STORECODE . '/default/template/payment/payfort_fort_valu.expand';
        // } else {
        //     $this->template =  'default/template/payment/payfort_fort_valu.expand';
        // }
        
        $this->template =  'default/template/payment/payfort_fort_valu.expand';
        
        $this->render_ecwig();
    }

    public function verifyCustomerGenerateOtp()
    {
        // this function will verify customer phone number and generate otp if phone is verified
        // 1- check and verify user phone number through valU
        $customerVerified = $this->pfPayment->valuCustomerVerify();
        if($customerVerified['verified']){
            $json['customer_verified'] = 1 ;
            $json['merchant_reference'] = $customerVerified['merchant_reference'] ;
            // 2- generate otp to be sent to user by valU
            $generateOtpResponse = $this->pfPayment->valuGenerateOtp($customerVerified['merchant_reference']);
            if($generateOtpResponse['status'] == "88" && $generateOtpResponse['otp_status']){
                // OTP Generated Successfully.
                $json['otp_generated'] = 1;
                $json['transaction_id'] = $generateOtpResponse['transaction_id'];
            }
            else{
                // otp not generated or the amount is less than minimum
                $json['otp_generated'] = 0;
                $json['message'] = $this->language->get('error_low_price');
            }
        }
        else{
            // customer phone is not verified with valU
            $json['customer_verified'] = 0 ;
            $json['message'] = $customerVerified['message'];
        }
        $this->response->setOutput(json_encode($json));
    }

    public function verifyOtpGenerateTenures()
    {
        $this->language->load_json('payment/payfort_fort');

        $otp = $this->request->post['otp'];
        $merchant_reference = $this->request->post['merchant_reference'];
        // 3- verify the entered otp by the user 
        $otpVerifyResponse = $this->pfPayment->valuVerifyOtp($otp,$merchant_reference);
        // if otp status is 1 then we are recieving the installments plans "tenures"
        if($otpVerifyResponse['otp_status'] == 1){
            $json['otp_verified'] = 1;
            $plans = [];
            $i = 0;
            foreach ($otpVerifyResponse['tenure']['TENURE_VM'] as $plan) {
                $plans[$i]['id'] = $plan['TENURE'];
                $plans[$i]['value'] = $plan['EMI']." ".$this->language->get('text_monthly').$this->language->get('text_period_of')." ".$plan['TENURE']." ".$this->language->get('text_months')." ".$this->language->get('text_interest')." ".$plan['InterestRate']." %";
                $i++;
            }
            $json['tenures'] = $plans;
            $json['message'] = "OTP Is Verified";
        }
        else{
            $json['otp_verified'] = 0;
            $json['message'] =  $this->language->get('error_wrong_otp');
        }
        $this->response->setOutput(json_encode($json));
    }

    public function purchase()
    {
        $otp = $this->request->post['otp'];
        $tenure_id = $this->request->post['tenure_id'];
        $merchant_reference = $this->request->post['merchant_reference'];
        $transaction_id = $this->request->post['transaction_id'];
        $purchaseResponse['status'] = $this->pfPayment->valuPurchase($otp,$tenure_id,$merchant_reference,$transaction_id);
        if(!$purchaseResponse){
            $purchaseResponse['message'] = $this->language->get('error_wrong_purchase');
        }
        $this->response->setOutput(json_encode($purchaseResponse));
    }
}
