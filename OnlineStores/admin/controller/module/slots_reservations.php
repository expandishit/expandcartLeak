<?php

class ControllerModuleSlotsReservations extends Controller
{
    public function index()
    {
        $this->initializer([
            'module/slots_reservations/settings'
        ]);

        $this->load->language('module/slots_reservations');

        $settings = $this->config->get('slots_reservations');

        $data['status'] = $settings['status'];
        $data['notify_by_mail'] = $settings['notify_by_mail'];
        $data['notify_by_sms'] = $settings['notify_by_sms'];
        $data['required_fields'] = $settings['required_fields'];
        $data['days'] = $settings['days'];

        $this->document->setTitle($this->language->get('slots_reservations_heading_title'));

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_modules'),
            'href'      => $this->url->link(
                'marketplace/home',
                '',
                'SSL'
            ),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('slots_reservations_heading_title'),
            'href'      => $this->url->link('module/slots_reservations', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->template = 'module/slots_reservations/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $data['links'] = [
            'submit' => $this->url->link(
                'module/slots_reservations/updateSettings',
                '',
                'SSL'
            ),
            'cancel' => $this->url->link('module/slots_reservations', '', 'SSL'),
        ];

        $this->data = array_merge($this->data, $data);

        $this->response->setOutput($this->render());
    }

    public function updateSettings()
    {
        if (
            $this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)
        ) {
            return 'error';
        }

        $this->initializer([
            'module/slots_reservations/settings'
        ]);

        $basic = $this->request->post['slots_reservations'];
        $requiredFields = $this->request->post['required_fields'];
        $days = $this->request->post['days'];

        if ($this->settings->validateForm($this->request->post) == false) {
            return $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'INVALID_CREDENTIALS',
                'errors' => $this->settings->getErrors()
            ]));
        }

        $data = array_merge([], $basic, ['required_fields' => array_filter($requiredFields)], ['days' => $days]);

        $this->settings->updateSettings(['slots_reservations' => $data]);

        $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_settings_success');

        $result_json['success'] = '1';

        $this->response->setOutput(json_encode([
            'status' => 'OK',
            'message' => $this->language->get('text_settings_success')
        ]));

        return;

    }

    public function install()
    {
        $this->initializer([
            'module/slots_reservations/settings'
        ]);

        $this->settings->install();
    }

    public function uninstall()
    {
        $this->initializer([
            'module/slots_reservations/settings'
        ]);

        $this->settings->uninstall();
    }

    public function browse()
    {
        $this->load->language('module/slots_reservations');

        $this->document->setTitle($this->language->get('slots_reservations_grid'));

        $this->template = 'module/slots_reservations/browse.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->data = [];

        $this->response->setOutput($this->render());
    }

    public function list()
    {
        $request = $_REQUEST;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        $start = $request['start'] ?: 0;
        $length = $request['length'] ?: 10;

        $columns = array(
            0 => 'reservation_date',
            1 => 'slot',
            2 => 'count',
            4 => 'created_at'
        );

        $orderColumn = $columns[$request['order'][0]['column']] ?: 'reservation_date, slot';
        $orderType = $request['order'][0]['dir'] ?: 'ASC';

        $this->initializer([
            'module/slots_reservations/settings'
        ]);

        $return = $this->settings->listReservations($start, $length, $search, $orderColumn, $orderType);

        $records = $return['data'];
        $totalData = $return['total'];
        $totalFiltered = $return['totalFiltered'];

        $response = [
            "draw" => intval($request['draw']),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => $totalFiltered,
            "data" => $records,
        ];

        $this->response->setOutput(json_encode($response));
        return;
    }

    public function reserved()
    {
        $request = $_REQUEST;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        $start = $request['start'] ?: 0;
        $length = $request['length'] ?: 10;

        $columns = array(
            0 => 'reservation_date',
            1 => 'slot',
            2 => 'count',
            4 => 'created_at'
        );

        if (isset($this->request->get['slot_id']) == false || (int)$this->request->get['slot_id'] < 1) {
            $this->response->setOutput(json_encode([
                "draw" => intval($request['draw']),
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => [],
            ]));
        }

        $orderColumn = $columns[$request['order'][0]['column']] ?: 'reservation_date, slot';
        $orderType = $request['order'][0]['dir'] ?: 'ASC';

        $this->initializer([
            'module/slots_reservations/settings'
        ]);

        $slotId = $this->request->get['slot_id'];

        $return = $this->settings->listReservedSlots($slotId, $start, $length, $search, $orderColumn, $orderType);

        $records = $return['data'];
        $totalData = $return['total'];
        $totalFiltered = $return['totalFiltered'];

        return $this->response->setOutput(json_encode([
            "draw" => intval($request['draw']),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => $totalFiltered,
            "data" => $records,
        ]));
    }
}
