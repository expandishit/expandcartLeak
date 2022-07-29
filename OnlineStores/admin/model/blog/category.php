<?php


use ExpandCart\Foundation\Support\Facades\Slugify;


class ModelBlogCategory extends Model
{
    /**
     * flash_blog_category table name.
     *
     * @var string
     */
    private $blogCategoryTable = DB_PREFIX . 'flash_blog_category';

    /**
     * flash_blog_category_description table name.
     *
     * @var string
     */
    private $blogCategoryDescriptionTable = DB_PREFIX . 'flash_blog_category_description';

    /**
     * language table name.
     *
     * @var string
     */
    private $languageTable = DB_PREFIX . 'language';

    /**
     * errors array.
     *
     * @var array
     */
    public $errors = [];

    /**
     * get all root categories from database.
     *
     * @return array|bool
     */
    public function getRootCategories()
    {

        $queryString = [];
        $queryString[] = 'SELECT * FROM `'. $this->blogCategoryTable .'` AS bc';
        $queryString[] = 'INNER JOIN `'. $this->blogCategoryDescriptionTable .'` AS bcd';
        $queryString[] = 'ON bc.category_id=bcd.category_id';
        $queryString[] = 'WHERE bcd.language_id=(%s)';
        $queryString[] = 'AND bc.parent_id=0';

        $subQuery  = 'SELECT language_id FROM `' . $this->languageTable . '`';
        $subQuery .= 'WHERE code="' . $this->config->get('config_admin_language') . '"';

        $query = $this->db->query(sprintf(implode(' ', $queryString), $subQuery));

        if ($query->num_rows) {
            return $query->rows;
        }

        return false;
    }

    /**
     * get all categories from database.
     *
     * @return array|bool
     */
    public function getAllCategories()
    {
        $queryString = [];
        $queryString[] = 'SELECT * FROM `'. $this->blogCategoryTable .'` AS bc';
        $queryString[] = 'INNER JOIN `'. $this->blogCategoryDescriptionTable .'` AS bcd';
        $queryString[] = 'ON bc.category_id=bcd.category_id';
        $queryString[] = 'WHERE category_status = 1   AND  bcd.language_id=' . $this->config->get('config_language_id');

        $query = $this->db->query(implode(' ', $queryString));

        if ($query->num_rows) {
            return $query->rows;
        }

        return false;
    }

    /**
     * get all categories from using parent id.
     *
     * @param int $parentId
     *
     * @return array|bool
     */
    public function getCategoriesByParent($parentId)
    {

        $queryString = [];
        $queryString[] = 'SELECT * FROM `'. $this->blogCategoryTable .'` AS bc';
        $queryString[] = 'INNER JOIN `'. $this->blogCategoryDescriptionTable .'` AS bcd';
        $queryString[] = 'ON bc.category_id=bcd.category_id';
        $queryString[] = 'WHERE bcd.language_id=(%s)';
        $queryString[] = 'AND bc.parent_id=' . $parentId;

        $subQuery  = 'SELECT language_id FROM `' . $this->languageTable . '`';
        $subQuery .= 'WHERE code="' . $this->config->get('config_admin_language') . '"';

        $query = $this->db->query(sprintf(implode(' ', $queryString), $subQuery));

        if ($query->num_rows) {
            return $query->rows;
        }

        return false;
    }

    /**
     * retrieve category details from blogCategoryTable table.
     *
     * @param int $categoryId
     *
     * @return array|bool
     */
    public function getCategoryById($categoryId)
    {
        $queryString = [];

        $queryString[] = 'SELECT * FROM `'. $this->blogCategoryTable .'`';
        $queryString[] = 'WHERE category_id=' . $categoryId;

        $data = $this->db->query(implode(' ', $queryString));

        if ($data->num_rows) {
            return $data->row;
        }

        return false;
    }

