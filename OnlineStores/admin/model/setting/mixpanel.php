<?php
class ModelSettingMixpanel extends Model {

    public $token = MIXPANEL_TOKEN;

    public function trackEvent($event_name, $data=[]) {

        $mp = Mixpanel::getInstance($this->token); // instantiate the Mixpanel class
        $user   = $this->getMerchantInfo();
        if($user) {
            $mp->identify($user['user_id']);
            $mp->track($event_name, $data); // track an event
        }
    }

    public function trackRevenue($price) {
        $mp = Mixpanel::getInstance($this->token); // instantiate the Mixpanel class
        $user   = $this->getMerchantInfo();
        if($user) {
            $mp->identify($user['user_id']);
            $mp->people->trackCharge(12345, $price); // track charge
        }
    }

    public function createUser(){
        $mp     = Mixpanel::getInstance($this->token);
        $user   = $this->getMerchantInfo();

        if($user) {

            /***************** Start ExpandCartTracking #347687  ****************/

            $mp->people->set($user['user_id'], array(
                '$name'                 => $user['name'],
                '$email'                => $user['email'],
                '$phone'                => $user['phone'],
                '$whmcs client id'      => $user['whmcs_client_id'],
                '$store code'           => $user['store_code'],
                '$products count'       => 0,
                '$created at'           => date("Y-m-d H:i:s"),
                '$current template'     => $user['current_template'],
                '$subscription plan'    => PRODUCTID,
                '$subscription state'   => 'active',
            ));

            $this->trackEvent('Sign Up');

            /***************** End ExpandCartTracking #347687  ****************/
        }
    }

    public function updateUser($data=[],$default=''){
        $mp     = Mixpanel::getInstance($this->token);
        $user   = $this->getMerchantInfo();

        if($user)
            $mp->people->set($user['user_id'], $default != 'default' ? $data :[
                '$name'                 => $user['name'],
                '$email'                => $user['email'],
                '$phone'                => $user['phone'],
                '$whmcs client id'      => $user['whmcs_client_id'],
                '$store code'           => $user['store_code'],
                '$current template'     => $user['current_template'],
                '$subscription plan'    => PRODUCTID,
                '$subscription state'   => 'active',
            ]);
    }

    public function incrementProperty($property_name, $increment = 1)
    {
        $mp     = Mixpanel::getInstance($this->token);
        $user   = $this->getMerchantInfo();
        if ($user) {
            $mp->identify($user['user_id']);
            $mp->people->increment($user['user_id'], $property_name, $increment);
        }
    }

    public function getMerchantInfo()
    {
        $whmcs  = new whmcs();
        $userId = WHMCS_USER_ID;
        $phoneNumber = $whmcs->getClientPhone($userId);

        // get current template info
        $this->load->model('setting/template');
        $template_info = $this->model_setting_template->getTemplateInfo(CURRENT_TEMPLATE);
        $template_name = isset($template_info['Name']) ? $template_info['Name'] :  CURRENT_TEMPLATE;

        return [
            'user_id'           => strtoupper(STORECODE),
            'name'              => BILLING_DETAILS_NAME,
            'email'             => BILLING_DETAILS_EMAIL,
            'phone'             => $phoneNumber ?? $this->config->get('config_telephone'),
            'store_code'        => STORECODE,
            'whmcs_client_id'   => WHMCS_USER_ID,
            'current_template'  => $template_name,
        ];
    }

}
?>