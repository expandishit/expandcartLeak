<?php
class ModelModuleKnawatDropshipping extends Model {
    private $route = 'extension/module/knawat_dropshipping';

    private $languages = array();
    private $default_language_id = 1;
    private $default_language = 'en';
    private $all_stores;
    private $is_admin = false;

    public function __construct($registry){
        parent::__construct($registry);
        $this->load->language($this->route);
        $this->load->model('localisation/language');

        if( false !== stripos( DIR_APPLICATION, 'admin' ) ){
            $this->is_admin = true;
        }

        $this->all_stores = $this->config->get('module_knawat_dropshipping_store');

        $this->languages = $this->model_localisation_language->getLanguages();
        $config_language = $this->config->get('config_admin_language');
        $language = explode( '-', $config_language );
        $this->default_language = $language[0];
        if( isset( $this->languages[$config_language] ) ){
            $this->default_language_id = (int)$this->languages[$config_language]['language_id'];
        }
    }

    public function install(){
        // create knawat metadata table into database.
        $query = $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."knawat_metadata` (
            `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `resource_id` bigint(20) unsigned NOT NULL DEFAULT '0',
            `resource_type` varchar(255) NULL,
            `meta_key` varchar(255) NULL,
            `meta_value` longtext NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
        $this->db->query("CREATE INDEX knawat_metadata_resource_id ON knawat_metadata(resource_id);");
       
       $this->db->query("CREATE TABLE `".DB_PREFIX."knawat_sync_skus` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `sku` varchar(32) NOT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $this->db->query("insert into `".DB_PREFIX."setting` VALUES (NULL, '0', 'knawat_dropshipping_sync', 'knawat_dropshipping_sync_offset_page', '0', '0');");
        $this->db->query("insert into `".DB_PREFIX."setting` VALUES (NULL, '0', 'knawat_dropshipping_sync', 'knawat_dropshipping_sync_deleted_count', '0', '0');");
        
        $result=$this->db->query("select `value` from `".DB_PREFIX."setting` where `group` = 'knawat_dropshipping_status' and `key` = 'status'");
    
        if($result->num_rows > 0)
            $this->db->query("update `".DB_PREFIX."setting` set `value`=1 where `group` = 'knawat_dropshipping_status' and `key` = 'status'");
        else
            $this->db->query("insert into `".DB_PREFIX."setting` VALUES (NULL, '0', 'knawat_dropshipping_status', 'status', '1', '0');");
        
        if (!\Extension::isInstalled('productsoptions_sku')) {
            $this->installProductoptionsSku();
        }
    }

    public function installProductoptionsSku() {
        $query = "CREATE TABLE IF NOT EXISTS `product_variations` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `product_id` int(11) NOT NULL,
            `option_value_ids` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
            `product_sku` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `product_barcode` VARCHAR(255) NULL DEFAULT NULL,
            `num_options` int(11) NOT NULL,
            `product_price` DOUBLE DEFAULT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

        $this->db->query($query);

        $showColumns = $this->db->query('SHOW COLUMNS FROM product_variations');

        $columns = array_flip(array_column($showColumns->rows, 'Field'));

        if (!isset($columns['product_quantity'])) {
            $this->db->query("ALTER TABLE `product_variations` ADD `product_quantity` INT(11) NOT NULL AFTER `product_sku`");
        }

        $this->db->query("INSERT INTO " . DB_PREFIX . "extension SET `type` = 'module', `code` = 'productsoptions_sku'");

        $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` (`store_id`, `group`, `key`, `value`, `serialized`) VALUES (0, 'productsoptions_sku', 'productsoptions_sku_status', 1, 0)");
        $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` (`store_id`, `group`, `key`, `value`, `serialized`) VALUES (0, 'productsoptions_sku', 'productsoptions_sku_relational_status', 0, 0)");
        $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` (`store_id`, `group`, `key`, `value`, `serialized`) VALUES (0, 'productsoptions_sku', 'productsoptions_sku_option_mapping', '', 0)");

    }

