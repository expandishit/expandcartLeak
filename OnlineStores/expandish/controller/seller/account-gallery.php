<?php

class ControllerSellerAccountGallery extends ControllerSellerAccount {
	public function index() 
	{
		$gallery_status = $this->config->get('msconf_allow_seller_image_gallery');

		if(!$gallery_status)
			$this->redirect($this->url->link('seller/account-dashboard', '', 'SSL'));
		
		$this->document->addScript('expandish/view/javascript/fileupload/js/plugins/piexif.js');
		$this->document->addScript('expandish/view/javascript/fileupload/js/plugins/sortable.js');
		$this->document->addScript('expandish/view/javascript/fileupload/js/fileinput.js');
		$this->document->addScript('expandish/view/javascript/fileupload/themes/fas/theme.js');
		$this->document->addScript('expandish/view/javascript/fileupload/themes/explorer-fas/theme.js');
		// $this->document->addScript('https://code.jquery.com/jquery-3.3.1.min.js');
		$this->document->addScript('https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.bundle.min.js" crossorigin="anonymous');

		$this->document->addStyle('https://use.fontawesome.com/releases/v5.5.0/css/all.css');
		$this->document->addStyle('expandish/view/javascript/fileupload/css/fileinput.css');
		$this->document->addStyle('expandish/view/javascript/fileupload/themes/explorer-fas/theme.css');
		
		$seller = $this->MsLoader->MsSeller->getSeller($this->customer->getId());

		if ($seller) {
			switch ($seller['ms.seller_status']) {
				case MsSeller::STATUS_UNPAID:
					$this->data['statusclass'] = 'attention';
					break;				
				case MsSeller::STATUS_ACTIVE:
					$this->data['statusclass'] = 'success';
					break;
				case MsSeller::STATUS_DISABLED:
				case MsSeller::STATUS_DELETED:
					$this->data['statusclass'] = 'warning';
					break;
			}

			$seller_gallery = $this->MsLoader->MsSeller->getGalleryImgs($seller['seller_id']);

			$images_list = [];
			$seller_captions = [];

			$delUrl = $this->url->link('seller/account-gallery/jxDeleteImage', '', 'SSL');
			foreach ($seller_gallery as $image) {
				$images_list[]     = '"'.$this->MsLoader->MsFile->resizeImage($image['image'], 520, 300).'"';
				$images_captions[] = '{caption: "", size: "", width: "", url: "'.$delUrl.'", key: '.$image['id'].'}';
			}

			$this->data['seller_images']   = implode(',', $images_list);
			$this->data['seller_captions'] = implode(',', $images_captions);

			if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
				$custom_server =  HTTPS_SELLER_FILES.$this->customer->getId() .'/';
			} else {
				$custom_server =  HTTP_SELLER_FILES.$this->customer->getId() .'/';
			}
		}
		
		$this->data['seller_validation'] = $this->config->get('msconf_seller_validation');
		$this->data['link_back'] = $this->url->link('account/account', '', 'SSL');
		$this->data['seller_required_fields'] = $this->config->get('msconf_seller_required_fields');
		$this->data['seller_show_fields'] = $this->config->get('msconf_seller_show_fields');
		
		//Get Seller title from setting table
		$this->load->model('seller/seller');
		$seller_title = $this->model_seller_seller->getSellerTitle();
		$product_title = $this->model_seller_seller->getProductTitle();
		$products_title = $this->model_seller_seller->getProductsTitle();
		

		$this->document->setTitle(
			sprintf( $this->language->get('ms_account_sellergallery_breadcrumbs') , $seller_title )
		);
		
		$this->data['breadcrumbs'] = $this->MsLoader->MsHelper->setBreadcrumbs(array(
			array(
				'text' => $this->language->get('text_account'),
				'href' => $this->url->link('account/account', '', 'SSL'),
			),
			array(
				'text' => sprintf( $this->language->get('ms_account_dashboard_breadcrumbs'), $seller_title ),
				'href' => $this->url->link('seller/account-dashboard', '', 'SSL'),
			),
			array(
				'text' => sprintf( $this->language->get('ms_account_sellergallery_breadcrumbs') , $seller_title ),
				'href' => $this->url->link('seller/account-gallery', '', 'SSL'),
			)
		));

