<?php
class ControllerWkposSuppliers extends Controller {
	private $error = array();

	public function index() {
		$this->load->model('wkpos/wkpos');
		if(!$this->model_wkpos_wkpos->is_installed())
			$this->response->redirect($this->url->link('wkpos/main', '', true));

		$this->load->language('wkpos/supplier');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('wkpos/supplier');

		$this->getList();
	}

	public function add() {
		$this->load->model('wkpos/wkpos');
		if(!$this->model_wkpos_wkpos->is_installed())
			$this->response->redirect($this->url->link('wkpos/main', '', true));

		$this->load->language('wkpos/supplier');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('wkpos/supplier');

		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {

			if($this->validateForm()){
				$newSupplier = $this->model_wkpos_supplier->addSupplier($this->request->post);

				$result_json['success'] = '1';
	            $result_json['success_msg'] = $this->language->get('text_success');

                $result_json['redirect'] = 1;
                $result_json['to'] = 'wkpos/suppliers/edit?supplier_id='.$newSupplier.'#tab/products';

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
		$this->load->model('wkpos/wkpos');
		if(!$this->model_wkpos_wkpos->is_installed())
			$this->response->redirect($this->url->link('wkpos/main', '', true));
		
		$this->load->language('wkpos/supplier');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('wkpos/supplier');

		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			if($this->validateForm()){
				$this->model_wkpos_supplier->editSupplier($this->request->get['supplier_id'], $this->request->post);

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
		$this->load->language('wkpos/supplier');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('wkpos/supplier');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $supplier_id) {
				$this->model_wkpos_supplier->deleteSupplier($supplier_id);
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

			$this->response->redirect($this->url->link('wkpos/suppliers', 'token=' . $this->session->data['token'] . $url, 'SSL'));
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
			'href' => $this->url->link('wkpos/main', 'token=' . $this->session->data['token'], true)
		);
		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('wkpos/suppliers', 'token=' . $this->session->data['token'] . $url, 'SSL')
		);

		$this->data['add'] = $this->url->link('wkpos/suppliers/add', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('wkpos/suppliers/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_list'] = $this->language->get('text_list');
		$this->data['text_no_results'] = $this->language->get('text_no_results');
		$this->data['text_confirm'] = $this->language->get('text_confirm');

		$this->data['column_sname'] = $this->language->get('column_sname');
		$this->data['column_sstatus'] = $this->language->get('column_sstatus');
		$this->data['column_email'] = $this->language->get('column_email');
		$this->data['column_date'] = $this->language->get('column_date');
		$this->data['column_company'] = $this->language->get('column_company');
		$this->data['column_website'] = $this->language->get('column_website');
		$this->data['column_action'] = $this->language->get('column_action');

		$this->data['button_add'] = $this->language->get('text_add');
		$this->data['button_edit'] = $this->language->get('button_edit');
		$this->data['button_delete'] = $this->language->get('button_delete');

		$this->children = array(
           'common/header',
           'common/footer',
        );
		$this->template = 'wkpos/supplier_list.expand';
		$this->response->setOutput($this->render(TRUE));

		/*$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('wkpos/supplier_list.tpl', $data));*/
	}

	protected function getForm() {
		$this->data = array_merge($this->load->language('catalog/product'), $this->load->language('wkpos/products'), $this->load->language('wkpos/supplier'));

		$this->data['text_form'] = !isset($this->request->get['supplier_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		$error_array = array(
			'firstname',
			'lastname',
			'email',
			'telephone',
			'address',
			'city',
			'postcode',
			'country',
			'zone',
			'outlets'
			);

		foreach ($error_array as $error) {
			if (isset($this->error[$error])) {
				$this->data['error_' . $error] = $this->error[$error];
			} else {
				$this->data['error_' . $error] = '';
			}
		}

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

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
		);
		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_heading_main'),
			'href' => $this->url->link('wkpos/main', 'token=' . $this->session->data['token'], true)
		);
		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_list'),
			'href' => $this->url->link('wkpos/suppliers', 'token=' . $this->session->data['token'], true)
		);
		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('wkpos/suppliers', 'token=' . $this->session->data['token'] . $url, 'SSL')
		);

