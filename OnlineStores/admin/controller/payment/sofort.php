<?php

class ControllerPaymentSofort extends Controller
{
    
    private $error = array();

    public function index()
    {
        $this->initializer([
            'payment/sofort',
            'localisation/order_status',
            'localisation/currency',
        ]);

        $this->language->load('payment/sofort');

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
            'text'      => $this->language->get('sofort_heading_title'),
            'href'      => $this->url->link('payment/sofort', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $this->data['links'] = [
            'submit' => $this->url->link(
                'payment/sofort/update',
                'token=' . $this->session->data['token'],
                'SSL'
            ),
            'cancel' => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
            'callback' => HTTPS_CATALOG . 'index.php?route=payment/sofort/callback'
        ];

        $this->data['cancel'] = $this->url->link('payment/sofort', 'token=' . $this->session->data['token'], 'SSL');

        $this->data['settings'] = $this->sofort->getSettings();

        $this->data['order_statuses'] = $this->order_status->getOrderStatuses();
        $this->data['currencies'] = $this->currency->getCurrencies();

        $this->document->setTitle($this->language->get('sofort_heading_title'));

        $this->template = 'payment/sofort.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }


    private function validate()
    {

        if ( ! $this->user->hasPermission('modify', 'payment/sofort') )
        {
            $this->error['error'] = $this->language->get('error_permission');
        }

        $configKeyPattern = '#^\d+\:\d+\:[0-9a-z]+$#';

        if ( ! preg_match( $configKeyPattern, $this->request->post['sofort']['config_key'] ) )
        {
            $this->error['config_key'] = $this->language->get('invalid_config_key');
        }

        $supportedCurrencies = ['EUR', 'GBP', 'CHF', 'PLN', 'HUF', 'CZK'];

        $supportedCurrencies = array_flip($supportedCurrencies);

        if ( ! in_array($this->request->post['sofort']['default_currency'], $supportedCurrencies) )
        {
            $this->error['default_currency'] = $this->language->get('invalid_currency');
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
            'payment/sofort'
        ]);

        $this->language->load('payment/sofort');

        if ( ! $this->validate() )
        {
            $result_json['success'] = '0';
            $result_json['errors'] = $this->error;
            
            $this->response->setOutput(json_encode($result_json));
            
            return;
        }

        $this->load->model('setting/setting');

        $this->model_setting_setting->checkIfExtensionIsExists('payment', 'sofort', true);

        $settings = $this->request->post['sofort'];

        $this->sofort->updateSettings(array_merge($this->sofort->getSettings(), $settings));
                    $this->tracking->updateGuideValue('PAYMENT');

        $result_json['success'] = '1';
        
        $result_json['success_msg'] = $this->language->get('text_success');

        $this->response->setOutput(json_encode($result_json));
        
        return;
    }
}
