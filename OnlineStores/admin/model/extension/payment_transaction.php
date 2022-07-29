<?php
class ModelExtensionPaymentTransaction extends Model
{
    /**
     * @var string
     */
    protected $table = 'payment_transactions';

    /**
     * Select a transaction entry using the related order id.
     *
     * @param int $orderId
     *
     * @return array|bool
     */
    public function selectByOrderId(int $orderId)
    {
        $query = $columns = [];
        $query[] = 'SELECT * FROM %s WHERE `order_id`=%d';
        $data = $this->db->query(vsprintf(implode(' ', $query), [
            $this->table,
            $orderId
        ]));

        if ($data->num_rows > 0) {
            return $data->row;
        }

        return false;
    }

    /**
     * Select a transaction entry using the related filters
     *
     * @param array $filter
     *
     * @return array|bool
     */
    public function selectByFilters($filter = [])
    {
        $query = $columns = [];
        $query[] = sprintf('SELECT * FROM %s ',$this->table);
		
		$wheres = [];
		
		if (isset($filter['order_id']) && (int)$filter['order_id'] > 0) {
            $wheres[] = sprintf('AND `order_id` = %d', $filter['order_id']);
        }
		if (isset($filter['payment_gateway_id']) && (int)$filter['payment_gateway_id'] > 0) {
            $wheres[] = sprintf('AND `payment_gateway_id` = %d', $filter['payment_gateway_id']);
        }
		if (isset($filter['transaction_id']) && !empty($filter['transaction_id'])) {
            $wheres[] = sprintf('AND `transaction_id` = "%s"', $filter['transaction_id']);
        }
		if (isset($filter['payment_method']) && !empty($filter['payment_method'])) {
            $wheres[] = sprintf('AND `payment_method` = "%s"', $filter['payment_method']);
        }
		if (isset($filter['status']) && !empty($filter['status'])) {
            $wheres[] = sprintf('AND `status` = "%s"', $filter['status']);
        }
		
		if (count($wheres) > 0) {
            $query[] = 'WHERE 1=1 ' . implode(' ', $wheres);
        }
		
        $data = $this->db->query(implode(' ', $query));

        if ($data->num_rows > 0) {
            return $data->rows;
        }

        return false;
    }

    /**
     * Insert a payment transaction.
     *
     * @param array $data
     *
     * @return int
     */
    public function insert(array $data) : int
    {
        $query = $columns = [];
        $query[] = 'INSERT INTO %s SET';
        foreach ($data as $column => $value) {
            if ($column == "order_id" || $column == "amount") {
                $columns[] = sprintf('`%s` = %d', $column, $this->db->escape($value));
            } else {
                $columns[] = sprintf('`%s` = "%s"', $column, $this->db->escape($value));

            }
        }
        $query[] = implode(', ', $columns);

        $this->db->query(sprintf(implode(' ', $query), $this->table));
        return $this->db->getLastId();
    }
	
	/**
     * update a payment transaction.
     *
     * @param int $id 
	 * @param array $data
     *
     * @return bool
     */
	public function update(int $id , array $data) : bool
    {
        $query = $columns = [];
        $query[] = sprintf('UPDATE %s SET ',$this->table);
        
		foreach ($data as $column => $value) {
            
			if ($column == "order_id" || $column == "amount") {
                $columns[] = sprintf('`%s` = %d', $column, $this->db->escape($value));
            } else {
                $columns[] = sprintf('`%s` = "%s"', $column, $this->db->escape($value));

            }
		}
		
        if (count($columns) < 1) 
            return false;
        
        
		$query[] = implode(', ', $columns);
        $query[] = " WHERE " . sprintf('`%s` = "%s"', "id", $this->db->escape($id));
       
       
		$result = $this->db->query(implode(' ', $query));

		if($result)
			return true;

		return false;
    }

    /**
     * Select a apid transaction entry using the related order id.
     *
     * @param int $orderId
     *
     * @return array|bool
     */
    public function selectPaidByOrderId(int $orderId)
    {
        $query = $columns = [];
        $query[] = 'SELECT * FROM %s WHERE `order_id`=%d and status="Paid" ' ;
        $data = $this->db->query(vsprintf(implode(' ', $query), [
            $this->table,
            $orderId,
        ]));

        if ($data->num_rows > 0) {
            return $data->row;
        }

        return false;
    }

	/**
     * Insert a track transaction.
     *
     * @param array $data
     *
     * @return int
     */
    public function track_transaction(array $data){
        $query = $columns = [];
        $query[] = 'INSERT INTO payment_transactions_tracking SET';
        $columns[] = '`store_code` = "' . STORECODE . '"';
        foreach ($data as $column => $value) {
            if ($column == "amount") {
                $columns[] = sprintf('`%s` = %d', $column, $this->db->escape($value));
            } else {
                $columns[] = sprintf('`%s` = "%s"', $column, $this->db->escape($value));
            }
        }
        $query[] = implode(', ', $columns);

        $this->ecusersdb->query(implode(' ', $query));
		 return $this->ecusersdb->getLastId();
    }
}
