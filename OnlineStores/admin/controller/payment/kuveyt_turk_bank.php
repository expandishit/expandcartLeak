<?php

class ControllerPaymentKuveytTurkBank extends Controller {

    private $error = [],
            $paymentName = 'kuveyt_turk_bank';

    public function index() {

        $this->load->language("payment/{$this->paymentName}");
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');
        $this->load->model('localisation/order_status');
        $this->load->model('localisation/language');
        $this->load->model('localisation/geo_zone');


        if ($this->request->server['REQUEST_METHOD'] == 'POST') {

            if (!$this->validate()) {
                $result_json['success'] = '0';

                $result_json['errors'] = $this->error;

                $this->response->setOutput(json_encode($result_json));

                return;
            }

            $this->model_setting_setting->checkIfExtensionIsExists('payment', $this->paymentName, true);

            $this->model_setting_setting->insertUpdateSetting('kuveyt_turk_bank', $this->request->post);
            $this->tracking->updateGuideValue('PAYMENT');
            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

            $result_json['success'] = '1';
            $result_json['redirect'] = '1';
            $result_json['to'] = (string) $this->url->link("extension/payment/activate",'code=kuveyt_turk_bank&activated=1&payment_company=1', 'SSL');

            $this->response->setOutput(json_encode($result_json));

            return;
        }


        // ================== breadcrumbs =========================

        $this->data['breadcrumbs'] = array();

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
        $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $this->data['action'] = $this->url->link("payment/{$this->paymentName}", 'token=' . $this->session->data['token'], 'SSL');
        $this->data['cancel'] = $this->url->link("payment/{$this->paymentName}", 'token=' . $this->session->data['token'], 'SSL');

        // ================== /breadcrumbs =========================
        // ================== kuveyt_turk_bank variables =========================

        $kuveyt_turk_bankSettings = $this->model_setting_setting->getSetting('kuveyt_turk_bank');
        $languages = $this->model_localisation_language->getLanguages();

        $this->data['kuveyt_turk_bank_status'] = $this->config->get('kuveyt_turk_bank_merchant_idkuveyt_turk_bank_status');
        $this->data['kuveyt_turk_bank_customer_id'] = $this->config->get('kuveyt_turk_bank_customer_id');
        $this->data['kuveyt_turk_bank_merchant_id'] = $this->config->get('kuveyt_turk_bank_merchant_id');
        $this->data['kuveyt_turk_bank_username'] = $this->config->get('kuveyt_turk_bank_username');
        $this->data['kuveyt_turk_bank_password'] = $this->config->get('kuveyt_turk_bank_password');
        $this->data['kuveyt_turk_bank_order_status_id'] = $this->config->get('kuveyt_turk_bank_order_status_id');
        $this->data['entry_order_status_failed'] = $this->config->get('entry_order_status_failed');
        $this->data['kuveyt_turk_bank_geo_zone_id'] = $this->config->get('kuveyt_turk_bank_geo_zone_id');
        $this->data['kuveyt_turk_bank_testmode'] = $this->config->get('kuveyt_turk_bank_testmode');
        $this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
        $this->data['languages'] = $languages;

        foreach ($languages as $language) {
            $this->data['kuveyt_turk_bank_field_name_' . $language['language_id']] = $kuveyt_turk_bankSettings['kuveyt_turk_bank_field_name_' . $language['language_id']];
        }
        // ================== /kuveyt_turk_bank variables =========================

        $this->template = "payment/{$this->paymentName}.expand";

        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }

    private function validate() {
        if (!$this->user->hasPermission('modify', 'payment/kuveyt_turk_bank')) {
            $this->error['error'] = $this->language->get('error_permission');
        }
        
        if (!$this->request->post['kuveyt_turk_bank_customer_id']) {
            $this->error['kuveyt_turk_bank_customer_id'] = $this->language->get('error_api_url');
        }
        
        if (!$this->request->post['kuveyt_turk_bank_merchant_id']) {
            $this->error['kuveyt_turk_bank_merchant_id'] = $this->language->get('error_api_url');
        }
        
        if (!$this->request->post['kuveyt_turk_bank_username']) {
            $this->error['kuveyt_turk_bank_username'] = $this->language->get('error_api_url');
        }
        
        if (!$this->request->post['kuveyt_turk_bank_password']) {
            $this->error['kuveyt_turk_bank_password'] = $this->language->get('error_api_url');
        }
        
        if ($this->error && !isset($this->error['error'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return $this->error ? false : true;
    }

}
