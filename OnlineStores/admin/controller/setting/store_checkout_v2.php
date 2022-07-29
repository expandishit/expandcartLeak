<?php
class ControllerSettingStoreCheckoutV2 extends Controller
{
    private $error = [];

    public function index()
    {
        if (!$this->identity->isStoreOnWhiteList() || !defined('THREE_STEPS_CHECKOUT') || (defined('THREE_STEPS_CHECKOUT') && (int)THREE_STEPS_CHECKOUT === 0)) {
            $this->redirect($this->url->link('common/dashboard', '', 'SSL'));
            return;
        }

        $this->language->load('setting/setting');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['direction'] = $this->language->get('direction');

        $this->load->model('setting/setting');

        $this->load->model('catalog/information');

        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            $result_json = array(
                'success' => '0',
                'errors' => array(),
                'success_msg' => ''
            );

            if ($this->validate()) {

                $this->model_setting_setting->insertUpdateSetting('checkoutv2', $this->request->post);

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
            array('text' => $this->language->get('heading_title'), 'href' => $this->url->link('setting/store_checkout_v2'))
        );
        
        $checkoutSettings = $this->model_setting_setting->getSetting('checkoutv2')['checkoutv2'];
                
        $this->data['checkoutv2'] = $checkoutSettings;

        $this->data['quickcheckout_link'] = $this->url->link('module/quickcheckout');
        $this->data['try_new_checkout'] = !\Extension::isInstalled('quickcheckout') ? 1 : (int)$this->config->get('quickcheckout')['try_new_checkout'];

        $this->data['information_pages'] = $this->model_catalog_information->getInformations();

        $this->data['action'] = $this->url->link('setting/store_checkout_v2');

        $this->data['cancel'] = $this->url->link('setting/store_checkout_v2');

        $this->template = 'setting/store_checkout_v2.expand';
        $this->base = "common/base";

        $this->response->setOutput($this->render_ecwig());
    }

    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'setting/store_checkout')) {
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
}
