<?php

class ModelBlogComment extends Model
{
    /**
     * flash_blog_comment table name.
     *
     * @var string
     */
    private $blogCommentsTable = DB_PREFIX . 'flash_blog_comment';

    /**
     * get all comments that belongs to specific post.
     *
     * @param int $postId
     *
     * @return array|bool
     */
    public function getCommentsByPostId($postId)
    {
        $queryString = [];

        $queryString[] = 'SELECT * FROM `' . $this->blogCommentsTable . '`';
        $queryString[] = 'WHERE post_id=' . $postId;

        $data = $this->db->query(implode(' ', $queryString));

        if ($data->num_rows) {
            return $data->rows;
        }

        return false;
    }

    /**
     * get comment details by comment id.
     *
     * @param int $commentId
     *
     * @return array|bool
     */
    public function getCommentById($commentId)
    {
        $queryString = [];

        $queryString[] = 'SELECT * FROM `' . $this->blogCommentsTable . '`';
        $queryString[] = 'WHERE comment_id=' . $commentId;

        $data = $this->db->query(implode(' ', $queryString));

        if ($data->num_rows) {
            return $data->rows;
        }

        return false;
    }
    /**
     * update $blogCommentsTable table using post id.
     *
     * @param int $commentId
     * @param array $comment
     *
     * @return void
     */
    public function updateComment($commentId, $comment)
    {
        $queryString = $fields = [];
        $queryString[] = 'UPDATE `'. $this->blogCommentsTable .'` SET';
        $fields[] = 'name=' . '"'.$this->db->escape($comment['name']).'"';
        $fields[] = 'email=' .'"'. $this->db->escape($comment['email']).'"';
        $fields[] = 'comment="' . $this->db->escape($comment['comment']) . '"';
        $queryString[] = implode(', ', $fields);
        $queryString[] = 'WHERE comment_id=' . $commentId;
        $this->db->query(implode(' ', $queryString));
    }

    /**
     * update comment status using comment id.
     *
     * @param int $commentId
     * @param int $commentStatus
     *
     * @return void
     */
    public function updateCommentStatus($commentId, $commentStatus)
    {
        $queryString = [];

        $queryString[] = 'UPDATE `' . $this->blogCommentsTable . '` SET ';
        $queryString[] = 'comment_status=' . $commentStatus;
        $queryString[] = 'WHERE comment_id=' . $commentId;

        $this->db->query(implode(' ', $queryString));
    }

    /**
     * delete comment using comment id.
     *
     * @param int $commentId
     *
     * @return void
     */
    public function deleteCommentById($commentId)
    {
        $queryString = [];

        $queryString[] = 'DELETE FROM `' . $this->blogCommentsTable . '`';
        $queryString[] = 'WHERE comment_id=' . $commentId;

        $this->db->query(implode(' ', $queryString));
    }

    /**
     * delete all comments that belongs to a specific post.
     *
     * @param int $commentId
     *
     * @return void
     */
    public function deleteCommentByPostId($postId)
    {
        $queryString = [];

        $queryString[] = 'DELETE FROM `' . $this->blogCommentsTable . '`';
        $queryString[] = 'WHERE post_id=' . $postId;

        $this->db->query(implode(' ', $queryString));
    }

    public function dtHandler($start = 0, $length = 10, $search = null, $orderColumn = "name", $orderType = "ASC")
    {
        $query = "SELECT bc.*, DATE_FORMAT(created_at, \"%e %M %Y\") creation_date FROM flash_blog_comment AS bc";

        $total = $this->db->query($query)->num_rows;
        $where = "";
        if (!empty($search)) {
            $where .= "(bc.name like '%" . $this->db->escape($search) . "%'
                        OR bc.email like '%" . $this->db->escape($search) . "%'
                        OR bc.comment like '%" . $this->db->escape($search) . "%') ";
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
}
