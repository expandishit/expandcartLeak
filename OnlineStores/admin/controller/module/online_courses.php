<?php

class ControllerModuleOnlineCourses extends Controller
{
    public function index()
    {
        $this->language->load('module/online_courses');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_modules'),
            'href'      => $this->url->link('marketplace/home', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('module/online_courses', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->initializer([
            'manualShipping' => 'module/online_courses/settings',
            'locales' => 'localisation/language'
        ]);

        $this->data['languages'] = $this->locales->getLanguages();

        $this->data['online_courses'] = $settings = $this->manualShipping->getSettings();

        $this->template = 'module/online_courses/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
    }

    public function updateSettings()
    {
        if (
            $this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)
        ) {
            return 'error';
        }

        $data = $this->request->post['online_courses'];

        $this->initializer([
            'module/online_courses/settings',
        ]);

        $this->language->load('module/online_courses');

        $this->settings->updateSettings(array_merge($this->settings->getSettings(), $data));

        $response['success_msg'] = $this->language->get('message_settings_updated');

        $response['success'] = '1';

        $this->response->setOutput(json_encode($response));

        return;
    }

    public function insert()
    {
        if (!\Extension::isInstalled('online_courses')) {
            $this->redirect($this->url->link('common/home', '', 'SSL'));
        }

        $config = $this->config->get('online_courses');

        if ($config['status'] != 1) {
            $this->redirect($this->url->link('common/home', '', 'SSL'));
        }

        if (
            isset($this->request->get['product_id']) == false ||
            preg_match('#^[0-9]+$#', $this->request->get['product_id']) == false) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'INVALID_REQUEST',
                'errors' => ['Invalid request'],
            ]));

            return;
        }

        $this->language->load('module/online_courses');

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_modules'),
            'href'      => $this->url->link('marketplace/home', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('insert_heading_title'),
            'href'      => $this->url->link('module/online_courses', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->document->setTitle($this->language->get('insert_heading_title'));

        $this->data['product_id'] = $productId = $this->request->get['product_id'];

        $this->initializer([
            'course' => 'module/online_courses/course',
            'locales' => 'localisation/language'
        ]);

        $this->data['lessons'] = $this->course->getCompactCourseByProductId($productId);

        $expiration = (function ($expiration) {

            if (!$expiration) {
                return null;
            }

            $type = substr($expiration, 0, 1);
            $period = substr($expiration, 1);

            if (!in_array($type, ['d', 'm', 'h', 'y'])) {
                $type = 'd';
            }

            if (!$period || !preg_match('#^[0-9]+$#', $period)) {
                $period = 0;
            }

            return [
                'period' => $period,
                'type' => $type,
            ];

        }) (current($this->data['lessons'])['expiration_period']);

        // $this->data['expiration'] = $expiration;

        // if (
        $this->data['expiration'] = $expiration;

        $this->data['languages'] = $this->locales->getLanguages();

        $this->data['language_id'] = $this->config->get('config_language_id');

        $this->template = 'module/online_courses/insert.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
    }

    public function install()
    {
        $this->initializer(['module/online_courses/settings']);
        $this->settings->install();
    }

    public function uninstall()
    {
        $this->initializer(['module/online_courses/settings']);
        $this->settings->uninstall();
    }

    public function storeLesson()
    {
        if (!\Extension::isInstalled('online_courses')) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'UNAUTHORIZED_ACTION',
                'errors' => ['Unauthorized action #1'],
            ]));

            return;
        }

        $config = $this->config->get('online_courses');

        if ($config['status'] != 1) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'UNAUTHORIZED_ACTION',
                'errors' => ['Unauthorized action #2'],
            ]));

            return;
        }

        $data = $this->request->post;

        if (
            isset($this->request->post['product_id']) == false ||
            preg_match('#^[0-9]+$#', $this->request->post['product_id']) == false) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'INVALID_REQUEST',
                'errors' => ['Invalid request'],
            ]));

            return;
        }

        $this->initializer([
            'course' => 'module/online_courses/course',
            'module/online_courses/settings',
        ]);

        if (!$this->settings->validateLesson($data)) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'INVALID_CREDENTIALS',
                'errors' => $this->settings->getErrors()
            ]));

            return;
        }

        $id = $this->course->insertCourseLesson($data);

        if ($id) {
            $this->response->setOutput(json_encode([
                'status' => 'OK',
                'id' => $id,
            ]));

            return;
        }

        $this->response->setOutput(json_encode([
            'status' => 'ERR',
            'error' => 'UNEXPECTED_ERROR',
            'errors' => ['Unexpected error'],
        ]));
    }

    public function store()
    {
        if (!\Extension::isInstalled('online_courses')) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'UNAUTHORIZED_ACTION',
                'errors' => ['Unauthorized action #1'],
            ]));

            return;
        }

        $config = $this->config->get('online_courses');

        if ($config['status'] != 1) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'UNAUTHORIZED_ACTION',
                'errors' => ['Unauthorized action #2'],
            ]));

            return;
        }

        $data = $this->request->post;

        if (
            isset($this->request->post['product_id']) == false ||
            preg_match('#^[0-9]+$#', $this->request->post['product_id']) == false) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'INVALID_REQUEST',
                'errors' => ['Invalid request'],
            ]));

            return;
        }

        $files = $this->request->files;

        $this->initializer([
            'course' => 'module/online_courses/course',
            'module/online_courses/settings',
        ]);

        if (!$this->settings->validate($data, $files)) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'INVALID_CREDENTIALS',
                'errors' => $this->settings->getErrors()
            ]));

            return;
        }

        $data['file_path'] = $this->course->upload(
            $files['file']['name'],
            $this->request->post['product_id'],
            file_get_contents($files['file']['tmp_name'])
        );

        if ($id = $this->course->insertCourseSession($data)) {
            $this->response->setOutput(json_encode([
                'status' => 'OK',
                'id' => $id,
                'lesson_id' => $data['lesson_id'],
                'file' => $data['file_path'],
                'file_string' => basename($data['file_path']),
                'title' => $data['session_title'][$this->config->get('config_language_id')],
                // 'titles' => $data['session_title'],
                'titles' => json_encode($data['session_title'], JSON_UNESCAPED_UNICODE),
                'download_count' => $data['download_count'],
            ]));

            return;
        }

        $this->response->setOutput(json_encode([
            'status' => 'ERR',
            'error' => 'UNEXPECTED_ERROR',
            'errors' => ['Unexpected error'],
        ]));
    }

    public function updateLesson()
    {
        if (!\Extension::isInstalled('online_courses')) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'UNAUTHORIZED_ACTION',
                'errors' => ['Unauthorized action #1'],
            ]));

            return;
        }

        $config = $this->config->get('online_courses');

        if ($config['status'] != 1) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'UNAUTHORIZED_ACTION',
                'errors' => ['Unauthorized action #2'],
            ]));

            return;
        }

        if (
            isset($this->request->post['lesson_id']) == false ||
            preg_match('#^[0-9]+$#', $this->request->post['lesson_id']) == false
        ) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'INVALID_REQUEST',
                'errors' => ['Invalid request'],
            ]));

            return;
        }

        $data = $this->request->post;
        $id = $this->request->post['lesson_id'];
        unset($data['lesson_id']);

        $this->initializer([
            'course' => 'module/online_courses/course',
            'module/online_courses/settings',
        ]);

        if (!$this->settings->validateLesson($data)) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'INVALID_CREDENTIALS',
                'errors' => $this->settings->getErrors()
            ]));

            return;
        }

        if (!$msg = $this->course->getLessonById($id)) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'UNDEFINED_ROW',
                'errors' => ['Undefined lesson'],
            ]));

            return;
        }

        if ($this->course->updateLessonById($id, $data)) {
            $this->response->setOutput(json_encode([
                'status' => 'OK',
                'id' => $id,
            ]));

            return;
        }

        $this->response->setOutput(json_encode([
            'status' => 'ERR',
            'error' => 'UNEXPECTED_ERROR',
            'errors' => ['Unexpected error'],
        ]));
    }

    public function updateSession()
    {
        if (!\Extension::isInstalled('online_courses')) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'UNAUTHORIZED_ACTION',
                'errors' => ['Unauthorized action #1'],
            ]));

            return;
        }

        $config = $this->config->get('online_courses');

        if ($config['status'] != 1) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'UNAUTHORIZED_ACTION',
                'errors' => ['Unauthorized action #2'],
            ]));

            return;
        }

        if (
            isset($this->request->post['session_id']) == false ||
            preg_match('#^[0-9]+$#', $this->request->post['session_id']) == false
        ) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'INVALID_REQUEST',
                'errors' => ['Invalid request'],
            ]));

            return;
        }

        $data = $this->request->post;
        $id = $this->request->post['session_id'];
        unset($data['session_id']);

        $this->initializer([
            'course' => 'module/online_courses/course',
            'module/online_courses/settings',
        ]);

        if (!$this->settings->validate($data, null)) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'INVALID_CREDENTIALS',
                'errors' => $this->settings->getErrors()
            ]));

            return;
        }

        if (!$msg = $this->course->getSessionById($id)) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'UNDEFINED_ROW',
                'errors' => ['Undefined session'],
            ]));

            return;
        }

        if ($this->course->updateSessionById($id, $data)) {

            $data['title'] = json_encode($data['session_title'], JSON_UNESCAPED_UNICODE);
            unset($data['session_title']);

            $fullData = (array_merge($msg, $data));

            $this->response->setOutput(json_encode([
                'status' => 'OK',
                'id' => $id,
                'data' => $fullData,
            ]));

            return;
        }

        $this->response->setOutput(json_encode([
            'status' => 'ERR',
            'error' => 'UNEXPECTED_ERROR',
            'errors' => ['Unexpected error'],
        ]));
    }

    public function updateExpiration()
    {
        if (!\Extension::isInstalled('online_courses')) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'UNAUTHORIZED_ACTION',
                'errors' => ['Unauthorized action #1'],
            ]));

            return;
        }

        $config = $this->config->get('online_courses');

        if ($config['status'] != 1) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'UNAUTHORIZED_ACTION',
                'errors' => ['Unauthorized action #2'],
            ]));

            return;
        }

        if (
            isset($this->request->post['product_id']) == false ||
            preg_match('#^[0-9]+$#', $this->request->post['product_id']) == false) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'INVALID_REQUEST',
                'errors' => ['Invalid request'],
            ]));

            return;
        }

        $data = $this->request->post;

        $this->initializer([
            'module/online_courses/course',
            'module/online_courses/settings',
        ]);

        if (!$this->settings->validateExpiration($data)) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'INVALID_CREDENTIALS',
                'errors' => $this->settings->getErrors()
            ]));

            return;
        }

        $exists = $this->course->getLessonByProductId($data['product_id']);

        if (!$exists) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'INVALID_CREDENTIALS',
                'errors' => ['You must add contents first']
            ]));

            return;
        }

        $expiration = $data['period_type'] . $data['expiration_period'];

        if ($this->course->updateExpirationByProductId($data['product_id'], $expiration)) {
            $this->response->setOutput(json_encode([
                'status' => 'OK',
            ]));
            return;
        }

        $this->response->setOutput(json_encode([
            'status' => 'ERR',
            'error' => 'UNEXPECTED_ERROR',
            'errors' => ['Unexpected error'],
        ]));
    }

    public function destroy()
    {
        if (!\Extension::isInstalled('online_courses')) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'UNAUTHORIZED_ACTION',
                'errors' => ['Unauthorized action #1'],
            ]));

            return;
        }

        $config = $this->config->get('online_courses');

        if ($config['status'] != 1) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'UNAUTHORIZED_ACTION',
                'errors' => ['Unauthorized action #2'],
            ]));

            return;
        }

        if (
            isset($this->request->post['id']) == false ||
            preg_match('#^[0-9]+$#', $this->request->post['id']) == false
        ) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'INVALID_REQUEST',
                'errors' => ['Invalid request'],
            ]));

            return;
        }

        $id = $this->request->post['id'];

        $this->initializer([
            'msGateways' => 'module/online_courses/gateways',
            'msgOrder' => 'module/online_courses/order',
        ]);

        if (!$msg = $this->msGateways->getCompactShippingGatewayById($id)) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'UNDEFINED_ROW',
                'errors' => ['Undefined gateway'],
            ]));

            return;
        }

        $orders = $this->msgOrder->getOrdersByManualShippingGatewayId($id);

        if ($orders && (!isset($this->request->post['_h']) || $this->request->post['_h'] != 1)) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'INVALID_CONSTRAINTS',
                'errors' => [''],
            ]));

            return;
        }

        if ($this->msGateways->deleteShippingGateway($id)) {
            if (isset($this->request->post['_h']) && (int)$this->request->post['_h'] == 1) {
                $this->msgOrder->nullOrderManualShippingGateways($id);
            }

            $this->response->setOutput(json_encode([
                'status' => 'OK',
            ]));

            return;
        }

        $this->response->setOutput(json_encode([
            'status' => 'ERR',
            'error' => 'UNEXPECTED_ERROR',
            'errors' => ['Unexpected error'],
        ]));
    }
}
