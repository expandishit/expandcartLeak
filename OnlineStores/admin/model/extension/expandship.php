<?php

use Api\Models\ParentModel;
use Api\Models\Token;

class ModelExtensionExpandShip extends Model
{
    //api base url
//     const BASE_URL = "127.0.0.1:8060/api/v1/";
//     const BASE_URL = "127.0.0.1/expandship/api/v1/";
     const BASE_URL = EXPANDSHIP[EXPANDSHIP['env']]['host'].'/api/v1/';
     const APP_NAME = 'expandship';

    //user status
    private const ACTIVE = "1";
    private const SUSPEND = "0";


    /////////////////////////////////////////////////////////////
    ///                     Configurations                      //
    /////////////////////////////////////////////////////////////
    public function getSettings()
    {
        return $this->config->get(self::APP_NAME) ?? [];
    }

    public function isInstalled(): bool
    {
        return Extension::isInstalled(self::APP_NAME);
    }

    public function install($data)
    {
        $this->load->model('setting/extension');
        $this->load->model('setting/setting');
        $this->load->model('user/user_group');

        if (!$this->isInstalled()) {
            $this->model_setting_extension->install('shipping', self::APP_NAME);
            $this->model_user_user_group->addPermission($this->user->getId(), 'access', 'shipping/' . self::APP_NAME);
            $this->model_user_user_group->addPermission($this->user->getId(), 'modify', 'shipping/' . self::APP_NAME);
        }
        //add expandship settings
        $this->model_setting_setting->insertUpdateSetting(self::APP_NAME, [
            self::APP_NAME => [
                'status' => self::SUSPEND,
                'merchant' => $data->merchant,
                'token' => $data->merchant->token,
            ],
        ]);

    }

    public function updateSettings($data): bool
    {
        $this->load->model('setting/setting');
        $settings = $this->getSettings();

        foreach ($data as $key => $value)
            $settings[$key] = $value;
        $this->model_setting_setting->insertUpdateSetting(self::APP_NAME, [self::APP_NAME => $settings]);
        return true;
    }

    public function uninstall($store_id = 0, $group = self::APP_NAME)
    {
        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting($group);
    }

    function createApiToken() : string
    {

        //create custom client for api
        $this->load->model("api/clients");

        //get current expandship client
        $expandship_client = $this->model_api_clients->getClientByTarget(self::APP_NAME);

        //create new expandship api client if not exist
        if(!$expandship_client) {
            $clientId   = $this->model_api_clients->generateClientId();
            $secretKey  = $this->model_api_clients->generateSecretKey($clientId);
            $id         = $this->model_api_clients->storeCustomClient($clientId, $secretKey, 1, self::APP_NAME);
        }else{
            $id = $expandship_client['id'];
        }

        //store new token
        $access_token = $this->generateToken(25);
        $this->storeToken($access_token, $id, 99999999999);
        return $access_token;
    }

    /******* Generate new token string ********/
    function generateToken($length): string
    {
        return bin2hex(openssl_random_pseudo_bytes($length));
    }

    /********* Insert the given token into the database **********/
    public function storeToken($token, $clientId, $expiration=null): void
    {
        $queryString = $fields = [];
        $queryString[] = 'INSERT INTO `'.DB_PREFIX . 'api_tokens` SET';
        $fields[] = 'client_id="' . $clientId . '"';
        $fields[] = 'token="' . $token . '"';
        $fields[] = 'expiration="' . (time() + ($expiration ?? $this->expiration) ) . '"';
        $queryString[] = implode(',', $fields);
        $this->db->query(implode(' ', $queryString));
    }


    /////////////////////////////////////////////////////////////
    ///                     Authentication                      //
    /////////////////////////////////////////////////////////////


    /***************** get data required for registration ***************/
    public function getRegisterDate():stdClass
    {
        $url = self::BASE_URL . 'register';
        return  $this->sendRequest('GET', $url);
    }

    /***************** get application brief ***************/
    public function getAppBrief():stdClass
    {
        $url = self::BASE_URL . 'brief';
        return  $this->sendRequest('GET', $url);
    }


