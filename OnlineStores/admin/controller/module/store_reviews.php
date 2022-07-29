<?php

/**
 *   Controller Class for StoreReviews Module
 *
 * @author Fayez.
 */
class ControllerModuleStoreReviews extends Controller
{
    public $route = 'module/store_reviews';
    public $module = 'store_reviews';

    public function index()
    {

        if (!\Extension::isInstalled("{$this->module}") || !$this->user->hasPermission("modify", "module/{$this->module}")) {
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
        $this->data['customer_url'] = $this->url->link('sale/customer/update?customer_id=');
        $this->data["{$this->module}_data"] = $this->model_setting_setting->getSetting($this->module);
        $this->data['save_ad_packages_app_status_action'] = $this->url->link("module/{$this->module}/saveAppStatus", '', 'SSL');
        $this->data['save_ad_packages_app_review_visitor_action'] = $this->url->link("module/{$this->module}/saveAppReviewVisitor", '', 'SSL');
        
        $this->data["{$this->module}_reviews"] = $this->getReviews();
        
        $this->template = "module/{$this->module}.expand";

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->response->setOutput($this->render());
    }


    public function saveAppStatus() {
     
        $this->load->language("module/{$this->module}");
        $this->load->model('setting/setting');
        
        $store_reviews_app_status = $this->request->post['store_reviews_app_status'];

        $store_reviews_settings = $this->model_setting_setting->getSetting($this->module);
        $store_reviews_settings = array_merge(
            $store_reviews_settings, 
            compact(
                'store_reviews_app_status'
            ));

        $this->model_setting_setting->editSetting($this->module, $store_reviews_settings);

        $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');
        $result_json['success'] = '1';
        return $this->response->setOutput(json_encode($result_json));
    }

    public function saveAppReviewVisitor() {
     
        $this->load->language("module/{$this->module}");
        $this->load->model('setting/setting');
        
        $store_reviews_app_allow_guest = $this->request->post['store_reviews_app_allow_guest'];

        $store_reviews_settings = $this->model_setting_setting->getSetting($this->module);
        $store_reviews_settings = array_merge(
            $store_reviews_settings, 
            compact(
                'store_reviews_app_allow_guest'
            ));

        $this->model_setting_setting->editSetting($this->module, $store_reviews_settings);

        $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');
        $result_json['success'] = '1';
        return $this->response->setOutput(json_encode($result_json));
    }


    public function getReviews() {
        $this->load->model("module/{$this->module}");
        $app_model = "model_module_{$this->module}";

        return $this->$app_model->getReviews();

    }


    public function install()
    {

            $this->load->model("module/{$this->module}");
            $model_path = "model_module_{$this->module}";

            $this->$model_path->install();
    }


    public function uninstall()
    {
       
            $this->load->model("module/{$this->module}");
            $model_path = "model_module_{$this->module}";

            $this->$model_path->uninstall();
    }

}
