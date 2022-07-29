<?php
class ControllerSaleCustomerGroup extends Controller {
	private $error = array();

    private $plan_id = PRODUCTID;

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->load->model('plan/trial');
        $trial = $this->model_plan_trial->getLastTrial();
        if ($trial){
            $this->plan_id = $trial['plan_id'];
        }
    }

    public function dtDelete()
	{
		if($this->plan_id == 3){
            $this->redirect(
                $this->url->link('error/permission', '', 'SSL')
            );
            exit();
        }

        if ( !isset($this->request->post['id']) )
        {
            return false;
        }

        $this->language->load('sale/customer_group');

        $this->load->model('sale/customer_group');
        $id_s = $this->request->post['id'];

        if ( is_array($id_s) && $this->validateDelete($id_s))
        {
            foreach ($id_s as $customer_group_id)
            {
                if ( $this->model_sale_customer_group->deleteCustomerGroup( (int) $customer_group_id ) )
                {
                    $result_json['success'] = '1';
                    $result_json['success_msg'] = $this->language->get('text_success');
                }
                else
                {
                    $result_json['success'] = '0';
                    $result_json['errors'] = $this->error;
                    break;
                }
            }
        }
        elseif($this->validateDelete([$id_s]))
        {
            $customer_group_id = (int) $id_s;

            if ( $this->model_sale_customer_group->deleteCustomerGroup( $customer_group_id ) )
            {
                $result_json['success'] = '1';
                $result_json['success_msg'] = $this->language->get('text_success');
            }
            else
            {
                $result_json['success'] = '0';
                $result_json['error'] = $this->error;
            }
        }
        else{
        	$result_json['success'] = '0';
            $result_json['error'] = $this->error;
        }

        $this->response->setOutput(json_encode($result_json));
        return;

	}

	public function dtHandler() {
        $this->load->model('sale/customer_group');
        $request = $_REQUEST;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        $start = $request['start'];
        $length = $request['length'];

        $columns = array(
            0 => 'customer_group_id',
            1 => 'name',
            2 => 'sort_order',
            3 => '',
        );
        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];

        $return = $this->model_sale_customer_group->dtHandler($start, $length, $search, $orderColumn, $orderType);
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

	public function index() {

		$this->language->load('sale/customer_group');
 
		$this->document->setTitle($this->language->get('heading_title'));
 		
		$this->load->model('sale/customer_group');
		
		$this->getList();
	}

	
    public function insert()
    {
    	if($this->plan_id == 3){
            $this->redirect(
                $this->url->link('error/permission', '', 'SSL')
            );
            exit();
        }

		$this->language->load('sale/customer_group');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('sale/customer_group');
		
		if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {

            if ( ! $this->validateForm() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
                
                $this->response->setOutput(json_encode($result_json));
                
                return;
            }

			$this->model_sale_customer_group->addCustomerGroup($this->request->post);
			
			$this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

            $result_json['success'] = '1';
			$result_json['redirect'] = '1';
			$result_json['to'] = (string)$this->url->link(
				'sale/component/customers',
				'',
				'SSL'
			)->format();
            
            $this->response->setOutput(json_encode($result_json));
            
            return;
		}

		$this->getForm();
	}

	
    public function update()
    {

		$customer_group_id = (int)$this->request->get['customer_group_id'];
        if(!preg_match("/^[1-9][0-9]*$/", $customer_group_id)) return false;

		$this->language->load('sale/customer_group');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('sale/customer_group');
		
		if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {

            if ( ! $this->validateForm()  )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
                
                $this->response->setOutput(json_encode($result_json));
                
                return;
            }

			if ( $this->model_sale_customer_group->editCustomerGroup((int)$this->request->get['customer_group_id'], $this->request->post) ) 
			{
				$result_json['success'] = '1';
				$result_json['success_msg'] = $this->language->get('text_success');
			}
			else
			{
				$result_json['success'] = '0';
				$result_json['error'] = $this->error;
			}
			
			$this->response->setOutput(json_encode($result_json));
			return;
		}

		$this->getForm();
	}


	public function delete() { 

		if($this->plan_id == 3){
            $this->redirect(
                $this->url->link('error/permission', '', 'SSL')
            );
            exit();
        }

		$this->language->load('sale/customer_group');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('sale/customer_group');
		
		if (isset($this->request->post['selected']) && $this->validateDelete($this->request->post['selected'])) {
      		foreach ($this->request->post['selected'] as $customer_group_id) {
				$this->model_sale_customer_group->deleteCustomerGroup($customer_group_id);	
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
			
			$this->redirect($this->url->link('sale/component/customers', 'content_url=sale/customer_group', 'SSL'));
		}

		$this->getList();
	}

	protected function getList() {
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'cgd.name';
		}
		 
		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}
		
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
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
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', '', 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('sale/component/customers', 'content_url=sale/customer_group', 'SSL'),
      		'separator' => ' :: '
   		);
							
		$this->data['insert'] = $this->url->link('sale/customer_group/insert', '', 'SSL');
		$this->data['delete'] = $this->url->link('sale/customer_group/delete', '', 'SSL');
	
		$this->data['customer_groups'] = array();

		$data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit' => $this->config->get('config_admin_limit')
		);
		
		$customer_group_total = $this->model_sale_customer_group->getTotalCustomerGroups();
		
		$results = $this->model_sale_customer_group->getCustomerGroups($data);

		foreach ($results as $result) {
			$action = array();
			
			$action[] = array(
				'text' => $this->language->get('text_edit'),
				'href' => $this->url->link('sale/customer_group/update', 'customer_group_id=' . $result['customer_group_id'] . $url, 'SSL')
			);		
		
			$this->data['customer_groups'][] = array(
				'customer_group_id' => $result['customer_group_id'],
				'name'              => $result['name'] . (($result['customer_group_id'] == $this->config->get('config_customer_group_id')) ? $this->language->get('text_default') : null),
				'sort_order'        => $result['sort_order'],
				'selected'          => isset($this->request->post['selected']) && in_array($result['customer_group_id'], $this->request->post['selected']),
				'action'            => $action
			);
		}	
	
		$this->data['heading_title'] = $this->language->get('heading_title');
		
		$this->data['text_no_results'] = $this->language->get('text_no_results');

		$this->data['column_name'] = $this->language->get('column_name');
		$this->data['column_sort_order'] = $this->language->get('column_sort_order');
		$this->data['column_action'] = $this->language->get('column_action');

		$this->data['button_insert'] = $this->language->get('button_insert');
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
		
		$url = '';

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$this->data['sort_name'] = $this->url->link('sale/component/customers', 'content_url=sale/customer_group&sort=cgd.name' . $url, 'SSL');
		$this->data['sort_sort_order'] = $this->url->link('sale/component/customers', 'content_url=sale/customer_group&sort=cg.sort_order' . $url, 'SSL');
		
		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}
												
		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
				
		$pagination = new Pagination();
		$pagination->total = $customer_group_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('sale/component/customers', 'content_url=sale/customer_group&page={page}', 'SSL');
		
		$this->data['pagination'] = $pagination->render();				

		$this->data['sort'] = $sort; 
		$this->data['order'] = $order;

		if ($customer_group_total == 0){
			$this->template = 'sale/customer_group/empty.expand';
		}else{
			$this->template = 'sale/customer_group_list.expand';
		}

		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render_ecwig());
 	}

	protected function getForm() {

 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

 		if (isset($this->error['name'])) {
			$this->data['error_name'] = $this->error['name'];
		} else {
			$this->data['error_name'] = array();
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
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', '', 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('sale/component/customers', 'content_url=sale/customer_group', 'SSL'),
      		'separator' => ' :: '
   		);
		
        $this->data['breadcrumbs'][] = array(
            'text'      => !isset($this->request->get['customer_group_id']) ? $this->language->get('breadcrumb_insert') : $this->language->get('breadcrumb_update'),
            'href'      => $this->url->link('sale/component/customers', 'content_url=sale/customer_group', 'SSL'),
            'separator' => ' :: '
        );

		if (!isset($this->request->get['customer_group_id'])) {
			$this->data['action'] = $this->url->link('sale/customer_group/insert', '', 'SSL');
		} else {
			$this->data['customer_group_total']  = $this->model_sale_customer_group->getTotalCustomerGroups();
			$this->data['customer_group_id'] = $this->request->get['customer_group_id'];
			$this->data['is_default'] = $this->config->get('config_customer_group_id') == $this->request->get['customer_group_id'] ? 1 : 0;
			$this->data['action'] = $this->url->link('sale/customer_group/update', 'customer_group_id=' . $this->request->get['customer_group_id'] . $url, 'SSL');
		}
		  
    	$this->data['cancel'] = $this->url->link('sale/component/customers', '', 'SSL');

		if (isset($this->request->get['customer_group_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$customer_group_info = $this->model_sale_customer_group->getCustomerGroup($this->request->get['customer_group_id']);
		}
		
		$this->load->model('localisation/language');
		
		$this->data['languages'] = $this->model_localisation_language->getLanguages();
		
		if (isset($this->request->post['customer_group_description'])) {
			$this->data['customer_group_description'] = $this->request->post['customer_group_description'];
		} elseif (isset($this->request->get['customer_group_id'])) {
			$this->data['customer_group_description'] = $this->model_sale_customer_group->getCustomerGroupDescriptions($this->request->get['customer_group_id']);
		} else {
			$this->data['customer_group_description'] = array();
		}	

		if (isset($this->request->post['company_id_display'])) {
			$this->data['company_id_display'] = $this->request->post['company_id_display'];
		} elseif (!empty($customer_group_info)) {
			$this->data['company_id_display'] = $customer_group_info['company_id_display'];
		} else {
			$this->data['company_id_display'] = '';
		}			
			
		if (isset($this->request->post['company_id_required'])) {
			$this->data['company_id_required'] = $this->request->post['company_id_required'];
		} elseif (!empty($customer_group_info)) {
			$this->data['company_id_required'] = $customer_group_info['company_id_required'];
		} else {
			$this->data['company_id_required'] = '';
		}		
		
		if (isset($this->request->post['tax_id_display'])) {
			$this->data['tax_id_display'] = $this->request->post['tax_id_display'];
		} elseif (!empty($customer_group_info)) {
			$this->data['tax_id_display'] = $customer_group_info['tax_id_display'];
		} else {
			$this->data['tax_id_display'] = '';
		}			
			
		if (isset($this->request->post['tax_id_required'])) {
			$this->data['tax_id_required'] = $this->request->post['tax_id_required'];
		} elseif (!empty($customer_group_info)) {
			$this->data['tax_id_required'] = $customer_group_info['tax_id_required'];
		} else {
			$this->data['tax_id_required'] = '';
		}	
		
		if (isset($this->request->post['sort_order'])) {
			$this->data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($customer_group_info)) {
			$this->data['sort_order'] = $customer_group_info['sort_order'];
		} else {
			$this->data['sort_order'] = '';
		}

		$this->data['email_activation'] = $customer_group_info['email_activation'] ? 1 : 0;
		$this->data['sms_activation'] = $customer_group_info['sms_activation'] ? 1 : 0;
		$this->data['approval'] = $customer_group_info['approval'] ? 1 : 0;
		$this->data['customer_verified'] = $customer_group_info['customer_verified'] ? 1 : 0;

        $smshare = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'smshare'");

        $smsModule = null;

		if ($smshare->num_rows) {
		    $smsModule['status'] = 1;
        }

		$this->data['smsModule'] = $smsModule;

		$this->data['customPermissions'] =
		[		
			['value' => 'hidePrice', 'text'  => $this->language->get('text_hidePrice')],			
			['value' => 'hideCart', 'text'  => $this->language->get('text_hideCart')],
			['value' => 'hideProductsLinks', 'text'  => $this->language->get('text_hideProductsLinks')],
		];
		$this->data['selectedPermissions'] = (isset($customer_group_info['permissions'])) ? $customer_group_info['permissions'] : [];
    	
        $this->data['enable_new_login'] =  (defined('LOGIN_MODE') && LOGIN_MODE === 'identity' && $this->identity->isStoreOnWhiteList());

        $this->template = 'sale/customer_group_form.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render_ecwig()); 
	}

	
    private function validateForm()
    {
		if ( !$this->user->hasPermission('modify', 'sale/customer_group') )
        {
			$this->error['error'] = $this->language->get('error_permission');
		}
		

		foreach ( $this->request->post['customer_group_description'] as $language_id => $value )
        {
			if ((utf8_strlen($value['name']) < 3) || (utf8_strlen($value['name']) > 32)) {
				$this->error['name_' . $language_id] = $this->language->get('error_name');
			}
		}
		
		if ( $this->error && !isset($this->error['error']) )
        {
            $this->error['warning'] = $this->language->get('error_warning');
        }
        
        return $this->error ? false : true;
	}

	protected function validateDelete($ids) {
		if (!$this->user->hasPermission('modify', 'sale/customer_group')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		$this->load->model('setting/store');
		$this->load->model('sale/customer');
      	
		foreach ($ids as $customer_group_id) {
    		if ($this->config->get('config_customer_group_id') == $customer_group_id) {
	  			$this->error['warning'] = $this->language->get('error_default');	
			}  
			
			$store_total = $this->model_setting_store->getTotalStoresByCustomerGroupId($customer_group_id);

			if ($store_total) {
				$this->error['warning'] = sprintf($this->language->get('error_store'), $store_total);
			}
			
			$customer_total = $this->model_sale_customer->getTotalCustomersByCustomerGroupId($customer_group_id);

			if ($customer_total) {
				$this->error['warning'] = sprintf($this->language->get('error_customer'), $customer_total);
			}
		}
		
		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}
}
?>
