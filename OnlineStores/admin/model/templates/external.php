<?php

class ModelTemplatesExternal extends Model
{
    /**
     * the templates name, related to database.
     *
     * @var string
     */
    protected $templatesTable = '`' . DB_PREFIX . 'ectemplate`';

    /**
     * the templates description, related to database.
     *
     * @var string
     */
    protected $templatesDescriptionTable = '`' . DB_PREFIX . 'ectemplatedesc`';

    /**
     * the base directory path.
     *
     * @var string
     */
    // public $baseDir = DIR_CATALOG . 'view/custom/' . STORECODE . '/';
    public $baseDir = DIR_CUSTOM_TEMPLATE;

    /**
     * the temporary directory path.
     *
     * @var string
     */
    // public $tmpDir = DIR_CATALOG . 'view/custom/tmp/';
    public $tmpDir = TEMP_DIR_PATH;

    /**
     * Errors array.
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Main template name.
     *
     * @var string
     */
    protected $templateName;

    /**
     * Populate a new error in the errors array.
     *
     * @param mixed @error
     *
     * @return ModelTemplatesTemplate
     */
    public function setError($error)
    {
        $this->errors[] = $error;

        return $this;
    }

    /**
     * Get all the errors.
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Validate the template name to check if it is valid name.
     *
     * @param string $templateName
     *
     * @return bool
     */
    public function validateTemplateName($templateName)
    {
        return \preg_match('#^[a-z][a-z0-9\_]+$#i', $templateName);
    }

    /**
     * Checks if the template name is alreaddy installed in the user instance.
     *
     * @param string $templateName
     *
     * @return bool
     */
    public function checkTemplate($templateName)
    {
        $query = [];

        $query[] = 'SELECT id FROM ' . $this->templatesTable;
        $query[] = 'WHERE CodeName="' . $templateName . '"';

        $data = $this->ecusersdb->query(implode(' ', $query));

        if ($data->num_rows == 0) {
            return true;
        }

        throw new \Exception("The template name is already exists, kindley choose another name");
    }

    /**
     * Get the customized template using it's id.
     *
     * @param int $templateId
     *
     * @return array|bool
     */
    public function getTemplateById($templateId)
    {
        $query = [];

        $query[] = 'SELECT * FROM ' . $this->templatesTable;
        $query[] = 'WHERE id="' . $templateId . '" AND custom_template=1';

        $data = $this->ecusersdb->query(implode(' ', $query));

        if ($data->num_rows) {
            return $data->row;
        }

        return false;
    }

    /**
     * Get the external template using template code.
     *
     * @param string $templateCode
     *
     * @return array|bool
     */
    public function getTemplateByCode(string $templateCode)
    {
        $query = [];

        $query[] = 'SELECT * FROM ' . $this->templatesTable;
        $query[] = 'WHERE CodeName="' . $templateCode . '" AND custom_template=1';

        $data = $this->ecusersdb->query(implode(' ', $query));

        if ($data->num_rows) {
            return $data->row;
        }

        return false;
    }

    /**
     * Get all templates published externally by expandcart
     *
     * @param array $filter
     * @param string $language
     *
     * @return array|bool
     */
    public function getTemplates($filter, $language)
    {
        $query = $fields = [];

        $fields[] = 'ectemplate.*';
        $fields[] = 'ectemplatedesc.`Name`';
        $fields[] = 'ectemplatedesc.Description';
        $fields[] = 'ectemplatedesc.Image';
        $fields[] = 'ectemplatedesc.Demourl';
        $fields[] = '"external" as template_source';
        if (is_array($filter['installed']) && count($filter['installed']) > 0) {
            $fields[] = sprintf(
                'IF (ectemplate.id IN (%s), "1", null) as is_installed', implode(',', $filter['installed'])
            );
        } else {
            $fields[] = '"0" as is_installed';
        }

        $query[] = 'SELECT ' . implode(', ', $fields) . ' FROM ectemplate';
        $query[] = 'JOIN ectemplatedesc ON ectemplate.id=ectemplatedesc.TemplateId';
        $query[] = 'WHERE ectemplatedesc.Lang = "' . $language . '"';
        $query[] = 'AND NextGenTemplate="1"';

        if (isset($filter['categories'])) {
            $categories = $filter['categories'];

            foreach ($categories as $category) {
                $query[] = " AND category LIKE '%" . $this->db->escape($category) . "%'";
            }
        }

        if (is_array($filter['installed']) && count($filter['installed']) > 0) {
            // $query[] = "AND ectemplate.id NOT IN (" . implode(',', $filter['installed']) . ")";
        }

        $total = $this->ecusersdb->query(
            str_replace(implode(', ', $fields), 'COUNT(*) as _t', implode(' ', $query))
        )->row;

        if (isset($filter['limit']) && isset($filter['offset'])) {
            $query[] = "LIMIT {$filter['offset']}, {$filter['limit']}";
        }

        $data = $this->ecusersdb->query(implode(' ', $query));

        if ($data->num_rows) {
            return [
                'data' => array_column($data->rows, null, 'CodeName'),
                'total' => $total['_t']
            ];
        }

        return ['data' => [], 'total' => 0];
    }
}
