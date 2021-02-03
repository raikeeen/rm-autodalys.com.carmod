$(function() {

    $('#cart_navigation > ').on('click', function(evt) {
        $('#veebipoed-overlay').show();
    });

    $('.payment-method').on('click', function(evt) {
        evt.preventDefault();
        $('#veebipoed-overlay').show();
        $.ajax(mk_ajax_url, {
            'data': 'action=create_transaction&method='+$(this).data('method'),
            'dataType': 'json'
        }).success(function(data) {
                if (data.type == 'banklinks' || data.type == 'other' || data.type == 'payLater') {
                    window.location.href = data.url;
                } else {
                    $('#center_column').append(data.html);
                    var interval_id = setInterval(function() {
                        if (typeof window.Maksekeskus == 'object' &&
                            typeof window.Maksekeskus.Checkout == 'object'
                            ) {
                            $('#makecommerce-form').replaceWith(function(){
                                return $('<form method="POST" id="makecommerce-form"></form>').append($(this).contents());
                            });
                            window.Maksekeskus.Checkout.initialize();
                            window.Maksekeskus.Checkout.open();
                            $('.payment-method').off('click').on('click', function(evt) {
                                evt.preventDefault();
                                evt.stopPropagation();
                                window.Maksekeskus.Checkout.open();
                            });
                            clearInterval(interval_id);
                            $('#veebipoed-overlay').hide();
                        }
                    }, 100);
                }
            });
    });

    //Hide widget settings if payment display method isn't widget
    if($('#methods_display').val() != 2)
        $('.widget_setting').hide();
    $('#methods_display').change(function() {
        if($('#methods_display').val() == 2){
            $('.widget_setting').show();
        }else{
            $('.widget_setting').hide();
        }
    });


    //Hide simplecheckout settings if not enabled
    $('#configuration_form.makecommerce .panel:nth(2)').hide();
    if ($('.sco_switch input:checked').val() != 1) {
        $('.sco_setting').hide();
        $('#configuration_form.makecommerce .panel:nth(2)').show();
    }
    $('.sco_switch input').change(function() {
        if($('.sco_switch input:checked').val() == 1){
            $('.sco_setting').show();
            $('#configuration_form.makecommerce .panel:nth(2)').hide();
        }else{
            $('.sco_setting').hide();
            $('#configuration_form.makecommerce .panel:nth(2)').show();
        }
    });

    //Hide Omniva settings if omniva isn't enabled
    if($('.omniva_switch input:checked').val() != 1)
        $('.omniva_setting').hide();
    $('.omniva_switch input').change(function() {
        if($('.omniva_switch input:checked').val() == 1){
            $('.omniva_setting').show();
        }else{
            $('.omniva_setting').hide();
        }
    });

    //Hide SmartPost settings if smartpost isn't enabled

    if($('.smartpost_switch input:checked').val() != 1)
        $('.smartpost_setting').hide();
    $('.smartpost_switch input').change(function() {
        if($('.smartpost_switch input:checked').val() == 1){
            $('.smartpost_setting').show();
        }else{
            $('.smartpost_setting').hide();
        }
    });

    if($('#server').val() == 0){
        $('.test_settings').show();
        $('.live_settings').hide();
    }else{
        $('.test_settings').hide();
        $('.live_settings').show();
    }

    $('#server').change(function() {
        if($('#server').val() == 0){
            $('.test_settings').show();
            $('.live_settings').hide();
        }else{
            $('.test_settings').hide();
            $('.live_settings').show();
        }
    });

    if($('.adminorders #formAddPaymentPanel').lenght){
        $('.adminorders #formAddPaymentPanel').after($('#makecommerce_refund'));
    }else{
        $('.adminorders #formAddPayment').parent().after($('#makecommerce_refund'));
    }
    // Move makecommerce label block
    if($('.adminorders #formAddPaymentPanel').lenght){
        $('.adminorders #formAddPaymentPanel').before($('#makecommerce_label'));
    }else{
        $('.adminorders #formAddPayment').parent().before($('#makecommerce_label'));
    }

    if (typeof(mk_show_country_code) != "undefined" && mk_show_country_code !== null && mk_show_country_code !== "") {
        var country = $('.flag_mk_select .selected a').data('country');
        $('.mk_method:not(.mk_country-all)').hide();
        $('.mk_country-'+country).show();
        $('.flag_mk_select a').click(function(e) {
            e.preventDefault();
            $('.flag_mk_select li').removeClass('selected');
            $(this).closest('li').addClass('selected');
            var country = $('.flag_mk_select .selected a').data('country');
            $('.mk_method:not(.mk_country-all)').hide();
            $('.mk_country-'+country).show();
        });
    }

    if (typeof(mk_expanded) != "undefined" && mk_expanded !== null && !mk_expanded) {
        $('#mk_widget:not(.expanded) a').click(function() {
            $('#mk_widget').addClass('expanded');
        });
        $('#mk_widget .icon-chevron-down').click(function() {
            $('#mk_widget').toggleClass('expanded');
        });
    }

});