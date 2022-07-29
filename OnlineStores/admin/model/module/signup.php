<?php 
class ModelModuleSignup extends Model {
    
    public function uninstall() {
        $sql = "SHOW TABLES LIKE '" . DB_PREFIX . "signupkw_attributes'";

        $query = $this->db->query($sql);

        if(count($query->rows) > 0) {
            $sql = array();

            // condition not required
            // if (!defined('LOGIN_MODE') || LOGIN_MODE !== 'identity' || !$this->identity->isStoreOnWhiteList()) {
                $sql[]  = "DROP TABLE " . DB_PREFIX . "signupkw";
            // }

            $sql[]  = "DROP TABLE " . DB_PREFIX . "signupkw_attributes";
            $sql[]  = "DROP TABLE " . DB_PREFIX . "signupkw_locales";

            foreach ($sql as $q) {
                $query = $this->db->query($q);
            }
        }
    }

    public function install()
    {
		$sql = array();
        $sql[]  = "create table if not exists ".DB_PREFIX."signupkw (
                               enablemod tinyint(0) not null default 0,
                                 single_box tinyint(0) not null default 0,
                                 newsletter_sub_enabled tinyint(0) not null default 0,
                                 login_register_phonenumber_enabled tinyint(0) not null default 0,
                                 register_phonenumber_unique TINYINT(1) NOT NULL DEFAULT '0',
                                 country_phone_code TINYINT(1) NOT NULL DEFAULT '0',
                                 country_phone_code_login TINYINT(1) NOT NULL DEFAULT '0'
                               )";
		$sql[]  = "create table if not exists ".DB_PREFIX."signupkw_attributes(
                                f_name_show tinyint(0) not null default 0 ,f_name_req tinyint(0)  not null default 0,f_name_cstm varchar(255) not null default '',
                                l_name_show tinyint(0) not null default 0 ,l_name_req tinyint(0)  not null default 0,l_name_cstm varchar(255) not null default '',
                                dob_show tinyint(0) not null default 0 ,dob_req tinyint(0)  not null default 0,dob_cstm date null,
                                gender_show tinyint(0) not null default 0 ,gender_req tinyint(0)  not null default 0,gender_cstm varchar(10) not null default '',
                                mob_show tinyint(0) not null default 0 ,mob_req tinyint(0)  not null default 0,mob_cstm varchar(255) not null default '',
                                fax_show tinyint(0) not null default 0 ,fax_req tinyint(0)  not null default 0,fax_cstm varchar(255) not null default '',
                                company_show tinyint(0) not null default 0 ,company_req tinyint(0)  not null default 0,company_cstm varchar(255) not null default '',
                                companyId_show tinyint(0) not null default 0 ,companyId_req tinyint(0)  not null default 0,companyId_cstm varchar(255) not null default '',
                                address1_show tinyint(0) not null default 0 ,address1_req tinyint(0)  not null default 0,address1_cstm varchar(255) not null default '',
                                address2_show tinyint(0) not null default 0 ,address2_req tinyint(0)  not null default 0,address2_cstm varchar(255) not null default '',
                                city_show tinyint(0) not null default 0 ,city_req tinyint(0)  not null default 0,city_cstm varchar(255) not null default '',
                                pin_show tinyint(0) not null default 0 ,pin_req tinyint(0)  not null default 0,pin_cstm varchar(255) not null default '',
                                state_show tinyint(0) not null default 0 ,state_req tinyint(0)  not null default 0,state_cstm varchar(255) not null default '',
                                area_show tinyint(0) not null default 0 ,area_req tinyint(0)  not null default 0,area_cstm varchar(255) not null default '',
                                country_show tinyint(0) not null default 0 ,country_req tinyint(0)  not null default 0,country_cstm varchar(255) not null default '',
                                passconf_show tinyint(0) not null default 0 ,passconf_req tinyint(0)  not null default 0,passconf_cstm varchar(255) not null default '',
                                subsribe_show tinyint(0) not null default 0 ,subsribe_req tinyint(0)  not null default 0,subsribe_cstm varchar(255) not null default '',
                                mob_min int  not null default 0,mob_max int  not null default 0,mob_fix int  not null default 0,
                                pass_min int  not null default 0,pass_max int  not null default 0,pass_fix int  not null default 0
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
        
