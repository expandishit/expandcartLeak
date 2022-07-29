
<?php

class ModelShippingVanex extends Model{

  public function login(){
    // testing url
    //$url = "https://api.vanextest.com.ly/api/v1/authenticate";
    $url = "https://app.vanex.ly/api/v1/authenticate";
    $data = array('email' => $this->config->get('vanex_email') , 'password'=> $this->config->get('vanex_password'));
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
    return $response;
  }

  /**
	* [POST]Create new shipment Order.
	*
	*
	*/
  public function createShipment($access_token, $data){
    $url = "https://app.vanex.ly/api/v1/customer/package";
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
        "Authorization: Bearer $access_token",
        "Content-Type: application/json"
      ),
    ));
    $response = json_decode(curl_exec($curl), true);
    curl_close($curl);
    return $response;
  }


  public function install()
  {

  }

  public function uninstall()
  {

  }

  public function getCities(){
    $access_token = $this->login()['data']['access_token'];
    $url = "https://app.vanex.ly/api/v1/delivery/price?page=";
    $cities = [];
    $page = 1 ;
    do{
      $curl = curl_init();
      curl_setopt_array($curl, array(
        CURLOPT_URL => $url.$page,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
          "Authorization: Bearer $access_token",
          "Content-Type: application/json"
        ),
      ));
      $response = json_decode(curl_exec($curl), true);
      $cities = array_merge($cities, $response['data']['data']);
      curl_close($curl);      
      $page++;
      if($page > $response['data']['meta']['last_page'])
        break;
    }
    while(1);
    return $cities;
  }

}
?>
