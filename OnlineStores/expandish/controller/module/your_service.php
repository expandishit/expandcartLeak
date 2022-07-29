<?php

class ControllerModuleYourService extends Controller 
{
    public function requestService()
    {
        if ($this->config->get('login_before_add_to_cart') == 1 && !$this->customer->isLogged()) {

            $redirectWithParams = $this->url->link('module/your_service/requestService', '', 'ssl');
            $this->session->data['redirectWithParams'] = $redirectWithParams;

            $this->redirect($this->url->link('account/login'));
        }
        $this->checkModule();

        $this->language->load_json('module/your_service');
        $this->load->model('module/your_service');

        $this->document->setTitle($this->config->get('ys')['request_service_link_name'][$this->config->get('config_language')]);

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home'),
            'separator' => false
        ); 

        $this->data['breadcrumbs'][] = array(       	
            'text'      => $this->config->get('ys')['request_service_link_name'][$this->config->get('config_language')],
            'href'      => $this->url->link('module/your_service', '', 'SSL'),
            'separator' => $this->language->get('text_separator')
        );

        $this->data['requester_id'] = $this->customer->getId() ?? 0;
        $this->data['requester_name'] = trim($this->customer->getFirstName() . ' ' . $this->customer->getLastName());
        $this->data['requester_email'] = $this->customer->getEmail();
        $this->data['requester_telephone'] = $this->customer->getTelephone();
        $this->data['services'] = $this->model_module_your_service->getServices();
        $this->data['sub_services_link'] = $this->url->link('module/your_service/getSubServices', '', 'SSL');
        $this->data['submit_service_request_link'] = $this->url->link('module/your_service/submitServiceRequest', '', 'SSL');

        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        }

        if (isset($this->session->data['error'])) {
            $this->data['error'] = $this->session->data['error'];
            unset($this->session->data['error']);
        }

        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/module/your_service/request.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/module/your_service/request.expand';
        } else {
            $this->template = 'default/template/module/your_service/request.expand';
        }
        
        $this->children = array(
            'common/footer',
            'common/header'
        );
                
        $this->response->setOutput($this->render_ecwig());
    }

    public function getSubServices()
    {
        $this->load->model('module/your_service');
        $subServices = $this->model_module_your_service->getServices( $this->request->post['service_id'] );
        $this->response->setOutput( json_encode( $subServices ) );
    }

    public function submitServiceRequest()
    {
        $this->checkModule();
        $this->language->load_json('module/your_service');
        $this->load->model('module/your_service');
        $redirectURL = $this->url->link('module/your_service/requestService', '', 'SSL');
        $attachment = null;

        if (!empty($this->request->files['attachment']['tmp_name'])) {
            $attachment = $this->saveAttachment($this->request->files['attachment']);
        }

        if ($attachment === false)
        {
            $this->response->redirect( $redirectURL );
            return;
        }

        $request_id = $this->model_module_your_service->saveRequest([
            'service_id' => $this->request->post['service_id'],
            'requester_id' => $this->request->post['requester_id'],
            'requester_name' => $this->request->post['requester_name'],
            'requester_email' => $this->request->post['requester_email'],
            'requester_telephone' => $this->request->post['requester_telephone'],
            'description' => $this->request->post['description'],
            'attachment' => $attachment
        ]);

        if ($this->checkYsMsEnabled())
        {
            $this->notifyServiceProviders($this->request->post['service_id'], $request_id);
        }

        $this->notifyAdmin($this->request->post['service_id'], $request_id);

        $this->session->data['success'] = $this->language->get('ys_request_success');
        $this->response->redirect( $redirectURL );
    }

    public function serviceRequests()
    {
        $this->checkIsLoggedIn();
        $this->checkModule();
        $this->checkMsStatus();

        $this->language->load_json('module/your_service');
        $this->load->model('module/your_service');

        $this->document->setTitle($this->config->get('ys')['request_service_link_name'][$this->config->get('config_language')]);

        $this->data['ajax_link'] = $this->url->link('module/your_service/serviceRequestsAjaxList', '', 'SSL');

        $this->data['advanced_ms_enabled'] = 0;

        if (\Extension::isInstalled('multiseller_advanced'))
        {
            $this->data['advanced_ms_enabled'] = 1;
        }

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home'),
            'separator' => false
        ); 

        $this->data['breadcrumbs'][] = array(       	
            'text'      => $this->config->get('ys')['request_service_link_name'][$this->config->get('config_language')],
            'href'      => $this->url->link('module/your_service', '', 'SSL'),
            'separator' => $this->language->get('text_separator')
        );
        
        $this->data['breadcrumbs'][] = array(       	
            'text'      => $this->language->get('ys_service_requests'),
            'href'      => $this->url->link('module/your_service', '', 'SSL'),
            'separator' => $this->language->get('text_separator')
        );

        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/module/your_service/service_requests.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/module/your_service/service_requests.expand';
        } else {
            $this->template = 'default/template/module/your_service/service_requests.expand';
        }
        
        $this->children = array(
            'common/footer',
            'common/header'
        );
                
        $this->response->setOutput($this->render_ecwig());

    }

    public function serviceRequestsAjaxList()
    {
        $this->checkIsLoggedIn();
        $this->checkModule();
        $this->checkMsStatus();

        $this->load->model('module/your_service');
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;
        $request = $this->request->request;

        $start = $request['start'];
        $length = $request['length'];

        $columns = [
            0 => 'requester_name',
            1 => 'service'
        ];

        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];

        $serviceIds = $this->model_module_your_service->getServiceSettings($this->customer->getId());

        $return = [];

        if (!empty($serviceIds))
        {
            $return = $this->model_module_your_service->getServiceRequests([
                'service_ids' => $serviceIds,
                'search' => $request['search']['value'],
                'sort' => $orderColumn,
                'order' => $orderType,
                'start' => $start,
                'length' => $length
            ]);
        }

        $records = [];

        foreach($return['data'] as $row) {
            $records[] = [
                'requester_id' => $row['requester_id'],
                'request_id' => $row['ys_request_id'],
                'name' => $row['requester_name'],
                'service' => $row['service'],
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

    public function serviceSettings()
    {
        $this->checkIsLoggedIn();
        $this->checkModule();
        $this->checkMsStatus();

        $this->language->load_json('module/your_service');
        $this->load->model('module/your_service');

        $this->document->setTitle($this->config->get('ys')['request_service_link_name'][$this->config->get('config_language')]);

        $this->data['services'] = $this->model_module_your_service->getServicesWithSubServices();
        $this->data['saved_service_ids'] = $this->model_module_your_service->getServiceSettings($this->customer->getId());
        $this->data['submit_service_settings_link'] = $this->url->link('module/your_service/submitServiceSettings', '', 'SSL');

        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        }

        if (isset($this->session->data['error'])) {
            $this->data['error'] = $this->session->data['error'];
            unset($this->session->data['error']);
        }

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home'),
            'separator' => false
        ); 

        $this->data['breadcrumbs'][] = array(       	
            'text'      => $this->config->get('ys')['request_service_link_name'][$this->config->get('config_language')],
            'href'      => $this->url->link('module/your_service', '', 'SSL'),
            'separator' => $this->language->get('text_separator')
        );
        
        $this->data['breadcrumbs'][] = array(       	
            'text'      => $this->language->get('ys_service_settings'),
            'href'      => $this->url->link('module/your_service', '', 'SSL'),
            'separator' => $this->language->get('text_separator')
        );

        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/module/your_service/service_settings.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/module/your_service/service_settings.expand';
        } else {
            $this->template = 'default/template/module/your_service/service_settings.expand';
        }
        
        $this->children = array(
            'common/footer',
            'common/header'
        );
                
        $this->response->setOutput($this->render_ecwig());

    }

    public function submitServiceSettings()
    {
        $this->checkModule();
        $this->checkIsLoggedIn();
        $this->checkMsStatus();

        $this->language->load_json('module/your_service');

        $sellerId = $this->customer->getId();

        if (!empty($this->request->post['service_ids']))
        {

            $this->load->model('module/your_service');

            $this->model_module_your_service->saveServiceSettings($sellerId, $this->request->post['service_ids']);

            $this->session->data['success'] = $this->language->get('ys_save_success');

        } else {

            $this->session->data['error'] = $this->language->get('ys_select_one_service');

        }
        
        $this->response->redirect($this->url->link('module/your_service/serviceSettings', '', 'SSL'));
    }

    public function info()
    {
        $this->checkIsLoggedIn();
        $this->checkModule();
        $this->checkMsStatus();

        $this->language->load_json('module/your_service');
        $this->load->model('module/your_service');

        $this->document->setTitle($this->config->get('ys')['request_service_link_name'][$this->config->get('config_language')]);

        $this->data['request'] = $this->model_module_your_service->getServiceRequest($this->request->get['request_id']);

        $this->data['download_href'] = $this->url->link('module/your_service/download', 'request_id=' . $this->request->get['request_id'], 'SSL');
        
        if (!empty($this->data['request']['attachment']))
        {
            $this->data['request']['attachment'] = DIR_DOWNLOAD . 'ys_attachments/' . $this->data['request']['attachment'];
        }

        if (!empty($this->data['request']['description']))
        {
            $this->data['request']['description'] = nl2br($this->data['request']['description']);
        }

        $this->data['advanced_ms_enabled'] = 0;

        if (\Extension::isInstalled('multiseller_advanced'))
        {
            $this->data['advanced_ms_enabled'] = 1;
        }

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home'),
            'separator' => false
        ); 

        $this->data['breadcrumbs'][] = array(       	
            'text'      => $this->language->get('ys_heading_title'),
            'href'      => $this->url->link('module/your_service', '', 'SSL'),
            'separator' => $this->language->get('text_separator')
        );

        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/module/your_service/info.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/module/your_service/info.expand';
        } else {
            $this->template = 'default/template/module/your_service/info.expand';
        }
        
        $this->children = array(
            'common/footer',
            'common/header'
        );
                
        $this->response->setOutput($this->render_ecwig());
    }

    public function getSellerNotifications()
    {
        $this->load->model('module/your_service');
        $this->language->load_json('module/your_service');
        $requests = $this->model_module_your_service->getSellerNotifications( $this->customer->getId() );
        $notificationMessage = $this->language->get('ys_notification_message');
        $notifications = '';
        foreach ($requests as $request)
        {
            $notificationMessageSprint = sprintf($notificationMessage, $request['service'], $request['requester_name']);
            $notifications .= '
                <div>
                    <a href="'.$this->url->link('module/your_service/info&request_id='.$request['ys_request_id'], '', 'SSL').'" target="_blank">
                        '.$notificationMessageSprint.'
                    </a>
                </div>
            ';
        }
        $this->response->setOutput( $notifications );
    }

    private function notifyServiceProviders($service_id, $request_id)
    {
        
        $sellersMails = $this->model_module_your_service->getSellersMails($service_id);

        if (count($sellersMails) === 0)
        {
            return;
        }
        $serviceName = $this->model_module_your_service->getServiceName($service_id);
        $requestDetailsLink = $this->url->link('module/your_service/info&request_id=' . $request_id, '', 'SSL');
        $subject = $this->language->get('ys_new_request_mail_subject');
        $html = $this->language->get('ys_new_request_mail_html');
        $html = sprintf($html,$serviceName,$requestDetailsLink);
        $mail = new Mail(); 
        $mail->protocol = $this->config->get('config_mail_protocol');
        $mail->parameter = $this->config->get('config_mail_parameter');
        $mail->hostname = $this->config->get('config_smtp_host');
        $mail->username = $this->config->get('config_smtp_username');
        $mail->password = $this->config->get('config_smtp_password');
        $mail->port = $this->config->get('config_smtp_port');
        $mail->timeout = $this->config->get('config_smtp_timeout');			
        $mail->setTo($sellersMails);
        $mail->setFrom($this->config->get('config_email'));
        $mail->setSender($this->config->get('config_name')[$this->config->get('config_language')]);
        $mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
        $mail->setHtml($html);
        $mail->send();
    }


    private function notifyAdmin($service_id, $request_id)
    {
        $serviceName = $this->model_module_your_service->getServiceName($service_id);
        $requestDetailsLink = $this->url->link('module/your_service/info&request_id=' . $request_id, '', 'SSL');
        $subject = $this->language->get('ys_new_request_mail_subject');
        $html = $this->language->get('ys_new_request_mail_html');
        $html = sprintf($html,$serviceName,$requestDetailsLink); 
        $mail = new Mail(); 
        $mail->protocol = $this->config->get('config_mail_protocol');
        $mail->parameter = $this->config->get('config_mail_parameter');
        $mail->hostname = $this->config->get('config_smtp_host');
        $mail->username = $this->config->get('config_smtp_username');
        $mail->password = $this->config->get('config_smtp_password');
        $mail->port = $this->config->get('config_smtp_port');
        $mail->timeout = $this->config->get('config_smtp_timeout');			
        $mail->setTo($this->config->get('config_email'));
        $mail->setFrom($this->config->get('config_email'));
        $mail->setSender($this->config->get('config_name')[$this->config->get('config_language')]);
        $mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
        $mail->setHtml($html);
        $mail->send();
    }

    private function checkModule()
    {
        $status = 0;
        if (\Extension::isInstalled('your_service')) {
            $status = $this->config->get('ys')['status'] ?? 0;
        }
        if ($status != 1) {
            $this->response->redirect($this->url->link('common/home'));
        }
    }

    private function checkMsStatus()
    {
        $ysMsEnabled = $this->config->get('ys')['ms_view_requests'] ?? 0;

        if ($ysMsEnabled != 1)
        {
            return $this->response->redirect('common/home', '', 'SSL');
        }
    }

    private function checkYsMsEnabled()
    {
        $ysMsEnabled = $this->config->get('ys')['ms_view_requests'] ?? 0;
        
        return $ysMsEnabled == 1;
    }

    private function checkIsLoggedIn()
    {
        if (!$this->customer->isLogged())
        {
            return $this->response->redirect('common/home', '', 'SSL');
        }
    }

    private function saveAttachment($file)
    {
        $allowedExtensions = ['jpg', 'png', 'pdf', 'doc', 'docx'];
        $filename =   time() . '_' . md5(rand()) . '.' . $file["name"];
        $fileExt = pathinfo($file["name"], PATHINFO_EXTENSION);
        $error = false;
        if (!in_array($fileExt, $allowedExtensions))
        {
            $error = $this->language->get('ys_error_file_ext');
        }
        if ($file['size'] > 2097152)
        {
            $error = $this->language->get('ys_error_file_size');
        }
        if ($error !== false)
        {
            $this->session->data['error'] = $error;
            return false;
        }
		move_uploaded_file($file["tmp_name"], DIR_DOWNLOAD . 'ys_attachments/' .  $filename);
		return $filename;
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