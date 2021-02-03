<?php 
header('Content-type: text/html; charset=utf-8');
$SERVER_NAME = str_replace('www.','',$_SERVER['SERVER_NAME']);
$PhpS=substr(phpversion(),0,24);
$PhpF=floatval($PhpS);
$Extensions = get_loaded_extensions();
$ICver='<strong>Not loaded</strong> <a href="iwizard.php">install ioncube</a>';
$ICMinVer=floatval(10.2);
$ShowICHTTP=false;
if(in_array('ionCube Loader', $Extensions)){
	$ICver='<strong>Information missing</strong>';
	if(function_exists('ioncube_loader_version')){
		if(floatval(ioncube_loader_version()) >= $ICMinVer){
			$ICver='<b>Version '.ioncube_loader_version().'</b>';
			$ShowICHTTP=true;
		}else{
			$ICver='<strong>'.ioncube_loader_version().'</strong> (required '.$ICMinVer.'+)';
		}
	}
} 
?>
<div class="Cont">
	<h1>CarMod :: Server verification</h1>
	<table class="chartab chartab_b">
		<tr><td class="tarig">Domain:</td><td style="font-weight:bold;"><?php echo $SERVER_NAME?></td></tr>
		<tr><td class="tarig">PHP:</td><td>
			<?php if($PhpF>=7.1 AND $PhpF<7.4){echo '<b>'.$PhpS.'</b>';}else{echo '<strong>'.$PhpS.'</strong>';}?> (required 7.1+)
			</td></tr>
		<tr><td class="tarig">Ioncube Loader:</td><td><?php echo $ICver?></td></tr>
		<tr><td class="tarig">PHP extensions required:</td><td>
			<?php 
			foreach(Array('mysqli','mbstring','iconv','curl','zip') as $Ext){
				if(extension_loaded($Ext)){echo '<b>'.$Ext.'</b>';}else{echo '<strong>'.$Ext.'</strong>';}
				echo ', ';
			}?>
			</td></tr>
		<tr><td class="tarig">PHP extensions optional:</td><td>
			<?php 
			foreach(Array('sockets','libxml','soap','imap') as $Ext){
				if(extension_loaded($Ext)){echo $Ext;}else{echo '<s>'.$Ext.'</s>';}
				echo ', ';
			}?>
			</td></tr>
		<tr><td class="tarig">short_open_tag:</td><td>
			<?php if(ini_get('short_open_tag')==1){echo '<b>On</b>';}else{echo '<strong>Off</strong>';}?>
			</td></tr>
		<tr><td class="tarig">mbstring.func_overload:</td><td>
			<?php $MFO = ini_get('mbstring.func_overload'); 
			if(MFO==0){echo '<b>'.$MFO.'</b>';}else{echo '<strong>'.$MFO.'</strong> (required = 0)';}
			?>
			</td></tr>
		<tr><td class="tarig">PHP memory_limit:</td><td>
			<?php $MML = ini_get('memory_limit');
			if($MML>=256){echo '<b>'.$MML.'</b>';}else{echo '<b>'.$MML.'</b>';}?> (minimal: 256Mb, optimal: 512Mb)
			</td></tr>
		<tr><td class="tarig">This server Gateway IP:</td><td>
		<?php 
		if(extension_loaded('curl')){
			$verbose = fopen('php://temp', 'w+');
			$ch = curl_init('http://car-mod.com/services/ip.php');
			curl_setopt($ch, CURLOPT_TIMEOUT, 5);
			curl_setopt($ch, CURLOPT_VERBOSE, true);
			curl_setopt($ch, CURLOPT_STDERR, $verbose);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$Res = curl_exec($ch);
			if($Res === FALSE){
				printf("cUrl error (#%d): %s<br>\n", curl_errno($ch), htmlspecialchars(curl_error($ch)));
				rewind($verbose);
				$verboseLog = stream_get_contents($verbose);
				echo "Verbose information:\n<pre>", htmlspecialchars($verboseLog), "</pre>\n";
				//$version = curl_version();
				//$aCurlInfo = curl_getinfo($ch);
				//echo '<pre>';print_r($aCurlInfo);echo '</pre>';
			}else{
				echo $Res;
			}			
			fclose($verbose);
			curl_close($ch);
		}else{echo '<strong>CURL extension NOT loaded</strong>';}
		?>
		</td></tr>
		<tr><td class="tarig">CURLOPT_FILE:</td><td>
		<?php 
		if(extension_loaded('curl')){
			if($fp=fopen('carmod-curl-test.php','w+')){
				chmod("carmod-curl-test.php", 0775);
				$ch = curl_init('http://car-mod.com/services/curltest.txt');
				curl_setopt($ch, CURLOPT_TIMEOUT, 4);
				curl_setopt($ch, CURLOPT_FILE, $fp);
				curl_exec($ch); curl_close($ch); fclose($fp);
				require_once('carmod-curl-test.php');
				if(!unlink('carmod-curl-test.php')){echo '<br><strong>No PHP permissions to DELETE files</strong>';}
			}else{echo '<strong>No PHP permissions to CREATE files</strong>';}
		}else{echo '<strong>CURL extension NOT loaded</strong>';}
		?>
		</td></tr>
		<tr><td class="tarig">Remote MySQL access:</td><td>
		<?php 
		if(extension_loaded('mysqli')){
			$oMyQ = new mysqli('80.211.222.65', 'TestConnector', '123456');
			if($oMyQ->connect_error){
				echo '<strong>mysqli() Failed: </strong><span style="font-size:11px;">'.$oMyQ->connect_error.'</span>';
				echo '<br><span style="font-size:11px;">Open port 3306 on your host/server</span>';
			}else{echo '<b>Connected</b>';}
		}
		?>
		</td></tr>
	</table>
	
	<span style="font-size:10px;">
		$_SERVER['DOCUMENT_ROOT']: <?php echo $_SERVER['DOCUMENT_ROOT']?><br>
		max_input_time: <?php echo (ini_get('max_input_time') );?> sec.<br>
		max_execution_time: <?php echo (ini_get('max_execution_time') );?> sec.<br>
	</span>
	
