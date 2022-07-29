<?php
/**
 *
 *
 */
trait CategoryHelper {
    /**
     *
     *
     */
    private function getCategoryIdFromPath(string $path): int
    {
        $parts = explode('_', $path);

        return (int)array_pop($parts);
    }

    /**
    * return the breadcrumbs as an array of links for a specific category
    *
    * @param array $category_info
    */
    private function createBreadcrumbs($category_info): array
    {
        $breadcrumbs = [];

        //Check if invalid parameter is given
        if(!is_array($category_info))
            throw new InvalidArgumentException('createBreadcrumbs function only accepts array. Input was: '.$category_info);

        //Loop from this sub-category through the tree to the big parent (main category)
        $category_temp = $category_info;

        while($category_temp){

            $item = array(
                'text'      => $category_temp['name'],
                'href'      => $this->url->link('product/category', 'path=' . $category_temp['category_id']),
                'separator' => $this->language->get('text_separator')
            );

            //add this node as first element in the array breadcrumbs
            array_unshift($breadcrumbs, $item);
            if ($category_info['category_id'] === $category_temp['parent_id']) {
                break;
            }
            $category_temp = $this->model_catalog_category->getCategory($category_temp['parent_id']);
        }

        //Add home page as first node
        array_unshift($breadcrumbs, [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home'),
            'separator' => false
        ]);

        return $breadcrumbs;
    }

    private function formatUrlFromRequestParams($keys = ['all']): string
    {
        $url = '';

        if (!empty($this->request->get['mfp']) && count(array_intersect($keys, ['all', 'mfp'])) >= 1 ) {
            $url .= '&mfp=' . $this->request->get['mfp'];
        }

        if (isset($this->request->get['sort']) && count(array_intersect($keys, ['all', 'sort'])) >= 1) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order']) && count(array_intersect($keys, ['all', 'order'])) >= 1) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['page']) && count(array_intersect($keys, ['all', 'page'])) >= 1) {
            $url .= '&page=' . $this->request->get['page'];
        }

        if (isset($this->request->get['limit']) && count(array_intersect($keys, ['all', 'limit'])) >= 1) {
            $url .= '&limit=' . $this->request->get['limit'];
        }

        if (isset($this->request->get['filter']) && count(array_intersect($keys, ['all', 'filter'])) >= 1) {
            $url .= '&filter=' . $this->request->get['filter'];
        }

        return $url;
    }

    private function getSortsList(): array
    {
        $url = $this->formatUrlFromRequestParams(['mfp', 'filter', 'limit']);

        $sorts = [];

        $sorts[] = array(
            'text' => $this->language->get('text_default'),
            'value' => 'p.sort_order-ASC',
            'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=' . $this->getDefaultSort() . '&order=ASC' . $url)
        );

        $sorts[] = array(
            'text' => $this->language->get('text_new_first'),
            'value' => 'p.date_available-DESC',
            'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.date_available&order=DESC' . $url)
        );

        $sorts[] = array(
            'text' => $this->language->get('text_name_asc'),
            'value' => 'pd.name-ASC',
            'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=pd.name&order=ASC' . $url)
        );

        $sorts[] = array(
            'text' => $this->language->get('text_name_desc'),
            'value' => 'pd.name-DESC',
            'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=pd.name&order=DESC' . $url)
        );

        $sorts[] = array(
            'text' => $this->language->get('text_price_asc'),
            'value' => 'p.price-ASC',
            'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.price&order=ASC' . $url)
        );

        $sorts[] = array(
            'text' => $this->language->get('text_price_desc'),
            'value' => 'p.price-DESC',
            'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.price&order=DESC' . $url)
        );

        if ($this->config->get('config_review_status')) {
            $sorts[] = array(
                'text' => $this->language->get('text_rating_desc'),
                'value' => 'rating-DESC',
                'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=rating&order=DESC' . $url)
            );

            $sorts[] = array(
                'text' => $this->language->get('text_rating_asc'),
                'value' => 'rating-ASC',
                'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=rating&order=ASC' . $url)
            );
        }

        $sorts[] = array(
            'text' => $this->language->get('text_model_asc'),
            'value' => 'p.model-ASC',
            'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.model&order=ASC' . $url)
        );

        $sorts[] = array(
            'text' => $this->language->get('text_model_desc'),
            'value' => 'p.model-DESC',
            'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.model&order=DESC' . $url)
        );

        return $sorts;
    }

    private function getLimits(){
        $limits_arr = [];

        $limits = array_unique(array($this->config->get('config_catalog_limit'), 25, 50, 75, 100));

        sort($limits);

        foreach ($limits as $limits) {
            $limits_arr[] = array(
                'text' => $limits,
                'value' => $limits,
                'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'] . $this->formatUrlFromRequestParams(['mpf', 'filter', 'sort', 'order']) . '&limit=' . $limits)
            );
        }

        return $limits_arr;
    }

    /**
     * Returns the sort value according to the url parameters 'sort'
     * If no sort selected, then apply the default one
     */
    public function getSortValue(): string
    {
        return isset($this->request->get['sort']) ? $this->request->get['sort'] : $this->getDefaultSort();
    }

    public function getSortOrderValue(): string
    {
        return isset($this->request->get['order']) ? $this->request->get['order'] : $this->getDefaultSortOrder();
    }

    private function getPageNumber(): int
    {
        return isset($this->request->get['page']) ? $this->request->get['page'] : 1;
    }

    private function getFilterValue(): string
    {
        return isset($this->request->get['filter']) ? $this->request->get['filter'] : '';
    }

    private function getLimitValue(): int
    {
        return isset($this->request->get['limit']) ? $this->request->get['limit'] : $this->config->get('config_catalog_limit');
    }

    private function getDefaultSort()
    {
        $defaultSortColumnName = '';
        $defaultSortColumnValue = '';

        if (\Extension::isinstalled('product_sort') && $this->config->get('product_sort_app_status') == '1') {
            $defaultSortColumnName = "product_manual_sort";
        }
        else{
            $defaultSortColumnName = $this->config->get('config_products_default_sorting_column');
        }

        if ($defaultSortColumnName === 'product_manual_sort') {
            $defaultSortColumnValue = "product_manual_sort";
        }
        elseif ($defaultSortColumnName === 'name') {
            $defaultSortColumnValue = "pd.name";
        } elseif ($defaultSortColumnName === 'date_added') {
            $defaultSortColumnValue = "p.date_added";
        } elseif ($defaultSortColumnName === 'stock_status') {
            $defaultSortColumnValue = "stock_status";
        } else {
            $defaultSortColumnValue = !empty($defaultSortColumnName) ? "p.{$defaultSortColumnName}" : 'p.sort_order';
        }

        return $defaultSortColumnValue;
    }

    private function getDefaultSortOrder()
    {
        return $this->config->get('config_products_default_sorting_by_column') ?: 'ASC';
    }
}
