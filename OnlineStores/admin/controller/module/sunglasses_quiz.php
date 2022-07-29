<?php

/**
 *   Controller Class for SunglassesQuiz Module
 *
 * @author Fayez.
 */
class ControllerModuleSunglassesQuiz extends Controller
{
    private $error = [];
    public $route = 'module/sunglasses_quiz';
    public $module = 'sunglasses_quiz';

    public function index()
    {

        if (!$this->isInstalled() || !$this->user->hasPermission("modify", "module/{$this->module}")) {
            return $this->forward('error/permission');
        }

        $this->load->model('localisation/language');
        $this->load->model('setting/setting');
        $this->load->language("module/{$this->module}");

        $this->data['breadcrumbs'] = [];
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

        $this->document->setTitle($this->language->get('heading_title'));
                
        $this->data['action'] = $this->url->link("module/{$this->module}/updateSettings", '', 'SSL');

        $fields = $this->getFormFields();
        $app_settings = $this->config->get($this->module);
        $this->reziseImageAndGetPath($app_settings);
        $this->data["{$this->module}_data"] = $app_settings;
        $this->data['languages'] = $languages = $this->model_localisation_language->getLanguages(); // append sys languages

        foreach ($fields as $field) {
            // get display field name for each language 
            foreach ($languages as $language) {
                $this->data["{$this->module}_{$field}_{$language['language_id']}"] = $settings["{$this->module}_{$field}_{$language['language_id']}"];
            }
        }

        // append products to data
        $this->load->model('catalog/product');
        $this->data['products'] = $this->model_catalog_product->getProductsFields(['name']);

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

        $post_data = $this->request->post;
        if ($this->validate($post_data)) {
            $this->load->model('setting/setting');

            if (isset($this->request->get['store_id'])) {
                $store_id = $this->request->get['store_id'];
            } else {
                $store_id = 0;
            }

            $this->model_setting_setting->editSetting(
                $this->module, [$this->module => $post_data]
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
     * @param array $data
     * @return boolean
     */
    private function validate($data)
    {

        $this->load->model('localisation/language');
        $this->load->language("module/{$this->module}");

        if (!$this->user->hasPermission('modify', "module/{$this->module}")) {
            $this->error[] = $this->language->get('error_permission');
            return false;
        }

        $fields = $this->getFormFields();
        $languages = $this->model_localisation_language->getLanguages();

        foreach ($fields as $field) {
            foreach ($languages as $language) {
                if (empty($data[$field . "_{$language['language_id']}"]) && (!$this->endsWith($field, '_products') && !$this->endsWith($field, '_img'))) {
                    return false;
                }
            }
        }

        return count($this->error) > 0 ? false : true;
    }


    /**
     * get form fields
     */
    private function getFormFields()
    {
        return [
            'step1_choice1', 'step1_choice2', 'step2_choice1', 'step2_choice2', 'step3_choice1', 'step3_choice2',
            'step1_choice1_img', 'step1_choice2_img', 'step2_choice1_img', 'step2_choice2_img', 'step3_choice1_img', 'step3_choice2_img',
            'step1_question', 'step1_title', 'step2_question', 'step2_title', 'step3_question', 'step3_title',
            'result111_products', 'result112_products', 'result121_products', 'result122_products', 'result211_products',
            'result212_products', 'result221_products', 'result222_products',
        ];
    }


    private function reziseImageAndGetPath(&$fields)
    {
        
        $this->load->model('tool/image');

        foreach ($fields as $key => $value) {

            if ($this->endsWith($key, '_img')) {
                $fields[$key . '_thumb'] = $this->model_tool_image->resize($value, 150, 150);
            }
        }

        $fields['no_image'] = $this->model_tool_image->resize('no_image.jpg', 150, 150);

        return $fields;
    }


    /**
     * check if string ends with substr
     * 
     * @param string $str
     * @param string $needle
     */
    private function endsWith($str, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        return (substr($str, -$length) === $needle);
    }
}