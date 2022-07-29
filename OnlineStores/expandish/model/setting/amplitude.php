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


    public function updateUser($properties=[]){
        $user   = $this->getMerchantInfo();
        $data   =  [
            'user_id'           =>$user['user_id'],
            'user_properties'   =>$properties
        ];

        if($user)
            $this->sendAmplitudeIdentifyRequest('https://api.amplitude.com/identify',$data);
    }


    public function getMerchantInfo()
    {
        return ['user_id' => strtoupper(STORECODE) ];
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
        return $response;
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
        return $response;
    }

}
?>