<?php echo $header; ?>
<div id="content">
    <ol class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <?php if ($breadcrumb === end($breadcrumbs)) { ?>
        <li class="active">
            <?php } else { ?>
        <li>
            <?php } ?>
            <a href="<?php echo $breadcrumb['href']; ?>">
                <?php if ($breadcrumb === reset($breadcrumbs)) { ?>
                <?php echo $breadcrumb['text']; ?>
                <?php } else { ?>
                <span><?php echo $breadcrumb['text']; ?></span>
                <?php } ?>
            </a>
        </li>
        <?php } ?>
    </ol>
<div class="box">
<div class="heading">
  <h1><?php echo $heading_title; ?></h1>
  <div class="buttons"> 
  <a href="<?php echo $back_to_order; ?>" class="button btn btn-primary"><?php echo $text_back_to_order; ?></a>
  <?php
  if($is_shipment)
  {
  ?>
  <a href="<?php echo $aramex_create_sipment; ?>" class="button btn btn-primary"><?php echo $text_return_shipment; ?></a>
  <?php
  }else{
  ?>
   <a href="<?php echo $aramex_create_sipment; ?>" class="button btn btn-primary"><?php echo $text_create_sipment; ?></a>
  <?php } ?>
  <a href="<?php echo $aramex_rate_calculator; ?>"  class="button btn btn-primary"><?php echo $text_rate_calculator; ?></a>
  <?php
  if($is_shipment)
  {
  ?>
	<a href="<?php echo $aramex_print_label; ?>" target="_blank" class="button btn btn-primary"><?php echo $text_print_label; ?></a>
	<a href="<?php echo $aramex_schedule_pickup; ?>"  class="button btn btn-primary"><?php echo $text_schedule_pickup; ?></a>
	<a href="<?php echo $aramex_traking; ?>"  class="button btn btn-primary"><?php echo $text_track; ?></a>
  <?php
  }
  ?>
  
  </div>
</div>
<div class="content">
  <!--  code --->
  <form enctype="multipart/form-data" action="" method="post" id="calculate_rate" novalidate="novalidate">
    <table width="100%" class="list">
	 <tr class="filter" align="center">
		 
		<td colspan="2" style="font-weight: bold;;">
			<input type="text" name="track_awb" value="<?php  echo $awb_no;?>">
			<a class="button btn btn-primary" onclick="$('#calculate_rate').submit();;"><?php echo $text_track; ?></a>
		 </td>
	 </tr>
	 <tr class="filter">
			<td colspan="2" style="font-weight: bold;;">
				<?php  echo "AWB NO: ".$awb_no;?>
		    </td>
	 </tr>
	 <tr>
        <td colspan="4" style="color:red;">
				<?php
					if(isset($eRRORS) && !empty($eRRORS))
					{
						foreach($eRRORS as $val)
						{
								echo $val;
								echo "<br>";
						}
					}
				?>
		</td>
	 </tr>
	 </table>
      <?php 
			if(isset($html) && !empty($html))
			{
					echo $html;
			}
			
			?>
	  
    
	
  </form>
 
  <!-- code --->
</div>
</div>		
<?php echo $footer; ?>