<?php
/**
 * Opencart Extension class.
 *
 * This class is used by Opencart as a twig extension and must not be used directly.
 *
 */
class Twig_Extension_ExpandcartCategory extends Twig_Extension
{
    protected $registry;
    protected $load;
    /**
     * @param Registry $registry
     */
    public function __construct(\Registry $registry) {
        $this->registry = $registry;
        $this->load = $this->registry->get('load');
    }
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('getFeaturedCategories', array($this, 'getFeaturedCategories')),
            new \Twig_SimpleFunction('getCategoryAndChilds', array($this, 'getCategoryAndChilds')),
            new \Twig_SimpleFunction('getCategoryAndChildsBylevel', array($this, 'getCategoryAndChildsBylevel')),
            new \Twig_SimpleFunction('getCategory', array($this, 'getCategory')),
            new \Twig_SimpleFunction('getBrands', array($this, 'getBrands')),
            new \Twig_SimpleFunction('getPrizes', array($this, 'getPrizes')),
            new \Twig_SimpleFunction('getLatestBlogPosts', array($this, 'getLatestBlogPosts')),
        );
    }

    public function getFeaturedCategories($category_path, $thumb_width=0, $thumb_height=0) {
        $this->load->model('section/category', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');
        $ret = $this->registry->get('model_section_category')->getFeaturedCategories($category_path, $thumb_width, $thumb_height);
        return $ret;
    }

    public function getCategory($category_id) {
        $this->load->model('section/category', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');
        $ret = $this->registry->get('model_section_category')->getCategory($category_id);
        return $ret;
    }

    public function getCategoryAndChilds($category_path, $thumb_width, $thumb_height) {
        $this->load->model('section/category', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');
        $ret = $this->registry->get('model_section_category')->getCategoryAndChilds($category_path, $thumb_width, $thumb_height);
        return $ret;
    }

    public function getCategoryAndChildsBylevel($category_path, $thumb_width, $thumb_height, $level) {
        $this->load->model('section/category', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');
        $ret = $this->registry->get('model_section_category')->getCategoryAndChildsBylevel($category_path, $thumb_width, $thumb_height, $level);
        return $ret;
    }

    public function getBrands($brand_ids_csv=[], $thumb_width=0, $thumb_height=0) {
        $this->load->model('section/brand', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');
        $ret = $this->registry->get('model_section_brand')->getBrands($brand_ids_csv, $thumb_width, $thumb_height);
        return $ret;
    }

    public function getPrizes($thumb_width=0, $thumb_height=0) {
        $this->load->model('section/prize', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');
        $ret = $this->registry->get('model_section_prize')->getFeaturedPrizes($thumb_width, $thumb_height);
        return $ret;
    }

    public function getLatestBlogPosts($limit=4)
    {
        $this->load->model('blog/post', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');
        $ret = $this->registry->get('model_blog_post')->getLatestPosts($limit);
        return $ret;
    }

    public function getName()
    {
        return 'ExpandcartCategory';
    }
}