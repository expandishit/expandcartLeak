<?php

class ControllerPaymentGate2play extends Controller
{

    private $error = array();

    public function index()
    {
        $this->load->language('payment/gate2play');
        $this->document->setTitle($this->language->get('heading_title'));
        
        $this->load->model('setting/setting');

        $this->load->model('localisation/order_status');

        $data['heading_title'] = $this->language->get('heading_title');

        if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {

            if ( ! $this->validate() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
                
                $this->response->setOutput(json_encode($result_json));
                
                return;
            }

            $this->model_setting_setting->checkIfExtensionIsExists('payment', 'gate2play', true);

            $this->tracking->updateGuideValue('PAYMENT');

            
            if (!empty($this->request->post['activedBrands'])) {
                $activedBrands = explode(',', $this->request->post['activedBrands']);
                $deactivedBrands = explode(',', $this->request->post['deactivedBrands']);
                foreach ($activedBrands as $activedBrand)
                {
                    $activeBrandArray[strtoupper($activedBrand)] = [
                      'name' => $activedBrand,
                       'status' => 1 
                    ];
                }
                
                foreach ($deactivedBrands as $deactivedBrand)
                {
                    $deactivedBrandArray[strtoupper($deactivedBrand)] = [
                      'name' => $deactivedBrand,
                       'status' => 0 
                    ];
                }
                
                $this->request->post['gate2play_brands'] = array_merge($activeBrandArray,$deactivedBrandArray);
            } else {
                unset($this->request->post['activedBrands']);
                unset($this->request->post['deactivedBrands']);
            }
//$this->request->post['gate2play_brands'] = array(
//
//                'VISA'=>['name' => 'Visa', 'status' => 1],
//                'CASHU'=>['name' => 'CashU', 'status' => 0],
//                'PAYPAL'=>['name' => 'PayPal', 'status' => 0],
//                'MASTER'=>['name' => 'Master', 'status' => 1],
//                'DISCOVER'=>['name' => 'Discover', 'status' => 0],
//                'AMEX'=>['name' => 'Amex', 'status' => 0],
//                'MADA'=>['name' => 'Mada', 'status' => 1],
//                'APPLEPAY'=>['name' => 'ApplePay', 'status' => 0],
//                'STC_PAY'=>['name' => 'Stc_Pay', 'status' => 0]               
//            );
            $this->model_setting_setting->insertUpdateSetting('gate2play', $this->request->post);
            
            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

            $result_json['success'] = '1';
            
            $this->response->setOutput(json_encode($result_json));
            
            return;
        }

        // ============ breadcrumbs ===========
        
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_payment'),
            'href' => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('payment/gate2play', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        // ============ /breadcrumbs ===========

        //-------------------------------------------------------
        $data['action'] = $this->url->link('payment/gate2play', 'token=' . $this->session->data['token'], 'SSL');
        $data['cancel'] = $this->url->link('payment/gate2play', 'token=' . $this->session->data['token'], 'SSL');
//        $data['entry_all_trans_type'] = $this->get_gate2play_trans_type();
//        $data['entry_all_trans_mode'] = $this->get_gate2play_trans_mode();
//        $data['entry_all_brands'] = $this->get_gate2play_payment_methods();
        $data['entry_all_currencies'] = $this->get_all_currencies();
//        $data['entry_all_brands'] = $this->get_gate2play_payment_methods();
        $data['entry_all_payment_style'] = $this->get_gate2play_payment_style();
//        $data['gate2play_brands'] = $this->config->get('gate2play_brands');
        //-----------------------------------------------------------------------
        
        $activedBrands = array_filter($this->config->get('gate2play_brands'),function($var){
           return $var['status'] == 1;
        });
        
        $deactivedBrands = array_filter($this->config->get('gate2play_brands'),function($var){
           return $var['status'] == 0;
        });
        if (!$activedBrands){
            $activedBrands = array_filter($this->get_gate2play_payment_methods(),function($var){
                return $var['status'] == 1;
            });
        }
        if (!$deactivedBrands){
            $deactivedBrands = array_filter($this->get_gate2play_payment_methods(),function($var){
                return $var['status'] == 0;
            });
        }
        
        $data['activedBrands'] = $activedBrands;
        $data['deactivedBrands'] = $deactivedBrands;
        $data['activedBrandsNames'] = implode(',',array_column($activedBrands,'name'));
        $data['deactivedBrandsNames'] = implode(',',array_column($deactivedBrands,'name'));
        
        $fields = [ 'status', 'base_currency', 'testmode', 'trans_type', 'trans_mode', 'heading_title', 'channel', 'loginid', 'password', 'brands', 'payment_style', 'mailerrors', 'mailerrors_enable', 'admin_email', 'order_status_failed_id', 'order_status_id' , 'auth_token', 'mada_entity_id', 'amex_entity_id', 'sort_order'];

        $settings = $this->model_setting_setting->getSetting('gate2play');

        foreach ($fields as $field)
        {
            $data['gate2play_' . $field] = $settings['gate2play_' . $field];
        }

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
        $data['entry_order_status_failed'] =  $this->config->get('entry_order_status_failed');

        $this->data = $data;

        $this->template = 'payment/gate2play.expand';

        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render(TRUE));
    }


