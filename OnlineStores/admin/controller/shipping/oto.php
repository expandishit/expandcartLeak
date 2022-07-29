<?php

class ControllerShippingOto extends Controller
{
    private $error = array();

	public function install() {
        $this->db->query("
        CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "oto_order` 
        (
            `oto_order_id` int(11) NOT NULL AUTO_INCREMENT,
            `order_id` int(11) NOT NULL DEFAULT 0,
            `oto_id` int(11) NOT NULL DEFAULT 0,
            PRIMARY KEY (`oto_order_id`)
        ) 
        ENGINE=InnoDB DEFAULT COLLATE=utf8_general_ci;");
    }

    public function uninstall() { 
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "oto_order`;");
    }

    public function index()
    {

        $this->language->load('shipping/oto');
        $this->load->model('setting/setting');

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {

            if (!$this->validate()) {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
                $this->response->setOutput(json_encode($result_json));
                return;
            }

            $this->model_setting_setting->checkIfExtensionIsExists('shipping', 'oto', true);

            
            $this->tracking->updateGuideValue('SHIPPING');

            $this->model_setting_setting->editSetting('oto', $this->request->post);
            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('oto_success_save');
            $result_json['success'] = '1';
            $this->response->setOutput(json_encode($result_json));
            return;
        }

        $this->document->setTitle($this->language->get('oto_title'));

        /**
         * Breadcrumbs
         */
        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_shipping'),
            'href' => $this->url->link('extension/shipping', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('oto_title'),
            'href' => $this->url->link('shipping/oto', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['links'] = [
            'action' => $this->url->link('shipping/oto', '', 'SSL'),
            'cancel' => $this->url->link('extension/shipping', '', 'SSL'),
        ];

        $this->template = 'shipping/oto.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->data['cancel'] = $this->url->link('extension/shipping', '', 'SSL');

        $this->data['oto_payment_methods'] = [
            ['value' => 'cod', 'label' => $this->language->get('oto_cod')],
            ['value' => 'paid', 'label' => $this->language->get('oto_paid')]
        ];

        $this->data['oto_status'] = $this->config->get('oto_status');
        $this->data['oto_retailer_id'] = $this->config->get('oto_retailer_id');
        $this->data['oto_retailer_token'] = $this->config->get('oto_retailer_token');
        $this->data['oto_shipping_rate'] = $this->config->get('oto_shipping_rate');

        $this->response->setOutput($this->render());
    }

    private function validate()
    {
        if (!$this->user->hasPermission('modify', 'shipping/oto')) {
            $this->error['error'] = $this->language->get('error_permission');
        }

        if (empty($this->request->post['oto_retailer_id'])) {
            $this->error['error'] = $this->language->get('oto_error_retailer_id');
        }

        if (empty($this->request->post['oto_retailer_token'])) {
            $this->error['error'] = $this->language->get('oto_error_retailer_token');
        }

        return $this->error ? false : true;
    }
}