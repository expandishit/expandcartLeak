<?php

set_time_limit(0);
ini_set("memory_limit", '2048M');

class ControllerModuleKnawat extends Controller
{

    private $error = [];
    private $route = 'module/knawat';

    public function index()
    {
        $this->load->model('setting/setting');
        $this->load->model($this->route);
        $this->load->language($this->route);
        $this->load->model('localisation/order_status');
        $this->document->setTitle($this->language->get('heading_title'));
        //set breadcrumbs
        $data['breadcrumbs'] = [
            [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/home', '', 'SSL'),
                'separator' => false
            ],
            [
                'text' => $this->language->get('text_modules'),
                'href' => $this->url->link('marketplace/home', '', 'SSL'),
                'separator' => ' :: '
            ],
            [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('module/zapier', '', 'SSL'),
                'separator' => ' :: '
            ]
        ];

        //get knawat settings
        if ($this->model_module_knawat->isInstalled()) {
            $data['knawat'] = $this->model_module_knawat->getSettings();

            //check if install process is complete
            if(!$data['knawat']['install_completed'])
                return $this->redirect($this->url->link('module/knawat/integrate'));
            $data['knawat']['store'] = json_decode(json_encode($data['knawat']['store']), true);
            $data['orders_count'] = $this->model_module_knawat->getOnProgressOrdersCount();
            $data['products_count'] = $this->model_module_knawat->getImportedProductsCount();
            $this->model_module_knawat->updateKnwatVersion();
        }

        //get order statuses
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        //set knawat urls
        $data['knawat_how_work_url'] = "#";
        $data['knawat_login_url'] = "#";
        $data['knawat_register_url'] = "#";
        $data['knawat_products_url'] = "#";
        $data['knawat_orders_url'] = "https://app.knawat.com/orders";
        $data['knawat_settings_url'] = "https://app.knawat.com/store/settings/info";
        $data['knawat_sync_products_url'] = $this->url->link('module/knawat/syncProducts', '', 'SSL');;
//        $data['uninstall'] = $this->url->link('module/knawat/uninstall', '', 'SSL');
        $data['uninstall_url'] = $this->url->link('/admin/marketplace/home/uninstall?extension=knawat', '', 'SSL');
        $data['action'] = $this->url->link('module/knawat/updateSettings', '', 'SSL');
        $data['cancel'] = $this->url->link('marketplace/home', '', 'SSL');
        $data['update_version_url'] = $this->url->link('module/knawat/updateVersion', '', 'SSL');

        $this->template = 'module/knawat/settings.expand';
        $this->children = array('common/header', 'common/footer',);
        
        if (isset($_GET['xxz'])) {
            $this->model_module_knawat->updateSettings(['sync' => []]);
        }
        
        $data['available_updates'] = false;
        // check knawat version
        $knwatSettings = $this->config->get('knawat');
        if (
            isset($knwatSettings['knawat_version']) &&
            $knwatSettings['knawat_version'] < $this->model_module_knawat->app_version
        ) {
            $data['available_updates'] = true;
        }

        $data['sync'] = ['sync_status' => 1];
        if (isset($knwatSettings['sync'])) {
            $data['sync'] = $knwatSettings['sync'];
        }

        $data['supported_knawat_currency'] = true;
        if (isset($knwatSettings['knawat_currency'])) {
            $data['supported_knawat_currency'] = $this->currency->has($knwatSettings['knawat_currency']);
        }

        $this->data = $data;

        $this->response->setOutput($this->render());

    }

