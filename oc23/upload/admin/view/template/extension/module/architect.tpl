<?php echo $header; ?>
<?php echo $column_left; ?>

<div id="content" class="arc-module arc-base">
    <?php $architect['i18n'] = $i18n; ?>
    <script>var architect = <?php echo json_encode($architect); ?>; delete architect.model;</script>

    <div class="container-fluid">
    <div class="content-head">
        <h1><?php echo $architect['title']; ?></h1>

        <ul class="breadcrumb">
            <?php foreach ($breadcrumbs as $breadcrumb) { ?>
                <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
            <?php } ?>
        </ul>

        <div class="pull-right">
            <a href="<?php echo $architect['url_module']; ?>&module_id=0" class="btn btn-primary arc-insert"><?php echo $i18n['text_insert']; ?></a>
        </div>
    </div> <!-- /.content-head -->

    <div class="content-body">
        <div class="panel panel-default panel-body isl-main-panel">

            <div class="tab-navigation" style="position:relative">
                <ul class="nav nav-tabs arc-nav-base" id="main-tabs">
                    <li class="active"><a href="#tab-manage" data-toggle="tab"><?php echo $i18n['text_manage']; ?></a></li>
                    <li><a href="#tab-gist" data-toggle="tab"><?php echo $i18n['text_gist']; ?></a></li>
                    <li><a href="#tab-help" data-toggle="tab"><?php echo $i18n['text_help']; ?></a></li>
                </ul>
            </div>

            <div class="tab-content">
                <div id="tab-manage" class="tab-pane fade active in"><?php echo $tab_manage; ?></div>
                <div id="tab-gist" class="tab-pane fade"><?php echo $tab_gist; ?></div>
                <div id="tab-help" class="tab-pane fade"><?php echo $tab_help; ?></div>
            </div>

        </div>
    </div> <!-- /.content-body -->

    </div>

    <!-- ============ -->

    <div class="arc-notification">
        <?php foreach ($notifications as $notify) { ?>
            <div class="alert alert-<?php echo $notify['type']; ?>">
                <?php echo $notify['message']; ?>
            </div>
        <?php } ?>
    </div>

</div> <!-- /.arc-module -->

<?php echo $footer; ?>
