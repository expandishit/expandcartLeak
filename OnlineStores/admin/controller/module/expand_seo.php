<?php

class ControllerModuleExpandSeo extends Controller
{
    private $model;

    private $patterns = [
        '@search@' => '([\p{L}\p{N}\-\_\s]+)',
        '@slug@' => '([\p{L}\p{N}\-\_]+)',
        '@product_id@' => '([0-9\_]+)',
        '@path@' => '([0-9\_]+)',
        '@manufacturer_id@' => '([0-9\_]+)',
        '@information_id@' => '([0-9\_]+)',
        '@post_id@' => '([0-9\_]+)',
        '@category_id@' => '([0-9\_]+)',
        '@seller_id@' => '([0-9\_]+)'
    ];

    public function index()
    {
        $this->load->language('module/expand_seo');

        $this->load->model('setting/setting');

        $data['expand_seo'] = array();

        if (isset($this->request->post['expand_seo'])) {
            $expand_seo['settings'] = $this->request->post['expand_seo'];
        } elseif ($this->config->get('expand_seo')) {
            $expand_seo['settings'] = $this->config->get('expand_seo');
        }

        $this->init();

        $this->document->setTitle($this->language->get('es_heading_title'));

        $data['schemaAction'] = $this->url->link(
            'module/expand_seo/updateSettings',
            '',
            'SSL'
        );

        $data['cancel'] = $this->url->link(
            'module/expand_seo',
            '',
            'SSL'
        );

        $this->load->model('setting/setting');

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_module'),
            'href'      => $this->url->link('marketplace/home', '', 'SSL'),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('module/expand_seo', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->template = 'module/expand_seo/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $data['expand_seo'] = $expand_seo;


        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');

        $this->data = $data;

        $this->response->setOutput($this->render());
    }

    public function schemaList()
    {
        $this->load->language('common/header');
        $this->load->language('module/expand_seo');

        $this->init();

        $this->document->setTitle($this->language->get('text_options'));

        $data['insert'] = $this->url->link(
            'module/expand_seo/insert',
            '',
            'SSL'
        );

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_module'),
            'href'      => $this->url->link('marketplace/home', '', 'SSL'),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('module/expand_seo', '', 'SSL'),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_options'),
            'href'      => $this->url->link('module/expand_seo/schemaList', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->template = 'module/expand_seo/schemaList.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->data = $data;

        $this->response->setOutput($this->render());
    }
    /*
     * Get schema list for datatable
     */
    public function dtHandler() {

        $this->load->language('module/expand_seo');

        $this->init();

        $request = $_REQUEST;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        $start = $request['start'];
        $length = $request['length'];

        $columns = array(
            0 => 'seo_id',
            1 => 'seo_group',
            2 => 'language',
            3 => 'schema_status',
            4 => 'schema_parameters',
            5 => '',
        );
        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];


        $return = $this->model->dtHandler($start, $length, $search, $orderColumn, $orderType);

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

    public function updateSettings()
    {
        $this->load->language('module/expand_seo');
        if (
            $this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)
        ) {
            $response['success'] = '0';
            $response['success_msg'] = "";
            $this->response->setOutput(json_encode($response));
            return;
        }

        $this->init();

        $post = $this->request->post;
//        echo '<pre>';print_r($post['schema']);exit;

        if (isset($post['formType'])) {
            if ($post['formType'] == 'settings') {
                $settings = $post['settings'];

                $this->model->updateSettings(['expand_seo' => $settings]);

                $response['success'] = '1';
                $response['success_msg'] = $this->language->get('text_success');
                $this->response->setOutput(json_encode($response));

            } else if ($post['formType'] == 'schema') {
                $schema = $post['schema'];

                if (!isset($post['schema'])) {
                    $post['schema'] = '0';
                }
                $url = [];
                $parts = $parameters = [];

                array_walk_recursive($schema['parts'], function ($value, $key) use (&$parts, &$parameters) {
//                    $parts[$key] = htmlspecialchars($value);
                    $parts[$key] = (isset($this->patterns[$value]) ? $this->patterns[$value] : $value);
                    if (preg_match('#\@.*?\@#', $value)) {
                        $parameters[$key] = trim($value, '@');
                    }
                });

                ksort($parts, SORT_NUMERIC);
                ksort($parameters, SORT_NUMERIC);

                $partsValues = array_values($parts);

                if (
                    in_array(substr($post['schema']['prefix'], -1), ['-', '_', '/']) === false &&
                    in_array($partsValues[0], ['-', '_', '/'])
                ) {
                    $schema['prefix'] = $post['schema']['prefix'] . $partsValues[0];
                }

                $parts = $this->model->trimArray($parts, ['/', '_', '-'], 'both');

//                $schema['language'] = (
//                    isset($schema['parts']['languages'][0]) ? $schema['parts']['languages'][0] : 'global'
//                );

                $schema['language'] = ($schema['language'] ? $schema['language']: 'global');

                $schema['parts'] = $parts;


                /*if (isset($schema['prefix']) && $schema['prefix'] != '') {
                    array_unshift($parts, '/');
                    array_unshift($parts, $schema['prefix']);
                }
                if ($language) {
                    array_unshift($parts, '/');
                    array_unshift($parts, $language);
                }*/

//                print_r($parameters);print_r($parts);exit;

                $schema['url'] = implode($parts);
                $schema['schema_parameters'] = array_values($parameters);
//                print_r(json_encode($schema['parts'], JSON_UNESCAPED_SLASHES));exit;
//                $schema['url'] = $language . '/' . $schema['prefix'] . '/' .implode($parts);

                $this->model->insertSchema($schema);

                $response['success'] = '1';
                $response['success_msg'] = $this->language->get('text_success');
                $this->response->setOutput(json_encode($response));
                return;
            }
        }
    }

    public function dtDelete()
    {
        $this->init();
        $this->load->language('module/expand_seo');
        $id = $this->request->post['id'];

        if (!$id) {
            $result_json['success'] = '0';
            $result_json['error'] = $this->language->get('es_error_invalid_id');
        }
        else{
            $this->model->deleteSchema($id);
            $result_json['success'] = '1';
            $result_json['success_msg'] =  $this->language->get('es_deleted_success');
        }

        $this->response->setOutput(json_encode($result_json));
        return;

    }

    public function insert(){

        $this->load->language('common/header');
        $this->load->language('module/expand_seo');

        $this->load->model('setting/setting');

        $data['expand_seo'] = array();

        if (isset($this->request->post['expand_seo'])) {
            $expand_seo['settings'] = $this->request->post['expand_seo'];
        } elseif ($this->config->get('expand_seo')) {
            $expand_seo['settings'] = $this->config->get('expand_seo');
        }

        $this->init();

        $this->document->setTitle($this->language->get('es_alias'));

        $data['schemaAction'] = $this->url->link(
            'module/expand_seo/updateSettings',
            '',
            'SSL'
        );
        $data['cancel'] = $this->url->link(
            'module/expand_seo/schemaList',
            '',
            'SSL'
        );

        $this->load->model('setting/setting');

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_module'),
            'href'      => $this->url->link('marketplace/home', '', 'SSL'),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('module/expand_seo', '', 'SSL'),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('es_alias'),
            'href'      => $this->url->link('module/expand_seo/schemaList', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->template = 'module/expand_seo/insertSchema.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        // Get all installed languages 
        $this->load->model('localisation/language');
        $languages=$this->model_localisation_language->getLanguages();
        foreach ($languages as $language_code => $value) {
            $expand_seo['languages'][]=$language_code;    
        }
        $expand_seo['separators'] = [
            '-', '/', '_'
        ];

        $expand_seo['products']['fields'] = [
            '@product_id@' => $this->language->get('es_product_id'),
            '@slug@' => $this->language->get('es_product_name'),
        ];
        $expand_seo['fields'] = [
            'common/home' => ['name' => $this->language->get('es_home_page'),'fields' => null],
            'product/product' => [
                'name' => $this->language->get('text_product'),
                'fields' => [
                    '@product_id@' => $this->language->get('es_id'),
                    '@slug@' => $this->language->get('es_product_name'),
                ]
            ],
            'product/category' => [
                'name' => $this->language->get('text_category'),
                'fields' => [
                    '@path@' => $this->language->get('es_id'),
                    '@slug@' => $this->language->get('es_category_name'),
                ]
            ],
            'product/manufacturer/info' => [
                'name' => $this->language->get('text_manufacturer'),
                'fields' => [
                    '@manufacturer_id@' => $this->language->get('es_id'),
                    '@slug@' => $this->language->get('es_manufacturer_name'),
                ]
            ],
            'product/search' => [
                'name' => $this->language->get('es_search_pages'),
                'fields' => [
                    '@search@' => $this->language->get('es_search_name'),
                ]
            ],
            'blog/blog' => [
                'name' => $this->language->get('es_blog_home'),
                'fields' => null
            ],
            'blog/post' => [
                'name' => $this->language->get('es_blog_posts'),
                'fields' => [
                    '@post_id@' => $this->language->get('es_blog_post_id'),
                    '@slug@' => $this->language->get('es_blog_post_title'),
                ]
            ],
            'blog/category' => [
                'name' => $this->language->get('es_blog_categories'),
                'fields' => [
                    '@category_id@' => $this->language->get('es_blog_category_id'),
                    '@slug@' => $this->language->get('es_blog_category_title'),
                ]
            ],
            'product/compare' => ['name' => $this->language->get('es_compare_page'),'fields' => null],
            'product/special' => ['name' => $this->language->get('es_special_page'),'fields' => null],
            'checkout/cart' => ['name' => $this->language->get('es_cart_page'),'fields' => null],
            'checkout/checkout' => ['name' => $this->language->get('es_checkout_page'),'fields' => null],
            'checkout/checkoutv2' => ['name' => $this->language->get('es_checkout_page_v2'),'fields' => null],
            'information/sitemap' => ['name' => $this->language->get('es_sitemap_page'),'fields' => null],
            'information/contact' => ['name' => $this->language->get('es_contact_page'),'fields' => null],
            'information/information' => [
                'name' => $this->language->get('es_information_pages'),
                'fields' => [
                    '@information_id@' => $this->language->get('es_id'),
                    '@slug@' => $this->language->get('es_information_name'),
                ]
            ],
            'account/login' => ['name' => $this->language->get('es_login_page'),'fields' => null],
            'account/register' => ['name' => $this->language->get('es_register_page'),'fields' => null],
            'seller/register-seller' => ['name' => $this->language->get('es_seller_register_page'),'fields' => null],
            'seller/catalog-seller/profile' => [
                'name' => $this->language->get('es_seller_profile_pages'),
                'fields' => [
                    '@seller_id@' => $this->language->get('es_id'),
                    '@slug@' => $this->language->get('es_nick_name'),
                ]
            ],
            'checkout/success' => ['name' => $this->language->get('es_checkout_sucess_page'),'fields' => null],
            'checkout/error' => ['name' => $this->language->get('es_checkout_error_page'),'fields' => null],
            'checkout/pending' => ['name' => $this->language->get('es_checkout_pending_page'),'fields' => null],
            'payment/oneglobal/callback' => ['name' => $this->language->get('es_one_global_callback'),'fields' => null],
        ];

        $expand_seo['json_fields'] = htmlspecialchars(json_encode($expand_seo['fields']));

        $data['expand_seo'] = $expand_seo;

        $data['domainName'] = HTTP_CATALOG;

        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');

        $data['patterns'] = $this->patterns;

        if (isset($this->session->data['errors'])) {
            $this->data['error_warning'] = $this->session->data['errors']['message'];
        } else {
            $this->data['error_warning'] = '';
        }

        $this->data = $data;

        $this->response->setOutput($this->render());
    }
    
    private function init()
    {
        $this->load->model('module/expand_seo/settings');

        $this->model = $this->model_module_expand_seo_settings;
    }

    public function install()
    {
        $this->init();

        $this->model->install();
    }

    public function uninstall()
    {
        $this->init();

        $this->model->uninstall();
    }
}
