<?php
/**
 * Track All Actions/Events occurred inside OnlineStore
 */
class AuditTrail
{
    private $db;
    private $user;
    private $table_name = 'audit_trail_events';
    public function __construct($registry)
    {
        # code...
        $this->db = $registry->get('db');
		$this->request = $registry->get('request');
        $this->user = $registry->get('user');
        $this->config = $registry->get('config');
    }

    private function checkStatus($group,$key)
    {
        $data = array();

		$query = $this->db->query("SELECT `value` FROM " . DB_PREFIX . "setting WHERE `key` = '" . $key . "' AND `group` = '" . $this->db->escape($group) . "'");

		if($query->num_rows == 1) {
            foreach ($query->rows as $row) {
               return $row['value'];
            }
		}
		return false;
    }

    public function log($data)
    {
        if($this->checkStatus('config','config_audit_trail')){
            $user_group = $this->user->getUserGroup() == ""?1:$this->user->getUserGroup();
            $data['resource_id'] = $data['resource_id'] == "" ? "null":$data['resource_id'];
            $sql_str = "insert into ".$this->table_name." values (null,".
                    $this->user->getId().",".
                    $data['resource_id'].",'".
                    $this->db->escape($data['resource_type'])."','".
                    $this->db->escape($data['event_type'])."','".
                    $this->db->escape($data['event_desc'])."',CURRENT_TIMESTAMP,'".
                    $this->db->escape($this->request->server['REMOTE_ADDR'])."','".
                    $this->db->escape($user_group)."')";
            
            $this->db->query($sql_str);
        }
    }
    
    public function getEventTypes()
    {
        # code...
        $sql_str = "SELECT * from audit_trail_event_type event_type join audit_trail_event_type_locales event_type_locale".
                   " ON event_type.event_type_id = event_type_locale.event_type_id ".
                   " WHERE language_id = ".(int)$this->config->get('config_language_id');
        $data = $this->db->query($sql_str);
        if($data->num_rows > 0)
            return $data->rows;
        
        return false;
    }

    public function get_events_data($start = 0, $length = 10, $filterData = null, $orderColumn = "event_id")
    {
        //total query
        $total_sql_str = "select count(*) AS total from ".$this->table_name;
        $total = ($this->db->query($total_sql_str)->row)['total'];

        $sql_str = "select SQL_CALC_FOUND_ROWS *, audit_trail_event_type_locales.event_type_text as event_type,
                    if(
                        (event_user_id = '999999999'),
                        ('admin'),
                        (
                            (select username from user where user_id = event_user_id)
                        )
                    ) as username,
                    if(
                        (event_user_id = '999999999'),
                        (
                            (select name from user_group where user_group_id = '1')
                        ),
                        (
                            (select name from user_group 
                             where user_group_id = event_user_group)
                        )
                    )
                    as event_user_group
                    from ".$this->table_name
                   ." join audit_trail_event_type on ".$this->table_name.".event_type_id = "
                   ." audit_trail_event_type.event_type_id join audit_trail_event_type_locales "
                   ." on audit_trail_event_type_locales.event_type_id = audit_trail_event_type.event_type_id "
                   ." Where audit_trail_event_type_locales.language_id =".(int)$this->config->get('config_language_id');

        $where = "";
        if (!empty($filterData['search'])) {
            $where .= "(event_description like '%" . $this->db->escape($filterData['search'])
            ."%' OR event_resource_id like '%".$this->db->escape($filterData['search'])
            ."%' OR event_user_ip like '%".$this->db->escape($filterData['search'])."%') ";
            $sql_str .= " AND " . $where;
        } 
             
        if (isset($filterData['user_group']) && !empty($filterData['user_group'])) {
            $sql_str .= ' AND (event_user_group like "'.$filterData['user_group'].'")';
        }

        if (isset($filterData['username']) && !empty($filterData['username'])) {
            $sql_str .= ' AND (event_user_id like "'.$filterData['username'].'")';
        }
        
        if (isset($filterData['event_type']) && !empty($filterData['event_type'])) {
            $sql_str .= ' AND ('.$this->table_name.'.event_type_id like "'.$filterData['event_type'].'")';
        }

        if (isset($filterData['date_added'])) {
            $startDate = null;
            $endDate = null;
            if (isset($filterData['date_added']['start']) && isset($filterData['date_added']['start'])) {
                $startDate = strtotime($filterData['date_added']['start']);
                $endDate = strtotime($filterData['date_added']['end']);
            }

            if (($startDate && $endDate) && $endDate > $startDate) {

                $formattedStartDate = date('Y-m-d', $startDate);
                $formattedEndDate = date('Y-m-d', $endDate);

                $sql_str .= 'AND (DATE(event_date_time) BETWEEN "' . $formattedStartDate . '" AND "' . $formattedEndDate . '")';
            } else if (($startDate && $endDate) && $endDate == $startDate) {
                $formattedStartDate = date('Y-m-d', $startDate);

                $sql_str .= 'AND (DATE(event_date_time)="' . $formattedStartDate . '")';
            }
        }

        $sql_str .= " ORDER by {$orderColumn} DESC";
        if ($length != -1) {
            $sql_str .= " LIMIT " . $start . ", " . $length;
        }

        $results = $this->db->query($sql_str)->rows;
        // to prevent retrieving all data if there is no filter, just get the count of filtered
        $totalFiltered = $this->db->query('SELECT FOUND_ROWS() AS count')->row;
        $data = array(
            'data' => $results,
            'total' => $total,
            'totalFiltered' => $totalFiltered['count']
        );

        return $data;
    }
}