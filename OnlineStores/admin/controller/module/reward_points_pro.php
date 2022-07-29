<?php
class ControllerModuleRewardPointsPro extends Controller {
    private $error = array();

	public function index() {

        $this->redirect($this->url->link('promotions/reward_points/catalogRuleList'));
        return;
    }

    public function install() {
        $show_table = "show tables";
        $show_table_query = $this->db->query($show_table);
        $table_rows = array();
        foreach($show_table_query->rows as $table){
            $table_rows[end($table)] = end($table);
        }

        $data = array(
            'rwp_enabled_module'        => $this->config->get('rwp_enabled_module'),
            'earn_point_sort_order'     => ($this->config->get('earn_point_sort_order')) ? $this->config->get('earn_point_sort_order') : 1,
            'redeem_point_sort_order'   => ($this->config->get('redeem_point_sort_order')) ? $this->config->get('redeem_point_sort_order') : 8,
            'earn_point_status'         => $this->config->get('earn_point_status'),
            'redeem_point_status'       => $this->config->get('redeem_point_status'),
            'currency_exchange_rate'    => ($this->config->get('currency_exchange_rate')) ? $this->config->get('currency_exchange_rate') : '100/1',
            'show_point_listing'        => $this->config->get('show_point_listing'),
            'show_point_detail'         => $this->config->get('show_point_detail'),
            'update_based_order_status' => ($this->config->get('update_based_order_status')) ? $this->config->get('update_based_order_status') : 5
        );

        foreach($this->$languages as $code => $language){
            $data['text_points_'.$code] = $this->config->get('text_points_'.$code);
        }

        $store_id = 0;

        if(!$this->config->get('earn_point_sort_order'))
            $this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE store_id = '" . (int)$store_id . "' AND `group` = 'earning'");

        if(!$this->config->get('redeem_point_sort_order'))
            $this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE store_id = '" . (int)$store_id . "' AND `group` = 'redeeming'");

        $this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE store_id = '" . (int)$store_id . "' AND `group` = 'reward_points'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE `key` = 'rwp_enabled_module' AND `group` = ''");

        $this->db->query("DELETE FROM " . DB_PREFIX . "extension WHERE type = 'total' AND `code` = 'earn_point'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "extension WHERE type = 'total' AND `code` = 'redeem_point'");

        $this->db->query("INSERT INTO " . DB_PREFIX . "extension SET `type` = 'total', `code` = 'earn_point'");
        $this->db->query("INSERT INTO " . DB_PREFIX . "extension SET `type` = 'total', `code` = 'redeem_point'");

        foreach ($data as $key => $value) {
            if($key == 'earn_point_sort_order' || $key == 'earn_point_status')
                $code = 'earning';
            elseif($key == 'redeem_point_sort_order' || $key == 'redeem_point_status')
                $code = 'redeeming';
            else
                $code = 'reward_points';

            if (!is_array($value)) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$store_id . "', `group` = 'reward_points', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape($value) . "'");
            }
            else {
                $this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$store_id . "', `group` = 'reward_points', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape(serialize($value)) . "', serialized = '1'");
            }
        }

        /** Create & Update tables */
        $table_catalog_rules = DB_PREFIX . "catalog_rules";
        if(!isset($table_rows[$table_catalog_rules])){
            $catalog_rule_sql = "CREATE TABLE IF NOT EXISTS $table_catalog_rules(
                    `rule_id` int(11) unsigned NOT NULL auto_increment,
                    `name` varchar(255) NOT NULL default '',
                    `description` text NOT NULL default '',
                    `conditions_serialized` mediumtext NOT NULL default '',
                    `store_view` varchar(255) NOT NULL default '0',
                    `customer_group_ids` varchar(255) NOT NULL default '',
                    `start_date` varchar(255) NOT NULL default '',
                    `end_date` varchar(255) NOT NULL default '',
                    `actions` int(2) NOT NULL default '0',
                    `reward_per_spent` int(11) NOT NULL default '0',
                    `reward_point` int(11) NOT NULL default '0',
                    `rule_position` int(11) NOT NULL default '0',
                    `stop_rules_processing` int(2) NOT NULL default '0',
                    `status` INT(2) NOT NULL default '0',
                    PRIMARY KEY (`rule_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
            try
            {
                if ($this->db->query($catalog_rule_sql)) {
                    $insert_sql = "INSERT INTO `$table_catalog_rules` (`rule_id`, `name`, `description`, `conditions_serialized`, `store_view`, `customer_group_ids`, `start_date`, `end_date`, `actions`, `reward_per_spent`, `reward_point`, `rule_position`, `stop_rules_processing`, `status`) VALUES
						   (1, 'Reward for all products', 'Reward for all products in', 'YToxOntzOjEwOiJjb25kaXRpb25zIjthOjE6e2k6MTthOjM6e3M6MTA6ImFnZ3JlZ2F0b3IiO3M6MzoiYWxsIjtzOjU6InZhbHVlIjtzOjE6IjEiO3M6OToibmV3X2NoaWxkIjtzOjA6IiI7fX19', '0', 'a:2:{i:0;s:2:\"99\";i:1;s:1:\"1\";}', '', '', 1, 0, 100, 0, 0, 0),
						   (2, 'For any products', 'For any products', 'YToxOntzOjEwOiJjb25kaXRpb25zIjthOjE6e2k6MTthOjM6e3M6MTA6ImFnZ3JlZ2F0b3IiO3M6MzoiYWxsIjtzOjU6InZhbHVlIjtzOjE6IjEiO3M6OToibmV3X2NoaWxkIjtzOjA6IiI7fX19', '0', 'a:2:{i:0;s:2:\"99\";i:1;s:1:\"1\";}', '', '', 2, 20, 5, 0, 0, 0);";
                    $this->db->query($insert_sql);
                }
            }catch(Exception $e){

            }
        }

        $table_shopping_cart_rules = DB_PREFIX . "shopping_cart_rules";
        if(!isset($table_rows[$table_shopping_cart_rules])){
            $shopping_cart_rule_sql = "CREATE TABLE IF NOT EXISTS $table_shopping_cart_rules(
                    `rule_id` int(11) unsigned NOT NULL auto_increment,
                    `name` varchar(255) NOT NULL default '',
                    `description` text NOT NULL default '',
                    `conditions_serialized` mediumtext NOT NULL default '',
                    `store_view` varchar(255) NOT NULL default '0',
                    `customer_group_ids` varchar(255) NOT NULL default '',
                    `start_date` varchar(255) NOT NULL default '',
                    `end_date` varchar(255) NOT NULL default '',
                    `actions` int(2) NOT NULL default '0',
                    `reward_per_spent` int(11) NOT NULL default '0',
                    `reward_point` int(11) NOT NULL default '0',
                    `rule_position` int(11) NOT NULL default '0',
                    `stop_rules_processing` int(2) NOT NULL default '0',
                    `status` INT(2) NOT NULL default '0',
                    PRIMARY KEY (`rule_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
            try
            {
                if ($this->db->query($shopping_cart_rule_sql)) {
                    $insert_sql = "INSERT INTO `$table_shopping_cart_rules` (`rule_id`, `name`, `description`, `conditions_serialized`, `store_view`, `customer_group_ids`, `start_date`, `end_date`, `actions`, `reward_per_spent`, `reward_point`, `rule_position`, `stop_rules_processing`, `status`) VALUES
				           (1, 'Whole Cart Rule', 'Buy $1000, get 300 points!', 'YToxOntzOjEwOiJjb25kaXRpb25zIjthOjI6e2k6MTthOjM6e3M6MTA6ImFnZ3JlZ2F0b3IiO3M6MzoiYWxsIjtzOjU6InZhbHVlIjtzOjE6IjEiO3M6OToibmV3X2NoaWxkIjtzOjA6IiI7fXM6NDoiMS0tMSI7YTo0OntzOjQ6InR5cGUiO3M6Mzc6InNhbGUvcmV3YXJkX3BvaW50cy9ydWxlfHN1YnRvdGFsLXRleHQiO3M6OToiYXR0cmlidXRlIjtzOjE2OiJhdHRyaWJ1dGVfc2V0X2lkIjtzOjg6Im9wZXJhdG9yIjtzOjU6IiZndDs9IjtzOjU6InZhbHVlIjtzOjM6IjUwMCI7fX19', '0', 'a:2:{i:0;s:2:\"99\";i:1;s:1:\"1\";}', '', '', 2, 0, 300, 0, 0, 0),
				           (2, 'Bulk Purchase amount of $2000+', 'Bulk Purchase amount of $2000+', 'YToxOntzOjEwOiJjb25kaXRpb25zIjthOjI6e2k6MTthOjM6e3M6MTA6ImFnZ3JlZ2F0b3IiO3M6MzoiYWxsIjtzOjU6InZhbHVlIjtzOjE6IjEiO3M6OToibmV3X2NoaWxkIjtzOjA6IiI7fXM6NDoiMS0tMSI7YTo1OntzOjQ6InR5cGUiO3M6Mzc6InNhbGUvcmV3YXJkX3BvaW50cy9ydWxlfHN1YnRvdGFsLXRleHQiO3M6NDoidGV4dCI7czo4OiJTdWJ0b3RhbCI7czo5OiJhdHRyaWJ1dGUiO3M6MTY6ImF0dHJpYnV0ZV9zZXRfaWQiO3M6ODoib3BlcmF0b3IiO3M6NToiJmd0Oz0iO3M6NToidmFsdWUiO3M6NDoiMjAwMCI7fX19', '0', 'a:2:{i:0;s:2:\"99\";i:1;s:1:\"1\";}', '', '', 2, 0, 2000, 0, 0, 0),
				           (3, 'Bulk Purchase amount of $3000+', 'Bulk Purchase amount of $3000+', 'YToxOntzOjEwOiJjb25kaXRpb25zIjthOjI6e2k6MTthOjM6e3M6MTA6ImFnZ3JlZ2F0b3IiO3M6MzoiYWxsIjtzOjU6InZhbHVlIjtzOjE6IjEiO3M6OToibmV3X2NoaWxkIjtzOjA6IiI7fXM6NDoiMS0tMSI7YTo1OntzOjQ6InR5cGUiO3M6Mzc6InNhbGUvcmV3YXJkX3BvaW50cy9ydWxlfHN1YnRvdGFsLXRleHQiO3M6NDoidGV4dCI7czo4OiJTdWJ0b3RhbCI7czo5OiJhdHRyaWJ1dGUiO3M6MTY6ImF0dHJpYnV0ZV9zZXRfaWQiO3M6ODoib3BlcmF0b3IiO3M6NToiJmd0Oz0iO3M6NToidmFsdWUiO3M6NDoiMzAwMCI7fX19', '0', 'a:2:{i:0;s:2:\"99\";i:1;s:1:\"1\";}', '', '', 2, 0, 3000, 0, 0, 0),
				           (4, 'Bulk purchase of 5 products', 'Bulk purchase of 5 products', 'YToxOntzOjEwOiJjb25kaXRpb25zIjthOjI6e2k6MTthOjM6e3M6MTA6ImFnZ3JlZ2F0b3IiO3M6MzoiYWxsIjtzOjU6InZhbHVlIjtzOjE6IjEiO3M6OToibmV3X2NoaWxkIjtzOjA6IiI7fXM6NDoiMS0tMSI7YTo1OntzOjQ6InR5cGUiO3M6Mzc6InNhbGUvcmV3YXJkX3BvaW50cy9ydWxlfHF1YW50aXR5LXRleHQiO3M6NDoidGV4dCI7czoyMDoiVG90YWwgaXRlbXMgcXVhbnRpdHkiO3M6OToiYXR0cmlidXRlIjtzOjE2OiJhdHRyaWJ1dGVfc2V0X2lkIjtzOjg6Im9wZXJhdG9yIjtzOjU6IiZndDs9IjtzOjU6InZhbHVlIjtzOjE6IjUiO319fQ==', '0', 'a:2:{i:0;s:2:\"99\";i:1;s:1:\"1\";}', '', '', 2, 0, 500, 0, 0, 0);";
                    $this->db->query($insert_sql);
                }
            }catch(Exception $e){

            }
        }

        $table_spending_rules = DB_PREFIX . "spending_rules";
        if(!isset($table_rows[$table_spending_rules])){
            $spending_rule_sql = "CREATE TABLE IF NOT EXISTS $table_spending_rules(
                `rule_id` int(11) unsigned NOT NULL auto_increment,
                `name` varchar(255) NOT NULL default '',
                `description` text NOT NULL default '',
                `conditions_serialized` mediumtext NOT NULL default '',
                `store_view` varchar(255) NOT NULL default '0',
                `customer_group_ids` varchar(255) NOT NULL default '',
                `start_date` varchar(255) NOT NULL default '',
                `end_date` varchar(255) NOT NULL default '',
                `actions` int(2) NOT NULL default '0',
                `reward_per_spent` int(11) NOT NULL default '0',
                `reward_point` int(11) NOT NULL default '0',
                `rule_position` int(11) NOT NULL default '0',
                `stop_rules_processing` int(2) NOT NULL default '0',
                `status` INT(2) NOT NULL default '0',
                PRIMARY KEY (`rule_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

            try
            {
                if ($this->db->query($spending_rule_sql)) {
                    $insert_sql = "INSERT INTO `$table_spending_rules` (`rule_id`, `name`, `description`, `conditions_serialized`, `store_view`, `customer_group_ids`, `start_date`, `end_date`, `actions`, `reward_per_spent`, `reward_point`, `rule_position`, `stop_rules_processing`, `status`) VALUES
						   (1, 'Spending Rule ', 'some particular products', 'YToxOntzOjEwOiJjb25kaXRpb25zIjthOjI6e2k6MTthOjM6e3M6MTA6ImFnZ3JlZ2F0b3IiO3M6MzoiYWxsIjtzOjU6InZhbHVlIjtzOjE6IjEiO3M6OToibmV3X2NoaWxkIjtzOjA6IiI7fXM6NDoiMS0tMSI7YTo1OntzOjQ6InR5cGUiO3M6Mzc6InNhbGUvcmV3YXJkX3BvaW50cy9ydWxlfHN1YnRvdGFsLXRleHQiO3M6NDoidGV4dCI7czo4OiJTdWJ0b3RhbCI7czo5OiJhdHRyaWJ1dGUiO3M6MTY6ImF0dHJpYnV0ZV9zZXRfaWQiO3M6ODoib3BlcmF0b3IiO3M6NToiJmd0Oz0iO3M6NToidmFsdWUiO3M6MzoiNTAwIjt9fX0=', '0', 'a:2:{i:0;s:2:\"99\";i:1;s:1:\"1\";}', '', '', 2, 0, 0, 0, 0, 0);";
                    $this->db->query($insert_sql);
                }
            }catch(Exception $e){

            }
        }

        $table_behavior_rules = DB_PREFIX . "behavior_rules";
        if(!isset($table_rows[$table_behavior_rules])){
            $behavior_rule_sql = "CREATE TABLE IF NOT EXISTS $table_behavior_rules(
                `rule_id` int(11) unsigned NOT NULL auto_increment,
                `name` varchar(255) NOT NULL default '',
                `store_view` varchar(255) NOT NULL default '0',
                `customer_group_ids` varchar(255) NOT NULL default '',
                `actions` int(2) NOT NULL default '0',
                `reward_point` int(11) NOT NULL default '0',
                `status` INT(2) NOT NULL default '0',
                PRIMARY KEY (`rule_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

            try
            {
                if ($this->db->query($behavior_rule_sql)) {
                    $insert_sql = "INSERT INTO `$table_behavior_rules` (`rule_id`, `name`, `store_view`, `customer_group_ids`, `actions`, `reward_point`, `status`) VALUES
						   (1, 'Signing Up', '0', 'a:1:{i:0;s:2:\"99\";}', 1, 100, 1),
						   (3, 'Sign-up Newsletter', '0', 'a:1:{i:0;s:2:\"99\";}', 5, 60, 1),
						   (4, 'Post a review for product', '0', 'a:1:{i:0;s:2:\"99\";}', 2, 15, 1);";
                    $this->db->query($insert_sql);
                }
            }catch(Exception $e){

            }
        }

        $table_product_to_reward = DB_PREFIX . "product_to_reward";
        if(!isset($table_rows[$table_product_to_reward])){
            $product_to_reward_sql = "CREATE TABLE IF NOT EXISTS $table_product_to_reward(
                        `product_id` int(11) NOT NULL,
                        `rule_id` int(11) NOT NULL,
                        `reward_point` int(11) NOT NULL)
                        ENGINE=InnoDB DEFAULT CHARSET=utf8;";

            try
            {
                if ($this->db->query($product_to_reward_sql)) {
                   // echo message_tbl("Created table <b>$table_product_to_reward</b> complete.");
                }
            }catch(Exception $e){
                //echo message_tbl("Issue when create table <b>$table_product_to_reward</b> exist.", 'error');
            }
        }

        $alterFields = array(
            array(
                'main_table'  => 'customer_reward',
                'field_name'  => 'order_status_id',
                'field_type'  => 'tinyint',
                'field_after' => 'order_id'
            ),
            array(
                'main_table'  => 'customer_reward',
                'field_name'  => 'transaction_type',
                'field_type'  => 'int(10)',
                'field_after' => 'order_status_id'
            ),
            array(
                'main_table'  => 'customer_reward',
                'field_name'  => 'product_id',
                'field_type'  => 'int(10)',
                'field_after' => 'transaction_type'
            ),
            array(
                'main_table'  => 'customer_reward',
                'field_name'  => 'status',
                'field_type'  => 'tinyint',
                'field_after' => 'date_added',
                'field_default' => '1'
            ),
            array(
                'main_table'  => 'customer_reward',
                'field_name'  => 'custom_id',
                'field_type'  => 'int(5)',
                'field_after' => 'product_id'
            ),
            array(
                'main_table'  => 'behavior_rules',
                'field_name'  => 'consecutive_in_day',
                'field_type'  => 'int(5)',
                'field_after' => 'reward_point'
            ),
            array(
                'main_table'  => 'behavior_rules',
                'field_name'  => 'is_cycle',
                'field_type'  => 'tinyint',
                'field_after' => 'reward_point'
            ),
        );

        foreach($alterFields as $field){
            $this->updateField($field);
        }
    }

    public function uninstall() {
        $store_id = 0;

        $this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE store_id = '" . (int)$store_id . "' AND `group` = 'earning'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE store_id = '" . (int)$store_id . "' AND `group` = 'redeeming'");

        $this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE store_id = '" . (int)$store_id . "' AND `group` = 'reward_points'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE `key` = 'rwp_enabled_module' AND `group` = ''");

        $this->db->query("DELETE FROM " . DB_PREFIX . "extension WHERE type = 'total' AND `code` = 'earn_point'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "extension WHERE type = 'total' AND `code` = 'redeem_point'");

        /** Create & Update tables */
        $table_catalog_rules = DB_PREFIX . "catalog_rules";
        $catalog_rule_sql = "DROP TABLE IF EXISTS $table_catalog_rules;";
        try {
            $this->db->query($catalog_rule_sql);
        } catch (Exception $e) { }

        $table_shopping_cart_rules = DB_PREFIX . "shopping_cart_rules";
        $shopping_cart_rule_sql = "DROP TABLE IF EXISTS $table_shopping_cart_rules;";
        try {
            $this->db->query($shopping_cart_rule_sql);
        } catch (Exception $e) { }

        $table_spending_rules = DB_PREFIX . "spending_rules";
        $spending_rule_sql = "DROP TABLE IF EXISTS $table_spending_rules;";
        try {
            $this->db->query($spending_rule_sql);
        } catch (Exception $e) { }

        $table_behavior_rules = DB_PREFIX . "behavior_rules";
        $behavior_rule_sql = "DROP TABLE IF EXISTS $table_behavior_rules;";
        try {
            $this->db->query($behavior_rule_sql);
        } catch (Exception $e) { }

        $table_product_to_reward = DB_PREFIX . "product_to_reward";
        $product_to_reward_sql = "DROP TABLE IF EXISTS $table_product_to_reward;";
        try {
            $this->db->query($product_to_reward_sql);
        } catch (Exception $e) { }

        $table_customer_reward = DB_PREFIX . "customer_reward";
        $customer_reward_fields_sql = "ALTER TABLE $table_customer_reward
                                            DROP COLUMN order_status_id,
                                            DROP COLUMN transaction_type,
                                            DROP COLUMN product_id,
                                            DROP COLUMN status,
                                            DROP COLUMN custom_id;";
        try {
            $this->db->query($customer_reward_fields_sql);
        } catch (Exception $e) { }
    }

    function updateField($fieldInfo){
        $table = DB_PREFIX.$fieldInfo['main_table'];
        $columns = "SHOW COLUMNS FROM ".$table;
        $columns_query = $this->db->query($columns);
        $columns_rows = array();
        foreach($columns_query->rows as $col){
            $columns_rows[$col['Field']] = $col['Field'];
        }


        $alter_field_sql = "ALTER TABLE " . $table . " ADD COLUMN `{$fieldInfo['field_name']}` {$fieldInfo['field_type']} NOT NULL";
        $alter_field_sql .= (isset($fieldInfo['field_default']) ? " default '{$fieldInfo['field_default']}' " : ' ');
        $alter_field_sql .= " AFTER `{$fieldInfo['field_after']}`";

        try {
            if (!isset($columns_rows[$fieldInfo['field_name']]) && $this->db->query($alter_field_sql)) {
                //echo message_tbl("Created field <b>{$fieldInfo['field_name']}</b> in table " . $table . " is complete.");
            }else{
                //echo message_tbl('Field <b>'.$fieldInfo['field_name'].'</b> is exist in table <i>'.$table.'</i>.', 'error');
            }
        }
        catch (Exception $e) {
            //echo message_tbl('Field <b>'.$fieldInfo['field_name'].'</b> is exist in table <i>'.$table.'</i>.', 'error');
        }
    }
}