<?php
include_once '../jwt_helper.php';
class ControllerApiSellersProduct extends Controller
{
    public function add()
    {
        try {
            $this->load->language('api/login');
            $this->load->language('multiseller/multiseller');
            $this->load->model('multiseller/seller');
            $this->load->model('module/trips');
            $this->load->model('catalog/product');
            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;
            $this->load->model('account/api');

            if (!isset($this->session->data['api_id'])) 
            {
                $json['error']['warning'] = $this->language->get('error_permission');
            } 
            else {
              if($this->customer->isLogged()) {

               $errors = $this->validate();
                if(count($errors)) {
                    $json['status'] = false;
                    $json['error'] = $errors;
                }
                else
                {
                $productparameters=$this->productParameters();
                $productparameters['languages'] = json_decode(json_encode($productparameters['product_description']), true);
                if($productparameters['product_thumbnail']){
                $productparameters['product_thumbnail']= $this->model_multiseller_seller->uploadImagebase64($productparameters['product_thumbnail'],$this->customer->getId());
                 }
                 $productparameters['product_status']=1;
                 $productparameters['product_approved']=1;
                $product_id =$this->MsLoader->MsProduct->saveProduct($productparameters);
                $images = $productparameters['images'];
                if($product_id &&!empty($images)) 
                {   
                    $uploaded_images=array();    
                    foreach($images as $img)
                    {
                        $uploaded_images[]=$this->model_multiseller_seller->uploadImagebase64($img,$this->customer->getId());           
                    }
                      $imageAdded=$this->model_catalog_product->AddProductImages($product_id,$uploaded_images);
                      if ($imageAdded) { 
                        $this->model_catalog_product->updateproductMainImage($product_id,$uploaded_images[1]);
                      }
                }
               if($this->model_module_trips->isTripsAppInstalled()) 
               {
                    $tripsParameters = json_decode(json_encode($productparameters['trip_data']), true);
                    $tripsParameters['product_id']=$product_id;
                    $this->model_module_trips->addTripProduct($tripsParameters);
                    ///add Trip Questionnaire to every trip while inserting new product/trip
                    $tripQuestionnaires=$this->model_module_trips->getTripsQuestionnaire();
                    foreach ($tripQuestionnaires as $product_option) {
                        if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image' || $product_option['type'] == 'product') {
                            $this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', required = 1, sort_order = '" . (int)$product_option['sort_order'] . "'");
        
                            $product_option_id = $this->db->getLastId();
                            $optionValues=$this->model_multiseller_seller->getOptionValues($product_option['option_id']);
                            foreach ($optionValues as $product_option_value) {
                                $this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET product_option_id = '" . (int)$product_option_id . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', option_value_id = '" . (int)$product_option_value['option_value_id'] . "', quantity = '1', subtract = '0', price = '0', price_prefix = '0', points = '0', points_prefix = '0', weight = '0', weight_prefix = '0', sort_order = '0'");
                            }
                        }
                    }

               }
                if ($product_id != null) { 
                    $json['status'] = true;                 
                    $json['message'] = $this->language->get('ms_text_add_success');
                    $json['product_id'] = $product_id;
                }else{                     
                    $json['status'] = false;                 
                    $json['message'] = 'error happened';
                    $json['product_id'] = '';
                   }
                }
               }else{
                    $json['status'] = false;
                    $json['error'] = "not logged in";}

               $this->response->addHeader('Content-Type: application/json');
               $this->response->addHeader('Access-Control-Allow-Origin: *');
               $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
               $this->response->addHeader('Access-Control-Allow-Credentials: true');
               $this->response->setOutput(json_encode($json));
            }
        } catch (Exception $exception) {

        }
    }
    public function addProductImages()
    {
        try {
            $this->load->language('api/login');
            $this->load->language('multiseller/multiseller');
            $this->load->model('multiseller/seller');
            $this->load->model('catalog/product');
            $params = json_decode(file_get_contents('php://input'));
            $product_id =$params->product_id;
            $images=$params->images;
            $encodedtoken = $params->token;
            $this->load->model('account/api');

            if (!isset($this->session->data['api_id'])) 
            {
                $json['error']['warning'] = $this->language->get('error_permission');
            } 
            else {
              if($this->customer->isLogged()) {
                
               if(empty($product_id)){
                    $json['status'] = false;
                    $json['error'] = 'Product ID is required!';
                }
                elseif (empty($images) || !is_array($images))
                {
                    $json['status'] = false;
                    $json['error'] = 'Images array can\'t be empty';
                }
                else
                {   
                $uploaded_images=array();               
                foreach($images as $img)
                {
                $uploaded_images[]=$this->model_multiseller_seller->uploadImagebase64($img,$this->customer->getId());           
                }
                 $result= $this->model_catalog_product->AddProductImages($product_id,$uploaded_images);
               }   
                if ($result) { 
                    $this->model_catalog_product->updateproductMainImage($product_id,$uploaded_images[1]);
                    $json['status'] = true;                 
                    $json['message'] = $this->language->get('ms_text_add_success');
                }else{                     
                    $json['status'] = false;                 
                    $json['message'] = 'error happened';
                }
                
               }else{
                    $json['status'] = false;
                    $json['error'] = "not logged in";}

               $this->response->addHeader('Content-Type: application/json');
               $this->response->addHeader('Access-Control-Allow-Origin: *');
               $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
               $this->response->addHeader('Access-Control-Allow-Credentials: true');
               $this->response->setOutput(json_encode($json));
            }
        } catch (Exception $exception) {

        }
    }
   
    public function productParameters()
    {
        $params = json_decode(file_get_contents('php://input'));
        $product_data['product_description']=$params->product_description;
        $product_data['trip_data']=$params->trip_data;
        $product_data['product_category']=$params->category;
        $product_data['product_upc']=$params->upc;
        $product_data['product_ean']=$params->ean;
        $product_data['product_jan']=$params->jan;
        $product_data['product_isbn']=$params->isbn;
        $product_data['product_mpn']=$params->mpn;
        $product_data['keyword']=$params->keyword;
        $product_data['images']=$params->images;
        $product_data['product_enable_shipping']=$params->shipping;
        $product_data['product_price']=$params->price;
        $product_data['product_cost']=$params->cost_price;
        $product_data['product_thumbnail']=$params->image;
        $product_data['product_tax_class_id']=$params->tax_class_id;
        $product_data['product_quantity']=$params->quantity;
        $product_data['product_minimum']=$params->minimum;
        $product_data['product_subtract']=$params->subtract;
        $product_data['product_sort_order']=$params->sort_order;
        $product_data['product_stock_status_id']=$params->stock_status_id;
        if($params->status)$product_data['enabled']=$params->status;
        else $product_data['enabled']=1;
        $product_data['product_weight']=$params->weight;
        $product_data['product_weight_class_id']=$params->weight_class_id;
        $product_data['product_length']=$params->length;
        $product_data['product_width']=$params->width;
        $product_data['product_height']=$params->height;
        $product_data['length_class_id']=$params->length_class_id;
        $product_data['product_manufacturer_id']=$params->manufacturer_id;
        $product_data['product_status']=1;
        $product_data['product_approved']=1;
        return $product_data;

    }
    public function validate(){

        $productparameters=$this->productParameters();
        $errors = array();
        $productDesc=json_decode(json_encode($productparameters['product_description']), true);
        if(empty($productDesc["1"]['product_name']) && empty($productDesc["2"]['product_name']) ) {
            $errors['name']= $this->language->get('error_product_name');
        } 
        if(empty($productDesc["1"]['product_description']) && empty($productDesc["2"]['product_description']) ) {
            $errors['description']= $this->language->get('error_product_description');
        }  
        if(empty($productparameters['product_price'])) {
            $errors['price']= $this->language->get('error_product_price');
        } 
        if($this->model_module_trips->isTripsAppInstalled()) 
        {
          $tripsData=json_decode(json_encode($productparameters['trip_data']), true); 

           if(empty($tripsData['area_id'])) {
            $errors['area_id']= $this->language->get('error_area_id');
            } 
          /*  if(empty($tripsData['pickup_point'])||empty($tripsData['pickup_point'])) {
                $errors['pickup_point']= $this->language->get('error_pickup_point');
            }
            if(empty($tripsData['destination_point'])||empty($tripsData['destination_point'])) {
                $errors['destination_point']=$this->language->get('error_destination_point');
            } */
            if(empty($tripsData['min_no_seats'])) {
                $errors['min_no_seats']= $this->language->get('error_min_no_seats');
            }
            if(empty($tripsData['max_no_seats'])) {
                $errors['max_no_seats']= $this->language->get('error_max_no_seats');
            } 
            if(empty($tripsData['from_date'])) {
                $errors['from_date']= $this->language->get('error_from_date');
            }  
            if(empty($tripsData['to_date'])) {
                $errors['to_date']= $this->language->get('error_to_date');
            } 
            if(empty($tripsData['time'])) {
                $errors['time']= $this->language->get('error_time');
            } 
            if(empty($productparameters['images'])) {
                $errors['images']= $this->language->get('error_images');
            } 
        } 
        return $errors;

    }
    

  
}