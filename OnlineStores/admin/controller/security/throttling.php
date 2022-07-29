<?php

class ControllerSecurityThrottling extends Controller
{
    public function index()
    {
        $this->language->load('security/throttling');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('security/throttling', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['links'] = [
            'submit' => $this->url->link('security/throttling/updateSetting')
        ];

        $this->data['setting'] = $this->config->get('throttling');

        $this->template = 'security/throttling.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->data['config_password'] = $this->config->get('config_password');
        
        $this->data['enable_login_v2'] = defined('LOGIN_MODE') && LOGIN_MODE === 'identity' && $this->identity->isStoreOnWhiteList();

        $this->response->setOutput($this->render_ecwig());
    }

    public function updateSetting()
    {
        if (
            $this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)
        ) {
            return 'error';
        }

        unset($this->request->post['undefined']);

        $this->load->model('setting/setting');
        $this->model_setting_setting->insertUpdateSetting('security', ['throttling' => $this->request->post]);

        $result_json['success_msg'] = $this->language->get('text_settings_success');

        $result_json['success'] = '1';

        $this->response->setOutput(json_encode($result_json));

        return;
    }

    public function bannedList()
    {
        $this->initializer(['security/throttling']);

        $request = $_REQUEST;

        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        $start = $request['start'];
        $length = $request['length'];

        $columns = array(
            'id',
            'ipv4',
            'ban_status',
            'attempts',
            'recaptcha_status',
            'created_at',
        );

        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];

        $ips = $this->throttling->getIpsForDatatables($filter);

        $json_data = array(
            "draw" => intval($request['draw']),
            "recordsTotal" => intval($ips['total']),
            "recordsFiltered" => $ips['totalFiltered'],
            "data" => $ips['data']
        );
        $this->response->setOutput(json_encode($json_data));
        return;
    }

    public function delete()
    {
        $this->initializer(['security/throttling']);

        if (isset($this->request->post['selected'])) {

            foreach ($this->request->post['selected'] as $id) {
                $this->throttling->deleteById($id);
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
}
