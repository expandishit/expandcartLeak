<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * 22 Sep 2020 @ hassan ahmad
 * Due to EC-21588 and cors issues
 * as we are going serving the images from cdn
 * product designer app had faced a lot of issues related to the CORS rules
 * to fix this quickly ( as we need to merge all customers to cdn ) I had to
 * double creating / editing / copying images
 * to be fixed
 */

class ControllerModuleProductDesigner extends Controller
{

    public function index()
    {
        $this->language->load_json('module/product_designer');

        $pid = $this->request->get['product_id'];

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home'),
            'separator' => false
        );

        $this->load->model('tool/image');

        $this->load->model('module/product_designer');
        $this->model_designit_designit = $this->model_module_product_designer;

        $this->load->model('tool/image');
        $data['pcolor'] = "FFFFFF";
        $data['pId'] = '';
        $data['pImage'] = '';
        $data['back_Image'] = '';
        $data['customprice'] = '';
        $data['isoption'] = false;
        $productInfo = array();

        $data['designdic'] = HTTP_SERVER . '/' . FRONT_FOLDER_NAME . '/view/theme/default/template/designit/';
        // $data['dirImage']  = \Filesystem::getUrl('image/') . '/';
        $data['dirImage']  = HTTP_IMAGE;//\Filesystem::getUrl('image/') . '/';

        /*thumb preview*/
        $data['f_image'] = '';
        $data['b_image'] = '';
        //
        unset($this->session->data['back_design']);
        unset($this->session->data['front_design']);
        unset($this->session->data['imgsize']);
        unset($this->session->data['special_url']);
        unset($this->session->data['b_image_name']);
        unset($this->session->data['f_image_name']);
        unset($this->session->data['special_url']);
        unset($this->session->data['image_url']);
        unset($this->session->data['upload_id']);
        unset($this->session->data['image_current_slide']);
        unset($this->session->data['uniqid']);

        $data['options'] = array();
        $this->language->load_json('product/product');
        $data['text_select'] = $this->language->get('text_select');
        // $this->session->data['special_url'] = design_dir;
        $this->session->data['special_url'] = DIR_APPLICATION . "/view/theme/default/template/designit/";
        $this->session->data['uniqid'][$pid] = $this->guid();
        $data['uniqid'] = $this->session->data['uniqid'][$pid];
        $this->session->data['image_url'] = DIR_IMAGE;

        $data['mug_front_image_big'] = '';
        $data['mug_back_image_big'] = '';


        $data['mug_front_image_thumb'] = '';
        $data['mug_back_image_thumb'] = '';

        //
        if (isset($this->request->get['product_id'])) {

            $productInfo = $this->model_designit_designit->getProductImages($this->request->get['product_id']);

            $data['customprice'] = json_decode($productInfo['pd_custom_price'], true);
            $data['pcolor'] = '';

            //here for the front image
            if (isset($productInfo['image'])) {
                $data['mug_front_image_big'] = $this->model_tool_image->resize($productInfo['image'], 350, 350);
                $data['mug_front_image_thumb'] = $this->model_tool_image->resize($productInfo['image'], 83.33, 87.33);
            }
            //here for the back image
            if (isset($productInfo['pd_back_image'])) {
                $data['mug_back_image_big'] = $this->model_tool_image->resize($productInfo['pd_back_image'], 350, 350);
                $data['mug_back_image_thumb'] = $this->model_tool_image->resize($productInfo['pd_back_image'], 83.33, 87.33);
            }


            $data['pId'] = $this->request->get['product_id'];

            $this->session->data['back_design'] = $productInfo['image'];

            $this->load->model('catalog/product');

            /*Option work here for specific product*/
            $product_info = $this->model_catalog_product->getProduct($this->request->get['product_id']);

            if ($product_info) {
                foreach ($this->model_catalog_product->getProductOptions($this->request->get['product_id']) as $option) {
                    $product_option_value_data = array();
                    foreach ($option['option_value'] as $option_value) {
                        if (!$option_value['subtract'] || ($option_value['quantity'] > 0)) {
                            if (($this->customer->isCustomerAllowedToViewPrice()) && (float)$option_value['price']) {
                                $price = $this->currency->format($this->tax->calculate($option_value['price'], $product_info['tax_class_id'], $this->config->get('config_tax') ? 'P' : false));
                            } else {
                                $price = false;
                            }
                            $optionValueImage = null;
                            if (isset($option_value['images'][0]['image'])&&!empty($option_value['images'][0]['image'])){
                                $optionValueImage = $this->model_tool_image->resize($option_value['images'][0]['image'], 500, 500);
                            }
                            $product_option_value_data[] = array(
                                'product_option_value_id' => $option_value['product_option_value_id'],
                                'option_value_id' => $option_value['option_value_id'],
                                'name' => $option_value['name'],
                                'image' => $optionValueImage,
                                'price' => $price,
                                'price_prefix' => $option_value['price_prefix']
                            );
                        }
                    }

                    $data['options'][] = array(
                        'product_option_id' => $option['product_option_id'],
                        'product_option_value' => $product_option_value_data,
                        'option_id' => $option['option_id'],
                        'name' => $option['name'],
                        'type' => $option['type'],
                        'value' => $option['value'],
                        'required' => $option['required']
                    );
                }
            } //end product info
            /****************************************/

        }

