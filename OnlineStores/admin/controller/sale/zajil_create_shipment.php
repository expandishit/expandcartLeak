<?php
class ControllerSaleZajilCreateShipment extends Controller {
	
	private $error = array();

	public function index() {

		$this->language->load('sale/zajil');
		
		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('sale/order');
		
		$this->load->model('sale/zajil');
		
		$this->data['is_shipment'] = $this->model_sale_zajil->checkAWB($this->request->get['order_id']);
		

		$this->getForm();
	
	}
	
	public function getForm() {
		


		

		$this->language->load('sale/zajil');
		
		$this->load->model('sale/order');
		
		$this->load->model('sale/zajil');
		


		if(isset($this->error['warning'])) {
		
			$this->data['error_warning'] = $this->error['warning'];
		
		}else{
		
			$this->data['error_warning'] = '';
		
		}

		
		
		if(isset($this->request->get['order_id'])) {
		
			$order_id = $this->request->get['order_id'];
		
		} else {
		
			$order_id = 0;
		
		}
		
		$this->data['order_id'] = $order_id;

        $order_id = (int)$order_id;

		$order_info = $this->model_sale_order->getOrder($order_id);	

	
		
		
		if($order_info) {	
			
			

			$this->document->setTitle($this->language->get('heading_title'));
			
			$this->data['breadcrumbs'] = array();

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home')
			);

			$this->data['breadcrumbs'][] = array(
			
				'text'      => $this->language->get('heading_title'),
			
				'href'      => $this->url->link('sale/order')
			
			);

			

			

			$this->data['order_id'] = $this->request->get['order_id'];

			
	
			
			$this->data['back_to_order'] = $this->url->link('sale/order/info', 'order_id=' . $order_id , true);	
	
			$this->data['zajil_create_shipment'] = $this->url->link('sale/zajil_create_shipment', 'order_id=' . $order_id , true);
	
			$this->data['zajil_print_label'] = $this->url->link('sale/zajil_create_shipment/lable', 'order_id=' . $order_id , true);
                        
			$this->data['zajil_print_tracking'] = $this->url->link('sale/zajil_create_shipment/tracking', 'order_id=' . $order_id , true);
	

			
			
		

			$rsawbno= $this->db->query("SELECT awb FROM ".DB_PREFIX."zajil WHERE order_id='". $order_id."'")->row;
	
			

			if($rsawbno){
	
				$this->data['zajil_traking']=$rsawbno['awb'];	
	
			}else{
			
				$this->data['zajil_traking']='';		
				
			}

			
			$this->data['store_name'] = $order_info['store_name'];
			
			$this->data['store_url']  = $order_info['store_url'];
			
			$this->data['firstname']  = $order_info['firstname'];
			
			$this->data['lastname']   = $order_info['lastname'];


		

			if ($order_info['customer_id']) {

				$this->data['customer'] = $this->url->link('sale/customer/update', 'customer_id=' . $order_info['customer_id'], true);
			
			} else {
			
				$this->data['customer'] = '';
			
			}

			

			$this->load->model('sale/customer_group');



			$customer_group_info = $this->model_sale_customer_group->getCustomerGroup($order_info['customer_group_id']);

			if ($customer_group_info) {
				$this->data['customer_group'] = $customer_group_info['name'];
			} else {
				$this->data['customer_group'] = '';
			}
		
			if(isset($this->request->post['items_tobe_shipped_number'])) {
				
				$this->data['items_tobe_shipped_number']= $this->request->post['items_tobe_shipped_number'];
			
			}

			
			
	
	 	$this->data['zajil_sender_email']= $this->config->get('zajil_sender_email');
		 
		$this->data['zajil_password'] = $this->config->get('zajil_password');
			
	if(isset($this->request->post['zajil_service'])) {
		$this->data['zajil_service']=$this->request->post['zajil_service'];
		
	}elseif(isset($this->request->get['zajil_service'])) {
	
		$this->data['zajil_service']=$this->request->get['zajil_service'];
	
	} 
			
	if(isset($this->request->post['zajil_productType'])) {
	 $this->data['zajil_productType'] = $this->request->post['zajil_productType'];
	$this->session->data['productType']=$this->request->post['zajil_productType'];
	
	}else{
	 $this->data['zajil_productType'] = ($this->config->get('zajil_productType'))?$this->config->get('zajil_productType'):'Parcel';
	}
	if(isset($this->request->post['zajil_bookingMode'])) {
	 $this->data['zajil_bookingMode'] = $this->request->post['zajil_bookingMode'];
	$this->session->data['bookingMode']=$this->request->post['zajil_bookingMode'];
	
	}else{
	 $this->data['zajil_bookingMode'] = ($this->config->get('zajil_bookingMode'))?$this->config->get('zajil_bookingMode'):'COD';
	}		
		
			
	##################### config sender details ################
	$receiver_email=$order_info['email'];
	
	if(isset($this->request->post['zajil_sender_name'])) {
			$this->data['zajil_sender_name'] = $this->request->post['zajil_sender_name'];
	}else{
			$this->data['zajil_sender_name'] = ($this->config->get('zajil_sender_name'))?$this->config->get('zajil_sender_name'):'';
	}
	
	if(isset($this->request->post['zajil_sender_phone'])) {
			$this->data['zajil_sender_phone'] = $this->request->post['zajil_sender_phone'];
	}else{
			$this->data['zajil_sender_phone'] = ($this->config->get('zajil_sender_phone'))?$this->config->get('zajil_sender_phone'):'';
	}
	
	if(isset($this->request->post['zajil_sender_city'])) {
			$this->data['zajil_sender_city'] = $this->request->post['zajil_sender_city'];
	}else{
			$this->data['zajil_sender_city'] = ($this->config->get('zajil_sender_city'))?$this->config->get('zajil_sender_city'):'';
	}
	
	if(isset($this->request->post['zajil_sender_address'])) {
			$this->data['zajil_sender_address'] = $this->request->post['zajil_sender_address'];
	}else{
			$this->data['zajil_sender_address'] = ($this->config->get('zajil_sender_address'))?$this->config->get('zajil_sender_address'):'';
	}
	
	

	##################### customer shipment details ################
	
	$shipment_receiver_name ='';
	
	$shipment_receiver_street ='';

	if(isset($order_info['firstname']) && !empty($order_info['firstname']))
	{
		$shipment_receiver_name .= $order_info['firstname'];
	}
	if(isset($order_info['lastname']) && !empty($order_info['lastname']))
	{
		$shipment_receiver_name .= " ".$order_info['lastname'];
	}
	if(isset($order_info['shipping_address_1']) && !empty($order_info['shipping_address_1']))
	{
		$shipment_receiver_street .= $order_info['shipping_address_1'];
	}
	if(isset($order_info['shipping_address_2']) && !empty($order_info['shipping_address_2']))
	{
		$shipment_receiver_street .= " ".$order_info['shipping_address_2'];
	}
	
			
	
	if(isset($this->request->post['zajil_shipment_receiver_name']) && !empty($this->request->post['zajil_shipment_receiver_name'])) {
			$this->data['zajil_shipment_receiver_name'] = $this->request->post['zajil_shipment_receiver_name'];
	}else{
			$this->data['zajil_shipment_receiver_name']    = $shipment_receiver_name;
	}
	
	if(isset($this->request->post['zajil_shipment_receiver_company'])) {
			$this->data['zajil_shipment_receiver_company'] = $this->request->post['zajil_shipment_receiver_company'];
	}else{
			$this->data['zajil_shipment_receiver_company'] = ($order_info['company'])?$order_info['company']:'';
	}
	
	if(isset($this->request->post['zajil_shipment_receiver_street'])) {
			$this->data['zajil_shipment_receiver_street'] = $this->request->post['zajil_shipment_receiver_street'];
	}else{
			$this->data['zajil_shipment_receiver_street']  = $shipment_receiver_street;
	}
	
	if(isset($this->request->post['zajil_shipment_receiver_country'])) {
			$this->data['zajil_shipment_receiver_country'] = $this->request->post['zajil_shipment_receiver_country'];
	}else{
			$this->data['zajil_shipment_receiver_country'] = ($order_info['shipping_iso_code_2'])?$order_info['shipping_iso_code_2']:'';
	}
	
	if(isset($this->request->post['zajil_shipment_receiver_city'])) {
			$this->data['zajil_shipment_receiver_city'] = $this->request->post['zajil_shipment_receiver_city'];
	}else{
			$this->data['zajil_shipment_receiver_city']    = ($order_info['shipping_city']) ? $order_info['shipping_city']:'';
	}
	
	if(isset($this->request->post['zajil_shipment_receiver_postal'])) {
			$this->data['zajil_shipment_receiver_postal'] = $this->request->post['zajil_shipment_receiver_postal'];
	}else{
			$this->data['zajil_shipment_receiver_postal']  = ($order_info['shipping_postcode'])?$order_info['shipping_postcode']:'';
	}
	
	if(isset($this->request->post['zajil_shipment_receiver_state'])) {
			$this->data['zajil_shipment_receiver_state'] = $this->request->post['zajil_shipment_receiver_state'];
	}else{
			$this->data['zajil_shipment_receiver_state']   = ($order_info['shipping_zone'])?$order_info['shipping_zone']:'';
	}
	
	if(isset($this->request->post['zajil_shipment_receiver_phone'])) {
			$this->data['zajil_shipment_receiver_phone'] = $this->request->post['zajil_shipment_receiver_phone'];
	}else{
			$this->data['zajil_shipment_receiver_phone']   = ($order_info['telephone'])?$order_info['telephone']:'';
	}	
	#################
	
	
	$this->data['reference'] = $order_id;
	
	$this->data['zajil_shipment_sender_account'] = ($this->config->get('zajil_account_number'))?$this->config->get('zajil_account_number'):'';
	
	
							
	
   if(isset($this->request->post['zajil_shipment_info_billing_account'])) {
			$this->data['zajil_shipment_info_billing_account'] = $this->request->post['zajil_shipment_info_billing_account'];
	}else{
			$this->data['zajil_shipment_info_billing_account'] = "";
	}
	if(isset($this->request->post['zajil_shipment_info_payment_option'])) {
			$this->data['pay_option'] = $this->request->post['zajil_shipment_info_payment_option'];
	
	$this->session->data['pay_option']=$this->request->post['zajil_shipment_info_payment_option'];
	}else{
			$this->data['pay_option'] = '';
	}
	if(isset($this->request->post['zajil_shipment_currency_code'])) {
			$this->data['currency_code'] = $this->request->post['zajil_shipment_currency_code'];
	}else{
			$this->data['currency_code'] = ($order_info['currency_code'])?$order_info['currency_code']:'';
	}
	
	if(isset($this->request->post['zajil_shipment_info_cod_amount'])) {
			$this->data['cod_value'] = $this->request->post['zajil_shipment_info_cod_amount'];
	}else{
			$this->data['cod_value'] = ($order_info['total'])?$order_info['total']:'';
	}
	
	if(isset($this->request->post['zajil_shipment_info_custom_amount'])) {
	 $this->data['custom_amount'] = $this->request->post['zajil_shipment_info_custom_amount'];
	}else{
	 $this->data['custom_amount'] = '';
	}
	if(isset($this->request->post['zajil_shipment_info_comment'])) {
			$this->data['zajil_shipment_info_comment'] = $this->request->post['zajil_shipment_info_comment'];
	}else{
			$this->data['zajil_shipment_info_comment'] = '';
	}
	if(isset($this->request->post['zajil_shipment_info_foreignhawb'])) {
			$this->data['zajil_shipment_info_foreignhawb'] = $this->request->post['zajil_shipment_info_foreignhawb'];
	}else{
			$this->data['zajil_shipment_info_foreignhawb'] = '';
	}
	if(isset($this->request->post['weight_unit'])) {
			$getunit_classid = $this->model_sale_zajil->getWeightClassId($this->request->post['weight_unit']);
			$this->data['weight_unit'] = $getunit_classid->row['unit'];
			$config_weight_class_id = $getunit_classid->row['weight_class_id'];
	}else{
			$this->data['weight_unit'] = $this->weight->getUnit($this->config->get('config_weight_class_id'));
			$config_weight_class_id = $this->config->get('config_weight_class_id');
	}
	
		$this->data['total'] = ($order_info['total'])?number_format($order_info['total'],0):'';

	    ########### product list ##########
		if (isset($this->request->post['order_product'])) {
			$order_products = $this->request->post['order_product'];
		} elseif (isset($this->request->get['order_id'])) {
			$order_products = $this->model_sale_order->getOrderProducts($this->request->get['order_id']);
		} else {
			$order_products = array();
		}
		$this->data['order_products'] = array();
		$weighttot = 0;
		foreach ($order_products as $order_product) {
			if (isset($order_product['order_option'])) {
				$order_option = $order_product['order_option'];
			} elseif (isset($this->request->get['order_id'])) {
				$order_option = $this->model_sale_order->getOrderOptions($this->request->get['order_id'], $order_product['order_product_id']);
				$product_weight_query = $this->db->query("SELECT weight, weight_class_id FROM " . DB_PREFIX . "product WHERE product_id = '" . $order_product['product_id'] . "'");
				$weight_class_query = $this->db->query("SELECT wcd.unit FROM " . DB_PREFIX . "weight_class wc LEFT JOIN " . DB_PREFIX . "weight_class_description wcd ON (wc.weight_class_id = wcd.weight_class_id) WHERE wcd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND wc.weight_class_id = '" . $product_weight_query->row['weight_class_id'] . "'");
			} else {
				$order_option = array();
			}

		
			$prodweight = $this->weight->convert($product_weight_query->row['weight'], $product_weight_query->row['weight_class_id'], $config_weight_class_id);
			$prodweight = ($prodweight * $order_product['quantity']); 
			$weighttot = ($weighttot + $prodweight);
			$this->data['product_arr'][] = $order_product['name'];
			$this->data['order_products'][] = array(
				'order_product_id' => $order_product['order_product_id'],
				'product_id'       => $order_product['product_id'],
				'name'             => $order_product['name'],
				'model'            => $order_product['model'],
				'option'           => $order_option,
				'quantity'         => $order_product['quantity'],
				'weight' 		   => number_format($this->weight->convert($product_weight_query->row['weight'], $product_weight_query->row['weight_class_id'], $config_weight_class_id),2),
				'weight_class'     => $weight_class_query->row['unit'],
				'price'            => number_format($order_product['price'],0),
				'total'            => $order_product['total'],
				'tax'              => $order_product['tax'],
				'reward'           => $order_product['reward']
			);
		}
		$this->data['weighttot'] = number_format($weighttot,0);
		$this->data['total'] = number_format($order_info['total'],0);


		
		
		################## create shipment ###########
		if ($this->request->post) { 
			

			

			
			
			$zajil_errors = false;
						

			$flag = true;
			$error = "";
			
	

				$totalWeight 	= 0;
				$totalItems 	= 0;


				$zajil_items_counter = 0;

				foreach($this->request->post['zajil_items'] as $key => $value){
				
					$zajil_items_counter++;

					if($value != 0){
						//itrating order items
						foreach($order_products as $item){
				
							if($item['order_product_id'] == $key){
				
								//get weight
								$weight = $this->weight->convert($product_weight_query->row['weight'], $product_weight_query->row['weight_class_id'], $config_weight_class_id);
				
								$weight = ($weight * $item['quantity']);
                                                                
                                                                //length
                                                                $length =  (number_format($item['length'],2) * $item['quantity']);
                                                                
                                                                //width
                                                                $width =  (number_format($item['width'],2) * $item['quantity']);
                                                                
                                                                //height
                                                                $height =  (number_format($item['height'],2) * $item['quantity']);
                                                                
                                                                //product total price
                                                                $totalPrices = $item['price'] * $item['quantity'];
                                                                $productTotalPrice = number_format($totalPrices,2);
                                                                
								$order_product_id = $item['order_product_id'];
								// collect items for zajil
								$zajil_items[]	= array(
                                                                    "description" => $item['name'],
                                                                    "declared_value" => $productTotalPrice,
								    "weight"=> $weight,
                                                                    "height" => $height,
                                                                    "length" => $length,
                                                                    "width" => $width
								);

								$totalWeight 	+= $weight;
								$totalItems 	+= $this->request->post[$order_product_id];
							}
						}
					}
				}

	    $zajil_customer_code=$this->config->get('zajil_customer_code');	
				
		if(isset($this->request->post['zajil_productType'])) {
			$productType= $this->request->post['zajil_productType'];
		}elseif(isset($this->request->get['zajil_productType'])) {
			$productType= $this->request->get['zajil_productType'];
		} 
				
		if(isset($this->request->post['zajil_bookingMode'])) {
			$bookingMode= $this->request->post['zajil_bookingMode'];
		}elseif(isset($this->request->get['zajil_bookingMode'])) {
			$bookingMode= $this->request->get['zajil_bookingMode'];
		} 
		if(isset($this->request->post['zajil_service'])) {
			$service=$this->request->post['zajil_service'];
		}elseif(isset($this->request->get['zajil_service'])) {
			$service=$this->request->get['zajil_service'];
		} 	
			
		
		
	     if(isset($this->request->post['zajil_sender_name'])) {
			$sender_name= $this->request->post['zajil_sender_name'];
		} elseif (isset($this->request->get['zajil_sender_name'])) {
			$sender_name= $this->request->get['zajil_sender_name'];
		} 
		if (isset($this->request->post['zajil_sender_phone'])) {
			$sender_phone= $this->request->post['zajil_sender_phone'];
		} elseif (isset($this->request->get['zajil_sender_phone'])) {
			$sender_phone= $this->request->get['zajil_sender_phone'];
		} 
		
		if (isset($this->request->post['zajil_sender_city'])) {
			$sender_city= $this->request->post['zajil_sender_city'];
		} elseif (isset($this->request->get['zajil_sender_city'])) {
			$sender_city= $this->request->get['zajil_sender_city'];
		} 
		
		if (isset($this->request->post['zajil_sender_address'])) {
	     $sender_address= $this->request->post['zajil_sender_address'];
		} elseif (isset($this->request->get['zajil_sender_address'])) {
			$sender_address= $this->request->get['zajil_sender_address'];
		} 
				
		
		if (isset($this->request->post['zajil_shipment_receiver_name'])) {
			$receiver_name= $this->request->post['zajil_shipment_receiver_name'];
		} elseif (isset($this->request->get['zajil_shipment_receiver_name'])) {
			$receiver_name= $this->request->get['zajil_shipment_receiver_name'];
		} 
		if (isset($this->request->post['zajil_shipment_receiver_phone'])) {
			$receiver_phone= $this->request->post['zajil_shipment_receiver_phone'];
		} elseif (isset($this->request->get['zajil_shipment_receiver_phone'])) {
			$receiver_phone= $this->request->get['zajil_shipment_receiver_phone'];
		} 		
		if (isset($this->request->post['zajil_shipment_receiver_city'])) {
			$reciever_city= $this->request->post['zajil_shipment_receiver_city'];
		} elseif (isset($this->request->get['zajil_shipment_receiver_city'])) {
			$reciever_city= $this->request->get['zajil_shipment_receiver_city'];
		} 
		if (isset($this->request->post['zajil_shipment_receiver_street'])) {
			$receiver_address= $this->request->post['zajil_shipment_receiver_street'];
		} elseif (isset($this->request->get['zajil_shipment_receiver_street'])) {
			$receiver_address= $this->request->get['zajil_shipment_receiver_street'];
		} 
	
		$pkgdesc=($this->request->post['zajil_shipment_description'])?$this->request->post['zajil_shipment_description']:'';
		
			
		if (isset($this->request->post['pieces'])) {
			$pcs= $this->request->post['pieces'];
		} elseif (isset($this->request->get['pieces'])) {
			$pcs=$totalItems;
		}
    $cod_amount = 0;
	if(isset($this->request->post['zajil_shipment_info_cod_amount'])) {
			$cod_amount= $this->request->post['zajil_shipment_info_cod_amount'];
	}
	// the actual total
    $price_value= ($order_info['total'])?$order_info['total']:0;
	if(isset($this->request->post['zajil_shipment_info_custom_amount'])) {
	 $custom_amount= $this->request->post['zajil_shipment_info_custom_amount'];
	}else{
	 $custom_amount= 0;
	}
		
	$priceValue= $price_value + $custom_amount;	
		if(isset($this->request->post['order_weight'])) {
			$weight= $this->request->post['order_weight'];
		}else{
		$weight=$totalWeight;	
			
		}	
			
		$refrence_id=($this->request->post['zajil_shipment_sender_reference'])?$this->request->post['zajil_shipment_sender_reference']:'';
			
		$product_price=round($order_product['total'], 2); 

                $shippingRequest = [
                    "consignments" => [[
                        "customer_code" => $zajil_customer_code,
                        "reference_number" => '',
                        "service_type_id" => $service,
                        "description" => $pkgdesc,
                        "customer_reference_number" => $refrence_id,
                        "weight" => $this->request->post['order_weight'],
                        "declared_value"=> $priceValue,
                        "declared_price"=> $priceValue,
                        "cod_amount" => $cod_amount,
                        "cod_collection_mode" => $this->data['pay_option'],
                        "weight_unit" => $this->request->post['weight_unit'],
                        "num_pieces" => $totalItems,
                        "origin_details" => [
                            "name" => $sender_name,
                            "phone" => $sender_phone,
                            "address_line_1" => $sender_address,
                            "city" => $sender_city,
                            "state" => $sender_city
                        ],
                        "destination_details" => [
                            "name" => $receiver_name,
                            "phone" => $receiver_phone,
                            "address_line_1" => $receiver_address,
                            "city" => $reciever_city,
                            "state" => $reciever_city
                        ],
                        "pieces_detail" => $zajil_items,
                    ]]
                ];

                $baseUrl = $this->model_sale_zajil->getPath();
                $url = $baseUrl . 'softdata';

                //create shipment call
                $object = $this->model_sale_zajil->get_data($url, $shippingRequest);
//		$this->data['error_warning']=$object['Weight'];

			if($object['data'][0]['success'] === true){
			
				$awbno=$object['data'][0]['reference_number'];
                                $awb_print_url = '';
						
				$this->db->query("INSERT INTO ".DB_PREFIX . "zajil SET order_id= '" .$refrence_id. "', awb= '".$awbno. "',awb_print_url= '".$awb_print_url. "'");
			}else{
                            $this->data['error_warning']= "Zajil Error Message :". (!empty($object['data'][0]['message'])) ? $object['data'][0]['message'] : $object['error']['message'];
                            $result_json['success'] = '0';                            
                        }
			
     if($this->config->get('zajil_debug')==1){	
			
				$this->data['error_warning']='<a href="'.$url.'" target="_blank" class="red">Visit API</a>';
			}
			


		//Send data for verification
			
		$shipmenthistory= "Referencer No. ".$awbno." - Order No. ".$this->data['order_id'];
		
		if(isset($this->request->post['zajil_email_customer']) && $this->request->post['zajil_email_customer']=='yes')
						{
						
								$is_email = 1;
						}else{
								$is_email = 0;
						}
						$message = array(
							'notify' => $is_email,
							'comment' => $shipmenthistory
						);
	
	if($object['data'][0]['success'] === true) {
			
			$this->model_sale_zajil->addOrderHistory($this->request->get['order_id'], $message);

//			$this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');
                        
                        $redirectLink = $this->url->link('sale/zajil_create_shipment?order_id='.$this->data['order_id']);
			$result_json['success'] = '1';
			$result_json['redirect'] = '1';                       
			$result_json['to'] = (string) $redirectLink;
			$result_json['success_msg']  = $this->language->get('text_success');	
			$this->response->setOutput(json_encode($result_json));
				
			return;
	

		}else{
			
			if($object == null ){

				
				$this->error['warning'] = $this->language->get('error_cred');

			}else{

				$this->error['warning'] = $this->data['error_warning'];

			}

			
			// $this->data['success_html'] = '';

			$this->session->data['success_html'] = $shipmenthistory;

			$result_json['success'] = '0';
					
			$result_json['errors'] = $this->error;
					
			$this->response->setOutput(json_encode($result_json));
					
			return;
		}
			
		
}


	################## create shipment end ###########
		
		
		$this->data['is_shipment'] = $this->model_sale_zajil->checkAWB($this->request->get['order_id']);	
			
		if (isset($this->session->data['success_html'])) {
			$this->data['success_html'] = $this->session->data['success_html'];

			unset($this->session->data['success_html']);
		} else {
			$this->data['success_html'] = '';
		}
		
		$this->data['productTypes'] = array();
		  
		$this->data['productTypes'][] = array(
			'value' =>'NON-DOCUMENT',
			'text'  => $this->language->get('text_non_document')
		);
		$this->data['productTypes'][] = array(
			'value' =>'Document',
			'text'  => $this->language->get('text_document')
		);
	
		
		
		$this->data['bookingModes'] = array();
		  
		$this->data['bookingModes'][] = array(
			'value' =>'digital',
			'text'  => $this->language->get('text_digital')
		);
		$this->data['bookingModes'][] = array(
			'value' =>'dd',
			'text'  => $this->language->get('text_dd')
		);
		$this->data['bookingModes'][] = array(
			'value' =>'cheque',
			'text'  => $this->language->get('text_cheque')
		);
		$this->data['bookingModes'][] = array(
			'value' =>'cash',
			'text'  => $this->language->get('text_cash')
		);
		$this->data['services'] = array();
		
		if($this->config->get('zajil_available_service_types')){
			$this->data['services'] = $this->config->get('zajil_available_service_types');
		}	
		else{
			$this->data['services'][] = array(
				'value' =>'1',
				'text'  => $this->language->get('text_same_day')
			);
			$this->data['services'][] = array(
				'value' =>'2',
				'text'  => $this->language->get('text_next_day')
			);	
		}
			
		$this->data['cities'] = array();
		  
		$this->data['cities'][] = array(
			'value' =>'Riyadh',
			'text'  => $this->language->get('text_Riyadh')
		);	
		$this->data['cities'][] = array(
			'value' =>'Dammam',
			'text'  => $this->language->get('text_Dammam')
		);
		$this->data['cities'][] = array(
			'value' =>'Hayel',
			'text'  => $this->language->get('text_Hayel')
		);	
		$this->data['cities'][] = array(
			'value' =>'Al hassa',
			'text'  => $this->language->get('text_Al_hassa')
		);
		$this->data['cities'][] = array(
			'value' =>'Madina',
			'text'  => $this->language->get('text_Madina')
		);
		$this->data['cities'][] = array(
			'value' =>'Jeddah',
			'text'  => $this->language->get('text_Jeddah')
		);
		$this->data['cities'][] = array(
			'value' =>'Makkah',
			'text'  => $this->language->get('text_Makkah')
		);
		$this->data['cities'][] = array(
			'value' =>'Taif',
			'text'  => $this->language->get('text_Taif')
		);
		$this->data['cities'][] = array(
			'value' =>'Arar',
			'text'  => $this->language->get('text_Arar')
		);
		$this->data['cities'][] = array(
			'value' =>'Khobar',
			'text'  => $this->language->get('text_Khobar')
		);
		$this->data['cities'][] = array(
			'value' =>'Buraidah',
			'text'  => $this->language->get('text_Buraidah')
		);
		$this->data['cities'][] = array(
			'value' =>'Hafer Albaten',
			'text'  => $this->language->get('text_Hafer_Albaten')
		);
		$this->data['cities'][] = array(
			'value' =>'Jubail',
			'text'  => $this->language->get('text_Jubail')
		);
		$this->data['cities'][] = array(
			'value' =>'Khafji',
			'text'  => $this->language->get('text_Khafji')
		);
		$this->data['cities'][] = array(
			'value' =>'Khamis mushit',
			'text'  => $this->language->get('text_Khamis_mushit')
		);
		$this->data['cities'][] = array(
			'value' =>'Oneza',
			'text'  => $this->language->get('text_Oneza')
		);
		$this->data['cities'][] = array(
			'value' =>'Rass',
			'text'  => $this->language->get('text_Rass')
		);
		$this->data['cities'][] = array(
			'value' =>'Skaka',
			'text'  => $this->language->get('text_Skaka')
		);
		$this->data['cities'][] = array(
			'value' =>'Tabuk',
			'text'  => $this->language->get('text_Tabuk')
		);
		$this->data['cities'][] = array(
			'value' =>'Qatif',
			'text'  => $this->language->get('text_Qatif')
		);
		$this->data['cities'][] = array(
			'value' =>'Jizan',
			'text'  => $this->language->get('text_Jizan')
		);
		$this->data['cities'][] = array(
			'value' =>'Abha',
			'text'  => $this->language->get('text_Abha')
		);
		$this->data['cities'][] = array(
			'value' =>'Yanbu',
			'text'  => $this->language->get('text_Yanbu')
		);
		$this->data['cities'][] = array(
			'value' =>'Methneb',
			'text'  => $this->language->get('text_Methneb')
		);
		$this->data['cities'][] = array(
			'value' =>'AlBadai',
			'text'  => $this->language->get('text_AlBadai')
		);
		$this->data['cities'][] = array(
			'value' =>'AlBukayriyah',
			'text'  => $this->language->get('text_AlBukayriyah')
		);
		$this->data['cities'][] = array(
			'value' =>'Towal',
			'text'  => $this->language->get('text_Towal')
		);
		$this->data['cities'][] = array(
			'value' =>'Samtta',
			'text'  => $this->language->get('text_Samtta')
		);
		$this->data['cities'][] = array(
			'value' =>'Ahad AlMasarihah',
			'text'  => $this->language->get('text_Ahad_AlMasarihah')
		);
		$this->data['cities'][] = array(
			'value' =>'Abo Aresh',
			'text'  => $this->language->get('text_Abo_Aresh')
		);
		$this->data['cities'][] = array(
			'value' =>'Buqayq',
			'text'  => $this->language->get('text_Buqayq')
		);
		$this->data['cities'][] = array(
			'value' =>'Sebya',
			'text'  => $this->language->get('text_Sebya')
		);
		$this->data['cities'][] = array(
			'value' =>'Bish',
			'text'  => $this->language->get('text_Bish')
		);
		$this->data['cities'][] = array(
			'value' =>'Muhayl Aseer',
			'text'  => $this->language->get('text_Muhayl_Aseer')
		);
		$this->data['cities'][] = array(
			'value' =>'Mzahmeya',
			'text'  => $this->language->get('text_Mzahmeya')
		);
		$this->data['cities'][] = array(
			'value' =>'Quweya',
			'text'  => $this->language->get('text_Quweya')
		);
		$this->data['cities'][] = array(
			'value' =>'Dhurma',
			'text'  => $this->language->get('text_Dhurma')
		);
		$this->data['cities'][] = array(
			'value' =>'Laith',
			'text'  => $this->language->get('text_Laith')
		);
		$this->data['cities'][] = array(
			'value' =>'Qunfotha',
			'text'  => $this->language->get('text_Qunfotha')
		);
		$this->data['cities'][] = array(
			'value' =>'Bahra',
			'text'  => $this->language->get('text_Bahra')
		);
		$this->data['cities'][] = array(
			'value' =>'Thwal',
			'text'  => $this->language->get('text_Thwal')
		);
		$this->data['cities'][] = array(
			'value' =>'AlBaha',
			'text'  => $this->language->get('text_AlBaha')
		);
		$this->data['cities'][] = array(
			'value' =>'Majmah',
			'text'  => $this->language->get('text_Majmah')
		);
		$this->data['cities'][] = array(
			'value' =>'Najran',
			'text'  => $this->language->get('text_Najran')
		);
		$this->data['cities'][] = array(
			'value' =>'Bisha',
			'text'  => $this->language->get('text_Bisha')
		);
		$this->data['cities'][] = array(
			'value' =>'Baljurashi',
			'text'  => $this->language->get('text_Baljurashi')
		);
		$this->data['cities'][] = array(
			'value' =>'Namas',
			'text'  => $this->language->get('text_Namas')
		);
		$this->data['cities'][] = array(
			'value' =>'Dawadmi',
			'text'  => $this->language->get('text_Dawadmi')
		);
		$this->data['cities'][] = array(
			'value' =>'Shaqra',
			'text'  => $this->language->get('text_Shaqra')
		);
		$this->data['cities'][] = array(
			'value' =>'Zulfi',
			'text'  => $this->language->get('text_Zulfi')
		);
		$this->data['cities'][] = array(
			'value' =>'Dawmeh Jandal',
			'text'  => $this->language->get('text_Dawmeh_Jandal')
		);
		$this->data['cities'][] = array(
			'value' =>'Qurayat',
			'text'  => $this->language->get('text_Qurayat')
		);
		$this->data['cities'][] = array(
			'value' =>'Turaif',
			'text'  => $this->language->get('text_Turaif')
		);
		$this->data['cities'][] = array(
			'value' =>'Abo Ajram',
			'text'  => $this->language->get('text_Abo_Ajram')
		);
		$this->data['cities'][] = array(
			'value' =>'Ḍuba',
			'text'  => $this->language->get('text_Ḍuba')
		);
		$this->data['cities'][] = array(
			'value' =>'Tayma',
			'text'  => $this->language->get('text_Tayma')
		);
		$this->data['cities'][] = array(
			'value' =>'Rafaha',
			'text'  => $this->language->get('text_Rafaha')
		);
		$this->data['cities'][] = array(
			'value' =>'Sharora',
			'text'  => $this->language->get('text_Sharora')
		);
		$this->data['cities'][] = array(
			'value' =>'Wadi adDawasir',
			'text'  => $this->language->get('text_Wadi_adDawasir')
		);
		$this->data['cities'][] = array(
			'value' =>'AlAflaj',
			'text'  => $this->language->get('text_AlAflaj')
		);
		$this->data['cities'][] = array(
			'value' =>'Kharj',
			'text'  => $this->language->get('text_Kharj')
		);
		$this->data['cities'][] = array(
			'value' =>'AlKhabra',
			'text'  => $this->language->get('text_AlKhabra')
		);
		$this->data['cities'][] = array(
			'value' =>'AdDiriyah',
			'text'  => $this->language->get('text_AdDiriyah')
		);
		$this->data['cities'][] = array(
			'value' =>'Dhaharan',
			'text'  => $this->language->get('text_Dhaharan')
		);
		$this->data['cities'][] = array(
			'value' =>'Uqlat As Suqur',
			'text'  => $this->language->get('text_Uqlat_As_Suqur')
		);
		$this->data['cities'][] = array(
			'value' =>'Dukhnah',
			'text'  => $this->language->get('text_Dukhnah')
		);
		$this->data['cities'][] = array(
			'value' =>'Al Dulaymiyah',
			'text'  => $this->language->get('text_Al_Dulaymiyah')
		);
		$this->data['cities'][] = array(
			'value' =>'An Nabhaniyah',
			'text'  => $this->language->get('text_An_Nabhaniyah')
		);
		$this->data['cities'][] = array(
			'value' =>'Uyun al jawa',
			'text'  => $this->language->get('text_Uyun_al_jawa')
		);
		$this->data['cities'][] = array(
			'value' =>'AdDilam',
			'text'  => $this->language->get('text_AdDilam')
		);
		$this->data['cities'][] = array(
			'value' =>'Hotat Bani Tamim',
			'text'  => $this->language->get('text_Hotat_Bani_Tamim')
		);
		$this->data['cities'][] = array(
			'value' =>'Ar Ruwaidhah',
			'text'  => $this->language->get('text_Ar_Ruwaidhah')
		);
		$this->data['cities'][] = array(
			'value' =>'Huraymila',
			'text'  => $this->language->get('text_Huraymila')
		);
		$this->data['cities'][] = array(
			'value' =>'malham',
			'text'  => $this->language->get('text_malham')
		);
		$this->data['cities'][] = array(
			'value' =>'Ghat',
			'text'  => $this->language->get('text_Ghat')
		);
		$this->data['cities'][] = array(
			'value' =>'Hawtat Sudayr',
			'text'  => $this->language->get('text_Hawtat_Sudayr')
		);
		$this->data['cities'][] = array(
			'value' =>'Sajir',
			'text'  => $this->language->get('text_Sajir')
		);
		$this->data['cities'][] = array(
			'value' =>'Al Hulwah',
			'text'  => $this->language->get('text_Al_Hulwah')
		);
		$this->data['cities'][] = array(
			'value' =>'Al-Badie Al-Shamali',
			'text'  => $this->language->get('text_Al-Badie_Al-Shamali')
		);
		$this->data['cities'][] = array(
			'value' =>'As Sulayyil',
			'text'  => $this->language->get('text_As_Sulayyil')
		);	
       $this->data['cities'][] = array(
			'value' =>'Al Hariq',
			'text'  => $this->language->get('text_Al_Hariq')
		);
      $this->data['cities'][] = array(
			'value' =>'Salbukh',
			'text'  => $this->language->get('text_Salbukh')
		);
     $this->data['cities'][] = array(
			'value' =>'Rumah',
			'text'  => $this->language->get('text_Rumah')
		);
		$this->data['cities'][] = array(
			'value' =>'Riyadh AlKhabra',
			'text'  => $this->language->get('text_Riyadh_AlKhabra')
		);
       $this->data['cities'][] = array(
			'value' =>'Layla',
			'text'  => $this->language->get('text_Layla')
		);
		$this->data['cities'][] = array(
			'value' =>'AlJumoom',
			'text'  => $this->language->get('text_AlJumoom')
		);
       $this->data['cities'][] = array(
			'value' =>'Alhawiyah',
			'text'  => $this->language->get('text_Alhawiyah')
		);
      $this->data['cities'][] = array(
			'value' =>'Badr',
			'text'  => $this->language->get('text_Badr')
		);
		$this->data['cities'][] = array(
			'value' =>'Turabah',
			'text'  => $this->language->get('text_Turabah')
		);
		$this->data['cities'][] = array(
			'value' =>'Rabigh',
			'text'  => $this->language->get('text_Rabigh')
		);
       $this->data['cities'][] = array(
			'value' =>'Ummluj',
			'text'  => $this->language->get('text_Ummluj')
		);

     $this->data['cities'][] = array(
			'value' =>'AlUla',
			'text'  => $this->language->get('text_AlUla')
		);

       $this->data['cities'][] = array(
			'value' =>'haql',
			'text'  => $this->language->get('text_haql')
		);
		$this->data['cities'][] = array(
			'value' =>'AlQurayyat',
			'text'  => $this->language->get('text_AlQurayyat')
		);
		$this->data['cities'][] = array(
			'value' =>'zalom',
			'text'  => $this->language->get('text_zalom')
		);
       $this->data['cities'][] = array(
			'value' =>'Ranyah',
			'text'  => $this->language->get('text_Ranyah')
		);
		$this->data['cities'][] = array(
			'value' =>'tabarjal',
			'text'  => $this->language->get('text_tabarjal')
		);
		$this->data['cities'][] = array(
			'value' =>'Afïf',
			'text'  => $this->language->get('text_Afïf')
		);
		$this->data['cities'][] = array(
			'value' =>'Ain Dar',
			'text'  => $this->language->get('text_Ain_Dar')
		);

       $this->data['cities'][] = array(
			'value' =>'Anak',
			'text'  => $this->language->get('text_Anak')
		);
		$this->data['cities'][] = array(
			'value' =>'Nairiyah',
			'text'  => $this->language->get('text_Nairiyah')
		);
		$this->data['cities'][] = array(
			'value' =>'Qarya Al Uliya',
			'text'  => $this->language->get('text_Qarya_Al_Uliya')
		);
       $this->data['cities'][] = array(
			'value' =>'Ras Tannurah',
			'text'  => $this->language->get('text_Ras_Tannurah')
		);
		$this->data['cities'][] = array(
			'value' =>'Uthmaniyah',
			'text'  => $this->language->get('text_Uthmaniyah')
		);
		$this->data['cities'][] = array(
			'value' =>'Udhayliyah',
			'text'  => $this->language->get('text_Udhayliyah')
		);
		$this->data['cities'][] = array(
			'value' =>'AlUyun',
			'text'  => $this->language->get('text_AlUyun')
		);
		$this->data['cities'][] = array(
			'value' =>'Athuqbah',
			'text'  => $this->language->get('text_Athuqbah')
		);

      $this->data['cities'][] = array(
			'value' =>'AlAwamiyah',
			'text'  => $this->language->get('text_AlAwamiyah')
		);
      $this->data['cities'][] = array(
			'value' =>'AlMubarraz',
			'text'  => $this->language->get('text_AlMubarraz')
		);
   $this->data['cities'][] = array(
			'value' =>'Qaisumah',
			'text'  => $this->language->get('text_Qaisumah')
		);
		$this->data['cities'][] = array(
			'value' =>'Tathlith',
			'text'  => $this->language->get('text_Tathlith')
		);

      $this->data['cities'][] = array(
			'value' =>'Tarut',
			'text'  => $this->language->get('text_Tarut')
		);
    $this->data['cities'][] = array(
			'value' =>'Saihat',
			'text'  => $this->language->get('text_Saihat')
		);
     $this->data['cities'][] = array(
			'value' =>'Safwa',
			'text'  => $this->language->get('text_Safwa')
		);
      $this->data['cities'][] = array(
			'value' =>'Hofuf',
			'text'  => $this->language->get('text_Hofuf')
		);
     $this->data['cities'][] = array(
			'value' =>'Al Jafr',
			'text'  => $this->language->get('text_Al_Jafr')
		);
		$this->data['cities'][] = array(
			'value' =>'Batha',
			'text'  => $this->language->get('text_Batha')
		);
		$this->data['cities'][] = array(
			'value' =>'Salwa/ hassa',
			'text'  => $this->language->get('text_Salwa_hassa')
		);
      $this->data['cities'][] = array(
			'value' =>'AlQudaih',
			'text'  => $this->language->get('text_AlQudaih')
		);
 $this->data['cities'][] = array(
			'value' =>'Ahad Rafidah',
			'text'  => $this->language->get('text_Ahad_Rafidah')
		);
		$this->data['cities'][] = array(
			'value' =>'Sabt AlUlayah',
			'text'  => $this->language->get('text_Sabt_AlUlayah')
		);
		$this->data['cities'][] = array(
			'value' =>'AlKhurmah',
			'text'  => $this->language->get('text_AlKhurmah')
		);
     $this->data['cities'][] = array(
			'value' =>'AlMakhwah',
			'text'  => $this->language->get('text_AlMakhwah')
		);

    $this->data['cities'][] = array(
			'value' =>'Ḍamad',
			'text'  => $this->language->get('text_Ḍamad')
		);

     $this->data['cities'][] = array(
			'value' =>'Dhahran Al Janub',
			'text'  => $this->language->get('text_Dhahran_Al_Janub')
		);
       $this->data['cities'][] = array(
			'value' =>'Bariq',
			'text'  => $this->language->get('text_Bariq')
		);
      $this->data['cities'][] = array(
			'value' =>'Jash',
			'text'  => $this->language->get('text_Jash')
		);
		$this->data['cities'][] = array(
			'value' =>'Majardah',
			'text'  => $this->language->get('text_Majardah')
		);
		$this->data['cities'][] = array(
			'value' =>'Nakeea',
			'text'  => $this->language->get('text_Nakeea')
		);
		$this->data['cities'][] = array(
			'value' =>'Darb/abha',
			'text'  => $this->language->get('text_Darb_abha')
		);
		$this->data['cities'][] = array(
			'value' =>'Rijal Alma',
			'text'  => $this->language->get('text_Rijal_Alma')
		);
		$this->data['cities'][] = array(
			'value' =>'Sarat Abideh',
			'text'  => $this->language->get('text_Sarat_Abideh')
		);
		$this->data['cities'][] = array(
			'value' =>'Wadi Bin Hashbal',
			'text'  => $this->language->get('text_Wadi_Bin_Hashbal')
		);
		$this->data['cities'][] = array(
			'value' =>'AlQuz',
			'text'  => $this->language->get('text_AlQuz')
		);
		$this->data['cities'][] = array(
			'value' =>'Taraf',
			'text'  => $this->language->get('text_Taraf')
		);
		$this->data['cities'][] = array(
			'value' =>'uthal',
			'text'  => $this->language->get('text_uthal')
		);
		$this->data['cities'][] = array(
			'value' =>'AlWajh',
			'text'  => $this->language->get('text_AlWajh')
		);


		  
		 
		  // ATH was here at 10:00 pm 2-4-2019
			$this->base = 'common/base';
		
			$this->template = 'sale/zajil_create_shipment.expand';
			
			$this->response->setOutput($this->render_ecwig());
			return;

			

		} else {

			

			$this->language->load('error/not_found');

			$this->document->setTitle($this->language->get('heading_title'));

			$this->data['heading_title'] = $this->language->get('heading_title');

			$this->data['text_not_found'] = $this->language->get('text_not_found');

			$this->data['breadcrumbs'] = array();

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], true)
			);

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('error/not_found', 'user_token=' . $this->session->data['user_token'], true)
			);

			// ATH was here at 10:02 pm 2-4-2019
      $this->base = "common/base";
			
			$this->template = 'error/not_found.expand';

			$this->response->setOutput($this->render_ecwig());

			// $this->data['header'] = $this->load->controller('common/header');
			// $this->data['column_left'] = $this->load->controller('common/column_left');
			// $this->data['footer'] = $this->load->controller('common/footer');
		
			// $this->response->setOutput($this->load->view('error/not_found', $this->data));

		}
	}
	
	public function lable()
	{
		$this->load->model('sale/order');
		$this->load->model('sale/zajil');
	
		
		if (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
		} else {
			$order_id = 0;
		}
		$order_info = $this->model_sale_order->getOrder($order_id);
		
		if($order_id)
		{
		    $referenceNumber = $this->model_sale_zajil->getZajiReferenceNumber($this->request->get['order_id']);
			
			if($referenceNumber){
                            
                        $urlLable = $this->model_sale_zajil->getPath() . "shippinglabel/link?reference_number=$referenceNumber";
                        
                        $filepath = $this->model_sale_zajil->connectToTrackAndPrint($urlLable)['data']['url'];
                        header('Location:'.$filepath);
			exit;

}else{
$this->error['warning'] = $this->language->get('error_shipment_empty');
}
}
else{
$this->error['warning'] = $this->language->get('error_no_order');
}
}

public function tracking() {
    
        $this->load->model('sale/order');
        $this->load->model('sale/zajil');
        
        $this->language->load('sale/zajil');
        
        $this->document->setTitle($this->language->get('heading_title'));
        
        $referenceNumber = $this->model_sale_zajil->getZajiReferenceNumber($this->request->get['order_id']);
        
        $urlTrack = $this->model_sale_zajil->getPath() . "track?reference_number=$referenceNumber";
        
        $trackData = $this->model_sale_zajil->connectToTrackAndPrint($urlTrack)['events'];
        
        $this->data['trakingEvents'] = $trackData;
        $this->data['order_id'] = $this->request->get['order_id'];
        
	$this->base = 'common/base';

        $this->template = 'sale/zajil_shipment_tracking.expand';
        $this->response->setOutput($this->render_ecwig());
        return;
    }

}
