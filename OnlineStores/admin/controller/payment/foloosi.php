<?php

class ControllerPaymentFoloosi extends Controller {

    private $error = array();

    public function index() {
        $this->language->load('payment/foloosi');

        $this->load->model('localisation/order_status');

        $this->load->model('localisation/geo_zone');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');


        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {

            if ( ! $this->validate() )
            {

                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
                $this->response->setOutput(json_encode($result_json));

                return;

            }
            $this->model_setting_setting->checkIfExtensionIsExists('payment', 'foloosi', true);
            $this->tracking->updateGuideValue('PAYMENT');
            $this->model_setting_setting->insertUpdateSetting('foloosi', $this->request->post);

            $result_json['success'] = '1';

            $result_json['success_msg'] = $this->language->get('text_success');

            $this->response->setOutput(json_encode($result_json));

            return;

            //$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
        }


        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('extension/payment')
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('payment/foloosi')
        );


        $this->data['heading_title'] = $this->language->get('heading_title');
        $this->data['text_edit'] = $this->language->get('text_edit');
        $this->data['text_all_zones'] = $this->language->get('text_all_zones');
        $this->data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
        $this->data['entry_merchant_key'] = $this->language->get('entry_merchant_key');
        $this->data['entry_secret_key'] = $this->language->get('entry_secret_key');
        $this->data['entry_order_status'] = $this->language->get('entry_order_status');
        $this->data['entry_status'] = $this->language->get('entry_status');
        $this->data['entry_sort_order'] = $this->language->get('entry_sort_order');

        $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();


        $this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();



        $this->data['action'] = $this->url->link('payment/foloosi', 'token=' . $this->session->data['token'], 'SSL');

        $this->data['cancel'] = $this->url->link('payment/foloosi', 'token=' . $this->session->data['token'], 'SSL');

        if (isset($this->request->post['payment_foloosipay_merchant_key'])) {
            $this->data['payment_foloosipay_merchant_key'] = $this->request->post['payment_foloosipay_merchant_key'];
        } else {
            $this->data['payment_foloosipay_merchant_key'] = $this->config->get('payment_foloosipay_merchant_key');
        }

        if (isset($this->request->post['payment_foloosipay_secret_key'])) {
            $this->data['payment_foloosipay_secret_key'] = $this->request->post['payment_foloosipay_secret_key'];
        } else {
            $this->data['payment_foloosipay_secret_key'] = $this->config->get('payment_foloosipay_secret_key');
        }

        if (isset($this->request->post['payment_foloosipay_order_status_id'])) {
            $this->data['payment_foloosipay_order_status_id'] = $this->request->post['payment_foloosipay_order_status_id'];
        } else {
            $this->data['payment_foloosipay_order_status_id'] = $this->config->get('payment_foloosipay_order_status_id');
        }

        if (isset($this->request->post['foloosi_status'])) {
            $this->data['foloosi_status'] = $this->request->post['foloosi_status'];
        } else {
            $this->data['foloosi_status'] = $this->config->get('foloosi_status');
        }

        if (isset($this->request->post['payment_foloosipay_sort_order'])) {
            $this->data['payment_foloosipay_sort_order'] = $this->request->post['payment_foloosipay_sort_order'];
        } else {
            $this->data['payment_foloosipay_sort_order'] = $this->config->get('payment_foloosipay_sort_order');
        }

        $this->template = 'payment/foloosipay.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );
        $this->response->setOutput($this->render());
    }
    protected function validate() {

        if ( ! $this->user->hasPermission('modify', 'payment/foloosi') )
        {
            $this->error['error'] = $this->language->get('error_permission');
        }

        if (!$this->request->post['payment_foloosipay_merchant_key']) {
            $this->error['payment_foloosipay_merchant_key'] = $this->language->get('error_key_id');
        }

        if (!$this->request->post['payment_foloosipay_secret_key']) {
            $this->error['payment_foloosipay_secret_key'] = $this->language->get('error_key_secret');
        }

        if ( $this->error && !isset($this->error['error']) )
        {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return $this->error ? false : true;

    }

}

?>