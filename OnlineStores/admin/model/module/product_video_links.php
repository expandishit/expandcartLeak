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

	public function install(){
		if( !$this->_checkIfColumnExist() ){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "product` ADD external_video_url TEXT DEFAULT NULL;");
		}
	}

	public function uninstall(){
		$this->db->query("ALTER TABLE `" . DB_PREFIX . "product` DROP COLUMN external_video_url;");
	}

	private function _checkIfColumnExist(){
		$query = $this->db->query("SELECT COUNT(*) colcount
                                  FROM INFORMATION_SCHEMA.COLUMNS
                                  WHERE  table_name = 'product'
                                  AND table_schema = DATABASE()
                                  AND column_name = 'external_video_url'");
      	
      	return $query->row['colcount'] > 0 ? TRUE : FALSE;
	}
}
