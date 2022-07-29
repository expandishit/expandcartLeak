<?php  
class ControllerSellerContentTop extends Controller {
	protected function index() {
		$this->template = 'default/template/multiseller/content_top.tpl';
								
		$this->render();
	}
}
?>