<?php

class ControllerBlogSetting extends Controller
{
    private $errors = array();

    private $plan_id = PRODUCTID;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('plan/trial');
        $trial = $this->model_plan_trial->getLastTrial();
        if ($trial){
            $this->plan_id = $trial['plan_id'];
        }

        if($this->plan_id == 3){
            $this->redirect(
                $this->url->link('error/not_found', '', 'SSL')
            );
            exit();
        }
    }

    public function index() {
        $this->language->load('blog/setting');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('blog/settings');

        if (isset($this->request->post['blog_settings'])) {
            $this->data['blog_settings'] = $this->request->post['blog_settings'];
        } elseif ($this->config->get('flash_blog')) {
            $this->data['blog_settings'] = $this->config->get('flash_blog');
        } else {
            $this->data['blog_settings'] = null;
        }

        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('blog/setting')
        );

        if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {
            $result_json = array(
                'success' => '0',
                'errors' => array(),
                'success_msg' => ''
            );

            $data = $this->request->post['blog_settings'];

            if ( $this->validate($data) !== true )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->errors;

            }
            else
            {
                $this->model_blog_settings->updateSettings(['flash_blog' => $data]);
                $result_json['success'] = '1';
                $result_json['success_msg'] = $this->language->get('text_settings_success');
            }

            $this->response->setOutput(json_encode($result_json));
            return;
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL')
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('flash_blog_heading_title'),
            'href'      => $this->url->link('blog/setting')
        );

        $this->data['action'] = $this->url->link('blog/setting');
        $this->template = 'blog/setting.expand';
        $this->base = 'common/base';

        $this->response->setOutput($this->render_ecwig());
    }


    private function validate($data)
    {
        if (!isset($data['status']) || (!in_array($data['status'], [0, 1]))) {
            $this->errors['status'] = $this->language->get('error_status_field');
        }

        if (
            empty($data['maximum_index_blogs']) == false &&
            preg_match('#[0-9]+$#', $data['maximum_index_blogs']) == false
        ) {
            $this->errors['maximum_index_blogs'] = $this->language->get('error_maxmium_blogs_field');
        }

        if ( empty( $data['maximum_index_categories'] ) )
        {
            $this->errors['maximum_index_categories'] = $this->language->get('error_maxmium_categories_field');
        }

        if ( ! preg_match('#^[0-9]+$#', $data['maximum_index_categories'] ) )
        {
            $this->errors['maximum_index_categories'] = $this->language->get('error_maxmium_categories_field');
        }

        if ( empty( $data['maximum_index_blogs'] ) )
        {
            $this->errors['maximum_index_blogs'] = $this->language->get('error_maxmium_categories_field');
        }

        if ( ! preg_match('#^[0-9]+$#', $data['maximum_index_blogs'] ) )
        {
            $this->errors['maximum_index_blogs'] = $this->language->get('error_maxmium_categories_field');
        }

        return $this->errors ? false : true;
    }

}