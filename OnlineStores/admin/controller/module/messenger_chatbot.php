<?php


use Facebook\Facebook;
use Facebook\Exceptions\FacebookSDKException;
use ExpandCart\Foundation\Providers\DedicatedDomains;

class ControllerModuleMessengerChatbot extends Controller
{
    /**
     * @var array
     */
    private $error = [];
    /**
     * @var array
     */
    private $result_json = ['success' => false, 'message' => '', 'code' => ''];
    /**
     * @var string
     */
    private $name = 'messenger_chatbot';
    /**
     * @var string
     */
    private $facebook_app_id = '329928231042768';
    /**
     * @var string
     */
    private $facebook_secret_key = '89b8ba250426527ac48bafeead4bb19c';
    /**
     *
     * @var Facebook $fb
     */
    private $fb;
    /**
     * @var string
     */
    private $redirect_page ='https://auth.expandcart.com/messenger_chatbot.php';
    // private $redirect_page ='https://f7b13e693dcc.ngrok.io/private_replies/messenger_chatbot.php';
    /**
     * @var string
     */
    private $accessToken = '';
    /**
     * @var string
     */
    private $user_id = '';

     /**
     * @var array
     */
    private $plugin_settings = [];


    public function test(){
        echo '<pre>';
        $this->init();

        // sandy id is 100067192178470
        // zamalek id is 100032059564705
        // $localeResponse = $this->fb->post('pages_id_mapping', ["user_ids" => "100067192178470", "appsecret_proof" => $this->facebook_secret_key ], 'EAAEsEWcDPtABABSyphUmH8s7pIjUggNYmwtW7ZCR8y59MYAjuYePg8T3eQ5rUQC28shkIecMzlJG5OnIstBCsI7hR6ZADSjT3SuHwYEtGe7XO97pT7HlXpkQwnYSO0cBRkro8MoZCGRklNZB3c1GcdRPSjSrDarDaqy6TBGCZCdmHsD47qXy4')->getDecodedBody();
        // try {      
        //     $localeResponse = $this->fb->get('5930975683587032' . '?fields=locale,name', 'EAAEsEWcDPtABAN5Q19Lekd5WLZBR8GWGTVE3Cdr5lAZAaARxe1LFmg3c3MvSebFBbYlu2wYlRsQsNZAQtOul98gTMFhkxFMlY4GiusZANzTNSiAFIHCi0JFAi9UeQEMme9lM2KgjwvQ56KdPrfZAe4YqcI1PAOYgHpvYBrmYuUDB0snHclhQI')->getDecodedBody();
        //     print_r($localeResponse);
        // } catch (Exception $e) {
        //     echo $e->getMessage();
        // }
        // $apptoken = $this->fb->get('oauth/access_token' . '?client_id=' . $this->facebook_app_id . '&client_secret=' . $this->facebook_secret_key . '&grant_type=client_credentials', $this->accessToken)->getDecodedBody();
        // $localeResponse = $this->fb->get('100067192178470', 'EAAEsEWcDPtABAB5tWX9eeOp0Xu8FAb4PIGqFOMYjyE26lsvqeT9QTIord25Nxy9QacZCL1seoAm2lQOV7tzViTh8SACChFJpb7tg13ZCjdknbWg6rPZAXrJzV3QZAdIugUw09FLiyyuVj0Wsm2HWZCeMFskzVjemC1OoYD0wabkZBql8ltuMDM')->getDecodedBody();


        // $response = $this->fb->get('100067192178470?fields=locale', 'EAAEsEWcDPtABADTQqRoD5cVCFA3Grrh9ZCSCLUCTp1ey5xWro4MY2I4PPzxk5pgA0OEZBHgxLpTDmUxHGLg69EsBi95qWaiEPWuLcaFEtgT2A6HZAc9KocnVFLEf73ZAPG4Gx4j9RZAtWXGUKFB7ddPaffglsN2q5jJMAfcjPRRb1DZBKV5dSn')->getDecodedBody();
        // $response = $this->fb->get('/me/permissions', $this->accessToken)->getDecodedBody();

        // $query = $this->db->query('select * from messenger_chatbot_replies where page_id = "44444" limit 1');
        // print_r($query->row['attributes']);
        // $query = $this->db->query('select * from messenger_chatbot_replies where page_id = "33333" limit 1');
        // print_r(json_decode($query->row['attributes']));
        $query = $this->db->query('select * from messenger_chatbot_replies where page_id = "22222" limit 1');
        print_r($query->row['attributes']);
        // $query = $this->db->query('select * from messenger_chatbot_replies where page_id = "22222" limit 1');
        // print_r(json_decode($query->row['attributes']));
        // $query = $this->db->query('select * from messenger_chatbot_replies where page_id = "33333" limit 1');
        // print_r($query->row['attributes']);

        // $query = $this->db->query('select * from messenger_chatbot_pages');
        // print_r($query->rows);
        // $query = $this->db->query('select * from messenger_chatbot_replies');
        // print_r($query->rows);
        die;
    }


