<?php if ($logged) { ?>

<?php
    $parentArrowClass = 'fa-angle-right';
    if ($direction == 'rtl') {
        $parentArrowClass = 'fa-angle-left';
    }
    $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
?>

<?php if (isset($this->session->data['ms_db_latest'])) { ?>
<script>
    var notificationString = '<?php echo $this->session->data['ms_db_latest']; ?>';
    var notificationType = 'success';
</script>
<?php unset($this->session->data['ms_db_latest']); ?>
<?php } ?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />


    <title><?php echo $title; ?></title>

    <base href="<?php echo $base; ?>"/>
    <?php if ($description) { ?>
    <meta name="description" content="<?php echo $description; ?>"/>
    <?php } ?>
    <?php if ($keywords) { ?>
    <meta name="keywords" content="<?php echo $keywords; ?>"/>
    <?php } ?>

    <scriptholder id="ckeditorplaceholder"></scriptholder>

    <!-- bootstrap -->
    <link rel="stylesheet" type="text/css" href="view/stylesheet/cube/css/bootstrap/bootstrap.min.css" />


    <?php if ($direction == 'rtl') { ?>
    <link rel="stylesheet" type="text/css" href="view/stylesheet/cube/css/libs/bootstrap-rtl.css" />
    <?php } ?>

    <!-- RTL support - for demo only -->
    <!--
    <script src="view/javascript/cube/demo-rtl.js"></script>

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

    <link rel="stylesheet" type="text/css" href="view/stylesheet/cube/css/libs/ns-default.css">
    <link rel="stylesheet" type="text/css" href="view/stylesheet/cube/css/libs/ns-style-growl.css">
    <link rel="stylesheet" type="text/css" href="view/stylesheet/cube/css/libs/ns-style-bar.css">
    <link rel="stylesheet" type="text/css" href="view/stylesheet/cube/css/libs/ns-style-attached.css">
    <link rel="stylesheet" type="text/css" href="view/stylesheet/cube/css/libs/ns-style-other.css">
    <link rel="stylesheet" type="text/css" href="view/stylesheet/cube/css/libs/ns-style-theme.css">
    <link rel="stylesheet" type="text/css" href="view/stylesheet/cube/css/libs/dropzone.css">

    <!-- this page specific styles -->
    <link rel="stylesheet" href="view/stylesheet/cube/css/libs/weather-icons.css" type="text/css" />

    <link rel="stylesheet" type="text/css" href="view/stylesheet/cube/css/libs/hopscotch.css">

    <!-- Favicon -->
    <link type="image/x-icon" href="favicon.png" rel="shortcut icon" />

    <!-- google font libraries -->
    <link href='//fonts.googleapis.com/css?family=Open+Sans:400,600,700,300|Titillium+Web:200,300,400' rel='stylesheet' type='text/css'>

    <?php foreach ($styles as $style) { ?>
    <link rel="<?php echo $style['rel']; ?>" type="text/css" href="<?php echo $style['href']; ?>" media="<?php echo $style['media']; ?>" />
    <?php } ?>

    <!--[if lt IE 9]>
    <script src="view/javascript/cube/html5shiv.js"></script>
    <script src="view/javascript/cube/respond.min.js"></script>
    <![endif]-->

    <!--TODO: Moved from footer-->
    <script src="view/javascript/cube/jquery.js"></script>
    <script type="text/javascript" src="view/javascript/jquery-ui-1.11.4.custom/jquery-ui.js"></script>
    <link type="text/css" href="view/javascript/jquery-ui-1.11.4.custom/jquery-ui.min.css" rel="stylesheet" />
    <script src="view/javascript/cube/bootstrap.js"></script>

    <script>
        var catalog_link = '<?php echo HTTP_CATALOG; ?>';
        var langCode = '<?php echo $direction == "rtl" ? "ar" : "en"; ?>';
    </script>

    <script type="text/javascript">
        if (!window.console)
            console = {
                        log: function() {}
                      };
        var msGlobals = {
                            config_admin_limit: '<?php echo $this->config->get('config_admin_limit'); ?>',
                            config_language: '<?php echo $lang; ?>'
                        };
    </script>

    <!--TODO: should be removed or upgraded to latest versions-->

    <?php
        $newPages = array("module/mobile_app");

        if (!in_array($this->request->get['route'], $newPages)) { ?>
        <?php if ($direction == 'rtl') { ?>
            <link rel="stylesheet" type="text/css" href="view/stylesheet/stylesheet-a.css" />
        <?php } else { ?>
            <link rel="stylesheet" type="text/css" href="view/stylesheet/stylesheet.css" />
        <?php } ?>
    <?php } ?>

    <link rel="stylesheet" type="text/css" href="view/stylesheet/cube/css/libs/nifty-component.css">

    <script type="text/javascript" src="view/javascript/jquery/tabs.js"></script>

    <?php foreach ($scripts as $script) { ?>
    <script type="text/javascript" src="<?php echo $script; ?>"></script>
    <?php } ?>

    <script>
        var text_lengthMenu = '<?php echo $text_lengthMenu; ?>';
        var text_zeroRecords = '<?php echo $text_zeroRecords; ?>';
        var text_info = '<?php echo $text_info; ?>';
        var text_infoEmpty = '<?php echo $text_infoEmpty; ?>';
        var text_infoFiltered = '<?php echo $text_infoFiltered; ?>';
        var text_search = '<?php echo $text_search; ?>';
    </script>

    <!-- Begin Inspectlet Embed Code -->
    <?php if(PRODUCTID == 3) { ?>
    <script type="text/javascript" id="inspectletjs">
        window.__insp = window.__insp || [];
        __insp.push(['wid', 47507414]);
        __insp.push(['tagSession', {userid: "<?php echo STORECODE; ?>"}]);
        __insp.push(['identify', "<?php echo STORECODE; ?>"]);
        (function() {
            function __ldinsp(){var insp = document.createElement('script'); insp.type = 'text/javascript'; insp.async = true; insp.id = "inspsync"; insp.src = ('https:' == document.location.protocol ? 'https' : 'http') + '://cdn.inspectlet.com/inspectlet.js'; var x = document.getElementsByTagName('script')[0]; x.parentNode.insertBefore(insp, x); };
            document.readyState != "complete" ? (window.attachEvent ? window.attachEvent('onload', __ldinsp) : window.addEventListener('load', __ldinsp, false)) : __ldinsp();

        })();
    </script>
    <?php }?>
    <!-- End Inspectlet Embed Code -->

    <scriptholder id="headerScriptsContent"></scriptholder>

    <?php if($guide) { ?>
    <script type="text/javascript">
        var loadJS = function(jsURL, cssURL, implementationCode, location){
            //Load CSS
            if(typeof cssURL != "undefined" && cssURL != null && cssURL != "") {
                var file = cssURL;
                var link = document.createElement("link");
                link.href = file;
                link.type = "text/css";
                link.rel = "stylesheet";
                link.media = "screen,print";

                document.getElementsByTagName("head")[0].appendChild(link);
                //End: Load CSS
            }

            var scriptTag = document.createElement('script');
            scriptTag.src = jsURL;

            scriptTag.onload = implementationCode;
            scriptTag.onreadystatechange = implementationCode;

            location.appendChild(scriptTag);
        };
        var afterLoadCallback = function(){
            var Popups = <?= $guide ?>;
            Popups.forEach(initPopup);
        };


        var initPopup = function(popup) {
            //debugger;
            popupDIV = document.createElement('div');
            popupDIV.id = popup.Settings.PID;
            popupDIV.className="well";
            popupDIV.innerHTML = popup.Content;

            if(popup.Settings.Width) {
                popupDIV.style.width = popup.Settings.Width;
            }
            if(popup.Settings.Height) {
                popupDIV.style.height = popup.Settings.Height;
            }
            if(popup.Settings.CSSClass) {
                popupDIV.className = popup.Settings.CSSClass
            }
            if(popup.Settings.NextPID) {
                closeButton = document.createElement('button');
                closeButton.className = popup.Settings.PID + "_close " + popup.Settings.NextPID + "_open btn btn-default";
                closeButton.innerText = "Next";
                popupDIV.appendChild(closeButton);
            }
            if(popup.Settings.HasClose) {
                closeButton = document.createElement('button');
                closeButton.className = popup.Settings.PID + "_close btn btn-default";
                closeButton.innerText = "Close";
                popupDIV.appendChild(closeButton);
            }
            document.body.appendChild(popupDIV);

            $('#' + popup.Settings.PID).popup(popup.Settings.Options);
        };
        loadJS('view/libguide/jquery.popupoverlay.js', null, afterLoadCallback, document.body);
    </script>
    <?php } ?>
</head>
<body class="<?php echo $direction; ?> theme-whbl fixed-header fixed-leftmenu <?php echo $isCODCollector ? 'no-leftmenu' : ''; ?>">
<div id="theme-wrapper">
<header class="navbar" id="header-navbar">
<div class="container">
<a href="<?php echo $home; ?>" id="logo" class="navbar-brand">
    <?php
        $LogoPath = 'logo.png';

        if (PARTNER_CODE != '') {
            $LogoPath = 'partners/' . PARTNER_CODE . '/logo-backend.png';
        }
    ?>
    <img src="view/image/cube/<?php echo $LogoPath; ?>" alt="" style="<?php echo PARTNER_CODE == 'PNIKAD' ? 'height: 35px;' : '' ; ?>" class="normal-logo logo-white"/>
    <img src="view/image/cube/<?php echo $LogoPath; ?>" alt="" style="<?php echo PARTNER_CODE == 'PNIKAD' ? 'height: 35px;' : '' ; ?>" class="normal-logo logo-black"/>
    <img src="view/image/cube/<?php echo $LogoPath; ?>" alt="" style="<?php echo PARTNER_CODE == 'PNIKAD' ? 'height: 35px;' : '' ; ?>" class="small-logo hidden-xs hidden-sm hidden"/>
</a>

<div class="clearfix">
<button class="navbar-toggle" data-target=".navbar-ex1-collapse" data-toggle="collapse" type="button">
    <span class="sr-only">Toggle navigation</span>
    <span class="fa fa-bars"></span>
</button>

<div class="nav-no-collapse navbar-left pull-left hidden-sm hidden-xs">
    <ul class="nav navbar-nav pull-left">
        <li style="display: none;">
            <a class="btn" id="make-small-nav">
                <i class="fa fa-bars"></i>
            </a>
        </li>
        <?php if ($billingAccess == 1) { ?>
        <li class="dropdown hidden-xs">
            <a class="btn dropdown-toggle" data-toggle="dropdown">
                <?php echo $text_myaccount; ?>
                <i class="fa fa-caret-down"></i>
            </a>
            <ul class="dropdown-menu">
                <li class="item">
                    <a href="<?php echo $url_billingaccount; ?>" target="_blank">
                        <i class="fa fa-credit-card"></i>
                        <?php echo $text_billingaccount; ?>
                    </a>
                </li>
                <li class="item">
                    <a href="<?php echo $url_supportticket; ?>" target="_blank">
                        <i class="fa fa-support"></i>
                        <?php echo $text_supportticket; ?>
                    </a>
                </li>
                <?php if (1==0) { ?>
                <li class="item">
                    <a href="<?php echo $url_knowledgebase; ?>" target="_blank">
                        <i class="fa fa-archive"></i>
                        <?php echo $text_knowledgebase; ?>
                    </a>
                </li>
                <?php } ?>
            </ul>
        </li>
        <?php if (1==0) { ?>
        <li class="dropdown profile-dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <?php if ($direction == 'ltr') { ?>
                    <img src="view/image/support_agents/<?php echo $agentDetails['image']; ?>" alt="">
                <?php } ?>
                <span class="hidden-xs"><b><?php echo $agentDetails['displayname']; ?></b><?php echo $text_personallassistant; ?>
                    <i class="fa <?php echo $direction == 'ltr' ? 'fa-flip-horizontal' : ''; ?> fa-phone" style="margin-left: 3px;margin-right: 3px;"></i>
                </span>
                <?php if ($direction == 'rtl') { ?>
                    <img src="view/image/support_agents/<?php echo $agentDetails['image']; ?>" alt="">
                <?php } ?>
            </a>
            <ul class="dropdown-menu <?php echo $direction == 'ltr' ? 'dropdown-menu-left' : 'dropdown-menu-right'; ?>" style="width: 453px; padding-top: 0px; padding-bottom: 0px;">
                <li>
                    <?php echo $agentDetails['coveragehtml']; ?>
                </li>
            </ul>
        </li>
        <?php } ?>
        <?php } ?>
    </ul>
