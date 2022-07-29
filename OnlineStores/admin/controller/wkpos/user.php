<?php
class ControllerWkposUser extends Controller {
	private $error = array();

	public function index() {
		$this->load->model('wkpos/wkpos');
		if(!$this->model_wkpos_wkpos->is_installed())
			$this->response->redirect($this->url->link('wkpos/main', '', true));

		$this->load->language('wkpos/user');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('wkpos/user');

		$this->getList();
	}

	public function add() {
		$this->load->model('wkpos/wkpos');
		if(!$this->model_wkpos_wkpos->is_installed())
			$this->response->redirect($this->url->link('wkpos/main', '', true));

		$this->load->language('wkpos/user');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('wkpos/user');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') ) {
			if($this->validateForm()){
				$this->model_wkpos_user->addUser($this->request->post);

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
		$this->load->model('wkpos/wkpos');
		if(!$this->model_wkpos_wkpos->is_installed())
			$this->response->redirect($this->url->link('wkpos/main', '', true));
		
		$this->load->language('wkpos/user');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('wkpos/user');

		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			
			if($this->validateForm()){
				$this->model_wkpos_user->editUser($this->request->get['user_id'], $this->request->post);

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
		$this->load->language('wkpos/user');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('wkpos/user');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $user_id) {
				$this->model_wkpos_user->deleteUser($user_id);
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

			$this->response->redirect($this->url->link('wkpos/user', 'token=' . $this->session->data['token'] . $url, 'SSL'));
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
			'href' => $this->url->link('wkpos/user', 'token=' . $this->session->data['token'] . $url, 'SSL')
		);

		$this->data['add'] = $this->url->link('wkpos/user/add', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('wkpos/user/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_list'] = $this->language->get('text_list');
		$this->data['text_no_results'] = $this->language->get('text_no_results');
		$this->data['text_confirm'] = $this->language->get('text_confirm');

		$this->data['column_username'] = $this->language->get('column_username');
		$this->data['column_useroutlet'] = $this->language->get('column_useroutlet');
		$this->data['column_status'] = $this->language->get('column_status');
		$this->data['column_date_added'] = $this->language->get('column_date_added');
		$this->data['column_action'] = $this->language->get('column_action');

		$this->data['button_add'] = $this->language->get('text_add');
		$this->data['button_edit'] = $this->language->get('button_edit');
		$this->data['button_delete'] = $this->language->get('button_delete');

		$this->children = array(
           'common/header',
           'common/footer',
        );
		$this->template = 'wkpos/user_list.expand';
		$this->response->setOutput($this->render(TRUE));

		/*$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('wkpos/user_list.tpl', $data));*/
	}

	protected function getForm() {
		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_form'] = !isset($this->request->get['user_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_settings'] = $this->language->get('text_settings');
		
		$this->data['entry_username'] = $this->language->get('entry_username');
		$this->data['entry_outlet'] = $this->language->get('entry_outlet');
		$this->data['entry_password'] = $this->language->get('entry_password');
		$this->data['entry_confirm'] = $this->language->get('entry_confirm');
		$this->data['entry_firstname'] = $this->language->get('entry_firstname');
		$this->data['entry_lastname'] = $this->language->get('entry_lastname');
		$this->data['entry_email'] = $this->language->get('entry_email');
		$this->data['entry_image'] = $this->language->get('entry_image');
		$this->data['entry_status'] = $this->language->get('entry_status');

		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');

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
			'href' => $this->url->link('wkpos/user', 'token=' . $this->session->data['token'], true)
		);
		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('wkpos/user', 'token=' . $this->session->data['token'] . $url, 'SSL')
		);

		if (!isset($this->request->get['user_id'])) {
			$this->data['action'] = $this->url->link('wkpos/user/add', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('wkpos/user/edit', 'token=' . $this->session->data['token'] . '&user_id=' . $this->request->get['user_id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('wkpos/user', 'token=' . $this->session->data['token'] . $url, 'SSL');

		if (isset($this->request->get['user_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$user_info = $this->model_wkpos_user->getUser($this->request->get['user_id']);
		}

		if (isset($this->request->post['username'])) {
			$this->data['username'] = $this->request->post['username'];
		} elseif (!empty($user_info)) {
			$this->data['username'] = $user_info['username'];
		} else {
			$this->data['username'] = '';
		}

		if (isset($this->request->post['outlet_id'])) {
			$this->data['outlet_id'] = $this->request->post['outlet_id'];
		} elseif (!empty($user_info)) {
			$this->data['outlet_id'] = $user_info['outlet_id'];
		} else {
			$this->data['outlet_id'] = '';
		}

		$this->load->model('wkpos/outlet');

		$this->data['outlets'] = $this->model_wkpos_outlet->getOutlets();

		if (isset($this->request->post['password'])) {
			$this->data['password'] = $this->request->post['password'];
		} else {
			$this->data['password'] = '';
		}

		if (isset($this->request->post['confirm'])) {
			$this->data['confirm'] = $this->request->post['confirm'];
		} else {
			$this->data['confirm'] = '';
		}

		if (isset($this->request->post['firstname'])) {
			$this->data['firstname'] = $this->request->post['firstname'];
		} elseif (!empty($user_info)) {
			$this->data['firstname'] = $user_info['firstname'];
		} else {
			$this->data['firstname'] = '';
		}

		if (isset($this->request->post['lastname'])) {
			$this->data['lastname'] = $this->request->post['lastname'];
		} elseif (!empty($user_info)) {
			$this->data['lastname'] = $user_info['lastname'];
		} else {
			$this->data['lastname'] = '';
		}

		if (isset($this->request->post['email'])) {
			$this->data['email'] = $this->request->post['email'];
		} elseif (!empty($user_info)) {
			$this->data['email'] = $user_info['email'];
		} else {
			$this->data['email'] = '';
		}

		if (isset($this->request->post['image'])) {
			$this->data['image'] = $this->request->post['image'];
		} elseif (!empty($user_info)) {
			$this->data['image'] = $user_info['image'];
		} else {
			$this->data['image'] = '';
		}

		$this->load->model('tool/image');

		$this->data['placeholder'] = $this->model_tool_image->resize('no_image.jpg', 150, 150);

		if (isset($this->request->post['image'])) {
			$this->data['thumb'] = $this->model_tool_image->resize($this->request->post['image'], 100, 100);
		} elseif (!empty($user_info) && $user_info['image']) {
			$this->data['thumb'] = $this->model_tool_image->resize($user_info['image'], 100, 100);
		} else {
			$this->data['thumb'] = $this->data['placeholder'];
		}

		if (isset($this->request->post['status'])) {
			$this->data['status'] = $this->request->post['status'];
		} elseif (!empty($user_info)) {
			$this->data['status'] = $user_info['status'];
		} else {
			$this->data['status'] = 0;
		}

		$this->children = array(
           'common/header',
           'common/footer',
        );
		$this->template = 'wkpos/user_form.expand';
		$this->response->setOutput($this->render(TRUE));
		
		/*$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('wkpos/user_form.tpl', $data));*/
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'wkpos/user')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen(trim($this->request->post['username'])) < 3) || (utf8_strlen(trim($this->request->post['username'])) > 20)) {
			$this->error['username'] = $this->language->get('error_username');
		}

		$user_info = $this->model_wkpos_user->getUserByUsername($this->request->post['username']);

		if (!isset($this->request->get['user_id'])) {
			if ($user_info) {
				$this->error['warning'] = $this->language->get('error_exists');
			}
		} else {
			if ($user_info && ($this->request->get['user_id'] != $user_info['user_id'])) {
				$this->error['warning'] = $this->language->get('error_exists');
			}
		}

		if (!isset($this->request->post['outlet_id']) || !$this->request->post['outlet_id']) {
			$this->error['outlet'] = $this->language->get('error_outlet');
		}

		if ((utf8_strlen(trim($this->request->post['firstname'])) < 1) || (utf8_strlen(trim($this->request->post['firstname'])) > 32)) {
			$this->error['firstname'] = $this->language->get('error_firstname');
		}

		if ((utf8_strlen(trim($this->request->post['lastname'])) < 1) || (utf8_strlen(trim($this->request->post['lastname'])) > 32)) {
			$this->error['lastname'] = $this->language->get('error_lastname');
		}

		if ($this->request->post['password'] || (!isset($this->request->get['user_id']))) {
			if ((utf8_strlen($this->request->post['password']) < 4) || (utf8_strlen($this->request->post['password']) > 20)) {
				$this->error['password'] = $this->language->get('error_password');
			}

			if ($this->request->post['password'] != $this->request->post['confirm']) {
				$this->error['confirm'] = $this->language->get('error_confirm');
			}
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'wkpos/user')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function dtHandler() {
        $this->load->model('wkpos/user');

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

        $return = $this->model_wkpos_user->getUsersDt($data, $filterData);
        $finalRecords = [];
        $records = $return['data'];
        $totalData = $return['total'];
        $totalFiltered = $return['totalFiltered'];

        foreach ($records as $result) {
			$finalRecords[] = array(
				'user_id'    => $result['user_id'],
				'username'   => $result['username'],
				'outlet'   => $result['outlet'],
				'status'     => ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
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
        $this->load->model('wkpos/user');
        $this->load->language('wkpos/user');

        if ( !isset($this->request->post['id']) )
        {
            return false;
        }

        $id_s = $this->request->post['id'];


        if ( is_array($id_s) )
        {

            foreach ($id_s as $user_id)
            {

                if ( $this->model_wkpos_outlet->deleteUser( (int) $user_id ) )
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
            $user_id = (int) $id_s;

            if ( $this->model_wkpos_outlet->deleteUser( $user_id ) )
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
