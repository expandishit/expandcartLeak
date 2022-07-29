<div class="box pav-affiliate">
  <h3 class="box-heading"><?php echo $heading_title; ?></h3>
  <nav class="box-content">
    <ul>
      <?php if (!$logged) { ?>
      <li><i class="icon"></i><a href="<?php echo $login; ?>"><?php echo $text_login; ?></a> / <a href="<?php echo $register; ?>"><?php echo $text_register; ?></a></li>
      <li><i class="icon"></i><a href="<?php echo $forgotten; ?>"><?php echo $text_forgotten; ?></a></li>
      <?php } ?>
      <li><i class="icon"></i><a href="<?php echo $account; ?>"><?php echo $text_account; ?></a></li>
      <?php if ($logged) { ?>
      <li><i class="icon"></i><a href="<?php echo $edit; ?>"><?php echo $text_edit; ?></a></li>
      <li><i class="icon"></i><a href="<?php echo $password; ?>"><?php echo $text_password; ?></a></li>
      <?php } ?>
      <li><i class="icon"></i><a href="<?php echo $payment; ?>"><?php echo $text_payment; ?></a></li>
      <li><i class="icon"></i><a href="<?php echo $tracking; ?>"><?php echo $text_tracking; ?></a></li>
      <li><i class="icon"></i><a href="<?php echo $transaction; ?>"><?php echo $text_transaction; ?></a></li>
      <?php if ($logged) { ?>
      <li><i class="icon"></i><a href="<?php echo $logout; ?>"><?php echo $text_logout; ?></a></li>
      <?php } ?>
    </ul>
  </nav>
</div>
