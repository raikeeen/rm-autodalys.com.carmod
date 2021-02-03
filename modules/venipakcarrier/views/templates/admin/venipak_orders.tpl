<style>.tab-content{ border:1px solid #ddd;padding: 10px;}</style>
<div class="panel col-lg-12">
    <div class="panel-heading">
        <h4>{l s='Venipak orders' mod='venipakcarrier'}</h4>
    </div>
    {if count($warehouses) > 1}<div style="float: right;">
    <h4 style="display: inline;">{l s='Selected warehouse: ' mod='venipakcarrier'}</h4>&nbsp;&nbsp;
        <select style="width: 200px;display:inline;" class="change-warehouse">
        {foreach from=$warehouses item=warehouse}
            <option value="{$warehouse.id}" {if $warehouse.id == $warehouseId}selected{/if}>{$warehouse.address_title}</option>
        {/foreach}
    </select>
    </div>
    {/if}
    <ul class="nav nav-tabs">
        <li class="active"><a href="#tab-new" data-toggle="tab">{l s='Open' mod='venipakcarrier'}</a></li>
        <li><a href="#tab-closed" data-toggle="tab">{l s='Closed' mod='venipakcarrier'}</a></li>
        <li><a href="#tab-search" data-toggle="tab">{l s='Search' mod='venipakcarrier'}</a></li>
    </ul>
    <div class="tab-content">
        <!-- New Orders -->
        <div class="tab-pane active" id="tab-new">
            {if !empty($open_manifests)}

            {foreach from=$open_manifests key=manifest_no item=manifest}
                <h4>[{l s='Manifest no.:' mod='venipakcarrier'} <b>{$manifest_no}</b>&nbsp;&nbsp;&nbsp;&nbsp;{l s='Date:' mod='venipakcarrier'} <b>{$manifest.manifest.manifest_date}</b>&nbsp;&nbsp;&nbsp;&nbsp;{l s='Number of orders:' mod='venipakcarrier'} <b>{$manifest.manifest.orders_count}</b>&nbsp;&nbsp;&nbsp;&nbsp;{l s='Number of packets:' mod='venipakcarrier'} <b>{$manifest.manifest.packets_count}</b>]</h4>
                <table class="table order" >
                    <thead>
                    <tr class="nodrag nodrop">
                        <th width='1%'>
                            <span class="title_box active"><input type="checkbox" class="check-all" data-group="manifest-{$manifest_no}" /></span>
                        </th>
                        <th width='5%'>
                            <span class="title_box active">{l s='Order ID' mod='venipakcarrier'}</span>
                        </th>
                        <th width='15%'>
                            <span class="title_box">{l s='Customer' mod='venipakcarrier'}</span>
                        </th>
                        <th width='15%'>
                            <span class="title_box">{l s='Tracking' mod='venipakcarrier'}</span>
                        </th>
                        <th width='5%'>
                            <span class="title_box">{l s='Pack count' mod='venipakcarrier'}</span>
                        </th>
                        <th width='15%'>
                            <span class="title_box">{l s='Update date' mod='venipakcarrier'}</span>
                        </th>
                        <th width='5%'>
                            <span class="title_box">{l s='C.O.D' mod='venipakcarrier'}</span>
                        </th>
                        <th width='10%'>
                            <span class="title_box">{l s='Total' mod='venipakcarrier'}</span>
                        </th>
                        <th width='5%'>
                            <span class="title_box">{l s='Labels' mod='venipakcarrier'}</span>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach from=$manifest.orders item=order}
                        <tr>
                            <td><input type="checkbox" class="manifest-{$manifest_no}" value="{$order.order_id}" /></td>
                            <td>{$order.order_id}</td>
                            <td><a href="{$orderLink}&id_order={$order.order_id}">{$order.firstname} {$order.lastname}</td>
                            <td>{$order.tracking_number}</td>
                            <td>{$order.packscount}</td>
                            <td>{$order.date_upd}</td>
                            <td>{if $order.is_cod == 1}{l s='Yes' mod='venipakcarrier'}{else}{l s='No' mod='venipakcarrier'}{/if}</td>
                            <td>{$order.total_paid_tax_incl}</td>
                            <td><a href="{$printlabelsurl}&order_ids={$order.order_id}" class="btn btn-info btn-xs" target="_blank">{l s='Labels' mod='venipakcarrier'}</a></td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
                <br>

                <form method="POST" action="{$printlabelsurl}" target="_blank" style="display:inline;">
                    <button type="button" value="" class="veni-print-labels btn btn-info btn-xs" data-group="manifest-{$manifest_no}">{l s='Print Labels'}</button>
                </form>&nbsp;&nbsp;
                <a href="{$printmanifesturl}&manifest_no={$manifest_no}" class="btn btn-warning btn-xs" target='_blank'>{l s='Print Manifest' mod='venipakcarrier'}</a>
                &nbsp;&nbsp;
                <a href="{$closemanifesturl}&manifest_no={$manifest_no}" onclick="return confirm('{l s='Are you sure you want to close manifest?' mod='venipakcarrier'}');" class="btn btn-danger btn-xs">{l s='Close Manifest' mod='venipakcarrier'}</a>
                <br /><hr />
            {/foreach}
            {else}
                <div class="text-center">{l s='No open manifests' mod='venipakcarrier'}</div>
            {/if}
        </div>
        <!-- Completed Orders -->
        <div class="tab-pane" id="tab-closed">
            {if !empty($closed_manifests)}


                {foreach from=$closed_manifests key=manifest_no item=manifest}
                    <h4>[{l s='Manifest no.:' mod='venipakcarrier'} <b>{$manifest_no}</b>&nbsp;&nbsp;&nbsp;&nbsp;{l s='Date:' mod='venipakcarrier'} <b>{$manifest.manifest.manifest_date}</b>&nbsp;&nbsp;&nbsp;&nbsp;{l s='Number of orders:' mod='venipakcarrier'} <b>{$manifest.manifest.orders_count}</b>&nbsp;&nbsp;&nbsp;&nbsp;{l s='Number of packets:' mod='venipakcarrier'} <b>{$manifest.manifest.packets_count}</b>]</h4>
                    <table class="table order" >
                        <thead>
                        <tr class="nodrag nodrop">
                            <th width='1%'>
                                <span class="title_box active"><input type="checkbox" class="check-all" data-group="manifest-{$manifest_no}" /></span>
                            </th>
                            <th width='5%'>
                                <span class="title_box active">{l s='Order ID' mod='venipakcarrier'}</span>
                            </th>
                            <th width='15%'>
                                <span class="title_box">{l s='Customer' mod='venipakcarrier'}</span>
                            </th>
                            <th width='15%'>
                                <span class="title_box">{l s='Tracking' mod='venipakcarrier'}</span>
                            </th>
                            <th width='5%'>
                                <span class="title_box">{l s='Pack count' mod='venipakcarrier'}</span>
                            </th>
                            <th width='15%'>
                                <span class="title_box">{l s='Update date' mod='venipakcarrier'}</span>
                            </th>
                            <th width='5%'>
                                <span class="title_box">{l s='C.O.D' mod='venipakcarrier'}</span>
                            </th>
                            <th width='10%'>
                                <span class="title_box">{l s='Total' mod='venipakcarrier'}</span>
                            </th>
                            <th width='5%'>
                                <span class="title_box">{l s='Labels' mod='venipakcarrier'}</span>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach from=$manifest.orders item=order}
                            <tr>
                                <td><input type="checkbox" class="manifest-{$manifest_no}" value="{$order.order_id}" /></td>
                                <td>{$order.order_id}</td>
                                <td><a href="{$orderLink}&id_order={$order.order_id}">{$order.firstname} {$order.lastname}</td>
                                <td>{$order.tracking_number}</td>
                                <td>{$order.packscount}</td>
                                <td>{$order.date_upd}</td>
                                <td>{if $order.is_cod == 1}{l s='Yes' mod='venipakcarrier'}{else}{l s='No' mod='venipakcarrier'}{/if}</td>
                                <td>{$order.total_paid_tax_incl}</td>
                                <td><a href="{$printlabelsurl}&order_ids={$order.order_id}" class="btn btn-info btn-xs" target="_blank">{l s='Labels' mod='venipakcarrier'}</a></td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                    <br>

                    <form method="POST" action="{$printlabelsurl}" target="_blank" style="display:inline;">
                        <button type="button" value="" class="veni-print-labels btn btn-info btn-xs" data-group="manifest-{$manifest_no}">{l s='Print Labels'}</button>
                    </form>&nbsp;&nbsp;
                    <a href="{$printmanifesturl}&manifest_no={$manifest_no}" class="btn btn-warning btn-xs" target='_blank'>{l s='Print Manifest' mod='venipakcarrier'}</a>
                    <br /><hr />
                {/foreach}
                {$pagination_content}
            {else}
                <div class="text-center">{l s='No closed manifests' mod='venipakcarrier'}</div>
            {/if}
        </div>
        <!--/ Completed Orders -->
        <!--/ Completed Orders -- Tab search -->
        <div class="tab-pane" id="tab-search">
            <table class="table">
                <thead>
                <tr class="nodrag nodrop filter row_hover">
                    <th class="text-center" style="width: 10%;">
                        <input type="text" class="filter" name="order_id" placeholder="{l s='Order Id' mod='venipakcarrier'}" value="">
                    </th>
                    <th class="text-center">
                        <input type="text" class="filter" name="customer" placeholder="{l s='Customer name, last name' mod='venipakcarrier'}" value="">
                    </th>
                    <th class="text-center">
                        <input type="text" class="filter" name="tracking_nr" placeholder="{l s='Tracking no.' mod='venipakcarrier'}" value="">
                    </th>
                    <th class="text-center">
                        <input class="datetimepicker" name="manifest_date" placeholder="{l s='Manifest date' mod='venipakcarrier'}" type="text" >
                        <script type="text/javascript">
                            $(document).ready(function(){
                                $(".datetimepicker").datepicker({
                                    prevText: '',
                                    nextText: '',
                                    dateFormat: 'yy-mm-dd'
                                });
                            });

                        </script>
                    </th>
                    <th class="text-center">
                        <input type="text" class="filter" name="manifest_no" placeholder="{l s='Manifest no.' mod='venipakcarrier'}" value="">
                    </th>
                    <th class="text-center" style="vertical-align: middle;">{l s='Warehouse' mod='venipakcarrier'}</th>
                    <th class="text-center" style="vertical-align: middle;">{l s='C.O.D' mod='venipakcarrier'}</th>
                    <th class="text-center" style="vertical-align: middle;">{l s='Total' mod='venipakcarrier'}</th>
                    <th class="text-center" style="vertical-align: middle;">{l s='Labels' mod='venipakcarrier'}</th>
                    <th class="text-center" style="vertical-align: middle;"><a id="button-search" class="btn btn-default btn-sm">
                            {l s='Search' mod='venipakcarrier'}
                        </a>
                    </th>
                </tr>
                </thead>
                <tbody id="searchTable">
                <tr ><td colspan="10" style="text-align:center;">{l s='Search' mod='venipakcarrier'}</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        $(document).ready(function(){

            $(".change-warehouse").change(function(e){
                window.location.href = '{$baseUrl}&warehouse_id='+$(this).val();
            });

            $(".veni-print-labels").click(function(){

                var selected = $(':checkbox.'+$(this).data('group')+":checked").map(function() {
                    return this.value;
                }).get();


                if(selected.length==0){ alert('{l s="No orders selected."}'); return false; }

                var callerBtn = $(this);
                callerBtn.closest('form').find('input[type="hidden"]').remove();
                $.each(selected, function(index,value){
                    callerBtn.closest('form').append('<input type="hidden" name="order_ids[]" value="'+value+'" />');
                });
                callerBtn.closest('form').submit();
                return false;

            });


            var params={};
            window.location.search
                .replace(/[?&]+([^=&]+)=([^&]*)/gi, function(str,key,value) {
                        params[key] = value;
                    }
                );
            if(params['tab'] == 'closed')
                $('[href="#tab-closed"]').trigger('click');


            $('input[name="tracking_nr"], ' +
                'input[name="customer"], ' +
                'input[name="order_id"], ' +
                'input[name="manifest_no"]'
            ).on("keyup", function(event)
            {
                event.preventDefault();
                if (event.keyCode === 13) {
                    ajaxSend();
                }
            });

            $('.check-all').on('click', function() {
                    $(':checkbox.'+$(this).data('group')).prop('checked', this.checked);
            });


            /* Search script */
            $('#button-search').on('click', function() {
                ajaxSend();
            });

            function ajaxSend()
            {
                $.ajax({
                    url: '{$ajaxCall}',
                    type: 'post',
                    dataType: 'json',
                    data: $('input[name="tracking_nr"], ' +
                        'input[name="customer"], ' +
                        'input[name="manifest_date"], ' +
                        'input[name="order_id"],' +
                        'input[name="manifest_no"]'
                    ),
                    beforeSend: function() {
                        $('#searchTable').empty();
                    },
                    success: function(data) {
                        //console.log(data);
                        if(data != null && data[0] && Object.keys(data[0]).length >0) {
                            datas = data;
                            for(data of datas){
                                $('#searchTable').append("" +
                                    "<tr><td class='left'>"+data['id_order']+"</td>\
                                    <td><a href='{$orderLink}&id_order="+data['id_order']+"' target='_blank'>"+data['full_name']+"</a></td>\
                                    <td> "+data['tracking_number']+"</a></td>\
                                    <td>"+data['manifest_date']+"</td>\
                                    <td>"+data['manifest_no']+"</td>\
                                    <td class=\"text-center\">"+data['warehouse_id']+"</td>\
                                    <td class=\"text-center\">"+(data['is_cod'] == 1 ? '{l s='Yes' mod='venipakcarrier'}' : '{l s='No' mod='venipakcarrier'}')+"</td>\
                                    <td class=\"text-center\">"+data['total_paid_tax_incl']+"</td>\
                                    <td class=\"text-center\"><a href='{$printlabelsurl}&order_ids="+data['id_order']+"' class='btn btn-info btn-xs' target='_blank'>{l s='Labels' mod='venipakcarrier'}</a></td>\
                                    <td></td>\
                                </tr>");
                            }
                        } else
                            $('#searchTable').append("<tr><td colspan='10' style=\"text-align:center;\">{l s='No records were found' mod='venipakcarrier'}</td>");

                    },
                    error: function(xhr, ajaxOptions, thrownError) {

                    }
                });
            }

            });
    </script>