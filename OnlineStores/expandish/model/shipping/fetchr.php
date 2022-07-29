<?php

class ModelShippingFetchr extends Model
{
    public function validateForm()
    {

    }

    public function updateStatus($orderId, $trackNo, $date)
    {
        $this->language->load_json('module/fetchr');

        $queryString = [];
        $queryString[] = 'UPDATE `'. DB_PREFIX .'order` SET';
        $queryString[] = 'order_status_id=' . $this->config->get('fetchr_already_shipped_status');
        $queryString[] = 'WHERE tracking=' . $trackNo;
        $query = $this->db->query(implode(' ', $queryString));

        $queryString = [];
        $queryString[] = 'INSERT INTO `'. DB_PREFIX .'order_history`';
        $queryString[] = '(order_id, order_status_id, notify, comment, date_added)';
        $queryString[] = 'VALUES';
        $queryString[] = "('$orderId', $this->config->get('fetchr_already_shipped_status'), '1', '" . $this->language->get('fetchr_api_update_status_comment') . "', '$date')";
        $query = $this->db->query(implode(' ', $queryString));
    }

    public function selectByTackingNumber($trackNo)
    {
        $queryString = [];
        $queryString[] = 'SELECT * FROM `'. DB_PREFIX .'order`';
        $queryString[] = 'WHERE tracking=' . $trackNo;
        return $this->db->query(implode(' ', $queryString));
    }

    public function checkModuleStatus()
    {
        $fetchr_servicetype = $this->config->get('fetchr_servicetype');

        if (isset($fetchr_servicetype)) {
            return true;
        }

        $checkColumn = $this->db->query('SHOW COLUMNS FROM `order` LIKE "tracking"');

        if ($checkColumn->num_rows >= 1) {
            return true;
        }

        return false;
    }
}
