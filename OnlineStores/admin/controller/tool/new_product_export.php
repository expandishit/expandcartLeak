<?php

header('Cache-Control: no-cache, no-store');
ini_set('memory_limit', '2048M');
ini_set('max_execution_time', 3600);
ini_set('error_reporting', E_ALL);

include DIR_SYSTEM.'library/PHPExcel.php';

class ControllerToolNewProductExport extends Controller
{
    private $plan_id = PRODUCTID;

    public function __construct($registry) {
        parent::__construct($registry);
        $this->load->model('plan/trial');
        $trial = $this->model_plan_trial->getLastTrial();
        if ($trial){
            $this->plan_id = $trial['plan_id'];
        }
    }

	public function index(){
        if( $this->plan_id  == 3){
            return $this->response->redirect($this->url->link('catalog/product', '', true));
        }

		$this->load->language('catalog/product');
		
		$this->load->language('tool/product_export');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/product');

		$this->data['tab_export'] = $this->language->get('tab_export');
		
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

		if (isset($this->request->get['filter_price'])) {
			$this->data['filter_price'] = $this->request->get['filter_price'];
		} else {
			$this->data['filter_price'] = null;
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
		
		if (isset($this->request->get['filter_limit'])) {
			$this->data['filter_limit'] = $this->request->get['filter_limit'];
		} else {
			$this->data['filter_limit'] = $this->model_catalog_product->countProducts();
		}
		
		if (isset($this->error['warning'])){
			$this->data['error_warning'] = $this->error['warning'];
		}else{
			$this->data['error_warning'] = '';
		}
		
		$url = '';
		
		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		);

		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('catalog/product', 'token=' . $this->session->data['token'] . $url, 'SSL'),
			'separator' => ' :: '
		);
		
		$this->load->model('localisation/language');
		$this->data['languages'] = $this->model_localisation_language->getLanguages();
		
		$this->load->model('setting/store');
		$this->data['stores'] = $this->model_setting_store->getStores();
		
		$this->load->model('localisation/stock_status');
		$this->data['stock_statuses'] = $this->model_localisation_stock_status->getStockStatuses();
		
		$this->load->model('catalog/category');
		$this->data['categories'] = $this->model_catalog_category->getCategories(array());
		
		$this->load->model('catalog/manufacturer');
		$this->data['manufacturers'] = $this->model_catalog_manufacturer->getManufacturers();
		
		$this->load->model('tool/product_export');
		
		$this->data['maxproduct_id'] = $this->model_tool_product_export->getmaxproducts();
		
		$this->data['miniproduct_id'] = $this->model_tool_product_export->getminiproducts();
        
        $this->data['new_product_export'] = true;

