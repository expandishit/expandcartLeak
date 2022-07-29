<?php

class ModelModuleCurtainSeller extends Model 
{
    protected $settings;
    protected $MODULE_NAME = 'curtain_seller';


    public function getSettings()
    {
        $this->load->model('setting/setting');

        return $this->model_setting_setting->getSetting($this->MODULE_NAME);
    }


    public function isEnabled()
    {
        $this->settings = $this->getSettings();

        return ! array_key_exists( 'curtain_seller_status', $this->settings ) || $this->settings['curtain_seller_status'] != '0' || $this->settings['curtain_seller_status'] == '1';
    }

    public function getProductCurtainSellerData(int $product_id)
    {
        $this->load->model('catalog/product');

        $product_data = $this->model_catalog_product->getProduct($product_id);

        return $product_data['price_meter_data']['curtain_seller'];
    }

    /**
     * @param string $selling_type
     * @param int|null $product_id
     * @return mixed
     */
    public function calculateTotal(string $selling_type, int $product_id = null)
    {
        if (! $product_id) {
            $product_id = (int) $this->request->post['product_id'];
        }

        $product_data = $this->getProductCurtainSellerData($product_id);

        $method_name = 'calculateTotalFor' . ucfirst($selling_type);

        return $this->$method_name($product_data);
    }


    protected function calculateRoomWidthForRoll($room_width, array $roll_data)
    {
        return ceil($room_width / $roll_data['width']) * $roll_data['width'];
    }


    protected function calculateTotalForRoll(array $product_data)
    {
        $room_width = $this->request->post['room_width'] / 100;
        $room_height = $this->request->post['room_height'] / 100;
        $roll_data = $product_data['product_roll_widths'][$this->request->post['roll_index']];

        //check if the roll width is available in comparison with total width
        $room_width = (ceil($room_width/($roll_data['width']/100)) * $roll_data['width'])/100;
        
        if (! $roll_data) {
            return false;
        }
        
        $final_width = $this->calculateRoomWidthForRoll($room_width, $roll_data);
        $area = $room_width * $room_height;


        $cost = $area * $roll_data['price'];

        return [
            'cost'          => $cost,
            'cost_display'  => $this->currency->currentValue($cost),
            'width'         => $room_width,
            'height'        => $room_height,
            'area'          => $area,
            'roll_price'    => $roll_data['price'],
            'final_width'   => $final_width,
            'final_height'  => $room_height
        ];
    }

    /**
     * @param array $product_data
     * @return array
     */
    protected function calculateTotalForBlock(array $product_data)
    {
        //$room_width = ceil(($this->request->post['room_width']/100) /0.5) *50;
        $room_width = $this->request->post['room_width'];
        $room_height = $this->request->post['room_height'];

        $final_height_data = $this->getBlockFinalHeight($room_height, $room_width, $this->reArrangeProductBlockWidths($product_data['product_block_widths']));
        //$cost = ceil($room_width / $final_height_data['block_width']) * $final_height_data['price'];
        $cost = $final_height_data['price'];

        return [
            'room_width'                => $this->request->post['room_width'],
            'final_width'               => $room_width,
            'final_height'              => $final_height_data['final_height'],
            'room_final_height_index'   => $final_height_data['block_index'],
            'cost'                      => $cost,
            'cost_display'              => $this->currency->currentValue($cost)
        ];
    }


    protected function reArrangeProductBlockWidths(array $product_block_widths)
    {
        usort($product_block_widths, function($a, $b) {
            $a_height = explode(" * ", $a['width_x_height'])[1];
            $b_height = explode(" * ", $b['width_x_height'])[1];

            return $a_height - $b_height;
        });

        return $product_block_widths;
    }