    public function uninstall(){
        // drop knawat metadata table from database.
        $this->db->query("DROP TABLE `".DB_PREFIX."knawat_metadata`");
        $this->db->query("DROP TABLE `".DB_PREFIX."knawat_sync_skus`");
        $this->db->query("update `".DB_PREFIX."setting` set `value`=0 where `group` = 'knawat_dropshipping_status' and `key` = 'status'");
    
        $productsoptions_sku_installed = $this->db->query("SELECT * FROM " . DB_PREFIX . "appservice INNER JOIN storeappservice ON appservice.id=" . DB_PREFIX . "storeappservice.appserviceid where appservice.name='productsoptions_sku'")->row;
        if (!$productsoptions_sku_installed) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "extension WHERE `type` = 'module' AND `code` = 'productsoptions_sku'");
            $this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE `group` = 'productsoptions_sku'");
        }
    }

    /////////////////////////////////////////////////////////////
    ////////////////////// MetaData Functions ///////////////////
    /////////////////////////////////////////////////////////////

    /**
     * Get Custom data by id and resource.
     */
    public function get_knawat_meta( $resource_id, $meta_key,  $resource_type = 'product', $single = true ) {
        $get_sql = "SELECT * FROM `" . DB_PREFIX . "knawat_metadata` WHERE `resource_id` = ".(int)$resource_id." AND `meta_key` = '".$this->db->escape($meta_key)."' AND `resource_type` = '".$this->db->escape($resource_type)."' LIMIT 1";
        $result = $this->db->query( $get_sql );
        if( isset( $result->num_rows ) && $result->num_rows > 0 && !empty( $result->rows ) ){
            if( $single ){
                return $result->rows[0]['meta_value'];
            }else{
                return $result->rows[0];
            }
        }
        return false;
    }

     /**
     * Get knawat Products 
     */
    public function get_knawat_products(  $meta_key='is_knawat',  $resource_type = 'product') {
        $get_sql = "SELECT * FROM `" . DB_PREFIX . "knawat_metadata` WHERE `meta_key` = '".$this->db->escape($meta_key)."' AND `resource_type` = '".$this->db->escape($resource_type)."'";
        $result = $this->db->query( $get_sql );
        if( isset( $result->num_rows ) && $result->num_rows > 0 && !empty( $result->rows ) ){
            return $result->rows;
        }
        return false;
    }

    public function check_knawat_product($product_id, $meta_key='is_knawat',  $resource_type = 'product') {
        $get_sql = "SELECT * FROM `" . DB_PREFIX . "knawat_metadata` WHERE `meta_key` = '".$this->db->escape($meta_key)."' AND `resource_type` = '".$this->db->escape($resource_type)."' AND  `" . DB_PREFIX . "resource_id` = $product_id";

        $result = $this->db->query( $get_sql );
        if( isset( $result->num_rows ) && $result->num_rows > 0 && !empty( $result->rows ) ){
            return true;
        }
        return false;
    }

    /**
     * Add Custom data by id and resource.
     */
    public function add_knawat_meta( $resource_id, $meta_key, $meta_value = '', $resource_type = 'product' ) {
        $insert_sql = "INSERT INTO `" . DB_PREFIX . "knawat_metadata` (`resource_id`, `resource_type`, `meta_key`, `meta_value`) VALUES (".(int)$resource_id.", '".$this->db->escape($resource_type)."', '".$this->db->escape($meta_key)."', '".$this->db->escape($meta_value)."');";
        $result = $this->db->query( $insert_sql );
        // Last instered ID.
        $meta_id = $this->db->getLastId();
        if( $meta_id > 0 ){
            return true;
        }
        return false;
    }

    /**
     * Update Custom data by id and resource.
     */
    public function update_knawat_meta( $resource_id, $meta_key, $meta_value = '', $resource_type = 'product' ) {

        $meta = $this->get_knawat_meta( $resource_id, $meta_key, $resource_type, false );
        if( $meta && !empty( $meta ) && $meta['meta_id'] > 0 ){
            $meta_id = (int)$meta['meta_id'];

            $update_query = "UPDATE " . DB_PREFIX . "knawat_metadata SET `resource_id` = ".(int)$resource_id.", `meta_key` = '" . $this->db->escape($meta_key) . "', `meta_value` = '" . $this->db->escape($meta_value) . "', `resource_type` = '" . $this->db->escape($resource_type) . "' WHERE meta_id = " . (int)$meta_id;

            $update = $this->db->query( $update_query );
            return $update;
        }

        // Meta data not exist add it.
        $addmeta = $this->add_knawat_meta( $resource_id, $meta_key, $meta_value, $resource_type );
        return $addmeta;
    }

    /**
     * Update Custom data by id and resource.
     */
    public function delete_knawat_meta( $resource_id, $meta_key, $resource_type = 'product' ) {
        $delete_query = "DELETE FROM " . DB_PREFIX . "knawat_metadata WHERE resource_id = " . $this->db->escape((int)$resource_id) . " AND `meta_key` = '" . $this->db->escape($meta_key) . "' AND `resource_type` = '" . $this->db->escape($resource_type) . "'";

        $delete = $this->db->query( $delete_query );
        return $delete;
    }

    /**
     * Update Product option skus.
     */
    public function update_product_options_sku( $product_id, $data, $productOptions ) {

        $data = $this->load_options_id_to_data( $product_id, $data );

        $poptions = array();
        foreach ($data['product_option'] as $pkey => $product_option) {
            foreach ($product_option['product_option_value'] as $pvkey => $poptionv) {
                if( isset( $poptionv['product_option_value_id'] ) && isset( $poptionv['sku'] ) ){
                    $this->update_knawat_meta( $poptionv['product_option_value_id'], 'sku', $poptionv['sku'], 'product_option_value' );
                }
            }
        }
    }

    /////////////////////////////////////////////////////////////
    ////////////////////// Product Functions ////////////////////
    /////////////////////////////////////////////////////////////
    /**
     * Partial Product Update.
     *
     */
    public function partial_update_product( $product_id, $data ) {

        $this->db->query("UPDATE " . DB_PREFIX . "product SET quantity = '" . (int)$data['quantity'] . "', stock_status_id = '" . (int)$data['stock_status_id'] . "', price = '" . (float)$data['price'] . "', cost_price = '" . (float)$data['cost_price'] . "', status = '" . (int)$data['status'] . "', date_modified = NOW() WHERE product_id = '" . (int)$product_id . "'");

        // update product special table
        $this->db->query("UPDATE " . DB_PREFIX . "product_special
                        SET price = " . (float)$data['price'] . " * (1 - (`discount_value` / 100))
                        WHERE `product_id` = " . $this->db->escape((int)$product_id) ." and `discount_type` = 'per'");

        $data = $this->load_options_id_to_data( $product_id, $data );
        ecTargetLog( [
            'backtrace' => debug_backtrace(),
            'uri' => $_SERVER['REQUEST_URI']
        ]);

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . (int)$product_id . "'");

        if (isset($data['product_option'])) {
            foreach ($data['product_option'] as $product_option) {
                if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
                    if (isset($product_option['product_option_value'])) {
                        $this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_option_id = '" . (int)$product_option['product_option_id'] . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', required = '" . (int)$product_option['required'] . "'");

                        $product_option_id = $this->db->getLastId();

                        foreach ($product_option['product_option_value'] as $product_option_value) {
                            $this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET product_option_value_id = '" . (int)$product_option_value['product_option_value_id'] . "', product_option_id = '" . (int)$product_option_id . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', option_value_id = '" . (int)$product_option_value['option_value_id'] . "', quantity = '" . (int)$product_option_value['quantity'] . "', subtract = '" . (int)$product_option_value['subtract'] . "', price = '" . (float)$product_option_value['price'] . "', price_prefix = '" . $this->db->escape($product_option_value['price_prefix']) . "', points = '" . (int)$product_option_value['points'] . "', points_prefix = '" . $this->db->escape($product_option_value['points_prefix']) . "', weight = '" . (float)$product_option_value['weight'] . "', weight_prefix = '" . $this->db->escape($product_option_value['weight_prefix']) . "'");
                        }
                    }
                } else {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_option_id = '" . (int)$product_option['product_option_id'] . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', value = '" . $this->db->escape($product_option['value']) . "', required = '" . (int)$product_option['required'] . "'");
                }
            }
        }
        $productsoptions_sku_status = $this->config->get('productsoptions_sku_status');
        if ($productsoptions_sku_status) {
            if (isset($data['product_options_variations'])) {

                $this->db->query("DELETE FROM product_variations WHERE product_id = '" . (int)$product_id . "'");
                foreach ($data['product_options_variations'] as $variationKey => $option_variation_value) {

                    sort($option_variation_value['options']);

                    $option_value_ids = implode(',', $option_variation_value['options']);
                    $variationsSku = $option_variation_value['sku'];
                    $variationsQuantity = $option_variation_value['quantity'];
                    $variationsPrice = $option_variation_value['price'];
                    $variationsBarcode = $option_variation_value['barcode'];

                    $variationsQueryString = [];
                    $variationsQueryString[] = 'INSERT INTO product_variations SET';
                    $variationsQueryString[] = 'product_id="' . $product_id . '",';
                    $variationsQueryString[] = 'option_value_ids="' . $option_value_ids . '",';
                    if (!empty($variationsQuantity) || $variationsQuantity === '0') {

                        if ($variationsQuantity < -1) {
                            $variationsQuantity = -1;
                        }

                        $variationsQueryString[] = 'product_quantity="' . $variationsQuantity . '",';
                    }
                    $variationsQueryString[] = 'product_price="' . $variationsPrice . '",';
                    $variationsQueryString[] = 'product_sku="' . $variationsSku . '",';
                    $variationsQueryString[] = 'product_barcode="' . $variationsBarcode . '",';
                    $variationsQueryString[] = 'num_options="' . $data['num_options'] . '"';


                    $this->db->query(implode(' ', $variationsQueryString));
                }
            }
        }

        // if (isset($data['product_attribute'])) {
        //     foreach ($data['product_attribute'] as $product_attribute) {
        //         if ($product_attribute['attribute_id']) {
        //             $this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");

        //             foreach ($product_attribute['product_attribute_description'] as $language_id => $product_attribute_description) {
        //                 $this->db->query("INSERT INTO " . DB_PREFIX . "product_attribute SET product_id = '" . (int)$product_id . "', attribute_id = '" . (int)$product_attribute['attribute_id'] . "', language_id = '" . (int)$language_id . "', text = '" . $this->db->escape($product_attribute_description['text']) . "'");
        //             }
        //         }
        //     }
        // }


        $this->cache->delete('product');

        return $product_id;
    }

    /**
     * Get Product ID based on sku
     */
    public function get_product_id_by_sku( $sku = '' ) {
        $temporal_sql = "SELECT product_id FROM `" . DB_PREFIX . "product` WHERE sku = '".$this->db->escape($sku)."' LIMIT 1";
        $result = $this->db->query( $temporal_sql );

        return !empty($result->row['product_id']) ? $result->row['product_id'] : false;
    }

    /**
     * Load Existing IDs (For prevent generate new IDs)
     *
     * @param   array $data
     * @return  array $data
     */
    public function load_options_id_to_data( $product_id, $data ){

        if (isset($data['product_option'])) {
            foreach ($data['product_option'] as $pkey => $product_option) {
                $psql = "SELECT product_option_id FROM " . DB_PREFIX . "product_option WHERE `product_id` = ".(int)$product_id." AND `option_id` = " . (int)$product_option['option_id'] . " LIMIT 1";
                $pquery = $this->db->query($psql);
                if( !empty( $pquery->rows ) && $pquery->num_rows > 0 ){
                    $product_option_id = $pquery->rows[0]['product_option_id'];
                    $data['product_option'][$pkey]['product_option_id'] = $product_option_id;

                    foreach ($product_option['product_option_value'] as $pvkey => $product_option_value) {

                        $pvsql = "SELECT product_option_value_id FROM " . DB_PREFIX . "product_option_value WHERE `product_option_id` = ".(int)$product_option_id." AND `product_id` = ".(int)$product_id." AND `option_id` = " . (int)$product_option['option_id'] . "  AND `option_value_id` = " . (int)$product_option_value['option_value_id'] . " LIMIT 1";
                        $pvquery = $this->db->query( $pvsql );
                        if( !empty( $pvquery->rows ) && $pvquery->num_rows > 0 ){
                            $product_option_value_id = $pvquery->rows[0]['product_option_value_id'];
                            $data['product_option'][$pkey]['product_option_value'][$pvkey]['product_option_value_id'] = $product_option_value_id;
                        }
                    }
                }
            }
        }
        return $data;
    }

    /////////////////////////////////////////////////////////////
    ////////////////////// Category Functions ///////////////////
    /////////////////////////////////////////////////////////////
    /**
     * Parse Category field.
     * Create New Category and return ID if its not there and return ID if its already exists
     *
     */
    public function parse_categories( $categories = array() ){

        if( empty( $categories ) ){
            return array();
        }

        $parse_cats = array();
        foreach ( $categories as $key => $cat ) {
            $cat_name = array_key_exists( $this->default_language, $cat ) ? $cat[$this->default_language] : $cat['en'];

            if( empty( $cat_name ) ){
                continue;
            }


            $new_cat = explode( ' / ', $cat_name );

            if( !empty( $new_cat ) ) {
                $parent_id = 0;
                $cat_number = 0;
                foreach ( $new_cat as $new_cat_name ) {
                    $temp_cat_id = 0;
                    $temp_cat_id = $this->get_category_id_by_name( $new_cat_name, $parent_id );
                    if( !$temp_cat_id ){
                        $temp_cat = array();
                        foreach ( $cat as $key => $value ) {
                            $temp_value = explode( ' / ', $value );
                            if( isset( $temp_value[$cat_number] ) ){
                                $temp_cat[$key] = $temp_value[$cat_number];
                            }
                        }
                        if( empty( $temp_cat ) ) {
                            continue;
                        }
                        $temp_cat_id = $this->create_category( $temp_cat, $parent_id );
                    }
                    $temp_cat_id = (int)$temp_cat_id;
                    $parent_id = $temp_cat_id;
                    $parse_cats[$temp_cat_id] = $temp_cat_id;
                    $cat_number++;
                }
            }
        }
        return $parse_cats;
    }

    /**
     * Create a category with tree.
     *
     */
    public function create_category( $category, $parent_id = 0 ){

        if( $this->is_admin ){
            $this->load->model('catalog/category');
        }else{
            $admin_dir = str_replace( 'system/', 'admin/', DIR_SYSTEM );
            require_once $admin_dir . "model/catalog/category.php";
            $this->model_catalog_category = new ModelCatalogCategory( $this->registry );
        }

        $temp = array(
            'category_description' => array(),
            'category_store' => array(0),
            'path' => null,
            'parent_id' => $parent_id,
            'filter' => null,
            'image' => 'no_image.jpg',
            'top' => 1,
            'column' => 1,
            'sort_order' => 0,
            'status' => 1,
        );

        if( $parent_id > 0 ){
            $temp['top'] = 0;
        }

        foreach ( $this->languages as $key => $lng ) {
            // Check for name in current language.
            $lng_code = explode( '-', $lng['code'] );
            $lng_code = $lng_code[0];

            $cat_name = array_key_exists( $lng_code, $category ) ? $category[$lng_code] : $category['en'];
            $temp['category_description'][$lng['language_id']] = array(
                'name'              => htmlspecialchars($cat_name),
                'meta_title'        => $cat_name,
                'meta_description'  => null,
                'meta_keyword'      => null,
                'description'       => null,
            );
        }

        if ( !empty( $this->all_stores ) ) {
            $temp['category_store'] = $this->all_stores;
        }

        return $this->model_catalog_category->addCategory($temp);

    }

    /**
     * Get Category ID by name
     */
    public function get_category_id_by_name( $cat_name, $parent_id = 0) {

        $cat_name = htmlspecialchars($cat_name);

        $sql = "SELECT cd.* FROM " . DB_PREFIX . "category_description cd";

        if($parent_id > 0){
            $sql .= " INNER JOIN " . DB_PREFIX . "category cat ON(cd.category_id = cat.category_id AND cat.parent_id = ".$parent_id.")";
        }

        $sql .= " WHERE cd.language_id = " . (int)$this->default_language_id . " AND cd.name='".$this->db->escape( $cat_name )."' ORDER BY cd.category_id DESC";

        $query = $this->db->query($sql);

        if( !empty( $query->num_rows) ){
            return $query->rows[0]['category_id'];
        }
        return false;
    }


    /////////////////////////////////////////////////////////////
    ////////////////////// Option Functions /////////////////////
    /////////////////////////////////////////////////////////////


    /**
     * This function is responsible for preparing options in multiple options per variation  
     *
     * @param array $variations
     * @return array $prepared_variations
     */
    public function parse_product_options_v2($variations) {

        $prepared_variations = [];
        
        $x = false;
        if (isset($variations) && !empty($variations)) {
            $_variation = [];
            foreach ($variations as $variation) {
                if (isset($variation->attributes) && !empty($variation->attributes)) {

                    $_variation = [
                        'price' => isset($variation->sale_price) ? $variation->sale_price : 0,
                        'quantity' => isset($variation->quantity) ? $variation->quantity : 0,
                        'sku' => isset($variation->sku) ? $variation->sku : '',
                        'options' => []
                    ];
                    foreach ($variation->attributes as $attribute) {

                        $attribute_names = (array)$attribute->name; // holds option names in different locales.
                        $attribute_options = (array)$attribute->option; // holds option value names in different locales.
                        
                        $option_name = array_key_exists( $this->default_language, $attribute_names ) ? $attribute_names[$this->default_language] : $attribute_names['en'];
                        $option_value_name = array_key_exists( $this->default_language, $attribute_options ) ? $attribute_options[$this->default_language] : $attribute_options['en'];

                        // if option name is blank then take a chance for TR.
                        if(empty($option_name)){
                            $option_name = isset( $attribute_names['tr'] ) ? $attribute_names['tr'] : '';
                        }
                        if(empty($option_value_name)){
                            $option_value_name = isset($attribute_options['tr']) ? $attribute_options['tr'] : '';
                        }

                        // continue if no option name found.
                        if(empty($option_name)){
                            continue;
                        }

                        // find OR create option by name.
                        $option_id = $this->get_option_id_by_name($option_name);
                        if(!$option_id){
                            $option_id = $this->create_option($attribute_names);
                        }

                        // find or create option value by name.
                        $option_value_id = $this->get_option_value_id_by_name($option_value_name, $option_id);
                        if(!$option_value_id){
                            $option_value_id = $this->create_option_value($attribute_options, $option_id);
                        }

                        $_variation['options'][] = $option_value_id;
                    }
                }
                $prepared_variations[] = $_variation;
            }
        }
        
        return $prepared_variations;
    }


    /**
     * Parse Product options.
     * Create New option or option value if not exists.
     *
     */
    public function parse_product_options( $variations = array(), $price ){
        if( empty( $variations ) ){
            return array();
        }


        $product_options = array();

        if( isset( $variations ) && !empty( $variations ) ){

            $attributes = array();
            $varients = array();
            foreach ( $variations as $variation ) {

                if( isset( $variation->attributes ) && !empty( $variation->attributes ) ){
                    foreach ( $variation->attributes as $attribute ) {

                        $attribute_names = (array) $attribute->name;
                        $attribute_options = (array) $attribute->option;

                        $option_name = array_key_exists( $this->default_language, $attribute_names ) ? $attribute_names[$this->default_language] : $attribute_names['en'];
                        $option_value_name = array_key_exists( $this->default_language, $attribute_options ) ? $attribute_options[$this->default_language] : $attribute_options['en'];

                        // if option name is blank then take a chance for TR.
                        if( empty( $option_name ) ){
                            $option_name = isset( $attribute_names['tr'] ) ? $attribute_names['tr'] : '';
                        }
                        if( empty( $option_value_name ) ){
                            $option_value_name = isset( $attribute_options['tr'] ) ? $attribute_options['tr'] : '';
                        }

                        // continue if no option name found.
                        if( empty( $option_name ) ){
                            continue;
                        }

                        if( isset( $attributes[ $option_name ] ) ){
                            if( !array_key_exists( $option_value_name, $attributes[ $option_name ]['values'] ) ){
                                $attributes[ $option_name ]['values'][$option_value_name] = $attribute_options;
                            }
                        }else{
                            $attributes[ $option_name ]['name'] = $attribute_names;
                            $attributes[ $option_name ]['values'][$option_value_name] = $attribute_options;
                        }
                        $varients[ $option_name ][$option_value_name]['price'] = isset( $variation->sale_price ) ? $variation->sale_price : 0;
                        $varients[ $option_name ][$option_value_name]['quantity'] = isset( $variation->quantity ) ? $variation->quantity : 0;
                        $varients[ $option_name ][$option_value_name]['sku'] = isset( $variation->sku ) ? $variation->sku : '';
                    }
                }
            }

            if( !empty( $attributes ) ){
                foreach ($attributes as $key => $attribute ) {

                    $temp_option = array(
                        'product_option_id'     => '',
                        'name'                  => $key,
                        'option_id'             => '',
                        'type'                  => 'select',
                        'required'              => '1',
                        'product_option_value'  => array()
                    );

                    $option_id = $this->get_option_id_by_name( $key );
                    if( !$option_id ){
                        $option_id = $this->create_option( $attribute['name'] );
                    }

                    if( !$option_id ){
                        // continue if fail to create option.
                        continue;
                    }

                    $temp_option['option_id'] = $option_id;

                    if( !empty( $attribute['values'] ) ){
                        foreach ( $attribute['values'] as $okey => $ovalue ) {

                            $temp_value = array(
                                'option_value_id'           => '',
                                'product_option_value_id'   => '',
                                'quantity'                  => $varients[$key][$okey]['quantity'],
                                'sku'                       => $varients[$key][$okey]['sku'],
                                'subtract'                  => '1',
                                'price_prefix'              => '+',
                                'price'                     => '0',
                                'points_prefix'             => '+',
                                'points'                    => '0',
                                'weight_prefix'             => '+',
                                'weight'                    => '0'
                            );

                            if( $price != $varients[$key][$okey]['price'] ){
                                $difference = ( $varients[$key][$okey]['price'] - $price );
                                if( $difference > 0 ){
                                    $temp_value['price_prefix'] = '+';
                                    $temp_value['price'] = $difference;
                                }elseif( $difference < 0 ){
                                    $temp_value['price_prefix'] = '-';
                                    $temp_value['price'] = -($difference);
                                }
                            }

                            $option_value_id = $this->get_option_value_id_by_name( $okey, $option_id );
                            if( !$option_value_id ){
                                $option_value_id = $this->create_option_value( $ovalue, $option_id );
                            }

                            if( !$option_value_id ){
                                // continue if fail to create option.
                                continue;
                            }

                            $temp_value['option_value_id'] = $option_value_id;
                            $temp_option['product_option_value'][] = $temp_value;
                        }
                    }

                    $product_options[] = $temp_option;
                }
            }
        }
        return $product_options;
    }


    /**
     * Get Option ID by name
     */
    public function get_option_id_by_name( $option_name, $from_option_value = false) {
        $sql = "SELECT fgd.* FROM " . DB_PREFIX . "option_description fgd WHERE name = BINARY '".$this->db->escape($option_name)."' AND language_id = " . $this->default_language_id;
        $query = $this->db->query($sql);

        if( !empty( $query->num_rows ) ){
            return $query->rows[0]['option_id'];
        }
        return false;
    }

    /**
     * Create Option.
     */
    public function create_option( $attribute_names ){
        if( empty( $attribute_names ) ){
            return false;
        }

        if( $this->is_admin ){
            $this->load->model('catalog/option');
        }else{
            $admin_dir = str_replace( 'system/', 'admin/', DIR_SYSTEM );
            require_once $admin_dir . "model/catalog/option.php";
            $this->model_catalog_option = new ModelCatalogOption( $this->registry );
        }

        $temp = array(
            'option_description'=> array(),
            'type'              => 'select',
            'sort_order'        => '0'
        );
        foreach ( $this->languages as $key => $lng ) {
            // Check for name in current language.
            $lng_code = explode( '-', $lng['code'] );
            $lng_code = $lng_code[0];
            $attr_name = array_key_exists( $lng_code, $attribute_names ) ? $attribute_names[$lng_code] : $attribute_names['en'];
            // if option name is blank then take a chance for TR.
            if( empty( $attr_name ) ){
                $attr_name = isset( $attribute_names['tr'] ) ? $attribute_names['tr'] : '';
            }
            $temp['option_description'][$lng['language_id']] = array(
                'name'              => $attr_name
            );
        }

        $option_id = $this->model_catalog_option->addOption($temp);
        // Return it.
        return $option_id;
    }

    /**
     * Get option value ID by Name
     */
    public function get_option_value_id_by_name( $option_value_name, $option_id ) {
        $sql = "SELECT atd.* FROM " . DB_PREFIX . "option_value_description atd WHERE name = '".str_replace("'","''",$option_value_name)."' AND language_id = " . $this->default_language_id . " AND option_id = ". (int)$option_id;

        $query = $this->db->query($sql);

        if( !empty( $query->num_rows ) ){
            return ($query->rows[0]['option_value_id']) ? $query->rows[0]['option_value_id'] : false;
        }
    }

    /**
     * Create Option Value.
     */
    public function create_option_value( $option_value_names, $option_id ){
        if( empty( $option_value_names ) || empty( $option_id ) ){
            return false;
        }

        $option_value['option_value_description'] = array();
        foreach ( $this->languages as $key => $lng ) {
            // Check for name in current language.
            $lng_code = explode( '-', $lng['code'] );
            $lng_code = $lng_code[0];

            $option_value_names = (array) $option_value_names;
            $value_name = array_key_exists( $lng_code, $option_value_names ) ? $option_value_names[$lng_code] : $option_value_names['en'];
            // if option name is blank then take a chance for TR.
            if( empty( $value_name ) ){
                $value_name = isset( $option_value_names['tr'] ) ? $option_value_names['tr'] : '';
            }
            $option_value['option_value_description'][$lng['language_id']] = array(
                'name' => $value_name
            );
        }

        $this->db->query("INSERT INTO " . DB_PREFIX . "option_value SET option_id = '" . (int)$option_id . "', image = '', sort_order = 0");

        $option_value_id = $this->db->getLastId();

        foreach ($option_value['option_value_description'] as $language_id => $option_value_description) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "option_value_description SET option_value_id = '" . (int)$option_value_id . "', language_id = '" . (int)$language_id . "', option_id = '" . (int)$option_id . "', name = '" . $this->db->escape($option_value_description['name']) . "'");
        }

        // Return it.
        return $option_value_id;
    }
    
    /**
     * Parse Product attributes.
     * Create New attribute or attribute value if not exists.
     *
     */
    public function parse_product_attributes( $attributes = array() ){

        if( empty( $attributes ) ){
            return array();
        }

        $product_attributes = array();

        if( isset( $attributes ) && !empty( $attributes ) ){
            foreach ($attributes as $key => $attribute ) {

                $temp_attr = array(
                    'attribute_id'             => '',
                    'product_attribute_description'  => array()
                );
                $attr_name = array_key_exists( $this->default_language, $attribute['name'] ) ? $attribute['name'][$this->default_language] : $attribute['name']['tr'];
                    
                if(is_null($attr_name)){
                    $attr_name=$attribute['name']['tr'];
                }
                $attr_name = str_replace("&nbsp;", "", $attr_name);
                $attr_name = trim($attr_name);

               // $attr_name = htmlentities($attr_name);
                $attr_id = $this->get_attribute_id_by_name( $attr_name );

                if( !$attr_id ){
                    $attr_id = $this->create_attribute( $attribute['name'] );
                }

                if( !$attr_id ){
                    // continue if fail to create option.
                    continue;
                }

                $temp_attr['advanced_attribute_id'] = $attr_id;
                $temp_attr['type'] = "text";

                foreach ( $this->languages as $key => $lng ) {
                    // Check for name in current language.
                    $lng_code = explode( '-', $lng['code'] );
                    $lng_code = $lng_code[0];

            ///////// The last thing is attribute values isn't inserted correctly.
                    foreach ($attribute['values'] as $value) {
                        # code...
                        $value = (array) $value;
                        $attr_val_name = array_key_exists( $lng_code, $value ) ? $value[$lng_code] : $value['en'];
                        // if attribute value name is blank then take a chance for TR.
                        if( empty( $attr_val_name ) ){
                            $attr_val_name = isset( $value['tr'] ) ? $value['tr'] : '';
                        }
                        if(isset($temp_attr['product_attribute_description'][$lng['language_id']])){
                            $temp_attr['product_attribute_description'][$lng['language_id']] = array(
                                'text'=>$temp_attr['product_attribute_description'][$lng['language_id']]['text'].",".$attr_val_name
                            );
                        }
                        else{
                            $temp_attr['product_attribute_description'][$lng['language_id']] = array(
                                'text'=>$attr_val_name
                            );
                        }
                    }
                    
                }

                $product_attributes[] = $temp_attr;
            }
        }
        return $product_attributes;
    }

      /**
     * Get Option ID by name
     */
    public function get_attribute_id_by_name( $attr_name, $from_option_value = false) {
        
        $sql = "SELECT * FROM " . DB_PREFIX . "attribute_description WHERE name like '".$this->db->escape($attr_name)."' AND language_id = ".$this->default_language_id ;

        $query = $this->db->query($sql);

        if( $query->num_rows > 0 ){
            return $query->rows[0]['attribute_id'];
        }
        return false;
    }

    /**
     * Create Option.
     */
    public function create_attribute( $attribute_names ){
        if( empty( $attribute_names ) ){
            return false;
        }

        if( $this->is_admin ){
            $this->load->model('catalog/attribute');
        }else{
            $admin_dir = str_replace( 'system/', 'admin/', DIR_SYSTEM );
            require_once $admin_dir . "model/catalog/attribute.php";
            $this->model_catalog_attribute = new ModelCatalogAttribute( $this->registry );
        }

        $temp = array(
            'attribute_group_id' => 1,
            'sort_order' => 0,
            'attribute_description'=> array(),
        );
        foreach ( $this->languages as $key => $lng ) {
            // Check for name in current language.
            $lng_code = explode( '-', $lng['code'] );
            $lng_code = $lng_code[0];
            $attr_name = array_key_exists( $lng_code, $attribute_names ) ? $attribute_names[$lng_code] : $attribute_names['tr'];
            
            if(is_null($attr_name))
                $attr_name = $attribute_names['tr'];
                
            $attr_name = str_replace("&nbsp;", "", $attr_name);
            $attr_name = trim($attr_name);
           // $attr_name = htmlentities($attr_name);
            // if option name is blank then take a chance for TR.
            //if( empty( $attr_name ) ){
            //    $attr_name = isset( $attribute_names['tr'] ) ? htmlentities(trim($attribute_names['tr'])) : '';        
            //}
            $temp['attribute_description'][$lng['language_id']] = array(
                'name'              => $attr_name
            );
        }
        $attribute_id = $this->model_catalog_attribute->addAttribute($temp);
        
        // Return it.
        return $attribute_id;
    }

    /**
     * Get option value ID by Name
     */
    public function get_attribute_value_id_by_name( $option_value_name, $option_id ) {
        $sql = "SELECT atd.* FROM " . DB_PREFIX . "option_value_description atd WHERE name = '".$option_value_name."' AND language_id = " . $this->default_language_id . " AND option_id = ". (int)$option_id;

        $query = $this->db->query($sql);

        if( !empty( $query->num_rows ) ){
            return ($query->rows[0]['option_value_id']) ? $query->rows[0]['option_value_id'] : false;
        }
    }

    public function get_order_status_name( $order_status_id ) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_status WHERE order_status_id = '" . (int)$order_status_id . "' AND language_id = 1 ");

        if( !empty( $query->row ) ){
            return $query->row['name'];
        }
		return false;
    }

    public function edit_setting( $code, $data, $store_id = 0 ) {

//        $this->load->model('setting/setting');
//        $this->model_setting_setting->editSetting($code, $data);
		$this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE store_id = '" . (int)$store_id . "' AND `group` = '" . $this->db->escape($code) . "'");
		foreach ($data as $key => $value) {
			if (substr($key, 0, strlen($code)) == $code) {
				if (!is_array($value)) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$store_id . "', `group` = '" . $this->db->escape($code) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape($value) . "'");
				} else {
					$this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$store_id . "', `group` = '" . $this->db->escape($code) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape(serialize($value)) . "', serialized = '1'");
				}
			}
		}
		//$this->cache->delete('settings');
	}

    /**
     * Get Syncronization failed order.
     */
    public function get_sync_failed_orders(){
        $get_sql = "SELECT `resource_id` FROM `" . DB_PREFIX . "knawat_metadata` join `" . DB_PREFIX . "order` on ".
                    " `" . DB_PREFIX . "knawat_metadata`.resource_id = `" . DB_PREFIX . "order`.order_id WHERE  `meta_key` = 'knawat_sync_failed' AND `resource_type` = 'order'";
        $result = $this->db->query( $get_sql );
        if( isset( $result->num_rows ) && $result->num_rows > 0 && !empty( $result->rows ) ){
            $orders = $result->rows;

            foreach ($orders as $key => $order) {
                $order_product_query = 'SELECT product_id FROM ' . DB_PREFIX . 'order_product';
                $order_product_query .= ' WHERE order_product.order_id = "' . (int)$order['resource_id'] . '"';
                $order_product_query_result = $this->db->query($order_product_query)->rows;
                $order_product_ids = array_column($order_product_query_result, 'product_id');

                $products_count_query = 'SELECT COUNT(product_id) AS count FROM product ';
                $products_count_query .= 'WHERE product_id IN (' . implode(',', $order_product_ids) . ')';
                $products_count = $this->db->query($products_count_query)->row['count'];

                if (count($order_product_ids) != $products_count) {
                    unset($orders[$key]);
                    continue;
                }
            }
            if (count($orders) > 0) {
                return $orders;
            }
        }
        return false;
    }

    public function saveSncySKUs($sku_arr, $page) {
        $query = "INSERT INTO " . DB_PREFIX . "knawat_sync_skus (`sku`) VALUES ";
        foreach ($sku_arr as $sku) {
            $query .= "('{$sku}'),";
        }
        $query = substr_replace($query ,"", -1);

        $this->db->query($query);
        $this->db->query("UPDATE `" . DB_PREFIX . "setting` SET `value`={$page} WHERE `group`='knawat_dropshipping_sync' AND `key`='knawat_dropshipping_sync_offset_page'");
    }

    public function getPrevSyncSKUs () {
        
        return $this->db->query("SELECT `sku` FROM `" . DB_PREFIX . "knawat_sync_skus`")->rows;
    }

    public function saveSyncDeletedCount($deleted_count) {
        $this->db->query("UPDATE `" . DB_PREFIX . "setting` SET `value`={$deleted_count} WHERE `group`='knawat_dropshipping_sync' AND `key`='knawat_dropshipping_sync_deleted_count'");
    }

    public function resetSyncValues() {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "knawat_sync_skus`");
        $this->db->query("UPDATE `" . DB_PREFIX . "setting` SET `value`='0' WHERE `group`='knawat_dropshipping_sync' AND `key`='knawat_dropshipping_sync_offset_page'");
        $this->db->query("UPDATE `" . DB_PREFIX . "setting` SET `value`='0' WHERE `group`='knawat_dropshipping_sync' AND `key`='knawat_dropshipping_sync_deleted_count'");
    }

    public function updateImportOffsetPage($page) {

        $import_settings = $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE `group`='knawat_dropshipping_import' AND `key`='knawat_dropshipping_import_offset_page'");
        if (empty($import_settings->rows)) {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` VALUES (NULL, '0', 'knawat_dropshipping_import', 'knawat_dropshipping_import_offset_page', '1', '0')");
        }
        $this->db->query("UPDATE `" . DB_PREFIX . "setting` SET `value`={$page} WHERE `group`='knawat_dropshipping_import' AND `key`='knawat_dropshipping_import_offset_page'");
    }
}
