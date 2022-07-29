<?php
require_once('jwt_helper.php');
class ControllerApiCategory extends Controller
{
    public function Categories()
    {
        try {
            $this->load->language('api/login');
            $json = array();
            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;
        
            $this->load->model('account/api');

            if (!isset($this->session->data['api_id'])) {
                http_response_code(400);                
                $json['error']['warning'] = $this->language->get('error_permission');
            } else {               
                $parts = array();

                if (isset($parts[0])) {
                    $json['category_id'] = $parts[0];
                } else {
                    $json['category_id'] = 0;
                }

                if (isset($parts[1])) {
                    $json['child_id'] = $parts[1];
                } else {
                    $json['child_id'] = 0;
                }

                /* add for level 3 subs category's */

                if (isset($parts[2])) {
                    $json['submenu'] = $parts[2];
                } else {
                    $json['submenu'] = 0;
                }

                if (isset($parts[2])) {
                    $json['sisters_id'] = $parts[2];
                } else {
                    $json['sisters_id'] = 0;
                }

                $language_id = $this->_getLanguageId($params->locale); //should be 'en', 'ar', 'ar-SA' ... etc        

                $data = [
                    "limit" =>$params->limit,
                    "start" => $params->start,
                    "filter_text" => $params->filter_text,
                ];

                $this->load->model('catalog/category');

                $json['categories'] = array();
                ////Getting trips categories incase trips App installed
                if (\Extension::isInstalled('trips') && ($this->config->get('trips')['status']==1) && ($params->trips==1)) {
                    $tripsCategoriesIDs=$this->model_catalog_category->getCustomCategoriesIDs('trips_categories');
                    $categories = $this->model_catalog_category->getCustomCategories($tripsCategoriesIDs,$language_id);
                }
                else{
                $categories = $this->model_catalog_category->getCategories(0,$data, $language_id);
                 }

                foreach ($categories as $category) {
                    $children_data = array();

                    $children = $this->model_catalog_category->getCategories($category['category_id'],$data);

                    foreach ($children as $child) {
                        $sister_data = array();
                        $sisters = $this->model_catalog_category->getCategories($child['category_id'],$data);
                        if(count($sisters) > 0) {
                            foreach ($sisters as $sisterMember) {
                                $sister_data[] = array(
                                    'category_id' =>$sisterMember['category_id'],
                                    'name'        => $sisterMember['name'],
                                    'href'        => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id']. '_' . $sisterMember['category_id'])
                                );

                            }

                            $children_data[] = array(
                                'category_id' => $child['category_id'],
                                'image' => \Filesystem::getUrl('image/' . (empty($child['image']) ? 'no_image.png' :  $child['image'])),
                                'sister_id'   => $sister_data,
                                'name'        => $child['name'],
                                'href'        => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id']),
                                'manufacturer' => $this->model_catalog_category->getManufacturerByCategoryId($child['category_id'])
                            );
                        }else{
                            $children_data[] = array(
                                'category_id' => $child['category_id'],
                                'image' => \Filesystem::getUrl('image/' . (empty($child['image']) ? 'no_image.png' :  $child['image'])),
                                'sister_id'    =>'',
                                'name'        => $child['name'],
                                'href'        => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id']),
                                'manufacturer' => $this->model_catalog_category->getManufacturerByCategoryId($child['category_id'])
                            );
                        }
                    }

                    $json['categories'][] = array(
                        'category_id' => $category['category_id'],
                        'name'        => $category['name'],
                        'children'    => $children_data,

                        'storecode' => STORECODE,
                        //           'sister'    => $sister_data,
                        //image  and description
                        'image'       => \Filesystem::getUrl('image/' . (empty($category['image']) ? 'no_image.png' : $category['image'])),
                        'description' => mb_substr(strip_tags(html_entity_decode($category['description'], ENT_QUOTES, 'UTF-8')), 0, 100) . '',
                        //
                        'href'        => $this->url->link('product/category', 'path=' . $category['category_id']),
                        'manufacturer' => $this->model_catalog_category->getManufacturerByCategoryId($category['category_id'])
                    );


                }
            }


            //$json=$this->session;
            $this->response->addHeader('Content-Type: application/json');
            $this->response->addHeader('Access-Control-Allow-Origin: *');
            $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
            $this->response->addHeader('Access-Control-Allow-Credentials: true');
            //$json['cookie'] = $_COOKIE[$this->session->getId()];
            $this->response->setOutput(json_encode($json));

        } catch (Exception $e) {
            http_response_code(500);
            $json['error']['warning'] = 'Internal Server Error';
        }
    }

