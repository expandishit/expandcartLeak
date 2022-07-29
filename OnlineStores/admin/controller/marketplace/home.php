<?php

class ControllerMarketplaceHome extends Controller
{

    public function index()
    {
        if (isset($this->session->data['cant_uninstall']) && $this->session->data['cant_uninstall'] == true) {
            $this->data['cant_uninstall'] = true;
            unset( $this->session->data['cant_uninstall'] );
        }

        $this->language->load('marketplace/home');

        $this->document->setTitle($this->language->get('text_availableapps'));

        $this->data['isTrial'] = PRODUCTID == 3 ? '1' : '0';
        $this->data['packageslink'] = $this->url->link('billingaccount/plans', '', 'SSL');

        $this->load->model('marketplace/common');

        $this->initializer([
            'marketplace/appservice',
            'setting/extension',
        ]);

        #required for paid apps/services
        $this->load->model('billingaccount/common');

        $billingAccess = null;
        if ($this->user->hasPermission('access', 'billingaccount/plans') && ENABLE_BILLING == '1') {
            $billingAccess = '1';
        }

        $filterData = [];

//        $page = 1;
//        $offset = 0;
//        $perPage = 9999;
//        if (isset($this->request->get['page']) && $this->request->get['page'] > 1) {
//            $page = $this->request->get['page'];
//
//            $offset = ($page - 1) * $perPage;
//        }

        $this->data['remove_recommended_services'] = false;
        if (isset($this->request->get['application']) && $this->request->get['application'] == 1) {
            $this->data['application'] = $filterData['application'] = $this->request->get['application'];
            $this->data['remove_recommended_services'] = true;
        }

        if (isset($this->request->get['service']) && $this->request->get['service'] == 1) {
            $this->data['service'] = $filterData['service'] = $this->request->get['service'];
            $this->data['remove_recommended_services'] = true;
        }

        if (isset($this->request->get['paid']) && $this->request->get['paid'] == 1) {
            $this->data['paid'] = $filterData['paid'] = $this->request->get['paid'];
            $this->data['remove_recommended_services'] = true;
        }

        if (isset($this->request->get['free']) && $this->request->get['free'] == 1) {
            $this->data['free'] = $filterData['free'] = $this->request->get['free'];
            $this->data['remove_recommended_services'] = true;
        }

        if (isset($this->request->get['installed']) && $this->request->get['installed'] == 1) {
            $this->data['installed'] = $filterData['installed'] = $this->request->get['installed'];
            $this->data['remove_recommended_services'] = true;
        }

        if (isset($this->request->get['purchased']) && $this->request->get['purchased'] == 1) {
            $this->data['purchased'] = $filterData['purchased'] = $this->request->get['purchased'];
            $this->data['remove_recommended_services'] = true;
        }

        if (isset($this->request->get['isbundle']) && $this->request->get['isbundle'] == 1) {
            $this->data['isbundle'] = $filterData['isbundle'] = $this->request->get['isbundle'];
            $this->data['remove_recommended_services'] = true;
        }

        if (isset($this->request->get['isnew']) && $this->request->get['isnew'] == 1) {
            $this->data['isnew'] = $filterData['isnew'] = $this->request->get['isnew'];
            $this->data['remove_recommended_services'] = true;
        }

        if (isset($this->request->get['lookup']) && mb_strlen($this->request->get['lookup']) >= 1) {
            $this->data['lookup'] = $filterData['lookup'] = $this->request->get['lookup'];
            $this->data['remove_recommended_services'] = true;
        }

        if (isset($this->request->get['category']) && count($this->request->get['category']) > 0) {
            $this->data['selectedCategories'] = $filterData['categories'] = $this->request->get['category'];
            $this->data['remove_recommended_services'] = true;
        }

        $this->data['billingAccess'] = $billingAccess;

        //$this->load->model('setting/extension');
        $installedextensions = $this->model_setting_extension->getInstalled('module');

        $this->data['counts'] = $this->appservice->getCounts();
        $this->data['categories'] = $this->appservice->resolveCategories($this->appservice->getCategories());

        $activeTrials = array_column($this->extension->getActiveTrials(), null, 'extension_code');

        $recommended_results = $this->appservice->getRecommendedAppService();

        $recommended_apps_and_services = $this->renderAppsServicesResults($recommended_results,$activeTrials,$installedextensions,$billingAccess);

        $this->data['recommended_apps_and_services'] = $recommended_apps_and_services;

        $results = $this->appservice->getAppService($filterData);
        //echo '<pre>';print_r($results);exit;
        $avaliableModules = $this->renderAppsServicesResults($results,$activeTrials,$installedextensions,$billingAccess);



        //echo '<pre>';print_r($params);exit;
        $avaliableModulesCount = count($avaliableModules);

        //$this->data['avaliableModules'] = array_slice($avaliableModules, $offset, $perPage);

        $this->data['avaliableModules'] = $avaliableModules;

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('marketplace/home', '', 'SSL'),
            'separator' => false
        );

