<?php

class ControllerModuleSalesBooster extends Controller
{
    private $settings;
    private $error = array();

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

        $this->language->load('module/sales_booster');
    }

    public function index()
    {
        $this->init([
            'module/sales_booster'
        ]);

        $this->load->model('localisation/language');
        $data['languages'] = $this->model_localisation_language->getLanguages();

        $this->load->model('module/sales_booster');
        $data['layouts'] = $this->model_module_sales_booster->getLayouts();
        
        if (isset($this->request->post['sales_booster'])) {
            $data['sales_booster_module'] = $this->request->post['sales_booster_module'];
        } elseif ($this->config->get('sales_booster_module')) {
            $data['sales_booster_module'] = $this->config->get('sales_booster_module');
        }

        

        $gsafeImg = $data['sales_booster_module']['gsafe'];

        $this->load->model('tool/image');
        
        if ($gsafeImg) {
            $image = $gsafeImg;
        } else {
            $image = 'no_image.jpg';
        }

        $data['gsafeImg'] = array(
            'image' => $image,
            'thumb' => $this->model_tool_image->resize($image, 150, 150)
        );
        
        $this->data['no_image'] = $this->model_tool_image->resize('no_image.jpg', 150, 150);

        $this->document->setTitle($this->language->get('sales_booster_heading_Stitle'));

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
            'text'      => $this->language->get('sales_booster_heading_Stitle'),
            'href'      => $this->url->link('module/sales_booster', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->template = 'module/sales_booster/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $data['action'] = $this->url->link('module/sales_booster/updateSettings', '', 'SSL');
        $data['cancel'] = $this->url.'marketplace/home';

        $this->data = array_merge($this->data, $data);

        $this->response->setOutput($this->render());
    }

    //Description header and footer layouts
    public function layouts()
    {
        $this->init([
            'module/sales_booster'
        ]);
        
        $this->load->model('localisation/language');
        $data['languages'] = $this->model_localisation_language->getLanguages();

        $this->load->model('module/sales_booster');
        $data['layouts'] = $this->model_module_sales_booster->getLayouts();

        $this->document->setTitle($this->language->get('sales_booster_heading_Ltitle'));

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
            'text'      => $this->language->get('sales_booster_heading_Ltitle'),
            'href'      => $this->url->link('module/sales_booster', '', 'SSL'),
            'separator' => ' :: '
        );
        
        $this->template = 'module/sales_booster/description_layouts.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $data['action'] = $this->url->link('module/sales_booster/remove', '', 'SSL');
        $data['cancel'] = $this->url.'marketplace/home';

        $this->data = array_merge($this->data, $data);

        $this->response->setOutput($this->render());
    }

    //insert new layout
    public function insert() {
        $this->language->load('module/sales_booster');

        $this->document->setTitle($this->language->get('sales_booster_heading_Ltitle'));

        $this->load->model('module/sales_booster');

        if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {
            if ( !$this->validateForm() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
                
                $this->response->setOutput(json_encode($result_json));
                
                return;
            }

            $this->model_module_sales_booster->addLayout($this->request->post);

            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

            $result_json['success'] = '1';
            
            $this->response->setOutput(json_encode($result_json));
            
            return;
        }

        $this->getForm();
    }

        public function update() {
        $this->language->load('module/sales_booster');

        $this->document->setTitle($this->language->get('sales_booster_heading_Ltitle'));

        $this->load->model('module/sales_booster');

        if ($this->request->server['REQUEST_METHOD'] == 'POST')
        {
            if ( !$this->validateForm() )
            {
                $result_json['success'] = '0';
                $result_json['error'] = $this->error;
                
                $this->response->setOutput(json_encode($result_json));
                
                return;
            }

            $this->model_module_sales_booster->editLayout($this->request->get['layout_id'], $this->request->post);

            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

            $result_json['success'] = '1';
            
            $this->response->setOutput(json_encode($result_json));
            
            return;
        }

        $this->getForm();
    }

    public function delete()
    {
        $this->load->model("module/sales_booster");
        
        $id = $this->request->post["layout_id"];
        $this->model_module_sales_booster->deleteLayout($id);
        $result_json['success'] = '1';
        $result_json['success_msg'] = $this->language->get('text_bulkdelete_success');

        $this->response->setOutput(json_encode($result_json));
        return;
    }

    protected function getForm()
    {
        if (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else {
            $this->data['error_warning'] = '';
        }

        if (isset($this->error['name'])) {
            $this->data['error_name'] = $this->error['name'];
        } else {
            $this->data['error_name'] = array();
        }

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_Ltitle'),
            'href'      => $this->url->link('module/sales_booster/layouts', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => !isset($this->request->get['layout_id']) ? $this->language->get('breadcrumb_insert') : $this->language->get('breadcrumb_update'),
            'href'      => $this->url->link('module/sales_booster/layouts', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        if (!isset($this->request->get['layout_id'])) {
            $this->data['action'] = $this->url->link('module/sales_booster/insert', 'token=' . $this->session->data['token'], 'SSL');

        } else {

            $this->data['action'] = $this->url->link('module/sales_booster/update', 'token=' . $this->session->data['token'] . '&layout_id=' . $this->request->get['layout_id'], 'SSL');
        }

        $this->data['cancel'] = $this->url->link('module/sales_booster/layouts', 'token=' . $this->session->data['token'], 'SSL');


        $this->data['token'] = $this->session->data['token'];

        $this->load->model('localisation/language');

        $this->data['languages'] = $this->model_localisation_language->getLanguages();

        if (isset($this->request->post['layout_description'])) {
            $this->data['layout_description'] = $this->request->post['layout_description'];
        } elseif (isset($this->request->get['layout_id'])) {
            $this->data['layout_description'] = $this->model_module_sales_booster->getLayoutDescriptions($this->request->get['layout_id']);
        } else {
            $this->data['layout_description'] = array();
        }

        $this->template = 'module/sales_booster/layout_form.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }

    private function validateForm()
    {
        if ( !$this->user->hasPermission('modify', 'module/sales_booster') )
        {
            $this->error['error'] = $this->language->get('error_permission');
        }

        foreach ( $this->request->post['layout_description'] as $language_id => $value )
        {
            if ( (utf8_strlen($value['name']) < 2) || (utf8_strlen($value['name']) > 255) )
            {
                $this->error['name_' . $language_id] = $this->language->get('error_name');
            }
        }

        if ( $this->error && !isset($this->error['error']) )
        {
            $this->error['warning'] = $this->language->get('error_warning');
        }
        
        return $this->error ? false : true;
    }

    public function updateSettings()
    {

        $this->load->model('setting/setting');
        $this->language->load('module/sales_booster');

        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)) {
             $result_json['success'] = '0';
             $result_json['error'] = $this->error;
        }else{
            if ( $this->validate() )
            {
                $postData = $this->request->post;
                //unset($postData['path']);
                $this->model_setting_setting->insertUpdateSetting('sales_booster', $postData);

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
        $this->init(['module/sales_booster']);

        $this->settings->install();
    }

    public function uninstall()
    {
        $this->init(['module/sales_booster']);

        $this->settings->uninstall();
    }

    private function validate() {
        if (!$this->user->hasPermission('modify', 'module/sales_booster')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        
        if (!$this->error) {
            return true;
        } else {
            return false;
        }   
    }
}