    public function GetCategoryInfo(){
        try {
            $this->load->language('api/login');
            $json = array();

            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;
            $language_id = $this->_getLanguageId($params->locale); //should be 'en', 'ar', 'ar-SA' ... etc        

            $this->load->model('account/api');

            if (!isset($this->session->data['api_id'])) {
                $json['error']['warning'] = $this->language->get('error_permission');
             } else {
                $category_id = $params->category_id;

                $this->load->model('catalog/category');

                $category_info = $this->model_catalog_category->getCategory($category_id, $language_id);

                $image = \Filesystem::getUrl('image/' . $category_info['image']);

                if ($category_info) {
                    $json['CategoryInfo'] = array(
                        'Category_Id' => $category_info['category_id'],
                        'Image' => $image,
                        'Name' => strip_tags(htmlspecialchars_decode($category_info['name'])),
                        'ShortDescription' => (mb_substr(html_entity_decode($category_info['description'], ENT_QUOTES, 'UTF-8'), 0, 20)),
                        'seller_id' => $this->getSellerId($category_info),
                        'SubCategories' => $this->getSubCategories($category_id),
                        'brands' => $this->getBrands($category_id)
                    );
                }
            }

            $this->response->addHeader('Content-Type: application/json');
            $this->response->addHeader('Access-Control-Allow-Origin: *');
            $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
            $this->response->addHeader('Access-Control-Allow-Credentials: true');

            $this->response->setOutput(json_encode($json));
        }
        catch(Exception $e) {

        }
    }

