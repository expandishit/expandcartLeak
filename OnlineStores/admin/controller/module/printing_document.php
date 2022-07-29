<?php

class ControllerModulePrintingDocument extends Controller
{
    private $settings;

    public function init($models)
    {
        // TODO modularize this.
        foreach ($models as $model) {

            $this->load->model($model);

            $object = explode('/', $model);
            $object = end($object);

            $model = str_replace('/', '_', $model);

            $this->settings = $this->{"model_" . $model};
        }

        if (isset($this->session->data['errors'])) {
            $this->data['error_warning'] = implode('</br >', $this->session->data['errors']);

            unset($this->session->data['errors']);
        }

        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        }

        $this->language->load('module/printing_document');
    }

    public function index()
    {
        $this->init([
            'module/printing_document'
        ]);
        $this->data['defaultCurrency'] = $this->config->get('config_currency');
        $this->load->model('localisation/language');

        $data['languages'] = $this->model_localisation_language->getLanguages();

        if (isset($this->request->post['printing_document'])) {
            $data['printing_document_module'] = $this->request->post['printing_document_module'];
        } elseif ($this->config->get('printing_document_module')) {
            $data['printing_document_module'] = $this->config->get('printing_document_module');
        }

        

        $covers = $data['printing_document_module']['cover'];

        $this->load->model('tool/image');
        foreach ($covers as $cover) {
            if ($cover['image']) {
                $image = $cover['image'];
                $thumb = $this->model_tool_image->resize($image, 150, 150);
            } else {
                $image = 'no_image.jpg';
                $thumb = $this->model_tool_image->resize($image, 150, 150);
            }

            $data['modules'][] = array(
                'name'  => $cover['name'],
                'price' => $cover['price'],
                'image' => $image,
                'thumb' => $thumb,
            );
        }

        // print_r($data);
        // exit;
        $this->data['no_image'] = $this->model_tool_image->resize('no_image.jpg', 150, 150);

        $this->document->setTitle($this->language->get('printing_document_heading_title'));

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
            'text'      => $this->language->get('printing_document_heading_title'),
            'href'      => $this->url->link('module/printing_document', '', 'SSL'),
            'separator' => ' :: '
        );
        
        $data['entry_status'] = $this->language->get('entry_status');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_settings'] = $this->language->get('text_settings');
        $data['text_cover_types'] = $this->language->get('text_cover_types');
        $data['text_image'] = $this->language->get('text_image');
        $data['text_name'] = $this->language->get('text_name');
        $data['text_price'] = $this->language->get('text_price');
        $data['button_add_module'] = $this->language->get('button_add_module');


        $this->template = 'module/printing_document/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $data['action'] = $this->url->link('module/printing_document/updateSettings', '', 'SSL');
        $data['cancel'] = $this->url.'marketplace/home';

        $this->data = array_merge($this->data, $data);

        $this->response->setOutput($this->render());
    }

    public function updateSettings()
    {

        $this->load->model('setting/setting');
        $this->language->load('module/printing_document');

        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)) {
             $result_json['success'] = '0';
             $result_json['error'] = $this->error;
        }else{
            if ( $this->validate() )
            {
                $postData = $this->request->post;
                //unset($postData['path']);
                $this->model_setting_setting->insertUpdateSetting('printing_document', $postData);

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
        $this->init(['module/printing_document']);

        $this->settings->install();
    }

    public function uninstall()
    {
        $this->init(['module/printing_document']);

        $this->settings->uninstall();
    }

    private function validate() {
        if (!$this->user->hasPermission('modify', 'module/printing_document')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        
        if (!$this->error) {
            return true;
        } else {
            return false;
        }   
    }
}
