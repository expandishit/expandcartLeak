<?php

class ControllerCommonCaptcha extends controller
{
    function index()
    {
        $this->drawCaptcha();

    }

    function drawCaptcha()
    {
        $this->load->library('captcha');

        $captcha = new Captcha();

        $this->session->data['captcha'] = $captcha->getCode();

        $captcha->showImage();
    }
}