    public function GetCategoryProducts(){
        try {
            $this->load->language('api/login');
            $json = array();

            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;
            $this->load->model('account/api');


            if (!isset($this->session->data['api_id'])) {
                $json['error']['warning'] = $this->language->get('error_permission');
            } else {
                $category_id = $params->category_id  ?: $this->request->post['category_id'];
                $language_id = $this->_getLanguageId($params->locale); //should be 'en', 'ar', 'ar-SA' ... etc
                $start = $params->start  ?: $this->request->post['start'];
                $limit = $params->limit  ?: $this->request->post['limit'];
                $filterText = $params->filterText ?: $this->request->post['filterText'];
                $deals_set = $params->deals ?: $this->request->post['deals'];

                $deals = isset($deals_set) ? (boolean)$deals_set : null;

                $json['Products'] = array();

                $this->load->model('catalog/product');

                $data = array(
                    'filter_name'         => $filterText,
                    'filter_tag'          => $filterText,
                    'filter_description'  => 'true',
                    'filter_category_id'  => 0,
                    'filter_sub_category' => '',
                    'sort'                => 'p.date_added',
                    'order'               => 'DESC',
                    'start'               => $start,
                    'limit'               => $limit,
                );

                if($filterText == null) {
                    $data = array(
                        'filter_category_id' => $category_id,
                        'sort' => 'p.date_added',
                        'order' => 'DESC',
                        'start' => $start,
                        'limit' => $limit,
                    );
                }


                $results = $this->model_catalog_product->getProducts($data, $language_id);

                foreach ($results as $result) {
                    if ($result['image']) {
                        $image = \Filesystem::getUrl('image/' . $result['image']);
                    } else {
                        $image = \Filesystem::getUrl('image/no_image.jpg');
                    }

                    if ($this->customer->isCustomerAllowedToViewPrice()) {
                        $price = $this->currency->format($this->tax->calculate($result['price'],
                            $result['tax_class_id'], $this->config->get('config_tax')), '', '', false);

                        $float_price = $this->tax->calculate(
                            $result['price'],
                            $result['tax_class_id'],
                            $this->config->get('config_tax')
                        );
                    } else {
                        $price = false;
                    }

                    if ((float)$result['special']) {
                        $special = $this->currency->format($this->tax->calculate($result['special'],
                            $result['tax_class_id'], $this->config->get('config_tax')), '', '', false);

                        $float_special = $this->tax->calculate(
                            $result['special'],
                            $result['tax_class_id'],
                            $this->config->get('config_tax')
                        );
                    } else {
                        $special = false;
                    }

                    if (isset($deals)) {
                       if ($deals && !$special) {
                           continue;
                       }
                    }
                    ///check permissions to view Add to Cart Button
						$viewAddToCart = true;
						$hidCartConfig = $this->config->get('config_hide_add_to_cart');
						if(($result['quantity'] <=0 && $hidCartConfig) || !$this->customer->isCustomerAllowedToAdd_cart)
						{
							$viewAddToCart = false;
						}


                    $json['Products'][] = array(
                        'product_id' => $result['product_id'],
                        'image' => $image,
                        'name' => $result['name'],
                        'price' => $price,
                        'float_price' => $float_price,
                        'special' => $special,
                        'viewAddToCartBtn'=>$viewAddToCart,
                        'float_special' => $float_special,
                        'currency' => $this->currency->getCode(),
                        'short_description' => (mb_substr(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'), 0, 25)),
                    );
                }
            }

            $this->response->addHeader('Content-Type: application/json');
            $this->response->addHeader('Access-Control-Allow-Origin: *');
            $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
            $this->response->addHeader('Access-Control-Allow-Credentials: true');

            $this->response->setOutput(json_encode($json));
        }
        catch(Exception $e) {

        }
    }


    /**
     * RECURSIVE FUNCTION TO GET ALL SUB CATEGORIES
     * @param $category_id
     * @return array
     */
    protected function getSubCategories($category_id): array
    {
        $sub_categories = $this->model_catalog_category->getCategories($category_id);

        $SubCategoryArray = array();

        foreach ($sub_categories as $sub_category) {
            $subSubcategory = $this->getSubCategories($sub_category['category_id']);
                $SubCategoryArray[] = array(
                    'category_id' => $sub_category['category_id'],
                    'name' => strip_tags(htmlspecialchars_decode($sub_category['name'])),
                    'image' => \Filesystem::getUrl('image/' . (empty($sub_category['image']) ? 'no_image.png' : $sub_category['image'])),
                    'icon_image' => \Filesystem::getUrl('image/' . (empty($sub_category['icon']) ? 'no_image.png' : $sub_category['icon'])),
                    'seller_id' => $this->getSellerId($sub_category),
                    'subCategories' =>  $subSubcategory ? $subSubcategory : []
                );

        }
        return $SubCategoryArray;
    }

    /**
     * @param $category_info
     * @return string
     */
    protected function getSellerId($category_info): string
    {
        $seller_id = "";
        preg_match_all('~<a(.*?)href="([^"]+)"(.*?)>~', htmlspecialchars_decode($category_info['description']), $matches);
        $query_str = parse_url(htmlspecialchars_decode($matches[2][0]), PHP_URL_QUERY);
        parse_str($query_str, $query_params);
        if ($query_params['seller_id'] != null) {
            $seller_id = $query_params['seller_id'];
        }
        return $seller_id;
    }

    /**
     * RECURSIVE FUNCTION TO GET ALL Brands
     * @param $category_id
     * @return array
     */
    protected function getBrands($category_id): array
    {
        $brands = $this->model_catalog_category->getManufacturerByCategoryId($category_id);

        foreach ($brands as &$brand) {
            $brand['image'] = \Filesystem::getUrl('image/' . (empty($brand['image']) ? 'no_image.png' : $brand['image']));
        }
        return $brands;
    }
    
    //Get Language id by it's locale code..
    private function _getLanguageId($locale){
        $this->load->model('localisation/language');
        return $this->model_localisation_language->getLanguageByLocale($locale)['language_id'];
    }
}
