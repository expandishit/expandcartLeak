<?php

class ModelModuleFifaCardsSettings extends Model
{
    /**
     * update the module settings.
     *
     * @param array $inputs
     *
     * @return bool|\Exception
     */
    public function updateSettings($inputs)
    {
        $this->load->model('setting/setting');

        $this->model_setting_setting->editSetting(
            'fifa_cards', $inputs
        );

        return true;
    }

    public function install()
    {
        $alterQueries = [];
        $alterQueries[] = 'ALTER TABLE `order_product` ADD COLUMN `fifa_cards` TEXT NULL AFTER `reward`';

        $alterQueries[] = 'CREATE TABLE `fifa_cards_clubs` (`club_id` int(11) UNSIGNED NOT NULL, `club_logo` varchar(255) NOT NULL, `status` tinyint(1) NOT NULL DEFAULT 1)';

        $alterQueries[] = 'ALTER TABLE `fifa_cards_clubs` ADD PRIMARY KEY (`club_id`)';
        $alterQueries[] = 'ALTER TABLE `fifa_cards_clubs` MODIFY `club_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3';

        $alterQueries[] = 'CREATE TABLE `fifa_cards_clubs_locale` (`id` int(11) UNSIGNED NOT NULL, `club_id` int(11) UNSIGNED NOT NULL, `lang_id` int(11) UNSIGNED NOT NULL, `name` varchar(128) NOT NULL)';

        $alterQueries[] = 'ALTER TABLE `fifa_cards_clubs_locale` ADD PRIMARY KEY (`id`)';
        $alterQueries[] = 'ALTER TABLE `fifa_cards_clubs_locale` MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3';

        foreach ($alterQueries as $alterQuery) {
            $this->db->query($alterQuery);
        }
    }

    public function uninstall()
    {
        $alterQueries = [];
        $alterQueries[] = 'ALTER TABLE `order_product` DROP COLUMN `fifa_cards`';
        $alterQueries[] = 'DROP TABLE `fifa_cards_clubs`';
        $alterQueries[] = 'DROP TABLE `fifa_cards_clubs_locale`';

        foreach ($alterQueries as $alterQuery) {
            $this->db->query($alterQuery);
        }
    }

    public function getSettings()
    {
        return $this->config->get('fifa_cards');
    }

    public function isActive()
    {
        $settings = $this->getSettings();

        if (isset($settings) && $settings['status'] == 1 && \Extension::isInstalled('fifa_cards')) {
            return true;
        }

        return false;
    }
}
