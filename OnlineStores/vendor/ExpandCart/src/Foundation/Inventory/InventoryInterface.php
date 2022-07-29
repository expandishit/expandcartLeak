<?php

namespace ExpandCart\Foundation\Inventory;

use ExpandCart\Foundation\Inventory\Inventory;
use ExpandCart\Foundation\Inventory\Clients\Client;



interface InventoryInterface
{
    //************************************** Config *********************************************

    public function setConfig(array $config): Inventory;

    public function getConfig();

    //************************************** Client *********************************************

    public function setClient(Client $client): Inventory;

    public function getClient(): Client;

    //************************************** Item *********************************************

    public function createItem(array $item);

    public function updateItem(string $item_id, array $item);

    public function retrieveItem(string $item_id);

    public function listItems(array $filters = []);

    public function searchItem($search_text);

    public function deleteItem($item_id,$org_id);

    public function activeItem($item_id);

    public function inactiveItem($item_id);

    public function updateItemImage($item_id,$params);

    public function getImageUrl($item_id);

    //************************************** Purchase Order *********************************************

    public function createPurchaseOrder(array $params, $ignore = false);

    public function updatePurchaseOrder($purchaseorder_id, array $params, $ignore = false);

    public function retrievePurchaseOrder($purchaseorder_id = null);

    public function listPurchaseOrders();

    public function deletePurchaseOrder($purchaseorder_id);

    public function issuePurchaseOrder($purchaseorder_id);

    public function cancelPurchaseOrder($purchaseorder_id);

    //************************************** Sales Order *********************************************

    public function createSalesOrder(array $params = [], $ignore = false);

    public function updateSalesOrder($salesorder_id, array $params, $ignore = false);

    public function retrieveSalesOrder($salesorder_id = null);

    public function listSalesOrders();

    public function deleteSalesOrder($salesorder_id);

    public function voidSalesOrder($salesorder_id);

    public function confirmSalesOrder($salesorder_id);

    //************************************** Invoices *********************************************

    public function createInvoice(array $params = [], $ignore = false);

    public function updateInvoice($invoice_id, array $params, $ignore = false);

    public function retrieveInvoice($invoice_id = null);

    public function deleteInvoice($invoice_id);

    public function voidInvoice($invoice_id);

    public function listInvoices();

    //************************************** Contacts *********************************************

    public function createContact(array $params = []);

    public function updateContact($contact_id, array $params = []);

    public function retrieveContact($contact_id = null, array $filters = []);

    public function listContacts(array $filters = []);

    public function deleteContact($contact_id);

    public function activeContact($contact_id);

    public function inactiveContact($contact_id);

    //************************************** Currencies *********************************************

    public function listCurrencies(array $filters = []);
}
