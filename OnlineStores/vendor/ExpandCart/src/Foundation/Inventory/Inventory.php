<?php

namespace ExpandCart\Foundation\Inventory;

use ExpandCart\Foundation\Inventory\Clients\Client;
use ExpandCart\Foundation\Inventory\Exception\InventoryException;


/**
 * This class is based on a Solid Principles (DIP) Dependency Inversion,
 * It allows you to manage external inventory of products, customers, orders and invoices.
 * So far, the class supports [ZOHO] inventory.
 * created date 8/26/2020
 */
final class Inventory implements InventoryInterface
{
    private $client;

    private $config;


    public function __construct(Client $client = null, array $config = null)
    {
        $this->client = $client;
        $this->config = $config;
    }

    /**
     * Get the value of client
     * @return Client
     * @throws \ExpandCart\Foundation\Inventory\Exception\InventoryException
     */
    public function getClient(): Client
    {
        if (!$this->client) {
            throw new InventoryException('You have to set \'client\' to use inventory api\'s');
        }

        return $this->client;
    }

    /**
     * Set the value of client
     *
     * @return  self
     */
    public function setClient(Client $client): Inventory
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get the value of config
     */
    public function getConfig()
    {
        if (!$this->config && ($this->client && !$this->client->getConfig())) {
            throw new InventoryException('You have to set \'config\' to use inventory api\'s');
        }

        return $this->config ?? $this->client->getConfig();
    }

    /**
     * Set the value of config
     *
     * @return self
     */
    public function setConfig(array $config): Inventory
    {
        $this->config = $config;

        return $this;
    }

    //************************************** Item *********************************************

    public function createItem(array $item)
    {
        return $this->prepareClient()->createItem($item);
    }

    public function updateItem(string $item_id, array $item)
    {
        return $this->prepareClient()->updateItem($item_id, $item);
    }

    public function retrieveItem(string $item_id)
    {
        return $this->prepareClient()->retrieveItem($item_id);
    }

    /**
     * List all items
     * @param array $filters an extra option used in zoho web application allows you to filter items by specific fields, leave empty to list all items
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function listItems(array $filters = [])
    {
        return $this->prepareClient()->listItems($filters);
    }
    public function readItems(array $ids=[] , array $fields = [])
    {
        return $this->prepareClient()->readItems($ids,$fields);
    }
  
    public function searchItem($search_text)
    {
        return $this->prepareClient()->searchItem($search_text);
    }

    public function deleteItem($item_id,$org_id)
    {
        return $this->prepareClient()->deleteItem($item_id,$org_id);
    }

    public function activeItem($item_id)
    {
        return $this->prepareClient()->activeItem($item_id);
    }

    public function inactiveItem($item_id)
    {
        return $this->prepareClient()->inactiveItem($item_id);
    }

    public function updateItemImage($item_id, $path)
    {
        return $this->prepareClient()->updateItemImage($item_id,$path);
    }

    public function getImageUrl($item_id)
    {
        return $this->prepareClient()->getImageUrl($item_id);
    }

    //************************************** Purchase Order *********************************************

    /**
     * Create a purchase order
     * @param array $params
     * @param bool $ignore ignore auto number generation, if true you must specify the new id, default is false
     * @return \stdClass
     * @throws Inventory\Exception\ClientException
     */
    public function createPurchaseOrder(array $params, $ignore = false)
    {
        return $this->prepareClient()->createPurchaseOrder($params, $ignore);
    }

    /**
     * Update details of an existing purchase order
     * @param string $purchaseorder_id to update
     * @param array $params
     * @param bool $ignore ignore auto number generation, if true you must specify the new id, default is false
     * @return \stdClass
     * @throws Inventory\Exception\ClientException
     */
    public function updatePurchaseOrder($purchaseorder_id, array $params, $ignore = false)
    {
        return $this->prepareClient()->updatePurchaseOrder($purchaseorder_id, $params, $ignore);
    }

    /**
     * Retrieve a purchase order
     * @param string $purchaseorder_id to retrieve, leave empty to list all purchase orders
     * @return \stdClass
     * @throws Inventory\Exception\ClientException
     */
    public function retrievePurchaseOrder($purchaseorder_id = null)
    {
        return $this->prepareClient()->retrievePurchaseOrder($purchaseorder_id);
    }

