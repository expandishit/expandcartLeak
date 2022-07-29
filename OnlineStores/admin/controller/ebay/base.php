<?php

abstract class ControllerEbayBase extends Controller
{
    public function __construct($registry)
    {
        parent::__construct($registry);

        if (!$this->config->get('module_wk_ebay_dropship_status')) {

            $this->response->redirect($this->url->link('common/dashboard','' , 'SSL'));
        }
    }
}
