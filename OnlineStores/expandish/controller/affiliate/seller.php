<?php

class ControllerAffiliateSeller extends Controller
{
    public function index() {

        $this->language->load_json('affiliate/tracking');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_account'),
            'href'      => $this->url->link('affiliate/account', '', 'SSL'),
            'separator' => $this->language->get('text_separator')
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('affiliate/tracking', '', 'SSL'),
            'separator' => $this->language->get('text_separator')
        );

        $this->data['text_description'] = sprintf($this->language->get('text_description'), $this->config->get('config_name'));

        $this->data['sellerTracking'] = $sellerTracking= $this->affiliate->getSellerAffiliateCode();
        $this->data['sellerAffiliateLink'] = str_replace('&amp;', '&', $this->url->link('seller/register-seller', 'sellerTracking=' . $sellerTracking));

        $this->data['continue'] = $this->url->link('affiliate/account', '', 'SSL');

        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/affiliate/seller.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/affiliate/seller.expand';
        }
        else {
            $this->template = $this->config->get('config_template') . '/template/affiliate/seller.expand';
        }

        $this->children = array(
            'common/footer',
            'common/header'
        );

        $this->response->setOutput($this->render_ecwig());
    }
}

?>
