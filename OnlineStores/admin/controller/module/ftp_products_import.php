<?php

class ControllerModuleFtpProductsImport extends Controller
{
    private $ftpApp;

    private $error = [];

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->ftpApp = $this->load->model("module/ftp_products_import", ["return" => true]);
    }

    public function install()
    {
        if (!$this->ftpApp->isInstalled()) {
            $this->ftpApp->install();
        }
    }

    public function uninstall()
    {
        if ($this->ftpApp->isInstalled()) {
            $this->ftpApp->uninstall();
        }
    }

    public function index()
    {
        $this->language->load("module/ftp_products_import");

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
            'href'      => $this->url->link('module/ftp_products_import', '', 'SSL'),
            'separator' => ' :: '
        );

        if ($this->ftpApp->isInstalled()) {
            $data['settings'] = $this->ftpApp->getSettings();
            $data['settings']['import_products_url'] = $this->url->link('module/ftp_products_import/startImportProducts', '', 'SSL');
        }

        if ($this->ftpApp->getUnNotifiedTasksCount() > 0) {
            $data['text_success'] = $this->language->get('text_success_imported_products');
            $this->ftpApp->markAsNotified();
        }

        $this->template = 'module/ftp_products_import.expand';

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $data['action'] = $this->url->link('module/ftp_products_import/updateSettings', '', 'SSL');

        $data['cancel'] = $this->url . 'marketplace/home';

        $this->data = $data;

        $this->response->setOutput($this->render());
    }

    public function updateSettings()
    {
        $this->language->load("module/ftp_products_import");

        $data = $this->request->post['settings'];

        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST) || !$this->validateForm($data)) {
            $result_json['success'] = '0';
            $result_json['error'] = $this->error;
            $this->response->setOutput(json_encode($result_json));
            return;
        }

        $this->ftpApp->updateSettings(['ftp_products_import' =>  $data]);
        $this->session->data['success'] = $this->language->get('text_settings_success');
        $result_json['success_msg'] = $this->language->get('text_success');
        $result_json['success'] = '1';

        $this->response->setOutput(json_encode($result_json));
        return;
    }

    public function startImportProducts()
    {
        $this->language->load("module/ftp_products_import");

        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)) {
            $result_json['success'] = '0';
            $result_json['error'] = $this->error;
        } else {

            if ($this->ftpApp->isActive() && $this->validateForm($this->ftpApp->getSettings())) {
                $this->ftpApp->startImportProducts();
                $this->session->data['success'] = $this->language->get('text_settings_success');
                $this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_wait');
                $result_json['success'] = '1';
            } else {
                $result_json['success'] = '0';
                $result_json['error_msg'] = $this->error;
            }
        }

        return $this->response->setOutput(json_encode($result_json));
    }

    public function importProducts()
    {
        $this->ftpApp->importProducts();
        $this->response->setOutput(json_encode(['success' => '1']));
    }

    private function validateForm(array $data)
    {

        // product credentials
        if (empty($data['products_credentials']['server'])) {
            $this->error['products_credentials_server'] = $this->language->get('error_invalid_products_credentials_server');
        }

        if (empty($data['products_credentials']['user'])) {
            $this->error['products_credentials_user'] = $this->language->get('error_invalid_products_credentials_user');
        }

        if (empty($data['products_credentials']['password'])) {
            $this->error['products_credentials_password'] = $this->language->get('error_invalid_products_credentials_password');
        }

        if (empty($data['products_credentials']['filename'])) {
            $this->error['products_credentials_filename'] = $this->language->get('error_invalid_products_credentials_filename');
        } elseif (!in_array(pathinfo($data['products_credentials']['filename'])['extension'], $this->ftpApp::ALLOWED_EXCEL_EXTENSIONS)) {
            $this->error['products_credentials_filename'] = $this->language->get('error_invalid_products_credentials_filename_extensions')
                . ' ( ' . implode(', ', $this->ftpApp::ALLOWED_EXCEL_EXTENSIONS) . ' )';
        }

        if (empty($data['products_credentials']['document_root'])) {
            $this->error['products_credentials_document_root'] = $this->language->get('error_invalid_products_credentials_document_root');
        }

        // file schema
        if (empty($data['file_schema']['identifier_name'])) {
            $this->error['file_schema_identifier_name'] = $this->language->get('error_invalid_file_schema_identifier_name');
        }

        foreach ($data['file_schema']['file_columns_map_required'] as $key => $value) {
            if (empty($value)) {
                $this->error['file_schema_file_columns_map_required_' . $key] = $this->language->get('error_invalid_file_schema_file_columns_map_required_' . $key);
            }
        }

        // images credentials
        if (empty($data['images_credentials']['server'])) {
            $this->error['images_credentials_server'] = $this->language->get('error_invalid_images_credentials_server');
        }

        if (empty($data['images_credentials']['user'])) {
            $this->error['images_credentials_user'] = $this->language->get('error_invalid_images_credentials_user');
        }

        if (empty($data['images_credentials']['password'])) {
            $this->error['images_credentials_password'] = $this->language->get('error_invalid_images_credentials_password');
        }

        if (empty($data['images_credentials']['document_root'])) {
            $this->error['images_credentials_document_root'] = $this->language->get('error_invalid_images_credentials_document_root');
        }

        if ($this->error && !isset($this->error['error'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return $this->error ? false : true;
    }
}
