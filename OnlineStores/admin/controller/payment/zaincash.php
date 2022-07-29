<?php
class ControllerPaymentZainCash extends Controller
{
  private $error = array();
 
  public function index()
  {
  
    $this->load->language('payment/zaincash');
    $this->document->setTitle( $this->language->get('heading_title') );
    $this->load->model('setting/setting');
 
    $this->data['breadcrumbs'] = array();

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
          'text'      => $this->language->get('heading_title'),
          'href'      => $this->url->link('payment/zaincash', 'token=' . $this->session->data['token'], 'SSL'),
          'separator' => ' :: '
      );

    if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
    {
      
        if ( ! $this->validate() )
        {
            $result_json['success'] = '0';
            $result_json['errors'] = $this->error;
            
            $this->response->setOutput(json_encode($result_json));
            
            return;
        }

      $this->model_setting_setting->checkIfExtensionIsExists('payment', 'zaincash', true);

      $this->model_setting_setting->insertUpdateSetting('zaincash', $this->request->post);

      $this->tracking->updateGuideValue('PAYMENT');

      $result_json['success'] = '1';

      $result_json['success_msg'] = $this->language->get('text_success');

      $this->response->setOutput(json_encode($result_json));
      
      return;
      
    }
 
    $this->data['action'] = $this->url->link('payment/zaincash', 'token=' . $this->session->data['token'], 'SSL');
    $this->data['cancel'] = $this->url->link('payment/zaincash', 'token=' . $this->session->data['token'], 'SSL');
 
    
    $this->data['text_merchantid'] = $this->config->get('text_merchantid');
    
    $this->data['text_merchantsecret'] = $this->config->get('text_merchantsecret');
    
    $this->data['text_merchantmsisdn'] = $this->config->get('text_merchantmsisdn');

    $this->data['text_isdollar'] = $this->config->get('text_isdollar');

    $this->data['text_dollarprice'] = $this->config->get('text_dollarprice');
        
    $this->data['text_testcred'] = $this->config->get('text_testcred');
        
    $this->data['zaincash_status'] = $this->config->get('zaincash_status');
        
    $this->template = 'payment/zaincash.expand';
            
    $this->children = array(
      'common/header',
      'common/footer'
    );
 
    $this->response->setOutput($this->render());
  }

  private function validate()
  {

  	if ( ! $this->user->hasPermission('modify', 'payment/zaincash') )
    {
		$this->error['error'] = $this->language->get('error_permission');
	}

  	if ( ! $this->request->post['text_merchantid'] )
  	{
  		$this->error['text_merchantid'] = $this->language->get('error_field_cant_be_empty');
  	}

  	if ( ! $this->request->post['text_merchantsecret'] )
  	{
  		$this->error['text_merchantsecret'] = $this->language->get('error_field_cant_be_empty');
  	}

  	if ( ! $this->request->post['text_merchantmsisdn'] )
  	{
  		$this->error['text_merchantmsisdn'] = $this->language->get('error_field_cant_be_empty');
  	}

    if($this->request->post['text_isdollar'] == 1){
      if ( ! $this->request->post['text_dollarprice'] )
      {
        $this->error['text_dollarprice'] = $this->language->get('error_field_cant_be_empty');
      }
    }
  	
  	if ( $this->error && !isset($this->error['error']) )
  	{
  	    $this->error['warning'] = $this->language->get('error_warning');
  	}
  	
  	return $this->error ? false : true;

  }

}
