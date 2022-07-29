<?php

use GuzzleHttp\Client;

use ExpandCart\Foundation\String\Slugify;

/**
 * API Model for printful app
 * 
 * @author MohamedHassanWD <hassan.mohamed.sf@expandcart.com>
 * @copyright 2020 ExpandCart
 * @see https://www.printful.com/docs/products
 */
class ModelModulePrintfulApi extends Model
{
    /**
     * Base api endpints url
     * 
     * @var string
     */
    private $base_url = 'https://api.printful.com/';

    /**
     *  Get products with pagination
     * @param null $apikey
     * @param int $offset
     * @param int $limit
     * @return bool|mixed|string|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getProducts($apikey = null, $offset = 0, $limit = 100)
    {
        $client = new Client([
            'base_uri' => $this->base_url,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($apikey)
            ]
        ]);

        $result = false;

        try {
            $res = $client->request('GET', "sync/products?limit=$limit&offset=$offset");

            if ($res->getStatusCode() === 200) {
                $result = json_decode($res->getBody()->getContents(), 1);
            }

        } catch (\Exception $e) {
            $result = $e->getMessage();
        }

        return $result;
    }

    /**
     * Return all imported products from printful_to_products table
     * 
     * @param int $offset
     * @param int $limit
     * @return array|null
     */
    public function getImportedProducts(int $offset=0,int $limit=20)
    {
        return $this->db->query("select * from `printful_to_products` order by rand() limit $offset, $limit")->rows;
    }
    
    /**
     * Return all imported products count
     * 
     * @return int|null
     */
    public function getImportedProductsCount()
    {
        return $this->db->query("select count(*) as count from `printful_to_products`")->row['count'];
    }

    /**
     * Get information about 1 product
     *
     * @param string $apikey
     * @param int $product_id
     * @return void
     */
    public function getProduct($apikey = null, $product_id = null)
    {
        $client = new Client([
            'base_uri' => $this->base_url,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($apikey)
            ]
        ]);

