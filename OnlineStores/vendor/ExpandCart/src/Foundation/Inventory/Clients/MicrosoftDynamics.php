<?php

namespace ExpandCart\Foundation\Inventory\Clients;

use ExpandCart\Foundation\Inventory\Exception\ClientException;
use stdClass;

class MicrosoftDynamics extends Client
{

    protected $baseUri;

    protected $httpVerifySslCertificate = false;

    protected $authToken;

    //************************************** Item *********************************************

    /**
     * @param array $params array of properties for the new item
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function createItem($params)
    {
    }

    /**
     * @param string $item_id item to update
     * @param array $params array of new properties
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function updateItem($item_id, $params)
    {
    }

    /**
     * @param string $item_id to retrieve, leave empty to list all items
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function retrieveItem(string $item_id = null)
    {
        return $this->makeRequest("GET", "/apiProd/products/GetById/{$item_id}");
    }

    /**
     * List all items
     * @param array $filters an extra option used in zoho web application allows you to filter items by specific fields, leave empty to list all items
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function listItems(array $filters = [])
    {
        return $this->makeRequest('GET', "/api/products/" . $filters['per_page'] . '/' . $filters['page'] . '/' . $filters['modified_date']);
    }
    public function readItems(array $ids=[], array $fields = [])
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
    }

    /**
     * Deletes an item
     * @param string $item_id item to delete
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function deleteItem($item_id,$org_id='')
    {
    }

    /**
     * Change status of the item to <strong>active</strong>
     * @param string $item_id
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function activeItem($item_id)
    {
    }

    /**
     * Change status of the item to <strong>inactive</strong>
     * @param string $item_id
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function inactiveItem($item_id)
    {
    }

    //************************************** Purchase Order *********************************************

    /**
     * Create a purchase order
     * @param array $params
     * @param bool $ignore ignore auto number generation, if true you must specify the new id, default is false
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function createPurchaseOrder(array $params, $ignore = false)
    {
    }

    /**
     * Update details of an existing purchase order
     * @param string $purchaseorder_id to update
     * @param array $params
     * @param bool $ignore ignore auto number generation, if true you must specify the new id, default is false
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function updatePurchaseOrder($purchaseorder_id, array $params, $ignore = false)
    {
    }

    /**
     * Retrieve a purchase order
     * @param string $purchaseorder_id to retrieve, leave empty to list all purchase orders
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function retrievePurchaseOrder($purchaseorder_id = null)
    {
    }

    /**
     * List all purchase order
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function listPurchaseOrders()
    {
    }

    /**
     * Delete an purchase order
     * @param string $purchaseorder_id to be deleted
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function deletePurchaseOrder($purchaseorder_id)
    {
    }

    /**
     * Mark as issued
     * @param string $purchaseorder_id
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function issuePurchaseOrder($purchaseorder_id)
    {
    }

    /**
     * Mark as cancelled
     * @param string $purchaseorder_id
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function cancelPurchaseOrder($purchaseorder_id)
    {
    }

    //************************************** Sales Order *********************************************

    /**
     * Create a sales order
     * @param array $params
     * @param bool $ignore ignore auto number generation, if true you must specify the new id, default is false
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function createSalesOrder(array $params = [], $ignore = false)
    {
        return $this->makeRequest("PUT", "/api/salesorder", [], $params);
    }

    /**
     * Update details of an existing sales order
     * @param string $salesorder_id to update
     * @param array $params
     * @param bool $ignore ignore auto number generation, if true you must specify the new id, default is false
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function updateSalesOrder($salesorder_id, array $params, $ignore = false)
    {
    }

    /**
     * Retrieve a sales order
     * @param string $salesorder_id to retrieve, leave empty to list all purchase orders
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function retrieveSalesOrder($salesorder_id = null)
    {
    }

    /**
     * List all sales order
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function listSalesOrders(array $filters = [])
    {
    }
    public function createSalesOrderProducts(array $params = [], $ignore = false)
    {

    }
    public function readSalesOrders(array $ids=[], array $fields = [])
    {

    }
    public function readSalesOrdersProducts(array $ids=[], array $fields = [])
    {

    }



    /**
     * Delete an sales order
     * @param array $salesorder to be deleted
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function deleteSalesOrder($salesorder)
    {
        return $this->makeRequest("POST", "/api/ReturnOrder", [], $salesorder);
    }

    /**
     * Mark as void
     * @param string $salesorder_id
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function voidSalesOrder($salesorder_id)
    {
    }

    /**
     * Mark as confirmed
     * @param string $salesorder_id
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function confirmSalesOrder($salesorder_id)
    {
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
    }

    /**
     * retrieve a single invoice
     * @param null $invoice_id
     * @return bool|mixed|string
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function retrieveInvoice($invoice_id = null)
    {
    }

    /**
     * @param string $invoice_id contact id to delete
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function deleteInvoice($invoice_id)
    {
    }

    /**
     * Mark as void
     * @param string $invoice_id
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function voidInvoice($invoice_id)
    {
    }

    /**
     * list all Invoice
     * @return bool|mixed|string
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function listInvoices()
    {
    }

    //************************************** Contacts *********************************************

    /**
     * @param array $params
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function createContact(array $params = [])
    {
        return $this->makeRequest("PUT", "/api/customer", [], $params);
    }

    /**
     * @param string $contact_id contact id to update
     * @param array $params
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function updateContact($contact_id, array $params = [])
    {
    }

    /**
     * @param string $contact_id the phone number 
     * @param array $filters filters array, use constants STATUS_.. to filter by contact type
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function retrieveContact($contact_id = null, array $filters = [])
    {
        return $this->makeRequest("GET", "/apiCust/customer/GetCustByPhone/{$contact_id}");
    }

    /**
     * @param array $filters filters array, use constants STATUS_.. to filter by contact type
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function listContacts(array $filters = [])
    {
    }
    public function readContacts(array $ids=[], array $fields = [])
    {
        
    }

    /**
     * @param string $contact_id contact id to delete
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function deleteContact($contact_id)
    {
    }

    /**
     * @param string $contact_id contact id to active
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function activeContact($contact_id)
    {
    }

    /**
     * @param string $contact_id contact id to inactive
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function inactiveContact($contact_id)
    {
    }

    //************************************** Currency *********************************************
    /**
     * List all Currencies
     * @param array $filters an extra option used in zoho web application allows you to filter Currencies by specific fields, leave empty to list all Currencies
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function listCurrencies(array $filters = [])
    {
    }

    //************************************** Others *********************************************

    /**
     * configuration validately
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     * @return boolean
     */
    public function validateConfiguration()
    {
        if (!isset($this->config['username'])) {
            throw new ClientException('You have to set config \'username\' to use microSoft-dynamics client');
        }

        if (!isset($this->config['password'])) {
            throw new ClientException('You have to set config \'password\' to use microSoft-dynamics client');
        }

        if (!isset($this->config['server_base_uri'])) {
            throw new ClientException('You have to set config \'server_base_uri\' to use microSoft-dynamics client');
        }

        // set base uri on fly from config
        $this->baseUri = $this->config['server_base_uri'];

        return true;
    }

