<?php
class ControllerLocalisationZone extends Controller
{
    private $error = array();

    public function getCityDetails()
    {
        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($this->request->post['zone_id'])) {
            return false;
        }

        $zone_id = (int) $this->request->post['zone_id'];
        $this->load->model('localisation/zone');
        $zone = $this->model_localisation_zone->getZone($zone_id);
        $zone_locale = $this->model_localisation_zone->getAllZoneLocale($zone_id);

        $locales = array();
        foreach ($zone_locale as $locale) {
            $locales[$locale['lang_id']] = $locale['name'];
        }

        $data = array('zone' => $zone, 'locale' => $locales);
        $this->response->setOutput(json_encode($data));
    }

    public function index()
    {
        $this->language->load('localisation/country');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('localisation/zone');

        $this->getList();
    }

    public function insert()
    {
        $this->language->load('localisation/country');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('localisation/zone');

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            if (!$this->validateForm()) {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;

                $this->response->setOutput(json_encode($result_json));

                return;
            }

            $data = array();

            foreach ($this->request->post as $key => $value) {
                if (preg_match('/zoneLang.*/', $key)) {
                    $lang_id = explode('zoneLang', $key)[1];
                    $data['names'][$lang_id] = $value;
                } else {
                    $data[$key] = $value;
                }
            }

            $zone_id = $this->model_localisation_zone->addZone($data);

            // add data to log_history
            $this->load->model('setting/audit_trail');
            $pageStatus = $this->model_setting_audit_trail->getPageStatus("lang_settings");
            if($pageStatus){
                $log_history['action'] = 'add';
                $log_history['reference_id'] = $zone_id;
                $log_history['old_value'] = NULL;
                $log_history['new_value'] = json_encode($data,JSON_UNESCAPED_UNICODE);
                $log_history['type'] = 'zone';
                $this->load->model('loghistory/histories');
                $this->model_loghistory_histories->addHistory($log_history);
            }

            $result_json['success'] = '1';

            $result_json['success_msg'] = $this->language->get('text_success');

            $this->response->setOutput(json_encode($result_json));

            return;
        }

        $this->getForm();
    }

    public function dtCityUpdateStatus()
    {
        $this->load->model("localisation/zone");
        if (isset($this->request->post["id"]) && isset($this->request->post["status"])) {
            $id = $this->request->post["id"];
            $status = $this->request->post["status"];

            $cityData = $this->model_localisation_zone->getZone($id);
            $oldValue = $cityData;
            $cityData["status"] = $status;

            $allLocales = $this->model_localisation_zone->getAllZoneLocale($id);
            if(count($allLocales) > 0){
                foreach ($allLocales as $key=>$locale){
                    $oldValue['names'][$locale['lang_id']] = $locale['name'];
                }
            }
            $this->model_localisation_zone->editZone($id, $cityData);
            // add data to log_history
            $this->load->model('setting/audit_trail');
            $pageStatus = $this->model_setting_audit_trail->getPageStatus("lang_settings");
            if($pageStatus){
                $log_history['action'] = 'update';
                $log_history['reference_id'] = $id;
                $log_history['old_value'] = json_encode($oldValue,JSON_UNESCAPED_UNICODE);
                $log_history['new_value'] = json_encode($cityData,JSON_UNESCAPED_UNICODE);
                $log_history['type'] = 'zone';
                $this->load->model('loghistory/histories');
                $this->model_loghistory_histories->addHistory($log_history);
            }

            $response['success'] = '1';
            $response['success_msg'] = $this->language->get('message_updated_successfully');
        } else {
            $response['success'] = '0';
            $response['errors'] = $this->language->get('notification_unknoen_error');
        }

        $this->response->setOutput(json_encode($response));
        return;
    }

    public function update()
    {
        $this->language->load('localisation/country');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('localisation/zone');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $oldValue = $this->model_localisation_zone->getZone($this->request->get['zone_id']);

            $this->model_localisation_zone->editZone($this->request->get['zone_id'], $this->request->post);

            //ahmed
            $data = $this->request->post;
            $keys = array();
            foreach (array_keys($data) as $key) {
                if (strstr($key, 'zoneLang')) {
                    $keys[] = array('key' => $key, 'value' => $data[$key], 'lang_id' => explode('Lang', $key)[1]);
                }
            }
            $zone_id = $this->request->get['zone_id'];
            foreach ($keys as $key) {
                $existBefore = $this->model_localisation_zone->getZoneLocale($zone_id, $key['lang_id']);
                if (count($existBefore) > 0) {
                    $oldValue['names'][$key['lang_id']] = $existBefore['name'];
                    $this->model_localisation_zone->updateZoneLocale($zone_id, $key['lang_id'], $key['value']);
                    $data['names'][$key['lang_id']] = $key['value'];
                } else {
                    $country_id = (int) $this->model_localisation_zone->getZoneCountry($zone_id);
                    $this->model_localisation_zone->insertZoneLocale($zone_id, $country_id, $key['lang_id'], $key['value']);
                    $data['names'][$key['lang_id']] = $key['value'];
                }
            }

            // add data to log_history
            $this->load->model('setting/audit_trail');
            $pageStatus = $this->model_setting_audit_trail->getPageStatus("lang_settings");
            if($pageStatus){
                $log_history['action'] = 'update';
                $log_history['reference_id'] = $zone_id;
                $log_history['old_value'] = json_encode($oldValue,JSON_UNESCAPED_UNICODE);
                $log_history['new_value'] = json_encode($data,JSON_UNESCAPED_UNICODE);
                $log_history['type'] = 'zone';
                $this->load->model('loghistory/histories');
                $this->model_loghistory_histories->addHistory($log_history);
            }

            $result_json['success_msg'] = $this->language->get('text_success');
            $result_json['success'] = '1';
            $this->response->setOutput(json_encode($result_json));
            return;
        }

        $this->getForm();
    }

    public function delete()
    {
        $this->language->load('localisation/zone');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('localisation/zone');

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            if (!$this->validateDelete()) {
                $result_json['success'] = '0';
                $result_json['error'] = ':(';
            } else {
                $zone_ids = $this->request->post['zone_ids'];

                $this->load->model('setting/audit_trail');
                $pageStatus = $this->model_setting_audit_trail->getPageStatus("lang_settings");

                if (is_array($zone_ids)) {
                    foreach ($zone_ids as $zone_id) {
                        $oldValue = $this->model_localisation_zone->getZone($zone_id);
                        $allLocales = $this->model_localisation_zone->getAllZoneLocale($zone_id);
                        if(count($allLocales) > 0){
                            foreach ($allLocales as $key=>$locale){
                                $oldValue['names'][$locale['lang_id']] = $locale['name'];
                            }
                        }
                        if($pageStatus){
                            $log_history['action'] = 'delete';
                            $log_history['reference_id'] = $zone_id;
                            $log_history['old_value'] = json_encode($oldValue,JSON_UNESCAPED_UNICODE);
                            $log_history['new_value'] = NULL;
                            $log_history['type'] = 'zone';
                            $this->load->model('loghistory/histories');
                            $this->model_loghistory_histories->addHistory($log_history);
                        }
                        $this->model_localisation_zone->deleteZone($zone_id);
                        $this->model_localisation_zone->deleteZoneLocals($zone_id);
                        $this->model_localisation_zone->deleteZoneArea($zone_id);
                    }
                } else {
                    if($pageStatus){
                        $oldValue = $this->model_localisation_zone->getZone($zone_ids);
                        $allLocales = $this->model_localisation_zone->getAllZoneLocale($zone_ids);
                        if(count($allLocales) > 0){
                            foreach ($allLocales as $key=>$locale){
                                $oldValue['names'][$locale['lang_id']] = $locale['name'];
                            }
                        }
                        $log_history['action'] = 'delete';
                        $log_history['reference_id'] = $zone_ids;
                        $log_history['old_value'] = json_encode($oldValue,JSON_UNESCAPED_UNICODE);
                        $log_history['new_value'] = NULL;
                        $log_history['type'] = 'zone';
                        $this->load->model('loghistory/histories');
                        $this->model_loghistory_histories->addHistory($log_history);
                    }
                    $this->model_localisation_zone->deleteZone($zone_ids);
                    $this->model_localisation_zone->deleteZoneLocals($zone_ids);
                    $this->model_localisation_zone->deleteZoneArea($zone_ids);
                }

                $result_json['success'] = '1';
                $result_json['success_msg'] = $this->language->get('text_success');
            }

            $this->response->setOutput(json_encode($result_json));
            return;
        }

        $this->getList();
    }

    protected function getList()
    {
        $this->data['genericUpdate'] = $this->url->link('localisation/zone/update', 'token=' . $this->session->data['token'] . '&zone_id=' . $url, 'SSL');
        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'c.name';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'ASC';
        }

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false,
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('localisation/zone', 'token=' . $this->session->data['token'] . $url, 'SSL'),
            'separator' => ' :: ',
        );

        $this->data['insert'] = $this->url->link('localisation/zone/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
        $this->data['delete'] = $this->url->link('localisation/zone/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

        $this->data['zones'] = array();

        $data = array(
            'sort' => $sort,
            'order' => $order,
            'start' => ($page - 1) * $this->config->get('config_admin_limit'),
            'limit' => $this->config->get('config_admin_limit'),
        );

        $zone_total = $this->model_localisation_zone->getTotalZones();

        $results = $this->model_localisation_zone->getZones($data);

        foreach ($results as $result) {
            $action = array();

            $action[] = array(
                'text' => $this->language->get('text_edit'),
                'href' => $this->url->link('localisation/zone/update', 'token=' . $this->session->data['token'] . '&zone_id=' . $result['zone_id'] . $url, 'SSL'),
            );

            $this->data['zones'][] = array(
                'zone_id' => $result['zone_id'],
                'country' => $result['country'],
                'name' => $result['name'] . (($result['zone_id'] == $this->config->get('config_zone_id')) ? $this->language->get('text_default') : null),
                'code' => $result['code'],
                'selected' => isset($this->request->post['selected']) && in_array($result['zone_id'], $this->request->post['selected']),
                'action' => $action,
            );
        }

        $this->data['heading_title'] = $this->language->get('heading_title');

        $this->data['text_no_results'] = $this->language->get('text_no_results');

        $this->data['column_country'] = $this->language->get('column_country');
        $this->data['column_name'] = $this->language->get('column_name');
        $this->data['column_code'] = $this->language->get('column_code');
        $this->data['column_action'] = $this->language->get('column_action');

        $this->data['button_insert'] = $this->language->get('button_insert');
        $this->data['button_delete'] = $this->language->get('button_delete');

        if (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else {
            $this->data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $this->data['success'] = '';
        }

        $url = '';

        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $this->data['sort_country'] = $this->url->link('localisation/zone', 'token=' . $this->session->data['token'] . '&sort=c.name' . $url, 'SSL');
        $this->data['sort_name'] = $this->url->link('localisation/zone', 'token=' . $this->session->data['token'] . '&sort=z.name' . $url, 'SSL');
        $this->data['sort_code'] = $this->url->link('localisation/zone', 'token=' . $this->session->data['token'] . '&sort=z.code' . $url, 'SSL');

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        $pagination = new Pagination();
        $pagination->total = $zone_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_admin_limit');
        $pagination->text = $this->language->get('text_pagination');
        $pagination->url = $this->url->link('localisation/zone', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

        $this->data['pagination'] = $pagination->render();

        $this->data['sort'] = $sort;
        $this->data['order'] = $order;

        $this->template = 'localisation/zone_list.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->response->setOutput($this->render());
    }

    private function getForm()
    {

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false,
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('localisation/country_city', 'token=' . $this->session->data['token'] . $url, 'SSL'),
            'separator' => ' :: ',
        );

        $this->data['breadcrumbs'][] = array(
            'text' => !isset($this->request->get['zone_id']) ? $this->language->get('breadcrumb_insert') : $this->language->get('breadcrumb_update'),
            'href' => $this->url->link('localisation/zone', 'token=' . $this->session->data['token'] . $url, 'SSL'),
            'separator' => ' :: ',
        );

        if (!isset($this->request->get['zone_id'])) {
            $this->data['action'] = $this->url->link('localisation/zone/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
        } else {
            $this->data['action'] = $this->url->link('localisation/zone/update', 'token=' . $this->session->data['token'] . '&zone_id=' . $this->request->get['zone_id'] . $url, 'SSL');
        }

        $this->data['cancel'] = $this->url->link('localisation/country_city', 'token=' . $this->session->data['token'] . $url, 'SSL');

        $this->load->model('localisation/language');
        $languages = $this->data['languages'] = $this->model_localisation_language->getLanguages();

        if (isset($this->request->get['zone_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $zone_info = $this->model_localisation_zone->getZone($this->request->get['zone_id']);
            //print_r($zone_info);die();

            //ahmed

            $zoneLocale = $this->model_localisation_zone->getAllZoneLocale($this->request->get['zone_id']);
            $zoneLocaleNames = array();
            foreach ($languages as $index => $lang) {
                $value = array_values(array_filter($zoneLocale, function ($record) use ($lang) {
                    return $record['lang_id'] == $lang['language_id'];
                }));
                $value = count($value) > 0 ? $value[0]['name'] : '';
                $zoneLocaleNames[$index] = $value;
            }
            $this->data['zoneLocaleNames'] = $zoneLocaleNames;
        }

        if (isset($this->request->post['status'])) {
            $this->data['status'] = $this->request->post['status'];
        } elseif (!empty($zone_info)) {
            $this->data['status'] = $zone_info['status'];
        } else {
            $this->data['status'] = '1';
        }

        if (isset($this->request->post['name'])) {
            $this->data['name'] = $this->request->post['name'];
        } elseif (!empty($zone_info)) {
            $this->data['name'] = $zone_info['name'];
        } else {
            $this->data['name'] = '';
        }

        if (isset($this->request->post['code'])) {
            $this->data['code'] = $this->request->post['code'];
        } elseif (!empty($zone_info)) {
            $this->data['code'] = $zone_info['code'];
        } else {
            $this->data['code'] = '';
        }

        if (isset($this->request->post['country_id'])) {
            $this->data['country_id'] = (int) $this->request->post['country_id'];
        } elseif (!empty($zone_info)) {
            $this->data['country_id'] = $zone_info['country_id'];
        } else {
            $this->data['country_id'] = '';
        }

        $this->load->model('localisation/country');

        $this->data['countries'] = $this->model_localisation_country->getCountries();
        //$this->data['countries'] = $this->model_localisation_country->getAllCountriesLocale($this->config->get('config_language_id'));

        $this->template = 'localisation/zone_form.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->response->setOutput($this->render());
    }

    private function validateForm()
    {
        if (!$this->user->hasPermission('modify', 'localisation/zone')) {
            $this->error['error'] = $this->language->get('error_permission');
        }

        $data = $this->request->post;

        $keys = array();
        foreach (array_keys($data) as $key) {
            if (strstr($key, 'zoneLang')) {
                $keys[] = $key;
            }
        }

        if (count($keys) <= 0) {
            $this->error['warning'] = $this->language->get('error_name');
        } else {
            foreach ($keys as $key) {
                if ((utf8_strlen($this->request->post[$key]) < 3) || (utf8_strlen($this->request->post[$key]) > 128)) {
                    $this->error[$key] = $this->language->get('error_name');
                }
            }
        }

        if (!$this->request->post['code']) {
            $this->error['code'] = $this->language->get('error_field_cant_be_empty');
        }

        if ($this->error && !isset($this->error['error'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return $this->error ? false : true;
    }

    protected function validateDelete()
    {
        if (!$this->user->hasPermission('modify', 'localisation/zone')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        $this->load->model('setting/store');
        $this->load->model('sale/customer');
        $this->load->model('sale/affiliate');
        $this->load->model('localisation/geo_zone');

        foreach ($this->request->post['selected'] as $zone_id) {
            if ($this->config->get('config_zone_id') == $zone_id) {
                $this->error['warning'] = $this->language->get('error_default');
            }

            $store_total = $this->model_setting_store->getTotalStoresByZoneId($zone_id);

            if ($store_total) {
                $this->error['warning'] = sprintf($this->language->get('error_store'), $store_total);
            }

            $address_total = $this->model_sale_customer->getTotalAddressesByZoneId($zone_id);

            if ($address_total) {
                $this->error['warning'] = sprintf($this->language->get('error_address'), $address_total);
            }

            $affiliate_total = $this->model_sale_affiliate->getTotalAffiliatesByZoneId($zone_id);

            if ($affiliate_total) {
                $this->error['warning'] = sprintf($this->language->get('error_affiliate'), $affiliate_total);
            }

            $zone_to_geo_zone_total = $this->model_localisation_geo_zone->getTotalZoneToGeoZoneByZoneId($zone_id);

            if ($zone_to_geo_zone_total) {
                $this->error['warning'] = sprintf($this->language->get('error_zone_to_geo_zone'), $zone_to_geo_zone_total);
            }
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    //ahmed
    public function handler()
    {
        $this->load->model('localisation/zone');
        $request = $_REQUEST;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;
        $lang_id = $this->config->get('config_language_id');

        $start = $request['start'];
        $length = $request['length'];

        $columns = array(
            0 => 'zone_id',
            1 => 'zone_name',
            2 => 'country_name',
            3 => 'code',
            4 => 'status',
        );
        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];
        $return = ($this->model_localisation_zone->handler($start, $length, $lang_id, $orderColumn, $orderType, $search));
        $records = $return['data'];
        $totalData = $return['total'];
        $totalFiltered = $return['totalFiltered'];
        $alt = array();

        foreach ($records as $record) {
            $alt[] = array($record['zone_id'], $record['country_id'], $record['zone_name'], $record['country_name'], $record['code'], $record['status']);
        }

        $json_data = array(
            "draw" => intval($request['draw']),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => $totalFiltered,
            "data" => $alt,
        );
        echo json_encode($json_data);
    }

    public function removeZone()
    {
        $zone_id = $_POST['zone_id'];
        if ($this->deleteValidationCheck($zone_id)) {
            $this->load->model('localisation/zone');
            $this->load->model('setting/audit_trail');
            $pageStatus = $this->model_setting_audit_trail->getPageStatus("lang_settings");
            if($pageStatus){
                $oldValue = $this->model_localisation_zone->getZone($zone_id);
                $allLocales = $this->model_localisation_zone->getAllZoneLocale($zone_id);
                if(count($allLocales) > 0){
                    foreach ($allLocales as $key=>$locale){
                        $oldValue['names'][$locale['lang_id']] = $locale['name'];
                    }
                }
                $log_history['action'] = 'delete';
                $log_history['reference_id'] = $zone_id;
                $log_history['old_value'] = json_encode($oldValue,JSON_UNESCAPED_UNICODE);
                $log_history['new_value'] = NULL;
                $log_history['type'] = 'zone';
                $this->load->model('loghistory/histories');
                $this->model_loghistory_histories->addHistory($log_history);
            }
            $this->model_localisation_zone->deleteZone($zone_id);
        } else {
            $this->language->load('localisation/zone');
            $errors = array();
            foreach ($this->error as $err) {
                $errors[] = $this->language->get("$err");
            }
            print_r($errors);
        }
    }

    public function removeZoneBulk()
    {
        $zone_ids = $_POST['zone_ids'];
        if ($this->deleteValidationCheck($zone_ids)) {
            $this->load->model('localisation/zone');
            $this->load->model('setting/audit_trail');
            $pageStatus = $this->model_setting_audit_trail->getPageStatus("lang_settings");
            foreach ($zone_ids as $id) {
                if($pageStatus){
                    $oldValue = $this->model_localisation_zone->getZone($id);
                    $allLocales = $this->model_localisation_zone->getAllZoneLocale($id);
                    if(count($allLocales) > 0){
                        foreach ($allLocales as $key=>$locale){
                            $oldValue['names'][$locale['lang_id']] = $locale['name'];
                        }
                    }
                    $log_history['action'] = 'delete';
                    $log_history['reference_id'] = $id;
                    $log_history['old_value'] = json_encode($oldValue,JSON_UNESCAPED_UNICODE);
                    $log_history['new_value'] = NULL;
                    $log_history['type'] = 'zone';
                    $this->load->model('loghistory/histories');
                    $this->model_loghistory_histories->addHistory($log_history);
                }
                $this->model_localisation_zone->deleteZone($id);
            }
        } else {
            $this->language->load('localisation/zone');
            $errors = array();
            foreach ($this->error as $err) {
                $errors[] = $this->language->get("$err");
            }
            print_r($errors);
        }
    }

    public function deleteValidationCheck($zones)
    {
        if (is_array($zones)) {
            foreach ($zones as $zone_id) {
                if (!$this->genericDeleteValidation($zone_id)) {
                    return false;
                }
            }
            return true;
        } else {
            return $this->genericDeleteValidation($zones);
        }
    }

    public function genericDeleteValidation($zone_id)
    {
        if (!$this->user->hasPermission('modify', 'localisation/zone')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        $this->load->model('setting/store');
        $this->load->model('sale/customer');
        $this->load->model('sale/affiliate');
        $this->load->model('localisation/geo_zone');

        if ($this->config->get('config_zone_id') == $zone_id) {
            $this->error['warning'] = $this->language->get('error_default');
        }

        $store_total = $this->model_setting_store->getTotalStoresByZoneId($zone_id);

        if ($store_total) {
            $this->error['warning'] = sprintf($this->language->get('error_store'), $store_total);
        }

        $address_total = $this->model_sale_customer->getTotalAddressesByZoneId($zone_id);

        if ($address_total) {
            $this->error['warning'] = sprintf($this->language->get('error_address'), $address_total);
        }

        $affiliate_total = $this->model_sale_affiliate->getTotalAffiliatesByZoneId($zone_id);

        if ($affiliate_total) {
            $this->error['warning'] = sprintf($this->language->get('error_affiliate'), $affiliate_total);
        }

        $zone_to_geo_zone_total = $this->model_localisation_geo_zone->getTotalZoneToGeoZoneByZoneId($zone_id);

        if ($zone_to_geo_zone_total) {
            $this->error['warning'] = sprintf($this->language->get('error_zone_to_geo_zone'), $zone_to_geo_zone_total);
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    public function getZonesByCountryId()
    {
        $this->load->model('localisation/zone');

        $results = $this->model_localisation_zone->getZonesByCountryId($this->request->post['country_id']);

        $this->response->setOutput(json_encode(['status' => 'OK', 'data' => $results]));
    }

    public function getZonesByCountriesId()
    {
        $country_id = implode(',', $this->request->post['country_id']);

        if (!empty($country_id)) {

            $countriesZonesArray = [];
            $lang_id = $this->config->get('config_language_id');
            $this->load->model('localisation/zone');

            $countriesZonesArray['countries'] = $country_id;
            $countriesZonesArray['langId'] = $lang_id;

            $zonesData = $this->model_localisation_zone->getZonesByCountriesId($countriesZonesArray);

            $this->response->setOutput(json_encode($zonesData));
        }

    }

}
