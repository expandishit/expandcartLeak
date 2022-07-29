<?php if ($thumb || $description) { ?>
  <div class="category-info">
    <?php if ($thumb) { ?>
    <div class="image"><img src="<?php echo $thumb; ?>" alt="<?php echo $heading_title; ?>" /></div>
    <?php } ?>
    <?php if ($description) { ?>
    <?php echo $description; ?>
    <?php } ?>
  </div>
  <?php } ?>
  <?php if ($categories) { ?>
	<div class="refine page">
	  <h2><?php echo $text_refine; ?></h2>
	  <div class="category-list">
		<?php if (count($categories) <= 5) { ?>
		<ul>
			<?php foreach ($categories as $category) { ?>
			<li><a href="<?php echo $category['href']; ?>"><?php echo $category['name']; ?></a></li>
			<?php } ?>
		</ul>
		<?php } else { ?>
		<?php for ($i = 0; $i < count($categories);) { ?>
		<ul>
		  <?php $j = $i + ceil(count($categories) / 4); ?>
		  <?php for (; $i < $j; $i++) { ?>
		  <?php if (isset($categories[$i])) { ?>
		  <li><a href="<?php echo $categories[$i]['href']; ?>"><?php echo $categories[$i]['name']; ?></a></li>
		  <?php } ?>
		  <?php } ?>
		</ul>
		<?php } ?>
		<?php } ?>
	  </div>
	</div>  
  <?php } ?>