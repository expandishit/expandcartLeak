<?php
class ControllerSettingStoreOrders extends Controller {
    private $error = array();
 
    public function index() {
        $this->language->load('setting/setting'); 

        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['direction'] = $this->language->get('direction');
        
        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            /*$result_json = array(
                'success' => '0',
                'errors' => array(),
                'success_msg' => ''
            );*/

            if ($this->validate()) {

                if ( ! $this->request->post['config_order_invoice_logo_height'] )
                {
                    $this->request->post['config_order_invoice_logo_height'] = 50;
                }

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
            array('text' => $this->language->get('heading_title'), 'href' => $this->url->link('setting/store_checkout'))
        );

        $this->data['action'] = $this->url->link('setting/store_orders');
        
        $this->data['cancel'] = $this->url->link('setting/store_orders');

        // Loads
        $this->load->model('catalog/information');
        $this->load->model('localisation/order_status');
        $this->load->model('localisation/return_status');

        $order_tracking_status = $this->config->get('config_order_tracking_status');
        $this->data['exist_status_ids']      = array_column($order_tracking_status, 'id');
        $this->data['order_tracking_status'] = $order_tracking_status;

        // load return statuses
        $this->data['return_statuses'] = $this->model_localisation_return_status->getReturnStatuses();

        // Datas
        $this->data['informations'] = $this->model_catalog_information->getInformations();
        $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        //Get categories max child level
        $this->load->model('catalog/category');
        $this->data['max_child_level'] = $this->model_catalog_category->getMaxChildLevel() ?? 0;

        $this->value_from_post_or_config([
            'config_cart_weight',
            'hide_shipping_cart',
            'config_order_popup',
            'config_guest_checkout',
            'config_status_based_revenue',
            'invoice_image_product',
            'config_checkout_id',
            'config_order_edit',
            'config_webhook_url',
            'config_order_status_id',
            'config_order_shipped_status_id',
            'config_order_cod_status_id',
            'config_complete_status_id',
            'config_cancelled_order_status_id',
            'config_cancelled_reversal_status_id',
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
            'config_order_hide_country_code',
            'config_invoice_hide_country_code',
            'config_invoice_display_total_quantity',
            'config_custom_product_order_fields_display',
            'config_product_order_fields',
            'config_show_archived_orders_history',
            'product_quantity_update_status_selector',
            'config_return_status_id',
            'config_invoice_width'
        ]);
        
        // To make this option not empty permenant. 
        if($this->data['config_invoice_prefix'] == ""){
            $this->data['config_invoice_prefix'] = 'INV-00';
        }

        $this->template = 'setting/store_orders.expand';
        $this->base = "common/base";
                
        $this->response->setOutput($this->render_ecwig());
    }

    private function value_from_post_or_config($array)
    {
        foreach ($array as $elem)
        {
            $this->data[$elem] = $this->request->post[$elem] ?: $this->config->get($elem);
        }
    }

    protected function validate() {
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
?>