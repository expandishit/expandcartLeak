<?php 
include DIR_SYSTEM.'library/PHPExcel.php';

class importProduct
{
    private $model_tool_product_import;
    private $model_catalog_manufacturer;
    private $errors;
    private $error;
    private $products_limit;
    private $plan_id=PRODUCTID;

    function __construct($registry)	{
		$this->registry 				= $registry;
        $this->config   				= $registry->get('config');
        $this->db       				= $registry->get('db');
        $this->cache       				= $registry->get('cache');
        $this->language       	= $registry->get('language');
        $this->genericConstants       	=  json_decode(file_get_contents('json/generic_constants.json'),true);
        // to fix old stores limits
        if (PRODUCTID==3 || PRODUCTID==53){
            $this->genericConstants ['plans_limits'][PRODUCTID]['products_limit']=PRODUCTSLIMIT;
        }
        $this->language->load('tool/product_import');

        $admin_app_dir = str_replace( 'system/', 'admin/', DIR_SYSTEM );
        require_once $admin_app_dir."model/tool/product_import.php";
        $this->model_tool_product_import = new ModelToolProductImport( $registry );
	
        require_once $admin_app_dir."model/catalog/manufacturer.php";
        $this->model_catalog_manufacturer = new ModelCatalogManufacturer( $registry );

        require_once $admin_app_dir."model/catalog/product.php";
        $this->model_catalog_product = new ModelCatalogProduct( $registry );

        require_once $admin_app_dir."model/localisation/language.php";
        $this->model_localisation_language = new ModelLocalisationLanguage( $registry );

        require_once $admin_app_dir."model/plan/trial.php";
        $this->model_plan_trial = new ModelPlanTrial( $registry );

        $trial = $this->model_plan_trial->getLastTrial();
        if ($trial){
            $this->plan_id = $trial['plan_id'];
        }


        $this->products_limit =  $this->genericConstants["plans_limits"][$this->plan_id]['products_limit'];
    }

