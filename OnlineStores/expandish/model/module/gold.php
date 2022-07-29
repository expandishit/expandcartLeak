<?php

class ModelModuleGold extends Model
{
    /**
     * get Caliber
     *
     * @return array
     */
    public function getProductCaliber($product_id)
    {
        return $this->db->query("SELECT g.caliber, g.price, gtp.manuf_price FROM `" .DB_PREFIX. "gold_to_product` gtp LEFT JOIN `" .DB_PREFIX. "gold` g ON (g.id = gtp.gold_id) WHERE gtp.product_id = $product_id")->rows[0] ?? false;
    }

    /**
     * get settings.
     *
     * @return array
     */
    public function getSettings()
    {
        return $this->config->get('gold');
    }

    public function isActive()
    {
        $settings = $this->getSettings();

        if (isset($settings) && $settings['status'] == 1 && \Extension::isInstalled('gold')) {
            return true;
        }

        return false;
    }
}
