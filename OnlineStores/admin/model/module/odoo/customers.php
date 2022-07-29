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
                    $this->changeCustomerStatus((int)$customer_id, $data['status']);
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
    public function syncCustomers()
    {   
        if (!$this->module->isActive()) return false;
            $model_customer = $this->load->model('sale/customer', ['return' => true]);
            $limit = 200;
            $lastSyncDate= $this->module->selectlastSync('customers')['last_sync_date'];
            $filterData=['module'=>'customer','date'=>$lastSyncDate];
            $resultCount = $this->module->getInventory()->searchItem($filterData);
            $customersCount=$resultCount->result;
          ///Start sync from odoo to expand
           for($offset=0;$offset< $customersCount; $offset+=$limit)
            {
               $this->syncCustomersFromOdoo($offset, $limit,$lastSyncDate);
                if( $customersCount-$offset <= $limit )
                {
                    $limit=$customersCount-$offset; 
                    $this->syncCustomersFromOdoo($offset, $limit,$lastSyncDate);
                    break;
                }
            }
            $syncedCustomersCount=count($this->retrieveSyncedCustomers());

            ///Update last Sync Date
            if($syncedCustomersCount==$customersCount)
            {
            $this->module->updatelastSync('customers',date('d/m/Y'));
            }
            
        // end sync from odoo to expand
        //Sync customers to odoo

        foreach ($this->retrieveUnSyncedCustomers() as $customer) {
            $data = $model_customer->getCustomer($customer['customer_id']);
            $this->createCustomer($customer['customer_id'], $data);
        }

    }

    private function syncCustomersFromOdoo(int $offset,int $limit,$lastSyncDate)
    {
        $model_customer = $this->load->model('sale/customer', ['return' => true]);
    
        $synced_customers_ids = $this->retrieveSyncedCustomers();

        $odoo_customers=  $this->retrieveOdooCustomers($offset, $limit,$lastSyncDate);
        
 
        // customers divided into two parts [new|exist]
        $customers_batch = array_reduce($odoo_customers, function ($accumulator, $odoo_customer) use ($synced_customers_ids) {
           
            $formatted_customer = $this->mapOdooCustomerData($odoo_customer);

            $customer_index = array_search($odoo_customer['id'], array_column($synced_customers_ids, 'odoo_customer_id'));

            if ($customer_index === false) {
                // push customer to new
                $accumulator['new'][] = $formatted_customer;
            } else {
                // push to exist
                $formatted_customer['customer_id'] = (int)$synced_customers_ids[$customer_index]['customer_id'];
                $accumulator['exist'][] = $formatted_customer; // if true update
            }
            return $accumulator;
        }, ['new' => [], 'exist' => []]);

        // save new customers to db
        foreach ($customers_batch['new'] as $customer) {
            $customer_id = $model_customer->addCustomer($customer); // default save method
            $this->linkCustomer($customer_id, $customer['odoo_customer_id'], true); // save the customer link
        }
        //update customers with odoo values
        foreach ($customers_batch['exist'] as $customer) {
            $model_customer->editCustomer($customer['customer_id'], $customer);
        }

        unset($customers_batch);
    }
    
   
    private function retrieveOdooCustomers(int $offset, int $limit,$lastSyncDate)
    {   
        $filterData=['paging'=>['offset' => $offset, 'limit' => $limit],'date'=>$lastSyncDate];      
        $customersIDs = $this->module->getInventory()->listContacts($filterData);
        $customersIDsArr=json_decode(json_encode($customersIDs->result), true);
        $odooCustomersFields= array('id','name','company_type','email','phone','active','create_date','__last_update');
        $result = $this->module->getInventory()->readContacts($customersIDsArr,$odooCustomersFields);

        return $result->result;

    }
    
    private function mapOdooCustomerData(array $data)
    {
        return [
            'odoo_customer_id' => $data['id'],
            'firstname' => $data['name'],
            'email' => $data['email'],
            'telephone' =>$data['phone'],  
            'status' =>$data['active'],
            'date_added' =>$data['create_date']  ];
    }
    private function retrieveSyncedCustomers()
    {
        $query = $this->db->query("SELECT customer_id, odoo_customer_id FROM " . DB_PREFIX . "odoo_customers WHERE 1");
        return $query->num_rows ? $query->rows : [];
    }

    // retrieve customer id for all un sync customers to odoo
    private function retrieveUnSyncedCustomers()
    {
        $query = $this->db->query("SELECT t1.customer_id FROM " . DB_PREFIX . "customer t1 LEFT JOIN " . DB_PREFIX . "odoo_customers t2 ON t2.customer_id = t1.customer_id WHERE t2.customer_id IS NULL ");
        return $query->num_rows ? $query->rows : [];
    }

    public function selectLinkCustomer($customer_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "odoo_customers WHERE customer_id = '" . (int)$customer_id . "'");
        return $query->num_rows ? $query->row : null;
    }

    private function linkCustomer($customer_id, $odoo_customer_id, $without_check = false)
    {
        $link = $without_check ? false : $this->selectLinkCustomer($customer_id);

        if (!$link) {
            $this->db->query("INSERT INTO  " . DB_PREFIX . "odoo_customers SET customer_id = '" . (int)$customer_id . "', odoo_customer_id = '" . $odoo_customer_id . "'");
        }
    }

    private function unLinkCustomer($customer_id, $odoo_customer_id)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "odoo_customers WHERE customer_id = '" . (int)$customer_id . "' AND odoo_customer_id = '" . $odoo_customer_id . "'");
    }
}
