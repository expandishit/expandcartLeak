<?php    

class ControllerCatalogManufacturer extends Controller
{
	private $error = array();
  
  	public function index()
    {
		$this->language->load('catalog/manufacturer');
		
		$this->document->setTitle($this->language->get('heading_title'));
		 
		$this->load->model('catalog/manufacturer');
		
    	$this->getList();
  	}
  
  	
    public function insert()
    {
		$this->language->load('catalog/manufacturer');

    	$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('catalog/manufacturer');
			
		if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {

            if ( !$this->validateForm() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
                
                $this->response->setOutput(json_encode($result_json));
                
                return;
            }

            $manufacturer=$this->model_catalog_manufacturer->addManufacturer($this->request->post);

			$this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

            $result_json['success'] = '1';
            $result_json['redirect'] = '1';
            $result_json['manufacturer_id']=$manufacturer['id'];
            $result_json['name']=$manufacturer['name'];

            $result_json['to'] = (string)$this->url->link(
                'catalog/component/collection',
                'content_url=catalog/manufacturer',
                'SSL'
            )->format();
            
            $this->response->setOutput(json_encode($result_json));
            
            return;
		}

		$this->data['links'] = [
		    'submit' => $this->url->link('catalog/manufacturer/insert', '', 'SSL'),
		    'cancel' => $this->url->link(
		        'catalog/component/collection','content_url=catalog/manufacturer', 'SSL'
            ),
        ];
    
    	$this->getForm();
  	} 
   
  	
    public function update()
    {
		$this->language->load('catalog/manufacturer');

    	$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('catalog/manufacturer');
		
    	if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {

            if ( !$this->validateForm() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
                
                $this->response->setOutput(json_encode($result_json));
                
                return;
            }

			$this->model_catalog_manufacturer->editManufacturer($this->request->get['manufacturer_id'], $this->request->post);

			$this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

			$result_json['success'] = '1';
            
            $this->response->setOutput(json_encode($result_json));
            
            return;
		}

        $this->data['links'] = [
            'submit' => $this->url->link('catalog/manufacturer/update', 'manufacturer_id=' . $this->request->get['manufacturer_id'], 'SSL'),
            'cancel' => $this->url->link(
                'catalog/component/collection', 'content_url=catalog/manufacturer', 'SSL'
            ),
        ];
    
    	$this->getForm();
  	}   

  	public function delete() {
		$this->language->load('catalog/manufacturer');

    	$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('catalog/manufacturer');
			
    	if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $manufacturer_id) {
				$this->model_catalog_manufacturer->deleteManufacturer($manufacturer_id);
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
			
			$this->redirect($this->url->link(
			    'catalog/component/collection',
                'content_url=catalog/manufacturer&token=' . $this->session->data['token'] . $url, 'SSL')
            );
    	}
	
