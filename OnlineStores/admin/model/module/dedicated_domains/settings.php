<?php

use ExpandCart\Foundation\Providers\DedicatedDomains;

class ModelModuleDedicatedDomainsSettings extends Model
{
    private $dedicatedDomainsTable = DB_PREFIX . 'dedicated_domains';
    private $dedicatedDomainsPricesTable = DB_PREFIX . 'dedicated_domains_prices';
    private $productSpecialTable = DB_PREFIX . 'product_special';
    private $productToDomainTable = DB_PREFIX . 'product_to_domain';
    private $productDiscountTable = DB_PREFIX . 'product_discount';

    public $errors = [];

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
            'dedicated_domains', $inputs
        );

        return true;
    }

    public function install()
    {
        $installQueries = [];
        $installQueries[] = 'CREATE TABLE IF NOT EXISTS `' . $this->dedicatedDomainsTable . '` (';
        $installQueries[] = '`domain_id` INT(11) NOT NULL AUTO_INCREMENT,';
        $installQueries[] = '`domain` VARCHAR(255) NOT NULL,';
        $installQueries[] = '`currency` VARCHAR(3) NOT NULL DEFAULT "USD",';
        $installQueries[] = '`country` VARCHAR(3) NULL DEFAULT "WWW",';
        $installQueries[] = '`domain_status` INT(1) NOT NULL DEFAULT "0",';
        $installQueries[] = 'PRIMARY KEY (`domain_id`)';
        $installQueries[] = ')';

        $this->db->query(implode(' ', $installQueries));

        // Add default domain
        $installQueries = [];
        $installQueries[] = "INSERT INTO `" . $this->dedicatedDomainsTable . "`";
        $installQueries[] = " VALUES (NULL,'".$_SERVER[HTTP_HOST]."','".$this->config->get('config_currency')."','WWW','1')";

        $this->db->query(implode(' ', $installQueries));


        $installQueries = [];
        $installQueries[] = 'CREATE TABLE IF NOT EXISTS `' . $this->dedicatedDomainsPricesTable . '` (';
        $installQueries[] = '`price_id` INT(11) NOT NULL AUTO_INCREMENT,';
        $installQueries[] = '`domain_id` INT(11) NOT NULL,';
        $installQueries[] = '`product_id` INT(11) NOT NULL,';
        $installQueries[] = '`price` decimal(15,4) NOT NULL DEFAULT "0.0000",';
        $installQueries[] = 'PRIMARY KEY (`price_id`, `domain_id`, `product_id`)';
        $installQueries[] = ')';

        $this->db->query(implode(' ', $installQueries));

        $installQueries = [];
        $installQueries[] = 'CREATE TABLE IF NOT EXISTS `' . $this->productToDomainTable . '` (';
        $installQueries[] = '`product_id` INT(11) NOT NULL,';
        $installQueries[] = '`domain_id` INT(11) NOT NULL,';
        $installQueries[] = 'PRIMARY KEY (`product_id`, `domain_id`)';
        $installQueries[] = ')';

        $this->db->query(implode(' ', $installQueries));

        $alterTables = [];
        $alterTables[] = 'ALTER TABLE `' . $this->productSpecialTable . '`';
        $alterTables[] = 'ADD `dedicated_domains` TEXT NULL after `price`';

        $this->db->query(implode(' ', $alterTables));

        $alterTables = [];
        $alterTables[] = 'ALTER TABLE `' . $this->productDiscountTable . '`';
        $alterTables[] = 'ADD `dedicated_domains` TEXT NULL after `price`';

        $this->db->query(implode(' ', $alterTables));
    }

    public function uninstall()
    {
        $dropTables = [];
        $dropTables[] = 'DROP TABLE IF EXISTS `' . $this->dedicatedDomainsTable . '`';

        $this->db->query(implode(' ', $dropTables));

        $dropTables = [];
        $dropTables[] = 'DROP TABLE IF EXISTS `' . $this->dedicatedDomainsPricesTable . '`';

        $this->db->query(implode(' ', $dropTables));


        $dropTables = 'DROP TABLE IF EXISTS `' . $this->productToDomainTable . '`';

        $this->db->query($dropTables);

        $alterTables = [];
        $alterTables[] = 'ALTER TABLE `' . $this->productSpecialTable . '`';
        $alterTables[] = 'DROP COLUMN `dedicated_domains`';

        $this->db->query(implode(' ', $alterTables));

        $alterTables = [];
        $alterTables[] = 'ALTER TABLE `' . $this->productDiscountTable . '`';
        $alterTables[] = 'DROP COLUMN `dedicated_domains`';

        $this->db->query(implode(' ', $alterTables));
    }

    public function validate($inputs)
    {
        return true;
    }

    public function validateDomainInputs($inputs)
    {
        $this->errors = [];
        $pattern = '#^[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9](?:\.[a-zA-Z]{2,})+$#';
        $pattern = '#^(?!\-)(?:[a-zA-Z\d\-]{0,62}[a-zA-Z\d]\.){1,126}(?!\d+)[a-zA-Z\d]{1,63}$#';

        if (!preg_match($pattern, $inputs['name'])) {
            $this->errors[] = $this->language->get('error_invalid_domain_name');
        }

        if (!preg_match('#^[A-Z]{3}$#', $inputs['currency'])) {
            $this->errors[] = $this->language->get('error_invalid_currency');
        }

        if (count($this->errors) == 0) {
            return true;
        }

        return false;
    }

    public function isActive()
    {
        $dedicatedDomains = new DedicatedDomains;

        if ($dedicatedDomains->isActive()) {

            unset($dedicatedDomains);

            return true;
        }

        unset($dedicatedDomains);

        return false;
    }
}
