<?php

namespace ExpandCart\Foundation\Inventory\Clients;

use ExpandCart\Foundation\Inventory\Exception\ClientException;


class Zoho extends Client
{

    const STATUS_ALL = 'Status.All';
    const STATUS_ACTIVE_CUSTOMERS = 'Status.ActiveCustomers';
    const STATUS_ACTIVE_VENDORS = 'Status.ActiveVendors';
    const STATUS_INACTIVE_CUSTOMERS = 'Status.InactiveCustomers';
    const STATUS_INACTIVE_VENDORS = 'Status.InactiveVendors';
    const STATUS_CRM = 'Status.Crm';
    const STATUS_INACTIVE = 'Status.Inactive';
    const STATUS_ACTIVE = 'Status.Active';

    protected $baseUri = 'https://inventory.zoho.com';

    //************************************** Item *********************************************

    /**
     * @param array $params array of properties for the new item
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     * @see https://www.zoho.com/inventory/api/v1/#create-a-item
     */
    public function createItem($params)
    {
        return $this->makeRequest('POST', '/api/v1/items', [], ['JSONString' => json_encode($params)]);
    }

    /**
     * @param string $item_id item to update
     * @param array $params array of new properties
     * @return \stdClass
     * @see https://www.zoho.com/inventory/api/v1/#update-an-item
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function updateItem($item_id, $params)
    {
        return $this->makeRequest('PUT', "/api/v1/items/{$item_id}", [], ['JSONString' => json_encode($params)]);
    }

    /**
     * @param string $item_id to retrieve, leave empty to list all items
     * @return \stdClass
     * @see https://www.zoho.com/inventory/api/v1/#retrieve-a-item
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function retrieveItem(string $item_id = null)
    {
        return $this->makeRequest('GET', "/api/v1/items/{$item_id}");
    }

    /**
     * List all items
     * @param array $filters an extra option used in zoho web application allows you to filter items by specific fields, leave empty to list all items<br>
     * some of available filters :<br>
     * search_text : to search about a part of text inside the item page<br>
     * filter_by   : ItemType.Sales , Status.Unmapped , Status.Active, Status.Lowstock, ItemType.Purchases, ItemType.Inventory, ItemType.NonInventory, ItemType.Service<br>
     * Pagenation arguments : page, per_page,sort_column(column name), sort_order(A/D)<br>
     * ( exmple : to filter by upc field just add to the list of parameters with the value you want to filter, you may use your custom fields also )
     * @return \stdClass
     * @see https://www.zoho.com/inventory/api/v1/#list-all-item
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function listItems(array $filters = [])
    {
        return $this->makeRequest('GET', "/api/v1/items/", $filters);
    }
    public function readItems(array $ids=[] ,array $fields = [])
    {
    
    }
 
    /**
     * an extra function allows you to search items by text portion
     * @param string $search_text
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function searchItem($search_text)
    {
        return $this->listItems(['search_text' => $search_text]);
    }

    /**
     * Deletes an item
     * @param string $item_id item to delete
     * @return \stdClass
     * @see https://www.zoho.com/inventory/api/v1/#delete-an-item
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function deleteItem($item_id, $org_id)
    {
        return $this->makeRequest('DELETE', "/api/v1/items/{$item_id}?organization_id={$org_id}");
    }

    /**
     * Change status of the item to <strong>active</strong>
     * @param string $item_id
     * @return \stdClass
     * @see https://www.zoho.com/inventory/api/v1/#mark-as-active31
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function activeItem($item_id)
    {
        return $this->makeRequest('POST', "/api/v1/items/{$item_id}/active");
    }

    /**
     * Change status of the item to <strong>inactive</strong>
     * @see https://www.zoho.com/inventory/api/v1/#mark-as-inactive32
     * @param string $item_id
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function inactiveItem($item_id)
    {
        return $this->makeRequest('POST', "/api/v1/items/{$item_id}/inactive");
    }


    /**
     * Change status of the item to <strong>inactive</strong>
     * @see https://www.zoho.com/inventory/api/v1/#update-item-image
     * @param string $item_id
     * @param string $path
     * @return \stdClass
     */
    public function updateItemImage($item_id, $path)
    {
        $formParams = [
            [
                'name'     => 'image',
                'contents' => file_get_contents($path),
                'filename' => basename($path),
            ],
        ];

        if (isset($this->config['organization_id'])) {
            array_push($formParams, [
                'name'     => 'organization_id',
                'contents' => $this->config['organization_id']
            ]);
        }

        return $this->makeRequest('POST', "/api/v1/items/{$item_id}/images", [], $formParams, [], false, true);
    }

