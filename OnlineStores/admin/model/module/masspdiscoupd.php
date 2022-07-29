<?php


class ModelModuleMasspdiscoupd extends Model
{

    public function dtProductFilterHandler($start=0, $length=10, $filter = null, $orderColumn="product_id", $orderType="ASC") {

        $this->language->load('module/masspdiscoupd');

        $plus_join="";
        $plus_where="";
        $plus_select="";
        $query = "";
        $this->load->model('setting/extension');
        $installedextensions = $this->model_setting_extension->getInstalled('module');
        $isDedicatedDomainsInstalled = in_array("dedicated_domains",$installedextensions);
        if($isDedicatedDomainsInstalled){
            $query="SELECT p.product_id, p.model, p.price, p.quantity, p.status, pd.name,pdisc.dedicated_domains as pdiscDomains,pspec.dedicated_domains as pspecDomains,dom.domain FROM " . DB_PREFIX . "product p
                      LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)
                      LEFT JOIN " . DB_PREFIX . "product_discount pdisc ON (p.product_id = pdisc.product_id)
                      LEFT JOIN " . DB_PREFIX . "product_special pspec ON (p.product_id = pspec.product_id)
                      LEFT JOIN " . DB_PREFIX . "dedicated_domains dom ON (pspec.dedicated_domains = dom.domain_id OR pdisc.dedicated_domains = dom.domain_id )
                      GROUP BY p.product_id";
        }
        else{
            $query="SELECT p.product_id, p.model, p.price, p.quantity, p.status, pd.name FROM " . DB_PREFIX . "product p
                      LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)
                      LEFT JOIN " . DB_PREFIX . "product_discount pdisc ON (p.product_id = pdisc.product_id)
                      LEFT JOIN " . DB_PREFIX . "product_special pspec ON (p.product_id = pspec.product_id)
                      GROUP BY p.product_id";
        }

        $total = $this->db->query($query)->num_rows;

        $plus_join.=" LEFT JOIN " . DB_PREFIX . "product_discount pdisc ON (p.product_id = pdisc.product_id)";
        $plus_join.=" LEFT JOIN " . DB_PREFIX . "product_special pspec ON (p.product_id = pspec.product_id)";

        if($isDedicatedDomainsInstalled){
            $plus_join.=" LEFT JOIN " . DB_PREFIX . "dedicated_domains dom ON (pspec.dedicated_domains = dom.domain_id OR pdisc.dedicated_domains = dom.domain_id )";
            $plus_select = " ,pdisc.dedicated_domains as pdiscDomains,pspec.dedicated_domains as pspecDomains,dom.domain  ";
        }


        if (isset($filter) and count($filter) != 0) { /// data filters
            if (isset($filter['product_category'])) { // categories
                $plus_join .=" LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)";
                if ($plus_where=="") { $prfx=" WHERE "; } else { $prfx=" AND "; }
                foreach ($filter['product_category'] as $key => &$filterElem) {
                    $filterElem = htmlspecialchars($filterElem);
                }
                if (in_array(0,$filter['product_category'])) {
                    $plus_join.=" LEFT JOIN " . DB_PREFIX . "product_to_category p2c0x ON (p.product_id = p2c0x.product_id)";
                    $plus_where=$prfx."(p2c.category_id IN ('" .implode("', '", $filter['product_category']). "') OR p2c0x.category_id IS NULL)";
                } else {
                    $plus_where=$prfx."p2c.category_id IN('" .implode("', '", $filter['product_category']). "')";
                }
            }

            if (isset($filter['manufacturer_ids'])) { // manufacturers
                if ($plus_where=="") { $prfx=" WHERE "; } else { $prfx=" AND "; }
                foreach ($filter['manufacturer_ids'] as $key => &$filterElem) {
                    $filterElem = htmlspecialchars($filterElem);
                }
                $plus_where.=$prfx."p.manufacturer_id IN ('" .implode("', '", $filter['manufacturer_ids']). "')";
            }
            
            if (isset($filter['seller_ids']) && !empty($filter['seller_ids'])) { // sellers
                $prfx = $plus_where=="" ? " WHERE " : " AND ";
                foreach ($filter['seller_ids'] as $key => &$value) {
                    $value = htmlspecialchars($value);
                }
                $plus_where.=$prfx."p.product_id IN (
                    (
                        SELECT msp.product_id FROM " . DB_PREFIX . "ms_product msp WHERE msp.seller_id IN ('" .implode("', '", $filter['seller_ids']) . "')
                    )
                )";
            }

            if (isset($filter['filters_ids'])) { // filters
                $plus_join.=" LEFT JOIN " . DB_PREFIX . "product_filter prfil ON (p.product_id = prfil.product_id)";
                if ($plus_where=="") { $prfx=" WHERE "; } else { $prfx=" AND "; }
                foreach ($filter['product_category'] as $key => &$filterElem) {
                    $filterElem = htmlspecialchars($filterElem);
                }
                if (in_array(0,$filter['filters_ids'])) {
                    $plus_join.=" LEFT JOIN " . DB_PREFIX . "product_filter pf0x ON (p.product_id = pf0x.product_id)";
                    $plus_where.=$prfx."(prfil.filter_id IN ('" .implode("', '", $filter['filters_ids']). "') OR pf0x.filter_id IS NULL)";
                } else {
                    $plus_where.=$prfx."prfil.filter_id IN ('" .implode("', '", $filter['filters_ids']). "')";
                }
            }

            if ($filter['price_mmarese']!="0") { // price greater than or equal to
                if ($plus_where=="") { $prfx=" WHERE "; } else { $prfx=" AND "; }
                $plus_where.=$prfx."p.price >= '" . (float)$filter['price_mmarese'] . "'";
            }

            if ($filter['price_mmicse']!="39499") { // price less than or equal to
                if ($plus_where=="") { $prfx=" WHERE "; } else { $prfx=" AND "; }
                $plus_where.=$prfx."p.price <= '" . (float)$filter['price_mmicse'] . "'";
            }


            if ($filter['d_cust_group_filter']!="any") { // cusomer group
                if ($plus_where=="") { $prfx=" WHERE "; } else { $prfx=" AND "; }
                $plus_where.=$prfx."pdisc.customer_group_id = '" . (int)$filter['d_cust_group_filter'] . "'";
            }
            if ($filter['d_price_mmicse']!="100") { // less than or equal to
                if ($plus_where=="") { $prfx=" WHERE "; } else { $prfx=" AND "; }
                $plus_where.=$prfx."(pdisc.price BETWEEN '" . (float)$filter['d_price_mmarese'] . "' AND '" . (float)$filter['d_price_mmicse'] . "'";
                $plus_where.=" OR pspec.price BETWEEN '" . (float)$filter['d_price_mmarese'] . "' AND '" . (float)$filter['d_price_mmicse'] . "')";
            }else if ($filter['d_price_mmarese']!="0"){
                if ($plus_where=="") { $prfx=" WHERE "; } else { $prfx=" AND "; }
                $plus_where.=$prfx."(pdisc.price >= '" . (float)$filter['d_price_mmarese'] . "'";
                $plus_where.=" OR pspec.price >= '" . (float)$filter['d_price_mmarese'] . "')";

            }


            if ($filter['tax_class_filter']!="any") { // tax class
                if ($plus_where=="") { $prfx=" WHERE "; } else { $prfx=" AND "; }
                $plus_where.=$prfx."p.tax_class_id = '" . (int)$filter['tax_class_filter'] . "'";
            }

            if ($filter['stock_mmarese']!="0") { // stock greater than or equal to
                if ($plus_where=="") { $prfx=" WHERE "; } else { $prfx=" AND "; }
                $plus_where.=$prfx."p.quantity >= '" . (int)$filter['stock_mmarese'] . "'";
            }

            if ($filter['stock_mmicse']!="50") { // stock less than or equal to
                if ($plus_where=="") { $prfx=" WHERE "; } else { $prfx=" AND "; }
                $plus_where.=$prfx."p.quantity <= '" . (int)$filter['stock_mmicse'] . "'";
            }

            if ($filter['min_q_mmarese']!="0") { // Minimum Quantity greater than or equal to
                if ($plus_where=="") { $prfx=" WHERE "; } else { $prfx=" AND "; }
                $plus_where.=$prfx."p.minimum >= '" . (int)$filter['min_q_mmarese'] . "'";
            }

            if ($filter['min_q_mmicse']!="50") { // Minimum Quantity less than or equal to
                if ($plus_where=="") { $prfx=" WHERE "; } else { $prfx=" AND "; }
                $plus_where.=$prfx."p.minimum <= '" . (int)$filter['min_q_mmicse'] . "'";
            }

            if ($filter['stock_status_filter']!="any") { // Subtract Stock
                if ($plus_where=="") { $prfx=" WHERE "; } else { $prfx=" AND "; }
                $plus_where.=$prfx."p.stock_status_id = '" . (int)$filter['stock_status_filter'] . "'";
            }

            if ($filter['subtract_filter']!="any") { // Out Of Stock Status
                if ($plus_where=="") { $prfx=" WHERE "; } else { $prfx=" AND "; }
                $plus_where.=$prfx."p.subtract = '" . (int)$filter['subtract_filter'] . "'";
            }

            if ($filter['shipping_filter']!="any") { // Requires Shipping
                if ($plus_where=="") { $prfx=" WHERE "; } else { $prfx=" AND "; }
                $plus_where.=$prfx."p.shipping = '" . (int)$filter['shipping_filter'] . "'";
            }

            if ($filter['date_mmarese']!="") { // Date Available greater than or equal to
                if ($plus_where=="") { $prfx=" WHERE "; } else { $prfx=" AND "; }
                $date_available = explode(' - ', $filter['date_mmarese']);
                $date_available[0] = date('Y-m-d', strtotime(str_replace('-', '/', $date_available[0])));
                $date_available[1] = date('Y-m-d', strtotime(str_replace('-', '/', $date_available[1])));
                $plus_where.=$prfx."p.date_available BETWEEN '" . $this->db->escape($date_available[0]) . "' AND '" . $this->db->escape($date_available[1]) . "' + INTERVAL 1 DAY";
              
                // $plus_where.=$prfx."p.date_available >= '" . $this->db->escape($filter['date_mmarese']) . "'";
            }

            // if ($filter['date_mmicse']!="") { // Date Available less than or equal to
            //     if ($plus_where=="") { $prfx=" WHERE "; } else { $prfx=" AND "; }
                
            //     // $plus_where.=$prfx."p.date_available <= '" . $this->db->escape($filter['date_mmicse']) . "'";
            // }

            if ($filter['date_added_mmarese']!="") { // Date added greater than or equal to
                if ($plus_where=="") { $prfx=" WHERE "; } else { $prfx=" AND "; }
                $date_added = explode(' - ', $filter['date_added_mmarese']);
                $date_added[0] = date('Y-m-d', strtotime(str_replace('-', '/', $date_added[0])));
                $date_added[1] = date('Y-m-d', strtotime(str_replace('-', '/', $date_added[1])));
                $plus_where.=$prfx."p.date_added BETWEEN '" . $this->db->escape($date_added[0]) . "' AND '" . $this->db->escape($date_added[1]) . "' + INTERVAL 1 DAY";
            }

            // if ($filter['date_added_mmicse']!="") { // Date added less than or equal to
            //     if ($plus_where=="") { $prfx=" WHERE "; } else { $prfx=" AND "; }
            //     $plus_where.=$prfx."p.date_added <= '" . $this->db->escape($filter['date_added_mmicse']) . "'";
            // }

            if ($filter['date_modified_mmarese']!="") { // Date modified greater than or equal to
                if ($plus_where=="") { $prfx=" WHERE "; } else { $prfx=" AND "; }
                $date_added = explode(' - ', $filter['date_modified_mmarese']);
                $plus_where.=$prfx."p.date_modified BETWEEN '" . $this->db->escape($date_added[0]) . "' AND '" . $this->db->escape($date_added[1]) . "' + INTERVAL 1 DAY";
         
                // $plus_where.=$prfx."p.date_modified >= '" . $this->db->escape($filter['date_modified_mmarese']) . "'";
            }

            // if ($filter['date_modified_mmicse']!="") { // Date modified less than or equal to
            //     if ($plus_where=="") { $prfx=" WHERE "; } else { $prfx=" AND "; }
            //     $plus_where.=$prfx."p.date_modified <= '" . $this->db->escape($filter['date_modified_mmicse']) . "'";
            // }


            if ($filter['prod_status']!="any") { // status
                if ($plus_where=="") { $prfx=" WHERE "; } else { $prfx=" AND "; }
                $plus_where.=$prfx."p.status = '" . (int)$filter['prod_status'] . "'";
            }

            if ($filter['store_filter']!="any") { // store
                $plus_join.=" LEFT JOIN " . DB_PREFIX . "product_to_store pts ON (p.product_id = pts.product_id)";
                if ($plus_where=="") { $prfx=" WHERE "; } else { $prfx=" AND "; }
                $plus_where.=$prfx."pts.store_id = '" . (int)$filter['store_filter'] . "'";
            }

            if ($filter['filter_attr']!="any") { // attribute
                $plus_join.=" LEFT JOIN " . DB_PREFIX . "product_attribute pattr ON (p.product_id = pattr.product_id)";
                if ($plus_where=="") { $prfx=" WHERE "; } else { $prfx=" AND "; }
                $plus_where.=$prfx."pattr.attribute_id = '" . (int)$filter['filter_attr'] . "'";
            }

            if ($filter['filter_opti']!="any") { // option
                $plus_join.=" LEFT JOIN " . DB_PREFIX . "product_option po ON (p.product_id = po.product_id)";
                if ($plus_where=="") { $prfx=" WHERE "; } else { $prfx=" AND "; }
                $plus_where.=$prfx."po.option_id = '" . (int)$filter['filter_opti'] . "'";
            }

            if ($filter['filter_attr_val']!="") { // attribute value (text)
                if ($filter['filter_attr']=="any") { $plus_join.=" LEFT JOIN " . DB_PREFIX . "product_attribute pattr ON (p.product_id = pattr.product_id)"; }
                if ($plus_where=="") { $prfx=" WHERE "; } else { $prfx=" AND "; }
                $plus_where.=$prfx."pattr.text LIKE '%" . $this->db->escape($filter['filter_attr_val']) . "%'";
            }

            if ($filter['filter_opti_val']!="any") { // option value
                $plus_join.=" LEFT JOIN " . DB_PREFIX . "product_option_value pov ON (p.product_id = pov.product_id)";
                if ($plus_where=="") { $prfx=" WHERE "; } else { $prfx=" AND "; }
                $plus_where.=$prfx."pov.option_value_id = '" . (int)$filter['filter_opti_val'] . "'";
            }

            if ($filter['filter_name']!="") { // part of name
                if ($plus_where=="") { $prfx=" WHERE "; } else { $prfx=" AND "; }
                if(version_compare(VERSION, '1.5.4.1', '>')) {
                    $plus_where.=$prfx."pd.name LIKE '%" . $this->db->escape($filter['filter_name']) . "%'";
                } elseif (version_compare(VERSION, '1.5.1.2', '>')) {
                    $plus_where.=$prfx."LCASE(pd.name) LIKE '%" . $this->db->escape(utf8_strtolower($filter['filter_name'])) . "%'";
                } else {
                    $plus_where.=$prfx."LCASE(pd.name) LIKE '%" . $this->db->escape(strtolower($filter['filter_name'])) . "%'";
                }
            }

            if ($filter['filter_model']!="") { // part of model
                if ($plus_where=="") { $prfx=" WHERE "; } else { $prfx=" AND "; }
                if(version_compare(VERSION, '1.5.4.1', '>')) {
                    $plus_where.=$prfx."p.model LIKE '%" . $this->db->escape($filter['filter_model']) . "%'";
                } elseif (version_compare(VERSION, '1.5.1.2', '>')) {
                    $plus_where.=$prfx."LCASE(p.model) LIKE '%" . $this->db->escape(utf8_strtolower($filter['filter_model'])) . "%'";
                } else {
                    $plus_where.=$prfx."LCASE(p.model) LIKE '%" . $this->db->escape(strtolower($filter['filter_model'])) . "%'";
                }
            }


            if ($filter['filter_tag']!="") { // tag
                if ($plus_where=="") { $prfx=" WHERE "; } else { $prfx=" AND "; }
                if(version_compare(VERSION, '1.5.3.1', '>')) {
                    $plus_where.=$prfx."LCASE(pd.tag) LIKE '%" . $this->db->escape(utf8_strtolower($filter['filter_tag'])) . "%'";
                } else {
                    $plus_join.=" LEFT JOIN " . DB_PREFIX . "product_tag ptag ON (p.product_id = ptag.product_id)";
                    $plus_where.=$prfx."LCASE(ptag.tag) LIKE '%" . $this->db->escape(utf8_strtolower($filter['filter_tag'])) . "%'";
                }
            }
            if ($plus_where=="") { $prfx=" WHERE "; } else { $prfx=" AND "; }
            $plus_where.=$prfx."pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";


            $query="SELECT p.product_id, p.model, p.price, p.quantity, p.status, pd.name".$plus_select." FROM " . DB_PREFIX . "product p
                      LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)"
                      .$plus_join.$plus_where."
                      GROUP BY p.product_id";


        } /// end data filters

        // var_dump($query);
        // die();
        $totalFiltered = $this->db->query($query)->num_rows;

        if($orderColumn)
            $query .= " ORDER by {$orderColumn} {$orderType}";

        if($length != -1) {
            $query .= " LIMIT " . $start . ", " . $length;
        }

        $results = $this->db->query($query)->rows;
        $this->load->language('module/reward_points_pro');

        foreach ($results as $key => $result)
        {
            $results[$key]['domain'] = $results[$key]['domain'] ? $results[$key]['domain'] : "";
            $results[$key]['pdiscDomains'] = $results[$key]['pdiscDomains'] ? $results[$key]['pdiscDomains'] : "";
            $results[$key]['pspecDomains'] = $results[$key]['pspecDomains'] ? $results[$key]['pspecDomains'] : "";
            $results[$key]['status_text'] = $result['status'] ? $this->language->get('masstxt_enabled') : $this->language->get('masstxt_disabled');
        }

        $data = array (
            'data' => $results,
            'total' => $total,
            'totalFiltered' => $totalFiltered
        );
        return $data;
    }

}




