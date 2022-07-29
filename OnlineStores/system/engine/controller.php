<?php

use \ExpandCart\Foundation\Exceptions\FileException;

abstract class Controller {
	protected $registry;	
	protected $id;
	protected $layout;
	protected $template;
	protected $children = array();
	protected $base;
	protected $data = array();
	protected $output;
    public static $user_permissions = array();

	public function __construct($registry) {
		$this->registry = $registry;

		####START: pro filter#####################
		if( NULL != ( $mfSettings = $this->config->get('mega_filter_settings') )) {
			if (!empty($this->request->get['mfp'])) {
				preg_match('/path\[([^]]*)\]/', $this->request->get['mfp'], $mf_matches);

				if (!empty($mf_matches[1])) {
					$this->request->get['path'] = $mf_matches[1];

					if (isset($this->request->get['category_id']) || (isset($this->request->get['route']) && in_array($this->request->get['route'], array('product/search', 'product/special', 'product/manufacturer/info')))) {
						$mf_matches = explode('_', $mf_matches[1]);
						$this->request->get['category_id'] = end($mf_matches);
					}
				}

				unset($mf_matches);
			}
		}
		####END: pro filter#####################
	}
	
	public function __get($key) {
		return $this->registry->get($key);
	}
	
	public function __set($key, $value) {
		$this->registry->set($key, $value);
	}
			
	protected function forward($route, $args = array()) {
		return new Action($route, $args);
	}

	protected function redirect($url, $status = 302) {
		header('Status: ' . $status);
		header('Location: ' . str_replace(array('&amp;', "\n", "\r"), array('&', '', ''), $url));
		exit();
	}
	
	protected function getChild($child, $args = array()) {
		$action = new Action($child, $args);
	
		if (file_exists($action->getFile())) {
			require_once($action->getFile());

			$class = $action->getClass();

			$controller = new $class($this->registry);

            if (isset($args['data']) && is_array($args['data'])){
                $controller->data = $args['data'];
            }
            
			$controller->{$action->getMethod()}($action->getArgs());

			if(strpos($child, "common/") !== 0 && strpos($child, "module/") !== 0) {
                $this->document->setChildData($controller->data);
            }

			return $controller->output;
		} else {
            throw new FileException('Error: Could not load controller ' . $child . '!');
			trigger_error('Error: Could not load controller ' . $child . '!');
			exit();					
		}		
	}

    protected function getBaseData($base, $args = array()) {
        $action = new Action($base, $args);

        if (file_exists($action->getFile())) {
            require_once($action->getFile());

            $class = $action->getClass();

            $controller = new $class($this->registry);

            $controller->{$action->getMethod()}($action->getArgs());

            return $controller->data;
        } else {
            throw new FileException('Error: Could not load controller ' . $base . '!');
            trigger_error('Error: Could not load controller ' . $base . '!');
            exit();
        }
    }

    public static function routesToBeRendered( $user_perms )
    {
        $general_routes = Controller::getSettingsRoutes('general');
        $localisation_routes = Controller::getSettingsRoutes('localisation');
        $user_routes = Controller::getSettingsRoutes('user');
        $advanced_routes = Controller::getSettingsRoutes('advanced');
        
        $menu_items = array();

        foreach ($user_perms as $perm)
        {
            if ( in_array($perm, $general_routes) )
            {
                $menu_items['display_general'] = true;
            }
            else if ( in_array($perm, $localisation_routes) )
            {
                $menu_items['display_localisation'] = true;
            }
            else if ( in_array($perm, $user_routes) )
            {
                $menu_items['display_user'] = true;
            }
            else if ( in_array($perm, $advanced_routes) )
            {
                $menu_items['display_advanced'] = true;
            }
        }

        return $menu_items;
    }

