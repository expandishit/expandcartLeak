<?php

class ControllerShippingEsnad extends Controller
{
    private $route = "shipping/esnad";

    /**
     * Order Invoice
     */
    public function invoice(){

        $this->language->load_json( $this->route );
        if( !isset( $this->request->get['order_id'] ) ){
            echo $this->language->get( 'error_order_id' );
            exit;
        }

        $temporder = $this->request->get['order_id'];

        $temporder = base64_decode( $temporder );

        $token_array = explode( '___', $temporder );

        $order_id = $token_array[0];
        $customer_id = $token_array[1];
        $order_add = $token_array[2];

        if( empty( $order_id ) || empty( $order_add ) ){
            echo $this->language->get( 'error_wrong' );
            exit;
        }

        $data['title'] = $this->language->get('text_invoice');
        if ($this->request->server['HTTPS']) {
            $data['base'] = HTTPS_SERVER;
        } else {
            $data['base'] = HTTP_SERVER;
        }

        $data['direction'] = $this->language->get('direction');
        $data['lang'] = $this->language->get('code');

        if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
            $data['logo'] = DIR_IMAGE . $this->config->get('config_logo');
        } else {
            $data['logo'] = '';
        }

        $this->load->model('checkout/order');
        $this->load->model('account/order');
        $this->load->model('setting/setting');

        $data['orders'] = array();

        $order_info = $this->model_checkout_order->getOrder($order_id);

        // Validate Order.
        $order_added = $order_info['date_added'];
        $order_customer_id = $order_info['customer_id'];
        if( empty( $order_info ) || $order_added != $order_add || $order_customer_id != $customer_id ){
            echo $this->language->get( 'error_wrong' );
            exit;
        }

        // Load invoice in Order language.
        $this->load->model('localisation/language');
        $languages = $this->model_localisation_language->getLanguages();
        $order_lang = $order_info['language_code'];
        $sort_lang_code = explode( '-', $order_lang );
        $sort_lang_code = $sort_lang_code[0];
        $current_lang = $this->language->get('code');
        if( !empty( $current_lang ) && !empty( $order_lang ) && isset( $languages[$order_lang]) && ( $order_lang != $current_lang && $sort_lang_code != $current_lang ) ){
            $this->session->data['language'] = $order_lang;
            $current_url = $data['base'].'index.php?'. urldecode( http_build_query( $this->request->get) );
            header( "Location: ". $current_url );
        }

