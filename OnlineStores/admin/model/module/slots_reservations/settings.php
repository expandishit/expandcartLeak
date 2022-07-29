<?php

class ModelModuleSlotsReservationsSettings extends Model
{
    protected $errors;

    /**
     * update the module settings.
     *
     * @param array $inputs
     *
     * @return bool|\Exception
     */
    public function updateSettings($inputs)
    {
        $this->load->model('setting/setting');

        $this->model_setting_setting->editSetting(
            'slots_reservations', $inputs
        );

        return true;
    }

    public function install()
    {
        $data = [
            'status' => '0',
            'required_fields' => ['name', 'email', 'phone'],
            'notify_by_mail' => '0',
            'notify_by_sms' => '0',
            'days' => [
                1 => [
                    'open_at' => '08:00 AM',
                    'close_at' => '06:00 PM',
                    'slot_size' => '30',
                    'max_visitors' => '10',
                    'work_day' => 1
                ],
                2 => [
                    'open_at' => '08:00 AM',
                    'close_at' => '06:00 PM',
                    'slot_size' => '30',
                    'max_visitors' => '10',
                    'work_day' => 1
                ],
                3 => [
                    'open_at' => '08:00 AM',
                    'close_at' => '06:00 PM',
                    'slot_size' => '30',
                    'max_visitors' => '10',
                    'work_day' => 1
                ],
                4 => [
                    'open_at' => '08:00 AM',
                    'close_at' => '06:00 PM',
                    'slot_size' => '30',
                    'max_visitors' => '10',
                    'work_day' => 1
                ],
                5 => [
                    'open_at' => '08:00 AM',
                    'close_at' => '06:00 PM',
                    'slot_size' => '30',
                    'max_visitors' => '10',
                    'work_day' => 1
                ],
                6 => [
                    'open_at' => '08:00 AM',
                    'close_at' => '06:00 PM',
                    'slot_size' => '30',
                    'max_visitors' => '10',
                    'work_day' => 1
                ],
                7 => [
                    'open_at' => '08:00 AM',
                    'close_at' => '06:00 PM',
                    'slot_size' => '30',
                    'max_visitors' => '10',
                    'work_day' => 1
                ],
            ],
        ];
 
        $this->updateSettings(['slots_reservations' => $data]);

        $query = [];
        $query[] = 'CREATE TABLE IF NOT EXISTS `reserved_slots` (';
        $query[] = '`id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,';
        $query[] = '`code` varchar(255) DEFAULT NULL,';
        $query[] = '`slots_reservation_id` int(11) NOT NULL,';
        $query[] = '`customer_id` int(11) DEFAULT NULL,';
        $query[] = '`name` varchar(255) DEFAULT NULL,';
        $query[] = '`email` varchar(255) DEFAULT NULL,';
        $query[] = '`phone` varchar(255) DEFAULT NULL,';
        $query[] = '`created_at` datetime DEFAULT CURRENT_TIMESTAMP,';
        $query[] = '`updated_at` datetime DEFAULT NULL,';
        $query[] = 'KEY `slots_reservation_id` (`slots_reservation_id`),';
        $query[] = 'KEY `customer_id` (`customer_id`)';
        $query[] = ') ENGINE=InnoDB';
        $this->db->query(implode(' ', $query));

        $query = [];
        $query[] = 'CREATE TABLE IF NOT EXISTS `slots_reservations` (';
        $query[] = '`id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,';
        $query[] = '`reservation_date` date DEFAULT NULL,';
        $query[] = '`slot` varchar(50) DEFAULT NULL,';
        $query[] = '`count` int(11) DEFAULT "1",';
        $query[] = '`dow` smallint(6) DEFAULT "0",';
        $query[] = '`created_at` datetime DEFAULT CURRENT_TIMESTAMP,';
        $query[] = '`updated_at` datetime DEFAULT NULL';
        $query[] = ') ENGINE=InnoDB';
        $this->db->query(implode(' ', $query));

        $query = [];
        $query[] = 'ALTER TABLE `reserved_slots`';
        $query[] = 'ADD CONSTRAINT `customers_customer_id_foreign` FOREIGN KEY (`customer_id`)';
        $query[] = 'REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE,';
        $query[] = 'ADD CONSTRAINT `slots_reservations_slots_reservation_id_foreign` FOREIGN KEY (`slots_reservation_id`)';
        $query[] = 'REFERENCES `slots_reservations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE';
        $this->db->query(implode(' ', $query));
    }

    public function uninstall()
    {
        $this->db->query("DROP TABLE IF EXISTS `reserved_slots`");
        $this->db->query("DROP TABLE IF EXISTS `slots_reservations`");
    }

    public function getSettings()
    {
        return $this->config->get('slots_reservations');
    }

    public function isActive()
    {
        $settings = $this->getSettings();

        if (isset($settings) && $settings['status'] == 1) {
            return true;
        }

        return false;
    }

    public function listReservations($start, $length, $search, $orderColumn, $orderType)
    {
        $query = "SELECT * FROM slots_reservations";
        $total = $this->db->query($query)->num_rows;
        $where = "";

        $totalFiltered = $this->db->query($query)->num_rows;
        $query .= " ORDER by {$orderColumn} {$orderType}";

        if ($length != -1) {
            $query .= " LIMIT " . $start . ", " . $length;
        }

        $data = [
            'data' => $this->db->query($query)->rows,
            'total' => $total,
            'totalFiltered' => $totalFiltered
        ];

        return $data;
    }

    public function listReservedSlots($slotId, $start, $length, $search, $orderColumn, $orderType)
    {
        $query = "SELECT * FROM reserved_slots";
        $total = $this->db->query($query)->num_rows;
        $query .= " WHERE slots_reservation_id = " . $slotId;

        $totalFiltered = $this->db->query($query)->num_rows;
        // $query .= " ORDER by {$orderColumn} {$orderType}";

        if ($length != -1) {
            $query .= " LIMIT " . $start . ", " . $length;
        }

        $data = [
            'data' => $this->db->query($query)->rows,
            'total' => $total,
            'totalFiltered' => $totalFiltered
        ];

        return $data;
    }

    public function validateForm($data)
    {
        foreach ($data['days'] as $key => $day) {
            if (strtotime($day['open_at']) >= strtotime($day['close_at'])) {
                $this->errors['days'][$key][] = 'open time should be before close time';
            }

            if (in_array($day['slot_size'], [15, 30, 45, 60]) == false) {
                $this->errors['days'][$key][] = 'illegal slot size';
            }

            if ((int) $day['max_visitors'] < 1) {
                $this->errors['days'][$key][] = 'illegal visitor count';
            }
        }

        if ($this->errors) {
            return false;
        }

        return true;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
