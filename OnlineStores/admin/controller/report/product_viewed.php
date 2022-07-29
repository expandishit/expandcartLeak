<?php
class ControllerReportProductViewed extends Controller {

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
		$this->language->load('report/product_viewed');

		$this->document->setTitle($this->language->get('heading_title'));
		
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';
				
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
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('report/product_viewed', '', 'SSL'),
      		'separator' => ' :: '
   		);

        $stores_codes = [];
        $multi_store_manager = FALSE;

        $comming_from = '';
        // check multi store manager app
        if(isset($this->request->get['comming_from']) && $this->request->get['comming_from'] == 'multi_store_manager'){
            $comming_from = 'comming_from=multi_store_manager';
            // load app model
            $this->load->model('module/multi_store_manager/settings');
            // check if app active
            if( $this->model_module_multi_store_manager_settings->isActive()){
                $this->data['multi_store_manager'] = $multi_store_manager  = TRUE;
                $this->data['stores_codes'] = $this->model_module_multi_store_manager_settings->getStoreCodes();
                $stores_codes = $this->model_module_multi_store_manager_settings->getStoreCodesArray();
            }
        }
		
		$this->load->model('report/product');
		
		$data = array(
			'start' => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit' => $this->config->get('config_admin_limit'),
            'multi_store_manager' => $multi_store_manager,
            'stores_codes' => $stores_codes
		);
				
		$product_viewed_total = $this->model_report_product->getTotalProductsViewed($data); 
		
		$product_views_total = $this->model_report_product->getTotalProductViews($data);
		
		$this->data['products'] = array();
		
		$results = $this->model_report_product->getProductsViewed($data);
		
		foreach ($results as $result) {
			if ($result['viewed']) {
				$percent = round($result['viewed'] / $product_views_total * 100, 2);
			} else {
				$percent = 0;
			}
					
			$this->data['products'][] = array(
				'name'    => $result['name'],
				'model'   => $result['model'],
				'viewed'  => $result['viewed'],
				'percent' => $percent . '%'			
			);
		}
 		
		$this->data['heading_title'] = $this->language->get('heading_title');
        $this->data['action'] = $this->url->link('report/product_viewed/ajaxResponse', $comming_from, 'SSL');
        $this->data['reset'] = $this->url->link('report/product_viewed/reset', '', 'SSL');

        $this->data['text_no_results'] = $this->language->get('text_no_results');
		
		$this->data['column_name'] = $this->language->get('column_name');
		$this->data['column_model'] = $this->language->get('column_model');
		$this->data['column_viewed'] = $this->language->get('column_viewed');
		$this->data['column_percent'] = $this->language->get('column_percent');
		
		$this->data['button_reset'] = $this->language->get('button_reset');
        $this->data['entry_stores'] = $this->language->get('entry_stores');
        $this->data['button_filter'] = $this->language->get('button_filter');

		$url = '';		
				
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
				
		$this->data['reset'] = $this->url->link('report/product_viewed/reset', '', 'SSL');

		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];
		
			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}
						
		$pagination = new Pagination();
		$pagination->total = $product_viewed_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('report/product_viewed', 'page={page}', 'SSL');
			
		$this->data['pagination'] = $pagination->render();
				 
		$this->data['export_link'] = $this->url->link('tool/w_export_tool/exportProductViewedReport', '', 'SSL');

		$this->template = 'report/product_viewed.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}
	
	public function reset() {
		$this->language->load('report/product_viewed');
		
		$this->load->model('report/product');
		
		$this->model_report_product->reset();
		
		$this->session->data['success'] = $this->language->get('text_success');

        $requestData = $_REQUEST;

        $json_data = array(
            "draw" => intval($requestData['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => 0, // total number of records
            "recordsFiltered" => 0, // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => []   // total data array
        );


        echo json_encode($json_data);
	}


    public function ajaxResponse()
    {
        $this->language->load('report/sale_coupon');


        $requestData = $_REQUEST;
        $columns = array(
            0 => 'name',
            1 => 'model',
            2 => 'viewed',
            3 => 'percent'

        );

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

        // default and filtaration values
        $this->load->model('report/product');

        $this->data['orders'] = array();

        $data = array(
            'search' => !empty($requestData['search']['value']) ? $requestData['search']['value'] : null,
            'multi_store_manager' => $multi_store_manager,
            'stores_codes' => $stores_codes
        );

        $total = $this->model_report_product->getTotalProductsViewed($data);
        $product_views_total = $this->model_report_product->getTotalProductViews($data);

        $totalData = $total;
        $totalFiltered = $totalData;


        $results = $this->model_report_product->getProductsViewedDataTable($data, $requestData, $columns);


        $alt = array();
        foreach ($results['data'] as $result) {
            if ($result['viewed']) {
                $percent = round($result['viewed'] / $product_views_total * 100, 2);
            } else {
                $percent = 0;
            }

            $alt[] = [$result['name'],
                $result['model'], $result['viewed'], $percent.'%'];
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