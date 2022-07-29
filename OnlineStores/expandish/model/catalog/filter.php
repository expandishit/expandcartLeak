<?php
class ModelCatalogFilter extends Model
{

    public function getFilter($filter_id)
    {
        $query = $this->db->query("SELECT *, (SELECT name FROM " . DB_PREFIX . "filter_group_description fgd WHERE f.filter_group_id = fgd.filter_group_id AND fgd.language_id = '" . (int) $this->config->get('config_language_id') . "') AS `group` FROM " . DB_PREFIX . "filter f LEFT JOIN " . DB_PREFIX . "filter_description fd ON (f.filter_id = fd.filter_id) WHERE f.filter_id = '" . (int) $filter_id . "' AND fd.language_id = '" . (int) $this->config->get('config_language_id') . "'");

        return $query->row;
    }
}