        $data['font_coll'] = array();
        if ($handle = opendir($this->session->data['special_url'] . 'font')) {
            while (false !== ($entry = readdir($handle))) {
                if (isset($entry) && $entry != '.' && $entry != '..') {
                    $font_name = explode('.', $entry);
                    if (isset($font_name)) {
                        $data['font_coll'][] = array(
                            'font' => $font_name[0]
                        );
                    }
                }
            }
            closedir($handle);
        }

        //$data['font_coll'] = $this->SortByKeyValue($data['font_coll'],'font');
        //

        /*Set settings*/
        /*Template part********************/
        $data['block_color'] = '#FF0000';
        $data['back_color'] = '#FF0000';
        $data['price_text'] = 'will be added for this design';
        $data['cust_price_color'] = '000000';
        $data['cust_price_text_bold'] = 'none';
        $data['temp'] = 'Theme A';
        $data['theme_button'] = 'theme_a';
        $data['text_style'] = 'none';
        $data['text_style'] = 'none';
        $data['min_cust_qty'] = $productInfo['pd_custom_min_qty'];
        $data['cust_min_alert_text'] = 'You need to add atleast quantity';

        //tab text
        $data['tab_1_text'] = 'Add Text';
        $data['tab_2_text'] = 'Add Image';
        $data['tab_3_text'] = 'Clip Art';

        //header text
        $data['header_text_1'] = 'Add Your Text';
        $data['header_text_2'] = 'Add Your Image';
        $data['header_text_3'] = 'Add Clip Art';

        //additional
        $data['font_text'] = 'Font:';
        $data['color_text'] = 'Color:';
        $data['text_tex'] = 'Text:';
        $data['add_text_button'] = 'Next:';
        $data['qty_text'] = 'Qty:';
        $data['upload_text'] = 'Upload Image:';
        $data['add_to_cart'] = 'Add to Cart';


        $this->load->model('tool/image');

