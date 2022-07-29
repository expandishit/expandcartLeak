<?php

namespace Api\Models;

use Psr\Container\ContainerInterface;

class Manufacturer
{
    private $load;
    private $registry;
    private $languagecodes;
    private $languageids;
    private $config;

    public function __construct(ContainerInterface $container)
    {
        $this->registry = $container['registry'];
        $this->config = $container['config'];
        $this->load = $container['loader'];
        $this->languagecodes = $container['languagecodes'];
        $this->languageids = $container['languageids'];
    }

	 public function getManufacturers($data)
    {
        $this->load->model('catalog/manufacturer');
        $manufacturers = $this->registry->get('model_catalog_manufacturer')->getManufacturers($data);
        return $manufacturers;
    }

    ################  Get Manufacturer By Name ##################
    public function getByName($name)
    {
        $this->load->model('catalog/manufacturer');
        $manufacturers = $this->registry->get('model_catalog_manufacturer')->getManufacturers(['filter_name'=>$name]);
        return count($manufacturers) ? $manufacturers[0] : null;
    }
    
	
}
