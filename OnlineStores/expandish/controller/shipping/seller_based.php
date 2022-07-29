<?php
class ControllerShippingSellerBased extends Controller
{
	private $type = 'shipping';
	private $name = 'seller_based';
	
	public function index()
    {	
		$data = array(
			'type'				=> $this->type,
			'name'				=> $this->name,
			'autobackup'		=> true,
			'save_type'			=> 'keepediting',
			'token'				=> null,
			'permission'		=> '1',
			'exit'				=> $this->url->link('extension/' . $this->type, '', 'SSL'),
		);
		
		$this->loadSettings($data);
		
		$this->language->load_json('shipping/seller_based');
		//------------------------------------------------------------------------------
		// Data Arrays
		//------------------------------------------------------------------------------
		//'warehouse'
		$data['rule_options'] = array(
			//'adjustments'				    => array('adjust', 'cumulative', 'max', 'min', 'round', 'setting_override', 'tax_class', 'total_value'),
			//'cart_criteria'				=> array('length', 'width', 'height', 'quantity', 'stock', 'total', 'volume', 'weight'),
			//'datetime_criteria'			=> array('day', 'date', 'time'),
			'location_criteria'			=> array('geo_zone'),
			//'order_criteria'			    => array('currency', 'customer_group', 'language', 'past_orders', 'store'),
			//'product_criteria'			=> array('category', 'manufacturer', 'product', 'product_group'),
			//'seller_criteria'			    => array('seller','allowedShippingMethod'),
		);
		
		$data['setting_override_array'] = array(
			array((version_compare(VERSION, '2.0') < 0 ? 'group' : 'code') => 'config', 'key' => 'config_address', 'value' => $this->config->get('config_address')),
		);
		
		$data['currency_array'] = array($this->config->get('config_currency') => '');
		$this->load->model('localisation/currency');
		foreach ($this->model_localisation_currency->getCurrencies() as $currency) {
			$data['currency_array'][$currency['code']] = $currency['code'];
		}
		
		$data['geo_zone_array'] = array(0 => $this->language->get('text_everywhere_else'));
		$this->load->model('localisation/geo_zone');
		foreach ($this->model_localisation_geo_zone->getGeoZones() as $geo_zone) {
			$data['geo_zone_array'][$geo_zone['geo_zone_id']] = $geo_zone['name'];
		}
		$this->load->model('shipping/seller_based');
		$data['ms_basedOptions']= $this->model_shipping_seller_based->getMultiSellerOptions(0);
		$data['ms_admin_basedOptions']= $this->model_shipping_seller_based->getMultiSellerOptions(1);

		$this->load->model('localisation/language');
		$data['language_array'] = array($this->config->get('config_language') => '');
		$data['language_flags'] = array();
		foreach ($this->model_localisation_language->getLanguages() as $language) {
			$data['language_array'][$language['code']] = $language['name'];
			$data['language_flags'][$language['code']] = $language['image'];
		}
		
		$data['store_array'] = array(0 => $this->config->get('config_name'));
		$store_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "store ORDER BY name");
		foreach ($store_query->rows as $store) {
			$data['store_array'][$store['store_id']] = $store['name'];
		}
		
		
		$data['total_value_array'] = array();
		foreach (array('prediscounted', 'nondiscounted', 'taxed', 'total') as $total_value) {
			$data['total_value_array'][$total_value] = $data['text_' . $total_value . '_subtotal'];
		}
		
		$data['quantity_unit'] = $data['text_items'];
		$data['stock_unit'] = $data['text_items'];
		$left_symbol = $this->currency->getSymbolLeft();
		$data['total_unit'] = ($left_symbol) ? $left_symbol : $this->currency->getSymbolRight();
		$length = $this->db->query("SELECT * FROM " . DB_PREFIX . "length_class_description WHERE length_class_id = " . (int)$this->config->get('config_length_class_id'));
		$data['length_unit'] = $length->row['unit'];
		$data['width_unit'] = $length->row['unit'];
		$data['height_unit'] = $length->row['unit'];
		$data['volume_unit'] = $length->row['unit'] . '&sup3;';
		$weight = $this->db->query("SELECT * FROM " . DB_PREFIX . "weight_class_description WHERE weight_class_id = " . (int)$this->config->get('config_weight_class_id'));
		$data['weight_unit'] = $weight->row['unit'];

