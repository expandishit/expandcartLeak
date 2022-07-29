<?php
class Twig_Extension_ExpandcartAdminGlobals extends Twig_Extension implements Twig_Extension_GlobalsInterface
{
    protected $registry;
    protected $_globals;

    public function __construct(\Registry $registry) {
        $this->registry = $registry;

        $document = $this->registry->get('document');
        $session = $this->registry->get('session');
        $load = $this->registry->get('load');
        $url = $this->registry->get('url');
        $db = $this->registry->get('db');
        $user = $this->registry->get('user');
        $queryMultiseller = $db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");
        $IsMultiSellerInstalled = ($queryMultiseller->num_rows > 0);

        $request = array();
        $request['get'] = $_GET;
        $request['post'] = $_POST;

        $globals = array(
            'BuildNumber' => BuildNumber,
            'document' => $document,
            'ChildData' => $document->getChildData(),
            'session_data' => $session->data,
            'HTTP_SERVER' => HTTP_SERVER,
            'route' => isset($this->registry->get('options')['routeString']) ? $this->registry->get('options')['routeString'] : 'common/home',
            'is_multiseller_installed' => $IsMultiSellerInstalled,
            'request' => $request,
            'session' => $session,
            'ONLINE_STORES_WEB_PATH' => ONLINE_STORES_WEB_PATH,
            'BILLING_DETAILS_EMAIL' => BILLING_DETAILS_EMAIL,
            'user' => $user,
            'PackageId' => PRODUCTID,
            'storecode' => STORECODE
        );


        if ($user->getId()) {
            $billingDetailsModel = $load->model('billingaccount/common' , ['return' => true]);
            $billingDetails = $billingDetailsModel->getBillingDetails();
            $whmcs = new whmcs();
            $clientDetails = $whmcs->getClientDetails(WHMCS_USER_ID);
            $globals['store_merchant_phone'] = $clientDetails['phonenumber'];
            $globals['store_merchant_country'] = $clientDetails['country'];
            $globals['store_merchant_city'] = $clientDetails['city'];
            $userActivation = $this->registry->get('userActivation');
            $globals['Soft_activated'] = $userActivation->isSoftActivationCompleted()?:0;
            $globals['Hard_activated'] = $userActivation->isHardActivationCompleted()?:0;
            $globals['billing_email'] =  $billingDetails['email'];
            $globals['billing_first_name'] =  $billingDetails['name'];
            $globals['billing_last_name'] =  $billingDetails['name'];
        }

        if($user->getId() == 1) {
            $queryProductsCount = $db->query("SELECT COUNT(DISTINCT p.product_id) AS total FROM " . DB_PREFIX . "product p");
            $productsCount = $queryProductsCount->row['total'];

            $queryOrdersCount = $db->query("SELECT COUNT(DISTINCT o.order_id) AS total FROM `" . DB_PREFIX . "order` o WHERE order_status_id = (SELECT value FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_complete_status_id')");
            $ordersCount = $queryOrdersCount->row['total'];

            $queryLastOrderDate = $db->query("SELECT date_added FROM `" . DB_PREFIX . "order` o WHERE order_status_id = (SELECT value FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_complete_status_id') ORDER BY date_added DESC LIMIT 1");
            $last_order_at = $queryLastOrderDate->num_rows ? $queryLastOrderDate->row['date_added'] : 0;

            $domainsetting = $load->model('setting/domainsetting', ['return' => true]);
            $setting = $load->model('setting/setting', ['return' => true]);

            $countrycode = $clientDetails['countrycode'];

            $globals['products_count'] = $productsCount;
            $globals['orders_count'] = $ordersCount;
            $globals['has_domain'] = $domainsetting->countDomain() ? 1 : 0;
            $globals['published_template'] = $this->registry->get('config')->get('cust_design') ? 1 : 0;
            $globals['has_payment'] = $this->has_activated('payment');
            $globals['has_shipping'] = $this->has_activated('shipping');
            $globals['client_country_code'] = $countrycode;
            $globals['client_registered_at'] = STORE_CREATED_AT;
            $globals['last_order_at'] = $last_order_at;
        }


        $this->_globals = $globals;
    }

    public function getGlobals()
    {
        return $this->_globals;
    }

    private function has_activated($module)
    {
        $load = $this->registry->get('load');
        $config = $this->registry->get('config');

        $extension = $load->model('setting/extension', ['return' => true]);
        $extensions = $extension->getInstalled($module);

        foreach ($extensions as $key => $value) {
            $settings = $config->get($value);
            if ($settings && is_array($settings) == true) {
                $status = $settings['status'];
            } else {
                $status = $config->get($value . '_status');
            }

            if (!$status) {
                unset($extensions[$key]);
            }
        }

        return count($extensions) > 1 ? 1 : 0;
    }

}