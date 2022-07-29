<?php    
class ControllerSaleAffiliate extends Controller { 
	private $error = array();

	function __construct($registry){
		parent::__construct($registry);
		if(! \Extension::isInstalled('affiliates')){
			echo "app not installed";
			exit();
		}
	}

	public function dtDelete()
	{
        $this->load->model('sale/affiliate');

        if ( !isset($this->request->post['id']) )
        {
            return false;
        }

        $id_s = $this->request->post['id'];

        if ( is_array($id_s) )
        {
            foreach ($id_s as $affiliate_id)
            {
                if ( $this->model_sale_affiliate->deleteAffiliate( (int) $affiliate_id ) )
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
            $affiliate_id = (int) $id_s;

            if ( $this->model_sale_affiliate->deleteAffiliate($affiliate_id) )
            {
                $result_json['success'] = '1';
                $result_json['success_msg'] = $this->language->get('text_success');
            }
            else
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
            }
        }

        $this->response->setOutput(json_encode($result_json));
        return;
	}

	public function dtApprove()
	{
        $this->load->model('sale/affiliate');

        if ( !isset($this->request->post['id']) )
        {
            return false;
        }

        $id_s = $this->request->post['id'];

        if ( is_array($id_s) )
        {
            foreach ($id_s as $affiliate_id)
            {
                if ( $this->model_sale_affiliate->approve( (int) $affiliate_id ) )
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
            $affiliate_id = (int) $id_s;

            if ( $this->model_sale_affiliate->approve($affiliate_id) )
            {
                $result_json['success'] = '1';
                $result_json['success_msg'] = $this->language->get('text_success');
            }
            else
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
            }
        }

        $this->response->setOutput(json_encode($result_json));
        return;
	}

    public function dtHandler() {
        $this->load->model('sale/affiliate');
        $request = $_REQUEST;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        $start = $request['start'];
        $length = $request['length'];

        $columns = array(
            0 => 'affiliate_id',
            1 => 'firstname',
            2 => 'lastname',
            3 => 'email',
            4 => 'status',
            5 => 'approved',
            6 => 'date_added'
        );

        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];

        $return = $this->model_sale_affiliate->dtHandler($start, $length, $search, $orderColumn, $orderType);
        $records = $return['data'];
        $totalData = $return['total'];
        $totalFiltered = $return['totalFiltered'];

        foreach ($records as $key => $record)
        {
        	$balance = $this->db->query("SELECT SUM(amount) AS amount FROM affiliate_transaction WHERE affiliate_id = '{$record['affiliate_id']}'")->row['amount'];
        	$records[$key]['name'] = $record['firstname'] . ' ' . $record['lastname'];
        	$records[$key]['balance'] = $this->currency->format($balance, $this->config->get('config_currency'));
        	$records[$key]['status_text'] = ($record['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled'));
        	$records[$key]['approved_text'] = ($record['approved'] ? $this->language->get('text_yes') : $this->language->get('text_no'));
        	$records[$key]['date_added_date'] = date($this->language->get('date_format_short'), strtotime($record['date_added']));
        }

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
		$this->language->load('sale/affiliate');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('sale/affiliate');
		
