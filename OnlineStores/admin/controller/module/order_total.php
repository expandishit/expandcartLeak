<?php

/**
 * Controller Class for order_notification Module
 *
 * @author Fayez.
 */
class ControllerModuleOrderTotal extends Controller
{
    private $error = [];
    public $route = "module/order_total";
    public $module = "order_total";

    public function index()
    {
        $this->redirect($this->url->link('extension/total'));
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
        }
    }

}