<?php
header('Cache-Control: no-cache, no-store');
ini_set('memory_limit', '2048M');
ini_set('max_execution_time', 900);
ini_set('error_reporting', E_ALL);
include DIR_SYSTEM.'library/PHPExcel.php';
class ControllerToolProductexport extends Controller {
	private $error = array();

    public function __construct($registry)
    {
        parent::__construct($registry);
        if(PRODUCTID == 3){
            exit();
        }
    }

	public function index(){
		$this->load->language('catalog/product');
		
		$this->load->language('tool/product_export');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/product');

		$this->data['tab_export'] = $this->language->get('tab_export');
		
		$this->data['token'] = $this->session->data['token'];
		
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
			$this->data['filter_limit'] = $this->config->get('config_admin_limit');
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
			'text' => $this->language->get('product'),
			'href' => $this->url->link('catalog/product', 'token=' . $this->session->data['token'], 'SSL'),
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
		
		$this->template = 'tool/product_export.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);
		$this->response->setOutput($this->render());
	}
	
	public function export(){
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
		
		if (isset($this->request->get['filter_store'])) {
			$filter_store = $this->request->get['filter_store'];
		} else {
			$filter_store = null;
		}
		
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
			$filter_limit = $this->config->get('config_limit_admin');
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
		
		if (isset($this->request->get['filter_pimage'])) {
			$filter_pimage = $this->request->get['filter_pimage'];
		} else {
			$filter_pimage = null;
		}
		
		if (isset($this->request->get['filter_eformat'])) {
			$filter_eformat = $this->request->get['filter_eformat'];
		} else {
			$filter_eformat = null;
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
        $objPHPExcel->getActiveSheet()->setCellValue('L'.$i, 'SKU')->getColumnDimension('L')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('M'.$i, 'UPC')->getColumnDimension('M')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('N'.$i, 'EAN')->getColumnDimension('N')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('O'.$i, 'JAN')->getColumnDimension('O')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('P'.$i, 'ISBN')->getColumnDimension('P')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('Q'.$i, 'MPN')->getColumnDimension('Q')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('R'.$i, 'Location')->getColumnDimension('R')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('S'.$i, 'Price')->getColumnDimension('S')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('T'.$i, 'Tax Class ID')->getColumnDimension('T')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('U'.$i, 'Tax Class')->getColumnDimension('U')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('V'.$i, 'Quantity')->getColumnDimension('V')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('W'.$i, 'Minimum Quantity')->getColumnDimension('W')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('X'.$i, 'Subtract Stock')->getColumnDimension('X')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('Y'.$i, 'Stock Status ID')->getColumnDimension('Y')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('Z'.$i, 'Stock Status')->getColumnDimension('Z')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('AA'.$i, 'Shipping')->getColumnDimension('AA')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('AB'.$i, 'SEO')->getColumnDimension('AB')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('AC'.$i, 'Date Available')->getColumnDimension('AC')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('AD'.$i, 'Length')->getColumnDimension('AD')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('AE'.$i, 'Length Class ID')->getColumnDimension('AE')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('AF'.$i, 'Length Class')->getColumnDimension('AF')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('AG'.$i, 'Width')->getColumnDimension('AG')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('AH'.$i, 'Height')->getColumnDimension('AH')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('AI'.$i, 'Weight')->getColumnDimension('AI')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('AJ'.$i, 'Weight Class ID')->getColumnDimension('AJ')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('AK'.$i, 'Weight Class')->getColumnDimension('AK')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('AL'.$i, 'Status')->getColumnDimension('AL')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('AM'.$i, 'Sort Order')->getColumnDimension('AM')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('AN'.$i, 'Manufacturer ID')->getColumnDimension('AN')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('AO'.$i, 'Manufacturer')->getColumnDimension('AO')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('AP'.$i, 'Category ids')->getColumnDimension('AP')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('AQ'.$i, 'Categories')->getColumnDimension('AQ')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('AR'.$i, 'Filters (Filter Group :: filter Value)')->getColumnDimension('AR')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('AS'.$i, 'Download')->getColumnDimension('AS')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('AT'.$i, 'Related Products')->getColumnDimension('AT')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('AU'.$i, 'Attributes (attribute_Group::attribute::text)')->getColumnDimension('AU')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('AV'.$i, 'Options (Option::Type::optionvalue~qty~subtract~price~points~weight)')->getColumnDimension('AV')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('AW'.$i, 'Options Required (optionname:optiontype=0,optionname:optiontype=1)')->getColumnDimension('AW')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('AX'.$i, 'Discount (Customer Group id::Quantity::Priority::Price::startdate::Enddate)')->getColumnDimension('AX')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('AY'.$i, 'Special (Customer_group_id::Priority::Price::startdate::Enddate)')->getColumnDimension('AY')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('AZ'.$i, 'Sub Images (image1,image2)')->getColumnDimension('AZ')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('BA'.$i, 'Reward')->getColumnDimension('BA')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('BB'.$i, 'Viewed')->getColumnDimension('BB')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('BC'.$i, 'Barcode')->getColumnDimension('BC')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('BD'.$i, 'Cost Price')->getColumnDimension('BD')->setAutoSize(true);
		$products = $this->model_tool_product_export->getProducts($filter_data);
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
				if($product['image'] != '' || file_exists(HTTP_IMAGE.$product['image'])){
                    $objPHPExcel->getActiveSheet()->setCellValue('K'.$i, HTTP_IMAGE.$product['image']);
				}
				else{
                    $objPHPExcel->getActiveSheet()->setCellValue('K'.$i, '');
				}
				
			}else{
                $objPHPExcel->getActiveSheet()->setCellValue('K'.$i, $product['image']);
			}
            $objPHPExcel->getActiveSheet()->setCellValue('L'.$i, $product['sku']);
            $objPHPExcel->getActiveSheet()->setCellValue('M'.$i, $product['upc']);
            $objPHPExcel->getActiveSheet()->setCellValue('N'.$i, $product['ean']);
            $objPHPExcel->getActiveSheet()->setCellValue('O'.$i, $product['jan']);
            $objPHPExcel->getActiveSheet()->setCellValue('P'.$i, $product['isbn']);
            $objPHPExcel->getActiveSheet()->setCellValue('Q'.$i, $product['mpn']);
            $objPHPExcel->getActiveSheet()->setCellValue('R'.$i, $product['location']);
            $objPHPExcel->getActiveSheet()->setCellValue('S'.$i, $product['price']);
            $objPHPExcel->getActiveSheet()->setCellValue('T'.$i, $product['tax_class_id']);
            $objPHPExcel->getActiveSheet()->setCellValue('U'.$i, $this->model_tool_product_export->getTaxClass($product['tax_class_id']));
            $objPHPExcel->getActiveSheet()->setCellValue('V'.$i, $product['quantity']);
            $objPHPExcel->getActiveSheet()->setCellValue('W'.$i, $product['minimum']);
            $objPHPExcel->getActiveSheet()->setCellValue('X'.$i, $product['subtract']);
            $objPHPExcel->getActiveSheet()->setCellValue('Y'.$i, $product['stock_status_id']);
            $objPHPExcel->getActiveSheet()->setCellValue('Z'.$i, $this->model_tool_product_export->getStockstatus($product['stock_status_id'],$language_id));
            $objPHPExcel->getActiveSheet()->setCellValue('AA'.$i, $product['shipping']);
            $objPHPExcel->getActiveSheet()->setCellValue('AB'.$i, $this->model_tool_product_export->getKeyword($product['product_id']));
            $objPHPExcel->getActiveSheet()->setCellValue('AC'.$i,  $product['date_available']);
            $objPHPExcel->getActiveSheet()->setCellValue('AD'.$i, $product['length']);
            $objPHPExcel->getActiveSheet()->setCellValue('AE'.$i, $product['length_class_id']);
            $objPHPExcel->getActiveSheet()->setCellValue('AF'.$i, $this->model_tool_product_export->getLengthClass($product['length_class_id'],$language_id));
            $objPHPExcel->getActiveSheet()->setCellValue('AG'.$i, $product['width']);
            $objPHPExcel->getActiveSheet()->setCellValue('AH'.$i, $product['height']);
            $objPHPExcel->getActiveSheet()->setCellValue('AI'.$i, $product['weight']);
            $objPHPExcel->getActiveSheet()->setCellValue('AJ'.$i, $product['weight_class_id']);
            $objPHPExcel->getActiveSheet()->setCellValue('AK'.$i, $this->model_tool_product_export->getWeightClass($product['weight_class_id'],$language_id));
            $objPHPExcel->getActiveSheet()->setCellValue('AL'.$i, $product['status']);
            $objPHPExcel->getActiveSheet()->setCellValue('AM'.$i, $product['sort_order']);
            $objPHPExcel->getActiveSheet()->setCellValue('AN'.$i, $product['manufacturer_id']);
            $objPHPExcel->getActiveSheet()->setCellValue('AO'.$i, $this->model_tool_product_export->getManufacturer($product['manufacturer_id']));
            $categories = $this->model_catalog_product->getProductCategories($product['product_id']);
            $objPHPExcel->getActiveSheet()->setCellValue('AP'.$i, (!empty($categories) ? implode(',',$categories) : ''));
			$categoryname = array();
			$this->load->model('catalog/category');
			$this->load->model('catalog/filter');
			foreach($categories as $category_id){
			$categoryinfo = $this->model_catalog_category->getcategory($category_id);
			if($categoryinfo){
				$categoryname[] = html_entity_decode(($categoryinfo['path'] ? $categoryinfo['path']. ' &gt; ' . $categoryinfo['name'] : $categoryinfo['name']));
			}
			}
            $objPHPExcel->getActiveSheet()->setCellValue('AQ'.$i, implode(', ',$categoryname));
			
			$filterdata=array();
			$filteres = $this->model_catalog_product->getProductFilters($product['product_id']);
			foreach($filteres as $filter_id){
				$filter_info = $this->model_catalog_filter->getFilter($filter_id);
				if($filter_info){
					$filterdata[] = html_entity_decode(($filter_info['group'] ? $filter_info['group']. ' :: ' . $filter_info['name'] : $filter_info['name']));
				}
			}
            $objPHPExcel->getActiveSheet()->setCellValue('AR'.$i, implode(', ',$filterdata));
			$downloads = $this->model_catalog_product->getProductDownloads($product['product_id']);
            $objPHPExcel->getActiveSheet()->setCellValue('AS'.$i, implode(', ',$downloads));
			$realated = $this->model_catalog_product->getProductRelated($product['product_id']);
            $objPHPExcel->getActiveSheet()->setCellValue('AT'.$i, implode(', ',$realated));
			
			//GetAttribute
			$this->load->model('catalog/attribute');
			$this->load->model('catalog/attribute_group');
			$attributes = $this->model_tool_product_export->getProductAttributes($product['product_id'],$language_id);
            $objPHPExcel->getActiveSheet()->setCellValue('AU'.$i, implode(', ',$attributes));
			
			//options
			$productoptions = $this->model_tool_product_export->getProductOptions($product['product_id'],$language_id);
            $objPHPExcel->getActiveSheet()->setCellValue('AV'.$i, implode('; ',$productoptions));
			
			//options required
			$productoptionrequired = $this->model_tool_product_export->getProductOptionsrequired($product['product_id'],$language_id);

            $objPHPExcel->getActiveSheet()->setCellValue('AW'.$i, implode('; ',$productoptionrequired));
			
			///getDiscount
			$discounts=array();
			$productdiscounts = $this->model_catalog_product->getProductDiscounts($product['product_id']);
			foreach($productdiscounts as $pdiscount){
				$discounts[]= $pdiscount['customer_group_id'].'::'.$pdiscount['quantity'].'::'.$pdiscount['priority'].'::'.$pdiscount['price'].'::'.$pdiscount['date_start'].'::'.$pdiscount['date_end'];
			}
            $objPHPExcel->getActiveSheet()->setCellValue('AX'.$i, implode(', ',$discounts));
			
			//GetSpecial
			$specials=array();
			$productspecials = $this->model_catalog_product->getProductSpecials($product['product_id']);
			foreach($productspecials as $pspecial){
				$specials[]= $pspecial['customer_group_id'].'::'.$pspecial['priority'].'::'.$pspecial['price'].'::'.$pspecial['date_start'].'::'.$pspecial['date_end'];
			}
            $objPHPExcel->getActiveSheet()->setCellValue('AY'.$i, implode(', ',$specials));
			
			//GET Images
			$images=array();
			$productimages = $this->model_catalog_product->getProductImages($product['product_id']);
			foreach($productimages as $pimage){
				
				if($filter_pimage == 'yes'){
					if($pimage['image'] != '' || file_exists(HTTP_IMAGE.$pimage['image'])){
						$images[]= HTTP_IMAGE.$pimage['image'];
					}else{
						$images[]= '';
					}
				}else{
					$images[]= $pimage['image'];
				}
				
			}
            $objPHPExcel->getActiveSheet()->setCellValue('AZ'.$i, implode(';',$images));
            $objPHPExcel->getActiveSheet()->setCellValue('BA'.$i, $product['points']);
            $objPHPExcel->getActiveSheet()->setCellValue('BB'.$i, $product['viewed']);
			$objPHPExcel->getActiveSheet()->setCellValue('BC'.$i, $product['barcode']);
			$objPHPExcel->getActiveSheet()->setCellValue('BD'.$i, $product['cost_price']);
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
	
	public function exportproductreview(){
		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = null;
		}
		
		if (isset($this->request->get['filter_start'])){
			$filter_start = $this->request->get['filter_start'];
		} else {
			$filter_start = 0;
		}
		
		if (isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		} else {
			$filter_status = null;
		}
		
		if (!empty($this->request->get['filter_limit'])) {
			$filter_limit = $this->request->get['filter_limit'];
		} else {
			$filter_limit = '';
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
		
		if (isset($this->request->get['filter_eformat'])) {
			$filter_eformat = $this->request->get['filter_eformat'];
		} else {
			$filter_eformat = null;
		}
			
		$filter_data=array(
			'filter_status'   			=> $filter_status,
		    'limit'           			=> $filter_limit,
		    'start'           			=> $filter_start,
			'filter_name'				=> $filter_name,
			'filter_product_id'			=> $filter_product_id,
			'filter_endproduct_id'		=> $filter_endproduct_id,
		);

		$this->load->model('tool/product_export');
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setTitle("Product Reviews");
		$i=1;
		$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, 'Review ID')->getColumnDimension('A')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, 'Product ID')->getColumnDimension('B')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Product Name')->getColumnDimension('C')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, 'Customer ID')->getColumnDimension('D')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, 'Author')->getColumnDimension('E')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, 'Text')->getColumnDimension('F')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('G'.$i, 'Rating')->getColumnDimension('G')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('H'.$i, 'Status')->getColumnDimension('H')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('I'.$i, 'Date Added')->getColumnDimension('I')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('J'.$i, 'Date Modified')->getColumnDimension('J')->setAutoSize(true);

