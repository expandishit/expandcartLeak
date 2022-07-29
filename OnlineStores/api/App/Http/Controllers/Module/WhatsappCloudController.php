<?php

namespace Api\Http\Controllers\Module;

use Api\Http\Controllers\Controller;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;


class WhatsappCloudController extends Controller
{
 
	private  $config, $setting, $registry, $whatsapp;
	private  $errors = [];
	private  $module = 'whatsapp_cloud';
	
	private static $autoDownloadMedia = false;
	 
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->config 	 = $this->container['config'];
        $this->setting 	 = $this->container['setting'];
		$this->registry  = $this->container['registry'];
        $this->whatsapp_cloud = $this->container['whatsapp_cloud'];
    }
	
    /*
	 *  listen to webhook from ectools 
	 *  @param json array 
	 *  @return json array
	 */
    public function webhook(Request $request, Response $response)
    {
		$data =  $request->getParsedBody();
		 
		//logger for development prepose 
		\WhatappCloudHelper::developerLog(" [webhook] #recevive |  ".json_encode($data)." | headers |  ".json_encode( getallheaders()) );
	
		$payload = file_get_contents('php://input'); 
         if (!$this->validate($payload))
			return $response->withJson(['status' => 'failed', 'errors' => $this->errors]);
       

		$events_has_logic	= [
								'account_update',
								'account_review_update',
								'message_template_status_update',
								'messages'
								];
		
		//check webhook type 
		if(isset($data['type']) && in_array($data['type'],$events_has_logic)){
			
			$method = '_'.$data['type'];
			$status = $this->$method($data['value']) ? 'success' : 'failed';
		
			return ['status' => $status, 'errors' => $this->errors]; //errors array is internal for debuging usage 

			
		}else {
			$this->errors =  ['not allowed webhook event'];
			\WhatappCloudHelper::developerLog(" [webhook] recieve unhandled webhook type  ");
		}
		
		return $response->withJson(['status' => 'failed', 'errors' => $this->errors]);
    }

	/*
	 *  listen to webhook from ectools 
	 *  @param json array 
	 *  @return void
	 */
	private function _account_update($data){
		$status = $data["event"];
		if(in_array($status,['VERIFIED_ACCOUNT','PENDING_REVIEW','DISABLED_UPDATE'])){
            $this->setting->insertUpdateSetting($this->module, ['whatsapp_cloud_sandbox_status'=> $status]);
			$this->config->set('whatsapp_sandbox_status',$status); 
			\WhatappCloudHelper::developerLog(" [webhook] config edited ");
		}
		
		return true;
	}

	/*
	 *  listen to webhook from ectools 
	 *  @param json array 
	 *  @return void
	 */
	private function _account_review_update($data){
		
		$review_status = $data["decision"]??"";
		
		if(in_array($review_status,['APPROVED','PENDING','REJECTED'])){
			
			$review_Statuses = [
				'APPROVED' => 'VERIFIED_ACCOUNT',
				'PENDING'  => 'PENDING_REVIEW',
				'REJECTED' => 'DISABLED_UPDATE'
			];
			
            $this->setting->insertUpdateSetting($this->module, ['whatsapp_cloud_sandbox_status'=> $review_Statuses[$review_status]]);
			$this->config->set('whatsapp_sandbox_status',$review_Statuses[$review_status]); 
			\WhatappCloudHelper::developerLog(" [webhook] config edited ");
		}
				return true;

	}
	
	/*
	 *  listen to webhook from ectools 
	 *  @param json array 
	 *  @return void
	 */
	private function _message_template_status_update($data){
         
		$template_name		= $data["message_template_name"];
		$template_lang		= $data["message_template_language"];
		$template_status	= $data["event"];

		//for templates belong to observer 
		if (strpos($template_name, 'observers') !== false) {
			
			if(substr($template_name, 0, strlen("admin_order_observers")) === "admin_order_observers"){
			
				$observer_name = 'whatsapp_cloud_admin_order_observers';
			
			} else if(substr($template_name, 0, strlen("customer_order_observers")) === "customer_order_observers"){
				
				$observer_name = 'whatsapp_cloud_customer_order_observers';
			
			}  else if(substr($template_name, 0, strlen("seller_order_observers")) === "seller_order_observers"){
				
				$observer_name = 'whatsapp_cloud_seller_order_observers';
			
			}else{
				return false; //other unhandled observers 
			}

			return $this->_updateObserverTemplateStatus($observer_name,$template_name,$template_lang,$template_status);
		}
		
		$setting = $this->setting->getSettingByValue($template_name,"whatsapp_cloud");
			
		if(!empty($setting) && isset($setting["key"])){
				
			$key			= $setting["key"];			
			$template_key 	= str_replace("_name", '' , $key);
			$template 		= $this->config->get($template_key);
			
			$template[$template_lang]["STATUS"] = $template_status;
			
			$this->setting->insertUpdateSetting($this->module, [$template_key=> $template]);			
			return true;
		}
			
			return false;
	}	
	
	private function _updateObserverTemplateStatus($observer_name,$template_name,$template_lang,$status){
		
		$observer_templates = $this->config->get($observer_name);
		
		if(empty($observer_templates))
						return false;
		
		$template_index = '';
		if(!empty($observer_templates)){
			foreach	($observer_templates as $index => $template ){
				if($template["name"] == $template_name){
					$template_index = $index;
					break;
				}
			}
			
		}
		
		if(!isset($observer_templates[$template_index]))
			return false;
		
		
		$template = $observer_templates[$template_index];
		
		$template["data"][$template_lang]['STATUS'] = $status;
		
		$observer_templates[$template_index] = $template;
		
		$this->setting->insertUpdateSetting($this->module, [$observer_name => $observer_templates]);
		$this->config->set($observer_name,$observer_templates); 
		
		return true;
	}
	
	/*
	 *  listen to webhook from ectools 
	 *  @param json array 
	 *  @return void
	 */
	private function _messages($data){
		
		//TO:DO || verify that the received message to phone_number_id

		//is the same phone_number_id in config 
		//$data = $data["whatsapp_business_api_data"]??[];
		$contact 		= $data["contacts"][0] ?? [];
		$message		= $data["messages"][0] ?? [];
		$message		= $data["messages"][0] ?? [];
		$status			= $data["statuses"][0] ?? [];
		
		$wa_id 			= $contact["wa_id"]  		    ?? "";
		$profile_name	= $contact["profile"]["name"]	?? "";
		
		$fb_message_id	= $message["id"]	 			?? "";
		$message_text 	= $message["text"]["body"]		?? "";
		$type 			= $message["type"]				?? "text";
		$fb_timestamp 	= $message["timestamp"]			?? time();
		
		$fb_timestamp 	= gmdate("Y-m-d H:i:s",$fb_timestamp);
		$media_id		= null;
		
		if(!empty($contact)){
			$chat_id  	= $this->whatsapp_cloud->insertUpdateChat([
																	"phone_number" => $wa_id , 
																	"profile_name" => $profile_name
																	],true);
			if($chat_id){
				
				//handle media 
				if(in_array($type,["image","audio","video"])){
					
					$fb_media 	 		= $message[$type];
					$fb_media_id 		= $fb_media["id"];
					$media		   		= []; 
					$media["name"] 		= 'media_received'; //TO:DO | what to do later 
					$media["type"] 		= $type; 
					$media["caption"] 	= $fb_media["caption"]??""; 
					$media["fb_media"] 	= $fb_media; //till stability & make sure we no more need this object 
				
					$data_to_insert = [ 
										'media' 	  => json_encode($media),
										'fb_media_id' => $fb_media_id,
									];
									
					$media_url  = false;
					
					if(self::$autoDownloadMedia)
						$media_url  = $this->whatsapp_cloud->downloadMedia($fb_media_id);
					
					if($media_url)
						$data_to_insert['url']	= $media_url;
									
					$media_id 	= $this->whatsapp_cloud->insertMedia($data_to_insert);
				}
				// saving of message 
				
				$message_id = $this->whatsapp_cloud->insertMessage([
																	"chat_id"		=> $chat_id,
																	"type"			=> $type,
																	"fb_message_id"	=> $fb_message_id,
																	"from_me"		=> 0,
																	"type"			=> $type,
																	"media_id"		=> $media_id,
																	"text"			=> $message_text,
																	"fb_timestamp"	=> $fb_timestamp
																  ]);
				
				if(!$message_id)
					$this->errors[] =  'something went wrong , cant save message';

			} else {
				$this->errors[] =  "something went wrong , can't save chat";
			}
			
		}
		
		if(!empty($status)){
			$fb_message_id = $status["id"];
			$message = $this->whatsapp_cloud->getMessageFilter(["fb_message_id" => $fb_message_id]);
			
			if(!empty($message) && isset($message["id"])){
				$message_id = $message["id"];
				
				//fb statuses : sent , delivered , failed , 
				$this->whatsapp_cloud->updateMessage($message_id,["fb_status" => $status["status"]]);
			}
			
		}
		
		return empty($this->errors);
		
	}

    /*
	 *
	 *
	 */
    private function validate($payload)
    {

		//ectools signature 
		$signature =  isset(getallheaders()['X-Ec-Signature'])? getallheaders()['X-Ec-Signature'] : '';
		
		
		if (!$this->whatsapp_cloud->validateSignature($payload, $signature )){
			$this->errors[] = "invalid signature";
			\WhatappCloudHelper::developerLog(" [webhook] validation error - invalid signature ");
		}
		 
		if (!\Extension::isInstalled('whatsapp_cloud')) {
			$this->errors[] = "App not installed";
			\WhatappCloudHelper::developerLog(" [webhook] validation error - APP not installed ");
		}else {
			//TO:DO | make sure that this webhook belong to the current exists account 
			$data = json_decode($payload,true);
		//$data["waba_id"] == $this->config->get("whatsapp_cloud_business_account_id")
		}

		
        return empty($this->errors);
    }



}