<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

class ControllerShippingFetchr extends Controller
{
    private $model;

    private $status = [
        'UPL'   => 'order_created',
        'PKD'   => 'order_picked',
        'RTW'   => 'order_received_at_warehouse',
        'SL V'  => 'order_shelved',
        'SCH'   => 'order_scheduled',
        'DSP'   => 'order_dispatched',
        'NDL V' => 'order_not_delivered',
        'DL V'  => 'order_delivered',
        'CXL'   => 'order_cancelled',
        'MSG'   => 'order_missing',
        'INT'   => 'in_transit_to_hub',
        'RFD'   => 'order_scheduled_for_return',
        'HLD'   => 'order_on_hold',
        'RETD'  => 'order_cancelled',
    ];

    private function init()
    {
        $this->load->model('shipping/fetchr');

        $this->model = $this->model_shipping_fetchr;
    }

    public function callback()
    {
        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {

            $this->init();

            $post = json_decode(html_entity_decode(file_get_contents('php://input')), true);

            if (isset($this->status[$post['status_code']]) === false) {
                $response = [
                    'status' => 901,
                    'message' => 'undefined status'
                ];
                return $this->response->setOutput(json_encode($response));
            }

            // this is to strict the api updates to one status [ delivered ]
            // we may remove this lines in the future in case if we need to support all status types.
            if ($this->status[$post['status_code']] != $this->status['DL V']) {
                $response = [
                    'status' => 901,
                    'message' => 'status code should be DL V'
                ];
                return $this->response->setOutput(json_encode($response));
            }

            if ($this->model->checkModuleStatus() === false) {
                $response = [
                    'status' => 902,
                    'message' => 'undefined status'
                ];
                return $this->response->setOutput(json_encode($response));
            }

            $trackingId = $post['tracking_id'];

            $order = $this->model->selectByTackingNumber($trackingId);

            if ($order->num_rows < 1) {
                $response = [
                    'status' => 902,
                    'message' => 'order not found'
                ];
                return $this->response->setOutput(json_encode($response));
            }

            if (isset($post['date']) && $post['date'] != '') {
                $date = strtotime($post['date']);
                $date = date("Y-m-d H:i:s", $date);
            } else {
                $date = date("Y-m-d H:i:s", time());
            }

            $this->model->updateStatus($order->row['order_id'], $trackingId, $date);
            $response['status'] = 800;
            $response['message'] = 'success';

        } else {
            $response['status'] = 900;
            $response['message'] = 'error';
        }


        $this->response->setOutput(json_encode($response));
    }
}