    /**
     * List all purchase order
     * @return \stdClass
     * @throws Inventory\Exception\ClientException
     */
    public function listPurchaseOrders()
    {
        return $this->prepareClient()->retrievePurchaseOrder();
    }

    /**
     * Delete an purchase order
     * @param string $purchaseorder_id to be deleted
     * @return \stdClass
     * @throws Inventory\Exception\ClientException
     */
    public function deletePurchaseOrder($purchaseorder_id)
    {
        return $this->prepareClient()->deletePurchaseOrder($purchaseorder_id);
    }

    /**
     * Mark as issued
     * @param string $purchaseorder_id
     * @return \stdClass
     * @throws Inventory\Exception\ClientException
     */
    public function issuePurchaseOrder($purchaseorder_id)
    {
        return $this->prepareClient()->issuePurchaseOrder($purchaseorder_id);
    }

    /**
     * Mark as cancelled
     * @param string $purchaseorder_id
     * @return \stdClass
     * @see https://www.zoho.com/inventory/api/v1/#mark-as-cancelled
     * @throws Inventory\Exception\ClientException
     */
    public function cancelPurchaseOrder($purchaseorder_id)
    {
        return $this->prepareClient()->cancelPurchaseOrder($purchaseorder_id);
    }

    //************************************** Sales Order *********************************************

    /**
     * Create a sales order
     * @param array $params
     * @param bool $ignore ignore auto number generation, if true you must specify the new id, default is false
     * @return \stdClass
     * @see https://www.zoho.com/inventory/api/v1/#create-a-sales-order
     * @throws Inventory\Exception\ClientException
     */
    public function createSalesOrder(array $params = [], $ignore = false)
    {
        return $this->prepareClient()->createSalesOrder($params, $ignore);
    }

     
    public function createSalesOrderProducts(array $params = [], $ignore = false)
    {
        return $this->prepareClient()->createSalesOrderProducts($params, $ignore);
    }


    /**
     * Update details of an existing sales order
     * @param string $salesorder_id to update
     * @param array $params
     * @param bool $ignore ignore auto number generation, if true you must specify the new id, default is false
     * @return \stdClass
     * @throws Inventory\Exception\ClientException
     */
    public function updateSalesOrder($salesorder_id, array $params, $ignore = false)
    {
        return $this->prepareClient()->updateSalesOrder($salesorder_id, $params, $ignore);
    }

    /**
     * Retrieve a sales order
     * @param string $salesorder_id to retrieve, leave empty to list all purchase orders
     * @return \stdClass
     * @see https://www.zoho.com/inventory/api/v1/#retrieve-a-sales-order
     * @throws Inventory\Exception\ClientException
     */
    public function retrieveSalesOrder($salesorder_id = null)
    {
        return $this->prepareClient()->retrieveSalesOrder($salesorder_id);
    }

    /**
     * List all sales order
     * @return \stdClass
     * @see https://www.zoho.com/inventory/api/v1/#list-all-sales-order
     * @throws Inventory\Exception\ClientException
     */
  
    public function listSalesOrders(array $filters = [])
    {
        return $this->prepareClient()->listSalesOrders($filters);
    }
    public function readSalesOrders(array $ids=[] , array $fields = [])
    {
        return $this->prepareClient()->readSalesOrders($ids,$fields);
    }

      public function readSalesOrdersProducts(array $ids=[] , array $fields = [])
    {
        return $this->prepareClient()->readSalesOrdersProducts($ids,$fields);
    }

    /**
     * Delete an sales order
     * @param string $salesorder_id to be deleted
     * @return \stdClass
     * @throws Inventory\Exception\ClientException
     */
    public function deleteSalesOrder($salesorder_id)
    {
        return $this->prepareClient()->deleteSalesOrder($salesorder_id);
    }

    /**
     * Mark as void
     * @param string $salesorder_id
     * @return \stdClass
     * @throws Inventory\Exception\ClientException
     */
    public function voidSalesOrder($salesorder_id)
    {
        return $this->prepareClient()->voidSalesOrder($salesorder_id);
    }

    /**
     * Mark as confirmed
     * @param string $salesorder_id
     * @return \stdClass
     * @throws Inventory\Exception\ClientException
     */
    public function confirmSalesOrder($salesorder_id)
    {
        return $this->prepareClient()->confirmSalesOrder($salesorder_id);
    }

