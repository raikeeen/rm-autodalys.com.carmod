<?php
//mb_internal_encoding("UTF-8");
if(isset($_POST['DelFile']) AND $_POST['DelFile']!=''){
	unlink($_SERVER['DOCUMENT_ROOT'].$_POST['DelFile']);
	//echo error_get_last();
	echo 'DELETED';
	die();
}


$_POST['dir'] = urldecode($_POST['dir']);
$arExts=Array('csv','txt','xls','xlsx','zip','rar');
$root=$_SERVER['DOCUMENT_ROOT'];
if( file_exists($root . $_POST['dir']) ) {
	$files = scandir($root . $_POST['dir']);
	natcasesort($files);
	if( count($files) > 2 ) { /* The 2 accounts for . and .. */
		echo "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
		// All dirs
		foreach( $files as $file ) {
			if( file_exists($root . $_POST['dir'] . $file) && $file != '.' && $file != '..' && is_dir($root . $_POST['dir'] . $file) ) {
				echo "<li class=\"directory collapsed\"><a href=\"#\" rel=\"" . $_POST['dir'] . $file . "/\">" . $file . "</a></li>";
			}
		}
		// All files
		foreach( $files as $file ) {
			if( file_exists($root . $_POST['dir'] . $file) && $file != '.' && $file != '..' && !is_dir($root . $_POST['dir'] . $file) ) {
				//$file = iconv("cp1251", "UTF-8", $file);
				$ext = preg_replace('/^.*\./', '', $file);
				if(in_array(strtolower($ext),$arExts)){
					echo "<li class=\"file ext_$ext\"><a href=\"#\" rel=\"" . $_POST['dir'] . $file . "\">" . $file . "</a><a download href=\"".$_POST['dir'].$file."\" title=\"Download\" class=\"CmATip imgRotate\" style=\"float:right; margin:-17px 17px 0 0;\"><img src=\"media/images/im_download.png\" width=\"16\" height=\"16\"/></a> <img src=\"media/images/trash.gif\" namefile=\"".$_POST['dir'].$file."\" class=\"CmATip imgRotate DelPList\" width=\"16\" height=\"16\" title=\"Delete\" style=\"float:right; margin-top:-17px;\"></li>";
				}
			}
		}
		echo "</ul>";	
	}
}

?>
<script>
$(".DelPList").on("click","", function(e){
	OverlayToggle();
	var ThisElem = this;
	var objPostBra = {};
	objPostBra['HeadOff']='Y';
	objPostBra['DelFile']=$(this).attr('namefile');
	$.post("media/jqft/ft-prices.php", objPostBra, function(Res){
		if(Res=='DELETED'){
			$(ThisElem).parent().remove();
		}else{alert(Res);}
		OverlayToggle();
	});

});
</script>