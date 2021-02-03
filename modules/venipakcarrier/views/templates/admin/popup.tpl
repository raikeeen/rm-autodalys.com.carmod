<div id="venipak_popup" class="bootstrap">
    <div id="tabs-container">
        <ul class="nav nav-tabs" style="background-color: #F9F9F9;">
            {assign var=counter value=0}
            {foreach from=$venipak_warehouses item=warehouse}
                <li {if $counter==0} class="active" {/if}><a href="#tab-{$warehouse.id}">{$warehouse.address_title}</a></li>
                {assign var=counter value=$counter+1}
            {/foreach}
        </ul>
        <div class="tab-content">
            {assign var=tabCounter value=0}
            {foreach from=$venipak_warehouses item=warehouse}
            {assign var=warehouseId value=$warehouse.id}
            {assign var=tabCounter value=$tabCounter+1}
                <div id="tab-{$warehouse.id}" class="tab-pane call-courrier-popup {if $tabCounter==1} active {/if}" style="overflow:hidden;">
                    
                    <div class="courrier-call-wrapper">
                        {if $courrierCalls.$warehouseId}
                            <div style="line-height: 25px;">
                            {foreach from=$courrierCalls.$warehouseId item=call}
                            <div class="callentry">
                                <span class="courrier-called">{l s="Courrier called"}:</span>
                                {assign var=calld value=$call.data_parsed}
                                <b>{$calld.date_y}-{$calld.date_m}-{$calld.date_d} {$calld.hour_from}:{$calld.min_from}-{$calld.hour_to}:{$calld.min_to}</b>, {l s="Weight:"} <b>{$calld.weight}</b>, {l s="Pallets:"} <b>{$calld.pallets}</b>
                                {if $calld.comment!=""}
                                    <b>{$calld.comment}</b>
                                {/if}
                            </div>
                            {/foreach}
                            </div>
                        {/if}
                    <div class="courrier-call-form">
                        <form method="POST" action="#">
                            <input type="hidden" name="warehouse_id" value="{$warehouse.id}" />
                            <div class="form-row col-md-6 col-xs-12">
                                <label for="weight">{l s='Weight'}</label>
                                <input type="text" name="weight" value="" />
                            </div>
                            <div class="form-row col-md-6 col-xs-12">
                                <label for="pallets">{l s='Pallets'}</label>
                                <input type="text" name="pallets" value="0" />
                            </div>

                            <div class="form-row col-md-6 col-xs-12">
                                <label for="arrive_date">{l s='Date of arrival'}</label>
                                <select name="arrive_date" class="veni-arrive-date">
                                {foreach $couries_call_dates item=datetime}
                                        {assign var=calldate value=$datetime.date}
                                        <option value="{$datetime.date}" data-weight="{$weights_by_warehouse_by_date.$warehouseId.$calldate}">{$datetime.date}</option>
                                {/foreach}
                                </select>
                                    <div class="call-times-select" data-fordate="{$datetime.date}">
                                        <label for="arrive_date">{l s='From:'}</label>
                                        <div class="clearfix"></div>
                                        <select name="hour_from" class="hour_from col-md-4 col-xs-12">
                                            <option value="8">8</option>
                                            <option value="9">9</option>
                                            <option value="10">10</option>
                                            <option value="11">11</option>
                                            <option value="12">12</option>
                                            <option value="13">13</option>
                                            <option value="14">14</option>
                                            <option value="15">15</option>
                                            <option value="16">16</option>
                                            <option value="17">17</option>
                                            <option value="18">18</option>
                                            <option value="19">19</option>
                                            <option value="20">20</option>
                                            <option value="21">21</option>
                                        </select>
                                        <select name="min_from" class="min_from col-md-4 col-xs-12">
                                            <option value="00">00</option>
                                            <option value="15">15</option>
                                            <option value="30">30</option>
                                            <option value="45">45</option>
                                        </select>
                                        <div class="clearfix"></div>
                                        <div class="col-md-1"></div>
                                        <div class="clearfix"></div>

                                        <div class="venipak-date-to">
                                            <label for="arrive_date">{l s='To:'}</label>
                                            <div class="clearfix"></div>
                                            <select name="hour_to" class="hour_to col-md-4 col-xs-12">
                                                <option value="10">10</option>
                                                <option value="11">11</option>
                                                <option value="12">12</option>
                                                <option value="13">13</option>
                                                <option value="14">14</option>
                                                <option value="15">15</option>
                                                <option value="16">16</option>
                                                <option value="17">17</option>
                                                <option value="18">18</option>
                                                <option value="19">19</option>
                                                <option value="20">20</option>
                                                <option value="21">21</option>
                                                <option value="22">22</option>
                                                <option value="23">23</option>
                                            </select>


                                            <select name="min_to" class="min_to col-md-4 col-xs-12">
                                                <option value="00">00</option>
                                                <option value="15">15</option>
                                                <option value="30">30</option>
                                                <option value="45">45</option>
                                            </select>
                                            <div class="clearfix"></div>
                                        </div>
                                    </div>


                                <p class="notice">{l s="There has to be at least 2 hours interval."}</p>
                            </div>
                            <div class="form-row col-md-6 col-xs-12">
                                <label for="arrive_date">{l s='Comment:'}</label>
                                <textarea name="comment"></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-12 col-xs-12">
                                    <div class="form-row" style="padding-top: 10px; text-align: right;">
                                        <button type="submit" class="veni-call-carrier btn btn-default" name="veni-call-carrier" value=""><i class="icon-phone"></i> {l s='Submit call'}</button>
                                    </div>
                                </div>
                            </div>

                        </form>
                    </div>
                    </div>
                </div>
            {/foreach}
        </div>
    </div>
    <div class="clearfix"></div>
