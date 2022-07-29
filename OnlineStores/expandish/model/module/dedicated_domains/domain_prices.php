<?php

use ExpandCart\Foundation\Providers\DedicatedDomains;

class ModelModuleDedicatedDomainsDomainPrices extends Model
{

    private $dedicatedDomainsPricesTable = DB_PREFIX . 'dedicated_domains_prices';

    public function isActive()
    {
        $dedicatedDomains = new DedicatedDomains;

        if ($dedicatedDomains->setRegistry($this->registry)->isActive()) {

            unset($dedicatedDomains);

            return true;
        }

        unset($dedicatedDomains);

        return false;
    }

    public function getDomain()
    {
        return (new DedicatedDomains)->setServerName($_SERVER['SERVER_NAME'])
            ->setRegistry($this->registry)
            ->getDomain();
    }

    public function getProductDedicatedPrices($product_id, $domainData)
    {
        $queryString = [];
        $queryString[] = 'SELECT * FROM `' . $this->dedicatedDomainsPricesTable . '`';
        $queryString[] = 'WHERE domain_id="' . $domainData['domain_id'] . '"';
        $queryString[] = 'AND product_id=' . $product_id;

        $data = $this->db->query(implode(' ', $queryString));

        if (!isset($data->num_rows) || $data->num_rows != 1) {
            return false;
        }

        $dedicatedPrice = $data->row;

        if ($dedicatedPrice) {
            $price = $this->currency->convert(
                $dedicatedPrice['price'],
                $domainData['currency'],
                $this->config->get('config_currency')
            );
        } else {
            $price = null;
        }

        return $price;
    }

    public function getProductDedicatedSpecial($productId, $customer_group_id, $domainData)
    {
        $queryString = [];

        $queryString[] = 'SELECT * FROM product_special WHERE product_id=' . $productId;
        $queryString[] = 'AND customer_group_id = "' . (int)$customer_group_id . '" AND';
        $queryString[] = '((IFNULL(date_start, "0000-00-00") = "0000-00-00" OR date_start < NOW()) AND';
        $queryString[] = '(IFNULL(date_end, "0000-00-00") = "0000-00-00" OR date_end > NOW()))';
        $queryString[] = 'AND (dedicated_domains IN (0, "' . $domainData['domain_id'] . '") OR dedicated_domains IS NULL)';
        $queryString[] = 'ORDER BY priority ASC, price ASC LIMIT 1';

        $data = $this->db->query(implode(' ', $queryString));

        if (!isset($data->num_rows) || $data->num_rows != 1) {
            return false;
        }

        $dedicatedSpecial = $data->row;

        if ($dedicatedSpecial['dedicated_domains'] == 0) {
//            $special = $dedicatedSpecial['price'];
        } else {
            $dedicatedSpecial['price'] = $this->currency->convert(
                $dedicatedSpecial['price'],
                $domainData['currency'],
                $this->config->get('config_currency')
            );
        }

        if ($dedicatedSpecial['price']) {
            $special = $dedicatedSpecial['price'];
        } else {
            $special = null;
        }

        return $special;
    }

    public function getProductDedicatedDiscount($productId, $customer_group_id, $domainData)
    {
        $queryString = [];

        $queryString[] = 'SELECT * FROM product_discount WHERE product_id=' . $productId;
        $queryString[] = 'AND customer_group_id = "' . (int)$customer_group_id . '" AND';
        $queryString[] = 'quantity = "1" AND ((IFNULL(date_start, "0000-00-00") = "0000-00-00" OR date_start < NOW()) AND';
        $queryString[] = '(IFNULL(date_end, "0000-00-00") = "0000-00-00" OR date_end > NOW()))';
        $queryString[] = 'AND (dedicated_domains IN (0, "' . $domainData['domain_id'] . '") OR dedicated_domains IS NULL)';
        $queryString[] = 'ORDER BY priority ASC, price ASC LIMIT 1';

        $data = $this->db->query(implode(' ', $queryString));

        if (!isset($data->num_rows) || $data->num_rows != 1) {
            return false;
        }

        $dedicatedDiscount = $data->row;

        if ($dedicatedDiscount['dedicated_domains'] != 0) {
            $dedicatedDiscount['price'] = $this->currency->convert(
                $dedicatedDiscount['price'],
                $domainData['currency'],
                $this->config->get('config_currency')
            );
        }

        if ($dedicatedDiscount) {
            $discount = $dedicatedDiscount['price'];
        } else {
            $discount = null;
        }

        return $discount;
    }

