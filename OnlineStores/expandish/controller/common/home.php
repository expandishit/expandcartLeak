<?php
class ControllerCommonHome extends Controller {

    protected $isCustomerAllowedToViewPrice;

    public function __construct($registry)
    {
		parent::__construct($registry);
		$this->isCustomerAllowedToViewPrice = $this->customer->isCustomerAllowedToViewPrice();
    }
	public function index($args)
    {
        // exit if affiliate app not installed for affiliate routs
        if($this->request->get['route'] && str_contains($this->request->get['route'],'affiliate')){
            $affiliateAppIsActive = (
                \Extension::isInstalled('affiliates') && $this->config->get('affiliates')['status']
            );
            if(! $affiliateAppIsActive){
                $this->redirect(
                    $this->url->link('error/not_found', '', 'SSL')
                );
                exit();
            }
        }

        // promo code in query string (coupon)
        if (isset($this->request->get['promo']) && !empty($this->request->get['promo'])) {
            setcookie('promo', $this->request->get['promo'], time() + 3600 * 24 * 1000, '/');
            $this->session->data['coupon'] = $this->request->get['promo'];
        }

        if (isset($this->request->cookie['promo'])) {
            $this->session->data['coupon'] = $this->request->cookie['promo'];
        }

        //Check for natural touch user id 147295
        if(WHMCS_USER_ID == '147295'){
            //Set store language
            $lang = in_array($this->config->get('config_language'),['ar','en']) ? $this->config->get('config_language') : 'en';

            //Get all stores for natural touch
            $this->load->model('module/multi_store_manager/settings');
            $this->data['stores'] = $this->model_module_multi_store_manager_settings->getStores();

            //load language file
            $this->language->load_json('module/multistore');

            //Loop on all stores to check their status
            foreach($this->data['stores'] as $key=>$store){
                $sql_status = '
                    SELECT * from `ashawqy_'.$store['STORECODE'].'`.`setting`
                    WHERE `key`="config_maintenance"
                ';
                $query = $this->db->query($sql_status);

                $this->data['stores'][$key] = $store;
                if($query->row['value'] == 0){
                    $this->data['stores'][$key]['url'] = "http://".$store['DOMAINNAME'];
                    $this->data['stores'][$key]['name'] = $this->language->get($store['STORECODE']);
                    continue;
                }
                unset($this->data['stores'][$key]);
            }

            $this->data['confirm_text'] = $this->language->get('multistore_confirm');
            $this->data['change_store'] = $this->language->get('change_store');

            $storesorder = array();
            $storesData = $this->data['stores'];

            if ($lang == 'en') {
                function cmp($a, $b)
                {
                    $storesorder = array('Jeddah'=>0, 'Makkah'=>1, 'Eastern Province'=>2, 'Qassim'=>3, 'Madinah'=>4, 'Ehsa’a'=>5, 'Aseer'=>6, 'Taif'=>7, 'Hail'=>8, 'Tabuk'=>9, 'Other Areas'=>10);

                    $posA = $storesorder[$a['name']];
                    $posB = $storesorder[$b['name']];

                    if ($posA == $posB) {
                        return 0;
                    }
                    return ($posA < $posB) ? -1 : 1;
                }
            } else {
                function cmp($a, $b)
                {
                    $storesorder = array('جدة'=>0, 'مكة المكرمة'=>1, 'المنطقة الشرقية'=>2, 'القصيم'=>3, 'المدينة المنورة'=>4, 'الإحساء'=>5, 'عسير'=>6, 'الطائف'=>7, 'حائل'=>8, 'تبوك'=>9, 'باقي المناطق'=>10);

                    $posA = $storesorder[$a['name']];
                    $posB = $storesorder[$b['name']];

                    if ($posA == $posB) {
                        return 0;
                    }
                    return ($posA < $posB) ? -1 : 1;
                }
            }
            usort($storesData, 'cmp');

            if ($lang == 'en') {
                array_unshift($storesData, array('url' => 'https://global.naturaltouchshop.com/', 'name' => 'Riyadh'));

                $storesData[] =  array('url' => 'https://global.naturaltouchshop.com/', 'name' => 'Other Areas');
            } else {
                array_unshift($storesData, array('url' => 'https://global.naturaltouchshop.com/', 'name' => 'الرياض'));

                $storesData[] = array('url' => 'https://global.naturaltouchshop.com/', 'name' => 'باقي المناطق');
            }

            $this->data['stores'] = $storesData;
        }else{
            //Uncomment the below array to test
            // $this->data['stores'] = [
            //     [
            //         "url"=>"http://sss.expandcart.com",
            //         "name"=>"عسير",
            //     ],
            //     [
            //         "url"=>"gogo",
            //         "name"=>"حائل",
            //     ],
            // ];

            $this->data['stores'] = [];
        }

        $this->load->model('setting/setting');

        $this->data['store_reviews_app_is_activated'] = \Extension::isInstalled('store_reviews') && (bool) $this->config->get('store_reviews_app_status');

        if ($this->data['store_reviews_app_is_activated'])
        {
            $this->document->addScript('expandish/view/javascript/store_reviews/store_reviews.js');

            $this->data['store_review_installed'] = true;
            $this->data['store_review_action'] = $this->url->link('module/store_reviews/postStoreReview');

            $this->load->model('module/store_reviews');
            $this->data['customer_rates'] = $this->model_module_store_reviews->getCustomerReview([
                'customer_id' => $this->customer->getId(),
			    'ip_address' => $this->customer->getClientRealIP()
            ]);

            $this->data['customer_can_rate'] = empty($this->data['customer_rates']);
            if (!$this->config->get('store_reviews_app_allow_guest') && !$this->customer->getId()) {
                $this->data['customer_can_rate'] = false;
            }

            $all_rates = $this->model_module_store_reviews->getStoreRate();

            $this->data['store_rate'] = $all_rates['rate_count'];
            $this->data['store_rate1'] = $this->model_module_store_reviews->getStoreRate1()['rate_count'];
            $this->data['store_rate2'] = $this->model_module_store_reviews->getStoreRate2()['rate_count'];
            $this->data['store_rate3'] = $this->model_module_store_reviews->getStoreRate3()['rate_count'];
            $this->data['store_rate4'] = $this->model_module_store_reviews->getStoreRate4()['rate_count'];
            $this->data['store_rate5'] = $this->model_module_store_reviews->getStoreRate5()['rate_count'];

            $weightMultipliedWithCount = 1*$this->data['store_rate1'] + 2*$this->data['store_rate2'] + 3*$this->data['store_rate3'] + 4*$this->data['store_rate4'] + 5*$this->data['store_rate5'];
            $this->data['overall_rate'] = round($weightMultipliedWithCount/$all_rates['rate_count']);
            if (is_nan($this->data['overall_rate'])) {
                $this->data['overall_rate'] = '0';
            }
        }

        // detect customer language change
        if ($this->customer->isLogged()) {
            $customerLanguageId = $this->customer->getLanguageId();
            $currentLanguageId = $this->config->get("config_language_id");
            if ($customerLanguageId != $currentLanguageId) {
                $customerId = $this->customer->getId();
                $this->load->model('account/customer');
                $this->model_account_customer->updateCustomerLanguage($customerId, $currentLanguageId);
            }
        }

        if (\Extension::isInstalled('fast_finder') && $this->config->get('fast_finder')['status'] && !$this->config->get('lableb')['status'])
        {
            $this->document->addScript('expandish/view/javascript/fast_finder/fast_finder.js');
        }

        if (\Extension::isInstalled('lableb')&& $this->config->get('lableb')['status'])
        {
            $this->document->addScript('expandish/view/javascript/lableb/lableb.js');
        }

        // check if game ball app installed
        if(\Extension::isInstalled('gameball')){
            $this->load->model('module/gameball/settings');
            // check if app status is active
            if($this->model_module_gameball_settings->isActive()){
                $gameballSettings = $this->model_module_gameball_settings->getSettings();
                $this->data['gameballApiKey'] = ($gameballSettings['environment'] == 1) ? $gameballSettings['test_apikey'] : $gameballSettings['live_apikey'] ;
            }
        }
        // d-social-login module custom script to control buttons
        $this->document->addScript('expandish/view/javascript/common/d-social-login.js');

        #######################social login app#########################################
                $social_login_settings = $this->config->get('d_social_login_settings');
                if ($social_login_settings && $social_login_settings['status']) {
                        $this->data['d_social_login_enabled'] = true;
                        $this->data['d_social_login'] = $this->getChild('module/d_social_login');
                }

        $this->language->load_json('common/home');

        // if ($this->customer->isLogged() && $this->customer->getApprovalStatus() > 1) {
        //     if (!in_array($this->request->get['route'], [
        //         'account/activation/status',
        //         'account/activation/activate',
        //         'account/logout',
        //         'error/not_found',
        //         'checkout/success',
        //     ])) {
        //         // $this->redirect($this->url->link('account/activation/status'));
        //     }
        // }
        if ($this->customer->isLogged() && $this->customer->getApprovalStatus() > 1) {
            if (in_array($this->request->get['route'], [
                'checkout/cart',
                'checkout/checkout',
            ])) {
                $this->redirect($this->url->link('account/activation/status'));
            }
        }

        $this->data['integrations']="";

        $this->data['integration_settings'] = $this->model_setting_setting->getSetting('integrations');

        // check if a user enabled facebook pixel integration
        $facebook_pixel_setting=$this->model_setting_setting->getSetting('integrations');
        if($facebook_pixel_setting['mn_integ_fbp_status']){
            $facebook_pixel_ecommerce_code=$facebook_pixel_setting['mn_integ_fbp_code'];

            $this->data['integrations'].=<<<HTML
       <script>
           !function(f,b,e,v,n,t,s)
           {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
               n.callMethod.apply(n,arguments):n.queue.push(arguments)};
               if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.agent='plexpandcart';
               n.queue=[];t=b.createElement(e);t.async=!0;
               t.src=v;s=b.getElementsByTagName(e)[0];
               s.parentNode.insertBefore(t,s)}(window, document,'script',
               'https://connect.facebook.net/en_US/fbevents.js');
           fbq('init', '{$facebook_pixel_ecommerce_code}');
           fbq('track', 'PageView');

        </script>
HTML;
        }

