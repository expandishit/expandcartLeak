<?php

class ControllerModuleCurtainSeller extends Controller
{
    protected $MODULE_NAME = "curtain_seller";


    public function index()
    {
        $this->init();
        $this->renderTemplate();
    }


    public function submit()
    {
        $this->saveSettings();
        
        return $this->successResponse();
    }


    private function init()
    {
        $this->loadModels();
        $this->language->load("module/{$this->MODULE_NAME}");
        $this->document->setTitle($this->language->get('heading_title'));
        $this->breadcrumbs();
        $this->getSettings();
    }


    private function loadModels()
    {
        $this->load->model("setting/setting");
        $this->load->model("module/{$this->MODULE_NAME}");

        return true;
    }


    private function saveSettings()
    {
        if (! $this->request->post['available_widths']) {
            $this->request->post['available_widths'] = [];
        } else {
            $this->request->post['available_widths'] = array_unique( $this->request->post['available_widths'] );
        }

        if (! $this->request->post['available_blocks']) {
            $this->request->post['available_blocks'] = [];
        }

        $this->loadModels();
        $this->model_setting_setting->insertUpdateSetting($this->MODULE_NAME, $this->request->post);

        return true;
    }


    private function getSettings()
    {
        $this->loadModels();
        $this->data['settings'] = $this->model_setting_setting->getSetting($this->MODULE_NAME);
        $this->data['settings']['is_enabled'] = $this->model_module_curtain_seller->isEnabled();
        $this->data['settings']['is_in_form'] = $this->model_module_curtain_seller->isInForm();
    }


    private function successResponse()
    {
        $result_json = [];
        $result_json['success'] = '1';
        $result_json['success_msg'] = $this->language->get('text_success');

        return $this->response->setOutput(json_encode($result_json));
    }


    private function renderTemplate()
    {
        $this->template = "module/{$this->MODULE_NAME}.expand";
        $this->data['action'] = $this->url->link("module/{$this->MODULE_NAME}/submit", '', 'SSL');
        $this->data['cancel'] = $this->url->link('marketplace/home', '', 'SSL');
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->response->setOutput($this->render());
    }


    private function breadcrumbs()
    {
        // -==-==-==-==- breadcrumbs -==-==-==-==-
        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_module'),
            'href'      => $this->url->link('marketplace/home', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('module/custom_fees_for_payment_method', '', 'SSL'),
            'separator' => ' :: '
        );
        // -==-==-==-==- end of breadcrumbs -==-==-==-==-
    }
}
