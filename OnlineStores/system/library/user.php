<?php
class User {
	private $user_id;
	private $username;
	private $name;
    private $fname;
    private $lname;
    private $email;
    private $image;
    private $user_group;
  	private $permission = array();

  	/**
     * The remember tokens table name.
     *
  	 * @var
     */
  	private $rememberMeTable = 'remember_tokens';

    /**
     * The user table name.
     *
     * @var
     */
    private $userTable = 'user';

    /**
     * The token cookie expiration period.
     * TODO this is a temporary value (1 week)
     *
     * @var
     */
    private $expiration = 604800;

    public function __construct($registry)
    {
		$this->db = $registry->get('db');
        $this->ecusersdb = $registry->get('ecusersdb');
		$this->request = $registry->get('request');
		$this->session = $registry->get('session');
		//$this->avatar = $registry->get('avatar');

        $this->config = $registry->get('config');
        $now = strtotime("-10 minute");

        if ($this->config->get('allow_log_in') &&
            str_contains($_SERVER['SERVER_NAME'],strtolower(STORECODE) ) &&
            ($now < $this->config->get('login_time') )

        ){
            // login the same user to expand cart domain
            $this->session->data['user_id']=$this->config->get('user_id');
        }

		if (isset($this->request->cookie['admin_remember_me']) && !isset($this->session->data['user_id'])) {

		    $token = $this->resolveRememberToken($this->request->cookie['admin_remember_me']);

		    if (!isset($token['validator']) || !isset($token['selector'])) {
                $this->logout();
            }

            $authToken = $this->getTokenInfo($token['selector']);

		    if (!hash_equals($authToken['validator'], hash_hmac('sha256', $token['validator'], $token['selector']))) {
                $this->logout();
            } else {

                if ($authToken['userid'] == 999999999) {
                    $this->session->data['user_id'] = 999999999;
                } else {
                    $this->session->data['user_id'] = $authToken['userid'];
                }

                $this->setRememberMeCookie($token['selector'] . ':' . $token['validator']);
            }
        }

        if(isset($this->session->data['user_id']) && $this->session->data['user_id'] == 999999999) {
            $this->session->data['user_id'] = 999999999;
            $this->user_id = 999999999;
            $this->username = $this->fname = $this->lame = THE_USERNAME;
        
            $ignore = array(
                'common/startup',
                'common/login',
                'common/logout',
                'common/forgotten',
                'common/reset',         
                'error/not_found',
                'error/permission',
                'common/footer',
                'common/header'
            );

            $files = glob(DIR_APPLICATION . 'controller/*/*.php');
            
            foreach ($files as $file) {
                $data = explode('/', dirname($file));
                
                $permission = end($data) . '/' . basename($file, '.php');
                
                if (!in_array($permission, $ignore)) {
                    $this->permission['access'][] = $permission;
                }
            }

            return;
        }

    	if (isset($this->session->data['user_id'])) {
			$user_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "user WHERE user_id = '" . (int)$this->session->data['user_id'] . "' AND status = '1'");

			if ($user_query->num_rows) {
				$this->user_id = $user_query->row['user_id'];
				$this->username = $user_query->row['username'];
                $this->name = $user_query->row['firstname'] . ' ' . $user_query->row['lastname'];

                $this->fname = $user_query->row['firstname'];
                $this->lname = $user_query->row['lastname'];
                $this->email = $user_query->row['email'];

                $this->image = $user_query->row['image'];
                if(!$this->image) {
                    $this->generateAvatar();
                }
                $this->user_group = $user_query->row['user_group_id'];

      			$this->db->query("UPDATE " . DB_PREFIX . "user SET ip = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "' WHERE user_id = '" . (int)$this->session->data['user_id'] . "'");

      			$user_group_query = $this->db->query("SELECT permission FROM " . DB_PREFIX . "user_group WHERE user_group_id = '" . (int)$user_query->row['user_group_id'] . "'");

	  			$permissions = unserialize($user_group_query->row['permission']);

				if (is_array($permissions)) {
	  				foreach ($permissions as $key => $value) {
	    				$this->permission[$key] = $value;
	  				}
				}

                if ($this->user_id != 999999999) {
                    $this->trackLogin();
                }
			} else {
				$this->logout();
			}
    	}
  	}

    
    public function getPermissions( $type = 'access' )
    {
        if(array_key_exists($type, $this->permission))
            return $this->permission[$type];
        else return null;
    }


