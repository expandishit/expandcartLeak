<?php

/**
 * Mega Filter
 * 
 * @author marsilea15 <marsilea15@gmail.com> 
 */
class ControllerModuleMegaFilter extends Controller {
	
	/**
	 * Lista błędów
	 *
	 * @var array
	 */
	private $error			= array();
	
	private $_name			= 'mega_filter';
	
	private $_version		= '1.3.0.8';
	
	private $_hasFilters	= NULL;
	
	private $_cache_dir;
	
	private $_stores_list	= NULL;
	
	private static $_tmp_sort_parameters = NULL;
	
	private $_mijoshop_update = array(
		'../../mijoshop/opencart.php' => array(
			'foreach ($modules as $module) {' => array(
				'$idx=0;',
				'$idxs=array();',
				'foreach( $modules as $k => $v ) {$idxs[] = $k;}',
				'foreach ($modules as $module) {',
				'if( ! isset( $module[\'layout_id\'] ) ) { $module[\'layout_id\'] = 0; }',
				'if( ! isset( $module[\'position\'] ) ) { $module[\'position\'] = \'\'; }',
				'if( ! isset( $module[\'status\'] ) ) { $module[\'status\'] = \'0\'; }',
				'if( ! isset( $module[\'sort_order\'] ) ) { $module[\'sort_order\'] = 0; }',
				'if( ! is_array( $module[\'layout_id\'] ) ) { $module[\'layout_id\'] = array( $module[\'layout_id\'] ); }',
				'$module[\'_idx\'] = $idxs[$idx++];'
			),
			'$module[\'layout_id\'] == $layout_id' => array( 
				'( in_array( $layout_id, $module[\'layout_id\'] ) || in_array( \'-1\', $module[\'layout_id\'] ) )'
			),
			'$args[\'setting\'] = $module;' => array(
				'if( $module_name != \'mega_filter\' ) {',
					'unset( $module[\'_idx\'] );',
					'$module[\'layout_id\'] = current( $module[\'layout_id\'] );',
				'}',
				'$args[\'setting\'] = $module;',
			)
		)
	);
	
	private $_aceshop_update = array(
		'../../aceshop/opencart.php' => array(
			'foreach ($modules as $module) {' => array(
				'$idx=0;',
				'$idxs=array();',
				'foreach( $modules as $k => $v ) {$idxs[] = $k;}',
				'foreach ($modules as $module) {',
				'if( ! isset( $module[\'layout_id\'] ) ) { $module[\'layout_id\'] = 0; }',
				'if( ! isset( $module[\'position\'] ) ) { $module[\'position\'] = \'\'; }',
				'if( ! isset( $module[\'status\'] ) ) { $module[\'status\'] = \'0\'; }',
				'if( ! isset( $module[\'sort_order\'] ) ) { $module[\'sort_order\'] = 0; }',
				'if( ! is_array( $module[\'layout_id\'] ) ) { $module[\'layout_id\'] = array( $module[\'layout_id\'] ); }',
				'$module[\'_idx\'] = $idxs[$idx++];'
			),
			'$module[\'layout_id\'] == $layout_id' => array( 
				'( in_array( $layout_id, $module[\'layout_id\'] ) || in_array( \'-1\', $module[\'layout_id\'] ) )'
			),
			'$args[\'setting\'] = $module;' => array(
				'if( $module_name != \'mega_filter\' ) {',
					'unset( $module[\'_idx\'] );',
					'$module[\'layout_id\'] = current( $module[\'layout_id\'] );',
				'}',
				'$args[\'setting\'] = $module;',
			)
		)
	);
	
	//private static $_tmp_sort_parameters = NULL;
	
	public function __construct($registry) {
		parent::__construct($registry);
		
		$this->_cache_dir = DIR_CACHE . 'cache_mfp';
	}
	
	private function hasFilters() {
		if( $this->_hasFilters === NULL ) {
			$this->_hasFilters = version_compare( VERSION, '1.5.5', '>=' );
		}
		
		return $this->_hasFilters;
	}
	
	private function _cacheWritable() {
		if( ! is_dir( DIR_CACHE . 'cache_mfp' ) ) {
			@mkdir(DIR_CACHE . 'cache_mfp/');
		}
		return ! is_writable( $this->_cache_dir ) ? false : true;
	}

