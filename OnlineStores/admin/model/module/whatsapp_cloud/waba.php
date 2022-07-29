<?php


class ModelModuleWhatsappCloudWaba extends Model {

	
	private static  $businessId 	= '697248897325946',
					$adminUserId	= '104272995252685',  		//WhatsApp notifications user Admin
					$employeeUserId	= '107358521147997',  		//WhatsApp User Employee
					$wabaCurrency	= 'USD',
					$adminToken 	= EC_WHATSAPP['ADMIN_TOKEN'],
					$employeeToken  = EC_WHATSAPP['EMPLOYEE_TOKEN'],
					$creditId 	  	= EC_WHATSAPP['CREDIT_ID'];
				
	
	protected static $integrationRequestTable =  'whatsapp_requests';

		
	//========================== Graph API methods ================================//
	
	/*
	 *  debug the callback token to get the shared waba_id
	 * 
	 * @parm string $token  
	 *
	 * @return int | false 
	 */
	public function fetchWaba(string $token)
	{
		
		$result				= $this->debugToken ($token);		
		$shared_waba 		= isset($result['whatsapp_business_management'])? $result['whatsapp_business_management'] : [];
		$waba_id 			= false;
		
		
		if(count($shared_waba) == 1){
			return end($shared_waba);
		}
		else if(count($shared_waba) > 1){
			$waba_id = $this->filterWaba($shared_waba);
		}
		
		return $waba_id;
	}
	
	/*
	 *  debug the callback token to get the shared assets ex:waba_id
	 * 
	 * @parm array $shared_waba  
	 *
	 * @return int | false 
	 */
	public function filterWaba($shared_waba)
	{
		
		$result	= $this->sharedWaba();
		$shared_waba_id = false;
		

		if(!empty($result) && property_exists($result,'error')) 
		{
			$this->errors[] = $result->error;
		}
		
		if(property_exists($result,'data')) 
		{
			
			foreach ($result->data as $row )
			{
				
				if(in_array($row->id ,$shared_waba))
				{
					
					 /*
					  * if the found shared has phone_numbers its the required one 
					  */
					if($row->phone_numbers) 
					{
						
						$shared_waba_id = $row->id; 
						break;
					}
					
					//if not have phone_numbers continue searching, with catching the first found one 
					if(!$shared_waba_id)
						$shared_waba_id = $row->id;
					
				}
			}
		}
		
		return $shared_waba_id;
	}
	
	/*
	 *  debug the callback token to get the shared assets ex:waba_id
	 * 
	 * @parm string $token  
	 *
	 * @return array
	 */
	public function debugToken(string $token){
		
		$admin_token 		= self::$adminToken;
        $headers 		 	= [];
		$headers[]		 	= 'Content-Type: application/json';
		$_url 			 	= '/debug_token?input_token=' . $token . '&access_token='.$admin_token;
		$response 		 	= json_decode(WhatappCloudHelper::sendGraphApiRequest($_url,'GET',$headers,[]));
		$granular_scopes 	= $response->data->granular_scopes;
		$data 	  		 	= [];
		foreach($granular_scopes as $scope ){
			$data[$scope->scope]=$scope->target_ids;
		}
		
		return $data;
	}

	/*
	 *  Shared Whatsapp business account with our platform
	 * 
	 * @parm array $parms  
	 *
	 * @return object $response 
	 */
	public function sharedWaba(array $parms=[]){
		
		$sort = "creation_time_descending";
		$limit = "1000";
		$fields = "id,phone_numbers{display_phone_number}";
		
		if(isset($parms['sort']))
			$sort = $parms['sort'];
		
		if(isset($parms['limit']))
			$limit = $parms['limit'];
		
		if(isset($parms['fields']))
			$fields = $parms['fields'];
		
		
		$_url = '/'.self::$businessId.'/client_whatsapp_business_accounts?fields='.$fields.'&access_token='.self::$adminToken;
		$_url .= '&sort=' . $sort ;
		$_url .= '&limit=' . $limit;

		if(isset($parms['after'])){
			$_url .= '&after=' . $parms['after'];
		}else if (isset($parms['before'])){
			$_url .= '&before=' . $parms['before'];
		}

	    $headers = [];
		$headers[]="Content-Type: application/json";
		$headers[]="Authorization: Bearer ".$admin_token;

		$response = json_decode(WhatappCloudHelper::sendGraphApiRequest($_url,'GET',$headers,[]));
		return $response;
	}
	