    public function getProductDedicatedDiscountByQuantity($productId, $customer_group_id, $domainData, $quantity)
    {
        $queryString = [];

        $queryString[] = 'SELECT * FROM product_discount WHERE product_id=' . $productId;
        $queryString[] = 'AND customer_group_id = "' . (int)$customer_group_id . '"';
        $queryString[] = 'AND (quantity <= "'.$quantity.'")';
        $queryString[] = 'AND ((IFNULL(date_start, "0000-00-00") = "0000-00-00" OR date_start < NOW()) AND';
        $queryString[] = '(IFNULL(date_end, "0000-00-00") = "0000-00-00" OR date_end > NOW()))';
        $queryString[] = 'AND (dedicated_domains IN (0, "' . $domainData['domain_id'] . '") OR dedicated_domains IS NULL)';
        $queryString[] = 'ORDER BY priority ASC, price ASC LIMIT 1';

        $data = $this->db->query(implode(' ', $queryString));

        if (!isset($data->num_rows) || $data->num_rows != 1) {
            return false;
        }

        $dedicatedDiscount = $data->row;

        if ($dedicatedDiscount['dedicated_domains'] != 0) {
            $dedicatedDiscount['price'] = $this->currency->convert(
                $dedicatedDiscount['price'],
                $domainData['currency'],
                $this->config->get('config_currency')
            );
        }

        if ($dedicatedDiscount) {
            $discount = $dedicatedDiscount['price'];
        } else {
            $discount = null;
        }

        return $discount;
    }

    public function getProductDedicatedDiscounts($productId, $customer_group_id, $domainId, $domainCurrency)
    {
        $queryString = [];

        $queryString[] = 'SELECT * FROM product_discount WHERE product_id=' . $productId;
        $queryString[] = 'AND customer_group_id = "' . (int)$customer_group_id . '" AND';
        $queryString[] = 'quantity > 1 AND ((IFNULL(date_start, "0000-00-00") = "0000-00-00" OR date_start < NOW()) AND';
        $queryString[] = '(IFNULL(date_end, "0000-00-00") = "0000-00-00" OR date_end > NOW()))';
        $queryString[] = 'AND (dedicated_domains IN (0, "' . $domainId . '") OR dedicated_domains IS NULL)';
        $queryString[] = 'ORDER BY priority ASC, price ASC';

        $data = $this->db->query(implode(' ', $queryString));

        if ($data->num_rows) {

            $data->rows = array_map(function ($value) use ($domainCurrency) {
                if ($value['dedicated_domains'] > 0) {
                    $value['price'] = $this->currency->convert(
                        $value['price'],
                        $domainCurrency,
                        $this->config->get('config_currency')
                    );
                }

                return $value;
            }, $data->rows);

            return $data->rows;
        }

        return false;
    }

    /***
     * Factory method to bulk update price, discount and special to be used in multiple places
     *
     * @param int $product_id
     * @param int $customer_group_id
     * @param array $domainData
     * @param float $price
     * @param float $discount
     * @param float $special
     *
     * @return void
     */
    public function updatePrices($product_id, $customer_group_id, $domainData, &$price, &$discount, &$special)
    {
        $dedicatedDiscount = $this->getProductDedicatedDiscount(
            $product_id,
            $customer_group_id,
            $domainData['domain_id']
        );

        if ($dedicatedDiscount['dedicated_domains'] != 0) {
            $dedicatedDiscount['price'] = $this->currency->convert(
                $dedicatedDiscount['price'],
                $domainData['currency'],
                $this->config->get('config_currency')
            );
        }

        if ($dedicatedDiscount) {
            $discount = $dedicatedDiscount['price'];
        } else {
            $discount = null;
        }

        $dedicatedPrice = $this->getProductDedicatedPrices(
            $product_id,
            $domainData['domain_id']
        );

        if ($dedicatedPrice) {
            if ($dedicatedDiscount['price']) {
                $price = $this->currency->convert(
                    $dedicatedDiscount['price'],
                    $domainData['currency'],
                    $this->config->get('config_currency')
                );
            } else {
                $price = $this->currency->convert(
                    $dedicatedPrice['price'],
                    $domainData['currency'],
                    $this->config->get('config_currency')
                );
            }
        }

        $dedicatedSpecial = $this->getProductDedicatedSpecial(
            $product_id,
            $customer_group_id,
            $domainData['domain_id']
        );

        if ($dedicatedSpecial) {
            if ($dedicatedSpecial['dedicated_domains'] == 0) {
                $special = $dedicatedSpecial['price'];
            } else {
                $special = $this->currency->convert(
                    $dedicatedSpecial['price'],
                    $domainData['currency'],
                    $this->config->get('config_currency')
                );
            }
        } else {
            $special = null;
        }
    }
}
