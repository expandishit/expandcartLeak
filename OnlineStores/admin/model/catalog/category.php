<?php

use ExpandCart\Foundation\String\Slugify;

class ModelCatalogCategory extends Model {
	public function addCategory($data) {
		
		$this->db->execute("INSERT INTO " . DB_PREFIX . "category SET parent_id = ?, `top` = ?, `column` = ?, sort_order = ?, status = ?, date_modified = NOW(), date_added = NOW()", 
			[
				(int)$data['parent_id'],
				(isset($data['top']) ? (int)$data['top'] : 0),
				($data['column'] == null)? 0 : $data['column'],
				(int)$data['sort_order'],
				(int)$data['status']
				
			]);

		$category_id = $this->db->getLastId();

		if ( isset($data['image']) && !empty($data['image']) )
		{
			$this->db->execute("UPDATE " . DB_PREFIX . "category SET image = ? WHERE category_id = ?", 
			[
				html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8'),
				(int)$category_id
				
			]);
		}

        if ( isset($data['icon']) && !empty($data['icon']) )
        {
			$this->db->execute(
				"UPDATE " . DB_PREFIX . "category SET icon = ? WHERE category_id = ?",
				[
					html_entity_decode($data['icon'], ENT_QUOTES, 'UTF-8'),
					(int)$category_id
				]
			);
        }

        if (isset($data['ms_fee'])) {
			$fee = serialize($data['ms_fee']);
			$this->db->execute("UPDATE " . DB_PREFIX . "category SET ms_fee = ? WHERE category_id = ?",
			[
				$fee,
				(int)$category_id
			]
			
			);
		}

		if (isset($data['droplist_show'])) {
			$this->db->execute(
			"UPDATE " . DB_PREFIX . "category SET droplist_show = ? WHERE category_id = ?",
				[
					$data['droplist_show'],
					(int)$category_id
				]
			);
		}
		
		//Set the first non-empty language
		$setLanguage = [];
		//dd($data['category_description']);
		foreach ($data['category_description'] as $language_id => $value) {
				//Set the setLanguage if the current is not empty
				if($value['name']){
						$setLanguage = $value;
						break;
				}
		}

		foreach ($data['category_description'] as $language_id => $value) {
			if(!$value['name']){
					$value=$setLanguage;
			}

			$this->db->execute("INSERT INTO " . DB_PREFIX . "category_description SET category_id=?, language_id=?, name=?, slug=?, meta_keyword=?, meta_description=?, description=?",
			    [
			        (int)$category_id,
			        (int)$language_id,
			        trim($value['name']),
			        (new Slugify)->slug($value['name']),
			        $value['meta_keyword'] ?? '',
			        $value['meta_description'] ?? '',
			        html_entity_decode($value['description'], ENT_QUOTES, 'UTF-8') ]);
		}

		// MySQL Hierarchical Data Closure Table Pattern
		$level = 0;
		
		$query = $this->db->execute("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE category_id = ? ORDER BY `level` ASC",
			[
				(int)$data['parent_id'] 
			]
		);
		foreach ($query->rows as $result) {
			$this->db->execute("INSERT INTO `" . DB_PREFIX . "category_path` SET `category_id` = ?, `path_id` = ?, `level` = ?", [
				(int)$category_id ,
				(int)$result['path_id'],
				(int)$level
			]);
			$level++;
		}
		
		$this->db->execute("INSERT INTO `" . DB_PREFIX . "category_path` SET `category_id` = ?, `path_id` = ?, `level` = ?", 
		[
			(int)$category_id, 
			(int)$category_id, 
			(int)$level
			]);
		if (isset($data['category_filter'])) {
			foreach ($data['category_filter'] as $filter_id) {
				$this->db->execute(
					"INSERT INTO " . DB_PREFIX . "category_filter SET category_id = ?, filter_id = ?",
					[
						(int)$category_id,
						(int)$filter_id
					]
				);
			}
		}
				
		if (isset($data['category_store'])) {
			foreach ($data['category_store'] as $store_id) {
				$this->db->execute(
				"INSERT INTO " . DB_PREFIX . "category_to_store SET category_id = ?, store_id = ?",
				[
					(int)$category_id,
					(int)$store_id
				]
				);
			}
		}
		else{
			$this->db->execute("INSERT INTO " . DB_PREFIX . "category_to_store SET category_id = ?, store_id = ?",
			[
				(int)$category_id,
				0
			]
			);
        }
		// Set which layout to use with this category
		if (isset($data['category_layout'])) {
			foreach ($data['category_layout'] as $store_id => $layout) {
				if ($layout['layout_id']) {
					$this->db->execute("INSERT INTO " . DB_PREFIX . "category_to_layout SET category_id = ?, store_id = ?, layout_id = ?",
					[
						(int)$category_id,
						(int)$store_id,
						(int)$layout['layout_id']						
					]
					);
				}
			}
		}
						
		if ($data['keyword']) {
			$this->db->execute("INSERT INTO " . DB_PREFIX . "url_alias SET query = ?, keyword = ?", 
			[
				'category_id=' . (int)$category_id,
				$data['keyword']
			]);
		}
		
		$this->cache->delete('category');

		return $category_id;
	}
	
