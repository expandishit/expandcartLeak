<?php

class ControllerBlogComment extends Controller
{

    protected $post, $comment, $settings;

    public function submit()
    {
        $postId = (isset($this->request->get['post_id']) ? $this->request->get['post_id'] : null);

        if (!$postId) {
            $this->redirect($this->url->link('error/not_found', '', 'SSL'));
        }

        $this->initializer([
            'post' => 'blog/post',
            'comment' => 'blog/comment',
            'settings' => 'blog/settings',
        ], [
            'blog/post'
        ]);

        if ($this->settings->isActive() == false) {
            $this->redirect($this->url->link('error/not_found', '', 'SSL'));
        }

        $this->settings->getSettings();

        $commentedData = $this->request->post['comment'];

        if ($this->comment->validate($commentedData) === false) {
            $this->session->data['errors'] = implode('<br />', $this->comment->errors);
            $this->redirect($this->url->link('blog/post', '&post_id=' . $postId, 'SSL'));
        }

        $post = $this->post->getPostById($postId);

        if (!$post) {
            $this->redirect($this->url->link('error/not_found', '', 'SSL'));
        }

        $commentedData['post_description_id'] = $post['post_description_id'];

        $commentedData['comment_status'] = 0;

        if ($this->customer->isLogged()) {
            if (in_array($this->settings->settings['auto_approval'], [1, 2])) {
                $commentedData['comment_status'] = 1;
            }
        } else {
            if (in_array($this->settings->settings['auto_approval'], [1, 3])) {
                $commentedData['comment_status'] = 1;
            }
        }

        $this->comment->storeComment($postId, $commentedData);

        $this->session->data['success'] = $this->language->get('message_comment_pending_added_successfully');

        $this->redirect($this->url->link('blog/post', '&post_id=' . $postId, 'SSL'));
    }
}
