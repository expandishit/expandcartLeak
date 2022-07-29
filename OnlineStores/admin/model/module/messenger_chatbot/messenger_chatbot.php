<?php

/**
 * Class ModelModuleMessengerChatbot
 */
class ModelModuleMessengerChatbotMessengerChatbot extends Model
{

    /**
     * Application settings key.
     *
     * @var string
     */
    protected $settingsKey = 'messenger_chatbot';

    /**
     * Install the Application
     */
    public function install()
    {
        if (!\Extension::isInstalled('messenger_chatbot')) {
            $this->install_messenger_chatbot();
        }
    }


    /**
     * install facebook catalog application
     */
    public function install_messenger_chatbot()
    {
        $pages = "CREATE TABLE IF NOT EXISTS messenger_chatbot_pages (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `page_id` BIGINT(20) NOT NULL,
            `store_code` VARCHAR(191) NOT NULL,
            `user_id` BIGINT(20) NOT NULL,
            `reply_id` INT(11) UNSIGNED DEFAULT NULL,
            `access_token` TEXT NOT NULL,
            `is_default` TINYINT(1) DEFAULT 0,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";

        $posts = "CREATE TABLE IF NOT EXISTS messenger_chatbot_posts (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `post_id` VARCHAR(255) NOT NULL,
            `page_id` INT(11) NOT NULL,
            `reply_id` INT(11) UNSIGNED DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";

        $replies = "CREATE TABLE IF NOT EXISTS messenger_chatbot_replies (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `page_id` INT(11) NOT NULL,
            `name` VARCHAR(255) NOT NULL,
            `type` VARCHAR(255) NOT NULL,
            `applied_on` VARCHAR(191) NOT NULL,
            `status` TINYINT(1) DEFAULT 1,
            `attributes` BLOB NOT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";

        $audience = "CREATE TABLE IF NOT EXISTS messenger_chatbot_audience (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `psid` BIGINT(20) NOT NULL,
            `name` VARCHAR(255) DEFAULT NULL,
            `page_id` BIGINT(20) NOT NULL,
            `last_interacted_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";

        $this->db->query($pages);
        $this->db->query($posts);
        $this->db->query($replies);
        $this->db->query($audience);
        $this->db->query("ALTER TABLE `" . DB_PREFIX . "order` ADD `psid` BIGINT(20) NULL AFTER `shipping_trackId`;");
        
        $this->db->query("INSERT INTO " . DB_PREFIX . "extension SET `type` = 'module', `code` = 'messenger_chatbot'");

    }

    /**
     * uninstall the Application
     */
    public function uninstall()
    {
        $this->logout();
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "messenger_chatbot_pages`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "messenger_chatbot_posts`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "messenger_chatbot_replies`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "messenger_chatbot_audience`");
    }

