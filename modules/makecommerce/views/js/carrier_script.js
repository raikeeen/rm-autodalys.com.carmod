jQuery(document).ready(function() {
    var orderSelected = 'body#checkout';

    var makecommerce_carrier = {
        checked_options: jQuery(orderSelected + ' div#center_column input.delivery_option_radio:checked'),
        ids: [],
        address: 0
    };

    var makecommerce_carrier_error = '';
    var selected_makecommerce_carriers;
    var selects = false;
    var module;
    var carriers;

    for (var i = makecommerce_carrier.checked_options.length - 1; i >= 0; i--) {
        makecommerce_carrier.ids[getAddress(makecommerce_carrier.checked_options[i].id)] = makecommerce_carrier.checked_options[i].id;
    }

    jQuery(orderSelected + ' div#center_column').off('click').on('click', 'label', function(evt){
        makecommerce_carrier.address = getAddress(evt.currentTarget.control.id);
        if(!(makecommerce_carrier.address in makecommerce_carrier.ids))
            makecommerce_carrier.ids[makecommerce_carrier.address] = evt.currentTarget.control.id;
        if(jQuery.inArray(evt.currentTarget.control.id, makecommerce_carrier.ids) !== -1)
            return false;
        makecommerce_carrier.ids[makecommerce_carrier.address] = evt.currentTarget.control.id;
    });



    moveList();


    var disableCheckout = false;

    if($('.delivery-option input:checked').parents('.delivery-option').find('.makecommerce_carrier').val() == "0"){
        disableCheckout = true;
    }else{
        disableCheckout = false;
    }

    $('body#checkout section.checkout-step .custom-radio input[type=radio]').on('click', function() {
        if($(this).parents('.delivery-option').find('.makecommerce_carrier').length && $(this).parents('.delivery-option').find('.makecommerce_carrier').val() == 0){
            disableCheckout = true;
        }else{
            disableCheckout = false;
        }
    });


    $('.makecommerce_carrier').on('change',function(){
        if($(this).val() == 0){
            disableCheckout = true;
        }else{
            disableCheckout = false;
        }
    });

    $(".delivery-options-list button[type='submit']").on( "click", function( event ) {
        if(disableCheckout){
            event.preventDefault();
            $('html, body').stop().animate({
                scrollTop: $("#checkout-delivery-step").offset().top
            }, 1000);
            alert(chooseTerminal);
        }
    });
});


function getAddress(id_string) {
    var splitted = id_string.split('_');
    return splitted[2];
}


function updateCarrierTerminal(id_address, name, module) {
    var data = {
        terminal_id: $('select#' + module + '_' + id_address).val(),
        id_address: id_address,
        name: module
    };
    var current_carrier;
    $.ajax({
        type: 'POST',
        url:  $('.'+name+'_'+id_address+'_ajax').first().val(),
        data: jQuery.param(data),
        dataType: 'json',
        success: function(json) {
            current_carrier = $('select#' + module + '_' + id_address).parent().parent().find('.custom-radio input');
            if(
                (current_carrier.length === 1 && !current_carrier.prop('checked')) ||
                    parseInt(json['old_terminal_id'], 10) === 0 ||
                    parseInt(data.terminal_id, 10) === 0
                )
            { // is_carrier_checked || selected_terminal || deselected_terminal
                if (current_carrier.length === 1)
                {
                    current_carrier.prop('checked', true).click().change();
                }
                else
                {
                    updateCarrierSelectionAndGift();
                }
            }
        }
    });
}

function updateCarrierCity(id_address, name, currentapm, citytext) {
    var data = {
        terminal_city: $('select#' + name + '_location').val(),
        id_address: id_address,
        currentapm: currentapm,
        cityText: citytext
    };
    var apms = this[name + '_terminals'];
    var content = '';
    var selectedExists = 0;
    apms.forEach( function (element) {
       if (element.city == data.terminal_city) {
           content += '<option value="' + element.id + '"';
           if (element.id == currentapm) {
               content += ' selected="selected"';
               selectedExists = 1;
           }
           content += ' data-address="' + element.name + '">' + element.name + '</option>' +"\n";
       }
    });
    if ( selectedExists == 0)
        content = '<option value="0" selected="selected">' + citytext + '</option>'+"\n" + content;
    if ( selectedExists == 1)
        content = '<option value="0">' + citytext + '</option>'+"\n" + content;
    $('select#' + name + '_' + id_address).html(content);
}


function moveList()
{

    $('.delivery-options input').each(function() {
        module = $('#hook-display-before-carrier > #makecommerce_carrier_'+parseInt($(this).val()));
        if (module.length === 1)
        {
            $(this).closest('.delivery-option').find('label').append(module.html());
            module.remove();
        }
    });

}

function getActiveDeliveryOption()
{
    $delivery_option = $('.delivery_option_radio:checked').closest('.delivery_option');
    if ($delivery_option.length)
    {
        if ($delivery_option.find('.makecommrece-carrier'))
        {
            alert($delivery_option);
            return $delivery_option;
        }
        else
        {
            return false;
        }
    }
    else
    {
        return false;
    }
}

