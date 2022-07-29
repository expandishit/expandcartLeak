<?php
class ControllerWkposSupplyRequest extends Controller {
	private $error = array();

	public function index() {
		$this->load->model('wkpos/wkpos');
		if(!$this->model_wkpos_wkpos->is_installed())
			$this->response->redirect($this->url->link('wkpos/main', '', true));

		$this->load->language('wkpos/supply_request');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('wkpos/supply_request');

		$this->getList();
	}

	public function add() {
		$this->load->model('wkpos/wkpos');
		if(!$this->model_wkpos_wkpos->is_installed())
			$this->response->redirect($this->url->link('wkpos/main', '', true));

		$this->load->language('wkpos/supply_request');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('wkpos/supply_request');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_wkpos_supply_request->addSupplyRequest($this->request->post);

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

			$this->response->redirect($this->url->link('wkpos/supply_request', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->model('wkpos/wkpos');
		if(!$this->model_wkpos_wkpos->is_installed())
			$this->response->redirect($this->url->link('wkpos/main', '', true));
		
		$this->load->language('wkpos/supply_request');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('wkpos/supply_request');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_wkpos_supply_request->editSupplyRequest($this->request->get['supply_request_id'], $this->request->post);

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

			$this->response->redirect($this->url->link('wkpos/supply_request', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function update() {
		if (isset($this->request->get['request_id']) && $this->request->get['request_id']) {
			$this->load->language('wkpos/supply_request');
			$this->load->model('wkpos/supply_request');
			$this->model_wkpos_supply_request->updateStatus($this->request->get['request_id']);
			$this->session->data['success'] = $this->language->get('text_success_update');
		}
		$this->response->redirect($this->url->link('wkpos/supply_request', 'token=' . $this->session->data['token'], 'SSL'));
	}

	public function cancel() {
		$this->load->language('wkpos/supply_request');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('wkpos/supply_request');

		if (isset($this->request->post['selected']) && $this->validateCancel()) {
			foreach ($this->request->post['selected'] as $supply_request_id) {
				$this->model_wkpos_supply_request->cancelSupplyRequest($supply_request_id);
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

			$this->response->redirect($this->url->link('wkpos/supply_request', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	protected function getList() {
		$this->data = $this->load->language('wkpos/supply_request');

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
			'href' => $this->url->link('wkpos/supply_request', 'token=' . $this->session->data['token'] . $url, 'SSL')
		);

		$this->data['add'] = $this->url->link('wkpos/supply_request/add', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['cancel'] = $this->url->link('wkpos/supply_request/cancel', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['typ'] = $this->request->get['typ'] ? $this->request->get['typ'] : 'p';
	
		$this->children = array(
           'common/header',
           'common/footer',
        );
		$this->template = 'wkpos/supply_request_list.expand';
		$this->response->setOutput($this->render(TRUE));

		/*$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('wkpos/supply_request_list.tpl', $data));*/
	}

	protected function getForm() {
		$this->data = array_merge($this->load->language('catalog/product'), $this->load->language('wkpos/products'), $this->load->language('wkpos/supply_request'));

		$this->data['text_form'] = !isset($this->request->get['supply_request_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$this->data['error_name'] = $this->error['name'];
		} else {
			$this->data['error_name'] = '';
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
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('wkpos/supply_request', 'token=' . $this->session->data['token'] . $url, 'SSL')
		);

		if (!isset($this->request->get['supply_request_id'])) {
			$this->data['action'] = $this->url->link('wkpos/supply_request/add', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('wkpos/supply_request/edit', 'token=' . $this->session->data['token'] . '&supply_request_id=' . $this->request->get['supply_request_id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('wkpos/supply_request', 'token=' . $this->session->data['token'] . $url, 'SSL');

		if (isset($this->request->get['supply_request_id']) && $this->request->server['REQUEST_METHOD'] != 'POST') {
			$supply_request_info = $this->model_wkpos_supply_request->getSupplyRequest($this->request->get['supply_request_id']);
		}

		if (isset($this->request->post['name'])) {
			$this->data['name'] = $this->request->post['name'];
		} elseif (!empty($supply_request_info)) {
			$this->data['name'] = $supply_request_info['name'];
		} else {
			$this->data['name'] = '';
		}

		if (isset($this->request->post['address'])) {
			$this->data['address'] = $this->request->post['address'];
		} elseif (!empty($supply_request_info)) {
			$this->data['address'] = $supply_request_info['address'];
		} else {
			$this->data['address'] = '';
		}

		if (isset($this->request->post['status'])) {
			$this->data['status'] = $this->request->post['status'];
		} elseif (!empty($supply_request_info)) {
			$this->data['status'] = $supply_request_info['status'];
		} else {
			$this->data['status'] = '';
		}

		if (isset($supply_request_info) && $supply_request_info) {
			$this->data['supply_request_id'] = $this->request->get['supply_request_id'];
		} else {
			$this->data['supply_request_id'] = 0;
		}

		$this->data['token'] = $this->session->data['token'];

		$this->children = array(
           'common/header',
           'common/footer',
        );
		$this->template = 'wkpos/supply_request_form.expand';
		$this->response->setOutput($this->render(TRUE));
		
		/*$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('wkpos/supply_request_form.tpl', $data));*/
	}

	public function view() {
		$json = array();

		if (isset($this->request->get['request_id']) && $this->request->get['request_id']) {
			$this->load->model('wkpos/supply_request');
			$json['supply_info'] = $this->model_wkpos_supply_request->getRequestInfo($this->request->get['request_id']);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'wkpos/supply_request')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		return !$this->error;
	}

	protected function validateCancel() {
		if (!$this->user->hasPermission('modify', 'wkpos/supply_request')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function dtHandler() {
        $this->load->model('wkpos/supply_request');
        $this->data = $this->load->language('wkpos/supply_request');

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

        if (isset($request['typ'])) {
            $filterData['typ'] = $request['typ'];
        }

        $start = $request['start'];
        $length = $request['length'];

        $columns = array(
            0 => '',
            1 => 'request_id',
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

        $return = $this->model_wkpos_supply_request->getRequestsDt($data, $filterData);
        $finalRecords = [];
        $records = $return['data'];
        $totalData = $return['total'];
        $totalFiltered = $return['totalFiltered'];

        foreach ($records as $result) {
        	if ($result['status']) {
				$status = $this->language->get('text_fulfilled');
			} else {
                if ($result['cancel']) {
                    $status = $this->language->get('text_cancelled');
                }
				$status = $this->language->get('text_unfulfilled');
			}



			$finalRecords[] = array(
				'request_id'    => $result['request_id'],
				'name'          => $result['name'],
				'status_text'   => $status,
				'status'        => $result['status'],
				'cancel'        => $result['cancel'],
				'date_added'    => $result['date_added'],
				'comment'       => $result['comment'],
				'update'        => $this->url->link('wkpos/supply_request/update', 'token=' . $this->session->data['token'] . '&request_id=' . $result['request_id'] . $url, 'SSL')
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

    public function dtCancel()
    {
        $this->load->model('wkpos/supply_request');
        $this->load->language('wkpos/supply_request');

        if ( !isset($this->request->post['id']) )
        {
            return false;
        }

        $id_s = $this->request->post['id'];


        if ( is_array($id_s) )
        {

            foreach ($id_s as $request_id)
            {

                if ( $this->model_wkpos_supply_request->cancelSupplyRequest((int) $request_id) )
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
            $request_id = (int) $id_s;

            if ( $this->model_wkpos_supply_request->cancelSupplyRequest((int) $request_id) )
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

    public function dtUpdate()
    {
        $this->load->model('wkpos/supply_request');
        $this->load->language('wkpos/supply_request');

        if ( !isset($this->request->post['id']) )
        {
            return false;
        }

        $id_s = $this->request->post['id'];


        if ( is_array($id_s) )
        {

            foreach ($id_s as $request_id)
            {

                if ( $this->model_wkpos_supply_request->updateStatus((int) $request_id) )
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
            $request_id = (int) $id_s;

            if ( $this->model_wkpos_supply_request->updateStatus((int) $request_id) )
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