    /**
     * Change status of the item to <strong>inactive</strong>
     * @see https://www.zoho.com/inventory/api/v1/#update-item-image
     * @param string $item_id
     * @return string
     */
    public function getImageUrl($item_id)
    {
        return $this->baseUri . "/api/v1/items/" . $item_id . '/image?authtoken=' . $this->getAuthParams()['authtoken'];
    }

    //************************************** Purchase Order *********************************************

    /**
     * Create a purchase order
     * @param array $params
     * @param bool $ignore ignore auto number generation, if true you must specify the new id, default is false
     * @return \stdClass
     * @see https://www.zoho.com/inventory/api/v1/#create-a-purchase-order
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function createPurchaseOrder(array $params, $ignore = false)
    {
        return $this->makeRequest(
            'POST',
            '/api/v1/purchaseorders',
            ['ignore_auto_number_generation' => $ignore ? 'true' : 'false'],
            ['JSONString' => json_encode($params)]
        );
    }

    /**
     * Update details of an existing purchase order
     * @param string $purchaseorder_id to update
     * @param array $params
     * @param bool $ignore ignore auto number generation, if true you must specify the new id, default is false
     * @return \stdClass
     * @see https://www.zoho.com/inventory/api/v1/#update-an-purchase-order
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function updatePurchaseOrder($purchaseorder_id, array $params, $ignore = false)
    {
        return $this->makeRequest(
            'PUT',
            "/api/v1/purchaseorders/{$purchaseorder_id}",
            ['ignore_auto_number_generation' => $ignore ? 'true' : 'false'],
            ['JSONString' => json_encode($params)]
        );
    }

    /**
     * Retrieve a purchase order
     * @param string $purchaseorder_id to retrieve, leave empty to list all purchase orders
     * @return \stdClass
     * @see https://www.zoho.com/inventory/api/v1/#retrieve-a-purchase-order
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function retrievePurchaseOrder($purchaseorder_id = null)
    {
        return $this->makeRequest("GET", "/api/v1/purchaseorders/{$purchaseorder_id}");
    }

    /**
     * List all purchase order
     * @return \stdClass
     * @see https://www.zoho.com/inventory/api/v1/#retrieve-a-purchase-order
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function listPurchaseOrders()
    {
        return $this->retrievePurchaseOrder();
    }

    /**
     * Delete an purchase order
     * @param string $purchaseorder_id to be deleted
     * @return \stdClass
     * @see https://www.zoho.com/inventory/api/v1/#delete-an-purchase-order
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function deletePurchaseOrder($purchaseorder_id)
    {
        return $this->makeRequest("DELETE", "/api/v1/purchaseorders/{$purchaseorder_id}");
    }

    /**
     * Mark as issued
     * @param string $purchaseorder_id
     * @return \stdClass
     * @see https://www.zoho.com/inventory/api/v1/#mark-as-issued
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function issuePurchaseOrder($purchaseorder_id)
    {
        return $this->makeRequest("POST", "/api/v1/purchaseorders/{$purchaseorder_id}/status/issued");
    }

    /**
     * Mark as cancelled
     * @param string $purchaseorder_id
     * @return \stdClass
     * @see https://www.zoho.com/inventory/api/v1/#mark-as-cancelled
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function cancelPurchaseOrder($purchaseorder_id)
    {
        return $this->makeRequest("POST", "/api/v1/purchaseorders/{$purchaseorder_id}/status/cancelled");
    }

    //************************************** Sales Order *********************************************

    /**
     * Create a sales order
     * @param array $params
     * @param bool $ignore ignore auto number generation, if true you must specify the new id, default is false
     * @return \stdClass
     * @see https://www.zoho.com/inventory/api/v1/#create-a-sales-order
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function createSalesOrder(array $params = [], $ignore = false)
    {
        return $this->makeRequest(
            'POST',
            '/api/v1/salesorders',
            ['ignore_auto_number_generation' => $ignore ? 'true' : 'false'],
            ['JSONString' => json_encode($params)]
        );
    }
    public function createSalesOrderProducts(array $params = [], $ignore = false)
    {
      
    }

    /**
     * Update details of an existing sales order
     * @param string $salesorder_id to update
     * @param array $params
     * @param bool $ignore ignore auto number generation, if true you must specify the new id, default is false
     * @return \stdClass
     * @see https://www.zoho.com/inventory/api/v1/#update-an-sales-order
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function updateSalesOrder($salesorder_id, array $params, $ignore = false)
    {
        return $this->makeRequest(
            'PUT',
            "/api/v1/salesorders/{$salesorder_id}",
            ['ignore_auto_number_generation' => $ignore ? 'true' : 'false'],
            ['JSONString' => json_encode($params)]
        );
    }

    /**
     * Retrieve a sales order
     * @param string $salesorder_id to retrieve, leave empty to list all purchase orders
     * @return \stdClass
     * @see https://www.zoho.com/inventory/api/v1/#retrieve-a-sales-order
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function retrieveSalesOrder($salesorder_id = null)
    {
        return $this->makeRequest("GET", "/api/v1/salesorders/{$salesorder_id}");
    }

    /**
     * List all sales order
     * @return \stdClass
     * @see https://www.zoho.com/inventory/api/v1/#list-all-sales-order
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function listSalesOrders(array $filters = [])
    {
        return $this->retrieveSalesOrder();
    }
    public function readSalesOrders(array $ids=[],array $fields = [])
    { 
     
    }
    public function readSalesOrdersProducts(array $ids=[],array $fields = [])
    { 
       
    }
  

    /**
     * Delete an sales order
     * @param string $salesorder_id to be deleted
     * @return \stdClass
     * @see https://www.zoho.com/inventory/api/v1/#delete-an-sales-order
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function deleteSalesOrder($salesorder_id)
    {
        return $this->makeRequest("DELETE", "/api/v1/salesorders/{$salesorder_id}");
    }

    /**
     * Mark as void
     * @param string $salesorder_id
     * @return \stdClass
     * @see https://www.zoho.com/inventory/api/v1/#mark-as-void68
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function voidSalesOrder($salesorder_id)
    {
        return $this->makeRequest("POST", "/api/v1/salesorders/{$salesorder_id}/status/void");
    }

    /**
     * Mark as confirmed
     * @param string $salesorder_id
     * @return \stdClass
     * @see https://www.zoho.com/inventory/api/v1/#Sales_Orders_Mark_as_Confirmed
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function confirmSalesOrder($salesorder_id)
    {
        return $this->makeRequest("POST", "/api/v1/salesorders/{$salesorder_id}/status/confirmed");
    }

    //************************************** Invoices *********************************************

    /**
     * create invoice
     * @param array $params
     * @param bool $ignore ignore auto number generation, if true you must specify the new id, default is false
     * @return bool|mixed|string
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function createInvoice(array $params = [], $ignore = false)
    {
        return $this->makeRequest(
            'POST',
            '/api/v1/invoices',
            ['ignore_auto_number_generation' => $ignore ? 'true' : 'false', 'send' => 'true'],
            ['JSONString' => json_encode($params)]
        );
    }

    /**
     * update Invoice
     * @param $invoice_id
     * @param array $params
     * @param bool $ignore ignore auto number generation, if true you must specify the new id, default is false
     * @return bool|mixed|string
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function updateInvoice($invoice_id, array $params, $ignore = false)
    {
        return $this->makeRequest(
            'PUT',
            "/api/v1/invoices/{$invoice_id}",
            ['ignore_auto_number_generation' => $ignore ? 'true' : 'false'],
            ['JSONString' => json_encode($params)]
        );
    }

    /**
     * retrieve a single invoice
     * @param null $invoice_id
     * @return bool|mixed|string
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function retrieveInvoice($invoice_id = null)
    {
        return $this->makeRequest("GET", "/api/v1/invoices/{$invoice_id}");
    }

    /**
     * @param string $invoice_id contact id to delete
     * @return \stdClass
     * @see https://www.zoho.com/inventory/api/v1/#Invoices_Delete_an_invoice
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function deleteInvoice($invoice_id)
    {
        return $this->makeRequest("DELETE", "/api/v1/invoices/{$invoice_id}");
    }

    /**
     * Mark as void
     * @param string $invoice_id
     * @return \stdClass
     * @see https://www.zoho.com/inventory/api/v1/#Invoices_Void_an_invoice
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function voidInvoice($invoice_id)
    {
        return $this->makeRequest("POST", "/api/v1/invoices/{$invoice_id}/status/void");
    }

    /**
     * list all Invoice
     * @return bool|mixed|string
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function listInvoices()
    {
        return $this->retrieveInvoice();
    }

    //************************************** Contacts *********************************************

    /**
     * @param array $params
     * @return \stdClass
     * @see https://www.zoho.com/inventory/api/v1/#create-a-contact
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function createContact(array $params = [])
    {
        return $this->makeRequest("POST", "/api/v1/contacts", [], ['JSONString' => json_encode($params)]);
    }

    /**
     * @param string $contact_id contact id to update
     * @param array $params
     * @return \stdClass
     * @see https://www.zoho.com/inventory/api/v1/#update-an-contact
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function updateContact($contact_id, array $params = [])
    {
        return $this->makeRequest("PUT", "/api/v1/contacts/{$contact_id}", [], ['JSONString' => json_encode($params)]);
    }

    /**
     * @param string $contact_id leave empty to list all contacts
     * @param array $filters filters array, use constants STATUS_.. to filter by contact type
     * @return \stdClass
     * @see https://www.zoho.com/inventory/api/v1/#retrieve-a-contact
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function retrieveContact($contact_id = null, array $filters = [])
    {
        return $this->makeRequest("GET", "/api/v1/contacts/{$contact_id}", $filters);
    }

    /**
     * @param array $filters filters array, use constants STATUS_.. to filter by contact type
     * @return \stdClass
     * @see https://www.zoho.com/inventory/api/v1/#list-all-contact
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function listContacts(array $filters = [])
    {
        return $this->makeRequest("GET", "/api/v1/contacts", $filters);
    }
    public function readContacts(array $ids=[],array $fields = [])
    { 
      
    }

    /**
     * @param string $contact_id contact id to delete
     * @return \stdClass
     * @see https://www.zoho.com/inventory/api/v1/#delete-an-contact
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function deleteContact($contact_id)
    {
        return $this->makeRequest("DELETE", "/api/v1/contacts/{$contact_id}");
    }

    /**
     * @param string $contact_id contact id to active
     * @return \stdClass
     * @see https://www.zoho.com/inventory/api/v1/#mark-as-active
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function activeContact($contact_id)
    {
        return $this->makeRequest("POST", "/api/v1/contacts/{$contact_id}/active");
    }

    /**
     * @param string $contact_id contact id to inactive
     * @return \stdClass
     * @see https://www.zoho.com/inventory/api/v1/#mark-as-inactive
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function inactiveContact($contact_id)
    {
        return $this->makeRequest("POST", "/api/v1/contacts/{$contact_id}/inactive");
    }

    //************************************** Currency *********************************************
    /**
     * List all Currencies
     * @param array $filters an extra option used in zoho web application allows you to filter Currencies by specific fields, leave empty to list all Currencies<br>
     * some of available filters :<br>
     * search_text : to search about a part of text inside the item page<br>
     * filter_by   : Status.Active<br>
     * Pagenation arguments : page, per_page,sort_column(column name), sort_order(A/D)<br>
     * ( exmple : to filter by upc field just add to the list of parameters with the value you want to filter, you may use your custom fields also )
     * @return \stdClass
     * @see https://www.zoho.com/inventory/api/v1/#Currency_List_Currency
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function listCurrencies(array $filters = [])
    {
        return $this->makeRequest('GET', "/api/v1/settings/currencies", $filters);
    }

    //************************************** Others *********************************************

    /**
     * configuration validately
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     * @return boolean
     */
    public function validateConfiguration()
    {
        if (
            empty($this->config['client_id'])
            || empty($this->config['client_secret'])
            || !isset($this->config['token']['access_token'])
        ) {
            throw new ClientException('You have to set valid \'client_id\' and \'client_secret\' to use zoho client');
        }

        return true;
    }

    /**
     * Retrieves the array of authentication configs
     * @return array
     */
    protected function getAuthParams()
    {
        return [
            'Authorization' => "Zoho-oauthtoken " . $this->config['token']['access_token'],
        ];
    }

    protected function resolveAuthorization(&$queryParams, &$formParams, &$headers)
    {
        $queryParams = array_merge($queryParams, (isset($this->config['organization_id']) ? ['organization_id' => $this->config['organization_id']] : []));
        $headers = array_merge($headers, $this->getAuthParams());
    }

    protected function decodeResponse($response)
    {
        $response = parent::decodeResponse($response);

        if ($response) {
            // success result
            if ((int)$response->code === 0) {
                $response->status = true;
            } else {
                // fail result
                $response->status = false;
            }

            unset($response->code);
        } else {
            $response->status = false;
        }

        return $response;
    }
    public function validateAuth(){}
}
