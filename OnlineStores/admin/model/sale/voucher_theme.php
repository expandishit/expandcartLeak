<?php

class ModelSaleVoucherTheme extends Model
{
    public function addVoucherTheme($data)
    {
        $queryString = [];
        $queryString[] = 'INSERT INTO ' . DB_PREFIX . 'voucher_theme SET';
        $queryString[] = 'image = "' . $this->db->escape(html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8')) . '"';
        $this->db->query(implode(' ', $queryString));

        $voucher_theme_id = $this->db->getLastId();

        foreach ($data['voucher_theme_description'] as $language_id => $value) {
            $queryString = [];
            $queryString[] = "INSERT INTO " . DB_PREFIX . "voucher_theme_description SET";
            $queryString[] = "voucher_theme_id = '" . (int)$voucher_theme_id . "',";
            $queryString[] = "language_id = '" . (int)$language_id . "',";
            $queryString[] = "name = '" . $this->db->escape($value['name']) . "'";
            $this->db->query(implode(' ', $queryString));
        }

        $this->cache->delete('voucher_theme');
    }

    public function editVoucherTheme($voucher_theme_id, $data)
    {
        $queryString = [];
        $queryString[] = "UPDATE " . DB_PREFIX . "voucher_theme SET";
        $queryString[] = "image = '" . $this->db->escape(html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8')) . "'";
        $queryString[] = "WHERE voucher_theme_id = '" . (int)$voucher_theme_id . "'";
        $this->db->query(implode(' ', $queryString));

        $queryString = [];
        $queryString[] = "DELETE FROM " . DB_PREFIX . "voucher_theme_description";
        $queryString[] = "WHERE voucher_theme_id = '" . (int)$voucher_theme_id . "'";
        $this->db->query(implode(' ', $queryString));

        foreach ($data['voucher_theme_description'] as $language_id => $value) {
            $queryString = [];
            $queryString[] = "INSERT INTO " . DB_PREFIX . "voucher_theme_description SET";
            $queryString[] = "voucher_theme_id = '" . (int)$voucher_theme_id . "',";
            $queryString[] = "language_id = '" . (int)$language_id . "',";
            $queryString[] = "name = '" . $this->db->escape($value['name']) . "'";
            $this->db->query(implode(' ', $queryString));
        }

        $this->cache->delete('voucher_theme');
    }

    public function deleteVoucherTheme($voucher_theme_id)
    {
        $this->db->query(
            "DELETE FROM " . DB_PREFIX . "voucher_theme WHERE voucher_theme_id = '" . (int)$voucher_theme_id . "'"
        );
        $this->db->query(
            "DELETE FROM " . DB_PREFIX . "voucher_theme_description
            WHERE voucher_theme_id = '" . (int)$voucher_theme_id . "'"
        );

        $this->cache->delete('voucher_theme');
    }

    public function getVoucherTheme($voucher_theme_id)
    {
        $queryString = [];
        $queryString[] = "SELECT * FROM " . DB_PREFIX . "voucher_theme vt";
        $queryString[] = "LEFT JOIN " . DB_PREFIX . "voucher_theme_description vtd";
        $queryString[] = "ON (vt.voucher_theme_id = vtd.voucher_theme_id)";
        $queryString[] = "WHERE vt.voucher_theme_id = '" . (int)$voucher_theme_id . "'";
        $queryString[] = "AND vtd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
        $query = $this->db->query(implode(' ', $queryString));

        return $query->row;
    }

    public function getVoucherThemes($data = array())
    {
        if ($data) {
            $queryString = [];

            $queryString[] = "SELECT * FROM " . DB_PREFIX . "voucher_theme vt";
            $queryString[] = "LEFT JOIN " . DB_PREFIX . "voucher_theme_description vtd";
            $queryString[] = "ON (vt.voucher_theme_id = vtd.voucher_theme_id)";
            $queryString[] = "WHERE vtd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
            $queryString[] = "ORDER BY vtd.name";

            if (isset($data['order']) && ($data['order'] == 'DESC')) {
                $queryString[] = " DESC";
            } else {
                $queryString[] = " ASC";
            }

            if (isset($data['start']) || isset($data['limit'])) {
                if ($data['start'] < 0) {
                    $data['start'] = 0;
                }

                if ($data['limit'] < 1) {
                    $data['limit'] = 20;
                }

                $queryString[] = " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
            }

            $query = $this->db->query(implode(' ', $queryString));

            return $query->rows;
        } else {
            $voucher_theme_data = $this->cache->get('voucher_theme.' . (int)$this->config->get('config_language_id'));

            if (!$voucher_theme_data) {
                $queryString = [];
                $queryString[] = "SELECT * FROM " . DB_PREFIX . "voucher_theme vt";
                $queryString[] = "LEFT JOIN " . DB_PREFIX . "voucher_theme_description vtd";
                $queryString[] = "ON (vt.voucher_theme_id = vtd.voucher_theme_id)";
                $queryString[] = "WHERE vtd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
                $queryString[] = "ORDER BY vtd.name";
                $query = $this->db->query(implode(' ', $queryString));

                $voucher_theme_data = $query->rows;

                $this->cache->set(
                    'voucher_theme.' . (int)$this->config->get('config_language_id'), $voucher_theme_data
                );
            }

            return $voucher_theme_data;
        }
    }

    public function getVoucherThemeDescriptions($voucher_theme_id)
    {
        $voucher_theme_data = $queryString = [];

        $queryString[] = "SELECT * FROM " . DB_PREFIX . "voucher_theme_description";
        $queryString[] = "WHERE voucher_theme_id = '" . (int)$voucher_theme_id . "'";
        $query = $this->db->query(implode(' ', $queryString));

        foreach ($query->rows as $result) {
            $voucher_theme_data[$result['language_id']] = array('name' => $result['name']);
        }

        return $voucher_theme_data;
    }

    public function getTotalVoucherThemes()
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "voucher_theme");

        return $query->row['total'];
    }
}
