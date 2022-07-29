<?php
class ControllerReportCustomerReward extends Controller {

    private $plan_id = PRODUCTID;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('plan/trial');
        $trial = $this->model_plan_trial->getLastTrial();
        if ($trial){
            $this->plan_id = $trial['plan_id'];
        }

        if($this->plan_id == 3){
            $this->redirect(
                $this->url->link('error/not_found', '', 'SSL')
            );
            exit();
        }
    }


	public function index() {     
		$this->language->load('report/customer_reward');

		$this->document->setTitle($this->language->get('heading_title'));

        // define default values
        $this->data['filter_date_start'] = date('y/m/d', strtotime(date('Y') . '-' . date('m') . '-01'));
        $this->data['filter_date_end'] = date('y/m/d');


						
		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', '', 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('report/customer_reward', '', 'SSL'),
      		'separator' => ' :: '
   		);

        $comming_from = '';
        // check multi store manager app
        if(isset($this->request->get['comming_from']) && $this->request->get['comming_from'] == 'multi_store_manager'){
            $comming_from = 'comming_from=multi_store_manager';
            // load app model
            $this->load->model('module/multi_store_manager/settings');
            // check if app active
            if( $this->model_module_multi_store_manager_settings->isActive()){
                $this->data['multi_store_manager'] = TRUE;
                $this->data['stores_codes'] = $this->model_module_multi_store_manager_settings->getStoreCodes();
            }
        }

        // action and localized values
        $this->data['action'] = $this->url->link('report/customer_reward/ajaxResponse', $comming_from, 'SSL');
        $this->data['heading_title'] = $this->language->get('heading_title');
        $this->data['edit_button_text'] = $this->language->get('text_edit') ;
		 
		$this->data['text_no_results'] = $this->language->get('text_no_results');
		
		$this->data['column_customer'] = $this->language->get('column_customer');
		$this->data['column_email'] = $this->language->get('column_email');
		$this->data['column_customer_group'] = $this->language->get('column_customer_group');
		$this->data['column_status'] = $this->language->get('column_status');
		$this->data['column_points'] = $this->language->get('column_points');
		$this->data['column_orders'] = $this->language->get('column_orders');
		$this->data['column_total'] = $this->language->get('column_total');
		$this->data['column_action'] = $this->language->get('column_action');
		
		$this->data['entry_date_start'] = $this->language->get('entry_date_start');
		$this->data['entry_date_end'] = $this->language->get('entry_date_end');
        $this->data['entry_stores'] = $this->language->get('entry_stores');

		$this->data['button_filter'] = $this->language->get('button_filter');
		
		$this->data['token'] = null;

        $this->data['export_link'] = $this->url->link('tool/w_export_tool/exportCustomerRewardsReport', '', 'SSL');
				 
		$this->template = 'report/customer_reward.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}

    public function ajaxResponse()
    {
        $this->language->load('report/customer_reward');


        $requestData = $_REQUEST;
        $columns = array(
           0 => 'customer'   ,
           1 => 'email'       ,
           2 => 'customer_group',
           3 => 'status'   ,
           4 => 'points'   ,
           5 =>'orders'  ,
           6 => 'total'
        );

        // default and filtaration values

        if (isset($requestData['date'])) {
            $date = explode("-", $requestData['date']);
            $filter_date_start = date('Y-m-d', strtotime(trim($date[0])));
        } else {
            $filter_date_start = date('Y-m-d', strtotime(date('Y') . '-' . date('m') . '-01'));
        }

        if (isset($requestData['date'])) {
            $date = explode("-", $requestData['date']);
            $filter_date_end = date('Y-m-d', strtotime(trim($date[1])));
        } else {
            $filter_date_end = date('Y-m-d');
        }


        $stores_codes = [];
        $multi_store_manager = FALSE;

        if (isset($requestData['filter_stores'])) {
            $multi_store_manager = TRUE;
            if(empty($requestData['filter_stores']) || !array($requestData['filter_stores'])){
                // load app model
                $this->load->model('module/multi_store_manager/settings');
                $stores_codes = $this->model_module_multi_store_manager_settings->getStoreCodesArray();
            }else{
                $stores_codes = $requestData['filter_stores'];
            }
        } else {
            if(isset($this->request->get['comming_from']) && $this->request->get['comming_from'] == 'multi_store_manager'){
                // load app model
                $this->load->model('module/multi_store_manager/settings');
                // check if app active
                if( $this->model_module_multi_store_manager_settings->isActive()){
                    $multi_store_manager = TRUE;
                    $stores_codes = $this->model_module_multi_store_manager_settings->getStoreCodesArray();
                }
            }
        }


        $this->load->model('report/customer');

        $this->data['orders'] = array();

        $data = array(
            'filter_date_start' => $filter_date_start,
            'filter_date_end' => $filter_date_end,
            'search' => !empty($requestData['search']['value']) ? $requestData['search']['value'] : null,
            'multi_store_manager' => $multi_store_manager,
            'stores_codes' => $stores_codes
        );

        $order_total = $this->model_report_customer->getTotalRewardPoints($data);

        $totalData = $order_total;
        $totalFiltered = $totalData;


        $results = $this->model_report_customer->getRewardPointsDataTable($data, $requestData, $columns);


        $alt = array();
        foreach ($results['data'] as $result) {
            $alt[] = [$result['customer'],
                $result['email'], $result['customer_group'], $result['status'], $result['points'],$result['orders'],$result['total'],['text' => $this->language->get('text_edit'),
                    'href' =>(string) $this->url->link('sale/customer/update', 'customer_id=' . $result['customer_id'], 'SSL')]];
        }


//        var_dump($alt);
//        die();
        $totalFiltered = $results['totalFilter'];

        $json_data = array(
            "draw" => intval($requestData['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "iTotalDisplayRecords" => intval($totalData), // total number of records
            "recordsTotal" => intval($totalData), // total number of records
            "recordsFiltered" => $totalFiltered, // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $alt   // total data array
        );


        echo json_encode($json_data);


    }
}
?>