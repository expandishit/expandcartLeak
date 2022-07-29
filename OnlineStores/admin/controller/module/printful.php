<?php

use ExpandCart\Foundation\Support\Facades\Url;

class ControllerModulePrintful extends Controller
{
    /**
     * If set to true will try to detect and guess the product options individually like Size and Color
     *
     * @var boolean
     */
    private $guess_options = false;

    /**
     * Printful clothing sizes
     * 
     * @var array
     */
    private $clothes_sizes = [
        'S',
        'M',
        'L',
        'XL',
        '2XL',
        '3XL',
        '4XL',
        '5XL',
        '6XL',
    ];

    /**
     * Printful accessories sizes
     * 
     * @var array
     */
    private $accessories_sizes = [
        '1"',
        '2"',
        '3"',
        '4"',
        '5"',
        '6"',
        '7"',
        '8"',
        '9"',
        '10"',
        '1\"',
        '2\"',
        '3\"',
        '4\"',
        '5\"',
        '6\"',
        '7\"',
        '8\"',
        '9\"',
        '10\"',
    ];

    /**
     * Show settings pages for printful app
     */
    public function index()
    {
        $this->language->load('module/printful');

        $this->load->model('catalog/category');

        $this->document->setTitle($this->language->get('heading_title_printful'));

        $this->data['breadcrumbs'] = [
            [
                'text' => $this->language->get('text_home'),
                'href' => Url::addPath(['common', 'home'])->format(),
                'separator' => false
            ],
            [
                'text' => $this->language->get('text_module'),
                'href' => Url::addPath(['marketplace', 'home'])->format(),
                'separator' => ' :: '
            ],
            [
                'text' => $this->language->get('heading_title_printful'),
                'href' => Url::addPath(['module', 'printful'])->format(),
                'separator' => ' :: '
            ],
        ];

        $this->initializer([
            'localisation/geo_zone',
            'printful' => 'module/printful/settings'
        ]);

        //Get categories
        $this->data['categories'] = $this->model_catalog_category->getCategories();

        $this->data['data'] = $this->printful->getSettings();

        $this->data['geo_zones'] = $this->geo_zone->getGeoZones();

        $this->template = 'module/printful/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
    }

    /**
     * Show import view for prointful
     */
    public function import()
    {
        $this->language->load('module/printful');
        $this->load->model('module/printful/api');

        $this->document->setTitle($this->language->get('heading_title_printful'));

        $this->data['breadcrumbs'] = [
            [
                'text' => $this->language->get('text_home'),
                'href' => Url::addPath(['common', 'home'])->format(),
                'separator' => false
            ],
            [
                'text' => $this->language->get('text_module'),
                'href' => Url::addPath(['marketplace', 'home'])->format(),
                'separator' => ' :: '
            ],
            [
                'text' => $this->language->get('heading_title_printful'),
                'href' => Url::addPath(['module', 'printful'])->format(),
                'separator' => ' :: '
            ],
        ];

        $this->initializer([
            'localisation/geo_zone',
            'printful' => 'module/printful/settings'
        ]);

        //Get printful settings
        $this->data['printful_settings'] = $this->printful->getSettings();



        if($this->data['printful_settings']['api_key']){


            //Get printful product information
            $this->data['products'] = $this->model_module_printful_api->getProducts($this->data['printful_settings']['api_key'], 0, 5);



        }

        //Get imported products array limited by 50
        $this->data['imported_products'] = $this->model_module_printful_api->getImportedProducts(0,50);

        //Get impoted products count
        $this->data['imported_products_count'] = $this->model_module_printful_api->getImportedProductsCount();

        $this->template = 'module/printful/import.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        //Check for session message
        if (isset($this->session->data['printful_message'])) {
            $this->data['printful_message'] = $this->session->data['printful_message'];
            unset($this->session->data['printful_message']);
        }

        $this->response->setOutput($this->render_ecwig());
    }

