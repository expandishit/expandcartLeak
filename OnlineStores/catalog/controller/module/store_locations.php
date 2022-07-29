<?php

class ControllerModuleStoreLocations extends Controller {
	protected function index($setting) {
		//Load the language file for this module - catalog/language/module/store_locations.php
		$this->language->load('module/store_locations');

		//Get the title from the language file
      	$this->data['heading_title'] = $this->language->get('heading_title');

		//Load any required model files - catalog/product is a common one, or you can make your own DB access
		//methods in catalog/model/module/store_locations.php
		$this->load->model('module/store_locations');

		$Locations = $this->model_module_store_locations->getList(0,$this->config->get('store_locations_per_page'));
		$this->data['Locations'] = array();
		$ctr = 0;
		foreach($Locations as $Location) {
			$Location['href'] = $this->url->link('common/location_details&loc_id=' . $Location['ID']);
			$this->data['Locations'][$ctr] = $Location;
			$ctr++;
		}
		 		
		$this->data['txt_All_Locations'] = $this->language->get('txt_All_Locations');
		$this->data['link_all_locations'] = $this->url->link('common/store_locations');

		//Choose which template to display this module with
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/store_locations.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/module/store_locations.tpl';
		} else {
			$this->template = 'default/template/module/store_locations.tpl';
		}

		//Render the page with the chosen template
		$this->render();
	}
}
?>