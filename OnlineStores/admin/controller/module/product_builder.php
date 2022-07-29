<?php

/**
 *   Controller Class for Product Builder Module
 *
 * @author Fayez.
 */
class ControllerModuleProductBuilder extends Controller
{
    private $error = [];
    public $route = 'module/product_builder';
    public $module = 'product_builder';

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
            'text' => $this->language->get("{$this->module}_heading_title"),
            'href' => $this->url->link("module/{$this->module}", '', 'SSL'),
            'separator' => ' :: '
        );
                
        $this->data['action'] = $this->url->link("module/{$this->module}/updateSettings", '', 'SSL');

        $this->data["{$this->module}_data"] = $this->config->get($this->module);

        $this->template = "module/{$this->module}.expand";

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->response->setOutput($this->render());
    }


    public function updateSettings()
    {
        $this->load->language("module/{$this->module}");

        if ($this->validate()) {
            $this->load->model('setting/setting');

            if (isset($this->request->get['store_id'])) {
                $store_id = $this->request->get['store_id'];
            } else {
                $store_id = 0;
            }

            $this->model_setting_setting->editSetting(
                $this->module, [$this->module => $this->request->post]
            );

            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');
            $result_json['success'] = '1';
        } else {
            $result_json['success'] = '0';
            $this->error['warning'] = $this->language->get('text_error');
            $result_json['error'] = $this->error;
        }

        $this->response->setOutput(json_encode($result_json));
        return;
    }


    public function install()
    {
        $this->language->load("module/{$this->module}");

        if (!$this->user->hasPermission("modify", "module/{$this->module}")) {
            
            return $this->forward('error/permission');

        } else {

            $this->load->model("user/user_group");
            $this->model_user_user_group->addPermission(
                $this->user->getId(),
                'access', "module/{$this->module}"
            );

            $this->load->model("module/{$this->module}");
            $model_path = "model_module_{$this->module}";

            $this->$model_path->install();

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


    /**
     *   Validate the input data.
     *
     * @return boolean
     */
    private function validate()
    {
        $this->load->language("module/{$this->module}");

        if (!$this->user->hasPermission('modify', "module/{$this->module}")) {
            $this->error[] = $this->language->get('error_permission');
            return false;
        }

        $data = $this->request->post;

        // if (!isset($data['username']) || strlen($data['username']) == 0) {
        //     $this->error['username'] = $this->language->get('error_username_required');
        // }

        return count($this->error) > 0 ? false : true;
    }
}