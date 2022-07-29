<?php

class ModelModuleMessengerChatbotPrivateReply extends Model
{

    private $languages = ['en', 'ar'];

    private function check_attributes_column(){
        if(!$this->db->check(['messenger_chatbot_replies' => ['attributes']], 'column') ){
            $query = $this->db->query("ALTER TABLE `" . DB_PREFIX . "messenger_chatbot_replies` ADD `attributes` BLOB  NOT NULL AFTER `status`");
        }
    }

    public function addPrivateReply($data)
    {
        $this->check_attributes_column();
        $log = new Log("PrivateReply.log");

        $this->load->model('setting/setting');
        $this->load->model('module/messenger_chatbot/messenger_chatbot');

        $mcb_settings = $this->model_setting_setting->getSetting('messenger_chatbot');
        $default_page_id = $this->model_module_messenger_chatbot_messenger_chatbot->get_default_page($mcb_settings['user_id'])['id'];

        $this->db->query(
            "INSERT INTO " . DB_PREFIX . "messenger_chatbot_replies SET
            page_id = " . $default_page_id . ",
            name = '" . $this->db->escape($data['name']) . "',
            type = '" . $this->db->escape($data['type']) . "',
            applied_on = '" . $this->db->escape($data['applied_on']) . "',
            status = " . (int)$data['status'] . ",
            attributes = '" . serialize(['formdata' => $data]) ."'"
        );

        $reply_id = $this->db->getLastId();

        if ($data['applied_on'] == 'page') {
            $this->load->model('setting/setting');
            $this->load->model('module/messenger_chatbot/messenger_chatbot');
            $mcb_settings = $this->model_setting_setting->getSetting('messenger_chatbot');
            $user_id = $mcb_settings['user_id'];
            $default_page = $this->model_module_messenger_chatbot_messenger_chatbot->get_default_page($user_id)['id'];
            $this->db->query("UPDATE " . DB_PREFIX . "messenger_chatbot_pages SET reply_id = '" . (int)$reply_id . "' WHERE id = $default_page");
            $this->db->query("UPDATE " . DB_PREFIX . "messenger_chatbot_replies SET status = 0 WHERE id <> $reply_id AND applied_on = 'page' AND page_id = $default_page");
        }
        elseif($data['applied_on'] == 'posts') {
            foreach ($data['posts'] as $post_id) {
                $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "messenger_chatbot_posts WHERE post_id = '" . $post_id . "'");
                if($query->num_rows){
                    $this->db->query("UPDATE " . DB_PREFIX . "messenger_chatbot_posts SET reply_id = '" . (int)$reply_id . "' WHERE post_id = '" . $post_id . "'");
                }else{
                    $this->db->query("INSERT INTO " . DB_PREFIX . "messenger_chatbot_posts SET post_id = '" . $post_id . "', reply_id = '" . (int)$reply_id . "'");
                }
                $this->db->query("UPDATE " . DB_PREFIX . "messenger_chatbot_replies mcr SET status = 0 WHERE applied_on = 'posts' AND (SELECT COUNT(*) FROM " . DB_PREFIX . "messenger_chatbot_posts mcp WHERE mcp.reply_id = mcr.id) = 0");
                $this->db->query("UPDATE " . DB_PREFIX . "messenger_chatbot_pages SET reply_id = NULL WHERE reply_id = $reply_id");
            }
        }