</div>

<div class="nav-no-collapse pull-right" id="header-nav">
    <ul class="nav navbar-nav pull-right">
        <?php if ($trialDaysLeft != "-2" && $billingAccess == "1" && !defined('CLIENT_SUSPEND')) { ?>
        <li class="alert alert-success hidden-sm hidden-xs">
            <i class="fa fa-info-circle fa-fw fa-lg"></i>
            <?php echo $text_trialmessage; ?>
            <span class="alert-link" onclick="location.href = '<?php echo $url_upgradenow; ?>';"><?php echo $text_upgradenow; ?></span>
        </li>
        <?php } ?>

        <li class="hidden-xs">
            <a class="btn" href="<?php echo $store; ?>" target="_blank">
                <?php echo $text_front; ?>
            </a>
        </li>

        <li class="hidden-xxs">
            <a class="btn" href="<?php echo $logout; ?>" title="<?php echo $text_logout; ?>">
                <i class="fa fa-power-off"></i>
            </a>
        </li>
    </ul>
</div>
</div>
</div>
</header>
<div id="page-wrapper" class="container">
<div class="row">
<div id="nav-col" <?php echo defined('CLIENT_SUSPEND') ? 'style="display:none;"' : ''; ?>>
<section id="col-left" class="col-left-nano">
<div id="col-left-inner" class="col-left-nano-content">
<?php if ($trialDaysLeft != -2 && $billingAccess == "1") { ?>
<ul class="nav nav-pills nav-stacked nav-above-profile">
    <li class="alert-success">
        <a href="<?php echo $url_upgradenow; ?>">
            <span class="hidden-sm hidden-xs"><?php echo $text_trialperiod; ?></span>
            <span class="hidden-lg hidden-md"><?php echo $text_trialmessage; ?></span>
            <span class="label label-success pull-right"><?php echo $text_upgradenow; ?></span>
        </a>
    </li>
</ul>
<?php } ?>
<div id="user-left-box" class="clearfix hidden-sm hidden-xs dropdown profile2-dropdown">
    <img alt="" src="view/image/cube/samples/user-159.png" />
    <div class="user-box">
									<span class="name">
										<a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                            <?php echo $this->user->getUserName(); ?>
                                            <i class="fa fa-angle-down"></i>
                                        </a>
										<ul class="dropdown-menu">
                                            <li><a href="<?php echo $loggedUserProfile; ?>"><i class="fa fa-user"></i><?php echo $text_profile; ?></a></li>
                                            <li><a href="<?php echo $directStoreSettings; ?>"><i class="fa fa-cog"></i><?php echo $text_setting; ?></a></li>
                                            <li><a href="<?php echo $logout; ?>"><i class="fa fa-power-off"></i><?php echo $text_logout; ?></a></li>
                                        </ul>
									</span>
									<span class="status" onclick="expandMenu();">
										<i class="fa fa-circle"></i> <?php echo $text_online; ?>
									</span>
    </div>
