<?php
class ControllerUserUserPermission extends Controller {
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

    public function dtUserPermissionHandler()
    {
        $this->load->model('user/user_group');
        $request = $_REQUEST;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        $start = $request['start'];
        $length = $request['length'];

        $columns = array(
            0 => 'user_group_id',
            1 => 'name',
        );
        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];

        $return = $this->model_user_user_group->dtHandler($start, $length, $search, $orderColumn, $orderType);
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

	public function index()
	{
		$this->language->load('user/user_group');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('user/user_group');

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
		$this->language->load('user/user_group');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('user/user_group');

		if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
		{
			if ( !$this->validateForm() )
			{
				$result_json['success'] = '0';
				$result_json['errors'] = $this->error;
			}
			else
			{
				$id = $this->model_user_user_group->addUserGroup($this->request->post);
				//Add User Group Statuses if order_change_status checked & statuses added
				if($this->request->post['permission']['custom']['order_change_status'] && $this->request->post['user_group_order_statuses'])
			    	{
			 		 $this->model_user_user_group->addUserGroupOrderStatuses($id,$this->request->post['user_group_order_statuses']);
			    	}
		    		// add data to log_history
		    	$this->load->model('setting/audit_trail');
			    $pageStatus = $this->model_setting_audit_trail->getPageStatus("groups_permissions");
		    	if($pageStatus){
				$log_history['action'] = 'add';
				$log_history['reference_id'] = $id;
				$log_history['old_value'] = NULL;
				$log_history['new_value'] = json_encode($this->request->post,JSON_UNESCAPED_UNICODE);
				$log_history['type'] = 'groups_permissions';
				$this->load->model('loghistory/histories');
				$this->model_loghistory_histories->addHistory($log_history);
			                   }
				$result_json['success'] = '1';
				$result_json['success_msg'] = $this->language->get('text_success');
			}

			$this->response->setOutput(json_encode($result_json));
			return;
		}

		$this->getForm();
	}

	public function update()
	{
		$this->language->load('user/user_group');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('user/user_group');

		if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
		{
			if ( !$this->validateForm() )
			{
				$result_json['success'] = '0';
				$result_json['errors'] = $this->error;
			}
			else
			{    
			//Add User Group Statuses if order_change_status checked & statuses added	
			if($this->request->post['permission']['custom']['order_change_status'] )
				{
					if(isset($this->request->post['user_group_order_statuses']))
					{$this->model_user_user_group->addUserGroupOrderStatuses($this->request->get['user_group_id'],$this->request->post['user_group_order_statuses']);}	
				}
			else{  
					$this->model_user_user_group->DeleteUserGroupOrderStatuses($this->request->get['user_group_id']);
				}
				// add data to log_history
				$this->load->model('setting/audit_trail');
                $pageStatus = $this->model_setting_audit_trail->getPageStatus("groups_permissions");
                if($pageStatus){
					$oldValue = $this->model_user_user_group->getUserGroup($this->request->get['user_group_id']);    
					$log_history['action'] = 'update';
					$log_history['reference_id'] = $this->request->get['user_group_id'];
					$log_history['old_value'] = json_encode($oldValue,JSON_UNESCAPED_UNICODE);
					$log_history['new_value'] = json_encode($this->request->post,JSON_UNESCAPED_UNICODE);
					$log_history['type'] = 'groups_permissions';
					$this->load->model('loghistory/histories');
					$this->model_loghistory_histories->addHistory($log_history);
				}
				$this->model_user_user_group->editUserGroup($this->request->get['user_group_id'], $this->request->post);
				$result_json['success'] = '1';
				$result_json['success_msg'] = $this->language->get('text_success');
			}

			$this->response->setOutput(json_encode($result_json));
			return;
		}

		$this->getForm();
	}