    	$this->getList();
  	}
  
  	public function insert() {
		$this->language->load('sale/affiliate');

    	$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('sale/affiliate');
			
		if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
		{
			if ( $this->validateForm() )
			{
	      	  	$this->model_sale_affiliate->addAffiliate($this->request->post);
	      	  	$result_json['success'] = '1';
				$result_json['success_msg'] = $this->language->get('text_success');
                $result_json['redirect'] = '1';
                $result_json['to'] = (string)$this->url->link(
                    'sale/affiliate',
                    '',
                    'SSL'
                )->format();
			}
		  	else
		  	{
		  		$result_json['success'] = '0';
		  		$result_json['errors'] = $this->error;
		  	}

			$this->response->setOutput(json_encode($result_json));
			return;
		}
    	
    	$this->getForm();
  	} 
   
  	public function update() {
		$this->language->load('sale/affiliate');

    	$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('sale/affiliate');
		
    	if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
    	{
    		if ( $this->validateForm() )
    		{
    			$this->model_sale_affiliate->editAffiliate($this->request->get['affiliate_id'], $this->request->post);
    			$result_json['success'] = '1';
    			$result_json['success_msg'] = $this->language->get('text_success');
    		}
    		else
    		{
    			$result_json['success'] = '0';
    			$result_json['errors'] = $this->error;
    		}
	  		
	  		$this->response->setOutput(json_encode($result_json));
			return;
		}
    
    	$this->getForm();
  	}   

  	public function delete() {
		$this->language->load('sale/affiliate');

    	$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('sale/affiliate');
			
    	if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $affiliate_id) {
				$this->model_sale_affiliate->deleteAffiliate($affiliate_id);
			}
			
			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}
			
			if (isset($this->request->get['filter_email'])) {
				$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
			}
								
			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}
			
			if (isset($this->request->get['filter_approved'])) {
				$url .= '&filter_approved=' . $this->request->get['filter_approved'];
			}		
		
			if (isset($this->request->get['filter_date_added'])) {
				$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
			}
						
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
			
			$this->redirect($this->url->link('sale/affiliate', '', 'SSL'));
    	}
    
    	$this->getList();
  	}  
		 
	public function approve() {
		$this->language->load('sale/affiliate');
    	
		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('sale/affiliate');	
		
		if (!$this->user->hasPermission('modify', 'sale/affiliate')) {
			$this->error['warning'] = $this->language->get('error_permission');
		} elseif (isset($this->request->post['selected'])) {
			$approved = 0;
			
			foreach ($this->request->post['selected'] as $affiliate_id) {
				$affiliate_info = $this->model_sale_affiliate->getAffiliate($affiliate_id);
				
				if ($affiliate_info && !$affiliate_info['approved']) {
					$this->model_sale_affiliate->approve($affiliate_id);
				
					$approved++;
				}
			}
			
			$this->session->data['success'] = sprintf($this->language->get('text_approved'), $approved);
			
			$url = '';
		
			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}
		
			if (isset($this->request->get['filter_email'])) {
				$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
			}
		
			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}
			
			if (isset($this->request->get['filter_approved'])) {
				$url .= '&filter_approved=' . $this->request->get['filter_approved'];
			}	
			
			if (isset($this->request->get['filter_date_added'])) {
				$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
			}
						
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}
		
			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}
							
			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}	
		
			$this->redirect($this->url->link('sale/affiliate', '', 'SSL'));
		}
		
		$this->getList();
	} 
	    
  	protected function getList() {
		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = null;
		}

		if (isset($this->request->get['filter_email'])) {
			$filter_email = $this->request->get['filter_email'];
		} else {
			$filter_email = null;
		}
		
		if (isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		} else {
			$filter_status = null;
		}
		
		if (isset($this->request->get['filter_approved'])) {
			$filter_approved = $this->request->get['filter_approved'];
		} else {
			$filter_approved = null;
		}
		
		if (isset($this->request->get['filter_date_added'])) {
			$filter_date_added = $this->request->get['filter_date_added'];
		} else {
			$filter_date_added = null;
		}	
			
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'name'; 
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

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['filter_email'])) {
			$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
		}
						
		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}
		
		if (isset($this->request->get['filter_approved'])) {
			$url .= '&filter_approved=' . $this->request->get['filter_approved'];
		}	
			
		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}
						
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
       		'text'      => $this->language->get('text_marketing'),
			'href'      => $this->url->link('sale/affiliate', '', 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('sale/affiliate', '', 'SSL'),
      		'separator' => ' :: '
   		);
		
		$this->data['approve'] = $this->url->link('sale/affiliate/approve', '', 'SSL');
		$this->data['insert'] = $this->url->link('sale/affiliate/insert', '', 'SSL');
		$this->data['delete'] = $this->url->link('sale/affiliate/delete', '', 'SSL');

		$this->data['affiliates'] = array();

		$data = array(
			'filter_name'       => $filter_name, 
			'filter_email'      => $filter_email, 
			'filter_status'     => $filter_status, 
			'filter_approved'   => $filter_approved, 
			'filter_date_added' => $filter_date_added,
			'sort'              => $sort,
			'order'             => $order,
			'start'             => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit'             => $this->config->get('config_admin_limit')
		);
		
		$affiliate_total = $this->model_sale_affiliate->getTotalAffiliates($data);
	
		$results = $this->model_sale_affiliate->getAffiliates($data);
 
    	foreach ($results as $result) {
			$action = array();
		
			$action[] = array(
				'text' => $this->language->get('text_edit'),
				'href' => $this->url->link('sale/affiliate/update', 'affiliate_id=' . $result['affiliate_id'] . $url, 'SSL')
			);
			
			$this->data['affiliates'][] = array(
				'affiliate_id' => $result['affiliate_id'],
				'name'         => $result['name'],
				'email'        => $result['email'],
				'balance'      => $this->currency->format($result['balance'], $this->config->get('config_currency')),
				'status'       => ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
				'approved'     => ($result['approved'] ? $this->language->get('text_yes') : $this->language->get('text_no')),
				'date_added'   => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'selected'     => isset($this->request->post['selected']) && in_array($result['affiliate_id'], $this->request->post['selected']),
				'action'       => $action
			);
		}	

		$this->data['token'] = null;

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

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['filter_email'])) {
			$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
		}
			
		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}
		
		if (isset($this->request->get['filter_approved'])) {
			$url .= '&filter_approved=' . $this->request->get['filter_approved'];
		}	
		
		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}
			
		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		
		$this->data['sort_name'] = $this->url->link('sale/affiliate', '', 'SSL');
		$this->data['sort_email'] = $this->url->link('sale/affiliate', '', 'SSL');
		$this->data['sort_status'] = $this->url->link('sale/affiliate', '', 'SSL');
		$this->data['sort_approved'] = $this->url->link('sale/affiliate', '', 'SSL');
		$this->data['sort_date_added'] = $this->url->link('sale/affiliate', '', 'SSL');
		
		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['filter_email'])) {
			$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}
		
		if (isset($this->request->get['filter_approved'])) {
			$url .= '&filter_approved=' . $this->request->get['filter_approved'];
		}
		
		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}
			
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}
												
		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $affiliate_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('sale/affiliate', 'page={page}', 'SSL');
			
		$this->data['pagination'] = $pagination->render();

		$this->data['filter_name'] = $filter_name;
		$this->data['filter_email'] = $filter_email;
		$this->data['filter_status'] = $filter_status;
		$this->data['filter_approved'] = $filter_approved;
		$this->data['filter_date_added'] = $filter_date_added;
		
		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->template = 'sale/affiliate_list.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
  	}
  
  	
    private function getForm()
    {

  		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', '', 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('sale/affiliate', '', 'SSL'),
      		'separator' => ' :: '
   		);

        $this->data['breadcrumbs'][] = array(
            'text'      => !isset($this->request->get['affiliate_id']) ? $this->language->get('breadcrumb_insert') : $this->language->get('breadcrumb_update'),
            'href'      => $this->url->link('sale/affiliate', '', 'SSL'),
            'separator' => ' :: '
        );

		if (!isset($this->request->get['affiliate_id'])) {
			$this->data['action'] = $this->url->link('sale/affiliate/insert', '', 'SSL');
		} else {
			$this->data['action'] = $this->url->link('sale/affiliate/update', 'affiliate_id=' . $this->request->get['affiliate_id'] . $url, 'SSL');
		}
		  
    	$this->data['cancel'] = $this->url->link('sale/affiliate', '', 'SSL');

    	if (isset($this->request->get['affiliate_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
      		$affiliate_info = $this->model_sale_affiliate->getAffiliate($this->request->get['affiliate_id']);
    	}

		$this->data['token'] = null;

		if (isset($this->request->get['affiliate_id'])) {
			$this->data['affiliate_id'] = $this->request->get['affiliate_id'];
		} else {
			$this->data['affiliate_id'] = 0;
		}
					
    	if (isset($this->request->post['firstname'])) {
      		$this->data['firstname'] = $this->request->post['firstname'];
		} elseif (!empty($affiliate_info)) { 
			$this->data['firstname'] = $affiliate_info['firstname'];
		} else {
      		$this->data['firstname'] = '';
    	}

    	if (isset($this->request->post['lastname'])) {
      		$this->data['lastname'] = $this->request->post['lastname'];
    	} elseif (!empty($affiliate_info)) { 
			$this->data['lastname'] = $affiliate_info['lastname'];
		} else {
      		$this->data['lastname'] = '';
    	}

    	if (isset($this->request->post['email'])) {
      		$this->data['email'] = $this->request->post['email'];
    	} elseif (!empty($affiliate_info)) { 
			$this->data['email'] = $affiliate_info['email'];
		} else {
      		$this->data['email'] = '';
    	}

    	if (isset($this->request->post['telephone'])) {
      		$this->data['telephone'] = $this->request->post['telephone'];
    	} elseif (!empty($affiliate_info)) { 
			$this->data['telephone'] = $affiliate_info['telephone'];
		} else {
      		$this->data['telephone'] = '';
    	}

    	if (isset($this->request->post['fax'])) {
      		$this->data['fax'] = $this->request->post['fax'];
    	} elseif (!empty($affiliate_info)) { 
			$this->data['fax'] = $affiliate_info['fax'];
		} else {
      		$this->data['fax'] = '';
    	}

    	if (isset($this->request->post['company'])) {
      		$this->data['company'] = $this->request->post['company'];
    	} elseif (!empty($affiliate_info)) { 
			$this->data['company'] = $affiliate_info['company'];
		} else {
      		$this->data['company'] = '';
    	}
		
    	if (isset($this->request->post['address_1'])) {
      		$this->data['address_1'] = $this->request->post['address_1'];
    	} elseif (!empty($affiliate_info)) { 
			$this->data['address_1'] = $affiliate_info['address_1'];
		} else {
      		$this->data['address_1'] = '';
    	}
				
    	if (isset($this->request->post['address_2'])) {
      		$this->data['address_2'] = $this->request->post['address_2'];
    	} elseif (!empty($affiliate_info)) { 
			$this->data['address_2'] = $affiliate_info['address_2'];
		} else {
      		$this->data['address_2'] = '';
    	}

    	if (isset($this->request->post['city'])) {
      		$this->data['city'] = $this->request->post['city'];
    	} elseif (!empty($affiliate_info)) { 
			$this->data['city'] = $affiliate_info['city'];
		} else {
      		$this->data['city'] = '';
    	}

    	if (isset($this->request->post['postcode'])) {
      		$this->data['postcode'] = $this->request->post['postcode'];
    	} elseif (!empty($affiliate_info)) { 
			$this->data['postcode'] = $affiliate_info['postcode'];
		} else {
      		$this->data['postcode'] = '';
    	}
    	
		if (isset($this->request->post['country_id'])) {
      		$this->data['country_id'] = (int)$this->request->post['country_id'];
    	} elseif (!empty($affiliate_info)) { 
			$this->data['country_id'] = $affiliate_info['country_id'];
		} else {
      		$this->data['country_id'] = '';
    	}
		
		$this->load->model('localisation/country');
		
		$this->data['countries'] = $this->model_localisation_country->getCountries();
				
		if (isset($this->request->post['zone_id'])) {
      		$this->data['zone_id'] = (int)$this->request->post['zone_id'];
    	} elseif (!empty($affiliate_info)) { 
			$this->data['zone_id'] = $affiliate_info['zone_id'];
		} else {
      		$this->data['zone_id'] = '';
    	}

		if (isset($this->request->post['code'])) {
      		$this->data['code'] = $this->request->post['code'];
    	} elseif (!empty($affiliate_info)) { 
			$this->data['code'] = $affiliate_info['code'];
		} else {
      		$this->data['code'] = uniqid();
    	}
		
		if (isset($this->request->post['commission'])) {
      		$this->data['commission'] = $this->request->post['commission'];
    	} elseif (!empty($affiliate_info)) { 
			$this->data['commission'] = $affiliate_info['commission'];
		} else {
      		$this->data['commission'] = $this->config->get('config_commission');
    	}
		
		if (isset($this->request->post['tax'])) {
      		$this->data['tax'] = $this->request->post['tax'];
    	} elseif (!empty($affiliate_info)) { 
			$this->data['tax'] = $affiliate_info['tax'];
		} else {
      		$this->data['tax'] = '';
    	}		

		if (isset($this->request->post['payment'])) {
      		$this->data['payment'] = $this->request->post['payment'];
    	} elseif (!empty($affiliate_info)) { 
			$this->data['payment'] = $affiliate_info['payment'];
		} else {
      		$this->data['payment'] = 'cheque';
    	}	

		if (isset($this->request->post['cheque'])) {
      		$this->data['cheque'] = $this->request->post['cheque'];
    	} elseif (!empty($affiliate_info)) { 
			$this->data['cheque'] = $affiliate_info['cheque'];
		} else {
      		$this->data['cheque'] = '';
    	}	

		if (isset($this->request->post['paypal'])) {
      		$this->data['paypal'] = $this->request->post['paypal'];
    	} elseif (!empty($affiliate_info)) { 
			$this->data['paypal'] = $affiliate_info['paypal'];
		} else {
      		$this->data['paypal'] = '';
    	}	

		if (isset($this->request->post['bank_name'])) {
      		$this->data['bank_name'] = $this->request->post['bank_name'];
    	} elseif (!empty($affiliate_info)) { 
			$this->data['bank_name'] = $affiliate_info['bank_name'];
		} else {
      		$this->data['bank_name'] = '';
    	}	

		if (isset($this->request->post['bank_branch_number'])) {
      		$this->data['bank_branch_number'] = $this->request->post['bank_branch_number'];
    	} elseif (!empty($affiliate_info)) { 
			$this->data['bank_branch_number'] = $affiliate_info['bank_branch_number'];
		} else {
      		$this->data['bank_branch_number'] = '';
    	}

		if (isset($this->request->post['bank_swift_code'])) {
      		$this->data['bank_swift_code'] = $this->request->post['bank_swift_code'];
    	} elseif (!empty($affiliate_info)) { 
			$this->data['bank_swift_code'] = $affiliate_info['bank_swift_code'];
		} else {
      		$this->data['bank_swift_code'] = '';
    	}

		if (isset($this->request->post['bank_account_name'])) {
      		$this->data['bank_account_name'] = $this->request->post['bank_account_name'];
    	} elseif (!empty($affiliate_info)) { 
			$this->data['bank_account_name'] = $affiliate_info['bank_account_name'];
		} else {
      		$this->data['bank_account_name'] = '';
    	}

		if (isset($this->request->post['bank_account_number'])) {
      		$this->data['bank_account_number'] = $this->request->post['bank_account_number'];
    	} elseif (!empty($affiliate_info)) { 
			$this->data['bank_account_number'] = $affiliate_info['bank_account_number'];
		} else {
      		$this->data['bank_account_number'] = '';
    	}
																												
    	if (isset($this->request->post['status'])) {
      		$this->data['status'] = $this->request->post['status'];
    	} elseif (!empty($affiliate_info)) { 
			$this->data['status'] = $affiliate_info['status'];
		} else {
      		$this->data['status'] = 1;
    	}

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

        if (isset($this->request->post['seller_affiliate_code'])) {
            $this->data['seller_affiliate_code'] = $this->request->post['seller_affiliate_code'];
        } elseif (!empty($affiliate_info)) {
            $this->data['seller_affiliate_code'] = $affiliate_info['seller_affiliate_code'];
        } else {
            $this->data['seller_affiliate_code'] = uniqid();
        }


        $this->data['seller_affiliate_enabled'] = false;
        if(\Extension::isInstalled('multiseller_advanced')){

            $this->load->model('setting/setting');
            $multiSellerAdvanced = $this->model_setting_setting->getSetting('multiseller_advanced');

            if($multiSellerAdvanced['multiseller_advanced']['seller_affiliate']){
                $this->data['seller_affiliate_enabled'] = true;

                if (isset($this->request->post['seller_affiliate_commission'])) {
                    $this->data['seller_affiliate_commission'] = $this->request->post['seller_affiliate_commission'];
                } elseif (!empty($affiliate_info)) {
                    $this->data['seller_affiliate_commission'] = $affiliate_info['seller_affiliate_commission'];
                } else {
                    $this->data['seller_affiliate_commission'] = $multiSellerAdvanced['multiseller_advanced']['affiliate_seller_commission'];
                }

                if (isset($this->request->post['seller_affiliate_type'])) {
                    $this->data['seller_affiliate_type'] = $this->request->post['seller_affiliate_type'];
                } elseif (!empty($affiliate_info)) {
                    $this->data['seller_affiliate_type'] = $affiliate_info['seller_affiliate_type'];
                } else {
                    $this->data['seller_affiliate_type'] = $multiSellerAdvanced['multiseller_advanced']['affiliate_seller_type'];
                }
            }
        }
		$this->template = 'sale/affiliate_form.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}
	
  	
    private function validateForm()
    {
    	if ( !$this->user->hasPermission('modify', 'sale/affiliate') )
        {
      		$this->error['error'] = $this->language->get('error_permission');
    	}

    	if ((utf8_strlen($this->request->post['firstname']) < 1) || (utf8_strlen($this->request->post['firstname']) > 32)) {
      		$this->error['firstname'] = $this->language->get('error_firstname');
    	}

    	if ((utf8_strlen($this->request->post['lastname']) < 1) || (utf8_strlen($this->request->post['lastname']) > 32)) {
      		$this->error['lastname'] = $this->language->get('error_lastname');
    	}

		if ((utf8_strlen($this->request->post['email']) > 96) || (!preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $this->request->post['email']))) {
      		$this->error['email'] = $this->language->get('error_email');
    	}
		
		$affiliate_info = $this->model_sale_affiliate->getAffiliateByEmail($this->request->post['email']);
		
		if (!isset($this->request->get['affiliate_id'])) {
			if ($affiliate_info) {
				$this->error['warning'] = $this->language->get('error_exists');
			}
		} else {
			if ($affiliate_info && ($this->request->get['affiliate_id'] != $affiliate_info['affiliate_id'])) {
				$this->error['warning'] = $this->language->get('error_exists');
			}
		}
		
    	if ((utf8_strlen($this->request->post['telephone']) < 3) || (utf8_strlen($this->request->post['telephone']) > 32)) {
      		$this->error['telephone'] = $this->language->get('error_telephone');
    	}

    	if ($this->request->post['password'] || (!isset($this->request->get['affiliate_id']))) {
      		if ((utf8_strlen($this->request->post['password']) < 4) || (utf8_strlen($this->request->post['password']) > 20)) {
        		$this->error['password'] = $this->language->get('error_password');
      		}
	
	  		if ($this->request->post['password'] != $this->request->post['confirm']) {
	    		$this->error['confirm'] = $this->language->get('error_confirm');
	  		}
    	}
		
    	if ((utf8_strlen($this->request->post['address_1']) < 3) || (utf8_strlen($this->request->post['address_1']) > 128)) {
      		$this->error['address_1'] = $this->language->get('error_address_1');
    	}

    	if ((utf8_strlen($this->request->post['city']) < 2) || (utf8_strlen($this->request->post['city']) > 128)) {
      		$this->error['city'] = $this->language->get('error_city');
    	}
		
		$this->load->model('localisation/country');
		
		$country_info = $this->model_localisation_country->getCountry((int)$this->request->post['country_id']);
		
		if ($country_info && $country_info['postcode_required'] && (utf8_strlen($this->request->post['postcode']) < 2) || (utf8_strlen($this->request->post['postcode']) > 10)) {
			$this->error['postcode'] = $this->language->get('error_postcode');
		}
		
    	if ($this->request->post['country_id'] == '' || !is_numeric($this->request->post['country_id'])) {
      		$this->error['country'] = $this->language->get('error_country');
    	}
		
    	if (!isset($this->request->post['zone_id']) || $this->request->post['zone_id'] == '' || !is_numeric($this->request->post['zone_id'])) {
      		$this->error['zone'] = $this->language->get('error_zone');
    	}

    	if (!$this->request->post['code']) {
      		$this->error['code'] = $this->language->get('error_code');
    	}

        if (isset($this->request->post['seller_affiliate_code']) && !$this->request->post['seller_affiliate_code']) {
            $this->error['error_seller_affiliate_code'] = $this->language->get('error_seller_affiliate_code');
        }

		if ( $this->error && !isset($this->error['error']) )
        {
            $this->error['warning'] = $this->language->get('error_warning');
        }

		if (!$this->error) {
	  		return true;
		} else {
	  		return false;
		}
  	}    

  	protected function validateDelete() {
    	if (!$this->user->hasPermission('modify', 'sale/affiliate')) {
      		$this->error['warning'] = $this->language->get('error_permission');
    	}	
	  	 
		if (!$this->error) {
	  		return true;
		} else {
	  		return false;
		}  
  	} 

	public function country() {
		$json = array();
		
		$this->load->model('localisation/country');

    	$country_info = $this->model_localisation_country->getCountry($this->request->post['country_id']);
		
		if ($country_info) {
			$this->load->model('localisation/zone');

			$json = array(
				'country_id'        => $country_info['country_id'],
				'name'              => $country_info['name'],
				'iso_code_2'        => $country_info['iso_code_2'],
				'iso_code_3'        => $country_info['iso_code_3'],
				'address_format'    => $country_info['address_format'],
				'postcode_required' => $country_info['postcode_required'],
				'zone'              => $this->model_localisation_zone->getZonesByCountryId($this->request->post['country_id']),
				'status'            => $country_info['status']		
			);
		}
		
		$this->response->setOutput(json_encode($json));
	}
		
	
    public function transaction()
    {

        if ( ! $this->request->get['affiliate_id'] )
        {
            $this->redirect($this->url->link('sale/affiliate', '', 'SSL'));
            return;
        }

    	$this->language->load('sale/affiliate');
		
		$this->load->model('sale/affiliate');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->user->hasPermission('modify', 'sale/affiliate')) { 
			$this->model_sale_affiliate->addTransaction($this->request->get['affiliate_id'], $this->request->post['description'], $this->request->post['amount'],0,$this->request->post['isSellerTransaction']);
				
			$this->data['success'] = $this->language->get('text_success');
		} else {
			$this->data['success'] = '';
		}

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && !$this->user->hasPermission('modify', 'sale/affiliate')) {
			$this->data['error_warning'] = $this->language->get('error_permission');
		} else {
			$this->data['error_warning'] = '';
		}

		$this->data['text_no_results'] = $this->language->get('text_no_results');
		$this->data['text_balance'] = $this->language->get('text_balance');
		
		$this->data['column_date_added'] = $this->language->get('column_date_added');
		$this->data['column_description'] = $this->language->get('column_description');
		$this->data['column_amount'] = $this->language->get('column_amount');
		
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}  
		
		$this->data['transactions'] = array();
        $is_seller_affiliate = array();
		if(!$this->request->get['isSellerTransaction']){
		    $is_seller_affiliate = ['is_seller_affiliate' => 0];
        }else{
            $is_seller_affiliate = ['is_seller_affiliate' => $this->request->get['isSellerTransaction']];
        }

		$results = $this->model_sale_affiliate->getTransactions($this->request->get['affiliate_id'], ($page - 1) * 10, 10,$is_seller_affiliate);

		foreach ($results as $result) {
        	$this->data['transactions'][] = array(
				'amount'      => $this->currency->format($result['amount'], $this->config->get('config_currency')),
				'description' => $result['description'],
        		'date_added'  => date($this->language->get('date_format_short'), strtotime($result['date_added']))
        	);
      	}			
		
		$this->data['balance'] = $this->currency->format($this->model_sale_affiliate->getTransactionTotal($this->request->get['affiliate_id'],$is_seller_affiliate), $this->config->get('config_currency'));
		
		$transaction_total = $this->model_sale_affiliate->getTotalTransactions($this->request->get['affiliate_id'],$is_seller_affiliate);
			
		$pagination = new Pagination();
		$pagination->total = $transaction_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('sale/affiliate/transaction', 'affiliate_id=' . $this->request->get['affiliate_id'] .($is_seller_affiliate['isSellerTransaction'] ? '&isSellerTransaction=1' : '&isSellerTransaction=0' ) . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->template = 'sale/affiliate_transaction.expand';
		
		$this->response->setOutput($this->render());
	}
		
	public function autocomplete() {
		$affiliate_data = array();
		
		if (isset($this->request->get['filter_name'])) {
			$this->load->model('sale/affiliate');
			
			$data = array(
				'filter_name' => $this->request->get['filter_name'],
				'start'       => 0,
				'limit'       => 20
			);
		
			$results = $this->model_sale_affiliate->getAffiliates($data);
			
			foreach ($results as $result) {
				$affiliate_data[] = array(
					'affiliate_id' => $result['affiliate_id'],
					'name'         => html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')
				);
			}
		}
		
		$this->response->setOutput(json_encode($affiliate_data));
	}		
}
?>
