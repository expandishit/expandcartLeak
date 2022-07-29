<?php

use GuzzleHttp\Psr7;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;

class ControllerModuleMobileNotifications extends Controller
{
    public function index()
    {
        $this->language->load('module/notifications');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_modules'),
            'href' => $this->url->link('extension/payment', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('module/mobile/notifications', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->initializer(['module/mobile/notifications']);

        $this->template = 'module/mobile/notifications/browse.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
    }

    public function auth()
    {
        $this->initializer([
            'module/mobile/settings',
            'module/mobile/notifications',
        ]);

        $settings = $this->settings->getSettings();

        $data = $this->request->post;

        $this->notifications->setClientId(FIREBASE_CLIENTID);
        $this->notifications->setClientSecret(FIREBASE_CLIENTSECRET);
        $this->notifications->setRedirectUri(FIREBASE_REDIRECTURI);

        $link = $this->notifications->getAuthLink($data);

        $response['status'] = 'OK';
        $response['link'] = $link;

        $this->response->setOutput(json_encode($response));
        return;
    }

    public function insert()
    {
        $this->initializer([
            'module/mobile/settings',
            'module/mobile/notifications',
        ]);

        /*$settings = $this->settings->getSettings();

        $code = $this->request->get['code'];

        $state = json_decode(base64_decode($this->request->get['state']), true);

        $projectId = $settings['projectId'];

        $response = [];

        $this->notifications->setClientId(FIREBASE_CLIENTID);
        $this->notifications->setClientSecret(FIREBASE_CLIENTSECRET);
        $this->notifications->setRedirectUri(FIREBASE_REDIRECTURI);*/

        $settings = $this->settings->getSettings();

        $data = $this->request->post;

        if (isset($data['title']) == false || mb_strlen($data['title']) == 0) {
            $data['title'] = 'Untitled';
        }

        $data['name'] = $data['title'];
        $data['message_type'] = 'topic';
        $data['topic_name'] = 'all';
        $data['data'] = null;
        if (in_array($data['data-selector'], ['product', 'category'])) {
            $data['data'] = [
                'type' => $data['data-selector'],
                'id' => $data[$data['data-selector']]['id'],
                'name' => $data[$data['data-selector']]['name'],
                'image' => $data[$data['data-selector']]['image'],
            ];
        }

        if ($data['data-selector'] == 'deal') {
            $data['data'] = [
                'type' => 'deals',
            ];
        }

        $data['message_attributes'] = ['topic_name' => $data['topic_name'], 'data' => $data['data']];

        /*$devices = array_column(
            $this->notifications->getNotifiableUsersByDevice($data['type']), 'firebase_token'
        );

        $devices = array_chunk($devices, 100)[0];*/

        if ($notification = $this->notifications->pushNotificationToTopic($data)) {

            $state['data']['messageUri'] = '';//$notification['name'];

            $notificationId = $this->notifications->insertNotification($data);

            $this->response->setOutput(json_encode([
                'status' => 'OK',
            ]));
            return;
        }

        $this->response->setOutput(json_encode([
            'status' => 'ERR',
            'errors' => $this->notifications->getErrors()
        ]));
        return;
    }

    public function browse()
    {
        $this->initializer([
            'module/mobile/settings',
            'module/mobile/notifications',
        ]);

        $request = $_REQUEST;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        $start = $request['start'];
        $length = $request['length'];

        $columns = [
            0 => 'id',
            1 => 'name',
            2 => 'topic',
            3 => 'title',
            4 => 'body',
            5 => 'messageUri',
            6 => 'type'
        ];

        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];

        $return = $this->notifications->getNotifications($start, $length, $search, $orderColumn, $orderType, $columns);
        $records = $return['data'];
        $totalData = $return['total'];
        $totalFiltered = $return['totalFiltered'];

        $this->response->setOutput(json_encode([
            "draw" => intval($request['draw']),
            "recordsTotal" => intval($totalFiltered),
            "recordsFiltered" => $totalData,
            "data" => $records
        ]));
        return;
    }
}
