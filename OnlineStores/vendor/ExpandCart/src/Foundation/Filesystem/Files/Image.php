<?php

namespace ExpandCart\Foundation\Filesystem\Files;

use SplFileInfo;
use ExpandCart\Foundation\Filesystem\File;
use ExpandCart\Foundation\Filesystem\Filesystem;

class Image extends AbstractFile
{
    /**
     * @var GD resource
     */
    protected $image;

    /**
     * @var array
     */
    protected $info;

    /**
     * @var string
     */
    protected $mimeType;

    /**
     * @var int
     */
    protected $quality = -1;

    /**
     * @var mixed
     */
    protected $filters = -1;

    /**
     * @var File
     */
    protected $file;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var string
     */
    protected $imageBinary;

    /**
     * Scale type for image
     * possible values : [width or w, height or h]
     * any other value will be ignored
     * @var string
     */
    protected $scaleType = 'both';

    /**
     * Creates gd instance from a given image source string.
     *
     * @param string $imgSource
     *
     * @return self
     */
    public function createFromString($imgSource)
    {
        $this->image = imagecreatefromstring($imgSource);

        return $this;
    }

        /**
     * Creates gd instance from a given image source webp.
     *
     * @param string $imgSource
     *
     * @return self
     */
    public function createFromWebp($imgSource)
    {
        $this->image = imagecreatefromwebp($imgSource);

        return $this;
    }

    /**
     * Set the file adapter.
     *
     * @param File $file
     *
     * @return self
     */
    public function setFile(File $file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Set the filesystem adapter.
     *
     * @param Filesystem $filesystem
     *
     * @return self
     */
    public function setFilesystem(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;

        return $this;
    }

    /**
     * Set the info array.
     *
     * @param array $filesystem
     *
     * @return self
     */
    public function setInfo(array $info)
    {
        $this->info = $info;

        return $this;
    }

    /**
     * Resize the Image::$image to a given width and height.
     *
     * @param int $width
     * @param int $height
     *
     * @return self
     */
    public function resize($width, $height)
    {
        $resized = imagecreatetruecolor($width, $height);
        $oldWidth = imagesx($this->image);
        $oldHeight = imagesy($this->image);

        $newX = $newY = $oldX = $oldY = 0;

        $scale = 1;

        $scaleWidth = $width / $oldWidth;
        $scaleHeight = $height / $oldHeight;

        if ($this->scaleType == 'width' || $this->scaleType == 'w') {
            $scale = $scaleWidth;
        } elseif ($this->scaleType == 'height' || $this->scaleType == 'h') {
            $scale = $scaleHeight;
        } else {
            $scale = min($scaleWidth, $scaleHeight);
        }

        $newWidth = (int)($oldWidth * $scale);
        $newHeight = (int)($oldHeight * $scale);
        $newX = (int)(($width - $newWidth) / 2);
        $newY = (int)(($height - $newHeight) / 2);

        $resource = $this->image;

        $transIndex = imagecolortransparent($resource);

        if ($transIndex != -1) {
            $rgba = imagecolorsforindex($resized, $transIndex);

            $transColor = imagecolorallocatealpha(
                $resized,
                $rgba['red'],
                $rgba['green'],
                $rgba['blue'],
                127
            );

            imagefill($resized, 0, 0, $transColor);
            imagecolortransparent($resized, $transColor);
        } else {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transColor = imagecolorallocatealpha($resized, 255, 255, 255, 127);
            imagecolortransparent($resized, $transColor);
        }

        imagefilledrectangle($resized, 0, 0, $width, $height, $transColor);

        $result = imagecopyresampled(
            $resized,
            $resource,
            $newX,
            $newY,
            $oldX,
            $oldY,
            $newWidth,
            $newHeight,
            $oldWidth,
            $oldHeight
        );

        $this->image = $resized;

        imagedestroy($resource);

        return $this;
    }

    /**
     * Set image quality on rendering level.
     *
     * @param int $quality
     *
     * @return self
     */
    public function setQuality($quality)
    {
        $this->quality = $quality;

        return $this;
    }

    /**
     * Get quality.
     *
     * @return mixed
     */
    public function getQuality()
    {
        return $this->quality;
    }

    /**
     * Get image filters , mostly used in rendering png image.
     *
     * @param mixed $filters
     *
     * @return self
     */
    public function setFilters($filters)
    {
        $this->filters = $filters;

        return $filters;
    }

    /**
     * Get filters.
     *
     * @return mixed
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Get filters.
     *
     * @return string
     */
    public function getBinary()
    {
        return $this->imageBinary;
    }

    /**
     * Render image to string based on it's mimetype.
     *
     * @return string
     */
    public function getImageBinary()
    {
        if ($this->info['mimetype'] == 'image/jpeg') {
            return ($this->imageBinary = $this->getJpegBinary());
        } else if ($this->info['mimetype'] == 'image/png') {
            return ($this->imageBinary = $this->getPngBinary());
        } else if ($this->info['mimetype'] == 'image/gif') {
            return ($this->imageBinary = $this->getGifBinary());
        } else if ($this->info['mimetype'] == 'image/webp') {
            return ($this->imageBinary = $this->getWebpBinary());
        }

        return 'throw';
    }

    /**
     * Render Png images.
     *
     * @return string
     */
    private function getPngBinary()
    {
        ob_start();
        imagealphablending($this->image, false);
        imagesavealpha($this->image, true);
        imagepng($this->image, null, $this->getQuality(), $this->getFilters());
        $this->mimeType = image_type_to_mime_type(IMAGETYPE_PNG);
        $buffer = ob_get_contents();
        ob_end_clean();

        return $buffer;
    }

    /**
     * Render Jpeg images.
     *
     * @return string
     */
    private function getJpegBinary()
    {
        ob_start();
        imagejpeg($this->image, null, $this->getQuality());
        $this->mimeType = image_type_to_mime_type(IMAGETYPE_JPEG);
        $buffer = ob_get_contents();
        ob_end_clean();

        return $buffer;
    }

    /**
     * Render Gif images.
     *
     * @return string
     */
    private function getGifBinary()
    {
        ob_start();
        imagegif($this->image, null);
        $this->mimeType = image_type_to_mime_type(IMAGETYPE_GIF);
        $buffer = ob_get_contents();
        ob_end_clean();

        return $buffer;
    }

        /**
     * Render Webp images.
     *
     * @return string
     */
    private function getWebpBinary()
    {
        ob_start();
        imagewebp($this->image, null);
        $this->mimeType = image_type_to_mime_type(IMAGETYPE_WEBP);
        $buffer = ob_get_contents();
        ob_end_clean();

        return $buffer;
    }

    /**
     * Save image to a specific path.
     * this will save the Image::$image content so no need to
     * the image contents
     *
     * @param string $path
     *
     * @return self
     */
    public function save(string $path)
    {
        $this->file->setContent($this->getImageBinary());
        $this->file->saveTo($path);

        return $this;
    }

    public function __toString()
    {
        return $this->imageBinary;
    }
}