        // check if a user enabled Twitter pixel integration
        if($this->config->get('twitter_pixel_status')){
            $twitter_pixel_id = $this->config->get('twitter_pixel_id');

            $this->data['integrations'].=<<<HTML
        <!-- Twitter universal website tag code -->
        <script>
        !function(e,t,n,s,u,a){e.twq||(s=e.twq=function(){s.exe?s.exe.apply(s,arguments):s.queue.push(arguments);
        },s.version='1.1',s.queue=[],u=t.createElement(n),u.async=!0,u.src='//static.ads-twitter.com/uwt.js',
        a=t.getElementsByTagName(n)[0],a.parentNode.insertBefore(u,a))}(window,document,'script');
        twq('init','{$twitter_pixel_id}');
        twq('track','PageView');
        </script>
        <!-- End Twitter universal website tag code -->
HTML;
        }

        // check if a user enabled snapchat pixel integration
        if($this->data['integration_settings']['mn_integ_snp_status']){
            $snapchat_pixel_ecommerce_code  = $this->data['integration_settings']['mn_integ_snp_code'];
            $snapchat_pixel_ecommerce_email = $this->data['integration_settings']['mn_integ_snp_email'];

            $this->data['integrations'].=<<<HTML

        <script type='text/javascript'>
          (function(win, doc, sdk_url){
          if(win.snaptr) return;
          var tr=win.snaptr=function(){
          tr.handleRequest? tr.handleRequest.apply(tr, arguments):tr.queue.push(arguments);
        };
          tr.queue = [];
          var s='script';
          var new_script_section=doc.createElement(s);
          new_script_section.async=!0;
          new_script_section.src=sdk_url;
          var insert_pos=doc.getElementsByTagName(s)[0];
          insert_pos.parentNode.insertBefore(new_script_section, insert_pos);
        })(window, document, 'https://sc-static.net/scevent.min.js');

          snaptr('init','{$snapchat_pixel_ecommerce_code}',{
          'user_email':'{$snapchat_pixel_ecommerce_email}'
        })
          snaptr('track','PAGE_VIEW');
        </script>
HTML;
        }

        // check if a user enabled google analytics integration
        $google_analytics_ecommerce_setting=$this->model_setting_setting->getSetting('integrations');
        if($google_analytics_ecommerce_setting['mn_integ_go_analytics_status']){
            $google_analytics_ecommerce_id=$google_analytics_ecommerce_setting['mn_integ_go_analytics_id'];

            $this->data['integrations'].=<<<HTML
        <!-- Google Analytics -->
        <script>
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

        ga('create', '{$google_analytics_ecommerce_id}');
        ga('require', 'ec');
HTML;

            if(count($this->setGoogleAnalyticsIntegration($this->request->get)) > 0){
                foreach ($this->setGoogleAnalyticsIntegration($this->request->get) as $action){
                    $this->data['integrations'].=$action;
                }
            }
            else{
                $this->data['integrations'].="ga('send', 'pageview');";
            }
        $this->data['integrations'].=<<<HTML
        </script>
        <!-- End Google Analytics -->
HTML;
        }
        // check if a user enabled google adwords integration
        $google_adwords_setting=$this->model_setting_setting->getSetting('integrations');
        if($google_adwords_setting['mn_integ_go_adwords_status']){
            $this->data['mn_integ_go_adwords_status']=$google_adwords_setting['mn_integ_go_adwords_id'];
            $mn_integ_go_adwords_id=$google_adwords_setting['mn_integ_go_adwords_id'];
            $mn_integ_go_adwords_label=$google_adwords_setting['mn_integ_go_adwords_label'];

            $this->data['integrations'].=<<<HTML
    <!-- Global site tag (gtag.js) - Google AdWords: 123456789 -->
      <script async src="https://www.googletagmanager.com/gtag/js?id={$mn_integ_go_adwords_id}"></script>
      <script>
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments);}
          gtag('js', new Date());
          gtag('config', '{$mn_integ_go_adwords_id}');
      </script>
