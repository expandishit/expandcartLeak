<?php
class MsFile extends Model {
	private function _isNewUpload($fileName) {
		// return file_exists(DIR_IMAGE . $this->config->get('msconf_temp_image_path') . $fileName) || file_exists(DIR_DOWNLOAD . $this->config->get('msconf_temp_download_path') . $fileName);

        if (
            \Filesystem::isExists('image/' . $this->config->get('msconf_temp_image_path') . $fileName) ||
            \Filesystem::isExists('download/' . $this->config->get('msconf_temp_download_path') . $fileName)
        ) {
            return true;
        }

        return false;
	}

	// ***FUNCTION***: checks whether file already exists and proposes a new name for a file
	function _checkExistingFiles($path, $filename) {
		$newFilename = $filename;
		$i = 1;

		while (\Filesystem::isExists($path . '/' . $newFilename)) {
			$newFilename = substr($filename, 0, strrpos($filename, '.')) . "-" . $i++ . substr($filename, strrpos($filename, '.'));
		}

		return $newFilename;
	}

    /**
     * @deprecated
     */
	private function _checkExistingFilesSizes($path, $filename, $md5) {
		$newFilename = $filename;
		$i = 1;

		while ( file_exists($path . '/' . $newFilename) && ($filesize != filesize($path . '/' . $newFilename)) ) {
			$newFilename = substr($filename, 0, strrpos($filename, '.')) . "-" . $i++ . substr($filename, strrpos($filename, '.'));
		}

		return $newFilename;
	}

    /**
     * @deprecated
     */
	private function _checkExistingFilesMd5($path, $filename, $md5) {
		$newFilename = $filename;
		$i = 1;

		while ( file_exists($path . '/' . $newFilename) && ($md5 !== md5_file($path . '/' . $newFilename)) ) {
			$newFilename = substr($filename, 0, strrpos($filename, '.')) . "-" . $i++ . substr($filename, strrpos($filename, '.'));
		}

		return $newFilename;
	}

	public function checkPostMax($post, $files) {
		$errors = array();

		if (empty($post) || empty($files)) {
			$POST_MAX_SIZE = ini_get('post_max_size');
			$mul = substr($POST_MAX_SIZE, -1);
			$mul = ($mul == 'M' ? 1048576 : ($mul == 'K' ? 1024 : ($mul == 'G' ? 1073741824 : 1)));
			if ($_SERVER['CONTENT_LENGTH'] > $mul * (int)$POST_MAX_SIZE && $POST_MAX_SIZE) {
				$errors[] = $this->language->get('ms_error_file_size');
			} else {
				$errors[] = $this->language->get('ms_error_file_upload_error');
			}
		}

		return $errors;
	}

	public function checkFile($file, $allowed_filetypes) {
		$errors = array();

		$filetypes = explode(',', $allowed_filetypes);
		$filetypes = array_map('strtolower', $filetypes);
		$filetypes = array_map('trim', $filetypes);

		$ext = explode('.', $file['name']);
		$ext = end($ext);

		if (!in_array(strtolower($ext),$filetypes)) {
			$errors[] = $this->language->get('ms_error_file_extension');
		}

		if ($file["error"] != UPLOAD_ERR_OK) {
			if ($file["error"] == UPLOAD_ERR_INI_SIZE || $file["error"] == UPLOAD_ERR_FORM_SIZE) {
				$errors[] = $this->language->get('ms_error_file_size');
			} else {
				$errors[] = $this->language->get('ms_error_file_upload_error');
			}
		} else {
			// todo filename size
			if (mb_strlen($file['name']) > 150) {
				$errors[] = $this->language->get('ms_error_file_upload_error');
			}
		}

		return $errors;
	}

	public function checkDownload($file) {
		return $this->checkFile($file, $this->config->get('msconf_allowed_download_types'));
	}

