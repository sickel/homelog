{include file='page_head.tpl'}
<p><a href="list.php">Unfiltered</a></p>
<table><tr><th>Id</th><th>Sensor</th><th>Type</th><th>Verdi</th><th>Tid</th><th>Bruk</th><th>aux</th><th>Nr</th><th>Stasjon</th><th>Sender</th></tr>
{strip}
{foreach from=$data item=s name=item}
 {cycle values='Odd,Even' assign=trclass} 
            <tr class="{$trclass} right"><td class="right"><a href="stripchart.php?selid={$s['sensorid']}">{$s['id']}</td>
            <td class="right">{$s['sensorid']}</td>
            <td class="right">{$s['type']}</td>
            <td class="right">{$s['value']}</td>
            <td>{$s['datetime']}</td>
            <td class="right">{$s['use']}</td>
            <td class="right">{$s['aux']}</td>
            <td class="right">{$s['payload']}</td>
            <td class="right"><a href=list.php?senderid={$s['stationid']}>{$s['stationid']}</a></td>
            <td class="right"><a href=list.php?senderid={$s['senderid']}>{$s['senderid']}</a></td>
        </tr>
    {/foreach}
{/strip}
</table>
{include file='page_foot.tpl'}
