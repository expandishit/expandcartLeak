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

    public function updateUser($data=[]){
        $mp     = Mixpanel::getInstance($this->token);
        $user   = $this->getMerchantInfo();

        if($user)
            $mp->people->set($user['user_id'], $data );
    }

    public function getMerchantInfo()
    {
        return ['user_id' => strtoupper(STORECODE)];
    }

}
?>