<?php
class ControllerModuleDSocialLogin extends Controller {
	public $route  = 'module/d_social_login';
	public $mbooth = 'mbooth_d_social_login_lite.xml';
	public $module = 'd_social_login';
	private $error = array(); 
	
	public function index() {
        $this->load->model('marketplace/common');
        $market_appservice_status = $this->model_marketplace_common->hasApp('d_social_login');
        if (!$market_appservice_status['hasapp']) {
            $this->redirect($this->url->link('marketplace/app', 'id=' . $market_appservice_status['appserviceid'], 'SSL'));
            return;
        }

		/*
		*	Multistore
		*/
		
		$store_id = $this->request->get['store_id'] ?: 0;

		$this->document->addStyle('view/javascript/shopunity/colorpicker/jquery.colorpicker.css');
		$this->document->addScript('view/javascript/shopunity/colorpicker/jquery.colorpicker.js');

		$this->language->load($this->route);

		$this->document->setTitle($this->language->get('heading_title_main'));
		
		$this->load->model('setting/setting');
		$this->load->model('sale/customer_group');
				
		if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
		{

			if ( !$this->validate() )
			{
				$result_json['success'] = '0';
				$result_json['error'] = $this->error;
			}
			else
			{
				$this->model_setting_setting->editSetting($this->module, $this->request->post, $store_id);
				$this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');
				$result_json['success'] = '1';
			}

			$this->response->setOutput(json_encode($result_json));
			return;
		}
				
		$this->data['version'] = $this->get_version();
        $this->data['token'] =  null;
		$this->data['route'] = $this->route;
		$this->data['id'] = $this->module;
		$this->data['module_link'] = $this->url->link($this->route, '', 'SSL');
		$this->data['heading_title'] = $this->language->get('heading_title_main');
		
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
       		'text'      => $this->language->get('heading_title_main'),
			'href'      => $this->url->link($this->route, 'store_id='.$store_id, 'SSL'),
      		'separator' => ' :: '
   		);
		
		$this->data['action'] = $this->url->link($this->route, 'store_id='.$store_id, 'SSL');
		
		$this->data['cancel'] = $this->url->link('marketplace/home', '', 'SSL');

		$this->data['modules'] = array();
		
		if (isset($this->request->post['d_social_login_module'])) {
			$this->data['modules'] = $this->request->post['d_social_login_module'];
		} elseif ($this->model_setting_setting->getSetting('d_social_login', $store_id)) { 
			$this->data['modules'] = $this->model_setting_setting->getSetting('d_social_login', $store_id);
			$this->data['modules'] = (isset($this->data['modules']['d_social_login_module'])) ? $this->data['modules']['d_social_login_module'] : array();
		}



		if (isset($this->request->post['d_social_login_setting'])) {
			$this->data['setting'] = $this->request->post['d_social_login_settings'];
		} elseif ($this->model_setting_setting->getSetting('d_social_login', $store_id)) { 
			$this->data['setting'] = $this->model_setting_setting->getSetting('d_social_login', $store_id);
			$this->data['setting'] = (isset($this->data['setting']['d_social_login_settings'])) ? $this->data['setting']['d_social_login_settings'] : array();
		} else {
			$this->config->load($this->get_light_or_full_version());
			$this->data['setting'] = $this->config->get('d_social_login_settings');
		}


		$this->config->load($this->get_light_or_full_version());
		$config = $this->config->get('d_social_login_settings'); 
		$this->data['setting'] = $this->array_merge_recursive_distinct($config, $this->data['setting']);
		
		$this->load->model('tool/image');

		if (isset($this->request->post['setting']['background_img'])) {
			$this->data['background_img'] = $this->request->post['setting']['background_img'];
		} else {
			$this->data['background_img'] = $this->data['setting']['background_img'];			
		}

		if ($this->data['setting']['background_img']) {
			$this->data['background_img_thumb'] = $this->model_tool_image->resize(
				$this->data['setting']['background_img'], 150, 150
			);
		} else {
			$this->data['background_img_thumb'] = $this->model_tool_image->resize('no_image.jpg', 150, 150);
		}

