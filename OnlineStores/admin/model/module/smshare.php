<?php
class ModelModuleSmshare extends Model {
    
    
    /**
     * 
     */
    public function notify_or_not_on_order_status_update($order_info, $data){
        $this->notify_or_not_customer_on_order_update($order_info, $data);
        $this->notify_or_not_store_owner_on_order_update($order_info, $data);
    }
    
    public function notify_or_not_on_admin_change_seller_status($message)
    {
        if (!SmsharePhonenumberFilter::passFilters($message['data']['seller_mobile'], $this->config)){
            return false;
        }
        
        $msg_type = $message['type'];
        
        $smshare_sms_template = $this->get_template_from_observer($msg_type, 'smshare_cfg_seller_observers');
        
        if (empty($smshare_sms_template)) {
            $smshare_sms_template = '{default_template}';
        }
        
        $find = array('{seller_email}' , '{seller_firstname}', '{seller_lastname}', '{seller_nickname}');
        
        $smshare_sms_template = str_replace($find, $message['data'], $smshare_sms_template);
        
        /*
        * If default_template keyword is present, we build the default message
        */
        if (strpos($smshare_sms_template, '{default_template}') !== false) {

            $this->language->load('module/smshare');
            $this->language->load('multiseller/multiseller');

            //Build the default message.
            $message  = $this->language->get('text_seller_status_notification_header') . "\n";
            $message .= $this->language->get('text_seller_status_notification_body_prefix') . ' ' .$this->language->get('ms_seller_status_' . $msg_type) . "\n\n";
            $message .= $this->language->get('text_seller_status_notification_footer');

            $find = array('{default_template}');
            $replace = array('default_template' => $message);
            
            $smshare_sms_template = str_replace($find, $replace, $smshare_sms_template);
        }
                
        $sms_to = SmsharePhonenumberFilter::rewritePhoneNumber($message['data']['seller_mobile'], $this->config);
        
        (new SmshareCommons())->sendSMS($sms_to, $smshare_sms_template, $this->config);
        
        return true;
    }
    
    /**
     * 
     */
    public function notify_or_not_customer_on_order_update($order_info, $data){
        
        //Optin: Allow or not sms sending to customer
        //hook_d2341
        
        
        if(!isset($data['notify_by_sms']) || !$data['notify_by_sms']) {
            if (SmshareCommons::log($this->config)) $this->log->write("[smshare] Do not notify customer on order update because notify_by_sms is false. Not checked or not submitted. Aborting!");
            return;
        }
        
        if(!SmsharePhonenumberFilter::passFilters($order_info['telephone'], $this->config)){
            return;
        }
    
        /*
         * Get all observers
         */
        $smshare_sms_template = $this->get_template_from_observer($data['order_status_id'], 'smshare_cfg_odr_observers');
        if(empty($smshare_sms_template)){
            $smshare_sms_template = '{default_template}';
        }
        
        $sms_body = $this->merge_template($smshare_sms_template, $order_info, $data);    
        $sms_to   = SmsharePhonenumberFilter::rewritePhoneNumber($order_info['telephone'], $this->config);
        
        $smshareCommons = new SmshareCommons();
        $smshareCommons->sendSMS($sms_to, $sms_body, $this->config);
        
    }
    
    
    /**
     * 
     */
    public function notify_or_not_store_owner_on_order_update($order_info, $data){
        
        /*
         * Get all observers
         */
        $smshare_sms_template = $this->get_template_from_observer($data['order_status_id'], 'smshare_cfg_odr_observers_for_admin');
        if(empty($smshare_sms_template)){
            if (SmshareCommons::log($this->config)) $this->log->write("[smshare] Do not notify store owner on order status update because no observer is found. Aborting!");
            return ;
        }
        
        //Merge
        $sms_body = $this->merge_template($smshare_sms_template, $order_info, $data);
        $sms_to   = $this->config->get('config_telephone');
        
        //hook to add any additional number
        //hook_91116
        
        $smshareCommons = new SmshareCommons();
        $smshareCommons->sendSMS($sms_to, $sms_body, $this->config);
        
    }
    