	public function delete()
	{
		if($this->plan_id == 3){
			$this->redirect(
				$this->url->link('error/permission', '', 'SSL')
			);
			exit();
		}
		$this->language->load('user/user_group');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('user/user_group');

		if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
		{
			if ( !$this->validateDelete() )
			{
				$result_json['success'] = '0';
				$result_json['error'] = ':(';
			}
			else
			{
				$ids = $this->request->post['ids'];

				if (is_array($ids) == false) {
					$ids = [$ids];
				}

				if (in_array(1, $ids)) {
					$result_json['success'] = '0';
					$result_json['error'] = $this->language->get('error_user_group');
				} elseif ($usersCount = $this->model_user_user_group->ifGroupHasUsers($ids) !== false) {
					$result_json['success'] = '0';
					$result_json['error'] = [sprintf($this->language->get('error_user'), $usersCount)];
				} else {
					 // add data to log_history
					$this->load->model('setting/audit_trail');
					$pageStatus = $this->model_setting_audit_trail->getPageStatus("groups_permissions");
					if($pageStatus){
						$oldValue = $this->model_user_user_group->getUserGroup($this->request->post['ids']);
						 $log_history['action'] = 'delete';
						 $log_history['reference_id'] =$this->request->post['ids'];
						 $log_history['old_value'] = json_encode($oldValue,JSON_UNESCAPED_UNICODE);
						 $log_history['new_value'] = NULL;
						 $log_history['type'] = 'groups_permissions';
						 $this->load->model('loghistory/histories');
						 $this->model_loghistory_histories->addHistory($log_history);
						}

					$this->model_user_user_group->deleteOnlyEmptyUserGroup($ids);
					$result_json['success'] = '1';
					$result_json['success_msg'] = $this->language->get('text_success');
				}

				/*if ( is_array($ids) )
				{
					foreach ($ids as $id)
					{
						$this->model_user_user_group->deleteUserGroup($id);
					}
				}
				else
				{
					$this->model_user_user_group->deleteUserGroup($ids);
				}*/
			}

			$this->response->setOutput(json_encode($result_json));
			return;
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
			'href'      => $this->url->link('user/user_permission', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);

		$this->data['insert'] = $this->url->link('user/user_permission/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('user/user_permission/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');
        $this->data['users'] = $this->url->link('user/user', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['user_groups'] = array();

		$data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit' => $this->config->get('config_admin_limit')
		);

		$user_group_total = $this->model_user_user_group->getTotalUserGroups();

		$results = $this->model_user_user_group->getUserGroups($data);

		foreach ($results as $result) {
			$action = array();

			$action[] = array(
				'text' => $this->language->get('text_edit'),
				'href' => $this->url->link('user/user_permission/update', 'token=' . $this->session->data['token'] . '&user_group_id=' . $result['user_group_id'] . $url, 'SSL')
			);

			$this->data['user_groups'][] = array(
				'user_group_id' => $result['user_group_id'],
				'name'          => $result['name'],
				'selected'      => isset($this->request->post['selected']) && in_array($result['user_group_id'], $this->request->post['selected']),
				'action'        => $action
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

		$this->data['sort_name'] = $this->url->link('user/user_permission', 'token=' . $this->session->data['token'] . '&sort=name' . $url, 'SSL');

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $user_group_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('user/user_permission', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

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
       		'text'      => !isset($this->request->get['user_group_id']) ? $this->language->get('breadcrumb_insert') : $this->language->get('breadcrumb_update'),
			'href'      => $this->url->link('user/user_permission', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);

		if (!isset($this->request->get['user_group_id'])) {
			$this->data['action'] = $this->url->link('user/user_permission/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('user/user_permission/update', 'token=' . $this->session->data['token'] . '&user_group_id=' . $this->request->get['user_group_id'] . $url, 'SSL');
		}

    	$this->data['cancel'] = $this->url->link('user/user', 'token=' . $this->session->data['token'] . $url, 'SSL');
         
		//For Order Assignee App
		$this->load->model("module/order_assignee");
		$this->data['isOrderAssigneeAppInstalled']=$this->model_module_order_assignee->isOrderAssigneeAppInstalled();

		if (isset($this->request->get['user_group_id']) && $this->request->server['REQUEST_METHOD'] != 'POST') {
			$user_group_info = $this->model_user_user_group->getUserGroup($this->request->get['user_group_id']);
		}

		if (isset($this->request->post['name'])) {
			$this->data['name'] = $this->request->post['name'];
		} elseif (!empty($user_group_info)) {
			$this->data['name'] = $user_group_info['name'];
		} else {
			$this->data['name'] = '';
		}

		if (isset($this->request->post['description'])) {
			$this->data['description'] = $this->request->post['description'];
		} elseif (!empty($user_group_info)) {
			$this->data['description'] = $user_group_info['description'];
		} else {
			$this->data['description'] = '';
		}

		$ignore = array(
			'common/startup',
			'common/login',
			'common/logout',
			'common/forgotten',
			'common/reset',
			'error/not_found',
			'error/permission',
			'common/footer',
			'common/header'
		);

		$this->data['permissions'] = array();

//		$files = glob(DIR_APPLICATION . 'controller/*/*.php');
//
//		foreach ($files as $file) {
//			$data = explode('/', dirname($file));
//
//			$permission = end($data) . '/' . basename($file, '.php');
//
//			if (!in_array($permission, $ignore)) {
//				$this->data['permissions'][] = $permission;
//			}
//		}

        // Loop on all controllers files in folders and sub folders in controller path
        $it = new RecursiveDirectoryIterator(DIR_APPLICATION . 'controller');

        foreach(new RecursiveIteratorIterator($it) as $file) {
            if ($file->getExtension() == 'php') {
                $data = explode('/', dirname($file));

                $permission = str_replace('controller/', '', str_replace("\\", "/",end($data) . '/' . basename($file, '.php'))) ;

                if (!in_array(rtrim($permission, "/"), $ignore))
                {
                    $this->data['permissions'][] = rtrim($permission, "/");
                }
            }
		}
		///get Order Statuses list for Change Staus permission
		$this->load->model('localisation/order_status');
		$data['orderby']='order_status_id';
		$order_statuses = $this->model_localisation_order_status->getOrderStatuses($data['orderby']);
		$this->data['order_statuses'] = $order_statuses;
		
		///Get Order Statuses IDs allowed for the selected group to change
		if(isset($this->request->get['user_group_id']))
		   {
		$user_group_order_statuses = $this->model_user_user_group->getUserGroupOrderStatuses($this->request->get['user_group_id']);
		$this->data['user_group_order_statuses'] = $user_group_order_statuses;
	       }
	 	
        /**
         * Loop on all sub directories of controller path
         * Note: This loop is on sub folders only not on it's entire controllers
         */
        // Loop on all sub directories
        $sub_files = glob(DIR_APPLICATION . 'controller/*/*/');

        foreach ($sub_files as $file) {
            $data = explode('/', dirname($file));

            $permission = end($data) . '/' . basename($file);

            if (!in_array($permission, $ignore)) {
                $this->data['permissions'][] = $permission;
            }
        }

		if (isset($this->request->post['permission']['access'])) {
			$this->data['access'] = $this->request->post['permission']['access'];
		} elseif (isset($user_group_info['permission']['access'])) {
			$this->data['access'] = $user_group_info['permission']['access'];
		} else {
			$this->data['access'] = array();
		}

		if (isset($this->request->post['permission']['modify'])) {
			$this->data['modify'] = $this->request->post['permission']['modify'];
		} elseif (isset($user_group_info['permission']['modify'])) {
			$this->data['modify'] = $user_group_info['permission']['modify'];
		} else {
			$this->data['modify'] = array();
		}

        $this->data['customPermission'] = $user_group_info['permission']['custom'];

        $this->data['text_permission_custom_perms'] = $this->language->get('text_permission_custom_perms');
        $this->data['text_permission_deleteOrder'] = $this->language->get('text_permission_deleteOrder');
        $this->data['text_permission_deleteOrder_hint'] = $this->language->get('text_permission_deleteOrder_hint');

		$this->template = 'user/user_group_form.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}


	private function validateForm()
	{

		if ( !$this->user->hasPermission('modify', 'user/user_permission') )
		{
			$this->error['error'] = $this->language->get('error_permission');
		}

		if ( (utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64) )
		{
			$this->error['name'] = $this->language->get('error_name');
		}
		if( $this->request->post['permission']['custom']['order_change_status'] && $this->request->post['user_group_order_statuses'])
		{    
			$groupOrderStatuses=$this->request->post['user_group_order_statuses'];
				$validateGroupOrderStatusesnotEqualCols = $validateGroupOrderStatusesnotEqualRows = true;
			   for($i=0; $i<count($groupOrderStatuses['from_order_status_ids']); $i++){
			    	if($groupOrderStatuses['from_order_status_ids'][$i] == $groupOrderStatuses['to_order_status_ids'][$i])
				      {$validateGroupOrderStatusesnotEqualCols= false;}
			
				for($j=0; $j<count($groupOrderStatuses['from_order_status_ids']); $j++){
					 if($i!=$j){
						if(($groupOrderStatuses['from_order_status_ids'][$i] == $groupOrderStatuses['from_order_status_ids'][$j])
							&&($groupOrderStatuses['to_order_status_ids'][$i] == $groupOrderStatuses['to_order_status_ids'][$j]))
							{$validateGroupOrderStatusesnotEqualRows= false;}
					}
				}
			}
			if($validateGroupOrderStatusesnotEqualCols==false){ $this->error['changeStatusNotEqualCols']  = $this->language->get('error_userGroupOrderStatuses');}
			if($validateGroupOrderStatusesnotEqualRows==false){ $this->error['changeStatusNotEqualRows']  = $this->language->get('error_userGroupOrderStatusesRowsEqual');}
		}

		if ( $this->error && !isset($this->error['error']) )
		{
		    $this->error['warning'] = $this->language->get('error_warning');
		}

		return $this->error ? false : true;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'user/user_permission')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		$this->load->model('user/user');

		foreach ($this->request->post['selected'] as $user_group_id) {
			$user_total = $this->model_user_user->getTotalUsersByGroupId($user_group_id);

			if ($user_total) {
				$this->error['warning'] = sprintf($this->language->get('error_user'), $user_total);
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
