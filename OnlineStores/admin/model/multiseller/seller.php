<?php

class ModelMultisellerSeller extends Model
{
    /**
     * Get all sellers from database
     *
     * @param array $data
     * @param array $sort
     * @param array $cols
     *
     * @return array
     */
    public function getSellers($data = array(), $sort = array(), $cols = array())
    {

        
        $hFiltersData = $wFiltersData = [];
        if (isset($sort['filters'])) {
            $cols = array_merge($cols, array("`c.name`" => 1,"ms.mobile"=>1,"c.email" => 1,"ms.nickname" => 1, "total_sales" => 1, "`ms.date_created`" => 1));
            foreach ($sort['filters'] as $k => $v) {
                if (!isset($cols[$k])) {
                    $wFiltersData[] = "{$k} LIKE '%" . $this->db->escape($v) . "%'";
                } else {
                    $hFiltersData[] = "{$k} LIKE '%" . $this->db->escape($v) . "%'";
                }
            }
        }
        if(!(\Extension::isInstalled('trips') &&$this->config->get('trips')['status']!=1))
        {
        $wFilters = '';
        if (count($wFiltersData) > 0) {
            $wFilters = ' AND (' . implode(' OR ', $wFiltersData) . ') ';
        }
    }

        $hFilters = '';
        if (count($hFiltersData) > 0) {
            $hFilters = ' AND (' . implode(' OR ', $hFiltersData) . ') ';
        }


        $sql = "SELECT
					SQL_CALC_FOUND_ROWS"
            // additional columns
            . (isset($cols['total_products']) ? "
						(SELECT COUNT(*) FROM " . DB_PREFIX . "product p
						LEFT JOIN " . DB_PREFIX . "ms_product mp USING (product_id)
						LEFT JOIN " . DB_PREFIX . "ms_seller USING (seller_id)
						WHERE seller_id = ms.seller_id) as total_products,
					" : "")

            . (isset($cols['total_earnings']) ? "
						(SELECT COALESCE(SUM(amount),0)
							- (SELECT COALESCE(ABS(SUM(amount)),0)
								FROM `" . DB_PREFIX . "ms_balance`
								LEFT JOIN order_product p ON (ms_balance.order_id = p.order_id) AND (ms_balance.product_id = p.product_id)
								WHERE seller_id = ms.seller_id
								AND balance_type = " . MsBalance::MS_BALANCE_TYPE_REFUND
                . ") as total
						FROM `" . DB_PREFIX . "ms_balance`
						LEFT JOIN order_product p ON (ms_balance.order_id = p.order_id) AND (ms_balance.product_id = p.product_id)
						WHERE seller_id = ms.seller_id
						AND balance_type = " . MsBalance::MS_BALANCE_TYPE_SALE . ") as total_earnings,
					" : "")

            . (isset($cols['current_balance']) ? "
						(SELECT COALESCE(
							(SELECT balance FROM " . DB_PREFIX . "ms_balance
								WHERE seller_id = ms.seller_id  
								ORDER BY balance_id DESC
								LIMIT 1
							),
							0
						)) as current_balance,
					" : "")

            // default columns
            . " CONCAT(c.firstname, ' ', c.lastname) as 'c.name',
					c.email as 'c.email',
					ms.seller_id as 'seller_id',
					ms.nickname as 'ms.nickname',
					ms.company as 'ms.company',
					ms.mobile as 'ms.mobile',
					ms.website as 'ms.website',
					ms.seller_status as 'ms.seller_status',
					ms.seller_approved as 'ms.seller_approved',
					ms.date_created as 'ms.date_created',
					ms.avatar as 'ms.avatar',
					ms.country_id as 'ms.country_id',
					ms.zone_id as 'ms.zone_id',
					ms.description as 'ms.description',
					ms.paypal as 'ms.paypal',
					IFNULL(SUM(mp.number_sold), 0) as 'total_sales'
				FROM `" . DB_PREFIX . "customer` c
				INNER JOIN `" . DB_PREFIX . "ms_seller` ms
					ON (c.customer_id = ms.seller_id)
				LEFT JOIN `" . DB_PREFIX . "ms_product` mp
					ON (c.customer_id = mp.seller_id)
				WHERE 1 = 1 "
            . (isset($data['seller_id']) ? " AND ms.seller_id =  " . (int)$data['seller_id'] : '')
            . (
                isset($data['seller_status']) ?
                    " AND ms.seller_status IN  (" . $this->db->escape(implode(',', $data['seller_status'])) . ")" :
                    ''
            )

            . $wFilters

            . " GROUP BY ms.seller_id HAVING 1 = 1 "

            . $hFilters

            . (isset($sort['order_by']) ? " ORDER BY {$sort['order_by']} {$sort['order_way']}" : '')
            . (isset($sort['limit']) ? " LIMIT " . (int)$sort['offset'] . ', ' . (int)($sort['limit']) : '');



        $res = $this->db->query($sql);
        $total = $this->db->query("SELECT FOUND_ROWS() as total");
        if ($res->rows) $res->rows[0]['total_rows'] = $total->row['total'];

        return $res->rows;
    }

    public function getSellerInfo($seller_id){

        $sql="select * from " . DB_PREFIX . " ms_seller where seller_id=$seller_id";
        $result=$this->db->query($sql);
        if($result->num_rows > 0){
            return $result->row;
        }
        return false;
    }

    public function editSeller($seller_id,$bank_transfer){
        $this->db->query("UPDATE " . DB_PREFIX . "ms_seller SET bank_transfer = '" . $this->db->escape($bank_transfer) . "' WHERE seller_id = " . (int)$seller_id);
    }

    public function editSellerColumn($seller_id,$col, $val){
        $this->db->query("UPDATE " . DB_PREFIX . "ms_seller SET $col = '" . $this->db->escape($val) . "' WHERE seller_id = " . (int)$seller_id);
    }
}
