<?php

namespace Api\Http\Controllers\Module;

use Api\Http\Controllers\Controller;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;


class WhatsappController extends Controller
{
 
	 private  $config, $setting, $registry, $whatsapp;
	 private  $errors = [];

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->config 	= $this->container['config'];
        $this->setting 	= $this->container['setting'];
		$this->registry = $this->container['registry'];
        $this->whatsapp = $this->container['whatsapp'];
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
		 \WhatAppCommons::developerLog(" [webhook] #recevive |  ".json_encode($data)." | headers |  ".json_encode( getallheaders()) ,$this->config);
	
        if (!$this->validate($data))
			return $response->withJson(['status' => 'failed', 'errors' => $this->errors]);
           
		//check webhook type 
		if(isset($data['type']) && $data['type'] == 'account_update'){
			
			$allowed_status = ['EXPANDPAY_REVIEW','PENDING_REVIEW','VERIFIED_ACCOUNT','DISABLED_UPDATE'];
			$result = $this->_account_update($data['value']['event']);
			
			//add to logs
			\WhatAppCommons::developerLog(" [webhook] config edited ",$this->config);
			 
			return $response->withJson([
				'status' => 'success'
			]);
			
		}else {
			$this->errors =  ['not allowed webhook event'];
			\WhatAppCommons::developerLog(" [webhook] recieve unhandled webhook type  ",$this->config);
		}
		
		
		return $response->withJson(['status' => 'failed', 'errors' => $this->errors]);
   }

	/*
	 *  listen to webhook from ectools 
	 *  @param json array 
	 *  @return void
	 */
	private function _account_update($status){
            $this->setting->insertUpdateSetting('whatsapp_config', ['whatsapp_sandbox_status'=> $status]);
			$this->config->set('whatsapp_sandbox_status',$status); 
	}

    /*
	 *
	 *
	 */
    private function validate($parameters)
    {
		//return true;

		$signature =  isset(getallheaders()['X-Ec-Signature'])? getallheaders()['X-Ec-Signature'] : '';
		 if (!$this->whatsapp->validateSignature(json_encode($parameters), $signature )){
			 $this->errors[] = "invalid signature";
			\WhatAppCommons::developerLog(" [webhook] validation error - invalid signature ",$this->config);
		   return false;
		 }
		 
		 if (!\Extension::isInstalled('whatsapp')) {
			 $this->errors[] = "App not installed";
			\WhatAppCommons::developerLog(" [webhook] validation error - APP not installed ",$this->config);
			 return false;
		 }

		
        return true;
    }



}