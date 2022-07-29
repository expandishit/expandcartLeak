<?php

class ModelSettingTemplate extends Model
{
    /**
     * Get all templates
     *
     * @param array $filter
     * @param int $languageId
     *
     * @return array|bool
     */
    public function getTemplates($filter, $languageId)
    {
        $query = $fields = [];

        $fields[] = 'ectemplate.*';
        $fields[] = 'ectemplatedesc.`Name`';
        $fields[] = 'ectemplatedesc.Description';
        $fields[] = 'ectemplatedesc.Image';
        $fields[] = 'ectemplatedesc.Demourl';

        $query[] = 'SELECT ' . implode(', ', $fields) . ' FROM ectemplate';
        $query[] = 'JOIN ectemplatedesc ON ectemplate.id=ectemplatedesc.TemplateId';
        $query[] = 'WHERE ectemplatedesc.Lang = "' . $languageId . '"';
        $query[] = 'AND NextGenTemplate="1"';
        $query[] = 'AND external_template_id IS NULL';

        if (isset($filter['categories'])) {
            $categories = $filter['categories'];

            foreach ($categories as $category) {
                $query[] = " AND category LIKE '%" . $this->db->escape($category) . "%'";
            }
        }

        $total = $this->db->query(str_replace(implode(', ', $fields), 'COUNT(*) as _t', implode(' ', $query)))->row;

        if (isset($filter['limit']) && isset($filter['offset'])) {
            $query[] = "LIMIT {$filter['offset']}, {$filter['limit']}";
        }

        $data = $this->db->query(implode(' ', $query));

        if ($data->num_rows) {
            return [
                'data' => array_column($data->rows, null, 'CodeName'),
                'total' => $total['_t']
            ];
        }

        return false;
    }

    /**
     * Get installed external templates
     *
     * @return array
     */
    public function getInstalledExternalTemplates()
    {
        $data = $this->db->query('SELECT * FROM ectemplate WHERE external_template_id IS NOT NULL');

        return array_column($data->rows, null, 'external_template_id');
    }


    public function getTemplateInfo($template_code)
    {
        return $this->db->query("SELECT * FROM ectemplate JOIN ectemplatedesc ON ectemplate.id=ectemplatedesc.TemplateId WHERE ectemplate.CodeName = '".$this->db->escape($template_code)  ."' AND ectemplatedesc.Lang = '".$this->db->escape('en'). "'")->row;

    }
}