        return $reply_id;
    }

    public function editPrivateReply($id, $data)
    {
        $this->check_attributes_column();
        $this->db->query(
            "UPDATE " . DB_PREFIX . "messenger_chatbot_replies SET
            name = '" . $this->db->escape($data['name']) . "',
            type = '" . $this->db->escape($data['type']) . "',
            applied_on = '" . $this->db->escape($data['applied_on']) . "',
            updated_at = CURRENT_TIMESTAMP,
            status = '" . (int)$data['status'] . "',
            attributes = '" . serialize(['formdata' => $data]) ."'
            WHERE id = '" . (int)$id . "'" 
        );

        if ($data['applied_on'] == 'page') {
            $this->load->model('setting/setting');
            $this->load->model('module/messenger_chatbot/messenger_chatbot');
            $mcb_settings = $this->model_setting_setting->getSetting('messenger_chatbot');
            $user_id = $mcb_settings['user_id'];
            $default_page = $this->model_module_messenger_chatbot_messenger_chatbot->get_default_page($user_id)['id'];
            $this->db->query("UPDATE " . DB_PREFIX . "messenger_chatbot_pages SET reply_id = null WHERE reply_id = $id");
            $this->db->query("UPDATE " . DB_PREFIX . "messenger_chatbot_pages SET reply_id = '" . $id . "' WHERE id = $default_page");
            $this->db->query("DELETE FROM ". DB_PREFIX . "messenger_chatbot_posts WHERE reply_id = $id");
            $this->db->query("UPDATE " . DB_PREFIX . "messenger_chatbot_replies SET status = 0 WHERE id <> $id AND applied_on = 'page' AND page_id = $default_page");
        }
        elseif($data['applied_on'] == 'posts') {
            $this->db->query("DELETE FROM ". DB_PREFIX . "messenger_chatbot_posts WHERE reply_id = $id");
            $this->db->query("UPDATE " . DB_PREFIX . "messenger_chatbot_pages SET reply_id = null WHERE reply_id = $id");
            foreach ($data['posts'] as $post_id) {
                $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "messenger_chatbot_posts WHERE post_id = '" . $post_id . "'");
                if($query->num_rows){
                    $this->db->query("UPDATE " . DB_PREFIX . "messenger_chatbot_posts SET reply_id = '" . $id . "' WHERE post_id = '" . $post_id . "'");
                }else{
                    $this->db->query("INSERT INTO " . DB_PREFIX . "messenger_chatbot_posts SET post_id = '" . $post_id . "', reply_id = '" . $id . "'");
                }
            }
            $this->db->query("UPDATE " . DB_PREFIX . "messenger_chatbot_replies mcr SET status = 0 WHERE applied_on = 'posts' AND (SELECT COUNT(*) FROM " . DB_PREFIX . "messenger_chatbot_posts mcp WHERE mcp.reply_id = mcr.id) = 0");
        }
    }

    public function generateReply($data, $lang)
    {
        $this->load->model('setting/domainsetting');
        $domains = $this->model_setting_domainsetting->getDomains();
        $https_catalog_domain = HTTPS_CATALOG;
        if (is_array($domains) && count($domains)) {
            $https_catalog_domain = strtolower($domains[0]['DOMAINNAME']) . '/';
        }
        $url_object = new Url($https_catalog_domain,$https_catalog_domain);

        $this->language->load('messenger_chatbot/private_reply');

        $this->load->model('catalog/product');
        $this->load->model('catalog/category');
        $this->load->model('catalog/information');
        $this->load->model('localisation/language');
        $this->load->model('tool/image');

        $lang_id = $this->model_localisation_language->getLanguageByCode($lang)['language_id'];
        $payload = [];

        switch ($data['type']) {
            case 'store_link':
                switch ($data[$lang]['store_link']['link_type']) {
                    case 'home':
                        $payload = [
                            'template_type' => 'generic',
                            'elements' => [
                                [
                                    'title' => $this->config->get('config_name')[$lang],
                                    'image_url' => $this->model_tool_image->resize($this->config->get('config_logo'), 200, 200),
                                    'subtitle' => $data[$lang]['store_link']['subtitle'],
                                    'default_action' => [
                                        'type' => 'web_url',
                                        'url' => $url_object->frontUrl('common/home', '', true),
                                        'messenger_extensions' => false,
                                        'webview_height_ratio' => 'FULL'
                                    ]     
                                ]
                            ]
                        ];
                        $buttons = $this->getButtonsList($data[$lang]['store_link']);
                        foreach ($payload['elements'] as $key => $value) {
                            if(count($buttons)){
                                $payload['elements'][$key]['buttons'] = $buttons;
                            }
                        }
                        break;
                    case 'product':
                        $product = $this->model_catalog_product->getProductByLanguageId($data[$lang]['store_link']['product'], $lang_id);
                        $payload = [
                            'template_type' => 'generic',
                            'elements' => [
                                [
                                    'title' => $product['name'],
                                    'image_url' => $this->model_tool_image->resize($product['image'], 200, 200),
                                    'subtitle' => $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'))),
                                    'default_action' => [
                                        'type' => 'web_url',
                                        'url' => $url_object->frontUrl('product/product', 'product_id='.$product['product_id'], true, false),
                                        'messenger_extensions' => false,
                                        'webview_height_ratio' => 'FULL'
                                    ]     
                                ]
                            ]
                        ];
                        $buttons = $this->getButtonsList($data[$lang]['store_link']);
                        foreach ($payload['elements'] as $key => $value) {
                            if(count($buttons)){
                                $payload['elements'][$key]['buttons'] = $buttons;
                            }
                        }
                        break;
                    case 'category':
                        $category = $this->model_catalog_category->getCategoryByLangugaeId($data[$lang]['store_link']['category'], $lang_id);
                        $payload = [
                            'template_type' => 'generic',
                            'elements' => [
                                [
                                    'title' => $category['name'],
                                    'image_url' => $this->model_tool_image->resize($category['image'], 200, 200),
                                    'subtitle' => $category['description'],
                                    'default_action' => [
                                        'type' => 'web_url',
                                        'url' => $url_object->frontUrl('product/category', 'path='.$category['category_id'], true, false),
                                        'messenger_extensions' => false,
                                        'webview_height_ratio' => 'FULL'
                                    ]     
                                ]
                            ]
                        ];
                        $buttons = $this->getButtonsList($data[$lang]['store_link']);
                        foreach ($payload['elements'] as $key => $value) {
                            if(count($buttons)){
                                $payload['elements'][$key]['buttons'] = $buttons;
                            }
                        }
                        break;
                    case 'web_page':
                        $information = $this->model_catalog_information->getInformation($data[$lang]['store_link']['web_page']);
                        $information_description = $this->model_catalog_information->getInformationDescriptions($data[$lang]['store_link']['web_page'])[$lang_id];
                        $payload = [
                            'template_type' => 'generic',
                            'elements' => [
                                [
                                    'title' => $information_description['title'],
                                    'image_url' => $this->config->get('config_logo') ? \Filesystem::getUrl('image/' . $this->config->get('config_logo')) : '',
                                    'subtitle' => $information_description['description'],
                                    'default_action' => [
                                        'type' => 'web_url',
                                        'url' => $url_object->frontUrl('information/information', 'information_id='.$information['information_id'], true, false),
                                        'messenger_extensions' => false,
                                        'webview_height_ratio' => 'FULL'
                                    ]     
                                ]
                            ]
                        ];
                        $buttons = $this->getButtonsList($data[$lang]['store_link']);
                        foreach ($payload['elements'] as $key => $value) {
                            if(count($buttons)){
                                $payload['elements'][$key]['buttons'] = $buttons;
                            }
                        }
                        break;
                    case 'other_page':
                        $link = $this->db->query('SELECT * FROM link LEFT JOIN link_description ON link.link_id = link_description.link_id WHERE link.link_id = ' . $data[$lang]['store_link']['other_page'])->row . ' AND link_description.language_id = ' . $lang_id;
                        $payload = [
                            'template_type' => 'generic',
                            'elements' => [
                                [
                                    'title' => $link['title'],
                                    'image_url' => $this->config->get('config_logo') ? \Filesystem::getUrl('image/' . $this->config->get('config_logo')) : '',
                                    'default_action' => [
                                        'type' => 'web_url',
                                        'url' => $url_object->frontUrl($link['route'], '', true, false),
                                        'messenger_extensions' => false,
                                        'webview_height_ratio' => 'FULL'
                                    ]     
                                ]
                            ]
                        ];
                        $buttons = $this->getButtonsList($data[$lang]['store_link']);
                        foreach ($payload['elements'] as $key => $value) {
                            if(count($buttons)){
                                $payload['elements'][$key]['buttons'] = $buttons;
                            }
                        }
                        break;
                    default:
                        break;
                }
                break;

            case 'free_text':
                return [
                    'text' => $data[$lang]['free_text']['body']
                ];

            case 'media_template':
                $payload = [
                    'template_type' => 'media',
                    'elements' => [
                        [
                            'media_type' => $data['media_template']['media_type'],
                            'attachment_id' => $data['media_template']['attachment_id']
                        ]
                    ]
                ];
                $buttons = $this->getButtonsList($data[$lang]['media_template']);
                foreach ($payload['elements'] as $key => $value) {
                    if(count($buttons)){
                        $payload['elements'][$key]['buttons'] = $buttons;
                    }
                }
                break;

            case 'direct_product':
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
                $product = $this->model_catalog_product->getProductByLanguageId($data['direct_product']['product'], $lang_id);
                $payload = [
                    'template_type' => 'generic',
                    'elements' => [
                        [
                            'title' => $product['name'],
                            'image_url' => $this->model_tool_image->resize($product['image'], 200, 200),
                            'subtitle' => $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'))),
                            'default_action' => [
                                'type' => 'web_url',
                                'url' => $url_object->frontUrl('product/product', 'product_id='.$product['product_id'], true, false),
                                'messenger_extensions' => false,
                                'webview_height_ratio' => 'FULL'
                            ],
                            'buttons' => [
                                [
                                    'type' => 'web_url',
                                    'url' => $url_object->frontUrl('product/product', 'product_id='.$product['product_id'], true, false),
                                    'title' => $button_titles[$lang]['view_detais'],
                                    'webview_height_ratio' => 'full',
                                    'messenger_extensions' => false
                                ],
                                [
                                    'type' => 'postback',
                                    'title' => $button_titles[$lang]['proceed_to_pay'],
                                    'payload' => '{"type":"direct_product","value":"'.$product['product_id'].'"}',
                                ]
                            ]
                        ]
                    ]
                ];
                break;

            case 'specific_category':
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
                $elements = [];
                foreach($data['specific_category']['products'] as $product_id){
                    $product = $this->model_catalog_product->getProductByLanguageId($product_id, $lang_id);
                    $elements[] = [
                        'title' => $product['name'],
                        'image_url' => $this->model_tool_image->resize($product['image'], 200, 200),
                        'subtitle' => $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'))),
                        'default_action' => [
                            'type' => 'web_url',
                            'url' => $url_object->frontUrl('product/product', 'product_id='.$product['product_id'], true, false),
                            'messenger_extensions' => false,
                            'webview_height_ratio' => 'FULL'
                        ],
                        'buttons' => [
                            [
                                'type' => 'web_url',
                                'url' => $url_object->frontUrl('product/product', 'product_id='.$product['product_id'], true, false),
                                'title' => $button_titles[$lang]['view_detais']
                            ],
                            [
                                'type' => 'postback',
                                'title' => $button_titles[$lang]['proceed_to_pay'],
                                'payload' => '{"type":"direct_product","value":"'.$product['product_id'].'"}',
                            ]
                        ]
                    ];
                }

                $payload = [
                    'template_type' => 'generic',
                    'elements' => array_slice($elements, 0, 10)
                ];
                break;

            case 'specific_manufacturer':
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
                $elements = [];
                foreach($data['specific_manufacturer']['products'] as $product_id){
                    $product = $this->model_catalog_product->getProductByLanguageId($product_id, $lang_id);
                    $elements[] = [
                        'title' => $product['name'],
                        'image_url' => $this->model_tool_image->resize($product['image'], 200, 200),
                        'subtitle' => $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'))),
                        'default_action' => [
                            'type' => 'web_url',
                            'url' => $url_object->frontUrl('product/product', 'product_id='.$product['product_id'], true, false),
                            'messenger_extensions' => false,
                            'webview_height_ratio' => 'FULL'
                        ],
                        'buttons' => [
                            [
                                'type' => 'web_url',
                                'url' => $url_object->frontUrl('product/product', 'product_id='.$product['product_id'], true, false),
                                'title' => $button_titles[$lang]['view_detais']
                            ],
                            [
                                'type' => 'postback',
                                'title' => $button_titles[$lang]['proceed_to_pay'],
                                'payload' => '{"type":"direct_product","value":"'.$product['product_id'].'"}',
                            ]
                        ]
                    ];
                }

                $payload = [
                    'template_type' => 'generic',
                    'elements' => array_slice($elements, 0, 10)
                ];
                break;
            
            default:
                break;
        }

        $message = [
            'attachment' => [
                'type' => 'template',
                'payload' => $payload
            ]
        ];

        return $message;
    }

    private function getButtonsList($data)
    {
        $this->load->model('setting/domainsetting');
        $domains = $this->model_setting_domainsetting->getDomains();
        $https_catalog_domain = HTTPS_CATALOG;
        if (is_array($domains) && count($domains)) {
            $https_catalog_domain = strtolower($domains[0]['DOMAINNAME']) . '/';
        }
        $url_object = new Url($https_catalog_domain,$https_catalog_domain);

        $buttons = [];
        if(isset($data['buttons']) && count($data['buttons'])){
            foreach ($data['buttons'] as $button) {
                $url = '';
                switch ($button['link_type']) {
                    case 'home':
                        $url = $url_object->frontUrl('common/home', '', true);
                        break;
                    case 'product':
                        $url = $url_object->frontUrl('product/product', 'product_id='.$button['product'], true, false);
                        break;
                    case 'category':
                        $url = $url_object->frontUrl('product/category', 'path='.$button['category'], true, false);
                        break;
                    case 'web_page':
                        $url = $url_object->frontUrl('information/information', 'information_id=' . $button['web_page'], true, false);
                        break;
                    case 'other_page':
                        $route = $this->db->query('SELECT route FROM ' . DB_PREFIX . 'link WHERE link_id = ' . $button['other_page'])->row['route'];
                        $url = $url_object->frontUrl($route, '', true, false);
                        break;
                    default:
                        break;
                }

                $buttons[] = [
                    'type' => 'web_url',
                    'url' => $url,
                    'title' => $button['title']
                ];
            }
        }

        return $buttons;
    }

    public function deletePrivateReply($id)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "messenger_chatbot_pages SET reply_id = NULL WHERE reply_id = '" . (int)$id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "messenger_chatbot_posts WHERE reply_id = '" . (int)$id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "messenger_chatbot_replies WHERE id = '" . (int)$id . "'");
    }

    public function getPrivateReply($id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "messenger_chatbot_replies WHERE id = " . (int)$id);
        if($query->num_rows == 0) return false;
        return $query->row;
    }

    public function getPrivateReplyFormData($reply_id)
    {
        $this->check_attributes_column();
        $reply_description_data = array();

        $query = $this->db->query(
            "SELECT * FROM " . DB_PREFIX . "messenger_chatbot_replies
            WHERE id = '" . (int)$reply_id . "'"
        );

        $result = $query->row;

        return unserialize($result['attributes'])['formdata'];
    }

    public function getPrivateReplyPosts($reply_id)
    {
        $query = $this->db->query(
            "SELECT * FROM " . DB_PREFIX . "messenger_chatbot_posts
            WHERE reply_id = '" . (int)$reply_id . "'"
        );

        return array_column($query->rows, 'post_id');
    }

    public function getPrivateReplyByName($name)
    {
        $this->load->model('setting/setting');
        $this->load->model('module/messenger_chatbot/messenger_chatbot');

        $mcb_settings = $this->model_setting_setting->getSetting('messenger_chatbot');
        $default_page_id = $this->model_module_messenger_chatbot_messenger_chatbot->get_default_page($mcb_settings['user_id'])['id'];
        
        $queryString = 'SELECT DISTINCT * FROM ' . DB_PREFIX . 'messenger_chatbot_replies WHERE name = "' . $this->db->escape($name) . '" AND page_id = ' . $default_page_id;

        $query = $this->db->query($queryString);

        return $query->row;
    }

    public function getPrivateReplies($data = array())
    {
        $this->check_attributes_column();
        $queryString = [];

        $fields = 'id, name, type, created_at, status, attributes';

        $queryString[] = "SELECT {$fields} FROM " . DB_PREFIX . "messenger_chatbot_replies";

        $sort_data = array(
            'name',
            'type',
            'created_at',
            'status'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $queryString[] = " ORDER BY " . $data['sort'];
        } else {
            $queryString[] = " ORDER BY name";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $queryString[] = " DESC";
        } else {
            $queryString[] = " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $queryString[] = " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query(implode(' ', $queryString));

        return $query->rows;
    }

    public function getTotalPrivateReplies()
    {
        $this->load->model('setting/setting');
        $this->load->model('module/messenger_chatbot/messenger_chatbot');

        $mcb_settings = $this->model_setting_setting->getSetting('messenger_chatbot');
        $default_page_id = $this->model_module_messenger_chatbot_messenger_chatbot->get_default_page($mcb_settings['user_id'])['id'];
        
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "messenger_chatbot_replies where page_id = $default_page_id");

        return $query->row['total'];
    }

    public function dtHandler($data)
    {
        $this->load->model('setting/setting');
        $this->load->model('module/messenger_chatbot/messenger_chatbot');

        $mcb_settings = $this->model_setting_setting->getSetting('messenger_chatbot');
        $default_page_id = $this->model_module_messenger_chatbot_messenger_chatbot->get_default_page($mcb_settings['user_id'])['id'];

        $queryString = [];
        $fields = ['r.id as private_reply_id', 'r.name', 'r.type', 'r.created_at', 'r.status'];
        $fields = implode(', ', $fields);
        $queryString[] = "SELECT {$fields} FROM " . DB_PREFIX . "messenger_chatbot_replies r";

        $queryString[] = "WHERE page_id = $default_page_id";

        if (!empty($data['filter_name'])) {
            $queryString[] = "AND name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
        }

        $total = $this->db->query(str_replace($fields, 'COUNT(*) as dc', implode(' ', $queryString)))->row['dc'];

        $sort_data = array(
            'name',
            'type',
            'created_at',
            'status'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $queryString[] = " ORDER BY " . $data['sort'];
        } else {
            $queryString[] = " ORDER BY name";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $queryString[] = " DESC";
        } else {
            $queryString[] = " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $queryString[] = " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $queryData = $this->db->query(implode(' ', $queryString));

        $data = array(
            'data' => $queryData->rows,
            'total' => $total,
            'totalFiltered' => $queryData->num_rows
        );

        return $data;
    }

    function log($type, $contents , $fileName=false)
    {
        if (!$fileName || empty($fileName))
            $fileName='private_reply.log';

        $log = new Log($fileName);
        $log->write('[' . strtoupper($type) . '] ' . $contents);
    }

    public function get_other_pages(){
        $lang_id = $this->config->get('config_language_id') ? (int)$this->config->get('config_language_id') : 1;
        $sql ="SELECT l.link_id as id,l.name,title,route FROM `" . DB_PREFIX . "link` as l left join link_description as l_d on l.link_id = l_d.link_id where language_id = $lang_id AND l.name NOT IN ('home', 'product', 'category', 'web page')";

        $results = $this->db->query($sql);
        $data_source=array();
        foreach ($results->rows as $value) {
            $this->mainPages[]=$value['title'];
            $data_source[]=array(
                'id'    => $value['id'],
                'title' => $value['title']
            );
        }
        return $data_source;
    }

    public function getPostsByIds($ids, $reply_id){
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "messenger_chatbot_posts WHERE post_id in ('" . implode($ids, "','") ."') AND reply_id <> $reply_id");
        return $query->rows;
    }

    public function updateStatus($id, $status)
    {
        $this->db->query(
            "UPDATE " . DB_PREFIX . "messenger_chatbot_replies SET
            status = '" . (int)$data['status'] . "'
            WHERE id = '" . (int)$id . "'"
        );
    }
}