        // check if table signupkw empty
        
        $sql[]  = "insert into ".DB_PREFIX."signupkw_attributes set f_name_show =0";
        
        $sql[] = "CREATE TABLE IF NOT EXISTS " . DB_PREFIX ."signupkw_locales (
            `id` INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
            `language_id` INT(11) NULL,
            `key` VARCHAR(255) NULL,
            `value` VARCHAR(255) NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        foreach( $sql as $q )
        {
            $query = $this->db->query( $q );
        }

        $signupkw_query = $this->db->query("SELECT * FROM `" . DB_PREFIX ."signupkw`");

        if (!$signupkw_query->num_rows) {
            $this->db->query("insert into " . DB_PREFIX . "signupkw set enablemod =0");
        }

    }

    public function isInstalled(): bool
    {
        return \Extension::isInstalled('signup');
    }
     
	public function isActiveMod()
    {
        // $queryExt = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'signup'");
        // if($queryExt->num_rows){
        //     $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "signupkw LIMIT 1");       
        //     return $query->row;
        // }  
        
        // return false;     

        if ($this->isInstalled()){
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "signupkw LIMIT 1");
            return $query->row;
        }
        return false;
	}
    
    public function isLoginRegisterByPhonenumber()
    {
        # code...
        $module = $this->isActiveMod();		
		return $module['login_register_phonenumber_enabled'];
    }

        public function getModData() {
		
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "signupkw_attributes LIMIT 1");		
		return $query->row;
	}
	public function editSetting($data) {
		$this->db->query("update " . DB_PREFIX . "signupkw_attributes  set 
                     f_name_show = " . $this->db->escape($data['f_name_show']) .
                       ", f_name_req = " . $this->db->escape($data['f_name_req']) .
", l_name_show = " . $this->db->escape($data['l_name_show']) .
", l_name_req = " . $this->db->escape($data['l_name_req']) .
", mob_show = " . $this->db->escape($data['mob_show']) .
", dob_show = " . $this->db->escape($data['dob_show']) .
", gender_show = " . $this->db->escape($data['gender_show']) .
", dob_req = " . $this->db->escape($data['dob_req']) .
", gender_req = " . $this->db->escape($data['gender_req']) .
", mob_req = " . $this->db->escape($data['mob_req']) .
", fax_show = " . $this->db->escape($data['fax_show']) .
", fax_req = " . $this->db->escape($data['fax_req']) .
", company_show = " . $this->db->escape($data['company_show']) .
", company_req = " . $this->db->escape($data['company_req']) .
", companyId_show = " . $this->db->escape($data['companyId_show']) .
", companyId_req = " . $this->db->escape($data['companyId_req']) .
", address1_show = " . $this->db->escape($data['address1_show']) .
", address1_req = " . $this->db->escape($data['address1_req']) .
", address2_show = " . $this->db->escape($data['address2_show']) .
", address2_req = " . $this->db->escape($data['address2_req']) .
", city_show = " . $this->db->escape($data['city_show']) .
", city_req = " . $this->db->escape($data['city_req']) .
", pin_show = " . $this->db->escape($data['pin_show']) .
", pin_req = " . $this->db->escape($data['pin_req']) .
", state_show = " . $this->db->escape($data['state_show']) .
", state_req = " . $this->db->escape($data['state_req']) .
", area_show = " . $this->db->escape($data['area_show']) .
", area_req = " . $this->db->escape($data['area_req']) .
", country_show = " . $this->db->escape($data['country_show']) .
", country_req = " . $this->db->escape($data['country_req']) .
", passconf_show = " . $this->db->escape($data['passconf_show']) .
", passconf_req = " . $this->db->escape($data['passconf_req']) .
", subsribe_show = " . $this->db->escape($data['subsribe_show']) .
", subsribe_req = " . $this->db->escape($data['subsribe_req']).
 ", f_name_cstm = '" . $this->db->escape($data['f_name_cstm_1']) .
"', l_name_cstm = '" . $this->db->escape($data['l_name_cstm_1']) .
"', dob_cstm = '" . $this->db->escape($data['dob_cstm']) .
"', gender_cstm = '" . $this->db->escape($data['gender_cstm']) .
"', mob_cstm = '" . $this->db->escape($data['mob_cstm_1']) .
"', fax_cstm = '" . $this->db->escape($data['fax_cstm_1']) .
"', company_cstm = '" . $this->db->escape($data['company_cstm_1']) .
"', companyId_cstm = '" . $this->db->escape($data['companyId_cstm_1']) .
"', address1_cstm = '" . $this->db->escape($data['address1_cstm_1']) .
"', address2_cstm = '" . $this->db->escape($data['address2_cstm_1']) .
"', city_cstm = '" . $this->db->escape($data['city_cstm_1']) .
"', pin_cstm = '" . $this->db->escape($data['pin_cstm_1']) .
"', state_cstm = '" . $this->db->escape($data['state_cstm_1']) .
"', area_cstm = '" . $this->db->escape($data['area_cstm_1']) .
"', country_cstm = '" . $this->db->escape($data['country_cstm_1']) .
"', passconf_cstm = '" . $this->db->escape($data['passconf_cstm_1']) .
"', subsribe_cstm = '" . $this->db->escape($data['subsribe_cstm_1']) .
"', mob_min = '" . $this->db->escape($data['mob_min']) .
"', mob_max = '" . $this->db->escape($data['mob_max']) .
"', mob_fix = '" . $this->db->escape($data['mob_fix']) .
"', pass_min = '" . $this->db->escape($data['pass_min']) .
"', pass_max = '" . $this->db->escape($data['pass_max']) .
"', pass_fix = '" . $this->db->escape($data['pass_fix']) ."'"
);

   $this->db->query("update " . DB_PREFIX . "signupkw  set
    enablemod = " . $this->db->escape($data['mod_enable']).
       " , single_box = " . $this->db->escape($data['single_box']).
       " , newsletter_sub_enabled = " . $this->db->escape($data['newsletter_sub_enabled']).
       ", login_register_phonenumber_enabled = ".$this->db->escape($data['login_register_phonenumber_enabled']).
        ", register_phonenumber_unique = ".$this->db->escape($data['register_phonenumber_unique']).
        ", country_phone_code = ".$this->db->escape($data['country_phone_code']).
        ", country_phone_code_login = ".$this->db->escape($data['country_phone_code_login'])
 );
}

