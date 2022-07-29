<?php

class ModelPaymentMyFatoorah extends Model{


  /**
   * @const strings API URLs.
   */
  const BASE_API_URL    = 'https://apidemo.myfatoorah.com/';


  /**
	* [POST]Create new shipment Order.
	*
  * @param Array   $order data to be shipped.
	*
	* @return Response Object contains newly created order details
	*/
    public function register($data){
      $url       = self::BASE_API_URL . '/Account/Register';
      $curl = curl_init($url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_HEADER, true);
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_POSTFIELDS,  json_encode($data));
      curl_setopt($curl, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'accept-language: '.$lang,
      ]);

      // Execute cURL request with all previous settings
      $response = curl_exec($curl);
      $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

      // Close cURL session
      curl_close($curl);
      return json_decode($response, true);
    }

    public function generateToken($data){
      $url       = self::BASE_API_URL . '/Token';
      $curl = curl_init($url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_HEADER, true);
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
      curl_setopt($curl, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
        'accept-language: '.$lang,
      ]);

      // Execute cURL request with all previous settings
      $response = curl_exec($curl);
      $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      // Close cURL session
      curl_close($curl);
      return json_decode($response, true);
    }

    public function UpdateDocument($token,$data){
      $url       = self::BASE_API_URL . '/VendorProfile/UpdateDocument';
      $curl = curl_init();
      curl_setopt_array($curl, array(
        CURLOPT_URL => "https://apidemo.myfatoorah.com/VendorProfile/UpdateDocument",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "PUT",
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => array(
          "Authorization: Bearer ".$token,
          "accept-language: en",
          "cache-control: no-cache",
          "content-type: multipart/form-data",
        ),
      ));

      $response = curl_exec($curl);
      $err = curl_error($curl);
      curl_close($curl);
      if ($err) {
        return "cURL Error #:" . $err;
      } else {
        return json_decode($response, true);
      }
      
    }

  	public function getBanks(){
  		$lang = $this->config->get('config_admin_language');
    	$url       = self::BASE_API_URL . '/AnonymousLists/GetBanks';
  		$curl = curl_init($url);
  		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  		curl_setopt($ch, CURLOPT_HEADER, true);
  		curl_setopt($curl, CURLOPT_HTTPHEADER, [
  		  'Content-Type: application/json',
  		  'accept-language: '.$lang,

  		]);

  		// Execute cURL request with all previous settings
  		$response = curl_exec($curl);
  		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

  		// Close cURL session
  		curl_close($curl);
      return json_decode($response, true);
  	}

    public function getCountiesWithCodes(){
      $lang = $this->config->get('config_admin_language');
      $url       = self::BASE_API_URL . '/AnonymousLists/GetCountiesWithCodes';
      $curl = curl_init($url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_HEADER, true);
      curl_setopt($curl, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'accept-language: '.$lang,

      ]);
      // Execute cURL request with all previous settings
      $response = curl_exec($curl);
      $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

      // Close cURL session
      curl_close($curl);
      return json_decode($response, true);
    }

    public function getNationalityCountries(){
      $lang = $this->config->get('config_admin_language');
      $url       = self::BASE_API_URL . '/AnonymousLists/GetNationalityCountries';
      $curl = curl_init($url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_HEADER, true);
      curl_setopt($curl, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'accept-language: '.$lang,

      ]);
      // Execute cURL request with all previous settings
      $response = curl_exec($curl);
      $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

      // Close cURL session
      curl_close($curl);
      return json_decode($response, true);
    }

    public function getVendorCategories(){
      $lang = $this->config->get('config_admin_language');
      $url       = self::BASE_API_URL . '/AnonymousLists/GetVendorCategories';
      $curl = curl_init($url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_HEADER, true);
      curl_setopt($curl, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'accept-language: '.$lang,
      ]);
      // Execute cURL request with all previous settings
      $response = curl_exec($curl);
      $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

      // Close cURL session
      curl_close($curl);
      return json_decode($response, true);
    }

    public function getProfile($token){
      $lang = $this->config->get('config_admin_language');
      $url       = self::BASE_API_URL . '/VendorProfile/GetProfile';
      $curl = curl_init($url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_HEADER, true);
      curl_setopt($curl, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'accept-language: '.$lang,
        'Authorization: Bearer '.$token,
      ]);

      // Execute cURL request with all previous settings
      $response = curl_exec($curl);
      $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      // Close cURL session
      curl_close($curl);
      return json_decode($response, true);
    }

}
?>
