<?php

class ControllerModuleGameball extends Controller
{
    protected $errors = [];

    protected $error ;

    public function index()
    {

        $this->load->model('module/gameball/settings');
        $this->language->load('module/gameball');

        $this->document->setTitle($this->language->get('gameball_heading_title'));

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
            'text'      => $this->language->get('gameball_heading_title'),
            'href'      => $this->url->link('module/gameball', '', 'SSL'),
            'separator' => ' :: '
        );


        $this->template = 'module/gameball/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );
        // get app settings
        $this->data['settingsData'] = $this->model_module_gameball_settings->getSettings();

        $this->data['action'] = $this->url->link('module/gameball/updateSettings', '', 'SSL');
        $this->data['cancel'] = $this->url->link('marketplace/home', '', 'SSL');


        $this->response->setOutput($this->render());
    }


    public function updateSettings()
    {
        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)) {
            $result_json['success'] = '0';
            $result_json['errors'] = 'Invalid Request';
        }else{

            if ( ! $this->validate() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->errors;

                $this->response->setOutput(json_encode($result_json));

                return;
            }

            $this->load->model('module/gameball/settings');
            $this->language->load('module/gameball');

            $data = $this->request->post['gameball'];

            $this->model_module_gameball_settings->updateSettings(['gameball' => $data]);

            $this->session->data['success'] = $this->language->get('text_settings_success');

            $this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success');
            $result_json['success'] = '1';
        }
        $this->response->setOutput(json_encode($result_json));
        return;
    }

    private function validate()
    {
        $postData = $this->request->post['gameball'];
        $this->language->load('module/gameball');


        if (  !isset($postData['live_apikey']) ||  empty($postData['live_apikey']) )
        {
            $this->errors['live_apikey'] = $this->language->get('error_live_apikey_required');
        }

        if (  !isset($postData['transaction_key']) ||  empty($postData['transaction_key']) )
        {
            $this->errors['transaction_key'] = $this->language->get('error_transaction_key_required');
        }

        if ( $this->errors && !isset($this->errors['error']) )
        {
            $this->errors['warning'] = $this->language->get('error_warning');
        }

        return $this->errors ? false : true;
    }
}
