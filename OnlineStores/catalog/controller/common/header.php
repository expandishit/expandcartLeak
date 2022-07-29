<?php
class ControllerCommonHeader extends Controller {
	protected function index() {
        $queryRewardPointInstalled = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'reward_points_pro'");

        if($queryRewardPointInstalled->num_rows) {
            $this->document->addStyle('catalog/view/javascript/rewardpoints/css/stylesheet.css');
            $this->document->addStyle('catalog/view/javascript/rewardpoints/css/jquery.nouislider.css');
            $this->document->addStyle('catalog/view/javascript/rewardpoints/css/jquery.nouislider.pips.css');
            $this->document->addScript('catalog/view/javascript/rewardpoints/js/lib/jquery.nouislider.all.js');
            $this->document->addScript('catalog/view/javascript/rewardpoints/js/lib/underscore.js');
            $this->document->addScript('catalog/view/javascript/rewardpoints/js/lib/backbone.js');
            $this->document->addScript('catalog/view/javascript/rewardpoints/js/head.main.js');
            $this->document->addScript('catalog/view/javascript/rewardpoints/js/view.js');
        }

        $this->document->addScript('catalog/view/javascript/jquery/colorbox/jquery.colorbox-min.js');
        $this->document->addStyle('catalog/view/javascript/jquery/colorbox/colorbox.css');

        $rss = isset($this->request->get["rss"]) ? $this->request->get["rss"] : "";
        if ($rss=="latest" || $rss=="featured" || $rss=="special" || $rss=="bestseller") {
            $this->load->model("catalog/product");
            $this->load->model("localisation/currency");
            $this->load->model("tool/image");
            $this->data["products"] = array();

            $limit = 20;
            $image_width = 100;
            $image_height = 100;
            $currency = $this->currency->getCode();

            if ($rss == "latest") {
                $data = array("sort"  => "p.date_added", "order" => "DESC", "start" => 0, "limit" => $limit);
                $products = $this->model_catalog_product->getProducts($data);
            }
            elseif ($rss == "featured") {
                $featured_products = explode(",", $this->config->get("featured_product"));
                $featured_products = array_slice($featured_products, 0, $limit);
                foreach ($featured_products as $product_id) $products[] = $this->model_catalog_product->getProduct($product_id);
            }
            elseif ($rss == "special") {
                $data = array("sort"  => "pd.name", "order" => "ASC", "start" => 0, "limit" => $limit);
                $products = $this->model_catalog_product->getProductSpecials($data);
            }
            elseif ($rss == "bestseller") {
                $data = array("sort"  => "pd.name", "order" => "ASC", "start" => 0, "limit" => $limit);
                $products = $this->model_catalog_product->getBestSellerProducts($limit);
            }

            $output = "<?xml version='1.0' encoding='UTF-8' ?>";
            $output .= "<rss version='2.0'>";
            $output .= "<channel>";
            $output .= "<title><![CDATA[" . $this->config->get("config_name") . " - $rss products]]></title>";
            $output .= "<description><![CDATA[" . $this->config->get("config_meta_description") . "]]></description>";
            $output .= "<link><![CDATA[" . HTTP_SERVER . "]]></link>";
            foreach ($products as $product) {
                $title = $product["name"];
                $link = $this->url->link("product/product", "product_id=" . $product["product_id"]);
                $price = $this->config->get("config_customer_price") ? false : $this->currency->format($this->tax->calculate($product["price"], $product["tax_class_id"], $this->config->get("config_tax")));
                $special = ((float)$product["special"]) ? $this->currency->format($this->tax->calculate($product["special"], $product["tax_class_id"], $this->config->get("config_tax"))) : false;
                $image_url = $this->model_tool_image->resize($product["image"], $image_width, $image_height);
                $description = "";
                if ($price) $description .= ($special) ? "<p><strong><span style='color:red; text-decoration:line-through;'>$price</span>$special</strong></p>" : "<p><strong>$price</strong></p>";
                if ($image_url) $description .= "<p><a href='$link'><img src='$image_url' alt=''/></a></p>";
                if ($product["description"]) $description .= $product["description"];

                if ($rss != "special" || $special) {
                    $output .= "<item>";
                    $output .= "<title><![CDATA[" . html_entity_decode($title, ENT_QUOTES, "UTF-8") . "]]></title>";
                    $output .= "<link><![CDATA[" . html_entity_decode($link, ENT_QUOTES, "UTF-8") . "]]></link>";
                    $output .= "<description>" . $description . "</description>";
                    $output .= "<guid><![CDATA[" . html_entity_decode($link, ENT_QUOTES, "UTF-8") . "]]></guid>";
                    $output .= "<pubDate>" . date("D, d M Y H:i:s O", strtotime($product["date_added"])) . "</pubDate>";
                    $output .= "</item>";
                }
            }
            $output .= "</channel>";
            $output .= "</rss>";

            header("Content-Type: application/rss+xml");
            echo "$output";
            die();
        }

		$this->data['title'] = $this->document->getTitle();

		if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
			$server = $this->config->get('config_ssl');
		} else {
			$server = $this->config->get('config_url');
		}

