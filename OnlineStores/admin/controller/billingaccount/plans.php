<?php 
class ControllerBillingAccountPlans extends Controller {
	private $error = array();
   
  	public function index() {

          $this->redirect($this->url->link('account/charge', '', 'SSL'));

        $this->language->load('billingaccount/plans');

        $geoip2 = new ModulesGarden\Geolocation\Submodules\GeoIP2();

        $country = "US";
        $pricingCurrency = "USD";
        $langcode="en";

        try {
            $country = $geoip2->getCountry();
        }
        catch(\Exception $ex) { }

        $countryCurrency = array("EG" => "EGP", "SA" => "SAR");

        if (array_key_exists($country, $countryCurrency))
            $pricingCurrency = $countryCurrency[$country];

        $pricingJSONFileName = "https://expandcart.com/pricing_all_backend.json";
        $pricingJSONAll = json_decode(file_get_contents($pricingJSONFileName), true);
        $pricingJSON = $pricingJSONAll[$pricingCurrency];

        if ($this->language->get('direction') == 'rtl') {
            $langcode="ar";
        }

        $pricingJSON = array_merge($pricingJSON, $pricingJSON["strings"][$langcode]);
	
    	$this->document->setTitle($this->language->get('heading_title'));

        $this->data['heading_title'] = $this->language->get('heading_title');

        $this->data['text_upgradeExpand'] = $this->language->get('text_upgradeExpand');
        $this->data['text_basic'] = $this->language->get('text_basic');
        $this->data['text_permonth'] = $this->language->get('text_permonth');
        $this->data['text_permonth_billed_annually'] = $this->language->get('text_permonth_billed_annually');
        $this->data['text_perq'] = $this->language->get('text_perq');
        $this->data['text_peryear'] = $this->language->get('text_peryear');
        $this->data['text_signup'] = $this->language->get('text_signup');
        $this->data['text_products'] = $this->language->get('text_products');
        $this->data['text_diskSpace'] = $this->language->get('text_diskSpace');
        $this->data['text_unlimitedbandwidth'] = $this->language->get('text_unlimitedbandwidth');
        $this->data['text_freedomainname'] = $this->language->get('text_freedomainname');
        $this->data['text_googleanalytics'] = $this->language->get('text_googleanalytics');
        $this->data['text_facebookfans'] = $this->language->get('text_facebookfans');
        $this->data['text_freeproductlisting'] = $this->language->get('text_freeproductlisting');
        $this->data['text_dealoftheday'] = $this->language->get('text_dealoftheday');
        $this->data['text_freeimagedesign'] = $this->language->get('text_freeimagedesign');
        $this->data['text_standard'] = $this->language->get('text_standard');
        $this->data['text_monthlylikes'] = $this->language->get('text_monthlylikes');
        $this->data['text_business'] = $this->language->get('text_business');
        $this->data['text_productslisting'] = $this->language->get('text_productslisting');
        $this->data['text_professional'] = $this->language->get('text_professional');
        $this->data['text_proimages'] = $this->language->get('text_proimages');
        $this->data['text_ultimate'] = $this->language->get('text_ultimate');
        $this->data['text_unlimitedproducts'] = $this->language->get('text_unlimitedproducts');
        $this->data['text_unlimitedspace'] = $this->language->get('text_unlimitedspace');
        $this->data['text_ultimateimages'] = $this->language->get('text_ultimateimages');
        $this->data['text_bestValue'] = $this->language->get('text_bestValue');
        $this->data['trial_expired'] = $this->language->get('trial_expired');
        $this->data['text_no_billaccess'] = $this->language->get('text_no_billaccess');

        $this->data['text_commission_sales'] = $this->language->get('text_commission_sales');
        $this->data['text_multi_lang'] = $this->language->get('text_multi_lang');
        $this->data['text_multi_curr'] = $this->language->get('text_multi_curr');
        $this->data['text_coupons_vouchers'] = $this->language->get('text_coupons_vouchers');
        $this->data['text_free_res_templates'] = $this->language->get('text_free_res_templates');
        $this->data['text_payment_shipping'] = $this->language->get('text_payment_shipping');
        $this->data['text_one_page_checkout'] = $this->language->get('text_one_page_checkout');
        $this->data['text_blog'] = $this->language->get('text_blog');
        $this->data['text_google_adwords'] = $this->language->get('text_google_adwords');
        $this->data['text_SSL'] = $this->language->get('text_SSL');
        $this->data['text_facebook_store'] = $this->language->get('text_facebook_store');
        $this->data['text_multi_merchant'] = $this->language->get('text_multi_merchant');
        $this->data['text_pro_filter'] = $this->language->get('text_pro_filter');
        $this->data['text_custom_copyrights'] = $this->language->get('text_custom_copyrights');
        $this->data['text_pro_help_launch'] = $this->language->get('text_pro_help_launch');
        $this->data['text_pro_help_marketing'] = $this->language->get('text_pro_help_marketing');
        $this->data['text_data_migration'] = $this->language->get('text_data_migration');
        $this->data['text_save_with_yearly'] = $this->language->get('text_save_with_yearly');
        $this->data['text_launch_my_store'] = $this->language->get('text_launch_my_store');
        $this->data['text_enterprise'] = $this->language->get('text_enterprise');
        $this->data['text_handle_everything'] = $this->language->get('text_handle_everything');
        $this->data['text_personal_staff'] = $this->language->get('text_personal_staff');
        $this->data['text_includes_everything'] = $this->language->get('text_includes_everything');
        $this->data['text_full_launch'] = $this->language->get('text_full_launch');
        $this->data['text_pro_marketing_plan'] = $this->language->get('text_pro_marketing_plan');
        $this->data['text_handle_online_ads'] = $this->language->get('text_handle_online_ads');
        $this->data['text_special_personal_assistant'] = $this->language->get('text_special_personal_assistant');
        $this->data['text_basic_seo'] = $this->language->get('text_basic_seo');
        $this->data['text_monthly_data_update'] = $this->language->get('text_monthly_data_update');
        $this->data['text_monthly_images_update'] = $this->language->get('text_monthly_images_update');
        $this->data['text_custom_dev_design'] = $this->language->get('text_custom_dev_design');
        $this->data['text_mobile_app_discount_standard'] = $this->language->get('text_mobile_app_discount_standard');
        $this->data['text_mobile_app_discount_business'] = $this->language->get('text_mobile_app_discount_business');
        $this->data['text_mobile_app_discount_ultimate'] = $this->language->get('text_mobile_app_discount_ultimate');
        $this->data['text_mobile_app_discount_enterprise'] = $this->language->get('text_mobile_app_discount_enterprise');
        $this->data['pricingJSON'] = $pricingJSON;

        $billingAccess = '0';

        if ($this->user->hasPermission('access', 'billingaccount/plans') && ENABLE_BILLING == '1') {
            $billingAccess = '1';
        }

        $this->data['billingAccess'] = $billingAccess;

        if ($billingAccess == "1") {
            $this->load->model('billingaccount/common');

            # Define WHMCS URL & AutoAuth Key
            $whmcsurl = MEMBERS_LINK;
            $autoauthkey = MEMBERS_AUTHKEY;

            $langParam = '&language=English';

            if ($this->language->get('code') == 'ar') {
                $langParam = '&language=Arabic';
            }

            $timestamp = time(); # Get current timestamp
            $email = BILLING_DETAILS_EMAIL; # Clients Email Address to Login
            $basicDLink = "cart.php?a=add&pid=1" . $langParam;
            $standardDLink = "cart.php?a=add&pid=2" . $langParam;
            $businessDLink = "cart.php?a=add&pid=4" . $langParam;
            $professionalDLink = "cart.php?a=add&pid=5" . $langParam;
            $ultimateDLink = "cart.php?a=add&pid=6" . $langParam;
            $enterpriseDLink = "cart.php?a=add&pid=8" . $langParam;

            $hash = sha1($email.$timestamp.$autoauthkey); # Generate Hash

            $this->data['url_basic'] = $whmcsurl."?email=$email&timestamp=$timestamp&hash=$hash&goto=".urlencode($basicDLink);
            $this->data['url_standard'] = $whmcsurl."?email=$email&timestamp=$timestamp&hash=$hash&goto=".urlencode($standardDLink);
            $this->data['url_business'] = $whmcsurl."?email=$email&timestamp=$timestamp&hash=$hash&goto=".urlencode($businessDLink);
            $this->data['url_professional'] = $whmcsurl."?email=$email&timestamp=$timestamp&hash=$hash&goto=".urlencode($professionalDLink);
            $this->data['url_ultimate'] = $whmcsurl."?email=$email&timestamp=$timestamp&hash=$hash&goto=".urlencode($ultimateDLink);
            $this->data['url_enterprise'] = $whmcsurl."?email=$email&timestamp=$timestamp&hash=$hash&goto=".urlencode($enterpriseDLink);
        }
        else {
            $this->data['url_basic'] = '#';
            $this->data['url_standard'] = '#';
            $this->data['url_business'] = '#';
            $this->data['url_professional'] = '#';
            $this->data['url_enterprise'] = '#';
        }

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('billingaccount/plans', '', 'SSL'),
            'separator' => false
        );

        if ($this->language->get('direction') == 'rtl') {
            $this->template = 'billingaccount/plans-ar.expand';
        } else {
            $this->template = 'billingaccount/plans-v2.expand';
        }

        $this->base="common/base";

        $this->response->setOutput($this->render());
  	}

  	public function extendtrial() {
        //CLIENT_SUSPEND
        //check he didn't extend before from settings
        //session has isExtend = 1

        if(defined(CLIENT_SUSPEND) && !$this->config->get('trial_extended') && $this->session->data['isextend']) {
            $url = "https://api.expandcart.com/extend.php";

            $postfields['t'] = "IARR2Cxtx207bONNys8KFBksKWCxU9A1rIC0iZlns5CKl6K0Gq19TL9EYD";
            $postfields['extend'] = '5'; //no. of days to be extended
            $postfields['storecode'] = STORECODE;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 100);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            $data = json_decode(curl_exec($ch));
            curl_close($ch);

            $status = $data->status;
            $callErrMsg = $data->message;

            if($status === 'success') {
                $this->load->model('setting/setting');
                $this->model_setting_setting->insertUpdateSetting('trial', array('trial_extended' => '1'));
            }
        }
        $this->redirect($this->url->link('common/home'));
    }
}
?>