		$this->data['no_image'] = $this->model_tool_image->resize('no_image.jpg', 150, 150);


		$this->data['providers'] = $this->data['setting']['providers'];
		$this->data['fields'] = $this->data['setting']['fields'];
		$this->data['return_urls'] = array('viewed', 'checkout', 'address', 'home', 'account');

		//Get stores
		$this->data['store_id'] = $store_id;
		$this->load->model('setting/store');
		$results = $this->model_setting_store->getStores();
		if($results){
			$this->data['stores'][] = array('store_id' => 0, 'name' => $this->config->get('config_name'));
			foreach ($results as $result) {
				$this->data['stores'][] = array(
					'store_id' => $result['store_id'],
					'name' => $result['name']
					
				);
			}	
		}

		//customer groups
		$this->data['customer_groups'] = $this->model_sale_customer_group->getCustomerGroups();

		//debug
		$this->data['debug'] = $this->getFileContents(DIR_LOGS.$this->data['setting']['debug_file']);
		$this->data['debug_file'] = $this->data['setting']['debug_file'];
						
		$this->load->model('design/layout');
		
		$this->data['layouts'] = $this->model_design_layout->getLayouts();

		$this->db_authentication();
		
		$this->template = $this->route . '.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}

	private function getFileContents($file){

		if (file_exists($file)) {
			$size = filesize($file);

			if ($size >= 5242880) {
				$suffix = array(
					'B',
					'KB',
					'MB',
					'GB',
					'TB',
					'PB',
					'EB',
					'ZB',
					'YB'
				);

				$i = 0;

				while (($size / 1024) > 1) {
					$size = $size / 1024;
					$i++;
				}

				return sprintf($this->language->get('error_get_file_contents'), basename($file), round(substr($size, 0, strpos($size, '.') + 4), 2) . $suffix[$i]);
			} else {
				return file_get_contents($file, FILE_USE_INCLUDE_PATH, null);
			}
		}
	}

