<?php

class ControllerMultisellerSellerbasedOptions extends ControllerMultisellerBase
{
    private $model;

    private function init()
    {
        $this->language->load('module/multiseller_advanced'); 

        $this->document->setTitle($this->language->get('seller_options_heading_title'));

        $this->load->model('module/multiseller_sellerbased_options');

        $this->model = $this->model_module_multiseller_sellerbased_options;

        if (isset($this->session->data['errors'])) {
            $this->data['error_warning'] = implode('</br >', $this->session->data['errors']);

            unset($this->session->data['errors']);
        }

        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        }

    }

    public function index()
    {
        $this->init();

        $data = [];

        $data['sellerbased_options'] = $this->model->getList();

        $data['createPlan'] = $this->url->link(
            'multiseller/subscriptions/create',
            'token=' . $this->session->data['token'],
            'SSL'
        );

        $data['refreshLink'] = $this->url->link(
            'multiseller/subscriptions',
            'token=' . $this->session->data['token'],
            'SSL'
        );

        $data['editLink'] = $this->url->link(
            'multiseller/subscriptions/edit',
            'token=' . $this->session->data['token'],
            'SSL'
        );

        $data['deleteLink'] = $this->url->link(
            'multiseller/subscriptions/delete',
            'token=' . $this->session->data['token'],
            'SSL'
        );


        $this->template = 'module/advanced_multiseller/sellerbased_options/list.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->data = array_merge($this->data, $data);

        $this->response->setOutput($this->render());
    }


}