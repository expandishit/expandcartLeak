<?php

class ControllerSaleVoucher extends Controller
{
    private $error = array();

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
        $this->language->load('sale/voucher');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('sale/voucher');

        $this->getList();
    }

    public function insert()
    {
        $this->language->load('sale/voucher');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('sale/voucher');

        if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {

            if ( ! $this->validateForm() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
                
                $this->response->setOutput(json_encode($result_json));
                
                return;
            }

            $voucher_id = $this->model_sale_voucher->addVoucher($this->request->post);

            // add data to log_history
            $this->load->model('setting/audit_trail');
            $pageStatus = $this->model_setting_audit_trail->getPageStatus("voucher");
            if($pageStatus){
                $log_history['action'] = 'add';
                $log_history['reference_id'] = $voucher_id;
                $log_history['old_value'] = NULL;
                $log_history['new_value'] = json_encode($this->request->post);
                $log_history['type'] = 'voucher';
                $this->load->model('loghistory/histories');
                $this->model_loghistory_histories->addHistory($log_history);
            }


            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');


            $result_json['success'] = '1';
            $result_json['redirect'] = '1';
            $result_json['to'] = (string) $this->url->link('sale/voucher', '', 'SSL');
            
            $this->response->setOutput(json_encode($result_json));
            
            return;
        }

        $this->data['links'] = [
            'submit' => $this->url->link('sale/voucher/insert', '', 'SSL'),
            'cancel' => $this->url->link('sale/voucher', '', 'SSL')
        ];

        $this->data['cancel'] = $this->url->link('sale/voucher', '', 'SSL');

        $this->getForm();
    }

    public function update()
    {
        $this->language->load('sale/voucher');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('sale/voucher');

        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            if($this->validateForm()){
                if (!isset($this->request->post['status'])) {
                    $this->request->post['status'] = '0';
                }

                $oldValue = $this->model_sale_voucher->getVoucher($this->request->get['voucher_id']);

                $this->model_sale_voucher->editVoucher($this->request->get['voucher_id'], $this->request->post);

                // add data to log_history
                $this->load->model('setting/audit_trail');
                $pageStatus = $this->model_setting_audit_trail->getPageStatus("voucher");
                if($pageStatus){
                    $log_history['action'] = 'update';
                    $log_history['reference_id'] = $this->request->get['voucher_id'];
                    $log_history['old_value'] = json_encode($oldValue);
                    $log_history['new_value'] = json_encode($this->request->post);
                    $log_history['type'] = 'voucher';
                    $this->load->model('loghistory/histories');
                    $this->model_loghistory_histories->addHistory($log_history);
                }


                $result_json['success_msg'] = $this->language->get('text_success');

                $result_json['success'] = '1';

                $this->response->setOutput(json_encode($result_json));
                return;
            }else{

                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;

                $this->response->setOutput(json_encode($result_json));

                return;
            }

        }

        $this->data['links'] = [
            'submit' => $this->url->link(
                'sale/voucher/update',
                'voucher_id=' . $this->request->get['voucher_id'],
                'SSL'
            ),
            'history' => $this->url->link(
                'sale/voucher/history',
                'voucher_id=' . $this->request->get['voucher_id'],
                'SSL'
            ),
            'cancel' => $this->url->link('sale/voucher', '', 'SSL')
        ];

        $this->getForm();
    }

    public function delete()
    {
        $this->language->load('sale/voucher');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('sale/voucher');

        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            $this->load->model('loghistory/histories');
            $this->load->model('setting/audit_trail');
            $pageStatus = $this->model_setting_audit_trail->getPageStatus("voucher");

            foreach ($this->request->post['selected'] as $voucher_id) {
                if($pageStatus) {
                    $oldValue = $this->model_sale_voucher->getVoucher($voucher_id);
                    $log_history['action'] = 'delete';
                    $log_history['reference_id'] = $voucher_id;
                    $log_history['old_value'] = json_encode($oldValue);
                    $log_history['new_value'] = NULL;
                    $log_history['type'] = 'voucher';
                    $this->model_loghistory_histories->addHistory($log_history);
                }

                $this->model_sale_voucher->deleteVoucher($voucher_id);
            }

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->redirect($this->url->link('sale/voucher', '', 'SSL'));
        }

        $this->getList();
    }

    protected function getList()
    {
        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('sale/voucher', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['links']['voucherthemes'] = $this->url->link('sale/voucher_theme', '', 'SSL');

        $this->data['vouchers'] = array();

        if (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else {
            $this->data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $this->data['success'] = '';
        }

        $this->data['sort'] = $sort;
        $this->data['order'] = $order;


        $total = $this->model_sale_voucher->getTotalVouchers();

        if ($total == 0){
            $this->template = 'sale/voucher/empty.expand';
        }else{
            $this->template = 'sale/voucher/list.expand';

        }

        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
    }

    protected function getForm()
    {
        $this->data['errors'] = null;
        if (count($this->error) > 0) {
            $this->data['errors'] = $this->error;
        }

        if (count($this->request->post) > 0) {
            $this->data['data'] = array_merge($this->data['data'], $this->request->post);
        }

        if (isset($this->request->get['voucher_id'])) {
            $this->data['voucher_id'] = $this->request->get['voucher_id'];
        } else {
            $this->data['voucher_id'] = 0;
        }

        if (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else {
            $this->data['error_warning'] = '';
        }

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('sale/voucher', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text' => !isset($this->request->get['voucher_id']) ? $this->language->get('breadcrumb_insert') : $this->language->get('breadcrumb_update'),
            'href' => $this->url->link('sale/voucher', '', 'SSL'),
            'separator' => ' :: '
        );

        if (isset($this->request->get['voucher_id']) && (!$this->request->server['REQUEST_METHOD'] != 'POST')) {
            $voucher_info = $this->model_sale_voucher->getVoucher($this->request->get['voucher_id']);
        }

        $this->data['token'] = null;

        if (isset($this->request->post['code'])) {
            $this->data['code'] = $this->request->post['code'];
        } elseif (!empty($voucher_info)) {
            $this->data['code'] = $voucher_info['code'];
        } else {
            $this->data['code'] = '';
        }

        if (isset($this->request->post['from_name'])) {
            $this->data['from_name'] = $this->request->post['from_name'];
        } elseif (!empty($voucher_info)) {
            $this->data['from_name'] = $voucher_info['from_name'];
        } else {
            $this->data['from_name'] = '';
        }

        if (isset($this->request->post['from_email'])) {
            $this->data['from_email'] = $this->request->post['from_email'];
        } elseif (!empty($voucher_info)) {
            $this->data['from_email'] = $voucher_info['from_email'];
        } else {
            $this->data['from_email'] = '';
        }

        if (isset($this->request->post['to_name'])) {
            $this->data['to_name'] = $this->request->post['to_name'];
        } elseif (!empty($voucher_info)) {
            $this->data['to_name'] = $voucher_info['to_name'];
        } else {
            $this->data['to_name'] = '';
        }

        if (isset($this->request->post['to_email'])) {
            $this->data['to_email'] = $this->request->post['to_email'];
        } elseif (!empty($voucher_info)) {
            $this->data['to_email'] = $voucher_info['to_email'];
        } else {
            $this->data['to_email'] = '';
        }

        $this->load->model('sale/voucher_theme');

        $this->data['voucher_themes'] = $this->model_sale_voucher_theme->getVoucherThemes();

        if (isset($this->request->post['voucher_theme_id'])) {
            $this->data['voucher_theme_id'] = $this->request->post['voucher_theme_id'];
        } elseif (!empty($voucher_info)) {
            $this->data['voucher_theme_id'] = $voucher_info['voucher_theme_id'];
        } else {
            $this->data['voucher_theme_id'] = '';
        }

        if (isset($this->request->post['message'])) {
            $this->data['message'] = $this->request->post['message'];
        } elseif (!empty($voucher_info)) {
            $this->data['message'] = $voucher_info['message'];
        } else {
            $this->data['message'] = '';
        }

        if (isset($this->request->post['amount'])) {
            $this->data['amount'] = $this->request->post['amount'];
        } elseif (!empty($voucher_info)) {
            $this->data['amount'] = $voucher_info['amount'];
        } else {
            $this->data['amount'] = '';
        }

        if (isset($this->request->post['status'])) {
            $this->data['status'] = $this->request->post['status'];
        } elseif (!empty($voucher_info)) {
            $this->data['status'] = $voucher_info['status'];
        } else {
            $this->data['status'] = 1;
        }

        $this->template = 'sale/voucher/form.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
    }

    protected function validateForm()
    {
        if ( !$this->user->hasPermission('modify', 'sale/voucher') )
        {
            $this->error['error'] = $this->language->get('error_permission');
        }

        if ((utf8_strlen($this->request->post['code']) < 3) || (utf8_strlen($this->request->post['code']) > 10)) {
            $this->error['code'] = $this->language->get('error_code');
        }

        $voucher_info = $this->model_sale_voucher->getVoucherByCode($this->request->post['code']);

        if ($voucher_info) {
            if (!isset($this->request->get['voucher_id'])) {
                $this->error['warning'] = $this->language->get('error_exists');
            } elseif ($voucher_info['voucher_id'] != $this->request->get['voucher_id']) {
                $this->error['warning'] = $this->language->get('error_exists');
            }
        }

        if (
            (utf8_strlen($this->request->post['to_name']) < 1) ||
            (utf8_strlen($this->request->post['to_name']) > 64)
        ) {
            $this->error['to_name'] = $this->language->get('error_to_name');
        }

        if (
            (utf8_strlen($this->request->post['to_email']) > 96) ||
            !preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $this->request->post['to_email'])
        ) {
            $this->error['to_email'] = $this->language->get('error_email');
        }

        if (
            (utf8_strlen($this->request->post['from_name']) < 1) ||
            (utf8_strlen($this->request->post['from_name']) > 64)
        ) {
            $this->error['from_name'] = $this->language->get('error_from_name');
        }

        if (
            (utf8_strlen($this->request->post['from_email']) > 96) ||
            !preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $this->request->post['from_email'])
        ) {
            $this->error['from_email'] = $this->language->get('error_email');
        }

        if ($this->request->post['amount'] < 1) {
            $this->error['amount'] = $this->language->get('error_amount');
        }

        if ( $this->error && !isset($this->error['error']) )
        {
            if(!isset($this->error['warning'] )){
                $this->error['warning'] = $this->language->get('error_warning');
            }
        }
        
        return $this->error ? false : true;
    }

    protected function validateDelete()
    {
        if (!$this->user->hasPermission('modify', 'sale/voucher')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        $this->load->model('sale/order');

        foreach ($this->request->post['selected'] as $voucher_id) {
            $order_voucher_info = $this->model_sale_order->getOrderVoucherByVoucherId($voucher_id);

            if ($order_voucher_info) {
                $this->error['warning'] = sprintf(
                    $this->language->get('error_order'),
                    $this->url->link('sale/order/info',
                        'order_id=' . $order_voucher_info['order_id'],
                        'SSL'
                    )
                );

                break;
            }
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    public function history()
    {
        $this->language->load('sale/voucher');

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('sale/voucher', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('breadcrumb_update'),
            'href' => $this->url->link('sale/voucher', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->load->model('sale/voucher');

        $this->data['histories'] = array();

        $results = $this->model_sale_voucher->getVoucherHistories($this->request->get['voucher_id']);

        foreach ($results as $result) {
            $this->data['histories'][] = array(
                'order_id' => $result['order_id'],
                'customer' => $result['customer'],
                'amount' => $this->currency->format($result['amount'], $this->config->get('config_currency')),
                'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added']))
            );
        }

        $this->data['links'] = [
            'update' => $this->url->link(
                'sale/voucher/update',
                'voucher_id=' . $this->request->get['voucher_id'],
                'SSL'
            ),
            'cancel' => $this->url->link('sale/voucher', '', 'SSL')
        ];

        $this->template = 'sale/voucher/history.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
    }

    public function send()
    {
        $this->language->load('sale/voucher');

        $json = array();

        if (!$this->user->hasPermission('modify', 'sale/voucher')) {
            $json['error'] = $this->language->get('error_permission');
            $json['title'] = $this->language->get('error_title');
        } elseif (isset($this->request->get['voucher_id'])) {
            $this->load->model('sale/voucher');

            $this->model_sale_voucher->sendVoucher($this->request->get['voucher_id']);

            $json['success'] = $this->language->get('text_sent_message');
            $json['title'] = $this->language->get('text_sent_title');
        }

        $this->response->setOutput(json_encode($json));
    }

    public function dtHandler()
    {
        $this->load->model('sale/voucher');
        $request = $this->request->request;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        $start = $request['start'];
        $length = $request['length'];

        $columns = array(
            0 => 'voucher_id',
            1 => 'code',
            2 => 'from_name',
            3 => 'from_email',
            4 => 'to_name',
            5 => 'to_email',
            6 => 'theme',
            7 => 'amount',
            8 => 'status',
            9 => 'date_added',
        );

        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];

        $return = $this->model_sale_voucher->dtHandler([
            'sort' => $orderColumn,
            'order' => $orderType,
            'start' => $start,
            'limit' => $length
        ]);

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

    public function dtDelete()
    {
        $this->load->model('sale/voucher');

        if (isset($this->request->post['selected']) && $this->validateDelete()) {

            $this->load->model('loghistory/histories');
            $this->load->model('setting/audit_trail');
            $pageStatus = $this->model_setting_audit_trail->getPageStatus("voucher");

            foreach ($this->request->post['selected'] as $id) {
                if($pageStatus) {
                    $oldValue = $this->model_sale_voucher->getVoucher($id);
                    $log_history['action'] = 'delete';
                    $log_history['reference_id'] = $id;
                    $log_history['old_value'] = json_encode($oldValue);
                    $log_history['new_value'] = NULL;
                    $log_history['type'] = 'voucher';
                    $this->model_loghistory_histories->addHistory($log_history);
                }
                $this->model_sale_voucher->deleteVoucher($id);
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

    public function dtUpdateStatus()
    {
        $this->load->model("sale/voucher");
        if (isset($this->request->post["id"]) && isset($this->request->post["status"])) {
            $id = $this->request->post["id"];
            $status = $this->request->post["status"];

            $voucher = $this->model_sale_voucher->getVoucher($id);
            $voucher["status"] = $status;
            $this->model_sale_voucher->editVoucher($id, $voucher);

            // add data to log_history
            $this->load->model('setting/audit_trail');
            $pageStatus = $this->model_setting_audit_trail->getPageStatus("voucher");
            if($pageStatus) {
                $log_history['action'] = 'update';
                $log_history['reference_id'] = $id;
                $log_history['old_value'] = json_encode($voucher);
                $voucher['status'] = $status;
                $log_history['new_value'] = json_encode($voucher);
                $log_history['type'] = 'voucher';
                $this->load->model('loghistory/histories');
                $this->model_loghistory_histories->addHistory($log_history);
            }

            $result_json['success'] = '1';
            $result_json['success_msg'] = $this->language->get('text_langstatus_success');
        } else {
            $result_json['success'] = '0';
            $result_json['error'] = $this->language->get('text_langstatus_error');
        }

        $this->response->setOutput(json_encode($result_json));
        return;
    }
}
