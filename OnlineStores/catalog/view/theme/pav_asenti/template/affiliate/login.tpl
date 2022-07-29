<?php require( DIR_TEMPLATE.$this->config->get('config_template')."/template/common/config.tpl" ); ?>
<?php echo $header; ?>

<?php if( $SPAN[0] ): ?>
	<aside class="span<?php echo $SPAN[0];?>">
		<?php echo $column_left; ?>
	</aside>
<?php endif; ?> 

<section class="span<?php echo $SPAN[1];?>">
	<?php if ($success) { ?>
		<div class="success"><?php echo $success; ?></div>
	<?php } ?>
	<?php if ($error_warning) { ?>
		<div class="warning"><?php echo $error_warning; ?></div>
	<?php } ?>
 
	<div id="content" class="affiliate-login">
		<?php echo $content_top; ?>
		<div class="breadcrumb">
			<?php foreach ($breadcrumbs as $breadcrumb) { ?>
			<?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
			<?php } ?>
		</div>
		<h1><?php echo $heading_title; ?></h1>
		
		<div class="description">
			<?php echo $text_description; ?>
		</div>
		
		<div class="login-content">
			<div class="row-fluid">
				<div class="span6">
					<div class="inner">
						<h2><?php echo $text_new_affiliate; ?></h2>
						<div class="content"><?php echo $text_register_account; ?> <a href="<?php echo $register; ?>" class="button"><?php echo $button_continue; ?></a></div>
					</div>
				</div>
			
				<div class="span6">
					<div class="inner">
						<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data">
							<h2><?php echo $text_returning_affiliate; ?></h2>
							<div class="content">
								<p><?php echo $text_i_am_returning_affiliate; ?></p>
								<p><b><?php echo $entry_email; ?></b></p>
								<p><input type="text" name="email" value="<?php echo $email; ?>" class="span8" /></p>
								<p><b><?php echo $entry_password; ?></b></p>
								<p><input type="password" name="password" value="<?php echo $password; ?>" class="span8" /></p>
								<p><a href="<?php echo $forgotten; ?>"><?php echo $text_forgotten; ?></a></p>														
								<p>
									<input type="submit" value="<?php echo $button_login; ?>" class="button" />
									<?php if ($redirect) { ?>
									<input type="hidden" name="redirect" value="<?php echo $redirect; ?>" />
									<?php } ?>
								</p>
							</div>
						</form>
					</div>					
				</div>
			</div>			
		</div>
	<?php echo $content_bottom; ?>
  </div>
</section> 

<?php if( $SPAN[2] ): ?>
<aside class="span<?php echo $SPAN[2];?>">	
	<?php echo $column_right; ?>
</aside>
<?php endif; ?>

<?php echo $footer; ?>