<?php  
class ControllerSellerContentBottom extends Controller {
	protected function index() {
		$this->template = 'default/template/multiseller/content_bottom.tpl';
								
		$this->render();
	}
}
?>