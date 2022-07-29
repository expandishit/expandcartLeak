<?php

class ControllerSaleOrderApi extends Controller
{
    public function products()
    {
        $customerGroup = null;

        if (isset($this->request->get['customer_group_id'])) {
            if (preg_match('#^[0-9]+$#', $this->request->get['customer_group_id'])) {
                $customerGroup = $this->request->get['customer_group_id'];
            }
        }

        $json = array();

        if (
            isset($this->request->get['filter_name']) ||
            isset($this->request->get['filter_model']) ||
            isset($this->request->get['filter_category_id'])
        ) {
            $this->load->model('catalog/product');
            $this->load->model('catalog/option');

            if (isset($this->request->get['filter_name'])) {
                $filter_name = $this->request->get['filter_name'];
            } else {
                $filter_name = '';
            }

            if (isset($this->request->get['filter_model'])) {
                $filter_model = $this->request->get['filter_model'];
            } else {
                $filter_model = '';
            }

            if (isset($this->request->get['limit'])) {
                $limit = $this->request->get['limit'];
            } else {
                $limit = 20;
            }

            $data = array(
                'filter_name' => $filter_name,
                'filter_model' => $filter_model,
                'start' => 0,
                'limit' => $limit
            );

            if (isset($this->request->get['filter_status'])) {
                $data['filter_status'] = $this->request->get['filter_status'];
            }

            $results = $this->model_catalog_product->getProducts($data);

            foreach ($results as $result) {
                $option_data = array();

                $product_options = $this->model_catalog_product->getProductOptions($result['product_id']);

                // Get Product special prices                 
                // $product_specials = $this->model_catalog_product->getProductSpecials($result['product_id']);
                $product_specials = $this->model_catalog_product->getProductDiscountsAndSpecials([
                    'product_id' => $result['product_id'],
                    'customer_group_id' => $customerGroup,
                ]);

                foreach ($product_options as $product_option) {
                    $option_info = $this->model_catalog_option->getOption($product_option['option_id']);

                    if ($option_info) {
                        if (
                            $option_info['type'] == 'select' ||
                            $option_info['type'] == 'radio' ||
                            $option_info['type'] == 'checkbox' ||
                            $option_info['type'] == 'image'
                        ) {
                            $option_value_data = array();

                            foreach ($product_option['product_option_value'] as $product_option_value) {
                                $option_value_info = $this->model_catalog_option->getOptionValue(
                                    $product_option_value['option_value_id']
                                );

                                if ($option_value_info) {

                                    $price = (
                                    (float)$product_option_value['price'] ?
                                        $this->currency->format(
                                            $product_option_value['price'],
                                            $this->config->get('config_currency')
                                        ) :
                                        false
                                    );

                                    $option_value_data[] = array(
                                        'product_option_value_id' => $product_option_value['product_option_value_id'],
                                        'option_value_id' => $product_option_value['option_value_id'],
                                        'name' => $option_value_info['name'],
                                        'price' => $price,
                                        'price_prefix' => $product_option_value['price_prefix']
                                    );
                                }
                            }

                            $option_data[] = array(
                                'product_option_id' => $product_option['product_option_id'],
                                'option_id' => $product_option['option_id'],
                                'name' => $option_info['name'],
                                'type' => $option_info['type'],
                                'option_value' => $option_value_data,
                                'required' => $product_option['required']
                            );
                        } else {
                            $option_data[] = array(
                                'product_option_id' => $product_option['product_option_id'],
                                'option_id' => $product_option['option_id'],
                                'name' => $option_info['name'],
                                'type' => $option_info['type'],
                                'option_value' => $product_option['option_value'],
                                'required' => $product_option['required']
                            );
                        }
                    }
                }

                $result['discount_price'] = 0;
                $result['discount_quantity'] = 0;

                if(count($product_specials) > 0){
                    foreach ($product_specials as $special_price) {

                        $date_start = $special_price['date_start'];
                        $date_end = $special_price['date_end'];
                        
                        if(
                            ($date_start == null || !$date_start || $date_start == "0000-00-00") &&
                            ($date_end == null || !$date_end || $date_end == "0000-00-00")
                        ) {
                            if ($special_price['type'] == 'discount') {
                                $result['discount_price'] = $special_price['price'];
                                $result['discount_quantity'] = $special_price['quantity'];
                                break;
                            } else if ($special_price['type'] == 'special') {
                                $result['price'] = $special_price['price'];
                                $result['discount_quantity'] = 1;
                                break;
                            }
                        } else {
                            if ($special_price['date_end'] >= date("Y-m-d",time())) {
                                if ($special_price['type'] == 'discount') {
                                    $result['discount_price'] = $special_price['price'];
                                    $result['discount_quantity'] = $special_price['quantity'];
                                    break;
                                } else if ($special_price['type'] == 'special') {
                                    $result['price'] = $special_price['price'];
                                    $result['discount_quantity'] = 1;
                                    break;
                                }
                            }
                        }
                    }
                }

                $json[] = array(
                    'product_id' => $result['product_id'],
                    'name' => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
                    'model' => $result['model'],
                    'option' => $option_data,
                    'price' => $result['price'],
                    'discount_price' => $result['discount_price'],
                    'discount_quantity' => $result['discount_quantity'],
                    'total' => $result['price'],
                    'image' => HTTP_IMAGE . '/' . $result['image'],
                );
            }
        }

        $this->response->setOutput(json_encode($json));
    }

    public function getZonesByCountriesId()
    {
        $country_id = implode(',', $this->request->post['country_id']);

        if (!empty($country_id)) {

            $countriesZonesArray = [];
            $lang_id = $this->config->get('config_language_id');
            $this->load->model('localisation/zone');

            $countriesZonesArray['countries'] = $country_id;
            $countriesZonesArray['langId'] = $lang_id;

            $zonesData = $this->model_localisation_zone->getZonesByCountriesId($countriesZonesArray);

            $this->response->setOutput(json_encode($zonesData));
        }
    }
}
