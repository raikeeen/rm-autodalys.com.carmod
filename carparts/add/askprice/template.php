<link rel="stylesheet" href="/<?=$ModDir?>/add/askprice/style.css"/>
<div id="CmAskFormBl">
    <div id="CmLoadImg" style="background-image:url(/<?=$ModDir?>/add/askprice/images/loading.gif)"></div>
    <div class="CmAskTitleBl CmColorBgL"><div class="CmRequestTitle CmColorTx"><?=$ReplArt?></div><div class="CmReqTitText"><?=$title?></div></div>
    <?if(isset($_POST['Subreq']) && empty($arErrorMess)){ ?>
        <div class="ErrorMes">
            <?if($mailTrue){?>
                <span style="font-weight:bold; color:red; display:block;"><?=$thanks_we_will_reply?></span>
            <?}else{?>
                <span style="font-weight:bold; color:red; display:block;"><?=$errorSend?></span>
            <?}?>
        </div>
    <?}?>
    <div class="tclear"></div>
    <?if($mailTrue!=='Y'&&$_POST['Subreq']!=='Y'){?>
        <div class="CmFormBlock" id="formReq">
            <table class="CmAskPrTable">
                <tr>
                    <td class="CmFormText"><?=$your_mail?></td>
                    <td>
                        <input class="CmReqInput CmTitShow c_BorderHov c_BorderFoc c_Tx" title="<b style='font-size:12px;'><?=$example?></b><br><b><?=$mail?></b>&nbsp;<?=$example_email?><br><b><?=$phone?></b>&nbsp;<?=$example_phone?>" type="text" name="Useremail" data-email="<?=$email_correct?>" data-empty="<?=$input_empty?>">
                        <div class="CmErrBlock"><span class="CmErr"><?if($arErrorMess['Email']){echo $arErrorMess['Email'];}?></span></div>
                    </td>
                </tr>
                <tr>
                    <td class="CmFormText"><?=$your_name?></td>
                    <td>
                        <input class="CmReqInput c_BorderHov c_BorderFoc c_Tx" type="text" name="Reqname" data-name="<?=$write_name?>" data-empty="<?=$input_empty?>">
                        <div class="CmErrBlock"><span class="CmErr"><?if($arErrorMess['Name']){echo $arErrorMess['Name'];}?></span></div>
                    </td>
                </tr>
                <tr>
                    <td class="CmFormText"><?=$message?></td>
                    <td>
                        <textarea class="CmReqTextarea CmTitShow c_BorderHov c_BorderFoc c_Tx" title="<?=$textarea_title?>" type="text" name="message" data-mess="<?=$textarea_title?>" data-empty="<?=$input_empty?>"><?=$MessArt?></textarea>
                        <div class="CmErrBlock"><span class="CmErr"><?if($arErrorMess['Text']){echo $arErrorMess['Text'];}?></span></div>
                    </td>
                </tr>
                <tr>
                    <td class="CmFormText CmCaptInp"><img style="background-color:#e9f1f5; width:100px; height:28px;" src="<?=$ReltPath?>/captcha/captcha.php"/></td>
                    <td>
                        <input class="CmReqInput CmTitShow c_BorderHov c_BorderFoc c_Tx" type="text" title="<?=$captcha_title?>" name="captcha" placeholder="<?=$empty_code?>" data-code="<?=$captcha_title?>" data-empty="<?=$input_empty?>" data-numbonly="<?=$input_number?>">
                        <div class="CmErrBlock"><span class="CmErr"><?if($arErrorMess['Capt']){echo $arErrorMess['Capt'];}?></span></div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <div id="CmRequestSub" data-artnum="<?=$Art?>" data-brand="<?=$Bra?>" data-moduledir="<?=$ModDir?>" data-lang="<?=$Lang?>" data-link="<?=$Link?>">
                            <svg class="CmAskPriceImg" viewBox="0 -5 24 24"><path d="M12 2c5.514 0 10 4.486 10 10s-4.486 10-10 10-10-4.486-10-10 4.486-10 10-10zm0-2c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm1.25 17c0 .69-.559 1.25-1.25 1.25-.689 0-1.25-.56-1.25-1.25s.561-1.25 1.25-1.25c.691 0 1.25.56 1.25 1.25zm1.393-9.998c-.608-.616-1.515-.955-2.551-.955-2.18 0-3.59 1.55-3.59 3.95h2.011c0-1.486.829-2.013 1.538-2.013.634 0 1.307.421 1.364 1.226.062.847-.39 1.277-.962 1.821-1.412 1.343-1.438 1.993-1.432 3.468h2.005c-.013-.664.03-1.203.935-2.178.677-.73 1.519-1.638 1.536-3.022.011-.924-.284-1.719-.854-2.297z"/></svg>
                            <span><?=$askPrice?></span>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    <?}?>
</div>
