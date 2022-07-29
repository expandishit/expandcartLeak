<?php

class ControllerBlogCategory extends Controller
{
    protected $category, $post, $settings;

    public function index()
    {
        $this->document->addStyle('expandish/view/theme/default/css/blog/blog.css');
        
        $categoryId = (isset($this->request->get['category_id']) ? $this->request->get['category_id'] : null);

        $this->initializer(['category' => 'blog/category']);

        if ($this->preAction->isActive()) {
            if (isset($this->request->get['slug']) && !isset($this->request->get['category_id'])) {
                $name = $this->request->get['slug'];
                $data = $this->category->getCategoryIdBySlug($name);

                if ($data) {
                    $categoryId = $this->request->get['category_id'] = $data;
                }
            }
        }

        if (!$categoryId) {
            $this->redirect($this->url->link('error/not_found', '', 'SSL'));
        }

        $this->initializer([
            'post' => 'blog/post',
            'settings' => 'blog/settings',
            'image' => 'tool/image',
        ], [
            'blog/category'
        ]);

        if ($this->settings->isActive() == false) {
            $this->redirect($this->url->link('error/not_found', '', 'SSL'));
        }

        $category = $this->category->getCategoryDescriptionById($categoryId, $this->session->data['language']);

        $category['description'] = html_entity_decode($category['description']);

        $this->data['category'] = $category;

        $this->data['category']['category_image'] = null;

        if ($category['category_image'] != 'no_image.jpg') {
            $this->data['category']['category_image'] = $this->image->resize($category['category_image'], 700, 250);
        }

        if (!$category) {
            $this->redirect($this->url->link('error/not_found', '', 'SSL'));
        }

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

        if ($category['parent_id'] != 0) {

            $parentData = $this->category->getCategoryDescriptionById(
                $category['parent_id'], $this->session->data['language']
            );

            $this->data['breadcrumbs'][] = array(
                'text'      => $parentData['name'],
                'href'      => $this->url->link('blog/category', '&category_id=' . $parentData['category_id'], 'SSL'),
                'separator' => $this->language->get('text_separator')
            );
        }

        $this->data['breadcrumbs'][] = array(
            'text'      => $category['name'],
            'href'      => $this->url->link('blog/category', '', 'SSL'),
            'separator' => $this->language->get('text_separator')
        );

        $this->document->setTitle($category['name']);
        $this->document->setDescription($category['meta_description']);
        $this->document->setKeywords($category['meta_keywords']);

        $this->data['subCategories'] = $this->category->getCategoriesByParent($categoryId);
        $this->data['categoryPosts'] = $this->post->getPostsByCategoryId($categoryId);

        $this->template = $this->checkTemplate('blog/category.expand');

        $this->response->setOutput($this->render_ecwig());
    }

    public function browse()
    {
        $this->initializer([
            'category' => 'blog/category',
            'settings' => 'blog/settings',
        ], [
            'blog/category'
        ]);

        if ($this->settings->isActive() == false) {
            $this->redirect($this->url->link('error/not_found', '', 'SSL'));
        }

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


        $this->document->setTitle($this->language->get('blog_categories'));

        $this->data['categories'] = $this->category->getCategoriesByParent(0);

        $this->template = $this->checkTemplate('blog/categories.expand');

        $this->response->setOutput($this->render_ecwig());
    }
}
