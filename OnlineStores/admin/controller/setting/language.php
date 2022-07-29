<?php
class ControllerSettingLanguage extends Controller {
	private $error = array();
 
	public function index() {
		$this->language->load('setting/setting'); 

		$this->document->setTitle($this->language->get('heading_title'));

        $this->data['direction'] = $this->language->get('direction');
		
		$this->load->model('setting/setting');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            $result_json = array(
                'success' => '0',
                'errors' => array(),
                'success_msg' => ''
            );

		    if ($this->validate()) {

                if ( $this->config->get('config_admin_language') != $this->request->post['config_admin_language'] )
                {
                    $result_json['redirect'] = '1';
                    $result_json['to'] = (string) $this->url->link('setting/language', '', 'SSL');
                }

                $this->model_setting_setting->insertUpdateSetting('config', $this->request->post);

                if ($this->config->get('config_currency_auto')) {
                    $this->load->model('localisation/currency');

                    $this->model_localisation_currency->updateCurrencies();
                }

                $this->tracking->updateGuideValue('LANGUAGE');

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
            array('text' => $this->language->get('heading_title'), 'href' => $this->url->link('setting/language'))
        );

		$this->data['action'] = $this->url->link('setting/language');
		
		$this->data['cancel'] = $this->url->link('setting/language');

        $this->load->model('localisation/language');
        $this->data['languages'] = $this->model_localisation_language->getLanguages();

        $this->data['admin_languages'] = $this->model_localisation_language->getAdminLanguages();
        $this->data['front_languages'] = $this->model_localisation_language->getFrontLanguages();

        $this->load->model('localisation/timezone');
        $this->data['timezones'] = $this->model_localisation_timezone->getTimezones();

        $this->load->model('localisation/country');
        $this->data['countries'] = $this->model_localisation_country->getCountries();

        $this->load->model('localisation/currency');
        $this->data['currencies'] = $this->model_localisation_currency->getCurrencies(["sort" =>"title"]);

        $this->load->model('localisation/length_class');
        $this->data['length_classes'] = $this->model_localisation_length_class->getLengthClasses();

        $this->load->model('localisation/weight_class');
        $this->data['weight_classes'] = $this->model_localisation_weight_class->getWeightClasses();

        $this->data['config_country_id'] = $this->config->get('config_country_id');
        $this->data['config_timezone'] = $this->config->get('config_timezone');
        $this->data['config_zone_id'] = $this->config->get('config_zone_id');
        $this->data['config_language'] = $this->config->get('config_language');
        $this->data['config_admin_language'] = $this->config->get('config_admin_language');
        $this->data['config_currency'] = $this->config->get('config_currency');
        $this->data['config_currency_auto'] = $this->config->get('config_currency_auto');
        $this->data['config_length_class_id'] = $this->config->get('config_length_class_id');
        $this->data['config_weight_class_id'] = $this->config->get('config_weight_class_id');

        $this->template = 'setting/language.expand';
		$this->base = "common/base";
				
		$this->response->setOutput($this->render_ecwig());
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'setting/language')) {
			$this->error['error'] = $this->language->get('error_permission');
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

    public function country() {
        $json = array();

        $this->load->model('localisation/country');

        $country_info = $this->model_localisation_country->getCountry($this->request->get['country_id']);

        if ($country_info) {
            $this->load->model('localisation/zone');

            $json = array(
                'country_id'        => $country_info['country_id'],
                'name'              => $country_info['name'],
                'iso_code_2'        => $country_info['iso_code_2'],
                'iso_code_3'        => $country_info['iso_code_3'],
                'address_format'    => $country_info['address_format'],
                'postcode_required' => $country_info['postcode_required'],
                'zone'              => $this->model_localisation_zone->getZonesByCountryId($this->request->get['country_id']),
                'status'            => $country_info['status']
            );
        }

        $this->response->setOutput(json_encode($json));
        return;
    }
}
?>