		if (!isset($this->request->get['supplier_id'])) {
			$this->data['action'] = $this->url->link('wkpos/suppliers/add', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('wkpos/suppliers/edit', 'token=' . $this->session->data['token'] . '&supplier_id=' . $this->request->get['supplier_id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('wkpos/suppliers', 'token=' . $this->session->data['token'] . $url, 'SSL');

		if (isset($this->request->get['supplier_id']) && $this->request->server['REQUEST_METHOD'] != 'POST') {
			$supplier_info = $this->model_wkpos_supplier->getSupplier($this->request->get['supplier_id']);
		}

		$post_array = array(
			'firstname',
			'lastname',
			'email',
			'telephone',
			'company',
			'website',
			'extra_info',
			'address',
			'city',
			'postcode',
			'country_id',
			'zone_id',
			'status'
			);

		foreach ($post_array as $post) {
			if (isset($this->request->post[$post])) {
				$this->data[$post] = trim($this->request->post[$post]);
			} elseif (!empty($supplier_info)) {
				$this->data[$post] = $supplier_info[$post];
			} else {
				$this->data[$post] = '';
			}
		}

		if (isset($this->request->post['outlets'])) {
			$this->data['outlets'] = $this->request->post['outlets'];
		} elseif (!empty($supplier_info)) {
			$this->data['outlets'] = json_decode($supplier_info['outlets']);
		} else {
			$this->data['outlets'] = array();
		}

		if (isset($supplier_info) && $supplier_info) {
			$this->data['supplier_id'] = $this->request->get['supplier_id'];
		} else {
			$this->data['supplier_id'] = 0;
		}

		if (isset($this->request->get['tab']) && $this->request->get['tab'] == 'product') {
			$this->data['tab'] = 1;
		} else {
			$this->data['tab'] = 0;
		}

		$this->load->model('localisation/country');

		$this->data['countries'] = $this->model_localisation_country->getCountries();

		$this->load->model('wkpos/outlet');

		$this->data['outlet_array'] = $this->model_wkpos_outlet->getOutlets();

		$this->data['token'] = $this->session->data['token'];

		$this->children = array(
           'common/header',
           'common/footer',
        );
		$this->template = 'wkpos/supplier_form.expand';
		$this->response->setOutput($this->render(TRUE));
		
		/*$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('wkpos/supplier_form.tpl', $data));*/
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'wkpos/suppliers')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen(trim($this->request->post['firstname'])) < 1) || (utf8_strlen(trim($this->request->post['firstname'])) > 32)) {
			$this->error['firstname'] = $this->language->get('error_firstname');
		}

		if ((utf8_strlen(trim($this->request->post['lastname'])) < 1) || (utf8_strlen(trim($this->request->post['lastname'])) > 32)) {
			$this->error['lastname'] = $this->language->get('error_lastname');
		}

		if ((utf8_strlen(trim($this->request->post['email'])) > 96) || !filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
			$this->error['email'] = $this->language->get('error_email');
		}

		$this->load->model('wkpos/supplier');

		$supplier_info = $this->model_wkpos_supplier->getSupplierByEmail($this->request->post['email']);

		if (!isset($this->request->get['supplier_id'])) {
			if ($supplier_info) {
				$this->error['warning'] = $this->language->get('error_exists');
			}
		} else {
			if ($supplier_info && ($this->request->get['supplier_id'] != $supplier_info['supplier_id'])) {
				$this->error['warning'] = $this->language->get('error_exists');
			}
		}

		if ((utf8_strlen(trim($this->request->post['telephone'])) < 3) || (utf8_strlen($this->request->post['telephone']) > 32)) {
			$this->error['telephone'] = $this->language->get('error_telephone');
		}

		if ((utf8_strlen(trim($this->request->post['address'])) < 3) || (utf8_strlen(trim($this->request->post['address'])) > 128)) {
			$this->error['address'] = $this->language->get('error_address');
		}

		if ((utf8_strlen(trim($this->request->post['city'])) < 2) || (utf8_strlen(trim($this->request->post['city'])) > 128)) {
			$this->error['city'] = $this->language->get('error_city');
		}

		$this->load->model('localisation/country');

		$country_info = $this->model_localisation_country->getCountry($this->request->post['country_id']);

		if ($country_info && $country_info['postcode_required'] && (utf8_strlen(trim($this->request->post['postcode'])) < 2 || utf8_strlen(trim($this->request->post['postcode'])) > 10)) {
			$this->error['postcode'] = $this->language->get('error_postcode');
		}

		if ($this->request->post['country_id'] == '') {
			$this->error['country'] = $this->language->get('error_country');
		}

		if (!isset($this->request->post['zone_id']) || $this->request->post['zone_id'] == '' || !is_numeric($this->request->post['zone_id'])) {
			$this->error['zone'] = $this->language->get('error_zone');
		}

		if (!isset($this->request->post['outlets']) || !$this->request->post['outlets']) {
			$this->error['outlets'] = $this->language->get('error_outlets');
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'wkpos/suppliers')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function loadProducts() {
		$this->load->model('wkpos/supplier');
		$this->load->model('catalog/product');

		if (isset($this->request->post['supplier']) && $this->request->post['supplier']) {
			$supplier = $this->request->post['supplier'];
		} else {
			$supplier = 0;
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

		if (isset($this->request->post['filter_status']) && !($this->request->post['filter_status'] == '')) {
			$filter_status = $this->request->post['filter_status'];
		} else {
			$filter_status = null;
		}

		$json['products'] = array();

		$filter_data = array(
			'filter_name'	      => $filter_name,
			'filter_model'	    => $filter_model,
			'filter_price'	    => $filter_price,
			'filter_status'     => $filter_status,
			'sort'              => $sort,
			'order'             => $order,
			'start'             => $start,
			'limit'             => $this->config->get('config_limit_admin')
		);

		$this->load->model('tool/image');
		$this->load->model('catalog/product');
		$json['product_total'] = $this->model_catalog_product->getTotalProducts($filter_data);

		$results = $this->model_catalog_product->getProducts($filter_data);

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

			$supplier_data = $this->model_wkpos_supplier->getSupplierProductData($result['product_id'], $supplier);
			$min_quantity = $supplier_data['min_quantity'];
			$max_quantity = $supplier_data['max_quantity'];
			$sup_status = $supplier_data['status'];

			$json['products'][] = array(
				'product_id'   => $result['product_id'],
				'image'        => $image,
				'name'         => $result['name'],
				'model'        => $result['model'],
				'price'        => $result['price'],
				'special'      => $special,
				'quantity'     => $result['quantity'],
				'min_quantity' => $min_quantity,
				'max_quantity' => $max_quantity,
				'status'       => ($result['status']) ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
				'sup_status'   => $sup_status
			);
		}

		if (count($json['products'])) {
			$json['success'] = 'Success';
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function assignQuantity() {
		$json = array();
		$this->load->language('wkpos/products');

		if (!$this->user->hasPermission('modify', 'wkpos/products')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json && isset($this->request->post['product_id']) && $this->request->post['product_id'] && isset($this->request->post['quantity']) && isset($this->request->post['qtype']) && isset($this->request->post['supplier']) && (($this->request->post['qtype'] == 'min') || ($this->request->post['qtype'] == 'max'))) {
			if (ctype_digit($this->request->post['quantity']) && ($this->request->post['quantity'] >= 0)) {
				$this->load->model('wkpos/supplier');

				$this->model_wkpos_supplier->assignQuantity($this->request->post['product_id'], $this->request->post['quantity'], $this->request->post['supplier'], $this->request->post['qtype']);
				$json['success'] = $this->language->get('text_success');
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

		if (!$json && isset($this->request->post['product_id']) && $this->request->post['product_id'] && isset($this->request->post['status']) && isset($this->request->post['supplier'])) {
			$this->load->model('wkpos/supplier');
			$this->model_wkpos_supplier->changeStatus($this->request->post['product_id'], $this->request->post['status'], $this->request->post['supplier']);
			$json['success'] = $this->language->get('text_success_status');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function dtHandler() {
        $this->load->model('wkpos/supplier');

        $request = $_REQUEST;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            parse_str(html_entity_decode($this->request->post['filter']), $filterData);
            $filterData = $filterData['filter'];
        }

        $filterData['search'] = null;
        if (isset($request['search']['value']) && strlen($request['search']['value']) > 0) {
            $filterData['search'] = $request['search']['value'];
        }

        $start = $request['start'];
        $length = $request['length'];

        $columns = array(
            0 => '',
            1 => 'supplier_id',
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

        $return = $this->model_wkpos_supplier->getSuppliersDt($data, $filterData);
        $finalRecords = [];
        
        $records = $return['data'];
        $totalData = $return['total'];
        $totalFiltered = $return['totalFiltered'];

        foreach ($records as $result) {
			$finalRecords[] = array(
				'supplier_id' => $result['supplier_id'],
				'name'        => $result['name'],
				'email'       => $result['email'],
				'company'     => $result['company'],
				'website'     => $result['website'],
				'date_added'  => $result['date_added'],
				'status'      => $result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
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
        $this->load->model('wkpos/supplier');
        $this->load->language('wkpos/supplier');

        if ( !isset($this->request->post['id']) )
        {
            return false;
        }

        $id_s = $this->request->post['id'];


        if ( is_array($id_s) )
        {

            foreach ($id_s as $supplier_id)
            {

                if ( $this->model_wkpos_supplier->deleteSupplier( (int) $supplier_id ) )
                {
                    $result_json['success'] = '1';
                    $result_json['success_msg'] = $this->language->get('text_success');
                }
                else
                {
                    $result_json['success'] = '0';
                    break;
                }
            }
        }
        else
        {
            $supplier_id = (int) $id_s;

            if ( $this->model_wkpos_supplier->deleteSupplier( $supplier_id ) )
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
}
