<?php
class ModelModuleProductOptionImagePro extends Model {
  
  public function installed() {
    
    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `type` = 'module' AND `code` = 'product_option_image_pro'");
    
    // не работает в mijoshop
    //$query = $this->db->query('SHOW TABLES LIKE "' . DB_PREFIX . 'poip_option_image"');
    return $query->num_rows;
  }
  
  public function isAvaStore() {
    
    $headerFileName = DIR_TEMPLATE."/".$this->config->get('config_template')."/template/common/header.tpl"; 
    if (file_exists($headerFileName) && strpos(file_get_contents($headerFileName), 'AVA STORE') !== false ) {
      return true;
    }
    
    return false;
  }
  
  public function addMainProductImageToAdditional($product_id, &$product_images) {
    
    if ($this->installed()) {
      
      $poip_settings = $this->getSettings();
      
      if (isset($poip_settings['img_main_to_additional']) && $poip_settings['img_main_to_additional']) {
        // Если нет главного изображения в списке доп изображений - добавим
        $product_info = $this->model_catalog_product->getProduct($product_id);
        if (isset($product_info['image']) && trim($product_info['image']) != "" ) {
          $have_image = false;
          foreach ($product_images as $product_image) {
            if ($product_image['image'] == $product_info['image']) {
              $have_image = true;
              break;
            }
          }
          if (!$have_image) {
            array_unshift($product_images, array('product_id'=>$product_id, 'image'=>$product_info['image'], 'sort_order'=>0, 'product_image_id'=>"-1"));
          }
        }
      }
    }
    
  }
  
