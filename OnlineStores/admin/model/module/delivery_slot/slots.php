<?php
class ModelModuleDeliverySlotSlots extends Model {
    // define table name
    private $table = "ds_delivery_slot";

    public function addSlots(array $data) {
        $status = 1;
        if(!isset($data['status'])){
            $status = 0;
        }
        $this->db->query("INSERT INTO " . DB_PREFIX . "ds_delivery_slot SET ds_day_id = '" . (int)$data['day_id'] . "', delivery_slot = '" . $this->db->escape($data['delivery_slot']) . "',time_start =  TIME( STR_TO_DATE( '".$data['time_start']."', '%h:%i %p' ) ) ,time_end =  TIME( STR_TO_DATE( '".$data['time_end']."', '%h:%i %p' ) ) , total_orders = '" . (int)$data['total_orders'] . "', status = '".$status."'");
        return true;
    }

    public function editSlot($slot_id, array $data) {

        $queryString = $fields = [];
        $queryString[] = "UPDATE " . DB_PREFIX.$this->table . " SET";

        $fields[] = "delivery_slot = '" . $data['delivery_slot'] . "'";
        $fields[] = "time_start =  TIME( STR_TO_DATE( '".$data['time_start']."', '%h:%i %p' ) )";
        $fields[] = "time_end =  TIME( STR_TO_DATE( '".$data['time_end']."', '%h:%i %p' ) )";
        $fields[] = "total_orders = '" . (int)$data['total_orders'] . "'";
        $fields[] = "status = '" . (int)$data['status'] . "'";

        $queryString[] = implode(',', $fields);
        $queryString[] = 'WHERE ds_delivery_slot_id="' . $slot_id . '"';

        $this->db->query(implode(' ', $queryString));

        return true;
    }

    public function deleteSlot($slot_id) {
        $this->db->query("DELETE FROM " . DB_PREFIX.$this->table . " WHERE 	ds_delivery_slot_id = '" . (int)$slot_id . "'");

        return true;
    }

    public function getSlot($slot_id) {

        $query = $this->db->query("SELECT *, TIME_FORMAT( `time_start`, '%h:%i %p' ) as time_start_formated, TIME_FORMAT( `time_end`, '%h:%i %p' ) as time_end_formated FROM " . DB_PREFIX.$this->table  . " WHERE ds_delivery_slot_id = '" . (int)$slot_id . "' ");

        return $query->row;
    }

    public function getSlots($data = array()) {
        $sql = "SELECT * FROM " . DB_PREFIX .$this->table . " ";

        $sql .= " WHERE ds_day_id = ".$data['day_id'];

        if (!empty($data['filter_name'])) {
            $sql .= " AND delivery_slot LIKE '" . $this->db->escape($data['filter_name']) . "%'";
        }

        $sort_data = array(
            'delivery_slot',
            'ds_delivery_slot_id'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY delivery_slot";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getTotalSlots($data = array()) {
        $sql = "SELECT COUNT(ds_delivery_slot_id) AS total FROM ".DB_PREFIX.$this->table;
        $sql .= " WHERE ds_day_id = ".$data['day_id'];

        $query = $this->db->query($sql);
        return $query->row['total'];
    }

    public function getOrderDeliverySlot($order_id) {
        $query = $this->db->query("SELECT *  FROM ".DB_PREFIX."ds_delivery_slot_order WHERE order_id = ".(int)$order_id." ");
        return $query->row;
    }

    public function getSlotsOrders($data = array(), $filterData = array()) {

        $language_id = $this->config->get('config_language_id') ?: 1;

        $fields = [
            'so.*',
            'ors.name AS status',
            'ors.bk_color AS status_color'
            ];
        $fields =implode(',', $fields);

        $query = "SELECT $fields  FROM ".DB_PREFIX."ds_delivery_slot_order so ";
        $query .= " LEFT JOIN `".DB_PREFIX."order` o ON (o.order_id = so.order_id)";
        $query .= ' LEFT JOIN ' . DB_PREFIX . 'order_status ors ON (ors.order_status_id = o.order_status_id)';
        $query .= " WHERE ors.language_id = '" . $language_id . "'";

        $total = $this->db->query(
            'SELECT COUNT(*) AS totalData FROM ('.
            str_replace($fields, '0 as fakeColumn', $query)
            .') AS t'
        )->row['totalData'];


        if (isset($filterData['search'])) {

            $filterData['search'] = $this->db->escape($filterData['search']);

            $query .= ' AND (';
            $query .= "so.order_id LIKE '%{$filterData['search']}%'";
            $query .= " OR so.slot_description LIKE '%{$filterData['search']}%'";
            $query .= " OR so.delivery_date LIKE '%{$filterData['search']}%'";
            $query .= " OR so.day_name LIKE '%{$filterData['search']}%'";
            $query .= ')';
        }

        if (isset($filterData['order_status_id']) && count($filterData['order_status_id']) > 0) {

            $this->load->model('sale/order');
            $statusesIds = implode(', ', $this->model_sale_order->filterArrayOfIds($filterData['order_status_id']));

            $query .= 'AND (o.order_status_id IN (' . $statusesIds . '))';
        }

        if(isset($filterData['delivery_slot_date'])){
            $ds_startDate = null;
            $ds_endDate = null;
            if (isset($filterData['delivery_slot_date']['start']) && isset($filterData['delivery_slot_date']['end'])) {
                $ds_startDate = strtotime($filterData['delivery_slot_date']['start']);
                $ds_endDate = strtotime($filterData['delivery_slot_date']['end']);
            }

            if (($ds_startDate && $ds_endDate) && $ds_endDate > $ds_startDate) {

                $ds_formattedStartDate = date('m-d-Y', $ds_startDate);
                $ds_formattedEndDate = date('m-d-Y', $ds_endDate);

                $query .= 'AND (so.delivery_date BETWEEN "' . $ds_formattedStartDate . '" AND "' . $ds_formattedEndDate . '")';
            } elseif(($ds_startDate && $ds_endDate) && $ds_endDate == $ds_startDate) {
                $ds_formattedEndDate = date('m-d-Y', $ds_startDate);

                $query .= 'AND (so.delivery_date ="' . $ds_formattedEndDate . '")';
            }
        }

        $totalFiltered = $this->db->query(
            'SELECT COUNT(*) AS totalData FROM ('.
            str_replace($fields, '0 as fakeColumn', $query)
            .') AS t'
        )->row['totalData'];

        $sort_data = array(
            'order_id',
            'total'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $query .= " ORDER BY " . $data['sort'];
        } else {
            $query .= " ORDER BY so.order_id";
        }

        if (isset($data['order']) && ($data['order'] == 'desc')) {
            $query .= " DESC";
        } else {
            $query .= " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] > 0) {
                $query .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
            }
        }

        $query = $this->db->query($query);

        return [
            'data' => $query->rows,
            'total' => $total,
            'totalFiltered' => $totalFiltered
        ];
    }

