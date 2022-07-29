<?php
class ModelPaymentBookeey extends Model {

    public function install(){
		$this->load->model('setting/setting');

   		$this->model_setting_setting->insertUpdateSetting( 'bookeey', ['bookeey_payment_modes' =>
   			[
   				['code' => 'bookeey' , 'name' => 'Bookeey'], 
   				['code' => 'knet'    , 'name' => 'K-Net'], 
   				['code' => 'credit'  , 'name' => 'Credit Cards'], 
   				['code' => 'amex'    , 'name' => 'Amex']
   			]
   		]);
	}
}
