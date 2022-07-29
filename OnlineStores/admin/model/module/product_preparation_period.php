<?php

class ModelModuleProductPreparationPeriod extends Model
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
        return $this->config->get('product_preparation_period');
    }

    /**
     * Check if app installed
     *
     * @return boolean
     */
    public function isInstalled()
    {
        return \Extension::isInstalled('product_preparation_period');
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
        if( !$this->_checkIfColumnExist() ){
            $this->db->query("ALTER TABLE `" . DB_PREFIX . "product` ADD preparation_days smallint(6) DEFAULT 0;");
        }
    }

    /**
     *   Remove the values from the database.
     *
     *   @return void whether successful or not.
     */
    public function uninstall()
    {
        $this->db->query("ALTER TABLE `" . DB_PREFIX . "product` DROP COLUMN preparation_days");
    }

    private function _checkIfColumnExist(){
        $query = $this->db->query("SELECT COUNT(*) colcount
                                  FROM INFORMATION_SCHEMA.COLUMNS
                                  WHERE  table_name = 'product'
                                  AND table_schema = DATABASE()
                                  AND column_name = 'preparation_days'");

        return $query->row['colcount'] > 0 ? TRUE : FALSE;
    }
}