	/**
	 * Inicjuj
	 */
	private function _init( $tab ) {
		// załaduj język
		$this->data = array_merge($this->data, $this->language->load('module/' . $this->_name));
		
		// aktywna zakładka
		$this->data['tab_active'] = $tab;
		
		// linki zakładek
		$this->data['tab_layout_link']		= $this->url->link('module/' . $this->_name, '', 'SSL');
		$this->data['tab_attributes_link']	= $this->url->link('module/' . $this->_name . '/attributes', '', 'SSL');
		$this->data['tab_options_link']		= $this->url->link('module/' . $this->_name . '/options', '', 'SSL');
		if( $this->hasFilters() ) {
			$this->data['tab_filters_link']		= $this->url->link('module/' . $this->_name . '/filters', '', 'SSL');
		}
		$this->data['tab_settings_link']	= $this->url->link('module/' . $this->_name . '/settings', '', 'SSL');
		$this->data['tab_about_link']		= $this->url->link('module/' . $this->_name . '/about', '', 'SSL');
		
		// linki
		$this->data['action']	= $this->url->link('module/' . $this->_name, '', 'SSL');
		$this->data['back']		= $this->url->link('marketplace/home', '', 'SSL');
		$this->data['_name']	= $this->_name;
		
		// okruszki
		$this->data['breadcrumbs'] = array();
		
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', '', 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_module'),
			'href'      => $this->url->link('marketplace/home', '', 'SSL'),
      		'separator' => ' :: '
   		);
		
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('module/' . $this->_name, '', 'SSL'),
      		'separator' => ' :: '
   		);
		
		// tytuł
		$this->document->setTitle($this->language->get('heading_title'));
		
		// ustawienia szablonu
		if($this->request->get['layout_id'])
			$this->template = 'module/' . $this->_name . '_form' . '.expand';
		else
			$this->template = 'module/' . $this->_name . ( $tab != $this->_name ? '_' . $tab : '' ) . '.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->_messages();
		
		$curr_ver = $this->config->get('mfilter_version');
		
		// instalacja/aktualizacja
		if( ! $curr_ver || version_compare( $curr_ver, $this->_version, '<' ) || $this->_isOldMFilterPlus() ) {			
			$this->load->model('setting/setting');
			$this->load->model('setting/store');
			
			$stores = array(0);
			
			foreach( $this->model_setting_store->getStores() as $row ) {
				$stores[] = $row['store_id'];
			}
			
			// if update
			if( $curr_ver ) {
				// @since 1.2.0
				if( version_compare( $curr_ver, '1.2.0', '<' ) ) {
					$this->db->query( "
						UPDATE
							" . DB_PREFIX . "setting
						SET
							`group` = CONCAT( `group`, '_', '" . (int) $this->config->get( 'config_language_id' ) . "' ),
							`key` = CONCAT( `key`, '_', '" . (int) $this->config->get( 'config_language_id' ) . "' )
						WHERE
							`key` REGEXP '^mega_filter_at_img_[0-9]+$' OR `key` REGEXP '^mega_filter_at_sort_[0-9]+$'
					" );
				}

				// @since 1.2.2
				if( version_compare( $curr_ver, '1.2.2', '<' ) ) {
					$settings = $this->config->get( $this->_name . '_settings' );
					$settings['show_loader_over_results'] = '1';

					foreach( $stores as $store_id ) {
						$this->model_setting_setting->editSetting($this->_name . '_settings', array(
							$this->_name . '_settings' => $settings
						), $store_id);
					}
				}

				// @since 1.2.7
				if( version_compare( $curr_ver, '1.2.7', '<' ) ) {
					// pliki JavaScript zostały przeniesione do innego katalogu - poniższe nie są już potrzebne
					if( file_exists( DIR_APPLICATION . 'view/javascript/mf/colorpicker.js' ) && is_writable( DIR_APPLICATION . 'view/javascript/mf/colorpicker.js' ) ) {
						ob_start();
						@ unlink( DIR_APPLICATION . 'view/javascript/mf/colorpicker.js' );
						ob_end_clean();
					}

					if( file_exists( DIR_APPLICATION . 'view/javascript/mf/remaining.js' ) && is_writable( DIR_APPLICATION . 'view/javascript/mf/remaining.js' ) ) {
						ob_start();
						@ unlink( DIR_APPLICATION . 'view/javascript/mf/remaining.js' );
						ob_end_clean();
					}

					if( file_exists( DIR_APPLICATION . 'view/javascript/mf' ) && is_writable( DIR_APPLICATION . 'view/javascript/mf' ) ) {
						ob_start();
						@ rmdir( DIR_APPLICATION . 'view/javascript/mf' );
						ob_end_clean();
					}
				}

				// @since 1.2.8
				if( version_compare( $curr_ver, '1.2.8', '<' ) ) {
					// plik nie jest już potrzebny
					if( file_exists( DIR_APPLICATION . 'view/stylesheet/mf/js/remaining.js' ) && is_writable( DIR_APPLICATION . 'view/stylesheet/mf/js/remaining.js' ) ) {
						ob_start();
						@ unlink( DIR_APPLICATION . 'view/stylesheet/mf/js/remaining.js' );
						ob_end_clean();
					}
				}
				
				// @since 1.2.8.9.1
				// w tej wersji zmieniła się ścieżka do folderu Cache - sprawdź czy PHP ma prawa zapisu w tym folderze
				if( version_compare( $curr_ver, '1.2.8.9.1', '<' ) ) {
					$settings = $this->config->get( $this->_name . '_settings' );
					
					// jeśli nie ma praw zapisu wyłącz cache
					if( ! empty( $settings['cache_enabled'] ) && ! $this->_cacheWritable() ) {
						$settings['cache_enabled'] = '0';

						foreach( $stores as $store_id ) {
							$this->model_setting_setting->editSetting($this->_name . '_settings', array(
								$this->_name . '_settings' => $settings
							), $store_id);
						}

						$this->session->data['error'] = $this->language->get('error_cache_dir');
					}
				}
				
				// @since 1.2.8.9.7
				// w tej wersji ze względu na problemy w IE zmieniamy domyślny selektor JS z '#content' na '#mfilter-content-container'
				if( version_compare( $curr_ver, '1.2.8.9.7', '<' ) ) {
					$settings = $this->config->get( $this->_name . '_settings' );
					
					if( empty( $settings['content_selector'] ) || $settings['content_selector'] == '#content' ) {
						$settings['content_selector'] = '#mfilter-content-container';

						foreach( $stores as $store_id ) {
							$this->model_setting_setting->editSetting($this->_name . '_settings', array(
								$this->_name . '_settings' => $settings
							), $store_id);
						}
					}
				}
				
				// @since 1.2.8.9.8
				// w tej wersji rozdzielono wyświetlanie ilości elementów z obliczaniem tej ilości
				if( version_compare( $curr_ver, '1.2.8.9.8', '<' ) ) {
					$settings = $this->config->get( $this->_name . '_settings' );
					
					if( ! empty( $settings['show_number_of_products'] ) ) {
						$settings['calculate_number_of_products'] = '1';

						foreach( $stores as $store_id ) {
							$this->model_setting_setting->editSetting($this->_name . '_settings', array(
								$this->_name . '_settings' => $settings
							), $store_id);
						}
					}
				}
				
				// @since 1.2.9.2
				if( $this->_writableCss() ) {
					$settings = $this->config->get( $this->_name . '_settings' );
						
					$this->_updateCss( $settings );
				}
				
				// @since 1.2.9.8.9
				if( version_compare( $curr_ver, '1.2.9.8.9', '<' ) ) {
					foreach( $this->db->query( "SELECT * FROM " . DB_PREFIX . "setting WHERE `key` = 'mega_filter_module'" )->rows as $row ) {
						$values = unserialize( $row['value'] );
						$updated = false;
						
						foreach( $values as $k => $v ) {						
							if( isset( $v['inline_horizontal'] ) ) {
								$values[$k]['display_options_as'] = 'inline_horizontal';

								unset( $values[$k]['inline_horizontal'] );
								
								$updated = true;
							}
						}

						if( $updated ) {
							$this->db->query( "
								UPDATE 
									`" . DB_PREFIX . "setting` 
								SET
									`value` = '" . $this->db->escape( serialize( $values ) ) . "'
								WHERE 
									`setting_id` = " . $row['setting_id']
							);
						}
					}
				}
			}
			
			////////////////////////////////////////////////////////////////////
			
			// Change column 'value' in table 'setting' from 'text' to 'longtext'
			$query = $this->db->query( "
				SELECT 
					* 
				FROM 
					INFORMATION_SCHEMA.COLUMNS 
				WHERE 
					TABLE_SCHEMA LIKE '" . $this->db->escape( DB_DATABASE ) . "' AND
					TABLE_NAME LIKE '" . $this->db->escape( DB_PREFIX . 'setting' ) . "' AND 
					COLUMN_NAME LIKE 'value'
			");
			
			if( $query->num_rows && isset( $query->row['COLUMN_TYPE'] ) && strtolower( $query->row['COLUMN_TYPE'] ) != 'longtext' ) {
				$sql = array();
				$sql[] = 'ALTER TABLE `' . DB_PREFIX . 'setting` CHANGE `value` `value` LONGTEXT';
				
				if( ! empty( $query->row['CHARACTER_SET_NAME'] ) ) {
					$sql[] = 'CHARACTER SET ' . $query->row['CHARACTER_SET_NAME'];
				}
				
				if( ! empty( $query->row['COLLATION_NAME'] ) ) {
					$sql[] = 'COLLATE ' . $query->row['COLLATION_NAME'];
				}
				
				if( ! empty( $query->row['IS_NULLABLE'] ) && strtolower( $query->row['IS_NULLABLE'] ) == 'no' ) {
					$sql[] = 'NOT';
				}
				
				$sql[] = 'NULL';
				
				$this->db->query( implode( ' ', $sql ) );
			}
			
			// dodatkowe szablony //////////////////////////////////////////////
			
			$add_routes = array(
				'Mega Filter PRO' => 'module/mega_filter/results',
				'Manufacturer info' => 'product/manufacturer/info'
			);
			
			foreach( $add_routes as $name => $route ) {
				if( ! $this->db->query( "SELECT COUNT(*) AS c FROM " . DB_PREFIX . "layout_route WHERE route LIKE '" . $this->db->escape( $route ) . "'")->row['c'] ) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "layout SET name='" . $this->db->escape( $name ) . "'");
					$layout_id = $this->db->getLastId();

					foreach( $stores as $store_id ) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "layout_route SET layout_id='" . $layout_id . "', store_id='" . $store_id . "', route='" . $this->db->escape( $route ) . "'");
					}
				}
			}
			
			////////////////////////////////////////////////////////////////////
			
			foreach( $stores as $store_id ) {
				$this->model_setting_setting->editSetting('mfilter_version', array(
					'mfilter_version' => $this->_version
				), $store_id);
			}
			
			if( $tab != 'installprogress' ) {
				$this->_installMFilterPlus();
			}
			
			if( $curr_ver ) {
				$this->session->data['success'] = $this->language->get('success_updated');
			
				$this->redirect($this->url->link('module/' . $this->_name . '/about', '', 'SSL'));
			}
		} else if( ! file_exists( DIR_CATALOG . 'view/theme/default/template/module/mega_filter.tpl' ) ) {
			$this->_setErrors(array(
				'warning' => $this->language->get( 'error_missing_template_file' )
			));
		}
		
		if( is_readable( __FILE__ ) ) {
			// sprawdź czy użytkownik skopiował plik szablonu
			$files = glob( DIR_CATALOG . 'view/theme/*/template/module/mega_filter.tpl' );
			$source = filemtime( DIR_CATALOG . 'view/theme/default/template/module/mega_filter.tpl' );
			
			foreach( $files as $id => $file ) {
				$file = realpath( $file );
				$parts = explode( DIRECTORY_SEPARATOR, $file );
				
				array_pop( $parts ); // nazwa pliku
				array_pop( $parts ); // katalog 'module'
				array_pop( $parts ); // katalog 'template'
				
				$theme = array_pop( $parts );
				
				if( $theme == 'default' || ! is_readable( $file ) ) {
					unset( $files[$id] );
				} else {
					$time = filemtime( $file );
					
					if( $source - $time > 60 * 10 ) {
						$files[$id] = '<span style="margin-left:15px; display: inline-block;"> - /view/theme/<b>' . $theme . '</b>/template/module/mega_filter.tpl</span>';
					} else {
						unset( $files[$id] );
					}
				}
			}
			
			if( $files ) {
				$this->_setErrors(array(
					'warning' => sprintf( $this->language->get( 'error_upgrade_template_file' ), implode( '<br>', $files ) )
				));
			}
		}
		
		if( class_exists( 'AceShop' ) && version_compare( $this->config->get('mfilter_aceshop'), $curr_ver, '<' ) ) {
			$warnings = array();
			
			foreach( $this->_aceshop_update as $file => $changes ) {
				$file = realpath( DIR_SYSTEM . $file );
				
				if( file_exists( $file ) && is_readable( $file ) ) {
					$tmp = NULL;
					
					if( file_exists( $file . '_backup_mf' ) ) {
						if( is_readable( $file . '_backup_mf' ) ) {
							$tmp = file_get_contents( $file . '_backup_mf' );
						} else {
							$warnings[] = sprintf( 'No permission to read the file "%s"', $file . '_backup_mf' );
						}
					} else {
						$tmp = file_get_contents( $file );
					}
					
					if( $tmp !== NULL ) {
						foreach( $changes as $search => $replace ) {
							$replace = implode( "\n", $replace );

							if( mb_strpos( $tmp, $search, 0, 'utf-8' ) !== false ) {
								$tmp = str_replace( $search, $replace, $tmp );
							} else if( mb_strpos( $tmp, $replace, 0, 'utf-8' ) === false ) {
								$warnings[] = sprintf( 'In the file "%s" not found string "%s"', $file, $search );
							}
						}
					}
					
					if( ! $warnings ) {
						if( ! is_writable( dirname( $file ) ) ) {
							$warnings[] = sprintf( 'No permission to create a copy of the file "%s" in directory "%s"', $file, dirname( $file ) );
						} else if( ! is_writable( $file ) ) {
							$warnings[] = sprintf( 'No permission to modify the file "%s"', $file );
						} else if( $tmp !== NULL ) {
							if( ! file_exists( $file . '_backup_mf' ) ) {
								copy( $file, $file . '_backup_mf' );
							}
							
							file_put_contents( $file, $tmp );
						}
					}
				}
			}
			
			if( empty( $warnings ) ) {
				$this->_saveSettings('mfilter_aceshop', array(
					'mfilter_aceshop' => $this->_version
				));
			} else {
				$warnings[] = 'You need to manually find and replace the following strings:';
				$warnings[] = '';
				
				foreach( $this->_aceshop_update as $file => $changes ) {
					$file = realpath( DIR_SYSTEM . $file );
					
					foreach( $changes as $search => $replace ) {
						$warnings[] = sprintf( 'String: <pre>%s</pre> replace to: <pre>%s</pre> in file <b>%s</b>', $search, implode( "\n", $replace ), $file );
						$warnings[] = '';
					}
				}
				
				$warnings[] = sprintf( 'Remember to make backup your files! <a href="%s">Click here when you done</a>', $this->url->link('module/' . $this->_name . '/aceshop_manually', '', 'SSL') );
				
				$this->_setErrors(array(
					'warning' => implode( '<br />', $warnings )
				));
			}
		}
		
		if( class_exists( 'MijoShop' ) && version_compare( $this->config->get('mfilter_mijoshop'), $curr_ver, '<' ) ) {
			$warnings = array();
			
			foreach( $this->_mijoshop_update as $file => $changes ) {
				$file = realpath( DIR_SYSTEM . $file );
				
				if( file_exists( $file ) && is_readable( $file ) ) {
					$tmp = NULL;
					
					if( file_exists( $file . '_backup_mf' ) ) {
						if( is_readable( $file . '_backup_mf' ) ) {
							$tmp = file_get_contents( $file . '_backup_mf' );
						} else {
							$warnings[] = sprintf( 'No permission to read the file "%s"', $file . '_backup_mf' );
						}
					} else {
						$tmp = file_get_contents( $file );
					}
					
					if( $tmp !== NULL ) {
						foreach( $changes as $search => $replace ) {
							$replace = implode( "\n", $replace );

							if( mb_strpos( $tmp, $search, 0, 'utf-8' ) !== false ) {
								$tmp = str_replace( $search, $replace, $tmp );
							} else if( mb_strpos( $tmp, $replace, 0, 'utf-8' ) === false ) {
								$warnings[] = sprintf( 'In the file "%s" not found string "%s"', $file, $search );
							}
						}
					}
					
					if( ! $warnings ) {
						if( ! is_writable( dirname( $file ) ) ) {
							$warnings[] = sprintf( 'No permission to create a copy of the file "%s" in directory "%s"', $file, dirname( $file ) );
						} else if( ! is_writable( $file ) ) {
							$warnings[] = sprintf( 'No permission to modify the file "%s"', $file );
						} else if( $tmp !== NULL ) {
							if( ! file_exists( $file . '_backup_mf' ) ) {
								copy( $file, $file . '_backup_mf' );
							}
							
							file_put_contents( $file, $tmp );
						}
					}
				}
			}
			
			if( empty( $warnings ) ) {
				$this->_saveSettings('mfilter_mijoshop', array(
					'mfilter_mijoshop' => $this->_version
				));
			} else {
				$warnings[] = 'You need to manually find and replace the following strings:';
				$warnings[] = '';
				
				foreach( $this->_mijoshop_update as $file => $changes ) {
					$file = realpath( DIR_SYSTEM . $file );
					
					foreach( $changes as $search => $replace ) {
						$warnings[] = sprintf( 'String: <pre>%s</pre> replace to: <pre>%s</pre> in file <b>%s</b>', $search, implode( "\n", $replace ), $file );
						$warnings[] = '';
					}
				}
				
				$warnings[] = sprintf( 'Remember to make backup your files! <a href="%s">Click here when you done</a>', $this->url->link('module/' . $this->_name . '/mijoshop_manually', '', 'SSL') );
				
				$this->_setErrors(array(
					'warning' => implode( '<br />', $warnings )
				));
			}
		}
	}
	
	public function mijoshop_manually() {
		$this->_saveSettings('mfilter_mijoshop', array(
			'mfilter_mijoshop' => $this->_version
		));
		
		$this->redirect($this->url->link('module/' . $this->_name, '', 'SSL'));
	}
	
	public function aceshop_manually() {
		$this->_saveSettings('mfilter_aceshop', array(
			'mfilter_aceshop' => $this->_version
		));
		
		$this->redirect($this->url->link('module/' . $this->_name, '', 'SSL'));
	}
	
	private function _saveSettings( $group, $data ) {
		if( $this->_stores_list === NULL ) {
			$this->load->model('setting/store');

			$this->_stores_list = array(0);

			foreach( $this->model_setting_store->getStores() as $row ) {
				$this->_stores_list[] = $row['store_id'];
			}
		}
		
		$this->load->model('setting/setting');
		
		foreach( $this->_stores_list as $store_id ) {
			$this->model_setting_setting->editSetting($group, $data, $store_id);
		}
	}
	
	private function _hasMFilterPlus() {
		if( ! file_exists( DIR_SYSTEM . 'library/mfilter_plus.php' ) ) {
			return false;
		}
		
		return true;
	}
	
	private function _isOldMFilterPlus() {		
		if( ! $this->_hasMFilterPlus() ) {
			return false;
		}
		
		$curr_ver = $this->config->get('mfilter_plus_version');		
		$this->load->library('mfilter_plus');
		
		return version_compare( $curr_ver, Mfilter_Plus::getInstance( $this )->version(), '<' );
	}
	
	private function _messages() {		
		// powiadomienia
		if( isset( $this->session->data['success'] ) ) {
			$this->data['_success'] = $this->session->data['success'];
			
			unset( $this->session->data['success'] );
		}
		
		if( isset( $this->session->data['error'] ) ) {
			$this->_setErrors(array(
				'warning' => $this->session->data['error']
			));
			
			unset( $this->session->data['error'] );
		}
	}
	
	public function get_data() {
		$json = array();
		
		if( isset( $this->request->get['mf_id'] ) ) {
			$json = $this->config->get( $this->_name . '_module' );
			$json = isset( $json[$this->request->get['mf_id']] ) ? $json[$this->request->get['mf_id']] : array();
		}
		
		echo '<div id="json-encode">' . base64_encode( json_encode( $json ) ) . '</div>';
		
		die();
	}
	
	private function _remove_data( $id ) {
		$data = $this->config->get( $this->_name . '_module' );
			
		if( isset( $data[$id] ) ) {
			unset( $data[$id] );
		}
			
		$this->load->model('setting/setting');
		$this->model_setting_setting->editSetting($this->_name . '_module', array( $this->_name . '_module' => $data ));
	}
	
	public function remove_data() {
		if( isset( $this->request->get['mf_id'] ) && $this->checkPermission() ) {
			$this->_remove_data( $this->request->get['mf_id'] );
		}
		
		die();
	}
	
	private function _save( & $data, & $store ) {
		foreach( $data as $k => $v ) {
			if( is_array( $v ) ) {
				$tmp = isset( $store[$k] ) ? $store[$k] : array();
				
				$this->_save( $v, $tmp );
				
				$store[$k] = $tmp;
			//} else if( isset( $store[$k] ) && is_array( $store[$k] ) ) {
			//	array_push( $store, $v );
			} else {
				$store[$k] = $v;
			}
		}
	}
	
	public function save_data() {
		if( $this->request->server['REQUEST_METHOD'] == 'POST' && $this->checkPermission() ) {
			$idx	= (int) $this->request->get['mf_idx'];
			$count	= (int) $this->request->get['mf_count'];
			$pager	= (bool) $this->request->get['mf_pager'];
			$mf_id	= (int) $this->request->get['mf_id'];
			
			if( ! $idx || ! isset( $this->session->data['mf_data_to_save'] ) ) {
				$settings	= $this->config->get($this->_name . '_settings' );
				
				$this->session->data['mf_data_to_save'] = array(
					'attribs'		=> (array) $this->config->get( $this->_name . '_attribs' ),
					'options'		=> (array) $this->config->get( $this->_name . '_options' ),
					'base_attribs'	=> (array) $settings['attribs'],
				);
				
				if( $this->hasFilters() ) {
					$this->session->data['mf_data_to_save']['filters'] = (array) $this->config->get( $this->_name . '_filters' );
				}
				
				if( NULL != ( $module = $this->config->get($this->_name . '_module') ) ) {
					if( isset( $module[$mf_id]['attribs'] ) ) {
						$this->session->data['mf_data_to_save']['attribs'] = $module[$mf_id]['attribs'];
					}
					
					if( isset( $module[$mf_id]['options'] ) ) {
						$this->session->data['mf_data_to_save']['options'] = $module[$mf_id]['options'];
					}
					
					if( isset( $module[$mf_id]['filters'] ) ) {
						$this->session->data['mf_data_to_save']['filters'] = $module[$mf_id]['filters'];
					}
					
					if( isset( $module[$mf_id]['base_attribs'] ) ) {
						$this->session->data['mf_data_to_save']['base_attribs'] = $module[$mf_id]['base_attribs'];
					}
				}
			}
			
			$this->_save( $this->request->post[$this->_name.'_module'], $this->session->data['mf_data_to_save'] );
			
			if( ! $pager && $idx == $count ) {				
				$settings = $this->config->get( $this->_name . '_settings' );
				
				if( empty( $this->session->data['mf_data_to_save']['layout_id'] ) ) {
					$this->session->data['mf_data_to_save']['layout_id'] = array();
				}
				
				if( ! in_array( $settings['layout_c'], $this->session->data['mf_data_to_save']['layout_id'] ) ) {
					$this->session->data['mf_data_to_save']['category_id'] = array();
				}
			
				if( empty( $this->session->data['mf_data_to_save']['store_id'] ) ) {
					$this->session->data['mf_data_to_save']['store_id'] = array( 0 );
				}
				
				if( NULL == ( $data = $this->config->get($this->_name . '_module') ) ) {
					$data = array();
				}
				
				$data[$mf_id] = $this->session->data['mf_data_to_save'];
				
				$byStores = array();
				
				foreach( $data as $v ) {
					if( empty( $v['store_id'] ) ) {
						$v['store_id'] = array( 0 );
					}
					
					foreach( $v['store_id'] as $vv ) {
						$byStores[$vv] = $vv;
					}
				}			
				
				$this->load->model('setting/setting');
				
				foreach( $byStores as $store_id ) {
					$this->model_setting_setting->editSetting(
						$this->_name . '_module', 
						array( $this->_name . '_module' => $data ),
						$store_id
					);
				}	
				
				unset( $this->session->data['mf_data_to_save'] );
				
				if( $this->_cacheWritable() && NULL != ( $files = glob($this->_cache_dir . '/idx.' . $mf_id . '.*') ) ) {
					foreach( $files as $file ) {
						if( strlen( basename( $file ) ) > 32 ) {
							@ unlink( $file );
						}
					}
				}
			}
		}
		
		die();
	}

	function searchForAttributeId($id, $array) {
		foreach ($array as $key => $val) {
			if ($val['attribute_id'] === $id) {
				return $key;
			}
		}
		return -1;
	 }
	
	/**
	 * Ustawienia szablonów
	 */
	public function index() {
        $this->load->model('marketplace/common');
        $market_appservice_status = $this->model_marketplace_common->hasApp('mega_filter');
        if (!$market_appservice_status['hasapp']) {
            $this->redirect($this->url->link('marketplace/app', 'id=' . $market_appservice_status['appserviceid'], 'SSL'));
            return;
        }

		$this->_init( $this->_name );
		
		// załaduj modele
		$this->load->model('design/layout');
		$this->load->model('localisation/language');
		$this->load->model('design/layout');
		$this->load->model('catalog/category');
		$this->load->model('setting/store');
		
		$settings = $this->config->get( $this->_name . '_settings' );
       		
		if( $this->request->server['REQUEST_METHOD'] == 'POST' && $this->checkPermission() ) {

			$idx	= (int) $this->request->get['mf_idx'];
			$count	= (int) $this->request->get['mf_count'];
			$pager	= (bool) $this->request->get['mf_pager'];
			$mf_id	= (int) $this->request->get['mf_id'];
			
			if( ! $idx || ! isset( $this->session->data['mf_data_to_save'] ) ) {
				$settings	= $this->config->get($this->_name . '_settings' );
				
				$this->session->data['mf_data_to_save'] = array(
					'attribs'		=> (array) $this->config->get( $this->_name . '_attribs' ),
					'options'		=> (array) $this->config->get( $this->_name . '_options' ),
					'base_attribs'	=> (array) $settings['attribs'],
				);
				
				if( $this->hasFilters() ) {
					$this->session->data['mf_data_to_save']['filters'] = (array) $this->config->get( $this->_name . '_filters' );
				}
				
				if( NULL != ( $module = $this->config->get($this->_name . '_module') ) ) {
					if( isset( $module[$mf_id]['attribs'] ) ) {
						$this->session->data['mf_data_to_save']['attribs'] = $module[$mf_id]['attribs'];
					}
					
					if( isset( $module[$mf_id]['options'] ) ) {
						$this->session->data['mf_data_to_save']['options'] = $module[$mf_id]['options'];
					}
					
					if( isset( $module[$mf_id]['filters'] ) ) {
						$this->session->data['mf_data_to_save']['filters'] = $module[$mf_id]['filters'];
					}
					
					if( isset( $module[$mf_id]['base_attribs'] ) ) {
						$this->session->data['mf_data_to_save']['base_attribs'] = $module[$mf_id]['base_attribs'];
					}
				}
			}

			$this->_save( $this->request->post[$this->_name.'_module'], $this->session->data['mf_data_to_save'] );

			//Arrange layouts
			if( is_array( $this->session->data['mf_data_to_save']['layout_id'] ) ) {
				$lytArr = array();
				foreach ($this->session->data['mf_data_to_save']['layout_id'] as $key => $value) {
					if($value)
						$lytArr[] = $key;
				}
				$this->session->data['mf_data_to_save']['layout_id'] = $lytArr;
			}
			////////////////////////
			//Arrange customer groups
			if( is_array( $this->session->data['mf_data_to_save']['customer_groups'] ) ) {
				$custArr = array();
				foreach ($this->session->data['mf_data_to_save']['customer_groups'] as $key => $value) {
					if($value)
						$custArr[] = $value;
				}
				$this->session->data['mf_data_to_save']['customer_groups'] = $custArr;
			}
			////////////////////////

			if( ! $pager && $idx == $count ) {				
				$settings = $this->config->get( $this->_name . '_settings' );
				
				if( empty( $this->session->data['mf_data_to_save']['layout_id'] ) ) {
					$this->session->data['mf_data_to_save']['layout_id'] = array();
				}
				
				if( ! in_array( $settings['layout_c'], $this->session->data['mf_data_to_save']['layout_id'] ) ) {
					$this->session->data['mf_data_to_save']['category_id'] = array();
				}
			
				if( empty( $this->session->data['mf_data_to_save']['store_id'] ) ) {
					$this->session->data['mf_data_to_save']['store_id'] = array( 0 );
				}
				
				if( NULL == ( $data = $this->config->get($this->_name . '_module') ) ) {
					$data = array();
				}
				/// Remove module
				if((int) $this->request->get['rmv_id']){
					unset($data[$this->request->get['rmv_id']]);
				}
				// Add Edit Module
				else{
					$data[$mf_id] = $this->session->data['mf_data_to_save'];
				}
				/// if data is empty (caused by remove last element)
				if(!count($data))
					$data[0] = null;

				$byStores = array();
				
				foreach( $data as $v ) {
					if( empty( $v['store_id'] ) ) {
						$v['store_id'] = array( 0 );
					}
					
					foreach( $v['store_id'] as $vv ) {
						$byStores[$vv] = $vv;
					}
				}			
				
				$this->load->model('setting/setting');

				foreach( $byStores as $store_id ) {
					$this->model_setting_setting->editSetting(
						$this->_name . '_module', 
						array( $this->_name . '_module' => $data ),
						$store_id
					);
				}	
				
				unset( $this->session->data['mf_data_to_save'] );
				
				if( $this->_cacheWritable() && NULL != ( $files = glob($this->_cache_dir . '/idx.' . $mf_id . '.*') ) ) {
					foreach( $files as $file ) {
						if( strlen( basename( $file ) ) > 32 ) {
							@ unlink( $file );
						}
					}
				}

				$this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success');
                $result_json['success'] = '1';
	            
	            $this->response->setOutput(json_encode($result_json));
	            return;
			}
		}
		
		$modules = (array) $this->config->get($this->_name . '_module');

		///load attributes if not exists
		$this->load->model('catalog/attribute');
		//$attribute_total = $this->model_catalog_attribute->getTotalAdvancedAttributes();

        //Get config settings for  advanced product attribute status
        $this->load->model('module/advanced_product_attributes/settings');
        $advanced_product_attributes_installed = $this->model_module_advanced_product_attributes_settings->isInstalled();
        $this->data['advanced_product_attributes_status'] = $advanced_product_attributes_installed;

        if ($advanced_product_attributes_installed){
            $advanced_attribute_results = $this->model_catalog_attribute->getAdvancedAttributes($data);
            $attribute_results = $this->model_catalog_attribute->getAttributes($data);
            foreach ($advanced_attribute_results as $item) {
               $attribute_results[]=$item;
            }
            foreach( $modules as $module_id => $module ) {
                $tempAttArr =$attribute_results;
                if(empty( $module['attribs'] ) ) {
                    $modules[$this->request->get['layout_id']]['attribs'] = $attribute_results;
                }else{
                    foreach( $modules[$module_id]['attribs'] as $key => $val ) {

                        $temp_key=$this->searchForAttributeId($modules[$module_id]['attribs'][$key]["attribute_id"], $tempAttArr);

                        if ($temp_key!=-1){
                           unset($tempAttArr[$temp_key]) ;
                        }
                    }
                    $modules[$module_id]['attribs'] = $modules[$module_id]['attribs'] + $tempAttArr ;
                }
            }
		}
		//////////////////////////////

		///load options if not exists
		$this->load->model('catalog/option');
		$option_total = $this->model_catalog_option->getTotalOptions();
		$option_results = $this->model_catalog_option->getOptions($data);

		foreach( $modules as $module_id => $module ) {	
			$tempOptArr = $option_results;
			if(empty( $module['options'] ) ) {
				$modules[$module_id]['options'] = $option_results;
			}else{
				foreach( $modules[$module_id]['options'] as $key => $val ) {
					if (array_key_exists($key,$tempOptArr)){
					  unset( $tempOptArr[$key] );
					}	
				}
				$modules[$module_id]['options'] = array_merge($modules[$module_id]['options'], $tempOptArr);
			}
		}
		//////////////////////////////
		unset( $modules[0] );

		$this->_setParams(array(
			'modules'		=> $modules
		), array());

		// parametry
		$this->data['token']		= null;
		$this->data['layouts']		= $this->model_design_layout->getLayouts();
		$this->data['languages']	= $this->model_localisation_language->getLanguages();		
		$this->data['layouts']		= $this->model_design_layout->getLayouts();
		$this->data['settings']		= $settings;
		$this->data['attribs']		= (array) $this->config->get( $this->_name . '_attribs' );
		//$this->data['options']		= (array) $this->config->get($this->_name . '_options');
		$this->data['filters']		= (array) $this->config->get($this->_name . '_filters');
		$this->data['items']		= $this->_getItems();
		$this->data['optionItems']	= $this->_getOptionItems();
		
		if( $this->hasFilters() ) {
			$this->data['filterItems']	= $this->_getFilterItems();
		}
		

		// Categories
        $this->load->model('catalog/category');

        $categories_ = $this->data['modules'][$this->request->get['layout_id']]['categories'];
        foreach ($categories_ as $key => $value) {
            $categoryInfo = $this->model_catalog_category->getCategory($value['root_category_id']);

            if ($categoryInfo) {
                $this->data['product_categories'][$key] = array(
                    'category_id' => $categoryInfo['category_id'],
                    'name' => ($categoryInfo['path'] ? $categoryInfo['path'] . ' &gt; ' : '') . $categoryInfo['name']
                );
            }
        }

		// Show in Categories ////////////////////////////////////////////////////////////
		$categories_ids = array();
		
		foreach( $this->data['modules'] as $module ) {			
			if( ! empty( $module['category_id'] ) ) {
				foreach( $module['category_id'] as $category_id ) {
					$categories_ids[] = (int) $category_id;
				}
			}
			
			if( ! empty( $module['categories'] ) ) {
				foreach( $module['categories'] as $category_id ) {
					if( ! empty( $category_id['root_category_id'] ) ) {
						$categories_ids[] = (int) $category_id['root_category_id'];
					}
				}
			}
		}
		
		$this->data['categories'] = array();
		$categories = array();
		
		if( ! empty( $categories_ids ) ) {
			$categories	= array();
			
			foreach( $this->db->query( $this->_qCat( $categories_ids ) )->rows as $category ) {
				$categories[$category['category_id']] = ( $category['path'] ? $category['path'] . ' &gt; ' : '' ) . $category['name'];
			}
			
			foreach( $this->data['modules'] as $module_id => $module ) {
				if( ! empty( $module['category_id'] ) ) {				
					foreach( $module['category_id'] as $category_id ) {
						if( isset( $categories[$category_id] ) ) {
							$this->data['categories'][$module_id][$category_id] = $categories[$category_id];
						}
					}
				}
			}
		}
		
		$this->data['categoriesNames'] = $categories;
		
		// Hide in Categories ////////////////////////////////////////////////////////////
		$categories_ids = array();
		
		foreach( $this->data['modules'] as $module ) {
			if( empty( $module['hide_category_id'] ) ) continue;
			
			foreach( $module['hide_category_id'] as $category_id ) {
				$categories_ids[] = (int) $category_id;
			}
		}
		
		$this->data['hideCategories'] = array();
		
		if( ! empty( $categories_ids ) ) {
			$categories	= array();
			
			foreach( $this->db->query( $this->_qCat( $categories_ids ) )->rows as $category ) {
				$categories[$category['category_id']] = ( $category['path'] ? $category['path'] . ' &gt; ' : '' ) . $category['name'];
			}
			
			foreach( $this->data['modules'] as $module_id => $module ) {
				if( empty( $module['hide_category_id'] ) ) continue;
				
				foreach( $module['hide_category_id'] as $category_id ) {
					if( empty( $categories[$category_id] ) ) continue;
					
					$this->data['hideCategories'][$module_id][$category_id] = $categories[$category_id];
				}
			}
		}
		
		// Stores //////////////////////////////////////////////////////////////
		
		$this->data['stores'][] = array(
			'store_id' => 0,
			'name'     => $this->config->get('config_name') . $this->language->get('text_default')
		);
				
		foreach( $this->model_setting_store->getStores() as $result ) {
			$this->data['stores'][] = array(
				'store_id' => $result['store_id'],
				'name'     => $result['name']
			);
		}
		
		// Groups of customers /////////////////////////////////////////////////
		
		$this->load->model('sale/customer_group');
		
		$this->data['customerGroups'] = array();
		
		foreach( $this->model_sale_customer_group->getCustomerGroups(array()) as $result ) {
			$this->data['customerGroups'][] = array(
				'customer_group_id' => $result['customer_group_id'],
				'name' => $result['name']
			);
		}
		
		////////////////////////////////////////////////////////////////////////
		if($this->request->get['layout_id'])
			$this->data['layout_id'] = $this->request->get['layout_id'];

		$this->data['action_ldv']			= $this->url->link('module/' . $this->_name . '/ldv', '', 'SSL');
		$this->data['action_save_data']		= $this->url->link('module/' . $this->_name , '', 'SSL');
		$this->data['cancel']		= $this->url.'module/' . $this->_name;
		$this->data['action_get_data']		= $this->url->link('module/' . $this->_name . '/get_data', '', 'SSL');
		$this->data['action_remove_data']	= $this->url->link('module/' . $this->_name . '/remove_data', '', 'SSL');
				
		$this->response->setOutput( $this->render() );
	}

	public function update(){
		$this->index();
	}

	private function _qCat( $categories_ids ) {
		return "
			SELECT 
				`c`.`category_id`, `cd`.`name`,
				" . ( $this->hasFilters() ? "
				(
					SELECT 
						GROUP_CONCAT(`cd1`.`name` ORDER BY `level` SEPARATOR ' &gt; ') 
					FROM 
						`" . DB_PREFIX . "category_path` AS `cp` 
					LEFT JOIN 
						`" . DB_PREFIX . "category_description` AS `cd1` 
					ON 
						`cp`.`path_id` = `cd1`.`category_id` AND `cp`.`category_id` != `cp`.`path_id` 
					WHERE 
						`cp`.`category_id` = `c`.`category_id` AND `cd1`.`language_id` = '" . (int)$this->config->get('config_language_id') . "' 
					GROUP BY 
						`cp`.`category_id`
				) AS `path`" : "CONCAT( '', '' ) AS `path`" ) . "
			FROM 
				`" . DB_PREFIX . "category` AS `c`
			LEFT JOIN
				`" . DB_PREFIX . "category_description` AS `cd`
			ON
				`c`.`category_id` = `cd`.`category_id` AND `cd`.`language_id` = " . $this->config->get( 'config_language_id' ) . "
			WHERE
				`c`.`category_id` IN(" . implode( ',', array_unique( $categories_ids ) ) . ")
		";
	}
	
	public function ldv() {
		// załaduj język
		$this->data = array_merge($this->data, $this->language->load('module/' . $this->_name));
		
		$this->data['_name']	= $this->_name;
		$this->data['name']		= $this->request->get['name'];
		$this->data['type']		= $this->request->get['type'];
		$this->data['IDX']		= $this->request->get['idx'];
		
		
		$filter	= isset( $this->request->get['filter'] ) ? $this->request->get['filter'] : '';
		$page	= isset( $this->request->get['page'] ) ? $this->request->get['page'] : 1;		
		$limit	= 100;//$this->config->get('config_admin_limit');
		
		switch( $this->data['type'] ) {
			case 'base_attribs' : {
				$settings	= $this->config->get($this->_name . '_settings' );
				$this->data['base_attribs'] = $settings['attribs'];
				
				break;
			}
			case 'attribs'		: $this->data['attribs']	= $this->config->get($this->_name . '_attribs'); break;
			case 'options'		: $this->data['options']	= $this->config->get($this->_name . '_options'); break;
			case 'filters'		: $this->data['filters']	= $this->config->get($this->_name . '_filters'); break;
		}
		
		if( ! empty( $this->request->get['mf_default'] ) && $this->hasPermission() ) {
			$data = $this->config->get( $this->_name . '_module' );
			
			$data[$this->request->get['idx']][$this->data['type']] = array();

			$this->load->model('setting/setting');
			$this->model_setting_setting->editSetting($this->_name . '_module', array( $this->_name . '_module' => $data ));
		} else if( empty( $this->request->get['mf_default'] ) && NULL != ( $module = $this->config->get($this->_name . '_module' ) ) ) {
			if( isset( $module[$this->request->get['idx']] ) ) {
				$module = $module[$this->request->get['idx']];
				
				if( ! empty( $module['base_attribs'] ) ) {
					$this->data['base_attribs'] = $module['base_attribs'];
				}

				if( ! empty( $module['attribs'] ) ) {
					$this->data['attribs'] = $module['attribs'];
				}

				if( ! empty( $module['options'] ) ) {
					$this->data['options'] = $module['options'];
				}

				if( ! empty( $module['filters'] ) ) {
					$this->data['filters'] = $module['filters'];
				}
			}
		}
		
		if( $this->data['type'] == 'options' ) {
			$this->data['optionItems']	= $this->_getOptionItems($limit * ( $page-1 ), $limit, $filter);
			$total = $this->_getTotalOptionItems( $filter );
		} else if( $this->hasFilters() && $this->data['type'] == 'filters' ) {
			$this->data['filterItems'] = $this->_getFilterItems($limit * ( $page-1 ), $limit, $filter);
			$total = $this->_getTotalFilterItems( $filter );
		} else {
			$this->data['items']	= $this->_getItems( $limit * ( $page-1 ), $limit, $filter);
			$total					= $this->_getTotalItems( $filter );
		}
		
		$pagination = new Pagination();
		$pagination->total = $total;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('module/mega_filter/ldv', 'page={page}', 'SSL');
			
		$this->data['pagination'] = $pagination->render();
		
		$this->template = 'module/' . $this->_name . '_ldv.tpl';
		$this->response->setOutput( $this->render() );
	}
	
	private function _getItems( $start = NULL, $limit = NULL, $filter = NULL ) {
		$items = array();
		
		$this->load->model('catalog/attribute');
		
		foreach( $this->model_catalog_attribute->getAttributes(array( 'start' => $start, 'limit' => $limit, 'filter_name' => $filter )) as $attribute ) {
			$items[$attribute['attribute_group_id']]['name']		= $attribute['attribute_group'];
			$items[$attribute['attribute_group_id']]['childs'][]	= $attribute;
		}
		
		return $items;
	}
	
	private function _getTotalItems( $filter = NULL ) {
		$sql = "SELECT 
			COUNT(*) AS c 
			FROM 
				" . DB_PREFIX . "attribute a 
			LEFT JOIN 
				" . DB_PREFIX . "attribute_description ad ON (a.attribute_id = ad.attribute_id) 
			WHERE 
				ad.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		
		if( $filter ) {
			$sql .= " AND ad.name LIKE '" . $this->db->escape( $filter ) . "%'";
		}
		
		return $this->db->query( $sql )->row['c'];
	}
	
	private function _getAttribIds() {
		$ids = array();
		
		$this->load->model('catalog/attribute');
		
		foreach( $this->model_catalog_attribute->getAttributes(array()) as $attribute ) {
			$ids[$attribute['attribute_id']] = $attribute['attribute_id'];
		}
		
		return $ids;
	}
	
	////////////////////////////////////////////////////////////////////////////
	
	/**
	 * Ustawienia atrybutów 
	 */
	public function attributes() {
		$this->_init( 'attributes' );
		
		$page = isset( $this->request->get['page'] ) ? $this->request->get['page'] : 1;
		$config = $this->config->get($this->_name . '_attribs');
		$limit = 100;//$this->config->get('config_admin_limit');
		
		$this->data['action']	= $this->url->link('module/' . $this->_name . '/attributes', 'page=' . $page, 'SSL');
		
		if( $this->request->server['REQUEST_METHOD'] == 'POST' && $this->checkPermission() ) {
			$this->load->model('setting/setting');
			$this->load->model('setting/store');
			
			if( ! empty( $this->request->post[$this->_name.'_attribs'] ) ) {
				foreach( $this->request->post[$this->_name.'_attribs'] as $id => $conf ) {
					foreach( $conf as $id2 => $conf2 ) {						
						if( $id2 == 'items' ) {
							foreach( $conf2 as $id3 => $conf3 ) {
								$config[$id][$id2][$id3] = $conf3;
							}
						} else {
							$config[$id][$id2] = $conf2;
						}
					}
				}
				
				$ids = $this->_getAttribIds();
				
				foreach( $config as $id_g => $conf_g ) {
					foreach( $config[$id_g]['items'] as $id => $conf ) {
						if( ! isset( $ids[$id] ) ) {
							unset( $config[$id_g]['items'][$id] );
						}
					}
					
					if( empty( $config[$id_g]['items'] ) ) {
						unset( $config[$id_g] );
					}
				}
			}
			
			// główny sklep
			$this->model_setting_setting->editSetting($this->_name . '_attribs', array( $this->_name . '_attribs' => $config ));
			
			// pozostałe sklepy			
			foreach( $this->model_setting_store->getStores() as $result ) {
				$this->model_setting_setting->editSetting($this->_name . '_attribs', array( $this->_name . '_attribs' => $config ), $result['store_id']);
			}
					
			$this->session->data['success'] = $this->language->get('text_success');
						
			$this->redirect($this->url->link('module/' . $this->_name . '/attributes', 'page=' . $page, 'SSL'));
		}
		
		$this->load->model('tool/image');
		$this->load->model('catalog/attribute_group');
		$this->load->model('localisation/language');
		
		$this->data['no_image'] = $this->model_tool_image->resize('no_image.jpg', 150, 150);
		
		$this->data['token'] = null;
		$this->data['items'] = $this->_getItems( $limit * ( $page-1 ), $limit );
		$this->data['attribs'] = $config;
		$this->data['languages'] = $this->model_localisation_language->getLanguages();
		$this->data['attribGroups'] = $this->model_catalog_attribute_group->getAttributeGroups(array());
		$this->data['action_attribs_by_group'] = $this->url->link('module/' . $this->_name . '/getAttribsByGroupAsJson', '', 'SSL');
		$this->data['action_values_by_attrib'] = $this->url->link('module/' . $this->_name . '/getAttribsValuesByAttribAsJson', '', 'SSL');
		$this->data['action_attribs_save'] = $this->url->link('module/' . $this->_name . '/attribsSave', '', 'SSL');
		$this->data['action_attribs_reset'] = $this->url->link('module/' . $this->_name . '/attribsReset', '', 'SSL');
		
		$pagination = new Pagination();
		$pagination->total = $this->model_catalog_attribute->getTotalAttributes();
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('module/mega_filter/attributes', 'page={page}', 'SSL');
			
		$this->data['pagination'] = $pagination->render();
		
		$this->response->setOutput( $this->render() );
	}
	
	public function attribsReset() {
		if( ! empty( $this->request->post['attr_id'] ) && ! empty( $this->request->post['type'] ) && ! empty( $this->request->post['lang_id'] ) ) {
			$this->load->model('setting/setting');
			$this->load->model('setting/store');
			
			$attr_id = (int)$this->request->post['attr_id'];
			$lang_id = (int)$this->request->post['lang_id'];
			$type = (string)$this->request->post['type'];
			
			switch( $type ) {
				case 'images' : $type = 'img'; break;
				default : $type = 'sort';
			}
			
			// główny sklep
			$this->model_setting_setting->deleteSetting($this->_name . '_at_' . $type . '_' . $attr_id . '_' . $lang_id );
			
			// pozostałe sklepy			
			foreach( $this->model_setting_store->getStores() as $result ) {
				$this->model_setting_setting->deleteSetting($this->_name . '_at_' . $type . '_' . $attr_id . '_' . $lang_id, $result['store_id']);
			}	
		}
	}
	
	public function attribsSave() {
		if( ! empty( $this->request->post['attr_id'] ) && ! empty( $this->request->post['items'] ) || ! empty( $this->request->post['type'] ) && ! empty( $this->request->post['lang_id'] ) ) {
			$data		= array();
			$attr_id	= (int) $this->request->post['attr_id'];
			$lang_id = (int)$this->request->post['lang_id'];
			$type		= $this->request->post['type'];
			
			if( isset( $this->request->post['items'] ) ) {
				foreach( $this->request->post['items'] as $i => $v ) {
					switch( $type ) {
						case 'images' : {
							$data[$v['key']] = $v['img'];

							break;
						}
						case 'sort-values' : {
							$data[$v['key']] = $i;

							break;
						}
					}
				}
			}
			
			switch( $type ) {
				case 'images'		: 
					$type = 'img'; break;
				case 'sort-values'	: 
				default : 
					$type = 'sort'; break;
			}
			
			$key	= $this->_name . '_at_' . $type . '_' . $attr_id . '_' . $lang_id;
			$data	= array( $key => $data );
			
			$this->load->model('setting/store');
			$this->load->model('setting/setting');
			
			// główny sklep
			$this->model_setting_setting->editSetting($key, $data);
			
			// pozostałe sklepy
			foreach( $this->model_setting_store->getStores() as $result ) {
				$this->model_setting_setting->editSetting($key, $data, $result['store_id']);
			}
		}
	}
	
	public function getAttribsByGroupAsJson() {
		$json = array();
		
		if( ! empty( $this->request->get['attribute_group_id'] ) ) {
			$this->load->model('catalog/attribute');
			$json = $this->model_catalog_attribute->getAttributes(array('filter_attribute_group_id'=>(int)$this->request->get['attribute_group_id']));
		}
		
		$this->response->setOutput( '<div id="json-encode">' . base64_encode( json_encode( $json ) ) . '</div>' );
	}
	
	public function getAttribsValuesByAttribAsJson() {
		$json = array();
		
		if( ! empty( $this->request->get['attribute_id'] ) && ! empty( $this->request->get['type'] ) && ! empty( $this->request->get['lang_id'] ) ) {
			$this->load->model('tool/image');
			
			/**
			 * Parametry 
			 */
			$attribute_id	= $this->request->get['attribute_id'];
			$lang_id		= $this->request->get['lang_id'];
			$type			= $this->request->get['type'];
			$settings		= $this->config->get( $this->_name . '_settings' );
			$attribs		= (array) $this->config->get( $this->_name . '_attribs' );
			$images			= $type == 'images' ? (array) $this->config->get( $this->_name . '_at_img_' . $attribute_id . '_' . $lang_id ) : array();
			
			/**
			 * SQL 
			 */
			$sql			= "
				SELECT
					REPLACE(REPLACE(TRIM(`pa`.`text`), '\r', ''), '\n', '') AS `text`,
					`pa`.`attribute_id`
				FROM
					`" . DB_PREFIX . "product_attribute` AS `pa`
				INNER JOIN
					`" . DB_PREFIX . "product` AS `p`
				ON
					`pa`.`product_id` = `p`.`product_id` AND `p`.`status` = '1'
				WHERE
					`pa`.`attribute_id` = " . (int) $this->request->get['attribute_id'] . " AND
					`pa`.`language_id` = '" . (int)$lang_id . "'
				GROUP BY
					`text`, `pa`.`attribute_id`
			";
			$unique = array();
			foreach( $this->db->query( $sql )->rows as $row ) {
				$row['text'] = trim( $row['text'] );
				
				if( ! empty( $settings['attribute_separator'] ) ) {
					$row['text'] = array_map( 'trim', explode( $settings['attribute_separator'], $row['text'] ) );
					
					foreach( $row['text'] as $text ) {
						if( isset( $unique[$text] ) ) continue;
						
						$k = md5( $text );
						
						$json[] = array( 
							'key' => $k, 
							'val' => $text,
							'ival' => isset( $images[$k] ) ? $images[$k] : '',
							'img' => isset( $images[$k] ) ? $this->model_tool_image->resize( $images[$k], 100, 100 ) : '' 
						);
						$unique[$text] = true;
					}
				} else if( ! isset( $unique[$row['text']] ) ) {
					$k = md5( $row['text'] );
					$json[] = array( 
						'key' => $k, 
						'val' => $row['text'], 
						'ival' => isset( $images[$k] ) ? $images[$k] : '',
						'img' => isset( $images[$k] ) ? $this->model_tool_image->resize( $images[$k], 100, 100 ) : '' 
					);
					$unique[$row['text']] = true;
				}
			}
			
			$this->_sortOptions( $json, ! empty( $attribs[$attribute_id]['sort_order_values'] ) ? $attribs[$attribute_id]['sort_order_values'] : '', $lang_id, $attribute_id );
			
			$tmp = array();
			
			foreach( $json as $v ) {
				$tmp[] = $v;
			}
			
			$json = $tmp;
		}
		
		$this->response->setOutput( '<div id="json-encode">' . base64_encode( json_encode( $json ) ) . '</div>' );
	}
	
	private static function _sortItems( $a, $b ) {
		$as = isset( self::$_tmp_sort_parameters['config'][$a['key']] ) ? self::$_tmp_sort_parameters['config'][$a['key']] : count(self::$_tmp_sort_parameters['config']);
		$bs = isset( self::$_tmp_sort_parameters['config'][$b['key']] ) ? self::$_tmp_sort_parameters['config'][$b['key']] : count(self::$_tmp_sort_parameters['config']);
		
		if( $as > $bs )
			return 1;
		
		if( $as < $bs )
			return -1;
		
		return 0;
	}
	
	private function _sortOptions( & $options, $sort, $lang_id, $attribute_id = NULL ) {		
		if( $sort ) {
			list( $type, $order ) = explode( '_', $sort );
		} else {
			$type = $order = '';
		}
		
		if( $attribute_id ) {
			$attribute_id = str_replace(array(
				'a_', 'o_', 'f_'
			), '', $attribute_id);
		}
		
		self::$_tmp_sort_parameters = array(
			'attribute_id'	=> $attribute_id,
			'type'			=> $type,
			'order'			=> $order,
			'a'				=> false,
			'options'		=> $options,
			'config'		=> $this->config->get( 'mega_filter_at_sort_' . $attribute_id . '_' . $lang_id )
		);
		
		if( ! self::$_tmp_sort_parameters['type'] && ! self::$_tmp_sort_parameters['config'] ) {
			self::$_tmp_sort_parameters = NULL;
			
			return;
		}
		
		if( ! self::$_tmp_sort_parameters['type'] && self::$_tmp_sort_parameters['attribute_id'] !== NULL && self::$_tmp_sort_parameters['config'] ) {
			uasort( $options, array( 'ControllerModuleMegaFilter', '_sortItems' ) );
		} else {
			$tmp = array();
			
			foreach( $options as $k => $v ) {
				$tmp['_'.$k] = htmlspecialchars_decode( $v['name'] );
			}
			
			if( $order == 'desc' ) {
				arsort( $tmp, $type == 'string' ? SORT_STRING : SORT_NUMERIC );
			} else {
				asort( $tmp, $type == 'string' ? SORT_STRING : SORT_NUMERIC );
			}
			
			$tmp2 = array();
			
			foreach( $tmp as $k => $v ) {
				$tmp2[trim($k,'_')] = $options[trim($k,'_')];
			}
		
			$options = $tmp2;
		}
			
		self::$_tmp_sort_parameters = NULL;
	}
	
	////////////////////////////////////////////////////////////////////////////
	
	private function _getOptionItems( $start = NULL, $limit = NULL, $filter = NULL ) {
		$items = array();
		$sql = "
			SELECT 
				* 
			FROM 
				`" . DB_PREFIX . "option` AS `o` 
			LEFT JOIN 
				`" . DB_PREFIX . "option_description` AS `od` 
			ON 
				`o`.`option_id` = `od`.`option_id` 
			WHERE 
				`od`.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND
				`o`.`type` IN( 'select', 'radio', 'checkbox', 'image' )
		";
		
		if( $filter ) {
			$sql .= " AND `od`.`name` LIKE '%" . $this->db->escape( $filter ) . "%'";
		}
		
		if( $start !== NULL && $limit !== NULL ) {
			$sql .= 'LIMIT ' . (int) ( $start * $limit ) . ', ' . ( (int) $limit );
		}
		
		foreach( $this->db->query( $sql )->rows as $option ) {
			$items[$option['option_id']] = $option;
		}
		
		return $items;
	}
	
	private function _getTotalOptionItems( $filter = NULL ) {
		$sql = "
			SELECT 
				COUNT(*) AS c 
			FROM 
				`" . DB_PREFIX . "option` AS `o` 
			LEFT JOIN 
				`" . DB_PREFIX . "option_description` AS `od` 
			ON 
				`o`.`option_id` = `od`.`option_id` 
			WHERE 
				`od`.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND
				`o`.`type` IN( 'select', 'radio', 'checkbox', 'image' )
		";
		
		if( $filter ) {
			$sql .= " AND `od`.`name` LIKE '%" . $this->db->escape( $filter ) . "%'";
		}
		
		return $this->db->query( $sql )->row['c'];
	}
	
	/**
	 * Ustawienia opcji 
	 */
	public function options() {
		$this->_init( 'options' );
		
		$page = isset( $this->request->get['page'] ) ? $this->request->get['page'] : 1;
		$config = $this->config->get($this->_name . '_options');
		$limit = 100;//$this->config->get('config_admin_limit');
		
		$this->data['action']	= $this->url->link('module/' . $this->_name . '/options', 'page=' . $page, 'SSL');
		
		if( $this->request->server['REQUEST_METHOD'] == 'POST' && $this->checkPermission() ) {
			$this->load->model('setting/setting');
			$this->load->model('setting/store');
			
			if( ! empty( $this->request->post[$this->_name . '_options'] ) ) {
				foreach( $this->request->post[$this->_name . '_options'] as $id => $conf ) {
					$config[$id] = $conf;
				}
			}
			
			$ids = array_keys( $this->_getOptionItems() );
			
			foreach( $config as $id => $conf ) {
				if( ! in_array( $id, $ids ) ) {
					unset( $config[$id] );
				}
			}
			
			// główny sklep
			$this->model_setting_setting->editSetting($this->_name . '_options', array(	$this->_name . '_options' => $config ));
			
			// pozostałe sklepy			
			foreach( $this->model_setting_store->getStores() as $result ) {
				$this->model_setting_setting->editSetting($this->_name . '_options', array(	$this->_name . '_options' => $config ), $result['store_id']);
			}	
			
			$this->session->data['success'] = $this->language->get('text_success');
						
			$this->redirect($this->url->link('module/' . $this->_name . '/options', 'page=' . $page, 'SSL'));
		}
		
		$this->data['optionItems'] = $this->_getOptionItems( $page-1, $limit );
		$this->data['options'] = $config;
		
		$pagination = new Pagination();
		$pagination->total = $this->_getTotalOptionItems();
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('module/mega_filter/options', 'page={page}', 'SSL');
			
		$this->data['pagination'] = $pagination->render();
		
		$this->response->setOutput( $this->render() );
	}
	
	////////////////////////////////////////////////////////////////////////////
	
	private function _getFilterItems( $start = NULL, $limit = NULL, $filter = NULL ) {
		$items = array();
		$sql = "
			SELECT 
				* 
			FROM 
				`" . DB_PREFIX . "filter_group` AS `fg` 
			INNER JOIN 
				`" . DB_PREFIX . "filter_group_description` AS `fgd` 
			ON 
				`fg`.`filter_group_id` = `fgd`.`filter_group_id` AND `fgd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'
		";
		
		if( $filter ) {
			$sql .= " AND `fgd`.`name` LIKE '%" . $this->db->escape( $filter ) . "%'";
		}
		
		if( $start !== NULL && $limit !== NULL ) {
			$sql .= 'LIMIT ' . (int) ( $start * $limit ) . ', ' . (int) $limit;
		}
		
		foreach( $this->db->query( $sql )->rows as $filter ) {
			$items[$filter['filter_group_id']] = $filter;
		}
		
		return $items;
	}
	
	private function _getTotalFilterItems( $filter = NULL ) {
		$sql = "
			SELECT 
				COUNT(*) AS c 
			FROM 
				`" . DB_PREFIX . "filter_group` AS `fg` 
			INNER JOIN 
				`" . DB_PREFIX . "filter_group_description` AS `fgd` 
			ON 
				`fg`.`filter_group_id` = `fgd`.`filter_group_id` AND `fgd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'
		";
		
		if( $filter ) {
			$sql .= " AND `fgd`.`name` LIKE '%" . $this->db->escape( $filter ) . "%'";
		}
		
		return $this->db->query( $sql )->row['c'];
	}
	
	/**
	 * Ustawienia filtrów 
	 */
	public function filters() {
		$this->_init( 'filters' );
		
		$page = isset( $this->request->get['page'] ) ? $this->request->get['page'] : 1;
		$config = $this->config->get($this->_name . '_filters');
		$limit = 100;//$this->config->get('config_admin_limit');
		
		$this->data['action']	= $this->url->link('module/' . $this->_name . '/filters', 'page=' . $page, 'SSL');
		
		if( $this->request->server['REQUEST_METHOD'] == 'POST' && $this->checkPermission() ) {
			$this->load->model('setting/setting');
			$this->load->model('setting/store');
			
			if( ! empty( $this->request->post[$this->_name . '_filters'] ) ) {
				foreach( $this->request->post[$this->_name . '_filters'] as $id => $conf ) {
					$config[$id] = $conf;
				}
			}
			
			$ids = array_keys( $this->_getFilterItems() );
			
			foreach( $config as $id => $conf ) {
				if( ! in_array( $id, $ids ) ) {
					unset( $config[$id] );
				}
			}
			
			// główny sklep
			$this->model_setting_setting->editSetting($this->_name . '_filters', array(	$this->_name . '_filters' => $config ));
			
			// pozostałe sklepy
			foreach( $this->model_setting_store->getStores() as $result ) {
				$this->model_setting_setting->editSetting($this->_name . '_filters', array(	$this->_name . '_filters' => $config ), $result['store_id']);
			}	
					
			$this->session->data['success'] = $this->language->get('text_success');
						
			$this->redirect($this->url->link('module/' . $this->_name . '/filters', 'page=' . $page, 'SSL'));
		}
		
		$this->data['filterItems'] = $this->_getFilterItems();
		$this->data['filters'] = $config;
		$this->data['filters_view'] = true;
		
		$pagination = new Pagination();
		$pagination->total = $this->_getTotalFilterItems();
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('module/mega_filter/filters', 'page={page}', 'SSL');
			
		$this->data['pagination'] = $pagination->render();
		
		$this->response->setOutput( $this->render() );
	}
	
	////////////////////////////////////////////////////////////////////////////
	
	private function _updateCss( $settings ) {
		$css = '
			.mfilter-counter {
				background: #' . ( empty( $settings['background_color_counter'] ) ? '428BCA' : trim( $settings['background_color_counter'], '#' ) ) . ';
				color: #' . ( empty( $settings['text_color_counter'] ) ? 'FFFFFF' : trim( $settings['text_color_counter'], '#' ) ) . ';
			}
			.mfilter-counter:after {
				border-right-color: #' . ( empty( $settings['background_color_counter'] ) ? '428BCA' : trim( $settings['background_color_counter'], '#' ) ) . ';
			}
		';
		
		if( ! empty( $settings['css_style'] ) ) {
			$css .= htmlspecialchars_decode( $settings['css_style'] );
		}
	
		if( ! empty( $settings['background_color_search_button'] ) ) {
			$css .= '
				.mfilter-search #mfilter-opts-search_button {
					background-color: #' . trim( $settings['background_color_search_button'], '#' ) . ';
				}
			';
		}
		
		if( ! empty( $settings['background_color_slider'] ) ) {
			$css .= '
				.mfilter-slider-slider .ui-slider-range,
				#mfilter-price-slider .ui-slider-range {
					background: #' . trim( $settings['background_color_slider'], '#' ) . ' !important;
				}
			';
		}
		
		if( ! empty( $settings['background_color_header'] ) ) {
			$css .= '
				.mfilter-heading {
					background: #' . trim( $settings['background_color_header'], '#' ) . ' !important;
				}
			';
		}
		
		if( ! empty( $settings['text_color_header'] ) ) {
			$css .= '
				.mfilter-heading {
					color: #' . trim( $settings['text_color_header'], '#' ) . ';
				}
			';
		}

		if( ! empty( $settings['border_bottom_color_header'] ) ) {
			$css .= '
				.mfilter-opts-container {
					border-top-color: #' . trim( $settings['border_bottom_color_header'], '# ' ) . ' !important;
				}
			';
		}
		
		file_put_contents( DIR_CACHE . 'view/theme/default/stylesheet/mf/style-2.css', $css );
	}
	
	private function _writableCss() {
		$dir = DIR_CACHE . 'view/theme/default/stylesheet/mf';
		$path = $dir . '/style-2.css';

		if( ! file_exists( $dir ) ){
			mkdir($dir, 0777, true);
		}

		if( ! file_exists( $path ) ) {
			fopen($path, "w");
		}
		
		if( ! is_writable( $path ) ) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Ustawienia modułu 
	 */
	public function settings() {
		$this->_init( 'settings' );
		
		$this->data['action']	= $this->url->link('module/' . $this->_name . '/settings', '', 'SSL');
		$this->data['cancel']	= $this->url.'marketplace/home';
		
		$this->data['action_clear_cache']	= $this->url->link('module/' . $this->_name . '/clearcache', '', 'SSL');
		
		$this->data['settings'] = $this->config->get($this->_name . '_settings');
		
		$this->data['action_rebuild_index'] = $this->data['action']	= $this->url->link('module/' . $this->_name . '/installprogress', 'token=' . $this->session->data['token'], 'SSL');
		
		if( ! $this->_writableCss() ) {
			$this->_setErrors(array(
				'warning' => $this->language->get( 'error_css_file' )
			));
		}
		
		if( $this->request->server['REQUEST_METHOD'] == 'POST' && $this->checkPermission() ) {
			if( ! empty( $this->request->post[$this->_name . '_settings']['cache_enabled'] ) && ! $this->_cacheWritable() ) {
				$this->request->post[$this->_name . '_settings']['cache_enabled'] = '0';
				
				$this->session->data['error'] = $this->language->get('error_cache_dir');
			} else {
				$this->session->data['success'] = $this->language->get('text_success');
			}
			
			$this->load->model('setting/setting');
			$this->load->model('setting/store');
			
			//Remove items of value 0 (for is empty checks)
			$dataPost = $this->request->post;
			foreach ($dataPost['mega_filter_settings'] as $key => $value) {
				if(!is_array($value)){
					if($value == '0')
						unset($dataPost['mega_filter_settings'][$key]);
				}
			}
			////////////////////////////////////////
			
			// główny sklep
			$this->model_setting_setting->editSetting($this->_name . '_settings', $dataPost);
			
			// pozostałe sklepy
			foreach( $this->model_setting_store->getStores() as $result ) {
				$this->model_setting_setting->editSetting($this->_name . '_settings', $dataPost, $result['store_id']);
			}	
			
			if( $this->_writableCss() ) {
				$this->_updateCss( $dataPost[$this->_name . '_settings'] );
			}
			
			if( $this->_hasMFilterPlus() ) {
				$before = empty( $this->data['settings']['attribute_separator'] ) ? '' : $this->data['settings']['attribute_separator'];
				$after = empty( $dataPost['mega_filter_settings']['attribute_separator'] ) ? '' : $dataPost['mega_filter_settings']['attribute_separator'];

				if( $before != $after ) {
					$this->redirect($this->url->link('module/' . $this->_name . '/installprogress', '', 'SSL'));
				}
			}
			
			$this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success');
            $result_json['success'] = '1';
            
            $this->response->setOutput(json_encode($result_json));
            return;

			//$this->redirect($this->url->link('module/' . $this->_name . '/settings', 'token=' . $this->session->data['token'], 'SSL'));
		}
		
		if( ! isset( $this->data['settings']['in_stock_status'] ) )
			$this->data['settings']['in_stock_status'] = 7;
		
		$this->load->model('design/layout');
		$this->data['layouts'] = $this->model_design_layout->getLayouts();
		
		$this->load->model('localisation/stock_status');
		$this->data['stockStatuses'] = $this->model_localisation_stock_status->getStockStatuses(array());
		$this->data['mfp_plus_version'] = $this->config->get('mfilter_plus_version');
				
		$this->response->setOutput( $this->render() );
	}
	
	/**
	 * Ustaw błędy
	 * 
	 * @param array $errors
	 */
	private function _setErrors( $errors ) {
		foreach( $errors as $name => $default ) {
			$this->data['_error_' . $name] = isset( $this->error[$name] ) ? $this->error[$name] : $default;
		}
	}
	
	/**
	 * Ustaw parametry
	 * 
	 * @param array $params
	 * @param array $record
	 */
	private function _setParams( $params, $record = NULL ) {
		if( ! $record )
			$record = array();

		foreach( $params as $param => $default ) {
			if( isset( $this->request->post[$param] ) )
				$this->data[$param] = $this->request->post[$param];
			else if( isset( $record[$param] ) )
				$this->data[$param] = $record[$param];
			else
				$this->data[$param] = $default;
		}
	}
	
	private function _clearCache( $messages ) {
		if( ! $this->_cacheWritable() ) {
			if( $messages ) {
				$this->session->data['error'] = $this->language->get('error_cache_dir');
			}
		} else {	
			$dir	= @ opendir( $this->_cache_dir );
			$items	= 0;

			while( false !== ( $file = readdir( $dir ) ) ) {
				if( strlen( $file ) > 32 && strpos( $file, 'idx.' ) === 0 ) {
					@ unlink( $this->_cache_dir . '/' . $file );

					$items++;
				}
			}

			closedir( $dir );

			if( $messages ) {
				$this->session->data['success'] = sprintf( $this->language->get('success_cache_clear'), $items );
			}
		}
	}
	
	public function clearcache() {
		$this->language->load('module/' . $this->_name);
		
		$this->_clearCache( true );
		
		$this->redirect($this->url->link('module/mega_filter/settings', '', 'SSL'));
	}
			
	public function category_autocomplete() {
		$json = array();
		
		if (isset($this->request->get['filter_name'])) {
			$this->load->model('catalog/category');
			
			$data = array(
				'filter_name' => $this->request->get['filter_name'],
				'start'       => 0,
				'limit'       => 20
			);
			
			$results = $this->model_catalog_category->getCategories_MF($data);
				
			foreach ($results as $result) {
				$json[] = array(
					'category_id' => $result['category_id'], 
					'name'        => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
				);
			}		
		}
		
		$sort_order = array();
	  
		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->setOutput( '<div id="json-encode">' . base64_encode( json_encode( $json ) ) . '</div>' );
	}
	
	/**
	 * About
	 */
	public function about() {
		$this->_init( 'about' );
		
		$this->data['ext_version'] = $this->_version;
		$this->data['action']	= $this->url->link('module/' . $this->_name . '/about', '', 'SSL');
		
		if( $this->config->get('mfilter_plus_version') ) {
			$this->data['plus_version'] = $this->config->get('mfilter_plus_version');
			$this->data['action_rebuild_index'] = $this->data['action']	= $this->url->link('module/' . $this->_name . '/installprogress', '', 'SSL');
		}
		
		$this->response->setOutput( $this->render() );
	}
	
	private function _installMFilterPlus() {
		if( ! $this->_hasMFilterPlus() )
			return false;
		
		$this->load->library('mfilter_plus');
		
		if( Mfilter_Plus::getInstance( $this )->install() ) {
			return ['mega_filter_init_install_url'=>$this->url->link('module/' . $this->_name . '/installprogress', '', 'SSL')->__toString()];
		}
	}
	
	public function installprogress() {
		if( ! $this->_hasMFilterPlus() ) {
			$this->redirect($this->url->link('module/' . $this->_name, '', 'SSL'));
		}
		
		if( $this->request->server['REQUEST_METHOD'] == 'POST' && $this->checkPermission() ) {
			$this->load->library('mfilter_plus');
			
			echo '<div id="json-encode">' . base64_encode( json_encode( Mfilter_Plus::getInstance( $this )->installprogress() ) ) . '</div>';
			
			return;
		}
		
		$this->_init( 'installprogress' );
		
		$this->data['progress_action'] = $this->url->link('module/' . $this->_name.'/installprogress', '', 'SSL');
		$this->data['main_action'] = $this->url->link('module/' . $this->_name, '', 'SSL');
		
		$this->response->setOutput( $this->render() );
	}
	
	/**
	 * Instalacja
	 */
	public function install() {		
		$this->language->load('module/' . $this->_name);
		
		// załaduj modele
		$this->load->model('setting/setting');
		$this->load->model('setting/extension');
		$this->load->model('localisation/language');
		
		$titles = array();
		
		foreach( $this->model_localisation_language->getLanguages() as $language ) {
			$titles[$language['language_id']] = 'Advanced filter';
		}
			
		/**
		 * Wprowadź domyślne ustawienia 
		 */
		$this->model_setting_setting->editSetting($this->_name . '_module', array(
			$this->_name . '_module' => array(
				1 => array(
					'title'			=> $titles,
					'layout_id'		=> array( '3' ),
					'store_id'		=> array( 0 ),
					'position'		=> 'column_left',
					'status'		=> '1',
					'sort_order'	=> ''
				)
			)
		));
		
		$this->model_setting_setting->editSetting($this->_name . '_settings', array(
			$this->_name . '_settings' => array(
				'show_number_of_products'		=> '1',
				'calculate_number_of_products'	=> '1',
				'show_loader_over_results'		=> '1',
				'css_style'					=> '',
				'content_selector'			=> '#mfilter-content-container',
				'refresh_results'			=> 'immediately',
				'attribs'					=> array(
					'price'		=> array(
						'enabled'		=> '1',
						'sort_order'	=> '-1'
					)
				),
				'layout_c'					=> '3',
				'display_live_filter'		=> array(
					'items' => '1'
				)
			)
		));
		
		/**
		 * Sprawdź czy wtyczka jest na liście 
		 */
		if( ! in_array( $this->_name, $this->model_setting_extension->getInstalled('module') ) ) {
			$this->model_setting_extension->install('module', $this->_name);
		}
		
		/**
		 * Sprawdź czy jest duplikat na liście
		 */
		$idx = 0;
		foreach( $this->db->query( "SELECT * FROM " . DB_PREFIX . "extension WHERE code='mega_filter' AND type='module'")->rows as $row ) {
			if( $idx ) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "extension WHERE extension_id='" . (int) $row['extension_id'] . "'");
			}
			
			$idx++;
		}
			
		$data=$this->_installMFilterPlus();
		$this->session->data['success'] = $this->language->get('success_install');

        if (!empty($data['mega_filter_init_install_url']))
            return $data;

		$this->redirect($this->url->link('module/' . $this->_name, '', 'SSL'));
	}
	
	/**
	 * Deinstalacja
	 */
	public function uninstall() {
		$this->language->load('module/' . $this->_name);	
			
		/**
		 * Sprawdź czy wtyczka jest na liście 
		 */
		$this->load->model('setting/extension');
		$this->load->model('setting/store');
		
		$this->db->query("
			DELETE FROM
				`" . DB_PREFIX . "setting`
			WHERE
				`key` IN('mega_filter_module','mfilter_version','mega_filter_attribs','mega_filter_settings','mega_filter_filters','mega_filter_options','mfilter_plus_version','mfilter_mijoshop','mfilter_aceshop') OR
				`key` REGEXP '^mega_filter_at_img_[0-9]+_[0-9]+$' OR 
				`key` REGEXP '^mega_filter_at_sort_[0-9]+_[0-9]+$'
		");
			
		if( in_array( $this->_name, $this->model_setting_extension->getInstalled('module') ) )
			$this->model_setting_extension->uninstall('module', $this->_name);
		
		if( file_exists( DIR_SYSTEM . 'library/mfilter_plus.php' ) ) {
			$this->load->library( 'mfilter_plus' );
			Mfilter_Plus::getInstance( $this )->uninstall();
		}
		
		foreach( $this->_mijoshop_update as $file => $changes ) {
			$file = realpath( DIR_SYSTEM . $file );
			
			if( file_exists( $file . '_backup_mf' ) ) {
				if( copy( $file . '_backup_mf', $file ) ) {
					unlink( $file . '_backup_mf' );
				}
			}
		}
		
		foreach( $this->_aceshop_update as $file => $changes ) {
			$file = realpath( DIR_SYSTEM . $file );
			
			if( file_exists( $file . '_backup_mf' ) ) {
				if( copy( $file . '_backup_mf', $file ) ) {
					unlink( $file . '_backup_mf' );
				}
			}
		}
		
		$this->session->data['success'] = $this->language->get('success_uninstall');

		// przekieruj do listy modułów
		$this->redirect( $this->url->link('marketplace/home', '', 'SSL') );
	}
	
	/**
	 * Zweryfikuj prawnienia
	 * 
	 * @return boolean
	 */
	protected function hasPermission() {
		if( ! $this->user->hasPermission('modify', 'module/' . $this->_name) )
			return false;
		
		return true;
	}
	
	protected function checkPermission() {
		if( ! $this->hasPermission() ) {
			$this->_setErrors(array(
				'warning'	=> $this->language->get( 'error_permission' )
			));
			
			return false;
		}
		
		return true;
	}
}