        if ($this->config->get('tshirt_module')) {
            $str_module = $this->config->get('tshirt_module');

            $data['pd_themes_path'] = HTTP_SERVER . 'image/modules/product_designer/';

            if (isset($str_module['block_color'])) {
                $data['block_color'] = $str_module['block_color'];
            }
            if (isset($str_module['back_color'])) {
                $data['back_color'] = $str_module['back_color'];
            }

            if ($this->language->get('price_text')) {
                $data['price_text'] = $this->language->get('price_text');
            }

            if (isset($str_module['cust_price_color'])) {
                $data['cust_price_color'] = $str_module['cust_price_color'];
            }
            if (isset($str_module['cust_fw']) && $str_module['cust_fw'] == 1) {
                $data['cust_price_text_bold'] = 'italic';
            }
            if (isset($str_module['cust_dec']) && $str_module['cust_dec'] == 1) {
                $data['text_style'] = 'underline';
            }
            if (isset($str_module['template']) && $str_module['template'] != '--select--') {
                $data['temp'] = $str_module['template'];
                $data['theme_button'] = str_replace(' ', '_', strtolower($str_module['template']));
            }

            if (isset($str_module['cust_min_alert_text']) && $str_module['cust_min_alert_text'] != '') {
                $data['cust_min_alert_text'] = $str_module['cust_min_alert_text'];
            } else if ($this->language->get('cust_min_alert_text')) {
                $data['cust_min_alert_text'] = $this->language->get('cust_min_alert_text');
            }


            //tab
            if (isset($str_module['tab_text_1']) && $str_module['tab_text_1'] != '') {
                $data['tab_1_text'] = $str_module['tab_text_1'];
            } else if ($this->language->get('tab_1_text')) {
                $data['tab_1_text'] = $this->language->get('tab_1_text');
            }
            if (isset($str_module['tab_text_2']) && $str_module['tab_text_2'] != '') {
                $data['tab_2_text'] = $str_module['tab_text_2'];
            } else if ($this->language->get('tab_2_text')) {
                $data['tab_2_text'] = $this->language->get('tab_2_text');
            }
            if (isset($str_module['tab_text_3']) && $str_module['tab_text_3'] != '') {
                $data['tab_3_text'] = $str_module['tab_text_3'];
            } else if ($this->language->get('tab_3_text')) {
                $data['tab_3_text'] = $this->language->get('tab_3_text');
            }

            //header text
            if (isset($str_module['header_text_1']) && $str_module['header_text_1'] != '') {
                $data['header_text_1'] = $str_module['header_text_1'];
            } else if ($this->language->get('header_text_1')) {
                $data['header_text_1'] = $this->language->get('header_text_1');
            }
            if (isset($str_module['header_text_2']) && $str_module['header_text_2'] != '') {
                $data['header_text_2'] = $str_module['header_text_2'];
            } else if ($this->language->get('header_text_2')) {
                $data['header_text_2'] = $this->language->get('header_text_2');
            }
            if (isset($str_module['header_text_3']) && $str_module['header_text_3'] != '') {
                $data['header_text_3'] = $str_module['header_text_3'];
            } else if ($this->language->get('header_text_3')) {
                $data['header_text_3'] = $this->language->get('header_text_3');
            }

            //Additional text
            if (isset($str_module['font_text']) && $str_module['font_text'] != '') {
                $data['font_text'] = $str_module['font_text'];
            } else if ($this->language->get('font_text')) {
                $data['font_text'] = $this->language->get('font_text');
            }
            if (isset($str_module['color']) && $str_module['color'] != '') {
                $data['color_text'] = $str_module['color'];
            } else if ($this->language->get('color_text')) {
                $data['color_text'] = $this->language->get('color_text');
            }
            if (isset($str_module['text']) && $str_module['text'] != '') {
                $data['text_tex'] = $str_module['text'];
            } else if ($this->language->get('text_tex')) {
                $data['text_tex'] = $this->language->get('text_tex');
            }
            if (isset($str_module['qty_box_text']) && $str_module['qty_box_text'] != '') {
                $data['qty_text'] = $str_module['qty_box_text'];
            } else if ($this->language->get('qty_text')) {
                $data['qty_text'] = $this->language->get('qty_text');
            }
            if (isset($str_module['btn_add_text']) && $str_module['btn_add_text'] != '') {
                $data['add_text_button'] = $str_module['btn_add_text'];
            } else if ($this->language->get('add_text_button')) {
                $data['add_text_button'] = $this->language->get('add_text_button');
            }
            if (isset($str_module['btn_upload_text']) && $str_module['btn_upload_text'] != '') {
                $data['upload_text'] = $str_module['btn_upload_text'];
            } else if ($this->language->get('upload_text')) {
                $data['upload_text'] = $this->language->get('upload_text');
            }
            if (isset($str_module['add_btn_text']) && $str_module['add_btn_text'] != '') {
                $data['add_to_cart'] = $str_module['add_btn_text'];
            } else if ($this->language->get('add_to_cart')) {
                $data['add_to_cart'] = $this->language->get('add_to_cart');
            }

        }


        // if product has option then we set the token to change the add to cart function
        if (count($data['options']) > 0) {
            $data['isoption'] = true;
        }

        /**********************************/


        $data['categorylist'] = array();

        $this->load->model('tool/image');

        $results_cat = $this->model_designit_designit->getAllCategoryName();

        foreach ($results_cat as $result) {

            $category_image = $this->model_tool_image->resize('no_image.jpg', 20, 20);
            if (isset($result['category_image']) && $result['category_image'] != '') {
                $category_image = $this->model_tool_image->resize($result['category_image'], 20, 20);
            }

            $data['categorylist'][] = array(
                'caid' => $result['caid'],
                'category_image' => $category_image,
                'category_name' => $result['category_name']
            );
        }

