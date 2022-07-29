<?php

class ControllerModuleDeliverySlot extends Controller
{
    private $settings;
    protected $errors = [];

    protected $error ;

    public function index()
    {

        $this->load->model('module/delivery_slot/settings');
        $this->language->load('module/delivery_slot');

        $this->document->setTitle($this->language->get('delivery_slot_heading_title'));

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_modules'),
            'href'      => $this->url->link(
                'marketplace/home',
                '',
                'SSL'
            ),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('delivery_slot_heading_title'),
            'href'      => $this->url->link('module/delivery_slot', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->document->addScript('view/javascript/popupwindow/timepicker.js');


        $this->data['entry_status'] = $this->language->get('entry_status');
        $this->data['text_enabled'] = $this->language->get('text_enabled');
        $this->data['text_disabled'] = $this->language->get('text_disabled');
        $this->data['text_settings'] = $this->language->get('text_settings');

        $this->initializer([
            'statuses' => 'localisation/order_status'
        ]);
        $this->data['order_statuses'] = $this->statuses->getOrderStatuses();

        $this->template = 'module/delivery_slot/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );
        // get app settings
        $this->data['settingsData'] = $this->model_module_delivery_slot_settings->getSettings();

        $this->load->model('localisation/language');
        $this->data['languages'] = $this->model_localisation_language->getLanguages();

        $this->data['action'] = $this->url->link('module/delivery_slot/updateSettings', '', 'SSL');
        $this->data['cancel'] = $this->url->link('marketplace/home', '', 'SSL');


        $this->response->setOutput($this->render());
    }

    public function insert()
    {
        $this->load->model('module/delivery_slot/slots');
        $this->load->model('module/delivery_slot/settings');
        $this->language->load('module/delivery_slot');

        $this->document->setTitle($this->language->get('delivery_slot_heading_title'));


        $this->data['heading_title'] = $this->language->get('delivery_slot_heading_title');


        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('delivery_slot_heading_title'),
            'href'      => $this->url->link('module/delivery_slot',  $url, 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_new_delivery_slot'),
            'href'      => $this->url->link('module/delivery_slot',  $url, 'SSL'),
            'separator' => ' :: '
        );



        $this->document->addScript('view/javascript/popupwindow/timepicker.js');


        $this->data['currentDay'] = isset($this->request->get["day"]) ? $this->request->get["day"] : "Saturday";
        $this->data['day_id'] = isset($this->request->get["day_id"]) ? $this->request->get["day_id"] : 1;

        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
            $url .= '&sort=' . $this->request->get['sort'];
        } else {
            $sort = 'ds_date';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
            $url .= '&order=' . $this->request->get['order'];
        } else {
            $order = 'ASC';
        }
        // get today date
        $todayDate = date("m-d-Y");
        $todayName = date('l', strtotime(date("d-m-Y")));


        if(strtolower($todayName) == strtolower($this->data['currentDay'])){
            $balanceDate = $todayDate;
        }else{
            $next = "next ".$this->data['currentDay'];
            $balanceDate = date("m-d-Y", strtotime($next));
        }

        $data = array(
            'sort'  => $sort,
            'order' => $order,
            'day_id' => $this->data['day_id']
        );


        // get total slots
        $slots_total = $this->model_module_delivery_slot_slots->getTotalSlots($data);
        // get all slotss
        $results = $this->model_module_delivery_slot_slots->getSlots($data);

        $dayes = [
            1 => $this->language->get('entry_saturday'),
            2 => $this->language->get('entry_sunday'),
            3 => $this->language->get('entry_monday'),
            4 => $this->language->get('entry_tuesday'),
            5 => $this->language->get('entry_wednesday'),
            6 => $this->language->get('entry_thursday'),
            7 => $this->language->get('entry_friday')
        ];

        foreach ($results as  $result) {
            $action = array();

            $action[] = array(
                'text' => $this->language->get('text_edit'),
                'href' => $this->url->link('delivery_slot/slots/update', '&slot_id=' . $result['ds_delivery_slot_id'] . $url, 'SSL')
            );

            $resevedOrders = $this->model_module_delivery_slot_slots->getBalance(['balanceDate'=>$balanceDate,'slot_id'=>$result['ds_delivery_slot_id']]);

            $this->data['slots'][] = array(
                'slot_id'           => $result['ds_delivery_slot_id'],
                'day'              => $dayes[$result['ds_day_id']],
                'slot_desc'         => $result['delivery_slot'],
                'total_orders'      => $result['total_orders'],
                'reserved'      => $resevedOrders,
                'balance'  => ($result['total_orders'] - $resevedOrders),
                'action'            => $action
            );
        }


        $this->data['dayes'] = $dayes;
        $this->data['update'] = $this->url->link('module/delivery_slot/update', $url, 'SSL');

        $this->data['action'] = $this->url->link('module/delivery_slot/addNewSlot', '', 'SSL');
        $this->data['cancel'] = $this->url->link('module/delivery_slot', '', 'SSL');
        $this->data['entry_status'] = $this->language->get('entry_status');
        $this->data['text_enabled'] = $this->language->get('text_enabled');
        $this->data['text_disabled'] = $this->language->get('text_disabled');

        $this->template = 'module/delivery_slot/slot/insert.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );
        $this->response->setOutput($this->render());
    }

