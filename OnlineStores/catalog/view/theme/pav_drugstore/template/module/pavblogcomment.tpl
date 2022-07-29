<div class="box pavblogs-comments-box">
	<h3 class="box-heading">
		<span><?php echo $heading_title; ?></span>
	</h3>
	<div class="box-content nopadding">
		<?php if( !empty($comments) ) { ?>
		<div class="pavblog-comments clearfix">
			 <?php $default=''; foreach( $comments as $comment ) { ?>
				<div class="pav-comment media nomargin">
					<a class="pull-left" href="<?php echo $comment['link'];?>" title="<?php echo $comment['user'];?>">
						<img src="<?php echo "http://www.gravatar.com/avatar/" . md5( strtolower( trim( $comment['email'] ) ) ) . "?d=" . urlencode( $default ) . "&s=60" ?>" align="left"/>
					</a>
					<div class="media-body">
						<p class="comment"><?php echo utf8_substr( $comment['comment'], 50 ); ?></p>
						<p><span class="comment-author color"><?php echo $this->language->get('text_postedby');?> <?php echo $comment['user'];?>...</span></p>
					</div>					
				</div>
			 <?php } ?>
		</div>
		<?php } ?>
	</div>
 </div>
