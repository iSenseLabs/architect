<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th class="text-center" style="width:80px">ID</th>
            <th class="text-center" style="min-width:200px">Name</th>
            <th class="text-center" style="width:80px">Status</th>
            <th class="text-center" style="width:110px">Action</th>
        </tr>
    </thead>
    <tbody id="architect-list"></tbody>
</table>

<div class="row pagination-wrapper" style="display:hidden;">
    <div class="col-sm-7 pagination-number"></div>
    <div class="col-sm-5 text-right pagination-info"></div>
</div>

<script>
var urlList   = 'index.php?route=<?php echo $architect["path_module"]; ?>/itemList&<?php echo $architect["url_token"]; ?>',
    urlUpdate = 'index.php?route=<?php echo $architect["path_module"]; ?>/itemUpdate&<?php echo $architect["url_token"]; ?>';

$(document).ready(function()
{
    fetchList(urlList);

    $('.pagination-number').on('click', 'a', function(e) {
        e.preventDefault();
        fetchList($(this).attr('href'));
    });

    // Update item
    $('#architect-list').on('click', '[data-arc-update]', function(e) {
        e.preventDefault();

        var el = $(this),
            elData = el.data('arc-update');

        $.ajax({
            url: urlUpdate + '&_='+ new Date().getTime(),
            type: 'POST',
            dataType: 'json',
            data: elData,
            cache: false,
            beforeSend: function() {
                $('.beforeUpdate').trigger('click'); // close .arc-alert.beforeUpdate

                if (elData.action == 'delete') {
                    if (!confirm(architect.i18n.confirm_delete)) {
                        return false;
                    }
                    el.after('<i class="fa fa-spinner fa-spin spinner-' + elData.id + '"></i>');
                }
            },
            success: function(data) {
                if (!data.error) {
                    fetchList(urlList);
                } else {
                    notify('danger beforeUpdate', data.error);
                }
            }
        });
    });
});

function fetchList(url) {
    $.ajax({
        url: url + '&_='+ new Date().getTime(),
        type: 'POST',
        dataType: 'json',
        cache: false,
        beforeSend: function() {
            $('#architect-list').html('<tr><td class="text-center" colspan="4" style="padding:20px 10px;"><i class="fa fa-spinner fa-spin"></i> ' + architect.i18n.text_processing + '</td></tr>');
            $('.pagination-wrapper').hide(10);
        },
        success: function(data) {
            if (data.output) {
                $('#architect-list').html(data.output);

                if (data.pagination_info) {
                    $('.pagination-number').html(data.pagination);
                    $('.pagination-info').html(data.pagination_info);
                    $('.pagination-wrapper').show();
                }
            }
        }
    });
}
</script>
