{include file='page_head.tpl'}
<table class="lastlist"><tr><th>Måling</th><th>Verdi</th></tr>
{foreach from=$data item=s name=item}
            <tr><td>
            <a href="stripchart.php?selid={$s['sensorid']}">{$s['station']} {$s['type']}</a></td>
            <td align="right">{$s['value']}</td>
            <td>{$s['unit']}</td></tr>
    {/foreach}
</table>
{include file='page_foot.tpl'}
