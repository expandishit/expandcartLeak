<?php
class ControllerModulePopupWindow extends Controller {
    // Module Unifier
    private $moduleName = 'PopupWindow';
    private $moduleNameSmall = 'popupwindow';
    private $moduleData_module = 'popupwindow_module';
    private $moduleModel = 'model_module_popupwindow';
    private $version = '1.3.1';
    // Module Unifier

    public function index() {
        $this->load->model('marketplace/common');
        $market_appservice_status = $this->model_marketplace_common->hasApp('popupwindow');
        if (!$market_appservice_status['hasapp']) {
            $this->redirect($this->url->link('marketplace/app', 'id=' . $market_appservice_status['appserviceid'], 'SSL'));
            return;
        }

        // Module Unifier
        $this->data['moduleName'] = $this->moduleName;
        $this->data['moduleNameSmall'] = $this->moduleNameSmall;
        $this->data['moduleData_module'] = $this->moduleData_module;
        $this->data['moduleModel'] = $this->moduleModel;
        // Module Unifier
     
        $this->load->language('module/'.$this->moduleNameSmall);
        $this->loadLanguage();

        $this->load->model('module/'.$this->moduleNameSmall);
        $this->load->model('setting/store');
        $this->load->model('localisation/language');
        $this->load->model('design/layout');

        $this->load->model('sale/customer_group');
        $this->data['customerGroups'] = $this->model_sale_customer_group->getCustomerGroups();
        
        $catalogURL = $this->getCatalogURL();

        $this->document->addStyle('view/stylesheet/'.$this->moduleNameSmall.'/'.$this->moduleNameSmall.'.css');
        $this->document->addScript('view/javascript/'.$this->moduleNameSmall.'/timepicker.js');
        $this->document->addScript('view/javascript/ckeditor/ckeditor.js');

        $this->document->setTitle($this->language->get('heading_title'));

        if(!isset($this->request->get['store_id'])) {
           $this->request->get['store_id'] = 0; 
        }
    
        $store = $this->getCurrentStore($this->request->get['store_id']);
        //////////////////////////// Post
        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {     
            if (!$this->user->hasPermission('modify', 'module/'.$this->moduleNameSmall)) {
                $result_json['success'] = '0';
                $result_json['error'] = $this->error;

                $this->response->setOutput(json_encode($result_json));
                return;
            }

            if(!isset($this->request->post[$this->moduleData_module])) {
                $this->request->post[$this->moduleData_module] = array();
            }

            $data = $this->{$this->moduleModel}->getSetting($this->moduleNameSmall, $store['store_id']);
            $oldArrayIDs = array();
            $newArrayIDs = array();

            if(isset($this->request->post['ids'])) {
                foreach ($this->request->post['ids'] as $popup) {
                    array_push($newArrayIDs, $popup);
                }
            }

            if(isset($data['PopupWindow']['PopupWindow'])) {
                foreach ($data['PopupWindow']['PopupWindow'] as $popup) {
                    array_push($oldArrayIDs, $popup['id']);
                }
            }

            $deletedIDs = array_diff($oldArrayIDs, $newArrayIDs);

            if($deletedIDs) {
                foreach($deletedIDs as $id) {
                    $this->model_module_popupwindow->deletePopupID($id);
                }
            }

            $layouts = $this->model_design_layout->getLayouts();
            for($i=0;$i<count($layouts);$i++) {
                $this->request->post[$this->moduleData_module][] = $layouts[$i];
                $this->request->post[$this->moduleData_module][$i]['position'] = 'content_bottom';
                $this->request->post[$this->moduleData_module][$i]['status'] = '1';
                $this->request->post[$this->moduleData_module][$i]['sort_order'] = '0';
            }

            //$this->model_module_popupwindow->addSetting($this->moduleNameSmall, $this->request->post[$this->moduleData_module], $this->request->post['store_id']);
            $this->model_module_popupwindow->addSetting($this->moduleNameSmall, $this->moduleName, $this->request->post, $this->request->post['store_id']);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success');
            $result_json['success'] = '1';

            if ( $this->request->post['to_be_redirected'] )
            {
                $result_json['redirect'] = '1';
                $result_json['to'] = (string) $this->url->link('module/popupwindow/update', 'popup_id=' . $this->request->post['to_be_edited'], 'SSL');
            }

            $this->response->setOutput(json_encode($result_json));
            return;
        }
        /////////////////////////////// end post

        if (isset($this->error['code'])) {
            $this->data['error_code'] = $this->error['code'];
        } else {
            $this->data['error_code'] = '';
        }

        $this->data['breadcrumbs']   = array();
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
        );
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_module'),
            'href' => $this->url->link('marketplace/home', '', 'SSL'),
        );
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('module/'.$this->moduleNameSmall, '', 'SSL'),
        );

        $this->loadLanguage();

        $this->data['heading_title'] = $this->language->get('heading_title');
        $this->data['stores'] = array_merge(array(0 => array('store_id' => '0', 'name' => $this->config->get('config_name') . ' (' . $this->data['text_default'].')', 'url' => HTTP_SERVER, 'ssl' => HTTPS_SERVER)), $this->model_setting_store->getStores());
        $this->data['error_warning']          = '';  
        $this->data['languages']              = $this->model_localisation_language->getLanguages();
        $this->data['store']                  = $store;
        $this->data['store_id']               = $store['store_id'];
        $this->data['token']                  = null;
        $this->data['action']                 = $this->url->link('module/'.$this->moduleNameSmall, '', 'SSL');
        $this->data['cancel']                 = $this->url.'marketplace/home';
        $this->data['data']                   = $this->{$this->moduleModel}->getSetting($this->moduleNameSmall, $store['store_id']);
        $this->data['impressions']            = $this->model_module_popupwindow->getImpressions();
        $this->data['modules']              = $this->{$this->moduleModel}->getSetting($this->moduleData_module, $store['store_id']);
        $this->data['layouts']                = $this->model_design_layout->getLayouts();
        $this->data['catalog_url']          = $catalogURL;
        
        if (isset($this->data['data'][$this->moduleName])) {
        // Module Unifier
        $this->data['moduleData'] = $this->data['data'][$this->moduleName];
        // Module Unifier
        }
        
        $this->template = 'module/'.$this->moduleNameSmall.'.expand';
        $this->children = array('common/header', 'common/footer');
        $this->response->setOutput($this->render());
    }
    
    public function update() {

        $popup_id = (int)$this->request->get['popup_id'];
        if(!preg_match("/^[1-9][0-9]*$/", $popup_id)) return false;

        $this->load->language('module/popupwindow');

        //Set the title from the language file $_['heading_title'] string
        $this->document->setTitle($this->language->get('heading_title'));
        
        //Load the settings model. You can also add any other models you want to load here.
        $this->load->model('setting/setting');
        $this->load->model('module/popupwindow');
        $this->load->model('localisation/language');
        $allLangs = $this->model_localisation_language->getLanguages();
        $this->data['languages'] = $allLangs;

        $this->load->model('sale/customer_group');
        $allGroups =  $this->model_sale_customer_group->getCustomerGroups();
        $this->data['custGroups'] = $allGroups;

        //Save the settings if the user has submitted the admin form (ie if someone has pressed save).
        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            

                $data = $this->request->post;

                $contentData = array();
                foreach ($allLangs as $lang) {
                    $contentData[$lang['language_id']] = $data['content_'.$lang['language_id']];
                }

                $groupsData = $data['customer_groups'];

                $newData = array(
                    'id' => $this->request->get['popup_id'],
                    'Enabled' => $data['Enabled'] ? 'yes' : 'no',
                    'method' => $data['method'],
                    'event' => $data['event'],
                    'url' => $data['url'],
                    'excluded_urls' => $data['excluded_urls'],
                    'css_selector' => $data['css_selector'],
                    'time_interval' => $data['time_interval'],
                    'start_time' => $data['start_time'],
                    'end_time' => $data['end_time'],
                    'scroll_percentage' => $data['scroll_percentage'],
                    'repeat' => $data['repeat'],
                    'days' => $data['days'],
                    'date_interval' => $data['date_interval'],
                    'start_date' => $data['start_date'],
                    'end_date' => $data['end_date'],
                    'seconds' => $data['seconds'],
                    'prevent_closing' => $data['prevent_closing'],
                    'content' => $contentData,
                    'width' => $data['width'],
                    'height' => $data['height'],
                    'auto_resize' => $data['auto_resize'] ? 'true' : 'false',
                    'aspect_ratio' => $data['aspect_ratio'] ? 'true' : 'false',
                    'animation' => $data['animation'],
                    'customerGroups' => $groupsData
                );
                $this->model_module_popupwindow->editSetting($this->moduleNameSmall, $this->moduleName, $newData, $this->request->get['popup_id'], $store['store_id']);

                $this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success');
                $result_json['success'] = '1';

               /* $result_json['success'] = '0';
                $result_json['error'] = $this->error;*/
            
            $this->response->setOutput(json_encode($result_json));
            return;
        }
        
        $this->getForm();
                
    }


    private function getForm() {

        $this->loadLanguage();
        
        $this->data['token'] = null;
        
        if(isset($this->request->get['popup_id'])) {
            $result = $this->model_module_popupwindow->getPopupWindow($this->request->get['popup_id'], $this->moduleName, $store_id);
            $this->data['edit'] = '';
            $this->data['popup_id'] = $this->request->get['popup_id'];
        }
        else {
            $result = NULL;
            $this->data['edit'] = 'checked';
        }

        //print_r($result);
        //exit;
        
        $config_data = array(
                'id',
                'Enabled',
                'method',
                'event',
                'url',
                'excluded_urls',
                'css_selector',
                'time_interval',
                'start_time',
                'end_time',
                'scroll_percentage',
                'repeat',
                'days',
                'date_interval',
                'start_date',
                'end_date',
                'seconds',
                'prevent_closing',
                'content',
                'width',
                'height',
                'auto_resize',
                'aspect_ratio',
                'animation',
                'customerGroups'
        );
        
        foreach ($config_data as $conf) {
            if (isset($this->request->post[$conf])) {
                $this->data[$conf] = $this->request->post[$conf];
            } 
            else {
                $this->data[$conf] = ($result == NULL) ? '' : $result[$conf];
            }
            
        }
        
        $this->data['heading_title'] = $this->language->get('heading_title');
        //This creates an error message. The error['warning'] variable is set by the call to function validate() in this controller (below)
        if (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else {
            $this->data['error_warning'] = '';
        }
        
        //SET UP BREADCRUMB TRAIL. YOU WILL NOT NEED TO MODIFY THIS UNLESS YOU CHANGE YOUR MODULE NAME.
        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_module'),
            'href'      => $this->url->link('marketplace/home', '', 'SSL'),
            'separator' => ' :: '
        );
        
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('module/'.$this->moduleNameSmall, '', 'SSL'),
        );
        
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('popup_title').' '.$this->request->get['popup_id'] ,
            'href'      => '#',
            'separator' => ' :: '
        );
        
        
        if (!isset($this->request->get['popup_id'])) {
            $this->data['action'] = $this->url->link('module/popupwindow/insert', '', 'SSL');
        } else {
            $this->data['action'] = $this->url->link('module/popupwindow/update', 'popup_id=' . $this->request->get['popup_id'], 'SSL');
        }
        
            
        //$this->data['cancel'] = $this->url->link('module/store_locations', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['cancel'] = $this->url.'module/popupwindow?'.'';
        
        $this->load->model('design/layout');
        
        $this->data['layouts'] = $this->model_design_layout->getLayouts();

        //Choose which template file will be used to display this request.
        $this->template = 'module/popupwindow_form.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        //Send the output.
        $this->response->setOutput($this->render());
        
    }


        private function loadLanguage() {
        
        // language strings
        $languageVariables = array(
            'heading_title',
            'error_permission',
            'text_success',
            'text_enabled',
            'text_disabled',
            'button_cancel',
            'save_changes',
            'text_default',
            'text_module',
            'entry_code',
            'entry_code_help',
            'text_show_on',
            'text_window_load',
            'text_page_load',
            'text_body_click',
            'text_url',
            'entry_content',
            'entry_size',
            'entry_action_options',
            'entry_popup_options',
            'button_add_module',
            'button_remove',
            'popup_title',
            'popup_width',
            'popup_height',
            'popup_enabled',
            'column_action',
            'add_popup',
            'text_no_results',
            'entry_repeat',
            'text_addnewpopup',
            'text_popup',
            'text_conf_remove',
            'text_settings',
            'text_appearance',
            'text_status',
            'text_enable_option',
            'entry_impressions',
            'entry_popup_method',
            'text_method',
            'text_onhomepage',
            'text_allpages',
            'text_specificurls',
            'text_css',
            'entry_urls',
            'text_urls_wish_popup',
            'entry_excluded_urls',
            'text_urls_wish_exclude',
            'entry_choose_css',
            'text_set_csss',
            'entry_showon',
            'text_set_event',
            'text_windowloadevent',
            'text_pageloadevent',
            'text_bodyclickevent',
            'text_exitintent',
            'text_scrollpercentageevent',
            'text_scrollpercentage',
            'text_trigger_popup__scrolled',
            'text_set_frequency',
            'text_showalways',
            'text_once_ession',
            'text_show_xdays',
            'text_days',
            'entry_hoursinterval',
            'text_hours_popup_show',
            'entry_starttime',
            'entry_endtime',
            'entry_dateinterval',
            'text_setdateinterval',
            'entry_startdate',
            'entry_enddate',
            'entry_delay',
            'text_show_xseconds',
            'text_secs',
            'entry_preventclosing',
            'text_disable_closing',
            'text_customergroups',
            'text_choose_group',
            'text_guest',
            'entry_multi_lingual',
            'entry_popupcontent',
            'entry_width',
            'text_define_width',
            'text_width',
            'text_px',
            'entry_height',
            'text_define_height',
            'text_height',
            'entry_fancyboxautoresize',
            'text_enable_autoresize',
            'entry_fancyboxaspectratio',
            'text_enable_aspect',
            'entry_animation',
            'text_choose_animation',
        );
       
        foreach ($languageVariables as $languageVariable) {
            $this->data[$languageVariable] = $this->language->get($languageVariable);
        }
        
        $this->data['entry_status'] = $this->language->get('entry_status');
        
        //END LANGUAGE
        
    }

    public function get_popupwindow_settings() {
        $this->load->model('module/'.$this->moduleNameSmall);
        $this->load->model('setting/store');
        $this->load->model('localisation/language');
        $this->load->model('sale/customer_group');
        $this->load->language('module/'.$this->moduleNameSmall);
           
        $this->data['currency']            = $this->config->get('config_currency');
        $this->data['languages']              = $this->model_localisation_language->getLanguages();
        $this->data['popup']['id']          = $this->request->get['popup_id'];
        $store_id                            = $this->request->get['store_id'];
        $this->data['data']                   = $this->{$this->moduleModel}->getSetting($this->moduleNameSmall, $store_id);
        $this->data['moduleName']            = $this->moduleName;
        $this->data['moduleData']            = (isset($this->data['data'][$this->moduleName])) ? $this->data['data'][$this->moduleName] : array();
        $this->data['newAddition']          = true;
        $this->data['customerGroups'] = $this->model_sale_customer_group->getCustomerGroups();
        
        $this->template = 'module/'.$this->moduleNameSmall.'/tab_popuptab.tpl';
        $this->response->setOutput($this->render());
    }

    private function getCatalogURL() {
        if (isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1'))) {
            $storeURL = HTTPS_CATALOG;
        } else {
            $storeURL = HTTP_CATALOG;
        } 
        return $storeURL;
    }

    private function getServerURL() {
        if (isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1'))) {
            $storeURL = HTTPS_SERVER;
        } else {
            $storeURL = HTTP_SERVER;
        } 
        return $storeURL;
    }

    private function getCurrentStore($store_id) {    
        if($store_id && $store_id != 0) {
            $store = $this->model_setting_store->getStore($store_id);
        } else {
            $store['store_id'] = 0;
            $store['name'] = $this->config->get('config_name');
            $store['url'] = $this->getCatalogURL(); 
        }
        return $store;
    }
    
    public function install() {
        $this->load->model('module/'.$this->moduleNameSmall);
        $this->{$this->moduleModel}->install();
    }
    
    public function uninstall() {
        $this->load->model('setting/setting');
        
        $this->load->model('setting/store');
        $this->model_setting_setting->deleteSetting($this->moduleData_module,0);
        $stores=$this->model_setting_store->getStores();
        foreach ($stores as $store) {
            $this->model_setting_setting->deleteSetting($this->moduleData_module, $store['store_id']);
        }
        
        $this->load->model('module/'.$this->moduleNameSmall);
        $this->{$this->moduleModel}->uninstall();
    }
}

?>