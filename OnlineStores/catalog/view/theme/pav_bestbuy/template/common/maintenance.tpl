<!DOCTYPE html>
<html>
<head>
    <title><?php echo $heading_title; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="keywords" content="Cool Under Construction Page Flat Responsive, Login form web template,Flat Pricing tables,Flat Drop downs  Sign up Web Templates, Flat Web Templates, Login signup Responsive web template, Smartphone Compatible web template, free webdesigns for Nokia, Samsung, LG, SonyEricsson, Motorola web design" />
    <script type="application/x-javascript"> addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false); function hideURLbar(){ window.scrollTo(0,1); } </script>
    <script type="applijewelleryion/x-javascript"> addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false); function hideURLbar(){ window.scrollTo(0,1); } </script>
    <link href='//fonts.googleapis.com/css?family=Oswald:400,700,300' rel='stylesheet' type='text/css'>
    <link href='//fonts.googleapis.com/css?family=Montserrat:400,700' rel='stylesheet' type='text/css'>
    <link href="catalog/view/javascript/maintenance/css/style.css" rel="stylesheet" type="text/css" media="all" />
    <link rel="stylesheet" href="catalog/view/javascript/maintenance/css/jquery.countdown.css" />
    <!--js-->
    <script src="catalog/view/javascript/maintenance/js/jquery.min.js"></script>
    <script src="catalog/view/javascript/maintenance/js/jquery.countdown.js"></script>
    <script src="catalog/view/javascript/maintenance/js/script.js"></script>

    <?php if ($this->language->get('direction') == 'rtl') { ?>
        <style>
            .header h1, .content {
                font-family: 'Droid Arabic Kufi', 'droid_serifregular'; !important;
            }
        </style>
    <?php } ?>
    <!--js-->
</head>
<body style="direction: <?php echo $this->language->get('direction'); ?>;">
<div class="header">
    <h1><?php echo $heading_title; ?></h1>
</div>
<div class="content">
    <div class="content1">
        <img src="catalog/view/javascript/maintenance/images/work.png" alt="under-construction">
    </div>
    <div class="content3">
        <p><?php echo $message; ?></p>
    </div>
</div>
</body>
</html>