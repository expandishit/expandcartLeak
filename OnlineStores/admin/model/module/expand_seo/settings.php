<?php

class ModelModuleExpandSeoSettings extends Model
{
    /**
     * update the module settings.
     *
     * @param array $inputs
     *
     * @return bool|\Exception
     */
    public function updateSettings($inputs)
    {
        $this->load->model('setting/setting');

        $this->model_setting_setting->editSetting(
            'expand_seo', $inputs
        );

        return true;
    }

    public function checkSchemaExistence($schema)
    {
        $queryString = [];
        $queryString[] = 'SELECT * FROM `' . DB_PREFIX . 'expand_seo` WHERE';
        $queryString[] = 'seo_group="'.$schema['group'].'"';
        $queryString[] = 'AND';
        $queryString[] = 'language="'.$schema['parts']['languages'][0].'"';
        $data = $this->db->query(implode(' ', $queryString));

        return [
            'data' => $data->rows,
            'count' => $data->num_rows,
        ];
    }

    public function insertSchema($schema)
    {
        $queryString = $fields = [];
        $queryString[] = 'INSERT INTO `' . DB_PREFIX . 'expand_seo` SET';
        $fields[] = 'seo_group="' . $schema['group'] . '"';
        $fields[] = 'max_len="' . 200 . '"';
        $fields[] = 'schema_prefix="' . $schema['prefix'] . '"';
        $fields[] = 'schema_suffix="' . $schema['suffix'] . '"';
        $fields[] = "schema_parts='" . addslashes(json_encode($schema['parts'])) . "'";
        $fields[] = "schema_status='" . $schema['status'] . "'";
        $fields[] = "schema_url='" . htmlspecialchars($this->db->escape($schema['url'])) . "'";
        $fields[] = "fields_language='" . $schema['fields_language'] . "'";
        $fields[] = "schema_parameters='" . json_encode($schema['schema_parameters']) . "'";
        $fields[] = "language='" . $schema['language'] . "'";
        $queryString[] = implode(', ', $fields);

        $this->db->query(implode(' ', $queryString));
    }

    public function listSchemas()
    {
        $groupedData = $this->db->query('SELECT * FROM `' . DB_PREFIX . 'expand_seo`');

        $data = [];

        foreach ($groupedData->rows as $row) {
            $data[$row['seo_group']][$row['language']] = $row;
        }

        return $data;
    }

    public function dtHandler($start=0, $length=10, $search = null, $orderColumn="seo_id", $orderType="ASC") {
        $query = "SELECT * FROM " . DB_PREFIX . "expand_seo";
        //$query = ;
        $total = $this->db->query($query)->num_rows;
        $where = "";
        if (!empty($search)) {
            $where .= "(language like '%" . $this->db->escape($search) . "%' OR
            seo_group like '%" . $this->db->escape($search) . "%')";
            $query .= " WHERE " . $where;
        }

        $totalFiltered = $this->db->query($query)->num_rows;
        $query .= " ORDER by {$orderColumn} {$orderType}";

        if($length != -1) {
            $query .= " LIMIT " . $start . ", " . $length;
        }

        $results = $this->db->query($query)->rows;

        $domainName = HTTP_CATALOG;

        $expand_seo['schemas'] = $this->listSchemas();
        $expand_seo['settings'] = $this->config->get('expand_seo');
        $expand_seo['separators'] = [
            '-', '/', '_'
        ];

        $expand_seo['languages'] = [
            'en', 'ar'
        ];

        $expand_seo['groups'] = [
            'product/product' => 'products',
            'product/categories' => 'categories',
            'product/search' => 'search',
        ];

