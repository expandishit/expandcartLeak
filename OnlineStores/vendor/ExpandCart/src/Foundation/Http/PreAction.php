<?php

namespace ExpandCart\Foundation\Http;

/**
 * TODO
 * re-write this class
*/

class PreAction
{
    private $routes;
    public $class;
    public $method;

    private $db;
    private $session;
    private $request;

    public $patterns = [
        'product/product' => [
//            '@slug@' => '([\p{Arabic}a-z0-9\-\_]+)',
            '@slug@' => '([\p{L}\p{N}\-\_]+)',
            '@product_id@' => '([0-9\_]+)',
        ],
        'product/category' => [
            '@slug@' => '([\p{L}\p{N}\-\_]+)',
            '@path@' => '([0-9\_]+)',
        ],
        'product/manufacturer/info' => [
            '@slug@' => '([\p{L}\p{N}\-\_]+)',
            '@manufacturer_id@' => '([0-9\_]+)',
        ],
        'product/search' => [
            '@search@' => '([\p{L}\p{N}\-\_\s]+)',
        ],
        'product/compare' => [
            '@search@' => '([\p{L}\p{N}\-\_]+)',
        ],
        'blog/post' => [
            '@slug@' => '([\p{L}\p{N}\-\_]+)',
            '@post_id@' => '([0-9\_]+)',
        ],
        'blog/category' => [
            '@slug@' => '([\p{L}\p{N}\-\_]+)',
            '@category_id@' => '([0-9\_]+)',
        ],
        'blog/blog' => [],
        'product/special' => [],
        'checkout/cart' => [],
        'checkout/checkout' => [],
        'checkout/checkoutv2' => [],
        'information/contact' => [],
        'common/home' => [],
        'information/sitemap' => [],
        'information/information' => [
            '@information_id@' => '([0-9\_]+)',
            '@slug@' => '([\p{L}\p{N}\-\_]+)',
        ],
        'account/login' => [],
        'account/register' => [],
        'seller/register-seller' => [],
        'seller/catalog-seller/profile' => [
            '@seller_id@' => '([0-9\_]+)',
            '@slug@' => '([\p{L}\p{N}\-\_]+)',
        ],
        'checkout/success' => [],
        'checkout/error' => [],
        'checkout/pending' => [],
        'payment/oneglobal/callback' => [],

    ];

    public function __construct($registry)
    {
        $this->registry = $registry;
        $this->db = $this->registry->get('db');
        $this->request = $this->registry->get('request');
        $this->session = $this->registry->get('session');
    }

    public function listByRoute($route)
    {
        $query = 'SELECT  * FROM expand_seo WHERE schema_status=1 and seo_group="' . $route . '" and language IN ("global", "' . $this->session->data['language'] . '")';
        $data = $this->db->query($query);

        if ($data->num_rows > 0) {
            return $data->row;
        } else {
            return false;
        }
    }

    public function listAllRoutes()
    {
        $query = 'SELECT  * FROM expand_seo where schema_status=1 and language IN ("global", "' . $this->session->data['language'] . '")';
        $data = $this->db->query($query);

        if ($data->num_rows > 0) {
//            return $data->rows;
            return array_column($data->rows, null, 'seo_group');
        } else {
            return false;
        }
    }

    public function getLanguages(){
        $db = $this->registry->get('db');
        $query = $db->query("SELECT `code` FROM ".DB_PREFIX."language where status=1 ");
        return $query->rows;
    }

