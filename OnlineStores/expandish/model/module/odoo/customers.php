<?php

use ExpandCart\Foundation\Inventory\Inventory;
use ExpandCart\Foundation\Providers\Extension;
use ExpandCart\Foundation\Inventory\Clients\Odoo;

class ModelModuleOdooCustomers extends Model
{
    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->module = $this->load->model("module/odoo/settings", ['return' => true]);
    }
    /**
     * @param $customer_id
     * @param $data
     * @return false|stdClass
     */
    private function mapCustomerData($data)
    {

        $customer_data = [
            'name' => $data['firstname'] . ' ' . $data['lastname'], // required
            'company_type' => 'person', // optional allowed types [person, company]
          //  'lang' => $this->config->get('config_admin_language'), // optional
            'email' => $data['email'],
            'phone' => $data['telephone'],
            'mobile' => $data['telephone'],
            
         
        ];
        if (isset($data['delivery'])) $customer_data['delivery'] = $data['delivery'];
        if (isset($data['invoice'])) $customer_data['invoice'] = $data['invoice'];

        return $customer_data;
    }
    public function createCustomer($customer_id, array $data = null)
    {
        if (!$data) return false;
        if (!$this->module->isActive()) return false;

        $result = $this->module->getInventory()->createContact($this->mapCustomerData($data));
        if ($result->status === true) {
            $this->linkCustomer($customer_id, $result->result, true);
            $this->changeCustomerStatus($customer_id, $data['status']);
        }

        return $result;
    }


    public function updateCustomer($customer_id, $data)
    {

        if (!$this->module->isActive()) return false;

        $link = $this->selectLinkCustomer($customer_id);
        if ($link) {
            $odoo_customer_result =$this->module->getInventory()->retrieveContact((int)$link['odoo_customer_id']);
            if ($odoo_customer_result->status === true) {
                $result = $this->module->getInventory()->updateContact($link['odoo_customer_id'], $this->mapCustomerData($data));

                if ($result->status === true) {
                    $this->changeCustomerStatus($customer_id,1);
                }

                return $result;
            }
            
        }
        // save new Customer to inventory
        return $this->createCustomer($customer_id, $data);
    }

    public function deleteCustomer($customer_id)
    {
        if (!$this->module->isActive()) return false;

        $link = $this->selectLinkCustomer($customer_id);
        if ($link) {
            $result =$this->module->getInventory()->deleteContact((int)$link['odoo_customer_id']);
            $this->unLinkCustomer($customer_id, $link['odoo_customer_id']);
            
        }
    }


    public function changeCustomerStatus(int $customer_id, int $status)
    {
        if (!$this->module->isActive()) return false;

        $link = $this->selectLinkCustomer($customer_id);

        if ($link) {
            if ((int)$status == 1) {
                $this->module->getInventory()->activeContact($link['odoo_customer_id']);
            } else {
                $this->module->getInventory()->inactiveContact($link['odoo_customer_id']);
            }
        }
    }

    public function selectLinkCustomer($customer_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "odoo_customers WHERE customer_id = '" . (int)$customer_id . "'");
        return $query->num_rows ? $query->row : null;
    }

    public function linkCustomer($customer_id, $odoo_customer_id, $without_check = false)
    {
        $link = $without_check ? false : $this->selectLinkCustomer($customer_id);

        if (!$link) {
            $this->db->query("INSERT INTO  " . DB_PREFIX . "odoo_customers SET customer_id = '" . (int)$customer_id . "', odoo_customer_id = '" . $odoo_customer_id . "'");
        }
    }

    public function unLinkCustomer($customer_id, $odoo_customer_id)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "odoo_customers WHERE customer_id = '" . (int)$customer_id . "' AND odoo_customer_id = '" . $odoo_customer_id . "'");
    }
}
