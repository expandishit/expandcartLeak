<?php
class ControllerModuleQuickcheckoutFields extends Controller {
	private $error = array();

	public function index() {
        $this->load->language('module/quickcheckout_fields');
		$this->load->model('module/quickcheckout_fields');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->getList();
	}

	public function add() {
        $this->load->language('module/quickcheckout_fields');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('module/quickcheckout_fields');

		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {

			if($this->validate()){
				$data = $this->request->post;

				$field_id = $this->model_module_quickcheckout_fields->addField($data);

				if($field_id > 0){
					$result_json['redirect'] = '1';
					$result_json['to']       = 'module/quickcheckout_fields';
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
        $this->load->language('module/quickcheckout_fields');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('module/quickcheckout_fields');

		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			if($this->validate()){
				$data = $this->request->post;

				$this->model_module_quickcheckout_fields->editField($this->request->get['id'], $data);
				
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

    protected function getForm() {

        $this->load->language('module/quickcheckout_fields');

        if (isset($this->request->get['id']) && $this->request->server['REQUEST_METHOD'] != 'POST') {
            $field_info = $this->model_module_quickcheckout_fields->getField($this->request->get['id']);
        }

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'SSL')
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_module'),
            'href' => $this->url->link('marketplace/home', true)
        );
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_heading_main'),
            'href' => $this->url->link('module/quickcheckout', true)
        );
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title_main'),
            'href' => $this->url->link('module/quickcheckout_fields', true)
        );

        if (isset($this->request->get['id']) && !empty($field_info)) {
            $this->data['breadcrumbs'][] = array(
                'text' => $this->language->get('edit_field'). $field_info['title'],
                'href' => ''
            );
        } else {
            $this->data['breadcrumbs'][] = array(
                'text' => $this->language->get('add_field'),
                'href' => ''
            );

        }

        if (!isset($this->request->get['id'])) {
            $this->data['action'] = $this->url->link('module/quickcheckout_fields/add', 'SSL');
        } else {
            $this->data['action'] = $this->url->link('module/quickcheckout_fields/edit', 'id=' . $this->request->get['id'] , 'SSL');
        }

        $this->data['cancel'] = $this->url->link('module/quickcheckout_fields', 'SSL');

        if (isset($this->request->post['field_description'])) {
            $this->data['field_description'] = $this->request->post['field_description'];
        } elseif (isset($this->request->get['id'])) {
            $this->data['field_description'] = $this->model_module_quickcheckout_fields->getFieldDescription($this->request->get['id']);
        } else {
            $this->data['field_description'] = array();
        }

        if (isset($this->request->post['section'])) {
            $this->data['section'] = $this->request->post['section'];
        } elseif (isset($this->request->get['id'])) {
            $this->data['section'] = $field_info['section'];
        } else {
            $this->data['section'] = array();
        }

        if (isset($this->request->post['field_type'])) {
            $this->data['field_type'] = $this->request->post['field_type'];
        } elseif (isset($this->request->get['id'])) {
            $this->data['field_type'] = $field_info['field_type'];
        } else {
            $this->data['field_type'] = array();
        }

        if (isset($this->request->get['id']) && $this->request->get['id']) {
            $this->data['id'] = $this->request->get['id'];
        } else {
            $this->data['id'] = 0;
        }

        if (isset($this->request->post['sort_order'])) {
            $this->data['sort_order'] = $this->request->post['sort_order'];
        } elseif (!empty($option_info)) {
            $this->data['sort_order'] = $field_info['sort_order'];
        } else {
            $this->data['sort_order'] = '';
        }

        if (isset($this->request->post['option_value'])) {
            $option_values = $this->request->post['option_value'];
        } elseif (isset($this->request->get['id'])) {
            $option_values = $this->model_module_quickcheckout_fields->getOptionValueDescriptions($this->request->get['id']);
        } else {
            $option_values = array();
        }

        $this->data['option_values'] = array();
        foreach ($option_values as $option_value)
        {
            $this->data['option_values'][] = array(
                'option_value_id'          => $option_value['option_value_id'],
                'option_value_description' => $option_value['option_value_description'],
                'sort_order'               => $option_value['sort_order'],
                'valuable_type'            => $option_value['valuable_type'],
                'valuable_id'              => $option_value['valuable_id']
            );
        }

        $this->load->model('localisation/language');
        $this->data['languages'] = $this->model_localisation_language->getLanguages();

        $this->data['fieldTypes'] = [
            'text_choose' => [
                'select',
                'radio'
            ],
            'text_input' => [
                'text',
                'textarea',
                'checkbox'
            ],
            'text_text' => [
                'label'
            ]
        ];

        $this->data['fieldSections'] = [
            'payment_address',
            'shipping_address',
            'confirm'
        ];

        $this->children = array(
            'common/header',
            'common/footer',
        );
        $this->template = 'module/quickcheckout_fields/field_form.expand';
        $this->response->setOutput($this->render(TRUE));
    }

	public function delete() {
        $this->load->language('module/quickcheckout_fields');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('module/quickcheckout_fields');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $id) {
                $this->model_module_quickcheckout_fields->deleteField($id);
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

			$this->response->redirect($this->url->link('module/quickcheckout_fields', 'SSL'));
		}

		$this->getList();
	}