		$this->template = 'tool/product_export.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);
		$this->response->setOutput($this->render());
    }
    
    public function export(){
        if($this->plan_id == 3){
            return $this->response->redirect($this->url->link('catalog/product', '', true));
        }
		if(!empty($this->request->get['filter_language'])){
			$language_id = $this->request->get['filter_language'];
		}else{
			$language_id = $this->config->get('config_language_id');
		}
		
		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = null;
		}

		if (isset($this->request->get['filter_model'])) {
			$filter_model = $this->request->get['filter_model'];
		} else {
			$filter_model = null;
		}

		if (isset($this->request->get['filter_price_to'])) {
			$filter_price_to = $this->request->get['filter_price_to'];
		} else {
			$filter_price_to = null;
		}
		
		if (isset($this->request->get['filter_price_form'])) {
			$filter_price_form = $this->request->get['filter_price_form'];
		} else {
			$filter_price_form = null;
		}

		if (isset($this->request->get['filter_quantity_to'])) {
			$filter_quantity_to = $this->request->get['filter_quantity_to'];
		} else {
			$filter_quantity_to = null;
		}
		
		if (isset($this->request->get['filter_quantity_form'])) {
			$filter_quantity_form = $this->request->get['filter_quantity_form'];
		} else {
			$filter_quantity_form = null;
		}

		if (isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		} else {
			$filter_status = null;
		}
		
		$filter_store = $this->config->get('config_store_id')?$this->config->get('config_store_id'):0;
		
		if (isset($this->request->get['filter_stock_status'])) {
			$filter_stock_status = $this->request->get['filter_stock_status'];
		} else {
			$filter_stock_status = null;
		}
		
		if (!empty($this->request->get['filter_start'])) {
			$filter_start = $this->request->get['filter_start'];
		} else {
			$filter_start = 0;
		}
		
		if (!empty($this->request->get['filter_limit'])) {
			$filter_limit = $this->request->get['filter_limit'];
		} else {
			$filter_limit = $this->model_catalog_product->countProducts();
		}
		
		if (isset($this->request->get['filter_categories'])) {
			$filter_categories = $this->request->get['filter_categories'];
		} else {
			$filter_categories = null;
		}
		
		if (isset($this->request->get['filter_manufacturer'])) {
			$filter_manufacturer = $this->request->get['filter_manufacturer'];
		} else {
			$filter_manufacturer = null;
		}
		
		if (isset($this->request->get['filter_product_id'])) {
			$filter_product_id = $this->request->get['filter_product_id'];
		} else {
			$filter_product_id = null;
		}
		
		if (isset($this->request->get['filter_endproduct_id'])) {
			$filter_endproduct_id = $this->request->get['filter_endproduct_id'];
		} else {
			$filter_endproduct_id = null;
		}
		

        $filter_pimage = 'no';

		
		if (isset($this->request->get['filter_eformat'])) {
			$filter_eformat = $this->request->get['filter_eformat'];
		} else {
			$filter_eformat = null;
		}

		if (isset($this->request->get['filter_option'])) {
			$filter_option = $this->request->get['filter_option'];
		} else {
			$filter_option = null;
		}

		if (isset($this->request->get['filter_file_format'])) {
			$filter_file_format = $this->request->get['filter_file_format'];
		} else {
			$filter_file_format = null;
		}
		
		if (isset($this->request->get['filter_image_path'])) {
			$filter_image_path = $this->request->get['filter_image_path'];
		} else {
			$filter_image_path = null;
		}

		$filter_data=array(
			'filter_name'	  		=> $filter_name,
			'filter_model'	  		=> $filter_model,
			'filter_price_to'	  	=> $filter_price_to,
			'filter_price_form'	  	=> $filter_price_form,
			'filter_quantity_to' 	=> $filter_quantity_to,
			'filter_quantity_form' 	=> $filter_quantity_form,
			'filter_status'   		=> $filter_status,
		    'filter_language_id'	=> $language_id,
		    'filter_store'			=> $filter_store,
		    'filter_categories'		=> $filter_categories,
		    'filter_manufacturer'	=> $filter_manufacturer,
		    'start'           		=> $filter_start,
		    'limit'           		=> $filter_limit,
		    'filter_stock_status'   => $filter_stock_status,
			'filter_product_id'		=> $filter_product_id,
			'filter_eformat'  		=> $filter_eformat,
		    'filter_pimage'  		=> $filter_pimage,
			'filter_endproduct_id'	=> $filter_endproduct_id,
		);
		
		$this->load->model('catalog/product');
		$this->load->model('tool/product_export');
		$this->load->model('setting/store');

		$imported_file_structure = "default";
		// Check if user selects expandcart file structure
		// Or another file structure

		
		if($filter_file_format == "user_format"){
			$imported_file = $this->model_tool_product_export->getLastImportedProductFile();	
			if(count($imported_file) > 0){
				$imported_file_structure = $imported_file['file_mapping'];
			}
		}

		$image_path = "";
		if($filter_image_path == 0){
			$image_path = rtrim(\Filesystem::getUrl('image/'), '/') . '/';
		}

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
		$i=1;

		if($imported_file_structure == "default"){
			
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, 'Product ID')->getColumnDimension('A')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, 'Language')->getColumnDimension('B')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Store')->getColumnDimension('C')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, 'Name')->getColumnDimension('D')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, 'Model')->getColumnDimension('E')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, 'Description')->getColumnDimension('F')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('G'.$i, 'Meta Title')->getColumnDimension('G')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('H'.$i, 'Meta Description')->getColumnDimension('H')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('I'.$i, 'Meta Keyword')->getColumnDimension('I')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('J'.$i, 'Tag')->getColumnDimension('J')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('K'.$i, 'Main Image')->getColumnDimension('K')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->setCellValue('L'.$i, 'Barcode')->getColumnDimension('L')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('M'.$i, 'SKU')->getColumnDimension('M')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('N'.$i, 'UPC')->getColumnDimension('N')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('O'.$i, 'EAN')->getColumnDimension('O')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('P'.$i, 'JAN')->getColumnDimension('P')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('Q'.$i, 'ISBN')->getColumnDimension('Q')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('R'.$i, 'MPN')->getColumnDimension('R')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('S'.$i, 'Location')->getColumnDimension('S')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('T'.$i, 'Price')->getColumnDimension('T')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('U'.$i, 'Tax Class ID')->getColumnDimension('U')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('V'.$i, 'Tax Class')->getColumnDimension('V')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('W'.$i, 'Quantity')->getColumnDimension('W')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('X'.$i, 'Minimum Quantity')->getColumnDimension('X')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('Y'.$i, 'Subtract Stock')->getColumnDimension('Y')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('Z'.$i, 'Stock Status ID')->getColumnDimension('Z')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('AA'.$i, 'Stock Status')->getColumnDimension('AA')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('AB'.$i, 'Shipping')->getColumnDimension('AB')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('AC'.$i, 'SEO')->getColumnDimension('AC')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('AD'.$i, 'Date Available')->getColumnDimension('AD')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('AE'.$i, 'Length')->getColumnDimension('AE')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('AF'.$i, 'Length Class ID')->getColumnDimension('AF')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('AG'.$i, 'Length Class')->getColumnDimension('AG')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('AH'.$i, 'Width')->getColumnDimension('AH')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('AI'.$i, 'Height')->getColumnDimension('AI')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('AJ'.$i, 'Weight')->getColumnDimension('AJ')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('AK'.$i, 'Weight Class ID')->getColumnDimension('AK')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('AL'.$i, 'Weight Class')->getColumnDimension('AL')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('AM'.$i, 'Status')->getColumnDimension('AM')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('AN'.$i, 'Sort Order')->getColumnDimension('AN')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('AO'.$i, 'Manufacturer ID')->getColumnDimension('AO')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('AP'.$i, 'Manufacturer')->getColumnDimension('AP')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('AQ'.$i, 'Category ids')->getColumnDimension('AQ')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('AR'.$i, 'Categories')->getColumnDimension('AR')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('AS'.$i, 'Filters (Filter Group :: filter Value)')->getColumnDimension('AS')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('AT'.$i, 'Download')->getColumnDimension('AT')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('AU'.$i, 'Related Products')->getColumnDimension('AU')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('AV'.$i, 'Attributes (attribute_Group::attribute::text)')->getColumnDimension('AV')->setAutoSize(true);

			$col = 47;
			switch($filter_option){
				case 1:
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, 1,'Options (Option Name 1:Option Value 1~qty~subtract~price~points~weight,Option Value 2~qty~subtract~price~points~weight; other options)')->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))->setAutoSize(true);
					break;
				case 2:
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, 1,'Option Name')->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))->setAutoSize(true);
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, 1,'Option Value')->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))->setAutoSize(true);
			}
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, 1, 'Discount (Customer Group id::Quantity::Priority::Price::startdate::Enddate)')->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, 1, 'Special (Customer_group_id::Priority::Price::startdate::Enddate)')->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, 1, 'Sub Images (image1,image2)')->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, 1, 'Reward')->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, 1, 'Viewed')->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, 1, 'Slug')->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, 1, 'The Largest Quantity Can Be Ordered')->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, 1, 'Cost Price')->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, 1, 'Unlimited Quantity (1 => true , 0 => false)')->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))->setAutoSize(true);

			// load  Option SKU and export it when filter option equal 2

            $productsoptions_sku_status = $this->config->get('productsoptions_sku_status');
            if ($productsoptions_sku_status) {
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, 1, 'SKU App Options (Option Ids::Number Options::SKU::Quantity::Barcode::Price; Another Options ) ')->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))->setAutoSize(true);
            }

			$products = $this->model_tool_product_export->getProducts($filter_data);

			$row = 2;
			$option_counter = 0;
			$option_values_counter = 0;
			$lock_product_writing = false;
				
			foreach($products as $product){
				
				$storeinfo = $this->model_setting_store->getStore($product['store_id']);
				if($storeinfo){
					$store = $storeinfo['name'];
				}else{
					$store = 'default';
				}
			
				$i++;
				$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $product['product_id']);
				$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $product['language']);
				$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $store);
				$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, html_entity_decode($product['name']));
				$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $product['model']);
				$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, html_entity_decode($product['description']));
				$objPHPExcel->getActiveSheet()->setCellValue('G'.$i, '');
				$objPHPExcel->getActiveSheet()->setCellValue('H'.$i, $product['meta_description']);
				$objPHPExcel->getActiveSheet()->setCellValue('I'.$i, $product['meta_keyword']);
				$objPHPExcel->getActiveSheet()->setCellValue('J'.$i, $product['tag']);
				if($filter_pimage == 'yes'){
					if($product['image'] != '' || file_exists($image_path.$product['image'])){
						$objPHPExcel->getActiveSheet()->setCellValue('K'.$i, self::urlencode($image_path.$product['image']));
					}
					else{
						$objPHPExcel->getActiveSheet()->setCellValue('K'.$i, '');
					}
					
				}else{
                    if ($product['image'] != ''  || \Filesystem::isExists($image_path.$product['image'])){
                        $objPHPExcel->getActiveSheet()->setCellValue('K'.$i, self::urlencode($image_path.$product['image']));
                    }else{
                        $objPHPExcel->getActiveSheet()->setCellValue('K'.$i, '');
                    }
				}
                $objPHPExcel->getActiveSheet()->setCellValue('L'.$i, $product['barcode']);
				$objPHPExcel->getActiveSheet()->setCellValueExplicit('M'.$i, $product['sku'],PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->setCellValue('N'.$i, $product['upc']);
				$objPHPExcel->getActiveSheet()->setCellValue('O'.$i, $product['ean']);
				$objPHPExcel->getActiveSheet()->setCellValue('P'.$i, $product['jan']);
				$objPHPExcel->getActiveSheet()->setCellValue('Q'.$i, $product['isbn']);
				$objPHPExcel->getActiveSheet()->setCellValue('R'.$i, $product['mpn']);
				$objPHPExcel->getActiveSheet()->setCellValue('S'.$i, $product['location']);
				$objPHPExcel->getActiveSheet()->setCellValue('T'.$i, $product['price']);
				$objPHPExcel->getActiveSheet()->setCellValue('U'.$i, $product['tax_class_id']);
				$objPHPExcel->getActiveSheet()->setCellValue('V'.$i, $this->model_tool_product_export->getTaxClass($product['tax_class_id']));
				$objPHPExcel->getActiveSheet()->setCellValue('W'.$i, $product['quantity']);
				$objPHPExcel->getActiveSheet()->setCellValue('X'.$i, $product['minimum']);
				$objPHPExcel->getActiveSheet()->setCellValue('Y'.$i, $product['subtract']);
				$objPHPExcel->getActiveSheet()->setCellValue('Z'.$i, $product['stock_status_id']);
				$objPHPExcel->getActiveSheet()->setCellValue('AA'.$i, $this->model_tool_product_export->getStockstatus($product['stock_status_id'],$product['language_id']));
				$objPHPExcel->getActiveSheet()->setCellValue('AB'.$i, $product['shipping']);
				$objPHPExcel->getActiveSheet()->setCellValue('AC'.$i, $this->model_tool_product_export->getKeyword($product['product_id']));
				$objPHPExcel->getActiveSheet()->setCellValue('AD'.$i,  $product['date_available']);
				$objPHPExcel->getActiveSheet()->setCellValue('AE'.$i, $product['length']);
				$objPHPExcel->getActiveSheet()->setCellValue('AF'.$i, $product['length_class_id']);
				$objPHPExcel->getActiveSheet()->setCellValue('AG'.$i, $this->model_tool_product_export->getLengthClass($product['length_class_id'],$product['language_id']));
				$objPHPExcel->getActiveSheet()->setCellValue('AH'.$i, $product['width']);
				$objPHPExcel->getActiveSheet()->setCellValue('AI'.$i, $product['height']);
				$objPHPExcel->getActiveSheet()->setCellValue('AJ'.$i, $product['weight']);
				$objPHPExcel->getActiveSheet()->setCellValue('AK'.$i, $product['weight_class_id']);
				$objPHPExcel->getActiveSheet()->setCellValue('AL'.$i, $this->model_tool_product_export->getWeightClass($product['weight_class_id'],$product['language_id']));
				$objPHPExcel->getActiveSheet()->setCellValue('AM'.$i, $product['status']);
				$objPHPExcel->getActiveSheet()->setCellValue('AN'.$i, $product['sort_order']);
				$objPHPExcel->getActiveSheet()->setCellValue('AO'.$i, $product['manufacturer_id']);
				$objPHPExcel->getActiveSheet()->setCellValue('AP'.$i, $this->model_tool_product_export->getManufacturer($product['manufacturer_id']));
				$categories = $this->model_catalog_product->getProductCategories($product['product_id']);
				$objPHPExcel->getActiveSheet()->setCellValue('AQ'.$i, (!empty($categories) ? implode(',',$categories) : ''));
				$categoryname = array();
				$this->load->model('catalog/category');
				$this->load->model('catalog/filter');
				foreach($categories as $category_id){
				$categoryinfo = $this->model_tool_product_export->getcategory($category_id,$product['language_id']);
				if($categoryinfo){
					$categoryname[] = html_entity_decode(($categoryinfo['path'] ? $categoryinfo['path']. ' &gt; ' . $categoryinfo['name'] : $categoryinfo['name']));
				}
				}
				$objPHPExcel->getActiveSheet()->setCellValue('AR'.$i, implode(', ',$categoryname));
				
				$filterdata=array();
				$filteres = $this->model_catalog_product->getProductFilters($product['product_id']);
				foreach($filteres as $filter_id){
					$filter_info = $this->model_catalog_filter->getFilter($filter_id);
					if($filter_info){
						$filterdata[] = html_entity_decode(($filter_info['group'] ? $filter_info['group']. ' :: ' . $filter_info['name'] : $filter_info['name']));
					}
				}
				$objPHPExcel->getActiveSheet()->setCellValue('AS'.$i, implode(', ',$filterdata));
				$downloads = $this->model_catalog_product->getProductDownloads($product['product_id']);
				$objPHPExcel->getActiveSheet()->setCellValue('AT'.$i, implode(', ',$downloads));
				$realated = $this->model_catalog_product->getProductRelated($product['product_id']);
				$objPHPExcel->getActiveSheet()->setCellValue('AU'.$i, implode(', ',$realated));
				
				//GetAttribute
				$this->load->model('catalog/attribute');
				$this->load->model('catalog/attribute_group');
				$attributes = $this->model_tool_product_export->getProductAttributes($product['product_id'],$product['language_id']);
				$objPHPExcel->getActiveSheet()->setCellValue('AV'.$i, implode(', ',$attributes));
				
				$col = 47;
				//options
				$productoptions = "";
				if($filter_option != 0){
					$productoptions = $this->model_tool_product_export->getNewProductOptions($filter_option,$product['product_id'],$product['language_id'],$language_id);

					switch($filter_option){
						case 1:
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, $row, implode('; ',$productoptions));
							break;
						case 2:
							$lock_product_writing = true;
							$option = $productoptions[0];
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, $row,$option['name']);
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, $row,$option['values'][0]);
							$option_value_row = 1;
							break;
					}			
				}
				
				///getDiscount
				$discounts=array();
				$productdiscounts = $this->model_catalog_product->getProductDiscounts($product['product_id']);
				foreach($productdiscounts as $pdiscount){
					$discounts[]= $pdiscount['customer_group_id'].'::'.$pdiscount['quantity'].'::'.$pdiscount['priority'].'::'.$pdiscount['price'].'::'.$pdiscount['date_start'].'::'.$pdiscount['date_end'];
				}
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, $row, implode(', ',$discounts));
				
				//GetSpecial
				$specials=array();
				$productspecials = $this->model_catalog_product->getProductSpecials($product['product_id']);
				foreach($productspecials as $pspecial){
					$specials[]= $pspecial['customer_group_id'].'::'.$pspecial['priority'].'::'.$pspecial['price'].'::'.$pspecial['date_start'].'::'.$pspecial['date_end'];
				}
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, $row,  implode(', ',$specials));
				
				//GET Images
				$images=array();
				$productimages = $this->model_catalog_product->getProductImages($product['product_id']);
				foreach($productimages as $pimage){
					
					if($filter_pimage == 'yes'){
						if($pimage['image'] != '' || file_exists(HTTP_IMAGE.$pimage['image'])){
							$images[]= self::urlencode($image_path.$pimage['image']);
						}else{
							$images[]= '';
						}
					}else{
						$images[]= self::urlencode($image_path.$pimage['image']);
					}
					
				}

				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, $row, implode(';',$images));
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, $row,  $product['points']);
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, $row, $product['viewed']);
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, $row, $product['slug']);
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, $row, $product['maximum']);
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, $row, $product['cost_price']);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, $row, $product['unlimited']);

                // check if app installed  and option == advanced option
                if ($productsoptions_sku_status) {
                    $custom_product_options =array();
                    $product_variation_sku = $this->model_catalog_product->getProductVariationSku($product['product_id']);
                    foreach ($product_variation_sku as $option){
                        $custom_product_options[] = $option['option_value_ids']."::".$option['num_options']."::".$option['product_sku']."::".$option['product_quantity']."::".$option['product_barcode']."::".$option['product_price'];
                    }
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, $row,  implode('; ',$custom_product_options));

                }

				$row++;

				if($lock_product_writing){
					
					foreach ($productoptions as $option) {
						foreach ($option['values'] as $option_value) {
							// Product ID
							if($option_value_row == 1){
								$option_value_row++;
								continue;
							}

							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $row,$product['product_id']);
							$empty_columns = range(1,3);
							
							for($counter=0; $counter<count($empty_columns);$counter++){
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($empty_columns[$counter], $row,'');
							}
							// Product Model
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $row,$product['model']);
													
							$empty_columns = range(5,46);
							for($counter=0; $counter<count($empty_columns);$counter++){
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($empty_columns[$counter], $row,'');
							}
							
							$option_column = 47;

							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$option_column, $row,$option['name']);
							# code...
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$option_column, $row,$option_value);	
							
							$empty_columns = range(50,55);
							for($counter=0; $counter<count($empty_columns);$counter++){
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($empty_columns[$counter], $row,'');
							}
							$row++;
						}
					}
					$lock_product_writing = false;
				}
				// To synchronize $i in Letters (column-row) with $row(used in advanced options structure)
				$i = $row-1;

			}

		}
		else{
			$imported_file_structure = json_decode($imported_file_structure,true);
			$expandcart_file_structure = file_get_contents(__DIR__.'/product_file_structure.json');
			$expandcart_file_structure = json_decode($expandcart_file_structure,true);

			$col = 0;
			$row = 1;

			foreach ($imported_file_structure as $key => $value) {
				# code...
				if($value != "0"){
					if($key == 'AV'){
						switch($filter_option){
							case 1:
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, 1,'Options (Option Name 1:Option Value 1,Option Value 2; other options)')->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))->setAutoSize(true);
								break;
							case 2:
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, 1,'Option Name')->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))->setAutoSize(true);
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$col, 1,'Option Value')->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))->setAutoSize(true);
								break;
						}
						$col++;
					}
					else{
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $expandcart_file_structure[$key])->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))->setAutoSize(true);
						$col++;
					}
					
				}
			}
			$row = 2;
			$products = $this->model_tool_product_export->getProducts($filter_data);
			$product_id_cell_pos = -1;
			$model_cell_pos = 0;
			$option_name_cell_pos = 0;
			$last_cell_pos = 0;
			foreach($products as $product){
				$col = 0;
				if(!is_null($imported_file_structure['C']) && $imported_file_structure['C'] != "0"){
					$storeinfo = $this->model_setting_store->getStore($product['store_id']);
					if($storeinfo){
						$store = $storeinfo['name'];
					}else{
						$store = 'default';
					}
				}
				
				if($imported_file_structure['A'] != "0"){
					$product_id_cell_pos = $col;
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row,$product['product_id']);
				}
				if(!is_null($imported_file_structure['B']) && $imported_file_structure['B'] != "0"){	
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, $product['language']);
				}
				if(!is_null($imported_file_structure['C']) && $imported_file_structure['C'] != "0"){
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, $store);
				}
				if($imported_file_structure['D'] != "0"){
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, html_entity_decode($product['name']));
				}
				if($imported_file_structure['E'] != "0"){
					$model_cell_pos = $col;
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, $product['model']);
				}

				if($imported_file_structure['F'] != "0"){
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, html_entity_decode($product['description']));
				}

				if($imported_file_structure['G'] != "0"){
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, '');
				}

				if($imported_file_structure['H'] != "0"){
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, $product['meta_description']);
				}

				if($imported_file_structure['I'] != "0"){
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, $product['meta_keyword']);
				}

				if($imported_file_structure['J'] != "0"){
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, $product['tag']);
				}
				
				if($imported_file_structure['K'] != "0"){
					if($filter_pimage == 'yes'){
						if($product['image'] != '' || file_exists($image_path.$product['image'])){
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, self::urlencode($image_path.$product['image']));
						}
						else{
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, '');
						}
						
					}else{
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, self::urlencode($image_path.$product['image']));
					}
				}

                if($imported_file_structure['L'] != "0"){
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, $product['barcode']);
                }

				if($imported_file_structure['M'] != "0"){
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, $product['sku']);
				}

				if($imported_file_structure['N'] != "0"){
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, $product['upc']);
				}
				
				if($imported_file_structure['O'] != "0"){
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, $product['ean']);
				}

				if($imported_file_structure['P'] != "0"){
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, $product['jan']);
				}

				if($imported_file_structure['Q'] != "0"){
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, $product['isbn']);
				}

				if($imported_file_structure['R'] != "0"){
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, $product['mpn']);
				}
				
				if($imported_file_structure['S'] != "0"){
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, $product['location']);
				}

				if($imported_file_structure['T'] != "0"){
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, $product['price']);
				}
				
				if($imported_file_structure['U'] != "0"){
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, $product['tax_class_id']);
				}
			
				if($imported_file_structure['V'] != "0"){
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, $this->model_tool_product_export->getTaxClass($product['tax_class_id']));
				}
				
				if($imported_file_structure['W'] != "0"){
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, $product['quantity']);
				}
				
				if($imported_file_structure['X'] != "0"){
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, $product['minimum']);
				}

				if($imported_file_structure['Y'] != "0"){
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, $product['subtract']);
				}
				
				if($imported_file_structure['Z'] != "0"){
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, $product['stock_status_id']);
				}

				if($imported_file_structure['AA'] != "0"){
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, $this->model_tool_product_export->getStockstatus($product['stock_status_id'],$language_id));
				}
				
				if($imported_file_structure['AB'] != "0"){
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, $product['shipping']);
				}

				if($imported_file_structure['AC'] != "0"){
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, $this->model_tool_product_export->getKeyword($product['product_id']));
				}

				if($imported_file_structure['AD'] != "0"){
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row,  $product['date_available']);
				}

				if($imported_file_structure['AE'] != "0"){
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, $product['length']);
				}

				if($imported_file_structure['AF'] != "0"){
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, $product['length_class_id']);
				}

				if($imported_file_structure['AG'] != "0"){
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, $this->model_tool_product_export->getLengthClass($product['length_class_id'],$language_id));
				}

				if($imported_file_structure['AH'] != "0"){
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, $product['width']);
				}

				if($imported_file_structure['AI'] != "0"){
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, $product['height']);
				}

				if($imported_file_structure['AJ'] != "0"){
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, $product['weight']);
				}

				if($imported_file_structure['AK'] != "0"){
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, $product['weight_class_id']);
				}

				if($imported_file_structure['AL'] != "0"){
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, $this->model_tool_product_export->getWeightClass($product['weight_class_id'],$language_id));
				}

				if($imported_file_structure['AM'] != "0"){
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, $product['status']);
				}

				if($imported_file_structure['AN'] != "0"){
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, $product['sort_order']);
				}

				if($imported_file_structure['AO'] != "0"){
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, $product['manufacturer_id']);
				}

				if($imported_file_structure['AP'] != "0"){
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, $this->model_tool_product_export->getManufacturer($product['manufacturer_id']));
				}
				

				if($imported_file_structure['AQ'] != "0"){
					$categories = $this->model_catalog_product->getProductCategories($product['product_id']);
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, (!empty($categories) ? implode(',',$categories) : ''));
					$categoryname = array();
					$this->load->model('catalog/category');
					$this->load->model('catalog/filter');
					foreach($categories as $category_id){
					$categoryinfo = $this->model_catalog_category->getcategory($category_id);
					if($categoryinfo){
						$categoryname[] = html_entity_decode(($categoryinfo['path'] ? $categoryinfo['path']. ' &gt; ' . $categoryinfo['name'] : $categoryinfo['name']));
					}
					}
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, implode(', ',$categoryname));
				}
				
				if($imported_file_structure['AS'] != "0"){
					$filterdata=array();
					$filteres = $this->model_catalog_product->getProductFilters($product['product_id']);
					foreach($filteres as $filter_id){
						$filter_info = $this->model_catalog_filter->getFilter($filter_id);
						if($filter_info){
							$filterdata[] = html_entity_decode(($filter_info['group'] ? $filter_info['group']. ' :: ' . $filter_info['name'] : $filter_info['name']));
						}
					}
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, implode(', ',$filterdata));
				}

				if($imported_file_structure['AT'] != "0"){
					$downloads = $this->model_catalog_product->getProductDownloads($product['product_id']);
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, implode(', ',$downloads));
				}

				if($imported_file_structure['AU'] != "0"){
					$realated = $this->model_catalog_product->getProductRelated($product['product_id']);
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, implode(', ',$realated));
				}
				
				//GetAttribute
				if($imported_file_structure['AV'] != "0"){
					$this->load->model('catalog/attribute');
					$this->load->model('catalog/attribute_group');
					$attributes = $this->model_tool_product_export->getProductAttributes($product['product_id'],$language_id);
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, implode(', ',$attributes));
				}


				//options
				if($filter_option != 0){
					if($imported_file_structure['AW'] != "0"){
				
						$productoptions = $this->model_tool_product_export->getNewProductOptions($filter_option,$product['product_id'],$product['language_id'],$language_id);
						
						switch($filter_option){
							case 1:
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, implode('; ',$productoptions));
								break;
							case 2:
								$option_name_cell_pos = $col;
								$lock_product_writing = true;
								$option = $productoptions[0];
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row,$option['name']);
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row,$option['values'][0]);
								$option_value_row = 1;
								break;
						}
					}			
				}

				switch (count($imported_file_structure)) {
					case 52:
						# code...
						$discounts_column = "AW";
						$specials_column = "AX";
						$sub_images_column = "AY";
						$reward_column = "AZ";
						$views_column = "BA";
						break;
					case 53:
						# code...
						$discounts_column = "AX";
						$specials_column = "AY";
						$sub_images_column = "AZ";
						$reward_column = "BA";
						$views_column = "BB";
						
						break;
					case 54:
						# code...
						$discounts_column = "AY";
						$specials_column = "AZ";
						$sub_images_column = "BA";
						$reward_column = "BB";
						$views_column = "BC";
						break;
				}

				///getDiscount
				if($imported_file_structure[$discounts_column] != "0"){
					
					$discounts=array();
					$productdiscounts = $this->model_catalog_product->getProductDiscounts($product['product_id']);
					foreach($productdiscounts as $pdiscount){
						$discounts[]= $pdiscount['customer_group_id'].'::'.$pdiscount['quantity'].'::'.$pdiscount['priority'].'::'.$pdiscount['price'].'::'.$pdiscount['date_start'].'::'.$pdiscount['date_end'];
					}
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, implode(', ',$discounts));
				}	
				//GetSpecial
				if($imported_file_structure[$specials_column] != "0"){
					$specials=array();
					$productspecials = $this->model_catalog_product->getProductSpecials($product['product_id']);
					foreach($productspecials as $pspecial){
						$specials[]= $pspecial['customer_group_id'].'::'.$pspecial['priority'].'::'.$pspecial['price'].'::'.$pspecial['date_start'].'::'.$pspecial['date_end'];
					}
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, implode(', ',$specials));
				}	
				//GET Images
				if($imported_file_structure[$sub_images_column] != "0"){
					$images=array();
					$productimages = $this->model_catalog_product->getProductImages($product['product_id']);
					foreach($productimages as $pimage){
						
						if($filter_pimage == 'yes'){
							if($pimage['image'] != '' || file_exists($image_path.$pimage['image'])){
								$images[]= self::urlencode($image_path.$pimage['image']);
							}else{
								$images[]= '';
							}
						}else{
							$images[]= self::urlencode($image_path.$pimage['image']);
						}
						
					}
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, implode(';',$images));
				}

				if($imported_file_structure[$reward_column] != "0"){
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, $product['points']);
				}
				if($imported_file_structure[$views_column] != "0"){
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, $product['viewed']);
				}
				
				$last_cell_pos = $col;
				$row++;

				if($lock_product_writing){
					
					foreach ($productoptions as $option) {
						foreach ($option['values'] as $option_value) {					
							// Product ID
							if($option_value_row == 1){
								$option_value_row++;
								continue;
							}

							if($product_id_cell_pos == 0){
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $row,$product['product_id']);
								$empty_columns = range($product_id_cell_pos+1,$model_cell_pos);						
							}
							elseif($product_id_cell_pos > 0){
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($product_id_cell_pos, $row,$product['product_id']);
								$empty_columns = range(0,$product_id_cell_pos);	
							}
							for($counter=0; $counter<count($empty_columns);$counter++){
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($empty_columns[$counter], $row,'');
							}

							if($product_id_cell_pos > 0){
								$empty_columns = range($product_id_cell_pos+1,$model_cell_pos);	
								for($counter=0; $counter<count($empty_columns);$counter++){
									$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($empty_columns[$counter], $row,'');
								}
							}
							// Product Model
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($model_cell_pos, $row,$product['model']);
													
							$empty_columns = range($model_cell_pos+1,$option_name_cell_pos);
							for($counter=0; $counter<count($empty_columns);$counter++){
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($empty_columns[$counter], $row,'');
							}

							$option_column = $option_name_cell_pos;

							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($option_column, $row,$option['name']);
							# code...
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(++$option_column, $row,$option_value);	
							
							$empty_columns = range(++$option_column,$last_cell_pos);
							for($counter=0; $counter<count($empty_columns);$counter++){
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($empty_columns[$counter], $row,'');
							}
							$row++;
						}
						//die();
					}
					$lock_product_writing = false;
				}
				// To synchronize $i in Letters (column-row) with $row(used in advanced options structure)
				$i = $row-1;
			}
		}
		
		if($filter_eformat == 'csv'){
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
			$filename = 'product'.time().'.csv';
		}elseif($filter_eformat == 'xls'){
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
			$filename = 'product'.time().'.xls';
		}
		elseif($filter_eformat == 'xlsx'){
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$filename = 'product'.time().'.xlsx';
		}
		else{
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
			$filename = 'product'.time().'.xls';
		}
        if (ob_get_length()) ob_end_clean();
		header('Content-Type: application/vnd.ms-excel'); 
		header('Content-Disposition: attachment;filename='.$filename); 
		header('Cache-Control: max-age=0');
        if (ob_get_length()) ob_end_clean();
		$objWriter->save('php://output'); 
		exit();
	}

	private function urlencode($path) {
	    $urlencode=  implode("/", array_map("rawurlencode", explode("/", $path)));
        return str_replace('%3A',':',$urlencode);
	}
}
