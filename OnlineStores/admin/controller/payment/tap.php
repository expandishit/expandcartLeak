<?php
class ControllerPaymentTap extends Controller {
    
    private $error = array();

	public function index() {

		$this->load->language('payment/tap');

		$this->load->model('localisation/language');


		$this->document->setTitle($this->language->get('heading_title'));
        
        $arr = array();	
				

		// ATH why this foreach $arr not used in other places..
		foreach($this->request->post as $key => $value)
		{
			if($key == 'payment_paytm_merchant2')
			{				
            
                $arr[$key] = encrypt_e($value, $const1);		
            
                continue;
            
            }
            
            $arr[$key] = $value;
		} 

		$this->load->model('setting/setting');

	

		if ( $this->is_post_request() ) {

			if ( ! $this->validate() )
			{

				$this->handleFailure();
				
				return;
			}

			$this->model_setting_setting->checkIfExtensionIsExists('payment', 'tap', true);
                        $this->tracking->updateGuideValue('PAYMENT');

			$this->handleSuccessModifing();
			return ;
		}


		$this->justifyLang();

		$this->checkErrors();

		$this->setBreadCrumbs();

		$this->setRoutes();

		$this->setPaymentFromPostOrConfig();

		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		$this->data['languages'] = $this->model_localisation_language->getLanguages();

		// echo "<pre>";
		// print_r($this->data);
		// echo "</pre>";
		// die();


		$this->setLayouts();
		
		$this->response->setOutput($this->render() );

	}

	/**
	 * determine of post request is comming

	 * @param none
	 * @return type bool
	 * @author ATH 
	 **/
	protected function  is_post_request(){

		return ($this->request->server['REQUEST_METHOD'] == 'POST'); 

	}

	/**
	 * 
	 * 
	 * handle success request and model/setting updating 
	 */

	protected function handleSuccessModifing(){

		// var_dump( $this->model_setting_setting->editSetting('tap', $this->request->post) );

		// die();
		
		$this->model_setting_setting->editSetting('tap', $this->request->post);

		// $this->session->data['success'] = $this->language->get('text_success');
	
		$this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

		$result_json['success'] = '1';
	
		$this->response->setOutput(json_encode($result_json));

		// $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));


	}


	/**
	 * 
	 *  set necessary links or routes for this controller
	 * 
	 */
	protected function setRoutes(){

		$this->data['action'] = $this->url->link('payment/tap');
		
		$this->data['cancel'] = $this->url->link('extension/payment');
	
	} 

	/**
	 * 
	 * set Layouts header and footer and determine this controller patial
	 * 
	 */

	protected function setLayouts(){

		$this->template = 'payment/tap.expand';

		$this->children = array(
		
			'common/header',
		
			'common/footer'
		
		);

	} 


	/**
	 * 
	 * Handle failure
	 * 
	 */
	protected function handleFailure(){

		$result_json['success'] = '0';
	
		$result_json['errors'] = $this->error;
	
		$this->response->setOutput(json_encode($result_json));
	


	}

	/**
	 * fetching and setting necessary language indexes for a view partial
	 * @return void
	 * 
	 **/

	protected function justifyLang(){

		$this->data['heading_title'] = $this->language->get('heading_title');
	
		$this->data['text_edit'] = $this->language->get('text_edit');
	
		$this->data['text_enabled'] = $this->language->get('text_enabled');
	
		$this->data['text_disabled'] = $this->language->get('text_disabled');
	
		$this->data['text_all_zones'] = $this->language->get('text_all_zones');
	
		$this->data['text_yes'] = $this->language->get('text_yes');
	
		$this->data['text_no'] = $this->language->get('text_no');
	
		$this->data['entry_merchantid'] = $this->language->get('entry_merchantid');
	
		$this->data['entry_username'] = $this->language->get('entry_username');


		$this->data['entry_password'] = $this->language->get('entry_password');
		
		$this->data['entry_apikey'] = $this->language->get('entry_apikey');

		$this->data['entry_test'] = $this->language->get('entry_test');
	
		$this->data['entry_total'] = $this->language->get('entry_total');
	
		$this->data['entry_order_status'] = $this->language->get('entry_order_status');
	
		$this->data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
	
		$this->data['entry_status'] = $this->language->get('entry_status');
	
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');

		$this->data['help_password'] = $this->language->get('help_password');
	
		$this->data['help_total'] = $this->language->get('help_total');

		$this->data['button_save'] = $this->language->get('button_save');
	
		$this->data['button_cancel'] = $this->language->get('button_cancel');


	}

	/**
	 * checking for errors and displaying it 
	 * @return void
	 * 
	 **/

	protected function checkErrors(){

		if (isset($this->error['warning'])) {
	
			$this->data['error_warning'] = $this->error['warning'];
		
		} else {
		
			$this->data['error_warning'] = '';
		
		}

		if (isset($this->error['merchantid'])) {
			
			$this->data['error_merchantid'] = $this->error['merchantid'];
		
		} else {
			
			$this->data['error_merchantid'] = '';
		
		}
		
		if (isset($this->error['username'])) {
		
			$this->data['error_username'] = $this->error['username'];
		
		} else {
		
			$this->data['error_username'] = '';
		
		}

		if (isset($this->error['password'])) {
		
			$this->data['error_password'] = $this->error['password'];
		
		} else {
		
			$this->data['error_password'] = '';
		
		}




	} 


