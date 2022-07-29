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
  <div class="box">
    <div class="heading">
      <h1><?php echo $heading_title; ?></h1>
      <div class="buttons"><a onclick="$('#form').submit();" class="button btn btn-primary"><?php echo $button_save; ?></a><a href="<?php echo $cancel; ?>" class="button btn btn-primary"><?php echo $button_cancel; ?></a></div>
    </div>
    <div class="content">
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
        <table class="form">
          <tr>
            <td><span class="required">*</span>العنوان</td>

            <td><input type="text" name="title" value="<?php echo $title; ?>" />

          </tr>
          <tr>
            <td><span class="required">*</span>الوصف</td>

            <td><input type="text" name="description" value="<?php echo $description; ?>" />

          </tr>
          <tr>
            <td><span class="required">*</span>التصنيف</td>

            <td>

              <select name="category">
                <?php foreach ($categories as $category) { ?>

                <option value="<?php echo $category['name']; ?>"><?php echo $category['name']; ?></option>
                <?php } ?>
              </select>

            </td>

          </tr>


        </table>
      </form>
    </div>
  </div>
</div>
<?php echo $footer; ?> 