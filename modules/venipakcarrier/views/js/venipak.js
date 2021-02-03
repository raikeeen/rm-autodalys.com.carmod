$(document).ready(function() {
    clickFunctions();
    bulkActions();
});

function bulkActions()
{
    if (help_class_name  == 'AdminOrders') {
        var bulk_dropdown = $('.bulk-actions ul.dropdown-menu');
        bulk_dropdown.append('<li><a href="#" onclick="sendVenipakBulkAction($(this).closest(\'form\').get(0), \''+venipakAdminLabels+'\',$(this),true);"><i class="icon-cloud-download"></i>&nbsp;'+venipakAdminLabelsTitle+'</a></li>');
    }
}

function sendVenipakBulkAction(form, action, object, reload)
{
    var order_ids = '';
    $("input[name='orderBox[]']:checked").each(function( index ) {
        order_ids += $( this ).val() +',';
    });
    if (order_ids) {

        order_ids = order_ids.substring(0, order_ids.length - 1);

        object.attr('href',action+'&order_ids='+order_ids);
        object.attr('target','_blank');
        if (reload == 0){
            setTimeout(function(){
                window.location.href = location.href;
            }, 5000);
        }
    } else {
        alert('Select orders');
    }
    return false;
}

function clickFunctions()
{
    $(document).on("click","#page-header-desc-configuration-venipakbtn",function(e) {
        e.preventDefault();

        var modal_width = "30%";
        if ($(window).width() < 420) {
            modal_width = "100%"
        }

        $.fancybox({
            width: modal_width,
            height: 'auto',
            autoSize: false,

            href: $("#page-header-desc-configuration-venipakbtn").attr('href'),
            type: 'ajax'
        });

    });

    $(document).on("click","#VENIPAK_LAST_PACK_NO_CHANGE_on",function() {
        $("#VENIPAK_LAST_PACK_NO_NUMBER").attr('type','text');
        $("#VENIPAK_LAST_PACK_NO_NUMBER").closest('.form-group').removeClass('hide');
    });

    $(document).on("click","#VENIPAK_LAST_PACK_NO_CHANGE_off",function() {
        $("#VENIPAK_LAST_PACK_NO_NUMBER").attr('value', '');
        $("#VENIPAK_LAST_PACK_NO_NUMBER").attr('type','hidden');
        $("#VENIPAK_LAST_PACK_NO_NUMBER").closest('.form-group').addClass('hide');
    });

    $(document).on("click","#VENIPAK_SHOW_DELIVERY_TYPES_on",function() {
        $(".venipak-delivery-types").removeClass('hide');
    });

    $(document).on("click","#VENIPAK_SHOW_DELIVERY_TYPES_off",function() {
        $('[id^=VENIPAK_DELIVERY_TYPES]').prop( "checked", false );
        $(".venipak-delivery-types").addClass('hide');
    });
}