    public function index($inputFileName,$file_id,$language_id,$store_id,$option_format, $seller_id = null){

        $current_product_count = $this->model_tool_product_import->getTotalProductsCount();

        $limit_diff = $this->products_limit - $current_product_count;
        $extension = pathinfo($inputFileName);

		$file_extension = $extension['extension'];
        try
        {
            if ( $file_extension == 'csv' )
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

        // Add all languages
        $languages = array();
        $i=0;
        $updateproduct = 0;
        $newproduct = 0;

        $import_product_file_mapping_json = $this->model_tool_product_import->checkImportProcessStatus();
        
        // $product_model = "";
        $language_pointer = -1;

        if($import_product_file_mapping_json['file_mapping'] == "default"){
            if($language_id == "all"){
                $j=0;
                foreach($allDataInSheet as $value){     
                    if($j++!=0 && !empty($value['B'])){
                        $languages[]=$value['B'];
                    }
                }
            }
            foreach($allDataInSheet as $k=> $value){
                if($i!=0){
                    if($value['D']){
                        //Tax Class
                        if(empty($value['U'])){
                            $value['U'] = $this->model_tool_product_import->CheckTaxClass($value['V']);
                        }
                        
                        //Stock Status
                        if(empty($value['Z'])){
                            $value['Z'] = $this->model_tool_product_import->CheckStockStatus($value['AA']);
                        }
                        
                        if(!empty($value['AC'])){
                            $value['AC'] = str_replace(' ','_',$value['AC']);
                        }
                        
                        //manufacture
                        $manufacturer_info = array();
                        if(!empty($value['AN'])){
                            $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($value['AN']);
                        }
                        if(!$manufacturer_info){
                            if(!empty($value['AP'])){
                            $value['AO'] = $this->model_tool_product_import->CheckManufacturer($value['AP'],$store_id);
                            }
                        }
                    
                        //Categories
                        $categoryids=array();
                        if(!empty($value['AQ'])){
                        foreach(explode(',',trim($value['AQ'])) as $category_id){
                            $cquery = $this->db->query("SELECT category_id FROM ".DB_PREFIX."category WHERE category_id = '".(int)$category_id."'");
                            if(!empty($cquery->row['category_id'])){
                            $categoryids[]=$cquery->row['category_id'];
                            }
                        }
                        }
                        
                        $categoryidsx=array();
                        if(!empty($value['AR'])){
                            $categoryidsx = $this->model_tool_product_import->CheckCategories($value['AR'],$store_id);
                            foreach($categoryidsx as $cate_id){
                                $categoryids[] = $cate_id;
                            }
                        }
                        
                        //Filter
                        $filters=array();
                        if(!empty($value['AS'])){
                            $filters = $this->model_tool_product_import->checkFilter($value['AS']);
                        }
                        
                        //Download
                        $downloads = array();
                        if(!empty($value['AT'])){
                            $downloads = explode(',',trim($value['AT']));
                        }
                        
                        //Relaled Products
                        $relaled_products = array();
                        if(!empty($value['AU'])){
                            $relaled_products = explode(',',trim($value['AU']));
                        }
                        
                        //Attribute Group
                        $attributes = array();
                        if(!empty($value['AV'])){
                            $attributes = $this->model_tool_product_import->checkAttribute($value['AV']);
                        }
                        
                        //Options
                        $options = array();
                        if(!empty($value['AW'])){
                            $options = $this->model_tool_product_import->checkoptions($value['AW']);
                        }
                        
                        //Discount
                        $discounts = array();
                        if(!empty($value['AY'])){
                            $discounts = $this->model_tool_product_import->checkdiscount($value['AY']);
                        }
                        
                        //Specail
                        $specails = array();
                        if(!empty($value['AZ'])){
                            $specails = $this->model_tool_product_import->checkspecial($value['AZ']);
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
                        'barcode' 			=> $value['L'],
                        'sku' 				=> $value['M'],
                        'upc' 				=> $value['N'],
                        'ean' 				=> $value['O'],
                        'jan' 				=> $value['P'],
                        'isbn' 				=> $value['Q'],
                        'mpn' 				=> $value['R'],
                        'location' 			=> $value['S'],
                        'price' 				=> $value['T'],
                        'tax_class_id' 		=> $value['U'],
                        'quantity' 			=> $value['W'],
                        'minimum' 			=> $value['X'],
                        'subtract' 			=> $value['Y'],
                        'stock_status_id' 	=> $value['Z'],
                        'shipping' 			=> $value['AB'],
                        'keyword' 			=> $value['AC'],
                        'date_available' 		=> ($value['AD'] ? $value['AD'] : date('Y-m-d')),
                        'length' 				=> $value['AE'],
                        'length_class_id' 	=> $value['AF'],
                        'width' 				=> $value['AH'],
                        'height' 				=> $value['AI'],
                        'weight' 				=> $value['AJ'],
                        'weight_class_id' 	=> $value['AK'],
                        'status' 				=> $value['AM'],
                        'sort_order' 			=> $value['AN'],
                        'manufacturer_id' 	=> $value['AO'],
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
                        'points' 				=> $value['BB'],
                        'viewed' 				=> $value['BC'],
                        );
                        //$this->request->post['importtype']
                        if(count($languages) > 0){
                            // if($product_model != "" && $product_model == $value['E']){
                            //     $language_pointer++;
                            // }
                            // else{
                            //     $product_model = $value['E'];
                            //     $language_pointer = 0;
                            // }
                            $language_pointer++;
                            if($language_pointer >= count($languages))
                                $language_pointer = 0;

                            $language_code = $languages[$language_pointer];
                            $language_id = $this->model_localisation_language->getLanguageByCode($language_code)['language_id'];
                        }
                        // Model is the identity field if imported product file is in multiple languages
                        if($value['E'] != ""){
                            $product_id = $this->model_tool_product_import->getproductIDbymodel($value['E']);

                            try{
                                if($product_id){
                                    $this->model_tool_product_import->Editproduct($importdata,$product_id,$language_id,$store_id, $seller_id);
                                    $updateproduct++;
                                }else{
                                    // ex: if the allowed number is 3 and products number is 2
                                    $addData = [
                                        'limit_diff' => $limit_diff,
                                        'newproduct' => $newproduct,
                                        'importdata' => $importdata,
                                        'language_id' => $language_id,
                                        'store_id' => $store_id,
                                        'seller_id' => $seller_id,
                                        'method' => 'addproduct'
                                    ];
                                    $this->addProductAction($addData);
                                    $newproduct++;

//                                    if ( $limit_diff >= ($newproduct+1) ){
//                                        $this->model_tool_product_import->addproduct($importdata,$language_id,$store_id, $seller_id);
//                                        $newproduct++;
//                                    }else{
//                                        $this->errors[] = "Limit is reached";
//                                    }
                                }
                            }
                            catch(Exception $e){
                                $this->errors[] = "There is a problem in importing/updating product: ".$value['D'];
                            }
                            
                        }
                        else if((int)$value['A']){
                            $product_info = $this->model_catalog_product->getproduct($value['A']);
                            try{             
                                if($product_info){
                                    $this->model_tool_product_import->Editproduct($importdata,$value['A'],$language_id,$store_id, $seller_id);
                                    $updateproduct++;
                                }else{
                                    if(count($languages) <= 1 ){
                                        $language_data = 'all';
                                    } else {
                                        $language_data = '';
                                    }

                                    $addData = [
                                        'limit_diff' => $limit_diff,
                                        'newproduct' => $newproduct,
                                        'importdata' => $importdata,
                                        'language_id' => $language_id,
                                        'store_id' => $store_id,
                                        'language_data' => $language_data,
                                        'seller_id' => $seller_id,
                                        'mapping' => $value['A'],
                                        'method' => 'addoldproduct'
                                    ];
                                    $this->addProductAction($addData);
                                    $newproduct++;

//                                    if ( $limit_diff >= ($newproduct+1) ) {
//                                        $this->model_tool_product_import->addoldproduct($importdata, $language_id, $language_data, $store_id, $value['A'], $seller_id);
//                                        $newproduct++;
//                                    }
//                                    else{
//                                        $this->errors[] = "Limit is reached";
//                                    }
                                }
                            }
                            catch(Exception $e){
                                $this->errors[] = "There is a problem in importing/updating product: ".$value['D'];
                            }
                        }
                        else{
                            try{
                                $addData = [
                                    'limit_diff' => $limit_diff,
                                    'newproduct' => $newproduct,
                                    'importdata' => $importdata,
                                    'language_id' => $language_id,
                                    'store_id' => $store_id,
                                    'seller_id' => $seller_id,
                                    'method' => 'addproduct'
                                ];
                                $this->addProductAction($addData);
                                $newproduct++;

//                                if ( $limit_diff >= ($newproduct+1) ) {
//                                    $this->model_tool_product_import->addproduct($importdata,$language_id,$store_id, $seller_id);
//                                    $newproduct++;
//                                }else{
//                                    $this->errors[] = "Limit is reached";
//                                }
                            }
                            catch(Exception $e){
                                $this->errors[] = "There is a problem in importing/updating product: ".$value['D'];
                            }
                        }
                       
                    }
                }
                $i++;
            }
        }
        else{
            $import_product_file_mapping = json_decode($import_product_file_mapping_json['file_mapping'],true);

            if($language_id == "all"){
                $j=0;
                foreach($allDataInSheet as $value){    
                    if($j++!=0 && !empty($value[$import_product_file_mapping['B']])){
                        $languages[]=$value[$import_product_file_mapping['B']];
                    }
                }
                $languages = array_unique($languages);
            }

            $options_counter = 1;
            foreach($allDataInSheet as $k=> $value){
                if($value[$import_product_file_mapping['D']] == ""
                    && $value[$import_product_file_mapping['E']] != "")
                {
                    ++$i;
                    continue;
                }
                if($i!=0){
                    
                    if($import_product_file_mapping['D'] != '0' && $value[$import_product_file_mapping['D']]){
                        //Tax Class
                        if($import_product_file_mapping['U'] != '0' && empty($value[$import_product_file_mapping['U']])){
                            $value[$import_product_file_mapping['U']] = $this->model_tool_product_import->CheckTaxClass($value[$import_product_file_mapping['V']]);
                        }
                        
                        //Stock Status
                        if($import_product_file_mapping['Z'] != '0' && empty($value[$import_product_file_mapping['Z']])){
                            $value[$import_product_file_mapping['Z']] = $this->model_tool_product_import->CheckStockStatus($value[$import_product_file_mapping['AA']]);
                        }
                        
                        if($import_product_file_mapping['AC'] != '0' && !empty($value[$import_product_file_mapping['AC']])){
                            $value[$import_product_file_mapping['AC']] = str_replace(' ','_',$value[$import_product_file_mapping['AC']]);
                        }
                        
                        //manufacture
                        $manufacturer_info = array();
                        if($import_product_file_mapping['AO'] != '0' && !empty($value[$import_product_file_mapping['AO']])){
                            $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($value[$import_product_file_mapping['AO']]);
                        }
                        if(!$manufacturer_info){
                            if($import_product_file_mapping['AP'] != '0' && !empty($value[$import_product_file_mapping['AP']])){
                                $value[$import_product_file_mapping['AO']] = $this->model_tool_product_import->CheckManufacturer($value[$import_product_file_mapping['AP']],$store_id);
                            }
                        }
                    
                        //Categories
                        $categoryids=array();
                        if($import_product_file_mapping['AQ'] != '0' && !empty($value[$import_product_file_mapping['AQ']])){
                            foreach(explode(',',trim($value[$import_product_file_mapping['AQ']])) as $category_id){
                                $cquery = $this->db->query("SELECT category_id FROM ".DB_PREFIX."category WHERE category_id = '".(int)$category_id."'");
                                if(!empty($cquery->row['category_id'])){
                                $categoryids[]=$cquery->row['category_id'];
                                }
                            }
                        }
                        
                        $categoryidsx=array();
                        if($import_product_file_mapping['AR'] != '0' && !empty($value[$import_product_file_mapping['AR']])){
                            $categoryidsx = $this->model_tool_product_import->CheckCategories($value[$import_product_file_mapping['AR']],$store_id);
                            foreach($categoryidsx as $cate_id){
                                $categoryids[] = $cate_id;
                            }
                        }
                        
                        //Filter
                        $filters=array();
                        if($import_product_file_mapping['AS'] != '0' && !empty($value[$import_product_file_mapping['AS']])){
                            $filters = $this->model_tool_product_import->checkFilter($value[$import_product_file_mapping['AS']]);
                        }
                        
                        //Download
                        $downloads = array();
                        if($import_product_file_mapping['AT'] != '0' && !empty($value[$import_product_file_mapping['AT']])){
                            $downloads = explode(',',trim($value[$import_product_file_mapping['AT']]));
                        }
                        
                        //Relaled Products
                        $relaled_products = array();
                        if($import_product_file_mapping['AU'] != '0' && !empty($value[$import_product_file_mapping['AU']])){
                            $relaled_products = explode(',',trim($value[$import_product_file_mapping['AU']]));
                        }
                        
                        //Attribute Group
                        $attributes = array();
                        if($import_product_file_mapping['AV'] != '0' && !empty($value[$import_product_file_mapping['AV']])){
                            $attributes = $this->model_tool_product_import->checkAttribute($value[$import_product_file_mapping['AV']]);
                        }
                        
                        //Options
                        $options = array();
                        $discounts_column = '';
                        $special_column = '';
                        $discount_column = '';
                        $sub_images_column = '';
                        $unlimited_column = '';
                        $sku_column = '';
                        switch ($option_format) {
                            case '0':
                                $discounts_column = 'AW';
                                $special_column = 'AX';
                                $sub_images_column = 'AY';
                                $reward_column = 'AZ';
                                $viewed_column = 'BA';
                                $slug_column = 'BB';
                                $maximum_column = 'BC';
                                $cost_price_column = 'BD';
                                $unlimited_column = 'BE';
                                $sku_column = 'BF';
                                break;
                            case '1':
                                if($import_product_file_mapping['AW'] != '0' && !empty($value[$import_product_file_mapping['AW']])){
                                    if(count($languages) > 0){
                                        $lp = $language_pointer < 0 ? 0 : $language_pointer;
                                        $language_code = $languages[$lp];
                                        $language_id = $this->model_localisation_language->getLanguageByCode($language_code)['language_id'];    
                                    }
                                    $options = $this->model_tool_product_import->checkSimpleOptionsFormat($value[$import_product_file_mapping['AW']],$language_id);
                                }
                                $discounts_column = 'AX';
                                $special_column = 'AY';
                                $sub_images_column = 'AZ';
                                $reward_column = 'BA';
                                $viewed_column = 'BB';
                                $slug_column = 'BC';
                                $maximum_column = 'BD';
                                $cost_price_column = 'BE';
                                $unlimited_column = 'BF';
                                $sku_column = 'BG';
                                break;
                            case '2':
                                // The last step
                                if(count($languages) > 0){
                                    // if($product_model != "" && $product_model == $value[$import_product_file_mapping['E']]){
                                    //     $language_pointer++;
                                    // }
                                    // else{
                                    //     $product_model = $value[$import_product_file_mapping['E']];
                                    //     $language_pointer = 0;
                                    // }
                                    $language_pointer++;
                                    if($language_pointer >= count($languages))
                                        $language_pointer = 0;
                                    $language_code = $languages[$language_pointer];
                                    $language_id = $this->model_localisation_language->getLanguageByCode($language_code)['language_id'];
                                }
                                if($import_product_file_mapping['AW'] != '0'
                                    && $import_product_file_mapping['AX'] != '0'
                                    && !empty($value[$import_product_file_mapping['AW']])
                                    && !empty($value[$import_product_file_mapping['AX']])
                                ){
                                   $options[] = $this->model_tool_product_import->checkAdvancedOptionsFormat(
                                       $value[$import_product_file_mapping['AW']],
                                       $value[$import_product_file_mapping['AX']],
                                       $language_id);
                                    
                                    for ($l=$i+2;; $l++){ 
                                        # code...
                                        if($allDataInSheet[$l][$import_product_file_mapping['AW']] != ""
                                            && $allDataInSheet[$l][$import_product_file_mapping['AX']] != ""
                                            && $allDataInSheet[$l][$import_product_file_mapping['D']] == ""
                                            && $allDataInSheet[$l][$import_product_file_mapping['E']] == $value[$import_product_file_mapping['E']])
                                        {
                                            $options[] = $this->model_tool_product_import->checkAdvancedOptionsFormat(
                                                $allDataInSheet[$l][$import_product_file_mapping['AW']],
                                                $allDataInSheet[$l][$import_product_file_mapping['AX']],
                                                $language_id);
                                        }
                                        else{
                                            break;
                                        }
                                    }
                                    
                                }
                                $import_advanced_options = true;
                                $discounts_column = 'AY';
                                $special_column = 'AZ';
                                $sub_images_column = 'BA';
                                $reward_column = 'BB';
                                $viewed_column = 'BC';
                                $slug_column = 'BD';
                                $maximum_column = 'BE';
                                $cost_price_column = 'BF';
                                $unlimited_column = 'BG';
                                $sku_column = 'BH';

                                break;
                        }
                        
                        //Discount
                        $discounts = array();
                        if($import_product_file_mapping[$discounts_column] != '0' && !empty($value[$import_product_file_mapping[$discounts_column]])){
                            $discounts = $this->model_tool_product_import->checkdiscount($value[$import_product_file_mapping[$discounts_column]]);
                        }
                        
                        //Specail
                        $specails = array();
                        if($import_product_file_mapping[$special_column] != '0' && !empty($value[$import_product_file_mapping[$special_column]])){
                            $specails = $this->model_tool_product_import->checkspecial($value[$import_product_file_mapping[$special_column]]);
                        }

                        // SKU App
                        $productsoptions_sku_status = $this->config->get('productsoptions_sku_status');
                        if ($productsoptions_sku_status){
                            $product_variation_sku = array();
                            if($import_product_file_mapping[$sku_column] != '0' && !empty($value[$import_product_file_mapping[$sku_column]])){
                                $product_variation_sku = $this->model_tool_product_import->checkProductVariationSku($value[$import_product_file_mapping[$sku_column]]);
                            }
                        }

                        //Image
                        $imagen = str_replace(' ','_',$value[$import_product_file_mapping['D']]);
                        $imagen = $imagen.time().rand(1,10000000);
                        $mainimage = $value[$import_product_file_mapping['K']];
                        if($import_product_file_mapping['K'] != '0' && !empty($value[$import_product_file_mapping['K']])){
                            $value[$import_product_file_mapping['K']] = str_replace('?dl=0','?raw=1',$value[$import_product_file_mapping['K']]);
                            $mainimage = $this->model_tool_product_import->fetchingimage($value[$import_product_file_mapping['K']],$imagen);	
                        }
                        
                        //Image
                        $images = array();
                        if($import_product_file_mapping[$sub_images_column] != '0' && !empty($value[$import_product_file_mapping[$sub_images_column]])){
                            $ic=1;
                            foreach(explode(';',trim($value[$import_product_file_mapping[$sub_images_column]])) as $imageurl){
                                $imageurl = str_replace('?dl=0','?raw=1',$imageurl);
                                $imagename = $imagen.$ic++;
                                $images[] = $this->model_tool_product_import->fetchingimage($imageurl,$imagename);
                            }
                        }
                        
                        //Options Required
                        // Deprecated
                        /*
                        $optionsrequired = array();
                        if($import_product_file_mapping['AW'] != '0' && !empty($value[$import_product_file_mapping['AW']])){
                            $optionsrequired = $this->model_tool_product_import->checkoptionsrequred($value[$import_product_file_mapping['AW']]);
                        }
                        */
                        if($option_format != 2 || ($option_format == 2 && ($i == 1 || $import_advanced_options))){
                            $importdata=array(
                                'name' 	 			=> $import_product_file_mapping['D'] != '0'?$value[$import_product_file_mapping['D']]:'',
                                'model'  	 		=> $import_product_file_mapping['E'] != '0'?$value[$import_product_file_mapping['E']]:'',
                                'description' 		=> $import_product_file_mapping['F'] != '0'?$value[$import_product_file_mapping['F']]:'',
                                'meta_titile' 		=> $import_product_file_mapping['G'] != '0' ?$value[$import_product_file_mapping['G']]:'',
                                'meta_description' 	=> $import_product_file_mapping['H'] != '0' ?$value[$import_product_file_mapping['H']]:'',
                                'meta_keyword' 		=> $import_product_file_mapping['I'] != '0' ?$value[$import_product_file_mapping['I']]:'',
                                'tag' 				=> $import_product_file_mapping['J'] != '0' ?$value[$import_product_file_mapping['J']]:'',
                                'image' 				=> $mainimage,
                                'barcode' 				=> $import_product_file_mapping['L'] != '0'?$value[$import_product_file_mapping['L']]:'',
                                'sku' 				=> $import_product_file_mapping['M'] != '0'?$value[$import_product_file_mapping['M']]:'',
                                'upc' 				=> $import_product_file_mapping['N'] != '0'? $value[$import_product_file_mapping['N']]:'',
                                'ean' 				=> $import_product_file_mapping['O'] != '0'?$value[$import_product_file_mapping['O']]:'',
                                'jan' 				=> $import_product_file_mapping['P'] != '0'?$value[$import_product_file_mapping['P']]:'',
                                'isbn' 				=> $import_product_file_mapping['Q'] != '0'? $value[$import_product_file_mapping['Q']]:'',
                                'mpn' 				=> $import_product_file_mapping['R'] != '0' ?$value[$import_product_file_mapping['R']]:'',
                                'location' 			=> $import_product_file_mapping['S'] != '0' ?$value[$import_product_file_mapping['S']]:'',
                                'price' 			=> $import_product_file_mapping['T'] != '0' ?$value[$import_product_file_mapping['T']]:'0',
                                'tax_class_id' 		=> $import_product_file_mapping['U'] != '0' ?$value[$import_product_file_mapping['U']]:'',
                                'quantity' 			=> $import_product_file_mapping['W'] != '0'?$value[$import_product_file_mapping['W']]:'0',
                                'minimum' 			=> $import_product_file_mapping['X'] != '0'?$value[$import_product_file_mapping['X']]:'',
                                'subtract' 			=> $import_product_file_mapping['Y'] != '0'?$value[$import_product_file_mapping['Y']]:'',
                                'stock_status_id' 	=> $import_product_file_mapping['Z'] != '0'?$value[$import_product_file_mapping['Z']]:'',
                                'shipping' 			=> $import_product_file_mapping['AB'] != '0'?$value[$import_product_file_mapping['AB']]:'',
                                'keyword' 			=> $import_product_file_mapping['AC'] != '0'?$value[$import_product_file_mapping['AC']]:'',
                                'date_available' 		=> ($import_product_file_mapping['AD'] != '0' && $value[$import_product_file_mapping['AD']] ? $value[$import_product_file_mapping['AD']] : date('Y-m-d')),
                                'length' 				=> $import_product_file_mapping['AE'] != '0'?$value[$import_product_file_mapping['AE']]:'',
                                'length_class_id' 	=> $import_product_file_mapping['AF'] != '0'?$value[$import_product_file_mapping['AF']]:'',
                                'width' 				=> $import_product_file_mapping['AH'] != '0'?$value[$import_product_file_mapping['AH']]:'',
                                'height' 				=> $import_product_file_mapping['AI'] != '0'?$value[$import_product_file_mapping['AI']]:'',
                                'weight' 				=> $import_product_file_mapping['AJ'] != '0'?$value[$import_product_file_mapping['AJ']]:'',
                                'weight_class_id' 	=> $import_product_file_mapping['AK'] != '0'?$value[$import_product_file_mapping['AK']]:'',
                                'status' 				=> $import_product_file_mapping['AM'] != '0'?$value[$import_product_file_mapping['AM']]:'0',
                                'sort_order' 			=> $import_product_file_mapping['AN'] != '0'?$value[$import_product_file_mapping['AN']]:'',
                                'manufacturer_id' 	=> $import_product_file_mapping['AO'] != '0'?$value[$import_product_file_mapping['AO']]:'',
                                'categories'			=> array_unique($categoryids),
                                'filters'				=> array_unique($filters),
                                'downloads' 			=> $downloads,
                                'relaled_products' 	=> $relaled_products,
                                'attributes'			=> $attributes,
                                'options'				=> $options,
                                'discounts'			=> $discounts,
                                'specails'			=> $specails,
                                'images'				=> $images,
                                'points' 				=> $import_product_file_mapping[$reward_column] != '0'?$value[$import_product_file_mapping[$reward_column]]:'',
                                'viewed' 				=> $import_product_file_mapping[$viewed_column] != '0'?$value[$import_product_file_mapping[$viewed_column]]:'',
                                'slug' 				=> $import_product_file_mapping[$slug_column] != '0'?$value[$import_product_file_mapping[$slug_column]]:'',
                                'maximum' 				=> $import_product_file_mapping[$maximum_column] != '0'?$value[$import_product_file_mapping[$maximum_column]]:'',
                                'cost_price' 				=> $import_product_file_mapping[$cost_price_column] != '0'?$value[$import_product_file_mapping[$cost_price_column]]:'',
                                'unlimited'  => $import_product_file_mapping[$unlimited_column] != '0' ? $value[$import_product_file_mapping[$unlimited_column]] : '0',
                                'product_variation_sku'  => isset($product_variation_sku) ? $product_variation_sku : '',
                                );
                        }
                        // Import product data in case of no/simple options from current row
                        if($option_format != 2 || ($option_format == 2 && $import_advanced_options)){
                            
                            if(count($languages) > 0){
                                if($option_format != 2){
                                    // if($product_model != "" && $product_model == $value[$import_product_file_mapping['E']]){
                                    //     $language_pointer++;
                                    // }
                                    // else{                                        
                                    //     $product_model = $value[$import_product_file_mapping['E']];
                                    //     $language_pointer = 0;
                                    // }

                                    $language_pointer++;
                                    if($language_pointer >= count($languages))
                                        $language_pointer = 0;                                     
                                }

                                $language_code = $languages[$language_pointer];
                                $language_id = $this->model_localisation_language->getLanguageByCode($language_code)['language_id'];
                            }
                            
                            if($import_product_file_mapping['A'] != '0' && $value[$import_product_file_mapping['A']] != "" && (int)$value[$import_product_file_mapping['A']]){
                                
                                $product_availability = $this->model_catalog_product->checkIfProductIdIsExisted($value[$import_product_file_mapping['A']]);
                                try{             
                                    if($product_availability){
                                        $this->model_tool_product_import->Editproduct($importdata,$value[$import_product_file_mapping['A']],$language_id,$store_id, $seller_id);
                                        $updateproduct++;
                                    }else{
                                        if(count($languages) <= 1 ){
                                            $language_data = 'all';
                                        } else {
                                            $language_data = '';
                                        }

                                        $addData = [
                                            'limit_diff' => $limit_diff,
                                            'newproduct' => $newproduct,
                                            'importdata' => $importdata,
                                            'language_id' => $language_id,
                                            'store_id' => $store_id,
                                            'language_data' => $language_data,
                                            'seller_id' => $seller_id,
                                            'mapping' => $value[$import_product_file_mapping['A']],
                                            'method' => 'addoldproduct'
                                        ];
                                        $this->addProductAction($addData);
                                        $newproduct++;

//                                        if ( $limit_diff >= ($newproduct+1) ) {
//                                            $this->model_tool_product_import->addoldproduct($importdata,$language_id,$language_data,$store_id,$value[$import_product_file_mapping['A']], $seller_id);
//                                            $newproduct++;
//                                        }
//                                        else{
//                                            $this->errors[] = "Limit is reached";
//                                        }
                                    }
                                }
                                catch(Exception $e){
                                    $this->errors[] = "There is a problem in importing/updating product: ".$value[$import_product_file_mapping['D']];
                                }
                            }
                            elseif($import_product_file_mapping['E'] != '0' && !empty($value[$import_product_file_mapping['E']])){
                                $product_id = $this->model_tool_product_import->getproductIDbymodel($value[$import_product_file_mapping['E']]);

                                try{
                                    if($product_id){
                                        $this->model_tool_product_import->Editproduct($importdata,$product_id,$language_id,$store_id, $seller_id);
                                        $updateproduct++;
                                    }else{
                                        $addData = [
                                            'limit_diff' => $limit_diff,
                                            'newproduct' => $newproduct,
                                            'importdata' => $importdata,
                                            'language_id' => $language_id,
                                            'store_id' => $store_id,
                                            'method' => 'addproduct'
                                        ];
                                        $this->addProductAction($addData);
                                        $newproduct++;

//                                        if ( $limit_diff >= ($newproduct+1) ){
//                                            $this->model_tool_product_import->addproduct($importdata,$language_id,$store_id);
//                                            $newproduct++;
//                                        }else{
//                                            $this->errors[] = "Limit is reached";
//                                        }
                                    }
                                }
                                catch(Exception $e){
                                    $this->errors[] = "There is a problem in importing/updating product: ".$value[$import_product_file_mapping['D']];
                                }
                                
                            }
                            else{

                                try{
                                    $addData = [
                                        'limit_diff' => $limit_diff,
                                        'newproduct' => $newproduct,
                                        'importdata' => $importdata,
                                        'language_id' => $language_id,
                                        'store_id' => $store_id,
                                        'method' => 'addproduct'
                                    ];
                                    $this->addProductAction($addData);
                                    $newproduct++;

//                                    if ( $limit_diff >= ($newproduct+1) ) {
//                                        $this->model_tool_product_import->addproduct($importdata,$language_id,$store_id);
//                                        $newproduct++;
//                                    }
//                                    else{
//                                        $this->errors[] = "Limit is reached";
//                                    }
                                }
                                catch(Exception $e){
                                    $this->errors[] = "There is a problem in importing/updating product: ".$value[$import_product_file_mapping['D']];
                                }
                            }
                        
                        }
                        $import_advanced_options = false;
                    }
                }
                // we should find a way to pass rows with no main data.
                $i++;
            }

        }

        if(count($languages) > 1){
            $newproduct /= count($languages);
            $updateproduct /= count($languages);
        }

        if($newproduct || $updateproduct){
            $this->error = sprintf($this->language->get('text_success'),$newproduct,$updateproduct);
        }
    
        if(!$newproduct && !$updateproduct){
            $this->error = $this->language->get('text_no_data');            
        }

        $this->model_tool_product_import->update_import_file_status($file_id,$this->error,$this->errors);
        
        unlink($inputFileName);

    }

    private function addProductAction($data){
        //Exclude plan id 8 (enterprise)
        if ( $data['limit_diff'] < ($data['newproduct']+1) && PRODUCTID != 8) {
            $data['importdata']['status'] = 0;
        }

        if($data['method'] == 'addproduct')
            $this->model_tool_product_import->addproduct($data['importdata'], $data['language_id'], $data['store_id'], $data['seller_id']);
        elseif($data['method'] == 'addoldproduct')
            $this->model_tool_product_import->addoldproduct($data['importdata'], $data['language_id'], $data['language_data'], $data['store_id'], $data['mapping'], $data['seller_id']);
    }
}
