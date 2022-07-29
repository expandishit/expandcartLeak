<?php
include DIR_SYSTEM.'library/PHPExcel.php';

class ControllerModuleLike4card extends Controller
{
    private $error = [];
    public $route = "module/like4card";
    public $module = "like4card";

    public function init()
    {
        if (!\Extension::isInstalled($this->module) || !$this->user->hasPermission("modify", "module/{$this->module}")) {
            return $this->forward("error/permission");
        }

        $this->load->model("setting/setting");
        $this->load->language("module/{$this->module}");
        $this->data["updateSettingsAction"] = $this->url->link("module/{$this->module}/updateSettings", "", "SSL");
        $this->data["uploadProductsAction"] = $this->url->link("module/{$this->module}/uploadProducts", "", "SSL");
        $this->data["syncCatsAction"] = $this->url->link("module/{$this->module}/syncCats", "", "SSL");
        $this->data["syncProductsAction"] = $this->url->link("module/{$this->module}/syncProducts", "", "SSL");
        $this->data["checkBalance"] = $this->url->link("module/{$this->module}/checkBalance", "", "SSL");

        $this->data["{$this->module}_data"] = $this->model_setting_setting->getSetting($this->module);
        $this->data["breadcrumbs"] = array();
        $this->data["breadcrumbs"][] = array(
            "text" => $this->language->get("text_home"),
            "href" => $this->url->link("common/home", "", "SSL"),
            "separator" => false
        );

        $this->data["breadcrumbs"][] = array(
            "text" => $this->language->get("text_modules"),
            "href" => $this->url->link(
                "marketplace/home",
                "",
                "SSL"
          ),
            "separator" => " :: "
        );

        $this->data["breadcrumbs"][] = array(
            "text" => $this->language->get("{$this->module}_heading_title"),
            "href" => $this->url->link("module/{$this->module}", "", "SSL"),
            "separator" => " :: "
        );
        $this->template = "module/{$this->module}.expand";
        $this->children = array(
            "common/header",
            "common/footer",
        );
        $this->response->setOutput($this->render());
    }

    public function index()
    {
        $this->init();
    }


    public function updateSettings()
    {
        $this->load->language("module/{$this->module}");

        if ($this->validate()) {
            $this->load->model("setting/setting");

            if($this->request->post['like4card_app_status']){
                $this->request->post['like4card_app_status'] = 1;
            }
            else{
                $this->request->post['like4card_app_status'] = 0;

            }
            if($this->request->post['like4card_test_mode']){
                $this->request->post['like4card_test_mode'] = 1;
            }
            else{
                $this->request->post['like4card_test_mode'] = 0;                
            }  
                  
            $this->model_setting_setting->editSetting(
                $this->module, $this->request->post
            );
            $this->session->data["success"] = $result_json["success_msg"] = $this->language->get("text_success");
            $result_json["success"] = 1;
        } else {
            $result_json["success"] = 0;
            $this->error["{$this->module}_warning"] = $this->language->get("text_error");
            $result_json["error"] = $this->error;
        }

        $this->response->setOutput(json_encode($result_json));
        return;
    }

    public function checkBalance()
    {
        $this->load->language("module/{$this->module}");
        $this->load->model("module/{$this->module}");
        $model_path = "model_module_{$this->module}";
        $balance =  $this->$model_path->getBalance();

        if ($balance['response']) {
            $result_json["success_msg"] = $balance['balance']. " " .$balance['currency'];
            $result_json["success"] = 1;
        } else {
            $result_json["success"] = 0;
            $result_json["error"] = $this->language->get("error_wrong_credentials");
        }

        $this->response->setOutput(json_encode($result_json));
        return;
    }
    
    public function uploadProducts()    
    {
        $this->load->language("module/{$this->module}");

        if ($this->validateFile()) {
            $this->importProducts();
            $this->data["message"] = $this->language->get("text_upload_success");
        } else {
            $this->data["errors"] = $this->error;
        }
        $this->init();
    }