    public static function getSettingsRoutes( $type = 'all' )
    {
        $routes['general'] = ['setting/store_general', 'extension/shipping', 'extension/payment', 'localisation/tax_rate', 'setting/domains','setting/store_affiliates'];
        $routes['localisation'] = ['setting/language', 'localisation/currency', 'localisation/language', 'localisation/country_city', 'localisation/geo_zone', 'setting/store_units'];
        $routes['user'] = ['user/user', 'setting/store_account'];
        $routes['advanced'] = ['setting/store_products', 'setting/store_checkout', 'setting/store_stock', 'setting/store_returns', 'setting/store_taxes', 'setting/store_vouchers', 'extension/total', 'localisation/statuses', 'setting/store_scripts', 'setting/store_items', 'setting/setting_mail', 'setting/integration', 'sale/customer_ban_ip'];

        $routes['all'] = array_merge($routes['general'], $routes['localisation'], $routes['user'], $routes['advanced']);
        
        return $routes[ $type ];
    }

	protected function render() {

	    ###TODO: Remove after backend revoke
	    if($this->template != "common/header.tpl" && $this->template != "common/footer.tpl") {
            if (IS_ADMIN) {
                return $this->render_ecwig();
            }
        }
        if (IS_EXPANDISH_FRONT && !IS_ADMIN && 0 != strpos($this->template, 'default/template/multiseller/')) {
             unset($this->children);
             $this->children = array();
         }

		foreach ($this->children as $child) {
			#(replaced with the following)#$this->data[basename($child)] = $this->getChild($child);
			####START: pro filter#############
			if( NULL != ( $mfSettings = $this->config->get('mega_filter_settings') )) {
				if (!isset($this->data['mfp_' . basename($child)])) {
					$this->data[basename($child)] = $this->getChild($child);
				}
			}
			else
				$this->data[basename($child)] = $this->getChild($child);
			####END: pro filter##############
		}

        $dirTemplate = (IS_CUSTOM_TEMPLATE == 1 ? DIR_CUSTOM_TEMPLATE : DIR_TEMPLATE);

        // For POS: if is a custom template and the template that come from catalog dir  is "/default/wkpos/*" then reset the
        // $dirTemplate to prevent wrong path issue like "ecdata/stores/storename/customtemplates/default/template/wkpos/*.tpl"
        if (IS_CUSTOM_TEMPLATE == 1) {
            if ((strpos($this->template, 'multiseller') || strpos($this->template, 'wkpos')) && !file_exists($dirTemplate . $this->template) ) {
                $dirTemplate = DIR_TEMPLATE;
            }
        }
        
		if (file_exists($dirTemplate . $this->template)) {
			extract($this->data);
			
      		ob_start();
      
	  		require($dirTemplate . $this->template);
      
	  		$this->output = ob_get_contents();

			ob_end_clean();

			$this->tplLangTagParser($this->output);
      		
			return $this->output;
    	} else {
            throw new FileException('Error: Could not load template ' . $dirTemplate . $this->template . '!');
			trigger_error('Error: Could not load template ' . $dirTemplate . $this->template . '!');
			exit();				
    	}
	}

	private function tplLangTagParser(&$template)
	{
		$pattern = '#\{\{\s*lang\([\'\"]*([a-z_0-9]+)[\'\"]*\)\s*\}\}#i';
		$pattern = '#\{\{\s*lang\([\'\"]*([a-z_0-9]+)[\'\"]*\,?\s*(\$[a-z\_]+[a-z0-9\_]*|[\'\"]+\w*[\'\"]+)?\s*\)\s*\}\}#i';
		$template = preg_replace_callback($pattern, function($matches) {
            if (isset($matches[2])) {
                return sprintf(
                    $this->language->get($matches[1]),
                    trim(trim($matches[2], "'"), '"')
                );
            }

			if (isset($matches[1])) {
				return $this->language->get($matches[1]);
			}
		}, $template);

		return $template;
	}

	/**
	 * Render a string ccontent from a file.
     *
     * @param string $templage
     *
     * @return string
     */
	public function renderDefaultTemplate($template)
    {
        $file = 'expandish/view/theme/default/' . $template;

        $file = file_get_contents($file);

        $env = new \Twig_Environment(new \Twig_Loader_String());

        return $env->render(
            $file,
            $this->data
        );
    }

