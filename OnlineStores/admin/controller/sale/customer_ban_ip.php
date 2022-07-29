<?php    
class ControllerSaleCustomerBanIp extends Controller { 
	private $error = array();
  
    public function dtDelete()
    {
        $this->load->model('sale/customer_ban_ip');

        if ( !isset($this->request->post['id']) )
        {
            return false;
        }

        $id_s = $this->request->post['id'];

        if ( is_array($id_s) )
        {
            foreach ($id_s as $customer_ip_id)
            {
                if ( $this->model_sale_customer_ban_ip->deleteCustomerBanIp( (int) $customer_ip_id ) )
                {
                    $result_json['success'] = '1';
                    $result_json['success_msg'] = ':)';
                }
                else
                {
                    $result_json['success'] = '0';
                    $result_json['error'] = ':(';
                    break;
                }
            }
        }
        else
        {
            $customer_ip_id = (int) $id_s;

            if ( $this->model_sale_customer_ban_ip->deleteCustomerBanIp($customer_ip_id) )
            {
                $result_json['success'] = '1';
                $result_json['success_msg'] = ':)';
            }
            else
            {
                $result_json['success'] = '0';
                $result_json['error'] = ':(';
            }
        }

        $this->response->setOutput(json_encode($result_json));
        return;
    }

    public function dtHandler() {
        $this->load->model('sale/customer_ban_ip');
        $request = $_REQUEST;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        $start = $request['start'];
        $length = $request['length'];

        $columns = array(
            0 => 'customer_ban_ip_id',
            1 => 'ip',
        );
        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];

        $return = $this->model_sale_customer_ban_ip->dtHandler($start, $length, $search, $orderColumn, $orderType);
        $records = $return['data'];
        $totalData = $return['total'];
        $totalFiltered = $return['totalFiltered'];

