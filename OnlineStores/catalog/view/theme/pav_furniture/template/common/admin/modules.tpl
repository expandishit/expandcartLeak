<?php 
	$d = array(
    'demo_widget_newsletter_data' => '
        <div class="input-group"><input alt="username" class="inputbox" name="email" placeholder="Type your email" size="31" type="text" /> <span class="input-group-btn"><button class="btn btn-newletter" type="button">Go!</button> </span></div>

        <p>Sunday when we open from and dions contact page</p>

        <div class="box-heading">Get Social</div>

        <div class="social">
            <ul class="pull-left">
                <li><a class="iconbox pinterest" data-original-title="pinterest" data-placement="top" data-toggle="tooltip" href="#" title=""><i class="icon-pinterest">&nbsp;</i></a></li>
                <li><a class="iconbox google-plus" data-original-title="google-plus" data-placement="top" data-toggle="tooltip" href="#" title=""><i class="icon-facebook">&nbsp;</i></a></li>
                <li><a class="iconbox google-plus" data-original-title="google-plus" data-placement="top" data-toggle="tooltip" href="#" title=""><i class="icon-google-plus">&nbsp;</i></a></li>
                <li><a class="iconbox twitter" data-original-title="twitter" data-placement="top" data-toggle="tooltip" href="#" title=""><i class="icon-twitter">&nbsp;</i></a></li>
            </ul>
        </div>
    ',
    'demo_widget_contactus_data'=>'
        <ul>
            <li><i class="icon"><img alt="" src="image/data/icon/icon-telephone.png" />&nbsp; </i><span>+01 888 (000) 1234</span></li>
            <li><i class="icon"><img alt="" src="image/data/icon/icon-phone.png" />&nbsp; </i><span>+01 888 (000) 1234</span></li>
            <li><i class="icon"><img alt="" src="image/data/icon/icon-email.png" />&nbsp; </i><span>Email : furniture@gmail.vn</span></li>
            <li><i class="icon"><img alt="" src="image/data/icon/icon-skype.png" />&nbsp; </i><span>BrainOs</span></li>
        </ul>

    ',
        'demo_widget_paypal_data'=>'

        <p><img alt="" src="image/data/paypal/paypal.png" style="width: 171px; height: 18px;" /></p>

    '
    );
    $module = array_merge( $d, $module );

//	echo '<pre>'.print_r( $languages, 1 );die;
?>

<div class="inner-modules-wrap">
    <div class="vtabs mytabs" id="my-tab-innermodules">
        <a onclick="return false;" href="#tab-imodule-footer" class="selected"><?php echo $this->language->get('Footer');?></a>
    </div>

    <div class="page-tabs-wrap">

        <div id="tab-imodule-footer">
            <h4><?php echo $this->language->get( 'Contact Us Module' ) ; ?></h4>

            <div id="language-widget_contactus_data" class="htabs mytabstyle">
                <?php foreach ($languages as $language) { ?>
                <a href="#tab-language-widget_contactus_data-<?php echo $language['language_id']; ?>"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a>
                <?php } ?>
            </div>

            <?php foreach ($languages as $language) {   ?>
            <div id="tab-language-widget_contactus_data-<?php echo $language['language_id']; ?>">

                <table class="form">
                    <?php $text =  isset($module['widget_contactus_data'][$language['language_id']]) ? $module['widget_contactus_data'][$language['language_id']] : $module['demo_widget_contactus_data'];  ?>

                    <tr>
                        <td><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $this->language->get('Contact');?>: </td>
                        <td><textarea name="themecontrol[widget_contactus_data][<?php echo $language['language_id']; ?>]" id="widget_contactus_data-<?php echo $language['language_id']; ?>" rows="5" cols="60"><?php echo $text; ?></textarea></td>
                    </tr>
                </table>
            </div>
            <?php } ?>




            <h4><?php echo $this->language->get( 'Newsletter Module' ) ; ?></h4>

            <div id="language-widget_newsletter_data" class="htabs mytabstyle">
                <?php foreach ($languages as $language) { ?>
                <a href="#tab-language-widget_newsletter_data-<?php echo $language['language_id']; ?>"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a>
                <?php } ?>
            </div>

            <?php foreach ($languages as $language) {   ?>
            <div id="tab-language-widget_newsletter_data-<?php echo $language['language_id']; ?>">

                <table class="form">
                    <?php $text =  isset($module['widget_newsletter_data'][$language['language_id']]) ? $module['widget_newsletter_data'][$language['language_id']] : $module['demo_widget_newsletter_data'];  ?>

                    <tr>
                        <td><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $this->language->get('Newsletter');?>: </td>
                        <td><textarea name="themecontrol[widget_newsletter_data][<?php echo $language['language_id']; ?>]" id="widget_newsletter_data-<?php echo $language['language_id']; ?>" rows="5" cols="60"><?php echo $text; ?></textarea></td>
                    </tr>
                </table>
            </div>
            <?php } ?>



            <h4><?php echo $this->language->get( 'Paypal Module' ) ; ?></h4>

            <div id="language-widget_paypal_data" class="htabs mytabstyle">
                <?php foreach ($languages as $language) { ?>
                <a href="#tab-language-widget_paypal_data-<?php echo $language['language_id']; ?>"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a>
                <?php } ?>
            </div>

            <?php foreach ($languages as $language) {   ?>
            <div id="tab-language-widget_paypal_data-<?php echo $language['language_id']; ?>">

                <table class="form">
                    <?php $text =  isset($module['widget_paypal_data'][$language['language_id']]) ? $module['widget_paypal_data'][$language['language_id']] : $module['demo_widget_paypal_data'];  ?>

                    <tr>
                        <td><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $this->language->get('Paypal');?>: </td>
                        <td><textarea name="themecontrol[widget_paypal_data][<?php echo $language['language_id']; ?>]" id="widget_paypal_data-<?php echo $language['language_id']; ?>" rows="5" cols="60"><?php echo $text; ?></textarea></td>
                    </tr>
                </table>
            </div>
            <?php } ?>



            <script type="text/javascript" src="view/javascript/ckeditor/ckeditor.js"></script>

            <script type="text/javascript"><!--
                $("#language-widget_newsletter_data a").tabs();
                <?php foreach( $languages as $language )  { ?>
                    CKEDITOR.replace('widget_newsletter_data-<?php echo $language['language_id']; ?>', {
                        filebrowserBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
                        filebrowserImageBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
                        filebrowserFlashBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
                        filebrowserUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
                        filebrowserImageUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
                        filebrowserFlashUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>'
                    });
                <?php } ?>
                //--></script>

            <script type="text/javascript"><!--
                $("#language-widget_contactus_data a").tabs();
                <?php foreach( $languages as $language )  { ?>
                    CKEDITOR.replace('widget_contactus_data-<?php echo $language['language_id']; ?>', {
                        filebrowserBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
                        filebrowserImageBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
                        filebrowserFlashBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
                        filebrowserUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
                        filebrowserImageUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
                        filebrowserFlashUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>'
                    });
                <?php } ?>
                //--></script>
            <script type="text/javascript"><!--
                $("#language-widget_paypal_data a").tabs();
                <?php foreach( $languages as $language )  { ?>
                    CKEDITOR.replace('widget_paypal_data-<?php echo $language['language_id']; ?>', {
                        filebrowserBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
                        filebrowserImageBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
                        filebrowserFlashBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
                        filebrowserUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
                        filebrowserImageUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
                        filebrowserFlashUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>'
                    });
                <?php } ?>
                //--></script>
        </div>
    </div>
    <div class="clearfix clear"></div>
</div>

