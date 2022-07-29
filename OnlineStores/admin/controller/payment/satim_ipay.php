<?php

class ControllerPaymentSatimIpay extends Controller
{

    private $error = array();
    private $paymentName = "satim_ipay";

    public function index()
    {

        $this->language->load("payment/{$this->paymentName}");

        $this->load->model("setting/setting");
        $this->load->model("localisation/geo_zone");
        $this->load->model("localisation/order_status");
        $this->load->model("localisation/language");

        $this->document->setTitle($this->language->get("heading_title"));

        $this->breadCrumbs($this->data);
        
        $this->data["action"] = $this->url->link("payment/{$this->paymentName}/submitPaymentData", "token=" . $this->session->data["token"], "SSL");
        $this->data["cancel"] = $this->url->link("payment/{$this->paymentName}", "token=" . $this->session->data["token"], "SSL");

        // form fields
        $formFields = [
            "status", "test_mode", "user_name", "password", "force_terminal_id", "completed_order_status_id", 
            "geo_zone_id","failed_order_status_id"
        ];

        $settings = $this->model_setting_setting->getSetting("{$this->paymentName}");
 
        foreach ($formFields as $field) {
            $this->data["{$this->paymentName}_{$field}"] = $settings["{$this->paymentName}_{$field}"];
        }

        $this->data["order_statuses"] = $this->model_localisation_order_status->getOrderStatuses();
        $this->data["geo_zones"] = $this->model_localisation_geo_zone->getGeoZones();

        // get payment settings
        $settings = $this->model_setting_setting->getSetting("{$this->paymentName}");

        // append sys languages
        $this->data["languages"] = $languages = $this->model_localisation_language->getLanguages();

        // get payment display field name for each language 
        foreach ($languages as $language) {
            $this->data["{$this->paymentName}_field_name_{$language["language_id"]}"] = $settings["{$this->paymentName}_field_name_{$language["language_id"]}"];
        }

        $this->template = "payment/{$this->paymentName}.expand";
        $this->children = array(
            "common/header",
            "common/footer"
        );

        $this->response->setOutput($this->render());
    }
    /**
     * validate post data 
     * 
     * @return bool
     */
    private function validate()
    {

        $this->language->load("payment/{$this->paymentName}");

        if (!$this->user->hasPermission("modify", "payment/{$this->paymentName}")) {
            $this->error["error"] = $this->language->get("error_permission");
        }

        if (!$this->request->post["{$this->paymentName}_user_name"]) {
            $this->error["{$this->paymentName}_user_name"] = $this->language->get("error_user_name");
        }
        
        if (!$this->request->post["{$this->paymentName}_password"]) {
            $this->error["{$this->paymentName}_password"] = $this->language->get("error_password");
        }
        
        if (!$this->request->post["{$this->paymentName}_force_terminal_id"]) {
            $this->error["{$this->paymentName}_force_terminal_id"] = $this->language->get("error_force_terminal_id");
        }

        return $this->error ? false : true;
    }
    /**
     * append breadcrumbs to data
     * 
     * @param &data array
     */
    private function breadCrumbs(&$data)
    {
        $this->data["breadcrumbs"][] = array(
            "text" => $this->language->get("text_home"),
            "href" => $this->url->link("common/home", "token=" . $this->session->data["token"], "SSL"),
            "separator" => false
        );
        $this->data["breadcrumbs"][] = array(
            "text" => $this->language->get("text_payment"),
            "href" => $this->url->link("extension/payment", "token=" . $this->session->data["token"], "SSL"),
            "separator" => " :: "
        );
        $this->data["breadcrumbs"][] = array(
            "text" => $this->language->get("heading_title"),
            "href" => $this->url->link("payment/{$this->paymentName}", "token=" . $this->session->data["token"], "SSL"),
            "separator" => " :: "
        );

    }
    /**
     * handle payment submit(POST) call
     */
    public function submitPaymentData()
    {
        if (!$this->validate()) {
            $result["success"] = "0";
            $result["errors"] = $this->error;
            $this->response->setOutput(json_encode($result));
            return;
        }
        // save data into db
        $result = $this->savePaymentIntoDB($this->request->post);
        $this->response->setOutput(json_encode($result));
        return;
    }
    /**
     * @save data into DB
     * 
     * @return json
     */
    private function savePaymentIntoDB($data) 
    {
        $this->load->model("setting/setting");
        $settingsModel=$this->model_setting_setting;

        $result = [];
        $settingsModel->checkIfExtensionIsExists("payment", "{$this->paymentName}", true);
        $settingsModel->editGuideValue("GETTING_STARTED", "PAYMENT", "1");
        $settingsModel->insertUpdateSetting("{$this->paymentName}", $data);
        $this->session->data["success"] = $result_json["success_msg"] = $this->language->get("text_success");
        $result["success"] = "1";

        return $result;
    }
    
}
