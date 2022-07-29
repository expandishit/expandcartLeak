<?php

class ControllerModuleFastFinder extends Controller
{
    public function index()
    {
        $this->language->load('module/fast_finder');
        $this->document->setTitle($this->language->get('fast_finder_title'));

        $this->data['fast_finder'] = $this->config->get('fast_finder');
        $this->data['submit_link'] = $this->url->link('module/fast_finder/saveSettings', '', 'SSL');
        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_module'),
            'href'      => $this->url->link('marketplace/home', 'type=apps', 'SSL'),
            'separator' => ' :: '
        );
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('fast_finder_title'),
            'href'      => $this->url->link('module/fast_finder', '', 'SSL'),
            'separator' => ' :: '
        );
        $this->template = 'module/fast_finder.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );
        $this->response->setOutput($this->render_ecwig());
    }

    public function saveSettings()
    {
        $this->language->load('module/fast_finder');
        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting('fast_finder', $this->request->post);
        $json['success'] = '1';
        $json['success_msg'] = $this->language->get('fast_finder_success_save');
        $this->response->setOutput(json_encode($json));
    }

}