        $res = $client->request('GET', "sync/products/$product_id");
        if ($res->getStatusCode() === 200) {
            return json_decode($res->getBody()->getContents(), 1);
        }
    }

    /**
     * Get information about 1 product
     *
     * @param string $apikey
     * @param int $product_id
     * @return void
     */
    public function getProductDetails($apikey = null, $variant_id = null)
    {
        $client = new Client([
            'base_uri' => $this->base_url,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($apikey)
            ]
        ]);
        //not call service if not $variant_id

        if(!empty($variant_id)) {
            $res = $client->request('GET', "sync/variant/$variant_id");
            // echo('<pre>'); print_R($res);

            if ($res->getStatusCode() === 200) {
                return json_decode($res->getBody()->getContents(), 1);
            } else {
                $result = "";
                return ($result);
            }
        }else
        {
            $result = "";
            return ($result);
        }
    }

    /**
     * Insert one variant as a product
     *
     * @param array $variant
     * @param array $parent_product
     * @return void
     */
    public function insertVariant($variant, $parent_product)
    {
        //Check if product imported before
        $product_id = $this->checkIfProductImported($parent_product,$variant);

        if(!$product_id){
            //Insert product into product table
            $inserted_product_id = $this->insertMainProduct($variant);
            
            //Insert product description
            $this->insertProductDescription($variant, $inserted_product_id);
            
            //Insert into the printful_to_products table
            $this->insertPrintfulProduct($variant, $parent_product, $inserted_product_id);
            
            //Insert product images
            $this->insertProductImages($variant, $inserted_product_id);
        }
    }

    /**
     * Grep an image with CURL and save it to the specified path
     *
     * @param string $url
     * @param string $saveTo
     * @return void
     */
    public function grab_image(string $url, string $saveTo): void
    {
        set_time_limit(0);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 400);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $raw = curl_exec($ch);
        curl_close($ch);

        if (\Filesystem::isExists($saveTo)) {
            \Filesystem::deleteFile($saveTo);
        }
        \Filesystem::setPath($saveTo)->put($raw);

    }

    /**
     * Handle the insert for each variant's main data
     *
     * @param array $variant
     * @return int $inserted_product_id Inserted product ID
     */
    public function insertMainProduct($variant)
    {
        //Grep image
        preg_match('#[a-zA-Z0-9_]*\.[a-zA-Z]{3,}$#', $variant['product']['image'], $matches);
        $this->grab_image($variant['product']['image'], 'image/data/products/' . $matches[0]);

        //Insert product
        $sql = '
            insert into product (
                price,
                image,
                model,
                sku,
                location,
                manufacturer_id,
                quantity,
                stock_status_id,
                date_available,
                weight_class_id,
                tax_class_id,
                pd_custom_price,
                length_class_id,
                sort_order,
                status,
                date_modified
            ) values (
                "' . $variant['retail_price'] . '",
                "data/products/' . $matches[0] . '",
                "' . $variant['sku'] . '",
                "' . $variant['sku'] . '",
                "",
                0,
                ' . rand(150, 1000) . ',
                1,
                DATE(NOW()),
                1,
                0,
                0,
                1,
                1,
                1,
                DATE(NOW())
            )
        ';

        $this->db->query($sql);

        $inserted_product_id = $this->db->getLastId();

        $this->db->query('INSERT INTO product_to_store values ('.$inserted_product_id.',0);');

        return $inserted_product_id;
    }

    /**
     * Insert product description
     * 
     * @param array $variant
     * @param int $inserted_product_id
     */
    public function insertProductDescription($variant, $inserted_product_id)
    {
        $langs = $this->db->query('select language_id from language')->rows;

        foreach ($langs as $language) {
            $sql2 = '
				insert into product_description (
					product_id,
					language_id,
					name,
					description,
                    slug
				) values (
					' . $inserted_product_id . ',
					' . $language['language_id'] . ',
					"' . $this->db->escape($variant['product']['name']) . '",
					"' . $this->db->escape($variant['product']['name']) . '",
                    "' . $this->db->escape((new Slugify)->slug($variant['name'])) . '"
				);
			';

            $this->db->query($sql2);
        }
    }

    /**
     * Insert product into printful_to_products table
     *
     * @param array $variant
     * @param array $parent_product
     * @param int $inserted_product_id
     * @return void
     */
    public function insertPrintfulProduct($variant, $parent_product, $inserted_product_id)
    {
        $sql_u = '
            insert into  printful_to_products 
            values (
                null,
                ' . $this->db->escape($parent_product['id']) . ',
                ' . $this->db->escape($variant['variant_id']) . ',
                "' . $this->db->escape($variant['product']['image']) . '",
                0,
                ' . $this->db->escape($variant['retail_price']) . ',
                "' . $this->db->escape($variant['currency']) . '",
                NOW(),
                ' . $this->db->escape($inserted_product_id) . ',
                "' . $this->db->escape($variant['name']) . '"
            )
            ON DUPLICATE KEY UPDATE 
            retail_price = ' . $this->db->escape($variant['retail_price']) . ',
            name = "' . $this->db->escape($variant['name']) . '"
        ';

        $this->db->query($sql_u);
    }

    /**
     * Loop and insert all product images and link them to product
     *
     * @param array $variant
     * @param int $inserted_product_id
     * @return void
     */
    public function insertProductImages($variant, $inserted_product_id)
    {
        //Grab other variant images and link them
        $i = 0;
        foreach ($variant['files'] as $file) {
            $this->grab_image($file['preview_url'], 'image/data/products/' . $file['filename']);

            //Insert into product_images table
            $this->db->query('
                insert into `product_image` (`product_image_id`, `product_id`, `image`, `sort_order`) values (
                    null,
                    ' . $inserted_product_id . ',
                    "data/products/' . $this->db->escape($file['filename']) . '",
                    ' . $i . '
                )
            ');

            $i++;
        }
    }

    /**
     * Check if a product is imported already
     *
     * @param array $parent_product
     * @param array $variant
     * @return int|false
     */
    public function checkIfProductImported($parent_product,$variant)
    {
        $query = $this->db->query('SELECT * FROM `printful_to_products` WHERE product_id='.$this->db->escape($parent_product['id']).' AND variant_id='.$this->db->escape($variant['variant_id']));

        return $query->num_rows > 0 ? $query->row['expand_product_id'] : false; 
    }

    /**
     * Test method to guess the product options based on the variant name
     *
     * @param array $product
     * @param array $product_data
     * @return void
     */
    public function guessProductOptions($product,$product_data)
    {
        //Check if variants more than 1
        if($product['variants'] > 1){
            dump($product);
            dump($product_data);

            //Strip product name from all variants and guess options
            foreach($product_data['result']['sync_variants'] as $variant){
                $variant_options = str_replace($product['name'].' - ','',$variant['name']);

                foreach(explode('/',$variant_options) as $variant_option){
                    $variant_option = trim($variant_option);
                    
                    //Check if the option is size
                    //else, treat it as color
                    if(
                        in_array($variant_option,$this->clothes_sizes) || 
                        in_array($variant_option,$this->accessories_sizes) || 
                        preg_match('#[0-9\.]*[×xX][0-9\.]*#',$variant_option)
                    ){
                        dump('Size: '.$variant_option);
                    }else{
                        dump('Color: '.$variant_option);
                    }
                }
                dump($variant);
            }
            dd('finished');
        }
    }

    /**
     * Handle import process for all products
     * 
     * @param array $products List of structured products
     * @param bool $guess_options If set to true, the code will try to figure out what options are present for product and insert them in DB
     * @return void
     */
    public function importProducts(array $products=[], bool $guess_options=false): void
    {
        //Load the app settings
        $this->load->model('module/printful/settings');

        $printful_settings = $this->model_module_printful_settings->getSettings();
        //Import all products
        foreach($products as $product){
                /**
                 * Insert into product table
                 */
                //Grap image
                preg_match('#[a-zA-Z0-9_]*\.[a-zA-Z]{3,}$#', $product['thumbnail_url'], $matches);
                $this->grab_image($product['thumbnail_url'], 'image/data/products/' . $matches[0]);

                //Insert product
                $random_quantity = rand(350, 1000);
                $product['sku'] = isset($product['sku']) ? $product['sku'] : null;
                $sql = '
                    insert into product (
                        price,
                        image,
                        model,
                        sku,
                        location,
                        manufacturer_id,
                        quantity,
                        stock_status_id,
                        date_available,
                        weight_class_id,
                        tax_class_id,
                        pd_custom_price,
                        length_class_id,
                        sort_order,
                        status,
                        date_modified
                    ) values (
                        "' . $product['lowest_price'] . '",
                        "data/products/' . $matches[0] . '",
                        "' . $product['sku'] . '",
                        "' . $product['sku'] . '",
                        "",
                        0,
                        ' . $random_quantity . ',
                        1,
                        DATE(NOW()),
                        1,
                        0,
                        0,
                        1,
                        1,
                        1,
                        DATE(NOW())
                    )
                ';
    
                $this->db->query($sql);
    
                $inserted_product_id = $this->db->getLastId();
    
                /**
                 * Insert into product_to_store table
                 */
                $this->db->query('INSERT INTO product_to_store values ('.$inserted_product_id.',0);');
    
                /**
                 * Insert into product_description table
                 */
                $langs = $this->db->query('select language_id from language')->rows;
                $product['description'] = isset($product['description']) ? $product['description'] : null;
                foreach ($langs as $language) {
                    $sql2 = '
                        insert into product_description (
                            product_id,
                            language_id,
                            name,
                            description,
                            slug
                        ) values (
                            ' . $inserted_product_id . ',
                            ' . $language['language_id'] . ',
                            "' . $this->db->escape($product['name']) . '",
                            "' . $this->db->escape($product['description']) . '",
                            "' . $this->db->escape((new Slugify)->slug($product['name'])) . '"
                        );
                    ';
    
                    $this->db->query($sql2);
                }
    
                /**
                 * Insert into printful_to_product
                 */
                $sql_u = '
                    insert into  printful_to_products 
                    values (
                        null,
                        ' . $this->db->escape($product['id']) . ',
                        "",
                        "' . $this->db->escape($product['thumbnail_url']) . '",
                        0,
                        ' . $this->db->escape($product['lowest_price']) . ',
                        "USD",
                        NOW(),
                        ' . $this->db->escape($inserted_product_id) . ',
                        "' . $this->db->escape($product['name']) . '"
                    )
                    ON DUPLICATE KEY UPDATE 
                    retail_price = ' . $this->db->escape($product['lowest_price']) . ',
                    name = "' . $this->db->escape($product['name']) . '"
                ';

                /**
                 * Insert product category if it exists
                 */
                if($printful_settings['default_category']){
                    $sql_categoryies = "INSERT INTO `product_to_category` VALUES ({$inserted_product_id},{$printful_settings['default_category']})";

                    $this->db->query($sql_categoryies);
                }
    
                $this->db->query($sql_u);
                
                /**
                 * Insert product options
                 */
                if(isset($product['options'])){
                    foreach ($product['options'] as $option) {
                        //Insert image and link it to product
                        $i=0;
                        foreach($option['option_data']['files'] as $file){
                            $this->grab_image($file['preview_url'], 'image/data/products/' . $file['filename']);
    
                            //Insert into product_images table
                            if(!$this->db->query("select * from product_image where product_id={$inserted_product_id} and image='data/products/{$this->db->escape($file['filename'])}'")->row){
                                $this->db->query('
                                    insert into `product_image` values (
                                        null,
                                        ' . $inserted_product_id . ',
                                        "data/products/' . $this->db->escape($file['filename']) . '",
                                        ' . $i . '
                                    )
                                ');
                    
                                $i++;
                            }
                        }
    
                        if(!$guess_options){
                            //Insert option if not exists
                            $size_option = $this->db->query("SELECT * FROM `option_description` WHERE name='Option' OR name='option' LIMIT 1")->row;
        
                            if(!$size_option){
                                $this->db->query("INSERT INTO `option` (`option_id`,`type`,`sort_order`) VALUES(null,'select',0)");
        
                                $option_id = $this->db->getLastId();
        
                                $langs = $this->db->query('select language_id from language')->rows;
        
                                foreach ($langs as $language) {
                                    $this->db->query("INSERT INTO `option_description` VALUES ({$option_id},{$language['language_id']},'Option')");
                                }
                                
                                $size_option = $this->db->query("SELECT * FROM `option_description` WHERE name='Option' OR name='option' LIMIT 1")->row;
                            }
        
                            //Insert size_option to product_option
                            $product_option = $this->db->query("SELECT * FROM product_option WHERE product_id={$inserted_product_id} AND option_id={$size_option['option_id']} LIMIT 1")->row;
                            if(!$product_option){
                                $this->db->query("insert into product_option (`product_id`, `option_id`, `option_value`, `required`) values ('{$inserted_product_id}', '{$size_option['option_id']}', '', 1)");
                                $product_option_id = $this->db->getLastId();
                            }else{
                                $product_option_id = $product_option['product_option_id'];
                            }
        
                            //Insert option value if not exist
                            $size_option_value = $this->db->query("SELECT * FROM `option_value_description` WHERE name='{$option['option_value']}' LIMIT 1")->row;
        
                            if(!$size_option_value){
                                $this->db->query("INSERT INTO `option_value` (`option_value_id`,`option_id`,`image`,`sort_order`) VALUES(null,'{$size_option['option_id']}','',{$i})");
        
                                $option_value_id = $this->db->getLastId();
        
                                $langs = $this->db->query('select language_id from language')->rows;
                                
                                foreach ($langs as $language) {
                                    $this->db->query("INSERT INTO `option_value_description` VALUES ({$option_value_id},{$language['language_id']},{$size_option['option_id']},'{$option['option_value']}')");
                                }
        
                                $size_option_value = $this->db->query("SELECT * FROM `option_value_description` WHERE name='{$option['option_value']}' LIMIT 1")->row;
                            }
        
                            //Insert size_option_value into product_option_value
                            //Get option quantity
                            $option_quantity = floor($random_quantity / $product['variants']);
        
                            //Get option price difference
                            $option_price_difference = $option['option_data']['retail_price'] - $product['lowest_price'];
        
                            //Set price prefix
                            $price_prefix = $option_price_difference <= 0 ? '-' : '+';

                            $query_sql = [];
                            $query_sql[] = 'INSERT INTO product_option_value( `product_option_value_id`, `product_option_id`, `product_id`, `option_id`, `option_value_id`, `quantity`, `subtract`, `price`, `price_prefix`, `points`, `points_prefix`, `weight`, `weight_prefix`) VALUES (';
                            $query_sql[] = 'null,';
                            $query_sql[] = "{$product_option_id},";
                            $query_sql[] = "{$inserted_product_id},";
                            $query_sql[] = "{$size_option['option_id']},";
                            $query_sql[] = "{$size_option_value['option_value_id']},";
                            $query_sql[] = "{$option_quantity},";
                            $query_sql[] = "0,";
                            $query_sql[] = abs($option_price_difference).",";
                            $query_sql[] = "'{$price_prefix}',";
                            $query_sql[] = "0,";
                            $query_sql[] = "'+',";
                            $query_sql[] = "0,";
                            $query_sql[] = "'+'";
                            $query_sql[] = ")";
        
                            $this->db->query(implode('',$query_sql));
                        }else{
                            //Insert options with guessing
                            if($option['option_name'] == 'size'){
                                //Insert option if not exists
                                $size_option = $this->db->query("SELECT * FROM `option_description` WHERE name='Size' OR name='size' LIMIT 1")->row;
        
                                if(!$size_option){
                                    $this->db->query("INSERT INTO `option` (`option_id`,`type`,`sort_order`) VALUES(null,'select',0)");
        
                                    $option_id = $this->db->getLastId();
        
                                    $langs = $this->db->query('select language_id from language')->rows;
        
                                    foreach ($langs as $language) {
                                        $this->db->query("INSERT INTO `option_description` VALUES ({$option_id},{$language['language_id']},'Size')");
                                    }
                                    
                                    $size_option = $this->db->query("SELECT * FROM `option_description` WHERE name='Size' OR name='size' LIMIT 1")->row;
                                }
        
                                //Insert size_option to product_option
                                $product_option = $this->db->query("SELECT * FROM product_option WHERE product_id={$inserted_product_id} AND option_id={$size_option['option_id']} LIMIT 1")->row;
                                if(!$product_option){
                                    $this->db->query("insert into product_option (`product_id`, `option_id`, `option_value`, `required`) values ('{$inserted_product_id}', '{$size_option['option_id']}', '', 1)");
                                    $product_option_id = $this->db->getLastId();
                                }else{
                                    $product_option_id = $product_option['product_option_id'];
                                }
        
                                //Insert option value if not exist
                                $size_option_value = $this->db->query("SELECT * FROM `option_value_description` WHERE name='{$option['option_value']}' LIMIT 1")->row;
        
                                if(!$size_option_value){
                                    $this->db->query("INSERT INTO `option_value` (`option_value_id`,`option_id`,`image`,`sort_order`) VALUES(null,'{$size_option['option_id']}','',{$i})");
        
                                    $option_value_id = $this->db->getLastId();
        
                                    $langs = $this->db->query('select language_id from language')->rows;
                                    
                                    foreach ($langs as $language) {
                                        $this->db->query("INSERT INTO `option_value_description` VALUES ({$option_value_id},{$language['language_id']},{$size_option['option_id']},'{$option['option_value']}')");
                                    }
        
                                    $size_option_value = $this->db->query("SELECT * FROM `option_value_description` WHERE name='{$option['option_value']}' LIMIT 1")->row;
                                }
        
                                //Insert size_option_value into product_option_value
                                //Get option quantity
                                $option_quantity = floor($random_quantity / $product['variants']);
        
                                //Get option price difference
                                $option_price_difference = $option['option_data']['retail_price'] - $product['lowest_price'];
        
                                //Set price prefix
                                $price_prefix = $option_price_difference <= 0 ? '-' : '+';
        
                                $query_sql = [];
                                $query_sql[] = 'INSERT INTO product_option_value VALUES (';
                                $query_sql[] = 'null,';
                                $query_sql[] = "{$product_option_id},";
                                $query_sql[] = "{$inserted_product_id},";
                                $query_sql[] = "{$size_option['option_id']},";
                                $query_sql[] = "{$size_option_value['option_value_id']},";
                                $query_sql[] = "{$option_quantity},";
                                $query_sql[] = "0,";
                                $query_sql[] = abs($option_price_difference).",";
                                $query_sql[] = "'{$price_prefix}',";
                                $query_sql[] = "0,";
                                $query_sql[] = "'+',";
                                $query_sql[] = "0,";
                                $query_sql[] = "'+'";
                                $query_sql[] = ")";
        
                                $this->db->query(implode('',$query_sql));
                            }else{
                                //Insert option if not exists
                                $color_option = $this->db->query("SELECT * FROM `option_description` WHERE name='Color' OR name='color' LIMIT 1")->row;
        
                                if(!$color_option){
                                    $this->db->query("INSERT INTO `option` (`option_id`,`type`,`sort_order`) VALUES(null,'select',0)");
        
                                    $option_id = $this->db->getLastId();
        
                                    $langs = $this->db->query('select language_id from language')->rows;
        
                                    foreach ($langs as $language) {
                                        $this->db->query("INSERT INTO `option_description` VALUES ({$option_id},{$language['language_id']},'Color')");
                                    }
                                    
                                    $color_option = $this->db->query("SELECT * FROM `option_description` WHERE name='Color' OR name='color' LIMIT 1")->row;
                                }
        
                                //Insert size_option to product_option
                                //Insert size_option to product_option
                                $product_option = $this->db->query("SELECT * FROM product_option WHERE product_id={$inserted_product_id} AND option_id={$color_option['option_id']} LIMIT 1")->row;
                                if(!$product_option){
                                    $this->db->query("insert into product_option (`product_id`, `option_id`, `option_value`, `required`) values ('{$inserted_product_id}', '{$size_option['option_id']}', '', 1)");
                                    $product_option_id = $this->db->getLastId();
                                }else{
                                    $product_option_id = $product_option['product_option_id'];
                                }
        
                                //Insert option value if not exists
                                $color_option_value = $this->db->query("SELECT * FROM `option_value_description` WHERE name='{$option['option_value']}' LIMIT 1")->row;
        
                                if(!$color_option_value){
                                    $this->db->query("INSERT INTO `option_value` VALUES(null,'{$color_option['option_id']}','',{$i},null,0)");
        
                                    $option_value_id = $this->db->getLastId();
        
                                    $langs = $this->db->query('select language_id from language')->rows;
                                    
                                    foreach ($langs as $language) {
                                        $this->db->query("INSERT INTO `option_value_description` VALUES ({$option_value_id},{$language['language_id']},{$option_id},'{$option['option_value']}')");
                                    }
        
                                    $color_option_value = $this->db->query("SELECT * FROM `option_value_description` WHERE name='{$option['option_value']}' LIMIT 1")->row;
                                }
        
                                //Insert size_option_value into product_option_value
                                //Get option quantity
                                $option_quantity = floor($random_quantity / $product['variants']);
        
                                //Get option price difference
                                $option_price_difference =  $option['option_data']['retail_price'] - $product['lowest_price'];
        
                                //Set price prefix
                                $price_prefix = $option_price_difference <= 0 ? '-' : '+';
        
                                $query_sql = [];
                                $query_sql[] = 'INSERT INTO product_option_value VALUES (';
                                $query_sql[] = 'null,';
                                $query_sql[] = "{$product_option_id},";
                                $query_sql[] = "{$inserted_product_id},";
                                $query_sql[] = "{$color_option['option_id']},";
                                $query_sql[] = "{$color_option_value['option_value_id']},";
                                $query_sql[] = "{$option_quantity},";
                                $query_sql[] = "0,";
                                $query_sql[] = abs($option_price_difference).",";
                                $query_sql[] = "'{$price_prefix}',";
                                $query_sql[] = "0,";
                                $query_sql[] = "'+',";
                                $query_sql[] = "0,";
                                $query_sql[] = "'+'";
                                $query_sql[] = ")";
        
                                $this->db->query(implode('',$query_sql));
                            }
                        }
    
                        $i++;
                    }
                }
            
        }
    }

    /**
     * Delete from printful_to_products by expand_product_id
     * to remove printful products when product get deleted from store
     *
     * @param array $ids
     *
     * @return bool
     */
    public function deletePrintfulProductByEspandProductIds(array $ids): bool
    {
        if (count($ids) < 1) {
            return false;
        }

        $ids = array_filter(array_map(function ($v) {
            if (preg_match('#^[0-9]+$#', $v)) {
                return $v;
            }
        }, $ids));

        $query = 'DELETE FROM printful_to_products WHERE expand_product_id IN (%s)';

        try {
            $this->db->query(sprintf($query, implode(', ', $ids)));

            return true;
        } catch (\Exception $e) {
            return false;
        }

        return false;
    }

    public function getPrintfulProduct($product_id)
    {
        return $this->db->query("SELECT * FROM `printful_to_products` WHERE product_id=".$this->db->escape($product_id)." LIMIT 1");
    }
}
