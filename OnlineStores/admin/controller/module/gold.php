<?php
class ControllerModuleGold extends Controller {
	private $error = array(); 
	private $_name = "gold";
	public function index() {
        $this->load->model('marketplace/common');

        $market_appservice_status = $this->model_marketplace_common->hasApp('gold');
        if (!$market_appservice_status['hasapp']) {
            $this->redirect($this->url->link('marketplace/app', 'id=' . $market_appservice_status['appserviceid'], 'SSL'));
            return;
        }

		$this->language->load('module/' . $this->_name );

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');

        $this->initializer(['module/gold']);

		if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {
            if ( !$this->validate() )
            {
                $result_json['success'] = '0';
                $result_json['error'] = $this->error;
            }
            else
            {
                $data = $this->request->post;
                $setting = ['gold' => $data['gold']];

                $data_calibers = $data['caliber'];

                //Update settings data
                $this->model_setting_setting->insertUpdateSetting($this->_name, $setting);
                //Update DB calibers data
                $this->gold->insertUpdate($data_calibers);

                $result_json['success_msg'] = $this->language->get('text_success');
                $result_json['success'] = '1';
            }

            $this->response->setOutput(json_encode($result_json));
			return;
		}
				
		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');

		$this->data['entry_status'] = $this->language->get('entry_status');
		
		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');
		
 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

 		//View data
        $this->data['defaultCurrency'] = $this->config->get('config_currency');
        $this->data['gold'] = $this->config->get('gold');

 		$this->data['calibers'] = $this->gold->getCalibers();

        $this->load->model('localisation/language');
        $this->data['languages'] = $this->model_localisation_language->getLanguages();
        //////////////

        $this->data['breadcrumbs'] = array();

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

        $this->data['cancel'] = $this->url->link('marketplace/home', '', 'SSL');

						
		$this->template = 'module/gold/' . $this->_name . '.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}
	
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'module/' . $this->_name)) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}

    public function delete(){
        $this->language->load('module/' . $this->_name );

        $this->initializer(['module/gold']);

        if(isset($this->request->post["id"]) && $this->request->post["id"])  {

            $result_json['success'] = '0';
            $result_json['error'] = $this->language->get('text_delete_error');

            $id = $this->request->post["id"];

            $dlAction = $this->gold->deleteCaliber($id);
            if( $dlAction == 1){
                $result_json['success'] = '1';
                $result_json['success_msg'] = $this->language->get('text_delete_success');
            }else if($dlAction == 2){
                $result_json['success'] = '0';
                $result_json['error'] = $this->language->get('text_relatedtoproduct_error');
            }
        }

        $this->response->setOutput(json_encode($result_json));
        return;
    }

    public function install() {
        $this->initializer(['module/gold']);
        $this->gold->install();
    }

    public function uninstall() {
        $this->initializer(['module/gold']);
        $this->gold->uninstall();
    }
}
?>