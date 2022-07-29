<?php

namespace Api\Models;

use ExpandCart\Foundation\String\Slugify;
use Psr\Container\ContainerInterface;

class Product
{
    private $load;
    private $registry;
    private $languagecodes;
    private $languageids;
    private $config;
    private $db;

    public function __construct(ContainerInterface $container)
    {
        $this->registry = $container['registry'];
        $this->config = $container['config'];
        $this->load = $container['loader'];
        $this->languagecodes = $container['languagecodes'];
        $this->languageids = $container['languageids'];
        $this->db = $container['db'];
        $this->currency = $container['currency'];
    }

    public function getAll()
    {
        $this->load->model('catalog/product');
        $products = $this->registry->get('model_catalog_product')->getProducts();
        return $products;
    }
	
	public function getProductsToFilter($data = [], $filterData = [], $pos = false)
    {
        $this->load->model('catalog/product');
        $products = $this->registry->get('model_catalog_product')->getProductsToFilter($data,$filterData, $pos);
        return $products;
    }

    public function getById($id)
    {
        $this->load->model('catalog/product');

        $product = $this->registry->get('model_catalog_product')->getProduct($id);
        if ($product) {
            if ($product['image'] && strpos($product['image'], 'no_image.jpg') === false)
                $product['image'] = \Filesystem::getUrl('image/'.$product['image']);
            else
                $product['image'] = \Filesystem::getUrl('image/no_image.jpg');
            $product['product_description'] = $this->prepareProductDescriptionGet($this->registry->get('model_catalog_product')->getProductDescriptions($id));
            // Get Product Categories
            $product['product_category'] = array();
            $categories = $this->registry->get('model_catalog_product')->getProductCategories($id);
            if ($categories)
            {
                $product['product_category'] = $categories;
            }
            // Get Related Products
            $product['product_related'] = array();
            $related_products = $this->registry->get('model_catalog_product')->getProductRelated($id, true);
            if ($related_products)
            {
                foreach ($related_products as $key => $related_product)
                {
                    $product['product_related'][$key]['product_id'] = $related_product['product_id'];
                    $product['product_related'][$key]['name'] = $related_product['name'];
                    $product['product_related'][$key]['price'] = $related_product['price'];
                    $product['product_related'][$key]['quantity'] = $related_product['quantity'];
                    $product['product_related'][$key]['image'] = \Filesystem::getUrl($related_product['image']);
                }
            }
            // Get Product Discount
            $product['product_discount'] = array();
            $product_discounts = $this->registry->get('model_catalog_product')->getProductDiscounts($id);
            if ($product_discounts)
            {
                $product['product_discount'] = $product_discounts;
            }
            return $product;
        } else
            return null;
    }

    public function getByNameOrSku($value)
    {
        $this->load->model('catalog/product');
        
        //get product by sku
        $product = $this->registry->get('model_catalog_product')->getProductBySku($value)[0];
        
        
        //get product_id by name if not exists with sku
        if(!$product){
            
            $product_description = $this->registry->get('model_catalog_product')->getProductByName($value);
            if($product_description)
                $product = $this->registry->get('model_catalog_product')->getProduct($product_description['product_id']);
        }

        if ($product) {
             $product['product_description'] = $this->prepareProductDescriptionGet($this->registry->get('model_catalog_product')->getProductDescriptions($product['product_id']));
            return $product;
        }
          
       return null;
    }

    public function getDropnaProductById($id)
    {
        $this->load->model('catalog/product');

        $productDropna = $this->registry->get('model_catalog_product')->getDropnaProductById($id);
        if ($productDropna) {
            return $productDropna;
        } else return null;
    }

    public function getDropnaProByDropnaId($id)
    {
        $this->load->model('catalog/product');

        $productDropna = $this->registry->get('model_catalog_product')->getDropnaProduct($id);
        if ($productDropna) {
            return $productDropna;
        } else return null;
    }

    //Run Dropna product schedule
    public function productsSchedule(){
        $this->load->model('catalog/product');
        return $this->registry->get('model_catalog_product')->dropnaRunScheduleProduct(true);
    }

    //Mapp Dropna Products
    public function productsMapp($mappData){
        $this->load->model('catalog/product');
        return $this->registry->get('model_catalog_product')->dropnaProductsMapp($mappData);
    }

