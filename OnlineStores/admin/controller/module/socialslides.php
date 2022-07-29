<?php
// -----------------------------
// Social Slides for OpenCart 
// By Best-Byte
// www.best-byte.com
// -----------------------------

class ControllerModuleSocialSlides extends Controller {
	private $error = array();
	private $_name = 'socialslides';
	private $_version = '1.4'; 

	public function index() {
        $this->load->model('marketplace/common');
        $market_appservice_status = $this->model_marketplace_common->hasApp('socialslides');
        if (!$market_appservice_status['hasapp']) {
            $this->redirect($this->url->link('marketplace/app', 'id=' . $market_appservice_status['appserviceid'], 'SSL'));
            return;
        }

		$this->load->language('module/' . $this->_name);

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->data[$this->_name . '_version'] = $this->_version;
		
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
		
		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_module_settings'] = $this->language->get('text_module_settings');

    $this->data['entry_facebook'] = $this->language->get('entry_facebook');
    $this->data['entry_facebook_show'] = $this->language->get('entry_facebook_show');
    $this->data['entry_facebook_code'] = $this->language->get('entry_facebook_code');
    
    $this->data['entry_twitter'] = $this->language->get('entry_twitter');
    $this->data['entry_twitter_show'] = $this->language->get('entry_twitter_show');
    $this->data['entry_twitter_code'] = $this->language->get('entry_twitter_code');  
    
    $this->data['entry_google'] = $this->language->get('entry_google');
    $this->data['entry_google_show'] = $this->language->get('entry_google_show');
    $this->data['entry_google_code'] = $this->language->get('entry_google_code');  
    
    $this->data['entry_pinterest'] = $this->language->get('entry_pinterest');
    $this->data['entry_pinterest_show'] = $this->language->get('entry_pinterest_show');
    $this->data['entry_pinterest_code'] = $this->language->get('entry_pinterest_code');     
    
    $this->data['entry_youtube'] = $this->language->get('entry_youtube');
    $this->data['entry_youtube_show'] = $this->language->get('entry_youtube_show');
    $this->data['entry_youtube_code'] = $this->language->get('entry_youtube_code');  
    
    $this->data['entry_linkedin'] = $this->language->get('entry_linkedin');
    $this->data['entry_linkedin_show'] = $this->language->get('entry_linkedin_show');
    $this->data['entry_linkedin_code'] = $this->language->get('entry_linkedin_code');

	$this->data['entry_instagram'] = $this->language->get('entry_instagram');
	$this->data['entry_instagram_show'] = $this->language->get('entry_instagram_show');
	$this->data['entry_instagram_code'] = $this->language->get('entry_instagram_code');
		
	$this->data['entry_whatsapp'] = $this->language->get('entry_whatsapp');
	$this->data['entry_whatsapp_show'] = $this->language->get('entry_whatsapp_show');
	$this->data['entry_whatsapp_phone'] = $this->language->get('entry_whatsapp_phone');
	$this->data['entry_whatsapp_welcome_msg'] = $this->language->get('entry_whatsapp_welcome_msg');

	$this->data['top_position'] = $this->language->get('top_position');
	$this->data['entry_display'] = $this->language->get('entry_display');
    $this->data['entry_right'] = $this->language->get('entry_right');	 
    $this->data['entry_left'] = $this->language->get('entry_left');	             
    $this->data['entry_yes'] = $this->language->get('entry_yes');	 
    $this->data['entry_no'] = $this->language->get('entry_no');	
    $this->data['entry_moduleinfo'] = $this->language->get('entry_moduleinfo');		
		$this->data['entry_template']	= $this->language->get('entry_template');
        
		$this->data['entry_layout'] = $this->language->get('entry_layout');
		$this->data['entry_status'] = $this->language->get('entry_status');
		
		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');
		$this->data['button_add_module'] = $this->language->get('button_add_module');
		$this->data['button_remove'] = $this->language->get('button_remove');
		
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
			'href'      => $this->url->link('module/' . $this->_name, '', 'SSL'),
      		'separator' => ' :: '
   		);
		
