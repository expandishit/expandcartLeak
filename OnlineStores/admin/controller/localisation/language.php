<?php

class ControllerLocalisationLanguage extends Controller
{
    private $error = array();

    protected $availableLangs = [
        'Choose' => 'Choose',
        'Arabic' => 'Arabic',
        'English' => 'English',
        'Italian' => 'Italian',
        'German' => 'German',
        'Turkish' => 'Turkish',
        'French' => 'French',
        'Indian' => 'Indian',
        'Kurdish' => 'Kurdish'
    ];

    public function index()
    {
        if ($this->request->get['card_name']){
            $this->session->data['card_name'] = $this->request->get['card_name'];
        }

        $this->language->load('localisation/language');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('localisation/language');

        $this->data['breadcrumbs'] = array(
            array('text' => $this->language->get('text_home'), 'href' => $this->url->link('common/home')),
            array('text' => $this->language->get('heading_title'), 'href' => $this->url->link('localisation/language'))
        );

        $this->getList();
    }

    public function insert()
    {
        $this->language->load('localisation/language');

        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('localisation/language');

        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {

            $response = [];

            if (!$this->validateForm()) {

                $response['success'] = '0';
                $response['errors'] = $this->error;

                $this->response->setOutput(json_encode($response));
                return;
            }

            $this->request->post['front'] = 1;

            $language_id = $this->model_localisation_language->addLanguage($this->request->post);

            // add data to log_history
            $this->load->model('setting/audit_trail');
            $pageStatus = $this->model_setting_audit_trail->getPageStatus("lang_settings");
            if($pageStatus){
                $log_history['action'] = 'add';
                $log_history['reference_id'] = $language_id;
                $log_history['old_value'] = NULL;
                $log_history['new_value'] = json_encode($this->request->post,JSON_UNESCAPED_UNICODE);
                $log_history['type'] = 'language';
                $this->load->model('loghistory/histories');
                $this->model_loghistory_histories->addHistory($log_history);
            }


            $this->model_localisation_language->addLanguage($this->request->post);

            $this->load->model('setting/setting');
            $this->tracking->updateGuideValue('LANGUAGE');

            $response['success'] = '1';
            $response['success_msg'] = $this->language->get('notification_inserted_successfully');

			$response['redirect'] = '1';
            $response['to'] = $this->url->link(
                'localisation/language',
                '',
                'SSL'
            )->format();

            $this->response->setOutput(json_encode($response));
	    return;
        }
        $this->data['links'] = [
            'submit' => $this->url->link('localisation/language/insert', '', 'SSL'),
            'cancel' => $this->url->link('localisation/language', '', 'SSL')
        ];

        $this->getForm();
    }

    public function update()
    {
        $this->language->load('localisation/language');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('localisation/language');

        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {

            if (!$this->validateForm()) {
                $response['success'] = '0';
                $response['errors'] = $this->error;

                $this->response->setOutput(json_encode($response));
                return;
            }

            $oldValue = $this->model_localisation_language->getLanguage($this->request->get['language_id']);
            // add data to log_history
            $this->load->model('setting/audit_trail');
            $pageStatus = $this->model_setting_audit_trail->getPageStatus("lang_settings");
            if($pageStatus){
                $log_history['action'] = 'update';
                $log_history['reference_id'] = $this->request->get['language_id'];
                $log_history['old_value'] = json_encode($oldValue,JSON_UNESCAPED_UNICODE);
                $log_history['new_value'] = json_encode($this->request->post,JSON_UNESCAPED_UNICODE);
                $log_history['type'] = 'language';
                $this->load->model('loghistory/histories');
                $this->model_loghistory_histories->addHistory($log_history);
            }
            $this->request->post['front'] = 1;

            if ($this->request->post['code']== "en" || $this->request->post['code']== "ar" ){
                $this->request->post['admin'] = 1;
            }

            $this->model_localisation_language->editLanguage($this->request->get['language_id'], $this->request->post);

            $this->load->model('setting/setting');
            $this->tracking->updateGuideValue('LANGUAGE');

            $response['success'] = '1';
            $response['success_msg'] = $this->language->get('notification_updated_successfully');

			$response['redirect'] = '1';
            $response['to'] = $this->url->link(
                'localisation/language',
                '',
                'SSL'
            )->format();

            $this->response->setOutput(json_encode($response));
            return;
        }

        $this->data['links'] = [
            'submit' => $this->url->link(
                'localisation/language/update',
                'language_id=' . $this->request->get['language_id'],
                'SSL'
            ),
            'cancel' => $this->url->link('localisation/language', '', 'SSL')
        ];

        $this->getForm();
    }

