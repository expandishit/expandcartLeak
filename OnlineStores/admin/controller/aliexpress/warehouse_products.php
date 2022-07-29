<?php

class ControllerAliexpressWarehouseProducts extends ControllerAliexpressBase
{
    private $error = array();

    public function index()
    {
        $this->initializer(['aliexpress/warehouses']);

        $this->load->language('aliexpress/products');

        $this->document->setTitle($this->language->get('heading_title'));

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
            'href' => $this->url->link('aliexpress/warehouse_products', '', true),
        );

        $data['products'] = array();

        $filter_data = array(
            'start' => ($page - 1) * $this->config->get('config_admin_limit'),
            'limit' => $this->config->get('config_admin_limit'),
        );

        $data['warehouses'] = $this->warehouses->getWarehouses();
        $data['warehouseManagers'] = $this->warehouses->getWarehouseManagers();

        $warehouseProducts = $this->warehouses->getWarehouseProducts($filter_data);
        $warehouseProductsTotal = $this->warehouses->getWarehouseProductsTotal($filter_data);

        if ($warehouseProducts) {
            foreach ($warehouseProducts as $key => $warehouseProduct) {
                $sold_quantity = 0;
                if ($warehouseProduct['sold_quantity']) {
                    $sold_quantity = $warehouseProduct['sold_quantity'];
                }
                if ($warehouseProduct['warehouse_quantity'] - $sold_quantity <= 0) {
                    $warehouse_quantity = 0;
                } else {
                    $warehouse_quantity = $warehouseProduct['warehouse_quantity'] - $sold_quantity;
                }
                $data['products'][] = array(
                    'warehouse_product_id' => $warehouseProduct['warehouse_product_id'],
                    'warehouseManager' => $warehouseProduct['warehouseManager'],
                    'warehouse_code' => $warehouseProduct['warehouse_code'],
                    'warehouse_title' => $warehouseProduct['warehouse_title'],
                    'product_id' => $warehouseProduct['product_id'],
                    'name' => $warehouseProduct['name'],
                    'model' => $warehouseProduct['model'],
                    'quantity' => $warehouseProduct['quantity'],
                    'warehouse_quantity' => $warehouseProduct['warehouse_quantity'],
                    'price' => $this->currency->format($warehouseProduct['price'], $this->config->get('config_currency')),
                    'status' => $warehouseProduct['approved'],
                );
            }
        }

        $pagination = new Pagination();
        $pagination->total = $warehouseProductsTotal;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_admin_limit');
        $pagination->url = $this->url->link('aliexpress/warehouse_products', 'page={page}', true);

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        if (isset($this->session->data['error_warning'])) {
            $data['error_warning'] = $this->session->data['error_warning'];
            unset($this->session->data['error_warning']);
        } else {
            $data['error_warning'] = '';
        }

        $data['pagination'] = $pagination->render();

        $this->template = 'aliexpress/products/list.expand';

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->data = $data;

        $this->response->setOutput($this->render_ecwig());
    }

    public function updateQuantity()
    {
        $json = array();
        if ('POST' == $this->request->server['REQUEST_METHOD']) {
            $this->load->language('aliexpress/products');
            if ($this->request->post) {
                if (isset($this->request->post['warehosueQuantity']) && $this->request->post['warehosueQuantity']) {
                    if (is_numeric($this->request->post['warehosueQuantity'])) {
                        $data['warehouseQuantity'] = $this->request->post['warehosueQuantity'];
                    } else {
                        $json['error'] = $this->language->get('error_warehouse_quantity_number');
                    }
                } else {
                    $json['error'] = $this->language->get('error_warehouse_quantity');
                }

                if (isset($this->request->post['warehouseProductId']) && $this->request->post['warehouseProductId']) {
                    $data['warehouseProductId'] = $this->request->post['warehouseProductId'];
                } else {
                    $json['error'] = $this->language->get('error_general');
                }

                if (isset($this->request->post['productId']) && $this->request->post['productId']) {
                    $data['productId'] = $this->request->post['productId'];
                } else {
                    $json['error'] = $this->language->get('error_general');
                }

                if (!$json) {
                    $this->initializer(['aliexpress/warehouses']);
                    $result = $this->warehouses->updateWarehouseQuantity($data);
                    if ($result) {
                        $json['success'] = $this->language->get('success_quantity_update');
                    } else {
                        $json['error'] = $this->language->get('error_main_quantity');
                    }
                }
            } else {
                $json['error'] = $this->language->get('error_general');
            }
        } else {
            $json['error'] = $this->language->get('error_general');
        }

        $this->response->setOutput(json_encode($json));
    }

    public function delete()
    {
        $this->load->language('aliexpress/products');
        $this->initializer(['aliexpress/warehouses']);
        if (('POST' == $this->request->server['REQUEST_METHOD']) && $this->validateModification()) {
            if (isset($this->request->post['producAction']) && $this->request->post['producAction']) {
                if (1 == $this->request->post['producAction']) {
                    if (isset($this->request->post['selected']) && $this->request->post['selected']) {
                        $this->approve($this->request->post['selected']);
                    } else {
                        $this->session->data['error_warning'] = $this->language->get('error_warehouse_product_approve_no_select');
                        $this->response->redirect($this->url->link('aliexpress/warehouse_products', '', true));
                    }
                } elseif (2 == $this->request->post['producAction']) {
                    if (isset($this->request->post['selected']) && $this->request->post['selected']) {
                        $this->disapprove($this->request->post['selected']);
                    } else {
                        $this->session->data['error_warning'] = $this->language->get('error_warehouse_product_disapprove_no_select');
                        $this->response->redirect($this->url->link('aliexpress/warehouse_products', '', true));
                    }
                }
            }
            if (isset($this->request->post['selected']) && $this->request->post['selected']) {
                foreach ($this->request->post['selected'] as $warehouseProductId) {
                    $this->warehouses->deleteWarehouseProduct($warehouseProductId);
                }
                $this->session->data['success'] = $this->language->get('success_warehouse_product_delete');
            } else {
                $this->session->data['error_warning'] = $this->language->get('error_warehouse_product_delete_no_select');
            }
        } else {
            $this->session->data['error_warning'] = $this->language->get('error_warehouse_product_delete_general');
        }
        $this->response->redirect($this->url->link('aliexpress/warehouse_products', '', true));
    }

    private function approve($data)
    {
        if ($data) {
            foreach ($data as $warehouseProductId) {
                $this->warehouses->approveWarehouseProduct($warehouseProductId);
            }
            $this->session->data['success'] = $this->language->get('success_warehouse_product_approve');
        } else {
            $this->session->data['error_warning'] = $this->language->get('error_warehouse_product_approve_no_select');
        }
        $this->response->redirect($this->url->link('aliexpress/warehouse_products', '', true));
    }

    private function disapprove($data)
    {
        if ($data) {
            foreach ($data as $warehouseProductId) {
                $this->warehouses->disapproveWarehouseProduct($warehouseProductId);
            }
            $this->session->data['success'] = $this->language->get('success_warehouse_product_disapprove');
        } else {
            $this->session->data['error_warning'] = $this->language->get('error_warehouse_product_disapprove_no_select');
        }
        $this->response->redirect($this->url->link('aliexpress/warehouse_products', '', true));
    }

    protected function validateModification()
    {
        if (!$this->user->hasPermission('modify', 'aliexpress/warehouse_products')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }
}
