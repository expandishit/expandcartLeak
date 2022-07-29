<?php

class MessengerChatbotMigration {

	protected $db;
	protected $config;

    public function __construct($db, $config)
    {
        $this->db = $db;
        $this->config   = $config;
    }

	public function publish_attributes(){

		if ($this->config->get('platform_version') >= 1.2) {
            return false;
        }

        if (! $this->config->get('platform_version') || $this->config->get('platform_version') < 1.2) {
	        if($this->db->check(['messenger_chatbot_replies_description'], 'table') ){
	        	if(!$this->db->check(['messenger_chatbot_replies' => ['attributes']], 'column') ){
		            $query = $this->db->query("ALTER TABLE `" . DB_PREFIX . "messenger_chatbot_replies` ADD `attributes` BLOB  NOT NULL AFTER `status`");
		        }

	            $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "messenger_chatbot_replies` WHERE `attributes` = ''");
	            foreach ($query->rows as $reply) {
	                $query = $this->db->query("SELECT attributes FROM `" . DB_PREFIX . "messenger_chatbot_replies_description` WHERE reply_id = " . $reply['id'] . " AND lang_code = 'en' LIMIT 1");
	                $formdata_en = unserialize($query->row['attributes'])['formdata'];
	                $query = $this->db->query("SELECT attributes FROM `" . DB_PREFIX . "messenger_chatbot_replies_description` WHERE reply_id = " . $reply['id'] . " AND lang_code = 'ar' LIMIT 1");
	                $formdata_ar = unserialize($query->row['attributes'])['formdata'];

	                $attributes = [];
	                switch ($reply['type']) {
	                    case 'store_link':
	                        $attributes = [
	                            'formdata' => [
	                                'en' => [
	                                    'store_link' => [
	                                        'link_type' => 'home',
	                                        'subtitle' => 'Start Shopping',
	                                        'buttons' => [
	                                            [
	                                                'link_type' => 'home',
	                                                'title' => 'Start Shopping'
	                                            ]
	                                        ]
	                                    ]
	                                ],
	                                'ar' => [
	                                    'store_link' => [
	                                        'link_type' => 'home',
	                                        'subtitle' => 'تسوق الان',
	                                        'buttons' => [
	                                            [
	                                                'link_type' => 'home',
	                                                'title' => 'تسوق الان'
	                                            ]
	                                        ]
	                                    ]
	                                ]
	                            ]
	                        ];
	                        break;

	                    case 'free_text':
	                        $attributes = [
	                            'formdata' => [
	                                'en' => [
	                                    'free_text' => [
	                                        'body' => $formdata_en['free_text']['subtitle']
	                                    ]
	                                ],
	                                'ar' => [
	                                    'free_text' => [
	                                        'body' => $formdata_ar['free_text']['subtitle']
	                                    ]
	                                ]
	                            ]
	                        ];
	                        break;

	                    case 'media_template':
	                        $attributes = [
	                            'formdata' => [
	                                'en' => [
	                                    'media_template' => [
	                                        'buttons' => isset($formdata_en['media_template']['buttons']) ? $formdata_en['media_template']['buttons'] : []
	                                    ]
	                                ],
	                                'ar' => [
	                                    'media_template' => [
	                                        'buttons' => isset($formdata_ar['media_template']['buttons']) ? $formdata_ar['media_template']['buttons'] : []
	                                    ]
	                                ],
	                                'media_template' => [
	                                    'media_type' => $formdata_en['media_template']['media_type'],
	                                    'attachment_id' => $formdata_en['media_template']['attachment_id']
	                                ]
	                            ]
	                        ];
	                        break;

	                    case 'direct_product':
	                        $attributes = [
	                            'formdata' => [
	                                'direct_product' => [
	                                    'product' => $formdata_en['direct_product']['product']
	                                ]
	                            ]
	                        ];
	                        break;

	                    case 'specific_category':
	                        $attributes = [
	                            'formdata' => [
	                                'specific_category' => [
	                                    'category' => $formdata_en['specific_category']['category'],
	                                    'products' => $formdata_en['specific_category']['products']
	                                ]
	                            ]
	                        ];
	                        break;

	                    case 'specific_manufacturer':
	                        $attributes = [
	                            'formdata' => [
	                                'specific_manufacturer' => [
	                                    'manufacturer' => $formdata_en['specific_manufacturer']['manufacturer'],
	                                    'products' => $formdata_en['specific_manufacturer']['products']
	                                ]
	                            ]
	                        ];
	                        break;
	                    
	                    default:
	                        break;
	                }

	                $this->db->query("UPDATE `" . DB_PREFIX . "messenger_chatbot_replies` SET attributes = '" . serialize($attributes) . "' WHERE id = '" . (int)$reply['id'] . "'");
	            }

	            $this->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "`messenger_chatbot_replies_description`");
	        }
	        
	        if($this->config->get('platform_version')) {
                $this->db->query("UPDATE " . DB_PREFIX . "setting SET `value` = '1.2' WHERE `key` = 'platform_version'");
            } else { 
              	$this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = 0, `group` = 'platform', `key` = 'platform_version', `value` = '1.2', serialized = '0'");
            }
	    }
    }
}