	public function checkImage($file,$allowed_ext=array()) {
		//i added $allowed_ext as in case seller file uploader extensions are mixed extensions with different allowed files
		$check_ext = $allowed_ext ? $allowed_ext : $this->config->get('msconf_allowed_image_types');
		$errors = $this->checkFile($file, $check_ext);
		
		if (!$errors) {
			$size = getimagesize($file["tmp_name"]);
			
			list($width, $height, $type, $attr) = getimagesize($file["tmp_name"]);
			
			if (($this->config->get('msconf_min_uploaded_image_width') > 0 && $width < $this->config->get('msconf_min_uploaded_image_width')) || ($this->config->get('msconf_min_uploaded_image_height') > 0 && $height < $this->config->get('msconf_min_uploaded_image_height'))) {
				$errors[] = sprintf($this->language->get('ms_error_image_too_small'), $this->config->get('msconf_min_uploaded_image_width'), $this->config->get('msconf_min_uploaded_image_height'));
			} else if (($this->config->get('msconf_max_uploaded_image_width') > 0 && $width > $this->config->get('msconf_max_uploaded_image_width')) || ($this->config->get('msconf_max_uploaded_image_height') > 0 && $height > $this->config->get('msconf_max_uploaded_image_height'))) {
				$errors[] = sprintf($this->language->get('ms_error_image_too_big'), $this->config->get('msconf_max_uploaded_image_width'), $this->config->get('msconf_max_uploaded_image_height'));
			}
			//@TODO? Flash reports all files as octet-stream
			//if(!isset($size) || stripos($file['type'],'image/') === FALSE || stripos($size['mime'],'image/') === FALSE) {
			if(!isset($size)) {
				//var_dump('error');
				$errors[] = $this->language->get('ms_error_file_type');
			}
		}

		return $errors;
	}

    /**
     * @param $file
     *  upload image to another path if present
     * @param string|null $path
     * @return string
     */
    public function uploadImage($file, string $path = null): string
    {
        if (is_null($path)) {
            $path = $this->config->get('msconf_temp_image_path');
        }

        $filename = time() . '_' . md5(rand()) . '.' . $file["name"];
        $filename = $path . $filename;
        \Filesystem::setPath('image/' . $filename)->upload($file["tmp_name"]);
        // move_uploaded_file($file["tmp_name"], DIR_IMAGE . $this->config->get('msconf_temp_image_path') .  $filename);

        if (!in_array($filename, $this->session->data['multiseller']['files'])) {
            $this->session->data['multiseller']['files'][] = $filename;
        }

        return $filename;
    }

	public function uploadDownload($file) {
		$filename =   time() . '_' . md5(rand()) . '.' . $this->MsLoader->MsSeller->getNickname() . '_' . $file["name"];
        $filename =   'download/' . $this->config->get('msconf_temp_download_path') . $filename;
        \Filesystem::setPath($filename)->upload($file["tmp_name"]);
		// move_uploaded_file($file["tmp_name"], DIR_DOWNLOAD . $this->config->get('msconf_temp_download_path') .  $filename);

		if (!in_array($filename, $this->session->data['multiseller']['files']))
			$this->session->data['multiseller']['files'][] = $filename;

		return array(
			'fileName' => $filename,
			'fileMask' => $file['name']
		);
	}

	public function checkFileAgainstSession($fileName) {
		if (array_search($fileName, $this->session->data['multiseller']['files']) === FALSE) {
			return FALSE;
		}

		return TRUE;
	}

	public function checkPredefinedAvatar($fileName) {
		return (
            strpos($fileName, $this->config->get('msconf_predefined_avatars_path')) === 0 &&
            \Filesystem::isExists('image/' . $fileName)
        );
	}

