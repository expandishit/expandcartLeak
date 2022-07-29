<?php

class ModelAccountPaypal extends Model
{
    /**
     * @var string
     */
    private $liveUri = 'https://api.paypal.com';

    /**
     * @var string
     */
    private $sandboxUri = 'https://api.sandbox.paypal.com';

    /**
     * @var string
     */
    private $environment = PAYPAL_ENV;

    /**
     * @var string
     */
    private $accessToken = '';

    /**
     * Get the base URI
     *
     * @return string
     */
    private function getBaseUri()
    {
        return $this->environment == 'sandbox' ? $this->sandboxUri : $this->liveUri;
    }

    /**
     * Set access token to override it
     *
     * @param string $accessToken
     *
     * @return void
     */
    private function setAccessToken(string $accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * Get access token
     *
     * @return string
     **/
    private function getAccessToken() : string
    {
        return $this->accessToken;
    }

    /**
     * Auth paypal api
     *
     * @param string $clientId
     * @param string $clientSecret
     *
     * @return array
     */
    public function auth(string $clientId, string $clientSecret) : array
    {
        $hash = base64_encode($clientId . ":" . $clientSecret);
        $ch = curl_init(sprintf('%s/v1/oauth2/token', $this->getBaseUri()));
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Accept-Language: en_US',
                sprintf('Authorization: Basic %s', $hash)
            ],
            CURLOPT_POSTFIELDS => http_build_query(['grant_type' => 'client_credentials']),
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
        ]);

        $response = curl_exec($ch);

        if (!$response) {
            $err = curl_error($ch);
            curl_close($ch);
            return [
                'status' => 'ERR',
                'errors' => [$err]
            ];
        }

        curl_close($ch);

        $response = json_decode($response, true);

        $this->accessToken = $response['access_token'];

        return [
            'status' => 'OK',
            'access_token' => $this->accessToken
        ];
    }

    /**
     * Create subscription
     *
     * @param array $data
     * @param array $opts
     *
     * @return array
     */
    public function createSubscription(array $body, array $opts = []) : array
    {
        $ch = curl_init(sprintf('%s/v1/billing/subscriptions', $this->getBaseUri()));
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
                'Prefer: return=representation',
                sprintf(
                    'Authorization: Bearer %s',
                    isset($opts['access_token']) ? $opts['access_token'] : $this->getAccessToken()
                ),
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'plan_id' => $body['plan_id'],
//                'notify_url'=>"",
                'application_context' => [
                    'return_url' => $body['return_url'],
                    'cancel_url' => $body['cancel_url'],
                ]
            ]),
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
        ]);

        $response = curl_exec($ch);

        if (!$response) {
            $err = curl_error($ch);
            curl_close($ch);
            return [
                'status' => 'ERR',
                'errors' => [$err]
            ];
        }

        curl_close($ch);

        $response = json_decode($response, true);

//        if (STAGING_MODE == 1){
            $this->log($response);
//        }

        return $response;
    }

    /**
     * Cancel subscription
     *
     * @param string $subscription_id
     *
     * @return array
     */
    public function cancelSubscription(string $subscription_id) : bool
    {
        // return true; // skip cancellation process for now

        $ch = curl_init(sprintf('%s/v1/billing/subscriptions/%s/cancel', $this->getBaseUri(), $subscription_id));
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
                sprintf(
                    'Authorization: Bearer %s',
                    isset($opts['access_token']) ? $opts['access_token'] : $this->getAccessToken()
                ),
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'reason' => 'plan changed'
            ]),
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
        ]);

        $response = curl_exec($ch);

        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        $this->log($response);

        return $httpcode == 204 ? true : false;
    }

    /**
     * @param array $body
     * @param array $opts
     * @return array
     */
    public function createBillingPlan(array $body, array $opts = []) : array
    {

        $paymentDef = [];
        $ch = curl_init(sprintf('%s/v1/payments/billing-plans', $this->getBaseUri()));
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
                'Prefer: return=representation',
                sprintf(
                    'Authorization: Bearer %s',
                    isset($opts['access_token']) ? $opts['access_token'] : $this->getAccessToken()
                ),
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'name' => "Billing Plan",
                'description'=>'Plan with regular payment',
                "type"=> "INFINITE",
                "payment_definitions"=>[
                    [
                        "name"=>$body['name'],
                        "type"=>"REGULAR",
                        "frequency"=>$body['frequency'],
                        "frequency_interval"=> "1",
                        "amount"=> [
                        "value"=> $body['value'] ,
                        "currency"=>$body['currency']
                        ],
                        "cycles"=> "0"
                    ],
                ] ,
                'merchant_preferences' => [
                    'return_url' => $body['return_url'],
                    'cancel_url' => $body['cancel_url'],
                ]
            ]),
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
        ]);

        $response = curl_exec($ch);

        if (!$response) {
            $err = curl_error($ch);
            curl_close($ch);
            return [
                'status' => 'ERR',
                'errors' => [$err]
            ];
        }

        curl_close($ch);

        $response = json_decode($response, true);

