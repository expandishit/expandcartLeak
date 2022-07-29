<?php

use ExpandCart\Foundation\Support\Facades\GetResponseFactory as GetResponse;

class ModelModuleOnlineCoursesSettings extends Model
{
    /**
     * The settings key string
     *
     * @var string
     */
    protected $settingsKey = 'online_courses';

    /**
     * @var array
     */
    protected $errors = null;

    /**
     * Update the module settings.
     *
     * @param array $inputs
     *
     * @return bool|\Exception
     */
    public function updateSettings($inputs)
    {
        $this->load->model('setting/setting');

        $this->model_setting_setting->insertUpdateSetting(
            'module', [$this->settingsKey => $inputs]
        );

        return true;
    }

    /**
     * Get settings.
     *
     * @return array|bool
     */
    public function getSettings()
    {
        return ($this->config->get($this->settingsKey) ?: []);
    }

    /**
     * validate inputs
     *
     * @param array $data
     *
     * return bool
     */
    public function validate($data, $files = false)
    {
        $this->errors = [];

        if (isset($data['session_title']) == false || is_array($data['session_title']) == false) {
            $this->errors[] = 'session title field is required';
        }

        if (is_array($data['session_title'])) {
            foreach ($data['session_title'] as $title) {
                if (mb_strlen($title) < 1) {
                    $this->errors[] = 'session title field is required';
                }
            }
        }

        if (
            !isset($data['download_count']) ||
            !preg_match('#^[0-9]+$#', $data['download_count'])
        ) {
            $this->errors[] = 'Invalid download count';
        }

        if (is_array($files)) {
            if (isset($files['file']['name']) == false || $files['file']['error'] > 1) {
                $this->errors[] = 'Error in upload file';
            }
        }

        if ($this->errors) {
            return false;
        }

        return true;
    }

    public function validateLesson($data)
    {
        if (mb_strlen($data['title']) < 1) {
            $this->errors[] = 'title field is required';
        }

        if ($this->errors) {
            return false;
        }

        return true;
    }

    public function validateExpiration($data)
    {
        if (
            !isset($data['expiration_period']) ||
            !preg_match('#^[0-9]+$#', $data['expiration_period'])
        ) {
            $this->errors[] = 'invalid expiration field , it should only numeric values';
        }

        if (!in_array($data['period_type'], ['d', 'm', 'h', 'y'])) {
            $this->errors[] = 'invalid expiration type';
        }

        if ($this->errors) {
            return false;
        }

        return true;
    }

    /**
     * Push an error to the errors array.
     *
     * @param mixed $error
     *
     * @return void
     */
    public function setError($error)
    {
        $this->errors[] = $error;
    }

    /**
     * Get all errors.
     *
     * @return array
     */
    public function getErrors()
    {
        return is_array($this->errors) ? $this->errors : [];
    }

    /**
     * Install and apply the required DB changes.
     *
     * @return void
     */
    public function install()
    {
        $columns = [];
        $columns[] = '`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY';
        $columns[] = '`title` TEXT NULL';
        $columns[] = '`status` TINYINT NOT NULL DEFAULT "1"';
        $columns[] = '`download_count` INT NULL DEFAULT NULL';
        $columns[] = '`expiration_period` VARCHAR(10) NULL DEFAULT NULL';
        $columns[] = '`file_path` VARCHAR(255) NULL DEFAULT NULL';
        $columns[] = '`product_id` INT NOT NULL';
        $columns[] = '`parent_id` INT NULL DEFAULT "0"';
        $columns[] = '`created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP';
        $columns[] = '`updated_at` DATETIME NULL DEFAULT NULL';
        $this->db->query(sprintf(
            'CREATE TABLE `%s` (%s) ENGINE = InnoDB CHARSET=utf8 COLLATE=utf8_general_ci',
            'app_online_courses',
            implode(', ', $columns)
        ));

        $columns = [];
        $columns[] = '`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY';
        // $columns[] = '`online_course_id` INT NULL';
        $columns[] = '`order_product_id` INT NULL';
        $columns[] = '`customer_id` INT NULL';
        $columns[] = '`expire_date` text NULL';
        $columns[] = '`download_count` text NULL';
        $columns[] = '`created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP';
        $columns[] = '`updated_at` DATETIME NULL DEFAULT NULL';
        $this->db->query(sprintf(
            'CREATE TABLE `%s` (%s) ENGINE = InnoDB CHARSET=utf8 COLLATE=utf8_general_ci',
            'app_online_courses_orders',
            implode(', ', $columns)
        ));

        /*$this->db->query('ALTER TABLE `app_online_courses_orders` ADD INDEX(`online_course_id`)');
        $this->db->query('ALTER TABLE `app_online_courses_orders`
            ADD CONSTRAINT `app_online_courses_orders_online_course_id_foreign`
            FOREIGN KEY (`online_course_id`) REFERENCES `app_online_courses`(`id`)
            ON DELETE CASCADE ON UPDATE CASCADE;');*/

        $this->db->query('ALTER TABLE `app_online_courses_orders` ADD INDEX(`order_product_id`)');
        $this->db->query('ALTER TABLE `app_online_courses_orders`
            ADD CONSTRAINT `app_online_courses_orders_order_product_id_foreign`
            FOREIGN KEY (`order_product_id`) REFERENCES `order_product`(`order_product_id`)
            ON DELETE CASCADE ON UPDATE CASCADE;');

        $this->db->query('ALTER TABLE `app_online_courses_orders` ADD INDEX(`customer_id`)');
        $this->db->query('ALTER TABLE `app_online_courses_orders`
            ADD CONSTRAINT `app_online_courses_orders_customer_id_foreign`
            FOREIGN KEY (`customer_id`) REFERENCES `customer`(`customer_id`)
            ON DELETE CASCADE ON UPDATE CASCADE;');
    }

    /**
     * To drop the application related changes.
     *
     * @return void
     */
    public function uninstall()
    {
        /*$this->db->query(sprintf(
            'ALTER TABLE `%s` DROP FOREIGN KEY `app_online_courses_orders_online_course_id_foreign`',
            'app_online_courses_orders'
        ));*/
        $this->db->query(sprintf(
            'ALTER TABLE `%s` DROP FOREIGN KEY `app_online_courses_orders_order_product_id_foreign`',
            'app_online_courses_orders'
        ));
        $this->db->query(sprintf(
            'ALTER TABLE `%s` DROP FOREIGN KEY `app_online_courses_orders_customer_id_foreign`',
            'app_online_courses_orders'
        ));

        $this->db->query('DROP TABLE IF EXISTS `app_online_courses_orders`');
        $this->db->query('DROP TABLE IF EXISTS `app_online_courses`');

    }
}
