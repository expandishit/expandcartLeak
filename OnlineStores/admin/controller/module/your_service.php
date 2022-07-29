<?php

class ControllerModuleYourService extends Controller
{
    private $errors = [];

    public function install()
    {
        $this->load->model('module/your_service');
        $this->model_module_your_service->install();
    }

    public function uninstall()
    {
        $this->load->model('module/your_service');
        $this->model_module_your_service->uninstall();
    }

    public function index()
    {
        $this->language->load('module/your_service');
        $this->load->model('module/your_service');
        $this->load->model('localisation/language');

        $this->document->setTitle($this->language->get('ys_title'));

        $this->data['ys'] = $this->config->get('ys');
        $this->data['submit_link'] = $this->url->link('module/your_service/saveSettings', '', 'SSL');
        $this->data['languages'] = $this->model_localisation_language->getLanguages();
        $this->data['ms_enabled'] = \Extension::isInstalled('multiseller') ? 1 : 0;

        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('ys_text_module'),
            'href'      => $this->url->link('marketplace/home', 'type=apps', 'SSL'),
            'separator' => ' :: '
        );
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('ys_title'),
            'href'      => $this->url->link('module/your_service', '', 'SSL'),
            'separator' => ' :: '
        );
        $this->template = 'module/your_service/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );
        $this->response->setOutput($this->render_ecwig());
    }

    public function requests()
    {
        $this->language->load('module/your_service');

        $this->document->setTitle($this->language->get('ys_title'));

        $this->data['ajax_link'] = $this->url->link('module/your_service/getRequests', '', 'SSL');
        $this->data['ajax_delete_link'] = $this->url->link('module/your_service/deleteRequests', '', 'SSL');

        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('ys_text_module'),
            'href'      => $this->url->link('marketplace/home', 'type=apps', 'SSL'),
            'separator' => ' :: '
        );
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('ys_title'),
            'href'      => $this->url->link('module/your_service', '', 'SSL'),
            'separator' => ' :: '
        );
        $this->template = 'module/your_service/requests.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );
        $this->response->setOutput($this->render_ecwig());
    }

    public function services()
    {
        $this->language->load('module/your_service');

        $this->document->setTitle($this->language->get('ys_title'));

        $this->data['ajax_link'] = $this->url->link('module/your_service/getServices', '', 'SSL');
        $this->data['ajax_delete_link'] = $this->url->link('module/your_service/deleteServices', '', 'SSL');

        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('ys_text_module'),
            'href'      => $this->url->link('marketplace/home', 'type=apps', 'SSL'),
            'separator' => ' :: '
        );
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('ys_title'),
            'href'      => $this->url->link('module/your_service', '', 'SSL'),
            'separator' => ' :: '
        );
        $this->template = 'module/your_service/services.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );
        $this->response->setOutput($this->render_ecwig());
    }

    public function info()
    {
        $this->language->load('module/your_service');
        $this->load->model('module/your_service');

        $this->document->setTitle($this->language->get('ys_title'));

        $this->data['request_info'] = $this->model_module_your_service->getRequest($this->request->get['request_id']);

        if (!empty($this->data['request_info']['attachment']))
        {
            $this->data['request_info']['attachment'] = DIR_DOWNLOAD . 'ys_attachments/' . $this->data['request_info']['attachment'];
        }

        if (!empty($this->data['request_info']['description']))
        {
            $this->data['request_info']['description'] = nl2br($this->data['request_info']['description']);
        }

        $this->data['download_href'] = $this->url->link('module/your_service/download', 'request_id=' . $this->request->get['request_id'], 'SSL');


        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('ys_text_module'),
            'href'      => $this->url->link('marketplace/home', 'type=apps', 'SSL'),
            'separator' => ' :: '
        );
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('ys_title'),
            'href'      => $this->url->link('module/your_service', '', 'SSL'),
            'separator' => ' :: '
        );
        $this->template = 'module/your_service/info.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );
        $this->response->setOutput($this->render_ecwig());
    }

    public function updateService()
    {
        $this->language->load('module/your_service');
        $this->load->model('module/your_service');
        $this->load->model('localisation/language');

        $this->document->setTitle($this->language->get('ys_title'));

        if (isset($this->request->get['service_id'])) {
            $this->data['service'] = $this->model_module_your_service->getService($this->request->get['service_id']);
            $this->data['service_id'] = $this->request->get['service_id'];
        }
        
        $this->data['languages'] = $this->model_localisation_language->getLanguages();
        $this->data['services'] = $this->model_module_your_service->getServicesForDropdown($this->request->get['service_id']);
        $this->data['form_link'] = $this->url->link('module/your_service/updateServicePost', '', 'SSL');

        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('ys_text_module'),
            'href'      => $this->url->link('marketplace/home', 'type=apps', 'SSL'),
            'separator' => ' :: '
        );
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('ys_title'),
            'href'      => $this->url->link('module/your_service', '', 'SSL'),
            'separator' => ' :: '
        );
        $this->template = 'module/your_service/update_service.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );
        $this->response->setOutput($this->render_ecwig());
    }

    public function getRequests()
    {
        $this->load->model('module/your_service');

        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;
        $request = $this->request->request;

        $start = $request['start'];
        $length = $request['length'];

        $columns = [
            0 => 'email',
            1 => 'telephone'
        ];

        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];

        $return = $this->model_module_your_service->getRequests([
            'search' => $request['search']['value'],
            'sort' => $orderColumn,
            'order' => $orderType,
            'start' => $start,
            'length' => $length
        ]);

        $records = [];

        foreach($return['data'] as $row) {
            $records[] = [
                'requester' => $row['requester_name'],
                'ys_request_id' => $row['ys_request_id'],
                'requester_id' => $row['requester_id'],
                'email' => $row['requester_email'],
                'telephone' => $row['requester_telephone'],
                'service' => $row['service']
            ];
        }
        
        $totalData = $return['total'];
        $totalFiltered = $return['totalFiltered'];

        $json_data = [
            "draw" => intval($request['draw']),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => $totalFiltered,
            "data" => $records
        ];
        $this->response->setOutput(json_encode($json_data));
        return;
    }

    public function getServices()
    {
        $this->load->model('module/your_service');

        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;
        $request = $this->request->request;

        $start = $request['start'];
        $length = $request['length'];

        $columns = [
            0 => 'service_id',
            1 => 'name'
        ];

        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];

        $return = $this->model_module_your_service->getServices([
            'search' => $request['search']['value'],
            'sort' => $orderColumn,
            'order' => $orderType,
            'start' => $start,
            'length' => $length
        ]);

        $records = [];

        foreach($return['data'] as $row) {
            $records[] = [
                'service_id' => $row['ys_service_id'],
                'service' => $row['name']
            ];
        }
        
        $totalData = $return['total'];
        $totalFiltered = $return['totalFiltered'];

        $json_data = [
            "draw" => intval($request['draw']),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => $totalFiltered,
            "data" => $records
        ];
        $this->response->setOutput(json_encode($json_data));
        return;
    }

    public function updateServicePost()
    {
        $this->language->load('module/your_service');
        if (!$this->validateService()) {
            $json['success'] = '0';
            $json['errors'] = $this->errors;
            $this->response->setOutput(json_encode($json));
            return;
        }
        $this->load->model('module/your_service');
        if (isset($this->request->post['service_id']))
        {
            $this->model_module_your_service->updateService($this->request->post);
        } else {
            $this->model_module_your_service->addService($this->request->post);
        }
        $json['success'] = '1';
        $json['success_msg'] = $this->language->get('ys_success');
        $this->response->setOutput(json_encode($json));
    }

    public function deleteRequests()
    {
        $this->load->model('module/your_service');
        foreach($this->request->post['selected'] as $id) {
            $this->model_module_your_service->deleteRequest($id);
        }
        $this->response->setOutput(json_encode(['success' => 1]));
    }

    public function deleteServices()
    {
        $this->load->model('module/your_service');
        foreach($this->request->post['selected'] as $id) {
            $this->model_module_your_service->deleteService($id);
        }
        $this->response->setOutput(json_encode(['success' => 1]));
    }

    public function saveSettings()
    {
        $this->language->load('module/your_service');

        if (!$this->validate()) {
            $json['success'] = '0';
            $json['errors'] = $this->errors;
        } else {
            $this->load->model('setting/setting');
            $this->model_setting_setting->editSetting('ys', $this->request->post);
            $json['success'] = '1';
            $json['success_msg'] = $this->language->get('ys_success');
        }

        $this->response->setOutput(json_encode($json));
    }

    private function validateService()
    {
        foreach ($this->request->post['service']['name'] as $serviceName)
        {
            if (empty($serviceName))
            {
                $this->errors = $this->language->get('ys_error_service_name');
            }
        }
        return empty($this->errors);
    }

    private function validate()
    {
        foreach ($this->request->post['ys']['request_service_link_name'] as $navName) 
        {
            if (empty($navName)) 
            {
                $this->errors = $this->language->get('ys_error_request_service_link_name');
            }
        }
        return empty($this->errors);
    }

    public function download() {

        $this->load->model('module/your_service');

        if (isset($this->request->get['request_id'])) {
            $request_id = $this->request->get['request_id'];
        } else {
            $request_id = 0;
        }

        $request_info = $this->model_module_your_service->getRequest($request_id);

        if ($request_info) {
            $file = DIR_DOWNLOAD ."ys_attachments/". $request_info['attachment'];
            if (!preg_match(sprintf('#\.%s#i', $request_info['extension']), $request_info['attachment'])) {
                $mask = $request_info['attachment'] . '.' . $request_info['extension'];
            } else {
                $mask = basename($request_info['attachment']);
            }

            if (!headers_sent()) {
                if (file_exists($file)) {
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename="' . ($mask ? $mask : basename($file)) . '"');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                    header('Pragma: public');
                    header('Content-Length: ' . filesize($file));

                    if (ob_get_level()) ob_end_clean();

                    readfile($file, 'rb');

                    exit;
                } else {
                    exit('Error: Could not find file ' . $file . '!');
                }
            } else {
                exit('Error: Headers already sent out!');
            }
        } else {
            $this->redirect($this->url->link('account/download', '', 'SSL'));
        }
    }
}
