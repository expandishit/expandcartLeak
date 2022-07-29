<?php

class ControllerAccountCourses extends Controller
{
    public function index()
    {
        if (!\Extension::isInstalled('online_courses') || !$this->customer->isLogged()) {
            $this->redirect($this->url->link('common/home', '', 'SSL'));
        }
        
        $config = $this->config->get('online_courses');
        
        if ($config['status'] != 1) {
            $this->redirect($this->url->link('common/home', '', 'SSL'));
        }
        
        if (defined('LOGIN_MODE') && LOGIN_MODE === 'identity' && $this->identity->isStoreOnWhiteList()) {
            return $this->renderContent();
        }
        
        $this->language->load_json('module/online_courses');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(           
            'text'      => $this->language->get('text_account'),
            'href'      => $this->url->link('account/account', '', 'SSL'),
            'separator' => $this->language->get('text_separator')
        );

        $this->data['breadcrumbs'][] = array(           
            'text'      => $this->language->get('text_courses'),
            'href'      => $this->url->link('account/courses', '', 'SSL'),
            'separator' => $this->language->get('text_separator')
        );

        $this->initializer([
            // 'module/online_courses/settings',
            'module/online_courses/course',
        ]);

        $this->data['courses'] = $this->course->getCoursesByCustomerId($this->customer->getId());

        $this->template = $this->checkTemplate('account/courses/list.expand');
        $this->response->setOutput($this->render_ecwig());
    }

    public function renderContent()
    {
        $this->data['taps'] = $this->getChild('account/taps/renderTaps', 'account-courses');
        
        $this->language->load_json('module/online_courses');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(           
            'text'      => $this->language->get('text_account'),
            'href'      => $this->url->link('account/account', '', 'SSL'),
            'separator' => $this->language->get('text_separator')
        );

        $this->data['breadcrumbs'][] = array(           
            'text'      => $this->language->get('text_courses'),
            'href'      => $this->url->link('account/courses', '', 'SSL'),
            'separator' => $this->language->get('text_separator')
        );

        $this->initializer([
            // 'module/online_courses/settings',
            'module/online_courses/course',
        ]);

        $this->data['courses'] = $this->course->getCoursesByCustomerId($this->customer->getId());

        $this->template = 'default/template/account/courses/list.expand';
        
        $this->response->setOutput($this->render_ecwig());
    }
    
    public function view()
    {
        if (!\Extension::isInstalled('online_courses') || !$this->customer->isLogged()) {
            $this->redirect($this->url->link('common/home', '', 'SSL'));
        }

        $config = $this->config->get('online_courses');

        if ($config['status'] != 1) {
            $this->redirect($this->url->link('common/home', '', 'SSL'));
        }

        if (
            !isset($this->request->get['product_id']) ||
            !preg_match('#^[0-9]+$#', $this->request->get['product_id'])
        ) {
            $this->redirect($this->url->link('account/courses', '', 'SSL'));
        }

        if (
            !isset($this->request->get['course_order_id']) ||
            !preg_match('#^[0-9]+$#', $this->request->get['course_order_id'])
        ) {
            $this->redirect($this->url->link('account/courses', '', 'SSL'));
        }

        $this->language->load_json('module/online_courses');

        $this->document->setTitle($this->language->get('heading_title'));

        $productId = $this->request->get['product_id'];
        $this->data['course_order_id'] = $courseOrderId = $this->request->get['course_order_id'];

        $this->initializer([
            'module/online_courses/course',
        ]);

        $order = $this->course->getCourseOrderByOrderId($courseOrderId);

        if (!$order) {
            $this->redirect($this->url->link('account/courses', '', 'SSL'));
        }

        if ($order['customer_id'] != $this->customer->getId()) {
            $this->redirect($this->url->link('account/courses', '', 'SSL'));
        }

        $this->data['counts'] = $order['download_count'] ? json_decode($order['download_count'], true) : [];

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

        $this->data['language_id'] = $this->config->get('config_language_id');

        $this->template = $this->checkTemplate('account/courses/view.expand');

        $this->response->setOutput($this->render_ecwig());
    }

    public function download()
    {
        if (!\Extension::isInstalled('online_courses') || !$this->customer->isLogged()) {
            $this->redirect($this->url->link('common/home', '', 'SSL'));
        }

        $config = $this->config->get('online_courses');

        if ($config['status'] != 1) {
            $this->redirect($this->url->link('common/home', '', 'SSL'));
        }

        if (
            !isset($this->request->get['product_id']) ||
            !preg_match('#^[0-9]+$#', $this->request->get['product_id'])
        ) {
            $this->redirect($this->url->link('account/courses', '', 'SSL'));
        }

        if (
            !isset($this->request->get['lesson_id']) ||
            !preg_match('#^[0-9]+$#', $this->request->get['lesson_id'])
        ) {
            $this->redirect($this->url->link('account/courses', '', 'SSL'));
        }

        if (
            !isset($this->request->get['course_order_id']) ||
            !preg_match('#^[0-9]+$#', $this->request->get['course_order_id'])
        ) {
            $this->redirect($this->url->link('account/courses', '', 'SSL'));
        }

        $this->initializer([
            'course' => 'module/online_courses/course',
        ]);

        $courseOrderId = $this->request->get['course_order_id'];

        $courseOrder = $this->course->getCourseOrderByOrderId($courseOrderId);

        if (!$courseOrder) {
            $this->redirect($this->url->link('account/courses', '', 'SSL'));
        }

        if ($courseOrder['customer_id'] != $this->customer->getId()) {
            $this->redirect($this->url->link('account/courses', '', 'SSL'));
        }

        $order = $this->course->getOrderByOrderProductId($courseOrder['order_product_id']);

        if (!$order) {
            $this->redirect($this->url->link('account/courses', '', 'SSL'));
        }

        $productId = $this->request->get['product_id'];
        $lessonId = $this->request->get['lesson_id'];

        $lesson = $this->course->getLessonById($lessonId);

        if (!$lesson) {
            $this->redirect($this->url->link('account/courses', '', 'SSL'));
        }

        if ($lesson['product_id'] != $productId) {
            $this->redirect($this->url->link('account/courses', '', 'SSL'));
        }

        $counts = $courseOrder['download_count'] ? json_decode($courseOrder['download_count'], true) : [];

        if (!isset($counts[$lesson['id']])) {
            $counts[$lesson['id']] = 0;
        }

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

            $types = [
                'd' => 'day',
                'm' => 'month',
                'h' => 'hour',
                'y' => 'year'
            ];

            return sprintf('+%d %s', $period, $types[$type]);

        }) ($lesson['expiration_period']);

        $dateAdded = new \Datetime($order['date_added']);

        $dateAdded->modify($expiration);

        $now = new \Datetime();
        /*
        if ($now->diff($dateAdded, false)->invert == 1) {
            $this->redirect($this->url->link('account/courses', '', 'SSL'));
        }*/

        $counts[$lesson['id']]++;

        if ($lesson['download_count'] != 0 && $counts[$lesson['id']] > $lesson['download_count']) {
            $this->redirect($this->url->link('account/courses', '', 'SSL'));
        }

        $this->course->updateDownloadCountBySessionId($courseOrder['id'], $counts);

        echo $this->course->download($lesson['file_path']);exit;
    }
}
