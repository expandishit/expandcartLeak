<?php

require_once(DIR_SYSTEM . 'library/phpqrcode/qrlib.php');

class ControllerSettingInvoice extends Controller{

    private $error = array();

    public function index(){
		
        $this->language->load('setting/setting');
		$this->language->load('setting/invoice');
		$this->load->model('localisation/language');
		
        $this->document->setTitle($this->language->get('title_invoice_settings'));

        $this->data['submit_link'] = $this->url->link('setting/invoice/saveSettings', '', 'SSL');
        $this->data['languages'] = $this->model_localisation_language->getLanguages();
        
		//responsible for show & hide zatca QR code setting 
		$in_saudia 		= false; 
		//hide & show expandPay according to merchant country in whmcs
		$whmcs			= new whmcs();
		$clientDetails 	= $whmcs->getClientDetails(WHMCS_USER_ID);
		$whiteList = ['QAZ123','OMARTAMMAM']	;		
		if(!empty($clientDetails)){
			$in_saudia  = (strtoupper($clientDetails['countrycode']) == 'SA');
		}else if (in_array(STORECODE,CHECKOUT_TEST_STORES) || in_array(strtoupper(STORECODE),$whiteList)){
			$in_saudia  = true ;
		}
		
		$this->data['in_saudia']   = $in_saudia;
		 
		$this->data['breadcrumbs'] = array(
            array('text' => $this->language->get('text_home'), 'href' => $this->url->link('common/home')),
            array('text' => $this->language->get('heading_title'), 'href' => $this->url->link('setting/setting')),
            array('text' => $this->language->get('title_invoice_settings'), 'href' => $this->url->link('setting/invoice'))
        );

        $this->value_from_post_or_config([
            'invoice_image_product',
            'config_order_invoice_logo_height',
            'config_invoice_product_image',
            'config_invoice_prefix',
            'config_soft_delete_status',
            'config_show_qr',
            'config_tax_number',
            'config_qr_compatible_with',
            'config_show_tax_number',
           // 'config_show_invoice_number',
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
            'config_qrcode_settings',
            'config_qrcode_text_fields'            
        ]);

        // To make this option not empty permenant.
        if($this->data['config_invoice_prefix'] == ""){
            $this->data['config_invoice_prefix'] = 'INV-00';
        }

		$this->template = 'setting/invoice.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );
		
        $this->response->setOutput($this->render_ecwig());
    }

	public function saveSettings(){
        $this->language->load('setting/invoice');
        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting('config', $this->request->post);
        $json['success'] = '1';
        $json['success_msg'] = $this->language->get('cit_save');
        $this->response->setOutput(json_encode($json));
    }

    private function value_from_post_or_config($array){
		
        foreach ($array as $elem)
        {
            $this->data[$elem] = $this->request->post[$elem] ?: $this->config->get($elem);
        }
    }

	/**
	 *
	 *
	 */
	public function generateZatcaQR(){
		
        $this->load->language('sale/order');

        $order_id 			= $this->request->get['order_id'];
        $language_id        = $this->request->get['language_id'];
        $language_code      = $this->request->get['language_code'];
        $language_directory = $this->request->get['language_directory'];

        $config_qrcode_settings = $this->config->get('config_qrcode_settings');
		
		 //get order info
        $this->load->model('sale/order');
        $order_info = $this->model_sale_order->getOrder($order_id);
		
        //Get total data
         $total_data = $this->model_sale_order->getOrderTotals($this->request->get['order_id']);
		$total = 0;
		$tax   = 0;
		foreach ($total_data as $total_record ){
			if($total_record['code'] == 'total'){
				$total =$total_record['value'];
			}
			else if($total_record['code'] == 'tax'){
				$tax =$total_record['value'];
			}
			
		}
		
		//this is temp till built a schema for invoicing & get data from it 
		$invoice_date = date("Y-m-d\TH:i:s.000\Z",strtotime($this->getDateByCurrentTimeZone($order_info['date_added'])));
		
		QRcode::png($this->_zatcaDataFormat ([
												'seller_name' 	=> $order_info ['store_name'], 
												'seller_tax' 	=> $this->config->get('config_tax_number'),
												'invoice_date' 	=> $invoice_date,
												'invoice_total' => number_format((float)$total, 2, '.', ''),
												'invoice_tax' 	=> number_format((float)$tax, 2, '.', '')										
												]));

    }
	/*
	 * 
	 * for more info refer to https://zatca.gov.sa/ar/E-Invoicing/SystemsDevelopers/Documents/20210528_ZATCA_Electronic_Invoice_Security_Features_Implementation_Standards_vShared.pdf
	 * page no.23 
	 *
	 *
	 */
	private function _zatcaDataFormat ($data){
	
		$data =  $this->_TLVFormat(1,$data['seller_name']??'')
				.$this->_TLVFormat(2,$data['seller_tax']??'')
				.$this->_TLVFormat(3,$data['invoice_date']??'') 
				.$this->_TLVFormat(4,$data['invoice_total']??'')
				.$this->_TLVFormat(5,$data['invoice_tax']??'');

		//TO:DO add the remaining data for the next phases 
		//from 1st January2023
		/*
			6  => Hash of XML invoice  
			7  => ECDSA signature
			8  => ECDSA public key
			 
			(For Simplified Tax Invoices and their associated notes)
			9  => the ECDSA signature of the cryptographic stamp’s public key by ZATCA’s technical CA
		*/
		return base64_encode($data);
	}
	
	/*
	 *
	 * TLV Format 
	 *
	 */
	private function _TLVFormat ($tag,$value){
		return pack("H*", sprintf("%02X", $tag)).pack("H*", sprintf("%02X", strlen($value))).($value);
	}

}
