<?php

class ControllerModuleQoyodIntegration extends Controller
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

            $this->$object = $this->{"model_" . $model};
        }

        if (isset($this->session->data['errors'])) {
            $this->data['error_warning'] = implode('</br >', $this->session->data['errors']);

            unset($this->session->data['errors']);
        }

        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        }

        $this->language->load('module/qoyod_integration');
    }

    public function index()
    {

        $this->init([
            'module/qoyod_integration/settings'
        ]);

        if (isset($this->request->post['qoyod'])) {
            $data['settings'] = $this->request->post['qoyod'];
        }
        elseif ($this->config->get('qoyod')) {
            $data['settings'] = $this->config->get('qoyod');

            if(!empty($data['settings']['api_key']) && $data['settings']['api_key']){
                // GET INVENTORIES
                $inventoriesData = $this->sendCurlRequest("https://www.qoyod.com/api/2.0/inventories",$data['settings']['api_key']);
                $inventories = json_decode($inventoriesData)->inventories;
                foreach($inventories as $inventory){
                    $this->data['inventories'][] = $inventory;
                }
                // GET CUSTOMERS
                $customersData = $this->sendCurlRequest("https://www.qoyod.com/api/2.0/customers",$data['settings']['api_key']);
                $customers = json_decode($customersData)->customers;
                foreach($customers as $customer){
                    $this->data['customers'][] = $customer;
                }
            }
        }

        $data['selected_order_statuses'] = $data['settings']['order_statuses'];
        
        $this->load->model("localisation/order_status");
        $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $this->document->setTitle($this->language->get('qoyod_integration_heading_title'));

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
            'text'      => $this->language->get('qoyod_integration_heading_title'),
            'href'      => $this->url->link('module/qoyod_integration', '', 'SSL'),
            'separator' => ' :: '
        );
        
        $data['entry_status'] = $this->language->get('entry_status');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_settings'] = $this->language->get('text_settings');

        $this->template = 'module/qoyod_integration/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $data['action'] = $this->url->link('module/qoyod_integration/updateSettings', '', 'SSL');
        $data['cancel'] = $this->url.'marketplace/home';

        $this->data = array_merge($this->data, $data);

        $this->response->setOutput($this->render());
    }

    public function updateSettings()
    {
        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)) {
             $result_json['success'] = '0';
             $result_json['error'] = $this->error;
        }else{
            $this->init([
                'module/qoyod_integration/settings'
            ]);

            $data = $this->request->post['qoyod'];

            $this->settings->updateSettings(['qoyod' => $data]);

            $this->session->data['success'] = $this->language->get('text_settings_success');

            $this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success');
            $result_json['success'] = '1';
        }
        $this->response->setOutput(json_encode($result_json));
        return;
    }

    public function install()
    {
        $this->init(['module/qoyod_integration/settings']);

        $this->settings->install();
    }

    public function uninstall()
    {
        $this->init(['module/qoyod_integration/settings']);

        $this->settings->uninstall();
    }

    /**
     * @param $_url
     * @param $data
     */
    private function sendCurlRequest($_url, $data)
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "API-KEY: ".$data.""
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

}
