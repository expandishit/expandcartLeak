<?php
namespace Api\Models\Module;

use Psr\Container\ContainerInterface;


class WhatsappCloud
{
 
	private $load;
    private $registry;
    private $config;

    public function __construct(ContainerInterface $container)
    {
        $this->registry = $container['registry'];
        $this->config 	= $container['config'];
        $this->load 	= $container['loader'];

    }
	
	/**
     *  
	 * validate webhook signature 
     * 
     */
	public function validateSignature($body, $header_signature = ''): bool
    { 
		$this->load->model('module/whatsapp_cloud');
        return $this->registry->get('model_module_whatsapp_cloud')->validateSignature($body, $header_signature);
    }
	
	public function insertMessage($data=[])
    { 
		$this->load->model('module/whatsapp_cloud/message');
        return $this->registry->get('model_module_whatsapp_cloud_message')->insertMessage($data);
    }
	
	public function updateMessage($id=0,$data=[])
    { 
		$this->load->model('module/whatsapp_cloud/message');
        return $this->registry->get('model_module_whatsapp_cloud_message')->updateMessage($id,$data);
    }
	
	public function getMessageFilter($filters=[])
    { 
		$this->load->model('module/whatsapp_cloud/message');
        return $this->registry->get('model_module_whatsapp_cloud_message')->getMessageFilter($filters);
    }
	
	public function insertUpdateChat($data=[],$unread_increment=false)
    { 
		$this->load->model('module/whatsapp_cloud/chat');
        return $this->registry->get('model_module_whatsapp_cloud_chat')
							->insertUpdateChat($data,$unread_increment);
    }
	
	public function insertMedia($data=[])
    { 
		$this->load->model('module/whatsapp_cloud/media');
        return $this->registry->get('model_module_whatsapp_cloud_media')->insertMedia($data);
    }
	
	public function downloadMedia($media_id){
		
		$this->load->model('module/whatsapp_cloud/media');
        return $this->registry->get('model_module_whatsapp_cloud_media')->downloadFbMedia($media_id);
		
	}
	
	//currently not used here 
	public function getMediaUrl($media_id)
    { 
       $this->load->model('module/whatsapp_cloud/media');
        return $this->registry->get('model_module_whatsapp_cloud_media')->getMediaUrl($media_id);
    }
	
	//currently not used here 
	public function grabImage($url, $save_to)
    { 
	
        $this->load->model('module/whatsapp_cloud/media');
        return $this->registry->get('model_module_whatsapp_cloud_media')->grabImage($url, $save_to);
    }

}
