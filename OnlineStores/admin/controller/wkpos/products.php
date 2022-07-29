<?php

use ExpandCart\Foundation\String\Barcode\Generator;
class ControllerWkposProducts extends Controller {

    public function __construct($registry)
    {
        parent::__construct($registry);
        if(PRODUCTID == 3){
            $this->redirect(
                $this->url->link('error/not_found', '', 'SSL')
            );
            exit();
        }
    }

	public function index() {
		$this->load->model('wkpos/wkpos');
		if(!$this->model_wkpos_wkpos->is_installed())
			$this->response->redirect($this->url->link('wkpos/main', '', true));
		
		$this->data = $this->load->language('catalog/product');
		$this->data = array_merge($this->data, $this->load->language('wkpos/products'));

		$this->document->setTitle($this->language->get('heading_title'));

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
		);

		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_heading_main'),
			'href' => $this->url->link('wkpos/main', 'token=' . $this->session->data['token'], true)
		);


		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('wkpos/products', 'token=' . $this->session->data['token'], true)
		);

		if (isset($this->session->data['warning'])) {
			$this->data['error_warning'] = $this->session->data['warning'];
			unset($this->session->data['warning']);
		} else {
			$this->data['error_warning'] = '';
		}
        $this->load->model('wkpos/outlet');
		$this->data['outlets']   = $this->model_wkpos_outlet->getOutlets(['enabled' => 1]);
        $this->load->model('wkpos/supplier');
        $this->data['suppliers'] = $this->model_wkpos_supplier->getSuppliers(['enabled' => 1]);

		$this->load->model('catalog/product');

		$this->data['product_total'] = $this->model_catalog_product->getTotalProducts();
		$this->data['number_inslot'] = $this->config->get('wkpos_barcode_slot') > 5 ? $this->config->get('wkpos_barcode_slot') : 5;

		$this->data['token'] = $this->session->data['token'];
		$this->data['print_action'] = $this->url->link('wkpos/products/generatePrint', 'token=' . $this->session->data['token'], true);
		$this->data['mass_print'] = $this->url->link('wkpos/products/massPrint', 'token=' . $this->session->data['token'], true);

		$this->language->load('catalog/product_filter');
		$this->initializer([
            'filter' => 'catalog/product_filter'
        ]);
		$this->data['filterElements'] = $this->filter->getFilter();

		$this->children = array(
           'common/header',
            'common/footer',
        );
		$this->template = 'wkpos/products.expand';
		$this->response->setOutput($this->render(TRUE));

		// $this->data['header'] = $this->load->controller('common/header');
		// $this->data['column_left'] = $this->load->controller('common/column_left');
		// $this->data['footer'] = $this->load->controller('common/footer');

		//return $this->load->view('wkpos/products.tpl', $data);

		//$this->response->setOutput($this->load->view('wkpos/products.tpl', $data));
	}

	public function loadProducts() {
		$this->load->model('wkpos/products');
		$this->load->model('catalog/product');

		if (isset($this->request->post['outlet']) && $this->request->post['outlet']) {
			$outlet = $this->request->post['outlet'];
		} else {
			$outlet = 0;
		}

		if (isset($this->request->post['start']) && $this->request->post['start']) {
			$start = $this->request->post['start'];
		} else {
			$start = 0;
		}

		if (isset($this->request->post['order']) && $this->request->post['order']) {
			$order = $this->request->post['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->post['sort']) && $this->request->post['sort']) {
			$sort = $this->request->post['sort'];
		} else {
			$sort = '';
		}

		if (isset($this->request->post['filter_name']) && $this->request->post['filter_name']) {
			$filter_name = $this->request->post['filter_name'];
		} else {
			$filter_name = null;
		}

		if (isset($this->request->post['filter_model']) && $this->request->post['filter_model']) {
			$filter_model = $this->request->post['filter_model'];
		} else {
			$filter_model = null;
		}

		if (isset($this->request->post['filter_price']) && $this->request->post['filter_price']) {
			$filter_price = $this->request->post['filter_price'];
		} else {
			$filter_price = null;
		}

		if (isset($this->request->post['filter_quantity']) && !($this->request->post['filter_quantity'] == '')) {
			$filter_quantity = $this->request->post['filter_quantity'];
		} else {
			$filter_quantity = null;
		}

		if (isset($this->request->post['filter_assign']) && !($this->request->post['filter_assign'] == '')) {
			$filter_assign = $this->request->post['filter_assign'];
		} else {
			$filter_assign = null;
		}

		if (isset($this->request->post['filter_status']) && !($this->request->post['filter_status'] == '')) {
			$filter_status = $this->request->post['filter_status'];
		} else {
			$filter_status = null;
		}

		if (isset($this->request->post['filter_pos_status']) && !($this->request->post['filter_pos_status'] == '')) {
			$filter_pos_status = $this->request->post['filter_pos_status'];
		} else {
			$filter_pos_status = null;
		}

		$json['products'] = array();

		$filter_data = array(
			'filter_name'	    => $filter_name,
			'filter_model'	    => $filter_model,
			'filter_price'	    => $filter_price,
			'filter_quantity'   => $filter_quantity,
			'filter_assign'     => $filter_assign,
			'filter_status'     => $filter_status,
			'filter_pos_status' => $filter_pos_status,
			'sort'              => $sort,
			'order'             => $order,
			'start'             => $start,
			'limit'             => $this->config->get('config_limit_admin')
		);

		$this->load->model('tool/image');

		if ($outlet) {
			$this->load->model('catalog/product');
			$json['product_total'] = $this->model_catalog_product->getTotalProducts($filter_data);

			$results = $this->model_catalog_product->getProducts($filter_data);
		} else {
			$json['product_total'] = $this->model_wkpos_products->getTotalProducts($filter_data);

			$results = $this->model_wkpos_products->getProducts($filter_data);
		}

		foreach ($results as $result) {
			$image = $this->model_tool_image->resize($result['image'], 40, 40);

			$special = false;

			$product_specials = $this->model_catalog_product->getProductSpecials($result['product_id']);

			foreach ($product_specials  as $product_special) {
				if (($product_special['date_start'] == '0000-00-00' || strtotime($product_special['date_start']) < time()) && ($product_special['date_end'] == '0000-00-00' || strtotime($product_special['date_end']) > time())) {
					$special = $product_special['price'];

					break;
				}
			}

			if ($outlet) {
				$outlet_data = $this->model_wkpos_products->getOutletProductData($result['product_id'], $outlet);
				$pos_quantity = $outlet_data['quantity'];
                $pos_all_quantity = $result['pos_all_quantity'];
				$pos_status = $outlet_data['status'];
				$result['barcode'] = '';
			} else {
				$pos_quantity = $result['pos_quantity'];
                $pos_all_quantity = $result['pos_quantity'];
				$pos_status = $result['pos_status'];
			}

			if ($result['barcode'] && is_file(str_replace('system/', 'wkpos/barcode/img/', DIR_SYSTEM) . $result['barcode'] . '.png')) {
				$barcode = HTTPS_CATALOG . 'wkpos/barcode/img/' . $result['barcode'] . '.png?lastimage='.uniqid();
			} else {
				$barcode = 0;
			}

			$json['products'][] = array(
				'product_id'   => $result['product_id'],
				'image'        => $image,
				'name'         => $result['name'],
				'model'        => $result['model'],
				'price'        => $result['price'],
				'special'      => $special,
				'quantity'     => $result['quantity'],
				'barcode'      => $barcode,
				'pos_quantity' => $pos_quantity ?? 0,
                'pos_all_quantity' => $pos_all_quantity ?? 0,
				'status'       => ($result['status']) ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
				'pos_status'   => $pos_status
			);
		}

		if (count($json['products'])) {
			$json['success'] = 'Success';
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function loadProductsDt() {
		$this->load->model('wkpos/products');
		$this->load->model('catalog/product');

		$request = $this->request->request;

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            parse_str(html_entity_decode($this->request->post['filter']), $filterData);
            $filterData = $filterData['filter'];
        }

		$start  = $request['start'];
        $length = $request['length'];

        $orderColumn = $request['columns'][$request['order'][0]['column']]['name'];
        $orderType = $request['order'][0]['dir'];

        $data['search'] = null;
        if (isset($request['search']['value']) && strlen($request['search']['value']) > 0) {
            $filterData['search'] = $request['search']['value'];
        }

		$json['products'] = array();

		$data = array(
			'filter_name'	    => $filter_name,
			'filter_model'	    => $filter_model,
			'filter_price'	    => $filter_price,
			'filter_quantity'   => $filter_quantity,
			'filter_assign'     => $filter_assign,
			'filter_status'     => $filter_status,
			'filter_pos_status' => $filter_pos_status,
			'sort'              => $orderColumn,
			'order'             => $orderType,
			'start'             => $request['start'],
			'limit'             => $request['length']
		);

		$this->load->model('tool/image');

		$json['product_total'] = $this->model_catalog_product->getTotalProducts($data);
		$filterdProducts = $this->model_catalog_product->getProductsToFilter($data, $filterData, true);
		$this->data['locales'] = [
            'show_x_from' => sprintf(
                $this->language->get('filter_show_x_from'),
                $filterdProducts['totalFiltered'],
                $filterdProducts['total']
            )
        ];

		$results = $filterdProducts['data'];

		/*if ($outlet) {
			$this->load->model('catalog/product');
			$json['product_total'] = $this->model_catalog_product->getTotalProducts($data);

			$results = $this->model_catalog_product->getProducts($data);
		} else {
			$json['product_total'] = $this->model_wkpos_products->getTotalProducts($data);

			$results = $this->model_wkpos_products->getProducts($data);
		}*/
		$this->load->model('localisation/currency');
		$currency = $this->model_localisation_currency->getCurrencyByCode($this->config->get('config_currency'));
		foreach ($results as $result) {
			$image = $this->model_tool_image->resize($result['image'], 40, 40);

			$special = false;

			$product_specials = $this->model_catalog_product->getProductSpecials($result['product_id']);

			foreach ($product_specials  as $product_special) {
				if (($product_special['date_start'] == '0000-00-00' || strtotime($product_special['date_start']) < time()) && ($product_special['date_end'] == '0000-00-00' || strtotime($product_special['date_end']) > time())) {
					$special = $product_special['price'];

					break;
				}
			}

			if ($outlet) {
				$outlet_data = $this->model_wkpos_products->getOutletProductData($result['product_id'], $outlet);
				$pos_quantity = $outlet_data['quantity'];
				$pos_status = $outlet_data['status'];
				$result['barcode'] = '';
			} else {
				$pos_quantity = $result['pos_quantity'];
				$pos_status = $result['pos_status'];
			}

			if ($result['barcode']) {
				$barcode = $this->barcode($result['barcode']);
			} else {
				$barcode = null;
			}

			$json['products'][] = array(
				'product_id'   => $result['product_id'],
				'image'        => $image,
				'name'         => $result['localized_name'] ?? $result['name'],
				'model'        => $result['model'],
				'price'        => number_format($result['price'] , $currency['decimal_place']),
				'special'      => $special,
				'quantity'     => $result['quantity'],
				'barcode'      => $barcode,
				'sku'          => 'TEMP',
				'pos_quantity' => $pos_quantity ? $pos_quantity : 0,
				'status'       => ($result['status']) ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
				'pos_status'   => $pos_status
			);
		}

		if (count($json['products'])) {
			$json['success'] = 'Success';
		}

		$this->data['filter_name'] = $filter_name;
        $this->data['filter_model'] = $filter_model;
        $this->data['filter_price'] = $filter_price;
        $this->data['filter_quantity'] = $filter_quantity;
        $this->data['filter_status'] = $filter_status;

        $this->data['sort'] = $sort;
        $this->data['order'] = $order;

        $this->template = 'wkpos/product-list.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

		$this->response->addHeader('Content-Type: application/json');
		//$this->response->setOutput(json_encode($json));
		$this->response->setOutput(json_encode([
            "draw" => intval($request['draw']),
            "recordsTotal" => intval($filterdProducts['total']),
            "recordsFiltered" => $filterdProducts['totalFiltered'],
            'data' => $json['products'],
            'heading' => $this->data['locales']['show_x_from']
        ]));
	}

	public function assignQuantity() {
		$json = array();
		$this->load->language('wkpos/products');

		if (!$this->user->hasPermission('modify', 'wkpos/products')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json && isset($this->request->post['product_id']) && $this->request->post['product_id'] && isset($this->request->post['quantity']) && isset($this->request->post['outlet'])) {
			if (ctype_digit($this->request->post['quantity']) && ($this->request->post['quantity'] >= 0)) {
				$this->load->model('wkpos/products');
				$this->load->model('catalog/product');
				$product = $this->model_catalog_product->getProduct($this->request->post['product_id']);
				$assigned_quantity = $this->model_wkpos_products->getAssignedQuantity($this->request->post['product_id'], $this->request->post['outlet']);
				$total_quantity = $assigned_quantity + $this->request->post['quantity'];

				if ($product['quantity'] < $total_quantity) {
					$json['error'] = $this->language->get('error_quantity');
				} else {
					$this->model_wkpos_products->assignQuantity($this->request->post['product_id'], $this->request->post['quantity'], $this->request->post['outlet']);
					$json['success'] = $this->language->get('text_success');
				}
			} else {
				$json['error'] = $this->language->get('error_num_quantity');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function changeStatus() {
		$json = array();
		$this->load->language('wkpos/products');

		if (!$this->user->hasPermission('modify', 'wkpos/products')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json && isset($this->request->post['product_id']) && $this->request->post['product_id'] && isset($this->request->post['status']) && isset($this->request->post['outlet'])) {
			$this->load->model('wkpos/products');
			$this->model_wkpos_products->changeStatus($this->request->post['product_id'], $this->request->post['status'], $this->request->post['outlet']);
			$json['success'] = $this->language->get('text_success_status');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function generateBarcode() {
		$json = array();
		$this->load->language('wkpos/products');

		if (isset($this->request->post['product_id']) && $this->request->post['product_id']) {
			$this->load->model('wkpos/products');
			$image = $this->model_wkpos_products->generateBarcode($this->request->post['product_id']);
			$json['image'] = HTTPS_CATALOG . 'wkpos/barcode/img/' . $image . '.png?lastimage='.uniqid();
			$json['success'] = $this->language->get('text_success_barcode');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function generatePrint() {
		$this->load->model('wkpos/products');
		$this->load->language('wkpos/products');

		if (isset($this->request->post['product_id']) && $this->request->post['product_id']) {
			$product_id = $this->request->post['product_id'];
			if (isset($this->request->post['quantity']) && $this->request->post['quantity']) {
				$quantity = $this->request->post['quantity'];
			} else {
				$quantity = 1;
			}

			$this->data['button_back'] = $this->language->get('button_back');
			$this->data['button_print'] = $this->language->get('button_print');
			$this->data['text_print_barcode'] = $this->language->get('text_print_barcode');

			$barcode = $this->model_wkpos_products->getBarcode($product_id);

			$this->data['image'] = HTTPS_CATALOG . 'wkpos/barcode/img/' . $barcode['barcode'] . '.png';
			$this->data['name'] = $barcode['name'];
			$this->data['barcode_name'] = $this->config->get('wkpos_barcode_name');
			$this->data['quantity'] = $quantity;
			$this->data['cancel'] = $this->url->link('wkpos/products', 'token=' . $this->session->data['token'], true);

			$this->template = 'wkpos/print_barcode.expand';
			$this->response->setOutput($this->render(TRUE));

			//$this->response->setOutput($this->load->view('wkpos/print_barcode.tpl', $data));
		}
	}

	public function massPrint() {
		$this->load->language('wkpos/products');
		if (isset($this->request->post['selected']) && $this->request->post['selected']) {
			if (isset($this->request->post['allcheckbox']) && $this->request->post['allcheckbox']) {
				$all = 1;
			} else {
				$all = 0;
			}
			if (isset($this->request->post['print_quantity']) && $this->request->post['print_quantity']) {
				$this->data['quantity'] = $this->request->post['print_quantity'];
			} else {
				$this->data['quantity'] = 1;
			}

			$this->load->model('wkpos/products');
            $this->model_wkpos_products->initBarcodes($this->request->post['selected']);
            $mass_data = $this->model_wkpos_products->massBarcode($this->request->post['selected'], $all);

			$this->data['button_back'] = $this->language->get('button_back');
			$this->data['button_print'] = $this->language->get('button_print');
			$this->data['text_print_barcode'] = $this->language->get('text_print_barcode');

			$this->data['barcodes'] = array();
			$this->load->model('localisation/currency');
			$currency = $this->model_localisation_currency->getCurrencyByCode($this->config->get('config_currency'));
			foreach ($mass_data as $barcode) {
				if ($barcode['barcode']) {
					$this->data['barcodes'][] = array(
						'name'  => (int) $this->config->get('wkpos_barcode_name') ? $barcode['name']:null,
						'image' => "data:image/png;base64,". $this->barcode($barcode['barcode']),
                        'barcode_number' => (int) $this->config->get('wkpos_barcode_number' ) ? $barcode['barcode']:null,
                        'price' => (int) $this->config->get('wkpos_barcode_price') ?"(Price: ".number_format($barcode['price'] , $currency['decimal_place']).")":null
						);
				}
			}
			//.uniqid()
			$this->data['mass'] = true;
			$this->data['cancel'] = $this->url->link('wkpos/products', 'token=' . $this->session->data['token'], true);

			$this->template = 'wkpos/print_barcode.expand';
			$this->response->setOutput($this->render(TRUE));

			//$this->response->setOutput($this->load->view('wkpos/print_barcode.tpl', $data));
		} else {
			$this->session->data['warning'] = $this->language->get('error_product');
			$this->response->redirect($this->url->link('wkpos/products', 'token=' . $this->session->data['token'], true));
		}
	}

	public function massGenerate() {
		$json = array();
		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			if (isset($this->request->post['count_barcode'])) {
				$this->load->language('wkpos/products');
				$this->load->model('wkpos/products');
				$filter = array(
					'sort'  => 'p.product_id',
					'order' => 'ASC',
					'start' => $this->request->post['count_barcode'],
					'limit' => $this->config->get('wkpos_barcode_slot') ? (int)$this->config->get('wkpos_barcode_slot') : 5
				);
				$products = $this->model_wkpos_products->getProducts($filter);
				if ($products) {
					$count = 0;
					foreach ($products as $product) {
						$this->model_wkpos_products->generateBarcode($product['product_id']);
						$count ++;
					}
					$json['count'] = $count;
				}
				$json['success'] = $this->language->get('text_success1');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

    public function assignOutSpl() {
        $json = array();
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            if (isset($this->request->post['pids']) && (isset($this->request->post['oids']) || isset($this->request->post['sids']))) {
                $pids = $this->request->post['pids'];
                $oids = $this->request->post['oids'];
                $sids = $this->request->post['sids'];

                $this->load->model('wkpos/products');
                if ($pids && ($oids || $sids)) {
                    $pids = explode(',', $pids);
                    $oids = explode(',', $oids);
                    $sids = explode(',', $sids);

                    $this->load->model('wkpos/outlet');
                    $this->load->model('wkpos/supplier');

                    foreach ($pids as $key => $pid) {

                        //Assign product to outlet
                        if(count($oids)){
                            foreach ($oids as $key => $oid) {
                                $this->model_wkpos_outlet->assignProduct($pid, $oid);
                            }
                        }

                        //Assign product to supplier
                        if(count($sids)){
                            foreach ($sids as $key => $sid) {
                                $this->model_wkpos_supplier->assignProduct($pid, $sid);
                            }
                        }
                    }
                    $json['status'] = $this->language->get('text_success1');
                }else{
                    $json['status'] = 'failed, no ids';
                }
            }else{
                $json['status'] = 'failed, no data';
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function massAssign() {
        $json = array();
        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->request->post['id']) {
            $this->load->model('wkpos/outlet');
            $this->model_wkpos_outlet->assignAll($this->request->post['id']);
            $json['status'] = 'success';
        }else{
            $json['status'] = 'failed, no data';
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
	}
	
	public function barcode($barcode_input=null)
    {
		if($barcode_input){
			$barcodeGenerator = (new Generator())
                ->setType($this->config->get('config_barcode_type'))
				->setBarcode($barcode_input);
				
            return $barcodeGenerator->generate();
		}

        if ($this->request->server['REQUEST_METHOD'] != 'POST') {
            $this->response->setOutput(json_encode(['status' => 'fail']));
            return;
		}

		
        if (isset($this->request->post['product_id'])) {
			$this->data['product_id'] = $this->request->post['product_id'];
        } else {
			$this->data['product_id'] = '';
        }
		
        if ($this->data['product_id'] != '') {
			//Save it in DB
			$this->load->model('catalog/product');
			$this->model_catalog_product->updateProductBarcode($this->data['product_id'],$this->data['product_id']);

            $barcodeGenerator = (new Generator())
                ->setType($this->config->get('config_barcode_type'))
                ->setBarcode($this->data['product_id']);

			$response['success'] = $this->language->get('text_success_barcode');
            $response['image'] = $barcodeGenerator->generate();

            $this->response->setOutput(json_encode($response));
            return;
        } 
    }
}