    /**
     * retrieve category descriptions from blogCategoryDescriptionTable table.
     *
     * @param int $categoryId
     *
     * @return array|bool
     */
    public function getCategoryDescriptionByCategoryId($categoryId)
    {
        $queryString = [];

        $queryString[] = 'SELECT * FROM `'. $this->blogCategoryDescriptionTable .'`';
        $queryString[] = 'WHERE category_id=' . $categoryId;

        $data = $this->db->query(implode(' ', $queryString));

        if ($data->num_rows) {
            return array_column($data->rows, null, 'language_id');
        }

        return false;
    }

    /**
     * insert new entry to flash_blog_categories table.
     *
     * @param array $category
     *
     * @return int
     */
    public function insertCategory($category)
    {
        $queryString = $fields = [];

        $queryString[] = 'INSERT INTO `'. $this->blogCategoryTable .'` SET';

        if (isset($category['parent_id'])) {
            $fields[] = 'parent_id=' . (int)$category['parent_id'];
        }

        $fields[] = 'sort_order=' . (int)$category['sort_order'];
        $fields[] = 'category_status=' . (int)$category['category_status'];
        $fields[] = 'user_id=' . $this->user->getId();
        $fields[] = 'category_image="' . $this->db->escape($category['category_image']) . '"';

        $queryString[] = implode(', ', $fields);

        $this->db->query(implode(' ', $queryString));

        return $this->db->getLastId();
    }

    /**
     * insert new entry to flash_blog_categories table.
     *
     * @param int $categoryId
     * @param array $categoryDescription
     *
     * @return void
     */
    public function insertCategoryDescription($categoryId, $categoryDescription)
    {
        foreach ($categoryDescription as $languageId => $description) {

            $queryString = $fields = [];

            $queryString[] = 'INSERT INTO `'. $this->blogCategoryDescriptionTable .'` SET';
            $fields[] = 'category_id=' . $categoryId;
            $fields[] = 'language_id=' . $languageId;
            $fields[] = 'name="' . Slugify::trim($this->db->escape($description['name'])) . '"';
            $fields[] = 'slug="' . Slugify::slug($description['name']) . '"';
            $fields[] = 'description="' . Slugify::trim($this->db->escape($description['description'])) . '"';
            $fields[] = 'meta_description="' . Slugify::trim($this->db->escape($description['meta_description'])) . '"';
            $fields[] = 'meta_keywords="' . Slugify::trim($this->db->escape($description['meta_keywords'])) . '"';
            $fields[] = 'tags="' . Slugify::trim($this->db->escape($description['tags'])) . '"';

            $queryString[] = implode(', ', $fields);

            $this->db->query(implode(' ', $queryString));
        }
    }

    /**
     * update flash_blog_categories table using category id.
     *
     * @param int $categoryId
     * @param array $category
     *
     * @return void
     */
    public function updateCategory($categoryId, $category)
    {
        $queryString = $fields = [];

        $queryString[] = 'UPDATE `'. $this->blogCategoryTable .'` SET';

        $fields[] = 'sort_order=' . (int)$category['sort_order'];
        $fields[] = 'category_status=' . (int)$category['category_status'];
        $fields[] = 'category_image="' . $this->db->escape($category['category_image']) . '"';

        $queryString[] = implode(', ', $fields);
        $queryString[] = 'WHERE category_id=' . $categoryId;

        $this->db->query(implode(' ', $queryString));
    }

    /**
     * update flash_blog_category_description table using category id.
     *
     * @param int $categoryId
     * @param array $categoryDescription
     *
     * @return void
     */
    public function updateCategoryDescription($categoryId, $categoryDescription)
    {
        foreach ($categoryDescription as $languageId => $description) {

            $queryString = $fields = [];

            $queryString[] = 'UPDATE `'. $this->blogCategoryDescriptionTable .'` SET';
            $fields[] = 'name="' . Slugify::trim($this->db->escape($description['name'])) . '"';
            $fields[] = 'slug="' . Slugify::slug($description['name']) . '"';
            $fields[] = 'description="' . Slugify::trim($this->db->escape($description['description'])) . '"';
            $fields[] = 'meta_description="' . Slugify::trim($this->db->escape($description['meta_description'])) . '"';
            $fields[] = 'meta_keywords="' . Slugify::trim($this->db->escape($description['meta_keywords'])) . '"';
            $fields[] = 'tags="' . Slugify::trim($this->db->escape($description['tags'])) . '"';

            $queryString[] = implode(', ', $fields);
            $queryString[] = 'WHERE category_id=' . $categoryId;
            $queryString[] = 'AND language_id=' . $languageId;

            $this->db->query(implode(' ', $queryString));
        }
    }

