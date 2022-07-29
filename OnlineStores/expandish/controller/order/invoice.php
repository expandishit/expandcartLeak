<?php

use Mpdf\Mpdf;

class ControllerOrderInvoice extends Controller {

	public function PDF(){
        $languageId   = isset($this->request->request['language_id']) ? $this->request->request['language_id'] : $this->config->get('config_language_id');
        $suffix       = $this->request->request['language_code'] != 'en' ? "_" . $this->request->request['language_code'] : '';
        $order_id     = $this->request->get['order_id'];

        $languageCode = $this->getLanguageCode($languageId);
        $this->setLocalisationSettingsData($suffix , $languageCode);

        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($order_id);

        if ($order_info) {
            $this->load->model('module/quickcheckout_fields');
            $this->data['custom_fields'][$order_id] = $this->model_module_quickcheckout_fields->getOrderCustomFields($order_id,$languageId);

            $this->data['logo']         = $this->getLogo();
            $this->data['gift_product'] = $order_info['gift_product'];
            $this->data['logo_height']  = $this->config->get('config_order_invoice_logo_height');

            $this->load->model('setting/setting');
            $store_info = $this->model_setting_setting->getSetting('config', $order_info['store_id']);
            
            $totalQuantity = 0;

            $this->data['order'] = array(
                'order_id'           => $order_id,
                'invoice_no'         => $this->getInvoiceNo($order_info),
                'invoice_no_barcode' => $this->getInvoiceBarcode(),
                'date_added'         => date($this->language->get('date_format_short'), strtotime($order_info['date_added'])),
                'time_added'         => date('H:i', strtotime($this->getDateByCurrentTimeZone($order_info['date_added']))),
                'store_name'         => $store_info['config_name'][$languageCode] ?? $this->config->get('config_name'),
                'store_url'          => rtrim($order_info['store_url'], '/'),
                'store_address'      => nl2br($store_info['config_address'][$languageCode] ?? $this->config->get('config_address')),
                'store_email'        => $store_info['config_email'] ?? (!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email')),
                'store_telephone'    => $store_info['config_telephone'] ?? $this->config->get('config_telephone'),
                'store_fax'          => $store_info['config_fax'] ?? $this->config->get('config_fax'),
                'email'              => $order_info['email'],
                'telephone'          => $order_info['telephone'],
                'fax'                => $order_info['fax'],
                'shipping_address'   => $this->getShippingAddress($order_info, $languageId),
                'firstname'          => $order_info['payment_firstname'],
                'lastname'           => $order_info['payment_lastname'],
                'shipping_method'    => $order_info['shipping_method'],
                'payment_address'    => $this->getPaymentAddress($order_info, $languageId),
                'payment_company_id' => $order_info['payment_company_id'],
                'payment_tax_id'     => $order_info['payment_tax_id'],
                'payment_method'     => $this->model_checkout_order->getOrderPaymentMethod($order_id),
                'products'           => $this->getOrderProducts($order_id, $languageId, $totalQuantity),
                'voucher'            => $this->getVoucherData($order_id),
                'total'              => $this->getTotalData($order_id , $languageId, $languageCode, $totalQuantity),
                'comment'            => nl2br($order_info['comment']),
                'delivery_info'      => nl2br($order_info['delivery_info'])
            );
        }

        // Display tax number in case of the admin set it from setting -> advanced ->tax options
        $this->checkTaxNo();

        // Display product sku in case of the admin set it from setting -> advanced -> products
        $this->checkSku();

        // check if delivery slot app installed
        $this->checkDeliverySlotApp($order_id);

        $this->template = 'default/template/order_invoice/invoice.expand';
        $this->base="common/base";
        // $this->response->setOutput( $this->render_ecwig() );

        $mpdf = new Mpdf(['tempDir' => TEMP_DIR_PATH]);
        $mpdf->SetTitle(sprintf($this->language->get('invoice_pdf_heading_title'), $order_id));
        $mpdf->WriteHTML($this->render_ecwig());
        $mpdf->Output();
    }

    private function getShowTotalQualitySetting()
    {
        return $this->config->get('config_invoice_display_total_quantity') ?? 0;
    }

    private function getOrderProducts($order_id , $languageId, &$totalQuantity = 0)
    {
        $products = $this->model_checkout_order->getOrderProductsForInvoice($order_id, $languageId);
        $product_data = array();
        $showTotalQuantity = $this->getShowTotalQualitySetting();
        $this->load->model('account/customer');

        //Warehouses check
        $warehouse_setting        = $this->config->get('warehouses');
        $this->data['warehouses'] = $warehouse_setting && $warehouse_setting['status'] == 1 && $warehouse_setting['invoice_display'] == 1 ? true : false;

        foreach ($products as $product) {
            if ($showTotalQuantity == 1) $totalQuantity += $product['quantity'];

            $option_data = [];
            $options = $this->model_checkout_order->getOrderOptionsForInvoice($order_id, $product['order_product_id'], $languageId);

            // Product Option Image PRO module <<
            if (isset($product['image'])) {
                $this->load->model('module/product_option_image_pro');
                $poip_installed = $this->model_module_product_option_image_pro->installed();
                if ($poip_installed) {
                    $product['image'] = $this->model_module_product_option_image_pro->getProductOrderImage($product['product_id'], $options, $product['image']);
                }
            }

            $productOptionValueId = [];
            // define options array
            $optionsArr = [];

            foreach ($options as $option) {
                if ($option['type'] != 'file') {
                    $value = $option['value'];
                } else {
                    $value = utf8_substr($option['value'], 0, utf8_strrpos($option['value'], '.'));
                }
                // check if options exists before add value to old values
                if(in_array($option['product_option_id'],$optionsArr)){
                    $option_data[$option['product_option_id']]['value'] .= ' , '.$value;
                }else{
                    $option_data[$option['product_option_id']] = array(
                        'name'  => $option['name'],
                        'value' => $value
                    );
                }

                $optionsArr[] = $option['product_option_id'];
                $productOptionValueId[] = $option['product_option_value_id'];
            }

            if ($product['rent_data']) {
                $rentData = json_decode($product['rent_data'], true);
                $rentData['range'] = array_map(function ($value) {
                    return date("Y-m-d", $value);
                } , $rentData['range']);
            }
            // the new data of rental data comes from order_product_rental table instead of the old rent_data field in order_product table
            elseif($product['from_date']){
                $rentData['range']['from'] = $product['from_date'];
                $rentData['range']['to'] = $product['to_date'];
                $rentData['diff'] = $product['diff'];
            }

            if ($product['price_meter_data']) {
                $pricePerMeterData = json_decode($product['price_meter_data'], true);
            }

            if ($product['printing_document']) {
                $printingDocument = json_decode($product['printing_document'], true);
            }

            $queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");
            if ($queryMultiseller->num_rows) {
                // check if product is sold by a seller.
                $check = $this->db->query("SELECT `seller_id` FROM `ms_product` WHERE `product_id` = '{$product['product_id']}'");

                if ($check->num_rows > 0) {
                    $seller_id = $check->row['seller_id'];
                    $seller = $this->db->query("SELECT * FROM `ms_seller` WHERE `seller_id` = '{$seller_id}' AND `seller_status` = '1' AND `seller_approved` = '1'");

                    $sellerAddress = $this->model_account_customer->getAddress(
                        $this->model_account_customer->getDefaultAddressId($seller->row['seller_id'])
                    );

                    if ($seller->num_rows > 0) {
                        $seller = (object)$seller->rows[0];
                        $this->data['there_is_a_seller'] = true;
                        $this->load->model('localisation/country');
                        $this->load->model('localisation/zone');

                        $lang_id = $this->config->get('config_language_id');
                        $seller->country = $this->model_localisation_country->getCountryLocale($seller->country_id, $lang_id)['name'];
                        $seller->zone = $this->model_localisation_zone->getZoneLocale($seller->zone_id, $lang_id)['name'];
                        $seller->address = $sellerAddress['address_1'];
                    } else {
                        $seller = null;
                    }
                }
            }

            $this->data['remaining_total'] = null;
            $this->load->model('module/minimum_deposit/settings');
            if ($this->model_module_minimum_deposit_settings->isActive()) {
                $this->data['remaining_total'] = $this->model_checkout_order->getOrderProductsRemainingTotal($order_id);
                $main_price = $this->currency->format($product['main_price'] , $order_info['currency_code'], $order_info['currency_value']);
                $remaining_amount = $this->currency->format($product['remaining_amount'] , $order_info['remaining_amount'], $order_info['remaining_amount']);
            }

            $this->initializer(['sku' => 'module/product_variations']);
            $skuInfo = null;
            if ($this->sku->isActive()) {
                $skuInfo = $this->sku->getProductVariationByValuesIds(
                    $product['product_id'], array_column(
                        $this->sku->getOptionValuesIds($productOptionValueId),
                        'option_value_id'
                    )
                );

                if ($skuInfo) {
                    $product['sku'] = $skuInfo['product_sku'];
                    $product['barcode'] = $skuInfo['product_barcode']? $skuInfo['product_barcode'] : $product['barcode'] ;
                }
            }

            $product_data[] =  [
                'product_id' => $product['product_id'],
                'name'       => $product['name'],
                'model'      => $product['model'],
                'sku'        => $product['sku'],
                'option'     => $option_data,
                'quantity'   => $product['quantity'],
                'barcode'    => $product['barcode'],
                'barcode_image'     => $this->getBarcodeImage($product['barcode']),
                'rentData'          => $rentData,
                'pricePerMeterData' => $pricePerMeterData,
                'printingDocument'  => $printingDocument,
                'thumb'             => $this->getProductThumb($product['image']),
                'price'             => $this->currency->format($product['price'] , $order_info['currency_code'], $order_info['currency_value']),
                'total'             => $this->currency->format($product['total'] , $order_info['currency_code'], $order_info['currency_value']),
                'main_price'        => isset($main_price) ? $main_price : NULL ,
                'remaining_amount'  => isset($remaining_amount) ? $remaining_amount : NULL,
                'seller'            => $seller,
                'is_soft_deleted'   => 0
            ];
        }

        // check if soft delete is enabled
        $soft_products = [];
        if($this->config->get('config_soft_delete_status')){
            $soft_deleted_products = $this->model_checkout_order->getOrderSoftDeletedProductsForInvoice($order_id, $languageId);
            foreach ($soft_deleted_products as $product) {
                // dashed invoice element
                $product['is_soft_deleted'] = 1 ;
                $product['price'] = $this->currency->format($product['price'] , $order_info['currency_code'], $order_info['currency_value']);
                $product['total'] = $this->currency->format($product['total'] , $order_info['currency_code'], $order_info['currency_value']);
                $soft_products[] = $product;
                // zero price invoice element
                $product['price'] = 0;
                $product['quantity'] = 0;
                $product['total'] = 0;
                $product['is_soft_deleted'] = 0;
                $soft_products[] = $product;
            }

        }
        return array_merge($product_data,$soft_products);
    }

    private function getBarcodeImage($barcode)
    {
        if ($barcode != '') {
            $barcodeGenerator = (new BarcodeGenerator())
                ->setType($this->config->get('config_barcode_type'))
                ->setBarcode($barcode);

            $barcodeImageString = $barcodeGenerator->generate();
        } else {
            $barcodeImageString = 0;
        }

        return $barcodeImageString;
    }

    private function getProductThumb($image)
    {
        $this->load->model('tool/image');

        // resize image product  to height height and width
        $config_invoice_product_image = $this->config->get('config_invoice_product_image');

        if(!$config_invoice_product_image)
            $invoice_product_image =  $this->model_tool_image->resize($image, 150, 150);
        else
            $invoice_product_image = $this->model_tool_image->resize($image, $config_invoice_product_image, $config_invoice_product_image);

        return $invoice_product_image;
    }

    private function checkTaxNo()
    {
        $tax_number = $this->config->get('config_tax_number');

        if( $tax_number ){
            $this->data['tax_number'] = $tax_number;
        }
    }

    private function checkSku()
    {
        $show_sku_product_invoice = $this->config->get('config_show_sku_product_invoice');

        if( $show_sku_product_invoice ){
            $this->data['show_sku_product_invoice'] = $show_sku_product_invoice;
        }
    }

    private function checkDeliverySlotApp($order_id)
    {
        //Get delivery slot app settings
        $delivery_slot = $this->config->get('delivery_slot');

        //Check if installed
        if(is_array($delivery_slot) && count($delivery_slot) > 0){

            $this->language->load('module/delivery_slot');
            $this->load->model('module/delivery_slot/slots');

            $orderSlot = $this->model_module_delivery_slot_slots->getOrderDeliverySlot($order_id);

            if($orderSlot['delivery_date']){
                //Convert m-d-Y to d-m-Y to be able to formate it
                $tempDate = explode('-', $orderSlot['delivery_date']);
                $newDate = $tempDate['1']."-".$tempDate['0']."-".$tempDate['2'];
                $orderSlot['delivery_date'] = date('d/m/Y', strtotime($newDate));
            }

            $this->data['delivery_slot'] = true;
            $this->data['order_delivery_slot'] = $orderSlot;
        }
    }

    private function setLocalisationSettingsData($suffix, $languageCode)
    {
        $this->language->setDirectory($languageCode)->load_json('order/invoice', true);
        $this->load->model('setting/setting');
        $localizationSettings = $this->model_setting_setting->getSetting('localization');

        $this->data['column_model'] = ! empty( $localizationSettings['text_product_model' . $suffix] ) ? $localizationSettings['text_product_model' . $suffix] : $this->language->get('column_model');
        $this->data['text_address1'] = ! empty( $localizationSettings['entry_address_1' . $suffix] ) ? $localizationSettings['entry_address_1' . $suffix] : $this->language->get('text_address_1');
        $this->data['text_address2'] = ! empty( $localizationSettings['entry_address_2' . $suffix] ) ? $localizationSettings['entry_address_2' . $suffix] : $this->language->get('text_address_2');
        $this->data['text_city'] = ! empty( $localizationSettings['entry_city' . $suffix] ) ? $localizationSettings['entry_city' . $suffix] : $this->language->get('text_city');
        $this->data['text_zone'] = ! empty( $localizationSettings['entry_checkout_zone' . $suffix] ) ? $localizationSettings['entry_checkout_zone' . $suffix] : $this->language->get('text_zone');
        $this->data['text_area'] = ! empty( $localizationSettings['entry_checkout_area' . $suffix] ) ? $localizationSettings['entry_checkout_area' . $suffix] : $this->language->get('text_area');
        $this->data['text_postcode'] = ! empty( $localizationSettings['entry_postcode' . $suffix] ) ? $localizationSettings['entry_postcode' . $suffix] : $this->language->get('text_postcode');
        $this->data['text_country'] = ! empty( $localizationSettings['entry_country' . $suffix] ) ? $localizationSettings['entry_country' . $suffix] : $this->language->get('text_country');
        $this->data['text_telephone'] = ! empty( $localizationSettings['entry_telephone' . $suffix] ) ? $localizationSettings['entry_telephone' . $suffix] : $this->language->get('text_payment_telephone');
        $this->data['text_company'] = ! empty( $localizationSettings['entry_company' . $suffix] ) ? $localizationSettings['entry_company' . $suffix] : $this->language->get('text_company');
        $this->data['text_fax'] = ! empty( $localizationSettings['entry_fax' . $suffix] ) ? $localizationSettings['entry_fax' . $suffix] : $this->language->get('text_fax');
        $this->data['text_company_id'] = ! empty( $localizationSettings['entry_company_id' . $suffix] ) ? $localizationSettings['entry_company_id' . $suffix] : $this->language->get('text_company_id');
        $this->data['text_tax_id'] = ! empty( $localizationSettings['entry_tax_id' . $suffix] ) ? $localizationSettings['entry_tax_id' . $suffix] : $this->language->get('text_tax_id');
        $this->data['text_invoice_title'] = ! empty( $localizationSettings['entry_invoice' . $suffix] ) ? $localizationSettings['entry_invoice' . $suffix] : $this->language->get('text_invoice');
    }

    private function getInvoiceBarcode()
    {
        $invoiceBarcodeString = '';

        if ($this->config->get('config_invoice_no_barcode') == 1) {
            $barcodeGenerator = (new BarcodeGenerator())
                ->setType($this->config->get('config_barcode_type'))
                ->setBarcode($order_id);
            $invoiceBarcodeString = $barcodeGenerator->generate();
        }

        return $invoiceBarcodeString;
    }

    private function getInvoiceNo($order_info)
    {
        return $order_info['invoice_no'] ? $order_info['invoice_prefix'] . $order_info['invoice_no'] : '';
    }

    private function getLogo()
    {
        $logo = \Filesystem::getUrl('image/no_image.jpg');

        if ( $this->config->get('config_logo') && \Filesystem::getUrl('image/' . $this->config->get('config_logo')) ) {
            $logo = \Filesystem::getUrl('image/' . $this->config->get('config_logo'));
        }

        return $logo;
    }

    private function getShippingAddress($order_info, $languageId)
    {
        $this->load->model('localisation/country');
        $this->load->model('localisation/zone');
        $this->load->model('localisation/area');

        $format = "";
        if ($order_info['shipping_company']) {
            $format .= $this->data['text_company'] . ': {company}' . "\n";
        }

        if ($order_info['shipping_address_1'] || $order_info['payment_address_1']) {
            $format .= $this->data['text_address1'] . ': {address_1}' . "\n";
        }

        if ($order_info['shipping_address_2'] || $order_info['payment_address_2']) {
            $format .= $this->data['text_address2'] . ': {address_2}' . "\n";
        }

        if ($order_info['shipping_city'] || $order_info['payment_city']) {
            $format .= $this->data['text_city'] . ': {city}' . "\n";
        }

        if ($order_info['shipping_postcode'] || $order_info['payment_postcode']) {
            $format .= $this->data['text_postcode'] . ': {postcode}' . "\n";
        }

        if ($order_info['shipping_area'] || $order_info['payment_area_id']) {
            $format .= $this->data['text_area'] . ': {area}' . "\n";
        }

        if ($order_info['shipping_zone'] || $order_info['payment_zone_id']) {
            $format .= $this->data['text_zone'] . ': {zone}' . "\n";
        }

        if ($order_info['shipping_country'] || $order_info['payment_country_id']) {
            $format .= $this->data['text_country'] . ': {country}';
        }
        $shippingCountryLocalised = $this->model_localisation_country->getCountry($order_info["shipping_country_id"], $languageId)["name"];
        $paymentCountryLocalised = $this->model_localisation_country->getCountry($order_info["payment_country_id"], $languageId)["name"];
        $shippingStateLocalised = $this->model_localisation_zone->getZone($order_info["shipping_zone_id"],$languageId)["name"];
        $paymentStateLocalised = $this->model_localisation_zone->getZone($order_info["payment_zone_id"],$languageId)["name"];
        $paymentAreaLocalised = $this->model_localisation_area->getArea($order_info["payment_area_id"],$languageId)["name"];
        $shippingAreaLocalised = $this->model_localisation_area->getArea($order_info["shipping_area_id"],$languageId)["name"];

        $find = array(
            '{firstname}',
            '{lastname}',
            '{company}',
            '{address_1}',
            '{address_2}',
            '{city}',
            '{postcode}',
            '{area}',
            '{zone}',
            '{zone_code}',
            '{country}'
        );

        $replace = array(
            'firstname' => $order_info['shipping_firstname'] ? $order_info['shipping_firstname'] : $order_info['payment_firstname'],
            'lastname'  => $order_info['shipping_lastname'] ? $order_info['shipping_lastname'] : $order_info['payment_lastname'],
            'company'   => $order_info['shipping_company'] ? $order_info['shipping_company'] : $order_info['payment_company'],
            'address_1' => $order_info['shipping_address_1'] ? $order_info['shipping_address_1'] : $order_info['payment_address_1'],
            'address_2' => $order_info['shipping_address_2'] ? $order_info['shipping_address_2'] : $order_info['payment_address_2'],
            'city'      => $order_info['shipping_city'] ? $order_info['shipping_city'] : $order_info['payment_city'],
            'postcode'  => $order_info['shipping_postcode'] ? $order_info['shipping_postcode'] : $order_info['payment_postcode'],
            'area'      => $shippingAreaLocalised ? $shippingAreaLocalised :($paymentAreaLocalised ? $paymentAreaLocalised : $order_info['shipping_area']) ,
            'zone'      => $shippingStateLocalised ? $shippingStateLocalised :($paymentStateLocalised ? $paymentStateLocalised : $order_info['shipping_zone']) ,
            'zone_code' => $order_info['shipping_zone_code'] ? $order_info['shipping_zone_code'] : $order_info['payment_zone_code'],
            'country'   => $shippingCountryLocalised ? $shippingCountryLocalised : $paymentCountryLocalised
        );
        return str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));
    }

    private function getPaymentAddress($order_info, $languageId)
    {
        $format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';

        if ($order_info['payment_address_format']) {
            $format = $order_info['payment_address_format'];
        }

        $this->load->model('localisation/country');
        $this->load->model('localisation/zone');
        $this->load->model('localisation/area');
        $paymentCountryLocalised = $this->model_localisation_country->getCountry($order_info["payment_country_id"], $languageId)["name"];
        $paymentStateLocalised = $this->model_localisation_zone->getZone($order_info["payment_zone_id"],$languageId)["name"];
        $paymentAreaLocalised = $this->model_localisation_area->getArea($order_info["payment_area_id"],$languageId)["name"];

        $find = array(
            '{firstname}',
            '{lastname}',
            '{company}',
            '{address_1}',
            '{address_2}',
            '{city}',
            '{postcode}',
            '{area}',
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
            'area'      => $paymentAreaLocalised,
            'zone'      => $paymentStateLocalised,
            'zone_code' => $order_info['payment_zone_code'],
            'country'   => $paymentCountryLocalised
        );

        return str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));
    }

    private function getVoucherData($order_id)
    {
        $voucher_data = [];
        $vouchers = $this->model_checkout_order->getOrderVouchers($order_id);
        foreach ($vouchers as $voucher) {
            $voucher_data[] = array(
                'description' => $voucher['description'],
                'amount'      => $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value'])
            );
        }
        return $voucher_data;
    }

    private function getTotalData($order_id , $languageId, $languageCode, $totalQuantity)
    {
        $total_data = $this->model_checkout_order->getOrderTotals($order_id);
        foreach ($total_data as &$total) {

            $total['title'] = html_entity_decode($total['title']);

            if ( $total['code'] == 'tax' ){
                $tax_rate_id = $this->db->query("SELECT tax_rate_id FROM " . DB_PREFIX . "tax_rate_description
                WHERE name = '" . $this->db->escape($total['title']) ."'")->row['tax_rate_id'];
                $total['title'] = $this->db->query("SELECT `name` FROM " . DB_PREFIX . "tax_rate_description
                WHERE tax_rate_id = " . (int)$tax_rate_id . " AND language_id = " . (int) $languageId)->row['name'];
            }

            $this->language->setDirectory($languageCode)->load_json('total/' . $total['code']);
            if ( $total['code'] == 'wkpos_discount' ){
                $total['title'] = $this->language->get('wkpos_discount_title');
            }
            elseif ( ! in_array($total['code'], ['shipping' , 'tax']) ){
                $total['title'] = $this->language->get('text_'.$total['code']);
            }

            if ($total['code'] == 'cffpm' ){
                $total['title']=$this->config->get('cffpm_total_row_name_'.$languageCode);
            }
        }

        if ($this->getShowTotalQualitySetting() == 1) {
            $total_data[] = [
                'title' => $this->language->get('column_total_quantity'),
                'text'  => $totalQuantity
            ];
        }

        return $total_data;
    }

    private function getLanguageCode($languageId)
    {
        $this->load->model('localisation/language');
        return $this->model_localisation_language->getLanguage($languageId)['code'];
    }
}