		$data['typeaheads'] = array('category', 'manufacturer', 'product');
		if (!empty($data['saved']['autocomplete_preloading'])) {
			$data['all_preload'] = array();
			foreach ($data['typeaheads'] as $typeahead_type) {
				$data[$typeahead_type . '_preload'] = array();
				$data_query = $this->db->query("SELECT * FROM " . DB_PREFIX . $typeahead_type . ($typeahead_type == 'manufacturer' ? "" : "_description"));
				foreach ($data_query->rows as $row) {
					if ($typeahead_type == 'category') {
						$category_id = $row['category_id'];
						$parent_exists = true;

                        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category_description WHERE category_id = (SELECT parent_id FROM " . DB_PREFIX . "category WHERE category_id = " . (int)$category_id . ")");
                        if (isset($query->row['name']) && false == empty($query->row['name'])) {
                            $category_id = $query->row['category_id'];
                            $row['name'] = $query->row['name'] . ' > ' . $row['name'];
                        }

						/*while ($parent_exists) {
							$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category_description WHERE category_id = (SELECT parent_id FROM " . DB_PREFIX . "category WHERE category_id = " . (int)$category_id . ")");
							if (!empty($query->row['name'])) {
								$category_id = $query->row['category_id'];
								$row['name'] = $query->row['name'] . ' > ' . $row['name'];
							} else {
								$parent_exists = false;
							}
						}*/
					}
					$row_name = str_replace(array("\n", '"'), array(' ', '&quot;'), html_entity_decode($row['name'], ENT_NOQUOTES, 'UTF-8'));
					$data['all_preload'][] = '"' . $row_name . ' [' . $typeahead_type . ':' . $row[$typeahead_type . '_id'] . ']",';
					$data[$typeahead_type . '_preload'][] = '"' . $row_name . ' [' . $typeahead_type . ':' . $row[$typeahead_type . '_id'] . ']",';
				}
				natcasesort($data[$typeahead_type . '_preload']);
				$data[$typeahead_type . '_preload'] = implode('', $data[$typeahead_type . '_preload']);
			}
			natcasesort($data['all_preload']);
			$data['all_preload'] = implode('', $data['all_preload']);
		}

		$data['product_groups'] = array();
		$data['rule_sets'] = array();
		foreach ($data['saved'] as $key => $setting) {
			if (preg_match('/product_group_(\d+)_name/', $key)) {
				$data['product_groups'][str_replace(array('product_group_', '_name'), '', $key)] = $setting;
			} elseif (preg_match('/rule_set_(\d+)_name/', $key)) {
				$data['rule_sets'][str_replace(array('rule_set_', '_name'), '', $key)] = $setting;
			}
		}

        $data['tabs'] = array(
            'charges',
        );

  
        $data['setting'] = [];
        $data['setting']['distance_calculation'] = [
            'driving',
            'straightline',
        ];

        $data['setting']['languages'] = $this->model_localisation_language->getLanguages();

        $charge_options = array();
        $charge_options['text_simple_charges']['flat'] = $data['text_flat_charge'];
		//$charge_options['text_simple_charges']['peritem'] = $data['text_per_item_charge'];
		$charge_options['text_bracket_charges']['percentage']	= $data['text_percentage'];
      //  $charge_options['text_bracket_charges']['distance'] = $data['text_distance'];
      //  $charge_options['text_bracket_charges']['postcode']	= $data['text_postcode'];
      //  $charge_options['text_bracket_charges']['price']	= $data['text_price'];
      //  $charge_options['text_bracket_charges']['quantity']	= $data['text_quantity'];
      //  $charge_options['text_bracket_charges']['total']	= $data['text_total'];
      //  $charge_options['text_bracket_charges']['volume']	= $data['text_volume'];
      //  $charge_options['text_bracket_charges']['weight']	= $data['text_weight'];
        

		$data['setting']['chargeOptGroups'] = $charge_options;
		

        $table = 'charge';
        $sortby = 'group';
        $data['settings'][] = array(
            'key'		=> $table,
            'type'		=> 'table_start',
            'columns'	=> array('action', 'group', 'title', 'charge', 'rules'),
        );
        $data['setting']['charges'] = [];
        foreach ($this->getTableRowNumbers($data, $table, $sortby) as $num => $rules) {
            $data['setting']['charges'][$num] = $num;
		}
		
		//ksort($data['setting']['charges']);
        $table = 'combination';
        $sortby = 'sort_order';
        $data['settings'][] = array(
            'key'		=> $table,
            'type'		=> 'table_start',
            'columns'	=> array('action', 'group', 'title', 'charge', 'rules'),
        );
        $data['setting']['combination'] = [];
        foreach ($this->getTableRowNumbers($data, $table, $sortby) as $num => $rules) {
            $data['setting']['combination'][$num] = $num;
        }

        $table = 'product_group';
        $sortby = 'sort_order';
        $data['settings'][] = array(
            'key'		=> $table,
            'type'		=> 'table_start',
            'columns'	=> array('action', 'sort_order', 'name', 'group_members', ''),
        );
        $data['setting']['product_group'] = [];
        foreach ($this->getTableRowNumbers($data, $table, $sortby) as $num => $rules) {
            $data['setting']['product_group'][$num] = $num;
        }
        $table = 'rule_set';
        $sortby = 'sort_order';
        $data['settings'][] = array(
            'key'		=> $table,
            'type'		=> 'table_start',
            'columns'	=> array('action', 'sort_order', 'name', 'rules'),
        );
        $data['setting']['rule_set'] = [];
        foreach ($this->getTableRowNumbers($data, $table, $sortby) as $num => $rules) {
            $data['setting']['rule_set'][$num] = $num;
        }

//        echo '<pre>';print_r($data['setting']['product_group']);exit;

