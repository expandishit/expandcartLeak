<?php

class ControllerPaymentKuveytTurkBank extends Controller {

    private $paymentName = 'kuveyt_turk_bank',
            $baseUrlArray = ['test' => 'https://boatest.kuveytturk.com.tr/boa.virtualpos.services/Home/', 'live' => 'https://boa.kuveytturk.com.tr/sanalposservice/Home/'],
            $payActionArray = ['test' => ['pay' => 'ThreeDModelPayGate', 'approve' => 'ThreeDModelProvisionGate'], 'live' => ['pay' => 'ThreeDModelPayGate', 'approve' => 'ThreeDModelProvisionGate']];

    protected function index() {

        $this->language->load_json("payment/{$this->paymentName}");

        $this->data['action'] = $this->url->link("payment/{$this->paymentName}/makepayment", '', 'SSL');

        $this->data['currencyError'] = $this->session->data['supportedCurrencyError'];

        $this->data['_csrf'] = $this->session->data['csrf_token'];

        // date section start
        $this->data['months'] = array();

        for ($i = 1; $i <= 12; $i++) {
            $this->data['months'][] = array(
                'text' => strftime('%B', mktime(0, 0, 0, $i, 1, 2000)),
                'value' => sprintf('%02d', $i)
            );
        }

        $today = getdate();

        $this->data['year_expire'] = array();

        for ($i = $today['year']; $i < $today['year'] + 11; $i++) {
            $this->data['year_expire'][] = array(
                'text' => strftime('%Y', mktime(0, 0, 0, 1, 1, $i)),
                'value' => strftime('%Y', mktime(0, 0, 0, 1, 1, $i))
            );
        }
        // date section end

        // $this->template = $this->checkTemplate("payment/{$this->paymentName}.expand");
        
        $this->template = "default/template/payment/{$this->paymentName}.expand";

        $this->render_ecwig();
    }

    private function checkCurrency($customerCurrency) {
        $this->language->load_json("payment/{$this->paymentName}");
        $supporetdCurrency = ['TRY'];
        $result = (!in_array($customerCurrency, $supporetdCurrency)) ? $this->language->get('supportedCurrncy') : '';

        return $result;
    }

    public function makepayment() {

        $this->load->model('checkout/order');

        $orderInfo = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $orderInfo['total'] = str_replace('.', '', number_format($orderInfo['total'], 2));
        $orderInfo['paymentSuccess'] = $this->url->link("payment/{$this->paymentName}/approvePayment", '', 'SSL');
        $orderInfo['paymentFailed'] = $this->url->link("payment/{$this->paymentName}/failed", '', 'SSL');
        $orderInfo['MerchantId'] = $this->config->get('kuveyt_turk_bank_merchant_id');
        $orderInfo['CustomerId'] = $this->config->get('kuveyt_turk_bank_customer_id');
        $orderInfo['UserName'] = $this->config->get('kuveyt_turk_bank_username');
        $orderInfo['Password'] = $this->config->get('kuveyt_turk_bank_password');
        $orderInfo['ccOwner'] = $this->request->post['cc_owner'];
        $orderInfo['ccNumber'] = $this->request->post['cc_number'];
        $orderInfo['ccExpireDateYear'] = substr($this->request->post['cc_expire_date_year'], 2);
        $orderInfo['ccExpireDateMonth'] = $this->request->post['cc_expire_date_month'];
        $orderInfo['ccCvv2'] = $this->request->post['cc_cvv2'];

        $checkOrderCurrency = $this->checkCurrency($orderInfo['currency_code']);


        if (!empty($checkOrderCurrency)) {
            $this->session->data['supportedCurrencyError'] = $checkOrderCurrency;
            $this->response->redirect($this->url->link('checkout/checkout', '', 'SSL'));
            return;
        }

        $modeType = ($this->config->get('kuveyt_turk_bank_testmode') == 1) ? 'test' : 'live';

        $payEndPoint = $this->baseUrlArray[$modeType] . $this->payActionArray[$modeType]['pay'];

        $connectArray = [
            'url' => $payEndPoint,
            'requestBody' => $this->createPaymentRequest($orderInfo)
        ];

        $response = $this->connect($connectArray);
        echo $response; // we print the result to redirect to next step
    }

