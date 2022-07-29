<?php

namespace Api\Models;

use Psr\Container\ContainerInterface;

class Download
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

	 public function getDownloads($data)
    {
        $this->load->model('catalog/download');
        $downloads = $this->registry->get('model_catalog_download')->getDownloads($data);
        return $downloads;
    }
    
	
}
