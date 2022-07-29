<div class="table-responsive">
  <table class="table table-bordered table-hover">
    <thead>
      <tr>
        <td class="text-left"><?php echo $column_date_added; ?></td>
        <td class="text-left"><?php echo $column_description; ?></td>
        <td class="text-right"><?php echo $column_credit; ?></td>
      </tr>
    </thead>
    <tbody>
    <?php if ($credits) { ?>
      <?php foreach($credits as $credit) { ?>
      <tr>
        <td class="text-left"><?php echo $credit['date_added']; ?></td>
        <td class="text-left"><?php echo $credit['description']; ?></td>
        <td class="text-right"><?php echo $credit['amount']; ?></td>
      </tr>
      <?php } ?>
      <tr>
        <td></td>
        <td class="text-right"><b><?php echo $column_balance; ?></b></td>
        <td class="text-right"><?php echo $balance; ?></td>
      </tr>
    <?php } else { ?>
      <tr>
        <td class="text-center" colspan="3"><?php echo $text_no_results; ?></td>
      </tr>
    <?php } ?>
    </tbody>
  </table>
</div>
<div class="row">
  <div class="col-sm-6 text-left"><?php echo $pagination; ?></div>
  <div class="col-sm-6 text-right"><?php echo $results; ?></div>
</div>
<script type="text/javascript">
function validate(key, thisthis, nodot) {
  //getting key code of pressed key
  var keycode = (key.which) ? key.which : key.keyCode;

  if (keycode == 46) {
    if (nodot) {
      return false;
    }

    var val = $(thisthis).val();
    if (val == val.replace('.', '')) {
      return true;
    } else {
      return false;
    }
  }

  if (keycode == 45) {
    var val = $(thisthis).val();
    if (val == val.replace('-', '')) {
      return true;
    } else {
      return false;
    }
  }

  //comparing pressed keycodes
  if (!(keycode == 8 || keycode == 9 || keycode == 46 || keycode == 116) && (keycode < 48 || keycode > 57)) {
    return false;
  } else {
    return true;
  }
}
</script>
