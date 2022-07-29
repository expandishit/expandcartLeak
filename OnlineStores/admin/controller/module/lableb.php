<?php
set_time_limit(0);
/*
 *
 * Documentation URL : https://lableb.com/en/doc/rest/v2/getting-started
 * testing URL       : https://api-demo.lableb.com/
 *
 */ 
class ControllerModuleLableb extends Controller
{
	protected $logger;
    protected $error;
	protected $errors = [];
	private   $module_name 	= 'lableb';
	
	private static  $codes 	= [
								'SUCCESS'		 => 200,
								'VALIDATION_ERR' => 400, 
								'NOT_AUTHORIZED' => 401, 
								'RECORD_EXISTS'  => 409 
							  ];
							  
	private $queueFileLocation = DIR_SYSTEM . 'library/lableb_queue.php';
	
	private $status, $project_name, $api_key, $inprogress_indexing, $fresh_indexing_v1_1;
	private $has_project, $has_apikeys;
	private $email, $password;
	
	public function __construct ($registry){
		parent::__construct($registry);
		
		$setting_data 		= $this->config->get($this->module_name);
		$this->status 		= $setting_data['status'] ?? 0;
		$this->email 		= $setting_data['email'] ?? "";
		$this->password 	= $setting_data['password'] ?? "";
		$this->project_name = $setting_data['project'] ?? "";
		$this->has_project 	= isset($setting_data['project']) && !empty($setting_data['project']);
		$this->has_apikeys 	= ( 
								$this->has_project  //if it has no project so no apikey 
								&& isset($setting_data['index_apikey'])
								&& !empty($setting_data['index_apikey'])
								&& isset($setting_data['search_apikey'])
								&& !empty($setting_data['search_apikey'])
								);
		$this->api_key 		= $this->has_apikeys? $setting_data['index_apikey'] : '';
		
		$this->inprogress_indexing = $setting_data['inprogress_indexing']??0;
		$this->fresh_indexing_v1_1 = $setting_data['fresh_indexing_v1_1']??0;
		$this->logger 		= new \Log('lableb');
		
		$this->initializer([
							'module/lableb'
							]);	
	}
	
    public function index(){
       
        $this->language->load('module/lableb');
        $this->document->setTitle($this->language->get('lableb_heading_title'));
        $this->initializer([
							'setting/setting',
							'catalog/product'
							]);
							
        $this->data['settings_data'] = $this->config->get($this->module_name);

		$registered_before = $this->config->get('is_lableb_register_before');
		if( 
			$registered_before != "" 
			|| (
				$this->data["settings_data"]["search_apikey"] != "" 
				&& $this->data["settings_data"]["index_apikey"] != ""
				)
		) {
			$this->data["register_before"] = 1;
            
			if($registered_before == "") {
                $this->load->model("setting/setting");
                $this->setting->insertUpdateSetting("config", ['is_lableb_register_before' => 1]);
            }
          }
		
		//for already old installed users 
		if(!isset( $this->data['settings_data']['indexed_product_table_created'])){
			
			$this->lableb->createIndexedProductTable();
			$this->data['settings_data']['indexed_product_table_created'] = 1;
			$this->setting->editSetting($this->module_name, [$this->module_name => $this->data['settings_data']]);
		}
        
		$this->data['is_project_plain_expired'] = $this->_isProjectPlanExpired();
		$this->data['total_products'] 			= $this->product->getTotalProductsCount();
        $this->data['total_active_products'] 	= $this->product->getTotalActiveProductsCount();
        $this->data['indexed_products']  		= $this->lableb->indexedProductsCounts();
        $this->data['inprogress_indexing']  	= $this->data["settings_data"]["inprogress_indexing"]??0;
        $this->data['fresh_indexing_v1_1']  	= $this->data["settings_data"]["fresh_indexing_v1_1"]??0;
        $this->data['has_project_and_api_keys'] = $this->has_project && $this->has_apikeys ? 1 : 0  ;
		
		$this->data['action'] 		= $this->url->link('module/lableb/updateSettings', '', 'SSL');
        $this->data['cancel'] 		= $this->url->link('marketplace/home', '', 'SSL');
		$this->data['breadcrumbs'] 	= array([
											'text'      => $this->language->get('text_home'),
											'href'      => $this->url->link('common/home', '', 'SSL'),
											'separator' => false
											],[
											'text'      => $this->language->get('text_modules'),
											'href'      => $this->url->link('marketplace/home','','SSL'),
											'separator' => ' :: '
											],[
											'text'      => $this->language->get('lableb_heading_title'),
											'href'      => $this->url->link('module/lableb', '', 'SSL'),
											'separator' => ' :: '
											]);
        $this->children = array(
            'common/header',
            'common/footer',
        );
		
        $this->template = 'module/lableb/settings.expand';
        $this->response->setOutput($this->render());
    }