	protected function getList() {
		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'SSL')
		);
		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_module'),
			'href' => $this->url->link('marketplace/home', true)
		);
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_heading_main'),
            'href' => $this->url->link('module/quickcheckout', true)
        );
		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_fields_list'),
			'href' => $this->url->link('module/quickcheckout_fields','SSL')
		);

		$this->data['add'] = $this->url->link('module/quickcheckout_fields/add', 'SSL');
		$this->data['delete'] = $this->url->link('module/quickcheckout_fields/delete', 'SSL');

		$this->data['fields'] = array();

		$this->data['heading_title'] = $this->language->get('heading_title');

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
		$this->template = 'module/quickcheckout_fields/fields_list.expand';
		$this->response->setOutput($this->render(TRUE));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'module/quickcheckout_fields')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

        foreach ( $this->request->post['field_description'] as $language_id => $value )
        {
            if (utf8_strlen(ltrim($value['field_title'] ," ")) < 1)
            {
                $this->error['title_' . $language_id] = $this->language->get('error_title');
            }
        }

		if (!$this->request->post['section']) {
			$this->error['section'] = $this->language->get('error_section');
		}

        if (!$this->request->post['field_type']) {
            $this->error['field_type'] = $this->language->get('error_field_type');
        }

        if ( $this->error && !isset($this->error['error']) )
        {
            $this->error['warning'] = $this->language->get('error_warning');
        }
        
		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'module/quickcheckout_fields')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function dtHandler() {
        $this->load->model('module/quickcheckout_fields');

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
            1 => 'id',
			2 => 'qfd.field_title'
        );

        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];

        $data = array(
            'sort' => $orderColumn,
            'order' => $orderType,
            'start' => $request['start'],
            'limit' => $request['length']
        );

        $return = $this->model_module_quickcheckout_fields->getFieldsDt($data, $filterData);
        $finalRecords = [];

        $records = $return['data'];
        $totalData = $return['total'];
        $totalFiltered = $return['totalFiltered'];

        $json_data = array(
            "draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($totalData), // total number of records
            "recordsFiltered" => $totalFiltered, // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $records,   // total data array
        );
        $this->response->setOutput(json_encode($json_data));
        return;
    }

    public function dtDelete()
    {
        $this->load->model('module/quickcheckout_fields');
        $this->load->language('module/quickcheckout_fields');

        if ( !isset($this->request->post['id']) )
        {
            return false;
        }

        $id_s = $this->request->post['id'];


        if ( is_array($id_s) )
        {

            foreach ($id_s as $id)
            {
                if ($this->model_module_quickcheckout_fields->deleteField( (int) $id ) )
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

            if ($this->model_module_quickcheckout_fields->deleteField( $id ) )
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