    public function createProduct($data)
    {
        //Dropna
        if($data['dropna_product_id']){
            $dropna_product_id = $data['dropna_product_id'];
            unset($data['dropna_product_id']);

            //Prevent adding product categories before checking
            $dropna_product_category = isset($data['product_category']) ? $data['product_category'] : null;
            unset($data['product_category']);

            //Prevent adding product options before checking
            $dropna_product_option = isset($data['product_option']) ? $data['product_option'] : null;
            unset($data['product_option']);

            $dropna_user_id = $data['dropna_user_id'];
            unset($data['dropna_user_id']);

            ////////////////////////Get Dollar Rate
            $dropna_settings = $this->config->get('dropna');
            $dollar_rate = 1;

            if($dropna_settings['auto_dollar_rate']){
                //// Get Dollar rate from API
                $dollar_rate = $this->currency->gatUSDRate($this->config->get('config_currency'));
                ////////////////////////////
            }else if($dropna_settings['dollar_rate']){
                $dollar_rate = $dropna_settings['dollar_rate'];
            }

            $data['price'] = $data['price'] / $dollar_rate;
            ///////////////////
        }
        
        ///////
        $this->load->model('catalog/product');
        $newProductDescription = $this->prepareProductDescriptionPost($data["product_description"]);
        $data["product_description"] = $newProductDescription;
        $this->ApplyDefaultValues($data);

        return $this->registry->get('model_catalog_product')->addProduct($data);
        $product_id = $this->registry->get('model_catalog_product')->addProduct($data);

        if ($product_id > 0) {
            //Dropna

            if($dropna_product_id){
                $dataDropna = array(
                                    'store_product_id' => $product_id, 
                                    'dropna_product_id' => $dropna_product_id,
                                    'dropna_user_id' => $dropna_user_id
                                    );
                
                $this->registry->get('model_catalog_product')->addDropnaProduct($dataDropna);
                
                ///######### Handle Categories #############
                if($dropna_product_category)
                    $this->addProductCatRelation($dropna_product_category, $product_id);

                ///######### Handle Options #############
                if($dropna_product_option)
                    $this->addProductOptRelation($dropna_product_option, $product_id);
            }
            ///////

            $product = $this->registry->get('model_catalog_product')->getProduct($product_id);
            $product['product_description'] = $this->prepareProductDescriptionGet(
                $this->registry->get('model_catalog_product')->getProductDescriptions($product_id)
            );
        }

        return $product ? $product : null;
    }

