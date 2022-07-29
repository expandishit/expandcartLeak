<?php
class ControllerModuleMicrosoftDynamics extends Controller
{
    private $error = [];

    public function install()
    {
        $this->load->model("module/microsoft_dynamics");
        $this->model_module_microsoft_dynamics->install();
    }

    public function uninstall()
    {
        $this->load->model("module/microsoft_dynamics");
        $this->model_module_microsoft_dynamics->uninstall();
    }

    public function index()
    {
        $this->language->load('module/microsoft_dynamics');

        $this->load->model('module/microsoft_dynamics');


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
            'href'      => $this->url->link('module/microsoft_dynamics', '', 'SSL'),
            'separator' => ' :: '
        );

        if ($this->model_module_microsoft_dynamics->isInstalled()) {
            $data['settings'] = $this->model_module_microsoft_dynamics->getSettings();
        }

        $this->template = 'module/microsoft_dynamics.expand';

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $data['action'] = $this->url->link('module/microsoft_dynamics/updateSettings', '', 'SSL');

        $data['cancel'] = $this->url . 'marketplace/home';

        $this->data = $data;

        $this->response->setOutput($this->render());
    }

    public function updateSettings()
    {
        $this->language->load('module/microsoft_dynamics');

        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)) {
            $result_json['success'] = '0';
            $result_json['error'] = $this->error;
        } else {
            if (!$this->validateForm($this->request->post)) {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;

                $this->response->setOutput(json_encode($result_json));

                return;
            }

            $this->load->model('module/microsoft_dynamics');

            $data = $this->request->post;

            $data['order_status_ids'] = array_unique($data['order_status_ids']);
            $data['return_status_ids'] = array_unique($data['return_status_ids']);

            $this->model_module_microsoft_dynamics->updateSettings(['microsoft_dynamics' => $data]);

            $this->session->data['success'] = $this->language->get('text_settings_success');

            $this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success');

            $result_json['success'] = '1';
        }

        $this->response->setOutput(json_encode($result_json));

        return;
    }

    public function startSyncProducts()
    {
        $this->language->load('module/microsoft_dynamics');

        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)) {
            $result_json['success'] = '0';
            $result_json['error'] = $this->error;
        } else {
            $this->load->model('module/microsoft_dynamics');
            $settings = $this->model_module_microsoft_dynamics->getSettings();
            if (
                $this->model_module_microsoft_dynamics->isActive() &&
                $this->validateForm($settings) &&
                1 === (int)$settings['product_status']
            ) {
                $this->model_module_microsoft_dynamics->startSyncProducts($this->request->post['date'] ?: date('Y-m-d'));
                $result_json['success'] = '1';
                $this->session->data['success'] = $this->language->get('text_settings_success');
                $this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_wait');
            } else {
                $result_json['success'] = '0';
                $result_json['error_msg'] = $this->language->get('error_sync_warning');
            }
        }

        return $this->response->setOutput(json_encode($result_json));
    }

    public function syncProducts(string $date = null)
    {
        $this->load->model('module/microsoft_dynamics');
        $this->model_module_microsoft_dynamics->syncProducts($date);
        if (property_exists($this, 'response'))
            $this->response->setOutput(json_encode(['success' => '1']));
    }


    private function validateForm(array $settings = [])
    {
        if (empty($settings['username'])) {
            $this->error['username'] = $this->language->get('error_invalid_username');
        }

        if (empty($settings['password'])) {
            $this->error['password'] = $this->language->get('error_invalid_password');
        }

        if (empty($settings['server_base_uri'])) {
            $this->error['server_base_uri'] = $this->language->get('error_invalid_server_base_uri');
        }

        if ($this->error && !isset($this->error['error'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return empty($this->error);
    }
}