    public function mapCat($cat)
    {
        $mappedCat = array();
        $mappedCat['status'] = 1;
        $mappedCat['category_description']['2']['name'] = $cat['categoryName'];
        $mappedCat['category_description']['2']['description'] = $cat['categoryName'];
        $mappedCat['parent_id'] = 0;
        return $mappedCat;
    }

    public function syncCats()
    {
        $this->load->language("module/{$this->module}");
        $this->load->model('catalog/category');
        $this->load->model("module/{$this->module}");
        $model_path = "model_module_{$this->module}";
        $results =  $this->$model_path->getCats();
        like4cardLog($results);
        // save categories
        if($results['response']){
            foreach ($results['data'] as $cat) {
                $exist = false;
                $mappedCat = $this->mapCat($cat);
                like4cardLog($mappedCat);
                // check if cat has parent 
                if($cat['categoryParentId']!=0)
                {
                    $expand_parent_id = $this->$model_path->getExpandCatId($cat['categoryParentId']);
                    $mappedCat['parent_id'] = $expand_parent_id;
                }
                $mappedCat['image'] = $mappedCat['icon']  = $this->save_image($cat["amazonImage"], "cat");
                // check if cat exist then update if not add it
                $expand_cat_id = $this->$model_path->isCatExist($cat['id']);            
                if(!$expand_cat_id){
                    $expand_cat_id = $this->model_catalog_category->addCategory($mappedCat);
                    $cat['expand_cat_id'] = $expand_cat_id;  
                    $this->$model_path->insertLike4cardCat( $cat['id'], $cat['categoryParentId'] , $cat['expand_cat_id'], $mappedCat['parent_id']  );
                }
                else{
                    $this->model_catalog_category->editCategory($expand_cat_id, $mappedCat);
                }

                if(count($cat['childs'])){
                    // save childs
                    foreach ($cat['childs'] as $child) {
                        $mappedChild = $this->mapCat($child);
                        $mappedChild['parent_id'] = $cat['expand_cat_id'];
                        $mappedChild['image'] = $mappedChild['icon']  = $this->save_image($child["amazonImage"], "cat");
                        // check if child exist then update if not add it
                        $expand_child_id = $this->$model_path->isCatExist($child['id']);   
                        if(!$expand_child_id){
                            $expand_child_id = $this->model_catalog_category->addCategory($mappedChild);
                            $child['expand_cat_id'] = $expand_child_id;
                            $this->$model_path->insertLike4cardCat( $child['id'], $child['categoryParentId'] , $child['expand_cat_id'], $mappedChild['parent_id'] );
                        }
                        else{
                            $this->model_catalog_category->editCategory($expand_child_id, $mappedChild);
                        }         
                    }
                }
            }
            $this->data["message"] = $this->language->get("text_cats_sync_success");;
        }
        else{
            $this->data["errors"][] = $this->language->get("error_wrong_credentials");
        }        
        $this->init();
    }


    public function syncProducts()
    {
        set_time_limit(0);
        $this->load->language("module/{$this->module}");
        $this->load->model('catalog/product');
        $this->load->model("module/{$this->module}");

        $model_path = "model_module_{$this->module}";
        // getProducts() return sellable products
        $results =  $this->$model_path->getProducts();
        like4cardLog($results);
        if($results['response']){
            foreach ($results['data'] as $product) {
                // check at product_to_like4card if it has product_id value
                $expand_product_id =  $this->$model_path->getExpandProductId($product['productId']);
                $product['expand_cat_id'] =  $this->$model_path->getExpandCatId($product['categoryId']);
                $data = $this->formatProduct($product);
                like4cardLog([
                    'productId' => $product['productId'],
                    'categoryId' =>   $product['categoryId'],
                    'expand_product_id'=>$expand_product_id ,
                    'expand_cat_id' => $product['expand_cat_id'] ,
                    'data' => $data
                ]);
                if(isset($expand_product_id)){
                    //product exist then just update
                    like4cardLog(['editProduct']);
                    $this->model_catalog_product->editProduct($expand_product_id, $data);
                }
                else{
                    // if it is null then add it to products and
                    like4cardLog(['addProduct']);
                    $expand_product_id = $this->model_catalog_product->addProduct($data);
                    //  update the product_to_like4card with the new id
                    $this->$model_path->updateExpandProductId($expand_product_id,(int)$product['productId']);
                }
            }    
            $this->data["message"] = $this->language->get("text_products_sync_success");        
        }
        else{
            $this->data["errors"][] = $results['message'];
            $this->data["errors"][] = $this->language->get("error_wrong_credentials"); 
            $this->data["errors"][] = $this->language->get("error_upload_sheet_first");
        }
        $this->init();
    }

