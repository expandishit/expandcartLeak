<?php

/**
 *   Controller Class for SizeChart Module
 *
 * @author Fayez.
 */
class ControllerModuleSizeChart extends Controller {
    private $error = [];
    public $route = "module/size_chart";
    public $module = "size_chart";

    public function index() {

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

        $this->document->setTitle($this->language->get('heading_title'));

        $this->data["{$this->module}_data"] = $this->model_setting_setting->getSetting($this->module);

        $this->load->model("module/{$this->module}");
        $model_path = "model_module_{$this->module}";
        $this->data["charts"] = $this->$model_path->getCharts($this->config->get('config_language_id'));

        $this->data["action"] = $this->url->link("module/{$this->module}/updateSettings", "", "SSL");

        $this->template = "module/{$this->module}.expand";

        $this->children = array(
            "common/header",
            "common/footer",
      );

        $this->response->setOutput($this->render());
    }


    public function updateSettings() {
        $this->load->language("module/{$this->module}");

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

        $this->response->setOutput(json_encode($result_json));
        return;
    }


    public function install() {
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


    public function uninstall() {
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
     * Validate the input data.
     */
    private function validate() {
        $this->load->language("module/{$this->module}");

        if (!$this->user->hasPermission("modify", "module/{$this->module}")) {
            $this->error[] = $this->language->get("error_permission");
            return false;
        }

        $data = $this->request->post;

        if (empty(($data['product_id']))) {
            $this->error["product_id"] = $this->language->get("error_product_id_required");
        }

        if (empty(($data['product_category']))) {
            $this->error["product_category"] = $this->language->get("error_product_category_required");
        }

        // if (empty(($data['country_id']))) {
        //     $this->error["country_id"] = $this->language->get("error_country_id_required");
        // }

        if (empty(($data['chart_row_size']))) {
            $this->error["chart_row_size"] = $this->language->get("error_chart_row_size_required");
        }

        if (empty(($data['chart_col_size']))) {
            $this->error["chart_col_size"] = $this->language->get("error_chart_col_size_required");
        }

        if (empty(($data['chart_sizes']))) {
            $this->error["chart_sizes"] = $this->language->get("error_chart_sizes_required");
        }

        if (empty(($data['size_chart_description']))) {

            $this->error["size_chart_description"] = $this->language->get("error_size_chart_description_required");

        } else {

            $this->load->model('localisation/language');
            $languages = $this->model_localisation_language->getLanguages();

            foreach ($languages as $language) {
                if (empty(($data['size_chart_description'][$language['language_id']]['name']))) {
                    $this->error["chart_size_name_{$language['code']}"] = $this->language->get("error_chart_size_name_{$language['code']}_required");
                }
                if (empty(($data['size_chart_description'][$language['language_id']]['description']))) {
                    $this->error["chart_size_description_{$language['code']}"] = $this->language->get("error_chart_size_description_{$language['code']}_required");
                }
            }
        }

        if ($this->error && !isset($this->error['error'])) {
           $this->error['warning'] = $this->language->get('error_warning');
        }

        return count($this->error) > 0 ? false : true;
    }


    public function insert() {
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
                
        $this->document->setTitle($this->language->get('heading_title'));

        $this->data["action"] = $this->url->link("module/{$this->module}/postSaveSizeChart", "", "SSL");

        $this->data["{$this->module}_data"] = $this->model_setting_setting->getSetting($this->module);

        // Categories
        $this->load->model('catalog/category');
        $categories = $this->model_catalog_category->getCategories();

        $this->data['product_categories'] = array();

        foreach ($categories as $category) {
            $categoryInfo = $this->model_catalog_category->getCategory($category['category_id']);

            if ($categoryInfo) {
                $this->data['product_categories'][] = array(
                    'category_id' => $categoryInfo['category_id'],
                    'name' => ($categoryInfo['path'] ? $categoryInfo['path'] . ' &gt; ' : '') . $categoryInfo['name']
                );
            }
        }

        $this->load->model('localisation/country');
        $this->data['countries'] = $this->model_localisation_country->getCountries();

        // $this->load->model('localisation/geo_zone');
        // $this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
        
        $this->load->model("catalog/product");
        $this->data['products'] = $this->model_catalog_product->getProductsNamesList();

        $this->load->model('localisation/language');
        $this->data['languages'] = $this->model_localisation_language->getLanguages();

        $this->template = "module/{$this->module}_form.expand";

        $this->children = array(
            "common/header",
            "common/footer",
        );

        $this->response->setOutput($this->render());
    }

    public function postSaveSizeChart() {
        
        if (!$this->validate()) {
            $result_json['success'] = '0';
            $result_json['errors'] = $this->error;
            return $this->response->setOutput(json_encode($result_json));
        }

        $data = $this->request->post;

        $size_chart_id = $this->request->post['size_chart_id'] ? $this->request->post['size_chart_id'] : '';
        $products_ids = $this->request->post['product_id'] ? $this->request->post['product_id'] : [];
        $categories_ids = $this->request->post['product_category'] ? $this->request->post['product_category'] : [];
        $countries_ids = $this->request->post['country_id'] ? $this->request->post['country_id'] : [];
        $size_chart_description = $this->request->post['size_chart_description'] ? $this->request->post['size_chart_description'] : [];
        $chart_row_size = $this->request->post['chart_row_size'] ? $this->request->post['chart_row_size'] : '';
        $chart_col_size = $this->request->post['chart_col_size'] ? $this->request->post['chart_col_size'] : '';
        $chart_sizes = $this->request->post['chart_sizes'] ? $this->request->post['chart_sizes'] : [];


        $this->load->model("module/{$this->module}");
        $model_path = "model_module_{$this->module}";

        if ($size_chart_id) {

            // update size chart
            $this->$model_path->updateChart(compact('size_chart_id', 'chart_row_size', 'chart_col_size', 'chart_sizes'));

            // delete size chart relations
            $this->$model_path->deleteChartDependancies($size_chart_id);

        } else {

            // insert size chart
            $size_chart_id = $this->$model_path->insertSizeChart(compact('chart_row_size', 'chart_col_size', 'chart_sizes'));

        }
       
        // insert localizations
        $this->$model_path->insertSizeChartDetails(compact('size_chart_id', 'size_chart_description'));

        // insert relationables
        $this->$model_path->insertSizeChartRelationables(compact('size_chart_id', 'categories_ids', 'products_ids', 'countries_ids'));

        $result_json['success'] = '1';
        $result_json['success_msg'] = $this->language->get('text_success');
        $result_json['redirect'] = '1';
        $result_json['to'] = $this->url->link(
            'module/size_chart',
            '',
            'SSL'
        )->format();
        
        return $this->response->setOutput(json_encode($result_json));
    }


    public function update() {

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

        $this->document->setTitle($this->language->get('heading_title'));
        
        $this->data["action"] = $this->url->link("module/{$this->module}/postSaveSizeChart", "", "SSL");

        // Categories
        $this->load->model('catalog/category');
        $categories = $this->model_catalog_category->getCategories();

        $this->data['product_categories'] = array();

        foreach ($categories as $category) {
            $categoryInfo = $this->model_catalog_category->getCategory($category['category_id']);

            if ($categoryInfo) {
                $this->data['product_categories'][] = array(
                    'category_id' => $categoryInfo['category_id'],
                    'name' => ($categoryInfo['path'] ? $categoryInfo['path'] . ' &gt; ' : '') . $categoryInfo['name']
                );
            }
        }

        $this->load->model('localisation/country');
        $this->data['countries'] = $this->model_localisation_country->getCountries();

        $this->load->model('localisation/language');
        $this->data['languages'] = $this->model_localisation_language->getLanguages();

        $this->load->model("module/{$this->module}");
        $model_path = "model_module_{$this->module}";

        $this->data['chart'] = $this->$model_path->getChart($this->request->get['chart_id']);

        $this->data['chart']['size_chart']['chart_sizes'] = json_encode(unserialize($this->data['chart']['size_chart']['chart_sizes']));

        $size_chart_details = [];
        foreach ($this->data['chart']['size_chart_details'] as $detail) {
            $size_chart_details[$detail['lang_id']] = $detail;
        }

        $this->data['chart']['size_chart_details'] = $size_chart_details;

        $this->load->model("catalog/product");
        $this->data['products'] = $this->model_catalog_product->getProductsNamesList();

        $this->template = "module/{$this->module}_form.expand";

        $this->children = array(
            "common/header",
            "common/footer",
        );

        $this->response->setOutput($this->render());
    }


    public function deleteChart() {

        $this->load->model("module/{$this->module}");
        $model_path = "model_module_{$this->module}";

        $this->$model_path->deleteChart($this->request->post['chartId']);

        $result_json['success'] = '1';
        $result_json['success_msg'] = $this->language->get('text_success');
        $this->response->setOutput(json_encode($result_json));
        return;
    }


}