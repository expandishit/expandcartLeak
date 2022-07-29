<?php echo $header; ?>
<div id="content">
    <div class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
        <?php } ?>
    </div>
    <?php if ($error_warning) { ?>
    <script>
        var notificationString = '<?php echo $error_warning; ?>';
        var notificationType = 'warning';
    </script>
    <?php } ?>

    <div class="box">
        <div class="heading">
            <h1><?php echo $heading_title; ?></h1>
            <div class="buttons"><a onclick="$('#form').submit();" class="button btn btn-primary"><?php echo $button_save; ?></a><a href="<?php echo $cancel; ?>" class="button btn btn-primary"><?php echo $button_cancel; ?></a></div>
        </div>
        <div class="content">
            <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
                <table class="form">
                    <tr>
                        <td>Facebook:</td>
                        <td><input type="text" name="facebook" value="<?php echo $facebook;	?>"></td>
                    </tr>
                    <tr>
                        <td>Twitter:</td>
                        <td><input type="text" name="twitter" value="<?php echo $twitter;	?>"></td>
                    </tr>
                    <tr>
                        <td>LinkedIn:</td>
                        <td><input type="text" name="linkedin" value="<?php echo $linkedin;	?>"></td>
                    </tr>
                    <tr>
                        <td>Skype:</td>
                        <td><input type="text" name="skype" value="<?php echo $skype;	?>"></td>
                    </tr>
                    <tr>
                        <td>google+:</td>
                        <td><input type="text" name="gplus" value="<?php echo $gplus;	?>"></td>
                    </tr>
                    <tr>
                        <td>My Space:</td>
                        <td><input type="text" name="myspace" value="<?php echo $myspace;	?>"></td>
                    </tr>
                    <tr>
                        <td>Flickr:</td>
                        <td><input type="text" name="flickr" value="<?php echo $flickr;	?>"></td>
                    </tr>
                    <tr>
                        <td>Rss feed:</td>
                        <td><input type="text" name="rss" value="<?php echo $rss;	?>"></td>
                    </tr>
                    <tr>
                        <td>Description:</td>
                        <td><textarea name="description" cols="70" rows="5"><?php echo $description;	?></textarea></td>
                    </tr>
                </table>
            </form>
        </div>
    </div>
</div>
<?php echo $footer; ?>