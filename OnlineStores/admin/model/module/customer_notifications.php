<?php

class ModelModuleCustomerNotifications extends Model
{ 
    /**
    *   Install the required values for the application.
    *
    *   @return boolean whether successful or not.
    */
    public function install($store_id = 0)
    {
        try 
        {
            $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "customer_notifications` 
            (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `customer_id` int(11) NOT NULL,
                `read_status` tinyint(4) DEFAULT 0,
                `icon` varchar(45) DEFAULT NULL,
                `url` varchar(255) DEFAULT NULL,
                `notification_text` text DEFAULT NULL,
                `notification_module` varchar(45) DEFAULT NULL,
                `notification_module_code` varchar(45) DEFAULT NULL,
                `notification_module_id` int(11) NULL,
                `count` int(11) NULL,
                `created_at` datetime NOT NULL DEFAULT current_timestamp(),
                 PRIMARY KEY (`id`),
                 INDEX (`customer_id`)
            ) 
              ENGINE=InnoDB DEFAULT CHARSET=utf8;";

           $this->db->query($sql);

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
    public function uninstall()
    {
        try
        {
            $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "customer_notifications`;");
           
            return true;
        } 
        catch (Exception $e)
        {
            return false;
        }
    }
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
            'customer_notifications', $inputs
        );

        return true;
    }

    public function addCustomerNotifications($data)
    {
        $this->db->query("INSERT INTO " . DB_PREFIX . "customer_notifications 
        SET customer_id = '" . (int)$this->db->escape($data['customer_id']) . "', 
        icon = '" .$data['icon']. "',
        notification_text = '" .serialize($data['notification_text']). "',
        notification_module = '" .$data['notification_module']. "',
        notification_module_code = '" .$data['notification_module_code']. "',
        notification_module_id = '" .$data['notification_module_id']. "'");
    }

   
}