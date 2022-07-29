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

        $this->session->data['recaptcha'] = $captcha->getCode();

        $captcha->showImage();

    }
}