<?php

/**
 *   Controller Class for payThem Module
 *
 * @author Fayez.
 */
class ControllerModulepayThem extends Controller
{
    private $error = [];
    public $route = "module/payThem";
    public $module = "payThem";

    public function index()
    {
        if (!\Extension::isInstalled($this->module) || !$this->user->hasPermission("modify", "module/{$this->module}")) {
            return $this->forward("error/permission");
        }

        $this->load->model("setting/setting");
        $this->load->language("module/{$this->module}");

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
                
        $this->data["action"] = $this->url->link("module/{$this->module}/updateSettings", "", "SSL");
        $this->data["import_url"] = $this->url->link("module/{$this->module}/importProducts", "", "SSL");

        $this->data["{$this->module}_data"] = $this->model_setting_setting->getSetting($this->module);

        $this->template = "module/{$this->module}.expand";

        $this->children = array(
            "common/header",
            "common/footer",
      );

        $this->response->setOutput($this->render());
    }


    public function updateSettings()
    {
        $this->load->language("module/{$this->module}");

        if ($this->validate()) {
            $this->load->model("setting/setting");

            if (isset($this->request->get["store_id"])) {
                $store_id = $this->request->get["store_id"];
            } else {
                $store_id = 0;
            }

            $this->model_setting_setting->editSetting(
                $this->module, $this->request->post
          );

            $this->session->data["success"] = $result_json["success_msg"] = $this->language->get("text_success");
            $result_json["success"] = "1";
        } else {
            $result_json["success"] = "0";
            $this->error["{$this->module}_warning"] = $this->language->get("text_error");
            $result_json["error"] = $this->error;
        }

        $this->response->setOutput(json_encode($result_json));
        return;
    }


    public function install()
    {
        $this->language->load("module/{$this->module}");

        if (!$this->user->hasPermission("modify", "module/{$this->module}")) {
            
            return $this->forward("error/permission");

        } else {

            $this->load->model("user/user_group");
            $this->model_user_user_group->addPermission(
                    $this->user->getId(),
                    "access", "module/{$this->module}"
            );
            $this->model_user_user_group->addPermission(
                    $this->user->getId(),
                    "modify", "module/{$this->module}"
            );

            $this->load->model("module/{$this->module}");
            $model_path = "model_module_{$this->module}";

            $this->$model_path->install();

            $this->redirect($this->url->link("module/{$this->module}", "", "SSL"));
        }
    }


    public function uninstall()
    {
        $this->language->load("module/{$this->module}");

        if (!$this->user->hasPermission("modify", "module/{$this->module}")) {

            return $this->forward("error/permission");

        } else {

            $this->load->model("module/{$this->module}");
            $model_path = "model_module_{$this->module}";

            $this->$model_path->uninstall();

            $this->redirect($this->url->link("marketplace/home", "", "SSL"));
        }
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

        if (!isset($data["{$this->module}_username"]) || strlen($data["{$this->module}_username"]) == 0) {
            $this->error["{$this->module}_username"] = $this->language->get("error_username_required");
        }

        if (!isset($data["{$this->module}_password"]) || strlen($data["{$this->module}_password"]) == 0) {
            $this->error["{$this->module}_password"] = $this->language->get("error_password_required");
        }

        if (!isset($data["{$this->module}_public_key"]) || strlen($data["{$this->module}_public_key"]) == 0) {
            $this->error["{$this->module}_public_key"] = $this->language->get("error_public_key_required");
        }

        if (!isset($data["{$this->module}_private_key"]) || strlen($data["{$this->module}_private_key"]) == 0) {
            $this->error["{$this->module}_private_key"] = $this->language->get("error_private_key_required");
        }

        if (!isset($data["{$this->module}_timezone"]) || strlen($data["{$this->module}_timezone"]) == 0) {
            $this->error["{$this->module}_timezone"] = $this->language->get("error_timezone_required");
        }
        return count($this->error) > 0 ? false : true;
    }


    public function importProducts() {

        set_time_limit(0);

        require_once "PayThemAPI/class.PTN_API_v2.php";

        //$app_id = $this->config->get("{$this->module}_app_id") ? $this->config->get("{$this->module}_app_id") : "2824";
        $app_mode = $this->config->get('payThem_test_mode') == 1 ? "demo" : "";
        $api = new PTN_API_v2($app_mode, "2824");
        $api->PUBLIC_KEY = $this->config->get("{$this->module}_public_key");
        $api->PRIVATE_KEY = $this->config->get("{$this->module}_private_key");
        $api->USERNAME = $this->config->get("{$this->module}_username");
        $api->PASSWORD = $this->config->get("{$this->module}_password");
        $api->FUNCTION = "get_ProductList";
        $api->SERVER_TIMESTAMP = (new DateTime("now", new DateTimeZone($this->config->get("{$this->module}_timezone"))))->format('Y-m-d H:i:s');
        $api->SERVER_TIMEZONE = $this->config->get("{$this->module}_timezone");
        $res = $api->callAPI(false);
        
        if (!$res || (isset($res["CONTENT"]) && $res["CONTENT"] == "ERROR")) {
            return $this->response->setOutput(json_encode([
                "status" => "error"
            ]));
        }

        $currencies = $this->currency->getCurrencies();

        foreach ($res as $product) {
            if (empty($currencies[$product['OEM_PRODUCT_BaseCurrencySymbol']]))
                continue;

            $product_data = $this->formatProduct($product);
            
            $this->load->model('catalog/product');
            $payThemProduct = $this->model_catalog_product->getPayThemProductByOEMID($product['OEM_PRODUCT_ID']);

            if (!empty($payThemProduct)) {
                $this->model_catalog_product->editProduct($payThemProduct['product_id'], $product_data);
            } else {
                $this->model_catalog_product->addProduct($product_data);
            }
        }

        return $this->response->setOutput(json_encode([
            "status" => "success"
        ]));

    }


