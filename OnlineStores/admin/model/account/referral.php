<?php

class ModelAccountReferral extends Model
{
    public function get_my_code()
    {
        $query = 'SELECT * FROM ' . DB_PREFIX . 'merchant_referral_codes WHERE store_code = "' . STORECODE . '" LIMIT 1';
        $result = $this->ecusersdb->query($query);
        return $result->num_rows ? $result->row : false;
    }

    public function get_referral_code($code, $not_my_code = true){
        $query = 'SELECT * FROM ' . DB_PREFIX . 'merchant_referral_codes WHERE code = "' . $code . '"' . ($not_my_code ? ' AND store_code != "' . STORECODE .'"' : '') . ' LIMIT 1';
        $result = $this->ecusersdb->query($query);
        return $result->num_rows ? $result->row : false;
    }

    public function get_reward_code($code, $my_code = true){
        $query = 'SELECT * FROM ' . DB_PREFIX . 'merchant_referral_reward_codes WHERE code = "' . $code . '"' . ($my_code ? ' AND store_code = "' . STORECODE .'"' : '') . ' LIMIT 1';
        $result = $this->ecusersdb->query($query);
        return $result->num_rows ? $result->row : false;
    }

    public function invalidate_reward_code($code, $my_code = true){
        $query = 'UPDATE ' . DB_PREFIX . 'merchant_referral_reward_codes SET status = 1, used_at = "' . date('Y-m-d H:i:s') . '" WHERE code = "' . $code . '"';
        $result = $this->ecusersdb->query($query);
        return;
    }

    public function used_code($code_id){
        $query = 'SELECT id FROM ' . DB_PREFIX . 'merchant_referral_history WHERE referral_code_id = "' . $code_id . '" AND history_type = "subscription" AND store_code = "' . STORECODE .'" LIMIT 1';
        $result = $this->ecusersdb->query($query);
        return $result->num_rows ? $result->row : false;
    }

    public function get_code_balance($code_id, $type)
    {
        $query = 'SELECT SUM(h.reward_amount) as amount FROM ' . DB_PREFIX . 'merchant_referral_history h '; 
        if($type == 'current'){
            $query .= 'WHERE h.reward_code_id IS NULL ';
        }
        elseif($type = 'redeemed'){
            $query .= 'WHERE h.reward_code_id IS NOT NULL ';
        }
        $query .= 'AND h.referral_code_id = "' . $code_id . '"';

        $result = $this->ecusersdb->query($query);
        return $result->row['amount'] ? $result->row['amount'] : 0;
    }

    public function get_code_subscribers_count($code_id)
    {
        $query = 'SELECT COUNT(*) as total_count FROM ' . DB_PREFIX . 'merchant_referral_history WHERE history_type = "subscription" AND referral_code_id = "' . $code_id . '"';
        $result = $this->ecusersdb->query($query);
        return $result->row['total_count'];
    }

    public function get_code_registerss_count($code_id)
    {
        $query = 'SELECT COUNT(*) as total_count FROM ' . DB_PREFIX . 'merchant_referral_history WHERE history_type = "registeration" AND referral_code_id = "' . $code_id . '"';
        $result = $this->ecusersdb->query($query);
        return $result->row['total_count'];
    }

    public function insert_code($code, $currency)
    {
        $result_code;
        $result = $this->ecusersdb->query('SELECT id FROM ' . DB_PREFIX . 'merchant_referral_codes WHERE store_code = "' . STORECODE . '" LIMIT 1');
        if($result->num_rows == 0){
            $query = 'INSERT INTO ' . DB_PREFIX . 'merchant_referral_codes SET store_code = "' . STORECODE . '", code = "' . $code . '", currency = "' . $currency . '"';
            $this->ecusersdb->query($query);
            $result_code = $this->ecusersdb->query('SELECT * FROM ' . DB_PREFIX . 'merchant_referral_codes WHERE id = ' . $this->ecusersdb->getLastId())->row;
        }
        else{
            $result_code = $result->row;
        }
        return $result_code;
    }

