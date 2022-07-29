<?php

namespace Api\Models;

use Psr\Container\ContainerInterface;

class Category
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

    public function getAll()
    {
        $this->load->model('catalog/category');
        $categories = $this->registry->get('model_catalog_category')->getCategoriesForApi();
        return $categories;
    }
	
    public function getCategories($data)
    {
        $this->load->model('catalog/category');
        $categories = $this->registry->get('model_catalog_category')->getCategories($data);
        return $categories;
    }

    ################  Get Category By Name ##################
    public function getByName($value)
    {
        $this->load->model('catalog/category');
        $categories = $this->registry->get('model_catalog_category')->getCategories(['filter_name'=>$value]);
        return count($categories) ? $categories[0] : null;
    }
	
	
}
