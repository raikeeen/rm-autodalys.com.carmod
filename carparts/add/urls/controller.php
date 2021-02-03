<?
$Url = 'http://rb-aa.bosch.com/wiperblade-videos/kwba021/video/'; 
if(strpos(' '.$Url,'http')){
	if($curl = curl_init($Url)){
		curl_setopt($curl, CURLOPT_TIMEOUT,5);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		$Res = curl_exec($curl);
		curl_close($curl);
		echo $Res;
		
	}else{echo 'Error! Fail to curl_init():<br>'.$Url;}
}else{echo 'Error! Invalid URL:<br>'.$Url;}
?>