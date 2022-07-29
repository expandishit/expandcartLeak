<?php

namespace ExpandCart\Foundation\Support\Factories\Payments\FastpayCash;

/**
 * a temporary class to handle curl request
 * To be replaced by Guzzle or any other http library
 */

class Http
{
    /**
     * The request method.
     *
     * @var string
     */
    private $method = 'GET';

    /**
     * Array of status codes, note that the currently supported request methods are get ad post only.
     *
     * @var array
     */
    private $isPost = ['get' => 0, 'post' => 1];

    /**
     * Array of posted data.
     *
     * @var array
     */
    private $postData = [];

    /**
     * The request URl.
     *
     * @var string
     */
    private $url;

    /**
     * Sets the url string.
     *
     * @param string $url
     *
     * @return \ExpandCart\Foundation\Support\Factories\Payments\FastpayCash\Http
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Sets the request method.
     *
     * @param string $method
     *
     * @return \ExpandCart\Foundation\Support\Factories\Payments\FastpayCash\Http
     */
    public function setMethod($method)
    {
        $this->method = $this->isPost[strtolower($method)];

        return $this;
    }

    /**
     * a magic method to quick sets the request method to post.
     *
     * @return \ExpandCart\Foundation\Support\Factories\Payments\FastpayCash\Http
     */
    public function post()
    {
        return $this->setMethod('post');
    }

    /**
     * a magic method to quick sets the request method to get.
     *
     * @return \ExpandCart\Foundation\Support\Factories\Payments\FastpayCash\Http
     */
    public function get()
    {
        return $this->setMethod('get');
    }

    /**
     * Sets the request post data.
     *
     * @param string $method
     *
     * @return \ExpandCart\Foundation\Support\Factories\Payments\FastpayCash\Http
     */
    public function setPostData($postData)
    {
        $this->postData = $postData;

        return $this;
    }

    /**
     * Create the curl instanse and return the request response.
     *
     * @return string
     */
    public function run()
    {
        $handle = \curl_init();
        \curl_setopt($handle, CURLOPT_URL, $this->url);
        \curl_setopt($handle, CURLOPT_TIMEOUT, 10);
        \curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 10);
        if ($this->method === 1) {
            \curl_setopt($handle, CURLOPT_POST, 1);
            \curl_setopt($handle, CURLOPT_POSTFIELDS, $this->postData);
        }
        \curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

        $content = \curl_exec($handle);

        $code = \curl_getinfo($handle, CURLINFO_HTTP_CODE);

        if ($code == 200 && !(\curl_errno($handle))) {
            \curl_close($handle);
            $response = $content;
        } else {
            \curl_close($handle);
            echo "FAILED TO CONNECT WITH FastPay  API";
            exit;
        }

        return $response;
    }
}
