<?php
/**
 * User: Anh TO
 * Date: 9/30/14
 * Time: 8:58 AM
 */

class ControllerPromotionsRewardPoints extends Controller
{
    private $error = array();

	CONST DO_NOT_ALLOW_USE_REWARD = 1;
	CONST USE_UNLIMITED_REWARD    = 2;
	CONST USE_FIXED_REWARD        = 3;
	CONST SPEND_Y_TO_GET_X_REWARD = 4;

	public function shoppingCartRuleDelete()
	{
		$this->load->model('promotions/reward_points');
		$this->model_promotions_reward_points->deleteShoppingCartRule($this->request->get['rule_id']);
		$this->session->data['success'] = $this->language->get('text_success');
		$this->redirect($this->url->link('promotions/reward_points/shoppingCartRuleList', 'token=' . $this->session->data['token'], 'SSL'));
	}

    public function dtShoppingCartRulesDelete()
    {
        $this->load->model('promotions/reward_points');
        $this->language->load('module/reward_points_pro');
        if ( !isset($this->request->post['id']) )
        {
            $result_json['success'] = '0';
            $result_json['errors']['error'] =  '';
            return $this->response->setOutput(json_encode($result_json));
        }
        $id_s = $this->request->post['id'];

        if ( is_array($id_s) )
        {
            foreach ($id_s as $rule_id)
            {
                if ( $this->model_promotions_reward_points->deleteShoppingCartRule( (int) $rule_id ) )
                {
                    $result_json['success'] = '1';
                    $result_json['success_msg'] =  $this->language->get('text_success_delete');
                }
                else
                {
                    $result_json['success'] = '0';
                    $result_json['errors']['error'] = '';
                    break;
                }
            }
        }
        else
        {
            $rule_id = (int) $id_s;

            if ( $this->model_promotions_reward_points->deleteShoppingCartRule($rule_id) )
            {
                $result_json['success'] = '1';
                $result_json['success_msg'] =  $this->language->get('text_success_delete');
            }
            else
            {
                $result_json['success'] = '0';
                $result_json['errors']['error'] = '';
            }
        }
        return $this->response->setOutput(json_encode($result_json));
    }

	public function behaviorRuleDelete()
	{
		$this->load->model('promotions/reward_points');
		$this->model_promotions_reward_points->deleteBehaviorRule($this->request->get['rule_id']);
		$this->session->data['success'] = $this->language->get('text_success');
		$this->redirect($this->url->link('promotions/reward_points/behaviorRuleList', 'token=' . $this->session->data['token'], 'SSL'));
	}

    public function dtCustomerBehaviorRulesDelete()
    {
        $this->load->model('promotions/reward_points');
        $this->language->load('module/reward_points_pro');
        if ( !isset($this->request->post['id']) )
        {
            $result_json['success'] = '0';
            $result_json['errors']['error'] =  '';
            return $this->response->setOutput(json_encode($result_json));
        }
        $id_s = $this->request->post['id'];

        if ( is_array($id_s) )
        {
            foreach ($id_s as $rule_id)
            {
                if ( $this->model_promotions_reward_points->deleteBehaviorRule( (int) $rule_id ) )
                {
                    $result_json['success'] = '1';
                    $result_json['success_msg'] =  $this->language->get('text_success_delete');
                }
                else
                {
                    $result_json['success'] = '0';
                    $result_json['errors']['error'] = '';
                    break;
                }
            }
        }
        else
        {
            $rule_id = (int) $id_s;

            if ( $this->model_promotions_reward_points->deleteBehaviorRule($rule_id) )
            {
                $result_json['success'] = '1';
                $result_json['success_msg'] =  $this->language->get('text_success_delete');
            }
            else
            {
                $result_json['success'] = '0';
                $result_json['errors']['error'] = '';
            }
        }
        return $this->response->setOutput(json_encode($result_json));
    }

	public function dtCatalogEarningRulesDelete()
	{
		$this->load->model('promotions/reward_points');
        $this->language->load('module/reward_points_pro');
        if ( !isset($this->request->post['id']) )
        {
            $result_json['success'] = '0';
            $result_json['success_msg'] =  '';
            return $this->response->setOutput(json_encode($result_json));
        }
        $id_s = $this->request->post['id'];

        if ( is_array($id_s) )
        {
            foreach ($id_s as $cat_id)
            {
                if ( $this->model_promotions_reward_points->deleteCatalogRule( (int) $cat_id ) )
                {
                    $result_json['success'] = '1';
                    $result_json['success_msg'] =  $this->language->get('text_success_delete');
                }
                else
                {
                    $result_json['success'] = '0';
                    $result_json['success_msg'] = '';
                    break;
                }
            }
        }
        else
        {
            $rule_id = (int) $id_s;

            if ( $this->model_promotions_reward_points->deleteCatalogRule($rule_id) )
            {
                $result_json['success'] = '1';
                $result_json['success_msg'] =  $this->language->get('text_success_delete');
            }
            else
            {
                $result_json['success'] = '0';
                $result_json['success_msg'] = '';
            }
        }
        return $this->response->setOutput(json_encode($result_json));
	}

	public function spendingRuleDelete()
	{
		$this->load->model('promotions/reward_points');
		$this->model_promotions_reward_points->deleteSpendingRule($this->request->get['rule_id']);
		$this->session->data['success'] = $this->language->get('text_success');
		$this->redirect($this->url->link('promotions/reward_points/spendingRuleList', 'token=' . $this->session->data['token'], 'SSL'));
	}

    public function dtSpendingRulesDelete()
    {
        $this->load->model('promotions/reward_points');
        $this->language->load('module/reward_points_pro');
        if ( !isset($this->request->post['id']) )
        {
            $result_json['success'] = '0';
            $result_json['errors']['error'] =  '';
            return $this->response->setOutput(json_encode($result_json));
        }
        $id_s = $this->request->post['id'];

        if ( is_array($id_s) )
        {
            foreach ($id_s as $rule_id)
            {
                if ( $this->model_promotions_reward_points->deleteSpendingRule( (int) $rule_id ) )
                {
                    $result_json['success'] = '1';
                    $result_json['success_msg'] =  $this->language->get('text_success_delete');
                }
                else
                {
                    $result_json['success'] = '0';
                    $result_json['errors']['error'] = '';
                    break;
                }
            }
        }
        else
        {
            $rule_id = (int) $id_s;

            if ( $this->model_promotions_reward_points->deleteSpendingRule($rule_id) )
            {
                $result_json['success'] = '1';
                $result_json['success_msg'] =  $this->language->get('text_success_delete');
            }
            else
            {
                $result_json['success'] = '0';
                $result_json['errors']['error'] = '';
            }
        }
        return $this->response->setOutput(json_encode($result_json));
    }

	public function configuration()
	{
		$this->document->addStyle('view/stylesheet/rewardpoints/stylesheet.css');

		$this->language->load('module/reward_points_pro');
		$this->document->setTitle($this->language->get('heading_title_configuration'));

		$this->data['heading_title'] = $this->language->get('heading_title_configuration');
		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateConfiguration()) {
			/** DISPATCH_EVENT:CONFIGURATION_BEFORE_SAVE_SETTING */
			//var_dump($this->request->post);
			//die();
			$this->model_setting_setting->editSetting('reward_points', $this->request->post);
            $result_json['success'] = '1';
            $result_json['success_msg'] =  $this->language->get('text_success');
            $this->response->setOutput(json_encode($result_json));
            return;
		}

