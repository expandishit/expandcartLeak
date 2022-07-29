<?php 
class ControllerAccountWishList extends Controller {

	public $wishlist = [];
	 
	public function index() {

    	if (!$this->customer->isLogged()) {
	  		$this->session->data['redirect'] = $this->url->link('account/wishlist', '', 'SSL');

	  		$this->redirect($this->url->link('account/login', '', 'SSL')); 
    	}   
         	
        $this->data['taps'] = $this->getChild('account/taps/renderTaps', 'account-wishlist');
        
		$this->language->load_json('account/wishlist', $this->identityAllowed());
		
		$this->load->model('catalog/product');
		
		$this->load->model('tool/image');
		$this->load->model('account/wishlist');
		$isCustomerAllowedToViewPrice = $this->customer->isCustomerAllowedToViewPrice();

		$this->wishlist = $this->model_account_wishlist->getWishlist($this->session->data['customer_id']);
		if (isset($this->request->get['remove'])) {
			$key = array_search($this->request->get['remove'], $this->wishlist);
			if ($key !== false) {
				unset($this->wishlist[$key]);
				$this->wishlist = serialize($this->wishlist);
				$this->model_account_wishlist->UpdateWishlist($this->wishlist,$this->session->data['customer_id']);
				unset($this->session->data['wishlist'][$key]);
                $this->customer->updateActs();
			}
			$this->wishlist = $this->model_account_wishlist->getWishlist($this->session->data['customer_id']);
			$this->session->data['success'] = $this->language->get('text_remove');
		
			$this->redirect($this->url->link('account/wishlist'));
		}
						
		$this->document->setTitle($this->language->get('heading_title'));	
      	
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
        	'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('account/wishlist'),
        	'separator' => $this->language->get('text_separator')
      	);

		
		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];
			
			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}
							
		$this->data['products'] = array();

		//Login Display Prices
		$config_customer_price = $this->config->get('config_customer_price');
		$this->wishlist = $this->model_account_wishlist->getWishlist($this->session->data['customer_id']);

		foreach ($this->wishlist as $key => $value) {
			 $product_info = $this->model_catalog_product->getProduct($value);
			if ($product_info) { 
				if ($product_info['image']) {
					$image = $this->model_tool_image->resize($product_info['image'], $this->config->get('config_image_wishlist_width'), $this->config->get('config_image_wishlist_height'));
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
				///check permissions to view Add to Cart Button and view products
				$this->data['viewAddToCart'] = true;
				$hidCartConfig = $this->config->get('config_hide_add_to_cart');		
				if(($product_info['quantity'] <=0 && $hidCartConfig) || !$this->customer->isCustomerAllowedToAdd_cart )
				{
					$this->data['viewAddToCart'] = false;
				}	
				$price = false;		
				if ($isCustomerAllowedToViewPrice) {
					$price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')));
				}
				
				$special = false;
				if ((float)$product_info['special'] && $isCustomerAllowedToViewPrice) {
					$special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')));
				}
																			
				$this->data['products'][] = array(
					'product_id' => $product_info['product_id'],
					'thumb'      => $image,
					'name'       => $product_info['name'],
					'model'      => $product_info['model'],
					'rating'	 => (int)($product_info['rating'] ?: 0),
					'stock'      => $stock,
					'price'      => $price,		
					'special'    => $special,
					'href'       => $this->url->link('product/product', 'product_id=' . $product_info['product_id']),
					'remove'     => $this->url->link('account/wishlist', 'remove=' . $product_info['product_id'])
				);
			} else {
				unset($this->wishlist[$key]);
			}
		}	
		$this->session->data['wishlist'] = $this->model_account_wishlist->getWishlist($this->session->data['customer_id']);

		$this->data['continue'] = $this->url->link('account/account', '', 'SSL');
		
        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/account/wishlist.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/account/wishlist.expand';
        }
        else {
            $this->template = $this->config->get('config_template') . '/template/account/wishlist.expand';
        }
		
		$this->children = array(
			'common/footer',
			'common/header'
        );
        
        if ($this->identityAllowed()) {
            // This is to handle new template structure using extend
            $this->data['include_page'] = 'wishlist.expand';
            if(USES_TWIG_EXTENDS == 1)
                $this->template = 'default/template/account/layout_extend.expand';
            else
                $this->template = 'default/template/account/layout_default.expand';
		}
							
		$this->response->setOutput($this->render_ecwig());
	}
	
	public function add() {
		$this->language->load_json('account/wishlist', $this->identityAllowed());
		$this->load->model('account/wishlist');
		$json = array();

		if (!isset($this->session->data['wishlist'])) {
			$this->session->data['wishlist'] = array();
		}
				
		if (isset($this->request->post['product_id'])) {
			$product_id = $this->request->post['product_id'];
		} else {
			$product_id = 0;
		}
		
		$this->load->model('catalog/product');
		
		$product_info = $this->model_catalog_product->getProduct($product_id);

		if ($product_info) {
            
            if ($this->customer->isLogged()) {
                // Only customers can use this.
                if (!in_array($this->request->post['product_id'], $this->session->data['wishlist'])) {	
                    $this->session->data['wishlist'][] = $this->request->post['product_id'];
                    $this->customer->updateActs();
                }

				$this->wishlist = $this->model_account_wishlist->getWishlist($this->session->data['customer_id']);
				if (!in_array($this->request->post['product_id'], $this->wishlist)) {	
					$this->wishlist[] = $this->request->post['product_id'];
					$products = serialize($this->wishlist);
					$this->model_account_wishlist->UpdateWishlist($products,$this->session->data['customer_id']);

				}

                ///Notify me when available App
                $this->load->model('module/product_notify_me');
                if($this->model_module_product_notify_me->isActive()){
                    $data = [
                        'customer_id' => $this->customer->getId(),
                        'product_id' => $product_id,
                        'name' => '\''.$this->customer->getFirstName().' '.$this->customer->getLastName().'\'',
                        'email' => '\''.$this->customer->getEmail().'\'',
                        'phone' => '\''.$this->customer->getTelephone().'\'',
                        'is_notified' => 0
                    ];
                    $this->model_module_product_notify_me->addNotify($data);
                }
                //////////////////////////////


				$json['status'] = '1';			
				$json['success'] = sprintf($this->language->get('text_success'), $this->url->link('product/product', 'product_id=' . $this->request->post['product_id']), $product_info['name'], $this->url->link('account/wishlist'));				
			} else {
				$json['status'] = '0';
				$json['success'] = sprintf($this->language->get('text_login'), $this->url->link('account/login', '', 'SSL'), $this->url->link('account/register', '', 'SSL'), $this->url->link('product/product', 'product_id=' . $this->request->post['product_id']), $product_info['name'], $this->url->link('account/wishlist'));				
			}
            $this->session->data['wishlist'] = $this->model_account_wishlist->getWishlist($this->session->data['customer_id']);
			$json['total'] = isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0;
		}	
		
		$this->response->setOutput(json_encode($json));
	}

	public function remove() {
		$this->language->load_json('account/wishlist', $this->identityAllowed());
		$this->load->model('account/wishlist');
		$json = array();
				
		if (isset($this->request->post['product_id'])) {
			$product_id = $this->request->post['product_id'];
		} else {
			$product_id = 0;
		}
		
		$this->load->model('catalog/product');
		
		$product_info = $this->model_catalog_product->getProduct($product_id);

		if ($product_info) {
			if ($this->customer->isLogged()) {

				$this->wishlist = $this->model_account_wishlist->getWishlist($this->session->data['customer_id']);
				$key = array_search($this->request->post['product_id'], $this->wishlist);
				if ($key !== false) {
					unset($this->wishlist[$key]);
					$products = serialize($this->wishlist);
					$this->model_account_wishlist->UpdateWishlist($products,$this->session->data['customer_id']);
				}

				$json['status'] = '1';			
				$json['success'] = sprintf($this->language->get('text_remove'));				
			} else {
				$json['status'] = '0';
				$json['success'] = sprintf($this->language->get('text_login'), $this->url->link('account/login', '', 'SSL'), $this->url->link('account/register', '', 'SSL'), $this->url->link('product/product', 'product_id=' . $this->request->post['product_id']), $product_info['name'], $this->url->link('account/wishlist'));				
			}

			$this->session->data['wishlist'] = $this->model_account_wishlist->getWishlist($this->session->data['customer_id']);
			$json['total'] = isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0;
		}	
		
		$this->response->setOutput(json_encode($json));
	}
    
    public function identityAllowed()
    {
        return defined('LOGIN_MODE') && LOGIN_MODE === 'identity' && $this->identity->isStoreOnWhiteList();
    }
}
?>
