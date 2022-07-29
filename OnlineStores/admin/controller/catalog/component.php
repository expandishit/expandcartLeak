<?php

class ControllerCatalogComponent extends Controller {

    private $products_limit = null;
    private $plan_id = PRODUCTID;

    public function __construct($registry) {
        parent::__construct($registry);
        $this->load->model('plan/trial');
        $trial = $this->model_plan_trial->getLastTrial();
        if ($trial){
            $this->plan_id = $trial['plan_id'];
        }

        $this->products_limit =  $this->genericConstants["plans_limits"][$this->plan_id]['products_limit'];

    }

    public function products(){
        $content_url=$this->request->get['content_url'];
        if ($content_url == null || trim($content_url) == ""){
            $content_url = "catalog/product";
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
            'href' => $this->url->link('catalog/index/products', 'content_url='.$content_url, true)
        );

        $data['content_url'] = $content_url;

        try {
            $data['content'] = $this->getChild($content_url);
        }
        catch (\ExpandCart\Foundation\Exceptions\FileException $e) {
            $this->redirect($this->url->link('common/dashboard', '', 'SSL'));
        }

        $this->initializer([
            'ProductAttachments' => 'module/product_attachments'
        ]);

        $this->load->model('catalog/product');

        $data['product_attachments_app_status'] = $this->ProductAttachments->isActive();

        if ($content_url == "catalog/product"){
            $total_products_count = $this->model_catalog_product->getTotalProductsCount();
            $data['limit_reached'] =
                ( $this->products_limit && ( ($total_products_count + 1) > $this->products_limit ) )
                ||
                ( KANAWAT_PRODUCTSLIMIT != -1 && $total_products_count >= KANAWAT_PRODUCTSLIMIT && $this->plan_id  == 52)
            ;

            $data['limit_warning'] =
                $this->products_limit &&
                (
                    ($this->model_catalog_product->getTotalProductsCount())
                    >=
                    ($this->products_limit - $this->genericConstants["plans_limits"][$this->plan_id]['products_warning_diff'])
                )
            ;
        }

        $this->template = 'catalog/products.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );
        $this->data=$data;

        $this->response->setOutput($this->render());
    }
    public function collection(){
        $content_url=$this->request->get['content_url'];
        if ($content_url == null || trim($content_url) == ""){
            $content_url = "catalog/category";
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
            'href' => $this->url->link('catalog/category', 'content_url='.$content_url, true)
        );

        $data['content_url'] = $content_url;

        try {
            $data['content'] = $this->getChild($content_url);
        }
        catch (\ExpandCart\Foundation\Exceptions\FileException $e) {
            $this->redirect($this->url->link('common/dashboard', '', 'SSL'));
        }

        $this->template = 'catalog/collection.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );
        $this->data=$data;

        $this->response->setOutput($this->render());
    }



}