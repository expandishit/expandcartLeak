<?php

class ControllerAmazonPriceRulesAmazonPriceRules extends Controller {
	private $error = array();

	private $csvMimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');

  private $sorting = array(
		'price_to',
		'ASC',
		 1
	);

  private $post_required_fields = array('price_from','price_to','price_value');

  private $post_optional_fields = array('price_type','price_opration','price_status','rule_type');

	private $sop_keys = array(
		'sort',
		'order',
		'page'
	);

  private $so_keys = array(
		'sort',
		'order',
	);

  static $rule_rgs;

	private $route = 'amazon/price_rule_amazon/price_rule';
	public function __construct($registory) {
		parent::__construct($registory);
		$this->load->model($this->route);
		$this->_amazonPriceRule = $this->model_amazon_price_rule_amazon_price_rule;
		$this->load->language('amazon/amazon_map/rules');
		$this->load->language('amazon/amazon_map/common');
  }

  public function index() {
    $this->getList();
	}

  public function getList(){

    $this->document->setTitle($this->language->get('heading_title'));


    $data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('amazon/price_rules_amazon/price_rules', '' , true)
		);

    if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}


		$data['add_rules'] = $this->url->link('amazon/price_rules_amazon/price_rules/add', '' , true);

		$data['add_csv'] = $this->url->link('amazon/price_rules_amazon/price_rules/csv', '' , true);

    
		$this->template = 'amazon/price_rules_amazon/rule_list.expand';
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
					0 => 'id',
					1 => 'rule_type',
					2 => 'price_from',
					3 => 'price_to',
					4 => 'price_value',
					5 => 'price_type',
					6 => 'price_opration',
					7 => 'price_status',
			);
			$orderColumn = $columns[$request['order'][0]['column']];
			
			$results = $this->_amazonPriceRule->getAllPriceRules($start, $length, $filterData , $orderColumn);			
			
			$records = $results['data'];

	    $records = $this->_buildRuleListFormat($url,$records);

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

	public function csv(){
	 $data = array();

	 $data = array_merge($data, $this->load->language('amazon_map/rules'));

	$this->document->setTitle($this->language->get('heading_title_csv'));
	$url = '';
	if(isset($this->request->post['rule_type'])) {
		$data['rule_type'] = $this->request->post['rule_type'];
	} else {
		$data['rule_type'] ='price';
	}

	 if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateCsv()) {

			 $files = $this->request->files;

			 $bulkData = $this->_buildPriceRuleDataFromCsv($files);

			 foreach ($bulkData['csvValues'] as $key => $value) {
           $value['rule_type'] = $this->request->post['rule_type'];
					 $this->_amazonPriceRule->addPriceRule($value);

					 $this->session->data['success'] = $this->language->get('text_success_add');

					 $url = '';

					 foreach ($this->sop_keys as $key => $sop_key) {
							 if (isset($this->request->get[$sop_key])) {
								  $url .= '&' .$sop_key. '=' . $this->request->get[$sop_key];
							 }
					 }
			 }

			 $this->response->redirect($this->url->link('amazon/price_rules_amazon/price_rules', '' , true));
	 }

	 $data['breadcrumbs'] = array();

	 $data['breadcrumbs'][] = array(
		 'text' => $this->language->get('text_home'),
		 'href' => $this->url->link('common/dashboard', '', true)
	 );

	 $data['breadcrumbs'][] = array(
		 'text' => $this->language->get('heading_title'),
		 'href' => $this->url->link('amazon/price_rules_amazon/price_rules', '' , true)
	 );

	 $data['breadcrumbs'][] = array(
		 'text' => $this->language->get('heading_title_csv'),
		 'href' => $this->url->link('amazon/price_rules_amazon/price_rules/csv', '' , true)
	 );

   if(isset($this->error['error_csv_file']) && $this->error['error_csv_file']) {
		 $data['error_csv_file'] = $this->error['error_csv_file'];
	 }

	 if(isset($this->error['warning']) && $this->error['warning']) {
		 $data['warning'] = $this->error['warning'];
	 }

	 $data['action'] = $this->url->link('amazon/price_rules_amazon/price_rules/csv', 'token=' . $this->session->data['token'] . $url, true);

	 $data['cancel'] = $this->url->link('amazon/price_rules_amazon/price_rules', 'token=' . $this->session->data['token'] . $url, true);


	 $this->template = 'amazon/price_rules_amazon/rule_csv.expand';
	 $this->children = array(
			 'common/header',
			 'common/footer'
	 );
	 $this->data=$data;
	 $this->response->setOutput($this->render());
}

