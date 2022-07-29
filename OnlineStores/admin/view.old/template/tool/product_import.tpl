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
    <?php if ($success) { ?>
    <script>
        var notificationString = '<?php echo $success; ?>';
        var notificationType = 'success';
    </script>
    <?php } ?>
  <div class="box">
    <div class="heading">
      <h1><?php echo $heading_title; ?></h1>
    </div>
    <div class="content">
	  
		  <div id="htabs" class="htabs">
			<a href="#productimport"><?php echo $text_products; ?></a>
			<a href="#productreviewimport"><?php echo $text_productreview; ?></a>
		  </div>
		  <div class="tab-pane active" id="productimport">
			  <form action="<?php echo $importproduct; ?>" method="post" enctype="multipart/form-data" id="form">
				 <table class="form">
					<tr>
					  <td><?php echo $entry_import; ?></td>
					  <td><input style="height:37px; margin-top:5px;" type="file" name="import" value=""/></td>
					</tr>
					<tr>
					  <td><?php echo $text_importtype; ?> 
						<span class="help">
							<?php echo $help_importtype; ?>
						</span>
					  </td>
					  <td>
						<select class="form-control" name="importtype">
						  <option value="2"><?php echo $text_model; ?></option>
						  <option value="1"><?php echo $text_productid; ?></option>
						</select>
						<br/>
					  </td>
					</tr>
					<tr>
					  <td><?php echo $entry_store; ?></td>
					  <td>
						<select class="form-control" name="store_id">
						 <option value="0"><?php echo $text_default; ?></option>
						 <?php foreach($stores as $store){ ?>	
							<option value="<?php echo $store['store_id']; ?>"><?php echo $store['name']; ?></option>
						  <?php } ?>
						</select>
					  </td>
					</tr>
					<tr>
						<td><?php echo $entry_language; ?></td>
						<td>
						  <select class="form-control" name="language_id">
							<?php foreach($languages as $language){ ?>
							<option value="<?php echo $language['language_id']; ?>"><?php echo $language['name']; ?></option>
							<?php } ?>
						  </select>
						</td>
					</tr>
				 </table>
				 <br /><br />
					<table width="100%">
						<tr>
							<td width="40%">
								<button onclick="$('#form').submit();" class="excel_main_button" type="button"><?php echo $button_import; ?></button>
							</td>
							<td width="40%"></td>
						</tr>
					</table>
			  </form>
		   </div>
		   <div class="tab-pane" id="productreviewimport">
				<form action="<?php echo $productreviewimportaction; ?>" method="post" enctype="multipart/form-data" id="form_productreview" class="form-horizontal">
					<table class="form">
						<tr>
							<td>
								<input type="file" name="import" value="" />
							</td>
							<td><?php echo $entry_productreview; ?></td>
						</tr>
					</table>
					<br /><br />
					<table width="100%">
						<tr>
							<td width="40%">
								<button onclick="$('#form_productreview').submit();" class="excel_main_button" type="button"><?php echo $button_import; ?></button>
							</td>
							<td width="40%"></td>
						</tr>
					</table>
				</form>
			</div>
		</div>
    </div>
</div>
<script type="text/javascript"><!--
$('.htabs a').tabs();
$('.vtabs a').tabs();
//--></script>
<style>
.form-group {
	margin-bottom: 15px
}
.examplea{
	padding:10px;
	color:#fff;
	background-color:#c1bc44;
	text-decoration: none;
}
.select_excel{
	width:100%;
	height:25px;
	margin:10px 0px
}
.excel_main_button{
		background-color:#921e6f;
		width:100%;
		border:none;
		color:#fff;
		padding:5px;
		cursor:pointer;
}
</style>
<?php echo $footer; ?>