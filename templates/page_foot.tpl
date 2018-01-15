<hr />
<p>Last update  {$smarty.now|date_format:"%Y-%m-%d %H:%M:%S"} 
{if $vlevel<10}
Voltage: <a class="{$vlevel}" href="last_voltage.php">{$vlevel}</a>
{/if}
</p>
<p><a href="stripchart.php">Stripchart</a>  <a href="list.php">Last values list</a> <a href="last.php">Last values</a>
<a href="maintenance.php">Maintenance</a></p>
</body></html>
