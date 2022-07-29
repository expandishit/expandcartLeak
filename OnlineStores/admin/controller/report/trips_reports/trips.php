<?php
class ControllerReportTripsReportsTrips extends Controller
{
    public function index()
    {
        $this->language->load('module/trips');

        $this->document->setTitle($this->language->get('trip_report_title'));


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
               'text'      => $this->language->get('trip_report_title'),
            'href'      => $this->url->link('report/product_purchased', '', 'SSL'),
              'separator' => ' :: '
        );

        $comming_from = '';
        // check multi store manager app
        if (isset($this->request->get['comming_from']) && $this->request->get['comming_from'] == 'multi_store_manager') {
            $comming_from = 'comming_from=multi_store_manager';
            // load app model
            $this->load->model('module/multi_store_manager/settings');
            // check if app active
            if ($this->model_module_multi_store_manager_settings->isActive()) {
                $this->data['multi_store_manager'] = true;
                $this->data['stores_codes'] = $this->model_module_multi_store_manager_settings->getStoreCodes();
            }
        }

                
        $this->data['heading_title'] = $this->language->get('trip_report_title');
        $this->data['action'] = $this->url->link('report/trips_reports/trips/ajaxResponse', $comming_from, 'SSL');

        $this->data['text_no_results'] = $this->language->get('text_no_results');
        $this->data['text_all_status'] = $this->language->get('text_all_status');
        
        $this->data['column_name'] = $this->language->get('column_name');
        $this->data['column_model'] = $this->language->get('column_model');
        $this->data['column_quantity'] = $this->language->get('column_booked_seats');
        $this->data['column_total'] = $this->language->get('column_total');
        $this->data['column_profit'] = $this->language->get('column_profit');
        $this->data['column_current_quantity'] = $this->language->get('column_current_quantity');
        $this->data['column_cost'] = $this->language->get('column_cost');
        
        $this->data['entry_date_start'] = $this->language->get('entry_date_start');
        $this->data['entry_date_end'] = $this->language->get('entry_date_end');
        $this->data['entry_all_products'] = $this->language->get('entry_all_products');
    
        $this->data['button_filter'] = $this->language->get('button_filter');
    
        
        // Categories
        $this->load->model('catalog/category');
        $this->load->model('module/trips');
        $data['selectedCategories'] = $this->model_module_trips->getTripsCategoriesIDs();
        $this->data['categories']=$this->model_module_trips->getTripsCategories($data['selectedCategories']);

        $this->template = 'module/trips/reports/trips.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );
                
        $this->response->setOutput($this->render());
    }


    public function ajaxResponse()
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->language->load('module/trips');

        $requestData = $_REQUEST;
        $columns = array(
            0 => 'name',
            1 => 'quantity',
            2 => 'current_quantity',
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

        $filterDAta = $requestData['filter_all_products'];
        
        if (isset($requestData['order_status'])) {
            $filter_order_status_id = $requestData['order_status'];
        } else {
            $filter_order_status_id = 0;
        }
        


        $this->load->model('report/product');

        $this->data['orders'] = array();

        $data = array(
            'filter_date_start'	     => $filter_date_start,
            'filter_date_end'	     => $filter_date_end,
            'trips' => 1,
            'filter_product_category_id'  => $requestData['filter_product_category_id'],
            'search' => !empty($requestData['search']['value']) ? $requestData['search']['value'] : null,
            'multi_store_manager' => $multi_store_manager,
            'stores_codes' => $stores_codes
        );

        $product_total = $this->model_report_product->getTotalPurchased($data);

        $totalData = $product_total;
        $totalFiltered = $totalData;

        $results = $this->model_report_product->getPurchasedDataTable($data, $requestData, $columns);

        $alt = array();
        foreach ($results['data'] as $result) {
            $alt[] = [$result['name'], $result['current_quantity'], $result['quantity'],$this->currency->format($result['cost'], $this->config->get('config_currency'))];
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
