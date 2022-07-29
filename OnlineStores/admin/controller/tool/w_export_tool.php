<?php
header('Cache-Control: no-cache, no-store');
ini_set('memory_limit', '2048M');
ini_set('max_execution_time', 3600);
ini_set('error_reporting', E_ALL);
include DIR_SYSTEM.'library/PHPExcel.php';
class ControllerToolWExporttool extends Controller {
	private $error = array();

    private $plan_id = PRODUCTID;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('plan/trial');
        $trial = $this->model_plan_trial->getLastTrial();
        if ($trial){
            $this->plan_id = $trial['plan_id'];
        }

        if($this->plan_id == 3){
            exit();
        }
    }

    public function index(){
		$this->load->language('catalog/product');
		
		$this->load->language('tool/excel_order_port');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('tool/excel_order_port');
		
		$this->data['token'] = $this->session->data['token'];
		
		if(isset($this->error['warning'])){
			$this->data['error_warning'] = $this->error['warning'];
		}elseif(isset($this->session->data['error_warning'])){
			$this->data['error_warning'] = $this->session->data['error_warning'];
			unset($this->session->data['error_warning']);
		}else{
			$this->data['error_warning'] = '';
		}
		
		if(isset($this->session->data['success'])){
			$this->data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		}else{
			$this->data['success'] = '';
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
		
		$url = '';
		
		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'),
			'separator'	=> '::'
		);

		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('catalog/w_export_tool', 'token=' . $this->session->data['token'] . $url, 'SSL'),
			'separator'	=> false
		);
		
	
		$this->load->model('localisation/stock_status');
		$this->data['stock_statuses'] = $this->model_localisation_stock_status->getStockStatuses();
		
		$this->data['customer_groups'] = $this->model_tool_excel_order_port->getCustomerGroups();
		
		$this->load->model('localisation/order_status');
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		if (isset($this->request->get['filter_limit'])) {
			$this->data['filter_limit'] = $this->request->get['filter_limit'];
		} else {
			$this->data['filter_limit'] = $this->config->get('config_admin_limit');
		}
		
		$this->template = 'tool/w_export_tool.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}
	
	public function exportOrder(){
		$this->load->model('sale/order');
		$this->load->language('tool/excel_order_port');
		$this->load->model('tool/excel_order_port');
		
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0);
		
		$i=1;
		
		$objPHPExcel->getActiveSheet()->setTitle("Order");
		
		//Change Cell Format 
		$objPHPExcel->getActiveSheet()->getStyle('BA')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
		$objPHPExcel->getActiveSheet()->getStyle('BM')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
		$objPHPExcel->getActiveSheet()->getStyle('BG')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
		
		
		$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $this->language->get('entry_order_id'))->getColumnDimension('A')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $this->language->get('text_invoice_no'))->getColumnDimension('B')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $this->language->get('text_invoice_prefix'))->getColumnDimension('C')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $this->language->get('entry_store_id'))->getColumnDimension('D')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $this->language->get('entry_store'))->getColumnDimension('E')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, $this->language->get('entry_store_url'))->getColumnDimension('F')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('G'.$i, $this->language->get('entry_customer_id'))->getColumnDimension('G')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('H'.$i, $this->language->get('entry_customer'))->getColumnDimension('H')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('I'.$i, $this->language->get('entry_customergroup_id'))->getColumnDimension('I')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('J'.$i, $this->language->get('entry_firstname'))->getColumnDimension('J')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('K'.$i, $this->language->get('entry_lastname'))->getColumnDimension('K')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('L'.$i, $this->language->get('entry_email'))->getColumnDimension('L')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('M'.$i, $this->language->get('entry_telephone'))->getColumnDimension('M')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('N'.$i, $this->language->get('entry_fax'))->getColumnDimension('N')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('O'.$i, $this->language->get('text_custom_field'))->getColumnDimension('O')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('P'.$i, $this->language->get('text_payment_firstname'))->getColumnDimension('P')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('Q'.$i, $this->language->get('text_payment_lastname'))->getColumnDimension('Q')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('R'.$i, $this->language->get('text_payment_company'))->getColumnDimension('R')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('S'.$i, $this->language->get('text_payment_address_1'))->getColumnDimension('S')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('T'.$i, $this->language->get('text_payment_address_2'))->getColumnDimension('T')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('U'.$i, $this->language->get('text_payment_postcode'))->getColumnDimension('U')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('V'.$i, $this->language->get('text_payment_city'))->getColumnDimension('V')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('W'.$i, $this->language->get('text_payment_zone_id'))->getColumnDimension('W')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('X'.$i, $this->language->get('text_payment_zone'))->getColumnDimension('X')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('Y'.$i, $this->language->get('text_payment_zone_code'))->getColumnDimension('Y')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('Z'.$i, $this->language->get('text_payment_country_id'))->getColumnDimension('Z')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('AA'.$i,$this->language->get('text_payment_country'))->getColumnDimension('AA')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('AB'.$i, $this->language->get('text_payment_iso_code_2'))->getColumnDimension('AB')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('AC'.$i, $this->language->get('text_payment_iso_code_3'))->getColumnDimension('AC')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('AD'.$i, $this->language->get('text_payment_address_format'))->getColumnDimension('AD')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('AE'.$i, $this->language->get('text_payment_custom_field'))->getColumnDimension('AE')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('AF'.$i, $this->language->get('text_payment_method'))->getColumnDimension('AF')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('AG'.$i, $this->language->get('text_payment_code'))->getColumnDimension('AG')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('AH'.$i, $this->language->get('text_shipping_firstname'))->getColumnDimension('AH')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('AI'.$i, $this->language->get('text_shipping_lastname'))->getColumnDimension('AI')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('AJ'.$i, $this->language->get('text_shipping_company'))->getColumnDimension('AJ')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('AK'.$i, $this->language->get('text_shipping_address_1'))->getColumnDimension('AK')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('AL'.$i, $this->language->get('text_shipping_address_2'))->getColumnDimension('AL')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('AM'.$i, $this->language->get('text_shipping_postcode'))->getColumnDimension('AM')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('AN'.$i, $this->language->get('text_shipping_city'))->getColumnDimension('AN')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('AO'.$i, $this->language->get('text_shipping_zone_id'))->getColumnDimension('AO')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('AP'.$i, $this->language->get('text_shipping_zone'))->getColumnDimension('AP')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('AQ'.$i, $this->language->get('text_shipping_zone_code'))->getColumnDimension('AQ')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('AR'.$i, $this->language->get('text_shipping_country_id'))->getColumnDimension('AR')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('AS'.$i, $this->language->get('text_shipping_country'))->getColumnDimension('AS')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('AT'.$i, $this->language->get('text_shipping_iso_code_2'))->getColumnDimension('AT')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('AU'.$i, $this->language->get('text_shipping_iso_code_3'))->getColumnDimension('AU')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('AV'.$i, $this->language->get('text_shipping_address_format'))->getColumnDimension('AV')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('AW'.$i, $this->language->get('text_shipping_custom_field'))->getColumnDimension('AW')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('AX'.$i, $this->language->get('text_shipping_method'))->getColumnDimension('AX')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('AY'.$i, $this->language->get('text_shipping_code'))->getColumnDimension('AY')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('AZ'.$i, $this->language->get('text_comment'))->getColumnDimension('AZ')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('BA'.$i, $this->language->get('text_total'))->getColumnDimension('BA')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('BB'.$i, $this->language->get('text_reward'))->getColumnDimension('BB')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('BC'.$i, $this->language->get('text_order_status_id'))->getColumnDimension('BC')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('BD'.$i, $this->language->get('text_affiliate_id'))->getColumnDimension('BD')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('BE'.$i, $this->language->get('text_affiliate_firstname'))->getColumnDimension('BE')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('BF'.$i, $this->language->get('text_affiliate_lastname'))->getColumnDimension('BF')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('BG'.$i, $this->language->get('text_commission'))->getColumnDimension('BG')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('BH'.$i, $this->language->get('text_language_id'))->getColumnDimension('BH')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('BI'.$i, $this->language->get('text_language_code'))->getColumnDimension('BI')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('BJ'.$i, $this->language->get('text_language_directory'))->getColumnDimension('BJ')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('BK'.$i, $this->language->get('text_currency_id'))->getColumnDimension('BK')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('BL'.$i, $this->language->get('text_currency_code'))->getColumnDimension('BL')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('BM'.$i, $this->language->get('text_currency_value'))->getColumnDimension('BM')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('BN'.$i, $this->language->get('text_ip'))->getColumnDimension('BN')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('BO'.$i, $this->language->get('text_forwarded_ip'))->getColumnDimension('BO')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('BP'.$i, $this->language->get('text_user_agent'))->getColumnDimension('BP')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('BQ'.$i, $this->language->get('text_accept_language'))->getColumnDimension('BQ')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('BR'.$i, $this->language->get('text_date_added'))->getColumnDimension('BR')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('BS'.$i, $this->language->get('text_date_modified'))->getColumnDimension('BS')->setAutoSize(true);
		
		
		//Order Products
		$u=1;
		$objWorkSheet = $objPHPExcel->createSheet(1);
		$objWorkSheet->setTitle("Order Product");

		$objWorkSheet->setCellValue('A'.$u, 'Order product ID')->getColumnDimension('A')->setAutoSize(true);
		$objWorkSheet->setCellValue('B'.$u, 'Order ID')->getColumnDimension('B')->setAutoSize(true);
		$objWorkSheet->setCellValue('C'.$u, 'Product ID')->getColumnDimension('C')->setAutoSize(true);
		$objWorkSheet->setCellValue('D'.$u, 'Product')->getColumnDimension('D')->setAutoSize(true);
		$objWorkSheet->setCellValue('E'.$u, 'Model')->getColumnDimension('E')->setAutoSize(true);
		$objWorkSheet->setCellValue('F'.$u, 'Quantity')->getColumnDimension('F')->setAutoSize(true);
		$objWorkSheet->setCellValue('G'.$u, 'Price')->getColumnDimension('G')->setAutoSize(true);
		$objWorkSheet->setCellValue('H'.$u, 'Total')->getColumnDimension('H')->setAutoSize(true);
		$objWorkSheet->setCellValue('I'.$u, 'Tax')->getColumnDimension('I')->setAutoSize(true);
		$objWorkSheet->setCellValue('J'.$u, 'Reward')->getColumnDimension('J')->setAutoSize(true);
		$objWorkSheet->setCellValue('K'.$u, 'SKU')->getColumnDimension('K')->setAutoSize(true);
		$objWorkSheet->setCellValue('L'.$u, 'Barcode')->getColumnDimension('L')->setAutoSize(true);
        // check delivery slot app
        $this->load->model('module/delivery_slot/settings');
        $delivery_slot_status = $this->model_module_delivery_slot_settings->isActive();
        if($delivery_slot_status == true ){
            $objWorkSheet->setCellValue('M'.$u, 'The Day of Delivery')->getColumnDimension('M')->setAutoSize(true);
            $objWorkSheet->setCellValue('N'.$u, 'Delivery Date')->getColumnDimension('N')->setAutoSize(true);
            $objWorkSheet->setCellValue('O'.$u, 'Delivery Time')->getColumnDimension('O')->setAutoSize(true);

        }

        $this->initializer(['sku' => 'module/product_variations']);
		
		if (isset($this->request->get['filter_order_history_admin_status']) && $this->request->get['filter_order_history_admin_status'] != 'undefined' ) {
            $order_history_admin_status = $this->request->get['filter_order_history_admin_status'];
		} else {
			$order_history_admin_status = 0;
		}
		//Order Option
		
		$o=1;
		$objoptionWorkSheet = $objPHPExcel->createSheet(2);
		$objoptionWorkSheet->setTitle("Order Product Option");
		$objoptionWorkSheet->setCellValue('A'.$o, 'Order Option ID')->getColumnDimension('A')->setAutoSize(true);
		$objoptionWorkSheet->setCellValue('B'.$o, 'Order ID')->getColumnDimension('B')->setAutoSize(true);
		$objoptionWorkSheet->setCellValue('C'.$o, 'Order Product ID')->getColumnDimension('C')->setAutoSize(true);
		$objoptionWorkSheet->setCellValue('D'.$o, 'Product Option ID')->getColumnDimension('D')->setAutoSize(true);
		$objoptionWorkSheet->setCellValue('E'.$o, 'Product Option Value ID')->getColumnDimension('E')->setAutoSize(true);
		$objoptionWorkSheet->setCellValue('F'.$o, 'Option Name')->getColumnDimension('F')->setAutoSize(true);
		$objoptionWorkSheet->setCellValue('G'.$o, 'Option Value')->getColumnDimension('G')->setAutoSize(true);
		$objoptionWorkSheet->setCellValue('H'.$o, 'Option Type')->getColumnDimension('H')->setAutoSize(true);
        if ($this->sku->isActive()) {
            $objoptionWorkSheet->setCellValue('I' . $o, 'Product Variations(SKU)')->getColumnDimension('I')->setAutoSize(true);
            $objoptionWorkSheet->setCellValue('J' . $o, 'Product Variations(Barcode)')->getColumnDimension('J')->setAutoSize(true);
        }
		
		///Order Total
		$t=1;
		$objtotalWorkSheet = $objPHPExcel->createSheet(3);
		$objtotalWorkSheet->setTitle("Order Total");
		$objtotalWorkSheet->setCellValue('A'.$t, 'Order Total ID')->getColumnDimension('A')->setAutoSize(true);
		$objtotalWorkSheet->setCellValue('B'.$t, 'Order ID')->getColumnDimension('B')->setAutoSize(true);
		$objtotalWorkSheet->setCellValue('C'.$t, 'Code')->getColumnDimension('C')->setAutoSize(true);
		$objtotalWorkSheet->setCellValue('D'.$t, 'Title')->getColumnDimension('D')->setAutoSize(true);
		$objtotalWorkSheet->setCellValue('E'.$t, 'Value')->getColumnDimension('E')->setAutoSize(true);
		$objtotalWorkSheet->setCellValue('F'.$t, 'Sort order')->getColumnDimension('F')->setAutoSize(true);
		
		//Order History
		$h=1;
		$objhistoryWorkSheet = $objPHPExcel->createSheet(4);
		$objhistoryWorkSheet->setTitle("Order History");
		$objhistoryWorkSheet->setCellValue('A'.$h, 'Order History ID')->getColumnDimension('A')->setAutoSize(true);
		$objhistoryWorkSheet->setCellValue('B'.$h, 'Order ID')->getColumnDimension('B')->setAutoSize(true);
		$objhistoryWorkSheet->setCellValue('C'.$h, 'Order Status ID')->getColumnDimension('C')->setAutoSize(true);
		$objhistoryWorkSheet->setCellValue('D'.$h, 'Order Status')->getColumnDimension('D')->setAutoSize(true);
		$objhistoryWorkSheet->setCellValue('E'.$h, 'Notify')->getColumnDimension('E')->setAutoSize(true);
		$objhistoryWorkSheet->setCellValue('F'.$h, 'Comment')->getColumnDimension('F')->setAutoSize(true);
		$objhistoryWorkSheet->setCellValue('G'.$h, 'Date Added')->getColumnDimension('G')->setAutoSize(true);
		if($order_history_admin_status)
			$objhistoryWorkSheet->setCellValue('H'.$h, 'Admin ID')->getColumnDimension('H')->setAutoSize(true);
		//Order Voucher
		$v=1;
		$objVoucherWorkSheet = $objPHPExcel->createSheet(5);
		$objVoucherWorkSheet->setTitle("Order Voucher");
		$objVoucherWorkSheet->setCellValue('A'.$v, 'Order Voucher ID')->getColumnDimension('A')->setAutoSize(true);
		$objVoucherWorkSheet->setCellValue('B'.$v, 'Order ID')->getColumnDimension('B')->setAutoSize(true);
		$objVoucherWorkSheet->setCellValue('C'.$v, 'Voucher ID')->getColumnDimension('C')->setAutoSize(true);
		$objVoucherWorkSheet->setCellValue('D'.$v, 'Description')->getColumnDimension('D')->setAutoSize(true);
		$objVoucherWorkSheet->setCellValue('E'.$v, 'Code')->getColumnDimension('E')->setAutoSize(true);
		$objVoucherWorkSheet->setCellValue('F'.$v, 'From Name')->getColumnDimension('F')->setAutoSize(true);
		$objVoucherWorkSheet->setCellValue('G'.$v, 'From Email')->getColumnDimension('G')->setAutoSize(true);
		$objVoucherWorkSheet->setCellValue('H'.$v, 'To Name')->getColumnDimension('H')->setAutoSize(true);
		$objVoucherWorkSheet->setCellValue('I'.$v, 'Voucher Theme ID')->getColumnDimension('I')->setAutoSize(true);
		$objVoucherWorkSheet->setCellValue('J'.$v, 'Message')->getColumnDimension('J')->setAutoSize(true);
		$objVoucherWorkSheet->setCellValue('K'.$v, 'Amount')->getColumnDimension('K')->setAutoSize(true);

		if (isset($this->request->get['filter_to_order_id']) && $this->request->get['filter_to_order_id'] != 'undefined' ) {
			$filter_to_order_id = $this->request->get['filter_to_order_id'];
		} else {
			$filter_to_order_id = null;
		}
		
		if (isset($this->request->get['filter_from_order_id']) && $this->request->get['filter_from_order_id'] != 'undefined' ) {
			$filter_from_order_id = $this->request->get['filter_from_order_id'];
		} else {
			$filter_from_order_id = null;
		}
		
		if (isset($this->request->get['filter_to_date_added']) && $this->request->get['filter_to_date_added'] != 'undefined' ) {
			$filter_to_date_added = $this->request->get['filter_to_date_added'];
		} else {
			$filter_to_date_added = null;
		}
		
		if (isset($this->request->get['filter_from_date_added']) && $this->request->get['filter_from_date_added'] != 'undefined' ) {
			$filter_from_date_added = $this->request->get['filter_from_date_added'];
		} else {
			$filter_from_date_added = null;
		}
		
		if (isset($this->request->get['filter_to_date_modified']) && $this->request->get['filter_to_date_modified'] != 'undefined' ) {
			$filter_to_date_modified = $this->request->get['filter_to_date_modified'];
		} else {
			$filter_to_date_modified = null;
		}
		
		if (isset($this->request->get['filter_form_date_modified']) && $this->request->get['filter_form_date_modified'] != 'undefined' ) {
			$filter_form_date_modified = $this->request->get['filter_form_date_modified'];
		} else {
			$filter_form_date_modified = null;
		}

		if (isset($this->request->get['filter_order_status']) && $this->request->get['filter_order_status'] != 'undefined' ) {
			$filter_order_status = $this->request->get['filter_order_status'];
		} else {
			$filter_order_status = null;
		}

		if (isset($this->request->get['filter_total_from']) && $this->request->get['filter_total_from'] != 'undefined' ) {
            $filter_total_from = $this->request->get['filter_total_from'];
		} else {
			$filter_total_from = null;
		}

		if (isset($this->request->get['filter_total_to']) && $this->request->get['filter_total_to'] != 'undefined' ) {
			$filter_total_to = $this->request->get['filter_total_to'];
		} else {
            $filter_total_to = null;
		}

		if (isset($this->request->get['sort']) && $this->request->get['sort'] != 'undefined' ) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'o.order_id';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'DESC';
		}
		
		if (isset($this->request->get['filter_start']) && $this->request->get['filter_start'] != 'undefined' ) {
			$filter_start = $this->request->get['filter_start'];
		} else {
			$filter_start = 0;
		}
		
		if (isset($this->request->get['filter_limit']) && $this->request->get['filter_limit'] != 'undefined' ) {
			$filter_limit = $this->request->get['filter_limit'];
		} else {
			$filter_limit = '';
		}

		$this->data['orders'] = array();
		$filter_data = array(
			'filter_to_date_added'   	=> $filter_to_date_added,
			'filter_from_date_added'    => $filter_from_date_added,
			'filter_to_date_modified'   => $filter_to_date_modified,
			'filter_form_date_modified' => $filter_form_date_modified,
			'filter_to_order_id'   		=> $filter_to_order_id,
			'filter_from_order_id' 		=> $filter_from_order_id,
			'filter_order_status'  		=> $filter_order_status,
			'order_history_admin_status'=> $order_history_admin_status,
			'filter_total_from'        	=> $filter_total_from,
			'filter_total_to'        	=> $filter_total_to,
			'sort'                		=> $sort,
			'order'                		=> $order,
			'start'              		=> $filter_start,
			'limit'                     => $filter_limit,
		);

		$results = $this->model_tool_excel_order_port->getOrders($filter_data);

		foreach($results as $value){
			$result = $this->model_tool_excel_order_port->getOrder($value['order_id']);
			$i++;
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $result['order_id']);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $result['invoice_no'] ? $result['invoice_prefix'] . $result['invoice_no'] : '');
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $result['invoice_prefix']);
			$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $result['store_id']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $result['store_name']);
			$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, $result['store_url']);
			$objPHPExcel->getActiveSheet()->setCellValue('G'.$i, $result['customer_id']);
			$objPHPExcel->getActiveSheet()->setCellValue('H'.$i, $result['customer']);
			$objPHPExcel->getActiveSheet()->setCellValue('I'.$i, $result['customer_group_id']);
			$objPHPExcel->getActiveSheet()->setCellValue('J'.$i, $result['firstname']);
			$objPHPExcel->getActiveSheet()->setCellValue('K'.$i, $result['lastname']);
			$objPHPExcel->getActiveSheet()->setCellValue('L'.$i, $result['email']);
			$objPHPExcel->getActiveSheet()->setCellValue('M'.$i, $result['telephone']);
			$objPHPExcel->getActiveSheet()->setCellValue('N'.$i, $result['fax']);
			$objPHPExcel->getActiveSheet()->setCellValue('O'.$i, '');
			$objPHPExcel->getActiveSheet()->setCellValue('P'.$i, $result['payment_firstname']);
			$objPHPExcel->getActiveSheet()->setCellValue('Q'.$i, $result['payment_lastname']);
			$objPHPExcel->getActiveSheet()->setCellValue('R'.$i, $result['payment_company']);
			$objPHPExcel->getActiveSheet()->setCellValue('S'.$i, $result['payment_address_1']);
			$objPHPExcel->getActiveSheet()->setCellValue('T'.$i, $result['payment_address_2']);
			$objPHPExcel->getActiveSheet()->setCellValue('U'.$i, $result['payment_postcode']);
			$objPHPExcel->getActiveSheet()->setCellValue('V'.$i, $result['payment_city']);
			$objPHPExcel->getActiveSheet()->setCellValue('W'.$i, $result['payment_zone_id']);
			$objPHPExcel->getActiveSheet()->setCellValue('X'.$i, $result['payment_zone']);
			$objPHPExcel->getActiveSheet()->setCellValue('Y'.$i, $result['payment_zone_code']);
			$objPHPExcel->getActiveSheet()->setCellValue('Z'.$i, $result['payment_country_id']);
			$objPHPExcel->getActiveSheet()->setCellValue('AA'.$i, $result['payment_country']);
			$objPHPExcel->getActiveSheet()->setCellValue('AB'.$i, $result['payment_iso_code_2']);
			$objPHPExcel->getActiveSheet()->setCellValue('AC'.$i, $result['payment_iso_code_3']);
			$objPHPExcel->getActiveSheet()->setCellValue('AD'.$i, $result['payment_address_format']);
			$objPHPExcel->getActiveSheet()->setCellValue('AE'.$i, '');
			$objPHPExcel->getActiveSheet()->setCellValue('AF'.$i, $result['payment_method']);
			$objPHPExcel->getActiveSheet()->setCellValue('AG'.$i, $result['payment_code']);
			
			$objPHPExcel->getActiveSheet()->setCellValue('AH'.$i, $result['shipping_firstname']);
			$objPHPExcel->getActiveSheet()->setCellValue('AI'.$i, $result['shipping_lastname']);
			$objPHPExcel->getActiveSheet()->setCellValue('AJ'.$i, $result['shipping_company']);
			$objPHPExcel->getActiveSheet()->setCellValue('AK'.$i, $result['shipping_address_1']);
			$objPHPExcel->getActiveSheet()->setCellValue('AL'.$i, $result['shipping_address_2']);
			$objPHPExcel->getActiveSheet()->setCellValue('AM'.$i, $result['shipping_postcode']);
			$objPHPExcel->getActiveSheet()->setCellValue('AN'.$i, $result['shipping_city']);
			$objPHPExcel->getActiveSheet()->setCellValue('AO'.$i, $result['shipping_zone_id']);
			$objPHPExcel->getActiveSheet()->setCellValue('AP'.$i, $result['shipping_zone']);
			
			$objPHPExcel->getActiveSheet()->setCellValue('AQ'.$i, $result['shipping_zone_code']);
			$objPHPExcel->getActiveSheet()->setCellValue('AR'.$i, $result['shipping_country_id']);
			
			$objPHPExcel->getActiveSheet()->setCellValue('AS'.$i, $result['shipping_country']);
			$objPHPExcel->getActiveSheet()->setCellValue('AT'.$i, $result['shipping_iso_code_2']);
			$objPHPExcel->getActiveSheet()->setCellValue('AU'.$i, $result['shipping_iso_code_3']);
			$objPHPExcel->getActiveSheet()->setCellValue('AV'.$i, $result['shipping_address_format']);
			
			$objPHPExcel->getActiveSheet()->setCellValue('AW'.$i, '');
			
			$objPHPExcel->getActiveSheet()->setCellValue('AX'.$i, $result['shipping_method']);
			
			$objPHPExcel->getActiveSheet()->setCellValue('AY'.$i, $result['shipping_code']);
			$objPHPExcel->getActiveSheet()->setCellValue('AZ'.$i, $result['comment']);
			$objPHPExcel->getActiveSheet()->setCellValue('BA'.$i, sprintf("%0.2f", $result['total']));
			
			
			$objPHPExcel->getActiveSheet()->setCellValue('BB'.$i, $result['reward']);
			$objPHPExcel->getActiveSheet()->setCellValue('BC'.$i, $result['order_status_id']);
			$objPHPExcel->getActiveSheet()->setCellValue('BD'.$i, $result['affiliate_id']);
			$objPHPExcel->getActiveSheet()->setCellValue('BE'.$i, $result['affiliate_firstname']);
			$objPHPExcel->getActiveSheet()->setCellValue('BF'.$i, $result['affiliate_lastname']);
			$objPHPExcel->getActiveSheet()->setCellValue('BG'.$i, $result['commission']);
			$objPHPExcel->getActiveSheet()->setCellValue('BH'.$i, $result['language_id']);
			$objPHPExcel->getActiveSheet()->setCellValue('BI'.$i, $result['language_code']);
			$objPHPExcel->getActiveSheet()->setCellValue('BJ'.$i, $result['language_directory']);
			$objPHPExcel->getActiveSheet()->setCellValue('BK'.$i, $result['currency_id']);
			$objPHPExcel->getActiveSheet()->setCellValue('BL'.$i, $result['currency_code']);
			$objPHPExcel->getActiveSheet()->setCellValue('BM'.$i, $result['currency_value']);
			$objPHPExcel->getActiveSheet()->setCellValue('BN'.$i, $result['ip']);
			$objPHPExcel->getActiveSheet()->setCellValue('BO'.$i, $result['forwarded_ip']);
			$objPHPExcel->getActiveSheet()->setCellValue('BP'.$i, $result['user_agent']);
			$objPHPExcel->getActiveSheet()->setCellValue('BQ'.$i, $result['accept_language']);
			$objPHPExcel->getActiveSheet()->setCellValue('BR'.$i, $result['date_added']);
			$objPHPExcel->getActiveSheet()->setCellValue('BS'.$i, $result['date_modified']);
			
			$order_products  = $this->model_sale_order->getOrderProducts($result['order_id']);
			foreach($order_products as $orderproduct){
				$u++;

				$order_product_options = $this->model_sale_order->getOrderOptions(
					$result['order_id'],
					$orderproduct['order_product_id']
				);

				$skuInfo = null;
                if ($this->sku->isActive()) {
                	$productOptionValueId = array_column($order_product_options, 'product_option_value_id');
                    $skuInfo = $this->sku->getProductVariationByValuesIds(
                        $orderproduct['product_id'], array_column(
                            $this->sku->getOptionValuesIds($productOptionValueId),
                            'option_value_id'
                        )
                    );
                }

				$objWorkSheet->setCellValue('A'.$u, $orderproduct['order_product_id']);
				$objWorkSheet->setCellValue('B'.$u, $orderproduct['order_id']);
				$objWorkSheet->setCellValue('C'.$u, $orderproduct['product_id']);
				$objWorkSheet->setCellValue('D'.$u, $orderproduct['name']);
				$objWorkSheet->setCellValue('E'.$u, $orderproduct['model']);
				$objWorkSheet->setCellValue('F'.$u, $orderproduct['quantity']);
				$objWorkSheet->setCellValue('G'.$u, $orderproduct['price']);
				$objWorkSheet->setCellValue('H'.$u, $orderproduct['total']);
				$objWorkSheet->setCellValue('I'.$u, $orderproduct['tax']);
				$objWorkSheet->setCellValue('J'.$u, $orderproduct['reward']);
                $objWorkSheet->setCellValue('K'.$u, $orderproduct['sku']);
                $objWorkSheet->setCellValue('L'.$u, $orderproduct['barcode']);

                // check delivery slot app
                $this->load->model('module/delivery_slot/settings');
                $delivery_slot_status = $this->model_module_delivery_slot_settings->isActive();
                if($delivery_slot_status == true ){
                    $order_delivry  = $this->model_sale_order->orderDeliveryDetails($result['order_id']);
                    $objWorkSheet->setCellValue('M'.$u, $order_delivry['day_name']);
                    $objWorkSheet->setCellValue('N'.$u, $order_delivry['delivery_date']);
                    $objWorkSheet->setCellValue('O'.$u, $order_delivry['slot_description']);

                }

				foreach($order_product_options as $option){
					$o++;
					$objoptionWorkSheet->setCellValue('A'.$o, $option['order_option_id']);
					$objoptionWorkSheet->setCellValue('B'.$o, $option['order_id']);
					$objoptionWorkSheet->setCellValue('C'.$o, $option['order_product_id']);
					$objoptionWorkSheet->setCellValue('D'.$o, $option['product_option_id']);
					$objoptionWorkSheet->setCellValue('E'.$o, $option['product_option_value_id']);
					$objoptionWorkSheet->setCellValue('F'.$o, $option['name']);
					$objoptionWorkSheet->setCellValue('G'.$o, $option['value']);
					$objoptionWorkSheet->setCellValue('H'.$o, $option['type']);
                    if ($skuInfo) {
                        $objoptionWorkSheet->setCellValue('I'.$o, $skuInfo['product_sku']);
                        $objoptionWorkSheet->setCellValue('J'.$o, $skuInfo['product_barcode']);
                    }
                }
			}
			
			//totals
			$order_totals  = $this->model_sale_order->getOrderTotals($result['order_id']);
			foreach($order_totals as $total){
				$t++;
				$objtotalWorkSheet->setCellValue('A'.$t, $total['order_total_id']);
				$objtotalWorkSheet->setCellValue('B'.$t, $total['order_id']);
				$objtotalWorkSheet->setCellValue('C'.$t, $total['code']);
				$objtotalWorkSheet->setCellValue('D'.$t, $total['title']);
				$objtotalWorkSheet->setCellValue('E'.$t, sprintf("%0.2f", $total['value']));
				$objtotalWorkSheet->setCellValue('F'.$t, $total['sort_order']);
			}

			//history
			$order_historys  = $this->model_tool_excel_order_port->getOrderexportHistories($result['order_id']);
			foreach($order_historys as $history){
				$h++;
				$objhistoryWorkSheet->setCellValue('A'.$h, $history['order_history_id']);
				$objhistoryWorkSheet->setCellValue('B'.$h, $history['order_id']);
				$objhistoryWorkSheet->setCellValue('C'.$h, $history['order_status_id']);
				$objhistoryWorkSheet->setCellValue('D'.$h, $history['name']);
				$objhistoryWorkSheet->setCellValue('E'.$h, $history['notify']);
				$objhistoryWorkSheet->setCellValueExplicit('F'.$h, $history['comment']);
				$objhistoryWorkSheet->setCellValue('G'.$h, $history['date_added']);
				if($order_history_admin_status)
					$objhistoryWorkSheet->setCellValue('H'.$h, $history['user_id']);
			}

			//Voucher
			$order_vouchers  = $this->model_sale_order->getOrderVouchers($result['order_id']);
			foreach($order_vouchers as $voucher){
				$v++;
				$objVoucherWorkSheet->setCellValue('A'.$v, $voucher['order_voucher_id']);
				$objVoucherWorkSheet->setCellValue('B'.$v, $voucher['order_id']);
				$objVoucherWorkSheet->setCellValue('C'.$v, $voucher['voucher_id']);
				$objVoucherWorkSheet->setCellValue('D'.$v, $voucher['description']);
				$objVoucherWorkSheet->setCellValue('E'.$v, $voucher['code']);
				$objVoucherWorkSheet->setCellValue('F'.$v, $voucher['from_name']);
				$objVoucherWorkSheet->setCellValue('G'.$v, $voucher['from_email']);
				$objVoucherWorkSheet->setCellValue('H'.$v, $voucher['to_name']);
				$objVoucherWorkSheet->setCellValue('I'.$v, $voucher['to_email']);
				$objVoucherWorkSheet->setCellValue('J'.$v, $voucher['voucher_theme_id']);
				$objVoucherWorkSheet->setCellValue('K'.$v, $voucher['message']);
				$objVoucherWorkSheet->setCellValue('L'.$v, $voucher['amount']);
			}
		}
		
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		
		$filname ="orderexport-".time().'.xls';
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename='.$filname);
		header('Cache-Control: max-age=0');
		ob_end_clean();
        ob_start();
		$objWriter->save('php://output');
			// add data to log_history
			$this->load->model('setting/audit_trail');
			$pageStatus = $this->model_setting_audit_trail->getPageStatus("order");
			if($pageStatus){
				$log_history['action'] = 'exportOrder';
				$log_history['reference_id'] = NULL;
				$log_history['old_value'] = NULL;
				$log_history['new_value'] = NULL;
				$log_history['type'] = 'order';
				$this->load->model('loghistory/histories');
				$this->model_loghistory_histories->addHistory($log_history);
			}
		exit();
	}
	
	public function exportCustomer(){
		$this->load->model('tool/excel_order_port');
		$this->load->model('setting/store');
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0);  
		$customers=array();
		if(isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		}else{
			$filter_name = null;
		}

		if(isset($this->request->get['filter_email'])) {
			$filter_email = $this->request->get['filter_email'];
		}else{
			$filter_email = null;
		}

		if(isset($this->request->get['filter_customer_group_id'])) {
			$filter_customer_group_id = $this->request->get['filter_customer_group_id'];
			if($filter_customer_group_id == -1){
				$this->exportNewsletterSubscribers($this->request->get);
			}
		}else{
			$filter_customer_group_id = null;
		}

		if(isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		}else{
			$filter_status = null;
		}

		if (isset($this->request->get['filter_approved'])) {
			$filter_approved = $this->request->get['filter_approved'];
		} else {
			$filter_approved = null;
		}

		if (isset($this->request->get['filter_ip'])) {
			$filter_ip = $this->request->get['filter_ip'];
		} else {
            $filter_ip = null;
		}
		
		if (isset($this->request->get['filter_date_added'])) {
			$filter_date_added = $this->request->get['filter_date_added'];
		} else {
			$filter_date_added = null;
		}
		
		if (isset($this->request->get['filter_start'])) {
			$filter_start = $this->request->get['filter_start'];
		} else {
			$filter_start = 0;
		}
		
		if (isset($this->request->get['filter_limit'])) {
			$filter_limit = $this->request->get['filter_limit'];
		} else {
			$filter_limit = '';
		}
		
		$filter_data = array(
			'filter_name'              => $filter_name,
			'filter_email'             => $filter_email,
			'filter_customer_group_id' => $filter_customer_group_id,
			'filter_status'            => $filter_status,
			'filter_approved'          => $filter_approved,
			'filter_ip'                => $filter_ip,
			'filter_date_added'        => $filter_date_added,
			'start' 				   => $filter_start,
			'limit' 				   => $filter_limit
		);
			
		$results = $this->model_tool_excel_order_port->getCustomers($filter_data);

		foreach($results as $result){
			$address_info = $this->model_tool_excel_order_port->getAddress($result['address_id']);
			$customers[]=array(
				'customer_id'		=> $result['customer_id'],
				'firstname' 		=> $result['firstname'],
				'lastname' 			=> $result['lastname'],
				'email' 			=> $result['email'],
				'password' 			=> $result['password'],
				'salt' 				=> $result['salt'],
				'telephone' 		=> $result['telephone'],
				'fax' 				=> $result['fax'],
				'customer_group_id' => $result['customer_group_id'],
				'company'     		=> $address_info['company'],
				'address_1'   		=> $address_info['address_1'],
				'address_2'   		=> $address_info['address_2'],
				'postcode'    		=> $address_info['postcode'],
				'city'  	  		=> $address_info['city'],
				'zone_id'  	  		=> $address_info['zone_id'],
				'country_id'     	=> $address_info['country_id'],
				'approved' 	 		=> $result['approved'],
				'newsletter'        => $result['newsletter'],
				'status' 			=> $result['status'],
                'country' 			=> $address_info['country'],
                'zone' 			    => $address_info['zone'],
			);
		}
		
		
		$i=1;
		$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, 'Customer ID')->getColumnDimension('A')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, 'First Name')->getColumnDimension('B')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Last Name')->getColumnDimension('C')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, 'E-Mail')->getColumnDimension('D')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, 'Password')->getColumnDimension('E')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, 'Salt')->getColumnDimension('F')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('G'.$i, 'TelePhone')->getColumnDimension('G')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('H'.$i, 'Fax')->getColumnDimension('H')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('I'.$i, 'Customer Group ID')->getColumnDimension('I')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('J'.$i, 'Company')->getColumnDimension('J')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('K'.$i, 'Address 1')->getColumnDimension('K')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('L'.$i, 'Address 2')->getColumnDimension('L')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('M'.$i, 'Postal Code')->getColumnDimension('M')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('N'.$i, 'City')->getColumnDimension('N')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('O'.$i, 'Zone ID')->getColumnDimension('O')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('P'.$i, 'Country ID')->getColumnDimension('P')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('Q'.$i, 'Approved')->getColumnDimension('Q')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('R'.$i, 'Newsletter')->getColumnDimension('R')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('S'.$i, 'Status')->getColumnDimension('S')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('T'.$i, 'Country Name')->getColumnDimension('T')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('U'.$i, 'Zone Name')->getColumnDimension('U')->setAutoSize(true);
		foreach($customers as $customer){
			$i++;
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $customer['customer_id']);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $customer['firstname']);
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $customer['lastname']);
			$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $customer['email']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $customer['password']);
			$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, $customer['salt']);
			$objPHPExcel->getActiveSheet()->setCellValue('G'.$i, $customer['telephone']);
			$objPHPExcel->getActiveSheet()->setCellValue('H'.$i, $customer['fax']);
			$objPHPExcel->getActiveSheet()->setCellValue('I'.$i, $customer['customer_group_id']);
			$objPHPExcel->getActiveSheet()->setCellValue('J'.$i, $customer['company']);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('K'.$i, $customer['address_1']);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('L'.$i, $customer['address_2']);
			$objPHPExcel->getActiveSheet()->setCellValue('M'.$i, $customer['postcode']);
			$objPHPExcel->getActiveSheet()->setCellValue('N'.$i, $customer['city']);
			$objPHPExcel->getActiveSheet()->setCellValue('O'.$i, $customer['zone_id']);
			$objPHPExcel->getActiveSheet()->setCellValue('P'.$i, $customer['country_id']);
			$objPHPExcel->getActiveSheet()->setCellValue('Q'.$i, $customer['approved']);
			$objPHPExcel->getActiveSheet()->setCellValue('R'.$i, $customer['newsletter']);
			$objPHPExcel->getActiveSheet()->setCellValue('S'.$i, $customer['status']);
			$objPHPExcel->getActiveSheet()->setCellValue('T'.$i, $customer['country']);
			$objPHPExcel->getActiveSheet()->setCellValue('U'.$i, $customer['zone']);
		}
			
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5'); 
		
		$filename = 'customerList'.time().'.xls';
		header('Content-Type: application/vnd.ms-excel'); 
		header('Content-Disposition: attachment;filename='.$filename);
		header('Cache-Control: max-age=0');
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 300);
		ob_end_clean();
        ob_start();
		$objWriter->save('php://output'); 
		exit(); 
	}

	/**
	 * Export all order report data
	 */
	public function exportOrdersReport()
	{
		$this->language->load('report/sale_order');
		$this->load->model('report/sale');

		$data = $this->model_report_sale->getOrders();
		$i=1;

		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $this->language->get('column_date_start'))->getColumnDimension('A')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $this->language->get('column_date_end'))->getColumnDimension('B')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $this->language->get('column_orders'))->getColumnDimension('C')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $this->language->get('column_products'))->getColumnDimension('D')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $this->language->get('column_tax'))->getColumnDimension('E')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, $this->language->get('column_total'))->getColumnDimension('F')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('G'.$i, $this->language->get('column_profit'))->getColumnDimension('G')->setAutoSize(true);

		foreach($data as $row)
		{
			$i++;
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $row['date_start']);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $row['date_end']);
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $row['orders']);
			$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $row['products']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $row['tax']);
			$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, $row['total']);
			$objPHPExcel->getActiveSheet()->setCellValue('G'.$i, $row['profit']);
		}

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$filname = $this->language->get('heading_title') . '-' . time().'.xls';

		header('Content-Type: application/vnd.ms-excel'); 
		header('Content-Disposition: attachment;filename='.$filname); 
		header('Cache-Control: max-age=0'); 

		ob_end_clean();
		ob_start();
		
		$objWriter->save('php://output'); 
		exit(); 
	}

	/**
	 * Export all tax report data
	 */
	public function exportTaxesReport()
	{
		$this->language->load('report/sale_tax');
		$this->load->model('report/sale');

		$data = $this->model_report_sale->getTaxes();
		$i=1;

		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $this->language->get('column_date_start'))->getColumnDimension('A')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $this->language->get('column_date_end'))->getColumnDimension('B')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $this->language->get('column_title'))->getColumnDimension('C')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $this->language->get('column_orders'))->getColumnDimension('D')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $this->language->get('column_total'))->getColumnDimension('E')->setAutoSize(true);

		foreach($data as $row)
		{
			$i++;
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $row['date_start']);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $row['date_end']);
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $row['title']);
			$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $row['orders']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $row['total']);
		}

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$filname = $this->language->get('heading_title') . '-' . time().'.xls';

		header('Content-Type: application/vnd.ms-excel'); 
		header('Content-Disposition: attachment;filename='.$filname); 
		header('Cache-Control: max-age=0'); 
		
		ob_end_clean();
		ob_start();
		
		$objWriter->save('php://output'); 
		exit(); 
	}

	/**
	 * Export all shipping report data
	 */
	public function exportShippingReport()
	{
		$this->language->load('report/sale_shipping');
		$this->load->model('report/sale');

		$data = $this->model_report_sale->getShipping();
		$i=1;

		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $this->language->get('column_date_start'))->getColumnDimension('A')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $this->language->get('column_date_end'))->getColumnDimension('B')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $this->language->get('column_title'))->getColumnDimension('C')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $this->language->get('column_orders'))->getColumnDimension('D')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $this->language->get('column_total'))->getColumnDimension('E')->setAutoSize(true);

		foreach($data as $row)
		{
			$i++;
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $row['date_start']);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $row['date_end']);
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $row['title']);
			$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $row['orders']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $row['total']);
		}

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$filname = $this->language->get('heading_title') . '-' . time().'.xls';

		header('Content-Type: application/vnd.ms-excel'); 
		header('Content-Disposition: attachment;filename='.$filname); 
		header('Cache-Control: max-age=0'); 
		
		ob_end_clean();
		ob_start();
		
		$objWriter->save('php://output'); 
		exit(); 
	}

	/**
	 * Export all return report data
	 */
	public function exportReturnsReport()
	{
		$this->language->load('report/sale_return');
		$this->load->model('report/return');

		$data = $this->model_report_return->getReturns();
		$i=1;

		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $this->language->get('column_date_start'))->getColumnDimension('A')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $this->language->get('column_date_end'))->getColumnDimension('B')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $this->language->get('column_returns'))->getColumnDimension('C')->setAutoSize(true);

		foreach($data as $row)
		{
			$i++;
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $row['date_start']);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $row['date_end']);
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $row['returns']);
		}

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$filname = $this->language->get('heading_title') . '-' . time().'.xls';

		header('Content-Type: application/vnd.ms-excel'); 
		header('Content-Disposition: attachment;filename='.$filname); 
		header('Cache-Control: max-age=0'); 
		
		ob_end_clean();
		ob_start();
		
		$objWriter->save('php://output'); 
		exit(); 
	}

	/**
	 * Export all coupon report data
	 */
	public function exportCouponsReport()
	{
		$this->language->load('report/sale_coupon');
		$this->load->model('report/coupon');

		$data = $this->model_report_coupon->getCoupons();
		$i=1;

		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $this->language->get('column_name'))->getColumnDimension('A')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $this->language->get('column_code'))->getColumnDimension('B')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $this->language->get('column_orders'))->getColumnDimension('C')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $this->language->get('column_total'))->getColumnDimension('D')->setAutoSize(true);

		foreach($data as $row)
		{
			$i++;
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $row['name']);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $row['code']);
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $row['orders']);
			$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $row['total']);
		}

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$filname = $this->language->get('heading_title') . '-' . time().'.xls';

		header('Content-Type: application/vnd.ms-excel'); 
		header('Content-Disposition: attachment;filename='.$filname); 
		header('Cache-Control: max-age=0'); 
		
		ob_end_clean();
		ob_start();
		
		$objWriter->save('php://output'); 
		exit(); 
	}

	/**
	 * Export all abandoned report data
	 */
	public function exportAbandonedReport()
	{
        $this->language->load('report/sale_order');
        $this->language->load('sale/order');
        $this->language->load('catalog/product_filter');
        $this->language->load('report/abandoned_cart');
		$this->language->load('module/abandoned_cart');
		
		$this->initializer([
			'module/abandoned_cart/settings',
            'abandonedReports' => 'module/abandoned_cart/reports',
        ]);

		$data = $this->abandonedReports->getOrdersList([], []);
		$i=1;

		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $this->language->get('column_order_id'))->getColumnDimension('A')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $this->language->get('column_customer'))->getColumnDimension('B')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $this->language->get('entry_email'))->getColumnDimension('C')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $this->language->get('column_total'))->getColumnDimension('D')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $this->language->get('text_emailed'))->getColumnDimension('E')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, $this->language->get('column_status'))->getColumnDimension('F')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('G'.$i, $this->language->get('column_date_added'))->getColumnDimension('G')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('H'.$i, $this->language->get('column_date_modified'))->getColumnDimension('H')->setAutoSize(true);

		foreach($data['data'] as $row)
		{
			$i++;
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $row['order_id']);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $row['customer_id'] > 0 ? $row['customer'] : $this->language->get('customer_not_in_db'));
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $row['email']);
			$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $row['total']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $row['emailed'] ? $this->language->get('text_yes') : $this->language->get('text_no'));
			$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, $row['status'] ?? $this->language->get('text_abandoned'));
			$objPHPExcel->getActiveSheet()->setCellValue('G'.$i, $row['date_added']);
			$objPHPExcel->getActiveSheet()->setCellValue('H'.$i, $row['date_modified']);
		}

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$filname = $this->language->get('abandoned_cart_heading_title') . '-' . time().'.xls';

		header('Content-Type: application/vnd.ms-excel'); 
		header('Content-Disposition: attachment;filename='.$filname); 
		header('Cache-Control: max-age=0'); 
		
		ob_end_clean();
		ob_start();
		
		$objWriter->save('php://output'); 
		exit(); 
	}

	/**
	 * Export all customer online report data
	 */
	public function exportCustomerOnlineReport()
	{
		$this->language->load('report/customer_online');
		$this->load->model('report/online');

		$data = $this->model_report_online->getCustomersOnline();
		$i=1;

		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $this->language->get('column_ip'))->getColumnDimension('A')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $this->language->get('column_customer'))->getColumnDimension('B')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $this->language->get('column_url'))->getColumnDimension('C')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $this->language->get('column_referer'))->getColumnDimension('D')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $this->language->get('column_date_added'))->getColumnDimension('E')->setAutoSize(true);

		foreach($data as $row)
		{
			$i++;
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $row['ip']);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $row['customer_id']);
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $row['url']);
			$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $row['referrer']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $row['date_added']);
		}

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$filname = $this->language->get('heading_title') . '-' . time().'.xls';

		header('Content-Type: application/vnd.ms-excel'); 
		header('Content-Disposition: attachment;filename='.$filname); 
		header('Cache-Control: max-age=0'); 
		
		ob_end_clean();
		ob_start();
		
		$objWriter->save('php://output'); 
		exit(); 
	}

	/**
	 * Export all customer orders report data
	 */
	public function exportCustomerOrdersReport()
	{
		$this->language->load('report/customer_order');
		$this->load->model('report/customer');

		$data = $this->model_report_customer->getOrders();
		$i=1;

		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $this->language->get('column_customer'))->getColumnDimension('A')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $this->language->get('column_email'))->getColumnDimension('B')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $this->language->get('column_customer_group'))->getColumnDimension('C')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $this->language->get('column_status'))->getColumnDimension('D')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $this->language->get('column_orders'))->getColumnDimension('E')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, $this->language->get('column_products'))->getColumnDimension('F')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('G'.$i, $this->language->get('column_total'))->getColumnDimension('G')->setAutoSize(true);

		foreach($data as $row)
		{
			$i++;
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $row['customer']);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $row['email']);
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $row['customer_group']);
			$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $row['status']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $row['orders']);
			$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, $row['products']);
			$objPHPExcel->getActiveSheet()->setCellValue('G'.$i, $row['total']);
		}

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$filname = $this->language->get('heading_title') . '-' . time().'.xls';

		header('Content-Type: application/vnd.ms-excel'); 
		header('Content-Disposition: attachment;filename='.$filname); 
		header('Cache-Control: max-age=0'); 
		
		ob_end_clean();
		ob_start();
		
		$objWriter->save('php://output'); 
		exit(); 
	}

	/**
	 * Export all customer reward report data
	 */
	public function exportCustomerRewardsReport()
	{
		$this->language->load('report/customer_reward');
		$this->load->model('report/customer');

		$data = $this->model_report_customer->getRewardPoints();
		$i=1;

		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $this->language->get('column_customer'))->getColumnDimension('A')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $this->language->get('column_email'))->getColumnDimension('B')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $this->language->get('column_customer_group'))->getColumnDimension('C')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $this->language->get('column_status'))->getColumnDimension('D')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $this->language->get('column_points'))->getColumnDimension('E')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, $this->language->get('column_orders'))->getColumnDimension('F')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('G'.$i, $this->language->get('column_total'))->getColumnDimension('G')->setAutoSize(true);

		foreach($data as $row)
		{
			$i++;
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $row['customer']);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $row['email']);
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $row['customer_group']);
			$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $row['status']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $row['points']);
			$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, $row['orders']);
			$objPHPExcel->getActiveSheet()->setCellValue('G'.$i, $row['total']);
		}

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$filname = $this->language->get('heading_title') . '-' . time().'.xls';

		header('Content-Type: application/vnd.ms-excel'); 
		header('Content-Disposition: attachment;filename='.$filname); 
		header('Cache-Control: max-age=0'); 
		
		ob_end_clean();
		ob_start();
		
		$objWriter->save('php://output'); 
		exit(); 
	}

	/**
	 * Export all customer credit report data
	 */
	public function exportCustomerCreditReport()
	{
		$this->language->load('report/customer_credit');
		$this->load->model('report/customer');

		$data = $this->model_report_customer->getCredit();
		$i=1;

		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $this->language->get('column_customer'))->getColumnDimension('A')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $this->language->get('column_email'))->getColumnDimension('B')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $this->language->get('column_customer_group'))->getColumnDimension('C')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $this->language->get('column_status'))->getColumnDimension('D')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $this->language->get('column_total'))->getColumnDimension('E')->setAutoSize(true);

		foreach($data as $row)
		{
			$i++;
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $row['customer']);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $row['email']);
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $row['customer_group']);
			$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $row['status']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $row['total']);
		}

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$filname = $this->language->get('heading_title') . '-' . time().'.xls';

		header('Content-Type: application/vnd.ms-excel'); 
		header('Content-Disposition: attachment;filename='.$filname); 
		header('Cache-Control: max-age=0'); 
		
		ob_end_clean();
		ob_start();
		
		$objWriter->save('php://output'); 
		exit(); 
	}

	/**
	 * Export all product viewed report data
	 */
	public function exportProductViewedReport()
	{
		$this->language->load('report/product_viewed');
		$this->load->model('report/product');

		$data = $this->model_report_product->getProductsViewed();
		$i=1;

		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $this->language->get('column_name'))->getColumnDimension('A')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $this->language->get('column_model'))->getColumnDimension('B')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $this->language->get('column_viewed'))->getColumnDimension('C')->setAutoSize(true);

		foreach($data as $row)
		{
			$i++;
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $row['name']);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $row['model']);
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $row['viewed']);
		}

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$filname = $this->language->get('heading_title') . '-' . time().'.xls';

		header('Content-Type: application/vnd.ms-excel'); 
		header('Content-Disposition: attachment;filename='.$filname); 
		header('Cache-Control: max-age=0'); 
		
		ob_end_clean();
		ob_start();
		
		$objWriter->save('php://output'); 
		exit(); 
	}

	/**
	 * Export all product purchased report data
	 */
	public function exportProductPurchasedReport()
	{
		$this->language->load('report/product_purchased');
		$this->load->model('report/product');

		$data = $this->model_report_product->getPurchased();
		$i=1;

		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $this->language->get('column_name'))->getColumnDimension('A')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $this->language->get('column_model'))->getColumnDimension('B')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $this->language->get('column_quantity'))->getColumnDimension('C')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $this->language->get('column_total'))->getColumnDimension('D')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $this->language->get('column_profit'))->getColumnDimension('E')->setAutoSize(true);

		foreach($data as $row)
		{
			$i++;
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $row['name']);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $row['model']);
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $row['quantity']);
			$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $row['total']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $row['profit']);
		}

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$filname = $this->language->get('heading_title') . '-' . time().'.xls';

		header('Content-Type: application/vnd.ms-excel'); 
		header('Content-Disposition: attachment;filename='.$filname); 
		header('Cache-Control: max-age=0'); 
		
		ob_end_clean();
		ob_start();
		
		$objWriter->save('php://output'); 
		exit(); 
	}

	/**
	 * Export all commission report data
	 */
	public function exportCommissionReport()
	{
		$this->language->load('report/affiliate_commission');
		$this->load->model('report/affiliate');

		$data = $this->model_report_affiliate->getCommission();
		$i=1;

		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $this->language->get('column_affiliate'))->getColumnDimension('A')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $this->language->get('column_email'))->getColumnDimension('B')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $this->language->get('column_status'))->getColumnDimension('C')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $this->language->get('column_commission'))->getColumnDimension('D')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $this->language->get('column_orders'))->getColumnDimension('E')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, $this->language->get('column_total'))->getColumnDimension('F')->setAutoSize(true);

		foreach($data as $row)
		{
			$i++;
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $row['affiliate']);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $row['email']);
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $row['status']);
			$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $row['commission']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $row['orders']);
			$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, $row['total']);
		}

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$filname = $this->language->get('heading_title') . '-' . time().'.xls';

		header('Content-Type: application/vnd.ms-excel'); 
		header('Content-Disposition: attachment;filename='.$filname); 
		header('Cache-Control: max-age=0'); 
		
		ob_end_clean();
		ob_start();
		
		$objWriter->save('php://output'); 
		exit(); 
	}

	public function exportOptionsQuantitiesReport(){

        $this->language->load('report/products_quantities');
        $this->load->model('report/products_quantities');

        $data = $this->model_report_products_quantities->getTree([
            'start' => 0, 'length' => -1
        ]);

        $i=1;

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $this->language->get('category_name'))
            ->getColumnDimension('A')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $this->language->get('product_name'))
            ->getColumnDimension('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $this->language->get('product_quantity'))
            ->getColumnDimension('C')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $this->language->get('option'))
            ->getColumnDimension('D')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $this->language->get('option_quantity'))
            ->getColumnDimension('E')->setAutoSize(true);

        foreach($data['data'] as $row)
        {
            $i++;
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$i, html_entity_decode( $row['categories_names'],ENT_QUOTES,'UTF-8')
           );
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $row['name']);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $row['product_quantity']);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $row['fn']. " " . $row['ln']);
            $objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $row['quantity']);
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $filname = $this->language->get('heading_title') . '-' . time().'.xls';

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename='.$filname);
        header('Cache-Control: max-age=0');

        ob_end_clean();
        ob_start();

        $objWriter->save('php://output');
        exit();

    }

	public function exportZonePurchasedReport(){

        $this->language->load('report/zone_purchased');
        $this->load->model('report/zone');

        $data = $this->model_report_zone->getZonePurchased();


        $i=1;

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $this->language->get('column_name'))
            ->getColumnDimension('A')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $this->language->get('column_total_orders'))
            ->getColumnDimension('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $this->language->get('column_quantity'))
            ->getColumnDimension('C')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $this->language->get('column_total'))
            ->getColumnDimension('D')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $this->language->get('column_cost'))
            ->getColumnDimension('E')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('F'.$i, $this->language->get('column_profit'))
            ->getColumnDimension('F')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('G'.$i, $this->language->get('entry_status'))
            ->getColumnDimension('G')->setAutoSize(true);

        foreach($data['data'] as $row)
        {
            $i++;
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$i, html_entity_decode( $row['name'],ENT_QUOTES,'UTF-8')
           );
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $row['total_orders']);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $row['quantity']);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $row['total']);
            $objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $row['cost']);
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$i, $row['profit']);
            $objPHPExcel->getActiveSheet()->setCellValue('G'.$i, $row['status']);
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $filname = $this->language->get('heading_title') . '-' . time().'.xls';

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename='.$filname);
        header('Cache-Control: max-age=0');

        ob_end_clean();
        ob_start();

        $objWriter->save('php://output');
        exit();

    }

	public function exportZoneAvgPurchasedReport(){

        $this->language->load('report/zone_avg_purchased');
        $this->load->model('report/zone');

        $data = $this->model_report_zone->getZoneAvgPurchased();


        $i=1;

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $this->language->get('column_name'))
            ->getColumnDimension('A')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $this->language->get('column_total_orders'))
            ->getColumnDimension('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $this->language->get('column_quantity'))
            ->getColumnDimension('C')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $this->language->get('column_total'))
            ->getColumnDimension('D')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $this->language->get('column_cost'))
            ->getColumnDimension('E')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('F'.$i, $this->language->get('column_profit'))
            ->getColumnDimension('F')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('G'.$i, $this->language->get('entry_status'))
            ->getColumnDimension('G')->setAutoSize(true);

        foreach($data['data'] as $row)
        {
            $i++;
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$i, html_entity_decode( $row['name'],ENT_QUOTES,'UTF-8')
           );
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $row['total_orders']);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $row['quantity']);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $row['total']);
            $objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $row['cost']);
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$i, $row['profit']);
            $objPHPExcel->getActiveSheet()->setCellValue('G'.$i, $row['status']);
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $filname = $this->language->get('heading_title') . '-' . time().'.xls';

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename='.$filname);
        header('Cache-Control: max-age=0');

        ob_end_clean();
        ob_start();

        $objWriter->save('php://output');
        exit();

    }

    public function exportNewsletterSubscribers($filter)
    {
    	$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0); 

    	$filter_data = array(
			'name'              => $filter['filter_name']?:null,
			'email'             => $filter['filter_email'],
			'date_added'        => $filter['filter_date_added'],
			'status'            => $filter['filter_status'],
			'start' 			=> $filter['filter_start'],
			'limit' 			=> $filter['filter_limit']
		);
		
		$this->load->model('sale/newsletter/subscriber');

		$results = $this->model_sale_newsletter_subscriber->getAllSubscribers($filter_data);
		$customers = [];
		foreach($results as $result){
			$customers[]=array(
				'subscriber_id'		=> $result['newsletter_subscriber_id'],
				'customer_id'		=> $result['customer_id'],
				'name' 		        => $result['name'],
				'email' 			=> $result['email'],
				'status' 			=> $result['status'],
			);
		}
		// echo '<pre>'; print_r($customers); die();
		
		$i=1;
		$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, 'Newsletter Subscriber ID')->getColumnDimension('A')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, 'Customer ID')->getColumnDimension('B')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Name')->getColumnDimension('C')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, 'E-Mail')->getColumnDimension('D')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, 'Status')->getColumnDimension('E')->setAutoSize(true);
		
		foreach($customers as $customer){
			$i++;
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $customer['subscriber_id']);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $customer['customer_id']);
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $customer['name']);
			$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $customer['email']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $customer['status']);
		}

        $objPHPExcel->getDefaultStyle()->applyFromArray([
        	'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER]
    	]);

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5'); 
		
		$filename = 'customerList'.time().'.xls';
		header('Content-Type: application/vnd.ms-excel'); 
		header('Content-Disposition: attachment;filename='.$filename);
		header('Cache-Control: max-age=0');
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 300);
		ob_end_clean();
        ob_start();
		$objWriter->save('php://output'); 
		exit(); 
    }

    /**
     *
     * export all brand purchased
     */
    public function exportBrandPurchasedReport(){

        $this->language->load('report/brand_purchased');
        $this->load->model('report/brand');

        $data = $this->model_report_brand->getAllBrandPurchased();


        $i=1;

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $this->language->get('column_name'))
            ->getColumnDimension('A')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $this->language->get('column_current_quantity'))
            ->getColumnDimension('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $this->language->get('column_quantity'))
            ->getColumnDimension('C')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $this->language->get('column_total'))
            ->getColumnDimension('D')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $this->language->get('column_cost'))
            ->getColumnDimension('E')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('F'.$i, $this->language->get('column_profit'))
            ->getColumnDimension('F')->setAutoSize(true);

        foreach($data as $row)
        {
            $i++;
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$i, html_entity_decode( $row['name'],ENT_QUOTES,'UTF-8')
            );
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $row['current_quantity']);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $row['quantity']);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$i,  $this->currency->format($row['total']));
            $objPHPExcel->getActiveSheet()->setCellValue('E'.$i,  $this->currency->format($row['cost']));
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$i,  $this->currency->format($row['profit']));
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $filname = $this->language->get('heading_title') . '-' . time().'.xls';

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename='.$filname);
        header('Cache-Control: max-age=0');

        ob_end_clean();
        ob_start();

        $objWriter->save('php://output');
        exit();

    }
}