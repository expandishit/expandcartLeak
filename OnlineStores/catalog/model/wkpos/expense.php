<?php
class ModelWkposExpense extends Model {

    public function addExpense($pos_data) {

        $currency_data = $this->getCurrencyData(
            $pos_data['currency_code']
            );

        $this->db->query("INSERT INTO " . DB_PREFIX .
            "wkpos_expenses SET 
            title = '" . $pos_data['title'] . "', 
            user_id = '" . (int)$pos_data['user_id'] . "',
            description = '" . $pos_data['description'] . "', 
            currency_id = '" . $currency_data->row['currency_id'] . "', 
            currency_code = '" . $pos_data['currency_code'] . "', 
            currency_value = '" . $currency_data->row['value'] . "', 
            
            amount = '" .  $pos_data['amount'] . "', outlet_id = '" . $pos_data['outlet_id'] . "', date_added = NOW() ");

    }

    private function getCurrencyData($currency_code){

        return $this->db->query(
            "SELECT currency_id, `value`  FROM currency where code= '" .$currency_code ."' limit 1"
        );
    }
    public function getExpenses($user_id,$outlet_id,$start = 0, $limit = 200){
        if ($start < 0) {
            $start = 0;
        }

        if ($limit < 1) {
            $limit = 1;
        }

        $query = $this->db->query("SELECT  * FROM `" . DB_PREFIX . "wkpos_expenses` 
          WHERE user_id = '".$user_id."' AND outlet_id = '" .
            (int) $outlet_id . "' 
             ORDER BY wkpos_expense_id ASC LIMIT " . (int)$start . "," . (int)$limit);
        return $query->rows;
    }
}
