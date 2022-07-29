<?php
/**
 * Opencart Extension class.
 *
 * This class is used by Opencart as a twig extension and must not be used directly.
 *
 */
class Twig_Extension_ExpandcartProduct extends Twig_Extension
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
            new \Twig_SimpleFunction('getFeaturedProducts', array($this, 'getFeaturedProducts')),
            new \Twig_SimpleFunction('getBestSellerProducts', array($this, 'getBestSellerProducts')),
            new \Twig_SimpleFunction('getLatestProducts', array($this, 'getLatestProducts')),
            new \Twig_SimpleFunction('getSpecialProducts', array($this, 'getSpecialProducts')),
            new \Twig_SimpleFunction('getProductsByCategoryId', array($this, 'getProductsByCategoryId')),
            new \Twig_SimpleFunction('getBestSellerProductsByCategoryId', array($this, 'getBestSellerProductsByCategoryId')),
            new \Twig_SimpleFunction('getLatestProductsByCategoryId', array($this, 'getLatestProductsByCategoryId')),
            new \Twig_SimpleFunction('getSpecialProductsByCategoryId', array($this, 'getSpecialProductsByCategoryId')),
            new \Twig_SimpleFunction('getBrands', array($this, 'getBrands')),
            new \Twig_SimpleFunction('getProductsByPrizeId', array($this, 'getProductsByPrizeId')),
            new \Twig_SimpleFunction('getProductsByBrandId', array($this, 'getProductsByBrandId'))
        );
    }

    public function getFeaturedProducts($product_ids, $thumb_width, $thumb_height) {
        $this->load->model('section/product', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');
        $ret = $this->registry->get('model_section_product')->getFeaturedProducts($product_ids, $thumb_width, $thumb_height);
        return $ret;
    }

    public function getBestSellerProducts($limit, $thumb_width, $thumb_height) {
        $this->load->model('section/product', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');
        $ret = $this->registry->get('model_section_product')->getBestSellerProducts($limit, $thumb_width, $thumb_height);
        return $ret;
    }

    public function getLatestProducts($limit, $thumb_width, $thumb_height) {
        $this->load->model('section/product', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');
        $ret = $this->registry->get('model_section_product')->getLatestProducts($limit, $thumb_width, $thumb_height);
        return $ret;
    }

    public function getSpecialProducts($limit, $thumb_width, $thumb_height) {
        $this->load->model('section/product', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');
        $ret = $this->registry->get('model_section_product')->getSpecialProducts($limit, $thumb_width, $thumb_height);
        return $ret;
    }

    public function getProductsByCategoryId($category_id, $limit, $thumb_width, $thumb_height, $get_cat_ids = false) {
        $this->load->model('section/product', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');
        $ret = $this->registry->get('model_section_product')->getProductsByCategoryId($category_id, $limit, $thumb_width, $thumb_height, $get_cat_ids);
        return $ret;
    }

    public function getBestSellerProductsByCategoryId($category_id, $limit, $thumb_width, $thumb_height) {
        $this->load->model('section/product', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');
        $ret = $this->registry->get('model_section_product')->getBestSellerProductsByCategoryId($category_id, $limit, $thumb_width, $thumb_height);
        return $ret;
    }

    public function getLatestProductsByCategoryId($category_id, $limit, $thumb_width, $thumb_height) {
        $this->load->model('section/product', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');
        $ret = $this->registry->get('model_section_product')->getLatestProductsByCategoryId($category_id, $limit, $thumb_width, $thumb_height);
        return $ret;
    }

    public function getSpecialProductsByCategoryId($category_id, $limit, $thumb_width, $thumb_height) {
        $this->load->model('section/product', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');
        $ret = $this->registry->get('model_section_product')->getSpecialProductsByCategoryId($category_id, $limit, $thumb_width, $thumb_height);
        return $ret;
    }

    public function getBrands($brand_ids, $thumb_width, $thumb_height) {
        $this->load->model('section/brand', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');
        $ret = $this->registry->get('model_section_brand')->getBrands($brand_ids, $thumb_width, $thumb_height);
        return $ret;
    }

    public function getProductsByPrizeId($prize_id, $limit, $thumb_width, $thumb_height) {

        $this->load->model('section/product', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');
        $ret = $this->registry->get('model_section_product')->getPrizeProducts($prize_id, $limit, $thumb_width, $thumb_height);
        return $ret;
    }

    public function getProductsByBrandId($brand_id, $limit, $thumb_width, $thumb_height) {
        $this->load->model('section/product', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');
        $ret = $this->registry->get('model_section_product')->getProductsByBrandId($brand_id, $limit, $thumb_width, $thumb_height);
        return $ret;
    }

    public function getName()
    {
        return 'ExpandcartProduct';
    }
}