		//------------------------------------------------------------------------------
		// Extensions Settings
		//------------------------------------------------------------------------------
		$data['settings'] = array();
		
		$data['settings'][] = array(
			'type'		=> 'tabs',
			'tabs'		=> array('extension_settings', 'charges', 'charge_combinations', 'product_groups', 'rule_sets'),
		);
		$data['settings'][] = array(
			'key'		=> 'extension_settings',
			'type'		=> 'heading',
			'buttons'	=> 'backup_restore',
		);
		$data['settings'][] = array(
			'key'		=> 'status',
			'type'		=> 'select',
			'options'	=> array(0 => $data['text_disabled'], 1 => $data['text_enabled']),
            'default'	=> 1
		);
		if ($this->type == 'shipping') {
			$data['settings'][] = array(
				'key'		=> 'heading',
				'type'		=> 'multilingual_text',
				'default'	=> $data['heading_title'],
			);
		}
		if ($this->type != 'module') {
			$data['settings'][] = array(
				'key'		=> 'sort_order',
				'type'		=> 'text',
				'default'	=> ($this->type == 'shipping' ? 1 : 3),
				'class'		=> 'short',
				'attributes'=> array('maxlength' => 2),
			);
	
		}
		if ((isset($data['rule_options']['location_criteria']) && in_array('distance', $data['rule_options']['location_criteria'])) || strpos($this->name, 'distance_based') === 0) {
			$data['settings'][] = array(
				'key'		=> 'distance_calculation',
				'type'		=> 'select',
				'options'	=> array('driving' => $data['text_driving_distance'], 'straightline' => $data['text_straightline_distance']),
			);
			$data['settings'][] = array(
				'key'		=> 'distance_units',
				'type'		=> 'select',
				'options'	=> array('mi' => $data['text_miles'], 'km' => $data['text_kilometers']),
			);
		}
		$data['settings'][] = array(
			'key'		=> 'testing_mode',
			'type'		=> 'select',
			'options'	=> array(0 => $data['text_disabled'], 1 => $data['text_enabled']),
		);
				
		// Admin Panel Settings
		$data['settings'][] = array(
			'key'		=> 'admin_panel_settings',
			'type'		=> 'heading',
		);
		$data['settings'][] = array(
			'key'		=> 'autosave',
			'type'		=> 'select',
			'options'	=> array(0 => $data['text_disabled'], 1 => $data['text_enabled']),
		);
		if (!empty($data['typeaheads'])) {
			$data['settings'][] = array(
				'key'		=> 'autocomplete_preloading',
				'type'		=> 'select',
				'options'	=> array(0 => $data['text_disabled'], 1 => $data['text_enabled']),
			);
		}
		$data['settings'][] = array(
			'key'		=> 'display',
			'type'		=> 'select',
			'options'	=> array('expanded' => $data['text_expanded'], 'collapsed' => $data['text_collapsed']),
		);
		$data['settings'][] = array(
			'key'		=> 'tooltips',
			'type'		=> 'select',
			'options'	=> array(0 => $data['text_disabled'], 1 => $data['text_enabled']),
			'default'	=> 1,
		);
		
		//------------------------------------------------------------------------------
		// Charges
		//------------------------------------------------------------------------------
		$data['settings'][] = array(
			'key'		=> 'charges',
			'type'		=> 'tab',
		);
		$data['settings'][] = array(
			'type'		=> 'html',
			'content'	=> '<div class="text-info text-center" style="padding-bottom: 5px">' . $data['help_charges'] . '</div>',
		);
		$data['settings'][] = array(
			'key'		=> 'charges',
			'type'		=> 'heading',
			'buttons'	=> 'expand_collapse',
		);
		
		$table = 'charge';// TODO remove
		$sortby = 'group';
		$data['settings'][] = array(
			'key'		=> $table,
			'type'		=> 'table_start',
			'columns'	=> array('action', 'group', 'title', 'charge', 'rules'),
		);
		foreach ($this->getTableRowNumbers($data, $table, $sortby) as $num => $rules) {
			$prefix = $table . '_' . $num . '_';
			$data['settings'][] = array(
				'type'		=> 'row_start',
			);
			$data['settings'][] = array(
				'key'		=> 'expand_collapse',
				'type'		=> 'button',
			);
			$data['settings'][] = array(
				'key'		=> 'copy',
				'type'		=> 'button',
			);
			$data['settings'][] = array(
				'key'		=> 'delete',
				'type'		=> 'button',
			);
			$data['settings'][] = array(
				'type'		=> 'column',
			);
			$data['settings'][] = array(
				'key'		=> $prefix . 'group',
				'type'		=> 'text',
				'class'		=> 'short',
				'attributes'=> array('maxlength' => 2),
			);
			$data['settings'][] = array(
				'type'		=> 'column',
			);
			$data['settings'][] = array(
				'key'		=> $prefix . 'title',
				'type'		=> 'multilingual_text',
				'admin_ref'	=> true,
			);
			$data['settings'][] = array(
				'type'		=> 'column',
			);
			
			$charge_options = array();
			$charge_options['text_simple_charges']			= '';
			$charge_options['flat'] 						= $data['text_flat_charge'];
			//$charge_options['peritem']						= $data['text_per_item_charge'];
			$charge_options['percentage']					= $data['text_percentage'];
		//	$charge_options['text_bracket_charges']			= '';
		//	$charge_options['distance']						= $data['text_distance'];
		//	$charge_options['postcode']						= $data['text_postcode'];
		//	$charge_options['price']						= $data['text_price'];
		//	$charge_options['quantity']						= $data['text_quantity'];
		//	$charge_options['total']						= $data['text_total'];
		//	$charge_options['volume']						= $data['text_volume'];
		//	$charge_options['weight']						= $data['text_weight'];
			
			
			$data['settings'][] = array(
				'key'		=> $prefix . 'type',
				'type'		=> 'select',
				'options'	=> $charge_options,
			);
			$data['settings'][] = array(
				'key'		=> $prefix . 'charges',
				'type'		=> 'textarea',
			);
			$data['settings'][] = array(
				'type'		=> 'column',
			);
			$data['settings'][] = array(
				'key'		=> $prefix . 'rule',
				'type'		=> 'rule',
				'rules'		=> $rules,
                'group'     => $table
			);
			$data['settings'][] = array(
				'type'		=> 'row_end',
			);
		}

