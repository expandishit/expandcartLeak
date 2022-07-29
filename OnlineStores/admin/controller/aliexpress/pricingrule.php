<?php

class ControllerAliexpressPricingRule extends ControllerAliexpressBase
{
    public function index()
    {
        $this->load->language('aliexpress/pricingrule');

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
            'href' => $this->url->link('aliexpress/pricingrule', '', true),
        );

        $this->load->model('catalog/category');

        $results = $this->model_catalog_category->getCategories();

        $categories = array();

        foreach ($results as $result) {
            $categories[$result['category_id']] = array(
                'category_id' => $result['category_id'],
                'name' => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
            );
        }

        $sort_order = array();
        foreach ($categories as $key => $value) {
            $sort_order[$key] = $value['name'];
        }

        array_multisort($sort_order, SORT_ASC, $categories);
        $data['categories'] = $categories;

        $filterData = array(
            'start' => ($page - 1) * $this->config->get('config_admin_limit'),
            'limit' => $this->config->get('config_admin_limit'),
        );

        $this->initializer(['aliexpress/warehouses']);
        $priceRules = $this->warehouses->getPriceRules($filterData);
        $priceRulesTotal = $this->warehouses->getPriceRulesTotal($filterData);

        $data['priceRules'] = array();

        if ($priceRules) {
            foreach ($priceRules as $key => $priceRule) {
                if ('p' == $priceRule['method_type']) {
                    $amount = $priceRule['amount'].'%';
                } else {
                    $amount = $this->currency->format($priceRule['amount'], $this->config->get('config_currency'));
                }
                $data['priceRules'][] = array(
                    'rule_id' => $priceRule['rule_id'],
                    'name' => $priceRule['name'],
                    'channel' => $priceRule['channel'],
                    // 'category_id' => $priceRule['category_id'],
                    // 'category' => $priceRule['category_name'],
                    'price_from' => $this->currency->format($priceRule['price_from'], $this->config->get('config_currency')),
                    'price_to' => $this->currency->format($priceRule['price_to'], $this->config->get('config_currency')),
                    'method_type' => $priceRule['method_type'],
                    'amount' => $amount,
                );
            }
        }

        $data['channelType'] = array(
            'm' => $this->language->get('text_manual'),
            // 'mu' => $this->language->get('text_mass_upload'),
            'ali' => $this->language->get('text_aliexpress'),
        );

        $data['methodType'] = array(
            'f' => $this->language->get('text_fixed'),
            'p' => $this->language->get('text_per'),
        );

        if (isset($this->session->data['success']) && $this->session->data['success']) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        if (isset($this->session->data['error_warning']) && $this->session->data['error_warning']) {
            $data['error_warning'] = $this->session->data['error_warning'];
            unset($this->session->data['error_warning']);
        } else {
            $data['error_warning'] = '';
        }

        $pagination = new Pagination();
        $pagination->total = $priceRulesTotal;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_admin_limit');
        $pagination->url = $this->url->link('aliexpress/pricingrule', 'page={page}', 'SSL');

        $data['pagination'] = $pagination->render();

        $this->template = 'aliexpress/pricingrule.expand';

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->data = $data;

