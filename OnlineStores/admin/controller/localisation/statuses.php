<?php

class ControllerLocalisationStatuses extends Controller {

    private $objectNames = array('order_status', 'stock_status', 'return_status', 'return_action', 'return_reason');

    protected $error;

    private $plan_id = PRODUCTID;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('plan/trial');
        $trial = $this->model_plan_trial->getLastTrial();

        if($trial){
            $this->plan_id = $trial['plan_id'];
        }
        if($this->plan_id == 3){
            $this->redirect(
                $this->url->link('error/permission', '', 'SSL')
            );
            exit();
        }
    }

    public function index() {
        //echo $this->config->get('config_order_status_id');die();
        $this->language->load('localisation/statuses');

        $this->document->setTitle($this->language->get('heading_title'));

        //$this->load->model('localisation/statuses');

        $this->load->model('localisation/language');
        $languages = $this->data['languages'] = $this->model_localisation_language->getLanguages();
        $this->data['languages'] = $languages;

        $this->data['statusObjects'] = $this->objectNames;
        $this->template = 'localisation/statuses_list.expand';
        $this->base = 'common/base';

        $this->response->setOutput($this->render_ecwig());
    }


    public function dtHandler() {
        $request = $_REQUEST;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        $objName = !empty($request['objName']) ? $request['objName'] : null;
        if(!in_array($objName, $this->objectNames)) return;


        $this->load->model('localisation/statuses');
        $start = $request['start'];
        $length = $request['length'];

        $columns = array(
            0 => $objName . '_id',
            1 => 'name'
        );
        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];

        $return = $this->model_localisation_statuses->dtHandler($objName, $start, $length, $search, $orderColumn, $orderType);
        $records = $return['data'];
        $totalData = $return['total'];
        $totalFiltered = $return['totalFiltered'];

        $json_data = array(
            "draw" => intval($request['draw']),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => $totalFiltered,
            "data" => $records
        );
        $this->response->setOutput(json_encode($json_data));
        return;
    }

    public function insert() {
        $response = array();
        $this->language->load('localisation/statuses');

        $data = $_REQUEST;
        $objName = $data['modal_objname'];
        $statuses = $data[$objName];

        if ($this->validateForm($statuses, $objName)) {
            $this->load->model('localisation/statuses');
            if($this->model_localisation_statuses->insert($statuses, $objName)) {
                $response['success'] = '1';
                $response['title'] = $this->language->get('notification_success_title');
                $response['success_msg'] = $this->language->get("text_success_$objName");
            }
            else {
                $response['success'] = '0';
                $response['title'] = $this->language->get('notification_error_title');
                $response['errors'] = $this->error;
            }
        } else {
            $response['success'] = '0';
            $response['title'] = $this->language->get('notification_error_title');
            $response['errors'] = $this->error;
        }
        $this->response->setOutput(json_encode($response));
        return;
    }

    public function bulkDelete()
    {
        $this->load->model("localisation/statuses");
        $this->load->language('localisation/statuses');
        

        $objName = $this->db->escape($this->request->post['objName']);
        if (
            isset($this->request->post['selected']) &&
            $this->{"validateDelete_" . $objName}($this->request->post['selected'])
        ) {

            foreach ($this->request->post['selected'] as $id) {
                $this->model_localisation_statuses->delete($id, $objName);
            }

            $response['status'] = 'success';
            $response['title'] = $this->language->get('notification_success_title');
            $response['message'] = $this->language->get('message_deleted_successfully');
        } else {
            $response['status'] = 'error';
            $response['title'] = $this->language->get('notification_error_title');
            $response['errors'] = $this->error;
        }

        $this->response->setOutput(json_encode($response));
        return;
    }

    public function get() {
        $id = $this->db->escape($this->request->post['id']);
        $objName = $this->db->escape($this->request->post['objName']);
        $this->load->model('localisation/statuses');
        $data = $this->model_localisation_statuses->get($id, $objName);
        $res = array();
        foreach ($data as $record) {
            $res[$objName][$record['language_id']] = $record['name'];
            if($objName == 'order_status')
                $res[$objName]['bk_color'] = $record['bk_color'];
        }
        $this->response->setOutput(json_encode($res));
    }

    public function update() {
        $response = array();
        $this->language->load('localisation/statuses');

        $data = $_REQUEST;
        $objName = $data['objName'];
        $id = $data['id'];
        $statuses = $data[$objName];

        if ($this->validateForm($statuses, $objName)) {
            $this->load->model('localisation/statuses');
            if($this->model_localisation_statuses->edit($id, $statuses, $objName)) {
                $response['status'] = 'success';
                $response['title'] = $this->language->get('notification_success_title');
                $response['message'] = $this->language->get("text_success_$objName");
            }
            else {
                $response['status'] = 'error';
                $response['title'] = $this->language->get('notification_error_title');
                $response['errors'] = $this->error;
            }
        } else {
            $response['status'] = 'error';
            $response['title'] = $this->language->get('notification_error_title');
            $response['errors'] = $this->error;
        }
        $this->response->setOutput(json_encode($response));
        return;
    }

    protected function validateForm($data = array(), $objName) {
        if (!$this->user->hasPermission('modify', "localisation/$objName")) {
            $this->error["warning_$objName"] = $this->language->get("error_permission_$objName");
        }
        if (!empty($data)) {
            foreach ($data as $key => $name) {
                if ((utf8_strlen($name) < 3) || (utf8_strlen($name) > 32)) {
                    if ($key === 'bk_color') continue;
                    $this->error["error_name_$objName"] = $this->language->get("error_name_$objName");
                }
            }
        } else {
            $this->error["warning_$objName"] = 'empty data';
        }
        return !$this->error;
    }

    protected function validateDelete_order_status($order_status_ids)
    {
        $this->load->language('localisation/statuses');

        $this->load->model('setting/store');
        $this->load->model('sale/order');

        if (!$this->user->hasPermission('modify', 'localisation/order_status')) {
            $this->error['warning_order_status'] = $this->language->get('error_permission_order_status');
        }

        if (in_array($this->config->get('config_order_status_id'), $order_status_ids)) {
            $this->error['warning_order_status'] = $this->language->get('error_default_order_status');
        }

        if (in_array($this->config->get('config_download_status_id'), $order_status_ids)) {
            $this->error['warning_order_status'] = $this->language->get('error_download_order_status');
        }

        $store_total = $this->model_setting_store->getTotalStoresByOrderStatusIds($order_status_ids);

        if ($store_total) {
            $this->error['warning_order_status'] = sprintf(
                $this->language->get('error_store_order_status'),
                $store_total
            );
        }

        $order_total = $this->model_sale_order->getTotalOrderHistoriesByOrderStatusIds($order_status_ids);

        if ($order_total) {
            $this->error['warning_order_status'] = sprintf(
                $this->language->get('error_order_order_status'),
                $order_total
            );
        }
        return !$this->error;
    }

    protected function validateDelete_stock_status($stock_status_ids)
    {
        $this->load->language('localisation/statuses');
        if (!$this->user->hasPermission('modify', 'localisation/stock_status')) {
            $this->error['warning_stock_status'] = $this->language->get('error_permission_stock_status');
        }
        $this->load->model('setting/store');
        $this->load->model('catalog/product');
        if (in_array($this->config->get('config_stock_status_id'), $stock_status_ids)) {
            $this->error['warning_stock_status'] = $this->language->get('error_default_stock_status');
        }

        $product_total = $this->model_catalog_product->getTotalProductsByStockStatusIds($stock_status_ids);

        if ($product_total) {
            $this->error['warning_stock_status'] = sprintf(
                $this->language->get('error_product_stock_status'),
                $product_total
            );
        }
        return !$this->error;
    }

    protected function validateDelete_return_status($return_status_ids)
    {
        $this->load->language('localisation/return_status');
        if (!$this->user->hasPermission('modify', 'localisation/return_status')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        $this->load->model('sale/return');

        if (in_array($this->config->get('config_return_status_id'), $return_status_ids)) {
            $this->error['warning'] = $this->language->get('error_default');
        }

        $return_total = $this->model_sale_return->getTotalReturnsByReturnStatusIds($return_status_ids);

        if ($return_total) {
            $this->error['warning'] = sprintf($this->language->get('error_return'), $return_total);
        }

        $return_total = $this->model_sale_return->getTotalReturnHistoriesByReturnStatusIds($return_status_ids);

        if ($return_total) {
            $this->error['warning'] = sprintf($this->language->get('error_return'), $return_total);
        }


        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    protected function validateDelete_return_action($return_action_ids)
    {
        $this->load->language('localisation/return_status');
        if (!$this->user->hasPermission('modify', 'localisation/return_action')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        $this->load->model('sale/return');

        $return_total = $this->model_sale_return->getTotalReturnsByReturnActionId($return_action_ids);

        if ($return_total) {
            $this->error['warning'] = sprintf($this->language->get('error_return'), $return_total);
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    protected function validateDelete_return_reason($return_reason_ids)
    {
        $this->load->language('localisation/return_reason');
        if (!$this->user->hasPermission('modify', 'localisation/return_reason')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        $this->load->model('sale/return');

        $return_total = $this->model_sale_return->getTotalReturnsByReturnReasonIds($return_reason_ids);

        if ($return_total) {
            $this->error['warning'] = sprintf($this->language->get('error_return'), $return_total);
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }
}
