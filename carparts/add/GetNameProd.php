<?if(!defined("CM_PROLOG_INCLUDED")){define('CM_PROLOG_INCLUDED',true);}
set_time_limit(0);
$FName = 'TempProdName.csv';

if(!isset($_POST['GETNAME'])){die();}
if(isset($_POST['DEL']) AND $_POST['DEL']=='Y'){unlink($FName); unlink('GetNameProd.php'); die();}

mb_internal_encoding("UTF-8");
header('Content-type: text/html; charset=utf-8');
require_once("../config.php");
require_once("../core/object.php");
global $CPMod;
$CPMod = new CPMod();


if(file_exists('TempPriceStat.csv')){
	unlink('TempPriceStat.csv');
}
$file = fopen($FName, 'w');
$result = $CPMod->LQ("SELECT nPKEY, nNAME, nLANG FROM CM_META GROUP BY nPKEY");

while($row = $CPMod->LFetch()){
	$Pom = PKeyPom_x($row['nPKEY']);
	$Name = str_replace(';',',',$row['nNAME']);
	//echo '<pre>'; print_r($row); echo '</pre><br><br>'; die();
	fwrite($file, $Pom.";".$row['nLANG'].";".$Name."\r\n");
}	
fclose($file);
echo 'SUCCESS';

?>