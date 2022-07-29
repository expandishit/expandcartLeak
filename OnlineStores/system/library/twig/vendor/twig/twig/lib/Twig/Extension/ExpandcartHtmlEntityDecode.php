<?php

class Twig_Extension_ExpandcartHtmlEntityDecode extends Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('html_decode', array($this, 'html_decode')),
        );
    }

    public function html_decode($value)
    {
        return html_entity_decode($value);
    }
}
