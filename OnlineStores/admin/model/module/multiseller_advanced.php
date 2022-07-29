<?php

class ModelModuleMultisellerAdvanced extends Model
{ 

    /**
     * update the module settings.
     *
     * @param array $inputs
     *
     * @return bool|\Exception
     */
    public function updateSettings($inputs)
    {
        $this->load->model('setting/setting');

        $this->model_setting_setting->editSetting(
            'multiseller_advanced', $inputs
        );

        return true;
    }

    public function getSettings()
    {
        return $this->config->get('multiseller_advanced');
    }

    public function isActive()
    {
        $settings = $this->getSettings();

        if (isset($settings) && $settings['status'] == 1) {
            return true;
        }

        return false;
    }

    public function isMessagingSellerEnabled(){
        $settings = $this->getSettings();

        if (isset($settings) && $settings['messaging_seller'] == 1) {
            return true;
        }

        return false;
    }

    //Check if multiseller App Installed
    public function isMultiseller(){
        $queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");

        if($queryMultiseller->num_rows) {
            return true;
        }

        return false;
    }

    /**
    *   Install the required values for the application.
    *
    *   @return boolean whether successful or not.
    */
    public function install($store_id = 0)
    {
        try 
        {
            $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ms_messaging` 
                    (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `user1_id` int(11) NOT NULL,
                        `user2_id` int(11) NOT NULL,
                        `user1_type` varchar(191) NOT NULL,
                        `user2_type` varchar(191) NOT NULL,
                        `subject` varchar(191) NOT NULL DEFAULT 'No Subject',
                        `product_id` int(11) NULL,
                        `status` int(2) NOT NULL,
                        `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        PRIMARY KEY (`id`)
                    ) 
                    ENGINE=InnoDB DEFAULT CHARSET=utf8;";

            $this->db->query($sql);

            $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ms_messaging_msgs` 
                    (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `messaging_id` int(11) NOT NULL,
                        `owner_id` int(11) NOT NULL,
                        `owner` varchar(191) NOT NULL,
                        `message` TEXT NULL,
                        `read` int(2) NOT NULL DEFAULT 0,
                        `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        PRIMARY KEY (`id`)
                    ) 
                    ENGINE=InnoDB DEFAULT CHARSET=utf8;";   
            $this->db->query($sql);    
            
           $this->sellerBasedInstall();
               return true;
  
        } 
        catch (Exception $e) 
        {
            return false;
        }
    }

    public function sellerBasedInstall()
    {
        try 
        { 
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ms_sellerbased_options` (
                `sellerbased_options_id` int(11) NOT NULL AUTO_INCREMENT,
                `sellerbased_options_status` int(2) NOT NULL DEFAULT '0',
                `sellerbased_options_default` int(2) NOT NULL DEFAULT '0',
                `allowed_options_ids` TEXT NULL,
                PRIMARY KEY (`sellerbased_options_id`)
                ) DEFAULT CHARSET=utf8");

            $this->db->query("
                CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ms_sellerbased_options_description` (
                `sellerbased_options_id` int(11) NOT NULL,
                `language_id` int(11) NOT NULL,
                `title` VARCHAR(255) NOT NULL,
                PRIMARY KEY (`sellerbased_options_id`, `language_id`)
                ) DEFAULT CHARSET=utf8");
            $queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'seller_based'");
            if(!$queryMultiseller->num_rows) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "extension (type, code) VALUES('shipping','seller_based')"); 
            } 
               return true;
  
        } 
        catch (Exception $e) 
        {
            return false;
        }

    }

    /**
    *   Remove the values from the database.
    *
    *   @return boolean whether successful or not.
    */
    public function uninstall($store_id = 0, $group = 'custom_fees_for_payment_method')
    {
        try
        {
            $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ms_messaging`;");
            $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ms_messaging_msgs`;");
            $this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `group`='seller_based' AND `key`='seller_based_status'");
            return true;
        } 
        catch (Exception $e)
        {
            return false;
        }
    }


        /**
     * Get all sellers from database
     *
     * @param array $data
     * @param array $sort
     * @param array $cols
     *
     * @return array
     */
    public function getSellersHaveMessages()
    {
        $sql = "select seller_id as id, nickname from ms_seller where seller_id in (select distinct user1_id from ms_messaging union select distinct user2_id from ms_messaging);";
        $res = $this->db->query($sql);
        return $res->rows;
    }

    public function getConversations($user_id)
    {
        $query = "SELECT convs.*, if(DATE(`convs`.`date_added`) = DATE(NOW()), DATE_FORMAT(convs.date_added, '%h:%i %p') , DATE_FORMAT(convs.date_added, '%b %d'))  as date_added ";
        $query .= ",c.customer_id customer_id, CONCAT(c.firstname, ' ', c.lastname) name, c.email email, c.telephone telephone, c.fax fax ";
        $query .= ",_c.customer_id _customer_id, CONCAT(_c.firstname, ' ', _c.lastname) _name, _c.email _email, _c.telephone _telephone, _c.fax _fax ";
        $query .= ",ms.seller_id seller_id, ms.company company, ms.website website, ms.avatar avatar, ms.nickname nickname ";
        $query .= ",_ms.seller_id _seller_id, _ms.company _company, _ms.website _website, _ms.avatar _avatar, _ms.nickname _nickname ";
        $query .= ",(SELECT COUNT(`read`) total_unread from ". DB_PREFIX ."ms_messaging_msgs WHERE `read`= 0 AND " . DB_PREFIX . "ms_messaging_msgs.messaging_id=convs.id AND owner_id != '{$user_id}') total_unread ";
        $query .= "FROM " . DB_PREFIX . "ms_messaging convs ";
        $query .= "LEFT JOIN customer c ON (c.customer_id = convs.user1_id AND convs.user1_id != '{$user_id}') ";
        $query .= "LEFT JOIN customer _c ON (_c.customer_id = convs.user2_id AND convs.user2_id != '{$user_id}') ";
        $query .= "LEFT JOIN ms_seller ms ON (ms.seller_id = convs.user1_id AND convs.user1_id != '{$user_id}') ";
        $query .= "LEFT JOIN ms_seller _ms ON (_ms.seller_id = convs.user2_id AND convs.user2_id != '{$user_id}') ";
        $query .= "WHERE convs.user1_id={$user_id} OR convs.user2_id={$user_id}";
        return $this->db->query($query)->rows;
    }

    public function getConversationMsgs($messaging_id)
    {
        $query = "SELECT * FROM " . DB_PREFIX . "ms_messaging_msgs WHERE messaging_id = '{$messaging_id}'";

        return $this->db->query($query)->rows;
    }
}
