<?php  
class ControllerUserUser extends Controller {  
	private $error = array();

	private $plan_id = PRODUCTID;

	private $users_limit = null;

	private $last_user_in_limit_id = null;

	public function __construct($registry)
	{
		parent::__construct($registry);

		$this->load->model('plan/trial');
		$trial = $this->model_plan_trial->getLastTrial();
		if ($trial){
			$this->plan_id = $trial['plan_id'];
		}

		if ($this->config->get('platform_version') > 1.2 || $this->plan_id == 3 ){
			$this->users_limit = $this->genericConstants["plans_limits"][$this->plan_id]['users_limit']??null;

			if($this->users_limit) {
				$this->load->model('user/user');
				$this->last_user_in_limit_id = $this->model_user_user->getLastUserInLimitId($this->users_limit);
			}
		}

	}

    public function dtUserHandler()
    {
        $this->load->model('user/user');
        $request = $_REQUEST;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        $start = $request['start'];
        $length = $request['length'];

        $columns = array(
            0 => 'user_id',
            1 => 'username',
            2 => 'status',
            3 => 'date_added'
        );
        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];

        $return = $this->model_user_user->dtHandler($start, $length, $search, $orderColumn, $orderType);
        $records = $return['data'];
        $totalData = $return['total'];
        $totalFiltered = $return['totalFiltered'];