		$data['settings'][] = array(
			'type'		=> 'table_end',
			'buttons'	=> 'add_row',
			'text'		=> 'button_add_charge',
		);
		
		//------------------------------------------------------------------------------
		// Charge Combinations
		//------------------------------------------------------------------------------
		$data['settings'][] = array(
			'key'		=> 'charge_combinations',
			'type'		=> 'tab',
		);
		$data['settings'][] = array(
			'type'		=> 'html',
			'content'	=> '<div class="text-info text-center" style="padding-bottom: 5px">' . $data['help_charge_combinations'] . '</div>',
		);
		$data['settings'][] = array(
			'key'		=> 'charge_combinations',
			'type'		=> 'heading',
		);
		
		$table = 'combination';
		$sortby = 'sort_order';
		$data['settings'][] = array(
			'key'		=> $table,
			'type'		=> 'table_start',
			'columns'	=> array('action', 'sort_order', 'title_combination', 'combination_formula'),
		);
		foreach ($this->getTableRowNumbers($data, $table, $sortby) as $num => $rules) {
			$prefix = $table . '_' . $num . '_';
			$data['settings'][] = array(
				'type'		=> 'row_start',
			);
			$data['settings'][] = array(
				'key'		=> 'delete',
				'type'		=> 'button',
			);
			$data['settings'][] = array(
				'type'		=> 'column',
			);
			$data['settings'][] = array(
				'key'		=> $prefix . 'sort_order',
				'type'		=> 'text',
				'class'		=> 'short',
				'attributes'=> array('maxlength' => 2),
			);
			$data['settings'][] = array(
				'type'		=> 'column',
			);
			$data['settings'][] = array(
				'key'		=> $prefix . 'title',
				'type'		=> 'select',
				'options'	=> array(
					'single'			=> $data['text_single_title'],
					'combined'			=> $data['text_combined_title_no_prices'],
					'combined_prices'	=> $data['text_combined_title_with_prices']
				),
			);
			$data['settings'][] = array(
				'type'		=> 'column',
			);
			$data['settings'][] = array(
				'key'		=> $prefix . 'formula',
				'type'		=> 'text',
				'attributes'=> array('style' => 'font-family: monospace; font-size: 14px; width: 300px !important', 'placeholder' => $data['placeholder_formula']),
			);
			$data['settings'][] = array(
				'type'		=> 'row_end',
			);
		}
		
		$data['settings'][] = array(
			'type'		=> 'table_end',
			'buttons'	=> 'add_row',
			'text'		=> 'button_add_combination',
		);
		
		//------------------------------------------------------------------------------
		// Product Groups
		//------------------------------------------------------------------------------
		$data['settings'][] = array(
			'key'		=> 'product_groups',
			'type'		=> 'tab',
		);
		$data['settings'][] = array(
			'type'		=> 'html',
			'content'	=> '<div class="text-info text-center" style="padding-bottom: 5px">' . $data['help_product_groups'] . '</div>',
		);
		$data['settings'][] = array(
			'key'		=> 'product_groups',
			'type'		=> 'heading',
			'buttons'	=> 'expand_collapse',
		);
		