		$this->data['action'] = $this->url->link('module/' . $this->_name, '', 'SSL');
		
		$this->data['cancel'] = $this->url.'marketplace/home?'.'';
		
		$this->data['templates'] = array();

		$directories = glob(DIR_CATALOG . 'view/theme/*', GLOB_ONLYDIR);
		
		foreach ($directories as $directory) {
			$this->data['templates'][] = basename($directory);
		}	
		
		if (isset($this->request->post['config_template'])) {
			$this->data['config_template'] = $this->request->post['config_template'];
		} else {
			$this->data['config_template'] = $this->config->get('config_template');			
		}	
		
		$this->load->model('localisation/language');
		
		$languages = $this->model_localisation_language->getLanguages();
		
		$this->data['languages'] = $languages;
		
		if (isset($this->request->post[$this->_name . '_facebook_show'])) {
			$this->data[$this->_name . '_facebook_show'] = $this->request->post[$this->_name . '_facebook_show'];
		} else {
			$this->data[$this->_name . '_facebook_show'] = $this->config->get($this->_name . '_facebook_show');
		}	
		
		if (isset($this->request->post[$this->_name . '_top_position'])) {
			$this->data[$this->_name . '_top_position'] = $this->request->post[$this->_name . '_top_position'];
		} else {
			$this->data[$this->_name . '_top_position'] = $this->config->get($this->_name . '_top_position');
		}			

		if (isset($this->request->post[$this->_name . '_facebook_code'])) {
			$this->data[$this->_name . '_facebook_code'] = $this->request->post[$this->_name . '_facebook_code'];
		} else {
			$this->data[$this->_name . '_facebook_code'] = $this->config->get($this->_name . '_facebook_code');
		}	

		if (isset($this->request->post[$this->_name . '_twitter_show'])) {
			$this->data[$this->_name . '_twitter_show'] = $this->request->post[$this->_name . '_twitter_show'];
		} else {
			$this->data[$this->_name . '_twitter_show'] = $this->config->get($this->_name . '_twitter_show');
		}	

		if (isset($this->request->post[$this->_name . '_twitter_code'])) {
			$this->data[$this->_name . '_twitter_code'] = $this->request->post[$this->_name . '_twitter_code'];
		} else {
			$this->data[$this->_name . '_twitter_code'] = $this->config->get($this->_name . '_twitter_code');
		}	
		
		if (isset($this->request->post[$this->_name . '_google_show'])) {
			$this->data[$this->_name . '_google_show'] = $this->request->post[$this->_name . '_google_show'];
		} else {
			$this->data[$this->_name . '_google_show'] = $this->config->get($this->_name . '_google_show');
		}

		if (isset($this->request->post[$this->_name . '_google_code'])) {
			$this->data[$this->_name . '_google_code'] = $this->request->post[$this->_name . '_google_code'];
		} else {
			$this->data[$this->_name . '_google_code'] = $this->config->get($this->_name . '_google_code');
		}		

		if (isset($this->request->post[$this->_name . '_pinterest_show'])) {
			$this->data[$this->_name . '_pinterest_show'] = $this->request->post[$this->_name . '_pinterest_show'];
		} else {
			$this->data[$this->_name . '_pinterest_show'] = $this->config->get($this->_name . '_pinterest_show');
		}

		if (isset($this->request->post[$this->_name . '_pinterest_code'])) {
			$this->data[$this->_name . '_pinterest_code'] = $this->request->post[$this->_name . '_pinterest_code'];
		} else {
			$this->data[$this->_name . '_pinterest_code'] = $this->config->get($this->_name . '_pinterest_code');
		}	
    
