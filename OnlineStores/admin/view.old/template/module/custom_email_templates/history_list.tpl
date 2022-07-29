<table class="table table-bordered">
  <thead>
    <tr>
      <td class=""></td>
	  <td class=""><?php echo $column_subject; ?></td>
	  <td class=""><?php echo $column_code; ?></td>
	  <td class=""><?php echo $column_email; ?></td>
	  <td class=""><?php echo $column_attachment; ?></td>
      <td class=""><?php echo $column_date_added; ?></td>
      <td class=""><?php echo $column_date_opened; ?></td>
	  <td class=""></td>
    </tr>
  </thead>
  <tbody>
    <?php if ($histories) { ?>
    <?php foreach ($histories as $history) { ?>
    <tr>
      <td class="">#<?php echo $history['history_id']; ?></td>
	  <td class=""><?php echo $history['subject']; ?></td>
	  <td class=""><?php echo $history['code']; ?></td>
      <td class=""><?php echo $history['email']; ?></td>
      <td class=""><?php if ($history['attachment']) { ?>
        <table class="table table-bordered">
          <tbody>
            <?php foreach ($history['attachment'] as $attachment) { ?>
              <tr>
                <td class=""><a href="<?php echo $attachment['href']; ?>"><?php echo $attachment['file']; ?></a></td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
	  <?php } ?></td>
	  <td class=""><?php echo $history['date_added']; ?></td>
      <td class=""><?php echo $history['date_opened']; ?></td>
	  <td class=""><a id="button-template-preview" data-toggle="tooltip" data-history-id="<?php echo $history['history_id']; ?>" title="<?php echo $button_preview; ?>" class="btn btn-info"><i class="fa fa-eye"></i></a> <a id="button-history-remove" data-toggle="tooltip" data-history-id="<?php echo $history['history_id']; ?>" title="<?php echo $button_delete; ?>" class="btn btn-danger"><i class="fa fa-trash-o"></i></a></td>
    </tr>
    <?php } ?>
    <?php } else { ?>
    <tr>
      <td class="text-center" colspan="8"><?php echo $text_no_results; ?></td>
    </tr>
    <?php } ?>
  </tbody>
</table>
<div class="row">
  <div class="col-sm-6 "><?php echo $pagination; ?></div>
  <div class="col-sm-6 "><?php echo $results; ?></div>
</div>