    public function getSlotsReport($data = array(), $filterData = array()) {

        $language_id = $this->config->get('config_language_id') ?: 1;

        $statuses = [];
        if (isset($filterData['status_ids'])) {
            $statuses = explode(',',$filterData['status_ids']);
        }

        $fields = [
            'so.*',
            'IFNULL(s.total_orders, 0) as total_orders',
            'COUNT(so.order_id) as received_orders'
        ];

        if(count($statuses) > 0){
            foreach ($statuses as $status){
                array_push($fields, 'SUM(IF(ors.order_status_id='.$status.',1,0)) AS status_'.$status);
            }
        }

        $fields =implode(',', $fields);

        $query = "SELECT $fields  FROM ".DB_PREFIX."ds_delivery_slot_order so ";
        $query .= " LEFT JOIN `".DB_PREFIX."ds_delivery_slot` s ON (s.ds_delivery_slot_id = so.ds_delivery_slot_id)";
        $query .= " LEFT JOIN `".DB_PREFIX."order` o ON (o.order_id = so.order_id)";
        $query .= ' LEFT JOIN ' . DB_PREFIX . 'order_status ors ON (ors.order_status_id = o.order_status_id)';
        $query .= " WHERE ors.language_id = '" . $language_id . "'";

        if(count($statuses) > 0){
            $query .= ' AND ors.order_status_id IN('.$filterData['status_ids'].')';
        }

        $total = $this->db->query(
            'SELECT COUNT(*) AS totalData FROM ('.
            str_replace($fields, '0 as fakeColumn', $query)
            .') AS t'
        )->row['totalData'];


        if (isset($filterData['search'])) {

            $filterData['search'] = $this->db->escape($filterData['search']);

            $query .= ' AND (';
            $query .= "so.order_id LIKE '%{$filterData['search']}%'";
            $query .= " OR so.slot_description LIKE '%{$filterData['search']}%'";
            $query .= " OR so.delivery_date LIKE '%{$filterData['search']}%'";
            $query .= " OR so.day_name LIKE '%{$filterData['search']}%'";
            $query .= ')';
        }

        if (isset($filterData['order_status_id']) && count($filterData['order_status_id']) > 0) {

            $this->load->model('sale/order');
            $statusesIds = implode(', ', $this->model_sale_order->filterArrayOfIds($filterData['order_status_id']));

            $query .= 'AND (o.order_status_id IN (' . $statusesIds . '))';
        }

        if(isset($filterData['delivery_slot_date'])){
            $ds_startDate = null;
            $ds_endDate = null;
            if (isset($filterData['delivery_slot_date']['start']) && isset($filterData['delivery_slot_date']['end'])) {
                $ds_startDate = strtotime($filterData['delivery_slot_date']['start']);
                $ds_endDate = strtotime($filterData['delivery_slot_date']['end']);
            }

            if (($ds_startDate && $ds_endDate) && $ds_endDate > $ds_startDate) {

                $ds_formattedStartDate = date('m-d-Y', $ds_startDate);
                $ds_formattedEndDate = date('m-d-Y', $ds_endDate);

                $query .= 'AND (so.delivery_date BETWEEN "' . $ds_formattedStartDate . '" AND "' . $ds_formattedEndDate . '")';
            } elseif(($ds_startDate && $ds_endDate) && $ds_endDate == $ds_startDate) {
                $ds_formattedEndDate = date('m-d-Y', $ds_startDate);

                $query .= 'AND (so.delivery_date ="' . $ds_formattedEndDate . '")';
            }
        }
        $query .= " GROUP BY so.delivery_date, so.slot_description";
        $totalFiltered = $this->db->query(
            'SELECT COUNT(*) AS totalData FROM ('.
            str_replace($fields, '0 as fakeColumn', $query)
            .') AS t'
        )->row['totalData'];

        $sort_data = array(
            'order_id',
            'total'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $query .= " ORDER BY " . $data['sort'];
        } else {
            $query .= " ORDER BY so.order_id";
        }

        if (isset($data['order']) && ($data['order'] == 'desc')) {
            $query .= " DESC";
        } else {
            $query .= " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] > 0) {
                $query .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
            }
        }

