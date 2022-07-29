<?php

use pcrov\JsonReader\JsonReader;
use ExpandCart\Foundation\String\Slugify;

set_time_limit(0);

class ModelModuleRewixDropshippingProduct extends Model 
{
    /**
     * @const strings API URLs.
     */
    const BASE_API_TESTING_URL    = 'https://griffati.rewix.zero11.org';
    const BASE_API_PRODUCTION_URL = 'https://www.griffati.com';
    private $baseurl = '';

    private $insertProductStatement;
    private $insertProductDescStatement;
    private $insertProductStoreStatement;
    private $insertProductrewixStatement;        
    private $insertProductSubImagesStatement; 

    private $insertCategoryStatement;
    private $insertCategoryDescStatement;
    private $insertCategoryrewixStatement;
    private $insertCategoryPathStatement;
    private $insertCategoryStoreStatement;
    private $insertProductCategoryStatement;
    private $existedCategories;

    private $insertManufacturerStatement;
    private $insertManufacturerStoreStatement;
    private $existedManufacturer;

    private $insertOptionStatement      ;
    private $insertOptionDescStatement  ;
    private $insertOptionValueStatement ;
    private $insertOptionValueDescStatement    ;
    private $insertProductOptionStatement      ;
    private $insertProductOptionValueStatement ;
    private $existedOptions;
    private $productsLinkedOptions;
    private $productLinkedOptionValues;

    private $images = [];
    private $totalInserted = 0;

