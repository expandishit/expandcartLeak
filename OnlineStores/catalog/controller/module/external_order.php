<?php
class ControllerModuleExternalOrder extends Controller {
    private $route = 'module/external_order';
    private $error = array();

    public function index() {
        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('module/external_order', '', 'SSL');

            $this->redirect($this->url->link('account/login', '', 'SSL'));
        }
        $this->language->load('module/external_order');
        $this->load->model('account/externalorder');

        $this->data['heading_title'] = $this->language->get('heading_title');
        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['entry_url'] = $this->language->get('entry_url');
        $this->data['entry_name'] = $this->language->get('entry_name');
        $this->data['entry_category'] = $this->language->get('entry_category');
        $this->data['entry_quantity'] = $this->language->get('entry_quantity');
        $this->data['entry_price'] = $this->language->get('entry_price');
        $this->data['entry_notes'] = $this->language->get('entry_notes');
        $this->data['entry_captcha'] = $this->language->get('entry_captcha');

        $this->data['button_submit'] = $this->language->get('button_submit');
        $this->data['categories'] = $result = $this->model_account_externalorder->getCategories();

        $this->data['action'] = $this->url->link('module/external_order', '', 'SSL');
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $formData = array();
            $formData['url'] = $this->request->post['url'];
            $formData['name'] = $this->request->post['name'];
            $formData['category'] = $this->request->post['category'];
            $formData['quantity'] = $this->request->post['quantity'];
            $formData['price'] = $this->request->post['price'];
            $formData['notes'] = $this->request->post['notes'];

            $this->model_account_externalorder->addExternalOrder($formData);
            $this->success();
            return;
            //$this->redirect($this->url->link('account/success'));
        }
        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/external_order.tpl')) {
            $this->template = $this->config->get('config_template') . '/template/module/external_order.tpl';
        } else {
            $this->template = 'default/template/module/external_order.tpl';
        }

        $this->children = array(
            'common/column_left',
            'common/column_right',
            'common/content_top',
            'common/content_bottom',
            'common/footer',
            'common/header'
        );

        $this->response->setOutput($this->render());
    }

    protected function validate() {
        $iserror = false;
        if (utf8_strlen($this->request->post['url']) < 1 || utf8_strlen($this->request->post['url']) > 500) {
            $this->data['error_url'] = $this->language->get('error_required');
            $iserror = true;
        }

        if (utf8_strlen($this->request->post['name']) < 1 || utf8_strlen($this->request->post['name']) > 255) {
            $this->data['error_name'] = $this->language->get('error_required');
            $iserror = true;
        }

        if (utf8_strlen($this->request->post['category']) < 1) {
            $this->data['error_category'] = $this->language->get('error_required');
            $iserror = true;
        }

        if (utf8_strlen($this->request->post['quantity']) < 1 || !is_numeric ($this->request->post['quantity'])) {
            $this->data['error_quantity'] = $this->language->get('error_required');
            $iserror = true;
        }

        if (utf8_strlen($this->request->post['price']) < 1 || !is_numeric ($this->request->post['price'])) {
            $this->data['error_price'] = $this->language->get('error_required');
            $iserror = true;
        }

        if (empty($this->session->data['captcha']) || ($this->session->data['captcha'] != $this->request->post['captcha'])) {
            $this->data['error_captcha'] = $this->language->get('error_captcha');
            $iserror = true;
        }

        $this->data['url'] = $this->request->post['url'];
        $this->data['name'] = $this->request->post['name'];
        $this->data['category'] = $this->request->post['category'];
        $this->data['quantity'] = $this->request->post['quantity'];
        $this->data['price'] = $this->request->post['price'];
        $this->data['notes'] = $this->request->post['notes'];

        return !$iserror;
    }

    public function success() {
        $this->language->load('module/external_order');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('module/external_order', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['heading_title'] = $this->language->get('heading_title');

        $this->data['text_message'] = $this->language->get('text_message');

        $this->data['button_continue'] = $this->language->get('button_continue');

        $this->data['continue'] = $this->url->link('common/home');

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/common/success.tpl')) {
            $this->template = $this->config->get('config_template') . '/template/common/success.tpl';
        } else {
            $this->template = 'default/template/common/success.tpl';
        }

        $this->children = array(
            'common/column_left',
            'common/column_right',
            'common/content_top',
            'common/content_bottom',
            'common/footer',
            'common/header'
        );

        $this->response->setOutput($this->render());
    }

}