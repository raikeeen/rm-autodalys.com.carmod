Array.prototype.remove = function(value) {
    var idx = this.indexOf(value);
    if (idx != -1) {
        // Второй параметр - число элементов, которые необходимо удалить
        return this.splice(idx, 1);
    }
    return false;
}
function MailEmprtyInp(e){
    $(e).css({'border' : '1px solid #ff0000'});
    $(e).siblings('.CmErrBlock').fadeIn(300);
}
var aMailErr = [];
function MailVarError(name){
    if((aMailErr[name]==undefined&&aMailErr.length == 0)||(aMailErr[name]==undefined&&aMailErr.length > 0)){
        aMailErr.push(name);
    }else if(aMailErr[name]!=undefined && aMailErr.length == 1){
        aMailErr = [name];
    }
    return aMailErr;
}
jQuery(document).ready(function($){
    //CHECK INPUT
    $('#CmContent').on('blur','.CmMailPhoneInput',function() {
        var el = $(this);
        if($(this).val() != '') {
            var patemail = /^([a-z0-9_\.-])+@[a-z0-9-]+\.([a-z]{2,4}\.)?[a-z]{2,4}$/i;
            var patphone = /^\d[\d\(\)\ -]{4,14}\d$/;
            if(patemail.test($(this).val()) || patphone.test($(this).val())){
                $(this).css({'border' : '1px solid #569b44'});
                $(this).siblings('.CmErrBlock').fadeOut(300);
                aMailErr.remove(1);
            }else{
               MailEmprtyInp(el);
               MailVarError(1);
            }
        }else{
            MailEmprtyInp(el);
            MailVarError(1);
        }
        console.log(aMailErr);
    });
    $('#CmContent').on('blur','.CmMailNameInput',function() {
        var el = $(this);
        if($(this).val() != '' && $(this).val().length < 3) {
            MailEmprtyInp(el);
            MailVarError(2);
        }else if($(this).val() == ''){
            MailEmprtyInp(el);
            MailVarError(2);
        }else if($(this).val().length >= 3){
            $(this).css({'border' : '1px solid #569b44'});
            $(this).siblings('.CmErrBlock').fadeOut(300);
            aMailErr.remove(2);
        }
        console.log(aMailErr);
    });
    $('#CmContent').on('blur','.CmMailTextarea',function() {
        var el = $(this);
        if($(this).val() != '' && $(this).val().length < 20) {
            MailEmprtyInp(el);
            MailVarError(3);
        }else if($(this).val() == ''){
            MailEmprtyInp(el);
            MailVarError(3);
        }else if($(this).val().length >= 20){
            $(this).css({'border' : '1px solid #569b44'});
            $(this).siblings('.CmErrBlock').fadeOut(300);
            aMailErr.remove(3);
        }
        console.log(aMailErr);
    });
    $('#CmContent').on('blur','.CmMailCaptInput',function() {
        var el = $(this);
        if($(this).val() != '' && $(this).val().length < 6) {
            MailEmprtyInp(el);
            MailVarError(4);
        }else if($(this).val() == ''){
            MailEmprtyInp(el);
            MailVarError(4);
        }else if($(this).val().length == 6){
            $(this).css({'border' : '1px solid #569b44'});
            $(this).siblings('.CmErrBlock').fadeOut(300);
            aMailErr.remove(4);
        }
        console.log(aMailErr);
    });
    // $('#CmContent').on('keyup','input[name="captcha"]',function() {
    //     var value = $(this).val();
    //     var patcapt = /[-\.;":'a-zA-Zа-яА-Я]/;
    //     if (patcapt.test(value)) {
    //         $(this).siblings('.CmErrBlock').fadeOut(300);
    //     }else if(!patcapt.test(value)){
    //         $(this).siblings('.CmErrBlock').fadeIn(300);
    //     }
    // });
    // $('#CmContent').on('keyup','.CmReqInput, .CmCaptchaInput, .CmReqTextarea',function(){
    //     $(this).siblings('.CmErrBlock').fadeOut(300);
    // });

    //SEND REQUEST
    $('#CmContent').on('click', '#CmRequestSub', function(){
        $('.CmMailChechEr').each(function(){
            if($(this).val()==''){
                var elem = $(this);
                MailEmprtyInp(elem);
                MailVarError(5);
            }else{
                aMailErr.remove(5);
            }
            console.log(aMailErr);
        });
        if(aMailErr.length == 0){
            $('.fxCont').html('<div class="CmSchLoadWrap" style="display:block;"><div class="CmSchLoading"><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div></div></div>');
            var mBrand = $(this).data('brand'),
                mArticle = $(this).data('artnum'),
                mModuleDir = $(this).data('moduledir'),
                mDataLang = $(this).data('lang'),
                mLink = $(this).data('link'),
                mName = $(this).parent().parent().find('.CmMailNameInput').val(),
                mEmail = $(this).parent().parent().find('.CmMailPhoneInput').val(),
                mText = $(this).parent().parent().find('textarea[name="message"]').val(),
                mCapt = $(this).parent().parent().find('input.CmMailCaptInput').val();
                $('.fxCont').html('<div id="tempSaver"></div><div class="CmSchLoadWrap" style="display:block; margin:auto;"><div class="CmSchLoading"><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div></div></div>');
            jQuery.post('/'+mModuleDir+'/add/mail_order/controller.php', {ModDir:mModuleDir, Name:mName, Phone:mEmail, Text:mText, Capt:mCapt, Article:mArticle, Brand:mBrand, Lang:mDataLang, Link:mLink, Subreq:"Y"},
             function(Res){
                 $('.fxCont').find('#tempSaver').html(Res);
                 setTimeout(() => {
                     jQuery('.fxCont').html('<div class="fxClose"></div>'+Res);
                 }, 300);
                 $('.fxCont').find('#tempSaver').html('');
            });
        }
    });

    //Popup Tips for input
    $("#CmContent").on("mouseover", ".CmTitShow", function(){
        var title = jQuery(this).attr('title');
        if(title){
            jQuery(this).data('tipText', title).removeAttr('title');
            jQuery('<p class="CmATipBox"></p>').html(title).appendTo('body').fadeIn('slow'); //alert('+'+title);
        }else{return false;}
    });
    $("#CmContent").on("mouseleave", ".CmTitShow", function(){
        jQuery(this).attr('title', jQuery(this).data('tipText'));
        jQuery('.CmATipBox').remove();
    });
    $("#CmContent").on("mousemove", ".CmTitShow", function(e){
        var mousex = e.pageX + 16; //Get X coordinates
        var mousey = e.pageY + 7; //Get Y coordinates
        jQuery('.CmATipBox').css({ top:mousey, left:mousex });
    });
});
