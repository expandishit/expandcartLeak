<?php

class ControllerPaymentPayfortFort extends Controller {

    private $error = array();

    public function index() {
        $this->language->load('payment/payfort_fort');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        $this->load->model('localisation/order_status');

        if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {

            if ( ! $this->validate() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
                
                $this->response->setOutput(json_encode($result_json));
                
                return;
            }

            $this->tracking->updateGuideValue('PAYMENT');

            if(isset($this->request->post['payfort_fort_credit_card']) && isset($this->request->post['payfort_fort_status'])){
                $this->model_setting_setting->checkIfExtensionIsExists('payment', 'payfort_fort', true);
                 $this->model_setting_setting->insertUpdateSetting('payfort_fort', $this->request->post);
            }

            if(isset($this->request->post['payfort_fort_sadad']) && isset($this->request->post['payfort_fort_status'])){
               $this->model_setting_setting->checkIfExtensionIsExists('payment', 'payfort_fort_sadad', true);
                $sadad_post = $this->fixPostData($this->request->post, 'sadad');
                $this->model_setting_setting->insertUpdateSetting('payfort_fort_sadad', $sadad_post);
            }

            if(isset($this->request->post['payfort_fort_valu']) && isset($this->request->post['payfort_fort_status'])){
               $this->model_setting_setting->checkIfExtensionIsExists('payment', 'payfort_fort_valu', true);
                $valu_post = $this->fixPostData($this->request->post, 'valu');
                $this->model_setting_setting->insertUpdateSetting('payfort_fort_valu', $valu_post);
            }

            if(isset($this->request->post['payfort_fort_qpay']) && isset($this->request->post['payfort_fort_status'])){
               $this->model_setting_setting->checkIfExtensionIsExists('payment', 'payfort_fort_qpay', true);
                $qpay_post = $this->fixPostData($this->request->post, 'qpay');
                $this->model_setting_setting->insertUpdateSetting('payfort_fort_qpay', $qpay_post);
            }

            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

            $result_json['success'] = '1';
            
            $this->response->setOutput(json_encode($result_json));
            
            return;
        }

        // =================== breadcrumbs ========================

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
            'href' => $this->url->link('payment/payfort_fort', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $this->data['action'] = $this->url->link('payment/payfort_fort', 'token=' . $this->session->data['token'], 'SSL');

        $this->data['cancel'] = $this->url->link('payment/payfort_fort', 'token=' . $this->session->data['token'], 'SSL');

        // =================== /breadcrumbs ========================

        
        $url = new Url(HTTP_CATALOG, $this->config->get('config_secure') ? HTTPS_CATALOG : HTTP_CATALOG);

        $host_to_host_url = $url->link('payment/payfort_fort/response', '', 'SSL');

        $this->data['host_to_host_url'] = $host_to_host_url;

        $this->data['payfort_fort_entry_merchant_identifier'] = $this->config->get('payfort_fort_entry_merchant_identifier');
        $this->data['payfort_fort_entry_access_code'] = $this->config->get('payfort_fort_entry_access_code');
        $this->data['payfort_fort_entry_command'] = $this->config->get('payfort_fort_entry_command');
        $this->data['payfort_fort_entry_sandbox_mode'] = $this->config->get('payfort_fort_entry_sandbox_mode');
        $this->data['payfort_fort_entry_request_sha_phrase'] = $this->config->get('payfort_fort_entry_request_sha_phrase');
        $this->data['payfort_fort_entry_response_sha_phrase'] = $this->config->get('payfort_fort_entry_response_sha_phrase');
        $this->data['payfort_fort_entry_language'] = $this->config->get('payfort_fort_entry_language');
        $this->data['payfort_fort_entry_hash_algorithm'] = $this->config->get('payfort_fort_entry_hash_algorithm');
        $this->data['payfort_fort_order_status_id'] = $this->config->get('payfort_fort_order_status_id');
        $this->data['payfort_fort_entry_gateway_currency'] = $this->config->get('payfort_fort_entry_gateway_currency');
        $this->data['payfort_fort_debug'] = $this->config->get('payfort_fort_debug');
        $this->data['payfort_fort_order_placement'] = $this->config->get('payfort_fort_order_placement');
        $this->data['payfort_fort_sadad'] = $this->config->get('payfort_fort_sadad');
        $this->data['payfort_fort_valu'] = $this->config->get('payfort_fort_valu');
        $this->data['payfort_fort_qpay'] = $this->config->get('payfort_fort_qpay');
        $this->data['payfort_fort_credit_card'] = $this->config->get('payfort_fort_credit_card');
        $this->data['payfort_fort_cc_integration_type'] = $this->config->get('payfort_fort_cc_integration_type');
        $this->data['payfort_fort_status'] = $this->config->get('payfort_fort_status');
        $this->data['payfort_fort_sort_order'] = $this->config->get('payfort_fort_sort_order');
        $this->data['payfort_fort_sadad_sort_order'] = $this->config->get('payfort_fort_sadad_sort_order');
        $this->data['payfort_fort_valu_sort_order'] = $this->config->get('payfort_fort_valu_sort_order');
        $this->data['payfort_fort_qpay_sort_order'] = $this->config->get('payfort_fort_qpay_sort_order');
        
        $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $this->template = 'payment/payfort_fort.expand';

        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }

    protected function validate()
    {
        if ( ! $this->user->hasPermission('modify', 'payment/payfort_fort') )
        {
            $this->error['error'] = $this->language->get('error_permission');
        }

        if ( ! $this->request->post['payfort_fort_entry_merchant_identifier'] )
        {
            $this->error['payfort_fort_entry_merchant_identifier'] = $this->language->get('error_payfort_fort_entry_merchant_identifier');
        }
        
        if ( ! $this->request->post['payfort_fort_entry_access_code'] )
        {
            $this->error['payfort_fort_entry_access_code'] = $this->language->get('error_payfort_fort_entry_access_code');
        }
        
        if ( ! $this->request->post['payfort_fort_entry_request_sha_phrase'] )
        {
            $this->error['payfort_fort_entry_request_sha_phrase'] = $this->language->get('error_payfort_fort_entry_request_sha_phrase');
        }
        
        if ( ! $this->request->post['payfort_fort_entry_response_sha_phrase'] )
        {
            $this->error['payfort_fort_entry_response_sha_phrase'] = $this->language->get('error_payfort_fort_entry_response_sha_phrase');
        }
        
        if ( ! $this->request->post['payfort_fort_credit_card'] && ! $this->request->post['payfort_fort_sadad'] && $this->request->post['payfort_fort_status'] && ! $this->request->post['payfort_fort_qpay'] && ! $this->request->post['payfort_fort_valu'])
        {
            $this->error['warning'] = $this->language->get('payfort_fort_payment_method_required');
        }

        if ( $this->error && !isset($this->error['error']) && !isset($this->error['warning']) )
        {
            $this->error['warning'] = $this->language->get('error_warning');
        }
        
        return $this->error ? false : true;
    }

    public function install() {
            $this->load->model('setting/extension');
            $this->model_setting_extension->install('payment', 'payfort_fort_sadad');
            $this->model_setting_extension->install('payment', 'payfort_fort_valu');
            $this->model_setting_extension->install('payment', 'payfort_fort_qpay');
    }

    public function uninstall() {
            $this->load->model('setting/extension');
            $this->model_setting_extension->uninstall('payment', 'payfort_fort_sadad');
            $this->model_setting_extension->uninstall('payment', 'payfort_fort_valu');
            $this->model_setting_extension->uninstall('payment', 'payfort_fort_qpay');
            
            $this->load->model('setting/setting');
            $this->model_setting_setting->deleteSetting('payfort_fort_sadad');
            $this->model_setting_setting->deleteSetting('payfort_fort_valu');
            $this->model_setting_setting->deleteSetting('payfort_fort_qpay');
    }

    private function fixPostData($post, $code) {
            $newPost = array();
            foreach($post as $key => $value) {
                $newstr = substr_replace($key, '_'.$code, strlen('payfort_fort'), 0);
                if(isset($this->request->post[$newstr])) {
                    $newPost[$newstr] = $this->request->post[$newstr]; 
                }
                else{
                    $newPost[$newstr] = $value; 
                }
            }
            return $newPost;
    }
}

?>