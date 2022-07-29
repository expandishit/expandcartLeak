<?php

class ControllerAmazonAmazonMapMapProductData extends Controller {
	private $error = array();
  	private $csvMimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');
	private $route = 'amazon/amazon_map/product';
	private $common = 'amazon/amazon_map/common';
	private $map_product_route = 'amazon/amazon_map/map_product_data';

	public function __construct($registory) {
		parent::__construct($registory);
		$this->load->model($this->route);
		$this->_amazonMapProduct = $this->model_amazon_amazon_map_product;
		$this->load->language($this->common);
		$this->load->language($this->route);
  	}
  	public function index() {
      	$data                  = [];
		$url                   = '';
        $this->load->language($this->map_product_route);
		$this->document->setTitle($this->language->get('heading_title'));
				$data['breadcrumbs']   = array();

				$data['breadcrumbs'][] = array(
					'text' => $this->language->get('text_home'),
					'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
				);

				$data['breadcrumbs'][] = array(
					'text' => $this->language->get('heading_title'),
					'href' => $this->url->link('amazon_map/map_product_data', 'token=' . $this->session->data['token'], true)
				);
				if (isset($this->error['warning'])) {
					$data['error_warning'] = $this->error['warning'];
				} else {
					$data['error_warning'] = '';
				}

				if (isset($this->session->data['success'])) {
					$data['success'] = $this->session->data['success'];

					unset($this->session->data['success']);
				} else {
					$data['success'] = '';
				}

				$filter_array_values = array(
					'filter_name'   => '',
					'category_name' => ''
					// 'filter_oc_cat_name'  => '',
					// 'filter_oc_price'     => NULL,
					// 'filter_oc_quantity'  => NULL
				);

				foreach ($filter_array_values as $filter_array_value => $default_value) {
					$data[$filter_array_value]=${$filter_array_value} = isset($this->request->get[$filter_array_value]) ? $this->request->get[$filter_array_value] : $default_value;
         if(isset($this->request->get[$filter_array_value])) {
					$url .= '&'.$filter_array_value.'=' . $this->request->get[$filter_array_value];
				}
			}


					if (isset($this->request->get['page'])) {
						$page = $this->request->get['page'];
					} else {
						$page = 1;
					}

					if (isset($this->request->post['selected'])) {
						$data['selected'] = (array)$this->request->post['selected'];
					} else {
						$data['selected'] = array();
					}
				 		$data['oc_products'] = array();

				 		$filter_data = array(
				 			'filter_oc_prod_name'	=> $filter_name,
				 			'filter_oc_cat_name' 	=> $category_name,
				 			'start' 							=> ($page - 1) * $this->config->get('config_limit_admin'),
				 			'limit' 							=> $this->config->get('config_limit_admin')
				 		);

				 		$getGlobalOption = $this->Amazonconnector->__getOcAmazonGlobalOption();

				 		$amazonProductTotal = $this->_amazonMapProduct->getTotalOcUnmappedProducts($filter_data);

				 		$results = $this->_amazonMapProduct->getOcUnmappedProducts($filter_data);

				 		$this->load->model('catalog/product');
				 		$this->load->model('catalog/category');

				 		if($results){
				 			foreach ($results as $result) {
				 				// Categories
				 				if (isset($result['product_id'])) {
				 					$categories = $this->model_catalog_product->getProductCategories($result['product_id']);
				 				} else {
				 					$categories = array();
				 				}

				 				$product_categories = array();

				 				foreach ($categories as $category_id) {
				 					$category_info = $this->model_catalog_category->getCategory($category_id);

				 					if ($category_info) {
				 						$product_categories[] = array(
				 							'category_id' => $category_info['category_id'],
				 							'name'        => ($category_info['path']) ? $category_info['path'] . ' &gt; ' . $category_info['name'] : $category_info['name']
				 						);
				 					}
				 				}

				 				$data['oc_products'][] = array(
				 					'product_id' 		=> $result['product_id'],
				 					'name' 					=> $result['name'],
				 					'category' 			=> $product_categories,
				 					'price'	 				=> $result['price'],
				 					'quantity'			=> $result['quantity'],
				 				);
				 			}
				 		}
						 	
			$data['token']        	 = $this->session->data['token'];
			$data['add']					 	= $this->url->link('amazon/amazon_map/map_product_data/add','token='. $this->session->data['token'], true);
			$data['import_product'] = $this->url->link('amazon/amazon_map/map_product_data/csv','', true);
			$data['export_product'] = $this->url->link('amazon/amazon_map/map_product_data/exportProduct','', true);
			
			$pagination    					= new Pagination();
			$pagination->total 			= $amazonProductTotal;
			$pagination->page 			= $page;
			$pagination->limit 			= $this->config->get('config_limit_admin');
			$pagination->url 				= $this->url->link('amazon_map/map_product_data', 'token=' . $this->session->data['token'] . $url . '&page={page}', true);

			$data['pagination'] = $pagination->render();

			$data['results'] = sprintf($this->language->get('text_pagination'), ($amazonProductTotal) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($amazonProductTotal - $this->config->get('config_limit_admin'))) ? $amazonProductTotal : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $amazonProductTotal, ceil($amazonProductTotal / $this->config->get('config_limit_admin')));
	
