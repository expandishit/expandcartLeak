<?php

class ControllerAliexpressManagerShipping extends ControllerAliexpressBase
{
    private $error = array();

    public function index()
    {
        $this->language->load('aliexpress/shipping');
        $this->document->setTitle($this->language->get('heading_title'));
        // $this->load->model('aliexpress/shipping');
        $this->initializer(['aliexpress/shipping']);

        /*
         * get warehouse id
         */
        $this->shipping->getWarehouseId($this->session->data['user_id']);
        $this->getList();
    }

    protected function getList()
    {
        $filter_array = array(
            'filter_country',
            'filter_zip_to',
            'filter_zip_from',
            'filter_price',
            'filter_weight_to',
            'filter_weight_from',
            'page',
            'sort',
            'order',
            'start',
            'limit',
        );

        $url = '';

        foreach ($filter_array as $unsetKey => $key) {
            if (isset($this->request->get[$key])) {
                $filter_array[$key] = $this->request->get[$key];
            } else {
                if ('page' == $key) {
                    $filter_array[$key] = 1;
                } elseif ('sort' == $key) {
                    $filter_array[$key] = 'cs.id';
                } elseif ('order' == $key) {
                    $filter_array[$key] = 'ASC';
                } elseif ('start' == $key) {
                    $filter_array[$key] = ($filter_array['page'] - 1) * $this->config->get('config_product_limit');
                } elseif ('limit' == $key) {
                    $filter_array[$key] = $this->config->get('config_product_limit');
                } else {
                    $filter_array[$key] = null;
                }
            }
            unset($filter_array[$unsetKey]);

            if (isset($this->request->get[$key])) {
                if ('filter_country' == $key) {
                    $url .= '&'.$key.'='.urlencode(html_entity_decode($filter_array[$key], ENT_QUOTES, 'UTF-8'));
                } else {
                    $url .= '&'.$key.'='.$filter_array[$key];
                }
            }
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
            'separator' => false,
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('aliexpress/shipping', '', 'SSL'),
            'separator' => ' :: ',
        );

        $results = $this->shipping->viewdata($filter_array);

        $product_total = $this->shipping->viewtotalentry($filter_array);

        //user
        $data['result_shipping'] = array();

        if ($results) {
            foreach ($results as $result) {
                $data['result_shipping'][] = array(
                    'selected' => false,
                    'id' => $result['id'],
                    'price' => $result['price'],
                    'country' => $result['country_code'],
                    'zip_to' => $result['zip_to'],
                    'zip_from' => $result['zip_from'],
                    'weight_from' => $result['weight_from'],
                    'weight_to' => $result['weight_to'],
                );
            }
        }

        $pagination = new Pagination();
        $pagination->total = $product_total;
        $pagination->page = $filter_array['page'];
        $pagination->limit = $this->config->get('config_admin_limit');
        $pagination->text = $this->language->get('text_pagination');
        $pagination->url = $this->url->link('aliexpress/shipping', 'page={page}', 'SSL');

        $data['pagination'] = $pagination->render();

        foreach ($filter_array as $key => $value) {
            if ('start' != $key and 'end' != $key) {
                $data[$key] = $value;
            }
        }

        $this->template = 'aliexpress/shipping/list.expand';

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->data = $data;

        $this->response->setOutput($this->render_ecwig());
    }