    	$this->getList();
  	}  
    
  	protected function getList() {
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
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('catalog/component/collection', 'content_url=catalog/manufacturer&token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);
							
		$this->data['insert'] = $this->url->link('catalog/manufacturer/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('catalog/manufacturer/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');	

		$this->data['manufacturers'] = array();

		$data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit' => $this->config->get('config_admin_limit')
		);
		
		$manufacturer_total = $this->model_catalog_manufacturer->getTotalManufacturers();
	
		$results = $this->model_catalog_manufacturer->getManufacturers($data);
 
    	foreach ($results as $result) {
			$action = array();
			
			$action[] = array(
				'text' => $this->language->get('text_edit'),
				'href' => $this->url->link('catalog/manufacturer/update', 'token=' . $this->session->data['token'] . '&manufacturer_id=' . $result['manufacturer_id'] . $url, 'SSL')
			);
						
			$this->data['manufacturers'][] = array(
				'manufacturer_id' => $result['manufacturer_id'],
				'name'            => $result['name'],
				'sort_order'      => $result['sort_order'],
				'selected'        => isset($this->request->post['selected']) && in_array($result['manufacturer_id'], $this->request->post['selected']),
				'action'          => $action
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
		
		$this->data['sort_name'] = $this->url->link('catalog/component/collection', 'content_url=catalog/manufacturer&token=' . $this->session->data['token'] . '&sort=name' . $url, 'SSL');
		$this->data['sort_sort_order'] = $this->url->link('catalog/component/collection', 'content_url=catalog/manufacturer&token=' . $this->session->data['token'] . '&sort=sort_order' . $url, 'SSL');
		
		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}
												
		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $manufacturer_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('catalog/component/collection', 'content_url=catalog/manufacturer&token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');
			
		$this->data['pagination'] = $pagination->render();

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		if ($manufacturer_total == 0){
            $this->template = 'catalog/manufacturer/empty.expand';
        }else{
            $this->template = 'catalog/manufacturer/list.expand';
        }
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}
  
  	
    protected function getForm()
    {

        $this->data['cancel'] = $this->url->link(
            'catalog/component/collection', 'content_url=catalog/manufacturer', 'SSL'
        );

        if (count($this->request->post) > 0) {
            $this->data = array_merge($this->data, $this->request->post);
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
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('catalog/component/collection', 'content_url=catalog/manufacturer&token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);
		
        $this->data['breadcrumbs'][] = array(
            'text'      => ! isset($this->request->get['manufacturer_id']) ?  $this->language->get('breadcrumb_insert') : $this->language->get('breadcrumb_update'),
            'href'      => $this->url->link('catalog/component/collection', 'content_url=catalog/manufacturer&token=' . $this->session->data['token'] . $url, 'SSL'),
            'separator' => ' :: '
        );

    	if (isset($this->request->get['manufacturer_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
      		$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($this->request->get['manufacturer_id']);
    	}

		$this->data['token'] = $this->session->data['token'];

    	if (isset($this->request->post['name'])) {
      		$this->data['name'] = $this->request->post['name'];
    	} elseif (!empty($manufacturer_info)) {
			$this->data['name'] = $manufacturer_info['name'];
		} else {	
      		$this->data['name'] = '';
    	}
		
		$this->load->model('setting/store');
		
		$this->data['stores'] = $this->model_setting_store->getStores();
		
		if (isset($this->request->post['manufacturer_store'])) {
			$this->data['manufacturer_store'] = $this->request->post['manufacturer_store'];
		} elseif (isset($this->request->get['manufacturer_id'])) {
			$this->data['manufacturer_store'] = $this->model_catalog_manufacturer->getManufacturerStores($this->request->get['manufacturer_id']);
		} else {
			$this->data['manufacturer_store'] = array(0);
		}
		
		if (isset($this->request->post['keyword'])) {
			$this->data['keyword'] = $this->request->post['keyword'];
		} elseif (!empty($manufacturer_info)) {
			$this->data['keyword'] = $manufacturer_info['keyword'];
		} else {
			$this->data['keyword'] = '';
		}

		if (isset($this->request->post['image'])) {
			$this->data['image'] = $this->request->post['image'];
		} elseif (!empty($manufacturer_info)) {
			$this->data['image'] = $manufacturer_info['image'];
		} else {
			$this->data['image'] = '';
		}
		
		$this->load->model('tool/image');

		if (!empty($manufacturer_info) && $manufacturer_info['image']) {
			$this->data['thumb'] = $this->model_tool_image->resize($manufacturer_info['image'], 150, 150);
		} else {
			$this->data['thumb'] = $this->model_tool_image->resize('no_image.jpg', 150, 150);
		}

		$this->data['no_image'] = $this->model_tool_image->resize('no_image.jpg', 150, 150);
		
		if (isset($this->request->post['sort_order'])) {
      		$this->data['sort_order'] = $this->request->post['sort_order'];
    	} elseif (!empty($manufacturer_info)) {
			$this->data['sort_order'] = $manufacturer_info['sort_order'];
		} else {
      		$this->data['sort_order'] = '';
    	}

        //$this->document->addScript('view/javascript/cube/dropzone.min.js');

        $this->document->addScript('view/javascript/cube/scripts.js');

//        $this->document->addScript('view/javascript/pages/catalog/manufacturer.js');

		$this->template = 'catalog/manufacturer/form.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}  
	 
  	
    private function validateForm()
    {
    	if ( !$this->user->hasPermission('modify', 'catalog/manufacturer') )
        {
      		$this->error['error'] = $this->language->get('error_permission');
    	}
        if ((utf8_strlen(ltrim($this->request->post['name']," "))< 3) || (utf8_strlen(ltrim($this->request->post['name'] ," ")) > 64))
        {
      		$this->error['name'] = $this->language->get('error_name');
    	}
		
		if ( $this->error && !isset($this->error['error']) )
        {
            $this->error['warning'] = $this->language->get('error_warning');
        }
        
        return $this->error ? false : true;
  	}    


  	protected function validateDelete() {
    	if (!$this->user->hasPermission('modify', 'catalog/manufacturer')) {
			$this->error['warning'] = $this->language->get('error_permission');
    	}	
		
		$this->load->model('catalog/product');

		foreach ($this->request->post['selected'] as $manufacturer_id) {
  			$product_total = $this->model_catalog_product->getTotalProductsByManufacturerId($manufacturer_id);
    
			if ($product_total) {
	  			$this->error['warning'] = sprintf($this->language->get('error_product'), $product_total);	
			}	
	  	} 
		
		if (!$this->error) {
	  		return true;
		} else {
	  		return false;
		}  
  	}
	
	public function autocomplete() {
		$json = array();
		
		if (isset($this->request->get['filter_name'])) {
			$this->load->model('catalog/manufacturer');
			
			$data = array(
				'filter_name' => $this->request->get['filter_name'],
				//'start'       => 0,
				//'limit'       => 20
			);
			
			$results = $this->model_catalog_manufacturer->getManufacturers($data);

			if (isset($this->request->get['add_default']) && $this->request->get['add_default'] == 'y') {
                $json[] = array(
                    'manufacturer_id' => 0,
                    'name' => $this->language->get('text_none')
                );
            }
				
			foreach ($results as $result) {
				$json[] = array(
					'manufacturer_id' => $result['manufacturer_id'], 
					'name'            => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
				);
			}		
		}

		$sort_order = array();
	  
		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->setOutput(json_encode($json));
	}

	public function dtHandler()
    {
        $this->load->model('catalog/manufacturer');
        $request = $this->request->request;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        $start = $request['start'];
        $length = $request['length'];

        $columns = array(
            0 => 'manufacturer_id',
            1 => 'name',
            4 => 'sort_order',
        );

        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];

        $return = $this->model_catalog_manufacturer->dtHandler([
            'sort' => $orderColumn,
            'order' => $orderType,
            'start' => $start,
            'length' => $length
        ]);

        $records = $return['data'];
        $totalData = $return['total'];
        $totalFiltered = $return['totalFiltered'];


        $json_data = array(
            "draw" => intval($request['draw']),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => $totalFiltered,
            "data" => $records
        );
        $this->response->setOutput(json_encode($json_data));
        return;
    }

    public function dtDelete()
    {
		$this->language->load('catalog/manufacturer');
        $this->load->model('catalog/manufacturer');

        if (isset($this->request->post['selected']) && $this->validateDelete()) {

            foreach ($this->request->post['selected'] as $manufacturer_id) {
                $this->model_catalog_manufacturer->deleteManufacturer($manufacturer_id);
            }

            $result_json['success'] = '1';
            $result_json['success_msg'] = $this->language->get('text_bulkdelete_success');
        } else {
			$result_json['success'] = '0';
           	$result_json['errors'] = $this->error;
        }

        $this->response->setOutput(json_encode($result_json));
        return;
    }
}
