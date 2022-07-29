<?php

class ModelTemplatesPreview extends ModelTemplatesTemplate
{
    /**
     * Check if template is exists or not.
     *
     * @param int $id
     *
     * @return bool
     */
    public function temporaryTemplateExists($id)
    {
        $data = $this->db->query(vsprintf('SELECT 1 FROM %s WHERE TemplateId=%d', [
            'ecpage',
            $id
        ]));

        if (isset($data->num_rows) && $data->num_rows > 1) {
            return true;
        }

        return false;
    }

    /**
     * Apply template file to preview.
     *
     * @param array $template
     *
     * @return void
     * @throws \Exception
     */
    public function applyTemplateForPreview($template)
    {
        if ($template['custom_template'] == '0') {

            $setting = $this->load->model('setting/setting', ['return' => true]);

            $setting->applyNextGenTemplateForPreview($template['CodeName']);

        } else if ($template['custom_template'] == '1') {
            throw new \Exception('error');
        } else {
            throw new \Exception('error');
        }
    }
}
