<?php

class ControllerSettingStoreProducts extends Controller
{
    private $error = array();

    public function index()
    {
        $this->language->load('setting/setting');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['direction'] = $this->language->get('direction');

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            $result_json = array(
                'success' => '0',
                'errors' => array(),
                'success_msg' => ''
            );

            if ($this->validate()) {
                $this->processStockStatusDisplayBadgeField();

                $this->model_setting_setting->insertUpdateSetting('config', $this->request->post);

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
            array('text' => $this->language->get('heading_title'), 'href' => $this->url->link('setting/store_products'))
        );

        $this->data['action'] = $this->url->link('setting/store_products');

        $this->data['cancel'] = $this->url->link('setting/store_products');

        $arrValues = [
            'config_product_count',
            'config_category_product_count',
            'config_show_sku_product_invoice',
            'config_show_manfacture_image',
            'config_review_status',
            'config_download',
            'config_hide_add_to_cart',
            'paypal_view_checkout',
            'show_brands',
            'show_quantity',
            'config_barcode_type',
            'config_show_attribute',
            'config_show_option',
            'show_meta_tag',
            'show_meta_desc',
            'auto_price_weigh_calc',
            'config_products_default_sorting_column',
            'config_products_default_sorting_by_column',
            'show_outofstock_option',
            'config_show_option_prices',
            'unique_barcode',
            'unique_sku',
            'login_before_add_to_cart',
            'enable_order_maximum_quantity',
            'pending_order_statuses',
            'product_default_subtract_stock',
            'product_addthis_id',
            'product_option_image_width',
            'product_option_image_height',
            'config_review_auto_approve'
        ];

        $queryMultiseller = $this->db->query("SELECT extension_id FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");
        if($queryMultiseller->num_rows){
            $this->data['show_seller_field'] = true;
            array_push($arrValues, 'show_seller');
        }

        //Get attributes
        $this->load->model('catalog/attribute');
        $results = $this->model_catalog_attribute->getAttributes();
        foreach ($results as $result) {                        
            $this->data['attributes'][$result['attribute_id']] = $result['name'];
        }
        //Get options
        $this->load->model('catalog/option');
        $results = $this->model_catalog_option->getOptions();
        foreach ($results as $result) {
            $this->data['options'][$result['option_id']] = $result['name'];
        }
        // get order status id
        $this->load->model('localisation/order_status');
        $this->load->model('localisation/stock_status');
        $this->load->model('tool/image');

        $this->data['setting'] = $this->model_setting_setting->getSetting('config');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $this->data['stock_statuses'] = $this->model_localisation_stock_status->getStockStatuses();

        $this->data['no_image'] = $this->model_tool_image->resize($this->config->get('no_image'),200,200);

        $this->data['product_image_without_image'] = $this->model_tool_image->resize($this->config->get('product_image_without_image'),200,200);

        $this->value_from_post_or_config($arrValues);

        $this->data['barcode_types'] = [
            'TYPE_CODE_39' => 'Code 39',
            'TYPE_CODE_39_CHECKSUM' => 'Code 39 with checksum',
            'TYPE_CODE_39E' => 'Code 39 Extended',
            'TYPE_CODE_39E_CHECKSUM' => 'Code 39 Extended with checksum',
            'TYPE_CODE_93' => 'Code 93 - USS-93',
            'TYPE_STANDARD_2_5' => 'Standard 2 of 5',
            'TYPE_STANDARD_2_5_CHECKSUM' => 'Standard 2 of 5 with checksum',
            'TYPE_INTERLEAVED_2_5' => 'Interleaved 2 of 5',
            'TYPE_INTERLEAVED_2_5_CHECKSUM' => 'Interleaved 2 of 5 with checksum',
            'TYPE_CODE_128' => 'Code 128',
            'TYPE_CODE_128_A' => 'CODE 128 A',
            'TYPE_CODE_128_B' => 'CODE 128 B',
            'TYPE_CODE_128_C' => 'CODE 128 C',
            'TYPE_EAN_2' => '2-Digits UPC-Based Extention',
            'TYPE_EAN_5' => '5-Digits UPC-Based Extention',
            'TYPE_EAN_8' => 'EAN 8',
            'TYPE_EAN_13' => 'EAN 13',
            'TYPE_UPC_A' => 'UPC-A',
            'TYPE_UPC_E' => 'UPC-E',
            'TYPE_MSI' => 'MSI (Variation of Plessey code)',
            'TYPE_MSI_CHECKSUM' => 'MSI + CHECKSUM (modulo 11)',
            'TYPE_POSTNET' => 'POSTNET',
            'TYPE_PLANET' => 'PLANET',
            'TYPE_RMS4CC' => 'RMS4CC (Royal Mail 4-state Customer Code) - CBC (Customer Bar Code)',
            'TYPE_KIX' => 'KIX (Klant index - Customer index)',
            'TYPE_IMB' => 'IMB - Intelligent Mail Barcode - Onecode - USPS-B-3200',
            'TYPE_CODABAR' => 'CODABAR',
            'TYPE_CODE_11' => 'CODE 11',
            'TYPE_PHARMA_CODE' => 'PHARMA',
            'TYPE_PHARMA_CODE_TWO_TRACKS' => 'PHARMACODE TWO-TRACKS',
        ];

        $this->template = 'setting/store_products.expand';
        $this->base = "common/base";

        $this->response->setOutput($this->render_ecwig());
    }

    private function value_from_post_or_config($array)
    {
        foreach ($array as $elem) {
            $this->data[$elem] = $this->request->post[$elem] ?: $this->config->get($elem);
        }
    }

    private function processStockStatusDisplayBadgeField()
    {
        if(!isset($this->request->post['config_stock_status_display_badge'])){
            $this->request->post['config_stock_status_display_badge'] = [];
        }
    }
    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'setting/store_general')) {
            $this->error['error'] = $this->language->get('error_permission');
        }

        if (!$this->request->post['config_catalog_limit']) {
            $this->error['config_catalog_limit'] = $this->language->get('error_limit');
        }

        if (!$this->request->post['config_admin_limit']) {
            $this->error['config_admin_limit'] = $this->language->get('error_limit');
        }

        if ($this->error && !isset($this->error['error'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        if($this->request->post['config_review_auto_approve']){
            if(!$this->request->post['config_review_status']){
                $this->error['config_review_status'] = $this->language->get('review_error');
            }
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }
}
