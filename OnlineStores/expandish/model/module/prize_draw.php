<?php
class ModelModulePrizeDraw extends Model
{
    private function getSettings(){
        return $this->config->get('prize_draw_module');
    }

    public function isActive()
    {
        $settings = $this->getSettings();

        if (isset($settings) && $settings['status'] == 1) {
            return true;
        }

        return false;
    }

    public function getProductPrize($product_id) {
        $today = date('Y-m-d');

        $query = $this->db->query("SELECT prize_draw_id FROM " . DB_PREFIX . "prdw_product_to_prize  WHERE product_id = '" . (int)$product_id . "'");
        $prize_draw = $query->row;

        if(isset($prize_draw['prize_draw_id'])){
            $query = $this->db->query("SELECT p.id, p.image, p.start_date, p.end_date, pd.title, pd.short_description, pd.description FROM " . DB_PREFIX . "prdw_prize_draw p 
                                       LEFT JOIN " . DB_PREFIX . "prdw_prize_draw_description pd on (p.id = pd.prize_draw_id) 
                                       WHERE pd.language_id = '" . (int) $this->config->get('config_language_id') . "' 
                                       AND p.id = '" . (int)$prize_draw['prize_draw_id'] . "' 
                                       AND (p.dates_status = 0 OR (p.dates_status = 1 AND p.start_date <= '" . $today . "' AND end_date >= '" . $today . "')) 
                                       AND status=1");
            return $query->row;
        }

        return false;
    }

    public function getOrderedCount($product_id){
        $settings = $this->getSettings();
        $complete_status = $settings['complete_status_id'];

        $ordered_quantity = 0;
        if($complete_status){
            $query = $this->db->query("SELECT quantity FROM " . DB_PREFIX . "order_product op LEFT JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id) WHERE  op.product_id = " . (int)$product_id . " AND o.order_status_id = " . (int)$complete_status);

            if($query->num_rows){
                foreach($query->rows as $prod){
                    $ordered_quantity += $prod['quantity'];
                }
            }
        }
        return $ordered_quantity;
    }
}
