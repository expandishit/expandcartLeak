<?php

require_once(DIR_SYSTEM . 'library/gmap/GoogleMap.php');

class ControllerModuleStoreLocations extends Controller {
	
	private $error = array(); 
	
	public function index() {
        $this->load->model('marketplace/common');
        $market_appservice_status = $this->model_marketplace_common->hasApp('store_locations');
        if (!$market_appservice_status['hasapp']) {
            $this->redirect($this->url->link('marketplace/app', 'id=' . $market_appservice_status['appserviceid'], 'SSL'));
            return;
        }

		//Load the language file for this module
		$this->load->language('module/store_locations');

		//Set the title from the language file $_['heading_title'] string
		$this->document->setTitle($this->language->get('heading_title'));
		
		//Load the settings model. You can also add any other models you want to load here.
		$this->load->model('setting/setting');
		
		$this->load->model('module/store_locations');
		$this->data['token'] = null;
		
		
		//Save the settings if the user has submitted the admin form (ie if someone has pressed save).
		if ($this->request->server['REQUEST_METHOD'] == 'POST') {

			if ( $this->validate() )
            {
                $this->model_setting_setting->editSetting('store_locations', $this->request->post);		
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
		$this->getList();
	}
	
	public function insert() {
		
		$this->load->language('module/store_locations');

		//Set the title from the language file $_['heading_title'] string
		$this->document->setTitle($this->language->get('heading_details'));
		
		//Load the settings model. You can also add any other models you want to load here.
		$this->load->model('setting/setting');
		
		$this->load->model('module/store_locations');
		
		$this->data['store_locations_api_key'] = $this->config->get('store_locations_api_key');

		//Save the settings if the user has submitted the admin form (ie if someone has pressed save).
		if ($this->request->server['REQUEST_METHOD'] == 'POST') {

			if ( $this->locValidate() )
            {
                //requesting the GeoCoding of the Address
				// $MAP_OBJECT = new GoogleMapAPI();
				$storeKey = $this->getStoreKey();
				$cords = array();
				$cords['lat'] = $this->request->post['latMap'];
				$cords['lon'] = $this->request->post['lngMap'];
				
				if($cords == false) {				//if geocoding failed, then pass the dummy lat/lon
					$cords = array("lat"=> 0, "lon" => 0);
					$this->session->data['success'] = $this->language->get('text_success_nogeocode');
				}
				else {
					$this->session->data['success'] = $this->language->get('text_success_store');
				}
				
				$this->model_module_store_locations->addLocation($this->request->post, $cords);

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
		
		$this->getForm();
		
	}

	// ATH
	function getStoreKey(){

		if (isset($this->request->post['store_locations_api_key'])) {
		
			return  $this->request->post['store_locations_api_key'];
		}

		return  $this->config->get('store_locations_api_key');

	}
	
	public function update() {
		
		$this->load->language('module/store_locations');

		//Set the title from the language file $_['heading_title'] string
		$this->document->setTitle($this->language->get('heading_details'));
		
		//Load the settings model. You can also add any other models you want to load here.
		$this->load->model('setting/setting');
		
		$this->load->model('module/store_locations');

		$this->data['store_locations_api_key'] = $this->config->get('store_locations_api_key');
		
		//Save the settings if the user has submitted the admin form (ie if someone has pressed save).
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->locValidate()) {
			
                //requesting the GeoCoding of the Address
				// $MAP_OBJECT = new GoogleMapAPI();
				// $storeKey = $this->getStoreKey();
				
				$cords = array();
				$cords['lat'] = $this->request->post['latMap'];
				$cords['lon'] = $this->request->post['lngMap'];
				
				if(empty($cords['lat']) || empty($cords['lon'])) {
					$cords = array("lat"=> 0, "lon" => 0);
					$this->session->data['success'] = $this->language->get('text_success_nogeocode');
					$this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success_nogeocode');
				}else {
					$this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success_store');
					
					$this->session->data['success'] = $this->language->get('text_success_store') ;
				}
				$this->model_module_store_locations->editLocation($this->request->post, $this->request->get['location_id'], $cords);	
                $result_json['success'] = '1';
            
            $this->response->setOutput(json_encode($result_json));
            return;
		}
		
		$this->getForm();
				
	}
	
	public function delete() {   
		//Load the language file for this module
		$this->load->language('module/store_locations');

		//Set the title from the language file $_['heading_title'] string
		$this->document->setTitle($this->language->get('heading_title'));
		
		//Load the settings model. You can also add any other models you want to load here.
		$this->load->model('setting/setting');
		
		$this->load->model('module/store_locations');
		
		//Save the settings if the user has submitted the admin form (ie if someone has pressed save).
		if ($this->request->server['REQUEST_METHOD'] == 'POST') {

			if ( $this->validate() )
            {
                $sIDs = implode(",",$this->request->post['selected']);		// getting the selected IDs
				$this->model_module_store_locations->deleteLocations($sIDs);
				unset($this->request->post['selected']);		//deleting the selected from post, as it is note requried to be stored in settings
				
				$this->model_setting_setting->editSetting('store_locations', $this->request->post);		//saving normal settings	
                $this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success');
                $result_json['success'] = '1';
            }
            else
            {
                $result_json['success'] = '0';
                $result_json['error'] = $this->error;
            }
            echo $result_json['success'];
            //$this->response->setOutput(json_encode($result_json));
            //return;		
		}
		
		//$this->getList();

	}
	
	private function getForm() {
		
		$this->loadLanguage();
		
		$this->data['token'] = null;
		
		if(isset($this->request->get['location_id'])) {
			$result = $this->model_module_store_locations->getLocation($this->request->get['location_id']);
			$this->data['edit'] = '';
		}
		else {
			$result = NULL;
			$this->data['edit'] = 'checked';
		}
		
		$config_data = array(
				'Name',
				'Details',
				'Address',
				'Email',
				'Phone',
				'SpecialOffers',
				'Timing',
				'sort_order',
				'lat',
				'lon'
		);
		
		foreach ($config_data as $conf) {
			if (isset($this->request->post[$conf])) {
				$this->data[$conf] = $this->request->post[$conf];
			} 
			else {
				$this->data[$conf] = ($result == NULL) ? '' : $result[$conf];
			}
			
		}
		$this->load->model('tool/image');
		
		$this->data['no_image'] = $this->model_tool_image->resize('no_image.jpg', 150, 150);
		
		if (isset($this->request->post['location_image'])) {
			$location_images = $this->request->post['location_image'];
		} elseif (isset($this->request->get['location_id'])) {
			$location_images = $this->model_module_store_locations->getLocationImages($this->request->get['location_id']);
		} else {
			$location_images = array();
		}
		
		$this->data['location_images'] = array();
		
		foreach ($location_images as $location_image) {
			if ($location_image['image']) {
				$image = $location_image['image'];
			} else {
				$image = 'no_image.jpg';
			}
			
			$this->data['location_images'][] = array(
				'image'      => $image,
				'thumb'      => $this->model_tool_image->resize($image, 150, 150),
				'sort_order' => $location_image['sort_order'],
			);
		}
		
		//This creates an error message. The error['warning'] variable is set by the call to function validate() in this controller (below)
 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}
		
		//SET UP BREADCRUMB TRAIL. YOU WILL NOT NEED TO MODIFY THIS UNLESS YOU CHANGE YOUR MODULE NAME.
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
       		'text'      => $this->language->get('heading_store'),
			'href'      => $this->url->link('module/store_locations', '', 'SSL'),
      		'separator' => ' :: '
   		);
		
		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_details'),
			'href'      => '#',
      		'separator' => ' :: '
   		);
		
		
		if (!isset($this->request->get['location_id'])) {
			$this->data['action'] = $this->url->link('module/store_locations/insert', '', 'SSL');
		} else {
			$this->data['action'] = $this->url->link('module/store_locations/update', 'location_id=' . $this->request->get['location_id'], 'SSL');
		}
		
			
		//$this->data['cancel'] = $this->url->link('module/store_locations', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel'] = $this->url.'module/store_locations';
		
		$this->load->model('design/layout');
		
		$this->data['layouts'] = $this->model_design_layout->getLayouts();

		//Choose which template file will be used to display this request.
		$this->template = 'module/store_locations_form.expand';
		$this->children = array(
			'common/header',
			'common/footer',
		);

		//Send the output.
		$this->response->setOutput($this->render());
		
	}
	
