<?php

include DIR_SYSTEM . 'library/PHPExcel.php';

use ExpandCart\Foundation\Support\Facades\Filesystem;

class ControllerSellerToolProductExport extends ControllerSellerAccount
{
    public function index()
    {
        // initialize the export settings
        $seller_id = $this->customer->getId();

        if (isset($this->request->get['filter_language'])) {
            $this->data['filter_language'] = $this->request->get['filter_language'];
        } else {
            $this->data['filter_language'] = null;
        }

        if (isset($this->request->get['filter_categories'])) {
            $this->data['filter_categories'] = $this->request->get['filter_categories'];
        } else {
            $this->data['filter_categories'] = null;
        }

        if (isset($this->request->get['filter_name'])) {
            $this->data['filter_name'] = $this->request->get['filter_name'];
        } else {
            $this->data['filter_name'] = null;
        }

        if (isset($this->request->get['filter_model'])) {
            $this->data['filter_model'] = $this->request->get['filter_model'];
        } else {
            $this->data['filter_model'] = null;
        }

        if (isset($this->request->get['filter_price_from'])) {
            $this->data['filter_price_from'] = $this->request->get['filter_price_from'];
        } else {
            $this->data['filter_price_from'] = null;
        }

        if (isset($this->request->get['filter_price_to'])) {
            $this->data['filter_price_to'] = $this->request->get['filter_price_to'];
        } else {
            $this->data['filter_price_to'] = null;
        }

        if (isset($this->request->get['filter_quantity_from'])) {
            $this->data['filter_quantity_from'] = $this->request->get['filter_quantity_from'];
        } else {
            $this->data['filter_quantity_from'] = null;
        }

        if (isset($this->request->get['filter_quantity_to'])) {
            $this->data['filter_quantity_to'] = $this->request->get['filter_quantity_to'];
        } else {
            $this->data['filter_quantity_to'] = null;
        }

        if (isset($this->request->get['filter_quantity'])) {
            $this->data['filter_quantity'] = $this->request->get['filter_quantity'];
        } else {
            $this->data['filter_quantity'] = null;
        }

        if (isset($this->request->get['filter_status'])) {
            $this->data['filter_status'] = $this->request->get['filter_status'];
        } else {
            $this->data['filter_status'] = null;
        }

        if (isset($this->request->get['filter_start'])) {
            $this->data['filter_start'] = $this->request->get['filter_start'];
        } else {
            $this->data['filter_start'] = 0;
        }

        if (isset($this->request->get['filter_limit'])) {
            $this->data['filter_limit'] = $this->request->get['filter_limit'];
        } else {
            $this->data['filter_limit'] = $this->MsLoader->MsProduct->countProducts($seller_id);
        }

        if (isset($this->request->get['filter_option'])) {
            $this->data['filter_option'] = $this->request->get['filter_option'];
        } else {
            $this->data['filter_option'] = 0;
        }

        if (isset($this->request->get['filter_image_path'])) {
            $this->data['filter_image_path'] = $this->request->get['filter_image_path'];
        } else {
            $this->data['filter_image_path'] = 0;
        }

        if (isset($this->request->get['filter_eformat'])) {
            $this->data['filter_eformat'] = $this->request->get['filter_eformat'];
        } else {
            $this->data['filter_eformat'] = 0;
        }

        if (isset($this->request->get['filter_manufacturer'])) {
            $this->data['filter_manufacturer'] = $this->request->get['filter_manufacturer'];
        } else {
            $this->data['filter_manufacturer'] = null;
        }

        $this->data['submit_link'] = $this->url->link('seller/tool/product_export/export');



        $this->load->model('localisation/language');
        $this->data['languages'] = $this->model_localisation_language->getLanguages();

        $this->load->model('setting/store');
        $this->data['stores'] = $this->model_setting_store->getStores();

        $this->load->model('catalog/category');
        $this->data['categories'] = $this->model_catalog_category->getCategories(array());

        $this->load->model('catalog/manufacturer');
        $this->data['manufacturers'] = $this->model_catalog_manufacturer->getManufacturers();

        // set template
        $this->template = 'default/template/multiseller/tool/product_export.tpl';

        $this->response->setOutput($this->render(true));
    }