    public function integrate(){
        $this->load->model('setting/setting');
        $this->load->model($this->route);
        $this->load->language($this->route);

        $this->document->setTitle($this->language->get('heading_title'));
        //set breadcrumbs
        $data['breadcrumbs'] = [
            [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/home', '', 'SSL'),
                'separator' => false
            ],
            [
                'text' => $this->language->get('text_modules'),
                'href' => $this->url->link('marketplace/home', '', 'SSL'),
                'separator' => ' :: '
            ],
            [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('module/zapier', '', 'SSL'),
                'separator' => ' :: '
            ]
        ];

        $data['knawat'] = $this->model_module_knawat->getSettings();
        //check if install process is complete then redirect to setting page
        if($data['knawat']['install_completed'])
            return $this->redirect($this->url->link('module/knawat'));


        $this->data['check_installing_url'] = $this->url->link('module/knawat/checkInstalling', '', 'SSL');
        $this->template = 'module/knawat/integrate.expand';
        $this->children = array('common/header', 'common/footer',);
        $this->response->setOutput($this->render());
    }

    public function updateSettings()
    {
        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)) {
            $result_json['success'] = '0';
            $result_json['error'] = $this->error;
        } else {

            //make validation
            if (!$this->validate()) {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
                return $this->response->setOutput(json_encode($result_json));

            }

            $this->load->model($this->route);
            $this->load->language($this->route);

            $inputs = [
                'push_order_status_id' => $this->request->post['push_order_status_id']
            ];

            //update knawat configurations
            $this->model_module_knawat->updateSettings($inputs);

            $this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success');
            $result_json['success'] = '1';

        }

        return $this->response->setOutput(json_encode($result_json));
    }

    protected function validate()
    {

        $this->load->language($this->route);
        if (!$this->user->hasPermission('modify', $this->route)) {
            $this->error['warning'] = $this->language->get('error_permission');
            return false;
        }

        return true;
    }

    public function install()
    {
        $this->load->model($this->route);
        $this->model_module_knawat->install();
    }

    public function uninstall()
    {
        $this->load->model($this->route);
        $this->model_module_knawat->uninstall();
    }

    public function syncProducts()
    {
        $this->load->language($this->route);
        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST) || !$this->user->hasPermission('modify', $this->route)) {
            $result_json['success'] = '0';
            $result_json['error'] = $this->error;
        } else {
            $this->load->model($this->route);

            $knwatSettings = $this->config->get('knawat');

            if (isset($knwatSettings['sync']) && isset($knwatSettings['sync']['sync_status']) && $knwatSettings['sync']['sync_status'] == 0) {
                return false;
            }
            exec('php /var/www/ectools/artisan db:snapshot ' . STORECODE . ' --knawat');
            $async = $this->model_module_knawat->asyncKnawatProducts();

            if ($async['status'] == 'OK') {
                $result_json['success_msg'] = $this->language->get('text_success');
                $result_json['success'] = '1';
                $result_json['redirect'] = (string) $this->url->link('module/knawat');
            } else if ($async['status'] == 'ERR') {
                $result_json['error'] = $async['errors'][0]['message'];
                $result_json['success'] = '0';
            } else {
                $result_json['error'] = $this->language->get('text_sync_error');
                $result_json['success'] = '0';
            }

            return $this->response->setOutput(json_encode($result_json));
            /*$products_count = $this->model_module_knawat->syncKnawatProducts();

            $result_json['products_count'] = $products_count;
            $result_json['success_msg'] = $this->language->get('text_success');
            $result_json['success'] = '1';*/
        }




        return $this->response->setOutput(json_encode($result_json));
    }

    public function checkInstalling()
    {
        $result_json['success'] = '0';
        if ($this->request->server['REQUEST_METHOD'] = 'POST') {
            $this->load->model($this->route);
            if ($this->model_module_knawat->isInstalled()){
                $data= $this->model_module_knawat->getSettings();

                if($data['install_completed'])
                    $result_json['success'] = '1';
            }

        }

        return $this->response->setOutput(json_encode($result_json));
    }

    function updateVersion(){
        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST) || !$this->user->hasPermission('modify', $this->route)) {
            $result_json['success'] = '0';
            $result_json['error'] = $this->error;
        } else {
            $this->load->model($this->route);
            $this->model_module_knawat->updateKnwatVersion();

            $result_json['success'] = '1';
        }
        return $this->response->setOutput(json_encode($result_json));
    }

}
