<?php


class ModelModuleSignup extends Model
{

    public function isActiveMod()
    {
        $queryExt = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'signup'");
        if($queryExt->num_rows){
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "signupkw LIMIT 1");
            return $query->row;
        }

        return false;
    }

    public function isLoginRegisterByPhoneNumber()
    {
        # code...
        $module = $this->isActiveMod();
        return $module['login_register_phonenumber_enabled'];
    }

}