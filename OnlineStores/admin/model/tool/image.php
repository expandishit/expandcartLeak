<?php
class ModelToolImage extends Model
{

    public function resize($filename, $width, $height, $type = "")
    {
        $width = (int) $width;
        $height = (int) $height;

        if (defined('FILESYSTEM') && FILESYSTEM === 'gcs') {
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
            $imageBase64 = vsprintf('data:%s;base64, %s', [$info['mimetype'], base64_encode($resize)]);
            $imageSource = \Filesystem::getUrl($thumb);
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

    public function oldResize($filename, $width, $height)
    {
        if (!file_exists(DIR_IMAGE . $filename) || !is_file(DIR_IMAGE . $filename)) {
            return;
        } 
        
        $info = pathinfo($filename);
        
        $extension = $info['extension'];
        
        $old_image = $filename;
        $new_image = 'cache/' . utf8_substr($filename, 0, utf8_strrpos($filename, '.')) . '-' . $width . 'x' . $height . '.' . $extension;
        
        if (!file_exists(DIR_IMAGE . $new_image) || (filemtime(DIR_IMAGE . $old_image) > filemtime(DIR_IMAGE . $new_image))) {
            $path = '';
            
            $directories = explode('/', dirname(str_replace('../', '', $new_image)));
            
            foreach ($directories as $directory) {
                $path = $path . '/' . $directory;
                
                if (!file_exists(DIR_IMAGE . $path)) {
                    @mkdir(DIR_IMAGE . $path, 0777);
                }       
            }

            $image_dim = getimagesize(DIR_IMAGE . $old_image);

            if (!(($image_dim[0] * $image_dim[1]) > 13000000)) {

                $image = new Image(DIR_IMAGE . $old_image);
                $image->resize($width, $height);
                $image->save(DIR_IMAGE . $new_image);
            }
		}
		
		if($filename === 'no_image.jpg') {
			return HTTP_IMAGE . $new_image . '?t=' . time();
        }

        return HTTP_IMAGE . $new_image;
    }
}
