<?php

class ControllerModuleDedicatedDomains extends Controller
{
    private $settings, $domains, $currency;
    private $errors = array();

    public function dtDelete()
    {
        if ( $this->request->server['REQUEST_METHOD'] != 'POST' || !isset($this->request->post) )
        {
            return;
        }

        $this->init([
            'module/dedicated_domains/domains',
            'module/dedicated_domains/settings'
        ]);

        $domainIDS = $this->request->post['ids'];

        if ( is_array($domainIDS) )
        {
            foreach ($domainIDS as $id)
            {
                $this->domains->removeDomain($id);
            }
        }
        else
        {
            $this->domains->removeDomain($domainIDS);
        }

        $result_json['success'] = '1';
        $result_json['success_msg'] = $this->language->get('success_remove_domain');

        $this->response->setOutput(json_encode($result_json));
        return;
    }

    public function dtHandler()
    {
        $request = $_REQUEST;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        $start = $request['start'];
        $length = $request['length'];

        $columns = array(
            0 => 'domain_id',
            1 => 'domain',
            2 => 'currency',
            3 => 'country',
            4 => 'domain_status'
        );
        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];

        $return = $this->handlerCode($start, $length, $search, $orderColumn, $orderType);

        $records = $return['data'];
        $totalData = $return['total'];
        $totalFiltered = $return['totalFiltered'];