    public function export()
    {
        $filter = $this->prepareFilterData();

        // call models
        $this->load->model('multiseller/tool/product_export');
        $this->load->model('setting/store');
        $this->load->model('catalog/category');
        $this->load->model('catalog/filter');
        $this->load->model('catalog/attribute');
        // $this->load->model('catalog/attribute_group');

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setTitle("Product");
        $objPHPExcel->getActiveSheet()->getStyle('S')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $objPHPExcel->getActiveSheet()
            ->getStyle('A1:BB1')
            ->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'd9d9d9')
                    )
                )
            );
        $i = 1;

        $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, 'Product ID')->getColumnDimension('A')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, 'Language')->getColumnDimension('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, 'Store')->getColumnDimension('C')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, 'Name')->getColumnDimension('D')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, 'Model')->getColumnDimension('E')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $i, 'Description')->getColumnDimension('F')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $i, 'Meta Title')->getColumnDimension('G')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $i, 'Meta Description')->getColumnDimension('H')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . $i, 'Meta Keyword')->getColumnDimension('I')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . $i, 'Tag')->getColumnDimension('J')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . $i, 'Main Image')->getColumnDimension('K')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . $i, 'Barcode')->getColumnDimension('L')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('M' . $i, 'SKU')->getColumnDimension('M')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('N' . $i, 'UPC')->getColumnDimension('N')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('O' . $i, 'EAN')->getColumnDimension('O')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('P' . $i, 'JAN')->getColumnDimension('P')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('Q' . $i, 'ISBN')->getColumnDimension('Q')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('R' . $i, 'MPN')->getColumnDimension('R')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('S' . $i, 'Location')->getColumnDimension('S')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('T' . $i, 'Price')->getColumnDimension('T')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('U' . $i, 'Tax Class ID')->getColumnDimension('U')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('V' . $i, 'Tax Class')->getColumnDimension('V')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('W' . $i, 'Quantity')->getColumnDimension('W')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('X' . $i, 'Minimum Quantity')->getColumnDimension('X')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('Y' . $i, 'Subtract Stock')->getColumnDimension('Y')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('Z' . $i, 'Stock Status ID')->getColumnDimension('Z')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('AA' . $i, 'Stock Status')->getColumnDimension('AA')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('AB' . $i, 'Shipping')->getColumnDimension('AB')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('AC' . $i, 'SEO')->getColumnDimension('AC')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('AD' . $i, 'Date Available')->getColumnDimension('AD')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('AE' . $i, 'Length')->getColumnDimension('AE')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('AF' . $i, 'Length Class ID')->getColumnDimension('AF')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('AG' . $i, 'Length Class')->getColumnDimension('AG')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('AH' . $i, 'Width')->getColumnDimension('AH')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('AI' . $i, 'Height')->getColumnDimension('AI')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('AJ' . $i, 'Weight')->getColumnDimension('AJ')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('AK' . $i, 'Weight Class ID')->getColumnDimension('AK')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('AL' . $i, 'Weight Class')->getColumnDimension('AL')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('AM' . $i, 'Status')->getColumnDimension('AM')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('AN' . $i, 'Sort Order')->getColumnDimension('AN')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('AO' . $i, 'Manufacturer ID')->getColumnDimension('AO')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('AP' . $i, 'Manufacturer')->getColumnDimension('AP')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('AQ' . $i, 'Category ids')->getColumnDimension('AQ')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('AR' . $i, 'Categories')->getColumnDimension('AR')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('AS' . $i, 'Filters (Filter Group :: filter Value)')->getColumnDimension('AS')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('AT' . $i, 'Download')->getColumnDimension('AT')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('AU' . $i, 'Related Products')->getColumnDimension('AU')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('AV' . $i, 'Attributes (attribute_Group::attribute::text)')->getColumnDimension('AV')->setAutoSize(true);

        $col = 47;
        switch ($filter['filter_option']) {
            case 1:
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, 1, 'Options (Option Name 1:Option Value 1~qty~subtract~price~points~weight,Option Value 2~qty~subtract~price~points~weight; other options)')->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))->setAutoSize(true);
                break;
            case 2:
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, 1, 'Option Name')->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))->setAutoSize(true);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, 1, 'Option Value')->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))->setAutoSize(true);
        }

        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, 1, 'Discount (Customer Group id::Quantity::Priority::Price::startdate::Enddate)')->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, 1, 'Special (Customer_group_id::Priority::Price::startdate::Enddate)')->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, 1, 'Sub Images (image1,image2)')->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, 1, 'Reward')->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, 1, 'Viewed')->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, 1, 'Slug')->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, 1, 'The Largest Quantity Can Be Ordered')->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, 1, 'Cost Price')->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))->setAutoSize(true);

        $products = $this->model_multiseller_tool_product_export->getProducts($filter);

        $row = 2;
        $option_counter = 0;
        $option_values_counter = 0;
        $lock_product_writing = false;

        // draw products
        foreach ($products as $product) {

            $store_info = $this->model_setting_store->getStore($product['store_id']);
            if ($store_info) {
                $store = $store_info['name'];
            } else {
                $store = 'default';
            }

            $i++;
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $product['product_id']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $product['language']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $store);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, html_entity_decode($product['name']));
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $product['model']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $i, html_entity_decode($product['description']));
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $i, '');
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $i, $product['meta_description']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $i, $product['meta_keyword']);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $i, $product['tag']);
            if ($filter['filter_pimage'] == 'yes') {
                if ($product['image'] != '' || file_exists($filter['filter_image_path'] . $product['image'])) {
                    $objPHPExcel->getActiveSheet()->setCellValue('K' . $i, self::urlencode($filter['filter_image_path'] . $product['image']));
                } else {
                    $objPHPExcel->getActiveSheet()->setCellValue('K' . $i, '');
                }
            } else {
                $objPHPExcel->getActiveSheet()->setCellValue('K' . $i, self::urlencode($filter['filter_image_path'] . $product['image']));
            }
            $objPHPExcel->getActiveSheet()->setCellValue('L' . $i, $product['barcode']);
            $objPHPExcel->getActiveSheet()->setCellValue('M' . $i, $product['sku']);
            $objPHPExcel->getActiveSheet()->setCellValue('N' . $i, $product['upc']);
            $objPHPExcel->getActiveSheet()->setCellValue('O' . $i, $product['ean']);
            $objPHPExcel->getActiveSheet()->setCellValue('P' . $i, $product['jan']);
            $objPHPExcel->getActiveSheet()->setCellValue('Q' . $i, $product['isbn']);
            $objPHPExcel->getActiveSheet()->setCellValue('R' . $i, $product['mpn']);
            $objPHPExcel->getActiveSheet()->setCellValue('S' . $i, $product['location']);
            $objPHPExcel->getActiveSheet()->setCellValue('T' . $i, $product['price']);
            $objPHPExcel->getActiveSheet()->setCellValue('U' . $i, $product['tax_class_id']);
            $objPHPExcel->getActiveSheet()->setCellValue('V' . $i, $this->model_multiseller_tool_product_export->getTaxClass($product['tax_class_id']));
            $objPHPExcel->getActiveSheet()->setCellValue('W' . $i, $product['quantity']);
            $objPHPExcel->getActiveSheet()->setCellValue('X' . $i, $product['minimum']);
            $objPHPExcel->getActiveSheet()->setCellValue('Y' . $i, $product['subtract']);
            $objPHPExcel->getActiveSheet()->setCellValue('Z' . $i, $product['stock_status_id']);
            $objPHPExcel->getActiveSheet()->setCellValue('AA' . $i, $this->model_multiseller_tool_product_export->getStockstatus($product['stock_status_id'], $product['language_id']));
            $objPHPExcel->getActiveSheet()->setCellValue('AB' . $i, $product['shipping']);
            $objPHPExcel->getActiveSheet()->setCellValue('AC' . $i, $this->model_multiseller_tool_product_export->getKeyword($product['product_id']));
            $objPHPExcel->getActiveSheet()->setCellValue('AD' . $i,  $product['date_available']);
            $objPHPExcel->getActiveSheet()->setCellValue('AE' . $i, $product['length']);
            $objPHPExcel->getActiveSheet()->setCellValue('AF' . $i, $product['length_class_id']);
            $objPHPExcel->getActiveSheet()->setCellValue('AG' . $i, $this->model_multiseller_tool_product_export->getLengthClass($product['length_class_id'], $product['language_id']));
            $objPHPExcel->getActiveSheet()->setCellValue('AH' . $i, $product['width']);
            $objPHPExcel->getActiveSheet()->setCellValue('AI' . $i, $product['height']);
            $objPHPExcel->getActiveSheet()->setCellValue('AJ' . $i, $product['weight']);
            $objPHPExcel->getActiveSheet()->setCellValue('AK' . $i, $product['weight_class_id']);
            $objPHPExcel->getActiveSheet()->setCellValue('AL' . $i, $this->model_multiseller_tool_product_export->getWeightClass($product['weight_class_id'], $product['language_id']));
            $objPHPExcel->getActiveSheet()->setCellValue('AM' . $i, $product['status']);
            $objPHPExcel->getActiveSheet()->setCellValue('AN' . $i, $product['sort_order']);
            $objPHPExcel->getActiveSheet()->setCellValue('AO' . $i, $product['manufacturer_id']);
            $objPHPExcel->getActiveSheet()->setCellValue('AP' . $i, $this->model_multiseller_tool_product_export->getManufacturer($product['manufacturer_id']));
            $categories = $this->MsLoader->MsProduct->getProductCategories($product['product_id']);
            $categories = explode(',', $categories);
            $objPHPExcel->getActiveSheet()->setCellValue('AQ' . $i, (!empty($categories) ? implode(',', $categories) : ''));
            $category_name = array();

            foreach ($categories as $category_id) {
                $category = $this->model_catalog_category->getCategoryPath($category_id);
                if ($category) {
                    $category_name[] = html_entity_decode(($category['path'] ? $category['path'] . ' &gt; ' . $category['name'] : $category['name']));
                }
            }
            $objPHPExcel->getActiveSheet()->setCellValue('AR' . $i, implode(', ', $category_name));

            $filter_data = array();
            $filters = $this->MsLoader->MsProduct->getProductFilters($product['product_id']);
            foreach ($filters as $filter_id) {
                $filter_info = $this->model_catalog_filter->getFilter($filter_id);
                if ($filter_info) {
                    $filter_data[] = html_entity_decode(($filter_info['group'] ? $filter_info['group'] . ' :: ' . $filter_info['name'] : $filter_info['name']));
                }
            }
            $objPHPExcel->getActiveSheet()->setCellValue('AS' . $i, implode(', ', $filter_data));
            $downloads = $this->MsLoader->MsProduct->getProductDownloads($product['product_id']);
            $objPHPExcel->getActiveSheet()->setCellValue('AT' . $i, implode(', ', $downloads));
            $related_products = $this->MsLoader->MsProduct->getProductRelated($product['product_id']);
            $objPHPExcel->getActiveSheet()->setCellValue('AU' . $i, implode(', ', $related_products));

            //GetAttribute

            $attributes = $this->model_multiseller_tool_product_export->getProductAttributes($product['product_id'], $product['language_id']);
            $objPHPExcel->getActiveSheet()->setCellValue('AV' . $i, implode(', ', $attributes));

            $col = 47;
            //options
            $product_options = "";
            if ($filter['filter_option'] != 0) {
                $product_options = $this->model_multiseller_tool_product_export->getNewProductOptions($filter['filter_option'], $product['product_id'], $product['language_id'], $filter['filter_language_id']);

                switch ($filter['filter_option']) {
                    case 1:
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, $row, implode('; ', $product_options));
                        break;
                    case 2:
                        $lock_product_writing = true;
                        $option = $product_options[0];
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, $row, $option['name']);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, $row, $option['values'][0]);
                        $option_value_row = 1;
                        break;
                }
            }

            ///getDiscount
            $discounts = array();
            $product_discounts = $this->MsLoader->MsProduct->getProductDiscounts($product['product_id']);
            foreach ($product_discounts as $pdiscount) {
                $discounts[] = $pdiscount['customer_group_id'] . '::' . $pdiscount['quantity'] . '::' . $pdiscount['priority'] . '::' . $pdiscount['price'] . '::' . $pdiscount['date_start'] . '::' . $pdiscount['date_end'];
            }
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, $row, implode(', ', $discounts));

            //GetSpecial
            $specials = array();
            $product_specials = $this->MsLoader->MsProduct->getProductSpecials($product['product_id']);
            foreach ($product_specials as $special) {
                $specials[] = $special['customer_group_id'] . '::' . $special['priority'] . '::' . $special['price'] . '::' . $special['date_start'] . '::' . $special['date_end'];
            }

            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, $row,  implode(', ', $specials));

            //GET Images
            $images = array();
            $product_images = $this->MsLoader->MsProduct->getProductImages($product['product_id']);
            foreach ($product_images as $pimage) {
                if ($filter['filter_pimage'] == 'yes') {
                    if ($pimage['image'] != '' || file_exists(HTTP_IMAGE . $pimage['image'])) {
                        $images[] = self::urlencode($filter['filter_image_path'] . $pimage['image']);
                    } else {
                        $images[] = '';
                    }
                } else {
                    $images[] = self::urlencode($filter['filter_image_path'] . $pimage['image']);
                }
            }

            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, $row, implode(';', $images));
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, $row,  $product['points']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, $row, $product['viewed']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, $row, $product['slug']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, $row, $product['maximum']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, $row, $product['cost_price']);


            $row++;

            if ($lock_product_writing) {

                foreach ($product_options as $option) {
                    foreach ($option['values'] as $option_value) {
                        // Product ID
                        if ($option_value_row == 1) {
                            $option_value_row++;
                            continue;
                        }

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $product['product_id']);
                        $empty_columns = range(1, 3);

                        for ($counter = 0; $counter < count($empty_columns); $counter++) {
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($empty_columns[$counter], $row, '');
                        }
                        // Product Model
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $product['model']);

                        $empty_columns = range(5, 46);
                        for ($counter = 0; $counter < count($empty_columns); $counter++) {
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($empty_columns[$counter], $row, '');
                        }

                        $option_column = 46;

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$option_column, $row, $option['name']);
                        # code...
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$option_column, $row, $option_value);

                        $empty_columns = range(49, 54);
                        for ($counter = 0; $counter < count($empty_columns); $counter++) {
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($empty_columns[$counter], $row, '');
                        }
                        $row++;
                    }
                }
                $lock_product_writing = false;
            }
            // To synchronize $i in Letters (column-row) with $row(used in advanced options structure)
            $i = $row - 1;
        }

        // download file

        $filter_eformat = $filter['filter_eformat'];

        switch ($filter_eformat) {
            case 'csv':
                $writer_type = 'CSV';
                $file_ext = 'csv';
                break;
            case 'xlsx':
                $writer_type = 'Excel2007';
                $file_ext = 'xlsx';
                break;
            default:
                $writer_type = 'Excel5';
                $file_ext = 'xls';
                break;
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $writer_type);
        $filename = 'product' . time() . '.' . $file_ext;

        if (ob_get_length()) {
            ob_end_clean();
        }

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename=' . $filename);
        header('Cache-Control: max-age=0');

        if (ob_get_length()) {
            ob_end_clean();
        }

        $objWriter->save('php://output');

        exit();
    }


    private function prepareFilterData()
    {
        $filter_seller_id = $this->customer->getId();

        $request = $this->request->request;

        if (!empty($request['filter_language'])) {
            $filter_language_id = $request['filter_language'];
        } else {
            $filter_language_id = $this->config->get('config_language_id');
        }

        if (isset($request['filter_name'])) {
            $filter_name = $request['filter_name'];
        } else {
            $filter_name = null;
        }

        if (isset($request['filter_model'])) {
            $filter_model = $request['filter_model'];
        } else {
            $filter_model = null;
        }

        if (isset($request['filter_price_to'])) {
            $filter_price_to = $request['filter_price_to'];
        } else {
            $filter_price_to = null;
        }

        if (isset($request['filter_price_form'])) {
            $filter_price_form = $request['filter_price_form'];
        } else {
            $filter_price_form = null;
        }

        if (isset($request['filter_quantity_to'])) {
            $filter_quantity_to = $request['filter_quantity_to'];
        } else {
            $filter_quantity_to = null;
        }

        if (isset($request['filter_quantity_form'])) {
            $filter_quantity_form = $request['filter_quantity_form'];
        } else {
            $filter_quantity_form = null;
        }

        if (isset($request['filter_status'])) {
            $filter_status = $request['filter_status'];
        } else {
            $filter_status = null;
        }

        $filter_store = $this->config->get('config_store_id') ? $this->config->get('config_store_id') : 0;

        if (isset($request['filter_stock_status'])) {
            $filter_stock_status = $request['filter_stock_status'];
        } else {
            $filter_stock_status = null;
        }

        if (!empty($request['filter_start'])) {
            $filter_start = $request['filter_start'];
        } else {
            $filter_start = 0;
        }

        if (!empty($request['filter_limit'])) {
            $filter_limit = $request['filter_limit'];
        } else {
            $filter_limit = $this->model_catalog_product->countProducts();
        }

        if (isset($request['filter_categories'])) {
            $filter_categories = $request['filter_categories'];
        } else {
            $filter_categories = null;
        }

        if (isset($request['filter_manufacturer'])) {
            $filter_manufacturer = $request['filter_manufacturer'];
        } else {
            $filter_manufacturer = null;
        }

        if (isset($request['filter_product_id'])) {
            $filter_product_id = $request['filter_product_id'];
        } else {
            $filter_product_id = null;
        }

        if (isset($request['filter_endproduct_id'])) {
            $filter_endproduct_id = $request['filter_endproduct_id'];
        } else {
            $filter_endproduct_id = null;
        }

        $filter_pimage = 'no';

        if (isset($request['filter_eformat'])) {
            $filter_eformat = $request['filter_eformat'];
        } else {
            $filter_eformat = null;
        }

        if (isset($request['filter_option'])) {
            $filter_option = $request['filter_option'];
        } else {
            $filter_option = null;
        }

        if (isset($request['filter_file_format'])) {
            $filter_file_format = $request['filter_file_format'];
        } else {
            $filter_file_format = null;
        }

        if (isset($request['filter_image_path'])) {
            $filter_image_path = $request['filter_image_path'];
        } else {
            $filter_image_path = null;
        }

        if ($filter_image_path == 0) {
            $filter_image_path = rtrim(Filesystem::getUrl('image/'), '/') . '/';
        } else {
            $filter_image_path = '';
        }

        return array(
            'filter_seller_id'     => $filter_seller_id,
            'filter_option'        => $filter_option,
            'filter_file_format'   => $filter_file_format,
            'filter_image_path'    => $filter_image_path,
            'filter_name'          => $filter_name,
            'filter_model'         => $filter_model,
            'filter_price_to'      => $filter_price_to,
            'filter_price_form'    => $filter_price_form,
            'filter_quantity_to'   => $filter_quantity_to,
            'filter_quantity_form' => $filter_quantity_form,
            'filter_status'        => $filter_status,
            'filter_language_id'   => $filter_language_id,
            'filter_store'         => $filter_store,
            'filter_categories'    => $filter_categories,
            'filter_manufacturer'  => $filter_manufacturer,
            'start'                => $filter_start,
            'limit'                => $filter_limit,
            'filter_stock_status'  => $filter_stock_status,
            'filter_product_id'    => $filter_product_id,
            'filter_eformat'       => $filter_eformat,
            'filter_pimage'        => $filter_pimage,
            'filter_endproduct_id' => $filter_endproduct_id,
        );
    }

    private static function urlencode($path)
    {
        $url =  implode("/", array_map("rawurlencode", explode("/", $path)));
        return str_replace('%3A', ':', $url);
    }
}
