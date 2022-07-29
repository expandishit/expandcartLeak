<?php
class ControllerModuleMobileApp extends Controller {
    private $error = array();

	public function index() {
        $this->language->load('module/mobile_app');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        //Save if the user has submitted the admin form (ie if someone has pressed save).
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('mobile_app', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->redirect($this->url->link('module/mobile_app', '', 'SSL'));
        }

        $this->load->model('tool/image');

        if (isset($this->request->post['mapp_logo_image'])) {
            $this->data['mapp_logo_image'] = $this->request->post['mapp_logo_image'];
        } else {
            $this->data['mapp_logo_image'] = $this->config->get('mapp_logo_image');
        }

        if ($this->config->get('mapp_logo_image')) {
            $this->data['mapp_logo_thumb'] = $this->model_tool_image->resize(
                $this->config->get('mapp_logo_image'), 150, 150
            );
        } else {
            $this->data['mapp_logo_thumb'] = $this->model_tool_image->resize('no_image.jpg', 150, 150);
        }

        $this->data['no_image'] = $this->model_tool_image->resize('no_image.jpg', 150, 150);

        if (isset($this->request->post['mapp_mobile_slides'])) {
            $mapp_mobile_slides = $this->request->post['mapp_mobile_slides'];
        } else {
            $mapp_mobile_slides = $this->config->get('mapp_mobile_slides');
        }

        $this->data['slides'] = array();

        $slide_counter = 0;

        for ($i = 0; $i < count($mapp_mobile_slides['image']); $i++) {
            $slide_thumbLink = $this->data['no_image'];

            if ($mapp_mobile_slides['image'][$i]) {
                $slide_thumbLink = $this->model_tool_image->resize($mapp_mobile_slides['image'][$i], 150, 150);
            }

            $this->data['slides'][] = array(
                'id' => $slide_counter,
                'thumbLink' => $slide_thumbLink,
                'imgLink' => $mapp_mobile_slides['image'][$i],
                'href_type' => $mapp_mobile_slides['href_type'][$i],
                'href_id' => $mapp_mobile_slides['href_id'][$i]
            );

            $slide_counter++;
        }

        //This creates an error message. The error['warning'] variable is set by the call to function validate() in this controller (below)
        if (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else {
            $this->data['error_warning'] = '';
        }

        if (isset($this->request->post['mapp_store_slogan'])) {
            $this->data['mapp_store_slogan'] = $this->request->post['mapp_store_slogan'];
        } else {
            $this->data['mapp_store_slogan'] = $this->config->get('mapp_store_slogan');
        }

        if (isset($this->request->post['mapp_main_color'])) {
            $this->data['mapp_main_color'] = $this->request->post['mapp_main_color'];
        } else {
            $this->data['mapp_main_color'] = $this->config->get('mapp_main_color');
        }

        if (isset($this->request->post['mapp_infopage'])) {
            $this->data['mapp_infopage'] = $this->request->post['mapp_infopage'];
        } else {
            $this->data['mapp_infopage'] = $this->config->get('mapp_infopage');
        }

        if (isset($this->request->post['mapp_firstpromo'])) {
            $this->data['mapp_firstpromo'] = $this->request->post['mapp_firstpromo'];
        } else {
            $this->data['mapp_firstpromo'] = $this->config->get('mapp_firstpromo');
        }

        if (isset($this->request->post['mapp_secondpromo'])) {
            $this->data['mapp_secondpromo'] = $this->request->post['mapp_secondpromo'];
        } else {
            $this->data['mapp_secondpromo'] = $this->config->get('mapp_secondpromo');
        }

        if (isset($this->request->post['mapp_footerinfo'])) {
            $this->data['mapp_footerinfo'] = $this->request->post['mapp_footerinfo'];
        } else {
            $this->data['mapp_footerinfo'] = $this->config->get('mapp_footerinfo');
        }

        if (isset($this->request->post['mapp_featured_product'])) {
            $this->data['mapp_featured_product'] = $this->request->post['mapp_featured_product'];
        } else {
            $this->data['mapp_featured_product'] = $this->config->get('mapp_featured_product');
        }

        $this->load->model('catalog/product');

        if (isset($this->request->post['mapp_featured_product'])) {
            $products = explode(',', $this->request->post['mapp_featured_product']);
        } else {
            $products = explode(',', $this->config->get('mapp_featured_product'));
        }

        $this->data['products'] = array();

        foreach ($products as $product_id) {
            $product_info = $this->model_catalog_product->getProduct($product_id);

            if ($product_info) {
                $this->data['products'][] = array(
                    'product_id' => $product_info['product_id'],
                    'name'       => $product_info['name']
                );
            }
        }

        if (isset($this->request->post['mapp_featured_category'])) {
            $this->data['mapp_featured_category'] = $this->request->post['mapp_featured_category'];
        } else {
            $this->data['mapp_featured_category'] = $this->config->get('mapp_featured_category');
        }

        $this->load->model('catalog/category');

        if (isset($this->request->post['mapp_featured_category'])) {
            $categories = explode(',', $this->request->post['mapp_featured_category']);
        } else {
            $categories = explode(',', $this->config->get('mapp_featured_category'));
        }

        $this->data['categories'] = array();

        foreach ($categories as $category_id) {
            $category_info = $this->model_catalog_category->getCategory($category_id);

            if ($category_info) {
                $this->data['categories'][] = array(
                    'category_id' => $category_info['category_id'],
                    'name'       => $category_info['name']
                );
            }
        }

        if (isset($this->request->post['mapp_ava_category'])) {
            $this->data['mapp_ava_category'] = $this->request->post['mapp_ava_category'];
        } else {
            $this->data['mapp_ava_category'] = $this->config->get('mapp_ava_category');
        }

        if (isset($this->request->post['mapp_ava_category'])) {
            $mapp_available_categories = explode(',', $this->request->post['mapp_ava_category']);
        } else {
            $mapp_available_categories = explode(',', $this->config->get('mapp_ava_category'));
        }

        $this->data['mapp_available_categories'] = array();

        foreach ($mapp_available_categories as $category_id) {
            $mapp_available_category_info = $this->model_catalog_category->getCategory($category_id);

            if ($mapp_available_category_info) {
                $this->data['mapp_available_categories'][] = array(
                    'category_id' => $mapp_available_category_info['category_id'],
                    'name'       => $mapp_available_category_info['name']
                );
            }
        }

        //SET UP BREADCRUMB TRAIL. YOU WILL NOT NEED TO MODIFY THIS UNLESS YOU CHANGE YOUR MODULE NAME.
        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL')
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('module/mobile_app', '', 'SSL')
        );

        $this->load->model('catalog/information');

        $this->data['info_pages'] = array();

        $results = $this->model_catalog_information->getInformations();

        foreach ($results as $result) {
            $this->data['info_pages'][] = array(
                'page_id' => $result['information_id'],
                'page_name'          => $result['title']
            );
        }

        //Localization Strings
        $this->data['heading_title'] = $this->language->get('heading_title');
        $this->data['text_save'] = $this->language->get('text_save');
        $this->data['text_general_settings'] = $this->language->get('text_general_settings');
        $this->data['text_app_main_color'] = $this->language->get('text_app_main_color');
        $this->data['text_red'] = $this->language->get('text_red');
        $this->data['text_pink'] = $this->language->get('text_pink');
        $this->data['text_purple'] = $this->language->get('text_purple');
        $this->data['text_deep_purple'] = $this->language->get('text_deep_purple');
        $this->data['text_indingo'] = $this->language->get('text_indingo');
        $this->data['text_blue'] = $this->language->get('text_blue');
        $this->data['text_light_blue'] = $this->language->get('text_light_blue');
        $this->data['text_cyan'] = $this->language->get('text_cyan');
        $this->data['text_teal'] = $this->language->get('text_teal');
        $this->data['text_green'] = $this->language->get('text_green');
        $this->data['text_light_green'] = $this->language->get('text_light_green');
        $this->data['text_lime'] = $this->language->get('text_lime');
        $this->data['text_yellow'] = $this->language->get('text_yellow');
        $this->data['text_amber'] = $this->language->get('text_amber');
        $this->data['text_orange'] = $this->language->get('text_orange');
        $this->data['text_deep_orange'] = $this->language->get('text_deep_orange');
        $this->data['text_brown'] = $this->language->get('text_brown');
        $this->data['text_grey'] = $this->language->get('text_grey');
        $this->data['text_blue_grey'] = $this->language->get('text_blue_grey');
        $this->data['text_app_logo'] = $this->language->get('text_app_logo');
        $this->data['text_browse'] = $this->language->get('text_browse');
        $this->data['text_clear'] = $this->language->get('text_clear');
        $this->data['text_store_slogan'] = $this->language->get('text_store_slogan');
        $this->data['text_category_settings'] = $this->language->get('text_category_settings');
        $this->data['text_available_categories'] = $this->language->get('text_available_categories');
        $this->data['text_about_us_settings'] = $this->language->get('text_about_us_settings');
        $this->data['text_source_information_page'] = $this->language->get('text_source_information_page');
        $this->data['text_none_do_not_display'] = $this->language->get('text_none_do_not_display');
        $this->data['text_home_page'] = $this->language->get('text_home_page');
        $this->data['text_featured_products'] = $this->language->get('text_featured_products');
        $this->data['text_featured_categories'] = $this->language->get('text_featured_categories');
        $this->data['text_mobile_slide_show'] = $this->language->get('text_mobile_slide_show');
        $this->data['text_first_promotional_block'] = $this->language->get('text_first_promotional_block');
        $this->data['text_none_hide_this_block'] = $this->language->get('text_none_hide_this_block');
        $this->data['text_latest_products'] = $this->language->get('text_latest_products');
        $this->data['text_special_products'] = $this->language->get('text_special_products');
        $this->data['text_best_selling_products'] = $this->language->get('text_best_selling_products');
        $this->data['text_featured_products'] = $this->language->get('text_featured_products');
        $this->data['text_second_promotional_block'] = $this->language->get('text_second_promotional_block');
        $this->data['text_footer_info'] = $this->language->get('text_footer_info');
        $this->data['text_slide_link'] = $this->language->get('text_slide_link');
        $this->data['text_product'] = $this->language->get('text_product');
        $this->data['text_category'] = $this->language->get('text_category');
        $this->data['text_price'] = $this->language->get('text_price');
        $this->data['text_model'] = $this->language->get('text_model');
        $this->data['text_name'] = $this->language->get('text_name');
        $this->data['text_create_mobile_app_heading'] = $this->language->get('text_create_mobile_app_heading');
        $this->data['text_simple_pricing'] = $this->language->get('text_simple_pricing');
        $this->data['text_mobile_price'] = $this->language->get('text_mobile_price');
        $this->data['text_per_year'] = $this->language->get('text_per_year');
        $this->data['text_business_discount'] = $this->language->get('text_business_discount');
        $this->data['text_ultimate_discount'] = $this->language->get('text_ultimate_discount');
        $this->data['text_we_publish'] = $this->language->get('text_we_publish');
        $this->data['text_create_my_app'] = $this->language->get('text_create_my_app');
        $this->data['text_try_app'] = $this->language->get('text_try_app');
        $this->data['text_choose_plans'] = $this->language->get('text_choose_plans');
        $this->data['text_choose_plans_text'] = $this->language->get('text_choose_plans_text');
        $this->data['text_explore_plans'] = $this->language->get('text_explore_plans');
        $this->data['text_app_store_pub'] = $this->language->get('text_app_store_pub');
        $this->data['text_getmobileapps'] = $this->language->get('text_getmobileapps');
        $this->data['text_mobile_apps_sales'] = $this->language->get('text_mobile_apps_sales');
        $this->data['text_mobile_price_ultimate'] = $this->language->get('text_mobile_price_ultimate');

        $this->data['action'] = $this->url->link('module/mobile_app', '', 'SSL');

        $this->data['cancel'] = $this->url->link('module/mobile_app', '', 'SSL');

        $this->load->model('marketplace/common');

        $mobile_app_has_app = $this->model_marketplace_common->hasApp('mobile_app');

        $this->data['hasMobileApp'] = $mobile_app_has_app['hasapp'];

        $this->data['isTrial'] = PRODUCTID == 3 ? '1' : '0';
        $this->data['product_id'] = PRODUCTID;

        #required for paid apps/services
        $this->load->model('billingaccount/common');
        $timestamp = time(); # Get current timestamp
        $email = BILLING_DETAILS_EMAIL; # Clients Email Address to Login

        # Define WHMCS URL & AutoAuth Key
        $whmcsurl = MEMBERS_LINK;
        $autoauthkey = MEMBERS_AUTHKEY;
        $hash = sha1($email.$timestamp.$autoauthkey); # Generate Hash

        $billingAccess = '0';

        if ($this->user->hasPermission('access', 'billingaccount/plans') && ENABLE_BILLING == '1') {
            $billingAccess = '1';
        }

        $this->data['hasBillingAccess'] = $billingAccess == 1 ? '1' : '0';

        $tmpbuylink = 'cart.php?a=add';
        $tmpbuylink = ($this->language->get('code') == 'ar') ? $tmpbuylink . '&language=Arabic' : $tmpbuylink . '&language=English';

        if (PRODUCTID == 6 || PRODUCTID == 53) {
            $tmpbuylink = $tmpbuylink . '&promocode=MOBULTIMATE';
            $price = 399;
        } elseif (PRODUCTID == 8 || PRODUCTID == 50) {
            $tmpbuylink = $tmpbuylink . '&promocode=MOBENTERPRISE';
            $price = 0;
        } else {
            $price = 499;
        }
        //elseif (PRODUCTID == 4)
        //    $tmpbuylink = $tmpbuylink . '&promocode=MOBBUSINESS';

        $buyAndroidLink = ($billingAccess == "1") ? $whmcsurl . "?email=$email&timestamp=$timestamp&hash=$hash&goto=" . urlencode($tmpbuylink . '&pid=15') : '#';
        $buyIOSLink = ($billingAccess == "1") ? $whmcsurl . "?email=$email&timestamp=$timestamp&hash=$hash&goto=" . urlencode($tmpbuylink . '&pid=43') : '#';
        $buyAllLink = ($billingAccess == "1") ? $whmcsurl . "?email=$email&timestamp=$timestamp&hash=$hash&goto=" . urlencode($tmpbuylink . '&bid=2') : '#';

        $this->data['buylink'] = array(
            'android' => $buyAndroidLink,
            'ios' => $buyIOSLink,
            'all' => $buyAllLink
        );

        $this->data['price'] = $price;
        $this->data['originalprice'] = 499;

        $this->data['packageslink'] = $this->url->link('billingaccount/plans', '', 'SSL');

        $this->data['productsService'] = $this->url->link('module/mobile_app/getProducts', '', 'SSL');

        $this->data['categoryService'] = $this->url->link('module/mobile_app/getCategories', '', 'SSL');

        $this->data['token'] = null;

        //Choose which template file will be used to display this request.
        $this->template = 'module/mobile_app.expand';

        $this->children = array(
            'common/header',
            'common/footer',
        );

        //Send the output.
        $this->response->setOutput($this->render_ecwig());
    }

