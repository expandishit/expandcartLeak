<?php
use ExpandCart\Foundation\String\Barcode\Generator as BarcodeGenerator;

class ControllerModuleWarehouses extends Controller {
	private $error = array();

	public function install()
    {
        $this->load->model("module/warehouses");
        $this->model_module_warehouses->install();
    }

    public function uninstall()
    {
        $this->load->model("module/warehouses");
        $this->model_module_warehouses->uninstall();
    }

    public function index() {
    	$this->load->language('module/warehouses');
    	$this->document->setTitle($this->language->get('heading_title'));

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
		);
		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_heading_main'),
			'href' => $this->url->link('marketplace/home', 'token=' . $this->session->data['token'], true)
		);
		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_settings'),
			'href' => $this->url->link('module/warehouses', 'token=' . $this->session->data['token'] . $url, 'SSL')
		);

		$this->load->model('module/warehouses');
		$this->data['warehouses_list'] = $this->model_module_warehouses->getWarehouses();

		$this->data['warehouses'] = $this->config->get('warehouses');
		
		$this->data['action'] = $this->url->link('module/warehouses/updateSettings', '', 'SSL');
        $this->data['cancel'] = $this->url.'marketplace/home';

        $this->load->model('localisation/order_status');
        $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$this->children = array(
           'common/header',
           'common/footer',
        );
		$this->template = 'module/warehouses/settings.expand';
		$this->response->setOutput($this->render(TRUE));
	}

	public function updateSettings()
    {
        $this->language->load('module/warehouses');

        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)) {
             $result_json['success'] = '0';
             $result_json['error'] = $this->error;
        }else{

            $this->load->model('module/warehouses');

            $data = $this->request->post['warehouses'];

            $this->model_module_warehouses->updateSettings(['warehouses' => $data]);

            $this->session->data['success'] = $this->language->get('text_settings_success');

            $this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success');
            $result_json['success'] = '1';
        }
        $this->response->setOutput(json_encode($result_json));
        return;
    }

	public function list() {
		$this->load->language('module/warehouses');
		$this->load->model('module/warehouses');

		// if(!$this->model_module_warehouses->isActive())
		// 	$this->response->redirect($this->url->link('marketplace/home', '', true));

		$this->document->setTitle($this->language->get('heading_title'));

		$this->getList();
	}

	public function add() {
		$this->load->language('module/warehouses');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('module/warehouses');

		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {

			if($this->validate()){
				$data = $this->request->post;
				$data['rates'] = json_encode($data['rates']);
				$data['duration'] = json_encode($data['duration']);

				$wrs_id = $this->model_module_warehouses->addWarehouse($data);

				if($wrs_id > 0){
					$result_json['redirect'] = '1';
					$result_json['to']       = 'module/warehouses/edit?id='.$wrs_id.'#tab/products';
				}
				
				$result_json['success'] = '1';
	            $result_json['success_msg'] = $this->language->get('text_success');
	            $this->response->setOutput(json_encode($result_json));
	            
	            return;
			}else{
				$result_json['success'] = '0';
                $result_json['errors'] = $this->error;

                $this->response->setOutput(json_encode($result_json));
                return;
			}
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('module/warehouses');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('module/warehouses');

		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			if($this->validate()){
				$data = $this->request->post;
				$data['rates'] = json_encode($data['rates']);
				$data['duration'] = json_encode($data['duration']);

				$this->model_module_warehouses->editWarehouse($this->request->get['id'], $data);
				
				$result_json['success'] = '1';
	            $result_json['success_msg'] = $this->language->get('text_success');
	            $this->response->setOutput(json_encode($result_json));
	            
	            return;
			}else{
				$result_json['success'] = '0';
                $result_json['errors'] = $this->error;

                $this->response->setOutput(json_encode($result_json));
                return;
			}
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('module/warehouses');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('module/warehouses');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $id) {
				if( $id != 1)
					$this->model_module_warehouses->deleteWarehouse($id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('module/warehouses', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	protected function getList() {
		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
		);
		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_heading_main'),
			'href' => $this->url->link('marketplace/home', 'token=' . $this->session->data['token'], true)
		);
		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_warehouses_list'),
			'href' => $this->url->link('module/warehouses', 'token=' . $this->session->data['token'] . $url, 'SSL')
		);

		$this->data['add'] = $this->url->link('module/warehouses/add', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('module/warehouses/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['warehouses'] = array();

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_list'] = $this->language->get('text_list');
		$this->data['text_no_results'] = $this->language->get('text_no_results');
		$this->data['text_confirm'] = $this->language->get('text_confirm');

		$this->data['column_oname'] = $this->language->get('column_oname');
		$this->data['column_ostatus'] = $this->language->get('column_ostatus');
		$this->data['column_oaddress'] = $this->language->get('entry_address');
		$this->data['column_action'] = $this->language->get('column_action');

		$this->data['button_add'] = $this->language->get('text_add');
		$this->data['button_edit'] = $this->language->get('button_edit');
		$this->data['button_delete'] = $this->language->get('button_delete');

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}

		$this->children = array(
           'common/header',
           'common/footer',
        );
		$this->template = 'module/warehouses/warehouses_list.expand';
		$this->response->setOutput($this->render(TRUE));

		/*$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('module/warehouses_list.tpl', $data));*/
	}

	protected function getForm() {
		$this->data = array_merge($this->load->language('catalog/product'), $this->load->language('module/warehouses'));

		if (isset($this->request->get['id']) && $this->request->server['REQUEST_METHOD'] != 'POST') {
			$warehouse_info = $this->model_module_warehouses->getWarehouse($this->request->get['id']);
		}

		$this->data['text_form'] = !isset($this->request->get['id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
		);

		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_list'),
			'href' => $this->url->link('module/warehouses/list', 'token=' . $this->session->data['token'], true)
		);

		if (isset($this->request->get['id']) && !empty($warehouse_info)) {
			$this->data['breadcrumbs'][] = array(
				'text' => $this->language->get('edit_warehouse'). $warehouse_info['name'],
				'href' => $this->url->link('module/warehouses', 'token=' . $this->session->data['token'] . $url, 'SSL')
			);
		} else {
			$this->data['breadcrumbs'][] = array(
				'text' => $this->language->get('add_warehouse'),
				'href' => $this->url->link('module/warehouses', 'token=' . $this->session->data['token'] . $url, 'SSL')
			);
			
		}

		if (!isset($this->request->get['id'])) {
			$this->data['action'] = $this->url->link('module/warehouses/add', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('module/warehouses/edit', 'token=' . $this->session->data['token'] . '&id=' . $this->request->get['id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('module/warehouses', 'token=' . $this->session->data['token'] . $url, 'SSL');


		if (isset($this->request->post['name'])) {
			$this->data['name'] = $this->request->post['name'];
		} elseif (!empty($warehouse_info)) {
			$this->data['name'] = $warehouse_info['name'];
		} else {
			$this->data['name'] = '';
		}

		if (isset($this->request->post['name'])) {
			$this->data['id'] = 0;
		} elseif (!empty($warehouse_info)) {
			$this->data['id'] = $warehouse_info['id'];
		} else {
			$this->data['id'] = 0;
		}

		if (isset($this->request->post['seller_id'])) {
			$this->data['seller_id'] = $this->request->post['seller_id'];
		} elseif (!empty($warehouse_info)) {
			$this->data['seller_id'] = $warehouse_info['seller_id'];
		} else {
			$this->data['seller_id'] = '';
		}

		if (isset($this->request->post['rates'])) {
			$this->data['rates'] = json_encode($this->request->post['rates']);
		} elseif (!empty($warehouse_info)) {
			$this->data['rates'] = json_decode($warehouse_info['rates'], true);
		} else {
			$this->data['rates'] = '';
		}

		if (isset($this->request->post['duration'])) {
			$this->data['duration'] = json_encode($this->request->post['duration']);
		} elseif (!empty($warehouse_info)) {
			$this->data['duration'] = json_decode($warehouse_info['duration'], true);
		} else {
			$this->data['duration'] = '';
		}

		if (isset($this->request->post['status'])) {
			$this->data['status'] = $this->request->post['status'];
		} elseif (!empty($warehouse_info)) {
			$this->data['status'] = $warehouse_info['status'];
		} else {
			$this->data['status'] = '';
		}

		if (isset($this->request->get['id']) && $this->request->get['id']) {
			$this->data['id'] = $this->request->get['id'];
		} else {
			$this->data['id'] = 0;
		}

		$this->data['sellers'] = [];
		$queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");

        if($queryMultiseller->num_rows) {
            $this->initializer([
	            'sellers' => 'multiseller/seller'
	        ]);
			$this->data['sellers'] = $this->sellers->getSellers();
        }		

		$this->load->model('localisation/geo_zone');
		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		$this->data['currency'] = $this->config->get('config_currency');

		$this->data['token'] = $this->session->data['token'];
		// url to assign all products to this warehouse
		$this->data['assignAll'] = $this->url->link('module/warehouses/assignAll', 'token=' . $this->session->data['token'] . '&id=' . $this->data['id'], 'SSL');

		$this->children = array(
           'common/header',
           'common/footer',
        );
		$this->template = 'module/warehouses/warehouses_form.expand';
		$this->response->setOutput($this->render(TRUE));
	}

	// assigns product to given warehouse
	public function assignQuantity() {
		$json = array();

		if (isset($this->request->post['product_id']) && $this->request->post['product_id'] && isset($this->request->post['quantity']) && isset($this->request->post['warehouse'])) {

			$this->load->language('module/warehouses');

			$qty = $this->request->post['quantity'];
			$pid = $this->request->post['product_id'];
			$wrh = $this->request->post['warehouse'];

			if (ctype_digit($qty) && ($qty >= 0)) {
				$this->load->model('module/warehouses');
				$this->load->model('catalog/product');
				$product = $this->model_catalog_product->getProduct($pid);
				
				$assigned_quantity = $this->model_module_warehouses->getAssignedQuantity($pid, $wrh);
				
				$total_quantity = $assigned_quantity + $qty;

				if ($product['quantity'] < $total_quantity) {
					$json['error'] = $this->language->get('error_quantity'). ' '. ($product['quantity'] - $assigned_quantity);
				} else {
					$this->model_module_warehouses->assignQuantity($pid, $qty, $wrh);
					$json['success'] = $this->language->get('text_success_quantity');
				}
			} else {
				$json['error'] = $this->language->get('error_num_quantity');
			}

		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	// assigns all products to given warehouse
	public function assignAll() {

		$this->load->language('module/warehouses');

		if (isset($this->request->post['id']) && $this->request->post['id']) {
			$this->load->model('module/warehouses');
			$this->model_module_warehouses->assignAll($this->request->post['id']);

            $result_json['success'] = '1';
            $result_json['success_msg'] = $this->language->get('text_success');
	          
		}else{
			$result_json['success'] = '0';
		}

		$this->response->setOutput(json_encode($result_json));
	}

	// unassigns all products from given warehouse
	public function unAssignAll() {

		$this->load->language('module/warehouses');

		if (isset($this->request->post['id']) && $this->request->post['id']) {
			$this->load->model('module/warehouses');
			$this->model_module_warehouses->unAssignAll($this->request->post['id']);

            $result_json['success'] = '1';
            $result_json['success_msg'] = $this->language->get('text_success');
	          
		}else{
			$result_json['success'] = '0';
		}

		$this->response->setOutput(json_encode($result_json));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'module/warehouses')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen(trim($this->request->post['name'])) < 3) || (utf8_strlen(trim($this->request->post['name'])) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'module/warehouses')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function dtHandler() {
        $this->load->model('module/warehouses');

        $request = $_REQUEST;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            parse_str(html_entity_decode($this->request->post['filter']), $filterData);
            $filterData = $filterData['filter'];

        //            print_r($filterData);exit;
        }

        $filterData['search'] = null;
        if (isset($request['search']['value']) && strlen($request['search']['value']) > 0) {
            $filterData['search'] = $request['search']['value'];
        }

        $start = $request['start'];
        $length = $request['length'];

        $columns = array(
            0 => '',
            1 => 'id',
			2 => 'name',
            3 => 'status',
            4 => '',
        );

        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];

        $data = array(
            'sort' => $orderColumn,
            'order' => $orderType,
            'start' => $request['start'],
            'limit' => $request['length']
        );

        $return = $this->model_module_warehouses->getWarehousesDt($data, $filterData);
        $finalRecords = [];

        $records = $return['data'];
        $totalData = $return['total'];
        $totalFiltered = $return['totalFiltered'];

        foreach ($records as $result) {
			$finalRecords[] = array(
				'id' => $result['id'],
				'name'          => $result['name'],
				'address'          => $result['address'],
				'status'        => $result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
				'label'          => $result['status'] ? 'success' : 'danger'
			);
		}

        $json_data = array(
            "draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($totalData), // total number of records
            "recordsFiltered" => $totalFiltered, // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $finalRecords,   // total data array
        );
        $this->response->setOutput(json_encode($json_data));
        return;
    }

    public function dtDelete()
    {
        $this->load->model('module/warehouses');
        $this->load->language('module/warehouses');

        if ( !isset($this->request->post['id']) )
        {
            return false;
        }

        $id_s = $this->request->post['id'];


        if ( is_array($id_s) )
        {

            foreach ($id_s as $id)
            {
                if ( $id != 1 && $this->model_module_warehouses->deleteWarehouse( (int) $id ) )
                {
                    $result_json['success'] = '1';
                    $result_json['success_msg'] = $this->language->get('text_success');
                }
                else
                {
                    $result_json['success'] = '0';
                }
            }
        }
        else
        {
            $id = (int) $id_s;

            if ( $id != 1 && $this->model_module_warehouses->deleteWarehouse( $id ) )
            {
                $result_json['success'] = '1';
                $result_json['success_msg'] = $this->language->get('text_success');
            }
            else
            {
                $result_json['success'] = '0';
            }
        }

        $this->response->setOutput(json_encode($result_json));
        return;

    }

    public function loadProducts() {
		$this->load->model('module/warehouses');
		$this->load->model('catalog/product');

		if (isset($this->request->post['warehouse']) && $this->request->post['warehouse']) {
			$warehouse = $this->request->post['warehouse'];
		} else {
			$warehouse = 0;
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

		if (isset($this->request->post['filter_wrs_status']) && !($this->request->post['filter_wrs_status'] == '')) {
			$filter_wrs_status = $this->request->post['filter_wrs_status'];
		} else {
			$filter_wrs_status = null;
		}

		$json['products'] = array();

		$filter_data = array(
			'filter_name'	    => $filter_name,
			'filter_model'	    => $filter_model,
			'filter_price'	    => $filter_price,
			'filter_quantity'   => $filter_quantity,
			'filter_assign'     => $filter_assign,
			'filter_status'     => $filter_status,
			'filter_wrs_status' => $filter_wrs_status,
			'sort'              => $sort,
			'order'             => $order,
			'start'             => $start,
			'limit'             => $this->config->get('config_limit_admin')
		);

		$this->load->model('tool/image');

		if ($warehouse) {
			$this->load->model('catalog/product');
			$json['product_total'] = $this->model_catalog_product->getTotalProducts($filter_data);

			$results = $this->model_catalog_product->getProducts($filter_data);
		} else {
			$json['product_total'] = $this->model_module_warehouses->getTotalProducts($filter_data);

			$results = $this->model_module_warehouses->getProducts($filter_data);
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

			if ($warehouse) {
				$warehouse_data = $this->model_module_warehouses->getWarehouseProductData($result['product_id'], $warehouse);
				$wrs_quantity = $warehouse_data['quantity'];
				$wrs_status = $warehouse_data['status'];
				//$result['barcode'] = '';
			} else {
				$wrs_quantity = $result['wrs_quantity'];
				$wrs_status = $result['wrs_status'];
			}

            $barcode = $result['barcode'] ?? '';
            $barcode_image = 0;
            if ($result['barcode'] != '') {
                $barcodeGenerator = (new BarcodeGenerator())
                    ->setType($this->config->get('config_barcode_type'))
                    ->setBarcode($result['barcode']);

                $barcode_image = $barcodeGenerator->generate();
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
                'barcode_image'=> $barcode_image,
				'wrs_quantity' => $wrs_quantity ? $wrs_quantity : 0,
				'status'       => ($result['status']) ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
				'wrs_status'   => $wrs_status
			);
		}

		if (count($json['products'])) {
			$json['success'] = 'Success';
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function changeStatus() {
		$json = array();
		$this->load->language('module/warehouses');

		if (!$this->user->hasPermission('modify', 'module/warehouses')) {
			$json['error'] = 'aa';
		}

		if (!$json && isset($this->request->post['product_id']) && $this->request->post['product_id'] && isset($this->request->post['status']) && isset($this->request->post['warehouse'])) {
			$this->load->model('module/warehouses');
			$this->model_module_warehouses->changeStatus($this->request->post['product_id'], $this->request->post['status'], $this->request->post['warehouse']);
			$json['success'] = $this->language->get('text_success_status');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

    function autocomplete(){
        if (isset($this->request->get['filter_name'])) {
            if (isset($this->request->get['filter_name'])) {
                $filter_name = $this->request->get['filter_name'];
            } else {
                $filter_name = '';
            }

            if (isset($this->request->get['limit'])) {
                $limit = $this->request->get['limit'];
            } else {
                $limit = 20;
            }

            $data = array(
                'filter_name' => $filter_name,
                'start' => 0,
                'limit' => $limit
            );

            $json = array();
            $this->load->model('module/warehouses');
            $warehouses = $this->model_module_warehouses->getWarehouses($data);

            foreach ($warehouses as $wr) {
                $json[] = array(
                    'id' => $wr['id'],
                    'name' => $wr['name'],
                );
            }
            $this->response->setOutput(json_encode($json));
        }
    }

}
