<?php
class ControllerReportAffiliateCommission extends Controller {
	public function index() {     
		$this->language->load('report/affiliate_commission');

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
			'href'      => $this->url->link('report/affiliate_commission', '', 'SSL'),
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

        $this->data['action'] = $this->url->link('report/affiliate_commission/ajaxResponse', $comming_from, 'SSL');
        $this->data['heading_title'] = $this->language->get('heading_title');
        $this->data['edit_button_text'] = $this->language->get('text_edit') ;
		$this->data['text_no_results'] = $this->language->get('text_no_results');
		
		$this->data['column_affiliate'] = $this->language->get('column_affiliate');
		$this->data['column_email'] = $this->language->get('column_email');
		$this->data['column_status'] = $this->language->get('column_status');
		$this->data['column_commission'] = $this->language->get('column_commission');
		$this->data['column_orders'] = $this->language->get('column_orders');
		$this->data['column_total'] = $this->language->get('column_total');
		$this->data['column_action'] = $this->language->get('column_action');
		
		$this->data['entry_date_start'] = $this->language->get('entry_date_start');
		$this->data['entry_date_end'] = $this->language->get('entry_date_end');
        $this->data['entry_stores'] = $this->language->get('entry_stores');

		$this->data['button_filter'] = $this->language->get('button_filter');
		
		$this->data['token'] = null;
		
		$url = '';
						
		if (isset($this->request->get['filter_date_start'])) {
			$url .= '&filter_date_start=' . $this->request->get['filter_date_start'];
		}
		
		if (isset($this->request->get['filter_date_end'])) {
			$url .= '&filter_date_end=' . $this->request->get['filter_date_end'];
		}
		
        $this->data['export_link'] = $this->url->link('tool/w_export_tool/exportCommissionReport', '', 'SSL');
				 
		$this->template = 'report/affiliate_commission.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}

    public function ajaxResponse()
    {
        $this->language->load('report/affiliate_commission');


        $requestData = $_REQUEST;
        $columns = array(
            0 => 'affiliate',
            1 => 'email',
            2 => 'status',
            3 => 'commission',
            4 => 'orders',
            5 => 'total',

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

        $this->load->model('report/affiliate');

        $this->data['orders'] = array();

        $data = array(
            'filter_date_start' => $filter_date_start,
            'filter_date_end' => $filter_date_end,
            'search' => !empty($requestData['search']['value']) ? $requestData['search']['value'] : null,
            'multi_store_manager' => $multi_store_manager,
            'stores_codes' => $stores_codes
        );

        $affiliate_total = $this->model_report_affiliate->getTotalCommission($data);

        $totalData = $affiliate_total;
        $totalFiltered = $totalData;


        $results = $this->model_report_affiliate->getCommissionDataTable($data, $requestData, $columns);


        $alt = array();
        foreach ($results['data'] as $result) {
            $status = ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')) ;

            $alt[] = [$result['affiliate'],
                $result['email'], $status , $this->currency->format($result['commission'], $this->config->get('config_currency'))
                ,$result['orders'],$this->currency->format($result['total'], $this->config->get('config_currency'))
                ,['text' => $this->language->get('text_edit'),
                    'href' =>(string) $this->url->link('sale/affiliate/update', 'affiliate_id=' . $result['affiliate_id'], 'SSL')]];
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