        $query = $this->db->query($query);

        return [
            'data' => $query->rows,
            'total' => $total,
            'totalFiltered' => $totalFiltered
        ];
    }

    public function getSlotsByDayId($day_id)
    {
        return $this->db->query("
            SELECT ds_delivery_slot_id, delivery_slot 
            FROM " . DB_PREFIX . "ds_delivery_slot
            WHERE ds_day_id = " . (int) $this->db->escape($day_id) . " AND status = 1"
        )->rows;
    }

    public function getDaysLocalized()
    {
        $this->load->language('module/delivery_slot');
        return [
            [
                'id' => 1,
                'name' => $this->language->get('entry_saturday')
            ],
            [
                'id' => 2,
                'name' => $this->language->get('entry_sunday')
            ],
            [
                'id' => 3,
                'name' => $this->language->get('entry_monday')
            ],
            [
                'id' => 4,
                'name' => $this->language->get('entry_tuesday')
            ],
            [
                'id' => 5,
                'name' => $this->language->get('entry_wednesday')
            ],
            [
                'id' => 6,
                'name' => $this->language->get('entry_thursday')
            ],
            [
                'id' => 7,
                'name' => $this->language->get('entry_friday')
            ],
        ];
    }

    public function getSlotsTojson($data = array()) {
        $sql = "SELECT * FROM " . DB_PREFIX .$this->table . " ds ";
        $sql .= " WHERE ds.total_orders > (SELECT COUNT(o.order_id) FROM ds_delivery_slot_order o WHERE o.delivery_date = '".$data['dayValue']."' ) ";
        if($data['dayValue'] == date("m-d-Y")){
            $sql .= " AND ds.time_end > TIME( STR_TO_DATE( TIME_FORMAT(NOW(), '%h:%i %p'), '%h:%i %p' ) ) ";
        }
        $sql .= " AND ds.ds_day_id = ". $data['day_id'];
        $sql .= " AND ds.status = 1";
        $query = $this->db->query($sql);

        return $query->rows;
    }

    /**
     * Update order delivery slot data
     *
     * @param array $data
     * @return bool
     */
    public function updateOrderSlot($data = array()){
        // get slot data
        $query_slot_data = $this->db->query("SELECT  * FROM " . DB_PREFIX ."ds_delivery_slot WHERE ds_delivery_slot_id = '" . (int)$data['slot_id'] . "' ");
        $slotData = $query_slot_data->row;

        $querySlot = "UPDATE  ".DB_PREFIX."ds_delivery_slot_order SET ";
        $querySlot .= " ds_delivery_slot_id = ".(int)$data['slot_id'];
        $querySlot .= " , delivery_date = '".$data['slot_date']."'";
        $querySlot .= " , ds_day_id = ".(int)$slotData['ds_day_id'];
        $querySlot .= " , slot_description = '".$slotData['delivery_slot']."'";
        $querySlot .= " , day_name = '".$data['days'][date("w", strtotime($data['slot_date_dmy_format']))]."'";
        $querySlot .= " WHERE order_id =".(int)$data['order_id']." AND ds_delivery_slot_order_id = ".(int)$data['slot_order_id'];

        try {
            $this->db->query($querySlot);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * delete order delivery slot
     *
     * @param array $data
     * @return bool
     */
    public function deleteOrderSlot($data = array()){
        if(!isset($data['slot_order_id']))
            return false;

        $querySlot = "DELETE FROM " . DB_PREFIX . "ds_delivery_slot_order WHERE ds_delivery_slot_order_id = ".(int)$data['slot_order_id'];

        if(isset($data['order_id']))
            $querySlot .= " AND order_id = " . (int)$data['order_id'];

        try {
            $this->db->query($querySlot);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getBalance($data = array()) {
        $sql = "SELECT COUNT(ds_delivery_slot_id) AS total FROM ".DB_PREFIX."ds_delivery_slot_order ";
        $sql .= " WHERE ds_delivery_slot_id = ".$data['slot_id']." AND delivery_date = '".$data['balanceDate']."'";

        $query = $this->db->query($sql);
        return $query->row['total'];
    }
}
?>