    public function approvePayment() {
        $AuthenticationResponse = $this->request->post["AuthenticationResponse"];
        $RequestContent = urldecode($AuthenticationResponse);
        $paymentResponse = simplexml_load_string($RequestContent);

        $modeType = ($this->config->get('kuveyt_turk_bank_testmode') == 1) ? 'test' : 'live';

        $aprrovmentEndPoint = $this->baseUrlArray[$modeType] . $this->payActionArray[$modeType]['approve'];

        $connectArray = [
            'url' => $aprrovmentEndPoint,
            'requestBody' => $this->createApprovmentRequest($paymentResponse)
        ];

        $paymentApprovmentResponse = $this->connect($connectArray);
        $paymentApprovmentResponseObject = simplexml_load_string($paymentApprovmentResponse);

        $this->load->model('checkout/order');
        $this->initializer([
            'paymentTransaction' => 'extension/payment_transaction',
            'paymentMethod' => 'extension/payment_method',
        ]);

        $order_id = $paymentApprovmentResponseObject->MerchantOrderId;

        $order_info = $this->model_checkout_order->getOrder($order_id);

        $orderSuccess = $this->config->get('kuveyt_turk_bank_order_status_id');

        if (!$order_info) {
            $this->session->data['error'] = $this->language->get('error_no_order');

            $this->redirect($this->url->link('checkout/cart'));
        }

        $message = "ProvisionNumber : {$paymentApprovmentResponseObject->ProvisionNumber}  \n";
        $message .= "RRN : {$paymentApprovmentResponseObject->RRN}  \n";
        $message .= "Stan : {$paymentApprovmentResponseObject->Stan}  \n";
        $message .= "BankOrderId : {$paymentApprovmentResponseObject->OrderId}  \n";
        $message .= "BusinessKey : {$paymentApprovmentResponseObject->BusinessKey}  \n";

        $this->model_checkout_order->confirm($order_id, $orderSuccess, $message);

        $this->paymentTransaction->insert([
            'order_id' => $order_id,
            'transaction_id' => $paymentApprovmentResponseObject->RRN,
            'payment_gateway_id' => $this->paymentMethod->selectByCode('kuveyt_turk_bank')['id'],
            'payment_method' => 'Kuveyt Turk Bank',
            'status' => 'Success',
            'amount' => $order_info['total'],
            'currency' => $order_info['currency_code'],
            'details' => '',
        ]);

        $this->response->redirect($this->url->link('checkout/success', '', 'SSL'));
    }

    public function failed() {
        $AuthenticationResponse = $this->request->post["AuthenticationResponse"];
        $RequestContent = urldecode($AuthenticationResponse);

        $paymentApprovmentResponseObject = simplexml_load_string($RequestContent);

        $this->initializer([
            'checkoutOrder' => 'checkout/order',
            'paymentTransaction' => 'extension/payment_transaction',
            'paymentMethod' => 'extension/payment_method',
        ]);

        $order_id = $paymentApprovmentResponseObject->MerchantOrderId;

        $order_info = $this->model_checkout_order->getOrder($order_id);

        $orderFailed = $this->config->get('entry_order_status_failed');

        if (!$order_info) {
            $this->session->data['error'] = $this->language->get('error_no_order');

            $this->redirect($this->url->link('checkout/cart'));
        }

        $message = $paymentApprovmentResponseObject->ResponseMessage;

        $this->model_checkout_order->confirm($order_id, $orderFailed, $message);

        $this->paymentTransaction->insert([
            'order_id' => $order_id,
            'transaction_id' => $paymentApprovmentResponseObject->ReferenceId,
            'payment_gateway_id' => $this->paymentMethod->selectByCode('kuveyt_turk_bank')['id'],
            'payment_method' => 'Kuveyt Turk Bank',
            'status' => 'Failed',
            'amount' => $order_info['total'],
            'currency' => $order_info['currency_code'],
            'details' => '',
        ]);
        $this->redirect($this->url->link('checkout/error'));
    }

