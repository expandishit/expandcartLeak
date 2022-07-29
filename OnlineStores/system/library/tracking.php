<?php

class Tracking extends Controller {

    public function __construct($registry)
    {
        parent::__construct($registry);
    }

    public function updateGuideValue($key){

        $this->load->model('setting/setting');

        $guide = $this->model_setting_setting->getGuideValue('ASSISTANT')[$key];
        if ($guide != 1){

            $this->model_setting_setting->editGuideByKey($key, '1');

            if ($this->session->data['card_name']==$key){

                /** Start ExpandCartTracking #347740 */

                //  track the done step event

                $this->load->model('setting/mixpanel');
                $this->model_setting_mixpanel->trackEvent('Assistant - card done',[
                    'card_name' => $key
                ]);

                $this->load->model('setting/amplitude');
                $this->model_setting_amplitude->trackEvent('Assistant - card done',[
                    'card_name' => $key
                ]);

                /** End */
            }
        }

        return $guide;

    }

}