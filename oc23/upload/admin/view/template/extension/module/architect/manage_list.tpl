<?php foreach ($items as $item) { ?>
    <tr class="text-center">
        <td><?php echo $item['module_id']; ?></td>
        <td class="text-left"><?php echo $item['identifier']; ?></td>
        <td class="text-left">
            <a href="<?php echo $item['url_edit']; ?>" class="sub-module-name"><?php echo $item['name']; ?></a>
            <?php if (!empty($item['meta']['note'])) { ?>
                <div class="small text-muted sub-module-note"><?php echo $item['meta']['note']; ?></div>
            <?php } ?>
        </td>
        <td><span class="label label-<?php echo $item['status'] ? 'success' : 'danger'; ?>"><?php echo $item['status'] ? $i18n['text_enabled'] : $i18n['text_disabled']; ?></span></td>
        <td style="padding:8px 2px;">
            <a href="<?php echo $item['url_edit']; ?>" class="btn btn-primary btn-sm" data-toggle="tooltip" title="<?php echo $i18n['text_edit']; ?>"><i class="fa fa-pencil"></i></a>
            <a class="btn btn-danger btn-sm" data-arc-update='{"action":"delete", "identifier":"<?php echo $item['identifier']; ?>", "module_id":<?php echo $item['module_id']; ?>}' data-toggle="tooltip" title="<?php echo $i18n['text_delete']; ?>"><i class="fa fa-trash-o"></i></a>
        </td>
    </tr>
<?php } ?>
