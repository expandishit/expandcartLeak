<?php
class ControllerCommonCart extends Controller
{
    public function index()
    {
        $this->language->load_json('common/cart');
        $this->language->load_json('checkout/cart');
        $this->language->load_json('product/product');

        $isCustomerAllowedToViewPrice = $this->customer->isCustomerAllowedToViewPrice();
        // set shipping method to the default if no current one exists.
        if(!isset($this->session->data['shipping_method']['code']) || 
           !isset($this->session->data['shipping_method']['title']) ||
           !isset($this->session->data['shipping_method']['cost']) ||
            ( isset($this->session->data['recalc_shipping']) && $this->session->data['recalc_shipping'] == "1") ){

            unset($this->session->data['shipping_method']);
            $op = $this->getChild('module/quickcheckout/load_settings');
            $this->session->data['recalc_shipping'] = 0;
        }

        // Cart - START
        $queryRewardPointInstalled = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'reward_points_pro'");

        if($queryRewardPointInstalled->num_rows) {
            $this->load->model('rewardpoints/spendingrule');
            $this->load->model('rewardpoints/shoppingcartrule');
            $this->model_rewardpoints_spendingrule->getSpendingPoints();
            $this->model_rewardpoints_shoppingcartrule->getShoppingCartPoints();
        }

        if (isset($this->request->get['remove'])) {
            $this->cart->remove($this->request->get['remove']);

            unset($this->session->data['vouchers'][$this->request->get['remove']]);

            //Stock Forecasting app check delivery date for this product to remove
            unset($this->session->data['stock_forecasting_cart'][$this->request->get['remove']]);
        }

        // Totals
        $this->load->model('setting/extension');

        $total_data = array();
        $total = 0;
        $taxes = $this->cart->getTaxes();

        // Display prices
        if ($isCustomerAllowedToViewPrice) {
            $sort_order = array();

            $results = $this->model_setting_extension->getExtensions('total');

            foreach ($results as $key => $value) {
                $sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
            }

            array_multisort($sort_order, SORT_ASC, $results);

            foreach ($results as $result) {
                if($this->config->get('hide_shipping_cart') && $result['code'] == 'shipping'){
                    continue;
                }
                if ($this->config->get($result['code'] . '_status')) {
                    $this->load->model('total/' . $result['code']);

                    $this->{'model_total_' . $result['code']}->getTotal($total_data, $total, $taxes);
                }

                $sort_order = array();

                foreach ($total_data as $key => $value) {
                    $sort_order[$key] = $value['sort_order'];
                }

                array_multisort($sort_order, SORT_ASC, $total_data);
            }
        }

        $this->data['totals'] = $total_data;

        //exists above in line 23
        //$queryRewardPointInstalled = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'reward_points_pro'");

        if($queryRewardPointInstalled->num_rows) {
            $this->data['totals'] = array();
            foreach ($total_data as $_total) {
                if($_total['code'] == 'earn_point' || $_total['code'] == 'redeem_point'){
                    $this->data['totals'][] = array(
                        'title' => $_total['title'],
                        'text'  => $_total['value'] . ' ' . $this->config->get('text_points_' . $this->session->data['language'] )
                    );
                }else{
                    $this->data['totals'][] = array(
                        'title' => $_total['title'],
                        'text'  => $_total['text']
                    );
                }
  
            }
        }


        $this->data['cart_items_count'] = $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0);
        $this->data['cart_total'] = $this->currency->format($total);
        //$this->data['text_items'] = sprintf($this->language->get('text_items'), $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0));

        $this->load->model('tool/image');

        $this->data['products'] = array();
//        echo '<pre>';print_r($this->cart->getProducts());exit;

        foreach ($this->cart->getProducts() as $key => $product) {
            if ($product['image']) {
                $image = $this->model_tool_image->resize($product['image'], 95, 110);
            } else {
                $image = '';
            }

            $option_data = array();

            foreach ($product['option'] as $option) {
                if ($option['type'] != 'file') {
                    $value = $option['option_value'];
                } else {
                    $filename = $this->encryption->decrypt($option['option_value']);

                    $value = utf8_substr($filename, 0, utf8_strrpos($filename, '.'));
                }

                $option_data[] = array(
                    'name'  => $option['name'],
                    'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value),
                    'type'  => $option['type']
                );
            }

            // Display prices
            if ($isCustomerAllowedToViewPrice) {
                $price = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')));
            } else {
                $price = false;
            }

            // Display prices
            if ($isCustomerAllowedToViewPrice) {
                $total = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')) * $product['quantity']);
            } else {
                $total = false;
            }

            if (isset($product['rentData'])) {
                $product['rentData']['range'] = array_map(function ($value) {
                    return date("Y-m-d", $value);
                } , $product['rentData']['range']);
            }

            // $queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");

            $this->data['products'][$key] = array(
                'product_id' => \Extension::isInstalled('multiseller') ? $product['product_id'] : 0,
                'key'      => $product['key'],
                'thumb'    => $image,
                'name'     => $product['name'],
                'model'    => $product['model'],
                'option'   => $option_data,
                'quantity' => $product['quantity'],
                'price'    => $price,
                'total'    => $total,
                'href'     => $this->url->link('product/product', 'product_id=' . $product['product_id']),
                'rentData' => $product['rentData'],
                'main_price'=> isset($product['main_price']) ? $this->currency->format($product['main_price']) : NULL,
                'remaining_amount' => isset($product['remaining_amount']) ? $this->currency->format($product['remaining_amount']) : NULL,
                'pricePerMeterData'=> $product['pricePerMeterData'],
                'printingDocument' => $product['printingDocument']
            );
            if( \Extension::isinstalled('stock_forecasting') && $this->config->get('stock_forecasting_status') == 1 ){
                $this->data['products'][$key]['delivey_date'] = $this->session->data['stock_forecasting_cart'][$product['key']]['stock_forecasting_delivery_date'];
                $this->data['stock_forecasting_app_installed'] = TRUE;
            }
        }

        // Gift Voucher
        $this->data['vouchers'] = array();

        if (!empty($this->session->data['vouchers'])) {
            foreach ($this->session->data['vouchers'] as $key => $voucher) {
                $v_quantity = $voucher['quantity'] ? $voucher['quantity'] : 1;
                $this->data['vouchers'][] = array(
                    'key'         => $key,
                    'description' => $voucher['description'],
                    'quantity'    => $v_quantity,
                    'amount'      => $this->currency->format($voucher['amount'] * $v_quantity)
                );
            }
        }
        // Cart - END


        $this->data['currency_symbols'] = [
            'left' => $this->currency->getSymbolLeft(),
            'right' => $this->currency->getSymbolRight()
        ];
        
        //PayPal Express
        $pp_express_stgs = $this->config->get('pp_express');
        $this->data['pp_exp_enabled']      = $pp_express_stgs['status'] ? 1 : 0;
        $this->data['pp_exp_hidecheckout'] = $pp_express_stgs['hidecheckout'] ? 1 : 0;
        ////////////////////////////////////////

        //$this->data['cart'] = $this->url->link('checkout/cart');

        //$this->data['checkout'] = $this->url->link('checkout/checkout', '', 'SSL');

        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/common/cart.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/common/cart.expand';
        }
        else {
            $this->template = $this->config->get('config_template') . '/template/common/cart.expand';
        }

        $this->response->setOutput($this->render_ecwig());
    }

    public function get_cart_count()
    {
        $this->response->setOutput($this->cart->countProducts());
    }
}
?>