    /**
     * Initiate the background import job
     */
    public function handleImportAll()
    {

        $this->language->load('module/printful');

        //Start background job
        $file_location = DIR_SYSTEM.'library/import_from_printful.php';
        $storecode = STORECODE;



        //This should be run on production
        shell_exec("php $file_location $storecode >/dev/null 2>&1 &");
            //Uncomment this for debug mode

//        echo shell_exec("php $file_location $storecode");die;

        $this->session->data['printful_message'] = ['type' => 'success', 'message' => $this->language->get('job_started_message')];

        $this->response->redirect('/admin/module/printful/import');
    }

    /**
     * The background process to import all products from printful
     */
    public function handleImportInBackground(): void
    {
        $this->load->model('module/printful/api');
        $this->load->model('module/printful/settings');

        $settings = $this->model_module_printful_settings->getSettings();

        try {
            $products = $this->model_module_printful_api->getProducts($settings['api_key']);
            $perPage = 100;
            $totalProducts = $products['paging']['total'];
            $totalPages = ceil($totalProducts / $perPage);
            $offset = 0;
            $lowest_price = null;
            //Loop on all pages
            for ($i = 1; $i <= $totalPages; $i++) {
                //Loop on all products to import them
                foreach ($this->model_module_printful_api->getProducts($settings['api_key'], $offset, $perPage)['result'] as $product) {
                 $existing_product = $this->model_module_printful_api->getPrintfulProduct($product['id']);
                 if(!$existing_product->num_rows){
                    $products = [];
                    //Add product to array
                    $products[$product['id']] = $product;

                    //Set lowest price to null
                    $lowest_price = null;

                    //Get product data and variants
                    $product_data = $this->model_module_printful_api->getProduct($settings['api_key'], $product['id']);

                    //Strip product name from all variants and guess options
                    //to solve if variants come as one item not array
                    if (!$product_data['result']['sync_variants'][0]) {
                        $temp[0] = $product_data['result']['sync_variants'];
                        $product_data['result']['sync_variants'] = $temp[0];
                    }

                    foreach ($product_data['result']['sync_variants'] as $variant) {
                        //exclude product with variant_id=null
                        //we should send id not variant_id

                        if (!empty($variant['id'])) {
                            $productDetails = $this->model_module_printful_api->getProductDetails($settings['api_key'], $variant['id']);

                            if (!empty($productDetails)) {

//                            if($productDetails['result']['sync_product']['description']) {
//                                $products[$product['id']]['description'] = $productDetails['result']['sync_product']['description'];
//                            }
                                 $variant['retail_price']=$this->currency->convert($variant['retail_price'],$variant['currency'],$this->config->get('config_currency'));
                                //Set lowest price
                                if ($variant['retail_price'] < $lowest_price || !$lowest_price) {
                                    $lowest_price = $variant['retail_price'];
                                    $products[$product['id']]['lowest_price'] = $variant['retail_price'];
                                }

                                //Get variant options
                                $variant_options = str_replace($product['name'], '', $variant['name']);
                                $variant_options = str_replace(' - ', '', $variant_options);

                                //check if the variant is empty then the product has no variants
                                if (!$variant_options) {
                                    continue;
                                } else {
                                    //Loop on all variant options to guess it

                                    //Remove whitespace
                                    $variant_option = trim($variant_options);

                                    if (!$this->guess_options) {
                                        $products[$product['id']]['options'][] = [
                                            'option_name' => 'color',
                                            'option_name' => 'variant',
                                            'option_value' => $variant_option,
                                            'option_data' => $variant
                                        ];
                                    } else {
                                        /**
                                         * This code will try to split the option names and compare them to guess different options
                                         * It may however cause problems because we don't know exactly the type of data coming
                                         */
                                        //Check if the option is size
                                        //else, treat it as color
                                        if (
                                            in_array($variant_option, $this->clothes_sizes) ||
                                            in_array($variant_option, $this->accessories_sizes) ||
                                            preg_match('#[0-9\.]*[×xX][0-9\.]*#', $variant_option)
                                        ) {
                                            //Add size to the products array
                                            $products[$product['id']]['options'][] = [
                                                'option_name' => 'size',
                                                'option_value' => $variant_option,
                                                'option_data' => $variant
                                            ];
                                        } else {
                                            //Add color to the products array
                                            $products[$product['id']]['options'][] = [
                                                'option_name' => 'color',
                                                'option_value' => $variant_option,
                                                'option_data' => $variant
                                            ];
                                        }
                                    }
                                }
                            }
                        }
                    }
                    //Send  product to the model for insertion into DB
                  $this->model_module_printful_api->importProducts($products, $this->guess_options);
                }
              }

                $offset += $perPage;
            }



        } catch (Exception $e) {
            // TODO:  redirect
        }
    }

