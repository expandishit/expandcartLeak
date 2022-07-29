<?php
class ModelModulePrintful extends Model
{

    public function getAPIKey($seller_id)
    {
        return $this->db->query('
            SELECT `api_key` FROM `' . DB_PREFIX . 'printful_seller_key`
            WHERE `seller_id` = ' . (int) $this->db->escape($seller_id))->row['api_key'];
    }

    public function getAPIKeyByStoreId($store_id)
    {
        return $this->db->query('
            SELECT `api_key` FROM `' . DB_PREFIX . 'printful_seller_key`
            WHERE `store_id` = ' . (int) $this->db->escape($store_id))->row['api_key'];
    }

    public function insertAPIKey($seller_id, $key)
    {
        $result = $this->db->query('
            SELECT COUNT(`api_key`) as `seller_key_count` FROM `' . DB_PREFIX . 'printful_seller_key`
            WHERE `seller_id` = ' . (int) $this->db->escape($seller_id))->row['seller_key_count'] ?? 0;

        if ($result > 0) {
            $this->db->query('
                UPDATE `' . DB_PREFIX . 'printful_seller_key`
                SET `api_key` = \'' . $this->db->escape($key) . '\'
                WHERE `seller_id` = ' . (int) $this->db->escape($seller_id));
        } else {
            $this->db->query('
                INSERT INTO `' . DB_PREFIX . 'printful_seller_key` (`seller_id`, `api_key`)
                VALUES (' . (int) $this->db->escape($seller_id) . ', \'' . $this->db->escape($key) . '\')');
        }
    }

    public function insertDefaultCategoryId(int $seller_id, int $category_id): void
    {
        $result = $this->db->query('
            SELECT * from ' . DB_PREFIX . '`setting` 
            WHERE `key` = "seller_printful_default_category_' . $this->db->escape($seller_id) . '"
        ')->num_rows;


        if ($result > 0) {
            $this->db->query('
                UPDATE `' . DB_PREFIX . 'setting`
                SET `value` = ' . $this->db->escape($category_id) . '
                WHERE `key` = "seller_printful_default_category_' . (int) $this->db->escape($seller_id).'"'
            );
        } else {
            $this->db->query('
                INSERT INTO `' . DB_PREFIX . 'setting` (`store_id`, `group`,`key`,`value`,`serialized`)
                VALUES (
                        0,
                        "multiseller_printful",
                        "seller_printful_default_category_' . $this->db->escape($seller_id) . '",
                        ' . $this->db->escape($category_id) . ',
                        0
                    )
            ');
        }
    }

    public function getGetDefaultCategory(int $customer_id): ?int
    {
        $result = $this->db->query('
            SELECT * from ' . DB_PREFIX . '`setting` 
            WHERE `key` = "seller_printful_default_category_' . $this->db->escape($customer_id) . '"
        ');

        if($result->num_rows){
            return $result->row['value'];
        }

        return 0;
    }

    public function updateStoreId($seller_id, $store_id)
    {
        $this->db->query('
            UPDATE `' . DB_PREFIX . 'printful_seller_key`
            SET `store_id` = ' . $this->db->escape($store_id) . '
            WHERE `seller_id` = ' . (int) $this->db->escape($seller_id));
    }

    public function insertProduct($product)
    {
        $query = '';
    }
}
