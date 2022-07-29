<?php
header('Cache-Control: no-cache, no-store');
ini_set('memory_limit', '1024M');
ini_set('max_execution_time', 900);
ini_set('error_reporting', E_ALL);
include DIR_SYSTEM.'library/PHPExcel.php';
class ControllerToolWImportTool extends Controller {
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

	public function index()
	{

		if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
		{
			if ( $this->request->post['import_method_name'] == 'customers' )
			{
				$this->importcustomer();
			}
			else if ( $this->request->post['import_method_name'] == 'orders' )
			{
				$this->importorder();
			}

			$this->data['form_return_status'] = $this->error ?: true;
		}

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
			$this->data['filter_limit'] = $this->config->get('config_limit_admin');
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
		
		$this->data['customer_groups'] = $this->model_tool_excel_order_port->getCustomerGroups();
		
		$this->load->model('localisation/order_status');
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		if (isset($this->request->get['filter_limit'])) {
			$this->data['filter_limit'] = $this->request->get['filter_limit'];
		} else {
			$this->data['filter_limit'] = $this->config->get('config_limit_admin');
		}
		
		////Import Start
		$examplefiles = '';
		
		$examplecustomerfiles = HTTP_CATALOG.'system/wexcel/customerexample.xls';
		$this->data['text_import_customer'] = sprintf($this->language->get('text_import_customer'),$examplecustomerfiles);
		
		$exampleorderfiles = HTTP_CATALOG.'system/wexcel/orderexample.xls';
		
		$this->data['entry_orderimport'] = sprintf($this->language->get('entry_orderimport'),$exampleorderfiles);
		
		$this->data['orderaction'] = $this->url->link('tool/w_import_tool/importorder','token='.$this->session->data['token'],'SSL');
		
		$this->data['customeraction'] = $this->url->link('tool/w_import_tool/importcustomer','token='.$this->session->data['token'],'SSL');
		
		$this->template = 'tool/w_import_tool.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}
	
	public function importcustomer(){
		$this->load->model('tool/excel_order_port');
		$this->load->language('tool/excel_order_port');
		if(($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validatecustomerForm()){
			if(!empty($this->request->post['password_format'])){
			  $password_format = $this->request->post['password_format'];
			}else{
			  $password_format = 'P';
			}
			
			if($this->request->files) {
                // make sure that the temp directory is exist before uploading.
                if(!is_dir(TEMP_DIR_PATH)){
                    mkdir(TEMP_DIR_PATH);
                }
			$file = basename($this->request->files['import']['name']);
			move_uploaded_file($this->request->files['import']['tmp_name'], TEMP_DIR_PATH . $file);
			$inputFileName = TEMP_DIR_PATH .$file;
			$extension = pathinfo($inputFileName);
			if($extension['basename']){
				if($extension['extension']=='xlsx' || $extension['extension']=='xls') {
					try{
						$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
					}catch(Exception $e){
						die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
					}
					$allDataInSheet = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
					$i=0;
					$newentry=0;
					$updateentry=0;
					foreach($allDataInSheet as $value){
						if($i!=0){
							$customer_id  	= $value['A'];
							$firstname  	= $value['B'];
							$lastname   	= $value['C'];
							$email 	    	= $value['D'];
							$password   	= $value['E'];
							$salt   		= $value['F'];
							$telephone  	= $value['G'];
							$fax 			= $value['H'];
							$customergroup_id 	= $value['I'];
							$company 		= $value['J'];
							$address1 		= $value['K'];
							$address2 		= $value['L'];
							$postcode 		= trim($value['M']);
							$city 			= trim($value['N']);
							$zone_id 		= $value['O'];
							$country_id 	= $value['P'];
							$approved 		= $value['Q'];
							$newsletter 	= $value['R'];
							$status 		= $value['S'];
							
							$filter_data=array(
								'firstname' 		=> $firstname,
								'lastname'			=> $lastname,
								'email'				=> $email,
								'password'			=> $password,
								'salt'				=> $salt,
								'telephone'			=> $telephone,
								'fax'				=> $fax,
								'customer_group_id'	=> $customergroup_id,
								'company'			=> $company,
								'address1'			=> $address1,
								'address2'			=> $address2,
								'postcode'			=> $postcode,
								'city'				=> $city,
								'state'				=> $zone_id,
								'country'			=> $country_id,
								'approved'			=> $approved,
								'newsletter'		=> $newsletter,
								'status'			=> $status,
							);
							
							if((int)$customer_id){
							  $customerinfo = $this->model_tool_excel_order_port->getcustomer($customer_id);
							  if($customerinfo){
								  $this->model_tool_excel_order_port->EditCustomer($filter_data,$password_format,$customer_id);
								  $updateentry++;
							  }else{
								  $return_data = $this->model_tool_excel_order_port->addoldcustomer($filter_data,$password_format,$customer_id);
								  $newentry++;
							  }
							}else{
								$return_data = $this->model_tool_excel_order_port->addcustomer($filter_data,$password_format);
								$newentry++;
							}
						}
						$i++;
					}
					$this->error = sprintf($this->language->get('text_customersuccess'),$newentry,$updateentry);
				} else{
					$this->error = $this->language->get('error_warning');
				}
			}else{
				$this->error = $this->language->get('error_warning');
			}
			if($inputFileName){
				unlink($inputFileName);
			}
			
		  }else{
			$this->error = $this->language->get('error_warning');
		  }
	  }
	  return true;
	}
	
	public function importorder(){
		$this->load->language('tool/excel_order_port');
	    $this->load->model('tool/excel_order_port');
		
		if(($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()){
			if($this->request->files){
				$file = basename($this->request->files['import']['name']);
				move_uploaded_file($this->request->files['import']['tmp_name'], $file);
				$inputFileName = $file;
				$extension = pathinfo($inputFileName);
				if($extension['basename']){
				if(isset($extension['extension']) && ($extension['extension']=='xlsx' || $extension['extension']=='xls')){
                    try{
                        PHPExcel_Cell::setValueBinder( new PHPExcel_Cell_AdvancedValueBinder() );
                        $inputFileType = 'Excel5';
                        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                        $objPHPExcel = $objReader->load($inputFileName);
                        $objPHPExcel->getActiveSheet()->setTitle(pathinfo($inputFileName,PATHINFO_BASENAME));
                        $loadedSheetNames = $objPHPExcel->getSheetNames();
                        $sheetOrderData = $objPHPExcel->getSheet(0)->toArray(null,true,true,true);
                    }catch (Exception $ex){
                        $this->error = $ex->getMessage();
                        return false;
                    }
                    $i=0;
                    $order_new = 0;
                    $order_update = 0;
					foreach($sheetOrderData as $sheet){
					  if($i!=0){
						$orderproducts=array();
						$sheetproductData = $objPHPExcel->getSheet(1)->toArray(null,true,true,true);
						$op=0;
						foreach($sheetproductData as $PDATA){
						 if($op!=0){
							if($sheet['A']==$PDATA['B']){
							  $productoptions=array();
							  $sheetoptionData = $objPHPExcel->getSheet(2)->toArray(null,true,true,true);
							  $po=0;
							  foreach($sheetoptionData as $option){
								 if($op!=0){
									 if($PDATA['A']==$option['C']){
										$productoptions[]=array(
										  'order_option_id'			=> $option['A'],
										  'order_id'				=> $option['B'],
										  'order_product_id'		=> $option['C'],
										  'product_option_id'		=> $option['D'],
										  'product_option_value_id'	=> $option['E'],
										  'name'					=> $option['F'],
										  'value'					=> $option['G'],
										  'type'					=> $option['H'],
										);
									 }
								 }
							  }
							  $delivery_solt = array();
							  $delivery_solt[]=array(
                                  'day_name'			=> isset($PDATA['K']) ? $PDATA['K'] : "",
                                  'delivery_date'	    => isset($PDATA['L']) ? $PDATA['L'] : "",
                                  'slot_description'	=> isset($PDATA['M']) ? $PDATA['M'] : "",

                              );
								
							  $orderproducts[]=array(
								'order_product_id'	=> $PDATA['A'],
								'order_id'			=> $PDATA['B'],
								'product_id'		=> $PDATA['C'],
								'name'				=> $PDATA['D'],
								'model'				=> $PDATA['E'],
								'quantity'			=> $PDATA['F'],
								'price'				=> $PDATA['G'],
								'total'				=> $PDATA['H'],
								'tax'				=> $PDATA['I'],
								'reward'			=> $PDATA['J'],
								'delivery_solt'		=> $delivery_solt,
								'productoptions'	=> $productoptions,
							  );
							}
						  }
						 $op++;
						}
						
						//Order Total Sheet
						$sheetotals = $objPHPExcel->getSheet(3)->toArray(null,true,true,true);
						$order_totals=array();
						$ot=0;
						foreach($sheetotals as $totalsheet){
						  if($ot!=0){
							if($sheet['A']==$totalsheet['B']){
								$order_totals[]=array(
								  'order_total_id'	=> $totalsheet['A'],
								  'order_id'		=> $totalsheet['B'],
								  'code'			=> $totalsheet['C'],
								  'title'			=> $totalsheet['D'],
								  'value'			=> $totalsheet['E'],
								  'sort_order'		=> $totalsheet['F'],
								);
							}
						  }
						  $ot++;
						}
						
						//Order History
						$sheehistorys = $objPHPExcel->getSheet(4)->toArray(null,true,true,true);
						$oh=0;
						$orderhistorys=array();
						foreach($sheehistorys as $history){
							if($oh!=0){
								if($sheet['A']==$history['B']){
								  $orderhistorys[]=array(
									'order_history_id'	=> $history['A'],
									'order_id'			=> $history['B'],
									'order_status_id'	=> $history['C'],
									'order_status'		=> $history['D'],
									'notify'			=> $history['E'],
									'comment'			=> $history['F'],
									'date_added'		=> $history['G'],
									'user_id'			=> $history['H'],
								  );
								}
						    }
							$oh++;
						}
						$sheetvouchers = $objPHPExcel->getSheet(5)->toArray(null,true,true,true);
						$ordervouchers=array();
						$ov=0;
						foreach($sheetvouchers as $voucher){
						  if($ov!=0){
							  if($sheet['A']==$voucher['B']){
								  $ordervouchers[]=array(
									'order_voucher_id' => $voucher['A'],
									'order_id' 		   => $voucher['B'],
									'voucher_id' 	   => $voucher['C'],
									'description' 	   => $voucher['D'],
									'code' 	   		   => $voucher['E'],
									'from_name' 	   => $voucher['F'],
									'from_email' 	   => $voucher['G'],
									'to_name' 	   	   => $voucher['H'],
									'vocher_theme_id'  => $voucher['I'],
									'message'  		   => $voucher['J'],
									'amount'  		   => $voucher['K'],
								  );
							  }
						   }
						   $ov++;
						}
						
						 $orders=array(
						  'order_id' 				=> $sheet['A'],
						  'invoice_no'  			=> $sheet['B'],
						  'invoice_prefix'  		=> $sheet['C'],
						  'store_id' 	 			=> $sheet['D'],
						  'store_name' 	 			=> $sheet['E'],
						  'store_url' 	 			=> $sheet['F'],
						  'customer_id' 			=> $sheet['G'],
						  'customer' 				=> $sheet['H'],
						  'customer_group_id' 		=> $sheet['I'],
						  'firstname' 				=> $sheet['J'],
						  'lastname' 				=> $sheet['K'],
						  'email' 					=> $sheet['L'],
						  'telephone' 				=> $sheet['M'],
						  'fax' 					=> $sheet['N'],
						  'custom_field' 			=> $sheet['O'],
						  'payment_firstname' 		=> $sheet['P'],
						  'payment_lastname' 		=> $sheet['Q'],
						  'payment_company' 		=> $sheet['R'],
						  'payment_address_1' 		=> $sheet['S'],
						  'payment_address_2' 		=> $sheet['T'],
						  'payment_postcode' 		=> $sheet['U'],
						  'payment_city' 			=> $sheet['V'],
						  'payment_zone_id' 		=> $sheet['W'],
						  'payment_zone' 			=> $sheet['X'],
						  'payment_zone_code' 		=> $sheet['Y'],
						  'payment_country_id' 		=> $sheet['Z'],
						  'payment_country' 		=> $sheet['AA'],
						  'payment_iso_code_2' 		=> $sheet['AB'],
						  'payment_iso_code_3' 		=> $sheet['AC'],
						  'payment_address_format'  => $sheet['AD'],
						  'payment_custom_field'    => $sheet['AE'],
						  'payment_method'          => $sheet['AF'],
						  'payment_code'            => $sheet['AG'],
						  'shipping_firstname'      => $sheet['AH'],
						  'shipping_lastname'       => $sheet['AI'],
						  'shipping_company'        => $sheet['AJ'],
						  'shipping_address_1'      => $sheet['AK'],
						  'shipping_address_2'      => $sheet['AL'],
						  'shipping_postcode'       => $sheet['AM'],
						  'shipping_city'           => $sheet['AN'],
						  'shipping_zone_id'        => $sheet['AO'],
						  'shipping_zone'           => $sheet['AP'],
						  'shipping_zone_code'      => $sheet['AQ'],
						  'shipping_country_id'     => $sheet['AR'],
						  'shipping_country'        => $sheet['AS'],
						  'shipping_iso_code_2'     => $sheet['AT'],
						  'shipping_iso_code_3'     => $sheet['AU'],
						  'shipping_address_format' => $sheet['AV'],
						  'shipping_custom_field'   => $sheet['AW'],
						  'shipping_method'         => $sheet['AX'],
						  'shipping_code'           => $sheet['AY'],
						  'comment'                 => $sheet['AZ'],
						  'total'                   => $sheet['BA'],
						  'reward'                  => $sheet['BB'],
						  'order_status_id'         => $sheet['BC'],
						  'affiliate_id'            => $sheet['BD'],
						  'affiliate_firstname'     => $sheet['BE'],
						  'affiliate_lastname'      => $sheet['BF'],
						  'commission'              => $sheet['BG'],
						  'language_id'             => $sheet['BH'],
						  'language_code'           => $sheet['BI'],
						  'language_directory'      => $sheet['BJ'],
						  'currency_id'             => $sheet['BK'],
						  'currency_code'           => $sheet['BL'],
						  'currency_value'          => $sheet['BM'],
						  'ip'                      => $sheet['BN'],
						  'forwarded_ip'            => $sheet['BO'],
						  'user_agent'              => $sheet['BP'],
						  'accept_language'         => $sheet['BQ'],
						  'date_added'              => $sheet['BR'],
						  'date_modified'           => $sheet['BS'],
						  'orderproduct'			=> $orderproducts,
						  'order_total'				=> $order_totals,
						  'orderhistorys'			=> $orderhistorys,
						  'ordervouchers'			=> $ordervouchers,
						  );
						  if((int)$sheet['A']){
							$order_info = $this->model_tool_excel_order_port->getOrder($sheet['A']);
							if($order_info){
								$this->model_tool_excel_order_port->editOrder($orders,$sheet['A']);
								$order_update++;
							}else{
								$this->model_tool_excel_order_port->addorder($orders);
								$order_new++;
							}
						  }
					 }
					$i++;
					}
					$this->error = sprintf($this->language->get('text_orersuccess'),$order_update,$order_new);
				}else{
					$this->error = $this->language->get('error_warning');
					
				}
			  }else{
					$this->error = $this->language->get('error_warning');
					
			  }
			  if($inputFileName){
				unlink($inputFileName);
			}
			}
		}
		return true;
	}
	
	protected function validate(){
		if(!$this->user->hasPermission('modify', 'tool/w_import_tool')){
			$this->error = $this->language->get('error_permission');
			$this->error = $this->language->get('error_permission');
		}
		return !$this->error;
	}
	
	protected function validatecustomerForm(){
		if(!$this->user->hasPermission('modify', 'tool/w_import_tool')){
			$this->error = $this->language->get('error_permission');
			$this->error = $this->language->get('error_permission');
		}
		
		if(empty($this->request->post['password_format'])){
			$this->error = $this->language->get('error_password_format');
			$this->error = $this->error['warning'];
		}
		
		return !$this->error;
	}
}