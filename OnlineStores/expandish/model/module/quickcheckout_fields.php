<?php
class ModelModuleQuickcheckoutFields extends Model {
    /**
     * get field options
     * @param  int $id field id
     * @return array
     */
    public function getOptionValueDescriptions($id) {
        $option_value_data = array();
        $language_id = $this->config->get('config_language_id');

        $option_value_query = $this->db->query("SELECT fod.title FROM " . DB_PREFIX . "qchkout_field_options fo
                                                LEFT JOIN " . DB_PREFIX . "qchkout_field_options_description fod ON (fod.field_option_id = fo.id)
                                                WHERE fo.field_id = '" . (int)$id . "' AND fod.language_id = ".$language_id);

        return $option_value_query->rows;
    }

    /**
     * Fetches all fields for checkout section
     * @return array returns all fields
     */
    public function getFields() {
        $query = $fields = [];
        $language_id = $this->config->get('config_language_id');

        $fields = [
            'qf.*',
            'qfd.field_title',
            'qfd.field_error',
            'qfd.field_tooltip'
        ];

        $fields = sprintf(
            implode(',', $fields)
        );

        $query[] = 'SELECT ' . $fields . ' FROM `' . DB_PREFIX . 'qchkout_fields` qf';
        $query[] = 'LEFT JOIN `' . DB_PREFIX . 'qchkout_fields_description` qfd ON (qf.id = qfd.field_id)';

        $query[] = 'WHERE qfd.language_id = '.(int)$language_id;

        $sort_data = array(
            'field_title'
        );

        $query[] = " ORDER BY qfd.field_title";

        $query = $this->db->query(implode(' ', $query));

        return $query->rows ?? [];
    }

    /**
     * Update order custom fields data
     * @param array
     */
    public function updateOrderCustomFields($order_id, $fields){
        $this->db->query("DELETE FROM " . DB_PREFIX . "order_custom_fields WHERE order_id = '" . (int)$order_id . "'");

        $values = [];
        foreach ($fields as $record){
            $this->db->query("
                INSERT INTO " . DB_PREFIX . "order_custom_fields SET
                field_id = '" . (int)$record['field_id'] . "',
                order_id = '" . (int)$record['order_id'] . "',
                `section` = '" . $this->db->escape($record['section']) . "',
                `value` = '" . $this->db->escape($record['value']) . "'
			");
        }
    }

    /**
     * Get custom fields of an order
     * @param  int  $order_id
     * @return array
     */
    public function getOrderCustomFields($order_id, $field_id_as_key = false) {
        $field_data = array();
        $lang_id = $this->config->get('config_language_id') ? (int)$this->config->get('config_language_id') : 1;

        $query = $this->db->query("SELECT ocf.value, ocf.section, qfd.field_title, qf.field_type , ocf.field_id FROM " . DB_PREFIX . "order_custom_fields ocf
                                   LEFT JOIN " . DB_PREFIX . "qchkout_fields qf ON (ocf.field_id = qf.id)
                                   LEFT JOIN " . DB_PREFIX . "qchkout_fields_description qfd ON (ocf.field_id = qfd.field_id)
                                   WHERE order_id = '" . (int)$order_id . "'
                                   AND qfd.language_id = ".$lang_id);

        if(!$query->num_rows){
            return false;
        }

        $skipTypes = ['label'];

        foreach ($query->rows as $field){
            if($field['field_type'] == 'checkbox')
                $field['value'] = ($field['value']) ? 'Yes' : 'No';

            if(in_array($field['field_type'], $skipTypes))
                continue;

            if( $field_id_as_key )
                $field_data[$field['section']][$field['field_id']] = $field;
            else
                $field_data[$field['section']][] = $field;
        }

        return $field_data;
    }
}
