<?php

/**
 * TODO :-
 * - implement a seperated validation library.
*/

class ModelModuleProductDesignerSettings extends Model
{

    // private $table = DB_PREFIX . 'custom_tshirt_design';
    private $table = DB_PREFIX . 'setting';

    /**
     * validate inputs againts some rules.
     *
     * @param array $inputs
     * @param array $rules
     *
     * @return bool|\Exception
     */
    public function validateSettings($inputs, $rules)
    {
        return true;
    }

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
            'product_designer', $inputs
        );

        return true;
    }

    /**
     * get the module settings.
     *
     * @param int $group_id
     *
     * @return array
     */
    public function getSettings($store_id = 0)
    {
        $this->load->model('setting/setting');

        $data = $this->model_setting_setting->getSetting(
            'product_designer', $store_id
        );

        return $data;
    }

    private function installDirectoryTree()
    {
        $install = function ($dirname, $permession) {
            /*if (!file_exists($dirname)) {
                mkdir($dirname, $permession, true);
            }*/
            \Filesystem::setPath($dirname);
            \Filesystem::create();
            \Filesystem::changeMod($permession);
            // chmod($dirname, $permession);
        };

        foreach ($this->dirNames as $dirname => $permession) {
            $dirname = $this->parentDir . $dirname;
            $install($dirname, $permession);
        }
    }

    public function resolveFilesArray($files, $origImages, $did)
    {
        $resolveArray = function ($attributes, $origImages, $did, $type) {
            $return = [];
            $attributes = $attributes[$type . '_image'];
            $origImages = $origImages[$type . '_image'];
            foreach ($attributes as $attrKey => $attribute) {
                foreach ($attribute[$did] as $fileKey => $file) {
                    $return[$fileKey][$attrKey] = $file;
                    $return[$fileKey]['origImage'] = $origImages[$fileKey];
                }
            }
            return $return;
        };

        return [
            'front_info' => $resolveArray($files, $origImages, $did, 'front'),
            'back_info' => $resolveArray($files, $origImages, $did, 'back')
        ];
    }

    public function getTshirtDesignById($did)
    {
        if (!isset($did) || !preg_match('#^[0-9]+$#', $did)) {
            // handle errors
            die("Wrong design id");
        }

        $thirtDesign = $this->db->query(
            "SELECT * FROM `tshirtdesign` WHERE did=" . $did
        )->row;

        array_walk($thirtDesign, function($value, $key) use(&$thirtDesign) {
            $thirtDesign[$key] = (
                ($key == 'front_info' || $key == 'back_info') ?
                json_decode(html_entity_decode($value), true) :
                $value
            );
        });

        return $thirtDesign;
    }

    public function uploadFiles($sides, $tshirtDesign)
    {
        $u = [];
        foreach ($sides as $sideKey => $side) {
            foreach ($side as $fileKey => $file) {
                if ($file['name'] != '' && !empty($file['name']) && $file['size'] > 0) {
                    $ext = substr(strrchr($file['name'], "."), 1);
                    $this->moveUploadedFile($file, $tshirtDesign[$sideKey][$fileKey]);
                    $tshirtDesign[$sideKey][$fileKey]['modified_image'] = $file['origImage'] . '.' . $ext;
                }
            }
        }
        return $tshirtDesign;
    }

    private $dirName = DIR_IMAGE . "modules/pd_images/modified_image/";

    private function moveUploadedFile($file, $tshirtDesign)
    {
        $filename = $file["origImage"];
        $ext = substr(strrchr($file['name'], "."), 1);
        $filename = $filename . '.' . $ext;
        $dirName = $this->dirName;

        if (file_exists($dirName . $filename)) {
            unlink($dirName . $filename);
        }

        if(move_uploaded_file($file["tmp_name"], $dirName . $filename)) {
            return $filename;
        } else {
            return '';
        }
    }

    public function updateTshirtDesign($thirtDesign, $did)
    {
        array_walk($thirtDesign, function($value, $key) use(&$thirtDesign) {
            $thirtDesign[$key] = (
                ($key == 'front_info' || $key == 'back_info') ?
                json_encode($value) :
                $value
            );
        });

        $updateQueryString = $fields = [];
        $updateQueryString[] = "UPDATE `tshirtdesign` SET";
        foreach ($thirtDesign as $fieldName => $field) {
            if (!is_numeric($field)) {
                $field = "'" . $field . "'";
            }
            $fields[] = '`' .$fieldName . '`=' . $field;
        }
        $updateQueryString[] = implode(',', $fields);
        $updateQueryString[] = "WHERE did=" . $did;

        $this->db->query(implode(' ', $updateQueryString));
    }

    public $parentDir = "image/modules/pd_images/";

    public $dirNames = [
        'categories' => 'writable',
        'cliparts' => 'writable',
        'upload_image' => 'writable',
        'merge_image' => 'writable',
        'modified_image' => 'writable',
        'themes' => 'public',
    ];

    public function install()
    {
        /*$dirs[] = $this->dirName;
        if (!file_exists($this->dirName)) {
            mkdir($this->dirName, 0777, true);
            chmod($this->dirName, 0777);
            if (false) {
                $this->session->data['error'] .= sprintf($this->language->get('ms_error_directory'), $this->dirName);
            }
        } else {
            if (!is_writable($this->dirName)) {
                $this->session->data['error'] .= sprintf($this->language->get('ms_error_directory_notwritable'), $this->dirName);
            } else {
                $this->session->data['error'] .= sprintf($this->language->get('ms_error_directory_exists'), $this->dirName);
            }
        }*/

        $this->installDirectoryTree();

        $query = "CREATE TABLE IF NOT EXISTS `" .DB_PREFIX. "tshirtdesign` (
            `did` int(10) NOT NULL AUTO_INCREMENT,
            `design_id` varchar(100) CHARACTER SET utf8 NOT NULL,
            `front_info` text CHARACTER SET utf8 NOT NULL,
            `back_info` text CHARACTER SET utf8 NOT NULL,
            `pid` int(11) NOT NULL,
            `createdDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`did`)
        )";
        $this->db->query($query);

        $query = "CREATE TABLE IF NOT EXISTS `" .DB_PREFIX. "clipart_category` (
            `caid` int(5) NOT NULL AUTO_INCREMENT,
            `category_name` varchar(500) NOT NULL,
            `category_image` varchar(100) NOT NULL,
            `status` int(1) NOT NULL DEFAULT '0',
            PRIMARY KEY (`caid`)
        ) CHARACTER SET utf8 COLLATE utf8_unicode_ci";
        $this->db->query($query);

        $query = "CREATE TABLE IF NOT EXISTS `" .DB_PREFIX. "clipart_image` (
            `imgid` int(5) NOT NULL AUTO_INCREMENT,
            `category_id` int(10) NOT NULL DEFAULT '0',
            `image_name` varchar(100) NOT NULL,
            `added_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`imgid`)
        )";
        $this->db->query($query);

        $alterQueries = [
            "ALTER TABLE " . DB_PREFIX . "product ADD COLUMN pd_custom_price TEXT NOT NULL",
            "ALTER TABLE " .DB_PREFIX. "product ADD COLUMN pd_is_customize int(1) NOT NULL default 0",
            "ALTER TABLE " .DB_PREFIX. "product ADD COLUMN pd_custom_min_qty int(15) NOT NULL default '1'",
            "ALTER TABLE " .DB_PREFIX. "product ADD COLUMN pd_back_image varchar(255) DEFAULT NULL",
            "ALTER TABLE " .DB_PREFIX. "customer ADD COLUMN pd_cart TEXT DEFAULT NULL",
            "ALTER TABLE " .DB_PREFIX. "order_product ADD COLUMN pd_tshirt_id int(11) NULL default 0",
        ];

        foreach ($alterQueries as $alterQuery) {
            try {
                $this->db->query($alterQuery);
            }
            catch(exception $ex) {

            }
        }
    }

    public function uninstall()
    {
        if (\Filesystem::list($this->parentDir)) {
            // exec('rm ' . $this->parentDir . ' -R');
            \Filesystem::delete($this->parentDir);
            // rmdir($this->parentDir);
        }
        // TODO remote the $this->dirName directory.
        $query = "DROP TABLE IF EXISTS `" .DB_PREFIX. "tshirtdesign`";
        $this->db->query($query);

        $query = "DROP TABLE IF EXISTS `" .DB_PREFIX. "clipart_category`";
        $this->db->query($query);

        $query = "DROP TABLE IF EXISTS `" .DB_PREFIX. "clipart_image`";
        $this->db->query($query);

        $alterQueries = [
            "ALTER TABLE " .DB_PREFIX. "product DROP COLUMN pd_custom_price",
            "ALTER TABLE " .DB_PREFIX. "product DROP COLUMN pd_is_customize",
            "ALTER TABLE " .DB_PREFIX. "product DROP COLUMN pd_custom_min_qty",
            "ALTER TABLE " .DB_PREFIX. "product DROP COLUMN pd_back_image",
            "ALTER TABLE " .DB_PREFIX. "customer DROP COLUMN pd_cart",
            "ALTER TABLE " .DB_PREFIX. "order_product DROP COLUMN pd_tshirt_id",
        ];

        foreach ($alterQueries as $alterQuery) {
            $this->db->query($alterQuery);
        }
    }

    public function upgrade()
    {
        $alterQueries = [
            "ALTER TABLE " . DB_PREFIX . "product DROP COLUMN pd_custom_price",
            "ALTER TABLE " . DB_PREFIX . "product ADD COLUMN pd_custom_price TEXT NOT NULL",
        ];

        foreach ($alterQueries as $alterQuery) {
            $this->db->query($alterQuery);
        }
    }

    public function genereateBreadcrumbs($crumbs)
    {
        foreach ($crumbs as $crumb) {
            $data[] = [
                'text' => $this->language->get($crumb['text']),
                'href' => (
                    $crumb['href'] ? $this->url->link(
                        $crumb['href'], 'token=' . $this->session->data['token'], 'SSL'
                    ) : $this->url->link(null, null, null)
                ),
                'separator' => $crumb['separator'],
            ];
        }

        return $data;
    }

}
