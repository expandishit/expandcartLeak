<?php
class ControllerModuleSignup extends Controller {
	private $error = array(); 
	
	public function index() {
        $this->load->model('marketplace/common');
        $market_appservice_status = $this->model_marketplace_common->hasApp('signup');
        if (!$market_appservice_status['hasapp']) {
            $this->redirect($this->url->link('marketplace/app', 'id=' . $market_appservice_status['appserviceid'], 'SSL'));
            return;
        }

		$this->language->load('module/signup');

		$this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('module/signup');
               
				
		if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {
            if ( !$this->validate() )
            {
                $result_json['success'] = '0';
                $result_json['error'] = $this->error;
            }
            else
            {
                $this->model_module_signup->editSetting($this->request->post);
                $this->model_module_signup->editLocaleSettings($this->request->post);
                $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');
                $result_json['success'] = '1';
            }

            $this->response->setOutput(json_encode($result_json));
            return;
		}
				

        $this->data['fields'] = $this->getFields();

        $this->load->model('localisation/language');
        $this->data['languages'] = $this->model_localisation_language->getLanguages();

 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

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
			'href'      => $this->url->link('module/signup', '', 'SSL'),
      		'separator' => ' :: '
   		);
		
		$this->data['action'] = $this->url->link('module/signup', '', 'SSL');
		
		$this->data['cancel'] = $this->url->link('marketplace/home', '', 'SSL');
				
		$this->data['modules'] = array();
		
		if (isset($this->request->post['signup_module'])) {
			$this->data['modules'] = $this->request->post['signup_module'];
		} elseif ($this->config->get('signup_module')) { 
			$this->data['modules'] = $this->config->get('signup_module');
		}		
		
		$this->load->model('module/signup');
		
		$this->data['isModActive'] = $this->model_module_signup->isActiveMod();

        $this->data['modData']  = $this->model_module_signup->getModData();

        $this->data['locales'] = $this->model_module_signup->getLocales();
        $this->data['modSettings'] = $this->model_module_signup->getModuleSettings();

		$this->template = 'module/signup.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}
	
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'module/signup')) {
			$this->error['warning'] = $this->language->get('error_permission');
        }
        
        if ($this->request->post["pass_min"] > $this->request->post["pass_max"] && $this->request->post["pass_max"] > 0) {
            $this->error['warning'] = $this->language->get('error_pass_min_gt_max');
        }

		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}

    public function install() {
        $this->load->model('module/signup');
        $this->model_module_signup->install();
    }

    public function uninstall() {
        $this->load->model('module/signup');
        $this->model_module_signup->uninstall();
    }

    private function getFields()
    {
        $fields = array();

        $fields[] = array(
            'label' => 'text_first_name',
            'key'   => 'f_name'
        );

        $fields[] = array(
            'label' => 'text_second_name',
            'key'   => 'l_name'
        );

        $fields[] = array(
            'label' => 'text_tel',
            'key'   => 'mob'
        );

        $fields[] = array(
            'label' => 'text_fax',
            'key'   => 'fax'
        );

        $fields[] = array(
            'label' => 'text_company',
            'key'   => 'company'
        );

        $fields[] = array(
            'label' => 'text_company_id',
            'key'   => 'companyId'
        );

        $fields[] = array(
            'label' => 'text_add1',
            'key'   => 'address1'
        );

        $fields[] = array(
            'label' => 'text_add2',
            'key'   => 'address2'
        );

        $fields[] = array(
            'label' => 'text_country',
            'key'   => 'country'
        );

        $fields[] = array(
            'label' => 'text_city',
            'key'   => 'city'
        );

        $fields[] = array(
            'label' => 'text_state',
            'key'   => 'state'
        );

        $fields[] = array(
            'label' => 'text_area',
            'key'   => 'area'
        );

        $fields[] = array(
            'label' => 'text_post_code',
            'key'   => 'pin'
        );
        $fields[] = array(
            'label' => 'text_newsletter',
            'key'   => 'subsribe',
            'disabled' => true
        );

        $fields[] = array(
            'label' => 'text_pass_confirm',
            'key'   => 'passconf',
            'disabled' => true
        );
        $fields[] = array(
            'label' => 'text_dob',
            'key'   => 'dob'
        );
        $fields[] = array(
            'label' => 'text_gender',
            'key'   => 'gender'
        );

        return $fields;
    }
}
?>