<?php

class ModelModuleMessengerChatbotAudience extends Model
{
    /**
     * Insert into messenger_chatbot_audience table
     *
     * @param array $data
     *
     */
    public function insertOrUpdate(array $data)
    {
        if(!$this->db->check(['messenger_chatbot_audience'], 'table') ){
            $this->db->query("CREATE TABLE IF NOT EXISTS messenger_chatbot_audience (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `psid` BIGINT(20) NOT NULL,
                `name` VARCHAR(255) DEFAULT NULL,
                `page_id` BIGINT(20) NOT NULL,
                `last_interacted_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;");
        }

        $query = $this->db->query("SELECT * FROM messenger_chatbot_audience WHERE psid = " . $data['psid']);
        if($query->num_rows == 0){
            $query = $columns = $values = [];
            $query[] = 'INSERT INTO ' . DB_PREFIX . 'messenger_chatbot_audience';
            foreach ($data as $column => $value) {
                $columns[] = $column;
                $values[] = $value;
            }
            $query[] = '(`%s`)';
            $query[] = 'VALUES';
            $query[] = "('%s')";

            $this->db->query(vsprintf(implode(' ', $query), [
                implode('`,`', $columns),
                implode("', '", $values)
            ]));
        }
        else{
            $this->db->query("UPDATE messenger_chatbot_audience SET last_interacted_at = CURRENT_TIMESTAMP WHERE psid = " . $data['psid']);
        }
    }

    public function getAudience($psid){
        $query = $this->db->query("SELECT * FROM messenger_chatbot_audience mcba LEFT JOIN messenger_chatbot_pages mcbp ON mcba.page_id = mcbp.page_id WHERE psid = $psid");
        return $query->row;
    }
}
