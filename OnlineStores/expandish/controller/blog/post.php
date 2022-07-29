<?php

class ControllerBlogPost extends Controller
{

    protected $category, $post, $comment, $settings;

    public function index()
    {
        $postId = (isset($this->request->get['post_id']) ? $this->request->get['post_id'] : null);

        $this->initializer(['post' => 'blog/post']);

        if ($this->preAction->isActive()) {
            if (isset($this->request->get['slug']) && !isset($this->request->get['post_id'])) {
                $name = $this->request->get['slug'];
                $data = $this->post->getPostIdBySlug($name);

                if ($data) {
                    $postId = $this->request->get['post_id'] = $data;
                }
            }
        }

        if (!$postId) {
            $this->redirect($this->url->link('error/not_found', '', 'SSL'));
        }

        $this->initializer([
            'category' => 'blog/category',
            'comment' => 'blog/comment',
            'settings' => 'blog/settings',
            'image' => 'tool/image',
        ], [
            'blog/post',
            'blog/blog'
        ]);

        if ($this->settings->isActive() == false) {
            $this->redirect($this->url->link('error/not_found', '', 'SSL'));
        }

        $this->settings->getSettings();

        if (!($post = $this->post->getPostById($postId))) {
            $this->redirect($this->url->link('error/not_found', '', 'SSL'));
        }

        $this->data['post'] = $post;

        $this->data['post']['post_image'] = null;

        if ($post['post_image'] != 'no_image.jpg') {
            $this->data['post']['post_image'] =   $post['post_image'];
        }

        $this->data['comments'] = $this->comment->getCommentsByPostId(
            $postId, $this->data['post']['post_description_id']
        );

        $this->data['blogCategories'] = $this->category->getLatestCategories(10);

        foreach ($this->data['blogCategories'] as $key => $row) {
            $this->data['blogCategories'][$key]['description'] = strip_tags(html_entity_decode($row['description']));
        }
       

        $this->data['blogPosts'] = $this->post->getLatestPosts(
            $settings['maximum_index_blogs'] ?: 10
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_blog'),
            'href'      => $this->url->link('blog/blog', '', 'SSL'),
            'separator' => $this->language->get('text_separator')
        );

        $parentData = $this->category->getCategoryDescriptionById(
            $this->data['post']['parent_id'], $this->session->data['language']
        );

        $this->data['post']['content'] = html_entity_decode($this->data['post']['content']);

        $this->data['breadcrumbs'][] = array(
            'text'      => $parentData['name'],
            'href'      => $this->url->link('blog/category', '&category_id=' . $parentData['category_id'], 'SSL'),
            'separator' => $this->language->get('text_separator')
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->data['post']['name'],
            'href'      => $this->url->link('', '', 'SSL'),
            'separator' => $this->language->get('text_separator')
        );

        $this->data['relatedPosts'] = $this->post->getRelatedPosts(
            $postId,
            $this->data['post']['tags'],
            $this->session->data['language']
        );

        $this->data['enableCommenting'] = false;
        $this->data['showComments'] = false;

        if ($this->customer->isLogged()) {
            $this->data['email'] = $this->customer->getEmail();
            $this->data['name'] = $this->customer->getFirstName() . ' ' . $this->customer->getLastName();
            $this->data['customer_id'] = $this->customer->getId();

            if (in_array($this->settings->settings['enable_commenting'], [1, 2])) {
                $this->data['enableCommenting'] = true;
            }

            if (in_array($this->settings->settings['show_comments'], [1, 2])) {
                $this->data['showComments'] = true;
            }

        } else {
            $this->data['email'] = null;
            $this->data['name'] = null;
            $this->data['customer_id'] = '0';

            if (in_array($this->settings->settings['enable_commenting'], [1, 3])) {
                $this->data['enableCommenting'] = true;
            }

            if (in_array($this->settings->settings['show_comments'], [1, 3])) {
                $this->data['showComments'] = true;
            }
        }

        $this->post->updatePostVisits(
            $this->data['post']['post_id'],
            $this->session->data['language']
        );

        if (isset($this->session->data['success'])) {

            $this->data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        }

        if (isset($this->session->data['errors'])) {

            $this->data['error_warning'] = $this->session->data['errors'];

            unset($this->session->data['errors']);
        }

        //Get user data
        $this->load->model('user/user');

        $this->data['post']['user'] = $this->model_user_user->getUser($this->data['post']['user_id']);

        $this->document->setTitle($this->data['post']['name']);
        $this->document->setDescription($this->data['post']['meta_description']);
        $this->document->setKeywords($this->data['post']['meta_keywords']);

        $this->checkTemplate('blog/post.expand');

        $this->response->setOutput($this->render_ecwig());
    }
}