        $get = $this->request->get;
//        unset($get['page']);
        unset($get['ajaxish']);

//        if ( $avaliableModulesCount > $perPage )
//        {
//            $pagination = new Pagination();
//            $pagination->total = $avaliableModulesCount;
//            $pagination->page = $page;
//            $pagination->limit = $perPage;
//            $pagination->text = $this->language->get('text_pagination');
//            $pagination->url = $this->url->link('marketplace/home', http_build_query($get) . '&page={page}', 'SSL');
//
//            $this->data['pagination'] = $pagination->render();
//        }

        if ( isset( $this->request->get['ajaxish'] ) )
        {
            $this->template = 'marketplace/app_services_snippet.expand';
        }
        else
        {
            $this->template = 'marketplace/home.expand';
            $this->children = array(
                'common/header',
                'common/footer'
            );
        }

        $this->response->setOutput($this->render_ecwig());

        return;
    }

    public function install()
    {
        $this->language->load('extension/module');

        if (!$this->user->hasPermission('modify', 'marketplace/home')) {
            $this->session->data['error'] = $this->language->get('error_permission');

            $this->redirect($this->url->link('marketplace/home', 'type=' . $this->request->get['type'], 'SSL'));
        } else {
            $this->load->model('setting/extension');

            $this->model_setting_extension->install('module', $this->request->get['extension']);

            if ($this->model_setting_extension->isTrial($this->request->get['extension'])) {
                $this->model_setting_extension->removeTrial($this->request->get['extension']);
            }
            $this->load->model('user/user_group');
            $this->model_user_user_group->addPermission(
                $this->user->getId(),
                'access',
                'module/' . $this->request->get['extension']
            );
            $this->model_user_user_group->addPermission(
                $this->user->getId(),
                'modify',
                'module/' . $this->request->get['extension']
            );

            $class = $this->resolveExtensionClass($this->request->get['extension']);

            /***************** Start ExpandCartTracking #347721  ****************/

            // send mixpanel install app event
            $this->load->model('setting/mixpanel');
            $this->model_setting_mixpanel->trackEvent('Install App', ['App Code' => $this->request->get['extension']]);

            // send amplitude install app event
            $this->load->model('setting/amplitude');
            $this->model_setting_amplitude->trackEvent('Install App', ['App Code' => $this->request->get['extension']]);

            /***************** End ExpandCartTracking #347721  ****************/


            if (method_exists($class, 'install')) {
               $installInfo = $class->install();
            }
            unset($this->request->get['extension'], $this->request->get['ajaxish']);
            $data = array();
            $data['success'] = "true";
            // mega filter init process
            if(is_array($installInfo) && !empty($installInfo['mega_filter_init_install_url']))
                $data['init_install_url'] = $installInfo['mega_filter_init_install_url'];

            $this->response->setOutput(json_encode($data));

        }
    }

    public function trial()
    {
        $this->language->load('extension/module');
        if (!$this->user->hasPermission('modify', 'marketplace/home')) {
            $this->session->data['error'] = $this->language->get('error_permission');
            $this->redirect($this->url->link('marketplace/home', 'type=' . $this->request->get['type'], 'SSL'));
        } else {
            $this->load->model('setting/extension');
            $application = $this->model_setting_extension->getApplication($this->request->get['extension']);
            $applicationExtension = $this->model_setting_extension->getApplicationExtension($this->request->get['extension']);
            if (isset($applicationExtension['extension_id'])) {
                // handle errors
                return false;
            }
            $extensionId = $this->model_setting_extension->install('module', $this->request->get['extension']);
            $this->model_setting_extension->addTrial(
                $application['id'],
                $extensionId,
                $this->request->get['extension']
            );

            $this->load->model('user/user_group');

            $this->model_user_user_group->addPermission(
                $this->user->getId(),
                'access',
                'module/' . $this->request->get['extension']
            );
            $this->model_user_user_group->addPermission(
                $this->user->getId(),
                'modify',
                'module/' . $this->request->get['extension']
            );

            $class = $this->resolveExtensionClass($this->request->get['extension']);

            if (method_exists($class, 'install')) {
                $class->install();
            }

            $extension = $this->request->get['extension'];
            unset($this->request->get['extension'], $this->request->get['ajaxish']);

            $this->redirect(
                $this->url->link(
                    'module/'.$extension,'','SSL'
                )
            );
        }
    }

    public function uninstall()
    {
        $extension = $this->request->get['extension'];

        $ignoredExtensions = array();

        if ($extension == 'productsoptions_sku' && (\Extension::isInstalled('knawat_dropshipping') || \Extension::isInstalled('knawat'))) {
            $ignoredExtensions+= ['productsoptions_sku'];
        }

        if ($extension == "quickcheckout") {
            if (
                !defined('THREE_STEPS_CHECKOUT') ||
                (defined('THREE_STEPS_CHECKOUT') && (THREE_STEPS_CHECKOUT == 0 || (THREE_STEPS_CHECKOUT == 1 && !$this->identity->isStoreOnWhiteList())))
            ) $ignoredExtensions+= [$extension];
        }

        if ( in_array($extension, $ignoredExtensions) )
        {
            $this->session->data['cant_uninstall'] = !true;
        }
        else
        {
            $this->language->load('extension/module');

            if (!$this->user->hasPermission('modify', 'marketplace/home')) {
                $this->session->data['error'] = $this->language->get('error_permission');
            } else {
                $this->load->model('setting/extension');
                $this->load->model('setting/setting');

                if ($this->model_setting_extension->isTrial($extension)) {
                    $this->model_setting_extension->removeTrial($extension);

                    // work around for aliexpress dropshipping application
                    if ($extension == 'aliexpress_dropshipping') {
                        $this->model_setting_setting->deleteSetting('module_wk_dropship');
                        $this->model_setting_setting->deleteSetting('wk_dropship');
                    }
                }

                $this->model_setting_extension->uninstall('module', $extension);

               

                $class = $this->resolveExtensionClass($extension);

                /***************** Start ExpandCartTracking #347722  ****************/

                // send mixpanel uninstall app event
                $this->load->model('setting/mixpanel');
                $this->model_setting_mixpanel->trackEvent('Uninstall App', ['App Code' => $extension]);

                // send amplitude uninstall app event
                $this->load->model('setting/amplitude');
                $this->model_setting_amplitude->trackEvent('Uninstall App', ['App Code' => $extension]);

                /***************** End ExpandCartTracking #347722  ****************/


                if (method_exists($class, 'uninstall')) {
                    $class->uninstall();
                }

				 $this->model_setting_setting->deleteSetting($extension);
				 
            }
        }

            $data = array();
            $data['success'] = "true";
            $this->response->setOutput(json_encode($data));
    }

    private function resolveExtensionClass($extension_name)
    {
        require_once(DIR_APPLICATION . 'controller/module/' . $extension_name . '.php');
        $class = 'ControllerModule' . str_replace('_', '', $extension_name);
        $class = new $class($this->registry);

        return $class;
    }

    public function facebook(){

        if(\Extension::isInstalled('facebook_import')){
            $this->redirect($this->url->link('module/facebook_import','', 'SSL'));
        }
        $this->getAppData(61);

        $this->language->load('marketplace/facebook');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->template = 'marketplace/home/facebook.expand';

        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());

    }

    public function amazon(){

        $this->getAppData(52);

        $this->language->load('marketplace/amazon');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->template = 'marketplace/home/amazon.expand';

        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }

    protected function renderAppsServicesResults($results,$activeTrials,$installedextensions,$billingAccess){

        $avaliableModules = null;

        $timestamp = time(); # Get current timestamp

        $email = BILLING_DETAILS_EMAIL; # Clients Email Address to Login

        # Define WHMCS URL & AutoAuth Key
        $whmcsurl = MEMBERS_LINK;
        $autoauthkey = MEMBERS_AUTHKEY;
        $hash = sha1($email . $timestamp . $autoauthkey); # Generate Hash

        foreach ($results as $key => $result)
        {
            $apps_rules=$this->marketPlaceRules($result['CodeName']);
            if($apps_rules)
            {
                $storeCodeRule=$apps_rules['storeCode'];
                if($storeCodeRule)
                {
                    if($storeCodeRule['condition']=='is')
                    {
                    if(!in_array(STORECODE, $storeCodeRule['codes']))
                        continue;
                    }
                    elseif($storeCodeRule['condition']=='not')
                    {
                    if(in_array(STORECODE, $storeCodeRule['codes']))
                        continue;
                    }
                }
                
                $configRule = $apps_rules['config'];
                
                if ($configRule) {
                    
                    if ($configRule['condition'] == 'in' && !$this->config->has($configRule['key'])) {
                        continue;
                    }
                    
                    if ($configRule['condition'] == 'not-in' && $this->config->has($configRule['key'])) {
                        continue;
                    }
                }

                $planRule = $apps_rules['planId'];

                if ($planRule) {
                    if ($planRule['condition'] == 'in' && !in_array(PRODUCTID, $planRule['plans'])) {
                        continue;
                    }

                    if ($planRule['condition'] == 'not-in' && in_array(PRODUCTID, $planRule['plans'])) {
                        continue;
                    }
                }
            }
            $tmpbuylink = 'cart.php?a=add&pid=' . $result['whmcsappserviceid'];
            $extension = $result['extension'];

            $installed = in_array($extension, $installedextensions);

            $purchased = $result['storeappserviceid'] != -1;
            $isbundle = $result['packageappserviceid'] != -1;
            if (isset($activeTrials[$result['CodeName']]) && !$purchased && !$isbundle) {
                if (isset($activeTrials[$result['CodeName']]['deleted_at'])) {
                    $isTrial = 2;
                } else {
                    $isTrial = 1;
                }
            } else {
                $isTrial = 0;
            }

            if ($this->language->get('code') == 'ar') {
                $tmpbuylink = $tmpbuylink . '&language=Arabic';
            } else {
                $tmpbuylink = $tmpbuylink . '&language=English';
            }

            if(!empty($result['link']) && PRODUCTID >= $result['freeplan']) {
                $tmpbuylink = $tmpbuylink . '&promocode=' . $result['link'];
            }

            if ($billingAccess == "1")
                $buylink = $whmcsurl . "?email=$email&timestamp=$timestamp&hash=$hash&goto=" . urlencode($tmpbuylink);
            else
                $buylink = '#';

            $moduleData = array(
                'id' => $result['id'],
                'name' => $result['Name'],
                'desc' => $result['MiniDescription'],
                'installed' => $installed,
                'isquantity' => $result['IsQuantity'],
                'purchased' => ($result['storeappserviceid'] != -1),
                'recurring' => $result['recurring'],
                'freeplan' => $result['freeplan'],
                'freepaymentterm' => $result['freepaymentterm'],
                'isbundle' => ($result['packageappserviceid'] != -1),
                'isnew' => $result['IsNew'],
                'isTrial' => $isTrial,
                'price' => '$' . (
                    (floor($result['price']) == round($result['price'], 2)) ?
                        number_format($result['price']) :
                        number_format($result['price'], 2)
                    ),
                //'price'      => number_format((float)$result['price'], 2, '.', ''),
                'image' => $result['image'],
                'applink' => $this->url->link('marketplace/app', 'id=' . $result['id'])->format(),
                'links' => [
                    'uninstall' => $this->url->link(
                        'marketplace/home/uninstall?extension=' . $extension,
                        '',
                        'SSL'
                    )->format(),
                    'intall' => $this->url->link(
                        'marketplace/home/uninstall?extension=' . $extension,
                        '',
                        'SSL'
                    )->format(),
                    'install' => $this->url->link(
                        'marketplace/home/install',
                        'extension=' . $extension . '&' . http_build_query($this->request->get),
                        'SSL'
                    )->format(),
                    'trial' => $this->url->link(
                        'marketplace/home/trial',
                        'extension=' . $extension . '&' . http_build_query($this->request->get),
                        'SSL'
                    )->format(),
                    'extensionlink' => $this->url->link('module/' . $extension, '', 'SSL')->format(),
                    'buylink' => $buylink,
                ],
                'moduleType' => $result['type'],
                'extension' => $extension,
                'category'=>$result['category']
            );

            if (isset($filterData['installed'])) {
                if ($installed == 1) {
                    $avaliableModules[$key] = $moduleData;
                }
            } else {
                $avaliableModules[$key] = $moduleData;
            }

    }

        return $avaliableModules;
    }

    protected function getAppData($id){

        $this->initializer([
            'marketplace/appservice',
            'setting/extension',
            'marketplace/common'
        ]);

        $isApp=0;
        $isService=0;
        $isInstalled=0;
        $isPurchased=0;
        $isFree=0;

        $this->language->load('marketplace/app');

        //$this->document->setTitle($this->language->get('heading_title'));

        //$this->data['heading_title'] = $this->language->get('heading_title');

        // $this->load->model('marketplace/common');

        $result = $this->model_marketplace_common->getAppService($id);

		//TO:DO | we can enhance this later and add field at DB with number of trial days at trial App
		$maxTrialDays = $result['CodeName'] == 'lableb' ? 14 : 7 ; 
        $isApp = ($result['type']==1);
        $isService = ($result['type']==2);
        $isPurchased=($result['storeappserviceid'] != -1);
        $isFree=($result['packageappserviceid'] != -1);
        $promoCode = !empty($result['link']) && PRODUCTID >= $result['freeplan'] ? $result['link'] : '';
        if($isApp && ($isPurchased || $isFree)) {
            //$this->load->model('setting/extension');
            $installedextensions = $this->model_setting_extension->getInstalled('module');
            $isInstalled = in_array($result['CodeName'], $installedextensions);
        }

        $this->document->setTitle($result['Name']);
        $this->data['heading_title'] = $result['Name'];
        $this->data['id'] = $id;
        $this->data['moduleType'] = $result['type'];
        $this->data['name'] = $result['Name'];
        $this->data['minidesc'] = $result['MiniDescription'];
        $this->data['desc'] = $result['Description'];
        $this->data['image'] = $result['AppImage'];
        $this->data['coverimage'] = $result['CoverImage'];
        $this->data['price'] = '$'.((floor($result['price']) == round($result['price'], 2)) ? number_format($result['price']) : number_format($result['price'], 2));
        $this->data['isnew'] = $result['IsNew'];
        $this->data['isquantity'] = $result['IsQuantity'];
        $this->data['recurring'] = $result['recurring'];
        $this->data['isapp'] = $isApp;
        $this->data['isservice'] = $isService;
        $this->data['installed'] = $isInstalled;
        $this->data['purchased'] = $isPurchased;
        $this->data['isfree'] = $isFree;
        $this->data['isbundle'] = $isFree;
        $this->data['freeplan'] = $result['freeplan'];
        $this->data['freepaymentterm'] = $result['freepaymentterm'];
        $this->data['PRODUCTID'] = PRODUCTID;
        $this->data['extension']  = $result['CodeName'];

        $this->data['isTrial'] = PRODUCTID == 3 ? '1' : '0';
		$this->data['maxTrialDays'] = $maxTrialDays;
        $this->data['trial_model_description'] = vsprintf($this->language->get('text_modal_x_trial_body'), [$maxTrialDays,$maxTrialDays]);

        $activeTrials = array_column($this->extension->getActiveTrials(), null, 'extension_code');

        if (isset($activeTrials[$result['CodeName']]) && !$isPurchased && !$isFree) {
            if (isset($activeTrials[$result['CodeName']]['deleted_at'])) {
                $isTrial = 2;
            } else {
                try {
                    $today = new DateTime('now');
                    $created_at = new DateTime($activeTrials[$result['CodeName']]['created_at']);
                    $diff = $today->diff($created_at)->days;
                   $remaining_time = $maxTrialDays - (int) $diff;
                    $this->data['remaining_trial_time'] =$remaining_time;
                }
                catch (Exception $e) {}

                $isTrial = 1;
            }
        } else {
            $isTrial = 0;
        }

        $this->data['app_isTrial'] = $isTrial;

        $this->data['packageslink'] = $this->url->link('billingaccount/plans', 'token=' . $this->session->data['token'], 'SSL');

        #required for paid apps/services
        $this->load->model('billingaccount/common');
        $timestamp = time(); # Get current timestamp
        $email = BILLING_DETAILS_EMAIL; # Clients Email Address to Login

        # Define WHMCS URL & AutoAuth Key
        $whmcsurl = MEMBERS_LINK;
        $autoauthkey = MEMBERS_AUTHKEY;
        $hash = sha1($email.$timestamp.$autoauthkey); # Generate Hash

        if ($this->user->hasPermission('access', 'billingaccount/plans') && ENABLE_BILLING == '1') {
            $billingAccess = '1';
        }

        $this->data['billingAccess'] = $billingAccess;

        $action = array();
        if($isApp) {
            #action: <install/uninstall, edit(if installed)> if free or purchased, <buy> if not purchased or not free
            $extension = $result['CodeName'];
            if(!$isPurchased && !$isFree) {
                #action: buy
                $tmpbuylink='cart.php?a=add&pid=' . $result['whmcsappserviceid'];
                $tmpbuylink = ($this->language->get('code') == 'ar') ? $tmpbuylink . '&language=Arabic' : $tmpbuylink = $tmpbuylink . '&language=English';
                if(!empty($promoCode)) {
                    $tmpbuylink .= '&promocode=' . $promoCode;
                }
                $buylink = ($billingAccess == "1") ? $whmcsurl."?email=$email&timestamp=$timestamp&hash=$hash&goto=".urlencode($tmpbuylink) : '#';

                $this->data['buylink'] = $buylink;
            }
            else{
                $action[] = array(
                    'text' => $isInstalled ? $this->language->get('text_uninstall') : $this->language->get('text_install'),
                    'icon' => $isInstalled ? "icon-cancel-circle2" : "icon-download",
                    'href' => $this->url->link('marketplace/app/'. ($isInstalled ? 'uninstall' : 'install'), 'token=' . $this->session->data['token'] . '&extension=' . $extension . '&id=' . $id, 'SSL')
                );
                $this->data['extensionlink'] = $this->url->link('module/' . $extension . '', 'token=' . $this->session->data['token'], 'SSL');
                $this->data['actions'] = $action;
            }
        }
        elseif($isService) {
            #action: request service
            $tmpbuylink='cart.php?a=add&pid=' . $result['whmcsappserviceid'];
            $tmpbuylink = ($this->language->get('code') == 'ar') ? $tmpbuylink . '&language=Arabic' : $tmpbuylink = $tmpbuylink . '&language=English';
            if(!empty($promoCode)) {
                $tmpbuylink .= '&promocode=' . $promoCode;
            }
            $buylink = ($billingAccess == "1") ? $whmcsurl."?email=$email&timestamp=$timestamp&hash=$hash&goto=".urlencode($tmpbuylink) : '#';

            $this->data['buylink'] = $buylink;
        }
    }

    private static $marketplaceAppsRules;
    
    private function marketplaceAppsRules()
    {
        if (!static::$marketplaceAppsRules) {
            $marketplace_rules = file_get_contents('json/marketplace_rules.json');
            static::$marketplaceAppsRules = json_decode($marketplace_rules, true);
        }
        
        return static::$marketplaceAppsRules;
    }
    
    protected function marketPlaceRules($appCode)
    {
        $apps = $this->marketplaceAppsRules();
        $rules=[];
        if (array_key_exists($appCode, $apps) && $apps[$appCode]['Rules'])
        {
            foreach ($apps[$appCode]['Rules'] as $key => $value) {
                $rules[$key] = ['condition' => $value['condition']];
                
                if ($key == "storeCode") {
                    $rules[$key]['codes'] = $value['codes'];
                }
                
                if ($key == "config") {
                    $rules[$key]['key'] = $value['key'];
                }

                if ($key == "planId") {
                    $rules[$key]['plans'] = $value['plans'];
                }
            }
            
            // if($apps[$appCode]['Rules']['storeCode'])
            // {
            //     $ruleCondition=$apps[$appCode]['Rules']['storeCode']['condition'];
            //     $codes=$apps[$appCode]['Rules']['storeCode']['codes'];
            //     $rules['storeCode']=[
            //         'condition'=>$ruleCondition,
            //         'codes'=> $codes
            //         ];
            // }
        }
        return $rules;
    }
}
