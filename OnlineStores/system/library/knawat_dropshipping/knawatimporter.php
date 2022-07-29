<?php
/**
 * Knawat Dropshipping Importer class.
 *
 * @link       http://knawat.com/
 * @since      1.0.0
 * @category   Class
 * @author 	   Dharmesh Patel
 */

 class KnawatImporter{

    private $registry;
    private $codename = 'knawat_dropshipping';
    private $route = 'module/knawat_dropshipping';

    private $languages = array();
    private $default_language_id = 1;
    private $default_language = 'en';
    private $all_stores;
    private $is_admin = false;

    /**
     * Import Type
     *
     * @var string
     */
    protected $import_type = 'full';

    /**
     * API Wrapper Class instance
     *
     * @var string
     */
    protected $mp_api;

    /**
     * Parameters which contains information regarding import.
     *
     * @var array
     */
    public $params;

    /**
     * Knawat MP API Constructor
     */
    public function __construct( $registry, $import_args = array(), $import_type = 'full' ){
        
        $this->registry = $registry;
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

        if( $this->is_admin ){
            $this->load->model( $this->route );
        }else{
            $admin_dir = str_replace( 'system/', 'admin/', DIR_SYSTEM );
            require_once $admin_dir . "model/module/knawat_dropshipping.php";
            $this->model_module_knawat_dropshipping = new ModelModuleKnawatDropshipping( $registry );
        }

        $this->import_type = $import_type;

        // Load API Wrapper.
        require_once( DIR_SYSTEM . 'library/knawat_dropshipping/knawatmpapi.php' );
        $this->mp_api = new KnawatMPAPI( $this->registry );

        $default_args = array(
            'limit'             => 10,   // Limit for Fetch Products
            'page'              => 1,    // Page Number for API
            'force_update'      => false, // Whether to update existing items.
            'prevent_timeouts'  => true, // Check memory and time usage and abort if reaching limit.
            'is_complete'       => false,
            'imported'          => 0,
            'failed'            => 0,
            'updated'           => 0,
            'batch_done'        => false
        );

        if( is_array( $import_args ) ){
            $default_args = array_merge( $default_args, $import_args );
        }
        $this->params = $default_args;
    }

    public function __get($name) {
        return $this->registry->get($name);
    }


    /** 
     *  Main import function.
     */
    public function import(){

        $this->start_time = time();
        $data             = array(
            'imported' => array(),
            'failed'   => array(),
            'updated'  => array(),
        );

        if( $this->is_admin ){
            $this->load->model('catalog/product');
        }else{
            try {
                $admin_dir = str_replace( 'system/', 'admin/', DIR_SYSTEM );
                require_once $admin_dir . "model/catalog/product.php";
                $refl = new \ReflectionClass('ModelCatalogProduct');
                $params_for_construct = array($this->registry);
                $this->model_catalog_product = $refl->newInstanceArgs($params_for_construct);
            } catch (\Exception $e) {
                return false;
            }
        }
       
        switch ( $this->import_type ) {
            case 'full':
                if(isset($this->params['lastupdate']) && $this->params['lastupdate'] != ""){
                    $url='catalog/products/?limit='.$this->params['limit'].'&page='.$this->params['page'].'&lastupdate='.$this->params['lastupdate'];
                }
                else{
                    $url='catalog/products/?limit='.$this->params['limit'].'&page='.$this->params['page'];
                }
                $productdata = $this->mp_api->get($url);
                break;

            case 'single':
                $product_sku = '';
                $product_id = $this->params['product_id'];
                if( $product_id != '' ){
                    $tempproduct = $this->model_catalog_product->getProduct( $product_id );
                    $product_sku = isset( $tempproduct['sku'] ) ? $tempproduct['sku'] : '';
                    if( empty( $product_sku ) ){
                        $product_sku = isset( $tempproduct['model'] ) ? $tempproduct['model'] : '';
                    }
                }
                if( empty( $product_sku ) ){
                    return array( 'status' => 'fail', 'message' => 'Please provide product sku.' );
                }
                // API Wrapper class here.
                $productdata = $this->mp_api->get( 'catalog/products/'. $product_sku );
                break;

            default:
                break;
        }

        if( !empty( $productdata ) && ( isset( $productdata->products ) || isset( $productdata->product ) ) ){


            $this->model_module_knawat_dropshipping->updateImportOffsetPage($this->params['page']);

            $products = array();
            if ( 'single' === $this->import_type ) {
                if( !isset( $productdata->product ) ){
                    $error_message = isset( $productdata->message ) ? $productdata->message : 'Something went wrong during get data from Knawat MP API. Please try again later.';
                    return array( 'status' => 'fail', 'message' => $error_message );
                }
                $products[] = $productdata->product;
            }else{
                $products = $productdata->products;
            }

            // Handle errors
            if( isset( $products->code ) || !is_array( $products ) ){
                return array( 'status' => 'fail', 'message' => 'Something went wrong during get data from Knawat MP API. Please try again later.' );
            }
            
            // Update Product totals.
            if( empty( $products ) ){
                $this->params['is_complete'] = true;
                return $data;
            }
            
            foreach( $products as $index => $product ){

                $result = $this->import_product( $product, $this->params['force_update'] , $this->params['update_category_on_product_update'] );
                if ( !$result ) {
                    $data['failed'][] = $product->sku;
                } else{
                    if ( isset( $result['updated'] ) ) {
                        $data['updated'][] = $result['updated'];
                        $product_id = $result['updated'];
                    } else {
                        $data['imported'][] = $result['imported'];
                        $product_id = $result['imported'];
                    }

                }
                /*if ( $this->params['prevent_timeouts'] && ( $this->time_exceeded() || $this->memory_exceeded() ) ) {
                    break;
                }*/
            }

            // Total products from knawat
            $p_total = $productdata->total;
            
            $products_total = count( $products );
            if( $products_total === 0 ){
                $this->params['is_complete'] = true;
            }elseif( $products_total < $this->params['limit'] ){
                $this->params['is_complete'] = true;
            }
            elseif($p_total < $this->params['limit']*$this->params['page']){
                $this->params['is_complete'] = true;
            }
            else{
                $this->params['is_complete'] = false;
            }

            if ($this->params['is_complete']) {
                $this->model_module_knawat_dropshipping->updateImportOffsetPage(1);
            }
 
            $this->params['batch_done'] = true;
            return $data;

        }else{
            error_log( $this->data );
            return false;
            //return array( 'status' => 'fail', 'message' => 'Something went wrong during get data from Knawat MP API. Please try again later.' );
        }
    }
    
    /**
     * Import Products
     */
    public function import_product( $product, $force_update = false , $update_category_on_product_update = false ){

        if( empty( $product ) || !isset( $product->sku ) ){
            return false;
        }

        if( $this->is_admin ){
            $this->load->model('catalog/product');
            $this->load->model('module/advanced_product_attributes/attribute');

        }else{
            try {
                $admin_dir = str_replace( 'system/', 'admin/', DIR_SYSTEM );
                require_once $admin_dir . "model/catalog/product.php";
                $refl = new \ReflectionClass('ModelCatalogProduct');
                $params_for_construct = array($this->registry);
                $this->model_catalog_product = $refl->newInstanceArgs($params_for_construct);

                if($this->config->get('advanced_product_attribute_status')){
                    require_once $admin_dir . "model/module/advanced_product_attributes/attribute.php";
                    $refl = new \ReflectionClass('ModelModuleAdvancedProductAttributesAttribute');
                    $params_for_construct = array($this->registry);
                    $this->model_module_advanced_product_attributes_attribute = $refl->newInstanceArgs($params_for_construct);
                }
               
                
            } catch (\Exception $e) {
                return false;
            }
        }

        /* Check for Existing Product */
        $product_id = $this->model_module_knawat_dropshipping->get_product_id_by_sku( $product->sku );

        if( $product_id && $product_id > 0 ){
            $product_data = $this->format_product( $product, true, $force_update );

            //if( !empty( $product_data ) && $product_data['quantity'] > 0){
                if( $force_update ){
                    $product_data['knawat_category_edit'] = true;
                    if($update_category_on_product_update){
                        $product_data['update_category_on_product_update'] = true;
                    }
                    $this->model_catalog_product->editProduct( $product_id, $product_data, true);
                }else{
                    $product_id = $this->model_module_knawat_dropshipping->partial_update_product( $product_id, $product_data );
                }
            //}

            // add advanced attributes
            if(isset($product_data['product_advanced_attribute']) && $this->config->get('advanced_product_attribute_status')){
                $this->model_module_advanced_product_attributes_attribute->addProductAttributes($product_id, $product_data['product_advanced_attribute']);
            }

            // Update product options SKU
            $productOptions = $this->model_catalog_product->getProductOptions( $product_id );
            $this->model_module_knawat_dropshipping->update_product_options_sku( $product_id, $product_data, $productOptions );
            // update custom meta
            $this->model_module_knawat_dropshipping->update_knawat_meta( $product_id, 'is_knawat','1', 'product' );
            return array( 'updated' => $product_id );
            
        }else{
            $product_data = $this->format_product( $product );
            if( !empty( $product_data ) && $product_data['quantity'] > 0){
                if($this->params['knawat_pin_category']  && $this->params['knawat_pin_category_id']){
                    $product_data['product_category'] = [$this->params['knawat_pin_category_id']];
                }
                $product_id = $this->model_catalog_product->addProduct( $product_data );
                // Update product options SKU
                $productOptions = $this->model_catalog_product->getProductOptions( $product_id );
                $this->model_module_knawat_dropshipping->update_product_options_sku( $product_id, $product_data, $productOptions );
                // update custom meta
                $this->model_module_knawat_dropshipping->update_knawat_meta( $product_id, 'is_knawat','1', 'product' );
                return array( 'imported' => $product_id );
            }
        }
        return false;
    }


    /**
     * Format product as per Opencart Syntax.
     */
    public function format_product( $product, $update = false, $force_update = false ){
        if( empty( $product ) ){
            return $product;
        }

        if( !$update || $force_update ){

            $temp = array(
                'isKanawatProduct' => true,
                'product_description' => array(),
                'model'             => '',
                'sku'               => isset( $product->sku ) ? $product->sku : '',
                'upc'               => '',
                'ean'               => '',
                'jan'               => '',
                'isbn'              => '',
                'mpn'               => '',
                'location'          => '',
                'price'             => '0',
                'points'            => '',
                'tax_class_id'      => '0',
                'quantity'          => '0',
                'minimum'           => '1',
                'subtract'          => '1',
                'stock_status_id'   => '5',
                'shipping'          => '1',
                'date_available'    => date( 'Y-m-d', strtotime( '-1 day') ),
                'length'            => '',
                'width'             => '',
                'height'            => '',
                'length_class_id'   => '1',
                'weight'            => '',
                'weight_class_id'   => '1',
                'status'            => '1',
                'sort_order'        => '0',
                'manufacturer'      => '',
                'manufacturer_id'   => '0',
                'product_store'     => array(0),
                'product_category'  => array(),
                'product_option'    => array(),
                'product_attribute' => array(),
                'image'             => ''  // This is Pending.
            );

            //  Add to selected stores.
            if ( !empty( $this->all_stores ) ) {
                $temp['product_store'] = $this->all_stores;
            }

            foreach ( $this->languages as $key => $lng ) {
                // Check for name in current language.
                $lng_code = explode( '-', $lng['code'] );
                $lng_code = $lng_code[0];

                $name = $product->name;
                $description = $product->description;

                /*$product_name = array_key_exists( $lng_code, $name ) ? $name[$lng_code] : $name['en'];
                $product_desc = array_key_exists( $lng_code, $description ) ? $description[$lng_code] : $description['en'];*/
                $product_name = isset( $name->$lng_code ) ? $name->$lng_code : '';
                if( empty( $product_name )){
                  $product_name = isset( $name->en ) ? $name->en : '';
                }
                $product_desc = isset( $description->$lng_code ) ? $description->$lng_code : '';
                if( empty( $product_desc )){
                  $product_desc = isset( $description->en ) ? $description->en : '';
                }
                 $temp['product_description'][$lng['language_id']] = array(
                    'name'              => $product_name,
                    'description'       => $product_desc,
                    'meta_title'        => $product_name,
                    'meta_description'  => '',
                    'meta_keyword'      => '',
                    'tag'               => '',
                );
            }

            /**
             * Setup Product Category.
             */
            if(($this->params['knawat_pin_category']  && $this->params['knawat_pin_category_id']) || $update) {
                $temp['product_category'] = [];
            } else {
                if( isset( $product->categories ) && !empty( $product->categories ) ) {
                    $new_cats = array();
                    foreach ( $product->categories as $category ) {
                        if( isset( $category->name ) && !empty( $category->name ) ){
                            $new_cats[] = (array)$category->name;
                        }
                    }
                    $temp['product_category'] = $this->model_module_knawat_dropshipping->parse_categories( $new_cats );
                }
            }

            /**
             * Setup Product Images.
             */
            if( isset( $product->images ) && !empty( $product->images ) ) {
               
                $images = (array)$product->images;
                $product_images = (array)$product->images;
                $product_sku = isset( $product->sku ) ? $product->sku : '';

                $product_images = $this->parse_product_images( $images, $product_sku );
                if( !empty( $product_images ) ){
                    $temp['image'] = $product_images[0];
                    unset( $product_images[0] );
                    if( count( $product_images ) > 0 ){
                        foreach ($product_images as $pimage ) {
                            $temp_image['image'] = $pimage;
                            $temp_image['sort_order'] = '0';
                            $temp['product_image'][] = $temp_image;
                        }
                    }
                }
            }

        }else{
            $temp = array();
        }
        if( isset( $product->variations ) && !empty( $product->variations ) ){
            $price = $product->variations[0]->sale_price;
            $cost_price = $product->variations[0]->cost_price;
            $weight = $product->variations[0]->weight;
            $quantity = 0;
            $variations = [];
            foreach ( $product->variations as $vvalue ) {
                $quantity += $vvalue->quantity;
                if ($vvalue->quantity > 0) {
                    $variations[] = $vvalue;
                }
            }
            $product->variations = $variations;
            $temp['price']      = $price;
            $temp['cost_price']      = $cost_price;
            $temp['quantity']   = $quantity;
            $temp['weight']     = $weight;
            if( $quantity > 0 ){
                $temp['stock_status_id'] = '7';
                $temp['status'] = '1';
            }else{
                $temp['stock_status_id'] = '5';
                $temp['status'] = '0';
            }
            $temp['product_options_variations'] = $this->model_module_knawat_dropshipping->parse_product_options_v2( $product->variations, $price );
            $temp['product_option'] = $this->model_module_knawat_dropshipping->parse_product_options( $product->variations, $price );
            $temp_attributes = $this->returnProductAttributes($temp['product_option'],$product->attributes);
            $temp['product_advanced_attribute'] = $this->model_module_knawat_dropshipping->parse_product_attributes( $temp_attributes );
        } else {
            $temp['status'] = '0';
        }
        ///////////////////////////////////
        /////// @TODO Custom Fields ///////
        ///////////////////////////////////
        return $temp;
    }

    private function returnProductAttributes($options, $attrs)
    {
        # code...
        
        $attributes = array();
        $default_language = 'en';
        foreach ( $attrs as $attr ) {
            $attribute_names = (array) $attr->name;
            $attribute_values = (array) $attr->options;
            $attr_values = array();

            $attr_name = array_key_exists( 'en', $attribute_names ) ? $attribute_names[$default_language] : $attribute_names['en'];
           // var_dump($attribute_values);
            foreach ($attribute_values as $row) {
                # code...
                $attr_value = $row;
                foreach ($attr_value as $lang => $value) {
                    # code...
                    $attr_values[$lang][] = $value;
                }
            }
            if( empty( $attr_name ) ){
                $attr_name = isset( $attribute_names['tr'] ) ? $attribute_names['tr'] : '';
            }
            if( empty( $attr_value ) ){
                $attr_value = isset( $attribute_values['tr'] ) ? $attribute_values['tr'] : '';
            }

            if( empty( $attr_name ) ){
                continue;
            }
            
            $exist_op = false;
            foreach($options as $op){
                if($op['name'] == $attr_name )
                    $exist_op = true;
            }
            
            if($exist_op)
                continue;

            if( isset( $attributes[ $attr_name ] ) ){
                if( !array_key_exists( $attr_value, $attributes[ $attr_name ]['values'] ) ){
                    $attributes[ $attr_name ]['values'] = (array)$attribute_values;
                }
            }else{
                $attributes[ $attr_name ]['name'] = $attribute_names;
                $attributes[ $attr_name ]['values'] = (array)$attribute_values;
            }
        }
        return $attributes;
    }
    /**
     * Parse Product images.
     * download all images and return local path of images.
     *
     * @return array 
     */
    public function parse_product_images( $images = array(), $product_sku = '' ){

        if( empty( $images ) || !is_array( $images ) ){
            return array();
        }
        $parsed_images = array();
        foreach ( $images as $image ) {
            $parsed_image = $this->save_image( $image, $product_sku );

            if( $parsed_image && $parsed_image != '' ){
                $parsed_images[] = $parsed_image;
            }
        }

        return $parsed_images;
    }

    /**
     * Save image from URL and return relative path from catalog folder
     * 
     * @return string|boolean relative path from catalog folder
     */
    public function save_image( $image_url, $prefix = '' ){
        if( !empty( $image_url ) ){
            // Set variables for storage, fix file filename for query strings.
            preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png|webp)\b/i', $image_url, $matches );
            if ( ! $matches ) {
                return false;
            }
            
            $directory = 'image/data/KnawatProducts/';
            
            if (!\Filesystem::isDirExists($directory)) {
                // chmod($directory, 0777);
                \Filesystem::createDir($directory);
                \Filesystem::setPath($directory)->changeMod('writable');
            }
            
            $image_name = $prefix . '_image_'.basename( $matches[0] );

            $full_image_path = $directory.$image_name;
            $catalog_path = 'data/KnawatProducts/' . $image_name;

            if(\Filesystem::isExists($full_image_path)) {
                return $catalog_path;
            }else{
                // Download image.
                $ch = curl_init ( $image_url );
                curl_setopt( $ch, CURLOPT_HEADER, 0 );
                curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
                curl_setopt( $ch, CURLOPT_BINARYTRANSFER,1 );
                curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
                curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
                curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
                curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 120 );
                curl_setopt( $ch, CURLOPT_TIMEOUT, 120 );
                curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
                // curl_setopt( $ch, CURLOPT_PROXY, "192.168.10.5:8080" ); // for local USE only remove it please
                $raw_image_data = curl_exec( $ch );
                curl_close ($ch);
               
                try{
                    // $image = fopen( $full_image_path,'w' );
                    // fwrite( $image, $raw_image_data );
                    // fclose( $image );
                    \Filesystem::setPath($full_image_path)->put($raw_image_data);
                    return $catalog_path;
                }catch( Exception $e ){ 
                    return false;
                }

            }
        }
        return false;
    }

    /**
     * Get Import Parameters
     *
     * @return array()
     */
    public function get_import_params(){
        return $this->params;
    }

 }