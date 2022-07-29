<?php

/**
 * TODO :-
 * - implement a seperated validation library.
*/

class ModelModuleProductDesignerSettings extends Model
{

    // private $table = DB_PREFIX . 'custom_tshirt_design';

    /**
     * validate inputs againts some rules.
     *
     * @param array $inputs
     * @param array $rules
     *
     * @return bool|\Exception
     */
    public function validateSettings($inputs, $rules)
    {
        return true;
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
            'custom_tshirt_design', $inputs
        );

        return true;
    }

    public function checkTables()
    {
        echo 'show columns from ' . $this->table . ';';exit;
        $tables = $this->db->query('show columns from `' . $this->table . '`;');
        if (
            isset($tables) &&
            $tables->num_rows > 0
        ) {
            return true;
        }
    }

    public function install()
    {
        $column = 'custom_tshirt_design';
        if (
            !$this->db->query(
                'SHOW COLUMNS FROM `' . $this->table . '` LIKE "' . $column . '"'
            )->row
        ) {
            $query  = 'ALTER TABLE `' . $this->table . '` ADD `' . $column . '` ';
            $query .= 'TINYINT(1) NOT NULL DEFAULT "0"';
            $this->db->query($query);
        }
    }
}
