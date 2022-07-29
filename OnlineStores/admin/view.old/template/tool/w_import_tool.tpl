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
      <h1>Excel Import</h1>
    </div>
    <div class="content">
	 <div id="htabs" class="htabs">
		<a href="#customerimport">Customers</a>
		<a href="#orderimport">Orders</a>
	 </div>
	<style>
	.form-group {
		margin-bottom: 15px
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
	 <div class="content">
			<div class="row">
				<div class="col-sm-10">
				  <div class="tab-content">
					 <div id="customerimport">
						<?php require_once('import/customerimport.tpl'); ?>
					 </div>
					 <div id="orderimport">
						<?php require_once('import/orderimport.tpl'); ?>
					 </div>
				  </div>
				</div>
			</div>
	    </div>
	  </div>
    </div>
 </div>
<script type="text/javascript"><!--
$('.htabs a').tabs();
$('.vtabs a').tabs();
//--></script>
<?php echo $footer; ?>