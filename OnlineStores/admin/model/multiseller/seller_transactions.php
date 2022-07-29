<?php

class ModelMultisellerSellerTransactions extends Model
{
    /**
     * Get all balance entries from database
     *
     * @param array $data
     * @param array $sort
     *
     * @return array
     */
    public function getBalanceEntries($data = array(), $sort = array())
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

        // todo fix other getBalanceEntries calls
        $sql = "SELECT
					SQL_CALC_FOUND_ROWS
					*,
					mb.description as 'mb.description',
					mb.date_created as 'mb.date_created'
				FROM " . DB_PREFIX . "ms_balance mb
				INNER JOIN " . DB_PREFIX . "ms_seller ms
                    ON (mb.seller_id = ms.seller_id)
                LEFT JOIN order_product p
					ON (mb.order_id = p.order_id) AND (mb.product_id = p.product_id)     
				WHERE 1 = 1"
            . (isset($data['order_id']) ? " AND mb.order_id =  " . (int)$data['order_id'] : '')
            . (isset($data['product_id']) ? " AND mb.product_id =  " . (int)$data['product_id'] : '')
            
            . (isset($data['seller_id']) ? " AND mb.seller_id =  " . (int)$data['seller_id'] : '')
            
            . (isset($data['filter_date_start']) ? " AND DATE(mb.date_created) >= '" . $this->db->escape($data['filter_date_start']) . "'" : '')

                        
            . (isset($data['filter_date_end']) ? " AND DATE(mb.date_created) <= '" . $this->db->escape($data['filter_date_end']) . "'" : '')


            . (isset($data['withdrawal_id']) ? " AND seller_id =  " . (int)$data['withdrawal_id'] : '')
            . (isset($data['balance_type']) ? " AND balance_type =  " . (int)$data['balance_type'] : '')
            . $filters
            . (isset($sort['order_by']) ? " ORDER BY {$sort['order_by']} {$sort['order_way']}" : '')
            . (isset($sort['limit']) ? " LIMIT " . (int)$sort['offset'] . ', ' . (int)($sort['limit']) : '');

        $res = $this->db->query($sql);

        $total = $this->db->query("SELECT FOUND_ROWS() as total");


        if ($res->rows) $res->rows[0]['total_rows'] = $total->row['total'];

        
        
        $res = $this->appendCalculation($res->rows);

        // echo "<pre>";
        // print_r($res);
        // echo "</pre>";
        // die();



        return $res;

    }

    //ATH was here at 21-4-2019 16:45 
    public function appendCalculation($res){

        $mountType2 = 0;
        $newTransBalance = [];


        foreach($res as $prodTran){

            if( $prodTran['balance_type'] == '2' ){

                $mountType2 = $mountType2 + (int) $prodTran['amount']; 
            }

            $prodTran['avail'] = $this->availBalance($prodTran, $mountType2);
            
            $prodTran["net_earning"] = $this->totalEarnings($prodTran);

            $newTransBalance[] = $prodTran;



        }

        return $newTransBalance;

    }


    public function availBalance($prodTran, $mountType2){
        
        return floatval($prodTran['balance'] - $mountType2);
    }

    public function totalEarnings($prodTran){

        if(!$prodTran['price'] || !$prodTran['quantity'] ){
            
            return 0;
        
        }

        return  floatval($prodTran['price'] * $prodTran['quantity']);  

    }
}