public function _buildPriceRuleDataFromCsv($files) {

	 $row = 1;
 	 $csvData = array();
	 $csvKeys = array();
	 $csvValues = array();

 	 if (($csvFile = fopen($files['amazon_rule_csv']['tmp_name'], "r")) !== FALSE) {

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
 				$csvValues[$key-1][$csvKeys[$keys]] =  $value1;
 			}
 		}
 	}

  $dataValues['csvkeys'] = $csvKeys;
	$dataValues['csvValues'] = $csvValues;

 	return $dataValues;
}

public function _validatePriceRuleColumnNames($csvKeys) {

   $status = false;

	 $columns = array();

   $allCols = $this->_amazonPriceRule->getColumnNames();

   foreach ($allCols as $key => $value) {
		  if($value['COLUMN_NAME']!='rule_type') {
				 $columns[] = $value['COLUMN_NAME'];
			}
   }



	$main_array = array_chunk($columns,6);
	if($main_array[0] === $csvKeys) {
		 $status = true;
	 }

	 return $status;

}

public function _buildRuleListFormat($url,$results = array()){

    $rules = array();
    foreach ($results as $result) {

      $price_type = '- Decrement'; // Default value
			$price_opration = 'Fixed'; // Default value

      if($result['price_type']) {
        $price_type = '+ Increment';
      }
      if($result['price_opration']) {
        $price_opration = 'Percentage';
      }

      $rules[] = array(
        'id'              => $result['id'],
        'price_from'      => $result['price_from'],
				'rule_type'       => ucfirst($result['rule_type']),
        'price_to'        => $result['price_to'],
        'price_value'     => $result['price_value'],
        'price_type'      => $price_type,
        'price_opration'  => $price_opration,
        'price_status'     => $result['price_status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
      );
    }
    return $rules;
  }

  public function add(){

		$this->document->setTitle($this->language->get('heading_title_add'));

		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {

				if(!$this->validateRuleForm()){
					$result_json['success'] = '0';
					$result_json['errors'] = $this->error;
					$this->response->setOutput(json_encode($result_json));
					return;
				}

				$this->_amazonPriceRule->addPriceRule($this->request->post);

				$result_json['success'] = '1';
				$result_json['redirect'] = '1';
				$result_json['to'] = (string) $this->url->link('amazon/price_rules_amazon/price_rules',true);
				$result_json['success_msg']  = $this->language->get('text_success_add');
				$this->response->setOutput(json_encode($result_json));
				return;

		}

    $this->ruleForm();
  }

	public function edit(){

		$this->document->setTitle($this->language->get('heading_title_add'));

		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {

				if(!$this->validateRuleForm()){
					$result_json['success'] = '0';
					$result_json['errors'] = $this->error;
					$this->response->setOutput(json_encode($result_json));
					return;
				}

				$this->_amazonPriceRule->editPriceRule($this->request->post,$this->request->get['id']);

				$result_json['success'] = '1';
				$result_json['redirect'] = '1';
				$result_json['to'] = (string) $this->url->link('amazon/price_rules_amazon/price_rules',true);
				$result_json['success_msg']  = $this->language->get('text_success_add');
				$this->response->setOutput(json_encode($result_json));
				return;

		}

    $this->ruleForm();

	}

  public function ruleForm(){
     $data = array();

     $data = array_merge($data, $this->load->language('amazon_map/rules'));

     $url = '';

		 if(isset($this->request->get['id']) && $this->request->get['id']) {

			  $data['heading_title'] = $this->language->get('heading_title_edit');
			  $this->document->setTitle($this->language->get('heading_title_edit'));
		 } else {

			 $data['heading_title'] = $this->language->get('heading_title_add');
			 $this->document->setTitle($this->language->get('heading_title_add'));
		 }

     $data['breadcrumbs'] = array();

     $data['breadcrumbs'][] = array(
       'text' => $this->language->get('text_home'),
       'href' => $this->url->link('common/dashboard', '' , true)
     );

     $data['breadcrumbs'][] = array(
       'text' => $this->language->get('heading_title'),
       'href' => $this->url->link('amazon/price_rules_amazon/price_rules', '' , true)
     );

		 if(isset($this->request->get['id']) && $this->request->get['id']) {
				$data['breadcrumbs'][] = array(
	         'text' => $data['heading_title'],
	         'href' =>  $this->url->link('amazon/price_rules_amazon/price_rules/edit?id=' . $this->request->get['id'], '', true)
	      );
		 } else {

			 $data['breadcrumbs'][] = array(
	        'text' => $data['heading_title'],
	        'href' => $this->url->link('amazon/price_rules_amazon/price_rules/add', '' .  $this->session->data['token'] . $url, true)
	     );
		 }

     if (isset($this->error['warning'])) {
 			 $data['error_warning'] = $this->error['warning'];
 		 } else {
 			 $data['error_warning'] = '';
 		 }

     foreach ($this->post_required_fields as $key => $err_value) {
   		 if (isset($this->error['err_'.$err_value])) {

   			 $data['err_'.$err_value] = $this->error['err_'.$err_value];
   		 } else {

   			 $data['err_'.$err_value] = '';
   		 }
     }

		 if(isset($this->error['err_wide_range']) && $this->error['err_wide_range']) {
			 $data['err_price_to'] = $data['err_price_from'] = $this->error['err_wide_range'] ;
		 }

     $price_rule = array();

     if(isset($this->request->get['id']) && $this->request->get['id']) {
			 $price_rule =  $this->_amazonPriceRule->getPriceRule($this->request->get['id']);
		 }

     foreach ($this->post_required_fields as $key => $post_field) {
       if(isset($this->request->post[$post_field]) && $this->request->post[$post_field]) {
         $data[$post_field] = $this->request->post[$post_field];
       } else if(isset($price_rule[$post_field]) && $price_rule[$post_field]) {
         $data[$post_field] = $price_rule[$post_field];
       } else {
         $data[$post_field] = '';
       }
     }

     foreach ($this->post_optional_fields as $key => $post_field) {
       if( isset($this->request->post[$post_field]) &&  $this->request->post[$post_field]) {
         $data[$post_field] = $this->request->post[$post_field];
       } else if(isset($price_rule[$post_field])) {
         $data[$post_field] = $price_rule[$post_field];
       } else{
         $data[$post_field] = 0;
       }
     }

		 if(isset($this->request->get['id']) && $this->request->get['id']) {
			   $data['action'] = $this->url->link('amazon/price_rules_amazon/price_rules/edit?id=' . $this->request->get['id'], '' , true);
		 } else {
			  $data['action'] = $this->url->link('amazon/price_rules_amazon/price_rules/add', '' , true);
		 }

     $data['cancel'] = $this->url->link('amazon/price_rules_amazon/price_rules', '' , true);
		
		 $this->template = 'amazon/price_rules_amazon/rules_form.expand';
		 $this->children = array(
		 'common/header',
		 'common/footer'
		 );
		 $this->data=$data;
		 $this->response->setOutput($this->render());
	}

	 public function delete() {
		
				
		if ( isset($this->request->post['selected'])) {

			if(!$this->validate()){
				$result_json['success'] = '0';
				$result_json['errors'] = $this->error;
				$this->response->setOutput(json_encode($result_json));
				return;	
			}
			foreach ($this->request->post['selected'] as $rule_id) {
				$this->_amazonPriceRule->deleteRule($rule_id);
			}

			$result_json['success'] = '1';
			$result_json['success_msg']  = $this->language->get('text_success_del');
			$this->response->setOutput(json_encode($result_json));
      return;
		}
 	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'price_rules_amazon/price_rules')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	protected function validateCsv() {
		if (!$this->user->hasPermission('modify', 'price_rules_amazon/price_rules')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
      $files = $this->request->files;
		if(!isset($this->request->post['rule_type'])) {
       $this->error['warning']	=	$this->language->get('error_empty_file');
		} else {
			$rule_rgs = $this->_amazonPriceRule->getPriceRulesRanges($this->request->post['rule_type']);



			if(isset($files['amazon_rule_csv']['name']) && !$files['amazon_rule_csv']['name']) {
				$this->error['error_csv_file']	=	$this->language->get('error_empty_file');
				$this->error['warning']	=	$this->language->get('error_empty_file');
			} else if(!in_array($files['amazon_rule_csv']['type'],$this->csvMimes)){
				$this->error['error_csv_file']	=	$this->language->get('error_file_type');
				$this->error['warning']	=	$this->language->get('error_file_type');
			} else {

				 $bulkData = $this->_buildPriceRuleDataFromCsv($files);

				 if($this->_validatePriceRuleColumnNames($bulkData['csvkeys'])){
						foreach ($bulkData['csvValues'] as $key => $value) {
								if(!$this->error) {
									 if(isset($value['price_from']) && $value['price_from'] && isset($value['price_to']) && $value['price_to'] && ($value['price_to'] == $value['price_from']) && ($value['price_to'] > $value['price_from'])) {
												$this->error['error_csv_file']	=	$this->language->get('error_same_value');
												$this->error['warning']	=	$this->language->get('error_same_value');
										 } else {
												foreach ($value as $key => $post_value) {
													if(in_array($key,$this->post_required_fields)) {
															if(isset($post_value) && !$post_value){
																$this->error['error_csv_file']	=	$this->language->get('error_non_zero');
																$this->error['warning']	=	$this->language->get('error_non_zero');
															}
													}
													if(!isset($post_value) && !$this->error){
														$this->error['error_csv_file']	=	$this->language->get('error_field_required');
														$this->error['warning']	=	$this->language->get('error_field_required');
													} else if(!empty($post_value) && !is_numeric($post_value) && !$this->error) {
														$this->error['error_csv_file']	=	$this->language->get('error_numeric');
														$this->error['warning']	=	$this->language->get('error_field_required');
													}
												}
											}
									 if(!$this->error) {
										 $rule_rg = array();
										 $check_ranges['min'] = $value['price_from'];
										 $check_ranges['max'] = $value['price_to'];
										 if(!empty($rule_rgs)) {
											 $this->validatePriceValuesRanges($check_ranges,$rule_rgs);
										 }
										 array_push($rule_rgs,$check_ranges);
									 }
								}
						}
				 } else {
					 $this->error['error_csv_file']	=	$this->language->get('error_csv_keys');
					 $this->error['warning']	=	$this->language->get('error_csv_keys');
				 }
			}
		}

    if (isset($this->error['err_price_from']) && !isset($this->error['error_csv_file'])) {

			$this->error['error_csv_file']	=	$this->error['err_price_from'];

		}

		if (isset($this->error['err_price_to']) && !isset($this->error['error_csv_file'])) {

			$this->error['error_csv_file']	=	$this->error['err_price_to'];

		}

		if (isset($this->error['err_wide_range']) && !isset($this->error['error_csv_file'])) {

			$this->error['error_csv_file']	=	$this->error['err_wide_range'];

		}
		return !$this->error;
	}


	public function validatePriceValuesRanges($value,$rule_ranges) {

    foreach ($rule_ranges as $key => $rule_range) {

			if (isset($rule_range['min']) && isset($rule_range['max'])) {

				if (isset($value['min']) && $value['min']) {

           $this->_validateRuleRange('price_from',$value['min'], $rule_range['min'], $rule_range['max']);

				}
				if (isset($value['max']) && $value['max']){

           $this->_validateRuleRange('price_to',$value['max'], $rule_range['min'], $rule_range['max']);

				}

				if (isset($value['max']) && $value['max'] && isset($value['min']) && $value['min']) {

					$this->_validateRuleRange('wide_range',$rule_range['min'], $value['min'], $value['max']);

					$this->_validateRuleRange('wide_range',$rule_range['max'], $value['min'], $value['max']);
				}
			}
    }
	}

  public function validateRuleForm(){
    if (!$this->user->hasPermission('modify', 'price_rules_amazon/price_rules')) {

			$this->error['warning'] = $this->language->get('error_permission');
		}
		foreach ($this->post_required_fields as $key => $post_value) {

			if(empty($this->request->post[$post_value])){

				$this->error['err_'.$post_value]	=	$this->language->get('error_'.$post_value);

        $this->error['warning']	=	$this->language->get('error_field_required');

			} else if(!empty($this->request->post[$post_value]) && !is_numeric($this->request->post[$post_value])) {

				$this->error['err_'.$post_value]	=	$this->language->get('error_numeric');

        $this->error['warning']	=	$this->language->get('error_field_required');

			} else if(isset($this->request->post[$post_value]) && !$this->request->post[$post_value]) {

				$this->error['err_'.$post_value]	=	$this->language->get('error_zero');

        $this->error['warning']	=	$this->language->get('error_field_required');
			}
		}
    if (!$this->error) {

      if(!($this->request->post['price_from'] < $this->request->post['price_to'])) {

				$this->error['err_price_from']	=	$this->language->get('error_equal');

				$this->error['err_price_to']	=	$this->language->get('error_equal');

        $this->error['warning']	=	$this->language->get('error_field_required');
			}
		}

    if(!$this->error){

     if(isset($this->request->get['id']) && $this->request->get['id']){
			 $rule_ranges = $this->_amazonPriceRule->getExcludedPriceRulesRanges($this->request->get['id'],$this->request->post['rule_type']);
		 } else {
			 $rule_ranges = $this->_amazonPriceRule->getPriceRulesRanges($this->request->post['rule_type']);
		 }

	    foreach ($rule_ranges as $key => $rule_range) {

				if(isset($rule_range['min']) && isset($rule_range['max'])){

					if(isset($this->request->post['price_from']) && $this->request->post['price_from']){

	           $this->_validateRuleRange('price_from',$this->request->post['price_from'], $rule_range['min'], $rule_range['max']);

					}
					if(isset($this->request->post['price_to']) && $this->request->post['price_to']){

	           $this->_validateRuleRange('price_to',$this->request->post['price_to'], $rule_range['min'], $rule_range['max']);

					}
					if(isset($this->request->post['price_to']) && $this->request->post['price_to'] && isset($this->request->post['price_from']) && $this->request->post['price_from']){

						$this->_validateRuleRange('wide_range',$rule_range['min'], $this->request->post['price_from'], $this->request->post['price_to']);

						$this->_validateRuleRange('wide_range',$rule_range['max'], $this->request->post['price_from'], $this->request->post['price_to']);

					}
				}
	    }
	  }
    return !$this->error;
  }

	public function _validateRuleRange($for ,$price, $min, $max){

		if (filter_var($price, FILTER_VALIDATE_INT, array("options" => array("min_range"=>$min, "max_range"=>$max)))) {

			$this->error['err_'.$for]	=	$this->language->get('error_range_'.$for);

			$this->error['warning']	=	$this->language->get('error_field_required');
		}

	}

}
