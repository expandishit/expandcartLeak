<?php

class ModelExtensionExpandPay extends Model{

    private $base_url = 'http://34.107.96.22/';
    private $secret_key = EXPANDPAY_SECRET_KEY;
    public function register($data,$expandpay_status=0)
    {
        $this->load->model('setting/setting');
        $lang = $this->config->get('config_admin_language');
        $url       = $this->base_url . 'api/v1/merchant';
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS,  json_encode($data));
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
          'Content-Type:application/json',
          'secret-key:'.$this->secret_key,
          'client-Address:'.$this->request->server['REMOTE_ADDR'],
          'accept-language:'.$lang,
        ]);
  
        // Execute cURL request with all previous settings
        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  
        // Close cURL session
        curl_close($curl);

        $this->load->model('setting/extension');
        
        $responseData = json_decode($response);

        $expandPay_token = $responseData->data->secret_key;

        if($responseData->status == "OK")
        {
            // add token,status,merchant_id to setting table
            $data['expandpay_status'] = $expandpay_status;
            $data['expandpay_token'] = $expandPay_token;
            $data['expandpay_merchant_id'] = $responseData->data->merchant_id;
            $data['expandpay_data'] = $data;
            $data['expandpay_file_upload'] = 0;
            $data['expandpay_form_submit'] = 1;
            $data['expandpay_merchant_status'] = 'pending';
            $data['expandpay_default_currency'] = $responseData->data->currency;
            $allowed = [
              'expandpay_status',
              'expandpay_token',
              'expandpay_merchant_id',
              'expandpay_data',
              'expandpay_file_upload',
              'expandpay_form_submit',
              'expandpay_merchant_status',
              'expandpay_default_currency',
              'expandpay_country_code'
            ];
            $data = array_filter($data,function($key) use($allowed){
                return in_array($key, $allowed);
            },ARRAY_FILTER_USE_KEY);
            $this->model_setting_setting->insertUpdateSetting('expandpay', $data);
            $this->model_setting_extension->install('payment', 'expandpay');
        }
        return json_decode($response);
    }
