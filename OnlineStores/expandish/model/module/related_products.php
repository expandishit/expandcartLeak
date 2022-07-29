<?php

class ModelModuleRelatedProducts extends Model
{

    public $module;

    private $productTable = DB_PREFIX . 'product';
    private $productToCategoryTable = DB_PREFIX . 'product_to_category';
    private $productDescriptionTable = DB_PREFIX . 'product_description';

    private function getProductsCount()
    {
        $module = $this->config->get('related_products');

        return ((int)$module['products_count'] > 0 ? $module['products_count'] : 20);
    }

    public function isActive()
    {
        $this->module = $this->config->get('related_products');

        if (isset($this->module) && $this->module['rp_status'] == 1) {
            return true;
        }

        return false;
    }

    public function getRelatedProductsByCategory($productId)
    {
        if (!$this->config->get('related_products')['enable_random'] || !in_array('categories', $this->module['source'])) {
            return [];
        }

        $queryString = [];
        $queryString[] = 'SELECT p.*, pd.name FROM ' . $this->productTable . ' p';
        $queryString[] = 'INNER JOIN ' . $this->productToCategoryTable;
        $queryString[] = 'ON p.product_id=product_to_category.product_id';
        $queryString[] = 'LEFT JOIN ' . $this->productDescriptionTable . ' pd';
        $queryString[] = 'ON (p.product_id = pd.product_id)';
        $queryString[] = 'WHERE product_to_category.category_id IN (%s)';
        $queryString[] = 'AND pd.language_id = ' . (int)$this->config->get('config_language_id');
        $queryString[] = 'AND p.product_id != ' . (int)$productId;
        $queryString[] = 'AND p.status=1';
        $queryString[] = 'group by p.product_id';
        $queryString[] = 'LIMIT ' . $this->getProductsCount();

        $subQuery = 'SELECT category_id FROM ' . $this->productToCategoryTable . ' WHERE product_id=' . $productId;

        $queryString = sprintf(
            implode(' ', $queryString), $subQuery
        );

        $data = $this->db->query($queryString);

        return $data->rows;
    }

    public function getRelatedProductsByManufacturer($manufacturerId, $productId)
    {
        if (!$this->config->get('related_products')['enable_random'] || !in_array('manufacturers', $this->module['source'])) {
            return [];
        }

        if ($manufacturerId > 0) {
            $queryString = [];
            $queryString[] = 'SELECT p.*, pd.name FROM ' . $this->productTable . ' p';
            $queryString[] = ' LEFT JOIN ' . $this->productDescriptionTable . ' pd';
            $queryString[] = 'ON (p.product_id = pd.product_id)';
            $queryString[] = 'WHERE manufacturer_id=' . $manufacturerId;
            $queryString[] = 'AND pd.language_id = ' . (int)$this->config->get('config_language_id');
            $queryString[] = 'AND p.product_id != ' . (int)$productId;
            $queryString[] = 'AND p.status=1';
            $queryString[] = 'LIMIT ' . $this->getProductsCount();

            
            $data = $this->db->query(implode(' ', $queryString));

            return $data->rows;
        }

        return [];
    }

    public function getRelatedProductsByKeyword($keywordString, $productId)
    {
        if (!$this->config->get('related_products')['enable_random'] || !in_array('tags', $this->module['source'])) {
            return [];
        }

        $dataSet = [];

        if ($keywordString != '') {

            $keywords = explode(',', $keywordString);

            foreach ($keywords as $keyword) {
                $queryString = [];
                $queryString[] = 'SELECT * FROM ' . $this->productDescriptionTable . ' as pd';
                $queryString[] = 'INNER JOIN ' . $this->productTable . ' as p';
                $queryString[] = 'ON p.product_id=pd.product_id';
                $queryString[] = 'WHERE pd.tag LIKE "%' . $this->db->escape(trim($keyword)) . '%"';
                $queryString[] = 'AND pd.language_id = ' . (int)$this->config->get('config_language_id');
                $queryString[] = 'AND p.product_id != ' . (int)$productId;
                $queryString[] = 'AND p.status=1';
                $queryString[] = 'LIMIT ' . $this->getProductsCount();

                $data = $this->db->query(implode(' ', $queryString));

                $dataSet = array_merge($dataSet,$data->rows);
            }
            
            return $dataSet;

        }

        return [];
    }

    public function getRandomProducts()
    {
        if (isset($this->module['enable_random']) && $this->module['enable_random'] == 1) {

            $queryString = [];

            $limit = $this->module['products_count'] - $totalCount;

            $queryString[] = 'SELECT * FROM ' . $this->productTable . ' AS r1 JOIN (%s) AS r2
                 WHERE r1.product_id >= r2.id
                 AND r1.status=1
                 ORDER BY r1.product_id ASC
                 LIMIT ' . $limit;

            $subQuery = 'SELECT CEIL(RAND() * (SELECT MAX(product_id) FROM ' . $this->productTable . ')) AS id';

            $data = $this->db->query(sprintf(implode(' ', $queryString), $subQuery));

            return $data->rows;
        }

        return [];
    }

    public function mergeCatalog(&$catalog)
    {
        $data = [];
       // var_dump($catalog);die();
        if ($this->dedicatedDomains->isActive()) {
            $productDomain = $this->load->model('module/dedicated_domains/product_domain', ['return' => true]);

            $domainId = $this->dedicatedDomains->getDomainId();

            foreach ($catalog as $set) {
                foreach ($set as &$product) {
                    if ($productDomains = $productDomain->getProductDomainsByProductId($product['product_id'])) {
                        $productDomainsId = $productDomain->getProductDomainIds($productDomains);
                        if (isset(array_flip($productDomainsId)[$domainId])) {
                            $data[$product['product_id']] = $product;
                        }
                    }
                }
            }
        } else {
            foreach ($catalog as $set) {
                foreach ($set as &$product) {
                    $data[$product['product_id']] = $product;
                }
            }
        }

        $randomCount = count($data);

        if ($randomCount < $this->module['products_count']) {
            $randomProducts = $this->getRandomProducts();

            $data = array_merge($data, $randomProducts);
        }
        $random = [];

        $randomData = array_rand($data, min($this->getProductsCount(), count($data)));

        if (!is_array($randomData)) {
            $randomData = [$randomData];
        }

        foreach ($randomData as &$set) {
            $random[] = $data[$set];
        }


        return $random;
    }
}