    public function index()
    {

        // temporal lines ...
        if(!$this->db->check(['order' => ['psid']], 'column') ){
            $this->db->query("ALTER TABLE `" . DB_PREFIX . "order` ADD `psid` BIGINT(20) NULL AFTER `shipping_trackId`;");
        }
        // end temporal lines

        $this->init();
        $this->language->load('setting/setting');
        $this->language->load('module/messenger_chatbot');

        $this->document->setTitle($this->language->get('heading_title'));

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
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('module/messenger_chatbot', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_main_account_setting'),
            'href' => $this->url->link('module/messenger_chatbot', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->load->model('setting/setting');
        $this->load->model('module/messenger_chatbot/messenger_chatbot');
        $this->data['settings'] = $this->model_setting_setting->getSetting('config');
        $this->data['mcb_settings'] = $this->model_setting_setting->getSetting($this->name);

        $connected_pages = $this->model_module_messenger_chatbot_messenger_chatbot->get_connected_pages();
        if(!isset($this->data['mcb_settings']['access_token']) || empty($this->data['mcb_settings']['access_token'])){
            $this->redirect($this->url->link('module/messenger_chatbot/setup'));
        } else {
            try {
                $pagesObject = $this->fb->get($this->data['mcb_settings']['user_id'] . "/accounts?fields=name,access_token,picture,fan_count,link", $this->data['mcb_settings']['access_token'])->getDecodedBody();
            } catch(Exception $e) {
                $this->disable_plugin(false);
                $this->model_module_messenger_chatbot_messenger_chatbot->logout();
                $this->redirect($this->url->link('module/messenger_chatbot/setup'));
            }
            $pages = $pagesObject['data'];
            $pages = array_column($pages, 'id');
            $connected_pages = $this->model_module_messenger_chatbot_messenger_chatbot->get_connected_pages($pages);
            $this->data['connected_pages'] = count($connected_pages);
            // if(count($connected_pages) && strpos($_SERVER['HTTP_REFERER'], 'marketplace/app')){
            //     $this->redirect($this->url->link('messenger_chatbot/private_reply'));
            // }
        }

        $this->data['profile_details'] = $this->fb->get($this->data['mcb_settings']['user_id'] . '?fields=name,picture', $this->data['mcb_settings']['access_token'])->getDecodedBody();

        $pagesObject = $this->fb->get($this->data['mcb_settings']['user_id'] . "/accounts?fields=name,access_token,picture,fan_count,link", $this->data['mcb_settings']['access_token'])->getDecodedBody();
        $pages = $pagesObject['data'];

        $connected_pages = $this->model_module_messenger_chatbot_messenger_chatbot->get_connected_pages();
        $connected_pages = array_column($connected_pages, 'page_id');
        $stored_pages = $this->model_module_messenger_chatbot_messenger_chatbot->get_pages($this->data['mcb_settings']['user_id']);
        $stored_pages = array_column($stored_pages, 'page_id');

        $this->data['default_page_id'] = $this->model_module_messenger_chatbot_messenger_chatbot->get_default_page($this->data['mcb_settings']['user_id'])['page_id'];

        $this->data['plugin_status'] = isset($this->plugin_settings['status']) ? $this->plugin_settings['status'] : 0;

        $default_found = false;
        $plugin_available_pages = [];
        foreach ($pages as &$page) {
            if(in_array($page['id'], $stored_pages)){
                $this->model_module_messenger_chatbot_messenger_chatbot->update_page($page['id'], [
                    'access_token' => $page['access_token'], 
                    'user_id' => $this->data['mcb_settings']['user_id']
                ]);
            }
            else{
                $this->model_module_messenger_chatbot_messenger_chatbot->insert_page([
                    'page_id' => $page['id'],
                    'user_id' => $this->data['mcb_settings']['user_id'],
                    'access_token' => $page['access_token']
                ]);
            }

            if(in_array($page['id'], $connected_pages)){
                $page['connected'] = 1;
                $plugin_available_pages[] = $page;

                // temporal lines ...
                $this->fb->post($page['id'] . '/subscribed_apps', ['subscribed_fields' => 'feed,messaging_postbacks,messages'], $page['access_token']);
                // end temporal lines
                
            }
            else{
                $page['connected'] = 0;
            }

            if($page['id'] == $this->data['default_page_id']){
                $page['default'] = 1;
                $default_found = true;
            }
            else{
                $page['default'] = 0;
            }

            if(isset($this->plugin_settings['page_id']) && $page['id'] == $this->plugin_settings['page_id']){
                $this->data['plugin_page'] = $page;
            }
        }

        uasort($pages, function($a, $b){
            return $a['default'] < $b['default'] ? 1 : ($a['connected'] < $b['connected'] ? 1 : -1);
        });

        uasort($plugin_available_pages, function($a, $b){
            return $a['id'] == $this->data['plugin_page']['id'] ? -1 : 1;
        });

        if(!$default_found){
            $this->model_module_messenger_chatbot_messenger_chatbot->clear_default($this->user_id);
        }

        $this->data['pages'] = $pages;
        $this->data['plugin_available_pages'] = $plugin_available_pages;

        $this->data['main_message'] = json_decode($this->session->data['main_message']);
        unset($this->session->data['main_message']);

        $this->data['domain'] = DOMAINNAME;
        $this->data['store_code'] = STORECODE;
        $this->data['facebook_app_id'] = $this->facebook_app_id;
        $this->data['redirect_page'] = $this->redirect_page;
        $this->template = 'module/messenger_chatbot/main.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->response->setOutput($this->render());
    }

    public function setup()
    {
        $this->init();
        $this->language->load('setting/setting');
        $this->language->load('module/messenger_chatbot');

        $this->document->setTitle($this->language->get('heading_title'));

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
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('module/messenger_chatbot', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('setup_title'),
            'href' => $this->url->link('module/messenger_chatbot', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->load->model('setting/setting');
        $this->load->model('module/messenger_chatbot/messenger_chatbot');
        $this->data['settings'] = $this->model_setting_setting->getSetting('config');
        $this->data['mcb_settings'] = $this->model_setting_setting->getSetting($this->name);

        if(isset($this->data['mcb_settings']['access_token']) && !empty($this->data['mcb_settings']['access_token'])){

            $pagesObject = $this->fb->get($this->data['mcb_settings']['user_id'] . "/accounts?fields=name,access_token,picture,fan_count,link,posts", $this->data['mcb_settings']['access_token'])->getDecodedBody();
            $pages = $pagesObject['data'];
            $pages = array_column($pages, 'id');
            $connected_pages = $this->model_module_messenger_chatbot_messenger_chatbot->get_connected_pages($pages);

            if(count($connected_pages)){
                $this->redirect($this->url->link('messenger_chatbot/private_reply'));
            }
            else{
                $this->redirect($this->url->link('module/messenger_chatbot'));
            }
        }

        $this->data['main_message'] = json_decode($this->session->data['main_message']);
        unset($this->session->data['main_message']);

        $this->data['domain'] = DOMAINNAME;
        $this->data['store_code'] = STORECODE;
        $this->data['facebook_app_id'] = $this->facebook_app_id;
        $this->data['redirect_page'] = $this->redirect_page;
        $this->template = 'module/messenger_chatbot/setup.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->response->setOutput($this->render());
    }

    public function connect_page(){
        if(isset($_GET['id'])){
            $this->init();
            $this->language->load('module/messenger_chatbot');
            $this->load->model('module/messenger_chatbot/messenger_chatbot');
            $page = $this->model_module_messenger_chatbot_messenger_chatbot->get_page($_GET['id']);

            $able_to_connect = $this->model_module_messenger_chatbot_messenger_chatbot->able_to_connect($_GET['id']);
            if($able_to_connect['status'] == 1){
                try{
                    $this->fb->post($_GET['id'] . '/subscribed_apps', ['subscribed_fields' => 'feed,messaging_postbacks,messages'], $page['access_token']);
                    $result = $this->model_module_messenger_chatbot_messenger_chatbot->connect_page($_GET['id']);
                    

                    /***************** Start ExpandCartTracking #347726  ****************/

                    $this->load->model('setting/mixpanel');
                    $this->model_setting_mixpanel->trackEvent('Connect Page to Messenger',[
                        'page_id' => $page['page_id']
                    ]);

                    $this->load->model('setting/amplitude');
                    $this->model_setting_amplitude->trackEvent('Connect Page to Messenger',[
                        'page_id' => $page['page_id']
                    ]);

                    /***************** End ExpandCartTracking #347726  ****************/


                    $this->result_json = ['success' => true, 'message' => $this->language->get('text_success_connect_page'), 'code' => '200'];
                }
                catch (Exception $e) {
                    $this->result_json = ['success' => false, 'message' => $e->getMessage(), 'code' => '500'];
                }
            }
            elseif($able_to_connect['status'] == 2){
                $this->result_json = ['success' => false, 'message' => $this->language->get('error_page_connected'), 'code' => '500'];
            }
            elseif($able_to_connect['status'] == 3){
                $this->result_json = ['success' => false, 'message' => $this->language->get('error_page_connected_for_other_store'), 'code' => '500'];
            }

            if($_GET['ajax']){
                $this->response->json($this->result_json);
                return;
            }

            $this->session->data['main_message'] = json_encode($this->result_json);
            $this->redirect($this->url->link('module/messenger_chatbot'));
        }
    }

    public function disconnect_page(){
        if(isset($_GET['id'])){
            $this->init();
            $this->language->load('module/messenger_chatbot');
            $this->load->model('module/messenger_chatbot/messenger_chatbot');
            $page = $this->model_module_messenger_chatbot_messenger_chatbot->get_page($_GET['id']);
            $this->model_module_messenger_chatbot_messenger_chatbot->disconnect_page($_GET['id'], $this->user_id);
            if(isset($this->plugin_settings['page_id']) && $_GET['id'] == $this->plugin_settings['page_id']){
                $this->disable_plugin(false);
            }

            $this->fb->delete($_GET['id'] . '/subscribed_apps', [], $this->facebook_app_id.'|'.$this->facebook_secret_key);

            $this->result_json = ['success' => true, 'message' => $this->language->get('text_success_disconnect_page'), 'code' => '200'];
            $this->session->data['main_message'] = json_encode($this->result_json);
            $this->redirect($this->url->link('module/messenger_chatbot'));
        }
    }

    public function enable_plugin(){
        $this->init();
        $this->language->load('module/messenger_chatbot');
        $this->load->model('module/messenger_chatbot/messenger_chatbot');

        try{
            $page = $this->model_module_messenger_chatbot_messenger_chatbot->get_page($_POST['page_id']);

            $connected_pages = $this->model_module_messenger_chatbot_messenger_chatbot->get_connected_pages([$page['page_id']]);
            if(count($connected_pages) == 0){
                $this->fb->post($page['page_id'] . '/subscribed_apps', ['subscribed_fields' => 'feed,messaging_postbacks,messages'], $page['access_token']);
                $result = $this->model_module_messenger_chatbot_messenger_chatbot->connect_page($page['page_id'], false);

                /***************** Start ExpandCartTracking #347726  ****************/

                $this->load->model('setting/mixpanel');
                $this->model_setting_mixpanel->trackEvent('Connect Page to Messenger',[
                    'page_id' => $page['page_id']
                ]);

                $this->load->model('setting/amplitude');
                $this->model_setting_amplitude->trackEvent('Connect Page to Messenger',[
                    'page_id' => $page['page_id']
                ]);

                /***************** End ExpandCartTracking #347726  ****************/
            }

            // temporal lines ...

            $this->fb->post($page['page_id'] . '/subscribed_apps', ['subscribed_fields' => 'feed,messaging_postbacks,messages'], $page['access_token']);

            // end temporal lines

            $this->fb->post('me/messenger_profile', [
                "ice_breakers" => [
                    [
                        "question" => "🏬 Show me what's in your store",
                        "payload" => '{"type":"ice_breaker","value":"categories"}'
                    ],
                    [
                        "question" => "💥 Special Offers",
                        "payload" => '{"type":"ice_breaker","value":"special_products"}'
                    ],
                    [
                        "question" => "🆕 Show me what's new",
                        "payload" => '{"type":"ice_breaker","value":"latest_products"}'
                    ]
                ],
                "greeting" => [
                    [
                        "locale" => "default",
                        "text" => "Hi {{user_first_name}} 👋, How may I help you today?"
                    ]
                ],
                // "persistent_menu" => [
                //     [
                //         "locale" => "default",
                //         "composer_input_disabled" => false,
                //         "call_to_actions" => [
                //             [
                //                 "type" => "postback",
                //                 "title" => "select Option 1",
                //                 "payload" => "OPTION_1"
                //             ],
                //             [
                //                 "type" => "postback",
                //                 "title" => "select Option 2",
                //                 "payload" => "OPTION_2"
                //             ],
                //             [
                //                 "type" => "postback",
                //                 "title" => "select Option 3",
                //                 "payload" => "OPTION_3"
                //             ]
                //         ]
                //     ]
                // ]
            ], $page['access_token']);

            $data['plugin'] = ['status' => 1, 'page_id' => $page['page_id']];
            $this->load->model('setting/setting');
            $this->model_setting_setting->insertUpdateSetting($this->name, $data);

            if(isset($this->plugin_settings['page_id'])) {
                $previous_page_id = $this->plugin_settings['page_id'];
                $page = $this->model_module_messenger_chatbot_messenger_chatbot->get_page($previous_page_id);
                $this->fb->delete('me/messenger_profile', [
                   'fields' => ['ice_breakers', 'greeting']
                ], $page['access_token']);
            }

            /***************** Start ExpandCartTracking #347725  ****************/

            $this->load->model('setting/mixpanel');
            $this->model_setting_mixpanel->trackEvent('Activate Messenger Bot',[
                'page_id' => $page['page_id']
            ]);

            $this->load->model('setting/amplitude');
            $this->model_setting_amplitude->trackEvent('Activate Messenger Bot',[
                'page_id' => $page['page_id']
            ]);

            /***************** End ExpandCartTracking #347725  ****************/

            $this->result_json = ['success' => true, 'message' => $this->language->get('text_success_plugin_connected')];
            $this->response->json($this->result_json);
            return;
        }
        catch(Exception $e){
            $this->result_json = ['success' => false, 'message' => $e->getMessage()];
            $this->response->json($this->result_json);
            return;
        }
    }

    public function disable_plugin($return_response = true){
        $this->init();
        $this->language->load('module/messenger_chatbot');
        $this->load->model('module/messenger_chatbot/messenger_chatbot');

        try{
            $page_id = $this->plugin_settings['page_id'];
            $page = $this->model_module_messenger_chatbot_messenger_chatbot->get_page($page_id);
            $this->fb->delete('me/messenger_profile', [
               'fields' => ['ice_breakers', 'greeting']
            ], $page['access_token']);

            $data['plugin'] = ['status' => 0];
            $this->load->model('setting/setting');
            $this->model_setting_setting->insertUpdateSetting($this->name, $data);

            if($return_response){
                $this->result_json = ['success' => true, 'message' => $this->language->get('text_success_plugin_disconnected')];
                $this->response->json($this->result_json);
            }
            return;
        }
        catch(Exception $e){
            $this->result_json = ['success' => false, 'message' => $e->getMessage()];
            $this->response->json($this->result_json);
            return;
        }
    }

    public function set_default(){
        if(isset($_GET['id'])){
            $this->init();
            $this->language->load('module/messenger_chatbot');
            $this->load->model('module/messenger_chatbot/messenger_chatbot');
            $this->model_module_messenger_chatbot_messenger_chatbot->set_default($_GET['id'], $this->user_id);

            $this->result_json = ['success' => true, 'message' => $this->language->get('text_success_set_default'), 'code' => '200'];
            $this->session->data['main_message'] = json_encode($this->result_json);
            $this->redirect($this->url->link('module/messenger_chatbot'));
        }
    }

    public function init()
    {
        $this->load->model('setting/setting');
        $mcb_settings = $this->model_setting_setting->getSetting($this->name);
        $this->accessToken = $mcb_settings['access_token'];
        $this->user_id = $mcb_settings['user_id'];
        $this->plugin_settings = isset($mcb_settings['plugin']) ? $mcb_settings['plugin'] : [];
        $this->fb = new Facebook([
            'app_id' => $this->facebook_app_id,
            'app_secret' => $this->facebook_secret_key,
        ]);

    }

    public function logout()
    {
        $this->load->model('module/messenger_chatbot/messenger_chatbot');
        $this->disable_plugin(false);
        $this->model_module_messenger_chatbot_messenger_chatbot->logout();
        $this->redirect($this->url->link('module/messenger_chatbot', '', 'SSL'));
    }

    /**
     * POST
     * Store facebook business user token
     */
    public function storeToken()
    {
        if ($this->request->server['REQUEST_METHOD'] == 'GET' && isset($this->request->get['accessToken']) && isset($this->request->get['userID'])) {

            $this->init();
            $accessToken = $this->request->get['accessToken'];
            $this->user_id = $this->request->get['userID'];
            $this->accessToken = $this->getLongLiveToken($accessToken);
            $data['access_token'] = $this->accessToken;
            $data['user_id'] = $this->request->get['userID'];

            // Add token and Ids to the DB
            $this->load->model('setting/setting');
            $this->model_setting_setting->insertUpdateSetting($this->name, $data);
            
            $this->redirect($this->url->link('module/messenger_chatbot'));
        }
    }

    /**
     * @param $token
     * @return false
     */
    public function getLongLiveToken($token)
    {
        if ($token) {
            try {
                $curl_url = 'https://graph.facebook.com/v11.0/oauth/access_token?grant_type=fb_exchange_token&client_id=' . $this->facebook_app_id . '&client_secret=' . $this->facebook_secret_key . '&fb_exchange_token=' . $token;
                $result = file_get_contents($curl_url);
                $token = json_decode($result)->access_token;
                return $token;
            } catch (FacebookSDKException $e) {
                $this->result_json = ['success' => false, 'message' => $e->getMessage(), 'code' => '400'];
                $this->session->data['main_message'] = json_encode($this->result_json);
            }
        }
        return false;
    }

    public function webhook_callback(){
        $response = file_get_contents("php://input");
        $decodedResponse = json_decode($response);

        // $this->db->query("delete from messenger_chatbot_replies where page_id = '33333'");
        // $this->db->query("insert into messenger_chatbot_replies set attributes = '". $this->db->escape($response) . "', page_id = '33333', name = 'test', type = 'test', applied_on = 'test'");

        $https_catalog_domain = HTTPS_CATALOG;
        if (defined('HTTPS_FACEBOOK_CATALOG_DOMAIN')) {
            $https_catalog_domain = HTTPS_FACEBOOK_CATALOG_DOMAIN;
        }
        $url = new Url($https_catalog_domain,$https_catalog_domain);

        $this->load->model('setting/domainsetting');
        $domains = $this->model_setting_domainsetting->getDomains();
        $https_catalog_domain = HTTPS_CATALOG;
        if (is_array($domains) && count($domains)) {
            $https_catalog_domain = strtolower($domains[0]['DOMAINNAME']) . '/';
        }
        $url = new Url($https_catalog_domain,$https_catalog_domain);

        if(isset($decodedResponse->entry[0]->changes[0]->value->item) && 
            $decodedResponse->entry[0]->changes[0]->value->item == 'comment' && 
            $decodedResponse->entry[0]->changes[0]->value->verb == 'add'){

            // save ineracted user to audience of the page

            try{
                $this->load->model('module/messenger_chatbot/audience');
                $audience = [
                    'psid'      => $decodedResponse->entry[0]->changes[0]->value->from->id,
                    'name'      => $decodedResponse->entry[0]->changes[0]->value->from->name,
                    'page_id'   => $decodedResponse->entry[0]->id
                ];
                $this->model_module_messenger_chatbot_audience->insertOrUpdate($audience);
            }
            catch(Exception $e){}


            $comment_id = $decodedResponse->entry[0]->changes[0]->value->comment_id;
            $user_id = $decodedResponse->entry[0]->changes[0]->value->from->id;
            $post_id = $decodedResponse->entry[0]->changes[0]->value->post_id;
            $page_id = $decodedResponse->entry[0]->id;

            $this->init();
            $this->load->model('module/messenger_chatbot/messenger_chatbot');
            $this->load->model('module/messenger_chatbot/private_reply');

            $page = $this->model_module_messenger_chatbot_messenger_chatbot->check_page($page_id);

            // temporal line ...
            $this->fb->post($page_id . '/subscribed_apps', ['subscribed_fields' => 'feed,messaging_postbacks,messages'], $page['access_token']);
            if(!$this->db->check(['order' => ['psid']], 'column') ){
                $this->db->query("ALTER TABLE `" . DB_PREFIX . "order` ADD `psid` BIGINT(20) NULL AFTER `shipping_trackId`;");
            }
            // end temporal lines

            $reply_info = null;
            $reply_formdata = null;
            $message = null;

            if($post = $this->model_module_messenger_chatbot_messenger_chatbot->check_post($post_id)){
                $reply_info = $this->model_module_messenger_chatbot_private_reply->getPrivateReply($post['reply_id']);
                $reply_formdata = $this->model_module_messenger_chatbot_private_reply->getPrivateReplyFormData($post['reply_id']);
            }
            elseif($page && $page['reply_id']){
                $reply_info = $this->model_module_messenger_chatbot_private_reply->getPrivateReply($page['reply_id']);
                $reply_formdata = $this->model_module_messenger_chatbot_private_reply->getPrivateReplyFormData($page['reply_id']);
            }

            if($reply_info['status']){

                // sandy id is 100067192178470
                $locale = $this->get_locale($user_id, $page['access_token']);

                $message = $this->model_module_messenger_chatbot_private_reply->generateReply($reply_formdata, $locale);

                $reply = [
                    'recipient' => [
                        'comment_id' => $comment_id
                    ],
                    'message' => $message
                ];

                $result = $this->send_messages($page_id, $user_id, $page['access_token'], [$reply], false);

                /***************** Start Sent Private Reply #347727  ****************/

                $this->track_event('Sent Private Reply', [
                    'page_id' => $page_id,
                    'user_id' => $user_id,
                ]);

                /***************** End Sent Private Reply #347727  ****************/

                print_r($result);
                die;
            }
        }
        elseif(isset($decodedResponse->entry[0]->messaging[0]->postback)){

            try{
                $this->load->model('module/messenger_chatbot/audience');
                $audience = [
                    'psid'      => $decodedResponse->entry[0]->messaging[0]->sender->id,
                    'page_id'   => $decodedResponse->entry[0]->id
                ];
                $this->model_module_messenger_chatbot_audience->insertOrUpdate($audience);
            }
            catch(Exception $e){}

            $this->init();
            $this->load->model('module/messenger_chatbot/messenger_chatbot');
            $page_id = $decodedResponse->entry[0]->id;
            $user_id = $decodedResponse->entry[0]->messaging[0]->sender->id;
            $page = $this->model_module_messenger_chatbot_messenger_chatbot->check_page($page_id);
            // $payload = explode(':', $decodedResponse->entry[0]->messaging[0]->postback->payload);
            $payload = json_decode($decodedResponse->entry[0]->messaging[0]->postback->payload);

            $locale = $this->get_locale($user_id, $page['access_token']);

            $replies;

            if($payload->type == 'direct_product'){

                $button_titles = [
                    'en' => [
                        'shop_now' => 'Shop Now',
                        'text' => 'Direct Checkout Link:'
                    ],
                    'ar' => [
                        'shop_now' => 'اشتري الان',
                        'text' => 'رابط شراء مباشر:'
                    ]
                ];

                $replies[] = [
                    'recipient' => [
                        'id' => $decodedResponse->entry[0]->messaging[0]->sender->id
                    ],
                    'message' => [
                        'attachment' => [
                            'type' => 'template',
                            'payload' => [
                                'template_type' => 'button',
                                'text' => $button_titles[$locale]['text'],
                                'buttons' => [
                                    [
                                        'type' => 'web_url',
                                        'url' => $url->frontUrl('checkout/cart/product_checkout', 'product_id='.$payload->value.'&psid='.$decodedResponse->entry[0]->messaging[0]->sender->id, true, false),
                                        'title' => $button_titles[$locale]['shop_now']
                                    ]
                                ]
                            ]
                        ]
                    ]
                ];
            }
            elseif($payload->type == 'ice_breaker'){
                switch ($payload->value) {
                    case 'categories':
                        $this->load->model('catalog/category');
                        $data = array(
                            'sort' => 'RAND()',
                            'start' => 0,
                            'limit' => 10,
                            'status' => '1',
                        );
                        $results = $this->model_catalog_category->getMainCategories($data);
                        if(count($results)){
                            $categories = [];
                            foreach ($results as $result) {
                                $categories[] = [
                                    "content_type" => "text",
                                    "title" => htmlspecialchars_decode($result['name']),
                                    "payload" => '{"type":"products","value":"'.$result['category_id'].'"}'
                                ];
                            }
                            $replies[] = [
                                'recipient' => [
                                    'id' => $decodedResponse->entry[0]->messaging[0]->sender->id
                                ],
                                'message' => [
                                    'text' => 'Which category you are looking for? 👇',
                                    'quick_replies' => $categories
                                ]
                            ];
                        }
                        else{
                            $replies[] = [
                                'recipient' => [
                                    'id' => $decodedResponse->entry[0]->messaging[0]->sender->id
                                ],
                                'message' => [
                                    'text' => 'There is no categories in the store yet!',
                                    'quick_replies' => [
                                        [
                                            'content_type' => 'text',
                                            'title' => 'Start Over',
                                            'payload' => '{"type":"jump","value":"ice_breakers"}'
                                        ]
                                    ]
                                ]
                            ];
                        }
                        break;

                    case 'special_products':
                        $this->registry->set('customer', new Customer($this->registry));
                        $this->registry->set('dedicatedDomains', new DedicatedDomains());
                        $this->load->model('catalog/product', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');
                        $this->load->model('tool/image');

                        $data = array(
                            "sort"  => "RAND()", 
                            "start" => 0, 
                            "limit" => 5
                        );
                        $results = $this->model_catalog_product->getProductSpecials($data);
                        $products = [];

                        $button_titles = [
                            'en' => [
                                'view_detais' => 'View Details',
                                'proceed_to_pay' => 'Get Checkout Link'
                            ],
                            'ar' => [
                                'view_detais' => 'عرض التفاصيل',
                                'proceed_to_pay' => 'رابط شراء مباشر'
                            ]
                        ];

                        foreach ($results as $result) {
                            $products[] = [
                                'title' => htmlspecialchars_decode($result['name']),
                                'image_url' => $this->model_tool_image->resize($result['image'], 200, 200),
                                'subtitle' => $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax'))),
                                'default_action' => [
                                    'type' => 'web_url',
                                    'url' => $url->frontUrl('product/product', 'product_id='.$result['product_id'], true, false),
                                    'messenger_extensions' => false,
                                    'webview_height_ratio' => 'FULL'
                                ],
                                'buttons' => [
                                    [
                                        'type' => 'web_url',
                                        'url' => $url->frontUrl('product/product', 'product_id='.$result['product_id'], true, false),
                                        'title' => $button_titles[$locale]['view_detais']
                                    ],
                                    [
                                        'type' => 'postback',
                                        'title' => $button_titles[$locale]['proceed_to_pay'],
                                        'payload' => '{"type":"direct_product","value":"'.$result['product_id'].'"}',
                                    ]
                                ]
                            ];
                        }

                        if(count($results)){
                            $replies[] = [
                                'recipient' => [
                                    'id' => $decodedResponse->entry[0]->messaging[0]->sender->id
                                ],
                                'message' => [
                                    'text' => 'Select product you prefer 👇'
                                ]
                            ];

                            $replies[] = [
                                'recipient' => [
                                    'id' => $decodedResponse->entry[0]->messaging[0]->sender->id
                                ],
                                'message' => [
                                    'attachment' => [
                                        'type' => 'template',
                                        'payload' => [
                                            'template_type' => 'generic',
                                            'elements' => $products
                                        ]
                                    ],
                                    'quick_replies' => [
                                        [
                                            'content_type' => 'text',
                                            'title' => 'Start Over',
                                            'payload' => '{"type":"jump","value":"ice_breakers"}'
                                        ]
                                    ]
                                ]
                            ]; 
                        }
                        else{
                            $replies[] = [
                                'recipient' => [
                                    'id' => $decodedResponse->entry[0]->messaging[0]->sender->id
                                ],
                                'message' => [
                                    'text' => 'There is no special offers for now!',
                                    'quick_replies' => [
                                        [
                                            'content_type' => 'text',
                                            'title' => 'Start Over',
                                            'payload' => '{"type":"jump","value":"ice_breakers"}'
                                        ]
                                    ]
                                ]
                            ];
                        }
                        break;

                    case 'latest_products':
                        $this->registry->set('customer', new Customer($this->registry));
                        $this->registry->set('dedicatedDomains', new DedicatedDomains());
                        $this->load->model('catalog/product', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');
                        $this->load->model('tool/image');

                        $data = array(
                            "sort"  => "p.date_added", 
                            "order" => "DESC",
                            "start" => 0, 
                            "limit" => 4
                        );
                        $results = $this->model_catalog_product->getProducts($data);
                        $products = [];

                        $button_titles = [
                            'en' => [
                                'view_detais' => 'View Details',
                                'proceed_to_pay' => 'Get Checkout Link'
                            ],
                            'ar' => [
                                'view_detais' => 'عرض التفاصيل',
                                'proceed_to_pay' => 'رابط شراء مباشر'
                            ]
                        ];

                        foreach ($results as $result) {
                            $products[] = [
                                'title' => htmlspecialchars_decode($result['name']),
                                'image_url' => $this->model_tool_image->resize($result['image'], 200, 200),
                                'subtitle' => $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax'))),
                                'default_action' => [
                                    'type' => 'web_url',
                                    'url' => $url->frontUrl('product/product', 'product_id='.$result['product_id'], true, false),
                                    'messenger_extensions' => false,
                                    'webview_height_ratio' => 'FULL'
                                ],
                                'buttons' => [
                                    [
                                        'type' => 'web_url',
                                        'url' => $url->frontUrl('product/product', 'product_id='.$result['product_id'], true, false),
                                        'title' => $button_titles[$locale]['view_detais']
                                    ],
                                    [
                                        'type' => 'postback',
                                        'title' => $button_titles[$locale]['proceed_to_pay'],
                                        'payload' => '{"type":"direct_product","value":"'.$result['product_id'].'"}',
                                    ]
                                ]
                            ];
                        }

                        if(count($results)){
                            $replies[] = [
                                'recipient' => [
                                    'id' => $decodedResponse->entry[0]->messaging[0]->sender->id
                                ],
                                'message' => [
                                    'text' => 'This collection was added recently 👇'
                                ]
                            ];

                            $replies[] = [
                                'recipient' => [
                                    'id' => $decodedResponse->entry[0]->messaging[0]->sender->id
                                ],
                                'message' => [
                                    'attachment' => [
                                        'type' => 'template',
                                        'payload' => [
                                            'template_type' => 'generic',
                                            'elements' => $products
                                        ]
                                    ],
                                    'quick_replies' => [
                                        [
                                            'content_type' => 'text',
                                            'title' => 'Start Over',
                                            'payload' => '{"type":"jump","value":"ice_breakers"}'
                                        ]
                                    ]
                                ]
                            ];
                        }
                        else{
                            $replies[] = [
                                'recipient' => [
                                    'id' => $decodedResponse->entry[0]->messaging[0]->sender->id
                                ],
                                'message' => [
                                    'text' => 'There is no products were added recently!',
                                    'quick_replies' => [
                                        [
                                            'content_type' => 'text',
                                            'title' => 'Start Over',
                                            'payload' => '{"type":"jump","value":"ice_breakers"}'
                                        ]
                                    ]
                                ]
                            ];
                        }
                        break;

                    default:
                        # code...
                        break;
                }
            }

            if(count($replies)){

                $result = $this->send_messages($page_id, $user_id, $page['access_token'], $replies);

                /***************** Start Sent Messenger Message #347739  ****************/

                $this->track_event('Sent Messenger Message', [
                    'page_id' => $page_id,
                    'user_id' => $user_id,
                ]);

                /***************** End Sent Messenger Message #347739  ****************/

                print_r($result);
                die;
            }
        }
        elseif(isset($decodedResponse->entry[0]->messaging[0]->message->quick_reply)){

            try{
                $this->load->model('module/messenger_chatbot/audience');
                $audience = [
                    'psid'      => $decodedResponse->entry[0]->messaging[0]->sender->id,
                    'page_id'   => $decodedResponse->entry[0]->id
                ];
                $this->model_module_messenger_chatbot_audience->insertOrUpdate($audience);
            }
            catch(Exception $e){}

            $this->init();
            $this->load->model('module/messenger_chatbot/messenger_chatbot');
            $page_id = $decodedResponse->entry[0]->id;
            $user_id = $decodedResponse->entry[0]->messaging[0]->sender->id;
            $page = $this->model_module_messenger_chatbot_messenger_chatbot->check_page($page_id);
            $payload = json_decode($decodedResponse->entry[0]->messaging[0]->message->quick_reply->payload);

            $locale = $this->get_locale($user_id, $page['access_token']);

            $typing_on = [
                'recipient' => [
                    'id' => $decodedResponse->entry[0]->messaging[0]->sender->id
                ],
                'sender_action' => 'typing_on'
            ];

            $typing_off = [
                'recipient' => [
                    'id' => $decodedResponse->entry[0]->messaging[0]->sender->id
                ],
                'sender_action' => 'typing_off'
            ];

            $mark_seen = [
                'recipient' => [
                    'id' => $decodedResponse->entry[0]->messaging[0]->sender->id
                ],
                'sender_action' => 'mark_seen'
            ];

            $replies;
            
            if($payload->type == 'products'){

                $this->registry->set('customer', new Customer($this->registry));
                $this->registry->set('dedicatedDomains', new DedicatedDomains());
                $this->load->model('catalog/product', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');
                $this->load->model('tool/image');

                $data = array(
                    'filter_category_id' => $payload->value,
                    'sort'               => 'RAND()',
                    'start'              => 0,
                    'limit'              => 5
                );
                $results = $this->model_catalog_product->getProducts($data);
                $products = [];
                
                $button_titles = [
                    'en' => [
                        'view_detais' => 'View Details',
                        'proceed_to_pay' => 'Get Checkout Link'
                    ],
                    'ar' => [
                        'view_detais' => 'عرض التفاصيل',
                        'proceed_to_pay' => 'رابط شراء مباشر'
                    ]
                ];

                foreach ($results as $result) {
                    $products[] = [
                        'title' => htmlspecialchars_decode($result['name']),
                        'image_url' => $this->model_tool_image->resize($result['image'], 200, 200),
                        'subtitle' => $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax'))),
                        'default_action' => [
                            'type' => 'web_url',
                            'url' => $url->frontUrl('product/product', 'product_id='.$result['product_id'], true, false),
                            'messenger_extensions' => false,
                            'webview_height_ratio' => 'FULL'
                        ],
                        'buttons' => [
                            [
                                'type' => 'web_url',
                                'url' => $url->frontUrl('product/product', 'product_id='.$result['product_id'], true, false),
                                'title' => $button_titles[$locale]['view_detais']
                            ],
                            [
                                'type' => 'postback',
                                'title' => $button_titles[$locale]['proceed_to_pay'],
                                'payload' => '{"type":"direct_product","value":"'.$result['product_id'].'"}',
                            ]
                        ]
                    ];
                }

                if(count($results)){
                    $replies[] = [
                        'recipient' => [
                            'id' => $decodedResponse->entry[0]->messaging[0]->sender->id
                        ],
                        'message' => [
                            'text' => 'Which product is suitable for you? 👇'
                        ]
                    ];

                    $replies[] = [
                        'recipient' => [
                            'id' => $decodedResponse->entry[0]->messaging[0]->sender->id
                        ],
                        'message' => [
                            'attachment' => [
                                'type' => 'template',
                                'payload' => [
                                    'template_type' => 'generic',
                                    'elements' => $products
                                ]
                            ],
                            'quick_replies' => [
                                [
                                    'content_type' => 'text',
                                    'title' => 'Start Over',
                                    'payload' => '{"type":"jump","value":"ice_breakers"}'
                                ]
                            ]
                        ]
                    ];
                }
                else{
                    $replies[] = [
                        'recipient' => [
                            'id' => $decodedResponse->entry[0]->messaging[0]->sender->id
                        ],
                        'message' => [
                            'text' => 'This category does not contain any products!',
                            'quick_replies' => [
                                [
                                    'content_type' => 'text',
                                    'title' => 'Start Over',
                                    'payload' => '{"type":"jump","value":"ice_breakers"}'
                                ]
                            ]
                        ]
                    ];
                }
            }

            elseif($payload->type == 'jump'){

                $replies[] = [
                    'recipient' => [
                        'id' => $decodedResponse->entry[0]->messaging[0]->sender->id
                    ],
                    'message' => [
                        'attachment' => [
                            'type' => 'template',
                            'payload' => [
                                'template_type' => 'button',
                                'text' => 'How may I help you?',
                                'buttons' => [
                                    [
                                        "type" => "postback",
                                        "title" => "🏬 Show me what's in your store",
                                        "payload" => '{"type":"ice_breaker","value":"categories"}'
                                    ],
                                    [
                                        "type" => "postback",
                                        "title" => "💥 Special Offers",
                                        "payload" => '{"type":"ice_breaker","value":"special_products"}'
                                    ],
                                    [
                                        "type" => "postback",
                                        "title" => "🆕 Show me what's new",
                                        "payload" => '{"type":"ice_breaker","value":"latest_products"}'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ];
            }

            if(count($replies)){

                $result = $this->send_messages($page_id, $user_id, $page['access_token'], $replies);

                /***************** Start Sent Messenger Message #347739  ****************/

                $this->track_event('Sent Messenger Message', [
                    'page_id' => $page_id,
                    'user_id' => $user_id,
                ]);

                /***************** End Sent Messenger Message #347739  ****************/

                print_r($result);
                die;
            }
        }
    }

    public function install()
    {
        $this->load->model('module/messenger_chatbot/messenger_chatbot');
        $this->model_module_messenger_chatbot_messenger_chatbot->install();
    }

    public function uninstall()
    {
        $this->load->model('module/messenger_chatbot/messenger_chatbot');
        $this->model_module_messenger_chatbot_messenger_chatbot->uninstall();
    }

    private function get_locale($user_id, $accessToken){
        $locales = [
            'en_US' => 'en',
            'en_UK' => 'en',
            'ar_AR' => 'ar'
        ];

        $locale = 'en';

        try {
            $localeResponse = $this->fb->get($user_id . '?fields=locale', $accessToken)->getDecodedBody();
            $locale = isset($locales[$localeResponse['locale']]) ? $locales[$localeResponse['locale']] : 'en';                
        } catch (Exception $e) {}

        $this->load->model('localisation/language');
        $language = $this->model_localisation_language->getLanguageByCode($locale);
        if(empty($language) || $language['status'] == 0){
            $locale = $this->model_localisation_language->getActiveLanguages()[0]['code'];
        }

        return $locale;
    }

    private function send_messages($page_id, $user_id, $access_token, $messages, $sender_actions = true){
        $url = "https://graph.facebook.com/v11.0/$page_id/messages?access_token=" . $access_token;
        $headers = array("Content-type: application/json");

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $typing_on = [
            'recipient' => [
                'id' => $user_id
            ],
            'sender_action' => 'typing_on'
        ];

        $typing_off = [
            'recipient' => [
                'id' => $user_id
            ],
            'sender_action' => 'typing_off'
        ];

        $mark_seen = [
            'recipient' => [
                'id' => $user_id
            ],
            'sender_action' => 'mark_seen'
        ];

        $result;

        if($sender_actions) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode((object)$mark_seen, JSON_FORCE_OBJECT));
            $result = curl_exec($ch);

            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode((object)$typing_on, JSON_FORCE_OBJECT));
            $result = curl_exec($ch);
        }

        foreach ($messages as $key => $message) {
            if($key == count($messages) - 1 && $sender_actions){
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode((object)$typing_off, JSON_FORCE_OBJECT));
                $result = curl_exec($ch);
            }

            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode((object)$message, JSON_FORCE_OBJECT));
            $result = curl_exec($ch);

            // $this->db->query("delete from messenger_chatbot_replies where page_id in ('11111', '22222')");
            // $this->db->query("insert into messenger_chatbot_replies set attributes = '". json_encode((object)$message, JSON_FORCE_OBJECT) . "', page_id = '11111', name = 'test', type = 'test', applied_on = 'test'");
            // $this->db->query("insert into messenger_chatbot_replies set attributes = '". $result . "', page_id = '22222', name = 'test', type = 'test', applied_on = 'test'");
        }

        return $result;
    }

    private function track_event($event, $attrs = []){
        $this->load->model('setting/mixpanel');
        $this->model_setting_mixpanel->trackEvent($event,$attrs);

        $this->load->model('setting/amplitude');
        $this->model_setting_amplitude->trackEvent($event,$attrs);

        $this->load->model('module/messenger_chatbot/messenger_chatbot');
        $this->model_module_messenger_chatbot_messenger_chatbot->track_event($event, $attrs);
    }

}