//        if (STAGING_MODE == 1){
            $this->log($response);
//        }

        return $response;
    }

    public function createBillingAgreement(array $body, array $opts = []) : array
    {
        $ch = curl_init(sprintf('%s/v1/payments/billing-agreements', $this->getBaseUri()));
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
                'Prefer: return=representation',
                sprintf(
                    'Authorization: Bearer %s',
                    isset($opts['access_token']) ? $opts['access_token'] : $this->getAccessToken()
                ),
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'name' => "Payment Plan Agreement",
                'description'=>'PayPal payment agreement',
                "start_date"=>  date("Y-m-d\TH:i:sO",strtotime("+ 1 day")),
                "payer"=> [
                    "payment_method"=> "paypal",
                    "payer_info"=> [
                        "email"=> EMAIL
                    ]
                ],
                "plan"=>[
                    "id"=> $body['plan_id']
                ],
                'override_merchant_preferences' => [
                    'return_url' => $body['return_url'],
                    'cancel_url' => $body['cancel_url'],
                ]
            ]),
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
        ]);

        $response = curl_exec($ch);

        if (!$response) {
            $err = curl_error($ch);
            curl_close($ch);
            return [
                'status' => 'ERR',
                'errors' => [$err]
            ];
        }

        curl_close($ch);

        $response = json_decode($response, true);

//        if (STAGING_MODE == 1){
            $this->log($response);
//        }
        return $response;
    }

    public function activateBillingPlan(array $body, array $opts = [])
    {
        //P-74D14642GJ375804VKBZPYVA
        //$body['plan_id']
        $ch = curl_init(sprintf('%s/v1/payments/billing-plans/'.$body['plan_id'], $this->getBaseUri()));

      //  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');

        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
                'Prefer: return=representation',
                sprintf(
                    'Authorization: Bearer %s',
                    isset($opts['access_token']) ? $opts['access_token'] : $this->getAccessToken()
                ),
            ],
            CURLOPT_POSTFIELDS => json_encode([
                [
                    "op"=> "replace",
                    "path"=> "/",
                    "value"=> [
                        "state"=> "ACTIVE"
                    ]
                ]
            ]),
            CURLOPT_CUSTOMREQUEST=>'PATCH',
//            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
        ]);

        $response = curl_exec($ch);

//        if (!$response) {
//            $err = curl_error($ch);
//            curl_close($ch);
//            return [
//                'status' => 'ERR',
//                'errors' => [$err]
//            ];
//        }

        curl_close($ch);

      //  $response = json_decode($response, true);

//        if (STAGING_MODE == 1){
            $this->log($response);
//        }

        return $response;
    }

    public function paymentCapture(array $body, array $opts = []){
        $ch = curl_init(sprintf('%s/v1/payments/capture/'.$body['token'], $this->getBaseUri()));

        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
                'Prefer: return=representation',
                sprintf(
                    'Authorization: Bearer %s',
                    isset($opts['access_token']) ? $opts['access_token'] : $this->getAccessToken()
                ),
            ],
//            CURLOPT_CUSTOMREQUEST=>'PATCH',
//            CURLOPT_POST => true,
//            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FAILONERROR, false
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
        }

//        if (!$response) {
//            $err = curl_error($ch);
//            curl_close($ch);
//            return [
//                'status' => 'ERR',
//                'errors' => [$err]
//            ];
//        }

        curl_close($ch);

        //  $response = json_decode($response, true);

//        if (STAGING_MODE == 1){
        $this->log($response);
//        }

        return $response;
    }


    /**
     * Extract the approval link from the array of links to redirect the user to
     *
     * @param array $links
     *
     * @return string
     */
    public function extractApproveLink(array $links) : string
    {
        $links = array_column($links, 'href', 'rel');

        if (isset($links['approval_url']) == false && isset($links['self']) == false) {
            return '';
        }else if(isset($links['approve'])){
            return $links['approve'];
        }else if(isset($links['approval_url'])){
            return $links['approval_url'];
        }
        return '';
    }

    public function WHMCSCallback($body){

        $url = BILLING_SYSTEM_URL . 'modules/gateways/callback/paypal.php';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 100);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        if ($cer = curl_error($ch)) {
            return false;
        }
        curl_close($ch);

        return true;
    }

    public function log($msg){
        $log_array=array(
            "StoreCode" => STORECODE,
            "DateTime" => date("Y-m-d h:i:s A"),
            "Msg" => $msg,
            "type"=>"paypal"
        );
        $log_in_json=json_encode($log_array).", \r\n";
        file_put_contents(ONLINE_STORES_PATH."/OnlineStores/system/logs/payment.json",$log_in_json,FILE_APPEND);

    }
}