    private function validate()
    {
        if ( ! $this->user->hasPermission('modify', 'payment/gate2play') )
        {
           $this->error['error'] = $this->language->get('error_permission');
      }

        // if ( ! $this->request->post['gate2play_heading_title'] )
        // {
        //     $this->error['gate2play_heading_title'] = $this->language->get('error_heading_title');
        // }
        
        if ( ! $this->request->post['gate2play_channel'] )
        {
            $this->error['gate2play_channel'] = $this->language->get('error_channel');
        }

        if (in_array('MADA', $this->request->post['gate2play_brands'])) {
            if ( ! $this->request->post['gate2play_mada_entity_id'] )
            {
                $this->error['gate2play_mada_entity_id'] = $this->language->get('error_mada_entity_id');
            }
        }
        
        if ( ! $this->request->post['gate2play_auth_token'] )
        {
            $this->error['gate2play_auth_token'] = $this->language->get('error_channel');
        }
        
        // if ( ! $this->request->post['gate2play_loginid'] )
        // {
        //     $this->error['gate2play_loginid'] = $this->language->get('error_loginid');
        // } 
        
        // if ( ! $this->request->post['gate2play_password'] )
        // {
        //     $this->error['gate2play_password'] = $this->language->get('error_password');
        // }

        if ( $this->error && !isset($this->error['error']) )
        {
            $this->error['warning'] = $this->language->get('error_warning');
        }
        
        return $this->error ? false : true;
    }
    
    
    private function get_gate2play_payment_methods() {

        $gate2play_payments = array(

            'VISA'=>['name' => 'Visa', 'status' => 1],
            'CASHU'=>['name' => 'CashU', 'status' => 0],
            'PAYPAL'=>['name' => 'PayPal', 'status' => 0],
            'MASTER'=>['name' => 'Master', 'status' => 1],
            'DISCOVER'=>['name' => 'Discover', 'status' => 0],
            'AMEX'=>['name' => 'Amex', 'status' => 0],
            'MADA'=>['name' => 'Mada', 'status' => 1],
            'APPLEPAY'=>['name' => 'ApplePay', 'status' => 0],
            'STC_PAY'=>['name' => 'Stc_Pay', 'status' => 0]
        );

        return $gate2play_payments;
    }     
    
    
    private function get_gate2play_trans_mode() {
        $gate2play_trans_mode = array(
                'CONNECTOR_TEST' => 'CONNECTOR_TEST',
                'INTEGRATOR_TEST' => 'INTEGRATOR_TEST',
                'LIVE' => 'LIVE'       
        );  

        return $gate2play_trans_mode;
    }      
    
    private function get_gate2play_trans_type() {
        $gate2play_trans_type = array(
                'DB' => 'Debit',
                'PA' => 'Pre-Authorization'     
        );  

        return $gate2play_trans_type;
    }     
    
    private function get_gate2play_payment_style() {
        $gate2play_payment_style = array(
                'card' => 'Card',
                'plain' => 'Plain'     
        );  

        return $gate2play_payment_style;
    }

   private function get_all_currencies(){
	$this->load->model('localisation/currency');
	$currencyArray = [];
	$currencyArray = $this->model_localisation_currency->getCurrencies();
        $all = [];
	foreach( $currencyArray as $currency){
		
                   $all[$currency['code']] = $currency['code'];
                
	}
     return $all;

   }                     
    
}

?>
