<?php

class ControllerModuleTopBannerAd extends Controller
{
    private $error;

    public function index()
    {
        $this->load->model('module/top_banner_ad');
        $this->language->load('module/top_banner_ad');

        $this->document->setTitle($this->language->get('heading_Stitle'));

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_modules'),
            'href'      => $this->url->link(
                'marketplace/home',
                '',
                'SSL'
            ),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('module/top_banner_ad', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->load->model('localisation/language');
        $this->data['languages'] = $this->model_localisation_language->getLanguages();
        $this->load->model('tool/image');
        $this->data['top_banner_ad_thumb'] = $this->model_tool_image->resize($this->config->get('top_banner_ad_image'), 150, 150);
        $this->data['top_banner_ad_image'] = $this->config->get('top_banner_ad_image');
        $this->data['top_banner_ad_status'] = $this->config->get('top_banner_ad_status');
        $this->data['top_banner_fixed_timing_end_date'] = $this->config->get('top_banner_fixed_timing_end_date');
        $this->data['top_banner_ad_link'] = $this->config->get('top_banner_ad_link');
        $this->data['top_banner_ad_name'] = $this->config->get('top_banner_ad_name');
        $this->data['top_banner_content'] = $this->config->get('top_banner_content');
        $this->data['top_banner_timing_model_type']  = $this->config->get('top_banner_timing_model_type');
        $this->data['slots'] = $this->config->get('top_banner_dynamic_timing_slots');

        $this->template = 'module/top_banner_ad/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $data['action'] = $this->url->link('module/top_banner_ad/updateSettings', '', 'SSL');
        $data['cancel'] = $this->url.'marketplace/home';

        $this->data = array_merge($this->data, $data);

        $this->response->setOutput($this->render());
    }

    protected function getForm()
    {
        if (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else {
            $this->data['error_warning'] = '';
        }

        if (isset($this->error['title'])) {
            $this->data['error_title'] = $this->error['title'];
        } else {
            $this->data['error_title'] = array();
        }

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_Ltitle'),
            'href'      => $this->url->link('module/top_banner_ad', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['cancel'] = $this->url->link('module/top_banner_ad', '', 'SSL');

        $this->template = 'module/top_banner_ad/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }

    public function updateSettings()
    {

        $this->load->model('setting/setting');
        $this->language->load('module/top_banner_ad');

        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)) {
             $result_json['success'] = '0';
             $result_json['error'] = $this->error;
        }else{
            if ( $this->validate() )
            {
                $postData = $this->request->post;
                if (isset($postData['undefined'])){unset($postData['undefined']);}
                if (isset($postData['path'])){unset($postData['path']);}
                $top_banner_version = (int) $this->config->get('top_banner_version');
                $top_banner_version++;
                $postData['top_banner_version'] = $top_banner_version;

                $this->model_setting_setting->insertUpdateSetting('top_banner_ad', $postData);

                $this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success');
                $result_json['success'] = '1';
            }
            else
            {
                $result_json['success'] = '0';
                $result_json['error'] = $this->error;
            }
        }
        $this->response->setOutput(json_encode($result_json));
        return;
    }

    public function install()
    {
        $this->load->model('module/top_banner_ad');

        $this->model_module_top_banner_ad->install();
    }

    public function uninstall()
    {
        $this->load->model('module/top_banner_ad');

        $this->model_module_top_banner_ad->uninstall();
    }

    private function validate() {
        if (!$this->user->hasPermission('modify', 'module/top_banner_ad')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        
        if (!$this->error) {
            return true;
        }
        else {
            return false;
        }   
    }

}
