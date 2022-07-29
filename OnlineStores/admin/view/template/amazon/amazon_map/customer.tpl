<div id="content">
    <link href="view/stylesheet/csspin.css" rel="stylesheet" type="text/css"/>
    <style type="text/css">
      .cp-round::before, .cp-round::after{
        width: 35px;
        left:8px;
        height: 35px;
        /*top: 25px;*/
        margin-top: 25px;
      }
      .btn-success{
        background-color:#6ABD6A;
        color:#FFF;
        border-style: solid;
        border-width: 1px;
        border-color: #6ABD6A;
        border-bottom-width: 3px;
      }
      .btn-success:hover{
        background-color:#e6e6e6;
        color:#333;
        border-style: solid;
        border-width: 1px;
        border-color: #adadad;
        border-bottom-width: 3px;
      }
      .order_import_section, #profiler_order, #amazonOrderList, #start_import_all_order{
        display: none;
      }
    </style>
      <div class="page-header">
        <div class="container-fluid">
          <h3><?php echo $heading_title; ?></h3>
          <hr style="margin-bottom:0px;">
        </div>
      </div>
      <div class="container-fluid">
        <div class="panel panel-default" id="imported_order_list">
          <div class="panel-heading"  style="display:inline-block;width:100%;">
            <h3 class="panel-title"><i class="fa fa-list"></i> <?php echo $text_customer_list; ?></h3>
          </div>
          <div class="panel-body">
            <form action="<?php echo $action_delete; ?>" method="post" enctype="multipart/form-data" id="form-order-delete">
              <div class="table-responsive">
                <table class="table table-bordered table-hover">
                  <thead>
                    <tr>
                      <td class="text-left"><?php if ($sort == 'apm.map_id') { ?>
                        <a href="<?php echo $sort_map_id; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_map_id; ?></a>
                        <?php } else { ?>
                        <a href="<?php echo $sort_map_id; ?>"><?php echo $column_map_id; ?></a>
                        <?php } ?></td>
                      <td class="text-left"><?php if ($sort == 'acm.oc_customer_id') { ?>
                          <a href="<?php echo $sort_oc_customer_id; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_oc_customer_id; ?></a>
                          <?php } else { ?>
                          <a href="<?php echo $sort_oc_customer_id; ?>"><?php echo $column_oc_customer_id; ?></a>
                          <?php } ?></td>
                      <td class="text-left"><?php if ($sort == 'customer_name') { ?>
                          <a href="<?php echo $sort_customer_name; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_buyer_name; ?></a>
                          <?php } else { ?>
                          <a href="<?php echo $sort_customer_name; ?>"><?php echo $column_buyer_name; ?></a>
                          <?php } ?></td>
                      <td class="text-left"><?php if ($sort == 'c.email') { ?>
                              <a href="<?php echo $sort_customer_email; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_buyer_email; ?></a>
                              <?php } else { ?>
                              <a href="<?php echo $sort_customer_email; ?>"><?php echo $column_buyer_email; ?></a>
                              <?php } ?></td>
                      <td class="text-left"><?php if ($sort == 'aom.city') { ?>
                          <a href="<?php echo $sort_city; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_city; ?></a>
                          <?php } else { ?>
                          <a href="<?php echo $sort_city; ?>"><?php echo $column_city; ?></a>
                          <?php } ?></td>
                      <td class="text-left"><?php if ($sort == 'aom.country') { ?>
                          <a href="<?php echo $sort_country; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_country; ?></a>
                          <?php } else { ?>
                          <a href="<?php echo $sort_country; ?>"><?php echo $column_country; ?></a>
                          <?php } ?></td>
                      <td class="text-left"><?php echo $column_action; ?></td>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (isset($amazon_customers) && $amazon_customers) { ?>
                    <?php foreach ($amazon_customers as $amazon_customer) { ?>
                    <tr>
                      <td class="text-left"><?php echo $amazon_customer['map_id']; ?></td>
                      <td class="text-left"><?php echo $amazon_customer['oc_order_id']; ?></td>
                      <td class="text-left"><?php echo $amazon_customer['customer_name']; ?></td>
                      <td class="text-left"><?php echo $amazon_customer['customer_email']; ?></td>
                      <td class="text-left"><?php echo $amazon_customer['city']; ?></td>
                      <td class="text-left"><?php echo $amazon_customer['country']; ?></td>
                      <td class="text-left">
                        <a href="<?php echo $amazon_customer['view']; ?>" target="_blank" class="btn btn-warning" data-toggle="tooltip" title="<?php echo $button_view_info; ?>" >
                          <i class="fa fa-eye" aria-hidden="true"></i>  <?php echo $button_view; ?>
                        </a>
                      </td>
                    </tr>
                    <?php } ?>
                    <?php } else { ?>
                    <tr>
                      <td class="text-center" colspan="8"><?php echo $text_no_results; ?></td>
                    </tr>
                    <?php } ?>
                  </tbody>
                </table>
              </div>
            </form>
            <div class="row" style="margin-top:10px;">
              <div class="col-sm-6 text-left"><?php echo $pagination; ?></div>
              <div class="col-sm-6 text-right"><?php echo $results; ?></div>
            </div>
          </div>
        </div><!--imported_list-->

      </div><!--container-fluid-->
</div><!--content-->
