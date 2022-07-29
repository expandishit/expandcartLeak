<?php

class ControllerAccountStoreLocker extends Controller
{
    public function index()
    {
        if (!$this->customer->isLogged()) {
            $this->redirect($this->url->link('common/home', '', 'SSL'));
        }
        
        $config = $this->config->get('enable_storable_products');
        
        if ($config['status'] != 1) {
            $this->redirect($this->url->link('common/home', '', 'SSL'));
        }
        
        $this->data['taps'] = $this->getChild('account/taps/renderTaps', 'account-store-locker');
        
        $this->language->load_json('account/order');

        $this->document->setTitle($this->language->get('store_locker_heading_title'));

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home'),
            'separator' => false
        );

        // $this->data['breadcrumbs'][] = array(           
        //     'text'      => $this->language->get('text_account'),
        //     'href'      => $this->url->link('account/account', '', 'SSL'),
        //     'separator' => $this->language->get('text_separator')
        // );

        $this->data['breadcrumbs'][] = array(           
            'text'      => $this->language->get('store_locker_heading_title'),
            'href'      => $this->url->link('account/courses', '', 'SSL'),
            'separator' => $this->language->get('text_separator')
        );

        $this->initializer(['account/order']);

        $this->data['products'] = $this->order->getCustomerStorableOrderProducts($this->customer->getID());

        $this->template = $this->checkTemplate('account/store-locker-list.expand');
        
        if (defined('LOGIN_MODE') && LOGIN_MODE === 'identity' && $this->identity->isStoreOnWhiteList()) {
            $this->template = 'default/template/account/store-locker-list.expand';
        }
        
        $this->response->setOutput($this->render_ecwig());
    }
}
