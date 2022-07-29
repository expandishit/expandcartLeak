<?php
class ControllerSettingStoreGeneral extends Controller {
	private $error = array();

	public function index() {
		$this->language->load('setting/setting'); 

		$this->document->setTitle($this->language->get('heading_title'));

        $this->data['direction'] = $this->language->get('direction');
		
		$this->load->model('setting/setting');

        //$matomo = $this->config->get('matomo_analytics');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            //$matomo['status'] = $this->request->post['matomo_status'];
            $this->load->model('setting/audit_trail');
            $pageStatus = $this->model_setting_audit_trail->getPageStatus("general_setting");
            if($pageStatus){
                $oldValue['general_settings'] = $this->model_setting_setting->getSetting('config');
                //$oldValue['matomo'] = $this->model_setting_setting->getSetting('analytics');
                
                    // add data to log_history

                    $log_history['action'] = 'update';
                    $log_history['reference_id'] = NULL;
                    $log_history['old_value'] = json_encode($oldValue,JSON_UNESCAPED_UNICODE);
                    $log_history['new_value'] = json_encode($this->request->post,JSON_UNESCAPED_UNICODE);
                    $log_history['type'] = 'general_setting';
                    $this->load->model('loghistory/histories');
                    $this->model_loghistory_histories->addHistory($log_history);
            }

//            unset($this->request->post['matomo_status']);
//            $this->model_setting_setting->insertUpdateSetting("analytics",['matomo_analytics' => $matomo]);

            $result_json = array(
                'success' => '0',
                'errors' => array(),
                'success_msg' => ''
            );


		    if ($this->validate()) {


                $this->model_setting_setting->insertUpdateSetting('config', $this->request->post);

                $this->model_setting_setting->editGuideValue('GETTING_STARTED', 'EDIT_SETTINGS', '1');

                $this->load->model('account/referral');
                $this->model_account_referral->update_history($this->request->post["config_name"][$this->config->get('config_admin_language')]);

                $result_json['success'] = '1';
                $result_json['success_msg'] = $this->language->get('text_success');
            } else {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
            }

            $this->response->setOutput(json_encode($result_json));
		    return;
		}

  		$this->data['breadcrumbs'] = array(
  		    array('text' => $this->language->get('text_home'), 'href' => $this->url->link('common/home')),
            array('text' => $this->language->get('heading_title'), 'href' => $this->url->link('setting/store_general'))
        );

		$this->data['action'] = $this->url->link('setting/store_general');
		
		$this->data['cancel'] = $this->url->link('setting/store_general');

        $this->load->model('localisation/language');

        $this->data['languages'] = $this->model_localisation_language->getLanguages();

        $this->data['setting'] = $this->model_setting_setting->getSetting('config');

        //$this->data['setting']['matomo'] = $matomo;

        $this->load->model('tool/image');

        $this->data['setting']['config_logo_path'] = $this->model_tool_image->resize( $this->data['setting']['config_logo'], 150, 150);
        $this->data['setting']['config_icon_path'] = $this->model_tool_image->resize( $this->data['setting']['config_icon'], 150, 150);

		$this->template = 'setting/store_general.expand';
		$this->base = "common/base";
				
		$this->response->setOutput($this->render_ecwig());
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'setting/store_general')) {
			$this->error['error'] = $this->language->get('error_permission');
		}

		foreach ( $this->request->post['config_name'] as $code => $value )
        {
            if ( (utf8_strlen($value) < 1) || (utf8_strlen($value) > 255) )
            {
                $this->error['config_name_' . $code] = $this->language->get('error_name');
            }
            break;
        }

        foreach ( $this->request->post['config_address'] as $code => $value )
        {
            if ( (utf8_strlen($value) < 3) || (utf8_strlen($value) > 255) )
            {
                $this->error['config_address_' . $code] = $this->language->get('error_address');
            }
            break;
        }

        foreach ( $this->request->post['config_telephone'] as $code => $value )
        {
            if ( (utf8_strlen($value) < 3) || (utf8_strlen($value) > 32) )
            {
                $this->error['config_telephone_' . $code] = $this->language->get('error_telephone');
                break;
            }
        }

        foreach ( $this->request->post['config_title'] as $code => $value )
        {
            if ( (utf8_strlen($value) < 1) || (utf8_strlen($value) > 255) )
            {
                $this->error['config_title' . $code] = $this->language->get('error_title');
                break;
            }
        }
		
    	if ((utf8_strlen($this->request->post['config_email']) > 96) || !filter_var($this->request->post['config_email'], FILTER_VALIDATE_EMAIL)) {
      		$this->error['config_email'] = $this->language->get('error_email');
    	}
				
		if ($this->error && !isset($this->error['error'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}
			
		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}
}
?>