<?php

class ControllerPaymentOttu extends Controller {

    private $error = array();

    public function index() {

        $this->load->language('payment/ottu');
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

            $this->model_setting_setting->checkIfExtensionIsExists('payment', 'ottu', true);

            $this->model_setting_setting->insertUpdateSetting('ottu', $this->request->post);

            $this->tracking->updateGuideValue('PAYMENT');

            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

            $result_json['success'] = '1';

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
            'href' => $this->url->link('payment/ottu', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );
        $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $this->data['action'] = $this->url->link('payment/ottu', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['cancel'] = $this->url->link('payment/ottu', 'token=' . $this->session->data['token'], 'SSL');

        // ================== /breadcrumbs =========================
        // ================== ottu variables =========================

        $ottuSettings = $this->model_setting_setting->getSetting('ottu');
        $languages = $this->model_localisation_language->getLanguages();

        $this->data['ottu_status'] = $this->config->get('ottu_status');
        $this->data['ottue_api_url'] = $this->config->get('ottue_api_url');
        $this->data['ottu_knet_getway_code'] = $this->config->get('ottu_knet_getway_code');
        $this->data['ottu_cct_getway_code'] = $this->config->get('ottu_cct_getway_code');

        $this->data['ottu_order_status_id'] = $this->config->get('ottu_order_status_id');
        $this->data['entry_order_status_failed'] = $this->config->get('entry_order_status_failed');
        $this->data['ottu_geo_zone_id'] = $this->config->get('ottu_geo_zone_id');
        $this->data['ottu_testmode'] = $this->config->get('ottu_testmode');
        $this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
        $this->data['languages'] = $languages;

        foreach ($languages as $language) {
            $this->data['ottu_field_name_' . $language['language_id']] = $ottuSettings['ottu_field_name_' . $language['language_id']];
        }
        // ================== /ottu variables =========================

        $this->template = 'payment/ottu.expand';

        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }

    private function validate() {
        if (!$this->user->hasPermission('modify', 'payment/ottu')) {
            $this->error['error'] = $this->language->get('error_permission');
        }

        if (!$this->request->post['ottue_api_url']) {
            $this->error['ottue_api_url'] = $this->language->get('error_api_url');
        }
        if (!$this->request->post['ottu_knet_getway_code']) {
            $this->error['ottu_knet_getway_code'] = $this->language->get('error_ottu_knet_getway_code');
        }
        if (!$this->request->post['ottu_cct_getway_code']) {
            $this->error['ottu_cct_getway_code'] = $this->language->get('error_ottu_cct_getway_code');
        }

        if ($this->error && !isset($this->error['error'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return $this->error ? false : true;
    }

}
