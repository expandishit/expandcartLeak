<?php

use \PhpOffice\PhpSpreadsheet\Spreadsheet;
use \PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ControllerReportSaleOrder extends Controller
{
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

    public function index()
    {
        $this->language->load('report/sale_order');
        $this->language->load('catalog/product_filter');
        $this->language->load('sale/order');

        $this->document->setTitle($this->language->get('heading_title'));

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

        // define default values
        $this->data['filter_date_start'] = date('y/m/d', strtotime(date('Y') . '-' . date('m') . '-01'));
        $this->data['filter_date_end'] = date('y/m/d');
        $this->data['filter_group'] = 'week';
        $this->data['filter_order_status_id'] = 0;

        //breadcrumbs
        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('report/sale_order', '', 'SSL'),
            'separator' => ' :: '
        );

        // action and localized values
        $this->data['action'] = $this->url->link('report/sale_order/ajaxResponse', $comming_from, 'SSL');
        $this->data['heading_title'] = $this->language->get('heading_title');
        $this->data['text_no_results'] = $this->language->get('text_no_results');
        $this->data['text_all_status'] = $this->language->get('text_all_status');
        $this->data['column_date_start'] = $this->language->get('column_date_start');
        $this->data['column_date_end'] = $this->language->get('column_date_end');
        $this->data['column_orders'] = $this->language->get('column_orders');
        $this->data['column_products'] = $this->language->get('column_products');
        $this->data['column_tax'] = $this->language->get('column_tax');
        $this->data['column_total'] = $this->language->get('column_total');
        $this->data['column_profit'] = $this->language->get('column_profit');
        $this->data['entry_date_start'] = $this->language->get('entry_date_start');
        $this->data['entry_date_end'] = $this->language->get('entry_date_end');
        $this->data['entry_group'] = $this->language->get('entry_group');
        $this->data['entry_status'] = $this->language->get('entry_status');
        $this->data['entry_archive'] = $this->language->get('entry_archive');
        $this->data['entry_stores'] = $this->language->get('entry_stores');
        $this->data['archive_all'] = $this->language->get('archive_all');
        $this->data['archive_not_archived'] = $this->language->get('archive_not_archived');
        $this->data['button_filter'] = $this->language->get('button_filter');


        $this->load->model('localisation/order_status');
        $this->load->model('localisation/country');
        $this->load->model('sale/order');
        $this->load->model('user/user');  

        $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $this->data['countries'] = $this->model_localisation_country->getCountries();

         //For Order Assignee App
        $this->load->model("module/order_assignee");
        $this->data['isOrderAssigneeAppInstalled']=$this->model_module_order_assignee->isOrderAssigneeAppInstalled();
        if($this->data['isOrderAssigneeAppInstalled'])
        $this->data['admins_list'] = $this->model_user_user->getUsers();

        $this->data['filterElements'] = [
            'totalRange' => $this->model_sale_order->getOrderMinMaxTotal(),
        ];

        $this->data['groups'] = array();
        $this->data['groups'][] = array(
            'text' => $this->language->get('text_year'),
            'value' => 'year',
        );

        $this->data['groups'][] = array(
            'text' => $this->language->get('text_month'),
            'value' => 'month',
        );

        $this->data['groups'][] = array(
            'text' => $this->language->get('text_week'),
            'value' => 'week',
        );

        $this->data['groups'][] = array(
            'text' => $this->language->get('text_day'),
            'value' => 'day',
        );

        $this->data['export_link'] = $this->url->link('tool/w_export_tool/exportOrdersReport', '', 'SSL');

        $this->template = 'report/sale_order.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
    }

    public function ajaxResponse()
    {
        $this->language->load('report/sale_order');

        $requestData = $_REQUEST;

        $columns = array(
            0 => 'date_start',
            1 => 'date_end',
            2 => 'orders',
            3 => 'products',
            4 => 'tax',
            5 => 'total',
            6 => 'profit',
            7 => 'country_id',
            8 => 'customer_id',
            9 => 'product_id',
            10 => 'range_min',
            11 => 'range_max',
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

        if (isset($requestData['filter_group'])) {
            $filter_group = $requestData['filter_group'];
        } else {
            $filter_group = 'week';
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

        $this->load->model('report/sale');

        $this->data['orders'] = array();
        $data = array(
            'filter_date_start' => $filter_date_start,
            'filter_date_end' => $filter_date_end,
            'filter_group' => $filter_group,
            'filter_order_status_id' => $filter_order_status_id,
            'archive' => $requestData['archive'],
            'start' => 0,
            'limit' => 2,
            'search' => !empty($requestData['search']['value']) ? $requestData['search']['value'] : null,
            'multi_store_manager' => $multi_store_manager,
            'stores_codes' => $stores_codes,
            'country_id' => $requestData['country_id'],
            'customer_id' => $requestData['customer_id'],
            'product_id' => $requestData['product_id'],
            'range_min' => $requestData['ranges']['min'],
            'range_max' => $requestData['ranges']['max'],
        );   
        if(\Extension::isInstalled('order_assignee') &&$this->config->get('order_assignee')['status']==1)
        {   
            $orderAssignee = $requestData['order_assignee_id'];
            $data += ['order_assignee_id' => $orderAssignee, ];
        }

        $order_total = $this->model_report_sale->getTotalOrders($data);

        $totalData = $order_total;
        $totalFiltered = $totalData;

        $results = $this->model_report_sale->getOrdersJson($data, $requestData, $columns);


        $alt = array();
        foreach ($results['data'] as $result) {
            $alt[] = [date($this->language->get('date_format_short'), strtotime($result['date_start'])),
                date($this->language->get('date_format_short'), strtotime($result['date_end'])), $result['orders'],
                $result['products'], $result['tax'], $result['total'], $result['profit']];
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

    public function compactExport()
    {
        ini_set('memory_limit','2048M');
        ini_set('upload_max_filesize','1024M');
        ini_set('max_execution_time', 900);

        ini_set('max_input_vars',2000);
        ini_set('max_input_time',1000);

        $this->language->load('report/sale_order');
        $this->load->model('report/sale');

        $orders = $this->model_report_sale->getCompactOrders($this->request->post);

        $header = [
            'OrderId',
            'Modified at',
            'Created at',
            'Order status',
            'Paymeny method',
            'Customer name',
            'Customer address',
            'Customer phone',
            'Product id',
            'Product name',
            'Product barcode',
            'Product SKU',
            'Quantity',
            'Price',
            'Total',
            'Shipping fees',
            'Shipping method',
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->getRowDimension('1')->setRowHeight(50);
        $key = 'A';
        $index = 1;
        foreach ($header as $cell) {
            $sheet->getColumnDimension($key)->setAutoSize(true);
            $sheet->setCellValue($key . $index, $cell);
            $key++;
        }

        $refactor = function ($row) {

            $address = $row['shipping_address_1'] . ', ' .
                ($row['shipping_address_2'] ? $row['shipping_address_2'] . ', ' : '') .
                $row['shipping_city'] . ', ' . $row['shipping_country'];

            return [
                $row['order_id'],
                $row['date_modified'],
                $row['date_added'],
                $row['order_status'],
                $row['payment_method'],
                $row['full_name'],
                $address,
                $row['telephone'],
                $row['product_id'],
                $row['name'],
                $row['barcode'],
                $row['sku'],
                $row['quantity'],
                $row['price'],
                $row['total'],
                $row['shipping_fees'],
                $row['shipping_method'],
            ];
        };

        foreach ($orders as $order) {
            $index++;
            $key = 'A';
            foreach ($refactor($order) as $cell) {
                $sheet->setCellValue($key . $index, $cell);
                $key++;
            }
        }

        $writer = new Xlsx($spreadsheet);
        $writer->setPreCalculateFormulas(false);

        $filname = 'Order-Report-' . time().'.xlsx';






        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename='.$filname);
        header('Cache-Control: max-age=0');

        ob_end_clean();
        ob_start();

        $writer->save("php://output");
    }
}
