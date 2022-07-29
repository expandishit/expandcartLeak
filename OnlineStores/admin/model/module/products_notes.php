<?php

class ModelModuleProductsNotes extends Model
{
    /**
     * Check if app installed
     *
     * @return boolean
     */
    public function isInstalled(): bool
    {
        return \Extension::isInstalled('products_notes');
    }

    /**
     * Check is App Active
     *
     * @return boolean
     */
    public function isActive(): bool
    {
        return $this->isInstalled() && (int) $this->getSettings()['status'] === 1;
    }

    public function getSettings()
    {
        return $this->config->get('products_notes');
    }

    /**
     *   Install the required values for the application.
     *
     *   @return void whether successful or not.
     */
    public function install()
    {
        $query = "INSERT INTO `setting` (`store_id`, `group`, `key`, `value`, `serialized`) VALUES
            (0, 'products_notes', 'products_notes', 'a:3:{s:11:\"general_use\";s:1:\"1\";s:14:\"internal_notes\";s:1:\"1\";s:6:\"status\";s:1:\"1\";}', 1);";
        $this->db->query($query);

        if( !$this->_checkIfColumnExist("notes") ){
            $this->db->query("ALTER TABLE `" . DB_PREFIX . "product` ADD `notes` text DEFAULT null;");
        }

        if( !$this->_checkIfColumnExist("general_use") ){
            $this->db->query("ALTER TABLE `" . DB_PREFIX . "product` ADD `general_use` varchar(255) DEFAULT null;");
        }
    }

    /**
     *   Remove the values from the database.
     *
     *   @return void whether successful or not.
     */
    public function uninstall()
    {
        $this->db->query("ALTER TABLE `" . DB_PREFIX . "product` DROP COLUMN notes;");
        $this->db->query("ALTER TABLE `" . DB_PREFIX . "product` DROP COLUMN general_use;");

    }

    private function _checkIfColumnExist($column_name): bool
    {
        $query = $this->db->query("SELECT COUNT(*) colcount
                                  FROM INFORMATION_SCHEMA.COLUMNS
                                  WHERE  table_name = 'product'
                                  AND table_schema = DATABASE()
                                  AND column_name = '$column_name'");

        return $query->row['colcount'] > 0;
    }
}