		$this->getFormConfiguration();
	}

	public function shoppingCartRuleEdit()
	{
		$this->document->addStyle('view/stylesheet/rewardpoints/stylesheet.css');
		$this->document->addScript('view/javascript/rewardpoints/lib/underscore.js');
		$this->document->addScript('view/javascript/rewardpoints/lib/backbone.js');
		$this->document->addScript('view/javascript/rewardpoints/head.main.js');
		$this->document->addScript('view/javascript/rewardpoints/view.js');

		$this->language->load('module/reward_points_pro');
		$this->document->setTitle($this->language->get('heading_title_shopping_cart_rule'));

		$this->data['heading_title'] = $this->language->get('heading_title_shopping_cart_rule');

		if($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateForm())
		{
			$this->shoppingCartRuleUpdate();
            $result_json['success'] = '1';
            $result_json['success_msg'] =  $this->language->get('text_success');
            $this->response->setOutput(json_encode($result_json));
            return;
		}

		$this->getFormShoppingCartRule();
	}

	public function shoppingCartRuleList()
	{
		$this->load->model('promotions/reward_points');
		$this->language->load('module/reward_points_pro');
		$this->document->setTitle($this->language->get('heading_title_shopping_cart_rule'));
		$this->data['heading_title'] = $this->language->get('heading_title_shopping_cart_rule');

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'rule_id';
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

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('marketplace/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		);

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title_shopping_cart_rule'),
			'href'      => $this->url->link('prmotions/reward_points/shoppingCartRuleList', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		);

		$this->data['add_rule'] = $this->url->link('promotions/reward_points/shoppingCartRuleEdit', 'token=' . $this->session->data['token'], 'SSL');

		/*
		$this->data['rules'] = array();

		$data = array(
			'sort'            => $sort,
			'order'           => $order,
			'start'           => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit'           => $this->config->get('config_admin_limit')
		);
		$total_rule = $this->model_promotions_reward_points->getTotalShoppingCartRules();
		$this->data['rules'] = $this->model_promotions_reward_points->getShoppingCartRules($data);

		$this->data['token'] = $this->session->data['token'];
		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}
        */
		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}
        /*
		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $total_rule;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('promotions/reward_points/shoppingCartRuleList', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		*/
		$this->template = 'promotions/reward_points/shopping_cart_rule/list.expand';

		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}

    /*
     * Get catalog earning rules for datatable
     */
    public function dtShoppingCartRulesHandler() {
        $this->load->model('promotions/reward_points');
        $this->language->load('module/reward_points_pro');

        $request = $_REQUEST;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        $start = $request['start'];
        $length = $request['length'];

        $columns = array(
            0 => 'rule_id',
            1 => 'name',
            2 => 'start_date',
            3 => 'end_date',
            4 => 'status',
            5 => '',
        );
        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];


        $return = $this->model_promotions_reward_points->dtShoppingCartRulesHandler($start, $length, $search, $orderColumn, $orderType);
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

    public function spendingRuleEdit()
    {
        $this->document->addStyle('view/stylesheet/rewardpoints/stylesheet.css');
        $this->document->addScript('view/javascript/rewardpoints/lib/underscore.js');
        $this->document->addScript('view/javascript/rewardpoints/lib/backbone.js');
        $this->document->addScript('view/javascript/rewardpoints/head.main.js');
        $this->document->addScript('view/javascript/rewardpoints/view.js');

        $this->language->load('module/reward_points_pro');
        $this->document->setTitle($this->language->get('heading_title_spending_rule'));

        $this->data['heading_title'] = $this->language->get('heading_title_spending_rule');

        if($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateForm())
        {
            $this->spendingRuleUpdate();
            $result_json['success'] = '1';
            $result_json['success_msg'] =  $this->language->get('text_success');
            $this->response->setOutput(json_encode($result_json));
            return;
        }

        $this->getFormSpendingRule();
    }

	public function allTransactionHistory()
	{
		$this->document->addStyle('view/stylesheet/rewardpoints/stylesheet.css');
		$this->document->addScript('view/javascript/rewardpoints/lib/underscore.js');
		$this->document->addScript('view/javascript/rewardpoints/lib/backbone.js');
		$this->document->addScript('view/javascript/rewardpoints/head.main.js');
		$this->document->addScript('view/javascript/rewardpoints/view.js');


		$this->load->model('promotions/reward_points');
		$this->language->load('module/reward_points_pro');
		$this->document->setTitle($this->language->get('heading_title_transaction_history'));

		$this->data['heading_title'] = $this->language->get('heading_title_transaction_history');

        $this->data['filter_date_start']=date('Y/m/d',time());
        $this->data['filter_date_end']=date('Y/m/d',time());

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('marketplace/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		);

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title_transaction_history'),
			'href'      => $this->url->link('promotions/reward_points/allTransactionHistory', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		);

		$this->data['action'] = $this->url->link('promotions/reward_points/dtTransactionHistoryHandler', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['total_stats_action'] = $this->url->link('promotions/reward_points/getAllTotalRewards', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['url_post_status'] = $this->url->link('promotions/reward_points/updateStatusReward', 'token=' . $this->session->data['token'], 'SSL');

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

		$this->template = 'promotions/reward_points/transactions/list.expand';

		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}

    /*
     * Get customer behavior rules for datatable
     */
    public function dtTransactionHistoryHandler() {



        $this->load->model('promotions/reward_points');
        $this->language->load('module/reward_points_pro');

        $request = $_REQUEST;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;
        $dates= $request['date'];



        $start = $request['start'];
        $length = $request['length'];

        $columns = array(
            0 => 'customer_reward_id',
            1 => 'date_added',
            2 => 'customer_name',
            3 => 'customer_email',
            4 => 'points',
            5 => 'description',
            6 => 'status',
        );
        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];


        $data=array();

        if ($dates != ''){
            $dates=explode(' - ',$dates);
            $data['start_date']=$dates[0];
            $data['end_date']=$dates[1];
        }

        $return = $this->model_promotions_reward_points->dtTransactionHistoryHandler($data,$start, $length, $search, $orderColumn, $orderType);
        if(isset($return)){
            $records = $return['data'];
            $totalData = $return['total'];
            $totalFiltered = $return['totalFiltered'];

            $json_data = array(
                "draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
                "recordsTotal" => intval($totalData), // total number of records
                "recordsFiltered" => $totalFiltered, // total number of records after searching, if there is no searching then totalFiltered = totalData
                "data" => $records,   // total data array
            );

        }
        else{
            $json_data = array(
                "draw" => 0, // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
                "recordsTotal" => 0, // total number of records
                "recordsFiltered" => 0, // total number of records after searching, if there is no searching then totalFiltered = totalData
                "data" => [],   // total data array
            );
        }

        $this->response->setOutput(json_encode($json_data));
        return;
    }

    public function getAllTotalRewards(){

        $this->load->model('promotions/reward_points');
        $this->language->load('module/reward_points_pro');

        $request = $_REQUEST;
        $dates= $request['date'];

        $data=array();

        if ($dates != ''){
            $dates=explode(' - ',$dates);
            $data['start_date']=$dates[0];
            $data['end_date']=$dates[1];
        }

        $result=$this->model_promotions_reward_points->getAllTotalRewards($data);
        $config=$this->config->get('text_points_'.$this->language->get('code'));

        $result['total_rewarded']=number_format($result['total_rewarded'])." ".$config;
        $result['total_redeemed']=number_format($result['total_redeemed'])." ".$config;
        $this->response->setOutput(json_encode($result));
        return;
    }

	public function updateStatusReward(){
        $this->language->load('module/reward_points_pro');
		$this->load->model('promotions/reward_points_transactions');
		$this->model_promotions_reward_points_transactions->updateStatusReward($this->request->post);

        $result_json['success'] = '1';
        $result_json['success_msg'] =  $this->language->get('text_success');
        $this->response->setOutput(json_encode($result_json));
        return;

	}


	public function behaviorRuleList()
	{
		$this->document->addStyle('view/stylesheet/rewardpoints/stylesheet.css');
		$this->load->model('promotions/reward_points');
		$this->language->load('module/reward_points_pro');
		$this->document->setTitle($this->language->get('heading_title_behavior_rule'));

		$this->data['heading_title'] = $this->language->get('heading_title_behavior_rule');

		/*
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'rule_id';
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
        */
		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('marketplace/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		);

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title_behavior_rule'),
			'href'      => $this->url->link('promotions/reward_points/behaviorRuleList', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		);
		$this->data['add_rule'] = $this->url->link('promotions/reward_points/behaviorRuleEdit', 'token=' . $this->session->data['token'], 'SSL');
		/*
		$this->data['rules'] = array();

		$data = array(
			'sort'            => $sort,
			'order'           => $order,
			'start'           => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit'           => $this->config->get('config_admin_limit')
		);
		$total_rule = $this->model_promotions_reward_points->getTotalBehaviorRules();
		$this->data['rules'] = $this->model_promotions_reward_points->getBehaviorRules($data);

		$this->data['token'] = $this->session->data['token'];
        */
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

		/*
		$pagination = new Pagination();
		$pagination->total = 0;
		$pagination->page = 0;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('promotions/reward_points/behaviorRuleList', 'token=' . $this->session->data['token'] . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		*/
		$this->template = 'promotions/reward_points/behavior_rule/list.expand';

		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}

    /*
     * Get customer behavior rules for datatable
     */
    public function dtCustomerBehaviorRulesHandler() {
        $this->load->model('promotions/reward_points');
        $this->language->load('module/reward_points_pro');

        $request = $_REQUEST;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        $start = $request['start'];
        $length = $request['length'];

        $columns = array(
            0 => 'rule_id',
            1 => 'name',
            2 => 'actions',
            3 => 'reward_point',
            4 => 'status',
            5 => '',
        );
        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];


        $return = $this->model_promotions_reward_points->dtCustomerBehaviorRulesHandler($start, $length, $search, $orderColumn, $orderType);
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

	public function behaviorRuleEdit()
	{
		$this->document->addStyle('view/stylesheet/rewardpoints/stylesheet.css');

		$this->language->load('module/reward_points_pro');
		$this->document->setTitle($this->language->get('heading_title_behavior_rule'));

		$this->data['heading_title'] = $this->language->get('heading_title_behavior_rule');

		if($this->request->server['REQUEST_METHOD'] == 'POST')
		{
		    if($this->validateForm()){
                $this->behaviorRuleUpdate();
                $result_json['success'] = '1';
                $result_json['success_msg'] =  $this->language->get('text_success');
            }
            else{
                $result_json['error'] = '0';
                $result_json['errors'] =  $this->error;
            }
			return $this->response->setOutput(json_encode($result_json));
		}
		$this->getFormBehaviorRule();
	}

	public function spendingRuleList()
	{
		$this->load->model('promotions/reward_points');
		$this->language->load('module/reward_points_pro');
		$this->document->setTitle($this->language->get('heading_title_spending_rule'));
		$this->data['heading_title'] = $this->language->get('heading_title_spending_rule');

		/*
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'rule_id';
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

		*/
		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('marketplace/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		);

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title_spending_rule'),
			'href'      => $this->url->link('promotions/reward_points/spendingRuleList', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		);

		$this->data['add_rule'] = $this->url->link('promotions/reward_points/spendingRuleEdit', 'token=' . $this->session->data['token'], 'SSL');
		/*
		$this->data['rules'] = array();

		$data = array(
			'sort'            => $sort,
			'order'           => $order,
			'start'           => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit'           => $this->config->get('config_admin_limit')
		);
		$total_rule = $this->model_promotions_reward_points->getTotalSpendingRules();
		$this->data['rules'] = $this->model_promotions_reward_points->getSpendingRules($data);

		*/
		$this->data['token'] = $this->session->data['token'];
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

		/*
		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $total_rule;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('promotions/reward_points/spendingRuleList', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		*/
		$this->template = 'promotions/reward_points/spending_rule/list.expand';

		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}

    /*
     * Get catalog earning rules for datatable
     */
    public function dtSpendingRulesHandler() {
        $this->load->model('promotions/reward_points');
        $this->language->load('module/reward_points_pro');

        $request = $_REQUEST;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        $start = $request['start'];
        $length = $request['length'];

        $columns = array(
            0 => 'rule_id',
            1 => 'name',
            2 => 'start_date',
            3 => 'end_date',
            4 => 'status',
            5 => '',
        );
        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];


        $return = $this->model_promotions_reward_points->dtSpendingRulesHandler($start, $length, $search, $orderColumn, $orderType);
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

	public function catalogRuleList()
    {
        $this->load->model('promotions/reward_points');
        $this->language->load('module/reward_points_pro');
        $this->document->setTitle($this->language->get('heading_title_catalog_rule'));
        $this->data['heading_title'] = $this->language->get('heading_title_catalog_rule');

        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'rule_id';
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

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('marketplace/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title_catalog_rule'),
            'href'      => $this->url->link('promotions/reward_points/catalogRuleList', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $this->data['add_rule'] = $this->url->link('promotions/reward_points/catalogRuleEdit', 'token=' . $this->session->data['token'], 'SSL');

        /*
        $this->data['rules'] = array();

        $data = array(
            'sort'            => $sort,
            'order'           => $order,
            'start'           => ($page - 1) * $this->config->get('config_admin_limit'),
            'limit'           => $this->config->get('config_admin_limit')
        );
        $total_rule = $this->model_promotions_reward_points->getTotalCatalogRules();
        $this->data['rules'] = $this->model_promotions_reward_points->getCatalogRules($data);
        */
        $this->data['token'] = $this->session->data['token'];
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

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        /*
        $pagination = new Pagination();
        $pagination->total = $total_rule;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_admin_limit');
        $pagination->text = $this->language->get('text_pagination');
        $pagination->url = $this->url->link('promotions/reward_points/catalogRuleList', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

        $this->data['pagination'] = $pagination->render();

        $this->data['sort'] = $sort;
        $this->data['order'] = $order;

        */
        $this->template = 'promotions/reward_points/catalog_rule/list.expand';

        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }

    /*
     * Get catalog earning rules for datatable
     */
    public function dtCatalogEarningRulesHandler() {
        $this->load->model('promotions/reward_points');
        $this->language->load('module/reward_points_pro');

        $request = $_REQUEST;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        $start = $request['start'];
        $length = $request['length'];

        $columns = array(
            0 => 'rule_id',
            1 => 'name',
            2 => 'start_date',
            3 => 'end_date',
            4 => 'status',
            5 => '',
        );
        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];


        $return = $this->model_promotions_reward_points->dtCatalogRulesHandler($start, $length, $search, $orderColumn, $orderType);
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

    public function catalogRuleEdit()
    {
        $this->document->addStyle('view/stylesheet/rewardpoints/stylesheet.css');
        $this->document->addScript('view/javascript/rewardpoints/lib/underscore.js');
        $this->document->addScript('view/javascript/rewardpoints/lib/backbone.js');
        $this->document->addScript('view/javascript/rewardpoints/head.main.js');
        $this->document->addScript('view/javascript/rewardpoints/view.js');

        $this->language->load('module/reward_points_pro');
        $this->document->setTitle($this->language->get('heading_title_catalog_rule'));

        $this->data['heading_title'] = $this->language->get('heading_title_catalog_rule');

        if($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateForm())
        {
            $this->catalogRuleUpdate();
            $result_json['success'] = '1';
            $result_json['success_msg'] =  $this->language->get('text_success');
            $this->response->setOutput(json_encode($result_json));
            return;
        }

        $this->getFormCatalogRule();
    }

    protected function tryEasy(){
        $this->db->query("UPDATE " . DB_PREFIX . "setting SET `value` = '1' WHERE `key` = 'rwp_enabled_module'");
        //$this->db->query("UPDATE " . DB_PREFIX ."catalog_rules SET status = 1");
        //$this->db->query("UPDATE " . DB_PREFIX ."shopping_cart_rules SET status = 1");
        //$this->db->query("UPDATE " . DB_PREFIX ."behavior_rules SET status = 1");
    }

    protected function tryHard(){
        $this->db->query("UPDATE " . DB_PREFIX . "setting SET `value` = '0' WHERE `key` = 'rwp_enabled_module'");
        //$this->db->query("UPDATE " . DB_PREFIX ."catalog_rules SET status = 0");
        //$this->db->query("UPDATE " . DB_PREFIX ."shopping_cart_rules SET status = 0");
        //$this->db->query("UPDATE " . DB_PREFIX ."behavior_rules SET status = 0");
    }

	protected function getFormConfiguration()
	{
        $this->load->model('localisation/language');
        $this->load->model('setting/setting');
        $this->data['languages'] = $this->model_localisation_language->getLanguages();

        if (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else {
            $this->data['error_warning'] = '';
        }

		if (isset($this->session->data['warning'])) {
			$this->data['warning'] = $this->session->data['warning'];
		} else {
			$this->data['warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];
		} else {
			$this->data['success'] = '';
		}

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/marketplace', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		);

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title_configuration'),
			'href'      => $this->url->link('promotions/reward_points/configuration', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		);

		$this->data['token'] = $this->session->data['token'];

		$this->data['action'] = $this->url->link('promotions/reward_points/configuration', 'token=' . $this->session->data['token'], 'SSL');

		if (isset($this->request->post['rwp_enabled_module'])) {
			$this->data['rwp_enabled_module'] = $this->request->post['rwp_enabled_module'];
		} else {
			$this->data['rwp_enabled_module'] = $this->config->get('rwp_enabled_module');
        }
        //check if the option exists in DB
        //$is_allowed = $this->config->get('allow_no_product_points_spending');

        //if(!$is_allowed){
        //    $this->config->set('allow_no_product_points_spending');
        //}
                 
		if (isset($this->request->post['allow_no_product_points_spending'])) {
			$this->data['allow_no_product_points_spending'] = $this->request->post['allow_no_product_points_spending'];
		} else {
			$this->data['allow_no_product_points_spending'] = $this->config->get('allow_no_product_points_spending');
		}

		if (isset($this->request->post['currency_exchange_rate'])) {
			$this->data['currency_exchange_rate'] = $this->request->post['currency_exchange_rate'];
		} else {
			$this->data['currency_exchange_rate'] = $this->config->get('currency_exchange_rate');
		}
		if (isset($this->request->post['show_point_listing'])) {
			$this->data['show_point_listing'] = $this->request->post['show_point_listing'];
		} else {
			$this->data['show_point_listing'] = $this->config->get('show_point_listing');
		}
		if (isset($this->request->post['show_point_detail'])) {
			$this->data['show_point_detail'] = $this->request->post['show_point_detail'];
		} else {
			$this->data['show_point_detail'] = $this->config->get('show_point_detail');
		}

		if (isset($this->request->post['update_based_order_status'])) {
			$this->data['update_based_order_status'] = $this->request->post['update_based_order_status'];
		} else {

            $this->data['update_based_order_status'] = $this->model_setting_setting->getSetting('reward_points')['update_based_order_status'];

//            $this->data['update_based_order_status'] = $this->config->get('update_based_order_status');
		}
        
		if (isset($this->request->post['update_deduction_based_order_status'])) {
			$this->data['update_deduction_based_order_status'] = $this->request->post['update_deduction_based_order_status'];
		} else {
            $this->data['update_deduction_based_order_status'] = $this->model_setting_setting->getSetting('reward_points')['update_deduction_based_order_status'];
		}

		/** DISPATCH_EVENT:CONFIGURATION_AFTER_CHECK_FIELD_DATA */
        foreach($this->data['languages'] as $language){
            if (isset($this->request->post['text_points_'.$language['code']])) {
                $this->data['text_points_'.$language['code']] = $this->request->post['text_points_'.$language['code']];
            } else {
                $this->data['text_points_'.$language['code']] = $this->config->get('text_points_'.$language['code']);
            }
        }

		$this->data['earn_point_sort_order']   = $this->config->get('earn_point_sort_order');
		$this->data['redeem_point_sort_order'] = $this->config->get('redeem_point_sort_order');
		$this->data['earn_point_status']       = $this->config->get('earn_point_status');
		$this->data['redeem_point_status']     = $this->config->get('redeem_point_status');

		$this->load->model('localisation/order_status');
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		/** DISPATCH_EVENT:MODEL_AFTER_ADD_DATA_BEHAVIOR_RULE */

		$this->load->model('setting/extension');

		$extensions = $this->model_setting_extension->getInstalled('total');

		foreach ($extensions as $key => $value) {
			if (!file_exists(DIR_APPLICATION . 'controller/total/' . $value . '.php')) {
				$this->model_setting_extension->uninstall('total', $value);

				unset($extensions[$key]);
			}
		}

		$this->data['extensions'] = array();
		$this->data['extensions']['status'] = true;

		$files = glob(DIR_APPLICATION . 'controller/total/*.php');

		if ($files) {
			foreach ($files as $file) {
				$extension = basename($file, '.php');
				if($extension != 'earn_point' && $extension != 'redeem_point') continue;

				$this->language->load('total/' . $extension);

				$action = array();

				if(!$this->config->get($extension . '_status')) $this->data['extensions']['status'] = false;

				if (!in_array($extension, $extensions)) {
					$install = false;
					$this->data['extensions']['status'] = false;
				} else {
					$install = true;
				}

				$this->data['extensions'][$extension] = array(
					'name'       => $this->language->get('heading_title'),
					'status'     => ($install) ? $this->config->get($extension . '_status') : false,
					'sort_order' => (!$this->config->get($extension . '_sort_order')) ? ($install ? 0 : null) : ($install ? $this->config->get($extension . '_sort_order') : 'none'),
				);
			}
		}

		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$this->template = 'promotions/reward_points/configuration/form.expand';

		$this->children = array(
			'common/header',
			'common/footer'
		);
        //var_dump($this->data);
        //die();
		$this->response->setOutput($this->render());
	}

    protected function catalogRuleUpdate()
    {
        $this->load->model('promotions/reward_points');

        if (isset($this->request->post['rule_id']) && empty($this->request->post['rule_id']))
        {
            $this->model_promotions_reward_points->addCatalogRules($this->request->post);
        }
        else {
            $this->model_promotions_reward_points->updateCatalogRules($this->request->post);
        }
    }

	protected function shoppingCartRuleUpdate()
	{
		$this->load->model('promotions/reward_points');

		if (isset($this->request->post['rule_id']) && empty($this->request->post['rule_id']))
		{

			$this->model_promotions_reward_points->addShoppingCartRules($this->request->post);
		}
		else {
			$this->model_promotions_reward_points->updateShoppingCartRules($this->request->post);
		}
	}

    protected function spendingRuleUpdate()
    {
        $this->load->model('promotions/reward_points');

        if (isset($this->request->post['rule_id']) && empty($this->request->post['rule_id']))
        {

            $this->model_promotions_reward_points->addSpendingRules($this->request->post);
        }
        else {
            $this->model_promotions_reward_points->updateSpendingRules($this->request->post);
        }
    }

	protected function behaviorRuleUpdate()
	{
		$this->load->model('promotions/reward_points');

		if (isset($this->request->post['rule_id']) && empty($this->request->post['rule_id']))
		{

			$this->model_promotions_reward_points->addBehaviorRules($this->request->post);
		}
		else {
			$this->model_promotions_reward_points->updateBehaviorRules($this->request->post);
		}
	}

	protected function getFormBehaviorRule()
	{
		$this->document->addStyle('view/stylesheet/rewardpoints/stylesheet.css');
		/** DISPATCH_EVENT:BEHAVIOR_AFTER_ADD_STYLE_CSS */
		$this->document->addScript('view/javascript/rewardpoints/lib/underscore.js');
		$this->document->addScript('view/javascript/rewardpoints/lib/backbone.js');
		$this->document->addScript('view/javascript/rewardpoints/head.main.js');
		/** DISPATCH_EVENT:BEHAVIOR_BEFORE_ADD_VIEW_JS */
		$this->document->addScript('view/javascript/rewardpoints/view.js');
		/** DISPATCH_EVENT:BEHAVIOR_AFTER_ADD_VIEW_JS */
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

		if (isset($this->error['customer_group'])) {
			$this->data['error_customer_group'] = $this->error['customer_group'];
		} else {
			$this->data['error_customer_group'] = array();
		}

		if (isset($this->error['reward_point'])) {
			$this->data['error_reward_point'] = $this->error['reward_point'];
		} else {
			$this->data['error_reward_point'] = array();
		}

		$this->load->model('promotions/reward_points');

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('marketplace/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		);

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title_behavior_rule'),
			'href'      => $this->url->link('promotions/reward_points/behaviorRuleList', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		);

		$this->data['token'] = $this->session->data['token'];
        if (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else {
            $this->data['error_warning'] = '';
        }

		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];
		} else {
			$this->data['success'] = '';
		}

		$this->load->model('sale/customer_group');
		$this->data['customer_groups'] = $this->model_sale_customer_group->getCustomerGroups();

		if (!isset($this->request->get['rule_id'])) {
			$this->data['action'] = $this->url->link('promotions/reward_points/behaviorRuleEdit', 'token=' . $this->session->data['token'], 'SSL');
		} else {
			$this->data['action'] = $this->url->link('promotions/reward_points/behaviorRuleEdit', 'rule_id='.$this->request->get['rule_id'].'&token=' . $this->session->data['token'], 'SSL');
		}

		$this->data['cancel'] = $this->url->link('promotions/reward_points/behaviorRuleList', 'token=' . $this->session->data['token'], 'SSL');

        if (isset($this->request->get['rule_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $this->data['rule_id']=$this->request->get['rule_id'];
        }
            /** DISPATCH_EVENT:BEHAVIOR_BEFORE_CHECK_FIELD_DATA */
		if (isset($this->request->get['rule_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$behavior_rule_info = $this->model_promotions_reward_points->getBehaviorRule($this->request->get['rule_id']);
		}

		if (isset($this->request->post['name'])) {
			$this->data['name'] = $this->request->post['name'];
		} elseif (!empty($behavior_rule_info)) {
			$this->data['name'] = $behavior_rule_info['name'];
		} else {
			$this->data['name'] = '';
		}

		if (isset($this->request->post['status'])) {
			$this->data['status'] = $this->request->post['status'];
		} elseif (!empty($behavior_rule_info)) {
			$this->data['status'] = $behavior_rule_info['status'];
		} else {
			$this->data['status'] = '';
		}

		if (isset($this->request->post['customer_group_ids'])) {
			$this->data['customer_group_ids'] = $this->request->post['customer_group_ids'];
		} elseif (!empty($behavior_rule_info)) {
			$this->data['customer_group_ids'] = unserialize($behavior_rule_info['customer_group_ids']);
		} else {
			$this->data['customer_group_ids'] = array();
		}

		if (isset($this->request->post['actions'])) {
			$this->data['actions'] = $this->request->post['actions'];
		} elseif (!empty($behavior_rule_info)) {
			$this->data['actions'] = $behavior_rule_info['actions'];
		} else {
			$this->data['actions'] = '';
		}

		if (isset($this->request->post['reward_point'])) {
			$this->data['reward_point'] = $this->request->post['reward_point'];
		} elseif (!empty($behavior_rule_info)) {
			$this->data['reward_point'] = $behavior_rule_info['reward_point'];
		} else {
			$this->data['reward_point'] = '';
		}
		/** DISPATCH_EVENT:BEHAVIOR_AFTER_CHECK_FIELD_DATA */
		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];
		} else {
			$this->data['success'] = '';
		}

		$this->load->model('localisation/language');

		$this->data['languages'] = $this->model_localisation_language->getLanguages();

		$this->load->model('design/layout');

		$this->data['layouts'] = $this->model_design_layout->getLayouts();

		$this->template = 'promotions/reward_points/behavior_rule/form.expand';

		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}

    protected function getFormCatalogRule()
    {
        $this->load->model('promotions/reward_points');

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

        if (isset($this->error['customer_group'])) {
            $this->data['error_customer_group'] = $this->error['customer_group'];
        } else {
            $this->data['error_customer_group'] = array();
        }

        if (isset($this->error['reward_point'])) {
            $this->data['error_reward_point'] = $this->error['reward_point'];
        } else {
            $this->data['error_reward_point'] = array();
        }

        $this->load->model('promotions/reward_points');

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('marketplace/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title_catalog_rule'),
            'href'      => $this->url->link('promotions/reward_points/catalogRuleList', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $this->data['token'] = $this->session->data['token'];
        if (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else {
            $this->data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];
        } else {
            $this->data['success'] = '';
        }

        $this->data['rule_counter']=1;
        $this->data['rule_sub_counter'] = 1;

        $this->data['rule_id']='';

        if (isset($this->request->get['rule_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $this->data['rule_id']=$this->request->get['rule_id'];
        }

        $product_attributes = array();

        $product_attributes[] = array(
            'text'      =>  $this->language->get('text_category'),
            'model'     =>  'catalog/category|category_id',
            'type'      =>  'select',
            'method'    =>  'getCategories',
            'id'        =>  null
        );

        $product_attributes[] = array(
            'text'      =>  $this->language->get('text_model'),
            'model'     =>  'catalog/product|model',
            'type'      =>  'text',
            'method'    =>  'getProduct',
            'id'        =>  null
        );

        $product_attributes[] = array(
            'text'      =>  $this->language->get('text_sku'),
            'model'     =>  'catalog/product|sku',
            'type'      =>  'text',
            'method'    =>  'getProduct',
            'id'        =>  null
        );

        $product_attributes[] = array(
            'text'      =>  $this->language->get('text_price'),
            'model'     =>  'catalog/product|price',
            'type'      =>  'text',
            'method'    =>  'getProduct',
            'id'        =>  null
        );

        $this->load->model('catalog/attribute_group');
        $results = $this->model_catalog_attribute_group->getAttributeGroups();

        foreach($results as $result){
            $product_attributes[] = array(
                'text'      =>  $result['name'],
                'model'     =>  'catalog/attribute',
                'id'        =>  $result['attribute_group_id']
            );
        }

        $product_attributes[] = array(
            'text'      =>  $this->language->get('text_manufacturer'),
            'model'     =>  'catalog/manufacturer|manufacturer',
            'type'      =>  'text',
            'method'    =>  'getManufacturers',
            'id'        =>  null
        );
	    $this->data['product_attributes'] = $product_attributes;

        $this->load->model('sale/customer_group');
        $this->data['customer_groups'] = $this->model_sale_customer_group->getCustomerGroups();

        if (!isset($this->request->get['rule_id'])) {
            $this->data['action'] = $this->url->link('promotions/reward_points/catalogRuleEdit', 'token=' . $this->session->data['token'], 'SSL');
        } else {
            $this->data['action'] = $this->url->link('promotions/reward_points/catalogRuleEdit', 'rule_id='.$this->request->get['rule_id'].'&token=' . $this->session->data['token'], 'SSL');
        }

        $this->data['cancel'] = $this->url->link('promotions/reward_points/catalogRuleList', 'token=' . $this->session->data['token'], 'SSL');

        if (isset($this->request->get['rule_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $catalog_rule_info = $this->model_promotions_reward_points->getCatalogRule($this->request->get['rule_id']);
        }

        if (isset($this->request->post['name'])) {
            $this->data['name'] = $this->request->post['name'];
        } elseif (!empty($catalog_rule_info)) {
            $this->data['name'] = $catalog_rule_info['name'];
        } else {
            $this->data['name'] = '';
        }

        if (isset($this->request->post['description'])) {
            $this->data['description'] = $this->request->post['description'];
        } elseif (!empty($catalog_rule_info)) {
            $this->data['description'] = $catalog_rule_info['description'];
        } else {
            $this->data['description'] = '';
        }

        if (isset($this->request->post['status'])) {
            $this->data['status'] = $this->request->post['status'];
        } elseif (!empty($catalog_rule_info)) {
            $this->data['status'] = $catalog_rule_info['status'];
        } else {
            $this->data['status'] = '';
        }

        if (isset($this->request->post['customer_group_ids'])) {
            $this->data['customer_group_ids'] = $this->request->post['customer_group_ids'];
        } elseif (!empty($catalog_rule_info)) {
            $this->data['customer_group_ids'] = unserialize($catalog_rule_info['customer_group_ids']);
        } else {
            $this->data['customer_group_ids'] = array();
        }

        if (isset($this->request->post['start_date'])) {
            $this->data['start_date'] = $this->request->post['start_date'];
        } elseif (!empty($catalog_rule_info)) {
            $this->data['start_date'] = $catalog_rule_info['start_date'];
        } else {
            $this->data['start_date'] = '';
        }

        if (isset($this->request->post['end_date'])) {
            $this->data['end_date'] = $this->request->post['end_date'];
        } elseif (!empty($catalog_rule_info)) {
            $this->data['end_date'] = $catalog_rule_info['end_date'];
        } else {
            $this->data['end_date'] = '';
        }

        if (!empty($catalog_rule_info['conditions_serialized'])) {
            $rules = unserialize(base64_decode($catalog_rule_info['conditions_serialized']));

            $counters = array();
            $conditions = array();

            foreach($rules['conditions'] as $counter => $rule)
            {
                if((int)strpos($counter, "-") == 0 )
                {
                    $counters[$counter] = $rule;
                }
                else
                {
                    $data_option = $this->getDataOption($rule['type'], false, $rule['value']);

                    $conditions[$counter] = $rule;
                    $conditions[$counter]['data'] = $data_option;
                }
            }

            $this->data['rule'] = $conditions;
            $this->data['conditions_combine'] = $counters;
        } else {
            $this->data['rule'] = array();
            $this->data['conditions_combine'] = array();

            $this->data['conditions_combine'][$this->data['rule_counter']] = array(
                'aggregator'  =>  'all',
                'value'  =>  '1',
                'new_child'  =>  '',
            );
        }
        if (isset($this->request->post['actions'])) {
            $this->data['actions'] = $this->request->post['actions'];
        } elseif (!empty($catalog_rule_info)) {
            $this->data['actions'] = $catalog_rule_info['actions'];
        } else {
            $this->data['actions'] = '';
        }

        if (isset($this->request->post['reward_point'])) {
            $this->data['reward_point'] = $this->request->post['reward_point'];
        } elseif (!empty($catalog_rule_info)) {
            $this->data['reward_point'] = $catalog_rule_info['reward_point'];
        } else {
            $this->data['reward_point'] = '';
        }

        if (isset($this->request->post['reward_per_spent'])) {
            $this->data['reward_per_spent'] = $this->request->post['reward_per_spent'];
        } elseif (!empty($catalog_rule_info)) {
            $this->data['reward_per_spent'] = $catalog_rule_info['reward_per_spent'];
        } else {
            $this->data['reward_per_spent'] = '';
        }

        $this->load->model('localisation/language');

        $this->data['languages'] = $this->model_localisation_language->getLanguages();

        $this->load->model('design/layout');

        $this->data['layouts'] = $this->model_design_layout->getLayouts();

        $this->template = 'promotions/reward_points/catalog_rule/form.expand';

        $this->children = array(
            'common/header',
            'common/footer'
        );
        //var_dump($this->data);
        //die();

        $this->response->setOutput($this->render());
    }

	protected function getFormShoppingCartRule()
	{
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

		if (isset($this->error['customer_group'])) {
			$this->data['error_customer_group'] = $this->error['customer_group'];
		} else {
			$this->data['error_customer_group'] = array();
		}

		if (isset($this->error['reward_point'])) {
			$this->data['error_reward_point'] = $this->error['reward_point'];
		} else {
			$this->data['error_reward_point'] = array();
		}

		$this->load->model('promotions/reward_points');

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('marketplace/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		);

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title_shopping_cart_rule'),
			'href'      => $this->url->link('promotions/reward_points/shoppingCartRuleList', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		);

		$this->data['token'] = $this->session->data['token'];
        if (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else {
            $this->data['error_warning'] = '';
        }

		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];
		} else {
			$this->data['success'] = '';
		}

        $this->data['rule_counter']=1;
        $this->data['rule_sub_counter'] = 1;

        $this->data['rule_id']='';

        if (isset($this->request->get['rule_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $this->data['rule_id']=$this->request->get['rule_id'];
        }

        $cart_attributes = array();

		$cart_attributes[] = array(
			'text'      =>  $this->language->get('text_subtotal'),
			'model'     =>  'sale/reward_points/rule|subtotal',
			'type'      =>  'text',
			'id'        =>  null
		);

		$cart_attributes[] = array(
			'text'      =>  $this->language->get('text_total_items_quantity'),
			'model'     =>  'sale/reward_points/rule|quantity',
			'type'      =>  'text',
			'id'        =>  null
		);

		$cart_attributes[] = array(
			'text'      =>  $this->language->get('text_total_weight'),
			'model'     =>  'sale/reward_points/rule|weight',
			'type'      =>  'text',
			'id'        =>  null
		);

		$cart_attributes[] = array(
			'text'      =>  $this->language->get('text_payment_method'),
			'model'     =>  'sale/reward_points/rule|payment_method',
			'type'      =>  'select',
			'id'        =>  null
		);

		$cart_attributes[] = array(
			'text'      =>  $this->language->get('text_shipping_method'),
			'model'     =>  'sale/reward_points/rule|shipping_method',
			'type'      =>  'select',
			'id'        =>  null
		);

		$cart_attributes[] = array(
			'text'      =>  $this->language->get('text_shipping_postcode'),
			'model'     =>  'sale/reward_points/rule|shipping_postcode',
			'type'      =>  'select',
			'id'        =>  null
		);

		$cart_attributes[] = array(
			'text'      =>  $this->language->get('text_shipping_region'),
			'model'     =>  'sale/reward_points/rule|shipping_region',
			'type'      =>  'select',
			'id'        =>  null
		);

		$cart_attributes[] = array(
			'text'      =>  $this->language->get('text_shipping_state_province'),
			'model'     =>  'sale/reward_points/rule|shipping_state_province',
			'type'      =>  'select',
			'id'        =>  null
		);

		$cart_attributes[] = array(
			'text'      =>  $this->language->get('text_shipping_country'),
			'model'     =>  'sale/reward_points/rule|shipping_country',
			'type'      =>  'select',
			'id'        =>  null
		);

        $product_attributes = array();

        $product_attributes[] = array(
            'text'      =>  $this->language->get('text_category'),
            'model'     =>  'catalog/category|category_id',
            'type'      =>  'select',
            'method'    =>  'getCategories',
            'id'        =>  null
        );

        $product_attributes[] = array(
            'text'      =>  $this->language->get('text_model'),
            'model'     =>  'catalog/product|model',
            'type'      =>  'text',
            'method'    =>  'getProduct',
            'id'        =>  null
        );

        $product_attributes[] = array(
            'text'      =>  $this->language->get('text_sku'),
            'model'     =>  'catalog/product|sku',
            'type'      =>  'text',
            'method'    =>  'getProduct',
            'id'        =>  null
        );
        /*$this->load->model('catalog/attribute_group');
        $results = $this->model_catalog_attribute_group->getAttributeGroups();

        foreach($results as $result){
            $product_attributes[] = array(
                'text'      =>  $result['name'],
                'model'     =>  'catalog/attribute',
                'id'        =>  $result['attribute_group_id']
            );
        }*/

        $product_attributes[] = array(
            'text'      =>  $this->language->get('text_manufacturer'),
            'model'     =>  'catalog/manufacturer|manufacturer',
            'type'      =>  'text',
            'method'    =>  'getManufacturers',
            'id'        =>  null
        );
        $this->data['product_attributes'] = $product_attributes;

		$this->load->model('sale/customer_group');
		$this->data['customer_groups'] = $this->model_sale_customer_group->getCustomerGroups();

		$this->data['cart_attributes'] = $cart_attributes;
		if (!isset($this->request->get['rule_id'])) {
			$this->data['action'] = $this->url->link('promotions/reward_points/shoppingCartRuleEdit', 'token=' . $this->session->data['token'], 'SSL');
		} else {
			$this->data['action'] = $this->url->link('promotions/reward_points/shoppingCartRuleEdit', 'rule_id='.$this->request->get['rule_id'].'&token=' . $this->session->data['token'], 'SSL');
		}

		$this->data['cancel'] = $this->url->link('promotions/reward_points/shoppingCartRuleList', 'token=' . $this->session->data['token'], 'SSL');

		if (isset($this->request->get['rule_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$shopping_cart_rule_info = $this->model_promotions_reward_points->getShoppingCartRule($this->request->get['rule_id']);
		}

		if (isset($this->request->post['name'])) {
			$this->data['name'] = $this->request->post['name'];
		} elseif (!empty($shopping_cart_rule_info)) {
			$this->data['name'] = $shopping_cart_rule_info['name'];
		} else {
			$this->data['name'] = '';
		}

		if (isset($this->request->post['description'])) {
			$this->data['description'] = $this->request->post['description'];
		} elseif (!empty($shopping_cart_rule_info)) {
			$this->data['description'] = $shopping_cart_rule_info['description'];
		} else {
			$this->data['description'] = '';
		}

		if (isset($this->request->post['status'])) {
			$this->data['status'] = $this->request->post['status'];
		} elseif (!empty($shopping_cart_rule_info)) {
			$this->data['status'] = $shopping_cart_rule_info['status'];
		} else {
			$this->data['status'] = '';
		}

		if (isset($this->request->post['customer_group_ids'])) {
			$this->data['customer_group_ids'] = $this->request->post['customer_group_ids'];
		} elseif (!empty($shopping_cart_rule_info)) {
			$this->data['customer_group_ids'] = unserialize($shopping_cart_rule_info['customer_group_ids']);
		} else {
			$this->data['customer_group_ids'] = array();
		}

		if (isset($this->request->post['start_date'])) {
			$this->data['start_date'] = $this->request->post['start_date'];
		} elseif (!empty($shopping_cart_rule_info)) {
			$this->data['start_date'] = $shopping_cart_rule_info['start_date'];
		} else {
			$this->data['start_date'] = '';
		}

		if (isset($this->request->post['end_date'])) {
			$this->data['end_date'] = $this->request->post['end_date'];
		} elseif (!empty($shopping_cart_rule_info)) {
			$this->data['end_date'] = $shopping_cart_rule_info['end_date'];
		} else {
			$this->data['end_date'] = '';
		}

		if (!empty($shopping_cart_rule_info['conditions_serialized']) || isset($this->request->post['rule'])) {
			if(isset($this->request->post['rule']))
			{
				$rules = $this->request->post['rule'];
			}
			else
			{
				$rules = unserialize(base64_decode($shopping_cart_rule_info['conditions_serialized']));
			}

			$counters = array();
			$conditions = array();

			foreach($rules['conditions'] as $counter => $rule)
			{
				if((int)strpos($counter, "-") == 0 )
				{
					$counters[$counter] = $rule;
				}
				else
				{
					$data_option = $this->getDataOption($rule['type'], false, $rule['value']);

					$conditions[$counter] = $rule;
					$conditions[$counter]['data'] = $data_option;
				}
			}

			$this->data['rule'] = $conditions;
			$this->data['conditions_combine'] = $counters;
		} else {
			$this->data['rule'] = array();
			$this->data['conditions_combine'] = array();

            $this->data['conditions_combine'][$this->data['rule_counter']] = array(
                'aggregator'  =>  'all',
                'value'  =>  '1',
                'new_child'  =>  '',
            );
		}

		if (isset($this->request->post['reward_point'])) {
			$this->data['reward_point'] = $this->request->post['reward_point'];
		} elseif (!empty($shopping_cart_rule_info)) {
			$this->data['reward_point'] = $shopping_cart_rule_info['reward_point'];
		} else {
			$this->data['reward_point'] = '';
		}

        if (isset($this->request->post['stop_rules_processing'])) {
            $this->data['stop_rules_processing'] = $this->request->post['stop_rules_processing'];
        } elseif (!empty($shopping_cart_rule_info)) {
            $this->data['stop_rules_processing'] = $shopping_cart_rule_info['stop_rules_processing'];
        } else {
            $this->data['stop_rules_processing'] = 1;
        }

        if (isset($this->request->post['rule_position'])) {
            $this->data['rule_position'] = $this->request->post['rule_position'];
        } elseif (!empty($shopping_cart_rule_info)) {
            $this->data['rule_position'] = $shopping_cart_rule_info['rule_position'];
        } else {
            $this->data['rule_position'] = 0;
        }

		if (isset($this->request->post['actions'])) {
			$this->data['actions'] = $this->request->post['actions'];
		} elseif (!empty($shopping_cart_rule_info)) {
			$this->data['actions'] = $shopping_cart_rule_info['actions'];
		} else {
			$this->data['actions'] = '';
		}

		if (isset($this->request->post['reward_per_spent'])) {
			$this->data['reward_per_spent'] = $this->request->post['reward_per_spent'];
		} elseif (!empty($shopping_cart_rule_info)) {
			$this->data['reward_per_spent'] = $shopping_cart_rule_info['reward_per_spent'];
		} else {
			$this->data['reward_per_spent'] = '';
		}

		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];
		} else {
			$this->data['success'] = '';
		}
		$this->load->model('localisation/language');

		$this->data['languages'] = $this->model_localisation_language->getLanguages();

		$this->load->model('design/layout');

		$this->data['layouts'] = $this->model_design_layout->getLayouts();

		$this->template = 'promotions/reward_points/shopping_cart_rule/form.expand';

		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}

    protected function getFormSpendingRule()
    {
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

        if (isset($this->error['customer_group'])) {
            $this->data['error_customer_group'] = $this->error['customer_group'];
        } else {
            $this->data['error_customer_group'] = array();
        }

        if (isset($this->error['reward_point'])) {
            $this->data['error_reward_point'] = $this->error['reward_point'];
        } else {
            $this->data['error_reward_point'] = array();
        }

        $this->load->model('promotions/reward_points');

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('marketplace/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title_spending_rule'),
            'href'      => $this->url->link('promotions/reward_points/spendingRuleList', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $this->data['token'] = $this->session->data['token'];
        if (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else {
            $this->data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];
        } else {
            $this->data['success'] = '';
        }

        $this->data['rule_counter']=1;
        $this->data['rule_sub_counter'] = 1;

        $this->data['rule_id']='';

        if (isset($this->request->get['rule_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $this->data['rule_id']=$this->request->get['rule_id'];
        }

        $cart_attributes = array();

        $cart_attributes[] = array(
            'text'      =>  $this->language->get('text_subtotal'),
            'model'     =>  'sale/reward_points/rule|subtotal',
            'type'      =>  'text',
            'id'        =>  null
        );

        $cart_attributes[] = array(
            'text'      =>  $this->language->get('text_total_items_quantity'),
            'model'     =>  'sale/reward_points/rule|quantity',
            'type'      =>  'text',
            'id'        =>  null
        );

        $cart_attributes[] = array(
            'text'      =>  $this->language->get('text_total_weight'),
            'model'     =>  'sale/reward_points/rule|weight',
            'type'      =>  'text',
            'id'        =>  null
        );

        $cart_attributes[] = array(
            'text'      =>  $this->language->get('text_payment_method'),
            'model'     =>  'sale/reward_points/rule|payment_method',
            'type'      =>  'select',
            'id'        =>  null
        );

        $cart_attributes[] = array(
            'text'      =>  $this->language->get('text_shipping_method'),
            'model'     =>  'sale/reward_points/rule|shipping_method',
            'type'      =>  'select',
            'id'        =>  null
        );

        $cart_attributes[] = array(
            'text'      =>  $this->language->get('text_shipping_postcode'),
            'model'     =>  'sale/reward_points/rule|shipping_postcode',
            'type'      =>  'select',
            'id'        =>  null
        );

        $cart_attributes[] = array(
            'text'      =>  $this->language->get('text_shipping_region'),
            'model'     =>  'sale/reward_points/rule|shipping_region',
            'type'      =>  'select',
            'id'        =>  null
        );

        $cart_attributes[] = array(
            'text'      =>  $this->language->get('text_shipping_state_province'),
            'model'     =>  'sale/reward_points/rule|shipping_state_province',
            'type'      =>  'select',
            'id'        =>  null
        );

        $cart_attributes[] = array(
            'text'      =>  $this->language->get('text_shipping_country'),
            'model'     =>  'sale/reward_points/rule|shipping_country',
            'type'      =>  'select',
            'id'        =>  null
        );

	    $product_attributes = array();
	    $product_attributes[] = array(
		    'text'      =>  $this->language->get('text_category'),
		    'model'     =>  'catalog/category|category_id',
		    'type'      =>  'select',
		    'method'    =>  'getCategories',
		    'id'        =>  null
	    );

	    $product_attributes[] = array(
		    'text'      =>  $this->language->get('text_model'),
		    'model'     =>  'catalog/product|model',
		    'type'      =>  'text',
		    'method'    =>  'getProduct',
		    'id'        =>  null
	    );

	    $product_attributes[] = array(
		    'text'      =>  $this->language->get('text_sku'),
		    'model'     =>  'catalog/product|sku',
		    'type'      =>  'text',
		    'method'    =>  'getProduct',
		    'id'        =>  null
	    );
	    /*$this->load->model('catalog/attribute_group');
	    $results = $this->model_catalog_attribute_group->getAttributeGroups();

	    foreach($results as $result){
		    $product_attributes[] = array(
			    'text'      =>  $result['name'],
			    'model'     =>  'catalog/attribute',
			    'id'        =>  $result['attribute_group_id']
		    );
	    }*/

	    $product_attributes[] = array(
		    'text'      =>  $this->language->get('text_manufacturer'),
		    'model'     =>  'catalog/manufacturer|manufacturer',
		    'type'      =>  'text',
		    'method'    =>  'getManufacturers',
		    'id'        =>  null
	    );
	    $this->data['product_attributes'] = $product_attributes;

        $this->load->model('sale/customer_group');
        $this->data['customer_groups'] = $this->model_sale_customer_group->getCustomerGroups();

        $this->data['cart_attributes'] = $cart_attributes;
        if (!isset($this->request->get['rule_id'])) {
            $this->data['action'] = $this->url->link('promotions/reward_points/spendingRuleEdit', 'token=' . $this->session->data['token'], 'SSL');
        } else {
            $this->data['action'] = $this->url->link('promotions/reward_points/spendingRuleEdit', 'rule_id='.$this->request->get['rule_id'].'&token=' . $this->session->data['token'], 'SSL');
        }

        $this->data['cancel'] = $this->url->link('promotions/reward_points/spendingRuleList', 'token=' . $this->session->data['token'], 'SSL');

        if (isset($this->request->get['rule_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $spending_rule_info = $this->model_promotions_reward_points->getSpendingRule($this->request->get['rule_id']);
        }

        if (isset($this->request->post['name'])) {
            $this->data['name'] = $this->request->post['name'];
        } elseif (!empty($spending_rule_info)) {
            $this->data['name'] = $spending_rule_info['name'];
        } else {
            $this->data['name'] = '';
        }

        if (isset($this->request->post['description'])) {
            $this->data['description'] = $this->request->post['description'];
        } elseif (!empty($spending_rule_info)) {
            $this->data['description'] = $spending_rule_info['description'];
        } else {
            $this->data['description'] = '';
        }

        if (isset($this->request->post['status'])) {
            $this->data['status'] = $this->request->post['status'];
        } elseif (!empty($spending_rule_info)) {
            $this->data['status'] = $spending_rule_info['status'];
        } else {
            $this->data['status'] = '';
        }

        if (isset($this->request->post['customer_group_ids'])) {
            $this->data['customer_group_ids'] = $this->request->post['customer_group_ids'];
        } elseif (!empty($spending_rule_info)) {
            $this->data['customer_group_ids'] = unserialize($spending_rule_info['customer_group_ids']);
        } else {
            $this->data['customer_group_ids'] = array();
        }

        if (isset($this->request->post['start_date'])) {
            $this->data['start_date'] = $this->request->post['start_date'];
        } elseif (!empty($spending_rule_info)) {
            $this->data['start_date'] = $spending_rule_info['start_date'];
        } else {
            $this->data['start_date'] = '';
        }

        if (isset($this->request->post['end_date'])) {
            $this->data['end_date'] = $this->request->post['end_date'];
        } elseif (!empty($spending_rule_info)) {
            $this->data['end_date'] = $spending_rule_info['end_date'];
        } else {
            $this->data['end_date'] = '';
        }

        if (!empty($spending_rule_info['conditions_serialized']) || isset($this->request->post['rule'])) {
            if(isset($this->request->post['rule']))
            {
                $rules = $this->request->post['rule'];
            }
            else
            {
                $rules = unserialize(base64_decode($spending_rule_info['conditions_serialized']));
            }

            $counters = array();
            $conditions = array();

            foreach($rules['conditions'] as $counter => $rule)
            {
                if((int)strpos($counter, "-") == 0 )
                {
                    $counters[$counter] = $rule;
                }
                else
                {
                    $data_option = $this->getDataOption($rule['type'], false, $rule['value']);

                    $conditions[$counter] = $rule;
                    $conditions[$counter]['data'] = $data_option;
                }
            }

            $this->data['rule'] = $conditions;
            $this->data['conditions_combine'] = $counters;
        } else {
            $this->data['rule'] = array();
            $this->data['conditions_combine'] = array();

            $this->data['conditions_combine'][$this->data['rule_counter']] = array(
                'aggregator'  =>  'all',
                'value'  =>  '1',
                'new_child'  =>  '',
            );
        }

        if (isset($this->request->post['reward_point'])) {
            $this->data['reward_point'] = $this->request->post['reward_point'];
        } elseif (!empty($spending_rule_info)) {
            $this->data['reward_point'] = $spending_rule_info['reward_point'];
        } else {
            $this->data['reward_point'] = '';
        }

        if (isset($this->request->post['actions'])) {
            $this->data['actions'] = $this->request->post['actions'];
        } elseif (!empty($spending_rule_info)) {
            $this->data['actions'] = $spending_rule_info['actions'];
        } else {
            $this->data['actions'] = '';
        }

        if (isset($this->request->post['reward_per_spent'])) {
            $this->data['reward_per_spent'] = $this->request->post['reward_per_spent'];
        } elseif (!empty($spending_rule_info)) {
            $this->data['reward_per_spent'] = $spending_rule_info['reward_per_spent'];
        } else {
            $this->data['reward_per_spent'] = '';
        }

		if (isset($this->request->post['stop_rules_processing'])) {
			$this->data['stop_rules_processing'] = $this->request->post['stop_rules_processing'];
		} elseif (!empty($spending_rule_info)) {
			$this->data['stop_rules_processing'] = $spending_rule_info['stop_rules_processing'];
		} else {
			$this->data['stop_rules_processing'] = 1;
		}

		if (isset($this->request->post['rule_position'])) {
			$this->data['rule_position'] = $this->request->post['rule_position'];
		} elseif (!empty($spending_rule_info)) {
			$this->data['rule_position'] = $spending_rule_info['rule_position'];
		} else {
			$this->data['rule_position'] = 0;
		}

        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];
        } else {
            $this->data['success'] = '';
        }
        $this->load->model('localisation/language');

        $this->data['languages'] = $this->model_localisation_language->getLanguages();

        $this->load->model('design/layout');

        $this->data['layouts'] = $this->model_design_layout->getLayouts();

        $this->template = 'promotions/reward_points/spending_rule/form.expand';

        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }

	public function getDataOption($model_value = "", $json_output = true, $value_selected = '')
    {
        $model_value = (empty($model_value)) ? $this->request->post['value'] : $model_value;

        if($model_value != '')
        {
            if(strpos($model_value, "|") > -1 && strpos($model_value, "-") > -1)
            {
                $data   = explode("|", $model_value);
                $model  = $data[0];
                $data   = explode("-", $data[1]);
                $field  = $data[0];
                $type   = $data[1];
                $method = isset($data[2]) ? $data[2] : null;
            }else{
                $data = explode("|", $model_value);
                $model = $data[0];
                $id = $data[1];
            }

            $this->language->load('module/reward_points_pro');

            $this->load->model($model);
            $model_alias = str_replace("/", "_", $model);
            $selected = "";
            $selected_ID = "";

            switch($model_alias)
            {
                case 'catalog_option':
                    $option  = $this->{"model_".$model_alias}->getOption($id);
                    $_data = $this->{"model_".$model_alias}->getOptionValues($id);

                    $option_values = array();

                    foreach($_data as $_option)
                    {
                        if($_option['option_value_id'] == $value_selected)
                        {
                            $selected = $_option['name'];
                            $selected_ID = $value_selected;
                        }
                        $option_values[] = array(
                            'name'      =>  $_option['name'],
                            'value_id'  =>  $_option['option_value_id'],
                            'selected'  =>  ($_option['option_value_id'] == $value_selected) ? true : false
                        );
                    }
                    $data = array(
                        'label' =>  $option['name'],
                        'type'  =>  $option['type'],
                        'model' =>  $model_value,
                        'values'        =>  $option_values,
                        'operator'      =>  $this->renderOperator($option['type']),
                        'selected'      => $selected,
                        'selected_ID'   => $selected_ID,
                    );

                    break;
                case 'catalog_manufacturer':
                    $manufacturers = $this->{"model_".$model_alias}->{$method}(array());

                    $option_values = array();
                    foreach($manufacturers as $manufacturer)
                    {
                        if($manufacturer['manufacturer_id'] == $value_selected)
                        {
                            $selected = $manufacturer['name'];
                            $selected_ID = $value_selected;
                        }
                        $option_values[] = array(
                            'name'      =>  $manufacturer['name'],
                            'value_id'  =>  $manufacturer['manufacturer_id'],
                            'selected'  =>  ($manufacturer['manufacturer_id'] == $value_selected) ? true : false
                        );
                    }

                    $data = array(
                        'label' => $this->language->get('text_manufacturer'),
                        'type'  =>  'select',
                        'model' =>  $model_value,
                        'values'        =>  $option_values,
                        'operator'      =>  $this->renderOperator('select'),
                        'selected'      => $selected,
                        'selected_ID'   => $selected_ID,
                    );

                    break;
                case 'catalog_attribute':
                    $this->load->model('catalog/attribute_group');
                    $data = array(
                        'filter_attribute_group_id' =>  $id
                    );
                    $_attribute = $this->{"model_".$model_alias."_group"}->getAttributeGroupDescriptions($id);

                    $_attributes = $this->{"model_".$model_alias}->getAttributesByAttributeGroupId($data);

                    $option_values = array();
                    foreach($_attributes as $attribute)
                    {
                        if($attribute['attribute_id'] == $value_selected)
                        {
                            $selected = $attribute['name'];
                            $selected_ID = $value_selected;
                        }
                        $option_values[] = array(
                            'name'      =>  $attribute['name'],
                            'value_id'  =>  $attribute['attribute_id'],
                            'selected'  =>  ($attribute['attribute_id'] == $value_selected) ? true : false
                        );
                    }

                    $data = array(
                        'label' => $_attribute[(int)$this->config->get('config_language_id')]['name'],
                        'type'  =>  'select',
                        'model' =>  $model_value,
                        'values'        =>  $option_values,
                        'operator'      =>  $this->renderOperator('select'),
                        'selected'      => $selected,
                        'selected_ID'   => $selected_ID,
                    );

                    break;
                case 'catalog_category':
                    $categories = $this->{"model_".$model_alias}->{$method}(array());

                    $option_values = array();
                    foreach($categories as $category)
                    {
                        if($category['category_id'] == $value_selected)
                        {
                            $selected = $category['name'];
                            $selected_ID = $value_selected;
                        }
                        $option_values[] = array(
                            'name'      =>  $category['name'],
                            'value_id'  =>  $category['category_id'],
                            'selected'  =>  ($category['category_id'] == $value_selected) ? 1 : -1
                        );
                    }

                    $data = array(
                        'label' => $this->language->get('text_category'),
                        'type'  =>  'select',
                        'model' =>  $model_value,
                        'values'        =>  $option_values,
                        'operator'      =>  $this->renderOperator('select'),
                        'selected'      => $selected,
                        'selected_ID'   => $selected_ID,
                    );

                    break;
                case 'catalog_product':

                    $data = array(
                        'label' => $this->language->get('text_'.$field),
                        'type'  =>  'text',
                        'model' =>  $model_value,
                        'values'    =>  '',
                        'operator'  =>  $this->renderOperator('text'),
                        'selected'  =>  $value_selected
                    );

                    break;
                case 'sale_reward_points_rule':
                    switch($field)
                    {
                        case 'payment_method':
                            $payment_methods = $this->{"model_".$model_alias}->getExtension('payment');

                            $option_values = array();
                            foreach($payment_methods as $method)
                            {
                                if($method['code'] == $value_selected)
                                {
                                    $selected = $method['name'];
                                    $selected_ID = $value_selected;
                                }
                                $option_values[] = array(
                                    'name'      =>  $method['name'],
                                    'value_id'  =>  $method['code'],
                                    'selected'  =>  ($method['code'] == $value_selected) ? 1 : -1
                                );
                            }

                            $data = array(
                                'label' => $this->language->get('text_payment_method'),
                                'type'  =>  'select',
                                'model' =>  $model_value,
                                'values'        =>  $option_values,
                                'operator'      =>  $this->renderOperator('select'),
                                'selected'      => $selected,
                                'selected_ID'   => $selected_ID,
                            );
                            break;
                        case 'shipping_method':
                            $shipping_methods = $this->{"model_".$model_alias}->getExtension('shipping');

                            $option_values = array();
                            foreach($shipping_methods as $method)
                            {
                                if($method['code'] == $value_selected)
                                {
                                    $selected = $method['name'];
                                    $selected_ID = $value_selected;
                                }
                                $option_values[] = array(
                                    'name'      =>  $method['name'],
                                    'value_id'  =>  $method['code'],
                                    'selected'  =>  ($method['code'] == $value_selected) ? 1 : -1
                                );
                            }

                            $data = array(
                                'label' => $this->language->get('text_shipping_method'),
                                'type'  =>  'select',
                                'model' =>  $model_value,
                                'values'        =>  $option_values,
                                'operator'      =>  $this->renderOperator('select'),
                                'selected'      => $selected,
                                'selected_ID'   => $selected_ID,
                            );
                            break;
                        case 'shipping_country':
                            $countries = $this->{"model_".$model_alias}->getCountries();

                            $option_values = array();
                            foreach($countries as $country)
                            {
                                if($country['country_id'] == $value_selected)
                                {
                                    $selected = $country['name'];
                                    $selected_ID = $value_selected;
                                }
                                $option_values[] = array(
                                    'name'      =>  $country['name'],
                                    'value_id'  =>  $country['country_id'],
                                    'selected'  =>  ($country['country_id'] == $value_selected) ? 1 : -1
                                );
                            }

                            $data = array(
                                'label' => $this->language->get('text_shipping_country'),
                                'type'  =>  'select',
                                'model' =>  $model_value,
                                'values'        =>  $option_values,
                                'operator'      =>  $this->renderOperator('select'),
                                'selected'      => $selected,
                                'selected_ID'   => $selected_ID,
                            );
                            break;
                        default:
                            $data = array(
                                'label' => $this->language->get('text_'.$field),
                                'type'  =>  'text',
                                'model' =>  $model_value,
                                'values'    =>  '',
                                'operator'  =>  $this->renderOperator('text'),
                                'selected'  =>  $value_selected
                            );
                            break;
                    }
                    break;
            }

            if($json_output)
            {
                return $this->response->setOutput(json_encode($data));
            }
            else
            {
                return $data;
            }
        }
    }

    protected function validateForm() {
        if (!$this->user->hasPermission('modify', 'promotions/reward_points')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if ((utf8_strlen($this->request->post['name']) < 1)) {
            $this->error['name'] = $this->language->get('error_name');
        }

        if (!isset($this->request->post['customer_group_ids']) || count($this->request->post['customer_group_ids']) == 0) {
            $this->error['customer_group'] = $this->language->get('error_customer_group');
        }

	    if($this->request->post['actions'] != ControllerPromotionsRewardPoints::DO_NOT_ALLOW_USE_REWARD){
		    if (!isset($this->request->post['reward_point']) || is_numeric($this->request->post['reward_point']) == 0) {
			    $this->error['reward_point'] = $this->language->get('error_reward_point');
		    }
	    }

        if ($this->error && !isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    protected function renderOperator($type){
        $list_op = array(
            '=='  => $this->language->get('is'),
            '=!'  => $this->language->get('is not'),
            '>='  => $this->language->get('equals or greater than'),
            '<='  => $this->language->get('equals or less than'),
            '>'   => $this->language->get('greater than'),
            '<'   => $this->language->get('less than'),
            '{}'  => $this->language->get('contains'),
            '!{}' => $this->language->get('does not contain'),
            '()'  => $this->language->get('is one of'),
            '!()' => $this->language->get('is not one of')
        );

        switch($type)
        {
            case 'radio':
            case 'checkbox':
            case 'select':
                $in_op  =   array('==', '=!');
                $data_op = array();
                foreach($list_op as $op => $text_op)
                {
                    if(in_array($op, $in_op))
                    {
                        $data_op[$op] = $text_op;
                    }
                }
                break;
            default:
                $data_op = $list_op;
                break;
        }

        return $data_op;
    }

    public function getOperatorToText($op){
        $list_op = array(
            '=='  => $this->language->get('is'),
            '=!'  => $this->language->get('is not'),
            '>='  => $this->language->get('equals or greater than'),
            '<='  => $this->language->get('equals or less than'),
            '>'   => $this->language->get('greater than'),
            '<'   => $this->language->get('less than'),
            '{}'  => $this->language->get('contains'),
            '!{}' => $this->language->get('does not contain'),
            '()'  => $this->language->get('is one of'),
            '!()' => $this->language->get('is not one of')
        );
        return $list_op[$op];
    }

	protected function active(){
		$this->db->query("DELETE FROM ".DB_PREFIX."setting WHERE `key` = 'rwp_enabled_module'");
		$this->db->query("INSERT INTO ".DB_PREFIX."setting SET `key` = 'rwp_enabled_module', `value` = '1'");
	}

	protected function validateConfiguration()
	{
		if (!$this->user->hasPermission('modify', 'setting/setting')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->error) {
			return true;
		}
		else {
			return false;
		}
	}

	protected function writeLog($content = ""){
		if(!file_exists(DIR_SYSTEM."logs/error.log")){
			file_put_contents(DIR_SYSTEM."logs/error.log", $content);
			chmod(DIR_SYSTEM."logs/error.log", 0755);
		}else{
			file_put_contents(DIR_SYSTEM."logs/error.log", $content);
		}
	}
}
