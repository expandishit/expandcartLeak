<?php echo $header; ?><?php echo $column_left; ?><?php echo $column_right; ?>
<div id="content"><?php echo $content_top; ?>
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <h1><?php echo $heading_title; ?></h1>
  <div class="middle">
  <div>
          <table class="prods" width="100%">
          		<?php if($no_location) { ?>
                	<tr>
                    	<td colspan="2">
                        <div class="warning"><?php echo $txtWrong_loc_id; ?></div>
                        </td>
                    </tr>
                <?php } 
                	else {
                 ?>
          		<tr>
                	<td width="60%">
                    	<div><img src="<?php echo $Location['thumb']; ?>" /> </div>
                        <?php if ($images) { ?>
                          <div class="image-additional">
                            <?php foreach ($images as $image) { ?>
                            <a href="<?php echo $image['popup']; ?>" title="<?php echo $heading_title; ?>" class="fancybox" rel="fancybox"><img src="<?php echo $image['thumb']; ?>" title="<?php echo $heading_title; ?>" alt="<?php echo $heading_title; ?>" /></a>
                            <?php } ?>
                          </div>
                          <?php } ?>
                    </td>
                    <td width="40%" valign="top">
                    	<table class="prod">
                        	<tr>
                            	<td style="width:50px;vertical-align:top;font-weight:bold;"><?php echo $store_loc_address ?></td>
                                <td><?php echo $Location['Address']; ?></td>
                            </tr>
                            <tr>
                            	<td style="width:50px;vertical-align:top;font-weight:bold;"><?php echo $store_loc_offers ?></td>
                                <td><?php echo $Location['SpecialOffers']; ?></td>
                            </tr>
                            <tr>
                            	<td style="width:50px;vertical-align:top;font-weight:bold;"><?php echo $store_loc_timing ?></td>
                                <td><?php echo $Location['Timing']; ?></td>
                            </tr>
                            <tr>
                            	<td style="width:50px;vertical-align:top;font-weight:bold;"><?php echo $store_loc_phone ?></td>
                                <td><?php echo $Location['Phone']; ?></td>
                            </tr>
                             <tr>
                            	<td style="width:50px;vertical-align:top;font-weight:bold;"><?php echo $store_loc_email ?></td>
                                <td><a href="mailto:<?php echo $Location['Email']; ?>"><?php echo $Location['Email']; ?></a></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                	<td valign="top"><?php echo $Location['Details']; ?>
                    <div id="mapDirections"></div>
                    </td>
                    <td valign="top" class="gmapstyle"> 
                    	<?php
                            echo $gmapHeaderJS;
                            echo $gmapJS;
                            echo $gmaponLoad;
                            echo $gmapHtml;
	                     ?>
                    </td>
                </tr>
                <?php } ?>
        </table>
        </div>
  </div>
    <script type="text/javascript" src="catalog/view/javascript/jquery/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
    <link rel="stylesheet" type="text/css" href="catalog/view/javascript/jquery/fancybox/jquery.fancybox-1.3.4.css" media="screen" />
  <script language="javascript">
  	function getAdd(pLink) {
		//alert( encodeURI($("#stAddress").val()));
		window.location =  encodeURI(pLink + "&stAddress=" + $("#stAddress").val());
	}
  </script>
  <script type="text/javascript"><!--
$('.fancybox').fancybox({cyclic: true});
//--></script> 
 <?php echo $content_bottom; ?>
   </div>
   <?php echo $footer; ?>