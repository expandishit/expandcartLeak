<?php 
class ModelAccountSignup extends Model {
    public function install() {
        $sql = " SHOW TABLES LIKE '".DB_PREFIX."signupkw'";
		$query = $this->db->query($sql);

        if (count($query->rows) <= 0) {
			$sql = array();

            $sql[] = "create table if not exists ".DB_PREFIX."signupkw (
                      enablemod tinyint(0) not null default 0 ,
                      single_box tinyint(0) not null default 0,
                      newsletter_sub_enabled tinyint(0) not null default 0,
                      register_phonenumber_unique tinyint(0) not null default 0
                      )";

			$sql[] = "create table if not exists ".DB_PREFIX."signupkw_attributes(
                                    f_name_show tinyint(0) not null default 0 ,f_name_req tinyint(0)  not null default 0,f_name_cstm varchar(255) not null default '',
                                    l_name_show tinyint(0) not null default 0 ,l_name_req tinyint(0)  not null default 0,l_name_cstm varchar(255) not null default '',
                                    mob_show tinyint(0) not null default 0 ,mob_req tinyint(0)  not null default 0,mob_cstm varchar(255) not null default '',
                                    fax_show tinyint(0) not null default 0 ,fax_req tinyint(0)  not null default 0,fax_cstm varchar(255) not null default '',
                                    company_show tinyint(0) not null default 0 ,company_req tinyint(0)  not null default 0,company_cstm varchar(255) not null default '',
                                    companyId_show tinyint(0) not null default 0 ,companyId_req tinyint(0)  not null default 0,companyId_cstm varchar(255) not null default '',
                                    address1_show tinyint(0) not null default 0 ,address1_req tinyint(0)  not null default 0,address1_cstm varchar(255) not null default '',
                                    address2_show tinyint(0) not null default 0 ,address2_req tinyint(0)  not null default 0,address2_cstm varchar(255) not null default '',
                                    city_show tinyint(0) not null default 0 ,city_req tinyint(0)  not null default 0,city_cstm varchar(255) not null default '',
                                    pin_show tinyint(0) not null default 0 ,pin_req tinyint(0)  not null default 0,pin_cstm varchar(255) not null default '',
                                    state_show tinyint(0) not null default 0 ,state_req tinyint(0)  not null default 0,state_cstm varchar(255) not null default '',
                                    country_show tinyint(0) not null default 0 ,country_req tinyint(0)  not null default 0,country_cstm varchar(255) not null default '',
                                    passconf_show tinyint(0) not null default 0 ,passconf_req tinyint(0)  not null default 0,passconf_cstm varchar(255) not null default '',
                                    subsribe_show tinyint(0) not null default 0 ,subsribe_req tinyint(0)  not null default 0,subsribe_cstm varchar(255) not null default '',
                                    mob_min int  not null default 0,mob_max int  not null default 0,mob_fix int  not null default 0,
                                    pass_min int  not null default 0,pass_max int  not null default 0,pass_fix int  not null default 0
                                    )	";

            $sql[]  = "insert into ".DB_PREFIX."signupkw set enablemod =0";

            $sql[]  = "insert into ".DB_PREFIX."signupkw_attributes set f_name_show =0";
                        
            foreach( $sql as $q ){
				$query = $this->db->query( $q );
			}
        }
    }

	public function isActiveMod() {
        $exist_table = $this->db->query('SHOW TABLES LIKE "signupkw"');

        if($exist_table->num_rows == 0){
            return false;
        }
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "signupkw LIMIT 1");
        return $query->row;
	}
	
    public function getModData() {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "signupkw_attributes LIMIT 1");
		return $query->row;
	}

    public function isPhonenumberUnique()
    {
        # code...
        if(!$this->isActiveMod()){
            return false;
        }
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "signupkw LIMIT 1");
        return $query->row['register_phonenumber_unique'];
    }

    public function isLoginRegisterByPhonenumber()
    {
        # code...
        if(!$this->isActiveMod()){
            return false;
        }
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "signupkw LIMIT 1");
        return $query->row['login_register_phonenumber_enabled'];
    }

}
?>