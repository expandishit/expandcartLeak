<?php

class ControllerModuleGoogleCaptcha extends Controller
{
    private $settings;
    protected $errors = [];
    
    public function index()
    {

        $this->load->model('module/google_captcha/settings');
        $this->language->load('module/google_captcha');

        $this->document->setTitle($this->language->get('google_captcha_heading_title'));

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_modules'),
            'href'      => $this->url->link(
                'marketplace/home',
                '',
                'SSL'
            ),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('google_captcha_heading_title'),
            'href'      => $this->url->link('module/google_captcha', '', 'SSL'),
            'separator' => ' :: '
        );


        $this->template = 'module/google_captcha/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );
        // get app settings
        $this->data['settingsData'] = $this->model_module_google_captcha_settings->getSettings();


        $fields = [
                    "client_login"=>"client_login",
                    "client_registration"=>"client_registration",
                    "client_contactus"=>"client_contactus",
                    "seller_contact"=>"seller_contact",
                    "admin_login"=>"admin_login"
                 ];

        if (isset($this->data['settingsData'])) {
            $this->data['fields'] = $this->request->post['google_captcha'];
        }

        $this->data['fields'] = (isset($this->data['settingsData']['fields'])) ? $this->data['settingsData']['fields'] : $fields;


        $this->data['action'] = $this->url->link('module/google_captcha/updateSettings', '', 'SSL');
        $this->data['cancel'] = $this->url->link('marketplace/home', '', "SSL");


        $this->response->setOutput($this->render());
    }

    public function updateSettings()
    {
        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)) {
             $result_json['success'] = '0';
             $result_json['errors'] = 'Invalid Request';
        }else{
            $this->language->load('module/google_captcha');

            if ( ! $this->validate() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->errors;

                $this->response->setOutput(json_encode($result_json));

                return;
            }
            $this->load->model('module/google_captcha/settings');

            $data = $this->request->post['google_captcha'];

            $this->model_module_google_captcha_settings->updateSettings(['google_captcha' => $data]);

            $this->session->data['success'] = $this->language->get('text_settings_success');

            $this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success');
            $result_json['success'] = '1';
        }
        $this->response->setOutput(json_encode($result_json));
        return;
    }


    private function validate()
    {
        $postData = $this->request->post['google_captcha'];
        if (empty($postData['site_key'])) {
            $this->errors['site_key'] = $this->language->get('error_entry_site_key');
        }
        if (empty($postData['secret_key'])) {
            $this->errors['secret_key'] = $this->language->get('error_entry_secret_key');
        }

        if ($this->errors && !isset($this->errors['error'])) {
            $this->errors['warning'] = $this->language->get('error_warning');
        }
        return $this->errors ? false : true;
    }
}
