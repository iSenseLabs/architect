<div class="gist-reff">
    <p><?php echo $i18n['text_gist_information']; ?></p>

    <div class="gist-reff-icon">
        <a href="#" class="js-gist-refresh" data-toggle="tooltip" title="Reload list"><i class="fa fa-refresh"></i></a>
    </div>
</div>

<div id="gist-list" class="row arc-flex">
    <div class="gistlist-inner"><i class="fa fa-spinner fa-spin"></i> <?php echo $i18n['text_processing']; ?></div>
</div>

<div id="lightbox-modal" class="modal fade lightbox">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <div class="modal-dialog modal-lg">
        <img src="" alt="Gist image">
    </div>
</div>

<script>
var urlGistList      = 'index.php?route=<?php echo $architect["path_module"]; ?>/gistList&<?php echo $architect["url_token"]; ?>',
    tableGistProcess = '<div class="gistlist-inner">{string}</div>';

$(document).ready(function()
{
    fetchGistList(urlGistList);
    $('.js-gist-refresh').on('click', function(e) {
        e.preventDefault();
        fetchGistList(urlGistList);
    });

    $('#gist-list').on('click', '[data-lightbox]', function(e) {
        let imgSrc = $(this).find('img').attr('src'),
            imgAlt = $(this).find('img').attr('alt');

        $('#lightbox-modal img').attr('src', '').attr('alt', '');
        if (imgSrc) {
            $('#lightbox-modal img').attr('src', imgSrc).attr('alt', imgAlt);
            $('#lightbox-modal').modal('show');
        }
    });
});

function fetchGistList(url) {
    $.ajax({
        url: url + '&_='+ new Date().getTime(),
        type: 'POST',
        dataType: 'json',
        cache: false,
        beforeSend: function() {
            $('.js-gist-refresh .fa-refresh').addClass('fa-spin');
            $('#gist-list').html(tableGistProcess.replace('{string}', '<i class="fa fa-spinner fa-spin"></i> ' + architect.i18n.text_processing));
        },
        success: function(data) {
            if (data.items) {
                $('#gist-list').html(data.output);
                $('.fa-refresh').removeClass('fa-spin');
            } else {
                $('#gist-list').html(tableGistProcess.replace('{string}', architect.i18n.text_no_data));
            }
        }
    });
}
</script>
