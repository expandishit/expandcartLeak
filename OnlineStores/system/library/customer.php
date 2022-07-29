<?php
class Customer {
	private $customer_id;
    private $expand_id;
    private $updated_at;
    private $expand_updated_at;
	private $firstname;
	private $lastname;
	private $company;
	private $email;
	private $telephone;
	private $fax;
	private $newsletter;
	private $customer_group_id;
	private $address_id;
	private $approved;
	private $language_id;
    private $wishlist;
    private $date_added;
    private $customerGroupInfo;
	private $dob; //Date of birth
	protected $registry;
    protected $load;
    public $isCustomerAllowedToView_products= true;
    public $isCustomerAllowedToAdd_cart= true;

    public function __construct($registry)
    {
        $this->registry = $registry;
        $this->config = $registry->get('config');
        $this->db = $registry->get('db');
        $this->request = $registry->get('request');
        $this->session = $registry->get('session');
        $this->load = $registry->get('load');

        $customer_id = $this->resolveCustomerId();

        $customer_query = $customer_id ? $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE customer_id = '" . (int)$customer_id . "'") : null;

        if ($customer_query && $customer_query->num_rows) {
            $shouldLogOut =  (int)$customer_query->row['status'] === 0 || (int)$customer_query->row['force_logout'] === 1;

            if (!$shouldLogOut) {
                $this->customer_id = (int)$customer_query->row['customer_id'];
                $this->expand_id = (int)$customer_query->row['expand_id'];
                $this->updated_at = $customer_query->row['updated_at'];
                $this->expand_updated_at = $customer_query->row['expand_updated_at'];
                $this->firstname = $customer_query->row['firstname'];
                $this->lastname = $customer_query->row['lastname'];
                $this->company = $customer_query->row['company'];
                $this->email = $customer_query->row['email'];
                $this->telephone = $customer_query->row['telephone'];
                $this->fax = $customer_query->row['fax'];
                $this->newsletter = $customer_query->row['newsletter'];
                $this->customer_group_id = $customer_query->row['customer_group_id'];
                $this->address_id  = $customer_query->row['address_id'];
                $this->dob  = $customer_query->row['dob'];
                $this->wishlist  = unserialize($customer_query->row['wishlist']);
                $this->date_added = $customer_query->row['date_added'];
                $this->setApprovalStatus($customer_query->row['approved']);
                $this->language_id = $customer_query->row['language_id'];
                $this->customerGroupInfo = $this->getCustomerGroupInfo($this->getCustomerGroupId());
                $this->isCustomerAllowedToView_products = $this->isCustomerAllowedToViewProducts();
                $this->isCustomerAllowedToAdd_cart = $this->isCustomerAllowedToAddToCart();


                // $this->db->query("UPDATE " . DB_PREFIX . "customer SET cart = '" . $this->db->escape(isset($this->session->data['cart']) ? serialize($this->session->data['cart']) : '') . "', wishlist = '" . $this->db->escape(isset($this->session->data['wishlist']) ? serialize($this->session->data['wishlist']) : '') . "', ip = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "' WHERE customer_id = '" . (int)$this->customer_id . "'");

                $this->setActs($customer_query->row);

                $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer_ip WHERE customer_id = '" . $this->customer_id . "' AND ip = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "'");

                if (!$query->num_rows) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "customer_ip SET customer_id = '" . $this->customer_id . "', ip = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "', date_added = NOW()");
                }


                //Buyer Subscription Plan App - Downgrade subscription plan to free-plan if expired
                $buyer_subscription_plan_is_installed = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'buyer_subscription_plan'")->num_rows > 0 && $this->config->get('buyer_subscription_plan_status');

                if ($buyer_subscription_plan_is_installed && $customer_query->row['buyer_subscription_id']) {

                    $model_account_subscription      = $this->load->model('account/subscription', ['return' => true]);

                    $buyer_subscription_plan         = $model_account_subscription->getCustomerSubscriptionPlan($this->customer_id);

                    if ($model_account_subscription->checkIfSubscriptionExpired($buyer_subscription_plan, $this->customer_id)) {
                        $this->db->query("UPDATE " . DB_PREFIX . "customer SET buyer_subscription_id = NULL WHERE customer_id = " . (int)$this->customer_id);
                    }
                }
            } else {
                $this->logout();
                $this->session->data['cart'] = [];
                unset($this->session->data['wishlist']);
            }
        } else {
            $this->logout();
        }
    }
		
  	public function login($email_or_phonenumber, $password, $is_email_registration = true,$override = false)
    {

		$queryString = 'SELECT * FROM ' . DB_PREFIX . 'customer';
		if($is_email_registration)
        	$queryString .= ' WHERE LOWER(email)="' . $this->db->escape(utf8_strtolower($email_or_phonenumber)) . '"';
		else
			$queryString .= ' WHERE telephone ="' . $this->db->escape(utf8_strtolower($email_or_phonenumber)) . '"';
		
		if (!$override) {
		    $password = $this->db->escape($password);
		    $md5_password = $this->db->escape(md5($password));
            $queryString .= " AND (
            password = SHA1(CONCAT(salt, SHA1(CONCAT(salt, SHA1('$password'))))) 
            OR password = '{$md5_password}')";
        }

        $queryString .= ' AND status=1';

        $customer_query = $this->db->query($queryString);

        if ($customer_query->num_rows) {


            $customer = $customer_query->row;


            unset($customer_query);

            if ($customer['approved'] == 0) {
                return false;
            }
            // get customer group data
            $queryStringCustomerGroup = [];
            $queryStringCustomerGroup[] = 'SELECT * FROM ' . DB_PREFIX . 'customer_group ';
            $queryStringCustomerGroup[] = 'WHERE customer_group_id = "'.$customer['customer_group_id'].'" ';
            $customerGroupData = $this->db->query(implode(' ', $queryStringCustomerGroup));
             if($customerGroupData->num_rows)
             {
                 // check if admin choose customer must activate his account
                 if($customerGroupData->row['customer_verified'] == 1 && ($customerGroupData->row['email_activation'] == 1 || $customerGroupData->row['sms_activation'] == 1 || $customerGroupData->row['approval'] == 1  ) ){
                     $queryStringAccountActivation = [];
                     $queryStringAccountActivation[] = 'SELECT * FROM ' . DB_PREFIX . 'customer_activation';
                     $queryStringAccountActivation[] = 'WHERE customer_id="' . $customer['customer_id'] . '"';
                     $queryStringAccountActivation[] = 'AND activation_status="0"';

                     $activationData = $this->db->query(implode(' ', $queryStringAccountActivation));
                     if($activationData->num_rows)
                     {
                         if($activationData->row['activation_status'] == 0){
                             return ['activation_status'=>0,"activation_type"=>$activationData->row['activation_type'],"customer_id"=>$customer['customer_id']];
                         }
                     }
                 }
             }



            $this->setApprovalStatus($customer['approved']);

            $this->session->data['customer_id'] = $customer['customer_id'];

            if ($customer['cart'] && is_string($customer['cart'])) {
                $cart = unserialize($customer['cart']);

                foreach ($cart as $key => $value) {
                    if (!array_key_exists($key, $this->session->data['cart'])) {
                        $this->session->data['cart'][$key] = $value;
                    } else {
                        $this->session->data['cart'][$key] += $value;
                    }
                }
            }

            if ($customer['wishlist'] && is_string($customer['wishlist'])) {
                if (!isset($this->session->data['wishlist'])) {
                    $this->session->data['wishlist'] = array();
                }

                $wishlist = unserialize($customer['wishlist']);
                $this->wishlist = $wishlist;

                foreach ($wishlist as $product_id) {
                    if (!in_array($product_id, $this->session->data['wishlist'])) {
                        $this->session->data['wishlist'][] = $product_id;
                    }
                }
            }

            $this->customer_id = $customer['customer_id'];
            $this->firstname = $customer['firstname'];
            $this->lastname = $customer['lastname'];
            $this->company = $customer['company'];
            $this->email = $customer['email'];
            $this->telephone = $customer['telephone'];
            $this->fax = $customer['fax'];
            $this->newsletter = $customer['newsletter'];
            $this->customer_group_id = $customer['customer_group_id'];
            $this->address_id = $customer['address_id'];
            $this->date_added = $customer['date_added'];
            $this->dob = $customer['dob'];

            $this->db->query("UPDATE " . DB_PREFIX . "customer SET ip = '" . $this->db->escape($this->getClientRealIP()) . "' WHERE customer_id = '" . (int)$this->customer_id . "'");

            return true;
        } else {
            return false;
        }
    }

    /**
     * attemptLogin Loginjs api
     *
     * @param array $condition
     * @param integer $expand_id
     * @return bool 
     */
    public function attemptLogin(array $condition, int $expand_id = null)
    {
        list($key, $value) = $condition;
        
        $value = $this->db->escape(utf8_strtolower($value));

        if (is_null($expand_id)){
            $queryString = sprintf("SELECT * FROM %s WHERE LOWER(%s) = '%s' AND expand_id is null AND status = 1", DB_PREFIX.'customer', $key, $value);
        }else{
            $queryString = sprintf("SELECT * FROM %s WHERE LOWER(%s) = '%s' AND expand_id = %s AND status = 1", DB_PREFIX.'customer', $key, $value, $expand_id);
        }

        $customer_query = $this->db->query($queryString);

        if (!$customer_query->num_rows) {
            return false;
        }

        $customer = $customer_query->row;

        unset($customer_query);

        // if ($customer['approved'] == 0) {
        //     return false;
        // }

        // get customer group data
        $queryStringCustomerGroup = [];
        $queryStringCustomerGroup[] = 'SELECT * FROM ' . DB_PREFIX . 'customer_group ';
        $queryStringCustomerGroup[] = 'WHERE customer_group_id = "' . $customer['customer_group_id'] . '" ';
        $customerGroupData = $this->db->query(implode(' ', $queryStringCustomerGroup));
        if ($customerGroupData->num_rows) {
            // check if admin choose customer must activate his account
            if ($customerGroupData->row['customer_verified'] == 1 && ($customerGroupData->row['email_activation'] == 1 || $customerGroupData->row['sms_activation'] == 1 || $customerGroupData->row['approval'] == 1)) {
                $queryStringAccountActivation = [];
                $queryStringAccountActivation[] = 'SELECT * FROM ' . DB_PREFIX . 'customer_activation';
                $queryStringAccountActivation[] = 'WHERE customer_id="' . $customer['customer_id'] . '"';
                $queryStringAccountActivation[] = 'AND activation_status="0"';

                $activationData = $this->db->query(implode(' ', $queryStringAccountActivation));
                if ($activationData->num_rows) {
                    // if ($activationData->row['activation_status'] == 0) {
                    //     return ['activation_status' => 0, "activation_type" => $activationData->row['activation_type'], "customer_id" => $customer['customer_id']];
                    // }
                }
            }
        }

        // push customer to session
        $this->session->data['expand_id']   = (int)$expand_id;
        $this->session->data['customer_id'] = (int)$customer['customer_id'];

        $this->setActs($customer);

        $this->customer_id = (int)$customer['customer_id'];
        $this->firstname = $customer['firstname'];
        $this->lastname = $customer['lastname'];
        $this->company = $customer['company'];
        $this->email = $customer['email'];
        $this->telephone = $customer['telephone'];
        $this->fax = $customer['fax'];
        $this->newsletter = $customer['newsletter'];
        $this->customer_group_id = $customer['customer_group_id'];
        $this->address_id = $customer['address_id'];
        $this->approved = $customer['approved'];
        $this->expand_id = (int)$expand_id;

        $this->db->query("UPDATE " . DB_PREFIX . "customer SET ip = '" . $this->db->escape($this->getClientRealIP()) . "', force_logout = '0', expand_id = '" . (int)$expand_id . "' WHERE customer_id = '" . (int)$this->customer_id . "'");
        
        // remember customer
        $this->rememberMeFactory($this->customer_id);
        return true;
    }
    
    public function logout() {
        $queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");

        if($queryMultiseller->num_rows) {
            unset($this->session->data['multiseller']);
        }

        $this->updateActs();

        $this->expand_id = null;
        $this->updated_at = null;
        $this->expand_updated_at = null;
        $this->customer_id = '';
        $this->firstname = '';
        $this->lastname = '';
        $this->company = '';
        $this->email = '';
        $this->telephone = '';
        $this->fax = '';
        $this->newsletter = '';
        $this->customer_group_id = '';
        $this->address_id = '';
        $this->customerGroupData= '';
        
		$this->unsetRememberMeCookie();
        
        unset(
            $this->session->data['customer_id'],
            $this->session->data['expand_id'],
            $this->session->data['current_step'] // 3steps checkout reset current step
            //$this->session->data['wishlist'],
            //$this->session->data['cart']
        );
    }

    public function isLogged() {
        return $this->customer_id;
    }

    public function getId() {
        return $this->customer_id;
    }
    
    public function getExpandId()
    {
        return $this->expand_id;
    }
    
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }
    
    public function getExpandUpdatedAt()
    {
        return $this->expand_updated_at;
    }

    public function getFirstName() {
        return $this->firstname;
    }

    public function getLastName() {
        return $this->lastname;
    }
    
    public function getCompany() {
        return $this->company;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getTelephone() {
        return $this->telephone;
    }

    public function getFax() {
        return $this->fax;
    }

    public function getNewsletter() {
        return $this->newsletter;
    }

    public function getCustomerGroupId() {
        return $this->customer_group_id;
    }

    public function getAddressId() {
        return $this->address_id;
    }
    
    public function setAddressId($address_id = null)
    {
        $this->address_id = $address_id;
        return $this;
    }

    public function getWishList() {
        return $this->wishlist;
    }

    public function getDateAdded() {
        return $this->date_added;
    }

    public function getDOB() {
        return $this->dob;
    }

    public function getBalance() {
        $query = $this->db->query("SELECT SUM(amount) AS total FROM " . DB_PREFIX . "customer_transaction WHERE customer_id = '" . (int)$this->customer_id . "'");

        return $query->row['total'];
    }

    public function getRewardPoints() {

        /**
         * Check if customer is a visitor or logged customer to get reward points of customer
         * if he/she is logged customer otherwise he/she don't have reward points.
         */
        if(isset($this->customer_id) && !empty($this->customer_id)){
            $queryRewardPointInstalled = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'reward_points_pro'");

            if($queryRewardPointInstalled->num_rows) {
                $query1 = $this->db->query("SELECT SUM(points) AS total FROM " . DB_PREFIX . "customer_reward WHERE status = 1 AND customer_id = '" . (int)$this->customer_id . "'");

                return $query1->row['total'];
            }
            else {
                $query = $this->db->query("SELECT SUM(points) AS total FROM " . DB_PREFIX . "customer_reward WHERE customer_id = '" . (int)$this->customer_id . "'");

                return $query->row['total'];
            }
        }
        else{
            return false;
        }
    }

    public function getUpline()
    {
        if (!$this->customer_id) {
            return null;
        }

        $this->load->model('network_marketing/downlines');
        $this->load->model('network_marketing/settings');

        $settings = $this->registry->get('model_network_marketing_settings')->getSettings();

        if ($settings['nm_status'] != 1) {
            return null;
        }

        $referral = $this->registry->get('model_network_marketing_downlines')->getReferralByCustomerId(
            $this->customer_id
        );

        if (!$referral) {
            return false;
        }


        $upLine = $this->registry->get('model_network_marketing_downlines')->generateUpline($referral);

        return $upLine;
    }

    /**
     * Get customer group using a customer group id.
     *
     * @param int $groupId
     *
     * @return array|bool
     */
    private function getCustomerGroupInfo($groupId)
    {
        $customerGroup = $this->load->model('account/customer_group', ['return' => true]);

        return ($customerGroup->getCustomerGroup($groupId) ?: false);
    }

    /**
     * Set the status of the user approve status.
     *
     * @param int $status
     *
     * @return Customer $this
     */
    public function setApprovalStatus($status)
    {
        $this->approved = $status;

        return $this;
    }

    /**
     * Get customer approved status.
     *
     * @return int
     */
    public function getApprovalStatus()
    {
        return $this->approved;
	}
	
	public function getLanguageId()
	{
		return $this->language_id;
	}

    public function loginByCustomerArray($customerArr){
        $this->session->data = $customerArr;
        $this->customer_id = $customerArr['customer_id'];
    }

    public function isCustomerAllowedToViewPrice()
    { 
       $config_customer_price = $this->config->get('config_customer_price'); 

        if($this->isLogged())
        {

          if(strpos($this->customerGroupInfo['permissions'],'hidePrice') == false) return true; else return false; 
        }
        else
        { 

          if($config_customer_price)  return false; else return true; 
        }

    }
    public function isCustomerAllowedToAddToCart()
    {

       if(strpos($this->customerGroupInfo['permissions'],'hideCart') != false) return false; else  return true;          
    }
    
    public function isCustomerAllowedToViewProducts()
    {
        if(strpos($this->customerGroupInfo['permissions'],'hideProductsLinks') != false) return false; else return true; 
    }
    
    /**
     * The token cookie expiration period 90 days converted to seconds.
     */
    private const REMEMBER_SESSION_EXPIRE_AT = 7776000;

    /**
     * The remember cookie name.
     */
    private const REMEMBER_SESSION_NAME = 'remember_me';

    /**
     * The remember tokens table name.
     */
    private const REMEMBER_TABLE_NAME = DB_PREFIX . 'remember_tokens_customer';

    /**
     * resolveCustomerId
     *
     * @return bool|integer
     */
    private function resolveCustomerId()
    {
        // check session
        if (isset($this->session->data['customer_id'])) return $this->session->data['customer_id'];

        // check cookie
        if (!isset($this->request->cookie[self::REMEMBER_SESSION_NAME])) return false;

        $token = $this->resolveRememberToken($this->request->cookie[self::REMEMBER_SESSION_NAME]);

        if (!isset($token['validator']) || !isset($token['selector'])) return false;

        $authToken = $this->getTokenInfo($token['selector']);

        if (!hash_equals($authToken['validator'], hash_hmac('sha256', $token['validator'], $token['selector']))) return false;

        $this->session->data['customer_id'] = (int)$authToken['customer_id'];

        return (int)$authToken['customer_id'];
    }

    /**
     * Add remember me token to db & cookie.
     *
     * @param integer $customer_id
     * @return void
     */
    public function rememberMeFactory(int $customer_id): void
    {
        $token = $this->generateRememberToken();

        $this->insertRememberToken([
            'selector' => $token['selector'],
            'hash' => $token['hash'],
            'customer_id' => $customer_id,
        ]);

        $this->setRememberMeCookie($token['token']);
    }

    /**
     * Generate remember tokens & hash.
     *
     * @return array
     */
    private function generateRememberToken(): array
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
    private function insertRememberToken($data)
    {
        $queryString = $fields = [];

        $queryString[] = 'INSERT INTO ' . self::REMEMBER_TABLE_NAME . ' SET';
        $fields[] = 'customer_id="' . $data['customer_id'] . '"';
        $fields[] = 'selector="' . $data['selector'] . '"';
        $fields[] = 'validator="' . $data['hash'] . '"';
        $fields[] = 'expires="' . date('Y-m-d H:i:s.u', strtotime("+" . self::REMEMBER_SESSION_EXPIRE_AT . " seconds")) . '"';

        $queryString[] = implode(',', $fields);

        $this->db->query(implode(' ', $queryString));
    }

    /**
     * Set the remember me cookie.
     *
     * @param string $value
     *
     * @return bool
     */
    public function setRememberMeCookie($value)
    {
        return setcookie(self::REMEMBER_SESSION_NAME, $value, (time() + self::REMEMBER_SESSION_EXPIRE_AT), '/', '', false, true);
    }

    /**
     * Explain the remember me cookie token.
     *
     * @param string $tokenString
     *
     * @return array
     */
    private function resolveRememberToken($tokenString): array
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

        $queryString[] = 'SELECT token_id,customer_id,validator FROM ' . self::REMEMBER_TABLE_NAME;
        $queryString[] = 'WHERE `selector`="' . $selector . '" AND `expires` > NOW()';

        $data = $this->db->query(implode(' ', $queryString));
        
        if ($data->num_rows) {
            return [
                'customer_id' => $data->row['customer_id'],
                'validator' => $data->row['validator'],
            ];
        }

        return false;
    }

    /**
     * Remove the remember me cookie.
     *
     * @return bool
     */
    public function unsetRememberMeCookie()
    {
        return setcookie(self::REMEMBER_SESSION_NAME, '', (time() - (self::REMEMBER_SESSION_EXPIRE_AT + 10)), '/', '', false, true);
    }
    
    /**
     * Merge and store the cart and wishes 
     * of the guest and customer
     *
     * @param array $customer
     * @return void
     */
    public function setActs(array $customer)
    {
        $tempCart = $tempWishes = [];

        $gCart = $this->session->data['cart'] ?: [];
        $cCart = ($customer['cart'] && \is_string($customer['cart'])) 
            ? \unserialize($customer['cart']) 
            : [];
            
        if (!\is_array($gCart)) $gCart = [];
        if (!\is_array($cCart)) $cCart = [];
            
        foreach($gCart as $k => $v) $tempCart[$k] = (int)$v;
        
        foreach($cCart as $k => $v) $tempCart[$k] = (int)$v;
        
        $gWishes = $this->session->data['wishlist'] ?: [];
        $cWishes = ($customer['wishlist'] && \is_string($customer['wishlist'])) 
            ? \unserialize($customer['wishlist']) 
            : [];
        
        if (!\is_array($gWishes)) $gWishes = [];
        if (!\is_array($cWishes)) $cWishes = [];
        
        $tempWishes = $gWishes + $cWishes;
        
        $this->session->data['cart'] = $tempCart;
        $this->session->data['wishlist'] = $tempWishes;
        
        $this->updateActs();
    }
    
    public function updateActs()
    {
        if (!$this->isLogged()) return;
        
        $cart = !empty($this->session->data['cart']) ? serialize($this->session->data['cart']) : '';
        $wishes = !empty($this->session->data['wishlist']) ? serialize($this->session->data['wishlist']) : '';
        
        $this->db->query("UPDATE " . DB_PREFIX . "customer SET cart = '" . $this->db->escape($cart) . "', wishlist = '" . $this->db->escape($wishes) . "' WHERE customer_id = '" . (int)$this->customer_id . "'");
    }

    /**
     * Get the client's real IP if there is a proxy server
     */
    public function getClientRealIP()
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

    public function getRealIP()
    {
        return $this->getClientRealIP();
    }
}
