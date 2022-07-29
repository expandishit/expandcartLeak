<?php

class ModelModuleProductVideoLinks extends Model {

	public function isInstalled(){
        $isInstalled =  \Extension::isInstalled('product_video_links');
        
		if($isInstalled) {

			$status = $this->config->get('product_video_links_status');

			if($status){
            	return true;
			}

			return false;
        }

        return false;
	}

	public function getOrderStatus(){
		return $this->config->get('product_video_links_order_status_id_evu');
	}

}
