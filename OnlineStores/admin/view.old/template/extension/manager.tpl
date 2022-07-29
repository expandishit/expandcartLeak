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
      <div class="buttons"><a id="button-upload" class="button btn btn-primary"><?php echo $button_upload; ?></a></div>
    </div>
    <div class="content">
      <textarea wrap="off" style="width: 98%; height: 300px; padding: 5px; border: 1px solid #CCCCCC; background: #FFFFFF; overflow: scroll;"></textarea>
    </div>
  </div>
</div>
<script type="text/javascript" src="view/javascript/jquery/ajaxupload.js"></script> 
<script type="text/javascript"><!--
new AjaxUpload('#button-upload', {
	action: 'index.php?route=extension/manage/upload&token=<?php echo $token; ?>',
	name: 'file',
	autoSubmit: true,
	responseType: 'text',
	onSubmit: function(file, extension) {
		$('#button-upload').before('<img src="view/image/loading.gif" class="loading" style="padding-right: 5px;" />');
		$('#button-upload').attr('disabled', true);
	},
	onComplete: function(file, json) {
		$('.content').after(json);
		/*
		$('#button-upload').attr('disabled', false);
		
		if (json['success']) {
			alert(json['success']);
		}
		
		if (json['error']) {
			alert(json['error']);
		}
		
		$('.loading').remove();	
		*/
	}
});
//--></script>
<?php echo $footer; ?>