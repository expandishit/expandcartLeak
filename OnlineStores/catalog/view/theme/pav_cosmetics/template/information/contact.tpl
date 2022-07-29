<?php require( DIR_TEMPLATE.$this->config->get('config_template')."/template/common/config.tpl" );
    $themeConfig = $this->config->get( 'themecontrol' );
?>
<?php echo $header; ?>
<?php require( DIR_TEMPLATE.$this->config->get('config_template')."/template/common/breadcrumb.tpl" );  ?>  
<?php if( $SPAN[0] ): ?>
<aside class="col-lg-<?php echo $SPAN[0];?> col-md-<?php echo $SPAN[0];?> col-sm-12 col-xs-12">
	<?php echo $column_left; ?>
</aside>
<?php endif; ?> 
<section class="col-lg-<?php echo $SPAN[1];?> col-md-<?php echo $SPAN[1];?> col-sm-12 col-xs-12">
<div id="content"><?php echo $content_top; ?>
  
  <h1><?php echo $heading_title; ?></h1>

            <?php //start contact map ?>
            <div class="contact-location">
                <div id="contact-map"></div>
            </div>
            <?php // Jquery googlemap api v3?>
            <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAMHkjdQvUVxxzT27VvQ6wfjzhSm4Z3c_o&v=3.exp&sensor=false&language=en"></script>
            <script type="text/javascript" src="catalog/view/javascript/gmap/gmap3.min.js"></script>
            <script type="text/javascript" src="catalog/view/javascript/gmap/gmap3.infobox.js"></script>
            <script type="text/javascript">
                var mapDiv, map, infobox;
                var lat = <?php echo isset($themeConfig['location_latitude'])?$themeConfig['location_latitude']:'40.705423'; ?>;
                var lon = <?php echo isset($themeConfig['location_longitude'])?$themeConfig['location_longitude']:'-74.008616'; ?>;
                jQuery(document).ready(function($) {
                    mapDiv = $("#contact-map");
                    mapDiv.height(400).gmap3({
                        map:{
                            options:{
                                center:[lat,lon],
                                zoom: 15
                            }
                        },
                        marker:{
                            values:[
                                {latLng:[lat, lon], data:"<?php echo isset($themeConfig['location_address'])?$themeConfig['location_address']:'79-99 Beaver Street, New York, NY 10005, USA'; ?>"},
                            ],
                            options:{
                                draggable: false
                            },
                            events:{
                                  mouseover: function(marker, event, context){
                                    var map = $(this).gmap3("get"),
                                        infowindow = $(this).gmap3({get:{name:"infowindow"}});
                                    if (infowindow){
                                        infowindow.open(map, marker);
                                        infowindow.setContent(context.data);
                                    } else {
                                        $(this).gmap3({
                                        infowindow:{
                                            anchor:marker, 
                                            options:{content: context.data}
                                        }
                                      });
                                    }
                                },
                                mouseout: function(){
                                    var infowindow = $(this).gmap3({get:{name:"infowindow"}});
                                    if (infowindow){
                                        infowindow.close();
                                    }
                                }
                            }
                        }
                    });
                });
            </script>
            <?php //end contact map ?>

         <?php  if(  isset($themeConfig['contact_customhtml']) && isset($themeConfig['contact_customhtml'][$this->config->get('config_language_id')]) && trim($themeConfig['contact_customhtml'][$this->config->get('config_language_id')]) ){ ?>
        <div class="contact-customhtml">
            <?php echo ($themeConfig['contact_customhtml'][$this->config->get('config_language_id')]); ?>
        </div>
        <?php } ?> 

  <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data">
    <h2><?php echo $text_location; ?></h2>
    <div class="contact-info">
      <div class="content"><div class="left"><b><?php echo $text_address; ?></b><br />
        <?php echo $store; ?><br />
        <?php echo $address; ?></div>
      <div class="right">
        <?php if ($telephone) { ?>
        <b><?php echo $text_telephone; ?></b><br />
        <?php echo $telephone; ?><br />
        <br />
        <?php } ?>
        <?php if ($fax) { ?>
        <b><?php echo $text_fax; ?></b><br />
        <?php echo $fax; ?>
        <?php } ?>
      </div>
    </div>
    </div>
    <h2><?php echo $text_contact; ?></h2>
    <div class="content">
    <b><?php echo $entry_name; ?></b><br />
    <input class="form-control" type="text" name="name" value="<?php echo $name; ?>" />
    <br />
    <?php if ($error_name) { ?>
    <span class="error"><?php echo $error_name; ?></span>
    <?php } ?>
    <br />
    <b><?php echo $entry_email; ?></b><br />
    <input class="form-control" type="text" name="email" value="<?php echo $email; ?>" />
    <br />
    <?php if ($error_email) { ?>
    <span class="error"><?php echo $error_email; ?></span>
    <?php } ?>
    <br />
    <b><?php echo $entry_enquiry; ?></b><br />
    <textarea class="form-control" name="enquiry" cols="40" rows="10" style="width: 99%;"><?php echo $enquiry; ?></textarea>
    <br />
    <?php if ($error_enquiry) { ?>
    <span class="error"><?php echo $error_enquiry; ?></span>
    <?php } ?>
    <br />
    <b><?php echo $entry_captcha; ?></b><br />
    <span class="navbar-form">
        <span class="form-group"><input class="form-control" type="text" name="captcha" value="<?php echo $captcha; ?>" /></span>
    <img src="index.php?route=common/captcha" alt="" />
    <?php if ($error_captcha) { ?>
    <span class="error"><?php echo $error_captcha; ?></span>
    <?php } ?>
    </div>
    <div class="buttons">
      <div class="right"><input type="submit" value="<?php echo $button_continue; ?>" class="button" /></div>
    </div>
  </form>
  <?php echo $content_bottom; ?></div>
</section> 
<?php if( $SPAN[2] ): ?>
<aside class="col-lg-<?php echo $SPAN[2];?> col-sm-<?php echo $SPAN[2];?> col-xs-12">	
	<?php echo $column_right; ?>
</aside>
<?php endif; ?>
<?php echo $footer; ?>