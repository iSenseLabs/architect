<?php echo $header; ?>
<?php echo $column_left; ?>

<div id="content" class="arc-module js-editor">
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
                        <textarea name="meta[note]" cols="50" rows="6" class="form-control arc-note" placeholder="<?php echo $i18n['placeholder_note']; ?>"><?php echo $architect['setting']['meta']['note']; ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="control-label"><?php echo $i18n['entry_editor']; ?></label>
                        <div class="ml-10">
                            <div class="checkbox">
                                <label><input type="checkbox" name="meta[editor][controller]" value="1" <?php echo $architect['setting']['meta']['editor']['controller'] ? 'checked' : ''; ?> data-arc-tab-visible='controller'> <?php echo $i18n['text_controller']; ?></label>
                            </div>
                            <div class="checkbox">
                                <label><input type="checkbox" name="meta[editor][model]" value="1" <?php echo $architect['setting']['meta']['editor']['model'] ? 'checked' : ''; ?> data-arc-tab-visible='model'> <?php echo $i18n['text_model']; ?></label>
                            </div>
                            <div class="checkbox">
                                <label><input type="checkbox" name="meta[editor][template]" value="1" <?php echo $architect['setting']['meta']['editor']['template'] ? 'checked' : ''; ?> data-arc-tab-visible='template'> <?php echo $i18n['text_template']; ?></label>
                            </div>
                            <div class="checkbox">
                                <label><input type="checkbox" name="meta[editor][modification]" value="1" <?php echo $architect['setting']['meta']['editor']['modification'] ? 'checked' : ''; ?> data-arc-tab-visible='modification'> <?php echo $i18n['text_modification']; ?></label>
                            </div>
                            <div class="checkbox">
                                <label><input type="checkbox" name="meta[editor][event]" value="1" <?php echo $architect['setting']['meta']['editor']['event'] ? 'checked' : ''; ?> data-arc-tab-visible='event'> <?php echo $i18n['text_event']; ?></label>
                            </div>
                        </div>
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
                            <ul class="nav nav-tabs arc-nav-editor js-nav-editor">
                                <li style="<?php echo !$architect['setting']['meta']['editor']['controller'] ? 'display:none' : ''; ?>">
                                    <a href="#tab-controller" data-toggle="tab" class="js-editor-controller"><?php echo $i18n['text_controller']; ?></a>
                                </li>
                                <li style="<?php echo !$architect['setting']['meta']['editor']['model'] ? 'display:none' : ''; ?>">
                                    <a href="#tab-model" data-toggle="tab" class="js-editor-model"><?php echo $i18n['text_model']; ?></a>
                                </li>
                                <li style="<?php echo !$architect['setting']['meta']['editor']['template'] ? 'display:none' : ''; ?>">
                                    <a href="#tab-template" data-toggle="tab" class="js-editor-template"><?php echo $i18n['text_template']; ?></a>
                                </li>
                                <li style="<?php echo !$architect['setting']['meta']['editor']['modification'] ? 'display:none' : ''; ?>">
                                    <a href="#tab-modification" data-toggle="tab" class="js-editor-modification"><?php echo $i18n['text_modification']; ?></a>
                                </li>
                                <li style="<?php echo !$architect['setting']['meta']['editor']['event'] ? 'display:none' : ''; ?>">
                                    <a href="#tab-event" data-toggle="tab" class="js-editor-event"><?php echo $i18n['text_event']; ?></a>
                                </li>
                                <li>
                                    <a href="#tab-option" data-toggle="tab" class="js-editor-option"><?php echo $i18n['text_options']; ?></a>
                                </li>
                            </ul>

                            <div class="pull-right">
                                <a class="arc-help-editor" data-toggle="modal" data-target="#arc-help-editor"><i class="fa fa-question-circle"></i></a>
                            </div>
                        </div>

                        <div class="tab-content">
                            <div id="tab-controller" class="tab-pane fade">
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

    <div id="arc-help-editor" class="modal fade arc-help-editor-modal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content modal-body">
                <h2 class=" text-center">Quick Documentation</h2>
                <hr class="hr">

                <h3 class="legend">Overview</h3>
                <p>Each editor will be saved to respected file and database. This approach allow the module to work without any magic involvement and work per OpenCart system workflow.</p>

                <table class="table table-striped mt-10">
                    <thead>
                        <tr>
                            <td class="col-xs-2 col-sm-3">Editor</td>
                            <td>Save Destination</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Controller</td>
                            <td>catalog/controller/extension/architect/<code>arc101</code>.php</td>
                        </tr>
                        <tr>
                            <td>Model</td>
                            <td>catalog/model/extension/architect/<code>arc101</code>.php</td>
                        </tr>
                        <tr>
                            <td>Template</td>
                            <td>catalog/model/extension/architect/<code>arc101</code>.php</td>
                        </tr>
                        <tr>
                            <td>Modification</td>
                            <td>Saved to database</td>
                        </tr>
                        <tr>
                            <td>Event</td>
                            <td>
                                Saved to database<br>
                                catalog/controller/extension/architect/event/<code>arc101</code>.php
                            </td>
                        </tr>
                    </tbody>
                </table>


                <h3 class="legend">Codetags</h3>
                <p>Custom code available to all editors, which is replaced when save sub-module.</p>

                <table class="table table-striped mt-10">
                    <thead>
                        <tr>
                            <td class="col-xs-2 col-sm-3">Tags</td>
                            <td>Replacement Sample</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{module_id}</td>
                            <td><b>21</b></td>
                        </tr>
                        <tr>
                            <td>{identifier}</td>
                            <td><b>arc101</b> <i class="small">* unique per sub-module</i></td>
                        </tr>
                        <tr>
                            <td>{author}</td>
                            <td>johndoe</td>
                        </tr>
                        <tr>
                            <td>{controller_class}</td>
                            <td>ControllerExtensionArchitect<code>arc101</code></td>
                        </tr>
                        <tr>
                            <td>{model_class}</td>
                            <td>ModelExtensionArchitect<code>arc101</code></td>
                        </tr>
                        <tr>
                            <td>{model_path}</td>
                            <td>extension/architect/<code>arc101</code></td>
                        </tr>
                        <tr>
                            <td>{model_call}</td>
                            <td>extension_architect_<code>arc101</code></td>
                        </tr>
                        <tr>
                            <td>{template_path}</td>
                            <td>extension/architect/<code>arc101</code></td>
                        </tr>
                        <tr>
                            <td>{ocmod_name}</td>
                            <td>Architect #<code>21</code> - Sub-module name</td>
                        </tr>
                        <tr>
                            <td>{ocmod_code}</td>
                            <td><code>arc101</code></td>
                        </tr>
                        <tr>
                            <td>{event_class}</td>
                            <td>'ControllerExtensionArchitectEvent<code>arc101</code></td>
                        </tr>
                        <tr>
                            <td>{event_path}</td>
                            <td>extension/architect/event/<code>arc101</code></td>
                        </tr>
                    </tbody>
                </table>
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
