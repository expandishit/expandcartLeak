<?php

class ModelCatalogProductFilter extends Model
{
    private $productTable = '`' . DB_PREFIX . 'product`';

    private function getMinMax($field, $table = null)
    {
        $fields = 'MIN(`' . $field . '`) as _min, MAX(`' . $field . '`) as _max';

        $queryString = 'SELECT ' . $fields . ' FROM ' . ($table ?: $this->productTable);

        $data = $this->db->query($queryString);

        if ($data->num_rows) {
            return [
                'min' => floor($data->row['_min']),
                'max' => ceil($data->row['_max']),
            ];
        }

        return false;
    }

    public function getMinMaxPrice()
    {
        return $this->getMinMax('price');
    }

    public function getMinMaxCostPrice()
    {
        return $this->getMinMax('cost_price');
    }

    public function getMinMaxQuantity()
    {
        return $this->getMinMax('quantity');
    }

    public function getMinMaxPoints()
    {
        return $this->getMinMax('points');
    }

    public function getMinMaxWeight()
    {
        return $this->getMinMax('weight');
    }

    public function getMinMaxLength()
    {
        return $this->getMinMax('length');
    }

    public function getMinMaxWidth()
    {
        return $this->getMinMax('width');
    }

    public function getMinMaxHeight()
    {
        return $this->getMinMax('height');
    }

    public function getModels()
    {
        $data = $this->db->query('SELECT `model` FROM ' . $this->productTable . ' GROUP BY `model`');

        if ($data->num_rows) {
            return $data->rows;
        }

        return false;
    }

    public function getFilter()
    {
        $categories = $this->load->model('catalog/category', ['return' => true]);
        $brands = $this->load->model('catalog/manufacturer', ['return' => true]);
        $taxes = $this->load->model('localisation/tax_class', ['return' => true]);
        $weight = $this->load->model('localisation/weight_class', ['return' => true]);
        $length = $this->load->model('localisation/length_class', ['return' => true]);

        $filter = [
            'categories' => $categories->getCategories([]),
            'brands' => $brands->getManufacturers(),
            'taxes' => $taxes->getTaxClasses(),
            'weights' => $weight->getWeightClasses(),
            'lengths' => $length->getLengthClasses(),
            'priceRange' => $this->getMinMaxPrice(),
            'costPriceRange' => $this->getMinMaxCostPrice(),
            'quantityRange' => $this->getMinMaxQuantity(),
            'pointsRange' => $this->getMinMaxPoints(),
            'weightRange' => $this->getMinMaxWeight(),
            'lengthRange' => $this->getMinMaxLength(),
            'widthRange' => $this->getMinMaxWidth(),
            'heightRange' => $this->getMinMaxHeight(),
            'booleans' => [
                'image' => $this->language->get('product_has_image'),
                'subtract' => $this->language->get('product_has_substract'),
                'status' => $this->language->get('product_has_status'),
                'shipping' => $this->language->get('product_has_shipping'),
                'downloads' => $this->language->get('product_has_downloads'),
                // 'discounts' => $this->language->get('product_has_discount'),
                // 'specials' => $this->language->get('product_has_special'),
                'specialAndDiscounts' => $this->language->get('product_has_special_and_discount')
            ],
        ];

        return $filter;

        $this->response->setOutput(json_encode($filter));
    }
}
