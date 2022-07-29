<?php

class ModelModuleProductSort extends Model
{
    public function install()
    {
        if(!$this->db->check(['product' => ['manual_sort']], 'column')){
            $this->db->query("ALTER TABLE " . DB_PREFIX . "product ADD manual_sort INT DEFAULT NULL");
        }

        $this->db->query("SET @row_number = -1");
        $this->db->query("UPDATE " . DB_PREFIX . "product SET manual_sort= (@row_number:=@row_number + 1)");

        if (!$this->db->check(['product_to_categories_sorting'], 'table')){
            $this->db->query("CREATE TABLE " . DB_PREFIX . " product_to_categories_sorting (
            category_id INT(11) NOT NULL,
            product_id INT(11) NOT NULL,
            manual_sort INT(11) DEFAULT NULL
          );");
        }


    }

    public function uninstall()
    {
        if($this->db->check(['product' => ['manual_sort']], 'column')){
            $this->db->query("ALTER TABLE product DROP COLUMN manual_sort");
        }

        if ($this->db->check(['product_to_categories_sorting'], 'table')){
            $this->db->query("DROP TABLE product_to_categories_sorting");
        }
    }


    public function updatePostion($product_id, $position) {
        $this->db->query("UPDATE " . DB_PREFIX . "product SET manual_sort='{$position}' WHERE product_id='{$product_id}'");
    }

    public function updateCategoryProductsPostions($positions, $selected_category_id) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_categories_sorting  WHERE category_id='{$selected_category_id}'");
        $product_categorys_sql_stmt = "INSERT INTO " . DB_PREFIX . "product_to_categories_sorting (category_id, product_id, manual_sort) VALUES  ";
        $i=0;
        foreach ($positions as $position) {
            $product_categorys_sql_stmt .= "(" . $selected_category_id . "," . $position[0] . "," .$i . "),";
            $i++;
        }
        $product_categorys_sql_stmt = rtrim($product_categorys_sql_stmt, ',');
        $product_categorys_sql_stmt .= ";";
        $this->db->query($product_categorys_sql_stmt);
    }

    /**
     * Check if app installed
     *
     * @return boolean
     */
    public function isInstalled(): bool
    {
        return \Extension::isInstalled('product_sort');
    }

}