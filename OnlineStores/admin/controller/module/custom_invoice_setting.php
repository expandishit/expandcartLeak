<?php

class ControllerModuleCustomInvoiceSetting extends Controller
{
    public function install(){
        $this->load->model('setting/setting');
        $this->model_setting_setting->insertUpdateSetting( 'config', [
            'config_show_qr_image'         => 1,
            'config_qrcode_settings'       => 'text',
            'config_qrcode_text_fields'    => ['invoice_no', 'store_name', 'invoice_date', 'tax_no', 'taxes'],
            'config_qrcode_selected_taxes' => array_column($this->getTaxRates(), 'tax_rate_id')
        ] );
    }

    public function uninstall(){
        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteByKeys([
            'config_show_qr_image',
            'config_qrcode_settings',
            'config_qrcode_text_fields',
            'config_qrcode_selected_taxes'           
        ]);
    }

    public function index()
    {
        $this->language->load('module/custom_invoice_setting');
        $this->language->load('setting/setting');
        $this->load->model('localisation/language');

        $this->document->setTitle($this->language->get('cit_title'));

        $this->data['submit_link'] = $this->url->link('module/custom_invoice_setting/saveSettings', '', 'SSL');
        $this->data['languages'] = $this->model_localisation_language->getLanguages();
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
            'text'      => $this->language->get('cit_title'),
            'href'      => $this->url->link('module/custom_invoice_setting', '', 'SSL'),
            'separator' => ' :: '
        );

        //Get all taxe Rates list
        $this->data['taxes'] = $this->getTaxRates();
        // echo '<pre>'; print_r(array_column($this->getTaxRates(), 'tax_rate_id')); die();
        $this->template = 'module/custom_invoice_setting/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->value_from_post_or_config([
            'invoice_image_product',
            'config_order_invoice_logo_height',
            'config_invoice_product_image',
            'config_invoice_prefix',
            'config_soft_delete_status',
            'config_hide_comments',
            'config_invoice_option_price',
            'config_invoice_display_barcode',
            'config_invoice_no_barcode',
            'config_invoice_hide_model',
            'config_invoice_display_sku',
            'config_invoice_products_sort_order',
            'config_invoice_products_sort_type',
            'config_invoice_products_sort_ctlevel',
            'config_invoice_hide_country_code',
            'config_invoice_display_total_quantity',
            'config_invoice_width',
            'config_auto_generate_invoice_no',
            'config_show_qr_image',
            'config_qrcode_settings',
            'config_qrcode_text_fields',
            'config_qrcode_selected_taxes'            
        ]);

        // To make this option not empty permenant.
        if($this->data['config_invoice_prefix'] == ""){
            $this->data['config_invoice_prefix'] = 'INV-00';
        }

        $this->response->setOutput($this->render_ecwig());
    }

    public function saveSettings()
    {
        $this->language->load('module/custom_invoice_template');
        $this->load->model('module/custom_invoice_template');
        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting('config', $this->request->post);
        $json['success'] = '1';
        $json['success_msg'] = $this->language->get('cit_save');
        $this->response->setOutput(json_encode($json));
    }

    private function value_from_post_or_config($array)
    {
        foreach ($array as $elem)
        {
            $this->data[$elem] = $this->request->post[$elem] ?: $this->config->get($elem);
        }
    }

    private function getTaxRates()
    {
        $this->load->model('localisation/tax_rate');
        return $this->model_localisation_tax_rate->getTaxRates();
    }
}
