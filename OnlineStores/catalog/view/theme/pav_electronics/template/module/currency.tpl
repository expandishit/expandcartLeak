<?php if (count($currencies) > 1) { ?>
	<div id="currency" class="pull-left"> 
		<!--<span><?php echo $text_currency; ?> : </span> -->
		<div class="btn-group">
			<a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
				<span>			
					<?php foreach ($currencies as $currency) { ?>
					<?php if ($currency['code'] == $currency_code) { ?>
					<?php if ($currency['symbol_left']) { ?>
							
						<?php /* <b><?php echo $currency['symbol_left']; ?></b> */ ?>
						<?php echo $currency['title']; ?>			
					
						
					<?php } else { ?>
					
						<?php /* <b><?php echo $currency['symbol_right']; ?></b> */ ?>
						<?php echo $currency['title']; ?>
					
					<?php } break; }  }?>
				</span>			
				<span class="caret"></span>
			</a>
			<div class="dropdown-menu">
				<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data">
					<div>
					<?php foreach ($currencies as $currency) { ?>
					<?php if ($currency['code'] == $currency_code) { ?>
					<?php if ($currency['symbol_left']) { ?>
					<a title="<?php echo $currency['title']; ?>">			
					<b><?php echo $currency['symbol_left']; ?></b> 
					
					</a>

					<?php } else { ?>
					<a title="<?php echo $currency['title']; ?>">
					<b><?php echo $currency['symbol_right']; ?></b> 

					</a>
					<?php } ?>

					<?php } else { ?>
					<?php if ($currency['symbol_left']) { ?>
					<a title="<?php echo $currency['title']; ?>" onclick="$('input[name=\'currency_code\']').attr('value', '<?php echo $currency['code']; ?>'); $(this).parent().parent().submit();"><?php echo $currency['symbol_left']; ?></a>
					<?php } else { ?>
					<a title="<?php echo $currency['title']; ?>" onclick="$('input[name=\'currency_code\']').attr('value', '<?php echo $currency['code']; ?>'); $(this).parent().parent().submit();"><?php echo $currency['symbol_right']; ?></a>
					<?php } ?>
					<?php } ?>
					<?php } ?>

					<input type="hidden" name="currency_code" value="" />
					<input type="hidden" name="redirect" value="<?php echo $redirect; ?>" />
					</div>
				</form>
			</div>
		</div>
	</div>
<?php } ?>