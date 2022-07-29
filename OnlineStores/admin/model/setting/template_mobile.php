<?php

class ModelSettingTemplateMobile extends Model
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

        $fields[] = 'ectemplate_mobile.*';
        $fields[] = 'ectemplatedesc_mobile.`name`';
        $fields[] = 'ectemplatedesc_mobile.description';
        $fields[] = 'ectemplatedesc_mobile.image';

        $query[] = 'SELECT ' . implode(', ', $fields) . ' FROM ectemplate_mobile';
        $query[] = 'JOIN ectemplatedesc_mobile ON ectemplate_mobile.id=ectemplatedesc_mobile.ectemplate_mobile_id';
        $query[] = 'WHERE ectemplatedesc_mobile.lang = "' . $languageId . '"';

        if (isset($filter['categories'])) {
            $categories = $filter['categories'];

            foreach ($categories as $category) {
                $query[] = " AND ectemplate_mobile.category LIKE '%" . $this->ecusersdb->escape($category) . "%'";
            }
        }

        $total_users = $this->ecusersdb->query(str_replace(implode(', ', $fields), 'COUNT(*) as _t', implode(' ', $query)))->row;
        $total_local = $this->db->query(str_replace(implode(', ', $fields), 'COUNT(*) as _t', implode(' ', $query)))->row;
        $total       = $total_users + $total_local;

        if (isset($filter['limit']) && isset($filter['offset'])) {
            $query[] = "LIMIT {$filter['offset']}, {$filter['limit']}";
        }

        $data_users = $this->ecusersdb->query(implode(' ', $query));
        $data_local = $this->db->query(implode(' ', $query));

        $data = [];
        $data = array_merge($data, $data_users->rows, $data_local->rows);
        foreach ($data as &$tmplt){
            $tmplt_images = glob(DIR_ONLINESTORES.'image/templates/mobile/'.$tmplt['code_name'].'/*.png');
            foreach($tmplt_images as $image)
            {
                $tmplt['images'][] = str_replace(DIR_ONLINESTORES, HTTP_CATALOG, $image);
            }
        }

        if (count($data)) {
            return [
                'data' => array_column($data, null, 'code_name'),
                'total' => $total['_t']
            ];
        }

        return false;
    }
}