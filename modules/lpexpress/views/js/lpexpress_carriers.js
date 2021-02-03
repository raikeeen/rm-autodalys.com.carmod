if (typeof LPCarrierTerminal == 'undefined')
{
    LPCarrierTerminal = 0;
    LPCarrierPost = 0;
    LPCarrierHome = 0;
    LPToken = 0;
    LPAjax = 0;
    MessageBadZip = '';
}

// ID of validated carrier
var validated_carrier = null;

$(document).ready( function () {
    LPCarrierTerminal = parseInt(LPCarrierTerminal);
    LPCarrierPost = parseInt(LPCarrierPost);
    LPCarrierHome = parseInt(LPCarrierHome);

    $(document).on('click', '[name="confirmDeliveryOption"], [name="processCarrier"], body#order-opc #HOOK_PAYMENT .payment_module a', function (e) {
        var id = getSelectedCarrier();
        if (!id || !validated_carrier || id !== parseInt(validated_carrier))
        {
            e.preventDefault();
            e.stopPropagation();
            onCarrierSubmit($(this));
        }
        else
        {
            if ($(this).is('a') && $(this).attr('href'))
            {
                location.href = $(this).attr('href');
            }
            return true;
        }
    });
    $(document).on('change', '#lp_express_terminal', function () {
        var id_terminal = $('#lp_express_terminal').val();
        if (!id_terminal || !parseInt(id_terminal))
        {
            return false;
        }

        $(this).parent('.lp_carrier').removeClass('error');
        submitTerminal(id_terminal);
    });
    $(document).on('change', '[name^="delivery_option"]', function () {
        $('.lp_carrier_container').slideUp();
    });

    setTimeout(function () {
        $('#lp_express_terminal').select2();
    }, 250);
});

/**
 * Get selected carrier ID
 */
function getSelectedCarrier() {
    var carrier_radio = $('[name^="delivery_option"]:checked');
    if (carrier_radio.length)
    {
        return parseInt(carrier_radio.val());
    }
    return false;
}

/**
 * Check if everything fine and submit carrier form
 */
function onCarrierSubmit(target) {
    var id_carrier = getSelectedCarrier();
    if (!id_carrier)
    {
        validated_carrier = id_carrier;
        $(target).click();
        return true;
    }

    // Check if carrier one of LP carriers
    if (id_carrier !== parseInt(LPCarrierTerminal) && id_carrier !== parseInt(LPCarrierPost) && id_carrier !== parseInt(LPCarrierHome))
    {
        validated_carrier = id_carrier;
        $(target).click();
        return true;
    }

    if (id_carrier === parseInt(LPCarrierHome))
    {
        $(target).attr('disabled', true);
        submitHome(function () {
            validated_carrier = id_carrier;
            $(target).attr('disabled', false);
            $(target).click();
        }, function () {
            $(target).attr('disabled', false);
        });
        return true;
    }

    // Check if exists carrier data id element
    var carrier_extra = $('[data-carrier-id="'+id_carrier+'"]');
    if (!carrier_extra.length)
    {
        console.log('Can\'t find data-carrier-id for selected carrier: '+id_carrier);
        return false;
    }

    if (id_carrier === parseInt(LPCarrierTerminal))
    {
        if (!$(carrier_extra).find('select#lp_express_terminal').length)
        {
            console.log('Can\'t find select element for LP terminal carrier');
            return false;
        }

        var id_terminal = $('#lp_express_terminal').val();
        if (!id_terminal || !parseInt(id_terminal))
        {
            $(carrier_extra).addClass('error');
            return false;
        }

        // Submit terminal
        $(target).attr('disabled', true);
        submitTerminal(id_terminal, function () {
            validated_carrier = id_carrier;
            $(target).attr('disabled', false);
            $(target).click();
        }, function () {
            $(target).attr('disabled', false);
            $(carrier_extra).addClass('error');
        });
        return true;
    }

    if (id_carrier === parseInt(LPCarrierPost))
    {
        if (!$(carrier_extra).hasClass('error'))
        {
            $(target).attr('disabled', true);
            submitPost(function () {
                validated_carrier = id_carrier;
                $(target).attr('disabled', false);
                $(target).click();
            }, function () {
                $(target).attr('disabled', false);
            });
            return true;
        }
        else
        {
            if (!!$.prototype.fancybox)
                $.fancybox.open([
                        {
                            type: 'inline',
                            autoScale: true,
                            minHeight: 30,
                            content: '<p class="fancybox-error">' + MessageBadZip + '</p>'
                        }],
                    {
                        padding: 0
                    });
            else
            {
                alert(MessageBadZip);
            }
        }
    }
}

function submitTerminal(id_terminal, successCallback, failedCallback) {
    sendAjax('updateOrderTerminal', {
        'id_terminal': id_terminal
    }, successCallback, failedCallback);
}

function submitPost(successCallback, failedCallback) {
    sendAjax('updateOrderPost', {}, successCallback, failedCallback);
}
function submitHome(successCallback, failedCallback) {
    sendAjax('updateOrderAddress', {}, successCallback, failedCallback);
}

function sendAjax(action, data, successCallback, failedCallback) {
    var parameters = {
        'action': action,
        'LPToken': LPToken
    };

    $.extend(parameters, data);

    $.ajax({
        'url': decodeTerminalURL(LPAjax),
        'type': "POST",
        'data': parameters,
        dataType: "JSON",
        success: function(data){
            if (data.success)
            {
                if (typeof successCallback === 'function')
                {
                    successCallback(data);
                }
            }
            else
            {
                if (typeof successCallback === 'function')
                {
                    failedCallback(data);
                }

                if (!!$.prototype.fancybox)
                    $.fancybox.open([
                            {
                                type: 'inline',
                                autoScale: true,
                                minHeight: 30,
                                content: '<p class="fancybox-error">' + data.message + '</p>'
                            }],
                        {
                            padding: 0
                        });
                else
                    alert(data.message);
            }
        }
    });
}

function decodeTerminalURL(url) {
    return $('<div></div>').html(url).text();
}

function movePS16ToCarrier(id_selected_lp_carrier, id_carrier_address) {
    // Remove all containers
    $('.lp_carrier_container:not(.unvisible)').remove();

    // Find carrier container
    var carrier = $('[name="delivery_option['+id_carrier_address+']"][value^="'+id_selected_lp_carrier+'"]');

    if (!carrier.length)
    {
        console.log('Cant find carrier to store LP container');
        $('.lp_carrier_container').remove();
    }

    var container = carrier.closest('.delivery_option');
    if (!container.length)
    {
        console.log('Cant find carrier container');
        $('.lp_carrier_container').remove();
    }

    var content = $('.lp_carrier_container.unvisible');
    content.hide();
    container.append(content);
    content.removeClass('unvisible');
    content.slideDown();
}