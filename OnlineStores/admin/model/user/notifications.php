<?php

class ModelUserNotifications extends Model
{
    /**
     * Notification table name.
     *
     * @var string
     */
    private $notificationsTable = '`' . DB_PREFIX . 'notification`';
    private $adminNotificationsTable = '`' . DB_PREFIX . 'admin_notifications`';


    /**
     * Get latest $limit rows from notifications table.
     *
     * @param int $limit
     *
     * @return array|bool
     */
    public function getLatestNotifications($limit = 5)
    {
        $queryString = [];
        $queryString[] = 'SELECT * FROM '. $this->notificationsTable;
        $queryString[] = 'ORDER BY `id` DESC';
        $queryString[] = 'LIMIT 0, ' . $limit;

        $data = $this->db->query(implode(' ', $queryString));

        if ($data->num_rows) {
            return $data->rows;
        }

        return false;
    }

    /**
     * Get latest $limit rows from notifications table.
     *
     * @param int $limit
     *
     */
    public function getLatestAdminNotifications(int $limit = 5)
    {
        $queryString = [];
        $queryString[] = 'SELECT * FROM '. $this->adminNotificationsTable;
        $queryString[] = 'WHERE allow_read=1';
        $queryString[] = 'ORDER BY `id` DESC';
        if ($limit != -1){
            $queryString[] = 'LIMIT 0, ' . $limit;
        }

        $data = $this->db->query(implode(' ', $queryString));

        if ($data->num_rows) {
            return $data->rows;
        }

        return false;
    }

    public function markAsRead(){
        $this->db->query(
            "UPDATE " . DB_PREFIX . "admin_notifications SET `read_status` = 1"
        );
    }

    public function removeNotification($id){
        $this->db->query(
            "UPDATE " . DB_PREFIX . "admin_notifications SET `allow_read` = 0".
            " WHERE id =" .(int) $id
        );
    }
}
