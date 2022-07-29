<?php

use Endroid\QrCode\QrCode;

class ControllerModuleSlotsReservations extends Controller
{
    public function index()
    {
        if (\Extension::isInstalled('slots_reservations') == false) {
            throw new \Exception('application is not installed');
        }

        $this->language->load_json('module/slots_reservations');

        $this->initializer([
            'module/slots_reservations/settings'
        ]);

        $settings = $this->settings->getSettings();

        if (isset($settings['status']) == false || (int)$settings['status'] == 0) {
            throw new \Exception('application is not enabled');
        }

        $today = date("Y-m-d");
        $dayofweek = (date('w', strtotime($today)) + 2) % 7;
        $dayofweek = ($dayofweek === 0 ? 7 : $dayofweek);

        $this->data['days'] = $days = $settings['days'];
        $this->data['day'] = $day = $days[$dayofweek];

        $start = DateTime::createFromFormat("Y-m-d h:i A", date("Y-m-d") . " " . $day['open_at']);
        $close = DateTime::createFromFormat("Y-m-d h:i A", date("Y-m-d") . " " . $day['close_at']);
        $current = $start;

        $slotTime = $day['slot_size'];

        if (!in_array($slotTime, [15, 30, 45, 60])) {
            $slotTime = 15;
        }

        $allSlots = $slots = [];

        while ($current <= $close) {
            $slot = $current->format("h:i A");
            $allSlots[$slot] = [
                'format' => $slot,
                'available' => $day['max_visitors'],
                'work_day' => $day['work_day']
            ];
            $slots[] = $slot;
            $current = $current->add(new DateInterval('PT' . $slotTime . 'M'));
        }

        $q = $this->db->query(
            sprintf('SELECT * FROM slots_reservations WHERE reservation_date="%s" AND slot IN ("%s")',
                $today, implode('","', $slots))
        );

        $availableSlots = array_column($q->rows, null, 'slot');

        foreach ($allSlots as &$slot) {
            if (isset($availableSlots[$slot['format']])) {
                $slot['available'] -= $availableSlots[$slot['format']]['count'];
            }
        }

        $this->data['allSlots'] = $allSlots;

        $availableSlots = [];

        $this->data['fields'] = $settings['required_fields'];

        $this->template = $this->checkTemplate('module/slots_reservations/calendar.expand');

        $this->response->setOutput($this->render_ecwig());
    }

