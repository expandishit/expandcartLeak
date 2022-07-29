<?php
class Document {
	private $title;
	private $description;
	private $keywords;	
	private $links = array();		
	private $styles = array();
	private $scripts = array();
	private $inlineScripts = array();
	private $base = null;
	private $childdata = array();
	
	public function setTitle($title) {
		$this->title = $title;
	}
	
	public function getTitle() {
		return $this->title;
	}
	
	public function setDescription($description) {
		$this->description = $description;
	}

    public function setBasehref($base) {
        $this->base = $base;
    }

    public function setChildData($data) {
        $this->childdata = $data;
    }
	
	public function getDescription() {
		return $this->description;
	}
	
	public function setKeywords($keywords) {
		$this->keywords = $keywords;
	}
	
	public function getKeywords() {
		return $this->keywords;
	}
	
	public function addLink($href, $rel) {
		$this->links[md5($href)] = array(
			'href' => $href,
			'rel'  => $rel
		);			
	}
	
	public function getLinks() {
		return $this->links;
	}	
	
	public function addStyle($href, $rel = 'stylesheet', $media = 'screen') {
		$this->styles[md5($href)] = array(
			'href'  => $href,
			'rel'   => $rel,
			'media' => $media
		);
	}

    public function addExpandishStyle($href, $rel = 'stylesheet', $media = 'screen') {

	    [$fileWithoutAttributes, $attributes] = explode("?", $href);

	    if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' .  CURRENT_TEMPLATE . '/' . $fileWithoutAttributes)) {
            $file = 'expandish/view/theme/customtemplates/' . STORECODE . '/' . CURRENT_TEMPLATE . '/' . $fileWithoutAttributes;
        } else {
            if (IS_CUSTOM_TEMPLATE == 1) {
                // $file = 'expandish/view/custom/' . STORECODE . '/' . CURRENT_TEMPLATE . '/' . $href;
                $file = CUSTOM_TEMPLATE_PATH . CURRENT_TEMPLATE . '/' . $fileWithoutAttributes;
            } else {
                $file = 'expandish/view/theme/' . CURRENT_TEMPLATE . '/' . $fileWithoutAttributes;
            }
        }

        if (file_exists($file)){

            if(!empty($attributes)) {
                $file = "{$file}?{$attributes}";
            }

            $this->styles[md5($file)] = array(
                'href'  => $file,
                'rel'   => $rel,
                'media' => $media
            );

        }

    }
	
	public function getStyles() {
		return $this->styles;
	}	
	
	public function addScript($script) {
		$this->scripts[md5($script)] = $script;			
	}

    public function addExpandishScript($script) {
        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' .  CURRENT_TEMPLATE . '/' . $script)) {
            $file = 'expandish/view/theme/customtemplates/' . STORECODE . '/' . CURRENT_TEMPLATE . '/' . $script;
        } else {
            if (IS_CUSTOM_TEMPLATE == 1) {
                // $file = 'expandish/view/custom/' . STORECODE . '/' . CURRENT_TEMPLATE . '/' . $script;
                $file = CUSTOM_TEMPLATE_PATH . CURRENT_TEMPLATE . '/' . $href;
            } else {
                $file = 'expandish/view/theme/' . CURRENT_TEMPLATE . '/' . $script;
            }
        }
        $this->scripts[md5($file)] = $file;
    }

    /**
     * Adds a new inline script to be loaded globally on the home.expand file.
     *
     * @param string|Closure $script
     *
     * @return void
     */
    public function addInlineScript($script)
    {
        if (is_callable($script)) {
            $this->inlineScripts[] = [
                'script' => base64_encode($script()),
                'type' => 'callable'
            ];
        } else {
            $this->inlineScripts[] = [
                'script' => base64_encode($script),
                'type' => 'require'
            ];
        }
    }

    /**
     * Gets all inline scripts to be loaded as globals.
     *
     * return array
     */
    public function getInlineScripts()
    {
        return $this->inlineScripts;
    }
	
	public function getScripts() {
		return $this->scripts;
	}

	public function getBasehref() {
        return $this->base;
    }

    public function getChildData() {
        return $this->childdata;
    }
}
?>