    public function getRoute($expand_seo, $route)
    {
        $db = $this->registry->get('db');
//        $query = $db->query('SELECT * FROM expand_seo where language IN ("global", "' . $this->session->data['language'] . '")');
        $query = $db->query('SELECT * FROM expand_seo where schema_status=1 AND schema_prefix != ""');
        $rows = $query->rows;
        foreach ($rows as $row) {
            $fullPattern = [];
            $fullPattern[] = '#';
            if ($row['language'] != 'global' && $expand_seo['es_append_language'] == 1) {
                $fullPattern[] = '^';
                $fullPattern[] = $row['language'];
                $fullPattern[] = '/';
            } else {
                $language_codes=$this->getLanguages();
                $expression='^(?:';
                $count=1;
                foreach ($language_codes as $code) {
                    if($count == 1)
                        $expression.="\b".$code['code']."\b";
                    else
                        $expression.="|\b".$code['code']."\b";
                    $count++;
                }
                $expression.=')*/?';
                $fullPattern[]=$expression; 
            }

            if ($row['schema_prefix']) {
                $fullPattern[] = $row['schema_prefix'];
            }
            $fullPattern[] = $row['schema_url'];
            if ($row['schema_url']) {
                $fullPattern[] = '/?#ui';
            } else {
                $fullPattern[] = '/?$#Uui';
            }
            if (preg_match(implode('', $fullPattern), $route, $matches)) {
                array_shift($matches);
                $matches = array_combine(json_decode($row['schema_parameters'], true), $matches);
                $matches['route'] = $row['seo_group'];
                $mainLanguage = $row['language'];
                if (preg_match('#' . ($row['schema_prefix'].$matches['slug']) . '#', $route)) {
                    $data = ['data' => $row, 'parameters' => $matches];
                    break;
                }
            }
        }

        if (!$data) {
            $query = $db->query('SELECT * FROM expand_seo where schema_status=1 AND schema_prefix = ""');
            $rows = $query->rows;
            foreach ($rows as $row) {
                $fullPattern = [];
                $fullPattern[] = '#';
                if ($row['language'] != 'global' && $expand_seo['es_append_language'] == 1) {
                    $fullPattern[] = '^';
                    $fullPattern[] = $row['language'];
                    $fullPattern[] = '/';
                } else {
                    $language_codes=$this->getLanguages();
                    $expression='^(?:';
                    $count=1;
                    foreach ($language_codes as $code) {
                        if($count == 1)
                            $expression.="\b".$code['code']."\b";
                        else
                            $expression.="|\b".$code['code']."\b";
                        $count++;
                    }
                    $expression.=')*/?';
                    $fullPattern[]=$expression; 
                }

                if ($row['schema_prefix']) {
                    $fullPattern[] = $row['schema_prefix'];
                }
                $fullPattern[] = $row['schema_url'];
                if ($row['schema_url']) {
                    $fullPattern[] = '/?#ui';
                } else {
                    $fullPattern[] = '/?$#Uui';
                }
                if (preg_match(implode('', $fullPattern), $route, $matches)) {
                    array_shift($matches);
                    $matches = array_combine(json_decode($row['schema_parameters'], true), $matches);
                    $matches['route'] = $row['seo_group'];
                    $mainLanguage = $row['language'];
                    if (preg_match('#' . ($row['schema_prefix'].$matches['slug']) . '#', $route)) {
                        $data = ['data' => $row, 'parameters' => $matches];
                        break;
                    }
                }
            }
        }
        $req = $db = $this->registry->get('request');
        $ses = $db = $this->registry->get('session');
        $matches['language'] = ($mainLanguage != 'global' ? $mainLanguage : $ses->data['language']);
//        $ses->data['language'] = $matches['language'];
        $req->get = array_merge($req->get, $matches);
        return $data;
    }

