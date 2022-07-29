<?php
class ControllerCheckoutCoupon extends Controller
{
    public function index(){
        $this->load->model('checkout/coupon');
        $this->load->model('catalog/category');
        $this->load->model('catalog/product');
        $this->load->model('catalog/manufacturer');

        $coupons = $this->model_checkout_coupon->getCoupons();
        $coupon_gift = null;

        foreach ($coupons as $key => $coupon){
            $details = json_decode($coupon['details'],true);
            $coupon_gift[$key]['id']=$coupon['coupon_id'];
            $coupon_gift[$key]['buy_option']=$details['buy_option'];
            $coupon_gift[$key]['buy_quantity']=$details['buy_quantity'];
            if ($coupon['type'] != "B"){
                $coupon_gift[$key]['buy_amount']= $this->currency->convert($coupon['discount'], $this->config->get('config_currency'), $this->session->data['currency']);
                $coupon_gift[$key]['buy_item_from']=$details['apply_item_from'];
                $coupon_gift[$key]['minimum_to_apply']= $this->currency->convert($coupon['minimum_to_apply'], $this->config->get('config_currency'), $this->session->data['currency']);
            }else{
                $coupon_gift[$key]['buy_amount']= $this->currency->convert($details['buy_amount'], $this->config->get('config_currency'), $this->session->data['currency']);
                $coupon_gift[$key]['buy_item_from']=$details['buy_item_from'];
                $coupon_gift[$key]['get_quantity']=$details['get_quantity'];
                $coupon_gift[$key]['get_item_from']=$details['get_item_from'];
                $coupon_gift[$key]['get_percentage']=$details['get_percentage'];
                $coupon_gift[$key]['get_discount_value_option']=$details['get_discount_value_option'];
            }
            if ($coupon['shipping']==1 && !($coupon['discount'] > 0)){
                $coupon_gift[$key]['type']="S";
            }else{
                $coupon_gift[$key]['type']=$coupon['type'];
            }
            $coupon_gift[$key]['automatic_apply']=$coupon['automatic_apply'];
            if ($coupon['automatic_apply']==0){
                $coupon_gift[$key]['code']=$coupon['code'];
            }
            // Categories
            if ($details["buy_item_from"]=="category" || $details["apply_item_from"]=="category")
            {
                $category_info=null;
                $buy_category_ids = $this->model_checkout_coupon->getCouponCategories($coupon['coupon_id'],0);
                foreach ($buy_category_ids as $id => $buy_category_id){
                    $category = $this->model_catalog_category->getCategory($buy_category_id);
                    $category_info[$id] = $category;
                    $category_info[$id]['href'] = $this->url->link('product/category', 'path=' . $category['category_id']);
                }
                $coupon_gift[$key]['buy_items']=$category_info;
            }

            if ($details['get_item_from'] == "category"){
                $get_category_ids = $details['coupon_category_get'];
                $category_info=null;
                foreach ($get_category_ids as $id => $get_category_id){
                    $category = $this->model_catalog_category->getCategory($get_category_id);
                    $category_info[$id] = $category;
                    $category_info[$id]['href'] = $this->url->link('product/category', 'path=' . $category['category_id']);
                }
                $coupon_gift[$key]['get_items']=$category_info;
            }

            // Manufacturer
            $this->load->model('catalog/manufacturer');
            if ($details["buy_item_from"]=="manufacturer" || $details["apply_item_from"]=="manufacturer" )
            {
                $manufacturer_info=null;
                $buy_manufacturer_ids = $this->model_checkout_coupon->getCouponManufacturer($coupon['coupon_id'],0);

                foreach ($buy_manufacturer_ids as $buy_manufacturer_id){
                    $manufacturer = $this->model_catalog_manufacturer->getManufacturer($buy_manufacturer_id);
                    $manufacturer['href'] = $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $manufacturer['manufacturer_id']);
                    $manufacturer_info[] = $manufacturer;
                }
                $coupon_gift[$key]['buy_items']=$manufacturer_info;
            }
            if ($details["get_item_from"]=="manufacturer")
            {
                $manufacturer_info=null;
                $get_manufacturer_ids = $details['coupon_manufacturer_get'];
                foreach ($get_manufacturer_ids as $get_manufacturer_id){
                    $manufacturer = $this->model_catalog_manufacturer->getManufacturer($get_manufacturer_id);
                    $manufacturer['href'] = $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $manufacturer['manufacturer_id']);
                    $manufacturer_info[] = $manufacturer;
                }
                $coupon_gift[$key]['get_items']=$manufacturer_info;
            }

            // Product
            if ($details["buy_item_from"]=="product" || $details["apply_item_from"]=="product")
            {
                $product_info=null;
                $buy_product_ids = $this->model_checkout_coupon->getCouponProducts($coupon['coupon_id'],0);

                foreach ($buy_product_ids as $id => $buy_product_id){
                    $product = $this->model_catalog_product->getProduct($buy_product_id);
                    if ($product){
                        $product_info[$id]['name'] = $product['name'];
                        $product_info[$id]['product_id'] = $product['product_id'];
                        $product_info[$id]['href'] = $this->url->link('product/product', 'product_id=' . $product['product_id']);
                    }
                }
                $coupon_gift[$key]['buy_items']=$product_info;
            }

            if ($details["get_item_from"]=="product")
            {
                $product_info=null;
                $get_product_ids = $details['coupon_product_get'];

                foreach ($get_product_ids as $id => $get_product_id){
                    $product = $this->model_catalog_product->getProduct($get_product_id);
                    if ($product){
                        $product_info[$id]['name'] = $product['name'];
                        $product_info[$id]['product_id'] = $product['product_id'];
                        $product_info[$id]['href'] = $this->url->link('product/product', 'product_id=' . $product['product_id']);
                    }
                }
                $coupon_gift[$key]['get_items']=$product_info;
            }

        }

        $result_json['success'] = '1';
        $result_json['coupons'] = $coupon_gift;

        $this->response->setOutput(json_encode($result_json));

    }

}