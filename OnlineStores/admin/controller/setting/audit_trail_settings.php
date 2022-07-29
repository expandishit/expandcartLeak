<?php

class ControllerSettingAuditTrailSettings extends Controller
{
    private $errors = array();

    public function index()
    {
        $this->language->load('setting/setting');
        $this->language->load('setting/audit_trail');

        $this->document->setTitle($this->language->get('text_audit_trail'));

        // define default values
        $this->data['filter_date_start'] = date('y/m/d', strtotime(date('Y') . '-' . date('m') . '-01'));
        $this->data['filter_date_end'] = date('y/m/d');

        $this->data['entry_date_start'] = $this->language->get('entry_date_start');
        $this->data['entry_date_end'] = $this->language->get('entry_date_end');

        $this->data['button_filter'] = $this->language->get('button_filter');

        $this->load->model('setting/audit_trail');

        $this->data['breadcrumbs'] = array(
            array('text' => $this->language->get('text_home'), 'href' => $this->url->link('common/home')),
            array('text' => $this->language->get('heading_title'), 'href' => $this->url->link('setting/audit_trail_settings'))
        );


        $this->data['settingsData'] = $this->model_setting_audit_trail->getSettings();

        $this->data['pages'] = [
            "coupon",
            "voucher",
            "product",
            "customer",
            "lang_settings",
            "order",
            "currency",
            "category",
            "general_setting",
            "advanced_setting",
            "users_permissions",
            "groups_permissions"
        ];

        $this->data['logActions'] =
            [
                ['value' => 'add', 'text'  => $this->language->get('text_add')],
                ['value' => 'update', 'text'  => $this->language->get('text_update')],
                ['value' => 'delete', 'text'  => $this->language->get('text_delete')],
                ['value' => 'updateBalance', 'text'  => $this->language->get('text_update_balance')],
                ['value' => 'updateReward', 'text'  => $this->language->get('text_update_reward')],
                ['value' => 'updateOrderStatus', 'text'  => $this->language->get('text_order_status')],
                ['value' => 'updateManualShipping', 'text'  => $this->language->get('text_manual_shipping')],         
                ['value' => 'updateTaxOptions', 'text'  => $this->language->get('text_tax_options')],
                ['value' => 'updateVoucherOptions', 'text'  => $this->language->get('text_voucher_options')],
                ['value' => 'updateInterfaceCustomization', 'text'  => $this->language->get('text_interface_custom')],
                ['value' => 'updateCustomCode', 'text'  => $this->language->get('text_custom_code')],
                ['value' => 'exportOrder', 'text'  => $this->language->get('text_export_order')],
                ['value' => 'login', 'text'  => $this->language->get('text_login')]
            ]; 

        $this->data['logTypes'] =
            [
                ['value' => 'coupon', 'text'  => $this->language->get('text_coupon')],
                ['value' => 'voucher', 'text'  => $this->language->get('text_voucher')],
                ['value' => 'product', 'text'  => $this->language->get('text_product')],
                ['value' => 'customer', 'text'  => $this->language->get('text_customer')],
                ['value' => 'lang_settings', 'text'  => $this->language->get('text_lang_settings')],
                ['value' => 'order', 'text'  => $this->language->get('text_order')],
                ['value' => 'currency', 'text'  => $this->language->get('text_currency')],
                ['value' => 'category', 'text'  => $this->language->get('text_category')],
                ['value' => 'general_setting', 'text'  => $this->language->get('text_general_setting')],
                ['value' => 'advanced_setting', 'text'  => $this->language->get('text_advanced_setting')],
                ['value' => 'users_permissions', 'text'  => $this->language->get('text_users_permissions')],
                ['value' => 'groups_permissions', 'text'  => $this->language->get('text_groups_permissions')]
            ];

        $this->data['selectedPages'] = (isset($this->data['settingsData']['pages'])) ? $this->data['settingsData']['pages'] : [];
        
        $this->data['action'] = $this->url->link('setting/audit_trail_settings/updateSettings');

        $this->data['ajaxAction'] = $this->url->link('setting/audit_trail_settings/ajaxResponse', '', 'SSL');

        $this->data['cancel'] = $this->url->link('common/home');

        $this->data['records'] = $this->url->link('loghistory/histories');

        $this->template = 'setting/audit_trail_settings.expand';
        $this->base = "common/base";

        $this->response->setOutput($this->render_ecwig());
    }

    public function updateSettings()
    {
        if(PRODUCTID == 3){
            $this->redirect(
                $this->url->link('error/permission', '', 'SSL')
            );
            exit();
        }
        // load Language File
        $this->language->load('setting/setting');

        if ($this->request->server['REQUEST_METHOD'] != 'POST') {
            $this->response->setOutput(json_encode([
                'success' => 0,
                'errors' => [
                    'invalid request'
                ]
            ]));
            return;
        }

        $data = $this->request->post['audit_trail'];

        $this->load->model('setting/audit_trail');

        $this->model_setting_audit_trail->updateSettings(['audit_trail' => $data]);

        $this->response->setOutput(json_encode([
            'success' => 1,
            'success_msg' => $this->language->get('text_settings_success')
        ]));
        return;
    }


    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'setting/audit_trail')) {
            $this->error['error'] = $this->language->get('error_permission');
        }

        if ($this->error && !isset($this->error['error'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    public function ajaxResponse()
    {

        $requestData = $_REQUEST;
        $columns = array(
            0 => 'log_history_id',
            1 => 'username',
            2 => 'email',
            3 => 'action',
            4 => 'type',
            5 => 'date_added'

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

        if (isset($requestData['log_action'])) {
            $filter_log_action = $requestData['log_action'];
        } else {
            $filter_log_action = 'all';
        }

        if (isset($requestData['log_type'])) {
            $filter_type = $requestData['log_type'];
        } else {
            $filter_type = 'all';
        }


        $this->load->model('setting/audit_trail');


        $data = array(
            'filter_date_start' => $filter_date_start,
            'filter_date_end' => $filter_date_end,
            'filter_log_action' => $filter_log_action,
            'filter_type' => $filter_type,
            'search' => !empty($requestData['search']['value']) ? $requestData['search']['value'] : null,
        );

        $totalData = $this->model_setting_audit_trail->getTotalLogs($data);

        $results = $this->model_setting_audit_trail->getLogsDataTable($data, $requestData, $columns);


        $alt = array();
        foreach ($results['data'] as $result) {
            $alt[] = [
                'log_id' => $result['log_history_id'],
                'username' => $result['username'],
                'email' => $result['email'],
                'type' => $result['type'],
                'action' => $result['action'],
                'date_added' => $result['date_added']
            ];
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