</div>

<div class="collapse navbar-collapse navbar-ex1-collapse <?php echo ($trialDaysLeft != -2 && $billingAccess == '1') ? '' : 'no-trial'; ?>" id="sidebar-nav">
<ul class="nav nav-pills nav-stacked menu-stack">
<li id="dashboard">
    <a href="<?php echo $home; ?>">
        <i class="fa fa-dashboard"></i>
        <span><?php echo $text_dashboard; ?></span>
    </a>
</li>
<li id="sale">
    <a href="#" class="dropdown-toggle">
        <i class="fa fa-dollar"></i>
        <span><?php echo $text_sale; ?></span>
        <i class="fa <?php echo $parentArrowClass; ?> drop-icon"></i>
    </a>
    <ul class="submenu">
        <li><a href="<?php echo $order; ?>"><?php echo $text_order; ?></a></li>
        <li><a href="<?php echo $return; ?>"><?php echo $text_return; ?></a></li>

        <?php if($this->config->get('config_externalorder')) { ?>
            <li><a href="<?php echo $externalorder; ?>"><?php echo $text_externalorder; ?></a></li></li>
        <?php }?>

        <?php if($this->config->get('aramex_status')) { ?>
            <li><a href="<?php echo $bulk_shedule; ?>"><?php echo $text_aramexbulk; ?></a></li>
        <?php } ?>
    </ul>