			$this->template = 'amazon/amazon_map/map_product_data.expand';
			$this->children = array(
				'common/header',
				'common/footer'
			);
			$this->data=$data;
				
			$this->response->setOutput($this->render());
  }

	public function dtHandler()
	{
		$request = $_REQUEST;

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			parse_str(html_entity_decode($request['filter']), $filterData);
			$filterData = $filterData['filter'];
		}

		$filterData['search'] = null;
		if (isset($request['search']['value']) && strlen($request['search']['value']) > 0) {
			$filterData['search'] = $request['search']['value'];
		}

		$start = $request['start'];
		$length = $request['length'];

		$columns = array(
			0 => 'oc_product_id',
			1 => 'name',
			2 => 'category',
			3 => 'price',
			4 => 'quantity',
		);
		$orderColumn = $columns[$request['order'][0]['column']];

		$results = $this->_amazonMapProduct->getAllOcUnmappedProducts($start, $length, $filterData , $orderColumn);

		$this->load->model('catalog/product');
		$this->load->model('catalog/category');

		$records = $results['data'];
		
		foreach ($records as $key => $result) {
			// Categories
			if (isset($result['product_id'])) {
				$categories = $this->model_catalog_product->getProductCategories($result['product_id']);
			} else {
				$categories = array();
			}

			$product_categories = array();

			foreach ($categories as $category_id) {
				$category_info = $this->model_catalog_category->getCategory($category_id);

				if ($category_info) {
					$product_categories[] = $category_info['path'] ? $category_info['path'] . ' &gt; ' . $category_info['name'] : $category_info['name'];
				}
			}
			$product_categories = implode(",",$product_categories);
			$records[$key]['category'] = $product_categories;
			
		}

		$totalData = $results['total'];
		$totalFiltered = $results['totalFiltered'];

		$json_data = array(
			"draw" => intval($request['draw']),
			"recordsTotal" => intval($totalData),
			"recordsFiltered" => $totalFiltered,
			"data" => $records
		);
		$this->response->setOutput(json_encode($json_data));
		return;
	}
	public function add() {

		$this->load->language($this->map_product_route);
		$this->language->load('catalog/product');

		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {

			if(!$this->validateForm()){
				$result_json['success'] = '0';
				$result_json['errors'] = $this->error;
				$this->response->setOutput(json_encode($result_json));
				return;
			}

			$product_id =$this->request->post['product_id'];
			if($this->config->get('wk_amazon_connector_status')){
				$this->model_amazon_amazon_map_product->__saveOpencartProductData($product_id, $this->request->post, $type = 'add');
			}
			
			$result_json['success'] = '1';
			$result_json['redirect'] = '1';
			$result_json['to'] = (string) $this->url->link('amazon/amazon_map/map_product_data',true);
			$result_json['success_msg']  = $this->language->get('text_success');
			$this->response->setOutput(json_encode($result_json));
			return;

		}
			
        $this->getForm();
	}
	public function edit() {
		$this->load->language($this->map_product_route);
		$this->language->load('catalog/product');

		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			
			if(!$this->validateForm()){
				$result_json['success'] = '0';
				$result_json['errors'] = $this->error;
				$this->response->setOutput(json_encode($result_json));
				return;
			}

			$product_id =$this->request->post['product_id'];
			if($this->config->get('wk_amazon_connector_status')){
				$this->model_amazon_map_product->__saveOpencartProductData($product_id, $this->request->post, $type = 'edit');
			}
			
			$result_json['success'] = '1';
			$result_json['redirect'] = '1';
			$result_json['to'] = (string) $this->url->link('amazon/amazon_map/map_product_data',true);
			$result_json['success_msg']  = $this->language->get('text_success');
			$this->response->setOutput(json_encode($result_json));
			return;

		}
		$this->getForm();
	}
	public function getForm() {

        $this->load->language($this->map_product_route);
		$data                  = [];
		$data['product_id'] = $this->request->get['product_id'];
		
		$data['back'] 				 = $this->url->link('amazon_map/map_product_data', 'token=' . $this->session->data['token'], true);
		$data['token']		     = $this->session->data['token'];
		$this->load->language('amazon/amazon_map/map_product_data_form');
		$data['breadcrumbs']   = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
		);
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}


		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('amazon_map/map_product_data', 'token=' . $this->session->data['token'], true)
		);
		$data                  = array_merge($data, $this->load->language('amazon_map/map_product_data_form'));

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();
		if(isset($this->request->get['product_id'])) {
			$record = $this->_amazonMapProduct->getOcUnmappedProducts(array('filter_oc_prod_id'=>$this->request->get['product_id']));
			if(isset($record[0]['product_id'])) {
				$data['product_id']    = $this->request->get['product_id'];
				$data['product_name']  = $record[0]['name'];
			}
	     $data['action']        = $this->url->link('amazon/amazon_map/map_product_data/edit', 'token='. $this->session->data['token'], true);
		} else {
				$data['action']        = $this->url->link('amazon/amazon_map/map_product_data/add', 'token='. $this->session->data['token'], true);
				$data['add_action']   = 'add';
		}
		/**
		 * opencart Amazon connector code
		 */
			if($this->config->get('wk_amazon_connector_status')){
						if(isset($this->error['variation_error'])){
								$data['variation_error']	= $this->error['variation_error'];
						}else{
								$data['variation_error'] = array();
						}

						$data['amazon_connector_status']  				= true;
						$data['tab_amazon_authorization'] 				= $this->language->get('tab_amazon_authorization');
						$data['tab_amazon_specification'] 				= $this->language->get('tab_amazon_specification');
						$data['tab_amazon_variation']     				= $this->language->get('tab_amazon_variation');
						$data['entry_uin']     										= $this->language->get('entry_uin');
						$data['entry_in']     										= $this->language->get('entry_in');
						$data['entry_amazon_specification']     	= $this->language->get('entry_amazon_specification');
						$data['entry_amazon_specification_value'] = $this->language->get('entry_amazon_specification_value');
						$data['entry_combination_list'] 					= $this->language->get('entry_combination_list');
						$data['text_asin'] 												= $this->language->get('text_asin');
						$data['text_ean'] 												= $this->language->get('text_ean');
						$data['text_gtin'] 												= $this->language->get('text_gtin');
						$data['text_upc'] 												= $this->language->get('text_upc');
						$data['info_asin'] 												= $this->language->get('info_asin');
						$data['info_ean'] 												= $this->language->get('info_ean');
						$data['info_gtin'] 												= $this->language->get('info_gtin');
						$data['info_upc'] 												= $this->language->get('info_upc');
						$data['info_in'] 												  = $this->language->get('info_in');

						$data['getAmazonSpecification']   				= $this->Amazonconnector->_getAmazonSpecification();
						$data['getAmazonVariation']       				= $this->Amazonconnector->_getAmazonVariation();

						if (isset($this->request->get['product_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
								$data['getProductFields'] = $this->Amazonconnector->__getProductFields($this->request->get['product_id']);
						}else{
								$data['getProductFields'] = array();
						}
						if(isset($this->request->post['amazonProductType'])){
							$data['getProductFields']['main_product_type'] = $this->request->post['amazonProductType'];
						}
						if(isset($this->request->post['amazonProductTypeValue'])){
							$data['getProductFields']['main_product_type_value'] = $this->request->post['amazonProductTypeValue'];
						}

						//Amazon Specification
						$this->load->model('catalog/attribute');
						if (isset($this->request->post['amazon_product_specification'])) {
							$amazon_product_specification = $this->request->post['amazon_product_specification'];
						} elseif (isset($this->request->get['product_id'])) {
							$amazon_product_specification = $this->Amazonconnector->getProductSpecification($this->request->get['product_id']);
						} else {
							$amazon_product_specification = array();
						}

						$data['amazon_product_specifications'] = array();
						foreach ($amazon_product_specification as $key => $specification) {
							$specification_info = $this->model_catalog_attribute->getAttribute($specification['attribute_id']);
							if ($specification_info) {
								$data['amazon_product_specifications'][] = array(
									'attribute_id'                  => $specification['attribute_id'],
									'name'                          => $specification_info['name'],
									'product_attribute_description' => $specification['product_attribute_description']
								);
							}
						}

						//Amazon variation
						if(isset($this->request->post['amazon_product_variation'])){
								 $data['amazon_product_variation'] = $this->request->post['amazon_product_variation'];
						} elseif (isset($this->request->get['product_id'])) {
							$data['amazon_product_variation'] = $this->Amazonconnector->_getProductVariation($this->request->get['product_id'],'amazon_product_variation');
						} else {
								$data['amazon_product_variation'] = array();
						}

						if(isset($this->request->post['amazon_product_variation_value'])){
									$data['amazon_product_variation_value'] = $this->request->post['amazon_product_variation_value'];
						} elseif (isset($this->request->get['product_id'])) {
									$data['amazon_product_variation_value'] = $this->Amazonconnector->_getProductVariation($this->request->get['product_id'],'amazon_product_variation_value');
						} else {
									$data['amazon_product_variation_value'] = array();
						}
				}
		/**
		 * end here
		 */
		
		$data['attributes'] = $this->_amazonMapProduct->getAmazonAttributes();

		$this->template = 'amazon/amazon_map/map_product_data_form.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);
		$this->data=$data;
			
		$this->response->setOutput($this->render());
	}
	public function autocomplete(){
		$json = array();

		if (isset($this->request->get['filter_name'])) {
		 $json = $this->_amazonMapProduct->autocomplete($this->request->get);

	 }
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	public function autocompleteMapdata() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$filter_data = array(

				'filter_oc_prod_name' 			=> $this->request->get['filter_name'],
				'order'       => 'ASC',
				'start'       => 0,
				'limit'       => 10
			);
		 $json =  $this->_amazonMapProduct->getOcUnmappedProducts($filter_data);

	 }
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	protected function validateForm() {
		$this->load->language('amazon_map/map_product_data');
		if (!$this->user->hasPermission('modify', 'amazon_map/map_product_data')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		$data =  $this->_amazonMapProduct->checkAsin($this->request->post);

		if(isset($data['error'])) {
			$this->error['warning'] = $this->language->get('exist_asin').$data['product_id'];
		}
			if (isset($this->request->post['product_id']) && $this->request->post['product_id']=='') {
				$this->error['warning'] = $this->language->get('error_product_select');
			}

		return !$this->error;
	}
	public function autocompleteCategory() {
		$json = array();

			if(isset($this->request->get['category_name'])){
					$getFilter = '';

					if(isset($this->request->get['category_name'])){
						$getFilter = 'oc_category';
						$oc_category = $this->request->get['category_name'];
					}else{
						$oc_category = '';
					}

					$filter_data = array(

						'filter_oc_cat_name' 			=> $oc_category,
						'order'       => 'ASC',
						'start'       => 0,
						'limit'       => 5
					);

					$results = $this->_amazonMapProduct->getOcUnmappedProducts($filter_data);

					foreach ($results as $result) {
							if($getFilter == 'oc_product'){
									$json[$result['product_id']] = array(
										'item_id' 		=> $result['product_id'],
										'name'        => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
									);
							}else if($getFilter == 'oc_category'){
									$json[$result['category_id']] = array(
										'item_id' 		=> $result['category_id'],
										'name'        => strip_tags(html_entity_decode($result['category_name'], ENT_QUOTES, 'UTF-8'))
									);
							}
					}

				$sort_order = array();

				foreach ($json as $key => $value) {
					$sort_order[$key] = $value['name'];
				}

				array_multisort($sort_order, SORT_ASC, $json);

				$this->response->addHeader('Content-Type: application/json');
				$this->response->setOutput(json_encode($json));
			}
	}

	 public function csv() {
		 	
			$this->load->language('amazon/amazon_map/data_map_csv');
		
			$this->document->setTitle($this->language->get('heading_title'));
			if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateCsv($_FILES)) {

				if($this->config->get('wk_amazon_connector_status')){
					$report = $this->_amazonMapProduct->csvSave($this->_buildPriceRuleDataFromCsv($_FILES));
					$this->session->data['success'] = $this->language->get('text_success');
					$this->session->data['report'] = $report;
					$this->response->redirect($this->url->link('amazon/amazon_map/map_product_data/csv', '' , true));
				}
			}

			$data['breadcrumbs']   = array();

	 		$data['breadcrumbs'][] = array(
	 			'text' => $this->language->get('text_home'),
	 			'href' => $this->url->link('common/dashboard', 'token=' , true)
	 		);
	 		$data['breadcrumbs'][] = array(
	 			'text' => $this->language->get('heading_title_map'),
	 			'href' => $this->url->link('amazon/amazon_map/map_product_data', '' , true)
	 		);
			$data['breadcrumbs'][] = array(
	 			'text' => $this->language->get('heading_title'),
	 			'href' => $this->url->link('amazon/amazon_map/map_product_data/csv', '', true)
	 		);
	 		if (isset($this->error['warning'])) {
	 			$data['error_warning'] = $this->error['warning'];
	 		} else {
	 			$data['error_warning'] = '';
	 		}
			if(isset($this->error['error_import_product'])) {
				$data['error_import_product']  = $this->error['error_import_product'];
			}
			if (isset($this->session->data['report'])) {
	 			$data['report'] = $this->session->data['report'];

	 			unset($this->session->data['report']);
	 		}
	 		if (isset($this->session->data['success'])) {
	 			$data['success'] = $this->session->data['success'];

	 			unset($this->session->data['success']);
	 		} else {
	 			$data['success'] = '';
			 }
			$data['cancel']        =  $this->url->link('amazon/amazon_map/map_product_data', 'token=' . $this->session->data['token'], true);
			$data['action']        = $this->url->link('amazon/amazon_map/map_product_data/csv', 'token=' . $this->session->data['token'], true);
			$this->template = 'amazon/amazon_map/data_map_csv.expand';
			$this->children = array(
					'common/header',
					'common/footer'
			);
			$this->data=$data;
			$this->response->setOutput($this->render());
	 }
	 public function _buildPriceRuleDataFromCsv($files) {

	 	 $row 				= 1;
	   $csvData 		= array();
	 	 $csvKeys 		= array();
		 $csvValues   = array();

	  	 if (($csvFile = fopen($files['import_product']['tmp_name'], "r")) !== FALSE) {
	  			while (($data = fgetcsv($csvFile, 1000, ",")) !== FALSE) {

	  					$num = count($data);

	  					$csvData[] = $data;

	  					++$row;

	  			}
	  			fclose($csvFile);
	   }
	  	foreach ($csvData as $key => $value) {
	  		if($key == 0) {
	  			$csvKeys = $value;
	  		} else {
	  			foreach ($value as $keys => $value1) {
	  				@$csvValues[$key-1][$csvKeys[$keys]] =  $value1;
	  			}
	  		}
	  	}

	   $dataValues['csvkeys'] = $csvKeys;
	 	$dataValues['csvValues'] = $csvValues;

	  	return $dataValues;
	 }

	 public function validateCsv($files) {

			$this->load->language('amazon/amazon_map/data_map_csv');
			$array=  array (
			 'product_id',
			 'product_identification',
			 'product_identification_value'
			// 'specification_name',
			// 'specification_value'
			// 'variation_name',
			// 'variation_seller_sku',
			// 'variation_product_identification',
			// 'variation_product_identification_value',
			// 'variation_quantity',
			// 'variation_price',
			// 'variation_prefix'
				);

				if(isset($files['import_product']['name']) && !$files['import_product']['name']) {
					$this->error['error_import_product']	=	$this->language->get('error_empty_file');
					$this->error['warning']	=	$this->language->get('error_empty_file');
				}
				if(!isset($files['import_product'])) {
					$this->error['error_import_product']	=	$this->language->get('error_empty_file');
					$this->error['warning']	=	$this->language->get('error_empty_file');
				} else {
					if(!in_array($files['import_product']['type'],$this->csvMimes)){

					 $this->error['error_import_product']	=	$this->language->get('error_file_type');
					 $this->error['warning']	=	$this->language->get('error_file_type');
				 }
				}



			if(!isset($this->error['warning'])) {

				$data = $this->_buildPriceRuleDataFromCsv($files);
				 $key  = $data['csvkeys'];
				if($array === $key) {

				 } else {
					 $this->error['error_import_product']	=	$this->language->get('error_header_field');
					 $this->error['warning']	=	$this->language->get('error_header_field');
				 }
		 }
			 return !$this->error;
	 }
	 public function exportProduct() {
		$results = $this->_amazonMapProduct->getOcUnmappedProducts(array());
		foreach ($results as $result) {
			$record[] = array(
        'product_id'    		=> $result['product_id'],
				'product_name'   	  => html_entity_decode($result['name'], ENT_QUOTES),
				'meta_title' 				=>html_entity_decode($result['meta_title'], ENT_QUOTES),
				'product_price'			=> $result['price'],
				'product_quantity'  => $result['quantity'],
				'product_descripton'=>html_entity_decode($result['description'], ENT_QUOTES),
				'model' 						=> $result['model'],
				'sku'               => $result['model']


			);
		}
 		$fileName = 'export_product.csv';
 		    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
 		    header('Content-Description: File Transfer');
 		    header("Content-type: text/csv");
 		    header("Content-Disposition: attachment; filename={$fileName}");
 		    header("Expires: 0");
 		    header("Pragma: public");
 		    $fh1 = @fopen( 'php://output', 'w' );
 		    $headerDisplayed1 = false;
 		    foreach ( $record as $data1 ) {
 		        // Add a header row if it hasn't been added yet
 		        if ( !$headerDisplayed1 ) {
 		            // Use the keys from $data as the titles
 		            fputcsv($fh1, array_keys($data1));
 		            $headerDisplayed1 = true;
 		        }
 		        // Put the data into the stream
 		        fputcsv($fh1, $data1);
 		    }
 		// Close the file
 		    fclose($fh1);
			//	$this->response->redirect($this->url->link('amazon_map/map_product_data', 'token=' . $this->session->data['token'], true));
	 }

}
