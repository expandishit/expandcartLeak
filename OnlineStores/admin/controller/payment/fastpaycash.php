<?php

class ControllerPaymentFastpayCash extends Controller
{

    private $error = array();

    public function index()
    {
        $this->initializer([
            'payment/fastpaycash',
            'localisation/order_status'
        ]);

        $this->language->load('payment/fastpaycash');

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
            'text'      => $this->language->get('fastpay_heading_title'),
            'href'      => $this->url->link('payment/fastpaycash', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $this->data['links'] = [
            'submit' => $this->url->link(
                'payment/fastpaycash/update',
                'token=' . $this->session->data['token'],
                'SSL'
            ),
            'cancel' => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
            'callback' => $this->fronturl->link('payment/fastpaycash/callback', '', 'SSL'),
        ];

        $this->data['cancel'] = $this->url->link('payment/fastpaycash', 'token=' . $this->session->data['token'], 'SSL');

        $this->data['settings'] = $this->fastpaycash->getSettings();

        $this->data['order_statuses'] = $this->order_status->getOrderStatuses();

        $this->document->setTitle($this->language->get('fastpay_heading_title'));

        $this->template = 'payment/fastpaycash.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }


    private function validate()
    {

        if ( ! $this->user->hasPermission('modify', 'payment/fastpaycash') )
        {
            $this->error['error'] = $this->language->get('error_permission');
        }

        if ( ! $this->request->post['fastpaycash']['merchant_no'] )
        {
            $this->error['merchant_no'] = $this->language->get('error_field_cant_be_empty');
        }

        if ( ! $this->request->post['fastpaycash']['store_password'] )
        {
            $this->error['store_password'] = $this->language->get('error_field_cant_be_empty');
        }

        if ( $this->error && !isset($this->error['error']) )
        {
            $this->error['warning'] = $this->language->get('error_warning');
        }
        
        return $this->error ? false : true;

    }


    public function update()
    {
        $this->initializer([
            'payment/fastpaycash',
        ]);

        $this->language->load('payment/fastpaycash');

        $settings = $this->request->post['fastpaycash'];

        if ( ! $this->validate() )
        {
            
            $result_json['success'] = '0';
            
            $result_json['errors'] = $this->error;
            
            $this->response->setOutput(json_encode($result_json));
            
            return;
        }

        $this->load->model('setting/setting');

        $this->model_setting_setting->checkIfExtensionIsExists('payment', 'fastpaycash', true);

        $this->tracking->updateGuideValue('PAYMENT');

        $this->fastpaycash->updateSettings($settings);

        $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('message_settings_updated');

        $result_json['success'] = '1';
        
        $this->response->setOutput(json_encode($result_json));
        
        return;
    }
}
