<?php

class ModelCatalogBlogCategory extends Model
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
     * flash_blog_post table name.
     *
     * @var string
     */
    private $blogPostTable = DB_PREFIX . 'flash_blog_post';

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
     * get latest $count categories from ($blogCategoryTable X $blogCategoryDescriptionTable) tables.
     *
     * @param int $count
     * @param string $language_code
     *
     * @return array|bool
     */
    public function getLatestCategories($count = 10, $language_code)
    {
        $queryString = $subQuery = [];

        $queryString[] = 'SELECT * FROM `' . $this->blogCategoryTable . '` as bc';
        $queryString[] = 'INNER JOIN `' . $this->blogCategoryDescriptionTable . '` as bdc';
        $queryString[] = 'ON bc.category_id=bdc.category_id';
        $queryString[] = 'WHERE category_status=1';
        $queryString[] = 'AND parent_id=0';
        $queryString[] = 'AND bdc.language_id=(%s)';
        $queryString[] = 'ORDER BY bc.sort_order ASC';
        $queryString[] = ', bc.category_id DESC';
        $queryString[] = 'LIMIT ' . $count;

        $subQuery[] = 'SELECT language_id FROM `' . $this->languageTable . '`';
        $subQuery[] = 'WHERE code="' . $language_code . '"';

        $data = $this->db->query(
            sprintf(
                implode(' ', $queryString),
                implode(' ', $subQuery)
            )
        );

        if ($data->num_rows) {
            return $data->rows;
        }

        return false;
    }

    /**
     * get sub categories based on parent id.
     *
     * @param int $parentId
     * @param string $language_code
     *
     * @return array|bool
     */
    public function getCategoriesByParent($parentId, $language_code)
    {
        $queryString = [];
        $queryString[] = 'SELECT *, (%s) as postCount';
        $queryString[] = 'FROM `'. $this->blogCategoryTable .'` AS bc';
        $queryString[] = 'INNER JOIN `'. $this->blogCategoryDescriptionTable .'` AS bcd';
        $queryString[] = 'ON bc.category_id=bcd.category_id';
        $queryString[] = 'WHERE bcd.language_id=(%s)';
        $queryString[] = 'AND bc.parent_id=' . $parentId;
        $queryString[] = 'AND bc.category_status=1';

        $countQuery  = 'SELECT count(*) as postCount FROM `' . $this->blogPostTable . '` as pb ';
        $countQuery .= 'WHERE pb.parent_id=bc.category_id';

        $langQuery  = 'SELECT language_id FROM `' . $this->languageTable . '`';
        $langQuery .= 'WHERE code="' . $language_code . '"';

        $query = $this->db->query(sprintf(implode(' ', $queryString), $countQuery, $langQuery));

        if ($query->num_rows) {
            return $query->rows;
        }

        return false;
    }

    /**
     * retrieve category details from $blogCategoryTable table.
     *
     * @param int $categoryId
     * @param string $language
     *
     * @return array|bool
     */
    public function getCategoryDescriptionById($categoryId, $language)
    {
        $queryString = $lanaguagesQuery = [];

        $queryString[] = 'SELECT * FROM `'. $this->blogCategoryDescriptionTable .'` as bcd';
        $queryString[] = 'INNER JOIN `'. $this->blogCategoryTable .'` as bc';
        $queryString[] = 'ON bc.category_id=bcd.category_id';
        $queryString[] = 'WHERE bc.category_id=' . $categoryId;
        $queryString[] = 'AND bc.category_status=1';
        $queryString[] = 'AND language_id=(%s)';

        $lanaguagesQuery[] = 'SELECT language_id FROM `' . $this->languageTable . '`';
        $lanaguagesQuery[] = 'WHERE code="' . $language . '"';

        $data = $this->db->query(
            sprintf(
                implode(' ', $queryString),
                implode(' ', $lanaguagesQuery)
            )
        );

        if ($data->num_rows) {
            return $data->row;
        }

        return false;
    }

    /**
     * Return the category id using the slug string.
     *
     * @param string $slug
     *
     * @return bool|int
     */
    public function getCategoryIdBySlug($slug)
    {
        $query = [];

        $query[] = 'SELECT category_id FROM ' . $this->blogCategoryDescriptionTable;
        $query[] = 'WHERE slug="' . $this->db->escape($slug) . '"';

        $data = $this->db->query(implode(' ', $query));

        if ($data->num_rows) {
            return $data->row['category_id'];
        }

        return false;
    }
}