HTML;
        if($this->request->get['route'] == "checkout/success"){
            $order_id = $this->session->data['order_id'];
            $this->load->model('account/order');
            $order_info = $this->model_account_order->getOrder($order_id);
            $total = $order_info['total'];
            $currency = $order_info['currency_code'];

            $this->data['integrations'].=<<<HTML
 <!-- Event snippet for Example conversion page -->
        <script>
          gtag('event', 'conversion',
          {
              'send_to': '{$mn_integ_go_adwords_id}/{$mn_integ_go_adwords_label}',
              'value' : '{$total}',
              'currency' : '{$currency}'
          });
        </script>
HTML;

        }
        }
        $this->initializer([
            'security/throttling'
        ]);

        if ($this->throttling->throttlingStatus()) {

            $this->load->model('account/customer');

            if ($this->throttling->isBanned($this->customer->getRealIp()) || $this->model_account_customer->isBanIp($this->customer->getRealIp())) {
                if ($this->request->get['route'] != 'error/not_found') {
                    $this->redirect($this->url->link('error/not_found'));
                }
            }
        }

        // set shipping method to the default if no current one exists.
        if(!isset($this->session->data['shipping_method']['code']) ||
           !isset($this->session->data['shipping_method']['title']) ||
           !isset($this->session->data['shipping_method']['cost']) ||
            ( isset($this->session->data['recalc_shipping']) && $this->session->data['recalc_shipping'] == "1") ){

            unset($this->session->data['shipping_method']);
            $op = $this->getChild('module/quickcheckout/load_settings');
            $this->session->data['recalc_shipping'] = 0;
        }

        //Language Selector
        if (isset($this->request->post['language_code'])) {
            $this->language->changeLanguage($this->request->post['language_code']);
            /*$post_lang_code = $this->request->post['language_code'];
            $this->session->data['recalc_shipping'] = 1;

            $sessionLanguage = $this->session->data['language'];

            $this->session->data['language'] = $post_lang_code;
            $expand_seo = $this->preAction->isActive();
            if (isset($this->request->post['redirect']) && isset($this->request->get['_route_']) && !$expand_seo) {
                $this->redirect($this->request->post['redirect']);
            } else if (isset($_SERVER['HTTP_REFERER']) && !$expand_seo) {
                $this->redirect($_SERVER['HTTP_REFERER']);
            } else {
                if ($expand_seo) {
                    $_GET['_route_'] = str_replace(
                        $this->session->data['language'],
                        $post_lang_code,
                        $_GET['_route_']
                    );

                    $newRoute = $this->preAction->getRoute($expand_seo, $this->request->get['_route_']);
                    print_r($newRoute);
                    exit();
                    parse_str(html_entity_decode(parse_url($this->request->get)), $queryArray);

                    $newurl = $this->preAction->getURL($expand_seo, $newRoute['data'], $newRoute['parameters']);

                    if ($newurl && $newurl != '/') {
                        $this->redirect(
                            '/' . preg_replace(
                                "#^" . $sessionLanguage . "\/#",
                                $post_lang_code.'/',
                                $newurl
                            )
                        );
                    } else {
                        $this->redirect($this->url->link('common/home'));
                    }

                } else {
                    $this->redirect($this->url->link('common/home'));
                }
            }*/
        }

        $this->data['multiseller'] = false;
        // $queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");
        if(\Extension::isInstalled('multiseller')) {
            $this->data['multiseller'] = true;
        }

		//if whatsapp chat is installed & show chat is enabled & whatsapp_activation status true
		 // whatsapp chat
        if (\Extension::isInstalled('whatsapp')) {
             $chat_show_enabled        	= (int)$this->config->get('whatsapp_chat_show') ;
			 $whatsapp_chat_applied_on 	= $this->config->get('whatsapp_chat_applied_on') ;
			 $selected_groups 		   	= $this->config->get('whatsapp_chat_selected_groups') ;
			 $chat_url 				 	= $this->config->get('whatsapp_chat_url');

			 $chat_show = false;
			//chat show enabled make sure that customer grouped allowed
			if($chat_show_enabled){
				if($whatsapp_chat_applied_on =='all' ){
						$chat_show = true;
				}else {
					$isLogged 			= $this->customer->isLogged();
					$customer_group_id 	= $this->customer->getCustomerGroupId();
					$chat_show 			=  $isLogged && in_array($customer_group_id,$selected_groups);
				}
			}

			$this->data['whatsapp_chat_show']	= $chat_show;
            $this->data['whatsapp_chat_url']  	= $chat_url? $chat_url.'&lang='.$this->session->data['language'] : '#';
        }

        if (\Extension::isInstalled('whatsapp_cloud')) 
		{
             
			 $chat_show_enabled        = (int)$this->config->get('whatsapp_cloud_chat_show') ;
			 $whatsapp_chat_applied_on = $this->config->get('whatsapp_cloud_chat_applied_on') ?? "all";
			 $selected_groups 		   = $this->config->get('whatsapp_cloud_chat_selected_groups') ?? [];
			 $whatsapp_cloud_phone_cc  = $this->config->get('whatsapp_cloud_phone_cc') ;
			 $whatsapp_cloud_phone_cc = str_replace("+","",$whatsapp_cloud_phone_cc );
			 $whatsapp_cloud_phone_number 	=  $this->config->get('whatsapp_cloud_phone_number') ;

			 $default_chat_url ='https://api.whatsapp.com/send?phone='.$whatsapp_cloud_phone_cc.$whatsapp_cloud_phone_number;

			 $chat_url =  $this->config->has('whatsapp_cloud_chat_url') ? $this->config->get('whatsapp_cloud_chat_url') : $default_chat_url ;

			 $chat_show = false;
			//chat show enabled make sure that customer grouped allowed
			if($chat_show_enabled){
				if($whatsapp_chat_applied_on =='all' ){
						$chat_show = true;
				}else {
					$isLogged 			= $this->customer->isLogged();
					$customer_group_id 	= $this->customer->getCustomerGroupId();
					$chat_show 			=  $isLogged && in_array($customer_group_id,$selected_groups);
				}
			}

			$this->data['whatsapp_chat_show']	= $chat_show;
            $this->data['whatsapp_chat_url']  	= $chat_url? $chat_url.'&lang='.$this->session->data['language'] : '#';
        }

        /*if (isset($this->request->get['draftlangcode'])) {
            $this->session->data['language'] = $this->request->get['draftlangcode'];

            if (isset($this->request->post['redirect'])) {
                $this->redirect($this->request->post['redirect']);
            } else {
                $this->redirect($this->url->link('common/home'));
            }
        }*/

        /*$queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");

        if($queryMultiseller->num_rows) {
            $this->data = array_merge($this->data, $this->language->load_json('multiseller/multiseller'));
            $this->data['ms_total_products'] = $this->MsLoader->MsProduct->getTotalProducts(array(
                'enabled' => 1,
                //'product_status' => array(MsProduct::STATUS_ACTIVE),
            ));

            $this->data['ms_total_sellers'] = $this->MsLoader->MsSeller->getTotalSellers(array(
                'seller_status' => array(MsSeller::STATUS_ACTIVE)
            ));

            $this->MsLoader->MsHelper->addStyle('multiseller');

            // note: renamed catalog
            $lang = "view/javascript/multimerch/datatables/lang/" . $this->config->get('config_language') . ".txt";
            $this->data['dt_language'] = file_exists(DIR_APPLICATION . $lang) ? "'catalog/$lang'" : "undefined";

            // Add complemented common.js
            $this->document->addScript('expandish/view/javascript/multimerch/ms-common.js');
        }
        else {
            // Add complemented common.js
            $this->document->addScript('catalog/view/javascript/ms-common.js');
        }*/

        //Currency Selector
        if (isset($this->request->post['currency_code'])) {
            $this->currency->set($this->request->post['currency_code']);

            unset($this->session->data['shipping_method']);
            unset($this->session->data['shipping_methods']);

            if (isset($this->request->post['redirect'])) {
                $this->redirect($this->request->post['redirect']);
            } else {
                $this->redirect($this->url->link('common/home'));
            }
        }


        $this->document->setTitle($this->config->get('config_title'));
        $this->document->setDescription($this->config->get('config_meta_description'));

        $queryRewardPointInstalled = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'reward_points_pro'");
        if($queryRewardPointInstalled->num_rows) {

            $this->document->addStyle('expandish/view/javascript/rewardpoints/css/stylesheet.css');
            $this->document->addStyle('expandish/view/javascript/rewardpoints/css/jquery.nouislider.css');
            $this->document->addStyle('expandish/view/javascript/rewardpoints/css/jquery.nouislider.pips.css');
            $this->document->addScript('expandish/view/javascript/rewardpoints/js/lib/jquery.nouislider.all.js');
            $this->document->addScript('expandish/view/javascript/rewardpoints/js/lib/underscore.js');
            $this->document->addScript('expandish/view/javascript/rewardpoints/js/lib/backbone.js');
            $this->document->addScript('expandish/view/javascript/rewardpoints/js/head.main.js');
            $this->document->addScript('expandish/view/javascript/rewardpoints/js/view.js');

            $this->data['reward_point_installed'] = true;
        }

        $rss = isset($this->request->get["rss"]) ? $this->request->get["rss"] : "";
        if ($rss=="latest" || $rss=="featured" || $rss=="special" || $rss=="bestseller") {
            $this->load->model("catalog/product");
            $this->load->model("localisation/currency");
            $this->load->model("tool/image");

            $limit = 20;
            $image_width = 100;
            $image_height = 100;
            $currency = $this->currency->getCode();
            if ($rss == "latest") {
                $data = array("sort"  => "p.date_added", "order" => "DESC", "start" => 0, "limit" => $limit);
                $products = $this->model_catalog_product->getProducts($data);
            }
            elseif ($rss == "featured") {
                $featured_products = explode(",", $this->config->get("featured_product"));
                $featured_products = array_slice($featured_products, 0, $limit);
                foreach ($featured_products as $product_id) $products[] = $this->model_catalog_product->getProduct($product_id);
            }
            elseif ($rss == "special") {
                $data = array("sort"  => "pd.name", "order" => "ASC", "start" => 0, "limit" => $limit);
                $products = $this->model_catalog_product->getProductSpecials($data);
            }
            elseif ($rss == "bestseller") {
                $data = array("sort"  => "pd.name", "order" => "ASC", "start" => 0, "limit" => $limit);
                $products = $this->model_catalog_product->getBestSellerProducts($limit);
            }

            $output = "<?xml version='1.0' encoding='UTF-8' ?>";
            $output .= "<rss version='2.0'>";
            $output .= "<channel>";
            $output .= "<title><![CDATA[" . $this->config->get("config_name") . " - $rss products]]></title>";
            $output .= "<description><![CDATA[" . $this->config->get("config_meta_description") . "]]></description>";
            $output .= "<link><![CDATA[" . HTTP_SERVER . "]]></link>";

            //Login Display Prices
            $config_customer_price = $this->config->get('config_customer_price');

            foreach ($products as $product) {
                $title = $product["name"];
                $link = $this->url->link("product/product", "product_id=" . $product["product_id"]);
                $price = ($this->isCustomerAllowedToViewPrice) ? $this->currency->format($this->tax->calculate($product["price"], $product["tax_class_id"], $this->config->get("config_tax"))) : false;
                $special = ((float)$product["special"] && $this->isCustomerAllowedToViewPrice) ? $this->currency->format($this->tax->calculate($product["special"], $product["tax_class_id"], $this->config->get("config_tax"))) : false;
                $image_url = $this->model_tool_image->resize($product["image"], $image_width, $image_height);
                $description = "";
                if ($price) $description .= ($special) ? "<p><strong><span style='color:red; text-decoration:line-through;'>$price</span>$special</strong></p>" : "<p><strong>$price</strong></p>";
                if ($image_url) $description .= "<p><a href='$link'><img src='$image_url' alt=''/></a></p>";
                if ($product["description"]) $description .= $product["description"];

                if ($rss != "special" || $special) {
                    $output .= "<item>";
                    $output .= "<title><![CDATA[" . html_entity_decode($title, ENT_QUOTES, "UTF-8") . "]]></title>";
                    $output .= "<link><![CDATA[" . html_entity_decode($link, ENT_QUOTES, "UTF-8") . "]]></link>";
                    $output .= "<description>" . $description . "</description>";
                    $output .= "<guid><![CDATA[" . html_entity_decode($link, ENT_QUOTES, "UTF-8") . "]]></guid>";
                    $output .= "<pubDate>" . date("D, d M Y H:i:s O", strtotime($product["date_added"])) . "</pubDate>";
                    $output .= "</item>";
                }
            }
            $output .= "</channel>";
            $output .= "</rss>";

            header("Content-Type: application/rss+xml");
            echo "$output";
            die();
        }

        //$this->data['title'] = $this->document->getTitle();

        if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
            $server = $this->config->get('config_ssl');
        } else {
            $server = $this->config->get('config_url');
        }

        $this->data['base_url'] = $server;
        //$this->data['description'] = $this->document->getDescription();
        //$this->data['keywords'] = $this->document->getKeywords();
        //$this->data['links'] = $this->document->getLinks();
        // var_dump($this->config->get('config_google_analytics'));die();
        $this->data['google_analytics'] = html_entity_decode($this->config->get('config_google_analytics'), ENT_QUOTES, 'UTF-8');
        $this->data['google_analytics'].=$this->data['integrations'];
        $this->data['body_scripts'] = html_entity_decode($this->config->get('config_body_scripts'), ENT_QUOTES, 'UTF-8');

        if( \Extension::isInstalled('widebot') && $this->config->get('widebot')['status'] == 1)
            $this->data['body_scripts'] .= html_entity_decode($this->config->get('widebot')['script'], ENT_QUOTES, 'UTF-8');

        if( \Extension::isInstalled('sendpulse') && $this->config->get('sendpulse')['status'] == 1)
            $this->data['body_scripts'] .= html_entity_decode($this->config->get('sendpulse')['script'], ENT_QUOTES, 'UTF-8');

        $this->data['store_name'] = $this->config->get('config_name');
        $this->data['cart_content'] = $this->getChild('common/cart');
        $this->data['isdraft'] = $this->request->get['isdraft'];
        $this->data['logged'] = $this->customer->isLogged();
        $this->data['username'] = $this->customer->getFirstName();
        $this->data['wishlist_count'] = (isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0);

        if( \Extension::isInstalled('auctions') && $this->config->get('auctions_status') == 1 ){
            $this->data['auctions_app_installed'] = true;
            $this->language->load_json('module/auctions', true);
        }

        if( \Extension::isInstalled('form_builder') && $this->config->get('form_builder')['status'] == 1 ){
            $this->data['form_builder_app_installed'] = true;
            $this->data['form_builder_title'] = $this->config->get('form_builder')[$this->config->get('config_language_id')]['form_title'];
            $this->language->load_json('module/form_builder', true);
        }
        //Using brand_id in home url as a parameter to be used to display it's products
        if (isset($this->request->get['brand_id'])) {
            $this->data['brand_id'] = $this->request->get['brand_id'];
        }

        //Landing page logic, if the URL contains "store" then avoid showing landing page else, check landing page parameter
        $this->data['landing_page'] = 0;
        if (strpos($_SERVER['REQUEST_URI'], "store") == 0){
            if (isset($this->request->get['landing_page']) || ($this->config->get('landing_page_is_default') == 1)) {
                $this->data['landing_page'] = isset($this->request->get['landing_page']) ? $this->request->get['landing_page'] : 1;
            }
        }

