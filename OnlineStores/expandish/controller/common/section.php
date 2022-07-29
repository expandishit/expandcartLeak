<?php
class ControllerCommonSection extends Controller
{
    protected function index($section_data)
    {
        // route account/* no need to display section recent products.
        if (in_array($this->request->get['route'], [
            'account/account',
            'account/edit',
            'account/address',
            'account/wishlist',
            'account/newsletter',
            'account/messagingseller',
            'account/order',
            'account/download',
            'account/reward',
            'account/return',
            'account/transaction',
            'account/upgrade',
        ])) {
            return;
        }
            
        $section_fields = array();
        $collections = array();

        //$this->load->model('extension/section');

        $section_fields_info = $this->expandish->getSectionFields($section_data['section_id']);
        foreach ($section_fields_info as $section_field) {
            $field_value = $section_field['field_value'];

            if ($section_field['field_type'] == 'image' && $field_value != '') {
                $field_value = \Filesystem::getUrl('image/' . $field_value);
            }

            $section_fields[$section_field['field_codename']] = array('field_id' => $section_field['field_id'], 'field_value' => $field_value);
        }

        $collections_info = $this->expandish->getSectionCollections($section_data['section_id']);
        foreach ($collections_info as $collection_row) {
            $field_value = $collection_row['field_value'];

            if ($collection_row['field_type'] == 'image' && $field_value != '') {
                $field_value = \Filesystem::getUrl('image/' . $field_value);
            }

            $collections[$collection_row['collection_id']][$collection_row['field_codename']] =
                array('field_id' => $collection_row['field_id'], 'field_value' => $field_value);
        }

        $this->data['section_id'] = $section_data['section_id'];
        $this->data['fields'] = $section_fields;
        $this->data['collections'] = $collections;

        ////////////// Hide Layouts in case of view product in iframe 
        $this->data['hide_layouts'] = false;
        if(isset($this->request->get['iframe'])){
            $this->data['hide_layouts'] = true;
        }
        ///////////////////

        //Login Display Prices
         $config_customer_price = $this->config->get('config_customer_price');

         $this->data['show_prices_login'] = true;
         if((($config_customer_price && $this->customer->isLogged()) || !$config_customer_price)){
            $this->data['show_prices_login'] = false;
         }

         $pageCodeName = $this->expandish->getPageCodeName();
         if($pageCodeName == 'home'){
            ////////////// Category Droplist Filter
            $this->load->model('module/category_droplist');
            if($this->model_module_category_droplist->isActive()){
                $langCode = $this->config->get('config_language');

                $droplist_data = $this->config->get('category_droplist');
                $this->data['category_droplist']['status'] = true;
                $this->data['category_droplist']['levels'] = $droplist_data['levels'];
                $this->data['category_droplist']['lables'] = $droplist_data['lables'];
                $this->data['category_droplist']['title']  = $droplist_data['title'][$langCode];
                $this->data['category_droplist']['button']  = $droplist_data['button'][$langCode];
                $this->data['category_droplist']['form_url'] = $this->url->link('product/category');
                $this->data['category_droplist']['lang_code'] = $langCode;
                
                $this->data['category_droplist']['cols'] = round(12 / $droplist_data['levels']);
            }
            ///////////////////
            $this->load->model('catalog/category');
            $categories = $this->model_catalog_category->getCategories(0);
            $this->data['categories'] = $categories;
            $categoriesArray = explode(',', $categories);
            $this->data['hasOneCategory'] = count($categoriesArray) == 1 && $categoriesArray[0];
             $this->load->model('module/product_classification/settings');
             if($this->model_module_product_classification_settings->isActive()) {
                 $this->load->model('module/product_classification/brand');
                 $this->data['pc_brands'] = $this->model_module_product_classification_brand->getBrands();

                 $this->language->load_json('product/category');

                 $this->data['pc_enabled'] = true;
                 $this->data['pc_form_url'] = $this->url->link('product/product_classification');


                 // get app settings
                 $pcAppsettingsData = $this->model_module_product_classification_settings->getSettings();

                 $lang_id = $this->config->get('config_language_id');
                 $this->data['pc_form_title'] = (isset($pcAppsettingsData[$lang_id]['title']) && !empty($pcAppsettingsData[$lang_id]['title'])) ? $pcAppsettingsData[$lang_id]['title'] : $this->language->get('text_pc_form_title');
                 $this->data['pc_brand_text'] = (isset($pcAppsettingsData[$lang_id]['brand']) && !empty($pcAppsettingsData[$lang_id]['brand'])) ? $pcAppsettingsData[$lang_id]['brand'] : $this->language->get('text_pc_brand');
                 $this->data['pc_model_text'] = (isset($pcAppsettingsData[$lang_id]['model']) && !empty($pcAppsettingsData[$lang_id]['model'])) ? $pcAppsettingsData[$lang_id]['model'] : $this->language->get('text_pc_model');
                 $this->data['pc_year_text'] = (isset($pcAppsettingsData[$lang_id]['year']) && !empty($pcAppsettingsData[$lang_id]['year'])) ? $pcAppsettingsData[$lang_id]['year'] : $this->language->get('text_pc_year');

             }
        }
         
        //Landing page logic, if the URL contains "store" then avoid showing landing page else, check landing page parameter
        $this->data['landing_page'] = 0;
        if (strpos($_SERVER['REQUEST_URI'], "store") == 0){
            if (isset($this->request->get['landing_page']) || ($this->config->get('landing_page_is_default') == 1)) {
                $this->data['landing_page'] = isset($this->request->get['landing_page']) ? $this->request->get['landing_page'] : 1;
            }
        }

        $dirTemplate = (IS_CUSTOM_TEMPLATE == 1 ? DIR_CUSTOM_TEMPLATE : DIR_TEMPLATE);
        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/section/' . $section_data['region_codename'] . '/' . $section_data['section_codename'] . '.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/section/' . $section_data['region_codename'] . '/' . $section_data['section_codename'] . '.expand';
        }
        else if(file_exists($dirTemplate . $this->config->get('config_template') . '/template/section/' . $section_data['region_codename'] . '/' . $section_data['section_codename'] . '.expand')) {
            $this->template = $this->config->get('config_template') . '/template/section/' . $section_data['region_codename'] . '/' . $section_data['section_codename'] . '.expand';
        }
        else {
            return;
        }

        $this->render_ecwig();
    }
}
?>
