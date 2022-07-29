<?php  

require_once(DIR_SYSTEM . 'library/gmap/GoogleMap.php');
require_once(DIR_SYSTEM . 'library/gmap/JSMin.php');

class ControllerCommonLocationDetails extends Controller {
	private $error = array(); 
	
	public function index() { 
		$this->language->load_json('common/location_details');
		$this->load->model('tool/image');
				
		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'href'      => $this->url->link('common/home'),
			'text'      => $this->language->get('text_home'),
			'separator' => FALSE
		);
		
		$this->data['breadcrumbs'][] = array(
			'href'      => $this->url->link('common/store_locations'),
			'text'      => $this->language->get('txt_Store_Locations'),
			'separator' => $this->language->get('text_separator')
		);
		
		if (file_exists('expandish/view/theme/' . $this->config->get('config_template') . '/stylesheet/gmap-style.css')) {
			$this->document->addStyle('expandish/view/theme/' . $this->config->get('config_template') . '/stylesheet/gmap-style.css');
		} else {
			$this->document->addStyle('expandish/view/theme/default/css/gmap-style.css');
		}
		
	   			
		$text_strings = array(
				'heading_title',
				'text_home',
				'store_loc_name',
				'store_loc_detail',
				'store_loc_address',
				'store_loc_email',
				'store_loc_phone',
				'store_loc_offers',
				'store_loc_image',
				'store_loc_timing',
				'txtWrong_loc_id',
				'store_loc_need_dir',
				'txt_Store_Locations'
		);
		
		foreach ($text_strings as $text) {
			$this->data[$text] = $this->language->get($text);
		}
		
		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('module/store_locations');
		
		if(isset($this->request->get['loc_id'])) {
			
			
			$location_id = $this->request->get['loc_id'];

			$MAP_OBJECT = new GoogleMapAPI();
			$MAP_OBJECT->width = $this->config->get('store_locations_mdWidth') . 'px'; 
			$MAP_OBJECT->height =  $this->config->get('store_locations_mdHeight') . 'px'; 
			$MAP_OBJECT->zoom = 13;
			$MAP_OBJECT->_minify_js = isset($_REQUEST["min"])?FALSE:TRUE;
			if( $this->config->get('store_locations_image_icon') != "") {
				$MAP_OBJECT->setMarkerIcon($this->model_tool_image->resize( $this->config->get('store_locations_image_icon'),32,32));
			}

			$MAP_OBJECT->api_key = $this->config->get('store_locations_api_key'); 
			

			$Location = $this->model_module_store_locations->getLocation($location_id);
			$images = $this->model_module_store_locations->getLocationImages($Location['ID']);
			if(isset($images[0])) {
				$Location['thumb'] = $this->model_tool_image->resize($images[0]['image'], $this->config->get('store_locations_iWidth'), $this->config->get('store_locations_iHeight'));
			} else {
				$Location['thumb'] = $this->model_tool_image->resize('no_image.jpg', 80, 80);
			}
			
			$this->data['images'] = array();
			foreach ($images as $result) {
				$this->data['images'][] = array(
					'popup' => $this->model_tool_image->resize($result['image'], $this->config->get('store_locations_iWidth'), $this->config->get('store_locations_iHeight')),
					'thumb' => $this->model_tool_image->resize($result['image'], $this->config->get('config_image_additional_width'), $this->config->get('config_image_additional_height'))
				);
			}	
			
			
			$Location['Name'] = html_entity_decode($Location['Name'], ENT_QUOTES, 'UTF-8');
			$Location['Details'] = html_entity_decode($Location['Details'], ENT_QUOTES, 'UTF-8');
			$Location['Address'] = html_entity_decode($Location['Address'], ENT_QUOTES, 'UTF-8');
			$Location['SpecialOffers'] = html_entity_decode($Location['SpecialOffers'], ENT_QUOTES, 'UTF-8');
			$Location['href'] = $this->url->link('common/location_details&loc_id=' . $location_id);
			
			$this->data["Location"] = $Location;
			
				//if direction is request then add directions, else add the normal marker
			if(isset($this->request->get['stAddress']) && $this->request->get['stAddress'] != "") {
				$locAdd = trim( preg_replace( '/\s+/', ' ', $Location['Address']) );
				$MAP_OBJECT->addDirections(urldecode($this->request->get['stAddress']),$locAdd, "mapDirections");
			}
			else {
				$locHtml = "<b><a href='" . $this->url->link('common/location_details&loc_id=' . $Location['ID']) . "'>" . $Location['Name'] . "</a>" ;
			
			$locHtml = $locHtml . "<br /><br /><input type='text' id='stAddress' />&nbsp<input type='button' value='" . $this->language->get('store_loc_need_dir') . "' onclick='getAdd(\"" .  $this->url->link('common/location_details&loc_id=' . $Location['ID']) . "\");' />";
				$MAP_OBJECT->addMarkerByCoords($Location['lon'],$Location['lat'],$Location['Name'], $locHtml);
			}
	
			$this->data['gmapHeaderJS'] = $MAP_OBJECT->getHeaderJS();
			$this->data['gmapJS'] = $MAP_OBJECT->getMapJS();
			$this->data['gmapJS'] = $this->data['gmapJS'] . "<!-- Generated for OpenCart using Store Locations module by Asif Mahmood (asif.mahmood@gmail.com) \r\n download your copy from http://www.opencart.com/index.php?route=extension/extension/info&extension_id=3151 -->";
			
			$this->data['gmaponLoad'] =$MAP_OBJECT->getOnLoad();
			$this->data['gmapHtml'] = $MAP_OBJECT->getMap();
			$this->data['gmapSideBar'] =$MAP_OBJECT->getSidebar();
			
			$this->data['heading_title'] = $Location['Name'];
			
			$this->data['breadcrumbs'][] = array(
			'href'      => $Location['href'],
			'text'      => $Location['Name'],
			'separator' => $this->language->get('text_separator')
			);
			
			
			$this->data['no_location'] = false;
		}
		else {
			$this->data['no_location'] = true;
		}
		
		if($this->config->get('config_language') == 'ar'){
			$this->data['rightAlign'] = true;
		}

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/common/location_details.expand')) {
			$this->template = $this->config->get('config_template') . '/template/common/location_details.expand';
		} else {
			$this->template = 'default/template/common/location_details.expand';
		}
			
		$this->children = array(
			'common/footer',
			'common/header'
		);
					
		$this->response->setOutput($this->render_ecwig());

  	}
	
	private function validate() {
		
	
		if (!$this->error) {
			return TRUE;
		} else {
			return FALSE;
		}	
	}
}
?>