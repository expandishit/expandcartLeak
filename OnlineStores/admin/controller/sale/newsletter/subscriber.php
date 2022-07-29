<?php

class ControllerSaleNewsletterSubscriber extends Controller
{
	public function dtDelete()
	{
        $this->load->model('sale/newsletter/subscriber');

        if ( !isset($this->request->post['id']) )
        {
            return false;
        }

        $id_s = $this->request->post['id'];

	    if ( is_array($id_s) )
        { 
            foreach ($id_s as $subscriber_id)
            {
                $this->model_sale_newsletter_subscriber->deleteSubscriber( (int) $subscriber_id );                   
            }
        }
        else
        {
            $subscriber_id = (int) $id_s;            
            $this->model_sale_newsletter_subscriber->deleteSubscriber( $subscriber_id );
        	$id_s = is_array($id_s) ? $id_s : [$id_s];        
		}
        $result_json['success'] = '1';
        $result_json['success_msg'] = $this->language->get('text_success');

        $this->response->setOutput(json_encode($result_json));
        // return;
	}
}
