<?php

namespace Api\Models\Module;

use Psr\Container\ContainerInterface;

class FacebookBusiness
{
    private $load;
    private $registry;
    private $config;
    private $facebook_settings_model;

    public function __construct(ContainerInterface $container){
        $this->registry = $container['registry'];
        $this->config = $container['config'];
        $this->load = $container['loader'];
		
		$this->load->model('module/facebook_business');
	   $this->facebook_model = $this->registry->get('model_module_facebook_business');
    }
	
	public function getBatch($batch_id){
		return $this->facebook_model->getBatch($batch_id);
	}
	
	public function getBatchProducts($batch_id){
		return $this->facebook_model->getBatchProducts($batch_id);
	}
	
	public function updatePushProductsStatus($products_ids,$status){
		return $this->facebook_model->updatePushProductsStatus($products_ids,$status);
	}
	public function updatePushProduct($data,$retailer_id ,$catalog_id){
		return $this->facebook_model->updatePushProduct($data,$retailer_id ,$catalog_id);
	}
	
	public function sendLocalizeProducts($products_ids,$localizes=[]){
		return $this->facebook_model->sendLocalizeProducts($products_ids,$localizes);
	}
	
	public function batchStatus($catalog_id,$handle,$token){
		return $this->facebook_model->batchStatus($catalog_id,$handle,$token);
	}
	
	public function validateEctoolsSignature($body, $header_signature = '') { 
        return $this->facebook_model->validateEctoolsSignature($body, $header_signature);
    }
	
	public function uninstallBusiness(){
		$this->facebook_model->logout();
	}


}
