<?php
/**
 * Opencart Extension class.
 *
 * This class is used by Opencart as a twig extension and must not be used directly.
 *
 */
class Twig_Extension_Expandcart extends Twig_Extension
{
    protected $registry;
    protected $is_admin;
    protected $template_dir = DIR_TEMPLATE;
    /**
     * @param Registry $registry
     */
    public function __construct(\Registry $registry, $template_dir = null) {
        $this->registry = $registry;
        $this->is_admin = defined('DIR_CATALOG');
        if($template_dir){
            $this->template_dir = $template_dir;
        }
    }
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('style', array($this, 'styleFunction')),
            //new \Twig_SimpleFunction('getStyles', array($this, 'getstylesFunction')),
            new \Twig_SimpleFunction('script', array($this, 'scriptFunction')),
            new \Twig_SimpleFunction('jslink', array($this, 'jslinkFunction')),
            new \Twig_SimpleFunction('csslink', array($this, 'jslinkFunction')),
            new \Twig_SimpleFunction('commonLink', array($this, 'loadCommonAsset')),
            //new \Twig_SimpleFunction('getScripts', array($this, 'getscriptsFunction')),
            new \Twig_SimpleFunction('link', array($this, 'linkFunction')),
            //new \Twig_SimpleFunction('getLinks', array($this, 'getlinksFunction')),
            //new \Twig_SimpleFunction('getTitle', array($this, 'gettitleFunction')),
            //new \Twig_SimpleFunction('getDescription', array($this, 'getdescriptionFunction')),
            //new \Twig_SimpleFunction('getKeywords', array($this, 'getkeywordsFunction')),
            new \Twig_SimpleFunction('lang', array($this, 'langFunction')),
            new \Twig_SimpleFunction('localize', array($this, 'localizeFunction')),
            new \Twig_SimpleFunction('lang_printf', array($this, 'printf_langFunction')),
            new \Twig_SimpleFunction('config', array($this, 'configFunction')),
            new \Twig_SimpleFunction('paginate', array($this, 'paginateFunction')),
            new \Twig_SimpleFunction('image', array($this, 'imageFunction')),
            new \Twig_SimpleFunction('asset', array($this, 'assetFunction')),
            new \Twig_SimpleFunction('load', array($this, 'loadFunction')),
            new \Twig_SimpleFunction('can_access', array($this, 'canAccessFunction')),
            new \Twig_SimpleFunction('can_modify', array($this, 'canModifyFunction')),
            new \Twig_SimpleFunction('print_r', array($this, 'print_var')),
            new \Twig_SimpleFunction('htmlEntityDecode', array($this, 'htmlEntityDecode')),
            new \Twig_SimpleFunction('groupProducts', array($this, 'groupProducts')),
            new \Twig_SimpleFunction('isExtenionInstalled', array($this, 'isExtenionInstalled')),
            new \Twig_SimpleFunction('isset', array($this, 'isset')),
            new \Twig_SimpleFunction('setObjectProperty', array($this, 'setObjectProperty')),
            new \Twig_SimpleFunction('toObject', array($this, 'toObject')),
        );
    }

    public function isExtenionInstalled($name)
    {
        return \Extension::isInstalled($name);
    }

    public function groupProducts($products, $groupBy, $group = false)
    {
        if($group){
            foreach ($products as $prd) {
                $finalProducts[$prd[$groupBy]][] = $prd;
            }
        }
        else{
            $finalProducts[0] = $products;
        }

        return $finalProducts;
    }

    public function htmlEntityDecode($var)
    {
        return html_entity_decode($var);
    }

    public function styleFunction($path = null) {
        $document = $this->registry->get('document');
        $filePath = "";
        if(file_exists($this->template_dir . "customtemplates/" . STORECODE . "/" . CURRENT_TEMPLATE . "/" . $path)) {
            $filePath = "expandish/view/theme/customtemplates/" . STORECODE . "/" . CURRENT_TEMPLATE . "/" . $path;
        }
        elseif(file_exists($this->template_dir . CURRENT_TEMPLATE . "/" . $path)) {
            $filePath = "expandish/view/theme/" . CURRENT_TEMPLATE . "/" . $path;
        } elseif (file_exists(DIR_CUSTOM_TEMPLATE . CURRENT_TEMPLATE . "/" . $path)) {
            // $filePath = "expandish/view/custom/" . STORECODE . '/' . CURRENT_TEMPLATE . "/" . $path;
            $filePath = CUSTOM_TEMPLATE_PATH . CURRENT_TEMPLATE . "/" . $path;
        } else {
            $filePath = "expandish/view/" . $path;
        }
        //return '<link rel="stylesheet" type="text/css" href="' . $filePath . '" media="screen" />';
        $document->addStyle($filePath);
    }

    /*public function getstylesFunction() {
        $document = $this->registry->get('document');
        $styles = $document->getStyles();
        if($styles == null) {
            $styles = array();
        }
        return $styles;
    }*/

    public function scriptFunction($path = null) {
        $document = $this->registry->get('document');
        $filePath = "";
        if(file_exists($this->template_dir . "customtemplates/" . STORECODE . "/" . CURRENT_TEMPLATE . "/" . $path)) {
            $filePath = "expandish/view/theme/customtemplates/" . STORECODE . "/" . CURRENT_TEMPLATE . "/" . $path;
        }
        elseif(file_exists($this->template_dir . CURRENT_TEMPLATE . "/" . $path)) {
            $filePath = "expandish/view/theme/" . CURRENT_TEMPLATE . "/" . $path;
        } elseif (file_exists(DIR_CUSTOM_TEMPLATE . CURRENT_TEMPLATE . "/" . $path)) {
            // $filePath = "expandish/view/custom/" . STORECODE . '/' . CURRENT_TEMPLATE . "/" . $path;
            $filePath = CUSTOM_TEMPLATE_PATH . '/' . CURRENT_TEMPLATE . "/" . $path;
        } else {
            $filePath = "expandish/view/" . $path;
        }
        $document->addScript($filePath);
    }

    public function jslinkFunction($path = null) {

        if (isset($this->registry->get('request')->get['__p'])) {
            $template_codename = $this->registry->get('request')->get['__p'];
        }
        else{
            $template_codename = CURRENT_TEMPLATE;
        }
        if(file_exists($this->template_dir . "customtemplates/" . STORECODE . "/" . $template_codename . "/" . $path)) {
            $filePath = "expandish/view/theme/customtemplates/" . STORECODE . "/" . $template_codename . "/" . $path;
        } elseif (file_exists(DIR_CUSTOM_TEMPLATE . $template_codename . "/" . $path)) {
            // $filePath = "expandish/view/custom/" . STORECODE . '/' . $template_codename . "/" . $path;
            $filePath = CUSTOM_TEMPLATE_PATH . '/' . $template_codename . "/" . $path;
        }
        else {
            $filePath = "expandish/view/theme/" . $template_codename . "/" . $path;
        }

        return $filePath;
    }

    public function loadCommonAsset($path = null)
    {
        if(file_exists($this->template_dir . "customtemplates/" . STORECODE . "/" . CURRENT_TEMPLATE . "/" . $path)) {
            $filePath = "expandish/view/theme/customtemplates/" . STORECODE . "/" . CURRENT_TEMPLATE . "/" . $path;
        } elseif (file_exists(DIR_CUSTOM_TEMPLATE . CURRENT_TEMPLATE . "/" . $path)) {
            // $filePath = "expandish/view/custom/" . STORECODE . '/' . CURRENT_TEMPLATE . "/" . $path;
            $filePath = CUSTOM_TEMPLATE_PATH . '/' . CURRENT_TEMPLATE . "/" . $path;
        }
        else {
            $filePath = "expandish/view/theme/default/" . $path;
        }

        return $filePath;
    }

    /*public function getscriptsFunction() {
        $document = $this->registry->get('document');
        $scripts = $document->getScripts();
        if($scripts == null) {
            $scripts = array();
        }
        return $scripts;
    }*/

    /**
     * @param null  $route
     * @param array $args
     * @param bool  $secure
     *
     * @return string
     */
    public function linkFunction($route = null, $args = array(), $secure = false)
    {
        $url = $this->registry->get('url');
        $session = $this->registry->get('session');
        $token = isset($session->data['token']) ? $session->data['token'] : null;
        if($this->is_admin && $token) {
            $args['token'] = $token;
        }
        if(is_array($args)) {
            $args = http_build_query($args);
        }
        if(!empty($route)) {
            return $url->link($route, $args, $secure);
        } else if($secure) {
            return !empty($args) ? HTTPS_SERVER . 'index.php?' . $args : HTTPS_SERVER;
        }
        return !empty($args) ? HTTP_SERVER . 'index.php?' . $args : HTTP_SERVER;
    }

    /*public function getlinksFunction() {
        $document = $this->registry->get('document');
        $links = $document->getLinks();
        if($links == null) {
            $links = array();
        }
        return $links;
    }

    public function gettitleFunction() {
        $document = $this->registry->get('document');
        return $document->getTitle();
    }

    public function getdescriptionFunction() {
        $document = $this->registry->get('document');
        return $document->getDescription();
    }

    public function getkeywordsFunction() {
        $document = $this->registry->get('document');
        return $document->getKeywords();
    }*/
    /**
     * @param      $key
     * @param null $file
     * @param  bollean $theme_default
     *
     * @return mixed
     */
    public function langFunction($key, $file = null, $theme_default = false)
    {
        $language = $this->registry->get('language');
        if($file) {
            $language->load_json($file, $theme_default);
        }
        return $language->get($key);
    }

    public function printf_langFunction($key, $str, $file = null) {
        $language = $this->registry->get('language');
        if($file) {
            $language->load($file);
        }
        $ret = $language->get($key);
        return sprintf($ret, $str);
    }

    /**
     * @param      $key
     * @param null $file
     *
     * @return mixed
     */
    public function localizeFunction($key, $replacment, $file = null)
    {

        $suffix = '';
        $config = $this->registry->get('config');
        $language = $this->registry->get('language');

        if ($language->get('code') != 'en') {
            $specifiedLang = $language->get('code');
            $suffix = "_{$specifiedLang}";
        }

        $localizedText = $config->get($key.$suffix);
        return $localizedText ? $localizedText : $language->get($replacment);
    }

    /**
     * @param      $key
     * @param null $file
     *
     * @return mixed
     */
    public function configFunction($key, $file = null)
    {
        $config = $this->registry->get('config');
        if($file) {
            $config->load($file);
        }
        return $config->get($key);
    }
    /**
     * @param $value
     *
     * @return bool
     */
    public function canAccessFunction($value) {
        $user = $this->registry->get('user');
        if($user) {
            return $user->hasPermission('access',$value);
        }
        return false;
    }
    /**
     * @param $value
     *
     * @return bool
     */
    public function canModifyFunction($value) {
        $user = $this->registry->get('user');
        if($user) {
            return $user->hasPermission('modify',$value);
        }
        return false;
    }
    /**
     * @param        $filename
     * @param string $context
     *
     * @param null   $width
     * @param null   $height
     *
     * @return string|void
     */
    public function imageFunction($filename, $width = null, $height = null) {

        $request = $this->registry->get('request');
        // if no img
        if($filename =="" || empty($filename)) {
            return \Filesystem::getUrl('image/no_image.jpg?t=' . time());
        } ;

        if (!\Filesystem::isExists('image/' . $filename)) {
            return \Filesystem::getUrl('image/no_image.jpg?t=' .  time());
        }

        if ($width && $height) {
            $newImage = $this->registry->get('model_tool_image')->resize($filename, $width, $height);
            return $newImage;
        }

        return \Filesystem::getUrl('image/' . $filename);
    }
    public function imageFunction__($filename, $context = null /*= 'product'*/, $width = null, $height = null) {
        if (!is_file(DIR_IMAGE . $filename)) {
            return;
        }
        $request = $this->registry->get('request');
        $config = $this->registry->get('config');
        if(!$width && $context) {
            $width = $config->get('config_image_'. $context .'_width');
        }
        if(!$height && $context) {
            $height = $config->get('config_image_'. $context .'_height');
        }

        if($width && $height) {
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $old_image = $filename;
            $new_image = 'cache/' . utf8_substr($filename, 0, utf8_strrpos($filename, '.')) . '-' . $width . 'x' . $height . '.' . $extension;
            if (!is_file(DIR_IMAGE . $new_image) || (filectime(DIR_IMAGE . $old_image) > filectime(DIR_IMAGE . $new_image))) {
                $path = '';
                $directories = explode('/', dirname(str_replace('../', '', $new_image)));
                foreach ($directories as $directory) {
                    $path = $path . '/' . $directory;
                    if (!is_dir(DIR_IMAGE . $path)) {
                        @mkdir(DIR_IMAGE . $path, 0777);
                    }
                }
                list($width_orig, $height_orig) = getimagesize(DIR_IMAGE . $old_image);
                if ($width_orig != $width || $height_orig != $height) {
                    $image = new Image(DIR_IMAGE . $old_image);
                    $image->resize($width, $height);
                    $image->save(DIR_IMAGE . $new_image);
                } else {
                    copy(DIR_IMAGE . $old_image, DIR_IMAGE . $new_image);
                }
            }
        }
        else {
            $new_image = $filename;
        }
        if ($request->server['HTTPS']) {
            return HTTPS_IMAGE . $new_image;
        } else {
            return HTTPS_IMAGE . $new_image;
        }
    }
    /**
     * @param $path
     *
     * @return string
     */
    public function assetFunction($path) {
        //if(!$this->is_admin) {
            if(file_exists($this->template_dir . 'customtemplates/' . STORECODE . '/' .  $this->registry->get('config')->get('config_template') . '/' . $path)) {
                return 'expandish/view/theme/customtemplates/' . STORECODE . '/' . $this->registry->get('config')->get('config_template') . '/' . $path;
            } else {
                if (IS_CUSTOM_TEMPLATE == 1) {
                    return CUSTOM_TEMPLATE_PATH . '/' . $this->registry->get('config')->get('config_template') . '/' . $path;
                } else {
                    return 'expandish/view/theme/' . $this->registry->get('config')->get('config_template') . '/' . $path;
                }
            }
//        } else if(file_exists($this->template_dir . '../' . $path)) {
//            return 'admin/view/' . $path;
//        }
        return $path;
    }
    /**
     * @param $file
     *
     * @return mixed
     */
    public function loadFunction($file) {
        $loader = $this->registry->get('load');
        return $loader->controller($file);
    }
    /**
     * @param       $total
     * @param null  $route
     * @param array ;;;;;;;;;;;;$args
     * @param null  $template
     *
     * @return string
     */
    public function paginateFunction($total, $limit = 5, $route = null, $args = array(), $template = null)
    {
        $request = $this->registry->get('request');
        $page = isset($request->get['page']) ? $request->get['page'] : 1;
        $secure = $request->server['HTTPS'];
        $pagination = new \Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->limit = $limit;
        $args['page'] = '{page}';
        $pagination->url = $this->linkFunction($route, $args, $secure);
        if($template) {
            $loader = $this->registry->get('load');
            return $loader->view($template, get_object_vars($pagination));
        } else {
            return $pagination->render();
        }
    }
    
    public function isset($var)
    {
        return isset($var);
    }

    public function print_var($var) {
        print_r($var);
    }
    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('money', array($this, 'moneyFilter')),
            new \Twig_SimpleFilter('tax', array($this, 'taxFilter')),
            new \Twig_SimpleFilter('len', array($this, 'lenFilter')),
            new \Twig_SimpleFilter('wei', array($this, 'weiFilter')),
            new \Twig_SimpleFilter('truncate', array($this, 'truncateFilter')),
            new \Twig_SimpleFilter('encrypt', array($this, 'encryptFilter')),
            new \Twig_SimpleFilter('decrypt', array($this, 'decryptFilter')),
            new \Twig_SimpleFilter('chunk', array($this, 'arrayChunk')),
            new \Twig_SimpleFilter('base64_encode', array($this, 'base64EncodeFilter')),
            new \Twig_SimpleFilter('addslashes', array($this, 'addslashes')),
            new \Twig_SimpleFilter('html_entity_decode', array($this, 'html_entity_decode')),
            new \Twig_SimpleFilter('htmlspecialchars_decode', array($this, 'htmlspecialchars_decode')),
            new \Twig_SimpleFilter('strpad', array($this, 'strpad')),
            new \Twig_SimpleFilter('base64_decode', array($this, 'base64DecodeFilter')),
            new \Twig_SimpleFilter('html_cutter', array($this, 'html_cutter')),
        );
    }
    /**
     * @param        $number
     * @param string $currency
     * @param string $value
     * @param bool   $format
     *
     * @return mixed
     */
    public function moneyFilter($number, $currency = '', $value = '', $format = true)
    {
        $lib = $this->registry->get('currency');
        return $lib->format($number, $currency, $value, $format);
    }
    /**
     * @param      $value
     * @param      $tax_class_id
     * @param bool $calculate
     *
     * @return mixed
     */
    public function taxFilter($value, $tax_class_id, $calculate = true)
    {
        $tax = $this->registry->get('tax');
        return $tax->calculate($value, $tax_class_id, $calculate);
    }
    /**
     * @param        $value
     * @param        $length_class_id
     * @param string $decimal_point
     * @param string $thousand_point
     *
     * @return mixed
     */
    public function lenFilter($value, $length_class_id, $decimal_point = '.', $thousand_point = ',')
    {
        $length = $this->registry->get('length');
        return $length->format($value, $length_class_id, $decimal_point, $thousand_point);
    }
    /**
     * @param        $value
     * @param        $weight_class_id
     * @param string $decimal_point
     * @param string $thousand_point
     *
     * @return mixed
     */
    public function weiFilter($value, $weight_class_id, $decimal_point = '.', $thousand_point = ',')
    {
        $weight = $this->registry->get('weight');
        return $weight->format($value, $weight_class_id, $decimal_point, $thousand_point);
    }
    /**
     * @param        $value
     * @param string $end
     * @param null   $limit
     *
     * @return string
     */
    public function truncateFilter($value, $end = '...', $limit = null)
    {
        $config = $this->registry->get('config');
        if( ! $limit) {
            $limit = $config->get('config_product_description_length');
        }
        $str = strip_tags(html_entity_decode($value, ENT_QUOTES, 'UTF-8'));
        if(strlen($str) > $limit) {
            $str = utf8_substr($str, 0, $limit) . $end;
        }
        return  $str;
    }
    /**
     * @param $value
     *
     * @return string
     */
    public function encryptFilter($value)
    {
        $config = $this->registry->get('config');
        $encription = new \Encryption($config->get('config_encription'));
        return $encription->encrypt($value);
    }
    /**
     * @param $value
     *
     * @return string
     */
    public function decryptFilter($value)
    {
        $config = $this->registry->get('config');
        $encription = new \Encryption($config->get('config_encription'));
        return $encription->decrypt($value);
    }

    public function arrayChunk($arr, $size)
    {
        return array_chunk($arr, $size);
    }

    public function base64EncodeFilter($value) {
        return base64_encode($value);
    }

    public function base64DecodeFilter($value)
    {
        return base64_decode($value);
    }

    public function strpad($number, $pad_length, $pad_string) {
        return str_pad($number, $pad_length, $pad_string, STR_PAD_LEFT);
    }
    // cut html to implement see more link
    //without breaking HTML
    function html_cutter ($text,$max_length)
    {
        $tags   = array();
        $result = "";

        $is_open   = false;
        $grab_open = false;
        $is_close  = false;
        $in_double_quotes = false;
        $in_single_quotes = false;
        $tag = "";

        $i = 0;
        $stripped = 0;

        $stripped_text = strip_tags($text);

        while ($i < strlen($text) && $stripped < strlen($stripped_text) && $stripped < $max_length)
        {
            $symbol = utf8_substr($text , $i , 1);
            $result .= $symbol;

            switch ($symbol)
            {
                case '<':
                    $is_open   = true;
                    $grab_open = true;
                    break;

                case '"':
                    if ($in_double_quotes)
                        $in_double_quotes = false;
                    else
                        $in_double_quotes = true;

                    break;

                case "'":
                    if ($in_single_quotes)
                        $in_single_quotes = false;
                    else
                        $in_single_quotes = true;

                    break;

                case '/':
                    if ($is_open && !$in_double_quotes && !$in_single_quotes)
                    {
                        $is_close  = true;
                        $is_open   = false;
                        $grab_open = false;
                    }

                    break;

                case ' ':
                    if ($is_open)
                        $grab_open = false;
                    else
                        $stripped++;

                    break;

                case '>':
                    if ($is_open)
                    {
                        $is_open   = false;
                        $grab_open = false;
                        array_push($tags, $tag);
                        $tag = "";
                    }
                    else if ($is_close)
                    {
                        $is_close = false;
                        array_pop($tags);
                        $tag = "";
                    }

                    break;

                default:
                    if ($grab_open || $is_close)
                        $tag .= $symbol;

                    if (!$is_open && !$is_close)
                        $stripped++;
            }

            $i++;
        }

        while ($tags)
            $result .= "</".array_pop($tags).">";

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    /*public function getGlobals()
    {
        $document = $this->registry->get('document');
        $session = $this->registry->get('session');
        $expandish = $this->registry->get('expandish');
        $globals = array(
            'document_title' => $document->getTitle(),
            'document_description' => $document->getDescription(),
            'document_keywords' => $document->getKeywords(),
            'document_links' => $document->getLinks(),
            'document_styles' => $document->getStyles(),
            'document_scripts' => $document->getScripts(),
            'session_data' => $session->data,
            'HTTP_SERVER' => HTTP_SERVER,
            'route' => isset($this->registry->get('request')->get['route']) ? $this->registry->get('request') : '',
            'header_data' => $expandish->getHeader(),
            'footer_data' => $expandish->getFooter()
        );
        if($this->is_admin) {
            $user = $this->registry->get('user');
            $globals['user'] = $user;
            $globals['is_logged'] = $user->isLogged();
        } else {
            $customer = $this->registry->get('customer');
            $globals['customer'] = $customer;
            $globals['is_logged'] = $customer->isLogged();
            $affiliate = $this->registry->get('affiliate');
            $globals['affiliate'] = $affiliate;
            $globals['is_affiliate_logged'] = $affiliate->isLogged();
            $globals['cart'] = $this->registry->get('cart');
        }
        return $globals;
    }*/
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'expandcart';
    }

    function toObject($arr) {
        return (object)$arr;
    }

    function setObjectProperty($object, $propertyName, $propertyValue) {
        $object->{$propertyName} = $propertyValue;
        return $object;
    }
}