//        if (!\Extension::isInstalled('popupwindow'))
//        {
//            $this->data['body_scripts'] .= '
//                <script>
//                    loadCustomFancybox(
//                        "expandish/view/javascript/jquery/fancybox/jquery.fancybox.min.js",
//                        "expandish/view/javascript/jquery/fancybox/jquery.fancybox.min.css"
//                    );
//                </script>
//            ';
//        }

        /*$trackingCode = $this->config->get('matomo_analytics');
        if (isset($trackingCode['status']) && $trackingCode['status'] == 1) {
            $this->data['google_analytics'] .= html_entity_decode(
                $trackingCode['tracking_code'],
                ENT_QUOTES,
                'UTF-8'
            );
        }*/

        $this->load->model('setting/setting');

        $this->data["seller_ads_settings"] = $this->model_setting_setting->getSetting('seller_ads');
        if ($this->data["seller_ads_settings"]['seller_ads_app_status']) {

            // get all active ads
            $this->load->model('module/seller_ads');
            $this->data['sellers_ads'] = $this->model_module_seller_ads->getSellersAds();
            $this->data['sellers_ads'] = array_map(function($ad) {
                $ad['title'] = unserialize($ad['title']);
                $ad['image'] = \Filesystem::getUrl($ad['image']);
                return $ad;
            }, $this->data['sellers_ads']);
        }

        // Start SendStrap App
        $sendstrap = $this->model_setting_setting->getSetting('sendstrap');
        $sendstrap_token = $this->config->get('sendstrap_token');
        $sendstrap_id = $this->config->get('sendstrap_id');

        if ($sendstrap['status'] == 1) {
            $this->data['body_scripts'] .= '
            <script src="https://app.sendstrap.com/scripts/js/social_button.js?id='.$sendstrap_id.'&key='.$sendstrap_token.'">
            </script>
           ';
        }
        // END SendStrap App

        // Start Trustrol App
        $trustrol = $this->model_setting_setting->getSetting('trustrol');
        $trustrol_pixel = $this->config->get('trustrol_pixel');

        if ($trustrol['status'] == 1) {
            $this->data['body_scripts'] .= '
            <script src="https://trustrol.com/pixel/'.$trustrol_pixel.'">
            </script>
           ';
        }
        // END Trustrol App

        $request_data=$this->request->get;
        $this->setFacebookPixelIntegration($request_data);
        $this->setTwitterPixelIntegration($request_data);
        $this->setSnapchatPixelIntegration($request_data);


        if ($this->config->get('config_icon')) {
            $this->data['store_icon'] = \Filesystem::getUrl('image/' . $this->config->get('config_icon'));
            // $server . 'image/' . STORECODE . '/' . $this->config->get('config_icon');
        } else {
            $this->data['store_icon'] = '';
        }

        if ($this->config->get('config_logo')) {
            // $this->data['store_logo'] = $server . 'image/' . STORECODE . '/' . $this->config->get('config_logo');
            $this->data['store_logo'] = \Filesystem::getUrl('image/' . $this->config->get('config_logo'));
        } else {
            $this->data['store_logo'] = '';
        }

        // Menu
        //$this->load->model('catalog/category');

        $this->load->model('catalog/product');

        /*$this->data['categories'] = array();

        $categories = $this->model_catalog_category->getCategories(0);

        foreach ($categories as $category) {
            if ($category['top']) {
                // Level 2
                $children_data = array();

                $children = $this->model_catalog_category->getCategories($category['category_id']);

                foreach ($children as $child) {
                    $children_data[] = array(
                        'name'  => $child['name'],
                        'category_id' => $child['category_id'],
                        'href'  => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id'])
                    );
                }

                // Level 1
                $this->data['categories'][] = array(
                    'name'     => $category['name'],
                    'image'    => $category['image'] ? $server . 'image/' . STORECODE . '/' . $category['image'] : '',
                    'children' => $children_data,
                    'category_id' => $category['category_id'],
                    'column'   => $category['column'] ? $category['column'] : 1,
                    'href'     => $this->url->link('product/category', 'path=' . $category['category_id'])
                );
            }
        }*/


        //Language Selector

        if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
            $connection = 'SSL';
        } else {
            $connection = 'NONSSL';
        }

        //$this->data['lang_action'] = $this->url->link('common/home', '', $connection);

        $this->data['language_code'] = $this->session->data['language'];

        $this->load->model('localisation/language');
        $this->data['languages'] = array();
        $this->data['current_language'] = array();

        $results = $this->model_localisation_language->getLanguages();
        if(isset($_COOKIE['most_searched']))
            $this->data['most_searched'] = unserialize(base64_decode($_COOKIE['most_searched']));
        else
            $this->data['most_searched'] = '';

        foreach ($results as $result) {
            if ($result['status']) {
                $this->data['languages'][] = array(
                    'name'  => $result['name'],
                    'code'  => $result['code'],
                    'image' => $result['image']
                );

                if ($this->data['language_code'] == $result['code']) {
                    $this->data['current_language'] = array(
                        'name' => $result['name'],
                        'code' => $result['code'],
                        'image' => $result['image']
                    );
                }
            }
        }

        $route = '';
        if (!isset($this->request->get['route'])) {
            $this->data['redirect'] = $this->url->link('common/home');
            $route = 'common/home';
        } else {
            $data = $this->request->get;

            unset($data['_route_']);

            $route = $data['route'];

            unset($data['route']);

            $url = '';

            if ($data) {
                $url = '&' . urldecode(http_build_query($data, '', '&'));
            }

            $this->data['redirect'] = $this->url->link($route, $url, $connection);
        }



        //Currency Selector

        $this->data['ssl'] = $connection;

        $this->data['currency_code'] = $this->currency->getCode();

        $this->load->model('localisation/currency');

        $this->data['currencies'] = array();
        $this->data['current_currency'] = array();

        $results = [];
        if (
            !$this->dedicatedDomains->isActive() || (
                $this->dedicatedDomains->isActive() && $this->dedicatedDomains->options['changeCurrency']
            )
        ) {
            $results = $this->model_localisation_currency->getCurrencies();
        }

        foreach ($results as $result) {
            if ($result['status']) {
                $this->data['currencies'][] = array(
                    'title'        => $result['title'],
                    'code'         => $result['code'],
                    'symbol_left'  => $result['symbol_left'],
                    'symbol_right' => $result['symbol_right'],
                    'symbol'       => $result['symbol_left'] ? $result['symbol_left'] : $result['symbol_right']
                );

                if ($this->data['currency_code'] == $result['code']) {
                    $this->data['current_currency'] = array(
                        'title'        => $result['title'],
                        'code'         => $result['code'],
                        'symbol_left'  => $result['symbol_left'],
                        'symbol_right' => $result['symbol_right'],
                        'symbol'       => $result['symbol_left'] ? $result['symbol_left'] : $result['symbol_right']
                    );
                }
            }
        }

        ##############################Start: modules################################
        $this->load->model('extension/layout');
        $layout_id = $this->model_extension_layout->getLayout($route);
        if (!$layout_id) {
            $layout_id = $this->config->get('config_layout_id');
        }

        $extensions = $this->model_setting_extension->getExtensions('module');

        $module_data = array();

        foreach ($extensions as $extension) {
            if(!isset($module_data[$extension['code']])){
                $module_data[$extension['code']] = array();
                $module_data[$extension['code']]['enabled'] = false;
                $module_data[$extension['code']]['content'] = '';
            }

            if($extension['code'] == 'zopim_live_chat' || $extension['code'] == 'socialslides' || $extension['code'] == 'smartlook' || $extension['code'] == 'popupwindow') {
                $modules = $this->config->get($extension['code'] . '_module');

                if ($modules) {
                    foreach ($modules as $module) {
                        if ($module['layout_id'] == $layout_id && $module['status']) {
                            $module_data[$extension['code']]['enabled'] = true;
                            $module_data[$extension['code']]['content'] = $this->getChild('module/' . $extension['code'], $this->request->get);
                            break;
                        }
                    }
                }
            }

            if($extension['code'] == 'filter') {
                $modules = $this->config->get($extension['code'] . '_module');

                if ($modules) {
                    foreach ($modules as $module) {
                        if ($module['layout_id'] == $layout_id && $module['status']) {
                            $module_data[$extension['code']]['enabled'] = true;
                            $module_data[$extension['code']]['content'] = $this->getChild('module/' . $extension['code'], $this->request->get);
                            break;
                        }
                    }
                }
            }

            if($extension['code'] == 'mega_filter') {
                $modules = $this->config->get($extension['code'] . '_module');
                $idx = 0;
                $idxs = array();

                foreach( $modules as $k => $v ) {
                    $idxs[] = $k;
                }
                if ($modules) {
                    foreach ($modules as $module) {
                        if( is_array( $module ) ) {
                            if( ! isset( $module['layout_id'] ) )
                                $module['layout_id'] = 0;

                            if( ! isset( $module['position'] ) )
                                $module['position'] = '';

                            if( ! isset( $module['status'] ) )
                                $module['status'] = '0';

                            if( ! isset( $module['sort_order'] ) )
                                $module['sort_order'] = 0;

                            if( ! is_array( $module['layout_id'] ) )
                                $module['layout_id'] = array( $module['layout_id'] );

                            $module['_idx'] = $idxs[$idx++];
                        }
                        if (array_search($layout_id, $module['layout_id']) > -1  && $module['status']) {
                            $module_data[$extension['code']]['enabled'] = true;
                            $module_data[$extension['code']]['content'] = $this->getChild('module/' . $extension['code'], $module);
                            break;
                        }
                    }
                }
            }
        }

        $this->data['modules'] = $module_data;



        if(isset($this->session->data['order_id']) && $this->session->data['order_id']!=""){
            $this->session->data['criteo_order_id']=$this->session->data['order_id'];
        }

        $this->expandish->setPageModules($module_data);
        //$this->registry->set('core_data', $this->data);
        ##############################End: modules################################

        ##############################Start: Expandish regions################################
        $regions = $this->expandish->getRegions();

        $regions_data = array();

        /// is Slider
        $main_slider = false;

        foreach ($regions as $region) {
            $regions_data[$region['region_codename']] = array();
            $region_content = '';
            $region_sections = $this->expandish->getRegionSections($region['region_codename']);
            foreach ($region_sections as &$region_section) {
                //main slider
                if($region_section['section_codename'] == '1.revSlider'){
                    $main_slider = true;
                }

                if($region_section['section_codename'] == '01.MainSlider'){
                    $region_section['is_slider'] = $this->expandish->getSectionFieldValue($region_section['section_codename']);
                }

                $region_content .= $this->getChild('common/section', $region_section);

            }
            $regions_data[$region['region_codename']]['content'] = $region_content;
            $regions_data[$region['region_codename']]['sections_count'] = count($region_sections);
        }
        //print_r($regions_data);
        // exit();

        $this->data['regions_data'] = $regions_data;
        $this->language->load_json('product/category');

        //$this->data['header_data'] = $this->expandish->getHeader();
        //$this->data['footer_data'] = $this->expandish->getFooter();
        //$route = $this->expandish->getRoute();
        $pageCodeName = $this->expandish->getPageCodeName();

        $this->data['is_home_page'] = ($pageCodeName == 'home');

        if($pageCodeName == 'home'){

            $this->data['category_droplist'] = false;

            $this->data['pc_enabled'] = false;

            ////////////// Category Droplist Filter
            $this->load->model('module/category_droplist');
            if($this->model_module_category_droplist->isActive()){
                $langCode = $this->config->get('config_language');

                $this->document->addScript('expandish/view/javascript/common/category_droplist.js');

                //Show Category Droplist filter id Slider not Enabled
                if(!$main_slider){
                    $droplist_data = $this->config->get('category_droplist');
                    $this->data['category_droplist']['status'] = true;
                    $this->data['category_droplist']['levels'] = $droplist_data['levels'];
                    $this->data['category_droplist']['lables'] = $droplist_data['lables'];
                    $this->data['category_droplist']['title']  = $droplist_data['title'][$langCode];
                    $this->data['category_droplist']['button']  = $droplist_data['button'][$langCode];
                    $this->data['category_droplist']['form_url'] = $this->url->link('product/category');
                    $this->data['category_droplist']['lang_code'] = $langCode;

                    $this->data['category_droplist']['cols'] = round(12 / $droplist_data['levels']);
                }
            }
            ///////////////////
            $this->load->model('catalog/category');
//            $categories = $this->model_catalog_category->getCategories(0);
            $categories = $this->model_catalog_category->getCategoriesOrdered();
            $this->data['categories'] = $categories;

//            var_dump("<pre>",$categories);
//            die();


        }




        ##############################End: Expandish regions################################

        ////////////// Hide Layouts in case of view product in iframe
        $this->data['hide_layouts'] = false;
        if(isset($this->request->get['iframe'])){
            $this->data['hide_layouts'] = true;
        }
        ///////////////////

        // Whos Online
        if ($this->config->get('config_customer_online')) {
            $this->load->model('tool/online');

            if (isset($this->request->server['REMOTE_ADDR'])) {
                $ip = $this->request->server['REMOTE_ADDR'];
            } else {
                $ip = '';
            }

            if (isset($this->request->server['HTTP_HOST']) && isset($this->request->server['REQUEST_URI'])) {
                $url = 'http://' . $this->request->server['HTTP_HOST'] . $this->request->server['REQUEST_URI'];
            } else {
                $url = '';
            }

            if (isset($this->request->server['HTTP_REFERER'])) {
                $referer = $this->request->server['HTTP_REFERER'];
            } else {
                $referer = '';
            }

            $this->model_tool_online->whosonline($ip, $this->customer->getId(), $url, $referer);
        }

        if (\Extension::isInstalled('your_service')) {
            $yourServiceConfig = $this->config->get('ys') ?? [];
            if ($yourServiceConfig['status'] == 1) {
                $yourServiceConfig['ms_notifications'] = 0;
                if ($yourServiceConfig['ms_view_requests'] == 1)
                {
                    if ($this->customer->isLogged())
                    {
                        if (\Extension::isInstalled('multiseller') && $this->MsLoader->MsSeller->isCustomerSeller( $this->customer->getId() ))
                        {
                            $yourServiceConfig['ms_notifications'] = 1;
                        }
                    }
                }
                $ysSettingsEncoded = json_encode($yourServiceConfig,JSON_HEX_APOS);
                $this->data['body_scripts'] .= '
                    <script>
                        window.ys_settings = \''.$ysSettingsEncoded.'\';
                    </script>';
                $this->document->addScript('expandish/view/javascript/your_service/your_service.js');
            }
        }

        if(isset($this->request->get['ismobile'])) {
            $this->session->data['ismobile'] = $this->request->get['ismobile'];
        }

        if($this->request->get['ismobile'] == "1" || $this->session->data['ismobile'] == "1") {
            $this->template = 'default/template/mobile/home.expand';
        }
        elseif(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/common/home.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/common/home.expand';
        }
        else {
            $this->template = $this->config->get('config_template') . '/template/common/home.expand';
        }

        $this->data['is_enabled_new_login'] = defined('LOGIN_MODE') && LOGIN_MODE === 'identity' && $this->identity->isStoreOnWhiteList();
        if ($this->data['is_enabled_new_login']) {
            $this->initializeLogin();
            $this->forceUpdateCurrentTemplate();
        }

        if(USES_TWIG_EXTENDS){
            $args['data'] = $this->data;
        }
        $this->data['page_content'] = $pageCodeName == 'general' ? $this->getChild($args['route'], $args) : '';
        if(USES_TWIG_EXTENDS && $pageCodeName == 'general'){ // return the rendered twig template if the theme structure uses extend concept
            return;
        }

        if (\Extension::isInstalled('top_banner_ad') && $this->config->get('top_banner_ad_status') == 1){
            $timing_model = $this->config->get('top_banner_timing_model_type');

            if( $timing_model == 'fixed' ){
                $end_date = $this->config->get('top_banner_fixed_timing_end_date');
                $date_now = date("Y-m-d H:i");

                if ( $end_date > $date_now ){
                    $this->data['top_banner_ad_status'] = true;
                    $this->data['top_banner_ad_model']  = 'fixed';

                    $end_date = str_replace("/",'-',$end_date);
                    $now = $this->config->get('config_timezone') ? new DateTime("now", new DateTimeZone($this->config->get('config_timezone'))) : new DateTime("now");
                    $end_date = new DateTime($end_date);
                    $this->data['top_banner_ad_remaining_seconds'] = $end_date->getTimestamp() - $now->getTimestamp();
                }
            }elseif( $timing_model == 'dynamic'){
                $this->load->model('module/top_banner_ad');
                $slots = $this->model_module_top_banner_ad->getDynamicOrderedTimeSlots();

                if( !empty($slots) ){
                    $this->data['top_banner_ad_status'] = true;
                    $this->data['top_banner_ad_model']  = 'dynamic';
                    $this->data['top_banner_ad_dynamic_slots'] = $slots;
                }
            }else{
                $this->data['top_banner_ad_status'] = false;
            }

        }

        $this->response->setOutput($this->render_ecwig());
    } //end index

    public function setFacebookPixelIntegration($data){

	    $this->load->model('setting/setting');

	    $facebook_pixel_settings=$this->model_setting_setting->getSetting('integrations');

	    $open_script_str="<script>";
	    $close_script_str="</script>";

	    switch ($data['route']){
            case 'product/product':
                // Check if admin enabled this action
                if(in_array('View Content', $facebook_pixel_settings['mn_integ_fbp_action'])){
                    if (isset($data['product_id'])) {

                        $this->load->model('catalog/product');
                        $product_info = $this->model_catalog_product->getProduct($data['product_id']);


                        $catalog_id = "0";
                        if (\Extension::isInstalled('facebook_import')) {
                            $catalog_id = ($this->model_catalog_product->getFacebookCatalogId()['catalog_id']) ?? "0";
                        }

                        $this->data['body_scripts'] .= $open_script_str." fbq('track', 'ViewContent', {'content_type':'product','content_ids':['".$product_info['product_id']."'],'image_link':'". HTTP_IMAGE . $product_info['image']."','value':'". number_format(($product_info['special'] ?? $product_info['price']), 2) . "','currency':'" .  $this->currency->getCode() . "','product_catalog_id':'". $catalog_id ."'}); ";
                        if(in_array('Generate Lead', $facebook_pixel_settings['mn_integ_fbp_action'])){
                            if(isset($product_info['discount']) || isset($product_info['special']))
                            {
                                $this->data['body_scripts'] .= " fbq('track', 'Lead'); ";
                            }

                        }
                        $this->data['body_scripts'] .= $close_script_str;
                    }
                }

                break;
            case 'product/search':
                if(in_array('Search', $facebook_pixel_settings['mn_integ_fbp_action'])){
                    if (isset($data['search'])) {
                        $this->data['body_scripts'] .= <<<HTML
<script>
  fbq('track', 'Search', {
    search_string: '{$data['search']}'
  });
</script>
HTML;
                    }
                }

                break;
            case 'checkout/cart':
                if(in_array('Add to Basket', $facebook_pixel_settings['mn_integ_fbp_action'])){
                    if(count($this->session->data['cart']) > 0) {
                        $this->load->model('catalog/product');
                        $catalog_id = "0";
                        if (\Extension::isInstalled('facebook_import')) {
                            $catalog_id = ($this->model_catalog_product->getFacebookCatalogId()['catalog_id']) ?? "0";
                        }

                        $productsInCart = $this->cart->getProducts();

                        $total = 0;
                        $quantity = 0;
                        $content_ids = array();
                        $productsToSendToFacebookTrack = [];
                        foreach ($productsInCart as $singleProduct) {
                            $productsToSendToFacebookTrack[] = ['name' => $singleProduct['name'], 'quantity' => $singleProduct['quantity'],'price' => number_format($singleProduct['total'], 2),'currency' => $this->currency->getCode()];
                            $total += $singleProduct['total'];
                            $quantity += $singleProduct['quantity'];
                            $content_ids[] = $singleProduct['product_id'];
                        }
                        $productsToSendToFacebookTrack = json_encode($productsToSendToFacebookTrack);
                        $content_ids = json_encode($content_ids);
                        $this->data['body_scripts'] .= <<<HTML
<script>
    fbq('track', 'AddToCart', {
        "value" : $total,
        "num_items": $quantity ,
        "content_ids" : $content_ids,
        "content_type":'product',
        "currency" : "{$this->currency->getCode()}",
        "product_catalog_id": $catalog_id,
        "Products": $productsToSendToFacebookTrack
    });
</script>

HTML;
                    }
                }

                break;

             case 'account/wishlist':
                 if(in_array('Add to Wishlist', $facebook_pixel_settings['mn_integ_fbp_action'])){
                     if(count($this->session->data['wishlist']) > 0){
                         $this->load->model('catalog/product');
                         $catalog_id = "0";
                         if (\Extension::isInstalled('facebook_import')) {
                             $catalog_id = ($this->model_catalog_product->getFacebookCatalogId()['catalog_id']) ?? "0";
                         }
                         $quantity = count($this->session->data['wishlist']);
                         $content_ids = json_encode($this->session->data['wishlist']);

                         $this->data['body_scripts'] .= <<<HTML
<script>
  fbq('track', 'AddToWishlist', {
      "num_items": $quantity ,
      "content_ids" : $content_ids,
      "content_type":'product',
      "product_catalog_id": $catalog_id

  });
</script>

HTML;
                     }
                 }

                break;
            case 'checkout/checkout':
                if(in_array('Initiate Checkout', $facebook_pixel_settings['mn_integ_fbp_action'])){

                    $this->load->model('catalog/product');
                    $catalog_id = "0";
                    if (\Extension::isInstalled('facebook_import')) {
                        $catalog_id = ($this->model_catalog_product->getFacebookCatalogId()['catalog_id']) ?? "0";
                    }

                    $quantity = 0;
                    $content_ids = array();
                    foreach ($this->cart->getProducts() as $product){
                        $quantity += $product['quantity'];
                        $content_ids[] = $product['product_id'];
                    }
                    $content_ids = json_encode($content_ids);

                    if(count($this->session->data['cart']) > 0) {
                        $this->data['body_scripts'] .= <<<HTML
<script>
    fbq('track', 'InitiateCheckout', {
        "value":"{$this->cart->getTotal()}",
        "currency":"{$this->currency->getCode()}",
        "num_items":"$quantity" ,
        "content_ids" : $content_ids,
       "content_type":'product',
       "product_catalog_id": $catalog_id
    });
</script>

HTML;
                    }
                }

                break;
            case 'checkout/success':

                    if(isset($this->session->data['order_id'])) {

                        $order_id=$this->session->data['order_id'];

                        $this->load->model('account/order');

                        $order_info = $this->model_account_order->getOrder($order_id);

                        $currency=$order_info['currency_code'];

                        $products = $this->model_account_order->getOrderProducts($order_id);

                        $total=0;
                        $quantity = 0;
                        $content_ids = array();
                        foreach ($products as $product){
                            $total+=$product['total'];
                            $quantity += $product['quantity'];
                            $content_ids[] = $product['product_id'];
                            $productsToSendToFacebookTrack[] = ['id' => $product['product_id'],'name' => $product['name'], 'quantity' => $product['quantity'],'item_price' => number_format($product['price'], 2)];
                        }
                        $content_ids = json_encode($content_ids);
                        $productsToSendToFacebookTrack = json_encode($productsToSendToFacebookTrack);
                        $this->load->model('catalog/product');
                        $catalog_id = "0";
                        if (\Extension::isInstalled('facebook_import')) {
                            $catalog_id = ($this->model_catalog_product->getFacebookCatalogId()['catalog_id']) ?? "0";
                        }

                        $this->data['body_scripts'].= <<<HTML
                    <script>
HTML;
                        if(in_array('Add Payment Info', $facebook_pixel_settings['mn_integ_fbp_action'])){
                            $this->data['body_scripts'].= <<<HTML
                    fbq('track', 'AddPaymentInfo');
HTML;
                        }
                        if(in_array('Purchase', $facebook_pixel_settings['mn_integ_fbp_action'])) {
                            $this->data['body_scripts'] .= <<<HTML
                    fbq('track', 'Purchase', {
                    value: {$total},
                    currency: '{$currency}',
                    "num_items" : {$quantity} ,
                    "content_ids" : {$content_ids},
                    "contents" : $productsToSendToFacebookTrack,
                    "content_type":'product',
                    "product_catalog_id": {$catalog_id}
                    });
HTML;
                        }
                        $this->data['body_scripts'].= <<<HTML
                    </script>
HTML;
                    }

            break;
            case 'account/success':
                if(in_array('Complete Registration', $facebook_pixel_settings['mn_integ_fbp_action'])){
                    $this->data['body_scripts'] .= <<<HTML
<script>
    fbq('track', 'CompleteRegistration');
</script>

HTML;
                }
                break;

        }
    }


    /**
    * Twitter Pixel Integration
    */
    public function setTwitterPixelIntegration($data){
        $twitter_pixel_status = $this->config->get('twitter_pixel_status');
        $twitter_pixel_id     = $this->config->get('twitter_pixel_id');
        $twitter_pixel_selected_actions = $this->config->get('twitter_pixel_selected_actions');

        switch ($data['route']){

						//CONTENT_VIEW
            case 'product/product':
                // Check if admin enabled View Content event type
                if(in_array('View Content', $twitter_pixel_selected_actions)){
                    if (isset($data['product_id'])) {

                        $this->load->model('catalog/product');
                        $product_info = $this->model_catalog_product->getProduct($data['product_id']);

												$this->data['body_scripts'] .= "
													<script>
															twq('track', 'ViewContent', {
															  content_ids: ['{$data["product_id"]}'], // Required
															  content_type: 'product', // Required
															  content_name: '{$product_info["name"]}'
															});
													</script>";
                    }
                }
              break;


						//PURCHASE
            case 'checkout/success':
                if(in_array('Purchase', $twitter_pixel_selected_actions) && isset($this->session->data['order_id'])) {

                        $order_id = $this->session->data['order_id'];

                        $this->load->model('account/order');
                        $order_info = $this->model_account_order->getOrder($order_id);

                        $currency = $order_info['currency_code'];
                        $products = $this->model_account_order->getOrderProducts($order_id);
                        $total    = 0;
                        $quantity = 0;
												$content_ids = [];
												$content_name = '';

                        foreach ($products as $product){
                            $total        += $product['total'];
                            $quantity     += $product['quantity'];
														$content_ids[] = $product['product_id'];
														$content_name .= $product['name'] . ' , ';
                        }

												$content_ids = json_encode($content_ids);

												$this->data['body_scripts'].= "
		                    <script>
																twq('track','Purchase', {
																    'value'       : '{$total}',
																    'currency'    : '{$currency}',
																    'num_items'   : '{$quantity}',
																		'content_ids' :  $content_ids, // Required
																	  'content_type': 'product', 				// Required
																	  'content_name': '{$content_name}',
																		'order_id'    : '{$order_id}',
																	  'status'      : 'completed!'
																});
		                    </script>";

					 	    }
              break;


						//SIGN_UP
            case 'account/success':
                if(in_array('Sign Up', $twitter_pixel_selected_actions)){
                    $this->data['body_scripts'] .= "<script>twq('track', 'SignUp');</script>";
                }
              break;


						//ADD_TO_CART
						case 'checkout/cart':
								if(in_array('Add to Basket', $twitter_pixel_selected_actions) && count($this->session->data['cart']) > 0){
												$products = $this->cart->getProducts();
												$content_ids = [];
												foreach ($products as $product) {
													 $content_ids[] = $product['product_id'];
												}
												$content_ids = json_encode($content_ids);
												$this->data['body_scripts'] .= "
												<script>
												twq('track', 'AddToCart', {
													'content_ids' : $content_ids,
													'content_type':'product'
												});
												</script>";
								}
								break;


						//ADD_TO_WISHLIST
					  case 'account/wishlist':
								 if(in_array('Add to Wishlist', $twitter_pixel_selected_actions) && count($this->session->data['wishlist']) > 0){
										 $this->load->model('catalog/product');
										 $catalog_id = "0";

										 $quantity = count($this->session->data['wishlist']);
										 $content_ids = json_encode($this->session->data['wishlist']);

										 $this->data['body_scripts'] .= "
											<script>
											twq('track', 'AddToWishlist', {
											  'content_ids' : $content_ids,
											});
											</script>";
								 }
						  break;


						//CHECKOUT_INITIATED
						case 'checkout/checkout':
								if(in_array('Initiate Checkout', $twitter_pixel_selected_actions) && count($this->session->data['cart']) > 0){
										$content_name = "";
										$content_ids = [];
										foreach ($this->cart->getProducts() as $product){
												$content_name  .= $product['name'];
												$content_ids[]  = $product['product_id'];
										}
										$content_ids = json_encode($content_ids);

										$this->data['body_scripts'] .= "
										<script>
										twq('track', 'InitiateCheckout', {
												'content_ids'     : $content_ids,
												'content_category': 'product',
												'content_name'    : '{$content_name}'
										});
										</script>";

								}
						  break;


						//SEARCH
					  case 'product/search':
	            if(in_array('Search', $twitter_pixel_selected_actions) && isset($data['search']) ){
	                $this->data['body_scripts'] .= "<script>twq('track', 'Search');</script>";
	            }
		          break;
				}
    }



    public function setSnapchatPixelIntegration($data){

        $this->load->model('setting/setting');

        $snapchat_pixel_settings=$this->model_setting_setting->getSetting('integrations');

        $open_script_str="<script>";
        $close_script_str="</script>";
        switch ($data['route']){
            case 'product/product':
                // Check if admin enabled this action
                if(in_array('View Content', $snapchat_pixel_settings['mn_integ_snp_action'])){
                    if (isset($data['product_id'])) {

                        $this->load->model('catalog/product');
                        $product_info = $this->model_catalog_product->getProduct($data['product_id']);

                        $this->data['body_scripts'] .= $open_script_str." snaptr('track', 'ViewContent'); ";
                        if(in_array('Generate Lead', $snapchat_pixel_settings['mn_integ_snp_action'])){
                            if(isset($product_info['discount']) || isset($product_info['special']))
                            {
                                $this->data['body_scripts'] .= "snaptr('track','VIEW_CONTENT');";
                            }

                        }
                        $this->data['body_scripts'] .= $close_script_str;
                    }
                }

                break;

            case 'checkout/cart':
                if(in_array('Add to Basket', $snapchat_pixel_settings['mn_integ_snp_action'])){
                    if(count($this->session->data['cart']) > 0) {
                        $this->data['body_scripts'] .= <<<HTML
                        <script>
                            snaptr('track','ADD_CART');
                        </script>
HTML;
                    }
                }

                break;
            case 'checkout/success':
                if (isset($this->session->data['criteo_order_id'])) {

                    $order_id = $this->session->data['criteo_order_id'];

                    $this->load->model('account/order');

                    $order_info = $this->model_account_order->getOrder($order_id);

                    $currency = $order_info['currency_code'];

                    $products = $this->model_account_order->getOrderProducts($order_id);

                    $total = 0;
                    foreach ($products as $product) {
                        $total += $product['total'];
                    }
                    $this->data['body_scripts'] .= <<<HTML
                    <script>
HTML;
                    if (in_array('Purchase', $snapchat_pixel_settings['mn_integ_snp_action'])) {
                        $this->data['body_scripts'] .= <<<HTML
                            snaptr('track', 'PURCHASE', {
                            'currency': '{$currency}',
                            'price': {$total},
                            'transaction_id' : $order_id
                            });
HTML;
                    }
                    $this->data['body_scripts'] .= <<<HTML
                    </script>
HTML;
                }

                break;
        }
    }

    public function setGoogleAnalyticsIntegration($data)
    {

        $this->load->model('setting/setting');

        $google_analytics_settings=$this->model_setting_setting->getSetting('integrations');

        $google_commands = array();

        switch ($data['route']) {
            case 'product/product':
                if(in_array('View Content', $google_analytics_settings['mn_integ_go_analytics_action'])) {
                    if (isset($data['product_id'])) {
                        $this->load->model('catalog/product');
                        $product = $this->model_catalog_product->getProduct($data['product_id']);

                        $this->load->model('catalog/category');
                        $this->load->model('catalog/manufacturer');
                        $categories = $this->model_catalog_product->getCategories($product['product_id']);

                        $product_categories = array();

                        foreach ($categories as $category_id) {
                            $categoryInfo = $this->model_catalog_category->getCategory($category_id['category_id']);

                            if ($categoryInfo) {
                                $product_categories[] = $categoryInfo['path'] ? $categoryInfo['path'] . ' &gt; ' : '' . $categoryInfo['name'];
                            }
                        }

                        $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($product['manufacturer_id']);
                        $manufacturer = $manufacturer_info['name'];
                        $google_commands[]= <<<HTML
                    ga('ec:addProduct', {               // Provide product details in a productFieldObject.
  'id': '{$product["product_id"]}',                   // Product ID (string).
  'name': '{$product["name"]}', // Product name (string).
  'brand': '{$manufacturer}',
  'category': '{$product_categories[0]}',
  'position': 1
});
ga('ec:setAction', 'detail');
ga('send', 'event', 'detail view', 'click', 'Results');
HTML;
                    }

                }
                break;
            case 'product/search':
                if(in_array('Search', $google_analytics_settings['mn_integ_go_analytics_action'])) {
                    if (isset($data['search'])) {
                        // $product = $this->model_catalog_product->getProduct($data['product_id']);
                        $this->load->model('catalog/product');
                        $this->load->model('catalog/category');
                        $this->load->model('catalog/manufacturer');
                        $filters['filter_name']=$data['search'];
                        $filters['limit'] = 50;
                        $filters['start'] = 0;
                        $results = $this->model_catalog_product->getProducts($filters);
                        $position=1;
                        foreach ($results as $product) {

                            $categories = $this->model_catalog_product->getCategories($product['product_id']);

                            $product_categories = array();

                            foreach ($categories as $category_id) {
                                $categoryInfo = $this->model_catalog_category->getCategory($category_id['category_id']);

                                if ($categoryInfo) {
                                    $product_categories[] = $categoryInfo['path'] ? $categoryInfo['path'] . ' &gt; ' : '' . $categoryInfo['name'];
                                }
                            }

                            $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($product['manufacturer_id']);
                            $manufacturer = $manufacturer_info['name'];
                            $google_commands[]= <<<HTML
                    ga('ec:addImpression', {               // Provide product details in a productFieldObject.
  'id': '{$product['product_id']}',                   // Product ID (string).
  'name': '{$product["name"]}', // Product name (string).
  'brand': '{$manufacturer}',
  'category': '{$product_categories[0]}',
  'list': 'Search Results',
  'position': {$position}
});
HTML;
                            $position++;
                        }
                        $google_commands[]="ga('send', 'pageview'); ";

                    }

                }
                break;
            case 'checkout/cart':
                if(in_array('Add to Basket', $google_analytics_settings['mn_integ_go_analytics_action'])) {
                    if (count($this->cart->getProducts()) > 0) {

                        $google_commands= $this->getProductInfoFromCart();
                        $google_commands[]= <<<HTML
ga('ec:setAction', 'add');
ga('send', 'event', 'Add to cart', 'click', 'add to cart');     // Send data using an event.
HTML;
                    }
                }

                break;
            case 'checkout/checkout':

                if(in_array('Initiate Checkout', $google_analytics_settings['mn_integ_go_analytics_action'])) {

                    if (count($this->cart->getProducts()) > 0) {
                        $google_commands= $this->getProductInfoFromCart();
                        $google_commands[]=<<<HTML
ga('send', 'pageview');
HTML;
                    }
                }
                break;
            case 'checkout/success':

                if(in_array('Purchase', $google_analytics_settings['mn_integ_go_analytics_action'])) {
                    if (isset($this->session->data['order_id'])) {

                        $google_commands[]=<<<HTML
ga('set', 'currencyCode', '{$this->config->get('config_currency')}');
HTML;

                        $this->load->model('catalog/product');
                        $this->load->model('catalog/manufacturer');
                        $this->load->model('catalog/category');

                        $order_id=$this->session->data['order_id'];

                        $this->load->model('account/order');

                        $order_info = $this->model_account_order->getOrder($order_id);

                        $products = $this->model_account_order->getOrderProducts($order_id);
                        $total=0;
                        foreach ($products as $product){
                            $categories = $this->model_catalog_product->getCategories($product['product_id']);

                            $product_categories = array();

                            foreach ($categories as $category_id) {
                                $categoryInfo = $this->model_catalog_category->getCategory($category_id['category_id']);

                                if ($categoryInfo) {
                                    $product_categories[] = $categoryInfo['path'] ? $categoryInfo['path'] . ' &gt; ' : '' . $categoryInfo['name'];
                                }
                            }

                            $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($product['manufacturer_id']);
                            $manufacturer = $manufacturer_info['name'];
                            if ($this->isCustomerAllowedToViewPrice) {
                                $price=$this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'));
                            } else {
                                $price = false;
                            }
                            $total+=$product['total'];
                            $google_commands[]= <<<HTML
ga('ec:addProduct', {               // Provide product details in a productFieldObject.
  'id': '{$product['product_id']}',                   // Product ID (string).
  'name': '{$product["name"]}', // Product name (string).
  'brand': '{$manufacturer}',
  'category': '{$product_categories[0]}',
  'price': {$price},
  'quantity': {$product['quantity']}
});
HTML;
                        }
                        if(count($products) > 0){
                            $shipping_cost=$this->session->data['shipping_method']['cost']? : 0;
                            $coupon=$this->session->data['coupon'];
                            $google_commands[] = <<<HTML
ga('ec:setAction', 'purchase', {
    'id': '{$order_info['order_id']}',
    'affiliation': '{$order_info['affiliate_firstname']} - {$order_info['affiliate_lastname']}',
    'revenue': {$total},
    'shipping': {$shipping_cost},
    'coupon': '{$coupon}'    // User added a coupon at checkout.
});
ga('send', 'pageview');
HTML;
                        }

                    }

                }

                break;
        }
        return $google_commands;
    }

    public function getProductInfoFromCart(){

        $commands=array();
	    $commands[]=<<<HTML
ga('set', 'currencyCode', '{$this->config->get('config_currency')}');
HTML;

        $cart_details = $this->cart->getProducts();

        $this->load->model('catalog/product');
        $this->load->model('catalog/manufacturer');
        $this->load->model('catalog/category');


        foreach ($cart_details as $product) {

            # code...

            $product_id = $product['product_id'];
            $qty = $product['quantity'];

                    // echo "ASD";
            $product = $this->model_catalog_product->getProduct($product_id);
            $categories = $this->model_catalog_product->getCategories($product['product_id']);

            $product_categories = array();

            foreach ($categories as $category_id) {
                $categoryInfo = $this->model_catalog_category->getCategory($category_id['category_id']);

                if ($categoryInfo) {
                    $product_categories[] = $categoryInfo['path'] ? $categoryInfo['path'] . ' &gt; ' : '' . $categoryInfo['name'];
                }
            }

            $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($product['manufacturer_id']);
            $manufacturer = $manufacturer_info['name'];
            if ($this->isCustomerAllowedToViewPrice) {
                $price = $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'));
            } else {
                $price = false;
            }
        $commands[]= <<<HTML
                    ga('ec:addProduct', {               // Provide product details in a productFieldObject.
  'id': '{$product_id}',                   // Product ID (string).
  'name': '{$product["name"]}', // Product name (string).
  'brand': '{$manufacturer}',
  'category': '{$product_categories[0]}',
  'price': {$price},
  'quantity': {$qty}
});
HTML;

        }
        return $commands;
    }

    /**
     * This method called in every request to add login js plugin scripts
     * and sync the logged in customer profile data
     * based on expand_updated_at column in customer table
     *
     * @return false
     */
    public function initializeLogin()
    {
        $this->load->model('localisation/country');
        $this->load->model("module/google_map");

        $storeCode = STORECODE;

        $customer = json_encode($this->customer->isLogged() ? ['id' => $this->customer->getExpandId(), 'logged_in' => true] : ['logged_in' => false]);
        $lang = $this->config->get('config_language') ? : 'en';
        $countryId = $this->session->data['shipping_country_id'] ?? $this->config->get('config_country_id');
        $countries = json_encode($this->model_localisation_country->getCountries());

		//$whatsAppAgree = (int)(\Extension::isInstalled('whatsapp') && $this->config->get('whatsapp_config_customer_allow_receive_messages')) ? 1 : 0;
        $whatsAppAgree = true; //this option currently removed
		$enableMultiseller = (int)\Extension::isInstalled('multiseller');
        $loginWithPhone = $this->identity->isLoginByPhone();

        $googleMap = $this->model_module_google_map->getSettings();

        $loginSelectors = json_encode([
            'customer' => [
                'login' => [
                    // A list of links that the library will replace with the login pop-up modal
                    // When the user presses one of them
                    'a[href*="' . $this->url->link('account/account') . '"]',
                    'a[href*="' . $this->url->link('account/login') . '"]',
                    'a[href*="' . $this->url->link('account/register') . '"]',
                    'a[href*="' . $this->url->link('account/wishlist') . '"]',
                ],
            ],
            'seller' => [
                'login' => [
                    'a[href*="' . $this->url->link('seller/register-seller') . '"]',
                ]
            ],
            'checkout' => [
                'login' => [
                    '#option_login_popup_trigger_wrap [name="account"]#register',
                    '#option_login_popup_trigger_wrap #option_login_popup_trigger'
                ],
            ],
        ]);

        $storeName = $this->config->get('config_name');

        // social login app
        $this->load->model('setting/extension');
        $extensions = $this->model_setting_extension->getExtensions('module');
        $socialLogin = ['status' => false];
        foreach ($extensions as $extension) {
            if ($extension['code'] == 'd_social_login') {
                $settings = $this->config->get('d_social_login_settings');
                if ($settings) {
                    if ($settings['status']) {
                        $socialLogin['status'] = true;
                        $socialLogin['content'] = $this->getChild('module/' . 'd_social_login');
                    }
                }
                break;
            }
        }
        $socialLogin = json_encode($socialLogin);
        $customerAccountFields = json_encode($this->config->get('config_customer_fields'));
        $isGetRequest = !($this->request->server['REQUEST_METHOD'] == 'POST');
        $libraryStatus = (defined('LOGIN_MODE') && LOGIN_MODE === 'identity' && $this->identity->isStoreOnWhiteList()) ? 'on' : 'off';
        $this->document->addInlineScript(function () use ($storeName, $lang, $storeCode, $countryId, $whatsAppAgree, $enableMultiseller, $loginWithPhone, $customer, $googleMap, $loginSelectors, $socialLogin, $countries, $customerAccountFields, $isGetRequest, $libraryStatus) {
            $noCache = "v1"; // bin2hex(random_bytes(4));
            $return = "<link rel=\"stylesheet\" type=\"text/css\" href=\"/expandish/view/javascript/loginjs/dist/loginjs.css?nocache=$noCache\"/>";

            if ($isGetRequest && (int)$googleMap['status'] === 1) {
                $googleMapApiKey = $googleMap['api_key'];
                $return .= "<script type=\"text/javascript\" id=\"google-maps-sdk\" src=\"https://maps.googleapis.com/maps/api/js?key=$googleMapApiKey&libraries=places&language=$lang\"></script>";
            }

            $googleMap = json_encode($googleMap);

            $return .= "<script type=\"text/javascript\" defer src=\"/expandish/view/javascript/loginjs/dist/loginjs.js?nocache=$noCache\"></script><script id=\"loginjs-plugin-$noCache\">window.addEventListener('DOMContentLoaded', (event) => {window.Loginjs !== undefined && (window.login = new Loginjs({storeName: '$storeName', lang: '$lang', storeCode: '$storeCode', countryId: '$countryId', whatsAppAgree: $whatsAppAgree, enableMultiseller: $enableMultiseller, loginWithPhone: $loginWithPhone, customer: $customer, map: $googleMap, loginSelectors: $loginSelectors, socialLogin: $socialLogin, countries: $countries, customerAccountFields: $customerAccountFields, libraryStatus: '$libraryStatus'}).render());});</script>";

            return $return;
        });

        if (defined('LOGIN_MODE') && LOGIN_MODE === 'identity' && $this->identity->isStoreOnWhiteList()) {
            $this->identity->syncCustomerProfile();
        }


        return false;
    }

    /**
     * Force update custom template
     * for the first time
     *
     * @return boolean
     */
    private function forceUpdateCurrentTemplate()
    {
        $configKey = 'force_update_custom_template';

        // Template already updated or not a custom template!
        if ((int) $this->config->get($configKey) || !IS_CUSTOM_TEMPLATE) return false;

        $currentTemplate = $this->config->get('config_template');

        // Initialize Models
        $settingModel  = $this->load->model('setting/setting', ['return' => true]);
        $templateModel = $this->load->model('templates/template', ['return' => true]);

        $setConfig = function ($value = 1) use ($settingModel, $configKey) {
            $settingModel->insertUpdateSetting($configKey, [$configKey => $value]);
            $this->config->set($configKey, $value);
            return true;
        };

        // Current template not a custom template
        if (!$template = $templateModel->getCustomTemplateByConfigName($currentTemplate)) {
            return $setConfig();
        }

        $log = new Log("update_templates_logs.txt");
        $log->write('Start force update ' . $currentTemplate . ' template from front.');

        $url = (isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1'))) ? 'https://' : 'http://';
        $url.= rtrim(DOMAINNAME) . '/api/v1/force_update_template';

        $curlClient = $this->registry->get('curl_client');
        $response = $curlClient->request('POST', $url, [], ['template_id' => $template['id'],]);
        $content = $response->getContent();

        if ($response->ok() && $content['status'] === 'OK') {
            $log->write('Template ' . $currentTemplate . ' has been successfully updated.');
            return $setConfig();
        }

        $log->write('Failed to update the '. $currentTemplate .' template: ' . json_encode($content));
        return $setConfig(0);
    }




    /**
    work around to set domain activated flag
    in case the domain is not our expand default store
    (storecode.expandcart.com)
     */
    public function completeActivationLinkedDomain()
    {
        if (IS_POS || !IS_NEXTGEN_FRONT)
            return false;

        if ($this->request->server['HTTP_HOST']) {
            $host = strtolower($this->request->server['HTTP_HOST']);
            $isLinked = $this->config->get('UserActivationDomainLinked');
            if ((!(strpos($host, '.expandcart.com') !== false)) && !$isLinked) {
                $this
                    ->model_setting_setting
                    ->insertUpdateSetting('config', ['UserActivationDomainLinked' => true]);

                $this
                    ->userActivation
                    ->processSoftActivation($this->userActivation::LINK_DOMAIN);
                $this->load->model('setting/amplitude');
                $this->model_setting_amplitude->trackEvent('Domain Linked Successfully');
            }
        }
    }

}
