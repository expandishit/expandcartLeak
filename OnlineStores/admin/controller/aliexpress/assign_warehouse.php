<?php

class ControllerAliexpressAssignWarehouse extends ControllerAliexpressBase
{
    public function index()
    {
        $this->load->language('aliexpress/assign_warehouse');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->initializer(['aliexpress/assign_warehouse']);

        $selected_warehouse = 0;
        if (isset($this->request->get['id']) && $this->request->get['id']) {
            $data['selected_warehouse'] = $selected_warehouse = $this->request->get['id'];
        } else {
            $data['selected_warehouse'] = '';
        }

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', '', 'SSL'),
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('aliexpress/assign_warehouse', '', 'SSL'),
        );

        $data['psroducts'] = array();

        $filter_data = array(
            'selected_warehouse' => $selected_warehouse,
            'start' => ($page - 1) * $this->config->get('config_admin_limit'),
            'limit' => $this->config->get('config_admin_limit'),
        );

        if (('POST' == $this->request->server['REQUEST_METHOD']) && $this->validateForm()) {
            foreach ($this->request->post['selected'] as $value) {
                $this->assign_warehouse->addProducts(
                    $this->request->post[$value],
                    $value,
                    $this->request->post['warehouse']
                );
            }

            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('aliexpress/assign_warehouse', '', 'SSL'));
        }

        $warehouses = $this->assign_warehouse->getTotalWarehouseNames(true);

        $data['warehouses'] = array();

        if ($warehouses) {
            foreach ($warehouses as $key => $warehouse) {
                $data['warehouses'][] = array(
                    'warehouse_id' => $warehouse['warehouse_id'],
                    'title' => $warehouse['title'],
                    'url' => $this->url->link('aliexpress/assign_warehouse', 'id='.$warehouse['warehouse_id'], true),
                );
            }
        }

        $product_total = $this->assign_warehouse->getTotalProduct($filter_data);
        $products = $this->assign_warehouse->getProducts($filter_data);
        $data['products'] = array();
        if ($products) {
            $this->load->model('tool/image');
            foreach ($products as $key => $product) {
                $warehouse_quantities = $this->assign_warehouse->checkQuantity($product['product_id'], $selected_warehouse);
                $image = $this->model_tool_image->resize($product['image'], 40, 40);

                if (!$warehouse_quantities) {
                    $warehouse_quantities = 0;
                }
                $data['products'][] = array(
                    'image' => $image,
                    'product_id' => $product['product_id'],
                    'name' => $product['name'],
                    'model' => $product['model'],
                    'total_quantity' => $product['quantity'],
                    'price' => $product['price'],
                    'status' => $product['status'],
                    'warehouse_quantities' => $warehouse_quantities,
                );
            }
        }

        $pagination = new Pagination();
        $pagination->total = $product_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_admin_limit');
        $pagination->url = $this->url->link('aliexpress/assign_warehouse', 'page={page}', 'SSL');

        $data['pagination'] = $pagination->render();

        if (isset($this->request->get['id']) && $this->request->get['id']) {
            $data['action'] = $this->url->link('aliexpress/assign_warehouse', 'id='.$this->request->get['id'], 'SSL');
        } else {
            $data['action'] = $this->url->link('aliexpress/assign_warehouse', '', 'SSL');
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['selected'])) {
            $data['error_warning'] = $this->error['selected'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['quantity'])) {
            $data['error_quantity'] = $this->error['quantity'];
        } else {
            $data['error_quantity'] = array();
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        if (isset($this->error['warehouse'])) {
            $data['error_warehouse'] = $this->error['warehouse'];
        } else {
            $data['error_warehouse'] = '';
        }

        if (isset($this->request->post['selected'])) {
            foreach ($this->request->post['selected'] as $value) {
                $data['quantity'][$value] = $this->request->post[$value];
            }
        } else {
            $data['quantity'] = array();
        }

        if (isset($this->request->post['warehouse']) && $this->request->post['warehouse']) {
            $data['selected_warehouse'] = $this->request->post['warehouse'];
        } elseif (isset($this->request->get['id']) && $this->request->get['id']) {
            $data['selected_warehouse'] = $this->request->get['id'];
        } else {
            $data['selected_warehouse'] = '';
        }

        $this->template = 'aliexpress/warehouses/assign.expand';

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->data = $data;

        $this->response->setOutput($this->render_ecwig());
    }

    protected function validateForm()
    {
        if (!$this->user->hasPermission('modify', 'aliexpress/assign_warehouse')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        if (!isset($this->request->post['warehouse']) || (0 == $this->request->post['warehouse'])) {
            $this->error['warehouse'] = $this->language->get('error_warehouse');
        }
        if (!isset($this->request->post['selected']) && $this->request->post['warehouse']) {
            $this->error['selected'] = $this->language->get('error_selected');
        }
        if (isset($this->request->post['selected']) && $this->request->post['warehouse']) {
            if (isset($this->request->post['selected'])) {
                foreach ($this->request->post['selected'] as $value) {
                    if ('' == $this->request->post[$value]) {
                        $this->error['quantity'][$value] = $this->language->get('error_blank');
                    } elseif (!is_numeric($this->request->post[$value])) {
                        $this->error['quantity'][$value] = $this->language->get('error_not_number');
                    } elseif (0 == $this->request->post[$value]) {
                        $this->error['quantity'][$value] = $this->language->get('error_not_zero');
                    }
                }
            }
        }
        if ($this->error) {
            return false;
        } else {
            return true;
        }
    }
}
