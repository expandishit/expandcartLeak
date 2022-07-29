<?php
class ModelMobileSettings extends Model {
    public function getSettings(){
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "mobsettings` ");

        return $query->row;
    }
}