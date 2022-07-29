<?php

class ModelTemplatesTemplate extends Model
{
    protected $templatesTable = 'ectemplate';

    /**
     * Get template using it's config name.
     *
     * @param string $configName
     *
     * @return array|bool
     */
    public function getTemplateByConfigName($configName)
    {
        $data = $this->db->query(vsprintf('SELECT * FROM %s WHERE CodeName="%s"', [
            $this->templatesTable,
            $configName
        ]));

        if ($data->num_rows) {
            return $data->row;
        }

        return false;
    }

    /**
     * Get the customized template using it's config name.
     *
     * @param string $configName
     *
     * @return array|bool
     */
    public function getCustomTemplateByConfigName($configName)
    {
        $query = [];

        $query[] = 'SELECT * FROM ' . $this->templatesTable;
        $query[] = 'WHERE CodeName="' . $configName . '" AND custom_template=1';

        $data = $this->db->query(implode(' ', $query));

        if ($data->num_rows) {
            return $data->row;
        }

        return false;
    }

    /**
     * Upgrade template version to a specific version
     *
     * @param int $templateId
     * @param float $version
     *
     * @return void
     */
    public function updateTemplateVersion($templateId, $version)
    {
        $query = 'UPDATE %s SET theme_version="%s" WHERE id=%d';
        $query = sprintf($query, $this->templatesTable, $version, $templateId);

        $this->db->query($query);
    }
}
