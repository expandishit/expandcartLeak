<?php

use Facebook\Facebook;
use Facebook\Exceptions\FacebookSDKException;

/**
 * Facebook catalog products import module
 * 
 * @author MohamedHassanWD <hassan.mohamed.sf@gmail.com>
 * @copyright 2019 ExpandCart
 */
class ControllerModuleFacebookImport extends Controller
{
    /**
     * Errors
     *
     * @var array $error
     */
    private $errors;

    /**
     * Module model
     *
     * @var Model $model
     */
    private $model;

    /**
     * Module queue model
     *
     * @var Model $queue_model
     */
    private $queue_model;

    /**
     * Authenticated Facebook user token
     *
     * @var string|null $token
     */
    private $token;

    /**
     * FacebookAPI Object
     *
     * @var Facebook $fb
     */
    private $fb;

    /**
     * The default view of the module
     *
     * @return void
     */
    public function index(): void
    {
        $content_url=$this->request->get['content_url'];

        if ($content_url == null || trim($content_url) == ""){
            $content_url = "module/facebook_import/export";
        }

        $data = array();
        $data = array_merge($data, $this->load->language('module/facebook_import'));

        $this->document->setTitle($this->language->get('heading_title'));
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', '' , true)
        );

//        $data['breadcrumbs'][] = array(
//            'text' => $this->language->get('text_apps'),
//            'href' => $this->url->link('marketplace/home', '', true)
//        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('module/facebook_import', '', true)
        );

        $data['content_url'] = $content_url;

        try {
            $data['content'] = $this->getChild($content_url);
        }
        catch (\ExpandCart\Foundation\Exceptions\FileException $e) {
            $this->redirect($this->url->link('marketplace/home', '', 'SSL'));
        }
        $this->template = 'module/facebook_import/index.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );
        $this->data=$data;

        $this->response->setOutput($this->render());
    }

    public function dashboard(): void
    {
        $this->init();

        $this->load->language('module/facebook_import');

        $this->load->model('setting/setting');

        //If there is a token in the DB get
        $this->data['facebook_token'] = $this->token;
        $this->data['domain'] = DOMAINNAME;

        $this->document->setTitle($this->language->get('fc_heading_title'));

        //Set view breadcrumb list
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
            'href'      => $this->url->link('module/facebook_import', '', 'SSL'),
            'separator' => ' :: '
        );


        $this->data['queue_jobs'] = $this->queue_model->getJobs();

        //Set the view file
        $this->template = 'module/facebook_import/dashboard.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');

        $this->response->setOutput($this->render());
    }
    /**
     * The import view
     *
     * @return void
     */
    public function import(): void
    {
        $this->init();

        $this->load->language('module/facebook_import');

        $this->load->model('setting/setting');

        //If there is a token in the DB get 
        $this->data['facebook_token'] = $this->token;
        $this->data['domain'] = DOMAINNAME;

        $this->document->setTitle($this->language->get('fc_heading_title'));

        //Set view breadcrumb list
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
            'href'      => $this->url->link('module/facebook_import', '', 'SSL'),
            'separator' => ' :: '
        );

        //Set the view file
        $this->template = 'module/facebook_import/import.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');

        $this->response->setOutput($this->render());
    }

    /**
     * The export view 
     *
     * @return void
     */
    public function export(): void
    {
        $this->init();

        $this->initializer([
            'filter' => 'catalog/product_filter'
        ]);

        $this->language->load('catalog/product');
        $this->load->language('module/facebook_import');
        $this->language->load('catalog/product_filter');

        $this->load->model('setting/setting');

        //If there is a token in the DB get 
        $this->data['facebook_token'] = $this->token;
        $this->data['domain'] = DOMAINNAME;

        $this->document->setTitle($this->language->get('fc_heading_title'));

        //Set view breadcrumb list
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
            'href'      => $this->url->link('module/facebook_import', '', 'SSL'),
            'separator' => ' :: '
        );

        //Set the view file
        $this->template = 'module/facebook_import/export.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );
        $this->data['filterElements'] = $this->filter->getFilter();

        $this->data['filterData'] = $this->session->data['filterData'];
        
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');

        $this->response->setOutput($this->render());
    }

    /**
     * The sync view 
     *
     * @return void
     */
    public function sync(): void
    {
        $this->init();

        $this->load->language('module/facebook_import');

        $this->load->model('setting/setting');

        //If there is a token in the DB get 
        $this->data['facebook_token'] = $this->token;

        $this->document->setTitle($this->language->get('fc_heading_title'));

        //Set view breadcrumb list
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
            'href'      => $this->url->link('module/facebook_import', '', 'SSL'),
            'separator' => ' :: '
        );

        //Set the view file
        $this->template = 'module/facebook_import/sync.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');

        $this->response->setOutput($this->render());
    }

    /**
     * The settings view 
     *
     * @return void
     */
    public function settings(): void
    {
        $this->init();

        $this->load->language('module/facebook_import');

        $this->load->model('setting/setting');
        $this->load->model('localisation/language');

        //If there is a token in the DB get 
        $this->data['facebook_token'] = $this->token;
        $this->data['domain'] = DOMAINNAME;
        $this->data['queue_jobs'] = $this->queue_model->getJobs();

        //CHeck if post request
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $lang = $this->request->post['export_language'];
            $lang_keys = array_keys($this->model_localisation_language->getLanguages());

            //Make sure the language is present and real
            $lang = in_array($lang, $lang_keys) ? $lang : $this->config->get('config_admin_language');

            //Set the language in settings
            $this->model_setting_setting->insertUpdateSetting('config', ['facebook_export_language' => $lang]);
            $this->config->set('facebook_export_language', $lang);

            $this->model_setting_setting->insertUpdateSetting('config', ['facebook_plain_desc' => $this->request->post['export_plain_desc']]);
            $this->config->set('facebook_plain_desc', $lang);

            $this->model_setting_setting->insertUpdateSetting('config', ['ignore_facebook_product_quantity' => $this->request->post['ignore_facebook_product_quantity']]);
            $this->config->set('ignore_facebook_product_quantity', $lang);
        }

        $this->document->setTitle($this->language->get('fc_heading_title'));

        //Set view breadcrumb list
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
            'href'      => $this->url->link('module/facebook_import', '', 'SSL'),
            'separator' => ' :: '
        );

        //Set the view file
        $this->template = 'module/facebook_import/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->data['languages'] = $this->model_localisation_language->getLanguages();
        $this->data['export_language'] = $this->config->get('facebook_export_language') ? $this->config->get('facebook_export_language') : $this->config->get('config_admin_language');
        $this->data['export_plain_desc'] = $this->config->get('facebook_plain_desc') ?? 0;
        $this->data['ignore_facebook_product_quantity'] = $this->config->get('ignore_facebook_product_quantity') ?? 0;

        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');

        $this->response->setOutput($this->render());
    }

    /**
     * Store the token in the settings table
     *
     * @return void
     */
    public function store_token(): void
    {
        $this->init();

        if (isset($this->request->get['facebook_access_token'])) {
            //Set module settings
            $settings = [
                'facebook_access_token' => $this->request->get['facebook_access_token'],
            ];
            $this->token = $this->request->get['facebook_access_token'];

            //Load setting model
            $this->load->model('setting/setting');

            //Insert/Update settings
            $this->model_setting_setting->insertUpdateSetting('facebook_import', $settings);
        }

        $this->redirect('/admin/module/facebook_import');
    }

    /**
     * Redirect to auth server
     *
     * @return void
     */
    public function redirect_to_auth(): void
    {
        //redirect to auth server
        $this->redirect('https://auth.expandcart.com/store-data.php?domain_name=' . DOMAINNAME);
    }

    /**
     * Install the app on the store
     *
     * @return void
     */
    public function install(): void
    {
        $this->init();
        $this->model->install();

        \Filesystem::createDir('image/data/facebook_products');
        \Filesystem::setPath('image/data/facebook_products')->changeMod('writable');
    }

    /**
     * Uninstall the app on the store
     *
     * @return void
     */
    public function uninstall(): void
    {
        $this->init();
        $this->model->uninstall();

    
    }

    /**
     * Initialize app model
     *
     * @return void
     */
    private function init(): void
    {
        // set_time_limit(1000000);
        $this->load->model('module/facebook_import/facebook_import');
        $this->model = $this->model_module_facebook_import_facebook_import;

        $this->load->model('module/facebook_import/facebook_catalog_queue');
        $this->queue_model = $this->model_module_facebook_import_facebook_catalog_queue;

        $this->load->model('tool/image');

        try {
            $this->fb = new Facebook([
                'app_id' => '329928231042768',
                'app_secret' => '89b8ba250426527ac48bafeead4bb19c',
            ]);

            //Check if token is present
            $this->token = $this->config->get('facebook_access_token');
        } catch (FacebookSDKException $e) {
            echo json_encode(['error' => 'Something wrong happend, please contact support.' . $e->getMessage()]);
        }
    }

    /**
     * Get list of authenticated user businesses
     *
     * @return void
     */
    public function getBusinesses(): void
    {
        $this->init();

        if ($this->token) {
            try {
                $data['items'] = $this->fb->get('me/businesses', $this->token)->getGraphEdge()->asArray();

                echo json_encode($data);
            } catch (FacebookSDKException $e) {
                echo json_encode(['error' => 'Something wrong happend, please contact support.' . $e->getMessage()]);
            }
        }
    }

    /**
     * Get list of business catalogs
     *
     * @return void
     */
    public function getCatalogs(): void
    {
        $this->init();

        if ($this->token && isset($this->request->get['business_id'])) {
            try {
                $business_id = (int) $this->request->get['business_id'];

                $data = $this->fb->get($business_id . '?fields=owned_product_catalogs{id,name,product_count}', $this->token)->getDecodedBody();

                echo json_encode($data);
            } catch (FacebookSDKException $e) {
                echo json_encode(['error' => 'Something wrong happend, please contact support.' . $e->getMessage()]);
            }
        }
    }

    /**
     * Get the products from Facebook catalog and insert them in the Temp table
     *
     * @param int $catalog_id The facebook catablog ID
     * @param int $limit The number of products to limit
     * @param string|null $next_cursor The next cursor to paginate with
     * @param string|null $previous_cursor The previous cursor to paginate with
     * @return void
     */
    public function fetchFacebookProductsToDB($catalog_id = null, $limit = 100, $next_cursor = null, $previous_cursor = null): array
    {
        //Initialize the facebook objects
        $this->init();


        try {
            $return_data = [];

            $catalog_id = $this->request->get['catalog_id'];

            //Set the graphql request
            $request_ql = $catalog_id . '/products?fields=custom_label_0,retailer_id,id,name,description,currency,price,sale_price,brand,category,availability,image_url,inventory,custom_label_1,custom_label_2,custom_label_3,product_catalog{product_count}&limit=' . $limit;

            if ($next_cursor != null) {
                $request_ql .= '&after=' . $next_cursor;
            }

            if ($previous_cursor != null) {
                $request_ql .= '&before=' . $previous_cursor;
            }

            //Send the request and get products
            $request = $this->fb->get($request_ql, $this->token)->getDecodedBody();

            //Loop to insert into the DB
            foreach ($request['data'] as $key => $product) {
                //check if product has custom_label_0
                //which means this product is exported from expandcart
                //then the expand product id is the retailer id
                $expand_product_id = (isset($product['custom_label_0']) && $product['custom_label_0'] == 'expandcart') ? $product['retailer_id'] : "null";

                $insert_sql = '
                    insert into product_facebook values (
                        null,
                        ' . $expand_product_id . ',
                        "' . $product['retailer_id'] . '",
                        null,
                        ' . $product['product_catalog']['id'] . ',
                        ' . $product['id'] . ',
                        \'' . $this->db->escape(json_encode($product)) . '\',
                        NOW(),
                        NOW()
                    )
                    ON DUPLICATE KEY UPDATE 
                    payload=\'' . $this->db->escape(json_encode($product)) . '\',
                    facebook_product_id = ' . $product['id'] . ',
                    facebook_catalog_id = ' . $product['product_catalog']['id'] . ',';
                
                //if there is no new expand_product_id leave its value as it is
                if( $expand_product_id != 'null')                     
                    $insert_sql .= ' expand_product_id = ' . $expand_product_id. ',';

                $insert_sql .= 'updated_at=NOW()';

                $q = $this->db->query($insert_sql);

                //Check if product is imported before
                $is_imported = $this->db->query('select * from product_facebook where expand_product_id is not null AND expand_product_id IN (SELECT product_id FROM product) and facebook_product_id=' . $product['id'])->num_rows > 0 ? true : false;
                $return_data['products'][$key]['id'] = $product['id'];
                $return_data['products'][$key]['image_url'] = $product['image_url'];
                $return_data['products'][$key]['name'] = $product['name'];
                $return_data['products'][$key]['price'] = $product['price'];
                $return_data['products'][$key]['currency'] = $product['currency'];
                $return_data['products'][$key]['brand'] = $product['brand'];
                $return_data['products'][$key]['custom_label_1'] = $product['custom_label_1'];
                $return_data['products'][$key]['is_imported'] = $is_imported;
            }

            $return_data['next_cursor'] = $request['paging']['cursors']['after'];;
            $return_data['previous_cursor'] = $request['paging']['cursors']['before'];;
            $return_data['productCount'] = $request['data'][0]['product_catalog']['product_count'] ?? $request['data'][0]['product_catalog']['product_count'];

            return $return_data;
        } catch (Exception $e) {
            //Log the error
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get all products from Facebook catalog and insert them in the Temp table
     *
     * @param int $catalog_id The facebook catablog ID
     * @param int $limit The number of products to limit
     * @param string|null $next_cursor The next cursor to paginate with
     * @param string|null $previous_cursor The previous cursor to paginate with
     * @return void
     */
    public function fetchAllFacebookProductsToDB($catalog_id = null, $limit = 1000, $job_id = 0, $token = null)
    {
        //Comment this line when running in console
        // $catalog_id = !$catalog_id ? $this->request->post['catalog_id'] : $catalog_id;
        // $job_id = !$job_id ? $this->request->post['job_id'] : $job_id;
        // $token = !$token ? $this->request->post['token'] : $token;

        $next_cursor = '';

        //Initialize the facebook objects
        // set_time_limit(1000000);
        $this->load->model('module/facebook_import/facebook_import');
        $this->model = $this->model_module_facebook_import_facebook_import;

        $this->load->model('module/facebook_import/facebook_catalog_queue');
        $this->queue_model = $this->model_module_facebook_import_facebook_catalog_queue;


        try {
            $this->fb = new Facebook([
                'app_id' => '329928231042768',
                'app_secret' => '89b8ba250426527ac48bafeead4bb19c',
            ]);

            //Check if token is present
            $this->token = $token;

            //Get catalog info
            $catalog_gql = $catalog_id . '/?fields=product_count,business';

            //Send the request and get products
            $catalog = $this->fb->get($catalog_gql, $this->token)->getDecodedBody();

            $i = 1;
            $number_of_pages = ceil($catalog['product_count'] / $limit);

            $products_gql = $catalog_id . '/products?fields=description,custom_label_0,sale_price,retailer_id,id,name,currency,price,brand,category,availability,image_url,inventory,custom_label_1,custom_label_2,custom_label_3,product_catalog{product_count}&limit=' . $limit;

            //Set queue job status to processing
            $sql_update_q = 'update facebook_catalog_queue_jobs set status="processing",product_count="' . $catalog['product_count'] . '",updated_at=NOW() WHERE job_id=' . $job_id;

            $this->db->query($sql_update_q);

            for ($i; $i <= $number_of_pages; $i++) {
                $new_req = $products_gql;

                if ($next_cursor != null) {
                    $new_req .= '&after=' . $next_cursor;
                }

                //Send the request and get products
                $request = $this->decodedBodyToObject($this->fb->get($new_req, $this->token)->getDecodedBody());

                //Loop to insert into the DB
                foreach ($request->data as $key => $product) {
                    $expand_product_id = (isset($product->custom_label_0) && $product->custom_label_0 == 'expandcart') ? $product->retailer_id : "null";
                    $insert_sql = '
                        insert into product_facebook
                            (id, expand_product_id, retailer_id, facebook_business_id, facebook_catalog_id, facebook_product_id, payload, created_at, updated_at) 
                            values 
                           (
                                null,
                                 ' . $expand_product_id . ',
                                "' . $this->db->escape($product->retailer_id) . '",
                                null,
                                ' . $this->db->escape($product->product_catalog->id) . ',
                                ' . $this->db->escape($product->id) . ',
                                \'' . $this->db->escape(json_encode($product)) . '\',
                                NOW(),
                                NOW()
                            )
                        ON DUPLICATE KEY UPDATE 
                        payload=\'' . $this->db->escape(json_encode($product)) . '\',
                        facebook_product_id = ' . $this->db->escape($product->id) . ',
                        facebook_catalog_id = ' . $product->product_catalog->id . ',';
                    //if there is no new expand_product_id leave its value as it is
                    if( $expand_product_id != 'null')                     
                        $insert_sql .= ' expand_product_id = ' . $expand_product_id. ',';

                    $insert_sql .= 'updated_at=NOW()';

                    $this->db->query($insert_sql);

                    //get brand or insert
                    $b = $this->db->query('SELECT * FROM manufacturer where name="' . $this->db->escape($product->brand) . '"');
                    if ($b->num_rows > 0) {
                        $manufacturer_id = $b->row['manufacturer_id'];
                    } else {
                        $s = 'insert into manufacturer (name) values ("' . $this->db->escape($product->brand) . '");';
                        $this->db->query($s);
                        $manufacturer_id = $this->db->getLastId();

                        $s = 'insert into manufacturer_to_store values (' . $manufacturer_id . ',0);';
                        $this->db->query($s);
                    }


                    $inserted_product_id = $this->model->createOrUpdate($product, $manufacturer_id);

                    //Insert into relation queue
                    $sql_ins_relation = 'insert into facebook_catalog_queue_product values (
                        null,
                        ' . $job_id . ',
                        ' . $product->id . ',
                        ' . $inserted_product_id . ',
                        NOW()
                    );';

                    $this->db->query($sql_ins_relation);
                }

                $next_cursor = $request->paging->cursors->after;

                sleep(1);
            }

            //Set queue job status to completed
            $sql_update_q = 'update facebook_catalog_queue_jobs set status="completed",finished_at=NOW(),updated_at=NOW() WHERE job_id=' . $job_id;

            $this->db->query($sql_update_q);

            try {
                //Send email to admin when finished
                $this->load->language('module/facebook_import');
                //PHPMailer Object
                $mail = new PHPMailer;
                //Enable SMTP debugging.
                $mail->SMTPDebug = 1;
                $mail->CharSet = "UTF-8";
                //Set PHPMailer to use SMTP.
                $mail->isSMTP();
                //Set SMTP host name
                $mail->Host = $this->config->get('config_smtp_host');
                //Set this to true if SMTP host requires authentication to send email
                $mail->SMTPAuth = true;
                //Provide username and password
                $mail->Username = $this->config->get('config_smtp_username');
                $mail->Password = $this->config->get('config_smtp_password');
                //If SMTP requires TLS encryption then set it
                $mail->SMTPSecure = "ssl";
                //Set TCP port to connect to
                $mail->Port = $this->config->get('config_smtp_port');

                //From email address and name
                $mail->From = (!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email'));
                $mail->FromName = $this->config->get('config_name');

                //To address and name
                $mail->addAddress((!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email')), $this->config->get('config_name'));

                //Send HTML or Plain Text email
                $mail->isHTML(true);

                $mail->Subject = $this->language->get('email_success_subject');
                $mail->Body = $this->language->get('email_success_message');
                $mail->AltBody = $this->language->get('email_success_message');
                $mail->send();
            } catch (Exception $exception) {
                file_put_contents(
                    BASE_STORE_DIR . 'logs/facebook_errors.txt',
                    'Something wrong happend, please contact support.' . $exception->getMessage() . "\n",
                    FILE_APPEND
                );
            }

        } catch (FacebookSDKException $e) {
            file_put_contents(BASE_STORE_DIR . 'logs/errors.txt', $e, FILE_APPEND);
            die;

            //Set queue job status to completed
            $sql_update_q = 'update facebook_catalog_queue_jobs set status="failed",updated_at=NOW(),payload="' . $e . '" WHERE job_id=' . $job_id;

            $this->db->query($sql_update_q);
        }
    }

    /**
     * Get list of catalog products
     *
     * @return void
     */
    public function getProducts(): void
    {
        //Get request variables
        $limit = isset($this->request->get['limit']) ? (int) $this->request->get['limit'] : 100;
        $catalog_id = isset($this->request->get['catalog_id']) ? (int) $this->request->get['catalog_id'] : null;
        $next_cursor = isset($this->request->get['next_cursor']) ? (string) $this->request->get['next_cursor'] : null;
        $previous_cursor = isset($this->request->get['previous_cursor']) ? (string) $this->request->get['previous_cursor'] : null;

        //check for the catalog id in the request
        if (!$catalog_id) {
            echo json_encode(['status' => 'ERROR', 'message' => 'You have to provide a catalog id.']);
            die;
        }

        //Check and validate for the limit in the request
        if ($limit < 10) {
            $limit = 10;
        } else if ($limit > 100) {
            $limit = 100;
        }

        //Load the data from Facebook|Database
        $products = $this->fetchFacebookProductsToDB($catalog_id, $limit, $next_cursor, $previous_cursor);

        echo json_encode($products);
    }

    /**
     * Import the selected products to the user's store
     *
     * @return void
     */
    public function handleImport(): void
    {
        $this->init();
        $this->load->language('module/facebook_import');

        if ($this->token && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $selected_products = array_column($this->request->post['products'], 'id');

            $products_in_db = $this->model->getProductsByIds($selected_products, false);

            if (count($products_in_db) > 0) {
                $pr = [];
                $image_name = null;
                $manufacturer_id = null;

                //insert into product table
                foreach ($products_in_db as $product) {
                    $product_decoded = json_decode($product['payload']);

                    //get brand or insert
                    //check if the brand is not empty
                    if(!empty($product_decoded->brand)){
                        $b = $this->db->query('SELECT * FROM manufacturer where name="' . $product_decoded->brand . '"');
                        if ($b->num_rows > 0) {
                            $manufacturer_id = $b->row['manufacturer_id'];
                        } else {
                            $s = 'insert into manufacturer (name) values ("' . $product_decoded->brand . '");';
    
                            $this->db->query($s);
                            $manufacturer_id = $this->db->getLastId();
    
                            $s = 'insert into manufacturer_to_store values (' . $manufacturer_id . ',0);';
                            $this->db->query($s);
                        }

                        $inserted_product_id = $this->model->createOrUpdate($product_decoded, $manufacturer_id);
                    }else{
                        //Insert product with brand id 0
                        $inserted_product_id = $this->model->createOrUpdate($product_decoded);
                    }
   
                    $pr[] = $inserted_product_id;
                }
            }

            echo json_encode(['status' => 'success', 'message' => $this->language->get('res_import_success')]);
        } else {
            echo json_encode(['error' => $this->language->get('res_no_product')]);
        }
    }

    /**
     * handle the import all request
     *
     * @return void
     */
    public function handleImportAll(): void
    {
        $this->init();

        $this->load->language('module/facebook_import');

        if ($this->token && $_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->request->post['catalog_id']) {
                $catalog_id = $this->request->post['catalog_id'];

                $check_job = $this->queue_model->getLatestJobByCatalogId($catalog_id);


                if ($check_job && !in_array($check_job['status'], ['completed', 'failed'])) {
                    echo json_encode(['status' => 'error', 'message' => 'You already made a queue job for this catalog, please wait until it is finished.']);
                    die;
                }

                //Add the job to the DB
                $job_id = $this->queue_model->addJob($catalog_id, 'import');

                //Run the queue process
                $file_location = DIR_SYSTEM . 'library/facebook_queue.php';

                $token = $this->config->get('facebook_access_token');

                $storecode = STORECODE;

                // shell_exec("php $file_location $job_id \"$token\" import >/dev/null 2>&1 &");
                shell_exec("php $file_location $job_id \"$token\" \"import\" $storecode >/dev/null 2>&1 &");

                echo json_encode(['status' => 'success', 'message' => $this->language->get('res_queue_success')]);
            } else {
                echo json_encode(['status' => 'error', 'message' => $this->language->get('res_no_catalog')]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => $this->language->get('res_error_happened')]);
        }
    }

    /**
     * Innitialize the import queue job
     *
     * @param int $job_id The job ID to initialize
     * @param string $token Facebook access token
     */
    public function importAllProductsQueueJob($job_id = 0, $token = null)
    {
        $this->init();

        //comment if using console
        // $job_id = $job_id ? $job_id : $this->request->post['job_id'];
        // $token = $token ? $token : $this->request->post['token'];

        //Get job data
        $sql_job = "SELECT * FROM facebook_catalog_queue_jobs WHERE job_id=" . (int) $this->db->escape($job_id) . " LIMIT 1";

        $job = $this->db->query($sql_job)->row;

        try {
            $this->fetchAllFacebookProductsToDB($job['catalog_id'], 1000, $job_id, $token);
            // print_r($job);
        } catch (FacebookSDKException $e) {
            file_put_contents(BASE_STORE_DIR . 'logs/errors.txt', $e->getMessage() . "\n", FILE_APPEND);
        }
    }


    /**
     * Handle the export of selected products
     *
     * @return void
     */
    public function handleExport()
    {
        $this->init();

        //Load the language
        $this->load->language('module/facebook_import');

        //Load the models
        $this->load->model('catalog/product');
        $this->load->model('catalog/manufacturer');
        $this->load->model('localisation/language');

        //Check for proper request
        if (!$this->token || $_SERVER['REQUEST_METHOD'] != 'POST') {
            echo json_encode(['status' => 'error', 'message' => $this->language->get('res_select_product')]);
            die;
        }

        //Set the required vars
        $selected_products = $this->request->post['products'];
        $catalog_id = $this->request->post['catalog_id'];

        //Check for products
        if (count($selected_products) < 1) {
            echo json_encode(['status' => 'error', 'message' => $this->language->get('res_select_product')]);
            die;
        }

        //Check for catalog
        if (!$catalog_id) {
            echo json_encode(['status' => 'error', 'message' => $this->language->get('res_select_catalog')]);
            die;
        }

        //Check operation language first
        $export_lang = $this->config->get('facebook_export_language') ? $this->config->get('facebook_export_language') : $this->config->get('config_admin_language');
        $export_lang_id = $this->model_localisation_language->getLanguageByCode($export_lang);

        $export_plain_desc = $this->config->get('facebook_plain_desc') ?? 0;

        $this->config->set('config_language_id', $export_lang_id['language_id']);

        //1. Get all the products
        $products = $this->model_catalog_product->getProductsByIds($selected_products);

        //2. Check each product in product_facebook
        //   If: it has a an expand_product_id and retailer_id then update with the retailer_id
        //   Else: update with the expand_product_id
        $sql_p = '';
        $data = [];
        $data['allow_upsert'] = true;
        foreach ($products as $key => $product) {
            $sql_p = 'SELECT * FROM product_facebook WHERE expand_product_id=' . $product['product_id'] . ' LIMIT 1';
            $p = $this->db->query($sql_p)->row;

            if ($p['retailer_id'] == $product['product_id']) {
                $retailer_id = $p['retailer_id'];
            } else {
                $retailer_id = $product['product_id'];
            }
            if(isset($product['model']) && !empty(trim($product['model']))) {
                $productModel = $product['model'];
            }else{
                $productModel = $retailer_id;
            }
            if(isset($product['sku']) && !empty(trim($product['sku']))) {
                $productSku = $product['sku'];
            }else{
                $productSku = $retailer_id;
            }
            $brand = $this->model_catalog_manufacturer->getManufacturer($product['manufacturer_id'])['name'];
            $https_catalog_domain = HTTPS_CATALOG;
            if (defined('HTTPS_FACEBOOK_CATALOG_DOMAIN')) {
                $https_catalog_domain = HTTPS_FACEBOOK_CATALOG_DOMAIN;
            }

            $productDesc = substr($product['description'], 0, 9999);
            if((int)$export_plain_desc)
                $productDesc = strip_tags($productDesc);

            // apply tax if they exists
            $configTax = $this->config->get('config_tax');
            if ($configTax != 'price'){
                $product['price'] = number_format($this->tax->calculate($product['price'], $product['tax_class_id'], $configTax), 2, '.', '');
            }
            if (isset($product['sale_price']) && (float)$product['sale_price'] > 0) {
                $product['sale_price'] = number_format($this->tax->calculate($product['sale_price'], $product['tax_class_id'], $configTax), 2, '.', '');
            }

            $url = new Url($https_catalog_domain,$https_catalog_domain);
            $product_url=str_replace('admin/','',str_replace('&amp;','&',$url->frontUrl('product/product', 'product_id=' . $product['product_id'],'SSL')));
            $data['requests'][$key]['method'] = 'UPDATE';
            $data['requests'][$key]['retailer_id'] = $retailer_id;
            $data['requests'][$key]['data']['name'] = $product['name'];
            $data['requests'][$key]['data']['price'] = (int) ($product['price'] * 100);
            $data['requests'][$key]['data']['sale_price'] = (int) ($product['sale_price'] * 100);
            $data['requests'][$key]['data']['description'] = $productDesc;
            $data['requests'][$key]['data']['currency'] = $product['currency'];
            $data['requests'][$key]['data']['inventory'] = $product['quantity'];
            $data['requests'][$key]['data']['availability'] = $product['availability'];
            $data['requests'][$key]['data']['condition'] = $product['condition'];
            $data['requests'][$key]['data']['url'] =  $product_url ;
            $data['requests'][$key]['data']['image_url'] = $this->model_tool_image->resize($product["image"],800,800);
            $data['requests'][$key]['data']['brand'] = $brand ? $brand : 'No Brand';
            $data['requests'][$key]['data']['custom_label_0'] = 'expandcart';
            $data['requests'][$key]['data']['custom_label_1'] = "language_id:".$export_lang_id['language_id'];
            $data['requests'][$key]['data']['custom_label_2'] = $productModel;
            $data['requests'][$key]['data']['custom_label_3'] = $productSku;
        }

        try {
            //Send the batch request to Facebook
            $gql = $this->fb->post($catalog_id . '/batch', $data, $this->token, null, 'v12.0');

            //Get the update handle and insert it in the exports table
            $graphNode = $gql->getGraphNode()->asArray();

            foreach ($graphNode['handles'] as $handle) {
                $this->db->query('INSERT INTO facebook_catalog_exports values (
                    null,
                    ' . $this->user->getId() . ',
                    "' . $this->db->escape($handle) . '",
                    NOW()
                )');
            }
        } catch (Exception $e) {
            (new Log('facebook_errors.txt'))->write($e->getMessage());
            die;
        }

        echo json_encode(['status' => 'success', 'message' => $this->language->get('res_export_success')]);
    }

    /**
     * Handle the sync opertaion
     *
     * @return void
     */
    public function handleSync()
    {
        $this->init();

        $this->load->language('module/facebook_import');

        //Check for proper request
        if (!$this->token || $_SERVER['REQUEST_METHOD'] != 'POST') {
            echo json_encode(['status' => 'error', 'message' => $this->language->get('res_select_product')]);
            die;
        }

        if (!$this->request->post['catalog_id']) {
            echo json_encode(['status' => 'error', 'message' => $this->language->get('res_no_catalog_provided')]);
            die;
        }

        $catalog_id = $this->request->post['catalog_id'];

        $check_job = $this->queue_model->getLatestJobByCatalogId($catalog_id);

        if ($check_job && !in_array($check_job['status'], ['completed', 'failed'])) {
            echo json_encode(['status' => 'error', 'message' => $this->language->get('res_queue_exists')]);
            die;
        }

        //Add the job to the DB
        $job_id = $this->queue_model->addJob($catalog_id, 'sync');

        // TODO: Run the export function
        // TODO: Run the import function
        //Run the queue process
        $file_location = DIR_SYSTEM . 'library/facebook_queue.php';
        $token = $this->config->get('facebook_access_token');
        $storecode = STORECODE;
        $http_catalog = HTTPS_CATALOG;
        shell_exec("php $file_location $job_id \"$token\" \"export\" $storecode $http_catalog >/dev/null 2>&1 &");
        shell_exec("php $file_location $job_id \"$token\" \"import\" $storecode >/dev/null 2>&1 &");

        echo json_encode(['status' => 'success', 'message' => $this->language->get('sync_success')]);
    }

    /**
     * handle the export all request
     *
     * @return void
     */
    public function handleExportAll(): void
    {
        // TODO: Check the number of all products
        // split them into 5000 items chunks
        // loop hrough all these chunks and send the request to the batch
        // get back all the product ids from facebook to keep the product_facebook table in sync

        $this->init();

        $this->load->language('module/facebook_import');

        //Check for proper request
        if (!$this->token || $_SERVER['REQUEST_METHOD'] != 'POST') {
            echo json_encode(['status' => 'error', 'message' => $this->language->get('res_select_product')]);
            die;
        }

        if (!$this->request->post['catalog_id']) {
            echo json_encode(['status' => 'error', 'message' => $this->language->get('res_no_catalog_provided')]);
            die;
        }

        $catalog_id = $this->request->post['catalog_id'];

        $check_job = $this->queue_model->getLatestJobByCatalogId($catalog_id);

        if ($check_job && !in_array($check_job['status'], ['completed', 'failed'])) {
            echo json_encode(['status' => 'error', 'message' => $this->language->get('res_queue_exists')]);
            die;
        }

        //Add the job to the DB
        $job_id = $this->queue_model->addJob($catalog_id, 'export');

        //Run the queue process
        $file_location = DIR_SYSTEM . 'library/facebook_queue.php';
        $token = $this->config->get('facebook_access_token');
        $storecode = STORECODE;
        $http_catalog = HTTPS_CATALOG;
        shell_exec("php $file_location $job_id \"$token\" \"export\" $storecode $http_catalog >/dev/null 2>&1 &");
        echo json_encode(['status' => 'success', 'message' => $this->language->get('res_queue_success')]);
    }

    /**
     * Innitialize the export queue job
     *
     * @param int $job_id The job ID to initialize
     * @param string $token Facebook access token
     */
    public function exportAllProductsQueueJob($job_id = 0, $token = null,$http_catalog = null)
    {
        $this->init();

        //Get job data
        $sql_job = "SELECT * FROM facebook_catalog_queue_jobs WHERE job_id=" . (int) $this->db->escape($job_id) . " LIMIT 1";

        $job = $this->db->query($sql_job)->row;

        try {
            $this->exportAllFacebookProductsFromDB($job['catalog_id'], $job_id, $token, $http_catalog);
        } catch (FacebookSDKException $e) {
            file_put_contents(
                BASE_STORE_DIR . 'logs/facebook_errors.txt',
                'Something wrong happend, please contact support.' . $e->getMessage() . "\n",
                FILE_APPEND
            );
            die;
        }
    }


    /**
     * Get all products from DB catalog and export them to Facebook
     *
     * @param int $catalog_id The facebook catablog ID
     * @param int $limit The number of products to limit
     * @param int $job_id The job id
     * @param string $token Faceboko token
     * @return void
     */
    public function exportAllFacebookProductsFromDB($catalog_id = null, $job_id = 0, $token = null,$http_catalog=null)
    {
        $this->init();

        //Comment this line when running in console
        // $catalog_id = !$catalog_id ? $this->request->post['catalog_id'] : $catalog_id;
        // $job_id = !$job_id ? $this->request->post['job_id'] : $job_id;
        // $token = !$token ? $this->request->post['token'] : $token;

        //Get all products from database
        $this->load->model('catalog/product');
        $this->load->model('catalog/manufacturer');
        $this->load->model('localisation/language');

        //Check operation language first
        $export_lang = $this->config->get('facebook_export_language') ? $this->config->get('facebook_export_language') : $this->config->get('config_admin_language');
        $export_lang_id = $this->model_localisation_language->getLanguageByCode($export_lang);

        $export_plain_desc = $this->config->get('facebook_plain_desc') ?? 0;

        $this->config->set('config_language_id', $export_lang_id['language_id']);

        //Get all products
        $products = $this->model_catalog_product->getProducts(['filter_status'=>1]);

        //Set queue job status to processing
        $sql_update_q = 'update facebook_catalog_queue_jobs set status="processing",product_count="' . count($products) . '",updated_at=NOW() WHERE job_id=' . $job_id;

        $this->db->query($sql_update_q);

        //Loop on all products and add each product to the final array
        $data = [];
        $data['allow_upsert'] = true;
        foreach ($products as $key => $product) {
            $sql_p = 'SELECT * FROM product_facebook WHERE expand_product_id=' . $product['product_id'] . ' LIMIT 1';
            $p = $this->db->query($sql_p)->row;

            if (isset($p['retailer_id']) && $p['retailer_id'] == $product['product_id']) {
                $retailer_id = $p['retailer_id'];
            } else {
                $retailer_id = $product['product_id'];
            }

            if(isset($product['model']) && !empty(trim($product['model']))) {
                $productModel = $product['model'];
            }else{
                $productModel = $retailer_id;
            }
            if(isset($product['sku']) && !empty(trim($product['sku']))) {
                $productSku = $product['sku'];
            }else{
                $productSku = $retailer_id;
            }
            $http_catalog = ($http_catalog != null) ? $http_catalog : HTTPS_CATALOG;
            $brand = $this->model_catalog_manufacturer->getManufacturer($product['manufacturer_id'])['name'];
            $https_catalog_domain = HTTPS_CATALOG;
            if (defined('HTTPS_FACEBOOK_CATALOG_DOMAIN')) {
                $https_catalog_domain = HTTPS_FACEBOOK_CATALOG_DOMAIN;
            }

            $productDesc = substr($product['description'], 0, 9999);
            if((int)$export_plain_desc)
                $productDesc = strip_tags($productDesc);

            $product_specials = $this->model_catalog_product->getProductSpecials($product['product_id']);
            foreach ($product_specials as $product_special) {
                $date_start = $product_special['date_start'];
                $date_end = $product_special['date_end'];
                if (
                    (!$date_start || $date_start == null || $date_start == '0000-00-00' || $date_start < date('Y-m-d')) &&
                    (!$date_end || $date_end == null || $date_end == '0000-00-00' || $date_end > date('Y-m-d'))
                ) {
                    $product['sale_price'] = $product_special['price'] ?? 0.00;
                    break;
                }
            }

            // apply tax if they exists
            $configTax = $this->config->get('config_tax');
            if ($configTax != 'price'){
                $product['price'] = number_format($this->tax->calculate($product['price'], $product['tax_class_id'], $configTax), 2,'.', '');
            }
            if (isset($product['sale_price']) && (float)$product['sale_price'] > 0) {
                $product['sale_price'] = number_format($this->tax->calculate($product['sale_price'], $product['tax_class_id'], $configTax), 2, '.', '');
            }

            $url = new Url($https_catalog_domain,$https_catalog_domain);
            $product_url=str_replace('admin/','',str_replace('&amp;','&',$url->frontUrl('product/product', 'product_id=' . $product['product_id'],'SSL')));
            $data['requests'][$key]['method'] = 'UPDATE';
            $data['requests'][$key]['retailer_id'] = $retailer_id;
            $data['requests'][$key]['data']['name'] = $product['name'];
            $data['requests'][$key]['data']['price'] = (int) ($product['price'] * 100);
            $data['requests'][$key]['data']['sale_price'] = (int) ($product['sale_price'] * 100);
            $data['requests'][$key]['data']['description'] = $productDesc;
            $data['requests'][$key]['data']['currency'] = $this->config->get('config_currency');
            $data['requests'][$key]['data']['inventory'] = $product['quantity'];
            $data['requests'][$key]['data']['availability'] = $product['quantity'] > 0 ? 'in stock' : 'out of stock';
            $data['requests'][$key]['data']['condition'] = 'new';
            $data['requests'][$key]['data']['url'] =   $product_url;
            $data['requests'][$key]['data']['image_url'] = $this->model_tool_image->resize($product["image"],800,800);
            $data['requests'][$key]['data']['brand'] = $brand ? $brand : 'No Brand';
            $data['requests'][$key]['data']['custom_label_0'] = 'expandcart';
            $data['requests'][$key]['data']['custom_label_1'] = "language_id:".$export_lang_id['language_id'];
            $data['requests'][$key]['data']['custom_label_2'] = $productModel;
            $data['requests'][$key]['data']['custom_label_3'] = $productSku;
        }
        //Save the handle
        try {
            //Send the batch request to Facebook
            $gql = $this->fb->post($catalog_id . '/batch', $data, $this->token, null, 'v12.0');

            //Get the update handle and insert it in the exports table
            $graphNode = $gql->getGraphNode()->asArray();


            foreach ($graphNode['handles'] as $handle) {
                $this->db->query('INSERT INTO facebook_catalog_exports values (
                    null,
                    null,
                    "' . $this->db->escape($handle) . '",
                    NOW()
                )');
            }
        } catch (Exception $e) {
            file_put_contents(
                BASE_STORE_DIR . 'logs/facebook_errors.txt',
                $e->getMessage() . "\n",
                FILE_APPEND
            );
            //Set queue job status to completed
            $sql_update_q = 'update facebook_catalog_queue_jobs set status="failed",updated_at=NOW(),payload="' . $e . '" WHERE job_id=' . $job_id;

            $this->db->query($sql_update_q);
        }

            //Set queue job status to completed
            $sql_update_q = 'update facebook_catalog_queue_jobs set status="completed",finished_at=NOW(),updated_at=NOW() WHERE job_id=' . $job_id;

            $this->db->query($sql_update_q);

        try {
            //Send email to admin when finished
            $this->load->language('module/facebook_import');
            //PHPMailer Object
            $mail = new PHPMailer;
            //Enable SMTP debugging.
            $mail->SMTPDebug = 1;
            $mail->CharSet = "UTF-8";
            //Set PHPMailer to use SMTP.
            $mail->isSMTP();
            //Set SMTP host name
            $mail->Host = $this->config->get('config_smtp_host');
            //Set this to true if SMTP host requires authentication to send email
            $mail->SMTPAuth = true;
            //Provide username and password
            $mail->Username = $this->config->get('config_smtp_username');
            $mail->Password = $this->config->get('config_smtp_password');
            //If SMTP requires TLS encryption then set it
            $mail->SMTPSecure = "ssl";
            //Set TCP port to connect to
            $mail->Port = $this->config->get('config_smtp_port');

            //From email address and name
            $mail->From = (!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email'));
            $mail->FromName = $this->config->get('config_name');

            //To address and name
            $mail->addAddress((!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email')), $this->config->get('config_name'));

            //Send HTML or Plain Text email
            $mail->isHTML(true);

            $mail->Subject = $this->language->get('email_success_subject');
            $mail->Body = $this->language->get('email_success_message');
            $mail->AltBody = $this->language->get('email_success_message');
            $mail->send();
        } catch (Exception $exception) {
            file_put_contents(
                BASE_STORE_DIR . 'logs/facebook_errors.txt',
                'Something wrong happend, please contact support.' . $e->getMessage() . "\n",
                FILE_APPEND
            );
        }
    }

    /**
     * Convert an array to object
     *
     * @param array $Array
     * @return object
     */
    private function decodedBodyToObject($Array)
    {

        // Create new stdClass object 
        $object = new stdClass();

        // Use loop to convert array into 
        // stdClass object 
        foreach ($Array as $key => $value) {
            if (is_array($value)) {
                $value = $this->decodedBodyToObject($value);
            }
            $object->$key = $value;
        }
        return $object;
    }

    /**
     * Log the user out from the app and delete his token
     * 
     * @return void
     */
    public function logout_facebook()
    {
        $this->load->model('setting/setting');

        $admin_link = $this->url->link('module/facebook_import');

        $url = 'https://www.facebook.com/logout.php?next=' . $admin_link . '&access_token=' . $this->config->get('facebook_access_token');

        $this->model_setting_setting->deleteByKeys(['facebook_access_token']);

        $this->response->redirect($url);
    }

    public function resetCurrentJobs(){
        $this->load->language('module/facebook_import');
        $this->load->model('module/facebook_import/facebook_catalog_queue');
        $json['success'] = 1;
        $json['message']=$this->language->get('reset_current_queue_done');
        try {
            $this->model_module_facebook_import_facebook_catalog_queue->resetCurrentJobs();
        }catch (\Exception $e){
            $json['success'] = 0;
            $json['message']=$this->language->get('reset_current_queue_error');
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }


}