    /**
     * Retrieves the array of authentication configs
     * @return array
     */
    protected function getAuthParams()
    {
        if (isset($this->config['authtoken']))
            return ['authorization' => 'Bearer ' . $this->config['authtoken']];
        return [];
    }

    protected function resolveAuthorization(&$queryParams, &$formParams, &$headers)
    {
        $headers = array_merge($headers, $this->getAuthParams());
    }

    protected function decodeResponse($response)
    {
        $response = parent::decodeResponse($response);
        if (gettype($response) !== "object" && gettype($response) !== "array") {
            $result = $response;
            $response = new stdClass();
            $response->result  = $result;
        }

        if (
            $response &&
            !property_exists($response, 'error') &&
            (!property_exists($response, "Status Code") || $response->{"Status Code"} != 404)
        ) {
            $response->status = true;
        } else {
            $response->status = false;
        }

        return $response;
    }

    protected function makeRequest($method, $requestUrl, $queryParams = [], $formParams = [], $headers = [], $isJsonRequest = false, $isMultipart = false)
    {
        // Fetch token on the fly based on username & password 
        if (!isset($this->config['authtoken'])) {
            $tokenResult = parent::makeRequest('POST', '/token', [], [
                'username' => $this->config['username'],
                'password' => $this->config['password'],
                'grant_type' => 'password',
            ]);

            // Authentication Failed with microsoft-dynamics client! Please make sure the username and password are correct
            if ($tokenResult->status === false) return $tokenResult;
            $this->config['authtoken'] = $tokenResult->access_token;
        }

        return parent::makeRequest($method, $requestUrl, $queryParams, $formParams, $headers, $isJsonRequest);
    }
    public function validateAuth(){}
}
