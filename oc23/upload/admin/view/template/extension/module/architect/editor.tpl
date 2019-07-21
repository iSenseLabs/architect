<?php echo $header; ?>
<?php echo $column_left; ?>

<div id="content" class="arc-module arc-editor">
    <?php $architect['i18n'] = $i18n; ?>
    <script>var architect = <?php echo json_encode($architect); ?>; delete architect.model;</script>

    <div class="content-head head-shadow">
        <h1><?php echo $architect['title']; ?></h1>

        <ul class="breadcrumb">
            <?php foreach ($breadcrumbs as $breadcrumb) { ?>
                <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
            <?php } ?>
        </ul>

        <div class="pull-right">
            <button type="button" form="form-architect" class="btn btn-primary arc-save"><?php echo $i18n['text_save']; ?></button>
            <a href="<?php echo $architect['url_module_manage']; ?>" class="btn btn-default"><?php echo $i18n['text_close']; ?></a>
        </div>
    </div> <!-- /.content-head -->

    <div class="content-body">
        <form id="form-architect" action="<?php echo $architect['url_module_save']; ?>" method="post" enctype="multipart/form-data">

            <div class="arc-panel arc-flex">
                <input type="hidden" name="module_id" value="<?php echo $architect['setting']['module_id'] ?>" class="form-control module_id">
                <input type="hidden" name="identifier" value="<?php echo $architect['setting']['identifier'] ?>" class="form-control identifier">

                <div class="panel-sidebar">
                    <div class="form-group required">
                        <label class="control-label"><?php echo $i18n['entry_name']; ?></label>
                        <input type="text" name="name" value="<?php echo $architect['setting']['name']; ?>" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="control-label"><?php echo $i18n['entry_note']; ?></label>
                        <textarea name="note" cols="50" rows="6" class="form-control arc-note" placeholder="<?php echo $i18n['placeholder_note']; ?>"><?php echo $architect['setting']['note']; ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="control-label"><?php echo $i18n['entry_status']; ?></label>
                        <div>
                            <div class="btn-group btn-radio">
                                <input type="radio" name="status" id="status-1" value="1" hidden <?php echo $architect['setting']['status'] ? 'checked' : ''; ?>>
                                <label for="status-1" class="btn btn-default btn-active-success"><?php echo $i18n['text_enabled']; ?></label>
                                <input type="radio" name="status" id="status-0" value="0" hidden <?php echo $architect['setting']['status'] ? '' : 'checked'; ?>>
                                <label for="status-0" class="btn btn-default btn-active-danger"><?php echo $i18n['text_disabled']; ?></label>
                            </div>
                        </div>
                    </div>
                </div> <!-- /.panel-sidebar -->

                <div class="panel-auto">
                    <div class="panel-content">

                        <div class="tab-navigation">
                            <ul class="nav nav-tabs arc-nav-editor">
                                <li class="active"><a href="#tab-controller" data-toggle="tab"><?php echo $i18n['text_controller']; ?></a></li>
                                <li><a href="#tab-model" data-toggle="tab"><?php echo $i18n['text_model']; ?></a></li>
                                <li><a href="#tab-template" data-toggle="tab"><?php echo $i18n['text_template']; ?></a></li>
                                <li><a href="#tab-modification" data-toggle="tab"><?php echo $i18n['text_modification']; ?></a></li>
                                <li><a href="#tab-event" data-toggle="tab"><?php echo $i18n['text_event']; ?></a></li>
                            </ul>

                            <div class="pull-right">
                                <a class="arc-help-editor" data-toggle="modal" data-target="#arc-help-editor"><i class="fa fa-question-circle"></i></a>
                            </div>
                        </div>

                        <div class="tab-content">
                            <div id="tab-controller" class="tab-pane fade active in">
                                <textarea name="controller" id="cm-controller" cols="50" rows="10" class="form-control" data-arc-codemirror='{"mode":"application/x-httpd-php"}'><?php echo $architect['setting']['controller']; ?></textarea>
                            </div>
                            <div id="tab-model" class="tab-pane fade">
                                <textarea name="model" id="cm-model" cols="50" rows="10" class="form-control" data-arc-codemirror='{"mode":"application/x-httpd-php"}'><?php echo $architect['setting']['model']; ?></textarea>
                            </div>
                            <div id="tab-template" class="tab-pane fade">
                                <textarea name="template" id="cm-template" cols="50" rows="10" class="form-control" data-arc-codemirror='{"mode":"application/x-httpd-php"}'><?php echo $architect['setting']['template']; ?></textarea>
                            </div>
                            <div id="tab-modification" class="tab-pane fade">
                                <textarea name="modification" id="cm-modification" cols="50" rows="10" class="form-control" data-arc-codemirror='{"mode":"application/xml"}'><?php echo $architect['setting']['modification']; ?></textarea>
                            </div>
                            <div id="tab-event" class="tab-pane fade">
                                <textarea name="event" id="cm-event" cols="50" rows="10" class="form-control" data-arc-codemirror='{"mode":"application/x-httpd-php"}'><?php echo $architect['setting']['event']; ?></textarea>
                            </div>
                        </div>

                    </div>
                </div> <!-- /.panel-auto -->
            </div>

        </form>
    </div> <!-- /.content-body -->

    <!-- ============ -->

    <div id="arc-help-editor" class="modal fade arc-help-editor-modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content modal-body text-center">
                <h1><?php echo $architect['title']; ?></h1>
                <p>Architect is an OpenCart module for rapid extension development. It can be considered as low-level extension which provide access to OpenCart API to make prototype, build minimum viable product or specifically custom function.</p>
                <hr>
                <p>
                    v<?php echo $architect['version']; ?> •
                    <a href="https://github.com/iSenseLabs/architect" target="_blank">Project</a> •
                    <a href="https://github.com/iSenseLabs/architect/wiki" target="_blank">Docs</a> •
                    <a href="https://github.com/iSenseLabs/architect/issues" target="_blank">Issues</a> •
                    <a href="https://github.com/iSenseLabs/architect/blob/master/LICENSE" target="_blank">GPLv3+</a>
                </p>
            </div>
        </div>
    </div>

    <div class="arc-notification">
        <?php foreach ($notifications as $notify) { ?>
            <div class="alert alert-<?php echo $notify['type']; ?>">
                <?php echo $notify['message']; ?>
            </div>
        <?php } ?>
    </div>

</div> <!-- /.arc-module -->

<?php echo $footer; ?>
