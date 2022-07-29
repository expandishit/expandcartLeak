<?php echo $header; ?><?php echo $column_left; ?><?php echo $column_right; ?>
<div id="content">
	<div class="top">
		<div class="left"></div>
		<div class="right"></div>
		<div class="center">
			<h1><?php echo $heading_title; ?></h1>
		</div>
	</div>
	<div class="middle">
            <?php
            echo "<div class=\"warning\">".$general_error ."</div>";
            ?>
	</div>
        <div class="buttons">
          <table>
            <tr>
              <td align="left"><a href="<?php echo $back; ?>" class="button"><?php echo $button_back; ?></a></td>
              <td align="right"></td>
            </tr>
          </table>
        </div>    
	<div class="bottom">
		<div class="left"></div>
		<div class="right"></div>
		<div class="center"></div>
	</div>
</div>
<?php echo $footer; ?> 