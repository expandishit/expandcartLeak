<?php

use ExpandCart\Foundation\Support\Factories\PayPal\Webhook;

class ControllerPaymentPPPLus extends Controller
{
    protected $pp_plus;

    protected $order_status;

    private $error = array();

    private function init($models)
    {
        foreach ($models as $model) {

            $this->load->model($model);

            $object = explode('/', $model);
            $object = end($object);

            $model = str_replace('/', '_', $model);

            $this->$object = $this->{"model_" . $model};
        }
    }


    public function index()
    {

        $this->language->load('payment/pp_plus');

        $this->init([
            'payment/pp_plus',
            'localisation/order_status'
        ]);

        // ========================= breadcrumbs ============================

        $this->data['breadcrumbs']   = array();
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_payment'),
            'href'      => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('pp_plus_heading_title'),
            'href'      => $this->url->link('payment/pp_plus', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $this->data['cancel'] = $this->url->link('payment/pp_plus', 'token=' . $this->session->data['token'], 'SSL');

        $this->data['links'] = [
            'submit' => $this->url->link(
                'payment/pp_plus/update',
                'token=' . $this->session->data['token'],
                'SSL'
            ),
            'createWebhook' => $this->url->link(
                'payment/pp_plus/createWebhook',
                'token=' . $this->session->data['token'],
                'SSL'
            ),
            'callback' => HTTPS_CATALOG . 'index.php?route=payment/pp_plus/callback'
        ];

        $this->data['settings'] = $this->pp_plus->getSettings();

        $this->data['order_statuses'] = $this->order_status->getOrderStatuses();

        // ========================= /breadcrumbs ============================

        $this->template = 'payment/pp_plus.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }


    private function validate()
    {
        if ( ! $this->user->hasPermission('modify', 'payment/pp_plus') )
        {
            $this->error['error'] = $this->language->get('error_permission');
        }

        if ( ! $this->request->post['pp_plus']['client_id'] )
        {
            $this->error['pp_plus_client_id'] = $this->language->get('error_field_cant_be_empty');
        }

        if ( ! $this->request->post['pp_plus']['client_secret'] )
        {
            $this->error['pp_plus_client_secret'] = $this->language->get('error_field_cant_be_empty');
        }

        if ( ! $this->request->post['pp_plus']['webhook'] )
        {
            $this->error['pp_plus_webhook'] = $this->language->get('error_field_cant_be_empty');
        }

        if ( $this->error && !isset($this->error['error']) )
        {
            $this->error['warning'] = $this->language->get('error_warning');
        }
        
        return $this->error ? false : true;
    }


    public function update()
    {
        $this->language->load('payment/pp_plus');

        $this->init([
            'payment/pp_plus'
        ]);

        $settings = $this->request->post['pp_plus'];

        if (!$this->validate()) {
            $result_json['success'] = '0';
            $result_json['errors'] = $this->error;
            
            $this->response->setOutput(json_encode($result_json));
            
            return;
        }

        $this->load->model('setting/setting');

        $this->model_setting_setting->checkIfExtensionIsExists('payment', 'pp_plus', true);

                    $this->tracking->updateGuideValue('PAYMENT');

        $this->pp_plus->updateSettings(array_merge($this->pp_plus->getSettings(), $settings));

        $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('message_settings_updated');

        $result_json['success'] = '1';
        
        $this->response->setOutput(json_encode($result_json));
        
        return;
    }

    /**
     *
     * @deprecated
     *
     */
    public function createWebhook()
    {
        exit;
        $this->init([
            'payment/pp_plus',
        ]);

        $settings = $this->pp_plus->getSettings();

        $webhook = new Webhook;

        $webhook->setMode($settings['test_mode']);

        $webhook->setContext([
            'client_id' => $settings['client_id'],
            'client_secret' => $settings['client_secret'],
        ])->setContextConfig([]);

        if (isset($settings['webhook']) && $settings['webhook'] != '') {

            $webhookData = $webhook->setWebhookId($settings['webhook'])->get();

            if (isset($webhookData->id)) {
                $this->session->data['errors'] = [$this->language->get('error_webhook_is_already_exists')];
            } else {
                $this->session->data['errors'] = [$this->language->get('error_invalid_webhook_id')];
            }

            $this->redirect(
                $this->url->link(
                    'payment/pp_plus',
                    'token=' . $this->session->data['token'], 'SSL'
                )
            );
        }

        $baseUrl = HTTPS_CATALOG;

        $webhook->setUrl($baseUrl . 'index.php?route=payment/pp_plus/callback');

        $webhook
            ->addEventType('PAYMENT.SALE.COMPLETED')
            ->addEventType('PAYMENT.SALE.DENIED')
            ->addEventType('PAYMENT.SALE.PENDING')
            ->addEventType('PAYMENT.SALE.REFUNDED')
            ->addEventType('PAYMENT.SALE.REVERSED');


        $out = $webhook->create();

        if (isset($out->id)) {
            $this->pp_plus->updateSettings(array_merge($settings, ['webhook' => $out->id]));

            $this->session->data['success'] = $this->language->get('webhook_created_successfully');

            $this->redirect(
                $this->url->link(
                    'payment/pp_plus',
                    'token=' . $this->session->data['token'], 'SSL'
                )
            );

        }
    }
}