    public function getCategories() {
        $search_string = $this->request->get['search']['value'];
        $start = $this->request->get['start'];
        $limit = $this->request->get['length'];

        $json['Categories'] = array();

        $this->load->model('catalog/category');

        $data = array(
            'filter_name' => $search_string,
            'start' => $start,
            'limit' => $limit
        );

        $data_all = array(
            'filter_name' => $search_string
        );

        $results = $this->model_catalog_category->getCategoriesFiltered($data);
        $category_total_filtered = count($this->model_catalog_category->getCategoriesFiltered($data_all));
        $category_total = $this->model_catalog_category->getTotalCategories();

        $this->load->model('tool/image');

        foreach ($results as $result) {
            if ($result['image']) {
                $image = $this->model_tool_image->resize($result['image'], 30, 30);
            } else {
                $image = $this->model_tool_image->resize('no_image.jpg', 30, 30);
            }

            $json['Categories'][] = array(
                'category_id' => $result['category_id'],
                'image' => $image,
                'name' => $result['name']
            );
        }

        $json_data = array(
            "draw"            => intval( $this->request->get['draw'] ),
            "recordsTotal"    => intval( $category_total ),
            "recordsFiltered" => intval( $category_total_filtered ),
            "data"            => $json['Categories']
        );

        echo json_encode($json_data);
    }

