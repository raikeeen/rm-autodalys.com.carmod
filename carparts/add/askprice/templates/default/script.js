Array.prototype.remove = function(value) {
    var idx = this.indexOf(value);
    if (idx != -1) {
        // Второй параметр - число элементов, которые необходимо удалить
        return this.splice(idx, 1);
    }
    return false;
}
function emprtyInp(e){
    $(e).css({'border' : '1px solid #ff0000'});
    $(e).siblings('.CmErrBlock').fadeIn(300);
}
var aError = [];
function VarError(name){
    if((aError[name]==undefined&&aError.length == 0)||(aError[name]==undefined&&aError.length > 0)){
        aError.push(name);
    }else if(aError[name]!=undefined && aError.length == 1){
        aError = [name];
    }
    return aError;
}
jQuery(document).ready(function($){
    //CHECK INPUT
    $('#CmContent').on('blur','input[name="Useremail"]',function() {
        var el = $(this);
        if($(this).val() != '') {
            var patemail = /^([a-z0-9_\.-])+@[a-z0-9-]+\.([a-z]{2,4}\.)?[a-z]{2,4}$/i;
            var patphone = /^\d[\d\(\)\ -]{4,14}\d$/;
            if(patemail.test($(this).val()) || patphone.test($(this).val())){
                $(this).css({'border' : '1px solid #569b44'});
                aError.remove(1);
            }else{
               emprtyInp(el);
               VarError(1);
            }
        }else{
            var inp = $(this);
            emprtyInp(inp);
            VarError(1);
        }
    });
    $('#CmContent').on('blur','input[name="Reqname"]',function() {
        var el = $(this);
        if($(this).val() != '' && $(this).val().length < 3) {
            emprtyInp(el);
            VarError(2);
        }else if($(this).val() == ''){
            var inp = $(this);
            emprtyInp(inp);
            VarError(2);
        }else if($(this).val().length >= 3){
            $(this).css({'border' : '1px solid #569b44'});
            aError.remove(2);
        }
    });
    $('#CmContent').on('blur','textarea[name="message"]',function() {
        var el = $(this);
        if($(this).val() != '' && $(this).val().length < 20) {
            emprtyInp(el);
            VarError(3);
        }else if($(this).val() == ''){
            var inp = $(this);
            emprtyInp(inp);
            VarError(3);
        }else if($(this).val().length >= 20){
            $(this).css({'border' : '1px solid #569b44'});
            aError.remove(3);
        }
    });
    $('#CmContent').on('blur','input[name="captcha"]',function() {
        var el = $(this);
        if($(this).val() != '' && $(this).val().length < 6) {
            emprtyInp(el);
            VarError(4);
        }else if($(this).val() == ''){
            var inp = $(this);
            emprtyInp(inp);
            VarError(4);
        }else if($(this).val().length == 6){
            $(this).css({'border' : '1px solid #569b44'});
            aError.remove(4);
        }
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
    $('#CmContent').on('keyup','.CmReqInput, .CmCaptchaInput, .CmReqTextarea',function(){
        $(this).siblings('.CmErrBlock').fadeOut(300);
    });

    //SEND REQUEST
    $('#CmContent').on('click', '#CmAskReqSub', function(){
        $('.CmAskChechEr').each(function(){
            if($(this).val()==''){
                var el = $(this);
                emprtyInp(el);
                VarError(5);
            }else{
                aError.remove(5);
            }
        });
        if(aError.length == 0){
            var Brand = $(this).data('brand'),
                Article = $(this).data('artnum'),
                ModuleDir = $(this).data('moduledir'),
                DataLang = $(this).data('lang'),
                Link = $(this).data('link'),
                name = $('input[name="Reqname"]').val(),
                email = $('input[name="Useremail"]').val(),
                text = $('textarea[name="message"]').val(),
                capt = $('input[name="captcha"]').val();
                $('.fxCont').html('<div id="tempSaver"></div><div class="CmSchLoadWrap" style="display:block; margin:auto;"><div class="CmSchLoading"><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div><div class="CmColorBg"></div></div></div>');
            jQuery.post('/'+ModuleDir+'/add/askprice/controller.php', {ModDir:ModuleDir, Name:name, Email:email, Text:text, Capt:capt, Article:Article, Brand:Brand, Lang:DataLang, Link:Link, Subreq:"Y"},
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
