<?php 
class ControllerAccountReturn extends Controller { 
	private $error = array();
	
	public function index() {

		$return_type = $this->data['return_type'] = $this->config->get('config_return_type') ? $this->config->get('config_return_type') : "return";

    	if (!$this->customer->isLogged()) {
      		$this->session->data['redirect'] = $this->url->link('account/return', '', 'SSL');

	  		$this->redirect($this->url->link('account/login', '', 'SSL'));
        }
        
        $this->data['taps'] = $this->getChild('account/taps/renderTaps', 'account-return');
 
    	$this->language->load_json('account/return', $this->identityAllowed());

    	$this->document->setTitle($this->language->get('heading_title_'.$return_type));
		$this->document->addScript('expandish/view/javascript/jquery/colorbox/jquery.colorbox-min.js');
		$this->document->addStyle('expandish/view/javascript/jquery/colorbox/colorbox.css');
						
      	$this->data['breadcrumbs'] = array();

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),        	
        	'separator' => false
      	); 

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_account'),
			'href'      => $this->url->link('account/account', '', 'SSL'),        	
        	'separator' => $this->language->get('text_separator')
      	);
		
		$url = '';
		
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
				
      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('heading_title_'.$return_type),
			'href'      => $this->url->link('account/return', $url, 'SSL'),        	
        	'separator' => $this->language->get('text_separator')
      	);


		$this->load->model('account/return');
		
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
		
		$this->data['returns'] = array();
		
		$return_total = $this->model_account_return->getTotalReturns();
		
		$results = $this->model_account_return->getReturns(($page - 1) * 10, 10);
		
		foreach ($results as $result) {
			$this->data['returns'][] = array(
				'return_id'  => $result['return_id'],
				'order_id'   => $result['order_id'],
				'name'       => $result['firstname'] . ' ' . $result['lastname'],
				'status'     => $result['status'],
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'href'       => $this->url->link('account/return/info', 'return_id=' . $result['return_id'] . $url, 'SSL')
			);
		}

		$pagination = new Pagination();
		$pagination->total = $return_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_catalog_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('account/history', 'page={page}', 'SSL');
		
		$this->data['pagination'] = $pagination->render();

		$this->data['continue'] = $this->url->link('account/account', '', 'SSL');
		
        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/account/return_list.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/account/return_list.expand';
        }
        else {
            $this->template = $this->config->get('config_template') . '/template/account/return_list.expand';
        }
		
		$this->children = array(
			'common/footer',
			'common/header'
        );
        
        if ($this->identityAllowed()) {            
            // This is to handle new template structure using extend
            $this->data['include_page'] = 'return_list.expand';
            if(USES_TWIG_EXTENDS == 1)
                $this->template = 'default/template/account/layout_extend.expand';
            else
                $this->template = 'default/template/account/layout_default.expand';
        }
						
		$this->response->setOutput($this->render_ecwig());
	}
	
	public function info() {

		$return_type = $this->data['return_type'] = $this->config->get('config_return_type') ? $this->config->get('config_return_type') : "return";

		$this->language->load_json('account/return', $this->identityAllowed());
		
		if (isset($this->request->get['return_id'])) {
			$return_id = $this->request->get['return_id'];
		} else {
			$return_id = 0;
		}
    	
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/return/info', 'return_id=' . $return_id, 'SSL');
			
			$this->redirect($this->url->link('account/login', '', 'SSL'));
    	}
		
		$this->load->model('account/return');
						
		$return_info = $this->model_account_return->getReturn($return_id);

		if ($return_info) {
			$this->document->setTitle($this->language->get('text_return'));

			$this->data['breadcrumbs'] = array();
	
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home', '', 'SSL'),
				'separator' => false
			);
	
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_account'),
				'href'      => $this->url->link('account/account', '', 'SSL'),
				'separator' => $this->language->get('text_separator')
			);
			
			$url = '';
			
			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}	
					
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('heading_title_'.$return_type),
				'href'      => $this->url->link('account/return', $url, 'SSL'),
				'separator' => $this->language->get('text_separator')
			);
						
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_return'),
				'href'      => $this->url->link('account/return/info', 'return_id=' . $this->request->get['return_id'] . $url, 'SSL'),
				'separator' => $this->language->get('text_separator')
			);			
			

			$this->data['return_id'] = $return_info['return_id'];
			$this->data['order_id'] = $return_info['order_id'];
			$this->data['date_ordered'] = date($this->language->get('date_format_short'), strtotime($return_info['date_ordered']));
			$this->data['date_added'] = date($this->language->get('date_format_short'), strtotime($return_info['date_added']));
			$this->data['firstname'] = $return_info['firstname'];
			$this->data['lastname'] = $return_info['lastname'];
			$this->data['email'] = $return_info['email'];
			$this->data['telephone'] = $return_info['telephone'];						
			$this->data['product'] = $return_info['product'];
			$this->data['model'] = $return_info['model'];
			$this->data['quantity'] = $return_info['quantity'];
			$this->data['reason'] = $return_info['reason'];
			$this->data['opened'] = $return_info['opened'] ? $this->language->get('text_yes') : $this->language->get('text_no');
			$this->data['comment'] = nl2br($return_info['comment']);
			$this->data['action'] = $return_info['action'];
			
			$this->data['return_products'] = $return_info['products'];
			$this->data['product_id'] = $return_info['product_id'];
						
			$this->data['histories'] = array();
			
			$results = $this->model_account_return->getReturnHistories($this->request->get['return_id']);
			
      		foreach ($results as $result) {
        		$this->data['histories'][] = array(
          			'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
          			'status'     => $result['status'],
          			'comment'    => nl2br($result['comment'])
        		);
      		}
			
			$this->data['continue'] = $this->url->link('account/return', $url, 'SSL');

            if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/account/return_info.expand')) {
                $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/account/return_info.expand';
            }
            else {
                $this->template = $this->config->get('config_template') . '/template/account/return_info.expand';
            }
			
			$this->children = array(
			'common/footer',
			'common/header'
		);
									
			$this->response->setOutput($this->render_ecwig());
		} else {
			$this->document->setTitle($this->language->get('text_return'));
						
			$this->data['breadcrumbs'] = array();

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home'),
				'separator' => false
			);

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_account'),
				'href'      => $this->url->link('account/account', '', 'SSL'),
				'separator' => $this->language->get('text_separator')
			);
			
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('heading_title_'.$return_type),
				'href'      => $this->url->link('account/return', '', 'SSL'),
				'separator' => $this->language->get('text_separator')
			);
			
			$url = '';
			
			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
									
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_return'),
				'href'      => $this->url->link('account/return/info', 'return_id=' . $return_id . $url, 'SSL'),
				'separator' => $this->language->get('text_separator')
			);


			$this->data['continue'] = $this->url->link('account/return', '', 'SSL');

            if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/error/not_found.expand')) {
                $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/error/not_found.expand';
            }
            else {
                $this->template = $this->config->get('config_template') . '/template/error/not_found.expand';
            }
			
			$this->children = array(
			'common/footer',
			'common/header'
		);
						
			$this->response->setOutput($this->render_ecwig());
		}
	}
		
	public function insert() {
		// empty return errors
		unset($this->session->data['return_errors']);

		$return_type = $this->data['return_type'] = $this->config->get('config_return_type') ? $this->config->get('config_return_type') : "return";

		$this->language->load_json('account/return', $this->identityAllowed());

        $this->load->model('account/return');
		$this->load->model('account/order');
		$this->load->model('catalog/product');
		$this->load->model('localisation/language');

        $freezeStatus = $this->config->get('config_return_freeze_statuses');
        
        $order_info =  $this->model_account_order->getOrder((isset($this->request->get['order_id'])) ? $this->request->get['order_id']: $this->request->post['order_id']);

        if (in_array($order_info['order_status_id'], $freezeStatus)) {
			$this->session->data['return_errors'][] = $this->language->get('error_status');
            $this->redirect($this->url->link(
                'account/order/info',
                'order_id=' . $this->request->get['order_id'],
                'SSL'
            ));
        }

		$quantity_product_update_status_selector = $this->config->get('product_quantity_update_status_selector');
		$config_return_status_id = $this->config->get('config_return_status_id');
    	
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			
			$return_limit = (int)$this->config->get('config_return_limit');
			
			if ($return_limit !== 0) {
				
				$results = $this->model_account_order->getOrderHistories($order_info['order_id']);
				$result = end($results);
				$return_unit = $this->config->get('config_return_limit_unit') ? $this->config->get('config_return_limit_unit') : "day";
				
				$return_limit_date = strtotime(
					'+' . $return_limit . ' ' . $return_unit,
                    strtotime($result['date_added'])
                );
				$today = time();
				if($today > $return_limit_date) {
					// display Errors
					$this->session->data['return_errors'][] = $this->language->get('error_limit');

					$this->redirect($this->url->link(
                        'account/order/info',
                        'order_id=' . $order_info['order_id'],
                        'SSL'
                    ));
                }
			}
			
			if($quantity_product_update_status_selector ==  $config_return_status_id){ 
				$this->model_catalog_product->addQty($this->request->post['product_id'], $this->request->post['quantity']);
				$this->request->post['is_product_quantity_added'] = '1' ;
			}
			
			$lang_id = $this->model_localisation_language->getLanguageByCode($this->config->get('config_language'))['language_id'];
			//$this->request->post['product_id'] = $this->model_catalog_product->getProductByName($this->request->post['product'] , $lang_id)->row['product_id'];
			$return_id = $this->model_account_return->addReturn($this->request->post);
					
			$data['notification_module']="returns";
			$data['notification_module_code']="return_new";
			$data['notification_module_id']=$return_id;
			$this->notifications->addAdminNotification($data);

			$this->redirect($this->url->link('account/return/success', '', 'SSL'));
    	} 
							
		$this->document->setTitle($this->language->get('heading_title_'.$return_type));
		
      	$this->data['breadcrumbs'] = array();

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
        	'separator' => false
      	); 
		
      	$this->data['breadcrumbs'][] = array(       	
        	'text'      => $this->language->get('text_account'),
			'href'      => $this->url->link('account/account', '', 'SSL'),
        	'separator' => $this->language->get('text_separator')
      	);
		
      	$this->data['breadcrumbs'][] = array(       	
        	'text'      => $this->language->get('heading_title_'.$return_type),
			'href'      => $this->url->link('account/return/insert', '', 'SSL'),
        	'separator' => $this->language->get('text_separator')
      	);
		

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}
		
		if (isset($this->error['order_id'])) {
			$this->data['error_order_id'] = $this->error['order_id'];
		} else {
			$this->data['error_order_id'] = '';
		}
				
		if (isset($this->error['firstname'])) {
			$this->data['error_firstname'] = $this->error['firstname'];
		} else {
			$this->data['error_firstname'] = '';
		}	
		
		if (isset($this->error['lastname'])) {
			$this->data['error_lastname'] = $this->error['lastname'];
		} else {
			$this->data['error_lastname'] = '';
		}		
	
		if (isset($this->error['email'])) {
			$this->data['error_email'] = $this->error['email'];
		} else {
			$this->data['error_email'] = '';
		}
		
		if (isset($this->error['telephone'])) {
			$this->data['error_telephone'] = $this->error['telephone'];
		} else {
			$this->data['error_telephone'] = '';
		}
				
		if (isset($this->error['product'])) {
			$this->data['error_product'] = $this->error['product'];
		} else {
			$this->data['error_product'] = '';
		}
		
		if (isset($this->error['model'])) {
			$this->data['error_model'] = $this->error['model'];
		} else {
			$this->data['error_model'] = '';
		}
						
		if (isset($this->error['reason'])) {
			$this->data['error_reason'] = $this->error['reason'];
		} else {
			$this->data['error_reason'] = '';
		}
		
 		if (isset($this->error['captcha'])) {
			$this->data['error_captcha'] = $this->error['captcha'];
		} else {
			$this->data['error_captcha'] = '';
		}	

		$this->data['action'] = $this->url->link('account/return/insert', '', 'SSL');
	
		$results = $this->model_account_order->getOrderHistories($this->request->get['order_id']);

		$result = end($results);

		// Get the last order history
		$return_type = $this->data['return_type'] = $this->config->get('config_return_type') ? $this->config->get('config_return_type') : "return";
		$complete_status_id = $this->config->get('config_return_status_id');
			
		if( 
			($return_type == 'return' && $result['order_status_id'] == $complete_status_id)
			|| 
			($return_type == 'cancel' && $result['order_status_id'] != $complete_status_id)
		){

        	// $return_limit = $this->config->get('config_return_limit') ? $this->config->get('config_return_limit') : "";
			$return_limit = (int)$this->config->get('config_return_limit');

			if ($return_limit !== 0) {
				$return_unit = $this->config->get('config_return_limit_unit') ? $this->config->get('config_return_limit_unit') : "day";

				$return_limit_date = strtotime(
					'+' . $return_limit . ' ' . $return_unit,
                    strtotime($result['date_added'])
                );

				$today = time();

				if($today > $return_limit_date) {
					// display Errors
					$this->session->data['return_errors'][] = $this->language->get('error_limit');
					
					$this->redirect($this->url->link(
                        'account/order/info',
                        'order_id=' . $this->request->get['order_id'],
                        'SSL'
                    ));
                }
			} else {
				// display Errors
				$this->session->data['return_errors'][] = $this->language->get('error_status');

                $this->redirect($this->url->link(
                    'account/order/info',
                    'order_id=' . $this->request->get['order_id'],
                    'SSL'
                ));
            }
		}
		
		$this->load->model('catalog/product');
		
		if (isset($this->request->get['product_id'])) {
			$product_info = $this->model_catalog_product->getProduct($this->request->get['product_id']);
		}
		
    	if (isset($this->request->post['order_id'])) {
      		$this->data['order_id'] = $this->request->post['order_id']; 	
		} elseif (!empty($order_info)) {
			$this->data['order_id'] = $order_info['order_id'];
		} else {
      		$this->data['order_id'] = ''; 
    	}
				
    	if (isset($this->request->post['date_ordered'])) {
      		$this->data['date_ordered'] = $this->request->post['date_ordered']; 	
		} elseif (!empty($order_info)) {
			$this->data['date_ordered'] = date('Y-m-d', strtotime($order_info['date_added']));
		} else {
      		$this->data['date_ordered'] = '';
    	}
				
		if (isset($this->request->post['firstname'])) {
    		$this->data['firstname'] = $this->request->post['firstname'];
		} elseif (!empty($order_info)) {
			$this->data['firstname'] = $order_info['firstname'];	
		} else {
			$this->data['firstname'] = $this->customer->getFirstName();
		}

		if (isset($this->request->post['lastname'])) {
    		$this->data['lastname'] = $this->request->post['lastname'];
		} elseif (!empty($order_info)) {
			$this->data['lastname'] = $order_info['lastname'];			
		} else {
			$this->data['lastname'] = $this->customer->getLastName();
		}
		
		if (isset($this->request->post['email'])) {
    		$this->data['email'] = $this->request->post['email'];
		} elseif (!empty($order_info)) {
			$this->data['email'] = $order_info['email'];				
		} else {
			$this->data['email'] = $this->customer->getEmail();
		}
		
		if (isset($this->request->post['telephone'])) {
    		$this->data['telephone'] = $this->request->post['telephone'];
		} elseif (!empty($order_info)) {
			$this->data['telephone'] = $order_info['telephone'];				
		} else {
			$this->data['telephone'] = $this->customer->getTelephone();
		}
		if (isset($this->request->post['product_id'])) {
			$this->data['product_id'] = $this->request->post['product_id']; 	
		} elseif (!empty($product_info)) {
			$this->data['product_id'] = $product_info['product_id'];
		} else {
			$this->data['product_id'] = $this->request->get['product_id']; 
		}
		if (isset($this->request->post['product'])) {
    		$this->data['product'] = $this->request->post['product'];
		} elseif (!empty($product_info)) {
			$this->data['product'] = $product_info['name'];				
		} else {
			$this->data['product'] = '';
		}
		
		if (isset($this->request->post['model'])) {
    		$this->data['model'] = $this->request->post['model'];
		} elseif (!empty($product_info)) {
			$this->data['model'] = $product_info['model'];				
		} else {
			$this->data['model'] = '';
		}
			
		if (isset($this->request->post['quantity'])) {
    		$this->data['quantity'] = $this->request->post['quantity'];
		} else {
			$this->data['quantity'] = 1;
		}	
				
		if (isset($this->request->post['opened'])) {
    		$this->data['opened'] = $this->request->post['opened'];
		} else {
			$this->data['opened'] = false;
		}	
		
		if (isset($this->request->post['return_reason_id'])) {
    		$this->data['return_reason_id'] = $this->request->post['return_reason_id'];
		} else {
			$this->data['return_reason_id'] = '';
		}	
														
		$this->load->model('localisation/return_reason');
		
    	$this->data['return_reasons'] = $this->model_localisation_return_reason->getReturnReasons();
		
		if (isset($this->request->post['comment'])) {
    		$this->data['comment'] = $this->request->post['comment'];
		} else {
			$this->data['comment'] = '';
		}	
		
		if ($this->config->get('config_return_id')) {
			$this->load->model('catalog/information');
			
			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_return_id'));
			
			if ($information_info) {
				$this->data['text_agree'] = sprintf($this->language->get('text_agree'), $this->url->link('information/information/info', 'information_id=' . $this->config->get('config_return_id'), 'SSL'), $information_info['title'], $information_info['title']);
			} else {
				$this->data['text_agree'] = '';
			}
		} else {
			$this->data['text_agree'] = '';
		}
		
		if (isset($this->request->post['agree'])) {
      		$this->data['agree'] = $this->request->post['agree'];
		} else {
			$this->data['agree'] = false;
		}

		$this->data['back'] = $this->url->link('account/account', '', 'SSL');
				
        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/account/return_form.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/account/return_form.expand';
        }
        else {
            $this->template = $this->config->get('config_template') . '/template/account/return_form.expand';
        }
		
		$this->children = array(
			'common/footer',
			'common/header'
		);
				
		$this->response->setOutput($this->render_ecwig());
  	}
	
  	public function success() {
		$this->language->load_json('account/return', $this->identityAllowed());

		$this->document->setTitle($this->language->get('heading_title')); 
      
	  	$this->data['breadcrumbs'] = array();

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
        	'separator' => false
      	);

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('account/return', '', 'SSL'),
        	'separator' => $this->language->get('text_separator')
      	);	

	
    	$this->data['continue'] = $this->url->link('common/home');

        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/common/success.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/common/success.expand';
        }
        else {
            $this->template = $this->config->get('config_template') . '/template/common/success.expand';
        }
		
		$this->children = array(
			'common/footer',
			'common/header'
		);
				
 		$this->response->setOutput($this->render_ecwig());
	}
		
  	protected function validate() {
    	if (!$this->request->post['order_id']) {
      		$this->error['order_id'] = $this->language->get('error_order_id');
    	}
		
		if ((utf8_strlen($this->request->post['firstname']) < 1) || (utf8_strlen($this->request->post['firstname']) > 32)) {
      		$this->error['firstname'] = $this->language->get('error_firstname');
    	}

    	if ((utf8_strlen($this->request->post['lastname']) < 1) || (utf8_strlen($this->request->post['lastname']) > 32)) {
      		$this->error['lastname'] = $this->language->get('error_lastname');
    	}

    	if ((utf8_strlen($this->request->post['email']) > 96) || !preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $this->request->post['email'])) {
      		$this->error['email'] = $this->language->get('error_email');
    	}
		
    	if ((utf8_strlen($this->request->post['telephone']) < 3) || (utf8_strlen($this->request->post['telephone']) > 32)) {
      		$this->error['telephone'] = $this->language->get('error_telephone');
    	}		
		
		if ((utf8_strlen($this->request->post['product']) < 1) || (utf8_strlen($this->request->post['product']) > 255)) {
			$this->error['product'] = $this->language->get('error_product');
		}	
		
		if ((utf8_strlen($this->request->post['model']) < 1) || (utf8_strlen($this->request->post['model']) > 64)) {
			$this->error['model'] = $this->language->get('error_model');
		}							

		if (empty($this->request->post['return_reason_id'])) {
			$this->error['reason'] = $this->language->get('error_reason');
		}	
				
    	if (empty($this->session->data['recaptcha']) || ($this->session->data['recaptcha'] != $this->request->post['captcha'])) {
      		$this->error['captcha'] = $this->language->get('error_captcha');
    	}
		
		if ($this->config->get('config_return_id')) {
			$this->load->model('catalog/information');
			
			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_return_id'));
			
			if ($information_info && !isset($this->request->post['agree'])) {
      			$this->error['warning'] = sprintf($this->language->get('error_agree'), $information_info['title']);
			}
		}

		if (!$this->error) {
      		return true;
    	}

		$this->session->data['recaptcha'] = '';

		return false;
  	}
	
    public function identityAllowed()
    {
        return defined('LOGIN_MODE') && LOGIN_MODE === 'identity' && $this->identity->isStoreOnWhiteList();
    }
}
?>