    /***************** get all states in country  ***************/
    public function getStatesByCountry($country_id): array
    {
        $url = self::BASE_URL . 'states-by-country/'.$country_id;
        $response =   $this->sendRequest('GET', $url);
        return $response->states ??[];
    }

    /***************** get all cities in state***************/
    public function getCitiesByState($state_id): array
    {
        $url = self::BASE_URL . 'cities-by-state/'.$state_id;
        $response =   $this->sendRequest('GET', $url);
        return $response->cities ??[];
    }


    /***************** Register new merchant ***************/
    public function register($data = [])
    {
        //update merchant in expandship with expandcart api token
        $data['expand_token'] = $this->createApiToken();

        $url = self::BASE_URL . 'register';
        $response = $this->sendRequest('POST', $url, $data);

        if ($response->status_code != 200)
            return $response;

        //register merchant and install app if merchant has added to expandship dashboard
        if ($this->isInstalled()) {
            $this->updateSettings([
                'status' => self::SUSPEND,
                'merchant' => $response->merchant,
                'token' => $response->merchant->token,
            ]);
        }
        else {
            $this->install($response);
        }

        /***************** Start ExpandCartTracking #347759  ****************/

        // send mixpanel register expandship  event
        $this->load->model('setting/mixpanel');
        $this->model_setting_mixpanel->trackEvent('Complete ExpandShip Onboarding');

        // send amplitude register expandship event
        $this->load->model('setting/amplitude');
        $this->model_setting_amplitude->trackEvent('Complete ExpandShip Onboarding');

        /***************** End ExpandCartTracking #347759  ****************/


        return $response;
    }

    /***************** Login ***************/
    public function login($data = [])
    {
        $url = self::BASE_URL . 'login';
        $response = $this->sendRequest('POST', $url, $data);

        if ($response->status_code != 200)
            return $response;

        //update current setting
        $this->updateSettings(['merchant' => $response->merchant]);

        return $response;
    }

    /***************** Send Code ***************/
    public function sendCode($data = [])
    {
        $url = self::BASE_URL . 'send-code';
        return $this->sendRequest('POST', $url, $data);
    }

    /***************** Check Code ***************/
    public function checkCode($data = [])
    {
        $url = self::BASE_URL . 'check-code';
        return $this->sendRequest('POST', $url, $data);

    }

    /////////////////////////////////////////////////////////////
    ///                     Main Settings                      //
    /////////////////////////////////////////////////////////////


    /***************** get Home page data and balance history ***************/
    public function getIndexPageData():stdClass
    {
        $url = self::BASE_URL . 'home';
        return $this->sendRequest('GET', $url,[],$this->getSettings()['token']);

    }

    /***************** Get merchant Info ***************/
    public function getMerchantInfo():stdClass
    {
        $url = self::BASE_URL . 'profile';
        return $this->sendRequest('GET', $url,[],$this->getSettings()['token']);

    }

    /***************** Update merchant data ***************/
    public function updateMerchantProfile($data=[]):stdClass
    {

        $data = array_merge(['order_statuses'=>$this->getOrderStatusMapping()],$data);
        $url = self::BASE_URL . 'profile';
        return $this->sendRequest('PUT', $url,$data,$this->getSettings()['token']);
    }

    private function getOrderStatusMapping(){
        return [
            'approved'  => $this->config->get('config_order_status_id') ?? 1,
            'picked'    => $this->config->get('config_order_cod_status_id') ?? 15,
            'shipped'   => $this->config->get('config_order_shipped_status_id') ?? 3,
            'delivered' => $this->config->get('config_complete_status_id') ?? 5,
            'canceled'  => $this->config->get('config_cancelled_status_id') ?? 7,
            'returned'  => $this->config->get('config_return_status_id') ?? 2,
            'reversed'  => $this->config->get('config_return_status_id') ?? 2,
        ];
    }

     /////////////////////////////////////////////////////////////
    ///                 Payment And Balance                    //
    /////////////////////////////////////////////////////////////

    /***************** get available packages for topup ***************/
    public function getTopUpPackages(): array
    {
        $url = self::BASE_URL . 'packages';
        $response =   $this->sendRequest('GET', $url);
        return $response->packages ??[];
    }