    public function logout()
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE `group` = '" . $this->db->escape($this->settingsKey) . "'");
        $this->ecusersdb->query("DELETE FROM " . DB_PREFIX . "messenger_chatbot_pages WHERE `store_code` = '" . STORECODE . "'");
        $this->db->query("UPDATE " . DB_PREFIX . "messenger_chatbot_pages SET is_default = 0 ");
    }

    /**
     * Gets the application settings.
     *
     * @return array
     */
    public function getSettings()
    {
        return $this->config->get($this->settingsKey);
    }

    /**
     * Check if the Application is active or Not
     * @return bool
     */
    public function isActive()
    {
        $settings = $this->getSettings();

        if (isset($settings) && $settings['status'] == 1) {
            return true;
        }

        return false;
    }

    /**
     * Gets the pages for current user
     *
     * @return array
     */
    public function get_pages($user_id)
    {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "messenger_chatbot_pages` WHERE `user_id` = '$user_id'");
        return $query->rows;
    }

    /**
     * Gets the pages for current user
     *
     * @return array
     */
    public function get_page($id)
    {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "messenger_chatbot_pages` WHERE `page_id` = '$id'");
        return $query->row;
    }

    /**
     * Gets the connected page for current store code
     *
     * @return array
     */
    public function get_connected_pages($list = null)
    {
        $sql = "SELECT `page_id` FROM `" . DB_PREFIX . "messenger_chatbot_pages` WHERE `store_code` = '" . STORECODE ."'";
        if(is_array($list)){
            $sql .= " AND page_id in ('" . implode($list, "','") ."')";
        }
        $query = $this->ecusersdb->query($sql);
        return $query->rows;
    }

    /**
     * Gets the connected page for current store code
     *
     * @return array
     */
    public function get_default_page($user_id)
    {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "messenger_chatbot_pages` WHERE `user_id` = '$user_id' and is_default = 1 limit 1");

        if($query->num_rows){
            return $query->row;
        }
        return false;
    }

    /**
     * updates page in local db
     *
     * @param integer $page_id
     * @param array $data
     *
     */
    public function update_page($page_id, $data)
    {
        $query = $columns = [];
        $query[] = 'UPDATE `' . DB_PREFIX . 'messenger_chatbot_pages` SET';
        foreach ($data as $column => $value) {
            if (is_string($value)) {
                $columns[] = sprintf('%s = "%s"', $column, addslashes($value));
            } else if (is_int($value)) {
                $columns[] = sprintf('%s = %d', $column, $value);
            }
        }

        if (count($columns) < 1) {
            return false;
        }

        $query[] = implode(', ', $columns);

        $query[] = "WHERE page_id = $page_id";

        $this->db->query(implode(' ', $query));
    }

    /**
     * Insert into messenger_chatbot_pages table
     *
     * @param array $data
     *
     */
    public function insert_page(array $data)
    {
        $query = $columns = $values = [];
        $query[] = 'INSERT INTO ' . DB_PREFIX . 'messenger_chatbot_pages';
        foreach ($data as $column => $value) {
            $columns[] = $column;
            $values[] = $value;
        }
        $query[] = '(`%s`)';
        $query[] = 'VALUES';
        $query[] = "('%s')";

        $this->db->query(vsprintf(implode(' ', $query), [
            implode('`,`', $columns),
            implode("', '", $values)
        ]));
    }

    public function able_to_connect($page_id){
        $query = $this->ecusersdb->query("SELECT * FROM `" . DB_PREFIX . "messenger_chatbot_pages` WHERE `page_id` = '$page_id' limit 1");
        $page = $query->row;
        if($page){
            if($page['store_code'] == STORECODE){
                return [
                    'status' => 2
                ];
            }
            else{
                return [
                    'status' => 3
                ];
            }
        }
        return [
            'status' => 1
        ];
    }

    public function connect_page($page_id, $set_default = true){
        $query = $this->ecusersdb->query("INSERT INTO `" . DB_PREFIX . "messenger_chatbot_pages` (page_id, store_code) VALUES ('$page_id', '" . STORECODE ."')");
        if($set_default)
            $query = $this->db->query("UPDATE `" . DB_PREFIX . "messenger_chatbot_pages` SET is_default = 0");
        $query = $this->db->query("UPDATE `" . DB_PREFIX . "messenger_chatbot_pages` SET is_default = " . ($set_default ? '1' : '0') . " WHERE page_id = '$page_id'");
    }

    public function disconnect_page($page_id, $user_id){
        $query = $this->db->query("UPDATE `" . DB_PREFIX . "messenger_chatbot_pages` set `is_default` = 0 WHERE `user_id` = '$user_id' and `page_id` = '$page_id'");
        $query = $this->ecusersdb->query("DELETE FROM `" . DB_PREFIX . "messenger_chatbot_pages` WHERE `store_code` = '" . STORECODE ."' and `page_id` = '$page_id'");
    }

    public function set_default($page_id, $user_id){
        $query = $this->db->query("UPDATE `" . DB_PREFIX . "messenger_chatbot_pages` SET `is_default` = 0 WHERE `user_id` = '$user_id'");
        $query = $this->db->query("UPDATE `" . DB_PREFIX . "messenger_chatbot_pages` SET `is_default` = 1 WHERE `page_id` = '$page_id'");
    }

    public function clear_default($user_id){
        $query = $this->db->query("UPDATE `" . DB_PREFIX . "messenger_chatbot_pages` SET `is_default` = 0 WHERE `user_id` = '$user_id'");
    }

    public function check_post($post_id){
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "messenger_chatbot_posts` WHERE `post_id` = '$post_id' limit 1");
        if($query->num_rows > 0){
            return $query->row;
        }
        return false;
    }

    public function check_page($page_id){
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "messenger_chatbot_pages` WHERE `page_id` = '$page_id' limit 1");
        if($query->num_rows > 0){
            return $query->row;
        }
        return false;
    }

    public function track_event($event, $attrs){
        $query = $this->ecusersdb->query("INSERT INTO `" . DB_PREFIX . "messenger_chatbot_tracking` (event_name, store_code, attributes) VALUES ('$event', '" . STORECODE ."', '" . $this->db->escape(serialize($attrs)) . "')");
    }
}