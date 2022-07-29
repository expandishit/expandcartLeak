<?php

use ExpandCart\Foundation\Support\Url as UrlGenerator;

class Url {
	private $url;
	private $ssl;
	private $rewrite = array();
	private $config;
	private $registry;
	public static $schemas;

	public function __construct($url, $ssl = '', $registry = null) {
		$this->url = $url;
		$this->ssl = $ssl;
		$this->registry = $registry;

    }


    public function redirect(string $route, array $args = [])
    {
        $args = http_build_query( $args );
        $link = $this->link($route, $args, 'SSL');

        header("Location: {$link}");
        return;
    }


	public function addRewrite($rewrite) {
		$this->rewrite[] = $rewrite;
	}


	public function adminUrl($url, $route, $args, $connection)
    {
        parse_str($args, $args);

        unset($args['token']);

        $urlGenerator = new UrlGenerator;

        $urlGenerator->setBase($url)
            ->setPath($route)
            /*->setProtocol((
                $connection == 'NONSSL' ? 'http://' : 'https://'
            ))*/
            ->setQueryString($args)
        ;

        return $urlGenerator;
    }

    public function frontUrl($route, $args = '', $connection = 'NONSSL', $encode_query_strings = true)
    {
        if ($connection ==  'NONSSL') {
            $url = $this->url;
        } else {
            $url = $this->ssl;
        }

        $url = str_replace('/admin', '', $url);
        $url .= 'index.php?route=' . $route;

        if ($args) {
            if($encode_query_strings)
                $url .= str_replace('&', '&amp;', '&' . ltrim($args, '&'));
            else
                $url .= '&' . ltrim($args, '&');
        }

        foreach ($this->rewrite as $rewrite) {
            $url = $rewrite->rewrite($url);
        }

        if (IS_NEXTGEN_FRONT && !IS_ADMIN) {
            if ($skip) {
                return $url;
            } else {
                return $this->resolveUrl($url, $connection);
            }
        } else {
            return $url;
        }
    }

    public function frontUrlWithDomain(string $route, array $args = []){
        $args = http_build_query( $args );

        $protocol = "http://";
        $secure = false;

        if ( isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1')) ) {
            $protocol = "https://";
            $secure = true;
        }

        if ( DEV_MODE )
            return $this->frontUrl( $route, $args, $secure );

        require_once DIR_APPLICATION . 'model/setting/domainsetting.php';
        $model = new ModelSettingDomainSetting( $this->registry );
        $domains = $model->getDomains();

        if ( ! array_key_exists( 0, $domains ) || empty( $domains[0] ) || ! array_key_exists( "DOMAINNAME", $domains[0] ) )
            return $this->frontUrl( $route, $args, $secure );

        $first_domain = $domains[0]['DOMAINNAME'];

        return "{$protocol}{$first_domain}/index.php?route={$route}&{$args}";
    }
		
	public function link($route, $args = '', $connection = 'NONSSL', $skip = 0,$withStoreCode=false) {
	    if ($connection ==  'NONSSL') {
			$url = $this->url;
		} else {
			$url = $this->ssl;	
		}

        if($withStoreCode) $url = str_replace(DOMAINNAME, STORECODE.'.expandcart.com', $url);

		if (IS_ADMIN) {
		    return $this->adminUrl($url, $route, $args, $connection);
        }

        if (isset($_GET['__p'])) {
            $url .= 'index.php?route=' . $route . '&__p=' . $_GET['__p'];
        } else {
            $url .= 'index.php?route=' . $route;
        }
		if ($args) {
			$url .= str_replace('&', '&amp;', '&' . ltrim($args, '&'));
		}
		
		foreach ($this->rewrite as $rewrite) {
			$url = $rewrite->rewrite($url);
		}

		if (IS_NEXTGEN_FRONT && !IS_ADMIN) {
		    if ($skip) {
                return $url;
            } else {
                return $this->resolveUrl($url, $connection);
            }
        } else {
		    return $url;
        }
	}

	private function resolveUrl($url, $connection)
    {
        $config = $this->registry->get('config');
        $expand_seo = $config->get('expand_seo');
        if (isset($expand_seo['es_status']) && $expand_seo['es_status'] == 1) {
            if ($this->registry->has('preAction')) {
                parse_str(html_entity_decode(parse_url($url)['query']), $queryArray);
                $pre = $this->registry->get('preAction');
                if (!isset(self::$schemas)) {
                    self::$schemas = $pre->listAllRoutes();
                }

                if ($connection ==  'NONSSL') {
                    $prefix = $this->url;
                } else {
                    $prefix = $this->ssl;
                }

                $newurl = $pre->getURL($expand_seo, self::$schemas[$queryArray['route']], $queryArray);
                return (isset($newurl) && $newurl !== false ? $prefix . $newurl : $url);
            }
        } else {
            return $url;
        }
    }
}
?>
