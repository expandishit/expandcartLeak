<?php
include_once '../jwt_helper.php';
class ControllerApiSellersOrder extends Controller
{
    public function get_orders() {
        try {
            $this->load->language('api/cart');
            $json = array();

            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;
            $this->load->model('account/api');

            if (!isset($this->session->data['api_id'])) {
                $json['error']['warning'] = $this->language->get('error_permission');
            } else {
                $this->language->load('account/order');
                $this->load->model('account/order');
                $this->load->model('catalog/product');
                $this->load->model('module/trips');
                $json['orders'] = array();
                $seller_id = $params->seller_id;
                $product_id = $params->product_id;
                $results = $this->MsLoader->MsOrderData->getOrders(
                 array( 'seller_id' => $seller_id,'product_id' => $product_id ) );
                foreach ($results as $result) {
                    $product_total = $this->model_account_order->getTotalOrderProductsByOrderId($result['order_id']);
                    $voucher_total = $this->model_account_order->getTotalOrderVouchersByOrderId($result['order_id']);
                 
				if((\Extension::isInstalled('multiseller'))&&(\Extension::isInstalled('trips') 
				    && $this->config->get('trips')['status']==1) &&$params->trips==1) 
				 {
					 $this->load->model('multiseller/seller');
					 $products = $this->model_account_order->getOrderProductsTrips($result['order_id'],$params->trips_date);
					 foreach ($products as $product) {
						$product_info = $this->model_catalog_product->getProduct($product['product_id']);
						$tripData= $this->model_module_trips->getTripProduct($product_info['product_id']); 
                        $arrivalData['customer_id'] = $result['customer_id'];
                        $arrivalData['product_id'] = $product['product_id'];         
                        $arrivalData['order_id'] = $result['order_id'];         
						$arrival= $this->model_module_trips->getTripArrivalStatus($arrivalData); 
						 $json['orders'][] = array(
							 'order_id'   => $result['order_id'],
							 'product_id'   => $product['product_id'],
                             'name' => $product['name'],
                             'customer_name'=> $result['firstname'] . ' ' . $result['lastname'],
                             'customer_phone'       => $result['telephone'] ,
                             'customer_id'       => $result['customer_id'] ,
                             'arrival_status'=> $arrival['arrived'],
							 'image' => \Filesystem::getUrl('image/'.$product_info['image']),
							 'reservation' => $product['quantity'],
							 'Date' => $tripData['from_date'],
							 'currency' => $this->currency->getCode(),
							 'price' => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
							 'total' => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']),
						 );
					 }
				 }else{

                    $json['orders'][] = array(
                        'order_id'   => $result['order_id'],
                        'name'       => $result['firstname'] . ' ' . $result['lastname'],
                        'status'     => $result['status'],
                        'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
                        'products'   => ($product_total + $voucher_total),
                        'total'      => $this->currency->format($result['total'], $result['currency_code'], $result['currency_value']),
                        //'href'       => $this->url->link('account/order/info', 'order_id=' . $result['order_id'], 'SSL'),
                        //'reorder'    => $this->url->link('account/order', 'order_id=' . $result['order_id'], 'SSL')
                    );
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
    public function getInfo() {
        try {
            $this->load->language('api/cart');
            $json = array();

            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;
            $seller_id = $params->seller_id;
            $this->load->model('account/api');

            if (!isset($this->session->data['api_id'])) {
                $json['error']['warning'] = $this->language->get('error_permission');
            } else {
                $json = array();
                if ($this->customer->isLogged()) {
                    $this->load->model('account/order');
                    $this->load->model('catalog/product');

                    if (isset($params->order_id)) {
                        $order_id = $params->order_id;
                    } else {
                        $order_id = 0;
                    }

                    $order_info = $this->model_account_order->getOrder($order_id,$seller_id);

                    if ($order_info) {
                        if ($order_info['invoice_no']) {
                            $json['invoice_no'] = $order_info['invoice_prefix'] . $order_info['invoice_no'];
                        } else {
                            $json['invoice_no'] = '';
                        }

                        $json['order_id'] = $order_id;
                        $json['customer_name'] = $order_info['firstname'] . ' ' . $order_info['lastname'];
                        $json['date_added'] = date($this->language->get('date_format_short'), strtotime($order_info['date_added']));

                        if ($order_info['payment_address_format']) {
                            $format = $order_info['payment_address_format'];
                        } else {
                            $format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
                        }

                        $find = array(
                            '{firstname}',
                            '{lastname}',
                            '{company}',
                            '{address_1}',
                            '{address_2}',
                            '{city}',
                            '{postcode}',
                            '{zone}',
                            '{zone_code}',
                            '{country}'
                        );

                        $replace = array(
                            'firstname' => $order_info['payment_firstname'],
                            'lastname' => $order_info['payment_lastname'],
                            'company' => $order_info['payment_company'],
                            'address_1' => $order_info['payment_address_1'],
                            'address_2' => $order_info['payment_address_2'],
                            'city' => $order_info['payment_city'],
                            'postcode' => $order_info['payment_postcode'],
                            'zone' => $order_info['payment_zone'],
                            'zone_code' => $order_info['payment_zone_code'],
                            'country' => $order_info['payment_country']
                        );

                        $json['payment_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

                        $json['payment_method'] = $order_info['payment_method'];

                        if ($order_info['shipping_address_format']) {
                            $format = $order_info['shipping_address_format'];
                        } else {
                            $format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
                        }

                        $find = array(
                            '{firstname}',
                            '{lastname}',
                            '{company}',
                            '{address_1}',
                            '{address_2}',
                            '{city}',
                            '{postcode}',
                            '{zone}',
                            '{zone_code}',
                            '{country}'
                        );

                        $replace = array(
                            'firstname' => $order_info['shipping_firstname'],
                            'lastname' => $order_info['shipping_lastname'],
                            'company' => $order_info['shipping_company'],
                            'address_1' => $order_info['shipping_address_1'],
                            'address_2' => $order_info['shipping_address_2'],
                            'city' => $order_info['shipping_city'],
                            'postcode' => $order_info['shipping_postcode'],
                            'zone' => $order_info['shipping_zone'],
                            'zone_code' => $order_info['shipping_zone_code'],
                            'country' => $order_info['shipping_country']
                        );
                       
                        $json['shipping_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

                        $json['shipping_method'] = $order_info['shipping_method'];

                        $json['products'] = array();

                        $products = $this->model_account_order->getOrderProducts($order_id);
                      

                        foreach ($products as $product) {
                            $option_data = array();

                            $options = $this->model_account_order->getOrderOptions($order_id, $product['order_product_id']);
                            $product_info = $this->model_catalog_product->getProduct($product['product_id']);
                             $this->load->model('module/trips');
                            if($this->model_module_trips->isTripsAppInstalled()) 
                            {
                             $tripData= $this->model_module_trips->getTripProduct($product_info['product_id']); 
                            }

                            foreach ($options as $option) {
                                if ($option['type'] != 'file') {
                                    $value = $option['value'];
                                } else {
                                    $value = utf8_substr($option['value'], 0, utf8_strrpos($option['value'], '.'));
                                }

                                $option_data[] = array(
                                    'name' => $option['name'],
                                    'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
                                );
                            }

                            $json['products'][] = array(
                                'name' => $product['name'],
                                'model' => $product['model'],
                                'image' => \Filesystem::getUrl('image/'.$product_info['image']),
                                'manufacturer' => $product_info['manufacturer'],
                                'option' => $option_data,
                                'quantity' => $product['quantity'],
                                'currency' => $this->currency->getCode(),
                                'price' => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
                                'total' => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']),
                                'return' => $this->url->link('account/return/insert', 'order_id=' . $order_info['order_id'] . '&product_id=' . $product['product_id'], 'SSL'),
                                'trip_data'=>$tripData
                            );
                        }

                        // Voucher
                        $json['vouchers'] = array();

                        $vouchers = $this->model_account_order->getOrderVouchers($order_id);

                        foreach ($vouchers as $voucher) {
                            $json['vouchers'][] = array(
                                'description' => $voucher['description'],
                                'amount' => $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value'])
                            );
                        }

                        $json['totals'] = $this->model_account_order->getOrderTotals($order_id);

                        $json['comment'] = nl2br($order_info['comment']);

                        $json['histories'] = array();

                        $this->load->model('aramex/aramex');
                        $checkAWB = $this->model_aramex_aramex->checkAWB($order_id);
                        if ($checkAWB) {
                            $json['track_url'] = $this->url->link('account/aramex_traking', 'order_id=' . $order_id, 'SSL');
                        } else {
                            $json['track_url'] = "";
                        }

                        $results = $this->model_account_order->getOrderHistories($order_id);

                        foreach ($results as $result) {
                            $json['histories'][] = array(
                                'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
                                'status' => $result['status'],
                                'comment' => nl2br($result['comment'])
                            );
                        }
                    }
                } else {
                    $json['error'] = "not registered!";
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

    public function get_transactions() {
        try {
            $this->load->language('api/cart');
            $json = array();

            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;
            $this->load->model('account/api');
            $this->load->model('catalog/product');

            if (!isset($this->session->data['api_id'])) {
                $json['error']['warning'] = $this->language->get('error_permission');
            } else {
                $this->language->load('account/order');
                $json['transactions'] = array();
                $seller_id = $params->seller_id;
               
                $results = $this->MsLoader->MsBalance->getBalanceEntries(
                    array('seller_id' => $seller_id) );
                foreach ($results as $result) {
                    $product_info = $this->model_catalog_product->getProduct($result['product_id']);
                    $json['transactions'][] = array(
                    'transaction_id' => $result['balance_id'],
                    'order_id' => $result['order_id'],
                    'name' => $product_info['name'],
					'amount' => $this->currency->format($result['amount'], $this->config->get('config_currency')),
					'description' => (mb_strlen($result['mb.description']) > 80 ? mb_substr($result['mb.description'], 0, 80) . '...' : $result['mb.description']),
					'date_created' => date($this->language->get('date_format_short'), strtotime($result['mb.date_created'])),
                    );	
				
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
    public function customer_arrival() {
        try {
            $this->load->language('api/cart');
            $json = array();

            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;
            $this->load->model('account/api');

            if (!isset($this->session->data['api_id'])) {
                $json['error']['warning'] = $this->language->get('error_permission');
            } else {
                $orderData['customer_id'] = $params->customer_id;
                $orderData['product_id'] = $params->product_id;         
                $orderData['order_id'] = $params->order_id;         
                $this->load->model('module/trips');
                $arrival_status=$this->model_module_trips->updateArrivalStatus($orderData);
               
                $json['arrival_status'] = $arrival_status['arrived'];
                $json['message'] = 'Arrival status Updated successfully';

                $this->response->addHeader('Content-Type: application/json');
                $this->response->addHeader('Access-Control-Allow-Origin: *');
                $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
                $this->response->addHeader('Access-Control-Allow-Credentials: true');

                $this->response->setOutput(json_encode($json));
            }
        } catch (Exception $ex) {

        }
    }
    public function cancel_trip() {
        try {
            $this->load->language('api/cart');
            $json = array();

            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;
            $this->load->model('account/api');

            if (!isset($this->session->data['api_id'])) {
                $json['error']['warning'] = $this->language->get('error_permission');
            } else {
                $orderData['product_id'] = $params->product_id;         
                $orderData['order_id'] = $params->order_id;             
                $orderData['canceled_by'] = $params->canceled_by;             
                $this->load->model('module/trips');
                $cancelTrip=$this->model_module_trips->cancelTrip($orderData);
                 if($cancelTrip){
                    $json['status'] = true;
                    $json['message'] = 'Trip Canceled successfully';
                 }
                 else{
                     $json['status'] = false;
                    $json['message'] = 'Something Went Wrong';}
                
                $this->response->addHeader('Content-Type: application/json');
                $this->response->addHeader('Access-Control-Allow-Origin: *');
                $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
                $this->response->addHeader('Access-Control-Allow-Credentials: true');

                $this->response->setOutput(json_encode($json));
            }
        } catch (Exception $ex) {

        }
    }


  

}