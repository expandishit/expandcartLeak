<?php

class ControllerReportAbandonedCart extends Controller
{
    public function __construct($registry)
    {

        parent::__construct($registry);

        $this->initializer([
            'module/abandoned_cart/settings',
            'abandonedReports' => 'module/abandoned_cart/reports',
        ]);

        if ($this->settings->isInstalled() == false) {
            $this->redirect($this->url->link('marketplace/home', '', 'SSL'));
        }

        if ($this->settings->isActive() == false) {
            $this->redirect($this->url->link('marketplace/home', '', 'SSL'));
        }
    }

    public function ordersList()
    {
        $this->language->load('report/sale_order');
        $this->language->load('sale/order');
        $this->language->load('catalog/product_filter');
        $this->language->load('report/abandoned_cart');
        $this->language->load('module/abandoned_cart');

        $this->document->setTitle($this->language->get('abandoned_cart_heading_title'));

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

        $this->load->model('localisation/order_status');

        $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

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

        $comming_from = '';
        // check multi store manager app
        if(isset($this->request->get['comming_from']) && $this->request->get['comming_from'] == 'multi_store_manager'){
            $comming_from = '&comming_from=multi_store_manager';
            // load app model
            $this->load->model('module/multi_store_manager/settings');
            // check if app active
            if( $this->model_module_multi_store_manager_settings->isActive()){
                $this->data['multi_store_manager'] = TRUE;
                $this->data['stores_codes'] = $this->model_module_multi_store_manager_settings->getStoreCodes();
            }
        }

        $this->data['filter_date_start'] = date('y/m/d', strtotime(date('Y') . '-' . date('m') . '-01'));
        $this->data['filter_date_end'] = date('y/m/d');
        $this->data['filter_group'] = 'week';
        $this->data['filter_order_status_id'] = 0;
        $this->data['comming_from'] = $comming_from;

        $this->data['export_link'] = $this->url->link('tool/w_export_tool/exportAbandonedReport', '', 'SSL');

        $this->template = 'report/abandoned_cart/ordersList.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );
        $this->response->setOutput($this->render_ecwig());
    }

    public function datatables()
    {
        $this->language->load('catalog/product');

        $request = $this->request->request;

        $this->load->model('catalog/product');

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $filterData = $this->request->post['filter'];
        }

        $this->data['products'] = array();

        $start = $request['start'];
        $length = $request['length'];

        $request['list'] = isset($request['list']) ? $request['list'] : 'orders';

        $orderColumn = $request['columns'][$request['order'][0]['column']]['name'];
        $orderType = $request['order'][0]['dir'];

        $filterData['search'] = null;
        if (isset($request['search']['value']) && strlen($request['search']['value']) > 0) {
            $filterData['search'] = $request['search']['value'];
        }

        $stores_codes = [];
        $multi_store_manager = FALSE;

        if (isset($filterData['filter_stores'])) {
            $multi_store_manager = TRUE;
            if(empty($filterData['filter_stores']) || !array($filterData['filter_stores'])){
                // load app model
                $this->load->model('module/multi_store_manager/settings');
                $stores_codes = $this->model_module_multi_store_manager_settings->getStoreCodesArray();
            }else{
                $stores_codes = $filterData['filter_stores'];
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

        $data = array(
            'sort' => $orderColumn,
            'order' => $orderType,
            'start' => $request['start'],
            'limit' => $request['length'],
            'multi_store_manager' => $multi_store_manager,
            'stores_codes' => $stores_codes
        );

        if ($request['list'] == 'orders') {
            $results = $this->abandonedReports->getOrdersList($data, $filterData);

        } else if ($request['list'] == 'orders') {
            $results = $this->abandonedReports->getProductsToFilter($data, $filterData);
        }

        $this->response->setOutput(json_encode([
            "draw" => intval($request['draw']),
            "recordsTotal" => intval($results['total']),
            "iTotalDisplayRecords" => intval($results['total']),
            "recordsFiltered" => $results['totalFiltered'],
            'data' => $results['data'],
        ]));
        return;
    }
}