    protected function getBlockFinalHeight(int $room_height, int $room_width, array $product_data)
    {
        // $last_proudct_data = end($product_data);
        // $exploded_width_x_height = explode(" * ", $last_proudct_data['width_x_height']);
        // $last_block_width = $exploded_width_x_height[0];
        // $last_block_height = $exploded_width_x_height[1];
        // $last_block_price = $last_proudct_data['price'];
        
        // $data = [
        //     'final_height'  => $last_block_height,
        //     'block_width'   => $last_block_width,
        //     'block_index'   => end(array_keys($product_data)),
        //     'price'         => $last_block_price
        // ];

        // reset($product_data);

        $tempW = 10000;
        $tempH = 10000;
        $prices = array();
        $heights = array();
        $widths = array();
        foreach ($product_data as $index => $product_block_width) {
            $exploded_width_x_height = explode(" * ", $product_block_width['width_x_height']);
            $block_height = (int)$exploded_width_x_height[0];
            $block_width = (int)$exploded_width_x_height[1];
            $block_price = $product_block_width['price'];
            $prices[$index] = $block_price;
            $heights[$index] = $block_height;
            $widths[$index] = $block_width;

            if ($block_height == $room_height && $block_width == $room_width && ($block_height < $tempH || $block_width < $tempW)) {
                $tempH = $block_height;
                $tempW = $block_width;

                $data = [
                    'final_height'  => $block_height,
                    'block_width'   => $block_width,
                    'block_index'   => $index,
                    'price'         => $block_price
                ];
                break;
            }
            //Get nearest smallest matching width and height
            if ($block_height >= $room_height && $block_width >= $room_width && ($block_height < $tempH || $block_width < $tempW)) {
                $tempH = $block_height;
                $tempW = $block_width;

                $data = [
                    'final_height'  => $block_height,
                    'block_width'   => $block_width,
                    'block_index'   => $index,
                    'price'         => $block_price
                ];

            }
            // else if($block_height != $room_height && $block_width != $room_width && ($block_height < $tempH || $block_width < $tempW)){
            //     $tempH = $block_height;
            //     $tempW = $block_width;
            //     $height_ratio = $room_height/$block_height;
            //     $width_ratio  = $room_width/$block_width;
            //     $average_ratio = ($height_ratio + $width_ratio )/2;
            //     $data = [
            //         'final_height'  => $block_height,
            //         'block_width'   => $block_width,
            //         'block_index'   => $index,
            //         'price'         => $block_price*$average_ratio
            //     ];

            // }
        }

        if(!$data){
            if(!in_array( $room_width ,$widths ) ){
                $new_widhs = array_unique($widths); // to remove repeated values 
                sort($new_widhs); // to sort values ascending to get first larger value
                foreach($new_widhs as $key => $value){
                    if($value > $room_width){
                        $nearest_width_index = array_keys($widths, $value)[0]; // to get the key of this value in widths array
                        break;
                    }
                }
            }
            
            if(!in_array( $room_height ,$heights )){
                $new_heights = array_unique($heights); // to remove repeated values 
                sort($new_heights); // to sort values ascending to get first larger value
                foreach($new_heights as $k => $v){
                    if($v > $room_height){
                        $nearest_height_index = array_keys($heights, $v)[0]; // to get the key of this value in widths array
                        break;
                    }
                }
            }
            if($nearest_width_index){ // sometimes there will be no index in widths array
                $nearst_widt_price = $prices[$nearest_width_index];
            }
            if($nearest_height_index){ // sometimes there will be no index in heights array
                $nearst_height_price = $prices[$nearest_height_index];
            }

            if($nearst_height_price && $nearst_widt_price){ // if there are both values

                $block_price = $nearst_height_price >  $nearst_widt_price ? $nearst_height_price : $nearst_widt_price;
                $block_width = $nearst_height_price >  $nearst_widt_price ? $widths[$nearest_height_index] : $widths[$nearest_width_index];
                $block_height = $nearst_height_price >  $nearst_widt_price ? $heights[$nearest_height_index] : $heights[$nearest_width_index];
                $index =  $nearst_height_price >  $nearst_widt_price ? $nearest_height_index : $nearest_width_index;

            }else if($nearst_height_price && !$nearst_widt_price){
                // if only we have values in heights 
                $block_price =  $nearst_height_price ;
                $block_width =  $widths[$nearest_height_index] ;
                $block_height =  $heights[$nearest_height_index] ;
                $index =   $nearest_height_index ;

            }else if(!$nearst_height_price && $nearst_widt_price){
                // if only we have values in widths 
                $block_price =  $nearst_widt_price;
                $block_width = $widths[$nearest_width_index];
                $block_height = $heights[$nearest_width_index];
                $index =   $nearest_width_index;
            }
            $data = [
                'final_height'  => $block_height,
                'block_width'   => $block_width,
                'block_index'   => $index,
                'price'         => $block_price
            ];
        }
        return $data;
    }
}
