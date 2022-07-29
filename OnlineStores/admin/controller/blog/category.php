<?php

class ControllerBlogCategory extends Controller
{
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
        $this->language->load('blog/category');

        $this->data['heading_title'] = $this->language->get('heading_title');
        $this->document->setTitle($this->data['heading_title']);

        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('blog/category')
        );

        $this->template = 'blog/category.expand';
        $this->base = 'common/base';

        $this->response->setOutput($this->render_ecwig());
    }

    public function dtHandler()
    {
        $this->load->model('blog/category');
        $request = $_REQUEST;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        $start = $request['start'];
        $length = $request['length'];

        $columns = array(
            0 => 'category_id',
            1 => 'name',
            2 => 'description',
            3 => 'category_status',
            4 => 'creation_date'
        );
        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];

        $return = $this->model_blog_category->dtHandler($start, $length, $search, $orderColumn, $orderType);
        $records = $return['data'];
        $totalData = $return['total'];
        $totalFiltered = $return['totalFiltered'];

        $json_data = array(
            "draw" => intval($request['draw']),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => $totalFiltered,
            "data" => $records
        );
        $this->response->setOutput(json_encode($json_data));
        return;
    }

    public function dtDelete()
    {
        $this->load->model("blog/category");

        if (isset($this->request->post['selected'])) {

            foreach ($this->request->post['selected'] as $id) {
                $this->model_blog_category->deleteCategoryDescription($id);
                $this->model_blog_category->deleteCategory($id);
            }

            $response['status'] = 'success';
            $response['title'] = $this->language->get('notification_success_title');
            $response['message'] = $this->language->get('message_deleted_successfully');
        } else {
            $response['status'] = 'error';
            $response['title'] = $this->language->get('notification_error_title');
            $response['errors'] = $this->error;
        }

        $this->response->setOutput(json_encode($response));
        return;
    }

    public function dtUpdateStatus()
    {
        $this->load->model("blog/category");
        if (isset($this->request->post["id"]) && isset($this->request->post["status"])) {
            $id = $this->request->post["id"];
            $status = $this->request->post["status"];

            $this->model_blog_category->dtUpdateStatus($id, $status);

            $response['status'] = 'success';
            $response['title'] = $this->language->get('notification_success_title');
            $response['message'] = $this->language->get('message_updated_successfully');
        } else {
            $response['status'] = 'error';
            $response['title'] = $this->language->get('notification_error_title');
            $response['errors'] = $this->language->get('notification_unknoen_error');
        }

        $this->response->setOutput(json_encode($response));
        return;
    }

    public function insert() {
        $this->language->load('blog/category');

        $this->load->model('localisation/language');

        $this->data['languages'] = $this->model_localisation_language->getLanguages();

        $this->data['heading_title'] = $this->language->get('heading_title');

        $this->document->setTitle($this->data['heading_title']);

        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('blog/category')
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('breadcrumb_insert'),
        );

        $this->load->model('blog/category');
        $this->load->model('tool/image');

        if (isset($this->request->post['category']) || isset($this->request->post['category_description'])) {
            $this->data['category'] = $this->request->post['category'];
            $this->data['category_description'] = $this->request->post['category_description'];
        } else {
            $this->data['category'] = null;
            $this->data['category_description'] = null;
        }

        $this->data['category']['category_thumb'] = $this->model_tool_image->resize('no_image.jpg', 150, 150);

        $this->data['no_image'] = $this->model_tool_image->resize('no_image.jpg', 150, 150);

        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            $result_json = array(
                'success' => '0',
                'errors' => array(),
                'success_msg' => ''
            );

            $category = $this->request->post['category'];
            $category['sort_order'] = 0;
            $category_description = $this->request->post['category_description'];

            if (!$this->model_blog_category->validate(['category' => $category, 'description' => $category_description])) {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->model_blog_category->errors;
            } else {
                $lastId = $this->model_blog_category->insertCategory($category);
                $this->model_blog_category->insertCategoryDescription($lastId, $category_description);
                $result_json['success'] = '1';
                $result_json['success_msg'] = $this->language->get('text_success');
                $result_json['redirect'] = '1';
                $result_json['to'] =  (string)$this->url->link('blog/category', '', 'SSL');
                //$this->redirect($this->url->link('blog/category/update', 'category_id=' . $lastId));
            }

            $this->response->setOutput(json_encode($result_json));
            return;
        }

        $this->data['action'] = $this->url->link('blog/category/insert');
        $this->template = 'blog/category_edit.expand';
        $this->base = 'common/base';

        $this->response->setOutput($this->render_ecwig());
    }

    public function update() {
        $this->language->load('blog/category');

        $this->load->model('localisation/language');

        $this->data['languages'] = $this->model_localisation_language->getLanguages();

        $this->data['heading_title'] = $this->language->get('heading_title');

        $this->document->setTitle($this->data['heading_title']);

        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('blog/category')
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('breadcrumb_update'),
        );

        $this->load->model('blog/category');
        $this->load->model('tool/image');
        $category_id = 0;
        if (isset($this->request->post['category']) || isset($this->request->post['category_description'])) {
            $category_id = $this->request->get['category_id'];
            $this->data['category'] = $this->request->post['category'];
            $this->data['category_description'] = $this->request->post['category_description'];
        } elseif(isset($this->request->get['category_id'])) {
            $category_id = $this->request->get['category_id'];
            $this->data['category'] = $this->model_blog_category->getCategoryById($category_id);
            $this->data['category_description'] = $this->model_blog_category->getCategoryDescriptionByCategoryId($category_id);
        } else {
            $this->redirect($this->url->link('blog/category/insert'));
        }

        if (!empty($this->data['category']) && $this->data['category']['category_image']) {
            $this->data['category']['category_thumb'] = $this->model_tool_image->resize(
                $this->data['category']['category_image'], 150, 150
            );
        } else {
            $this->data['category']['category_thumb'] = $this->model_tool_image->resize('no_image.jpg', 150, 150);
        }

        $this->data['no_image'] = $this->model_tool_image->resize('no_image.jpg', 150, 150);

        if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {
            $result_json = array(
                'success' => '0',
                'errors' => array(),
                'success_msg' => ''
            );

            $category = $this->request->post['category'];
            $category['sort_order'] = 0;
            $category_description = $this->request->post['category_description'];

            $validation = $this->model_blog_category->validate(['category' => $category, 'description' => $category_description]);

            if ( is_array( $validation ) )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $validation;
            }
            else
            {
                $this->model_blog_category->updateCategory($category_id, $category);
                $this->model_blog_category->updateCategoryDescription($category_id, $category_description);
                $result_json['success'] = '1';
                $result_json['success_msg'] = $this->language->get('text_success');
            }

            $this->response->setOutput(json_encode($result_json));
            return;
        }

        $this->data['action'] = $this->url->link('blog/category/update', "category_id=" . $category_id);
        $this->template = 'blog/category_edit.expand';
        $this->base = 'common/base';

        $this->response->setOutput($this->render_ecwig());
    }
}