</div><?php if(isset($_GET['f']) AND $_GET['f']=='php'){phpinfo();}?>


<style>
b{color:#2C9C00!important;} 
strong{color:#D12300!important;}
.Cont{width:800px; background:#f2f2f2;
	font-family:Verdana;
	font-size:12px;
	margin:10px auto 20px auto !important; 
	padding:10px 20px 30px 20px !important; 
	position:relative; display:block!important; 
	background:#f2f2f2 url(media/images/topfon.png) left top repeat-x; 
	border:1px solid #ffffff; -moz-border-radius:10px; -webkit-border-radius:10px; border-radius:10px;
	-moz-box-shadow:2px 2px 8px rgba(0,0,0,0.4); -webkit-box-shadow:2px 2px 8px rgba(0,0,0,0.4); box-shadow:2px 2px 8px rgba(0,0,0,0.4);
}
table{font-family:Verdana; font-size:12px; border-collapse:collapse;}
.Cont h1{font-family:Calibri!important; display:inline-block; color:#585858!important; font-weight:bold; font-size:20px!important; text-shadow:0px 0px 2px #ffffff; margin:15px 0px 12px 0px; }
.Cont a{color:#0086a7; text-decoration:none;}

.chartab{margin:0px 0px 20px 0px; box-shadow:4px 4px 2px #d3d3d3; border-collapse:collapse;}
.chartab td{font-size:11px; background:#EDEDED; padding:6px 10px 6px 10px; border:1px solid #a8a8a8;}
.chartab a{color:#326390; text-decoration:none;}
.chartab .head td{font-weight:bold; font-size:12px; cursor:pointer; white-space:nowrap; height:37px; text-shadow:1px 1px 1px #ffffff; border-top:0px solid #a8a8a8;
	background: -webkit-gradient(linear, center top, center bottom, from(#fff), to(#B3B3B3));
	background-image: linear-gradient(#fff, #B3B3B3);
}
.chartab a:hover{color:#D13000; text-decoration:underline;}
.chartab .rows:hover td{background:#ffffff!important;}
.chartab tr td:first-child{border-left:0px!important;}
.chartab tr td:last-child{border-right:0px!important;}
.chartab_b td{font-size:14px!important;}
.tarig{text-align:right;}
</style>