    /**
     * delete row from $blogCategoryTable.
     *
     * @param int $categoryId
     *
     * @return void
     */
    public function deleteCategory($categoryId)
    {
        $this->db->query('DELETE FROM `' . $this->blogCategoryTable . '` WHERE category_id=' . $categoryId);
    }

    /**
     * delete row from $blogCategoryDescriptionTable.
     *
     * @param int $categoryId
     *
     * @return void
     */
    public function deleteCategoryDescription($categoryId)
    {
        $this->db->query(
            'DELETE FROM `' . $this->blogCategoryDescriptionTable . '` WHERE category_id=' . $categoryId
        );
    }

    /**
     * validate form inputs.
     *
     * @param array $data
     *
     * @return bool
     */
    public function validate($data)
    {
        $category = $data['category'];
        $descriptions = $data['description'];

        $errors = array();

        if ( !isset($category['sort_order']) )
        {
            $errors['sort_order'] = $this->language->get('error_not_isset_sort_order');
        }

        if ( preg_match('#^\d+$#', $category['sort_order']) == false )
        {
            $errors['sort_order'] = $this->language->get('error_invalid_sort_order_value');
        }

        foreach ( $descriptions as $description )
        {
            if ( ! isset( $description['name'] ) )
            {
                $errors['warning'] = $this->language->get('error_not_isset_name');
            }

            if ( strlen($description['name']) == 0 )
            {
                $errors['warning'] = $this->language->get('error_empty_name');
            }
        }
        
        return $errors ?: true;
    }

    public function dtHandler($start = 0, $length = 10, $search = null, $orderColumn = "name", $orderType = "ASC")
    {
        $query = "SELECT bcd.*, bc.category_status, DATE_FORMAT(created_at, \"%e %M %Y\") creation_date FROM flash_blog_category AS bc 
                  INNER JOIN flash_blog_category_description AS bcd ON bc.category_id = bcd.category_id AND bcd.language_id=" . (int)$this->config->get('config_language_id');
        //" LEFT JOIN flash_blog_category_description AS bcd ON bcd.category_id = bp.parent_id";

        $total = $this->db->query($query)->num_rows;
        $where = "";
        if (!empty($search)) {
            $where .= "(bcd.name like '%" . $this->db->escape($search) . "%'
                        OR bcd.description like '%" . $this->db->escape($search) . "%') ";
            $query .= " WHERE " . $where;
        }

        $totalFiltered = $this->db->query($query)->num_rows;
        $query .= " ORDER by {$orderColumn} {$orderType}";

        if ($length != -1) {
            $query .= " LIMIT " . $start . ", " . $length;
        }
        
        $results=$this->db->query($query)->rows;
        foreach ($results as $key => $row) {
            # code...
            $results[$key]['description'] = strip_tags(html_entity_decode($row['description']));
        }
        $data = array(
            'data' => $results,
            'total' => $total,
            'totalFiltered' => $totalFiltered
        );

        return $data;
    }

    public function dtUpdateStatus($postDescId, $status)
    {
        $query = [];
        $query[] = 'UPDATE ' . $this->blogCategoryTable . ' SET';
        $query[] = 'category_status="' . $this->db->escape($status) . '"';
        $query[] = 'WHERE category_id=' . $this->db->escape($postDescId);

        $this->db->query(implode(' ', $query));
    }
}