		$this->data['base'] = $server;
		$this->data['description'] = $this->document->getDescription();
		$this->data['keywords'] = $this->document->getKeywords();
		$this->data['links'] = $this->document->getLinks();	 
		$this->data['styles'] = $this->document->getStyles();
		$this->data['scripts'] = $this->document->getScripts();
		$this->data['lang'] = $this->language->get('code');
		$this->data['direction'] = $this->language->get('direction');
		$this->data['google_analytics'] = html_entity_decode($this->config->get('config_google_analytics'), ENT_QUOTES, 'UTF-8');
		$this->data['name'] = $this->config->get('config_name');
		
		if ($this->config->get('config_icon') && file_exists(DIR_IMAGE . $this->config->get('config_icon'))) {
			$this->data['icon'] = $server . 'image/' . STORECODE . '/' . $this->config->get('config_icon');
		} else {
			$this->data['icon'] = '';
		}
		
		if ($this->config->get('config_logo') && file_exists(DIR_IMAGE . $this->config->get('config_logo'))) {
			$this->data['logo'] = $server . 'image/' . STORECODE . '/' . $this->config->get('config_logo');
		} else {
			$this->data['logo'] = '';
		}

        // Calculate Totals
        $total_data = array();
        $total = 0;
        $taxes = $this->cart->getTaxes();
		
		$this->language->load('common/header');

        $display_name="";
        if ($this->customer->getFirstName())
            $display_name = $this->customer->getFirstName();
        else
            $display_name = $this->customer->getEmail();

        if ($this->customer->isCustomerAllowedToViewPrice()) {
            $this->load->model('setting/extension');

            $sort_order = array();

            $results = $this->model_setting_extension->getExtensions('total');

            foreach ($results as $key => $value) {
                $sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
            }

            array_multisort($sort_order, SORT_ASC, $results);

            foreach ($results as $result) {
                if ($this->config->get($result['code'] . '_status')) {
                    $this->load->model('total/' . $result['code']);

                    $this->{'model_total_' . $result['code']}->getTotal($total_data, $total, $taxes);
                }
            }
        }

