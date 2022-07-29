<?php
class ModelToolImage extends Model
{
    public function resize($filename, $width, $height, $type = "")
    {
        $width = (int) $width;
        $height = (int) $height;
        if (FILESYSTEM === 'gcs') {
            $info = pathinfo($filename);

            $thumb = vsprintf('%s/ecdata/stores/%s/image/cache/%s-%sx%s.%s', [
                CDN_SERVER,
                STORECODE,
                utf8_substr($filename, 0, utf8_strrpos($filename, '.')),
                $width,
                $height,
                $info['extension']
            ]);
            return $thumb;
        }

        if ($filename == '' || \Filesystem::isExists('image/' . $filename) == false) {
            $filename = 'no_image.jpg';
            $img = \Filesystem::setPath('image/' . $filename);
        }

        $info = pathinfo($filename);

        $thumb = vsprintf('image/cache/%s-%sx%s.%s', [
            utf8_substr($filename, 0, utf8_strrpos($filename, '.')),
            $width,
            $height,
            $info['extension']
        ]);

        if (\Filesystem::isExists($thumb)) {
            return \Filesystem::getUrl($thumb);
        }

        $img = \Filesystem::setPath('image/' . $filename);

        try {
            $extension=$info['extension'];
            $file = $img->get();
            $info = $img->getMetadata();

            if ($info['type'] == 'dir') {
                return \Filesystem::getUrl('image/no_image.jpg');
            }
            if($info['mimetype']=="image/webp"){
            $resize= $file->createFromWebp($img->getFullPath().$img->getPathInfo()['path'])->resize($width, $height)->save($thumb);
            }
            else{
            $resize = $file->createFromString($img->read())->resize($width, $height)->save($thumb);
            }
            //$imageSource = vsprintf('data:%s;base64, %s', [$info['mimetype'], base64_encode($resize)]);
            $thumb = vsprintf('image/cache/%s-%sx%s.%s', [
                utf8_substr($filename, 0, utf8_strrpos($filename, '.')),
                $width,
                $height,
                $extension
            ]);
            $imageSource=\Filesystem::getUrl($thumb);
        } catch (\Error $e) {
            $imageSource = \Filesystem::getUrl('image/no_image.jpg');
        } catch (\Exception $e) {
            $imageSource = \Filesystem::getUrl('image/no_image.jpg');
        }
        return $imageSource;
	}

    public function proxyResize($filename, $width, $height)
    {
        $query = http_build_query([
            'url' => \Filesystem::getUrl('image/' . $filename),
            'container' => 'focus',
            'resize_w' => $width,
            'resize_h' => $height,
        ]);
        return sprintf('https://images1-focus-opensocial.googleusercontent.com/gadgets/proxy?%s', $query);
    }

	/**
	*	
	*	@param filename string
	*	@param width 
	*	@param height
	*	@param type char [default, w, h]
	*				default = scale with white space, 
	*				w = fill according to width, 
	*				h = fill according to height
	*	
	*/
	public function oldResize($filename, $width=0, $height=0, $type = "") {
		
		//if no img
		if($filename=="" || empty($filename)) return ;
		//

		if (!file_exists(DIR_IMAGE . $filename) || !is_file(DIR_IMAGE . $filename)) {
			return;
		} 
		
		$info = pathinfo($filename);
		
		$extension = $info['extension'];
		
		$old_image = $filename;
		$new_image = 'cache/' . utf8_substr($filename, 0, utf8_strrpos($filename, '.')) . '-' . $width . 'x' . $height . $type .'.' . $extension;
		
		if (!file_exists(DIR_IMAGE . $new_image) || (filemtime(DIR_IMAGE . $old_image) > filemtime(DIR_IMAGE . $new_image))) {
			$path = '';
			
			$directories = explode('/', dirname(str_replace('../', '', $new_image)));
			
			foreach ($directories as $directory) {
				$path = $path . '/' . $directory;
				
				if (!file_exists(DIR_IMAGE . $path)) {
					@mkdir(DIR_IMAGE . $path, 0777);
				}		
			}

			list($width_orig, $height_orig) = getimagesize(DIR_IMAGE . $old_image);

			if ($width_orig != $width || $height_orig != $height) {
				$image = new Image(DIR_IMAGE . $old_image);
				if($width==0 && $height==0){
					$image->save(DIR_IMAGE . $new_image);
				}else{
					$image->resize($width, $height, $type);
					$image->save(DIR_IMAGE . $new_image);
				}
			} else {
				copy(DIR_IMAGE . $old_image, DIR_IMAGE . $new_image);
			}
		}

		if($filename === 'no_image.jpg')
			return HTTP_IMAGE . $new_image . '?t=' . time();


        return HTTP_IMAGE . $new_image;
//		if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
//			return $this->config->get('config_ssl') . 'image/' . STORECODE . '/' . $new_image;
//		} else {
//			return $this->config->get('config_url') . 'image/' . STORECODE . '/' . $new_image;
//		}
	}

	public function imageExist($image){
		if ($image == '' || \Filesystem::isExists('image/' . $image) == false) {
            return false;
        }else{
			return true;
		}
	}
}
?>