    public function update()
    {
        $this->load->model('module/delivery_slot/slots');
        $this->language->load('module/delivery_slot');

        $this->document->setTitle($this->language->get('delivery_slot_heading_title'));

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('delivery_slot_heading_title'),
            'href'      => $this->url->link('module/delivery_slot', $url, 'SSL'),
            'separator' => ' :: '
        );


        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('delivery_slot_heading_slots_update'),
            'href'      => $this->url->link('module/delivery_slot', $url, 'SSL'),
            'separator' => ' :: '
        );

        $this->document->addScript('view/javascript/popupwindow/timepicker.js');


        if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {

            if ( ! $this->validateform() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;

                $this->response->setOutput(json_encode($result_json));

                return;
            }

            $updateData = $this->model_module_delivery_slot_slots->editSlot($this->request->get['slot_id'], $this->request->post['delivery_slot']);

            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');
            $result_json['success'] = '1';
            $result_json['success_msg'] = $this->language->get('text_success');
            $this->response->setOutput(json_encode($result_json));

            return;
        }
        // get data
        $slots = $this->model_module_delivery_slot_slots->getSlot($this->request->get['slot_id']);

        $this->data['slot'] = $slots;
        $this->data['status'] = $slots['status'];

        $this->data['action'] = $this->url->link('module/delivery_slot/update?slot_id='.$this->request->get['slot_id'], '', 'SSL');

        $this->data['cancel'] = $this->url->link('module/delivery_slot', '', 'SSL');
        $this->data['entry_status'] = $this->language->get('entry_status');
        $this->data['text_enabled'] = $this->language->get('text_enabled');
        $this->data['text_disabled'] = $this->language->get('text_disabled');
        $this->data['text_settings'] = $this->language->get('text_settings');

        $this->data['heading_title'] = $this->language->get('delivery_slot_heading_title');

        $this->template = 'module/delivery_slot/slot/update.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );
        $this->response->setOutput($this->render());
    }

    public function dtDelete()
    {
        $this->load->model('module/delivery_slot/slots');
        $this->language->load('module/delivery_slot');

        if(isset($this->request->post["selected"])) {
            $ids = $this->request->post["selected"];

            $validate = $this->validateDelete($ids);

            if ( $validate !== true )
            {
                $result_json['success'] = '0';
                $result_json['error'] = $this->language->get( $validate );
                $this->response->setOutput(json_encode($result_json));
                return;
            }

            foreach ($ids as $id) {
                $this->model_module_delivery_slot_slots->deleteSlot($id);
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

    public function addNewSlot(){

        if ( $this->request->post )
        {

            if ( ! $this->validateAjax() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;

                $this->response->setOutput(json_encode($result_json));

                return;
            }
            $this->load->model('module/delivery_slot/slots');
            $this->language->load('module/delivery_slot');

            $addData = $this->model_module_delivery_slot_slots->addSlots($this->request->post['delivery_slot']);

            if($addData){
                $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');
                $result_json['success'] = '1';
                $result_json['success_msg'] = $this->language->get('text_success');
            }else{
                $result_json['success'] = '0';
                $result_json['errors'] = $this->language->get('unexpected_error');
            }

            $this->response->setOutput(json_encode($result_json));

            return;
        }
    }

    protected function validateDelete($ids)
    {
        if (!$this->user->hasPermission('modify', 'delivery_slot/slots'))
        {
            return array('error' => $this->language->get('error_permission'));
        }

        return true;
    }

    private function validateform()
    {
        $this->language->load('module/delivery_slot');

        $postData = $this->request->post['delivery_slot'] ;
        if (!isset($postData['delivery_slot']) || empty($postData['delivery_slot'])) {
            $this->error['delivery_slot'] = $this->language->get('error_delivery_slot_required');
        }

        if (!isset($postData['total_orders']) || empty($postData['total_orders'])) {
            $this->error['total_orders'] = $this->language->get('error_orders_count_required');
        }

        if (!isset($postData['time_start']) || empty($postData['time_start'])) {
            $this->error[] = $this->language->get('error_time_start_required');
        }

        if (!isset($postData['time_end']) || empty($postData['time_end'])) {
            $this->error[] = $this->language->get('error_time_end_required');
        }

        if ($this->error && !isset($this->error['error'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }
        return $this->error ? false : true;
    }

    public function updateSettings()
    {
        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)) {
            $result_json['success'] = '0';
            $result_json['errors'] = 'Invalid Request';
        }else{

            if ( ! $this->validate() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->errors;

                $this->response->setOutput(json_encode($result_json));

                return;
            }

            $this->load->model('module/delivery_slot/settings');
            $this->language->load('module/delivery_slot');

            $data = $this->request->post['delivery_slot'];

            $cut_off_notes = $this->request->post['cut_off_notes'] ? $this->request->post['cut_off_notes'] : [];

            $this->model_module_delivery_slot_settings->updateSettings(['delivery_slot' => $data, 'cut_off_notes' => $cut_off_notes]);

            $this->session->data['success'] = $this->language->get('text_settings_success');

            $this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success');
            $result_json['success'] = '1';
        }
        $this->response->setOutput(json_encode($result_json));
        return;
    }

    private function validateAjax()
    {
        $this->language->load('module/delivery_slot');

        $postData = $this->request->post['delivery_slot'] ;
        if (!isset($postData['delivery_slot']) || empty($postData['delivery_slot'])) {
            $this->error[] = $this->language->get('error_delivery_slot_required');
        }

        if (!isset($postData['total_orders']) || empty($postData['total_orders'])) {
            $this->error[] = $this->language->get('error_orders_count_required');
        }

        if (!isset($postData['time_start']) || empty($postData['time_start'])) {
            $this->error[] = $this->language->get('error_time_start_required');
        }

        if (!isset($postData['time_end']) || empty($postData['time_end'])) {
            $this->error[] = $this->language->get('error_time_end_required');
        }


        return $this->error ? false : true;
    }

    public function install()
    {
        $this->load->model('module/delivery_slot/settings');

        $this->model_module_delivery_slot_settings->install();
    }

    public function uninstall()
    {
        $this->load->model('module/delivery_slot/settings');

        $this->model_module_delivery_slot_settings->uninstall();
    }

    private function validate()
    {
        $postData = $this->request->post['delivery_slot'];
        $this->language->load('module/delivery_slot');


        if (  $postData['cutoff'] == 1 && empty($postData['slot_time_start']) )
        {
            $this->errors['slot_time_start'] = $this->language->get('error_time_start_required');
        }

        if (  $postData['cutoff'] == 1 && empty($postData['slot_time_end']) )
        {
            $this->errors['slot_time_end'] = $this->language->get('error_time_end_required');
        }



        if ( $this->errors && !isset($this->errors['error']) )
        {
            $this->errors['warning'] = $this->language->get('error_warning');
        }

        return $this->errors ? false : true;
    }

    public function getSlotsData() {
        $json = array();

        $this->load->model('module/delivery_slot/slots');

        $day_id = $this->request->get['dayOfWeek'];
        $day_value = $this->request->get['dateValue'];

        if($day_id >= -1 && $day_id < 5){
            $day_id = $day_id + 3;
        }else{
            $day_id = $day_id - 4;
        }

        $data = [
            "day_id"   => $day_id,
            "dayValue" => $day_value
        ];

        $slots = $this->model_module_delivery_slot_slots->getSlotsTojson($data);

        if (count($slots) > 0) {
            foreach ($slots as $key=> $slot){
                $json[]= array(
                    'slot_id'        => $slot['ds_delivery_slot_id'],
                    'ds_day_id'      => $slot['ds_day_id'],
                    'delivery_slot_data'  => $slot['delivery_slot'],
                );
            }

        }

        $this->response->setOutput(json_encode($json));
    }

    public function slot_orders() {
        $this->language->load('module/delivery_slot');

        $this->document->setTitle($this->language->get('delivery_slot_heading_title'));


        $this->data['heading_title'] = $this->language->get('delivery_slot_heading_title');


        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('delivery_slot_heading_title'),
            'href'      => $this->url->link('module/delivery_slot',  $url, 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_slots_orders'),
            'href'      => $this->url->link('module/delivery_slot',  $url, 'SSL'),
            'separator' => ' :: '
        );


        $this->data['heading_title'] = $this->language->get('heading_title');

        $this->load->model('localisation/order_status');
        $order_statuses = $this->model_localisation_order_status->getOrderStatuses();

        foreach ( $order_statuses as $index => $order_status )
        {
            $order_statuses[$index]['orders'] = array();

            foreach ( $this->data['orders'] as $order )
            {
                $order_statuses[$index]['index'] = $i;

                if ( $order['order_status_id'] == $order_status['order_status_id'] )
                {
                    $order_statuses[$index]['orders'][] = $order;
                }
            }
        }
        $this->data['statuses'] = $order_statuses;

        $this->load->model('module/delivery_slot/settings');
        if($this->model_module_delivery_slot_settings->isCutOff()){
            $ds_settings = $this->config->get('delivery_slot');

            $time_zone = $this->config->get('config_timezone');

            $dateTime = new DateTime('now', new DateTimeZone($time_zone));
            $current_time =  $dateTime->format("h:i A");
            if($current_time > $ds_settings['slot_time_start'] && $current_time < $ds_settings['slot_time_end']){
                $this->data['slots_day_index'] = $ds_settings['slot_day_index'];
            }else{
                $this->data['slots_day_index'] = $ds_settings['slot_other_time'];
            }

        }
        $this->data['slots_max_day'] = isset($ds_settings['slot_max_day']) ? $ds_settings['slot_max_day'] : 0;

        $this->template = 'module/delivery_slot/slot/slots_orders.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
    }

    public function dtordersHandler() {
        $this->load->model('module/delivery_slot/slots');
        $request = $_REQUEST;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            parse_str(html_entity_decode($this->request->post['filter']), $filterData);
            $filterData = $filterData['filter'];
        }

        $filterData['search'] = null;
        if (isset($request['search']['value']) && strlen($request['search']['value']) > 0) {
            $filterData['search'] = $request['search']['value'];
        }

        $start = $request['start'];
        $length = $request['length'];

        $columns = array(
            0 => '',
            1 => 'order_id',
            14 => '',
        );

        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];
        
        $data = array(
            'sort' => $orderColumn,
            'order' => $orderType,
            'start' => $request['start'],
            'limit' => $request['length']
        );

        $return = $this->model_module_delivery_slot_slots->getSlotsOrders($data, $filterData);

        $data = $return['data'];

        $records = $data;
        $totalData = $return['total'];
        $totalFiltered = $return['totalFiltered'];

        $json_data = array(
            "draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($totalData), // total number of records
            "recordsFiltered" => $totalFiltered, // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $records,   // total data array
        );
        $this->response->setOutput(json_encode($json_data));
        return;
    }

    public function dtSlotOrderDelete()
    {
        $this->load->model('module/delivery_slot/slots');
        $this->load->language('module/delivery_slot');


        $id_s   = $this->request->post['id'];
        $action = $this->request->post['action'] ? $this->request->post['action'] : 'archive';

        if ( is_array($id_s) )
        {

            foreach ($id_s as $slot_order_id)
            {

                $data = [
                    'slot_order_id' => $slot_order_id
                ];

                if ( $this->model_module_delivery_slot_slots->deleteOrderSlot( $data ))
                {
                    $result_json['success'] = '1';
                    $result_json['success_msg'] = $this->language->get('text_success');
                }
                else
                {
                    $result_json['success'] = '0';
                    break;
                }
            }
        }
        else
        {
            $data = [
                'slot_order_id' => $id_s
            ];

            if ( $this->model_module_delivery_slot_slots->deleteOrderSlot( $data ) )
            {
                $result_json['success'] = '1';
                $result_json['success_msg'] = $this->language->get('text_success');
            }
            else
            {
                $result_json['success'] = '0';
            }
        }

        $this->response->setOutput(json_encode($result_json));
        return;

    }
}
