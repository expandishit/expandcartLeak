<?php

/**
 *   Controller Class for product_sort Module
 *
 * @author Fayez.
 */
class ControllerModuleProductSort extends Controller
{
    private $error = [];
    public $route = "module/product_sort";
    public $module = "product_sort";

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
        $this->data["update_position_url"] = $this->url->link("module/{$this->module}/updatePostions", "", "SSL");

        $this->data["{$this->module}_data"] = $this->model_setting_setting->getSetting($this->module);

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('catalog/product');
        $this->data["products_list"] = $this->model_catalog_product->getProductsList();

        $this->load->model('catalog/category');
        $this->data['categories'] = $this->model_catalog_category->getCategories();

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

    public function updatePostions() {

        $this->load->model("module/{$this->module}");
        $model_path = "model_module_{$this->module}";

        if ($this->request->post['selected_category_id'] && count($this->request->post['positions']) > 0) {            
           $this->$model_path->updateCategoryProductsPostions($this->request->post['positions'], $this->request->post['selected_category_id']);
        } else { 
            foreach ($this->request->post['positions'] as $position) {
                $this->$model_path->updatePostion($position[0], $position[1]);
            } 
        }
    }

    public function getProducts(){
        $category_ids = $this->request->post['category_id'] ? [$this->request->post['category_id']] : [];
        
        $this->load->model('catalog/product');
        $products = $this->model_catalog_product->get_products_for_sort($category_ids);

        $this->response->setOutput(json_encode($products));
        return ;
    }

}
