<?php

class ControllerSaleVoucherTheme extends Controller
{
    private $error = array();

    public function index()
    {
        $this->language->load('sale/voucher_theme');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('sale/voucher_theme');

        $this->getList();
    }

    public function insert()
    {
        $this->language->load('sale/voucher_theme');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('sale/voucher_theme');

        if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {
            if ( !$this->validateForm() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;

                $this->response->setOutput(json_encode($result_json));

                return;
            }

            $this->model_sale_voucher_theme->addVoucherTheme($this->request->post);

            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

            $result_json['success'] = '1';

            $this->response->setOutput(json_encode($result_json));

            return;
        }

        $this->data['links'] = [
            'submit' => $this->url->link('sale/voucher_theme/insert', '', 'SSL'),
            'cancel' => $this->url->link('sale/voucher_theme', '', 'SSL')
        ];

        $this->getForm();
    }

    public function update()
    {
        $this->language->load('sale/voucher_theme');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('sale/voucher_theme');

        if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {

            if ( ! $this->validateForm() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
                
                $this->response->setOutput(json_encode($result_json));
                
                return;
            }

            $this->model_sale_voucher_theme->editVoucherTheme(
                $this->request->get['voucher_theme_id'], $this->request->post
            );

            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

            $result_json['success'] = '1';
            
            $this->response->setOutput(json_encode($result_json));
            
            return;
        }

        $this->data['links'] = [
            'submit' => $this->url->link(
                'sale/voucher_theme/update',
                'voucher_theme_id=' . $this->request->get['voucher_theme_id'],
                'SSL'
            ),
            'cancel' => $this->url->link('sale/voucher_theme', '', 'SSL')
        ];

        $this->getForm();
    }

    public function delete()
    {
        $this->language->load('sale/voucher_theme');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('sale/voucher_theme');

        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            foreach ($this->request->post['selected'] as $voucher_theme_id) {
                $this->model_sale_voucher_theme->deleteVoucherTheme($voucher_theme_id);
            }

            $this->session->data['success'] = $this->language->get('text_success');

            $this->redirect(
                $this->url->link('sale/voucher_theme', '', 'SSL')
            );
        }

        $this->getList();
    }

    protected function getList()
    {
        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('sale/voucher_theme', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['links']['vouchers'] = $this->url->link('sale/voucher', '', 'SSL');

        $this->data['voucher_themes'] = array();

        $voucher_theme_total = $this->model_sale_voucher_theme->getTotalVoucherThemes();

        $results = $this->model_sale_voucher_theme->getVoucherThemes();

        foreach ($results as $result) {
            $action = array();

            $this->data['voucher_themes'][] = array(
                'voucher_theme_id' => $result['voucher_theme_id'],
                'name' => $result['name'],
            );
        }

        if (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else {
            $this->data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $this->data['success'] = '';
        }

        $this->template = 'sale/voucher/theme_list.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
    }

    
    protected function getForm()
    {

        $this->data['cancel'] = $this->url->link('sale/voucher_theme', '', 'SSL');

        if (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else {
            $this->data['error_warning'] = '';
        }

        if (isset($this->error['name'])) {
            $this->data['error_name'] = $this->error['name'];
        } else {
            $this->data['error_name'] = array();
        }

        if (isset($this->error['image'])) {
            $this->data['error_image'] = $this->error['image'];
        } else {
            $this->data['error_image'] = '';
        }

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('sale/voucher_theme', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text' => ! isset($this->request->get['voucher_theme_id']) ? $this->language->get('breadcrumb_insert') : $this->language->get('breadcrumb_update'),
            'href' => $this->url->link('sale/voucher_theme', '', 'SSL'),
            'separator' => ' :: '
        );

        if (isset($this->request->get['voucher_theme_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $voucher_theme_info = $this->model_sale_voucher_theme->getVoucherTheme(
                $this->request->get['voucher_theme_id']
            );
        }

        $this->load->model('localisation/language');

        $this->data['languages'] = $this->model_localisation_language->getLanguages();

        if (isset($this->request->post['voucher_theme_description'])) {
            $this->data['voucher_theme_description'] = $this->request->post['voucher_theme_description'];
        } elseif (isset($this->request->get['voucher_theme_id'])) {
            $this->data['voucher_theme_description'] = $this->model_sale_voucher_theme->getVoucherThemeDescriptions(
                $this->request->get['voucher_theme_id']
            );
        } else {
            $this->data['voucher_theme_description'] = array();
        }

        if (isset($this->request->post['image'])) {
            $this->data['image'] = $this->request->post['image'];
        } elseif (!empty($voucher_theme_info)) {
            $this->data['image'] = $voucher_theme_info['image'];
        } else {
            $this->data['image'] = '';
        }

        $this->load->model('tool/image');

        $this->data['no_image'] = $this->model_tool_image->resize('no_image.jpg', 150, 150);

        if (isset($voucher_theme_info) && $voucher_theme_info['image']) {
            $this->data['thumb'] = $this->model_tool_image->resize($voucher_theme_info['image'], 150, 150);
        } else {
            $this->data['thumb'] = $this->data['no_image'];
        }

        $this->template = 'sale/voucher/theme_form.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        //$this->document->addScript('view/javascript/cube/dropzone.min.js');
        $this->document->addScript('view/javascript/cube/scripts.js');

        $this->response->setOutput($this->render_ecwig());
    }

    protected function validateForm()
    {
        if ( ! $this->user->hasPermission('modify', 'sale/voucher_theme') )
        {
            $this->error['error'] = $this->language->get('error_permission');
        }

        foreach ( $this->request->post['voucher_theme_description'] as $language_id => $value )
        {
            if ((utf8_strlen($value['name']) < 3) || (utf8_strlen($value['name']) > 32)) {
                $this->error['name_' . $language_id] = $this->language->get('error_name');
            }
        }

        if (!$this->request->post['image']) {
            $this->error['image'] = $this->language->get('error_image');
        }

        if ( $this->error && !isset($this->error['error']) )
        {
            $this->error['warning'] = $this->language->get('error_warning');
        }
        
        return $this->error ? false : true;
    }

    protected function validateDelete()
    {
        if (!$this->user->hasPermission('modify', 'sale/voucher_theme')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        $this->load->model('sale/voucher');

        foreach ($this->request->post['selected'] as $voucher_theme_id) {
            $voucher_total = $this->model_sale_voucher->getTotalVouchersByVoucherThemeId($voucher_theme_id);

            if ($voucher_total) {
                $this->error['warning'] = sprintf($this->language->get('error_voucher'), $voucher_total);
            }
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    public function dtDelete()
    {
        $this->load->model('sale/voucher_theme');

        if (isset($this->request->post['selected']) && $this->validateDelete()) {

            foreach ($this->request->post['selected'] as $id) {
                $this->model_sale_voucher_theme->deleteVoucherTheme($id);
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
