<?php

class ModelModuleOrderNotification extends Model
{
    public function install() {
        $this->db->query("ALTER TABLE " . DB_PREFIX . "admin_notifications ADD COLUMN desktop_notified_ids varchar(255) DEFAULT NULL;");
    }

    public function uninstall() {
        $this->db->query("ALTER TABLE " . DB_PREFIX . "admin_notifications DROP COLUMN desktop_notified_ids");
    }

    public function getOrdersNotifications() {
        return $this->db->query("SELECT * FROM " . DB_PREFIX . "admin_notifications WHERE notification_module_code IN ('orders_new', 'return_new')")->rows;
    }

    public function updateDesktopNotified($id, $user_ids) {
        $this->db->query("UPDATE " . DB_PREFIX . "admin_notifications SET desktop_notified_ids='$user_ids' WHERE `id`='$id'");
    }

    public function getUserDateAdded($username) {
        return $this->db->query("SELECT date_added FROM " . DB_PREFIX . "user WHERE username='{$username}'")->row['date_added'];
    }

}