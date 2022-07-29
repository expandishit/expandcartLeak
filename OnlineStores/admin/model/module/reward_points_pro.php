<?php

class ModelModuleRewardPointsPro extends Model {

    public function isInstalled(){
        $isInstalled =  \Extension::isInstalled('reward_points_pro');

        if($isInstalled) return true;

        return false;
    }
}