	private function loadLanguage() {
		
		// language strings
		
		$text_strings = array(
				'heading_title',
				'heading_details',
				'text_enabled',
				'text_disabled',
				'text_content_top',
				'text_content_bottom',
				'text_column_left',
				'text_column_right',
				'entry_layout',
				'entry_limit',
				'entry_image',
				'entry_position',
				'entry_status',
				'entry_sort_order',
				'button_save',
				'button_cancel',
				'button_add_module',
				'button_remove',
				'entry_example' ,
				'store_loc_name',
				'store_loc_detail',
				'store_loc_address',
				'store_loc_latlon',
				'store_loc_gcode_req',
				'store_loc_email',
				'store_loc_phone',
				'store_loc_offers',
				'store_loc_btn_remove',
				'store_loc_image',
				'store_loc_btn_add',
				'store_loc_timing',
				'store_loc_iWidth',
				'store_loc_iHeight',
				'store_loc_iaWidth',
				'store_loc_iaHeight',
				'store_loc_mdWidth',
				'store_loc_mdHeight',
				'store_loc_mWidth',
				'store_loc_mHeight',
				'store_loc_txt_per_page',
				'column_name',
				'column_sort_order',
				'column_action',
				'text_no_results',
				'button_delete',
				'button_insert',
				'button_edit',
				'button_add_image',
				'text_browse',
				'text_clear',
				'text_image_manager',
				'text_success_nogeocode',
				'text_success_store',
				'entry_icon',
				'store_loc_auto_detect',
				'entry_health',
				'button_diagnose'
		);
		
		foreach ($text_strings as $text) {
			$this->data[$text] = $this->language->get($text);
		}
		
		$this->data['entry_status'] = $this->language->get('entry_status');
		
		//END LANGUAGE
		
	}
	
	
	private function getList() {
	
		$this->loadLanguage();
		
		$this->data['token'] = null;
		
		$config_data = array(
				'store_locations_position',
				'store_locations_status',
				'store_locations_sort_order',
				'store_locations_iWidth',
				'store_locations_iHeight',
				'store_locations_iaWidth',
				'store_locations_iaHeight',
				'store_locations_mdWidth',
				'store_locations_mdHeight',
				'store_locations_mWidth',
				'store_locations_mHeight',
				'store_locations_per_page',
				'store_locations_image_icon',
				'thumb_icon',
				'store_locations_auto_detect',
				'store_locations_api_key',
		);
		
		foreach ($config_data as $conf) {
			if (isset($this->request->post[$conf])) {
				$this->data[$conf] = $this->request->post[$conf];
			} else {
				$this->data[$conf] = $this->config->get($conf);
			}
		}
		
		$this->load->model('tool/image');
		
		$this->data['no_image'] = $this->model_tool_image->resize('no_image.jpg', 150, 150);
		$this->data['thumb_icon'] = $this->model_tool_image->resize($this->data['store_locations_image_icon'],32,32);
		
		
	
		//This creates an error message. The error['warning'] variable is set by the call to function validate() in this controller (below)
 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}
		
