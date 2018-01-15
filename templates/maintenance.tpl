{include file='page_head_noupdate.tpl'}

<table>
<tr>
<th colspan="2">Id</th>
<th>Sender</th>
<th>Stasjon</th>
<th>Factor</th>
<th>Typeid</th>
<th>Type</th>
<th>Active</th>
<th>Min</th>
<th>Max</th>
<th>Max delta</th>
<th>Name</th>
<th>Address</th>
<tr>
<form method="POST" action="maintenance.php">
<td><input type="submit" Value="Add"/></td>
<td></td>
<td><input type="text" name="sensorSender" size="4"></td>
<td><input type="text" name="sensorStation"></td>
<td><input type="text" name="sensorFactor" size="4"></td>
<td><input type="text" name="sensorTypeid" size="4"></td>
<td><input type="text" name="sensorType" size="10"></td>
<td><input type="checkbox" name="sensorActive"></td>
<td><input type="text" name="sensorMin" size="4"></td>
<td><input type="text" name="sensorMax" size="4"></td>
<td><input type="text" name="sensorMaxDelta" size="4"></td>
<td><input type="text" name="sensorName"></td>
<td><input type="text" name="sensorAdress"></td>

</form>
</tr>
{strip}
{foreach from=$data item=s name=item}
 {cycle values='Odd,Even' assign=trclass} 
            <tr class="{$trclass} right"><td class="right">{$s[id]}</td>
            <td class="right">{$s['senderid']}</td>
            <td class="right">{$s['stationid']}</td>
            <td class="right">{$s['factor']}</td>
            <td>{$s['typeid']}</td>
            <td class="right">{$s['type']}</td>
            <td class="right">{$s['aux']}</td>
            <td class="right">{$s['payload']}</td>
            <td class="right"><a href=list.php?senderid={$s['stationid']}>{$s['stationid']}</a></td>
            <td class="right"><a href=list.php?senderid={$s['senderid']}>{$s['senderid']}</a></td>
        </tr>
    {/foreach}
{/strip}
</table>
{include file='page_foot.tpl'}
