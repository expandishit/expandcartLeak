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
  <?php if ($error_warning) { ?>
    <script>
        var notificationString = '<?php echo $error_warning; ?>';
        var notificationType = 'warning';
    </script>
  <?php } ?>
    <?php if ($success) { ?>
    <script>
        var notificationString = '<?php echo $success; ?>';
        var notificationType = 'success';
    </script>
    <?php } ?>

    <?php echo $newspanel; ?>
  <br />
<div class="box">
<div class="heading">
	<h1><?php echo $heading_title; ?></h1>
	<div class="buttons"><a onclick="location = '<?php echo $insert; ?>'" class="button btn btn-primary"><span><?php echo $button_insert; ?></span></a><a onclick="$('form').submit();" class="button btn btn-primary"><span><?php echo $button_delete; ?></span></a></div>
</div>
 <div class="content">
<form action="<?php echo $delete; ?>" method="post" enctype="multipart/form-data" id="form">
	<table class="table table-hover dataTable no-footer">
		<thead>
			<tr>
				<td width="1" align="center"><input type="checkbox" onclick="$('input[name*=\'delete\']').attr('checked', this.checked);" /></td>
				<td class="left">
					<?php if ($sort == 'nd.title') { ?>
						<a href="<?php echo $sort_title; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_title; ?></a>
					<?php } else { ?>
						<a href="<?php echo $sort_title; ?>"><?php echo $column_title; ?></a>
					<?php } ?>
				</td>
				<td class="right">
					<?php if ($sort == 'n.status') { ?>
						<?php echo $column_status; ?>
					<?php } else { ?>
						<?php echo $column_status; ?>
					<?php } ?>
				</td>
				<td class="right"><?php echo $column_action; ?></td>
			</tr>
		</thead>
		<tbody>
			<?php if ($news) { ?>
				<?php $class = 'odd'; ?>
				<?php foreach ($news as $news_story) { ?>
					<?php $class = ($class == 'even' ? 'odd' : 'even'); ?>
					<tr class="<?php echo $class; ?>">
						<td align="center">
							<?php if ($news_story['delete']) { ?>
								<input type="checkbox" name="delete[]" value="<?php echo $news_story['news_id']; ?>" checked="checked" />
							<?php } else { ?>
								<input type="checkbox" name="delete[]" value="<?php echo $news_story['news_id']; ?>" />
							<?php } ?>
						</td>
						<td class="left"><?php echo $news_story['title']; ?></td>
						<td class="right"><?php echo $news_story['status']; ?></td>
						<td class="right">
							<?php foreach ($news_story['action'] as $action) { ?>
								[ <a href="<?php echo $action['href']; ?>"><?php echo $action['text']; ?></a> ]
							<?php } ?>
						</td>
					</tr>
				<?php } ?>
			<?php } else { ?>
				<tr class="even">
					<td class="center" colspan="5"><?php echo $text_no_results; ?></td>
				</tr>
			<?php } ?>
		</tbody>
	</table>
</form>
<div class="pagination"><?php echo $pagination; ?></div>
</div>
    <?php echo $footer; ?>