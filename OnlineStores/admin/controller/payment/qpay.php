<?php

class ControllerPaymentQpay extends Controller {

    public function index() {

        $this->load->language('payment/qpay');
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

            $this->model_setting_setting->checkIfExtensionIsExists('payment', 'qpay', true);

            $this->model_setting_setting->insertUpdateSetting('qpay', $this->request->post);
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
            'href' => $this->url->link('payment/qpay', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        // ================== ottu variables =========================
        $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $this->data['action'] = $this->url->link('payment/qpay', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['cancel'] = $this->url->link('payment/qpay', 'token=' . $this->session->data['token'], 'SSL');

        $qpaySettings = $this->model_setting_setting->getSetting('qpay');
        $languages = $this->model_localisation_language->getLanguages();

        $this->data['qpay_status'] = $this->config->get('qpay_status');
        $this->data['qpay_testmode'] = $this->config->get('qpay_testmode');
        $this->data['entry_gid'] = $this->config->get('entry_gid');
        $this->data['entry_secret_key'] = $this->config->get('entry_secret_key');
        $this->data['qpay_total'] = $this->config->get('qpay_total');
        $this->data['qpay_geo_zone_id'] = $this->config->get('qpay_geo_zone_id');
        $this->data['qpay_sort_order'] = $this->config->get('qpay_sort_order');
        $this->data['qpay_order_status_id'] = $this->config->get('qpay_order_status_id');
        $this->data['entry_order_status_failed'] = $this->config->get('entry_order_status_failed');
        $this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
        $this->data['languages'] = $languages;

        foreach ($languages as $language) {
            $this->data['qpay_field_name_' . $language['language_id']] = $qpaySettings['qpay_field_name_' . $language['language_id']];
        }
        // ================== /ottu variables =========================

        $this->template = 'payment/qpay.expand';

        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }

    private function validate() {
        if (!$this->user->hasPermission('modify', 'payment/qpay')) {
            $this->error['error'] = $this->language->get('error_permission');
        }

        if (!$this->request->post['entry_gid']) {
            $this->error['error_gid'] = $this->language->get('error_gid');
        }

        if (!$this->request->post['entry_secret_key']) {
            $this->error['entry_secret_key'] = $this->language->get('entry_secret_key');
        }

        if ($this->error && !isset($this->error['error'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return $this->error ? false : true;
    }

}
