$(document).ready( function () {
    updateActiveFields();
    updateBoxSelect(true);
});

$(document).on('change', '#lp_carrier', function () {
    updateActiveFields();
});

$(document).on('change', '#lp_terminal, #lp_carrier', function () {
    updateBoxSelect(false);
});

$(document).on('change', '[name="lp_cod"]', function () {
    updateCodAmountField();
});

function updateActiveFields() {
    var id_reference = $('#lp_carrier').val();

    $('[data-reference-ignore-carrier]:not([data-reference-ignore-carrier="'+id_reference+'"])').slideDown();
    $('[data-reference-ignore-carrier="'+id_reference+'"]').slideUp();

    var postCheckbox = $('[name="lp_cod"]:checked');
    var postField = postCheckbox.closest('div.form-group');
    if (postField.length)
    {
        if (postField.data('reference-ignore-carrier') == id_reference)
        {
            $('#lp_cod_amount').slideUp();
        }
        else if(postCheckbox.val() == 1)
        {
            $('#lp_cod_amount').slideDown();
        }
    }

    $('[data-reference-carrier]:not([data-reference-carrier="'+id_reference+'"])').slideUp();
    $('[data-reference-carrier="'+id_reference+'"]').slideDown();
}

function updateCodAmountField() {
    var cod_checkbox = $('[name="lp_cod"]:checked');

    if (cod_checkbox.val() == 1)
    {
        $('#lp_cod_amount').slideDown();
    }
    else
    {
        $('#lp_cod_amount').slideUp();
    }
}

function updateBoxSelect(first) {
    var box_selection = $('#lp_box_size');
    var id_reference = $('#lp_carrier').val();

    var current_value;
    if (first)
    {
        current_value = $('#lp_terminal_box_size').val();
    }
    else
    {
        current_value = box_selection.val();
    }

    // Remove old sizes
    $(box_selection).find('option').not(':first').remove();

    var boxes = false;
    if (LP_ORDER_HOME_TYPE == 'CH' && id_reference == carrier_home_reference)
    {
        boxes = all_boxes;
    }
    else
    {
        var id_terminal = $('#lp_terminal').val();
        if (id_terminal in terminals)
        {
            boxes = terminals[id_terminal]['boxes'];
        }
    }

    if (boxes)
    {
        boxes.forEach(function(entry) {
            var option = $('<option>');
            option.attr('value', entry['id_lpexpress_box']);
            option.text(entry['size']);
            if (current_value == entry['id_lpexpress_box'])
            {
                option.attr('selected', true);
            }
            box_selection.append(option);
        });
    }
}