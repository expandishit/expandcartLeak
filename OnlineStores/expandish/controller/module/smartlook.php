<?php
/* 
 * @smartlook
 */  
class ControllerModuleSmartLook extends Controller {
    
    private $_name = 'smartlook';

	protected function index() {
		static $module = 0;
		
		$this->load->model('setting/store');
		
		$this->data['store'] = $this->config->get('config_name');

        $this->language->load_json('module/' . $this->_name);

        $this->data['smartlook_tracking'] = $this->config->get('smartlook_tracking');

        $status = $this->config->get('status');

        if($status == 1){
            $this->data['enabled'] = true;
        }
        else{
            $this->data['enabled'] = false;
        }

        $this->template = $this->checkTemplate('module/smartlook.expand');

		$this->render_ecwig();
	}
}
?>
		
		