</div>

{literal}
    <style type="text/css">
      .courrier-call-form .form-row {
          margin-top:15px;
      }

      .call-times-select {
          margin-top:15px;
      }

      .courrier-call-form p {
          margin-top:10px;
      }

      .courrier-call-form .form-row select {
          margin-right:15px;
      }

        .courrier-call-form .venipak-date-to {
            margin-top:15px;
        }
    </style>
{/literal}

<script type="text/javascript">
$(document).ready(function() {
    $("#venipak_popup .nav-tabs a").click(function(event) {
        event.preventDefault();
        $(this).parent().addClass("active");
        $(this).parent().siblings().removeClass("active");
        var tab = $(this).attr("href");
        $("div.tab-pane.call-courrier-popup.active").not(tab).css("display", "none").removeClass('active');
        $(tab).fadeIn().addClass('active');
    });

    fixHoursFrom(true);

    $('.veni-arrive-date').on('change', function () {
        fixHoursFrom(false);
    });

    $('.hour_from').on('change', function () {
        fixHoursTo(false);
    });

    function fixHoursFrom(initial)
    {
        var selected_tab;

        if (initial == false)
            selected_tab = $('div.tab-pane.call-courrier-popup.active');
        else
            selected_tab = $('div.tab-pane.call-courrier-popup');


        if (selected_tab.length == 0) {
            alert('Error! Close tab!');
            return;
        }

        var now = new Date();
        var day = ("0" + now.getDate()).slice(-2);
        var month = ("0" + (now.getMonth() + 1)).slice(-2);
        var today_date = now.getFullYear() + "-" + (month) + "-" + (day);

        if ($('.veni-arrive-date option:selected', selected_tab).val() == today_date)
        {
            var hour = new Date().getHours();
            $('.hour_from option', selected_tab).each(function ()
            {
                $(this).removeAttr('selected');
                $(this).removeAttr('style');
                if (parseInt($(this).val()) < hour + 2)
                {
                    $(this).attr('style', 'display:none;');
                }
                else if (parseInt($(this).val()) == hour + 2)
                {
                    $(this).attr('selected', true);
                }
            });
        }
        else
        {
            $('.hour_from option', selected_tab).each(function ()
            {
                $(this).removeAttr('selected');
                $(this).removeAttr('style');

            });
        }

        fixHoursTo(initial);
    }


    function fixHoursTo(initial)
    {

        var selected_tab;

        if (initial == false)
            selected_tab = $('div.tab-pane.call-courrier-popup.active');
        else
            selected_tab = $('div.tab-pane.call-courrier-popup');

        if (selected_tab.length == 0) {
            alert('Error! Close tab!');
            return;
        }

        var selected_hour_from = parseInt($('.hour_from option:selected', selected_tab).val());
        $('.hour_to option', selected_tab).each(function ()
        {
            $(this).removeAttr('selected');
            $(this).removeAttr('style');
            if (parseInt($(this).val()) < selected_hour_from + 2)
            {
                $(this).attr('style', 'display:none;');
            }
            else if (parseInt($(this).val()) == selected_hour_from + 2)
            {
                $(this).attr('selected', true);
            }
        });

    }


    $(".courrier-call-form form").submit(function(e){

        //make ajax call
        var selected = $(".veni-arrive-date option:selected").val();
        var formData = {
            'weight': $(this).find('input[name="weight"]').val(),
            'pallets': $(this).find('input[name="pallets"]').val(),
            'warehouse_id': $(this).find('input[name="warehouse_id"]').val(),
            'comment': $(this).find('textarea[name="comment"]').val(),
            'arrive_date': selected,
            //'arrive_date': $(this).find('.call-times-select[data-fordate="'+selected+'"]').val(),
            'hour_from': $('.hour_from').val(),
            'min_from': $('.min_from').val(),
            'hour_to': $('.hour_to').val(),
            'min_to': $('.min_to').val(),
        };

        $.ajax({
				type:"POST",
				url: "{$callcarrierurl}",
				async: true,
				dataType: "json",
				data : formData,
				success : function(res)
				{
					console.log(res);
                    if(res.status==1){
                        //reload window
                        alert("{l s='Sucessfully called.'}");
                        $.fancybox.close();
                    }else{
                        alert(res.error);
                    }
				},
                error: function(res){
                    console.log("Error: ");
                    console.log(res);
                    alert("Error X001");
                }
			});
        return false;
    });
});
</script>