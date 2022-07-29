<?php
// -----------------------------
// Integration With Trustrol
// -----------------------------
class ControllerModuleTrustrol extends Controller {
	private $error = array();
	private $_name = 'trustrol';

	public function index() {
        $this->load->model('marketplace/common');
        $market_appservice_status = $this->model_marketplace_common->hasApp('trustrol');
        if (!$market_appservice_status['hasapp']) {
            $this->redirect($this->url->link('marketplace/app', 'id=' . $market_appservice_status['appserviceid'], 'SSL'));
            return;
        }
		$this->load->language('module/' . $this->_name);

		$this->document->setTitle($this->language->get('heading_title'));
				
		$this->load->model('setting/setting');
		
		if ($this->request->server['REQUEST_METHOD'] == 'POST') {

			if ( $this->validate() )
            {
                $this->model_setting_setting->insertUpdateSetting($this->_name, $this->request->post);		
                $this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success');
                $result_json['success'] = '1';
            }
            else
            {
                $result_json['success'] = '0';
                $result_json['error'] = $this->error;
            }

            $this->response->setOutput(json_encode($result_json));
            return;
		}
		
		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
    		
		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');
		
 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}
		
        if (isset($this->request->post['trustrol_pixel'])) {
            $this->data['trustrol_pixel'] = $this->request->post['trustrol_pixel'];
        } else {
            $this->data['trustrol_pixel'] = $this->config->get('trustrol_pixel');
        }

        if (isset($this->request->post['status'])) {
            $this->data['status'] = $this->request->post['status'];
        } else {
            $this->data['status'] = $this->config->get('status');
        }
        
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', '', 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_module'),
			'href'      => $this->url->link('marketplace/home', '', 'SSL'),
      		'separator' => ' :: '
   		);
		
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('module/' . $this->_name, '', 'SSL'),
      		'separator' => ' :: '
   		);
		
		$this->data['action'] = $this->url->link('module/' . $this->_name, '', 'SSL');
		
		$this->data['cancel'] = $this->url.'marketplace/home'.'';
		
		$this->data['templates'] = array();

		if (isset($this->request->post[$this->_name . '_module'])) {
			$this->data['modules'] = $this->request->post[$this->_name . '_module'];
		} elseif ($this->config->get($this->_name . '_module')) { 
			$this->data['modules'] = $this->config->get($this->_name . '_module');
		}
	
		$this->template = 'module/' . $this->_name . '.expand';
		$this->children = array(
			'common/header',
			'common/footer',
		);

		$this->response->setOutput($this->render());
	}
	
	private function validate() {
		if (!$this->user->hasPermission('modify', 'module/' . $this->_name)) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->error) {
			return TRUE;
		} else {
			return FALSE;
		}	
	}
}
?>
