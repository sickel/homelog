<html><head><title>kwh-registration</title></head><body>
<?php

/*

For adding values manually read from the power usage meter

*/

$dbtype='pgsql';
include('dbconn.php');
// sets the values username, server, database and password

try{
    $connectstring=$dbtype.':host='.$server.';dbname='.$database;
    $dbh = new PDO($connectstring, $username, $password);
    if($dbtype=='pgsql'){
      $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

  }
  catch(PDOException $e){
    $message=$e->getMessage(); 
    exit( "<p>Cannot connect - $message</p>");
  }

$read=$_POST['kwh']*1;
if($read>0 && $read < 100000){
  print($read);
  print("<br /><a href=\"stripchart.php?selected=Forbruk\">back</a>");
  
  try{
    $sqlh=$dbh->prepare("select addreading(?)");
    $sqlh->execute(array($read));
  }
  catch(PDOException $e){
    $message=$e->getMessage();
    exit($message);
  }
}else{print("
<form method=\"post\"><input name='kwh'/><input type='submit'/></form>
");
}
  
?>
<br /><a href="stripchart.php">chart</a>
</body></html>
