<?php
class ModelSectionCategory extends Model {
	public function getFeaturedCategories($category_paths_csv, $thumb_width=0, $thumb_height=0) {
        $this->load->model('catalog/category', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');
        $this->load->model('tool/image', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');

        $categories = array();

        $category_paths = explode(',', $category_paths_csv);
        $limit = count($category_paths);

        ###################get random products from DB if zeros######################
        if(count(array_unique($category_paths)) == 1 && $category_paths[0] == "0") {
            $query = $this->db->query("SELECT category_id FROM category Where status = '1' ORDER BY parent_id, RAND() LIMIT $limit");
            $random_category_ids = array_column($query->rows, 'category_id');

            $generated_category_ids = array();
            while(count($generated_category_ids) <= $limit && count($random_category_ids) > 0){
                $generated_category_ids = array_merge($generated_category_ids, $random_category_ids);
            }
            $category_paths = array_slice($generated_category_ids, 0, $limit);
        }

        #############################################################################

        foreach ($category_paths as $category_path) {
            $parts = explode('_', $category_path);

            $category_id = (int)array_pop($parts);

            $category_info = $this->model_catalog_category->getCategory($category_id);

            if ($category_info) {
                $description = substr(strip_tags(htmlspecialchars_decode($category_info['description'])), 0, 100);

                if ($category_info['image']) {
                    $image = $this->model_tool_image->resize($category_info['image'], $thumb_width, $thumb_height);
                } else {
                    $image = false;
                }

                if ($category_info['icon']) {
                    $icon = $this->model_tool_image->resize($category_info['icon'], $thumb_width, $thumb_height);
                } else {
                    $icon = $this->model_tool_image->resize('no_image.jpg', $thumb_width, $thumb_height);
                }

                $categories[] = array(
                    'category_id'       => $category_info['category_id'],
                    'thumb'   	        => $image, // to preview as category banner and as category image in home page -- web browser case
                    'thumb_icon'        => $icon, // to preview as category image in home page mobile browser case
                    'name'    	        => $category_info['name'],
                    'short_description' => $description,
                    'href'    	        => $this->url->link('product/category', 'path=' . $category_path)
                );
            }
        }

        return $categories;
	}

    public function getCategoryAndChilds($category_paths_csv, $thumb_width, $thumb_height) {
        $this->load->model('catalog/category', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');
        $this->load->model('tool/image', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');

        $categories = array();

        $category_paths = explode(',', $category_paths_csv);
        $limit = count($category_paths);
        ###################get random products from DB if zeros######################
        if(count(array_unique($category_paths)) == 1 && $category_paths[0] == "0") {
            $query = $this->db->query("SELECT category_id FROM category ORDER BY parent_id, RAND() LIMIT $limit");
            $random_category_ids = array_column($query->rows, 'category_id');

            $generated_category_ids = array();
            while(count($generated_category_ids) <= $limit && count($random_category_ids) > 0){
                $generated_category_ids = array_merge($generated_category_ids, $random_category_ids);
            }
            $category_paths = array_slice($generated_category_ids, 0, $limit);
        }
        #############################################################################

        foreach ($category_paths as $category_path) {
            $parts = explode('_', $category_path);

            $category_id = (int)array_pop($parts);

            $category_info = $this->model_catalog_category->getCategory($category_id);

            if ($category_info) {
                $description = substr(strip_tags(htmlspecialchars_decode($category_info['description'])), 0, 100);

                if ($category_info['image']) {
                    $image = $this->model_tool_image->resize($category_info['image'], $thumb_width, $thumb_height);
                } else {
                    $image = false;
                }

                $children_data = array();

                $children = $this->model_catalog_category->getCategories($category_info['category_id']);

                foreach ($children as $child) {
                    $children_data[] = array(
                        'name'  => $child['name'],
                        'category_id' => $child['category_id'],
                        'href'  => $this->url->link('product/category', 'path=' . $category_info['category_id'] . '_' . $child['category_id'])
                    );
                }

                $categories[] = array(
                    'category_id'       => $category_info['category_id'],
                    'thumb'   	        => $image,
                    'name'    	        => $category_info['name'],
                    'short_description' => $description,
                    'href'    	        => $this->url->link('product/category', 'path=' . $category_path),
                    'sub_categories'    => $children_data
                );
            }
        }

        return $categories;
    }

    public function getCategory($category_id) {
        $this->load->model('catalog/category', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');

        $category = $this->model_catalog_category->getCategory($category_id);
        $category['href'] = $this->url->link('product/category', 'path=' . $category_id);
        return $category;
	}

	//Get all childs of category
    public function getCategoryAndChildsBylevel($category_paths_csv, $thumb_width, $thumb_height, $level) {
        $this->load->model('catalog/category', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');
        $this->load->model('tool/image', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');

        $categories = array();

        $category_paths = explode(',', $category_paths_csv);
        $limit = count($category_paths);
        ###################get random products from DB if zeros######################
        if(count(array_unique($category_paths)) == 1 && $category_paths[0] == "0") {
            $query = $this->db->query("SELECT category_id FROM category ORDER BY parent_id, RAND() LIMIT $limit");
            $random_category_ids = array_column($query->rows, 'category_id');

            $generated_category_ids = array();
            while(count($generated_category_ids) <= $limit && count($random_category_ids) > 0){
                $generated_category_ids = array_merge($generated_category_ids, $random_category_ids);
            }
            $category_paths = array_slice($generated_category_ids, 0, $limit);
        }
        #############################################################################

        foreach ($category_paths as $category_path) {
            $parts = explode('_', $category_path);

            $category_id = (int)array_pop($parts);

            $category_info = $this->model_catalog_category->getCategory($category_id);

            if ($category_info) {
                $description = substr(strip_tags(htmlspecialchars_decode($category_info['description'])), 0, 100);

                if ($category_info['image'] && file_exists(DIR_IMAGE . $category_info['image'])) {
                    $image = $this->model_tool_image->resize($category_info['image'], $thumb_width, $thumb_height);
                } else {
                    $image = false;
                }


                $categories[] = array(
                    'category_id'       => $category_info['category_id'],
                    'thumb'   	        => $image,
                    'name'    	        => $category_info['name'],
                    'short_description' => $description,
                    'href'    	        => $this->url->link('product/category', 'path=' . $category_path),
                    'sub_categories'    => $this->categoryChild($category_info['category_id'], $thumb_width, $thumb_height, $level)
                );
            }
        }

        return $categories;
    }

    //Recursively get sub categories
    public function categoryChild($id, $thumb_width, $thumb_height, $level)
    {
        $this->load->model('tool/image', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');

        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE c.parent_id = '" . (int)$id . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND c.status = '1'");

        $children = array();

        if($query->num_rows > 0 && $level > 0)
        {
            $level -= 1;
            #It has children, let's get them.
            foreach($query->rows as $row)
            {
                if ($row['image'] && file_exists(DIR_IMAGE . $row['image'])) {
                    $image = $this->model_tool_image->resize($row['image'], $thumb_width, $thumb_height);
                } else {
                    $image = false;
                }
                $description = substr(strip_tags(htmlspecialchars_decode($row['description'])), 0, 100);
                #Add the child to the list of children, and get its subchildren
                $children[$row['category_id']] = array(
                    'category_id'       => $row['category_id'],
                    'thumb'   	        => $image,
                    'name'    	        => $row['name'],
                    'short_description' => $description,
                    'href'    	        => $this->url->link('product/category', 'path=' . $row['category_id'])
                );

                $arr = $this->categoryChild($row['category_id'], $thumb_width, $thumb_height, $level);
                if(count($arr) > 0 )
                {
                    $children[$row['category_id']]['sub_categories'] = $this->categoryChild($row['category_id'], $thumb_width, $thumb_height, $level);
                }
            }
        }
        return $children;
    }

    /****
     * Get all childs of category
     * @param $product_ids Array
     * @param $thumb_width int
     * @param $thumb_height int
     * @return array
     */
    public function getCategoryByProductIds($product_ids, $thumb_width, $thumb_height) {
        $this->load->model('section/category', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');
        $this->load->model('tool/image', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');

        $categories = array();
        $product_ids = implode(',', $product_ids);

        $query = $this->db->query("SELECT ptc.category_id FROM product_to_category ptc LEFT JOIN category c on (ptc.category_id = c.category_id) where ptc.product_id IN ($product_ids) AND c.parent_id=0 ORDER BY c.parent_id");
        $category_ids = array_column($query->rows, 'category_id');
        $category_ids = array_unique($category_ids);

        foreach ($category_ids as $category_id) {

            $category_info = $this->model_catalog_category->getCategory($category_id);

            if ($category_info) {
                $description = substr(strip_tags(htmlspecialchars_decode($category_info['description'])), 0, 100);

                if ($category_info['image']) {
                    $image = $this->model_tool_image->resize($category_info['image'], $thumb_width, $thumb_height);
                } else {
                    $image = false;
                }

                $children_data = array();

                $children = $this->model_catalog_category->getCategories($category_info['category_id']);

                foreach ($children as $child) {
                    $children_data[] = array(
                        'name'  => $child['name'],
                        'category_id' => $child['category_id'],
                        'href'  => $this->url->link('product/category', 'path=' . $category_info['category_id'] . '_' . $child['category_id'])
                    );
                }

                $categories[] = array(
                    'category_id'       => $category_info['category_id'],
                    'thumb'   	        => $image,
                    'name'    	        => $category_info['name'],
                    'short_description' => $description,
                    'href'    	        => $this->url->link('product/category', 'path=' . $category_id),
                    'sub_categories'    => $children_data
                );
            }
        }

        return $categories;
    }
}
?>