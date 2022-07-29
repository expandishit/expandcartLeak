<?php  
class ControllerModuleCategory extends Controller {
   protected function index() {
      $this->language->load('module/category');
      
       $this->data['heading_title'] = $this->language->get('heading_title');
	   $this->data['goto_text'] = $this->language->get('goto_text');
      
      if (isset($this->request->get['path'])) {
         $parts = explode('_', (string)$this->request->get['path']);
      } else {
         $parts = array();
      }
      
      if (isset($parts[0])) {
         $this->data['category_id'] = $parts[0];
      } else {
         $this->data['category_id'] = 0;
      }
      
      if (isset($parts[1])) {
         $this->data['child_id'] = $parts[1];
      } else {
         $this->data['child_id'] = 0;
      }
      
      /* add for level 3 subs category's */

       if (isset($parts[2])) {
           $this->data['submenu'] = $parts[2];
       } else {
           $this->data['submenu'] = 0;
       }

      if (isset($parts[2])) {
       $this->data['sisters_id'] = $parts[2];
      } else {
       $this->data['sisters_id'] = 0;
      }      
      
      /* end add */
                     
      $this->load->model('catalog/category');
      $this->load->model('catalog/product');
      
      $this->data['categories'] = array();
               
      $categories = $this->model_catalog_category->getCategories(0);
      
   		
      foreach ($categories as $category) {
         $children_data = array();
         
         $children = $this->model_catalog_category->getCategories($category['category_id']);
         
         foreach ($children as $child) {            
            $sister_data = array();
            $sisters = $this->model_catalog_category->getCategories($child['category_id']);
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
                     'sister_id'   => $sister_data,
                     'name'        => $child['name'],
                     'href'        => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id'])   
                  );   
            }else{                     
               $children_data[] = array(
                  'category_id' => $child['category_id'],
                  'sister_id'    =>'',
                  'name'        => $child['name'],
                  'href'        => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id'])   
               );   
            }
         }

         $this->data['categories'][] = array(
            'category_id' => $category['category_id'],
            'name'        => $category['name'],
            'children'    => $children_data,

             'storecode' => STORECODE,
 //           'sister'    => $sister_data,
			//image  and description
			'image'       => empty($category['image']) ? 'no_image.png' : $category['image'],
			'description' => substr(strip_tags(html_entity_decode($category['description'], ENT_QUOTES, 'UTF-8')), 0, 100) . '',
			//
            'href'        => $this->url->link('product/category', 'path=' . $category['category_id'])
         );
           
    
      }
     /* end */ 



      if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/category.tpl')) {
         $this->template = $this->config->get('config_template') . '/template/module/category.tpl';
      } else {
         $this->template = 'default/template/module/category.tpl';
      }
      
      $this->render();
     }
}
?>