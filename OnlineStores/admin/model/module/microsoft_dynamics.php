<?php

use ExpandCart\Foundation\Inventory\Inventory;
use ExpandCart\Foundation\Providers\Extension;
use ExpandCart\Foundation\Inventory\Clients\MicrosoftDynamics;

class ModelModuleMicrosoftDynamics extends Model
{

    protected $table = 'microsoft_dynamics_orders';

    private $inventory;

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->setInventory(new Inventory(new MicrosoftDynamics, $this->getSettings()));
    }

    /**
     * Get the value of inventory
     */
    public function getInventory(): Inventory
    {
        return $this->inventory;
    }

    /**
     * Set the value of inventory
     *
     * @return  self
     */
    public function setInventory(Inventory $inventory): self
    {
        $this->inventory = $inventory;

        return $this;
    }

    /**
     * update the module settings.
     *
     * @param array $inputs
     *
     * @return bool
     */
    public function updateSettings($inputs)
    {
        $this->load->model('setting/setting');

        $this->model_setting_setting->insertUpdateSetting(
            'microsoft_dynamics',
            $inputs
        );

        return true;
    }

    public function getSettings()
    {
        return array_merge(
            $this->getDefaultSettings(),
            $this->config->get('microsoft_dynamics') ?? [],
            ['order_statuses' => $this->getOrderStatusesList()],
            ['return_statuses' => $this->getReturnStatusesList()]
        );
    }

    private function getOrderStatusesList()
    {
        $localOrderStatusModel = $this->load->model('localisation/order_status', ['return' => true]);
        return $localOrderStatusModel->getOrderStatuses();
    }

    private function getReturnStatusesList()
    {
        $localReturnStatusModel  = $this->load->model('localisation/return_status', ['return' => true]);
        return $localReturnStatusModel->getReturnStatuses();
    }

    /**
     * Check if app installed
     *
     * @return boolean
     */
    public function isInstalled()
    {
        return Extension::isInstalled('microsoft_dynamics');
    }

    /**
     * Check is App Active
     *
     * @return boolean
     */
    public function isActive()
    {
        $settings = $this->getSettings();

        return $this->isInstalled()
            && (int) $settings['status'] === 1
            && !empty($settings['username'])
            && !empty($settings['server_base_uri'])
            && !empty($settings['password']);
    }

    /**
     *   Install the required values for the application.
     *
     *   @return boolean whether successful or not.
     */
    public function install($store_id = 0)
    {
        try {
            $this->db->query(sprintf("CREATE TABLE IF NOT EXISTS `%s` (
                `order_id` INT UNSIGNED NOT NULL,
                `mic_order_id` VARCHAR(50) NOT NULL,
                `mic_return_id` VARCHAR(50) NULL DEFAULT NULL
            )", DB_PREFIX . $this->table));

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     *   Remove the values from the database.
     *
     *   @return boolean whether successful or not.
     */
    public function uninstall($store_id = 0, $group = 'microsoft_dynamics')
    {
        try {

            $this->db->query(sprintf("DROP TABLE IF EXISTS `%s`", DB_PREFIX . $this->table));

            return true;
        } catch (Exception $th) {
            return false;
        }
    }

    private function getDefaultSettings()
    {
        return [
            'status' => 0,
            'username' => null,
            'password' => null,
            'server_base_uri' => null,
            'order_status' => 0,
            'return_status' => 0,
            'product_status' => 0,
            'order_status_ids' => [/* local order statuses ids */],
            'return_status_ids' => [/* local return statuses ids */],
            'product_columns_can_be_modified' => [],
            'product_columns' => [
                'price',
                'quantity'
            ]
        ];
    }

    /**
     * update order status
     * this method will create customer if not exist on microsoft system  then send order to microsoft system
     * @param int $order_id
     * @param int $order_status_id
     * @return bool 
     */
    public function updateOrderStatus($order_id, $order_status_id)
    {
        $settings = $this->getSettings();

        if (
            0 === (int) $settings['status'] ||
            0 === (int) $settings['order_status'] ||
            !in_array($order_status_id, $settings['order_status_ids'])
        ) return false;
        // check if order is created before
        if (!$order = $this->selectLinkOrder($order_id)) {
            $order = $this->createOrder($order_id); // save order
        }

        if (!$order) return false;

        // [order] => mySql (order_id, mic_order_id)
        // TODO: update order status 
    }

    public function createReturnOrder($return_id, $return_status_id)
    {
        $settings = $this->getSettings();

        if (
            0 === (int) $settings['status'] ||
            0 === (int) $settings['return_status'] ||
            !in_array($return_status_id, $settings['return_status_ids'])
        ) return !true;

        $this->load->model('sale/return');
        $return = $this->model_sale_return->getReturn($return_id);
        if (
            !$return ||
            !$orderLink = $this->selectLinkOrder($return['order_id'])
        ) return !true;

        // order is deleted before
        if ($orderLink['mic_return_id'] !== null) return !true;

        $postReturnData = [
            'SalesId' => $orderLink['mic_order_id'],
            'Items' => [],
        ];

        $this->load->model('catalog/product');

        foreach ($return['products'] as $returnProduct) {
            $productInfo = $this->model_catalog_product->getProduct($returnProduct['product_id']);
            if (!$productInfo) continue;
            $postReturnData['Items'][] = [
                'SalesId' => $orderLink['mic_order_id'],
                'Item' => $productInfo['barcode'],
                'Qty' => $returnProduct['quantity'],
            ];
        }

        // no products for return
        if (empty($postReturnData['Items'])) return !true;

        try {
            $returnResult = $this->getInventory()->deleteSalesOrder($postReturnData);
            if ($returnResult->status === true) $this->linkReturnOrder($return['order_id'], $orderLink['mic_order_id'], $returnResult->result);
            return true;
        } catch (\Throwable $th) {
        }

        return !true;
    }

    private function retrieveOrCreateCustomer($sys_customer, $retry = true)
    {
        try {
            $microsoftCustomer = $this->getInventory()->retrieveContact($sys_customer['telephone']);
        } catch (Exception $th) {
            // 404 Customer not found Ex

            if (!$retry) return false; // can't create customer! 

            // save customer to microsoft dynamics system;
            $this->getInventory()->createContact([
                'first_name' => $sys_customer['firstname'],
                'middle_name' => $sys_customer['middle_name'] ?: $sys_customer['lastname'],
                'last_name' => $sys_customer['lastname'],
                'Email' => $sys_customer['email'],
                'dob' => (new DateTime($sys_customer['dob'] ?: date('Y-m-d')))->format('Y-m-d'),
                'Gender' => $this->parseCustomerGender($sys_customer['gender']),
                'phone' => $sys_customer['telephone'],
                'WSCUSTOMERID' => "\"\"",
            ]);

            return $this->retrieveOrCreateCustomer($sys_customer, false);
        }

        // can't retrieve customer!
        if (
            !$microsoftCustomer ||
            !$microsoftCustomer->status === true ||
            !property_exists($microsoftCustomer, 'Customer') ||
            empty($microsoftCustomer->Customer)
        ) {
            return false;
        }

        return $microsoftCustomer->Customer[0];
    }

    private function createOrder($order_id, $retry = true)
    {
        $this->load->model('sale/order');

        $order = $this->model_sale_order->getOrder($order_id);
        if (!$order) return false;

        $this->load->model('sale/customer');

        if (isset($order['customer_id']))
            $customer = $this->model_sale_customer->getCustomer($order['customer_id']);
        else
            $customer = $this->model_sale_customer->getCustomerByEmail($order['email']);

        if (!$customer) return false;
        // get the customer 
        $microsoftCustomer = $this->retrieveOrCreateCustomer($customer);

        if (!$microsoftCustomer) return false;

        $manualShipping = $this->orderHasManualShipping($order_id); // required

        if (!$manualShipping) return false;

        // get order totals
        $orderTotals = $this->model_sale_order->getOrderTotals($order_id);
        $orderTotal = $orderSubtotal = $orderShipping = 0;
        foreach ($orderTotals as $total) {
            if ($total['code'] == 'total') $orderTotal = $total['value'];
            if ($total['code'] == 'sub_total') $orderSubtotal = $total['value'];
            if ($total['code'] == 'shipping') $orderShipping = $total['value'];
        }

        // add order products
        $products = [];
        $this->load->model('catalog/product');
        $orderProducts = $this->model_sale_order->getOrderProducts($order_id);

        foreach ($orderProducts as $orderProduct) {
            $productInfo = $this->model_catalog_product->getProduct($orderProduct['product_id']);

            if (isset($productInfo['barcode']) && !empty($productInfo['barcode'])) {
                // check if product already exist before
                try {
                    $productExist = $this->getInventory()->retrieveItem($productInfo['barcode']);
                    $productExist && array_push($products, [
                        'Item' => $productInfo['barcode'],
                        'InventSiteID' => 'Online',
                        'Store' => 'Online',
                        'Code' => null,
                        'Qty' => $orderProduct['quantity'],
                        'SalesPrice' => number_format($productInfo['price'], 2),
                        'DiscountAmount' => number_format($productInfo['price'] - $orderProduct['price'], 2), // product original price - product price after setting discount
                        'NetPrice' => number_format($orderProduct['price'], 2),
                        'Totals' => number_format($orderProduct['total'], 2),
                        'ItemBarcode' => "\"\"",
                    ]);
                } catch (Exception $th) {
                    // 404 product not found Ex
                }
            }
        }

        // order missing products not complete
        if (count($orderProducts) !== count($products)) return false;

        $orderTotalDiscount = array_sum(array_column($products, 'DiscountAmount'));

        $postOrderData = [
            'CountryID' => $order['shipping_iso_code_3'],
            'CustomerID' => $microsoftCustomer->ACCOUNTNUM,
            'Address' => $order['shipping_address_1'],
            'City_Code' => $order['shipping_zone_code'],
            'Total_Order_Discount' => number_format($orderTotalDiscount, 2),
            'Total' => number_format($orderTotal, 2),
            'Shipping_fees' => 0,
            'COD_Fee' => 0,
            'Subtotal' => number_format($orderSubtotal, 2),
            'InventSite' => 'Ransite',
            'Store' => 'Online',
            'PaymentMethod' => $this->parseOrderPaymentMethod($order),
            'CodCode' => 'Non',
            'CODFeeAmount' => 0,
            'ShippingfeesCode' => 'Non',
            'WSSalesId' => "123456",
            'WSCustomerId' => "123",
            'Items' => $products,
        ];

        // add manual shipping details
        // the account code should saved in manual shipping config 
        $manualShippingAccountCode = $manualShipping['shipping_company_id'] ?? 'Cust-014637'; // manual shipping code for ABS
        $postOrderData += [
            'InvoiceAccount' => $manualShippingAccountCode,
        ];

        // add shipping cost as a product related to current manual shipping 
        $manualShippingAccountNumber = $manualShipping['sku_number'] ?: 1074555; //  manual shipping item number for ABS
        array_push($postOrderData['Items'], [
            'InventSiteID' => 'Online', // required fields!
            'Store' => 'Online', // required fields!
            'ItemBarcode' => "\"\"", // required fields!
            'Item' => $manualShippingAccountNumber,
            'Total' => number_format($orderShipping, 2),
        ]);

        try {
            $orderResult = $this->inventory->createSalesOrder($postOrderData);
            if ($orderResult->status === true)
                $this->linkOrder($order_id, $orderResult->result);

            return $this->selectLinkOrder($order_id);
        } catch (Exception $th) {
            // can't create order!
        }
        return false;
    }

    private function parseCustomerGender($genderT)
    {
        // based on desc 
        return $genderT == 'm' ? 1 : ($genderT == 'f' ? 2 : 0);
    }

    private function parseOrderPaymentMethod($order)
    {
        return $order['payment_code'] == 'cod' ? 'Cash' : 'Bank';
    }

    private function orderHasManualShipping($order_id)
    {
        $gateway = null;

        $this->load->model('sale/order');
        $this->load->model('extension/shipping');

        $bundledShippingMethods = $this->model_extension_shipping->getEnabled();
        $manualShippingInfo = $this->model_sale_order->getManualShippingGatewayId($order_id);

        foreach ($bundledShippingMethods as $bundledShippingMethod) {
            if ($manualShippingInfo['is_manual'] == 0 && $manualShippingInfo['id'] == $bundledShippingMethod['id']) {
                $gateway = $bundledShippingMethod;
            }
        }

        if (Extension::isInstalled('manual_shipping')) {
            $manualShipping = $this->config->get('manual_shipping');

            if (isset($manualShipping['status']) && $manualShipping['status'] == 1) {

                $this->language->load('module/manual_shipping');
                $manualShippingOrder = $this->load->model('module/manual_shipping/order', ['return' => true]);
                $manualShippingGatewaysModel = $this->load->model('module/manual_shipping/gateways', ['return' => true]);

                $manualShippingGateId = $manualShippingOrder->getManualShippingGatewayId($order_id);

                $manualShippingGateways = $manualShippingGatewaysModel->getCompactShippingGateways([
                    'start' => -1,
                    'status' => 1,
                    'language_id' => $this->config->get('config_language_id'),
                ]);

                $manualShippingGateways = array_column($manualShippingGateways['data'], null, 'id');
                if (isset($manualShippingGateways[$manualShippingGateId])) {
                    $manualShippingGateway = $manualShippingGateways[$manualShippingGateId];
                    $gateway = $manualShippingGateway;
                }
            }
        }

        return $gateway;
    }

    private function selectLinkOrder($order_id)
    {
        $query = $this->db->query(sprintf("SELECT order_id, mic_order_id, mic_return_id FROM `%s` WHERE `order_id` = %d", DB_PREFIX . $this->table, (int) $order_id));
        return $query->num_rows ? $query->row : null;
    }

    private function linkOrder($order_id, $mic_order_id)
    {
        $link = $this->selectLinkOrder($order_id);

        if (!$link) {
            $this->db->query(sprintf('INSERT INTO `%s` SET `order_id` = %d, mic_order_id = "%s"', DB_PREFIX . $this->table, (int)$order_id, $mic_order_id));
        }
    }

    private function linkReturnOrder($order_id, $mic_order_id, $mic_return_id)
    {
        $this->db->query(sprintf(
            'UPDATE `%s` SET `mic_return_id` = "%s" WHERE `order_id` = %d',
            DB_PREFIX . $this->table,
            $mic_return_id,
            (int) $order_id
        ));
    }

    public function startSyncProducts($date)
    {
        exec_command(sprintf("php %s module/microsoft_dynamics:syncProducts %s", DIR_SYSTEM . 'library/call_admin_controller.php', $date));
    }

    /**
     * sync microsoft-dynamics products 
     *
     * @param string $per_page 
     * @param string $date Y-m-d || null
     * @return void
     */
    public function syncProducts(string $date = null, int $per_page = 200)
    {
        $settings = $this->getSettings();
        $columnsCanModified = array_unique($settings['product_columns_can_be_modified'] ?: []);
        if (empty($columnsCanModified)) return false;

        $date = $date ?: date('Y-m-d');

        $this->log(sprintf('Start getting all the products that have been modified on a date: %s.', $date));

        $microsoftProducts = $this->retrieveMicrosoftProducts(1, $per_page, $date);
        $this->log(sprintf('Start validate %d product(s).', count($microsoftProducts)));

        $productModel = $this->load->model('catalog/product', ['return' => true]);

        $tempProducts = [];

        foreach ($microsoftProducts as $value) {
            $value = $this->objectToArray($value);
            if (
                !array_key_exists('Ax_ID', $value) ||
                !array_key_exists('OriginalPrice', $value) ||
                !array_key_exists('Qty',  $value)
            ) continue;

            $products = $productModel->getProductByBarcode($value['Ax_ID']);
            if (!$products) continue;

            foreach ($products as $product) {
                $tempData = [];
                foreach ($columnsCanModified  as $col) {
                    if ($col === "price" && (float)$product['price'] !== (float)$value['OriginalPrice']) {
                        $tempData[] = ['column' => 'price', 'value' => (float)$value['OriginalPrice']];
                    } else if ($col === 'quantity') {
                        $storeQuantities = $value['Qty'];
                        $qPos = array_search("online", array_map('strtolower', array_column($storeQuantities, "STORE_CODE")));
                        $storeQuantity = $storeQuantities[$qPos]['Value'] ?: 0;

                        if ($qPos !== false && (int)$product['quantity'] !== (int)$storeQuantity) {
                            $tempData[] = ['column' => 'quantity', 'value' => (int)$storeQuantity];
                        }
                    }
                }

                if (!empty($tempData)) {
                    $tempProducts[$product['product_id']] = $tempData;
                }
            }
        }

        // update products with new values
        if (!empty($tempProducts)) {
            foreach ($tempProducts as $key => $value) $productModel->updateProductMultipleValues($key, $value);
            $this->log(sprintf("The data of %d product(s) has been modified successfully.", count($tempProducts)));
        } else
            $this->log(sprintf("There are no updated products at %s.", $date));
    }

    private function retrieveMicrosoftProducts(int $page, int $per_page, string $modified_date)
    {
        try {
            $result = $this->getInventory()->listItems(compact('page', 'per_page', 'modified_date'));
            if ($result->status === true) {

                $products = $result->products;

                if ((int)$result->TotalPages > $page) {
                    $products = array_merge($products, $this->retrieveMicrosoftProducts(++$page, $per_page, $modified_date));
                }

                return $products;
            }
        } catch (Exception $th) {
            //products not found;
        }

        return [];
    }

    private function objectToArray(stdClass $obj)
    {
        return json_decode(json_encode($obj), true);
    }

    private function log($resource)
    {
        $this->log->write(is_array($resource) ? json_encode($resource) : $resource);
    }
}
