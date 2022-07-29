<?php
class ControllerModuleProductAffiliateLink extends Controller {
	private $error = array(); 
	private $_name = "product_affiliate_link";
	public function index() {
        $this->load->model('marketplace/common');

        $market_appservice_status = $this->model_marketplace_common->hasApp($this->_name);
        if (!$market_appservice_status['hasapp']) {
            $this->redirect($this->url->link('marketplace/app', 'id=' . $market_appservice_status['appserviceid'], 'SSL'));
            return;
        }

		$this->language->load('module/' . $this->_name );

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');
				
		if ( $this->request->server['REQUEST_METHOD'] == 'POST')
        {
            if ( !$this->validate() )
            {
                $result_json['success'] = '0';
                $result_json['error'] = $this->error;
            }
            else
            {
                $this->model_setting_setting->editSetting($this->_name, $this->request->post);
                $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');
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
	    $query = $this->db->query("SELECT COUNT(*) colcount
                                    FROM INFORMATION_SCHEMA.COLUMNS
                                    WHERE  table_name = 'product'
                                    AND table_schema = DATABASE()
                                    AND column_name = 'affiliate_link'");
	    $result = $query->row;
	    $colcount = $result['colcount'];

	    if($colcount <=0 ) {
            $this->db->query("ALTER TABLE `product` ADD COLUMN `affiliate_link`  varchar(3000) NULL");
        }

        $this->db->query("INSERT INTO " . DB_PREFIX . "extension SET `type` = 'module', `code` = '" . $this->_name . "'");
    }

    public function uninstall() {
        $this->db->query("DELETE FROM " . DB_PREFIX . "extension WHERE `type` = 'module' AND `code` = '" . $this->_name . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE `group` = '" . $this->_name . "'");
    }
}
?>