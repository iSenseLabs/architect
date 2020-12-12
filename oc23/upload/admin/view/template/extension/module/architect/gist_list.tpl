<?php foreach ($gists as $gist) { ?>
    <tr>
        <td>
            <div class="arc-flex">
                <?php if ($gist['image_url']) { ?>
                <div class="gist-image">
                    <a href="<?php echo $gist['image_url']; ?>" target="_blank">
                        <img class="img-responsive" src="<?php echo $gist['image_url']; ?>" alt="">
                    </a>
                </div>
                <?php } ?>
                <div class="gist-info">
                    <div class="gist-name">
                        <?php echo $gist['name']; ?> v<?php echo $gist['version']; ?>
                    </div>
                    <?php if ($gist['description']) { ?>
                        <div class="small text-muted hidden-xs gist-description"><?php echo $gist['description']; ?></div>
                    <?php } ?>
                </div>
            </div>
        </td>
        <td><?php echo $gist['author']; ?></td>
        <td class="text-center"><a href="<?php echo $architect['url_module']; ?>&module_id=0&gist=<?php echo $gist['codename']; ?>" class="btn btn-primary btn-sm"><?php echo $i18n['text_insert']; ?></a></td>
    </tr>
<?php } ?>
