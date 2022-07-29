<?php

class ControllerBlogComment extends Controller
{
    private $errors = array();

    private $plan_id = PRODUCTID;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('plan/trial');
        $trial = $this->model_plan_trial->getLastTrial();
        if($trial){
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
        $this->language->load('blog/comment');

        $this->data['heading_title'] = $this->language->get('heading_title');
        $this->document->setTitle($this->data['heading_title']);

        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('blog/comment')
        );

        $this->template = 'blog/comment.expand';
        $this->base = 'common/base';

        $this->response->setOutput($this->render_ecwig());
    }

    public function edit()
    {
        $commentId = (isset($this->request->get['comment_id']) ? $this->request->get['comment_id'] : null);

        if (!$commentId) {
            $this->session->data['errors'] = [$this->language->get('error_not_set_id')];

            $this->redirect(
                $this->url->link(
                    'blog/comment',
                    '',
                    'SSL'
                )
            );
        }

        $this->initializer([
            'blog/comment',
            'languageModel' => 'localisation/language'
        ]);

        $this->language->load('blog/flash_blog');

        $this->document->setTitle(
            $this->language->get('flash_blog_heading_title') .
            ' :: ' .
            $this->language->get('heading_edit_post')
        );

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('flash_blog_heading_title'),
            'href' => $this->url->link('blog/comment', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('breadcrumb_update'),
        );

        if (isset($this->session->data['flash'])) {
            $comment = $this->session->data['flash']['data']['comment'];
            unset($this->session->data['flash']);
        } else {
            $comment = $this->comment->getCommentById($commentId);
        }

        $this->data['comment'] = $comment[0];

        $this->data['links'] = [
            'submit' => $this->url->link('blog/comment/update',
                'comment_id=' . $commentId),
            'cancel' => $this->url->link('blog/comment'),
        ];

        $this->data['cancel'] = $this->url->link('blog/comment', '', 'SSL');

        $this->data['languages'] = $this->languageModel->getLanguages();

        $this->template = 'blog/comment/edit.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
    }

    public function update()
    {
        $this->language->load('blog/flash_blog');

        if ( $this->request->server['REQUEST_METHOD'] != 'POST' )
        {
            $this->redirect($this->url->link('blog/comment', '', 'SSL') );
        }

        $commentId = (isset($this->request->get['comment_id']) ? $this->request->get['comment_id'] : null);

        if ( ! $commentId )
        {
            $result_json['success'] = '0';
            $result_json['errors'] = array( 'warning' => $this->language->get('error_not_set_id') );
            $this->response->setOutput(json_encode($result_json));
            return;
        }

        $this->initializer([
            'blog/comment'
        ]);

        $comment = $this->request->post;

        if ( ! $this->validate(['comment' => $comment]) )
        {

            $result_json['success'] = '0';
            $result_json['errors'] = $this->errors;

            $this->response->setOutput(json_encode($result_json));

            return;
        }
//var_dump($comment);die;
        $commentToBeUpdated = $this->comment->getCommentById($commentId);


        if (! $commentToBeUpdated )
        {
            $result_json['success'] = '0';
            $result_json['errors'] = array( 'warning' => $this->language->get('error_not_set_id') ) ;
            $this->response->setOutput(json_encode($result_json));
            return;
        }

        $this->comment->updateComment($commentId, $comment);

        $result_json['success'] = '1';
        $result_json['success_msg'] = $this->language->get('message_post_edited_successfully');

        $this->response->setOutput(json_encode($result_json));

        return;
    }

    public function dtHandler()
    {
        $this->load->model('blog/comment');
        $request = $_REQUEST;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        $start = $request['start'];
        $length = $request['length'];

        $columns = array(
            0 => 'comment_id',
            1 => 'name',
            2 => 'email',
            3 => 'comment',
            4 => 'comment_status',
            5 => 'creation_date'
        );
        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];

        $return = $this->model_blog_comment->dtHandler($start, $length, $search, $orderColumn, $orderType);
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
        $this->load->model("blog/comment");

        if (isset($this->request->post['selected'])) {

            foreach ($this->request->post['selected'] as $id) {
                $this->model_blog_comment->deleteCommentById($id);
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
        $this->load->model("blog/comment");
        if (isset($this->request->post["id"]) && isset($this->request->post["status"])) {
            $id = $this->request->post["id"];
            $status = $this->request->post["status"];

            $this->model_blog_comment->updateCommentStatus($id, $status);

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

    private function validate( $data )
    {
        $comment = $data['comment'];

        if ( strlen( $comment['name'] ) <= 0 )
        {
            $this->errors['name'] = $this->language->get('error_not_isset_name');
        }

        if ( strlen( $comment['email'] ) <= 0  || !$this->validate_email($comment['email']))
        {
            $this->errors['email'] = $this->language->get('error_email_incorrect');
        }

        if ( strlen( $comment['comment'] ) <= 0 )
        {
            $this->errors['comment'] = $this->language->get('error_not_isset_comment');
        }

        return $this->errors ? false : true;
    }

    public function validate_email($email)
    {
        if (preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/', $email)) {
            return true;
        } else {
            return false;
        }
    }
}