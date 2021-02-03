<?php
session_start();
mb_internal_encoding("UTF-8");
define('CM_PROLOG_INCLUDED',true);
global $arMyCon;

$FullPath = __DIR__;
$ReltPath = str_replace($_SERVER['DOCUMENT_ROOT'],'',$FullPath);
$aDir = array_filter(explode('/',$ReltPath));
list($ModDir) = array_slice($aDir,-3,1);
require_once($_SERVER['DOCUMENT_ROOT']."/".$ModDir."/config.php");

$Art = $_POST['Article'];
$Bra = $_POST['Brand'];
$ModuleDir = $_POST['ModDir'];
$Lang = $_POST['Lang'];
$Sname = PROTOCOL_DOMAIN_x;
$Link = PROTOCOL_DOMAIN_x.$_POST['Link'];


if(isset($Lang) && file_exists($FullPath.'/lang/'.$Lang.'.php')){
    include($FullPath.'/lang/'.$Lang.'.php');
}else{
    include($FullPath.'/lang/en.php');
}

$ReplArt = $request_from_atr.$Bra.' - '.$Art;
$MessArt = $request_from_atr_txt.$Bra.' - '.$Art;
if($_POST['Subreq']=='Y'){
    $arErrorMess = array();
    if($_POST['Capt'] != $_SESSION['captcha']){$arErrorMess['Capt'] = "Y";}
    if($_POST['Name']=='' || strlen($_POST['Name'])<3){$arErrorMess['Name'] = "Y";}
    if($_POST['Phone']=='' || strlen($_POST['Phone'])<4){$arErrorMess['Phone'] = "Y";}

    if(empty($arErrorMess)){
        $Email_Message = '<div style="font-size:24px;">'.$ask_price_title.' <b>'.$Bra.' - '.$Art.'</b></div><br>
                        <table style="min-height:300px; font-size:18px;">
                            <tr><td style="text-align:right; padding:0px 10px; font-weight:bold;">'.$name.'</td><td style="text-align:left; padding:0px 10px; border-radius:6px; background-color:#f0f0f0;">'.$_POST['Name'].'</td></tr>
                            <tr><td style="text-align:right; padding:0px 10px; font-weight:bold;">'.$phone.'</td><td style="text-align:left; padding:0px 10px; border-radius:6px; background-color:#f0f0f0;">'.$_POST['Phone'].'</td></tr>
                            <tr><td style="text-align:right; padding:0px 10px; font-weight:bold;">'.$message.'</td><td style="text-align:left; padding:0px 10px; border-radius:6px; background-color:#f0f0f0;">'.$_POST['Text'].'</td></tr>
                            <tr><td style="text-align:right; padding:0px 10px; font-weight:bold;">'.$prod_link.'</td><td style="text-align:left; padding:0px 10px; border-radius:6px; background-color:#f0f0f0;">'.$_POST['Link'].'</td></tr>
                        </table>';

        $Email_Title = $email_title;
        require_once($_SERVER['DOCUMENT_ROOT']."/".$ModDir."/core/object.php");
        require_once('mailer/class.phpmailer.php');
        $CPMod = new CPMod();

        $AdminEmail = $CPMod->arSettings['ADMIN_EMAIL'];
        $mail = new PHPMailer;
        $mail->CharSet = 'utf-8';
        $mail->addAddress($AdminEmail);
        $mail->Subject = $_POST['Name'].': '.$_POST['Phone'];
        $mail->msgHTML($Email_Message);
        $mail->FromName = $_POST['Name'];
        // $mail->From = $_POST['Email'];
        $sendMail = $mail->send();

        if($sendMail){
            $mailTrue = "Y";
        }else{
            $errorSend=$Error_sending_mail;
        }
    }else{
        $errors = "Y";
    }
}
include($FullPath.'/templates/default/template.php');
?>
