<?php

class ControllerApiBlogCategory extends Controller
{
    protected $category, $post, $settings;

    public function index()
    {
        $json = [];
        $this->response->addHeader('Content-Type: application/json');
        $this->response->addHeader('Access-Control-Allow-Origin: *');
        $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
        $this->response->addHeader('Access-Control-Allow-Credentials: true');

        $this->language->load('blog/blog');
        
        if (!isset($this->session->data['api_id'])) {
            $json['error']['warning'] = $this->language->get('error_permission');
            $this->response->setOutput(json_encode($json));
            return;
        }

        $data = (array)json_decode(file_get_contents('php://input'));
        
        $categoryId = (isset($data['category_id']) ? $data['category_id'] : null);

        try {
            
            $this->initializer([
                'post' => 'catalog/blog/post',
                'settings' => 'catalog/blog/settings',
                'image' => 'tool/image',
                'category' => 'catalog/blog/category'
            ]);

            if (isset($data['slug']) && !isset($data['category_id'])) {
                $name = $data['slug'];
                $cat_data = $this->category->getCategoryIdBySlug($name);

                if ($data) {
                    $categoryId = $data['category_id'] = $cat_data;
                }
            }

            if (!$categoryId) {
                $json['error']['warning'] = $this->language->get('error_category_id');
                $this->response->setOutput(json_encode($json));
                return;
            }

            if ($this->settings->isActive() == false) {
                $json['error']['warning'] = $this->language->get('error_permission');
                $this->response->setOutput(json_encode($json));
                return;
            }

            $category = $this->category->getCategoryDescriptionById($categoryId, $data['language']);

            $category['description'] = html_entity_decode($category['description']);

            $json['category'] = $category;

            $json['category']['category_image'] = null;

            if ($category['category_image'] != 'no_image.jpg') {
                $json['category']['category_image'] = $this->image->resize($category['category_image'], 700, 250);
            }

            if (!$category) {
                $json['error']['warning'] = $this->language->get('error_not_found');
                $this->response->setOutput(json_encode($json));
                return;
            }

            if ($category['parent_id'] != 0) {

                $parentData = $this->category->getCategoryDescriptionById(
                    $category['parent_id'], $data['language']
                );
            }

            $json['subCategories'] = $this->category->getCategoriesByParent($categoryId, $data['language']);
            $json['categoryPosts'] = $this->post->getPostsByCategoryId($categoryId, $data['language']);
            $json['categoryPosts'] = array_map(function($post) {
                if ($post['post_image']) {
                    $post['post_image'] = $this->image->resize($post['post_image'], 250, 250);
                }
                return $post;
            }, $json['categoryPosts']);

            $json['status'] = 'ok';

        } catch (Exception $exception) {
            http_response_code(500);
            $json['error']['warning'] = 'Something went wrong';
        }
        $this->response->setOutput(json_encode($json));
    }

    public function browse()
    {
        $json = [];
        $this->response->addHeader('Content-Type: application/json');
        $this->response->addHeader('Access-Control-Allow-Origin: *');
        $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
        $this->response->addHeader('Access-Control-Allow-Credentials: true');
        
        if (!isset($this->session->data['api_id'])) {
            $json['error']['warning'] = $this->language->get('error_permission');
            $this->response->setOutput(json_encode($json));
            return;
        }

        $data = (array)json_decode(file_get_contents('php://input'));
        $this->initializer([
            'category' => 'catalog/blog/category',
            'settings' => 'catalog/blog/settings',
            'image' => 'tool/image',
        ]);

        try {

            if ($this->settings->isActive() == false) {
                $json['error']['warning'] = $this->language->get('error_permission');
                $this->response->setOutput(json_encode($json));
                return;
            }

            $json['categories'] = $this->category->getCategoriesByParent(0, $data['language']);
            $json['categories'] = array_map(function($cat) {
                if ($cat['category_image']) {
                    $cat['category_image'] = $this->image->resize($cat['category_image'], 250, 250);
                }
                return $cat;
            }, $json['categories']);
            $json['status'] = 'ok';

        } catch (Exception $exception) {
            http_response_code(500);
            $json['error']['warning'] = 'Something went wrong';
        }
        $this->response->setOutput(json_encode($json));
    }
}