		if (isset($this->request->post[$this->_name . '_youtube_show'])) {
			$this->data[$this->_name . '_youtube_show'] = $this->request->post[$this->_name . '_youtube_show'];
		} else {
			$this->data[$this->_name . '_youtube_show'] = $this->config->get($this->_name . '_youtube_show');
		}

		if (isset($this->request->post[$this->_name . '_youtube_code'])) {
			$this->data[$this->_name . '_youtube_code'] = $this->request->post[$this->_name . '_youtube_code'];
		} else {
			$this->data[$this->_name . '_youtube_code'] = $this->config->get($this->_name . '_youtube_code');
		}		
    
		if (isset($this->request->post[$this->_name . '_linkedin_show'])) {
			$this->data[$this->_name . '_linkedin_show'] = $this->request->post[$this->_name . '_linkedin_show'];
		} else {
			$this->data[$this->_name . '_linkedin_show'] = $this->config->get($this->_name . '_linkedin_show');
		}

		if (isset($this->request->post[$this->_name . '_linkedin_code'])) {
			$this->data[$this->_name . '_linkedin_code'] = $this->request->post[$this->_name . '_linkedin_code'];
		} else {
			$this->data[$this->_name . '_linkedin_code'] = $this->config->get($this->_name . '_linkedin_code');
		}

        if (isset($this->request->post[$this->_name . '_instagram_show'])) {
            $this->data[$this->_name . '_instagram_show'] = $this->request->post[$this->_name . '_instagram_show'];
        } else {
            $this->data[$this->_name . '_instagram_show'] = $this->config->get($this->_name . '_instagram_show');
        }

        if (isset($this->request->post[$this->_name . '_instagram_code'])) {
            $this->data[$this->_name . '_instagram_code'] = $this->request->post[$this->_name . '_instagram_code'];
        } else {
            $this->data[$this->_name . '_instagram_code'] = $this->config->get($this->_name . '_instagram_code');
		}
		
		if (isset($this->request->post[$this->_name . '_whatsapp_show'])) {
            $this->data[$this->_name . '_whatsapp_show'] = $this->request->post[$this->_name . '_whatsapp_show'];
        } else {
            $this->data[$this->_name . '_whatsapp_show'] = $this->config->get($this->_name . '_whatsapp_show');
		}
		
		if (isset($this->request->post[$this->_name . '_whatsapp_phone'])) {
            $this->data[$this->_name . '_whatsapp_phone'] = $this->request->post[$this->_name . '_whatsapp_phone'];
        } else {
            $this->data[$this->_name . '_whatsapp_phone'] = $this->config->get($this->_name . '_whatsapp_phone');
		}
		
		if (isset($this->request->post[$this->_name . '_whatsapp_welcome_msg'])) {
            $this->data[$this->_name . '_whatsapp_welcome_msg'] = $this->request->post[$this->_name . '_whatsapp_welcome_msg'];
        } else {
            $this->data[$this->_name . '_whatsapp_welcome_msg'] = $this->config->get($this->_name . '_whatsapp_welcome_msg');
        }

        if (isset($this->request->post[$this->_name . '_display'])) {
			$this->data[$this->_name . '_display'] = $this->request->post[$this->_name . '_display'];
		} else {
			$this->data[$this->_name . '_display'] = $this->config->get($this->_name . '_display');
		}

		if (isset($this->request->post[$this->_name . '_template'])) {
			$this->data[$this->_name . '_template'] = $this->request->post[$this->_name . '_template'];
		} else {
			$this->data[$this->_name . '_template'] = $this->config->get($this->_name . '_template');
		}
	
		$this->data['modules'] = array();
		
		if (isset($this->request->post[$this->_name . '_module'])) {
			$this->data['modules'] = $this->request->post[$this->_name . '_module'];
		} elseif ($this->config->get($this->_name . '_module')) { 
			$this->data['modules'] = $this->config->get($this->_name . '_module');
		}
		
		$this->load->model('design/layout');
		
		$this->data['layouts'] = $this->model_design_layout->getLayouts();
	
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
