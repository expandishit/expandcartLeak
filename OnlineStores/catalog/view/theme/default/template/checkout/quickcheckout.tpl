<?php if($this->session->data['ismobile'] != "1") { ?>
<?php echo $header; ?><?php echo $column_left; ?><?php echo $column_right; ?>
<div id="content">
  <?php echo $content_top; ?>
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>

  <!-- Quick Checkout v4.0 by Dreamvention.com checkout/quickcheckout.tpl -->
  <?php echo $quickcheckout; ?>

  <?php echo $content_bottom; ?>
</div>
<?php echo $footer; ?>

<?php } else { ?>
<?php echo $header; ?>
<div id="content">
<?php echo $quickcheckout; ?>
</div>
<?php } ?>