<?php

use ExpandCart\Foundation\Inventory\Inventory;
use ExpandCart\Foundation\Providers\Extension;
use ExpandCart\Foundation\Inventory\Clients\Odoo;

class ModelModuleOdooProducts extends Model
{
    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->module = $this->load->model("module/odoo/settings", ['return' => true]);
    }
    /**
     * @param $product_id
     * @param $data
     * @return false|stdClass
     */
    public function createProduct($product_id, $data)
    {
        if (!$this->module->isActive()) return false; 
        
        $result = $this->module->getInventory()->createItem($data);

        if ($result->status === true) {
            $this->linkProduct($product_id, $result->result, true);
        }
    
        return $result;
    }


    public function updateProduct(int $product_id, array $data)
    {
        if (!$this->module->isActive()) return false;

        $link = $this->selectLinkProduct($product_id);
        if ($link) {
            $result = $this->module->getInventory()->updateItem((int)$link['odoo_product_id'], $data);

            return $result;
        }

        // save new product to inventory
        return $this->createProduct($product_id, $data);
    }

  
    public function deleteProduct($product_id, $org_id)
    {
        if (!$this->module->isActive()) return false;

        $link = $this->selectLinkProduct($product_id);

        if ($link) {
            $this->unLinkProduct($product_id, $link['odoo_product_id']);
            $this->module->getInventory()->deleteItem($link['odoo_product_id'], '');
        }
    }

    public function changeProductStatus(int $product_id, int $status)
    {
        if (!$this->module->isActive()) return false;

        $link = $this->selectLinkProduct($product_id);

        if ($link) {
            if ((int)$status == 1) {
                $this->module->getInventory()->activeItem($link['odoo_product_id']);
            } else {
                $this->module->getInventory()->inactiveItem($link['odoo_product_id']);
            }
        }
    }

    public function selectLinkProduct($product_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "odoo_products WHERE product_id = '" . (int)$product_id . "'");
        return $query->num_rows ? $query->row : null;
    }

    public function linkProduct($product_id, $odoo_product_id, $without_check = false)
    {
        $link = $without_check ? false : $this->selectLinkProduct($product_id);

        if (!$link) {
            $this->db->query("INSERT INTO  " . DB_PREFIX . "odoo_products SET product_id = '" . (int)$product_id . "', odoo_product_id = '" . $odoo_product_id . "'");
        }
    }

    private function unLinkProduct($product_id, $odoo_product_id)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "odoo_products WHERE product_id = '" . (int)$product_id . "' AND odoo_product_id = '" . $odoo_product_id . "'");
    }
}
