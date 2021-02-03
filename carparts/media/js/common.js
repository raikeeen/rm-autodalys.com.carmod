    function AddTips(track){
        jQuery(function() {  jQuery( document ).tooltip({track:true, content:function(){return jQuery(this).prop('title');}});   });
    }
    function AddFSlyler(options){
        (function(jQuery) {  jQuery(function(){ jQuery(options).styler(); }); })(jQuery)
    }
    function TDMAddToCart(PHID,PKEY){
        PKEY = PKEY || '';
        var QTY = '';
        if(jQuery("#Qt_"+PHID).val()!=''){QTY=jQuery("#Qt_"+PHID).val();}
        jQuery("<form action='#item"+PKEY+"' id='addcartform' method='post'><input type='hidden' name='PHID' value='"+PHID+"'/><input type='hidden' name='QTY' value='"+QTY+"'/></form>").appendTo('body');
        jQuery("#addcartform").submit();
    }
    function ResetWSCache(){
        jQuery("<form action='' id='resetwscform' method='post'><input type='hidden' name='wsc' value='reset'/>").appendTo('body');
        jQuery("#resetwscform").submit();
    }
    function TDMOrder(PKEY){
        jQuery("<form action='' id='addcartform' method='post'><input type='hidden' name='TDORDER' value='"+PKEY+"'/></form>").appendTo('body');
        jQuery("#addcartform").submit();
    }
    jQuery(document).ready(function() {
        /* tips */
        jQuery('.tdmtip').hover(function(){
            var title = jQuery(this).attr('title');
            if(title){
                jQuery(this).data('tipText', title).removeAttr('title');
                jQuery('<p class="tdmtiplay"></p>').html(title).appendTo('body').show(); //alert('+'+title);
            }else{return false;}
        },function(){
            jQuery(this).attr('title', jQuery(this).data('tipText'));
            jQuery('.tdmtiplay').remove();
        }).mousemove(function(e) {
            var mousex = e.pageX + 16; //Get X coordinates
            var mousey = e.pageY + 7; //Get Y coordinates
            jQuery('.tdmtiplay').css({ top: mousey, left: mousex })
        });
    });
    jQuery(document).mouseup(function (e) {
        var container = jQuery(".mouseMiss");
        if (container.has(e.target).length === 0){
            container.hide();
        }  

        // COUNTRY FROM product_page
        
        $('.cmAdressTb').click(function (){
            $('.cmAdressTb').each(function () {
                $(this).find('.cmCountrNameH').removeClass('c_Tx');
                $(this).find('.cmLogoNameCountr').removeClass('c_Border');
            });
            $(this).find('.cmLogoNameCountr').addClass('c_Border');
            $(this).find('.cmCountrNameH').removeClass('c_TxHov');
            $(this).find('.cmCountrNameH').addClass('c_Tx');
            var dAddr = $(this).find('.cmLogoNameCountr').data('countryname');
            $(function() {
                $('.cmInfoAdrr').filter(function(){ return $(this).data("countryname") !== dAddr;}).hide();
                $('.cmInfoAdrr').filter(function(){ return $(this).data("countryname") === dAddr;}).show();
            });
        });
    });
      
        
        