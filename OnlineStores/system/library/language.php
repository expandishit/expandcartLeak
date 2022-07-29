<?php

use \ExpandCart\Foundation\Exceptions\FileException;
use \ExpandCart\Foundation\Http\PreAction;

class Language
{
    private $default = 'english';
    private $defaultcode = 'en';
    private $directory;
    private $db=null;
    private $config=null;
    private $data = array();
    private $url=null;
    private $session=null;
    private $request=null;
    private $preAction=null;
    private $fileName;

    public function __construct($directory, $registry=null)
    {
        $this->directory = $directory;
        if(!is_null($registry)) {
            $this->db = $registry->get('db');
            $this->config = $registry->get('config');

            //New added for changeLanguage()
            //$this->preAction = $registry->get('preAction');
            //if(!$this->preAction)
                $this->preAction = new PreAction($registry);
            $this->url = $registry->get('url');
            $this->session = $registry->get('session');
            $this->request = $registry->get('request');
        }
    }

    public function get($key, $emptyIfNotExist = false)
    {
        return (isset($this->data[$key]) ? $this->data[$key] : (!$emptyIfNotExist ? $key : ""));
    }
    
    // get all loaded files
    public function getAll()
    {
        return $this->data;
    }

    public function setDirectory($directory)
    {
        $this->directory = $directory;

        return $this;
    }

    public function load($filename)
    {
        $directory_mapp = ['ar' => 'arabic', 'en' => 'english', 'fr' => 'french'];

        $file = DIR_LANGUAGE . $this->directory . '/' . $filename . '.php';
        
        if (file_exists($file)) {
            $_ = array();

            require($file);

            $this->data = array_merge($this->data, $_);

            $this->fileName = $file;

            return $this->data;
        }
       
        //Get lang code mapping
        $directory_mapp = ['ar' => 'arabic', 'en' => 'english', 'fr' => 'french'];
        
        $file = DIR_LANGUAGE . $directory_mapp[$this->directory] . '/' . $filename . '.php';

        if (file_exists($file)) {
            $_ = array();

            require($file);

            $this->data = array_merge($this->data, $_);

            $this->fileName = $file;

            return $this->data;
        }
        /////////////////////

        $file = DIR_LANGUAGE . $this->default . '/' . $filename . '.php';

        if (file_exists($file)) {
            $_ = array();

            require($file);

            $this->data = array_merge($this->data, $_);

            $this->fileName = $file;

            return $this->data;
        } else {
            // throw new FileException('Error: Could not load language ' . $filename . '!');
            trigger_error('Error: Could not load language ' . $filename . '!');
            //	exit();
        }
    }

    /**
     * THIS FUNCTION ABOUT GET THE TEXT FROM TERMINOLOGY APP IF EXIST
     * ELSE GET THE TEXT FROM THE LANGUAGE FILE
     *
     * @param $key the needle to search in terminology app
     * @param $replacment the replacement text if the key not exist
     * @return mixed|string
     */
    public function localize($key, $replacment)
    {
        $suffix = '';
        if ($this->get('code') != 'en') {
            $specifiedLang = $this->get('code');
            $suffix = "_{$specifiedLang}";
        }

        $localizedText = $this->config->get($key.$suffix);
        return $localizedText ? $localizedText : $this->get($replacment);
    }

    public function load_json($filename, $theme_default = false, $baseDir = null)
    {
        $DIR = $baseDir ? $baseDir : DIR_LANGUAGE;
        ///Load system global language file
        $system_gl_file = $DIR . "global.{$this->directory}.json";
        $content_gl_file = "";
        if (file_exists($system_gl_file))
        {
            $file_content = file_get_contents( $system_gl_file );
            $content_gl_file = json_decode( $file_content, true );
            $this->data = array_merge( $this->data, $content_gl_file );
        }

        $custom_template_file = [];

        $dirTemplate = (IS_CUSTOM_TEMPLATE == 1 ? DIR_CUSTOM_TEMPLATE : DIR_TEMPLATE);

        $custom_template_file[] = $dirTemplate . 'customtemplates';
        $custom_template_file[] = STORECODE;
        $custom_template_file[] = CURRENT_TEMPLATE;
        $custom_template_file[] = 'language';
        $custom_template_file[] = $filename . '.' . $this->directory . '.json';

        $custom_template_file = implode('/', $custom_template_file);

        $template_file = $dirTemplate . CURRENT_TEMPLATE . '/language/' . $filename . '.' . $this->directory . '.json';
        $file = $DIR . $filename . '.' . $this->directory . '.json';

        
        if (file_exists($custom_template_file)) {
            $file = $custom_template_file;
        } elseif (file_exists($template_file)) {
            $file = $template_file;
        }elseif (!file_exists($file)) {
            $file = $DIR . $filename . '.' . $this->defaultcode . '.json';
        }


        if (file_exists($file)) {
            $_ = array();
            $_ = json_decode(file_get_contents($file), true);

            $locz_keys = [  
                            "button_cart",
                            "button_req_quote",
                            "entry_zone",
                            "ms_account_sellerinfo_zone",
                            "text_invoice_sub_total"
                         ];
                         
            $_ = $this->addNewTerminologies($locz_keys, $_, $content_gl_file);

            $this->data = array_merge($this->data, $_);
            $this->fileName = $file;
        } else {
            // throw new FileException('Error: Could not load language ' . $filename . '!');
            trigger_error('Error: Could not load language ' . $filename . '!');
        }
        
        // Load theme default language file
        if($theme_default){
            $theme_default_file = DIR_TEMPLATE . 'default/language/' . $filename . '.' . $this->directory . '.json';
            if (file_exists($theme_default_file))
            {
                $file_content = file_get_contents( $theme_default_file );
                $content_theme_default = json_decode( $file_content, true );
                $this->data = array_merge( $this->data, $content_theme_default );
            }
        }
        
        return $this->data;
    }


