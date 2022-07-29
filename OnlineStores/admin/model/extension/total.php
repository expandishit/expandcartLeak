<?php

class ModelExtensionTotal extends Model
{
    /**
     * The extension table name.
     *
     * @var string
     */
    private $extensionTable = 'extension';

    /**
     * Get all installed extensions.
     *
     * @return array|bool
     */
    public function getInstalledExtensions()
    {
        $data = $this->db->query('SELECT * FROM `' . $this->extensionTable . '` where `type`="total"');

        if ($data->num_rows) {
            return $data->rows;
        }

        return false;
    }

    /**
     * Updates the sort order.
     *
     * @param array $postData
     * @param string $key
     *
     * @return void
     */
    public function updateSortOrder($postData, $key)
    {
        $setting = $this->load->model('setting/setting', ['return' => true]);

        // $setting->editSettingValue($postData['name'], $key, $postData['now']);
        $setting->insertUpdateSetting($postData['name'], [$key => $postData['now']]);
    }

    /**
     * Updates the status.
     *
     * @param string $group
     * @param string $key
     * @param int $status
     *
     * @return void
     */
    public function updateStatus($group, $key, $status)
    {
        $setting = $this->load->model('setting/setting', ['return' => true]);

        // $setting->editSettingValue($group, $key, $status);
        $setting->insertUpdateSetting($group, [$key => $status]);
    }
}