        $expand_seo['products']['fields'] = [
            '@product_id@' => $this->language->get('es_product_id'),
            '@slug@' => $this->language->get('es_product_name'),
        ];
        $expand_seo['fields'] = [
            'common/home' => ['name' => $this->language->get('es_home_page'),'fields' => null],
            'product/product' => [
                'name' => $this->language->get('text_product'),
                'fields' => [
                    '@product_id@' => $this->language->get('es_id'),
                    '@slug@' => $this->language->get('es_product_name'),
                ]
            ],
            'product/category' => [
                'name' => $this->language->get('text_category'),
                'fields' => [
                    '@path@' => $this->language->get('es_id'),
                    '@slug@' => $this->language->get('es_category_name'),
                ]
            ],
            'product/manufacturer/info' => [
                'name' => $this->language->get('text_manufacturer'),
                'fields' => [
                    '@manufacturer_id@' => $this->language->get('es_id'),
                    '@slug@' => $this->language->get('es_manufacturer_name'),
                ]
            ],
            'product/search' => [
                'name' => $this->language->get('es_search_pages'),
                'fields' => [
                    '@search@' => $this->language->get('es_search_name'),
                ]
            ],
            'product/compare' => ['name' => $this->language->get('es_compare_page'),'fields' => null],
            'product/special' => ['name' => $this->language->get('es_special_page'),'fields' => null],
            'checkout/cart' => ['name' => $this->language->get('es_cart_page'),'fields' => null],
            'checkout/checkout' => ['name' => $this->language->get('es_checkout_page'),'fields' => null],
            'information/sitemap' => ['name' => $this->language->get('es_sitemap_page'),'fields' => null],
            'information/contact' => ['name' => $this->language->get('es_contact_page'),'fields' => null],
            'blog/post' => [
                'name' => $this->language->get('es_blog_posts'),
                'fields' => [
                    '@post_id@' => $this->language->get('es_blog_post_id'),
                    '@slug@' => $this->language->get('es_blog_post_title'),
                ]
            ],
            'blog/category' => [
                'name' => $this->language->get('es_blog_categories'),
                'fields' => [
                    '@category_id@' => $this->language->get('es_blog_category_id'),
                    '@slug@' => $this->language->get('es_blog_category_title'),
                ]
            ],
            'information/information' => [
                'name' => $this->language->get('es_information_pages'),
                'fields' => [
                    '@information_id@' => $this->language->get('es_id'),
                    '@slug@' => $this->language->get('es_information_name'),
                ]
            ],
            'seller/catalog-seller/profile' => [
                'name' => $this->language->get('es_seller_profile_pages'),
                'fields' => [
                    '@seller_id@' => $this->language->get('es_seller_id'),
                    '@slug@' => $this->language->get('es_nick_name'),
                ]
            ]
        ];
        foreach ($results as $key => $result)
        {
            $results[$key]['seo_group']= ucfirst($result['seo_group']);
            $parameters = json_decode($result['schema_parameters'], true);

            $results[$key]['url_schema']=trim($domainName . ($expand_seo['settings']['es_append_language'] == 1 ? '{language}/' : '') . $result['schema_prefix'], '/')."/";


            $filtered_params='';
            foreach ($parameters as $parameter){
                $filtered_params.=str_replace('_', ' ', $expand_seo['fields'][$result['seo_group']]['fields']['@'.$parameter.'@']).'/';
            }

            $results[$key]['sd']=$filtered_params;
            $results[$key]['status_text'] = $result['schema_status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled');
        }