    /***************** topup merchant balance  ***************/
    public function topupBalance($data=[])
    {
        $url = self::BASE_URL . 'topup';
        return $this->sendRequest('POST', $url, $data,$this->getSettings()['token']);
    }


    /***************** Get merchant balance history  ***************/
    public function getBalanceHistory($data=[])
    {
        $url = self::BASE_URL . 'balance-history?page='.$data['page'].'&date_from='.$data['date_from']."&date_to=".$data['date_to'];
        return $this->sendRequest('GET', $url, [],$this->getSettings()['token']);
    }

    /////////////////////////////////////////////////////////////
    ///                        Order                           //
    /////////////////////////////////////////////////////////////

    /***************** get provider country (countries from provider database to deliver shipments) ***************/
    public function getProviderCountries(): array
    {
        $url = self::BASE_URL . 'provider-countries';
        $response =   $this->sendRequest('GET', $url);
        return $response->countries ??[];
    }

    public function getTotalProducts(): int
    {
        // get total number of products
        $query = $this->db->query("SELECT count(*) AS total_products FROM product");
        return $query->row['total_products'];
    }

    public function lastOrderDate()
    {
        // get total number of products
        $query = $this->db->query("SELECT `date_added` FROM `order` ORDER BY `order_id` DESC LIMIT 1");
        return $query->row['date_added'];
    }
    /***************** get provider states depend on it's countries ***************/
    public function getProviderStates($country): array
    {
        $url = self::BASE_URL . 'provider-states-by-country/' . $country;
        $response =   $this->sendRequest('GET', $url);
        return $response->states ??[];
    }

    /***************** get provider cities depend on it's state and country ***************/
    public function getProviderCities($country, $state): array
    {
        $url = self::BASE_URL . 'provider-cities?country='.$country."&state=".$state;
        $response =   $this->sendRequest('GET', $url);
        return $response->cities ??[];
    }

    /***************** get shipping Price ***************/
    public function getShippingPrice($data = []): stdClass
    {
        $data['region'] = $data['country'] == 'EG' ? 'local' : 'global';
        $url = self::BASE_URL . 'shipping-price';
        return $this->sendRequest('GET', $url, $data);

    }

    /***************** Store shipment ***************/
    public function storeShipment($data = [])
    {
        $url = self::BASE_URL . 'orders';
        return $this->sendRequest('POST', $url, $data,$this->getSettings()['token']);
    }


    /***************** Reverse Order ***************/
    public function reverseOrder($data = [])
    {
        $url = self::BASE_URL . 'orders/reverse';
        return $this->sendRequest('POST', $url, $data,$this->getSettings()['token']);
    }

    /***************** Reverse Order ***************/
    private function getr($data = [])
    {
        $url = self::BASE_URL . 'orders/reverse';
        return $this->sendRequest('POST', $url, $data,$this->getSettings()['token']);
    }

    /////////////////////////////////////////////////////////////
    ///                     Requests                      //
    /////////////////////////////////////////////////////////////

    /*****************Send expandShip request ***************/
    private function sendRequest($type, $url, $body = [], $token = '')
    {
        //add authorization to request if needed
        $header = array('Content-Type: application/json', 'language: '.$this->config->get('config_admin_language'));
        if ($token)
            $header[] = 'Authorization: Bearer ' . $token;

        //send request
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url . ($type=='GET' && !empty($body) ? '?'.http_build_query($body) : ''),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $type,
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_HTTPHEADER => $header,
        ));

        $response = curl_exec($curl);
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if (in_array($http_status, [200, 401, 422, 503])) {
            $response = json_decode($response);
            $response->status_code = $http_status;
        } else {
            //set default error message
            $response = new stdClass();
            $response->status_code = 404;
            $response->message = "service not available please check with support";
        }
        return $response;
    }

    /**
     * @param $orderId
     * @return mixed|stdClass|null
     */
    public function getOrderBill($orderId)
    {
        $url = self::BASE_URL . "orders/{$orderId}/single-bill";
        return $this->sendRequest('GET', $url, null,$this->getSettings()['token']);
    }
}