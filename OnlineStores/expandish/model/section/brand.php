<?php
class ModelSectionBrand extends Model {
	public function getBrands($brand_ids_csv, $thumb_width=0, $thumb_height=0) {
        $this->load->model('catalog/manufacturer', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');
        $this->load->model('tool/image', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');

        $brands = array();

        $brand_ids = explode(',', $brand_ids_csv);
        $limit = count($brand_ids);
        ###################get random products from DB if zeros######################
        if(count(array_unique($brand_ids)) == 1 && $brand_ids[0] == "0") {
            $query = $this->db->query("SELECT manufacturer_id FROM manufacturer ORDER BY RAND() LIMIT $limit");
            $random_brand_ids = array_column($query->rows, 'manufacturer_id');

            $generated_brand_ids = array();
            while(count($generated_brand_ids) <= $limit && count($random_brand_ids) > 0){
                $generated_brand_ids = array_merge($generated_brand_ids, $random_brand_ids);
            }
            $brand_ids = array_slice($generated_brand_ids, 0, $limit);
        }
        #############################################################################
        $returned_brands = $this->model_catalog_manufacturer->getManufacturerssByIds($brand_ids);
        foreach ($returned_brands as $brand_info) {

            if ($brand_info) {

                if ($brand_info['image']) {
                    $image = $this->model_tool_image->resize($brand_info['image'], $thumb_width, $thumb_height);
                } else {
                    $image = $this->model_tool_image->resize('no_image.jpg', $thumb_width, $thumb_height);
                }

                $brands[] = array(
                    'brand_id'          => $brand_info['brand_id'],
                    'thumb'   	        => $image,
                    'name'    	        => $brand_info['name'],
                    'href'    	        => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $brand_info['brand_id']),
                    'slug'              => $brand_info['slug']
                );
            }
        }

        return $brands;
	}

}
?>