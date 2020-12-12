<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th><?php echo $i18n['text_information']; ?></th>
            <th style="width:150px"><?php echo $i18n['text_author']; ?></th>
            <th class="text-center" style="width:120px">
                <?php echo $i18n['text_action']; ?>
                <a href="#" class="ml-5 js-gist-refresh" data-toggle="tooltip" title="Reload list"><i class="fa fa-refresh"></i></a>
            </th>
        </tr>
    </thead>
    <tbody id="gist-list">
        <tr>
            <td colspan="4" class="table-process"><i class="fa fa-spinner fa-spin"></i> <?php echo $i18n['text_processing']; ?></td>
        </tr>
    </tbody>
</table>

<script>
var urlGistList      = 'index.php?route=<?php echo $architect["path_module"]; ?>/gistList&<?php echo $architect["url_token"]; ?>',
    tableGistProcess = '<tr><td colspan="4" class="table-process">{string}</td></tr>';

$(document).ready(function()
{
    fetchGistList(urlGistList);
    $('.js-gist-refresh').on('click', function(e) {
        e.preventDefault();
        fetchGistList(urlGistList);
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