        $this->response->setOutput($this->render_ecwig());
    }

    public function addPriceRule()
    {
        $json = array();
        if ('POST' == $this->request->server['REQUEST_METHOD'] && $this->request->post) {
            $this->load->language('aliexpress/pricingrule');
            $this->initializer(['aliexpress/warehouses']);

            $priceRule = [];
            if (isset($this->request->post['priceRule'])) {
                $priceRule = $this->request->post['priceRule'];
            }

            if (
                isset($priceRule['pricerule_name']) && (
                    strlen(trim($priceRule['pricerule_name'])) <= 3 ||
                    strlen(trim($priceRule['pricerule_name'])) >= 100
                )
            ) {
                $json['error'][] = $this->language->get('error_warning_rule_name');
            }

            // if(isset($priceRule['pricerule_category']) && !$priceRule['pricerule_category']) {
            //     $json['error'][] = $this->language->get('error_warning_rule_category');
            // }

            if (!isset($priceRule['pricerule_price_range_from'])) {
                $json['error'][] = $this->language->get('error_warning_price_rule_from');
            } elseif (
                isset($priceRule['pricerule_price_range_from']) &&
                strlen($priceRule['pricerule_price_range_from']) > 10) {
                $json['error'][] = sprintf($this->language->get('error_warning_length'), 'Price range from');
            }

            if (!isset($priceRule['pricerule_price_range_to'])) {
                $json['error'][] = $this->language->get('error_warning_price_rule_to');
            } elseif (
                isset($priceRule['pricerule_price_range_to']) &&
                strlen($priceRule['pricerule_price_range_to']) > 10) {
                $json['error'][] = sprintf($this->language->get('error_warning_length'), 'Price range to');
            }

            if (
                isset($priceRule['pricerule_price_range_from']) &&
                isset($priceRule['pricerule_price_range_to']) &&
                $priceRule['pricerule_price_range_from'] >= $priceRule['pricerule_price_range_to']) {
                $json['error'][] = $this->language->get('error_warning_price_rule_range');
            }

            if (!isset($priceRule['pricerule_amount'])) {
                $json['error'][] = $this->language->get('error_warning_amount');
            } elseif (isset($priceRule['pricerule_amount']) && strlen($priceRule['pricerule_amount']) > 10) {
                $json['error'][] = sprintf($this->language->get('error_warning_length'), 'Amount');
            }

            if (!isset($json['error']) && empty($json['error'])) {
                if (isset($priceRule['pricerule_rule_id']) && $priceRule['pricerule_rule_id']) {
                    $rule_ranges = $this->warehouses->getExcludedPriceRulesRanges($priceRule['pricerule_rule_id']);
                } else {
                    $rule_ranges = $this->warehouses->getPriceRulesRanges();
                }

                foreach ($rule_ranges as $key => $rule_range) {
                    if (isset($rule_range['min']) && isset($rule_range['max'])) {
                        if (isset($priceRule['pricerule_price_range_from']) && $priceRule['pricerule_price_range_to']) {
                            $err = $this->_validateRuleRange(
                                'price_from',
                                $priceRule['pricerule_price_range_from'],
                                $rule_range['min'],
                                $rule_range['max']
                            );

                            if ($err) {
                                $json['error'][] = $err;
                            }
                        }
                        if (isset($priceRule['pricerule_price_range_to']) && $priceRule['pricerule_price_range_to']) {
                            $err = $this->_validateRuleRange(
                                'price_to',
                                $priceRule['pricerule_price_range_to'],
                                $rule_range['min'],
                                $rule_range['max']
                            );

                            if ($err) {
                                $json['error'][] = $err;
                            }
                        }
                        if (
                            isset($priceRule['pricerule_price_range_to']) &&
                            $priceRule['pricerule_price_range_to'] &&
                            isset($priceRule['pricerule_price_range_from']) &&
                            $priceRule['pricerule_price_range_to']
                        ) {
                            $err = $this->_validateRuleRange(
                                'wide_range',
                                $rule_range['min'],
                                $priceRule['pricerule_price_range_from'],
                                $priceRule['pricerule_price_range_to']
                            );

                            if ($err) {
                                $json['error'][] = $err;
                            }

                            $err = $this->_validateRuleRange(
                                'wide_range',
                                $rule_range['max'],
                                $priceRule['pricerule_price_range_from'],
                                $priceRule['pricerule_price_range_to']
                            );

                            if ($err) {
                                $json['error'][] = $err;
                            }
                        }
                    }
                }
            }

            if (!$json) {
                if (isset($priceRule['pricerule_rule_id']) && $priceRule['pricerule_rule_id']) {
                    $this->warehouses->editPriceRule($priceRule);
                    $json['success'] = $this->session->data['success'] = $this->language->get('success_pricerule_updated');
                } else {
                    $this->warehouses->addPriceRule($priceRule);
                    $json['success'] = $this->session->data['success'] = $this->language->get('success_pricerule_added');
                }
            }
        }

        $this->response->setOutput(json_encode($json));
    }

    public function _validateRuleRange($for, $price, $min, $max)
    {
        if (filter_var($price, FILTER_VALIDATE_INT, array('options' => array('min_range' => $min, 'max_range' => $max)))) {
            return $this->language->get('error_range_'.$for);
        }
    }

    public function getRule()
    {
        $json = array();
        if ('POST' == $this->request->server['REQUEST_METHOD'] && $this->request->post) {
            if (isset($this->request->post['rule_id']) && $this->request->post['rule_id']) {
                $this->initializer(['aliexpress/warehouses']);
                $ruleDetails = $this->warehouses->getRule($this->request->post['rule_id']);
                $json['data'] = $ruleDetails;
            }
        }
        $this->response->setOutput(json_encode($json));
    }

    public function delete()
    {
        $json = array();
        $this->load->language('aliexpress/pricingrule');
        if ('POST' == $this->request->server['REQUEST_METHOD']) {
            $this->initializer(['aliexpress/warehouses']);
            if (isset($this->request->post['selected']) && $this->request->post['selected']) {
                foreach ($this->request->post['selected'] as $key => $value) {
                    $this->warehouses->deletePriceRule($value);
                }
                $this->session->data['success'] = $this->language->get('success_deleted');
            } else {
                $this->session->data['error_warning'] = $this->language->get('error_warning_no_selection');
            }
        }
        $this->response->redirect($this->url->link('aliexpress/pricingrule', '', true));
    }
}
