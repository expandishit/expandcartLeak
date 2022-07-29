<?php

namespace ExpandCart\Foundation\Support;


class Url
{
    /**
     * The base url.
     *
     * @var string
     */
    protected $base = null;

    /**
     * The url protocol.
     *
     * @var string
     */
    protected $protocol = null;

    /**
     * The url path.
     *
     * @var string
     */
    protected $path = [];

    /**
     * The query string parts.
     *
     * @var array
     */
    protected $queryString = [];

    /**
     * A default configs that used to set default data.
     *
     * @var array
     */
    protected $defaults = [];

    /**
     * @param array $config
     *
     * @return $this
     */
    public function __construct($config = [])
    {
        $this->setDefaults($config);
    }

    /**
     * Set defaults array.
     *
     * @param array $defaults
     *
     * @return $this
     */
    public function setDefaults($defaults)
    {
        $this->defaults = $defaults;

        return $this;
    }

    /**
     * Return a detaults array.
     *
     * @return $this
     */
    public function getDefaults()
    {
        return $this->defaults;
    }

    /**
     * Return a detault element for a specific key.
     *
     * @param string $key
     *
     * @return $this
     */
    public function getDefault($key)
    {
        return isset($this->defaults[$key]) ? $this->defaults[$key] : null;
    }

    /**
     * Check if the default element is set.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasDefault($key)
    {
        return isset($this->defaults[$key]);
    }

    /**
     * Set base property.
     *
     * @param string $base
     *
     * @return $this
     */
    public function setBase($base)
    {
        $this->base = $base;

        return $this;
    }

    /**
     * Get the base property.
     *
     * @return string
     */
    public function getBase()
    {
        return rtrim(($this->base ?: $this->getDefault('base')), '/') . '/';
    }

    /**
     * Set protocol property.
     *
     * @param string $protocol
     *
     * @return $this
     */
    public function setProtocol($protocol)
    {
        $this->protocol = $protocol;

        return $this;
    }

    /**
     * Get protocol property.
     *
     * @return string
     */
    public function getProtocol()
    {
        $protocol = ($this->protocol ?: $this->getDefault('protocol'));

        return $protocol ? $protocol . '://' : null;
    }

    /**
     * Set path property.
     *
     * @param string|array $path
     *
     * @return $this
     */
    public function setPath($path)
    {
        if (is_array($path) == false) {
            $path = explode('/', $path);
        }

        $this->path = $path;
        $this->defaults['path'] = null;

        return $this;
    }

    /**
     * Attach path to the path array.
     *
     * @param string|array $path
     *
     * @return $this
     */
    public function attachPath($path)
    {
        if (is_array($path) == false) {
            $path = explode('/', $path);
        }

        $this->path = array_merge($this->path, $path);

        return $this;
    }

    /**
     * An alias for attachPath method.
     *
     * @param string|array $path
     *
     * @return $this
     *
     * @see attachPath()
     */
    public function addPath($path)
    {
        return $this->attachPath($path);
    }

    /**
     * Get path property.
     *
     * @return string
     */
    public function getPath()
    {
        if ($this->hasDefault('path')) {
            $defaults = explode('/', $this->getDefault('path'));
            $this->path = array_merge($defaults, $this->path);
        }

        $path = implode('/', ($this->path ?: null));

        $this->path = [];

        return $path;
    }

    /**
     * Populate the query string array.
     *
     * @param array|string $queryString
     *
     * @return $this
     */
    public function setQueryString($queryString)
    {
        if (false === is_array($queryString)) {
            parse_str($queryString, $queryString);
        }

        $this->queryString = $queryString;

        return $this;
    }

    /**
     * Add new query string element to the array.
     *
     * @param string $key
     * @param string $value
     *
     * @return $this
     */
    public function attachQuery($key, $value)
    {
        $this->queryString[$key] = $value;

        return $this;
    }

    /**
     * An alias for the attachQuery method.
     *
     * @param string $key
     * @param string $value
     *
     * @return $this
     *
     * @see attachQuery()
     */
    public function addQueryString($key, $value)
    {
        return $this->attachQuery($key, $value);
    }

    /**
     * An alias for the attachQuery method.
     *
     * @param string $key
     * @param string $value
     *
     * @return $this
     *
     * @see attachQuery()
     */
    public function appendQuery($key, $value)
    {
        return $this->attachQuery($key, $value);
    }

    /**
     * An alias for the attachQuery method.
     *
     * @param string $key
     * @param string $value
     *
     * @return $this
     *
     * @see attachQuery()
     */
    public function addQuery($key, $value)
    {
        return $this->attachQuery($key, $value);
    }

    /**
     * Unset a query string.
     *
     * @param string $key
     *
     * @return $this
     */
    public function detachQuery($key)
    {
        unset($this->queryString[$key]);

        return $this;
    }

    /**
     * Return the query string array.
     *
     * @return array
     */
    public function getQueryElements()
    {
        return $this->queryString;
    }

    /**
     * Return the query string as a built string.
     *
     * @return string
     */
    public function getQueryString()
    {
        return (!empty($this->queryString) ? '?' . http_build_query($this->queryString) : null);
    }

    /**
     * Return the formated url string.
     *
     * @return string
     */
    public function format()
    {
        return (implode([
            $this->getProtocol(),
            $this->getBase(),
            $this->getPath(),
            $this->getQueryString()
        ]));
    }

    /**
     * Return the formated url string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->format();
    }
}
