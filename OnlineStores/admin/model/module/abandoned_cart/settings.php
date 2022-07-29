<?php

class ModelModuleAbandonedCartSettings extends Model
{
    /**
     * Abandoned cart send emails table.
     *
     * @var string
     */
    protected $emailedAbandonedOrdersTable = 'emailed_abandoned_orders';

    /**
     * Application settings key.
     *
     * @var string
     */
    protected $settingsKey = 'abandoned_cart';

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
            $this->settingsKey, $inputs
        );

        return true;
    }

    /**
     * Install and apply the required DB changes.
     *
     * @return void
     */
    public function install()
    {
        $this->load->model('setting/setting');
        $storeName = $this->config->get("config_name")['en'] ?? current($this->config->get("config_name"));
        $this->model_setting_setting->insertUpdateSetting( 'abandoned_cart', ['abandoned_cart' => ['mail_subject' => "{$storeName} Abandoned Cart Notification", 'mail_message' => '<p style="text-align: center; "><span style="font-size: 18px;"><font color="#085294"><b>Don\'t forget to complete your order!</b></font></span></p><p style="text-align: left;"><span style="font-size: 18px;"><font color="#085294"><b><br></b></font></span></p><p style="text-align: center;"><span style="font-size: 12px;"><font color="#424242"><span style="font-size: 14px;">﻿</span><font style=""><span style="font-size: 14px;">Looks like there are still some items in your cart, but don\'t worry - we have saved them for you.</span></font></font></span><span style="font-size: 18px;"><font color="#085294"><b><br></b></font></span></p>'] ]);

        //Create table emailed_abandoned_orders
        $installQueries = $fields = [];
        $installQueries[] = 'CREATE TABLE IF NOT EXISTS `' . $this->emailedAbandonedOrdersTable . '` (';
        $fields[] = '`id` INT(11) NOT NULL AUTO_INCREMENT';
        $fields[] = '`order_id` INT(11) NOT NULL';
        $fields[] = '`emailed` TINYINT(3) NOT NULL DEFAULT "0"';
        $fields[] = 'PRIMARY KEY (`id`)';
        $installQueries[] = implode(',', $fields);
        $installQueries[] = ')';
        $this->db->query(implode(' ', $installQueries));

        $this->createCronJobTable();
    }

    /**
     * To drop the application related changes.
     *
     * @return void
     */
    public function uninstall()
    {
        $dropTables = 'DROP TABLE IF EXISTS `' . $this->emailedAbandonedOrdersTable . '`, `abandoned_order_cronjob`';

        $this->db->query($dropTables);
    }


    public function createCronJobTable(){
        //Create table orders cronjobs 
        $this->db->query("CREATE TABLE IF NOT EXISTS  `abandoned_order_cronjob` (
            `cronjob_id` int(11) NOT NULL AUTO_INCREMENT,
            `order_id` int(11) NOT NULL,
            PRIMARY KEY (`cronjob_id`),
            FOREIGN KEY (order_id) REFERENCES `order` (order_id) ON DELETE CASCADE              
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    }
    
    /**
     * Gets the application settings.
     *
     * @return array
     */
    public function getSettings()
    {
        $settings = $this->config->get($this->settingsKey);
        $settings['mail_message'] = htmlspecialchars_decode($settings['mail_message']);
        return $settings;
    }

    /**
     * Checks if the application is active or not.
     *
     * @return bool
     */
    public function isActive()
    {
        $enabled=false;
        $settings = $this->getSettings();

        if (isset($settings) && $settings['status'] == 1) {
            $enabled =  true;
        }
        return $enabled && $this->isInstalled();
    }

    /**
     * Checks if the application is insalled or not.
     *
     * @return bool
     */
    public function isInstalled()
    {
        $application = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'abandoned_cart'");

        if ($application->num_rows > 0) {
            return true;
        }

        return false;
    }
}