</li>
<li id="catalog">
    <a href="#" class="dropdown-toggle">
        <i class="fa fa-table"></i>
        <span><?php echo $text_catalog; ?></span>
        <i class="fa <?php echo $parentArrowClass; ?> drop-icon"></i>
    </a>
    <ul class="submenu">
        <li><a href="<?php echo $product; ?>"><?php echo $text_product; ?></a></li>
        <li><a href="<?php echo $category; ?>"><?php echo $text_category; ?></a></li>
        <li><a href="<?php echo $option; ?>"><?php echo $text_option; ?></a></li>
        <li><a href="<?php echo $attribute; ?>"><?php echo $text_attribute; ?></a></li>
        <li><a href="<?php echo $filter; ?>"><?php echo $text_filter; ?></a></li>
        <li><a href="<?php echo $manufacturer; ?>"><?php echo $text_manufacturer; ?></a></li>
        <li><a href="<?php echo $review; ?>"><?php echo $text_review; ?></a></li>
        <li><a href="<?php echo $download; ?>"><?php echo $text_download; ?></a></li>
    </ul>
</li>
<li id="customers">
    <a href="#" class="dropdown-toggle">
        <i class="fa fa-users"></i>
        <span><?php echo $text_customers; ?></span>
        <i class="fa <?php echo $parentArrowClass; ?> drop-icon"></i>
    </a>
    <ul class="submenu">
        <li><a href="<?php echo $customer; ?>"><?php echo $text_customer; ?></a></li>
        <li><a href="<?php echo $customer_group; ?>"><?php echo $text_customer_group; ?></a></li>
        <li><a href="<?php echo $customer_ban_ip; ?>"><?php echo $text_customer_ban_ip; ?></a></li>
    </ul>
