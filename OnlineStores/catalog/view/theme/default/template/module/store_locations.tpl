<div class="box">
  <div class="box-heading"><?php echo $heading_title; ?></div>
  <div class="box-content">
    <div class="box-product">
    	<?php foreach($Locations as $sLocation) { ?>
        	<div>
               	<div class="name"><a href="<?php echo $sLocation['href']; ?>"><b><?php echo $sLocation['Name']; ?></b></a></div>
                    <div class="price"><?php echo $sLocation['Address']; ?></div>
             </div>
        <?php } ?>
        <div class="name"><a href="<?php echo $link_all_locations; ?>"><b><?php echo $txt_All_Locations; ?></b></a></div>
    </div>
  </div>
</div>
