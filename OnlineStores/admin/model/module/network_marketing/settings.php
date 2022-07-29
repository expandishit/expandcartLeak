<?php

class ModelModuleNetworkMarketingSettings extends Model
{

    private $referralsTable = DB_PREFIX . 'nm_referrals';
    private $levelsTable = DB_PREFIX . 'nm_levels';
    private $agenciesTable = DB_PREFIX . 'nm_agencies';
    private $transactionsTable = DB_PREFIX . 'nm_transactions';

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
            'network_marketing', $inputs
        );

        return true;
    }

    public function getSettings()
    {
        return ($this->config->get('network_marketing') ?: []);
    }

    public function install()
    {
        $installQueries = [];

        $installQueries[] = 'CREATE TABLE IF NOT EXISTS `' . $this->referralsTable . '` (
            `ref_id` INT(11) NOT NULL AUTO_INCREMENT,
            `customer_id` INT(11) NOT NULL,
            `referrer` INT(11) NOT NULL,
            `agency_id` INT(11) NOT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`ref_id`, `customer_id`, `referrer`, `agency_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8';

        $installQueries[] = 'CREATE TABLE IF NOT EXISTS `' . $this->levelsTable . '` (
            `level_id` INT(11) NOT NULL AUTO_INCREMENT,
            `fixed` FLOAT(11,0) NOT NULL,
            `percentage` FLOAT(11,0) NOT NULL,
            `level_order` INT(11) NOT NULL,
            `level_status` INT(1) NOT NULL DEFAULT "0",
            PRIMARY KEY (`level_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8';

        $installQueries[] = 'CREATE TABLE IF NOT EXISTS `' . $this->agenciesTable . '` (
            `agency_id` INT(11) NOT NULL AUTO_INCREMENT,
            `customer_id` INT(11) NOT NULL,
            `ref_id` VARCHAR(20) NOT NULL,
            `parent` INT(11) NOT NULL DEFAULT "0",
            `level` INT(11) NOT NULL DEFAULT "1",
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`agency_id`, `customer_id`, parent)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8';

        $installQueries[] = 'CREATE TABLE IF NOT EXISTS `' . $this->transactionsTable . '` (
            `transaction_id` INT(11) NOT NULL AUTO_INCREMENT,
            `customer_id` INT(11) NOT NULL,
            `order_id` VARCHAR(20) NOT NULL,
            `transaction_details` INT(11) NOT NULL DEFAULT "0",
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`transaction_id`, `customer_id`, `order_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8';

        foreach ($installQueries as $query) {
            $this->db->query($query);
        }
    }

    public function uninstall()
    {
        $dropQueries = [];
        $dropQueries[] = 'DROP TABLE IF EXISTS `' . $this->referralsTable . '`';
        $dropQueries[] = 'DROP TABLE IF EXISTS `' . $this->levelsTable . '`';
        $dropQueries[] = 'DROP TABLE IF EXISTS `' . $this->agenciesTable . '`';
        $dropQueries[] = 'DROP TABLE IF EXISTS `' . $this->transactionsTable . '`';

        foreach ($dropQueries as $query) {
            $this->db->query($query);
        }
    }

    public function validate($inputs)
    {
        return true;
    }

    /**
     * Checks if the application is installed or not.
     *
     * @return bool
     */
    public function isInstalled()
    {
        $isInstalled = $this->db->query("SELECT 1 FROM " . DB_PREFIX . "extension WHERE `code` = 'abandoned_cart'");

        if ($isInstalled->num_rows) {
            return true;
        }

        return false;
    }
}
