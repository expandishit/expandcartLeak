<?php echo $header; ?>

<div class="row">
    <div class="col-lg-12">
        <div class="row">
            <div class="col-lg-12">
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

                <h1><?php echo $heading_title; ?></h1>
            </div>
        </div>
        <div class="box">
            <div class="content">
                <button type="button" class="btn btn-primary btn-lg" onclick="location.href='<?= $url_registerDomainLink ?>'" style="margin-bottom: 27px; margin-left: 10px; margin-right: 10px; margin-top: 10px;">
                    <span class="fa fa-anchor"></span>
                    <?= $text_buynewdomain ?>
                </button>

                <table class="table table-hover dataTable no-footer">
                    <thead>
                        <td class="center"><?= $text_domainname ?></td>
                        <td class="center"><?= $text_action ?></td>
                    </thead>
                    <tbody id="domains-table">
                        <?php foreach($domains as $domain) { ?>
                        <tr id="domain-<?= $domain['UNIQUEID'] ?>">
                            <td><a target="_blank" href="http://<?= strtolower($domain['DOMAINNAME']) ?>"><?= strtolower($domain['DOMAINNAME']) ?></a></td>
                            <td>
                                <!--<a href="<?php echo $insert; ?>" class="button btn btn-primary">Update</a>-->
                                <a onclick="deleteDomain(<?= $domain['UNIQUEID'] ?>)" class="button btn btn-danger"><span class="fa fa-times"></span>&nbsp;<?= $text_delete ?></a>
                            </td>
                        </tr>
                        <?php } ?>
                        <tr id="input-row">
                            <td><input type="text" id="domainname" value="" /></td>
                            <td>
                                <a onclick="addDomain($('#domainname').val())" class="button btn btn-primary"><span class="fa fa-plus"></span>&nbsp;<?= $text_adddomain ?></a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript"><!--
    function deleteDomain(uniqueid) {
        $.ajax({
            url: 'index.php?route=setting/domainsetting/delete&token=<?php echo $token; ?>',
            type: 'post',
            data: {UniqueId: uniqueid},
            dataType: 'json',
            success: function(json) {
                if(json['deleted']) {
                    $('#domain-' + uniqueid).remove();
                    setTimeout(function() { alert(json['success']); }, 10);
                }
            }
        });
    }

    function addDomain(domainname) {
        if(domainname.trim() == "") return;
        $.ajax({
            url: 'index.php?route=setting/domainsetting/insert&token=<?php echo $token; ?>',
            type: 'post',
            data: {DomainName: domainname},
            dataType: 'json',
            success: function(json) {
                if(json['uniqueid'] > 0) {
                    $('<tr id="domain-' + json['uniqueid'] + '"><td><a target="_blank" href="http://' + json['domainname'] + '">' + json['domainname'] + '</a></td><td><a onclick="deleteDomain(' + json['uniqueid'] + ')" class="button btn btn-danger"><span class="fa fa-times"></span>&nbsp;<?= $text_delete ?></a></td></tr>')
                        .insertBefore($('#input-row'));
                    $('#domainname').val('');
                    setTimeout(function() { alert(json['success']); }, 10);
                } else {
                    alert(json['error']);
                }
            }
        });
    }

//--></script>
<?php echo $footer; ?>