<?php

/**
 *   Controller Class for SellerAds Module
 *
 * @author Fayez.
 */
class ControllerModuleSellerAds extends Controller
{
    public $route = 'module/seller_ads';
    public $module = 'seller_ads';

    public function index()
    {

        if (!$this->isInstalled() || !$this->user->hasPermission("modify", "module/{$this->module}")) {
            return $this->forward('error/permission');
        }

        $this->load->model('setting/setting');
        $this->load->language("module/{$this->module}");

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_modules'),
            'href' => $this->url->link(
                'marketplace/home',
                '',
                'SSL'
            ),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get("heading_title"),
            'href' => $this->url->link("module/{$this->module}", '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['defaultCurrency'] = $this->config->get('config_currency');
                
        $this->data['save_ad_packages_form_action'] = $this->url->link("module/{$this->module}/savePackages", '', 'SSL');
        $this->data['save_ad_packages_app_status_action'] = $this->url->link("module/{$this->module}/saveAppStatus", '', 'SSL');

        $this->data["{$this->module}_data"] = $this->model_setting_setting->getSetting($this->module);

        $this->data["{$this->module}_subscribers"] = $this->getSubscribers();
        $this->data['customer_url'] = $this->url->link('sale/customer/update?customer_id=');
        
        $this->template = "module/{$this->module}.expand";

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->response->setOutput($this->render());
    }


    public function savePackages() {
     
        $this->load->language("module/{$this->module}");
        $this->load->model('setting/setting');
        
        $square_ad_display_days = (int)$this->request->post['square_ad_display_days'];
        $banner_ad_display_days = (int)$this->request->post['banner_ad_display_days'];
        $square_ad_status = (int)$this->request->post['square_ad_status'];
        $banner_ad_status = (int)$this->request->post['banner_ad_status'];
        $square_ad_price = (float)$this->request->post['square_ad_price'];
        $banner_ad_price = (float)$this->request->post['banner_ad_price'];

        $seller_ads_settings = $this->model_setting_setting->getSetting($this->module);
        $seller_ads_settings = array_merge(
            $seller_ads_settings, 
            compact(
                'square_ad_display_days', 
                'banner_ad_display_days', 
                'square_ad_status', 
                'banner_ad_status',
                'square_ad_price', 
                'banner_ad_price'
            ));

        $this->model_setting_setting->editSetting($this->module, $seller_ads_settings);

        $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');
        $result_json['success'] = '1';
        return $this->response->setOutput(json_encode($result_json));
    }

    public function saveAppStatus() {
     
        $this->load->language("module/{$this->module}");
        $this->load->model('setting/setting');
        
        $seller_ads_app_status = (int)$this->request->post['seller_ads_app_status'];

        $seller_ads_settings = $this->model_setting_setting->getSetting($this->module);
        $seller_ads_settings = array_merge(
            $seller_ads_settings, 
            compact(
                'seller_ads_app_status'
            ));

        $this->model_setting_setting->editSetting($this->module, $seller_ads_settings);

        $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');
        $result_json['success'] = '1';
        return $this->response->setOutput(json_encode($result_json));
    }

    public function getSubscribers() {
        $this->load->model("module/{$this->module}");
        $app_model = "model_module_{$this->module}";

        return $this->$app_model->getSubscribers();

    }


    public function install()
    {
        $this->language->load("module/{$this->module}");

        if (!$this->user->hasPermission("modify", "module/{$this->module}")) {
            
            return $this->forward('error/permission');

        } else {

            $this->load->model("module/{$this->module}");
            $model_path = "model_module_{$this->module}";

            $this->$model_path->install();

            $this->load->model("user/user_group");
            $this->model_user_user_group->addPermission(
                $this->user->getId(),
                'access', "module/{$this->module}"
            );
            $this->model_user_user_group->addPermission(
                $this->user->getId(),
                'modify', "module/{$this->module}"
            );

            $this->load->model('setting/extension');
            $this->model_setting_extension->install('module', $this->module);

            $this->redirect($this->url->link("module/{$this->module}", '', 'SSL'));
        }
    }


    public function uninstall()
    {
        $this->language->load("module/{$this->module}");

        if (!$this->user->hasPermission("modify", "module/{$this->module}")) {

            return $this->forward('error/permission');

        } else {

            $this->load->model('setting/setting');
            $this->model_setting_setting->deleteSetting($this->module);

            $this->load->model("module/{$this->module}");
            $model_path = "model_module_{$this->module}";

            $this->$model_path->uninstall();

            $this->load->model('setting/extension');
            $this->model_setting_extension->uninstall('module', $this->module);

            $this->redirect($this->url->link('marketplace/home', '', 'SSL'));
        }
    }


    private function isInstalled()
    {
        $this->load->model('setting/extension');
        return in_array($this->module, $this->model_setting_extension->getInstalled('module'));
    }

}