        if ($order_info) {
            $store_info = $this->model_setting_setting->getSetting('config', $order_info['store_id']);

            if ($store_info) {
                $store_address = $store_info['config_address'];
                $store_email = $store_info['config_email'];
                $store_telephone = $store_info['config_telephone'];
                $store_fax = $store_info['config_fax'];
            } else {
                $store_address = $this->config->get('config_address');
                $store_email = $this->config->get('config_email');
                $store_telephone = $this->config->get('config_telephone');
                $store_fax = $this->config->get('config_fax');
            }

            if ($order_info['invoice_no']) {
                $invoice_no = $order_info['invoice_prefix'] . $order_info['invoice_no'];
            } else {
                $invoice_no = '';
            }

            if ($order_info['payment_address_format']) {
                $format = $order_info['payment_address_format'];
            } else {
                $format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
            }

            $find = array(
                '{firstname}',
                '{lastname}',
                '{company}',
                '{address_1}',
                '{address_2}',
                '{city}',
                '{postcode}',
                '{zone}',
                '{zone_code}',
                '{country}'
            );

            $replace = array(
                'firstname' => $order_info['payment_firstname'],
                'lastname'  => $order_info['payment_lastname'],
                'company'   => $order_info['payment_company'],
                'address_1' => $order_info['payment_address_1'],
                'address_2' => $order_info['payment_address_2'],
                'city'      => $order_info['payment_city'],
                'postcode'  => $order_info['payment_postcode'],
                'zone'      => $order_info['payment_zone'],
                'zone_code' => $order_info['payment_zone_code'],
                'country'   => $order_info['payment_country']
            );

            $payment_address = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

            if ($order_info['shipping_address_format']) {
                $format = $order_info['shipping_address_format'];
            } else {
                $format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
            }

            $find = array(
                '{firstname}',
                '{lastname}',
                '{company}',
                '{address_1}',
                '{address_2}',
                '{city}',
                '{postcode}',
                '{zone}',
                '{zone_code}',
                '{country}'
            );

            $replace = array(
                'firstname' => $order_info['shipping_firstname'],
                'lastname'  => $order_info['shipping_lastname'],
                'company'   => $order_info['shipping_company'],
                'address_1' => $order_info['shipping_address_1'],
                'address_2' => $order_info['shipping_address_2'],
                'city'      => $order_info['shipping_city'],
                'postcode'  => $order_info['shipping_postcode'],
                'zone'      => $order_info['shipping_zone'],
                'zone_code' => $order_info['shipping_zone_code'],
                'country'   => $order_info['shipping_country']
            );

            $shipping_address = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

            //$this->load->model('tool/upload');

            $product_data = array();

            $products = $this->model_account_order->getOrderProducts($order_id);

            $deduction = 0;
            $tax_deduction = 0;
            foreach ($products as $product) {
                $option_data = array();

                $options = $this->model_account_order->getOrderOptions($order_id, $product['order_product_id']);

                foreach ($options as $option) {
                    if ($option['type'] != 'file') {
                        $value = $option['value'];
                    } else {
                        //$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

                        if ($upload_info) {
                            $value = $upload_info['name'];
                        } else {
                            $value = '';
                        }
                    }

                    $option_data[] = array(
                        'name'  => $option['name'],
                        'value' => $value
                    );
                }

                $product_data[] = array(
                    'name'     => $product['name'],
                    'model'    => $product['model'],
                    'option'   => $option_data,
                    'quantity' => $product['quantity'],
                    'price'    => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
                    'total'    => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value'])
                );
            }

            $voucher_data = array();

            $vouchers = $this->model_account_order->getOrderVouchers($order_id);

            foreach ($vouchers as $voucher) {
                $v_quantity = $voucher['quantity'];

                $voucher_data[] = array(
                    'description' => $voucher['description'],
                    'amount'      => $this->currency->format( ($voucher['amount']*$v_quantity), $order_info['currency_code'], $order_info['currency_value'])
                );
            }

            $total_data = array();

            $totals = $this->model_account_order->getOrderTotals($order_id);

            if( $deduction > 0 ){
                $tax_total = array();
                foreach ($totals as $key => $total) {
                    if( $total['code'] == 'sub_total' ){
                        $totals[$key]['value'] -= $deduction;
                    }
                    if( $total['code'] == 'total' ){
                        $totals[$key]['value'] -= $deduction;
                        $totals[$key]['value'] -= $tax_deduction;
                    }
                    if( $total['code'] == 'tax' && $tax_deduction > 0 ){
                        if( isset( $tax_total['title'] ) ){
                            $tax_total['title'] .= ', ' . $total['title'];
                        }else{
                            $tax_total['title'] = $total['title'];
                        }

                        if( isset( $tax_total['value'] ) ){
                            $tax_total['value'] += $total['value'];
                        }else{
                            $tax_total['value'] = $total['value'];
                        }
                        unset( $totals[$key]);
                    }
                }
                if( $tax_deduction > 0 && !empty( $tax_total ) ){
                    $tax_total['title'] = $this->language->get('text_taxes').'('.$tax_total['title'].')';
                    $tax_total['value'] -= $tax_deduction;
                    $total = array_pop($totals);
                    $totals[] = $tax_total;
                    $totals[] = $total;
                }
            }

            foreach ($totals as $key => $total) {
                $total_data[] = array(
                    'title' => $total['title'],
                    'text'  => $this->currency->format($total['value'], $order_info['currency_code'], $order_info['currency_value'])
                );
            }

            $data['orders'][] = array(
                'order_id'	       => $order_id,
                'invoice_no'       => $invoice_no,
                'date_added'       => date($this->language->get('date_format_short'), strtotime($order_info['date_added'])),
                'store_name'       => $order_info['store_name'],
                'store_url'        => rtrim($order_info['store_url'], '/'),
                'store_address'    => nl2br($store_address),
                'store_email'      => $store_email,
                'store_telephone'  => $store_telephone,
                'store_fax'        => $store_fax,
                'email'            => $order_info['email'],
                'telephone'        => $order_info['telephone'],
                'shipping_address' => $shipping_address,
                'shipping_method'  => $order_info['shipping_method'],
                'payment_address'  => $payment_address,
                'payment_method'   => $order_info['payment_method'],
                'product'          => $product_data,
                'voucher'          => $voucher_data,
                'total'            => $total_data,
                'comment'          => nl2br($order_info['comment'])
            );
        }

        $this->data = $data;

        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/default/template/module/home.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/default/template/module/order_invoice.expand';
        }
        else {
            $this->template = '/default/template/module/order_invoice.expand';
        }

        $this->response->setOutput($this->render_ecwig());
    }

}
