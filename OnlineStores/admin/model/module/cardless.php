<?php

class ModelModuleCardless extends Model
{
    public function install()
    {

        $query = "CREATE TABLE IF NOT EXISTS `" .DB_PREFIX. "cardless_products` (
            `id` int(10) NOT NULL PRIMARY KEY AUTO_INCREMENT,
            `product_id` int(11) NOT NULL UNIQUE,
            `sku` varchar(255) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $this->db->query($query);

        $query = "CREATE TABLE IF NOT EXISTS `" .DB_PREFIX. "cardless_purchases` (
                  `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                  `transaction_id` varchar(255) NOT NULL,
                  `order_id` int(11) NOT NULL,
                  `product_id` int(11) NOT NULL,
                  `skuname` varchar(255) NULL,
                  `sku` varchar(255) NOT NULL,
                  `price` varchar(255) NULL,
                  `cost` varchar(255) NULL,
                  `serial` varchar(255) NULL,
                  `code` varchar(255) NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $this->db->query($query);

    }

    public function uninstall()
    {
        $query = "DROP TABLE IF EXISTS `" .DB_PREFIX. "cardless_products`";
        $this->db->query($query);

        $query = "DROP TABLE IF EXISTS `" .DB_PREFIX. "cardless_purchases`";
        $this->db->query($query);
    }


    public function isInstalled()
    {
        return \Extension::isInstalled('cardless');
    }

    public function addCardlessProduct($product_data)
    {

        $sku = $product_data['cardless_sku'];
        $product_id = $product_data['product_id'];

        $query = "SELECT `product_id` FROM `" . DB_PREFIX . "cardless_products` WHERE product_id={$product_id}";
        if ($this->db->query($query)->row) {

            if (!$sku || strlen($sku) == 0) {
                $query = "DELETE FROM  `" .DB_PREFIX. "cardless_products` WHERE product_id={$product_id}";
            } else {
                $query = "UPDATE `" .DB_PREFIX. "cardless_products` SET product_id={$product_id}, sku='{$sku}' WHERE product_id={$product_id}";
            }

        } else {
            $query = "INSERT INTO `" .DB_PREFIX. "cardless_products` (`product_id`, `sku`) VALUES ({$product_id}, '{$sku}')";
        }

        $this->db->query($query);
    }


    public function isCardlessProduct($product_id)
    {
        $query = "SELECT `sku` cardless_sku FROM `" . DB_PREFIX . "cardless_products` ";
        $query .= "WHERE `product_id`='{$product_id}'";
        return $this->db->query($query)->row;
    }

    

	public function getOrderCardlessPurchases($order_id) 
	{
		return $this->db->query("SELECT * FROM `" . DB_PREFIX . "cardless_purchases` WHERE order_id='{$order_id}'")->rows;
	}
}