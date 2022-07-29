<?php

/**
 * Class ControllerModuleFacebookBusiness
 */
class ControllerModuleFacebookBusiness extends Controller
{
    /**
     * @var string 
     */
    private $name = 'facebook_business';
    /**
     * @var mixed
     */
    private $pixel_id = '';

    /**
     * @var array Array of the available events now
     */
    private $facebook_pixel_active_events = [];

    /**
     * @var string[]
     */
    private $facebook_pixel_allowed_events = array(
        'Purchase', 'Generate Lead', 'Complete Registration',
        'Add Payment Info', 'Add to Basket', 'Add to Wishlist',
        'Initiate Checkout', 'Search', 'View Content'
    );

    /**
     * ControllerModuleFacebookBusiness constructor.
     * @param $registry
     */
    public function index()
    {
        $fbe_settings = $this->model_setting_setting->getSetting($this->name);
        $this->pixel_id = $fbe_settings['pixel_id'];
        $this->facebook_pixel_active_events = $fbe_settings['facebook_pixel_active_events'];
    }

    /**
     * @return mixed
     */
    public function getFbPixelId()
    {
        return $this->pixel_id;
    }

    /**
     * @return mixed
     */
    public function getFbPixelActiveEvents()
    {
        return $this->facebook_pixel_active_events;
    }
    /**
     * @return mixed
     */
    public function getFbPixelAllowedEvents()
    {
        return $this->facebook_pixel_allowed_events;
    }
    /**
     * @param $fbPixelEvent
     * @param array $data
     * @return false|string|void
     */
    public function getFbPixelEvent($fbPixelEvent, $data = [])
    {
        if (!is_array($fbPixelEvent,$this->facebook_pixel_active_events)){
            return;
        }
        switch ($fbPixelEvent) {
            
            case 'Purchase' :
                return json_encode([
                    'value' => $data['total'],
                    'currency' => $data['currency'],
                    "IP Address" => $this->request->server['REMOTE_ADDR'],
                    'num_items' => $data['quantity'],
                    'content_ids' => $data['content_ids'],
                    'contents' => $data['productsToSendToFacebookTrack'],
                    'content_type' => $data['product'],
                    'product_catalog_id' => $data['catalog_id']
                ]);

            case 'Complete Registration':
            case 'Add Payment Info':
            case 'Generate Lead' :
                return json_encode([
                    'IP Address' => $this->request->server['REMOTE_ADDR']
                ]);

            case 'Add to Cart' :
                return json_encode([
                    "IP Address" => $this->request->server['REMOTE_ADDR'],
                    "value" => $data['total'],
                    "num_items" => $data['quantity'],
                    "content_ids" => $data['content_ids'],
                    "content_type" => $data['product'],
                    "currency" => $this->currency->getCode(),
                    "product_catalog_id" => $data['catalog_id'],
                    "Products" => $data['productsToSendToFacebookTrack']
                ]);
                
            case 'Add to Wishlist' :
                return json_encode([
                    "IP Address" => $this->request->server['REMOTE_ADDR'],
                    "num_items" => $data['quantity'],
                    "content_ids" => $data['content_ids'],
                    "content_type" => $data['product'],
                    "product_catalog_id" => $data['catalog_id']
                ]);
                
            case 'Initiate Checkout' :
                return json_encode([
                    "IP Address" => $this->request->server['REMOTE_ADDR'],
                    "value" => $this->cart->getTotal(),
                    "currency" => $this->currency->getCode(),
                    "num_items" => $data['quantity'],
                    "content_ids" => $data['content_ids'],
                    "content_type" => $data['product'],
                    "product_catalog_id" => $data['catalog_id']
                ]);
                
            case 'Search' :
                return json_encode([
                    'search_string' => $data['search'],
                    'IP Address' => $this->request->server['REMOTE_ADDR']
                ]);
                
            case 'View Content' :
                return json_encode([
                    'IP Address' => $this->request->server['REMOTE_ADDR'],
                    'content_type' => 'product',
                    'content_ids' => $data['product_id'],
                    'image_link' => HTTP_IMAGE . $data['product_info']['image'],
                    'value' => number_format(($data['product_info']['special'] ?? $data['product_info']['price']), 2),
                    'currency' => $this->currency->getCode(),
                    'product_catalog_id' => $data['catalog_id']
                ]);
        }
    }

