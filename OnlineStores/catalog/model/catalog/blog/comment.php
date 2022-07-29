<?php

class ModelCatalogBlogComment extends Model
{
    /**
     * flash_blog_comment table name.
     *
     * @var string
     */
    private $blogCommentTable = DB_PREFIX . 'flash_blog_comment';

    /**
     * flash_blog_comment table name.
     *
     * @var string
     */
    private $customerTable = DB_PREFIX . 'customer';

    /**
     * errors array.
     *
     * @var array
     */
    public $errors = [];

    /**
     * validate comment inputs.
     *
     * @param array $inputs
     *
     * @return bool
     */
    public function validate($inputs)
    {
        if (!filter_var($inputs['email'], FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = $this->language->get('error_invalid_email_address');
        }

        if (!isset($inputs['customer_id']) || preg_match('#^[0-9]+$#', $inputs['customer_id']) === false) {
            $this->errors[] = $this->language->get('error_invalid_customer_id');
        }

        if (!isset($inputs['comment']) || strlen($inputs['comment']) === 0) {
            $this->errors[] = $this->language->get('error_invalid_comment');
        }

        if (empty($this->errors)) {
            return true;
        }

        return false;
    }

    /**
     * insert new comment.
     *
     * @param int $postId
     * @param array $data
     *
     * @return void
     */
    public function storeComment($postId, $data)
    {
        $queryString = $fields = [];

        $queryString[] = 'INSERT INTO `' . $this->blogCommentTable . '` SET';

        $fields[] = 'name="' . $this->db->escape($data['name']) . '"';
        $fields[] = 'email="' . $this->db->escape($data['email']) . '"';
        $fields[] = 'customer_id="' . $data['customer_id'] . '"';
        $fields[] = 'comment="' . $this->db->escape($data['comment']) . '"';
        $fields[] = 'comment_status="' . $data['comment_status'] . '"';
        $fields[] = 'post_id="' . $postId . '"';
        $fields[] = 'post_description_id="' . $data['post_description_id'] . '"';

        $queryString[] = implode(', ', $fields);

        $this->db->query(implode(' ', $queryString));
    }

    /**
     * retrieve comments that are belongs to specific post.
     *
     * @param int $postId
     * @param int $postDescriptionId
     *
     * @return array|bool
     */
    public function getCommentsByPostId($postId, $postDescriptionId)
    {
        $queryString = [];

        $queryString[] = 'SELECT * FROM `' . $this->blogCommentTable . '`';
        $queryString[] = 'WHERE post_id=' . $postId;
        $queryString[] = 'AND post_description_id=' . $postDescriptionId;
        $queryString[] = 'AND comment_status=1';

        $data = $this->db->query(implode(' ', $queryString));

        if ($data->num_rows) {
            return $data->rows;
        }

        return false;
    }
}
