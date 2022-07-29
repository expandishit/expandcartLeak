<?php


use ExpandCart\Foundation\Support\Facades\Slugify;


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
     * get category posts.
     *
     * @param $categoryId
     *
     * @return array|bool
     */
    public function getPostsByCategoryId($categoryId)
    {

        $queryString = $lanaguagesQuery = [];
        $queryString[] = 'SELECT * FROM `'. $this->blogPostTable .'` AS bp';
        $queryString[] = 'INNER JOIN `'. $this->blogPostDescriptionTable .'` AS bpd';
        $queryString[] = 'ON bp.post_id=bpd.post_id';
        $queryString[] = 'WHERE bp.parent_id=' . $categoryId;
//        $queryString[] = 'AND bpd.language_id=(%s)';

        $lanaguagesQuery[] = 'SELECT language_id FROM `' . $this->languageTable . '`';
        $lanaguagesQuery[] = 'WHERE code="' . $this->config->get('config_admin_language') . '"';

        $data = $this->db->query(implode(' ', $queryString));
        $language = $this->db->query(implode(' ', $lanaguagesQuery))->row;

        $allPosts = [];

        foreach ($data->rows as $row) {
            $allPosts[$row['post_id']][$row['language_id']] = $row;
        }

        $posts = [];

        $allPosts = array_map(function ($value) use($language, &$posts) {
            if (isset($value[$language['language_id']])) {
                $posts[] = $value[$language['language_id']];
            } else {
                $posts[] = $value[1];
            }
        }, $allPosts);


        if (empty($posts) === false) {
            return $posts;
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
        $queryString = [];

        $queryString[] = 'SELECT * FROM `'. $this->blogPostTable .'`';
        $queryString[] = 'WHERE post_id=' . $postId;

        $data = $this->db->query(implode(' ', $queryString));

        if ($data->num_rows) {
            return $data->row;
        }

        return false;
    }

    /**
     * retrieve post descriptions from $blogPostDescriptionTable table.
     *
     * @param int $postId
     *
     * @return array|bool
     */
    public function getPostDescriptionByPostId($postId)
    {
        $queryString = [];

        $queryString[] = 'SELECT * FROM `'. $this->blogPostDescriptionTable .'`';
        $queryString[] = 'WHERE post_id=' . $postId;

        $data = $this->db->query(implode(' ', $queryString));

        if ($data->num_rows) {
            return array_column($data->rows, null, 'language_id');
        }

        return false;
    }

    /**
     * retrieve post descriptions from $blogPostDescriptionTable table by post and language id.
     *
     * @param int $postId
     * @param int $languageId
     *
     * @return array|bool
     */
    public function getPostDescriptionByPostIdAndLanguageId($postId, $languageId)
    {
        $queryString = [];

        $queryString[] = 'SELECT * FROM `'. $this->blogPostDescriptionTable .'`';
        $queryString[] = 'WHERE post_id=' . $postId;
        $queryString[] = 'AND language_id=' . $languageId;

        $data = $this->db->query(implode(' ', $queryString));

        if ($data->num_rows) {
            return $data->row;
        }

        return false;
    }

    /**
     * trim tags.
     *
     * @param string $tags
     *
     * @return string
     */
    private function trimTags($tags)
    {
        return implode(',', array_map('trim', explode(',', $tags)));
    }

    /**
     * insert new entry to $blogPostTable table.
     *
     * @param array $category
     *
     * @return int
     */
    public function insertPost($post)
    {
        $queryString = $fields = [];

        $queryString[] = 'INSERT INTO `'. $this->blogPostTable .'` SET';

        $fields[] = 'parent_id=' . $this->db->escape($post['parent_id']);
        $fields[] = 'sort_order=' . $this->db->escape($post['sort_order']);
        $fields[] = 'user_id=' . $this->db->escape($this->user->getId());
        $fields[] = 'post_image="' . $this->db->escape($post['post_image']) . '"';

        $queryString[] = implode(', ', $fields);

        $this->db->query(implode(' ', $queryString));

        return $this->db->getLastId();
    }

    /**
     * insert new entry to $blogPostDescriptionTable table.
     *
     * @param int $postId
     * @param array $postDescription
     *
     * @return void
     */
    public function insertPostDescription($postId, $postDescription)
    {
        foreach ($postDescription as $languageId => $description) {

            $queryString = $fields = [];

            $queryString[] = 'INSERT INTO `'. $this->blogPostDescriptionTable .'` SET';
            $fields[] = 'post_id=' . $postId;
            $fields[] = 'language_id=' . $languageId;
            $fields[] = 'name="' . Slugify::trim($this->db->escape($description['name'])) . '"';
            $fields[] = 'slug="' . Slugify::slug($description['name']) . '"';
            $fields[] = 'description="' . Slugify::trim($this->db->escape($description['description'])) . '"';
            $fields[] = 'content="' . Slugify::trim($this->db->escape($description['content'])) . '"';
            $fields[] = 'post_status="' . $this->db->escape($description['post_status']) . '"';
            $fields[] = 'meta_description="' . Slugify::trim($this->db->escape($description['meta_description'])) . '"';
            $fields[] = 'meta_keywords="' . Slugify::trim($this->db->escape($description['meta_keywords'])) . '"';
            $fields[] = 'tags="' . $this->trimTags($this->db->escape($description['tags'])) . '"';

            $queryString[] = implode(', ', $fields);

            $this->db->query(implode(' ', $queryString));
        }
    }

    /**
     * update $blogPostTable table using post id.
     *
     * @param int $postId
     * @param array $post
     *
     * @return void
     */
    public function updatePost($postId, $post)
    {
        $queryString = $fields = [];

        $queryString[] = 'UPDATE `'. $this->blogPostTable .'` SET';

        $fields[] = 'parent_id=' . (int)$post['parent_id'];
        $fields[] = 'sort_order=' . (int)$post['sort_order'];
        $fields[] = 'post_image="' .  $this->db->escape($post['post_image']) . '"';

        $queryString[] = implode(', ', $fields);
        $queryString[] = 'WHERE post_id=' . (int)$postId;

        $this->db->query(implode(' ', $queryString));
    }

    /**
     * update $blogPostDescriptionTable table using post id.
     *
     * @param int $postId
     * @param array $postDescription
     *
     * @return void
     */
    public function updatePostDescription($postId, $postDescription)
    {
        foreach ($postDescription as $languageId => $description) {

            if (!$this->getPostDescriptionByPostIdAndLanguageId($postId, $languageId)) {
                $this->insertPostDescription(
                    $postId, [$languageId => $postDescription[$languageId]]
                );
                continue;
            }

            $queryString = $fields = [];

            $queryString[] = 'UPDATE `'. $this->blogPostDescriptionTable .'` SET';
            $fields[] = 'name="' . Slugify::trim($this->db->escape($description['name'])) . '"';
            $fields[] = 'slug="' . Slugify::slug($description['name']) . '"';
            $fields[] = 'description="' . Slugify::trim($this->db->escape($description['description'])) . '"';
            $fields[] = 'content="' . Slugify::trim($this->db->escape($description['content'])) . '"';
            $fields[] = 'post_status="' . Slugify::trim($this->db->escape($description['post_status'])) . '"';
            $fields[] = 'meta_description="' . Slugify::trim($this->db->escape($description['meta_description'])) . '"';
            $fields[] = 'meta_keywords="' . Slugify::trim($this->db->escape($description['meta_keywords'])) . '"';
            $fields[] = 'tags="' . $this->trimTags($this->db->escape($description['tags'])) . '"';

            $queryString[] = implode(', ', $fields);
            $queryString[] = 'WHERE post_id=' . $postId;
            $queryString[] = 'AND language_id=' . $languageId;

            $this->db->query(implode(' ', $queryString));
        }
    }

    /**
     * delete row from $blogPostTable.
     *
     * @param int $postId
     *
     * @return void
     */
    public function deletePost($postId)
    {
        $this->db->query('DELETE FROM `' . $this->blogPostTable . '` WHERE post_id=' . $postId);
    }

    /**
     * delete row from $blogPostDescriptionTable.
     *
     * @param int $postId
     *
     * @return void
     */
    public function deletePostDescription($postId)
    {
        $this->db->query(
            'DELETE FROM `' . $this->blogPostDescriptionTable . '` WHERE post_id=' . $postId
        );
    }


    public function dtHandler($start = 0, $length = 10, $search = null, $orderColumn = "name", $orderType = "ASC")
    {
        $query = "SELECT bpd.*,DATE_FORMAT(created_at, \"%e %M %Y\") creation_date FROM flash_blog_post AS bp 
                  INNER JOIN flash_blog_post_description AS bpd ON bp.post_id = bpd.post_id AND bpd.language_id=" . (int)$this->config->get('config_language_id');
                //" LEFT JOIN flash_blog_category_description AS bcd ON bcd.category_id = bp.parent_id";

        $total = $this->db->query($query)->num_rows;
        $where = "";
        if (!empty($search)) {
            $where .= "(bpd.name like '%" . $this->db->escape($search) . "%'
                        OR bpd.description like '%" . $this->db->escape($search) . "%') ";
            $query .= " WHERE " . $where;
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

    public function dtUpdateStatus($postDescId, $status)
    {
        $query = [];
        $query[] = 'UPDATE ' . $this->blogPostDescriptionTable . ' SET';
        $query[] = 'post_status="' . $this->db->escape($status) . '"';
        $query[] = 'WHERE post_description_id=' . $this->db->escape($postDescId);

        $this->db->query(implode(' ', $query));
    }


    public function getAllPostNamesById(int $post_id)
    {
        $sql = "SELECT name FROM flash_blog_post_description WHERE post_id={$post_id} AND name != '';";
        $query = $this->db->query($sql);

        if ( empty($query->rows) )
        {
            return '( No Title )';
        }
        
        return $query->rows[0]['name'];
    }

}

?>