<?php


class ControllerShippingCategoryProductBased extends Controller
{
	private $type = 'shipping';
	private $name = 'category_product_based';
	
	public function index()
    {
		$data = array(
			'type'				=> $this->type,
			'name'				=> $this->name,
			'autobackup'		=> true,
			'save_type'			=> 'keepediting',
			'token'				=> null,
			'permission'		=> $this->user->hasPermission('modify', $this->type . '/' . $this->name),
			'exit'				=> $this->url->link('extension/' . $this->type, '', 'SSL'),
		);
		
		$this->loadSettings($data);
		
		//------------------------------------------------------------------------------
		// Data Arrays
		//------------------------------------------------------------------------------
		//'warehouse'
		$data['rule_options'] = array(
			'adjustments'				=> array('adjust', 'cumulative', 'max', 'min', 'round', 'setting_override', 'tax_class', 'total_value'),
			'cart_criteria'				=> array('length', 'width', 'height', 'quantity', 'stock', 'total', 'volume', 'weight'),
			'datetime_criteria'			=> array('day', 'date', 'time'),
			'location_criteria'			=> array('city', 'distance', 'geo_zone', 'location_comparison', 'postcode'),
			'order_criteria'			=> array('currency', 'customer_group', 'language', 'past_orders', 'store'),
			'product_criteria'			=> array('category', 'manufacturer', 'product', 'product_group'),
			'rule_sets'					=> array('rule_set'),
		);
		
		$data['setting_override_array'] = array(
			array((version_compare(VERSION, '2.0') < 0 ? 'group' : 'code') => 'config', 'key' => 'config_address', 'value' => $this->config->get('config_address')),
		);
		
		$data['currency_array'] = array($this->config->get('config_currency') => '');
		$this->load->model('localisation/currency');
		foreach ($this->model_localisation_currency->getCurrencies() as $currency) {
			$data['currency_array'][$currency['code']] = $currency['code'];
		}
		
		$data['customer_group_array'] = array(0 => $data['text_guests']);
		$this->load->model((version_compare(VERSION, '2.1', '<') ? 'sale' : 'customer') . '/customer_group');
		foreach ($this->{'model_' . (version_compare(VERSION, '2.1', '<') ? 'sale' : 'customer') . '_customer_group'}->getCustomerGroups() as $customer_group) {
			$data['customer_group_array'][$customer_group['customer_group_id']] = $customer_group['name'];
		}
		
		$data['geo_zone_array'] = array(0 => $data['text_everywhere_else']);
		$this->load->model('localisation/geo_zone');
		foreach ($this->model_localisation_geo_zone->getGeoZones() as $geo_zone) {
			$data['geo_zone_array'][$geo_zone['geo_zone_id']] = $geo_zone['name'];
		}
		
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
		
		$data['tax_class_array'] = array(0 => $data['text_none']);
		$this->load->model('localisation/tax_class');
		foreach ($this->model_localisation_tax_class->getTaxClasses() as $tax_class) {
			$data['tax_class_array'][$tax_class['tax_class_id']] = $tax_class['title'];
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
		// if (!empty($data['saved']['autocomplete_preloading'])) {
		// 	$data['all_preload'] = array();
		// 	foreach ($data['typeaheads'] as $typeahead_type) {
		// 		$data[$typeahead_type . '_preload'] = array();
		// 		$data_query = $this->db->query("SELECT * FROM " . DB_PREFIX . $typeahead_type . ($typeahead_type == 'manufacturer' ? "" : "_description"));
		// 		foreach ($data_query->rows as $row) {
		// 			if ($typeahead_type == 'category') {
		// 				$category_id = $row['category_id'];
		// 				$parent_exists = true;

        //                 $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category_description WHERE category_id = (SELECT parent_id FROM " . DB_PREFIX . "category WHERE category_id = " . (int)$category_id . ")");
        //                 if (isset($query->row['name']) && false == empty($query->row['name'])) {
        //                     $category_id = $query->row['category_id'];
        //                     $row['name'] = $query->row['name'] . ' > ' . $row['name'];
        //                 }

		// 				/*while ($parent_exists) {
		// 					$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category_description WHERE category_id = (SELECT parent_id FROM " . DB_PREFIX . "category WHERE category_id = " . (int)$category_id . ")");
		// 					if (!empty($query->row['name'])) {
		// 						$category_id = $query->row['category_id'];
		// 						$row['name'] = $query->row['name'] . ' > ' . $row['name'];
		// 					} else {
		// 						$parent_exists = false;
		// 					}
		// 				}*/
		// 			}
		// 			$row_name = str_replace(array("\n", '"'), array(' ', '&quot;'), html_entity_decode($row['name'], ENT_NOQUOTES, 'UTF-8'));
		// 			$data['all_preload'][] = '"' . $row_name . ' [' . $typeahead_type . ':' . $row[$typeahead_type . '_id'] . ']",';
		// 			$data[$typeahead_type . '_preload'][] = '"' . $row_name . ' [' . $typeahead_type . ':' . $row[$typeahead_type . '_id'] . ']",';
		// 		}
		// 		natcasesort($data[$typeahead_type . '_preload']);
		// 		$data[$typeahead_type . '_preload'] = implode('', $data[$typeahead_type . '_preload']);
		// 	}
		// 	natcasesort($data['all_preload']);
		// 	$data['all_preload'] = implode('', $data['all_preload']);
		// }

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
            'extension_settings',
            'charges',
            'charge_combinations',
            'product_groups',
            'rule_sets',
        );

        $data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

        $data['setting'] = [];
        $data['setting']['distance_calculation'] = [
            'driving',
            'straightline',
        ];

        $data['setting']['languages'] = $this->model_localisation_language->getLanguages();

        $charge_options = array();
        $charge_options['text_simple_charges']['flat'] = $data['text_flat_charge'];
        $charge_options['text_simple_charges']['peritem'] = $data['text_per_item_charge'];
        $charge_options['text_bracket_charges']['distance'] = $data['text_distance'];
        $charge_options['text_bracket_charges']['postcode']	= $data['text_postcode'];
        $charge_options['text_bracket_charges']['price']	= $data['text_price'];
        $charge_options['text_bracket_charges']['quantity']	= $data['text_quantity'];
        $charge_options['text_bracket_charges']['total']	= $data['text_total'];
        $charge_options['text_bracket_charges']['volume']	= $data['text_volume'];
        $charge_options['text_bracket_charges']['weight']	= $data['text_weight'];

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
			$data['settings'][] = array(
				'key'		=> 'tax_class_id',
				'type'		=> 'select',
				'options'	=> $data['tax_class_array'],
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
			$charge_options['peritem']						= $data['text_per_item_charge'];
			$charge_options['text_bracket_charges']			= '';
			$charge_options['distance']						= $data['text_distance'];
			$charge_options['postcode']						= $data['text_postcode'];
			$charge_options['price']						= $data['text_price'];
			$charge_options['quantity']						= $data['text_quantity'];
			$charge_options['total']						= $data['text_total'];
			$charge_options['volume']						= $data['text_volume'];
			$charge_options['weight']						= $data['text_weight'];
			
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
            'submit' => $this->url->link('shipping/category_product_based/saveSettings', 'saving=manual', 'SSL'),
            'action' => $this->url->link('shipping/category_product_based/saveSettings', 'saving=manual', 'SSL'),
        ];

		$this->document->setTitle($data['heading_title']);

        $this->data = $data;
        $this->data['cancel'] = $this->url->link('shipping/category_product_based', '', 'SSL');
        $this->template = $this->type . '/' . $this->name . '.expand';

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
		if ($backup_type == 'manual' && !$this->user->hasPermission('modify', $this->type . '/' . $this->name)) {
			return;
		}
		
		// Load saved settings
		$data['saved'] = array();
		$settings_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE `" . (version_compare(VERSION, '2.0.1') < 0 ? 'group' : 'code') . "` = '" . $this->db->escape($this->name) . "' ORDER BY `key` ASC");
		
		foreach ($settings_query->rows as $setting) {
			$key = str_replace($this->name . '_', '', $setting['key']);
			$value = $setting['value'];
			if ($setting['serialized']) {
				$value = (version_compare(VERSION, '2.1', '<')) ? unserialize($setting['value']) : json_decode($setting['value'], true);
			}
			
			$data['saved'][$key] = $value;
			
			if (is_array($value)) {
				foreach ($value as $num => $value_array) {
					foreach ($value_array as $k => $v) {
						$data['saved'][$key . '_' . $num . '_' . $k] = $v;
					}
				}
			}
		}
		
		// Load language and check max_input _vars
		$data = array_merge($data, $this->load->language($this->type . '/' . $this->name));
		
		if (ini_get('max_input_vars') && ((ini_get('max_input_vars') - count($data['saved'])) < 50)) {
			$data['warning'] = $data['standard_warning'];
		}
		
		// Set save type
		if (!empty($data['saved']['autosave'])) {
			$data['save_type'] = 'auto';
		}
		
		// Skip auto-backup if not needed
		if ($backup_type == 'auto' && empty($data['autobackup'])) {
			return;
		}
		
		// Create settings auto-backup file
		$manual_filepath = DIR_LOGS . $this->name . '_backup' . $this->encryption_key . '.txt';
		$auto_filepath = DIR_LOGS . $this->name . '_autobackup' . $this->encryption_key . '.txt';
		$filepath = ($backup_type == 'auto') ? $auto_filepath : $manual_filepath;
		if (file_exists($filepath)) unlink($filepath);
		
		if ($this->columns == 3) {
			file_put_contents($filepath, 'EXTENSION	SETTING	VALUE' . "\n", FILE_APPEND|LOCK_EX);
		} elseif ($this->columns == 5) {
			file_put_contents($filepath, 'EXTENSION	SETTING	NUMBER	SUB-SETTING	VALUE' . "\n", FILE_APPEND|LOCK_EX);
		} else {
			file_put_contents($filepath, 'EXTENSION	SETTING	NUMBER	SUB-SETTING	SUB-NUMBER	SUB-SUB-SETTING	VALUE' . "\n", FILE_APPEND|LOCK_EX);
		}
		
		foreach ($data['saved'] as $key => $value) {
			if (is_array($value)) continue;
			
			$parts = explode('|', preg_replace(array('/_(\d+)_/', '/_(\d+)/'), array('|$1|', '|$1'), $key));
			
			$line = $this->name . "\t" . $parts[0] . "\t";
			for ($i = 1; $i < $this->columns - 2; $i++) {
				$line .= (isset($parts[$i]) ? $parts[$i] : '') . "\t";
			}
			$line .= str_replace(array("\t", "\n"), array('    ', '\n'), $value) . "\n";
			
			file_put_contents($filepath, $line, FILE_APPEND|LOCK_EX);
		}
		
		$data['autobackup_time'] = date('Y-M-d @ g:i a');
		$data['backup_time'] = (file_exists($manual_filepath)) ? date('Y-M-d @ g:i a', filemtime($manual_filepath)) : '';
		
		if ($backup_type == 'manual') {
			echo $data['autobackup_time'];
		}
	}
	