	public function editCategory($category_id, $data, $ajaxish=false)
	{
		$this->db->execute(
		"UPDATE " . DB_PREFIX . "category SET parent_id = ?, `top` = ?, `column` = ?, sort_order = ?, `status` = ?, date_modified = NOW() WHERE category_id = ?",
			[
				(int)$data['parent_id'],
				(isset($data['top']) ? (int)$data['top'] : 0),
				$data['column'],
				(int)$data['sort_order'],
				$data['status'],
				(int)$category_id			
			]
		);
		if ( $ajaxish === true )
		{
			return;
		}

		if (isset($data['image'])) {
			$this->db->execute("UPDATE " . DB_PREFIX . "category SET image = ? WHERE category_id = ?",
			[
				html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8'),
				(int)$category_id
			]
			);
		}

        if (isset($data['icon'])) {
			$this->db->execute("UPDATE " . DB_PREFIX . "category SET icon = ? WHERE category_id = ?", 
			[
				html_entity_decode($data['icon'], ENT_QUOTES, 'UTF-8'),
				(int)$category_id
			]);
        }


		$this->db->execute("DELETE FROM " . DB_PREFIX . "category_description WHERE category_id = ?", [(int)$category_id]);

		if (isset($data['ms_fee'])) {
			$fee = serialize($data['ms_fee']);

			$this->db->execute("UPDATE " . DB_PREFIX . "category SET ms_fee = ? WHERE category_id = ?", 
			[
				html_entity_decode($fee, ENT_QUOTES, 'UTF-8'),
				(int)$category_id
			]);
		}

		if (isset($data['droplist_show'])) {
			$this->db->execute("UPDATE " . DB_PREFIX . "category SET droplist_show = ? WHERE category_id = ?", 
			[
				html_entity_decode($data['droplist_show'], ENT_QUOTES, 'UTF-8'),
				(int)$category_id
			]);
		}

		foreach ($data['category_description'] as $language_id => $value) {
		    $this->db->execute("INSERT INTO " . DB_PREFIX . "category_description SET category_id=?, language_id=?, name=?, slug=?, meta_keyword=?, meta_description=?, description=?", 
		        [ 
		            (int)$category_id, 
		            (int)$language_id, 
		            trim($value['name']), 
		            (new Slugify)->slug($value['name']), 
		            $value['meta_keyword'] ?? '',
		            $value['meta_description'] ?? '',
		            html_entity_decode($value['description'], ENT_QUOTES, 'UTF-8') ]);
		}
		
		// MySQL Hierarchical Data Closure Table Pattern
		$query = $this->db->execute("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE path_id = ? ORDER BY level ASC", 
			[
				(int)$category_id 
			]
		);
		
		if ($query->rows) {
			foreach ($query->rows as $category_path) {
				// Delete the path below the current one
				$this->db->execute("DELETE FROM `" . DB_PREFIX . "category_path` WHERE category_id = ? AND level < ?",
					[
						(int)$category_path['category_id'],
						(int)$category_path['level']
					]
				);

				$path = array();
				
				// Get the nodes new parents
				$query = $this->db->execute("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE category_id = ? ORDER BY level ASC", 
				[
					(int)$data['parent_id']
				]);
				
				
				foreach ($query->rows as $result) {
					$path[] = $result['path_id'];
				}
				
				// Get whats left of the nodes current path
				$query = $this->db->execute("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE category_id = ? ORDER BY level ASC", 
				[
					(int)$category_path['category_id']
				]);
				
				foreach ($query->rows as $result) {
					$path[] = $result['path_id'];
				}
				
				// Combine the paths with a new level
				$level = 0;
				
				foreach ($path as $path_id) {
					$this->db->execute("REPLACE INTO `" . DB_PREFIX . "category_path` SET category_id = ?, `path_id` = ?, level = ?", 
					[
						(int)$category_path['category_id'],
						(int)$path_id,
						(int)$level
					]);
					
					$level++;
				}
			}
		} else {
			// Delete the path below the current one
			$this->db->execute("DELETE FROM `" . DB_PREFIX . "category_path` WHERE category_id = ?",
				[
				(int)$category_id
				]
			);

			// Fix for records with no paths
			$level = 0;
			
			$query = $this->db->execute("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE category_id = ? ORDER BY level ASC", [(int)$data['parent_id']]);

			foreach ($query->rows as $result) {
				$this->db->execute("INSERT INTO `" . DB_PREFIX . "category_path` SET category_id = ?, `path_id` = ?, level = ?",
					[
						(int)$category_id,
						(int)$result['path_id'],
						(int)$level
					]
				);

				$level++;
			}
			
			$this->db->execute("REPLACE INTO `" . DB_PREFIX . "category_path` SET category_id = ?, `path_id` = ?, level = ?", [
				(int)$category_id,
				(int)$category_id,
				(int)$level
			]);
		}
		
		$this->db->execute("DELETE FROM " . DB_PREFIX . "category_filter WHERE category_id = ?", [(int)$category_id]);
		
		if (isset($data['category_filter'])) {
			foreach ($data['category_filter'] as $filter_id) {
				$this->db->execute("INSERT INTO " . DB_PREFIX . "category_filter SET category_id = ?, filter_id = ?", 
				[
					(int)$category_id,
					(int)$filter_id
					
				]);
			}		
		}
		$this->db->execute("DELETE FROM " . DB_PREFIX . "category_to_store WHERE category_id = ?", [(int)$category_id]);
		
		if (isset($data['category_store'])) {		
			foreach ($data['category_store'] as $store_id) {
				$this->db->execute("INSERT INTO " . DB_PREFIX . "category_to_store SET category_id = ?, store_id = ?",
				[
					(int)$category_id,
					(int)$store_id
				]);
			}
		}
		else{
			$this->db->execute("INSERT INTO " . DB_PREFIX . "category_to_store SET category_id = ?, store_id = ?", 
				[
					(int)$category_id,
					0
				]
			);
        }
		$this->db->execute("DELETE FROM " . DB_PREFIX . "category_to_layout WHERE category_id = ?", [(int)$category_id]);

		if (isset($data['category_layout'])) {
			foreach ($data['category_layout'] as $store_id => $layout) {
				if ($layout['layout_id']) {
					$this->db->execute("INSERT INTO " . DB_PREFIX . "category_to_layout SET category_id = ?, store_id = ?, layout_id = ?", [(int)$category_id, (int)$store_id, (int)$layout['layout_id']]);
				}
			}
		}
		
		$this->db->execute("DELETE FROM " . DB_PREFIX . "url_alias WHERE query=?", ['category_id='.(int)$category_id]);
		
		if ($data['keyword']) {
			$this->db->execute("INSERT INTO " . DB_PREFIX . "url_alias SET query = ?, keyword = ?", 
			[
				'category_id='.(int)$category_id,
				$data['keyword']
				]
				);
		}
		
		$this->cache->delete('category');
	}
	