    public function getSlots()
    {
        if (!isset($this->request->post['day'])/* || (int)$this->request->post['day'] < 1*/) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => '',
                'errors' => []
            ]));
            return;
        }

        $daydate = $this->request->post['day'];

        $this->initializer([
            'module/slots_reservations/settings'
        ]);

        $settings = $this->settings->getSettings();

        if (isset($settings['status']) == false || (int)$settings['status'] == 0) {
            throw new \Exception('application is not enabled');
        }

        $today = date($daydate);
        $dayofweek = (date('w', strtotime($today)) + 2) % 7;
        $dayofweek = ($dayofweek === 0 ? 7 : $dayofweek);

        $this->data['days'] = $days = $settings['days'];
        $this->data['day'] = $day = $days[$dayofweek];

        $start = DateTime::createFromFormat("Y-m-d h:i A", date("Y-m-d") . " " . $day['open_at']);
        $close = DateTime::createFromFormat("Y-m-d h:i A", date("Y-m-d") . " " . $day['close_at']);
        $current = $start;

        $slotTime = $day['slot_size'];

        if (!in_array($slotTime, [15, 30, 45, 60])) {
            $slotTime = 15;
        }

        $allSlots = $slots = [];

        while ($current <= $close) {
            $slot = $current->format("h:i A");
            $allSlots[$slot] = [
                'format' => $slot,
                'available' => $day['max_visitors'],
                'work_day' => $day['work_day']
            ];
            $slots[] = $slot;
            $current = $current->add(new DateInterval('PT' . $slotTime . 'M'));
        }

        $q = $this->db->query(
            sprintf('SELECT * FROM slots_reservations WHERE reservation_date="%s" AND slot IN ("%s")',
                $today, implode('","', $slots))
        );

        $availableSlots = array_column($q->rows, null, 'slot');

        foreach ($allSlots as &$slot) {
            if (isset($availableSlots[$slot['format']])) {
                $slot['available'] -= $availableSlots[$slot['format']]['count'];
            }
        }

        $data = $day;
        $data['slots'] = $allSlots;
        $data['day_date'] = $daydate;

        $this->response->setOutput(json_encode([
            'status' => 'OK',
            'data' => $data,
        ]));
        return;
    }

    public function store()
    {
        $this->language->load_json('module/slots_reservations');

        if (isset($this->request->post) == false) {
            return $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => '',
                'errors' => []
            ]));
        }

        $this->initializer([
            'module/slots_reservations/settings'
        ]);

        if (!$this->settings->validateForm($this->request->post)) {
            return $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'INVALID_CREDENTIALS',
                'errors' => $this->settings->getErrors()
            ]));
        }

        $settings = $this->settings->getSettings();

        if (isset($settings['status']) == false || (int)$settings['status'] == 0) {
            throw new \Exception('application is not enabled');
        }

        $data = $this->request->post;

        $date = $data['reservation_date'];
        $slot = $data['reservation_slot'];

        $today = date($date);
        $dayofweek = (date('w', strtotime($today)) + 2) % 7;
        $dayofweek = ($dayofweek === 0 ? 7 : $dayofweek);

        $existingSlot = $this->settings->getSlotsByDateAndSlot($date, $slot);
        $existingSlot = $existingSlot[0];

        $currentDay = $settings['days'][$dayofweek];

        if ($existingSlot['count'] >= $currentDay['max_visitors']) {
            return $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'INVALID_REQUEST',
                'errors' => ['Slot had been reached the maximum slots']
            ]));
        }

        $slotId = $existingSlot['id'];

        if (!$existingSlot) {
            $slotId = $this->settings->insertSlot($data);
        } else {
            $this->settings->incrementCount($slotId);
        }

        $slotInfo = $this->settings->insertReservedSlot($slotId, $data);
        $slotInfo['reservation_time'] = $date . ' ' . $slot;
        $slotInfo['emailed'] = false;
        $slotInfo['sms_sent'] = false;

        $slotInfo['time'] = $data['reservation_date'] . ' ' . $data['reservation_slot'];

        $slotInfo['preview_link'] = $this->url->link(
            'module/slots_reservations/show&code=' . $slotInfo['code'],
            '',
            'SSL'
        );

        if (isset($data['email']) && isset($settings['notify_by_mail']) && (int)$settings['notify_by_mail'] == 1) {
            $mailLangs = [];
            $mailLangs['code'] = $this->language->get('code');
            $mailLangs['direction'] = $this->language->get('direction');
            $mailLangs['reservation_info'] = $this->language->get('reservation_info');
            $mailLangs['reservation_code'] = $this->language->get('reservation_code');
            $mailLangs['reservation_time'] = $this->language->get('reservation_time');
            $mailLangs['reservation_name'] = $this->language->get('reservation_name');
            $mailLangs['reservation_email'] = $this->language->get('reservation_email');
            $mailLangs['reservation_phone'] = $this->language->get('reservation_phone');
            $mailLangs['reservation_link'] = $this->language->get('reservation_link');
            $mailLangs['reservation_hint'] = $this->language->get('reservation_hint');
            $this->data['mailLangs'] = $mailLangs;
            $this->data['slot'] = $slotInfo;
            $message = $this->renderDefaultTemplate('template/module/slots_reservations/email_template.expand');
            $this->settings->sendEmail($data['email'], $slotInfo, $message);
            $slotInfo['emailed'] = true;
        }

        if (isset($data['phone']) && isset($settings['notify_by_sms']) && (int)$settings['notify_by_sms'] == 1) {
            $this->settings->sendSMS($data['phone'], $slotInfo);
            $slotInfo['sms_sent'] = true;
        }

        return $this->response->setOutput(json_encode([
            'status' => 'OK',
            'data' => $slotInfo,
        ]));
    }

    public function show()
    {
        $this->language->load_json('module/slots_reservations');
        $this->language->load_json('global');

        if (isset($this->request->get['code']) == false) {
            throw new \Exception('code parameter is missed');
        }

        $code = $this->request->get['code'];

        if (strlen($code) != 20 || preg_match('#^[0-9a-z]+$#', $code) == false) {
            throw new \Exception('invalid code parameter');
        }

        $this->initializer([
            'module/slots_reservations/settings'
        ]);

        $settings = $this->settings->getSettings();

        if (isset($settings['status']) == false || (int)$settings['status'] == 0) {
            throw new \Exception('application is not enabled');
        }

        $qrCode = new QrCode($code);

        $slot = $this->settings->getReservedSlotByCode($code);

        $slotReservation = $this->settings->getSlotReservation($slot['slots_reservation_id']);

        $slot['time'] = $slotReservation['reservation_date'] . ' ' . $slotReservation['slot'];

        $slot['qr_image'] = base64_encode($qrCode->writeString());

        $this->data['slot'] = $slot;

        $this->template = $this->checkTemplate('module/slots_reservations/show.expand');

        $this->response->setOutput($this->render_ecwig());
    }

    public function scanner()
    {
        $this->language->load_json('module/slots_reservations');
        $this->language->load_json('global');

        $this->initializer([
            'module/slots_reservations/settings'
        ]);

        $settings = $this->settings->getSettings();

        if (isset($settings['status']) == false || (int)$settings['status'] == 0) {
            throw new \Exception('application is not enabled');
        }

        $this->template = $this->checkTemplate('module/slots_reservations/scanner.expand');

        $this->response->setOutput($this->render_ecwig());
    }

    public function scan()
    {
        $this->language->load_json('module/slots_reservations');
        $this->language->load_json('global');

        if (isset($this->request->post['code']) == false) {
            throw new \Exception('code parameter is missed');
        }

        $code = $this->request->post['code'];

        if (strlen($code) != 20 || preg_match('#^[0-9a-z]+$#', $code) == false) {
            throw new \Exception('invalid code parameter');
        }

        $this->initializer([
            'module/slots_reservations/settings'
        ]);

        $settings = $this->settings->getSettings();

        if (isset($settings['status']) == false || (int)$settings['status'] == 0) {
            throw new \Exception('application is not enabled');
        }

        $slot = $this->settings->getReservedSlotByCode($code);

        $slotReservation = $this->settings->getSlotReservation($slot['slots_reservation_id']);

        $slot['time'] = $slotReservation['reservation_date'] . ' ' . $slotReservation['slot'];

        return $this->response->setOutput(json_encode([
            'status' => 'OK',
            'data' => $slot,
        ]));
    }
}
