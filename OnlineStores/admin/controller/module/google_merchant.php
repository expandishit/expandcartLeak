<?php
class ControllerModuleGoogleMerchant extends Controller
{
    private $appMode;

    private $error = [];

    private $feeds = array(
        'google_base',
    );

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->language->load('module/google_merchant');

        $this->appModel = $this->load->model("module/google_merchant", ['return' => true]);
    }

    public function install()
    {
        $this->appModel->install();
    }

    public function uninstall()
    {
        $feedId = "google_base";
        $status = 0;
        $this->model_setting_setting->editSetting($feedId, array($feedId.'_status' => $status));

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
            'href'      => $this->url->link('module/google_merchant', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->template = 'module/google_merchant.expand';

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $data['action'] = $this->url->link('module/google_merchant/updateSettings', '', 'SSL');

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

//            $this->appModel->updateSettings($this->request->post);

            $this->session->data['success'] = $this->language->get('text_settings_success');

            $this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success');

            $result_json['success'] = '1';
        }

        $this->response->setOutput(json_encode($result_json));

        return;
    }

    private function validateForm(array $settings = [])
    {
        if (!empty($this->error) && !isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return empty($this->error);
    }

    public function feed() {
        $this->language->load('setting/feed');
        $data = array();

        foreach($this->feeds as $feed) {
            $data[] = array(
                "id" => $feed,
                "name" => $this->language->get('text_' . $feed),
                "status" => $this->config->get($feed . '_status'),
                "url" => HTTP_CATALOG . 'index.php?route=feed/' . $feed
            );
        }

        $this->response->setOutput(json_encode(array("data" =>$data)));
        return;

    }
}
