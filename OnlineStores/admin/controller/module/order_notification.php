<?php

/**
 * Controller Class for order_notification Module
 *
 * @author Fayez.
 */
class ControllerModuleOrderNotification extends Controller
{
    private $error = [];
    public $route = "module/order_notification";
    public $module = "order_notification";

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

    public function getOrdersNotifications() {

        if (!$this->user->hasPermission("access", "module/{$this->module}")) {
            return;
        }

        $this->load->model("module/{$this->module}");
        $model_path = "model_module_{$this->module}";

        $_order_notifications = $this->$model_path->getOrdersNotifications();

        if (empty($_order_notifications)) {
            return;
        }

        $order_notifications = [];
        $user_id = $this->user->getId();
        $user_date_added = $this->$model_path->getUserDateAdded($this->user->getUsername());
        foreach ($_order_notifications as $order_notification) {
            $user_ids = $order_notification['desktop_notified_ids'] ? unserialize($order_notification['desktop_notified_ids']) : [];

            if (!in_array($user_id, $user_ids) && $order_notification['created_at'] > $user_date_added ) {
                $user_ids[] = $this->user->getId();
                $user_ids = serialize($user_ids);

                $this->$model_path->updateDesktopNotified($order_notification['id'], $user_ids);

               $order_notifications[] = $order_notification;
            }
        }

        $this->load->language("module/{$this->module}");

        $config = [
            'text_new_order_title' => $this->language->get("text_new_order_title"),
            'text_new_return_title' => $this->language->get("text_new_return_title"),
            'text_new_order_body' => $this->language->get("text_new_order_body"),
            'text_new_return_body' => $this->language->get("text_new_return_body"),
            'order_url' => "sale/order/info?order_id=",
            'return_url' => "sale/return/info?return_id=",
        ];

        return $this->response->setOutput(json_encode(compact('order_notifications', 'config')));
    }
}