    protected function formatProduct($product) {
        $this->load->model('localisation/language');
        $languages = $this->model_localisation_language->getLanguages();

        foreach ($languages as $lang) {
            $temp["product_description"][$lang["language_id"]] = array(
                "name"              => $product['productName'],
                "description"       => $product['productName'],
                "meta_title"        => $product['productName'],
                "meta_description"  => "",
                "meta_keyword"      => "",
                "tag"               => "",
           );
        }

        $temp['price'] = $product['sellPrice'];
        $temp['cost_price'] = $product['productPrice'];
        $temp['price'] = $product['sellPrice'];
        $temp['product_store'] = array(0);
        if(isset($product['expand_cat_id']) && !empty($product['expand_cat_id']))
           $temp['product_category'] = array($product['expand_cat_id']);
        else
           $temp['product_category'] = array();
                             
        $temp['quantity'] = 1;
        $temp['date_available'] = date("Y-m-d", strtotime("-1 day"));
        $temp['tax_class_id'] = 0;
        $temp['shipping'] = 0;
        $temp['stock_status_id'] = $this->config->get('config_stock_status_id');
        $temp['unlimited'] = 0;
        // check availability
        if($product['available']){
            $temp['quantity'] = 1;
            $temp['status'] = 1;
            $temp['subtract'] = 0;
        }
        else{
            $temp['quantity'] = 0; 
            $temp['subtract'] = 1;
            $temp['status'] = 0;
        }

        if ($product["productImage"]) {
            $temp['image'] = $this->save_image($product["productImage"], "product");
        }

        return $temp;
    }

