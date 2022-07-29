<?php

class ControllerModuleZohoInventory extends Controller
{
    private $error = [];

    public function install()
    {
        $this->load->model("module/zoho_inventory");
        $this->model_module_zoho_inventory->install();
    }

    public function uninstall()
    {
        $this->load->model("module/zoho_inventory");
        $this->model_module_zoho_inventory->uninstall();
    }

    public function index()
    {
        $this->load->model('module/zoho_inventory');

        $this->language->load('module/zoho_inventory');

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
            'href'      => $this->url->link('module/zoho_inventory', '', 'SSL'),
            'separator' => ' :: '
        );

        if ($this->model_module_zoho_inventory->isInstalled()) {
            $data['zoho_inventory'] = $this->model_module_zoho_inventory->getSettings();
            if ($data['zoho_inventory']['can_syncable_new_products']) {
                $data['zoho_inventory']['sync_products_url'] = '' . $this->url->link('module/zoho_inventory/syncProducts', '', 'SSL');
            }

            $data['zoho_inventory']['home_url'] = '' . $this->url->link('', '', 'SSL');
            $data['zoho_inventory']['redirect_url'] = '' . $this->url->link('module/zoho_inventory/authorized', '', 'SSL');
        }

        $this->template = 'module/zoho_inventory.expand';

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $data['action'] = $this->url->link('module/zoho_inventory/updateSettings', '', 'SSL');

        $data['cancel'] = $this->url . 'marketplace/home';

        $this->data = $data;

        $this->response->setOutput($this->render());

        unset($this->session->data['error'], $this->session->data['success']);
    }

    public function updateSettings()
    {
        $this->language->load('module/zoho_inventory');
        $this->load->model('module/zoho_inventory');

        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)) {
            $result_json['success'] = '0';
            $result_json['error'] = $this->error;
        } else {
            $postSettings = $this->request->post['zoho_inventory'];
            $settings = $this->model_module_zoho_inventory->getSettings();

            if (!$this->validateForm($postSettings)) {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;

                $this->response->setOutput(json_encode($result_json));

                return;
            }

            // check token validate
            if (
                !isset($settings['token'])
                || !isset($settings['token']['access_token'])
                || $postSettings['client_id'] !== $settings['client_id']
                || $postSettings['client_secret'] !== $settings['client_secret']
            ) {
                $result_json['redirect'] = '1';
                $settings = array_merge($settings, $postSettings);
                $result_json['to'] = $this->model_module_zoho_inventory->getAuthorizationUrl($settings);
                $result_json['success_msg'] = $this->language->get('text_settings_waiting');
                $this->session->data['zoho_inventory'] = $settings;
            } else {
                $settings = array_merge($settings, $postSettings);
                $this->model_module_zoho_inventory->updateSettings(['zoho_inventory' => $settings]);
                $result_json['success_msg'] = $this->language->get('text_settings_success');
            }

            $result_json['success'] = '1';
        }

        return $this->response->setOutput(json_encode($result_json));
    }

    /**
     * @return mixed
     */
    public function syncProducts()
    {
        $this->language->load('module/zoho_inventory');
        $this->load->model('module/zoho_inventory');

        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)) {
            $result_json['success'] = '0';
            $result_json['error'] = $this->error;
        } else {
            $settings = $this->model_module_zoho_inventory->getSettings();
            
            if (!$this->validateForm($settings) || !isset($settings['token']['access_token'])) {
                $result_json['success'] = '0';
                $this->error['warning'] = $this->language->get('error_warning');
                $result_json['errors'] = $this->error;
                $this->response->setOutput(json_encode($result_json));
                return;
            }
    

            $this->model_module_zoho_inventory->syncProducts();

            $this->session->data['success'] = $this->language->get('text_settings_success');

            $this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success_sync_products');

            $result_json['success'] = '1';
        }

        return $this->response->setOutput(json_encode($result_json));
    }

    private function validateForm($data)
    {

        if (empty($data['client_id'])) {
            $this->error['client_id'] = $this->language->get('error_invalid_client_id');
        }

        if (empty($data['client_secret'])) {
            $this->error['client_secret'] = $this->language->get('error_invalid_client_secret');
        }

        if ($this->error && !isset($this->error['error'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return $this->error ? false : true;
    }

    public function authorized()
    {
        $this->language->load('module/zoho_inventory');
        $this->load->model('module/zoho_inventory');

        if (isset($this->session->data['zoho_inventory'])) {
            $settings = $this->session->data['zoho_inventory'];
            unset($this->session->data['zoho_inventory']);
            // Generating Access Token
            $results = $this->model_module_zoho_inventory->generateToken(array_merge($this->request->get, $settings ?? []));
            if ($results && isset($results['access_token'])) {
                $settings['token'] = $results;
                $this->model_module_zoho_inventory->updateSettings(['zoho_inventory' => $settings]);
                $this->session->data['success'] = $this->language->get('text_settings_success');
            } else {
                // set basic inputs
                $this->model_module_zoho_inventory->updateSettings(['zoho_inventory' => [
                    'status' => (int)$settings['status'],
                    'organization_id' => $settings['organization_id'],
                    'client_id' => $settings['client_id'],
                    'client_secret' => $settings['client_secret'],
                ]]);
                
                $this->session->data['error'] = $this->language->get('text_settings_error');
            }
        }

        $this->url->redirect('module/zoho_inventory');
    }
}
