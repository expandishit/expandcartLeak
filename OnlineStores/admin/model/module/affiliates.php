<?php

class ModelModuleAffiliates extends Model
{

    /**
     * update the module settings.
     *
     * @param array $inputs
     *
     * @return bool
     */
    public function updateSettings($inputs)
    {
    }

    public function getSettings()
    {
        return $this->config->get('affiliates');
    }

    /**
     * Check if app installed
     *
     * @return boolean
     */
    public function isInstalled()
    {
        return \Extension::isInstalled('affiliates');
    }

    /**
     * Check is App Active
     *
     * @return boolean
     */
    public function isActive()
    {
        return $this->isInstalled() && (int) $this->getSettings()['status'] === 1;
    }

    /**
     *   Install the required values for the application.
     *
     *   @return void whether successful or not.
     */
    public function install()
    {

        $query = "INSERT INTO `setting` (`store_id`, `group`, `key`, `value`, `serialized`) VALUES
            (0, 'affiliates', 'affiliates', 'a:1:{s:6:\"status\";s:1:\"1\";}', 1);";
        $this->db->query($query);

        $query = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."affiliate` (
          `affiliate_id` int(11) NOT NULL AUTO_INCREMENT,
          `firstname` varchar(32) NOT NULL,
          `lastname` varchar(32) NOT NULL,
          `email` varchar(96) NOT NULL,
          `telephone` varchar(32) NOT NULL,
          `fax` varchar(32) NOT NULL,
          `password` varchar(40) NOT NULL,
          `salt` varchar(9) NOT NULL,
          `company` varchar(32) NOT NULL,
          `website` varchar(255) NOT NULL,
          `address_1` varchar(128) NOT NULL,
          `address_2` varchar(128) NOT NULL,
          `city` varchar(128) NOT NULL,
          `postcode` varchar(10) NOT NULL,
          `country_id` int(11) NOT NULL,
          `zone_id` int(11) NOT NULL,
          `code` varchar(64) NOT NULL,
          `commission` decimal(4,2) NOT NULL DEFAULT 0.00,
          `tax` varchar(64) NOT NULL,
          `payment` varchar(6) NOT NULL,
          `cheque` varchar(100) NOT NULL,
          `paypal` varchar(64) NOT NULL,
          `bank_name` varchar(64) NOT NULL,
          `bank_branch_number` varchar(64) NOT NULL,
          `bank_swift_code` varchar(64) NOT NULL,
          `bank_account_name` varchar(64) NOT NULL,
          `bank_account_number` varchar(64) NOT NULL,
          `ip` varchar(40) NOT NULL,
          `status` tinyint(1) NOT NULL,
          `approved` tinyint(1) NOT NULL,
          `date_added` datetime NOT NULL,
          `seller_affiliate_code` varchar(64) default null,
          `seller_affiliate_commission` decimal(4,2) default 0.00,
          `seller_affiliate_type` char default 'P',
          PRIMARY KEY (`affiliate_id`)
        );";
        $this->db->query($query);

        $query = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."affiliate_transaction` (
          `affiliate_transaction_id` int(11) NOT NULL AUTO_INCREMENT,
          `affiliate_id` int(11) NOT NULL,
          `order_id` int(11) NOT NULL,
          `description` text NOT NULL,
          `amount` decimal(15,4) NOT NULL,
          `date_added` datetime NOT NULL,
          `is_seller_affiliate` tinyint(1) default 0,
          PRIMARY KEY (`affiliate_transaction_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

        $this->db->query($query);
    }

    /**
     *   Remove the values from the database.
     *
     *   @return void whether successful or not.
     */
    public function uninstall()
    {
        $query = "DROP TABLE IF EXISTS `" .DB_PREFIX. "affiliate`";
        $this->db->query($query);

        $query = "DROP TABLE IF EXISTS `" .DB_PREFIX. "affiliate_transaction`";
        $this->db->query($query);
    }
}
