<?php

class ControllerModuleManualShipping extends Controller
{
    protected $error;

    public function index()
    {
        $this->language->load('module/manual_shipping');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_modules'),
            'href'      => $this->url->link('marketplace/home', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('module/manual_shipping', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->initializer([
            'manualShipping' => 'module/manual_shipping/settings',
            'locales' => 'localisation/language'
        ]);

        $this->data['languages'] = $this->locales->getLanguages();

        $this->data['manual_shipping'] = $settings = $this->manualShipping->getSettings();

        $this->template = 'module/manual_shipping/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
    }

    public function createShipment()
    {
        $this->language->load('module/manual_shipping');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_modules'),
            'href'      => $this->url->link('marketplace/home', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('module/manual_shipping', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->initializer([
            'manualShipping' => 'module/manual_shipping/settings',
            'order' => 'sale/order',
            'gateway' => 'module/manual_shipping/gateways',
        ]);

       $this->data['order_products'] = $this->order->getOrderProducts($this->request->get['order_id']);
       $this->data['allGateways'] = $this->gateway->getCompactShippingGateways(["language_id"=>$this->config->get('config_language_id')])['data'];
       $this->data['order_id'] = $order_id = $this->request->get['order_id'];
       $this->data['products'] =  $this->manualShipping->get_shipped_data_by_order_id($order_id);

        $this->template = 'module/manual_shipping/create_shipment.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
    }

    public function insertShipment()
    {
        if (
            $this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)
        ) {
            return 'error';
        }

        $data = $this->request->post['manual_shipping'];

        $this->initializer([
            'manualShipping' => 'module/manual_shipping/settings',
        ]);

        $this->language->load('module/manual_shipping');

        if (!$this->validateShipment()) {
            $response['success'] = '0';
            $response['errors'] = $this->error;

            $this->response->setOutput(json_encode($response));

            return;
        }

        require_once(DIR_APPLICATION . "controller/sale/order.php");
        $orderController = new ControllerSaleOrder($this->registry);
        $old_value = $orderController->getAllOrderInfo($data['order_id']);

        foreach ($data['products'] as $product){
            $insertData['product_id'] = $product;
            $insertData['shipping_gateway_id'] = $data['gateway'];
            $insertData['order_id'] = $data['order_id'];
            $this->manualShipping->insertShipmentData($insertData);
        }
        // add data to log_history
        $this->load->model('setting/audit_trail');
        $pageStatus = $this->model_setting_audit_trail->getPageStatus("order");
        if($pageStatus){
            $log_history['action'] = 'updateManualShipping';
            $log_history['reference_id'] = $data['order_id'];
            $log_history['old_value'] = json_encode($old_value,JSON_UNESCAPED_UNICODE);
            $log_history['new_value'] = json_encode($orderController->getAllOrderInfo($data['order_id']),JSON_UNESCAPED_UNICODE);
            $log_history['type'] = 'order';
            $this->load->model('loghistory/histories');
            $this->model_loghistory_histories->addHistory($log_history);
        }


        $response['success_msg'] = $this->language->get('message_shipment_created');

        $response['success'] = '1';

        $this->response->setOutput(json_encode($response));

        return;
    }
    private function validateShipment()
    {
        $postData = $this->request->post['manual_shipping'];
        $this->language->load('module/manual_shipping');


        if (  !isset($postData['products']) || !is_array($postData['products']) )
        {
            $this->error = $this->language->get('error_product');
        }

        if (  !isset($postData['gateway']) && empty($postData['gateway']) )
        {
            $this->error = $this->language->get('error_gateway');
        }

        return $this->error ? false : true;
    }

    public function updateSettings()
    {
        if (
            $this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)
        ) {
            return 'error';
        }

        $data = $this->request->post['manual_shipping'];

        $this->initializer([
            'manualShipping' => 'module/manual_shipping/settings',
        ]);

        $this->language->load('module/manual_shipping');

        if (!$this->manualShipping->validate($data)) {
            $response['success'] = '0';
            $response['errors'] = $this->manualShipping->getErrors();

            $this->response->setOutput(json_encode($response));

            return;
        }

        $this->manualShipping->updateSettings(array_merge($this->manualShipping->getSettings(), $data));

        $response['success_msg'] = $this->language->get('message_settings_updated');

        $response['success'] = '1';

        $this->response->setOutput(json_encode($response));

        return;
    }

    public function list()
    {
        $this->load->language('module/manual_shipping');

        $this->initializer([
            'manualShipping' => 'module/manual_shipping/gateways',
        ]);

        $request = $_REQUEST;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        $start = $request['start'] ?: 0;
        $length = $request['length'] ?: 10;

        $columns = [];
        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];

        $return = $this->manualShipping->getCompactShippingGateways([
            'start' => $start,
            'limit' => $length,
            'search' => $search,
            'language_id' => $this->config->get('config_language_id')
        ]);

        $records = $return['data'];
        $totalData = $return['total'];
        $totalFiltered = $return['totalFiltered'];

        $json_data = [
            "draw" => intval($request['draw']),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => $totalFiltered,
            "data" => $records,
        ];

        $this->response->setOutput(json_encode($json_data));
        return;
    }

    public function get()
    {
        if (
            isset($this->request->get['id']) == false ||
            preg_match('#^[0-9]+$#', $this->request->get['id']) == false
        ) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'INVALID_REQUEST',
                'errors' => ['Invalid request'],
            ]));

            return;
        }

        $this->initializer([
            'manualShipping' => 'module/manual_shipping/gateways',
        ]);

        $msg = $this->manualShipping->getCompactShippingGatewayById($this->request->get['id']);

        $this->response->setOutput(json_encode([
            'status' => 'OK',
            'data' => array_column($msg, null, 'language_id')
        ]));
        return;
    }

    public function dtDelete()
    {
        $this->load->model('module/manual_shipping/settings');
        $this->language->load('module/manual_shipping');

        if(isset($this->request->post["selected"])) {
            $ids = $this->request->post["selected"];

            foreach ($ids as $id) {
                $this->model_module_manual_shipping_settings->deleteShipment($id);
            }
            $result_json['success'] = '1';
            $result_json['success_msg'] = $this->language->get('text_bulkdelete_success');
        } else {
            $result_json['success'] = '0';
            $result_json['error'] = $this->language->get('text_bulkdelete_error');
        }

        $this->response->setOutput(json_encode($result_json));
        return;
    }

    public function install()
    {
        $this->initializer(['module/manual_shipping/settings']);
        $this->settings->install();
    }

    public function uninstall()
    {
        $this->initializer(['module/manual_shipping/settings']);
        $this->settings->uninstall();
    }

    public function store()
    {
        $data = $this->request->post['mp'];
        $dataOfGateWay=$this->request->post['msGateway'];

        $this->initializer([
            'msGateways' => 'module/manual_shipping/gateways',
            'ms' => 'module/manual_shipping/settings',
        ]);

        if (!$this->ms->validate($data)) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'INVALID_CREDENTIALS',
                'errors' => $this->ms->getErrors()
            ]));

            return;
        }

        if ($id = $this->msGateways->insertShippingGateway($dataOfGateWay)) {
            $this->msGateways->insertShippingGatewayDescription($id, $data);

            $this->response->setOutput(json_encode([
                'status' => 'OK',
                'id' => $id,
            ]));

            return;
        }

        $this->response->setOutput(json_encode([
            'status' => 'ERR',
            'error' => 'UNEXPECTED_ERROR',
            'errors' => ['Unexpected error'],
        ]));
    }

    public function update()
    {
        if (
            isset($this->request->get['id']) == false ||
            preg_match('#^[0-9]+$#', $this->request->get['id']) == false
        ) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'INVALID_REQUEST',
                'errors' => ['Invalid request'],
            ]));

            return;
        }

        $data = $this->request->post['mp'];
        $dataOfGateWay = $this->request->post['msGateway'];
        $id = $this->request->get['id'];

        $this->initializer([
            'msGateways' => 'module/manual_shipping/gateways',
            'ms' => 'module/manual_shipping/settings',
        ]);

        if (!$this->ms->validate($data)) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'INVALID_CREDENTIALS',
                'errors' => $this->ms->getErrors()
            ]));

            return;
        }

        if (!$msg = $this->msGateways->getCompactShippingGatewayById($id)) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'UNDEFINED_ROW',
                'errors' => ['Undefined gateway'],
            ]));

            return;
        }

        $msgs = array_column($msg, null, 'language_id');
        foreach ($data as $key => $value) {
            if (isset($msgs[$key])) {
                $_msg = $msgs[$key];
                $msgs[$key] = array_merge($msgs[$key], $value);
            }
        }
        
        if ($this->msGateways->updateShippingGateway($id, $dataOfGateWay)) {
            $this->msGateways->updateShippingGatewayDescription($id, $data);
            $this->response->setOutput(json_encode([
                'status' => 'OK',
                'id' => $id,
                'data' => $msgs,
            ]));

            return;
        }

        $this->response->setOutput(json_encode([
            'status' => 'ERR',
            'error' => 'UNEXPECTED_ERROR',
            'errors' => ['Unexpected error'],
        ]));
    }

    public function destroy()
    {
        if (
            isset($this->request->post['id']) == false ||
            preg_match('#^[0-9]+$#', $this->request->post['id']) == false
        ) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'INVALID_REQUEST',
                'errors' => ['Invalid request'],
            ]));

            return;
        }

        $id = $this->request->post['id'];

        $this->initializer([
            'msGateways' => 'module/manual_shipping/gateways',
            'msgOrder' => 'module/manual_shipping/order',
        ]);

        if (!$msg = $this->msGateways->getCompactShippingGatewayById($id)) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'UNDEFINED_ROW',
                'errors' => ['Undefined gateway'],
            ]));

            return;
        }

        $orders = $this->msgOrder->getOrdersByManualShippingGatewayId($id);

        if ($orders && (!isset($this->request->post['_h']) || $this->request->post['_h'] != 1)) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'INVALID_CONSTRAINTS',
                'errors' => [''],
            ]));

            return;
        }

        if ($this->msGateways->deleteShippingGateway($id)) {
            if (isset($this->request->post['_h']) && (int)$this->request->post['_h'] == 1) {
                $this->msgOrder->nullOrderManualShippingGateways($id);
            }

            $this->response->setOutput(json_encode([
                'status' => 'OK',
            ]));

            return;
        }

        $this->response->setOutput(json_encode([
            'status' => 'ERR',
            'error' => 'UNEXPECTED_ERROR',
            'errors' => ['Unexpected error'],
        ]));
    }
}