    //// Manage Product Categories
    private function addProductCatRelation($categories, $product_id)
    {
            /////////////////////////////
            //// Save Product Categories
            /////////////////////////////
            foreach ($categories as $category) {
                
                if(!isset($category['category_description']))
                    continue;

                // Check if category exists (match category language names)
                $category_id = 0;
                foreach ($category['category_description'] as $lang_id => $description) {
                   $catSelectQuery[] = "SELECT category_id FROM " . DB_PREFIX . "category_description WHERE name = '" . $description['name'] . "'";
                   //To prevent confusion with other language values match
                   if(isset($category_id) && $category_id > 0)
                        $catSelectQuery[] = "AND category_id = '" . (int)$category_id . "'";
                   $catSelectQuery[] = "limit 1";

                   $cat_query = $this->db->query(implode(' ', $catSelectQuery));

                   if($cat_query->num_rows == 0){
                      break;  
                   }
                   $category_id = $cat_query->rows[0]['category_id'];
                   $catSelectQuery = [];
                }

                //if category not exists
                if(!$category_id){
                // Add new category

                    //check if parent_id exists as dropna_category_id
                    $parent_id = $category['parent_id'];
                    if($parent_id){
                        $new_parent = $this->db->query("SELECT category_id FROM " . DB_PREFIX . "category_to_dropna WHERE dropna_category_id = '" . $category['parent_id'] . "' limit 1");
                        
                        if($new_parent->num_rows)
                            $parent_id = $new_parent->rows[0]['category_id'];
                        else
                            $parent_id = 0;
                    }
                    ////////////////////////////////////
                    $this->load->model('catalog/category');

                    $category_data['parent_id'] = $parent_id;
                    $category_data['top'] = $category['top'];
                    $category_data['column'] = $category['column'];
                    $category_data['sort_order'] = $category['sort_order'];
                    $category_data['status'] = $category['status'];
                    $category_data['image'] = isset($category['image']) ? $category['image'] : '';
                    $category_data['category_description'] = $category['category_description'];

                    $category_id = $this->registry->get('model_catalog_category')->addCategory($category_data);

                    //Add dropna category mapping
                    if($category_id){
                        $dataDropna = array('category_id' => $category_id, 'dropna_category_id' => $category['id']);
                        $this->registry->get('model_catalog_category')->addDropnaCategory($dataDropna);
                    }

                    // if($category['image']){
                    //     $image = $category['image'];
                    //     $imagInfo   = pathinfo($image);
                    //     $imgContent = file_get_contents($image);

                    //     $path = 'public/categories/'.$newCatId . '_' . uniqid() . '.' . $imagInfo['extension'];
                    //     Storage::put($path, $imgContent);
                    //     Storage::setVisibility($path, 'public');
                    //     $new_category->update(['image' => $path]);
                    //     $new_category->save();
                    // }
                }   

                if($category_id)
                    $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$category_id . "'");
            }
    }

    //// Manage Product Options
    private function addProductOptRelation($options, $product_id)
    {
        //return $options;
        //////////////////////////////////////////
        //// Save Product Options
        //////////////////////////////////////////

        $this->load->model('catalog/option');
        
        foreach ($options as $option) {
            // Check if option exists (match option language names), get option_id
            $option_id = 0;
            foreach ($option['option_name'] as $lang_id => $description) {
               $optSelectQuery[] = "SELECT option_id FROM " . DB_PREFIX . "option_description WHERE name = '" . $description['name'] . "'";
               //To prevent confusion with other language values match
               if(isset($option_id) && $option_id > 0)
                    $optSelectQuery[] = "AND option_id = '" . (int)$option_id . "'";
               $optSelectQuery[] = "limit 1";

               $opt_query = $this->db->query(implode(' ', $optSelectQuery));

               if($opt_query->num_rows == 0){
                  break;  
               }
               $option_id = $opt_query->rows[0]['option_id'];
               $optSelectQuery = [];
            }

            $new_option = false;
            if(!$option_id){
                // Insert as new Option
                
                $option_data['type'] = $option['option_type'];
                $option_data['sort_order'] = 0;
                $option_data['option_description'] = $option['option_name'];

                $option_id  = $this->registry->get('model_catalog_option')->addOption($option_data);
                
                // will be used in manage insertion of option values
                if($option_id)
                    $new_option = true;
            }

            // Insert Product Option
            if($option_id){
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '" . (int)$option_id . "', option_value = '" . $option['option_value'] . "', required = '" . $option['required'] . "'");

                $product_option_id = $this->db->getLastId();
            }
            //////////////////////////////////////////

            //option values
            foreach ($option['product_option_value'] as $op_value) {

                // if not new option added, Check if option value exists (match option value language names), get option_value_id
                $option_value_id = 0;
                if(!$new_option){
                    foreach ($op_value['option_value_name'] as $lang_id => $description) {
                       $optValSelectQuery[] = "SELECT option_value_id FROM " . DB_PREFIX . "option_value_description WHERE name = '" . $description['name'] . "' AND option_id = '" . (int)$option_id . "'";
                       //To prevent confusion with other language values match
                       if(isset($option_value_id) && $option_value_id > 0)
                            $optValSelectQuery[] = "AND option_value_id = '" . (int)$option_value_id . "'";
                       $optValSelectQuery[] = "limit 1";

                       $opt_value_query = $this->db->query(implode(' ', $optValSelectQuery));

                       if($opt_value_query->num_rows == 0){
                          break;  
                       }
                       $option_value_id = $opt_value_query->rows[0]['option_value_id'];
                       $optValSelectQuery = [];
                    }
                }

                // Insert new option value in case of 1- new option inserted above 2- no new option inserted and option_value_id = 0
                if($new_option || !$option_value_id){
                    // Insert new Option Value
                    $this->load->model('catalog/option');

                    $option_value_data['option_value'] = [ array('image' => '', 'option_value_description' => $op_value['option_value_name'])];
                    $option_value_data['option_id'] = $option_id;
                    $option_value_data['single_option_value'] = 1;

                    $option_value_id  = $this->registry->get('model_catalog_option')->addOptionValues($option_value_data);
                }
                
                // Insert Product Option Value
                if($option_value_id && $product_option_id && $option_id)
                {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET product_option_id = '" . (int)$product_option_id . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$option_id . "', option_value_id = '" . (int)$option_value_id . "', quantity = '" . (int)$op_value['quantity'] . "', subtract = '" . (int)$op_value['subtract'] . "', price = '" . (float)$op_value['price'] . "', price_prefix = '" . $this->db->escape($op_value['price_prefix']) . "', points = '" . (int)$op_value['points'] . "', points_prefix = '" . $this->db->escape($op_value['points_prefix']) . "', weight = '" . (float)$op_value['weight'] . "', weight_prefix = '" . $this->db->escape($op_value['weight_prefix']) . "'");

                    $product_option_val_id = $this->db->getLastId();

                    //Add dropna product option value mapping
                    if($product_option_val_id){
                        $dataDropna = array('product_option_value_id' => $product_option_val_id, 'dropna_pr_op_val_id' => $op_value['product_option_value_id']);
                        $this->registry->get('model_catalog_option')->addDropnaPrOptVal($dataDropna);
                    }
                }
                //////////////////////////////////////////
            }                  
        }        
        //////////////////////////////////////////
        //////////////////////////////////////////
        //////////////////////////////////////////
    }
	
	//get related products 
	public function getProductRelated ($id){
		$this->load->model('catalog/product');

        $products = $this->registry->get('model_catalog_product')->getProductRelated($id);
        if ($products) {
            return $products;
        } 

		return array();
	}

	//get product options 
	public function getProductOptions ($id){
		$this->load->model('catalog/product');

        $product_options = $this->registry->get('model_catalog_product')->getProductOptionsWithDescription($id);
        if ($product_options) {
            return $product_options;
        } 

		return array();
	}


    /**
     * @param $id
     * @param $data
     * @return bool
     */
    public function updateProductOptions($id, $data)
    {
        // Delete product options && Product options values
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . (int)$id . "'");

        // Insert new array of options
        if (!empty($data['product_options']))
        {
            foreach ($data['product_options'] as $product_option)
            {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET  product_id = '" . (int)$id . "',option_id = '" . (int)$product_option['option_id'] . "',required = '" . $product_option['required'] . "'");

                $product_option_id = $this->db->getLastId();

                if (!empty($product_option['product_option_value']))
                {
                    foreach ($product_option['product_option_value'] as $product_option_value)
                    {
                        if (!empty($product_option_value['product_option_value_id']))
                        {
                            $product_option_value['price_prefix'] = '+';
                            if ((int)$product_option_value['price'] < 0) {
                                $product_option_value['price_prefix'] = '-';
                                $product_option_value['price'] *= -1;
                            }

                            $product_option_value['weight_prefix'] = '+';
                            if ((int)$product_option_value['weight'] < 0) {
                                $product_option_value['weight_prefix'] = '-';
                                $product_option_value['weight'] *= -1;
                            }

                            // Insert Product Option Values
                            $insertQuery = $insertFields = [];
                            $insertQuery[] = "INSERT INTO " . DB_PREFIX . "product_option_value SET";
                            $insertFields[] = "product_option_value_id = '" . (int)$product_option_value['product_option_value_id'] . "'";
                            $insertFields[] = "option_value_id = '" . (int)$product_option_value['option_value_id'] . "'";
                            $insertFields[] = "product_id = '" . (int)$id . "'";
                            $insertFields[] = "product_option_id = '" . (int)$product_option_id . "'";
                            $insertFields[] = "option_id = '" . (int)$product_option['option_id'] . "'";
                            $insertFields[] = "quantity = '" . (int)$product_option_value['quantity'] . "'";
                            $insertFields[] = "subtract = '" . 0 . "'";
                            $insertFields[] = "price = '" . (float)$product_option_value['price'] . "'";
                            $insertFields[] = "price_prefix = '" . $product_option_value['price_prefix'] . "'";
                            $insertFields[] = "weight = '" . (float)$product_option_value['weight'] . "'";
                            $insertFields[] = "weight_prefix = '" . $this->db->escape($product_option_value['weight_prefix']) . "'";
                            $insertFields[] = "sort_order = '" . (float)$product_option_value['sort_order'] . "'";

                            $insertQuery[] = implode(', ', $insertFields);

                            $this->db->query(implode(' ', $insertQuery));
                        }
                    }
                }
            }
        }
        return true;
    }

    public function updateProductOptionValues($product_option_id, $product_id, $option_values, $parameters)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . (int)$product_id . "' AND product_option_id = '" . (int)$product_option_id . "'");
        if (isset($option_values))
        {
            $this->db->query("UPDATE `" . DB_PREFIX . "product_option` SET required = '" . $this->db->escape($parameters['required']) . "' WHERE product_option_id  = '" . (int)$product_option_id . "'");
            foreach ($option_values as $option_value)
            {
                $option_value['price_prefix'] = '+';
                if ((int)$option_value['price'] < 0) {
                    $option_value['price_prefix'] = '-';
                    $option_value['price'] *= -1;
                }

                $option_value['weight_prefix'] = '+';
                if ((int)$option_value['weight'] < 0) {
                    $option_value['weight_prefix'] = '-';
                    $option_value['weight'] *= -1;
                }


                $insertQuery = $insertFields = [];
                $insertQuery[] = "INSERT INTO " . DB_PREFIX . "product_option_value SET";
                $insertFields[] = "product_id = '" . (int)$product_id . "'";
                $insertFields[] = "product_option_id = '" . (int)$product_option_id . "'";
                $insertFields[] = "option_id = '" . (int)$parameters['option_id'] . "'";
                $insertFields[] = "quantity = '" . (int)$option_value['quantity'] . "'";
                $insertFields[] = "subtract = '" . 0 . "'";
                $insertFields[] = "price = '" . (float)$option_value['price'] . "'";
                $insertFields[] = "price_prefix = '" . $option_value['price_prefix'] . "'";
                $insertFields[] = "weight = '" . (float)$option_value['weight'] . "'";
                $insertFields[] = "weight_prefix = '" . $this->db->escape($option_value['weight_prefix']) . "'";
                $insertFields[] = "sort_order = '" . (float)$option_value['sort_order'] . "'";
                if (!empty($option_value['product_option_value_id']))
                {
                    $insertFields[] = "product_option_value_id = '" . (int)$option_value['product_option_value_id'] . "'";
                }

                if (!empty($option_value['option_value_id']))
                {
                    $insertFields[] = "option_value_id = '" . (int)$option_value['option_value_id'] . "'";
                }
                else
                {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "option_value SET option_id = '" . (int)$parameters['option_id'] . "', image = '" . "no_image.jpg" . "', sort_order = '" . (int)$option_value['sort_order'] . "'");

                    $option_value_id = $this->db->getLastId();

                    foreach ($option_value['option_value_description'] as $option_value_description) {
                        $this->db->query("INSERT INTO " . DB_PREFIX . "option_value_description SET option_value_id = '" . (int)$option_value_id . "', language_id = '" . (int)$option_value_description['language_id'] . "', option_id = '" . (int)$parameters['option_id'] . "', name = '" . $this->db->escape($option_value_description['name']) . "'");
                    }
                    $insertFields[] = "option_value_id = '" . (int)$option_value_id . "'";
                }


                $insertQuery[] = implode(', ', $insertFields);

                $this->db->query(implode(' ', $insertQuery));
            }
        }
        return true;
    }

	//get product options 
	public function getProductDiscounts ($id){
		$this->load->model('catalog/product');

        $product_discounts = $this->registry->get('model_catalog_product')->getProductDiscounts($id);
        if ($product_discounts) {
            return $product_discounts;
        } 

		return array();
	}
	
    public function updateProduct($id, $data)
    {
        $retproduct = null;

        $this->load->model('catalog/product');
        $product = $this->registry->get('model_catalog_product')->getProduct($id);
        $product_description = $this->registry->get('model_catalog_product')->getProductDescriptions($id);

        if ($product) {
            foreach ($data as $key => $value) {
                $product[$key] = $value;
            }
            if (isset($data['product_description'])) {
                $dataPD = $this->prepareProductDescriptionPost($data['product_description']);
                foreach ($dataPD as $pdkey => $pdvalue) {
                    foreach ($pdvalue as $key => $value) {
                        $product_description[$pdkey][$key] = $value;
                    }
                }
            }
            $product['product_description'] = $product_description;

            $this->registry->get('model_catalog_product')->editProduct($id, $product);


            $retproduct = $this->registry->get('model_catalog_product')->getProduct($id);
            $retproduct['product_description'] = $this->prepareProductDescriptionGet($this->registry->get('model_catalog_product')->getProductDescriptions($id));
        }

        return $retproduct ? $retproduct : null;
    }

	public function autocomplete($data,$customerGroup=null){
		
            $this->load->model('catalog/product');
            $this->load->model('catalog/option');

            
            $results = $this->registry->get('model_catalog_product')->getProducts($data);
			
			$final_data = [];
            foreach ($results as $result) {
                $option_data = array();

                $product_options = $this->registry->get('model_catalog_product')->getProductOptions($result['product_id']);

                // Get Product special prices                 
                // $product_specials = $this->registry->get('model_catalog_product')->getProductSpecials($result['product_id']);
                $product_specials = $this->registry->get('model_catalog_product')->getProductDiscountsAndSpecials([
                    'product_id' => $result['product_id'],
                    'customer_group_id' => $customerGroup,
                ]);
                
                foreach ($product_options as $product_option) {
                    $option_info = $this->registry->get('model_catalog_option')->getOption($product_option['option_id']);

                    if ($option_info) {
                        if (
                            $option_info['type'] == 'select' ||
                            $option_info['type'] == 'radio' ||
                            $option_info['type'] == 'checkbox' ||
                            $option_info['type'] == 'image' ||
                            $option_info['type'] == 'product'
                        ) {
                            $option_value_data = array();

                            foreach ($product_option['product_option_value'] as $product_option_value) {
                                $option_value_info = $this->registry->get('model_catalog_option')->getOptionValue(
                                    $product_option_value['option_value_id']
                                );
								
                                if ($option_value_info) {
													
                                    $price = (
                                    (float)$product_option_value['price'] ?
                                        $this->currency->format(
                                            $product_option_value['price'],
                                            $this->config->get('config_currency')
                                        ) :
                                        false
                                    );

                                    $option_value_data[] = array(
                                        'product_option_value_id' => $product_option_value['product_option_value_id'],
                                        'option_value_id' => $product_option_value['option_value_id'],
                                        'name' => $option_value_info['name'],
                                        'price' => $price,
                                        'price_prefix' => $product_option_value['price_prefix'],
                                        'quantity' =>$product_option_value['quantity']
                                    );
                                }
                            }

                            $option_data[] = array(
                                'product_option_id' => $product_option['product_option_id'],
                                'option_id' => $product_option['option_id'],
                                'name' => $option_info['name'],
                                'type' => $option_info['type'],
                                'option_value' => $option_value_data,
                                'required' => $product_option['required']
                            );
                        } else {
                            $option_data[] = array(
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
				
                $result['discount_price'] = 0;
                $result['discount_quantity'] = 0;
				
                if(is_array($product_specials) && count($product_specials) > 0){
                    foreach ($product_specials as $special_price) {

                        $date_start = $special_price['date_start'];
                        $date_end = $special_price['date_end'];
                        
                        if(
                            ($date_start == null || !$date_start || $date_start == "0000-00-00") &&
                            ($date_end == null || !$date_end || $date_end == "0000-00-00")
                        ) {
                            if ($special_price['type'] == 'discount') {
                                $result['discount_price'] = $special_price['price'];
                                $result['discount_quantity'] = $special_price['quantity'];
                                break;
                            } else if ($special_price['type'] == 'special') {
                                $result['price'] = $special_price['price'];
                                $result['discount_quantity'] = 1;
                                break;
                            }
                        } else {
                            if ($special_price['date_end'] >= date("Y-m-d",time())) {
                                if ($special_price['type'] == 'discount') {
                                    $result['discount_price'] = $special_price['price'];
                                    $result['discount_quantity'] = $special_price['quantity'];
                                    break;
                                } else if ($special_price['type'] == 'special') {
                                    $result['price'] = $special_price['price'];
                                    $result['discount_quantity'] = 1;
                                    break;
                                }
                            }
                        }
                    }
                }

                $final_data[] = array(
                    'product_id' => $result['product_id'],
                    'name' => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
                    'model' => $result['model'],
                    'option' => $option_data,
                    'price' => $result['price'],
                    'discount_price' => $result['discount_price'],
                    'discount_quantity' => $result['discount_quantity'],
                    'total' => $result['price'],
                    'image' => $result['image'],
                    'quantity' => $result['quantity']
                );
        }		
			
		return $final_data;
	}
    
	public function getProductImages($product_id){
		
            $this->load->model('catalog/product');
            $results = $this->registry->get('model_catalog_product')->getProductImages($product_id);
			
		return $results;
	}
    
	//Update Product Quantity
    public function api_model_updateProductValue($id, $updateData){
        $this->load->model('catalog/product');
        return $this->registry->get('model_catalog_product')->updateProductValue($id, $updateData);
    }


    //Update Product Attributes
    public function api_model_updateProductAttributes($id,$updateData){
        $this->load->model('catalog/product');
        
        return $this->registry->get('model_catalog_product')->updateProductAttributes($id,$updateData); 
        
}


    public function api_model_updateProductInventory($id,$data){
        $this->load->model('catalog/product');
        return $this->registry->get('model_catalog_product')->updateProductInventory($id, $data);
}
        //Update Product MultipleValues
    public function api_model_updateProductdescriptionMultipleValues($id,$updateData){
        
        $this->load->model('catalog/product');
        $this->registry->get('model_catalog_product')->UpdateSeo($id,$updateData);
        return true;
    }

    public function deleteProduct($id)
    {
        $this->load->model('catalog/product');
        $this->registry->get('model_catalog_product')->deleteProduct($id);

        return true;
    }


        //Update Product Shipping
        public function api_model_updateProductShipping($id,$updateData){
            $this->load->model('catalog/product');
            return $this->registry->get('model_catalog_product')->UpdateProductShipping($id,$updateData);
        }

    public function api_model_api_model_updateProductDiscount($data){
        $this->load->model('catalog/product');
        $this->registry->get('model_catalog_product')->updateProductDiscount($data);
        return true;
    }

    /**
     * @param $id
     * @param $data
     * @return bool
     */
    public function addProductDiscount($id, $data)
    {
        $this->load->model('catalog/product');
        $this->registry->get('model_catalog_product')->addProductDiscount($id,$data);
        return true;
    }

    /**
     * @param $id
     * @return bool
     */
    public function deleteProductDiscount($id)
    {
        $this->load->model('catalog/product');
        $this->registry->get('model_catalog_product')->deleteProductDiscount($id);
        return true;
    }


    public function api_model_api_model_addproductimages($id,$uploaded_images){
        $this->load->model('catalog/product');
        $this->registry->get('model_catalog_product')->AddProductImages($id,$uploaded_images);
        return true;
    }





    public function api_model_api_model_updateproductimages($id,$data){
        $this->load->model('catalog/product');
        $this->registry->get('model_catalog_product')->UpdateProductImages($id,$data);
        return true;
    }

    public function getProductMainImage($id)
    {
        $this->load->model('catalog/product');
        return $this->registry->get('model_catalog_product')->getProductMainImage($id);
    }

    private function ApplyDefaultValues(&$data)
    {
        $data["affiliate_link"] = isset($data["affiliate_link"]) ? $data["affiliate_link"] : "";
        $data["sku"] = isset($data["sku"]) ? $data["sku"] : "";
        $data["upc"] = isset($data["upc"]) ? $data["upc"] : "";
        $data["ean"] = isset($data["ean"]) ? $data["ean"] : "";
        $data["jan"] = isset($data["jan"]) ? $data["jan"] : "";
        $data["isbn"] = isset($data["isbn"]) ? $data["isbn"] : "";
        $data["mpn"] = isset($data["mpn"]) ? $data["mpn"] : "";
        $data["location"] = isset($data["location"]) ? $data["location"] : "";
        $data["product_store"] = (isset($data["product_store"]) && $data["product_store"]) ? $data["product_store"] : array(0);
        $data["keyword"] = isset($data["keyword"]) ? $data["keyword"] : "";
        $data["image"] = isset($data["image"]) ? $data["image"] : "image/no_image.jpg";
        $data["shipping"] = isset($data["shipping"]) ? $data["shipping"] : 1;
        $data["transaction_type"] = isset($data["transaction_type"]) ? $data["transaction_type"] : "";
        $data["price"] = isset($data["price"]) ? $data["price"] : "";
        $data["cost_price"] = isset($data["cost_price"]) ? $data["cost_price"] : "";
        $data["tax_class_id"] = isset($data["tax_class_id"]) ? $data["tax_class_id"] : 0;
        $data["date_available"] = (isset($data["date_available"]) && $data["date_available"]) ? $data["date_available"] : date('Y-m-d', time() - 86400);
        $data["quantity"] = isset($data["quantity"]) ? $data["quantity"] : 1;
        $data["minimum"] = isset($data["minimum"]) ? $data["minimum"] : 1;
        $data["subtract"] = isset($data["subtract"]) ? $data["subtract"] : 1;
        $data["sort_order"] = isset($data["sort_order"]) ? $data["sort_order"] : 1;
        $data["stock_status_id"] = isset($data["stock_status_id"]) ? $data["stock_status_id"] : $this->config->get('config_stock_status_id');
        $data["status"] = isset($data["status"]) ? $data["status"] : 1;
        $data["weight"] = isset($data["weight"]) ? $data["weight"] : "";
        $data["weight_class_id"] = isset($data["weight_class_id"]) ? $data["weight_class_id"] : $this->config->get('config_weight_class_id');
        $data["length"] = isset($data["length"]) ? $data["length"] : "";
        $data["width"] = isset($data["width"]) ? $data["width"] : "";
        $data["height"] = isset($data["height"]) ? $data["height"] : "";
        $data["length_class_id"] = isset($data["length_class_id"]) ? $data["length_class_id"] : $this->config->get('config_length_class_id');
        $data["manufacturer_id"] = isset($data["manufacturer_id"]) ? $data["manufacturer_id"] : 0;
        $data["manufacturer"] = isset($data["manufacturer"]) ? $data["manufacturer"] : "";
        $data["points"] = isset($data["points"]) ? $data["points"] : "";

        foreach ($data['product_description'] as $key => $pd) {
            $data['product_description'][$key]["meta_keyword"] = isset($data['product_description'][$key]["meta_keyword"]) ? $data['product_description'][$key]["meta_keyword"] : "";
            $data['product_description'][$key]["meta_description"] = isset($data['product_description'][$key]["meta_description"]) ? $data['product_description'][$key]["meta_description"] : "";
            $data['product_description'][$key]["description"] = isset($data['product_description'][$key]["description"]) ? $data['product_description'][$key]["description"] : "";
            $data['product_description'][$key]["tag"] = isset($data['product_description'][$key]["tag"]) ? $data['product_description'][$key]["tag"] : "";
        }
    }

    //Update Product Shipping
    public function api_model_updateProductInfo($id,$updateData){
        $this->load->model('catalog/product');
        $product_updated =  $this->registry->get('model_catalog_product')->UpdateProductInfo($id,$updateData);
        if($product_updated){
            $upres = $this->registry->get('model_catalog_product')->UpdateProductNameAndDesc($updateData['product_id'],$updateData);
        }
        return $upres;
    }

            
    private function prepareProductDescriptionGet($data)
    {
        $newData = array();
        foreach ($data as $langId => $productDescription) {
            $newProductDescription = $productDescription;
            $langcode = $this->languageids["$langId"]['code'];
            $newProductDescription['language_id'] = "$langId";
            $newData[$langcode] = $newProductDescription;
        }

        return $newData;
    }

    public function api_model_updateProductLinking($id,$updateData){
        $this->load->model('catalog/product');
        return $this->registry->get('model_catalog_product')->UpdateProductLinking($id,$updateData);
    }

    private function prepareProductDescriptionPost($data)
    {
        $newData = array();
        foreach ($data as $langCode => $productDescription) {
            $langid = $this->languagecodes[$langCode]['language_id'];
            $newData["$langid"] = $productDescription;
        }

        return $newData;
    }

    /**
     * @return mixed
     */
    public function getTotalProductsCount()
    {
        $this->load->model('catalog/product');
        return $this->registry->get('model_catalog_product')->getTotalProductsCount();
    }

    /**
     * validate given inputs against some rules
     *
     * TODO this should be seperated library, write it from scratch or use a ready tool from packagiest
     *
     * @param array $inputs
     *
     * @return bool
     */
    public function validator($inputs, $rules)
    {

        $required = function ($value) {

            if (isset($value) && empty($value) === false) {
                return true;
            }

            return false;

        };

        $string = function ($value) {

            if (is_string($value)) {
                return true;
            }

            return false;
        };

        $int = $integer = $id = function ($value) {
            if (preg_match('#^\d+$#', $value)) {
                return true;
            }

            return false;
        };

        foreach ($rules as $key => $value) {
            $rules = explode('|', $value);
            foreach ($rules as $rule) {
                if (!$$rule($inputs[$key])) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * download given image url to data directory
     *
     * @param string $image
     * @param string $path
     * @param string $imageName
     *
     * @return string|bool
     */
    public function downloadImage($image, $path, $imageName)
    {
        if ($contents = file_get_contents($image)) {
            if (file_put_contents($path . $imageName, $contents)) {
                return 'data/api/' . $this->config->get('config_template') . '/' . $imageName;
            }
        }

        return null;
    }

    /**
     * creates a directory
     *
     * @param string $path
     *
     * @return string
     */
    public function createDirectory($path)
    {
        if (is_dir($path) === false) {
            mkdir($path, 0755, true);
        }

        return (substr($path, -1) === '/' ? $path : $path . '/');
    }

    /**
     * check if the given image is a valid image
     *
     * @param string $image
     *
     * @return bool
     */
    public function isImage($image)
    {
        if ($imageInfo = @getimagesize($image)) {

            $pathInfo = pathinfo($image);

            $mimeTypes = [
                'image/jpeg',
                'image/pjpeg',
                'image/png',
                'image/x-png',
                'image/gif'
            ];

            $extensions = array(
                'jpg',
                'jpeg',
                'gif',
                'png'
            );

            if (
                    in_array($imageInfo['mime'], $mimeTypes) &&
                    isset($pathInfo['extension']) &&
                    in_array($pathInfo['extension'], $extensions)
                )
                return true;
        }

        return false;
    }
}
