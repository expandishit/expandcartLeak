<div class="box pav-store">
  <h3 class="box-heading"><?php echo $heading_title; ?></h3>
  <section class="box-content">
    <p style="text-align: center;"><?php echo $text_store; ?></p>
    <?php foreach ($stores as $store) { ?>
    <?php if ($store['store_id'] == $store_id) { ?>
    <a href="<?php echo $store['url']; ?>"><b><?php echo $store['name']; ?></b></a><br />
    <?php } else { ?>
    <a href="<?php echo $store['url']; ?>"><?php echo $store['name']; ?></a>
    <br />
    <?php } ?>
    <?php } ?>
    <br />
  </section>
</div>