		$table = 'product_group';
		$sortby = 'sort_order';
		$data['settings'][] = array(
			'key'		=> $table,
			'type'		=> 'table_start',
			'columns'	=> array('action', 'sort_order', 'name', 'group_members', ''),
		);
		foreach ($this->getTableRowNumbers($data, $table, $sortby) as $num => $rules) {
			$prefix = $table . '_' . $num . '_';
			$data['settings'][] = array(
				'type'		=> 'row_start',
			);
			$data['settings'][] = array(
				'key'		=> 'expand_collapse',
				'type'		=> 'button',
			);
			$data['settings'][] = array(
				'key'		=> 'copy',
				'type'		=> 'button',
			);
			$data['settings'][] = array(
				'key'		=> 'delete',
				'type'		=> 'button',
			);
			$data['settings'][] = array(
				'type'		=> 'column',
			);
			$data['settings'][] = array(
				'key'		=> $prefix . 'sort_order',
				'type'		=> 'text',
				'class'		=> 'short',
				'attributes'=> array('maxlength' => 2),
			);
			$data['settings'][] = array(
				'type'		=> 'column',
			);
			$data['settings'][] = array(
				'key'		=> $prefix . 'name',
				'type'		=> 'text',
			);
			$data['settings'][] = array(
				'type'		=> 'column',
			);
			$data['settings'][] = array(
				'key'		=> $prefix . 'member',
				'type'		=> 'typeahead',
			);
			$data['settings'][] = array(
				'type'		=> 'row_end',
			);
		}
		$data['settings'][] = array(
			'type'		=> 'table_end',
			'buttons'	=> 'add_row',
			'text'		=> 'button_add_product_group',
		);
		
		//------------------------------------------------------------------------------
		// Rule Sets
		//------------------------------------------------------------------------------
		$data['settings'][] = array(
			'key'		=> 'rule_sets',
			'type'		=> 'tab',
		);
		$data['settings'][] = array(
			'type'		=> 'html',
			'content'	=> '<div class="text-info text-center" style="padding-bottom: 5px">' . $data['help_rule_sets'] . '</div>',
		);
		$data['settings'][] = array(
			'key'		=> 'rule_sets',
			'type'		=> 'heading',
			'buttons'	=> 'expand_collapse',
		);
		
		$table = 'rule_set';
		$sortby = 'sort_order';
		$data['settings'][] = array(
			'key'		=> $table,
			'type'		=> 'table_start',
			'columns'	=> array('action', 'sort_order', 'name', 'rules'),
		);
		foreach ($this->getTableRowNumbers($data, $table, $sortby) as $num => $rules) {
			$prefix = $table . '_' . $num . '_';
			$data['settings'][] = array(
				'type'		=> 'row_start',
			);
			$data['settings'][] = array(
				'key'		=> 'expand_collapse',
				'type'		=> 'button',
			);
			$data['settings'][] = array(
				'key'		=> 'copy',
				'type'		=> 'button',
			);
			$data['settings'][] = array(
				'key'		=> 'delete',
				'type'		=> 'button',
			);
			$data['settings'][] = array(
				'type'		=> 'column',
			);
			$data['settings'][] = array(
				'key'		=> $prefix . 'sort_order',
				'type'		=> 'text',
				'class'		=> 'short',
				'attributes'=> array('maxlength' => 2),
			);
			$data['settings'][] = array(
				'type'		=> 'column',
			);
			$data['settings'][] = array(
				'key'		=> $prefix . 'name',
				'type'		=> 'text',
			);
			$data['settings'][] = array(
				'type'		=> 'column',
			);
			$data['settings'][] = array(
				'key'		=> $prefix . 'rule',
				'type'		=> 'rule',
				'rules'		=> $rules,
                'group'     => $table
			);
			$data['settings'][] = array(
				'type'		=> 'row_end',
			);
		}
		
		$data['settings'][] = array(
			'type'		=> 'table_end',
			'buttons'	=> 'add_row',
			'text'		=> 'button_add_rule_set',
		);

		//------------------------------------------------------------------------------
		// end settings
		//------------------------------------------------------------------------------

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', '', 'SSL'),
			'separator' => false
		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_shipping'),
			'href'      => $this->url->link('extension/shipping', '', 'SSL'),
			'separator' => ' :: '
		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('shipping/aramex', '', 'SSL'),
			'separator' => ' :: '
		);

		$__rules = [];
		foreach ($data['settings'] as $setting) {
		    if ($setting['type'] == 'rule') {
                $__rules[$setting['group']][$setting['key']] = $setting['rules'];
            }
        }
        
        $data['newrules'] = $__rules;
   
