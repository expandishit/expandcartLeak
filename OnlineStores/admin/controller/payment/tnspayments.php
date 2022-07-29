<?php

class ControllerPaymentTNSPayments extends Controller
{
    protected $availableProviders = [
        'tnspayment' => 'https://secure.ap.tnspayments.com/checkout/version/48/checkout.js',
        'bankofbeirut' => 'https://test-bobsal.gateway.mastercard.com/checkout/version/48/checkout.js',
        'alawalbank' => 'https://ap-gateway.mastercard.com/checkout/version/48/checkout.js',
        'credimax' => 'https://credimax.gateway.mastercard.com/checkout/version/55/checkout.js',
        'mastercard' => 'https://ap-gateway.mastercard.com/checkout/version/61/checkout.js',
        'banquemisr' => 'https://banquemisr.gateway.mastercard.com/checkout/version/55/checkout.js'
    ];

    public function index()
    {
        $this->language->load('payment/tnspayments');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_payment'),
            'href'      => $this->url->link('extension/payment', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('payment/payza', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->initializer([
            'geo' => 'localisation/geo_zone',
            'localisation/currency',
            'statuses' => 'localisation/order_status',
            'tns' => 'payment/tnspayments',
        ]);

        $this->data['geo_zones'] = $this->geo->getGeoZones();

        $this->data['currencies'] = $this->currency->getCurrencies();

        $this->data['order_statuses'] = $this->statuses->getOrderStatuses();

        $this->data['tns'] = $this->tns->getSettings();

        $this->data['availableProviders'] = $this->availableProviders;

        $this->template = 'payment/tnspayments.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
    }

    public function updateSettings()
    {
        if (
            $this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)
        ) {
            return 'error';
        }

        $data = $this->request->post['tns'];

        if (!isset($data['geo_zone_id'])) {
            $data['geo_zone_id'] = '0';
        }

        if (!isset($data['integeration'])) {
            $data['integeration'] = 'hosted';
        }

        $this->initializer([
            'tns' => 'payment/tnspayments',
        ]);

        $this->language->load('payment/tnspayments');

        if (!$this->tns->validate($data)) {
            $response['success'] = '0';
            $response['errors'] = $this->tns->getErrors();

            $this->response->setOutput(json_encode($response));

            return;
        }

        $this->load->model('setting/setting');

        $this->model_setting_setting->checkIfExtensionIsExists('payment', 'tnspayments', true);

        $this->tns->updateSettings(array_merge($this->tns->getSettings(), $data));

        $this->tracking->updateGuideValue('PAYMENT');

        $response['success_msg'] = $this->language->get('message_settings_updated');

        $response['success'] = '1';

        $this->response->setOutput(json_encode($response));

        return;
    }
}
