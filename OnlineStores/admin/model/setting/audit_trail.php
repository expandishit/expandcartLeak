<?php

class ModelSettingAuditTrail extends Model
{
    /**
     *  settings key.
     *
     * @var string
     */
    protected $settingsGroup = 'audit_trail';

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * update the module settings.
     *
     * @param array $inputs
     *
     * @return void
     */
    public function updateSettings($inputs)
    {
        $this->load->model('setting/setting');

        $this->model_setting_setting->insertUpdateSetting(
            'audit_trail', $inputs
        );
    }

    /**
     * Get  settings.
     *
     * @return array|null
     */
    public function getSettings()
    {
        return $this->config->get($this->settingsGroup);
    }

    public function getPageStatus($page)
    {
        $settings = $this->getSettings();

        if(count($settings) > 0 && isset($settings['pages']) && in_array($page,$settings['pages'])){
            return  true ;
        }
        return false;
    }

    public function getTotalLogs($data = array()) {

        $sql = "SELECT COUNT(DISTINCT log_history_id) AS total   FROM `" . DB_PREFIX . "log_history`";
        $implode = array();

        if (!empty($data['filter_date_start'])) {
            $implode[] = "DATE(date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
        }

        if (!empty($data['filter_date_end'])) {
            $implode[] = "DATE(date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
        }

        if ($data['filter_log_action'] != 'all') {
            $implode[] = " action = '".$data['filter_log_action']."' ";
        }

        if ($data['filter_type'] != 'all') {
            $implode[] = " type = '".$data['filter_type']."' ";
        }
        
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



    public function getLogsDataTable($data, $request, $columns)
    {

        $sql = "SELECT lh.log_history_id,lh.action,lh.date_added,lh.type,u.email ,CONCAT(u.firstname,' ' ,u.lastname) as username  FROM `" . DB_PREFIX . "log_history` lh LEFT JOIN `" . DB_PREFIX . "user` u ON (lh.user_id = u.user_id) ";

        $implode = array();

        if (!empty($data['filter_date_start'])) {
            $implode[] = "DATE(lh.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
        }

        if (!empty($data['filter_date_end'])) {
            $implode[] = "DATE(lh.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
        }

        if ($data['filter_log_action'] != 'all') {
            $implode[] = " action = '".$data['filter_log_action']."' ";
        }

        if ($data['filter_type'] != 'all') {
            $implode[] = " type = '".$data['filter_type']."' ";
        }

        if ($implode) {
            $sql .= " WHERE " . implode(" AND ", $implode);
        }

        if (!empty($data['search'])) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
            $sql .= " AND ( username LIKE '" . $request['search']['value'] . "%' ";
            $sql .= " OR u.email LIKE '" . $request['search']['value'] . "%' )";
        }


        $sql .= " ORDER BY " . $columns[$request['order'][0]['column']] . " " . $request['order'][0]['dir'] . "  LIMIT " . $request['start'] . " ," . $request['length'] . "   ";

        $query = $this->db->query($sql);

        $totalFiltered = $query->num_rows;

        $totalData = $query->rows;

        return ['data' => $totalData, 'totalFilter' => $totalFiltered];
    }


}
