<?php

namespace ExpandCart\Foundation\Inventory\Clients;

use ExpandCart\Foundation\Inventory\Traits\ConsumesExternalServices;


/**
 * This class is based on a pattern (TM) Template Method,
 * This class is parent of all supported external clients for inventories.
 */
abstract class Client
{
    use ConsumesExternalServices;

    protected $baseUri;

    protected $httpVerifySslCertificate = true;

    protected $config;

    /**
     * Client constructor.
     * @param array $config the credentials for current Client
     * @throws \Exception
     */
    public function __construct(array $config = null)
    {
        if ($config) {
            $this->setConfig($config);
        }
    }

    /**
     * Set current client config
     *
     * @param array $config
     * @return self
     */
    public function setConfig(array $config = null)
    {
        $this->config = array_merge($this->config ?? [], $config ?? []);
        $this->validateConfiguration();
        return $this;
    }

    /**
     * Get the value of config
     */
    public function getConfig()
    {
        return $this->config;
    }

    //************************************** Item *********************************************

    /**
     * @param array $params array of properties for the new item
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    abstract public function createItem(array $params);

    /**
     * @param string $item_id item to update
     * @param array $params array of new properties
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    abstract public function updateItem(string $item_id, array $params);

    /**
     * @param string $item_id to retrieve
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    abstract public function retrieveItem(string $item_id = null);

    /**
     * List all items
     * @param array $filters an extra option used in zoho web application allows you to filter items by specific fields, leave empty to list all items
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    abstract public function listItems(array $filters = []);

    abstract public function readItems(array $ids=[], array $fields = []);
  

    /**
     * an extra function allows you to search items by text portion
     * @param string $search_text
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    abstract function searchItem(string $search_text);

    /**
     * Deletes an item
     * @param string $item_id item to delete
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    abstract public function deleteItem(string $item_id , string $org_id);

    /**
     * Change status of the item to <strong>active</strong>
     * @param string $item_id
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    abstract public function activeItem(string $item_id);

    /**
     * Change status of the item to <strong>inactive</strong>
     * @param string $item_id
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    abstract public function inactiveItem(string $item_id);

    //************************************** Purchase Order *********************************************

    /**
     * Create a purchase order
     * @param array $params
     * @param bool $ignore ignore auto number generation, if true you must specify the new id, default is false
     * @return \stdClass
     * @throws Inventory\Exception\ClientException
     */
    abstract public function createPurchaseOrder(array $params, $ignore = false);

    /**
     * Update details of an existing purchase order
     * @param string $purchaseorder_id to update
     * @param array $params
     * @param bool $ignore ignore auto number generation, if true you must specify the new id, default is false
     * @return \stdClass
     * @throws Inventory\Exception\ClientException
     */
    abstract public function updatePurchaseOrder($purchaseorder_id, array $params, $ignore = false);

    /**
     * Retrieve a purchase order
     * @param string $purchaseorder_id to retrieve, leave empty to list all purchase orders
     * @return \stdClass
     * @throws Inventory\Exception\ClientException
     */
    abstract public function retrievePurchaseOrder($purchaseorder_id = null);

    /**
     * List all purchase order
     * @return \stdClass
     * @throws Inventory\Exception\ClientException
     */
    abstract public function listPurchaseOrders();

    /**
     * Delete an purchase order
     * @param string $purchaseorder_id to be deleted
     * @return \stdClass
     * @throws Inventory\Exception\ClientException
     */
    abstract public function deletePurchaseOrder($purchaseorder_id);

    /**
     * Mark as issued
     * @param string $purchaseorder_id
     * @return \stdClass
     * @throws Inventory\Exception\ClientException
     */
    abstract public function issuePurchaseOrder($purchaseorder_id);

    /**
     * Mark as cancelled
     * @param string $purchaseorder_id
     * @return \stdClass
     * @throws Inventory\Exception\ClientException
     */
    abstract public function cancelPurchaseOrder($purchaseorder_id);

    //************************************** Sales Order *********************************************

    /**
     * Create a sales order
     * @param array $params
     * @param bool $ignore ignore auto number generation, if true you must specify the new id, default is false
     * @return \stdClass
     * @throws Inventory\Exception\ClientException
     */
    abstract public function createSalesOrder(array $params = [], $ignore = false);
     /**
     * Create a purchase order products
     * @param array $params
     * @param bool $ignore ignore auto number generation, if true you must specify the new id, default is false
     * @return \stdClass
     * @throws Inventory\Exception\ClientException
     */
    abstract public function createSalesOrderProducts(array $params = [], $ignore = false);

    /**
     * Update details of an existing sales order
     * @param string $salesorder_id to update
     * @param array $params
     * @param bool $ignore ignore auto number generation, if true you must specify the new id, default is false
     * @return \stdClass
     * @throws Inventory\Exception\ClientException
     */
    abstract public function updateSalesOrder($salesorder_id, array $params, $ignore = false);