    public function delete()
    {
        $this->language->load('aliexpress/shipping');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->initializer(['aliexpress/shipping']);

        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            foreach ($this->request->post['selected'] as $id) {
                /*
                 * delete warehouse entry
                 */
                $this->shipping->deleteentry($id);
            }

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['page'])) {
                $url .= '&page='.$this->request->get['page'];
            }

            $this->response->redirect($this->url->link('aliexpress/shipping', '', 'SSL'));
        }

        $this->getList();
    }

    private function validateForm()
    {
        if (!$this->user->hasPermission('modify', 'aliexpress/shipping')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if ($this->error && !isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    protected function validateDelete()
    {
        if (!$this->user->hasPermission('modify', 'aliexpress/shipping')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    public function addshipping()
    {
        $this->load->language('aliexpress/shipping');

        $this->document->setTitle($this->language->get('heading_title_add'));

        $this->load->model('setting/setting');

        $this->initializer(['aliexpress/shipping']);

        $this->shipping->getWarehouseId($this->session->data['user_id']);

        if (!isset($this->session->data['csv_file_shipping'])) {
            $this->session->data['csv_file_shipping'] = array();
        }

        if (('POST' == $this->request->server['REQUEST_METHOD']) && $this->validate()) {
            $files = $this->request->files;

            if (isset($files['up_file']['tmp_name']) and $files['up_file']['tmp_name']) {
                // csv check
                $csv_extention = explode('.', $files['up_file']['name']);

                if (isset($csv_extention[1]) and 'csv' == $csv_extention[1]) {
                    $this->session->data['csv_post_shipping'] = $this->request->post;
                    if ($file = fopen($files['up_file']['tmp_name'], 'r')) {
                        // necessary if a large csv file
                        set_time_limit(0);
                        $separator = 'webkul';
                        if (isset($this->request->post['separator'])) {
                            $separator = $this->request->post['separator'];
                        }

                        if (strlen($separator) > 1) {
                            $this->error['warning'] = $this->language->get('entry_error_separator');
                        } else {
                            // remove chracters from separator
                            $separator = preg_replace('/[a-z A-Z .]+/', ' ', $separator);
                            if (strlen($separator) < 1 || ' ' == $separator) {
                                $separator = ';';
                            }

                            $this->session->data['csv_file_shipping'] = array();
                            while (false !== ($line = fgetcsv($file, 4096, $separator))) {
                                $this->session->data['csv_file_shipping'][] = $line;
                            }
                        }
                    }
                    $this->response->redirect($this->url->link('aliexpress/shipping/matchdata', '', 'SSL'));
                } else {
                    $this->error['warning'] = $this->language->get('entry_error_csv');
                }
            } else {
                $this->error['warning'] = $this->language->get('entry_error_csv');
            }
        }

        if (isset($this->session->data['error_warning'])) {
            $this->error['warning'] = $this->session->data['error_warning'];
            unset($this->session->data['error_warning']);
        }

        if (isset($this->session->data['attention'])) {
            $data['attention'] = $this->session->data['attention'];
            unset($this->session->data['attention']);
        } else {
            $data['attention'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
            'separator' => false,
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('wk_shipping_modadmin'),
            'href' => $this->url->link('aliexpress/shipping', '', 'SSL'),
            'separator' => ' :: ',
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('aliexpress/shipping/addshipping', '', 'SSL'),
            'separator' => ' :: ',
        );

        $this->template = 'aliexpress/shipping/add_step_1.expand';

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->data = $data;

        $this->response->setOutput($this->render_ecwig());
    }

    public function matchdata()
    {
        $this->load->language('aliexpress/shipping');

        if (isset($this->session->data['csv_post_shipping']) and isset($this->session->data['csv_file_shipping'])) {
            $post = $this->session->data['csv_post_shipping'];
            $files = $this->session->data['csv_file_shipping'];
            $fields = false;
            if (isset($files[0])) {
                $fields = $files[0];
            }

            $num = count($fields);
            //separator check
            if ($num < 2) {
                $this->error['warning'] = $this->language->get('entry_error_separator');
                $this->addshipping();
            } else {
                $this->stepTwo($fields);
            }
        } else {
            $this->error['warning'] = $this->language->get('error_somithing_wrong');
            $this->addshipping();
        }
    }

    public function stepTwo($fields = array())
    {
        $this->load->language('aliexpress/shipping');

        $this->document->setTitle($this->language->get('heading_title'));

        if (('POST' == $this->request->server['REQUEST_METHOD']) && $fields == array()) {
            //insert shipping
            foreach ($this->request->post as $chkpost) {
                if ('' == $chkpost) {
                    $this->error['warning'] = $this->language->get('error_fileds');
                    break;
                }
            }

            if (isset($this->error['warning']) and $this->error['warning']) {
                $fields = $this->session->data['csv_file_shipping'][0];
            } else {
                $message = $this->matchDataTwo();
                if ($message['success']) {
                    $this->session->data['success'] = $this->language->get('text_shipping').$message['success'];
                }
                if ($message['warning']) {
                    $this->session->data['error_warning'] = $this->language->get('fields_error').$message['warning'];
                }
                if ($message['update']) {
                    $this->session->data['attention'] = $this->language->get('text_attention').$message['update'];
                }

                unset($this->session->data['csv_file_shipping']);
                unset($this->session->data['csv_post_shipping']);

                $this->response->redirect($this->url->link('aliexpress/shipping/addshipping', '', 'SSL'));
            }
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->session->data['attention'])) {
            $data['attention'] = $this->session->data['attention'];
            unset($this->session->data['attention']);
        } else {
            $data['attention'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
            'separator' => false,
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('aliexpress/shipping/addshipping', '', 'SSL'),
            'separator' => ' :: ',
        );

        // send fields data
        $data['fields'] = $fields;

        // shipping data
        $data['shippingTable'] = array('country_code', 'zip_to', 'zip_from', 'price', 'weight_to', 'weight_from');

        $this->template = 'aliexpress/shipping/add_step_2.expand';

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->data = $data;

        $this->response->setOutput($this->render_ecwig());
    }

    private function matchDataTwo()
    {
        $this->initializer(['aliexpress/shipping']);
        $this->load->language('aliexpress/shipping');

        if (!isset($this->session->data['csv_file_shipping'])) {
            $this->response->redirect($this->url->link('aliexpress/shipping/addshipping', '', 'SSL'));
        }

        $files = $this->session->data['csv_file_shipping'];
        $post = $this->request->post;

        $fields = $files[0];
        $files = array_slice($files, 1);

        $shippingDatas = array();
        $i = 0;
        $num = count($files);

        foreach ($files as $line) {
            $entry = true;

            foreach ($post as $postchk) {
                if (!isset($line[$postchk]) || '' == trim($line[$postchk])) {
                    $entry = false;
                    break;
                }
            }

            if ($entry) {
                $shippingDatas[$i] = array();
                foreach ($post as $key => $postchk) {
                    $shippingDatas[$i][$key] = $line[$postchk];
                }
                ++$i;
            }
        }
        $updatechk = 0;
        foreach ($shippingDatas as $newShipping) {
            $result = $this->shipping->addShipping($newShipping);
            if ($result) {
                $updatechk++;
            }
        }

        return array('success' => $i - $updatechk,
                     'warning' => $num - $i,
                     'update' => $updatechk,
                    );
    }

    private function validate()
    {
        if (!$this->user->hasPermission('modify', 'aliexpress/shipping/addshipping')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }
}