    public function getURL($expand_seo, $data, $params)
    {
        $patterns = $this->patterns[$data['seo_group']];
        $_patterns = array_flip($patterns);

        $tmpParams = $params;
        $langAdded = false;

        $url = [];
        $prefix = null;
        if ($expand_seo['es_append_language'] == 1 && $data['language'] != 'global') {
            $url[] = $data['language'];
            $langAdded = true;
        }
        if (isset($data['schema_prefix']) && $data['schema_prefix'] != '') {
            $prefix = $data['schema_prefix'];
        }
        if (isset($data['schema_url']) && $data['schema_url'] != '') {
            $url[] = (
                ($prefix ?: null) .
                str_replace($patterns, $_patterns, $data['schema_url'])
            );
        } else {
            $url[] = ($prefix ?: null);
        }
//        print_r($url);
        $url = implode('/', $url);
        $parameters = json_decode($data['schema_parameters'], true);
//        echo '<pre>';#exit;
//        print_r($data);exit;
//        print_r($session);
        $url = preg_replace_callback('#\@(.*?)\@#', function ($matches) use($data, $params, &$tmpParams) {

            if (isset($params[$matches[1]]) && $data['seo_group'] != 'product/search' ) {
                unset($tmpParams[$matches[1]]);
                return $params[$matches[1]];
            } else {

                if ($data['seo_group'] == 'product/product') {
                    $table = 'product_description';
                    $where = 'product_id = "'.$params['product_id'].'"';
                    $query = 'select * from language as l inner join '.$table.' as t on l.language_id=t.language_id where
            	    '.$where.' and l.code="'.$data['fields_language'].'"';

                    unset($tmpParams['product_id']);

                } else if ($data['seo_group'] == 'product/category') {
                    $table = 'category_description';
                    $id = $params['path'];

                    if (strpos($params['path'], '_') != false) {
                        $ids = explode('_', $params['path']);
                        $id = $ids[1];
                    }

                    $where = 'category_id = "'.$id.'"';

                    $query = 'select * from language as l inner join '.$table.' as t on l.language_id=t.language_id where
            	    '.$where.' and l.code="'.$data['fields_language'].'"';

                    unset($tmpParams['path']);

                } else if ($data['seo_group'] == 'product/manufacturer/info') {
                    $query = 'select * from manufacturer where
            	    '.$where.' manufacturer_id="'.$params['manufacturer_id'].'"';

                    unset($tmpParams['manufacturer_id']);

                } else if ($data['seo_group'] == 'information/information') {
                    $where = 't.information_id="'.$params['information_id'].'"';

                    $query = 'select *, t.title as name from language as l inner join information_description as t on l.language_id=t.language_id where
            	    '.$where.' and l.code="'.$data['fields_language'].'"';

                    unset($tmpParams['information_id']);

                } else if ($data['seo_group'] == 'blog/post') {
                    $table = 'flash_blog_post_description';
                    $where = 'post_id = "'.$params['post_id'].'"';
                    $query = 'select * from language as l inner join '.$table.' as t on l.language_id=t.language_id where
            	    '.$where.' and l.code="'.$data['fields_language'].'"';

                    unset($tmpParams['post_id']);

                } else if ($data['seo_group'] == 'blog/category') {
                    $table = 'flash_blog_category_description';
                    $where = 'category_id = "'.$params['category_id'].'"';
                    $query = 'select * from language as l inner join '.$table.' as t on l.language_id=t.language_id where
            	    '.$where.' and l.code="'.$data['fields_language'].'"';

                    unset($tmpParams['category_id']);

                } else if ($data['seo_group'] == 'seller/catalog-seller/profile') {
                    $table = 'ms_seller';
                    $where = 'seller_id = "'.$params['seller_id'].'"';
                    $query = 'select * from '.$table.' as t where
            	    '.$where.'';

                    unset($tmpParams['seller_id']);

                }

                if (!$query) {
                    return false;
                }
                $_data = $this->db->query($query);
//                return trim(preg_replace('#[^\w\_\-]+#u', '-', $_data->row['name']));
                return $_data->row['slug'];
            }

        }, $url);

        $search = null;
        $lang = null;

        if (  array_key_exists("search", $tmpParams) && ! empty( $tmpParams['search'] ) ) {
            $search = $tmpParams['search'];
        }

        if ( $expand_seo['es_append_language'] == 1 && ! $langAdded ) {
            if ( $data['language'] == 'global' && $data['fields_language']) {
                $lang = $data['fields_language'] . "/";
            } else if ($data['language'] == 'global' && !$data['fields_language']) {
                $config = $this->registry->get('config');
                $lang = $config->get('config_language') . "/";
            } else {
                $lang = $data['language'] . "/";
            }
        }

        unset($tmpParams['route'], $tmpParams['language'], $tmpParams['search']);

        if (isset($patterns)) {
            return $lang . $url . $search . (!empty($tmpParams) ? '/?' . http_build_query($tmpParams) : null);
        } else {
            return false;
        }
    }

    public function redirect($url)
    {
        header('Location: ' . $url);
    }


    public function dispatch($r, $notindex = null)
    {

        $ref = (IS_EXPANDISH_FRONT ? 'expandish' : 'catalog');

        foreach ($r as $slug => &$route) {
            $this->params = [];

            $slug = preg_replace_callback('#\@(.*?)\@#', function ($matches) {
                $this->params[] = $matches[1];
                return '([a-z0-9\_\-\#]+)';
            }, $slug);

            if (preg_match('#'.rtrim($slug, '/').'#', $this->request->get['route'], $matches)) {
                $r = $route;
            }

        }
        array_shift($matches);
        $pp = array_combine($this->params, $matches);
        $r = $this->resolver($r);
        $this->class = $r[0];
        $this->method = $r[1];

        $ceee = '/var/www/expandcart/expandcartdev/OnlineStores/' . $ref . '/controller/common/home.php';
        require_once($ceee);

        $fclass = str_replace('\\', '', $this->class);

        $class = new \ControllerCommonHome($this->registry);
        $ppp = [];
        $reflectionmethod = new \ReflectionMethod($class, $this->method);
        foreach ($reflectionmethod->getParameters() as $p) {
            if (isset($pp[$p->name])) {
                $ppp[$p->name] = $pp[$p->name];
            } else {
                $ppp[$p->name] = null;
            }
        }

        return call_user_func_array([$class, $this->method], ['args' => ['route' => $this->request->get['route']]]);
    }

    public function resolver($r)
    {
        return explode('@', $r);
    }

    public function isActive()
    {
        $config = $this->registry->get('config');
        $expand_seo = $config->get('expand_seo');

        if (isset($expand_seo['es_status']) && $expand_seo['es_status'] == 1) {
            return $expand_seo;
        }

        return false;
    }
}
