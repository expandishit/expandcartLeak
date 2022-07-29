<?php

class ModelModuleSlotsReservationsSettings extends Model
{
    protected $errors = false;

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

    public function validateForm($data)
    {
        $settings = $this->getSettings();
        $requiredFields = array_flip($settings['required_fields']);

        if (isset($requiredFields['name'])) {
            if (
                isset($data['name']) == false ||
                mb_strlen($data['name']) == 0
            ) {
                $this->errors[] = 'The name input is required';
            }
        }

        if (isset($requiredFields['email'])) {
            if (
                isset($data['email']) == false ||
                mb_strlen($data['email']) == 0
            ) {
                $this->errors[] = 'The email input is required';
            }
        }

        if (mb_strlen($data['email']) > 0) {
            if (filter_var($data['email'], FILTER_VALIDATE_EMAIL) == false) {
                $this->errors[] = 'The given email is invalid , please set a valid email';
            }
        }

        if (isset($requiredFields['phone'])) {
            if (
                isset($data['phone']) == false ||
                mb_strlen($data['phone']) == 0
            ) {
                $this->errors[] = 'The phone input is required';
            }
        }

        return $this->errors ? false : true;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function insertSlot($data)
    {
        $query = $columns = [];
        $query[] = 'INSERT INTO slots_reservations SET';
        $columns[] = sprintf('reservation_date="%s"', $data['reservation_date']);
        $columns[] = sprintf('slot="%s"', $data['reservation_slot']);
        $columns[] = sprintf('dow=%d', $data['dow']);
        $query[] = implode(',', $columns);

        $this->db->query(implode(' ', $query));

        return $this->db->getLastId();
    }

    public function getSlotsByDateAndSlot($date, $slot)
    {
        $query = 'SELECT * FROM %s WHERE reservation_date="%s" AND slot="%s"';

        $data = $this->db->query(vsprintf($query, [
            'slots_reservations',
            $date,
            $slot
        ]));

        if ($data->num_rows > 0) {
            return $data->rows;
        }

        return false;
    }

    public function insertReservedSlot($slotId, $data)
    {
        $generateCode = function () {

            $code = bin2hex(random_bytes(10));

            $existingCode = $this->db->query(
                sprintf('SELECT 1 FROM reserved_slots WHERE `code` = "%s"', $code));

            if ($existingCode->num_rows > 0) {
                return $generateCode();
            }

            return $code;
        };

        $generatedCode = $generateCode();

        $query = $columns = [];
        $query[] = 'INSERT INTO reserved_slots SET';
        $columns[] = sprintf('code="%s"', $generatedCode);
        $columns[] = sprintf('slots_reservation_id=%d', $slotId);
        $columns[] = sprintf('name="%s"', $data['name']);
        $columns[] = sprintf('email="%s"', $data['email']);
        $columns[] = sprintf('phone="%s"', $data['phone']);
        $query[] = implode(',', $columns);

        $this->db->query(implode(' ', $query));

        return [
            'id' => $this->db->getLastId(),
            'code' => $generatedCode,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone']
        ];
    }

    public function incrementCount($id)
    {
        $this->db->query(sprintf('UPDATE %s SET count = count + 1 WHERE id=%d', 'slots_reservations', $id));
    }

    public function sendEmail($email, $slotInfo, $message)
    {
        $mail = new Mail();
        $mail->protocol = $this->config->get('config_mail_protocol');
        $mail->parameter = $this->config->get('config_mail_parameter');
        $mail->hostname = $this->config->get('config_smtp_host');
        $mail->username = $this->config->get('config_smtp_username');
        $mail->password = $this->config->get('config_smtp_password');
        $mail->port = $this->config->get('config_smtp_port');
        $mail->timeout = $this->config->get('config_smtp_timeout');
        $mail->setReplyTo(
            $this->config->get('config_mail_reply_to'),
            $this->config->get('config_name') ? $this->config->get('config_name')[0] : 'ExpandCart',
            $this->config->get('config_email')
        );
        $mail->setTo($email);
        $mail->setFrom((!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email')));
        $mail->setSender($this->config->get('config_name'));

        $mail->setSubject(html_entity_decode($this->language->get('email_template'), ENT_QUOTES, 'UTF-8'));

        $mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));

        $mail->send();
    }

    public function sendSMS($phone, $slotInfo)
    {
        if (\Extension::isInstalled('smshare')) {
            $this->load->model('module/smshare');

            $message = sprintf($this->language->get('sms_template'), $slotInfo['preview_link']);

            $this->model_module_smshare->sendSlotReservation($phone, $message);
        }
    }

    public function getReservedSlotByCode($code)
    {
        $query = 'SELECT * FROM %s WHERE `code` = "%s"';

        $data = $this->db->query(sprintf($query, 'reserved_slots', $code));

        if ($data->num_rows > 0) {
            return $data->row;
        }

        return false;
    }

    public function getSlotReservation($id)
    {
        $query = 'SELECT * FROM %s WHERE `id` = "%s"';

        $data = $this->db->query(sprintf($query, 'slots_reservations', $id));

        if ($data->num_rows > 0) {
            return $data->row;
        }

        return false;
    }
}
