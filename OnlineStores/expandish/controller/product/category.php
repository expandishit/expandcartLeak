<?php
include DIR_APPLICATION. 'app/Traits/category/categoryHelper.php';

class ControllerProductCategory extends Controller {

    /* Helper Methods */
    use CategoryHelper;

    public function index()
    {
        $this->language->load_json('product/category');
        $this->load->model('catalog/category');
        $this->load->model('catalog/product');

        //if there is no path, But a slug found , then evaluate path from slug
        if ($this->preAction->isActive()) {
            if (isset($this->request->get['slug']) && !isset($this->request->get['path'])) {
                $name = $this->request->get['slug'];
                $data = $this->model_catalog_category->getCategoryByName($name);

                if ($data) {
                    $this->request->get['path'] = (
                        ($data->row['parent_id'] ? $data->row['parent_id'] . '_' : '') . $data->row['category_id']
                    );
                }
            }
        }

        //Get category_id from path key in request params
        $category_id = isset($this->request->get['path']) ? $this->getCategoryIdFromPath($this->request->get['path']) : 0;

        //Get Category info
        $category_info = $this->model_catalog_category->getCategory($category_id);

        if ($category_info || $this->request->get['path'] == 'deals' || $this->request->get['path'] == 'gry_allCategories') {

            $this->document->setTitle($category_info['name']);
            $this->document->setDescription($category_info['meta_description']);
            $this->document->setKeywords($category_info['meta_keyword']);

            $this->data['category_name'] = $category_info['name'];
            $this->data['category_description'] = $category_info['description'];
            $this->data['category_parent_id'] = $category_info['parent_id'];

            //Create breadcrumbs links.
            $this->data['breadcrumbs'] = $this->createBreadcrumbs($category_info);

            $this->data['thumb'] = $this->data['image'] = '';

            if ($category_info['image']) {
                $this->load->model('tool/image');
                $this->data['thumb'] = $this->model_tool_image->resize($category_info['image'], $this->config->get('config_image_category_width'), $this->config->get('config_image_category_height'));
                $this->data['image'] = $category_info['image'];
            }

            //Category description
            $this->data['description'] = html_entity_decode($category_info['description'], ENT_QUOTES, 'UTF-8');
            $this->data['compare'] = $this->url->link('product/compare');
            preg_match_all('~<a(.*?)href="([^"]+)"(.*?)>~', $this->data['description'], $matches);
            $query_str = parse_url(htmlspecialchars_decode($matches[2][0]), PHP_URL_QUERY);
            parse_str($query_str, $query_params);
            if ($query_params['seller_id'] != null) {
                $this->data['seller_to_category'] = $query_params['seller_id'];
            }

            $url = $this->formatUrlFromRequestParams();

            //Mega Filter
            $fmSettings = $this->config->get('mega_filter_settings');
            if (!empty($fmSettings['not_remember_filter_for_subcategories']) && false !== ($mfpPos = strpos($url, '&mfp='))) {
                $mfSt = mb_strpos($url, '&', $mfpPos + 1, 'utf-8');
                $url = $mfSt === false ? '' : mb_substr($url, $mfSt, mb_strlen($url, 'utf-8'), 'utf-8');
            } else if (empty($fmSettings['not_remember_filter_for_subcategories']) && false !== ($mfpPos = strpos($url, '&mfp='))) {
                $url = preg_replace('/,?path\[[0-9_]+\]/', '', $url);
            }

            $this->data['config_category_product_count'] = $this->config->get('config_category_product_count');

            $this->data['products'] = [];
            $sort  = $this->getSortValue();
            $order = $this->getSortOrderValue();
            $page  = $this->getPageNumber();
            $filter= $this->getFilterValue();
            $limit = $this->getLimitValue();

            if ($this->request->get['path'] != 'deals') {
                $this->data['categories'] = [];

                $results = $this->model_catalog_category->getCategories($category_id);

                $categoryCount = 0;
                $categoryWithIconCount = 0;
                $categoryWithImageCount = 0;
                $categoryURL = preg_replace("(\?page=[0-9]*|&page=[0-9]*)", "", $url);
                foreach ($results as $result) {
                    $categoryCount++;
                    $thumb = $this->getCategoryThumb($result['image'], $result['icon'], $categoryWithImageCount, $categoryWithIconCount);
                    $this->data['categories'][] = array(
                        'category_id' => $result['category_id'],
                        'name' => $result['name'],
                        'image' => $result['image'],
                        'icon' => $thumb['icon'],
                        'icon_src' => $thumb['icon_src'],
                        'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '_' . $result['category_id'] . $categoryURL),
                        'thumb' => $thumb['icon']
                    );
                }

                $this->data['category_count'] = $categoryCount;
                $this->data['category_with_icon_count'] = $categoryWithIconCount;
                $this->data['category_with_img_count'] = $categoryWithImageCount;

                $data = array(
                    'filter_category_id' => $category_id,
                    'filter_filter' => $filter,
                    'sort' => $sort,
                    'order' => $order,
                    'start' => ($page - 1) * $limit,
                    'limit' => $limit,
                    'display_page' => 'category'
                );

                if (!empty($fmSettings['show_products_from_subcategories'])) {
                    if (!empty($fmSettings['level_products_from_subcategories'])) {
                        $fmLevel = (int)$fmSettings['level_products_from_subcategories'];
                        $fmPath = explode('_', empty($this->request->get['path']) ? '' : $this->request->get['path']);

                        if ($fmPath && count($fmPath) >= $fmLevel) {
                            $data['filter_sub_category'] = '1';
                        }
                    } else {
                        $data['filter_sub_category'] = '1';
                    }
                }
                if (!empty($fmSettings['in_stock_default_selected'])) {
                    $this->data['mfp_column_left'] = true;
                    $this->data['mfp_column_right'] = true;
                    $this->data['mfp_content_top'] = true;
                }
                if (!empty($this->request->get['manufacturer_id'])) {
                    $data['filter_manufacturer_id'] = (int)$this->request->get['manufacturer_id'];
                }
            } else {
                $this->document->setTitle($this->language->get('text_deals'));

                $data = array(
                    'path' => 'deals',
                    'sort' => $sort,
                    'order' => $order,
                    'start' => ($page - 1) * $limit,
                    'limit' => $limit
                );
            }

            $product_total = $this->model_catalog_product->getTotalProducts($data);

            $results = $this->model_catalog_product->getProducts($data);

            $this->load->model('module/dedicated_domains/domain_prices');
            if ($this->model_module_dedicated_domains_domain_prices->isActive()) {
                $limit = $data['limit'];
                $start = $data['start'];
                if ($product_total > count($results) && count($results) < $limit) {
                    while (true) {
                        if ($product_total <= count($results) || count($results) == $limit || $product_total <= ($start + count($results))) {
                            break;
                        }
                        $data['start'] = $data['start'] + $data['limit'];
                        $data['limit'] = $limit - count($results);
                        $results = $results + $this->model_catalog_product->getProducts($data);
                    }
                }
            }

            //Get config once
            $show_seller = $this->config->get('show_seller');
            $show_attr = $this->config->get('config_show_attribute');
            $config_language_id = $this->config->get('config_language_id');

            //Login Display Prices
            $config_customer_price = $this->config->get('config_customer_price');

            $this->data['show_prices_login'] = true;
            if ((($config_customer_price && $this->customer->isLogged()) || !$config_customer_price)) {
                $this->data['show_prices_login'] = false;
            }

            //products loop
            foreach ($results as $result) {
                //get default product image if product has no image
                if ( ($result['image'] == NULL || $result['image']=="image/no_image.jpg") && file_exists(' ' . $this->config->get('product_image_without_image')) . ' '   ){
                    $result['image']=$this->config->get('product_image_without_image');
                }

                if ($result['image']) {
                    $image = $this->model_tool_image->resize($result['image'], $this->config->get('config_image_product_width'), $this->config->get('config_image_product_height'));
                } else {
                    $image = false;
                }

                //this for swap image

                $images = $this->model_catalog_product->getProductImages($result['product_id']);

                if (isset($images[0]['image']) && !empty($images[0]['image'])) {
                    $images = $images[0]['image'];
                }

                //
                $isCustomerAllowedToViewPrice = $this->customer->isCustomerAllowedToViewPrice();
                $price = false;
                if ($isCustomerAllowedToViewPrice) {
                    if ($this->config->get('config_tax') == "price") {
                        $price = $this->currency->format($result['price']);
                    } else {
                        $price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')));
                    }
                }

                $special = false;
                if ((float)$result['special'] && $isCustomerAllowedToViewPrice) {
                    if ($this->config->get('config_tax') == "price") {
                        $special = $this->currency->format($result['special']);
                    } else {
                        $special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')));
                    }
                }

                if ($this->config->get('config_tax')) {
                    $tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price']);
                } else {
                    $tax = false;
                }

                $this->data['review_status'] = $this->config->get('config_review_status');

                if ($this->config->get('config_review_status')) {
                    $rating = (int)$result['rating'];
                } else {
                    $rating = false;
                }

                if ($result['price'] > 0) {
                    $savingAmount = round((($result['price'] - $result['special']) / $result['price']) * 100, 0);
                } else {
                    $savingAmount = 0;
                }

                $stock_status = '';
                if ($result['quantity'] <= 0) {
                    $stock_status = $result['stock_status'];
                }
                ///check permissions to view Add to Cart Button
                $this->data['viewAddToCart'] = true;
                $hidCartConfig = $this->config->get('config_hide_add_to_cart');
                if (($result['quantity'] <= 0 && $hidCartConfig) || !$this->customer->isCustomerAllowedToAdd_cart) {
                    $this->data['viewAddToCart'] = false;
                }

                $seller = false;
                $seller_messaging = FALSE;
                $this->load->model('multiseller/status');

                $queryMultiseller = $this->db->query("SELECT extension_id FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");

                if ($queryMultiseller->num_rows && $show_seller) {
                    $seller_id = $this->MsLoader->MsProduct->getSellerId($result['product_id']);
                    $seller = $this->MsLoader->MsSeller->getSeller($seller_id);

                    if (!$seller) {
                        $seller_messaging = FALSE;
                    } else {
                        //Check if MS Messaging seller, Replace Add To Cart installed
                        $multisellerMessaging = $this->model_multiseller_status->is_messaging_installed();
                        $multisellerReplcAddtoCart = $this->model_multiseller_status->is_replace_addtocart();

                        if ($multisellerMessaging && $multisellerReplcAddtoCart && $seller_id != (int)$this->customer->getId())
                            $seller_messaging = true;
                    }
                }

                //Check show attribute status
                $show_attribute = false;
                if ($show_attr) {
                    $product_attribute = $this->db->query("SELECT pa.text , ad.name FROM " . DB_PREFIX . "product_attribute pa LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (pa.attribute_id = ad.attribute_id AND ad.language_id ='" . (int)$config_language_id . "')  WHERE pa.product_id = '" . (int)$result['product_id'] . "' AND pa.attribute_id = '" . (int)$show_attr . "' AND pa.language_id = '" . (int)$config_language_id . "' limit 1");

                    if ($product_attribute->row) {
                        $show_attribute = $product_attribute->row['name'] . ': ' . $product_attribute->row['text'];
                    }
                }
                $full_description_string = $result['description'];
                $result['description'] = strip_tags($result['description'], "<style>"); // remove all htmls tags but leave style tags
                $css_string = substr($result['description'], strpos($result['description'], "<style"), strpos($result['description'], "</style>")); //get css lines
                $sanitized_desc = utf8_substr(trim(str_replace($css_string, "", $result['description'])), 0, 300) . ' ...'; //remove css lines

                $viewAddToCart = true;
                $hideCartConfig = $this->config->get('config_hide_add_to_cart');
                if (($result['quantity'] <= 0 && $hideCartConfig) || !$this->customer->isCustomerAllowedToAdd_cart) {
                    $viewAddToCart = false;
                }

                $cart_products = [];
                foreach ($this->session->data['cart'] as $key => $quantity) {
                    $p = explode(':', $key);
                    if (isset($cart_products[$p[0]])) {
                        $cart_products[$p[0]] += $quantity;
                        continue;
                    }
                    $cart_products[$p[0]] = $quantity;
                }
                $this->data['products'][] = array(
                    'product_id' => $result['product_id'],
                    'thumb' => $image,
                    'image' => $result['image'],
                    'name' => $result['name'],
                    'description' => $sanitized_desc,
                    'price' => $price,
                    'special' => $special,
                    'date_available' => $result['date_available'],
                    'tax' => $tax,
                    'rating' => $result['rating'],
                    'reviews' => sprintf($this->language->get('text_reviews'), (int)$result['reviews']),
                    'href' => $this->url->link('product/product', 'path=' . $this->request->get['path'] . '&product_id=' . $result['product_id'] . $url),
                    // for swap image
                    'athumb_swap' => $this->model_tool_image->resize($images, $this->config->get('config_image_product_width'), $this->config->get('config_image_product_height')),
                    'image_swap' => $images,
                    //
                    // for saving percentage
                    'saving' => $savingAmount,
                    //
                    'stock_status' => $stock_status,
                    'stock_status_id' => $result['stock_status_id'],
                    'quantity' => $result['quantity'],
                    'minimum_limit' => $result['minimum'],

                    'manufacturer_id' => $result['manufacturer_id'],
                    'manufacturer' => $result['manufacturer'],
                    'manufacturerimg' => $result['manufacturerimg'],
                    'manufacturer_href' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $result['manufacturer_id']),
                    'has_seller' => $seller ? 1 : 0,
                    'seller_nickname' => $seller ? $seller['ms.nickname'] : '',
                    'seller_href' => $seller ? $this->url->link('seller/catalog-seller/profile', 'seller_id=' . $seller['seller_id'] . $url) : '',
                    'attribute' => $show_attribute,
                    'seller_messaging' => $seller_messaging,
                    'seller_id' => $seller ? $seller['seller_id'] : '',
                    'messaging_seller_href' => $seller ? $this->url->link('account/messagingseller', 'seller_id=' . $seller['seller_id'] . '&product_id=' . $result['product_id']) : '',
                    'subtract_stock' => $this->config->get('product_default_subtract_stock'),
                    'option' => $result['option'],
                    'in_wishlist' => $result['in_wishlist'],
                    'in_cart' => array_key_exists($result['product_id'], $cart_products) ? $cart_products[$result['product_id']] : 0,
                    'display_price' => $result['display_price'],
                    'viewAddToCart' => $viewAddToCart,
                    'general_use' => $result['general_use'] ?? '',
                    'categories_ids' => $this->model_catalog_product->getCategories($result['product_id']),
                    'full_description_string' => $full_description_string
                );
            }

            $this->data['sorts']  = $this->getSortsList();
            $this->data['limits'] = $this->getLimits();

            //Create Pagination Object...
            $pagination = new Pagination();
            $pagination->total = $product_total;
            $pagination->page  = $page;
            $pagination->limit = $limit;
            $pagination->text  = $this->language->get('text_pagination');
            $pagination->url   = $this->url->link('product/category', 'path=' . $this->request->get['path'] . $this->formatUrlFromRequestParams(['mfp', 'filter', 'sort', 'order', 'limit']) . '&page={page}');
            $this->data['pagination'] = $pagination->render();

            $this->data['sort']  = $sort;
            $this->data['order'] = $order;
            $this->data['limit'] = $limit;

            //$this->data['continue'] = $this->url->link('common/home');
            $this->load->model('setting/setting');
            $this->data['integration_settings'] = $this->model_setting_setting->getSetting('integrations');

            if ($this->data['integration_settings']['mn_criteo_status']) {
                // Criteo
                $this->data['criteo_email'] = "";

                if ($this->customer->isLogged()) {
                    $this->data['criteo_email'] = $this->customer->getEmail();
                }
            }

            if (isset($this->request->get['mfilterAjax'])) {
                $settings = $this->config->get('mega_filter_settings');
                $baseTypes = array('stock_status', 'manufacturers', 'rating', 'attributes', 'price', 'options', 'filters');

                if (isset($this->request->get['mfilterBTypes'])) {
                    $baseTypes = explode(',', $this->request->get['mfilterBTypes']);
                }

                if (!empty($settings['calculate_number_of_products']) || in_array('categories:tree', $baseTypes)) {
                    if (empty($settings['calculate_number_of_products'])) {
                        $baseTypes = array('categories:tree');
                    }

                    $this->load->model('module/mega_filter');

                    $idx = 0;

                    if (isset($this->request->get['mfilterIdx']))
                        $idx = (int)$this->request->get['mfilterIdx'];

                    $this->data['mfilter_json'] = json_encode(MegaFilterCore::newInstance($this, NULL)->getJsonData($baseTypes, $idx));
                }

                foreach ($this->children as $mf_child) {
                    $mf_child = explode('/', $mf_child);
                    $mf_child = array_pop($mf_child);
                    $this->data[$mf_child] = '';
                }

                $this->children = array();
                //$this->data['header'] = $this->data['footer'] = '';
            }

            if (!empty($this->data['breadcrumbs']) && !empty($this->request->get['mfp'])) {

                foreach ($this->data['breadcrumbs'] as $mfK => $mfBreadcrumb) {
                    $mfReplace = preg_replace('/path\[[^\]]+\],?/', '', $this->request->get['mfp']);
                    $mfFind = (mb_strpos($mfBreadcrumb['href'], '?mfp=', 0, 'utf-8') !== false ? '?mfp=' : '&mfp=');

                    $this->data['breadcrumbs'][$mfK]['href'] = str_replace(array(
                        $mfFind . $this->request->get['mfp'],
                        '&amp;mfp=' . $this->request->get['mfp'],
                        $mfFind . urlencode($this->request->get['mfp']),
                        '&amp;mfp=' . urlencode($this->request->get['mfp'])
                    ), $mfReplace ? $mfFind . $mfReplace : '', $mfBreadcrumb['href']);
                }
            }

            //Set template
            if ($this->request->get['path'] == 'gry_allCategories') {
                $this->getGryAllCategories();
            } else {
                $this->template = $this->checkTemplate('product/category.expand');
            }

            $this->children = [ 'common/footer' , 'common/header' ];
            $this->response->setOutput($this->render_ecwig());

        } else {
            // if there is no path & category_id = 0

            $this->data['breadcrumbs'][] = array(
                'text' => $this->language->get('text_error'),
                'href' => $this->url->link('product/category', $this->formatUrlFromRequestParams()),
                'separator' => $this->language->get('text_separator')
            );

            $this->document->setTitle($this->language->get('text_error'));

            $this->data['heading_title'] = $this->language->get('text_error');

            $this->data['text_error'] = $this->language->get('text_error');

            $this->data['button_continue'] = $this->language->get('button_continue');

            $this->data['continue'] = $this->url->link('common/home');

            $this->template = $this->checkTemplate('error/not_found.expand');

            $this->children = ['common/footer','common/header'];

            $this->response->setOutput($this->render_ecwig());
        }
    }

    public function getSubCategories(){

        $responsData = ['status' => 'failed'];

        if($this->request->get['parent']){
            $parent = $this->request->get['parent'];

            $show_droplist = $this->request->get['parent'] ? 1 : 0;

            $this->load->model('catalog/category');
            $categories = $this->model_catalog_category->getCategories($parent, $show_droplist, 'name_only');

            $data = [];
            foreach ($categories as $category) {
                $data[] = ['id' => $category['category_id'], 'name' => $category['name'] ];
            }

            $responsData = [ 'status' => 'success', 'data' => $data ];
        }

        if($this->request->get['json']){
            $this->response->setOutput(json_encode($responsData));
            return;
        }

        return $responsData;
    }


    public function get_category_products(){
        $responsData = ['status' => 'failed'];

        if($this->request->get['category_id']){
            $category_id = $this->request->get['category_id'];

            $isCustomerAllowedToViewPrice = $this->customer->isCustomerAllowedToViewPrice();

            if (isset($this->request->get['filter'])) {
                $filter = $this->request->get['filter'];
            } else {
                $filter = '';
            }

            $defaultSortingColumn = $this->config->get('config_products_default_sorting_column');

            $defaultSortingColumnBy = (!empty($this->config->get('config_products_default_sorting_by_column'))) ? $this->config->get('config_products_default_sorting_by_column') : 'ASC';

            if (\Extension::isinstalled('product_sort') && $this->config->get('product_sort_app_status') == '1') {
                $sort  = "product_manual_sort";
                $order = $defaultSortingColumnBy;
            } else if (isset($this->request->get['sort'])) {
                $sort = $this->request->get['sort'];
            } else {

                if($defaultSortingColumn === 'name'){
                    $sort  = "pd.name";
                    $order = $defaultSortingColumnBy; //From A-to-Z
                }
                elseif($defaultSortingColumn === 'date_added'){
                    $sort  = "p.date_added";
                    $order = $defaultSortingColumnBy;
                }
                elseif($defaultSortingColumn === 'stock_status'){
                    $sort  = "stock_status";
                    $order = $defaultSortingColumnBy;
                }
                else{ //Date_Available
                    $sort = ! empty( $defaultSortingColumn ) ? "p.{$defaultSortingColumn}" : 'p.sort_order' ;
                }
            }

            if (isset($this->request->get['order'])) {
                $order = $this->request->get['order'];
            } else if(empty($order)){
                $order =  $defaultSortingColumnBy;
            }

            if (isset($this->request->get['page'])) {
                $page = $this->request->get['page'];
            } else {
                $page = 1;
            }

            if (isset($this->request->get['limit'])) {
                $limit = $this->request->get['limit'];
            } else {
                $limit = $this->config->get('config_catalog_limit');
            }

            $this->load->model('catalog/product');
            $this->load->model('tool/image');

            $data = array(
                'filter_category_id' => $category_id,
                'filter_filter'      => $filter
            );

            $product_total = $this->model_catalog_product->getTotalProducts($data);

            $data = array(
                'filter_category_id' => $category_id,
                'filter_filter'      => $filter,
                'sort'               => $sort,
                'order'              => $order,
                'start'              => ($page - 1) * $limit,
                'limit'              => $limit,
                'display_page'       => 'category'
            );
            $results = $this->model_catalog_product->getProducts($data);

            $this->load->model('module/dedicated_domains/domain_prices');

            if($this->model_module_dedicated_domains_domain_prices->isActive()) {
                $limit=$data['limit'];
                $start=$data['start'];
                if($product_total>count($results) && count($results)<$limit)
                {
                    while(true)
                    {
                        if($product_total<=count($results) || count($results)==$limit || $product_total<=($start+count($results)) )
                        {
                            break;
                        }
                        $data['start']=$data['start']+$data['limit'];
                        $data['limit']=$limit-count($results);
                        $results = $results+$this->model_catalog_product->getProducts($data);
                    }
                }
            }

            //Get config once
            $show_seller = $this->config->get('show_seller');
            $show_attr   = $this->config->get('config_show_attribute');
            $config_language_id = $this->config->get('config_language_id');

            //Login Display Prices
            $config_customer_price = $this->config->get('config_customer_price');

            $responsData['show_prices_login'] = true;
            if((($config_customer_price && $this->customer->isLogged()) || !$config_customer_price)){
                $responsData['show_prices_login'] = false;
            }


            $products = array();

            foreach ($results as $result) {
                //get default product image if product has no image
                if ( ($result['image'] == NULL || $result['image']=="image/no_image.jpg") && file_exists(' ' . $this->config->get('product_image_without_image')) . ' '   ){
                    $result['image']=$this->config->get('product_image_without_image');
                }

                if ($result['image']) {
                    $image = $this->model_tool_image->resize($result['image'], $this->config->get('config_image_product_width'), $this->config->get('config_image_product_height'));
                } else {
                    $image = false;
                }

                //this for swap image

                $images = $this->model_catalog_product->getProductImages($result['product_id']);

                if(isset($images[0]['image']) && !empty($images[0]['image'])){
                  $images =$images[0]['image'];
               }

                //

                $price = false;
                if ($isCustomerAllowedToViewPrice) {
                    if ( $this->config->get('config_tax') == "price" ) {
                        $price = $this->currency->format( $result['price'] );
                    } else {
                        $price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')));
                    }
                }

                $special = false;
                if ((float)$result['special'] && $isCustomerAllowedToViewPrice) {
                    if ( $this->config->get('config_tax') == "price" ) {
                        $special = $this->currency->format( $result['special'] );
                    } else {
                        $special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')));
                    }
                }

                if ($this->config->get('config_tax')) {
                    $tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price']);
                } else {
                    $tax = false;
                }

                $responsData['review_status'] = $this->config->get('config_review_status');
                $reviews_img = false;

                if ($this->config->get('config_review_status')) {
                    $rating = (int)$result['rating'];

                    if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' .  $this->registry->get('config')->get('config_template') . '/' . 'image/stars-' . $rating . '.png')) {
                        $reviews_img = 'expandish/view/theme/customtemplates/' . STORECODE . '/' . $this->registry->get('config')->get('config_template') . '/' . 'image/stars-' . $rating . '.png';
                    } else {
                        if (IS_CUSTOM_TEMPLATE == 1) {
                            $reviews_img = CUSTOM_TEMPLATE_PATH . '/' . $this->registry->get('config')->get('config_template') . '/' . 'image/stars-' . $rating . '.png';
                        } else {
                            $reviews_img = 'expandish/view/theme/' . $this->registry->get('config')->get('config_template') . '/' . 'image/stars-' . $rating . '.png';
                        }
                    }
                } else {
                    $rating = false;
                }

                if ($result['price'] > 0) {
                    $savingAmount = round((($result['price'] - $result['special'])/$result['price'])*100, 0);
                }
                else {
                    $savingAmount = 0;
                }

                $stock_status = '';
                if ($result['quantity'] <= 0) {
                    $stock_status = $result['stock_status'];
                }
                ///check permissions to view Add to Cart Button
                $responsData['viewAddToCart'] = true;
                $hidCartConfig = $this->config->get('config_hide_add_to_cart');
                if(($result['quantity'] <=0 && $hidCartConfig) || !$this->customer->isCustomerAllowedToAdd_cart )
                {
                    $responsData['viewAddToCart'] = false;
                }

                $seller = false;
                $seller_messaging = FALSE;
                $this->load->model('multiseller/status');

                $queryMultiseller = $this->db->query("SELECT extension_id FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");

                if($queryMultiseller->num_rows && $show_seller){
                    $seller_id = $this->MsLoader->MsProduct->getSellerId($result['product_id']);
                    $seller    = $this->MsLoader->MsSeller->getSeller($seller_id);

                    if (!$seller) {
                        $seller_messaging = FALSE;
                    } else {
                        //Check if MS Messaging seller, Replace Add To Cart installed
                        $multisellerMessaging = $this->model_multiseller_status->is_messaging_installed();
                        $multisellerReplcAddtoCart = $this->model_multiseller_status->is_replace_addtocart();

                        if($multisellerMessaging && $multisellerReplcAddtoCart && $seller_id != (int)$this->customer->getId() )
                            $seller_messaging= true;
                    }
                }


                //Check show attribute status
                $show_attribute = false;
                if($show_attr){
                    $product_attribute = $this->db->query("SELECT pa.text , ad.name FROM " . DB_PREFIX . "product_attribute pa LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (pa.attribute_id = ad.attribute_id AND ad.language_id ='" . (int)$config_language_id ."')  WHERE pa.product_id = '" . (int)$result['product_id'] . "' AND pa.attribute_id = '" . (int)$show_attr . "' AND pa.language_id = '" . (int)$config_language_id ."' limit 1");

                    if($product_attribute->row){
                       $show_attribute = $product_attribute->row['name'].': '.$product_attribute->row['text'];
                   }
                }
                $full_description_string = $result['description'];
                $result['description'] = strip_tags($result['description'],"<style>"); // remove all htmls tags but leave style tags
                $css_string = substr($result['description'],strpos($result['description'],"<style"),strpos($result['description'],"</style>")); //get css lines
                $sanitized_desc = utf8_substr(trim(str_replace($css_string,"",$result['description'])), 0, 300) . ' ...'; //remove css lines

                $viewAddToCart = true;
                $hideCartConfig = $this->config->get('config_hide_add_to_cart');
                if(($result['quantity'] <=0 && $hideCartConfig) || !$this->customer->isCustomerAllowedToAdd_cart)
                {
                    $viewAddToCart = false;
                }

                $cart_products = [];
                foreach ($this->session->data['cart'] as $key => $quantity) {
                    $p = explode(':', $key);
                    if(isset($cart_products[$p[0]])){
                        $cart_products[$p[0]] += $quantity;
                        continue;
                    }
                    $cart_products[$p[0]] = $quantity;
                }
                $products[] = array(
                    'product_id'  => $result['product_id'],
                    'thumb'       => $image,
                    'image'       => $result['image'],
                    'name'        => $result['name'],
                    'description' => $sanitized_desc,
                    'price'       => $price,
                    'special'     => $special,
                    'date_available' => $result['date_available'],
                    'tax'         => $tax,
                    'rating'      => $result['rating'],
                    'reviews_img' => $reviews_img,
                    'reviews'     => sprintf($this->language->get('text_reviews'), (int)$result['reviews']),
                    'href'        => $this->url->link('product/product', 'path=' . $this->request->get['path'] . '&product_id=' . $result['product_id'] . $url),
                    // for swap image
                    'athumb_swap'  => $this->model_tool_image->resize($images, $this->config->get('config_image_product_width'), $this->config->get('config_image_product_height')),
                    'image_swap'   => $images,
                    //
                    // for saving percentage
                    'saving'    => $savingAmount,
                    //
                    'stock_status' => $stock_status,
                    'stock_status_id' => $result['stock_status_id'],
                    'quantity' => $result['quantity'],
                    'minimum_limit' => $result['minimum'],

                    'manufacturer_id' => $result['manufacturer_id'],
                    'manufacturer' => $result['manufacturer'],
                    'manufacturerimg' => $result['manufacturerimg'],
                    'manufacturer_href' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $result['manufacturer_id']),
                    'has_seller' => $seller ? 1 : 0,
                    'seller_nickname' => $seller ? $seller['ms.nickname'] : '',
                    'seller_href' => $seller ? $this->url->link('seller/catalog-seller/profile', 'seller_id=' . $seller['seller_id'] . $url) : '',
                    'attribute' => $show_attribute,
                    'seller_messaging'=>$seller_messaging,
                    'seller_id'=> $seller ?  $seller['seller_id'] : '',
                    'messaging_seller_href' => $seller ? $this->url->link('account/messagingseller', 'seller_id=' . $seller['seller_id'].'&product_id='.$result['product_id']) : '',
                    'subtract_stock' => $this->config->get('product_default_subtract_stock'),
                    'option'=>$result['option'],
                    'in_wishlist'=>$result['in_wishlist'],
                    'in_cart' => array_key_exists($result['product_id'], $cart_products) ? $cart_products[$result['product_id']] : 0,
                    'display_price'=>$result['display_price'],
                    'viewAddToCart'=>$viewAddToCart,
                    'general_use' => $result['general_use'] ?? '',
                    'categories_ids' => $this->model_catalog_product->getCategories($result['product_id']),
                    'full_description_string'=>$full_description_string
                );
            }

            $pagination = new Pagination();
            $pagination->total = $product_total;
            $pagination->page = $page;
            $pagination->limit = $limit;
            $pagination->text = $this->language->get('text_pagination');
            $pagination->url = $this->url->link('product/category/ger_category_products', 'category_id=' . $this->request->get['category_id'] . $url . '&page={page}');

            $responsData['status'] = 'success';
            $responsData['pagination'] = $pagination->render();
            $responsData['products'] = $products;

            $responsData['translations'] = [
                'Price_on_selection'    => $this->language->get('Price_on_selection'),
                'button_cart'           => $this->language->get('button_cart')
            ];

            if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' .  $this->registry->get('config')->get('config_template') . '/' . 'assets/images/icon/addtocart.png')) {
                $responsData['cart_img'] = 'expandish/view/theme/customtemplates/' . STORECODE . '/' . $this->registry->get('config')->get('config_template') . '/' . 'assets/images/icon/addtocart.png';
            } else {
                if (IS_CUSTOM_TEMPLATE == 1) {
                    $responsData['cart_img'] = CUSTOM_TEMPLATE_PATH . '/' . $this->registry->get('config')->get('config_template') . '/' . 'assets/images/icon/addtocart.png';
                } else {
                    $responsData['cart_img'] = 'expandish/view/theme/' . $this->registry->get('config')->get('config_template') . '/' . 'assets/images/icon/addtocart.png';
                }
            }
        }

        $this->response->setOutput(json_encode($responsData));
        return;
    }

    private function getGryAllCategories(){

        $this->load->model('catalog/manufacturer');
        $this->document->setTitle($this->language->get('text_categories'));

        $brands = $this->model_catalog_manufacturer->getAllManufacturers();
        foreach ($brands as $brand) {
            $this->data['brands'][] = array(
                'brand_id'  => $brand['manufacturer_id '],
                'name'          => $brand['name'],
                'image'         => \Filesystem::getUrl('image/' .$brand['image'])
            );
        }
        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/product/categories.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/product/all_categories.expand';
        }
        else {
            $this->template = $this->config->get('config_template') . '/template/product/all_categories.expand';
        }

    }
    public function getCategoriesPaging(){

        $responsData = ['status' => 'failed'];
        $this->load->model('catalog/category');

            $categories_content='';
            $limit = $this->request->post['limit'];
            if (isset($this->request->post['page_no'])) {
                $page_no = $this->request->post['page_no'];
            }else{
                $page_no = 1;
            }
            $offset = ($page_no-1) * $limit;
            $cat_data = array(
                'start'     => $offset,
                'limit'     => $limit
            );
        $categories = $this->model_catalog_category->getCategories(0,0,'',$cat_data);
        $data = [];
        foreach ($categories as $category) {
            $image = \Filesystem::getUrl('image/' .$category['image']);
            $href=$this->url->link('product/category', 'path=' . $this->request->get['path'] . '_' . $category['category_id'] );
            $data[] = ['id' => $category['category_id'],
                        'name' => $category['name'],
                        'image'=>$image,
                        'href'=>$href ];
        }
        $totalRecords = $this->model_catalog_category->getCategoriesNumRows();
        $totalPage = ceil($totalRecords/$limit);
        $responsData = [ 'status' => 'success', 'data' => $data,'total_records'=>$totalPage ];
        $this->response->setOutput(json_encode($responsData));
        return;

    }


    /**
     * Get Category Thumb
     * @param string $image
     * @param string $icon
     * @param int $imageCount
     * @param int $iconCount
     * @return array
     */
    private function getCategoryThumb($image , $icon, &$imageCount = 0, &$iconCount = 0): array
    {

        if ($icon) {
            $iconCount++;
            $categoryIcon = $this->model_tool_image->resize($icon, $this->config->get('config_image_product_width'), $this->config->get('config_image_product_height'));
            $categoryIconSrc = \Filesystem::getUrl('image/' . $icon);
        } else if ($image){
            $imageCount++;
            $categoryIcon = $this->model_tool_image->resize($image, $this->config->get('config_image_product_width'), $this->config->get('config_image_product_height'));
            $categoryIconSrc = \Filesystem::getUrl('image/' . $image);
            if ($categoryIconSrc == '' || !file_exists($categoryIconSrc)) {
                $categoryIconSrc = $categoryIcon;
            }
        }else {
            $categoryIcon = $this->model_tool_image->resize($this->config->get('no_image'), $this->config->get('config_image_product_width'), $this->config->get('config_image_product_height'));
            $categoryIconSrc = $categoryIcon;
        }

        return ['icon' => $categoryIcon , 'icon_src' => $categoryIconSrc];
    }


}
?>
