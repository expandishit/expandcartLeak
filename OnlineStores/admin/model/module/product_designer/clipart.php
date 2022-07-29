<?php

class ModelModuleProductDesignerClipart extends Model
{

    private $categoriesDirName = DIR_IMAGE . "modules/pd_images/categories/";
    private $clipartDirName = DIR_IMAGE . "modules/pd_images/cliparts/";

    public function addCategory($data)
    {
        $this->db->query("INSERT INTO " . DB_PREFIX . "clipart_category SET category_name = '" . $this->db->escape($data['txtCategoryName']) . "', status = '" . $this->db->escape($data['optActive']) . "'");

        $clipart_cat_id = $this->db->getLastId();

        if(isset($data['image']))
            $this->db->query("UPDATE " . DB_PREFIX . "clipart_category SET category_image = '" . $this->db->escape(html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8')) . "' WHERE caid = '" . (int)$clipart_cat_id . "'");

        return true;
    }

    public function addClipartImage($data)
    {
        $this->db->query("INSERT INTO " . DB_PREFIX . "clipart_image SET category_id = '" . $this->db->escape($data['optcategory']) . "'");

        $image_id = $this->db->getLastId();
        if(isset($data['image']))
            $this->db->query("UPDATE " . DB_PREFIX . "clipart_image SET image_name = '" . $this->db->escape(html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8')) . "' WHERE imgid = '" . (int)$image_id . "'");

        return true;
    }

    //update
    public function editCategory($cid, $data)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "clipart_category SET category_name = '" . $this->db->escape($data['txtCategoryName']) . "', status = '" . $this->db->escape($data['optActive']) . "' WHERE caid = '" . (int)$cid . "'");

        if (isset($data['image'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "clipart_category SET category_image = '" . $this->db->escape(html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8')) . "' WHERE caid = '" . (int)$cid . "'");
        }
        return true;
      }

    public function updateImageCategory($imgid, $data)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "clipart_image SET category_id = '" . $this->db->escape($data['optcategory']) . "' WHERE imgid = '" . (int)$imgid . "'");

    }

    //delete
    public function deleteCategory($cid)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "clipart_category WHERE caid = '" . (int)$cid . "'");
    }

    //delete
    public function deleteImage($imgid)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "clipart_image WHERE imgid = '" . (int)$imgid . "'");
    }

    //get all category here
    public function getCategoryById($cid)
    {
        $query = $this->db->query("SELECT * from " . DB_PREFIX . "clipart_category WHERE caid = '" . (int)$cid . "'");
        return $query->row;
    }

    //select all
    public function getAllCategoryList()
    {
        $sql = "SELECT * FROM " . DB_PREFIX . "clipart_category order by category_name ASC";
        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function dtHandler($start=0, $length=10, $search = null, $orderColumn="caid", $orderType="ASC") {
        $query = "SELECT * FROM " . DB_PREFIX . "clipart_category";
        //$query = ;
        $total = $this->db->query($query)->num_rows;
        $where = "";
        if (!empty($search)) {
            $where .= "(category_name like '%" . $this->db->escape($search) . "%'
            OR status = '" . $this->db->escape($search) . "'
            OR caid like '%" . $this->db->escape($search) . "%')";
            $query .= " WHERE " . $where;
        }

        $totalFiltered = $this->db->query($query)->num_rows;
        $query .= " ORDER by {$orderColumn} {$orderType}";

        if($length != -1) {
            $query .= " LIMIT " . $start . ", " . $length;
        }

        $results = $this->db->query($query)->rows;
        $this->load->language('module/product_designer');
        $this->load->model('tool/image');

        foreach ($results as $key => $result)
        {
            $cate_img = $this->model_tool_image->resize('no_image.jpg', 35, 35);
            if(
                isset($result['category_image'])
                && ($result['category_image'] != 'no_image.jpg' && $result['category_image'] != '')
            ) {
                $cate_img = $this->model_tool_image->resize($result['category_image'], 35, 35);
            }

            $results[$key]['category_image']=$cate_img;
            $results[$key]['category_name'] = trim($result['category_name']);
            $results[$key]['status_text'] = $result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled');
        }

        $data = array (
            'data' => $results,
            'total' => $total,
            'totalFiltered' => $totalFiltered
        );
        return $data;
    }

    public function dtClipHandler($start=0, $length=10, $search = null, $orderColumn="imgid", $orderType="ASC") {

        $query = "SELECT *, (select category_name from clipart_category where caid=category_id) as category_name FROM " . DB_PREFIX . "clipart_image";
        //$query = ;
        $total = $this->db->query($query)->num_rows;

        $where = "";
        if (!empty($search)) {
            $where .= " category_id = (select caid from clipart_category
            where category_name like '%" . $this->db->escape($search) . "%')";
            $query .= " WHERE " . $where;
        }

        $totalFiltered = $this->db->query($query)->num_rows;
        $query .= " ORDER by {$orderColumn} {$orderType}";

        if($length != -1) {
            $query .= " LIMIT " . $start . ", " . $length;
        }

        $results = $this->db->query($query)->rows;

        $this->load->model('tool/image');

        foreach ($results as $key => $result){
            if (isset($result['image_name'])) {
                // "modules/pd_image/" . $result['image_name']
                $image = $this->model_tool_image->resize($result['image_name'], 50, 50);
            } else {
                $image = $this->model_tool_image->resize('no_image.jpg', 50, 50);
            }
            $results[$key]= array(
                'imgid'             => $result['imgid'],
                'category_name'     => $result['category_name'],
                'image_name'        => $image,
                'href2'             => $result['href2']
            );
        }

        $data = array (
            'data' => $results,
            'total' => $total,
            'totalFiltered' => $totalFiltered
        );
        return $data;
    }

    public function getAllCategoryListASC()
    {
        $sql = "SELECT * FROM " . DB_PREFIX . "clipart_category order by category_name ASC";
        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function getClipartImageByCategoryId($catid)
    {
        $sql = "SELECT * FROM " . DB_PREFIX . "clipart_image WHERE category_id = '" . (int)$catid . "'";
        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function uplaodImage($image, $dirName)
    {
        $filename = $image["name"];
        $ext = substr(strrchr($filename, "."), 1);
        $randName = '';
        $image_name = uniqid();
        $filename = $image_name . '.' . $ext;
        if(\Filesystem::setPath('image/' . $dirName . $filename)->upload($image["tmp_name"])) {
            return $filename;
        } else {
            return '';
        }

    }
}
?>
