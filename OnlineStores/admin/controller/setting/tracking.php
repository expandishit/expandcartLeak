<?php

class ControllerSettingTracking extends Controller{

    private $error = array();

    public function index(){
        $this->language->load('setting/setting'); 

		$this->document->setTitle($this->language->get('heading_title'));

        $this->data['direction'] = $this->language->get('direction');

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {

            $result_json = array(
                'success' => '0',
                'errors' => array(),
                'success_msg' => ''
            );

            if ($this->_validate()) {
                $this->model_setting_setting->insertUpdateSetting('config', $this->request->post);

                $result_json['success'] = '1';
                $result_json['success_msg'] = $this->language->get('text_success');
            } else {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
            }

            $this->response->setOutput(json_encode($result_json));
            return;
        }

        $this->data['breadcrumbs'] = array(
            array('text' => $this->language->get('text_home'), 'href' => $this->url->link('common/home')),
            array('text' => $this->language->get('heading_title'), 'href' => $this->url->link('setting/tracking'))
        );

        $this->data['action'] = $this->url->link('setting/tracking');

        $this->data['cancel'] = $this->url->link('setting/tracking');
 		

	    $this->load->model('localisation/order_status');
	    $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
	    $order_tracking_status = $this->config->get('config_order_tracking_status');
	    $this->data['exist_status_ids']      = array_column($order_tracking_status, 'id');
	    $this->data['order_tracking_status'] = $order_tracking_status;


 		$this->template = 'setting/tracking.expand';
        $this->base = "common/base";

        $this->response->setOutput($this->render_ecwig());
    }

    private function _validate(){
    	return true;
    }
}
