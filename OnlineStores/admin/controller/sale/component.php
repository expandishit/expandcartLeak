<?php

class ControllerSaleComponent extends Controller {
    public function orders(){
        $content_url=$this->request->get['content_url'];
        if ($content_url == null || trim($content_url) == ""){
            $content_url = "sale/order";
        }

        $data = array();
        $data = array_merge($data, $this->load->language($content_url));

        $this->document->setTitle($this->language->get('heading_title'));
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', '' , true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('sale/index/order?', 'content_url='.$content_url, true)
        );


        $data['content_url'] = $content_url;

        try {
            $data['content'] = $this->getChild($content_url);
        }
        catch (\ExpandCart\Foundation\Exceptions\FileException $e) {
            $this->redirect($this->url->link('common/dashboard', '', 'SSL'));
        }

        $this->initializer([
            'AbandonedCart' => 'module/abandoned_cart/settings'
        ]);

        $data['abandoned_cart_app_status'] = $this->AbandonedCart->isActive();

        $this->template = 'sale/orders.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );
        $this->data=$data;

        $this->response->setOutput($this->render());
    }

    public function customers(){
        $content_url=$this->request->get['content_url'];
        if ($content_url == null || trim($content_url) == ""){
            $content_url = "sale/customer";
        }

        $data = array();
        $data = array_merge($data, $this->load->language($content_url));

        $this->document->setTitle($this->language->get('heading_title'));
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', '' , true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('sale/index/customers', 'content_url='.$content_url, true)
        );

        $data['content_url'] = $content_url;

        try {
            $data['content'] = $this->getChild($content_url);
        }
        catch (\ExpandCart\Foundation\Exceptions\FileException $e) {
            $this->redirect($this->url->link('common/dashboard', '', 'SSL'));
        }
        $this->template = 'sale/customers.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );
        $this->data=$data;

        $this->response->setOutput($this->render());
    }
}