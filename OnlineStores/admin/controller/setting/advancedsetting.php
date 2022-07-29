<?php
class ControllerSettingAdvancedSetting extends Controller {
    public function index() {
        $this->language->load('setting/advancedsetting');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'text'      => $this->language->get('text_home'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'href'      => $this->url->link('setting/advancedsetting', 'token=' . $this->session->data['token'] . $url, 'SSL'),
            'text'      => $this->language->get('heading_title'),
            'separator' => ' :: '
        );

        $this->load->model('setting/advancedsetting');

        $this->data['heading_title'] = $this->language->get('heading_title');
        $this->data['text_workflow_fields'] = $this->language->get('text_workflow_fields');
        $this->data['text_stock_status'] = $this->language->get('text_stock_status');
        $this->data['text_order_status'] = $this->language->get('text_order_status');
        $this->data['text_return_status'] = $this->language->get('text_return_status');
        $this->data['text_return_action'] = $this->language->get('text_return_action');
        $this->data['text_return_reason'] = $this->language->get('text_return_reason');
        $this->data['text_geodata'] = $this->language->get('text_geodata');
        $this->data['text_countries'] = $this->language->get('text_countries');
        $this->data['text_cities'] = $this->language->get('text_cities');
        $this->data['text_geo_zones'] = $this->language->get('text_geo_zones');
        $this->data['text_lengthclass'] = $this->language->get('text_lengthclass');
        $this->data['text_weightclass'] = $this->language->get('text_weightclass');
        $this->data['text_productmetrics'] = $this->language->get('text_productmetrics');
        $this->data['text_productfeedstitle'] = $this->language->get('text_productfeedstitle');
        $this->data['text_productfeeds'] = $this->language->get('text_productfeeds');

        $this->data['stock_status'] = $this->url->link('localisation/stock_status', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['order_status'] = $this->url->link('localisation/order_status', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['return_status'] = $this->url->link('localisation/return_status', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['return_action'] = $this->url->link('localisation/return_action', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['return_reason'] = $this->url->link('localisation/return_reason', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['countries'] = $this->url->link('localisation/country', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['cities'] = $this->url->link('localisation/zone', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['geo_zones'] = $this->url->link('localisation/geo_zone', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['lengthclass'] = $this->url->link('localisation/length_class', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['weightclass'] = $this->url->link('localisation/weight_class', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['productfeeds'] = $this->url->link('extension/feed', 'token=' . $this->session->data['token'], 'SSL');

        $this->template = 'setting/advancedsetting.tpl';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }
}
?>