        $json_data = array(
            "draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($totalData), // total number of records
            "recordsFiltered" => $totalFiltered, // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $records,   // total data array
        );
        $this->response->setOutput(json_encode($json_data));
        return;
    }

    public function handlerCode($start=0, $length=10, $search = null, $orderColumn="domain_id", $orderType="ASC")
    {
        $language_id = $this->config->get('config_language_id') ?: 1;

        $query = "SELECT * FROM " . DB_PREFIX . "dedicated_domains";
        
        $total = $totalFiltered = $this->db->query($query)->num_rows;
        $where = "";
        if (!empty($search)) {
            $where .= "domain LIKE '%{$search}%' OR currency LIKE '%{$search}%' OR country LIKE '%{$search}%'";
            $query .= " WHERE " . $where;
            $totalFiltered = $this->db->query($query)->num_rows;
        }

        $query .= " ORDER by {$orderColumn} {$orderType}";

        if($length != -1) {
            $query .= " LIMIT " . $start . ", " . $length;
        }

        $results = $this->db->query($query)->rows;

        foreach ($results as $key => $record){
            $results[$key]['status_text']= $record['domain_status'] == 1 ? $this->language->get('text_enabled') :  $this->language->get('text_disabled');
        }
        $data = array (
            'data' => $results,
            'total' => $total,
            'totalFiltered' => $totalFiltered
        );
        return $data;
    }

    public function init($models)
    {
        foreach ($models as $model) {
            $this->load->model($model);
            $object = explode('/', $model);
            $object = end($object);
            $model = str_replace('/', '_', $model);
            $this->$object = $this->{"model_" . $model};
        }
        if (isset($this->session->data['errors'])) {
            $this->data['error_warning'] = implode('</br >', $this->session->data['errors']);
            unset($this->session->data['errors']);
        }
        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        }
        $this->language->load('module/dedicated_domains');
    }

    public function index()
    {
        $this->init([
            'module/dedicated_domains/settings',
            'localisation/currency',
            'localisation/country',
            'module/dedicated_domains/domains',
        ]);

        $this->data['newDomain']['currencies'] = $this->currency->getcurrencies();
        $this->data['newDomain']['countries'] = $this->country->getCountries();

        $this->data['domains'] = $this->domains->getDomains();

        $this->data['links'] = [
            'newDomain' => $this->url->link(
                'module/dedicated_domains/newDomain',
                '',
                'SSL'
            ),
            'removeDomain' => $this->url->link(
                'module/dedicated_domains/removeDomain',
                '',
                'SSL'
            ),
            'updateDomainLink' => $this->url->link(
                'module/dedicated_domains/updateDomain',
                '',
                'SSL'
            ),
            'settings' => $this->url->link('module/dedicated_domains', '', 'SSL'),
        ];

        if (isset($this->request->post['dedicated_domains'])) {
            $data['settings'] = $this->request->post['dedicated_domains'];
        } elseif ($this->config->get('dedicated_domains')) {
            $data['settings'] = $this->config->get('dedicated_domains');
        }

        $this->document->setTitle($this->language->get('dedicated_domains_heading_title'));

        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_modules'),
            'href' => $this->url->link(
                'marketplace/home',
                '',
                'SSL'
            ),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('dedicated_domains_heading_title'),
            'href' => $this->url->link('module/dedicated_domains', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->template = 'module/dedicated_domains/settings.expand';

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $data['links'] = [
            'submit' => $this->url->link(
                'module/dedicated_domains/updateSettings',
                '',
                'SSL'
            ),
            'domains' => $this->url->link(
                'module/dedicated_domains/domainsList',
                '',
                'SSL'
            ),
            'cancel' => $this->url->link('module/dedicated_domains', '', 'SSL'),
        ];

        $this->data = array_merge($this->data, $data);
        $this->response->setOutput($this->render());
    }

    public function updateSettings()
    {
        if (
            $this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)
        ) {
            $this->redirect(
                $this->url->link(
                    'module/dedicated_domains',
                    '',
                    'SSL'
                )
            );
        }

        $this->init([
            'module/dedicated_domains/settings'
        ]);

        $data = $this->request->post['dedicated_domains'];

        $this->settings->updateSettings(['dedicated_domains' => $data]);

        $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_settings_success');
        $result_json['success'] = '1';

        $this->response->setOutput(json_encode($result_json));

        return;
    }

    public function domainsList()
    {
        $this->init([
            'module/dedicated_domains/domains',
            'localisation/currency',
            'localisation/country',
        ]);

        $this->document->setTitle($this->language->get('dedicated_domains_heading_title_domains_list'));

        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_modules'),
            'href' => $this->url->link(
                'marketplace/home',
                '',
                'SSL'
            ),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('dedicated_domains_heading_title'),
            'href' => $this->url->link('module/dedicated_domains', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['newDomain']['currencies'] = $this->currency->getcurrencies();
        $this->data['newDomain']['countries'] = array_column($this->country->getCountries(), null, 'iso_code_2');

        $this->data['domains'] = $this->domains->getDomains();

        $this->data['links'] = [
            'newDomain' => $this->url->link(
                'module/dedicated_domains/newDomain',
                '',
                'SSL'
            ),
            'removeDomain' => $this->url->link(
                'module/dedicated_domains/removeDomain',
                '',
                'SSL'
            ),
            'updateDomainLink' => $this->url->link(
                'module/dedicated_domains/updateDomain',
                '',
                'SSL'
            ),
            'settings' => $this->url->link('module/dedicated_domains', '', 'SSL'),
        ];

        $this->template = 'module/dedicated_domains/domainsList.tpl';

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->response->setOutput($this->render());
    }

    public function newDomain()
    {
        if (
            $this->request->server['REQUEST_METHOD'] != 'POST' || !isset($this->request->post)
        ) {
            echo 'yea';
            return;
        }

        $this->init([
            'module/dedicated_domains/domains',
            'module/dedicated_domains/product_domain',
            'catalog/product',
            'module/dedicated_domains/settings'
        ]);

        $method = $this->request->post['method'];

        $domain = $this->request->post['domain'];

        if (!$this->settings->validateDomainInputs($domain)) {
            $this->errors[] = $this->settings->errors;
        }

        if ($method == 'newDomain' && $this->domains->getDomainByName($domain['name'])) {
            $this->errors[] = $this->language->get('error_duplicated_domain_name');
        }

        if ( !empty($this->errors) )
        {
            $result_json['success'] = '0';
            $result_json['error']['warning']=$this->language->get('error_warning');
            $result_json['error'] = $this->errors;

            $this->response->setOutput(json_encode($result_json));
            return;
        }

        if ( $method == 'newDomain' )
        {
            // Check if the new domain contains country inserted before in another domain or not
            if($this->domains->getDomainByCountry($domain['country'])){
                
                $result_json['success'] = '0';
                $result_json['error']['warning']=$this->language->get('error_warning');
                $result_json['error'][] =  $this->language->get('error_duplicated_country_with_another_domain');

                $this->response->setOutput(json_encode($result_json));
                return;
            }
            ////////////////////////////////////////////////////////////////////////////////////
            $domain_id = $this->domains->newDomain($domain);
            $this->load->model('setting/setting');
            $settings = $this->model_setting_setting->getSetting('dedicated_domains');

            if(isset($settings['dedicated_domains']['add_all_products'])
            && $settings['dedicated_domains']['add_all_products']){
                $products = $this->product->getProducts();
                $product_ids = array_column($products,'product_id');
                $this->product_domain->insertProductsDomain($product_ids,$domain_id);
            }
            
            $result_json['success'] = '1';
            $result_json['success_msg'] = $this->language->get('success_new_domain');

            $this->response->setOutput(json_encode($result_json));
            return;

        } else if ($method == 'updateExist') {

            $return = [];

            $domainId = $this->request->post['domainId'];


            if (
                isset($domainId) &&
                preg_match('#^[0-9]+$#', $domainId) &&
                $this->domains->getDomainById($domainId)
            ) {
                $this->domains->updateDomain($domainId, $domain);

                $result_json['success'] = '1';
                $result_json['success_msg'] = $this->language->get('success_update_domain');

                $this->response->setOutput(json_encode($result_json));
                return;
            } else {
                $this->errors[] = $this->language->get('error_invalid_domain_id');
                
                $result_json['error'] = $this->errors;
                $result_json['success'] = '0';

                $this->response->setOutput(json_encode($result_json));
                return;
            }

        }

        return;
    }

    public function removeDomain()
    {
        $domainId = $this->request->get['domain_id'];

        if (isset($domainId) && preg_match('#^[0-9]+$#', $domainId)) {
            $this->init([
                'module/dedicated_domains/domains',
                'module/dedicated_domains/settings'
            ]);

            if ($this->domains->getDomainById($domainId)) {
                $this->domains->removeDomain($domainId);
                $this->session->data['success'] = $this->language->get('success_remove_domain');
            } else {
                $this->session->data['errors'][] = $this->language->get('error_invalid_domain_id');
            }
            $this->redirect(
                $this->url->link(
                    'module/dedicated_domains/domainsList',
                    '',
                    'SSL'
                )
            );
        }

        $this->session->data['errors'][] = $this->language->get('error_invalid_domain_id');

        $this->redirect(
            $this->url->link(
                'module/dedicated_domains/domainsList',
                '',
                'SSL'
            )
        );
    }

    public function install()
    {
        $this->init(['module/dedicated_domains/settings']);
        $this->settings->install();
    }

    public function uninstall()
    {
        $this->init(['module/dedicated_domains/settings']);
        $this->settings->uninstall();
    }
}
