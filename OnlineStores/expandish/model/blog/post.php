<?php

class ModelBlogPost extends Model
{
    /**
     * flash_blog_post table name.
     *
     * @var string
     */
    private $blogPostTable = DB_PREFIX . 'flash_blog_post';

    /**
     * flash_blog_post_description table name.
     *
     * @var string
     */
    private $blogPostDescriptionTable = DB_PREFIX . 'flash_blog_post_description';

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
     * get latest $count posts.
     *
     * @param int $count
     *
     * @return array|bool
     */
    public function getLatestPosts($count = 10)
    {
        $queryString = $subQuery = [];

        $queryString[] = 'SELECT *, (%s) as lang_id, bcd.name as category_name,bp.created_at  FROM `' . $this->blogPostTable . '` as bp';
        $queryString[] = 'INNER JOIN `' . $this->blogCategoryDescriptionTable . '` as bcd';
        $queryString[] = 'ON bcd.category_id=bp.parent_id';
        $queryString[] = 'INNER JOIN `' . $this->blogPostDescriptionTable . '` as bpd';
        $queryString[] = 'ON bp.post_id=bpd.post_id';
        $queryString[] = 'INNER JOIN `' . $this->blogCategoryTable . '` as bc';
        $queryString[] = 'ON bc.category_id=bp.parent_id';
        $queryString[] = 'WHERE bpd.post_status=1';
        $queryString[] = 'AND category_status=1';
        $queryString[] = 'HAVING bpd.language_id=lang_id';
        $queryString[] = 'AND bcd.language_id=lang_id';
        $queryString[] = 'ORDER BY bp.sort_order ASC';
        $queryString[] = ', bp.post_id DESC';
        $queryString[] = 'LIMIT ' . $count;

        $subQuery[] = 'SELECT language_id FROM `' . $this->languageTable . '`';
        $subQuery[] = 'WHERE code="' . $this->session->data['language'] . '"';

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
     * get posts from $blogPostTable, $blogPostDescriptioinTable tables based on parent category id.
     *
     * @param int $categoryId
     *
     * @return array|bool
     */
    public function getPostsByCategoryId($categoryId)
    {
        $queryString = $lanaguagesQuery = [];
        $queryString[] = 'SELECT bp.post_id , bp.created_at , bpd.name , bpd.description , bpd.visits , bp.post_image FROM `'. $this->blogPostTable .'` AS bp';
        $queryString[] = 'INNER JOIN `'. $this->blogPostDescriptionTable .'` AS bpd';
        $queryString[] = 'ON bp.post_id=bpd.post_id';
        $queryString[] = 'WHERE bp.parent_id=' . $categoryId;
        $queryString[] = 'AND bpd.language_id=(%s)';

        $subQuery[] = 'SELECT language_id FROM `' . $this->languageTable . '`';
        $subQuery[] = 'WHERE code="' . $this->session->data['language'] . '"';

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
     * retrieve post details from $blogPostTable table.
     *
     * @param int $postId
     *
     * @return array|bool
     */
    public function getPostById($postId)
    {
        $queryString = $lanaguagesQuery = [];

        $queryString[] = 'SELECT * FROM `'. $this->blogPostTable .'` AS bp';
        $queryString[] = 'INNER JOIN `'. $this->blogPostDescriptionTable .'` AS bpd';
        $queryString[] = 'ON bp.post_id=bpd.post_id';
        $queryString[] = 'WHERE bp.post_id=' . $postId;
        $queryString[] = 'AND bpd.post_status=1';
        $queryString[] = 'AND bpd.language_id=(%s)';

        $lanaguagesQuery[] = 'SELECT language_id FROM `' . $this->languageTable . '`';
        $lanaguagesQuery[] = 'WHERE code="' . $this->session->data['language'] . '"';

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
     * update the visit post count column for specific post in specific language.
     *
     * @param int $postId
     * @param string $language
     *
     * @return void
     */
    public function updatePostVisits($postId, $language)
    {
        $queryString = [];
        $queryString[] = 'UPDATE `' . $this->blogPostDescriptionTable . '` SET';
        $queryString[] = 'visits=(visits+1)';
        $queryString[] = 'WHERE post_id=' . $postId;
        $queryString[] = 'AND language_id=(%s)';

        $subQuery = 'SELECT language_id FROM `' . $this->languageTable . '` WHERE code="' . $language . '"';

        $this->db->query(
            sprintf(
                implode(' ', $queryString),
                $subQuery
            )
        );
    }

    /**
     * get related blog posts using post tags.
     *
     * @param int $postId
     * @param string $tags
     *
     * @return array|bool
     */
    public function getRelatedPosts($postId, $tags, $languageId)
    {
        if ($tags == '') {
            return false;
        }

        $queryString = $where = [];
        $queryString[] = 'SELECT bpd.*, bp.*, lt.*, bc.name as category_name, bc.category_id, bpd.name as name FROM `'. $this->blogPostDescriptionTable .'` AS bpd';
        $queryString[] = 'LEFT JOIN `'. $this->blogPostTable .'` AS bp';
        $queryString[] = 'ON bpd.post_id=bp.post_id';
        $queryString[] = 'INNER JOIN `'. $this->blogCategoryDescriptionTable .'` AS bc';
        $queryString[] = 'ON bc.category_id=bp.parent_id';
        $queryString[] = 'INNER JOIN `'. $this->languageTable .'` AS lt';
        $queryString[] = 'ON bc.language_id=lt.language_id';
        $queryString[] = 'WHERE bpd.post_id!=' . $postId;
        $queryString[] = 'AND lt.code="' . $languageId . '"';

        $tags = explode(',', $tags);

        foreach ($tags as $tag) {
            $where[] = 'FIND_IN_SET("'.$tag.'", bpd.tags)';
        }

        if (empty($where) == false) {
            $queryString[] = 'AND (' . implode(' OR ', $where) . ')';
        }

        $data = $this->db->query(implode(' ', $queryString));

        if ($data->num_rows) {
            return $data->rows;
        }

        return false;
    }

    /**
     * Return the post id using the slug string.
     *
     * @param string $slug
     *
     * @return bool|int
     */
    public function getPostIdBySlug($slug)
    {
        $query = [];

        $query[] = 'SELECT post_id FROM ' . $this->blogPostDescriptionTable;
        $query[] = 'WHERE slug="' . $this->db->escape($slug) . '"';

        $data = $this->db->query(implode(' ', $query));

        if ($data->num_rows) {
            return $data->row['post_id'];
        }

        return false;
    }
    
    public function news($filter_category_id = null)
    {

        
        
        $search =  null;

        $start = 0;
        $length = 100;

        $columns = array(
            0 => 'post_id',
            1 => 'name',
            2 => 'description',
            3 => 'post_status',
            4 => 'content',
            5 => 'visits',
            6 => 'creation_date'
        );
        $orderColumn = 1;
        
        $orderType = 'asc';

        $return = $this->dtHandler($start, $length, $search, $orderColumn, $orderType, $filter_category_id );
        $records = $return['data'];
        $totalData = $return['total'];
        $totalFiltered = $return['totalFiltered'];



        foreach ($records as $index => $record)
        {
            if ( empty($record['name']) )
            {
                $records[$index]['name'] = $this->getAllPostNamesById($record['post_id']);
            }
        }

        return $records;
    }

    public function dtHandler($start = 0, $length = 10, $search = null, $orderColumn = "name", $orderType = "ASC", $filter_category_id = null)
    {
        $query = "SELECT bpd.*,
        
                    DATE_FORMAT(created_at, \"%e %M %Y\") creation_date 
        
                        FROM flash_blog_post AS bp 
                
                            INNER JOIN flash_blog_post_description AS bpd 
                  
                                ON bp.post_id = bpd.post_id AND bpd.language_id=" . (int)$this->config->get('config_language_id');
                //" LEFT JOIN flash_blog_category_description AS bcd ON bcd.category_id = bp.parent_id";
        $query .= " WHERE 1 ";

        $total = $this->db->query($query)->num_rows;
        
        if (!empty($search)) {
            
            $query .= " AND (bpd.name like '%" . $this->db->escape($search) . "%'
                            
                        OR bpd.description like '%" . $this->db->escape($search) . "%') ";
        }
        
        if($filter_category_id){
            
            $query .= " AND bp.parent_id = {$filter_category_id} ";
        }


        $totalFiltered = $this->db->query($query)->num_rows;
        
        $query .= " ORDER by {$orderColumn} {$orderType}";

        if ($length != -1) {
            $query .= " LIMIT " . $start . ", " . $length;
        }

        $data = array(
            'data' => $this->db->query($query)->rows,
            'total' => $total,
            'totalFiltered' => $totalFiltered
        );

        return $data;
    }
}
