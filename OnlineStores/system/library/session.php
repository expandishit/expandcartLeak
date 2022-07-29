<?php
class Session {

public $data = array();

public function __construct() {
    if (!session_id()) {
        if(strtolower(STORECODE) == 'qaz123'){
            ini_set('session.use_cookies', 'On');
            ini_set('session.use_trans_sid', 'Off');

            session_set_cookie_params(0, '/');
            // session_set_cookie_params(0, '/;SameSite=None', null, true);
            session_start();
        }else{
            if(defined('SESSION_DRIVER') &&  SESSION_DRIVER==="redis") {
                ini_set('session.save_handler', 'redis');
                $redisAuth='';
                if(REDIS_PASSWORD) {
                    $redisAuth=sprintf("?auth=%s", REDIS_PASSWORD);
                }
                ini_set('session.save_path', sprintf("tcp://%s:%s" . $redisAuth, REDIS_HOSTNAME, REDIS_PORT));
            }
//			if(!is_dir("/var/phpsessions/" . STORECODE . "/")) {
//				mkdir("/var/phpsessions/" . STORECODE . "/", 0775);
//			}
//			ini_set('session.save_path', "/var/phpsessions/" . STORECODE . "/");
            ini_set('session.use_cookies', 'On');
            ini_set('session.use_trans_sid', 'Off');
            ini_set('use_strict_mode', 1);

            //session_set_cookie_params(0, '/', null, true);
            session_set_cookie_params(0, '/;SameSite=None', $_SERVER['HTTP_HOST'], true);
            //session_name("PHPSESSID_" . STORECODE);
            //session_
            session_start();
            if (!isset($_SESSION['sso_site'])) {
                $_SESSION['sso_site']="PHPSESSID_" . STORECODE;
            } elseif ($_SESSION['sso_site'] != "PHPSESSID_" . STORECODE) {
                unset($_SESSION['user_id'], $_SESSION['token']);
                setcookie('admin_remember_me', '', (time() - (604800 + 10)), '/', '', false, true);
                session_destroy();
                //die();
            }
            if(STORECODE == "VYFBJN9114") {
                $session_ver="1";
                if (!isset($_SESSION['session_ver'])) {
                    $_SESSION['session_ver']=$session_ver;
                } elseif ($_SESSION['session_ver'] != $session_ver) {
                    unset($_SESSION['user_id'], $_SESSION['token']);
                    setcookie('admin_remember_me', '', (time() - (604800 + 10)), '/', '', false, true);
                    session_destroy();
                    //die();
                }
            }
        }
    }

    $this->data =& $_SESSION;
}

	function getId() {
		return session_id();
	}
}
?>