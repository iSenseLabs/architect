<?php foreach ($gists as $gist) { ?>

<div class="col-xs-4 col-md-3">
    <div class="thumbnail">
        <?php if (!$gist['oc_compatible']) { ?>
            <div class="alert alert-danger" title="<?php echo $i18n['text_not_compatible_info']; ?>" data-toggle="tooltip"><?php echo $i18n['text_not_compatible']; ?></div>
        <?php } ?>

        <div class="gist-image">
            <a data-lightbox>
                <img src="<?php echo $gist['image']; ?>" alt="<?php echo $gist['name']; ?> v<?php echo $gist['version']; ?> by <?php echo $gist['author']; ?>">
            </a>
        </div>

        <div class="caption">
            <h3 class="gist-name"><?php echo $gist['name']; ?> v<?php echo $gist['version']; ?></h3>
            <?php if ($gist['description']) { ?>
                <div class="gist-description"><?php echo $gist['description']; ?></div>
            <?php } ?>
        </div>

        <div class="gist-footer">
            <a href="<?php echo $gist['link']; ?>" target="_blank" title="Developed by <?php echo $gist['author']; ?>" data-toggle="tooltip"><?php echo $gist['author']; ?></a>
            <a href="<?php echo $architect['url_module']; ?>&module_id=0&gist=<?php echo $gist['codename']; ?>" class="btn btn-primary btn-sm"><?php echo $i18n['text_insert']; ?></a>
        </div>
    </div>
</div>

<?php } ?>