        // $this->response->setOutput($this->load->view('designit/mug', $data));
        $this->data = $data;

        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/designit/mug.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/designit/mug.expand';
        }
        else {
            $this->template = 'default/template/designit/mug.expand';
        }

        // echo $this->template;exit;

        $this->response->setOutput($this->render_ecwig());

    }

    public function guid()
    {
        if (function_exists('com_create_guid')) {
            return com_create_guid();
        } else {
            mt_srand((double)microtime() * 10000);//optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);// "-"
            $uuid = [];
//            $uuid[] = chr(123);
            $uuid[] = substr($charid, 0, 8);
            $uuid[] = substr($charid, 8, 4);
            $uuid[] = substr($charid, 12, 4);
            $uuid[] = substr($charid, 16, 4);
            $uuid[] = substr($charid, 20, 12);
//            $uuid[] = chr(125);
            /*$uuid = chr(123)// "{"
                . substr($charid, 0, 8) . $hyphen
                . substr($charid, 8, 4) . $hyphen
                . substr($charid, 12, 4) . $hyphen
                . substr($charid, 16, 4) . $hyphen
                . substr($charid, 20, 12)
                . chr(125);// "}"*/
            return implode($hyphen, $uuid);
        }
    }

    public function getclipartimage()
    {
        if (!empty($this->request->get['catid'])) {
            $theme_button = 'theme_a';
            if ($this->config->get('tshirt_module')) {
                $str_module = $this->config->get('tshirt_module');
                if (isset($str_module['template']) && $str_module['template'] != '--select--') {
                    $data['temp'] = $str_module['template'];
                    $theme_button = str_replace(' ', '_', strtolower($str_module['template']));
                }
            }
            $this->load->model('module/product_designer');
            $this->model_designit_designit = $this->model_module_product_designer;
            $this->load->model('tool/image');
            $results = $this->model_module_product_designer->getClipArtImageByCategoryId($this->request->get['catid']);
            $image_list = "<div style='margin-bottom:10px;'><a class='" . $theme_button . "' href='javascript:void(0);' onclick='switchpanel();'>Back</a></div>";
            foreach ($results as $result) {
                $image_url = $image = $this->model_tool_image->resize( $result['image_name'], 200, 200);
                $image_src = $result['image_name'];
                if (isset($result['image_name'])) {
                    $image_list .= "<div style='float:left;margin:2px;'><img height='55' weight='55' onclick=copyImageBeforeSendPanel('" . $image_url . "','".$image_src."'); src='" . $image_url . "'></div>";
                }
            }//end foreach
        } //end if

        echo $image_list;
    }

    public function SortByKeyValue($data, $sortKey, $sort_flags = SORT_ASC)
    {
        if (empty($data) or empty($sortKey)) return $data;

        $ordered = array();
        foreach ($data as $key => $value)
            $ordered[$value[$sortKey]] = $value;

        ksort($ordered, $sort_flags);

        return array_values($ordered);
    }

    public function copyImage()
    {
        if ($_POST['simage']) {
            $new_id = uniqid();
            $img_ext = pathinfo($_POST['simage'], PATHINFO_EXTENSION);
            $img_name = pathinfo($_POST['simage'], PATHINFO_FILENAME);
            $img_path= $_POST['pimage'];

            // Due to EC-21588 and cors issues
            // Temp solution
            // TODO
            /*$old_url = 'image/' . $img_path;
            $new_url = 'image/modules/pd_images/upload_image/' . $new_id . '.' . $img_ext;
            $img = \Filesystem::read($old_url);
            if (!\Filesystem::setPath($new_url)->write($img)) {
                echo "-1";
                die();
            } else {
                echo $new_id . '.' . $img_ext;
                die();
            }*/
            $old_url = 'image/' . $img_path;
            $new_url = 'image/modules/pd_images/upload_image/' . $new_id . '.' . $img_ext;
            $img = \Filesystem::read($old_url);
            // if (!copy(DIR_IMAGE . $old_url, DIR_IMAGE . $new_url)) {
            if (!file_put_contents(DIR_IMAGE . 'modules/pd_images/upload_image/' . $new_id . '.' . $img_ext, $img)) {
                echo "-1";
                die();
            } else {
                // Double upload on server in case if GCS is enabled
                if (FILESYSTEM == 'gcs') {
                    $img = \Filesystem::read($old_url);
                    $copy = !\Filesystem::setPath($new_url)->write($img);
                }
                echo $new_id . '.' . $img_ext;
                die();
            }

        }
    }

    public function uploadImage()
    {
        $filename = $_FILES["uploadfile"]["name"];
        $ext = substr(strrchr($filename, "."), 1);
        if (strtolower(trim($ext)) == 'jpg' || strtolower(trim($ext)) == 'png' || strtolower(trim($ext)) == 'gif') {
            $randName = '';
            $image_name = uniqid();
            $save_url = DIR_IMAGE;
            $filename = $image_name . '.' . $ext;
            // Due to EC-21588 and cors issues
            if (move_uploaded_file($_FILES["uploadfile"]["tmp_name"], $save_url . "modules/pd_images/upload_image/" . $filename)) {
                if (FILESYSTEM == 'gcs') {
                    $fullFilename = "image/modules/pd_images/upload_image/" . $filename;
                    $img = file_get_contents($save_url . "modules/pd_images/upload_image/" . $filename);
                    \Filesystem::setPath($fullFilename)->write($img);
                }

                echo $filename;
                die();
            } else {
                echo "error";
                die();
            }
        } else {
            echo '-9';
            die();
        }
    }

    public function saveImage()
    {
        $uniqid = $this->session->data['uniqid'][$_POST['productId']];
        $image = $_POST['image'];
        $decoded = base64_decode(str_replace('data:image/png;base64,', '', $image));
        file_put_contents(DIR_IMAGE . "modules/pd_images/merge_image/" . $uniqid . "__" . $_POST['side'] . ".png", $decoded);
        // Due to EC-21588 and cors issues
        if (FILESYSTEM == 'gcs') {
            $path = "image/modules/pd_images/merge_image/" . $uniqid . "__" . $_POST['side'] . ".png";
            \Filesystem::setPath($path)->put($decoded);
        }
    }

    protected function addToCart()
    {
        if (!$option || !is_array($option)) {
            $key = (int)$product_id;
        } else {
            $key = (int)$product_id . ':' . base64_encode(serialize($option));
        }

        if ((int)$qty && ((int)$qty > 0)) {
            if (!isset($this->session->data['cart'][$key])) {
                $this->session->data['cart'][$key] = (int)$qty;
            } else {
                $this->session->data['cart'][$key] += (int)$qty;
            }
        }
    }

    public function mergeImage()
    {
        $front_info = '';
        $back_info = '';
        $left_info = '';
        $right_info = '';
        try {
            if (isset($_POST['arrayobjectfront'])) {
                $front_info = $_POST['arrayobjectfront'];
            }
            if (isset($_POST['arrayobjectback'])) {
                $back_info = $_POST['arrayobjectback'];
            }
            $this->db->query("insert into " . DB_PREFIX . "tshirtdesign(pid,design_id,front_info,back_info) values('".$_POST['pid']."','" . $this->session->data['uniqid'][$_POST['pid']] . "', '" . addslashes($front_info) . "', '" . addslashes($back_info) . "')");
            echo $this->db->getLastId();
        } catch (Exception $e) {
            echo $e;
        }
    }

    public function readImage()
    {
        $img_name = $_GET['img'];
        $token = $_GET['token'];
        $img_dir = '';
        if (isset($img_name) && $img_name != '') {
            if ($token == 'u') {
                $img_dir = 'modules/pd_images/upload_image/';
            } else {
                $img_dir = 'data/upload_logo/';
            }
            $ext = explode('.', $img_name);
            if (strtolower($ext[1]) == 'jpg') {
                header("Content-type: image/jpeg");
                echo file_get_contents(DIR_IMAGE . $img_dir . $img_name);
                // echo \Filesystem::read('image/' . $img_dir . $img_name);
                die();
            } else if (strtolower($ext[1]) == 'png') {
                header("Content-type: image/png");
                echo file_get_contents(DIR_IMAGE . $img_dir . $img_name);
                // echo \Filesystem::read('image/' . $img_dir . $img_name);
                die();
            } else if (strtolower($ext[1]) == 'gif') {
                header("Content-type: image/gif");
                echo file_get_contents(DIR_IMAGE . $img_dir . $img_name);
                // echo \Filesystem::read('image/' . $img_dir . $img_name);
                die();
            }
        }
    }

    public function createTextToImage()
    {
        $stxt = explode("|", $_POST['str']);
        $count = count($stxt);
        $str = '';
        $uniqId = $_GET['guid'];

        foreach ($stxt as $ztxt) {
            if ($str != '' && $count != 0) {
                $str = $str . chr(0x0A) . $ztxt;
            } else if ($str != '' && $count = 0) {
                $str = $str . $ztxt;
            } else {
                $str = $ztxt;
            }
            $count = $count - 1;
        }

        require_once DIR_SYSTEM . "library/dompdf/src/I18N/Arabic/Glyphs.php";
        $Arabic = new \Dompdf\I18N\Arabic\Glyphs('Glyphs');        
        //
        $font = $this->session->data['special_url'] . 'font/' . $_GET['ft'] . '.ttf';
        $text_color = $_GET['col'];
        $back_color = $_GET['bg'];
        $fontsize = 50;
        $quotes = array($str);
        /*Background*/
        $r_bg = hexdec("0x" . substr($back_color, 0, 2));
        $g_bg = hexdec("0x" . substr($back_color, 2, 2));
        $b_bg = hexdec("0x" . substr($back_color, 4, 2));

        /*Text Color Change*/
        $r_col = hexdec("0x" . substr($text_color, 0, 2));
        $g_col = hexdec("0x" . substr($text_color, 2, 2));
        $b_col = hexdec("0x" . substr($text_color, 4, 2));
        //
        $pos = rand(0, count($quotes) - 1);
        $quote = wordwrap($quotes[$pos], 20);
        $dims = imagettfbbox($fontsize, 0, $font, $quote);
        
        $quote = $Arabic->utf8Glyphs($quote);
        
        $width = ($dims[4] - $dims[6]) + 10;
        $height = $dims[3] - $dims[5] + 10;
        $image = imagecreatetruecolor($width, $height);
        $bgcolor = imagecolorallocate($image, $r_bg, $g_bg, $b_bg);
        $fontcolor = imagecolorallocate($image, $r_col, $g_col, $b_col);
        imagecolortransparent($image, $bgcolor);
        imagefilledrectangle($image, 0, 0, $width, $height, $bgcolor);
        $x = 0;
        $y = $fontsize;
        imagettftext($image, $fontsize, 0, $x, $y, $fontcolor, $font, $quote);
        imagepng($image);
        $image_name = $uniqId;

        ob_start();
        imagealphablending($this->image, false);
        imagesavealpha($this->image, true);
        imagepng($image, null);
        $this->mimeType = image_type_to_mime_type(IMAGETYPE_PNG);
        $buffer = ob_get_contents();
        ob_end_clean();
        $path = 'image/modules/pd_images/upload_image/' . $image_name . '.png';

        file_put_contents(DIR_IMAGE . 'modules/pd_images/upload_image/' . $image_name . '.png', $buffer);

        // Due to EC-21588 and cors issues
        if (FILESYSTEM == 'gcs') {
            \Filesystem::setPath($path)->put($buffer);
        }
        imagedestroy($image);
    }

    public function proxy()
    {
        $rr = fopen('/var/www/expandcartdev/logs/save3.json', 'a+');
        fwrite($rr, json_encode($_GET));
        fclose($rr);
        /*header("Access-Control-Allow-Origin: *");
        header('Access-Control-Request-Method: *');
        header('Access-Control-Allow-Methods: OPTIONS, GET');*/
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            header('Access-Control-Allow-Credentials: true');
            // header('Access-Control-Max-Age: 86400');    // cache for 1 day
            header("Access-Control-Max-Age: 30");
        }
        header('Access-Control-Allow-Headers: *');
        header('Access-Control-Allow-Methods: OPTIONS, GET');

        header('Content-Type: application/javascript');
        $ctype = "image/jpeg";

        $info = pathinfo($_GET['url']);

        switch($info['extension']) {
            case "gif": $ctype="image/gif"; break;
            case "png": $ctype="image/png"; break;
            case "jpeg":
            case "jpg": $ctype="image/jpeg"; break;
            case "svg": $ctype="image/svg+xml"; break;
            default:
        }

        $img = file_get_contents($_GET['url']);

        $img64 = 'data:image/png;base64, ' . base64_encode($img);

        echo $_GET['callback'],'("' . $img64 . '")';
        exit;
    }
}

?>
