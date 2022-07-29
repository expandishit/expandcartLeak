<div class="box category">
  <div class="box-heading"><?php echo $heading_title; ?></div>
  <div class="box-content">
    <div class="box-category">
      <ul class="mega-category">
      <?php foreach ($categories as $category) { ?>
      
        <li>

        	<?php if ($category['category_id'] == $category_id) { ?>
            <a href="<?php echo $category['href']; ?>" class="active"><?php echo $category['name']; ?></a>
            <?php } else { ?>
            <a href="<?php echo $category['href']; ?>"><?php echo $category['name']; ?></a>
            <?php } ?>
            
            
        	<!--sub menu-->
            <?php if ($category['children']) { ?>
        	<div class="sub-menu">
            <span class="arrowMenu2"></span>
            	<!--image-->
                <?php if ($category['image'] != 'no_image.png') { ?>
      <img class="catImg" src="image/<?php echo $category['storecode']; ?>/<?php echo $category['image']; ?>" title="<?php echo $category['name']; ?>" alt="<?php echo $category['name']; ?>"width="300">
                <?php } ?>
                <!--list-->
                
            	<div class="sub-sub-menu">
                	
                	<ul style="<?php echo $category['image'] != 'no_image.png' ? '' : 'width:100% !important;'; ?>">
                    	<?php foreach ($category['children'] as $child) { ?>
                    	<li class="heading-sub">
                            <?php if ($child['category_id'] == $child_id) { ?>
                            <a href="<?php echo $child['href']; ?>" class="active secondCat"><?php echo $child['name']; ?></a>
                            <?php } else { ?>
                            <a href="<?php echo $child['href']; ?>"><?php echo $child['name']; ?></a>
                            <?php } ?>
                        
                        <!-- here 3rd level -->
              			<?php if($child['sister_id']){ ?>
                        <ul class="thirdCat">
                            <?php foreach($child['sister_id'] as $sisters) { ?>
                            <li>
                                <?php if ($sisters['category_id'] == $sisters_id) { ?>   
                                <a href="<?php echo $sisters['href']; ?>" ><?php echo $sisters['name']; ?></a>
                                <?php } else { ?>
                                <a href="<?php echo $sisters['href']; ?>"><?php echo $sisters['name']; ?></a>
                                <?php } ?>
                            </li>
                            <?php } ?>
                        </ul>
                        <?php } ?>
                        
                        </li>
                        <?php } ?>
                        
                    </ul>
   					
                </div><!--/sub-sub-menu-->
                
                <div class="clearfix"></div>
                <h3><?php echo $category['name']; ?></h3>
                <p>
                <?php echo $category['description']; ?>
                </p>
                <div class="clearfix"></div>
                <a href="<?php echo $category['href']; ?>" class="button"><?php echo $goto_text; ?> <?php echo $category['name']; ?></a>
            </div><!--/submenu-->
            <?php } ?>
        </li>
    
      <?php } ?>
      </ul>
    </div>
  </div>
</div>