	/*
	 *  assign system user to WABA Account 
	 * 
	 * @parm int 	$waba_id 
	 * @parm string $type  <admin|employee>
	 * @parm array	$tasks  
	 *
	 * @return object $response 
	 */
	public function assignUserToWaba(int $waba_id = 0,$type,$tasks){
		
		$user_id     = ($type == 'admin')? self::$adminUserId : self::$employeeUserId;
		$admin_token =  self::$adminToken ;

		$_url 		= '/' . $waba_id . '/assigned_users?user='.$user_id;
		$_url 	   .= "&tasks=['" . implode ( "','", $tasks ) . "']";
		$_url 	   .= "&access_token=".$admin_token;

		$headers	= [];
		$headers[]	= "Authorization: Bearer ".$admin_token;
		$headers[]	= "Accept: */*";
		$response 	= json_decode(WhatappCloudHelper::sendGraphApiRequest($_url,'POST',$headers,[]));
		
		return !empty($response->success) && $response->success;
	}
	
	/*
	 *  Fetch Assigned Users of WhatsApp Business Account
	 * 
	 * @parm int $waba_id 
	 *
	 * @return object $response 
	 */
	public function fetchAssignWaba(int $waba_id = 0){
		
		$admin_token 		= self::$adminToken;
		$_url 		= '/' . $waba_id . '/assigned_users?business='.self::$businessId;
        $headers	= [];
		$headers[]	= "Authorization: Bearer ".$admin_token;
		$headers[]	= "Accept: */*";
		$response 	= json_decode(WhatappCloudHelper::sendGraphApiRequest($_url,'GET',$headers,[]));
		return $response;
	}
	
	/*
	 *  our business line of credit id 
	 * 
	 * @parm int $waba_id 
	 *
	 * @return int | false
	 */
	public function lineOfCredit(){
		
		$employee_token 	= self::$employeeToken;
		$_url 				= '/' . self::$businessId . '/extendedcredits?fields=id,legal_entity_name';
       
	   $headers 	= [];
		$headers[]	= "Content-Type: application/json";
		$headers[]	= "Authorization: Bearer ".$employee_token;
		$response 	= json_decode(WhatappCloudHelper::sendGraphApiRequest($_url,'GET',$headers,[]));
		
		return $response->data[0]->id ? $response->data[0]->id : false; 
	}

	/*
	 *  share line of credit with client
	 * 
	 * @parm int $waba_id 
	 *
	 * @return object $response 
	 */
	public function shareLineOfCredit(int $waba_id = 0){
		
		$employee_token = self::$employeeToken;
		$_url 		= '/' . self::$creditId . '/whatsapp_credit_sharing_and_attach?waba_id='.$waba_id.'&waba_currency='.self::$wabaCurrency;
        $headers	= [];
		$headers[]	= "Authorization: Bearer ".$employee_token;
		$headers[]	= "Accept: */*";
		$response 	= json_decode(WhatappCloudHelper::sendGraphApiRequest($_url,'POST',$headers,[]));
		return $response;
	}
	
	/*
	 *  verify share line of credit with client 
	 * 
	 * @parm int $waba_id 
	 *
	 * @return object $response 
	 */
	public function verifyShareOfCredit(int $allocation_config_id = 0){
		
		$_url 		 = $allocation_config_id.'?fields=receiving_credential{id}';
        $headers	 = [
						"Authorization: Bearer ".self::$adminToken,
						];
		$response 	 = json_decode(WhatappCloudHelper::sendGraphApiRequest($_url,'GET',$headers,[]));
		
		return $response;
	}
	
	/*
	 * Subscribe App to WhatsApp Business Account to receive webhook
	 * 
	 * @parm int $waba_id 
	 *
	 * @return object $response 
	 */
	public function subscribeApp(int $waba_id = 0){
		$_url 		 = '/' .$waba_id.'/subscribed_apps';
        $headers	 = [
						"Authorization: Bearer ".self::$adminToken,
						];
		$response 	 = json_decode(WhatappCloudHelper::sendGraphApiRequest($_url,'POST',$headers,[]));
		return $response;
	}

