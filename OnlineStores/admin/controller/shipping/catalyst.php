<?php

class ControllerShippingCatalyst extends Controller
{
    /**
     * Validation errors array
     */
    private $error = array();

    /**
     * Entry point of the shipping method configurations
     */
    public function index() : void
    {
        $this->language->load('shipping/catalyst');
        $this->load->model('localisation/order_status');

        $this->document->setTitle($this->language->get('catalyst_title'));

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
            'text' => $this->language->get('catalyst_title'),
            'href' => $this->url->link('shipping/catalyst', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['links'] = [
            'submit' => $this->url->link('shipping/catalyst/saveSettings', '', 'SSL'),
            'action' => $this->url->link('shipping/catalyst/saveSettings', '', 'SSL'),
            'cancel' => $this->url->link('extension/shipping', '', 'SSL'),
        ];

        $this->template = 'shipping/catalyst.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->data['cancel'] = $this->url->link('extension/shipping', '', 'SSL');
        $this->data['catalyst_status'] = $this->config->get('catalyst_status');
        $this->data['catalyst_autoshipping'] = $this->config->get('catalyst_autoshipping');
        $this->data['catalyst_client_id'] = $this->config->get('catalyst_client_id');
        $this->data['catalyst_client_secret'] = $this->config->get('catalyst_client_secret');
        $this->data['catalyst_google_api_key'] = $this->config->get('catalyst_google_api_key');
        $this->data['catalyst_branch_id'] = $this->config->get('catalyst_branch_id');
        $this->data['catalyst_preparation_time'] = $this->config->get('catalyst_preparation_time');
        $this->data['catalyst_promise_time'] = $this->config->get('catalyst_promise_time');
        $this->data['catalyst_shipping_rate'] = $this->config->get('catalyst_shipping_rate');
        $this->data['catalyst_title'] = $this->config->get('catalyst_title');
        $this->data['catalyst_callback_url'] = $this->fronturl->frontUrl('shipping/catalyst/updateOrderStatus', '', 'SSL');
        $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
        for ($i = 0; $i < 7; $i++) {
            $this->data['catalyst_status_' . $i] = $this->config->get('catalyst_status_' . $i);
        }

        $this->load->model('localisation/language');
        $this->data['languages'] = $this->model_localisation_language->getLanguages();

        $this->response->setOutput($this->render());
    }

    /**
     * Save configurations data
     */
    public function saveSettings() : void
    {
        $this->language->load('shipping/catalyst');

        if (!$this->validate()) {
            $result_json['success'] = '0';
            $result_json['errors'] = $this->error;
            $this->response->setOutput(json_encode($result_json));
            return;
        }
        
        $this->load->model('setting/setting');

        $inputData = $this->trimInputData($this->request->post);

        if (
            $this->request->post['catalyst_client_id'] !== $this->config->get('catalyst_client_id') ||
            $this->request->post['catalyst_client_secret'] !== $this->config->get('catalyst_client_secret')
            ) {
                $this->model_setting_setting->deleteSetting('catalyst_auth');
            }

        $this->model_setting_setting->editSetting('catalyst', $inputData);
        
            $this->tracking->updateGuideValue('SHIPPING');

        $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('catalyst_success_save');
        $result_json['success'] = '1';
        $this->response->setOutput(json_encode($result_json));
    }

    /**
     * Trim white spaces from input data
     */
    private function trimInputData(array $data) : array
    {
        foreach($data as $key => $value)
        {
            if(!is_array($value))
                $data[$key] = trim($value);
        }
        return $data;
    }

    /**
     * Validate input data
     */
    private function validate() : bool
    {
        if (!$this->user->hasPermission('modify', 'shipping/catalyst')) {
            $this->error['error'] = $this->language->get('error_permission');
        }

        if (empty($this->request->post['catalyst_client_id'])) {
            $this->error['error'] = $this->language->get('catalyst_client_id') . ' ' . $this->language->get('catalyst_required');
        }

        if (empty($this->request->post['catalyst_client_secret'])) {
            $this->error['error'] = $this->language->get('catalyst_client_secret') . ' ' . $this->language->get('catalyst_required');
        }

        if (empty($this->request->post['catalyst_google_api_key'])) {
            $this->error['error'] = $this->language->get('catalyst_google_api_key') . ' ' . $this->language->get('catalyst_required');
        }

        if (empty($this->request->post['catalyst_branch_id'])) {
            $this->error['error'] = $this->language->get('catalyst_branch_id') . ' ' . $this->language->get('catalyst_required');
        }

        if (empty($this->request->post['catalyst_preparation_time'])) {
            $this->error['error'] = $this->language->get('catalyst_preparation_time') . ' ' . $this->language->get('catalyst_required');
        }

        if (empty($this->request->post['catalyst_promise_time'])) {
            $this->error['error'] = $this->language->get('catalyst_promise_time') . ' ' . $this->language->get('catalyst_required');
        }

        if (empty($this->request->post['catalyst_shipping_rate'])) {
            $this->error['error'] = $this->language->get('catalyst_shipping_rate') . ' ' . $this->language->get('catalyst_required');
        }

        if ($this->request->post['catalyst_shipping_rate'] < 0) {
            $this->error['error'] = $this->language->get('catalyst_shipping_rate_invalid_value');
        }

        for ($i = 0; $i < 7; $i++) {
            if (empty($this->request->post['catalyst_status_' . $i])) {
                $this->error['error'] = $this->language->get('catalyst_status_' . $i) . ' ' . $this->language->get('catalyst_required');
            }
        }

        return $this->error ? false : true;
    }
}