    /**
     * @deprecated
     */
    public function delete()
    {
        $this->language->load('localisation/language');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('localisation/language');


        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            $this->load->model('setting/audit_trail');
            $pageStatus = $this->model_setting_audit_trail->getPageStatus("lang_settings");
            $this->load->model('loghistory/histories');

            foreach ($this->request->post['selected'] as $language_id) {
                $oldValue = $this->model_localisation_language->getLanguage($language_id);
                $this->model_localisation_language->deleteLanguage($language_id);
                // add data to log_history
                if($pageStatus){
                    $log_history['action'] = 'delete';
                    $log_history['reference_id'] = $language_id;
                    $log_history['old_value'] = json_encode($oldValue,JSON_UNESCAPED_UNICODE);
                    $log_history['new_value'] = NULL;
                    $log_history['type'] = 'language';
                    $this->model_loghistory_histories->addHistory($log_history);
                }
            }

            $this->load->model('setting/setting');
            $this->tracking->updateGuideValue('LANGUAGE');

            $this->redirect($this->url->link('localisation/language', '', 'SSL'));
        }

        $this->getList();
    }

    protected function getList()
    {

        $this->template = 'localisation/language/list.expand';
        $this->base = "common/base";

        $this->response->setOutput($this->render_ecwig());
    }


    protected function getForm()
    {

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('localisation/language', 'token=' . $this->session->data['token'] . $url, 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text' => ! isset($this->request->get['language_id']) ? $this->language->get('breadcrumb_insert') : $this->language->get('breadcrumb_update') ,
            'href' => $this->url->link('localisation/language', 'token=' . $this->session->data['token'] . $url, 'SSL'),
            'separator' => ' :: '
        );

        $this->data['cancel'] = $this->url->link('localisation/language', '', 'SSL');

        $this->data['availableLangs'] = $this->availableLangs;

        if (isset($this->request->get['language_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $language_info = $this->model_localisation_language->getLanguage($this->request->get['language_id']);

            $this->data['admin']=$language_info['admin'];
            $this->data['front']=$language_info['front'];
        }

        if (isset($this->request->post['name'])) {
            $this->data['name'] = $this->request->post['name'];
        } elseif (!empty($language_info)) {
            $this->data['name'] = $language_info['name'];
        } else {
            $this->data['name'] = '';
        }

        if (isset($this->request->post['code'])) {
            $this->data['code'] = $this->request->post['code'];
        } elseif (!empty($language_info)) {
            $this->data['code'] = $language_info['code'];
        } else {
            $this->data['code'] = '';
        }

        if (isset($this->request->post['locale'])) {
            $this->data['locale'] = $this->request->post['locale'];
        } elseif (!empty($language_info)) {
            $this->data['locale'] = $language_info['locale'];
        } else {
            $this->data['locale'] = '';
        }

        if (isset($this->request->post['image'])) {
            $this->data['image'] = $this->request->post['image'];
        } elseif (!empty($language_info)) {
            $this->data['image'] = $language_info['image'];
        } else {
            $this->data['image'] = '';
        }

        if (isset($this->request->post['directory'])) {
            $this->data['directory'] = $this->request->post['directory'];
        } elseif (!empty($language_info)) {
            $this->data['directory'] = $language_info['directory'];
        } else {
            $this->data['directory'] = '';
        }

        if (isset($this->request->post['filename'])) {
            $this->data['filename'] = $this->request->post['filename'];
        } elseif (!empty($language_info)) {
            $this->data['filename'] = $language_info['filename'];
        } else {
            $this->data['filename'] = '';
        }

        if (isset($this->request->post['sort_order'])) {
            $this->data['sort_order'] = $this->request->post['sort_order'];
        } elseif (!empty($language_info)) {
            $this->data['sort_order'] = $language_info['sort_order'];
        } else {
            $this->data['sort_order'] = '';
        }

        if (isset($this->request->post['status'])) {
            $this->data['status'] = $this->request->post['status'];
        } elseif (!empty($language_info)) {
            $this->data['status'] = $language_info['status'];
        } else {
            $this->data['status'] = 1;
        }

        $this->template = 'localisation/language/form.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }


    private function validateForm()
    {
        if ( ! $this->user->hasPermission('modify', 'localisation/language') )
        {
            $this->error['error'] = $this->language->get('error_permission');
        }

        if ((utf8_strlen($this->request->post['name']) < 1) || (utf8_strlen($this->request->post['name']) > 32)) {
            $this->error['name'] = $this->language->get('error_name');
        }

        if (utf8_strlen($this->request->post['code']) < 2) {
            $this->error['code'] = $this->language->get('error_code');
        }

        if (!$this->request->post['locale']) {
            $this->error['locale'] = $this->language->get('error_locale');
        }

        if (!$this->request->post['directory']) {
            $this->error['directory'] = $this->language->get('error_directory');
        }

        if (!$this->request->post['filename']) {
            $this->error['filename'] = $this->language->get('error_filename');
        }

        if ((utf8_strlen($this->request->post['image']) < 3) || (utf8_strlen($this->request->post['image']) > 32)) {
            $this->error['image'] = $this->language->get('error_image');
        }

        if ( $this->error && !isset($this->error['error']) )
        {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return $this->error ? false : true;
    }


    protected function validateDelete()
    {
        if (!$this->user->hasPermission('modify', 'localisation/language')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        $this->load->model('setting/store');
        $this->load->model('sale/order');

        foreach ($this->request->post['selected'] as $language_id) {
            $language_info = $this->model_localisation_language->getLanguage($language_id);

            if ($language_info) {
                if ($this->config->get('config_language') == $language_info['code']) {
                    $this->error['warning'] = $this->language->get('error_default');
                }

                if ($this->config->get('config_admin_language') == $language_info['code']) {
                    $this->error['warning'] = $this->language->get('error_admin');
                }

                $store_total = $this->model_setting_store->getTotalStoresByLanguage($language_info['code']);

                if ($store_total) {
                    $this->error['warning'] = sprintf($this->language->get('error_store'), $store_total);
                }
            }

            $order_total = $this->model_sale_order->getTotalOrdersByLanguageId($language_id);

            if ($order_total) {
                $this->error['warning'] = sprintf($this->language->get('error_order'), $order_total);
            }
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    public function dtHandler()
    {
        $this->load->model('localisation/language');
        $request = $_REQUEST;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        $start = $request['start'];
        $length = $request['length'];

        $columns = array(
            0 => 'id',
            1 => 'name',
            2 => 'code',
            3 => 'status',
            4 => 'locale',
            5 => 'directory',
            6 => 'filename',
            7 => 'sort_order',
        );
        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];

        $return = $this->model_localisation_language->dtHandler($start, $length, $search, $orderColumn, $orderType);
        $records = $return['data'];
        $totalData = $return['total'];
        $totalFiltered = $return['totalFiltered'];

        $json_data = array(
            "draw" => intval($request['draw']),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => $totalFiltered,
            "data" => $records
        );
        $this->response->setOutput(json_encode($json_data));
        return;
    }

    public function dtDelete()
    {
        $this->load->model("localisation/language");

        $english_language_id = 1;

        if(in_array($english_language_id,$this->request->post['selected'])){
            $response['status'] = 'error';
            $response['title'] = $this->language->get('notification_error_title');
            $response['message'] = $this->language->get('error_delete_english');
            $this->response->setOutput(json_encode($response));
            return;
        }

        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            $this->load->model('setting/audit_trail');
            $pageStatus = $this->model_setting_audit_trail->getPageStatus("lang_settings");
            $this->load->model('loghistory/histories');

            foreach ($this->request->post['selected'] as $id) {
                $oldValue = $this->model_localisation_language->getLanguage($id);

                $this->model_localisation_language->deleteLanguage($id);
                // add data to log_history
                if($pageStatus){
                    $log_history['action'] = 'delete';
                    $log_history['reference_id'] = $id;
                    $log_history['old_value'] = json_encode($oldValue,JSON_UNESCAPED_UNICODE);
                    $log_history['new_value'] = NULL;
                    $log_history['type'] = 'language';
                    $this->model_loghistory_histories->addHistory($log_history);
                }
            }

            $response['status'] = 'success';
            $response['title'] = $this->language->get('notification_success_title');
            $response['message'] = $this->language->get('message_deleted_successfully');
        } else {
            $response['status'] = 'error';
            $response['title'] = $this->language->get('notification_error_title');
            $response['errors'] = $this->error;
        }

        $this->response->setOutput(json_encode($response));
        return;
    }

    public function dtUpdateStatus()
    {
        $this->load->model("localisation/language");
        if (isset($this->request->post["id"]) && isset($this->request->post["status"])) {
            $id = $this->request->post["id"];
            $status = $this->request->post["status"];

            $langData = $this->model_localisation_language->getLanguage($id);
            $oldValue = $langData;
            $langData["status"] = $status;
            $this->model_localisation_language->editLanguage($id, $langData);

            // add data to log_history
            $this->load->model('setting/audit_trail');
            $pageStatus = $this->model_setting_audit_trail->getPageStatus("lang_settings");
            if($pageStatus){
                $log_history['action'] = 'update';
                $log_history['reference_id'] = $id;
                $log_history['old_value'] = json_encode($oldValue,JSON_UNESCAPED_UNICODE);
                $log_history['new_value'] = json_encode($langData,JSON_UNESCAPED_UNICODE);
                $log_history['type'] = 'language';
                $this->load->model('loghistory/histories');
                $this->model_loghistory_histories->addHistory($log_history);
            }

            $this->load->model('setting/setting');
            $this->tracking->updateGuideValue('LANGUAGE');

            $response['status'] = 'success';
            $response['title'] = $this->language->get('notification_success_title');
            $response['message'] = $this->language->get('message_updated_successfully');
        } else {
            $response['status'] = 'error';
            $response['title'] = $this->language->get('notification_error_title');
            $response['errors'] = $this->language->get('notification_unknoen_error');
        }

        $this->response->setOutput(json_encode($response));
        return;
    }
}