		$productreview = $this->model_tool_product_export->getproductreview($filter_data);
		foreach($productreview as $productreviews){
			
			$i++;
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $productreviews['review_id']);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $productreviews['product_id']);
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $productreviews['name']);
			$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $productreviews['customer_id']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $productreviews['author']);
			$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, $productreviews['text']);
			$objPHPExcel->getActiveSheet()->setCellValue('G'.$i, $productreviews['rating']);
			$objPHPExcel->getActiveSheet()->setCellValue('H'.$i, $productreviews['status']);
			$objPHPExcel->getActiveSheet()->setCellValue('I'.$i, $productreviews['date_added']);
			$objPHPExcel->getActiveSheet()->setCellValue('J'.$i, $productreviews['date_modified']);
		}
		if($filter_eformat == 'csv'){
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
			$filename = 'ProductReview'.time().'.csv';
		}elseif($filter_eformat == 'xls'){
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
			$filename = 'ProductReview'.time().'.xls';
		}
		elseif($filter_eformat == 'xlsx'){
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$filename = 'ProductReview'.time().'.xlsx';
		}
		else{
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
			$filename = 'ProductReview'.time().'.xls';
		}	
		header('Content-Type: application/vnd.ms-excel'); 
		header('Content-Disposition: attachment;filename='.$filename); 
		header('Cache-Control: max-age=0'); 
		$objWriter->save('php://output'); 
		exit();
	}
}