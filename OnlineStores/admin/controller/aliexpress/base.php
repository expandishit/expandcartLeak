<?php

abstract class ControllerAliexpressBase extends Controller
{
    public function __construct($registry)
    {
        parent::__construct($registry);

        if (!$this->config->get('module_wk_dropship_status')) {
            
            $this->response->redirect($this->url->link('common/dashboard','' , 'SSL'));
        }
    }
}
