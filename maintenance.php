<?php

include('connect_db.php');
require 'smarty3/Smarty.class.php';
$smarty = new Smarty;
//$smarty->force_compile = true;
//$smarty->debugging = true;
$smarty->caching = false;
$smarty->cache_lifetime = 120;
print_r($_POST);
$smarty->assign('pagetitle','Maintenance');
$smarty->assign('vlevel',voltagelevel());
$smarty->display('maintenance.tpl');
?>
