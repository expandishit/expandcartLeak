<?php
class ControllerLoghistoryLogs extends Controller {

    private $logModelObject = '';
    private $logModuleName   = '';

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->logModuleName = $log_module  = $this->request->get['log_module']; // sale_coupon
        $base_module         = $this->request->get['base_module'] ?? '';         // sale/coupon

        //load model / language of the module
        $this->load->model("loghistory/".$log_module);
        $modelToLoad = "model_loghistory_".$log_module;

        $this->logModelObject = $modelToLoad;
        $this->load->language('loghistory/'.$log_module);

        //load language of base module
        if($base_module){
            $this->load->language($base_module);
        }

    }

	public function index() {     
		//$this->language->load('loghistory/sale_coupon');  // loaded in the constructor

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
            'text'      => $this->language->get('text_records'),
            'href'      => $this->url->link('loghistory/histories', '', 'SSL'),
            'separator' => false
        );

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('loghistory/histories', '', 'SSL'),
      		'separator' => ' :: '
   		);		


		$this->template = 'loghistory/'.$this->logModuleName.'/list.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render_ecwig());
	}
    public function view() {

        $this->language->load('loghistory/histories');

        $this->document->setTitle($this->language->get('heading_title'));


        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_records'),
            'href'      => $this->url->link('loghistory/histories', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('loghistory/histories', '', 'SSL'),
            'separator' => ' :: '
        );



        $log_id = $this->request->get['log_id'];

        $model = $this->logModelObject;

        $this->data = $this->$model->getHistoryInfo($log_id); // new function in each module to handle required data


        $this->template = 'loghistory/'.$this->logModuleName.'/form.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
    }


    public function ajaxResponse()
    {
        $requestData = $_REQUEST;

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


        $data = array(
            'filter_date_start' => $filter_date_start,
            'filter_date_end' => $filter_date_end,
            'search' => !empty($requestData['search']['value']) ? $requestData['search']['value'] : null,
        );

        $model = $this->logModelObject;
        /*** all following logic can be handled from the model of each module */
        $ajaxResponse = $this->$model->ajaxResponse($data,$requestData); // new function in each module to handle required data
        //var_dump($ajaxResponse);
        //exit;

        $totalData = $ajaxResponse['total']; //$this->model_loghistory_coupon->getTotalCoupons($data);

        $results = $ajaxResponse['data']; //$this->model_loghistory_coupon->getCouponsDataTable($data, $requestData, $columns);


        $alt = $ajaxResponse['data']; // array();

        $totalFiltered = $ajaxResponse['totalFilter']; //$results['totalFilter'];

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