    /**
     * @param $observer_type 'smshare_cfg_odr_observers' or 'smshare_cfg_odr_observers_for_admin'
     */
    private function get_template_from_observer($order_status_id, $observer_type){
        $template = "";

        $observers = $this->config->get($observer_type);
        if($observers){
            foreach ($observers as $observer) {
                if($observer['key'] == $order_status_id){
                    $template = $observer['val'];
                    if(empty($template)) $template = '{default_template}';
                    if (SmshareCommons::log($this->config)) $this->log->write("[smshare] SMS template: " . $template);
                    break;
                }
            }
        }
        
        return $template;
    }
    
    /**
     * 
     */
    private function merge_template($smshare_sms_template, $order_info, $post_data){

        
        $language = new Language($order_info['language_directory']);
        $language->load($order_info['language_filename']);
        $language->load('mail/order');
        $language->load('module/smshare');
        
        $smshareCommons = new SmshareCommons();
        
        $order_id = $order_info['order_id'];
        
        //The order total
        $this->load->model('sale/order');
        
        //in template email module we used:
        //$order_total = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value']) ;
        $order_total = $smshareCommons->doGetOrderTotal($this->model_sale_order->getOrderTotals($order_id),$this->config);
        /*
         * 1- check if order has status id
         * 2- get status name based on status id and langugae id
         * 3- add status name to order info array in key status_name to send user order status name
         */
        
        if(!empty($order_info['order_status_id']))
        {
         $order_status_query = $this->db->query("SELECT name FROM " . DB_PREFIX . "order_status WHERE order_status_id = '" . (int)$order_info['order_status_id'] . "' AND language_id = '" . (int)$order_info['language_id'] . "'");
          $order_info['status_name'] = $order_status_query->row['name'];
        }
        //do replacement here
        $smshare_message = $smshareCommons->replace_smshare_variables($smshare_sms_template, $order_info, $order_total);
        
        $find = array('{comment}','{order_id}','{order_date}');
        $replace = array('comment' => strip_tags(html_entity_decode($post_data['comment'], ENT_QUOTES, 'UTF-8')),'order_id'=>$order_id,'order_date'=>$order_info['date_added']);
        $smshare_message = str_replace($find, $replace, $smshare_message);

        
        /*
         * If default_template keyword is present, we build the default message as opencart did for email and we do replacement.
        */
        $isDefaultTemplateKeywordUsed = strpos($smshare_sms_template, '{default_template}');
        if($isDefaultTemplateKeywordUsed !== false){
        
            //Build the default message.
        
            $message  = $language->get('text_order') . ' ' . $order_id . "\n";
            $message .= $language->get('text_date_added') . ' ' . date($language->get('date_format_short'), strtotime($order_info['date_added'])) . "\n\n";
        
            $order_status_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_status WHERE order_status_id = '" . (int)$post_data['order_status_id'] . "' AND language_id = '" . (int)$order_info['language_id'] . "'");
        
            if ($order_status_query->num_rows) {
                $message .= $language->get('text_order_status') . "\n";
                $message .= $order_status_query->row['name'] . "\n\n";
            }
        
            if ($order_info['customer_id']) {
                $message .= $language->get('text_link') . "\n";
                $message .= html_entity_decode($order_info['store_url'] . 'index.php?route=account/order/info&order_id=' . $order_id, ENT_QUOTES, 'UTF-8') . "\n\n";
            }
        
            if ($post_data['comment']) {
                $message .= $language->get('text_comment') . "\n\n";
                $message .= strip_tags(html_entity_decode($post_data['comment'], ENT_QUOTES, 'UTF-8')) . "\n\n";
            }
        
            $message .= $language->get('text_footer');
        
            $find = array('{default_template}');
            $replace = array('default_template' => $message);
            $smshare_message = str_replace($find, $replace, $smshare_message);
        }
        
        //hook_24ce6
        
        return $smshare_message;
    }
    
    /**
     * Check if app installed
     *
     * @return boolean
     */
    public function isInstalled()
    {
        return \Extension::isInstalled('smshare');
    }
}
?>
