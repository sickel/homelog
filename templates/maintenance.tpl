{include file='page_head_noupdate.tpl'}
<form method="POST" action="maintenance.php">

<table>
<tr>
<th>Id</th>
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
<th>Address</th></tr>
<tr>
<td><input type="submit" Value="Add"/></td>
<td><input type="text" name="sensorSender" size="4"></td>
<td><input type="text" name="stationName" size="10">{html_options name=station options=$stationlist}</td>
<td><input type="text" name="sensorFactor" size="4"></td>
<td><input type="text" name="sensorTypeid" size="4"></td>
<td><input type="text" name="sensorType" size="10"></td>
<td><input type="checkbox" name="sensorActive"></td>
<td><input type="text" name="sensorMin" size="4"></td>
<td><input type="text" name="sensorMax" size="4"></td>
<td><input type="text" name="sensorMaxDelta" size="4"></td>
<td><input type="text" name="sensorName"></td>
<td><input type="text" name="sensorAdress"></td>
</tr>
{strip}
{foreach from=$data item=s name=item}
 {cycle values='Odd,Even' assign=trclass} 
            <tr class="{$trclass} right"><td class="right">{$s['id']}</td>
            <td class="right">{$s['senderid']}</td>
            <td class="right">{$s['station']}</td>
            <td class="right">{$s['factor']}</td>
            <td>{$s['typeid']} ({$s['typeid']|chr})</td>
            <td class="right">{$s['type']}</td>
            <td ><input type="checkbox" name="" onclick="return false;" {if $s['active']==1}checked="TRUE"{/if}></td>
            <td class="right">{$s['minvalue']}</td>
            <td class="right">{$s['maxvalue']}</td>
            <td class="right">{$s['maxdelta']}</td>
            <td class="right">{$s['name']}</td>
            <td class="right">{$s['adress']}</td>
        </tr>
    {/foreach}
{/strip}
</table>
</form>

{include file='page_foot.tpl'}
