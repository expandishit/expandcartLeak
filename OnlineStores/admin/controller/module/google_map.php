<?php
class ControllerModuleGoogleMap extends Controller
{
    private $appModel;

    private $error = [];

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->language->load('module/google_map');

        $this->appModel = $this->load->model("module/google_map", ['return' => true]);
    }

    public function install()
    {
        $this->appModel->install();
    }

    public function uninstall()
    {
        $this->appModel->uninstall();
    }

    public function index()
    {
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
            'href'      => $this->url->link('module/google_map', '', 'SSL'),
            'separator' => ' :: '
        );

        if ($this->appModel->isInstalled()) {
            $data['settings'] = $this->appModel->getSettings();
        }

        $this->template = 'module/google_map.expand';

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $data['action'] = $this->url->link('module/google_map/updateSettings', '', 'SSL');

        $data['cancel'] = $this->url . 'marketplace/home';

        $this->data = $data;

        $this->response->setOutput($this->render());
    }

    public function updateSettings()
    {
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

            $this->appModel->updateSettings($this->request->post);

            $this->session->data['success'] = $this->language->get('text_settings_success');

            $this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success');

            $result_json['success'] = '1';
        }

        $this->response->setOutput(json_encode($result_json));

        return;
    }

    private function validateForm(array $settings = [])
    {
        if (empty($settings['api_key'])) {
            $this->error['api_key'] = $this->language->get('error_required_api_key');
        }

        if (!isset($this->error['api_key']) && !$this->appModel->isValidApiKey($settings['api_key'])) {
            $this->error['api_key'] = $this->language->get('error_invalid_api_key');
        }

        if (!empty($this->error) && !isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return empty($this->error);
    }
}
