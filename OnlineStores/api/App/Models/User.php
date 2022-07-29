<?php

namespace Api\Models;

class User extends ParentModel
{
    /**
     * The api_clients table name string
     *
     * @var string
     */
    protected $clientsTable = DB_PREFIX . 'api_clients';

    /**
     * The user table name string
     *
     * @var string
     */
    protected $userTable = DB_PREFIX . 'user';

    /**
     * Retrieve the api client by it's secret key and client id
     *
     * @param string $secret
     * @param int $clientId
     *
     * @return array|bool
     */
    public function getUser($secret, $clientId)
    {
        $queryString = [];
        $queryString[] = 'SELECT * FROM `' . $this->clientsTable . '` WHERE';
        $queryString[] = 'client_status = 1 AND';
        $queryString[] = 'client_secret = "' . $secret . '" AND';
        $queryString[] = 'client_id = "' . $clientId . '"';

        $data = $this->client->query(implode(' ', $queryString));

        if ($data->num_rows) {
            return $data->row;
        }

        return false;
    }

    /**
     * login by username and password
     *
     * @param array $data
     *
     * @return array
     */
    public function logIn($data)
    {
        $registry = $this->container['registry'];
        $db       = $this->container['db'];

        //login action
        $user = $registry->get('user')->login($data['username'], $data['password'], true);

        if(!$user)
            return false;

        $user_data = [
            'user_id' => $user['user_id'],
            'user_group_id' => $user['user_group_id'],
            'username' => $user['username'],
            'firstname' => $user['firstname'],
            'lastname' => $user['lastname'],
            'email' => $user['email'],
            'image' => $user['image'],
            'status' => $user['status']
        ];

        $queryString = [];
        $queryString[] = 'SELECT * FROM `' . $this->clientsTable . '` WHERE';
        $queryString[] = 'client_status = 1 AND';
        $queryString[] = 'target = "mobile_app"';
        $client_data = $db->query(implode(' ', $queryString));

        //check if mobile app client_id and secret exists
        if ($client_data->num_rows) {
            $user_data['client_id'] = $client_data->row['client_id'];
            $user_data['client_secret'] = $client_data->row['client_secret'];
        }else{
            //Generate mobile app client_id and secret
            $load = $this->container['loader'];
            $load->model('api/clients');
            $clientId = $registry->get('model_api_clients')->generateClientId();
            $secretKey = $registry->get('model_api_clients')->generateSecretKey($clientId);

            $queryString = $fields = [];
            $queryString[] = 'INSERT INTO `' . $this->clientsTable . '` SET';
            $fields[] = 'client_id="' . $clientId . '"';
            $fields[] = 'client_secret="' . $secretKey . '"';
            $fields[] = 'target="mobile_app"';
            $fields[] = 'client_status="1"';
            $queryString[] = implode(',', $fields);

            $db->query(implode(' ', $queryString));

            if ($db->getLastId()){
                $user_data['client_id'] = $clientId;
                $user_data['client_secret'] = $secretKey;
            }
        }

        return $user_data;
    }

    /**
     * get user by dynamic col
     *
     * @param string $email
     *
     * @return bool
     */
    public function getUserByCol($val, $col)
    {
        $db       = $this->container['db'];

        $queryString = [];
        $queryString[] = 'SELECT user_id, username, firstname, lastname, status FROM `' . $this->userTable . '` WHERE';
        $queryString[] = $col.' = "' . $val . '"';

        $data = $db->query(implode(' ', $queryString));

        if ($data->num_rows) {
            return $data->row;
        }
        return false;
    }

    /**
     * update user security code
     *
     * @param string $email
     * @param string $security_code
     *
     * @return bool
     */
    public function updateSecurity($email, $security_code)
    {
        $db       = $this->container['db'];
        $data = $db->query("UPDATE `" . $this->userTable . "` SET security_code='".$db->escape($security_code)."' WHERE email ='".$db->escape($email)."'");

        if ($data) {
            return true;
        }
        return false;
    }

    /**
     * update user security code
     *
     * @param string $email
     * @param string $code
     *
     * @return bool
     */
    public function updateCode($email, $code)
    {
        $db       = $this->container['db'];
        $data = $db->query("UPDATE `" . $this->userTable . "` SET code='".$db->escape($code)."' WHERE email ='".$db->escape($email)."'");

        if ($data) {
            return true;
        }
        return false;
    }

    /**
     * change user password
     *
     * @param array $data['password', 'user_id', 'security_code']
     *
     * @return bool
     */
    public function changePassword($data)
    {
        if(isset($data['password']) && isset($data['user_id']) && isset($data['security_code'])){
            $db       = $this->container['db'];
            $chn = $db->query("UPDATE `" . $this->userTable . "` SET salt = '" . $db->escape($salt = substr(md5(uniqid(rand(), true)), 0, 9)) . "', password='".$db->escape(sha1($salt . sha1($salt . sha1($data['password']))))."', security_code='' WHERE user_id ='".$data['user_id']."' AND security_code='".$data['security_code']."'");

            if ($chn) {
                return true;
            }
        }
        return false;
    }

	public function profile($id)
    {
        $registry = $this->container['registry'];
        $db       = $this->container['db'];
		$session= $registry->get('session');
		
		/*if(!$session->data['user_id'])
			return false;*/
		
        $queryString = [];
		
        $queryString[] = 'SELECT u.user_id,u.username,u.firstname,u.lastname,u.email,u.user_group_id,ug.name as group_name,u.image,u.status FROM ' . $this->userTable . ' as u  
		LEFT JOIN '.DB_PREFIX.'user_group ug on ug.user_group_id = u.user_group_id WHERE';
		
        $queryString[] = 'u.user_id = "' . (int)$id . '"';
		
         $data = $db->query(implode(' ', $queryString));
		
		if ($data->num_rows) {
            return $data->row;
        }
        return false;
    }
	
	

}
