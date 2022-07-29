<?php

namespace Api\Http\Controllers\Module;

use Api\Http\Controllers\Controller;
use Api\Models\Token;
use Api\Models\User;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class FacebookBusinessController extends Controller
{

    private   $config, $setting, $registry, $facebook_business;
	private   $handled_fileds 	  = ['fbe_install'] ;
	private   $handled_fbe_events = ['INSTALL','UNINSTALL'];
	private   $errors = [];
	
	protected $logger ;
	
    public function __construct(ContainerInterface $container){
        parent::__construct($container);
		$this->config 	= $this->container['config'];
        $this->setting 	= $this->container['setting'];
		$this->registry = $this->container['registry'];
        $this->facebook_business = $this->container['facebook_business'];
		$this->logger =  new \Log('facebook_api');
    }
	
	public function webhooks(Request $request){
		$data =  $request->getParsedBody();

		//accurate at signature validation more than getting the parssed body & re-encode it 
		$body = file_get_contents('php://input'); 
		$this->logger->write("[receive-webhook] payload : ".$body . "\n headers : ".json_encode(getallheaders()) );

		if (!$this->_validateEctoolsSignature($body))
			return $response->withJson(['status' => 'failed', 'errors' => $this->errors]);

		$payload_entry  = $data['entry'] ?? [];
		
		foreach($payload_entry as $entry){
			
			$user_id = $entry['uid']??"";
			$changes = $entry['changes']??[];
		
			foreach($changes as $change ){
				
				$field = $change['field']?? "";
				$value = $change['value']?? [];
				
				if(in_array($field, $this->handled_fileds)){
					$this->$field($value);
				}
				
			}
		}
	}
	
	public function batchWebhooks(Request $request, Response $response){

		$data = $request->getParsedBody();

		if (!$this->_batchValidate($data))
			return $response->withJson(['status' => 'failed', 'errors' => $this->errors]);
	 
		$batch_data   = $this->facebook_business->getBatch($data['batch_id']);
		
		if(empty($batch_data)){
			return $response->withJson(['status' => 'failed', 'errors' =>"batch not found!"]);
		}
		$localize     = $batch_data['localize'];
		$localizes    = explode (",", $batch_data['localize']);
		$catalog_id   = $batch_data['catalog_id'];
		$products_ids = $batch_data['products_ids'];
		
		$errors_total_count = (int)$batch_data['errors_total_count'];
		
		if($batch_data['fb_status'] == "finished"){
			
			$approved_ids = [];
			$approved_ids = explode (",", $products_ids);
			
			$errors = json_decode($batch_data["errors"]);
			
			if(!empty($errors)){
				$failed_ids = [];
				foreach ($errors as $error){
					$this->facebook_business->updatePushProduct([
																'push_status' 		=> 'rejected' ,
																'rejection_reason' 	=> $error->message  
																], $error->id
																 ,$catalog_id);
					$failed_ids[] =  $error->id;											 
				}
				
					$diff=array_diff($approved_ids,$failed_ids);
					$approved_ids = $diff;
			}
			
			if(!empty($approved_ids)){
				$this->facebook_business->updatePushProductsStatus($approved_ids,$catalog_id,'approved');
			}
			
			if(!empty($approved_ids) && !empty($localizes)){
				$this->facebook_business->sendLocalizeProducts($approved_ids,$localizes);			
			}
			
			
		}else if ($batch_data['fb_status'] == "error") {
				$this->facebook_business->updatePushProductsStatus($products_ids,$catalog_id,'error');
		}
		
		return $response->withJson(['status' => 'success']);
	}
	
	//------ facebook handled fields --------------//
	//fbe_install field for INSTALL/ UNINSTALL of FBE 
	private function fbe_install($data=[]){
		
		if(!$this->validateFbeInstall($data)){
			return false ;
		}

		$fbe_event = $data['fbe_event'] ?? "";
		
		if($fbe_event == 'INSTALL'){
			//return $this->fbeInstall($data);
		}else {
			return $this->fbeUnInstall($data);
		}
	}
	
	private function fbeUnInstall($data){
		$this->facebook_business->uninstallBusiness();
	}
	
	private function fbeInstall($data){
		return true ;
	}
	
	
	//-------- validations methods -----------------///
	private function _batchValidate($parameters){
		
        $signature =  isset(getallheaders()['X-Ec-Signature'])? getallheaders()['X-Ec-Signature'] : '';
		 if (!$this->facebook_business->validateEctoolsSignature(json_encode($parameters), $signature )){
			 $this->errors[] = "invalid signature";
			 $this->logger->write("invalid signature :". $signature ,' parameters :'. json_encode($parameters) . ' errors :' . json_encode($this->errors));
		   return false;
		 }
		 
		 return true;
	}
	
	//-------- validations methods -----------------///

	private function _validateEctoolsSignature($body){
		
        $signature =  isset(getallheaders()['X-Ec-Signature'])? getallheaders()['X-Ec-Signature'] : '';
		 if (!$this->facebook_business->validateEctoolsSignature($body, $signature )){
			 $this->errors[] = "invalid signature";
			 $this->logger->write("invalid signature :". $signature ,' body :'. $body . ' errors :' . json_encode($this->errors));
		   return false;
		 }
		 
		 return true;
	}
	
	//-------- validations methods -----------------///	
	private function validateFbeInstall($data)
    {
		$business_id = $data['business_id'] ?? "";
		$store_code = explode('_', $business_id)[0];
		
		//for webhook sample test 
		if ($store_code == 'sample_business_id'){
			$store_code = 'omartammam';
		}
		
		//make sure it belong to this store 
		if(strtoupper($store_code) != STORECODE ){
			$this->errors[] = "this request not belong to shis store ! ";
			return false;
		}
		
		
        return true;
		
	}
	
	//used on time at adding the webhook at FB Manager or trying to change it 
	public function webhooksVerify(){
		$params = $_GET; 
		$verify_token = 'expandcart_test';
		if(isset($params['hub_mode']) && isset($params['hub_challenge']) && isset($params['hub_verify_token'])){
			if($params['hub_verify_token'] == $verify_token){
				echo $params['hub_challenge'];
				
				exit;
			}
			return false;
		}
	}
	
   
}