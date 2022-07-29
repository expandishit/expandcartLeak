<?php
require_once('jwt_helper.php');
class ControllerApiCart extends Controller {
	public function add() {
	    try {
            $this->load->language('api/cart');
            $json = array();

            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;
            $this->load->model('account/api');
            $this->load->model('catalog/product');

            if (!isset($this->session->data['api_id'])) {
                $json['error']['warning'] = $this->language->get('error_permission');
            }
            elseif($this->config->get('login_before_add_to_cart') == 1 && !$this->customer->isLogged()){ 
                $json['error']['warning'] = $this->language->get('error_login_first');
            }
            else {
                if (isset($params->product)) {
                    $params->product = (array) $params->product;


                    $json['data'] = [];
                    foreach ($params->product as $product) {
                        $product_info = $this->model_catalog_product->getProduct($product->product_id);
                        if (!$product_info['status']){
                            $json['error']['warning'][] =
                                str_replace('{product_id}',$product_info['product_id'],$this->language->get('error_product_status'));
                        }
                        if ($product->quantity > $product_info['quantity'] && !$product_info['unlimited']) // Check product quantity
                        {
                            $json['status_code'] = 403;
                            $json['error']['warning'][] = "unavailable quantity";
                        }
                        if (isset($product->option)) {
                            $option = get_object_vars($product->option);
                            $newOptionArray = array();
                            foreach ($option as $key => $option_value){
                                $option_value_exp = explode(",",$option_value);
                                if(count($option_value_exp) > 1){
                                    foreach ($option_value_exp as $valueExp){
                                        $newOptionArray[$key][] = $valueExp;
                                    }
                                }else{
                                    $newOptionArray[$key] = $option_value;
                                }
                            }
                            $option = $newOptionArray;
                        } else {
                            $option = array();
                        }
                        // rental app
                        $this->load->model('module/rental_products/settings');
                        if ($this->model_module_rental_products_settings->isActive() && !empty(get_object_vars($option['rentalRange'])) ) 
                        {
                            // check if these days span has a disabled day and return error instead of adding to cart
                            $option = get_object_vars($option['rentalRange']);
                            $from = strtotime($option['from']);
                            $to = strtotime($option['to']);
                            $disabled_days = $this->model_catalog_product->getRentDisabledDates($from,$to,$product->product_id,$product_info['quantity'],$product->quantity);

                            if(count($disabled_days)){
                                $json['error']['option'] = $this->language->get('error_rental_days');
                            }
                            else{
                                $option['rentalRange']['to'] = $to ;
                                $option['rentalRange']['from'] = $from;
                            }
                        }

                        $this->load->model('module/product_bundles/settings');
                        if ( $this->model_module_product_bundles_settings->isActive() && isset($option['productBundles']) ) {
                            $option['productBundles'] = true;
                        }
                        $productsReadyToCart[]= ['product_id' => $product->product_id , 'quantity' =>  $product->quantity  , 'option' => $option ];
                        $json['data'][] = [
                            'id' => $product_info['product_id'],
                            'name' => $product_info['name'],
                            'model' => $product_info['model'],
                            'quantity' => $product_info['quantity'],
                            'uid' => $this->cart->getCartProductUid(),
                        ];
                    }
                }

                if (isset($params->product_id)) {

                    $product_info = $this->model_catalog_product->getProduct($params->product_id);
                    if (!$product_info['status']){
                        $json['error']['warning'][] =
                            str_replace('{product_id}',$product_info['product_id'],$this->language->get('error_product_status'));
                    }
                    if ($product_info) {
                        if (isset($params->quantity)) {
                            $quantity = $params->quantity;
                        } else {
                            $quantity = 1;
                        }

                        if (isset($params->option)) {
                            $option = get_object_vars($params->option);
                        } else {
                            $option = array();
                        }
                        $this->load->model('module/trips');
                        if($this->model_module_trips->isTripsAppInstalled()) 
                        {
                            $this->load->model('multiseller/seller');
                            $this->load->language('multiseller/multiseller');
                            $tripData= $this->model_module_trips->getTripProduct($params->product_id); 
                            if($params->quantity > $product_info['quantity']){
                                $json['error']['available_seats']= $this->language->get('available_seats').$product_info['quantity'];
                            }
                            if($params->quantity > $tripData['max_no_seats']){
                                $json['error']['max_seats']=$this->language->get('max_seats').$tripData['max_no_seats'];
                            }
                            if($params->quantity < $tripData['min_no_seats']){
                                $json['error']['min_seats']=$this->language->get('min_seats').$tripData['min_no_seats'];
                            }
                             
                        }

                        $product_options = $this->model_catalog_product->getProductOptions($params->product_id);

                        foreach ($product_options as $product_option) {
                            if ($product_option['required'] && empty($option[$product_option['product_option_id']])) {
                                $json['error']['option'][$product_option['product_option_id']] = sprintf($this->language->get('error_required'), $product_option['name']);
                            }
                        }

                        if (!isset($json['error']['option'])) {
                            $productsReadyToCart[]= ['product_id' => $params->product_id , 'quantity' =>  $params->quantity  , 'option' => $option ];

                            $json['success'] = $this->language->get('text_success');
                            $json['status'] = 'OK';
                            $json['data'] = [
                                [
                                    'id' => $product_info['product_id'],
                                    'name' => $product_info['name'],
                                    'model' => $product_info['model'],
                                    'quantity' => $product_info['quantity'],
                                    'uid' => $this->cart->getCartProductUid(),
                                ]
                            ];

                            unset($this->session->data['shipping_method']);
                            unset($this->session->data['shipping_methods']);
                            unset($this->session->data['payment_method']);
                            unset($this->session->data['payment_methods']);
                        }
                    } else {
                        $json['error']['store'] = $this->language->get('error_store');
                    }
                }
            }
            if(!$json['error']){
                $json['success'] = $this->language->get('text_success');
                $json['status'] = 'OK';
                foreach ($productsReadyToCart as $product){
                    $this->cart->add($product['product_id'], $product['quantity'], $product['option']);
                }
            }
            $this->model_account_api->updateSession($encodedtoken);

            $this->response->addHeader('Content-Type: application/json');
            $this->response->addHeader('Access-Control-Allow-Origin: *');
            $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
            $this->response->addHeader('Access-Control-Allow-Credentials: true');

            $this->response->setOutput(json_encode($json));
        }
        catch(Exception $e) {

        }
	}

