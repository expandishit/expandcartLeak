<?php

class ModelModuleAbandonedCartEmailedOrders extends ModelModuleAbandonedCartSettings
{
    /**
     * Get all emailed abandoned orders by order id.
     *
     * @param int $orderId
     *
     * @return array|bool
     */
    public function getEmailedOrdersByOrderId($orderId)
    {
        $query = [];
        $query[] = 'SELECT * FROM ' . $this->emailedAbandonedOrdersTable;
        $query[] = 'WHERE emailed=1 AND order_id="' . (int)$orderId . '"';

        $data = $this->db->query(implode(' ', $query));

        if ($data->num_rows) {
            return $data->row;
        }

        return false;
    }

    /**
     * Store new emailed abandoned order.
     *
     * @param int $orderId
     *
     * @return void
     */
    public function insertEmailedOrder($orderId)
    {
        $query = [];
        $query[] = 'INSERT INTO ' . $this->emailedAbandonedOrdersTable;
        $query[] = 'SET emailed=1, order_id="' . (int)$orderId . '"';

        $this->db->query(implode(' ', $query));
    }

    /**
     * Delete emailed abandone order by it's order id.
     *
     * @param int $orderId
     *
     * @return void
     */
    public function deleteEmailedOrder($orderId)
    {
        $query = [];
        $query[] = 'DELETE FROM ' . $this->emailedAbandonedOrdersTable;
        $query[] = 'WHERE order_id="' . (int)$orderId . '"';

        $this->db->query(implode(' ', $query));
    }

    /**
     * Gets the minimum and maximum total for all orders.
     *
     * @return array|bool
     */
    public function getOrderMinMaxTotal()
    {
        $fields = 'MIN(`total`) as _min, MAX(`total`) as _max';

        $queryString = 'SELECT ' . $fields . ' FROM `' . DB_PREFIX . 'order` WHERE order_status_id = "0"';

        $data = $this->db->query($queryString);

        if ($data->num_rows) {
            return [
                'min' => (int)$data->row['_min'],
                'max' => (int)$data->row['_max'],
            ];
        }

        return false;
    }

    /**
     * Get all abandoned orders products.
     *
     * @return array|bool
     */
    public function getAllOrdersProducts()
    {
        $query = [];
        $fields = 'product_id, MAX(name) as product_name';
        $query[] = 'SELECT ' . $fields . ' FROM ' . DB_PREFIX . 'order_product as op';
        $query[] = 'INNER JOIN `order` as o';
        $query[] = 'ON o.order_id=op.order_id';
        $query[] = 'WHERE o.order_status_id = "0"';
        $query[] = 'GROUP BY op.product_id';

        $data = $this->db->query(implode(' ', $query));

        if ($data->num_rows) {
            return $data->rows;
        }

        return false;
    }

    /**
     * Get all abandoned orders order id column.
     *
     * @return array|bool
     */
    public function getOrderIds()
    {
        $query[] = 'SELECT order_id FROM ' . $this->emailedAbandonedOrdersTable;
        $query[] = 'GROUP BY order_id';

        $data = $this->db->query(implode(' ', $query));

        if ($data->num_rows) {
            return array_column($data->rows, 'order_id');
        }

        return false;
    }

    /**
     * Render the email message.
     *
     * @param string $message
     * @param array $orderInfo
     *
     * @return string
     */
    public function renderMessageTemplate($message, $orderInfo)
    {
        $message = preg_replace('#\{firstname\}#', $orderInfo['firstname'], $message);
        $message = preg_replace('#\{lastname\}#', $orderInfo['lastname'], $message);
        $message = preg_replace('#\{orderid\}#', $orderInfo['order_id'], $message);
        $message = preg_replace('#\{invoice\}#', $orderInfo['invoice_prefix'] . $orderInfo['invoice_no'], $message);
        $message = preg_replace('#\{telephone\}#', $orderInfo['telephone'], $message);
        $message = preg_replace('#\{email\}#', $orderInfo['email'], $message);

        if (strstr($message, '{products}') !== false) {

            $orders = $this->load->model('sale/order', ['return' => true]);

            $orderProducts = $orders->getOrderProducts($orderInfo['order_id']);

            $message = preg_replace_callback('#\{products\}#', function ($matches) use ($orderProducts) {

                $template = [];

                $template[] = '<table>';
                $template[] = '<thead>';
                $template[] = '<tr>';
                $template[] = '<th>Product Name</th>';
                $template[] = '<th>Total</th>';
                $template[] = '</tr>';
                $template[] = '</thead>';

                foreach ($orderProducts as $orderProduct) {
                    $template[] = '<tr>';
                    $template[] = '<td>' . $orderProduct['name'] . '</td>';
                    $template[] = '<td>' . $orderProduct['total'] . '</td>';
                    $template[] = '</tr>';
                }

                $template[] = '</table>';

                return implode(' ', $template);
            }, $message);
        }

        return $message;
    }
}
