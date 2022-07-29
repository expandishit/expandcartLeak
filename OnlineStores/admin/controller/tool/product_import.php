<?php

header('Cache-Control: no-cache, no-store');
ini_set('memory_limit', '2048M');
ini_set('max_execution_time', 4760);
ini_set('error_reporting', E_ALL);
include DIR_SYSTEM.'library/PHPExcel.php';


class ControllerToolProductimport extends Controller
{
	private $error;

    private $plan_id = PRODUCTID;

    /**
     * @var mixed
     */
    private $products_limit;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('plan/trial');
        $trial = $this->model_plan_trial->getLastTrial();
        if ($trial){
            $this->plan_id = $trial['plan_id'];
        }
        if($this->plan_id == 3){
            return $this->response->redirect($this->url->link('catalog/product', '', true));
        }

        $this->products_limit =  $this->genericConstants["plans_limits"][$this->plan_id]['products_limit'];

    }

	public function index()
	{

		if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
		{
			if ( $this->request->post['import_method_name'] == 'product' )
			{
				$this->importproduct();
			}
			else if ( $this->request->post['import_method_name'] == 'review' )
			{
				$this->importproductreview();
			}

			$this->data['form_return_status'] = $this->error ?: true;
		}

		$this->load->language('catalog/product');
		
		$this->load->language('tool/product_import');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/product');
		
		$this->load->model('tool/product_import');

		$this->data['heading_title'] = $this->language->get('heading_title');
		
		$this->data['entry_store'] = $this->language->get('entry_store');
		
		$examplefiles = HTTPS_CATALOG.'system/example/example.xls';
		$this->data['entry_import'] = sprintf($this->language->get('entry_import'),$examplefiles);
		
		$examplefiles = HTTPS_CATALOG.'system/example/rexample.xls';
		$this->data['entry_productreview'] = sprintf($this->language->get('entry_productreview'),$examplefiles);
		
		$this->data['productreviewimportaction'] = $this->url->link('tool/product_import/importproductreview','token='.$this->session->data['token'],'SSL');
		
		$this->data['importproduct'] = $this->url->link('tool/product_import/importproduct','token='.$this->session->data['token'],'SSL');
		
		$this->data['token'] = $this->session->data['token'];

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
		
		$this->data['errors'] = $this->session->data;
		
		$this->load->model('localisation/language');
		$this->data['languages'] = $this->model_localisation_language->getLanguages();
		
		$this->load->model('setting/store');
		$this->data['stores'] = $this->model_setting_store->getStores();

		$this->template = 'tool/product_import.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}
	
	public function importproduct()
	{						
		$this->load->language('catalog/product');
		$this->load->language('tool/product_import');
		$this->load->model('catalog/product');
		$this->load->model('tool/product_import');	
		$this->load->model('catalog/manufacturer');	
		
		if(($this->request->server['REQUEST_METHOD'] == 'POST')){
			if($this->request->files) {
				if(!empty($this->request->post['store_id'])){
					$store_id = $this->request->post['store_id'];
				}else{
					$store_id = 0;
				}
				
				if(!empty($this->request->post['language_id'])){
					$language_id = $this->request->post['language_id'];
				}else{
					$language_id = $this->config->get('config_langauge_id');
				}

				// make sure that the temp directory is exist before uploading.
                if(!is_dir(TEMP_DIR_PATH)){
                    mkdir(TEMP_DIR_PATH);
                }
			$file = basename($this->request->files['import']['name']);
			move_uploaded_file($this->request->files['import']['tmp_name'], TEMP_DIR_PATH . $file);
			$inputFileName = TEMP_DIR_PATH . $file;
			$extension = pathinfo($inputFileName);
			if($extension['basename']){
				if($extension['extension']=='xlsx' || $extension['extension']=='xls'|| $extension['extension']=='csv') {
					try{
						if($extension['extension']=='csv'){
							$inputFileType = 'CSV';
							$objReader = PHPExcel_IOFactory::createReader($inputFileType);
							$objPHPExcel = $objReader->load($inputFileName);
						}else{
							$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
						}
					}catch(Exception $e){
						die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
					}
					$allDataInSheet = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
					$i=0;
					$updateproduct = 0;
					$newproduct = 0;
					
					$this->load->model('localisation/tax_class');
					foreach($allDataInSheet as $k=> $value){
						if($i!=0){
							if($value['D']){
							//Tax Class
							if(empty($value['T'])){
								$value['T'] = $this->model_tool_product_import->CheckTaxClass($value['U']);
							}
							
							//Stock Status
							if(empty($value['Y'])){
								$value['Y'] = $this->model_tool_product_import->CheckStockStatus($value['Z']);
							}
							
							if(!empty($value['AB'])){
								 $value['AB'] = str_replace(' ','_',$value['AB']);
							}
							
							//manufacture
							$manufacturer_info = array();
							if(!empty($value['AN'])){
								$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($value['AN']);
							}
							if(!$manufacturer_info){
								if(!empty($value['AO'])){
								 $value['AN'] = $this->model_tool_product_import->CheckManufacturer($value['AO'],$store_id);
								}
							}
							
							//Categories
							$categoryids=array();
							if(!empty($value['AP'])){
							  foreach(explode(',',trim($value['AP'])) as $category_id){
								$cquery = $this->db->query("SELECT category_id FROM ".DB_PREFIX."category WHERE category_id = '".(int)$category_id."'");
								if(!empty($cquery->row['category_id'])){
								 $categoryids[]=$cquery->row['category_id'];
								}
							  }
							}
							
							$categoryidsx=array();
							if(!empty($value['AQ'])){
								 $categoryidsx = $this->model_tool_product_import->CheckCategories($value['AQ'],$store_id);
								 foreach($categoryidsx as $cate_id){
									 $categoryids[] = $cate_id;
								 }
							}
							
							//Filter
							$filters=array();
							if(!empty($value['AR'])){
								$filters = $this->model_tool_product_import->checkFilter($value['AR']);
							}
							
							//Download
							$downloads = array();
							if(!empty($value['AS'])){
								$downloads = explode(',',trim($value['AS']));
							}
							
							//Relaled Products
							$relaled_products = array();
							if(!empty($value['AT'])){
								$relaled_products = explode(',',trim($value['AT']));
							}
							
							//Attribute Group
							$attributes = array();
							if(!empty($value['AU'])){
								$attributes = $this->model_tool_product_import->checkAttribute($value['AU']);
							}
							
							//Options
							$options = array();
							if(!empty($value['AV'])){
								$options = $this->model_tool_product_import->checkoptions($value['AV']);
							}
							
							//Discount
							$discounts = array();
							if(!empty($value['AX'])){
								$discounts = $this->model_tool_product_import->checkdiscount($value['AX']);
							}
							
							//Specail
							$specails = array();
							if(!empty($value['AY'])){
								$specails = $this->model_tool_product_import->checkspecial($value['AY']);
							}
													
							//Image
							$imagen = str_replace(' ','_',$value['D']);
							$mainimage = $value['K'];
							if(!empty($value['K'])){
							  $value['K'] = str_replace('?dl=0','?raw=1',$value['K']);
							  $mainimage = $this->model_tool_product_import->fetchingimage($value['K'],$imagen);	
							}
							
							//Image
							$images = array();
							if(!empty($value['AZ'])){
								$ic=1;
								foreach(explode(';',trim($value['AZ'])) as $imageurl){
								  $imageurl = str_replace('?dl=0','?raw=1',$imageurl);
								  $imagename = $imagen.$ic++;
								  $images[] = $this->model_tool_product_import->fetchingimage($imageurl,$imagename);
								}
							}
							
							//Options Required
							$optionsrequired = array();
							if(!empty($value['AW'])){
								$optionsrequired = $this->model_tool_product_import->checkoptionsrequred($value['AW']);
							}

							$importdata=array(
							  'name' 	 			=> $value['D'],
							  'model'  	 			=> $value['E'],
							  'description' 		=> $value['F'],
							  'meta_titile' 		=> $value['G'],
							  'meta_description' 	=> $value['H'],
							  'meta_keyword' 		=> $value['I'],
							  'tag' 				=> $value['J'],
							  'image' 				=> $mainimage,
							  'sku' 				=> $value['L'],
							  'upc' 				=> $value['M'],
							  'ean' 				=> $value['N'],
							  'jan' 				=> $value['O'],
							  'isbn' 				=> $value['P'],
							  'mpn' 				=> $value['Q'],
							  'location' 			=> $value['R'],
							  'price' 				=> $value['S'],
							  'tax_class_id' 		=> $value['T'],
							  'quantity' 			=> $value['V'],
							  'minimum' 			=> $value['W'],
							  'subtract' 			=> $value['X'],
							  'stock_status_id' 	=> $value['Y'],
							  'shipping' 			=> $value['AA'],
							  'keyword' 			=> $value['AB'],
							  'date_available' 		=> ($value['AC'] ? $value['AC'] : date('Y-m-d')),
							  'length' 				=> $value['AD'],
							  'length_class_id' 	=> $value['AE'],
							  'width' 				=> $value['AG'],
							  'height' 				=> $value['AH'],
							  'weight' 				=> $value['AI'],
							  'weight_class_id' 	=> $value['AJ'],
							  'status' 				=> $value['AL'],
							  'sort_order' 			=> $value['AM'],
							  'manufacturer_id' 	=> $value['AN'],
							  'categories'			=> array_unique($categoryids),
							  'filters'				=> array_unique($filters),
							  'downloads' 			=> $downloads,
							  'relaled_products' 	=> $relaled_products,
							  'attributes'			=> $attributes,
							  'options'				=> $options,
							  'discounts'			=> $discounts,
							  'specails'			=> $specails,
							  'images'				=> $images,
							  'optionsrequired'		=> $optionsrequired,
							  'points' 				=> $value['BA'],
							  'viewed' 				=> $value['BB'],
							  'barcode' 			=> $value['BC'],
							  'cost_price' 			=> $value['BD'],
							);


							
							if($this->request->post['importtype']==2){
							 $product_id = $this->model_tool_product_import->getproductIDbymodel($value['E']);
								 if($product_id){
                                     // get old data
                                     $old_value = $this->getOldProductData($product_id);
                                     $this->model_tool_product_import->Editproduct($importdata,$product_id,$language_id,$store_id);
                                     // add data to log_history
                                     $this->load->model('setting/audit_trail');
                                     $pageStatus = $this->model_setting_audit_trail->getPageStatus("product");
                                     if($pageStatus){
                                         $log_history['action'] = 'update';
                                         $log_history['reference_id'] = $product_id;
                                         $log_history['old_value'] = $old_value;
                                         $log_history['new_value'] = json_encode($importdata);
                                         $log_history['type'] = 'product';
                                         $this->load->model('loghistory/histories');
                                         $this->model_loghistory_histories->addHistory($log_history);
                                     }
									 $updateproduct++;
								 }else{
                                     $product_id = $this->model_tool_product_import->addproduct($importdata,$language_id,$store_id);
                                     // add data to log_history
                                     $this->load->model('setting/audit_trail');
                                     $pageStatus = $this->model_setting_audit_trail->getPageStatus("product");
                                     if($pageStatus){
                                         $log_history['action'] = 'add';
                                         $log_history['reference_id'] = $product_id;
                                         $log_history['old_value'] = NULL;
                                         $log_history['new_value'] = json_encode($importdata);
                                         $log_history['type'] = 'product';
                                         $this->load->model('loghistory/histories');
                                         $this->model_loghistory_histories->addHistory($log_history);
                                     }
									 $newproduct++;
								 }
							}else{
								if((int)$value['A']){
									$product_info = $this->model_catalog_product->getproduct($value['A']);
									if($product_info){
                                        // get old data
                                        $old_value = $this->getOldProductData($value['A']);
                                        $this->model_tool_product_import->Editproduct($importdata,$value['A'],$language_id,$store_id);
										$updateproduct++;
									}else{
                                        $product_id = $this->model_tool_product_import->addoldproduct($importdata,$language_id,$store_id,$value['A']);
                                        // add data to log_history
                                        $this->load->model('setting/audit_trail');
                                        $pageStatus = $this->model_setting_audit_trail->getPageStatus("product");
                                        if($pageStatus){
                                            $log_history['action'] = 'add';
                                            $log_history['reference_id'] = $product_id;
                                            $log_history['old_value'] = NULL;
                                            $log_history['new_value'] = json_encode($importdata);
                                            $log_history['type'] = 'product';
                                            $this->load->model('loghistory/histories');
                                            $this->model_loghistory_histories->addHistory($log_history);
                                        }
										$newproduct++;
									}
								}else{
                                    $product_id = $this->model_tool_product_import->addproduct($importdata,$language_id,$store_id);
                                    // add data to log_history
                                    $this->load->model('setting/audit_trail');
                                    $pageStatus = $this->model_setting_audit_trail->getPageStatus("product");
                                    if($pageStatus){
                                        $log_history['action'] = 'add';
                                        $log_history['reference_id'] = $product_id;
                                        $log_history['old_value'] = NULL;
                                        $log_history['new_value'] = json_encode($importdata);
                                        $log_history['type'] = 'product';
                                        $this->load->model('loghistory/histories');
                                        $this->model_loghistory_histories->addHistory($log_history);
                                    }
									$newproduct++;
								}
							}
						 }
						}
						$i++;
					}
					if($newproduct || $updateproduct){
						$this->error = sprintf($this->language->get('text_success'),$newproduct,$updateproduct);
					}
				
					if(!$newproduct && !$updateproduct){
						$this->error = $this->language->get('text_no_data');
						
					}
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

	  unlink(TEMP_DIR_PATH . $file);
	  
	  return true;
	}

	private function getOldProductData($product_id){
        $this->load->model('catalog/product');
        $this->load->model('catalog/attribute');
        $this->load->model('catalog/option');

        $oldValue['product_info'] = $this->model_catalog_product->getProduct($product_id);
        $oldValue['product_description'] = $this->model_catalog_product->getProductDescriptions($product_id);
        $oldValue['product_store'] = $this->model_catalog_product->getProductStores($product_id);
        $oldValue['categories'] = $this->model_catalog_product->getProductCategories($product_id);
        $oldValue['filters'] = $this->model_catalog_product->getProductFilters($product_id);
        $oldValue['product_images'] = $this->model_catalog_product->getProductImages($product_id);
        $oldValue['product_discounts'] = $this->model_catalog_product->getProductDiscounts($product_id);
        $product_attributes = $this->model_catalog_product->getProductAttributes($product_id);

        $oldValue['product_attributes'] =  array();

        foreach ($product_attributes as $product_attribute) {
            $attribute_info = $this->model_catalog_attribute->getAttribute($product_attribute['attribute_id']);

            if ($attribute_info) {
                $oldValue['product_attributes'][] = array(
                    'attribute_id' => $product_attribute['attribute_id'],
                    'name' => $attribute_info['name'],
                    'GroupName' => $attribute_info['GroupName'],
                    'product_attribute_description' => $product_attribute['product_attribute_description']
                );
            }
        }

        $product_options = $this->model_catalog_product->getProductOptions($product_id);

        $oldValue['product_options'] = array();

        foreach ($product_options as $product_option) {
            $option_info = $this->model_catalog_option->getOption($product_option['option_id']);

            if ($option_info) {
                if (
                    $option_info['type'] == 'select' ||
                    $option_info['type'] == 'radio' ||
                    $option_info['type'] == 'checkbox' ||
                    $option_info['type'] == 'image' ||
                    $option_info['type'] == 'product'
                ) {
                    $product_option_value_data = array();

                    foreach ($product_option['product_option_value'] as $product_option_value) {
                        $optionValueData  = $this->model_catalog_option->getOptionValue($product_option_value['option_value_id']);
                        $product_option_value_data[] = array(
                            // Product Option Image PRO module <<
                            'images' => (isset($product_option_value['images']) ? $product_option_value['images'] : []),
                            // >> Product Option Image PRO module
                            'product_option_value_id' => $product_option_value['product_option_value_id'],
                            'option_value_id' => $product_option_value['option_value_id'],
                            'quantity' => $product_option_value['quantity'],
                            'subtract' => $product_option_value['subtract'],
                            'price' => $product_option_value['price'],
                            'price_prefix' => $product_option_value['price_prefix'],
                            'points' => $product_option_value['points'],
                            'points_prefix' => $product_option_value['points_prefix'],
                            'weight' => $product_option_value['weight'],
                            'weight_prefix' => $product_option_value['weight_prefix'],
                            'name' => $optionValueData['name']
                        );
                    }

                    $oldValue['product_options'][] = array(
                        'product_option_id' => $product_option['product_option_id'],
                        'product_option_value' => $product_option_value_data,
                        'option_id' => $product_option['option_id'],
                        'name' => $option_info['name'],
                        'type' => $option_info['type'],
                        'required' => $product_option['required']
                    );
                } else {
                    $oldValue['product_options'][] = array(
                        'product_option_id' => $product_option['product_option_id'],
                        'option_id' => $product_option['option_id'],
                        'name' => $option_info['name'],
                        'type' => $option_info['type'],
                        'option_value' => $product_option['option_value'],
                        'required' => $product_option['required']
                    );
                }
            }
        }

        $oldValue['product_discounts'] = $this->model_catalog_product->getProductDiscounts($product_id);
        return $oldValue;
    }
	
	
	public function importproductreview()
	{
		$this->load->model('tool/product_import');
		$this->load->language('tool/product_import');

		if ( $this->request->server['REQUEST_METHOD'] == 'POST')
		{
			if ( $this->request->files )
			{
				$file = basename($this->request->files['import']['name']);
				move_uploaded_file($this->request->files['import']['tmp_name'], $file);
				$inputFileName = $file;
				$extension = pathinfo($inputFileName);
				
				if ($extension['basename'] )
				{
					if ( $extension['extension']=='xlsx' || $extension['extension']=='xls' || $extension['extension']=='csv' )
					{
						try
						{
							if ( $extension['extension']=='csv' )
							{
								$inputFileType = 'CSV';
								$objReader = PHPExcel_IOFactory::createReader($inputFileType);
								$objPHPExcel = $objReader->load($inputFileName);
							}
							else
							{
								$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
							}
						}
						catch( Exception $e )
						{
							die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
						}

						$allDataInSheet = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
						$i = 0;
						$newentry = 0;
						$updateentry = 0;
						
						foreach ( $allDataInSheet as $value )
						{
							if ( $i != 0 )
							{
								$filter_data=array(
									'review_id' 	=> $value['A'],
									'product_id'  	=> $value['B'],
									'customer_id'  	=> $value['C'],
									'author' 	   	=> $value['D'],
									'text'   		=> $value['E'],
									'rating'   		=> $value['F'],
									'status'  		=> $value['G'],
									'date_added' 	=> $value['H'],
									'date_modified'	=> $value['I']
								);

								if( (int) $value['A'] )
								{
									$return_data = $this->model_tool_product_import->getReview($value['A']);
									
									if ( $return_data )
									{
										$this->model_tool_product_import->editproductreview($filter_data);
									 	$updateentry++;
									}
									else
									{
										$this->model_tool_product_import->addexsitproductreview($filter_data);
										$newentry++;
									}
								}
								else
								{
									$this->model_tool_product_import->addproductreview($filter_data);
									$newentry++;
								}
							}
							
							$i++;
						}
						
						$this->error = sprintf($this->language->get('text_productreviewsuccess'),$newentry,$updateentry);
					}
					else
					{
						$this->error = $this->language->get('error_warning');
					}
				}
				else
				{
					$this->error = $this->language->get('error_warning');
				}

				if ( $inputFileName )
				{
					unlink($inputFileName);
				}
			}
			else
			{
				$this->error = $this->language->get('error_warning');
			}
		}

	  	return true;
	}

	public function validate_form($data)
	{
        $this->load->model('catalog/product');

       $limit_reached =
            $this->products_limit &&
            ( ($this->model_catalog_product->getTotalProductsCount() + 1) > $this->products_limit )
        ;

       if ($limit_reached){
           $this->error[] = $this->language->get('error_limit');
       }else{
           # code...
           if($this->request->files['import']['name'] == ""){
               $this->error[] = $this->language->get('error_warning');
           }
       }

		if($this->error)
			return false;
		return true;
	}
	public function import(){
		$this->load->language('catalog/product');
		
		$this->load->language('tool/product_import');

		if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
		{
			if(!isset($this->request->post['mapping_form'])){
				if(!$this->validate_form($this->request->post)){
					$this->data['importing_errors'] = $this->error;
				}
				else{
                    $uploaded_product_file_name = $this->upload_product_file();
					if($uploaded_product_file_name){
						$this->session->data['uploaded_product_file_name'] = $uploaded_product_file_name;
						$this->session->data['language_id'] = $this->request->post['language_id'];
						$this->session->data['option'] = $this->request->post['option'];
						$status = $this->check_product_file_structure($this->session->data['uploaded_product_file_name']);
						if(is_array($status)){
							$this->data['mapping'] = true;
							$this->data['upload_file_fields'] = $status[2];
							$this->data['unmatching_fields'] = $status[1];
							$this->data['fields_uploaded_file'] = $status[0];
							$this->data['form_return_status'] = $this->error ?$this->error: $this->language->get('text_file_uploaded_sucess');
						}
						else{
							$this->process_uploaded_product_file($this->session->data['uploaded_product_file_name'],$this->session->data['language_id']);
						}
					}
					else{
						$this->data['importing_errors'] = $this->error;
					}					
				}
			}
			else{
				$this->process_uploaded_product_file($this->session->data['uploaded_product_file_name'],$this->session->data['language_id']);
                $this->response->redirect($this->url->link('tool/product_import/import', '', true));
            }
		}
		
		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/product');
		
		$this->load->model('tool/product_import');

		// Check the last import process status.
		$import_data = $this->model_tool_product_import->checkImportProcessStatus();

		$this->data['import_status'] = $import_data['import_status'];

		if(!is_null($this->data['import_status']))
		{ 
			if($this->data['import_status'] == 1 || $this->data['import_status'] == 2){                           
				$this->data['message'] =$import_data['message'];
                                $fetchedImagesFile = TEMP_DIR_PATH.'fetchedImages';
                                @unlink($fetchedImagesFile);
				if(!empty($import_data['error']))
					$this->data['importing_errors'] = $import_data['error'];
			}
			else{
				$this->data['message'] = $this->language->get('text_pending_process');
			}
		}
		

		$this->data['heading_title'] = $this->language->get('heading_title');
		
		$this->data['entry_store'] = $this->language->get('entry_store');
		
		$examplefiles = HTTPS_CATALOG.'system/example/example.xls';
		$this->data['entry_import'] = sprintf($this->language->get('entry_import'),$examplefiles);
		
		$examplefiles = HTTPS_CATALOG.'system/example/rexample.xls';
		$this->data['entry_productreview'] = sprintf($this->language->get('entry_productreview'),$examplefiles);
		
		$this->data['productreviewimportaction'] = $this->url->link('tool/product_import/importproductreview','token='.$this->session->data['token'],'SSL');
		
		$this->data['importproduct'] = $this->url->link('tool/product_import/importproduct','token='.$this->session->data['token'],'SSL');
		
		$this->data['token'] = $this->session->data['token'];

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
		
		$this->data['errors'] = $this->session->data;
		
		$this->load->model('localisation/language');
		$this->data['languages'] = $this->model_localisation_language->getLanguages();
		
		$this->load->model('setting/store');
		$this->data['stores'] = $this->model_setting_store->getStores();
		
		$this->template = 'tool/p_import.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	
	}

	public function upload_product_file()
	{
		# code...
		$this->load->model('tool/product_import');

        // make sure that the temp directory is exist before uploading.
        if(!is_dir(TEMP_DIR_PATH)){
            mkdir(TEMP_DIR_PATH);
        }
		$file = basename($this->request->files['import']['name']);
		move_uploaded_file($this->request->files['import']['tmp_name'], TEMP_DIR_PATH . $file);
		$inputFileName = TEMP_DIR_PATH . $file;
		$extension = pathinfo($inputFileName);
		$file_extension = $extension['extension'];

		if($extension['basename'] && ($file_extension == 'xlsx' || $file_extension =='xls'|| $file_extension =='csv')){
			return $inputFileName;
		}

		unlink($file);

		$this->error[] = $this->language->get('error_warning');
		return false;

	}

	public function check_product_file_structure($inputFileName)
	{
		# code...
		$extension = pathinfo($inputFileName);
		$file_extension = $extension['extension'];
		try
		{
			if ( $file_extension =='csv' )
			{
				$inputFileType = 'CSV';
				$objReader = PHPExcel_IOFactory::createReader($inputFileType);
				$objPHPExcel = $objReader->load($inputFileName);
			}
			else
			{
				$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
			}
		}
		catch( Exception $e )
		{
			die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
		}
		
		$product_file_structure_file_name = "";

		switch($this->session->data['option']){
			case '0':
				$product_file_structure_file_name="no_options_product_file_structure.json";
				break;
			case '1':
				$product_file_structure_file_name="product_file_structure.json";
				break;
			case '2':
				$product_file_structure_file_name="advanced_options_product_file_structure.json";
				break;
		}
		$expand_product_file_structure_file = file_get_contents(__DIR__."/".$product_file_structure_file_name);
		$file_structure = json_decode($expand_product_file_structure_file,true);
		$allDataInSheet = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);

		// todo prevent uploading if the file exceeds the product plan limit

        //        $ids= array();
        //
        //        $language_count = 1;
        //        $languages=array();
        //        if (strtolower($language_id) == "all"){
        //            $j=0;
        //            foreach($allDataInSheet as $value){
        //                if($j++!=0 && !empty($value['B'])){
        //                    $languages[]=$value['B'];
        //                }
        //            }
        //            $languages = array_unique($languages);
        //            $language_count = count($languages);
        //        }
        //
        //        $uploaded_products_count=0;
        //        foreach ($allDataInSheet as $key => $item){
        //            if ($key != 1){
        //                if ( $item['B'] == $languages[0]){
        //                    $uploaded_products_count++;
        //                }
        //            }
        //
        //            if ($key != 1 && !in_array($item['A'],$ids)){
        //                $ids[]= $item['A'];
        //            }
        //        }
        //
        //        $updated_products_count = $this->model_catalog_product->getProductsCountByIds($ids);
        //
        //        if ($this->products_limit){
        //            $current_product_count = $this->model_catalog_product->getTotalProductsCount();
        //            $language_count = $language_count??1;
        //            if ( !( $this->products_limit >= ($uploaded_products_count + $current_product_count - $updated_products_count) ) ){
        //                $this->limit_reached = true;
        //            }
        //        }

        // Check the last import process status.
		$import_data = $this->model_tool_product_import->checkImportProcessStatus();
		$mapping_fields = [];
		$uploaded_file_fields = $allDataInSheet[1];
		$expand_file_fields = $file_structure;

		switch($this->data->session['option']){
			case '0':
				unset($expand_file_fields['AV']);
				unset($expand_file_fields['AW']);
				break;
		}
		
		if(count($import_data) > 0 && $import_data['file_mapping'] != "default"){
			$last_product_file_structure = json_decode($import_data['file_mapping'],true);
			
			foreach ($last_product_file_structure as $cell => $value) {
				# code...
				if($value != "0" && array_key_exists($value,$uploaded_file_fields)){
					$mapping_fields[$cell] = $value;
				}
			}
			return array($mapping_fields,$expand_file_fields,$uploaded_file_fields);
		}

		// If it's the first time to import products.
		$mapping_fields = array_intersect($expand_file_fields,$uploaded_file_fields);

		foreach ($mapping_fields as $cell => $value) {
			$mapping_fields[$cell] = $cell;
		}
		
		return array($mapping_fields,$expand_file_fields,$uploaded_file_fields);
	}

	public function process_uploaded_product_file($file,$language_id)
	{
		$file_name = $file;
		$extension = pathinfo($file);
		$file_extension = $extension['extension'];

		$store_id = $this->config->get('config_store_id')?$this->config->get('config_store_id'):0;

		# code...
		// Insert a new record in import_files_tb.
		$data = array(
			'id' => null,
			'filename' => $file,
			'import_type' => 'product',
			'import_date' => date("Y-m-d H:i:s"),
			'file_mapping' => 'default',
			'import_status' => 0,
		);
		if(isset($this->request->post['mapping_form']) && $this->request->post['mapping_form'] == "true"){
			$product_file_structure_user_defined = $this->request->post['product'];
			$data['file_mapping'] = json_encode($product_file_structure_user_defined);
		}
		$this->load->model('tool/product_import');
		$file_id = $this->model_tool_product_import->add_import_file($data);
		// Run background process.
		$file_location = DIR_SYSTEM.'library/import_file.php';
		$options = $this->session->data['option'];
		// To check the error fired from importProduct.php
		// 1. remove >/dev/null 2>&1 &
		// 2. put echo before shell_exec
		// 3. put die() after shell_exec
        $storecode = STORECODE;
        shell_exec("php $file_location \"$file_name\" $file_id $language_id $store_id $options $storecode >/dev/null 2>&1 &");		
	}

}
