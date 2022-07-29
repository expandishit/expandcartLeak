<?php
namespace Api\Models\Module;

use Psr\Container\ContainerInterface;


class Paypal
{
 
	private $load;
    private $registry;
    private $config;
    private $paypal_model;

    public function __construct(ContainerInterface $container)
    {
        $this->registry = $container['registry'];
        $this->config 	= $container['config'];
        $this->load 	= $container['loader'];

        $this->load->model('payment/paypal');
        $this->paypal_model = $this->registry->get('model_payment_paypal');

    }
	
	public function createTokent() { //any transaction done with PayPal is need token to auth
        return $this->paypal_model->createTokent();
    }
	
	public function checkMerchantstatus($merchant_id,$token="") { 
        return $this->paypal_model->checkMerchantstatus($merchant_id,$token);
    }

	public function addAccountRecord($data) { 
        return $this->paypal_model->addAccountRecord($data);
    }
	
	public function uninstall(){
		/*
		$this->load->model('setting/extension');
		$model_setting_extension = $this->registry->get('model_setting_extension');	
		$model_setting_extension->uninstall('payment', 'paypal');
		*/
		$this->paypal_model->uninstall();
		
		$this->load->model('setting/setting');
        $model_setting_setting = $this->registry->get('model_setting_setting');
        $model_setting_setting->deleteSetting('paypal');
		
		 
	}

	
	public function paypalOrderData($order_id,$token=""){
		return $this->paypal_model->orderData($order_id,$token);
	}
	
	public function orderData($order_id){
		$this->load->model('sale/order');
		return $this->registry->get('model_sale_order')->getOrder($order_id);
	}
	
	public function updateOrderStatus($order_id,$data=[]){
		$this->load->model('sale/order');
		return $this->registry->get('model_sale_order')->addOrderHistory($order_id, $data);
    }
	
	public function insertTransaction(array $data) : int{
		$this->load->model('extension/payment_transaction');
		return $this->registry->get('model_extension_payment_transaction')->insert($data);
	}
	
	public function updateTransaction(int $id ,array $data) : int{
		$this->load->model('extension/payment_transaction');
		return $this->registry->get('model_extension_payment_transaction')->update($id,$data);
	}
	
	public function selectTransactionsByFilters($filters=[]) {
		$this->load->model('extension/payment_transaction');
		return $this->registry->get('model_extension_payment_transaction')->selectByFilters($filters);
	}
	
	public function track_transaction(array $data)
	{
		$this->load->model('extension/payment_transaction');
		return $this->registry->get('model_extension_payment_transaction')->track_transaction($data);
    }
	public function getPaymentMethodId($method_code='paypal'){
		$this->load->model('extension/payment_method');
		return $this->registry->get('model_extension_payment_method')->selectByCode($method_code)['id'];
	}
	
	
}
