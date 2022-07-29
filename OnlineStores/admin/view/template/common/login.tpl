<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />

    <title><?php echo $heading_title; ?></title>

    <!-- bootstrap -->
    <link rel="stylesheet" type="text/css" href="view/stylesheet/cube/css/bootstrap/bootstrap.min.css" />

    <?php if ($direction == 'rtl') { ?>
    <link rel="stylesheet" type="text/css" href="view/stylesheet/cube/css/libs/bootstrap-rtl.css" />
    <?php } ?>

    <!-- RTL support - for demo only
    <script src="view/javascript/cube/demo-rtl.js"></script>
    <!--
    If you need RTL support just include here RTL CSS file <link rel="stylesheet" type="text/css" href="css/libs/bootstrap-rtl.min.css" />
    And add "rtl" class to <body> element - e.g. <body class="rtl">
    -->

    <!-- libraries -->
    <link rel="stylesheet" type="text/css" href="view/stylesheet/cube/css/libs/font-awesome.css" />
    <link rel="stylesheet" type="text/css" href="view/stylesheet/cube/css/libs/nanoscroller.css" />

    <!-- global styles -->
    <link rel="stylesheet" type="text/css" href="view/stylesheet/cube/css/compiled/theme_styles.css" />

    <?php if ($direction == 'rtl') { ?>
    <link rel="stylesheet" type="text/css" href="view/stylesheet/cube/css/compiled/theme_styles_rtl.css" />
    <?php } ?>

    <!-- this page specific styles -->

    <!-- google font libraries -->
    <link href='//fonts.googleapis.com/css?family=Open+Sans:400,600,700,300|Titillium+Web:200,300,400' rel='stylesheet' type='text/css'>

    <!-- Favicon -->
    <link type="image/x-icon" href="favicon.png" rel="shortcut icon"/>

    <!--[if lt IE 9]>
    <script src="view/javascript/cube/html5shiv.js"></script>
    <script src="view/javascript/cube/respond.min.js"></script>
    <![endif]-->
</head>
<body id="login-page-full" class="theme-whbl rtl">
<div id="login-full-wrapper">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <div id="login-box">
                    <div id="login-box-holder">
                        <div class="row">
                            <div class="col-xs-12">
                                <header id="login-header">
                                    <div id="login-logo">
                                        <?php
                                            $LogoPath = 'logo.png';

                                            if (PARTNER_CODE != '') {
                                                $LogoPath = 'partners/' . PARTNER_CODE . '/logo-login.png';
                                            }
                                        ?>
                                        <img src="view/image/cube/<?php echo $LogoPath; ?>" alt="" style="<?php echo PARTNER_CODE == 'PNIKAD' ? 'height: 70px;' : '' ; ?>"/>
                                    </div>
                                </header>
                                <div id="login-box-inner">
                                    <?php if ($success) { ?>
                                    <p class="text-success"><?php echo $success; ?></p>
                                    <?php } elseif ($error_warning) { ?>
                                    <p class="text-danger"><?php echo $error_warning; ?></p>
                                    <?php } ?>
                                    <form role="form"  action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                            <input class="form-control" type="text" placeholder="<?php echo $entry_username; ?>" name="username" value="<?php if($_GET['email']) echo $_GET['email']; else echo $username; ?>" required="true">
                                        </div>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-key"></i></span>
                                            <input type="password" class="form-control" placeholder="<?php echo $entry_password; ?>" name="password" value="<?php echo $password; ?>"  required="true">
                                        </div>
                                        <div id="remember-me-wrapper">
                                            <div class="row">
                                                <?php if ($forgotten) { ?>
                                                <div class="col-xs-6">

                                                </div>
                                                <a href="<?php echo $forgotten; ?>" id="login-forget-link" class="col-xs-6">
                                                    <?php echo $text_forgotten; ?>
                                                </a>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-12">
                                                <button type="submit" class="btn btn-success col-xs-12"><?php echo $button_login; ?></button>
                                            </div>
                                        </div>

                                        <?php if ($redirect) { ?>
                                        <input type="hidden" name="redirect" value="<?php echo $redirect; ?>" />
                                        <?php } ?>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--<div id="login-box-footer">
                        <div class="row">
                            <div class="col-xs-12">
                                Do not have an account?
                                <a href="registration-full.html">
                                    Register now
                                </a>
                            </div>
                        </div>
                    </div>-->
                </div>
            </div>
        </div>
    </div>
</div>



<!-- global scripts
<script src="view/javascript/cube/demo-skin-changer.js"></script> <!-- only for demo -->

<script src="view/javascript/cube/jquery.js"></script>
<script src="view/javascript/cube/bootstrap.js"></script>
<script src="view/javascript/cube/jquery.nanoscroller.min.js"></script>

<script src="view/javascript/cube/demo.js"></script> <!-- only for demo -->

<!-- this page specific scripts -->


<!-- theme scripts -->
<script src="view/javascript/cube/scripts.js"></script>

<!-- this page specific inline scripts -->
</body>
</html>