	public function clearDebugFile() {
		$this->load->language($this->route);
		$json = array();

		if (!$this->user->hasPermission('modify', $this->route)) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			$file = DIR_LOGS.$this->request->post['debug_file'];

			$handle = fopen($file, 'w+');

			fclose($handle);

			$json['success'] = "success";
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));

	}
	
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'module/d_social_login')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		$this->install();
						
		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}

	public function array_merge_recursive_distinct( array &$array1, array &$array2 )
	{
	  $merged = $array1;	
	  foreach ( $array2 as $key => &$value )
		  {
			if ( is_array ( $value ) && isset ( $merged [$key] ) && is_array ( $merged [$key] ) )
			{
			  $merged [$key] = $this->array_merge_recursive_distinct ( $merged [$key], $value );
			}
			else
			{
			  $merged [$key] = $value;
			}
		  }
		
	  return $merged;
	}

	public function install() {

		// $query = $this->db->query("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '".DB_DATABASE."' AND TABLE_NAME = '" . DB_PREFIX . "customer' ORDER BY ORDINAL_POSITION"); 
		// $result = $query->rows; 
		// $columns = array();
		// foreach($result as $column){
		// 	$columns[] = $column['COLUMN_NAME'];
		// }

		// if(!in_array('facebook_id', $columns)){
		// 	 $this->db->query("ALTER TABLE " . DB_PREFIX . "customer ADD facebook_id VARCHAR( 255 )  NOT NULL");
		// }
		// if(!in_array('twitter_id', $columns)){
		// 	 $this->db->query("ALTER TABLE " . DB_PREFIX . "customer ADD twitter_id VARCHAR( 255 )  NOT NULL");
		// }
		// if(!in_array('google_id', $columns)){
		// 	 $this->db->query("ALTER TABLE " . DB_PREFIX . "customer ADD google_id VARCHAR( 255 )  NOT NULL");
		// }
		// if(!in_array('linkedin_id', $columns)){
		// 	 $this->db->query("ALTER TABLE " . DB_PREFIX . "customer ADD linkedin_id VARCHAR( 255 )  NOT NULL");
		// }
		// if(!in_array('vkontakte_id', $columns)){
		// 	 $this->db->query("ALTER TABLE " . DB_PREFIX . "customer ADD vkontakte_id VARCHAR( 255 )  NOT NULL");
		// }
		// if(!in_array('odnoklassniki_id', $columns)){
		// 	 $this->db->query("ALTER TABLE " . DB_PREFIX . "customer ADD odnoklassniki_id VARCHAR( 255 )  NOT NULL");
		// }
		// if(!in_array('live_id', $columns)){
		// 	 $this->db->query("ALTER TABLE " . DB_PREFIX . "customer ADD live_id VARCHAR( 255 )  NOT NULL");
		// }
		// if(!in_array('yandex_id', $columns)){
		// 	 $this->db->query("ALTER TABLE " . DB_PREFIX . "customer ADD yandex_id VARCHAR( 255 )  NOT NULL");
		// }
		// if(!in_array('mailru_id', $columns)){
		// 	 $this->db->query("ALTER TABLE " . DB_PREFIX . "customer ADD mailru_id VARCHAR( 255 )  NOT NULL");
		// }
		// if(!in_array('instagram_id', $columns)){
		// 	 $this->db->query("ALTER TABLE " . DB_PREFIX . "customer ADD instagram_id VARCHAR( 255 )  NOT NULL");
		// }
		// if(!in_array('paypal_id', $columns)){
		// 	 $this->db->query("ALTER TABLE " . DB_PREFIX . "customer ADD paypal_id VARCHAR( 255 )  NOT NULL");
		// }
		// if(!in_array('vimeo_id', $columns)){
		// 	 $this->db->query("ALTER TABLE " . DB_PREFIX . "customer ADD vimeo_id VARCHAR( 255 )  NOT NULL");
		// }
		// if(!in_array('tumblr_id', $columns)){
		// 	 $this->db->query("ALTER TABLE " . DB_PREFIX . "customer ADD tumblr_id VARCHAR( 255 )  NOT NULL");
		// }
		// if(!in_array('yahoo_id', $columns)){
		// 	 $this->db->query("ALTER TABLE " . DB_PREFIX . "customer ADD yahoo_id VARCHAR( 255 )  NOT NULL");
		// }
		// if(!in_array('foursquare_id', $columns)){
		// 	 $this->db->query("ALTER TABLE " . DB_PREFIX . "customer ADD foursquare_id VARCHAR( 255 )  NOT NULL");
		// }

		$this->db_authentication();

		  // $this->load->model('setting/setting');
		  // $file1 = str_replace("admin", "vqmod/xml", DIR_APPLICATION) . "a_vqmod_quickcheckout.xml_"; $file2 = str_replace("admin", "vqmod/xml", DIR_APPLICATION) . "a_vqmod_quickcheckout.xml";
		  // if (file_exists($file1)) rename($file1, $file2);
		  $this->version_check(1);	  
	}

	public function db_authentication(){
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "customer_authentication` (
		  `customer_authentication_id` int(11) NOT NULL AUTO_INCREMENT,
		  `customer_id` int(11) NOT NULL,
		  `provider` varchar(55) NOT NULL,
		  `identifier` varchar(200) NOT NULL,
		  `web_site_url` varchar(255) NOT NULL,
		  `profile_url` varchar(255) NOT NULL,
		  `photo_url` varchar(255) NOT NULL,
		  `display_name` varchar(255) NOT NULL,
		  `description` varchar(255) NOT NULL,
		  `first_name` varchar(255) NOT NULL,
		  `last_name` varchar(255) NOT NULL,
		  `gender` varchar(255) NOT NULL,
		  `language` varchar(255) NOT NULL,
		  `age` varchar(255) NOT NULL,
		  `birth_day` varchar(255) NOT NULL,
		  `birth_month` varchar(255) NOT NULL,
		  `birth_year` varchar(255) NOT NULL,
		  `email` varchar(255) NOT NULL,
		  `email_verified` varchar(255) NOT NULL,
		  `phone` varchar(255) NOT NULL,
		  `address` varchar(255) NOT NULL,
		  `country` varchar(255) NOT NULL,
		  `region` varchar(255) NOT NULL,
		  `city` varchar(255) NOT NULL,
		  `zip` varchar(255) NOT NULL,
		  `date_added` datetime NOT NULL,
		  PRIMARY KEY (`customer_authentication_id`),
		  UNIQUE KEY `identifier` (`identifier`, `provider`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;");
	}
		 
	public function uninstall() {
		  // $this->load->model('setting/setting');
		  // $file1 = str_replace("admin", "vqmod/xml", DIR_APPLICATION) . "a_vqmod_quickcheckout.xml"; $file2 = str_replace("admin", "vqmod/xml", DIR_APPLICATION) . "a_vqmod_quickcheckout.xml_";
		  // if (file_exists($file1)) rename($file1, $file2);
		  $this->version_check(0);
		  
	}

	public function check_shopunity(){
		$file1 = DIR_APPLICATION . "mbooth/xml/mbooth_shopunity_admin.xml"; 
		$file2 = DIR_APPLICATION . "mbooth/xml/mbooth_shopunity_admin_patch.xml"; 
		if (file_exists($file1) || file_exists($file2)) { 
			return true;
		} else {
			return false;
		}
	}

	public function get_light_or_full_version(){
		$full = DIR_SYSTEM . "config/d_social_login.php";
		$light = DIR_SYSTEM . "config/d_social_login_lite.php"; 
		if (file_exists($full)) { 
			return 'd_social_login';
		} elseif (file_exists($light)) {
			return 'd_social_login_lite';
		}else{
			return false;
		}

	}

	public function get_version(){
		$xml = file_get_contents(DIR_APPLICATION . 'mbooth/xml/' . $this->mbooth);

		$mbooth = new SimpleXMLElement($xml);

		return $mbooth->version ;
		}
		
	public function version_check($status = 1){
		$json = array();
		$mbooth = $this->mbooth;
		$this->load->language($this->route);
		$str = file_get_contents(DIR_APPLICATION . 'mbooth/xml/' . $this->mbooth);
		$xml = new SimpleXMLElement($str);
	
		$current_version = $xml->version ;

		$customer_url = HTTP_SERVER;
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "language` WHERE language_id = " . (int)$this->config->get('config_language_id') ); 
		$language_code = $query->row['code'];
		$ip = $this->request->server['REMOTE_ADDR'];

		$check_version_url = 'http://opencart.dreamvention.com/update/index.php?mbooth=' . $mbooth . '&store_url=' . $customer_url . '&module_version=' . $current_version . '&language_code=' . $language_code . '&opencart_version=' . VERSION . '&ip='.$ip . '&status=' .$status ;
		
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $check_version_url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$return_data = curl_exec($curl);
		$return_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);

      if ($return_code == 200) {
         $data = simplexml_load_string($return_data);
	
         if ((string) $data->version == (string) $current_version || (string) $data->version <= (string) $current_version) {
			 
           $json['success']   = $this->language->get('text_no_update');

         } elseif ((string) $data->version > (string) $current_version) {
			 
			$json['attention']   = $this->language->get('text_new_update');
				
			foreach($data->updates->update as $update){

				if((string) $update->attributes()->version > (string)$current_version){
					$version = (string)$update->attributes()->version;
					$json['update'][$version] = (string) $update[0];
				}
			}
         } else {
            $json['error']   = $this->language->get('text_error_update');
         }
      } else { 
         $json['error']   =  $this->language->get('text_error_failed');
      }
		 $json['asdasd']= 'asdasda';
	      if (file_exists(DIR_SYSTEM.'library/json.php')) { 
	         $this->load->library('json');
	         $this->response->setOutput(Json::encode($json));
	      } else {
	         $this->response->setOutput(json_encode($json));
	      }
	}
}
?>