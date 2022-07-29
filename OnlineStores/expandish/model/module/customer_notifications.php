<?php

class ModelModuleCustomerNotifications extends Model
{ 
    private $table = "customer_notifications";

    public function getCustomerNotifications() {

        $customer_id = $this->customer->getId();
        // if(!$customer_id) $customer_id=0;
        if($customer_id) {

            $sql = "SELECT * FROM " . DB_PREFIX .$this->table . " ";

            $sql .= " WHERE customer_id=".$customer_id;

            $sql.=" ORDER BY created_at DESC";

            $query = $this->db->query($sql);

            return $query->rows;
        }
        return 0;
    }

    public function getUnreadCustomerNotificationsCount()
    {
        $customer_id = $this->customer->getId();
        //if(!$customer_id) $customer_id=0;
        if ($customer_id) {
            $sql = "SELECT * FROM " . DB_PREFIX .$this->table . " ";

            $sql .= " WHERE customer_id=".$customer_id." AND read_status=0 ";

            $query = $this->db->query($sql);

            return $query->num_rows;
        }
        return 0;
    }
    public function markAsRead($id){

        $customer_id = $this->customer->getId();
        if($id)
        $this->db->query("UPDATE " . DB_PREFIX .$this->table . " SET `read_status` = 1 WHERE id=".$id." AND customer_id=".$customer_id."" );
        else
        $this->db->query("UPDATE " . DB_PREFIX .$this->table . " SET `read_status` = 1 WHERE customer_id=".$customer_id."");
    }

}