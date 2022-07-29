<?php

class ControllerApiBlogComment extends Controller
{

    protected $post, $comment, $settings;

    public function submit()
    {
        $json = [];
        $this->response->addHeader('Content-Type: application/json');
        $this->response->addHeader('Access-Control-Allow-Origin: *');
        $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
        $this->response->addHeader('Access-Control-Allow-Credentials: true');

        $this->language->load('blog/blog');

        if (!isset($json['api_id'])) {
            $json['error']['warning'] = $this->language->get('error_permission');
            $this->response->setOutput(json_encode($json));
            return;
        }

        $data = (array)json_decode(file_get_contents('php://input'));

        $postId = (isset($data['post_id']) ? $data['post_id'] : null);

        if (!$postId) {
            $json['error']['warning'] = $this->language->get('error_not_found');
            $this->response->setOutput(json_encode($json));
            return;
        }

        $commentedData = (array)$data['comment'];
        
        if (!isset($commentedData['customer_id']) || !$commentedData['customer_id']) {
            $json['error']['customer_id'] = $this->language->get('error_customer_id');
        }
        if (!isset($commentedData['name']) || !$commentedData['name']) {
            $json['error']['name'] = $this->language->get('error_name');
        }
        if (!isset($commentedData['email']) || !$commentedData['email']) {
            $json['error']['email'] = $this->language->get('error_email');
        }
        if (!isset($commentedData['comment']) || !$commentedData['comment']) {
            $json['error']['comment'] = $this->language->get('error_comment');
        }

        if (isset($json['error']) && !empty($json['error'])) {
            $json['status'] = 'validation_error';
            $this->response->setOutput(json_encode($json));
            return;
        }

        try {
            $this->initializer([
                'post' => 'catalog/blog/post',
                'comment' => 'catalog/blog/comment',
                'settings' => 'catalog/blog/settings',
            ]);

            if ($this->settings->isActive() == false) {
                $json['error']['warning'] = $this->language->get('error_permission');
                $this->response->setOutput(json_encode($json));
                return;
            }

            $this->settings->getSettings();

            if ($this->comment->validate($commentedData) === false) {
                $json['errors'] = $this->comment->errors;
                $this->response->setOutput(json_encode($json));
                return;
            }

            $post = $this->post->getPostById($postId, $data['language']);

            if (!$post) {
                $json['error']['warning'] = $this->language->get('error_not_found');
                $this->response->setOutput(json_encode($json));
                return;
            }

            $commentedData['post_description_id'] = $post['post_description_id'];

            $commentedData['comment_status'] = 0;

            if (in_array($this->settings->settings['auto_approval'], [1, 2])) {
                $commentedData['comment_status'] = 1;
            }

            $this->comment->storeComment($postId, $commentedData);

            $json['status'] = 'ok';

        } catch (Exception $exception) {
            http_response_code(500);
            $json['error']['warning'] = 'Something went wrong';
        }
        $this->response->setOutput(json_encode($json));
    }
}