    /**
     * @return false|string
     */
    function getFbPixelAddPaymentInfo()
    {
        return json_encode([
            'IP Address' => $this->request->server['REMOTE_ADDR']
        ]);
    }

    /**
     * @param $data
     * @return false|string
     */
    function getFbPixelAddToCart($data)
    {
        return json_encode([
            "IP Address" => $this->request->server['REMOTE_ADDR'],
            "value" => $data['total'],
            "num_items" => $data['quantity'],
            "content_ids" => $data['content_ids'],
            "content_type" => $data['product'],
            "currency" => $this->currency->getCode(),
            "product_catalog_id" => $data['catalog_id'],
            "Products" => $data['productsToSendToFacebookTrack']
        ]);
    }

    /**
     * @param $data
     * @return false|string
     */
    function getFbPixelAddToWishlist($data)
    {
        return json_encode([
            "IP Address" => $this->request->server['REMOTE_ADDR'],
            "num_items" => $data['quantity'],
            "content_ids" => $data['content_ids'],
            "content_type" => $data['product'],
            "product_catalog_id" => $data['catalog_id']
        ]);
    }

    /**
     * @return false|string
     */
    function getFbPixelCompleteRegistration()
    {
        return json_encode([
            'IP Address' => $this->request->server['REMOTE_ADDR']
        ]);
    }

    /**
     *
     */
    function getFbPixelContact()
    {
        /* not implemented yet */
    }

    /**
     *
     */
    function getFbPixelCustomizeProduct()
    {
        /* not implemented yet */

    }

    /**
     *
     */
    function getFbPixelDonate()
    {
        /* not implemented yet */

    }

    /**
     *
     */
    function getFbPixelFindLocation()
    {
        /* not implemented yet */

    }

    /**
     * @param $data
     * @return false|string
     */
    function getFbPixelInitiateCheckout($data)
    {
        return json_encode([
            "IP Address" => $this->request->server['REMOTE_ADDR'],
            "value" => $this->cart->getTotal(),
            "currency" => $this->currency->getCode(),
            "num_items" => $data['quantity'],
            "content_ids" => $data['content_ids'],
            "content_type" => $data['product'],
            "product_catalog_id" => $data['catalog_id']
        ]);
    }

    /**
     *
     */
    function getFbPixelLead()
    {
        return json_encode([
            'IP Address' => $this->request->server['REMOTE_ADDR']
        ]);
    }

    /**
     * @return false|string
     */
    function getFbPixelPageView()
    {
        return json_encode([
            'IP Address' => $this->request->server['REMOTE_ADDR']
        ]);
    }

    /**
     * @param $data
     * @return false|string
     */
    function getFbPixelPurchase($data)
    {
        return json_encode([
            'value' => $data['total'],
            'currency' => $data['currency'],
            "IP Address" => $this->request->server['REMOTE_ADDR'],
            'num_items' => $data['quantity'],
            'content_ids' => $data['content_ids'],
            'contents' => $data['productsToSendToFacebookTrack'],
            'content_type' => $data['product'],
            'product_catalog_id' => $data['catalog_id']
        ]);
    }

    /**
     *
     */
    function getFbPixelSchedule()
    {
        /* not implemented yet */

    }

    /**
     * @param $data
     * @return false|string
     */
    function getFbPixelSearch($data)
    {
        return json_encode([
            'search_string' => $data['search'],
            'IP Address' => $this->request->server['REMOTE_ADDR']
        ]);
    }

    /**
     *
     */
    function getFbPixelStartTrial()
    {
        /* not implemented yet */

    }

    /**
     *
     */
    function getFbPixelSubmitApplication()
    {
        /* not implemented yet */

    }

    /**
     *
     */
    function getFbPixelSubscribe()
    {
        /* not implemented yet */
    }

    /**
     *
     */
    function getFbPixelViewContent($data)
    {
        return json_encode([
            'IP Address' => $this->request->server['REMOTE_ADDR'],
            'content_type' => 'product',
            'content_ids' => $data['product_id'],
            'image_link' => HTTP_IMAGE . $data['product_info']['image'],
            'value' => number_format(($data['product_info']['special'] ?? $data['product_info']['price']), 2),
            'currency' => $this->currency->getCode(),
            'product_catalog_id' => $data['catalog_id']
        ]);
    }

}