    protected function render_ecwig()
    {
        //global $twig;
        global $twig_loader;
        //global $expandishglobals;
        if(IS_ADMIN) {
            if($this->base) {
                $this->data[basename($this->base)] = $this->getBaseData($this->base);
            }
            #TODO: Remove after revoke
            else {
                foreach ($this->children as $child) {
                    if($child == "common/header") {
                        $this->base = "common/base";
                        $this->data[basename($this->base)] = $this->getBaseData($this->base);
                    }
                }
            }
//            foreach ($this->children as $child) {
//                $this->data[basename($child)] = $this->getChild($child);
//            }
        }


        $dirTemplate = DIR_TEMPLATE;
        if (preg_match('#default#', $this->template) == false && IS_CUSTOM_TEMPLATE && !IS_ADMIN ) {
            $dirTemplate = DIR_CUSTOM_TEMPLATE;
        }

        if (file_exists($dirTemplate . $this->template)) {
            extract($this->data);

            if(DEV_MODE) {
                $twig = new Twig_Environment($twig_loader, array(
                    'autoescape' => false,
                    //'cache' => $dirTemplate . 'cache',
                    'debug' => true
                ));
                $twig->addExtension(new Twig_Extension_Debug());
            } else {
                $twig = new Twig_Environment($twig_loader, array(
                    'autoescape' => false,
                    'cache' => $dirTemplate . 'cache',
                    'debug' => false
                ));
            }

            if(!IS_ADMIN) {
                $twig->addExtension(new Twig_Extension_ExpandcartGlobals($this->registry)); //$expandishglobals);
                $twig->addExtension(new Twig_Extension_Expandcart($this->registry));
                $twig->addExtension(new Twig_Extension_ExpandcartCategory($this->registry));
                $twig->addExtension(new Twig_Extension_ExpandcartProduct($this->registry));
                $twig->addExtension(new Twig_Extension_ExpandcartHtmlEntityDecode);
                $twig->addFilter(new \Twig_SimpleFilter('htmlspecialchars_decode', 'htmlspecialchars_decode'));
                $twig->addFilter(new \Twig_SimpleFilter('strip_tags', 'strip_tags'));

            } else {
                $twig->addExtension(new Twig_Extension_ExpandcartAdminGlobals($this->registry)); //$expandishglobals);
                $twig->addExtension(new Twig_Extension_ExpandcartAdmin($this->registry));
            }
            $template = $twig->load($this->template);
            //ob_start();
            //require($dirTemplate . $this->template);
            //$this->output = ob_get_contents();
            //ob_end_clean();
            ###TODO: Remove after backend revoke
            if (IS_ADMIN && strpos($this->template, '.tpl') !== false) {
                $index ='{% extends "base" %}
                         {% from "breadcrumb" import breadcrumb as breadcrumb %}
                        {% block title %}
                            {{ heading_title }}
                        {% endblock title %}
                        {% block breadcrumb %}
                            {{ breadcrumb(breadcrumbs) }}
                        {% endblock breadcrumb %}
                        {% block content %}' . file_get_contents(DIR_TEMPLATE . $this->template) . '{% endblock content %}';
                $base = file_get_contents(DIR_TEMPLATE . 'base.expand');
                $breadcrumb = file_get_contents(DIR_TEMPLATE . 'controls/breadcrumb.expand');
//                $loader1 = new Twig_Loader_Array(array(
//                    'base' => $base,
//                ));
//                $loader2 = new Twig_Loader_Array(array(
//                    'index' => $index,
//                    'base'  => 'Will never be loaded',
//                ));
//
//                $loader = new Twig_Loader_Chain(array($loader1, $loader2));
//
//                $twig = new Twig_Environment($loader);
                $loader = new Twig_Loader_Array(array(
                    'base' => $base,
                    'breadcrumb' => $breadcrumb,
                    'index' => $index
                ));
                $twig = new Twig_Environment($loader);
                $twig->addExtension(new Twig_Extension_ExpandcartAdminGlobals($this->registry)); //$expandishglobals);
                $twig->addExtension(new Twig_Extension_ExpandcartAdmin($this->registry));
                $this->output = $twig->render('index', $this->data);
            } else {
                $this->output = $template->render($this->data);
            }
            ###TODO: Remove after backend revoke
            if(IS_ADMIN) {
                /*ob_start();
                eval('?>' . $this->output);
                $this->output = ob_get_contents();
                ob_end_clean();*/
            }
            return $this->output;
        } else {
            throw new FileException(
                'Error: Could not load template ' . $dirTemplate . $this->template . '!'
            );
            trigger_error('Error: Could not load template ' . $dirTemplate . $this->template . '!');
            exit();
        }
    }

