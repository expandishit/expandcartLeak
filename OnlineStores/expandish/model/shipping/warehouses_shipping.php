<?php 
class ModelShippingWarehousesShipping extends Model {    
  	public function getQuote($address) {
  		//warehouses App Check
		$warehouse_setting = $this->config->get('warehouses');
        $warehouses_shipping_setting = $this->config->get('warehouses_shipping');

        $quote_data = array();

		$this->data['warehouses'] = false;

		unset($this->session->data['warehouses_products']);

		if(!$warehouse_setting || $warehouse_setting['status'] != 1 || !$warehouses_shipping_setting || $warehouses_shipping_setting['status'] != 1 ){
			return;
		}

		$ship_title = '';

        //Run warehouses classification
        $warehousesCalssification = $this->warehousesCalssification($address, $warehouses_shipping_setting);
        ///////////////////////////////

        //// Warehouses shipping view name type
        $name_type = 'single';
        $warehouses_shipping = $this->config->get('warehouses_shipping');
        if($warehouses_shipping['name_type']){
            $name_type = $warehouses_shipping['name_type'];
        }
        ///////////////////////////

        if($name_type == 'single'){
			if($warehouses_shipping['sigle_title'][$this->session->data['language']]){
				$ship_title = $warehouses_shipping['sigle_title'][$this->session->data['language']];
			}else{
				$ship_title = $this->language->get('text_title');
			}
		}else{
            $wrs_name = $warehousesCalssification['wrs_name'];
            $wrs_costs = $warehousesCalssification['wrs_costs'];

			foreach ($wrs_costs as $key => $title) {
				if($name_type == 'combine'){
					$ship_title .= $wrs_name[$key] . ' + ';
				}else if($name_type == 'combinewithprice'){
					$ship_title .= $wrs_name[$key] . '(' . $this->currency->format($this->tax->calculate($wrs_costs[$key], $this->config->get('weight_tax_class_id'), $this->config->get('config_tax'))) . ') + ';
				}
			}
			$ship_title = substr($ship_title, 0, -2);
		} 
		//////////////////////////

		//hide shipping method if cost is 0 or setting to hide is enabled
        $total_cost = $warehousesCalssification['total_cost'];
		if($total_cost <= 0 || $warehouses_shipping_setting['hide_as_shipping'] == 1)
			return;
		///////////////////////////////////

		$quote_data['warehouses_shipping'] = array(
						'code'         => 'warehouses_shipping.warehouses_shipping',
						'title'        => $ship_title,
						'cost'         => $total_cost,
						'tax_class_id' => $this->config->get('weight_tax_class_id'),
						'text'         => $this->currency->format($this->tax->calculate($total_cost, $this->config->get('weight_tax_class_id'), $this->config->get('config_tax')))
					);

		$method_data = array();
	
		if ($quote_data) {
      		$method_data = array(
        		'code'       => 'warehouses_shipping',
        		'title'      => $this->language->get('text_title'),
        		'quote'      => $quote_data,
				'sort_order' => 1,
        		'error'      => false
      		);
		}
		
		return $method_data;
  	}

