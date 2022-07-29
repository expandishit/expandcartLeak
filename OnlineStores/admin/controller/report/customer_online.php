<?php  
class ControllerReportCustomerOnline extends Controller {

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
		$this->language->load('report/customer_online');
		
    	$this->document->setTitle($this->language->get('heading_title'));
		

						
  		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'href'      => $this->url->link('common/home', '', 'SSL'),
       		'text'      => $this->language->get('text_home'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'href'      => $this->url->link('report/customer_online', '', 'SSL'),
       		'text'      => $this->language->get('heading_title'),
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
		 $this->data['edit_button_text'] = $this->language->get('text_edit') ;
		$this->data['text_no_results'] = $this->language->get('text_no_results');
        // action and localized values
        $this->data['action'] = $this->url->link('report/customer_online/ajaxResponse', $comming_from, 'SSL');
		$this->data['column_ip'] = $this->language->get('column_ip');
		$this->data['column_customer'] = $this->language->get('column_customer');
		$this->data['column_url'] = $this->language->get('column_url');
		$this->data['column_referer'] = $this->language->get('column_referer');
		$this->data['column_date_added'] = $this->language->get('column_date_added');
		$this->data['column_action'] = $this->language->get('column_action');
        $this->data['entry_stores'] = $this->language->get('entry_stores');

		$this->data['button_filter'] = $this->language->get('button_filter');
				
		$this->data['token'] = null;
		
        $this->data['export_link'] = $this->url->link('tool/w_export_tool/exportCustomerOnlineReport', '', 'SSL');
				
		$this->template = 'report/customer_online.expand';
		$this->children = array(
			'common/header',	
			'common/footer'	
		);
		
		$this->response->setOutput($this->render_ecwig());
  	}



    public function ajaxResponse()
    {

        header( 'Content-Type: application/json; charset=utf-8' );

        $this->language->load('report/customer_online');
        $requestData = $_REQUEST;
        $columns = array(
            0 => 'ip',
            1 => 'customer',
            2 => 'url',
            3 => 'referer',
            4 => 'date_added',

        );

        // default and filtaration values


        if (isset($requestData['filter_ip'])) {
            $filter_ip = $requestData['filter_ip'];
        } else {
            $filter_ip = NULL;
        }

        if (isset($requestData['filter_customer'])) {
            $filter_customer = $requestData['filter_customer'];
        } else {
            $filter_customer = NULL;
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


        $this->load->model('report/online');

        $this->data['orders'] = array();

        $data = array(
            'filter_ip' => $filter_ip,
            'filter_customer' => $filter_customer,
            'search' => !empty($requestData['search']['value']) ? $requestData['search']['value'] : null,
            'multi_store_manager' => $multi_store_manager,
            'stores_codes' => $stores_codes
        );

        $order_total = $this->model_report_online->getTotalCustomersOnline($data);

        $totalData = $order_total;
        $totalFiltered = $totalData;


        $results = $this->model_report_online->getCustomersOnlineDataTable($data, $requestData, $columns);


        $alt = array();
        foreach ($results['data'] as $result) {

            $ip = '<a href="http://whatismyipaddress.com/ip/'.$result['ip'].'" target="_blank">'.implode('<br/>', str_split($result['ip'], 30)).'</a>';
            $url = '<a href="'.$result['url'].'" target="_blank">'.implode('<br/>', str_split($result['url'], 30)).'</a>';
            $referer = '<a href="'.$result['referer'].'" target="_blank">'.implode('<br/>', str_split($result['referer'], 30)).'</a>';
            $alt[] = [$ip,$result['customer'],
                $url,
                $referer,
                date($this->language->get('date_format_short'), strtotime($result['date_added'])),
                ['text' => $this->language->get('text_edit'),
                 'href' =>(string) $this->url->link('sale/customer/update', 'customer_id=' . $result['customer_id'], 'SSL')]];
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