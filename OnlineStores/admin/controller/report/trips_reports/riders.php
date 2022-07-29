<?php
class ControllerReportTripsReportsRiders extends Controller {
    
	public function index() {     
		$this->language->load('module/trips');

		$this->document->setTitle($this->language->get('rider_report_title'));

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
       		'text'      => $this->language->get('rider_report_title'),
			'href'      => $this->url->link('report/trips_reports/riders', '', 'SSL'),
      		'separator' => ' :: '
   		);  

        $this->data['heading_title'] = $this->language->get('rider_report_title');
        // action and localized values
        $this->data['action'] = $this->url->link('report/trips_reports/riders/ajaxResponse', $comming_from, 'SSL');
		$this->data['text_no_results'] = $this->language->get('text_no_results');
		$this->data['text_all_status'] = $this->language->get('text_all_status');	
		$this->data['column_rider'] = $this->language->get('column_rider');
		$this->data['column_trips'] = $this->language->get('column_trips');
		$this->data['column_action'] = $this->language->get('column_action');
		$this->data['entry_date_start'] = $this->language->get('entry_date_start');
		$this->data['entry_date_end'] = $this->language->get('entry_date_end');
		$this->data['button_filter'] = $this->language->get('button_filter');
		
        $this->template = 'module/trips/reports/riders.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}

    public function ajaxResponse()
    {

        header( 'Content-Type: application/json; charset=utf-8' );
        $this->language->load('module/trips');

        $requestData = $_REQUEST;

        $columns = array(
            0 => 'customer',
            1 => 'trips',
           
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


        $this->load->model('report/customer');

        $this->data['orders'] = array();

        $data = array(
            'filter_date_start' => $filter_date_start,
            'filter_date_end' => $filter_date_end,
            'trips' => 1,
            'search' => !empty($requestData['search']['value']) ? $requestData['search']['value'] : null,
        );

        $order_total = $this->model_report_customer->getTotalOrders($data);

        $totalData = $order_total;
        $totalFiltered = $totalData;

        $results = $this->model_report_customer->getOrdersDataTable($data, $requestData, $columns);

        $alt = array();
        foreach ($results['data'] as $result) {
            $alt[] = [
                $result['customer'],$result['orders'],$result['canceledTrips'], 
             
               ];
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