	/*
	 * get WABA Phone numbers 
	 * 
	 * @parm int $waba_id 
	 *
	 * @return array
	 */
	public function wabaPhoneNumbers(int $waba_id = 0){
		
		$_url 		= '/' .$waba_id.'/phone_numbers?fields=display_phone_number,id,status';
		$_url  	   .= ',certificate,code_verification_status,account_mode,verified_name';
		
		$headers	= [
						"Authorization: Bearer ".self::$adminToken,
						];
		
		$response 	= json_decode(WhatappCloudHelper::sendGraphApiRequest($_url,'GET',$headers,[]));
		
		return ($response->data)? $response->data : [];
	}
	
	/*
	 * get phone number details 
	 * 
	 * @parm int $phone_id 
	 *
	 * @return object data 
	 * 
	 * ex response: 
	 * 
	 * {
	 * "verified_name": "xxxx",
	 * "code_verification_status": "xxxxx",
	 * "display_phone_number": "xxx xxxxxx",
	 * "quality_rating": "xxxx",
	 * "id": "xxxxxxxxx"
	 * }
	 *
	 */
	public function phoneNumberData(int $phone_id = 0){
		
		$_url 		= '/' .$phone_id;
		
		$headers	= [
						"Authorization: Bearer ".self::$adminToken,
						];
		
		$response 	= json_decode(WhatappCloudHelper::sendGraphApiRequest($_url,'GET',$headers,[]));
		
		return $response;
	}
		
	/*
	 * request phone-number OTP
	 * 
	 * @parm int 	$phone_number_id 
	 * @parm string	$code_method  <SMS|VOICE>
	 * @parm string	$language  	  en_US
	 *
	 * @return object $response 
	 */
	public function requestCode(int $phone_number_id,string $code_method='SMS',string $language='en_US'){
		
		$_url 		= '/' .$phone_number_id.'/request_code';
        $data		= [
						"code_method" => $code_method,
						"language" 	  => $language
						];
		
		$headers	 = [
						"Authorization: Bearer ".self::$adminToken,
						];
		$response 	= json_decode(WhatappCloudHelper::sendGraphApiRequest($_url,'POST',$headers,$data));
		
		return $response;
		
	}
		
	/*
	 * verify phone-number OTP
	 * 
	 * @parm int 	$phone_number_id 
	 * @parm int	$code 
	 *
	 * @return object $response 
	 */
	public function verifyCode(int $phone_number_id,string $code){
		
		$_url 		= '/' .$phone_number_id.'/verify_code';
        $data		= ["code" => $code];
		$headers	= [
						"Authorization: Bearer ".self::$adminToken,
					];
		
		$response 	= json_decode(WhatappCloudHelper::sendGraphApiRequest($_url,'POST',$headers,$data));
		
		return $response;
	}
			
	/*
	 * phone registeration for cloud API's 
	 * 
	 * @parm int 	$phone_number_id 
	 * @parm int	$bin 
	 * @parm array 	$backup 
	 *
	 * @return object $response 
	 */
	public function wabaRegister(int $phone_number_id,string $bin,array $backup=[]){
		$_url 		= '/' .$phone_number_id.'/register';
        $data		= [
						"messaging_product" => "whatsapp",
						"pin" 				=> $bin
						];
		
		if(!empty($backup)){
			$data["backup"] = $backup;
		}
		
		$headers	= [];
		$headers[]	= "Authorization: Bearer ".self::$adminToken;
		
		$response 	= json_decode(WhatappCloudHelper::sendGraphApiRequest($_url,'POST',$headers,$data));
		return $response;
	}
	
