<?php

class ModelModuleStockzonesEntity extends Model {

    /**
     * @const strings API URLs.
     */
    const BASE_API_TESTING_URL    = 'http://makzoonadmin.stage2.demo321.com/api/request';
    const BASE_API_PRODUCTION_URL = 'http://makzoonadmin.stage2.demo321.com/api/request';

    const ARABIC_LANGUAGE_CODE    = '5e8ec36a5b07d34695a4be7f';
    const ENGLISH_LANGUAGE_CODE   = '5a3a13238481824b077b23ca';

    public function connectAPI($req){
        $data = "req=". urlencode(stripslashes(json_encode($req)));

        $settings = $this->config->get("stockzones");

        $curl = curl_init();

        curl_setopt_array($curl, [
          CURLOPT_URL => $this->_getBaseUrl(),
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => $data,
          CURLOPT_HTTPHEADER => [
            "Content-Type: application/x-www-form-urlencoded",
            "accessKey: " . $settings['access_key'],
            "accessToken: " . $settings['access_token'],
          ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
          return [ 'response' => ['message' => $err] ];
        } else {
          return json_decode($response, true);
        }
    }

    /* Helper Methods */
    private function _getBaseUrl(){
      //Check if API is in Debugging Mode..
      $is_debugging_mode = $this->config->get('stockzones')['debugging_mode'];
      return ( isset($is_debugging_mode) && $is_debugging_mode == 1 ) ? self::BASE_API_TESTING_URL : self::BASE_API_PRODUCTION_URL;
    }
 
}