    public function __construct($registry){
        parent::__construct($registry);

        $this->baseurl = $this->_getBaseUrl();
        
        //Insert Product Prepared Statement
        $insertProductQueryFields = ['model', 'sku', 'upc', 'ean', 'jan', 'isbn', 'mpn', 'location', 
        'quantity','minimum', 'barcode','stock_status_id', 'date_available','manufacturer_id','status', 
        'tax_class_id', 'price','image', 'weight'];
        $insertProductQuery = "INSERT INTO `product` (" . implode(',', $insertProductQueryFields) . ") VALUES(" . str_repeat('?,', count($insertProductQueryFields) - 1) . "?)";
        $this->insertProductStatement = $this->db->prepare($insertProductQuery);
        // $updateProductStatement = $this->db->prepare("UPDATE product SET quantity = ? WHERE product_id = ?");
        //Insert Product-Description Prepared Statement
        $insertProductDescQueryFields = ['product_id', 'language_id', 'name', 'description', 'meta_description', 'tag','meta_keyword', 'slug'];         
        $insertProductDescQuery = "INSERT INTO `product_description` (" . implode(',', $insertProductDescQueryFields) . ") VALUES(" . str_repeat('?,', count($insertProductDescQueryFields) - 1) . "?)";
        $this->insertProductDescStatement = $this->db->prepare($insertProductDescQuery);
        //insert in rewix_product
        $this->insertProductrewixStatement = $this->db->prepare("INSERT INTO `rewix_product` (`rewix_product_id`, `expandcart_product_id`) VALUES (?, ?)");
        //insert sub-images
        $this->insertProductSubImagesStatement = $this->db->prepare("INSERT INTO `product_image` (`product_id`, `image`) VALUES (?, ?)");
        //insert 
        $this->insertProductStoreStatement = $this->db->prepare("INSERT INTO `product_to_store` (`product_id`, `store_id`) VALUES (?, ?)");


        //categories
        //Insert category Prepared Statement
        $insertCategoryQueryFields = ['`parent_id`', '`top`', '`column`', '`status`', '`date_modified`'];           
        $insertCategoryQuery = "INSERT INTO `category` (" . implode(',', $insertCategoryQueryFields) . ") VALUES(" . str_repeat('?,', count($insertCategoryQueryFields) - 1) . "?)";
        $this->insertCategoryStatement = $this->db->prepare($insertCategoryQuery);

        //Insert Category-Description Prepared Statement
        $insertCategoryDescQueryFields = ['`category_id`', '`language_id`', '`name`', '`description`', '`meta_description`','`meta_keyword`', '`slug`'];            
        $insertCategoryDescQuery = "INSERT INTO `category_description` (" . implode(',', $insertCategoryDescQueryFields) . ") VALUES(" . str_repeat('?,', count($insertCategoryDescQueryFields) - 1) . "?)";
        $this->insertCategoryDescStatement = $this->db->prepare($insertCategoryDescQuery);

        $this->insertCategoryPathStatement  = $this->db->prepare("INSERT INTO `category_path` (`category_id`, `path_id`, `level`) VALUES (?, ?, ?)");
        $this->insertCategoryStoreStatement = $this->db->prepare("INSERT INTO `category_to_store` (`category_id`, `store_id`) VALUES (?, ?)");
        //insert in rewix_product
        $this->insertCategoryrewixStatement   = $this->db->prepare("INSERT INTO `rewix_category` (`rewix_category_id`, `expandcart_category_id`) VALUES (?, ?)");
        //insert product-category
        $this->insertProductCategoryStatement = $this->db->prepare("INSERT INTO `product_to_category` (`product_id`, `category_id`) VALUES (?, ?)");

        $existedCategories = $this->db->query("SELECT DISTINCT `category_id`, LOWER(`name`) as `name` FROM category_description WHERE language_id = 1 ")->rows;
        $this->existedCategories = array_column($existedCategories, 'category_id', 'name');

        //Manufacturer
        $this->insertManufacturerStatement      = $this->db->prepare("INSERT INTO `manufacturer` (`name`, `sort_order`, `slug`) VALUES (?, ?, ?)");
        $this->insertManufacturerStoreStatement = $this->db->prepare("INSERT INTO `manufacturer_to_store` (`manufacturer_id`, `store_id`) VALUES (?, ?)");

        $existedManufacturer = $this->db->query("SELECT DISTINCT manufacturer_id , LOWER(`name`) as `name` FROM manufacturer")->rows;
        $this->existedManufacturer = array_column($existedManufacturer, 'manufacturer_id', 'name');

        //insert prepared statments
        $this->insertOptionStatement      = $this->db->prepare("INSERT INTO `option` (`type`, `sort_order`) VALUES (?, ?)");
        $this->insertOptionDescStatement  = $this->db->prepare("INSERT INTO `option_description` (`option_id`, `language_id`, `name`) VALUES ( ? , ? , ? )");
        
        $this->insertOptionValueStatement = $this->db->prepare("INSERT INTO `option_value` (`option_id`, `image`, `sort_order`) VALUES ( ? , ? , ? )");
        $this->insertOptionValueDescStatement    = $this->db->prepare("INSERT INTO `option_value_description` (`option_value_id`, `language_id`, `option_id`, `name`) VALUES (?, ?, ?, ?)");
        
        $this->insertProductOptionStatement      = $this->db->prepare("INSERT INTO `product_option` (`product_id`, `option_id`, `option_value`, `required`, `sort_order`) VALUES (?, ?, ?, ?, ?)");
        $this->insertProductOptionValueStatement = $this->db->prepare("INSERT INTO `product_option_value` (`product_option_id`, `product_id`, `option_id`, `option_value_id`, `quantity`, `subtract`, `price`, `price_prefix`, `points`, `points_prefix`, `weight`, `weight_prefix`, `sort_order`) VALUES (?, ?, ?, ?, ?, ?, ?, ? , ?, ?, ? , ?, ?)");
        
        $this->existedOptions = array_column($this->db->query("
            SELECT
            group_concat(LOWER(ovd.`name`))as option_values_name,
            group_concat(ovd.option_value_id) as option_values_id, 
            LOWER(od.`name`) as option_name,
            od.option_id 
            FROM  `option_value_description` AS ovd
            JOIN option_description AS od ON od.option_id = ovd.option_id
            WHERE ovd.language_id = 1 AND od.language_id = 1
            group by od.option_id")->rows, null, 'option_name');

        $this->productsLinkedOptions = array_column($this->db->query("
            SELECT
            group_concat(po.option_id) as option_ids, 
            group_concat(po.product_option_id) as product_option_ids, 
            po.product_id
            FROM  `product_option` AS po
            group by product_id;")->rows , null,'product_id');

        $this->productLinkedOptionValues = array_column(
            $this->db->query("
                SELECT group_concat(product_option_value_id) as product_option_value_ids,
                group_concat(option_value_id) as option_value_ids , 
                product_id 
                FROM product_option_value
                group by product_id
            ")->rows , null,'product_id');  
    }


    public function __destruct() {
        $this->insertProductStatement->close();
        $this->insertProductDescStatement->close();
        $this->insertProductStoreStatement->close();
        $this->insertProductrewixStatement->close();        
        $this->insertProductSubImagesStatement->close();

        $this->insertCategoryStatement->close();
        $this->insertCategoryDescStatement->close();
        $this->insertCategoryrewixStatement->close();
        $this->insertCategoryPathStatement->close();
        $this->insertCategoryStoreStatement->close();
        $this->insertProductCategoryStatement->close();

        $this->insertManufacturerStatement->close();
        $this->insertManufacturerStoreStatement->close();

        $this->insertOptionStatement->close();
        $this->insertOptionDescStatement->close();
        $this->insertOptionValueStatement->close();   
        $this->insertOptionValueDescStatement->close();
        $this->insertProductOptionStatement->close();        
        $this->insertProductOptionValueStatement->close();
    }
    /**
	 * [POST] Get rewix Products from their API
	 *
	 * @return 
	 */
    public function getProducts()
    {   
        //For testing only
        // return [
        //         'status_code' => 200,
        //         'result'      => file_get_contents('rewix.json')
        //     ];
        $settings = $this->config->get('rewix_dropshipping');

    	$curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $this->_getBaseUrl() . '/restful/export/api/products.json?acceptedlocales=en_US',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
            'Authorization: Basic ' . base64_encode($settings['api_key'] . ':' . $settings['password'])
          ),
        ));

        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $err      = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return ['error' => $err];
        } else {
            return [
                'status_code' => $httpcode,
                'result'      => $response
            ];
        }
    }

    // public function insertBulkProducts(array $products , $addedProducts)
    public function insertBulkProducts(array $products)
    {
        try {
            $this->db->autocommit(FALSE);
        
            foreach ($products as $product) {
                $product = $this->formatProduct($product);

                //Add manufacturer
                $manufacturer_id = $this->addManufacturer($product['manufacturer']);                
                $product['product_data'][13] = $manufacturer_id;

                $this->insertProductStatement->bind_param("ssssssssiisisiiidsd", ...$product['product_data']);
                $this->insertProductStatement->execute();
                $product_id = $this->db->getLastId();
                $this->totalInserted++;
                //dispay errors if any
                //printf("Error insertProductStatement : %s.\n", $this->insertProductStatement->error);

                if( $product_id ) {

                    //add product description..                 
                    foreach( $product['product_description'] as $language_id => $product_description){
                        $this->insertProductDescStatement->bind_param("iissssss", $product_id, $language_id, ...(array_values($product_description)) );
                        $this->insertProductDescStatement->execute();
                        //printf("Error insertProductDescStatement : %s.\n", $this->insertProductDescStatement->error);
                    }

                    //Add product store relation
                    $this->insertProductStoreStatement->bind_param("ii", ...[$product_id, (int)$this->config->get('config_store_id')]);
                    $this->insertProductStoreStatement->execute();
                    //printf("Error insertProductStoreStatement : %s.\n", $this->insertProductStoreStatement->error);

                    $this->insertProductrewixStatement->bind_param("ii", ...[$product['rewix_product_id'] , $product_id]);
                    $this->insertProductrewixStatement->execute();
                    //printf("Error insertProductrewixStatement : %s.\n", $this->insertProductrewixStatement->error);

                    $this->addOptions($product_id , $product['options']);

                    //Add category...
                    $this->insertBulkCategories($product_id, $product['categories']);
                    // $this->model_module_rewix_dropshipping_category->insertBulkCategories($product_id, $product['categories']);

                    //Add product sub-images....
                    //Remove firt image (because this is the main image and it's already added above)
                    // foreach( array_slice($product['images'], 1, count($product['images'])) as $image){
                    //     $imageName = 'data/products/' . pathinfo($image, PATHINFO_BASENAME);
                    //     $this->insertProductSubImagesStatement->bind_param("is", $product_id, $imageName);
                    //     $this->insertProductSubImagesStatement->execute();
                    //     //printf("Error insertProductSubImagesStatement : %s.\n", $this->insertProductSubImagesStatement->error);
                    // }
                    // $this->images = array_merge($this->images , $product['images']);
                    $this->images[] = $product['image'];
                    // $this->downloadImages($product['images']);
                }            
            }
      
            $this->db->autocommit(TRUE);
            // return $images;
        } catch (Exception $e) {
            $this->db->rollback(); //remove all queries from queue if error (undo)
            throw $e;
        }
    }

    public function getImages()
    {
        return $this->images;
    }

    public function getTotalInserted()
    {
        return $this->totalInserted;
    }

    public function formatProduct($data)
    {
        $product = [];

        // categories & manufacturer
        $category = $manufacturer = [];
        $category_id = 0;
        foreach($data['tags'] as $tag){
            if( strcasecmp($tag['name'], 'brand') == 0 ){
                $manufacturer[] = [
                    'name' => $tag['value']['value'],
                    // 'manufacturer_store'    => [ 0 => 0],
                    // 'rewix_manufacturer_id' => $tag['id'],
                    'manufacturer_data'     => [ $tag['value']['value'], 0, (new Slugify)->slug($tag['value']['value']) ]
                ];
            }
            else if( strcasecmp($tag['name'], 'category') == 0 ){
                $category[$category_id] = [
                    'rewix_category_id' => $tag['id'],
                    'name' => $tag['value']['value'],
                    'category_data' => [ 0 , 0 , 0 , 1 , (new DateTime('NOW'))->format('Y-m-d H:i:s')],
                    'category_description' => [
                        '1' => [
                            'name' => $tag['value']['value'],
                            'description' => $tag['value']['value'],
                            'meta_description' => $tag['value']['value'],
                            'meta_keyword' => $tag['value']['value'],
                            'slug'=> str_replace(' ', '-', $tag['value']['value']) 
                        ],
                        '2' => [
                            'name' => $tag['value']['value'],
                            'description' => $tag['value']['value'],
                            'meta_description' => $tag['value']['value'],
                            'meta_keyword' => $tag['value']['value'],
                            'slug'=> str_replace(' ', '-', $tag['value']['value']) 
                        ]
                    ]
                ];

                $lastCategoryId = $category_id;
                $category_id++;
            }
            else if( strcasecmp($tag['name'], 'subcategory') == 0 ){
                $category[$category_id] = [
                    'rewix_category_id' => $tag['id'],
                    'name' => $tag['value']['value'],

                                        //parent_id
                    'category_data' => [ $lastCategoryId , 0 , 0 , 1 , (new DateTime('NOW'))->format('Y-m-d H:i:s')],
                    'category_description' => [
                        '1' => [
                            'name' => $tag['value']['value'],
                            'description' => $tag['value']['value'],
                            'meta_description' => $tag['value']['value'],
                            'meta_keyword' => $tag['value']['value'],
                            'slug'=> str_replace(' ', '-', $tag['value']['value']) 
                        ],
                        '2' => [
                            'name' => $tag['value']['value'],
                            'description' => $tag['value']['value'],
                            'meta_description' => $tag['value']['value'],
                            'meta_keyword' => $tag['value']['value'],
                            'slug'=> str_replace(' ', '-', $tag['value']['value']) 
                        ]
                    ]
                ];
                $category_id++;             
            }
        }

        // Options
        $options = $colors = $sizes = [];
        $options = [
            'size' => [
                'option_description' => [
                    1 => ['name' => 'size'],
                    2 => ['name' => 'المقاس']
                ],
                'type' => 'select'
            ],
            'color' => [
                'option_description' => [
                    1 => ['name' => 'color'],
                    2 => ['name' => 'اللون']
                ],
                'type' => 'select'
            ]            
        ];
        $quantity = 0;

        foreach($data['models'] as $model) {
            if($model['size'] != 'UNICA' /*&& !in_array( $model['size'], $sizes)*/){
                if( !empty($sizes[$model['size']]) ){
                    $sizes[$model['size']] += $model['availability'];
                    $quantity = $sizes[$model['size']];
                    $options['size']['option_value'][$model['size']]['quantity'] = $quantity;
                }
                else{
                    $colors[$model['size']] = $quantity = $model['availability'];

                    $options['size']['option_value'][$model['size']] = [
                        'option_value_description' => [
                            1 => ['name' => $model['size']],
                            2 => ['name' => $model['size']],
                        ],
                        'quantity' => $quantity,
                        'price'    => ((int)$data['suggestedPrice'] - (int)$model['suggestedPrice']), //price difference (original price - option price)
                        'weight'   => ((float)$data['weight'] - (float)$model['modelWeight']), //Weight difference..
                    ];
                }
            }            

            if($model['color'] != "multicolor" /*&& !in_array( $model['color'] , $colors)*/){
                if( !empty($colors[$model['color']]) ){
                    $colors[$model['color']] += $model['availability'];
                    $quantity = $colors[$model['color']];
                    $options['color']['option_value'][$model['color']]['quantity'] = $quantity;
                }
                else{
                    $colors[$model['color']] = $quantity = $model['availability'];

                    $options['color']['option_value'][$model['color']] = [
                        'option_value_description' => [
                            1 => ['name' => $model['color']],
                            2 => ['name' => $model['color']],
                        ],
                    'quantity' => $quantity,
                    'price'    => ((int)$data['suggestedPrice'] - (int)$model['suggestedPrice']), //price difference (original price - option price)
                    'weight'   => ((float)$data['weight'] - (float)$model['modelWeight']), //Weight difference..       
                    ];
                }
            }
        }
        
        //calculate price        
        $price = $data['suggestedPrice'];
        // $price = $this->currency->convert($data['suggestedPrice'], $data['currency'] , $this->config->get('config_currency'));
        
        return [
            'rewix_product_id' => $data['id'],
            'name' => $data['name'],
            'quantity' => $data['availability'],
            'product_data' => [
                '', $data['code'], '', '', '', '', '', '', $data['availability'], 0, $data['Barcode'], 
                $this->config->get('config_stock_status_id'),(new \DateTime('NOW'))->format('Y-m-d'), 0, 1, 0, $price, 'data/products/'.pathinfo($data['images'][0]['url'], PATHINFO_BASENAME), 
                $data['weight'],
            ],
            // 'images'  => array_column($data['images'], 'url'), //with subimages
            'image'   => $data['images'][0]['url'],
            'options' => $options,
            'product_description' => [
                '1' => [
                    'name'        => $data['productLocalizations']['productName']['en_US']['value'] ?: $data['name'],
                    'description' => $data['productLocalizations']['description']['en_US']['value'] ?: $data['name'],
                    'meta_description' => '',
                    'tag'              => '',
                    'meta_keyword'     => '',
                    'slug'             => '',
                ],
                '2' => [
                    'name'        => $data['productLocalizations']['productName']['ar_SA']['value'] ?: $data['name'],
                    'description' => $data['productLocalizations']['description']['ar_SA']['value'] ?: $data['name'],
                    'meta_description' => '',
                    'tag'              => '',
                    'meta_keyword'     => '',
                    'slug'             => '',
                ]               
            ],
            'categories' => $category,
            'manufacturer' => $manufacturer,
        ];
    }

    public function downloadImages($links, $chunkSize = 50)
    {
        $failed = [];
        $chunks = array_chunk($links, $chunkSize);
        $curlArray = [];
        $master = curl_multi_init();

        foreach($chunks as $key => $urls){
            $node_count = count($urls);

            for($i = 0; $i < $node_count; $i++) {
                $curlArray[$i] = curl_init($this->baseurl.$urls[$i]);
                curl_setopt($curlArray[$i], CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curlArray[$i], CURLOPT_HEADER, 0);
                curl_setopt($curlArray[$i], CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($curlArray[$i], CURLOPT_SSL_VERIFYPEER, 0);
                curl_multi_add_handle($master, $curlArray[$i]);
            }
            //execute the multi handle
            $active = null;
            do {
             $status = curl_multi_exec($master, $active);
            } while ($status == CURLM_CALL_MULTI_PERFORM);

            while ($active && $status == CURLM_OK) {
                if (curl_multi_select($master) != -1) {
                    do {
                        $status = curl_multi_exec($master, $active);
                    } while ($status == CURLM_CALL_MULTI_PERFORM);
                }
            }

            for($i = 0; $i < $node_count; $i++) {
                $httpCode = curl_getinfo($curlArray[$i], CURLINFO_HTTP_CODE);
                // var_dump('$httpCode='.$httpCode);
                
                $imageName = pathinfo($urls[$i], PATHINFO_BASENAME);

                if( $httpCode === 200) {
                    $data = curl_multi_getcontent( $curlArray[$i] );
                    // $this->saveImage($imageName, $data);
                    $filePath = DIR_ONLINESTORES .'ecdata/stores/' . STORECODE  . '/image/data/products/' . $imageName;

                    if( !empty($imageName) && strlen($data) > 0 ) { //&& !file_exists($filePath)
                        file_put_contents( $filePath, $data );
                    }
                }
                else{
                    $failed[] = $urls[$i];
                }
                curl_multi_remove_handle($master, $curlArray[$i]);
                // curl_close($curlArray[$i]);
            }
            // sleep(1);
        }

        curl_multi_close($master); 
        unset($curlArray);
        return $failed;
        // echo '<pre>Failed'; print_r($failed);
    }

    private function saveImage($imageName, $data)
    {
        if( !empty($imageName) && strlen($data) > 0 ) {
            $filePath = DIR_ONLINESTORES .'ecdata/stores/' . STORECODE  . '/image/data/products/' . $imageName;
            file_put_contents( $filePath, $data );
        }
    }
    
    public function addManufacturer($manufacturer)
    {
        if( !($manufacturer_id = $this->existedManufacturer[strtolower($manufacturer[0]['name'])]) ){
            $this->insertManufacturerStatement->bind_param('sis', ...$manufacturer[0]['manufacturer_data']);
            $this->insertManufacturerStatement->execute();
            $manufacturer_id = $this->db->getLastId();
            //printf("Error insertManufacturerStatement : %s.\n", $insertManufacturerStatement->error);
            $this->existedManufacturer[strtolower($manufacturer[0]['name'])] = $manufacturer_id;

            $this->insertManufacturerStoreStatement->bind_param('ii', ...[$manufacturer_id, (int)$this->config->get('config_store_id')]);
            $this->insertManufacturerStoreStatement->execute();
            //printf("Error insertManufacturerStoreStatement : %s.\n", $insertManufacturerStoreStatement->error);
        }
        
        return $manufacturer_id;
    }

    public function addOptions($product_id, $options)
    {

        //2- loop parameter options
        //check each if exist get id, if not,then add it.
        foreach($options as $name => $option){
            if( empty($option['option_value']) )continue;
            
            $existedOption = [];

            if( empty($existedOption = $this->existedOptions[$name]) ){
                //Add new option
                $this->insertOptionStatement->bind_param('si', ...['select', 0]);
                $this->insertOptionStatement->execute();
                //printf("Error insertOptionStatement : %s.\n", $insertOptionStatement->error);
                $option_id = $this->db->getLastId();

                foreach( $option['option_description'] as $language_id => $option_description){
                    $this->insertOptionDescStatement->bind_param("iis", $option_id, $language_id, $name );
                    $this->insertOptionDescStatement->execute();
                    //printf("Error insertOptionDescStatement : %s.\n", $insertOptionDescStatement->error);
                }

                $existedOption = [
                    'option_id'   => $option_id,
                    'option_name' => $name,
                    'option_values_id'   => '',
                    'option_values_name' => ''
                ];

                $this->existedOptions[$name] = $existedOption;

                $this->insertProductOptionStatement->bind_param("iisii", ...[$product_id, $option_id,'',1, 0 ]);
                $this->insertProductOptionStatement->execute();
                //printf("Error insertProductOptionStatement : %s.\n", $insertProductOptionStatement->error);
                $product_option_id = $this->db->getLastId();
            }else{
                //check if not linked, then link it...
                $options_ids = explode(',', $this->productsLinkedOptions[$product_id]['option_ids']);
                $product_option_id = explode(',',$this->productsLinkedOptions[$product_id]['product_option_ids'])[array_search($existedOption['option_id'], $options_ids)];

                if( empty($product_option_id) ) {
                    //Add relation between product_id and option_id
                    $this->insertProductOptionStatement->bind_param("iisii", ...[$product_id, $existedOption['option_id'],'',1, 0 ]);
                    $this->insertProductOptionStatement->execute();
                    //printf("Error insertProductOptionStatement : %s.\n", $insertProductOptionStatement->error);
                    $product_option_id = $this->db->getLastId();
                }

                //update $this->productsLinkedOptions
                $this->productsLinkedOptions[$product_id]['option_ids'] .= ','.$existedOption['option_id'];
                $this->productsLinkedOptions[$product_id]['product_option_ids'] .= ','.$existedOption['product_option_id'];
            }

            foreach($option['option_value'] as $optionValueName => $option_value){
                //To Check option value if exist
                //1-Get existed option values (name, id) pair array
                $existedOptionValues      = array_combine(
                    explode(',', $existedOption['option_values_name']),
                    explode(',', $existedOption['option_values_id'])
                );

                //Check if exist get id
                if( empty($existedOptionValueId = $existedOptionValues[strtolower($optionValueName)]) ){
                    //add new option value
                    $this->insertOptionValueStatement->bind_param('isi', ...[$existedOption['option_id'], 'no_image.jpg', 0]);
                    $this->insertOptionValueStatement->execute();
                    //printf("Error insertOptionValueStatement : %s.\n", $insertOptionValueStatement->error);
                    $existedOptionValueId = $this->db->getLastId();

                    foreach( $option_value['option_value_description'] as $language_id => $option_value_description){
                        $this->insertOptionValueDescStatement->bind_param("iiis", $existedOptionValueId, $language_id, $existedOption['option_id'] , ...(array_values($option_value_description)) );
                        $this->insertOptionValueDescStatement->execute();
                        //printf("Error insertOptionValueDescStatement : %s.\n", $insertOptionValueDescStatement->error);
                    }

                    $this->existedOptions[$name]['option_values_id']   .= ',' . $existedOptionValueId;
                    $this->existedOptions[$name]['option_values_name'] .= ',' . strtolower($optionValueName);

                    $this->insertProductOptionValueStatement->bind_param("iiiiiidsisdsi", ...[$product_option_id,$product_id, $existedOption['option_id'], $existedOptionValueId,$option_value['quantity'],1, $option_value['price'], '+', 0, '', $option_value['weight'], '+', 0 ]);
                    $this->insertProductOptionValueStatement->execute();
                }
                else{
                    $option_value_ids = explode(',',$this->productLinkedOptionValues[$product_id]['option_value_ids']);
                    $productOptionValue = explode(',',$this->productLinkedOptionValues[$product_id]['product_option_value_ids'])[array_search($existedOptionValueId, $option_value_ids)];
                    if( empty($productOptionValue) ){
                        //Add relation between product_id and option_value_id
                        $this->insertProductOptionValueStatement->bind_param("iiiiiidsisdsi", ...[$product_option_id,$product_id, $existedOption['option_id'], $existedOptionValueId,$option_value['quantity'],1, $option_value['price'], '+', 0, '', $option_value['weight'], '+', 0 ]);
                        $this->insertProductOptionValueStatement->execute();
                        //printf("Error insertProductOptionValueStatement : %s.\n", $insertProductOptionValueStatement->error);
                        $productOptionValueId = $this->db->getLastId();
                        
                        //update $this->productsLinkedOptions
                        $this->productLinkedOptionValues[$product_id]['option_value_ids'] .= ','.$existedOptionValueId;
                        $this->productLinkedOptionValues[$product_id]['product_option_value_ids'] .= ','.$productOptionValueId;
                    }
                }
            }        
        }
    }

    public function deleteProducts()
    {
        $this->db->query("DELETE FROM product WHERE product_id IN (SELECT expandcart_product_id FROM `rewix_product`)");
        $this->db->query("DELETE FROM product_description WHERE product_id IN (SELECT expandcart_product_id FROM `rewix_product`)");
        $this->db->query("DELETE FROM product_image WHERE product_id IN (SELECT expandcart_product_id FROM `rewix_product`)");
        $this->db->query("DELETE FROM product_to_category WHERE product_id IN (SELECT expandcart_product_id FROM `rewix_product`)");
        $this->db->query("DELETE FROM product_to_store WHERE product_id IN (SELECT expandcart_product_id FROM `rewix_product`)");
        $this->db->query("TRUNCATE TABLE rewix_product;");

        $this->db->query("DELETE FROM category WHERE category_id IN (SELECT expandcart_category_id FROM `rewix_category`)");
        $this->db->query("DELETE FROM category_description WHERE category_id IN (SELECT expandcart_category_id FROM `rewix_category`)");
        $this->db->query("DELETE FROM category_to_store WHERE category_id IN (SELECT expandcart_category_id FROM `rewix_category`)");
        $this->db->query("DELETE FROM category_path WHERE category_id IN (SELECT expandcart_category_id FROM `rewix_category`)");
        $this->db->query("TRUNCATE TABLE rewix_category;");
        
        $this->db->query("DELETE FROM manufacturer");
        $this->db->query("DELETE FROM manufacturer_to_store");

        unlink('imagesurlsrewix.json');
    }

    public function insertBulkCategories(int $product_id, array $categories)
    {        
        //loop on categories to be added
        foreach( $categories as $category ){
            //Check if the category name is already exist,
            if(!( $category_id = $this->existedCategories[strtolower($category['name'])] ) ){
                $this->insertCategoryStatement->bind_param("iiiis", ...$category['category_data']);
                $this->insertCategoryStatement->execute();
                //printf("Error insertCategoryStatement : %s.\n", $insertCategoryStatement->error);

                $category_id = $this->db->getLastId();
                $this->existedCategories[strtolower($category['name'])] = $category_id;

                foreach( $category['category_description'] as $language_id => $category_description){
                    $this->insertCategoryDescStatement->bind_param("iisssss", $category_id, $language_id, ...(array_values($category_description)) );
                    $this->insertCategoryDescStatement->execute();
                    //printf("Error insertCategoryDescStatement : %s.\n", $insertCategoryDescStatement->error);
                }

                $this->insertCategoryPathStatement->bind_param("iii", ...[$category_id, $category_id, 0]);
                $this->insertCategoryPathStatement->execute();
                //printf("Error insertCategoryPathStatement : %s.\n", $insertCategoryPathStatement->error);

                $this->insertCategoryStoreStatement->bind_param("ii", ...[$category_id, (int)$this->config->get('config_store_id')]);
                $this->insertCategoryStoreStatement->execute();
                //printf("Error insertCategoryStoreStatement : %s.\n", $insertCategoryStoreStatement->error);

                $this->insertCategoryrewixStatement->bind_param("ii", ...[$category['rewix_category_id'], $category_id]);
                $this->insertCategoryrewixStatement->execute();
                //printf("Error insertCategoryrewixStatement : %s.\n", $insertCategoryrewixStatement->error);
            }
            //Link product to these categories in product_to_category table
            $this->insertProductCategoryStatement->bind_param("ii", $product_id, $category_id);
            $this->insertProductCategoryStatement->execute();
        }
    }
    /* Helper Methods */
    private function _getBaseUrl()
    {
      //Check if API is in Debugging Mode..
      $is_debugging_mode = $this->config->get('rewix_dropshipping')['debugging_mode'];
      return ( isset($is_debugging_mode) && $is_debugging_mode == 1 ) ? self::BASE_API_TESTING_URL : self::BASE_API_PRODUCTION_URL;
    }
}