		list($this->template, $this->children) = $this->MsLoader->MsHelper->loadTemplate('account-gallery');
		$this->response->setOutput($this->render());
	}

	public function getConfig()
	{
		$lang=$this->config->get('config_language');
		return $this->response->setOutput(json_encode($lang));
	}

	public function jxUploadImage() 
	{
		$json = array();
		$file = array();
		
		$json['errors'] = $this->MsLoader->MsFile->checkPostMax($_POST, $_FILES);

		if ($json['errors']) {
			return $this->response->setOutput(json_encode($json));
		}
		
		$msconf_temp_image_path       = $this->config->get('msconf_temp_image_path');

		//Get Seller Info
		$seller = $this->MsLoader->MsSeller->getSeller($this->customer->getId());
		if ($seller && $seller['ms.seller_status'] != MsSeller::STATUS_DISABLED && $seller['ms.seller_status'] != MsSeller::STATUS_DELETED) {
			$seller_id = $seller['seller_id'];
		}else{
			$responds = ['error' => array_merge($json['errors'], $errors)];
			return $this->response->setOutput(json_encode($responds));
		}
		/////////////////

		foreach ($_FILES as $file) {   
			$fileSize = $file['size']; 

			$errors = $this->MsLoader->MsFile->checkImage($file);

			if ($errors) {
				$responds = ['error' => array_merge($json['errors'], $errors)];
				return $this->response->setOutput(json_encode($responds));
			} else {
				
				$fileName = $this->MsLoader->MsFile->uploadImage($file);

				$thumbUrl = $this->MsLoader->MsFile->resizeImage(
														$msconf_temp_image_path.$fileName, 
														250, 
														250
													);

				//Save DB image
				$image = $this->MsLoader->MsFile->moveImage($fileName);

				if($image){
					$this->MsLoader->MsSeller->addGalleryImg(['seller_id' => $seller_id, 'image' => $image]);
				}

				//////////////

				$responds = [
	                //'chunkIndex' => $index,         // the chunk index processed
	                'initialPreview' => $thumbUrl, // the thumbnail preview data (e.g. image)
	                'initialPreviewConfig' => [
	                    [
	                        'type' => 'image',      // check previewTypes (set it to 'other' if you want no content preview)
	                        'caption' => $fileName, // caption
	                        'key' => '',       // keys for deleting/reorganizing preview
	                        'fileId' => '',    // file identifier
	                        'size' => $fileSize,    // file size
	                        'zoomData' => $fileName, // separate larger zoom data
	                    ]
	                ],
	                'append' => true
	            ];
				
	            return $this->response->setOutput(json_encode($responds));
			}			
		}
		
		$responds = ['error' => 'No file found'];
		return $this->response->setOutput(json_encode($responds));
	}
	////////////////////

	public function jxDeleteImage() 
	{
		$id = $_POST['key'];

		$image = $this->MsLoader->MsSeller->getGalleryImg($id);

		if( $image && $this->MsLoader->MsSeller->removeGalleryImg($id) ){
			$this->MsLoader->MsFile->deleteImage($image['image']);
		}
	}

	public function jxUpdateSellerVideos()
	{

		$seller = $this->MsLoader->MsSeller->getSeller($this->customer->getId());
		if($seller){
			$this->MsLoader->MsSeller->updateSellerVideos(['seller_id' => $seller['seller_id'], 'videoIDs' => $this->request->post['videoIDs']]);
			$result = $this->MsLoader->MsSeller->getSellerVideos($seller['seller_id']);
			return $this->response->setOutput(json_encode($result));
		}
	}

	public function jxGetSellerVideos()
	{
		$seller = $this->MsLoader->MsSeller->getSeller($this->customer->getId());
		if($seller){
			$videoIDs = array();
			$sellerData = $this->MsLoader->MsSeller->getSellerVideos($seller['seller_id']);
			foreach($sellerData as $data){
				$videoIDs[] = $data['video_id'];
			}
			return $this->response->setOutput(json_encode($videoIDs));
		}
	}
}
?>
