<?php

class ModelModuleSunglassesQuiz extends Model
{
    public function install()
    {

    }

    public function uninstall()
    {
        
    }


    public function isInstalled()
    {
        return \Extension::isInstalled('cardless');
    }

    
}