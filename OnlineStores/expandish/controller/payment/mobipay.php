<?php
class ControllerPaymentMobipay extends Controller
{
    public function index()
    {
        $this->data = array_merge($this->data, $this->language->load_json('payment/mobipay'));
        $this->initializer([
            'mobipay' => 'payment/mobipay',
            'checkout/order',
        ]);
        $settings = $this->mobipay->getSettings();
        if (isset($settings['status']) == false) {
            return;
        }
        if ($settings['status'] == 0) {
            return;
        }
        $orderInfo = $this->order->getOrder($this->session->data['order_id']);
        if (!$orderInfo) {
            return false;
        }

        // $this->template = $this->checkTemplate('payment/mobipay.expand');
        
        $this->template = 'default/template/payment/mobipay.expand';
        
        $this->render_ecwig();
    }
    /*================================================================================================================= */
    public function makePayment()
    {
        $this->initializer([
            'mobipay' => 'payment/mobipay',
            'checkout/order',
        ]);

        $orderInfo = $this->order->getOrder($this->session->data['order_id']);
        $settings = $this->config->get('mobipay');
        $amount = round($orderInfo['total'], 2);
        $tranUID = mt_rand(1638, 2047) . '-' . mt_rand(1638, 2047) . '-' . mt_rand(1638, 2047);

        $mobipayArray = [
            'serviceId' => "60103",
            'amount' => $amount,
            'channelId' => 3,
            'tranUID' => $tranUID,
            'date' => date('ymdHis'),
            'spId' => "EXC1001",
            "serviceInfo" => "{\"amount\":\"$amount\",\"serviceId\": 60120}",
        ];

        $mobipayResponse = $this->connectToMobipay($mobipayArray);

        if ($mobipayResponse['responseCode'] == 110) {
            $this->response->setOutput(json_encode(['url'=>$this->paymentPage($tranUID)]));
        } else {
            $this->response->setOutput(json_encode(['url'=> (string) $this->url->link('checkout/cart', '', true)]));
        }

    }
    /*================================================================================================================= */
    private function connectToMobipay($mobipayArray)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://mobipay.amarouse.com:8086/api/processService',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($mobipayArray),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response,true);
        return $response;
    }
    /*================================================================================================================= */
    private function paymentPage($tranUID)
    {
        return "https://mobipay.amarouse.com:8086/MWebPlugin/index.php?&tranUID=$tranUID";
    }
    /*================================================================================================================= */
    public function success()
    {
        $this->initializer([
            'checkoutOrder' => 'checkout/order'
        ]);

        $order_id = $this->session->data['order_id'];

        $order_info = $this->checkoutOrder->getOrder($order_id);

        $orderSuccess = $this->config->get('mobipay')['completed_status_code'];

        if (!$order_info) {
            $this->session->data['error'] = $this->language->get('error_no_order');

            $this->redirect($this->url->link('checkout/cart'));
        }

        $this->checkoutOrder->confirm($order_id, $orderSuccess);

        $this->redirect($this->url->link('checkout/success', '', true));
        return;

    }
    /*================================================================================================================= */
    public function failed()
    {
        $this->initializer([
            'checkoutOrder' => 'checkout/order'
        ]);

        $order_id = $this->session->data['order_id'];

        $order_info = $this->checkoutOrder->getOrder($order_id);

        $orderFailed = $this->config->get('mobipay')['denied_status_code'];

        if (!$order_info) {
            $this->session->data['error'] = $this->language->get('error_no_order');

            $this->redirect($this->url->link('checkout/cart'));
        }

        $this->checkoutOrder->confirm($order_id, $orderFailed);

        $this->redirect($this->url->link('checkout/error', '', true));
        return;
    }
    /*================================================================================================================= */
}
