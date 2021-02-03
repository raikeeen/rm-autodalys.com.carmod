jQuery(document).ready(function($){
    //SEND REQUEST
    $('#CmContent').on('click', '#CmRequestSub', function(){
        jQuery("#CmLoadImg").width(jQuery(".CmFormBlock").width()).height(jQuery(".CmFormBlock").height()).fadeIn(200);
        var Brand = $(this).data('brand'),
            Article = $(this).data('artnum'),
            ModuleDir = $(this).data('moduledir'),
            DataLang = $(this).data('lang'),
            Link = $(this).data('link'),
            name = $('input[name="Reqname"]').val(),
            email = $('input[name="Useremail"]').val(),
            text = $('textarea[name="message"]').val(),
            capt = $('input[name="captcha"]').val();
        jQuery.post('/'+ModuleDir+'/add/askprice/controller.php', {ModDir:ModuleDir, Name:name, Email:email, Text:text, Capt:capt, Article:Article, Brand:Brand, Lang:DataLang, Link:Link, Subreq:"Y"},
         function(Res){
            jQuery('.fxCont').html(Res);
            jQuery('#CmLoadImg').fadeOut(200);
        });
    });

    //CHECK INPUT
    function emprtyInp(e){
        $(e).css({'border' : '1px solid #ff0000'});
        var empty = $(e).data('empty');
        $(e).siblings('.CmErrBlock').find('.CmErr').html(empty).fadeIn(300);
    }

    $('input[name="Useremail"]').blur(function() {
        if($(this).val() != '') {
            var patemail = /^([a-z0-9_\.-])+@[a-z0-9-]+\.([a-z]{2,4}\.)?[a-z]{2,4}$/i;
            var patphone = /^\d[\d\(\)\ -]{4,14}\d$/;
            if(patemail.test($(this).val()) || patphone.test($(this).val())){
                $(this).css({'border' : '1px solid #569b44'});
            }else{
               $(this).css({'border' : '1px solid #ff0000'});
               var email = $(this).data('email');
               $(this).siblings('.CmErrBlock').find('.CmErr').html(email).fadeIn(300);
            }
        }else{
            var inp = $(this);
            emprtyInp(inp);
        }
    });
    $('input[name="Reqname"]').blur(function() {
        if($(this).val() != '' && $(this).val().length < 3) {
            $(this).css({'border' : '1px solid #ff0000'});
            var name = $(this).data('name');
            $(this).siblings('.CmErrBlock').find('.CmErr').html(name).fadeIn(300);
        }else if($(this).val() == ''){
            var inp = $(this);
            emprtyInp(inp);
        }else if($(this).val().length >= 3){
            $(this).css({'border' : '1px solid #569b44'});
        }
    });
    $('textarea[name="message"]').blur(function() {
        if($(this).val() != '' && $(this).val().length < 20) {
            $(this).css({'border' : '1px solid #ff0000'});
            var mess = $(this).data('mess');
            $(this).siblings('.CmErrBlock').find('.CmErr').html(mess).fadeIn(300);
        }else if($(this).val() == ''){
            var inp = $(this);
            emprtyInp(inp);
        }else if($(this).val().length >= 20){
            $(this).css({'border' : '1px solid #569b44'});
        }
    });
    $('input[name="captcha"]').blur(function() {
        if($(this).val() != '' && $(this).val().length < 6) {
            $(this).css({'border' : '1px solid #ff0000'});
            var code = $(this).data('code');
            $(this).siblings('.CmErrBlock').find('.CmErr').html(code).fadeIn(300);
        }else if($(this).val() == ''){
            var inp = $(this);
            emprtyInp(inp);
        }else if($(this).val().length == 6){
            $(this).css({'border' : '1px solid #569b44'});
        }
    });
    $('input[name="captcha"]').keyup(function() {
        var value = $(this).val();
        var patcapt = /[-\.;":'a-zA-Zа-яА-Я]/;
        if (patcapt.test(value)) {
            var numbon = $(this).data('numbonly');
            $(this).siblings('.CmErrBlock').find('.CmErr').html(numbon).fadeIn(300);
        }else if(!patcapt.test(value)){
            $(this).siblings('.CmErrBlock').find('.CmErr').fadeOut(300);
        }
    });
    $('.CmReqInput').focus(function(){
        $(this).siblings('.CmErrBlock').find('span').fadeOut(300);
    });
    $('.CmReqTextarea').focus(function(){
        $(this).siblings('.CmErrBlock').find('span').fadeOut(300);
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
