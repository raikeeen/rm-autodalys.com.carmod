var VenipakModule = new function(){
    var self = this;

    this.initHooks = function (e) {

        // select2;
        if (venipak_ps_version == 1.7) {
            $(".venipak_select2").select2({ width: 'resolve' });
        }
    }

    this.initActions = function (e) {

        // Save customer selections;
        $(document).on('blur', 'input[name^=\'venipak\']', function () {
            self.veniSaveConfigs();
        });

        $(document).on('change', '#venipak_delivery_type', function(e){
            self.veniSaveConfigs();
        });

        // Hide venipak error on pickup change;
        $(document).on('change', '#venipak_id_pickup_point', function(e){

            self.veniSaveConfigs();

            if ($('#venipak_id_pickup_point').val() != "0") {
                $('.venipakAlert').addClass('hidden');
            }
        });

        // Validate;
        if (venipak_ps_version == 1.6) {
            $(document).on('click', '.payment_module a', function(e){
                self.validateVenipak(e, $(this).attr('href'));
            });

            $(document).on('click', 'button[name=processCarrier]', function(e){
                if (venipak_checkout_type == 1) {
                    self.validateVenipak(e, null);
                } else {
                    return self.validateVenipak(e, null);
                }
            });

        } else if (venipak_ps_version == '1.7') {
            $('form#js-delivery').off('submit').on('submit', function(e){
                return self.validateVenipak(e, null);
            });
        }
    };

    this.validateVenipak = function(e, moduleLink = null) {

        // Set selectors by conditions;
        if (venipak_ps_version == '1.6' && venipak_checkout_type == 0) {
            selectedCarrier = $("input.delivery_option_radio:checked");
            carrierFormSelector = $('form[name="carrier_area"]');

        } else if (venipak_ps_version == '1.6' && venipak_checkout_type == 1) {
            selectedCarrier = $("input.delivery_option_radio:checked");
            carrierFormSelector = $('form[name="carrier_area"]');

            e.preventDefault();
        } else if (venipak_ps_version == '1.7') {
            selectedCarrier = $('.delivery-options .delivery-option input[type="radio"]:checked');
            carrierFormSelector = $('#js-delivery');
        }

        // Validate customer pickup point;
        if (selectedCarrier.val() == venipak_id_parcels + ',' && $('#venipak_id_pickup_point').val() == "0")
        {
            $('.venipakAlert').removeClass('hidden');

            if (venipak_ps_version == '1.7' || (venipak_ps_version == '1.6' && venipak_checkout_type == 0)) {
                return false;
            }
        } else {
            if (venipak_ps_version == '1.6' && venipak_checkout_type == 1) {
                window.location.href = moduleLink; // redirect to payment module;
            } else {
                return true;
            }
        }
    }

    this.veniSaveConfigs = function(){
        var ajaxData = {};
        ajaxData.venipak_delivery_type = $("#venipak_delivery_type").val();
        ajaxData.venipak_door_code = $("#venipak_door_code").val();
        ajaxData.venipak_office_no = $("#venipak_office_no").val();
        ajaxData.venipak_warehouse_no = $("#venipak_warehouse_no").val();
        ajaxData.venipak_comment_call = $("#venipak_comment_call").val();
        ajaxData.venipak_id_pickup_point = $("#venipak_id_pickup_point").val();
        ajaxData.venipak_token = venipak_token;

        ajaxData.chosenCarrier = $("input[name*='delivery_option[']:checked").val().split(',')[0];

        //ajax call
        $.ajax(venipak_controller_url,
            {
                data: ajaxData,
                type:"POST",
                dataType: "json",
            });

        $('.venipakAlert').removeClass('hidden').addClass('hidden');
    }

    this.onReady = function(){
        self.initActions();
        self.initHooks();
    };
}

$(document).ready(function() {
    VenipakModule.onReady();
});

