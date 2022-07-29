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
<div id="login-full-wrapper" class="reset-password-wrapper">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <div id="login-box">
                    <div id="login-box-holder">
                        <div class="row">
                            <div class="col-xs-12">
                                <header id="login-header">
                                    <div id="login-logo">
                                        <img src="view/image/cube/logo.png" alt=""/>
                                    </div>
                                </header>
                                <div id="login-box-inner">
                                    <h4><?php echo $heading_title; ?></h4>
                                    <p>
                                        <?php echo $text_password; ?>
                                    </p>
                                    <form role="form" action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="reset">
                                        <div class="input-group <?php echo $error_password ? 'has-error' : ''; ?>">
                                            <span class="input-group-addon"><i class="fa fa fa-lock"></i></span>
                                            <input class="form-control" type="password" name="password" value="<?php echo $password; ?>" placeholder="<?php echo $entry_password; ?>">
                                        </div>
                                        <?php if ($error_password) { ?>
                                        <div class="has-error"><span class="help-block"><i class="icon-remove-sign"></i> <?php echo $error_password; ?></span></div>
                                        <br>
                                        <?php } ?>

                                        <div class="input-group <?php echo $error_confirm ? 'has-error' : ''; ?>">
                                            <span class="input-group-addon"><i class="fa fa-unlock-alt"></i></span>
                                            <input class="form-control" type="password" name="confirm" value="<?php echo $confirm; ?>" placeholder="<?php echo $entry_confirm; ?>">
                                        </div>
                                        <?php if ($error_confirm) { ?>
                                        <div class="has-error"><span class="help-block"><i class="icon-remove-sign"></i> <?php echo $error_confirm; ?></span></div>
                                        <br>
                                        <?php } ?>

                                        <div class="row">
                                            <div class="col-xs-12">
                                                <button type="submit" onclick="$('#reset').submit();" class="btn btn-success col-xs-12"><?php echo $text_save_pass; ?></button>
                                            </div>
                                            <div class="col-xs-12">
                                                <br/>
                                                <a href="<?php echo $cancel; ?>" id="login-forget-link" class="forgot-link col-xs-12"><?php echo $text_back_login; ?></a>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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