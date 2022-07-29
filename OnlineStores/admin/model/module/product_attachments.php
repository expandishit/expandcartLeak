<?php

class ModelModuleProductAttachments extends Model
{

    /**
     * update the module settings.
     *
     * @param array $inputs
     *
     * @return bool
     */
    public function updateSettings($inputs)
    {
    }

    public function getSettings()
    {
        return $this->config->get('product_attachments');
    }

    /**
     * Check if app installed
     *
     * @return boolean
     */
    public function isInstalled()
    {
        return \Extension::isInstalled('product_attachments');
    }

    /**
     * Check is App Active
     *
     * @return boolean
     */
    public function isActive()
    {
        return $this->isInstalled() && (int) $this->getSettings()['status'] === 1;
    }

    /**
     *   Install the required values for the application.
     *
     *   @return void whether successful or not.
     */
    public function install()
    {

        $query = "INSERT INTO `setting` (`store_id`, `group`, `key`, `value`, `serialized`) VALUES
            (0, 'product_attachments', 'product_attachments', 'a:1:{s:6:\"status\";s:1:\"1\";}', 1);";
        $this->db->query($query);

        $query = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."download` (
          `download_id` int(11) NOT NULL AUTO_INCREMENT,
          `filename` varchar(128) NOT NULL,
          `mask` varchar(128) NOT NULL,
          `extension` varchar(30) DEFAULT NULL,
          `remaining` int(11) NOT NULL DEFAULT 0,
          `date_added` datetime NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`download_id`)
        );";
        $this->db->query($query);

        $query = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."download_description` (
          `download_id` int(11) NOT NULL,
          `language_id` int(11) NOT NULL,
          `name` varchar(64) NOT NULL,
          PRIMARY KEY (`download_id`,`language_id`)
        );";
        $this->db->query($query);

        $query = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."product_to_download` (
          `product_id` int(11) NOT NULL,
          `download_id` int(11) NOT NULL,
          PRIMARY KEY (`product_id`,`download_id`)
        );";
        $this->db->query($query);
    }

    /**
     *   Remove the values from the database.
     *
     *   @return void whether successful or not.
     */
    public function uninstall()
    {
        $query = "DROP TABLE IF EXISTS `" .DB_PREFIX. "download`";
        $this->db->query($query);

        $query = "DROP TABLE IF EXISTS `" .DB_PREFIX. "download_description`";
        $this->db->query($query);

        $query = "DROP TABLE IF EXISTS `" .DB_PREFIX. "product_to_download`";
        $this->db->query($query);
    }
}