	public function deleteCategory($category_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "category_path WHERE category_id = '" . (int)$category_id . "'");
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category_path WHERE path_id = '" . (int)$category_id . "'");
			
		foreach ($query->rows as $result) {	
			$this->deleteCategory($result['category_id']);
		}
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "category WHERE category_id = '" . (int)$category_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "category_description WHERE category_id = '" . (int)$category_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "category_filter WHERE category_id = '" . (int)$category_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "category_to_store WHERE category_id = '" . (int)$category_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "category_to_layout WHERE category_id = '" . (int)$category_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE category_id = '" . (int)$category_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'category_id=" . (int)$category_id . "'");
	    
	    //El-Modaqeq App
        if( \Extension::isInstalled('elmodaqeq')) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "elmodaqeq_category WHERE expandcart_category_id  = " . (int)$category_id);
        }
		$this->cache->delete('category');
	} 
	
	// Function to repair any erroneous categories that are not in the category path table.
	public function repairCategories($parent_id = 0) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category WHERE parent_id = '" . (int)$parent_id . "'");
		
		foreach ($query->rows as $category) {
			// Delete the path below the current one
			$this->db->query("DELETE FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$category['category_id'] . "'");
			
			// Fix for records with no paths
			$level = 0;
			
			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$parent_id . "' ORDER BY level ASC");
			
			foreach ($query->rows as $result) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "category_path` SET category_id = '" . (int)$category['category_id'] . "', `path_id` = '" . (int)$result['path_id'] . "', level = '" . (int)$level . "'");
				
				$level++;
			}
			
			$this->db->query("REPLACE INTO `" . DB_PREFIX . "category_path` SET category_id = '" . (int)$category['category_id'] . "', `path_id` = '" . (int)$category['category_id'] . "', level = '" . (int)$level . "'");
						
			$this->repairCategories($category['category_id']);
		}
	}
			
	public function getCategory($category_id) {
		$query = $this->db->execute("SELECT DISTINCT *, (SELECT GROUP_CONCAT(cd1.name ORDER BY level SEPARATOR ' &gt; ') FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "category_description cd1 ON (cp.path_id = cd1.category_id AND cp.category_id != cp.path_id) WHERE cp.category_id = c.category_id AND cd1.language_id = ? GROUP BY cp.category_id) AS path, (SELECT keyword FROM " . DB_PREFIX . "url_alias WHERE query = ?) AS keyword FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd2 ON (c.category_id = cd2.category_id) WHERE c.category_id = ? AND cd2.language_id = ?",
		[
			(int)$this->config->get('config_language_id'),
			'category_id=' . (int)$category_id,
			(int)$category_id,
			(int)$this->config->get('config_language_id')
		]
		);
		
		return $query->row;
	}

	public function getCategoryByLangugaeId($category_id, $lang_id) {
		$query = $this->db->query("SELECT DISTINCT *, (SELECT GROUP_CONCAT(cd1.name ORDER BY level SEPARATOR ' &gt; ') FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "category_description cd1 ON (cp.path_id = cd1.category_id AND cp.category_id != cp.path_id) WHERE cp.category_id = c.category_id AND cd1.language_id = '" . (int)$lang_id . "' GROUP BY cp.category_id) AS path, (SELECT keyword FROM " . DB_PREFIX . "url_alias WHERE query = 'category_id=" . (int)$category_id . "') AS keyword FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd2 ON (c.category_id = cd2.category_id) WHERE c.category_id = '" . (int)$category_id . "' AND cd2.language_id = '" . (int)$lang_id . "'");
		
		return $query->row;
	}
	
	public function getCategoriesWithDesc() {
		$sql = "SELECT * FROM " . DB_PREFIX . "category";
		$categories = $this->db->query($sql);

		$fullCategories = [];
		foreach ($categories->rows as $key => $category) {
			
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category_description WHERE category_id = '" . (int)$category['category_id'] . "'");
	        foreach ($query->rows as $result) {
	            $description[$result['language_id']] = array(
	                'name' => $result['name'],
	                'description' => $result['description'],
	                'meta_keyword' => $result['meta_keyword'],
	                'meta_description' => $result['meta_description']
	            );
	        }
	        $category['category_description'] = $description;
			$fullCategories[] = $category;
		}
		
		return $fullCategories;
	}

	public function getCategories($data=[]) {
		$sql = "SELECT cp.category_id AS category_id, GROUP_CONCAT(cd1.name ORDER BY cp.level SEPARATOR ' &gt; ') AS name, c.parent_id, c.sort_order,c.image FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "category c ON (cp.path_id = c.category_id) LEFT JOIN " . DB_PREFIX . "category_description cd1 ON (c.category_id = cd1.category_id) LEFT JOIN " . DB_PREFIX . "category_description cd2 ON (cp.category_id = cd2.category_id) WHERE cd1.language_id = '" . (int)$this->config->get('config_language_id') . "' AND cd2.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		
		if (!empty($data['filter_name'])) {
			$sql .= " AND cd2.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}

		$sql .= " GROUP BY cp.category_id ORDER BY name";
		
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}				

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}	
		 
			//$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);
		
		return $query->rows;
	}


	public function getCategoriesForApi()
	{
		$sql = "SELECT cp.category_id AS category_id, GROUP_CONCAT(cd1.name ORDER BY cp.level SEPARATOR ' &gt; ') AS name, c.parent_id, c.sort_order FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "category c ON (cp.path_id = c.category_id) LEFT JOIN " . DB_PREFIX . "category_description cd1 ON (c.category_id = cd1.category_id) LEFT JOIN " . DB_PREFIX . "category_description cd2 ON (cp.category_id = cd2.category_id) WHERE cd1.language_id = '" . (int)$this->config->get('config_language_id') . "' AND cd2.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY cp.category_id ORDER BY c.sort_order";

		$query = $this->db->query($sql);

		return $query->rows;
	}

    public function getCategoriesFiltered($data) {
        $sql = "SELECT cp.category_id AS category_id, GROUP_CONCAT(cd1.name ORDER BY cp.level SEPARATOR ' &gt; ') AS name, c.parent_id, c.sort_order FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "category c ON (cp.path_id = c.category_id) LEFT JOIN " . DB_PREFIX . "category_description cd1 ON (c.category_id = cd1.category_id) LEFT JOIN " . DB_PREFIX . "category_description cd2 ON (cp.category_id = cd2.category_id) WHERE cd1.language_id = '" . (int)$this->config->get('config_language_id') . "' AND cd2.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        if (!empty($data['filter_name'])) {
            $sql .= " AND cd2.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
        }

        $sql .= " GROUP BY cp.category_id ORDER BY name";

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }
				
	public function getCategoryDescriptions($category_id) {
		$category_description_data = array();
		
		$query = $this->db->execute("SELECT * FROM " . DB_PREFIX . "category_description WHERE category_id = ?", [(int)$category_id]);
		
		foreach ($query->rows as $result) {
			$category_description_data[$result['language_id']] = array(
				'name'             => $result['name'],
				'meta_keyword'     => $result['meta_keyword'],
				'meta_description' => $result['meta_description'],
				'description'      => $result['description']
			);
		}
		
		return $category_description_data;
	}	
	
	public function getCategoryFilters($category_id) {
		$category_filter_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category_filter WHERE category_id = '" . (int)$category_id . "'");
		
		foreach ($query->rows as $result) {
			$category_filter_data[] = $result['filter_id'];
		}

		return $category_filter_data;
	}

	
	public function getCategoryStores($category_id) {
		$category_store_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category_to_store WHERE category_id = '" . (int)$category_id . "'");

		foreach ($query->rows as $result) {
			$category_store_data[] = $result['store_id'];
		}
		
		return $category_store_data;
	}

	public function getCategoryLayouts($category_id) {
		$category_layout_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category_to_layout WHERE category_id = '" . (int)$category_id . "'");
		
		foreach ($query->rows as $result) {
			$category_layout_data[$result['store_id']] = $result['layout_id'];
		}
		
		return $category_layout_data;
	}
		
	public function getTotalCategories() {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "category");
		
		return $query->row['total'];
	}	
		
	public function getTotalCategoriesByImageId($image_id) {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "category WHERE image_id = '" . (int)$image_id . "'");
		
		return $query->row['total'];
	}

	public function getTotalCategoriesByLayoutId($layout_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "category_to_layout WHERE layout_id = '" . (int)$layout_id . "'");

		return $query->row['total'];
	}

	####START: pro filter##################
	public function getCategories_MF($data) {
		if( version_compare( VERSION, '1.5.5', '>=' ) ) {
			$sql = "SELECT cp.category_id AS category_id, GROUP_CONCAT(cd1.name ORDER BY cp.level SEPARATOR ' &gt; ') AS name, c.parent_id, c.sort_order FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "category c ON (cp.path_id = c.category_id) LEFT JOIN " . DB_PREFIX . "category_description cd1 ON (c.category_id = cd1.category_id) LEFT JOIN " . DB_PREFIX . "category_description cd2 ON (cp.category_id = cd2.category_id) WHERE cd1.language_id = '" . (int)$this->config->get('config_language_id') . "' AND cd2.language_id = '" . (int)$this->config->get('config_language_id') . "'";

			if( ! empty( $data['filter_name'] ) ) {
				$sql .= " AND cd2.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
			}

			$sql .= " GROUP BY cp.category_id ORDER BY name";
		} else {
			$sql = "SELECT * FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) WHERE cd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

			if( ! empty( $data['filter_name'] ) ) {
				$sql .= " AND LOWER(cd.name) LIKE '" . $this->db->escape( function_exists( 'mb_strtolower' ) ? mb_strtolower( $data['filter_name'], 'utf-8' ) : $data['filter_name'] ) . "%'";
			}

			$sql .= " GROUP BY c.category_id ORDER BY name";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}
	####END: pro filter##################

    public function dtHandler($start=0, $length=10, $search = null, $orderColumn="name", $orderType="ASC")
    {

    	$lang_id = (int) $this->config->get('config_language_id');

    	$total = $this->db->query("SELECT * FROM " . DB_PREFIX . "category_description WHERE language_id='${lang_id}'")->num_rows;

        $fields = "cp.category_id AS category_id, c_img.status as cstatus, GROUP_CONCAT(cd1.name ORDER BY cp.level SEPARATOR ' &gt; ') AS name, c.parent_id, c.sort_order, c.status, c_img.image";
        $queryString  = [];
        $queryString[] = "SELECT {$fields} ";
        $queryString[] = "FROM " . DB_PREFIX . "category_path cp";
        $queryString[] = "LEFT JOIN " . DB_PREFIX . "category as c ON (cp.path_id = c.category_id)";
        $queryString[] = "LEFT JOIN " . DB_PREFIX . "category as c_img ON (cp.category_id = c_img.category_id)";
        $queryString[] = "LEFT JOIN " . DB_PREFIX . "category_description cd1 ON (c.category_id = cd1.category_id)";
        $queryString[] = "LEFT JOIN " . DB_PREFIX . "category_description cd2 ON (cp.category_id = cd2.category_id)";

        // $total = $this->db->query(str_replace($fields, 'count(*) as dc', implode(' ', $queryString)))->row['dc'];

        $where = "";
        $queryString[] = "WHERE cd1.language_id = '" . (int)$this->config->get('config_language_id') . "'";
        $queryString[] = "AND cd2.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        // $totalFiltered = $this->db->query(str_replace($fields, 'count(*) as dc', implode(' ', $queryString)))->row['dc'];

        if (!empty($search)) {
            $where .= "(cd2.name LIKE '%" . $this->db->escape($search) . "%')";
            $queryString[] = "AND " . $where;
        }

        $queryString[] = "GROUP BY cp.category_id";
        $queryString[] = " ORDER by {$orderColumn} {$orderType}";
        $totalFiltered = $this->db->query(implode(' ', $queryString))->num_rows;

        if($length != -1) {
            $queryString[] = " LIMIT " . $start . ", " . $length;
		}

		
		$allCategories = $this->db->query(implode(' ', $queryString))->rows;
		$allCategories = array_map(function($ctgry) {
//			$ctgry['product_count'] = $this->db->query("SELECT count(*) as count FROM  " . DB_PREFIX . "product_to_category where category_id=".$ctgry['category_id'])->row['count'];
			$ctgry['product_count'] = $this->db->query("select count(*) as count from " . DB_PREFIX . "product p left join " . DB_PREFIX . "product_to_category ptc on p.product_id=ptc.product_id where p.archived = 0 and ptc.category_id=".$ctgry['category_id'])->row['count'];
			$ctgry['active_product_count'] = $this->db->query("select count(*) as count from " . DB_PREFIX . "product p left join " . DB_PREFIX . "product_to_category ptc on p.product_id=ptc.product_id where p.archived = 0 and p.status=1 and date_available <= date(now()) and ptc.category_id=".$ctgry['category_id'])->row['count'];
			return $ctgry;
		}, $allCategories);

		$data = array (
            'data' => $allCategories,
            'total' => $total,
            'totalFiltered' => $totalFiltered
        );

        return $data;
    }

    //Store Dropna Ids
    public function addDropnaCategory($data){
        $this->db->query("INSERT INTO " . DB_PREFIX . "category_to_dropna SET category_id = '" . (int)$data['category_id'] . "', dropna_category_id = '" . (int)$data['dropna_category_id'] . "'");
    }


	public function updateMainCategory($main_category_id,$cats_ids)
	{
		# remove selected category is the same as main category.
		if (($key = array_search($main_category_id, $cats_ids)) !== false) {
			unset($cats_ids[$key]);
		}
		
		$str_sql = [];
		$str_sql[] = "DELETE from category_path";
		$str_sql[] = "WHERE category_id in (".implode(",",$cats_ids).")";

		$this->db->query(implode(" ",$str_sql));

		$str_sql = [];
		$str_sql[] = "UPDATE category set parent_id = $main_category_id";
		$str_sql[] = "WHERE category_id in (".implode(",",$cats_ids).")";

		
		$this->db->query(implode(" ",$str_sql));
		
		$count = count($cats_ids);

		$insert_str = "";
		foreach($cats_ids as $cat_id){
			$insert_str .= "(";
			$insert_str .="$cat_id,$main_category_id,0";
			$insert_str .="),";
			$insert_str .= "(";
			$insert_str .="$cat_id,$cat_id,1";
			$insert_str .=")";

			if(--$count > 0)
				$insert_str .=",";
		}

		$str_sql = [];
		$str_sql[] = "INSERT category_path VALUES $insert_str";
		
		$this->db->query(implode(" ",$str_sql));
		
	}

	 /**
     * get all category description
     *
     */
    public function getCategoriesDescription()
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category_description");

        return $query->rows;
    }

    /**
     * Update slug
     *
     */
    public function slugUpdate($category_id, $lang_id, $name)
    {
        $this->db->query(
                "UPDATE ".DB_PREFIX."category_description SET slug = '".$this->db->escape((new Slugify)->slug($name))."' WHERE category_id = '".(int)$category_id."' AND language_id='".(int)$lang_id."'");
    }

	/**
	 * get max child level
	 *
	 */
	public function getMaxChildLevel()
	{
		return $this->db->query("SELECT MAX(level) as level FROM category_path")->row['level'] ?? 0;
	}

	public function getCategoryNameById($category_id , $language_id){
		$query = $this->db->query("SELECT name FROM " . DB_PREFIX . "category_description  
		 WHERE category_id =".$category_id." AND language_id = ".$language_id);
		return $query->row['name'];
	}

	public function getMainCategories($data) {
		$sqlQry = "SELECT * FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE c.parent_id = '0' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') ."'";

		if (!empty($data['filter_name'])) {
			$sqlQry .= " AND cd2.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}

		if(isset($data['status'])){
			$sqlQry .= " AND c.status=" . $data['status'];
		}

		if(isset($data['sort'])){
			$sqlQry .= " ORDER BY " . $data['sort'];
		}
		else{
			$sqlQry .= " ORDER BY cd.name";
		}
		
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}				

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}	
		 
			$sqlQry .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

	

		$query = $this->db->query($sqlQry);

		return $query->rows;
	}

}
?>