/* 
    public function getVendorCategoryList(){

        $url = 'http://api.expandpay.com/api/v1/vendorCategories';
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        // Execute cURL request with all previous settings
        $response = curl_exec($curl);
  
        curl_close($curl);

        $result = json_decode($response,true);
        
        return $result['data'];
    }
 */
    public function UpdateDocument($token,$data){
        //$url       = self::BASE_API_URL . '/VendorProfile/UpdateDocument';
        $this->load->model('setting/setting');
        $lang = $this->config->get('config_admin_language');
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => $this->base_url . "api/v1/merchant/uploadFile",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 300,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_POST => true,
          CURLOPT_POSTFIELDS => $data,
          CURLOPT_HTTPHEADER => array(
            "Authorization: Bearer ".$token,
            "accept-language:".$lang,
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
  
    public function getTransactionList($page,$length,$search_key='',$start_date,$end_date,$statuses){
        $this->load->model('setting/setting');
        $lang = $this->config->get('config_admin_language');
        $url = $this->base_url . 'api/v1/merchant/'.$this->config->get('expandpay_merchant_id').'/transactions?page='.$page.'&limit='.$length.'';
        if(!empty($search_key))
          $url .= '&search='.$search_key;
        if(!empty($start_date))
          $url .= "&filters[start_date]=".$start_date."&filters[end_date]=".$end_date;
        if(!empty($statuses)){
          foreach($statuses as $status){
            $url.="&filters[statuses][]=".$status;
          }
        }
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
          'accept-language:'.$lang,
          'Authorization:Bearer ' . $this->config->get('expandpay_token')
          ]);

        $result = curl_exec($curl);

        $err    = curl_error($curl);

        $respArr = json_decode($result,true);
        foreach($respArr['data'] as $key => $value){

            $respArr['data'][$key]['custom_fields'] = json_decode($respArr['data'][$key]['custom_fields'],true);

        }

        return $respArr;
    }

    public function getMerchantBalances($start_date=null,$end_date=null){
      $this->load->model('setting/setting');
      $merchat_id = $this->config->get('expandpay_merchant_id');
      $lang = $this->config->get('config_admin_language');
      $url = $this->base_url . 'api/v1/merchant/'.$merchat_id.'/balances';
      if(!empty($start_date))
        $url .= "?filters[start_date]=".$start_date."&filters[end_date]=".$end_date;

      $curl = curl_init($url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_HEADER, true);
      curl_setopt($curl, CURLOPT_HTTPHEADER, [
        'accept-language:'.$lang,
        'Authorization:Bearer ' . $this->config->get('expandpay_token')
        ]);

      $result = curl_exec($curl);

      $err    = curl_error($curl);
      $respArr = json_decode($result,true);
      if($respArr['status'] == 'OK'){
        // filter out unused data
        // $allowed = ['total_balance','available_balance','received_balance','currency','transactions_total'];
        // $respArr = array_filter($respArr['data']['info'],function($key) use($allowed){
        //     return in_array($key, $allowed);
        // },ARRAY_FILTER_USE_KEY);

        return $respArr['data'];

      }

    }

    public function getWithdrawsList($page,$length,$search_key='',$start_date,$end_date,$statuses){
      $this->load->model('setting/setting');
      $lang = $this->config->get('config_admin_language');
      $url = $this->base_url . 'api/v1/withdrawal/'.$this->config->get('expandpay_merchant_id').'/withdrawals?page='.$page.'&limit='.$length.'';
      if(!empty($search_key))
        $url .= '&search='. $search_key;
      
      if(!empty($start_date))
        $url .= "&filters[start_date]=".$start_date."&filters[end_date]=".$end_date;

      if(!empty($statuses)){
        foreach($statuses as $status){
          $url.="&filters[statuses][]=".$status;
        }
      }
      $curl = curl_init($url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_HEADER, true);
      curl_setopt($curl, CURLOPT_HTTPHEADER, [
        'accept-language:'.$lang,
        'Authorization:Bearer ' . $this->config->get('expandpay_token')
        ]);

      $result = curl_exec($curl);

      $err    = curl_error($curl);

      $respArr = json_decode($result,true);


      if($respArr['status'] == 'OK')
        return $respArr;
      else
        return $respArr['error'];

    }

    public function getBankList($country_code){
      $url = $this->base_url . 'api/v1/banksList/'.$country_code;

      $this->load->model('setting/setting');
      $lang = $this->config->get('config_admin_language');
      $curl = curl_init($url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_HEADER, false);
      curl_setopt($curl, CURLOPT_HTTPHEADER, [
        'accept-language:'.$lang,
      ]);
      $result = curl_exec($curl);
      $err    = curl_error($curl);
      
      $response = json_decode($result,true);

      if($response['status'] == 'OK')
        return $response['data'];
      else
        return $response['error'];
      
      

    }
    public function restoreMerchantData($data){
      $this->load->model('setting/setting');

      $data['country'] = $data['country_iso_2'];
      $data['merchant']['custom_fields'] = json_decode($data['merchant']['custom_fields'],true);
      $merchantData = [];
      $merchantData['expandpay_status'] = 0;
      $merchantData['expandpay_token'] = $data['secret_key'];
      $merchantData['expandpay_merchant_id'] = $data['merchant_id'];
      $merchantData['expandpay_data'] = $data['merchant'];
      $merchantData['expandpay_file_upload'] = 1;
      $merchantData['expandpay_form_submit'] = 1;
      $merchantData['expandpay_configure'] = 1;
      $merchantData['expandpay_merchant_status'] = $data['merchant']['status'] ? 'active' : 'pending';
      $merchantData['expandpay_default_currency'] = $data['currency'];

      $this->model_setting_setting->insertUpdateSetting('expandpay', $merchantData);

    }
}





?>