	public function moveDownload($fileName) {
		$newpath = $fileName;

		$key = array_search($fileName, $this->session->data['multiseller']['files']);

		//strip nonce and timestamp
		$original_file_name = substr($fileName, strpos($fileName, '.') + 1, mb_strlen($fileName));
		//var_dump($original_file_name);
		if ($this->_isNewUpload($fileName)) {
			$newpath = $original_file_name . '.' . md5(rand());
			//var_dump($newpath);
            $tmpPath = $this->config->get('msconf_temp_download_path');
			\Filesystem::setPath('download/' . $tmpPath . $fileName)->rename('download/' . $newpath);
		}

		unset ($this->session->data['multiseller']['files'][$key]);

		return $newpath;
	}

	public function moveImage($path, $adminCustomerId = null) {
		if (!$this->checkPredefinedAvatar($path)) {
			$key = array_search($path, $this->session->data['multiseller']['files']);
			if ($key === FALSE) return;
		}

		$dirname = dirname($path) . '/';
		$filename = basename($path);

		if ($adminCustomerId) {
            $tmpCustomerId = $adminCustomerId;
		} else {
			$tmpCustomerId = $this->customer->getId();
		}

		$imageDir = $this->config->get('msconf_product_image_path');

		// Check if folder exists and create if not
		if (!\Filesystem::isDirExists('image/' . $imageDir . $tmpCustomerId . '/')) {
            \Filesystem::createDir('image/' . $imageDir . $tmpCustomerId . '/');
            \Filesystem::setPath('image/' . $imageDir . $tmpCustomerId . '/index.html')->put('');
		}

		if ($dirname == './') {
			// new upload
			$dirname = $this->config->get('msconf_temp_image_path');
			//strip nonce and timestamp
			$originalFilename = $filename;
			$filename = substr($filename, strpos($filename, '.') + 1, mb_strlen($filename));
		}

		if ('image/' . $imageDir . $tmpCustomerId . "/" . $filename != 'image' . $path) {
			$newFilename = $this->_checkExistingFiles('image/' . $imageDir . $tmpCustomerId, $filename);
			$newPath = $imageDir . $tmpCustomerId . "/" . $newFilename;
            $oFilename = $dirname . (isset($originalFilename) ? $originalFilename : $filename);
			if ($this->checkPredefinedAvatar($path)) {
				// copy(DIR_IMAGE . $dirname . (isset($originalFilename) ? $originalFilename : $filename), DIR_IMAGE . $newPath);
                \Filesystem::setPath('image/' . $oFilename)->copy('image/' . $newPath);
            } else {
                // rename(DIR_IMAGE . $dirname . (isset($originalFilename) ? $originalFilename : $filename), DIR_IMAGE . $newPath);
                \Filesystem::setPath('image/' . $oFilename)->moveTo('image/' . $newPath);
			}
		} else {
			$newPath = $imageDir . $tmpCustomerId . "/" . $filename;
		}

		if (!$this->checkPredefinedAvatar($path)) {
			unset ($this->session->data['multiseller']['files'][$key]);
		}
		return $newPath;
	}

	public function deleteDownload($fileName) {
		if (empty($fileName))
			return false;

		$key = array_search($fileName, $this->session->data['multiseller']['files']);

		if (\Filesystem::isExists('download/' . $fileName)) {
			\Filesystem::remove('download/' . $fileName);
		}

		unset ($this->session->data['multiseller']['files'][$key]);
	}

	public function deleteImage($fileName) {
		if (empty($fileName))
			return false;

		$key = array_search($fileName, $this->session->data['multiseller']['files']);

        if (\Filesystem::isExists('image/' . $fileName)) {
            \Filesystem::remove('image/' . $fileName);
        }

		unset ($this->session->data['multiseller']['files'][$key]);
	}

	public function resizeImage($filename, $width, $height)
	{
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

            $resize = $file->createFromString($img->read())->resize($width, $height)->save($thumb);
            $imageSource = vsprintf('data:%s;base64, %s', [$info['mimetype'], base64_encode($resize)]);
        } catch (\Exception $e) {
            $imageSource = \Filesystem::getUrl('image/no_image.jpg');
        }