public function editLocaleSettings($data)
{
    $query = $this->db->query("DELETE FROM " . DB_PREFIX ."signupkw_locales");
    foreach ($data as $key => $value)
    {
        
        if ( preg_match('/.*_cstm_/', $key) )
        {
            $lang_id = (int) preg_replace('/.*_cstm_/', '', $key);
            $stripped_key = preg_replace('/_cstm_[1-9]/', '', $key);
            $final_key = $stripped_key . '_' . $lang_id;
            $this->db->query("INSERT INTO " . DB_PREFIX . "signupkw_locales SET `key`='" . $this->db->escape($final_key) . "', `value`='" . $this->db->escape($value) . "', `language_id`='" . $this->db->escape($lang_id) . "'");
        }
    }
}


public function getLocales()
{
    $query = $this->db->query("SELECT * FROM " . DB_PREFIX ."signupkw_locales");

    $final = array();
    foreach ($query->rows as $row)
    {
        $key = $row['key'] . '_' . $row['language_id'];
        $final[$key] = $row['value'];
    }

    return $final;
}

    public function getModuleSettings()
    {
        $sql = "SHOW TABLES LIKE '".DB_PREFIX."signupkw'";
        $result = $this->db->query($sql);
        if (count($result->rows) <= 0){
            return array();
        }
        // if the custom signup app installed and the table is exists then fetch
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "signupkw");
        return $query->row;
    }
    
    public function setLoginByPhoneStatus(int $status)
    {
        if($this->isActiveMod()) {
            $this->db->query("UPDATE " . DB_PREFIX . "signupkw set `enablemod` = 1, `login_register_phonenumber_enabled` = ". $status);
        } else {
            $this->db->query("INSERT INTO " . DB_PREFIX . "signupkw set `enablemod` = 1, `login_register_phonenumber_enabled` = ". $status);
        }
    }

}
?>
