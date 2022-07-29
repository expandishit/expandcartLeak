<?php
class ControllerModuleSmshare extends Controller {
    private $error = array(); 
    
    /**
     * 
     */
    public function install() {
        
        $defaultSettings = array(
            "smshare_cfg_log" => false,
        );
        
        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting('smshare', $defaultSettings);
    }
    
    /**
     * 
     */
    public function index() {
        $this->load->model('marketplace/common');
        $market_appservice_status = $this->model_marketplace_common->hasApp('smshare');
        if (!$market_appservice_status['hasapp']) {
            $this->redirect($this->url->link('marketplace/app', 'id=' . $market_appservice_status['appserviceid'], 'SSL'));
            return;
        }

        
        //Seems to be available in a "default" language object. See controller/common/header.php @0ce36
        $this->data['direction'] = $this->language->get('direction');
        
        //Load the language file for this module
        $this->load->language('module/smshare');

        $this->document->setTitle($this->language->get('heading_title'));
        
        $this->load->model('setting/setting');
        
        if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {

            //Data cleaning.

            if ( ! $this->validate() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;

                $this->response->setOutput(json_encode($result_json));

                return;
            }

            if ( isset( $_POST['smshare_api_kv'] ) )
            {

                foreach ( $_POST['smshare_api_kv'] as $index => $api_kv )
                {
                    if ( $api_kv['key'] == "" )
                    {
                        unset( $_POST['smshare_api_kv'][$index] );
                    }
                    else if ( $api_kv['val'] == "" )
                    {
                        $_POST['smshare_api_kv'][$index]['val']=null;
                    }
                }
                $this->request->post["smshare_api_kv"] = $_POST['smshare_api_kv'];
            }

            /*
             * Clean observers with status==0
             * 
             * No need. we do it JS side.
             */
            if ( isset( $_POST['smshare_cfg_odr_observers'] ) )
            {
                foreach ( $_POST['smshare_cfg_odr_observers'] as $index => $api_kv )
                {
                    if( $api_kv['key'] == "0" )
                    {
                        unset( $_POST['smshare_cfg_odr_observers'][$index] );
                    }
                }

                $this->request->post["smshare_cfg_odr_observers"] = $_POST['smshare_cfg_odr_observers'];
            }

            if ( isset( $_POST['smshare_cfg_odr_observers_for_admin'] ) )
            {
                foreach ( $_POST['smshare_cfg_odr_observers_for_admin'] as $index => $api_kv )
                {
                    if( $api_kv['key'] == "0" )
                    {
                        unset( $_POST['smshare_cfg_odr_observers_for_admin'][$index] );
                    }
                }

                $this->request->post["smshare_cfg_odr_observers_for_admin"] = $_POST['smshare_cfg_odr_observers_for_admin'];
            }
            
            if ( isset( $_POST['smshare_cfg_seller_observers'] ) )
            {
                foreach ( $_POST['smshare_cfg_seller_observers'] as $index => $api_kv )
                {
                    if( $api_kv['key'] == "0" )
                    {
                        unset( $_POST['smshare_cfg_seller_observers'][$index] );
                    }
                }

                $this->request->post["smshare_cfg_seller_observers"] = $_POST['smshare_cfg_seller_observers'];
            }

            $this->model_setting_setting->editSetting('smshare', $this->request->post);
            
            $result_json['success'] = '1';

            $result_json['success_msg'] = $this->language->get('text_success');

            $this->response->setOutput( json_encode($result_json) );

            return;
        }

        /*
          ____                     _                           _
         |  _ \                   | |                         | |
         | |_) |_ __ ___  __ _  __| | ___ _ __ _   _ _ __ ___ | |__  ___
         |  _ <| '__/ _ \/ _` |/ _` |/ __| '__| | | | '_ ` _ \| '_ \/ __|
         | |_) | | |  __/ (_| | (_| | (__| |  | |_| | | | | | | |_) \__ \
         |____/|_|  \___|\__,_|\__,_|\___|_|   \__,_|_| |_| |_|_.__/|___/
          
         */

        $this->data['breadcrumbs'] = array();

           $this->data['breadcrumbs'][] = array(
                   
               'href'      => $this->url->link('common/home', '', 'SSL'),
               'text'      => $this->language->get('text_home'),
               'separator' => FALSE
           );

           $this->data['breadcrumbs'][] = array(
               'href'      => $this->url->link('marketplace/home', '', 'SSL'),
               'text'      => $this->language->get('text_module'),
               'separator' => ' :: '
           );
        
           $this->data['breadcrumbs'][] = array(
               'href'      => $this->url->link('module/smshare', '', 'SSL'),
               'text'      => $this->language->get('heading_title'),
               'separator' => ' :: '
           );
        
        $this->data['action'] = $this->url->link('module/smshare', '', 'SSL');
        
        $this->data['cancel'] = $this->url->link('marketplace/home', '', 'SSL');

        /*
         * order statuses
         */
        $this->load->model('localisation/order_status');
        $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
        
        // init seller statuses list
        $msSeller = new ReflectionClass('MsSeller');
        $this->data['seller_statuses'] = [];
        foreach ($msSeller->getConstants() as $constant => $value) {
            if (strpos($constant, 'STATUS_') !== FALSE) {
                $this->data['seller_statuses'][] = $value;
            }
        }

        //The following code pulls in the required data from either config files or user
        //submitted data (when the user presses save in admin). Add any extra config data
        // you want to store.
        //
        // NOTE: These must have the same names as the form data in your smshare.tpl file
        $config_data = array(
            "smshare_api_url",
            "smshare_api_dest_var",
            "smshare_api_msg_var",
            "smshare_api_msg_to_unicode",
            "smshare_api_http_method",
            "smshare_api_type",
            "smshare_api_kv",

            'smshare_config_notify_customer_by_sms_on_registration',
            'smshare_config_notify_seller_on_status_change',
            'smshare_config_notify_customer_by_sms_on_checkout',
            'smshare_cfg_donotsend_sms_on_checkout_coupon_keywords',
            'smshare_cfg_ntfy_admin_by_sms_on_reg'          ,
            'smshare_config_notify_admin_by_sms_on_checkout',
            'smshare_config_notify_extra_by_sms_on_checkout',
            /* hook_8dd90 optin */
            'smshare_cfg_odr_observers'                     ,
            'smshare_cfg_odr_observers_for_admin'           ,
            'smshare_cfg_seller_observers'           ,
            
            'smshare_config_sms_template_for_customer_notif_on_registration',
            'smshare_config_sms_activation_message_template',
            'smshare_config_sms_template_for_customer_notif_on_checkout',
            'smshare_cfg_sms_tmpl_for_admin_notif_on_reg',
            'smshare_config_sms_template_for_storeowner_notif_on_checkout',
            
            'smshare_cfg_number_rewriting_search',
            'smshare_cfg_number_rewriting_replace',

            'smshare_config_number_filtering',
            'smshare_cfg_num_filt_by_size',
            'smshare_cfg_log',

            'smshare_config_sms_confirm',
            'smshare_config_sms_confirm_per_order',
            'smshare_config_sms_trials',
            'smshare_config_sms_template',
            
            'smshare_cfg_code_length',
            'smshare_cfg_code_type',
        );
        
        //★ Inject data from config into the data (to be displayed)
        foreach ($config_data as $conf) {
            if (isset($this->request->post[$conf])) {
                $this->data[$conf] = $this->request->post[$conf];
            } else {

                $this->data[$conf] = $this->config->get($conf);
                
                if(empty($this->data[$conf])){
                    
                    if($conf == 'smshare_config_sms_template_for_customer_notif_on_registration') {
                        $this->data[$conf] = "Thank you for your registration.";
                        
                    } if($conf == 'smshare_config_sms_activation_message_template') {
                        $this->data[$conf] = "Your activation code is : {activationToken}";

                    } else if($conf == 'smshare_config_sms_template_for_customer_notif_on_checkout'){
                        $this->data[$conf] = "Thank you for your order.";
                        
                    }else if($conf == 'smshare_config_sms_template_for_storeowner_notif_on_checkout'){
                        $this->data[$conf] = "{default_template}";
                        
                    }else if($conf === 'smshare_cfg_odr_observers'){
                        $this->data[$conf] = array();
                        
                    }else if($conf === 'smshare_cfg_odr_observers_for_admin'){
                        $this->data[$conf] = array();
                        
                    }/* hook_64d0a optin */
                    else if ($conf === 'smshare_cfg_seller_observers') {
                        $this->data[$conf] = array();
                    }
                }

            }
        }            
        
        /*
         * Add template to observers
         */
        $tmpl_arr = array("key" => "", "val" => "", "encode" => "", "is_tmpl" => true);
        
        array_push($this->data['smshare_cfg_odr_observers'],           $tmpl_arr);
        array_push($this->data['smshare_cfg_odr_observers_for_admin'], $tmpl_arr);
        array_push($this->data['smshare_cfg_seller_observers'], $tmpl_arr);
        
        
        
        //Choose which template file will be used to display this request, and send the output.
        $this->template = 'module/smshare.expand';
        $this->children = array(
            'common/header',    
            'common/footer'    
        );
        
        $this->response->setOutput($this->render(TRUE));
    }
    
    /**
     * This function is called to ensure that the settings chosen by the admin user are allowed/valid.
     * You can add checks in here of your own.
     */
    private function validate()
    {
        if ( ! $this->user->hasPermission('modify', 'module/smshare') )
        {
           $this->error['warning'] = $this->language->get('error_permission');
        }

        return $this->error ? false : true;
    }

    public function deleteField()
    {
        $this->load->model('setting/setting');
        $config = $this->config->get($this->request->post['targetField']);
        unset($config[$this->request->post['targetIndex']]);
        $this->model_setting_setting->insertUpdateSetting('smshare', [$this->request->post['targetField'] => $config]);
        return;
    }
}
?>
