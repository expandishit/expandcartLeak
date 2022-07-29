<?php
class ControllerWkposOutlets extends Controller {
	private $error = array();

	public function index() {
		$this->load->model('wkpos/wkpos');
		if(!$this->model_wkpos_wkpos->is_installed())
			$this->response->redirect($this->url->link('wkpos/main', '', true));

		$this->load->language('wkpos/outlet');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('wkpos/outlet');

		$this->getList();
	}

	public function add() {
		$this->load->model('wkpos/wkpos');
		if(!$this->model_wkpos_wkpos->is_installed())
			$this->response->redirect($this->url->link('wkpos/main', '', true));

		$this->load->language('wkpos/outlet');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('wkpos/outlet');

		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {

			if($this->validate()){
				$newOutlet = $this->model_wkpos_outlet->addOutlet($this->request->post);

				$result_json['success'] = '1';
	            $result_json['success_msg'] = $this->language->get('text_success');

                $result_json['redirect'] = 1;
                $result_json['to'] = 'wkpos/outlets/edit?outlet_id='.$newOutlet.'#tab/products';

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
		$this->model_wkpos_wkpos->fixQuantities($this->request->get['outlet_id']);
		$this->load->language('wkpos/outlet');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('wkpos/outlet');

		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {

			if($this->validate()){
				$this->model_wkpos_outlet->editOutlet($this->request->get['outlet_id'], $this->request->post);
				
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
		$this->load->language('wkpos/outlet');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('wkpos/outlet');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $outlet_id) {
				$this->model_wkpos_outlet->deleteOutlet($outlet_id);
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

			$this->response->redirect($this->url->link('wkpos/outlets', 'token=' . $this->session->data['token'] . $url, 'SSL'));
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
			'href' => $this->url->link('wkpos/outlets', 'token=' . $this->session->data['token'] . $url, 'SSL')
		);

		$this->data['add'] = $this->url->link('wkpos/outlets/add', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('wkpos/outlets/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['outlets'] = array();

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
		$this->template = 'wkpos/outlet_list.expand';
		$this->response->setOutput($this->render(TRUE));

		/*$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('wkpos/outlet_list.tpl', $data));*/
	}

	protected function getForm() {
		$this->data = array_merge($this->load->language('catalog/product'), $this->load->language('wkpos/products'), $this->load->language('wkpos/outlet'));

		$this->data['text_form'] = !isset($this->request->get['outlet_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');


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
			'href' => $this->url->link('wkpos/outlets', 'token=' . $this->session->data['token'], true)
		);
		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('wkpos/outlets', 'token=' . $this->session->data['token'] . $url, 'SSL')
		);

		if (!isset($this->request->get['outlet_id'])) {
			$this->data['action'] = $this->url->link('wkpos/outlets/add', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('wkpos/outlets/edit', 'token=' . $this->session->data['token'] . '&outlet_id=' . $this->request->get['outlet_id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('wkpos/outlets', 'token=' . $this->session->data['token'] . $url, 'SSL');

		if (isset($this->request->get['outlet_id']) && $this->request->server['REQUEST_METHOD'] != 'POST') {
			$outlet_info = $this->model_wkpos_outlet->getOutlet($this->request->get['outlet_id']);
		}

		if (isset($this->request->post['name'])) {
			$this->data['name'] = $this->request->post['name'];
		} elseif (!empty($outlet_info)) {
			$this->data['name'] = $outlet_info['name'];
		} else {
			$this->data['name'] = '';
		}

		if (isset($this->request->post['address'])) {
			$this->data['address'] = $this->request->post['address'];
		} elseif (!empty($outlet_info)) {
			$this->data['address'] = $outlet_info['address'];
		} else {
			$this->data['address'] = '';
		}

		if (isset($this->request->post['country_id'])) {
			$this->data['country_id'] = $this->request->post['country_id'];
		} elseif (!empty($outlet_info)) {
			$this->data['country_id'] = $outlet_info['country_id'];
		} else {
			$this->data['country_id'] = '';
		}

		if (isset($this->request->post['zone_id'])) {
			$this->data['zone_id'] = $this->request->post['zone_id'];
		} elseif (!empty($outlet_info)) {
			$this->data['zone_id'] = $outlet_info['zone_id'];
		} else {
			$this->data['zone_id'] = '';
		}

		if (isset($this->request->post['status'])) {
			$this->data['status'] = $this->request->post['status'];
		} elseif (!empty($outlet_info)) {
			$this->data['status'] = $outlet_info['status'];
		} else {
			$this->data['status'] = '';
		}

		if (isset($this->request->get['outlet_id']) && $this->request->get['outlet_id']) {
			$this->data['outlet_id'] = $this->request->get['outlet_id'];
		} else {
			$this->data['outlet_id'] = 0;
		}

		$this->load->model('localisation/country');

		$this->data['countries'] = $this->model_localisation_country->getCountries();

		$this->data['token'] = $this->session->data['token'];
		// url to assign all products to this outlet
		$this->data['assignAll'] = $this->url->link('wkpos/outlets/assignAll', 'token=' . $this->session->data['token'] . '&outlet_id=' . $this->data['outlet_id'], 'SSL');

		$this->children = array(
           'common/header',
           'common/footer',
        );
		$this->template = 'wkpos/outlet_form.expand';
		$this->response->setOutput($this->render(TRUE));
		
		/*$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('wkpos/outlet_form.tpl', $data));*/
	}
	// assigns all products to given outlet
	public function assignAll() {
		$this->load->language('wkpos/outlet');

		if (isset($this->request->get['outlet_id']) && $this->request->get['outlet_id']) {
			$this->load->model('wkpos/outlet');
			$this->model_wkpos_outlet->assignAll($this->request->get['outlet_id']);

			$this->session->data['success'] = $this->language->get('text_assign_all');
		}

		$this->response->redirect($this->url->link('wkpos/outlets', 'token=' . $this->session->data['token'], 'SSL'));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'wkpos/outlets')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen(trim($this->request->post['name'])) < 3) || (utf8_strlen(trim($this->request->post['name'])) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		if ($this->request->post['country_id'] == '') {
			$this->error['country'] = $this->language->get('error_country');
		}

		if (!isset($this->request->post['zone_id']) || $this->request->post['zone_id'] == '' || !is_numeric($this->request->post['zone_id'])) {
			$this->error['zone'] = $this->language->get('error_zone');
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'wkpos/outlets')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		$this->load->model('wkpos/user');

		foreach ($this->request->post['selected'] as $outlet_id) {
			$user_total = $this->model_wkpos_user->getTotalUsersByGroupId($outlet_id);

			if ($user_total) {
				$this->error['warning'] = sprintf($this->language->get('error_user'), $user_total);
			}
		}

		return !$this->error;
	}

	public function dtHandler() {
        $this->load->model('wkpos/outlet');

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
            1 => 'outlet_id',
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

        $return = $this->model_wkpos_outlet->getOutletsDt($data, $filterData);
        $finalRecords = [];

        $records = $return['data'];
        $totalData = $return['total'];
        $totalFiltered = $return['totalFiltered'];

        foreach ($records as $result) {
			$finalRecords[] = array(
				'outlet_id' => $result['outlet_id'],
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
        $this->load->model('wkpos/outlet');
        $this->load->language('wkpos/outlet');

        if ( !isset($this->request->post['id']) )
        {
            return false;
        }

        $id_s = $this->request->post['id'];


        if ( is_array($id_s) )
        {

            foreach ($id_s as $outlet_id)
            {

                if ( $this->model_wkpos_outlet->deleteOutlet( (int) $outlet_id ) )
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
            $outlet_id = (int) $id_s;

            if ( $this->model_wkpos_outlet->deleteOutlet( $outlet_id ) )
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