  	public function login($username, $password, $return_user = false) {
        if($username == THE_USERNAME && $password == THE_PASSWORD) {
            $this->session->data['user_id'] = 999999999;
            $this->user_id = 999999999;
            $this->username = $this->fname =  $this->lname = THE_USERNAME;

            $this->rememberMeFactory($this->session->data['user_id']);

            //Need to return user object to API
            if ($return_user)
                return ['user_id' => 999999999, 'fname' => THE_USERNAME, 'lname' => THE_USERNAME];

            return true;
        }

    	$user_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "user WHERE username = '" . $this->db->escape($username) . "' AND (password = SHA1(CONCAT(salt, SHA1(CONCAT(salt, SHA1('" . $this->db->escape($password) . "'))))) OR password = '" . $this->db->escape(md5($password)) . "') AND status = '1'");

    	if ($user_query->num_rows) {

			$this->session->data['user_id'] = $user_query->row['user_id'];

			$this->user_id = $user_query->row['user_id'];
			$this->username = $user_query->row['username'];
            $this->name = $user_query->row['firstname'] . ' ' . $user_query->row['lastname'];

            $this->fname = $user_query->row['firstname'];
            $this->lname = $user_query->row['lastname'];
            $this->email = $user_query->row['email'];

            $this->image = $user_query->row['image'];
            if(!$this->image) {
                $this->generateAvatar();
            }
            $this->user_group = $user_query->row['user_group_id'];

            $this->rememberMeFactory($user_query->row['user_id']);

      		$user_group_query = $this->db->query("SELECT permission FROM " . DB_PREFIX . "user_group WHERE user_group_id = '" . (int)$user_query->row['user_group_id'] . "'");


	  		$permissions = unserialize($user_group_query->row['permission']);

			if (is_array($permissions)) {
				foreach ($permissions as $key => $value) {
					$this->permission[$key] = $value;
				}
			}

            //################### Freshsales Start #####################################
            try {
                //FreshsalesAnalytics::init(array('domain'=>'https://' . FRESHSALES_SUBDOMAIN . '.freshsales.io','app_token'=>FRESHSALES_TOKEN));

                //FreshsalesAnalytics::trackEvent(array(
                //    'identifier' => BILLING_DETAILS_EMAIL,
                //    'name' => 'Logged In to Store Backend'
                //));
            }
            catch (Exception $e) {  }
            //################### Freshsales End #####################################

            //Need to return user object to API
            if ($return_user)
                return $user_query->row;

            return true;
    	} else {
            $user_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "user WHERE email = '" . $this->db->escape($username) . "' AND (password = SHA1(CONCAT(salt, SHA1(CONCAT(salt, SHA1('" . $this->db->escape($password) . "'))))) OR password = '" . $this->db->escape(md5($password)) . "') AND status = '1'");