        return $imageSource;
	}

    /**
     * @deprecated
     */
	private function _resizeImage($filename, $width, $height) {
		// todo consider using default cache folder
		if (!file_exists(DIR_IMAGE . $filename) || !$filename || !filesize(DIR_IMAGE . $filename)) {
			return;
		}

		$size = getimagesize(DIR_IMAGE . $filename);
		if (!$size) return;
		
		$info = pathinfo($filename);
		$extension = $info['extension'];

		$temporary_filename = time() . '_' . md5(rand()) . '.' . $info["basename"];
		$image = new Image(DIR_IMAGE . $filename);
		$image->resize($width, $height);
		$image->save(DIR_IMAGE . $this->config->get('msconf_temp_image_path') . $temporary_filename);

		$file = substr($info['basename'], 0, strrpos($info['basename'], '.')) . '-' . $width . 'x' . $height . '.' . $extension;
		$new_image = $this->_checkExistingFilesMd5(DIR_IMAGE . $this->config->get('msconf_temp_image_path'), $file, md5_file(DIR_IMAGE . $this->config->get('msconf_temp_image_path') . $temporary_filename));

		if (copy(DIR_IMAGE . $this->config->get('msconf_temp_image_path') . $temporary_filename, DIR_IMAGE . $this->config->get('msconf_temp_image_path') . $new_image)) {
			unlink(DIR_IMAGE . $this->config->get('msconf_temp_image_path') . $temporary_filename);
		}

		if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
			//$base = defined('HTTPS_CATALOG') ? HTTPS_CATALOG : HTTPS_SERVER;
			return HTTPS_IMAGE . $this->config->get('msconf_temp_image_path') . $new_image;
		} else {
			//$base = defined('HTTP_CATALOG') ? HTTP_CATALOG : HTTP_SERVER;
			return HTTP_IMAGE . $this->config->get('msconf_temp_image_path') . $new_image;
		}
	}

    public function getPredefinedAvatars()
    {
        $avs = [];
        $files = \Filesystem::listRecursively('image/' . $this->config->get('msconf_predefined_avatars_path'));
        foreach ($files as $a) {
            if ($a['type'] == 'file') {
                $avs[basename($a['dirname'])][] = [
                    'filename' => $a['basename'],
                    'dir' => mb_substr($a['dirname'], mb_strlen(\Filesystem::getPathPrefix() . 'image/')) . '/',
                    'image' => $this->MsLoader->MsFile->resizeImage(
                        mb_substr($a['path'], mb_strlen(\Filesystem::getPathPrefix() . 'image/')),
                        $this->config->get('msconf_preview_seller_avatar_image_width'),
                        $this->config->get('msconf_preview_seller_avatar_image_height')
                    ),
                ];
            }
        }
        return $avs;
    }

    /**
     * @deprecated
     */
	private function _getPredefinedAvatars($path = '') {
		static $avatars = array();

		$dir = DIR_IMAGE . $this->config->get('msconf_predefined_avatars_path') . $path;

		$list = array_values(array_diff(scandir($dir), array('.', '..')));

		foreach ($list as $value) {
			$full_path = $dir . $value;
			if (is_dir($full_path) && is_readable($full_path)) {
				$this->getPredefinedAvatars($path . $value . '/');
			} elseif (is_file($full_path) && is_readable($full_path)) {
				$category = basename(dirname($full_path));
				if (!isset($avatars[$category])) {
					$avatars[$category] = array();
				}

				$avatars[$category][] = array(
					'filename' => $value,
					'dir' => $this->config->get('msconf_predefined_avatars_path') . $path, // image can be placed in any subfolder level, so dir not always the same as category
					'image' => $this->resizeImage($this->config->get('msconf_predefined_avatars_path') . $path . $value, $this->config->get('msconf_preview_seller_avatar_image_width'), $this->config->get('msconf_preview_seller_avatar_image_height'))
				);
			}
		}

		return $avatars;
	}
}