	public function saveSettings()
    {
		if ( ! $this->user->hasPermission('modify', $this->type . '/' . $this->name) )
		{
			$result_json['success'] = '0';
			$result_json['errors'] = array('warning' => $this->language->get('standard_error'));
			
			$this->response->setOutput(json_encode($result_json));
			
			return;
		}
		
		if ($this->request->get['saving'] == 'manual') {
			$this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE `" . (version_compare(VERSION, '2.0.1') < 0 ? 'group' : 'code') . "` = '" . $this->db->escape($this->name) . "' AND `key` != '" . $this->db->escape($this->name . '_module') . "'");
		}

		if (!isset($this->request->post['status'])) {
            $this->request->post['status'] = '0';
        }

		$modules = array();
		$this->request->post['inputs'] = json_decode(html_entity_decode($this->request->post['inputs']));

		foreach ($this->request->post['inputs'] as $input) {
			$this->request->post[$input->name]=$input->value;
		}
		unset($this->request->post['inputs']);
		
		foreach ($this->request->post as $key => $value) {
			
			if (strpos($key, 'module_') === 0) {
				$parts = explode('_', $key, 3);
				$modules[$parts[1]][$parts[2]] = $value;
			} else {
				if ($this->request->get['saving'] == 'auto') {
					$this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE `" . (version_compare(VERSION, '2.0.1') < 0 ? 'group' : 'code') . "` = '" . $this->db->escape($this->name) . "' AND `key` = '" . $this->db->escape($this->name . '_' . $key) . "'");
				}
				$this->db->query("
					INSERT INTO " . DB_PREFIX . "setting SET
					`store_id` = 0,
					`" . (version_compare(VERSION, '2.0.1') < 0 ? 'group' : 'code') . "` = '" . $this->db->escape($this->name) . "',
					`key` = '" . $this->db->escape($this->name . '_' . $key) . "',
					`value` = '" . $this->db->escape(stripslashes(is_array($value) ? implode(';', $value) : $value)) . "',
					`serialized` = 0
				");
			}
		}
		
		if (version_compare(VERSION, '2.0.1') < 0) {
			$module_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE `" . (version_compare(VERSION, '2.0.1') < 0 ? 'group' : 'code') . "` = '" . $this->db->escape($this->name) . "'AND `key` = '" . $this->db->escape($this->name . '_module') . "'");
			if ($module_query->num_rows) {
				foreach (unserialize($module_query->row['value']) as $key => $value) {
					foreach ($value as $k => $v) {
						if (!isset($modules[$key][$k])) $modules[$key][$k] = $v;
					}
				}
			}

			if (isset($modules[0])) {
				$index = 1;
				while (isset($modules[$index])) {
					$index++;
				}
				$modules[$index] = $modules[0];
				unset($modules[0]);
			}
			
			$this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE `" . (version_compare(VERSION, '2.0.1') < 0 ? 'group' : 'code') . "` = '" . $this->db->escape($this->name) . "'AND `key` = '" . $this->db->escape($this->name . '_module') . "'");
			$this->db->query("
				INSERT INTO " . DB_PREFIX . "setting SET
				`store_id` = 0,
				`" . (version_compare(VERSION, '2.0.1') < 0 ? 'group' : 'code') . "` = '" . $this->db->escape($this->name) . "',
				`key` = '" . $this->db->escape($this->name . '_module') . "',
				`value` = '" . $this->db->escape(serialize($modules)) . "',
				`serialized` = 1
			");
		} else {
			foreach ($modules as $module_id => $module) {
				$module_settings = (version_compare(VERSION, '2.1', '<')) ? serialize($module) : json_encode($module);
				if ($module_id) {
					$this->db->query("
						UPDATE " . DB_PREFIX . "module SET
						`name` = '" . $this->db->escape($module['name']) . "',
						`code` = '" . $this->db->escape($this->name) . "',
						`setting` = '" . $this->db->escape($module_settings) . "'
						WHERE module_id = " . (int)$module_id . "
					");
				} else {
					$this->db->query("
						INSERT INTO " . DB_PREFIX . "module SET
						`name` = '" . $this->db->escape($module['name']) . "',
						`code` = '" . $this->db->escape($this->name) . "',
						`setting` = '" . $this->db->escape($module_settings) . "'
					");
				}
			}
		}

		$this->load->model('setting/setting');

		$this->model_setting_setting->checkIfExtensionIsExists('shipping', 'category_product_based', true);

        
            $this->tracking->updateGuideValue('SHIPPING');

        $result_json['success'] = '1';
		
		$result_json['success_msg'] = $this->language->get('text_success');

		$this->response->setOutput(json_encode($result_json));
		
		return;
	}
	
	public function deleteSetting()
    {
		$this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE `" . (version_compare(VERSION, '2.0.1') < 0 ? 'group' : 'code') . "` = '" . $this->db->escape($this->name) . "' AND `key` = '" . $this->db->escape($this->name . '_' . str_replace('[]', '', $this->db->escape($this->request->get['setting']))) . "'");
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
		if (!$this->user->hasPermission('access', $this->type . '/' . $this->name)) {
			echo 'You do not have permission to view this file.';
			return;
		}
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
		if (!$this->user->hasPermission('access', $this->type . '/' . $this->name) || !file_exists($file)) {
			return;
		}
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
		
		if ( ! $this->user->hasPermission('modify', $this->type . '/' . $this->name) )
		{

			$result_json['errors'] = array('warning' => $this->language->get('standard_error'));
			
			$result_json['success'] = '0';

			$this->response->setOutput(json_encode($result_json));
			
			return;
		}
		
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
