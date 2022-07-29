<?php
class ControllerSellerCatalog extends Controller {
	public function __construct($registry) {
		parent::__construct($registry);
		$this->MsLoader->MsHelper->addStyle('multiseller');
		$this->data = array_merge($this->data, $this->language->load_json('multiseller/multiseller'),$this->language->load_json('product/product'));
	}
}
?>