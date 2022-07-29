<?php
class ModelLoghistoryCustomerBalance extends Model {

    public function getTotalCustomersBalance($data = array()) {

        $sql = "SELECT COUNT(DISTINCT log_history_id) AS total   FROM `" . DB_PREFIX . "log_history`";
        $implode = array();

        if (!empty($data['filter_date_start'])) {
            $implode[] = "DATE(date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
        }

        if (!empty($data['filter_date_end'])) {
            $implode[] = "DATE(date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
        }

        $implode[] = " type = 'customer' ";
        $implode[] = " action = 'updateBalance' ";

        if ($implode) {
            $sql .= " WHERE " . implode(" AND ", $implode);
        }

        $sql .= " ORDER BY date_added DESC";

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

        }

        $query = $this->db->query($sql);

        return $query->row['total'];
    }


    public function ajaxResponse($data, $request)
    {

        $logData = [];

        $sql = "SELECT lh.log_history_id,lh.old_value,lh.new_value,lh.action,lh.date_added,CONCAT(c.firstname,' ' ,c.lastname) as customer_name,c.email as customer_email,c.telephone,u.email ,CONCAT(u.firstname,' ' ,u.lastname) as username  FROM `" . DB_PREFIX . "log_history` lh LEFT JOIN `" . DB_PREFIX . "user` u ON (lh.user_id = u.user_id) ";
        $sql .= " LEFT JOIN `" . DB_PREFIX . "customer` c ON (lh.reference_id = customer_id) ";
        $implode = array();

        if (!empty($data['filter_date_start'])) {
            $implode[] = "DATE(lh.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
        }

        if (!empty($data['filter_date_end'])) {
            $implode[] = "DATE(lh.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
        }

        $implode[] = " lh.type = 'customer' ";

        $implode[] = " lh.action = 'updateBalance' ";

        if ($implode) {
            $sql .= " WHERE " . implode(" AND ", $implode);
        }

        if (!empty($data['search'])) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
            $sql .= " AND ( c.firstname LIKE '" . $request['search']['value'] . "%' ";
            $sql .= " OR c.lastname LIKE '" . $request['search']['value'] . "%' ";
            $sql .= " OR c.telephone LIKE '" . $request['search']['value'] . "%' )";
        }


        $sql .= " ORDER BY lh.log_history_id " . $request['order'][0]['dir'] . "  LIMIT " . $request['start'] . " ," . $request['length'] . "   ";

        $query = $this->db->query($sql);

        $logData['totalFilter'] = $query->num_rows;

        $logData['data'] = $query->rows;


        foreach ($logData['data'] as $key=> $info){
            $old_value = json_decode($info['old_value'],true);
            $new_value = json_decode($info['new_value'],true);
            $old_balance_value = isset($old_value['old_balance']) ? $old_value['old_balance'] : 0;
            $logData['data'][$key]['old_value'] = $old_balance_value;
            $logData['data'][$key]['new_value'] = $new_value['amount'];
        }

        $logData['total'] = $this->getTotalCustomersBalance($data);

        return $logData;
    }

}
?>