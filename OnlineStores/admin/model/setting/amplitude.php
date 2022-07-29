<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
class ModelSettingAmplitude extends Model {

    public $api_key = AMPLITUDE_TOKEN;

    public function trackEvent($event_name, $data=[]) {
        $user   = $this->getMerchantInfo();
        if($user) {
            $data = [
                'user_id'           =>  $user['user_id'],
                "event_type"        =>  $event_name,
                'event_properties'  =>  $data
            ];
            $this->sendAmplitudeEventRequest('https://api2.amplitude.com/2/httpapi',$data);
        }
    }


    public function createUser(){
        $user   = $this->getMerchantInfo();

        if($user) {

            /***************** Start ExpandCartTracking #347687  ****************/

            $data = [
                'user_id'           =>$user['user_id'],
                "event_type"        => "Sign Up",
                'user_properties'   =>[
                    'name'              => $user['name'],
                    'email'             => $user['email'],
                    'phone'             => $user['phone'],
                    'created at'        => date("Y-m-d H:i:s"),
                    'current template'  => $user['current_template'],
                    'subscription plan' => PRODUCTID,
                    'products count'    => 0,
                    'store code'        => $user['store_code'],
                    'whmcs client id'   => $user['whmcs_client_id'],
                    'subscription state'=> 'active',
                ]
            ];
            $this->sendAmplitudeEventRequest('https://api2.amplitude.com/2/httpapi',$data);

            /***************** End ExpandCartTracking #347687  ****************/
        }
    }

    public function updateUser($properties=[],$default =""){
        $user   = $this->getMerchantInfo();
        $data = [
                    'user_id'           =>$user['user_id'],
                    'user_properties'   => $default != 'default' ? $properties :[
                                                                                    'name'              => $user['name'],
                                                                                    'email'             => $user['email'],
                                                                                    'phone'             => $user['phone'],
                                                                                    'store code'        => $user['store_code'],
                                                                                    'whmcs client id'   => $user['whmcs_client_id'],
                                                                                    'current template'  => $user['current_template'],
                                                                                    'subscription plan' => PRODUCTID,
                                                                                    'subscription state'=> 'active',
                                                                                ]
        ];

        if($user)
            $this->sendAmplitudeIdentifyRequest('https://api.amplitude.com/identify',$data);
    }


    public function getMerchantInfo()
    {
        $whmcs  = new whmcs();
        $userId = WHMCS_USER_ID;
        $phoneNumber = $whmcs->getClientPhone($userId);

        // get current template info
        $this->load->model('setting/template');
        $template_info = $this->model_setting_template->getTemplateInfo(CURRENT_TEMPLATE);
        $template_name = isset($template_info['Name']) ? $template_info['Name'] :  CURRENT_TEMPLATE;

        return [
            'user_id'           => strtoupper(STORECODE),
            'name'              => BILLING_DETAILS_NAME,
            'email'             => BILLING_DETAILS_EMAIL,
            'phone'             => $phoneNumber ?? $this->config->get('config_telephone'),
            'store_code'        => STORECODE,
            'whmcs_client_id'   => WHMCS_USER_ID,
            'current_template'  => $template_name,
        ];
    }


    //send amplitude Event request
    private function sendAmplitudeEventRequest($url,$data=[]){

        $body['api_key']    = $this->api_key;
        $body['events']     = [$data];
        $client = new Client();
        try{

            $response = $client->request('POST',$url,[
                'json' => $body,
                'headers' => [
                    'Content-Type' => 'application/json',
                ]
            ]);
            $response = $response->getBody()->getContents();
            $response= json_decode($response);

        } catch (RequestException | \Exception $e) {
            $msg = "Unable to complete request ";
            // Catch all 4XX errors
            if ($e instanceof RequestException && $e->hasResponse())
                $msg = " : the response given " . $e->getResponse()->getBody()->getContents();
            // throw new Exception($msg);
        }
    }


    //send amplitude Identify request
    private function sendAmplitudeIdentifyRequest($url,$data=[]){

        $body['api_key'] = $this->api_key;
        $body['identification'] = json_encode($data);
        $client = new Client();
        try{

            $response = $client->request('POST',$url,[
                'form_params' => $body,
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ]
            ]);
            $response = $response->getBody()->getContents();
            $response= json_decode($response);

        } catch (RequestException | \Exception $e) {
            $msg = "Unable to complete request ";
            // Catch all 4XX errors
            if ($e instanceof RequestException && $e->hasResponse())
                $msg = " : the response given " . $e->getResponse()->getBody()->getContents();
            // throw new Exception($msg);
        }
    }

}
?>