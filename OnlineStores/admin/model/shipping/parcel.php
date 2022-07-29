

<?php
class ModelshippingParcel extends Model {

    /**
      * @const strings API URLs.
      */
    const AUTH_URL = 'https://auth.tryparcel.com/oauth2/token';
    const BASE_API_TESTING_URL  = 'https://private-anon-9de8c8c449-parcelv3.apiary-mock.com/v3';
    const BASE_API_LIVE_URL     = 'https://api.tryparcel.com/v3';


    /**
    * [POST]Authenticate a parcel user.
    *
    *
    * @return string access token or boolean false
    */
    public function authenticate(){
        $parcel_settings = $this->config->get('parcel');
        $auth_string = base64_encode($parcel_settings['client_id'] . ':' . $parcel_settings['client_secret']);

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => self::AUTH_URL,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => 'grant_type=client_credentials&scope=api%2Ftasks',
          CURLOPT_HTTPHEADER => array(
            'Authorization: Basic '.$auth_string,
            'Content-Type: application/x-www-form-urlencoded',
          ),
        ));

        $response = json_decode(curl_exec($curl), true);
        curl_close($curl);

        if( $response['status'] == 200 && !empty($response['access_token']) ){
            return $response['token_type'] . ' ' . $response['access_token'];
        }

        return false;
    }

  	/**
  	* [POST]Create new shipment Order.
  	*
    * @param Array   $order data to be shipped.
  	*
  	* @return Response Object contains newly created order details
  	*/
    public function createShipment($order){
      $this->language->load('shipping/parcel');
      $parcel_settings = $this->config->get('parcel');

      if( !$access_token = $this->authenticate() ){

          return $this->language->get("error_auth_failed");
      }

      $ch = curl_init();

      curl_setopt($ch, CURLOPT_URL, $this->_getBaseUrl() . "/create_task");
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
      curl_setopt($ch, CURLOPT_HEADER, FALSE);
      curl_setopt($ch, CURLOPT_POST, TRUE);
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($order));

      curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          'Authorization: ' . $access_token,
          'Content-Type: application/json'
      ));

      $response = curl_exec($ch);

      //Fixing parcel bug in mock server (Testing server)..
      if( $parcel_settings['debugging_mode'] == 1)
          $this->_formateTestingReponse($response);
      else
          $response = json_decode($response, true);

		  $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      $err      = curl_error($ch);
		  curl_close($ch);

		  return $err ? ['error' => $err] : ['status_code' => $httpcode, 'result' => $response];
    }

    /*  Helper Methods */
    private function _getBaseUrl(){
      //Check if API is in Debugging Mode..
      $is_debugging_mode = $this->config->get('parcel')['debugging_mode'];

      return ( isset($is_debugging_mode) && $is_debugging_mode == 1 ) ? self::BASE_API_TESTING_URL : self::BASE_API_LIVE_URL;
    }

    private function _formateTestingReponse(&$response){
        //Remove this characters from response to be parsable by json_decode method
        $response = preg_replace('/[\x00-\x1F]/','', $response);
        $response = json_decode($response, true);

        //Trim beginning spaces from response array keys
        $keys = str_replace( ' ', '', array_keys($response) );
        $values = array_values($response);
        $response = array_combine($keys, $values);
    }
}
