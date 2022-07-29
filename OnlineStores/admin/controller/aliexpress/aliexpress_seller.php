<?php

class ControllerAliexpressAliexpressSeller extends ControllerAliexpressBase
{
    private $error = array();

    public function index()
    {
        $this->load->language('aliexpress/seller');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->initializer(['aliexpress/warehouses']);

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', '', true),
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('aliexpress/aliexpress_seller', ''.$url, true),
        );

        $data['sellers'] = array();

        $filter_data = array(
            'start' => ($page - 1) * $this->config->get('config_admin_limit'),
            'limit' => $this->config->get('config_admin_limit'),
        );

        $seller_total = $this->warehouses->getTotalSellers($filter_data);

        $results = $this->warehouses->getSellers($filter_data);

        foreach ($results as $result) {
            $data['sellers'][] = array(
                'aliexpress_seller_id' => $result['id'],
                'seller_name' => $result['seller_name'],
                'products' => $result['products'],
                'edit' => $this->url->link(
                    'aliexpress/aliexpress_product',
                    'filter_aliexpress_seller_id=' . $result['id'],
                    true
                ),
            );
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        if (isset($this->request->post['selected'])) {
            $data['selected'] = (array) $this->request->post['selected'];
        } else {
            $data['selected'] = array();
        }

        $pagination = new Pagination();
        $pagination->total = $seller_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_admin_limit');
        $pagination->url = $this->url->link('aliexpress/aliexpress_seller', 'page={page}', true);

        $data['pagination'] = $pagination->render();

        $this->template = 'aliexpress/aliexpress/seller.expand';

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->data = $data;

        $this->response->setOutput($this->render_ecwig());
    }

    public function delete()
    {
        $this->load->language('aliexpress/seller');

        $this->document->setTitle($data['heading_title']);

        $this->initializer(['aliexpress/warehouses']);

        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            foreach ($this->request->post['selected'] as $aliexpress_seller_id) {
                $this->warehouses->deleteSeller($aliexpress_seller_id);
            }

            $this->session->data['success'] = $this->language->get('text_success');
        }

        $this->response->redirect($this->url->link('aliexpress/aliexpress_seller', '', true));
    }

    protected function validateDelete()
    {
        if (!$this->user->hasPermission('modify', 'aliexpress/aliexpress_seller')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }
}
