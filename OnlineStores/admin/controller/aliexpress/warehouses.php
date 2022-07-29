<?php

class ControllerAliexpressWarehouses extends ControllerAliexpressBase
{
    public function index()
    {
        $this->load->language('aliexpress/warehouses');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

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
            'href' => $this->url->link('aliexpress/warehouses', '', true),
        );

        $url = '';

        $data['warehouses'] = array();
        $filter_data['start'] = ($page - 1) * $this->config->get('config_admin_limit');
        $filter_data['limit'] = $this->config->get('config_admin_limit');
        //error checking

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

        if (isset($this->session->data['warning']) && '' == $data['error_warning']) {
            $data['error_warning'] = $this->session->data['warning'];

            unset($this->session->data['warning']);
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->request->post['selected'])) {
            $data['selected'] = (array) $this->request->post['selected'];
        } else {
            $data['selected'] = array();
        }

        $warehouse_total = $this->warehouses->getTotalWarehouses($filter_data);

        $results = $this->warehouses->getWarehouses($filter_data);

        $this->load->model('localisation/country');

        $data['countries'] = $this->model_localisation_country->getCountries();

        if ($results) {
            foreach ($results as $result) {
                $data['warehouses'][] = array(
                    'warehouse_id' => $result['warehouse_id'],
                    'title' => $result['title'],
                    'contactname' => $result['firstname'] . ' ' . $result['lastname'],
                    'user' => $result['username'],
                    'description' => $result['description'],
                    'originstreet' => $result['street'],
                    'origincountry' => $result['country_id'],
                    'postalcode' => $result['postal_code'],
                    'country' => $result['name'],
                    'edit' => $this->url->link(
                        'aliexpress/warehouses/edit',
                        'warehouse_id=' . $result['warehouse_id'],
                        true
                    ),
                );
            }
        }

        $pagination = new Pagination();
        $pagination->total = $warehouse_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_admin_limit');
        $pagination->url = $this->url->link('aliexpress/warehouses', '&page={page}', true);

        $data['pagination'] = $pagination->render();

        $this->template = 'aliexpress/warehouses/list.expand';

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->data = $data;

        $this->response->setOutput($this->render_ecwig());
    }

    public function delete()
    {
        $this->load->language('aliexpress/warehouses');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->initializer(['aliexpress/warehouses']);

        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            foreach ($this->request->post['selected'] as $warehouse_id) {
                $this->warehouses->deleteWarehousecompletely($warehouse_id);
            }
            $this->session->data['success'] = $this->language->get('text_success');
        } else {
            $this->session->data['warning'] = $this->language->get('error_empty');
        }
        $this->response->redirect($this->url->link('aliexpress/warehouses', '', true));
    }

    public function add()
    {
        $this->load->language('aliexpress/warehouses');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->initializer(['aliexpress/warehouses']);

        if (('POST' == $this->request->server['REQUEST_METHOD']) && $this->validateForm()) {
            if (isset($this->request->post['originstreet']) && $this->request->post['originstreet']) {
                $address = $this->request->post['originstreet'].','.$this->request->post['origincity'].','.$this->request->post['zoneName'].','.$this->request->post['countryName'];
            } else {
                $address = $this->request->post['origincity'].','.$this->request->post['zoneName'].','.$this->request->post['countryName'];
            }

            $url = 'https://maps.googleapis.com/maps/api/geocode/json?key='.trim($this->config->get('wk_dropship_google_api_key')).'&address='.urlencode($address).'&sensor=false';
            $res = file_get_contents($url);
            $decode = json_decode($res, true);
            $this->request->post['longitude'] = '';
            $this->request->post['latitude'] = '';
            if ('OK' == $decode['status']) {
                if (isset($decode['results'][0]['geometry']['location']['lng'])) {
                    $this->request->post['longitude'] = $decode['results'][0]['geometry']['location']['lng'];
                    $this->request->post['latitude'] = $decode['results'][0]['geometry']['location']['lat'];
                }
            }

            $this->warehouses->addWarehouse($this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('aliexpress/warehouses', '', true));
        }

        $this->getForm();
    }

    public function edit()
    {
        $this->load->language('aliexpress/warehouses');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->initializer(['aliexpress/warehouses']);

        if (!isset($this->request->get['warehouse_id']) || '' == trim($this->request->get['warehouse_id'])) {
            $this->response->redirect($this->url->link('aliexpress/warehouses', '', true));
        } else {
            $warhouse_details = $this->warehouses->checkWarehouse($this->request->get['warehouse_id']);

            if (empty($warhouse_details)) {
                $this->response->redirect($this->url->link('aliexpress/warehouses', '', true));
            }
        }

        if (('POST' == $this->request->server['REQUEST_METHOD']) && $this->validateForm()) {
            if (isset($this->request->post['originstreet']) && $this->request->post['originstreet']) {
                $address = $this->request->post['originstreet'].','.$this->request->post['origincity'].','.$this->request->post['zoneName'].','.$this->request->post['countryName'];
            } else {
                $address = $this->request->post['origincity'].','.$this->request->post['zoneName'].','.$this->request->post['countryName'];
            }

            $url = 'https://maps.googleapis.com/maps/api/geocode/json?key='.trim($this->config->get('wk_dropship_google_api_key')).'&address='.urlencode($address).'&sensor=false';
            $res = file_get_contents($url);
            $decode = json_decode($res, true);
            $this->request->post['longitude'] = '';
            $this->request->post['latitude'] = '';
            if ('OK' == $decode['status']) {
                if (isset($decode['results'][0]['geometry']['location']['lng'])) {
                    $this->request->post['longitude'] = $decode['results'][0]['geometry']['location']['lng'];
                    $this->request->post['latitude'] = $decode['results'][0]['geometry']['location']['lat'];
                }
            }

            $this->warehouses->editWarehouse($this->request->get['warehouse_id'], $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('aliexpress/warehouses', '', true));
        }

        $this->getForm();
    }

    protected function getForm()
    {
        $data['totalshipping_methods'] = array();

        $this->initializer(['aliexpress/warehouses']);

        $data['user_group_id'] = $this->config->get('wk_dropship_user_group');

        if (!empty($data['user_group_id'])) {
            $data['user'] = $this->warehouses->getUser($data['user_group_id']);
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', '', true),
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('aliexpress/warehouses', '', true),
        );

        if (!isset($this->request->get['warehouse_id'])) {
            $data['action'] = $this->url->link('aliexpress/warehouses/add', '', true);
        } else {
            $data['action'] = $this->url->link(
                'aliexpress/warehouses/edit',
                'warehouse_id='.$this->request->get['warehouse_id'],
                true
            );
        }

        $this->load->model('localisation/country');
        $data['countries'] = $this->model_localisation_country->getCountries();

        $result = $this->warehouses->getExtensions('shipping');
        foreach ($result as $value) {
            if ($this->config->get($value['code'].'_status')) {
                $data['totalshipping_methods'][] = $value['code'];
            }
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['warehousecode'])) {
            $data['error_warehousecode'] = $this->error['warehousecode'];
        } else {
            $data['error_warehousecode'] = '';
        }

        if (isset($this->error['title'])) {
            $data['error_title'] = $this->error['title'];
        } else {
            $data['error_title'] = '';
        }

        if (isset($this->error['description'])) {
            $data['error_description'] = $this->error['description'];
        } else {
            $data['error_description'] = '';
        }

        if (isset($this->error['user'])) {
            $data['error_user'] = $this->error['user'];
        } else {
            $data['error_user'] = '';
        }

        if (isset($this->error['country_id'])) {
            $data['error_country_id'] = $this->error['country_id'];
        } else {
            $data['error_country_id'] = '';
        }

        if (isset($this->error['zone_id'])) {
            $data['error_zone_id'] = $this->error['zone_id'];
        } else {
            $data['error_zone_id'] = '';
        }

        if (isset($this->error['origincity'])) {
            $data['error_origincity'] = $this->error['origincity'];
        } else {
            $data['error_origincity'] = '';
        }

        if (isset($this->error['postalcode'])) {
            $data['error_postalcode'] = $this->error['postalcode'];
        } else {
            $data['error_postalcode'] = '';
        }

        if (isset($this->request->get['warehouse_id']) && ('POST' != $this->request->server['REQUEST_METHOD'])) {
            $warehouse_info = $this->warehouses->getWarehouse($this->request->get['warehouse_id']);
        }

        if (isset($this->request->post['warehousecode'])) {
            $data['warehousecode'] = $this->request->post['warehousecode'];
        } elseif (!empty($warehouse_info)) {
            $data['warehousecode'] = $warehouse_info['warehouse_code'];
        } else {
            $data['warehousecode'] = '';
        }

        if (isset($this->request->post['title'])) {
            $data['title'] = $this->request->post['title'];
        } elseif (!empty($warehouse_info)) {
            $data['title'] = $warehouse_info['title'];
        } else {
            $data['title'] = '';
        }

        if (isset($this->request->post['description'])) {
            $data['description'] = $this->request->post['description'];
        } elseif (!empty($warehouse_info)) {
            $data['description'] = $warehouse_info['description'];
        } else {
            $data['description'] = '';
        }

        if (isset($this->request->post['origincity'])) {
            $data['origincity'] = $this->request->post['origincity'];
        } elseif (!empty($warehouse_info)) {
            $data['origincity'] = $warehouse_info['city'];
        } else {
            $data['origincity'] = '';
        }

        if (isset($this->request->post['originstreet'])) {
            $data['originstreet'] = $this->request->post['originstreet'];
        } elseif (!empty($warehouse_info)) {
            $data['originstreet'] = $warehouse_info['street'];
        } else {
            $data['originstreet'] = '';
        }

        if (isset($this->request->post['postalcode'])) {
            $data['postalcode'] = $this->request->post['postalcode'];
        } elseif (!empty($warehouse_info)) {
            $data['postalcode'] = $warehouse_info['postal_code'];
        } else {
            $data['postalcode'] = '';
        }

        if (isset($this->request->post['country_id'])) {
            $data['country_id'] = $this->request->post['country_id'];
        } elseif (!empty($warehouse_info)) {
            $data['country_id'] = $warehouse_info['country_id'];
        } else {
            $data['country_id'] = '';
        }

        if (isset($this->request->post['zone_id'])) {
            $data['zone_id'] = $this->request->post['zone_id'];
        } elseif (!empty($warehouse_info)) {
            $this->load->model('localisation/zone');
            $data['zone_datas'] = $this->model_localisation_zone->getZonesByCountryId($data['country_id']);
            $data['zone_id'] = $warehouse_info['zone_id'];
        } else {
            $data['zone_id'] = '';
        }

        if (isset($this->request->post['longitude'])) {
            $data['longitude'] = $this->request->post['longitude'];
        } elseif (!empty($warehouse_info)) {
            $data['longitude'] = $warehouse_info['longitude'];
        } else {
            $data['longitude'] = '';
        }

        if (isset($this->request->post['latitude'])) {
            $data['latitude'] = $this->request->post['latitude'];
        } elseif (!empty($warehouse_info)) {
            $data['latitude'] = $warehouse_info['latitude'];
        } else {
            $data['latitude'] = '';
        }

        if (isset($this->request->post['user']) && $this->request->post['user']) {
            $data['selected_user'] = $this->request->post['user'];
        } elseif (!empty($warehouse_info['user_id'])) {
            $data['selected_user'] = $warehouse_info['user_id'];
        }

        if (isset($this->request->post['shippingmethods'])) {
            $data['shippingmethods'] = $this->request->post['shippingmethods'];
        } elseif (!empty($warehouse_info) && isset($warehouse_info['shipping_methods'])) {
            foreach ($warehouse_info['shipping_methods'] as $key => $shipping_method) {
                $data['shippingmethods'][] = $shipping_method['code'];
            }
        } else {
            $data['shippingmethods'] = array();
        }

        if (isset($this->request->post['status'])) {
            $data['status'] = $this->request->post['status'];
        } elseif (!empty($warehouse_info)) {
            $data['status'] = $warehouse_info['status'];
        } else {
            $data['status'] = '';
        }

        $this->template = 'aliexpress/warehouses/form.expand';

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->data = $data;

        $this->response->setOutput($this->render_ecwig());
    }

    protected function validateForm()
    {
        if (!$this->user->hasPermission('modify', 'aliexpress/warehouses')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        $this->initializer(['aliexpress/warehouses']);

        if ((utf8_strlen(trim($this->request->post['warehousecode'])) < 1) || (utf8_strlen(trim($this->request->post['warehousecode'])) > 32)) {
            $this->error['warehousecode'] = $this->language->get('error_warehousecode');
        } elseif (isset($this->request->post['warehousecode'])) {
            if (isset($this->request->get['warehouse_id'])) {
                $query = $this->warehouses->checkWarehouseCode(
                    $this->request->post['warehousecode'],
                    $this->request->get['warehouse_id']
                );
            } else {
                $query = $this->warehouses->checkWarehouseCode($this->request->post['warehousecode']);
            }

            if (!empty($query)) {
                $this->error['warehousecode'] = $this->language->get('error_exists_warehousecode');
            }
        }

        if (
            (utf8_strlen(trim($this->request->post['title'])) < 1) ||
            (utf8_strlen(trim($this->request->post['title'])) > 32)
        ) {
            $this->error['title'] = $this->language->get('error_title');
        } elseif (isset($this->request->post['title'])) {
            if (isset($this->request->get['warehouse_id'])) {
                $query = $this->warehouses->checkWarehouseTitle(
                    $this->request->post['title'],
                    $this->request->get['warehouse_id']
                );
            } else {
                $query = $this->warehouses->checkWarehouseTitle($this->request->post['title']);
            }

            if (!empty($query)) {
                $this->error['title'] = $this->language->get('error_exists_warehousetitle');
            }
        }

        if (
            (utf8_strlen(trim($this->request->post['description'])) < 1) ||
            (utf8_strlen(trim($this->request->post['description'])) > 500)
        ) {
            $this->error['description'] = $this->language->get('error_description');
        }

        if (!isset($this->request->post['user']) || (0 == $this->request->post['user'])) {
            $this->error['user'] = $this->language->get('error_user');
        }

        if (
            (utf8_strlen(trim($this->request->post['origincity'])) < 1) ||
            (utf8_strlen(trim($this->request->post['origincity'])) > 500)
        ) {
            $this->error['origincity'] = $this->language->get('error_origincity');
        }

        if (!isset($this->request->post['postalcode']) || (strlen($this->request->post['postalcode']) < 1)) {
            $this->error['postalcode'] = $this->language->get('error_postalcode');
        } elseif (isset($this->request->post['postalcode'])) {
            if (isset($this->request->get['warehouse_id'])) {
                $query = $this->warehouses->checkWarehousePostcode(
                    $this->request->post['postalcode'],
                    $this->request->get['warehouse_id']
                );
            } else {
                $query = $this->warehouses->checkWarehousePostcode($this->request->post['postalcode']);
            }

            if (!empty($query)) {
                $this->error['postalcode'] = $this->language->get('error_postalcode_exist');
            }
        }

        if (!isset($this->request->post['country_id']) || ('' == $this->request->post['country_id'])) {
            $this->error['country_id'] = $this->language->get('error_origincountry');
        }

        if (!isset($this->request->post['zone_id']) || (0 == $this->request->post['zone_id'])) {
            $this->error['zone_id'] = $this->language->get('error_originstate');
        }

        return !$this->error;
    }

    protected function validateDelete()
    {
        if (!$this->user->hasPermission('modify', 'aliexpress/warehouses')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }
}
