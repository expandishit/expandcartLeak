<?php
class ControllerReportSaleCoupon extends Controller {

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
		$this->language->load('report/sale_coupon');

		$this->document->setTitle($this->language->get('heading_title'));


        // define default values
        $this->data['filter_date_start'] = date('y/m/d', strtotime(date('Y') . '-' . date('m') . '-01'));
        $this->data['filter_date_end'] = date('y/m/d');

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
        
		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', '', 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('report/sale_coupon', '', 'SSL'),
      		'separator' => ' :: '
   		);		
		


        // action and localized values
        $this->data['action'] = $this->url->link('report/sale_coupon/ajaxResponse', $comming_from, 'SSL');
 		$this->data['heading_title'] = $this->language->get('heading_title');
 		$this->data['edit_button_text'] = $this->language->get('text_edit') ;
		$this->data['text_no_results'] = $this->language->get('text_no_results');
		
		$this->data['column_name'] = $this->language->get('column_name');
		$this->data['column_code'] = $this->language->get('column_code');
		$this->data['column_orders'] = $this->language->get('column_orders');
		$this->data['column_total'] = $this->language->get('column_total');
		$this->data['column_action'] = $this->language->get('column_action');
		
		$this->data['entry_date_start'] = $this->language->get('entry_date_start');
		$this->data['entry_date_end'] = $this->language->get('entry_date_end');
        $this->data['entry_stores'] = $this->language->get('entry_stores');

        $this->data['button_filter'] = $this->language->get('button_filter');
		
		$this->data['token'] = null;
        
        $this->data['export_link'] = $this->url->link('tool/w_export_tool/exportCouponsReport', '', 'SSL');

        //Affiliate promo app
        if (\Extension::isInstalled('affiliate_promo') && $this->config->get('affiliate_promo')['status'] == 1) {
            $this->data['affiliate_promo'] = true;

            $this->load->model('sale/affiliate');
            $this->data['affiliates'] = $this->model_sale_affiliate->getAffiliates(['selections' => ['affiliate_id', 'CONCAT(firstname, \' \', lastname) AS name', 'email']]);
        }
        //////////////////////

		$this->template = 'report/sale_coupon.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render_ecwig());
	}


    public function ajaxResponse()
    {
        $this->language->load('report/sale_coupon');


        $requestData = $_REQUEST;
        $columns = array(
            0 => 'name',
            1 => 'code',
            2 => 'orders',
            3 => 'total',

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

        //Affiliate promo App
        $filter_affiliate_id = 0;
        if (isset($requestData['filter_affiliate_id'])) {
            $filter_affiliate_id = $requestData['filter_affiliate_id'];
        }
        /////////////////////
        $this->load->model('report/coupon');

        $this->data['orders'] = array();

        $data = array(
            'filter_date_start' => $filter_date_start,
            'filter_date_end' => $filter_date_end,
            'search' => !empty($requestData['search']['value']) ? $requestData['search']['value'] : null,
            'multi_store_manager' => $multi_store_manager,
            'stores_codes' => $stores_codes,
            'filter_affiliate_id' => $filter_affiliate_id
        );

        $totalData = $this->model_report_coupon->getTotalCoupons($data);

        $results = $this->model_report_coupon->getCouponsDataTable($data, $requestData, $columns);

        $alt = array();
        foreach ($results['data'] as $result) {
            $alt[] = [
                'coupon_id' => $result['coupon_id'],
                'name' => $result['name'],
                'code' => $result['code'],
                'orders' => $result['orders'],
                'total' => $result['total'],
                ['text' => $this->language->get('text_edit'),
                    'href' =>(string) $this->url->link('sale/coupon/update', 'coupon_id=' . $result['coupon_id'], 'SSL')]];
        }

        $totalFiltered = $results['totalFilter'];

        $json_data = array(
            "draw" => intval($requestData['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "iTotalDisplayRecords" => $totalData, // total number of records
            "recordsTotal" => $totalData, // total number of records
            "recordsFiltered" => $totalFiltered, // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $alt   // total data array
        );


        echo json_encode($json_data);


    }

}
?>