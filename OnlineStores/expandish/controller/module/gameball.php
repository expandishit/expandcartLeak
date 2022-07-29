<?php


class ControllerModuleGameball extends Controller {

    public function addGameballCoupon(){
        if($this->request->server['REQUEST_METHOD'] == 'POST' && $this->customer->isLogged()){
            $path = ONLINE_STORES_PATH . 'OnlineStores/admin/';
		
            $this->load->model('sale/coupon',false,$path);
            
            $coupon_code = $this->getCode();
            $coupon_data = [
                'name' => 'gameball_coupons_' . $coupon_code,
                'code' => $coupon_code,
                'type' => 'F',
                'discount' => $this->request->post['amount'],
                'logged' => 1,
                'shipping' => 2,
                'total' => 0,
                'date_start' => date("Y-m-d"),
                'date_end' => date("Y-m-d"), 
                'uses_total' => 0,
                'uses_customer' => 1,
                'status' => 1,
                'details' => [
                    "customer_option" => "logged",
                    "apply_item_from" => "all",
                    "exclude_item" => "none"
                ]
            ];

            if($this->redeemPoints($this->request->post['amount'])){

                if($this->model_sale_coupon->addCoupon($coupon_data))
                    $result = [
                        'status' => 1,
                        'coupon' => $coupon_code
                    ];
                else
                    $result = [
                        'status' => 0,
                    ];
                
            }
            else{
                $result = [
                    'status' => 0,
                ];
            }
            $this->response->setOutput(json_encode($result));

        }

    }
    private function getCode(){
        $length = 20;
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function redeemPoints($amount){

        $this->load->model('module/gameball/settings');
        $customer_points = $this->model_module_gameball_settings->getpoints($this->customer->getId());
        $redeem_amount = $this->currency->convertUsingRatesAPI($amount,$this->session->data['currency'],$customer_points['currency']);
        $data = [
            "playerUniqueId" => strval($this->customer->getId()),
            "transactionId" => strval(rand()),
            "transactionTime" => date('Y-m-d\TH:i:s.vP'),
            "redeemedAmount" => $redeem_amount,
        ];

        return $this->sendGameballRedeem($data) ? true : false;
    }

    private function sendGameballRedeem($data){
        $url = 'https://api.gameball.co/api/v3.0/integrations/transaction/redeem';
        $appSittings = $this->model_module_gameball_settings->getSettings();
        // check test mode
        $apikey = ($appSittings['environment'] == 1) ? $appSittings['test_apikey'] : $appSittings['live_apikey'];

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS,  json_encode($data));
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
          'Content-Type:application/json',
          'apiKey:'.$apikey,
          'secretkey:'.$appSittings['transaction_key']
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);  
        // Close cURL session
        curl_close($curl);
        $responseData = json_decode($response,true);
        return isset($responseData['redeemedPoints']) ? true : false;

    }

}










?>