<?php
class ModelAccountSignup extends Model
{
    public function isActiveMod()
    {
        // check table exists
        $exist_table = $this->db->query('SHOW TABLES LIKE "signupkw"');

        if ($exist_table->num_rows == 0) {
            // create signupkw table
            $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX ."signupkw`(`enablemod` TINYINT(0) NOT NULL DEFAULT 0, `single_box` TINYINT(0) NOT NULL DEFAULT 0, `newsletter_sub_enabled` TINYINT(0) NOT NULL DEFAULT 0, `login_register_phonenumber_enabled` TINYINT(0) NOT NULL DEFAULT 0, `register_phonenumber_unique` TINYINT(1) NOT NULL DEFAULT '0', `country_phone_code` TINYINT(1) NOT NULL DEFAULT '0') ENGINE = INNODB DEFAULT CHARSET = utf8");
            // set default value
            $this->db->query("INSERT INTO `" . DB_PREFIX ."signupkw` VALUES(1, 0, 0, 0, 0, 0)");
        }
        
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "signupkw LIMIT 1");
        return $query->row;
    }

    public function isLoginRegisterByPhonenumber()
    {
        $settings = $this->isActiveMod();
        
        return $settings['login_register_phonenumber_enabled'];
    }
}
