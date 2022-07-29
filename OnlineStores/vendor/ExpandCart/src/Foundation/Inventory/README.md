# Inventory API

Unofficial Inventory API for PHP, It is a library that enables you to easily manage external inventories with many clients

## Installation 

```bash
composer require mostafaezz/inventory
```

## Usage

```php
require __DIR__ . '/vendor/autoload.php';

use Inventory\Inventory;
use Inventory\Clients\Zoho;

# Firstly, you can set up the inventory in this way, 

$inventory = new Inventory;
$inventory->setClient(new Zoho); // set client required!

// or initialize by one line
$inventory = new Inventory(new Zoho);

// now, you can set client config in these ways
$inventory->setConfig(['authtoken' => '8c228a04f35045d0e54e1eb1eff1b110', 'organization_id' => '720992172']);
// or
$inventory->getClient()->setConfig(['authtoken' => '8c228a04f35045d0e54e1eb1eff1b110', 'organization_id' => '720992172']);

// finally you can set up inventory by typing one line 
$inventory = new Inventory(new Zoho, ['authtoken' => '8c228a04f35045d0e54e1eb1eff1b110', 'organization_id' => '720992172']);
// or
$inventory = new Inventory(new Zoho(['authtoken' => '8c228a04f35045d0e54e1eb1eff1b110', 'organization_id' => '720992172']));

// The API's 

//************************************** Item *********************************************

$result = $inventory->createItem(['name' => 'foo']);
$result = $inventory->updateItem($result->item->item_id, ['name' => 'foo updated']);
$result = $inventory->retrieveItem($result->item->item_id);
$result = $inventory->activeItem($result->item->item_id);
$result = $inventory->inactiveItem($result->item->item_id);
$result = $inventory->deleteItem($result->item->item_id);
$inventory->searchItem('foo');
$results = $inventory->listItems();

//************************************** Purchase Order *********************************************

$inventory->createPurchaseOrder(['name' => 'foo']);
$inventory->updatePurchaseOrder('1', ['name' => 'foo']);
$inventory->retrievePurchaseOrder('1');
$inventory->listPurchaseOrders();
$inventory->deletePurchaseOrder('1');
$inventory->issuePurchaseOrder(1);
$inventory->cancelPurchaseOrder(1);

//************************************** Sales Order *********************************************

$inventory->createSalesOrder(['name' => 'foo'], true);
$inventory->updateSalesOrder('1', ['name' => 'foo']);
$inventory->retrieveSalesOrder('1');
$inventory->listSalesOrders();
$inventory->deleteSalesOrder('1');
$inventory->voidSalesOrder(1);

//************************************** Invoices *********************************************

$inventory->createInvoice(['name' => 'foo']);
$inventory->updateInvoice('1', ['name' => 'foo']);
$inventory->retrieveInvoice('1');
$inventory->listInvoices();

//************************************** Contacts *********************************************

$inventory->createContact(['name' => 'Mostafa Ezz']);
$inventory->updateContact(1, ['name' => 'Mostafa Ezz Eldin']);
$inventory->retrieveContact(1);
$inventory->retrieveContact(1);
$inventory->listContacts();
$inventory->deleteContact(1);
$inventory->activeContact(1);
$inventory->inactiveContact(1);


# Secondly, you can set up the zoho client directly in these ways

$zoho = new Zoho(['authtoken' => '8c228a04f35045d0e54e1eb1eff1b110', 'organization_id' => '720992172']);
// or
$zoho = new Zoho;
// set config on the fly
$zoho->setConfig(['authtoken' => '8c228a04f35045d0e54e1eb1eff1b110', 'organization_id' => '720992172']);

// Now you can call all zoho client API's, ex:
$result = $zoho->createItem(['name' => 'foo']);

```

## Supported Clients
| Client                                                         | Description                                                                                     |
| -------------------------------------------------------------- | ----------------------------------------------------------------------------------------------- |
| [ZOHO](https://www.zoho.com/inventory/api/v1/#getting-started) | The Zoho Inventory API allows you to perform all the operations that you do with our web client |