    //************************************** Invoices *********************************************

    /**
     * create invoice
     * @param array $params
     * @param bool $ignore ignore auto number generation, if true you must specify the new id, default is false
     * @return bool|mixed|string
     * @throws Inventory\Exception\ClientException
     */
    public function createInvoice(array $params = [], $ignore = false)
    {
        return $this->prepareClient()->createInvoice($params, $ignore);
    }

    /**
     * update Invoice
     * @param $invoice_id
     * @param array $params
     * @param bool $ignore ignore auto number generation, if true you must specify the new id, default is false
     * @return bool|mixed|string
     * @throws Inventory\Exception\ClientException
     */
    public function updateInvoice($invoice_id, array $params, $ignore = false)
    {
        return $this->prepareClient()->updateInvoice($invoice_id, $params, $ignore);
    }

    /**
     * delete a single invoice
     * @param $invoice_id string
     * @return bool|mixed|string
     * @throws Inventory\Exception\ClientException
     */
    public function deleteInvoice($invoice_id)
    {
        return $this->prepareClient()->deleteInvoice($invoice_id);
    }

    /**
     * mark invoice as a void
     * @param $invoice_id string 
     * @return bool|mixed|string
     * @throws Inventory\Exception\ClientException
     */
    public function voidInvoice($invoice_id)
    {
        return $this->prepareClient()->voidInvoice($invoice_id);
    }

    /**
     * retrieve a single invoice
     * @param null $invoice_id
     * @return bool|mixed|string
     * @throws Inventory\Exception\ClientException
     */
    public function retrieveInvoice($invoice_id = null)
    {
        return $this->prepareClient()->retrieveInvoice($invoice_id);
    }

    /**
     * list all Invoice
     * @return bool|mixed|string
     * @throws Inventory\Exception\ClientException
     */
    public function listInvoices()
    {
        return $this->prepareClient()->listInvoices();
    }

    //************************************** Contacts *********************************************

    /**
     * @param array $params
     * @return \stdClass
     * @throws Inventory\Exception\ClientException
     */
    public function createContact(array $params = [])
    {
        return $this->prepareClient()->createContact($params);
    }

    /**
     * @param string $contact_id contact id to update
     * @param array $params
     * @return \stdClass
     * @throws Inventory\Exception\ClientException
     */
    public function updateContact($contact_id, array $params = [])
    {
        return $this->prepareClient()->updateContact($contact_id, $params);
    }

    /**
     * @param string $contact_id leave empty to list all contacts
     * @param array $filters filters array, use constants STATUS_.. to filter by contact type
     * @return \stdClass
     * @throws Inventory\Exception\ClientException
     */
    public function retrieveContact($contact_id = null, array $filters = [])
    {
        return $this->prepareClient()->retrieveContact($contact_id, $filters);
    }

    /**
     * @param array $filters filters array, use constants STATUS_.. to filter by contact type
     * @return \stdClass
     * @throws Inventory\Exception\ClientException
     */
    public function listContacts(array $filters = [])
    {
        return $this->prepareClient()->listContacts($filters);
    }
    public function readContacts(array $ids=[] , array $fields = [])
    {
        return $this->prepareClient()->readContacts($ids,$fields);
    }

    /**
     * @param string $contact_id contact id to delete
     * @return \stdClass
     * @throws Inventory\Exception\ClientException
     */
    public function deleteContact($contact_id)
    {
        return $this->prepareClient()->deleteContact($contact_id);
    }

    /**
     * @param string $contact_id contact id to active
     * @return \stdClass
     * @throws Inventory\Exception\ClientException
     */
    public function activeContact($contact_id)
    {
        return $this->prepareClient()->activeContact($contact_id);
    }

    /**
     * @param string $contact_id contact id to inactive
     * @return \stdClass
     * @throws Inventory\Exception\ClientException
     */
    public function inactiveContact($contact_id)
    {
        return $this->prepareClient()->inactiveContact($contact_id);
    }

    //************************************** Currencies *********************************************

    public function listCurrencies(array $filters = [])
    {
        return $this->prepareClient()->listCurrencies($filters);
    }

    private function prepareClient(): Client
    {
        return $this->getClient()->setConfig($this->getConfig());
    }
    public function validateAuth()
    {
        return $this->prepareClient()->validateAuth();
    }

}
