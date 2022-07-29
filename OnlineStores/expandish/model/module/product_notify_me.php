<?php
class ModelModuleProductNotifyMe extends Model {
	public function getSettings()
    {
        return $this->config->get('product_notify_me');
    }

    public function isActive()
    {
        $settings = $this->getSettings();

        if (isset($settings) && $settings['status'] == 1) {
            return true;
        }

        return false;
    }

    public function addNotify($data){
	    if($data['product_id'] && $data['name'] && $data['email'] && $data['phone']){
	        $checkExistance = $this->db->query("SELECT id FROM `" . DB_PREFIX . "md_product_notify_me` WHERE product_id='".$data['product_id']."' AND email=".$data['email']." AND is_notified='0'");
	        if($checkExistance->num_rows > 0)
	            return 'exists';

	        $this->db->query("INSERT INTO `" . DB_PREFIX . "md_product_notify_me` (customer_id, product_id, name, email, phone, is_notified) VALUES (".implode(',',$data).")");
            return 'done';
	    }
    }
}
