<?php

class ModelShippingBarq extends Model{


  /**
   * @const strings API URLs.
   */
  const BASE_API_URL    = 'https://live.barqfleet.com/api/v1/merchants';
  const Staging_API_URL    = 'https://staging.barqfleet.com/api/v1/merchants';


  /**
	* [POST]Create new shipment Order.
	*
  * @param Array   $order data to be shipped.
	*
	* @return Response Object contains newly created order details
	*/
  public function login(){
    $email   = $this->config->get('barq_email');
    $password = $this->config->get('barq_password');
    $test_mode = $this->config->get('barq_test_mode');
    if($test_mode == 0)
      $url = self::BASE_API_URL.'/login';
    else
      $url = self::Staging_API_URL.'/login';

    $data = array("email"=>$email,"password"=>$password);
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS =>json_encode($data),
      CURLOPT_HTTPHEADER => array(
        "Content-Type: application/json"
      ),
    ));
    $response = json_decode(curl_exec($curl), true);
    curl_close($curl);
    return $response['token'];
  }
  public function getProfile($token){
    $test_mode = $this->config->get('barq_test_mode');
    if($test_mode == 0)
      $url = self::BASE_API_URL.'/profile';
    else
      $url = self::Staging_API_URL.'/profile';

    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_HTTPHEADER => array(
        "Authorization: $token"
      ),
    ));
    $response = json_decode(curl_exec($curl), true);
    curl_close($curl);
    return $response;
  }

  public function getHubs($token){
    $test_mode = $this->config->get('barq_test_mode');
    $lang = $this->config->get('config_admin_language');
    if($test_mode == 0)
      $url = self::BASE_API_URL.'/hubs/';
    else
      $url = self::Staging_API_URL.'/hubs/';

    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_HTTPHEADER => array(
        "Authorization: $token",
        "Content-Type: application/json",
        "Language: $lang"
      ),
    ));
    $response = json_decode(curl_exec($curl), true);
    curl_close($curl);
    return $response[0]['merchant']['hubs'];
  }

  public function getCities($token){
    $test_mode = $this->config->get('barq_test_mode');
    if($test_mode == 0)
      $url = self::BASE_API_URL.'/cities/active_cities';
    else
      $url = self::Staging_API_URL.'/cities/active_cities';

    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_HTTPHEADER => array(
        "Authorization: $token",
        "Content-Type: application/json",
      ),
    ));
    $response = json_decode(curl_exec($curl), true);
    curl_close($curl);
    return $response;
  }
  public function createShipment($data,$token){
    $test_mode = $this->config->get('barq_test_mode');
    if($test_mode == 0)
      $url = self::BASE_API_URL.'/orders';
    else
      $url = self::Staging_API_URL.'/orders';
    
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS =>json_encode($data),
      CURLOPT_HTTPHEADER => array(
        "Content-Type: application/json",
        "Authorization: $token"
      ),
    ));
    $response = json_decode(curl_exec($curl), true);
    curl_close($curl);
		return $response;
  }
  
  //coordinates
  public function getGeocode($address)
  {
    $coordinatesData = [
        'lat' => 0,
        'lng' => 0
    ];

    $url = 'https://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($address).'&key='.$this->config->get('barq_google_api_key');

    $ch = curl_init($url); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = json_decode( preg_replace('/[\x00-\x1F\x80-\xFF]/', '', curl_exec($ch)), true);
    if (!curl_errno($ch)) {
      if (!empty($result['results'])) {
        $coordinatesData['lat'] = $result['results'][0]['geometry']['location']['lat'];
        $coordinatesData['lng'] = $result['results'][0]['geometry']['location']['lng'];
      }
    }
    curl_close($ch);
    return $coordinatesData;
  }
}
?>