</li>
<li id="marketing">
    <a href="#" class="dropdown-toggle">
        <i class="fa fa-bullhorn"></i>
        <span><?php echo $text_marketing; ?></span>
        <i class="fa <?php echo $parentArrowClass; ?> drop-icon"></i>
    </a>
    <ul class="submenu">
        <?php if(!IS_NEXTGEN_FRONT) {?>
        <li><a href="<?php echo $productpromo; ?>"><?php echo $text_productpromo; ?></a></li>
        <?php }?>
        <li><a href="<?php echo $contact; ?>"><?php echo $text_contact; ?></a></li>
        <?php $querySMSModule = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'smshare'");
        if($querySMSModule->num_rows) { ?>
            <li><a href="<?php echo $smshare_sms; ?>"><?php echo $text_smshare_sms; ?></a></li>
        <?php } ?>
        <li><a href="<?php echo $affiliate; ?>"><?php echo $text_affiliate; ?></a></li>
        <li><a href="<?php echo $coupon; ?>"><?php echo $text_coupon; ?></a></li>
        <li><a href="<?php echo $voucher; ?>"><?php echo $text_voucher; ?></a></li>
    </ul>
</li>
<!--<li id="blog">
    <a href="<?= $flashBlog; ?>">
        <i class="fa fa-file-text-o"></i>
        <span><?php echo $blog; ?></span>
    </a>
