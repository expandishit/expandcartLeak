<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-product" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
     <h1><?php echo $heading_title; ?></h1>
    <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
    </ul>
   </div>
  </div>
  <div class="container-fluid">
    <?php if (isset($warning) && $warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <?php if (isset($success) && $success) {?>
    <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo $success; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $heading_title; ?></h3>
      </div>
      <div class="panel-body">
        <div class="alert alert-info">
          <button type="button" class="close" data-dismiss="alert">&times;</button>
          <i class="fa fa-exclamation-circle"></i>
          <?php echo $entry_info; ?>
        </div>
         <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-amazon-price-rules" class="form-horizontal">

         <div class="form-group" required>
           <label class="col-sm-3 control-label"><span data-toggle="tooltip" title="<?php echo $help_csv; ?>"><?php echo $entry_csv; ?></span></label>
           <div class="col-sm-9">
             <div class="input-group">
               <input type="file" name="import_product" class="form-control file">
             </div>
             <?php if (isset($error_import_product) && $error_import_product) {?>
                <div class="text-danger"><?php echo $error_import_product; ?></div>
             <?php } ?>
           </div>
         </div>
       </form>
       <?php if(isset($report)){
           foreach($report as $product_id=>$error){
                if(isset($error['error']['product'])) {
                  echo '<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> Product Id: '.$product_id.' '.$error['error']['product'].'
                    <button type="button" class="close" data-dismiss="alert">&times;</button></div>';
                }
                if(isset($error['error']['identification'])) {
                  echo '<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> Product Id: '.$product_id.' '.$error['error']['identification'].'
                    <button type="button" class="close" data-dismiss="alert">&times;</button></div>';
                }
                if(isset($error['success'])) {
                  echo '<div class="alert alert-success"><i class="fa fa-exclamation-circle"></i> Product Id: '.$product_id.' '.$error['success'].'
                    <button type="button" class="close" data-dismiss="alert">&times;</button></div>';
                }

           }
       } ?>
      </div>
    </div>
  </div>

</div>
<style>
@supports (-moz-appearance:none) {
    .file {
      padding-bottom: 41px;
    }
}
</style>
<?php echo $footer; ?>
