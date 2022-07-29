<?php echo $header; ?>
<link href="https://fonts.googleapis.com/css?family=Open+Sans:400,300,600" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/5.0.0/normalize.min.css">

<div id="content" class="ms-account-profile">
    <?php echo $content_top; ?>

    <div class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
        <?php } ?>
    </div>

    <h1 class="maintitle">{{ lang('ms_account_subscriptions_heading') }}</h1>

    <?php if (isset($success) && ($success)) { ?>
    <div class="success"><?php echo $success; ?></div>
    <?php } ?>

    <?php if (isset($statustext) && ($statustext)) { ?>
    <div class="<?php echo $statusclass; ?>"><?php echo $statustext; ?></div>
    <?php } ?>

    <p class="warning main"></p>

    <div class="content" style="text-align: center;">

        <h3>{{ lang('expired_plan') }}</h3>
        <a href="<?php echo $updatePlan ?>"><h4>{{ lang('renew_your_plan') }}</h4></a>
    </div>

</div>

<?php echo $footer; ?>