    public function getDirectory()
    {
        return $this->directory;
    }


    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * This Function Will add New Terminologies for the given properties.
     * if the property doesn't Exist read it from the global file content.
     *
     * @param array      # Needles.
     * @param mixed $_    # Properties array.
     * @param mixed $content_gl_file # Global file to get the default Property if not exist as a Terminology.
     * @return mixed
     */
    public function addNewTerminologies($properties, $_, $content_gl_file)
    {
        $localizationDic = array();

        if (!is_null($this->db)) {
            $localizationQuery = $this->db->query(
                "SELECT `key`,`value` FROM " . DB_PREFIX . "setting WHERE store_id = '0' AND `group` = 'localization'"
            );
            if ($localizationQuery) {
                foreach ($localizationQuery->rows as $row) {
                    $localizationDic[$row['key']] = $row['value'];
                }
            }
        }

        $suffix = $this->directory != "en" ? '_' . $this->directory : '';
        foreach ($properties as $property){
            $_[$property] = isset($localizationDic[$property . $suffix]) && trim($localizationDic[$property . $suffix]) != null ?
                $localizationDic[$property . $suffix] :
                $content_gl_file[$property];
        }
        return $_;
    }
    //convert arabic numbers to english numbers
    public function convertArToEn($telephone)
    {
        return strtr($telephone, array('۰'=>'0', '۱'=>'1', '۲'=>'2', '۳'=>'3', '۴'=>'4', '۵'=>'5', '۶'=>'6', '۷'=>'7', '۸'=>'8', '۹'=>'9', '٠'=>'0', '١'=>'1', '٢'=>'2', '٣'=>'3', '٤'=>'4', '٥'=>'5', '٦'=>'6', '٧'=>'7', '٨'=>'8', '٩'=>'9'));
   
    }

    /**
     * Change store language
     *
     * @param $post_lang_code
     * @return void
     */
    public function changeLanguage($post_lang_code){
        //$post_lang_code = $this->request->post['language_code'];
        $this->session->data['recalc_shipping'] = 1;
        $sessionLanguage = $this->session->data['language'];

        $this->session->data['language'] = $post_lang_code;
        $expand_seo = $this->preAction->isActive();
        if (isset($this->request->post['redirect']) && isset($this->request->get['_route_']) && !$expand_seo) {
            $this->redirect($this->request->post['redirect']);
        } else if (isset($_SERVER['HTTP_REFERER']) && !$expand_seo) {
            $this->redirect($_SERVER['HTTP_REFERER']);
        } else {
            if ($expand_seo) {
                $_GET['_route_'] = str_replace(
                    $this->session->data['language'],
                    $post_lang_code,
                    $_GET['_route_']
                );

                $newRoute = $this->preAction->getRoute($expand_seo, $this->request->get['_route_']);

                parse_str(html_entity_decode(parse_url($this->request->get)), $queryArray);

                $newurl = $this->preAction->getURL($expand_seo, $newRoute['data'], $newRoute['parameters']);

                if ($newurl && $newurl != '/') {
                    $this->redirect(
                        '/' . preg_replace(
                            "#^" . $sessionLanguage . "\/#",
                            $post_lang_code.'/',
                            $newurl
                        )
                    );
                } else {
                    $this->redirect($this->url->link('common/home'));
                }

            } else {
                $this->redirect($this->url->link('common/home'));
            }
        }
    }

    private function redirect($url, $status = 302) {
        header('Status: ' . $status);
        header('Location: ' . str_replace(array('&amp;', "\n", "\r"), array('&', '', ''), $url));
        exit();
    }
}

