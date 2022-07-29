<?php
class ModelModuleSmshare extends Model {
    
    private function get_admins_numbers(){
        $admins = "";
         
        if($this->config->get('smshare_config_notify_admin_by_sms_on_checkout')){    //coded this way because we can send to additional numbers even if the notify admin setting is off.
            $admins = $this->config->get('config_telephone');
        }else{
            if (SmshareCommons::log($this->config)) $this->log->write('[smshare] Notify admin setting is off.. Checking the additional numbers..');
        }
        
        $admin_extra_numbers = $this->config->get('smshare_config_notify_extra_by_sms_on_checkout');
        if(!empty($admin_extra_numbers)){
            if(! empty($admins)){
                $admins .= ',';
            }
            $admins .= $admin_extra_numbers ;
        }
        
        return $admins;
    }
    
    public function notify_or_not_admin_on_checkout($order_info, $order_total_query, $order_status, $products, $vouchers){
        
        if (SmshareCommons::log($this->config)) $this->log->write('[smshare] Notify (or not) admin(s) on checkout..');
        
        $smshareCommons = new SmshareCommons ();
        $order_id = $order_info ['order_id'];
        $language = new Language ( $order_info ['language_code']);
        $language->load_json ( 'mail/order' );
        
        $admins = $this->get_admins_numbers();
        
        if (empty ( $admins )) {
            if (SmshareCommons::log ( $this->config ))
                $this->log->write ( '[smshare] No admins phone number and there are no extra admin phone numbers. No SMS will be sent. Abort!' );
            return;
        }
        
        // retrieve the template:
        $sms_template = $this->config->get ( 'smshare_config_sms_template_for_storeowner_notif_on_checkout' );
        
        $smshare_order_total = '';
        if($order_total_query){
            $smshare_order_total = $smshareCommons->doGetOrderTotal ( $order_total_query->rows,$this->config );
        }
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
        $sms_template = $smshareCommons->replace_smshare_variables ( $sms_template, $order_info, $smshare_order_total);
        
        $isDefaultTempateKeywordUsed = strpos ( $sms_template, '{default_template}' );
        
        if ($isDefaultTempateKeywordUsed !== false) {
            //
            // Text for defaultTemplate
            //
            $text = $language->get ( 'text_new_received' ) . "\n\n";
            $text .= $language->get ( 'text_new_order_id' ) . ' ' . $order_id . "\n";
            $text .= $language->get ( 'text_new_date_added' ) . ' ' . date ( $language->get ( 'date_format_short' ), strtotime ( $order_info ['date_added'] ) ) . "\n";
            $text .= $language->get ( 'text_new_order_status' ) . ' ' . $order_status . "\n\n";
            $text .= $language->get ( 'text_new_products' ) . "\n";
            
            foreach ( $products as $result ) {
                $text .= $result ['quantity'] . 'x ' . $result ['name'] . ' (' . $result ['model'] . ') ' . html_entity_decode ( $this->currency->format ( $result ['total'], $order_info ['currency_code'], $order_info ['currency_value'] ), ENT_NOQUOTES, 'UTF-8' ) . "\n";
                
                $order_option_query = $this->db->query ( "SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . ( int ) $order_id . "' AND order_product_id = '" . $result ['order_product_id'] . "'" );
                
                foreach ( $order_option_query->rows as $option ) {
                    if ($option ['type'] != 'file') {
                        $value = $option ['value'];
                    } else {
                        $value = utf8_substr ( $option ['value'], 0, utf8_strrpos ( $option ['value'], '.' ) );
                    }
                    
                    $text .= chr ( 9 ) . '-' . $option ['name'] . ' ' . (utf8_strlen ( $value ) > 20 ? utf8_substr ( $value, 0, 20 ) . '..' : $value) . "\n";
                }
            }
            
            foreach ( $vouchers as $voucher ) {
                $text .= '1x ' . $voucher ['description'] . ' ' . $this->currency->format ( $voucher ['amount'], $order_info ['currency_code'], $order_info ['currency_value'] );
            }
            
            $text .= "\n";
            
            $text .= $language->get ( 'text_new_order_total' ) . "\n";
            
            if($order_total_query){
                foreach ( $order_total_query->rows as $result ) {
                    $text .= $result ['title'] . ': ' . html_entity_decode ( $result ['text'], ENT_NOQUOTES, 'UTF-8' ) . "\n";
                }
            }
            
            $text .= "\n";
            
            if ($order_info ['comment']) {
                $text .= $language->get ( 'text_new_comment' ) . "\n\n";
                $text .= $order_info ['comment'] . "\n\n";
            }
            
            //
            // Do default_template variable substitution.
            //
            $find = array (
                    '{default_template}' 
            );
            $replace = array (
                    'default_template' => $text 
            );
            $sms_template = str_replace ( $find, $replace, $sms_template );
        } // End if isDefaultTempateKeywordUsed
        
        $isCompactDefaultTempateKeywordUsed = strpos ( $sms_template, '{compact_default_template}' );
        
        if ($isCompactDefaultTempateKeywordUsed !== false) {
            //
            // Text for compactDefaultTemplate
            //
            
            $text = 'ID:' . $order_id . "\n";
            $text .= 'Status:' . $order_status . "\n\n";
            $text .= $language->get ( 'text_new_products' ) . ":";
            
            foreach ( $products as $result ) {
                $text .= $result ['quantity'] . 'x ' . $result ['name'] . html_entity_decode ( $this->currency->format ( $result ['total'], $order_info ['currency_code'], $order_info ['currency_value'] ), ENT_NOQUOTES, 'UTF-8' ) . "\n";
                
                $order_option_query = $this->db->query ( "SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . ( int ) $order_id . "' AND order_product_id = '" . $result ['order_product_id'] . "'" );
                
                foreach ( $order_option_query->rows as $option ) {
                    if ($option ['type'] != 'file') {
                        $value = $option ['value'];
                    } else {
                        $value = utf8_substr ( $option ['value'], 0, utf8_strrpos ( $option ['value'], '.' ) );
                    }
                    
                    $text .= chr ( 9 ) . '-' . $option ['name'] . ' ' . (utf8_strlen ( $value ) > 20 ? utf8_substr ( $value, 0, 20 ) . '..' : $value) . "\n";
                }
            }
            
            foreach ( $vouchers as $voucher ) {
                $text .= '1x ' . $voucher ['description'] . ' ' . $this->currency->format ( $voucher ['amount'], $order_info ['currency_code'], $order_info ['currency_value'] );
            }
            
            $text .= "\n";
            
            foreach ( $order_total_query->rows as $result ) {
                if ('Total' === $result ['title']) {
                    $text .= $result ['title'] . ':' . html_entity_decode ( $result ['text'], ENT_NOQUOTES, 'UTF-8' ) . "\n";
                }
            }
            $text .= "\n";
            
            if ($order_info ['comment']) {
                $text .= $language->get ( 'text_new_comment' ) . "\n\n";
                $text .= $order_info ['comment'] . "\n\n";
            }
            
            //
            // Do default_template variable substitution.
            //
            $find = array (
                    '{compact_default_template}' 
            );
            $replace = array (
                    'compact_default_template' => $text 
            );
            $sms_template = str_replace ( $find, $replace, $sms_template );
        }
        
        $smshareCommons->send_sms(array(
            'to'     => $admins,
            'body'   => $sms_template,
            'config' => $this->config
        ));
        
    }
    
    /**
     * 
     */
    public function notify_or_not_customer_on_checkout($order_info, $order_total_query){
        
        $smshareCommons = new SmshareCommons();
        
        $keyword_found = false;
        
        //
        // Get coupon
        //
        $donotsend_coupon_keywords = $this->config->get('smshare_cfg_donotsend_sms_on_checkout_coupon_keywords');
        
        if (! $smshareCommons->isNullOrEmptyString($donotsend_coupon_keywords) && $order_total_query) {
            
            foreach ( $order_total_query->rows as $result ) {
                $code = $result ['code'];
                if ($code == 'coupon') {
                    if (SmshareCommons::log($this->config)) $this->log->write('[smshare] Customer used coupon so handle coupon');
                    
                    $title = $result ['title'];
                    
                    // foreach smshare do-not-send sms keyword
                    foreach ( preg_split ( "/((\r?\n)|(\r\n?))/", $donotsend_coupon_keywords ) as $donotsend_coupon_keyword ) {
                        // if coupon contains one of these keywords, then set the do not send SMS boolean to false.
                        $pos = strpos ( $title, $donotsend_coupon_keyword );
                        if ($pos !== false) {
                            $keyword_found = true;
                            if (SmshareCommons::log($this->config)) $this->log->write('[smshare] keyword found, we should not send SMS to customer on checkout.');
                            break;
                        }
                    }
                    break;
                }
            }
        }
        
        if($keyword_found === false) {
            
            $smshare_patterns   = $this->config->get('smshare_config_number_filtering');
            $number_size_filter = $this->config->get('smshare_cfg_num_filt_by_size');
            
            if($this->config->get('smshare_config_notify_customer_by_sms_on_checkout')                      &&
               SmsharePhonenumberFilter::isNumberAuthorized($order_info['telephone'], $smshare_patterns)    &&
               SmsharePhonenumberFilter::passTheNumberSizeFilter($order_info['telephone'], $number_size_filter)
            ) {

                $smshare_message = '';

                $payment_code = strtolower($order_info['payment_code']);

                if (array_key_exists('check_if_failed',$order_info)
                    && !empty($this->config->get($payment_code.'_failed_order_status_id'))
                    && $order_info['check_if_failed'] == $this->config->get($payment_code.'_failed_order_status_id')) {
                    $sms_templates = $this->config->get('smshare_cfg_odr_observers');
                    $sms_templates = unserialize($sms_templates);

                    foreach ($sms_templates as $sms_template) {
                        if ($sms_template['key'] == $this->config->get($payment_code.'_failed_order_status_id')) {
                            $smshare_message = $sms_template['value'];
                            break;
                        }
                    }
                } else {
                    $sms_template = $this->config->get('smshare_config_sms_template_for_customer_notif_on_checkout');
                    $smshare_order_total = '';

                    if ($order_total_query) {
                        $smshare_order_total = $smshareCommons->doGetOrderTotal($order_total_query->rows,$this->config);
                    }
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
                    $smshare_message = $smshareCommons->replace_smshare_variables($sms_template, $order_info, $smshare_order_total);
                }
  
                $sms_to = SmsharePhonenumberFilter::rewritePhoneNumber($order_info['telephone'], $this->config);
                $smshareCommons->sendSMS($sms_to, $smshare_message, $this->config);
            }
        }
        
    }

    /**
     * 
     */
    public function notify_or_not_customer_or_admins_on_registration($data){
        
        $smshareCommons     = new SmshareCommons();
        $smshare_patterns   = $this->config->get('smshare_config_number_filtering');
        $number_size_filter = $this->config->get('smshare_cfg_num_filt_by_size');
        
        $find = array(
            '{firstname}'  ,
            '{lastname}'   ,
            '{telephone}',
        	'{password}'   ,
        );
         
        $replace = array(
            'firstname'   => $data['firstname'],
            'lastname'    => $data['lastname'] ,
            'telephone' => $data['telephone'],
            'password'    => $data['password'] ,
        );
            
        if($this->config->get('smshare_config_notify_customer_by_sms_on_registration')            &&
           SmsharePhonenumberFilter::isNumberAuthorized($data['telephone'], $smshare_patterns)    &&
           SmsharePhonenumberFilter::passTheNumberSizeFilter($data['telephone'], $number_size_filter)
        ) {
        
            $sms_template = $this->config->get('smshare_config_sms_template_for_customer_notif_on_registration');
        
            $smshare_message = str_replace($find, $replace, $sms_template);
        
            $sms_to = SmsharePhonenumberFilter::rewritePhoneNumber($data['telephone'], $this->config);
            $smshareCommons->sendSMS($sms_to, $smshare_message, $this->config);
        }
        
        /*
         * Send sms to store owner on registration
         */
        if ($this->config->get('config_error_log')) $this->log->write("[smshare] Check if store owner wants SMS on registration");
        
        if ( $this->config->get('smshare_cfg_ntfy_admin_by_sms_on_reg') ) {
        	
        	if ($this->config->get('config_error_log')) $this->log->write("[smshare] Store owner wants sms on registration");
        	
	        $sms_template = $this->config->get('smshare_cfg_sms_tmpl_for_admin_notif_on_reg');
	        
	        $smshare_message = str_replace($find, $replace, $sms_template);
	        
	        if ($this->config->get('config_error_log')) $this->log->write("[smshare] Store owner phone: " . $this->config->get('config_telephone'));
	        $smshareCommons->sendSMS($this->config->get('config_telephone'), $smshare_message, $this->config);
        }
        
    }

    /**
     *
     */
    public function send_conf_sms_to_customer_on_new_order($data){
        $smshareCommons     = new SmshareCommons();

        $find = array(
            '{firstname}' ,
            '{lastname}' ,
            '{phonenumber}' ,
            '{confirm_code}' ,
        );

        $replace = array(
            'firstname'    => $data['firstname'],
            'lastname'     => $data['lastname'] ,
            'phonenumber'  => $data['telephone'],
            'confirm_code' => $data['confirm_code']
        );

        if ($this->config->get('smshare_config_sms_confirm')) {
            $sms_template = $this->config->get('smshare_config_sms_template');

            $smshare_message = str_replace($find, $replace, $sms_template);

            if (!$smshare_message) {
                $smshare_message = $data['confirm_code'];
            }

            $sms_to = SmsharePhonenumberFilter::rewritePhoneNumber($data['telephone'], $this->config);
            $smshareCommons->sendSMS($sms_to, $smshare_message, $this->config);
        }
    }

    /**
     * Send activation sms.
     *
     * @param array $data
     * @param string $smsToken
     *
     * @return void
     */
    public function sendActivationSms($data, $smsToken)
    {
        $smshare_patterns   = $this->config->get('smshare_config_number_filtering');
        $number_size_filter = $this->config->get('smshare_cfg_num_filt_by_size');

        $message = $this->config->get('smshare_config_sms_activation_message_template');

        $message = str_replace('{activationToken}', $smsToken, $message);

        if(
            SmsharePhonenumberFilter::isNumberAuthorized($data['telephone'], $smshare_patterns)    &&
            SmsharePhonenumberFilter::passTheNumberSizeFilter($data['telephone'], $number_size_filter)
        ) {
            $smshareCommons = new SmshareCommons();
            $sms_to = SmsharePhonenumberFilter::rewritePhoneNumber($data['telephone'], $this->config);
            $smshareCommons->sendSMS($sms_to, $message, $this->config);
        }
    }

    /**
     * Send slot reservation info sms.
     *
     * @param array $phone
     * @param string $message
     *
     * @return void
     */
    public function sendSlotReservation($phone, $message)
    {
        $smshare_patterns   = $this->config->get('smshare_config_number_filtering');
        $number_size_filter = $this->config->get('smshare_cfg_num_filt_by_size');

        if(
            SmsharePhonenumberFilter::isNumberAuthorized($phone, $smshare_patterns)    &&
            SmsharePhonenumberFilter::passTheNumberSizeFilter($phone, $number_size_filter)
        ) {
            $smshareCommons = new SmshareCommons();
            $sms_to = SmsharePhonenumberFilter::rewritePhoneNumber($phone, $this->config);
            $smshareCommons->sendSMS($sms_to, $message, $this->config);
        }
    }
}