  	//Warehouses classification
  	public function warehousesCalssification($address, $warehouses_shipping_setting){
        $this->language->load_json('shipping/warehouses_shipping');


        $missing_products = array();

        //Get Address Geo Zone Id
        $user_geo_zone_id = 0;
        $user_zone = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");
        if($user_zone->num_rows == 1 && $user_zone->row['geo_zone_id']){
            $user_geo_zone_id = $user_zone->row['geo_zone_id'];
        }
        //////////////////////////

        ////Get default warehouse rates once
        /// Disable force default warehouse
        /*$wr_default = $this->db->query("SELECT rates, duration FROM " . DB_PREFIX . "warehouses WHERE id = 1");
        $wr_default_rates = json_decode($wr_default->row['rates'], true);
        $wr_default_duration = json_decode($wr_default->row['duration'], true);*/
        ////////////////////////////////////

        $total_cost = 0;

        //////// Cart Products Loop
        $pr_group = [];
        $cart_products = $this->cart->getProducts();

        foreach ($cart_products as $product) {
            $prd_weight = $product['weight'] ? $product['weight'] : 1;

            //// Get product warehouse rates
            $wr_query = $this->wrSelection($product['product_id']);

            $wr_name   = 'other';
            $wr_id     = -1;
            $wr_rate   = '';
            $wr_duration = '';
            $cost      = 0;
            $wrs_costs = [];

            //Product warehouses rates loop
            if($wr_query->num_rows > 0){

                //// Get lowest reate if product exsits in more than one warehouse
                foreach ($wr_query->rows as $wr) {
                    $all_rates   = json_decode($wr['rates'], true);
                    $all_duration = json_decode($wr['duration'], true);

                    $exact_rate     = $all_rates[$user_geo_zone_id];
                    $exact_duration = $all_duration[$user_geo_zone_id];

                    $rates = explode(',', $exact_rate);

                    foreach ($rates as $rate) {
                        $data = explode(':', $rate);

                        if ((int)$data[0] >= (int)$prd_weight) {

                            if (isset($data[1]) && ($cost == 0 || $cost > $data[1])) {
                                $cost = $data[1];

                                $wr_name   = $wr['name'];
                                $wr_id     = $wr['id'];
                                $wr_rate   = $exact_rate;
                                $wr_duration = $exact_duration;

                                break;
                            }
                        }
                    }
                }

                //// End Get lowest reate if product exsits in more than one warehouse
            }else{
                $missing_products[] = $product['name'];
                //continue;
                //// Get rate of default warehouse
                /*
                 * Disable force default warehouse
                 *
                $all_rates = $wr_default_rates;
                $all_duration = $wr_default_duration;

                $exact_rate = $all_rates[$user_geo_zone_id];
                $wr_rate   = $exact_rate;

                $exact_duration = $all_duration[$user_geo_zone_id];
                $wr_duration = $exact_duration;

                $rates = explode(',', $exact_rate);

                foreach ($rates as $rate) {
                    $data = explode(':', $rate);
                    if ($data[0] >= $prd_weight) {
                        if (isset($data[1])) {
                            $cost = $data[1];
                            break;
                        }
                    }
                }*/
                //// End Get reate of default warehouse
            }
            ////// End Use Default rates in case not prpduct warehouse found

            //// End Get product warehouse rates

            /**************$total_cost += ($cost * $product['quantity'] );*/

            /////// Group warehouses cost
            /**************$wrs_costs[$wr_id] += ($cost * $product['quantity'] );*/
            //////////////////////////

            //Attache product warehouse to product object
            $product['warehouse'] = $wr_id;

            $pr_group[] = $product;

            $wrs_name[$wr_id] = $wr_name;

            //Group products based on warehouse to calculate price
            $wrs_prds[$wr_id]['products'][] = $product;
            $wrs_prds[$wr_id]['rate'] =  $wr_rate;
            $wrs_duration[$wr_id] = $wr_duration;
            ////////////////////////////////////////////
        }
        //////// End Cart Products Loop

        //// calculate price

        //costs list to be used in cost calculation type (combined or max or max plus value)
        $costs_list = [];

        foreach ($wrs_prds as $key => $wr_prd) {
            // SUM products weight in same warehouse
            $tot_weight = 0;
            foreach ($wr_prd['products'] as $prd) {
                /**
                 * This code has been commented and modified as the cart products already
                 * come with weight calculated based on the quantity so we don't need the
                 * multiplication process again which causes the rates to exceed the weight
                 * range defined in the warehouses application.
                 */
                // $tot_weight += ( $prd['weight'] * $product['quantity'] );
                $tot_weight += $prd['weight'];
            }

            // get rate of total products weight
            $wr_rate = explode(',', $wr_prd['rate']);
            foreach ($wr_rate as $rate) {
                $data = explode(':', $rate);
                if ($data[0] >= $tot_weight) {
                    if (isset($data[1])) {
                        $cost = $data[1];
                        break;
                    }
                }
            }

            $costs_list[] = $cost;
            $total_cost += $cost;
            $wrs_costs[$key] += $cost;
        }
        ///////////////////

        //Check cost calculation type sum/max_plus_fixed/min_plus_fixed default sum
        if($warehouses_shipping_setting['cost_calculation'] && $warehouses_shipping_setting['cost_calculation'] != 'sum' && count($costs_list)){
            $fixed_value = $warehouses_shipping_setting['cost_fixed_value'] ?? 0;

            if($warehouses_shipping_setting['cost_calculation'] == 'max_plus_fixed')
                $total_cost = max($costs_list) + ( $fixed_value * (count($wrs_prds) -1)); //-1 to avoid adding fixed value to warhouse of max value
            else if($warehouses_shipping_setting['cost_calculation'] == 'min_plus_fixed')
                $total_cost = min($costs_list) + ( $fixed_value * (count($wrs_prds) -1)); //-1 to avoid adding fixed value to warhouse of min value
        }

        /// Set warehouses_products session to use in controller/module/quickcheckout.php get_cart_view() method
        $finalData = [
            'products'  => $pr_group,
            'missing_products' => $missing_products,
            'wrs_costs' => $wrs_costs,
            'wrs_name'  => $wrs_name,
            'wrs_duration'  => $wrs_duration,
            'total_cost' => $total_cost
        ];

        $this->session->data['warehouses_products'] = $finalData;
        ///////////////////////////////////////////////////////////

        /////// Concat warehouses titles
        /*$wrs_costs = [];
        foreach ($wr_group as $group) {
            $wrs_costs[ $group['warehouse'] ] += $group['cost'];
        }*/

        return $finalData;
    }

    public function wrSelection($pr_id){
        $warehouse_setting = $this->config->get('warehouses');
        $chekcQty = '';
        if($warehouse_setting['check_product_qty'] == 1)
            $chekcQty = 'AND pr.quantity > 0 ';

        return $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_warehouse pr 
			                                       LEFT JOIN " . DB_PREFIX . "warehouses w ON (w.id = pr.warehouse_id) 
			                                       WHERE pr.product_id = '" . (int)$pr_id . "' 
			                                       AND pr.status = 1 
			                                       ".$chekcQty."
			                                       AND w.status = 1");
    }
}
?>