    /**
     * Retrieve a sales order
     * @param string $salesorder_id to retrieve, leave empty to list all purchase orders
     * @return \stdClass
     * @throws Inventory\Exception\ClientException
     */
    abstract public function retrieveSalesOrder($salesorder_id = null);

    /**
     * List all sales order
     * @return \stdClass
     * @throws Inventory\Exception\ClientException
     */
 
    abstract public function listSalesOrders(array $filters = []);

    abstract public function readSalesOrders(array $ids=[], array $fields = []);
    
    abstract public function readSalesOrdersProducts(array $ids=[], array $fields = []);
    

    /**
     * Delete an sales order
     * @param string $salesorder_id to be deleted
     * @return \stdClass
     * @throws Inventory\Exception\ClientException
     */
    abstract public function deleteSalesOrder($salesorder_id);

    /**
     * Mark as void
     * @param string $salesorder_id
     * @return \stdClass
     * @throws Inventory\Exception\ClientException
     */
    abstract public function voidSalesOrder($salesorder_id);

    /**
     * Mark as confirmed
     * @param string $salesorder_id
     * @return \stdClass
     * @throws Inventory\Exception\ClientException
     */
    abstract public function confirmSalesOrder($salesorder_id);

    //************************************** Invoices *********************************************

    /**
     * create invoice
     * @param array $params
     * @param bool $ignore ignore auto number generation, if true you must specify the new id, default is false
     * @return bool|mixed|string
     * @throws Inventory\Exception\ClientException
     */
    abstract public function createInvoice(array $params = [], $ignore = false);

    /**
     * update Invoice
     * @param $invoice_id
     * @param array $params
     * @param bool $ignore ignore auto number generation, if true you must specify the new id, default is false
     * @return bool|mixed|string
     * @throws Inventory\Exception\ClientException
     */
    abstract public function updateInvoice($invoice_id, array $params, $ignore = false);

    /**
     * retrieve a single invoice
     * @param null $invoice_id
     * @return bool|mixed|string
     * @throws Inventory\Exception\ClientException
     */
    abstract public function retrieveInvoice($invoice_id = null);

    /**
     * list all Invoice
     * @return bool|mixed|string
     * @throws Inventory\Exception\ClientException
     */
    abstract public function listInvoices();

    /**
     * delete a single invoice
     * @param $invoice_id
     * @return bool|mixed|string
     * @throws Inventory\Exception\ClientException
     */
    abstract public function deleteInvoice($invoice_id);

    /**
     * mark invoice as a void
     * @param $invoice_id
     * @return bool|mixed|string
     * @throws Inventory\Exception\ClientException
     */
    abstract public function voidInvoice($invoice_id);

    //************************************** Contacts *********************************************

    /**
     * @param array $params
     * @return \stdClass
     * @throws Inventory\Exception\ClientException
     */
    abstract public function createContact(array $params = []);

    /**
     * @param string $contact_id contact id to update
     * @param array $params
     * @return \stdClass
     * @throws Inventory\Exception\ClientException
     */
    abstract public function updateContact($contact_id, array $params = []);

    /**
     * @param string $contact_id leave empty to list all contacts
     * @param array $filters filters array, use constants STATUS_.. to filter by contact type
     * @return \stdClass
     * @throws Inventory\Exception\ClientException
     */
    abstract public function retrieveContact($contact_id = null, array $filters = []);

    /**
     * @param array $filters filters array, use constants STATUS_.. to filter by contact type
     * @return \stdClass
     * @throws Inventory\Exception\ClientException
     */
    abstract public function listContacts(array $filters = []);

    abstract public function readContacts(array $ids=[], array $fields = []);

    /**
     * @param string $contact_id contact id to delete
     * @return \stdClass
     * @throws Inventory\Exception\ClientException
     */
    abstract public function deleteContact($contact_id);

    /**
     * @param string $contact_id contact id to active
     * @return \stdClass
     * @throws Inventory\Exception\ClientException
     */
    abstract public function activeContact($contact_id);

    /**
     * @param string $contact_id contact id to inactive
     * @return \stdClass
     * @throws Inventory\Exception\ClientException
     */
    abstract public function inactiveContact($contact_id);

    //************************************** Currencies *********************************************

    /**
     * List all Currencies
     * @param array $filters an extra option used in zoho web application allows you to filter Currencies by specific fields, leave empty to list all Currencies
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    abstract public function listCurrencies(array $filters = []);

    /**
     * Check current configuration validately
     * @return boolean
     */
    abstract public function validateConfiguration();

    abstract protected function resolveAuthorization(&$queryParams, &$formParams, &$headers);

    protected function decodeResponse($response)
    {
        return json_decode($response);
    }
    abstract public function validateAuth();

}
