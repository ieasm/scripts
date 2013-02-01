
<form action="#" method="POST"><textarea name="servicename"></textarea>
<p><textarea name="first_date"></textarea></p>
<p><textarea name="second_date"></textarea></p>
<p><input type="submit"></p>
</form>

<?php
if (isset($_POST['servicename']) and isset($_POST['first_date']) and isset($_POST['second_date'])) {

$servicename = $_POST['servicename'];
$first_date = $_POST['first_date'];
$second_date = $_POST['second_date'];
header('Content-Type: text/html; charset=UTF-8');
$host='84.47.177.239';
$database='zabbix';
$user='root';
$pswd='naukazabb1xslanet';

$link = mysql_connect($host , $user , $pswd) or die ("Не могу соединиться с сервером");
mysql_select_db($database) or die ("пиздец :(");

$query="SET names utf8";
mysql_query($query);


$query3="SELECT * FROM `services` WHERE `name` = '$servicename'";
$res=mysql_query($query3);

$h=0; //счетчик

while ($row = mysql_fetch_array($res))
{
 $bdid[$h]=$row['serviceid'];
echo  $bdid[$h];
$h=$h+1;
}

##echo $h;

$clock1 = strtotime($first_date);
$clock2 = strtotime($second_date);


$query2="DELETE FROM `service_alarms` WHERE (`serviceid` = $bdid[0]) AND (`clock` BETWEEN $clock1 AND $clock2)";

echo $query2;
//$del=mysql_query($query2);

mysql_close($link);
}
?>
