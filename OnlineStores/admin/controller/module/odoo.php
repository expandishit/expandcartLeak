<?php
class ControllerModuleOdoo extends Controller
{

    /**
     * @var Class Odoo model
     */
    private $module;

    /**
     * Errors bag
     *
     * @var array
     */
    private $errorsBag = [];

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->module = $this->load->model("module/odoo/settings", ['return' => true]);
    }

    public function install()
    {
        $this->module->install();
    }

    public function uninstall()
    {
        $this->module->uninstall();
    }

    public function index()
    {
        $this->language->load('module/odoo');

        $this->document->setTitle($this->language->get('heading_title'));

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_modules'),
            'href'      => $this->url->link(
                'marketplace/home',
                '',
                'SSL'
            ),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('module/odoo', '', 'SSL'),
            'separator' => ' :: '
        );

        if ($this->module->isInstalled()) {
            $data['settings'] = $this->module->getSettings();
        }

        $this->template = 'module/odoo.expand';

        $this->children = array(
            'common/header',
            'common/footer',
        ); 
        $data['odoo']['sync_customers_url'] = '' . $this->url->link('module/odoo/syncCustomers', '', 'SSL');
        $data['odoo']['sync_products_url'] = '' . $this->url->link('module/odoo/syncProducts', '', 'SSL');
        $data['odoo']['sync_orders_url'] = '' . $this->url->link('module/odoo/syncOrders', '', 'SSL');
        $data['action'] = $this->url->link('module/odoo/updateSettings', '', 'SSL');

        $data['cancel'] = $this->url . 'marketplace/home';

        $this->data = $data;

        $this->response->setOutput($this->render());
    }

    public function updateSettings()
    {
        $this->language->load('module/odoo');

        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)) {
            $result_json['success'] = '0';
            $result_json['error'] = $this->errorsBag;
        } else {
            if (!$this->validateForm($this->request->post)) {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->errorsBag;
                $this->response->setOutput(json_encode($result_json));
                return;
            }

            $this->module->updateSettings(['odoo' => $this->request->post]);
            $this->session->data['success'] = $this->language->get('text_settings_success');
            $this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success');
            $result_json['success'] = '1';
        }

        $this->response->setOutput(json_encode($result_json));
        return;
    }

    private function validateForm($data)
    {  
        if (empty($data['url'])) $this->errorsBag['url'] = $this->language->get('error_invalid_url');
        if (empty($data['database'])) $this->errorsBag['database'] = $this->language->get('error_invalid_database');
        if (empty($data['username'])) $this->errorsBag['username'] = $this->language->get('error_invalid_username');
        if (empty($data['password'])) $this->errorsBag['password'] = $this->language->get('error_invalid_password');
        if (empty($data['version'])) $this->errorsBag['version'] = $this->language->get('error_invalid_version');
        if ($this->errorsBag && !isset($this->errorsBag['error'])) $this->errorsBag['warning'] = $this->language->get('error_warning');

        return empty($this->errorsBag);
    }
    public function syncProducts()
    {
      
        if(! $this->module->getInventory()->validateAuth()){

            $this->data['invalidAuth'] = $result_json['invalidAuth_msg'] ='Invalid Credentials';

            $result_json['invalidAuth'] = '1';
        } 
        else{
            $this->language->load('module/odoo');
            $this->load->model('module/odoo/products');
        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)) {
            $result_json['success'] = '0';
            $result_json['error'] = $this->error;
        } else {
            $settings = $this->module->getSettings();
        
            if (!$this->validateForm($settings)) {
                $result_json['success'] = '0';
                $this->error['warning'] = $this->language->get('error_warning');
                $result_json['errors'] = $this->error;
                $this->response->setOutput(json_encode($result_json));
                return;
            }
            else{
            $this->model_module_odoo_products->syncProducts();

            $this->session->data['success'] = $this->language->get('text_settings_success');

            $this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success_sync_products');

            $result_json['success'] = '1';
            }
        }  
    }
        return $this->response->setOutput(json_encode($result_json));
    
    }
    public function syncCustomers()
    {
        if(! $this->module->getInventory()->validateAuth()){

            $this->data['invalidAuth'] = $result_json['invalidAuth_msg'] ='Invalid Credentials';

            $result_json['invalidAuth'] = '1';
        } 
        else{
        $this->language->load('module/odoo');
        $this->load->model('module/odoo/customers');
        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)) {
            $result_json['success'] = '0';
            $result_json['error'] = $this->error;
        } else 
        {
            $settings = $this->module->getSettings();
            if (!$this->validateForm($settings)) {
                $result_json['success'] = '0';
                $this->error['warning'] = $this->language->get('error_warning');
                $result_json['errors'] = $this->error;
                $this->response->setOutput(json_encode($result_json));
                return;
            }else{
            $this->model_module_odoo_customers->syncCustomers();

            $this->session->data['success'] = $this->language->get('text_settings_success');

            $this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success_sync_customers');

            $result_json['success'] = '1';
            }
        }
    }

          return $this->response->setOutput(json_encode($result_json));
    }
    public function syncOrders()
    {
        if(! $this->module->getInventory()->validateAuth()){

            $this->data['invalidAuth'] = $result_json['invalidAuth_msg'] ='Invalid Credentials';

            $result_json['invalidAuth'] = '1';
        } else{
        $this->language->load('module/odoo');
        $this->load->model('module/odoo/orders');
        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)) {
            $result_json['success'] = '0';
            $result_json['error'] = $this->error;
        } else 
        {
            $settings = $this->module->getSettings();
            if (!$this->validateForm($settings)) {
                $result_json['success'] = '0';
                $this->error['warning'] = $this->language->get('error_warning');
                $result_json['errors'] = $this->error;
                $this->response->setOutput(json_encode($result_json));
                return;
            }else{
            $this->model_module_odoo_orders->syncOrders();

            $this->session->data['success'] = $this->language->get('text_settings_success');

            $this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success_sync_orders');

            $result_json['success'] = '1';
            }
        }
    }

          return $this->response->setOutput(json_encode($result_json));
    }

    

}
