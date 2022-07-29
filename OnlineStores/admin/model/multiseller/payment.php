<?php

class ModelMultisellerPayment extends Model
{
    /**
     * Get all payments entries from database
     *
     * @param array $data
     * @param array $sort
     *
     * @return array
     */
    public function getPayments($data = array(), $sort = array())
    {
        $filtersData = [];
        if (isset($sort['filters'])) {
            foreach ($sort['filters'] as $k => $v) {
                $filtersData[] = "{$k} LIKE '%" . $this->db->escape($v) . "%'";
            }
        }

        $filters = '';
        if (count($filtersData) > 0) {
            $filters = ' AND (' . implode(' OR ', $filtersData) . ') ';
        }

        $sql = "SELECT
					SQL_CALC_FOUND_ROWS
					payment_id,
					payment_type,
					payment_status,
					payment_method,
					payment_data,
					amount,
					currency_code,
					mpay.date_created as 'mpay.date_created',
					mpay.date_paid as 'mpay.date_paid',
					mpay.description as 'mpay.description',
					ms.seller_id as 'seller_id',
					ms.nickname,
					ms.paypal,
					product_id,
					order_id
				FROM `" . DB_PREFIX . "ms_payment` mpay
				LEFT JOIN `" . DB_PREFIX . "ms_seller` ms
					USING (seller_id)
				WHERE 1 = 1 "
            . (isset($data['payment_id']) ? " AND payment_id =  " . (int)$data['payment_id'] : '')
            . (isset($data['seller_id']) ? " AND seller_id =  " . (int)$data['seller_id'] : '')
            . (isset($data['product_id']) ? " AND product_id =  " . (int)$data['product_id'] : '')
            . (isset($data['order_id']) ? " AND order_id =  " . (int)$data['order_id'] : '')
            . (isset($data['currency_id']) ? " AND currency_id =  " . (int)$data['currency_id'] : '')
            . (isset($data['payment_type']) ? " AND payment_type IN  (" . $this->db->escape(implode(',', $data['payment_type'])) . ")" : '')
            . (isset($data['payment_method']) ? " AND payment_method IN  (" . $this->db->escape(implode(',', $data['payment_method'])) . ")" : '')
            . (isset($data['payment_status']) ? " AND payment_status IN  (" . $this->db->escape(implode(',', $data['payment_status'])) . ")" : '')

            . $filters

            . (isset($sort['order_by']) ? " ORDER BY {$sort['order_by']} {$sort['order_way']}" : '')
            . (isset($sort['limit']) ? " LIMIT " . (int)$sort['offset'] . ', ' . (int)($sort['limit']) : '');

        $res = $this->db->query($sql);

        $total = $this->db->query("SELECT FOUND_ROWS() as total");
        if ($res->rows) $res->rows[0]['total_rows'] = $total->row['total'];

        return ($res->num_rows == 1 && isset($data['single']) ? $res->row : $res->rows);
    }
}