    public function register(){
		$result_json = [];
		$result_json['success'] = '0';
        $result_json['errors'] = [];
		
        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)) {
            $result_json['errors'][] = 'Invalid Request';
			$this->response->setOutput(json_encode($result_json));
            return;
		}
		
		//TO:DO this condition need review 
        if (!in_array($this->config->get("is_lableb_register_before"), ["1", 1])) {
            if (!$this->_validateRegister()) {
                $result_json['errors'] = $this->error;
                $this->response->setOutput(json_encode($result_json));
                return;
            }
			
            $request = $this->lableb->register($this->request->post['register_lableb']);
            
			if ($request->code == self::$codes['VALIDATION_ERR']) {
                foreach ($request->response->errors as $key => $value) {
                        $this->error[] = $key . " " . $value[0];
                    }
					
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
                $this->response->setOutput(json_encode($result_json));
                return;
				
            } elseif ($request->code ==  self::$codes['RECORD_EXISTS']) { 
                
				$result_json['success'] = '0';
                $result_json['errors'] = $this->language->get('this_account_already_existed');

                $this->response->setOutput(json_encode($result_json));
                return;
				
            } else if ($request->code ==  self::$codes['SUCCESS']) { 

                $this->load->model('setting/setting');
                $this->model_setting_setting->insertUpdateSetting("config", ['is_lableb_register_before' => 1]);

                $result_json['success'] = '1';
                $result_json['success_msg'] = $this->language->get('text_register_success');
                $this->response->setOutput(json_encode($result_json));
                return;
            }
			
			$this->response->setOutput(json_encode($result_json));
            return;
        }
    }
 
    public function updateSettings(){
		
        if ( !$this->_validateSettings() ){
            $result_json['success'] = '0';
            $result_json['errors'] = $this->errors;
            $this->response->setOutput(json_encode($result_json));
			return;
        }
		
		$this->language->load('module/lableb');
		$this->initializer([
							'setting/setting'
							]);	
       
        $data 			     = $this->request->post['lableb'];
		$project_name_before = $this->project_name;		
		$api_key_before		 = $this->api_key;	
		$status_before		 = $this->status;	
		
        if (isset($data['status'])) {
            $this->status = $data['status'];
        }
		
		//-------- project check & create if not exits 
        if(!$this->has_project){
			$this->_createProject($data);
        }
		
		//------ apis check & create if not exists 
		if ($this->has_project && !$this->has_apikeys){
			$this->_createApiKeys($data);
		}
		
		$data['fresh_indexing_v1_1'] = $this->fresh_indexing_v1_1;
		# save to DB if project or api_key or status values changed 
		if(
			   $project_name_before != $this->project_name 
			|| $api_key_before      != $this->api_key  
			|| $status_before       != $this->status  
		){
			$this->_saveSetting($data, $this->project_name , $this->api_key);
		}
        $app_setting['settings_data'] = $this->config->get($this->module_name);
        $planUsageEndDate = $this->__getLablebPlanUsageEndDate($data);
		
        if ($planUsageEndDate) {
            $app_setting['settings_data']['project_usage_plan_end_date'] = $planUsageEndDate;
            $this->setting->editSetting($this->module_name, [$this->module_name => $app_setting['settings_data']]);
        }
        //---check if there any error return it
		if(!empty($this->errors)){
			$result_json['success'] = '0';
			$result_json['errors'] = $this->errors;
			 $this->response->setOutput(json_encode($result_json));
			return;
		}
		
		
        $this->session->data['success'] = $this->language->get('text_settings_success');
        $this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success');
       
	    $result_json['success']  = '1';
        $result_json['redirect'] = '1';
        $result_json['to'] 		 = (string) $this->url->link('module/lableb' , '', 'SSL');
        $this->response->setOutput(json_encode($result_json));
        return;
    }

    public function indexing(){
		$this->language->load('module/lableb');
		
		$result_json = [];
		$result_json['success'] = '0';
        $result_json['errors'] = [];
		$this->initializer([
							'setting/setting'
							]);
	   if (!$this->_validateIndexing()) {
                $result_json['errors'] = $this->errors;
                $this->response->setOutput(json_encode($result_json));
                return;
        }
	    
		$project_name 	= $this->project_name;
        $index_apikey 	= $this->api_key;
		$file_location 	= DIR_SYSTEM . 'library/lableb_queue.php';
        $storecode 		= STORECODE;
		
		
		$setting_data= $this->config->get($this->module_name);
		$setting_data['inprogress_indexing'] = 1;
		$this->setting->editSetting($this->module_name, [$this->module_name => $setting_data]);
		$this->config->set($this->module_name,$setting_data );
		
		shell_exec( "php $file_location $storecode  >/dev/null 2>&1 &");
		//shell_exec( "php $file_location $storecode  > NUL 2>&1"); //for local test but works sync have no idea why :) 
		//$this->productIndexing();

		 $result_json['success'] = '1';
         $result_json['success_msg'] = $this->language->get('job_done_message');
         $this->response->setOutput(json_encode($result_json));
         return;
		 
		
    }
	
	public function productIndexing(){
		$this->initializer([
							'setting/setting'
							]);
		$setting_data = $this->config->get($this->module_name);
	   // $setting_data['inprogress_indexing'] = 1;
		//$this->setting->editSetting($this->module_name, [$this->module_name => $setting_data]);
		$result = false;
		try{
			$this->load->model('catalog/product');
			$all_products    = $this->model_catalog_product->getLablebProducts();
			$indexing_result = $this->lableb->indexProducts($all_products);
			$setting_data['inprogress_indexing'] = 0;
		}
		 catch (Exception $e) {
			 $setting_data['inprogress_indexing'] = 0;
		}
		 
		 $this->setting->editSetting($this->module_name, [$this->module_name => $setting_data]);
	}
	
    public function fresh_indexing(){
		$this->language->load('module/lableb');
		
		$result_json = [];
		$result_json['success'] = '0';
        $result_json['errors'] = [];
		$this->initializer([
							'setting/setting'
							]);

	   if (!$this->_validateIndexing()) {
                $result_json['errors'] = $this->errors;
                $this->response->setOutput(json_encode($result_json));
                return;
        }
	    
		$project_name 	= $this->project_name;
        $index_apikey 	= $this->api_key;
		$file_location 	= DIR_SYSTEM . 'library/lableb_queue.php';
        $storecode 		= STORECODE;		
		
		$setting_data= $this->config->get($this->module_name);
		$setting_data['inprogress_indexing'] = 1;
		$setting_data['fresh_indexing_v1_1'] = 1;
		$this->setting->editSetting($this->module_name, [$this->module_name => $setting_data]);
		$this->config->set($this->module_name,$setting_data );  //for fixing caching issue 
		
		//truncate old data 
		//$result = $this->lableb->truncateProject(); //we no more need it , & Lableb required stopping it 
		
		shell_exec( "php $file_location $storecode  >/dev/null 2>&1 &");
		//shell_exec( "php $file_location $storecode  > NUL 2>&1"); //for local test but works sync have no idea why :) 
		//$this->productIndexing();

		 $result_json['success'] = '1';
         $result_json['success_msg'] = $this->language->get('job_done_message');
         $this->response->setOutput(json_encode($result_json));
         return;
		 
		
    }
	
	public function indexing_status(){
		$result_json = [
						'inprogress_indexing' => $this->inprogress_indexing, 
						'indexed_products'	  => $this->lableb->indexedProductsCounts()
						];

		$this->response->setOutput(json_encode($result_json));
        return;
	}
	
	public function install() {		
		$this->initializer([
							'setting/setting'
							]);
		$this->lableb->createIndexedProductTable();
		$setting_data = [];
		$setting_data['indexed_product_table_created'] =1 ;
		
		$this->setting->insertUpdateSetting($this->module_name, [$this->module_name => $setting_data]);
	}
	
	public function uninstall() {
		$this->lableb->dropIndexedProductTable();
	}
	
	//==============[ helper Functions ]=====================//
	private function  _createProject($data): void {
		$project_name = strtolower("expand_"  . STORECODE   );
			
		//Note: Login after remove App is creating a new project 
		//this case should handled at the new UI flow 
			
        $project_data = $this->lableb->createNewProject($project_name,$data);
             
		if ($project_data->code ==  self::$codes['SUCCESS']) {
				$this->project_name = $project_name;
				$this->has_project  = true ;
		} else if ($project_data->code ==  self::$codes['RECORD_EXISTS']) {
			// Project exists
			//if project exists we will add a random key to the store_code 
            $random_key 		= $this->_randomString();
            $project_name 		= $project_name .  "_" . $random_key;
            $project_data 		= $this->lableb->createNewProject($project_name,$data);
				
			// project created 
            if ($project_data->code ==  self::$codes['SUCCESS']) {
                $this->has_project  = true ;
				$this->project_name = $project_name;
            }
        }
			
		//if still not has_project return error 
		if(!$this->has_project){
			$this->errors[] = 'something went wrong , please try again later';
		}
	}
	
	private  function _createApiKeys($data): void {
		$api_key = false;
		
		$response = $this->lableb->projectApiKeys($this->project_name,$data);
			
		if ($response->code ==  self::$codes['SUCCESS'] && empty($response->response->apikeys)) {
                
			$response = $this->lableb->newProjectApiKey($this->project_name,$data);
               
			if ($response->code == self::$codes['NOT_AUTHORIZED']) { 
                $this->errors = $response->response;
				return;
            }
                
			$this->api_key =  $response->response->apikey->apikey;
					
        } else if ($response->code ==  self::$codes['SUCCESS'] && !empty($response->response->apikeys)) {
            $this->api_key =  $response->response->apikeys[0]->apikey;
        } else if ($response->code ==  self::$codes['NOT_AUTHORIZED']) {        
                $this->errors = $response->response;
				return;
            }
	}
	
	private function _saveSetting($data, $project_name , $api_key){
       			
        $setting_data = array();
        $setting_data['project'] 		= $project_name;
        $setting_data['index_apikey']   = $api_key?? '';
        $setting_data['search_apikey']  = $api_key?? '';
        $setting_data['status'] 		= $data['status'];

        $setting_data = array_merge($data, $setting_data);
        $this->setting->editSetting($this->module_name, [$this->module_name => $setting_data]);
    }
		
	private function _randomString($length = 10) {
		//no capital letters as its not valid for project creation
		return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyz', ceil($length/strlen($x)) )),1,$length);
	}

    private function _isProjectPlanExpired(){
		
		if( empty($this->project_name) 
			|| empty($this->email)
			|| empty($this->password)
		){
			return false ;
		}
		
		$loginData = [
					  "email" 	 => $this->email,
                      "password" => $this->password
					];
				
        $planUsage = $this->lableb->getLablebProjectPlanUsage($this->project_name, $loginData);
        
		$planUsageEndDate = "";
		if ($planUsage && $planUsage->code == self::$codes['SUCCESS']) {
                 $planUsageEndDate = $planUsage->response->plan_usage->end_date;
        }

        if (!$planUsageEndDate)
            return false;
		
        $time_zone 	 = $this->config->get('config_timezone') ?: 'UTC';
        $currentDate = new DateTime('NOW', new DateTimeZone($time_zone));
        $endDate 	 = new DateTime($planUsageEndDate, new DateTimeZone($time_zone));

        return ($currentDate > $endDate);
    }

    //============[ validation Functions ]==================//
    private function _validateRegister(){
		
        $post_data = $this->request->post['register_lableb'];
        $this->language->load('module/lableb');


        if (!isset($post_data['name']) ||  empty($post_data['name']) ){
            $this->error[] = $this->language->get('error_name_required');
        }

        if (!isset($post_data['first_name']) ||  empty($post_data['first_name']) ){
            $this->error[] = $this->language->get('error_first_name_required');
        }

        if (  !isset($post_data['last_name']) ||  empty($post_data['last_name']) ){
            $this->error[] = $this->language->get('error_last_name_required');
        }

        if (  !isset($post_data['phone_number']) ||  empty($post_data['phone_number']) ){
            $this->error[] = $this->language->get('error_phone_number_required');
        }

        if (  !isset($post_data['email']) ||  empty($post_data['email']) ){
            $this->error[] = $this->language->get('error_email_required');
        }

        if (  !isset($post_data['password']) ||  empty($post_data['password']) ){
            $this->error[] = $this->language->get('error_password_required');
        }

        if (  !isset($post_data['confirmed']) ||  empty($post_data['confirmed']) ){
            $this->error[] = $this->language->get('error_confirm_password_required');
        }

        if (  $post_data['password'] !=  $post_data['confirmed'] ){
            $this->error[] = $this->language->get('error_confirm_password_not_match');
        }

        return $this->error ? false : true;
    }

    private function _validateSettings(){
        $post_data = $this->request->post['lableb'];
        $this->language->load('module/lableb');

		 if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)) {
          $this->errors['request']= 'Invalid Request';
        }

        if (  !isset($post_data['email']) ||  empty($post_data['email']) ){
            $this->errors['email'] = $this->language->get('error_email_required');
        }

        if (  !isset($post_data['password']) ||  empty($post_data['password']) ){
            $this->errors['password'] = $this->language->get('error_password_required');
        }

        if ( $this->errors && !isset($this->errors['error']) ){
            $this->errors['warning'] = $this->language->get('error_warning');
        }

        return $this->errors ? false : true;
    }

	private function _validateIndexing(){
		
       $project_name 		= $this->project_name;
       $index_apikey 		= $this->api_key;
       $inprogress_indexing = $this->inprogress_indexing;
	   
	   if(!$project_name || !$index_apikey){
		   $this->errors[] = "some important data are missing , please re-login!";
	   }
	   
	   if($inprogress_indexing){
		   $this->errors[] = "there is already in-progress indexing";
	   }
	   return $this->errors ? false : true;
	    
	}
	
	//============= new  UI [Not Used Yet] ===============//
    public function connect() {
		$this->load->language('module/lableb');
        $this->template = 'module/lableb/connect.expand';
        $this->children = array(
            'common/header',    
            'common/footer'    
        );
        $this->response->setOutput($this->render());
	}

    public function newsettings() {
		$this->load->language('module/lableb');
        $this->template = 'module/lableb/newsettings.expand';
        $this->children = array(
            'common/header',    
            'common/footer'    
        );
        $this->response->setOutput($this->render());
	}


}
