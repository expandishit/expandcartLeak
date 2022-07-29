<?php
class ControllerLocalisationArea extends Controller
{
    private $error = array();

    public function getAreaDetails()
    {
        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($this->request->post['area_id'])) {
            return false;
        }

        $area_id = (int) $this->request->post['area_id'];
        $this->load->model('localisation/area');
        $area = $this->model_localisation_area->getArea($area_id);
        $area_locale = $this->model_localisation_area->getAllAreaLocale($area_id);

        $locales = array();
        foreach ($area_locale as $locale) {
            $locales[$locale['lang_id']] = $locale['name'];
        }

        $data = array('area' => $area, 'locale' => $locales);
        $this->response->setOutput(json_encode($data));
    }

    public function index()
    {
        $this->language->load('localisation/country');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('localisation/area');

        $this->getList();
    }

    public function insert()
    {
        $this->language->load('localisation/country');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('localisation/area');
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            if (!$this->validateForm()) {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;

                $this->response->setOutput(json_encode($result_json));

                return;
            }

            $data = array();

            foreach ($this->request->post as $key => $value) {
                if (preg_match('/areaLang.*/', $key)) {
                    $lang_id = explode('areaLang', $key)[1];
                    $data['names'][$lang_id] = $value;
                } else {
                    $data[$key] = $value;
                }
            }

            $this->model_localisation_area->addArea($data);

            $result_json['success'] = '1';

            $result_json['success_msg'] = $this->language->get('text_success');

            $this->response->setOutput(json_encode($result_json));

            return;
        }

        $this->getForm();
    }

    public function dtCityUpdateStatus()
    {
        $this->load->model("localisation/area");
        if (isset($this->request->post["id"]) && isset($this->request->post["status"])) {
            $id = $this->request->post["id"];
            $status = $this->request->post["status"];

            $cityData = $this->model_localisation_area->getArea($id);
            $cityData["status"] = $status;
            $this->model_localisation_area->editArea($id, $cityData);

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

        $this->load->model('localisation/area');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_localisation_area->editArea($this->request->get['area_id'], $this->request->post);

            //ahmed
            $data = $this->request->post;
            $keys = array();
            foreach (array_keys($data) as $key) {
                if (strstr($key, 'areaLang')) {
                    $keys[] = array('key' => $key, 'value' => $data[$key], 'lang_id' => explode('Lang', $key)[1]);
                }
            }
            $area_id = $this->request->get['area_id'];
            foreach ($keys as $key) {
                $existBefore = $this->model_localisation_area->getAreaLocale($area_id, $key['lang_id']);
                if (count($existBefore) > 0) {
                    $this->model_localisation_area->updateAreaLocale($area_id, $key['lang_id'], $key['value']);
                } else {
                    $country_id = (int) $this->model_localisation_area->getAreaCountry($area_id);
                    $this->model_localisation_area->insertAreaLocale($area_id, $country_id, $key['lang_id'], $key['value']);
                }
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
        $this->language->load('localisation/area');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('localisation/area');

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            if (!$this->validateDelete()) {
                $result_json['success'] = '0';
                $result_json['error'] = ':(';
            } else {
                $area_ids = $this->request->post['area_ids'];

                if (is_array($area_ids)) {
                    foreach ($area_ids as $area_id) {
                        $this->model_localisation_area->deleteArea($area_id);
                        $this->model_localisation_area->deleteAreaLocal($area_id);
                    }
                } else {
                    $this->model_localisation_area->deleteArea($area_ids);
                    $this->model_localisation_area->deleteAreaLocal($area_ids);
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
        $this->data['genericUpdate'] = $this->url->link('localisation/area/update', 'token=' . $this->session->data['token'] . '&city_id=' . $url, 'SSL');
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
            'href' => $this->url->link('localisation/area', 'token=' . $this->session->data['token'] . $url, 'SSL'),
            'separator' => ' :: ',
        );

        $this->data['insert'] = $this->url->link('localisation/area/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
        $this->data['delete'] = $this->url->link('localisation/area/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

        $this->data['areas'] = array();

        $data = array(
            'sort' => $sort,
            'order' => $order,
            'start' => ($page - 1) * $this->config->get('config_admin_limit'),
            'limit' => $this->config->get('config_admin_limit'),
        );

        $area_total = $this->model_localisation_area->getTotalAreas();

        $results = $this->model_localisation_area->getAreas($data);

        foreach ($results as $result) {
            $action = array();

            $action[] = array(
                'text' => $this->language->get('text_edit'),
                'href' => $this->url->link('localisation/area/update', 'token=' . $this->session->data['token'] . '&area_id=' . $result['area_id'] . $url, 'SSL'),
            );

            $this->data['areas'][] = array(
                'area_id' => $result['area_id'],
                'country' => $result['country'],
                'name' => $result['name'] . (($result['area_id'] == $this->config->get('config_area_id')) ? $this->language->get('text_default') : null),
                'code' => $result['code'],
                'selected' => isset($this->request->post['selected']) && in_array($result['area_id'], $this->request->post['selected']),
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

        $this->data['sort_country'] = $this->url->link('localisation/area', 'token=' . $this->session->data['token'] . '&sort=c.name' . $url, 'SSL');
        $this->data['sort_name'] = $this->url->link('localisation/area', 'token=' . $this->session->data['token'] . '&sort=z.name' . $url, 'SSL');
        $this->data['sort_code'] = $this->url->link('localisation/area', 'token=' . $this->session->data['token'] . '&sort=z.code' . $url, 'SSL');

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        $pagination = new Pagination();
        $pagination->total = $area_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_admin_limit');
        $pagination->text = $this->language->get('text_pagination');
        $pagination->url = $this->url->link('localisation/area', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

        $this->data['pagination'] = $pagination->render();

        $this->data['sort'] = $sort;
        $this->data['order'] = $order;

        $this->template = 'localisation/area_list.expand';
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
            'text' => !isset($this->request->get['area_id']) ? $this->language->get('breadcrumb_insert') : $this->language->get('breadcrumb_update'),
            'href' => $this->url->link('localisation/area', 'token=' . $this->session->data['token'] . $url, 'SSL'),
            'separator' => ' :: ',
        );

        if (!isset($this->request->get['area_id'])) {
            $this->data['action'] = $this->url->link('localisation/area/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
        } else {
            $this->data['action'] = $this->url->link('localisation/area/update', 'token=' . $this->session->data['token'] . '&area_id=' . $this->request->get['area_id'] . $url, 'SSL');
        }

        $this->data['cancel'] = $this->url->link('localisation/country_city', 'token=' . $this->session->data['token'] . $url, 'SSL');

        $this->load->model('localisation/language');
        $languages = $this->data['languages'] = $this->model_localisation_language->getLanguages();

        if (isset($this->request->get['area_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $area_info = $this->model_localisation_area->getArea($this->request->get['area_id']);
            //print_r($area_info);die();

            //ahmed

            $areaLocale = $this->model_localisation_area->getAllAreaLocale($this->request->get['area_id']);
            $areaLocaleNames = array();
            foreach ($languages as $index => $lang) {
                $value = array_values(array_filter($areaLocale, function ($record) use ($lang) {
                    return $record['lang_id'] == $lang['language_id'];
                }));
                $value = count($value) > 0 ? $value[0]['name'] : '';
                $areaLocaleNames[$index] = $value;
            }
            $this->data['areaLocaleNames'] = $areaLocaleNames;
        }

        if (isset($this->request->post['status'])) {
            $this->data['status'] = $this->request->post['status'];
        } elseif (!empty($area_info)) {
            $this->data['status'] = $area_info['status'];
        } else {
            $this->data['status'] = '1';
        }

        if (isset($this->request->post['name'])) {
            $this->data['name'] = $this->request->post['name'];
        } elseif (!empty($area_info)) {
            $this->data['name'] = $area_info['name'];
        } else {
            $this->data['name'] = '';
        }

        if (isset($this->request->post['zone_id'])) {
            $this->data['zone_id'] = $this->request->post['zone_id'];
        } elseif (!empty($area_info)) {
            $this->data['zone_id'] = $area_info['zone_id'];
        } else {
            $this->data['zone_id'] = '';
        }


        if (isset($this->request->post['code'])) {
            $this->data['code'] = $this->request->post['code'];
        } elseif (!empty($area_info)) {
            $this->data['code'] = $area_info['code'];
        } else {
            $this->data['code'] = '';
        }

        if (isset($this->request->post['country_id'])) {
            $this->data['country_id'] = (int) $this->request->post['country_id'];
        } elseif (!empty($area_info)) {
            $this->data['country_id'] = $area_info['country_id'];
        } else {
            $this->data['country_id'] = '';
        }

        $this->load->model('localisation/country');

        $this->data['countries'] = $this->model_localisation_country->getCountries();
        //$this->data['countries'] = $this->model_localisation_country->getAllCountriesLocale($this->config->get('config_language_id'));

        $this->template = 'localisation/area_form.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->response->setOutput($this->render());
    }

    private function validateForm()
    {
        if (!$this->user->hasPermission('modify', 'localisation/area')) {
            $this->error['error'] = $this->language->get('error_permission');
        }

        $data = $this->request->post;

        $keys = array();
        foreach (array_keys($data) as $key) {
            if (strstr($key, 'areaLang')) {
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

        if ($this->error && !isset($this->error['error'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return $this->error ? false : true;
    }

    protected function validateDelete()
    {
        if (!$this->user->hasPermission('modify', 'localisation/area')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        $this->load->model('setting/store');
        $this->load->model('sale/customer');
        $this->load->model('sale/affiliate');
        $this->load->model('localisation/geo_zone');

        foreach ($this->request->post['selected'] as $area_id) {


            $store_total = $this->model_setting_store->getTotalStoresByAreaId($area_id);

            if ($store_total) {
                $this->error['warning'] = sprintf($this->language->get('error_store'), $store_total);
            }

            $address_total = $this->model_sale_customer->getTotalAddressesByAreaId($area_id);

            if ($address_total) {
                $this->error['warning'] = sprintf($this->language->get('error_address'), $address_total);
            }

            $affiliate_total = $this->model_sale_affiliate->getTotalAffiliatesByAreaId($area_id);

            if ($affiliate_total) {
                $this->error['warning'] = sprintf($this->language->get('error_affiliate'), $affiliate_total);
            }

            $area_to_geo_area_total = $this->model_localisation_geo_zone->getTotalAreaToGeoAreaByAreaId($area_id);

            if ($area_to_geo_area_total) {
                $this->error['warning'] = sprintf($this->language->get('error_area_to_geo_area'), $area_to_geo_area_total);
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
        $this->load->model('localisation/area');
        $request = $_REQUEST;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;
        $lang_id = $this->config->get('config_language_id');

        $start = $request['start'];
        $length = $request['length'];

        $columns = array(
            0 => 'area_id',
            1 => 'zone_name',
            2 => 'area_name',
            4 => 'code',
            5 => 'status',
        );
        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];
        $return = ($this->model_localisation_area->handler($start, $length, $lang_id, $orderColumn, $orderType, $search));
        $records = $return['data'];
        $totalData = $return['total'];
        $totalFiltered = $return['totalFiltered'];
        $alt = array();

        foreach ($records as $record) {
            $alt[] = array($record['area_id'], $record['zone_id'], $record['area_name'], $record['zone_name'], $record['code'], $record['status']);
        }

        $json_data = array(
            "draw" => intval($request['draw']),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => $totalFiltered,
            "data" => $alt,
        );

        echo json_encode($json_data);
    }

    public function removeArea()
    {
        $area_id = $_POST['area_id'];
        if ($this->deleteValidationCheck($area_id)) {
            $this->load->model('localisation/area');
            $this->model_localisation_area->deleteArea($area_id);
        } else {
            $this->language->load('localisation/area');
            $errors = array();
            foreach ($this->error as $err) {
                $errors[] = $this->language->get("$err");
            }
            print_r($errors);
        }
    }

    public function removeAreaBulk()
    {
        $area_ids = $_POST['area_ids'];
        if ($this->deleteValidationCheck($area_ids)) {
            $this->load->model('localisation/area');
            foreach ($area_ids as $id) {
                $this->model_localisation_area->deleteArea($id);
            }
        } else {
            $this->language->load('localisation/area');
            $errors = array();
            foreach ($this->error as $err) {
                $errors[] = $this->language->get("$err");
            }
            print_r($errors);
        }
    }

    public function deleteValidationCheck($areas)
    {
        if (is_array($areas)) {
            foreach ($areas as $area_id) {
                if (!$this->genericDeleteValidation($area_id)) {
                    return false;
                }
            }
            return true;
        } else {
            return $this->genericDeleteValidation($areas);
        }
    }

    public function genericDeleteValidation($area_id)
    {
        if (!$this->user->hasPermission('modify', 'localisation/area')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        $this->load->model('setting/store');
        $this->load->model('sale/customer');
        $this->load->model('sale/affiliate');
        $this->load->model('localisation/geo_area');

        if ($this->config->get('config_area_id') == $area_id) {
            $this->error['warning'] = $this->language->get('error_default');
        }

        $store_total = $this->model_setting_store->getTotalStoresByAreaId($area_id);

        if ($store_total) {
            $this->error['warning'] = sprintf($this->language->get('error_store'), $store_total);
        }

        $address_total = $this->model_sale_customer->getTotalAddressesByAreaId($area_id);

        if ($address_total) {
            $this->error['warning'] = sprintf($this->language->get('error_address'), $address_total);
        }

        $affiliate_total = $this->model_sale_affiliate->getTotalAffiliatesByAreaId($area_id);

        if ($affiliate_total) {
            $this->error['warning'] = sprintf($this->language->get('error_affiliate'), $affiliate_total);
        }

        $area_to_geo_area_total = $this->model_localisation_geo_area->getTotalAreaToGeoAreaByAreaId($area_id);

        if ($area_to_geo_area_total) {
            $this->error['warning'] = sprintf($this->language->get('error_area_to_geo_area'), $area_to_geo_area_total);
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    public function getAreasByCountryId()
    {
        $this->load->model('localisation/area');

        $results = $this->model_localisation_area->getAreasByCountryId($this->request->post['country_id']);

        $this->response->setOutput(json_encode(['status' => 'OK', 'data' => $results]));
    }

    public function getAreasByCountriesId()
    {
        $country_id = implode(',', $this->request->post['country_id']);

        if (!empty($country_id)) {

            $countriesAreasArray = [];
            $lang_id = $this->config->get('config_language_id');
            $this->load->model('localisation/area');

            $countriesAreasArray['countries'] = $country_id;
            $countriesAreasArray['langId'] = $lang_id;

            $areasData = $this->model_localisation_area->getAreasByCountriesId($countriesAreasArray);

            $this->response->setOutput(json_encode($areasData));
        }

    }

}
