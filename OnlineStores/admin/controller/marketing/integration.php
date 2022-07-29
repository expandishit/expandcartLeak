<?php
class ControllerMarketingIntegration extends Controller {
    private $error = array();
    private $facebook_pixel_actions=array(
        'Purchase','Generate Lead','Complete Registration',
        'Add Payment Info','Add to Basket','Add to Wishlist',
        'Initiate Checkout','Search','View Content'
    );
    private $snapchar_pixel_actions=array(
        'Purchase','Add to Basket','View Content'
    );
    private $google_analytics_actions=array(
        'Purchase','Initiate Checkout','View Content',
        'Add to Basket', 'Search',
    );
    
    private $twitter_pixel_actions = [ 
        'Purchase' ,
        'Add to Basket',
        'View Content',
        'Initiate Checkout',
        'Add to Wishlist',
        'Sign Up',
        'Search'
    ];


    public function index() {
        $this->language->load('marketing/integration');

        $this->document->setTitle($this->language->get('heading_title'));
        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('marketing/integration'),
            'separator' => ' :: '
        );

        $this->load->model('setting/setting');

        $this->data['action']=$this->url->link('marketing/integration/saveSettings');

        $this->data['setting'] = $this->model_setting_setting->getSetting('integrations');
        $this->data['fbe_pixel_installed']=$this->config->get('isPixelsInstalled')?? false;
        $this->data['fbe_pixel_url']=$this->url->link('module/facebook_business/pixelsettings');
        $this->data['fbp_actions']=$this->facebook_pixel_actions;
        $this->data['snp_actions']=$this->snapchar_pixel_actions;
        $this->data['ga_ana_actions']=$this->google_analytics_actions;
        $this->data['twitter_pixel_actions'] = $this->twitter_pixel_actions;

        $this->template = 'marketing/integrations/index.expand';
        $this->base = "common/base";

        $this->response->setOutput($this->render_ecwig());
    }

    public function saveSettings(){

        $fbp_success=false;
        $go_adwords_success=false;
        $go_analytics_success=false;

        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
	
            if (PRODUCTID == "3"){
                $white_list = array(
                    "mn_integ_fbp_status",
                    "mn_integ_fbp_code",
                    "mn_integ_fbp_action",
//                    "mn_integ_go_analytics_status",
//                    "mn_integ_go_analytics_id",
//                    "mn_integ_go_analytics_action"
                );
                $this->request->post = array_intersect_key($this->request->post, array_flip($white_list));
                    if (empty($this->request->post)){
                        $result_json = array(
                            'success' => '0',
                            'errors' => array(),
                            'success_msg' => $this->language->get('upgrade_plan_error')
                        );
                        $this->response->setOutput(json_encode($result_json));
                        return;
                    }
            }

            $this->load->model('setting/setting');
            $this->load->language('marketing/integration');
			$pixel_installed = $this->config->get('isPixelsInstalled')?? false;
			
			if($pixel_installed){
				$fbp_success=true;
			}
            else if ($this->request->post['mn_integ_fbp_status'] != "0") {

                if(!empty($this->request->post['mn_integ_fbp_code'])){

                    $this->request->post['mn_integ_fbp_code']=trim($this->request->post['mn_integ_fbp_code']);
                    $fbp_success=true;

                } else {
                    $this->error['mn_integ_fbp_code'] = $this->language->get('error_fbp_code');
                    $fbp_success=false;
                }
            }
            else{
                $this->request->post['mn_integ_fbp_code']="";
                $this->request->post['mn_integ_fbp_action']="";
                $fbp_success=true;
            }
            if (isset($this->request->post['mn_integ_go_adwords_status']) && $this->request->post['mn_integ_go_adwords_status'] != '0') {

                if(!empty($this->request->post['mn_integ_go_adwords_id']) && !empty($this->request->post['mn_integ_go_adwords_label'])){
                    $this->request->post['mn_integ_go_adwords_id']=trim($this->request->post['mn_integ_go_adwords_id']);
                    $this->request->post['mn_integ_go_adwords_label']=trim($this->request->post['mn_integ_go_adwords_label']);

                    $go_adwords_success=true;

                } else {

                    if(empty($this->request->post['mn_integ_go_adwords_id']))
                        $this->error['mn_integ_go_adwords_id'] = $this->language->get('error_go_ad_id');

                    if(empty($this->request->post['mn_integ_go_adwords_label']))
                        $this->error['mn_integ_go_adwords_label'] = $this->language->get('error_go_ad_label');
                    if(empty($this->request->post['mn_integ_go_adwords_id']) || empty($this->request->post['mn_integ_go_adwords_label']))
                        $go_adwords_success=false;
                }
            }
            else{
                $this->request->post['mn_integ_go_adwords_id']="";
                $this->request->post['mn_integ_go_adwords_label']="";
                $go_adwords_success=true;
            }
            if (isset($this->request->post['mn_integ_go_analytics_status']) && $this->request->post['mn_integ_go_analytics_status'] != "0") {

                if(!empty($this->request->post['mn_integ_go_analytics_id'])){

                    $this->request->post['mn_integ_go_analytics_id']=trim($this->request->post['mn_integ_go_analytics_id']);
                    $go_analytics_success=true;

                } else {
                    if(empty($this->request->post['mn_integ_go_analytics_id'])){
                        $this->error['mn_integ_go_analytics_id'] = $this->language->get('error_go_ana_id');
                        $go_analytics_success=false;
                    }

                }
            }
            else{
                $this->request->post['mn_integ_go_analytics_id']="";
                $this->request->post['mn_integ_go_analytics_action']="";
                $go_analytics_success=true;
            }

            if (isset($this->request->post['mn_integ_snp_status']) && $this->request->post['mn_integ_snp_status'] != "0") {

                if(!empty($this->request->post['mn_integ_snp_code'])){

                    $this->request->post['mn_integ_snp_code']=trim($this->request->post['mn_integ_snp_code']);
                    $snp_success=ture;

                } else {
                    $this->error['mn_integ_snp_code'] = $this->language->get('error_snp_code');
                    $snp_success=false;
                }

                if(!empty($this->request->post['mn_integ_snp_email'])){

                    $this->request->post['mn_integ_snp_email']=trim($this->request->post['mn_integ_snp_email']);
                    $snp_success=ture;

                } else {
                    $this->error['mn_integ_snp_email'] = $this->language->get('error_snp_email');
                    $snp_success=false;
                }
            }
            else{
                $this->request->post['mn_integ_snp_code']="";
                $this->request->post['mn_integ_snp_action']="";
                $snp_success=true;
            }

            if($fbp_success && $go_adwords_success && $go_analytics_success && $snp_success){
				if($pixel_installed){
					$fbe_pixel_configs = ['mn_integ_fbp_code','mn_integ_fbp_action','mn_integ_fbp_status'];
					foreach ($fbe_pixel_configs as $config_to_remove){
						if(isset($this->request->post[$config_to_remove])){
							unset($this->request->post[$config_to_remove]);
						}
					}
					
				}
                $this->model_setting_setting->insertUpdateSetting('integrations', $this->request->post);
                $result_json['success'] = '1';
                $result_json['success_msg'] = $this->language->get('text_success');
            }
            else{
                $result_json['success'] = '0';
                $this->error['warning'] = $this->language->get('error_warning');
                $result_json['errors'] = $this->error;
            }
        }
        else{
            $result_json = array(
                'success' => '0',
                'errors' => array(),
                'success_msg' => ''
            );
        }
        $this->response->setOutput(json_encode($result_json));
        return;
    }
}
