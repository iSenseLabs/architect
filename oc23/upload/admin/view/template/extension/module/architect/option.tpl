<div class="tab-panel form-horizontal">
    <div class="form-group">
        <label class="col-sm-3 control-label"><?php echo $i18n['entry_customer_group']; ?></label>
        <div class="col-sm-9 js-toggle">

            <div class="radio">
                <label>
                    <input type="radio" name="option[customer_group]" value="0" <?php echo !$architect['setting']['option']['customer_group'] ? 'checked' : ''; ?>>
                    <?php echo $i18n['text_all_customer']; ?>
                </label>
            </div>

            <div class="radio">
                <label>
                    <input type="radio" name="option[customer_group]" value="1" <?php echo $architect['setting']['option']['customer_group'] ? 'checked' : ''; ?>>
                    <?php echo $i18n['text_selected_customer_group']; ?>
                </label>
                <div class="well well-sm js-toggle-target" <?php echo !$architect['setting']['option']['customer_group'] ? 'style="display:none"' : ''; ?>>
                    <label data-toggle="tooltip" data-placement="left">
                        <input type="checkbox" name="option[customer_group_ids][]" value="0" <?php echo in_array(0, $architect['setting']['option']['customer_group_ids']) ? 'checked' : ''; ?>> <?php echo $i18n['text_guest_visitor'] ?>
                    </label>

                    <?php foreach ($customer_groups as $group) { ?>
                        <label>
                            <input type="checkbox" name="option[customer_group_ids][]" value="<?php echo $group['customer_group_id']; ?>" <?php echo in_array($group['customer_group_id'], $architect['setting']['option']['customer_group_ids']) ? 'checked' : ''; ?>>
                            <?php echo $group['name']; ?>
                            <?php echo $group['customer_group_id'] == $default_cust_group ? '<b>(' . $i18n['text_default'] . ')</b>' : ''; ?>
                        </label>
                    <?php } ?>
                </div>
            </div>

        </div>
    </div>

    <div class="form-group date-duration">
        <label class="col-sm-3 control-label"><?php echo $i18n['entry_date_duration']; ?></label>
        <div class="col-sm-9">
            <div class="col-sm-4" style="padding-left:0">
                <div class="input-group js-date">
                    <input type="text" name="publish" value="<?php echo date('Y-m-d', strtotime($architect['setting']['publish'])); ?>" data-date-format="YYYY-MM-DD" class="form-control" data-toggle="tooltip" title="Publish">
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="button"><i class="fa fa-calendar"></i></button>
                    </span>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="input-group js-date">
                    <input type="text" id="date-end" name="unpublish" value="<?php echo $architect['setting']['unpublish'] ? date('Y-m-d', strtotime($architect['setting']['unpublish'])) : ''; ?>" data-date-format="YYYY-MM-DD" class="form-control" data-toggle="tooltip" title="Unpublish">
                    <a onclick="$('#date-end').val('')" class="pointer text-muted pointer" data-toggle="tooltip" title="Clear date end" style="position:absolute;right:-15px;top:.5em;"><i class="fa fa-close"></i></a>
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="button"><i class="fa fa-calendar"></i></button>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