	/**
	 * set BreadCrumbs for this view partial
	 *
	 * @return void
	 **/
	protected function setBreadCrumbs(){

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
	
			'text' => $this->language->get('text_home'),
	
			'href' => $this->url->link('common/home')
	
		
		);

		$this->data['breadcrumbs'][] = array(
		
			'text' => $this->language->get('text_payment'),
		
			'href' => $this->url->link('extension/payment')
	
		);

		$this->data['breadcrumbs'][] = array(
			
			'text' => $this->language->get('heading_title'),
			
			'href' => $this->url->link('payment/tap')
		
		);



	}




		
		
		

	/**
	 * set payment setting from post request of exist or retrive it from config 
	 * @return Type void
	 * 
	 **/
	
	protected function setPaymentFromPostOrConfig(){

		if (isset($this->request->post['tap_merchantid'])) {
		
			$this->data['tap_merchantid'] = $this->request->post['tap_merchantid'];
		
		} else {
		
			$this->data['tap_merchantid'] = $this->config->get('tap_merchantid');
		
		}
		
		if (isset($this->request->post['tap_username'])) {
		
			$this->data['tap_username'] = $this->request->post['tap_username'];
		
		} else {
		
			$this->data['tap_username'] = $this->config->get('tap_username');
		
		}
		if (isset($this->request->post['tap_apikey'])) {
		
			$this->data['tap_apikey'] = $this->request->post['tap_apikey'];
		
		} else {
		
			$this->data['tap_apikey'] = $this->config->get('tap_apikey');
		
		}

		if (isset($this->request->post['tap_password'])) {
		
			$this->data['tap_password'] = $this->config->get('tap_password');
		
			// } else {
		
				// 	$this->data['tap_password'] = md5(mt_rand());
		
			}
		else {
			$this->data['tap_password'] = $this->config->get('tap_password');
		}

		if (isset($this->request->post['tap_test'])) {
	
			$this->data['tap_test'] = $this->request->post['tap_test'];
	
		} else {
	
			$this->data['tap_test'] = $this->config->get('tap_test');
	
		}

		if (isset($this->request->post['tap_total'])) {
	
			$this->data['tap_total'] = $this->request->post['tap_total'];
	
		} else {
	
			$this->data['tap_total'] = $this->config->get('tap_total');
	
		}

		if (isset($this->request->post['tap_order_status_id'])) {
	
			$this->data['tap_order_status_id'] = $this->request->post['tap_order_status_id'];
	
		} else {
	
			$this->data['tap_order_status_id'] = $this->config->get('tap_order_status_id');
	
		}

		if (isset($this->request->post['tap_complete_status_id'])) {
	
			$this->data['tap_complete_status_id'] = $this->request->post['tap_complete_status_id'];
	
		} else {
	
			$this->data['tap_complete_status_id'] = $this->config->get('tap_complete_status_id');
	
		}

		if (isset($this->request->post['tap_denied_status_id'])) {
	
			$this->data['tap_denied_status_id'] = $this->request->post['tap_denied_status_id'];
	
		} else {
	
			$this->data['tap_denied_status_id'] = $this->config->get('tap_denied_status_id');
	
		}

		if (isset($this->request->post['tap_payment_title'])) {
	
			$this->data['tap_payment_title'] = $this->request->post['tap_payment_title'];
	
		} else {
	
			$this->data['tap_payment_title'] = $this->config->get('tap_payment_title');
	
		}

		$this->load->model('localisation/order_status');
        $this->language->load('module/abandoned_cart');
        $order_statuses = $this->model_localisation_order_status->getOrderStatuses();
        $order_statuses[] = [
            'order_status_id' => 0,
            'name' => $this->language->get('text_abandoned')
        ];
		$this->data['order_statuses'] = $order_statuses;

		if (isset($this->request->post['tap_geo_zone_id'])) {
		
			$this->data['tap_geo_zone_id'] = $this->request->post['tap_geo_zone_id'];
		
		} else {
		
			$this->data['tap_geo_zone_id'] = $this->config->get('tap_geo_zone_id');
		
		}

	
		if (isset($this->request->post['tap_status'])) {
			exit;
			$this->data['tap_status'] = $this->request->post['tap_status'];
		
		} else {
		
			$this->data['tap_status'] = $this->config->get('tap_status');
		
		}

		if (isset($this->request->post['tap_sort_order'])) {
		
			$this->data['tap_sort_order'] = $this->request->post['tap_sort_order'];
		
		} else {
		
			$this->data['tap_sort_order'] = $this->config->get('tap_sort_order');
		
		}


	} 
	


	

    
    public function install() {
        
        $this->load->model('payment/tap');
    
    }

    public function uninstall() {
    
        $this->load->model('payment/tap');
    
    }


	protected function validate() {
        
        if (!$this->user->hasPermission('modify', 'payment/tap')) {
        
            $this->error['warning'] = $this->language->get('error_permission');
        
        }

		if (!$this->request->post['tap_merchantid']) {
        
            $this->error['merchantid'] = $this->language->get('error_merchantid');
        
        }
		
		if (!$this->request->post['tap_username']) {
        
            $this->error['tap_username'] = $this->language->get('tap_username');
        
        }

		if (!$this->request->post['tap_password']) {
        
            $this->error['password'] = $this->language->get('error_password');
        
        }

		return !$this->error;
	}
}
