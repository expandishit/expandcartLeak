<?php
class ControllerReportDeliverySlots extends Controller {

	public function index() {
        $this->language->load('module/delivery_slot');
        $this->load->model('module/delivery_slot/settings');

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
            'separator' => ' :: '
        );

        $this->data['heading_title'] = $this->language->get('heading_title');

        if($this->request->get['status_ids']){
            $filter_status_id = explode(',', $this->request->get['status_ids']);
        }else{
            $filter_status_id = $this->model_module_delivery_slot_settings->getSettings()['filter_status_id'];
        }

        if($this->request->get['dt']){
            $dt = explode(" - ", $this->request->get['dt']);
            $this->data['start_date'] = $dt[0];
            $this->data['end_date'] = $dt[1];
        }

        $filter_status_name = [];

        $this->load->model('localisation/order_status');
        $order_statuses = $this->model_localisation_order_status->getOrderStatuses();

        //get status name of config selected statuses or url parameter statuses
        if($filter_status_id && count($filter_status_id) > 0) {
            foreach ( $order_statuses as $idx => $order_status )
            {
                if($filter_status_id && in_array($order_status['order_status_id'], $filter_status_id)){
                    $filter_status_name['status_'.$order_status['order_status_id']] = $order_status['name'];
                }
            }
        }else{
            /// Get first 5 statuses
            foreach ( $order_statuses as $idx => $order_status )
            {
                if($idx < 5){
                    $filter_status_id[] = $order_status['order_status_id'];
                    $filter_status_name['status_'.$order_status['order_status_id']] = $order_status['name'];
                }
            }
        }

        $this->data['filter_status_id_arr'] = $filter_status_id;
        $this->data['filter_status_id'] = implode(',', $filter_status_id);
        $this->data['filter_status_name'] = $filter_status_name;

        $this->data['statuses'] = $order_statuses;

        $this->template = 'report/delivery_slots.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
    }

    public function dtHandler() {
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

        if (isset($request['status_ids']) && strlen($request['status_ids']) > 0) {
            $filterData['status_ids'] = $request['status_ids'];
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

        $return = $this->model_module_delivery_slot_slots->getSlotsReport($data, $filterData);

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
}
?>