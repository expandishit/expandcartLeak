<?php

class ControllerBlogPost extends Controller
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

    public function index()
    {
        $this->language->load('blog/post');

        $this->data['heading_title'] = $this->language->get('heading_title');
        $this->document->setTitle($this->data['heading_title']);

        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('blog/post')
        );

        $this->template = 'blog/post.expand';
        $this->base = 'common/base';

        $this->response->setOutput($this->render_ecwig());
    }

    
    private function validate( $data )
    {
        $post = $data['post'];
        $descriptions = $data['description'];

        foreach ( $descriptions as $language_id => $p )
        {
            if ( strlen( $p['name'] ) <= 0 && (int) $p['post_status'] == 1 )
            {
                $this->errors['name_' . $language_id] = $this->language->get('error_not_isset_name');
            }
        }

        if ( ! $this->request->post['post']['parent_id'] )
        {
            $this->errors['parent_id'] = $this->language->get('error_field_cant_be_empty');
        }

        return $this->errors ? false : true;
    }


    public function create()
    {
        $categoryId = (isset($this->request->get['category_id']) ? $this->request->get['category_id'] : null);

        $this->initializer([
            'blog/post',
            'blog/category',
            'tool/image',
        ]);

        $this->language->load('blog/flash_blog');

        $this->document->setTitle(
            $this->language->get('flash_blog_heading_title') .
            ' :: ' .
            $this->language->get('heading_create_new_post')
        );

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('flash_blog_heading_title'),
            'href' => $this->url->link('blog/post', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('breadcrumb_insert'),
            'href' => $this->url->link('blog/post')
        );

        $this->data['categories'] = $this->category->getAllCategories();

        $this->load->model('localisation/language');

        $this->data['languages'] = $this->model_localisation_language->getLanguages();

        if (isset($this->session->data['flash'])) {
            $post = $this->session->data['flash']['data']['post'];
            $postDescription = $this->session->data['flash']['data']['post_description'];
            $this->data['post_thumb'] = $this->image->resize($post['post_image'], 150, 150);
            unset($this->session->data['flash']);
        } else {
            $post = [
                'sort_order' => null, 'post_image' => null
            ];
            foreach ($this->data['languages'] as $id => $language) {
                $postDescription[$language['language_id']] = [
                    'post_status' => 0, 'name' => null, 'content' => null, 'description' => null,
                    'meta_description' => null, 'meta_keywords' => null, 'tags' => null
                ];
            }
            $this->data['post_thumb'] = $this->image->resize('no_image.jpg', 150, 150);
        }

        $this->data['post'] = $post;
        $this->data['description'] = $postDescription;
        $this->data['categoryId'] = $categoryId;

        $this->data['cancel'] = $this->url->link('blog/post', '', 'SSL');

        $this->data['links'] = [
            'submit' => $this->url->link('blog/post/store'),
            'cancel' => $this->url->link('blog/post'),
        ];

        $this->template = 'blog/post/create.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
    }

    public function store()
    {
        if ( $this->request->server['REQUEST_METHOD'] != 'POST' )
        {
            $this->redirect($this->url->link('blog/post', ''));
        }

        $this->initializer([
            'blog/post'
        ]);

        $post = $this->request->post['post'];

        if ( !isset($post['sort_order']) )
        {
            $post['sort_order'] = 0;
        }

        $postDescription = array_filter(array_map(function ($value) {
            if (!isset($value['post_status'])) {
                $value['post_status'] = '0';
            }
            return $value;
        }, $this->request->post['post_description']));

        if ( !$this->validate(['post' => $post, 'description' => $postDescription]) )
        {
            
            $result_json['success'] = '0';
            $result_json['errors'] = $this->errors;
            
            $this->response->setOutput(json_encode($result_json));
            
            return;
        }

        $postId = $this->post->insertPost($post);

        $this->post->insertPostDescription($postId, $postDescription);

        $result_json['success_msg'] = $this->language->get('text_success');
        $result_json['redirect'] = '1';
        $result_json['to'] = (string) $this->url->link('blog/post');

        $result_json['success'] = '1';
        
        $this->response->setOutput(json_encode($result_json));
        
        return;
    }


    public function edit()
    {
        $postId = (isset($this->request->get['post_id']) ? $this->request->get['post_id'] : null);

        if (!$postId) {
            $this->session->data['errors'] = [$this->language->get('error_not_set_id')];

            $this->redirect(
                $this->url->link(
                    'blog/category',
                    '',
                    'SSL'
                )
            );
        }

        $this->initializer([
            'blog/post',
            'blog/category',
            'tool/image',
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
            'href' => $this->url->link('blog/post', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('breadcrumb_update'),
        );

        $this->data['categories'] = $this->category->getAllCategories();

        if (isset($this->session->data['flash'])) {
            $post = $this->session->data['flash']['data']['post'];
            $postDescription = $this->session->data['flash']['data']['post_description'];
            $this->data['post_thump'] = $this->image->resize($post['post_image'], 150, 150);
            unset($this->session->data['flash']);
        } else {
            $post = $this->post->getPostById($postId);
            $postDescription = $this->post->getPostDescriptionByPostId($postId);
        }

        $this->data['post'] = $post;
        $this->data['description'] = $postDescription;

        $this->data['post_thumb'] = $this->image->resize($post['post_image'], 150, 150);

        $this->data['links'] = [
            'submit' => $this->url->link('blog/post/update', 'post_id=' . $postId),
            'cancel' => $this->url->link('blog/post'),
        ];

        $this->data['cancel'] = $this->url->link('blog/post', '', 'SSL');

        $this->data['languages'] = $this->languageModel->getLanguages();

        $this->template = 'blog/post/edit.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
    }


    public function update()
    {
        if ( $this->request->server['REQUEST_METHOD'] != 'POST' )
        {
            $this->redirect($this->url->link('blog/post', '', 'SSL') );
        }

        $postId = (isset($this->request->get['post_id']) ? $this->request->get['post_id'] : null);

        if ( ! $postId )
        {
         
            $result_json['success'] = '0';
            $result_json['errors'] = array( 'warning' => $this->language->get('error_not_set_id') );
            
            $this->response->setOutput(json_encode($result_json));
            
            return;
        }

        $this->initializer([
            'blog/post'
        ]);

        $post = $this->request->post['post'];

        if ( !isset($post['sort_order']) || empty($post['sort_order']) )
        {
            $post['sort_order'] = 0;
        }

        $postDescription = array_filter(array_map(function ($value) {
            if (!isset($value['post_status'])) {
                $value['post_status'] = '0';
            }
            return $value;
        }, $this->request->post['post_description']));

        if ( ! $this->validate(['post' => $post, 'description' => $postDescription]) )
        {

            $result_json['success'] = '0';
            $result_json['errors'] = $this->errors;
            
            $this->response->setOutput(json_encode($result_json));
            
            return;
        }

        $postToBeUpdated = $this->post->getPostById($postId);

        if (! $postToBeUpdated )
        {

            $result_json['success'] = '0';
            $result_json['errors'] = array( 'warning' => $this->language->get('error_not_set_id') ) ;
            
            $this->response->setOutput(json_encode($result_json));
            
            return;
        }

        $this->post->updatePost($postId, $post);
        $this->post->updatePostDescription($postId, $postDescription);

        $result_json['success'] = '1';
        $result_json['success_msg'] = $this->language->get('message_post_edited_successfully');
        
        $this->response->setOutput(json_encode($result_json));
        
        return;
    }


    public function dtHandler()
    {
        $this->load->model('blog/post');
        $request = $_REQUEST;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        $start = $request['start'];
        $length = $request['length'];

        $columns = array(
            0 => 'post_id',
            1 => 'name',
            2 => 'description',
            3 => 'post_status',
            4 => 'content',
            5 => 'visits',
            6 => 'creation_date'
        );
        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];

        $return = $this->model_blog_post->dtHandler($start, $length, $search, $orderColumn, $orderType);
        $records = $return['data'];
        $totalData = $return['total'];
        $totalFiltered = $return['totalFiltered'];

        foreach ($records as $index => $record)
        {
            if ( empty($record['name']) )
            {
                $records[$index]['name'] = $this->model_blog_post->getAllPostNamesById($record['post_id']);
            }
        }

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
        $this->load->model("blog/post");

        if (isset($this->request->post['selected'])) {

            foreach ($this->request->post['selected'] as $id) {
                $this->model_blog_post->deletePostDescription($id);
                $this->model_blog_post->deletePost($id);
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
        $this->load->model("blog/post");
        if (isset($this->request->post["id"]) && isset($this->request->post["status"])) {
            $id = $this->request->post["id"];
            $status = $this->request->post["status"];

            $this->model_blog_post->dtUpdateStatus($id, $status);

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
}