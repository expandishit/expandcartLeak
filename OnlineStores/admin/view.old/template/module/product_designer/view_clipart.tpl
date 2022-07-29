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
    <?php } 
  <div class="box">
    <div class="heading">
      <h1>{{ lang('pd_category_and_cliparts') }}</h1>
      <div class="buttons">
          <a href="<?php echo $insert; ?>" class="button btn btn-primary">{{ lang('button_insert') }}</a>
          <a href="<?php echo $cancel; ?>" class="button btn btn-primary">{{ lang('button_cancel') }}</a>
          <?php /* ?>
          <a onclick="$('#form').attr('action', '<?php echo $delete; ?>'); $('#form').attr('target', '_self'); $('#form').submit();" class="button btn btn-danger">{{ lang('button_delete') }}</a>
          <?php */ ?>
      </div>
    </div>
    <div class="content">
        <table class="table table-hover dataTable no-footer">
           <thead>
             <tr>
               <td width="70%">{{ lang('pd_image') }}</a>
               <td>{{ lang('pd_options') }}</td>
             </tr>
           </thead>
           <tbody>
             <?php
             if ($imagelist)
             foreach ($imagelist as $clist) {
             ?>
             <tr>
               <td><img src="<?= $clist['image_name'] ?>" /></td>
               <td>
                   <button onclick="deleteimage('<?= $clist['href2'] ?>');" class="btn btn-danger" data-toggle="tooltip" type="button" data-original-title="{{ lang('pd_delete') }}"><i class="fa fa-trash-o"></i></button>
               </td>
             </tr>
             <?php } else { ?>
                 <tr>
                   <td class="center" align="center" colspan="8">{{ lang('text_no_results') }}</td>
                 </tr>
             <?php } ?>
           </tbody>
         </table>
    </div>
  </div>
</div>
<div class="md-overlay"></div><!-- the overlay element -->
<script type="text/javascript">
    function deleteimage(param)
    {
        var iAnswer = confirm("{{ lang('pd_delete_confirmation_message') }}");
        if(iAnswer){
            //true here
            window.location.href = param;
        }
        else{
            return false;
        }
    }
</script>
<?php echo $footer; ?>