    public function importProducts()
    {         
        if($this->request->files) {
            $this->load->model('module/like4card');

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
                    //

                    $allDataInSheet = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
                    $i=0;
                    foreach($allDataInSheet as $k=> $value){
                        if($i == 0){
                            $i++;
                            continue;
                        }
                        else{
                            $i++;
                            $importdata=array(
                              'product_like4card_id'=> $value['A']
                            );  
                            $this->model_module_like4card->insertLike4cardProduct($importdata);
                        }
                    }
                } 
                else{
                    $this->error = $this->language->get('error_warning');
                }
            }
            else{
                $this->error = $this->language->get('error_warning');
                
            }
            if($inputFileName){
                unlink($inputFileName);
            }
        }
        else
        {
            $this->error = $this->language->get('error_warning'); 
        }
        return true;
    }


    public function install()
    {
        $this->language->load("module/{$this->module}");
        $this->load->model("module/{$this->module}");
        $model_path = "model_module_{$this->module}";
        $this->$model_path->install();
    }

    public function uninstall()
    {
        $this->language->load("module/{$this->module}");
        $this->load->model("module/{$this->module}");
        $model_path = "model_module_{$this->module}";
        $this->$model_path->uninstall();
    }

    /**
     *   Validate the input data.
     *
     * @return boolean
     */
    private function validate()
    {
        $this->load->language("module/{$this->module}");

        if (!$this->user->hasPermission("modify", "module/{$this->module}")) {
            $this->error[] = $this->language->get("error_permission");
            return false;
        }

        $data = $this->request->post;

        if (!isset($data["{$this->module}_device_id"]) || strlen($data["{$this->module}_device_id"]) == 0) {
            $this->error["{$this->module}_device_id"] = $this->language->get("error_device_id_required");
        }

        if ( !isset($data["{$this->module}_email"]) || (strlen($data["{$this->module}_email"]) == 0) || 
            !preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/", $data["{$this->module}_email"]) )  
        {
            $this->error["{$this->module}_email"] = $this->language->get("error_email_required");
        }    


        if (!isset($data["{$this->module}_phone"]) || strlen($data["{$this->module}_phone"]) == 0) {
            $this->error["{$this->module}_phone"] = $this->language->get("error_phone_required");
        }

        if (!isset($data["{$this->module}_password"]) || strlen($data["{$this->module}_password"]) == 0) {
            $this->error["{$this->module}_password"] = $this->language->get("error_password_required");
        }

        if (!isset($data["{$this->module}_security_code"]) || strlen($data["{$this->module}_security_code"]) == 0) {
            $this->error["{$this->module}_security_code"] = $this->language->get("error_security_code_required");
        }

        if (!isset($data["{$this->module}_secret_key"]) || strlen($data["{$this->module}_secret_key"]) == 0) {
            $this->error["{$this->module}_secret_key"] = $this->language->get("error_secret_key_required");
        }

        if (!isset($data["{$this->module}_hash_key"]) || strlen($data["{$this->module}_hash_key"]) == 0) {
            $this->error["{$this->module}_hash_key"] = $this->language->get("error_hash_key_required");
        }

        if (!isset($data["{$this->module}_secret_iv"]) || strlen($data["{$this->module}_secret_iv"]) == 0) {
            $this->error["{$this->module}_secret_iv"] = $this->language->get("error_secret_iv_required");
        }

        return count($this->error) > 0 ? false : true;
    }

    private function validateFile()
    {
        $this->load->language("module/{$this->module}");

        if (!$this->user->hasPermission("modify", "module/{$this->module}")) {
            $this->error[] = $this->language->get("error_permission");
            return false;
        }

        if (isset($this->request->files['import'])) {
            $file = basename($this->request->files['import']['name']);
            move_uploaded_file($this->request->files['import']['tmp_name'], TEMP_DIR_PATH . $file);
            $inputFileName = TEMP_DIR_PATH . $file;
            $extension = pathinfo($inputFileName);  
            $allowed_exts = array('xlsx','xls','csv');
            if(!in_array( $extension['extension'], $allowed_exts)){
                $this->error["{$this->module}_import_file"] = $this->language->get("error_import_file_extension");
            }
        }
        else{

            $this->error["{$this->module}_import_file"] = $this->language->get("error_import_file_required");
        }
        return count($this->error) > 0 ? false : true;
    }

    public function save_image($image_url, $type) {
        // $type may be product or cat
        if(!empty($image_url)) {
            if($type == "product")
                $directory = "image/data/like4cardProducts/";
            else
                $directory = "image/data/";

            if (!\Filesystem::isDirExists($directory)) {
                \Filesystem::createDir($directory);
                \Filesystem::setPath($directory)->changeMod("writable");
            }
            
            $image_name = explode( '/' , $image_url)[4];

            $full_image_path = $directory.$image_name;

            if($type == "product")
                $catalog_path = "data/like4cardProducts/".$image_name;
            else                
                $catalog_path = "data/".$image_name;

            file_put_contents($output, file_get_contents($input));
            if(\Filesystem::isExists($full_image_path)) {
                return $catalog_path;
            } else {
                $ch = curl_init ($image_url);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
                curl_setopt($ch, CURLOPT_TIMEOUT, 120);
                curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
                $raw_image_data = curl_exec($ch);

                curl_close ($ch);
                try{
                    \Filesystem::setPath($full_image_path)->put($raw_image_data);
                    return $catalog_path;
                }catch(Exception $e) { 
                    return false;
                }
            }
        }
        return false;
    }

}