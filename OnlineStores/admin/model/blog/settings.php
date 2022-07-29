<?php

class ModelBlogSettings extends Model
{
    /**
     * The errors array.
     *
     * @var array
     */
    public $errors = [];

    /**
     * The main application setting group name.
     *
     * @var string
     */
    public $settingsIndex = 'flash_blog';

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
     * flash_blog_comment table name.
     *
     * @var string
     */
    private $blogCommentTable = DB_PREFIX . 'flash_blog_comment';

    /**
     * update the module settings.
     *
     * @param array $inputs
     *
     * @return bool
     */
    public function updateSettings($inputs)
    {
        $this->load->model('setting/setting');

        $this->model_setting_setting->editSetting(
            $this->settingsIndex, $inputs
        );

        return true;
    }

    /**
     * Install method that used to install the required application database tables.
     *
     * @return void
     */
    public function install()
    {
        $installQueries = [];
        $installQueries[] = 'CREATE TABLE IF NOT EXISTS `' . $this->blogPostTable . '` (
          `post_id` INT(11) NOT NULL AUTO_INCREMENT,
          `parent_id` INT(11) NOT NULL DEFAULT "0",
          `sort_order` INT(11) NOT NULL DEFAULT "1",
          `user_id` INT(11) NOT NULL,
          `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `modified_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`post_id`, `parent_id`, `user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';

        $installQueries[] = 'CREATE TABLE IF NOT EXISTS `' . $this->blogPostDescriptionTable . '` (
          `post_description_id` INT(11) NOT NULL AUTO_INCREMENT,
          `post_id` INT(11) NOT NULL,
          `language_id` INT(2) NOT NULL,
          `name` VARCHAR(255) NOT NULL,
          `slug` VARCHAR(255) NOT NULL,
          `description` TEXT NULL,
          `content` TEXT NULL,
          `post_status` INT(2) NOT NULL DEFAULT "0",
          `meta_description` VARCHAR(255) NULL,
          `meta_keywords` VARCHAR(255) NULL,
          `tags` TEXT NULL,
          `visits` INT(11) NOT NULL DEFAULT "0",
          PRIMARY KEY (`post_description_id`, `post_id`, `language_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';

        $installQueries[] = 'CREATE TABLE IF NOT EXISTS `' . $this->blogCategoryTable . '` (
          `category_id` INT(11) NOT NULL AUTO_INCREMENT,
          `parent_id` INT(11) NOT NULL DEFAULT "0",
          `sort_order` INT(11) NOT NULL DEFAULT "1",
          `category_status` INT(2) NOT NULL DEFAULT "0",
          `user_id` INT(11) NOT NULL,
          `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `modified_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`category_id`, `parent_id`, `user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';

        $installQueries[] = 'CREATE TABLE IF NOT EXISTS `' . $this->blogCategoryDescriptionTable . '` (
          `blog_description_id` INT(11) NOT NULL AUTO_INCREMENT,
          `category_id` INT(11) NOT NULL,
          `language_id` INT(2) NOT NULL,
          `name` VARCHAR(255) NOT NULL,
          `slug` VARCHAR(255) NOT NULL,
          `description` TEXT NULL,
          `meta_description` VARCHAR(255) NULL,
          `meta_keywords` VARCHAR(255) NULL,
          `tags` TEXT NULL,
          PRIMARY KEY (`blog_description_id`, `category_id`, `language_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';

        $installQueries[] = 'CREATE TABLE IF NOT EXISTS `' . $this->blogCommentTable . '` (
          `comment_id` INT(11) NOT NULL AUTO_INCREMENT,
          `post_id` INT(11) NOT NULL DEFAULT "0",
          `post_description_id` INT(11) NOT NULL DEFAULT "0",
          `email` VARCHAR(255) NOT NULL,
          `name` VARCHAR(255) NULL,
          `comment` TEXT NOT NULL,
          `comment_status` INT(2) NOT NULL DEFAULT "0",
          `customer_id` INT(11) NOT NULL DEFAULT "0",
          `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`comment_id`, `post_id`, `customer_id`, `post_description_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';

        foreach ($installQueries as $installQuery) {
            $this->db->query($installQuery);
        }
    }

    /**
     * Uninstall method that used to remove and the clean up.
     *
     * @return void
     */
    public function uninstall()
    {
        $uninstallQueries = [];
        $uninstallQueries[] = 'DROP TABLE IF EXISTS `' . $this->blogPostTable . '`';
        $uninstallQueries[] = 'DROP TABLE IF EXISTS `' . $this->blogPostDescriptionTable . '`';
        $uninstallQueries[] = 'DROP TABLE IF EXISTS `' . $this->blogCategoryTable . '`';
        $uninstallQueries[] = 'DROP TABLE IF EXISTS `' . $this->blogCategoryDescriptionTable . '`';
        $uninstallQueries[] = 'DROP TABLE IF EXISTS `' . $this->blogCommentTable . '`';

        foreach ($uninstallQueries as $uninstallQuery) {
            $this->db->query($uninstallQuery);
        }
    }

    /**
     * validate the form inputs.
     *
     * @param array $data
     *
     * @return bool
     */
    // public function validate($data)
    // {
    //     if (!isset($data['status']) || (!in_array($data['status'], [0, 1]))) {
    //         $this->errors['status'] = $this->language->get('error_status_field');
    //     }

    //     if (
    //         empty($data['maximum_index_blogs']) == false &&
    //         preg_match('#[0-9]+$#', $data['maximum_index_blogs']) == false
    //     ) {
    //         $this->errors['maximum_index_blogs'] = $this->language->get('error_maxmium_blogs_field');
    //     }

    //     if ( empty( $data['maximum_index_categories'] ) )
    //     {
    //         $this->errors['maximum_index_categories'] = $this->language->get('error_maxmium_categories_field');
    //     }

    //     if ( ! preg_match('#^[0-9]+$#', $data['maximum_index_categories'] ) )
    //     {
    //         $this->errors['maximum_index_categories'] = $this->language->get('error_maxmium_categories_field');
    //     }

    //     return $this->errors ? false : true;
    // }
}
