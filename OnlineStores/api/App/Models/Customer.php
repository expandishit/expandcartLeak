<?php
namespace Api\Models;

use Psr\Container\ContainerInterface;

class Customer 
{

    private $load;
    private $registry;
    private $db;
    public function __construct(ContainerInterface $container)
    {
        $this->registry = $container['registry'];
        $this->load = $container['loader'];
        $this->db = $container['db'];

    }

    /**
     * The customer table name string
     *
     * @var string
     */
    protected $customerTable = DB_PREFIX . 'customer';

    /**
     * login by email/phone and password
     *
     * TODO
     *
     * @return array
     */
    public function logIn()
    {

    }

    /**
     * get customer by dynamic col
     *
     * @param string $val
     * @param string $col
     *
     * @return bool
     */
    public function getCustomerByCol($val, $col)
    {
        // $db       = $this->container['db'];

        $queryString = [];
        $queryString[] = 'SELECT customer_id, firstname, lastname, status, approved FROM `' . $this->customerTable . '` WHERE';
        $queryString[] = $col.' = "' . $this->db->escape($val) . '"';

        $data = $this->db->query(implode(' ', $queryString));

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
    public function updateSecurity($val, $col, $security_code)
    {
        $data = $this->db->query("UPDATE `" . $this->customerTable . "` SET security_code='".$security_code."' WHERE ".$col." ='".$this->db->escape($val)."'");

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
        if(isset($data['password']) && isset($data['customer_id']) && isset($data['security_code'])){
            $chn = $this->db->query("UPDATE `" . $this->customerTable . "` SET salt = '" . $this->db->escape($salt = substr(md5(uniqid(rand(), true)), 0, 9)) . "', password='".$this->db->escape(sha1($salt . sha1($salt . sha1($data['password']))))."', security_code='' WHERE customer_id ='".$data['customer_id']."' AND security_code='".$data['security_code']."'");

            if ($chn) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get customers
     *
     * @param array $data['sort', 'order', 'start', 'limit']
     *
     * @return array
     */
    public function getAll($data)
    {
        $this->load->model('sale/customer');
        $customers = $this->registry->get('model_sale_customer')->getCustomers($data);
        return $customers;
    }


    /**
     * Store customer
     *
     * @param array $data
     *
     * @return array
     */
    public function createCustomer($data)
    {
        $this->load->model('sale/customer');
   
        if(!isset($data['customer_group_id']))
            $data['customer_group_id'] = "1";
        $data['status']                 = '1';
        $data['newsletter']             = '0';

        $customer_id = $this->registry->get('model_sale_customer')->addCustomer($data);
 
        // ZOHO inventory create customer if app is setup
        $this->load->model('module/zoho_inventory');
        $this->registry->get('model_module_zoho_inventory')->createCustomer($customer_id, $data);

        return $this->registry->get('model_sale_customer')->getCustomer($customer_id);
    }


    public function deleteCustomer($id)
    {
        $this->load->model('sale/customer');
        $this->registry->get('model_sale_customer')->deleteCustomer($id);

        return true;
    }
}
