<?php
class ModelSectionPrize extends Model {
	public function getFeaturedPrizes($thumb_width=0, $thumb_height=0) {
        $this->load->model('module/prize_draw', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');
        $this->load->model('tool/image', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');

        $prizes = array();

        $today = date('Y-m-d');
        $query = $this->db->query("SELECT p.*, pd.title FROM " . DB_PREFIX . "prdw_prize_draw p 
                                   LEFT JOIN " . DB_PREFIX . "prdw_prize_draw_description pd on (p.id = pd.prize_draw_id) 
                                   WHERE pd.language_id = '" . (int) $this->config->get('config_language_id') . "' 
                                   AND (p.dates_status = 0 OR (p.dates_status = 1 AND p.start_date <= '" . $today . "' AND end_date >= '" . $today . "')) 
                                   AND status=1");
        $prizes_list = $query->rows;

        foreach ($prizes_list as $prize) {
            $description = substr(strip_tags(htmlspecialchars_decode($prize['description'])), 0, 100);

            if ($prize['image']) {
                $image = $this->model_tool_image->resize($prize['image'], $thumb_width, $thumb_height);
            } else {
                $image = false;
            }

            $prizes[] = array(
                'prize_id'       => $prize['id'],
                'thumb'   	        => $image,
                'title'    	        => $prize['title'],
                'short_description' => $description
            );
        }

        return $prizes;
	}
}
?>