    /**
     * Update printful settings
     */
    public function updateSettings()
    {
        if ($this->request->server['REQUEST_METHOD'] != 'POST') {
            $this->response->setOutput(json_encode([
                'success' => 0,
                'errors' => [
                    'invalid request'
                ]
            ]));
            return;
        }

        $this->initializer([
            'printful' => 'module/printful/settings'
        ]);

        $printful = $this->request->post['printful'];

        if ($this->printful->validate($printful) == false) {
            $this->response->setOutput(json_encode([
                'success' => 0,
                'errors' => $this->printful->getErrors()
            ]));
            return;
        }

        $this->printful->updateSettings($printful);

        $this->response->setOutput(json_encode([
            'success' => 1,
            'success_msg' => $this->language->get('text_settings_success')
        ]));
        return;
    }

    /**
     * Install printful app
     *
     * @return void
     */
    public function install()
    {
        $this->initializer(['module/printful/settings']);
        $this->settings->install();
    }

    /**
     * Uninstall printful app
     *
     * @return void
     */
    public function uninstall()
    {
        $this->initializer(['module/printful/settings']);
        $this->settings->uninstall();
    }

    public function order()
    {
        if ($this->request->server['REQUEST_METHOD'] != 'POST') {
            throw \ExpandCart\Foundation\Http\Exceptions\HttpException(
                ['status' => 'ERR', 'errors' => [$this->language->get('illegal_method')]]
            );
        }

        if (
            isset(getallheaders()['x-source-ajax']) == false ||
            getallheaders()['x-source-ajax'] != 'expandcart'
        ) {
            throw \ExpandCart\Foundation\Http\Exceptions\HttpException(
                ['status' => 'ERR', 'errors' => [$this->language->get('corrupted_request')]]
            );
        }

        if (isset($this->request->post['order_id']) || (int) $this->request->post['order_id'] < 1) {
            throw \ExpandCart\Foundation\Http\Exceptions\JsonException(
                ['status' => 'ERR', 'errors' => [$this->language->get('invalid_request')]]
            );
        }

        $orderId = $this->request->post['order_id'];

        $this->initializer([
            'printful' => 'module/printful/settings',
            'shipment' => 'module/printful/order',
            'order' => 'sale/order',
        ]);

        $order = $this->order->getOrder($orderId);
        $order['products'] = $this->order->getOrderProducts($orderId);

        foreach ($order['products'] as $key => $product) {
            $order['products'][$key]['printful'] = $this->printful->getProduct($product['product_id']);
        }

        if (!$order) {
            throw \ExpandCart\Foundation\Http\Exceptions\JsonException(
                ['status' => 'ERR', 'errors' => [$this->language->get('invalid_order')]]
            );
        }

        $printful = $this->printful->getPrintfulByOrderId($orderId);

        $this->shipment->setApiKey($this->printful->getApiKey());

        if ($printful) {

            $order = $this->shipment->orderStatus($printful['printful_id']);

            $this->response->setOutput(json_encode([
                'status' => 'OK',
                'item' => $printful,
                'order' => $order
            ]));
            return;
        }

        if ($shipment = $this->shipment->createOrder($order)) {
            $printfulOrderId = $this->printful->insertOrder($shipment['result'], $orderId);

            foreach ($order['products'] as $product) {
                $this->printful->insertOrderProduct($printfulOrderId, $product);
            }

            $this->response->setOutput(json_encode([
                'status' => 'OK',
                'item' => null,
                'orderId' => $orderId,
                'shipment' => $shipment
            ]));
            return;
        }

        $this->response->setOutput(json_encode([
            'status' => 'ERR',
            'errors' => $this->shipment->getErrors(),
        ]));
    }
}
