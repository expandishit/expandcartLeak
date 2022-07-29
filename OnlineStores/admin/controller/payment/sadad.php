<?php

class ControllerPaymentSadad extends Controller
{

    /**
     * errors bag
     */
    private $error = array();

    /**
     * payment name
     * 
     * will be used as a unified name for all related files calls
     */
    private $paymentName = 'sadad';

    /**
     * index function that listens for payment render call and saving data
     */
    public function index()
    {

        $this->language->load("payment/{$this->paymentName}");

        $this->load->model('setting/setting');
        $this->load->model('localisation/geo_zone');
        $this->load->model('localisation/order_status');
        $this->load->model('localisation/language');


        // set document tilte
        $this->document->setTitle($this->language->get('heading_title'));

        // set page breadcrumbs
        $this->setBreadcrumbs($this->data);
        
        // set form action url
        $this->data['action'] = $this->url->link("payment/{$this->paymentName}/savePaymentData", 'token=' . $this->session->data['token'], 'SSL');

        // set cancellation url
        $this->data['cancel'] = $this->url->link("payment/{$this->paymentName}", 'token=' . $this->session->data['token'], 'SSL');

        // form fields
        $fields = $this->getFormFields();

        $settings = $this->model_setting_setting->getSetting("{$this->paymentName}");
 
        foreach ($fields as $field) {
            $this->data["{$this->paymentName}_{$field}"] = $settings["{$this->paymentName}_{$field}"];
        }

        $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();


        // get payment settings
        $settings = $this->model_setting_setting->getSetting("{$this->paymentName}");

        // append sys languages
        $this->data['languages'] = $languages = $this->model_localisation_language->getLanguages();

        // get payment display field name for each language 
        foreach ($languages as $language) {
            $this->data["{$this->paymentName}_field_name_{$language['language_id']}"] = $settings["{$this->paymentName}_field_name_{$language['language_id']}"];
        }

        $this->template = "payment/{$this->paymentName}.expand";
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }


    /**
     * validate post data 
     * 
     * @return bool
     */
    private function validate()
    {

        $this->language->load("payment/{$this->paymentName}");

        if (!$this->user->hasPermission('modify', "payment/{$this->paymentName}")) {
            $this->error['error'] = $this->language->get('error_permission');
        }

        if (!$this->request->post["{$this->paymentName}_token"]) {
            $this->error["{$this->paymentName}_token"] = $this->language->get('error_token');
        }

        return $this->error ? false : true;
    }


    /**
     * append breadcrumbs to data
     * 
     * @param &data array
     */
    private function setBreadcrumbs(&$data)
    {
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_payment'),
            'href' => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link("payment/{$this->paymentName}", 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

    }



    /**
     * handle payment save(POST) call
     */
    public function savePaymentData()
    {
        if (!$this->validate()) {
            $result['success'] = '0';
            $result['errors'] = $this->error;

            $this->response->setOutput(json_encode($result));

            return;
        }

        // save data into db
        $result = $this->savePaymentIntoDB($this->request->post);

        $this->response->setOutput(json_encode($result));

        return;
    }



    /**
     * @save data into DB
     * 
     * @return json
     */
    private function savePaymentIntoDB($data) 
    {

        $this->load->model('setting/setting');

        $result = [];
        $this->model_setting_setting->checkIfExtensionIsExists('payment', "{$this->paymentName}", true);

        $this->model_setting_setting->editGuideValue('GETTING_STARTED', 'PAYMENT', '1');

        $this->model_setting_setting->insertUpdateSetting("{$this->paymentName}", $data);
        $this->session->data['success'] = $result['success_msg'] = $this->language->get('text_success');

        $result['success'] = '1';

        return $result;
    }



    /**
     * get payment form fields
     */
    private function getFormFields()
    {
        return [
            'status', 'test_mode', 'token', 'completed_order_status_id', 'geo_zone_id', 'faild_order_status_id', 'category'
        ];
    }
    
}
