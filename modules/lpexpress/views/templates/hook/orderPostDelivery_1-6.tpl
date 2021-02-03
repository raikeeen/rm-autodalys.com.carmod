<div class="lp_carrier_container unvisible" data-id-carrier="{$id_carrier}">
    {$post_content}
</div>

{literal}
<script>
    $(document).ready(function() {
        movePS16ToCarrier("{/literal}{$id_carrier}{literal}", "{/literal}{$id_address}{literal}")
    });
</script>
{/literal}