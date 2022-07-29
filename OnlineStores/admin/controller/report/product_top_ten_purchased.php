<?php
class ControllerReportProductTopTenPurchased extends Controller {

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
		$this->language->load('report/product_top_ten_purchased');

		$this->document->setTitle($this->language->get('heading_title'));

        // define default values
        $this->data['filter_date_start'] = date('y/m/d', strtotime(date('Y') . '-' . date('m') . '-01'));
        $this->data['filter_date_end'] = date('y/m/d');
        $this->data['filter_order_status_id'] = 0;

   		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', '', 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('report/product_purchased', '', 'SSL'),
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
				
		$this->data['heading_title'] = $this->language->get('heading_title');
        $this->data['action'] = $this->url->link('report/product_top_ten_purchased/ajaxResponse', $comming_from, 'SSL');

        $this->data['text_no_results'] = $this->language->get('text_no_results');
		$this->data['text_all_status'] = $this->language->get('text_all_status');
		
		$this->data['column_name'] = $this->language->get('column_name');
		$this->data['column_model'] = $this->language->get('column_model');
		$this->data['column_quantity'] = $this->language->get('column_quantity');
		$this->data['column_total'] = $this->language->get('column_total');
        $this->data['column_profit'] = $this->language->get('column_profit');
        $this->data['column_current_quantity'] = $this->language->get('column_current_quantity');
        $this->data['column_cost'] = $this->language->get('column_cost');
		
		$this->data['entry_date_start'] = $this->language->get('entry_date_start');
		$this->data['entry_date_end'] = $this->language->get('entry_date_end');
		$this->data['entry_status'] = $this->language->get('entry_status');
        $this->data['entry_stores'] = $this->language->get('entry_stores');

		$this->data['button_filter'] = $this->language->get('button_filter');
		
		$this->data['token'] = null;
		
		$this->load->model('localisation/order_status');
		
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		
		$this->template = 'report/product_top_ten_purchased.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}


    public function ajaxResponse()
    {

        header( 'Content-Type: application/json; charset=utf-8' );
        $this->language->load('report/product_top_ten_purchased');

        $requestData = $_REQUEST;
        $columns = array(
            0 => 'name',
            1 => 'model',
            2 => 'quantity',
            3 => 'current_quantity',
            4 => 'total',
            5 => 'cost',
            6 => 'profit',
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


        if (isset($requestData['order_status'])) {
            $filter_order_status_id = $requestData['order_status'];
        } else {
            $filter_order_status_id = 0;
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

        $this->load->model('report/product');

        $this->data['orders'] = array();


        $total = 10;

        $totalData = $total;
        $totalFiltered = $totalData;

        $data = array(
            'filter_date_start' => $filter_date_start,
            'filter_date_end' => $filter_date_end,
            'filter_order_status_id' => $filter_order_status_id,
            'total' => $total,
            'search' => !empty($requestData['search']['value']) ? $requestData['search']['value'] : null,
            'multi_store_manager' => $multi_store_manager,
            'stores_codes' => $stores_codes
        );

        $results = $this->model_report_product->getTopTenPurchasedDataTable($data, $requestData, $columns);


        $alt = array();
        foreach ($results['data'] as $result) {
            $alt[] = [$result['name'],$result['model'], $result['current_quantity'], $result['quantity'],
                $this->currency->format($result['total'], $this->config->get('config_currency')),$this->currency->format($result['cost'], $this->config->get('config_currency')),$this->currency->format($result['profit'], $this->config->get('config_currency'))];
        }

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