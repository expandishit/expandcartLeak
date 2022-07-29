<?php
class ControllerModuleProductOptionImagePro extends Controller {
	private $error = array(); 
	
	public function index() {
        $this->load->model('marketplace/common');
        $market_appservice_status = $this->model_marketplace_common->hasApp('product_option_image_pro');
        if (!$market_appservice_status['hasapp']) {
            $this->redirect($this->url->link('marketplace/app', 'id=' . $market_appservice_status['appserviceid'], 'SSL'));
            return;
        }

		$this->load->language('module/product_option_image_pro');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');
				
		if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
		{
			
			if ( !$this->validate() )
			{
				$result_json['success'] = '0';
				$result_json['error'] = $this->error;
			}
			else
			{
				$this->model_setting_setting->editSetting('poip_module', $this->request->post);	
				$this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');
				$result_json['success'] = '1';
			}

			$this->response->setOutput(json_encode($result_json));
			return;
		}
				
		$this->data['heading_title'] 							= $this->language->get('heading_title');

		$this->data['text_enabled']								= $this->language->get('text_enabled');
		$this->data['entry_import'] 							= $this->language->get('entry_import');
		$this->data['entry_import_description'] 	= $this->language->get('entry_import_description');
		$this->data['button_upload'] 							= $this->language->get('button_upload');		
		$this->data['button_upload_help'] 				= $this->language->get('button_upload_help');
		$this->data['entry_server_response'] 			= $this->language->get('entry_server_response');
		$this->data['entry_import_result'] 				= $this->language->get('entry_import_result');
		
		$this->data['entry_settings'] 						= $this->language->get('entry_settings');
		$this->data['text_disabled'] 							= $this->language->get('text_disabled');
		
		$this->data['entry_layout'] 							= $this->language->get('entry_layout');
		$this->data['entry_position'] 						= $this->language->get('entry_position');
		$this->data['entry_status'] 							= $this->language->get('entry_status');
		$this->data['entry_sort_order'] 					= $this->language->get('entry_sort_order');
		
		$this->data['button_save'] 								= $this->language->get('button_save');
		$this->data['button_cancel'] 							= $this->language->get('button_cancel');
		$this->data['button_add_module'] 					= $this->language->get('button_add_module');
		$this->data['button_remove'] 							= $this->language->get('button_remove');
		
		$this->data['module_description'] 				= $this->language->get('module_description');
		$this->data['module_info'] 								= $this->language->get('module_info');
		
		$this->data['PHPExcelNotFound'] 					= $this->language->get('PHPExcelNotFound');
		
		$this->data['PHPExcelExists']							= file_exists($this->PHPExcelPath());
		$this->data['PHPExcelPath']				 				= $this->PHPExcelPath(true);
		
		$this->data['token'] = null;
		
		$this->load->model('module/product_option_image_pro');
		$settings_names = $this->model_module_product_option_image_pro->getSettingsNames(false);
		foreach ($settings_names as $setting_name) {
			$this->data['entry_'.$setting_name] = $this->language->get('entry_'.$setting_name);
			$this->data['entry_'.$setting_name.'_help'] = $this->language->get('entry_'.$setting_name.'_help');
		}
		$this->data['settings_names'] = $settings_names;
		
		$settings_values = $this->model_module_product_option_image_pro->getSettingsValues();
		foreach ($settings_values as $setting_value) {
			$this->data['entry_'.$setting_value] = $this->language->get('entry_'.$setting_value);
		}
		$this->data['settings_values'] = $settings_values;
		
 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}
		
		if (isset($this->session->data['success'])) {
      $this->data['success'] = $this->session->data['success'];
      unset($this->session->data['success']);
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
					'href'      => $this->url->link('module/product_option_image_pro', '', 'SSL'),
      		'separator' => ' :: '
   		);
		
		$this->data['action'] = $this->url->link('module/product_option_image_pro', '', 'SSL');
		
		$this->data['cancel'] = $this->url->link('marketplace/home', '', 'SSL');

		$this->data['modules'] = array();
		
		if (isset($this->request->post['poip_module'])) {
			$this->data['modules'] = $this->request->post['poip_module'];
		} elseif ($this->config->get('poip_module')) {
			$this->data['modules'] = $this->config->get('poip_module');
		}
		
		
		$this->load->model('design/layout');
		
		$this->data['layouts'] = $this->model_design_layout->getLayouts();
						
		$this->template = 'module/product_option_image_pro.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}
	
	
	private function PHPExcelPath($short = false) {
		if ($short) {
			return './system/PHPExcel/Classes/PHPExcel.php';
		} else {
			return DIR_SYSTEM . '/PHPExcel/Classes/PHPExcel.php';
		}
	}
	
	
	public function import() {
		
		$this->load->model('module/product_option_image_pro');
		
		$json = array();
		
		if (!empty($this->request->files['file']['name'])) {
			
			ini_set('display_errors', 1);
			error_reporting(E_ALL);
			
			require_once $this->PHPExcelPath();
			
			$cacheMethod = PHPExcel_CachedObjectStorageFactory:: cache_to_phpTemp; //PHPExcel_CachedObjectStorageFactory::cache_to_discISAM ; //
			$cacheSettings = array( 'memoryCacheSize' => '32MB');
			PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
			
			$excel = PHPExcel_IOFactory::load($this->request->files['file']['tmp_name']); // PHPExcel
			$sheet = $excel->getSheet(0);
			
			$data = $sheet->toArray();
			
			//$this->log->write( print_r($data,true) );
			
			if (count($data) > 1) {
				
				$this->model_module_product_option_image_pro->deleteAllImages();
				
				$head = array_flip($data[0]);
				
				if (!isset($head['product_id'])) {
					$json['error'] = "product_id not found";
				}
				
				if (!isset($head['image'])) {
					$json['error'] = "image not found";
				}
				
				if (!isset($head['option_value_id'])) {
					$json['error'] = "option_value_id not found";
				}
				
				if (!isset($json['error'])) {
					
					$images = 0;
					$skipped = 0;
					for ($i=1;$i<count($data);$i++) {
						
						$row = $data[$i];
						
						if (trim((string)$row[$head['image']]) != "") {
						
							if ($this->model_module_product_option_image_pro->add_product_option_value_image((int)$row[$head['product_id']], (int)$row[$head['option_value_id']], (string)$row[$head['image']])) {
								$images++;
							} else {
								$skipped++;
							}
						
						}
						
					}
					
					$json['rows'] = count($data)-1;
					$json['images'] = $images;
					$json['skipped'] = $skipped;
					
				}
				
			} else {
				$json['error'] = "empty table";
			}
			
			
			
		} else {
			$json['error'] = "file not uploaded";
		}
		
		$this->response->setOutput(json_encode($json));
	}
	
	
	public function install() {
		
    $this->load->model('module/product_option_image_pro');
    $this->model_module_product_option_image_pro->install();
  }
  
  public function uninstall() {
		
		$this->load->model('module/product_option_image_pro');
		$this->model_module_product_option_image_pro->uninstall();
		
  }
	
	private function validate() {
		if (!$this->user->hasPermission('modify', 'module/product_option_image_pro')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}
}
?>