		if (isset($this->session->data['success'] )) {
			$this->data['success'] = $this->session->data['success'] ;
			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}
		
		//SET UP BREADCRUMB TRAIL. YOU WILL NOT NEED TO MODIFY THIS UNLESS YOU CHANGE YOUR MODULE NAME.
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
			'href'      => $this->url->link('module/store_locations', '', 'SSL'),
      		'separator' => ' :: '
   		);
		
		$this->data['action'] = $this->url->link('module/store_locations', '', 'SSL');
		
		//$this->data['cancel'] = $this->url->link('marketplace/home', 'token=' . $this->session->data['token'] . '&type=apps', 'SSL');
		$this->data['cancel'] = $this->url.'marketplace/home';
		
		// Store List Display
		$this->data['insert'] = $this->url->link('module/store_locations/insert', '', 'SSL');
		$this->data['delete'] = $this->url->link('module/store_locations/delete', '', 'SSL');
		
		$this->data['sLocations'] = array();
		$results = $this->model_module_store_locations->getList();
		
		foreach ($results as $result) {
			$action = array();
			
			$action[] = array(
				'text' => $this->language->get('text_edit'),
				'href' => $this->url->link('module/store_locations/update', 'location_id=' . $result['ID'], 'SSL')
			);
					
			$this->data['sLocations'][] = array(
				'location_id' => $result['ID'],
				'name'        => $result['Name'],
				'sort_order'  => $result['sort_order'],
				'selected'    => isset($this->request->post['selected']) && in_array($result['location_id'], $this->request->post['selected']),
				'action'      => $action
			);
		}
		
		// End Store List Display
	
		//This code handles the situation where you have multiple instances of this module, for different layouts.
		$this->data['modules'] = array();
		
		if (isset($this->request->post['store_locations_module'])) {
			$this->data['modules'] = $this->request->post['store_locations_module'];
		} elseif ($this->config->get('store_locations_module')) { 
			$this->data['modules'] = $this->config->get('store_locations_module');
		}		

		$this->load->model('design/layout');
		
		$this->data['layouts'] = $this->model_design_layout->getLayouts();

		//Choose which template file will be used to display this request.
		$this->template = 'module/store_locations.expand';
		$this->children = array(
			'common/header',
			'common/footer',
		);

		//Send the output.
		$this->response->setOutput($this->render());
	}
	
	
	// creates the necessary tables while istallation of module
	public function install() {
		$this->load->model('module/store_locations');
		$this->model_module_store_locations->createModuleTables();
	}
	
	// delete custom tables createed for module
	public function uninstall() {
		$this->load->model('module/store_locations');
		$this->model_module_store_locations->dropModuleTables();
		
	}
	
	
	/*
	 * 
	 * This function is called to ensure that the settings chosen by the admin user are allowed/valid.
	 * You can add checks in here of your own.
	 * 
	 */
	private function validate() {
		if (!$this->user->hasPermission('modify', 'module/store_locations')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->error) {
			return TRUE;
		} else {
			return FALSE;
		}	
	}
	
	private function locValidate() {
		if (!$this->user->hasPermission('modify', 'module/store_locations')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		else if($this->request->post['Name'] == '') {
			$this->error['warning'] = $this->language->get('error_no_name');
		}
		
		if (!$this->error) {
			return TRUE;
		} else {
			return FALSE;
		}	
	}
	
	public function diagnoseGoogleAPI() {
	//Check if GeoCode API is working
		$geoCodeURL = 'https://maps.google.com/maps/api/geocode/json?sensor=false&address=Florida';
		$res = file_get_contents($geoCodeURL);
		$jsonres = json_decode($res);
		
		if ( isset($jsonres) && isset($jsonres->{'error_message'}) ) {
			$geoCodeError = $jsonres->{'error_message'};
		}
		else {
			$geoCodeError = 'Health OK';
		}
		
		$json['reason'] = $geoCodeError;
		
		$this->response->setOutput( json_encode($json) );
	}


}
?>