    private function createPaymentRequest($orderData) {

        $Name = $this->request->post['cc_owner'];
        $CardNumber = $this->request->post['cc_number'];
        $CardExpireDateYear = substr($this->request->post['cc_expire_date_year'], 2);
        $CardExpireDateMonth = $this->request->post['cc_expire_date_month'];
        $CardCVV2 = $this->request->post['cc_cvv2'];

        $CurrencyCode = "0949";
        $MerchantOrderId = $orderData['order_id'];
        $Amount = $orderData['total'];
        $CustomerId = $orderData['CustomerId'];
        $MerchantId = $orderData['MerchantId']; //Magaza Kodu
        $OkUrl = $this->url->link("payment/{$this->paymentName}/approvePayment", '', 'SSL');
        $FailUrl = $this->url->link("payment/{$this->paymentName}/failed", '', 'SSL');
        $UserName = $orderData['UserName'];
        $Password = $orderData['Password'];
        $TransactionSecurity = 3;

        $HashedPassword = base64_encode(sha1($Password, "ISO-8859-9"));
        $HashData = base64_encode(sha1($MerchantId . $MerchantOrderId . $Amount . $OkUrl . $FailUrl . $UserName . $HashedPassword, "ISO-8859-9"));

        $xmlRequest = '<KuveytTurkVPosMessage xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
                . '<APIVersion>1.0.0</APIVersion>'
                . '<OkUrl>' . $OkUrl . '</OkUrl>'
                . '<FailUrl>' . $FailUrl . '</FailUrl>'
                . '<SubMerchantId>0</SubMerchantId>'
                . '<HashData>' . $HashData . '</HashData>'
                . '<MerchantId>' . $MerchantId . '</MerchantId>'
                . '<CustomerId>' . $CustomerId . '</CustomerId>'
                . '<UserName>' . $UserName . '</UserName>'
                . '<CardNumber>' . $CardNumber . '</CardNumber>'
                . '<CardExpireDateYear>' . $CardExpireDateYear . '</CardExpireDateYear>'
                . '<CardExpireDateMonth>' . $CardExpireDateMonth . '</CardExpireDateMonth>'
                . '<CardCVV2>' . $CardCVV2 . '</CardCVV2>'
                . '<CardHolderName>' . $Name . '</CardHolderName>'
                . '<InstallmentCount>0</InstallmentCount>'
                . '<CardType>TROY</CardType>'
                . '<BatchID>0</BatchID>'
                . '<TransactionType>Sale</TransactionType>'
                . '<Amount>' . $Amount . '</Amount>'
                . '<DisplayAmount>' . $Amount . '</DisplayAmount>'
                . '<CurrencyCode>' . $CurrencyCode . '</CurrencyCode>'
                . '<MerchantOrderId>' . $MerchantOrderId . '</MerchantOrderId>'
                . '<TransactionSecurity>' . $TransactionSecurity . '</TransactionSecurity>'
                . '</KuveytTurkVPosMessage>';

        return $xmlRequest;
    }

    private function createApprovmentRequest($paymentResponse) {
        $MerchantOrderId = $paymentResponse->MerchantOrderId;
        $Amount = $paymentResponse->VPosMessage->Amount;
        $MD = $paymentResponse->MD;
        $APIVersion = "1.0.0";
        $CustomerId = $this->config->get('kuveyt_turk_bank_customer_id');
        $MerchantId = $this->config->get('kuveyt_turk_bank_merchant_id');
        $UserName = $this->config->get('kuveyt_turk_bank_username');
        $Password = $this->config->get('kuveyt_turk_bank_password');
        $HashedPassword = base64_encode(sha1($Password, "ISO-8859-9")); //md5($Password);	
        $HashData = base64_encode(sha1($MerchantId . $MerchantOrderId . $Amount . $UserName . $HashedPassword, "ISO-8859-9"));

        $xmlRequest = '<KuveytTurkVPosMessage xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
				<APIVersion>' . $APIVersion . '</APIVersion>
				<HashData>' . $HashData . '</HashData>
				<MerchantId>' . $MerchantId . '</MerchantId>
				<CustomerId>' . $CustomerId . '</CustomerId>
				<UserName>' . $UserName . '</UserName>
				<TransactionType>Sale</TransactionType>
				<InstallmentCount>0</InstallmentCount>
	         	        <DisplayAmount>' . $Amount . '</DisplayAmount>
				<Amount>' . $Amount . '</Amount>
				<MerchantOrderId>' . $MerchantOrderId . '</MerchantOrderId>
				<TransactionSecurity>3</TransactionSecurity>
				<KuveytTurkVPosAdditionalData>
				<AdditionalData>
					<Key>MD</Key>
					<Data>' . $MD . '</Data>
				</AdditionalData>
			</KuveytTurkVPosAdditionalData>
			</KuveytTurkVPosMessage>';
        return $xmlRequest;
    }

    private function connect($connectArray) {
        try {

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/xml', 'Content-length: ' . strlen($connectArray['requestBody'])));
            curl_setopt($ch, CURLOPT_POST, true); //POST Metodu kullanarak verileri g�nder  
            curl_setopt($ch, CURLOPT_HEADER, false); //Serverdan gelen Header bilgilerini �nemseme.  
            curl_setopt($ch, CURLOPT_URL, $connectArray['url']); //Baglanacagi URL  
            curl_setopt($ch, CURLOPT_POSTFIELDS, $connectArray['requestBody']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //Transfer sonu�larini al.

            $responseData = curl_exec($ch);
            curl_close($ch);
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
        }
        return $responseData;
    }

}

?>  
