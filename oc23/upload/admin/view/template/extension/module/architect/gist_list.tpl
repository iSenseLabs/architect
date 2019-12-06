<?php foreach ($gists as $gist) { ?>
    <tr>
        <td>
            <div class="gist-name">
                <?php echo $gist['name']; ?> v<?php echo $gist['version']; ?>
            </div>
            <?php if ($gist['description']): ?>
                <div class="gist-description small text-muted hidden-xs"><?php echo $gist['description']; ?></div>
            <?php endif ?>
        </td>
        <td><?php echo $gist['author']; ?></td>
        <td class="text-center"><a href="<?php echo $architect['url_module']; ?>&module_id=0&gist=<?php echo $gist['file']; ?>" class="btn btn-primary btn-sm">Insert</a></td>
    </tr>
<?php } ?>
