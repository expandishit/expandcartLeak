<?php

class ModelModuleOrderAssignee extends Model
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
            'order_assignee', $inputs
        );

        return true;
    }

    public function getSettings()
    {
        return $this->config->get('order_assignee');
    }

    public function isActive()
    {
        $settings = $this->getSettings();

        if (isset($settings) && $settings['status'] == 1) {
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
            $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "order_assignee` 
            (
                `order_assignee_id` int(11) NOT NULL AUTO_INCREMENT,
                `order_id` int(11) NOT NULL,
                `user_id` int(11) NOT NULL,
                PRIMARY KEY (`order_assignee_id`),
                INDEX (`order_id`,`user_id`)
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
            $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "order_assignee`;");
           
            return true;
        } 
        catch (Exception $e)
        {
            return false;
        }
    }

    
    public function isOrderAssigneeAppInstalled()
    {
        if(\Extension::isInstalled('order_assignee') &&$this->config->get('order_assignee')['status']==1) return true;
        else return false;
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
   
}