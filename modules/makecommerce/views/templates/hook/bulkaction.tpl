<script>
    $(function() {
        $('.adminorders .bulk-actions .dropdown-menu').append('<li><a href="#" onclick="bulkActionParcelLabel();"><i class="icon-print"></i> {l s='Print parcel lables' mod='makecommerce'}</a></li>');
    });

    function bulkActionParcelLabel(){
        var selected_orders = [];
        $('.table.order .row-selector input:checked').each(function() {
            selected_orders.push($(this).val());
        });
        $('#label-loader').show();
        $.ajax({
            type:"POST",
            url: "{$parcel_lable}",
            async: true,
            dataType: "json",
            data : {
                order_ids: selected_orders
            },
            success : function(res)
            {
                window.open(res, '_blank');
                $('#label-loader').hide();
            }
        });
    }
</script>
<div id="label-loader" style="display:none; z-index: 999999; position: absolute; width: 100%; height: 100%">
    <img src="{$loader_img}" style="position: absolute; top: 50%; left: 50%" />
    <div style="background:#000; opacity: 0.4; width: 100%; height: 100%"></div>
</div>