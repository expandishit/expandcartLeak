<?php

use ExpandCart\Foundation\Inventory\Inventory;
use ExpandCart\Foundation\Providers\Extension;
use ExpandCart\Foundation\Inventory\Clients\Zoho;


class ModelModuleZohoInventory extends Model
{

    private $inventory;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->setInventory(new Inventory(new Zoho, $this->getSettings()));
    }

    /**
     * Get the value of inventory
     */
    public function getInventory()
    {
        return $this->inventory;
    }

    /**
     * Set the value of inventory
     *
     * @return  self
     */
    public function setInventory(Inventory $inventory)
    {
        $this->inventory = $inventory;

        return $this;
    }

    public function getSettings()
    {
        return array_merge($this->getDefaultSettings(), $this->config->get('zoho_inventory') ?? []);
    }

    /**
     * Check if app installed
     *
     * @return boolean
     */
    public function isInstalled()
    {
        return Extension::isInstalled('zoho_inventory');
    }

    /**
     * Check is App Active
     *
     * @return boolean
     */
    public function isActive()
    {
        return $this->isInstalled() && (int) $this->getSettings()['status'] === 1;
    }

    private function getDefaultSettings()
    {
        return [
            'status' => 0,
            'authtoken' => '',
            'organization_id' => ''
        ];
    }

    //************************************** products *********************************************

    public function createProduct($product_id, $data)
    {
        if (!$this->isActive()) return false;

        $result = $this->getInventory()->createItem($this->mapProductData($data));
        if ($result->status === true) {
            $this->linkProduct($product_id, $result->item->item_id);
            $this->changeProductStatus($product_id, $data['status']);
        }

        return $result;
    }

    public function changeProductStatus($product_id, $status)
    {
        if (!$this->isActive()) return false;

        $link = $this->selectLinkProduct($product_id);

        if ($link) {
            if ((int)$status == 1) {
                $this->getInventory()->activeItem($link['zoho_product_id']);
            } else {
                $this->getInventory()->inactiveItem($link['zoho_product_id']);
            }
        }
    }

    private function mapProductData(array $data)
    {
        $product = [];
        $description = null;

        foreach ($data['product_description'] as $language_id => $value) {
            if (!$description && ($value['name'] && $value['description'])) {
                $description = $value;
                break;
            }
        }
        $product['unit'] = $this->detectProductUnit($data['weight_class_id'] ?? 0);
        $product['description'] = strip_tags($description ? $description['description'] : 'unknown');
        $product['name'] = $description ? $description['name'] : 'unknown';
        $product['sku'] = $data['sku'] ?? "";
        $product['upc'] = $data['upc'] ?? "";
        $product['ean'] = $data['ean'] ?? "";
        $product['isbn'] = $data['isbn'] ?? "";
        $product['jan'] = $data['jan'] ?? "";
        $product['mpn'] = $data['mpn'] ?? "";
        $product['rate'] = (float)$data['price'];
        $product['purchase_rate'] = (float)$data['cost_price'];
        $product['item_type'] = 'inventory';
        $product['product_type'] = 'goods';
        $product['package_details'] = [
            'length' => (float)$data['length'],
            'width' => (float)$data['width'],
            'height' => (float)$data['height'],
            'weight' => (float) $data['weight']
        ];

        $product['initial_stock'] = (int)$data['quantity'];
        $product['is_returnable'] = true; // true|false


        // $product['initial_stock_rate'] = (float)$data['price'] - (float)$data['cost_price'];

        return $product;
    }

    private function detectProductUnit($weight_class_id)
    {
        $class_descriptions = $this->db->query("SELECT DISTINCT unit FROM " . DB_PREFIX . "weight_class_description WHERE weight_class_id = '" . (int)$weight_class_id . "'");
        if ($class_descriptions->num_rows) {
            return $class_descriptions->row['unit'];
        }

        return '';
    }

    private function linkProduct($product_id, $zoho_product_id)
    {
        $link = $this->selectLinkProduct($product_id);

        if (!$link) {
            $this->db->query("INSERT INTO  " . DB_PREFIX . "zoho_products SET product_id = '" . (int)$product_id . "', zoho_product_id = '" . $zoho_product_id . "'");
        }
    }

    private function selectLinkProduct($product_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zoho_products WHERE product_id = '" . (int)$product_id . "'");
        return $query->num_rows ? $query->row : null;
    }

    //************************************** Customers *********************************************

    public function createCustomer($customer_id, array $data = null)
    {
        if (!$data) return false;
        if (!$this->isActive()) return false;

        try {
            $result = $this->getInventory()->createContact($this->mapCustomerData($data));
            if ($result->status === true) {
                $this->linkCustomer($customer_id, $result->contact->contact_id);
                $this->changeCustomerStatus($customer_id, $data['status']);
            }
        } catch (Exception $exception) {
            $result = stdClass::class;
            $result->status = false;
        }

        return $result;
    }

    public function updateCustomer($customer_id, $data)
    {
        if (!$this->isActive()) return false;

        $link = $this->selectLinkCustomer($customer_id);
        if ($link) {
            $zoho_customer_result = $this->getInventory()->retrieveContact($link['zoho_customer_id']);

            if ($zoho_customer_result->status === true) {
                if (!empty($zoho_customer_result->contact->contact_persons)) {
                    $data['contact_person_id'] = $zoho_customer_result->contact->contact_persons[0]->contact_person_id;
                }

                $result = $this->getInventory()->updateContact($link['zoho_customer_id'], $this->mapCustomerData($data));

                if ($result->status === true) {
                    $this->changeCustomerStatus($customer_id, $data['status']);
                }

                return $result;
            }
        }
        // save new Customer to inventory
        return $this->createCustomer($customer_id, $data);
    }

    public function changeCustomerStatus($customer_id, $status)
    {
        if (!$this->isActive()) return false;
        $link = $this->selectLinkCustomer($customer_id);
        if ($link) {
            if ((int)$status === 1) {
                $this->getInventory()->activeContact($link['zoho_customer_id']);
            } else {
                $this->getInventory()->inactiveContact($link['zoho_customer_id']);
            }
        }
    }

    /**
     * map customer data
     *
     * @param array $data the original request data
     * @return array converted data
     * @see https://www.zoho.com/inventory/api/v1/#Contacts
     */
    private function mapCustomerData($data)
    {

        $customer_data = [
            'contact_name' => $data['firstname'] . ' ' . $data['lastname'], // required
            'contact_type' => 'customer', // optional allowed types [customer, vendor]
            'language_code' => $this->config->get('config_admin_language'), // optional
            'currency_id' => $this->detectCustomerCurrency(),
            'contact_persons' => [
                [
                    'salutation' => '',
                    'first_name' => $data['firstname'],
                    'last_name' => $data['lastname'],
                    'email' => $data['email'],
                    'phone' => $data['telephone'],
                    'mobile' => $data['telephone'],
                    'is_primary_contact' => true,
                ]
            ]
        ];

        if (isset($data['contact_person_id'])) $customer_data['contact_persons'][0]['contact_person_id'] = $data['contact_person_id'];
        if (isset($data['billing_address'])) $customer_data['billing_address'] = $data['billing_address'];
        if (isset($data['shipping_address'])) $customer_data['shipping_address'] = $data['shipping_address'];

        return $customer_data;
    }

    private function detectCustomerCurrency()
    {
        $query = $this->db->query("SELECT code FROM " . DB_PREFIX . "currency WHERE status = '1'");
        if ($query->num_rows) {
            $system_currency_code = strtoupper($query->row['code']);
            try {
                $zoho_currencies_result = $this->getInventory()->listCurrencies();
                if ($zoho_currencies_result->status === true) {
                    $zoho_currencies = array_map(function ($currency) {
                        return $this->objectToArray($currency);
                    }, $zoho_currencies_result->currencies);

                    if ($key = array_search($system_currency_code, array_column($zoho_currencies, 'currency_code'))) {
                        return $zoho_currencies[$key]['currency_id'];
                    }
                }
            } catch (Exception $exception) {
                return null;
            }


        }

        return null;
    }

    private function linkCustomer($customer_id, $zoho_customer_id)
    {
        $link = $this->selectLinkCustomer($customer_id);

        if (!$link) {
            $this->db->query("INSERT INTO  " . DB_PREFIX . "zoho_customers SET customer_id = '" . (int)$customer_id . "', zoho_customer_id = '" . $zoho_customer_id . "'");
        }
    }

    private function selectLinkCustomer($customer_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zoho_customers WHERE customer_id = '" . (int)$customer_id . "'");
        return $query->num_rows ? $query->row : null;
    }

    //************************************** Orders *********************************************

    private const ORDER_CONFIRMED_STATUSES = [5, 25, 26];

    public function createOrder($order_id, array $order, array $products)
    {
        if (!$this->isActive()) return false;

        if (!isset($order_id)) return false;

        if (!isset($order['customer_id'])) return false;

        $customer_link = $this->selectLinkCustomer($order['customer_id']);

        $customer_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE customer_id = '" . (int)$order['customer_id'] . "'");
        if (!$customer_query->num_rows) {
            return false;
        }


        $customer_data = $customer_query->row;

        $customer_data['shipping_address'] = [
            'attention' => $order['shipping_firstname'],
            'address' => $order['shipping_address_1'],
            'city' => $order['shipping_city'],
            'state' => $order['shipping_zone_code'],
            'zip' => $order['shipping_postcode'],
            'country' => $order['shipping_country'],
            'phone' => $order['shipping_telephone'],
        ];

        $customer_data['billing_address'] = [
            'attention' => $order['payment_firstname'],
            'address' => $order['payment_address_1'],
            'city' => $order['payment_city'],
            'state' => $order['payment_zone_code'],
            'zip' => $order['payment_postcode'],
            'country' => $order['payment_country'],
            'phone' => $order['payment_telephone'],
        ];

        if (!$customer_link) {
            // create new customer
            $zoho_customer_result = $this->createCustomer(
                $order['customer_id'],
                $customer_data
            );

            if ($zoho_customer_result->status === true) {
                return $this->createOrder($order_id, $order, $products);
            } else {
                // cannot create new zoho customer!
                return false;
            }
        } else {
            $zoho_customer_result = $this->updateCustomer($order['customer_id'], $customer_data);
        }

        $zoho_order = $this->mapOrderData($customer_link['zoho_customer_id'], $order, $products);


        // save order
        if (!empty($zoho_order['line_items'])) {

            $order_result = $this->getInventory()->createSalesOrder($zoho_order, true);

            if ($order_result->status === true) {
                $this->linkOrder($order_id, $order_result->salesorder->salesorder_id);
                // mark as confirmed 
                if (in_array($order['order_status_id'], self::ORDER_CONFIRMED_STATUSES)) {
                    $this->getInventory()->confirmSalesOrder($order_result->salesorder->salesorder_id);
                }
                // create order invoice
                $this->createInvoice($order_id);
            }

            return $order_result;
        }

        return false;
    }

    private function mapOrderData($zoho_customer_id, $order, $products)
    {
        return [
            'customer_id' => $zoho_customer_id,
            'salesorder_number' => uniqid(),
            'reference_number' => $order['invoice_prefix'],
            'delivery_method' => $order['payment_method'],
            'shipping_charge' => $this->calculateOrderShippingCharge($order['total'], $products),
            'line_items' => array_reduce($products, function ($accumulator, $product) use ($order) {
                $valid_product = $this->prepareProductOrder($order['language_id'], $product);
                if ($valid_product) array_push($accumulator, $valid_product);
                return $accumulator;
            }, [])
        ];
    }

    private function calculateOrderShippingCharge($total, $products)
    {
        $has_tax = $this->config->get('config_tax');

        $shipping_charge = array_reduce($products, function ($accumulator, $product) use ($has_tax) {
            return $accumulator -= ((float)$product['total'] + ($has_tax ? ($product['tax'] * $product['quantity']) : 0));
        }, (float)$total);

        return $shipping_charge;
    }

    private function prepareProductOrder($language_id, array $product)
    {
        if (!isset($product['product_id'])) return null;

        // check if product saved in zoho
        $product_link = $this->selectLinkProduct($product['product_id']);

        // select system product
        $product_query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p.product_id = '" . (int)$product['product_id'] . "' AND pd.language_id = '" . (int)$language_id . "'");

        if (!$product_query->num_rows) {
            return null;
        }

        $product_data = $product_query->row;

        // save new product in zoho
        if (!$product_link) {
            $product_data['product_description'] = [
                $language_id => [
                    'name' => $product_data['name'],
                    'description' => $product_data['description']
                ]
            ];

            $zoho_product_result = $this->createProduct($product['product_id'], $product_data);

            if ($zoho_product_result->status === true) {
                return $this->prepareProductOrder($language_id, $product);
            } else {
                // cannot create new zoho product!
                return null;
            }
        }

        $has_tax = $this->config->get('config_tax');

        // format product to zoho item basic data
        return [
            'item_id' => $product_link['zoho_product_id'],
            'name' => $product_data['name'],
            'description' => strip_tags($product_data['description']),
            'rate' => (float)$product['price'] + ($has_tax ? $product['tax'] : 0),
            'quantity' => (int)$product['quantity'],
            'item_total' => (float)$product['total'] + ($has_tax ? ($product['tax'] * $product['quantity']) : 0),
        ];
    }

    private function linkOrder($order_id, $zoho_order_id)
    {
        $link = $this->selectLinkOrder($order_id);

        if (!$link) {
            $this->db->query("INSERT INTO  " . DB_PREFIX . "zoho_sales_orders SET order_id = '" . (int)$order_id . "', zoho_order_id = '" . $zoho_order_id . "'");
        }
    }

    private function selectLinkOrder($order_id)
    {
        $query = $this->db->query("SELECT order_id, zoho_order_id FROM " . DB_PREFIX . "zoho_sales_orders WHERE order_id = '" . (int)$order_id . "'");
        return $query->num_rows ? $query->row : null;
    }

    //************************************** Invoices *********************************************

    public function createInvoice($system_order_id)
    {
        if (!$this->isActive()) return false;

        $order_link = $this->selectLinkOrder($system_order_id);

        if (!$order_link) return false;

        $invoice_link = $this->selectLinkInvoice($system_order_id);

        if ($invoice_link) {
            // the invoice already created for this order
            return true;
        }

        // retrieve the order;
        $order_result = $this->getInventory()->retrieveSalesOrder($order_link['zoho_order_id']);

        if ($order_result->status === true) {
            $invoice_result = $this->getInventory()->createInvoice($invoice_data = $this->mapInvoiceData($order_result->salesorder));
            if ($invoice_result->status === true) {
                $this->linkInvoice($system_order_id, $invoice_result->invoice->invoice_id);
            }

            return $invoice_result;
        }

        // can not find zoho order
        return false;
    }

    private function mapInvoiceData(stdClass $zoho_order)
    {
        $zoho_order = $this->objectToArray($zoho_order);
        $contact_persons = !empty($zoho_order['contact_persons'])
            ? $zoho_order['contact_persons']
            : $zoho_order['contact_person_details'];

        if (!empty($contact_persons) && isset($contact_persons[0]['contact_person_id'])) {
            $contact_persons = [$contact_persons[0]['contact_person_id']];
        } else {
            $contact_persons = [];
        }

        return [
            'customer_id' => $zoho_order['customer_id'],
            'contact_persons' => $contact_persons,
            'salesperson_name' => $zoho_order['salesperson_name'],
            'line_items' => array_map(function ($item) {
                return [
                    'item_order' => $item['item_order'],
                    'item_id' => $item['item_id'],
                    'rate' => (float)$item['rate'],
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'quantity' => (int)$item['quantity'],
                    'discount' => $item['discount'] ?: 0,
                    'tax_id' => $item['tax_id'],
                    'salesorder_item_id' => $item['line_item_id'],
                    'unit' => $item['unit'],
                    'bcy_rate' => (float)$item['bcy_rate'],
                    'discount_amount' => $item['discount_amount'] ?: 0,
                    'tax_name' => $item['tax_name'],
                    'tax_type' => $item['tax_type'],
                    'tax_percentage' => $item['tax_percentage'],
                    'item_total' => $item['item_total'],
                ];
            }, $zoho_order['line_items']),
            'billing_address_id' => $zoho_order['billing_address_id'],
            'shipping_address_id' => $zoho_order['shipping_address_id'],
            'shipping_charge' => $zoho_order['shipping_charge'],
        ];
    }

    private function selectLinkInvoice($order_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zoho_invoices WHERE order_id = '" . (int)$order_id . "'");
        return $query->num_rows ? $query->row : null;
    }

    private function linkInvoice($order_id, $zoho_invoice_id)
    {
        $link = $this->selectLinkInvoice($order_id);

        if (!$link) {
            $this->db->query("INSERT INTO  " . DB_PREFIX . "zoho_invoices SET order_id = '" . (int)$order_id . "', zoho_invoice_id = '" . $zoho_invoice_id . "'");
        }
    }
    
    //************************************** Others *********************************************
    
    private function objectToArray(stdClass $obj)
    {
        return json_decode(json_encode($obj), true);
    }

}
