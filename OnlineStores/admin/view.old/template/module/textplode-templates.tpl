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
	  		<div class="buttons">
	  			<a onclick="$('#form').submit();" class="button btn btn-primary"><?php echo $button_save; ?></a>
	  			<a href="<?php echo $cancel; ?>" class="button btn btn-primary"><?php echo $button_cancel; ?></a>
	  		</div>
		</div>

		<div class="content">
	  		<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
	        	<table class="form">
					<tr>
						<td><?php echo $template_name;?></td>
						<td>
							<input type="text" name="template_name" style="width: 400px;" value="<?php echo (isset($entry_name)) ? $entry_name : ''; ?>"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $template_merge;?></td>
						<td>
							<a class="merge-tag" style="padding: 5px; background-color: #c0c0c0; border-radius: 3px" data-tag="{order_id}">Order ID</a>
							<a class="merge-tag" style="padding: 5px; background-color: #c0c0c0; border-radius: 3px" data-tag="{date}">Order Date</a>
							<a class="merge-tag" style="padding: 5px; background-color: #c0c0c0; border-radius: 3px" data-tag="{fname}">First Name</a>
							<a class="merge-tag" style="padding: 5px; background-color: #c0c0c0; border-radius: 3px" data-tag="{lname}">Last Name</a>
							<a class="merge-tag" style="padding: 5px; background-color: #c0c0c0; border-radius: 3px" data-tag="{email}">Email Address</a>
							<a class="merge-tag" style="padding: 5px; background-color: #c0c0c0; border-radius: 3px" data-tag="{confirm_code}">Confirmation Code</a>
						</td>
					</tr>
					<tr>
						<td><?php echo $template_content;?></td>
						<td>
							<textarea name="template_content" style="width: 400px;height: 120px;" /><?php echo (isset($entry_content)) ? $entry_content : ''; ?></textarea>
							<input type="hidden" name="language_id" value="<?php echo (isset($_GET['language'])) ? $_GET['language'] : $entry_language; ?>"/>
						</td>
					</tr>
		      	</table>
	  		</form>
		</div>

  	</div>
</div>
<script type="text/javascript"><!--
    $('#htabs a').tabs();

    $('.merge-tag').on('startdrag', function(){

    });

    $('.merge-tag').on('click', function(){
    	$('textarea[name="template_content"]').val($('textarea[name="template_content"]').val() + ' ' + $(this).attr('data-tag'));
    	$('textarea[name="template_content"]').focus();
    });
//--></script>
<?php echo $footer; ?>