	/*
	 * get whatsapp account data 
	 * 
	 * @parm int $waba_id 
	 *
	 * @return object $response 
	 */
	public function wabaData(int $waba_id = 0){
		if(!self::$adminToken){
			return false;
		}
		$admin_token = self::$adminToken;
		
		$_url 		= '/' .$waba_id.'?fields=message_template_namespace,owner_business_info,account_review_status&access_token='.$admin_token;
        $headers	= [];
		$headers[]	= "Authorization: Bearer ".$admin_token;
		$headers[]	= "Accept: */*";
		$response 	= json_decode(WhatappCloudHelper::sendGraphApiRequest($_url,'GET',$headers,[]));
		return  $response;
	}
	

	//TO:DO | Need to update this method according to cloud API parameters 
	public function validateRequestActivation(array $data){
		
		$errors = [];
		
		if (mb_strlen($data['whatsapp_methods']) < 1 || isset($data['whatsapp_methods']) == false) {
			$errors['whatsapp_methods'] = 'error in "whatsapp methods " field';
		}
		if (mb_strlen($data['whatsapp_country_code']) < 1 || isset($data['whatsapp_country_code']) == false) {
			$errors['whatsapp_country_code'] = 'you should enter phone country code ex : 20 in egpyt case';
		}



        if (count($errors) > 0) {
			return [
				'hasErrors' => true,
				'errors' => $errors
			];
		}

		return [
			'hasErrors' => false
		];
	}
	
	//TO:DO | Need to update this method according to cloud API parameters 
	public function validateVerifyActivation(array $data){
		
		$errors = [];
		
		if (mb_strlen($data['whatsapp_verification_code']) < 1 || isset($data['whatsapp_verification_code']) == false) {
			$errors['whatsapp_verification_code'] = 'error in "whatsapp verification code " field';
		}

        if (count($errors) > 0) {
			return [
				'hasErrors' => true,
				'errors' => $errors
			];
		}

		return [
			'hasErrors' => false
		];
	}

	//====================== Ectools DB ======================//
	
	/*
	 * Ectools - Add Request
	 * 
	 * @parm array $data 
	 *
	 * @return int 
	 */
	public function requestIntegration(array $data) : int {
		
		$embedded_signup 	  	 = 1; //all requests at v2 from embedded Flow 
		$is_cloud   	 		 = 1; //all requests at v2 using cloud API's 

		$query   = [];
        $query[] = 'INSERT INTO ' . self::$integrationRequestTable . ' SET';
        $query[] = ' business_name  			= "' . $this->ecusersdb->escape($data['whatsapp_cloud_business_name']??'') . '",';
        $query[] = ' business_id    			= "' . $this->ecusersdb->escape($data['whatsapp_cloud_business_id']??'') . '",';
        $query[] = ' whatsapp_business_id   	= "' . $this->ecusersdb->escape($data['whatsapp_cloud_business_account_id']??'') . '",';
        $query[] = ' phone_number  				= "' . $this->ecusersdb->escape($data['whatsapp_cloud_phone_number_id']??'') . '",';
        $query[] = ' store_code  				= "' . STORECODE . '",';
        $query[] = ' status  					= "' . $this->ecusersdb->escape($data['whatsapp_cloud_sandbox_status']??'') . '",';
        $query[] = ' fb_status  				= "' . $this->ecusersdb->escape($data['whatsapp_cloud_fb_status']??'') . '",';
		$query[] = ' embedded_signup  			= "' . $embedded_signup . '",';
        $query[] = ' is_cloud  					= "' . $is_cloud . '",';
        $query[] = ' phone_number_id 			= "' . $this->ecusersdb->escape($data['whatsapp_cloud_phone_number_id']??'') . '",';
        $query[] = ' created_at  				= NOW()';
				
        $this->ecusersdb->query(implode(' ', $query));
        
		return $this->ecusersdb->getLastId();
	}

	/*
	 * update the last WA Request Record at ectools 
	 * set app_status = 'uninstall'	
	 *
	 */
	public function integrationRequestUninstall(): void {
		
		//avoid any possible exceptions from ectools DB
		try {
				$uninstall_status = 'uninstall';
				$sql = sprintf('UPDATE `'.self::$integrationRequestTable.'` set app_status = "%s" where store_code = "%s" order by id DESC LIMIT 1',$uninstall_status, strtoupper(STORECODE));
				$this->ecusersdb->query($sql);
				
			} catch (Exception $e) {}
					
	}
	
}
?>