	public function edit() {
		$this->load->language('api/cart');

		$json = array();

		if (!isset($this->session->data['api_id'])) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			$this->cart->update($this->request->post['key'], $this->request->post['quantity']);

			$json['success'] = $this->language->get('text_success');

			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['reward']);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function remove() {
        try {
            $this->load->language('api/cart');
            $json = array();

            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;
            $this->load->model('account/api');

            if (!isset($this->session->data['api_id'])) {
                $json['error'] = $this->language->get('error_permission');
            } else {
                // Remove
                if (isset($params->key)) {
                    $this->cart->remove($params->key);

                    unset($this->session->data['vouchers'][$params->key]);

                    $json['success'] = $this->language->get('text_success');

                    unset($this->session->data['shipping_method']);
                    unset($this->session->data['shipping_methods']);
                    unset($this->session->data['payment_method']);
                    unset($this->session->data['payment_methods']);
                    unset($this->session->data['reward']);
                }
            }

            $this->model_account_api->updateSession($encodedtoken);

            $this->response->addHeader('Content-Type: application/json');
            $this->response->addHeader('Access-Control-Allow-Origin: *');
            $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
            $this->response->addHeader('Access-Control-Allow-Credentials: true');

            $this->response->setOutput(json_encode($json));
        }
        catch(Exception $e) {

        }
	}