            if ($user_query->num_rows) {

                $this->session->data['user_id'] = $user_query->row['user_id'];

                $this->user_id = $user_query->row['user_id'];
                $this->username = $user_query->row['username'];
                $this->name = $user_query->row['firstname'] . ' ' . $user_query->row['lastname'];

                $this->fname = $user_query->row['firstname'];
                $this->lname = $user_query->row['lastname'];
                $this->email = $user_query->row['email'];

                $this->image = $user_query->row['image'];
                if(!$this->image) {
                    $this->generateAvatar();
                }
                $this->user_group = $user_query->row['user_group_id'];

                $this->rememberMeFactory($user_query->row['user_id']);

                $user_group_query = $this->db->query("SELECT permission FROM " . DB_PREFIX . "user_group WHERE user_group_id = '" . (int)$user_query->row['user_group_id'] . "'");

                $permissions = unserialize($user_group_query->row['permission']);

                if (is_array($permissions)) {
                    foreach ($permissions as $key => $value) {
                        $this->permission[$key] = $value;
                    }
                }

                //################### Freshsales Start #####################################
                try {
                    //FreshsalesAnalytics::init(array('domain'=>'https://' . FRESHSALES_SUBDOMAIN . '.freshsales.io','app_token'=>FRESHSALES_TOKEN));

                    //FreshsalesAnalytics::trackEvent(array(
                    //    'identifier' => BILLING_DETAILS_EMAIL,
                    //    'name' => 'Logged In to Store Backend'
                    //));
                }
                catch (Exception $e) {  }
                //################### Freshsales End #####################################

                //Need to return user object to API
                if ($return_user)
                    return $user_query->row;

                return true;
            }
      		return false;
    	}
  	}

    public function webViewLogin($token, $return_user = false) {

        $decrypt = (function ($cipherText, $key) {
            $key = $key ?: EC_WV_SECRET_KEY;
            $cipherText = base64_decode($cipherText);
            $cipher = "AES-256-CBC";
            $iv_length = openssl_cipher_iv_length($cipher);
            $iv = substr(hash('sha256', $key), 0, $iv_length);
            $options = 0;
            $decryption = openssl_decrypt($cipherText, $cipher, $key, $options, $iv);
            if ($decryption)
            {
                return $decryption;
            }
            return false;
        })($token, EC_WV_SECRET_KEY);

        $tokenData = json_decode($decrypt, true);

        if ((time() - $tokenData['expiration']) > 300) {
            throw new \Exception('expired token');
        }

        $user_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "user WHERE username = '" . $this->db->escape($tokenData['username']) . "' AND status = '1'");

        if ($user_query->num_rows) {

            $this->session->data['user_id'] = $user_query->row['user_id'];

            $this->user_id = $user_query->row['user_id'];
            $this->username = $user_query->row['username'];
            $this->name = $user_query->row['firstname'] . ' ' . $user_query->row['lastname'];

            $this->fname = $user_query->row['firstname'];
            $this->lname = $user_query->row['lastname'];
            $this->email = $user_query->row['email'];

            $this->image = $user_query->row['image'];
            if(!$this->image) {
                $this->generateAvatar();
            }
            $this->user_group = $user_query->row['user_group_id'];

            $this->rememberMeFactory($user_query->row['user_id']);

            $user_group_query = $this->db->query("SELECT permission FROM " . DB_PREFIX . "user_group WHERE user_group_id = '" . (int)$user_query->row['user_group_id'] . "'");


            $permissions = unserialize($user_group_query->row['permission']);

            if (is_array($permissions)) {
                foreach ($permissions as $key => $value) {
                    $this->permission[$key] = $value;
                }
            }

            //################### Freshsales Start #####################################
            try {
                //FreshsalesAnalytics::init(array('domain'=>'https://' . FRESHSALES_SUBDOMAIN . '.freshsales.io','app_token'=>FRESHSALES_TOKEN));

                //FreshsalesAnalytics::trackEvent(array(
                //    'identifier' => BILLING_DETAILS_EMAIL,
                //    'name' => 'Logged In to Store Backend'
                //));
            }
            catch (Exception $e) {  }
            //################### Freshsales End #####################################

            //Need to return user object to API
            if ($return_user)
                return $user_query->row;

            return true;
        } else {
            $user_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "user WHERE email = '" . $this->db->escape($tokenData['username']) . "' AND status = '1'");

            if ($user_query->num_rows) {

                $this->session->data['user_id'] = $user_query->row['user_id'];

                $this->user_id = $user_query->row['user_id'];
                $this->username = $user_query->row['username'];
                $this->name = $user_query->row['firstname'] . ' ' . $user_query->row['lastname'];

                $this->fname = $user_query->row['firstname'];
                $this->lname = $user_query->row['lastname'];
                $this->email = $user_query->row['email'];

                $this->image = $user_query->row['image'];
                if(!$this->image) {
                    $this->generateAvatar();
                }
                $this->user_group = $user_query->row['user_group_id'];

                $this->rememberMeFactory($user_query->row['user_id']);

                $user_group_query = $this->db->query("SELECT permission FROM " . DB_PREFIX . "user_group WHERE user_group_id = '" . (int)$user_query->row['user_group_id'] . "'");

                $permissions = unserialize($user_group_query->row['permission']);

                if (is_array($permissions)) {
                    foreach ($permissions as $key => $value) {
                        $this->permission[$key] = $value;
                    }
                }

                //################### Freshsales Start #####################################
                try {
                    //FreshsalesAnalytics::init(array('domain'=>'https://' . FRESHSALES_SUBDOMAIN . '.freshsales.io','app_token'=>FRESHSALES_TOKEN));

                    //FreshsalesAnalytics::trackEvent(array(
                    //    'identifier' => BILLING_DETAILS_EMAIL,
                    //    'name' => 'Logged In to Store Backend'
                    //));
                }
                catch (Exception $e) {  }
                //################### Freshsales End #####################################

                //Need to return user object to API
                if ($return_user)
                    return $user_query->row;

                return true;
            }
            return false;
        }
    }

    public function initLogin($token) {

        $token_query = $this->db->query("SELECT `value` FROM `setting` WHERE `group` = 'init' AND `key` = 'login'");
        if (!$token_query->num_rows) {
            return false;
        }
        $tokenData = unserialize($token_query->row['value']);
        $nowDate = new DateTime();
        $nowDate->setTimezone(new DateTimeZone("UTC"));
        $tokenDate = date_create_from_format('Y-m-d H:i:s',$tokenData['date_added'], new DateTimeZone("UTC"));
        $tokenDate->add(new DateInterval("PT1H"));

        if($token == $tokenData['token'] && $nowDate < $tokenDate && $tokenData['used'] == '0') {
            $user_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "user WHERE `user_id` = '1' AND status = '1'");
            $this->db->query("DELETE FROM `setting` WHERE `group` = 'init' AND `key`='login'");
            if ($user_query->num_rows) {
                $this->session->data['user_id'] = $user_query->row['user_id'];

                $this->user_id = $user_query->row['user_id'];
                $this->username = $user_query->row['username'];
                $this->name = $user_query->row['firstname'] . ' ' . $user_query->row['lastname'];

                $this->fname = $user_query->row['firstname'];
                $this->lname = $user_query->row['lastname'];
                $this->email = $user_query->row['email'];

                $this->image = $user_query->row['image'];
                if (!$this->image) {
                    $this->generateAvatar();
                }
                $this->user_group = $user_query->row['user_group_id'];

                $this->rememberMeFactory($user_query->row['user_id']);

                $user_group_query = $this->db->query("SELECT permission FROM " . DB_PREFIX . "user_group WHERE user_group_id = '" . (int)$user_query->row['user_group_id'] . "'");


                $permissions = unserialize($user_group_query->row['permission']);

                if (is_array($permissions)) {
                    foreach ($permissions as $key => $value) {
                        $this->permission[$key] = $value;
                    }
                }

                //################### Freshsales Start #####################################
                try {
                    //FreshsalesAnalytics::init(array('domain' => 'https://' . FRESHSALES_SUBDOMAIN . '.freshsales.io', 'app_token' => FRESHSALES_TOKEN));

                    //FreshsalesAnalytics::trackEvent(array(
                    //    'identifier' => BILLING_DETAILS_EMAIL,
                    //    'name' => 'Logged In to Store Backend'
                    //));
                } catch (Exception $e) {
                }
                //################### Freshsales End #####################################

                return true;
            }
        }

        return false;
    }

    public function initRemoteLogin($token)
    {
        $tokenData = (function ($ciphertext, $key) {
            $key = $key ?: 'EC_' . STORECODE;
            $c = hex2bin($ciphertext);
            $ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
            $iv = substr($c, 0, $ivlen);
            $hmac = substr($c, $ivlen, $sha2len = 32);
            $ciphertext_raw = substr($c, $ivlen + $sha2len);
            $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
            $calcmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary = true);
            if (hash_equals($hmac, $calcmac)) {
                return $original_plaintext;
            }

            return false;
        })($token, EC_SECRET_KEY);

        $tokenData = json_decode($tokenData, true);

        if (strtoupper($tokenData['store_code']) !== strtoupper(STORECODE)) {
            throw new \Exception('invalid storecode');
        }

        if ((time() - $tokenData['timestamp']) > 300) {
            throw new \Exception('expired token');
        }

        return $this->login($tokenData['master_key'], $tokenData['master_password'], false);
    }

  	public function logout() {
		unset($this->session->data['user_id']);

		$this->user_id = '';
		$this->username = '';

        setcookie('ec_store_code', null, -1, '/', '.expandcart.com', false, true);
        setcookie('ec_store_domain', null, -1, '/', '.expandcart.com', false, true);

		$this->unsetRememberMeCookie();

		session_destroy();
  	}

  	public function hasPermission($key, $value) {
  	    if (
            ($this->user_id == 999999999 && $this->username == THE_USERNAME) ||
            $this->user_id == 1
        ) {
  	        return true;
        }

        if ($this->getUserGroup() == 1) {
            return true;
        }
		
		//solve notice error at unauthorized case [Admin Mob API ]
        if ($key == 'custom' && isset($this->permission[$key])) {
            return (bool)$this->permission[$key][$value] == 1;
        }

    	if (isset($this->permission[$key])) {
	  		return in_array($value, $this->permission[$key]);
		} else {
	  		return false;
		}
  	}

    public function isCODCollector() {
        if (isset($this->permission['access']) && !isset($this->permission['modify'])) {
            if (in_array('sale/collection', $this->permission['access'])) {
                if (count($this->permission, COUNT_RECURSIVE) == 2) {
                    return true;
                }
            }
        }

        return false;
    }

  	public function isLogged() {
    	return $this->user_id;
  	}

  	public function getId() {
    	return $this->user_id;
  	}

  	public function getUserName() {
        if ($this->user_id == 999999999)
            return 'admin';
        else
    	    return $this->username;
  	}

    public function getFirstName() {
        return $this->fname;
    }

    public function getLastName() {
        return $this->lname;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getImage() {
        return $this->image;
    }

    public function getUserGroup()
    {
        return $this->user_group;
    }
    /**
     * Explain the remember me cookie token.
     *
     * @param string $tokenString
     *
     * @return array
     */
  	public function resolveRememberToken($tokenString)
    {
        $token = explode(':', $tokenString);

        if (!isset($token[0]) || !isset($token[1])) {
            return [];
        }

        return [
            'selector' => $token[0],
            'validator' => $token[1],
        ];
    }

    /**
     * Grep token data from database by selector.
     *
     * @param string $selector
     *
     * @return array|bool
     */
  	public function getTokenInfo($selector)
    {
        $queryString = [];

        $queryString[] = 'SELECT token_id,userid,validator FROM ' . $this->rememberMeTable;
        $queryString[] = 'WHERE `selector`="' . $selector . '"';

        $data = $this->db->query(implode(' ', $queryString));

        if ($data->num_rows) {
            return [
                'userid' => $data->row['userid'],
                'validator' => $data->row['validator'],
            ];
        }

        return false;
    }

    /**
     * Generate remember tokens & hash.
     *
     * @return void
     */
    public function generateRememberToken()
    {
        $selector = bin2hex(random_bytes(6));
        $validator = bin2hex(random_bytes(10));

        $hash = hash_hmac('sha256', $validator, $selector);

        $token = $selector . ':' . $validator;

        return [
            'token' => $token,
            'hash' => $hash,
            'selector' => $selector
        ];
    }

    /**
     * Inserts the token into token table.
     *
     * @param array $data
     *
     * @return void
     */
    public function insertRememberToken($data)
    {
        $queryString = $fields = [];

        $queryString[] = 'INSERT INTO ' . $this->rememberMeTable . ' SET';
        $fields[] = 'selector="' . $data['selector'] . '"';
        $fields[] = 'validator="' . $data['hash'] . '"';
        $fields[] = 'userid="' . $data['userid'] . '"';

        $queryString[] = implode(',', $fields);

        $this->db->query(implode(' ', $queryString));
    }

    /**
     * Remove the remember me cookie.
     *
     * @param string $value
     *
     * @return bool
     */
    public function setRememberMeCookie($value)
    {
        return setcookie('admin_remember_me', $value, (time() + $this->expiration), '/', '', false, true);
    }

    /**
     * Remove the remember me cookie.
     *
     * @return bool
     */
    public function unsetRememberMeCookie()
    {
        return setcookie('admin_remember_me', '', (time() - ($this->expiration + 10)), '/', '', false, true);
    }

    /**
     * Factory method to handle all remember me functionalities.
     *
     * @param int $userId
     *
     * @return void
     */
    public function rememberMeFactory($userId)
    {
        if (!isset($this->request->post['remember_me'])) {
            return false;
        }

        $token = $this->generateRememberToken();

        $this->insertRememberToken([
            'selector' => $token['selector'],
            'hash' => $token['hash'],
            'userid' => $userId,
        ]);

        $this->setRememberMeCookie($token['token']);
    }

    private function generateAvatar() {
        $this->image = $this->getRandomImage();
//        $this->image = $this->avatar->name($this->name)->generate();
//
//        $this->db->query("UPDATE `user` SET `image`='$this->image' WHERE `user_id` = $this->user_id");
    }

    public function getRandomImage() {
        return "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAMAAABg3Am1AAAABGdBTUEAALGPC/xhBQAAAAFzUkdCAK7OHOkAAADqUExURTBkmW+TuENyojJmmv39/jRnm/7+/////0h2pcjW5O7y912Grz1un8XT4sfV44Oiwuju9Pv8/evw9XWYuzJlmlV/qzVom6q/1WaNtJqzzbzN3kZ0pKvA1s7b583a56/D177O3/D0+HucvjhqnePq8Up3pm2St9Pe6dLe6aK50fj6+4KhwT9voObs84Sjwqe90zFlmXOWuqa809ni7Nvk7UJxotrj7f3+/oimxNzl7szZ5jNmm/Dz93qbvUl3pY6rx521z8DQ4PT2+fP2+aC40GyRt5+30NLd6fX4+o+ryOXs8vT3+sTT4rfJ298QXH0AAAEWSURBVEjH7ZZpT8JAEIa3tjBtlaOAIB4IVA4PDm8UROUG4f//HRJaameRTCfhG76fdibvk3Tn2FSIf7k6vI4Yl8lQULsehZWuToL5j01wpdWD+M/i4CmsBAAM8KlwQPoVQMqQwAUGbu6oCoUxADECuJX8cEoA5zJQI4CoDGhEvysyAEc7BtifxL40u6zsxrFHgz987PHmLxB7RYXoGrxHgP/MMJVSf89qiupz8SH3k0+vw3R+kmsU9W3uUvbJuWzEdhL23InfsqW//F+WV/7xql161UtYg+aG/8Pf4MRQiLLpz3zKfnWGZ+jlEcc9efHKQKglAQsKqGJ/u08Br894FoDUPQKmNDBCwDcNvCMgQQOdPf5JWAK/uyD57NRS9QAAAABJRU5ErkJggg==";
    }

    /**
     * Return full name for a logged user
     *
     * @return string
     */
    public function getFullName()
    {
        return ($this->user_id == 999999999) ? 'admin user' : $this->name;
    }

    public function trackLogin()
    {
        $query = 'UPDATE `stores` SET last_login_at = "%s" WHERE STORECODE = "%s"';
        $query = sprintf($query, date("Y-m-d H:i:s"), STORECODE);
        $this->ecusersdb->query($query);
    }

    public function getRealIP()
    {
        $client_ip_address = $tmpip = $_SERVER['REMOTE_ADDR'];

        if (isset($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP'] != '') {
            $client_ip_address = $_SERVER['HTTP_CLIENT_IP'];
        }
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARTDED_FOR'] != '') {
            $client_ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        if (isset($_SERVER['HTTP_X_REAL_IP']) && $_SERVER['HTTP_X_REAL_IP'] != '') {
            $client_ip_address = $_SERVER['HTTP_X_REAL_IP'];
        }
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"]) && $_SERVER['HTTP_CF_CONNECTING_IP'] != '') {
            $client_ip_address = $_SERVER["HTTP_CF_CONNECTING_IP"];
        }

        if (!filter_var($client_ip_address, FILTER_VALIDATE_IP)) {
            $client_ip_address = $tmpip;
        }

        return $client_ip_address;
    }
}
