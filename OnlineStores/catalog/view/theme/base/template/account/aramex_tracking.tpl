<?php echo $header; ?><?php echo $column_left; ?><?php echo $column_right; ?>
<div id="content"><?php echo $content_top; ?>
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <h1><?php echo $heading_title; ?></h1>
			   
  
	<form enctype="multipart/form-data" action="" method="post" id="calculate_rate">
		<table class="form">
			<tbody><tr>
			  <td><span class="required">*</span>Enter AWB:</td>
			  <td><input type="text" value="" name="track_awb">
			  <input type="submit" class="button" value="Submit">
				</td>
				
			</tr>
		   </tbody>
		</table>
    </form>
	
    <table >
		<tr>
			<td colspan="4" style="font-weight: bold;;">
				<?php  echo "AWB NO: ".$awb_no;?>
		    </td>
		</tr>
		<tr>
        <td class="left" colspan="4">
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
				
      </tr>
      

    <tbody>
      <?php 
			if(isset($html) && !empty($html))
			{
					echo $html;
			}
			
			?>
    </tbody>
  </table>
  
  
  
  <?php echo $content_bottom; ?></div>
<?php echo $footer; ?> 