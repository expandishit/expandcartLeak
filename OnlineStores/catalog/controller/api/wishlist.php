<?php
require_once('jwt_helper.php');
class ControllerApiWishlist extends Controller
{
    public $wishlist = [];
    public function getList() {
        try {
            $this->load->language('api/cart');
            $json = array();
            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;
            $this->load->model('account/api');

            if (!isset($this->session->data['api_id'])) {
                $json['error']['warning'] = $this->language->get('error_permission');
            } else {
                $this->language->load('account/wishlist');

                $json = array();

                $this->load->model('catalog/product');
                $this->load->model('tool/image');


                $json['products'] = array();
                $this->load->model('account/wishlist');
                $this->wishlist = $this->model_account_wishlist->getWishlist($this->session->data['customer_id']);
                foreach ($this->wishlist as $key => $value) {
                    $product_info = $this->model_catalog_product->getProduct($value);

                    if ($product_info) {
                        if ($product_info['image']) {
                            $image = $this->model_tool_image->resize($product_info['image'], 200, 200);
                        } else {
                            $image = false;
                        }

                        if ($product_info['quantity'] <= 0) {
                            $stock = $product_info['stock_status'];
                        } elseif ($this->config->get('config_stock_display')) {
                            $stock = $product_info['quantity'];
                        } else {
                            $stock = $this->language->get('text_instock');
                        }

                        if ($this->customer->isCustomerAllowedToViewPrice()) {
                            $price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')));
                        } else {
                            $price = false;
                        }

                        if ((float)$product_info['special']) {
                            $special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')));
                        } else {
                            $special = false;
                        }
                         ///check permissions to view Add to Cart Button
						$viewAddToCart = true;
						$hidCartConfig = $this->config->get('config_hide_add_to_cart');
						$cutomerAddCartPermission= $this->customer->isCustomerAllowedToAddToCart();
						if(($product_info['quantity'] <=0 && $hidCartConfig) || !$cutomerAddCartPermission)
						{
							$viewAddToCart = false;
						}	

                        $json['products'][] = array(
                            'product_id' => $product_info['product_id'],
                            'image' => $image,
                            'name' => $product_info['name'],
                            'model' => $product_info['model'],
                            'quantity' => $product_info['quantity'],
                            'stock' => $stock,
                            'price' => $price,
                            'special' => $special,
                            'viewAddToCartBtn'=>$viewAddToCart,
                            'short_description' => $product_info['meta_description']
                            //'href'       => $this->url->link('product/product', 'product_id=' . $product_info['product_id']),
                            //'remove'     => $this->url->link('account/wishlist', 'remove=' . $product_info['product_id'])
                        );
                    } else {
                        unset($this->wishlist[$key]);
                    }
                }

                $this->response->addHeader('Content-Type: application/json');
                $this->response->addHeader('Access-Control-Allow-Origin: *');
                $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
                $this->response->addHeader('Access-Control-Allow-Credentials: true');

                $this->response->setOutput(json_encode($json));
            }
        } catch (Exception $ex) {

        }
    }

    public function add() {
        try {
            $this->load->language('api/cart');
            $json = array();

            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;
            $this->load->model('account/api');
            $this->load->model('account/wishlist');

            if (!isset($this->session->data['api_id'])) {
                $json['error']['warning'] = $this->language->get('error_permission');
            } else {
                $this->language->load('account/wishlist');

                $json = array();

                if (isset($params->product_id)) {
                    $product_id = $params->product_id;
                } else {
                    $product_id = 0;
                }

                $this->load->model('catalog/product');

                $product_info = $this->model_catalog_product->getProduct($product_id);
                $this->wishlist = $this->model_account_wishlist->getWishlist($this->session->data['customer_id']);

                if ($product_info) {
                    if (!in_array($product_id, $this->wishlist)) {
                        $this->wishlist[] = $product_id;
                        $products = serialize($this->wishlist);

                        //Note - Registration required before adding wishlist items
                        if (!empty($products) && !empty($this->session->data['customer_id'])) {
                            $this->model_account_wishlist->UpdateWishlist($products,$this->session->data['customer_id']);
                        }
                    }

                    if ($this->customer->isLogged()) {
                        $json['success'] = sprintf($this->language->get('text_success'), $this->url->link('product/product', 'product_id=' . $this->request->post['product_id']), $product_info['name'], $this->url->link('account/wishlist'));
                    } else {
                        $json['success'] = sprintf($this->language->get('text_login'), $this->url->link('account/login', '', 'SSL'), $this->url->link('account/register', '', 'SSL'), $this->url->link('product/product', 'product_id=' . $this->request->post['product_id']), $product_info['name'], $this->url->link('account/wishlist'));
                    }

                    $json['total'] = count($this->session->data['wishlist']);
                }

                $this->model_account_api->updateSession($encodedtoken);

                $this->response->addHeader('Content-Type: application/json');
                $this->response->addHeader('Access-Control-Allow-Origin: *');
                $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
                $this->response->addHeader('Access-Control-Allow-Credentials: true');

                $this->response->setOutput(json_encode($json));
            }
        } catch (Exception $ex) {

        }
    }

    public function remove() {
        try {
            $this->load->language('api/cart');
            $json = array();
            $this->load->model('account/wishlist');

            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;
            $this->load->model('account/api');

            if (!isset($this->session->data['api_id'])) {
                $json['error']['warning'] = $this->language->get('error_permission');
            } else {
                $this->language->load('account/wishlist');

                $json = array();

                if (isset($params->product_id)) {
                    $product_id = $params->product_id;
                } else {
                    $product_id = 0;
                }

                $this->load->model('catalog/product');
                $this->wishlist = $this->model_account_wishlist->getWishlist($this->session->data['customer_id']);

                $key = array_search($product_id, $this->wishlist);
                if ($key !== false) {
                    unset($this->wishlist[$key]);
                    unset($this->session->data['wishlist'][$key]);
                    $this->wishlist = serialize($this->wishlist);
                    $this->model_account_wishlist->UpdateWishlist($this->wishlist,$this->session->data['customer_id']);
                }

                $json['success'] = $this->language->get('text_remove');

                $this->model_account_api->updateSession($encodedtoken);

                $this->response->addHeader('Content-Type: application/json');
                $this->response->addHeader('Access-Control-Allow-Origin: *');
                $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
                $this->response->addHeader('Access-Control-Allow-Credentials: true');

                $this->response->setOutput(json_encode($json));
            }
        } catch (Exception $ex) {

        }
    }

    function utf8_string_array_encode(&$array){
        $func = function(&$value,&$key){
            if(is_string($value)){
                $value = htmlentities($value, ENT_QUOTES, 'UTF-8');
            }
            if(is_string($key)){
                $key = htmlentities($key, ENT_QUOTES, 'UTF-8');
            }
            if(is_array($value)){
                $this->utf8_string_array_encode($value);
            }
        };
        array_walk($array,$func);
        return $array;
    }
}