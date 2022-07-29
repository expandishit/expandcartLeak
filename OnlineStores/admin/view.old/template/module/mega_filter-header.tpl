<?php echo $header; ?>

<?php

	$HTTP_URL = '';
	
	if( class_exists( 'MijoShop' ) ) {
		$HTTP_URL = HTTP_CATALOG . 'opencart/admin/';
	}

?>

<!--<link type="text/css" href="<?php echo $HTTP_URL; ?>view/stylesheet/mf/css/bootstrap.css" rel="stylesheet" />-->
<link type="text/css" href="<?php echo $HTTP_URL; ?>view/stylesheet/mf/css/style.css" rel="stylesheet" />

<script type="text/javascript">
	$ = jQuery = $.noConflict(true);
</script>

<script type="text/javascript" src="<?php echo $HTTP_URL; ?>view/stylesheet/mf/js/jquery.min.js"></script>

<script type="text/javascript">
	var $$			= $.noConflict(true),
		$jQuery		= $$;
</script>

<!--<script type="text/javascript" src="<?php echo $HTTP_URL; ?>view/stylesheet/mf/js/bootstrap.js"></script>-->
<script type="text/javascript" src="<?php echo $HTTP_URL; ?>view/stylesheet/mf/js/json.js"></script>

<script type="text/javascript" src="<?php echo $HTTP_URL; ?>view/stylesheet/mf/js/jquery.form.js"></script>

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

	<?php if ($error_warning) { ?>
	<script>
		var notificationString = '<?php echo $error_warning; ?>';
		var notificationType = 'warning';
	</script>
	<?php } ?>
	<h1><?php echo $heading_title; ?></h1>
	<div class="mega-filter-pro">

		<div class="box">
			<div class="heading">

				<div class="buttons">
					<a id="mf-save-form" class="btn btn-success btn-sm"><i class="glyphicon glyphicon-ok"></i> <?php echo $button_save; ?></a>
					<a href="<?php echo $back; ?>" class="btn btn-danger btn-sm"><i class="glyphicon glyphicon-chevron-left"></i> <?php echo $button_back; ?></a>
				</div>
			</div>

			<script type="text/javascript">
				jQuery('#mf-save-form').click(function(){
					if( jQuery('#form').attr('data-to-ajax')!='1' ) {
						jQuery('#form').submit();
						
						return false;
					}
				});
			</script>

			<div class="content" id="mf-main-content" style="position: relative;">
				<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
					<ul class="nav nav-tabs">
						<li<?php if( $tab_active == $_name ) { ?> class="active"<?php } ?>><a href="<?php echo $tab_layout_link; ?>"><i class="glyphicon glyphicon-file"></i> <?php echo $tab_layout; ?></a></li>
						<li<?php if( $tab_active == 'attributes' ) { ?> class="active"<?php } ?>><a href="<?php echo $tab_attributes_link; ?>"><i class="glyphicon glyphicon-list"></i> <?php echo $tab_attributes; ?></a></li>
						<li<?php if( $tab_active == 'options' ) { ?> class="active"<?php } ?>><a href="<?php echo $tab_options_link; ?>"><i class="glyphicon glyphicon-list"></i> <?php echo $tab_options; ?></a></li>
						<?php if( isset( $tab_filters_link ) ) { ?>
							<li<?php if( $tab_active == 'filters' ) { ?> class="active"<?php } ?>><a href="<?php echo $tab_filters_link; ?>"><i class="glyphicon glyphicon-filter"></i> <?php echo $tab_filters; ?></a></li>
						<?php } ?>
						<li<?php if( $tab_active == 'settings' ) { ?> class="active"<?php } ?>><a href="<?php echo $tab_settings_link; ?>"><i class="glyphicon glyphicon-cog"></i> <?php echo $tab_settings; ?></a></li>
						<li<?php if( $tab_active == 'about' ) { ?> class="active"<?php } ?>><a style="display: block" href="<?php echo $tab_about_link; ?>"><i class="glyphicon glyphicon-question-sign"></i> <?php echo $tab_about; ?></a></li>
						<li style="display: block; float:left; padding: 8px 0 0 5px;"><?php echo $text_before_change_tab; ?></li>
					</ul>