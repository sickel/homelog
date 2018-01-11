<html><head><title>Last value</title>
<meta http-equiv=refresh content='60; url={$SCRIPT_NAME}'>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" type="text/css" href="tempdata.css">
<link rel="stylesheet" type="text/css" media="screen,projection,handheld and (min-device width:801px)" href="msi_smarty.css" charset="utf-8">
</head><body>
<table class="lastlist"><tr><th>Måling</th><th>Verdi</th></tr>
{foreach from=$data item=s name=item}
            <tr><td>
            <a href="stripchart.php?selid={$s['sensorid']}">{$s['station']} {$s['type']}</a></td>
            <td>{$s['value']}</td>
            <td>{$s['unit']}</td></tr>
    {/foreach}
</table>
<hr />
<p>Last update  {$smarty.now|date_format:"%Y-%m-%d %H:%M:%S"}</p>
<p><a href="stripchart.php">Stripchart</a>  <a href="list.php">Last values</a></p>
</body></html>
