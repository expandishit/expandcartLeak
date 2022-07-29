<?php

class ModelModuleMessengerChatbotAudience extends Model
{

    public function getAudience($psid){
        $query = $this->db->query("SELECT * FROM messenger_chatbot_audience mcba LEFT JOIN messenger_chatbot_pages mcbp ON mcba.page_id = mcbp.page_id WHERE psid = $psid");
        return $query->row;
    }
}
