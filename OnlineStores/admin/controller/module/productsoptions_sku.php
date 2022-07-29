<?php
class ControllerModuleProductsOptionsSKU extends Controller {
	private $error = array(); 
	private $_name = "productsoptions_sku";
	public function index() {
        $this->load->model('marketplace/common');

        $market_appservice_status = $this->model_marketplace_common->hasApp('productsoptions_sku');
        if (!$market_appservice_status['hasapp']) {
            $this->redirect($this->url->link('marketplace/app', 'id=' . $market_appservice_status['appserviceid'], 'SSL'));
            return;
        }

		$this->language->load('module/' . $this->_name );

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
                $data = $this->request->post;
                if($data['productsoptions_sku_option_mapping'])
                    $data['productsoptions_sku_option_mapping'] = serialize($data['productsoptions_sku_option_mapping']);

                $this->model_setting_setting->editSetting($this->_name, $this->request->post);    
                $result_json['success_msg'] = $this->language->get('text_success');
                $result_json['success'] = '1';
            }

            $this->response->setOutput(json_encode($result_json));
			return;
		}
				
		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');

		$this->data['entry_status'] = $this->language->get('entry_status');
		
		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');
		
 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

        if (isset($this->request->post[$this->_name . '_status'])) {
            $this->data[$this->_name . '_status'] = $this->request->post[$this->_name . '_status'];
        } else {
            $this->data[$this->_name . '_status'] = $this->config->get($this->_name . '_status');
        }

        if (isset($this->request->post[$this->_name . '_relational_status'])) {
            $this->data[$this->_name . '_relational_status'] = $this->request->post[$this->_name . '_relational_status'];
        } else {
            $this->data[$this->_name . '_relational_status'] = $this->config->get($this->_name . '_relational_status');
        }

        if (isset($this->request->post['option_relation'])) {
            $this->data['productsoptions_sku_option_mapping'] = $this->request->post['productsoptions_sku_option_mapping'];
        } else {
            $this->data['productsoptions_sku_option_mapping'] = $this->config->get('productsoptions_sku_option_mapping');
        }

        //Product options
        $this->load->model('catalog/option');
        $options = $this->model_catalog_option->getOptions(['option_type' => 'select']);
        $finalOptions = [];
        foreach ($options as $option){
            //TO DO: fix knawat importing options with no name
            if(!$option['name'] || $option['name'] == '-')
                continue;
            $finalOptions[] = $option;
        }

        $this->data['options'] = $finalOptions;
        $this->data['options_count'] = count($finalOptions);
        /////////////////
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

        $this->data['cancel'] = $this->url->link('marketplace/home', '', 'SSL');

						
		$this->template = 'module/' . $this->_name . '.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}
	
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'module/' . $this->_name)) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}

    public function install() {
        $query = "CREATE TABLE IF NOT EXISTS `product_variations` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `product_id` int(11) NOT NULL,
                  `option_value_ids` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
                  `product_sku` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                  `product_barcode` VARCHAR(255) NULL DEFAULT NULL,
                  `num_options` int(11) NOT NULL,
                  `product_price` DOUBLE DEFAULT NULL,
                  PRIMARY KEY (`id`)
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

        $this->db->query($query);

        $showColumns = $this->db->query('SHOW COLUMNS FROM product_variations');

        $columns = array_flip(array_column($showColumns->rows, 'Field'));

        if (!isset($columns['product_quantity'])) {
            $this->db->query("ALTER TABLE `product_variations` ADD `product_quantity` INT(11) NOT NULL AFTER `product_sku`");
        }

        if (!isset($columns['product_price'])) {
            $this->db->query("ALTER TABLE `product_variations` ADD `product_price` DOUBLE DEFAULT NULL");
        }

        $this->db->query("INSERT INTO " . DB_PREFIX . "extension SET `type` = 'module', `code` = '" . $this->_name . "'");
    }

    public function uninstall() {

	    //check if knawat install then prevent uninstall
        $this->load->model('module/knawat');

        if (!$this->model_module_knawat->isInstalled()) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "extension WHERE `type` = 'module' AND `code` = '" . $this->_name . "'");
            $this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE `group` = '" . $this->_name . "'");
        }
    }
}
?>