		$this->data['text_home'] = $this->language->get('text_home');
		$this->data['text_wishlist'] = sprintf($this->language->get('text_wishlist'), (isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0));
		$this->data['text_shopping_cart'] = $this->language->get('text_shopping_cart');
    	$this->data['text_search'] = $this->language->get('text_search');
        $this->data['text_searchbtn'] = $this->language->get('text_searchbtn');
        $this->data['text_welcome_pav_furniture'] = sprintf($this->language->get('text_welcome_pav_furniture'), $this->url->link('account/login', '', 'SSL'), $this->url->link('account/register', '', 'SSL'));
        $this->data['text_logged_pav_furniture'] = sprintf($this->language->get('text_logged_pav_furniture'), $this->url->link('account/account', '', 'SSL'), $this->customer->getFirstName(), $this->url->link('account/logout', '', 'SSL'));
        $this->data['text_welcome_pav_cosmetics'] = sprintf($this->language->get('text_welcome_pav_cosmetics'), $this->url->link('account/login', '', 'SSL'), $this->url->link('account/register', '', 'SSL'));
        $this->data['text_logged_pav_cosmetics'] = sprintf($this->language->get('text_logged_pav_cosmetics'), $this->url->link('account/account', '', 'SSL'), $this->customer->getFirstName(), $this->url->link('account/logout', '', 'SSL'));
        $this->data['text_welcome_pav_books'] = sprintf($this->language->get('text_welcome_pav_books'), $this->url->link('account/login', '', 'SSL'), $this->url->link('account/register', '', 'SSL'));
        $this->data['text_logged_pav_books'] = sprintf($this->language->get('text_logged_pav_books'), $this->url->link('account/account', '', 'SSL'), $this->customer->getFirstName(), $this->url->link('account/logout', '', 'SSL'));
		$this->data['text_welcome'] = sprintf($this->language->get('text_welcome'), $this->url->link('account/login', '', 'SSL'), $this->url->link('account/register', '', 'SSL'));
		$this->data['text_logged'] = sprintf($this->language->get('text_logged'), $this->url->link('account/account', '', 'SSL'), $display_name, $this->url->link('account/logout', '', 'SSL'));
		$this->data['text_account'] = $this->language->get('text_account');
        $this->data['text_menu'] = $this->language->get('text_menu');
    	$this->data['text_checkout'] = $this->language->get('text_checkout');
        $this->data['text_cart'] = $this->language->get('text_cart');
        $this->data['text_items'] = sprintf($this->language->get('text_items'), $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0), $this->currency->format($total));
        $this->data['text_language'] = $this->language->get('text_language');
        $this->data['text_currency'] = $this->language->get('text_currency');
		$this->data['text_blog'] = $this->language->get('text_blog');
		$this->data['text_about'] = $this->language->get('text_about');
		$this->data['text_categories'] = $this->language->get('text_categories');
		$this->data['text_special'] = $this->language->get('text_special');
		$this->data['text_manufacturer'] = $this->language->get('text_manufacturer');
		$this->data['text_contact'] = $this->language->get('text_contact');
		$this->data['text_sitemap'] = $this->language->get('text_sitemap');	
		$this->data['text_videogallery'] = $this->language->get('text_videogallery');
        $this->data['text_shop_all'] = $this->language->get('text_shop_all');
	    
		$this->data['home'] = $this->url->link('common/home');
		$this->data['wishlist'] = $this->url->link('account/wishlist', '', 'SSL');
		$this->data['logged'] = $this->customer->isLogged();
		$this->data['account'] = $this->url->link('account/account', '', 'SSL');
		$this->data['shopping_cart'] = $this->url->link('checkout/cart');
        $this->data['cart'] = $this->url->link('checkout/cart');
        $this->data['checkout'] = $this->url->link('checkout/checkout', '', 'SSL');

        if (isset($this->request->get['filter_name'])) {
            $this->data['filter_name'] = $this->request->get['filter_name'];
        } else {
            $this->data['filter_name'] = '';
        }
		
		// Daniel's robot detector
		$status = true;
		
		if (isset($this->request->server['HTTP_USER_AGENT'])) {
			$robots = explode("\n", trim($this->config->get('config_robots')));

			foreach ($robots as $robot) {
				if ($robot && strpos($this->request->server['HTTP_USER_AGENT'], trim($robot)) !== false) {
					$status = false;

					break;
				}
			}
		}
		
		// A dirty hack to try to set a cookie for the multi-store feature
		$this->load->model('setting/store');
		
		$this->data['stores'] = array();
		
		if ($this->config->get('config_shared') && $status) {
			$this->data['stores'][] = $server . 'catalog/view/javascript/crossdomain.php?session_id=' . $this->session->getId();
			
			$stores = $this->model_setting_store->getStores();
					
			foreach ($stores as $store) {
				$this->data['stores'][] = $store['url'] . 'catalog/view/javascript/crossdomain.php?session_id=' . $this->session->getId();
			}
		}

        // Search
        if (isset($this->request->get['search'])) {
            $this->data['search'] = $this->request->get['search'];
        } else {
            $this->data['search'] = '';
        }

        $this->data['action'] = $this->url->link('common/home');

        if (!isset($this->request->get['route'])) {
            $this->data['redirect'] = $this->url->link('common/home');
        } else {
            $data = $this->request->get;

            unset($data['_route_']);

            $route = $data['route'];

            unset($data['route']);

            $url = '';

            if ($data) {
                $url = '&' . urldecode(http_build_query($data, '', '&'));
            }

            $this->data['redirect'] = $this->url->link($route, $url);
        }

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && isset($this->request->post['language_code'])) {
            $this->session->data['language'] = $this->request->post['language_code'];

            if (isset($this->request->post['redirect'])) {
                $this->redirect($this->request->post['redirect']);
            } else {
                $this->redirect($this->url->link('common/home'));
            }
        }

        $this->data['language_code'] = $this->session->data['language'];

        $this->load->model('localisation/language');

        $this->data['languages'] = array();

        $results = $this->model_localisation_language->getLanguages();

        foreach ($results as $result) {
            if ($result['status']) {
                $this->data['languages'][] = array(
                    'name'  => $result['name'],
                    'code'  => $result['code'],
                    'image' => $result['image']
                );
            }
        }

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && isset($this->request->post['currency_code'])) {
            $this->currency->set($this->request->post['currency_code']);

            unset($this->session->data['shipping_methods']);
            unset($this->session->data['shipping_method']);

            if (isset($this->request->post['redirect'])) {
                $this->redirect($this->request->post['redirect']);
            } else {
                $this->redirect($this->url->link('common/home'));
            }
        }

        $this->data['currency_code'] = $this->currency->getCode();

        $this->load->model('localisation/currency');

        $this->data['currencies'] = array();

        $results = $this->model_localisation_currency->getCurrencies();

        foreach ($results as $result) {
            if ($result['status']) {
                $this->data['currencies'][] = array(
                    'title'        => $result['title'],
                    'code'         => $result['code'],
                    'symbol_left'  => $result['symbol_left'],
                    'symbol_right' => $result['symbol_right']
                );
            }
        }

        // Menu
		$this->load->model('catalog/category');
		
		$this->load->model('catalog/product');
		
		$this->data['categories'] = array();
					
		$categories = $this->model_catalog_category->getCategories(0);
		
		foreach ($categories as $category) {
			if ($category['top']) {
				// Level 2
				$children_data = array();
				
				$children = $this->model_catalog_category->getCategories($category['category_id']);
				
				foreach ($children as $child) {
				    $sub_children = $this->model_catalog_category->getCategories($child['category_id']);
                    $sub_children_data = array();
                    foreach ($sub_children as $sub_child) {
                        $sub_children_data[] = array(
                            'name'  => $sub_child['name'],
                            'category_id' => $sub_child['category_id'],
                            'href'  => $this->url->link('product/category', 'path=' . $child['category_id'] . '_' . $sub_child['category_id'])
                        );
                    }
					$children_data[] = array(
						'name'  => $child['name'],
                        'category_id' => $child['category_id'],
                        'href'  => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id']),
                        'children' => $sub_children_data
					);						
				}
                $manufacturers = $this->model_catalog_category->getCategoryManufacturerImages($category['category_id']);
                $manufacturers_data = array();
				foreach ($manufacturers as $brand) {
                    $manufacturers_data[] = array(
                      'name'    => $brand['name'],
                      'image'   => $brand['image'] ? $server . 'image/' . STORECODE . '/' . $brand['image'] : '',
                      'href'    => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $brand['manufacturer_id'])
                    );
                }

				// Level 1
				$this->data['categories'][] = array(
					'name'     => $category['name'],
					'image'    => $category['image'] ? $server . 'image/' . STORECODE . '/' . $category['image'] : '',
					'children' => $children_data,
                    'category_id' => $category['category_id'],
					'column'   => $category['column'] ? $category['column'] : "1",
					'href'     => $this->url->link('product/category', 'path=' . $category['category_id']),
                    'brands'   => $manufacturers_data
				);
			}
		}

        $this->load->model('catalog/product');

        if ($this->model_catalog_product->checkMSModule()) {

            $this->data['search_in_city'] = $this->language->get('search_in_city');

            $this->data['text_enabled'] = $this->language->get('text_select');

            $this->data['search_by_city']['status'] = 1;

            $this->data['search_by_city']['zones'] = $this->model_catalog_product->get_zones();

            $this->data['search_by_city_url'] = $this->url->link('product/search');

            if (isset($this->request->get['city']) && $this->request->get['city'] > 0) {
                $this->data['search_by_city']['city_id'] = $this->request->get['city'];
            }
        }


		$this->children = array(
			'module/language',
			'module/currency',
			'module/cart'
		);

        if($this->session->data['ismobile'] != "1") {
            if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/common/header.tpl')) {
                $this->template = $this->config->get('config_template') . '/template/common/header.tpl';
            } else {
                $this->template = 'default/template/common/header.tpl';
            }
        } else {
            $this->template = 'default/template/common/mobileheader.tpl';
        }
		
    	$this->render();
	} 	
}
?>
