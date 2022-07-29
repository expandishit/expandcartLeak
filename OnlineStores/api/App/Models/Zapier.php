<?php

namespace Api\Models;

use Psr\Container\ContainerInterface;

class Zapier
{
    private $load;
    private $registry;
    private $config;

    public function __construct(ContainerInterface $container)
    {
        $this->registry = $container['registry'];
        $this->config = $container['config'];
        $this->load = $container['loader'];
    }


    /****************** subscribe zapier hook ***************
     * @param $type
     * @param $url
     */
    public function subscribeHook($type, $url){
        $this->load->model('module/zapier');
        $zapier_model =$this->registry->get('model_module_zapier');

        //check if zapier is installed
        if ($zapier_model->isInstalled())
            return $zapier_model->subscribeHook($type,$url);
    }


    /****************** unsubscribe zapier hook ***************
     * @param $id
     */
    public function unSubscribeHook($id){
        $this->load->model('module/zapier');
        $zapier_model =$this->registry->get('model_module_zapier');

        //check if zapier is installed
        if ($zapier_model->isInstalled())
            $zapier_model->unSubscribeHook($id);
    }

}