    protected function formatProduct($product) {
        if(empty($product)) {
            return $product;
        }

        $temp = array(
            "product_description" => array(),
            "model"             => "",
            "sku"               => "",
            "upc"               => "",
            "ean"               => "",
            "jan"               => "",
            "isbn"              => "",
            "mpn"               => "",
            "location"          => "",
            "points"            => "",
            "tax_class_id"      => "0",
            "quantity"          => $product["OEM_PRODUCT_Available"],
            "minimum"           => "1",
            "subtract"          => "1",
            "stock_status_id"   => "5",
            "shipping"          => "1",
            "date_available"    => date("Y-m-d", strtotime("-1 day")),
            "length"            => "",
            "width"             => "",
            "height"            => "",
            "length_class_id"   => "1",
            "weight"            => "",
            "weight_class_id"   => "1",
            "status"            => "1",
            "sort_order"        => "0",
            "manufacturer"      => $product["OEM_BRAND_Name"],
            "manufacturer_id"   => "0",
            "product_store"     => array(0),
            "product_category"  => array(),
            "product_option"    => array(),
            "product_attribute" => array(),
            "image"             => "",
            "price"             => round($this->currency->convert(
                                        $product["OEM_PRODUCT_SellPrice"],
                                        $product["OEM_PRODUCT_BaseCurrencySymbol"], 
                                        'USD'
                                    ), 2),
            "cost_price"        => round($this->currency->convert(
                                        $product["OEM_PRODUCT_UnitPrice"],
                                        $product["OEM_PRODUCT_BaseCurrencySymbol"], 
                                        'USD'
                                    ), 2),
            "OEM_ID"            => $product["OEM_ID"],
            "OEM_Name"          => $product["OEM_Name"],
            "OEM_PRODUCT_ID"    => $product["OEM_PRODUCT_ID"],
            "OEM_PRODUCT_VVSSKU"=> $product["OEM_PRODUCT_VVSSKU"]
        );

        if($product["OEM_PRODUCT_Available"] > 0) {
            $temp["stock_status_id"] = "7";
            $temp["status"] = "1";
        } else {
            $temp["stock_status_id"] = "5";
            $temp["status"] = "0";
        }

        $this->load->model('localisation/language');
        $languages = $this->model_localisation_language->getLanguages();

        foreach ($languages as $key => $lng) {
            
            $lng_code = explode("-", $lng["code"]);
            $lng_code = $lng_code[0];

            $product_name = $product["OEM_PRODUCT_Name"];
            $product_desc = $product["OEM_PRODUCT_RedemptionInstructions"];

            $temp["product_description"][$lng["language_id"]] = array(
                "name"              => $product_name,
                "description"       => !empty($product_desc) ? $product_desc : $product_name,
                "meta_title"        => $product_name,
                "meta_description"  => "",
                "meta_keyword"      => "",
                "tag"               => "",
           );
        }

        if ($product["OEM_PRODUCT_ImageURL"]) {

            $parsed_image = $this->save_image($product["OEM_PRODUCT_ImageURL"], $product["OEM_PRODUCT_VVSSKU"]);
            if ($parsed_image) {
                $temp_image["image"] = $parsed_image;
                $temp_image["sort_order"] = "0";
                $temp["product_image"][] = $temp_image;
            }
        }

        if ($product["OEM_BRAND_Name"]) {

            $this->load->model('catalog/manufacturer');

            $manufacturer = $this->model_catalog_manufacturer->getManufacturerByName($product["OEM_BRAND_Name"]);
            if (!$manufacturer) {
                $manufacturer = $this->model_catalog_manufacturer->addManufacturer([
                    'name' => $product["OEM_BRAND_Name"]
                ]); 
            }

            $temp['manufacturer'] = $manufacturer['name'];     
            $temp['manufacturer_id'] = $manufacturer['id'];     
        }

        return $temp;
    }


    public function save_image($image_url, $prefix = "") {
        if(!empty($image_url)) {
            
            $directory = "image/data/payThemProducts/";
            
            if (!\Filesystem::isDirExists($directory)) {
                \Filesystem::createDir($directory);
                \Filesystem::setPath($directory)->changeMod("writable");
            }
            
            $image_name = explode('=', $image_url)[1] . '.jpg';

            $full_image_path = $directory.$image_name;
            $catalog_path = "data/products/" . urldecode($image_name);

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