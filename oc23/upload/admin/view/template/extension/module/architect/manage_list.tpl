<?php foreach ($items as $item) { ?>
    <tr class="text-center">
        <td><?php echo $item['module_id']; ?></td>
        <td class="text-left">
            <a href="<?php echo $item['url_edit']; ?>"><?php echo $item['name']; ?></a>
            <?php if (!empty($item['meta']['note'])) { ?>
                <div class="small text-muted"><?php echo $item['meta']['note']; ?></div>
            <?php } ?>
        </td>
        <td>
            <?php if ($item['status']) { ?>
                <a class="btn btn-sm btn-success btn-xs" data-arc-update='{"action":"status", "architect_id":<?php echo $item['architect_id']; ?>, "value":0}' data-toggle="tooltip" title="<?php echo $i18n['text_click_to_disable']; ?>"><i class="fa fa-check"></i></a>
            <?php } else { ?>
                <a class="btn btn-sm btn-danger btn-xs" data-arc-update='{"action":"status", "architect_id":<?php echo $item['architect_id']; ?>, "value":1}' data-toggle="tooltip" title="<?php echo $i18n['text_click_to_enable']; ?>"><i class="fa fa-close"></i></a>
            <?php } ?>
        </td>
        <td style="padding:8px 2px;">
            <a href="<?php echo $item['url_edit']; ?>" class="btn btn-primary btn-sm" data-toggle="tooltip" title="<?php echo $i18n['text_edit']; ?>"><i class="fa fa-pencil"></i></a>
            <a class="btn btn-danger btn-sm" data-arc-update='{"action":"delete", "architect_id":<?php echo $item['architect_id']; ?>, "module_id":<?php echo $item['module_id']; ?>}' data-toggle="tooltip" title="<?php echo $i18n['text_delete']; ?>"><i class="fa fa-trash-o"></i></a>
        </td>
    </tr>
<?php } ?>