//		echo '<pre>';print_r($data['saved']);exit;

			$data['links'] = [
				'submit' => $this->url->link('shipping/seller_based/saveSettings', 'saving=manual', 'SSL'),
				'action' => $this->url->link('shipping/seller_based/saveSettings', 'saving=manual', 'SSL'),
			];

		$this->document->setTitle($data['heading_title']);

        $this->data = $data;
        $this->data['cancel'] = $this->url->link('shipping/seller_based', '', 'SSL');
		$this->template ='default/template/multiseller/seller_based.expand';

        $this->children = array(
            'common/header',
            'common/footer',
        );
        $this->response->setOutput($this->render_ecwig());
	}
	
	//==============================================================================
	// Setting functions
	//==============================================================================
	private $encryption_key = '';
	private $columns = 7;
	
	private function getTableRowNumbers($data, $table, $sorting)
    {
		$groups = array();
		$rules = array();
	
		foreach ($data['saved'] as $key => $setting) {
			if (preg_match('/' . $table . '_(\d+)_' . $sorting . '/', $key, $matches)) {
				$groups[$setting][] = $matches[1];
			}
			if (preg_match('/' . $table . '_(\d+)_rule_(\d+)_type/', $key, $matches)) {
				$rules[$matches[1]][] = $matches[2];
			}
		}
		
		if (empty($groups)) {
			$groups = array('' => array('1'));
		}
		ksort($groups, SORT_NUMERIC);
		
		$rows = array();
		foreach ($groups as $group) {
			foreach ($group as $num) {
				$rows[$num] = (empty($rules[$num])) ? array() : $rules[$num];
			}
		}
		
		return $rows;
	}
	
	public function loadSettings(&$data)
    {
		
		$backup_type = (empty($data)) ? 'manual' : 'auto';
		$this->language->load_json('shipping/seller_based');
	
		$seller_id= $this->customer->getId();
		$data['seller_id']=$seller_id;
		
		$group= 'seller_based';

		$valueOfKey='seller_based_charge_'.$seller_id;
		$sellerBasedSettingsConfig= $this->config->get('seller_based_charge_'.$seller_id);
		$data['saved'] = array();
		$data['saved_admin_defult_charges'] = array();
		$data['saved'] = $sellerBasedSettingsConfig;
		$data['saved_admin_defult_charges']=$sellerBasedSettingsConfig['admin_defult_charges'];

	}
	
	public function saveSettings()
    {
		 
		$this->language->load_json('shipping/seller_based');

		$seller_id=$this->customer->getId();
        $group= 'seller_based';
        $data=$this->request->post;
		$valueOfKey='seller_based_charge_'.$seller_id;

	    if ($this->request->get['saving'] == 'manual') {
			$this->load->model('setting/setting');
			$this->model_setting_setting->insertUpdateSetting($group, [$valueOfKey =>$data]);
		}   
		
		$this->session->data['success']=$this->language->get('text_success');
		$this->redirect(
			$this->url->link('shipping/seller_based/activate', 'code=seller_based&activated=1&delivery_company=0','SSL')
		);
	}

	public function activate() {

		$code = $this->request->get['code'];
		 //$this->request->get['page']
		 if (!isset($code) ||empty($code)){
			 return;
		 }
		 $this->load->language('shipping/'.$code);
		 $this->document->setTitle($this->language->get('heading_title'));
 
		 $this->data['breadcrumbs'] = array();
 
		 $this->data['breadcrumbs'][] = array(
			 'text'      => $this->language->get('text_home'),
			 'href'      => $this->url->link('common/home', '', 'SSL'),
			 'separator' => false
		 );
 
		 $this->data['breadcrumbs'][] = array(
			 'text'      => $this->language->get('heading_title'),
			 'href'      => $this->url->link('extension/shipping', '', 'SSL'),
			 'separator' => ' :: '
		 );
 
		 $this->data['breadcrumbs'][] = array(
			 'text'    => !isset($this->request->get['activated']) ?
				 $this->language->get('breadcrumb_insert') :
				 $this->language->get('breadcrumb_update'),
			 'href'      =>"",
			 'separator' => ' :: '
		 );
 
		 $this->getForm();
	 }
 
	 public function getForm()
	 {
		 
		 $code = $this->request->get['code'];
		 if (count($this->request->post) > 0) {
			 $this->data = array_merge($this->data, $this->request->post);
		 }
 
		 $this->data['cancel'] = $this->url->link('extension/shipping', '', 'SSL');
 
		 $this->load->model('setting/store');
 
		 $this->data['stores'] = $this->model_setting_store->getStores();
 
		 //getShippingMethodData
 
		 if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
			 $this->data['store_url'] = HTTPS_CATALOG;
		 } else {
			 $this->data['store_url'] = HTTP_CATALOG;
		 }
 
		 $settingGroup = $this->config->get($code);
		 $status = null;
		 if ($settingGroup && is_array($settingGroup) === true) {
			 $status = $settingGroup['status'];
		 } else {
			 $status = $this->config->get($code . '_status');
		 }
 
		 // installed = 0 or 1
 
		 $activated= 0;
		 if (! is_null($status) || $this->request->get['activated']){
			 $activated= 1;
		 }
 
		 $this->data['activated'] = $activated;
 
		 // delivery_company = 0 or 1
 
		 $this->data['delivery_company'] = $this->request->get['delivery_company'];
 
		 // link ex : http://qaz123.expandcart.com/admin/extension/shipping/activate?activated=1&delivery_company=0&code=pickup
 
		 try {
			 $this->data['shipping_form_inputs'] = $this->getChild('shipping/' . $code);
		 }
		 catch (\ExpandCart\Foundation\Exceptions\FileException $e) {
			 $this->redirect($this->url->link('extension/shipping', '', 'SSL'));
		 }
		 if ($this->config->get('config_logo')) {
			 // $this->data['logo'] = $server . 'image/' . STORECODE . '/' . $this->config->get('config_logo');
			 $this->data['icon'] = \Filesystem::getUrl('image/' . $this->config->get('config_logo'));
		 } else {
			 $this->data['logo'] = '';
		 }
		 $display_name="";
		 if ($this->customer->getFirstName())
			 $display_name = $this->customer->getFirstName();
		 else
			 $display_name = $this->customer->getEmail();
		 $this->language->load_json('common/header');
		 $this->data['title'] = $this->language->get('ms_account_dashboard_shipping_methods');
		 $this->data['styles'] = $this->document->getStyles();
		 $this->data['direction'] = $this->language->get('direction');
		 $this->data['lang'] = $this->language->get('code');
		 $this->data['home'] = $this->url->link('common/home');
		 $this->data['text_home'] = $this->language->get('text_home');
		 $this->data['wishlist'] = $this->url->link('account/wishlist', '', 'SSL');
		 $this->data['text_wishlist'] = sprintf($this->language->get('text_wishlist'), (isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0)); 
		 $this->data['account'] = $this->url->link('account/account', '', 'SSL');
		 $this->data['text_account'] = $this->language->get('text_account');   
		 $this->data['shopping_cart'] = $this->url->link('checkout/cart');
		 $this->data['text_shopping_cart'] = $this->language->get('text_shopping_cart');
		 $this->data['checkout'] = $this->url->link('checkout/checkout', '', 'SSL');
		 $this->data['text_checkout'] = $this->language->get('text_checkout');
		 $this->data['text_welcome'] = sprintf($this->language->get('text_welcome'), $this->url->link('account/login', '', 'SSL'), $this->url->link('account/register', '', 'SSL'));
		 $this->data['text_logged'] = sprintf($this->language->get('text_logged'), $this->url->link('account/account', '', 'SSL'), $display_name, $this->url->link('account/logout', '', 'SSL'));
		 $this->data['logged'] = $this->customer->isLogged();
		 $this->data['breadcrumbs'] = $this->MsLoader->MsHelper->setBreadcrumbs(array(
			 array(
				 'text' => $this->language->get('text_account'),
				 'href' => $this->url->link('account/account', '', 'SSL'),
			 ),
			 array(
				 'text' => $this->language->get('ms_account_dashboard_shipping_methods'),
				 'href' => $this->url->link('seller/account-dashboard', '', 'SSL'),
			 )
		 ));
		 $this->template ='default/template/multiseller/seller_based_shipping_form.expand';
		 $this->children = array(
			 'common/header',
			 'common/footer'
		 );
 
		 $this->response->setOutput($this->render_ecwig());
	 }
	//==============================================================================
	// Backup functions
	//==============================================================================
	public function backupSettings()
    {
		$data = array();
		$this->loadSettings($data);
	}
	
	public function viewBackup()
    {
		
		if (!file_exists(DIR_LOGS . $this->name . '_backup.txt')) {
			echo 'Backup file "' . DIR_LOGS . $this->name . '_backup.txt" does not exist';
			return;
		}
		
		$contents = trim(file_get_contents(DIR_LOGS . $this->name . '_backup' . $this->encryption_key . '.txt'));
		$lines = explode("\n", $contents);
		
		$html = '<table border="1" style="font-family: monospace" cellspacing="0" cellpadding="5">';
		foreach ($lines as $line) {
			$html .= '<tr><td>' . implode('</td><td>', explode("\t", $line)) . '</td></tr>';
		}
		echo str_replace('<td></td>', '<td style="background: #DDD"></td>', $html);
	}
	
	public function downloadBackup()
    {
		$file = DIR_LOGS . $this->name . '_backup' . $this->encryption_key . '.txt';
	
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Description: File Transfer');
		header('Content-Disposition: attachment; filename=' . $this->name . '_backup.' . date('Y-n-d') . '.txt');
		header('Content-Length: ' . filesize($file));
		header('Content-Transfer-Encoding: binary');
		header('Content-Type: application/octet-stream');
		header('Expires: 0');
		header('Pragma: public');
		readfile($file);
	}
	
	public function restoreSettings()
    {
		$this->language->load($this->type . '/' . $this->name);
		
		
		if ($this->request->post['from'] == 'auto') {
			$filepath = DIR_LOGS . $this->name . '_autobackup' . $this->encryption_key . '.txt';
		} elseif ($this->request->post['from'] == 'manual') {
			$filepath = DIR_LOGS . $this->name . '_backup' . $this->encryption_key . '.txt';
		} elseif ($this->request->post['from'] == 'file') {
			$filepath = $this->request->files['backup_file']['tmp_name'];
			if (empty($filepath)) {
				$this->response->redirect(str_replace(array('&amp;', "\n", "\r"), array('&', '', ''), $this->url->link($this->type . '/' . $this->name, '', 'SSL')));
			}
		}
		
		$contents = str_replace("\r\n", "\n", trim(file_get_contents($filepath)));
		
		if (strpos($contents, 'EXTENSION') !== 0) {
			
			$result_json['success'] = '0';
			
			$result_json['errors'] = array('warning' => $this->language->get('error_invalid_file_data') );

			$this->response->setOutput(json_encode($result_json));

			return;
		}
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE `" . (version_compare(VERSION, '2.0.1') < 0 ? 'group' : 'code') . "` = '" . $this->db->escape($this->name) . "'");
		
		foreach (explode("\n", $contents) as $row) {
			if (empty($row) || strpos($row, 'EXTENSION') === 0) continue;
			
			$col = explode("\t", $row);
			$value = str_replace('\n', "\n", array_pop($col));
			$key = implode('_', array_diff($col, array('')));
			
			$this->db->query("INSERT INTO " . DB_PREFIX . "setting SET `store_id` = 0, `" . (version_compare(VERSION, '2.0.1') < 0 ? 'group' : 'code') . "` = '" . $this->db->escape($this->name) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape($value) . "', `serialized` = 0");
		}
		
		$result_json['success'] = '1';
		
		$result_json['success_msg'] = $this->language->get('text_settings_restored');

		$this->response->setOutput(json_encode($result_json));
		
		return;
	}
	
	//==============================================================================
	// Ajax functions
	//==============================================================================
	public function loadDropdown()
    {
		$data = $this->load->language($this->type . '/' . $this->name);
		echo '<option value="">' . $data['standard_select'] . '</option>';
		$options = array();
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE `" . (version_compare(VERSION, '2.0.1') < 0 ? 'group' : 'code') . "` = '" . $this->name . "' AND `key` LIKE '" . $this->name . "_" . $this->request->get['type'] . "%'");
		foreach ($query->rows as $row) {
			if (strpos($row['key'], '_name')) {
				$num = str_replace(array($this->name . '_' . $this->request->get['type'] . '_', '_name'), '', $row['key']);
				foreach ($query->rows as $subrow) {
					if (strpos($subrow['key'], '_' . $num . '_sort_order') && $row['value']) {
						$options['<option value="' . $num . '">' . $row['value'] . '</option>'] = $subrow['value'];
						break;
					}
				}
			}
		}
		natcasesort($options);
		foreach ($options as $option => $sort_order) {
			echo $option;
		}
	}
	
	public function typeahead()
    {
		$search = (strpos($this->request->get['q'], '[')) ? substr($this->request->get['q'], 0, strpos($this->request->get['q'], ' [')) : $this->request->get['q'];
		
		if ($this->request->get['type'] == 'all') {
			if (strpos($this->name, 'ultimate') === 0) {
				$tables = array('attribute_group_description', 'attribute_description', 'category_description', 'manufacturer', 'option_description', 'option_value_description', 'product_description');
			} else {
				$tables = array('category_description', 'manufacturer', 'product_description');
			}
		} elseif ($this->request->get['type'] == 'customer') {
			$tables = array('customer');
		} elseif ($this->request->get['type'] == 'manufacturer') {
			$tables = array('manufacturer');
		}
		/*elseif ($this->request->get['type'] == 'warehouse') {
			$tables = array('warehouses');
		}*/  else {
			$tables = array($this->request->get['type'] . '_description');
		}
		
		$results = array();
		foreach ($tables as $table) {
			if ($table == 'customer') {
				$query = $this->db->query("SELECT customer_id, CONCAT(firstname, ' ', lastname, ' (', email, ')') as name FROM " . DB_PREFIX . $table . " WHERE CONCAT(firstname, ' ', lastname, ' (', email, ')') LIKE '%" . $this->db->escape($search) . "%' ORDER BY name ASC LIMIT 0,100");
			} else {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . $table . " WHERE name LIKE '%" . $this->db->escape($search) . "%' ORDER BY name ASC LIMIT 0,100");
			}
			$results = array_merge($results, $query->rows);
		}
		
		if (empty($results)) {
			$variations = array();
			for ($i = 0; $i < strlen($search); $i++) {
				$variations[] = substr_replace($search, '_', $i, 1);
				$variations[] = substr_replace($search, '', $i, 1);
				if ($i != strlen($search)-1) {
					$transpose = $search;
					$transpose[$i] = $search[$i+1];
					$transpose[$i+1] = $search[$i];
					$variations[] = $transpose;
				}
			}
			foreach ($tables as $table) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . $table . " WHERE name LIKE '%" . implode("%' OR name LIKE '%", $variations) . "%' ORDER BY name ASC LIMIT 0,100");
				$results = array_merge($results, $query->rows);
			}
		}
		
		$items = array();
		foreach ($results as $result) {
			if (key($result) == 'category_id') {
				$category_id = reset($result);
				$parent_exists = true;
				while ($parent_exists) {
					$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category_description WHERE category_id = (SELECT parent_id FROM " . DB_PREFIX . "category WHERE category_id = " . (int)$category_id . ")");
					if (!empty($query->row['name'])) {
						$category_id = $query->row['category_id'];
						$result['name'] = $query->row['name'] . ' > ' . $result['name'];
					} else {
						$parent_exists = false;
					}
				}
			}
			$items[] = html_entity_decode($result['name'], ENT_NOQUOTES, 'UTF-8') . ' [' . key($result) . ':' . reset($result) . ']';
		}
		
		natcasesort($items);
		echo '["' . implode('","', str_replace(array('"', '_id'), array('&quot;', ''), $items)) . '"]';
	}
	
}