    public function add_history(array $data)
    {
        $query = $columns = $values = [];
        $query[] = 'INSERT INTO ' . DB_PREFIX . 'merchant_referral_history';
        foreach ($data as $column => $value) {
            $columns[] = $column;
            $values[] = $value;
        }
        $query[] = '(`%s`)';
        $query[] = 'VALUES';
        $query[] = "('%s')";

        $this->ecusersdb->query(vsprintf(implode(' ', $query), [
            implode('`,`', $columns),
            implode("', '", $values)
        ]));

        return $this->ecusersdb->getLastId();
    }

    public function update_history($store_name){

        $query = "select id,attributes from merchant_referral_history where store_code = '".STORECODE."'";
        $result = $this->ecusersdb->query($query);
        if (count($result->row)){
            foreach ($result->rows as $row){
                $attributes = unserialize($row['attributes']);
                $id = (int) $row['id'];
                $attributes['store_name']=$store_name;
                $query = 'UPDATE ' . DB_PREFIX . 'merchant_referral_history SET attributes = "'.$this->db->escape(serialize($attributes)).'"'
                    . ' WHERE id = ' . "'$id'";
                $this->ecusersdb->query($query);
            }
        }

    }

    public function historyDtHandler($data)
    {
        $queryString = [];
        $fields = ['h.id', 'h.attributes', 'h.created_at', 'h.reward_amount'];
        $fields = implode(', ', $fields);
        $queryString[] = "SELECT {$fields} FROM " . DB_PREFIX . "merchant_referral_history h INNER JOIN merchant_referral_codes c ON h.referral_code_id = c.id";

        $queryString[] = "WHERE c.store_code = '" . STORECODE . "' ";

        $total = $this->ecusersdb->query(str_replace($fields, 'COUNT(*) as dc', implode(' ', $queryString)))->row['dc'];

        $sort_data = array(
            'created_at',
            'status'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $queryString[] = " ORDER BY " . $data['sort'];
        } else {
            $queryString[] = " ORDER BY created_at";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $queryString[] = " DESC";
        } else {
            $queryString[] = " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $queryString[] = " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $queryData = $this->ecusersdb->query(implode(' ', $queryString));

        $data = array(
            'data' => $queryData->rows,
            'total' => $total,
            'totalFiltered' => $queryData->num_rows
        );

        return $data;
    }

    public function rewardCodesDtHandler($data)
    {
        $queryString = [];
        $fields = ['r.id', 'r.code', 'r.created_at', 'r.status', 'r.amount'];
        $fields = implode(', ', $fields);
        $queryString[] = "SELECT {$fields} FROM " . DB_PREFIX . "merchant_referral_reward_codes r ";

        $queryString[] = "WHERE r.store_code = '" . STORECODE . "' ";

        $total = $this->ecusersdb->query(str_replace($fields, 'COUNT(*) as dc', implode(' ', $queryString)))->row['dc'];

        $sort_data = array(
            'created_at',
            'status',
            'amount'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $queryString[] = " ORDER BY " . $data['sort'];
        } else {
            $queryString[] = " ORDER BY r.created_at";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $queryString[] = " DESC";
        } else {
            $queryString[] = " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $queryString[] = " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $queryData = $this->ecusersdb->query(implode(' ', $queryString));

        $data = array(
            'data' => $queryData->rows,
            'total' => $total,
            'totalFiltered' => $queryData->num_rows
        );

        return $data;
    }

    public function redeem($code)
    {
        $query = 'SELECT id FROM ' . DB_PREFIX . 'merchant_referral_codes WHERE store_code = "' . STORECODE . '" LIMIT 1';
        $code_id = $this->ecusersdb->query($query)->row['id'];
        $amount = $this->get_code_balance($code_id, 0);

        $query = 'INSERT INTO ' . DB_PREFIX . 'merchant_referral_reward_codes SET store_code = "' . STORECODE . '", code = "' . $code . '", amount = ' . $amount;
        $this->ecusersdb->query($query);
        $request_id = $this->ecusersdb->getLastId();

        $query = 'UPDATE ' . DB_PREFIX . 'merchant_referral_history SET reward_code_id = ' . $request_id . ' WHERE reward_code_id IS NULL AND referral_code_id = ' . $code_id;
        $this->ecusersdb->query($query);

        return;
    }

}
