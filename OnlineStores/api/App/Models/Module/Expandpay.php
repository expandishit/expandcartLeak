<?php
namespace Api\Models\Module;

use Psr\Container\ContainerInterface;


class Expandpay
{
 
	private $load;
    private $registry;
    private $config;
    private $expandpay_model;
    private $expandish_order_model;
    public function __construct(ContainerInterface $container)
    {
        $this->registry = $container['registry'];
        $this->config 	= $container['config'];
        $this->load 	= $container['loader'];
        $path = ONLINE_STORES_PATH . 'OnlineStores/admin/';

        $this->load->model('setting/setting',false,$path);

        $this->setting_model = $this->registry->get('model_setting_setting');

		$this->load->model('sale/order',false,$path);

        $this->order_model = $this->registry->get('model_sale_order');
    }
	
    public function editSettingValue($group = '', $key = '', $value = ''){
        $this->setting_model->editSettingValue($group,$key,$value);
        return true;
    }
    public function confirm($order_id,$order_status){
        $this->order_model->confirm($order_id,$order_status);
        return true;
    }

    public function getOrder($order_id){
        return $this->order_model->getOrder($order_id);
    }

}