        $json_data = array(
            "draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($totalData), // total number of records
            "recordsFiltered" => $totalFiltered, // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $records   // total data array
        );
        $this->response->setOutput(json_encode($json_data));
        return;
    }

  	public function index() {
		$this->language->load('sale/customer_ban_ip');
		 
		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('sale/customer_ban_ip');
		
    	$this->getList();
  	}
  
  	public function insert() {
		$this->language->load('sale/customer_ban_ip');

    	$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('sale/customer_ban_ip');
			
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
      	  	$this->model_sale_customer_ban_ip->addCustomerBanIp($this->request->post);
			
			$this->session->data['success'] = $this->language->get('text_success');

			$jsonResponse = [];

			$jsonResponse['success'] = "1";
			$jsonResponse['success_msg'] = $this->language->get('text_success');
			$jsonResponse['redirect'] = '1';
            $jsonResponse['to'] = $this->url->link(
                'admin/security/throttling',
                null,
                'SSL'
            )->format();

			$this->response->setOutput(json_encode($jsonResponse));

			return;
		}
    	
    	$this->getForm();
  	} 
   
  	public function update() {
		$this->language->load('sale/customer_ban_ip');

    	$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('sale/customer_ban_ip');
		
    	if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {
            if ( $this->validateForm() )
            {
                $this->model_sale_customer_ban_ip->editCustomerBanIp($this->request->get['customer_ban_ip_id'], $this->request->post);
                $result_json['success'] = '1';
                $result_json['success_msg'] = $this->language->get('text_success');
            }
			else
            {
                $result_json['success'] = '0';
                $result_json['success_msg'] = ":(";
            }

            $this->response->setOutput(json_encode($result_json));

			return;
		}
    
    	$this->getForm();
  	}   

  	public function delete() {
		$this->language->load('sale/customer_ban_ip');

    	$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('sale/customer_ban_ip');
			
    	if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $customer_ban_ip_id) {
				$this->model_sale_customer_ban_ip->deleteCustomerBanIp($customer_ban_ip_id);
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
			
			$this->redirect($this->url->link('sale/customer_ban_ip', '', 'SSL'));
    	}
    
    	$this->getList();
  	}  
    
  	protected function getList() {
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'ip'; 
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
       		'text'      => $this->language->get('seucrity_title'),
			'href'      => $this->url->link('sale/customer_ban_ip', '', 'SSL'),
      		'separator' => ' :: '
   		);
		
		$this->data['insert'] = $this->url->link('sale/customer_ban_ip/insert', '', 'SSL');
		$this->data['delete'] = $this->url->link('sale/customer_ban_ip/delete', '', 'SSL');

		$this->data['customer_ban_ips'] = array();

		$data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit' => $this->config->get('config_admin_limit')
		);
		
		$customer_ban_ip_total = $this->model_sale_customer_ban_ip->getTotalCustomerBanIps($data);
	
		$results = $this->model_sale_customer_ban_ip->getCustomerBanIps($data);
 
    	foreach ($results as $result) {
			$action = array();
		
			$action[] = array(
				'text' => $this->language->get('text_edit'),
				'href' => $this->url->link('sale/customer_ban_ip/update', 'customer_ban_ip_id=' . $result['customer_ban_ip_id'] . $url, 'SSL')
			);
			
			$this->data['customer_ban_ips'][] = array(
				'customer_ban_ip_id' => $result['customer_ban_ip_id'],
				'ip'                 => $result['ip'],
				'total'              => $result['total'],
				'customer'           => $this->url->link('sale/customer', 'filter_ip=' . $result['ip'], 'SSL'),
				'selected'           => isset($this->request->post['selected']) && in_array($result['customer_ban_ip_id'], $this->request->post['selected']),
				'action'             => $action
			);
		}

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
		
		$this->data['sort_ip'] = $this->url->link('sale/customer_ban_ip', 'sort=ip' . $url, 'SSL');
		
		$url = '';
			
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}
												
		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

        $this->data['config_password'] = $this->config->get('config_password');

		$pagination = new Pagination();
		$pagination->total = $customer_ban_ip_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('sale/customer_ban_ip', 'page={page}', 'SSL');
			
		$this->data['pagination'] = $pagination->render();
				
		$this->data['sort'] = $sort;
		$this->data['order'] = $order;
		
		$this->template = 'sale/customer_ban_ip_list.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render_ecwig());
  	}
  
  	protected function getForm() {
    	$this->data['heading_title'] = $this->language->get('heading_title');
 		
    	$this->data['entry_ip'] = $this->language->get('entry_ip');
 
		$this->data['button_save'] = $this->language->get('button_save');
    	$this->data['button_cancel'] = $this->language->get('button_cancel');

 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}
		
 		if (isset($this->error['ip'])) {
			$this->data['error_ip'] = $this->error['ip'];
		} else {
			$this->data['error_ip'] = '';
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
			'href'      => $this->url->link('sale/customer_ban_ip', '', 'SSL'),
      		'separator' => ' :: '
   		);

		if (!isset($this->request->get['customer_ban_ip_id'])) {
			$this->data['action'] = $this->url->link('sale/customer_ban_ip/insert', '', 'SSL');
		} else {
			$this->data['action'] = $this->url->link('sale/customer_ban_ip/update', 'customer_ban_ip_id=' . $this->request->get['customer_ban_ip_id'] . $url, 'SSL');
		}
		  
    	$this->data['cancel'] = $this->url->link('sale/customer_ban_ip', '', 'SSL');

    	if (isset($this->request->get['customer_ban_ip_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
      		$customer_ban_ip_info = $this->model_sale_customer_ban_ip->getCustomerBanIp($this->request->get['customer_ban_ip_id']);
    	}
			
    	if (isset($this->request->post['ip'])) {
      		$this->data['ip'] = $this->request->post['ip'];
		} elseif (!empty($customer_ban_ip_info)) { 
			$this->data['ip'] = $customer_ban_ip_info['ip'];
		} else {
      		$this->data['ip'] = '';
    	}
		
		$this->template = 'sale/customer_ban_ip_form.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}
			 
  	protected function validateForm() {
    	if (!$this->user->hasPermission('modify', 'sale/customer_ban_ip')) {
      		$this->error['warning'] = $this->language->get('error_permission');
    	}

    	if ((utf8_strlen($this->request->post['ip']) < 1) || (utf8_strlen($this->request->post['ip']) > 40)) {
      		$this->error['ip'] = $this->language->get('error_ip');
    	}
		
		if (!$this->error) {
	  		return true;
		} else {
	  		return false;
		}
  	}    

  	protected function validateDelete() {
    	if (!$this->user->hasPermission('modify', 'sale/customer_ban_ip')) {
      		$this->error['warning'] = $this->language->get('error_permission');
    	}	
	  	 
		if (!$this->error) {
	  		return true;
		} else {
	  		return false;
		}  
  	} 
}
?>