    public function updatequantity() {
        try {
            $this->load->language('api/cart');
            $json = array();

            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;
            $this->load->model('account/api');

            if (!isset($this->session->data['api_id'])) {
                $json['error'] = $this->language->get('error_permission');
            } else {
                // update quantity
                if (isset($params->key) && isset($params->quantity)) {
                    $this->cart->update($params->key, $params->quantity);

                    $json['success'] = $this->language->get('text_success');

                    unset($this->session->data['shipping_method']);
                    unset($this->session->data['shipping_methods']);
                    unset($this->session->data['payment_method']);
                    unset($this->session->data['payment_methods']);
                    unset($this->session->data['reward']);
                }
            }

            $this->model_account_api->updateSession($encodedtoken);

            $this->response->addHeader('Content-Type: application/json');
            $this->response->addHeader('Access-Control-Allow-Origin: *');
            $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
            $this->response->addHeader('Access-Control-Allow-Credentials: true');

            $this->response->setOutput(json_encode($json));
        }
        catch(Exception $e) {

        }
    }

	public function products() {
	    
		$this->load->language('api/cart');
		$json = array();

        $params = json_decode(file_get_contents('php://input'));
        $encodedtoken = $params->token;
        $language_id = $this->_getLanguageId($params->locale); //should be 'en', 'ar', 'ar-SA' ... etc        
        $with_recommended_products  = $params->with_recommended_products  ?? 0;
        $this->load->model('account/api');
        $this->load->model('catalog/product');

		if (!isset($this->session->data['api_id'])) {
			$json['error']['warning'] = $this->language->get('error_permission');
		} else {
			// Stock

			if (!$this->cart->hasStock(false, [], $language_id) && (!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning'))) {
				$json['error']['stock'] = $this->language->get('error_stock');
			}

			// Products
			$json['products'] = array();
			$products = $this->cart->getProducts($language_id);
            //$this->load->model('catalog/product');
            //$products=$this->model_catalog_product->getProducts();

			foreach ($products as $product) {
				$product_total = 0;

				foreach ($products as $product_2) {
					if ($product_2['product_id'] == $product['product_id']) {
						$product_total += $product_2['quantity'];
					}
				}

				if ($product['minimum'] > $product_total) {
					$json['error']['minimum'][] = sprintf($this->language->get('error_minimum'), $product['name'], $product['minimum']);
				}

				$option_data = array();

				foreach ($product['option'] as $option) {
					$option_data[] = array(
						'product_option_id'       => $option['product_option_id'],
						'product_option_value_id' => $option['product_option_value_id'],
						'name'                    => $option['name'],
						'value'                   => $option['option_value'],
						'type'                    => $option['type']
					);
				}

                $this->load->model('tool/image');

                if ($product['image']) {
                    $image = $this->model_tool_image->resize($product['image'], 250, 250);
                } else {
                    $image = $this->model_tool_image->resize('no_image.jpg', 250, 250);
                }

                $float_price = $this->tax->calculate(
                    $product['price'],
                    $product['tax_class_id'],
                    $this->config->get('config_tax')
                );
              
                $recommended_results = $this->model_catalog_product->getBestSellerProducts(10);
                $float_total = $float_price * $product['quantity'];
				$json['products'][] = array(
					'key'        => $product['key'],
					'product_id' => $product['product_id'],
					'name'       => $product['name'],
					'model'      => $product['model'],
                    'option'     => $option_data,
                    'rent_data'  => array("from"=> date('Y-m-d',$product['rentData']['range']['from']),
                                           "to" => date('Y-m-d',$product['rentData']['range']['to'])
                    ),
					'quantity'   => $product['quantity'],
					'available_quantity'   => $product['stock_quantity'],
					'unlimited'   => (int) $product['unlimited'],
					'stock'      => $product['stock'] ? true : !(!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning')),
					'shipping'   => $product['shipping'],
					'price'      => $this->currency->format($float_price),
                    'float_price' => $float_price,
                    'currency' => $this->currency->getCode(),
					'total'      => $this->currency->format($float_total),
                    'float_total' => $float_total,
					'reward'     => $product['reward'],
                    'image'      => $image,
				);
			}

            if($with_recommended_products)
                  $json['recommended']=$this->model_catalog_product->getRecommendedProducts();
			// Voucher
			$json['vouchers'] = array();

			if (!empty($this->session->data['vouchers'])) {
				foreach ($this->session->data['vouchers'] as $key => $voucher) {
					$json['vouchers'][] = array(
						'code'             => $voucher['code'],
						'description'      => $voucher['description'],
						'from_name'        => $voucher['from_name'],
						'from_email'       => $voucher['from_email'],
						'to_name'          => $voucher['to_name'],
						'to_email'         => $voucher['to_email'],
						'voucher_theme_id' => $voucher['voucher_theme_id'],
						'message'          => $voucher['message'],
						'amount'           => $this->currency->format($voucher['amount'])
					);
				}
			}

			// Totals
			$this->load->model('extension/extension');

			$total_data = array();
			$total = 0;
			$taxes = $this->cart->getTaxes();

			$sort_order = array();

			$results = $this->model_extension_extension->getExtensions('total');

			foreach ($results as $key => $value) {
				$sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
			}

			array_multisort($sort_order, SORT_ASC, $results);
            
			try {
                foreach ($results as $result) {
                    if ($this->config->get($result['code'] . '_status')) {
                        $this->load->model('total/' . $result['code']);

                        $this->{'model_total_' . $result['code']}->getTotal($total_data, $total, $taxes);
                    }
                }
            }catch (Exception $exception){
                $json['Error'] = [
                    'Success'=> False,
                    'Message'=> $exception->getMessage(),
                    'Erorr Code' => 404
                ];
            }
		

			$sort_order = array();

			foreach ($total_data as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $total_data);

			$json['totals'] = array();

            $generic_total = $sub_total = $this->cart->getSubTotal();

            $json['totals'][] = array(
                'title' => 'generic_total_mobile',
                'text'  => $this->currency->format($generic_total)
            );

			foreach ($total_data as $total) {
				$json['totals'][] = array(
					'title' => $total['title'],
					'text'  => $this->currency->format($total['value']),
                    'code'=> $total['code'],
                    'value'=> $total['value'],
                    'sort_order'=> $total['sort_order'],
				);
			}
		}
        $this->model_account_api->updateSession($encodedtoken);
		$this->response->addHeader('Content-Type: application/json');
        $this->response->addHeader('Access-Control-Allow-Origin: *');
        $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
        $this->response->addHeader('Access-Control-Allow-Credentials: true');
		$this->response->setOutput(json_encode($json));
	}

    public function test(){
        try {
            $this->load->language('api/cart');
            $json = array();
            //$product_info = array();
            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;

            $this->load->model('account/api');

            if (!isset($this->session->data['api_id'])) {
                $json['error']['warning'] = $this->language->get('error_permission');
            } else {
                //$this->language->load('product/product');
                $this->load->model('catalog/product');

                $json = $this->model_catalog_product->getProducts();
                //$product_info = $this->cart->getProducts();
            }


            //$json=$this->session;
            $this->response->addHeader('Content-Type: application/json');
            $this->response->addHeader('Access-Control-Allow-Origin: *');
            $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
            $this->response->addHeader('Access-Control-Allow-Credentials: true');
            //$json['cookie'] = $_COOKIE[$this->session->getId()];
            $this->response->setOutput(json_encode($json));

        }
        catch(Exception $e) {

        }
    }

    //Get Language id by it's locale code..
    private function _getLanguageId($locale){
        $this->load->model('localisation/language');
        return $this->model_localisation_language->getLanguageByLocale($locale)['language_id'];
    }
 
}
