<?php

class ControllerApiBlogPost extends Controller
{

    protected $category, $post, $comment, $settings;

    public function index()
    {
        $json = [];
        $this->response->addHeader('Content-Type: application/json');
        $this->response->addHeader('Access-Control-Allow-Origin: *');
        $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
        $this->response->addHeader('Access-Control-Allow-Credentials: true');
        
        if (!isset($json['api_id'])) {
            $json['error']['warning'] = $this->language->get('error_permission');
            $this->response->setOutput(json_encode($json));
            return;
        }

        $data = (array)json_decode(file_get_contents('php://input'));

        $postId = (isset($data['post_id']) ? $data['post_id'] : null);
        try {
        $this->initializer(['post' => 'catalog/blog/post']);

        if (isset($data['slug']) && !isset($data['post_id'])) {
            $name = $data['slug'];
            $post_data = $this->post->getPostIdBySlug($name);

            if ($data) {
                $postId = $data['post_id'] = $post_data;
            }
        }

        if (!$postId) {
            $json['error']['warning'] = $this->language->get('error_psot_id');
            $this->response->setOutput(json_encode($json));
            return;
        }

        $this->initializer([
            'category' => 'catalog/blog/category',
            'comment' => 'catalog/blog/comment',
            'settings' => 'catalog/blog/settings',
            'image' => 'tool/image',
        ], [
            'catalog/blog/post',
            'catalog/blog/blog'
        ]);

        if ($this->settings->isActive() == false) {
            $json['error']['warning'] = $this->language->get('error_permission');
            $this->response->setOutput(json_encode($json));
            return;
        }

        $this->settings->getSettings();

        if (!($post = $this->post->getPostById($postId, $data['language']))) {
            $json['error']['warning'] = $this->language->get('error_not_found');
            $this->response->setOutput(json_encode($json));
            return;
        }

        $json['post'] = $post;
        $json['post']['post_image'] = null;

        if ($post['post_image'] != 'no_image.jpg') {
            $json['post']['post_image'] =   $post['post_image'];
        }

        $json['comments'] = $this->comment->getCommentsByPostId(
            $postId, $json['post']['post_description_id']
        );
        
        $json['blogCategories'] = $this->category->getLatestCategories(10, $data['language']);

        foreach ($json['blogCategories'] as $key => $row) {
            $json['blogCategories'][$key]['description'] = strip_tags(html_entity_decode($row['description']));
        }
       

        $json['blogPosts'] = $this->post->getLatestPosts(
            $settings['maximum_index_blogs'] ?: 10, $data['language']
        );

        $parentData = $this->category->getCategoryDescriptionById(
            $json['post']['parent_id'], $json['language']
        );

        $json['post']['content'] = html_entity_decode($json['post']['content']);

        $json['relatedPosts'] = $this->post->getRelatedPosts(
            $postId,
            $json['post']['tags'],
            $json['language']
        );

        $json['enableCommenting'] = false;
        $json['showComments'] = false;

        $this->post->updatePostVisits(
            $json['post']['post_id'],
            $json['language']
        );

        $this->load->model('user/user');

        $json['post']['user'] = $this->model_user_user->getUser($json['post']['user_id']);

        $json['status'] = 'ok';

        } catch (Exception $exception) {
            http_response_code(500);
            $json['error']['warning'] = 'Something went wrong';
        }
        $this->response->setOutput(json_encode($json));
    }
}