  // настройки модуля
  public function getSettings() {
    
    $poip_settings = $this->config->get('poip_module');
    
    return $poip_settings;
    
  }

  
  public function getSettingsNames($for_product=true) {
    $settings = array();
    $settings[] = "img_change";
    if (!$for_product) {
      $settings[] = "img_hover";
    }
    $settings[] = "img_use";
    $settings[] = "img_limit";
    if (!$for_product) {
      $settings[] = "img_gal";
    }
    $settings[] = "img_option";
    $settings[] = "img_category";
    $settings[] = "img_first";
    $settings[] = "img_cart";
    
    return $settings;
  }
  

  
  
  
  public function getOptionSettings($product_option_id) {
    
    if (!$this->installed()) return array();
    
    $query = $this->db->query(" SELECT PMOS.*, PS.product_option_id
                                FROM ".DB_PREFIX."poip_main_option_settings PMOS, ".DB_PREFIX."product_option PS
                                WHERE PS.product_option_id = ".(int)$product_option_id."
                                  AND PS.option_id = PMOS.option_id
                                ");
    
    if ($query->num_rows) {
      return $query->row;
    }
    
    return array();
    
  }
  
  // настройки по конкретной опции
  public function getProductOptionSettings($product_option_id) {
    
    $option_settings = array();
    if (!$this->installed()) return $option_settings;
    
    $poip_settings = $this->config->get('poip_module');
    $poip_option_settings = $this->getOptionSettings($product_option_id);
    //print_r($poip_option_settings);
    
    $query = $this->db->query("SELECT * FROM ".DB_PREFIX."poip_option_settings WHERE product_option_id = ".(int)$product_option_id." ");
    
    $settings_names = $this->getSettingsNames();
    
    foreach ($settings_names as $setting_name) {
      if ($query->row && isset($query->row[$setting_name]) && $query->row[$setting_name] != 0) {
        $option_settings[$setting_name] = $query->row[$setting_name]-1;
        
      } elseif (isset($poip_option_settings[$setting_name]) && $poip_option_settings[$setting_name] != 0) {
        $option_settings[$setting_name] = $poip_option_settings[$setting_name]-1;
        
      } elseif (isset($poip_settings[$setting_name])) {
        $option_settings[$setting_name] = $poip_settings[$setting_name];
        
      } else {  
        $option_settings[$setting_name] = false;
      }
    }
    //print_r($option_settings);
    
    return $option_settings;
    
  }
  
  // все настройки для товара в целом (по всем опциям)
  public function getProductSettings($product_id) {
    $query = $this->db->query("SELECT product_option_id FROM ".DB_PREFIX."product_option WHERE product_id = ".(int)$product_id."  ");
    $poip_settings = array();
    foreach ($query->rows as $row) {
      $poip_settings[$row['product_option_id']] = $this->getProductOptionSettings($row['product_option_id']);
    }
    return $poip_settings;
  }
  
  
  
  // если будет нужна другая сортировка - она должна быть опциональной (такая сортировка нужна в категориях)
  public function getProductOptionImages($product_id, $return_table=false) {
    
    $images = array();
    
    if (!$this->installed()) return $images;
    
    $language_id = (int)$this->config->get('config_language_id');
    
    $poip_settings = $this->getProductSettings($product_id);
    
    // если надо, включим стандартную картинку значений опции в картинки значения опции товара
    $query = $this->db->query(" SELECT POV.*, OV.image, OD.name option_name, OVD.name value_name
                                FROM ".DB_PREFIX."product_option_value POV
                                    LEFT JOIN ".DB_PREFIX."option_description OD ON (POV.option_id = OD.option_id AND OD.language_id = ".$language_id.")
                                  , ".DB_PREFIX."option_value OV 
                                    LEFT JOIN ".DB_PREFIX."option_value_description OVD ON (OVD.option_value_id = OV.option_value_id AND OVD.language_id = ".$language_id.")
                                  
                                WHERE POV.option_value_id = OV.option_value_id
                                  AND POV.product_id = ".(int)$product_id." ");
    $all_product_option_images = array();
    foreach ($query->rows as $row) {
      if ( isset($poip_settings[$row['product_option_id']]['img_first']) && $poip_settings[$row['product_option_id']]['img_first'] == 2 ) {
        if ($row['image'] != 'no_image.jpg' && trim($row['image']) != '') {
          $all_product_option_images[] = array( 'option_name'=>$row['option_name']
                                               ,'value_name'=>$row['value_name']
                                               ,'product_id'=>$row['product_id']
                                               ,'product_option_id'=>$row['product_option_id']
                                               ,'product_option_value_id'=>$row['product_option_value_id']
                                               ,'image'=>$row['image']
                                               ,'sort_order'=>0
                                               ,'thumb'=>$row['image']
                                               );
        }
      }
    }
    
    
    $query = $this->db->query(" SELECT POIP.*, OV.image thumb, OD.name option_name, OVD.name value_name
                                  FROM ".DB_PREFIX."poip_option_image POIP
                                      ,".DB_PREFIX."product_option_value POV
                                      ,".DB_PREFIX."option_value OV LEFT JOIN ".DB_PREFIX."option_value_description OVD ON (OVD.option_value_id = OV.option_value_id AND OVD.language_id = ".$language_id.")
                                      ,`".DB_PREFIX."option` O LEFT JOIN ".DB_PREFIX."option_description OD ON (OD.option_id = O.option_id AND OD.language_id = ".$language_id.")
                                      ,".DB_PREFIX."product_option PO
                                WHERE POIP.product_id = ".(int)$product_id."
                                  AND POIP.product_option_value_id = POV.product_option_value_id
                                  AND POV.option_value_id = OV.option_value_id
                                  AND PO.product_option_id = POIP.product_option_id
                                  AND PO.option_id = O.option_id
                                  AND (NOT POV.subtract OR POV.quantity > 0)
                                ORDER BY O.sort_order ASC, POIP.product_option_id ASC, OV.sort_order ASC, POIP.product_option_value_id, POIP.sort_order ASC
                                ");
    
    $all_product_option_images = array_merge($all_product_option_images, $query->rows);
    
    // если надо установим первую картинку в качестве "иконки"
    $thumbs = array();
    foreach ($all_product_option_images as $row) {
      if (!isset($thumbs[$row['product_option_value_id']])) {
        if ( isset($poip_settings[$row['product_option_id']]['img_first']) && $poip_settings[$row['product_option_id']]['img_first'] ) {
          $thumbs[$row['product_option_value_id']] = $row['image'];
        } else {
          $thumbs[$row['product_option_value_id']] = $row['thumb'];
        }
      }
    }
    
    if ($return_table) return $all_product_option_images;
    
    foreach ($all_product_option_images as $row) {
      if (!isset($images[$row['product_option_id']])) {
        $images[$row['product_option_id']] = array();
      }
      if (!isset($images[$row['product_option_id']][$row['product_option_value_id']])) {
        $images[$row['product_option_id']][$row['product_option_value_id']] = array();
      }
      $images[$row['product_option_id']][$row['product_option_value_id']][] = array(  'image'=>$row['image']
                                                                                    , 'thumb'=>( (isset($thumbs[$row['product_option_value_id']]) && $thumbs[$row['product_option_value_id']]) ? $thumbs[$row['product_option_value_id']] : 'no_image.jpg')
                                                                                    , 'srt'=>$row['sort_order']
                                                                                    , 'product_option_id'=>$row['product_option_id']
                                                                                    , 'option_name'=> (isset($row['option_name']) && $row['option_name']) ? $row['option_name'] : ''
                                                                                    , 'value_name'=> (isset($row['value_name']) && $row['value_name']) ? $row['value_name'] : ''
                                                                                    );
    }
    
    return $images;
    
  }
  
  public function getProductOptionImagesByValues($product_id) {
    
    $images = array();
    
    if (!$this->installed()) return $images;
    
    $product_images = $this->getProductOptionImages($product_id);
    foreach ($product_images as $product_option_id => $po_images) {
      
      if ($po_images && is_array($po_images)) {
        foreach ($po_images as $product_option_value_id => $pov_images) {
          $images[$product_option_value_id] = $pov_images;
        }
      }
      
    }
    
    return $images;
    
  }
  
  
  public function addOptionImagesToProductImages($product_images_old, $product_id, &$product_images) {
    
    $product_images = array();
    $added_images = array();
    
    $options_images = $this->getProductOptionImages($product_id, true);
    
    
    foreach ($options_images as $row) {
      
      if (!in_array($row['image'], $added_images)) {
        $product_images[] = array('product_id'=>$product_id, 'image'=>$row['image'], 'sort_order'=>$row['sort_order']);
        $added_images[] = $row['image'];
      }
      foreach ($product_images as &$image) {
        if ($image['image'] == $row['image']) {
          if (!isset($image['product_option_id'])) $image['product_option_id'] = array();
          if (!isset($image['product_option_value_id'])) $image['product_option_value_id'] = array();
          if (!in_array($row['product_option_id'], $image['product_option_id'])) {
            $image['product_option_id'][] = $row['product_option_id'];
          }
          $image['product_option_value_id'][] = $row['product_option_value_id'];
          
          if (isset($row['option_name']) && $row['option_name'] && isset($row['value_name']) && $row['value_name']) {
            if (!isset($image['title'])) {
              $image['title'] = '';
            }
            $image['title'] .= "\n".$row['option_name'].": ".$row['value_name'];
          }
          
        }
      }
      unset($image);
      
    }
    
    
    //foreach ($product_images_old as $product_image) {
    foreach (array_reverse($product_images_old) as $product_image) {
      if (!in_array($product_image['image'], $added_images)) {
        array_unshift ($product_images, $product_image);
      }
    }
    
    $poip_product_settings = $this->getProductSettings($product_id);
    
    $results = array();
    foreach ($product_images as &$result) {
      
      if (isset($result['product_option_id']) && is_array($result['product_option_id'])) {
        $show_image = false;
        foreach ($result['product_option_id'] as $product_option_id) {
          if (isset($poip_product_settings[$product_option_id]) && $poip_product_settings[$product_option_id]['img_use']) {
            $show_image = true;
            break;
          }
        }
      } else {
        $show_image = true;
      }
      
      $result['popup'] = $this->model_tool_image->resize($result['image'], $this->config->get('config_image_popup_width'), $this->config->get('config_image_popup_height'));
      $result['thumb'] = $this->model_tool_image->resize($result['image'], $this->config->get('config_image_additional_width'), $this->config->get('config_image_additional_height'));
      $result['main'] = $this->model_tool_image->resize($result['image'], $this->config->get('config_image_thumb_width'), $this->config->get('config_image_thumb_height'));
      
      if ($show_image) {  
        $results[] = $result;
      }
    }
    unset($result);
    
    return $results;
      
  }
  
  public function getProductOptionsIdsWithImages($results) {
    
    $ids = array();
    
    foreach ($results as $result) {
      if (isset($result['product_option_id']) && is_array($result['product_option_id'])) {
        foreach ($result['product_option_id'] as $product_option_id) {
          if (!in_array($product_option_id, $ids)) {
            $ids[] = $product_option_id;
          }
        }
      }
    }
    
    return $ids;
  }
  
  
  public function getCategoryImages($product_id, $module_setting) {
    
    if (!$this->installed()) return false;
    
    $this->load->model('tool/image');
    
    if ($module_setting && is_array($module_setting) && isset($module_setting['image_width']) && isset($module_setting['image_height'])  ) {
      $image_product_width = $module_setting['image_width'];
      $image_product_height = $module_setting['image_height'];
    } elseif ($module_setting == "related_products") {
      $image_product_width = $this->config->get('config_image_related_width');
      $image_product_height = $this->config->get('config_image_related_height');
    } else {
      $image_product_width = $this->config->get('config_image_product_width');
      $image_product_height = $this->config->get('config_image_product_height');
    }
    
    // ориентир размер изображения 120 (с отступами будет 128), размер иконки 24 (с отступами будет 32)
    // (120)/4-6=24
    $icon_width = round(($image_product_width)/4-6);
    $icon_height = round(($image_product_height)/4-6);
    
    // pav fashion theme compatibility
    if ( ($this->config->get('config_template') == "pav_fashion") && (!$module_setting || $module_setting=="related_products") ) {
      $icon_width = 43;
      $icon_height = 43;
    }
    
    if ( ($this->config->get('config_template') == "lexus_royal") ) {
      $icon_width = 40;
      $icon_height = 40;
    }

    $images = $this->getProductOptionImages($product_id);
    
    $settings = $this->getProductSettings($product_id);
    
    $category_images = array();
    foreach ($images as $product_option_id => $image_po) {
      if (isset($settings[$product_option_id]) && $settings[$product_option_id]['img_category']) {
        
        foreach ($image_po as $product_option_value_id => $image_pov) {
          if (count($image_pov) > 0) {
            if (!isset($category_images[$product_option_id])) {
              $category_images[$product_option_id] = array();
            }
            $image_pov[0];
          
            $category_images[$product_option_id][$product_option_value_id] =array(
                'icon'=>$this->model_tool_image->resize($image_pov[0]['image'], $icon_width, $icon_height)
              , 'thumb'=>$this->model_tool_image->resize($image_pov[0]['image'], $image_product_width, $image_product_height)
              , 'image'=>$image_pov[0]['image']
              );
            
            if (isset($image_pov[0]['option_name']) && $image_pov[0]['option_name'] && isset($image_pov[0]['value_name']) && $image_pov[0]['value_name'] ) {
              $category_images[$product_option_id][$product_option_value_id]['title'] = "".$image_pov[0]['option_name'].": ".$image_pov[0]['value_name'];
            }
            
          }
        }
        
      }
    }
    
    return $category_images;
    
  }
  
   
  
  public function getProductCartImage($product_id, $option_data, $image) {
    
    if (!$this->installed()) {
      return $image;
    }
    
    
    $selected_product_option = array();
    $selected_product_option_value = array();
    foreach ($option_data as $option_value_data) {
      if (!in_array($option_value_data['product_option_id'], $selected_product_option)) $selected_product_option[] = $option_value_data['product_option_id'];
      if (!in_array($option_value_data['product_option_value_id'], $selected_product_option_value)) $selected_product_option_value[] = $option_value_data['product_option_value_id'];
    }
    
    
    $product_images = $this->getProductOptionImages($product_id);
    if ( count($product_images) > 0 ) {
      
      $product_settings = $this->getProductSettings($product_id);
      
      $cart_options = array();
      $filter_options = array();
      foreach ($product_images as $product_option_id => $product_option_images ) {
        
        if ( in_array($product_option_id, $selected_product_option) ) { // значение опции выбрано
          
          $images_count = 0;
          
          foreach ($product_option_images as $product_option_value => $product_option_value_images) {
            $images_count = $images_count + count($product_option_value_images);
          }
          
          if ($images_count > 0) {
            if (isset($product_settings[$product_option_id]['img_cart']) && $product_settings[$product_option_id]['img_cart']) {
              $cart_options[] = $product_option_id;
              if ($product_settings[$product_option_id]['img_limit']) $filter_options[] = $product_option_id;
            }
          }
        }
      }
      
      if (count($filter_options)>0) {
        
        $images = false;
        foreach ($product_images as $product_option_id => $product_option_images) {
          if (in_array($product_option_id, $filter_options)) {
            $current_images = array();
            foreach ($product_images[$product_option_id] as $product_option_value_id => $product_option_value_images) {
              if ( in_array($product_option_value_id, $selected_product_option_value) ) { // это выбранное значение опции
                foreach ($product_option_value_images as $image_info) {
                  if (!in_array($image_info['image'], $current_images)) {
                    $current_images[] = $image_info['image'];
                  }
                }
              } 
            }
            
            if (count($current_images) > 0) {
              if ($images === false) {
                $images = $current_images;
              } else {
                $images = array_values(array_intersect($images, $current_images));
              }
            }
            
          }
        }
        
        if ($images && count($images)>0) {
          $image = $images[0];
        }
        
      } elseif (count($cart_options)>0) { // берем первую картинку первой опции
        $product_option_id = $cart_options[0];
        foreach ($product_images[$product_option_id] as $product_option_value_id => $product_option_value_images) {
          if ( in_array($product_option_value_id, $selected_product_option_value) ) { // это выбранное значение опции
            foreach ($product_option_value_images as $image_info) {
              $image = $image_info['image'];
            }
          }
        }
      }
      
    }
    
    return $image;
    
  }
  
  
  
}