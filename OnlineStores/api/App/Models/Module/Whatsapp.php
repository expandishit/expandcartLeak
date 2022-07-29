<?php
namespace Api\Models\Module;

use Psr\Container\ContainerInterface;


class Whatsapp
{
 
	private $load;
    private $registry;
    private $config;
    private $whatsapp_model;

    public function __construct(ContainerInterface $container)
    {
        $this->registry = $container['registry'];
        $this->config 	= $container['config'];
        $this->load 	= $container['loader'];

        $this->load->model('module/whatsapp');
        $this->whatsapp_model = $this->registry->get('model_module_whatsapp');
    }
	
	/**
     *  
	 * validate webhook signature 
     * 
     */
	public function validateSignature($body, $header_signature = ''): bool
    { 
        $is_valid  = $this->whatsapp_model->validateSignature($body, $header_signature);
        return $is_valid;
    }

}
