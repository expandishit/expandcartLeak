<?php
class ControllerMarketplaceApp extends Controller {

    public function index() {
        $id = $this->request->get['id'];
        $isApp=0;
        $isService=0;
        $this->getAppData($id,$isApp,$isService);


        $this->data['flash_message'] = null;
        if (isset($this->session->data['charge'])) {
            $this->data['flash_message'] = $this->session->data['charge'];
            unset($this->session->data['charge']);
        }

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $isApp ? $this->language->get('text_apps') : $this->language->get('text_services'),
            'href'      => $this->url->link('marketplace/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->data['heading_title'],
            'href'      => $this->url->link('marketplace/app', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $this->template = 'marketplace/app.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
    }

    protected function getAppData($id,$isApp=0,$isService=0){

        $this->initializer([
            'marketplace/appservice',
            'setting/extension',
            'marketplace/common'
        ]);

        $isInstalled=0;
        $isPurchased=0;
        $isFree=0;

        $this->language->load('marketplace/app');

        //$this->document->setTitle($this->language->get('heading_title'));

        //$this->data['heading_title'] = $this->language->get('heading_title');

        // $this->load->model('marketplace/common');

        $result =$this->appservice->getAppServiceById($id);
		
		//TO:DO | we can enhance this later and add field at DB with number of trial days at trial App 
		$maxTrialDays = $result['CodeName'] == 'lableb' ? 14 : 7 ; 
        $isApp = ($result['type']==1);
        $isService = ($result['type']==2);
        $isPurchased=($result['storeappserviceid'] != -1);
        $isFree=($result['packageappserviceid'] != -1);
        $promoCode = !empty($result['link']) && PRODUCTID >= $result['freeplan'] ? $result['link'] : '';
        // if($isApp && ($isPurchased || $isFree)) {
            //$this->load->model('setting/extension');
            $installedextensions = $this->model_setting_extension->getInstalled('module');
            $isInstalled = in_array($result['CodeName'], $installedextensions);
        // }

        $this->document->setTitle($result['Name']);
        $this->data['heading_title'] = $result['Name'];

        $this->data['text_install'] = $this->language->get('text_install');
        $this->data['text_uninstall'] = $this->language->get('text_uninstall');
        $this->data['text_edit'] = $this->language->get('text_edit');
        $this->data['text_new'] = $this->language->get('text_new');
        $this->data['text_installed'] = $this->language->get('text_installed');
        $this->data['text_purchased'] = $this->language->get('text_purchased');
        $this->data['text_item'] = $this->language->get('text_item');
        $this->data['text_buy'] = $this->language->get('text_buy');
        $this->data['text_rec_once'] = $this->language->get('text_rec_once');
        $this->data['text_free_all'] = $this->language->get('text_free_all');
        $this->data['text_free_business'] = $this->language->get('text_free_business');
        $this->data['text_free_ultimate'] = $this->language->get('text_free_ultimate');
        $this->data['text_free_enterprise'] = $this->language->get('text_free_enterprise');
        $this->data['text_yearly_only'] = $this->language->get('text_yearly_only');
        $this->data['text_choose_plans'] = $this->language->get('text_choose_plans');
        $this->data['text_choose_plans_text'] = $this->language->get('text_choose_plans_text');
        $this->data['text_explore_plans'] = $this->language->get('text_explore_plans');
        $this->data['direction'] = $this->language->get('direction');

        $this->data['id'] = $this->session->data['appid'] = $id;
        $this->data['moduleType'] = $result['type'];
        $this->data['name'] = $result['Name'];
        $this->data['minidesc'] = $result['MiniDescription'];
        $this->data['category'] = $result['category'];
        $this->data['desc'] = $result['Description'];
        $this->data['image'] = $result['AppImage'];
        $this->data['coverimage'] = $result['CoverImage'];
        $this->data['price'] = '$'.((floor($result['price']) == round($result['price'], 2)) ? number_format($result['price']) : number_format($result['price'], 2));
        $this->data['isnew'] = $result['IsNew'];
        $this->data['isquantity'] = $result['IsQuantity'];
        $this->data['recurring'] = $result['recurring'];
        $this->data['isapp'] = $isApp;
        $this->data['isservice'] = $isService;
        $this->data['isinstalled'] = $isInstalled;
        $this->data['ispurchased'] = $isPurchased;
        $this->data['installed'] = $isInstalled;
        $this->data['purchased'] = $isPurchased;
        $this->data['isfree'] = $isFree;
        $this->data['isbundle'] = $isFree;
        $this->data['freeplan'] = $result['freeplan'];
        $this->data['freepaymentterm'] = $result['freepaymentterm'];
        $this->data['PRODUCTID'] = PRODUCTID;
        $this->data['extension']  = $result['CodeName'];
        $this->data['can_uninstall_app'] = $this->checkIfCanUnInstallApp($result['CodeName']);

        $this->data['isTrial'] = PRODUCTID == 3 ? '1' : '0';
        $this->data['maxTrialDays'] = $maxTrialDays;
        $this->data['trial_model_description'] = vsprintf($this->language->get('text_modal_x_trial_body'), [$maxTrialDays,$maxTrialDays]);


        $activeTrials = array_column($this->extension->getActiveTrials(), null, 'extension_code');
		
        if ($this->extension->isTrial($result['CodeName']) && !$isPurchased && !$isFree) {
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
		

        // dd([$isTrial, $isinstalled]);

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
                // $buylink = ($billingAccess == "1") ? $whmcsurl."?email=$email&timestamp=$timestamp&hash=$hash&goto=".urlencode($tmpbuylink) : '#';
                $buylink = ($billingAccess == "1") ? $this->url->link('account/checkout', 'asid='.$id, 'SSL') : '#';

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
            // $buylink = ($billingAccess == "1") ? $whmcsurl."?email=$email&timestamp=$timestamp&hash=$hash&goto=".urlencode($tmpbuylink) : '#';
            $buylink = ($billingAccess == "1") ? $this->url->link('account/checkout', 'asid='.$id, 'SSL') : '#';

            $this->data['buylink'] = $buylink;
        }
    }

    public function install() {
        $id = $this->request->get['id'];
        $this->language->load('extension/module');

        if (!$this->user->hasPermission('modify', 'marketplace/home')) {
            $this->session->data['error'] = $this->language->get('error_permission');

            $this->redirect($this->url->link('marketplace/app', 'token=' . $this->session->data['token'] . '&id=' . $id, 'SSL'));
        } else {
            $this->load->model('setting/extension');

            $this->model_setting_extension->install('module', $this->request->get['extension']);

            $this->load->model('user/user_group');

            $this->model_user_user_group->addPermission($this->user->getId(), 'access', 'module/' . $this->request->get['extension']);
            $this->model_user_user_group->addPermission($this->user->getId(), 'modify', 'module/' . $this->request->get['extension']);

            require_once(DIR_APPLICATION . 'controller/module/' . $this->request->get['extension'] . '.php');

            $class = 'ControllerModule' . str_replace('_', '', $this->request->get['extension']);
            $class = new $class($this->registry);

            if (method_exists($class, 'install')) {
                $class->install();
            }

            $this->redirect($this->url->link('marketplace/app', 'token=' . $this->session->data['token'] . '&id=' . $id, 'SSL'));
        }
    }

    public function uninstall() {
        $id = $this->request->get['id'];

        $this->language->load('extension/module');

        $extension = $this->request->get['extension'];
        if (!$this->user->hasPermission('modify', 'marketplace/home') || !$this->checkIfCanUnInstallApp($extension)) {
            $this->session->data['error'] = $this->language->get('error_permission');
            $this->redirect($this->url->link('marketplace/app', 'token=' . $this->session->data['token'] . '&id=' . $id, 'SSL'));
        } else {
            $this->load->model('setting/extension');
            $this->load->model('setting/setting');

            $this->model_setting_extension->uninstall('module', $extension);

            require_once(DIR_APPLICATION . 'controller/module/' . $extension . '.php');

            $class = 'ControllerModule' . str_replace('_', '', $extension);
            $class = new $class($this->registry);

            if (method_exists($class, 'uninstall')) {
                $class->uninstall();
            }

            $this->model_setting_setting->deleteSetting($extension);

            $this->redirect($this->url->link('marketplace/app', 'token=' . $this->session->data['token'] . '&id=' . $id, 'SSL'));
        }
    }

    private function checkIfCanUnInstallApp($app)
    {
        if (!Extension::isInstalled($app)) {
            return false;
        }

        if ($app == 'productsoptions_sku' && \Extension::isInstalled('knawat_dropshipping')) {
            return false;
        }
        
        // merchant can't uninstall the quick checkout app if 3steps checkout mode is disabled or not whitelisted
        if ($app == "quickcheckout") {
            if (
                !defined('THREE_STEPS_CHECKOUT') ||
                (defined('THREE_STEPS_CHECKOUT') && (THREE_STEPS_CHECKOUT == 0 || (THREE_STEPS_CHECKOUT == 1 && !$this->identity->isStoreOnWhiteList())))
            ) return false;
        }

       return true;
    }

    /**
     * Proxy method, it should be kept as is.
     * the purpose of this route method is to
     * navigate the clients who came from the
     * whatsapp marketing campaign to the app
     * page.
     */
    public function whatsapp()
    {
        $this->redirect($this->url->link('marketplace/app', 'id=142', 'SSL'));
    }
}
