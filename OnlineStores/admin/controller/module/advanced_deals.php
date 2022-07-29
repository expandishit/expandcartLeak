<?php
class ControllerModuleAdvancedDeals extends Controller {
	public function index() {
        $this->load->model('marketplace/common');

        $market_appservice_status = $this->model_marketplace_common->hasApp('advanced_deals');
        if (!$market_appservice_status['hasapp']) {
            $this->redirect($this->url->link('marketplace/app', 'id=' . $market_appservice_status['appserviceid'], 'SSL'));
            return;
        }

        $this->language->load('module/advanced_deals');

        $this->document->setTitle($this->language->get('heading_title'));

        $text_strings = array(
            'heading_title',
            'text_module',
            'heading_title_ecdeals',
            'heading_title_ecflashsale',
            'heading_title_ecdeals_settings',
            'heading_title_ecflashsale_settings'
        );

        foreach ($text_strings as $text) {
            $this->data[$text] = $this->language->get($text);
        }

        $this->data['url_ecdeals'] = $this->url->link('module/ecdeals/deals', '', 'SSL');
        $this->data['url_ecflashsale'] = $this->url->link('module/ecflashsale/flashsales', '', 'SSL');
        $this->data['url_ecdeals_settings'] = $this->url->link('module/ecdeals', '', 'SSL');
        $this->data['url_ecflashsale_settings'] = $this->url->link('module/ecflashsale', '', 'SSL');

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_module'),
            'href'      => $this->url->link('marketplace/home', 'type=apps', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('module/advanced_deals', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->template = 'module/advanced_deals.tpl';

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->response->setOutput($this->render());
    }

    public function install() {
        $this->load->model('ecdeals/product');
        $this->model_ecdeals_product->installSampleData();

        $this->load->model('ecflashsale/flashsale');
        $this->model_ecflashsale_flashsale->checkInstall();

        $this->db->query("INSERT INTO " . DB_PREFIX . "extension SET `type` = 'module', `code` = 'ecdeals'");
        $this->db->query("INSERT INTO " . DB_PREFIX . "extension SET `type` = 'module', `code` = 'ecflashsale'");
    }

    public function uninstall() {
        $this->db->query("DELETE FROM " . DB_PREFIX . "extension WHERE `type` = 'module' AND `code` = 'ecdeals'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "extension WHERE `type` = 'module' AND `code` = 'ecflashsale'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE `group` = 'ecdeals'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE `group` = 'ecflashsale'");
    }
}