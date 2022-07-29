<?php

namespace Api\Models;

class Image extends ParentModel
{
    
    /**
     * 
     *
     * @param 
     *
     * @return 
     */
    public function resize($filename, $width, $height, $type = "")
    {
        $this->container->load->model('tool/image');

        $image = $this->container->registry->get('model_tool_image')->resize($filename, $width, $height, $type);

		return $image;
    }
}
