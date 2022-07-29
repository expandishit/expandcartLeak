<?php
/**
 * Created by PhpStorm.
 * User: baoan
 * Date: 12/24/14
 * Time: 4:58 PM
 */
class ModelSaleRewardPointsCatalog extends Model
{
    public function validate($field, $op, $value)
    {
        switch($field)
        {
            case 'model':
                $this->load->model('catalog/product');
                $products = $this->cart->getProducts();
                $flag_compare = false;
                $available_model = array();
                foreach($products as $product){
                    $data = $this->model_catalog_product->getProduct($product['product_id']);
                    $flag_compare = $this->doComparison($data['model'], $op, $value);
                    if($flag_compare) return $flag_compare;
                }
                return $flag_compare;
                break;
            case 'category_id':
                $this->load->model('catalog/product');
                $products = $this->cart->getProducts();
                $flag_compare = false;
                foreach($products as $product){
                    $categories = $this->model_catalog_product->getCategories($product['product_id']);
                    foreach($categories as $category){
                        $flag_compare = $this->doComparison($category['category_id'], $op, $value);
                        if($flag_compare) return $flag_compare;
                    }
                }
                return $flag_compare;
                break;
            case 'sku':
                $this->load->model('catalog/product');
                $products = $this->cart->getProducts();
                $flag_compare = false;
                $available_sku = array();
                foreach($products as $product){
                    $data = $this->model_catalog_product->getProduct($product['product_id']);
                    $flag_compare = $this->doComparison($data['sku'], $op, $value);
                    if($flag_compare) return $flag_compare;
                }
                return $flag_compare;
                break;
            case ( preg_match("~attribute_id\|([0-9]+)~", $field)? true : false ):
                $this->load->model('catalog/product');
                $products = $this->cart->getProducts();
                $flag_compare = false;
                $attribute_id =  explode("|", $field);
                $attribute_id =  end($attribute_id);

                foreach($products as $product){
                    $attributes = $this->model_catalog_product->getProductAttributes($product['product_id']);
                    foreach($attributes as $attribute){
                        if($attribute['attribute_group_id'] == $attribute_id){
                            foreach($attribute['attribute'] as $attr){
                                $flag_compare = $this->doComparison($attr['attribute_id'], $op, $value);
                                if($flag_compare) return $flag_compare;
                            }
                        }
                    }
                }
                break;
            case ( preg_match("~option\|([0-9]+)~", $field)? true : false ):
                $this->load->model('catalog/product');
                $products = $this->cart->getProducts();
                $flag_compare = false;
                $option_id =  explode("|", $field);
                $option_id =  end($option_id);

                foreach($products as $product){
                    $options = $this->model_catalog_product->getProductOptions($product['product_id']);
                    foreach($options as $option){
                        if($option['option_id'] == $option_id){
                            foreach($option['option_value'] as $opt){
                                $flag_compare = $this->doComparison($opt['option_value_id'], $op, $value);
                                echo "<pre>";
                                print_r($flag_compare);
                                echo "</pre>";
                                if($flag_compare) return $flag_compare;
                            }
                        }
                    }
                }
                break;
            case 'manufacturer':
                $this->load->model('catalog/product');
                $products = $this->cart->getProducts();
                $flag_compare = false;

                foreach($products as $product){
                    $data = $this->model_catalog_product->getProduct($product['product_id']);
                    $flag_compare = $this->doComparison($data['manufacturer_id'], $op, $value);
                    if($flag_compare) return $flag_compare;
                }
                break;
        }

        return false;
    }

    public function doComparison($a, $operator, $b)
    {
        switch ($operator) {
            case '<':
                return ($a < $b);
                break;
            case '<=':
                return ($a <= $b);
                break;
            case '=':
                return ($a == $b);
                break; // SQL way
            case '==':
            case '{}':
            case '()':
                return ($a == $b);
                break;
            case '!=':
            case '!{}':
            case '!()':
            case '=!':
                return ($a != $b);
                break;
            case '>=':
                return ($a >= $b);
                break;
            case '>':
                return ($a > $b);
                break;
        }
    }
}
