/*
* 2017 Azelab
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@areama.net so we can send you a copy immediately.
*
*
*  @author Azelab <support@azelab.com>
*  @copyright  2017 Azelab

*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Azelab
*/

var arCU = {
    controller: 'AdminArContactUs',
    prevOrder: null,
    ajaxUrl: null,
    addTitle: null,
    editTitle: null,
    successSaveMessage: null,
    successOrderMessage: null,
    successDeleteMessage: null,
    errorMessage: null,
    prompt: {
        ajaxUrl: null,
        controller: 'AdminArContactUsPrompt',
        add: function(){
            arCU.prompt.resetForm();
            jQuery('#arcontactus-prompt-modal-title').html(arCU.addTitle);
            jQuery('#arcontactus-prompt-modal').modal('show');
        },
        populateForm: function(data){
            jQuery.each(data, function(i){
                var fieldId = '#arcontactus_prompt_' + i;
                if (typeof data[i] == 'object'){
                    if (data[i] != null){
                        $.each(data[i], function(id_lang){
                            $(fieldId + '_' + id_lang).val(data[i][id_lang]);
                        });
                    }
                }else{
                    $(fieldId).val(data[i]);
                }
            });
        },
        edit: function(id){
            arCU.prompt.resetForm();
            jQuery('#arcontactus-prompt-modal-title').html(arCU.editTitle);
            arCU.blockUI('#arcontactus-prompt-table');
            jQuery.ajax({
                type: 'GET',
                url: arCU.prompt.ajaxUrl,
                dataType: 'json',
                data: {
                    action : 'edit',
                    controller : arCU.prompt.controller,
                    ajax : true,
                    id: id
                },
                success: function(data){
                    jQuery('#arcontactus-prompt-modal').modal('show');
                    arCU.prompt.populateForm(data);
                    arCU.unblockUI('#arcontactus-prompt-table');
                }
            }).fail(function(){
                arCU.unblockUI('#arcontactus-prompt-modal');
                showErrorMessage(arCU.errorMessage);
            });
        },
        toggle: function(id){
            jQuery.ajax({
                type: 'POST',
                url: arCU.prompt.ajaxUrl,
                dataType: 'json',
                data: {
                    action : 'switch',
                    controller : arCU.prompt.controller,
                    id: id,
                    ajax : true
                },
                success: function(data){
                    arCU.prompt.reload();
                }
            }).fail(function(){
                showErrorMessage(arCU.errorMessage);
            });
        },
        _getFormData: function(){
            var params = [];
            jQuery('#arcontactus-prompt-form [data-serializable="true"]').each(function(){
                var val = $(this).val();
                if ($(this).attr('type') == 'checkbox'){
                    val = $(this).is(':checked');
                }
                params.push({
                    name: $(this).attr('name'),
                    value: val
                });
            });
            return params;
        },
        save: function(){
            var params = arCU.prompt._getFormData();
            jQuery.ajax({
                type: 'POST',
                url: arCU.prompt.ajaxUrl,
                dataType: 'json',
                data: {
                    action : 'save',
                    controller : arCU.prompt.controller,
                    ajax : true,
                    data: params,
                    id: jQuery('#arcontactus_prompt_id').val()
                },
                success: function(data){
                    if (!arCU.prompt.processErrors(data)){
                        showSuccessMessage(arCU.successSaveMessage);
                        jQuery('#arcontactus-prompt-modal').modal('hide');
                        arCU.prompt.reload();
                    }
                }
            }).fail(function(){
                showErrorMessage(arCU.errorMessage);
            });
        },
        clearErrors: function(){
            jQuery('#arcontactus-prompt-form .form-group.has-error').removeClass('has-error');
        },
        processErrors: function(data){
            arCU.prompt.clearErrors();
            if (data.success == 0){
                jQuery.each(data.errors, function(index){
                    if (typeof data.errors[index] == 'object'){
                        var errors = [];
                        var cont = null
                        jQuery.each(data.errors[index], function(i){
                            cont = jQuery('#arcontactus_prompt_'+index + '_' + data.errors[index][i]['id_lang']).parents('.form-group');
                            cont.addClass('has-error');
                            errors.push(data.errors[index][i]['error']);
                        });
                        
                        cont.find('.errors').html(errors.join('<br/>'));
                    }else{
                        jQuery('#arcontactus_prompt_'+index).parents('.form-group').addClass('has-error');
                        jQuery('#arcontactus_prompt_'+index).parents('.form-group').find('.errors').text(data.errors[index]);
                    }
                });
                showErrorMessage(arCU.errorMessage);
                return true;
            }
            return false;
        },
        remove: function(id){
            if (!confirm('Delete this item?')){
                return false;
            }
            jQuery.ajax({
                type: 'POST',
                url: arCU.prompt.ajaxUrl,
                dataType: 'json',
                data: {
                    action : 'delete',
                    controller : arCU.prompt.controller,
                    ajax : true,
                    id: id
                },
                success: function(data){
                    showSuccessMessage(arCU.successDeleteMessage);
                    arCU.prompt.reload(true);
                }
            }).fail(function(){
                showErrorMessage(arCU.errorMessage);
            });
        },
        updateOrder: function(table, silent){
            var positions = [];
            jQuery(table).find('tbody tr').each(function(index){
                var order = index + 1;
                var id = jQuery(this).data('id');
                positions.push(id + '_' + order);
            });
            arCU.blockUI(table);
            if (arCU.prevOrder != positions){
                jQuery.ajax({
                    type: 'POST',
                    url: arCU.prompt.ajaxUrl,
                    dataType: 'json',
                    data: {
                        action : 'reorder',
                        controller : arCU.prompt.controller,
                        ajax : true,
                        data: positions
                    },
                    success: function(data){
                        arCU.unblockUI(table);
                        arCU.prevOrder = positions;
                        if (!silent){
                            //arCU.showSuccessMessage(arCU.successOrderMessage);
                        }
                        jQuery(table).find('tbody tr').each(function(index){
                            var order = index + 1;
                            jQuery(this).find('.position').text(order);
                        });
                    }
                }).fail(function(){
                    arCU.unblockUI(table);
                    showErrorMessage(arCU.errorMessage);
                });
            }
        },
        reload: function(reorder){
            jQuery.ajax({
                type: 'POST',
                url: arCU.prompt.ajaxUrl,
                dataType: 'json',
                data: {
                    action : 'reload',
                    controller : arCU.prompt.controller,
                    ajax : true,
                },
                success: function(data){
                    jQuery('#arcontactus-prompt-table').replaceWith(data.content);
                    arCU.init();
                    if (reorder){
                        arCU.prompt.updateOrder('#arcontactus-prompt-table', true);
                    }
                }
            }).fail(function(){
                showErrorMessage(arCU.errorMessage);
            });
        },
        resetForm: function(){
            jQuery('#arcontactus-prompt-form [data-default]').each(function(){
                var attr = jQuery(this).attr('data-default');
                if (typeof attr !== typeof undefined && attr !== false) {
                    jQuery(this).val(jQuery(this).data('default'));
                }
            });
            arCU.prompt.clearErrors();

        },
    },
    callback: {
        reload: function(){
            $.ajax({
                type: 'POST',
                url: arCU.ajaxUrl,
                dataType: 'json',
                data: {
                    controller : arCU.controller,
                    action : 'reloadCallbacks',
                    ajax : true
                },
                success: function(data)
                {
                    $('#arcontactus-callbacks-table').replaceWith(data.content);
                }
            }).fail(function(){
                showErrorMessage(arCU.errorMessage);
            });
        },
        toggle: function(id, status){
            $.ajax({
                type: 'POST',
                url: arCU.ajaxUrl,
                dataType: 'json',
                data: {
                    controller : arCU.controller,
                    action : 'callbackSwitch',
                    id: id,
                    status: status,
                    ajax : true
                },
                success: function(data)
                {
                    arCU.callback.reload();
                }
            }).fail(function(){
                showErrorMessage(arCU.errorMessage);
            });
        },
        remove: function(id){
            if (!confirm('Delete this item?')){
                return false;
            }
            $.ajax({
                type: 'POST',
                url: arCU.ajaxUrl,
                dataType: 'json',
                data: {
                    controller : arCU.controller,
                    action : 'callbackDelete',
                    ajax : true,
                    id: id
                },
                success: function(data)
                {
                    showSuccessMessage(arCU.successDeleteMessage);
                    arCU.callback.reload();
                }
            }).fail(function(){
                showErrorMessage(arCU.errorMessage);
            });
        },
    },
    init: function(){
        $("#arcontactus-table").tableDnD({	
            dragHandle: 'dragHandle',
            onDragClass: 'myDragClass',
            onDrop: function(table, row) {
                arCU.updateOrder(table, false);
            }
        });
        $("#arcontactus-prompt-table").tableDnD({	
            dragHandle: 'dragHandle',
            onDragClass: 'myDragClass',
            onDrop: function(table, row) {
                arCU.prompt.updateOrder(table, false);
            }
        });
        $('#arcontactus-modal').on('shown.bs.modal', function () {
            $('#fa5-container').scrollTo(0);
            if ($('#fa5 ul li.active').length){
                $('#fa5-container').scrollTo($('#fa5 ul li.active').position().top - $('#fa5 ul li.active').height() - 30);
            }
        });
    },
    add: function(){
        arCU.resetForm();
        $('#arcontactus-modal-title').html(arCU.addTitle);
        $('#arcontactus-modal').modal('show');
    },
    populateForm: function(data){
        $.each(data, function(i){
            var fieldId = '#arcontactus_' + i;
            if (typeof data[i] == 'object'){
                if (data[i] != null){
                    $.each(data[i], function(id_lang){
                        $(fieldId + '_' + id_lang).val(data[i][id_lang]);
                    });
                }
            }else{
                if ($(fieldId).attr('type') == 'checkbox'){
                    if (data[i] == 1){
                        $(fieldId).prop('checked', 'true');
                    }else{
                        $(fieldId).removeProp('checked');
                    }
                }else{
                    $(fieldId).val(data[i]);
                }
            }
        });
        
        if (data.always == '1'){
            $('#ARCU_ALWAYS_on').click();
        }else{
            $('#ARCU_ALWAYS_off').click();
        }
        if (data.product_page == '1'){
            $('#ARCU_product_page_on').click();
        }else{
            $('#ARCU_product_page_off').click();
        }
        arContactUsSwitchFields();
        $('.arcu-icon-list li.active').removeClass('active');
        $('.arcu-icon-list li[data-id="' + data.icon + '"]').addClass('active');
        $('#arcontactus_color').trigger('keyup');
        arcontactusChangeType();
    },
    edit: function(id){
        arCU.resetForm();
        $('#arcontactus-modal-title').html(arCU.editTitle);
        arCU.blockUI('#arcontactus-modal');
        $.ajax({
            type: 'GET',
            url: arCU.ajaxUrl,
            dataType: 'json',
            data: {
                controller : arCU.controller,
                action : 'edit',
                ajax : true,
                id: id
            },
            success: function(data)
            {
                $('#arcontactus-modal').modal();
                arCU.populateForm(data);
                arCU.unblockUI('#arcontactus-modal');
            }
        }).fail(function(){
            arCU.unblockUI('#arcontactus-modal');
            showErrorMessage(arCU.errorMessage);
        });
    },
    toggle: function(id){
        $.ajax({
            type: 'POST',
            url: arCU.ajaxUrl,
            dataType: 'json',
            data: {
                controller : arCU.controller,
                action : 'switch',
                id: id,
                ajax : true
            },
            success: function(data)
            {
                arCU.reload();
            }
        }).fail(function(){
            showErrorMessage(arCU.errorMessage);
        });
    },
    toggleProduct: function(id){
        $.ajax({
            type: 'POST',
            url: arCU.ajaxUrl,
            dataType: 'json',
            data: {
                controller : arCU.controller,
                action : 'switchProduct',
                id: id,
                ajax : true
            },
            success: function(data)
            {
                arCU.reload();
            }
        }).fail(function(){
            showErrorMessage(arCU.errorMessage);
        });
    },
    _getFormData: function(){
        var params = [];
        $('#arcontactus-form [data-serializable="true"]').each(function(){
            var val = $(this).val();
            if ($(this).attr('type') == 'checkbox'){
                val = $(this).is(':checked')? 1 : 0;
            }
            params.push({
                name: $(this).attr('name'),
                value: val
            });
        });
        return params;
    },
    save: function(){
        var params = arCU._getFormData();
        $.ajax({
            type: 'POST',
            url: arCU.ajaxUrl,
            dataType: 'json',
            data: {
                controller : arCU.controller,
                action : 'save',
                ajax : true,
                data: params,
                lang: $('#arcontactus_id_lang').val(),
                id: $('#arcontactus_id').val()
            },
            success: function(data)
            {
                if (!arCU.processErrors(data)){
                    showSuccessMessage(arCU.successSaveMessage);
                    $('#arcontactus-modal').modal('hide');
                    arCU.reload();
                }
            }
        }).fail(function(){
            showErrorMessage(arCU.errorMessage);
        });
    },
    clearErrors: function(){
        $('#arcontactus-form .form-group.has-error').removeClass('has-error');
    },
    processErrors: function(data){
        arCU.clearErrors();
        if (data.success == 0){
            $.each(data.errors, function(index){
                $('#arcontactus_'+index).parents('.form-group').addClass('has-error');
                $('#arcontactus_'+index).parents('.form-group').find('.errors').text(data.errors[index]);
            });
            showErrorMessage(arCU.errorMessage);
            return true;
        }
        return false;
    },
    remove: function(id){
        if (!confirm('Delete this item?')){
            return false;
        }
        $.ajax({
            type: 'POST',
            url: arCU.ajaxUrl,
            dataType: 'json',
            data: {
                controller : arCU.controller,
                action : 'delete',
                ajax : true,
                id: id
            },
            success: function(data)
            {
                showSuccessMessage(arCU.successDeleteMessage);
                arCU.reload(true);
            }
        }).fail(function(){
            showErrorMessage(arCU.errorMessage);
        });
    },
    updateOrder: function(table, silent){
        var positions = [];
        $(table).find('tbody tr').each(function(index){
            var order = index + 1;
            var id = $(this).data('id');
            positions.push(id + '_' + order);
        });
        arCU.blockUI(table);
        if (arCU.prevOrder != positions){
            $.ajax({
                type: 'POST',
                url: arCU.ajaxUrl,
                dataType: 'json',
                data: {
                    controller : arCU.controller,
                    action : 'updateOrder',
                    ajax : true,
                    data: positions
                },
                success: function(data)
                {
                    arCU.unblockUI(table);
                    arCU.prevOrder = positions;
                    if (!silent){
                        showSuccessMessage(arCU.successOrderMessage);
                    }
                    $(table).find('tbody tr').each(function(index){
                        var order = index + 1;
                        $(this).find('.dragGroup .positions').text(order);
                    });
                }
            }).fail(function(){
                arCU.unblockUI(table);
                showErrorMessage(arCU.errorMessage);
            });
        }
    },
    reload: function(reorder){
        $.ajax({
            type: 'POST',
            url: arCU.ajaxUrl,
            dataType: 'json',
            data: {
                controller : arCU.controller,
                action : 'reload',
                ajax : true
            },
            success: function(data)
            {
                $('#arcontactus-table').replaceWith(data.content);
                arCU.init();
                if (reorder){
                    arCU.updateOrder('#arcontactus-table', true);
                }
            }
        }).fail(function(){
            showErrorMessage(arCU.errorMessage);
        });
    },
    resetForm: function(){
        arCU.clearErrors();
        $('#arcontactus-form [data-default]').each(function(){
            var attr = $(this).attr('data-default');
            if (typeof attr !== typeof undefined && attr !== false) {
                if ($(this).attr('type') == 'checkbox'){
                    if ($(this).data('default') == 1){
                        $(this).prop('checked', 'true');
                    }else{
                        $(this).removeProp('checked');
                    }
                }else{
                    $(this).val($(this).data('default'));
                }
            }
        });
        $('#ARCU_ALWAYS_on').click();
        $('#ARCU_product_page_off').click();
        arContactUsSwitchFields();
        $('#fa5 ul li.active').removeClass('active');
        $('#arcontactus_color').trigger('keyup');
        arcontactusFindIcon();
        arcontactusChangeType();
    },
    blockUI: function(selector){
        $(selector).addClass('ar-blocked');
        $(selector).find('.ar-loading').remove();
        $(selector).append('<div class="ar-loading"><div class="ar-loading-inner">Loading...</div></div>');
    },
    unblockUI: function(selector){
        $(selector).find('.ar-loading').remove();
        $(selector).removeClass('ar-blocked');
    },
};