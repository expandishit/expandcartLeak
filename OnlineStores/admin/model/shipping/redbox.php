
<?php

class ModelShippingRedbox extends Model{

  /**
	* [POST]Create new shipment Order.
	*
	*
	*/
  public function createShipment ($data){
    if($this->config->get('redbox_test'))
      $url = "https://stage.redboxsa.com/api/business/v1/create-shipment";
    else
      $url = "https://app.redboxsa.com/api/business/v1/create-shipment";

    $access_token = $this->config->get('redbox_api_key');

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

  public function trackShipment ($tracking_number){
    if($this->config->get('redbox_test'))
      $url = "https://stage.redboxsa.com/api/business/v1/shipment-detail?shipment_id=";
    else
      $url = "https://app.redboxsa.com/api/business/v1/shipment-detail?shipment_id=";

    $url .= $tracking_number;
    $access_token = $this->config->get('redbox_api_key');
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

}
?>
