<?php

class ControllerApiBlogBlog extends Controller
{
    protected $category, $post, $settings;

    public function index()
    {
        $json = [];
        $this->response->addHeader('Content-Type: application/json');
        $this->response->addHeader('Access-Control-Allow-Origin: *');
        $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
        $this->response->addHeader('Access-Control-Allow-Credentials: true');

        $data = (array)json_decode(file_get_contents('php://input'));

        $this->language->load('blog/blog');

        if (!isset($this->session->data['api_id'])) {
            $json['error']['warning'] = $this->language->get('error_permission');
            $this->response->setOutput(json_encode($json));
            return;
        }

        $this->initializer([
            'category' => 'catalog/blog/category',
            'post' => 'catalog/blog/post',
            'settings' => 'catalog/blog/settings',
        ]);


        if ($this->settings->isActive() == false) {
            $json['error']['warning'] = $this->language->get('error_permission');
            $this->response->setOutput(json_encode($json));
            return;
        }

        try {
            
            $settings = $this->settings->getSettings();

            $json['blogCategories'] = $this->category->getLatestCategories(
                $settings['maximum_index_categories'] ?: 10, $data['language']
            );

            foreach ($json['blogCategories'] as $key => $row) {
                $json['blogCategories'][$key]['description'] = strip_tags(html_entity_decode($row['description']));
            }

            $json['blogPosts'] = $this->post->getLatestPosts(
                $settings['maximum_index_blogs'] ?: 10, $data['language']
            );
            
            $json['status'] = 'ok';

        } catch (Exception $exception) {
            http_response_code(500);
            $json['error']['warning'] = 'Something went wrong';
        }
        $this->response->setOutput(json_encode($json));
    }
}