        $data = array (
            'data' => $results,
            'total' => $total,
            'totalFiltered' => $totalFiltered
        );
        return $data;
    }

    public function install()
    {
        $installQueries = [];
        $installQueries[] = 'CREATE TABLE `' . DB_PREFIX . 'expand_seo` (
          `seo_id` int(11) NOT NULL AUTO_INCREMENT,
          `seo_group` varchar(255) NOT NULL,
          `max_len` int(11) NOT NULL DEFAULT "200",
          `schema_prefix` varchar(255) NOT NULL,
          `schema_suffix` varchar(255) NOT NULL,
          `schema_status` int(2) NOT NULL DEFAULT "1",
          `schema_parts` text DEFAULT NULL,
          `schema_parameters` text DEFAULT NULL,
          `schema_url` text NOT NULL,
          `fields_language` varchar(7) NOT NULL DEFAULT "en",
          `language` varchar(7) NOT NULL DEFAULT "global",
          PRIMARY KEY (`seo_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';

        foreach ($installQueries as $installQuery) {
            $this->db->query($installQuery);
        }

        $insertQuery = $values = [];
        $insertQuery[] = 'INSERT INTO `expand_seo` (`seo_id`, `seo_group`, `max_len`, `schema_prefix`, `schema_suffix`, `schema_status`, `schema_parts`, `schema_parameters`, `schema_url`, `fields_language`, `language`)';
        $insertQuery[] = 'VALUES';
        $expand_seo = array(
            array('seo_id' => '1','seo_group' => 'common/home','max_len' => '200','schema_prefix' => '','schema_suffix' => '','schema_status' => '1','schema_parts' => '[]','schema_parameters' => '[]','schema_url' => '','fields_language' => '','language' => 'global'),
            array('seo_id' => '2','seo_group' => 'product/product','max_len' => '200','schema_prefix' => 'products/','schema_suffix' => '','schema_status' => '1','schema_parts' => '["([\\\\p{L}\\\\p{N}\\\\-\\\\_]+)", "-", "([0-9\\\\_]+)"]','schema_parameters' => '["slug", "product_id"]','schema_url' => '([\\p{L}\\p{N}\\-\\_]+)-([0-9\\_]+)','fields_language' => 'en','language' => 'en'),
            array('seo_id' => '3','seo_group' => 'product/product','max_len' => '200','schema_prefix' => 'products/','schema_suffix' => '','schema_status' => '1','schema_parts' => '["([\\\\p{L}\\\\p{N}\\\\-\\\\_]+)", "-", "([0-9\\\\_]+)"]','schema_parameters' => '["slug", "product_id"]','schema_url' => '([\\p{L}\\p{N}\\-\\_]+)-([0-9\\_]+)','fields_language' => 'ar','language' => 'ar'),
            array('seo_id' => '4','seo_group' => 'product/category','max_len' => '200','schema_prefix' => 'category/','schema_suffix' => '','schema_status' => '1','schema_parts' => '["([\\\\p{L}\\\\p{N}\\\\-\\\\_]+)", "-", "([0-9\\\\_]+)"]','schema_parameters' => '["slug", "path"]','schema_url' => '([\\p{L}\\p{N}\\-\\_]+)-([0-9\\_]+)','fields_language' => 'en','language' => 'en'),
            array('seo_id' => '5','seo_group' => 'product/category','max_len' => '200','schema_prefix' => 'category/','schema_suffix' => '','schema_status' => '1','schema_parts' => '["([\\\\p{L}\\\\p{N}\\\\-\\\\_]+)", "-", "([0-9\\\\_]+)"]','schema_parameters' => '["slug", "path"]','schema_url' => '([\\p{L}\\p{N}\\-\\_]+)-([0-9\\_]+)','fields_language' => 'ar','language' => 'ar'),
            array('seo_id' => '6','seo_group' => 'product/manufacturer/info','max_len' => '200','schema_prefix' => 'brand/','schema_suffix' => '','schema_status' => '1','schema_parts' => '["([\\\\p{L}\\\\p{N}\\\\-\\\\_]+)", "-", "([0-9\\\\_]+)"]','schema_parameters' => '["slug", "manufacturer_id"]','schema_url' => '([\\p{L}\\p{N}\\-\\_]+)-([0-9\\_]+)','fields_language' => 'en','language' => 'global'),
            array('seo_id' => '7','seo_group' => 'product/search','max_len' => '200','schema_prefix' => 'search/','schema_suffix' => '','schema_status' => '1','schema_parts' => '["([\\\\p{L}\\\\p{N}\\\\-\\\\_\\\\_s]+)"]','schema_parameters' => '["search"]','schema_url' => '([\\p{L}\\p{N}\\-\\_\\s]+)','fields_language' => 'en','language' => 'global'),
            array('seo_id' => '8','seo_group' => 'product/search','max_len' => '200','schema_prefix' => 'search/','schema_suffix' => '','schema_status' => '1','schema_parts' => '[]','schema_parameters' => '[]','schema_url' => '','fields_language' => 'en','language' => 'global'),
            array('seo_id' => '9','seo_group' => 'product/compare','max_len' => '200','schema_prefix' => 'compare/','schema_suffix' => '','schema_status' => '1','schema_parts' => '[]','schema_parameters' => '[]','schema_url' => '','fields_language' => '','language' => 'global'),
            array('seo_id' => '10','seo_group' => 'product/special','max_len' => '200','schema_prefix' => 'special/','schema_suffix' => '','schema_status' => '1','schema_parts' => '[]','schema_parameters' => '[]','schema_url' => '','fields_language' => '','language' => 'global'),
            array('seo_id' => '11','seo_group' => 'checkout/cart','max_len' => '200','schema_prefix' => 'cart/','schema_suffix' => '','schema_status' => '1','schema_parts' => '[]','schema_parameters' => '[]','schema_url' => '','fields_language' => '','language' => 'global'),
            array('seo_id' => '12','seo_group' => 'checkout/checkout','max_len' => '200','schema_prefix' => 'checkout/','schema_suffix' => '','schema_status' => '1','schema_parts' => '[]','schema_parameters' => '[]','schema_url' => '','fields_language' => '','language' => 'global'),
            array('seo_id' => '13','seo_group' => 'information/sitemap','max_len' => '200','schema_prefix' => 'sitemap/','schema_suffix' => '','schema_status' => '1','schema_parts' => '[]','schema_parameters' => '[]','schema_url' => '','fields_language' => '','language' => 'global'),
            array('seo_id' => '14','seo_group' => 'information/contact','max_len' => '200','schema_prefix' => 'contact/','schema_suffix' => '','schema_status' => '1','schema_parts' => '[]','schema_parameters' => '[]','schema_url' => '','fields_language' => 'en','language' => 'global'),
            array('seo_id' => '15','seo_group' => 'information/information','max_len' => '200','schema_prefix' => 'info/','schema_suffix' => '','schema_status' => '1','schema_parts' => '["([\\\\p{L}\\\\p{N}\\\\-\\\\_]+)", "-", "([0-9\\\\_]+)"]','schema_parameters' => '["slug", "information_id"]','schema_url' => '([\\p{L}\\p{N}\\-\\_]+)-([0-9\\_]+)','fields_language' => 'en','language' => 'en'),
            array('seo_id' => '16','seo_group' => 'information/information','max_len' => '200','schema_prefix' => 'info/','schema_suffix' => '','schema_status' => '1','schema_parts' => '["([\\\\p{L}\\\\p{N}\\\\-\\\\_]+)", "-", "([0-9\\\\_]+)"]','schema_parameters' => '["slug", "information_id"]','schema_url' => '([\\p{L}\\p{N}\\-\\_]+)-([0-9\\_]+)','fields_language' => 'ar','language' => 'ar'),
            array('seo_id' => '17','seo_group' => 'account/login','max_len' => '200','schema_prefix' => 'login/','schema_suffix' => '','schema_status' => '1','schema_parts' => '[]','schema_parameters' => '[]','schema_url' => '','fields_language' => '','language' => 'global'),
            array('seo_id' => '18','seo_group' => 'account/register','max_len' => '200','schema_prefix' => 'register/','schema_suffix' => '','schema_status' => '1','schema_parts' => '[]','schema_parameters' => '[]','schema_url' => '','fields_language' => '','language' => 'global'),
            array('seo_id' => '19','seo_group' => 'seller/register-seller','max_len' => '200','schema_prefix' => 'register-seller/','schema_suffix' => '','schema_status' => '1','schema_parts' => '[]','schema_parameters' => '[]','schema_url' => '','fields_language' => '','language' => 'global'),
            array('seo_id' => '20','seo_group' => 'seller/catalog-seller/profile','max_len' => '200','schema_prefix' => 'seller-profile/','schema_suffix' => '','schema_status' => '1','schema_parts' => '[]','schema_parameters' => '[]','schema_url' => '','fields_language' => '','language' => 'global'),
            array('seo_id' => '21','seo_group' => 'blog/blog','max_len' => '200','schema_prefix' => 'blog/','schema_suffix' => '','schema_status' => '1','schema_parts' => '[]','schema_parameters' => '[]','schema_url' => '','fields_language' => '','language' => 'global'),
            array('seo_id' => '22','seo_group' => 'checkout/success','max_len' => '200','schema_prefix' => 'checkout-success/','schema_suffix' => '','schema_status' => '1','schema_parts' => '[]','schema_parameters' => '[]','schema_url' => '','fields_language' => '','language' => 'global'),
            array('seo_id' => '23','seo_group' => 'checkout/error','max_len' => '200','schema_prefix' => 'checkout-error/','schema_suffix' => '','schema_status' => '1','schema_parts' => '[]','schema_parameters' => '[]','schema_url' => '','fields_language' => '','language' => 'global'),
            array('seo_id' => '24','seo_group' => 'checkout/pending','max_len' => '200','schema_prefix' => 'checkout-pending/','schema_suffix' => '','schema_status' => '1','schema_parts' => '[]','schema_parameters' => '[]','schema_url' => '','fields_language' => '','language' => 'global'),
            array('seo_id' => '25','seo_group' => 'payment/oneglobal/callback','max_len' => '200','schema_prefix' => 'payment/oneglobal/callback','schema_suffix' => '','schema_status' => '1','schema_parts' => '[]','schema_parameters' => '[]','schema_url' => '','fields_language' => '','language' => 'global'),
        );

        foreach ($expand_seo as $value) {
            $innerValues = [];
            $innerValues[] = '(';
            $fieldsValues = [];
            foreach ($value as $fieldKey => $fieldValue) {
                $fieldsValues[] = '"'.$this->db->escape($fieldValue).'"';
            }
            $innerValues[] = implode(',', $fieldsValues);
            $innerValues[] = ')';
            $values[] = implode('', $innerValues);
        }

        $insertQuery[] = implode(',', $values);
        $insertQuery[] = ';';

        $insertQuery = implode(' ', $insertQuery);

        $this->db->query($insertQuery);


        $this->upgradeDb();

    }

    public function upgradeDb()
    {
        $slugify = new ExpandCart\Foundation\String\Slugify;
        // upgrade categories table

        $data = $this->db->query('SELECT * FROM ' . DB_PREFIX . 'product_description');

        foreach ($data->rows as $row) {
            $this->db->query('
                UPDATE ' . DB_PREFIX . 'product_description SET
                slug="' . $slugify->slug($row['name']) . '"
                WHERE product_id=' . $row['product_id'] . '
                AND language_id=' . $row['language_id'] . '
            ');
        }

        $data = $this->db->query('SELECT * FROM ' . DB_PREFIX . 'category_description');

        foreach ($data->rows as $row) {
            $this->db->query('
                UPDATE ' . DB_PREFIX . 'category_description SET
                slug="' . $slugify->slug($row['name']) . '"
                WHERE category_id=' . $row['category_id'] . '
                AND language_id=' . $row['language_id'] . '
            ');
        }

        $data = $this->db->query('SELECT * FROM ' . DB_PREFIX . 'manufacturer');

        foreach ($data->rows as $row) {
            $this->db->query('
                UPDATE ' . DB_PREFIX . 'manufacturer SET
                slug="' . $slugify->slug($row['name']) . '"
                WHERE manufacturer_id=' . $row['manufacturer_id'] . '
            ');
        }

        $data = $this->db->query('SELECT * FROM ' . DB_PREFIX . 'information_description');

        foreach ($data->rows as $row) {
            $this->db->query('
                UPDATE ' . DB_PREFIX . 'information_description SET
                slug="' . $slugify->slug($row['title']) . '"
                WHERE information_id=' . $row['information_id'] . '
                AND language_id=' . $row['language_id'] . '
            ');
        }
    }

    public function deleteSchema($id)
    {
        $this->db->query('DELETE FROM `' . DB_PREFIX . 'expand_seo` WHERE seo_id="'.(int)$id.'"');
    }

    public function uninstall()
    {
        $uninstallQueries = [];
        $uninstallQueries[] = 'DROP TABLE IF EXISTS `' . DB_PREFIX . 'expand_seo`';

        foreach ($uninstallQueries as $uninstallQuery) {
            $this->db->query($uninstallQuery);
        }
    }

    public function trimArray($array, $value, $function)
    {
        $both = function (&$array, $value) use(&$both) {
            if ((is_array($value) && in_array($array[0], $value)) || (is_array($value) === false && $array[0] == $value)) {
                array_shift($array);
            } else if ((is_array($value) && in_array(end($array), $value)) || (is_array($value) === false && end($array) == $value)) {
                array_pop($array);
            } else {
                return $array;
            }

            return $both($array, $value);
        };

        $first = function (&$array, $value) use(&$first) {
            if ((is_array($value) && in_array($array[0], $value)) || (is_array($value) === false && $array[0] == $value)) {
                array_shift($array);
            } else {
                return $array;
            }

            return $first($array, $value);
        };

        $last = function (&$array, $value) use(&$last) {
            if ((is_array($value) && in_array(end($array), $value)) || (is_array($value) === false && end($array) == $value)) {
                array_pop($array);
            } else {
                return $array;
            }

            return $last($array, $value);
        };

        return $$function($array, $value);
    }
}
