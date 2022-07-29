<?php

class ControllerBlogBlog extends Controller
{
    protected $category, $post, $settings;

    public function index()
    {

        $this->document->addStyle('expandish/view/theme/default/css/blog/blog.css');

        $this->initializer([
            'category' => 'blog/category',
            'post' => 'blog/post',
            'settings' => 'blog/settings',
        ], [
            'blog/blog'
        ]);

        if ($this->settings->isActive() == false) {
            $this->redirect($this->url->link('error/not_found', '', 'SSL'));
        }

        $settings = $this->settings->getSettings();

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

        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['blogCategories'] = $this->category->getLatestCategories(
            $settings['maximum_index_categories'] ?: 10
        );
        foreach ($this->data['blogCategories'] as $key => $row) {
            # code...
            $this->data['blogCategories'][$key]['description'] = strip_tags(html_entity_decode($row['description']));
        }
       

        $this->data['blogPosts'] = $this->post->getLatestPosts(
            $settings['maximum_index_blogs'] ?: 10
        );

        $this->template = $this->checkTemplate('blog/blog.expand');

        $this->response->setOutput($this->render_ecwig());
    }
}
