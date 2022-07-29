<?php 
class ControllerModuleCustomerNotifications extends Controller
{
  
    public function getCustomerNotifications()
    {       
            $this->language->load_json('module/customer_notifications');
            $this->load->model('module/customer_notifications');
            
            $notification_content='';

            $notifications = $this->model_module_customer_notifications->getCustomerNotifications();

            if(!empty($notifications)) 
            {
                
            foreach ($notifications as $notification) 
            {
            if ($notification['read_status'] == 0) $readClass='unread'; else  $readClass='';
               
                if($notification['notification_module']=='orders')
                {   
                    $button=$this->language->get('view_order');
                    $url=$this->url->link('account/order/info', 'order_id=' . $notification['notification_module_id'], 'SSL');
                    if($notification['notification_module_code']=='orders_update_status') 
                    {
                        $unsiralizeText=unserialize($notification['notification_text']);
                        $statusID=$unsiralizeText[1];
                        $order_status_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_status WHERE order_status_id = '" . (int)$statusID . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");
                        if ($order_status_query->num_rows) {
                            $order_status= $order_status_query->row['name'];
                        }
                        else{$order_status='';}
                       
                        $notification_text= $this->language->get('notification_order_status_text').'<strong>'.$order_status.'</strong>';
                    }
                }

                $notification_content .= '
                <div class="notify-box  '.$readClass.'" onclick="markAsRead('.$notification['id'].')"> 
                <i class="fa '.$notification['icon'].'"></i>
                <div class="info">
                    <p class="title">'.$notification_text.'</p>
                    <a href="'.$url.'" class="link notify-btn"  onclick="markAsRead('.$notification['id'].')">'.$button.'</a>	
                    <p class="date"> '. $notification['created_at'].' </p>
                </div>
		    	</div> 
                ';
                }

            }else{
                $notification_content.='<p class="not_found">'.$this->language->get('no_notifications').'</p> ';
            }
         
            $unreadCount =  $this->model_module_customer_notifications->getUnreadCustomerNotificationsCount();
            $data = array(
            'notification' => $notification_content,
            'unread_notification'  => $unreadCount
            );

         echo json_encode($data);
    }
     public function markAsRead()
     {

        $this->load->model('module/customer_notifications');
        $this->model_module_customer_notifications->markAsRead($this->request->post['id']);
        
     }

}