    /**
     * check if the given template is exists in the following paths: customtemplates/, default/, configTemplate/.
     *
     * @param string $template
     *
     * @return string
     */
    public function checkTemplate($template)
    {
        $customFilePath = ('customtemplates/' . STORECODE . '/' .
            $this->config->get('config_template') . '/template/' . $template);

        $defaultFilePath = 'default/template/' . $template;

        $templatePath = $this->config->get('config_template') . '/template/' . $template;

        $dirTemplate = (IS_CUSTOM_TEMPLATE == 1 ? DIR_CUSTOM_TEMPLATE : DIR_TEMPLATE);

        if(file_exists(DIR_TEMPLATE . $customFilePath)) {
            $this->template = $customFilePath;
        } else if(file_exists($dirTemplate . $templatePath)) {
            $this->template = $templatePath;
        } else {
            $this->template = $defaultFilePath;
        }

        return $this->template;
    }

    /**
     * initialize the models & languages and assign it to a spesific property.
     *
     * @param array $models
     * @param array $languages
     *
     * @return \stdClass
     */
    protected function initializer($models, $languages = null)
    {
        $objects = new stdClass;

        if (isset($languages)) {

            $objects->languages = new stdClass;

            if (is_array($languages) === false) {
                $languages = [$languages];
            }

            foreach ($languages as $key => $language) {

                if (preg_match('#^[a-z\_]+[a-z0-9\_]*#i', $key)) {
                    $object = $key;
                } else {
                    $object = explode('/', $language);
                    $object = end($object);
                }

                $objects->languages->$object = $this->language->load_json($language);
            }
        }

        if (isset($models)) {

            $objects->models = new stdClass;

            if (is_array($models) === false) {
                $models = [$models];
            }

            foreach ($models as $key => $model) {
                $this->load->model($model);

                if (preg_match('#^[a-z\_]+[a-z0-9\_]*#i', $key)) {
                    $object = $key;
                } else {
                    $object = explode('/', $model);
                    $object = end($object);
                }

                $model = str_replace('/', '_', $model);
                $objects->models->$object = $this->$object = $this->{"model_" . $model};
            }
        }

        return $objects;
    }

    public function getDateByCurrentTimeZone($date_added){

        $original_timezone = new DateTimeZone('UTC');
        $datetime = new DateTime($date_added, $original_timezone);
        $config_timezone = $this->config->get('config_timezone');
        $target_timezone = new DateTimeZone($config_timezone ? $config_timezone : 'UTC');
        $datetime->setTimeZone($target_timezone);
        return $datetime->format('Y-m-d H:i:s');
    }

    public function getCountries($countries_string){
        $return = null;
        $allowed = explode(',',$countries_string);
        $str = file_get_contents(
            ONLINE_STORES_PATH."/OnlineStores/admin/language/countries_locale.json"
        );

        $my_array = json_decode($str,true);

        $filtered = array_intersect_key($my_array, array_flip($allowed));

        foreach ($filtered as $item){
            $return[]['name'] = $item[$this->config->get('config_admin_language')];
        }
        return $return;
    }

}
?>