</li>-->
<li id="reports">
    <a href="<?php echo $reports; ?>">
        <i class="fa fa-bar-chart-o"></i>
        <span><?php echo $text_reports; ?></span>
    </a>
</li>

<li class="nav-header hidden-sm hidden-xs"></li>

<li id="design">
    <a href="#" class="dropdown-toggle">
        <i class="fa fa-magic"></i>
        <span><?php echo $text_design; ?></span>
        <i class="fa <?php echo $parentArrowClass; ?> drop-icon"></i>
    </a>
    <ul class="submenu">
        <li><a href="<?php echo $templates; ?>"><?php echo $text_templates; ?></a></li>
        <li><a href="<?php echo IS_NEXTGEN_FRONT ? $teditor : $modthemecontrol; ?>"><?php echo $text_customizeTheme; ?></a></li>
        <?php if(!IS_NEXTGEN_FRONT) { ?>
            <li><a href="<?php echo $module; ?>"><?php echo $text_module; ?></a></li>
            <li><a href="<?php echo $banner; ?>"><?php echo $text_banner; ?></a></li>
            <li><a href="<?php echo $modslideshow; ?>"><?php echo $text_modslideshow; ?></a></li>
            <?php if ($config_template != 'gazal') { ?>
                <li><a href="<?php echo $modcustom; ?>"><?php echo $text_modcustom; ?></a></li>
            <?php } ?>
        <?php } ?>
        <li><a href="<?php echo $information; ?>"><?php echo $text_information; ?></a></li>
        <?php if(!IS_NEXTGEN_FRONT) {?>
            <?php if($pavblog_installed) { ?>
                <li><a href="<?php echo $modblogpav; ?>"><?php echo $text_modblogdashboard; ?></a></li>
            <?php } elseif ($gazalblog_installed) { ?>
                <li><a href="<?php echo $modbloggazalnews; ?>"><?php echo $text_modbloggazalnews; ?></a></li>
            <?php } ?>
        <?php } ?>
    </ul>
</li>
    <li id="mobileapp">
        <a href="<?php echo $mobileapp; ?>">
            <i class="fa fa-mobile"></i>
            <span><?php echo $text_mobileapp; ?></span>
            <span class="label label-danger pull-right"><?php echo $text_new; ?></span>
        </a>
    </li>
<li id="apps">
    <a href="<?php echo $apps; ?>">
        <i class="fa fa-puzzle-piece"></i>
        <span><?php echo $text_apps; ?></span>
    </a>
</li>
<li id="services">
    <a href="<?php echo $services; ?>">
        <i class="fa fa-suitcase"></i>
        <span><?php echo $text_services; ?></span>
    </a>
</li>

<li class="nav-header hidden-sm hidden-xs"></li>

<li id="settings">
    <a href="#" class="dropdown-toggle">
        <i class="fa fa-gears"></i>
        <span><?php echo $text_system; ?></span>
        <i class="fa <?php echo $parentArrowClass; ?> drop-icon"></i>
    </a>
    <ul class="submenu">
        <li><a href="<?php echo $setting; ?>"><?php echo $text_setting; ?></a></li>
        <li><a href="<?php echo $shipping; ?>"><?php echo $text_shipping; ?></a></li>
        <li><a href="<?php echo $payment; ?>"><?php echo $text_payment; ?></a></li>
        <li><a href="<?php echo $tax_rate; ?>"><?php echo $text_tax; ?></a></li>
        <li><a href="<?php echo $total; ?>"><?php echo $text_total; ?></a></li>
        <li><a href="<?php echo $user; ?>"><?php echo $text_user; ?></a></li>
        <li><a href="<?php echo $currency; ?>"><?php echo $text_currency; ?></a></li>
        <li><a href="<?php echo $language; ?>"><?php echo $text_language; ?></a></li>
        <li><a href="<?php echo $advancedsettings; ?>"><?php echo $text_advancedsettings; ?></a></li>
        <?php if(PRODUCTID != 3) { ?>
            <li><a href="<?php echo $domains; ?>"><?php echo $text_domains; ?></a></li>
        <?php } ?>
        <li><a href="<?php echo $links['api']; ?>">{{ lang('api') }}</a></li>
    </ul>