		if($this->users_limit){
			foreach ($records as $key => $record){
				if($this->last_user_in_limit_id && ($record['user_id'] > $this->last_user_in_limit_id) ){
					$records[$key]['limit_reached']=1;
				}else{
					$records[$key]['limit_reached']=0;
				}
			}
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

  	public function index()
    {
    	$this->language->load('user/user');

    	$this->document->setTitle($this->language->get('heading_title'));
	
		$this->load->model('user/user');
		
    	$this->getList();
  	}
   
  	
    public function insert()
    {

		$this->load->model('user/user');

		if($this->users_limit ){
			$total= $this->model_user_user->getTotalUsers();

			if ($total >= $this->users_limit ){
				$this->redirect(
					$this->url->link('error/permission', '', 'SSL')
				);
				exit();
			}
		}
    	$this->language->load('user/user');

    	$this->document->setTitle($this->language->get('heading_title'));
		

    	if ( $this->request->server['REQUEST_METHOD'] == 'POST')
        {

            if ( ! $this->validateForm() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
                
                $this->response->setOutput(json_encode($result_json));
                
                return;
            }

			$this->request->post['status']=1;
			$id = $this->model_user_user->addUser($this->request->post);
			// add data to log_history
			$this->load->model('setting/audit_trail');
			$pageStatus = $this->model_setting_audit_trail->getPageStatus("users_permissions");
			if($pageStatus){
				$log_history['action'] = 'add';
				$log_history['reference_id'] = $id;
				$log_history['old_value'] = NULL;
				$log_history['new_value'] = json_encode($this->request->post,JSON_UNESCAPED_UNICODE);
				$log_history['type'] = 'users_permissions';
				$this->load->model('loghistory/histories');
				$this->model_loghistory_histories->addHistory($log_history);
			}

			try {
				$storeName = $this->config->get('config_name');
				$storeName = is_array($storeName) ? array_pop(array_reverse($array)) : $storeName;
		        \ExpandCart\Foundation\Support\Hubspot::addContact([
		            'company' => $storeName,
		            'email' => $this->request->post['email'],
		            'firstname' => $this->request->post['firstname'],
		            'lastname' => $this->request->post['lastname'],
		            'ec_client_source' => 'ec_cs_merchant_admin',
		            'ec_store_code' => STORECODE,
		            'ec_store_name' => $storeName,
		        ]);
		    } catch (\Exception $e) {} catch (\Error $e) {}
			
			$this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');
						
			$result_json['success'] = '1';
			$result_json['redirect'] = '1';
			$result_json['to'] = $this->url->link('/user/user','',true)->format();
            $this->response->setOutput(json_encode($result_json));
            
            return;
    	}
	
    	$this->getForm();
  	}

  	public function update() {
    	$this->language->load('user/user');

    	$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('user/user');
		
    	if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {
            if ( $this->validateForm() )
            {
				$this->load->model('setting/audit_trail');
                $pageStatus = $this->model_setting_audit_trail->getPageStatus("users_permissions");
                if($pageStatus){

					$oldValue = $this->model_user_user->getUser($this->request->get['user_id']);
				    // add data to log_history
					$log_history['action'] = 'update';
					$log_history['reference_id'] = $this->request->get['user_id'];
					$log_history['old_value'] = json_encode($oldValue,JSON_UNESCAPED_UNICODE);
					$log_history['new_value'] = json_encode($this->request->post,JSON_UNESCAPED_UNICODE);
					$log_history['type'] = 'users_permissions';
					$this->load->model('loghistory/histories');
					$this->model_loghistory_histories->addHistory($log_history);
				}

				if($this->last_user_in_limit_id && ($this->request->get["user_id"] > $this->last_user_in_limit_id) ){
					$this->request->post['status']=0;
				}
				$id = $this->model_user_user->editUser($this->request->get['user_id'], $this->request->post);
			
                $result_json['success'] = '1';
                $result_json['success_msg'] = $this->language->get('text_success');
                $result_json['redirect'] = '1';
                $result_json['to'] = $this->url->link('/user/user','',true)->format();
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
 
  	public function delete()
    {
//		if($this->plan_id == 3){
//			$this->redirect(
//				$this->url->link('error/permission', '', 'SSL')
//			);
//			exit();
//		}
    	$this->language->load('user/user');

    	$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('user/user');
		
    	if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            if (!$this->validateDelete()) {
                $result_json['success'] = '0';
                $result_json['error'] = ':(';
            } else {
                $ids = $this->request->post['ids'];

                $tmpids = is_array($ids) ? $ids : [$ids];

                if (in_array(1, $tmpids)) {
                    $this->response->setOutput(json_encode([
                        'success' => '0',
                        'error' => [$this->language->get('protected_user')]
                    ]));
                    return;
                }

				unset($tmpids);
				$this->load->model('setting/audit_trail');
				$pageStatus = $this->model_setting_audit_trail->getPageStatus("users_permissions");

                if (is_array($ids)) {
                    foreach ($ids as $user_id) {		
				if($pageStatus){
					/// add log when delete selected Ids
					$oldValue = $this->model_user_user->getUser($user_id);
					 // add data to log_history
					 $log_history['action'] = 'delete';
					 $log_history['reference_id'] =$user_id;
					 $log_history['old_value'] = json_encode($oldValue,JSON_UNESCAPED_UNICODE);
					 $log_history['new_value'] = NULL;
					 $log_history['type'] = 'users_permissions';
					 $this->load->model('loghistory/histories');
					 $this->model_loghistory_histories->addHistory($log_history);
					}
                        $this->model_user_user->deleteUser($user_id);
                    }
                } else {
					if($pageStatus){
						/// add log when delete one user
						$oldValue = $this->model_user_user->getUser($ids);
						 // add data to log_history
						 $log_history['action'] = 'delete';
						 $log_history['reference_id'] =$ids;
						 $log_history['old_value'] = json_encode($oldValue,JSON_UNESCAPED_UNICODE);
						 $log_history['new_value'] = NULL;
						 $log_history['type'] = 'users_permissions';
						 $this->load->model('loghistory/histories');
						 $this->model_loghistory_histories->addHistory($log_history);
						}

                    $this->model_user_user->deleteUser($ids);
                }

                $result_json['success'] = '1';
                $result_json['success_msg'] = $this->language->get('text_success');
            }

            $this->response->setOutput(json_encode($result_json));
            return;
    	}
	
    	$this->getList();
  	}

	public function changeStatus()
	{
		$this->load->model("user/user");

		if(isset($this->request->post["id"]) && isset($this->request->post["status"])) {

			if($this->last_user_in_limit_id && $this->request->post["id"] > $this->last_user_in_limit_id){
				$result_json['limit_reached'] = '1';
				$result_json['error'] = $this->language->get('not_allowed');
			}else{
				$id = $this->request->post["id"];
				$status = $this->request->post["status"];

				$this->model_user_user->changeStatus($id,$status);

				$result_json['success'] = '1';
				$result_json['success_msg'] = $this->language->get('text_langstatus_success');
			}

		} else {
			$result_json['success'] = '0';
			$result_json['error'] = $this->language->get('text_langstatus_error');
		}

		$this->response->setOutput(json_encode($result_json));
	}

	protected function getList() {
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'username';
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
			'href'      => $this->url->link('user/user', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);
			
		$this->data['insert'] = $this->url->link('user/user/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
        $this->data['delete'] = $this->url->link('user/user/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');
        $this->data['usergroups'] = $this->url->link('user/user_permission', 'token=' . $this->session->data['token'] . $url, 'SSL');
			
    	$this->data['users'] = array();

		$data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit' => $this->config->get('config_admin_limit')
		);
		
		$user_total = $this->model_user_user->getTotalUsers();
		
		$results = $this->model_user_user->getUsers($data);
    	
		foreach ($results as $result) {
			$action = array();
			
			$action[] = array(
				'text' => $this->language->get('text_edit'),
				'href' => $this->url->link('user/user/update', 'token=' . $this->session->data['token'] . '&user_id=' . $result['user_id'] . $url, 'SSL')
			);
					
      		$this->data['users'][] = array(
				'user_id'    => $result['user_id'],
				'username'   => $result['username'],
				'status'     => ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'selected'   => isset($this->request->post['selected']) && in_array($result['user_id'], $this->request->post['selected']),
				'action'     => $action
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
					
		$this->data['sort_username'] = $this->url->link('user/user', 'token=' . $this->session->data['token'] . '&sort=username' . $url, 'SSL');
		$this->data['sort_status'] = $this->url->link('user/user', 'token=' . $this->session->data['token'] . '&sort=status' . $url, 'SSL');
		$this->data['sort_date_added'] = $this->url->link('user/user', 'token=' . $this->session->data['token'] . '&sort=date_added' . $url, 'SSL');
		
		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}
												
		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
				
		$pagination = new Pagination();
		$pagination->total = $user_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('user/user', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');
			
		$this->data['pagination'] = $pagination->render();
								
		$this->data['sort'] = $sort;
		$this->data['order'] = $order;
		$this->data['currentUser']=$this->user->getId();

		$this->load->model('user/user_group');
		$this->data['user_groups'] = $this->model_user_user_group->getUserGroups();

		if($this->users_limit ){
			$total= $this->model_user_user->getTotalUsers();

			if ($total >= $this->users_limit ){
				$this->data['limit_reached'] =1;
			}
		}
		$this->getOutletData();

		$this->template = 'user/user_user_group_list.expand';
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
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('user/user', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);

        $this->data['breadcrumbs'][] = array(
            'text'      => !isset($this->request->get['user_id']) ? $this->language->get('breadcrumb_insert') : $this->language->get('breadcrumb_update'),
            'href'      => $this->url->link('user/user', 'token=' . $this->session->data['token'] . $url, 'SSL'),
            'separator' => ' :: '
        );

		if (!isset($this->request->get['user_id'])) {
			$this->data['action'] = $this->url->link('user/user/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('user/user/update', 'token=' . $this->session->data['token'] . '&user_id=' . $this->request->get['user_id'] . $url, 'SSL');
		}

    	$this->data['cancel'] = $this->url->link('user/user', 'token=' . $this->session->data['token'] . $url, 'SSL');

    	if (isset($this->request->get['user_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
      		$user_info = $this->model_user_user->getUser($this->request->get['user_id']);
    	}

    	$this->data['status'] = $user_info['status'];

    	if (isset($this->request->post['username'])) {
      		$this->data['username'] = $this->request->post['username'];
    	} elseif (!empty($user_info)) {
			$this->data['username'] = $user_info['username'];
		} else {
      		$this->data['username'] = '';
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

    	if (isset($this->request->post['user_group_id'])) {
      		$this->data['user_group_id'] = $this->request->post['user_group_id'];
    	} elseif (!empty($user_info)) {
			$this->data['user_group_id'] = $user_info['user_group_id'];
		} else {
      		$this->data['user_group_id'] = '';
    	}

    	$this->data['thumb'] = $user_info['image'] ? $user_info['image'] : $this->user->getRandomImage();
		
		$this->load->model('user/user_group');
		
    	$this->data['user_groups'] = $this->model_user_user_group->getUserGroups();
 
     	if (isset($this->request->post['status'])) {
      		$this->data['status'] = $this->request->post['status'];
    	} elseif (!empty($user_info)) {
			$this->data['status'] = $user_info['status'];
		} else {
      		$this->data['status'] = null;
     	}

     	$this->getOutletData($user_info);

     	if ($this->request->get['ajax']== 1){

			if (!isset($this->request->get['user_id'])) {
				$this->data['action'] = $this->url->link('user/user/insert', 'token=' . $this->session->data['token'] . $url, 'SSL')->format();
			} else {
				$this->data['action'] = $this->url->link(
					'user/user/update', 'token=' . $this->session->data['token'] . '&user_id=' . $this->request->get['user_id'] . $url, 'SSL'
				)->format();
			}

			$json_data = array(
				"success" => 1,
				"data" => $this->data
			);

			$this->response->setOutput(json_encode($json_data));
			return;
		}

		$this->template = 'user/user_form.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
  	}
  	
  	
    private function validateForm()
    {
    	if ( !$this->user->hasPermission('modify', 'user/user') )
        {
      		$this->error['error'] = $this->language->get('error_permission');
    	}
    
    	if ( (utf8_strlen($this->request->post['username']) < 3) || (utf8_strlen($this->request->post['username']) > 20) )
        {
      		$this->error['username'] = $this->language->get('error_username');
    	}
		
		$user_info = $this->model_user_user->getUserByUsername($this->request->post['username']);
		
		if (!isset($this->request->get['user_id'])) {
			if ($user_info) {
				$this->error['username'] = $this->language->get('error_exists');
			}
		} else {
			if ($user_info && ($this->request->get['user_id'] != $user_info['user_id'])) {
				$this->error['username'] = $this->language->get('error_exists');
			}
		}

		if ((utf8_strlen($this->request->post['email']) > 96) || !preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $this->request->post['email'])) {
			$this->error['email'] = $this->language->get('error_email');
		}

        if ( ! $this->request->post['email'] )
        {
            $this->error['email'] = $this->language->get('error_field_cant_be_empty');
        }

		if ( ! $this->request->post['user_group_id'] )
		{
			$this->error['user_group_id'] = $this->language->get('error_field_cant_be_empty');
		}
    	if ((utf8_strlen($this->request->post['firstname']) < 1) || (utf8_strlen($this->request->post['firstname']) > 32)) {
			$this->error['firstname'] = $this->language->get('error_firstname');
    	}

    	if ((utf8_strlen($this->request->post['lastname']) < 1) || (utf8_strlen($this->request->post['lastname']) > 32)) {
      		$this->error['lastname'] = $this->language->get('error_lastname');
    	}

    	if ( $this->request->post['password'] || (!isset($this->request->get['user_id'])) )
        {
      		if ( (utf8_strlen($this->request->post['password']) < 4) || (utf8_strlen($this->request->post['password']) > 20) )
            {
        		$this->error['password'] = $this->language->get('error_password');
      		}
	
	  		if ( $this->request->post['password'] != $this->request->post['confirm'] )
            {
	    		$this->error['confirm'] = $this->language->get('error_confirm');
	  		}
    	}
	
//    	if ( $this->error && !isset($this->error['error']) && !isset($this->error['warning']))
//        {
//            $this->error['warning'] = $this->language->get('error_warning');
//        }
        
        return $this->error ? false : true;
  	}


  	protected function validateDelete() { 
    	if (!$this->user->hasPermission('modify', 'user/user')) {
      		$this->error['warning'] = $this->language->get('error_permission');
    	} 
	  	  
		foreach ($this->request->post['selected'] as $user_id) {
			if ($this->user->getId() == $user_id) {
				$this->error['warning'] = $this->language->get('error_account');
			}
		}
		 
		if (!$this->error) {
	  		return true;
		} else { 
	  		return false;
		}
  	}

	private function getOutletData($user_info = null)
	{
		//POS check
		$this->data['show_outlet'] = false;
		$this->load->model('wkpos/wkpos');
		$wkpos_init = $this->config->get('wkpos_init');
		if($this->model_wkpos_wkpos->is_installed() && $wkpos_init && $this->model_user_user->isOutletExists()){
			$this->data['show_outlet'] = true;
			$this->load->model('wkpos/outlet');
			$this->data['outlets'] = $this->model_wkpos_outlet->getOutlets();

			if (isset($this->request->post['outlet_id'])) {
				$this->data['outlet_id'] = $this->request->post['outlet_id'];
			} elseif (!empty($user_info)) {
				$this->data['outlet_id'] = $user_info['outlet_id'];
			} else {
				$this->data['outlet_id'] = '';
			}
		}
	}
}
?>
