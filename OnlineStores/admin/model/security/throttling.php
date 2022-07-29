<?php

class ModelSecurityThrottling extends Model
{
    /**
     * Sscurity settings object.
     *
     * @string var
     */
    private $settings = null;

    private $failedLoginsTable = DB_PREFIX . 'failed_logins';

    public function getIpsForDatatables($filter)
    {
        $query = [];

        $fields = 'id, INET_NTOA(ipv4) as ipv4, ban_status, attempts, recaptcha_status, created_at';

        $query[] = 'SELECT ' . $fields . ' FROM ' . $this->failedLoginsTable;

        if (!empty($filter['filter_name'])) {
            $query[] = " WHERE name LIKE '" . $this->db->escape($filter['filter_name']) . "%'";
        }

        $total = $this->db->query(str_replace($fields, 'COUNT(*) as dc', implode(' ', $query)))->row['dc'];

        $sort_data = array(
            'ipv4',
            'ban_status',
            'attempts',
            'recaptcha_status',
            'created_at',
        );

        if (isset($filter['sort']) && in_array($filter['sort'], $sort_data)) {
            $query[] = " ORDER BY " . $filter['sort'];
        } else {
            $query[] = " ORDER BY ipv4";
        }

        if (isset($filter['order']) && ($filter['order'] == 'DESC')) {
            $query[] = " DESC";
        } else {
            $query[] = " ASC";
        }

        if (isset($filter['start']) || isset($filter['limit'])) {
            if ($filter['start'] < 0) {
                $filter['start'] = 0;
            }

            if ($filter['limit'] < 1) {
                $filter['limit'] = 20;
            }

            $query[] = " LIMIT " . (int)$filter['start'] . "," . (int)$filter['limit'];
        }

        $queryData = $this->db->query(implode(' ', $query));

        $data = array(
            'data' => $queryData->rows,
            'total' => $total,
            'totalFiltered' => $queryData->num_rows
        );

        return $data;
    }

    public function deleteById($id)
    {
        $query = [];

        $query[] = 'DELETE FROM ' . $this->failedLoginsTable;
        $query[] = 'WHERE id="' . (int)$id . '"';

        $this->db->query(implode(' ', $query));
    }

    /**
     * Store the user ip in the $failedLoginsTable table.
     *
     * @param array $data
     *
     * @return void
     */
    public function banIp($data)
    {
        $query = $fields = [];

        $query[] = 'INSERT INTO ' . $this->failedLoginsTable . ' SET';
        $fields[] = 'ipv4=INET_ATON("' . $data['ipv4'] . '")';
        $fields[] = 'resource="' . $data['resource'] . '"';
        $fields[] = 'attempts="' . $data['attempts'] . '"';
        $fields[] = 'recaptcha_status="' . $data['recaptcha_status'] . '"';
        $query[] = implode(', ', $fields);

        $this->db->query(implode(' ', $query));
    }

    /**
     * Store the user ip in the $failedLoginsTable table.
     *
     * @param string $ipv4
     *
     * @return void
     */
    public function isBanned($ipv4)
    {
        $query = [];

        $query[] = 'SELECT * FROM ' . $this->failedLoginsTable;
        $query[] = 'WHERE ipv4=INET_ATON("' . $ipv4 . '")';

        $data = $this->db->query(implode(' ', $query));

        if ($data->num_rows) {
            return $data->rows;
        }

        return false;
    }

    /**
     * Get the throttlin settings.
     *
     * @return array|null
     */
    public function getSettings()
    {
        if (!$this->settings) {
            $this->settings = $this->config->get('throttling');
        }

        return $this->settings;
    }

    /**
     * Get the throttlin status.
     *
     * @return bool
     */
    public function throttlingStatus()
    {
        $settings = $this->getSettings();

        if (isset($settings['enable_throttling']) && $settings['enable_throttling'] == 1) {
            return true;
        }

        return false;
    }

    /**
     * Get the reCaptcha status.
     *
     * @return bool
     */
    public function reCaptchaStatus()
    {
        $settings = $this->getSettings();

        if (isset($settings['enable_recaptcha']) && $settings['enable_recaptcha'] == 1) {
            return true;
        }

        return false;
    }

    /**
     * Get the reCaptcha site key.
     *
     * @return string
     */
    public function reCaptchaSiteKey()
    {
        return $this->getSettings()['recaptcha_sitekey'];
    }
}