</li>
    <?php 	$queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");
    if($queryMultiseller->num_rows) { ?>
    <li id="multiseller">
        <a href="#" class="dropdown-toggle">
            <i class="fa fa-truck"></i>
            <span><?php echo $ms_menu_multiseller; ?></span>
            <i class="fa <?php echo $parentArrowClass; ?> drop-icon"></i>
        </a>
        <ul class="submenu">
            <li><a href="<?php echo $ms_link_sellers; ?>"><?php echo $ms_menu_sellers; ?></a></li>
            <li><a href="<?php echo $ms_link_seller_groups; ?>"><?php echo $ms_menu_seller_groups; ?></a></li>
            <li><a href="<?php echo $ms_link_attributes; ?>"><?php echo $ms_menu_attributes; ?></a></li>
            <li><a href="<?php echo $ms_link_products; ?>"><?php echo $ms_menu_products; ?></a></li>
            <li><a href="<?php echo $ms_link_transactions; ?>"><?php echo $ms_menu_transactions; ?></a></li>
            <li><a href="<?php echo $ms_link_payment; ?>"><?php echo $ms_menu_payment; ?></a></li>
            <li><a href="<?php echo $ms_link_subscriptions; ?>">{{ lang('ms_config_subscriptions_plans') }}</a></li>
            <li><a href="<?php echo $ms_link_settings; ?>"><?php echo $ms_menu_settings; ?></a></li>
        </ul>
    </li>
    <?php } ?>
    <?php $queryAuctionModule = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'special_count_down'");
    if($queryAuctionModule->num_rows) { ?>
    <li id="auction">
        <a href="#" class="dropdown-toggle">
            <i class="fa fa-legal"></i>
            <span><?php echo $text_auction; ?></span>
            <i class="fa <?php echo $parentArrowClass; ?> drop-icon"></i>
        </a>
        <ul class="submenu">
            <li><a href="<?php echo $auction; ?>"><?php echo $text_auction_product; ?></a></li>
            <li><a href="<?php echo $winner; ?>"><?php echo $text_winner; ?></a></li>
            <li><a href="<?php echo $auction_block; ?>"><?php echo $text_auction_block; ?></a></li>
            <li><a href="<?php echo $auction_blacklist; ?>"><?php echo $text_auction_blacklist; ?></a></li>
        </ul>
    </li>
    <?php } ?>

</ul>
</div>

<div class="next-nav <?php echo ($trialDaysLeft != -2 && $billingAccess == '1') ? '' : 'no-trial'; ?>" id="nav-col-submenu">

</div>

</div>
</section>
<div id="nav-col-submenuhover"></div>
</div>
<div id="content-wrapper" <?php echo defined('CLIENT_SUSPEND') ? 'style="margin-left: 0px;margin-right: 0px;"' : ''; ?>>
    <?php 	$queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");
    if($queryMultiseller->num_rows) { ?>
    <?php $this->load->model('multiseller/upgrade'); ?>

    <?php if ($this->MsLoader->MsHelper->isInstalled() && !$this->model_multiseller_upgrade->isDbLatest()) { ?>
    <div class="alert alert-warning" style="margin-bottom: 30px;">
        <i class="fa fa-warning fa-fw fa-lg"></i>
        <?php echo sprintf($this->language->get('ms_db_upgrade'), $this->url->link('module/multiseller/upgradeDb', 'token=' . $this->session->data['token'], 'SSL')); ?>
    </div>
    <?php } ?>
    <?php } ?>
<?php } ?>