    public function getProducts() {
        $search_string = $this->request->get['search']['value'];
        $start = $this->request->get['start'];
        $limit = $this->request->get['length'];

        $json['Products'] = array();

        $this->load->model('catalog/product');

        $data = array(
            'filter_name' => $search_string,
            'sort'  => 'p.date_added',
            'order' => 'DESC',
            'start' => $start,
            'limit' => $limit
        );

        $data_all = array(
            'sort'  => 'p.date_added',
            'order' => 'DESC',
            'start' => $start,
            'limit' => $limit
        );

        $results = $this->model_catalog_product->getProducts($data);
        $product_total_filtered = $this->model_catalog_product->getTotalProducts($data);
        $product_total = $this->model_catalog_product->getTotalProducts($data_all);

        $this->load->model('tool/image');

        foreach ($results as $result) {
            if ($result['image']) {
                $image = $this->model_tool_image->resize($result['image'], 30, 30);
            } else {
                $image = $this->model_tool_image->resize('no_image.jpg', 30, 30);
            }

            $json['Products'][] = array(
                'product_id' => $result['product_id'],
                'image' => $image,
                'name' => $result['name'],
                'model' => $result['model'],
                'price' => $result['price'],
                'special' => $result['special']
            );
        }

        $json_data = array(
            "draw"            => intval( $this->request->get['draw'] ),
            "recordsTotal"    => intval( $product_total ),
            "recordsFiltered" => intval( $product_total_filtered ),
            "data"            => $json['Products']
        );

        echo json_encode($json_data);
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'module/mobile_app')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->error) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
}
