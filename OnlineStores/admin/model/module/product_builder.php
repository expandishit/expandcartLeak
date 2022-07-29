<?php

class ModelModuleProductBuilder extends Model
{
    public function install()
    {

        $query = "ALTER TABLE `" .DB_PREFIX. "option_value` ADD COLUMN valuable_type VARCHAR(191) DEFAULT NULL";
        $this->db->query($query);

        $query = "ALTER TABLE `" .DB_PREFIX. "option_value` ADD COLUMN valuable_id INT DEFAULT 0";
        $this->db->query($query);

        $query = "ALTER TABLE `" .DB_PREFIX. "option` ADD COLUMN custom_option VARCHAR(225) DEFAULT NULL";
        $this->db->query($query);

    }

    public function uninstall()
    {
        $query = "ALTER TABLE `" .DB_PREFIX. "option_value` DROP COLUMN valuable_type";
        $this->db->query($query);

        $query = "ALTER TABLE `" .DB_PREFIX. "option_value` DROP COLUMN valuable_id";
        $this->db->query($query);

        $query = "ALTER TABLE `" .DB_PREFIX. "option` DROP COLUMN custom_option";
        $this->db->query($query);
    }

}