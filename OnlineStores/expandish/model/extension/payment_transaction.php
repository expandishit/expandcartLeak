<?php
class ModelExtensionPaymentTransaction extends Model
{
    /**
     * @var string
     */
    protected $table = 'payment_transactions';

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
                $columns[] = sprintf('`%s` = %f', $column, $this->db->escape($value));
            } else {
                $columns[] = sprintf('`%s` = "%s"', $column, $this->db->escape($value));

            }
        }
        $query[] = implode(', ', $columns);
        $this->db->query(sprintf(implode(' ', $query), $this->table));
        return $this->db->getLastId();
    }
    public function get_payment_transaction_by_order_id($order_id)
    {

        $query = $this->db->query("SELECT COUNT(*) As total FROM payment_transactions WHERE status='Paid' and order_id = '" . $order_id . "'");

        if ($query->row['total']>0) {
            return true;
        } else {
            return false;
        }
    }

    public function track_transaction(array $data)
	{
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
    }

    /**
     * @param int $orderId
     * @return mixed
     */
    public function getPaymentTransactionInfoByOrderId($orderId)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "payment_transactions WHERE `order_id` = '" . (int)$orderId . "'");
        return $query->row;
    }

    /**
     * @param array $data
     * @return int
     */
    public function update(array $data): int
    {
        if (!$data['order_id'] || empty($data['order_id']))
            return false;

        $orderId = $data['order_id'];
        unset($data['order_id']);

        $query = $columns = [];
        $query[] = 'UPDATE  %s SET';
        foreach ($data as $column => $value) {
            if ($column == "amount") {
                $columns[] = sprintf('`%s` = %f', $column, $this->db->escape($value));
            } else {
                $columns[] = sprintf('`%s` = "%s"', $column, $this->db->escape($value));

            }
        }
        $query[] = implode(', ', $columns);
        $queryString = sprintf(implode(' ', $query), $this->table);
        $queryString .= " where order_id={$orderId}";
        $this->db->query($queryString);
        return $this->db->getLastId();
    }
}
