<?php
namespace Api\Models;

class CustomerGroup extends ParentModel
{
   
	 /**
     * Get customer Groups
     *
     * 
     *
     * @return array
     */
    public function getCustomerGroups()
    {
        $registry = $this->container['registry'];
        $load = $this->container['loader'];

        $load->model('sale/customer_group');

        $customer_groups = $registry->get('model_sale_customer_group')->getCustomerGroups();
        return $customer_groups;
    }
}
